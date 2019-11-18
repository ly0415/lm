<?php

namespace app\store\controller;
use app\store\model\Order as OrderModel;
use think\Db;

/**
 * 微信扫码页面
 * Class WxPay
 * @package app\store\controller\WxPay
 */
class Wxpay extends Controller
{
    /**
     * 微信扫码支付
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-07
     * Time: 11:33
     */
    public function pay($order_id = 0,$pay_type = 2,$pay_price = 0.01,$order_sn = null,$payment = null){
        $order = [
            'order_id' => $order_id,
            'pay_type' => $pay_type,
            'order_sn' => $order_sn,
            'order_pay_price' => $pay_price,
            'payment' => $payment
        ];
        return $this->fetch('pay',compact('order'));
    }

    /**
     * 二维码
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-05
     * Time: 18:12
     */
    public function qr_code($url = null){
        require VENDOR_PATH.'/phpqrcode/phpqrcode.php'; //引入二维码
        \QRcode::png(urldecode($url));
    }

    /**
     * 查询订单支付状态
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-09
     * Time: 13:44
     */
    public function query_order_status( $order_id = null, $order_sn = null ){
        $order = OrderModel::get(['order_sn'=>$order_sn]);
        if(!$order){
           return $this->renderError('订单不存在或已失效，请重新下单');
        }
        $orderDetails = Db::name('order_'.$order['store_id'])
            ->field('a.*')
            ->alias('a')
            ->join('order_details_'.$order['store_id'].' b','a.order_sn = b.order_sn','LEFT')
            ->join('order_relation_'.$order['store_id'].' c','b.order_sn = c.order_sn','LEFT')
            ->where('a.order_sn','=',$order['order_sn'])
            ->where('a.mark','=',1)
            ->find();
//        dump($orderDetails);
        if($orderDetails['order_state'] == 20){
            return $this->renderSuccess('支付成功',url('order/index'));
        }
        return $this->renderJson(3,'未支付');
    }

}
