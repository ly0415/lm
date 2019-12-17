<?php

namespace app\api\model;


use think\Db;
use think\Config;
use app\common\exception\BaseException;
use app\api\model\User                  as UserModel;
use app\common\model\Order              as OrderModel;
use app\common\model\Cart               as CartModel;
use app\api\model\OrderGoods            as OrderGoodsModel;
use app\api\model\CouponLog             as CouponLogModel;
use app\api\service\Payment             as PaymentService;
use app\common\enum\OrderType           as OrderTypeEnum;
use app\common\enum\order\PayStatus     as PayStatusEnum;
use app\common\enum\order\PayType       as PayTypeEnum;
use app\common\enum\DeliveryType        as DeliveryTypeEnum;

/**
 * 订单模型
 * Class Order
 * @package app\api\model
 */
class Order extends OrderModel{

    /**
     * 立即购买校验
     * @author  luffy
     * @date    2019-11-28
     */
    public function buyNowCkeck($user_id, $store_goods_id, $key, $price, $num, $delivery_type){
        if(empty($user_id) || empty($store_goods_id) || empty($num) || empty($delivery_type)){
            $this->error = '参数错误';
            return false;
        }
        //删除购物车当前用户当前商品
        $CartModel  = new CartModel;
        $CartModel->where(['user_id'=>$user_id,'is_buy'=>1,'goods_id'=>$store_goods_id])->delete();
        //校验商品信息
        if(!empty($key)){
            $store_goods_spec_price_info    = Db::name('store_goods_spec_price')->where(['store_goods_id'=>$store_goods_id, 'key'=>$key, 'mark'=>1])->find();
            if(empty($store_goods_spec_price_info) || ($price != $store_goods_spec_price_info['price']) || ($store_goods_spec_price_info['goods_storage'] < $num) ){
                $this->error = '网络异常';
                return false;
            }
            return $store_goods_spec_price_info;
        }
    }

    /**
     * 校验订单商品信息
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-05
     * Time: 11:59
     */
    public function checkOrderGoods($orderList){
        // 购物车商品列表
        $goodsList = [];
        $cartModel = new CartModel();
        // 格式化购物车数据列表
        foreach ($orderList['goods'] as $goods) {
            $cartModel->where(['user_id'=>$orderList['buyer_id'],'is_buy'=>2,'spec_key'=>$goods['spec_key'],'store_id'=>$orderList['store_id'],'goods_id'=>$goods['store_goods_id'],'delivery_type'=>$orderList['sendout']])->delete();
            if(!$goodsInfo =StoreGoods::detail($goods['store_goods_id'])){
                $this->setError('很抱歉，商品 [' . $goods['goods_name'] . '] 不存在');
            }
            // 判断商品是否下架
            if ($goodsInfo['is_on_sale']['value'] != 1 || $goodsInfo['mark'] != 1) {
                $this->setError('很抱歉，商品 [' . $goods['goods_name'] . '] 已下架');
            }

            // 商品sku不存在则自动删除
            if (!$goods_sku = StoreGoodsSpecPrice::getSpecPriceStock($goods['store_goods_id'],$this->formatSpec($goods['spec_key']))) {
                $this->setError('很抱歉，商品 [' . $goods['goods_name'] . '] 不存在');
            }

            // 判断商品库存
            if ($goods['goods_num'] > $goods_sku['stock']) {
                $this->setError('很抱歉，商品 [' . $goods['goods_name'] . '] 库存不足');
            }
            // 商品sku信息
            $cart['user_id'] = $orderList['buyer_id'];
            $cart['store_id'] = $orderList['store_id'];
            $cart['goods_id'] = $goods['store_goods_id'];
            $cart['goods_sn'] = $goodsInfo['goods_sn'];
            $cart['goods_name'] = $goodsInfo['goods_name'];
            $cart['goods_price'] = $goods_sku['price'];
            $cart['market_price'] = $goodsInfo['market_price'];
            $cart['goods_num'] = $goods['goods_num'];
            $cart['spec_key'] = $goods['spec_key'];
            $cart['spec_key_name'] = $goods['spec_key_name'];
            $cart['is_buy'] = 2;
            $cart['add_time'] = time();
            $cart['order_from'] = 2;
            $cart['delivery_type'] = $orderList['sendout'];
            $goodsList[] = $cart;
        }
        return $goodsList;
    }



