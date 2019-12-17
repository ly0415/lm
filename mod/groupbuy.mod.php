<?php
/**
 * 店铺模块模型
 * @author: jh
 * @date: 2017/6/21
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class GroupbuyMod extends BaseMod {
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct("goods_group_buy");
    }

}
?>