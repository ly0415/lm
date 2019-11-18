<?php

namespace app\api\model;

use app\common\model\AmountLog as AmountLogModel;
use app\common\exception\BaseException;

/**
 * 充值模型
 * Class AmountLog
 * @package app\api\model
 */
class AmountLog extends AmountLogModel
{


    /**
     * 获取充值订单
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-15
     * Time: 20:56
     */
    public static function getDetail($order_sn = 0,$user_id = 0){
        if (!$order = self::get([
            'order_sn' => $order_sn,
            'add_user' => $user_id,
            'mark' => 1
        ])
        ) {
            throw new BaseException(['msg' => '充值订单不存在']);
        }
        if($order['status'] == 2){
            throw new BaseException(['msg' => '订单已支付，请勿重复操作']);
        }
        return $order;
    }

}
