<?php

/**
 * 来源列表
 * @author: 王硕
 * @date: 2018/5/21
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class sourceListMod extends BaseMod {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct("store_source");
    }

}

?>