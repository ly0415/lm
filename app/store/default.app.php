<?php

/**
 * 商家后台
 * @author wangh
 * @date 2017/07/19
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class DefaultApp extends BaseStoreApp {

    private $lang_id;
    private $areaAccountSessionMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
        $this->areaAccountSessionMod = &m('areaAccountSession');
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    /**
     * 空操作
     * @author lvji
     * @date 2015-03-20
     */
    public function emptyOperate() {
        $info = array();
        $this->setData($info);
    }

    /**
     * 首页
     * @author wangh
     * @date 2017/07/19
     */
    public function index() {

        //中英切换
        if ($this->lang_id == 1) {
            $str = 'ad_english_name';
        } else {
            $str = 'ad_name';
        }
        $store_id = $_SESSION['store']['storeId'];
        $opInfo = $_REQUEST['opInfo'] ? htmlspecialchars(trim($_REQUEST['opInfo'])) : '';

        $orderMod = &m('order');
        //交易情况异步切换
        if ($opInfo) {
            $start_time = $_REQUEST['start_time'] ? htmlspecialchars(trim($_REQUEST['start_time'])) : '';
            $end_time = $_REQUEST['end_time'] ? htmlspecialchars(trim($_REQUEST['end_time'])) : '';
            $timeSetArr = array(
                'start_time' => $start_time.' 00:00:00',
                'end_time' => $end_time.' 23:59:59'
            );
            //订单信息
            $upOrderInfo = $orderMod->getUpOrderCount(10, 0, $store_id, 0, $opInfo, $timeSetArr);
            //付款信息
            $payOrderInfo = $orderMod->getUpOrderCount(20, 0, $store_id, 1, $opInfo, $timeSetArr);
            $this->setData(array('upOrderInfo' => $upOrderInfo, 'payOrderInfo' => $payOrderInfo));
        }

        $jyInfo = $_REQUEST['jyInfo'] ? htmlspecialchars(trim($_REQUEST['jyInfo'])) : '';
        //交易趋势分析异步切换
        if ($jyInfo) {
            $start_time = $_REQUEST['start_time'] ? htmlspecialchars(trim($_REQUEST['start_time'])) : '';
            $end_time = $_REQUEST['end_time'] ? htmlspecialchars(trim($_REQUEST['end_time'])) : '';
            $timeSetArr = array(
                'start_time' => $start_time,
                'end_time' => $end_time
            );
            $trendInfo = $orderMod->getTransactionTrend(0, $store_id, $jyInfo, 0, $timeSetArr);
            $this->setData(array('trendInfo' => $trendInfo));
        }
        $gdInfo = $_REQUEST['gdInfo'] ? htmlspecialchars(trim($_REQUEST['gdInfo'])) : '';
        //商品TOP10异步切换
        if ($gdInfo) {
            $start_time = $_REQUEST['start_time'] ? htmlspecialchars(trim($_REQUEST['start_time'])) : '';
            $end_time = $_REQUEST['end_time'] ? htmlspecialchars(trim($_REQUEST['end_time'])) : '';
            $timeSetArr = array(
                'start_time' => $start_time,
                'end_time' => $end_time
            );
            $topInfo = $orderMod->getGoodsTop10(0, $store_id, $gdInfo, 0, $timeSetArr, $this->languageId);
            $this->setData(array('topInfo' => $topInfo));
        }

        //获取待办事项
        $backlogDaFuCount = $orderMod->getBacklogCount(10, 0, $store_id);
        $this->assign('backlogDaFuCount', $backlogDaFuCount);  //待付款数

        $backlogDaFaCount = $orderMod->getBacklogCount(20, 0, $store_id);
        $this->assign('backlogDaFaCount', $backlogDaFaCount);  //待发货数

        $backlogDaTuiCount = $orderMod->getBacklogCount(0, 0, $store_id, true);
        $this->assign('backlogDaTuiCount', $backlogDaTuiCount);  //待退货数
        //获取商品信息
        $storeGoodsMod = &m('storeGoods');
        $onSaleGoodsCount = $storeGoodsMod->getGoodsInfoCount(0, $store_id);
        $this->assign('onSaleGoodsCount', $onSaleGoodsCount);   //在售商品数

        $recommendGoodsCount = $storeGoodsMod->getGoodsInfoCount(0, $store_id, 1, 1);
        $this->assign('recommendGoodsCount', $recommendGoodsCount);   //推荐商品
        //订单信息
        $upOrderInfo = $orderMod->getUpOrderCount(10, 0, $store_id);
        $this->assign('upOrderInfo', $upOrderInfo);   //下单信息
        //付款信息
        $payOrderInfo = $orderMod->getUpOrderCount(20, 0, $store_id, 1);
        $this->assign('payOrderInfo', $payOrderInfo);   //付款信息
        //交易趋势分析
        $trendInfo = $orderMod->getTransactionTrend(0, $store_id, 'day', 1);
        $this->assign('trendInfo', $trendInfo);         //交易趋势
        //商品TOP10
        $top10 = $orderMod->getGoodsTop10(0, $store_id, 'day', 1, $this->languageId);
        $this->assign('top10', $top10);         //商品TOP10

        $systemConsoleMod = &m('systemConsole');
        $systemConsole = $systemConsoleMod->getOne(array(
            'cond' => "type = 5 and rebate_id = {$_SESSION['store']['storeId']}"
        ));

        if ($systemConsole['voucher_id'] != $_SESSION['store']['userId']) {//当前登录人没有权限打印订单
            $printCount = 0;
            $printOrderSn = '';
            $printMessage = '';
        } else {
            $ordersnArr = $orderMod->getPrintOrders();
            $printCount = count($ordersnArr);
            $printOrderSn = implode(',', $ordersnArr);
            $printMessage = '当前有' . $printCount . '个订单未打印';
        }

        $this->assign('printMessage', $printMessage);
        $this->assign('printOrderSn', $printOrderSn);
        $this->assign('printCount', $printCount);
        $this->assign('printReturn', $_SESSION['print_return']);


        $this->assign('lang_id', $this->lang_id);
        $this->addStoreLog('登录艾美平台');
        if ($_GET['lang_id'] == 1) {
            $this->display('index_1.html');
        } else {
            $this->display('index.html');
        }
    }

    /**
     * 登录界面
     * $Author: huxw $
     * $Datetime: 2016-11-02 17:27:16 $
     */
    public function login() {
        $this->assign('lang_id', $this->lang_id);
        if ($this->lang_id == 1) {
            $this->display('public/login_1.html');
        } else {
            $this->display('public/login.html');
        }
    }

    //创建验证码
    public function createCode() {
        import('captcha.lib');
        $captchaObj = new Captcha();
    }

    /**
     * 登录操作
     * @author lvji
     * @date 2015-3-10
     */
    public function doLogin() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $lang_id = $this->lang_id;
        //$userMod = &m('storeUser');
        $account_name = !empty($_REQUEST['account_name']) ? htmlspecialchars($_REQUEST['account_name']) : '';
        $password = !empty($_REQUEST['password']) ? htmlspecialchars($_REQUEST['password']) : '';
        $validate = !empty($_REQUEST['validate']) ? htmlspecialchars($_REQUEST['validate']) : '';

        $info = array();
        if (!$account_name) {
            $this->setData($info, $status = 0, $a['login_name']);
        }
        //获取用户信息
        $info_account = $this->getuserinfo($account_name);
        //各种判断
        if (!$info_account) {
            $this->setData($info, $status = 0, $a['login_No']);
        }
        if (!$password) {
            $this->setData($info, $status = 0, $a['login_Password']);
        }
        if (!$validate) {
            $this->setData($info, $status = 0, $a['login_Isempty']);
        }
        $tmp = checkCaptcha($validate);
        if (!$tmp['success']) {
            $this->setData($info, $status = 0, $a['login_Incorrect']);
        }
        if ($info_account['enable'] == 2) {
            $this->setData($info, $status = 0, $a['login_Disable']);
        }
        $store_status = $this->getStatus($info_account['store_id']);
        if ($store_status == 2) {
            $this->setData($info, $status = 0, $a['store_Disable']);
        }
        $md5Pass = md5($password); //md5加密
        if (!empty($info_account)) {
            if ($md5Pass == $info_account['password']) {  //判断输入的密码加密后和注册时的密码是否一致
//				$this->storeId = $info_account['id'];
//				$this->storeInfo = $info_account;
                //            $session_id = session_id(); // 用户登录时生成一个session_id
                // 查询用户是否已经登录
                $accountSessionInfo = $this->areaAccountSessionMod->getOne(array('cond' => "`user_id` = {$info_account['id']}", 'fields' => 'session_id,id,login_time'));
                if (empty($accountSessionInfo['session_id'])) {
                    $sessInsData = array(
                        'user_id' => $info_account['id'],
                        'session_id' => session_id(),
                        'login_ip' => $_SERVER['REMOTE_ADDR'],
                        'login_time' => time(),
                    );
                    $this->areaAccountSessionMod->doInsert($sessInsData);
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
                            $info['login_name'] = $info_account['login_name'];
                            $info['userId'] = $info_account['id'];
                            $info['url'] = base64_encode('?app=default&act=index&lang_id=0');
                            $this->setData($info, $status = 2, $message = "账号已被占用！");
//                        }
                        }
                    } else {
                        $sessEditData = array(
                            'session_id' => session_id(),
                            'login_ip' => $_SERVER['REMOTE_ADDR'],
                            'login_time' => time()
                        );
                        $this->areaAccountSessionMod->doEdit($accountSessionInfo['id'], $sessEditData);
                    }
                }
                $_SESSION['store']['userId'] = $info_account['id'];  //存入session
                $_SESSION['store']['storeId'] = $info_account['store_id'];  //存入session
                $_SESSION['store']['user_name'] = $info_account['real_name'];
                $_SESSION['store']['storeUserInfo'] = $info_account;
                $this->setData($info_account, $status = 1, $a['login_Success'], $url = '?app=default&act=index&lang_id=0');
            } else {
                $this->setData($info, $status = 0, $a['login_error']);
            }
        } else {
            $this->setData($info, $status = 0, $a['login_business']);
        }
    }

    /**
     *  继续登录
     */
    public function goOnLogin() {
        $userName = !empty($_REQUEST['login_name']) ? htmlspecialchars($_REQUEST['login_name']) : '';
        $userId = !empty($_REQUEST['userId']) ? intval($_REQUEST['userId']) : '';
        $url = !empty($_REQUEST['url']) ? htmlspecialchars($_REQUEST['url']) : '';
        $accountSessionInfo = $this->areaAccountSessionMod->getOne(array('cond' => "`user_id` = {$userId}", 'fields' => 'session_id,id,login_time'));
        $sessEditData = array(
            'session_id' => session_id(),
            'login_ip' => $_SERVER['REMOTE_ADDR'],
            'login_time' => time()
        );
        $this->areaAccountSessionMod->doEdit($accountSessionInfo['id'], $sessEditData);
        $info_account = $this->getuserinfo($userName);
        $_SESSION['store']['userId'] = $info_account['id'];  //存入session
        $_SESSION['store']['storeId'] = $info_account['store_id'];  //存入session
        $_SESSION['store']['user_name'] = $info_account['username'];
        $_SESSION['store']['storeUserInfo'] = $info_account;
        $info['url'] = base64_decode($url);
        $this->setData($info, $status = '1', '');
    }

    /**
     * 获取店铺的状态
     */
    public function getStatus($store_id) {
        $storeMod = &m('store');
        $rs = $storeMod->getOne(array('cond' => "`id`='{$store_id}'", 'fields' => "is_open"));
        return $rs['is_open'];
    }

    /**
     * 获取用户信息
     */
    public function getuserinfo($account_name) {
        $userMod = &m('storeUser');
        $where = '   where  mark =1  and enable=1 and  user_name = "' . $account_name . '"';
        $sql = 'select  * from   ' . DB_PREFIX . 'store_user ' . $where;
        $data = $userMod->querySql($sql);
        return $data[0];
    }

    /**
     * 退出系统
     * @author lvji
     * @date 2015-03-10
     */
    public function logout() {
        $areaAccountMod = &m('areaAccountSession');
        $areaAccountMod->doDelete(array('`user_id`' => $this->storeUserId));

        unset($_SESSION['store']['storeId']);
        //第一步：删除服务器端
        session_destroy();
        //第二步：删除实际的session
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600);
        }

        $_SESSION = array();  //第三步：删除$_SESSION全部变量数组
        $info['msg'] = '退出成功';
        $info['url'] = 'store.php?app=default&act=login&lang_id=' . $this->lang_id;
        $this->addStoreLog("退出店铺后台");
        $this->setData($info, $status = 1);
    }

    /**
     * 获取版本号
     * @author lvji
     * @date 2015-03-20
     */
    public function fetchVersion() {
        $info = array();
        $info['version'] = APPVERSION;
        $this->setData($info);
    }

}

?>