<?php

namespace app\store\controller;

use app\store\model\FxUserAccount;
use app\store\model\Business;
use app\store\model\FxUser;
use app\store\model\GoodsSpecItem;
use app\store\model\Order as OrderModel;
use app\store\model\OrderRefund as OrderRefundModel;
use app\store\model\Sms;
use app\store\model\StoreGoods as StoreGoodsModel;
use app\store\model\StoreGoodsSpecPrice;
use app\store\model\GoodsSpec as GoodsSpecModel;
use app\store\model\Store as StoreModel;
use app\store\model\Cart as CartModel;
use app\store\model\StoreSource     as StoreSourceModel;
use app\store\model\User as UserModel;
use app\store\model\UserCoupon;
use app\common\enum\order\PayType   as PayTypeEnum;
use app\common\library\sms\Driver   as SmsDriver;
use think\Config;

/**
 * 店铺订单管理
 * @author  luffy
 * @date    2019-07-15
 */
class Order extends Controller{

    /**
     * 订单列表
     * @author  luffy
     * @date    2019-07-15
     */
    public function index($search_store_id = 0, $delivery_type = 0, $start_time = '', $end_time = '', $phone = '', $order_sn = '', $order_state = '', $tips = 0){
        //订单列表
        $OrderModel     = new OrderModel;
        $orderList      = $OrderModel->getList($search_store_id, $delivery_type, $start_time, $end_time, $phone, $order_sn, $order_state, $tips);

        if(!T_GENERAL){
            //门店列表
            $StoreModel = new StoreModel;
            $storeList  = $StoreModel::getStoreList(TRUE, BUSINESS_ID);
        }
        //获取配送方式
        $orderState     = $OrderModel->order_state;
        //获取配送方式
        $deliveryType   = $OrderModel->delivery_type;
        return $this->fetch('index', compact('orderList','storeList', 'orderState', 'deliveryType'));
    }


    /**
     * 订单核销
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-11-04
     * Time: 17:40
     */
    public function extract($order_sn = null)
    {
        if(!$order_sn){
            return $this->renderError('缺少必填参数order_sn');
        }
        $orderModel = new OrderModel();
        // 订单详情
        $order = $orderModel->getOrderDetail($order_sn);
        // 确认核销
        if ($orderModel->verificationOrderSelf($order,2)) {
            return $this->renderSuccess('核销成功',url('order/detail',['order_sn'=>$order_sn]));
        }
        return $this->renderError($orderModel->getError() ?: '核销失败');
    }


    /**
     * 订单详情
     * @author  luffy
     * @date    2019-07-28
     */
    public function detail($order_sn){
        $OrderModel         = new OrderModel;
        $OrderRefundModel   = new OrderRefundModel;
        //获取订单详情
        $orderDetail        = $OrderModel->getOrderDetail($order_sn);
        //获取退款商品信息
        $orderRefundDetail  = $OrderRefundModel->getOrderRefundDetail($order_sn);
        return $this->fetch('detail', compact('orderDetail', 'orderRefundDetail'));
    }

    /**
     * 订单退款
     * @author  luffy
     * @date    2019-09-24
     */
    public function refund($order_sn){
        //执行退款
        $OrderRefundModel   = new OrderRefundModel;
        // 更新记录
        if ($OrderRefundModel->refund($order_sn, $this->postData('refund'))) {
            return $this->renderSuccess('操作成功', url('order/detail',['order_sn'=>$order_sn]));
        }
        return $this->renderError($OrderRefundModel->getError() ?: '操作失败');
    }

    /**
     * 票据打印
     * @author  luffy
     * @date    2019-08-08
     */
    public function order_print($order_sn){
        //获取打印商品信息
        $OrderModel = new OrderModel;
        $print_data = $OrderModel->getPrintData([$order_sn]);
        return $this->fetch('order_print', compact('print_data'));
    }

    /**
     * 指定分销人员
     * @author  luffy
     * @date    2019-09-20
     */
    public function appoint($id, $buyer_id){
        //获取打印商品信息
        $FxUserAccountModel = new FxUserAccount();
        if (!$FxUserAccountModel->setAppoint($id[0], $buyer_id)) {
            return $this->renderError($FxUserAccountModel->getError() ?: '删除失败');
        }
        return $this->renderSuccess('设置成功');
    }

