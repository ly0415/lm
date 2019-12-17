<?php
/**
 * 角色模型
 * @author: jh
 * @date: 2017/6/21
 */
if (!defined('IN_ECM')) {die('Forbidden');
}
class SystemRoleMod extends BaseMod{
    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct("system_role");
    }
    /**
     * 检测角色名称等是否存在
     * @author jh
     * @date 2017/06/21
     */
    public function isExist($type, $value, $id = 0){
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
    function getInfo($cond){
        $query = array('fields' => '*', 'cond' => $cond);
        $info = $this->getOne($query);
        return $info;
    }
}