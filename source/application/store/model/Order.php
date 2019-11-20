<?php

namespace app\store\model;

use app\store\model\shop\StoreUser;
use think\Config;
use think\Db;
use app\common\exception\BaseException;
use app\store\model\Store       as StoreModel;
use app\common\model\Order      as OrderModel;
use app\store\model\OrderGoods  as OrderGoodsModel;
use app\store\service\Payment   as PaymentService;
use app\common\enum\OrderType   as OrderTypeEnum;
use app\store\model\dealer\Order  as DealerOrderModel;
use app\common\enum\order\PayType as PayTypeEnum;
/**
 * 订单管理
 * @author  luffy
 * @date    2019-07-17
 */
class Order extends OrderModel{


    const ORDER_QUERY = 'https://api.mch.weixin.qq.com/pay/orderquery';
    const APP_ID = 'wxd483c388c3d545f3';
    const MCH_ID = '1515804821';
    const KEY = 'Sem68GvhBu2ag5ncyJxsbDrXzZAHT3VK';

    private static $_para = array(
        "appid"     => self::APP_ID,
        "mch_id"    => self::MCH_ID,
        "sign_type" => "MD5"
    );

    /**
     * 订单列表
     * @author  luffy
     * @date    2019-07-17
     */
    public function getList($search_store_id, $delivery_type, $start_time, $end_time, $phone, $order_sn, $order_state, $tips){
        $where['a.mark'] = 1;
        //订单提醒
        if($tips == 1){
            $where['a.order_state'] = 20;
        }elseif($tips == 2){
            $where['a.order_state'] = 60;
        }
        // 检索查询条件
        if(!empty($search_store_id)){       //门店
            $store_id = $search_store_id;
        } else {
            $store_id = SELECT_STORE_ID;
        }
        if(isset($order_state) && $order_state != '' && $order_state != '-1'){         //订单状态
            $where['a.order_state'] = $order_state;
        }
        if(!empty($delivery_type)){         //配送属性
            $where['a.sendout']     = $delivery_type;
        }
        if(!empty($start_time) || !empty($end_time)){            //下单时间
            $where = timeCond($where,'a.add_time',$start_time, $end_time);
        }
        if(!empty($phone)){               //手机号
            $where['d.phone']       = [ 'like', "%$phone%"];
        }
        if(!empty($order_sn)){            //订单号
            $where['a.order_sn']    = [ 'like', "%$order_sn%"];
        }
        $prefix = Config::get('database.prefix');
        $result = Db::table( $prefix.'order_'.$store_id)
            ->alias('a')
            ->field('a.order_sn,a.store_id,a.add_time,a.sendout,a.buyer_id,a.order_state,a.order_amount,b.payment_type,c.pay_sn,c.sendout_time,c.number_order,d.username,d.phone,e.img source_img')
            ->join('order_relation_'.$store_id.' b', 'b.order_sn = a.order_sn')
            ->join('order_details_'.$store_id.' c', 'c.order_sn = a.order_sn')
            ->join('user d', 'd.id = a.buyer_id')
            ->join('store_source e', 'e.id = c.store_source_id')
            ->where($where)
            ->order(['a.id' => 'desc'])
            ->paginate(15, false, ['query' => \request()->request()])
            ->each(function ($value){
                return $this->toSwitch($value, true);
            });
        return $result;
    }

    /**
     * 获取订单详情
     * @author  luffy
     * @date    2019-07-28
     */
    public function getOrderDetail($order_sn){
        //获取订单ID
        $orderInfo = self::get(function($query) use ($order_sn){
            $query->where('order_sn', $order_sn);
        });

        $prefix = Config::get('database.prefix');
        $result = Db::table( $prefix.'order_'.$orderInfo->store_id)
            ->alias('a')
            ->field('a.order_sn,a.store_id,a.add_time,a.sendout,a.buyer_id,a.order_state,a.goods_amount,a.order_amount,a.evaluation_state,a.source,b.sendout_time,b.number_order,b.seller_msg,b.delivery,b.shipping_fee,b.discount_num,b.discount,b.fx_money,c.payment_type,c.payment_time,c.receive_time,c.comment_time,d.username,d.phone')
            ->join('order_details_'.$orderInfo->store_id.' b', 'b.order_sn = a.order_sn')
            ->join('order_relation_'.$orderInfo->store_id.' c', 'c.order_sn = a.order_sn')
            ->join('user d', 'd.id = a.buyer_id')
            ->where(['a.mark'=>1,'a.order_sn'=>$order_sn])
            ->find();

        //数据转换
        return $this->toSwitch($result);
    }

    /**
     * 数据转换
     * @author  luffy
     * @date    2019-07-28
     */
    public function toSwitch($value, $is_order_list = false){
        //根据订单号获取订单商品
        $value['goods']                 = (new OrderGoodsModel)->getOrderGoods($value['order_sn']);
        $value['format_delivery_type']  = !empty($value['sendout']) ? $this->delivery_type[$value['sendout']] : '';
        $value['format_order_state']    = isset($value['order_state']) ? $this->order_state[$value['order_state']] : '';
        $value['format_payment_type']   = !empty($value['payment_type']) ? $this->payment_type[$value['payment_type']] : '未付款';
            if($value['payment_type'] == 11){
                if(isset($value['pay_sn']) && strpos($value['pay_sn'],'_') !== false){
                    $p = explode('_',$value['pay_sn'])[1];
                    $value['format_payment_type'] .= '-'.$this->payment[$p];

                }
            }
        if(isset($value['add_time']))       $value['format_add_time']       = date('Y-m-d H:i:s', $value['add_time']);
        if(isset($value['payment_time']))   $value['format_payment_time']   = date('Y-m-d H:i:s', $value['payment_time']);
        $value['format_sendout_time']       = (!empty($value['sendout']) && $value['sendout'] == 1 && !empty($value['sendout_time'])) ? date('Y-m-d H:i', $value['sendout_time']) : '----';
        if(isset($value['receive_time']))   $value['format_receive_time']   = date('Y-m-d H:i:s', $value['receive_time']);
        if(isset($value['comment_time']))   $value['format_comment_time']   = date('Y-m-d H:i:s', $value['comment_time']);
        if(isset($value['seller_msg']))     $value['format_seller_msg']     = (!empty($value['seller_msg']) ? $value['seller_msg'] : '------');
        if(isset($value['phone']))          $value['format_phone']          = substr_replace($value['phone'], '****', 3, 4);
        if(isset($value['delivery']) && !empty($value['delivery'])){
            $value['format_delivery']   = unserialize($value['delivery']);
        }
        //判断当前订单用户有无所属分销人员
        $value['format_fx_user'] = false;
        if($is_order_list == true){
            $_1 = Db::name('fx_user_account')->where(['user_id'=>$value['buyer_id']])->find();
            $value['format_fx_user'] = (!empty($_1) ? false : TRUE );
        }
        return $value;
    }

    /**
     * 获取交班报表
     * author fup
     * date 2019-07-24
     */
    public function getExpList($query = []){
        $where['a.mark'] = 1;
        $where['a.order_state'] = ['>',10];
        $where['a.add_time'] = ['BETWEEN',[strtotime(date('Y-m-d')),strtotime(date('Y-m-d')) + 86400-1]];
        if (isset($query['start_time']) && !empty($query['start_time'])) {
            $where['a.add_time'] = ['>=',strtotime($query['start_time'])];
        }
        if (isset($query['end_time']) && !empty($query['end_time'])) {
            $where['a.add_time'] = ['<',strtotime($query['end_time']) + 86400];
        }
        if (isset($query['end_time']) && !empty($query['end_time']) && isset($query['start_time']) && !empty($query['start_time'])) {
            $where['a.add_time'] = ['BETWEEN',[strtotime($query['start_time']),strtotime($query['end_time']) + 86400-1]];
        }
        if (isset($query['sendout']) && !empty($query['sendout']) && $query['sendout'] != -1) {
            $where['a.sendout'] = $query['sendout'];
        }
        if (isset($query['store_user_id']) && !empty($query['store_user_id']) && $query['store_user_id'] != -1) {
            $where['b.valet_order_user_id'] = $query['store_user_id'];
        }
        if (isset($query['paymentType']) && !empty($query['paymentType']) && $query['paymentType'] != -1) {
            $where['c.payment_time'] = $query['paymentType'];
        }
        if (isset($query['sourceId']) && !empty($query['sourceId']) && $query['sourceId'] != -1) {
            $where['c.payment_source'] = $query['sourceId'];
        }
        if (isset($query['store_id']) && !empty($query['store_id']) && $query['store_id'] != -1) {
            $store_id = $query['store_id'];
        }else{
            $store_id = SELECT_STORE_ID;
        }
//        dump($where);die;
        //店铺缓存
        $this->storeAll = StoreModel::getCacheAll();
        //数据转换
        $this->OrderGoodsModel = new OrderModel;
        $data = Db::name('order_'.$store_id)
            ->alias('a')
            ->field('a.order_sn,a.order_amount,a.order_state,a.sendout,a.evaluation_state,a.store_id,b.discount,b.fx_money,b.point_discount,b.coupon_discount,b.shipping_fee,b.pay_sn,c.payment_type,c.payment_source,c.payment_time,d.username,d.phone,e.name as sourceName')
            ->join('order_details_'.$store_id .' b','a.order_sn = b.order_sn','LEFT')
            ->join('order_relation_'.$store_id.' c', 'c.order_sn = a.order_sn','LEFT')
            ->join('user d', 'd.id = a.buyer_id','LEFT')
            ->join('store_source e','c.payment_source = e.id','LEFT')
            ->where($where)
            ->order(['a.id' => 'desc'])
            ->paginate(15, false, ['query' => \request()->request()])
            ->each(function ($item){
                //根据订单号获取订单商品
                $item['statusName'] = self::getOrderStatusName($item['sendout'], $item['order_state'], $item['evaluation_state']);
                if(!empty($item['sendout']) && isset($item['sendout'])){
                    $item['sendoutName']  = $this->delivery_type[$item['sendout']];
                }else{
                    $item['sendoutName'] = '';
                }
                //订单来源
                if ($item['payment_source'] == 1758421) {
                    $item['sourceName'] = '艾美睿';
                }
                if(!empty($item['phone']) && isset($item['phone'])){
                    $item['phone'] = substr_replace($item['phone'], '****', 3, 4);
                }
                $item['paymentName']  = !empty($item['payment_type']) ? $this->payment_type[$item['payment_type']] : '未付款';
                if($item['payment_type'] == 11){
                    if(isset($item['pay_sn']) && strpos($item['pay_sn'],'_') !== false){
                        $p = explode('_',$item['pay_sn'])[1];
                        $item['paymentName'] .= '-'.$this->payment[$p];

                    }
                }
                $item['payment_time'] = date('Y-m-d H:i', $item['payment_time']);
                $item['shipping_fee'] = $item['sendout']==1 ? 0 : $item['shipping_fee'];
                $item['format_order_state'] = $this->order_state[$item['order_state']];
                $item['format_store_name']  = $this->storeAll[$item['store_id']]['store_name'];
                return $item;
            });
        return $data;
    }

