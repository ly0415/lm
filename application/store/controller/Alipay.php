<?php

namespace app\store\controller;
use think\Config;

/**
 * 支付宝扫码页面
 * Class AliPay
 * @package app\store\controller\AliPay
 */
class Alipay extends Controller
{

    public function pay($order_id = 1,$pay_type = 1,$pay_price = '0.01',$order_sn = null){

        require VENDOR_PATH.'/alipay/pagepay/buildermodel/AlipayTradePagePayContentBuilder.php'; //引入支付宝支付
        require VENDOR_PATH.'/alipay/pagepay/service/AlipayTradeService.php'; //引支付宝支付
        //构造参数
        $payRequestBuilder = new \AlipayTradePagePayContentBuilder();
        $payRequestBuilder->setSubject('艾美睿零售');        //订单名称，必填
        $payRequestBuilder->setBody('订单支付');          //商品描述，可空
        $payRequestBuilder->setTotalAmount($pay_price);    //付款金额，必填
        $payRequestBuilder->setOutTradeNo($order_sn);  //订单编号传值使用（订单id，用，隔开）
        $aop = new \AlipayTradeService(Config::get('alipay'));
        $response = $aop->pagePay($payRequestBuilder,base_url().Config::get('alipay.return_url'),base_url().Config::get('alipay.notify_url'));
        return $response;
    }



}
