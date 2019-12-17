<?php

/**
 * 权限模型
 * @author: jh
 * @date: 2017/6/21
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class StoreUserAuthMod extends BaseMod {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct("store_user_auth");
    }

    /**
     * 检测权限名称等是否存在
     * @author jh
     * @date 2017/06/21
     */
    public function isExist($type, $value, $id = 0) {
        $cond = "{$type}='{$value}'";
        if ($id) {
            $cond .= " AND id!={$id}";
        }
        $query = array('fields' => 'id', 'cond' => $cond);
        $info = $this->getOne($query);
        $id = (int) $info['id'];
        return $id;
    }

    /**
     * 获取单行数据
     * @author jh
     * @date 2017/06/21
     */
    public function getInfo($type, $value) {
        $cond = "{$type} = '{$value}'";
        $query = array('fields' => '*', 'cond' => $cond);
        $info = $this->getOne($query);
        return $info;
    }

    /**
     * 获取菜单
     * @author jh
     * @date 2017/06/21
     */
    public function getAuthList() {
        $roleid = $_SESSION['store']['storeUserInfo']['storeuseradmin_id']; //当前登录人角色编号
        $sql = 'select distinct(auth_id) as auth_id from ' . DB_PREFIX . 'store_user_admin_auth where role_id in (' . $roleid . ') ';
        $authIds = $this->querySql($sql);
        $authChildIds = array(); //当前登录人拥有的二级菜单id
        foreach ($authIds as $v) {
            $authChildIds[] = $v['auth_id'];
        }
        $sql = 'select group_concat(distinct parent_id) as pid from ' . DB_PREFIX . 'store_user_auth where level = 2 and id in (' . implode(',', $authChildIds) . ') group by level ';
        $authPids = $this->querySql($sql); //当前登录人拥有的一级菜单id
        $sql = 'select * from ' . DB_PREFIX . 'store_user_auth where id in (' . $authPids[0]['pid'] . ') and is_menu = 1 order by sort asc';
        $info = $this->querySql($sql); //当前登录人拥有的一级菜单详细信息
        foreach ($info as $k => $v) {
            $sql = 'select * from ' . DB_PREFIX . 'store_user_auth where parent_id = ' . $v['id'] . ' and id in (' . implode(',', $authChildIds) . ') and is_menu = 1 order by sort asc ';
            $son = $this->querySql($sql);
            $info[$k]['son'] = $son; //一级菜单下，当前登录人拥有的二级菜单信息
        }
        return $info;
    }

}