    /**
     * 获取来源id
     * author fup
     * date 2019-07-23
     */
    public function getSourceId($sourceId){
        $source = new StoreSource();
        $data = $source->alias('a')
            ->join('store_source b','a.name = b.name','LEFT')
            ->where('a.id','=',$sourceId)
            ->column('b.id');
        return $data;
    }

    /**
     * 获取未提示的订单order_sn
     * @author  luffy
     * @date    2019-08-06
     */
    public function getNoTipsOrder($store_id, $re = 0){
        $start_time = strtotime(date('Y-m-d', time()));
        $end_time   = $start_time + 24*60*60 - 1;
        $data = Db::name('order_'.$store_id)->alias('a')
            ->join('order_details_'.$store_id.' b','a.order_sn = b.order_sn')
            ->where(['a.mark' => 1, 'a.order_state'=>['EGT', 20], 'b.warning_tone'=>1, 'a.add_time'=>['BETWEEN', $start_time.','.$end_time]])
            ->column('a.order_sn');
        if(empty($data)){
            return false;
        } else {
            Db::startTrans();
            try{
                Db::name('order_details_'.$store_id)
                    ->where(['order_sn'=>['in',implode(',', $data)]])
                    ->update(['warning_tone' => 2]);
                Db::commit();
                if($re != 1){
                    echo '<audio src="http://www.lmeri.com/assets/admin/dist/remind.mp3" id="myaudio"></audio>';
                    echo '<script>
                        (function( ){
                              var myAuto = document.getElementById(\'myaudio\');
                                myAuto.play();
                        })();
                    </script>';
                }
                //票据打印
                $view = new \think\View();
                $view->engine->layout(false);
                return $view->engine('php')->fetch('order/automatic-print', ['print_data'=>$this->getPrintData($data)]);
            }catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                $this->error = $e->getMessage();
                return false;
            }
        }
    }

    /**
     * 获取打印数据
     * @author  luffy
     * @date   2019-08-08
     */
    public function getPrintData($order_sn){
        if( empty($order_sn) ){
            $this->setData(array(), 1, "订单号必传");
        }
        $_order_sn      = implode(',', $order_sn);
        $first_order_sn = $order_sn[0];

        //获取订单ID
        $orderInfo = self::get(function($query) use ($first_order_sn){
            $query->where('order_sn', $first_order_sn);
        });
        $storeData  =  Store::getCacheAll()[$orderInfo->store_id];
        $print_data = Db::name('order_'.$orderInfo->store_id)
            ->alias('a')
            ->field('b.*,a.order_sn,a.order_state,a.goods_amount,a.sendout,a.order_amount,a.add_time,c.payment_type,d.username,d.phone')
            ->join('order_details_'.$orderInfo->store_id.' b', 'b.order_sn = a.order_sn')
            ->join('order_relation_'.$orderInfo->store_id.' c', 'c.order_sn = a.order_sn')
            ->join('user d', 'd.id = a.buyer_id')
            ->where(['a.mark'=>1,'a.order_sn'=>['in', $_order_sn]])
            ->select()->toArray();

        //小票打印商品总数
        foreach ($print_data as $key => $val) {
            //获取订单商品
            $list   = Db::name('order_goods')->where(['order_id'=>$val['order_sn']])->select()->toArray();
            $print_data[$key]['goods_list']             = $list;
            $print_data[$key]['total_num']              = count($list);

            foreach($list as $k => $v){
                $print_data[$key]['goods_list'][$k]['current_num']  = $k + 1;
            }
            //获取分销人员
            if( $val['fx_user_id'] ){
                $fxUserInfo   = Db::name('fx_user')->where(['id'=>$val['fx_user_id']])->find();
                $print_data[$key]['fx_code']            = $fxUserInfo['fx_code'];
            }
            $print_data[$key]['store_name']             = $storeData['store_name'];
            $print_data[$key]['store_mobile']           = $storeData['store_mobile'];
            $print_data[$key]['format_payment_type']    = $this->payment_type[$val['payment_type']];
            $print_data[$key]['format_delivery_type']   = $this->delivery_type[$val['sendout']];
            $print_data[$key]['format_order_state']     = $this->order_state[$val['order_state']];
            if(isset($val['phone']) && !empty($val['phone'])){
                $print_data[$key]['format_phone'] = substr_replace($val['phone'], '****', 3, 4);
            }
            if(isset($val['delivery']) && !empty($val['delivery'])){
                $print_data[$key]['format_delivery'] = unserialize($val['delivery']);
            }
        }
        return $print_data;
    }