    /**
     * 接单&&核销
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-05
     * Time: 20:04
     */
    public function state($order_sn = null){
        if(!$order_sn){
            return $this->renderError('缺少必填参数order_sn');
        }
        $orderModel = new OrderModel();
        //接单
        $order = $orderModel->getOrderDetail($order_sn);
        if($order['order_state'] == 20 && $orderModel->acceptOrder($order,$this->yoshop_store['user']['store_user_id'])){
            return $this->renderSuccess('接单成功');
        }
        // 确认核销
        if ($order['order_state'] == 25 && $orderModel->verificationOrderSelf($order,2)) {
            return $this->renderSuccess('核销成功',url('order/detail',['order_sn'=>$order_sn]));
        }
        return $this->renderError($orderModel->getError() ? : '该订单未满足接单或核销条件');

    }
    public function state1($order_sn = null){
        if(!$order_sn){
            return $this->renderError('缺少必填参数order_sn');
        }
        $orderModel = new OrderModel();
        //接单
        $order = $orderModel->getOrderDetail($order_sn);
        if($order['order_state'] == 20 && $orderModel->acceptOrder1($order,$this->yoshop_store['user']['store_user_id'])){
            return $this->renderSuccess('接单成功',url('order/detail',['order_sn'=>$order_sn]));
        }
        // 确认核销
        if ($order['order_state'] == 25 && $orderModel->verificationOrderSelf($order,2)) {
            return $this->renderSuccess('核销成功',url('order/detail',['order_sn'=>$order_sn]));
        }
        return $this->renderError($orderModel->getError() ? : '该订单未满足接单或核销条件');

    }

    /**
     * 获取提示音并且自动打印
     * @author  luffy
     * @date    2019-07-22
     */
    public function get_notips_order($store_id = STORE_ID){
        //获取未提示的订单以及order_sn
        $OrderModel = new OrderModel;
        $result     = $OrderModel->getNoTipsOrder($store_id, 1);
        if($result) return $this->renderSuccess('', '',  $result);
    }

    /**
     * 代客下单列表
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-21
     * Time: 19:14
     */
    public function goods_list(){
        $model = new CartModel();
        if (!$this->request->isAjax()) {
            $store = StoreModel::getStoreList(FALSE,'',['id','business_id']);
            $business = Business::getStoreBusiness($store[STORE_ID]['business_id']);
            return $this->fetch('goodsList',compact('business'));
        }
        // 新增记录
        if ($uniquecode = $model->add($this->postData('cart'))) {
            return $this->renderSuccess('下单成功', url('order/order_payment','&uniquecode='.$uniquecode));
        }
        return $this->renderError($model->getError() ?: '添加失败');

    }

    /**
     * 代客订单
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-28
     * Time: 11:23
     */
    public function order_payment($uniquecode = 0){
        header('content-type:text/html;charset=utf-8');
        $Cart = new CartModel();
        if(!$this->request->isAjax()){
            $cartList = $Cart->getList(['uniquecode'=>$uniquecode,'cart_ids'=>null],STORE_ID);
            if($cartList['has_error']){
                $this->error($cartList['error_msg'],'order/goods_list');
            }

            //获取订单来源平台
            $StoreSourceModel   = new StoreSourceModel;
            $sourceList         = $StoreSourceModel ->getAll();
            return $this->fetch('orderPayment',compact('cartList','sourceInfo', 'sourceList'));
        }
        if(!$this->valieFormData($data = $this->postData('order'))){
            return $this->renderError($this->error ? : '出现未知错误，请重新下单支付');
        }
        if($o = OrderModel::get(['order_sn'=>$data['uniquecode']])){
            return $this->renderSuccess('订单已存在，请前往支付',url('order/detail',['order_sn'=>$data['uniquecode']]));
        }
        //查看用户是否存在，不存在则注册
        $user = (new UserModel)->createUser($data['phone']);

        // 商品结算信息
        $order = $Cart->getList($data,STORE_ID);
        if($order['has_error']){
            return $this->renderError($order['error_msg']);
        }
        // 创建订单
        $model = new OrderModel;
        if (!$order_id = $model->createOrder($user, $order)) {
            return $this->renderJson($model->getErrorCode(),$model->getError() ?: '订单创建失败',url('order/index'));
        }
        // 移出购物车中已下单的商品
        $Cart->clearAll($data['cart_ids']);

        // 构建微信支付请求
        $payment = ($order['pay_type'] == PayTypeEnum::WECHAT) ? $model->paymentByWechat($user,$order) : [];
        // 返回状态
        return $this->renderSuccess($this->getMsg($order['pay_type']),$this->getJumpUrl($order['pay_type'],[
            'order_id' => $order_id,   // 订单id
            'pay_type' => $order['pay_type'],// 支付方式
            'pay_price'=>$order['order_pay_price'],
            'order_sn'=>$order['order_sn'],
            'payment' => $payment ? urlencode($payment['code_url']) : ''
        ]),[
            'order_id' => $order_id,   // 订单id
            'pay_type' => $order['pay_type'],// 支付方式
            'pay_price'=>$order['order_pay_price'],
            'order_sn'=>$order['order_sn'],
            'payment' => $payment ? urlencode($payment['code_url']) : ''
        ]);
    }

