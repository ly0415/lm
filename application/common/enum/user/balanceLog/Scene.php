<?php

namespace app\common\enum\user\balanceLog;

use app\common\enum\EnumBasics;

/**
 * 余额变动场景枚举类
 * Class Scene
 * @package app\common\enum\user\balanceLog
 */
class Scene extends EnumBasics
{
    // 用户充值
    const RECHARGE = 1;

    // 用户消费
    const CONSUME = 2;

    // 注册赠送
    const REGISTER = 3;

    //线下审核支付
    const OFFLINE = 4;

    //线下审核支付
    const RECHARGECODE = 5;

    // 退款回滚
    const REFUND = 6;

    // 订单退款
    const SYSTEM = 7;

    /**
     * 获取订单类型值
     * @return array
     */
    public static function data()
    {
        return [
            self::RECHARGE => [
                'name' => '用户充值',
                'value' => self::RECHARGE,
                'describe' => '用户充值：%s',
            ],
            self::CONSUME => [
                'name' => '用户消费',
                'value' => self::CONSUME,
                'describe' => '用户消费：%s',
            ],
            self::ADMIN => [
                'name' => '管理员操作',
                'value' => self::ADMIN,
                'describe' => '后台管理员 [%s] 操作',
            ],
            self::REFUND => [
                'name' => '订单退款',
                'value' => self::REFUND,
                'describe' => '订单退款：%s',
            ],
        ];
    }

}