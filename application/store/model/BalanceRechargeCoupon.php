<?php

namespace app\store\model;

use app\common\model\BalanceRechargeCoupon as BalanceRechargeCouponModel;

/**
 * 余额充值券模型
 * @author  luffy
 * @date    2019-07-10
 */
class BalanceRechargeCoupon extends BalanceRechargeCouponModel
{

    /**
     * 获取指定店铺余额充值总额
     * @author  luffy
     * @date    2019-07-10
     */
    public function getStoreRechargeTotal($store_id)
    {
        return parent::getRechargeTotal($store_id);
    }

}