    /**
     * 获取微信预支付二维码参数
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-10
     * Time: 10:25
     */
    public function payment(){
        $data = $this->postData('goods');
        // 构建微信支付请求
        $model = new OrderModel();
        $payment = $model->paymentByWechat([],$data) ;
        // 返回状态
        return $this->renderSuccess('正在前往支付...',$this->getJumpUrl(2,[
            'order_id' => $data['order_sn'],   // 订单id
            'pay_type' => 2,// 支付方式
            'pay_price'=>$data['order_pay_price'],
            'order_sn'=>$data['order_sn'],
            'payment' => $payment ? urlencode($payment['code_url']) : ''
        ]),[
            'order_id' => $data['order_sn'],   // 订单id
            'pay_type' => 2,// 支付方式
            'pay_price'=>$data['order_pay_price'],
            'order_sn'=>$data['order_sn'],
            'payment' => $payment ? urlencode($payment['code_url']) : ''
        ]);
    }

    /**
     * 验证表单数据
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-29
     * Time: 20:02
     */
    private function valieFormData($data){
        if(!isset($data['phone']) || empty($data['phone']) || !preg_match('/^\d{11}$/',$data['phone'])){
            $this->error = '请填写手机号';
            return false;
        }
        if($data['pay_type'] == 4){
            if(bcsub($data['payMoney'],$data['pay_price']) < 0){
                $this->error = '支付金额必须大于等于应付金额';
                return false;
            }
        }
        if($data['pay_type'] == 3){
            if(empty($data['code'])){
                $this->error = '请输入验证码';
                return false;
            }
            $sms = new Sms();
            if(!$code = $sms->detail($data['phone'])){
                $this->error = '验证码错误';
                return false;
            }
            $code->edit();
        }
        return true;
    }

    /**
     * 根据业务类型获取商品
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-24
     * Time: 15:12
     */
    public function lists($businessId = 0){
        //获取商品
        $list  = (new StoreGoodsModel)->getListAll($businessId);

        $this->view->engine->layout(false);
        return $this->fetch('lists', compact('list'));
    }

    /**
     * 获取商品规格
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-21
     * Time: 20:34
     */
    public function ajax_get_spec($store_goods_id = 0){
        //获取店铺商品对应规格
        $specKey = StoreGoodsSpecPrice::getSpecKey($store_goods_id);

        //获取规格对应的规格值
        $data = (new GoodsSpecModel)->getList($specKey);

        $list = $this->formatData($store_goods_id,$data);

        $this->view->engine->layout(false);
        return $this->fetch('ajaxGetSpec', compact('list'));
    }

    /**
     * 获取分销码
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-10
     * Time: 14:56
     */
    public function ajax_get_fx_code($phone = null ){
        $user = UserModel::detail(['phone'=>$phone]);
        $fxUserModel = new FxUser();
        $data = $fxUserModel->getCodeByUser($user);
        if($data){
            return $this->renderSuccess('SUCCESS','',$data);
        }
        return $this->renderError('FAIL');
    }

    /**
     * 获取规格对应价格
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-22
     * Time: 16:14
     */
    public function ajax_goods_price_stock($store_goods_id = 0,$key = null,$return_array = false){
        $spec_arr = [];
        if ($key) {
            $key_arr = explode('_', $key);
            $key_pailie = arrangement($key_arr, count($key_arr));
            foreach ($key_pailie as $v) {
                $spec_arr[] = implode('_', $v);
            }
        }
        $store_discount = StoreModel::getStoreDiscount();
        $goodsPriceStock = StoreGoodsSpecPrice::getSpecPriceStock($store_goods_id,$spec_arr);
        $goodsPriceStock['price'] = number_format($goodsPriceStock['price'] * $store_discount,'2','.','');
        if($return_array){
            return $goodsPriceStock;
        }
        return $this->renderSuccess('SUCCESS','',$goodsPriceStock);
    }

