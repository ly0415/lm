<?php

/**
 * 角色模块制器
 * @author: jh
 * @date: 2017/6/22
 */
class SystemRoleApp extends BackendApp {

    private $systemRoleMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->systemRoleMod = &m('systemRole');
    }

    /**
     * 析构函数
     */
    public function __destruct() {

    }
	public function getTypeTest(){
        $this->display('public/index1.html');

	}
	//创建验证码
    public function doIndex() {
        echo 'this is ceshi';
    }
    /**
     * 角色列表
     * @author wangshuo
     * @date 2017/09/26
     */
    public function index() {
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) : '';
        $this->assign('name', $name);
        // 数据
        $where = ' where 1=1 and a.mark = 1';

        //搜索
        if ($this->shorthand == 'EN') {
            if (!empty($name)) {
                $where .= '   and  a.`english_name`  like  "%' . $name . '%"';
            }
            $role_arr = G('role_type_en');
        } else if($this->shorthand == 'ZH'){
            if (!empty($name)) {
                $where .= ' and a.name like "%' . $name . '%"';
            }
            $role_arr = G('role_type');
        }
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "system_role as a" . $where;
        $totalCount = $this->systemRoleMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;

        //中 英切换
        $sql = 'select a.id,a.name,a.english_name,a.level,a.sort,a.add_time,s.cate_name as s_name,s.id as s_id,s.english_name as se_name from ' . DB_PREFIX . 'system_role as a
         LEFT JOIN ' . DB_PREFIX . 'store_cate as s on s.id=a.store_cate_id
        ' . $where . ' order by a.sort';
        $data = $this->systemRoleMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        $sclMod = &m('storeCateLang');
        foreach ($data['list'] as $k => $v) {
            if ($v['s_id']) {
                $info = $sclMod->getOne(array("cond" => "cate_id = " . $v['s_id'] . " and lang_id=" . $this->lang_id));
                if ($info) {
                    $data['list'][$k]['s_name'] = $info['cate_name'];
                }
            }
            if ($v['add_time']) {
                $data['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            } else {
                $data['list'][$k]['add_time'] = '';
            }
            $data['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
//            $rs['list'][$k]['sort'] = $total - $k - 20 * ($p - 1); //倒叙
        }
        $this->assign("role_arr", $role_arr);
        $this->assign('list', $data['list']);
        $this->assign('page_html', $data['ph']);
        $this->display('systemRole/index.html');
    }

    /*
     * 角色添加页面
     * @author jh
     * @date 2017/06/22
     */

    public function add() {
        $areaCateMod = &m('storeCate');
        $list = $areaCateMod->getCateList($this->lang_id);
        if ($this->shorthand == 'EN') {
            $role_arr = G('role_type_en');
        } else if($this->shorthand == 'ZH'){
            $role_arr = G('role_type');
        }
        $this->assign("role_arr", $role_arr);
        $this->assign("cate_list", $list);
        $this->display('systemRole/add.html');
    }

    /*
     * 角色添加入库
     * @author jh
     * @date 2017/06/22
     */

    public function doAdd() {
        $name = $_REQUEST['name'] ? htmlspecialchars(trim($_REQUEST['name'])) : '';
        $english_name = $_REQUEST['english_name'] ? htmlspecialchars(trim($_REQUEST['english_name'])) : '';
        $store_cate_id = $_REQUEST['store_cate_id'] ? htmlspecialchars(trim($_REQUEST['store_cate_id'])) : '';
        $level = $_REQUEST['level'] ? htmlspecialchars(trim($_REQUEST['level'])) : '';
        $sort = $_REQUEST['sort'] ? htmlspecialchars(trim($_REQUEST['sort'])) : 5;
        if (empty($name)) {
            $this->setData(array(), '0',$this->langDataBank->project->role_cn_required);
        }
        //level 4 大区管理员
        if (empty($store_cate_id) && ($level == 4)) {
            $this->setData(array(), '0', $this->langDataBank->project->role_cn_required);
        }
        if (mb_strlen($name) > 20) {
            $this->setData(array(), '0', $this->langDataBank->project->role_ch_name_lenth);
        }
        if ($this->systemRoleMod->isExist('name', $name)) {
            $this->setData(array(), '0', $this->langDataBank->project->role_ch_name_exist);
        }
        if (empty($english_name)) {
            $this->setData(array(), '0', $this->langDataBank->project->role_en_name_require);
        }
        if (mb_strlen($english_name) > 30) {
            $this->setData(array(), '0', $this->langDataBank->project->role_en_name_lenth);
        }
        if ($this->systemRoleMod->isExist('english_name', $english_name)) {
            $this->setData(array(), '0', $this->langDataBank->project->role_en_name_exist);
        }
        if (empty($level)) {
            $this->setData(array(), '0', $this->langDataBank->project->level_require);
        }
        if (!preg_match("/^[1-9][0-9]{0,2}$/", $level)) {
            $this->setData(array(), '0', $this->langDataBank->project->level_rule);
        }
        if (!empty($sort)) {
            if (!preg_match("/^[1-9][0-9]{0,2}$/", $sort)) {
                $this->setData(array(), '0', $this->langDataBank->project->sort_rule);
            }
        }
        //插入角色表
        $data = array(
            'name' => $name,
            'english_name' => $english_name,
            'level' => $level,
            'sort' => $sort,
            'add_time' => time(),
            'modify_time' => time(),
            'store_cate_id' => $store_cate_id
        );
        $res = $this->systemRoleMod->doInsert($data);
        if ($res) {
            $this->addLog('添加角色');
            $this->setData(array(), '1', $this->langDataBank->public->add_success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->public->add_error);
        }
    }

    /*
     * 角色编辑入库
     * @author jh
     * @date 2017/06/22
     */

    public function edit() {
        $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
        if (empty($id)) {
            $this->setData(array(), '0', $this->langDataBank->public->system_error);
        }
        //角色信息
        $data = $this->systemRoleMod->getInfo('id=' . $id);
        $areaCateMod = &m('storeCate');
        $list = $areaCateMod->getCateList($this->lang_id);
        if ($this->shorthand == 'EN') {
            $role_arr = G('role_type_en');
        } else if($this->shorthand == 'ZH'){
            $role_arr = G('role_type');
        }
        $this->assign("role_arr", $role_arr);
        $this->assign("cate_list", $list);
        $this->assign('data', $data);
        $this->display("systemRole/edit.html");
    }

    /*
     * 角色编辑入库
     * @author jh
     * @date 2017/06/22
     */

    public function doEdit() {
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : 0;
        $name = $_REQUEST['name'] ? htmlspecialchars(trim($_REQUEST['name'])) : '';
        $english_name = $_REQUEST['english_name'] ? htmlspecialchars(trim($_REQUEST['english_name'])) : '';
        $level = $_REQUEST['level'] ? htmlspecialchars(trim($_REQUEST['level'])) : '';
        $sort = $_REQUEST['sort'] ? htmlspecialchars(trim($_REQUEST['sort'])) : 5;
        $store_cate_id = $_REQUEST['store_cate_id'] ? htmlspecialchars(trim($_REQUEST['store_cate_id'])) : '';
        if (empty($id)) {
            $this->setData(array(), '0', $this->langDataBank->public->system_error);
        }
        if (empty($name)) {
            $this->setData($info = array(), '0', $this->langDataBank->project->role_cn_required);
        }
        //level 4 大区管理员
        if (empty($store_cate_id) && ($level == 4)) {
            $this->setData(array(), '0', $this->langDataBank->project->role_cn_required);
        }
        if (mb_strlen($name) > 20) {
            $this->setData(array(), '0', $this->langDataBank->project->role_ch_name_lenth);
        }
        if ($this->systemRoleMod->isExist('name', $name, $id)) {
            $this->setData(array(), '0', $this->langDataBank->project->role_ch_name_exist);
        }
        if (empty($english_name)) {
            $this->setData($info = array(), '0', $this->langDataBank->project->role_en_name_require);
        }
        if (mb_strlen($english_name) > 30) {
            $this->setData(array(), '0', $this->langDataBank->project->role_en_name_lenth);
        }
        if ($this->systemRoleMod->isExist('english_name', $english_name, $id)) {
            $this->setData(array(), '0', $this->langDataBank->project->role_en_name_exist);
        }
        if (empty($level)) {
            $this->setData(array(), '0', $this->langDataBank->project->level_require);
        }
        if (!preg_match("/^[1-9][0-9]{0,2}$/", $level)) {
            $this->setData(array(), '0', $this->langDataBank->project->level_rule);
        }
        if (!empty($sort)) {
            if (!preg_match("/^[1-9][0-9]{0,2}$/", $sort)) {
                $this->setData(array(), '0', $this->langDataBank->project->sort_rule);
            }
        }
        //更新角色表
        $data = array(
            'name' => $name,
            'english_name' => $english_name,
            'level' => $level,
            'sort' => $sort,
            'modify_time' => time(),
            'store_cate_id' => $store_cate_id
        );
        $res = $this->systemRoleMod->doEdit($id, $data);
        if ($res) {
            $this->addLog('编辑角色');
            $this->setData(array(), '1',$this->langDataBank->public->edit_success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->public->edit_fail);
        }
    }

    /**
     * 配置权限
     * @author wangshuo
     * @date 2017/06/27
     */
    public function roleAuth() {
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : 0;
        if (empty($id)) {
            $this->setData(array(), '0', $this->langDataBank->public->system_error);
        }
        $this->assign('role_id', $id);
        // 当前角色信息
        $data = $this->systemRoleMod->getInfo('id=' . $id);
        $this->assign('data', $data);
        //角色拥有的权限
        $sql = 'select group_concat(auth_id) as auth_ids from ' . DB_PREFIX . 'system_role_auth where role_id = ' . $id . ' group by role_id ';
        $authData = $this->systemRoleMod->querySql($sql);
        $this->assign('auth_arr', explode(',', $authData[0]['auth_ids']));
        //所有权限
        $sql = 'select id,title,english_title from ' . DB_PREFIX . 'system_auth where level = 1 order by sort asc';
        $authData = $this->systemRoleMod->querySql($sql);
        foreach ($authData as $k => $v) {
            $sql = 'select id,title,english_title from ' . DB_PREFIX . 'system_auth where parent_id = ' . $v['id'] . ' order by sort asc';
            $authChild = $this->systemRoleMod->querySql($sql);
            $authData[$k]['child'] = $authChild;
        }
        $this->assign('authData', $authData);
        $this->assign('shorthand', $this->shorthand);
        $this->display("systemRole/roleAuth.html");
    }

    /**
     * 配置权限入库
     * @author jh
     * @date 2017/06/22
     */
    public function roleAuthSave() {
        $role_id = $_REQUEST['role_id'] ? htmlspecialchars(trim($_REQUEST['role_id'])) : 0;
        if (empty($role_id)) {
            $this->setData(array(), '0', $this->langDataBank->public->system_error);
        }
        $authid = $_REQUEST['authid'] ? $_REQUEST['authid'] : array();
        if (empty($authid)) {
            $this->setData(array(), '0',  $this->langDataBank->project->assign_permission);
        }
        //更新角色权限关联表
        $roleAuthMod = &m('systemRoleAuth');
        $sql = 'select id,auth_id from ' . DB_PREFIX . 'system_role_auth where role_id = ' . $role_id;
        $authData = $roleAuthMod->querySql($sql);
        $oldAuthid = array();
        //删除不要的数据
        foreach ($authData as $v) {
            if (in_array($v['auth_id'], $authid)) {
                $oldAuthid[] = $v['auth_id'];
                continue;
            }
            $delData = array(
                "cond" => "id = " . $v['id'],
            );
            $roleAuthMod->doDelete($delData);
        }
        //插入新数据
        foreach ($authid as $v) {
            if (in_array($v, $oldAuthid)) {
                continue;
            }
            $dataTemp = array(
                'role_id' => $role_id,
                'auth_id' => $v,
                'add_time' => time()
            );
            $roleAuthMod->doInsert($dataTemp);
        }
        $this->addLog('配置权限');
        $info['url'] = "admin.php?app=systemRole&act=index";
        $this->setData($info, $status = '1', $this->langDataBank->project->permission_success);
    }

    /**
     * 删除角色
     * @author jh
     * @date 2017/06/22
     */
    public function dele() {
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        if (empty($id)) {
            $this->setData(array(), '0', $this->langDataBank->public->system_error);
        }
        // 删除数据
        $res = $this->systemRoleMod->doMark($id);
        if ($res) {//删除成功
            $this->addLog('删除角色');
            $this->setData(array(), '1', $this->langDataBank->public->drop_success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->public->drop_fail);
        }
    }

}
