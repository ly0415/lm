<?php
/**
 * 短信模型
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class SmsLogMod extends BaseMod
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct("sms_log");
    }
}