    /**
     * 是否存在错误
     * @return bool
     */
    public function hasError(){
        return !empty($this->error);
    }

    /**
     * 微信支付
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-15
     * Time: 21:21
     */
    public function paymentByWechat($order_sn,$openId,$payPrice,$service,$orderType =OrderTypeEnum::MASTER)
    {
        return PaymentService::wechat(
            $order_sn,
            $openId,
            $payPrice,
            $service,
            $orderType
        );
    }

    /**
     * 余额支付标记订单已支付
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-29
     * Time: 20:03
     */
    public function paymentByBalance($orderSn,$storeId)
    {
        // 获取订单详情
        $model = new \app\task\model\Order;
        $order = $model->payDetail($orderSn,$storeId);
        // 发起余额支付
        $status = $order->paySuccess(PayTypeEnum::BALANCE);
        if (!$status) {
            $this->error = $order->error;
        }
        return $status;
    }



    /**
     * 取消订单
     * @return bool|false|int
     * @throws \Exception
     */
    public function cancel()
    {
        $order = Db::name('order_'.$this->store_id)
            ->where('order_sn','=',$this->order_sn)
            ->where('buyer_id','=',$this->buyer_id)
            ->where('mark','=',1)
            ->find();
        if(empty($order)){
            $this->error = '订单不存在';
            return false;
        }


        if ($order['order_state'] == 0) {
            $this->error = '订单已失效';
            return false;
        }
        if ($order['order_state'] == 30) {
            $this->error = '已发货订单不可取消';
            return false;
        }

//        $pointLog = (new PointLog)->where('order_sn','=',$this->order_sn)
//            ->where('userid','=',$this->buyer_id)
//            ->find();
        //事务开启
        $this->transaction(function () {
            // 删除优惠券
            CouponLogModel::where('order_sn','=',$this->order_sn)
            ->where('user_id','=',$this->buyer_id)
            ->delete();
            //退还积分
//            if($pointLog && isset($pointLog['userid'])){
//                $userInfo = UserModel::where('id','=',$pointLog['userid'])
//                    ->find();
//                UserModel::where('id','=',$pointLog['userid'])->setInc('point',$pointLog['expend']);
//                //积分日志
//                (new PointLog)->allowField(true)->save([
//                    'operator' => '--',
//                    'username' => $userInfo['phone'],
//                    'add_time' => time(),
//                    'deposit' => $pointLog['expend'],
//                    'expend' => '-',
//                    'note' => "取消订单：" . $this->order_sn . " 获取：" . $pointLog['expend'] . "睿积分",
//                    'userid' => $pointLog['userid'],
//                ]);
//            }

            // 更新订单状态
            Db::name('order')
                ->where('order_sn','=',$this->order_sn)
                ->update(['order_state'=> 0]);
             Db::name('order_'.$this->store_id)
                ->where('order_sn','=',$this->order_sn)
                ->update(['order_state'=> 0]);
            Db::name('order_goods')
                ->where('order_id','=',$this->order_sn)
                ->update(['order_state'=> 0]);
            Db::name('order_relation_'.$this->store_id)
                ->where('order_sn','=',$this->order_sn)
                ->update(['cancel_time'=> time()]);
        });
        return true;
    }

    /**
     * 删除订单
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-15
     * Time: 10:10
     */
    public function del(){
        $order = Db::name('order_'.$this->store_id)
            ->where('order_sn','=',$this->order_sn)
            ->where('buyer_id','=',$this->buyer_id)
            ->where('mark','=',1)
            ->find();
        if(empty($order)){
            $this->error = '该订单不存在';
            return false;
        }
        if($order['order_state'] !== 0){
            $this->error = '该订单不可删除';
            return false;
        }
        // 启动事务
        Db::startTrans();
        try{
            Db::name('order_'.$this->store_id)
            ->where('id','=',$order['id'])
            ->update(['mark'=>0]);
            $this->mark = 2;
            $this->save();
            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }

    }


