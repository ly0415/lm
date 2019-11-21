<?php

/**
 * 用户中心
 * @author lvji
 *
 */
//include_once 'sms.app.php';
//include_once 'mail.app.php';
class UserApp extends BaseFrontApp {

    public $userMod;
    private $fxTreeMod;
    private $fxUserMod;
    private $fxuserMoneyMod;
    private $frontAccountSessionMod;
    private $pointMod;

    public function __construct() {
        parent::__construct();
        $this->userMod = &m('user');
        $this->fxTreeMod = &m('fxuserTree');
        $this->fxUserMod = &m('fxuser');
        $this->fxuserMoneyMod = &m('fxuserMoney');
        $this->frontAccountSessionMod = &m('frontAccountSession');
        $this->pointMod = &m('point');
    }

    /**
     * 登录
     * @author wangh
     * @date 2217/07/19
     */
    public function login() {
        //语言包
        $returnUrl1 = !empty($_REQUEST['returnUrl']) ? $_REQUEST['returnUrl'] : '';
        $this->assign('returnUrl1', $returnUrl1);
        $this->load($this->shorthand, 'user_login/user_login');
        $this->assign('langdata', $this->langData);
        $lang_id = $this->shorthand;
        $this->assign('lang_id', $lang_id);
        $returnUrl = !empty($_REQUEST['pageUrl']) ? $_REQUEST['pageUrl'] : '';
        $this->assign('returnUrl', $returnUrl);
        $this->display('public/login.html');
    }

    /**
     * 登录
     * @author wnayan
     * @date 2017/08-01
     */
    public function yanzheng() {
        $this->load($this->shorthand, 'user_login/user_login');
        $this->assign('langdata', $this->langData);
        $email = !empty($_REQUEST['email']) ? htmlspecialchars(trim($_REQUEST['email'])) : '';
        $returnUrl = !empty($_REQUEST['returnUrl']) ? urlencode($_REQUEST['returnUrl']) : '';
        $returnUrl1 = !empty($_REQUEST['returnUrl1']) ? urlencode($_REQUEST['returnUrl1']) : '';
        $langid = !empty($_REQUEST['langid']) ? urlencode($_REQUEST['langid']) : '';
        $query = array(
            'cond' => "(`phone`='{$email}' or `email`='{$email}') and mark=1",
            'fields' => 'username'
        );
        $res = $this->userMod->getOne($query);
        if (empty($res['username'])) {
            $data['flag'] = 1; // 系统没有该用户，需要注册
            $data['message'] = "";
            $data['url'] = "index.php?app=user&act=register&storeid={$this->storeid}&lang={$langid}&username=" . base64_encode($email) . "&returnUrl=" . $returnUrl . "&returnUrl1=" . $returnUrl1;
            //$data['url'] = 'index.php?app=user&act=register';
            echo json_encode($data);
            exit;
        } else {
            $data['flag'] = 2; // 系统有该用户，需要登录
            $data['message'] = "";
            $data['url'] = "index.php?app=user&act=login_stepk&lang={$langid}&username=" . base64_encode($email) . "&returnUrl=" . $returnUrl . "&returnUrl1=" . $returnUrl1;
            echo json_encode($data);
            exit;
        }
    }

    /**
     * 登录步骤第二步
     * @author wnayan
     * @date 2017/08-01
     */
    public function login_step() {
        //语言包
        $this->load($this->shorthand, 'user_login/user_login');
        $this->assign('langdata', $this->langData);
        $lang_id = $this->shorthand;
        $this->assign('lang_id', $lang_id);
        $this->assign('returnUrl', $_REQUEST['returnUrl']);
        $this->assign('returnUrl1', $_REQUEST['returnUrl1']);
        $this->assign('username', base64_decode($_REQUEST['username']));
        $this->display('public/login_step.html');
    }

    /**
     * PC端快捷登录
     * @author wangshuo
     * @date 2018/08-20
     */
    public function login_stepk() {
        //语言包
        $this->load($this->shorthand, 'user_login/user_login');
        $this->assign('langdata', $this->langData);
        $this->assign('lang', $this->langid);
        $this->assign('returnUrl', urlencode($_REQUEST['returnUrl']));
        $this->assign('returnUrl1', urlencode($_REQUEST['returnUrl1']));
        $this->assign('username', base64_decode($_REQUEST['username']));
        $this->display('public/register_china1.html');
    }

