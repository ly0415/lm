<?php

/**
 * 平台人员管理模块
 * @author wh
 * @date 2017-7-20
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class AccountApp extends BackendApp {

    private $accountMod;
    private $adminAccountSessionMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->accountMod = &m('account');
        $this->adminAccountSessionMod = &m('adminAccountSession');
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    /**
     * 登录界面
     * $Author: baoll
     * $Datetime: 2016-12-26 17:27:16
     */
    public function login() {
        $this->display('public/login.html');
    }

    /**
     * 登录操作
     * @author lvji
     * @date 2015-3-10
     */
    public function doLogin() {
        $accountName = !empty($_REQUEST['account_name']) ? htmlspecialchars($_REQUEST['account_name']) : '';
        $password = !empty($_REQUEST['password']) ? htmlspecialchars($_REQUEST['password']) : '';
        $validate = !empty($_REQUEST['validate']) ? htmlspecialchars($_REQUEST['validate']) : '';
        if (empty($accountName)) {
            $this->setData(array(), $status = '0', $this->langDataBank->project->user_required);
        }
        if (empty($password)) {
            $this->setData(array(), $status = '0', $this->langDataBank->project->password_required);
        }
        $info_account = $this->accountMod->getInfo(array('account_name="' . $accountName . '"', 'mark=1')); //查询该用户名是否存在
        if (!$info_account) {
            $this->setData(array(), $status = '0', $this->langDataBank->project->user_not_exist);
        }
        /* 验证吗 */
        if (empty($validate)) {
            $this->setData(array(), $status = '0', $this->langDataBank->project->code_required);
        }
        if ($validate != $_SESSION['seccode']) {
            $this->setData(array(), $status = '0', $this->langDataBank->project->wrong_code);
        }
        $md5Pass = md5($password); //md5加密
        if (!empty($info_account)) {
            if ($md5Pass == $info_account['password']) { //判断输入的密码加密后和注册时的密码是否一致
                //  同一账号不能同时登录
//            $session_id = session_id(); // 用户登录时生成一个session_id
                // 查询用户是否已经登录
                $accountSessionInfo = $this->adminAccountSessionMod->getOne(array('cond' => "`user_id` = {$info_account['id']}", 'fields' => 'session_id,id,login_time'));
                if (empty($accountSessionInfo['session_id'])) {
                    $sessInsData = array(
                        'user_id' => $info_account['id'],
                        'session_id' => session_id(),
                        'login_ip' => $_SERVER['REMOTE_ADDR'],
                        'login_time' => time(),
                    );
                    $this->adminAccountSessionMod->doInsert($sessInsData);
                } else {
                    if (time() - $accountSessionInfo['login_time'] < 600) {
                        if (session_id() != $accountSessionInfo['session_id']) {
//                        $sessEditData = array(
//                            'session_id' => session_id(),
//                            'login_ip' => $_SERVER['REMOTE_ADDR'],
//                            'login_time' => time()
//                        );
//                        $id = $this->frontAccountSessionMod->doEdit($accountSessionInfo['id'], $sessEditData);
//                        if ($id) {
                            $info['flag'] = 1;
                            $info['userName'] = $info_account['account_name'];
                            $info['userId'] = $info_account['id'];
                            $info['url'] = base64_encode('admin.php?app=default&act=index');
                            $this->setData($info, $status = 2, $this->langDataBank->project->account_occupied);
//                        }
                        }
                    } else {
                        $sessEditData = array(
                            'session_id' => session_id(),
                            'login_ip' => $_SERVER['REMOTE_ADDR'],
                            'login_time' => time()
                        );
                        $this->adminAccountSessionMod->doEdit($accountSessionInfo['id'], $sessEditData);
                    }
                }

                // $accountRole = &m('user');
                $query = array(
                    'cond' => "`id` = '{$info_account['id']}' ",
                    'fields' => '`role_id`'
                );
                $info = $this->accountMod->getOne($query);
                if (empty($info)) {
                    $this->setData(array(), $status = '0', $this->langDataBank->project->role_not_assigned);
                }
                $roleInfo = $this->getCurRole($info_account['id']);
                $_SESSION['roleid'] = $roleInfo['role_id'];
                $_SESSION['rolename'] = $roleInfo['name'];
                $_SESSION['account_id'] = $info_account['id']; //存入session
                $_SESSION['account_name'] = $info_account['account_name'];  //int 市
                //添加区域国家标识 modify by lee
                $_SESSION['admin']['store_country'] = $roleInfo['store_cate_id'];
                //end
                $this->setData(array("url" => '?app=default&act=index'), $status = '1', $this->langDataBank->project->login_success);
            } else {
                $this->setData(array(), $status = '0', $this->langDataBank->project->wrong_pass);
            }
        } else {
            $this->setData(array(), $status = '0', $this->langDataBank->project->user_not_exist);
        }
    }

    /**
     *  继续登录
     */
    public function goOnLogin() {
        $userName = !empty($_REQUEST['username']) ? htmlspecialchars($_REQUEST['userName']) : '';
        $userId = !empty($_REQUEST['userId']) ? intval($_REQUEST['userId']) : '';
        $url = !empty($_REQUEST['url']) ? htmlspecialchars($_REQUEST['url']) : '';
        $accountSessionInfo = $this->adminAccountSessionMod->getOne(array('cond' => "`user_id` = {$userId}", 'fields' => 'session_id,id,login_time'));
        $sessEditData = array(
            'session_id' => session_id(),
            'login_ip' => $_SERVER['REMOTE_ADDR'],
            'login_time' => time()
        );
        $this->adminAccountSessionMod->doEdit($accountSessionInfo['id'], $sessEditData);
        $query = array(
            'cond' => "`id` = '{$userId}' ",
            'fields' => '`role_id`'
        );
        $info = $this->accountMod->getOne($query);
        if (empty($info)) {
            $this->setData(array(), $status = '0',  $this->langDataBank->project->role_not_assigned);
        }
        $roleInfo = $this->getCurRole($userId);
        $_SESSION['roleid'] = $roleInfo['role_id'];
        $_SESSION['rolename'] = $roleInfo['name'];
        $_SESSION['account_id'] = $userId; //存入session
        $_SESSION['account_name'] = $userName;  //int 市
        //添加区域国家标识 modify by lee
        $_SESSION['admin']['store_country'] = $roleInfo['store_cate_id'];
        $info['url'] = base64_decode($url);
        $this->setData($info, $status = '1', '');
    }

    /**
     * 获取用户的角色名称
     * @author wanyan
     * @date 2017-08-29
     */
    public function getCurRole($user_id) {
        $sql = "select aa.role_id,sr.name,sr.store_cate_id from " . DB_PREFIX . 'account as aa left join ' . DB_PREFIX . 'system_role as sr on aa.role_id = sr.id where aa.id=' . $user_id;
        $rs = $this->accountMod->querySql($sql);
        return $rs[0];
    }

    /**
     * 退出系统
     * @author lvji
     * @date 2015-03-10
     */
    public function logout() {

        // 删除表中session_id
        $adminAccountMod = &m('adminAccountSession');
        $adminAccountMod->doDelete(array('`user_id`' => $this->accountId));
        unset($_SESSION['adminId']);
        //第一步：删除服务器端
        $_SESSION = array();  //第三步：删除$_SESSION全部变量数组
        session_destroy();
        //第二步：删除实际的session
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600);
        }
        $this->setData($info = array('url' => 'admin.php?app=account&act=login'), 1, $this->langDataBank->project->exit_success);//exit_success
    }

    /**
     * 管理员列表
     * @author wh
     * @date 2017/7/24
     */
    public function accountList() {
        $accountname = !empty($_REQUEST['accountname']) ? htmlspecialchars(trim($_REQUEST['accountname'])) : '';
        $phome = !empty($_REQUEST['phome']) ? htmlspecialchars(trim($_REQUEST['phome'])) : '';
        $email = !empty($_REQUEST['email']) ? htmlspecialchars(trim($_REQUEST['email'])) : '';
        $this->assign('accountname', $accountname);
        $this->assign('phone', $phome);
        $this->assign('email', $email);
        $where = '  where  1=1 and mark =1';
            if (!empty($accountname)) {
                $where .= '  and  account_name  like "%' . $accountname . '%"';
            }
            if (!empty($phome)) {
                $where .= '  and  phone  like "%' . $phome . '%"';
            }
            if (!empty($email)) {
                $where .= '  and  email  like "%' . $email . '%"';
            }
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "account " . $where;
        $totalCount = $this->accountMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        //中 英切换
        $this->assign('lang_id', $this->lang_id);
        $sql = 'select  * from  ' . DB_PREFIX . 'account  ' . $where . ' order by id desc';
        $data = $this->accountMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        foreach ($data['list'] as $k => $v) {
            if ($v['add_time']) {
                $data['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            } else {
                $data['list'][$k]['add_time'] = '';
            }
            $data['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
//            $rs['list'][$k]['sort'] = $total - $k - 20 * ($p - 1); //倒叙
        }
        $this->assign('list', $data['list']);
        $this->assign('page_html', $data['ph']);
        $this->display('account/accountList.html');
    }

    /**
     * 管理员添加
     * @author wh
     * @date 2017/7/24
     */
    public function accountAdd() {
        $rs = $this->getRoleList();
        $this->assign('role_list', $rs);
        $this->display('account/accountAdd.html');
    }

    /**
     * 获取所有的角色
     * @author wanyan
     * @date 2017/7/24
     */
    public function getRoleList() {
        $sql = "select `id`,`name`,`english_name` from " . DB_PREFIX . "system_role where `mark` = '1'";
        $rs = $this->accountMod->querySql($sql);
        return $rs;
    }

    /**
     * 处理添加
     * @author wh
     * @date 2017/7/24
     */
    public function doAdd() {
        $account_name = htmlspecialchars(trim($_REQUEST['account_name']));
        $password = $_REQUEST['password'];
        $phone = htmlspecialchars(trim($_REQUEST['phone']));
        $email = htmlspecialchars(trim($_REQUEST['email']));
        $role_id = !empty($_REQUEST['role_id']) ? intval($_REQUEST['role_id']) : '0';
        if (empty($account_name)) {
            $this->setData(array(), '0', $this->langDataBank->project->admin_name_required);
        }
        if (empty($password)) {
            $this->setData(array(), '0', $this->langDataBank->project->login_pass_required);
        }
        if (strlen($password) < 6) {
            $this->setData(array(), '0', $this->langDataBank->project->password_length);
        }
        if (empty($phone)) {
            $this->setData(array(), '0', $this->langDataBank->project->phone_required);
        }
        if (!empty($phone)) {
            if (!matchPhone($phone)) {
                $this->setData(array(), '0', $this->langDataBank->project->phone_format);
            }
        }
        if (!empty($email)) {
            if (!matchEmail($email)) {
                $this->setData(array(), '0', $this->langDaBank->project->email_format);
            }
        }
        if ($this->accountMod->isExist('account_name', $account_name)) {
            $this->setData(array(), '0', $this->langDataBank->project->admin_name_repeat);
        }
        if ($this->accountMod->isExist('phone', $phone)) {
            $this->setData(array(), '0', $this->langDataBank->project->phone_repeat);
        }
        if (empty($role_id)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->role_required);
        }
        // account 表 插入信息
        $data = array(
            'account_name' => $account_name,
            'password' => md5($password),
            'phone' => $phone,
            'email' => $email,
            'role_id' => $role_id,
            'add_time' => time()
        );
        $res = $this->accountMod->doInsert($data);
        if ($res) {
            $this->addLog('添加管理人员信息');
            $this->setData(array(), '1', $this->langDataBank->public->add_success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->public->add_error);
        }
    }

    /**
     * 管理员编辑
     * @author wh
     * @date 2017/7/24
     */
    public function accountEdit() {
        $id = $_REQUEST['id'];
        $sql = 'select  aa.*,sr.name,sr.english_name from  ' . DB_PREFIX . 'account as aa left join ' . DB_PREFIX . 'system_role as sr on aa.role_id = sr.id where aa.mark=1 and aa.id=' . $id;
        $data = $this->accountMod->querySql($sql);
        $this->assign('data', $data[0]);
        $rs = $this->getRoleList();
        $this->assign('role_list', $rs);
        $this->display('account/accountEdit.html');
    }

    /**
     * 处理编辑
     * @author wh
     * @date 2017/7/24
     */
    public function doEdit() {
        $id = $_REQUEST['id'];
        $account_name = htmlspecialchars(trim($_REQUEST['account_name']));
        $password = $_REQUEST['password'];
        $phone = htmlspecialchars(trim($_REQUEST['phone']));
        $email = htmlspecialchars(trim($_REQUEST['email']));
        $role_id = !empty($_REQUEST['role_id']) ? intval($_REQUEST['role_id']) : '0';
        if (empty($account_name)) {
            $this->setData(array(), '0', $this->langDataBank->project->admin_name_required);
        }
        if (empty($password)) {
            $this->setData(array(), '0', $this->langDataBank->project->login_pass_required);
        }
        if (strlen($password) < 6) {
            $this->setData(array(), '0', $this->langDataBank->project->password_length);
        }
        if (empty($phone)) {
            $this->setData(array(), '0', $this->langDataBank->project->phone_required);
        }
        if (!empty($phone)) {
            if (!matchPhone($phone)) {
                $this->setData(array(), '0', $this->langDataBank->project->phone_format);
            }
        }
        if (!empty($email)) {
            if (!matchEmail($email)) {
                $this->setData(array(), '0',  $this->langDaBank->project->email_format);
            }
        }
        if ($this->accountMod->isExist('account_name', $account_name, $id)) {
            $this->setData(array(), '0', $this->langDataBank->project->admin_name_repeat);
        }
        if ($this->accountMod->isExist('phone', $phone, $id)) {
            $this->setData(array(), '0', $this->langDataBank->project->phone_repeat);
        }
        if (empty($role_id)) {
            $this->setData(array(), '0', $this->langDataBank->project->role_required);
        }
        if ($password == '******') {
            $data = array(
                'account_name' => $account_name,
                'phone' => $phone,
                'email' => $email,
                'role_id' => $role_id,
                'modify_time' => time()
            );
        } else {
            $data = array(
                'account_name' => $account_name,
                'password' => md5($password),
                'phone' => $phone,
                'email' => $email,
                'role_id' => $role_id,
                'modify_time' => time()
            );
        }
        $res = $this->accountMod->doEdit($id, $data);
        if ($res) {
            $this->addLog('修改管理人员信息');
            $this->setData(array(), '1', $this->langDataBank->public->edit_success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->public->edit_fail);
        }
    }

    /**
     * 删除
     * @author wh
     * @date 2017/7/24
     */
    public function dele() {
        $this->load($this->lang_id, 'admin/admin');
        $a = $this->langData;
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        if (empty($id)) {
            $this->setData(array(), '0', $this->langDataBank->public->system_error);
        }
        // 删除数据
        $res = $this->accountMod->doMark($id);
        if ($res) {//删除成功
            $this->addLog('删除管理人员');
            $this->setData(array(), '1', $this->langDataBank->public->drop_success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->public->drop_fail);
        }
    }

}
