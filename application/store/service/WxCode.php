<?php

namespace app\store\service;


class WxCode
{


    /**
     * 生成微信支付二维码
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-15
     * Time: 21:22
     */
    public static function Qrcode($url){

        require VENDOR_PATH.'/phpqrcode/phpqrcode.php'; //引入二维码
        \QRcode::png($url);
    }

}