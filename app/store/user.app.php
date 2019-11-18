<?php

/**
 * 用户中心
 * @author lvji
 *
 */
//include_once 'sms.app.php';
//include_once 'mail.app.php';
class UserApp extends BaseStoreApp {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 登录
     * @author wangh
     * @date 2217/07/19
     */
    public function login() {
        $this->assign('lang_id', $this->lang_id);
        if ($this->lang_id == 1) {
            $this->display('public/login_1.html');
        } else {
            $this->display('public/login.html');
        }
    }

    /**
     * 登录
     * @author wangh
     * @date 2217/07/19
     */
    public function doLogin() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $userModel = &m('user');
        $carModel = &m('car');
        $username = !empty($_REQUEST['username']) ? htmlspecialchars($_REQUEST['username']) : '';
        $password = !empty($_REQUEST['password']) ? htmlspecialchars($_REQUEST['password']) : '';
        $md5Pass = md5($password); //md5加密
        //根据传来的登录帐号判断是那种类型登录方式，以便查询是否存在该用户
        if (preg_match("/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/", $username)) {//登录类型为邮箱
            $info = $userModel->getInfo('email', $username);
        } elseif (preg_match("/^(13[0-9]|14[0-9]|15[0-9]|17[0-9]|18[0-9])\d{8}$/i", $username)) {//登录类型为邮箱
            $info = $userModel->getInfo('phone', $username);
        } elseif (preg_match("/^[A-Z]\d{16}$/i", $username)) {//登录类型为车架号，需去绑定车辆表查询主账号的userid
            $mainUserId = $carModel->getUserIdByCode($username);
            if ($mainUserId) {
                $info = $userModel->getInfoById($mainUserId);
            }
        } else {
            $info = $userModel->getInfoByName($username); //查询该用户名是否存在
        }
        if (is_array($info)) {
            if ($md5Pass == $info['password']) {//判断输入的密码加密后和注册时的密码是否一致
                $this->adminId = $info['id'];
                $this->adminInfo = $info;
                $_SESSION['adminId'] = $info['id']; //存入session
                $this->setData($info, $status = 'success', $a['login_Success']);
            } else {
                $this->setData($info, $status = 'error', $a['login_error']);
            }
        } else {
            $this->setData($info, $status = 'error', $a['login_Nosuch']);
        }
    }

}