    /**
     * 获取订单
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-13
     * Time: 09:24
     */
    public static function getUserOrderDetail($order_sn, $user_id)
    {
        if (!$order = self::get([
            'order_sn' => $order_sn,
            'buyer_id' => $user_id,
            'mark' => 1
        ])
        ) {
            throw new BaseException(['msg' => '订单不存在']);
        }
        return $order;
    }

    /**
     * 订单详情
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-15
     * Time: 10:00
     */
    public static function getUserOrderDetails($order_sn, $user_id)
    {
        if (!$order = self::get([
            'order_sn' => $order_sn,
            'buyer_id' => $user_id,
            'mark' => 1
        ])
        ) {
            throw new BaseException(['msg' => '订单不存在']);
        }


        //订单信息
        $data = Db::name('order_'.$order['store_id'])
            ->alias('a')
            ->field('a.*,b.discount,b.coupon_discount,b.shipping_fee,b.delivery,b.seller_msg,b.sendout_time,b.discount_num,b.discount_num,b.discount_num,c.cancel_time,c.payment_type,c.payment_time,c.ship_time,c.delivery_time,c.receipt_time,c.refund_time')
            ->join('order_details_'.$order['store_id'].' b','a.order_sn = b.order_sn','LEFT')
            ->join('order_relation_'.$order['store_id'].' c','b.order_sn = c.order_sn','LEFT')
            ->where('a.order_sn','=',$order['order_sn'])
            ->find();

        $coupon = Db::name('coupon_log')
            ->alias('cl')
            ->field('cp.*')
            ->join('coupon cp','cl.coupon_id = cp.id','LEFT')
            ->where('cl.order_sn','=',$order['order_sn'])
            ->where('cl.user_id','=',$user_id)
            ->find();
        if(!empty($coupon)){

            switch($coupon['type']){
                case 2 :
                    $data['voucherStr'] = "封顶{$coupon['money']}元兑换";
                    break;
                case 1 :
                    $data['voucherStr'] = "满{$coupon['money']}元抵扣{$coupon['discount']}元";
                    break;
            }
        }else{
            $data['voucherStr'] = "暂无";//折扣劵
        }
        $data['sendout'] = ['text'=>(new static)->delivery_type[$data['sendout']],'value'=>$data['sendout']];

        $data['delivery'] = $data['delivery'] ? unserialize($data['delivery']) : '';
        $_time = time();
        //订单过期时间
        $expireTime = $data['add_time'] + Cart::EXPECT_TIME - $_time > 0  ? $data['add_time'] + Cart::EXPECT_TIME - $_time : 0;
        //获取门店信息
        $store = Store::getStoreList(true,'',['id','store_name','logo','store_mobile']);
        $storeInfo = isset($store[$data['store_id']]) ? $store[$data['store_id']] : [];
        $orderGoods = Db::name('order_goods')
            ->alias('og')
            ->field('og.*,sg.attributes,sg.is_free_shipping')
            ->join('store_goods sg','sg.id = og.goods_id','LEFT')
            ->where('og.order_id','=',$order['order_sn'])
            ->select()->toArray();

            return ['orderData'=>$data,
            'orderGoodsData'=>$orderGoods,
            'expireTime'=>$expireTime,
            'storeInfo'=>$storeInfo];

    }