    /**
     * 老后台交班报表
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-07-24
     * Time: 18:17
     */
    public function excelOut($query = []){

        $where['a.mark'] = 1;
        $where['a.order_state'] = ['in',[20,25,30,40,50,60,70]];
        if(isset($query['sendout']) && !empty($query['sendout']) && $query['sendout'] != -1){
            $where['a.sendout'] = $query['sendout'];
        }
        if(isset($query['store_user_id']) && !empty($query['store_user_id']) && $query['store_user_id'] != -1){
            $where['b.valet_order_user_id'] = $query['store_user_id'];
        }
        if(isset($query['paymentType']) && !empty($query['paymentType']) && $query['paymentType'] != -1){
            $where['c.payment_type'] =$query['paymentType'];
        }
        if(isset($query['sourceId']) && !empty($query['sourceId']) && $query['sourceId'] != -1){
            $where['c.payment_source'] = $query['sourceId'];
        }
        if(isset($query['start_time']) && !empty($query['start_time'])){
            $where['a.add_time'] =['>=',strtotime($query['start_time'])];
        }
        if(isset($query['end_time']) && !empty($query['end_time'])){
            $where['a.add_time'] = ['<=',strtotime($query['end_time'])+ 86400-1];
        }
        if(isset($query['start_time']) && !empty($query['start_time']) && isset($query['end_time']) && !empty($query['end_time'])){
            $where['a.add_time'] = ['BETWEEN',[strtotime($query['start_time']),strtotime($query['end_time']) + 86400-1]];

        }
        if(isset($query['store_id']) && !empty($query['store_id']) && $query['store_id'] != -1){
            $store_id = $query['store_id'];
        }else{
            $store_id = STORE_ID;
        }
        if(isset($query['store_user_id']) && !empty($query['store_user_id'])){
            $user = StoreUser::get(['id'=>$query['store_user_id']]);
            $storeUserName = $user['username'];
        }else{
            $storeUserName = '全部人员';
        }
//        dump($storeUserName);die;
        $data = Db::name('order_'.$store_id)->alias('a')->join('order_details_'.$store_id . ' b','a.order_sn = b.order_sn','LEFT')
            ->join('order_relation_'.$store_id.' c','a.order_sn = c.order_sn','LEFT')
            ->join('user d','a.buyer_id = d.id','LEFT')
            ->join('store_source e','c.payment_source = e.id','LEFT')
//            ->join('order_goods f','a.order_sn = f.order_id','LEFT')
//            ->join('store_goods g','f.goods_id = g.id','LEFT')
            ->where($where)
            ->field('a.id,a.order_sn,a.goods_amount,a.order_amount,a.order_state,a.sendout,a.evaluation_state,b.pay_sn,b.discount,b.fx_money,b.point_discount,b.coupon_discount,b.shipping_fee,c.payment_type,c.payment_source,c.payment_time,d.username,d.phone,e.name as sourceName')
            ->order('a.id DESC')
            ->select()->toArray();

        foreach ($data as $k => &$item){
            $item['original_price'] = 0;
            //订单状态
            $item['statusName'] = self::getOrderStatusName($item['sendout'], $item['order_state'], $item['evaluation_state']);
            if(isset($item['sendout']) && !empty($item['sendout'])){
                $item['sendoutName'] = $this->delivery_type[$item['sendout']];
            }else{
                $item['sendoutName'] = '';
            }
            //订单来源
            $item['payment_source'] == 1758421 && $item['sourceName'] = '艾美睿';

            //支付方式
            $item['paymentName']  = !empty($item['payment_type']) ? $this->payment_type[$item['payment_type']] : '未付款';
            if($item['payment_type'] == 11){
                if(isset($item['pay_sn']) && strpos($item['pay_sn'],'_') !== false){
                    $p = explode('_',$item['pay_sn'])[1];
                    $item['paymentName'] .= '-'.$this->payment[$p];

                }
            }
            //付款时间
            $item['payment_time'] = date('Y-m-d H:i', $item['payment_time']);
            $item['shipping_fee'] = $item['sendout']==1 ? 0 : $item['shipping_fee'];

            $item['goods_info'] = Db::name('order_goods')
                ->alias('o')
                ->field('o.order_id,o.goods_name,o.goods_price,o.goods_num,o.goods_pay_price,o.spec_key_name,s.market_price')
                ->join('store_goods s','o.goods_id = s.id','LEFT')
                ->where('o.order_id','=',$item['order_sn'])
//                ->page(input('page'),'')
                ->select()->toArray();
            foreach ($item['goods_info'] as $goods){
                $item['original_price'] += bcmul($goods['goods_num'] , $goods['goods_price'],2);
            }
        }
//        dump($data);die;
        //需要统计的变量
        $orderNumTotal = 0;
        $goodsAmountTotal = 0.00;
        $orderAmountTotal = 0.00;
        $discountTotal = 0.00;
        $fxMoneyTotal = 0.00;
        $pointDiscountTotal = 0.00;
        $couponDiscountTotal = 0.00;
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 0);
        ob_end_clean();
        ob_start();
        header('Content-Type: application/vnd.ms-excel');
        header('Cache-Control: max-age=0');
        header("Content-Disposition: attachment; filename=交班订单统计报表".date('YmdHis').".xls");
        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', "订单编号") . "\t";
        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', "配送方式") . "\t";
        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', "买家姓名") . "\t";
        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', "买家手机") . "\t";
        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', "订单来源") . "\t";
        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', "支付方式") . "\t";
        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', "支付单号") . "\t";
        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', "付款时间") . "\t";
        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', "订单运费") . "\t";
        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', "订单原价格") . "\t";
        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', "订单总价格") . "\t";
        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', "订单状态") . "\t";
        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', "优惠额抵扣") . "\t";
        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', "分销码抵扣") . "\t";
        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', "睿积分抵扣") . "\t";
        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', "优惠券抵扣") . "\t";
        echo "\n";
        foreach ($data as $k => $v) {
            //订单主表信息
            echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', "'" . $v['order_sn']) . "\t";          //订单编号
            echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', $v['sendoutName']) . "\t";              //配送方式
            echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', $v['username']) . "\t";              //买家姓名
            echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', $v['phone']) . "\t";                   //买家手机
            echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', $v['sourceName']) . "\t";                  //订单来源
            echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', $v['paymentName']) . "\t";            //支付方式
            echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', "'" . $v['pay_sn']) . "\t";            //支付单号
            echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', $v['payment_time']) . "\t";        //付款时间
            echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', $v['shipping_fee']) . "\t";            //订单运费
            echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', $v['original_price']) . "\t";            //订单原价格
            echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', $v['order_amount']) . "\t";            //订单总价格
            echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', $v['statusName']) . "\t";              //订单状态
            echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', $v['discount']) . "\t";                //优惠额抵扣
            echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', $v['fx_money']) . "\t";               //分销码抵扣
            echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', $v['point_discount']) . "\t";               //睿积分抵扣
            echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', $v['coupon_discount']) . "\t";               //优惠劵抵扣
            echo "\n";
            //订单商品信息
            echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', '') . "\t";                                //NULL
            echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', '商品编号') . "\t";
            echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', '商品名称') . "\t";
            echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', '商品价格') . "\t";
            echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', '商品数量') . "\t";
            echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', '商品实际成交价') . "\t";
            echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', '规格名称') . "\t";
            echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', '市场价') . "\t";
            echo "\n";

//            $order_goods_data = $orderGoodsInfo[$v['id']];

            foreach ($v['goods_info'] as $gv) {
                echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', '') . "\t";                                //NULL
                echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', "'" . $gv['order_id']) . "\t";         //订单编号
                echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', $gv['goods_name']) . "\t";             //商品名称
                echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', $gv['goods_price']) . "\t";            //商品价格
                echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', $gv['goods_num']) . "\t";              //商品数量
                echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', $gv['goods_pay_price']) . "\t";        //商品实际成交价
                echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', $gv['spec_key_name']) . "\t";          //规格名称
                echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', $gv['market_price']) . "\t";          //市场价
                echo "\n";
            }
            //统计
            if ($v['order_state'] != 70) {
                $orderNumTotal += 1;
                $goodsAmountTotal += $v['goods_amount'];
                $orderAmountTotal += $v['order_amount'];
                $discountTotal += $v['discount'];
                $fxMoneyTotal += $v['fx_money'];
                $pointDiscountTotal += $v['point_discount'];
                $couponDiscountTotal += $v['coupon_discount'];
            }
        }
//        dump($query);die;
        //获取筛选人员的昵称


