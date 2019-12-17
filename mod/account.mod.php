<?php
/**
 * 管理员模块模型
 * @author: jh
 * @date: 2017/6/21
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class AccountMod extends BaseMod {
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct("account");
    }
    /**
     * 检测注册名称等是否存在
     * @author jh
     * @date 2017/06/21
     */
    public function isExist($type, $value, $id = 0) {
        $cond = "{$type}='{$value}'";
        $cond .= '  and mark =1  ';
        if ($id) {
            $cond .= " AND id!={$id}";
        }
        $query = array('fields' => 'id', 'cond' => $cond);
        $info = $this->getOne($query);
        $id = (int)$info['id'];
        return $id;
    }
    /**
     * 获取单行数据
     * @author jh
     * @date 2017/06/21
     */
    public function getInfo($cond) {
        $query = array('fields' => '*', 'cond' => $cond);
        $info = $this->getOne($query);
        return $info;
    }
}