    /**
     * PC端验证快捷登录
     * @author wangs
     * @date 2018/8/20
     */
    public function dologin_stepk() {
        $phone = !empty($_REQUEST['phone']) ? htmlspecialchars($_REQUEST['phone']) : '';
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;
        $returnUrl = !empty($_REQUEST['returnUrl']) ? $_REQUEST['returnUrl'] : '';
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;  //多语言商品
        if (empty($phone)) {
            $this->setData(array(), $status = '0', '手机号码必填！');
        }
        $phoneCode = $_REQUEST['code'];
        //查看当前是否有此用户
        $userSql = 'select * from ' . DB_PREFIX . 'user where phone = ' . $phone;
        $userData = $this->userMod->querySql($userSql);
        if ($userData[0]['phone'] == '') {
            if (!preg_match('/^(13[0-9]|14[0-9]|15[0-9]|16[0-9]|17[0-9]|18[0-9]|19[0-9])\d{8}$/', $phone)) {
                $this->setData(array(), $status = '0', '手机号码格式错误');
            }
            //注册
            $smsCode = $this->getSmsCode($phone);
            if ($phoneCode != $smsCode) {
                $this->setData(array(), $status = '0', '验证码不正确！');
            }
            if (empty($phoneCode)) {
                $this->setData(array(), $status = '0', '验证码必填！');
            }
            $storeMod = &m('store');
            $store_info = $storeMod->getOne(array("cond" => "id=" . $storeid));
            $pSql = "SELECT *  FROM " . DB_PREFIX . 'user_point_site';
            $res = $this->userMod->querySql($pSql);
            $register_point = $res[0]['register_point'];
            $register_recharge = $res[0]['register_recharge'];

            $tmp = array(
                'phone' => $phone,
                'username' => $phone,
                'add_time' => time(),
                'login_type' => 'member',
                'store_id' => $storeid,
                'store_cate_id' => $store_info['store_cate_id'],
                'point' => $register_point,
                'amount' => $register_recharge,
                'resource' => 1
            );
            $result = $this->userMod->doInsert($tmp);
            $amountData = array(
                'order_sn' => '',
                'type' => 3,
                'status' => 4,
                'c_money' => $register_recharge,
                'old_money' => 0,
                'new_money' => $register_recharge,
                'source' => 3,
                'add_user' => $result,
                'add_time' => time(),
                'mark' => 1,
            );
            $amountLog = &m('amountLog');
            $amountResult = $amountLog->doInsert($amountData);

            if ($result) {
                //代客下单生成2维码
                $order_code = $this->orderZcode($phone);
                $orderurldata = array(
                    "table" => "user",
                    'cond' => 'id = ' . $result,
                    'set' => "order_url='" . $order_code . "'",
                );
                $ress = $this->userMod->doUpdate($orderurldata);
                if ($ress) {
                    //注册日志
                    $logData = array(
                        'operator' => '--',
                        'username' => $phone,
                        'add_time' => time(),
                        'note' => '注册获得' . $register_point . '睿积分',
                        'userid' => $result,
                        'deposit' => $register_point,
                        'expend' => '-',
                    );
                    $pointLogMod = &m("pointLog");
                    $pointLogMod->doInsert($logData);
                    //赚取睿积分
                    $pSql = "SELECT * FROM " . DB_PREFIX . 'user_point_site';
                    $pointData = $this->pointMod->querySql($pSql);
                    $sql = "SELECT id,phone_email,phone,email,point,username FROM " . DB_PREFIX . 'user';
                    $data = $this->userMod->querySql($sql);
                    $uSql = "SELECT id,phone_email,phone,email FROM " . DB_PREFIX . 'user WHERE id=' . $result;
                    $uData = $this->userMod->querySql($uSql);
                    $phone_email = $uData[0]['phone_email'];
                    $userData = $this->child($data, $phone_email, 1);
                    foreach ($userData as $key => $val) {
                        if ($val['level'] == 1) {
                            $res1 = $this->userMod->doEdit($val['id'], array('point' => $val['point'] + $pointData[0]['first_point']));
                            if ($res1) {
                                //推荐日志
                                $logData['operator'] = '--';
                                $logData['username'] = $val['username'];
                                $logData['note'] = '推荐会员获得' . $pointData[0]['first_point'] . '睿积分';
                                $logData['deposit'] = $pointData[0]['first_point'];
                                $logData['userid'] = $val['id'];
                                $pointLogMod->doInsert($logData);
                            }
                        }
                        if ($val['level'] == 2) {
                            $res2 = $this->userMod->doEdit($val['id'], array('point' => $val['point'] + $pointData[0]['second_point']));
                            if ($res2) {
                                //推荐日志
                                $logData['operator'] = '--';
                                $logData['username'] = $val['username'];
                                $logData['note'] = '推荐会员获得' . $pointData[0]['second_point'] . '睿积分';
                                $logData['deposit'] = $pointData[0]['second_point'];
                                $logData['userid'] = $val['id'];
                                $pointLogMod->doInsert($logData);
                            }
                        }
                        if ($val['level'] == 3) {
                            $res3 = $this->userMod->doEdit($val['id'], array('point' => $val['point'] + $pointData[0]['third_point']));
                            if ($res3) {
                                //推荐日志
                                $logData['operator'] = '--';
                                $logData['username'] = $val['username'];
                                $logData['note'] = '推荐会员获' . $pointData[0]['third_point'] . '得睿积分';
                                $logData['deposit'] = $pointData[0]['third_point'];
                                $logData['userid'] = $val['id'];
                                $pointLogMod->doInsert($logData);
                            }
                        }
                    }
                    $_SESSION['userId'] = $result;
                    $_SESSION['userName'] = $phone;
                    $info['url'] = 'index.php?app=default&act=index&storeid=' . $this->storeid . '&lang=' . $this->langid;
                }
                $this->setData($info, $status = '1', '注册成功'); //注册成功
            } else {
                $this->setData(array(), $status = '0', '注册失败'); //注册失败
            }
        } else {
            //登录
            $smsCode = $this->getSmsCode($phone);
            if ($phoneCode != $smsCode) {
                $this->setData(array(), $status = '0', '验证码不正确！');
            }
            if (empty($phoneCode)) {
                $this->setData(array(), $status = '0', '验证码必填！');
            }
            if ($phoneCode == $smsCode) {
                //放进session里
                $_SESSION['userName'] = $userData[0]['username'];
                $_SESSION['userId'] = $userData[0]['id'];
                if ($returnUrl) {
                    $info['url'] = $returnUrl;
                } else {
                    $info['url'] = 'index.php?app=default&act=index&storeid=' . $this->storeid . '&lang=' . $this->langid;
                }
                $this->setData($info, $status = '1', '登录成功');
            } else {
                $this->setData(array(), $status = '0', '用户名或密码错误！');
            }
        }
    }

