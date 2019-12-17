<?php

if (!defined('IN_ECM')) {
    die('Forbidden');
}

class StoreUserApp extends BaseStoreApp {

    private $storeUserMod;
    private $storeUserAdminMod;
    private $lang_id;

    /**
     * 构造函数
     * @author wangshuo
     * @date 2018/04/12
     */
    public function __construct() {
        parent::__construct();
        $this->storeMod = &m('store');
        $this->storeUserMod = &m('storeUser');
        $this->storeUserAdminMod = &m('storeUserAdmin');
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
    }

    /**
     * 权限用户表
     * @author wangshuo
     * @date 2018/04/12
     */
    public function index() {
        $username = !empty($_REQUEST['username']) ? htmlspecialchars(trim($_REQUEST['username'])) : '';
        $storeuseradmin_id = !empty($_REQUEST['storeuseradmin_id']) ? htmlspecialchars(trim($_REQUEST['storeuseradmin_id'])) : '';
        $where = " where su.mark =1 and  sd.mark =1";
        if (!empty($username)) {
            $where .= " and su.`real_name`='{$username}'";
        }
        if (!empty($storeuseradmin_id)) {
            $where .= " and su.`storeuseradmin_id`='{$storeuseradmin_id}'";
        }
        $where .= " and su.mark ='1' and  sd.level!=1 and su.store_id = " . $this->storeId . " order by su.add_time desc ";
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "store_user " . $where;
        $totalCount = $this->storeUserMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;

        $sql = "select su.*,s.store_name from " . DB_PREFIX . "store_user  as su left join " . DB_PREFIX . "store_user_admin as sd on su.storeuseradmin_id = sd.id left join " . DB_PREFIX . "store_lang as s on su.store_id = s.store_id and s.distinguish = 0  and s.lang_id = " . $this->defaulLang . $where;
        $rs = $this->storeUserMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        foreach ($rs['list'] as $k => $v) {
            $rs['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            if ($v['add_time']) {
                $rs['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            } else {
                $rs['list'][$k]['add_time'] = '';
            }
            $rs['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
//            $rs['list'][$k]['sort'] = $total - $k - 20 * ($p - 1); //倒叙
        }
        $this->assign('p', $p);
        $this->assign('username', $username);
        $this->assign('storeuseradmin_id', $storeuseradmin_id);
        $this->assign('storeInfo', $this->getStore($this->storeId));
        $this->assign('list', $rs['list']);
        $this->assign('page', $rs['ph']);
        $this->assign('lang_id', $this->lang_id);
        if ($this->lang_id == 0) {
            $this->display('storeUser/index.html');
        } else {
            $this->display('storeUser/index_en.html');
        }
    }

    /**
     * 用户添加页面
     * @author wangshuo
     * @date 2018/04/12
     */
    public function add() {
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $this->assign('p', $p);
        $this->assign('lang_id', $this->lang_id);
        $this->assign('storeInfo', $this->getStore($this->storeId));
        $this->assign('act', 'index');
        if ($this->lang_id == 0) {
            $this->display('storeUser/add.html');
        } else {
            $this->display('storeUser/add_en.html');
        }
    }

    /**
     * 获取店铺资料
     * @author wangshuo
     * @date 2018/04/12
     */
    public function getStore($store_id = null) {
        if ($store_id) {
            $where .= " and store_id=" . $store_id;
        }
        $sql = 'SELECT  id,name  FROM  ' . DB_PREFIX . 'store_user_admin  where level!= 1 and mark=1  ' . $where . ' order by id';
        $res = $this->storeMod->querySql($sql);
        return $res;
    }

    /**
     * 店铺管理员天剑
     * @auth wanyan
     * @date 2017-09-07
     */
    public function doAdd() {
        $this->load($this->lang_id, 'admin/admin');
        $a = $this->langData;
        $lang_id = !empty($_REQUEST['lang_id']) ? htmlspecialchars(trim($_REQUEST['lang_id'])) : '0';
        $username = !empty($_REQUEST['username']) ? htmlspecialchars(trim($_REQUEST['username'])) : '';
        $login_name = !empty($_REQUEST['login_name']) ? htmlspecialchars(trim($_REQUEST['login_name'])) : '';
        $password = !empty($_REQUEST['password']) ? htmlspecialchars(trim($_REQUEST['password'])) : '';
        $mobile = !empty($_REQUEST['mobile']) ? htmlspecialchars(trim($_REQUEST['mobile'])) : '';
        $email = !empty($_REQUEST['email']) ? htmlspecialchars(trim($_REQUEST['email'])) : '';
        $QQ = !empty($_REQUEST['QQ']) ? htmlspecialchars(trim($_REQUEST['QQ'])) : '';
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $store_id = $this->storeId;
        $storeuseradmin_id = !empty($_REQUEST['storeuseradmin_id']) ? htmlspecialchars(trim($_REQUEST['storeuseradmin_id'])) : '';
        $info = array();
        if (empty($storeuseradmin_id)) {
            $this->setData($info, $status = '0', $a['user_userid']);
        }
        if (empty($username)) {
            $this->setData($info, $status = '0', $a['user_en_name']);
        } else {
            $query = array(
                'cond' => "`real_name` = '{$username}' and mark='1'",
                'fields' => 'real_name'
            );
            $rs = $this->storeUserMod->getOne($query);
            if ($rs['real_name']) {
                $this->setData($info, $status = '0', $a['user_en_names']);
            }
        }
//        if(empty($mobile)) {
//            $this->setData($info, $status = '0', $message = '联系方式不能为空！');
//        }
        if (!empty($mobile) && strlen($mobile) != 11) {
            $this->setData($info, $status = '0', $a['user_mobile']);
        }
        if (!empty($mobile) && !matchPhone($mobile)) {
            $this->setData($info, $status = '0', $a['user_mobiles']);
        }
        if (!empty($email) && !matchEmail($email)) {
            $this->setData($info, $status = '0', $a['user_email']);
        }
        if (!empty($QQ) && !preg_match('/^[1-9]+\d*$/', $QQ)) {
            $this->setData($info, $status = '0', $a['user_qq']);
        }
        if (empty($login_name)) {
            $this->setData($info, $status = '0', $a['user_login']);
        } else {
            $query = array(
                'cond' => "`login_name` = '{$login_name}' and mark='1'",
                'fields' => 'login_name'
            );
            $rs = $this->storeUserMod->getOne($query);
            if ($rs['login_name']) {
                $this->setData($info, $status = '0', $a['user_logins']);
            }
        }
        if (empty($password)) {
            $this->setData($info, $status = '0', $a['user_password']);
        }
        if (strlen($password) < 6 || strlen($password) > 16) {
            $this->setData($info, $status = '0', $a['user_passwords']);
        }
        $insert_data = array(
            'real_name' => $username,
            'login_name' => $login_name,
            'password' => md5($password),
            'mobile' => $mobile,
            'email' => $email,
            'QQ' => $QQ,
            'store_id' => $store_id,
            'enable' => 1,
            'add_time' => time(),
            'storeuseradmin_id' => $storeuseradmin_id
        );
        $insert_id = $this->storeUserMod->doInsert($insert_data);
        if ($insert_id) {
            $this->addLog('商铺管理员添加操作');
            $info['url'] = "?app=storeUser&act=index&lang_id={$lang_id}&p={$p}";
            $this->setData($info, $status = '1', $a['add_Success']);
        } else {
            $this->setData($info, $status = '0', $a['add_fail']);
        }
    }

    /**
     * 店铺人员状态更新
     * @author wangshuo
     * @date 2018/04/12
     */
    public function getStatus() {
        $enable = !empty($_REQUEST['is_open']) ? intval($_REQUEST['is_open']) : '';
        $user_id = !empty($_REQUEST['cate_id']) ? intval($_REQUEST['cate_id']) : '';
        $data = array(
            'enable' => $enable
        );
        $rs = $this->storeUserMod->doEdit($user_id, $data);
        if ($rs) {
            $this->setData($info = array(), $status = '1', $message = '');
        } else {
            $this->setData($info = array(), $status = '0', $message = '启用失败！');
        }
    }

    /**
     * 店铺人员编辑页面
     * @author wangshuo
     * @date 2018/04/12
     */
    public function edit() {
        $id = !empty($_REQUEST['id']) ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        $rs = $this->storeUserMod->getOne(array('cond' => "`id`='{$id}'"));
        $this->assign('list', $rs);
        $this->assign('storeInfo', $this->getStore($this->storeId));
        $this->assign('act', 'index');
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $this->assign('p', $p);
        $this->assign('lang_id', $this->lang_id);
        if ($this->lang_id == 0) {
            $this->display('storeUser/edit.html');
        } else {
            $this->display('storeUser/edit_en.html');
        }
    }

    /**
     * 店铺人员编辑
     * @author wangshuo
     * @date 2018/04/12
     */
    public function doEdit() {
        $this->load($this->lang_id, 'admin/admin');
        $a = $this->langData;
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $lang_id = !empty($_REQUEST['lang_id']) ? htmlspecialchars(trim($_REQUEST['lang_id'])) : '0';
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '';
        $username = !empty($_REQUEST['username']) ? htmlspecialchars(trim($_REQUEST['username'])) : '';
        $login_name = !empty($_REQUEST['login_name']) ? htmlspecialchars(trim($_REQUEST['login_name'])) : '';
        $password = !empty($_REQUEST['password']) ? htmlspecialchars(trim($_REQUEST['password'])) : '';
        $mobile = !empty($_REQUEST['mobile']) ? htmlspecialchars(trim($_REQUEST['mobile'])) : '';
        $email = !empty($_REQUEST['email']) ? htmlspecialchars(trim($_REQUEST['email'])) : '';
        $QQ = !empty($_REQUEST['QQ']) ? htmlspecialchars(trim($_REQUEST['QQ'])) : '';
        $store_id = $this->storeId;
        $storeuseradmin_id = !empty($_REQUEST['storeuseradmin_id']) ? htmlspecialchars(trim($_REQUEST['storeuseradmin_id'])) : '';
        $info = array();
        if (empty($storeuseradmin_id)) {
            $this->setData($info, $status = '0', $a['user_userid']);
        }
        if (empty($username)) {
            $this->setData($info, $status = '0', $a['user_en_name']);
        } else {
            $query = array(
                'cond' => "`username` = '{$username}' and `id` != '{$id}' and `mark` =1",
                'fields' => 'username'
            );
            $rs = $this->storeUserMod->getOne($query);
            if ($rs['username']) {
                $this->setData($info, $status = '0', $a['user_en_names']);
            }
        }
//        var_dump($mobile);die;
//        if(empty($mobile)) {
//            $this->setData($info, $status = '0', $message = '联系方式不能为空！');
//        }
        if (!empty($mobile) && strlen($mobile) != 11) {
            $this->setData($info, $status = '0', $a['user_mobile']);
        }
        if (!empty($mobile) && !matchPhone($mobile)) {
            $this->setData($info, $status = '0', $a['user_mobiles']);
        }
        if (!empty($email) && !matchEmail($email)) {
            $this->setData($info, $status = '0', $a['user_email']);
        }
        if (!empty($QQ) && !preg_match('/^[1-9]+\d*$/', $QQ)) {
            $this->setData($info, $status = '0', $a['user_qq']);
        }
        if (empty($login_name)) {
            $this->setData($info, $status = '0', $a['user_login']);
        } else {
            $query = array(
                'cond' => "`login_name` = '{$login_name}' and id` != '{$id}' and mark =1",
                'fields' => 'login_name'
            );
            $rs = $this->storeUserMod->getOne($query);
            if ($rs['login_name']) {
                $this->setData($info, $status = '0', $a['user_logins']);
            }
        }
        if (empty($password)) {
            $this->setData($info, $status = '0', $a['user_password']);
        }
        if (strlen($password) < 6 || strlen($password) > 16) {
            $this->setData($info, $status = '0', $a['user_passwords']);
        }
        if ($password == '******') {
            $edit_data = array(
                'real_name' => $username,
                'login_name' => $login_name,
                'mobile' => $mobile,
                'email' => $email,
                'QQ' => $QQ,
                'store_id' => $store_id,
                'modify_time' => time(),
                'storeuseradmin_id' => $storeuseradmin_id
            );
        } else {
            $edit_data = array(
                'real_name' => $username,
                'login_name' => $login_name,
                'password' => md5($password),
                'mobile' => $mobile,
                'email' => $email,
                'QQ' => $QQ,
                'store_id' => $store_id,
                'modify_time' => time(),
                'storeuseradmin_id' => $storeuseradmin_id
            );
        }
        $insert_id = $this->storeUserMod->doEdit($id, $edit_data);
        if ($insert_id) {
            $this->addLog('商铺管理员编辑操作');
            $info['url'] = "?app=storeUser&act=index&lang_id={$lang_id}&p={$p}";
            $this->setData($info, $status = '1', $a['edit_Success']);
        } else {
            $this->setData($info, $status = '0', $a['edit_fail']);
        }
    }

    /**
     * 店铺人员删除
     * @author wangshuo
     * @date 2018/04/12
     */
    public function dele() {
        $this->load($this->lang_id, 'admin/admin');
        $a = $this->langData;
        $id = !empty($_REQUEST['id']) ? htmlspecialchars($_REQUEST['id']) : '';
        $rs = $this->storeUserMod->doMark($id);
        if ($rs) {
            $this->addLog('商铺管理员删除操作');
            $this->setData($info = array(), $status = '1', $a['delete_Success']);
        } else {
            $this->setData($info = array(), $status = '0', $a['delete_fail']);
        }
    }

}