    /**
     * 扫码加入购物车
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-29
     * Time: 13:58
     */
    public function add_cart($bcode){
        $sModel = new StoreGoodsSpecPrice();
        $model = new StoreGoodsModel();
        if($data = $sModel->getDetailByBarCode($bcode)){
            if($data['is_on_sale'] != 1){
                return $this->renderError('该商品未上架');
            }
            $data['key_names'] = implode(':',GoodsSpecItem::geyKeyName(explode('_',$data['key'])));
            $data['stockPrice'] = $this->ajax_goods_price_stock($data['store_goods_id'],$data['key'],true);
            if(!isset($data['stockPrice']['stock']) || $data['stockPrice']['stock'] <= 0){
                return $this->renderError('库存不足');
            }
            return $this->renderSuccess('SUCCESS','',$data);
        }
        if($info = $model->getDetailByBarCode($bcode)){
            if($info['is_on_sale']['value'] != 1){
                return $this->renderError('该商品未上架');
            }
            $info['key_names'] = '无规格属性';
            $info['stockPrice'] = $this->ajax_goods_price_stock($info['id'],null,true);
            if(!isset($info['stockPrice']['stock']) || $info['stockPrice']['stock'] <= 0){
                return $this->renderError('库存不足');
            }
            return $this->renderSuccess('SUCCESS','',$info);
        }

        return $this->renderError('商品不存在');
    }

    /**
     * 获取模型
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-29
     * Time: 14:02
     */
    public function getModel($sign){
        if($sign == '1'){
            $model = new StoreGoodsSpecPrice();
        }
        else{
            $model = new StoreGoodsModel();
        }
        return $model;
    }

    /**
     * 获取优惠券
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-04
     * Time: 17:19
     */
    public function ajax_get_coupon($phone = 0,$amount = 0,$uniquecode = 0){
        $coupon = [];
        $user = UserModel::detail(['phone'=>$phone]);
        if($user){
            $Cart = new CartModel();
            $order = $Cart->getList(['uniquecode'=>$uniquecode,'cart_ids'=>null],STORE_ID);
            $coupon = (new UserCoupon)->getCouponAll($user['id'],STORE_ID,$order['order_total_price'],array_column($order['goods_list'],'goods_id'));
//            dump($coupon);die;
        }
        $this->view->engine->layout(false);
        return $this->fetch('ajaxGetCoupon', compact('coupon'));
    }

    /**
     * 发送短信通知
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-12
     * Time: 10:45
     */
    public function sms_code($accept_phone,$msg_type = 'balance' ){
        $aliyun = Config::get('aliyun');

        $SmsDriver = new SmsDriver([
            'default' => 'aliyun',
            'engine' => [
                'aliyun' => [
                    'AccessKeyId' => $aliyun['AccessKeyId'],
                    'AccessKeySecret' => $aliyun['AccessKeySecret'],
                    'sign' => $aliyun['sign'],
                    $msg_type => [
                        'is_enable' => $aliyun['is_enable'],
                        'template_code' => $aliyun['template_code'],
                        'accept_phone' => $accept_phone
                    ],
                ],
            ],
        ]);
        $templateParams = ['phone'=>$accept_phone,'code' => self::getCode()];
        $sms = new Sms();
        $sms->add($templateParams);
        if ($SmsDriver->sendSms($msg_type, $templateParams)) {
            return $this->renderSuccess('发送成功');
        }
        return $this->renderError('发送失败 ' . $SmsDriver->getError());
    }

    /**
     * 生成验证码
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-12
     * Time: 11:32
     */
    public static function getCode($length = 6){
        $min = pow(10, ($length - 1));
        $max = pow(10, $length) - 1;
        return mt_rand($min, $max);
    }

    /**
     * 获取商品总价
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-28
     * Time: 13:46
     */
    public function getTotalAmount($cart){
        return sprintf('%.2f',array_sum(array_map(function ($c){
            return $c['member_goods_price'] * $c['goods_num'];
        },$cart)));
    }

    /**
     * 获取购物车商品总数
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-28
     * Time: 14:02
     */
    public function getTotalNumber($cart){
        return array_sum(array_map(function ($c){
            return $c['goods_num'];
        },$cart));
    }

    /**
     * 获取默认规格价格
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-22
     * Time: 14:59
     */
    public function getDefaultPrice($spec = []){
        $default = [];
        foreach ($spec as $v){
            foreach ($v['itemInfo'] as $k => $vv){
                if($k == 0){
                    $default[] = $vv['item_id'];
                }
            }
        }
        return implode('_',$default);
    }
}
