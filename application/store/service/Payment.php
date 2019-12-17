<?php

namespace app\store\service;

use app\common\enum\OrderType as OrderTypeEnum;
use app\common\exception\BaseException;
use app\store\model\Wxapp;
use think\Config;
use think\Db;

class Payment
{



    /**
     * 构建微信支付
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-15
     * Time: 21:22
     */
    public static function wechat(
        $goods_id,
        $store_id,
        $orderSn,
        $payPrice,
        $orderType = OrderTypeEnum::MASTER
    )
    {
        require VENDOR_PATH.'/wxpay/WxPay.Api.php'; //引入微信支付
        $wxConfig = Wxapp::detail($store_id);
        // 请求失败
        if (!$wxConfig) {
            throw new BaseException(['msg' => "该店铺尚未添加微信支付配置", 'code' => -10]);
        }
//        dump($wxConfig->toArray());die;
        $input = new \WxPayUnifiedOrder();//统一下单
        $config = new \WxPayConfig($wxConfig);//配置参数
        $input->SetBody('艾美睿零售');
        $input->SetAttach(json_encode(['order_type' => $orderType]));
        $input->SetOut_trade_no($orderSn);
        $input->SetTotal_fee($payPrice * 100);//金额乘以100
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 900));
        $input->SetGoods_tag("test");
        $input->SetNotify_url('http://www.711home.net/web/notice.php'); //回调地址
        $input->SetTrade_type("NATIVE");
        $input->SetProduct_id(json_encode(['goods_id'=>$goods_id]));//商品id
        $result = \WxPayApi::unifiedOrder($config, $input);
        // 请求失败
        if ($result['return_code'] === 'FAIL') {
            throw new BaseException(['msg' => "微信支付api：{$result['return_msg']}", 'code' => -10]);
        }
        if ($result['result_code'] === 'FAIL') {
            throw new BaseException(['msg' => "微信支付api：{$result['err_code_des']}", 'code' => -10]);
        }
        return [
            'code_url' => $result['code_url'],
        ];
    }

    /**
     * 构建支付宝支付
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-15
     * Time: 21:22
     */
    public static function alipay(
        $goods_id,
        $orderSn,
        $payPrice,
        $orderType = OrderTypeEnum::MASTER
    )
    {
        require VENDOR_PATH.'/alipay/pagepay/buildermodel/AlipayTradePagePayContentBuilder.php'; //引入支付宝支付
        require VENDOR_PATH.'/alipay/pagepay/service/AlipayTradeService.php'; //引支付宝支付
        //构造参数
        $payRequestBuilder = new \AlipayTradePagePayContentBuilder();
        $payRequestBuilder->setSubject('艾美睿零售');        //订单名称，必填
        $payRequestBuilder->setBody('订单支付');          //商品描述，可空
        $payRequestBuilder->setTotalAmount(0.01);    //付款金额，必填
        $payRequestBuilder->setOutTradeNo(time());  //订单编号传值使用（订单id，用，隔开）
        $aop = new \AlipayTradeService(Config::get('alipay'));
        $response = $aop->pagePay($payRequestBuilder,'','');
        return $response;
    }

    /**
     * 小程序微信子商户退款
     * @author  luffy
     * @date    2019-11-21
     */
    public static function wxRefund($orderSn = '', $store_id = '', $refund_price = ''){
        require VENDOR_PATH.'/wxpay/WxPay.Api.php'; //引入微信支付
        $input = new \WxPayRefund();    //退款

        $wxConfig = Wxapp::detail($store_id);
        // 请求失败
        if (!$wxConfig) {
            throw new BaseException(['msg' => "该店铺尚未添加微信支付配置", 'code' => -10]);
        }
        $config = new \WxPayConfig($wxConfig);      //配置参数
        $input->SetOut_trade_no($orderSn);
        $input->SetOut_refund_no($orderSn);
        $input->SetTotal_fee($refund_price * 100);      //金额乘以100
        $input->SetRefund_fee($refund_price * 100);     //金额乘以100
        $input->SetOp_user_id('1450526802');
        $input->ssssss($wxConfig['mchid']);
        $input->cccccc('http://www.711home.net/web/refund.php');
        $result = \WxPayApi::refund($config, $input);

        // 请求失败
        if ($result['return_code'] === 'FAIL') {
            throw new BaseException(['msg' => "微信支付api：{$result['return_msg']}", 'code' => -10]);
        }
        if ($result['result_code'] === 'SUCCESS') {
            Db::name('order_relation_'.STORE_ID)->where('order_sn', $orderSn)->update(['refund_review_user'=>USER_ID, 'refund_review_time'=>time()]);
            Db::name('order_'.$store_id)->where('order_sn', $orderSn)->update(['order_state'=>70]);
        } else {
            throw new BaseException(['msg' => "审核失败", 'code' => -10]);
        }
        return true;
//        return [
//            'code_url' => $result['code_url'],
//        ];
    }

    /**
     * 构建微信支付
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-15
     * Time: 21:22
     */
    public static function refundQuery($orderSn = '201911051138135230', $payPrice = '0.01'){
        require VENDOR_PATH.'/wxpay/WxPay.Api.php'; //引入微信支付
        $input = new \WxPayRefund();    //退款

        $wxConfig = Wxapp::detail(98);
        // 请求失败
        if (!$wxConfig) {
            throw new BaseException(['msg' => "该店铺尚未添加微信支付配置", 'code' => -10]);
        }
        $config = new \WxPayConfig($wxConfig);   //配置参数
        $input->SetOut_trade_no($orderSn);
        $input->ssssss('1553824111');
        $result = \WxPayApi::refundQuery($config, $input);
        pre($result);
        // 请求失败
        if ($result['return_code'] === 'FAIL') {
            throw new BaseException(['msg' => "微信支付api：{$result['return_msg']}", 'code' => -10]);
        }
        if ($result['result_code'] === 'FAIL') {
            throw new BaseException(['msg' => "微信支付api：{$result['err_code_des']}", 'code' => -10]);
        }
        return [
            'code_url' => $result['code_url'],
        ];
    }

}