        echo "\n";
        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', '有效订单总笔数:') . "\t";
        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', $orderNumTotal) . "\t";
        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', '订单原金额:') . "\t";
        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', number_format($goodsAmountTotal, 2)) . "\t";
        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', '实收营业额:') . "\t";
        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', number_format($orderAmountTotal, 2)) . "\t";
        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', '优惠金额:') . "\t";
        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', number_format($discountTotal, 2)) . "\t";
        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', '分销码抵扣金额:') . "\t";
        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', number_format($fxMoneyTotal, 2)) . "\t";
//        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', '睿币抵扣金额:') . "\t";
//        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', number_format($pointDiscountTotal, 2)) . "\t";
        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', '优惠卷抵扣金额:') . "\t";
        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', number_format($couponDiscountTotal, 2)) . "\t";
        echo "\n";
        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', '交班人员:') . "\t";
        echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', $storeUserName) . "\t";
        if (isset($query['start_time']) && !empty($query['start_time'])) {
            echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', '筛选时间开始:') . "\t";
            echo $this->filterTime(strtotime($query['start_time'])). "\t";
        }
        if (isset($query['end_time']) && !empty($query['end_time'])) {
            echo iconv('utf-8', 'gbk//TRANSLIT//IGNORE', '筛选时间结束:') . "\t";
            echo $this->filterTime(strtotime($query['end_time']) + 86400 -  1) . "\t";
        }
        echo "\n";
    }


    /**
     * 交班报表
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-11-18
     * Time: 17:48
     */
    public function exportList($query = [])
    {
        // 获取订单列表

        $where['a.mark'] = 1;
        $where['a.order_state'] = ['>',10];
        if(isset($query['sendout']) && !empty($query['sendout']) && $query['sendout'] != -1){
            $where['a.sendout'] = $query['sendout'];
        }
        if(isset($query['store_user_id']) && !empty($query['store_user_id']) && $query['store_user_id'] != -1){
            $where['b.valet_order_user_id'] = $query['store_user_id'];
        }
        if(isset($query['paymentType']) && !empty($query['paymentType']) && $query['paymentType'] != -1){
            $where['c.payment_type'] =$query['paymentType'];
        }
        if(isset($query['sourceId']) && !empty($query['sourceId']) && $query['sourceId'] != -1){
            $where['c.payment_source'] = $query['sourceId'];
        }
        if(isset($query['start_time']) && !empty($query['start_time'])){
            $where['a.add_time'] =['>=',strtotime($query['start_time'])];
        }
        if(isset($query['end_time']) && !empty($query['end_time'])){
            $where['a.add_time'] = ['<=',strtotime($query['end_time'])+ 86400-1];
        }
        if(isset($query['start_time']) && !empty($query['start_time']) && isset($query['end_time']) && !empty($query['end_time'])){
            $where['a.add_time'] = ['BETWEEN',[strtotime($query['start_time']),strtotime($query['end_time']) + 86400-1]];

        }
        if(isset($query['store_id']) && !empty($query['store_id']) && $query['store_id'] != -1){
            $store_id = $query['store_id'];
        }else{
            $store_id = STORE_ID ;
        }

//        dump($where);die;

//        dump($storeUserName);die;
        $data = Db::name('order_'.$store_id)->alias('a')->join('order_details_'.$store_id . ' b','a.order_sn = b.order_sn','LEFT')
            ->join('order_relation_'.$store_id.' c','a.order_sn = c.order_sn','LEFT')
            ->join('order_refund r','c.order_sn = r.order_sn','LEFT')
            ->join('user d','a.buyer_id = d.id','LEFT')
            ->join('store_source e','b.store_source_id = e.id','LEFT')
            ->where($where)
            ->field('a.id,a.order_sn,a.goods_amount,a.order_amount,a.order_state,a.sendout,a.evaluation_state,a.add_time,b.pay_sn,b.discount,b.fx_money,b.point_discount,b.coupon_discount,b.shipping_fee,c.payment_type,c.payment_source,c.payment_time,r.refund_amount,r.add_time as r_time,d.username,d.phone,e.name as sourceName')
            ->order('a.id DESC')
            ->select()->toArray();
        foreach ($data as $k => &$item){
            $item['original_price'] = 0;
            //订单状态
            $item['statusName'] = self::getOrderStatusName($item['sendout'], $item['order_state'], $item['evaluation_state']);
            if(isset($item['sendout']) && !empty($item['sendout'])){
                $item['sendoutName'] = $this->delivery_type[$item['sendout']];
            }else{
                $item['sendoutName'] = '';
            }
            //支付方式
            $item['paymentName']  = !empty($item['payment_type']) ? $this->payment_type[$item['payment_type']] : '未付款';
            if($item['payment_type'] == 11){
                if(isset($item['pay_sn']) && strpos($item['pay_sn'],'_') !== false){
                    $p = explode('_',$item['pay_sn'])[1];
                    $item['paymentName'] .= '-'.$this->payment[$p];

                }
            }
            //付款时间
            $item['payment_time'] = date('Y-m-d H:i', $item['payment_time']);
            $item['shipping_fee'] = $item['sendout']==1 ? 0 : $item['shipping_fee'];
        }
        // 表格标题
        $tileArray = ['订单号', '配送方式', '买家姓名', '买家手机', '订单来源', '支付方式', '付款时间','支付单号','订单金额','付款金额', '运费金额',
            '订单状态', '退款金额','退款时间', '优惠金额', '分销码抵扣', '睿积分抵扣', '优惠券抵扣', '下单时间'];

        // 表格内容
        $dataArray = [];
        $totalMoney = 0;//订单总金额
        $payMoney = 0;//实付金额
        $discountMoney = 0;//优惠金额
        $fxMoney = 0;//分销金额
        $couponMoney = 0;//优惠券金额
        foreach ($data as $order) {
            $totalMoney += $order['goods_amount'];
            $order['order_state'] != 70 && $payMoney += $order['order_amount'];
            $discountMoney += $order['discount'];
            $fxMoney += $order['fx_money'];
            $couponMoney += $order['coupon_discount'];
                $dataArray[] = [
                    '订单号' => $this->filterValue($order['order_sn']),
                    '配送方式' => $order['sendoutName'],
                    '买家姓名' => $order['username'],
                    '买家手机' => $this->filterValue($order['phone']),
                    '订单来源' => $order['sourceName'],
                    '支付方式' => $order['paymentName'],
                    '付款时间' => $this->filterValue($order['payment_time']),
                    '支付单号' => $this->filterValue($order['pay_sn']),
                    '订单金额' => $order['goods_amount'],
                    '付款金额' => $order['order_amount'],
                    '运费金额' => $order['shipping_fee'],
                    '订单状态' => $order['statusName'],
                    '退款金额' => $order['refund_amount'] ? : 0,
                    '退款时间' => $order['r_time'] ? $this->filterValue(date('Y-m-d H:i',$order['r_time'])) : '',
                    '优惠金额' => $order['discount'],
                    '分销码抵扣' => $order['fx_money'],
                    '睿积分抵扣' => $order['point_discount'],
                    '优惠券抵扣' => $order['coupon_discount'],
                    '下单时间' => $this->filterValue(date('Y-m-d H:i',$order['add_time']))
                ];
        }
        $dataArray[] = [
            '订单号' => '',
            '配送方式' => '',
            '买家姓名' => '',
            '买家手机' => '',
            '订单来源' => '',
            '支付方式' => '',
            '付款时间' => '',
            '支付单号' => '',
            '订单金额' => '',
            '付款金额' => '',
            '运费金额' => '',
            '订单状态' => '',
            '退款金额' => '',
            '退款时间' => '',
            '优惠金额' => '',
            '分销码抵扣' => '',
            '睿积分抵扣' => '',
            '优惠券抵扣' => '',
            '下单时间' => ''
        ];
        $dataArray[] = [
            '订单号' => '',
            '配送方式' => '',
            '买家姓名' => '',
            '买家手机' => '订单原金额:',
            '订单来源' => $totalMoney,
            '支付方式' => '订单实付金额:',
            '付款时间' => $payMoney,
            '支付单号' => '优惠金额:',
            '订单金额' => $discountMoney,
            '付款金额' => '优惠券金额:',
            '运费金额' => $couponMoney,
            '订单状态' => '',
            '退款金额' => '',
            '退款时间' => '',
            '优惠金额' => '',
            '分销码抵扣' => '',
            '睿积分抵扣' => '',
            '优惠券抵扣' => '',
            '下单时间' => ''
        ];
        $dataArray[] = [
            '订单号' => '',
            '配送方式' => '',
            '买家姓名' => '',
            '买家手机' => '',
            '订单来源' => '',
            '支付方式' => '',
            '付款时间' => '',
            '支付单号' => '',
            '订单金额' => '',
            '付款金额' => '',
            '运费金额' => '',
            '订单状态' => '',
            '退款金额' => '',
            '退款时间' => '筛选时间：',
            '优惠金额' => $this->filterValue($query['start_time'].'至'.$query['end_time']),
            '分销码抵扣' => '',
            '睿积分抵扣' => '',
            '优惠券抵扣' => '',
            '下单时间' => ''
        ];
        // 导出csv文件
        $filename = '交班报表-' . date('YmdHis');
        return export_excel($filename . '.csv', $tileArray, $dataArray);
    }





    /**
     * 通过sendout、evaluation_state和order_status获取订单状态
     */
    public static function getOrderStatusName($sendout, $status, $evaluation)
    {
        $statusName = '';
        if (empty($sendout)) {
            $sendout = 0;
        }
        if (in_array($sendout, array(0, 1, 2))) {//暂时只考虑补单、自提、配送
            switch ($status) {
                case 0:
                    $statusName = '已取消';
                    break;
                case 10:
                    $statusName = '待付款';
                    break;
                case 20:
                    $statusName = '已付款';
                    break;
                case 25:
                    $statusName = '已接单';
                    break;
                case 30:
                    $statusName = $sendout == 1 ? '待收货' : '已发货';
                    break;
                case 40:
                    $statusName = $sendout == 1 ? '待收货' : '区域配送';
                    break;
                case 50:
                    $statusName = $evaluation == 1 ? '已评价' : '已收货';
                    break;
                case 60:
                    $statusName = '退款中';
                    break;
                case 70:
                    $statusName = '已退款';
                    break;
            }
        }
        return $statusName;
    }




    /**
     * 批量发货模板
     */
    public function deliveryTpl()
    {
        // 导出csv文件
        $filename = 'delivery-' . date('YmdHis');
        return export_excel($filename . '.csv', ['订单号', '物流单号']);
    }

    /**
     * 表格值过滤
     * @param $value
     * @return string
     */
    private function filterValue($value)
    {
        return "\t" . $value . "\t";
    }

    /**
     * 日期值过滤
     * @param $value
     * @return string
     */
    private function filterTime($value)
    {
        if (!$value) return '';
        return $this->filterValue(date('Y-m-d H:i:s', $value));
    }

    /**
     * 设置检索查询条件
     * @param $query
     */
    private function setWhere($query)
    {
        if (isset($query['search']) && !empty($query['search'])) {
            $this->where('order_no|user.nickName', 'like', '%' . trim($query['search']) . '%');
        }
        if (isset($query['start_time']) && !empty($query['start_time'])) {
            $this->where('order.create_time', '>=', strtotime($query['start_time']));
        }
        if (isset($query['end_time']) && !empty($query['end_time'])) {
            $this->where('order.create_time', '<', strtotime($query['end_time']) + 86400);
        }
        if (isset($query['delivery_type']) && !empty($query['delivery_type'])) {
            $query['delivery_type'] > -1 && $this->where('delivery_type', '=', $query['delivery_type']);
        }
        if (isset($query['extract_shop_id']) && !empty($query['extract_shop_id'])) {
            $query['extract_shop_id'] > -1 && $this->where('extract_shop_id', '=', $query['extract_shop_id']);
        }
        // 用户id
        if (isset($query['user_id']) && $query['user_id'] > 0) {
            $this->where('order.user_id', '=', (int)$query['user_id']);
        }
    }

    /**
     * 转义数据类型条件
     * @param $dataType
     * @return array
     */
    private function transferDataType($dataType)
    {
        // 数据类型
        $filter = [];
        switch ($dataType) {
            case 'delivery':
                $filter = [
                    'pay_status' => 20,
                    'delivery_status' => 10,
                    'order_status' => ['in', [10, 21]]
                ];
                break;
            case 'receipt':
                $filter = [
                    'pay_status' => 20,
                    'delivery_status' => 20,
                    'receipt_status' => 10
                ];
                break;
            case 'pay':
                $filter = ['pay_status' => 10, 'order_status' => 10];
                break;
            case 'complete':
                $filter = ['order_status' => 30];
                break;
            case 'cancel':
                $filter = ['order_status' => 20];
                break;
            case 'all':
                $filter = [];
                break;
        }
        return $filter;
    }



    /**
     * 获取csv文件中的数据
     * @return array|bool
     */
    private function getCsvData()
    {
        // 获取表单上传文件 例如上传了001.jpg
        if (!$file = \request()->file('iFile')) {
            $this->error = '请上传发货模板';
            return false;
        }
        // 设置区域信息
        setlocale(LC_ALL, 'zh_CN');
        // 打开上传的文件
        $csvFile = fopen($file->getInfo()['tmp_name'], 'r');
        // 忽略第一行(csv标题)
        fgetcsv($csvFile);
        // 遍历并记录订单信息
        $orderList = [];
        while ($item = fgetcsv($csvFile)) {
            if (!isset($item[0]) || empty($item[0]) || !isset($item[1]) || empty($item[1])) {
                $this->error = '模板文件数据不合法';
                return false;
            }
            $orderList[] = $item;
        }
        if (empty($orderList)) {
            $this->error = '模板文件中没有订单数据';
            return false;
        }
        return $orderList;
    }

    /**
     * 修改订单价格
     * @param $data
     * @return bool
     */
    public function updatePrice($data)
    {
        if ($this['pay_status']['value'] != 10) {
            $this->error = '该订单不合法';
            return false;
        }
        // 实际付款金额
        $payPrice = bcadd($data['update_price'], $data['update_express_price'], 2);
        if ($payPrice <= 0) {
            $this->error = '订单实付款价格不能为0.00元';
            return false;
        }
        return $this->save([
                'order_no' => $this->orderNo(), // 修改订单号, 否则微信支付提示重复
                'pay_price' => $payPrice,
                'update_price' => $data['update_price'] - ($this['total_price'] - $this['coupon_price']),
                'express_price' => $data['update_express_price']
            ]) !== false;
    }


    /**
     * 获取已付款订单总数 (可指定某天)
     * @param null $day
     * @return int|string
     * @throws \think\Exception
     */
    public function getPayOrderTotal($day = null)
    {
        $filter = [
            'pay_status' => 20,
            'order_status' => ['<>', 20],
        ];
        if (!is_null($day)) {
            $startTime = strtotime($day);
            $filter['pay_time'] = [
                ['>=', $startTime],
                ['<', $startTime + 86400],
            ];
        }
        return $this->getOrderTotal($filter);
    }

    /**
     * 获取指定店铺已支付订单总额
     * @author  luffy
     * @date    2019-07-09
     */
    public function getOrderTotalPrice($store_id, $day = '')
    {
        if(!empty($day)){
            $startTime = strtotime($day);
            $this->where(['pay_time' => [['>=', $startTime], ['<', $startTime + 86399]]]);
        }
        return 1;
    }

    /********************************************** 数据统计-----店铺首页start *******************************************************/
    /**
     * 获取订单总量----已付款即可        [['egt', 20], ['elt', 50]] 《---这是已付款不包含退款
     * @author  luffy
     * @date    2019-07-11
     */
    public function getOrderTotal($storeId, $type = 0, $day = '')
    {
        $prefix         = Config::get('database.prefix');
        $this->tableMod = Db::table( $prefix.'order_'.$storeId);

        switch ($type)
        {
            case 1:
                $this->tableMod->where(['a.order_state'=>[['egt', 20], ['elt', 50]],'mark'=>1]);  //已付款不包含退款
                break;
            case 2:
                $this->tableMod->where(['a.order_state'=>['egt', 20],'mark'=>1]);       //已付款包含退款
                break;
            case 3:
                $this->tableMod->where(['a.order_state'=>['egt', 60],'mark'=>1]);       //仅仅是退款的（包含退款各种状态）
                break;
            case 4:
                $this->tableMod->where(['a.order_state'=>0,'mark'=>1]);                 //已取消
                break;
            case 5:
                $this->tableMod->where(['mark'=>0]);                                    //仅仅是已删除
                break;
            default:;
        }
        if(!empty($day)){
            $this->tableMod->where(['b.payment_time'=>[['>=', $day[0]], ['<=', $day[1]]]]);                            //仅仅是已删除
        }
        $getCount   = $this->tableMod->alias('a')
            -> join($prefix.'order_relation_'.$storeId.' b','a.order_sn = b.order_sn','LEFT')
            -> where(['a.store_id'=>$storeId])->count('a.order_sn');
        return $getCount;
    }

    /**
     * 获取营业额----已付款即可        [['egt', 20], ['elt', 50]] 《---这是已付款不包含退款
     * @author  luffy
     * @date    2019-07-11
     */
    public function getIncomeTotal($store_id, $day = '')
    {
        $prefix = Config::get('database.prefix');

        $filter_1 = [];
        if(!empty($day)){
            $filter_1 = [['>=', $day[0]], ['<=', $day[1]]];
        }
        if(!empty($filter_1)){
            $list   = Db::table($prefix.'order_'.$store_id)->alias('a')->field('order_amount')
                -> join($prefix.'order_relation_'.$store_id.' b','a.order_sn = b.order_sn','LEFT')
                -> where(['a.store_id'=>$store_id,'b.payment_time'=>$filter_1])->select();                                  //不管删不删除，都算营业额
            if($list) $list = $list->toArray();
        } else {
            $list   = Db::table($prefix.'order_'.$store_id)->field('order_amount')-> where(['store_id'=>$store_id,'mark'=>1,'order_state'=>['egt', 20],])->select();
        }
        //计算总额
        $total = 0;
        if(!empty($list)){
            foreach($list as $value){
                $total += (double)$value['order_amount'];
            }
        }
        return $total;
    }

    /**
     * 获取退款总额
     * @author  luffy
     * @date    2019-10-12
     */
    public function getReundMoneyTotal($store_id, $day = '')
    {
        $filter_1 = [];
        if(!empty($day)){
            $filter_1 = [['>=', $day[0]], ['<=', $day[1]]];
        }
        if(!empty($filter_1)){
            $list   = Db::name('order_'.$store_id)->alias('a')->field('order_amount')
                -> join('order_relation_'.$store_id.' b','a.order_sn = b.order_sn','LEFT')
                -> where(['a.store_id'=>$store_id,'b.payment_time'=>$filter_1,'a.order_state'=>['egt', 60]])->select();                                  //不管删不删除，都算营业额
            if($list) $list = $list->toArray();
        } else {
            $list   = Db::name('order_'.$store_id)->field('order_amount')-> where(['store_id'=>$store_id,'mark'=>1,'order_state'=>['egt', 60]])->select();
        }
        //计算总额
        $total = 0;
        if(!empty($list)){
            foreach($list as $value){
                $total += (double)$value['order_amount'];
            }
        }
        return $total;
    }

    /**
     * 获取下单用户总量----已付款即可        [['egt', 20], ['elt', 50]] 《---这是已付款不包含退款
     * @author  luffy
     * @date    2019-07-11
     */
    public function getOrderUserTotal($store_id, $day = '')
    {
        $filter_1 = [];
        if(!empty($day)){
            $filter_1 = [['>=', $day[0]], ['<=', $day[1]]];
        }
        if(!empty($store_id)){
            if(!empty($filter_1)){
                $prefix = Config::get('database.prefix');
                $list   = $this->alias('a')->distinct(true)->field('a.buyer_id')
                    -> join($prefix.'order_relation_'.$store_id.' b','a.order_sn = b.order_sn','LEFT')
                    -> where(['a.store_id'=>$store_id,'a.order_state'=>['egt', 20],'b.payment_time'=>$filter_1])->select();
            } else {
                $list   = $this->distinct(true)->field('a.buyer_id')->where(['store_id'=>$store_id,'order_state'=>['egt', 20]])->count();   //不管删不删除，都算总量
            }
        } else {

        }
        return count($list);
    }

    /**
     * 获取退款用户总量
     * @author  luffy
     * @date    2019-10-12
     */
    public function getRefundUserTotal($store_id, $day = '')
    {
        $filter_1 = [];
        if(!empty($day)){
            $filter_1 = [['>=', $day[0]], ['<=', $day[1]]];
        }
        if(!empty($store_id)){
            if(!empty($filter_1)){
                $list   = $this->alias('a')->distinct(true)->field('a.buyer_id')
                    -> join('order_relation_'.$store_id.' b','a.order_sn = b.order_sn','LEFT')
                    -> where(['a.store_id'=>$store_id,'a.order_state'=>['egt', 70],'b.payment_time'=>$filter_1])->select();
            } else {
                $list   = $this->distinct(true)->field('a.buyer_id')->where(['store_id'=>$store_id,'order_state'=>['egt', 60]])->count();   //不管删不删除，都算总量
            }
        }
        return count($list);
    }

    /**
     * 接单
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-05
     * Time: 20:15
     */
    public function acceptOrder($order_sn = 0,$store_id = 0,$store_user_id = 0){
        Db::startTrans();
        try{
            Db::name('order')
                ->where('order_sn','=',$order_sn)
                ->update(['order_state'=>25]);
            Db::name('order_'.$store_id)
                ->where('order_sn','=',$order_sn)
                ->update(['order_state'=>25]);
            Db::name('order_relation_'.$store_id)
                ->where('order_sn','=',$order_sn)
                ->update(['receive_user'=>$store_user_id,'receive_time'=>time()]);
            Db::commit();
            return true;
        }catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }

    }

    public function getTotalMoney($storeId=STORE_ID,$start_time=0,$end_time=0,$pay_sn=0){
        $where['a.source'] = 1;
        $where['a.mark'] = 1;
        $where['c.payment_type'] = 2;
        $where['a.order_state'] = ['>',10];
        if(!empty($start_time)){
            $where['c.payment_time'] = ['>',strtotime($start_time)];
        }
        if(!empty($end_time)){
            $where['c.payment_time'] = ['<',strtotime($end_time) + 3600*24 -1];
        }
        if(!empty($start_time) && !empty($end_time)){
            $where['c.payment_time'] = ['BETWEEN',[strtotime($start_time),strtotime($end_time) + 3600 * 24 -1]];
        }
        if(!empty($pay_sn)){
            $where['b.pay_sn'] = ['like','%'.$pay_sn.'%'];
        }
        $totalMoney = Db::name('order_'.$storeId .' a')
            ->field('a.order_sn,a.store_id,a.order_state,a.source,c.payment_type,b.pay_sn,a.order_amount,c.payment_time,a.add_time')
            ->join('order_details_'.$storeId . ' b','a.order_sn = b.order_sn','LEFT')
            ->join('order_relation_'.$storeId.' c','b.order_sn = c.order_sn','LEFT')
            ->where($where)
            ->sum('a.order_amount');
            return $totalMoney;
    }

    /**
     * 微信银收
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-07
     * Time: 10:15
     */
    public function getWxList($storeId=STORE_ID,$start_time=0,$end_time=0,$pay_sn=0){

        $where['a.source'] = 1;
        $where['a.mark'] = 1;
        $where['c.payment_type'] = 2;
        $where['a.order_state'] = ['>',10];
        if(!empty($start_time)){
            $where['c.payment_time'] = ['>',strtotime($start_time)];
        }
        if(!empty($end_time)){
            $where['c.payment_time'] = ['<',strtotime($end_time) + 3600*24 -1];
        }
        if(!empty($start_time) && !empty($end_time)){
            $where['c.payment_time'] = ['BETWEEN',[strtotime($start_time),strtotime($end_time) + 3600 * 24 -1]];
        }
        if(!empty($pay_sn)){
            $where['b.pay_sn'] = ['like','%'.$pay_sn.'%'];
        }


        $data = Db::name('order_'.$storeId .' a')
            ->field('a.order_sn,a.store_id,a.order_state,a.source,c.payment_type,b.pay_sn,a.order_amount,c.payment_time,a.add_time')
            ->join('order_details_'.$storeId . ' b','a.order_sn = b.order_sn','LEFT')
            ->join('order_relation_'.$storeId.' c','b.order_sn = c.order_sn','LEFT')
            ->where($where)
            ->order('a.id DESC')
            ->paginate(15, false, ['query' => \request()->request()])->each(function ($item){
                $item['store_name'] = Store::getStoreList(true)[$item['store_id']]['store_name'];
                $item['add_time'] = date('Y-m-d H:i:s',$item['add_time']);
                $item['payment_time'] = date('Y-m-d H:i:s',$item['payment_time']);
                $item['wx_amount'] = 0;
                $status = $this->checkOrderStatus($item['order_sn']);
                if(empty($status)){
                    $item['status'] = [
                        'text'=> $status['微信订单查询api请求失败']
                    ];
                }
                if ($status['return_code'] === 'FAIL') {
                    $item['status'] = [
                        'text'=> $status['return_msg']
                    ];
                }
                elseif ($status['result_code'] === 'FAIL') {
                    $item['status'] = [
                        'text'=> $status['err_code_des']
                    ];
                }else{
                    $item['wx_amount'] = $status['total_fee']/100;
                    if ($item['order_amount'] == $status['total_fee']/100){
                        $item['status'] = [
                            'text'=> '正常'
                        ];
                    }else{
                        $item['status'] = [
                            'text'=> '异常'
                        ];
                    }
                }
                return $item;
            });
        return $data;
    }

    /**
     * 小程序微信银收
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-08
     * Time: 11:16
     */
    public function exportWxList($storeId=STORE_ID,$start_time=0,$end_time=0,$pay_sn=0){
        $where['a.source'] = 1;
        $where['a.mark'] = 1;
        $where['c.payment_type'] = 2;
        $where['a.order_state'] = ['>',10];
        if(!empty($start_time)){
            $where['c.payment_time'] = ['>',strtotime($start_time)];
        }
        if(!empty($end_time)){
            $where['c.payment_time'] = ['<',strtotime($end_time) + 3600*24 -1];
        }
        if(!empty($start_time) && !empty($end_time)){
            $where['c.payment_time'] = ['BETWEEN',[strtotime($start_time),strtotime($end_time) + 3600 * 24 -1]];
        }
        if(!empty($pay_sn)){
            $where['b.pay_sn'] = ['like','%'.$pay_sn.'%'];
        }

        $data = Db::name('order_'.$storeId .' a')
            ->field('a.order_sn,a.store_id,b.pay_sn,a.order_amount,c.payment_time,a.add_time')
            ->join('order_details_'.$storeId . ' b','a.order_sn = b.order_sn','LEFT')
            ->join('order_relation_'.$storeId.' c','b.order_sn = c.order_sn','LEFT')
            ->where($where)
            ->order('a.id DESC')
            ->select()->each(function ($item){
                $item['store_name'] = Store::getStoreList(true)[$item['store_id']]['store_name'];
                return $item;
            });
        $titleArray = ['订单编号','所属店铺','支付单号','实付金额','微信入账金额','对账状态','下单时间','付款时间'];

        $dataArray = [];
        foreach ($data as $v) {
//            $v['wx_amount'] = 0;
//            $status = $this->checkOrderStatus($v['order_sn']);
//            if(empty($status)){
//                $v['status'] = [
//                    'text'=> $status['微信订单查询api请求失败']
//                ];
//            }
//            if ($status['return_code'] === 'FAIL') {
//                $v['status'] = [
//                    'text'=> $status['return_msg']
//                ];
//            }
//            elseif ($status['result_code'] === 'FAIL') {
//                $v['status'] = [
//                    'text'=> $status['err_code_des']
//                ];
//            }else{
//                $v['wx_amount'] = $status['total_fee']/100;
//                if ($v['order_amount'] == $status['total_fee']/100){
//                    $v['status'] = [
//                        'text'=> '正常'
//                    ];
//                }else{
//                    $v['status'] = [
//                        'text'=> '异常'
//                    ];
//                }
//            }
            $dataArray[] = [
                '订单编号' => $this->filterValue($v['order_sn']),
                '所属店铺' => $v['store_name'],
                '支付单号' => $this->filterValue($v['pay_sn']),
                '实付金额' => $v['order_amount'],
                '微信入账金额' => 0,
                '对账状态' => '正常',
                '下单时间' => $this->filterTime($v['add_time']),
                '付款时间' => $this->filterTime($v['payment_time']),
            ];
        }
        // 导出csv文件
        $filename = '小程序微信收银-' . date('YmdHis');
        return export_excel($filename . '.csv', $titleArray, $dataArray);
    }

    /**
     * 调用微信订单查询接口，检验支付状态
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-07
     * Time: 11:08
     */
    public function checkOrderStatus($order_sn){
        $params['appid'] = self::APP_ID;
        $params['mch_id'] = self::MCH_ID;
        $params['sign_type'] = 'MD5';
        $params['nonce_str'] = self::createNoncestr();
        $params['out_trade_no'] = $order_sn;
        $params['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];
        $params['sign'] = self::MakeSign($params);
        if(array_key_exists("transaction_id", $params) ||array_key_exists("out_trade_no", $params)){
            $res_xml = self::curl(self::ORDER_QUERY,self::dataToXml($params));
            $res_arr = self::xmlToArray($res_xml);
            return $res_arr;
        }
    }


    /**
     * 创建新订单
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-03
     * Time: 11:42
     */
    public function createOrder($user, &$order)
    {
        if($os = $this->where('order_sn','=',$order['order_sn'])
            ->find()){
            $this->errorCode = 2;
            $this->error = '订单已生成，请前往支付';
            return false;
        }
//        dump($order);die;
        // 创建新的订单
        $id = $this->transaction(function () use (&$order, $user) {
            //设置折扣/优惠金额
            $this->setDiscountPrice($order);
            //设置分销金额
            $this->setFxPrice($order);
            // 设置订单优惠券信息
            $this->setCouponPrice($order,$user['id']);
            //余额付款验证用户金额
            $this->validateOrderForm($user, $order);
            // 记录订单信息
            $id = $this->add($user['id'], $order);
            // 保存订单商品信息
            $this->saveOrderGoods($user['id'], $order);
            // 获取订单详情
//            $detail = self::getUserOrderDetail($id, $user['id']);
//            // 记录分销商订单
//            DealerOrderModel::createOrder($detail,OrderTypeEnum::MASTER);
            return $id;
        });

        // 余额支付标记订单已支付
        if ($id && $order['pay_type'] == 3) {
            $this->paymentByBalance($order['order_sn']);
        }
        // 线下支付
        if ($id && $order['pay_type'] == 4) {
            $this->paymentByOffline($order['order_sn']);
        }
        return $id;
    }

    /**
     * 订单详情
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-11
     * Time: 10:58
     */
    public static function getUserOrderDetail($order_id, $user_id)
    {
        if (!$order = self::get(['order_id' => $order_id, 'buyer_id' => $user_id])) {
            throw new BaseException(['msg' => '订单不存在']);
        }

        $detail = Db::name('order_'.$order['store_id'])
            ->field('a.*,b.fx_user_id,b.coupon_discount,l.user_coupon_id,l.coupon_id,cp.type as coupon_type')
            ->alias('a')
            ->join('order_details_'.$order['store_id'].' b','a.order_sn = b.order_sn','LEFT')
            ->join('order_relation_'.$order['store_id'].' c','b.order_sn = c.order_sn','LEFT')
            ->join('coupon_log l','a.order_sn = l.order_sn','LEFT')
            ->join('coupon cp','l.coupon_id = cp.id','LEFT')
            ->where('a.order_sn','=',$order['order_sn'])
            ->where('a.store_id','=',$order['store_id'])
            ->where('a.mark','=',1)
            ->find();
        $detail['order_id'] = $order['order_id'];
        $detail['order_goods'] = Db::name('order_goods')
                ->where('order_id','=',$order['order_sn'])
                ->select()->toArray();
        return $detail;
    }

    /**
     * 构建微信支付请求
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-05
     * Time: 16:05
     */
    public function paymentByWechat($user,$order)
    {
        return PaymentService::wechat(
            array_column($order['goods_list'],'goods_id'),
            $order['store_id'],
            $order['order_sn'],
            $order['order_pay_price'],
            OrderTypeEnum::MASTER
        );
    }


    /**
     * 新增订单记录
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-03
     * Time: 15:44
     */
    private function add($user_id, &$order)
    {
        $_order = [
            'order_sn' => $order['order_sn'],
            'store_id' => $order['store_id'],
            'buyer_id' =>$user_id,
            'cid' => $order['coupon_id'],
            'sendout' => $order['sendout'],
            'goods_amount' => $order['order_total_price'],
            'order_amount' => $order['order_pay_price'],
            'payment_code' => $this->payment_type[$order['pay_type']],
            'add_time' => time()
        ];

        $orderData = [
            'order_sn'          => $order['order_sn'],
            'store_id'          => $order['store_id'],
            'buyer_id'          => $user_id,
            'goods_amount'      => $order['order_total_price'],
            'order_amount'      => $order['order_pay_price'],
            'sendout'           => $order['sendout'],
            'source'            => $order['source'],
            'add_time'          => time()
        ];
        //订单详情表信息
        $orderDetailsData = [
            "order_sn"          => $order['order_sn'],
            'address_id'        => isset($order['address_id']) ? $order['address_id'] :0,
            "shipping_fee"      => isset($order['shipping_fee']) ? $order['shipping_fee'] : 0,
            "seller_msg"        => isset($order['seller_msg']) ? $order['seller_msg'] : '',
            "fx_user_id"        => isset($order['fx_user_id']) ? $order['fx_user_id'] : 0,
            'fx_money'          => isset($order['fx_price']) ? $order['fx_price'] : 0,
            'point_discount'    => isset($order['pd_amount']) ? $order['pd_amount'] : 0,
            'coupon_discount'   => isset($order['coupon_price']) ? $order['coupon_price'] : 0,
            'number_order'      => '',
            'underline_pay_money'   => isset($order['underline_pay_money']) ? $order['underline_pay_money'] : '',
            'store_source_id'       => isset($order['store_source_id']) ? $order['store_source_id'] : 0,
            'source_delivery_fee'   => isset($order['source_delivery_fee']) ? $order['source_delivery_fee'] : '',
            'source_address'        => isset($order['source_address']) ? $order['source_address'] : '',
            'sendout_time'      => $order['sendout_time']
        ];

        if ($order['source'] == 3) {
            $orderDetailsData['valet_order_user_id'] = isset($order['valet_order_user_id']) ? $order['valet_order_user_id'] : USER_ID;
            $orderDetailsData['valet_order_time'] = time();
            $orderDetailsData['discount_num'] = isset($order['discount_num']) ? $order['discount_num'] : 10;
            $orderDetailsData['discount'] = isset($order['reduced_price']) ? $order['reduced_price'] : 0;
        }

        //订单关联表信息
        $orderRelationData = [
            "order_sn" => $order['order_sn'],
            'payment_source' => isset($order['payment_source']) ? $order['payment_source'] : 1758421,
        ];

        $userOrderData = [
            'user_id' => $user_id,
            'store_id' => isset($order['store_id']) ? $order['store_id'] : 0,
            'order_sn' => isset($order['order_sn']) ? $order['order_sn'] : '',
            'pay_money' => isset($order['order_pay_price']) ? $order['order_pay_price'] : 0,
            'add_time' => time()
        ];
        $_id = Db::name('order')->insertGetId($_order);
        $id = Db::name('order_'.$order['store_id'])->insertGetId($orderData);
        $orderDetailsData['id'] = $orderRelationData['id'] = $id;
        $orderDetailsData['order_id'] = $orderRelationData['order_id'] = $id;
        Db::name('order_details_'.$order['store_id'])->insert($orderDetailsData);
        Db::name('order_relation_'.$order['store_id'])->insert($orderRelationData);
        Db::name('user_order')->insert($userOrderData);
        return $_id;
    }


    /**
     * 保存订单商品信息
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-03
     * Time: 17:16
     */
    private function saveOrderGoods($user_id, &$order)
    {
        // 订单商品列表
        $goodsList = [];
        foreach ($order['goods_list'] as $goods) {
            $goodsList[] = [
                'order_id' => $order['order_sn'],
                'goods_id' => $goods['id'],
                'goods_name' => $goods['goods_name'],
                'goods_price' => isset($goods['goods_sku']['price']) ? $goods['goods_sku']['price'] : $goods['shop_price'],
                'goods_num' => $goods['total_num'], //商品数量
                'goods_image' => $goods['original_img'],
                'goods_pay_price' => $goods['member_goods_price'],
                'spec_key_name' => $goods['spec_key_name'], //规格名
                'spec_key' => $goods['spec_key'], //规格
                'prom_type' => $goods['prom_type'], //0 普通商品,1 限时抢购,2团购,3促销优惠,4,组合销售,5.买赠活动
                'prom_id' => $goods['prom_id'], //活动ID
                'store_id' => $goods['store_id'], //店铺ID
                'order_state' => 10, //'订单状态：0(已取消)10(默认):未付款;20:已付款;30:已发货;40:区域配送;50:已收货;',
                'shipping_store_id' => $goods['store_id'], //配送区域站点ID
                'add_time' => time(),//添加时间
                'good_id' => $goods['goods_id'],
                'deduction' => $goods['deduction'],
                'buyer_id' => $user_id
            ];
        }
        $orderGoods = new OrderGoods();
        return $orderGoods->allowField(true)->saveAll($goodsList);
    }

    /**
     * 优惠信息
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-05
     * Time: 11:27
     */
    public function setDiscountPrice(&$order){
        if($order['discount_type'] == 1){
            // 计算订单金额 (打折后)
            $discount_price = bcmul(($order['discount_num'] / 10),$order['order_total_price'],2);
            // 减价
            $order['reduced_price'] = bcsub($order['order_total_price'],$discount_price,2);
        }
    }

    /**
     * 分销金额
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-05
     * Time: 11:27
     */
    public function setFxPrice(&$order){
        $order['fx_price'] = 0;
        if($order['fx_discount'] && $order['fx_discount'] > 0){
            // 计算分销金额 (分销后)
            if($order['user_coupon_id'] > 0){
                // 获取优惠券信息
                $userCoupon = UserCoupon::detail($order['user_coupon_id']);
                if (!$userCoupon || !isset($userCoupon['coupon'])) throw new BaseException(['msg' => '未找到优惠券信息']);
                // 计算分销金额 (使用优惠价后)
                $fx_price = bcmul(bcsub($order['order_total_price'], $userCoupon['coupon']['discount'], 2),$order['fx_discount'] / 100,2);
            }else{
                //未使用优惠券
                $fx_price = bcmul(($order['fx_discount'] / 100),$order['order_total_price'],2);

            }
            // 减价
            $order['fx_price'] = $fx_price;
        }
    }

    /**
     * 用卷信息
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-05
     * Time: 11:19
     */
    private function setCouponPrice(&$order,$user_id)
    {
        $orderTotalPrice = bcsub(bcsub($order['order_total_price'], $order['reduced_price'], 2),$order['fx_price'],2);
        $order['coupon_price'] = 0.00;
        if ($order['user_coupon_id'] > 0) {
            // 获取优惠券信息
            $userCoupon = UserCoupon::detail($order['user_coupon_id']);
            if (!$userCoupon || !isset($userCoupon['coupon'])) throw new BaseException(['msg' => '未找到优惠券信息']);
            // 计算订单金额 (抵扣后)
            $orderTotalPrice = bcsub($orderTotalPrice, $userCoupon['coupon']['discount'], 2);
            // 记录订单信息
            $order['coupon_price'] = $userCoupon['coupon']['discount'];
            // 设置优惠券使用状态
            $couponLog = [
                'user_coupon_id' => $userCoupon['id'],
                'coupon_id' => $userCoupon['coupon']['id'],
                'user_id' => $user_id,
                'order_sn' => $order['order_sn'],
                'add_time' => time()
            ];
            (new CouponLog)->allowField(true)->save($couponLog);

        }
        $orderTotalPrice <= 0 && $orderTotalPrice = '0.01';
        $order['order_pay_price'] = $orderTotalPrice;
        return true;
    }


    /**
     * 余额支付标记已支付
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-03
     * Time: 18:25
     */
    public function paymentByBalance($orderNo)
    {
        // 获取订单详情
        $model = new \app\task\model\Order;
        $order = $model->payDetail($orderNo);
        if($order['order_state'] != 10 ){
            $this->error = '该订单已被取消或已付款';
            return false;
        }
        // 发起线下支付
        $status = $model->paySuccess(3,[],$order);
        if (!$status) {
            $this->error = $model->error;
        }
        return $status;
    }

    /**
     * 线下支付
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-03
     * Time: 18:25
     */
    public function paymentByOffline($orderNo)
    {
        // 获取订单详情
        $model = new \app\task\model\Order;
        $order = $model->payDetail($orderNo);
        if($order['order_state'] != 10 ){
            $this->error = '该订单已被取消或已付款';
            return false;
        }
        // 发起线下支付
        $status = $model->paySuccess(4,[],$order);
        if (!$status) {
            $this->error = $model->error;
        }
        return $status;
    }

    /**
     * 生成分销订单
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-10
     * Time: 18:40
     */
    public function addFxOrder($order,$source){
        if($order['fx_user_id'] && $order['fx_user_id'] > 0){
            $o = $this->alias('a')
                ->field('a.order_id,a.cid,b.type')
                ->join('coupon b','a.cid = b.id','LEFT')
                ->where('a.order_sn','=',$order['order_sn'])
                ->find();
            //购买单个商品使用兑换券，不生成分销订单
            if (($order['order_amount'] <= 0) && ($o['cid'] > 0)) {
                if (count($order['order_goods']) <= 1 && ($o['type'] == 2)) {
                    return true;
                }
            }
           $fxuserInfo = FxUser::get($order['fx_user_id']);
            //获取一级分销信息
            $fxPInfo = FxUser::getLevel1Info($fxuserInfo['id']);
            $fxRuleInfo = FxRule::get($fxPInfo['rule_id']);
            $fxCommissionPercent = bcsub($fxRuleInfo['lev3_prop'], $fxuserInfo['discount']);
            $fxOrderData = array(
                'order_id' => $o['order_id'],
                'order_sn' => $order['order_sn'],
                'pay_money' => $order['order_amount'],
                'fx_money' => number_format(($order['goods_amount']-$order['coupon_discount']) * $fxuserInfo['discount'] * 0.01, 2, '.', ''),
                'source' => $source,
                'user_id' => $order['buyer_id'],
                'fx_user_id' => $order['fx_user_id'],
                'rule_id' => $fxPInfo['rule_id'],
                'store_cate' => STORE_CATE,
                'store_id' => $order['store_id'],
                'add_time' => time(),
                'add_user' => $order['buyer_id'],
                'fx_discount'=>$fxuserInfo['discount'],
                'fx_commission_percent'=>$fxCommissionPercent
            );

            $fxOrderModel = new FxOrder;
            $fxOrder = $fxOrderModel->where('order_sn','=',$order['order_sn'])->find();
            if($fxOrder){
                $fxOrder->allowField(true)->save($fxOrderData);
            }else{
                $fxOrderModel->allowField(true)->save($fxOrderData);
            }
            $type = 9;
            $fxAccountInfo = FxUserAccount::get(['user_id'=>$order['buyer_id']]);
            if($source == 1){
                if(empty($fxAccountInfo)){
                    $type = 5;
                }elseif($fxAccountInfo && ($fxAccountInfo['fx_user_id'] != $order['fx_user_id'])){
                    $type = 6;
                }
            }elseif($source == 2){
                if(empty($fxAccountInfo)){
                    $type = 7;
                }elseif($fxAccountInfo && ($fxAccountInfo['fx_user_id'] != $order['fx_user_id'])){
                    $type = 8;
                }
            }
            $this->addFxUser($order['fx_user_id'],$order['buyer_id'],$type);

            return true;

        }
    }

    /**
     * 会员绑定分销人员
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-10
     * Time: 20:11
     */
    public function addFxUser($fx_user_id,$user_id,$type){
        $info = FxUserAccount::get(['user_id'=>$user_id]);
        $oldFxUser = FxUser::get($fx_user_id);
        if( empty($info) ){
            (new FxUserAccount)->allowField(true)->save(
                [
                    'fx_user_id' => $fx_user_id,
                    'user_id'    => $user_id,
                ]

            );
        } elseif( $fx_user_id != $info['fx_user_id'] ) {
            $info->fx_user_id = $fx_user_id;
            $info->user_id = $user_id;
            $info->save();

        }
        $old_code = 0;
        if(in_array($type, array(6,8))){
            $old_code = $info['fx_code'];
        }
        if(in_array($type, array(5,6))){
            $create_user = USER_ID;
        } else {
            $create_user = $user_id;
        }
        (new FxUserChangeLog)->allowField(true)->save(
            [
                'type' => $type,
                'user_arr' => $user_id,
                'old_fx_code' => $old_code,
                'new_fx_code' => $oldFxUser['fx_code'],
                'create_user' => $create_user
            ]
        );

        return true;

    }



    /**
     * 表单验证 (订单提交)
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-03
     * Time: 13:06
     */
    private function validateOrderForm($user, &$order)
    {
        // 余额支付时判断用户余额是否足够
        if ($order['pay_type'] == PayTypeEnum::BALANCE) {
            if (!isset($user['amount']) || $user['amount'] < $order['order_pay_price']) {
                throw new BaseException(['msg' => '用户余额不足，无法使用余额支付']);
            }
        }
        return true;
    }

    /**
     * 是否存在错误
     * @return bool
     */
    public function hasError()
    {
        return !empty($this->error);
    }

    /**
     * 获取微信金额
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-08
     * Time: 12:08
     */
    public  function getOrderAmount($pay_sn){
        if(empty($pay_sn)){

            return 0;
        }

        $params['appid'] = self::APP_ID;
        $params['mch_id'] = self::MCH_ID;
        $params['sign_type'] = 'MD5';
        $params['nonce_str'] = self::createNoncestr();
        $params['transaction_id'] = $pay_sn;
        $params['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];
        $params['sign'] = self::MakeSign($params);
        if(array_key_exists("transaction_id", $params) ||array_key_exists("out_trade_no", $params)){
            $res_xml = self::curl(self::ORDER_QUERY,self::dataToXml($params));
            $res_arr = self::xmlToArray($res_xml);
//            dump($res_arr);die;
            if($res_arr && isset($res_arr['return_code']) && $res_arr['return_code'] == 'SUCCESS' && isset($res_arr['result_code']) && $res_arr['result_code'] == 'SUCCESS'){
                if($res_arr['trade_state'] == 'SUCCESS' ){

                    return $res_arr['total_fee'] /100;
                }
                return $res_arr['trade_state_desc'];

            }
            return '接口异常';
        }
    }

    private static function curl($url='',$xml='',$second = 30)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            die($error);
        }
    }

    private static function createNoncestr($length = 32 ){

        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";

        $str ="";

        for ( $i = 0; $i < $length; $i++ ) {

            $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);

        }

        return $str;

    }


    ///
    /**
     * 输出xml字符
     */
    private static function dataToXml($para = array())
    {
        if(!(is_array($para) &&count($para)>0)) die("数组数据异常！");
        $xml = "<xml>";
        foreach ($para as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }

    /**
     * 将xml转为array
     */
    private static function xmlToArray($xml = '')
    {
        if(!$xml) die("xml数据为空!");
        libxml_disable_entity_loader(true);//禁止引用外部xml实体
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }


    private static function MakeSign( $params ){
        //签名步骤一：按字典序排序数组参数
        $key = 'Sem68GvhBu2ag5ncyJxsbDrXzZAHT3VK';
        ksort($params);
        $string = self::ToUrlParams($params);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".$key;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    private static function ToUrlParams($params)
    {
        $string = '';
        if (!empty($params)){
            $array = array();
            foreach ($params as $key => $value){
                $array[] = $key.'='.$value;
            }
            $string = implode("&",$array);
        }
        return $string;
    }

    /**
     * 查询公共统计数据
     */
    public function tipsData(){
        if( T_GENERAL ){
            $store_id   = STORE_ID;
        } else {
            $store_id   = SELECT_STORE_ID;
        }
        //查询待接单
        $_1 = Db::name('order_'.$store_id)->where(['order_state'=>20, 'mark'=>1])->count();
        //查询待退款
        $_2 = Db::name('order_'.$store_id)->where(['order_state'=>60, 'mark'=>1])->count();
        return [$_1,$_2,($_1+$_2)];
    }
    /********************************************** 数据统计-----店铺首页end  *******************************************************/
}
