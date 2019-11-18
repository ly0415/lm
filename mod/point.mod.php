<?php
/**
 * 积分模块
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class PointMod extends BaseMod {
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct("user_point_site");
    }

}
?>