    public function orderZcode($phone) {
        include ROOT_PATH . "/includes/classes/class.qrcode.php"; // 生成二维码库
        $mainPath = ROOT_PATH . '/upload/orderCode';
        $this->mkDir($mainPath);
        $timePath = date('Ymd');
        $savePath = $mainPath . '/' . $timePath;
        $this->mkDir($savePath);
        $newFileName = uniqid() . ".png";
        $filename = $savePath . '/' . $newFileName;
        $pathName = 'upload/orderCode/' . $timePath . '/' . $newFileName;
        $http_host = $_SERVER['HTTP_HOST'];
        $valueUrl = "{$phone}";
        QRcode::png($valueUrl, $filename);
        return $pathName;
    }
    public function mkDir($dir) {
        if (!is_dir($dir)) {
            @mkdir($dir);
            @chmod($dir, 0777);
            @exec('chmod -R 777 {$dir}');
        }
    }
    /**
     * 登录
     * @author wangh
     * @date 2217/07/19
     */
    public function doLogin() {
        $this->load($this->shorthand, 'user_login/user_login');
        $a = $this->langData;
        $username = !empty($_REQUEST['email']) ? htmlspecialchars($_REQUEST['email']) : '';
        $password = !empty($_REQUEST['password']) ? htmlspecialchars($_REQUEST['password']) : '';
        $returnUrl = !empty($_REQUEST['returnUrl']) ? $_REQUEST['returnUrl'] : '';
        $returnUrl1 = !empty($_REQUEST['returnUrl1']) ? $_REQUEST['returnUrl1'] : '';
        if (empty($username)) {
            $this->setData(array(), $status = '0', $a['login_Accountnumber']);
        }
        if (empty($password)) {
            $this->setData(array(), $status = '0', $a['login_Passwords']);
        }
        $userinfo = $this->getInfoByUname($username);
        if (empty($userinfo)) {
            $this->setData(array(), $status = '0', $a['login_user']);
        }
        $md5Pass = md5($password); //md5加密
        if ($userinfo['password'] === $md5Pass) {
//            $session_id = session_id(); // 用户登录时生成一个session_id
//            // 查询用户是否已经登录
//            $accountSessionInfo = $this->frontAccountSessionMod->getOne(array('cond'=>"`user_id` = {$userinfo['id']}",'fields' => 'session_id,id,login_time'));
//            if(empty($accountSessionInfo['session_id'])){
//                $sessInsData =array(
//                    'user_id' => $userinfo['id'],
//                    'session_id' =>session_id(),
//                    'login_ip' =>$_SERVER['REMOTE_ADDR'],
//                    'login_time' => time(),
//                );
//                $this->frontAccountSessionMod->doInsert($sessInsData);
//            }else{
//                if(time() - $accountSessionInfo['login_time'] < 600) {
//                    if (session_id() != $accountSessionInfo['session_id']) {
////                        $sessEditData = array(
////                            'session_id' => session_id(),
////                            'login_ip' => $_SERVER['REMOTE_ADDR'],
////                            'login_time' => time()
////                        );
////                        $id = $this->frontAccountSessionMod->doEdit($accountSessionInfo['id'], $sessEditData);
////                        if ($id) {
//                            $info['userName'] = $userinfo['username'];
//                            $info['userId'] = $userinfo['id'];
//                            if ($returnUrl) {
//                                $info['url'] = base64_encode($returnUrl);
//                            } else {
//                                $info['url'] = base64_encode('index.php?app=default&act=index');
//                            }
//                            $this->setData($info, $status = 2, $message = "账号已被占用！");
////                        }
//                    }
//                }else{
//                  $sessEditData = array(
//                            'session_id' => session_id(),
//                            'login_ip' => $_SERVER['REMOTE_ADDR'],
//                            'login_time' => time()
//                        );
//                   $this->frontAccountSessionMod->doEdit($accountSessionInfo['id'], $sessEditData);
//                }
//            }
            //放进session里
            $_SESSION['userName'] = $userinfo['username'];
            $_SESSION['userId'] = $userinfo['id'];
            if ($returnUrl) {
                $info['url'] = $returnUrl;
            } else {
                $info['url'] = 'index.php?app=default&act=index';
            }
            if (!empty($returnurl1)) {
                $info['url'] = $returnUrl1;
            }
            $this->setData($info, $status = '1', $a['login_Success']);
        } else {
            $this->setData(array(), $status = '0', $a['login_error']);
        }
        //根据传来的登录帐号判断是那种类型登录方式，以便查询是否存在该用户
//        if(preg_match("/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/", $username)){//登录类型为邮箱
//            $info = $userModel->getInfo('email',$username);
//        }elseif(preg_match("/^(13[0-9]|14[0-9]|15[0-9]|17[0-9]|18[0-9])\d{8}$/i", $username)){//登录类型为邮箱
//            $info = $userModel->getInfo('phone',$username);
//        }
    }

