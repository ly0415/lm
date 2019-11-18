<?php

/**
 * 角色模块制器
 * @author wangshuo
 * @date 2018/04/12
 */
class storeUserAdminApp extends BaseStoreApp {

    private $storeUserAdminMod;
    private $lang_id;
    private $storeMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->storeUserAdminMod = &m('storeUserAdmin');
        $this->storeMod = &m('store');
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    /**
     * 角色列表
     * @author wangshuo
     * @date 2018/04/12
     */
    public function index() {
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) : '';
        $this->assign('name', $name);
        $names = !empty($_REQUEST['names']) ? htmlspecialchars(trim($_REQUEST['names'])) : '';
        $this->assign('names', $names);
        $store_id = $this->storeId;
        // 数据
        $where = 'where s.distinguish = 0 and a.mark = 1 and  a.level != 1 and s.lang_id =' . $this->defaulLang . ' and a.store_id =' . $store_id;
        //搜索
        if ($this->lang_id == 1) {
            if (!empty($names)) {
                $where .= '   and  a.`english_name`  like  "%' . $names . '%"';
            }
            $role_arr = G('role_type_en');
        } else {
            if (!empty($name)) {
                $where .= ' and a.name like "%' . $name . '%"';
            }
            $role_arr = G('role_type');
        }
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "store_user_admin" . $where;
        $totalCount = $this->storeUserAdminMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;

