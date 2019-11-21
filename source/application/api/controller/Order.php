<?php

namespace app\api\controller;

use app\api\model\Order as OrderModel;
use app\api\model\Cart as CartModel;
use app\common\enum\order\PayType as PayTypeEnum;
use app\common\enum\OrderType as OrderTypeEnum;
use app\api\model\Store as StoreModel;


//use app\api\model\Setting as SettingModel;
//use app\common\enum\order\PayType as PayTypeEnum;

/**
 * 订单控制器
 * @author  luffy
 * @date    2019-07-30
 */
class Order extends Controller
{

//    private $user;

    /**
     * 构造方法
     * @author  luffy
     * @date    2019-07-30
     */
    public function _initialize()
    {
        parent::_initialize();
//        $this->user = $this->getUser();   // 用户信息
    }

    /**
     * 提交订单展示页面
     * @author  luffy
     * @date    2019-07-30
     */
    public function submitOrderShow($user_id=0, $cart_ids = ''){
        // 商品结算信息
        if(empty($user_id) || empty($cart_ids)){
            return $this->renderError('参数错误！');
        }

        //获取提交购物信息
        $cartModel  = new CartModel();
        $cart_info  = $cartModel -> getCartInfos($cart_ids);
        return $this->renderSuccess($cart_info);
    }

    /**
     * 提交订单展示页面
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-19
     * Time: 21:53
     */
    public function submitOrderShow1($user_id=0, $cart_ids = '',$address_id = 0){
        // 商品结算信息
        if(empty($user_id) || empty($cart_ids) ){
            return $this->renderError('参数错误！');
        }

        //获取提交购物信息
        $cartModel  = new CartModel();
        $cart_info  = $cartModel -> getCartInfos($cart_ids,$user_id,$address_id);
        return $this->renderSuccess($cart_info);
    }

    /**
     * 总仓支配、海外   提交订单展示页面
     * Created by PhpStorm.
     * Author: ly
     * Date: 2019-11-21
     * Time:
     */
    public function submitOrderShow2($user_id=0, $cart_ids = '',$address_id = 0){
        // 商品结算信息
        $user_id=35035;
        $cart_ids=[1259,4096];//4552
        if(empty($user_id) || empty($cart_ids) ){
            return $this->renderError('参数错误！');
        }

        //获取提交购物信息
        $cartModel  = new CartModel();
        $cart_info  = $cartModel -> getCartInfos($cart_ids,$user_id,$address_id);
        return $this->renderSuccess($cart_info);
    }

    /**
     * 结算校验库存
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-11-13
     * Time: 15:45
     */
    public function settlement($cart_ids = null,$user_id = 0){
        $cartModel = new CartModel;
        if($list = $cartModel->getList($cart_ids,$user_id)){
            return $this->renderSuccess($list);
        }
        return $this->renderError($cartModel->getError() ? : '网络异常~');
    }

    /**
     * 支付校验库存
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-11-14
     * Time: 17:21
     */
    public function payment($order_sn = null,$user_id = 0){
        if(!$order_sn || !$user_id){
            return $this->renderError('缺少必要参数');
        }
        $orderModel = new OrderModel();
        if($list = $orderModel->getList($order_sn,$user_id)){
            return $this->renderSuccess($list);
        }
        return $this->renderError($orderModel->getError() ? : '网络异常~');
    }