    /**
     *  继续登录
     */
    public function goOnLogin() {
        $userName = !empty($_REQUEST['username']) ? htmlspecialchars($_REQUEST['userName']) : '';
        $userId = !empty($_REQUEST['userId']) ? intval($_REQUEST['userId']) : '';
        $url = !empty($_REQUEST['url']) ? htmlspecialchars($_REQUEST['url']) : '';
        $accountSessionInfo = $this->frontAccountSessionMod->getOne(array('cond' => "`user_id` = {$userId}", 'fields' => 'session_id,id,login_time'));
        $sessEditData = array(
            'session_id' => session_id(),
            'login_ip' => $_SERVER['REMOTE_ADDR'],
            'login_time' => time()
        );
        $this->frontAccountSessionMod->doEdit($accountSessionInfo['id'], $sessEditData);
        $_SESSION['userName'] = $userName;
        $_SESSION['userId'] = $userId;
        $info['url'] = base64_decode($url);
        $this->setData($info, $status = '1', '');
    }

    /**
     * 获取用户信息
     * @author wangh
     * @date 2217/07/19
     */
    public function getInfoByUname($uname) {
        $sql = 'select id,username,password   from  ' . DB_PREFIX . 'user  where  mark =1 and is_use=1 and (phone="' . $uname . '" or email="' . $uname . '")';
        $data = $this->userMod->querySql($sql);
        return $data[0];
    }

    /**
     * 注册
     * @author wangh
     * @date 2217/07/19
     */
    public function register() {
        //语言包
        $this->load($this->shorthand, 'user_login/user_login');
        $this->assign('langdata', $this->langData);
        $lang_id = $this->shorthand;
        $this->assign('lang_id', $lang_id);
        $location = $this->location; //定位
//echo '<pre>';print_r($location);die;
        if ($location != '中国') {
            $this->display('public/register.html');
        } else {
            $this->display('public/register_china1.html');
        }
    }

    /**
     * 中国区域注册验证
     */
    public function doRegisterChina() {
        $this->load($_REQUEST['lang_id'], 'user_login/user_login');
        $a = $this->langData;
        $phone = trim($_REQUEST['phone']);
        $passwd = trim($_REQUEST['passwd']);
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;
        $lang = trim($_REQUEST['lang']);
        $code = $_REQUEST['code'];
        $recom = !empty($_REQUEST['recom']) ? trim($_REQUEST['recom']) : 0;


        if (empty($phone)) {
            $this->setData(array(), $status = '0', $a['login_Mobilephone']);
        }
        if (!preg_match('/^(13[0-9]|14[0-9]|15[0-9]|16[0-9]|17[0-9]|18[0-9]|19[0-9])\d{8}$/', $phone)) {
            $this->setData(array(), $status = '0', $a['login_number']);
        }
        if (!empty($recom)) {
            $sql = "SELECT id FROM " . DB_PREFIX . "user WHERE (phone='" . $recom . "' OR email='" . $recom . "') AND is_kefu = 0 AND mark=1";
            $info = $this->userMod->querySql($sql);

            if (empty($info)) {
                $this->setData(array(), $status = '0', $a['login_recom']);
            }
        }



        if ($this->getPhoneInfo($phone)) {
            $this->setData(array(), $status = '0', $a['login_register']);
        }
        if (empty($passwd)) {
            $this->setData(array(), $status = '0', $a['login_Passwords']);
        }
        if (strlen($passwd) < 6 || strlen($passwd) > 16) {
            $this->setData(array(), $status = '0', $a['login_length']);
        }
        /*   $smsCode = $this->getSmsCode($phone);
          if ($code != $smsCode) {
          $this->setData(array(), $status = '0', $a['login_Verification']);
          } */

        $storeMod = &m('store');
        $store_info = $storeMod->getOne(array("cond" => "id=" . $storeid));
        $password = md5($passwd);
        $pSql = "SELECT *  FROM " . DB_PREFIX . 'user_point_site';
        $res = $this->userMod->querySql($pSql);
        $register_point = $res[0]['register_point'];
        $tmp = array(
            'phone' => $phone,
            'username' => $phone,
            'password' => $password,
            'add_time' => time(),
            'login_type' => 'member',
            'store_id' => $storeid,
            'store_cate_id' => $store_info['store_cate_id'],
            'phone_email' => $recom,
            'point' => $register_point
        );
        $result = $this->userMod->doInsert($tmp);

        if ($result) {
            //注册日志
            $logData = array(
                'operator' => '--',
                'username' => $phone,
                'add_time' => time(),
                'note' => '注册获得' . $register_point . '睿积分',
                'userid' => $result,
                'deposit' => $register_point,
                'expend' => '-',
            );
            $pointLogMod = &m("pointLog");
            $pointLogMod->doInsert($logData);
            //赚取睿积分
            $pSql = "SELECT * FROM " . DB_PREFIX . 'user_point_site';
            $pointData = $this->pointMod->querySql($pSql);
            $sql = "SELECT id,phone_email,phone,email,point,username FROM " . DB_PREFIX . 'user';
            $data = $this->userMod->querySql($sql);
            $uSql = "SELECT id,phone_email,phone,email FROM " . DB_PREFIX . 'user WHERE id=' . $result;
            $uData = $this->userMod->querySql($uSql);
            $phone_email = $uData[0]['phone_email'];
            $userData = $this->child($data, $phone_email, 1);
            foreach ($userData as $key => $val) {
                if ($val['level'] == 1) {
                    $res1 = $this->userMod->doEdit($val['id'], array('point' => $val['point'] + $pointData[0]['first_point']));
                    if ($res1) {
                        //推荐日志
                        $logData['operator'] = '--';
                        $logData['username'] = $val['username'];
                        $logData['note'] = '推荐会员获得' . $pointData[0]['first_point'] . '睿积分';
                        $logData['deposit'] = $pointData[0]['first_point'];
                        $logData['userid'] = $val['id'];
                        $pointLogMod->doInsert($logData);
                    }
                }
                if ($val['level'] == 2) {
                    $res2 = $this->userMod->doEdit($val['id'], array('point' => $val['point'] + $pointData[0]['second_point']));
                    if ($res2) {
                        //推荐日志
                        $logData['operator'] = '--';
                        $logData['username'] = $val['username'];
                        $logData['note'] = '推荐会员获得' . $pointData[0]['second_point'] . '睿积分';
                        $logData['deposit'] = $pointData[0]['second_point'];
                        $logData['userid'] = $val['id'];
                        $pointLogMod->doInsert($logData);
                    }
                }
                if ($val['level'] == 3) {
                    $res3 = $this->userMod->doEdit($val['id'], array('point' => $val['point'] + $pointData[0]['third_point']));
                    if ($res3) {
                        //推荐日志
                        $logData['operator'] = '--';
                        $logData['username'] = $val['username'];
                        $logData['note'] = '推荐会员获' . $pointData[0]['third_point'] . '得睿积分';
                        $logData['deposit'] = $pointData[0]['third_point'];
                        $logData['userid'] = $val['id'];
                        $pointLogMod->doInsert($logData);
                    }
                }
            }
            $_SESSION['userId'] = $result;
            $_SESSION['userName'] = $phone;
            $info['url'] = 'index.php?app=default&act=index&storeid=' . $storeid . '&lang=' . $lang;
            $this->setData($info, $status = '1', $a['Success_login']); //注册成功
        } else {
            $this->setData(array(), $status = '0', $a['fail_login']); //注册失败
        }
    }

