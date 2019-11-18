<?php

/**
 * 用户中心
 * @author lvji
 *
 */
//include_once 'sms.app.php';
//include_once 'mail.app.php';

class UserApp extends BaseWxApp {

    public $userMod;
    private $fxTreeMod;
    private $fxUserMod;
    private $fxuserMoneyMod;
    private $pointMod;
    private $amountLogMod;
    public function __construct() {
        parent::__construct();
        $this->userMod = &m('user');
   
        $this->fxUserMod = &m('fxuser');
        $this->fxuserMoneyMod = &m('fxuserMoney');
        $this->pointMod = &m('point');
    }

    /**
     * 授权登录m
     */
    public function loginOuth() {

        //重新session
        if ($_COOKIE['wx_openid'] && empty($_SESSION['user_id'])) {
            //查询用户是否存在
            $userMod = &m('user');
            $userInfo = $userMod->getOne(array(
                'fields' => 'id',
                'cond' => "openid = '" . $_COOKIE['wx_openid'] . "' and is_use = 1 AND mark =1"
            ));
            if ($userInfo) {   //快速登录
                $_SESSION['userId'] = $userInfo['id'];
                $_SESSION['userName'] = $userInfo['username'];
                header('Location: wx.php?app=default&act=index');
                exit;
            } else {  //重新给session
                header('Location: wx.php?app=user&act=quickLogin');
                exit;
            }
        }

        $redirectUrl = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '&back_app=' . $_REQUEST['back_app'] . '&back_act=' . $_REQUEST['back_act'];
/*        if(!empty($_REQUEST['storeGoodsId'])){
            $redirectUrl .="&storeGoodsId=".$_REQUEST['storeGoodsId'];
        }
        if(!empty($_REQUEST['storeGoodsId'])){
            $redirectUrl .="&activityId=".$_REQUEST['activityId'];
        }
        if(!empty($_REQUEST['lang_id'])){
            $redirectUrl .="&lang_id=".$_REQUEST['lang_id'];
        }
        if(!empty($_REQUEST['store_id'])){
            $redirectUrl .="&store_id=".$_REQUEST['store_id'];
        }*/

        $url = $this->getOAuthUrl($redirectUrl, 'snsapi_userinfo', 1);



        header("Location:" . $url);
    }

    /**
     * 登录
     * @author wangs
     * @date 2017/11/22
     */
    public function login() {
//语言包
        $this->load($this->shorthand, 'WeChat/user_login');
        $this->assign('langdata', $this->langData);
//        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;   //所选的站点id
//        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
//授权获取 opendid 放在 cookie里
//判断cookie是wx_userinfo的值是否存在
        $wx_openid = isset($_COOKIE['wx_openid']) ? $_COOKIE['wx_openid'] : '';
        if (empty($wx_openid)) {
//获取wx_userinfo 放在cookie 里面
            $code = $_REQUEST['code'];
            $accessTokenInfo = $this->getoAuthAccessToken($code);
            $OuthToken = $accessTokenInfo->access_token;
            $openid = $accessTokenInfo->openid;

//            $r_token=$this->getoAuthAccessTokenByRefreshToken($accessTokenInfo->refresh_token);
            $userInfo = $this->getUserInfo($OuthToken, $openid);

            $wx_openid = $userInfo->openid;
            $wx_nickname = $userInfo->nickname;
            $wx_city = $userInfo->city;
            $wx_province = $userInfo->province;
            $wx_country = $userInfo->country;
            $wx_headimgurl = $userInfo->headimgurl;
            $wx_sex = $userInfo->sex;
//3个月的cookie的生存期
            setcookie('wx_openid', $wx_openid, time() + 3600 * 24 * 30 * 3);
            setcookie('wx_nickname', $wx_nickname, time() + 3600 * 24 * 30 * 3);
            setcookie('wx_city', $wx_city, time() + 3600 * 24 * 30 * 3);
            setcookie('wx_province', $wx_province, time() + 3600 * 24 * 30 * 3);
            setcookie('wx_country', $wx_country, time() + 3600 * 24 * 30 * 3);
            setcookie('wx_headimgurl', $wx_headimgurl, time() + 3600 * 24 * 30 * 3);
            setcookie('wx_sex', $wx_sex, time() + 3600 * 24 * 30 * 3);
        }

        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $this->assign('latlon', $latlon);
        $this->assign('returnUrl', $_REQUEST['returnUrl']);
        $this->assign('returnUrl1', urlencode($_REQUEST['returnUrl']));
        $languages = $this->shorthand;
        $this->assign('languages', $languages);
        $this->assign('storeid', $this->storeid);
        $this->assign('lang', $this->langid);

        $this->display('public/login.html');
    }

     /**
     * 登录
     * @author wangs
     * @date 2018/8/16
     */
    public function quickLogin() {
        $this->load($this->shorthand, 'WeChat/user_login');
        $this->assign('langdata', $this->langData);
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;  //多语言商品
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $userId = !empty($_REQUEST['userId']) ? $_REQUEST['userId'] : '';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $bindId=!empty($_REQUEST['bindId']) ? $_REQUEST['bindId']:'';
        if(!empty($userId)){
            $_SESSION['user_Id']=$userId;
        }
        if (empty($_COOKIE['wx_openid'])) { //隐性授权
            $redirectUrl = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '&back_app=user&back_act=quickLogin';
            $url = $this->getOAuthUrl($redirectUrl, 'snsapi_userinfo', 1);
            header("Location:" . $url);
        }
        $user_id=!empty($userId)? $userId : $_SESSION['user_Id'];
        $this->assign('latlon', $latlon);
        $this->assign('returnUrl', $_REQUEST['returnUrl']);
        $this->assign('userId', $user_id);
        $this->assign('returnUrl1', urlencode($_REQUEST['returnUrl']));
        $languages = $this->shorthand;
        $this->assign('languages', $languages);
        $this->assign('storeid', $storeid);
        $this->assign('lang', $lang);
        $this->assign('bindId',$bindId);
        $this->display('public/quick_login.html');
    }

