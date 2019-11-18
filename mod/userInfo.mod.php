<?php
/**
 * 管理员模块模型
 * @author: jh
 * @date: 2017/6/21
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class userInfoMod extends BaseMod {
    public $orderMod;
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct("user_info");
        $this->orderMod = &m('order');
    }

    public $source = array(
        '1' => '年龄',
        '2' => '性别',
        '3' => '着装',
        '4' => '用的手机',
        '5' => '身高',
        '6' => '体型',
        '7' => '谈吐',
        '8' => '来了几人'
    );

    /**
     * 检测是否有画像在数据库
     * @param int $order_sn
     * @return array|false
     */
    public function getUserInfo($order_sn)
    {
        $sql = "SELECT * FROM bs_user_info WHERE order_sn = " . $order_sn;
        $res = $this->querySql($sql);
        $data = unserialize($res[0]['content']);
        return $data;
    }
    /**
     * 计算二维数组的长度
     * @param $order_sn
     * @return array|int
     */
    public function countUserInfo($order_sn)
    {
        $sql = "SELECT * FROM bs_user_info WHERE order_sn = " .$order_sn;
        $res = $this->querySql($sql);
        $data = unserialize($res[0]['content']);
        $res = count($data);
        return $res;
    }
}