    public function getPhoneInfo($phone) {
        $sql = 'select id from  bs_user where  mark =1  and  phone = ' . $phone . '  limit 1';
        $data = $this->userMod->querySql($sql);
        return $data[0]['id'];
    }

    public function getSmsCode($phone) {
        $smsMod = &m('sms');
        $sql = 'select  phone,code  from bs_sms where  phone =' . $phone . '  order by id desc  limit 1';
        $data = $smsMod->querySql($sql);
        return $data[0]['code'];
    }

    //
    public function child($data, $phone_email, $level) {
        static $tree = array();
        foreach ($data as $k => $v) {
            if ($v['phone'] != '') {
                if ($phone_email == $v['phone']) {
                    $v['level'] = $level;
                    $tree[] = $v;
                    $this->child($data, $v['phone_email'], $level + 1);
                }
            } else {
                if ($v['email'] == $phone_email) {
                    $v['level'] = $level;
                    $tree[] = $v;
                    $this->child($data, $v['phone_email'], $level + 1);
                }
            }
        }
        return $tree;
    }

    /**
     * 注册
     * @author wangh
     * @date 2217/07/19
     */
    public function doRegister() {
        $this->load($_REQUEST['lang_id'], 'user_login/user_login');
        $this->assign('langdata', $this->langData);
        $a = $this->langData;
        $lang_id = $this->shorthand;
        $this->assign('lang_id', $lang_id);
        if (IS_POST) {
            $username = !empty($_REQUEST['username']) ? htmlspecialchars($_REQUEST['username']) : '';
            $password = !empty($_REQUEST['password']) ? htmlspecialchars($_REQUEST['password']) : '';
            $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars($_REQUEST['store_id']) : '';
            $lang = !empty($_REQUEST['lang']) ? htmlspecialchars($_REQUEST['lang']) : '';
            if (!$username) {
                $this->setData(array(), $status = '0', $a['name_empty']); //用户名不能为空
            }
            if ($this->userMod->isExist($type = 'email', $username)) {//判断会员名是否重复
                $this->setData(array(), $status = '0', $a['name_existence']); //用户已存在
            }
            if (!$password) {
                $this->setData(array(), $status = '0', $a['login_Passwords']); //密码不能空
            }
            if (strlen($password) <= 5 && strlen($password) > 16) {//注册密码长度验证
                $this->setData(array(), $status = '0', $a['login_length']); //会员登录密码不能少于6位或大于16位
            }
            if (!preg_match("/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/", $username)) {
                $this->setData(array(), $status = '0', $a['name_mailbox']); //邮箱格式
            }
            $storeMod = &m('store');
            $store_info = $storeMod->getOne(array("cond" => "id=" . $store_id));
            $password = md5($password);
            $tmp = array(
                'username' => $username,
                'email' => $username,
                'password' => $password,
                'login_type' => 'member',
                'add_time' => time(),
                'store_id' => $store_id,
                'store_cate_id' => $store_info['store_cate_id']
            );
            $result = $this->userMod->doInsert($tmp);
            if ($result) {
                $_SESSION['userId'] = $result;
                $_SESSION['userName'] = $username;
                $info['url'] = 'index.php?app=default&act=index&storeid=' . $store_id . '&lang=' . $lang;
                $this->setData($info, $status = '1', $a['Success_login']); //注册成功
            } else {
                $this->setData(array(), $status = '0', $a['fail_login']); //注册失败
            }
        } else {
            $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;   //所选的站点id
            $this->assign("store_id", $storeid);
            $this->assign('username', base64_decode($_REQUEST['username']));
            $this->display("public/register.html");
        }
    }

