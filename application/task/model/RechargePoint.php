<?php

namespace app\task\model;

use app\common\model\RechargePoint as RechargePointModel;

/**
 * 用户模型
 * Class User
 * @package app\task\model
 */
class RechargePoint extends RechargePointModel
{

    /**
     * 获取积分抵扣比例
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-15
     * Time: 19:27
     */
    public static function detail($id){

        return self::get($id);
    }

}
