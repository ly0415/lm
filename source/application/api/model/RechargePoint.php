<?php

namespace app\api\model;

use app\common\model\RechargePoint as RechargePointModel;

/**
 * 余额充值规则模型
 * Class RechargePoint
 * @package app\api\model
 */
class RechargePoint extends RechargePointModel
{

    /**
     * 获取列表
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-23
     * Time: 17:57
     */
    public function getList(){
        return $this->where('mark','=',1)
            ->order('c_money')
            ->select();
    }


}