    /**
     * 上传头像,上传图片
     * @author zl
     * @date 2016-08-05
     */
    public function upload64() {
        $user_id = $this->id;
        $headimg_url64 = $_REQUEST['headimg_url'] ? $_REQUEST['headimg_url'] : '';
        //文件后缀名
        $suffix = '';
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $headimg_url64, $result)) {
            //$headimg_url = base64_decode($result[1]);
            $headimg_url = base64_decode(str_replace($result[1], '', $headimg_url64));
            $suffix = $result[2];
        }
        if ($headimg_url) {
            //拼接图片名称
            $newName = uniqid() . $suffix;
            $headimg_url_tmp = UPLOAD_IMG_SAVE_PATH . $newName;
            $this->setFolder(UPLOAD_IMG_SAVE_PATH);
            $showUrl = str_replace(ATTACHEMENT_PATH, '', $headimg_url_tmp);
            file_put_contents($headimg_url_tmp, $headimg_url);
            @chmod($headimg_url_tmp, 0777);
            $info['headimg_url'] = $showUrl;
            //查询之前是否已有头像
            $userModel = &m('user');
            $userInfo = $userModel->getUserImgById($user_id);
            $tmp = array(
                'head_img' => $showUrl,
                'modify_time' => time(),
            );

            if (!$userInfo['head_img']) {
                $result = $userModel->edit($tmp, $user_id);
            } else {
                unlink($userInfo['head_img']);
                $result = $userModel->edit($tmp, $user_id);
            }

            $this->setData($info = array(), $status = 'success', $msg = 'upload success');
        } else {
            $this->setData($info = array(), $status = 'error', $msg = 'upload file first!');
        }
    }

    /**
     * 忘记密码(手机)
     * @author wanyan
     * @date 2016-08-03
     */
    public function forgot_password_p() {
        $this->load($this->shorthand, 'user_login/user_login');
        $this->assign('langdata', $this->langData);
        $email = !empty($_REQUEST['username']) ? htmlspecialchars($_REQUEST['username']) : '';
        $this->assign('email', $email);
        $lang_id = $this->shorthand;
        $this->assign('lang_id', $lang_id);
        $this->display('public/forgot_password.html');
    }

