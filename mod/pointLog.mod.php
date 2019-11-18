<?php
/**
 * 日志模块模型
 * @author jh
 * @date 2017-06-30
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class PointLogMod extends BaseMod
{
    public function __construct()
    {
        parent::__construct("point_log");
    }

    /**
     * 生成日志
     */
    public function add($username, $note, $userid, $deposit, $expend, $order_sn = null)
    {
        $logData = array(
            'operator' => '--',
            'username' => $username,
            'add_time' => time(),
            'deposit' => $deposit,
            'expend' => $expend,
            'note' => $note,
            'userid' => $userid,
        );
        if ($order_sn) {
            $logData['order_sn'] = $order_sn;
        }
        $res = $this->doInsert($logData);
        return $res;
    }
}