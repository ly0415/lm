<?php

/**
 * 用户角色模型
 * @author: wangshuo
 * @date: 2018/4/11
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class StoreUserAdminMod extends BaseMod {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct("store_user_admin");
    }

}