        //中 英切换
        $this->assign('lang_id', $this->lang_id);
        $sql = 'select a.id,a.name,a.english_name,a.level,a.sort,a.add_time,s.store_name as s_name from ' . DB_PREFIX . 'store_user_admin as a
         LEFT JOIN ' . DB_PREFIX . 'store_lang as s on s.store_id=a.store_id
        ' . $where . ' order by a.sort';
        $data = $this->storeUserAdminMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        foreach ($data['list'] as $k => $v) {
            if ($v['id']) {
                $info = $this->storeUserAdminMod->getOne(array("cond" => "id = " . $v['id']));
                if ($info) {
                    $data['list'][$k]['name'] = $info['name'];
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
        $this->display('storeUserAdmin/index.html');
    }

    /*
     * 角色添加页面
     * @author wangshuo
     * @date 2018/04/12
     */

    public function add() {

        $lang_id = $_REQUEST['lang_id'];
        if ($lang_id == 1) {
            $role_arr = G('role_type_en');
            unset($role_arr[1]);
        } else {
            $role_arr = G('role_type');
            unset($role_arr[1]);
        }
        $this->assign("role_arr", $role_arr);
        $this->assign('store', $this->getUseStore());
        $this->display('storeUserAdmin/add.html');
    }

    /**
     * 获取启用的站点
     * @author wangshuo
     * @date 2018/04/12
     */
    public function getUseStore() {
        $sql = 'SELECT  c.id,l.store_name  FROM  ' . DB_PREFIX . 'store  as c
                 left join  ' . DB_PREFIX . 'store_lang as l on  c.id = l.store_id
                 WHERE c.is_open = 1 and l.distinguish = 0  and  l.lang_id =' . $this->defaulLang;
        $rs = $this->storeMod->querySql($sql);
        return $rs;
    }

    /*
     * 角色添加入库
     * @author wangshuo
     * @date 2018/04/12
     */

    public function doAdd() {
        $this->load($this->lang_id, 'admin/admin');
        $a = $this->langData;
        $name = $_REQUEST['name'] ? htmlspecialchars(trim($_REQUEST['name'])) : '';
        $english_name = $_REQUEST['english_name'] ? htmlspecialchars(trim($_REQUEST['english_name'])) : '';
        $store_id = $this->storeId;
        $level = $_REQUEST['level'] ? htmlspecialchars(trim($_REQUEST['level'])) : '';
        $sort = $_REQUEST['sort'] ? htmlspecialchars(trim($_REQUEST['sort'])) : 5;
        if (empty($name)) {
            $this->setData($info, $status = '0', $a['Role_ch_name']);
        } else {
            $query = array(
                'cond' => "`name` = '{$name}' and mark='1'",
                'fields' => 'name'
            );
            $rs = $this->storeUserAdminMod->getOne($query);
            if ($rs['name']) {
                $this->setData($info, $status = '0', $a['class_ch_anme']);
            }
        }
        if (mb_strlen($name) > 20) {
            $this->setData(array(), '0', $a['Role_ch_names']);
        }
        if (empty($english_name)) {
            $this->setData($info, $status = '0', $a['Role_en_name']);
        } else {
            $query = array(
                'cond' => "`english_name` = '{$english_name}' and mark='1'",
                'fields' => 'english_name'
            );
            $rs = $this->storeUserAdminMod->getOne($query);
            if ($rs['english_name']) {
                $this->setData($info, $status = '0', $a['class_en_name']);
            }
        }

        if (mb_strlen($english_name) > 30) {
            $this->setData(array(), '0', $a['Role_eng_names']);
        }

        if (empty($level)) {
            $this->setData(array(), '0', $a['Role_level']);
        }
        if (!preg_match("/^[1-9][0-9]{0,2}$/", $level)) {
            $this->setData(array(), '0', $a['Role_Grade']);
        }
        if (!empty($sort)) {
            if (!preg_match("/^[1-9][0-9]{0,2}$/", $sort)) {
                $this->setData(array(), '0', $a['class_sort']);
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
            'store_id' => $store_id
        );
        $res = $this->storeUserAdminMod->doInsert($data);
        if ($res) {
            $this->addLog('添加角色');
            $this->setData(array(), '1', $a['add_Success']);
        } else {
            $this->setData(array(), '0', $a['add_fail']);
        }
    }

    /*
     * 角色编辑入库
     * @author wangshuo
     * @date 2018/04/12
     */

    public function edit() {
        $this->load($this->lang_id, 'admin/admin');
        $a = $this->langData;
        $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
        $lang_id = $_REQUEST['lang_id'];
        if (empty($id)) {
            $this->setData(array(), '0', $a['System_error']);
        }
        //角色信息
        $sql = 'SELECT  *  FROM  ' . DB_PREFIX . 'store_user_admin  where id = ' . $id;
        $rs = $this->storeUserAdminMod->querySql($sql);
        $this->assign('store', $this->getUseStore());
        if ($lang_id == 1) {
            $role_arr = G('role_type_en');
            unset($role_arr[1]);
        } else {
            $role_arr = G('role_type');
            unset($role_arr[1]);
        }
        $this->assign("role_arr", $role_arr);
        $this->assign('data', $rs[0]);
        $this->display("storeUserAdmin/edit.html");
    }

    /*
     * 角色编辑入库
     * @author wangshuo
     * @date 2018/04/12
     */

    public function doEdit() {
        $this->load($this->lang_id, 'admin/admin');
        $a = $this->langData;
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : 0;
        $name = $_REQUEST['name'] ? htmlspecialchars(trim($_REQUEST['name'])) : '';
        $english_name = $_REQUEST['english_name'] ? htmlspecialchars(trim($_REQUEST['english_name'])) : '';
        $level = $_REQUEST['level'] ? htmlspecialchars(trim($_REQUEST['level'])) : '';
        $sort = $_REQUEST['sort'] ? htmlspecialchars(trim($_REQUEST['sort'])) : 5;
        $store_id = $this->storeId;
        if (empty($id)) {
            $this->setData(array(), '0', $a['System_error']);
        }
        if (empty($name)) {
            $this->setData(array(), $status = '0', $a['Role_ch_name']);
        } else {
            $query = array(
                'cond' => "`name` = '{$name}' and `id` != '{$id}' and `mark` =1",
                'fields' => 'name'
            );
            $rs = $this->storeUserAdminMod->getOne($query);
            if ($rs['name']) {
                $this->setData(array(), $status = '0', $a['class_ch_anme']);
            }
        }
        if (mb_strlen($name) > 20) {
            $this->setData(array(), '0', $a['Role_ch_names']);
        }
        if (empty($english_name)) {
            $this->setData(array(), $status = '0', $a['Role_en_name']);
        } else {
            $query = array(
                'cond' => "`english_name` = '{$name}' and `id` != '{$id}' and `mark` =1",
                'fields' => 'english_name'
            );
            $rs = $this->storeUserAdminMod->getOne($query);
            if ($rs['english_name']) {
                $this->setData(array(), $status = '0', $a['class_en_name']);
            }
        }
        if (mb_strlen($english_name) > 30) {
            $this->setData(array(), '0', $a['Role_eng_names']);
        }
        if (empty($level)) {
            $this->setData(array(), '0', $a['Role_level']);
        }
        if (!preg_match("/^[1-9][0-9]{0,2}$/", $level)) {
            $this->setData(array(), '0', $a['Role_Grade']);
        }
        if (!empty($sort)) {
            if (!preg_match("/^[1-9][0-9]{0,2}$/", $sort)) {
                $this->setData(array(), '0', $a['class_sort']);
            }
        }
        //更新角色表
        $data = array(
            'name' => $name,
            'english_name' => $english_name,
            'level' => $level,
            'sort' => $sort,
            'modify_time' => time(),
            'store_id' => $store_id
        );
        $res = $this->storeUserAdminMod->doEdit($id, $data);
        if ($res) {
            $this->addLog('编辑角色');
            $this->setData(array(), '1', $a['edit_Success']);
        } else {
            $this->setData(array(), '0', $a['edit_fail']);
        }
    }

    /**
     * 配置权限
     * @author wangshuo
     * @date 2018/04/12
     */
    public function roleAuth() {
        $this->load($this->lang_id, 'admin/admin');
        $a = $this->langData;
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : 0;
        $lang_id = $_REQUEST['lang_id'] ? htmlspecialchars(trim($_REQUEST['lang_id'])) : 0;
        if (empty($id)) {
            $this->setData(array(), '0', $a['System_error']);
        }
        $this->assign('role_id', $id);
        //中英切换
        $this->assign('lang_id', $this->lang_id);
        // 当前角色信息
        $sql = 'select * from ' . DB_PREFIX . 'store_user_admin where id = ' . $id;
        $data = $this->storeUserAdminMod->querySql($sql);
        $this->assign('data', $data[0]);
        // 获取当前站点
        $sql = 'select store_type from ' . DB_PREFIX . 'store where id = ' . $data[0]['store_id'] . ' and is_open =1';
        $store = $this->storeUserAdminMod->querySql($sql);
        if ($store[0]['store_type'] == 4) {
            //角色拥有的权限
            $sql = 'select group_concat(auth_id) as auth_ids from ' . DB_PREFIX . 'store_user_admin_auth where role_id = ' . $id . ' group by role_id ';
            $authData = $this->storeUserAdminMod->querySql($sql);
            $this->assign('auth_arr', explode(',', $authData[0]['auth_ids']));
            //所有权限
            $sql = 'select id,title,english_title from ' . DB_PREFIX . 'store_user_auth where level = 1  order by sort asc';
            $authData = $this->storeUserAdminMod->querySql($sql);
            foreach ($authData as $k => $v) {
                $sql = 'select id,title,english_title from ' . DB_PREFIX . 'store_user_auth where parent_id = ' . $v['id'] . '   order by sort asc';
                $authChild = $this->storeUserAdminMod->querySql($sql);
                $authData[$k]['child'] = $authChild;
            }
            $this->assign('authData', $authData);
        } else {
            //角色拥有的权限
            $sql = 'select group_concat(auth_id) as auth_ids from ' . DB_PREFIX . 'store_user_admin_auth where role_id = ' . $id . ' group by role_id ';
            $authData = $this->storeUserAdminMod->querySql($sql);
            $this->assign('auth_arr', explode(',', $authData[0]['auth_ids']));
            //所有权限
            $sql = 'select id,title,english_title from ' . DB_PREFIX . 'store_user_auth where level = 1 and is_odm = 0 order by sort asc';
            $authData = $this->storeUserAdminMod->querySql($sql);
            foreach ($authData as $k => $v) {
                $sql = 'select id,title,english_title from ' . DB_PREFIX . 'store_user_auth where parent_id = ' . $v['id'] . ' and is_odm = 0  order by sort asc';
                $authChild = $this->storeUserAdminMod->querySql($sql);
                $authData[$k]['child'] = $authChild;
            }
            $this->assign('authData', $authData);
        }
//        //角色拥有的权限
//        $sql = 'select group_concat(auth_id) as auth_ids from ' . DB_PREFIX . 'store_user_admin_auth where role_id = ' . $id . ' group by role_id ';
//        $authData = $this->storeUserAdminMod->querySql($sql);
//        $this->assign('auth_arr', explode(',', $authData[0]['auth_ids']));
//        //所有权限
//        $sql = 'select id,title,english_title from ' . DB_PREFIX . 'store_user_auth where level = 1 order by sort asc';
//        $authData = $this->storeUserAdminMod->querySql($sql);
//        foreach ($authData as $k => $v) {
//            $sql = 'select id,title,english_title from ' . DB_PREFIX . 'store_user_auth where parent_id = ' . $v['id'] . ' order by sort asc';
//            $authChild = $this->storeUserAdminMod->querySql($sql);
//            $authData[$k]['child'] = $authChild;
//        }
//        $this->assign('authData', $authData);
        $this->display("storeUserAdmin/roleAuth.html");
    }

    /**
     * 配置权限入库
     * @author wangshuo
     * @date 2018/04/12
     */
    public function roleAuthSave() {
        $this->load($this->lang_id, 'admin/admin');
        $a = $this->langData;
        $lang_id = !empty($_REQUEST['lang_id']) ? (int) ($_REQUEST['lang_id']) : '0';
        $role_id = $_REQUEST['role_id'] ? htmlspecialchars(trim($_REQUEST['role_id'])) : 0;
        if (empty($role_id)) {
            $this->setData(array(), '0', $a['System_error']);
        }
        $authid = $_REQUEST['authid'] ? $_REQUEST['authid'] : array();
        if (empty($authid)) {
            $this->setData(array(), '0', $a['class_authid']);
        }
        //更新角色权限关联表
        $storeUserAdminAuth = &m('storeUserAdminAuth');
        $sql = 'select id,auth_id from ' . DB_PREFIX . 'store_user_admin_auth where role_id = ' . $role_id;
        $authData = $storeUserAdminAuth->querySql($sql);
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
//            print_r($delData);
//            exit;
            $storeUserAdminAuth->doDelete($delData);
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
            $storeUserAdminAuth->doInsert($dataTemp);
        }
        $this->addLog('配置权限');
        $info['url'] = "store.php?app=storeUserAdmin&act=index&lang_id={$lang_id}";
        $this->setData($info, $status = '1', $a['class_Config']);
    }

    /**
     * 删除角色
     * @author wangshuo
     * @date 2018/04/12
     */
    public function dele() {
        $this->load($this->lang_id, 'admin/admin');
        $a = $this->langData;
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        if (empty($id)) {
            $this->setData(array(), '0', $a['System_error']);
        }
        // 删除数据
        $res = $this->storeUserAdminMod->doMark($id);
        if ($res) {//删除成功
            $this->addLog('删除角色');
            $this->setData(array(), '1', $a['delete_Success']);
        } else {
            $this->setData(array(), '0', $a['delete_fail']);
        }
    }

}