    /**
     * 验证是注册还是登录
     * @author wangs
     * @date 2018/8/16
     */
    public function doquickLogin()
    {
        writeLog($_REQUEST, 'doquickLogin');
        //语言包
        $this->load($_REQUEST['languages'], 'WeChat/user_login');
        $a = $this->langData;
        $phone = !empty($_REQUEST['phone']) ? htmlspecialchars(trim($_REQUEST['phone'])) : '';
        $returnUrl = !empty($_REQUEST['returnUrl']) ? $_REQUEST['returnUrl'] : '';
        $userId = !empty($_REQUEST['userId']) ? $_REQUEST['userId'] : '';
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;  //多语言商品
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        if (empty($phone)) {
            writeLog($a['phone_Required'], 'doquickLogin');
            $this->setData(array(), $status = '0', $a['phone_Required']);
        }
        //判断 openid 的唯一性
        $wx_openid = $_COOKIE['wx_openid'];
        if ($this->getOpenid($wx_openid, $phone)) {
            writeLog("该微信号已绑定其他手机号!!", 'doquickLogin');
            $this->setData(array(), $status = '0', "该微信号已绑定其他手机号");
        }
        $phoneCode = !empty($_REQUEST['code']) ? $_REQUEST['code'] : 0;
        //查看当前是否有此用户
        $userSql = 'select * from ' . DB_PREFIX . 'user where phone = ' . $phone . ' and mark =1';
        $userData = $this->userMod->querySql($userSql);
        $recomSql = "SELECT id,phone_email,phone,email FROM " . DB_PREFIX . 'user WHERE id=' . $userId . ' and mark=1';
        $recomData = $this->userMod->querySql($recomSql);
        $email_phone = $recomData[0]['phone'];

        //推荐人是否是分销人员
        $fxInfoSql = "SELECT id FROM " . DB_PREFIX . "fx_user where user_id=" . $userId . ' and level=3';
        $fxInfo = $this->userMod->querySql($fxInfoSql);

        if (empty($email_phone)) {
            $email_phone = 0;
        }
        if ($userData[0]['phone'] == '') {
            //注册
            $sql = 'select * from ' . DB_PREFIX . 'user where phone = ' . $phone;
            $uData = $this->userMod->querySql($sql);
            if (!empty($uData[0]['phone'])) {
                $this->userMod->doEdit($uData[0]['id'], array('mark' => 1));
                exit();
            }

            $smsCode = $this->getSmsCode($phone);
            if ($phoneCode != $smsCode) {
                writeLog($a['login_Verification'], 'doquickLogin');
                $this->setData(array(), $status = '0', $a['login_Verification']);
            }
            if (empty($phoneCode)) {
                writeLog($a['mailbox_Required'], 'doquickLogin');
                $this->setData(array(), $status = '0', $a['mailbox_Required']);
            }
            //从cookie 里 获取openid
            $wx_nickname = $_COOKIE['wx_nickname'];
            $wx_city = $_COOKIE['wx_city'];
            $wx_province = $_COOKIE['wx_province'];
            $wx_country = $_COOKIE['wx_country'];
            $wx_headimgurl = $_COOKIE['wx_headimgurl'];
            $wx_sex = $_COOKIE['wx_sex'];
            //判断 openid 的唯一性
            if ($this->getOpenid($wx_openid)) {
                writeLog("该微信号已绑定其他手机号", 'doquickLogin');
                $this->setData(array(), $status = '0', "该微信号已绑定其他手机号");
            }
            $pSql = "SELECT *  FROM " . DB_PREFIX . 'user_point_site';
            $res = $this->pointMod->querySql($pSql);
            $register_point = $res[0]['register_point'];
            $register_recharge = $res[0]['register_recharge'];
            $systemNickName = '艾美睿' . rand(100000, 999999);
            $tmp = array(
                'phone' => $phone,
                'username' => $systemNickName,
                'add_time' => time(),
                'phone_email' => $email_phone,
                'login_type' => 'member',
                'store_id' => $storeid,
                'openid' => $wx_openid,
                'nickname' => $systemNickName,
                'city' => $wx_city,
                'province' => $wx_province,
                'country' => $wx_country,
                'headimgurl' => $wx_headimgurl,
                'sex' => $wx_sex,
                'point' => $register_point,
                'amount' => $register_recharge
            );
            $result = $this->userMod->doInsert($tmp);
//                if ($result){
            $amountData = array(
                'order_sn' => '',
                'type' => 3,
                'status' => 4,
                'c_money' => $register_recharge,
                'old_money' => 0,
                'new_money' => $register_recharge,
                'source' => 1,
                'add_user' => $result,
                'add_time' => time(),
                'mark' => 1,
                'class' => 3
            );
            $amountLog = &m('amountLog');
            $amountLog->doInsert($amountData);
//                }
            //将注册会员绑定为三级分销人员的会员
            if (!empty($fxInfo)) {
                /*                    $fxUserAccountMod=&m('fxUserAccount');
                                    $fxData=array(
                                        'fx_user_id'=>$fxInfo[0]['id'],
                                        'user_id'=>$result
                                    );
                                    $fxUserAccountMod->doInsert($fxData);*/
                $fxUserAccountMod =& m('fxUserAccount');
                $fxUserAccountMod->addFxUser($fxInfo[0]['id'], $result);
            }
            $sql = "select * from " . DB_PREFIX . 'user where phone= ' . $phone;
            $uresult = $this->userMod->querySql($sql);
            /* desc  :记录参数集合
               auther:luffy
               date  :2018-09-11
            */
            $systemErrorLogMod =  &m('systemErrorLog');
            $systemErrorLogMod->doInsert(array(
                'user_id' => 111,
                'request_params' => $wx_openid,
                'deal_params' => serialize($tmp),
                'important_params' => $phone,
                'add_time' => time()
            ));
//                注册成功发送通知
            $userMod = &m('user');
            $userMod->sendSms($phone);
            /*
             * 注册送券的逻辑
             * @author tangp
             * @date 2019-02-14
             */
            $systemConsoleMod = &m('systemConsole');
            $userCoupon =& m('userCoupon');
            $getCouponActivityStatus = $systemConsoleMod->getCouponActivityStatus();//获取设置注册送电子券是否开启
            if ($getCouponActivityStatus['1'] == 1) {
                $coupon = $systemConsoleMod->getSetCoupon();
                $duiCoupon = $systemConsoleMod->getSetDuiCoupon();
                $limitTiems = $coupon[0]['limit_times'] * 3600 * 24;
                $limit = $duiCoupon[0]['limit_times'] * 3600 * 24;
                if (!empty($coupon)) {
                    $data = array(
                        'c_id' => $coupon[0]['id'],
                        'remark' => '注册送抵扣券',
                        'add_time' => time(),
                        'start_time' => time(),
                        'end_time' => time() + $limitTiems,
                        'user_id' => $result
                    );
                    $userCoupon->doInsert($data);
                    //赠券发短信
                    $userMod->sendMessage($result);
                }
                if (!empty($duiCoupon)) {
                    $data = array(
                        'c_id' => $duiCoupon[0]['id'],
                        'remark' => '注册送兑换券',
                        'add_time' => time(),
                        'start_time' => time(),
                        'end_time' => time() + $limit,
                        'user_id' => $result
                    );
                    $userCoupon->doInsert($data);
                    //赠券发短信
                    $userMod->sendMessage($result);
                }
            }

            if (!empty($uresult)) {
                //生成2维码
                $codee = $this->goodsZcode($result, $storeid, $lang, $latlon);
                $urldata = array(
                    "table" => "user",
                    'cond' => 'id = ' . $result,
                    'set' => "user_url='" . $codee . "'",
                );
                $ress = $this->userMod->doUpdate($urldata);
                if ($ress) {
                    //日志
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
                    //赚取积分
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
                                $logData['username'] = $val['phone'];
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
                                $logData['username'] = $val['phone'];
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
                                $logData['username'] = $val['phone'];
                                $logData['note'] = '推荐会员获得' . $pointData[0]['third_point'] . '睿积分';
                                $logData['deposit'] = $pointData[0]['third_point'];
                                $logData['userid'] = $val['id'];
                                $pointLogMod->doInsert($logData);
                            }
                        }
                    }
                    $_SESSION['userId'] = $result;
                    $_SESSION['userName'] = $wx_nickname;
                    $_SESSION['wx_openid'] = $wx_openid;
                    if (!empty($returnUrl)) {
                        $info['url'] = $returnUrl;
                    } else {
                        $info['url'] = 'wx.php?app = default&act = index&storeid = ' . $this->mrstoreid . '&lang = ' . $lang . '&latlon=' . $latlon;
                    }
                }
                writeLog($a['Success_login'], 'doquickLogin');
                $this->setData($info, $status = '1', $a['Success_login']); //注册成功
            } else {
                writeLog($a['fail_login'], 'doquickLogin');
                $this->setData(array(), $status = '0', $a['fail_login']); //注册失败
            }
        } else {
            //登录
            if (!empty($userData[0]['openid'])) {
                if ($userData[0]['openid'] != $_COOKIE['wx_openid']) {
                    writeLog('该手机号已绑定其他微信号！', 'doquickLogin');
                    $this->setData(array(), '0', '该手机号已绑定其他微信号！');
                } else {
                    $smsCode = $this->getSmsCode($phone);
                    if ($phoneCode != $smsCode) {
                        writeLog($a['login_Verification'], 'doquickLogin');
                        $this->setData(array(), $status = '0', $a['login_Verification']);
                    }
                    if (empty($phoneCode)) {
                        writeLog($a['mailbox_Required'], 'doquickLogin');
                        $this->setData(array(), $status = '0', $a['mailbox_Required']);
                    }
                    if ($phoneCode == $smsCode) {
                        $wx_openid = $_COOKIE['wx_openid'];
                        $_SESSION['userName'] = $userData[0]['username'];
                        $_SESSION['userId'] = $userData[0]['id'];
                        $_SESSION['wx_openid'] = $userData[0]['openid'];
                        if ($returnUrl) {
                            $info['url'] = $returnUrl;
                        } else {
                            $info['url'] = 'wx.php?app=default&act=index&storeid=' . $this->mrstoreid . '&lang=' . $lang . '&latlon=' . $latlon;
                        }
                        writeLog($a['login_Success'], 'doquickLogin');
                        $this->setData($info, $status = '1', $a['login_Success']);
                    } else {
                        writeLog($a['login_error'], 'doquickLogin');
                        $this->setData(array(), $status = '0', $a['login_error']);
                    }
                }
            } else {
                $smsCode = $this->getSmsCode($phone);
                if ($phoneCode != $smsCode) {
                    writeLog($a['login_Verification'], 'doquickLogin');
                    $this->setData(array(), $status = '0', $a['login_Verification']);
                }
                if (empty($phoneCode)) {
                    writeLog($a['mailbox_Required'], 'doquickLogin');
                    $this->setData(array(), $status = '0', $a['mailbox_Required']);
                }
                $this->userMod->doEdit($userData[0]['id'], array('openid' => $_COOKIE['wx_openid']));
                if ($phoneCode == $smsCode) {
                    $wx_openid = $_COOKIE['wx_openid'];
                    $_SESSION['userName'] = $userData[0]['username'];
                    $_SESSION['userId'] = $userData[0]['id'];
                    $_SESSION['wx_openid'] = $_COOKIE['wx_openid'];
                    if ($returnUrl) {
                        $info['url'] = $returnUrl;
                    } else {
                        $info['url'] = 'wx.php?app=default&act=index&storeid=' . $this->mrstoreid . '&lang=' . $lang . '&latlon=' . $latlon;
                    }
                    writeLog($a['login_Success'], 'doquickLogin');
                    $this->setData($info, $status = '1', $a['login_Success']);
                }

            }
        }


    }

    /**
     * 登录
     * @author wangs
     * @date 2017/11/22
     */
    public function doLogin() {
//语言包
        $this->load($_REQUEST['languages'], 'WeChat/user_login');
        $a = $this->langData;

        $username = !empty($_REQUEST['email']) ? htmlspecialchars($_REQUEST['email']) : '';
        $password = !empty($_REQUEST['password']) ? htmlspecialchars($_REQUEST['password']) : '';
        $returnUrl = !empty($_REQUEST['returnUrl']) ? $_REQUEST['returnUrl'] : '';
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;   //所选的站点id
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $this->assign('latlon', $latlon);

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
        if ($userinfo['password'] == $md5Pass) {
            //从cookie 里 获取openid
            $wx_openid = $_COOKIE['wx_openid'];
            //放进session里
            $_SESSION['userName'] = $userinfo['username'];
            $_SESSION['userId'] = $userinfo['id'];
            $_SESSION['wx_openid'] = $wx_openid;

            if ($returnUrl) {
                $info['url'] = $returnUrl;
            } else {
                $info['url'] = 'wx.php?app=default&act=index&storeid=' . $this->mrstoreid . '&lang=' . $lang . '&latlon=' . $latlon;
            }
            $this->setData($info, $status = '1', $a['login_Success']);
        } else {
            $this->setData(array(), $status = '0', $a['login_error']);
        }
    }

    /**
     * 获取用户信息
     * @author wangh
     * @date 2217/07/19
     */
    public function getInfoByUname($uname) {
        $sql = 'select id, username, password from ' . DB_PREFIX . 'user where mark = 1 and (phone = "' . $uname . '" or email = "' . $uname . '")';
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
        $this->load($this->shorthand, 'WeChat/user_login');
        $this->assign('langdata', $this->langData);
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;   //所选的站点id
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $returnUrl = !empty($_REQUEST['returnUrl']) ? $_REQUEST['returnUrl'] : '';
        $this->assign('returnUrl', $returnUrl);
        $this->assign('latlon', $latlon);
        $this->assign("store_id", $storeid);
        $this->assign("lang", $lang);
        $languages = $this->shorthand;
        $this->assign('languages', $languages);
        $this->display('public/register.html');
    }

    /**
     * 中国区域手机注册验证
     */
    public function doRegisterChina() {
        //语言包
        $this->load($this->shorthand, 'WeChat/user_login');
        $a = $this->langData;
        $phone = trim($_REQUEST['phone']);
        $passwd = trim($_REQUEST['password']);
        $email_phone = !empty($_REQUEST['phone_email']) ? trim($_REQUEST['phone_email']) : 0;
        $code = $_REQUEST['code'];
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->mrstoreid;   //所选的站点id
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $returnUrl = !empty($_REQUEST['returnUrl']) ? $_REQUEST['returnUrl'] : '';

        $this->assign('latlon', $latlon);
        if (empty($phone)) {
            $this->setData(array(), $status = '0', $a['phone_Required']);
        }
        if (!preg_match('/^(13[0-9]|14[0-9]|15[0-9]|17[0-9]|18[0-9])\d{8}$/', $phone)) {
            $this->setData(array(), $status = '0', $a['login_number']);
        }
        if ($this->getPhoneInfo($phone)) {
            $this->setData(array(), $status = '0', $a['login_register']);
        }
        if (!empty($email_phone)) {
            if (!preg_match("/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/", $email_phone) && !preg_match('/^(13[0-9]|14[0-9]|15[0-9]|17[0-9]|18[0-9])\d{8}$/', $email_phone)) {//
                $this->setData(array(), $status = '0', $a['login_recom']);
            } else {
                $sql = "SELECT id FROM " . DB_PREFIX . "user WHERE (phone='" . $email_phone . "' OR email='" . $email_phone . "') AND is_kefu = 0 AND mark=1";
                $info = $this->userMod->querySql($sql);
                if (empty($info)) {
                    $this->setData(array(), $status = '0', $a['login_email_phone']);
                }
            }
        }
        $smsCode = $this->getSmsCode($phone);
        if ($code != $smsCode) {
            $this->setData(array(), $status = '0', $a['login_Verification']);
        }
        if (empty($code)) {
            $this->setData(array(), $status = '0', $a['mailbox_Required']);
        }

        if (empty($passwd)) {
            $this->setData(array(), $status = '0', $a['Password_Required']);
        }
        if (strlen($passwd) < 6 || strlen($passwd) > 16) {
            $this->setData(array(), $status = '0', $a['login_length']);
        }

        $password = md5($passwd);

        //从cookie 里 获取openid
        $wx_openid = $_COOKIE['wx_openid'];
        $wx_nickname = $_COOKIE['wx_nickname'];
        $wx_city = $_COOKIE['wx_city'];
        $wx_province = $_COOKIE['wx_province'];
        $wx_country = $_COOKIE['wx_country'];
        $wx_headimgurl = $_COOKIE['wx_headimgurl'];
        $wx_sex = $_COOKIE['wx_sex'];

        //注册来源
        if (empty($phone_email)) {
            $resource = 3;
        } else {
            $resource = 4;
        }
        //判断 openid 的唯一性
        if ($this->getOpenid($wx_openid)) {
            $this->setData(array(), $status = '0', $a['register_Already']);
        }
        $pSql = "SELECT *  FROM " . DB_PREFIX . 'user_point_site';
        $res = $this->pointMod->querySql($pSql);
        $register_point = $res[0]['register_point'];
        $tmp = array(
            'phone' => $phone,
            'username' => $wx_nickname,
            'password' => $password,
            'add_time' => time(),
            'phone_email' => $email_phone,
            'login_type' => 'member',
            'store_id' => $store_id,
            'openid' => $wx_openid,
            'nickname' => $wx_nickname,
            'city' => $wx_city,
            'province' => $wx_province,
            'country' => $wx_country,
            'headimgurl' => $wx_headimgurl,
            'sex' => $wx_sex,
            'point' => $register_point,
            'resource' => $resource
        );
        $result = $this->userMod->doInsert($tmp);
        $sql = "select * from " . DB_PREFIX . 'user where phone= ' . $phone;
        $res = $this->userMod->querySql($sql);




        if (!empty($res)) {
            //生成2维码
            $codee = $this->goodsZcode($result, $store_id, $lang, $latlon);
            $urldata = array(
                "table" => "user",
                'cond' => 'id = ' . $result,
                'set' => "user_url='" . $codee . "'",
            );
            $ress = $this->userMod->doUpdate($urldata);
            if ($ress) {
                //日志
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
                //赚取积分
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
                            $logData['username'] = $val['phone'];
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
                            $logData['username'] = $val['phone'];
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
                            $logData['username'] = $val['phone'];
                            $logData['note'] = '推荐会员获得' . $pointData[0]['third_point'] . '睿积分';
                            $logData['deposit'] = $pointData[0]['third_point'];
                            $logData['userid'] = $val['id'];
                            $pointLogMod->doInsert($logData);
                        }
                    }
                }
                $_SESSION['userId'] = $result;
                $_SESSION['userName'] = $wx_nickname;
                $_SESSION['wx_openid'] = $wx_openid;
                if (!empty($returnUrl)) {
                    $info['url'] = $returnUrl;
                } else {
                    $info['url'] = 'wx.php?app=default&act=index&storeid= ' . $this->mrstoreid . '&lang = ' . $lang . '&latlon=' . $latlon;
                }
            }
            $this->setData($info, $status = '1', $a['Success_login']); //注册成功
        } else {
            $this->setData(array(), $status = '0', $a['fail_login']); //注册失败
        }
    }

    public function goodsZcode($user_id, $storeid, $lang, $latlon) {
        include ROOT_PATH . "/includes/classes/class.qrcode.php"; // 生成二维码库
        $mainPath = ROOT_PATH . '/upload/userCode';
        $this->mkDir($mainPath);
        $timePath = date('Ymd');
        $savePath = $mainPath . '/' . $timePath;
        $this->mkDir($savePath);
        $newFileName = uniqid() . ".png";
        $filename = $savePath . '/' . $newFileName;
        $pathName = 'upload/userCode/' . $timePath . '/' . $newFileName;
        $http_host = $_SERVER['HTTP_HOST'];
        $system_web ='www.711home.net';
        $serverName='www.711home.net';
        if($serverName==$system_web){
            $valueUrl = 'http://'.$system_web."/wx.php?app=user&act=quickLogin&userId={$user_id}&storeid={$storeid}&lang={$lang}&&auxiliary=0&latlon={$latlon}";
        }else{
            $valueUrl = 'http://'.$serverName."/bspm711/wx.php?app=user&act=quickLogin&userId={$user_id}&storeid={$storeid}&lang={$lang}&&auxiliary=0&latlon={$latlon}";
        }
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
     * 获取用户openid
     */
    public function getOpenid($openid, $existPhone = '') {
        $sql = "select id from bs_user where mark = 1 and openid = '{$openid}' ";
        if (!empty($existPhone)) {
            $sql .= " and phone != '{$existPhone}'";
        }
        $data = $this->userMod->querySql($sql);
        if (!empty($data)) {
            return $data[0]['id'];
        } else {
            return array();
        }
    }

    //手机
    public function getPhoneInfo($phone) {
        $sql = 'select id from bs_user where mark = 1 and phone = ' . $phone . ' limit 1';
        $data = $this->userMod->querySql($sql);
        return $data[0]['id'];
    }

    //验证码
    public function getSmsCode($phone) {
        $smsMod = &m('sms');
        $sql = 'select phone, code from bs_sms where phone = ' . $phone . ' order by id desc limit 1';
        $data = $smsMod->querySql($sql);
        return $data[0]['code'];
    }

    /**
     * 邮箱注册
     * @author lvji
     * @date 2015-3-10
     */
    public function doRegister() {
        //语言包
        $this->load($_REQUEST['languages'], 'WeChat/user_login');
        $a = $this->langData;
        /*   if (!isset($_COOKIE['wx_openid'])) {
          $this->setData(array(), $status = '0', $a['login_error_1']);
          } */
        $username = trim($_REQUEST['email']);
        $password = !empty($_REQUEST['email_password']) ? trim($_REQUEST['email_password']) : '';
        $email_phone = !empty($_REQUEST['email_phone']) ? trim($_REQUEST['email_phone']) : 0;
        $storeid = !empty($_REQUEST['email_store_id']) ? $_REQUEST['email_store_id'] : $this->mrstoreid;   //所选的站点id
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $this->assign('latlon', $latlon);
        if (!$username) {
            $this->setData(array(), $status = '0', $a['mailbox_Cantbeempty']);
        }
        if (!preg_match("/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/", $username)) {//
            $this->setData(array(), $status = '0', $a['mailbox_Formatting']);
        };
        if (!empty($email_phone)) {
            if (!preg_match("/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/", $email_phone) && !preg_match('/^(13[0-9]|14[0-9]|15[0-9]|17[0-9]|18[0-9])\d{8}$/', $email_phone)) {
                $this->setData(array(), $status = '0', $a['login_recom']);
            } else {

                $sql = "SELECT id FROM " . DB_PREFIX . "user WHERE (phone='" . $email_phone . "' OR email='" . $email_phone . "') AND is_kefu = 0 AND mark=1";
                $info = $this->userMod->querySql($sql);
                if (empty($info)) {
                    $this->setData(array(), $status = '0', $a['login_email_phone']);
                }
            }
        }


        if ($this->userMod->isExist($type = 'email', $username, 'mark', 1)) {
            $this->setData(array(), $status = '0', $a['mailbox_Havebeenregistered']);
        }
        if (!$password) {
            $this->setData(array(), $status = '0', $a['login_Passwords']); //密码不能空
        }
        if (strlen($password) < 6 || strlen($password) > 16) {
            $this->setData(array(), $status = '0', $a['login_length']);
        }

        $password = md5($password);

        //从cookie 里 获取openid
        $wx_openid = $_COOKIE['wx_openid'];
        $wx_nickname = $_COOKIE['wx_nickname'];
        $wx_city = $_COOKIE['wx_city'];
        $wx_province = $_COOKIE['wx_province'];
        $wx_country = $_COOKIE['wx_country'];
        $wx_headimgurl = $_COOKIE['wx_headimgurl'];
        $wx_sex = $_COOKIE['wx_sex'];

        //判断 openid 的唯一性
        if ($this->getOpenid($wx_openid)) {
            $this->setData(array(), $status = '0', $a['register_Already']);
        }
        $pSql = "SELECT *  FROM " . DB_PREFIX . 'user_point_site';
        $res = $this->pointMod->querySql($pSql);
        $register_point = $res[0]['register_point'];


        $tmp = array(
            'username' => $wx_nickname,
            'email' => $username,
            'password' => $password,
            'phone_email' => $email_phone,
            'store_id' => $storeid,
            'add_time' => time(),
            'openid' => $wx_openid,
            'nickname' => $wx_nickname,
            'city' => $wx_city,
            'province' => $wx_province,
            'country' => $wx_country,
            'headimgurl' => $wx_headimgurl,
            'sex' => $wx_sex,
            'point' => $register_point
        );
        $result = $this->userMod->doInsert($tmp);
        //生成2维码
        $codee = $this->goodsZcode($result, $storeid, $lang, $latlon);
        $urldata = array(
            "table" => "user",
            'cond' => 'id = ' . $result,
            'set' => "user_url='" . $code . "'",
        );
        $ress = $this->userMod->doUpdate($urldata);

        if ($result) {
            //日志
            $logData = array(
                'operator' => '--',
                'username' => $wx_nickname,
                'add_time' => time(),
                'note' => '注册获得' . $register_point . '积分',
                'userid' => $result,
                'deposit' => $register_point,
                'expend' => '-',
            );
            $pointLogMod = &m("pointLog");
            $pointLogMod->doInsert($logData);
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
                        $logData['username'] = $val['phone'];
                        $logData['note'] = '推荐会员获得' . $pointData[0]['first_point'] . '积分';
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
                        $logData['username'] = $val['phone'];
                        $logData['note'] = '推荐会员获得' . $pointData[0]['second_point'] . '积分';
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
                        $logData['username'] = $val['phone'];
                        $logData['note'] = '推荐会员获得' . $pointData[0]['third_point'] . '积分';
                        $logData['deposit'] = $pointData[0]['third_point'];
                        $logData['userid'] = $val['id'];
                        $pointLogMod->doInsert($logData);
                    }
                }
            }
            $_SESSION['userId'] = $result;
            $_SESSION['userName'] = $wx_nickname;
            $_SESSION['wx_openid'] = $wx_openid;

            $info['url'] = 'wx.php?app = default&act = index&storeid = ' . $storeid . '&lang = ' . $lang . '&latlon=' . $latlon;
            $this->setData($info, $status = '1', $a['Success_login']); //注册成功
        } else {
            $this->setData(array(), $status = '0', $a['fail_login']); //注册失败
        }
    }

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

    public function loginout() {
        //第一步：删除服务器端
        unset($_SESSION['userId']);
        //第二步：删除$_SESSION全部变量数组
        $_SESSION = array();
        session_destroy();
        //第三步：删除实际的session
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600);
        }
        //删除cookie
        foreach ($_COOKIE as $key => $val) {
            setcookie($key, '', time() - 3600);
        }
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;

