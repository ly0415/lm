<?php

namespace app\store\service;

class Yinlian{

    /**
     * 生成银联支付二维码
     * @author  luffy
     * @date    2019-09-18
     */
    public static function saomapay($pay_money, $order_sn, $store_id){

        require VENDOR_PATH.'/phpqrcode/phpqrcode.php'; //引入二维码
        include_once VENDOR_PATH."/shijicloud/Saomapay.php";

        $Saomapay = new \Saomapay();
        $res = $Saomapay->setMerchantTxnNo($order_sn.rand(1000, 9999))
            ->setChannelID(80)
            ->setTxnAmt($pay_money)
            ->setTxnReqTime(date('Y/m/d H:i:s'))
            ->setNotifyUrl(base_url() . '/web/yinlian.php')
            ->index($store_id);
        \QRcode::png($res['QRCode'], false, QR_ECLEVEL_L, 4, 1);
    }
};