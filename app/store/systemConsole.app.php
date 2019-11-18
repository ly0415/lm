<?php

/**
 * 控制台
 * @author  hjp
 * @date    2018-09-07
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class SystemConsoleApp extends BaseStoreApp {

    private $model;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    /**
     * 控制台
     * @author  hjp
     * @date    2018-09-07
     */
    public function index() {
        $config = array();
        $store_id = $_SESSION['store']['storeId'];
        $users = &m("storeUser")->getUserByStore($store_id);
        $config['notice_users'] = $users;
        $this->assign('config',$config);
        $this->display('systemConsole/index.html');
    }

    /**
     * 选择提醒用户
     * @author  hjp
     * @date    2018-09-07
     */
    public function selectNoticeUser()
    {
        $store_id = $_SESSION['store']['storeId'];
        $user_id = !empty($_REQUEST['user_id']) ? htmlspecialchars(trim($_REQUEST['user_id'])) : '';
        $para = array(
            'store_id'=>$store_id,
            'user_id'=>$user_id,
        );
        $rs = &m('systemConsole')->selectNoticeUser($para);
        if($rs){
            $this->jsonResult('设置成功！');
        }else{
            $this->jsonError('设置失败！');
        }
    }

}
