<?php

namespace app\api\service;

use app\common\library\wechat\WxPay;
use app\common\enum\OrderType as OrderTypeEnum;
use think\Config;

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
        $orderSn,
        $openId,
        $payPrice,
        $service = false,
        $orderType = OrderTypeEnum::MASTER
    )
    {
        // 统一下单API
        $seConfig = Config::get('service_xcx');

        $wxConfig = $service ? Config::get('xcx1') : Config::get('xcx');
        $WxPay = new WxPay($wxConfig,$seConfig);
        $payment = $WxPay->unifiedorder($orderSn, $openId, $payPrice, $service,$orderType);
        // 记录prepay_id
//        $model = new WxappPrepayIdModel;
//        $model->add($payment['prepay_id'], $orderId, $user['user_id'], $orderType);
        return $payment;
    }

}