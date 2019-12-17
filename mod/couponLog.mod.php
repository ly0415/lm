<?php
/**
 * 电子劵
 * @date: 2017/9/25
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class CouponLogMod extends BaseMod
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct("coupon_log");
    }

}

?>