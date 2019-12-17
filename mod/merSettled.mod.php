<?php
/**
 * 店铺入驻模块模型
 * @author: jh
 * @date: 2017/6/21
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class merSettledMod extends BaseMod {
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct("store_joinin");
    }
}
?>