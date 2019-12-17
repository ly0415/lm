<?php

namespace app\store\model;

use app\common\model\UserPointSite as UserPointSiteModel;

/**
 * 用户优惠券模型
 * Class UserCoupon
 * @package app\store\model
 */
class UserPointSite extends UserPointSiteModel
{
    /**
     * 获取优惠券列表
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-03
     * Time: 14:17
     */
    public function getList()
    {
        return $this->where('1=1')->find();
    }


}