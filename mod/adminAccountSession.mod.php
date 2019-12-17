<?php
/**
 * 管理员模块模型
 * @author: jh
 * @date: 2017/6/21
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class AdminAccountSessionMod extends BaseMod
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct("admin_account_session");
    }
}