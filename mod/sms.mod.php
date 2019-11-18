<?php
/**
 * 短信模型
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class SmsMod extends BaseMod
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct("sms");
    }

    /**
     * 根据手机号获取最新验证码
     */
    public function getSmsCode($phone)
    {
        $smsMod = &m('sms');
        $time = time() - 600;
        $sql = "select * from bs_sms where phone = {$phone} and send_time >= {$time} order by id desc limit 1";
        $data = $smsMod->querySql($sql);
        return $data[0];
    }
}