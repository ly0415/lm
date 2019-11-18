<?php
/**
 * 用户模型
 * @author: jh
 * @date: 2017/6/21
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class SystemRoleAuthMod extends BaseMod {
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct("system_role_auth");
    }
}