<?php

namespace app\api\controller;

/**
 * 微信小程序
 * Class Wxapp
 * @package app\api\controller
 */
class Wxapp extends Controller
{
    /**
     * 获取购物车商品总数
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-28
     * Time: 14:02
     */
    public function getYinlianCode($pay_money, $order_sn, $store_id){
        \app\store\service\Yinlian::saomapay($pay_money, $order_sn, $store_id);
    }

}