//        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
//        $url = "wx.php?app=default&act=index&storeid=" . $this->storeid . "&lang=" . $this->langid . "&auxiliary=" . $auxiliary;
        $url = "wx.php?app=default&act=index&latlon=" . $latlon;
        header("Location:$url");
    }

    /**
     * 忘记密码
     * @author wangh
     * @date 2217/07/19
     */
    public function forget() {
        //语言包
        $this->load($this->shorthand, 'WeChat/user_login');
        $this->assign('langdata', $this->langData);
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;   //所选的站点id
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $this->assign("store_id", $storeid);
        $this->assign("lang", $lang);
        $languages = $this->shorthand;
        $this->assign('languages', $languages);
        $this->display('public/forget-password-1.html');
    }

    /**
     * 忘记密码功能
     * @author wanyan
     * @date 2017-08-03
     */
    public function changePasswordE() {
        $this->load($_REQUEST['languages'], 'user_login/user_login');
        $a = $this->langData;
        $phone = trim($_REQUEST['phone']);
        $passwd = trim($_REQUEST['passwd']);
        $code = $_REQUEST['code'];
        if (empty($phone)) {
            $this->setData(array(), $status = '0', $a['login_Mobilephone']);
        }
        if (!preg_match('/^(13[0-9]|14[0-9]|15[0-9]|17[0-9]|18[0-9])\d{8}$/', $phone)) {
            $this->setData(array(), $status = '0', $a['login_number']);
        }
        $user_info = $this->getPhoneInfo($phone);
        if (empty($user_info)) {
            $this->setData(array(), $status = '0', $a['unregistered']);
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
            $info['url'] = "wx.php?app=user&act=login&storeid=" . $this->storeid . "&lang=" . $this->langid;
            $this->setData($info, $status = '1', $a['reset_Success']);
        } else {
            $this->setData($info = array(), $status = '0', $a['reset_fail']);
        }
    }

    /**
     * 获取用户列表
     * @author zl
     * @date 2016-8-6
     */
    public function userLists() {
        $page = !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : '1';
//做分页数据处理
        $page = $page - 1;
        $limit = 10;
        $start = $page * $limit;
        $limit = $start . ', ' . $limit;

        $userModel = &m('user');
        $info = $userModel->getAll($limit);
        $this->setData($info, $status = 'success', $message = 'Loading completed');
//		if($userLists){
//			$this -> setData($userLists , $status = 'success' , $message = 'Loading completed');
////			exit(json_encode(array('status' => 'success' , 'message' => 'Loading completed' ,'info' => $userLists)));
//		}else{
//			$this -> setData($userLists , $status = 'error' , $message = 'No users');
////			exit(json_encode(array('status' => 'error' , 'message' => 'No users' ,'info' => null)));
//		}
    }

    public function doFxuser() {
        $fx_code = ($_REQUEST['fx_code']) ? $_REQUEST['fx_code'] : '';
        $redirectUrl = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?app=user&act=doFxuserStep'; //http://127.0.0.1/bspm711/wx.php?app=easyDis&act=index
        $url = $this->getOAuthUrl($redirectUrl, 'snsapi_base', 1);
        setcookie('fx_code', $fx_code, time() + 3600 * 2);
        header("Location:" . $url);
    }

    /*
     * 推荐分销用户
     * @author lee
     * @date 2018-1-17 19:36:43
     */

    public function doFxuserStep() {
        $fx_code = ($_COOKIE['fx_code']) ? $_COOKIE['fx_code'] : '';
        $code = $_REQUEST['code'];
//设置微信cook
        if ($code) {
            $this->setWxCook($code);
        }
        $this->load($this->shorthand, 'WeChat/user_login');
        $this->assign('langdata', $this->langData);
        $this->assign("tj_code", $fx_code);
        $this->display("easydis/register.html");
    }

    /*
     * 处理分销注册
     * @author lee
     * @date 2017-11-25 15:47:06
     */

    public function doFxregister() {
        $this->load($this->shorthand, 'WeChat/userCenter');
        $a = $this->langData;
        $phone = !empty($_REQUEST['phone']) ? htmlspecialchars($_REQUEST['phone']) : '';
        $password = !empty($_REQUEST['password']) ? htmlspecialchars($_REQUEST['password']) : '';
        $code = $_REQUEST['code'];
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->mrstoreid;   //所选的站点id
        $real_name = ($_REQUEST['real_name']) ? trim($_REQUEST['real_name']) : '';
        $tj_code = ($_REQUEST['tj_code']) ? trim($_REQUEST['tj_code']) : '';
        $bank_name = ($_REQUEST['bank_name']) ? trim($_REQUEST['bank_name']) : '';
        $bank_account = ($_REQUEST['bank_account']) ? trim($_REQUEST['bank_account']) : '';

        if (!$phone) {
            $this->setData(array(), $status = '0', $a['userinfo_Mobile_Phone_Error']); //不能为空
        }
        if (!preg_match('/^(13[0-9]|14[0-9]|15[0-9]|17[0-9]|18[0-9])\d{8}$/', $phone)) {
            $this->setData(array(), $status = '0', $a['userinfo_Phone_Error']);
        }
        if ($this->getPhoneInfo($phone)) {
            $this->setData(array(), $status = '0', $a['userinfo_Hasbeen_Phone']);
        }

        $smsCode = $this->getSmsCode($phone);
        if (empty($code)) {
            $this->setData(array(), $status = '0', $a['userinfo_Code_Error']);
        }
        if ($code != $smsCode) {
            $this->setData(array(), $status = '0', $a['userinfo_Incorrect']);
        }
        if (!$password) {
            $this->setData(array(), $status = '0', $a['userinfo_input']); //密码不能空
        }

        if (empty($real_name)) {
            $this->setData(array(), $status = '0', $a['fx_real_name']);
        }
        if (empty($bank_name)) {
            $this->setData(array(), $status = '0', $a['fx_bank_name']);
        }
        if (empty($bank_account)) {
            $this->setData(array(), $status = '0', $a['fx_bank_account']);
        }
//判断是否已经是分销
        $has = $this->fxUserMod->getOne(array("cond" => "phone=" . $phone));
        if ($has) {
            $this->setData(array(), $status = '0', $a['fx_has_phone']);
        }

// $storeMod = &m('store');
//  $store_info = $storeMod->getOne(array("cond" => "id=" . $store_id),true);
        $fx_f_info = $this->fxUserMod->getOne(array("cond" => "fx_code='" . $tj_code . "'"));

//从cookie 里 获取openid
        $wx_openid = $_COOKIE['wx_openid'];
        $wx_nickname = $_COOKIE['wx_nickname'];
        $wx_city = $_COOKIE['wx_city'];
        $wx_province = $_COOKIE['wx_province'];
        $wx_country = $_COOKIE['wx_country'];
        $wx_headimgurl = $_COOKIE['wx_headimgurl'];
        $wx_sex = $_COOKIE['wx_sex'];

        $password = md5($password);
        $tmp = array(
            'username' => $wx_nickname,
            'phone' => $phone,
            'password' => $password,
            'login_type' => 'member',
            'add_time' => time(),
            'store_id' => $store_id,
            'store_cate_id' => $fx_f_info['store_cate'],
            'nickname' => $wx_nickname,
            'city' => $wx_city,
            'province' => $wx_province,
            'country' => $wx_country,
            'headimgurl' => $wx_headimgurl,
            'sex' => $wx_sex,
            'openid' => $wx_openid,
            'phone_email' => $fx_f_info['telephone']
        );
        $fx_info = array(
            'real_name' => $real_name,
            'telephone' => $phone,
            'bank_name' => $bank_name,
            'bank_account' => $bank_account,
            'tj_code' => $tj_code,
            'store_cate' => $fx_f_info['store_cate'],
            'user_id' => $fx_f_info['user_id']
        );
        $result = $this->userMod->doInsert($tmp);
        if ($result) {
            $_SESSION['userId'] = $result;
            $_SESSION['userName'] = $wx_nickname;
            $r = $this->addFxuser($wx_nickname, $fx_info, $result);
            $info['url'] = 'wx.php?app=userCenter&act=myCenter&storeid=' . $this->storeid . '&lang=' . $this->langid;
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
        $info['url'] = 'wx.php?app=userCenter&act=myCenter';
        if (empty($fx_info)) {
            $this->setData($info, $status = '0', $a['fx_tj']);
        } elseif ($fx_tree['fx_level'] == 3) {
            $this->setData($info, $status = '0', $a['fx_no_tree']);
        }
        $fx_code = $this->make_fxcode();
        $fx_img = $this->shareMyfxcode($fx_code);
        $data = array(
            'user_id' => $user_id,
            'real_name' => $fx_info['real_name'],
//            'email' => $username,
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
                'user_id' => $user_id,
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
        $value = "http://" . SYSTEM_WEB . "/" . SYSTEM_FILE_NAME . "/wx.php?app=user&act=doFxuser&fx_code=" . $fx_code; //二维码内容
        if ($_SERVER["PHP_SELF"]) {
            $_SERVER["PHP_SELF"] = str_replace("wx.php", "", $_SERVER["PHP_SELF"]);
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

    /**
     * 搜索用户
     * @author zl
     * @date 2016-8-6
     */
    public function searchUsers() {
        $page = !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : '1';
        $keywords = !empty($_REQUEST['keywords']) ? trim($_REQUEST['keywords']) : '';
        //这个user_id 是用户的  不是自己的   通过扫码快速定位好友信息
        $user_id = !empty($_REQUEST['user_id']) ? (int) $_REQUEST['user_id'] : '';
        $userModel = &m('user');
        $info = array();
        //做分页数据处理
        $page = $page - 1;
        $limit = 2;
        $start = $page * $limit;
        $limit = $start . ',' . $limit;

        if ($user_id) {
            $userInfo = $userModel->getInfoById($user_id);
            if ($userInfo) {
                $this->setData($userInfo, $status = 'success', $message = 'Loading completed');
            } else {
                $this->setData((object) $info, $status = 'error', $message = 'No search to the user information'); //没有此用户信息
            }
        }
        if (!$keywords) {
            $this->setData((object) $info, $status = 'error', $message = 'Please enter keywords'); //请输入搜索关键词
        } else {
            $userLists = $userModel->serachMember($keywords, $limit);
            if ($userLists) {
                $info = $userLists;
                $this->setData($info, $status = 'success', $message = 'Loading completed');
            } else {
                $this->setData($info, $status = 'error', $message = 'No search to the user information'); //没有此用户信息
            }
        }
    }

    /**
     * 查询用户信息
     * @author liyh
     * @date 2016-8-5
     */
    public function userinfo() {
        $userid = $this->id;
        $userModel = &m('user');
        $userInfo = $userModel->getUserImgById($userid);
        if (!$userInfo) {
            $status = "error";
            $message = "Find no record";
            $this->setData((object) $info = array(), $status, $message);
            exit();
        } else {
            $status = "success";
            $message = "Find success";
            $info = $userInfo;
            $this->setData($info, $status, $message);
            exit();
        }
    }

    /**
     * 用户主页
     * @author zl
     * @date 2016-8-15
     */
    public function userIndex() {
        $userId = $this->id; //自己id
        $user_id = !empty($_REQUEST['user_id']) ? (int) $_REQUEST['user_id'] : ''; //对方id
        $info = array();
        $userModel = &m('user');
        $sourceModel = &m('mysource');
        $followModel = &m('follow');
        $friendsModel = &m('friends');

        if (!$user_id) {
            $this->setData((object) $info, $status = 'error', $message = 'Without this user');
        }

        $userInfo = $userModel->getUserImgById($user_id); //用户基本信息
        if ($userInfo) {

            $isFollow = $followModel->getIsFollow($userId, $user_id); //查出是否已经关注
            $isFriend = $friendsModel->getIsFriend($userId, $user_id); //查出是否已是好友

            $sourceInfo = $sourceModel->getinfodata($user_id, $status = 0); //用户上传视频信息
        } else {
            $this->setData((object) $info, $status = 'error', $message = 'Without this user');
        }

        $userInfo['isFollow'] = $isFollow; //归并数据
        $userInfo['isFriend'] = $isFriend; //归并数据
        $userInfo['source'] = array_slice($sourceInfo, 0, 5); //归并数据   选5个
        $info = $userInfo;
        $this->setData($info, $status = 'success', $message = 'Loading completed');
    }

    /**
     * 编辑用户信息
     * @author lvji
     * @date 2015-03-20
     */
    public function edit() {
        $adminMemberMode = &m('adminMember');
        if (IS_POST) {
            $id = !empty($id) ? (int) $_REQUEST['id'] : $this->adminId;
            $para = (int) $_REQUEST['para'];
            $data = array();
            $sex = !empty($_REQUEST['sex']) ? htmlspecialchars($_REQUEST['sex']) : '';
            $mobile = !empty($_REQUEST['mobile']) ? htmlspecialchars($_REQUEST['mobile']) : '';
            $headimg_url64 = $_REQUEST['headimg_url'];
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $headimg_url64, $result)) {
//$headimg_url = base64_decode($result[1]);
                $headimg_url = base64_decode(str_replace($result[1], '', $headimg_url64));
            }

//$headimg_url = base64_decode($headimg_url64);
            $filename = !empty($_REQUEST['filename']) ? htmlspecialchars($_REQUEST['filename']) : '';
            if ($headimg_url) {
//拼接图片名称
                if (!$filename) {
                    $filename = 'jpg';
                } else {
                    $filename = substr($filename, -10);
                }
                $newName = substr(md5(date('HisYmd') . rand(0, 100)), - 10) . $filename;
                $headimg_url_tmp = UPLOAD_IMG_SAVE_PATH . $newName;
                $this->setFolder(UPLOAD_IMG_SAVE_PATH);
                $showUrl = str_replace(ATTACHEMENT_PATH, '', $headimg_url_tmp);
                file_put_contents($headimg_url_tmp, $headimg_url);
                @chmod($headimg_url_tmp, 0777);
                $data['headimg_url'] = $showUrl;
            }
            if ($sex) {
                $data['sex'] = $sex;
            }
            if ($mobile) {
                $data['mobile'] = $mobile;
            }

            $id = $adminMemberMode->edit($data, $id);
            if ($id) {
                $info['updateInfo'] = $data;
                $this->setData($info, $status = 'success', $msg = '恭喜您，操作成功！');
            } else {
                $this->setData(array(), $status = 'error', $msg = '操作失败！');
            }
        }
        $info['memberInfo'] = $this->adminInfo;
        $this->setData($info, $status = 'success', $msg = '获取数据成功！');
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

            $this->setData($info, $status = 'success', $msg = 'upload success');
        } else {
            $this->setData(array(), $status = 'error', $msg = 'upload file first!');
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
     * 修改密码
     * @author lvji
     * @date2015-03-16
     */
    pubLic function changePwd() {
        $adminMemberMode = &m('adminMember');
        $id = intval($_REQUEST['id']);
        if (IS_POST) {
            $old = trim($_POST['old']);
            $password = trim($_POST['np']);
            $info = array();
            if (!$old) {
                $this->setData($info, $status = 'error', $msg = '您没有填写旧密码');
            } else {
                $checkPassword = md5($old . $this->adminInfo['username']);
                if ($checkPassword != $this->adminInfo['password']) {
                    $this->setData($info, $status = 'error', $msg = '旧密码输入错误');
                }
            }

            if (!$password) {
                $this->setData($info, $status = 'error', $msg = '您还没有填写新密码');
            } else {
                if (absLength($password) <= 5) {
                    $this->setData($info, $status = 'error', $msg = '新密码长度不能小6位');
                }
                $password = md5($password . $this->adminInfo['username']);
                $data["password"] = $password;

                $adminMemberMode->edit($data, $this->adminInfo['id']);
                $this->setData($info, $status = 'success', $msg = '恭喜您密码修改成功！');
            }
        }
        $info['memberInfo'] = $this->adminInfo;
        $this->setData($info, 'success', '修改密码');
    }

    /**
     * 修改密码
     * $Author: huxw $
     * $Datetime: 2016-08-08 10:20:44 $
     */
    pubLic function changePassword() {
        $userModel = &m('user');
        $username = !empty($_REQUEST['username']) ? trim($_REQUEST['username']) : '';
        $old = !empty($_REQUEST['oldpassword']) ? trim($_REQUEST['oldpassword']) : '';
        $password = !empty($_REQUEST['newpassword']) ? trim($_REQUEST['newpassword']) : '';
        $info = null;
        if ($old || $password) {//修改密码
            if (!$old) {
                $this->setData($info, $status = 'error', $message = 'Old password required');
            } else {
                $checkPassword = md5($old . $this->adminInfo['username']);
                if ($checkPassword != $this->adminInfo['password']) {
                    $this->setData($info, $status = 'error', $message = 'Old password error');
                }
            }
            if (!$password) {
                $this->setData($info, $status = 'error', $message = 'New password required');
            }
        }

        if ($username) {
            if ($userModel->isExist($type = 'username', $username)) {//判断会员名是否重复
                $this->setData($info, $status = 'error', $message = 'User name already exists');
            }
        }

//			if(absLength($password) <= 5){
//				$this->setData($info, $status='error', $message='新密码长度不能小6位');
//			}
        if ($username || $password) {

            if ($username)
                $data["username"] = $username;
            $user_pwd = !empty($username) ? $username : $this->adminInfo['username'];
            if ($password) {
                $password = md5($password . $user_pwd);
                $data["password"] = $password;
            }

            $data["modify_time"] = time();

            $result = $userModel->edit($data, $this->adminInfo['id']);
            if ($result) {
                $info = array('id' => $this->adminInfo['id'], 'username' => $user_pwd, 'password' => !empty($password) ? $password : $this->adminInfo['password']
                    , 'modify_time' => time());
                $this->setData($info, $status = 'success', $message = 'Congratulations on your successful modification！');
            } else {
                $this->setData($info = null, $status = 'error', $message = 'Modify failed!');
            }
        }
//$info['memberInfo'] = $this->adminInfo;
//$this->setData($info, 'success', 'Modify the password');
    }

//	public function myspread()
//	{
//		$userId = $this -> id;
//		$userModel = &m('user');
//		if(!$userId){
//			$this -> setData($info ,  $status = 'error' , $message = 'Lack of userId');
//		}
//		$userInfo = $userModel -> getInfoById($userId);
//
//		require_once(dirname(__FILE__).'/phpqrcode.php');
//		$value = "http://".$_SERVER['HTTP_HOST']."/liveshow/index.php?app=user&act=searchUsers&user_id=".$userId; //二维码内容
//
//		$errorCorrectionLevel = 'L';//容错级别
//		$matrixPointSize = 6;//生成图片大小
//		$qrcode = ROOT_PATH."/upload/erwmimages/".$userId.".png";
//
//		if(file_exists("$qrcode")){
//			$qrcode = "http://".$_SERVER['HTTP_HOST']."/liveshow/upload/erwmimages/".$userId.".png";
//		}else{
//			//生成二维码图片
//			$QRcode = QRcode::png($value, $qrcode, $errorCorrectionLevel, $matrixPointSize, 2);
//			$qrcode = "http://".$_SERVER['HTTP_HOST']."/liveshow/upload/erwmimages/".$userId.".png";
//		}
//		$info['erwm_img'] = $qrcode;
//
//		if(!$userInfo['erwm_img']){
//			$userModel -> edit($info , $userId);
////			if($res){
////				$this -> setData($info , $status = 'success' , $message = 'Successful');
////			}
//		}
//		$this -> adminInfo['erwm_img'] = $qrcode;
//		$this -> setData($info , $status = 'success' , $message = 'Successful');
//	}

    /**
     * 用户中心
     * @author lvji
     * @date 2015-03-16
     */
    public function userCenter() {
        $department_mod = &m("department");
        $company_id = $this->company_id;
        $contactList = array();
        if ($company_id == MAIN_COMPANY) {
            $company_id = 0;
            $contactList = $this->getContactList($company_id);
        } else {
            $contactList = $this->getContactList($company_id);
        }
        $info = array();
//获取用户信息
        $info['memberInfo'] = $this->adminInfo;
        $info['contactList'] = $contactList;
//获取联系方式
        $this->setData($info);
    }

    /**
     * 获取联系人列表
     * @author lvji
     * @date 2015-03-16
     */
    public function getContactList($company_id) {
        $member_mod = &m("adminMember");
        $department_mod = &m("department");
        if ($company_id == 0) {
            $extern_depart = $department_mod->getInfo(35);
            $extern_departs = $extern_depart['departs'];
            $depart_cond = "mark = 1 AND department_id not in ({$extern_departs})";
            $parent_id = 0;
        } else {
            $department_info = $department_mod->getInfo($company_id);
            $departs = $department_info['departs'];
            $main_info = $department_mod->getInfo(MAIN_COMPANY);
            $main_departs = $main_info['departs'];
            $departs = $main_departs . "," . $departs;
            $depart_cond = "mark = 1 AND department_id in ({$departs})";
            $parent_id = $company_id;
        }
        $member_ids = $department_mod->getIds($depart_cond, "cg_admin_department_relation", "member_id");
        $member_ids = implode(",", $member_ids);
        $member_cond[] = "state  = 1";
        $member_cond[] = "mark = 1";
        $member_cond[] = "username not like \"%测试%\"";
        $member_cond[] = "username not like \"admin\"";
        $member_cond[] = "id in ({$member_ids})";
        $member_cond = implode(" AND ", $member_cond);
//取出要展示的所有用户
        $member_ids = $member_mod->getIds($member_cond);
        $member_ids = implode(",", $member_ids);
//取出都展示的所有部门
        $department_ids = $department_mod->getIds($depart_cond, "cg_admin_department_relation", "department_id");
//取出公司的所有部门
        $parent_ids = $parent_id == 0 ? 0 : MAIN_COMPANY . "," . $parent_id;
        $cond = $parent_ids ? "id in ({$parent_ids})" : 0;
        $list = $department_mod->getDepartmentList(0, 0, $cond);
        $department_relation_mod = &m("adminDepartmentRelation");
        $position_mod = &m("position");
        foreach ($list as $key => &$row) {
            if (in_array($row['id'], $department_ids)) {
                $name = $department_mod->getDepFullName($row['id']);
                $row['name'] = $name;
            } else {
                unset($list[$key]);
            }
            $member_list = $department_relation_mod->getDepartmentMembers($row['id'], $member_ids);
            foreach ($member_list as &$member) {
                $member = $member_mod->getInfo($member);
                $department_list = $department_relation_mod->getMemberDepartments($member['id']);
                foreach ($department_list as $depart_info) {
                    if ($depart_info['department_id'] == $row['id']) {
                        $position_info = $position_mod->getInfo($depart_info['position_id']);
                        $member['level'] = $position_info['level'];
                        break;
                    }
                }
            }
            unset($member);
            $member_list = array_sort($member_list, "level");
            if ($member_list[0]) {
                $member_list[0]['first'] = true;
            } else {
                unset($list[$key]);
            }
            $row['num'] = count($member_list);
            $row['member_list'] = $member_list;
        }
        unset($row);
        return $list;
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

    /**
     * 订单列表
     * @author zhoux
     * @date 2016-11-15
     */
    public function orderList() {
        $userId = $this->id;
        $tradeModel = &m('trade');
        $order_list = $tradeModel->getAllUser($userId, $_POST['trade_sn']);
        $order_list['list'] = $tradeModel->get_member($order_list['list']);
        $this->assign('order_list', $order_list);
        $this->display('user/order.list.html');
    }

    /**
     * 退货、信息 info
     * @author zhoux
     * @date 2016-11-15
     */
    public function returnRefund() {
        $tradeModel = &m('trade');
        $order_list = $tradeModel->returnRefund($_GET['id']);
        $order_list = $tradeModel->after_sale($order_list);
        $this->assign('order_list', $order_list);
        $this->display('user/after.sale.html');
    }

    /**
     * 退货、退款管理 确认进入数据库
     * @author zhoux
     * @date 2016-11-15
     */
    public function returnRefundConfirm() {
        $id = !empty($_GET['id']) ? htmlspecialchars($_GET['id']) : '81';
        $tradeModel = &m('trade');
        $order_list = $tradeModel->returnRefund($id); //主订单信息
        $order_list = $tradeModel->after_sale($order_list);
        $array = array(); //退货 ID 的数量 和 计算额总价格
        $afterSale = &m('afterSale');
        $afterSaleLog = &m('afterSaleLog');
        $tmp = array(
            'user_id' => !empty($order_list[0]['user_id']) ? htmlspecialchars($order_list[0]['user_id']) : '1',
            'shop_id' => !empty($order_list[0]['shop_id']) ? htmlspecialchars($order_list[0]['shop_id']) : '1',
            'aftersales_type' => !empty($_POST['aftersales_type']) ? htmlspecialchars($_POST['aftersales_type']) : 'REFUND_GOODS',
            'progress' => !empty($_POST['progress']) ? htmlspecialchars($_POST['progress']) : '0',
            'status' => !empty($_POST['status']) ? htmlspecialchars($_POST['status']) : '0',
            'trade_id' => !empty($order_list[0]['trade_id']) ? htmlspecialchars($order_list[0]['trade_id']) : '1',
            'goods_data' => serialize($array),
            'total_price' => !empty($_POST['total_price']) ? htmlspecialchars($_POST['total_price']) : '1',
            'title' => !empty($order_list[0]['title']) ? htmlspecialchars($order_list[0]['title']) : '1',
            'add_time' => time(),
        );
        $result = $afterSale->edit($tmp);
        if ($result) {
            $tmp = array(
                'user_id' => !empty($order_list[0]['user_id']) ? htmlspecialchars($order_list[0]['user_id']) : '1',
                'parent_id' => 0,
                'aftersales_id' => $result,
                'content' => !empty($_POST['content']) ? htmlspecialchars($_POST['content']) : '123123',
                'username' => !empty($_POST['username']) ? htmlspecialchars($_POST['username']) : '测试1',
                'add_time' => time(),
            );
            $afterSaleLog->edit($tmp);
            $this->setData($info = array('url' => '?app=brand&act=index'), '', '编辑成功！');
        } else {
            $this->setData($info = array(), "error", '编辑失败！');
        }
    }

    /**
     * 全额退款
     * @author zhoux
     * @date 2016-11-15
     */
    public function returnRefunds() {
        $id = !empty($_GET['id']) ? htmlspecialchars($_GET['id']) : '81'; //退款的订单号
        $tradeModel = &m('trade');
        $afterSale = &m('afterSale');
        $order_list = $tradeModel->returnRefund($id); //主订单信息
        $order_list = $tradeModel->after_sale($order_list);
        foreach ($order_list as $k => $vo) {
            $tmp = array(
                'user_id' => !empty($vo['user_id']) ? htmlspecialchars($vo['user_id']) : '1',
                'shop_id' => !empty($vo['shop_id']) ? htmlspecialchars($vo['shop_id']) : '1',
                'aftersales_type' => !empty($_POST['aftersales_type']) ? htmlspecialchars($_POST['aftersales_type']) : 'ONLY_REFUND',
                'progress' => !empty($_POST['progress']) ? htmlspecialchars($_POST['progress']) : '0',
                'status' => !empty($_POST['status']) ? htmlspecialchars($_POST['status']) : '0',
                'trade_id' => !empty($vo['trade_id']) ? htmlspecialchars($vo['trade_id']) : '1',
                'order_id' => !empty($vo['order_id']) ? htmlspecialchars($vo['order_id']) : '1',
                'total_price' => !empty($vo['total_fee']) ? htmlspecialchars($vo['total_fee']) : '1',
                'num' => !empty($vo['num']) ? htmlspecialchars($vo['num']) : '1',
                'title' => !empty($_POST['title']) ? htmlspecialchars($_POST['title']) : '1',
                'add_time' => time(),
            );
            $result = $afterSale->edit($tmp);
        }
        if ($result) {
            $this->setData($info = array(), '', '申请退款成功！等待商家确认');
        } else {
            $this->setData($info = array(), 'error', '网络错误！');
        }
    }



}