    /**
     * 订单确认-立即购买
     * @param int $goods_id 商品id
     * @param int $goods_num 购买数量
     * @param int $goods_sku_id 商品sku_id
     * @param int $delivery 配送方式
     * @param int $pay_type 支付方式
     * @param int $coupon_id 优惠券id
     * @param int $shop_id 自提门店id
     * @param string $linkman 自提联系人
     * @param string $phone 自提联系电话
     * @param string $remark 买家留言
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     * @throws \Exception
     */
    public function buyNow(
        $goods_id,
        $goods_num,
        $goods_sku_id,
        $delivery = null,
        $pay_type = PayTypeEnum::WECHAT,
        $shop_id = 0,
        $linkman = '',
        $phone = '',
        $coupon_id = null,
        $remark = ''
    )
    {
        // 商品结算信息
        $model = new OrderModel;
        $order = $model->getBuyNow(
            $this->user,
            $goods_id,
            $goods_num,
            $goods_sku_id,
            $delivery,
            $pay_type,
            $shop_id
        );
        if (!$this->request->isPost()) {
            return $this->renderSuccess(array_merge($order, [
                // 配送设置
                'deliverySetting' => SettingModel::getItem('store')['delivery_type']
            ]));
        }
        if ($model->hasError()) {
            return $this->renderError($model->getError());
        }
        // 创建订单
        if (!$model->createOrder($this->user, $order, $linkman, $phone, $coupon_id, $remark)) {
            return $this->renderError($model->getError() ?: '订单创建失败');
        }
        // 构建微信支付请求
        $payment = ($pay_type == PayTypeEnum::WECHAT) ? $model->paymentByWechat($this->user) : [];
        return $this->renderSuccess([
            'order_id' => $model['order_id'],   // 订单id
            'pay_type' => $pay_type,            // 支付方式
            'payment' => $payment               // 微信支付参数
        ]);
    }

    /**
     * 订单确认-购物车结算
     * @param string $cart_ids (支持字符串ID集)
     * @param int $delivery 配送方式
     * @param int $pay_type 支付方式
     * @param int $shop_id 自提门店id
     * @param string $linkman 自提联系人
     * @param string $phone 自提联系电话
     * @param int $coupon_id 优惠券id
     * @param string $remark 买家留言
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \Exception
     */
    public function cart(
        $cart_ids,
        $delivery = null,
        $pay_type = PayTypeEnum::WECHAT,
        $shop_id = 0,
        $linkman = '',
        $phone = '',
        $coupon_id = null,
        $remark = ''
    )
    {
        // 商品结算信息
        $Cart = new CartModel($this->user);
        $order = $Cart->getList($cart_ids, $delivery, $pay_type, $shop_id);
        if (!$this->request->isPost()) {
            return $this->renderSuccess(array_merge($order, [
                // 配送设置
                'deliverySetting' => SettingModel::getItem('store')['delivery_type']
            ]));
        }
        // 创建订单
        $model = new OrderModel;
        if (!$model->createOrder($this->user, $order, $linkman, $phone, $coupon_id, $remark)) {
            return $this->renderError($model->getError() ?: '订单创建失败');
        }
        // 移出购物车中已下单的商品
        $Cart->clearAll($cart_ids);
        // 构建微信支付请求
        $payment = ($pay_type == PayTypeEnum::WECHAT) ? $model->paymentByWechat($this->user) : [];
        // 返回状态
        return $this->renderSuccess([
            'order_id' => $model['order_id'],   // 订单id
            'pay_type' => $pay_type,            // 支付方式
            'payment' => $payment               // 微信支付参数
        ]);
    }

    /**
     * 订单微信支付
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-01
     * Time: 21:41
     */

    public function wxPay($order_sn = 201909012152408666,$open_id = '',$money = 0.01,$store_id = 98,$pay_type = PayTypeEnum::WECHAT) {

        $store = StoreModel::get($store_id);
        $model = new OrderModel();
        // 构建微信支付请求
        $payment = ($pay_type == PayTypeEnum::WECHAT) ? $model->paymentByWechat($order_sn,$open_id,$money, $store['business_id'] == 58 ? true : false,OrderTypeEnum::RECHARGE) : [];
        // 返回状态
        return $this->renderSuccess([
            'order_sn' => $order_sn,   // 订单id
            'pay_type' => $pay_type,            // 支付方式
            'payment' => $payment               // 微信支付参数
        ]);

    }

}