    /**
     * 获取订单详情
     * @author  luffy
     * @date    2019-07-28
     */
    public function getOrderDetail($order_sn,$user_id = 0){
        //获取订单ID
        $filter['order_sn'] = $order_sn;
        $filter['mark'] = 1;
        $user_id > 0 && $filter['buyer_id'] = $user_id;
        if (!$orderInfo = self::get($filter)
        ) {
            throw new BaseException(['msg' => '订单不存在']);
        }

        $result = Db::name( 'order_'.$orderInfo->store_id)
            ->alias('a')
            ->field('a.order_sn,a.store_id,a.add_time,a.sendout,a.buyer_id,a.order_state,a.goods_amount,a.order_amount,a.evaluation_state,b.sendout_time,b.number_order,b.seller_msg,b.delivery,b.shipping_fee,c.payment_type,c.payment_time,c.receive_time,c.comment_time,d.username,d.phone')
            ->join('order_details_'.$orderInfo->store_id.' b', 'b.order_sn = a.order_sn','LEFT')
            ->join('order_relation_'.$orderInfo->store_id.' c', 'c.order_sn = a.order_sn','LEFT')
            ->join('user d', 'd.id = a.buyer_id','LEFT')
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
    private function toSwitch($value){
        //根据订单号获取订单商品
        $value['goods']                 = (new OrderGoodsModel)->getOrderGoods($value['order_sn']);
        $value['format_delivery_type']  = !empty($value['sendout']) ? $this->delivery_type[$value['sendout']] : '';
        $value['format_order_state']    = isset($value['order_state']) ? $this->order_state[$value['order_state']] : '';
        $value['format_payment_type']   = !empty($value['payment_type']) ? $this->payment_type[$value['payment_type']] : '未付款';
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
        return $value;
    }

    /**
     * 判断商品库存不足 (未付款订单)
     * @param $goodsList
     * @return bool
     */
//    public function checkGoodsStatusFromOrder(&$goodsList)
//    {
//        foreach ($goodsList as $goods) {
//            // 判断商品是否下架
//            if (!$goods['goods'] || $goods['goods']['goods_status']['value'] != 10) {
//                $this->setError('很抱歉，商品 [' . $goods['goods_name'] . '] 已下架');
//                return false;
//            }
//            // 付款减库存
//            if ($goods['deduct_stock_type'] == 20 && $goods['sku']['stock_num'] < 1) {
//                $this->setError('很抱歉，商品 [' . $goods['goods_name'] . '] 库存不足');
//                return false;
//            }
//        }
//        return true;
//    }

    /**
     * 判断商品库存不足
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-24
     * Time: 17:39
     */
    public function checkGoodsStatusFromOrder($orderGoods)
    {
        foreach ($orderGoods as $item){
            if($item['prom_type'] > 0 && $item['prom_id'] > 0){
                if(!$model = SpikeGoods::detail(['spike_id'=>$item['prom_id'],'store_goods_id'=>$item['goods_id']])){
                    $this->setError('很抱歉，秒杀商品不存在');
                    return false;
                }
                if($model['goods_num'] <= 0 || $model['goods_num'] < $item['goods_num'] || $model['goods_num'] <= OrderGoods::getGoodsRemain($model['store_goods_id'],$model['spike_id'],$model['activity']['store_id'],1,['b.order_state'=>['GT',10]])){
                    $this->setError('很抱歉，秒杀库存不足 ');
                    return false;
                }
                if(!$stock = StoreGoodsSpecPrice::getSpecPriceStock($item['goods_id'],$item['spec_key'])){
                    $this->setError('很抱歉，商品 [' . $item['goods_name'] . '] 库存不足');
                    return false;
                }
                if($stock['stock'] < $item['goods_num']){
                    $this->setError('很抱歉，商品 [' . $item['goods_name'] . '] 库存不足');
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 校验订单状态是否可支付
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-24
     * Time: 17:45
     */
    public function checkOrderStatusFromOrder($order){
        if(($order['add_time'] + 900) < time()){
            $this->setError('订单已失效');
            return false;
        }
        if($order['order_state'] == 0){
            $this->setError('订单已取消');
            return false;
        }
        if($order['order_state'] > 10 ){
            $this->setError('订单已支付');
            return false;
        }
        return true;
    }

    /**
     * 判断当前订单是否允许核销
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-17
     * Time: 14:29
     */
    public function checkExtractOrder(&$order)
    {
        if (
        ($order['order_state'] == PayStatusEnum::SUCCESS || $order['order_state'] == 25)
            && $order['sendout'] == DeliveryTypeEnum::EXTRACT
        ) {
            return true;
        }
        $this->setError('该订单不能被核销');
        return false;
    }

    /**
     * 当前订单是否允许申请售后
     * @return bool
     */
//    public function isAllowRefund()
//    {
//        // 允许申请售后期限
//        $refund_days = SettingModel::getItem('trade')['order']['refund_days'];
//        if ($refund_days == 0) {
//            return false;
//        }
//        if (time() > $this['receipt_time'] + ((int)$refund_days * 86400)) {
//            return false;
//        }
//        if ($this['receipt_status']['value'] != 20) {
//            return false;
//        }
//        return true;
//    }

    /**
     * 设置错误信息
     * @param $error
     */
    private function setError($error)
    {
        empty($this->error) && $this->error = $error;
    }

    /**
     * 格式化规格
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-02
     * Time: 18:19
     */
    public function formatSpec($specKey){
        $spec_arr = [];
        if ($specKey) {
            $key_arr = explode('_', $specKey);
            $key_pailie = arrangement($key_arr, count($key_arr));
            foreach ($key_pailie as $v) {
                $spec_arr[] = implode('_', $v);
            }
        }
        return $spec_arr;
    }

    /**
     * 用户订单列表
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-12
     * Time: 14:05
     */

    public function orderList($user_id,$page = 1, $type = 'all'){

        //        // 筛选条件
        $filter = [];
//        // 订单数据类型
        switch ($type) {
            case 'all':
                break;
            case 'payment';
                $filter['order_state'] = 10;
                break;
            case 'delivery';
                $filter['order_state'] = ['in',[20,25]];
                break;
            case 'received';
                $filter['order_state'] = ['in',[30,40]];
                break;
            case 'comment';
                $filter['order_state'] = 50;
                $filter['evaluation_state'] = 0;
                break;
            case 'refund';
                $filter['order_state'] = ['in',[60,70]];
                break;
        }

        $res = $this->field('order_id,order_sn,store_id')
            ->where('buyer_id','=',$user_id)
            ->where('mark','=',1)
            ->where('store_id','neq',84)
            ->where($filter)
            ->order('order_id DESC')
            ->page($page,Config::get('paginate.list_rows'))
            ->select()->toArray();

        $data = [];
        foreach ($res as $k => $v){

            $info = Db::name('order_'.$v['store_id'])
                ->alias('a')
                ->field('a.*,b.shipping_fee')
                ->join('order_details_'.$v['store_id'] .' b','a.order_sn = b.order_sn','LEFT')
                ->where('a.order_sn','=',$v['order_sn'])
                ->where('a.mark','=',1)
                ->find();

            if(empty($info))continue;
            $info['goods_list'] = DB::name('order_goods')
                ->alias('a')
                ->field('a.*,a.goods_id ogoods_id')
                ->join('store_goods b','a.goods_id = b.id','LEFT')
                ->where('a.order_id','=',$v['order_sn'])
                ->select()->toArray();
           
            $info['num'] = array_sum(array_column($info['goods_list'], 'goods_num'));
            $info['is_spike'] = 0;
            foreach ($info['goods_list'] as $k2 => &$v2) {
                $v2['prom_id'] > 0 && $v2['prom_type'] > 0 && $info['is_spike'] = 1;
                $v2['spec_key_name'] = $this->get_spec1($v2['spec_key']);
//                    if ($v2['spec_key']) {
//                        $k_info = $this->get_spec($v2['spec_key']);
//                        foreach ($k_info as $k5 => $v5) {
//                            $v2['spec_key_name'] = $v5['item_name'];
//                        }
//                    }
            }


//                dump($info);die;
                $info['statusName'] = $this->getOrderStatusName($info['sendout'], $info['order_state'], $info['evaluation_state']);
                $info['storeName'] = Store::getCacheAll()[$info['store_id']]['store_name'];

                $info['invoice_status'] = 0;

                if(Db::name('order_invoice')
                    ->where('order_sn','=',$v['order_sn'])->select()->toArray()){
                    $info['invoice_status'] = 1;
                }

            if ($info){
                $data[] = [$info];
            }
        }

        return ['page'=>$page,'list'=>$data];
    }


    /**
     * 获取商品规格
     * @param $goods_id|商品id  $type=1 读取商品原有规格属性  2 读取区域编辑后的规格属性
     */
    public function get_spec($k, $lang =29) {
        $k = str_replace('_', ',', $k);
        return Db::name('goods_spec')
            ->alias('a')
            ->join('goods_spec_item b','a.id = b.spec_id','LEFT')
            ->join('goods_spec_lang al','a.id = al.spec_id','LEFT')
            ->join('goods_spec_item_lang bl','b.id = bl.item_id','LEFT')
            ->where('b.id','in',$k)
            ->where('al.lang_id','=',$lang)
            ->where('bl.lang_id','=',$lang)
            ->order('b.id')
            ->select()->toArray();
    }

    public function get_spec1($k, $lang =29) {
        $k = str_replace('_', ',', $k);
        $spec = Db::name('goods_spec')
            ->alias('a')
            ->join('goods_spec_item b','a.id = b.spec_id','LEFT')
            ->join('goods_spec_lang al','a.id = al.spec_id','LEFT')
            ->join('goods_spec_item_lang bl','b.id = bl.item_id','LEFT')
            ->where('b.id','in',$k)
            ->where('al.lang_id','=',$lang)
            ->where('bl.lang_id','=',$lang)
            ->order('b.id')
            ->select()->toArray();
        return implode(':',array_column($spec, 'item_name'));
    }


    /**
     * 通过sendout、evaluation_state和order_status获取订单状态
     */
    public function getOrderStatusName($sendout, $status, $evaluation)
    {
        $statusName = '';
        if (empty($sendout)) {
            $sendout = 1;
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
                    $statusName = '已付款-商家未接单';
                    break;
                case 25:
                    $statusName = '已接单-配制中';
                    break;
                case 30:
                    $statusName = $sendout == 1 ? '待收货' : '已发货';
                    break;
                case 40:
                    $statusName = $sendout == 1 ? '待收货' : '区域配送';
                    break;
                case 50:
                    $statusName = $evaluation == 1 ? '已收货' : '待评价';
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
     *  获取订单中的商品信息
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-02
     * Time: 17:05
     */
    public function getList($order_sn,$user_id)
    {
        // 订单商品列表
        $goodsList = [];
        // 获取订单系信息列表
        $orderList = $this->getOrderDetail($order_sn,$user_id);
        if (empty($orderList['goods'])) {
            $this->setError('当前订单没有商品');
            return false;
        }
        // 订单中所有商品id集
        $goodsIds = array_unique(array_column($orderList['goods'], 'store_goods_id'));
        // 获取并格式化商品数据
        $goodsData = [];
        foreach ((new StoreGoods())->getListByIds($goodsIds) as $item) {
            $goodsData[$item['id']] = $item;
        }
        // 格式化购物车数据列表
        foreach ($orderList['goods'] as $cart) {
            // 判断商品不存在则自动删除
            if (!isset($goodsData[$cart['store_goods_id']])) {
                $this->setError('很抱歉，商品 [' . $cart['goods_name'] . '] 已售空');
                return false;
            }
            /* @var GoodsModel $goods */
            $goods = $goodsData[$cart['store_goods_id']];
            // 判断商品是否已删除
            if (!$goods['mark']) {
                $this->setError('很抱歉，商品 [' . $goods['goods_name'] . '] 已下架');
                return false;
            }
            // 商品sku信息
            $goods['spec_key'] = $cart['spec_key'];
            $goods['member_goods_price'] = $cart['goods_pay_price'];
            $goods['spec_key_name'] = $cart['spec_key_name'];
            // 判断商品是否下架
            if ($goods['is_on_sale']['value'] != 1) {
                $this->setError('很抱歉，商品 [' . $goods['goods_name'] . '] 已下架');
                return false;
            }

            // 商品sku不存在则自动删除
            if (!$goods['goods_sku'] = StoreGoodsSpecPrice::getSpecPriceStock($cart['store_goods_id'],$this->formatSpec($cart['spec_key']))) {
                $this->setError('很抱歉，商品 [' . $goods['goods_name'] . '] 库存不足');
                return false;
            }

            // 判断商品库存
            if ($cart['goods_num'] > $goods['goods_sku']['stock']) {

                $this->setError('很抱歉，商品 [' . $goods['goods_name'] . '] 库存不足');
                return false;
            }

            if($goods['store_goods_type'] && $goods['store_goods_joint']){

                foreach ($goods['store_goods_joint'] as &$joint){
                    // 商品sku不存在则自动删除
                    if (!$joint['goods_sku'] = StoreGoodsSpecPrice::getSpecPriceStock($joint['store_goods_ids'],$this->formatSpec($joint['key']))) {
                        $this->setError('很抱歉，组合商品 [' . $goods['goods_name'] . '] 中 ['.$joint['store_goods']['goods_name'].']库存不足');
                        return false;
                    }
                    // 判断商品库存
                    if ($joint['num'] > $joint['goods_sku']['stock']) {

                        $this->setError('很抱歉，组合商品 [' . $goods['goods_name'] . '] 中 ['.$joint['store_goods']['goods_name'].'--'.($joint['key_name'] ? : '无规格').']库存不足');
                        return false;
                    }
                }

            }

            // 商品单价
            $goods['goods_price'] = $goods['goods_sku']['price'];
            // 购买数量
            $goods['total_num'] = $cart['goods_num'];

            // 商品总价
            $goods['total_price'] = $total_price = bcmul($goods['member_goods_price'], $cart['goods_num'], 2);
            $goodsList[] = $goods->toArray();
        }
        return $goodsList;
    }


    /**
     * 创建新订单
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-02
     * Time: 11:42
     */
    public function createOrder($user_id, &$order)
    {
        if($os = $this->where('order_sn','=',$order['order_sn'])
            ->find()){
            $this->errorCode = 2;
            $this->error = '订单已生成，请前往支付';
            return false;
        }

        // 表单验证
        if (!$this->validateOrderForm($order)) {
            return false;
        }
        // 创建新的订单
        $id = $this->transaction(function () use (&$order, $user_id) {
            //设置分销金额
            $this->setFxPrice($order);
            // 设置订单优惠券信息
            $this->setCouponPrice($order,$user_id);
            // 记录订单信息
            $id = $this->add($user_id, $order);
            // 保存订单商品信息
            $this->saveOrderGoods($user_id, $order);

            return $id;
        });
        return ['orderId'=>$id,'orderNo'=>$order['order_sn'],'storeId'=>$order['store_id']];
    }


    /**
     * 表单验证 (订单提交)
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-10
     * Time: 20:28
     */
    private function validateOrderForm(&$order)
    {
        if (in_array($order['sendout'],[2,3,4])) {
            if (empty($order['delivery'])) {
                $this->error = '请先选择地址';
                return false;
            }
        }
        return true;
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
        //order
        $_order = [
            'order_sn' => $order['order_sn'],
            'store_id' => $order['store_id'],
            'buyer_id' =>$user_id,
            'cid' => $order['coupon_id'],
            'fx_phone' => $order['fx_code'],
            'sendout' => $order['sendout'],
            'goods_amount' => $order['order_total_price'],
            'order_amount' => $order['order_pay_price'],
            'sendout_sign' => $order['sendout_sign'],
            'add_time' => time()
        ];
        //order_98
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
        //订单详情表信息order_detail
        $orderDetailsData = [
            "order_sn"          => $order['order_sn'],
            'address_id'        => isset($order['address_id']) ? $order['address_id'] :0,
            'delivery'        => isset($order['delivery']) ? $order['delivery'] :'',
            'post_sendout' => isset($order['post_sendout']) ? $order['post_sendout'] :0,
            "shipping_fee"      => isset($order['shippingfee']) ? $order['shippingfee'] : 0,
            "seller_msg"        => isset($order['seller_msg']) ? $order['seller_msg'] : '',
            "fx_user_id"        => isset($order['fx_user_id']) ? $order['fx_user_id'] : 0,
            'fx_money'          => isset($order['fx_price']) ? $order['fx_price'] : 0,
            'point_discount'    => isset($order['pd_amount']) ? $order['pd_amount'] : 0,
            'coupon_discount'   => isset($order['coupon_price']) ? $order['coupon_price'] : 0,
            'number_order'      => '',
            'underline_pay_money'   => isset($order['underline_pay_money']) ? $order['underline_pay_money'] : '',
            'store_source_id'       => isset($order['store_source_id']) ? $order['store_source_id'] : 1,
            'source_delivery_fee'   => isset($order['source_delivery_fee']) ? $order['source_delivery_fee'] : '',
            'source_address'        => isset($order['source_address']) ? $order['source_address'] : '',
            'sendout_time'      => isset($order['sendout_time']) ? $order['sendout_time'] : 0,
        ];

        //订单关联表信息orderRelation
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
                'goods_pay_price' => $goods['goods_price'],
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
     * 分销金额
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-05
     * Time: 11:27
     */
    public function setFxPrice(&$order){
        $order['fx_price'] = 0;
        if(isset($order['fx_discount']) && $order['fx_discount'] > 0){
            // 计算分销金额 (分销后)
            if(isset($order['user_coupon_id']) && $order['user_coupon_id'] > 0){
                // 获取优惠券信息
                $userCoupon = UserCoupon::detail($order['user_coupon_id']);
                if (!$userCoupon || !isset($userCoupon['coupon'])) throw new BaseException(['msg' => '未找到优惠券信息']);
                // 计算分销金额 (使用优惠价后)
                $fx_price = bcmul(bcsub($order['order_total_price'], $userCoupon['coupon']['discount'], 2),$order['fx_discount'] / 100,2);
                if($userCoupon['coupon']['type']['value'] == 2){
                    $fx_price = 0;
                }

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
        $orderTotalPrice = bcsub($order['order_total_price'],$order['fx_price'],2);
        $order['coupon_price'] = 0.00;
        $order['coupon_add_price'] = 0.01;
        if ($order['user_coupon_id'] > 0 && $order['coupon_id'] > 0) {
            // 获取优惠券信息
            $userCoupon = UserCoupon::detail($order['user_coupon_id']);
            if (!$userCoupon || !isset($userCoupon['coupon'])) throw new BaseException(['msg' => '未找到优惠券信息']);
            // 计算订单金额 (抵扣后)
            $orderTotalPrice = bcsub($orderTotalPrice, $userCoupon['coupon']['discount'], 2);
            if($userCoupon['coupon']['type']['value'] == 2){
                if($order['order_total_price'] < $userCoupon['coupon']['money']){
                    throw new BaseException(['msg' => '兑换券需满足'.$userCoupon['coupon']['money'].'元可用']);
                }
                $orderTotalPrice = 0;
                $order['coupon_add_price'] = 0;
            }
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
        $orderTotalPrice <= 0 && $orderTotalPrice += $order['coupon_add_price'];
        $order['order_pay_price'] = $orderTotalPrice;
        in_array($order['sendout'],[2,3,4]) &&  $order['order_pay_price'] = bcadd($orderTotalPrice, $order['shippingfee'], 2);
        return true;
    }

    /**
     * 自动收货接口
     * @author  luffy
     * @date    2019-12-13
     */
    public function automaticDelivery(){
        //获取系统设置自动收货时间
        $setting    = Db::name('system_console')->where(['type'=>2, 'status'=>1])->find();
        $auto_day   = $setting ? $setting['delivery_time'] : 1;
        //获取满足条件的订单------暂定接单未手动收货的
        $order_info = $this->where(['order_state'=>25, 'add_time'=>['<', 1575968187]])->column('order_sn');

        $vvvvvv = [];
        $aaaaaaaa = $this->field('store_id,order_sn,order_state')->where(['add_time'=>['>', 1569859200]])->select();
        foreach($aaaaaaaa as $key => $value){
            $vvv =  Db::name('order_'.$value['store_id'])->where(['order_sn'=>$value['order_sn']])->find();
            if($value['order_state'] != 0 && $value['order_state'] != $vvv['order_state']){
                $vvvvvv[$key]['order_sn'] = $value['order_sn'];
                $vvvvvv[$key]['order_sn_1'] = $value['order_state'];
                $vvvvvv[$key]['order_sn_2'] = $vvv['order_state'];
            }
        }
        pre($vvvvvv);
        //查询10月1号以来退款的订单
        //更新自动收货时间


        //刷退款状态、刨除退款相关代码
    }

}