//    /**
//     * 忘记密码(邮箱)
//     * @author wanyan
//     * @date 2016-08-03
//     */
//    public function forgot_password_e() {
//        $this->load($this->shorthand, 'user_login/user_login');
//        $this->assign('langdata', $this->langData);
//        $email = !empty($_REQUEST['username']) ? htmlspecialchars($_REQUEST['username']) : '';
//        $this->assign('email', $email);
//        $this->display('public/forgot_password_e.html');
//    }
//
//    /**
//     * 忘记密码
//     * @author lee
//     * @date 2016-08-03
//     */
//    public function forgot_password_step() {
//        $this->load($this->shorthand, 'user_login/user_login');
//        $this->assign('langdata', $this->langData);
//        $this->display('public/forgot_password_step.html');
//    }

    /**
     * 忘记密码功能
     * @author wanyan
     * @date 2017-08-03
     */
    public function changePasswordE() {
        $this->load($this->shorthand, 'user_login/user_login');
        $a = $this->langData;
        $phone = trim($_REQUEST['phone']);
        $passwd = trim($_REQUEST['passwd']);
        $code = $_REQUEST['code'];
        $user_info = $this->getPhoneInfo($phone);
        if (empty($user_info)) {
            $this->setData(array(), $status = '0', $a['unregistered']);
        }
        if (empty($phone)) {
            $this->setData(array(), $status = '0', $a['login_Mobilephone']);
        }
        if (!preg_match('/^(13[0-9]|14[0-9]|15[0-9]|17[0-9]|18[0-9])\d{8}$/', $phone)) {
            $this->setData(array(), $status = '0', $a['login_number']);
        }
        if (empty($passwd)) {
            $this->setData(array(), $status = '0', $a['login_Passwords']);
        }
        if (strlen($passwd) < 6 || strlen($passwd) > 16) {
            $this->setData(array(), $status = '0', $a['login_length']);
        }
        $smsCode = $this->getSmsCode($phone);
        if ($code != $smsCode) {
            $this->setData(array(), $status = '0', $a['login_Verification']);
        }
        $password = md5($passwd);
        $que_cond = array("password" => $password);
        $aff_id = $this->userMod->doEdit($user_info, $que_cond);
        if ($aff_id) {
            $info['url'] = "index.php?app=user&act=login";
            $this->setData($info, $status = '1', $a['Resetting_success']);
        } else {
            $this->setData($info = array(), $status = '0', $a['Resetting_fail']);
        }
    }

    /**
     * 上传头像
     * @author zl
     * @date 2016-08-05
     */
    public function upload() {
        $info = array();
        $user_id = $this->id;
        $myfile = $user_id . time() . $_FILES['file']['name']; //获取当前图片名
        $size = $_FILES['file']['size']; //获取当前图片大小
        $type = explode('.', $_FILES['file']['name']);
        $num = count($type) - 1;
        $allow_type = $type[$num];
        $tmp = array('jpg', 'jpeg', 'gif', 'bmp', 'png');

        if (!$user_id) {
            $this->setData((object) $info, $status = 'error', $msg = 'Lack of user ID');
        }
        if (!in_array($allow_type, $tmp)) {
            $this->setData((object) $info, $status = 'error', $msg = 'The file type error');
        }
//		if($size >= 1000000){
//			$this->setData($info, $status='error', $msg='Image size is beyond');
//		}
        //上传到服务器
        move_uploaded_file($_FILES["file"]['tmp_name'], dirname(dirname(dirname(__FILE__))) . '/upload/headimage/' . $myfile);
        //查询之前是否已有头像
        $userModel = &m('user');
        $userInfo = $userModel->getUserImgById($user_id);
        $tmp = array(
//				'head_img' => dirname(dirname(dirname(__FILE__))).'/upload/headimage/'.$myfile,
            'head_img' => "http://" . $_SERVER['HTTP_HOST'] . '/liveshow/upload/headimage/' . $myfile,
            'modify_time' => time(),
        );

        if (!$userInfo['head_img']) {
            $result = $userModel->edit($tmp, $user_id);
        } else {
            //将服务器路径转换为图片存储路径才可以找到并删除
            $imgurl = explode('liveshow', $userInfo['head_img']);
            $userInfo['head_img'] = dirname(dirname(dirname(__FILE__))) . $imgurl[1];

            unlink($userInfo['head_img']);
            $result = $userModel->edit($tmp, $user_id);
        }

        if ($result) {
            $info = array('id' => $user_id, 'username' => $userInfo['username'], 'password' => $userInfo['password'],
                'head_img' => $tmp['head_img'], 'add_time' => $userInfo['add_time'], 'modify_time' => time());
            $this->adminInfo['head_img'] = $tmp['head_img'];
            $this->setData($info, $status = 'sucess', $msg = 'Photos uploaded successfully');
        } else {
            $this->setData((object) $info, $status = 'error', $msg = 'Photos uploaded failed');
        }
    }

    /**
     * 创建目录
     * @author lvji
     * @date 2015-03-20
     */
    public function setFolder($Directroy) {
        /* 如果目标目录不存在，则创建它 */
        if (!file_exists($Directroy)) {
            @mkdir($Directroy, 0777, true);
            @chmod($Directroy, 0777);
            @exec("chmod 777 {$Directroy}");
        }
    }

    /*
     * 推荐分销用户
     * @author lee
     * @date 2017-11-25 15:16:21
     */

    public function doFxuser() {
        $fx_code = ($_REQUEST['fx_code']) ? $_REQUEST['fx_code'] : '';
        if (empty($_SESSION['userId'])) {
            $this->load($this->shorthand, 'user_login/user_login');
            $this->assign('langdata', $this->langData);
            $this->assign("tj_code", $fx_code);
            $this->display("fx/register.html");
        } else {
            $this->load($this->shorthand, 'userCenter/userCenter');
            $this->assign('langdata', $this->langData);
            $this->display("userCenter/myfx-info.html");
        }
    }

    /*
     * 处理分销注册
     * @author lee
     * @date 2017-11-25 15:47:06
     */

    public function doFxregister() {
        $this->load($this->shorthand, 'userCenter/userCenter');
        $a = $this->langData;
        $password = !empty($_REQUEST['password']) ? htmlspecialchars(trim($_REQUEST['password'])) : '';
        $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars($_REQUEST['store_id']) : '';
        $real_name = ($_REQUEST['real_name']) ? trim($_REQUEST['real_name']) : '';
        $telephone = ($_REQUEST['telephone']) ? trim($_REQUEST['telephone']) : '';
        $tj_code = ($_REQUEST['tj_code']) ? trim($_REQUEST['tj_code']) : '';
        $bank_name = ($_REQUEST['bank_name']) ? trim($_REQUEST['bank_name']) : '';
        $bank_account = ($_REQUEST['bank_account']) ? trim($_REQUEST['bank_account']) : '';
        $username = $telephone;

        if ($this->userMod->isExist($type = 'phone', $telephone)) {//判断会员名是否重复
            $this->setData(array(), $status = '0', $a['fenx_name']); //用户名已存在
        }
        if (!$password) {
            $this->setData(array(), $status = '0', $a['fenx_Password']); //密码不能空
        }
        if (strlen($password) <= 5 && strlen($password) > 16) {//注册密码长度验证
            $this->setData(array(), $status = '0', $a['fenx_Member']); //会员登录密码不能少于6位或大于16位
        }
        if (empty($real_name)) {
            $this->setData(array(), $status = '0', $a['fx_real_name']);
        }
        if (empty($telephone)) {
            $this->setData(array(), $status = '0', $a['fx_telephone']);
        }
        if (empty($bank_name)) {
            $this->setData(array(), $status = '0', $a['fx_bank_name']);
        }
        if (empty($bank_account)) {
            $this->setData(array(), $status = '0', $a['fx_bank_account']);
        }
        $fx_info = array(
            'real_name' => $real_name,
            'telephone' => $telephone,
            'bank_name' => $bank_name,
            'bank_account' => $bank_account,
            'tj_code' => $tj_code,
        );
        // $storeMod = &m('store');
        //  $store_info = $storeMod->getOne(array("cond" => "id=" . $store_id),true);
        $fx_info = $this->fxUserMod->getOne(array("cond" => "fx_code='" . $fx_info['tj_code'] . "'"));
        ;
        $password = md5($password);
        $tmp = array(
            'username' => $username,
            'email' => $username,
            'password' => $password,
            'login_type' => 'member',
            'add_time' => time(),
            'store_id' => $store_id,
            'store_cate_id' => $fx_info['store_cate']
        );
        $result = $this->userMod->doInsert($tmp);
        if ($result) {
            $_SESSION['userId'] = $result;
            $_SESSION['userName'] = $username;
            $r = $this->addFxuser($username, $fx_info, $result);
            $info['url'] = 'index.php?app=userCenter&act=myCenter';
            if ($r) {
                $this->userMod->doEdit($result, array("is_fx" => 1));
                $this->setData($info, $status = '1', $a['fenx_Success']); //注册成功
            } else {
                $this->setData($info, $status = '1', $a['fenx_distribution']); //注册成功
            }
        } else {
            $this->setData(array(), $status = '0', $a['fenx_fail']); //注册失败
        }
    }

    /*
     * 分销人员生成
     * @author lee
     * @date 2017-11-25 16:55:12
     */

    public function addFxuser($username, $fx_info, $user_id) {

        $this->load($this->shorthand, 'userCenter/userCenter');
        $a = $this->langData;
        $fx_tree = $this->fxTreeMod->getOne(array("cond" => "user_id=" . $fx_info['user_id']));
        if (empty($fx_info)) {
            $this->setData(array(), $status = '0', $a['fx_tj']);
        } elseif ($fx_tree['fx_level'] == 3) {
            $this->setData(array(), $status = '0', $a['fx_no_tree']);
        }
        $fx_code = $this->make_fxcode();
        $fx_img = $this->shareMyfxcode($fx_code);
        $data = array(
            'user_id' => $user_id,
            'real_name' => $fx_info['real_name'],
            'email' => $username,
            'telephone' => $fx_info['telephone'],
            'bank_name' => $fx_info['bank_name'],
            'bank_account' => $fx_info['bank_account'],
            'store_id' => $this->storeid,
            'store_cate' => $fx_info['store_cate'],
            'add_time' => time(),
            'fx_code' => $fx_code,
            'tj_code' => $fx_info['tj_code'],
            'source' => 2,
            'fx_img' => $fx_img,
            'is_check' => 2,
        );
        $res = $this->fxUserMod->doInsert($data);
        if ($res) {
            $level = $fx_tree['fx_level'] + 1;
            $tree_info = array(
                'user_id' => $this->userId,
                'fx_level' => $level,
                'pid' => $fx_tree['id'],
                'ppid' => $fx_tree['pid']
            );
            $r = $this->fxTreeMod->doInsert($tree_info);
            $this->doFxmoney($fx_info['store_cate'], $user_id);
            if ($r) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /*
     * 新增分销用户关系树，余额
     * @author lee
     * @date 2017-11-21 20:29:31W
     */

    public function doFxmoney($cate_id, $user_id) {
        $storeMod = &m('store');
        $sql = 'select id as store_id  from  ' . DB_PREFIX . 'store  where   store_cate_id =' . $cate_id;
        $res = $storeMod->querySql($sql);
        if (!empty($res)) {
            foreach ($res as $key => $val) {
                $arr_store['user_id'] = $user_id;
                $arr_store['store_cate'] = $cate_id;
                $arr_store['money'] = 0.00;
                $arr_store['store_id'] = $val['store_id'];
                $r = $this->fxuserMoneyMod->doInsert($arr_store);
            }
        }
    }

    /**
     * @return int|mixed
     * 分销码生成
     */
    public function make_fxcode() {
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789abcdefghijklmnopqrstuvwxyz';
        //$string=time();
        $string = rand(10000, 99999);
        for ($len = 5; $len >= 1; $len--) {
            $position = rand() % strlen($chars);
            $position2 = rand() % strlen($string);
            $string = substr_replace($string, substr($chars, $position, 1), $position2, 0);
        }
        return $string;
    }

    /*
     * 推荐分享自己的推荐码
     * @author lee
     * @date 2017-11-25 13:38:40
     * @param $fx_code 分销码
     */

    public function shareMyfxcode($fx_code) {
        if (!$fx_code) {
            $this->setData(array(), $status = 'error', $message = 'Lack of order number');
        }
        include_once ROOT_PATH . "/includes/classes/class.qrcode.php";
        $value = "http://" . SYSTEM_WEB . "/" . SYSTEM_FILE_NAME . "?app=user&act=doFxuser&fx_code=" . $fx_code; //二维码内容
        if ($_SERVER["PHP_SELF"]) {
            $_SERVER["PHP_SELF"] = str_replace("/index.php", "", $_SERVER["PHP_SELF"]);
        }
        $qrUrl = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER["PHP_SELF"] . "/upload/qr/" . $fx_code . ".png";
        $out_file = ROOT_PATH . "/upload/qr/" . $fx_code . ".png";
        $errorCorrectionLevel = 'L'; //容错级别
        $matrixPointSize = 6; //生成图片大小
        $path = ROOT_PATH . "/upload/qr/";
        $this->MkFolder($path);
        //生成二维码图片
        $QRcode = QRcode::png($value, $out_file, $errorCorrectionLevel, $matrixPointSize, 2);
        return $qrUrl;
    }

    /**
     * 生成路径
     * @author WQQ 2017-02-16 14:53:25
     * @param $path
     */
    public function MkFolder($path) {
        if (!is_readable($path)) {
            $this->MkFolder(dirname($path));
            if (!is_file($path))
                mkdir($path, 0777);
        }
    }

    //检查推荐码是否正确
    public function checkRecom() {
        $phone = !empty($_REQUEST['phone']) ? trim($_REQUEST['phone']) : '';
        $recom = !empty($_REQUEST['recom']) ? trim($_REQUEST['recom']) : '';
        if ($recom) {
            $sql = 'SELECT id FROM ' . DB_PREFIX . 'user WHERE phone =' . $recom . ' OR email=' . $recom;
            $res = $this->userMod->querySql($sql);
        } else {
            $res = ture;
        }
        if ($phone == $recom || !$res) {
            $this->setData(array(), $status = 1, '请填写正确推荐码');
        }
    }

}
