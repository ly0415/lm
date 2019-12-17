<?php

namespace app\api\controller;

/**
 * 生成二维码
 * @author  luffy
 * @date    2019-12-11
 */
class QRcode extends Controller{

    /**
     * s获取订单号对应二维码
     * @author  luffy
     * @date    2019-12-11
     */
    public function getOrderCode($order_sn){
        require VENDOR_PATH.'/phpqrcode/phpqrcode.php'; //引入二维码
        \QRcode::png($order_sn, false, QR_ECLEVEL_L, 8, 1);
    }

}
