<?php

namespace app\api\controller;

use app\api\model\Store             as StoreModel;
use app\api\model\Cart              as CartModel;
use app\api\model\Order             as OrderModel;
use app\common\enum\order\PayType   as PayTypeEnum;
use app\common\enum\OrderType       as OrderTypeEnum;

/**
 * 订单控制器
 * @author  luffy
 * @date    2019-07-30
 */
class Order extends Controller{

    /**
     * 构造方法
     * @author  luffy
     * @date    2019-07-30
     */
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 立即购买
     * @author  luffy
     * @date    2019-11-28
     */
    public function buyNow($user_id = 0, $store_goods_id = 0, $key = '', $price = 0, $num = 0, $delivery_type = 0){
        $OrderModel     = new OrderModel;
        $return_info    =  $OrderModel->buyNowCkeck($user_id, $store_goods_id, $key, $price, $num, $delivery_type);
        if ($OrderModel->hasError()){
            return $this->renderError($OrderModel->getError());
        }
        //加入购物车
        $CartModel      = new CartModel;
        $cart_id        = $CartModel->addCart($user_id, $store_goods_id, $key, $price, $num, $delivery_type, $return_info);
        if ($CartModel->hasError()){
            return $this->renderError($CartModel->getError());
        }
        return $this->renderSuccess($cart_id);
    }

    /**
     * 再来一单
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-05
     * Time: 10:51
     */
    public function buyAgain($order_sn = null,$user_id = 0){
        if(!$order_sn || !$user_id){
            $this->renderError('缺少必要参数');
        }
        $model = new OrderModel();
        $order  = $model->getOrderDetail($order_sn,$user_id);

        $cart = $model->checkOrderGoods($order);
        if ($model->hasError()){
            return $this->renderError($model->getError());
        }

        //加入购物车
        $CartModel      = new CartModel;
        if($cartIds = $CartModel->add($cart)){
            return $this->renderSuccess(['store_id'=>$order['store_id'],'cart'=>$cartIds],'SUCCESS');
        }
        return $this->renderError($CartModel->getError() ? : '网络异常');
    }
    /**
     * Created by PhpStorm.
     * 提交订单(自提，配送)
     * @param string $cart_ids (支持字符串ID集)
     * @param int $sendout 配送方式
     * @param int $store_id 门店id
     * @param string $addressId 联系人配送地址id
     * @param string $fxPhone 分销码
     * @param string $discount_rate 分销优惠比例
     * @param string $pei_time 自提时间
     * @param string $shippingfee 配送费
     * @param string $fx_user_id 分销人员
     * @param string $userCouponId 用户优惠券id
     * @param int    $couponId 优惠券id
     * @param string $discount_price 优惠金额
     * @param string $table_type 桌号类型
     * @param string $table_number 用餐人数
     * @param string $table_num 桌号
     * @param string $seller_msg 留言
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \Exception
     * Author: fup
     * Date: 2019-12-10
     * Time: 17:37
     */
    public function cart()
    {
        if($this->request->isPost()){
            $Cart = new CartModel();
            $data = $this->request->post();
            // 商品结算信息
            $order = $Cart->getUserList($data);
            if($order['has_error']){
                return $this->renderError($order['error_msg']);
            }
            // 创建订单
            $model = new OrderModel;
            if (!$orderInfo = $model->createOrder($data['user_id'], $order)) {
                return $this->renderError($model->getError() ?: '订单提交失败');
            }
            // 移出购物车中已下单的商品
            $Cart->clearAll($data['cart_ids']);
            // 返回状态
            return $this->renderSuccess($orderInfo,'提交订单成功,请前往支付');
        }
        return $this->renderError('请求方式错误');
    }

    /**
     * 提交订单(总仓直配，海外保税购)
     * Created by PhpStorm.
     * Author:fup
     * Date: 2019-12-10
     * Time: 17:37
     * $cart_ids        购物车id
     * $seller_msg       留言
     * $addressId        地址id
     * $fxPhone         分销code
     * $storeid         店铺
     * $discount_rate   分销抵扣比例
     * $sendout         //配送方式 数组形式 商品id-配送方式
     * post_sendout     邮寄-》配送 1
     * $shippingfee     邮费
     * $couponId        优惠劵Id
     * $userCouponId       用户优惠劵Id
     * $discount_price      优惠劵优惠金额
     * $fx_user_id          分销用户id
     * $daifu          是否代付
     *
     *
     */
    public function comfirm(){
        $data['cart_ids'] = !empty($_REQUEST['cart_ids']) ? htmlspecialchars($_REQUEST['cart_ids']) : ''; //购物车id
        $data['seller_msg']= !empty($_REQUEST['seller_msg']) ? htmlspecialchars($_REQUEST['seller_msg']) : ''; //留言
        $data['addressId']= !empty($_REQUEST['addressId']) ? htmlspecialchars($_REQUEST['addressId']) : ''; //地址id
        $data['fxPhone']= !empty($_REQUEST['fxPhone']) ? $_REQUEST['fxPhone'] : ''; //分销code
        $data['storeid']= !empty($_REQUEST['storeid']) ? intval($_REQUEST['storeid']) : ''; //店铺
//        $lang $data['cart_ids']= !empty($_REQUEST['lang']) ? intval($_REQUEST['lang']) : $this->langid; //语言
        $data['discount_rate']= !empty($_REQUEST['discount_rate']) ? intval($_REQUEST['discount_rate']) : '';//分销抵扣比例
        $data['sendout']= !empty($_REQUEST['sendout']) ? $_REQUEST['sendout'] : 3; //配送方式 数组形式 商品id-配送方式
        $data['post_sendout']= !empty($_REQUEST['post_sendout']) ? $_REQUEST['post_sendout'] : 0; //
        $data['shippingfee']= !empty($_REQUEST['shippingfee']) ? htmlspecialchars($_REQUEST['shippingfee']) : 0; //邮费
        $data['fx_user_id']= !empty($_REQUEST['fx_user_id']) ? intval($_REQUEST['fx_user_id']) : 0; //分销用户id
//        $rule_id $data['cart_ids']= !empty($_REQUEST['rule_id']) ? intval($_REQUEST['rule_id']) : ''; //分销规则id
        $data['daifu']=!empty($_REQUEST['daifu']) ?$_REQUEST['daifu']:0; //是否代付
        $data['couponId']= !empty($_REQUEST['couponId']) ? $_REQUEST['couponId'] : 0;//优惠劵Id
        $data['userCouponId']=!empty($_REQUEST['userCouponId']) ? $_REQUEST['userCouponId']:0;//用户优惠劵Id
        $data['discount_price']=!empty($_REQUEST['discount_price']) ? $_REQUEST['discount_price'] : 0;//优惠劵优惠金额
        $data['user_id']=!empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;//用户id


        $Cart = new CartModel();
        // 商品结算信息
        $order = $Cart->getUserList($data);
        if($order['has_error']){
            return $this->renderError($order['error_msg']);
        }
        // 创建订单
        $model = new OrderModel;
        if (!$orderInfo = $model->createOrder($data['user_id'], $order)) {
            return $this->renderError($model->getError() ?: '订单提交失败');
        }
        // 移出购物车中已下单的商品
        $Cart->clearAll($data['cart_ids']);
        // 返回状态
        return $this->renderSuccess($orderInfo,'提交订单成功,请前往支付');
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
