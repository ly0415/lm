<?php

namespace app\common\enum\order;

use app\common\enum\EnumBasics;

/**
 * 订单支付方式枚举类
 * Class PayType
 * @package app\common\enum\order
 */
class PayType extends EnumBasics
{

    // 余额支付
    const BALANCE = 3;

    // 微信支付
    const WECHAT = 2;

    //支付宝
    const ALIPAY = 1;

    //线下付款
    const OFFLINE = 4;

    //免费兑换
    const FREE = 5;

    //银联
    const YINLIAN = 11;

    /**
     * 获取枚举数据
     * @return array
     */
    public static function data()
    {
        return [
            self::BALANCE => [
                'name' => '余额支付',
                'value' => self::BALANCE,
            ],
            self::WECHAT => [
                'name' => '微信支付',
                'value' => self::WECHAT,
            ],
        ];
    }

}