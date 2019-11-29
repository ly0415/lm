<?php
/**
 * 个人中心控制器
 * @author: tangp
 * @date:   2018-08-23
 */
class UserCenter1App extends BasePhApp
{
    private $colleCtionMod;
    private $userStoreMod;
    private $userMod;
    private $userArticleMod;
    private $giftGoodMod;
    private $orderGoodsMod;
    private $orderMod;
    private $footPrintMod;
    private $fxUserMod;
    private $fxTreeMod;
    private $storeCateMod;
    private $fxuserMoneyMod;
    private $pointMod;
    private $pointOrderMod;
    private $pointLogMod;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();

        $this->colleCtionMod = &m('colleCtion');
        $this->userStoreMod = &m('userStore');
        $this->userMod = &m('user');
        $this->userArticleMod = &m('userArticle');
        $this->giftGoodMod = &m('giftGood');
        $this->orderGoodsMod = &m('orderGoods');
        $this->orderMod = &m('order');
        $this->footPrintMod = &m('footprint');
        $this->fxUserMod = &m('fxuser');
        $this->fxTreeMod = &m('fxuserTree');
        $this->storeCateMod = &m('storeCate');
        $this->fxuserMoneyMod = &m('fxuserMoney');
        $this->pointMod = &m('point');
        $this->pointOrderMod = &m('pointOrder');
        $this->pointLogMod = &m('pointLog');
    }
    /**
     * 获取openid
     * @author:tangp
     * @date:2018-09-13
     */
    public function login()
    {
        //获取code
        $code = !empty($_REQUEST['code']) ? $_REQUEST['code'] : "";

        if (empty($code)){
            $this->setData(array(),1,'请传递code到后台！');
        }

        $APPID = "wxd483c388c3d545f3";
        $AppSecret = "d19b0561679a32122f10d524153f7ea5";
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=" . $APPID . "&secret=" . $AppSecret . "&js_code=" . $code . "&grant_type=authorization_code";
        $res = $this->vget($url);
//        var_dump($res);die;
        $arr = json_decode($res, true);
        $openid = $arr['openid'];
        $this->setData($openid,'1','');


    }


    /**
     * 验证是注册还是登录
     * @author gao
     * @date 2018/8/16
     */
    public function doquickLogin() {
        $storeid = $this->store_id;
        $phone = !empty($_REQUEST['phone']) ? trim($_REQUEST['phone']) : '';
        $phoneCode = !empty($_REQUEST['code']) ? $_REQUEST['code'] : 0;//
        $openid    = !empty($_REQUEST['openid']) ? $_REQUEST['openid'] : "";
        $nickname  = !empty($_REQUEST['nickname']) ? $_REQUEST['nickname'] : "";
        $gender    = !empty($_REQUEST['gender']) ? $_REQUEST['gender'] : "";
        $city      = !empty($_REQUEST['city']) ? $_REQUEST['city'] : "";
        $province  = !empty($_REQUEST['province']) ? $_REQUEST['province'] : "";
        $country   = !empty($_REQUEST['country']) ? $_REQUEST['country'] : "";
        $avatarUrl = !empty($_REQUEST['avatarUrl']) ? $_REQUEST['avatarUrl'] : "";
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;  //多语言商品
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        if (empty($phone)){
            $this->setData('',0,'请填写手机号码！');
        }
        if (empty($phoneCode)) {
            $this->setData('',0,'请输入验证码！');
        }
        //查看当前是否存在该用户
        $userSql  = "SELECT * FROM bs_user WHERE phone ={$phone} AND mark=1";
        $userData = $this->userMod->querySql($userSql);
        if (empty($userData)) {
            //注册
            $smsCode = $this->getSmsCode($phone);
            if ($phoneCode != $smsCode) {
                $this->setData('',0,'验证码不正确！');
            }
            $pSql = "SELECT * FROM bs_user_point_site";
            $res = $this->pointMod->querySql($pSql);
            $register_point = $res[0]['register_point'];
            $register_recharge = $res[0]['register_recharge'];
            $systemNickName = '艾美睿'.rand(100000,999999);
            $tmp = array(
                'phone'      => $phone,
                'username'   => $systemNickName,
                'add_time'   => time(),
                'login_type' => 'member',
                'store_id'   => $storeid,
                'xcx_openid' => $openid,
                'nickname'   => $systemNickName,
                'city'       => $city,
                'province'   => $province,
                'country'    => $country,
                'headimgurl' => $avatarUrl,
                'sex'        => $gender,
                'point'      => $register_point,
                'amount'     => $register_recharge
            );
            $result = $this->userMod->doInsert($tmp);

            $amountData = array(
                'order_sn' => '',
                'type'     => 3,
                'status'   => 4,
                'c_money'  => $register_recharge,
                'new_money'=> $register_recharge,
                'source'   => 2,
                'add_user' => $result,
                'add_time' => time(),
                'mark'     => 1
            );
            $amountLog = &m('amountLog');
            $amountLog->doInsert($amountData);

            $sqll = "SELECT * FROM bs_user WHERE phone =".$phone;
            $uResult = $this->userMod->querySql($sqll);

            $userMod = &m('user');
            $userMod->sendSms($phone);

            $systemConsoleMod = &m('systemConsole');
            $userCoupon=&m('userCoupon');
            $getCouponActivityStatus=$systemConsoleMod->getCouponActivityStatus();//获取设置注册送电子券是否开启
            if ($getCouponActivityStatus['1']==1){
                $coupon = $systemConsoleMod->getSetCoupon();
                $duiCoupon = $systemConsoleMod->getSetDuiCoupon();
                $limitTiems=$coupon[0]['limit_times']*3600*24;
                $limit=$duiCoupon[0]['limit_times']*3600*24;
                if (!empty($coupon)){
                    $data = array(
                        'c_id' => $coupon[0]['id'],
                        'remark'=>'注册送抵扣券',
                        'add_time'=>time(),
                        'start_time'=>time(),
                        'end_time'=>time()+$limitTiems,
                        'user_id'=>$result
                    );
                    $userCoupon->doInsert($data);

                    $userMod->sendMessage($result);
                }
                if (!empty($duiCoupon)){
                    $data = array(
                        'c_id' => $duiCoupon[0]['id'],
                        'remark'=>'注册送兑换券',
                        'add_time'=>time(),
                        'start_time'=>time(),
                        'end_time'=>time()+$limit,
                        'user_id'=>$result
                    );
                    $userCoupon->doInsert($data);
                    $userMod->sendMessage($result);
                }
            }
            if (!empty($uResult)) {
                //生成二维码
                $codee = $this->goodsZcode($result,$storeid,$lang,$latlon);
                $urldata = array(
                    "table" => "user",
                    'cond'  => 'id ='.$result,
                    'set'   => "user_url='" . $codee . "'"
                );
                $ress = $this->userMod->doUpdate($urldata);
                if ($ress) {
                    $logData = array(
                        'operator' => '--',
                        'username' => $phone,
                        'add_time' => time(),
                        'note'     => '注册获得' . $register_point . '睿积分',
                        'userid'   => $result,
                        'deposit'  => $register_point,
                        'expend'   => '-'
                    );
                    $pointLogMod = &m('pointLog');
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


                    $this->setData('',1,'注册成功！');
                } else {
                    $this->setData('',0,'注册失败！');
                }
            }
        } else {
            //登录
            if (!empty($userData[0]['xcx_openid'])) {
                if ($userData[0]['xcx_openid'] != $openid) {
                    $this->setData('',0,'该手机号已绑定其他微信号！');
                } else {
                    $smsCode = $this->getSmsCode($phone);
                    if ($phoneCode != $smsCode) {
                        $this->setData('',0,'验证码错误！');
                    }
                    if ($phoneCode == $smsCode) {
                        $s = "SELECT id FROM bs_user WHERE phone=".$phone;
                        $e = $this->userMod->querySql($s);
                        if ($e) {
                            $arr = array(
                                'user_id' => $e[0]['id']
                            );
                            $this->setData($arr,1,'登陆成功！');
                        }else{
                            $this->setData('',0,'登录失败！');
                        }
                    }
                }
            } else {
                $smsCode = $this->getSmsCode($phone);
                if ($phoneCode != $smsCode) {
                    $this->setData('',0,'验证码错误！');
                }
                $this->userMod->doEdit($userData[0]['id'],array('xcx_openid' => $openid));
                if ($phoneCode == $smsCode) {
                    $sqls = "SELECT id FROM bs_user WHERE phone =".$phone;
                    $ss = $this->userMod->querySql($sqls);
                    if ($ss) {
                        $arr = array(
                            'user_id' => $ss[0]['id']
                        );
                        $this->setData($arr,1,'登陆成功！');
                    }else{
                        $this->setData('',0,'登录失败！');
                    }
                }
            }
        }

    }
    /**
     * 获取用户openid
     */
    public function getOpenid($openid, $existPhone = '') {
        $sql = "select id from bs_user where mark = 1 and xcx_openid = '{$openid}' ";
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
    //登录注册
    public function quickLogin()
    {
        $storeid = $this->store_id;
        $phone = !empty($_REQUEST['phone']) ? htmlspecialchars(trim($_REQUEST['phone'])) : '';
        $userId = !empty($_REQUEST['userId']) ? $_REQUEST['userId'] : '';
        $lang_id = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : '';
        $phoneCode = !empty($_REQUEST['code']) ? $_REQUEST['code'] : 0;//
        $nickname  = !empty($_REQUEST['nickname']) ? $_REQUEST['nickname'] : "";
        $gender    = !empty($_REQUEST['gender']) ? $_REQUEST['gender'] : "";
        $city      = !empty($_REQUEST['city']) ? $_REQUEST['city'] : "";
        $province  = !empty($_REQUEST['province']) ? $_REQUEST['province'] : "";
        $country   = !empty($_REQUEST['country']) ? $_REQUEST['country'] : "";
        $avatarUrl = !empty($_REQUEST['avatarUrl']) ? $_REQUEST['avatarUrl'] : "";
        $openid = !empty($_REQUEST['openid']) ? $_REQUEST['openid'] : "";
        if (empty($phone)){
            $this->setData('',0,'请填写手机号码！');
        }
        if (empty($phoneCode)){
            $this->setData('',0,'请输入验证码！');
        }
        if ($this->getOpenid($openid,$phone)){
            $this->setData('',0,'该微信号已绑定其他的手机号！');
        }
        $userSql = 'select * from ' . DB_PREFIX . 'user where phone = ' . $phone . ' and mark =1';
        $userData = $this->userMod->querySql($userSql);
        $recomSql = "SELECT id,phone_email,phone,email FROM " . DB_PREFIX . 'user WHERE id=' . $userId . ' and mark=1';
        $recomData = $this->userMod->querySql($recomSql);
        $email_phone = $recomData[0]['phone'];

        //推荐人是否是分销人员
        $fxInfoSql = "SELECT id FROM " . DB_PREFIX . "fx_user where user_id=" . $userId . ' and level=3';
        $fxInfo = $this->userMod->querySql($fxInfoSql);

        if (empty($email_phone)){
            $email_phone = 0;
        }

        if ($userData[0]['phone'] == ''){
            $sql = 'select * from ' . DB_PREFIX . 'user where phone = ' . $phone;
            $uData = $this->userMod->querySql($sql);
            if (!empty($uData[0]['phone'])){
                $this->userMod->doEdit($uData[0]['id'],array('mark'=>1));
                exit();
            }
            $smsCode = $this->getSmsCode($phone);
            if ($phoneCode != $smsCode){
                $this->setData('',0,'验证码错误！');
            }
            if (empty($phoneCode)){
                $this->setData('',0,'验证码不能为空！');
            }
            $pSql = "SELECT *  FROM " . DB_PREFIX . 'user_point_site';
            $res = $this->pointMod->querySql($pSql);
            $register_point = $res[0]['register_point'];
            $register_recharge = $res[0]['register_recharge'];
            $systemNickName = '艾美睿' . rand(100000, 999999);
            $tmp = array(
                'phone'      => $phone,
                'username'   => $systemNickName,
                'add_time'   => time(),
                'phone_email'=> $email_phone,
                'login_type' => 'member',
                'store_id'   => $storeid,
                'xcx_openid' => $openid,
                'nickname'   => $systemNickName,
                'city'       => $city,
                'province'   => $province,
                'country'    => $country,
                'headimgurl' => $avatarUrl,
                'sex'        => $gender,
                'point'      => $register_point,
                // 'amount'     => $register_recharge
            );
            $result = $this->userMod->doInsert($tmp);
//            $amountData = array(
//                'order_sn' => '',
//                'type'     => 3,
//                'status'   => 4,
//                 'c_money'  => $register_recharge,
//                 'new_money'=> $register_recharge,
//                'source'   => 2,
//                'add_user' => $result,
//                'add_time' => time(),
//                'mark'     => 1
//            );
//            $amountLog = &m('amountLog');
//            $amountLog->doInsert($amountData);
            //注册送券2019-08-01
            $coupon = &m('coupon');
            $userCoupon = &m('userCoupon');
            $c_time = time();
            $juan = $coupon->getOne(array('cond'=>'id = 92'));
            if($juan){
                $uc = array(
                    'user_id' => $result,
                    'c_id' => $juan['id'],
                    'remark' => '新用户注册赠卷',
                    'source' => 4,
                    'start_time' => $c_time,
                    'end_time' => $c_time + 3600 * 24 * $juan['limit_times'],
                    'add_user' => $result,
                    'add_time' => $c_time
                );
                if($userCoupon->doInsert($uc)){
                    $this->userMod->sendMessage($result);
                }
            }

            if (!empty($fxInfo)){
                $fxUserAccountMod = &m('fxUserAccount');
                $fxUserAccountMod->addFxUser($fxInfo[0]['id'],$result, 3);
            }
            $sql = "select * from " . DB_PREFIX . 'user where phone= ' . $phone;
            $uresult = $this->userMod->querySql($sql);

            //注册成功发送通知
            $userMod = &m('user');
            $userMod->sendSms($phone);

            $systemConsoleMod = &m('systemConsole');
            $userCoupon=&m('userCoupon');
            $getCouponActivityStatus=$systemConsoleMod->getCouponActivityStatus();//获取设置注册送电子券是否开启
            if ($getCouponActivityStatus['1']==1){
                $coupon = $systemConsoleMod->getSetCoupon();
                $duiCoupon = $systemConsoleMod->getSetDuiCoupon();
                $limitTiems=$coupon[0]['limit_times']*3600*24;
                $limit=$duiCoupon[0]['limit_times']*3600*24;
                if (!empty($coupon)){
                    $data = array(
                        'c_id' => $coupon[0]['id'],
                        'remark'=>'注册送抵扣券',
                        'add_time'=>time(),
                        'start_time'=>time(),
                        'end_time'=>time()+$limitTiems,
                        'user_id'=>$result
                    );
                    $userCoupon->doInsert($data);

                    $userMod->sendMessage($result);
                }
                if (!empty($duiCoupon)){
                    $data = array(
                        'c_id' => $duiCoupon[0]['id'],
                        'remark'=>'注册送兑换券',
                        'add_time'=>time(),
                        'start_time'=>time(),
                        'end_time'=>time()+$limit,
                        'user_id'=>$result
                    );
                    $userCoupon->doInsert($data);
                    $userMod->sendMessage($result);
                }
            }

            if (!empty($uresult)){
                $codee = $this->createCode($userId);
                $urldata = array(
                    "table" => "user",
                    'cond'  => 'id = ' . $result,
                    "set"   => "xcx_code_url='" . $codee . "'"
                );
                $ress = $this->userMod->doUpdate($urldata);
                if ($ress){
                    $logData = array(
                        'operator' => '--',
                        'username' => $phone,
                        'add_time' => time(),
                        'note'     => '注册获得' . $register_point . '睿积分',
                        'userid'   => $result,
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
                    $userData = $this->child($data,$phone_email,1);
                    foreach ($userData as $key => $val){
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
                }
                $this->setData('',1,'注册成功！');
            }else{
                $this->setData('',0,'注册失败！');
            }
        }else{
            if (!empty($userData[0]['xcx_openid'])){
                if ($userData[0]['xcx_openid'] != $openid){
                    $this->setData(array(),0,'该手机号已绑定其他微信号！');
                }else{
                    $smsCode = $this->getSmsCode($phone);
                    if ($phoneCode != $smsCode){
                        $this->setData(array(),0,'验证码错误！');
                    }
                    if (empty($phoneCode)){
                        $this->setData(array(),0,'验证码不能为空！');
                    }
                    if ($phoneCode == $smsCode) {
                        $s = "SELECT id FROM bs_user WHERE phone=".$phone;
                        $e = $this->userMod->querySql($s);
                        if ($e) {
                            $arr = array(
                                'user_id' => $e[0]['id']
                            );
                            $this->setData($arr,1,'登陆成功！');
                        }else{
                            $this->setData('',0,'登录失败！');
                        }
                    }
                }
            }else{
                $smsCode = $this->getSmsCode($phone);
                if ($phoneCode != $smsCode) {
                    $this->setData('',0,'验证码错误！');
                }
                $this->userMod->doEdit($userData[0]['id'],array('xcx_openid' => $openid));
                if ($phoneCode == $smsCode) {
                    $sqls = "SELECT id FROM bs_user WHERE phone =".$phone;
                    $ss = $this->userMod->querySql($sqls);
                    if ($ss) {
                        $arr = array(
                            'user_id' => $ss[0]['id']
                        );
                        $this->setData($arr,1,'登陆成功！');
                    }else{
                        $this->setData('',0,'登录失败！');
                    }
                }
            }
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

    /**
     * 注册接口（页面）
     * @author:tangp
     * @date:2018-09-13
     */
    public function register()
    {
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;

        $langData = array(
            $this->langData->project->please_phone,
            $this->langData->project->please_code,
            $this->langData->public->send_code,
            $this->langData->public->quick_login,
            $this->langData->project->no_toLook
        );
        $data = array(
            'langData' => $langData
        );
        $this->setData($data,1,'');
    }
    /**
     * curl请求
     *
     */
    public function vget($url){
        $info=curl_init();
        curl_setopt($info,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($info,CURLOPT_HEADER,0);
        curl_setopt($info,CURLOPT_NOBODY,0);
        curl_setopt($info,CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($info,CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($info,CURLOPT_URL,$url);
        $output= curl_exec($info);
        curl_close($info);
        return $output;
    }
    /**
     * 个人中心
     * @author:tangp
     * @date:2018-08-28
     */
    public function myCenter()
    {
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;  //多语言商品
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;  //所选的站点id
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $langData = array(
            $this->langData->project->pending_payment,
            $this->langData->project->pending_delivery,
            $this->langData->project->pending_collect_goods,
            $this->langData->project->to_be_evaluated,
            $this->langData->project->return_goods,
            $this->langData->project->after_sale,
            $this->langData->project->my_order,
            $this->langData->project->my_sharing_code,
            $this->langData->project->information,
            $this->langData->project->receiving_address,
            $this->langData->project->collection_of_articles,
            $this->langData->project->collection_of_stores,
            $this->langData->project->collection_of_goods,
            $this->langData->project->my_recommend,
            $this->langData->project->my_coupon,
            $this->langData->project->my_tracks,
            $this->langData->project->personal_score,
            $this->langData->project->my_distribution,
            $this->langData->project->security_center,
            $this->langData->project->look_more_order,
            $this->langData->project->change_cell_phone_number,
            '充值中心'
        );
        $userId = $this->userId;
        $sql = "select username,nickname,headimgurl,point,amount,id,order_url,phone from " . DB_PREFIX . "user where id=" . $userId;
        $res = $this->userMod->querySql($sql);
        $sql1 = "select * from " . DB_PREFIX . "fx_user where user_id=".$userId;
        $ress = $this->fxUserMod->querySql($sql1);
        $data = array(
            'listData' => $res,
            'langData' => $langData,
            'ress'     => $ress
        );
        $this->setData($data,1,'');
    }
    /**
     * 全部订单
     * @author:tangp
     * @date:2018-08-28
     */
    public function orderIndex()
    {
        $userId = $this->userId;
        $this->assign('symbol', $this->symbol);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;  //所选的站点id
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;  //多语言商品
        $langData = array(
            $this->langData->public->whole,
            $this->langData->project->pending_payment,
            $this->langData->project->pending_delivery,
            $this->langData->project->pending_collect_goods,
            $this->langData->project->to_be_evaluated,
            $this->langData->project->immediately_payment,
            $this->langData->project->cancel_order,
            $this->langData->public->total,
            $this->langData->project->include_freight,
            $this->langData->project->product_spec,
            $this->langData->project->total_start,
            $this->langData->project->total_end
        );
        $orderBy = " order by id desc ";
        $userOrder = &m('userOrder');
        $sql = "SELECT order_sn,store_id FROM bs_user_order WHERE user_id = {$userId}{$orderBy}";
        $res = $userOrder->querySql($sql);
        $data = array();
        foreach ($res as $k => $v) {
            $dataSql = "SELECT * FROM bs_order_{$v['store_id']} WHERE order_sn = {$v['order_sn']} and  mark =1";
            $info = $userOrder->querySql($dataSql);
            foreach ($info as $k1 =>$v1){
                $num_sql = "select count(*) as num from " . DB_PREFIX . "order_goods where order_id = '{$v1['order_sn']}'"  ;
                $num = $this->orderGoodsMod->querySql($num_sql);
                $info[$k1]['num'] = $num[0]['num'];
                $sqls = "select o.*,o.goods_id as ogoods_id,l.* from "
                    . DB_PREFIX . "order_goods as o left join "
                    . DB_PREFIX . "store_goods as s on o.goods_id = s.id left join "
                    . DB_PREFIX . "goods_lang as l  on s.goods_id = l.goods_id "
                    . " where o.order_id='{$v1['order_sn']}' and l.lang_id = {$lang} ";
                $list = $this->orderGoodsMod->querySql($sqls);
                $info[$k1]['goods_list'] = $list;
                foreach ($list as $k2 => $v2) {
                    if ($v2['spec_key']) {
                        $k_info = $this->get_spec($v2['spec_key'], $lang);
                        foreach ($k_info as $k5 => $v5) {
                            $list[$k2]['spec_key_name'] = $v['item_name'];
                        }
                    }
                }
                $info[$k1]['statusName'] = $this->orderMod->getOrderStatusName($v1['sendout'], $v1['order_state'], $v1['evaluation_state']);
                $info[$k1]['storeName'] = &m('store')->getNameById($v1['store_id'],$lang);
                $sqlss = "SELECT shipping_fee FROM bs_order_details_{$v['store_id']} WHERE order_id = {$v1['id']}";
                $shipping = $userOrder->querySql($sqlss);
                $info[$k1]['shipping_fee'] = $shipping[0]['shipping_fee'];
                $invoice = "SELECT * FROM bs_order_invoice WHERE order_sn = {$v['order_sn']}";
                $rr = $userOrder->querySql($invoice);
                if ($rr){
                    $info[$k1]['invoice_status'] = 1;
                }else{
                    $info[$k1]['invoice_status'] = 0;
                }
            }
            if ($info){
                $data[] = $info;
            }
        }

        $da = array(
            'langData' => $langData,
            'listData' => $data
        );
        $this->setData($da,1,'');
    }
    /**
     * 获取商品规格
     * @param $goods_id|商品id  $type=1 读取商品原有规格属性  2 读取区域编辑后的规格属性
     * @return array
     */
    public function get_spec($k, $lang) {
        $storeGoodMod = &m("storeGoodItemPrice");
        $k = str_replace('_', ',', $k);
        $sql4 = "SELECT a.`order`,b.*,al.spec_name,bl.`item_name`  FROM " . DB_PREFIX . "goods_spec AS a
                     INNER JOIN " . DB_PREFIX . "goods_spec_item AS b ON a.id = b.spec_id
                     LEFT JOIN " . DB_PREFIX . "goods_spec_lang AS al ON a.id=al.spec_id
                     LEFT JOIN " . DB_PREFIX . "goods_spec_item_lang as bl ON b.id=bl.item_id
                     WHERE b.id IN($k) and al.lang_id=" . $lang . " and bl.lang_id=" . $lang . " ORDER BY b.id";
        $filter_spec2 = $storeGoodMod->querySql($sql4);
        return $filter_spec2;
    }
    /**
     * 待付款订单
     * @author:tangp
     * @date:2018-08-28
     */
    public function orderPayment()
    {
        $userId = $this->userId;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;   //所选的站点id
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;  //多语言商品
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $langData = array(
            $this->langData->public->whole,
            $this->langData->project->pending_payment,
            $this->langData->project->pending_delivery,
            $this->langData->project->pending_collect_goods,
            $this->langData->project->to_be_evaluated,
            $this->langData->project->immediately_payment,
            $this->langData->project->cancel_order,
            $this->langData->public->total,
            $this->langData->project->include_freight,
            $this->langData->project->product_spec,
            $this->langData->project->total_start,
            $this->langData->project->total_end
        );
        $orderBy = " order by id desc ";
        $userOrderMod = &m('userOrder');
        $dataSql = "SELECT * FROM bs_user_order WHERE user_id = {$userId}{$orderBy}";
        $res = $userOrderMod->querySql($dataSql);
        $data = array();
        foreach ($res as $k => $v){
            $sql = "select * from bs_order_{$v['store_id']} where order_sn ={$v['order_sn']} and order_state = 10 and mark = 1";
            $info = $userOrderMod->querySql($sql);
            foreach ($info as $kk => $vv){
                $num_sql = "select count(*) as num from " . DB_PREFIX . "order_goods where order_id = '{$vv['order_sn']}'"  ;
                $num = $this->orderGoodsMod->querySql($num_sql);
                $info[$kk]['num'] = $num[0]['num'];
                $sqls = "select o.*,o.goods_id as ogoods_id,l.* from "
                    . DB_PREFIX . "order_goods as o left join "
                    . DB_PREFIX . "store_goods as s on o.goods_id = s.id left join "
                    . DB_PREFIX . "goods_lang as l  on s.goods_id = l.goods_id "
                    . " where o.order_id='{$vv['order_sn']}' and l.lang_id = {$lang} ";
                $list = $this->orderGoodsMod->querySql($sqls);
                $info[$kk]['goods_list'] = $list;
                foreach ($list as $k2 => $v2){
                    if ($v2['spec_key']) {
                        $k_info = $this->get_spec($v2['spec_key'], $lang);
                        foreach ($k_info as $k5 => $v5) {
                            $list[$k2]['spec_key_name'] = $v['item_name'];
                        }
                    }
                }
                $info[$kk]['statusName'] = $this->orderMod->getOrderStatusName($vv['sendout'], $vv['order_state'], $vv['evaluation_state']);
                $info[$kk]['storeName'] = &m('store')->getNameById($vv['store_id'],$lang);
                $sqlss = "SELECT shipping_fee FROM bs_order_details_{$v['store_id']} WHERE order_id = {$vv['id']}";
                $shipping = $userOrderMod->querySql($sqlss);
                $info[$kk]['shipping_fee'] = $shipping[0]['shipping_fee'];
            }
            if ($info){
                $data[] = $info;
            }
        }

        $da = array(
            'langData' => $langData,
            'listData' => $data
        );
        $this->setData($da,1,'');
    }

    /**
     * 待发货订单
     * @author:tangp
     * @date:2018-08-28
     */
    public function orderHair()
    {
        $userId = $this->userId;
        $this->assign('symbol', $this->symbol);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;   //所选的站点id
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;  //多语言商品
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $this->assign('latlon', $latlon);
        $langData = array(
            $this->langData->public->whole,
            $this->langData->project->pending_payment,
            $this->langData->project->pending_delivery,
            $this->langData->project->pending_collect_goods,
            $this->langData->project->to_be_evaluated,
            $this->langData->project->immediately_payment,
            $this->langData->project->cancel_order,
            $this->langData->public->total,
            $this->langData->project->include_freight,
            $this->langData->project->product_spec,
            $this->langData->project->total_start,
            $this->langData->project->total_end
        );
        $orderBy = " order by id desc ";
        $userOrder = &m('userOrder');
        $sql = "SELECT order_sn,store_id FROM bs_user_order WHERE user_id = {$userId}{$orderBy}";
        $res = $userOrder->querySql($sql);
        $data = array();
        foreach ($res as $k => $v){
            $dataSql = "SELECT * FROM bs_order_{$v['store_id']} WHERE order_sn = {$v['order_sn']} and order_state in(20,25) AND mark =1";
            $info = $userOrder->querySql($dataSql);
            foreach ($info as $k1 => $v1){
                $num_sql = "select count(*) as num from " . DB_PREFIX . "order_goods where order_id = '{$v1['order_sn']}'"  ;
                $num = $this->orderGoodsMod->querySql($num_sql);
                $info[$k1]['num'] = $num[0]['num'];
                $sqls = "select o.*,o.goods_id as ogoods_id,l.* from "
                    . DB_PREFIX . "order_goods as o left join "
                    . DB_PREFIX . "store_goods as s on o.goods_id = s.id left join "
                    . DB_PREFIX . "goods_lang as l  on s.goods_id = l.goods_id "
                    . " where o.order_id='{$v1['order_sn']}' and l.lang_id = {$lang} ";
                $list = $this->orderGoodsMod->querySql($sqls);
                $info[$k1]['goods_list'] = $list;
                foreach ($list as $k2 => $v2) {
                    if ($v2['spec_key']) {
                        $k_info = $this->get_spec($v2['spec_key'], $lang);
                        foreach ($k_info as $k5 => $v5) {
                            $list[$k2]['spec_key_name'] = $v['item_name'];
                        }
                    }
                }
                $info[$k1]['statusName'] = $this->orderMod->getOrderStatusName($v1['sendout'], $v1['order_state'], $v1['evaluation_state']);
                $info[$k1]['storeName'] = &m('store')->getNameById($v1['store_id'],$lang);
                $sqlss = "SELECT shipping_fee FROM bs_order_details_{$v['store_id']} WHERE order_id = {$v1['id']}";
                $shipping = $userOrder->querySql($sqlss);
                $info[$k1]['shipping_fee'] = $shipping[0]['shipping_fee'];
            }
            if ($info){
                $data[] = $info;
            }
        }

        $da = array(
            'langData' => $langData,
            'listData' => $data
        );
        $this->setData($da,1,'');
    }
    /**
     * 待收货订单
     * @author:tangp
     * @date:2018-08-28
     */
    public function orderCollect()
    {
        $userId = $this->userId;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;   //所选的站点id
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;  //多语言商品
        $langData = array(
            $this->langData->public->whole,
            $this->langData->project->pending_payment,
            $this->langData->project->pending_delivery,
            $this->langData->project->pending_collect_goods,
            $this->langData->project->to_be_evaluated,
            $this->langData->project->immediately_payment,
            $this->langData->project->cancel_order,
            $this->langData->public->total,
            $this->langData->project->include_freight,
            $this->langData->project->product_spec,
            $this->langData->project->total_start,
            $this->langData->project->total_end
        );
        $orderBy = " order by id desc ";
        $userOrder = &m('userOrder');
        $sql = "SELECT order_sn,store_id FROM bs_user_order WHERE user_id = {$userId}{$orderBy}";
        $res = $userOrder->querySql($sql);
        $data = array();
        foreach ($res as $k => $v){
            $dataSql = "SELECT * FROM bs_order_{$v['store_id']} WHERE order_sn = {$v['order_sn']} and order_state in(30,40) AND mark =1";
            $info = $userOrder->querySql($dataSql);
            foreach ($info as $k1 => $v1){
                $num_sql = "select count(*) as num from " . DB_PREFIX . "order_goods where order_id = '{$v1['order_sn']}'"  ;
                $num = $this->orderGoodsMod->querySql($num_sql);
                $info[$k1]['num'] = $num[0]['num'];
                $sqls = "select o.*,o.goods_id as ogoods_id,l.* from "
                    . DB_PREFIX . "order_goods as o left join "
                    . DB_PREFIX . "store_goods as s on o.goods_id = s.id left join "
                    . DB_PREFIX . "goods_lang as l  on s.goods_id = l.goods_id "
                    . " where o.order_id='{$v1['order_sn']}' and l.lang_id = {$lang} ";
                $list = $this->orderGoodsMod->querySql($sqls);
                $info[$k1]['goods_list'] = $list;
                foreach ($list as $k2 => $v2) {
                    if ($v2['spec_key']) {
                        $k_info = $this->get_spec($v2['spec_key'], $lang);
                        foreach ($k_info as $k5 => $v5) {
                            $list[$k2]['spec_key_name'] = $v['item_name'];
                        }
                    }
                }
                $info[$k1]['statusName'] = $this->orderMod->getOrderStatusName($v1['sendout'], $v1['order_state'], $v1['evaluation_state']);
                $info[$k1]['storeName'] = &m('store')->getNameById($v1['store_id'],$lang);
                $sqlss = "SELECT shipping_fee FROM bs_order_details_{$v['store_id']} WHERE order_id = {$v1['id']}";
                $shipping = $userOrder->querySql($sqlss);
                $info[$k1]['shipping_fee'] = $shipping[0]['shipping_fee'];
            }
            if ($info){
                $data[] = $info;
            }
        }
        $da = array(
            'langData' => $langData,
            'listData' => $data
        );
        $this->setData($da,1,'');
    }
    /**
     * 待评价订单
     * @author:tangp
     * @date:2018-08-28
     */
    public function orderEvaluate()
    {
        $userId = $this->userId;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;   //所选的站点id
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;  //多语言商品
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $langData = array(
            $this->langData->public->whole,
            $this->langData->project->pending_payment,
            $this->langData->project->pending_delivery,
            $this->langData->project->pending_collect_goods,
            $this->langData->project->to_be_evaluated,
            $this->langData->project->immediately_payment,
            $this->langData->project->cancel_order,
            $this->langData->public->total,
            $this->langData->project->include_freight,
            $this->langData->project->product_spec,
            $this->langData->project->total_start,
            $this->langData->project->total_end
        );
        $orderBy = " order by id desc ";
        $userOrderMod = &m('userOrder');
        $dataSql = "SELECT * FROM bs_user_order WHERE user_id = {$userId}{$orderBy}";
        $res = $userOrderMod->querySql($dataSql);
        $data = array();
        foreach ($res as $k => $v){
            $sql = "select * from bs_order_{$v['store_id']} where order_sn ={$v['order_sn']} and order_state = 50 AND evaluation_state = 0 and mark = 1";
            $info = $userOrderMod->querySql($sql);
            foreach ($info as $k1 => $v1){
                $num_sql = "select count(*) as num from " . DB_PREFIX . "order_goods where order_id = '{$v1['order_sn']}'"  ;
                $num = $this->orderGoodsMod->querySql($num_sql);
                $info[$k1]['num'] = $num[0]['num'];
                $sqls = "select o.*,o.goods_id as ogoods_id,l.* from "
                    . DB_PREFIX . "order_goods as o left join "
                    . DB_PREFIX . "store_goods as s on o.goods_id = s.id left join "
                    . DB_PREFIX . "goods_lang as l  on s.goods_id = l.goods_id "
                    . " where o.order_id='{$v1['order_sn']}' and l.lang_id = {$lang} ";
                $list = $this->orderGoodsMod->querySql($sqls);
                $info[$k1]['goods_list'] = $list;
                foreach ($list as $k2 => $v2) {
                    if ($v2['spec_key']) {
                        $k_info = $this->get_spec($v2['spec_key'], $lang);
                        foreach ($k_info as $k5 => $v5) {
                            $list[$k2]['spec_key_name'] = $v['item_name'];
                        }
                    }
                }
                $info[$k1]['statusName'] = $this->orderMod->getOrderStatusName($v1['sendout'], $v1['order_state'], $v1['evaluation_state']);
                $info[$k1]['storeName'] = &m('store')->getNameById($v1['store_id'],$lang);
                $sqlss = "SELECT shipping_fee FROM bs_order_details_{$v['store_id']} WHERE order_id = {$v1['id']}";
                $shipping = $userOrderMod->querySql($sqlss);
                $info[$k1]['shipping_fee'] = $shipping[0]['shipping_fee'];
                $invoice = "SELECT * FROM bs_order_invoice WHERE order_sn = {$v['order_sn']}";
                $rr = $userOrderMod->querySql($invoice);
                if ($rr){
                    $info[$k1]['invoice_status'] = 1;
                }else{
                    $info[$k1]['invoice_status'] = 0;
                }
            }
            if ($info){
                $data[] = $info;
            }
        }
        $da = array(
            'langData' => $langData,
            'listData' => $data
        );
        $this->setData($da,1,'');
    }
    /**
     * 退款售后
     * @author:tangp
     * @date:2018-08-28
     */
    public function orderRefund()
    {
        $userId = $this->userId;
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;  //多语言商品
        $langData = array(
            $this->langData->project->return_goods,
            $this->langData->project->after_sale,
        );
        $orderBy = " order by id desc ";
        $userOrder = &m('userOrder');
        $sql = "SELECT order_sn,store_id FROM bs_user_order WHERE user_id = {$userId}{$orderBy}";
        $res = $userOrder->querySql($sql);
        $data = array();
        foreach ($res as $k => $v){
            $dataSql = "SELECT * FROM bs_order_{$v['store_id']} WHERE order_sn = {$v['order_sn']} and order_state in(60,70) AND mark =1";
            $info = $userOrder->querySql($dataSql);
            foreach ($info as $k1 => $v1){
                $num_sql = "select count(*) as num from " . DB_PREFIX . "order_goods where order_id = '{$v1['order_sn']}'"  ;
                $num = $this->orderGoodsMod->querySql($num_sql);
                $info[$k1]['num'] = $num[0]['num'];
                $sqls = "select o.*,o.goods_id as ogoods_id,l.* from "
                    . DB_PREFIX . "order_goods as o left join "
                    . DB_PREFIX . "store_goods as s on o.goods_id = s.id left join "
                    . DB_PREFIX . "goods_lang as l  on s.goods_id = l.goods_id "
                    . " where o.order_id='{$v1['order_sn']}' and l.lang_id = {$lang} ";
                $list = $this->orderGoodsMod->querySql($sqls);
                $info[$k1]['goods_list'] = $list;
                foreach ($list as $k2 => $v2) {
                    if ($v2['spec_key']) {
                        $k_info = $this->get_spec($v2['spec_key'], $lang);
                        foreach ($k_info as $k5 => $v5) {
                            $list[$k2]['spec_key_name'] = $v['item_name'];
                        }
                    }
                }
                $info[$k1]['statusName'] = $this->orderMod->getOrderStatusName($v1['sendout'], $v1['order_state'], $v1['evaluation_state']);
                $info[$k1]['storeName'] = &m('store')->getNameById($v1['store_id'],$lang);
            }
            if ($info){
                $data[] = $info;
            }
        }
        $da = array(
            'langData' => $langData,
            'listData' => $data
        );
        $this->setData($da,1,'');
    }

    /**
     * 我的足迹
     * @author:tangp
     * @date:2018-08-28
     */
    public function footPrint()
    {
        $userId = $this->userId;
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;  //所选的站点id
        $where = ' f.user_id =' . $userId . ' and f.store_good_id =g.id';
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;  //多语言
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $langData = array(
            $this->langData->project->my_tracks,
            $this->langData->public->administration
        );
        //列表页数据
        // $sql = 'select distinct f.*,g.*,l.*,f.id,l.goods_name,gl.original_img,f.store_good_id  from '
        //     . DB_PREFIX . 'user_footprint as f inner join '
        //     . DB_PREFIX . 'store_goods as g on f.good_id = g.goods_id inner join '
        //     . DB_PREFIX . 'goods_lang as l on l.goods_id = g.goods_id and l.lang_id=' . $lang . '  left join '
        //     . DB_PREFIX . 'goods as gl on g.goods_id = gl.goods_id  where '
        //     . $where . ' and g.mark = 1  and f.store_id =' . $storeid .
        //     ' group by f.good_id order by f.adds_time desc limit 0, 8 ';
        $sql = 'select f.id,g.goods_name,g.market_price,g.shop_price,g.original_img  from '
            . DB_PREFIX . 'user_footprint as f inner join '
            . DB_PREFIX . 'store_goods as g on f.good_id = g.goods_id inner join '
            . DB_PREFIX . 'goods_lang as l on l.goods_id = g.goods_id and l.lang_id=' . $lang . '  left join '
            . DB_PREFIX . 'goods as gl on g.goods_id = gl.goods_id  where '
            . $where . ' and g.mark = 1  and f.store_id =' . $storeid .
            ' group by f.good_id order by f.adds_time desc limit 0, 8 ';

        $data = $this->footPrintMod->querySql($sql);
        $da = array(
            'langData' => $langData,
            'listData' => $data
        );
        $this->setData($da,1,'');
    }
    /**
     * 删除足迹
     * @author:tangp
     * @date:2018-08-28
     */
    public function DeletefootPrint()
    {
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;  //多语言
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;  //所选的站点id
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        if (empty($id)){
            $this->setData('',0,'请传入足迹id！');
        }
        $footPrintMod = &m("footprint");
        $res = $footPrintMod->doDrop($id);
        if ($res){
            $this->setData('',1,'删除成功');
        }else{
            $this->setData('',0,'删除失败');
        }
    }
    /**
     * 个人睿积分
     * @author:tangp
     * @date:2018-8-28
     */
    public function pointLog()
    {
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';

        $startTime = !empty($_REQUEST['start_time']) ? htmlspecialchars(trim($_REQUEST['start_time'])) : '';
        $endTime = !empty($_REQUEST['end_time']) ? htmlspecialchars(trim($_REQUEST['end_time'])) : '';

        $langData = array(
            $this->langData->project->my_tracks,
            $this->langData->project->my_integral,
            $this->langData->project->recharge_log,
            $this->langData->project->to_give,
            $this->langData->project->to_recharge,
            $this->langData->public->time,
            $this->langData->public->search,
            $this->langData->public->date,
            $this->langData->public->deposit_in,
            $this->langData->public->expenditure,
            $this->langData->project->information_summary
        );

        if (empty($startTime) && empty($endTime)) {
            $startTime = date('Y-m-d', strtotime('-7 days'));
            $endTime = date('Y-m-d');
        }
        if ($startTime && $endTime && ($startTime > $endTime)) {
            $temp = $startTime;
            $startTime = $endTime;
            $endTime = $temp;
        }
        $userId = $this->userId;
        //where条件
        $where = ' where 1=1 ';
        if ($this->lang == 1) {
            if (!empty($startTime)) {
                $where .= ' and  add_time >= ' . strtotime($startTime);
            }
            if (!empty($endTime)) {
                $where .= ' and  add_time < ' . (strtotime($endTime) + 3600 * 24);
            }
        } else {
            if (!empty($startTime)) {
                $where .= ' and  add_time >= ' . strtotime($startTime);
            }
            if (!empty($endTime)) {
                $where .= ' and  add_time < ' . (strtotime($endTime) + 3600 * 24);
            }
        }
        $uSql = "select point from " . DB_PREFIX . 'user where id=' . $userId;
        $uData = $this->userMod->querySql($uSql);

        //列表页数据
        $sql = ' select  *   from  ' . DB_PREFIX . 'point_log   ' . $where . ' AND userid=' . $userId . '  order by id desc ';
        $data = $this->pointLogMod->querySql($sql);
        $list = $data;

        $data = array(
            'langData'  => $langData,
            'startTime' => $startTime,
            'endTime'   => $endTime,
            'point'     => $uData[0]['point'],
            'list'      => $list
        );

        $this->setData($data,1,'');
    }
    /**
     * 个人睿积分详情
     * @author:tangp
     * @date:2018-08-28
     */
    public function details()
    {
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';

        $startTime = !empty($_REQUEST['start_time']) ? htmlspecialchars(trim($_REQUEST['start_time'])) : '';
        $endTime = !empty($_REQUEST['end_time']) ? htmlspecialchars(trim($_REQUEST['end_time'])) : '';

        $langData = array(
            $this->langData->project->detailed_information,
            $this->langData->public->date,
            $this->langData->public->deposit_in,
            $this->langData->public->expenditure,
            $this->langData->project->information_summary
        );
        if (empty($startTime) && empty($endTime)) {
            $startTime = date('Y-m-d', strtotime('-7 days'));
            $endTime = date('Y-m-d');
        }
        if ($startTime && $endTime && ($startTime > $endTime)) {
            $temp = $startTime;
            $startTime = $endTime;
            $endTime = $temp;
        }
        $userId = $this->userId;
        //where条件
        $where = ' where 1=1 ';
        if ($this->lang == 1) {
            if (!empty($startTime)) {
                $where .= ' and  add_time >= ' . strtotime($startTime);
            }
            if (!empty($endTime)) {
                $where .= ' and  add_time < ' . (strtotime($endTime) + 3600 * 24);
            }
        } else {
            if (!empty($startTime)) {
                $where .= ' and  add_time >= ' . strtotime($startTime);
            }
            if (!empty($endTime)) {
                $where .= ' and  add_time < ' . (strtotime($endTime) + 3600 * 24);
            }
        }
//        $uSql = "select point from " . DB_PREFIX . 'user where id=' . $userId;
//        $uData = $this->userMod->querySql($uSql);

        //列表页数据
        $sql = ' select  *   from  ' . DB_PREFIX . 'point_log   ' . $where . ' AND userid=' . $userId . '  order by id desc ';
        $data = $this->pointLogMod->querySqlPageData($sql);
        $list = $data['list'];

        $data = array(
            'langData'  => $langData,
            'list'      => $list
        );
        $this->setData($data,1,'');
    }
    /**
     * 赠送睿积分
     * @author:tangp
     * @date:2018-08-28
     */
    public function giveUserPoint()
    {
        $userId = $this->userId;
        $lang = !empty($_REQUEST['lan_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        $user_info = $this->userMod->getOne(array("cond" => "id=" . $userId));
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';

        $langData = array(
            $this->langData->project->give_integral,
            $this->langData->project->give_person,
            $this->langData->project->you_current_integral,
            $this->langData->project->ebter_phoneoremail,
            $this->langData->project->to_give
        );

        $data = array(
            'langData' => $langData
        );
        $this->setData($data,1,'');
    }
    /**
     * 处理赠送积分
     * @author:tangp
     * @date:2018-08-28
     */
    public function doGivePoint()
    {
        $userId = $this->userId;
//        $a = $this->langData;
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        $name = !empty($_REQUEST['user_name']) ? trim($_REQUEST['user_name']) : '';
        $point = !empty($_REQUEST['point']) ? trim($_REQUEST['point']) : '';
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $sql = "select * from " . DB_PREFIX . "user where phone = '" . $name . "' or email = '" . $name . "'";
        $res = $this->userMod->querySql($sql);
        //$receive_info = $this->userMod->getOne(array("cond"=>"phone =".$name." or email=".$name));
        $receive_info = $res[0];
        $give_info = $this->userMod->getOne(array("cond" => "id =" . $userId));
        if (empty($name)) {
            $this->setData(array(), $status = 0, '请填写赠送人手机号/邮箱');
        }
        if ($receive_info['id'] == $userId) {
            $this->setData(array(), $status = 0, '无效操作');
        }
        if (empty($receive_info)) {
            $this->setData(array(), $status = 0, '被赠送者不存在');
        }
        if (!preg_match("/^[1-9][0-9]*$/", $point)) {
            $this->setData(array(), $status = 0, '请输入整数');
        }
        if ($point > $give_info['point']) {
            $this->setData(array(), $status = 0, '睿积分余额不足');
        }
        $give_point = $give_info['point'] - $point;
        $give_arr = array(
            "point" => $give_point
        );
        $receive_arr = array(
            "point" => $receive_info['point'] + $point
        );
        $res = $this->userMod->doEdit($give_info['id'], $give_arr);
//        $this->addPointLog($give_info['username'], "赠予" . $receive_info['username'] . " " . $point . "睿积分", $give_info['id'], 0, $point);
        if ($res) {
            $res2 = $this->userMod->doEdit($receive_info['id'], $receive_arr);
//            $this->addPointLog($receive_info['username'], $give_info['username'] . "赠予" . $point . "睿积分", $receive_info['id'], $point, 0);
        }
        if ($res && $res2) {
            $this->setData(array(), $status = 1, '处理成功');
        } else {
            $this->setData(array(), $status = 0, '处理失败');
        }
    }
    public function addPointLog($username, $note, $userid, $deposit, $expend, $order_sn = null) {
        $logData = array(
            'operator' => '--',
            'username' => $username,
            'add_time' => time(),
            'deposit' => $deposit,
            'expend' => $expend,
            'note' => $note,
            'userid' => $userid
        );
        if ($order_sn) {
            $logData['order_sn'] = $order_sn;
        }
        $pointLogMod = &m("pointLog");
        $pointLogMod->doInsert($logData);
    }
    /**
     * 充值睿积分
     * @author:tangp
     * @date:2018-08-28
     */
    public function pointOrder()
    {
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $sql = "SELECT * FROM " . DB_PREFIX . 'user_point_site';
        $data = $this->pointMod->querySql($sql);

        $langData = array(
            $this->langData->project->recharge_integral,
            $this->langData->project->rmb_exchange,
            $this->langData->public->integral,
            $this->langData->project->amount_payable,
            $this->langData->project->to_recharge
        );

        $data = array(
            'langData' => $langData
        );

        $this->setData($data,1,'');
    }
    /**
     * 睿积分订单确认页面
     * @author:tangp
     * @date:2018-08-28
     */
    public function orderPoint()
    {
        $a = $this->langData;
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $rate = !empty($_REQUEST['rate']) ? intval($_REQUEST['rate']) : '0';
        $point_num = !empty($_REQUEST['point_num']) ? intval($_REQUEST['point_num']) : '0';
        $amount = number_format(($point_num / $rate), 2, '.', '');
        $userid = $this->userId ? $this->userId : $this->user_id_bank;
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';

        $rand = $this->buildNo(1);
        $orderNo = date('YmdHis') . $rand[0];
        if (empty($point_num)) {
            $this->setData($info = array(), $status = 0, $a['rui_num']);
        }
        if (!preg_match("/^[1-9][0-9]*$/", $point_num)) {
            $this->setData($info = array(), $status = 0, $a['rui_z']);
        }
        $orderData = array(
            'amount' => $amount,
            'point' => $point_num,
            'order_sn' => $orderNo,
            'status' => 0,
            'add_time' => time(),
            'buyer_id' => $userid
        );
        $res = $this->pointOrderMod->doInsert($orderData);
        if ($res) {
            $this->setData(array(), $status = 1, '提交订单成功，请往支付');
        } else {
            $this->setData($info = array(), $status = 0, '订单提交失败');
        }
    }
    //生成订单号
    public function buildNo($limit) {
        $begin = pow(10, 3);
        $end = (pow(10, 4) - 1);
        $rand_array = range($begin, $end);
        shuffle($rand_array); //调用现成的数组随机排列函数
        return array_slice($rand_array, 0, $limit); //截取前$limit个
    }
    /**
     * 判断是否为分销人员
     * @author tangp
     * @date 2018-10-24
     */
    public function is_fxuser()
    {
        $userId = $this->userId;
        $fxUserMod = &m('fxuser');
        $sql = "SELECT * FROM bs_fx_user WHERE user_id=".$userId;
        $res = $fxUserMod->querySql($sql);
        if (!empty($res)){
            $exist = 1;
            $data = array(
                'exist' => $exist
            );
            $this->setData($data,1,'是分销人员');
        }else{
            $exist = 0;
            $data = array(
                'exist' => $exist
            );
            $this->setData($data,0,'不是分销人员');
        }

    }
    /**
     * 判断分销人员的状态
     * @author tangp
     * @date 2018-10-26
     */
    public function getStatus()
    {
        $fxUserMod = &m('fxuser');
        $sql = "SELECT * FROM bs_fx_user WHERE user_id=".$this->userId;
        $res = $fxUserMod->querySql($sql);
        if ($res[0]['is_check'] == 1){
            $this->setData(array(),1,'正在审核中');
        }elseif ($res[0]['is_check'] == 3){
            $this->setData(array(),1,'审核失败');
        } elseif ($res[0]['status'] == 2){
            $this->setData(array(),1,'冻结');
        }elseif ($res[0]['is_check'] ==2 && $res[0]['status'] == 1){
            $this->setData(array(),1,'审核成功');
        }
    }
    /**
     * 我的分销首页
     * @author tangp
     * @date 2018-10-24
     */
    public function myFxIndex()
    {
        $fxUserMod = &m('fxuser');
        $fxOrderMod = &m('fxOrder');
        $fxOutmoneyApplyMod = &m('fxOutmoneyApply');
        $userid = $this->userId;
        $lang_id = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        //查询用户信息
        $sqlss = "select username,headimgurl from bs_user where id=" . $this->userId;
        $userInfo = $this->userMod->querySql($sqlss);
        //查出分销码
        $sql = "SELECT fx_code FROM bs_fx_user WHERE user_id =".$userid;
        $fxCode = $fxUserMod->querySql($sql);
        $sql3= "SELECT * FROM bs_fx_user WHERE user_id=".$userid;
        $info = $fxUserMod->querySql($sql3);
        $sqll = "SELECT SUM(apply_money) AS total FROM bs_fx_outmoney_apply WHERE fx_user_id={$info[0]['id']} AND is_check=2";
        $total = $fxOutmoneyApplyMod->querySql($sqll);
        if ($info[0]['level'] == 3){
            $sql = "SELECT fo.fx_discount,o.order_id,o.order_state,fs.level,fu.*,fo.add_time,o.goods_amount,fo.order_sn,fo.pay_money,o.order_state,o.order_amount FROM bs_fx_order AS fo
              LEFT JOIN bs_order AS o ON fo.order_id = o.order_id
              LEFT JOIN bs_fx_rule AS fu ON fo.rule_id = fu.id
              LEFT JOIN bs_fx_user AS fs ON fo.user_id = fs.user_id
              WHERE fo.fx_user_id={$info[0]['id']}";
            $data = $fxOrderMod->querySql($sql);

            foreach($data as $k=>$v){
                $data[$k]['fxmoney']=number_format($v['pay_money']*($v['lev3_prop']-$v['fx_discount'])/100,2,'.','');
            }
            $counts = count($data);
            $sum = 0;
            foreach($data as $item){
                $sum += $item['pay_money'];
            }
//            echo '<pre>';var_dump($counts);
            $sql2 = "SELECT o.order_id,o.order_state,fs.level,fu.*,fo.add_time,o.goods_amount,fo.order_sn,fo.pay_money,o.order_state,o.order_amount FROM bs_fx_order AS fo
              LEFT JOIN bs_order AS o ON fo.order_id = o.order_id
              LEFT JOIN bs_fx_rule AS fu ON fo.rule_id = fu.id
              LEFT JOIN bs_fx_user AS fs ON fo.user_id = fs.user_id
              WHERE fo.fx_user_id={$info[0]['id']} AND o.order_state=50";
            $data1 = $fxOrderMod->querySql($sql2);
            foreach($data1 as $k=>$v){
                $data1[$k]['fxmoney']=number_format($v['pay_money']*($v['lev3_prop']-$v['fx_discount'])/100,2,'.','');
            }
            $sums=0;
            foreach($data1 as $item){
                $sums += $item['fxmoney'];
            }
//            $outSql = "SELECT * FROM bs_fx_outmoney_apply WHERE fx_user_id={$info[0]['id']} AND is_check=2";
//            $out = $fxOutmoneyApplyMod->querySql($outSql);
//            if (!empty($out)){
//                $apply_money = 0;
//                foreach ($out as $v){
//                    $apply_money += $v['apply_money'];
//                }
//                $d = array(
//                    'monery' => $sums - $apply_money
//                );
//                $fxUserMod->doEdit($info[0]['id'],$d);
//            }else{
//                if ($sums !== 0) {
//                    $d = array(
//                        'monery' => $sums
//                    );
//                    $fxUserMod->doEdit($info[0]['id'],$d);
//                }
//            }
        }elseif ($info[0]['level'] == 2){
            //查出该二级用户下的三级用户的订单
            $sss = "select id from bs_fx_user where parent_id={$info[0]['id']} and level=3";
            $result =$fxUserMod->querySql($sss);
//            var_dump($result);die;
            foreach ($result as $v){
                $ids[] = $v['id'];
            }
            $two_ids    = implode(',',$ids);
            $sql = "select fu.level,fr.lev1_prop,fr.lev2_prop,fr.lev3_prop,o.goods_amount,fo.pay_money,o.order_state,fo.add_time,fo.order_id,fo.order_sn from bs_fx_order as fo
                  left join bs_order as o on fo.order_id = o.order_id
                  left join bs_fx_user as fu on fu.id = fo.fx_user_id
                  left join bs_fx_rule as fr on fo.rule_id = fr.id where fx_user_id in ({$two_ids})";
            $data = $fxOrderMod->querySql($sql);

            foreach ($data as $k => $v){
                $prop = $data[$k]['lev2_prop']/100;
                $data[$k]['fxmoney'] = number_format($data[$k]['pay_money'] * $prop,2,'.','');
            }
            $counts = count($data);
//            echo '<pre>';var_dump($counts);
            $sum = 0;
            foreach($data as $item){
                $sum += $item['pay_money'];
            }
            $sql2 ="select fu.level,fr.lev1_prop,fr.lev2_prop,fr.lev3_prop,o.goods_amount,fo.pay_money,o.order_state,fo.add_time,fo.order_id,fo.order_sn from bs_fx_order as fo
                  left join bs_order as o on fo.order_id = o.order_id
                  left join bs_fx_user as fu on fu.id = fo.fx_user_id
                  left join bs_fx_rule as fr on fo.rule_id = fr.id where fx_user_id in ({$two_ids}) AND o.order_state=50";
            $data1 = $fxOrderMod->querySql($sql2);
            foreach ($data1 as $k => $v){
                $prop = $data1[$k]['lev2_prop']/100;
                $data1[$k]['fxmoney'] = number_format($data1[$k]['pay_money'] * $prop,2,'.','');
            }
            $sums = 0;
            foreach($data1 as $item){
                $sums += $item['fxmoney'];
            }
//            $outSql = "SELECT * FROM bs_fx_outmoney_apply WHERE fx_user_id={$info[0]['id']} AND is_check=2";
//            $out = $fxOutmoneyApplyMod->querySql($outSql);
//            if (!empty($out)){
//                $apply_money = 0;
//                foreach ($out as $v){
//                    $apply_money += $v['apply_money'];
//                }
//                $d = array(
//                    'monery' => $sums - $apply_money
//                );
//                $fxUserMod->doEdit($info[0]['id'],$d);
//            }else{
//                if ($sums !== 0) {
//                    $d = array(
//                        'monery' => $sums
//                    );
//                    $fxUserMod->doEdit($info[0]['id'],$d);
//                }
//            }
//            var_dump($sum);
        }elseif ($info[0]['level'] == 1){
            //查出该用户的二级
            $s = "select id from bs_fx_user where parent_id={$info[0]['id']} and level=2";
            $ss = $fxUserMod->querySql($s);
            foreach($ss as $k=>$v){
                $secondFxUserIdData[]=$v['id'];
            }
            $secondFxUserIds=implode(',',$secondFxUserIdData);
            $sql5 = "select id from bs_fx_user where parent_id in (".$secondFxUserIds.") and level=3";
            $res = $fxUserMod->querySql($sql5);
            foreach($res as $k=>$v){
                $thirdFxUserIdData[]=$v['id'];
            }
            $thirdFxUserIds=implode(',',$thirdFxUserIdData);
            $sql6 = "select fu.level,fr.lev1_prop,fr.lev2_prop,fr.lev3_prop,o.goods_amount,fo.pay_money,o.order_state,fo.add_time,fo.order_id,fo.order_sn from bs_fx_order as fo
                      left join bs_order as o on fo.order_id = o.order_id
                      left join bs_fx_user as fu on fu.id = fo.fx_user_id
                      left join bs_fx_rule as fr on fo.rule_id = fr.id where fx_user_id in (".$thirdFxUserIds.")";
            $data = $fxOrderMod->querySql($sql6);
            foreach($data as $k=>$v){
                $data[$k]['fxmoney']=number_format($v['order_amount']*$v['lev1_prop']/100,2,'.','');
            }
            $counts = count($data);
            $sum = 0;
            foreach($data as $item){
                $sum += $item['pay_money'];
            }
            $sql7 = "select fu.level,fr.lev1_prop,fr.lev2_prop,fr.lev3_prop,o.goods_amount,fo.pay_money,o.order_state,fo.add_time,fo.order_id,fo.order_sn from bs_fx_order as fo
                      left join bs_order as o on fo.order_id = o.order_id
                      left join bs_fx_user as fu on fu.id = fo.fx_user_id
                      left join bs_fx_rule as fr on fo.rule_id = fr.id where fx_user_id in (".$thirdFxUserIds.") AND o.order_state=50";
            $data1 = $fxOrderMod->querySql($sql7);
            foreach($data1 as $k=>$v){
                $data1[$k]['fxmoney']=number_format($v['order_amount']*$v['lev1_prop']/100,2,'.','');
            }
            $sums = 0;
            foreach($data1 as $item){
                $sums += $item['fxmoney'];
            }
//            $outSql = "SELECT * FROM bs_fx_outmoney_apply WHERE fx_user_id={$info[0]['id']} AND is_check=2";
//            $out = $fxOutmoneyApplyMod->querySql($outSql);
//            if (!empty($out)){
//                $apply_money = 0;
//                foreach ($out as $v){
//                    $apply_money += $v['apply_money'];
//                }
//                $d = array(
//                    'monery' => $sums - $apply_money
//                );
//                $fxUserMod->doEdit($info[0]['id'],$d);
//            }else{
//                if ($sums !== 0) {
//                    $d = array(
//                        'monery' => $sums
//                    );
//                    $fxUserMod->doEdit($info[0]['id'],$d);
//                }
//            }
        }
        $fx_tree = $fxUserMod->getOne(array("cond" => "user_id=" . $userid));
        $count_user = $fxUserMod->countUser($fx_tree['id'],$fx_tree['level']);

        $langData = array(
            $this->langData->project->application_cash,
            $this->langData->project->commissions_available,
            $this->langData->project->accumulative_presentation,
            $this->langData->project->application_log,
            $this->langData->project->my_order,
            $this->langData->project->order_sn,
            $this->langData->public->pair,
            $this->langData->project->accumulative_amount,
            $this->langData->project->my_team,
            $this->langData->public->peoples
        );
        $listData = array(
            'counts'     => $counts,
            'sum'        => $sum,
            'sums'       => $d['monery'],
            'total'      => $total,
            'fxCode'     => $fxCode,
            'userInfo'   => $userInfo,
            'fx_user'    => $fx_tree,
            'count_user' => $count_user
        );
        $da = array(
            'langData' => $langData,
            'listData' => $listData
        );
        $this->setData($da,1,'');
    }
    public function fxUser() {
        $fxuserMod = &m('fxuser');
        $fxUserAccountMod = &m('fxUserAccount');
        $id = !empty($_REQUEST['id']) ? htmlspecialchars(trim($_REQUEST['id'])) : '0';
        $info     = $fxuserMod  ->getRow($id);
        $data = $fxUserAccountMod->checkUserAccount($id);
        $info['count'] = count($data);//会员数量
        $da = array(
            'info' => $info,
            'data' => $data,
        );
        $this->setData($da,1,'');
    }
    /**
     * 申请成为分销人员
     * @author:tangp
     * @date:2018-08-28
     */
    public function apply()
    {
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;  //多语言商品
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;  //所选的站点id
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';

        $langData = array(
            $this->langData->project->application_for_distribution,
            $this->langData->public->real_name,
            $this->langData->public->phone,
            $this->langData->project->referral_discode,
            $this->langData->project->open_bank,
            $this->langData->project->bank_account,
            $this->langData->public->save,
            $this->langData->public->cancel
        );

        $data = array(
            'langData' => $langData
        );
        $this->setData($data,1,'');

    }
    /**
     * 执行申请成为分销人员
     * @author:tangp
     * @date:2018-08-28
     */
    public function doapply()
    {
        $fxUserMod = &m('fxuser');
        $storeMod  = &m('store');
        $real_name = !empty($_REQUEST['real_name']) ? $_REQUEST['real_name'] : '';
        $telephone = !empty($_REQUEST['telephone']) ? $_REQUEST['telephone'] : '';
        $tj_code   = !empty($_REQUEST['fx_code']) ? $_REQUEST['fx_code'] : '';
        $bank_name = !empty($_REQUEST['bank_name']) ? $_REQUEST['bank_name'] : '';
        $bank_account = !empty($_REQUEST['bank_account']) ? $_REQUEST['bank_account'] : '';
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        if (empty($real_name)){
            $this->setData(array(),'0','真实姓名必填！');
        }
        if (empty($telephone)){
            $this->setData(array(),'0','手机号必填！');
        }
        if (!preg_match('/^1[34578]\d{9}$/', $telephone)) {
            $this->setData(array(), '0', '请输入正确的手机号！');
        }
        //验证手机号的唯一性（add）
        if ($this->getPhoneInfo($telephone)) {
            $this->setData(array(), '0', '手机号已存在！');
        }
        if (empty($bank_name)) {
            $this->setData(array(), '0', '开户银行必填！');
        }
        if (empty($bank_account)){
            $this->setData(array(),'0','银行账号必填！');
        }
        if (empty($tj_code)){
            $this->setData(array(),'0','推荐人分销码必填！');
        }
        $fx_info = $fxUserMod->getOne(array("cond" => "fx_code='" . $tj_code . "'"));
        if (empty($fx_info)){
            $this->setData(array(),'0','您写的分销码不存在！');
        }else{
            $sql = "select * from bs_fx_user where user_id=".$this->userId;
            $info = $fxUserMod->querySql($sql);
            if (!empty($info)){
                $this->setData(array(),'0','已存在该用户！');
            }
            $user_info = $this->userMod->getOne(array("cond" => "id=" . $this->userId));
            $store_info = $storeMod->getOne(array("cond"=>"id=".$store_id));
            if ($fx_info['level'] == 1){
                $data = array(
                    'parent_id'    => $fx_info['id'],
                    'level'        => 2,
                    'rule_id'      => $fx_info['rule_id'],
                    'user_id'      => $this->userId,
                    'real_name'    => $real_name,
                    'phone'        => $telephone,
                    'fx_code'      => $this->unique_rand(100000,999999,1),
                    'bank_name'    => $bank_name,
                    'bank_account' => $bank_account,
                    'store_cate'   => $store_info['store_cate_id'],
                    'store_id'     => $fx_info['store_id'],
                    'status'       => 1,
                    'is_check'     => 1,
                    'source'       => 4,
                    'discount'     => 0,
                    'add_time'     => time(),
                    'add_user'     => $this->userId,
                    'mark'         => 1,
                    'email'        => $user_info['email']
                );
                $result = $fxUserMod->doInsert($data);
                if ($result){
                    $this->setData(array(),'1','申请成功!请等待管理员审核！');
                }else{
                    $this->setData(array(),'0','申请失败！');
                }
            }elseif ($fx_info['level'] == 2){
                $data = array(
                    'parent_id'    => $fx_info['id'],
                    'level'        => 3,
                    'rule_id'      => $fx_info['rule_id'],
                    'user_id'      => $this->userId,
                    'real_name'    => $real_name,
                    'phone'        => $telephone,
                    'fx_code'      => $this->unique_rand(100000,999999,1),
                    'bank_name'    => $bank_name,
                    'bank_account' => $bank_account,
                    'store_cate'   => $store_info['store_cate_id'],
                    'store_id'     => $fx_info['store_id'],
                    'status'       => 1,
                    'is_check'     => 1,
                    'source'       => 4,
                    'discount'     => 0,
                    'add_time'     => time(),
                    'add_user'     => $this->userId,
                    'mark'         => 1,
                    'email'        => $user_info['email']
                );
                $result = $fxUserMod->doInsert($data);
                if ($result){
                    $this->setData(array(),'1','申请成功!请等待管理员审核！');
                }else{
                    $this->setData(array(),'0','申请失败！');
                }
            }elseif ($fx_info['level'] == 3){
                $this->setData(array(),'0','您写的分销码有误！');
            }
        }
    }
    /**
     * 优惠分销申请
     * @author tangp
     * @date 2018-10-25
     */
    public function disApply()
    {
        $fxUserMod = &m('fxuser');
        $fxruleMod = &m('fxrule');
        $disMod = &m('fxDiscountLog');
        $userid = $this->userId;
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        $fx_info = $fxUserMod->getOne(array("cond" => "user_id=" . $userid));
        //判断是否有正在审核的申请
        $is_check = $disMod->haveCheck($fx_info['id']);
        if ($is_check){
            $this->setData(array(),0,'您有正在审核的申请！');
        }
        //查询分销人员分润比例
        $getLev3Percent = $fxruleMod->getLev3Percent($fx_info['id']);
        $langData = array(
            $this->langData->project->application_change_preference,
            $this->langData->project->current_user_discount_rate,
            $this->langData->project->change_user_discount_rate,
            $this->langData->project->preferential_rate_application
        );
        $data = array(
            'langData' => $langData,
            'fx_info' => $fx_info,
            'getLev3Percent' => $getLev3Percent
        );
        $this->setData($data,1,'');
    }
    /**
     * 优惠比例申请
     * @author tangp
     * @date 2018-10-25
     */
    public function doDisApply()
    {
        $fx_user_id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : '';
        $old_discount = ($_REQUEST['discount']) ? trim($_REQUEST['discount']) : '';
        $new_discount = ($_REQUEST['new_discount']) ? intval($_REQUEST['new_discount']) : 0;
        $lev3_prop = ($_REQUEST['lev3_prop']) ? trim($_REQUEST['lev3_prop']) : '';
        $mod = &m('fxDiscountLog');
        if (!isset($new_discount)) {
            $this->setData(array(), $status = '0', '请填写优惠比例');
        }
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $new_discount)) {
            $this->setData($info = array(), $status = '0', '优惠比例格式不正确');
        }

        if ($lev3_prop < $new_discount) {
            $this->setData(array(), $status = '0', '优惠比例不能大于您的分润比例！');
        }
        $arr = array(
            'fx_user_id' => $fx_user_id,
            'fx_discount' => $new_discount,
            'old_discount' => $old_discount,
            'add_time' => time(),
            // 'lev3_prop' => $lev3_prop,
            'current_rule_percent' => $lev3_prop,  // 更新 current_rule_percent 字段 by xt 2019.03.06
            'source'=>3
        );
        $r = $mod->doInsert($arr);

        if ($r) {
            $this->setData(array(), $status = '1', '申请成功');
        } else {
            $this->setData(array(), $status = '0', '申请失败');
        }
    }
    /**
     * 校验银行卡号是否可行
     * @param int $card_number
     * @return string
     */
    public function check_bankCard($card_number){
        $arr_no = str_split($card_number);
        $last_n = $arr_no[count($arr_no)-1];
        krsort($arr_no);
        $i = 1;
        $total = 0;
        foreach ($arr_no as $n){
            if($i%2==0){
                $ix = $n*2;
                if($ix>=10){
                    $nx = 1 + ($ix % 10);
                    $total += $nx;
                }else{
                    $total += $ix;
                }
            }else{
                $total += $n;
            }
            $i++;
        }
        $total -= $last_n;
        $x = 10 - ($total % 10);
        if($x == $last_n){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 生成一定数量的不重复随机数
     * @param $min 最小值
     * @param $max 最大值
     * @param $num 数量
     * @return int 随机数
     */
    public function unique_rand($min, $max, $num) {
        //初始化变量为0
        $count = 0;
        //建一个新数组
        $return = array();
        while ($count < $num) {
            //在一定范围内随机生成一个数放入数组中
            $return[] = mt_rand($min, $max);
            //去除数组中的重复值用了“翻翻法”，就是用array_flip()把数组的key和value交换两次。这种做法比用 array_unique() 快得多。
            $return = array_flip(array_flip($return));
            //将数组的数量存入变量count中
            $count = count($return);
        }
        //为数组赋予新的键名
        shuffle($return);
        return $return[0];
    }
    /**
     * 账户安全
     * @author:tangp
     * @date:2018-08-28
     */
    public function accountSafe()
    {
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;  //多语言商品
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;  //所选的站点id
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
//        $userId = $this->userId ? $this->userId : $this->user_id_bank;
//        $sql = "select * from " . DB_PREFIX . "user where id=" . $userId;
//        $res = $this->userMod->querySql($sql);
        $langData = array(
            $this->langData->project->account_security,
            $this->langData->project->password_change_valid,
            $this->langData->project->email_change_valid,
            $this->langData->project->phone_change_valid
        );
        $data = array(
            'langData' => $langData
        );

        $this->setData($data,1,'');
    }
    /**
     * 修改密码
     * @author:tangp
     * @date:2018-08-28
     */
    public function editPassword()
    {
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;  //多语言商品
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;  //所选的站点id
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;

        $langData = array(
            $this->langData->project->change_password,
            $this->langData->project->old_password,
            $this->langData->project->new_password,
            $this->langData->project->enter_password,
            $this->langData->project->edit_password
        );
        $data = array(
            'langData' => $langData
        );
        $this->setData($data,1,'');
    }

    /**
     * 保存修改密码
     * @author:tangp
     * @date:2018-8-28
     */
    public function saveInfo()
    {
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $lang = !empty($_REQUEST['lang_id']) ? (int) ($_REQUEST['lang_id']) : '0';
        $storeid = !empty($_REQUEST['store_id']) ? (int) ($_REQUEST['store_id']) : '0';
        $old_password = $_REQUEST['old_password'] ? $_REQUEST['old_password'] : "";
        $new_password = $_REQUEST['new_password'] ? $_REQUEST['new_password'] : "";
        $new_again = $_REQUEST['new_again'] ? $_REQUEST['new_again'] : "";
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $userId = $this->userId;
        $userMod = &m('user');
        $user_info = $userMod->getOne(array("cond" => "id=" . $userId));
        if (empty($old_password)) {
            $res = $this->setData(array(), $status = '0', '原密码不能为空！');
        }
        if (empty($new_password)) {
            $res = $this->setData(array(), $status = '0', '新密码不能为空！');
        }
        if (empty($new_again)) {
            $res = $this->setData(array(), $status = '0', '请确认新密码！');
        }
        //更新用户密码
        if ((md5($old_password) != $user_info['password']) && $old_password != '') {
            $this->setData(array(), $status = '0', '原密码验证错误！');
        }
        if ($new_password != $new_again) {
            $this->setData(array(), $status = '0','新密码两次输入不一致！');
        }
        $data = array(
            'password' => md5($new_password)
        );
        $res = $userMod->doEdit($user_info['id'], $data);
        if ($res) {
            $this->setData(array(), 1, '修改成功');
        } else {
            $this->setData(array(), 0, '修改失败');
        }
    }
    /**
     * 修改邮箱
     * @author:tangp
     * @date:2018-8-28
     */
    public function editEmail()
    {
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;  //多语言商品
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;  //所选的站点id
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $userId = $this->userId;
        $sql = "select * from " . DB_PREFIX . "user where id=" . $userId;
        $res = $this->userMod->querySql($sql);

        $langData = array(
            $this->langData->project->change_email,
            $this->langData->project->old_email,
            $this->langData->project->new_email,
            $this->langData->project->edit_email,
            $this->langData->project->please_password
        );

        $data = array(
            'langData' => $langData
        );

        $this->setData($data,1,'');
    }
    /**
     * 保存修改邮箱
     * @author:tangp
     * @date:2018-08-28
     */
    public function emailInfo()
    {
        $lang = !empty($_REQUEST['lang_id']) ? (int) ($_REQUEST['lang_id']) : $this->lang_id;
        $storeid = !empty($_REQUEST['store_id']) ? (int) ($_REQUEST['store_id']) : $this->store_id;
        $primary_email = $_REQUEST['primary_email'] ? $_REQUEST['primary_email'] : "";
        $new_email = $_REQUEST['new_email'] ? trim($_REQUEST['new_email']) : "";
        $password = $_REQUEST['password'] ? $_REQUEST['password'] : "";
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;

        $userId = $this->userId;
        $userMod = &m('user');
        $user_info = $userMod->getOne(array("cond" => "id=" . $userId));
        if (!$new_email) {
            $this->setData(array(), $status = '0', '新邮箱不能为空！');
        }
        if ($this->userMod->isExist($type = 'email', $new_email, 'mark', 1)) {
            $this->setData(array(), $status = '0','邮箱用户名已存在！');
        }
        if (!preg_match("/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/", $new_email)) {//
            $this->setData(array(), $status = '0', '邮箱格式不正确！');
        };
        if (empty($password)) {
            $res = $this->setData(array(), $status = '0', '密码不能为空！');
        }
        //用户密码验证
        if ((md5($password) != $user_info['password']) && $password != '') {
            $this->setData(array(), $status = '0', '密码验证错误！');
        }
        $data = array(
            'email' => $new_email,
        );
        $res = $userMod->doEdit($user_info['id'], $data);
        if ($res) {
            $this->setData(array(), 1, '修改成功');
        } else {
            $this->setData(array(), 0, '修改失败');
        }
    }
    /**
     * 修改手机
     * @author:tangp
     * @date:2018-08-28
     */
    public function editPhone()
    {
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;  //多语言商品
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;  //所选的站点id
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $userId = $this->userId;
        $sql = "select * from " . DB_PREFIX . "user where id=" . $userId;
        $res = $this->userMod->querySql($sql);

        $langData = array(
            $this->langData->project->edit_phone,
            $this->langData->project->please_phone,
            $this->langData->project->please_code,
            $this->langData->project->get_code,
            $this->langData->project->enter_edit
        );

        $data = array(
            'langData' => $langData
        );

        $this->setData($data,1,'');
    }
    /**
     * 保存修改手机
     * @author:tangp
     * @date:2018-08-28
     */
    public function phoneInfo()
    {
        $a = $this->langData;
        $langid = !empty($_REQUEST['lang_id']) ? (int) ($_REQUEST['lang_id']) : '0';
        $storeid = !empty($_REQUEST['store_id']) ? (int) ($_REQUEST['store_id']) : '0';
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;

        $phone = trim($_REQUEST['phone']);
        $code = $_REQUEST['code'];
        $userId = $this->userId;
        $userMod = &m('user');
        $user_info = $userMod->getOne(array("cond" => "id=" . $userId));
        if (empty($phone)) {
            $this->setData(array(), $status = '0', '手机号码必填！');
        }

        if (!preg_match('/^(13[0-9]|14[0-9]|15[0-9]|17[0-9]|18[0-9])\d{8}$/', $phone)) {
            $this->setData(array(), $status = '0', '手机号码格式错误！');
        }
        if ($this->getPhoneInfo($phone)) {
            $this->setData(array(), $status = '0', '新手机号码已经被注册！');
        }
        $smsCode = $this->getSmsCode($phone);
        if (empty($code)) {
            $this->setData(array(), $status = '0', '验证码必填！');
        }
        if ($code != $smsCode) {
            $this->setData(array(), $status = '0', '验证码不正确！');
        }
        $data = array(
            'phone' => $phone,
        );
        $res = $userMod->doEdit($user_info['id'], $data);
        if ($data) {
            $this->setData(array(), 1, "修改成功"); //修改成功
        } else {
            $this->setData(array(), 0, "修改失败"); //修改失败
        }
    }
    public function getSmsCode($phone) {
        $smsMod = &m('sms');
        $sql = 'select  phone,code  from bs_sms where  phone =' . $phone . '  order by id desc  limit 1';
        $data = $smsMod->querySql($sql);
        return $data[0]['code'];
    }
    /**
     * @param $userid
     */
    public function updateUser($userid) {
        $data = array(
            'is_fx' => 1
        );
        $this->userMod->doEdit($userid, $data);
    }
    /*
     * 新增分销用户关系树，余额
     * @author lee
     * @date 2017-11-21 20:29:31W
     */

    public function doFxmoney($cate_id) {
        $userId = $this->userId;
        $storeMod = &m('store');
        $sql = 'select id as store_id  from  ' . DB_PREFIX . 'store  where   store_cate_id =' . $cate_id;
        $res = $storeMod->querySql($sql);
        if (!empty($res)) {
            foreach ($res as $key => $val) {
                $arr_store[$key]['user_id'] = $userId;
                $arr_store[$key]['store_cate'] = $cate_id;
                $arr_store[$key]['money'] = 0.00;
                $arr_store[$key]['store_id'] = $val['store_id'];
            }
            foreach ($arr_store as $k => $v) {
                $res = $this->fxuserMoneyMod->doInsert($v);
            }
        }
    }
    public function isfxUser() {
        $userId = $this->userId;
        $fxUserMod = &m('fxuser');
        $sql = 'select  id,is_check   from bs_fx_user  where   user_id = ' . $userId;
        $data = $fxUserMod->querySql($sql);
        if (!empty($data)) {
            return $data[0];
        } else {
            return array();
        }
    }
    public function getPhoneInfo($telphone) {
        $fxUserMod = &m('fxuser');
        $sql = 'select  id from  bs_fx_user  where  telephone =' . $telphone . '  limit 1';
        $data = $fxUserMod->querySql($sql);
        if (empty($data[0])) {
            return null;
        } else {
            return $data[0]['id'];
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
            $_SERVER["PHP_SELF"] = str_replace("/wx.php", "", $_SERVER["PHP_SELF"]);
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
     * 个人信息
     * @author:tangp
     * @date: 2018-08-23
     */
    public function userInfo()
    {
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;  //多语言商品
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;  //所选的站点id
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        $langData = array(
            $this->langData->project->weChat_head,
            $this->langData->project->user_name,
            $this->langData->project->weChat_name,
            $this->langData->project->change_the_phone,
            $this->langData->public->email,
            $this->langData->project->personal_data,
        );
        $userId = $this->userId;
        $sql = "select * from " . DB_PREFIX . "user where id=" . $userId;
        $res = $this->userMod->querySql($sql);
        $data = array(
            'langData' => $langData,
            'listData' => $res
        );
        if ($res){
            $this->setData($data,1,'');
        }

    }

    /**
     * 文章收藏
     * @author: tangp
     * @date:   2018-08-23
     */
    public function articleCollection()
    {
        $userId    = $this->userId;
        $storeid   = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        $lang      = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon    = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $langData = array(
            $this->langData->project->collection_of_articles,
        );
        $sql = 'SELECT article_id FROM ' .DB_PREFIX . 'user_article WHERE store_id = ' . $storeid . ' AND user_id= ' . $userId;
        $articleData = $this->colleCtionMod->querySql($sql);
        foreach ($articleData as $k => $v){
            $id[] = $v['article_id'];
        }
        $ids = implode(',',$id);
        if (!empty($ids)) {
            $articleSql = 'SELECT a.id,al.title,al.brif,a.image,a.add_time FROM ' . DB_PREFIX . 'article AS a LEFT JOIN ' . DB_PREFIX . 'article_lang AS al ON a.id=al.article_id WHERE al.lang_id= ' . $lang . ' AND a.id in (' . $ids . ')';
            $listData = $this->colleCtionMod->querySql($articleSql);
            $data = array('langData' => $langData, 'listData' => $listData);
            $this->setData($data, '1', '');

        }

    }

    /**
     * 店铺收藏
     * @author: tangp
     * @date: 2018-08-23
     */
    public function storeCollection()
    {
        $userId = $this->userId;
        $storeid   = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        $lang      = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon    = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon',$latlon);
        $langData = array(
            $this->langData->project->collection_of_stores,
        );
        $bSql = 'SELECT store_id FROM  ' . DB_PREFIX . 'user_store  WHERE  user_id='.$userId;

        $bData = $this->userStoreMod->querySql($bSql);
        foreach ($bData as $k => $v) {
            $sId[] = $v['store_id'];
        }
        $sIds = implode(',', $sId);
        if (!empty($sIds)) {
            $swhere = " where sl.distinguish=0 AND s.id in (" . $sIds . ')';
            $ssql = 'SELECT  s.logo,s.id,s.longitude,s.latitude,l.`name` AS lname ,c.`name` AS cname,sl.`store_name` AS sltore_name FROM  ' . DB_PREFIX . 'store AS s LEFT JOIN  ' . DB_PREFIX . 'store_lang AS sl ON sl.`store_id` = s.`id` and  sl.lang_id=' . $lang . ' LEFT JOIN  ' . DB_PREFIX . 'language AS l ON s.`lang_id` = l.`id` LEFT JOIN  ' . DB_PREFIX . 'currency AS c ON s.`currency_id` = c.`id`' . $swhere;
            $listData = $this->userStoreMod->querySql($ssql);
        } else {
            $listData = array();
        }
        foreach ($listData as $key => $val) {
            $busSql = "SELECT buss_id FROM " . DB_PREFIX . 'store_business WHERE  store_id=' . $val['id'];
            $busData = $this->userStoreMod->querySql($busSql);
            $listData[$key]['b_id'] = $busData[0]['buss_id'];
        }

        $data = array('langData'=>$langData,'listData'=>$listData);
        $this->setData($data,'1','');


    }

    /**
     * 商品收藏
     * @author: tangp
     * @date: 2018-08-23
     */
    public function goodsCollection()
    {
        $storeid   = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        $lang      = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon    = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $langData = array(
            $this->langData->project->collection_of_goods
        );
        $userId = $this->userId;
        $where = 'f.user_id = '.$userId;
//        $sql = 'select distinct f.*,g.*,l.*,gl.original_img,f.id  from '
//            . DB_PREFIX . 'store_goods as g inner join '
//            . DB_PREFIX . 'user_collection as f on f.store_good_id = g.id inner join '
//            . DB_PREFIX . 'goods_lang as l on l.goods_id = g.goods_id and l.lang_id=' . $lang . ' left join '
//            . DB_PREFIX . 'goods as gl on g.goods_id = gl.goods_id  where ' . $where . ' and f.store_id =' . $storeid
//            . ' group by f.store_good_id order by f.id desc limit 0, 8 ';
        $sql = "select distinct f.*,g.*,l.*,gl.original_img,f.id from "
            . DB_PREFIX . 'store_goods as g inner join '
            . DB_PREFIX . 'user_collection as f on f.store_good_id = g.id inner join '
            . DB_PREFIX . 'goods_lang as l on l.goods_id = g.goods_id and l.lang_id=' . $lang . ' left join '
            . DB_PREFIX . 'goods as gl on g.goods_id = gl.goods_id where ' . $where
            . ' group by f.store_good_id order by f.id desc limit 0, 8 ';
//        echo $sql;die;
        $data1 = $this->colleCtionMod->querySql($sql);
        $data = array(
            'langData'=>$langData,'listData'=>$data1
        );

        $this->setData($data,'1','');

    }

    /**
     * 收货地址
     * @author:wangshuo
     * @date:2018-8-31
     */
    public function myAddress()
    {
        $userId = $this->userId;
        $userId=35035;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '118.77807441,32.0572355';
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        //语言包
        $langData = array(
            $this->langData->project->receiving_address,//收货地址
            $this->langData->public->default,//默认
            $this->langData->public->set_default,//设置默认
            $this->langData->public->edit,//编辑
            $this->langData->public->drop,//删除
            $this->langData->project->add_new_address//添加新地址
        );
        $addressMod = &m("userAddress");
        $sql = "select * from " . DB_PREFIX . "user_address where `user_id`='{$userId}'";
        $res = $addressMod->querySql($sql);
        foreach ($res as $k => $v) {
        $store_address = explode('_', $v['address']);
        $res[$k]['address'] = $store_address[0];
        if(!empty($v['mailing_address'])){
            if($v['pays'] == 1){
                $maiadd['addre']   = $v['city'].$v['mailing_address'];
                $maiadd['id']      = $v['id'];
                $maiadd['default_addr']      = $v['default_addr'];
                $maiadd['phone']      = $v['phone'];
                $maiadd['name']      = $v['name'];
                $delivery_address[] = $maiadd;
            }else{
                $maiadd['addre']   = $v['mailing_address'];
                $maiadd['id']      = $v['id'];
                $maiadd['default_addr']      = $v['default_addr'];
                $maiadd['phone']      = $v['phone'];
                $maiadd['name']      = $v['name'];
                $mailing_address[] = $maiadd;

            }
        }
    }
        $address['a'] = !empty($delivery_address)?$delivery_address:[];
        $address['b'] = !empty($mailing_address)?$mailing_address:[];
        $data = array(
            'langData' => $langData,
            'listData' => $res,
            'latlon' => $latlon,
            'lang_id' => $this->lang_id,
            'store_id' => $this->store_id,
            'auxiliary' => $auxiliary,
            'address' => $address,
        );
        print_r($data);die;
        // if ($res){
        $this->setData($data,1,'');
        // }

    }
    /**
     * 添加收货地址（页面）
     * @author:tangp
     * @date:2018-08-24
     */
    public function addAddress()
    {
        //添加海外中国 ly 11-7
        $userAddressMod = &m('userAddress');
        $sql = "select  *  from " . DB_PREFIX . "city where parent_id = 0";
        $city = $userAddressMod->querySql($sql);
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->lang_id;

        $langData = array(
            $this->langData->public->linkman,//联系人
            $this->langData->public->phone,//手机号
            $this->langData->project->receiving_address,//收货地址
            $this->langData->project->add_receiving_address,//添加收货地址
            $this->langData->project->receiving_name_example,//请填写收货人的姓名
            $this->langData->project->receiving_phone_example,//请填写收货手机号
            $this->langData->project->door_number,//门牌号
            $this->langData->project->save_address//保存地址
        );
        $info=array('langData'=>$langData,'city'=>$city);
        $this->setData($info,1,'');
    }

    /*
    * 删除地址
    * @author wangshuo
    * @date 2018-8-31
    */

    public function addDelete() {
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : '';

        $addressMod = &m("userAddress");
        //获取收货地址
        $userId = $this->userId;
        $addrSql = "select * from " . DB_PREFIX . 'user_address where user_id=' . $userId . ' and id=' . $id;
        $userAddress = $addressMod->querySql($addrSql); // 获取用户的地址
        if ($userAddress[0]['default_addr'] == 1) {
            $res = $addressMod->doDrop($id);
            if ($res) {
                //获取收货地址
                $editSql = "select * from " . DB_PREFIX . 'user_address where user_id=' . $userId;
                $userEdit = $addressMod->querySql($editSql); // 获取用户的地址
                $user_addr = $userEdit[0]['id'];
                $data = array(
                    "default_addr" => 1, //默认收货
                );
                $res = $addressMod->doEdit($user_addr, $data);
                $this->setData(array(), $status = '1', '删除成功');
            } else {
                $this->setData(array(), $status = '0', '删除失败');
            }
        } else {
            $res = $addressMod->doDrop($id);
            if ($res) {
                $this->setData(array(), $status = '1', '删除成功');
            } else {
                $this->setData(array(), $status = '0', '删除失败');
            }
        }
    }



    /**
     * 添加收货地址（提交）
     * @author:gao
     * @date:2018-08-24
     */
    public function doAddress()
    {
        $url = !empty($_REQUEST['url']) ? $_REQUEST['url'] : '0';
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) : ''; //收货人
        $phone = !empty($_REQUEST['phone']) ? htmlspecialchars(trim($_REQUEST['phone'])) : ''; //手机
        $address = !empty($_REQUEST['address']) ? htmlspecialchars(trim($_REQUEST['address'])) : ''; //地址详情
        $mp = !empty($_REQUEST['mp']) ? htmlspecialchars(trim($_REQUEST['mp'])) : ''; //地址详情
        $mailing_address = !empty($_REQUEST['mailing_address']) ? htmlspecialchars(trim($_REQUEST['mailing_address'])) : ''; //邮寄地址详情
        $storeid = !empty($_REQUEST['storeid']) ? (int) ($_REQUEST['storeid']) : '0';
        $id = $_REQUEST['id'] ? $_REQUEST['id'] : 0;
        $latlon = !empty($_REQUEST['latlon']) ? $_REQUEST['latlon'] : '0';
        $postal = !empty($_REQUEST['postal']) ? htmlspecialchars(trim($_REQUEST['postal'])) : '';
        $nowlatlon = !empty($_REQUEST['nowlatlon']) ? $_REQUEST['nowlatlon'] : '';

        //添加省市区 ly 11-7
        $pays = !empty($_REQUEST['pays']) ? $_REQUEST['pays'] : 1;
        $citymod = &m('city');
        if(!empty($_REQUEST['province'])){
            $pro=explode(',',$_REQUEST['province']);
            $province=json_encode(implode('-',$pro));
            $citysqlthree="select * from " . DB_PREFIX . 'city where id=' . $pro[2] ;
            $citythree = $citymod->querySql($citysqlthree);
            $citysqltwo="select * from " . DB_PREFIX . 'city where id=' . $pro[1] ;
            $citytwo = $citymod->querySql($citysqltwo);
            $citysqlone="select * from " . DB_PREFIX . 'city where id=' . $pro[0] ;
            $cityone = $citymod->querySql($citysqlone);
            $postal_code=$citythree[0]['code'];
            $city=$cityone[0]['name'].$citytwo[0]['name'].$citythree[0]['name'];
        }

        $url = urldecode($url);
        $nowlatlon1 = explode(',', $nowlatlon);
        $nowlat = $nowlatlon1[1];
        $nowlng = $nowlatlon1[0];
        $latlon1 = explode(',', $latlon);
        $lat = $latlon1[1];
        $lng = $latlon1[0];
        $distance = $this->getdistance($lng, $lat, $nowlng, $nowlat);
        $distance = $distance / 1000;
        $userAddressMod = &m('userAddress');
        $sql = "select  distance from " . DB_PREFIX . "store where id =" . $storeid;
        $storeInfo = $userAddressMod->querySql($sql);
        if (empty($name)) {
            $this->setData(array(), $status = '0', "请填写收货人！"); //请填写收货人！
        }
        if (empty($phone)) {
            $this->setData(array(), $status = '0', "请填写手机号！"); //请填写手机号！
        }
        if (empty($latlon)) {
            $this->setData(array(), $status = '0', "请选择收货地址！");
        }
        if (empty($address)) {
            $this->setData(array(), $status = '0', '请填写收货地址！'); //请填写收货地址
        }


        $addr_sql = "select * from " . DB_PREFIX . "user_address where user_id=" . $this->userId . ' and default_addr =1  and distinguish=1';
        $info = $userAddressMod->querySql($addr_sql);
        if ($info[0]['default_addr'] != '1') {
            $data = array(
                "name" => $name, //收货人姓名
                "phone" => $phone, //收货人电话
                "app" => $mp, //地址
                "address" => $address . '_' . $mp, //详细地址
                'mailing_address'=>$mailing_address,
                "user_id" => $this->userId,
                'latlon' => $latlon,
                'city' => $city,
                'province' => $province,
                'postal_code' => $postal_code,
                'pays' => $pays,
                'default_addr' => 1
            );
        } else {
            $data = array(
                "name" => $name, //收货人姓名
                "phone" => $phone, //收货人电话
                "app" => $mp, //地址
                "address" => $address . '_' . $mp, //详细地址
                'mailing_address'=>$mailing_address,
                "user_id" => $this->userId,
                'city' => $city,
                'province' => $province,
                'postal_code' => $postal_code,
                'pays' => $pays,
                'latlon' => $latlon
            );
        }
        print_r($data);die;
        //判断地址
        if ($id) {
            //修改地址
            $res = $userAddressMod->doEdit($id, $data);
        } else {
            //添加地址
            $res = $userAddressMod->doInsert($data);
        }
        //添加最新的地址
        if ($res) {

            $this->setData($info, $status = '1', '添加成功'); //添加成功
        } else {

            $this->setData($info, $status = '0','添加失败'); //添加失败
        }

    }

    function getdistance($lng1, $lat1, $lng2, $lat2) {
        // 将角度转为狐度
        $radLat1 = deg2rad($lat1); //deg2rad()函数将角度转换为弧度
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137 * 1000;
        return $s;
    }

    /**
     * 我的推荐
     * @author:tangp
     * @date:2018-08-24
     */
    public function myRecommendation()
    {
        $userId = $this->userId;
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $langData = array(
            $this->langData->project->my_recommend,
            $this->langData->project->recommend_my,
            $this->langData->public->serial_number,
            $this->langData->public->phone,
            $this->langData->public->time,
        );
        //获取当前用户的邀请手机号
        $phone_sql = 'select phone,phone_email from ' . DB_PREFIX . 'user  where id =' . $userId . ' and mark =1';
        $phone_data = $this->userMod->querySql($phone_sql);
//        echo '<pre>';
//        var_dump($phone_data);die;
        //推荐我的人
        $Recommendme_sql = 'select phone,add_time from ' . DB_PREFIX . 'user where phone = ' . $phone_data[0]['phone_email'] . ' and mark =1';
        $Recommendme_data = $this->userMod->querySql($Recommendme_sql);
        //我推荐的数量
        $num_sql = "select count(*) as count from  " . DB_PREFIX . "user where phone_email = " . $phone_data[0]['phone'] . " and mark =1 order by id";
        $num_res = $this->userMod->querySql($num_sql);
        //我推荐的人
        $Irecommend_sql = "select phone,add_time  from  " . DB_PREFIX . "user where phone_email = " . $phone_data[0]['phone'] . " and mark =1 order by id";
        $res = $this->userMod->querySqlPageData($Irecommend_sql);
//        foreach ($res['list'] as $k => $v) {
//            $res['list'][$k]['add_time'] = date('Y-m-d H:i', $v['add_time']);
//            if ($v['add_time']) {
//                $res['list'][$k]['add_time'] = date('Y-m-d H:i', $v['add_time']);
//            } else {
//                $res['list'][$k]['add_time'] = '';
//            }
//            $res['list'][$k]['sort_id'] = $k + 2000 * ($p - 1) + 1; //正序
//        }
        if ($Recommendme_data[0]['phone'] == '' && $Recommendme_data[0]['phone_email'] == ''){
            $data = array(
                'langData'         => $langData,
                'Recommendme_data' => '',
                'num_res'          => $num_res,
                'res'              => $res
            );
        }else{
            $data = array(
                'langData'         => $langData,
                'Recommendme_data' => $Recommendme_data,
                'num_res'          => $num_res,
                'res'              => $res
            );
        }

        $this->setData($data,1,'');
    }

    /**
     * 我的优惠卷
     * @author:tangp
     * @date:2018-08-24
     *
     */
    public function coupon()
    {
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;  //多语言商品
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;  //所选的站点id
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        $langData = array(
            $this->langData->project->not_expired,
            $this->langData->project->expired,
        );
        $userId = $this->userId;
        $sql = "select c_id from " . DB_PREFIX . 'user_coupon where user_id=' . $userId . ' and store_id= ' . $storeid;
        $info = $this->userMod->querySql($sql);
        foreach ($info as $key => $val) {
            $cIds[] = $val['c_id'];
        }
        $cIds = implode(',', $cIds);
        //过期优惠券
        $cSql = "select * from " . DB_PREFIX . 'coupon  where id in (' . $cIds . ') and end_time < ' . time();
        $ctSql = "select count(*) as ctotal from " . DB_PREFIX . 'coupon  where id in (' . $cIds . ') and end_time < ' . time();
        $cData = $this->userMod->querySql($cSql);
        $ctData = $this->userMod->querySql($ctSql);

        //未过期优惠券
        $wSql = "select * from " . DB_PREFIX . 'coupon  where id in (' . $cIds . ') and end_time > ' . time() . ' and start_time < ' . time();
        $wtSql = "select count(*) as wtotal from " . DB_PREFIX . 'coupon  where id in (' . $cIds . ') and end_time > ' . time() . ' and start_time < ' . time();
        $wData = $this->userMod->querySql($wSql);
        $wtData = $this->userMod->querySql($wtSql);
        $data = array(
            'langData' => $langData,
            'cData'    => $cData,
            'wData'    => $wData,
            'ctData'   => $ctData,
            'wtData'   => $wtData
        );


        $this->setData($data,1,'');
    }

    /**
     * 删除收藏
     * @author:tangp
     * @date:2018-08-24
     *
     */
    public function delete()
    {
        $type = $_REQUEST['type'];//获取取消收藏类型
        if(empty($type)){
            $this->setData('',1,'必须传收藏类型');
        }else{
            if ($type == 1){
                $article_id = $_REQUEST['id'];//获取文章的id

                $res = $this->userArticleMod->doDrops('article_id =' . $article_id);

                if ($res){
                    $this->setData('',1,'取消收藏成功');
                }else{
                    $this->setData('',0,'取消收藏失败');
                }


            }elseif($type == 2){
                $store_id = $_REQUEST['id'];//获取店铺的id

                $res = $this->userStoreMod->doDrops('store_id =' . $store_id);

                if ($res){
                    $this->setData('',1,'取消收藏成功');
                }else{
                    $this->setData('',0,'取消收藏失败');
                }


            }elseif ($type == 3){
//            $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->store_id;  //所选的站点id
                $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : '';
//            $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言
                $res = $this->colleCtionMod->doDrop($id);

                if ($res){
                    $this->setData('',1,'取消收藏成功');
                }else{
                    $this->setData('',0,'取消收藏失败');
                }
            }
        }
    }

    /**
     * 我的分享码
     * @author:tangp
     * @date:2018-09-04
     */
    public function myShare()
    {
        $userId = $this->userId;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;  //多语言商品
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;  //所选的站点id
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $langData = array(
            $this->langData->project->invite_sweep,
            $this->langData->project->create_code,

        );

        if (!$userId) {
            $this->setData(array(),0,'参数user_id必传！');
        }else{
            $user = &m('user');
            $sql = "select nickname,headimgurl,user_url from " . DB_PREFIX . "user where id=" . $userId;
            $ress = $user->querySql($sql);
            $user_url = $ress[0]['user_url'];
            if($user_url){
                $data = array(
                    'langData' => $langData,
                    'listData' => $ress
                );
                $this->setData($data,0,'存在二维码');
            }else{
                $sql = 'select nickname,headimgurl from ' . DB_PREFIX . 'user where id = ' . $userId . ' and mark =1';
                $res = $user->querySql($sql);
                $data = array(
                    'langData' => $langData,
                    'listData' => $res
                );
                $this->setData($data,1,'');
            }


        }



    }

    public function share()
    {
        $userId = $this->userId;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;  //多语言商品
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;  //所选的站点id
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $langData = array(
            $this->langData->project->invite_sweep,
            $this->langData->project->create_code,

        );

        if (!$userId) {
            $this->setData(array(),0,'参数user_id必传！');
        }else{
            $user = &m('user');
            $sql = "select nickname,headimgurl,xcx_code_url from " . DB_PREFIX . "user where id=" . $userId;
            $ress = $user->querySql($sql);
            $user_url = $ress[0]['xcx_code_url'];
            if($user_url){
                $data = array(
                    'langData' => $langData,
                    'listData' => $ress
                );
                $this->setData($data,0,'存在二维码');
            }else{
                $sql = 'select nickname,headimgurl from ' . DB_PREFIX . 'user where id = ' . $userId . ' and mark =1';
                $res = $user->querySql($sql);
                $data = array(
                    'langData' => $langData,
                    'listData' => $res
                );
                $this->setData($data,1,'');
            }


        }



    }
    /**
     * 生成分享码
     * @author:tangp
     * @date:2018-09-04
     */
    public function doCode()
    {
        $userId = $this->userId;
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;  //用户ID
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang'] : $this->lang_id;  //语言ID
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        //生成二维码
        $codee = $this->goodsZcode($userId,$storeid,$lang,$latlon);

        $urldata = array(
            "table" => "user",
            'cond'  => 'id = ' . $userId,
            'set' => "user_url='" . $codee . "'",
        );
        $ress = $this->userMod->doUpdate($urldata);

        if ($ress) {
            $this->setData($codee,1,'生成成功');
        }else{
            $this->setData('',0,'生成失败');
        }
    }
    //小程序生成二维码
    public function makeCode()
    {
        $user_id = $this->userId;
        //生成二维码
        $codee = $this->createCode($user_id);
        $urldata = array(
            "table" => "user",
            "cond"  => 'id = ' . $user_id,
            "set"   => "xcx_code_url='" . $codee . "'"
        );
        $ress = $this->userMod->doUpdate($urldata);
        if ($ress){
            $this->setData($ress,1,'生成成功！');
        }else{
            $this->setData('',0,'生成失败！');
        }
    }
    public function createCode($user_id)
    {
        $post_data = json_encode(array(
            "width" => 120,
            "scene" => "$user_id",
            "page"  => "pages/register/register"
        ));
        $access_token = $this->getAccessToken();
        // 为二维码创建一个文件
        $mainPath = ROOT_PATH . '/upload/xcxCode';
        $this->mkDir($mainPath);
        $timePath = date('Ymd');
        $savePath = $mainPath . '/' . $timePath;
        $this->mkDir($savePath);
        $newFileName = uniqid() . ".png";
        $filename = $savePath . '/' . $newFileName;
        $pathName = 'upload/xcxCode/' . $timePath . '/' . $newFileName;
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$access_token;
        $result = $this->httpRequest($url,$post_data,'POST');
        $res = file_put_contents($pathName,$result);
        return $pathName;
    }
    /**
     * 读取access_token
     */
    public function getAccessToken()
    {
        $appid = 'wxd483c388c3d545f3';
        $secret = 'd19b0561679a32122f10d524153f7ea5';
        return $this->getNewToken($appid,$secret);
    }
    public function getNewToken($appid,$secret)
    {
        $tokenUrl = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $secret;
        $access_token_arr = $this->httpRequest($tokenUrl);
        $access = json_decode($access_token_arr,true);
        return $access['access_token'];
    }
    public function httpRequest($url, $data='', $method='GET'){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        if($method=='POST')
        {
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data != '')
            {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
        }

        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }
    public function goodsZcode($userId,$storeid,$lang,$latlon)
    {
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
        //         $system_web = 'www.711home.net';
        $system_web = 'www.711home.net';
        $valueUrl = 'http://'.$system_web."/wx.php?app=user&act=quickLogin&userId={$userId}&storeid={$storeid}&lang={$lang}&&auxiliary=0&latlon={$latlon}";
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
     * 个人资料修改
     * @author:tangp
     * @date:2018-09-04
     */
    public function doUserInfo()
    {
        $lang    = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : '0';
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        $goods_images = !empty($_REQUEST['order_pic']) ? $_REQUEST['order_pic'] : '';
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        // $arr = implode(',', $goods_images);
        // $list = rtrim($arr, ',');
        $username = $_REQUEST['username'] ? $_REQUEST['username'] : "";
        $phone = trim($_REQUEST['phone']);
        $email = $_REQUEST['email'];
        $userId = $this->userId;
        $userMod = &m('user');
        $data = array(
            'username' => $username,
            'headimgurl' => $goods_images,
        );
        $res = $userMod->doEdit($userId, $data);
        if ($phone) {
            if (!preg_match('/^(13[0-9]|14[0-9]|15[0-9]|17[0-9]|18[0-9])\d{8}$/', $phone)) {
                $this->setData(array(),0, '手机号码格式错误');
            }
            if ($this->userMod->isExist($type = 'phone', $phone, 'mark', 1)) {
                $this->setData(array(), 0, '手机用户名已存在');
            }
            $data = array(
                'phone' => $phone,
            );
            $res = $userMod->doEdit($userId, $data);
            if ($res == false) {
                $this->setData(array(), 0, '修改失败');
            }
        }
        if ($email) {
            if (!preg_match("/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/", $email)) {
                $this->setData(array(), 0, '邮箱格式不正确');
            }
            if ($this->userMod->isExist($type = 'email', $email, 'mark', 1)) {
                $this->setData(array(), 0, '邮箱用户名已存在');
            }
            $data = array(
                'email' => $email,
            );
            $res = $userMod->doEdit($userId, $data);
            if ($res == false) {
                $this->setData(array(), 0, '修改失败');
            }
        }
        $this->setData(array(), 1,'修改成功');
    }
    /**
     * 微信服务器图片下载R
     * @author wangshuo
     * @date 2018-1-15
     */
    public function getUploadPicture() {
        $serverId = isset($_POST['serverId']) ? htmlspecialchars($_POST['serverId']) : '';
        $access_token = isset($_POST['access_token']) ? htmlspecialchars($_POST['access_token']) : '';
        //        echo "<script type='text/javascript'>alert('已全部清除！');</script>";
        //下载图片
        $url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token={$access_token}&media_id={$serverId}";
        $dirName = 'upload/images/user/' . date('Ymd') . '/';
        $imageName = time() . rand(1000, 9999) . '.jpg';
        if (!file_exists($dirName)) {
            mkdir($dirName, 0777, true);
        }
        $uploadPath = $dirName . $imageName;
        $ch = curl_init($url); // 初始化
        $fp = fopen($uploadPath, 'wb'); // 打开写入
        curl_setopt($ch, CURLOPT_FILE, $fp); // 设置输出文件的位置，值是一个资源类型
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        $newDirName = './upload/images/user/' . date('Ymd') . '/';
        $this->setData(array('uploadPath' => $uploadPath, 'newDirName' => $newDirName, 'imageName' => $imageName), $status = 1, $message = '获取成功');
    }


    /**
     * 编辑收货地址（页面）
     * @author:gao
     * @date:2018-08-24
     */
    public function editAddress()
    {
        $id=!empty($_REQUEST['id']) ? $_REQUEST['id'] : 198;
        $id=583;
        $sql="select * from ".DB_PREFIX.'user_address  where id='.$id;
        $addressData=$this->userMod->querySql($sql);
        print_r($addressData);die;
        $addressInfo = explode('_',$addressData[0]['address']);
        $address = $addressInfo[0];
//        var_dump($address);die;

        //添加省市区  ly 2019-11-7
        $province=(!empty($addressData[0]['province']))?explode('-',json_decode($addressData[0]['province'])):'';
        $pays=(!empty($addressData[0]['pays']))?$addressData[0]['pays']:'';
        $userAddressMod = &m('userAddress');
        $sql = "select  *  from " . DB_PREFIX . "city where parent_id = 0";
        $city = $userAddressMod->querySql($sql);

        foreach ($addressData as $key => $value){
            $addressData[$key]['address'] = $address;
        }
        $langData = array(
            $this->langData->project->add_receiving_address,
            $this->langData->project->receiving_name_example,
            $this->langData->project->receiving_phone_example,
            $this->langData->project->door_number,
            $this->langData->project->save_address
        );
        $info=array('langData'=>$langData,'addressData'=>$addressData,'province'=>$province,'pays'=>$pays,'city'=>$city);
        $this->setData($info,1,'');
    }
    /**
     * 提现申请页面
     * @author tangp
     * @date 2018-09-21
     */
    public function cash()
    {
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        //获取fx_user_id
        $fxUserMod = &m('fxuser');
        $fxUser = $fxUserMod->getOne(array('cond'=>'user_id='.$this->userId));
        //判断是否有正在处理的提现申请
        $applyMod = &m('fxOutmoneyApply');
        $cond = 'fx_user_id=' . $fxUser['id'];
        $data = $applyMod->getOne(array('cond'=>$cond . ' and is_check = 1', 'order by id desc'));
        if (!empty($data)) {
            $this->setData(array(),0,'您已申请，请等待管理员审核');
        }
        $exist = $applyMod->getOne(array('cond'=>$cond));
        if ($exist) {
            $exist = 1;
        }
        $langData = array(
            $this->langData->project->commission,
            $this->langData->project->present_account,
            $this->langData->project->present_way,
            $this->langData->project->application_log,
        );
        $listData = array(
            'data' => $data,
            'user' => $fxUser,
            'exist'=> $exist
        );
        $data1 = array(
            'listData' => $listData,
            'langData' => $langData
        );
        $this->setData($data1,1,'');
    }
    /**
     * 提现处理
     * @author tangp
     * @date 2018-09-21
     */
    public function docash()
    {
        $storeId = !empty($_POST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        $userid = $this->userId;
        $fxUserId = !empty($_POST['fx_user_id']) ? $_POST['fx_user_id'] : 0;
        $apply_money = !empty($_POST['apply_money']) ? htmlspecialchars(trim($_POST['apply_money'])) : 0;
        $bankName = !empty($_POST['bank_name']) ? htmlspecialchars(trim($_POST['bank_name'])) : '';
        $bankAccount = !empty($_POST['bank_account']) ? htmlspecialchars(trim($_POST['bank_account'])) : '';
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        if (empty($fxUserId) || empty($storeId)) {
            $this->setData($info = array(), $status = '0', '系统错误');
        }
        if (empty($bankName)) {
            $this->setData($info = array(), $status = '0', '开户银行必填');
        }
        if (empty($bankAccount)) {
            $this->setData($info = array(), $status = '0', '银行账号必填');
        }
        if (empty($apply_money)) {
            $this->setData($info = array(), $status = '0', '提现金额必填');
        }
        if ($apply_money == 0) {
            $this->setData($info = array(), $status = '0', '提现金额不能为0');
        }
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $apply_money)) {
            $this->setData($info = array(), $status = '0', '提现金额必填格式不正确');
        }
        //分销设置
        $siteDate = $this->getFxSite($storeId);
        //本月的提现记录
        $jiluDate = $this->getTixianJilu($fxUserId, $storeId);
        //分销提现设置判断
        if ($siteDate['is_money'] == 1) {
            if ($siteDate['money'] > $apply_money) {
                $this->setData($info = array(), $status = '0', '提现金额要大于等于' . $this->symbol . $siteDate['money']);
            }
        }
        //分销提现记录次数的判断
        if ($siteDate['is_time'] == 1) {
            if (($jiluDate['total'] + 1 ) > $siteDate['time']) {
                $this->setData($info = array(), $status = '0', '每月最多提现' . $siteDate['time'] . '次');
            }
        }
        //验证可提现金额
        $fxUserMod = &m('fxuser');
        $info = $fxUserMod->getOne(array('cond'=>'user_id='.$this->userId));
        if ($apply_money > $info['monery']) {
            $this->setData($info = array(), $status = '0', '提现金额不能大于可提现金额');
        }
        $outmoneyMod = &m('fxOutmoneyApply');
        // 站点后台处理
        $data = array(
            'fx_user_id' => $info['id'],
            'apply_money' => $apply_money,
            'bank_name' => $bankName,
            'bank_account' => $bankAccount,
            'is_check' => 1,
            'source' => 3,
            'add_time' => time()
        );
        $res = $outmoneyMod->doInsert($data);
        if ($res) {
            $this->setData(array(), 1, '提现申请成功');
        } else {
            $this->setData(array(), 0, '提现申请失败');
        }
    }
    public function getFxSite($storeid) {
        $fxSiteMod = &m('fxSite');
        $sql = 'select  *  from  bs_fx_site where mark = 1 and  store_id = ' . $storeid;
        $data = $fxSiteMod->querySql($sql);
        return $data[0];
    }

    public function getTixianJilu($userid, $storeid) {
        $time = $this->thisMonth();
        $outmoneyMod = &m('fxOutmoneyApply');
        $sql = 'SELECT  COUNT(*)  AS total FROM  bs_fx_outmoney_apply
                WHERE  fx_user_id = ' . $userid . ' AND  (`is_check` = 1  OR `is_check` = 2  )    and  add_time >  ' . $time['begin_month'] . '  AND   add_time < ' . $time['end_month'];
        $data = $outmoneyMod->querySql($sql);
        return $data[0];
    }
    /**
     * 提现记录
     * @author tangp
     * @date 2018-09-21
     */
    public function cashLog()
    {
        $user_id = $this->userId;
        $storeid = $this->store_id;
        $fxLogMod = &m('fxOutmoneyApply');
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        //本月提现记录
        $this_month = $this->thisMonth();
        $fxUserSql="select id from bs_fx_user where user_id=".$user_id;
        $fxUserInfo=$fxLogMod->querySql($fxUserSql);
        $fxUserId=$fxUserInfo[0]['id'];

        $where1 = "fx_user_id = " . $fxUserId  . " and add_time >= " . $this_month['begin_month'] . " and add_time <= " . $this_month['end_month'];
        $sql1 = 'select * from  bs_fx_outmoney_apply  where ' . $where1;
        $this_data = $fxLogMod->querySql($sql1);

        $last_month = $this->lastMonth();
        $where ="fx_user_id = " . $fxUserId  . " and add_time >= " . $last_month['begin_month'] . " and add_time <= " . $last_month['end_month'];
        $last_data = $fxLogMod->getData(array("cond" => $where));

        //历史记录
        $where ="fx_user_id = " . $fxUserId  . " and add_time <= " . $this_month['begin_month'] ;
        $all_data = $fxLogMod->getData(array("cond"=>$where));

        $where = "fx_user_id = " . $fxUserId ;
        $sum_money = $fxLogMod->getData(array("cond" => $where, "fields" => "sum(apply_money) as money"));

        $langData = array(
            $this->langData->project->application_log,
            $this->langData->public->current_month,
            $this->langData->public->no_record,
            $this->langData->public->up_month
        );
        $listData = array(
            'langData'  => $langData,
//            'last_data' => $last_data,
            'all_data'  => $all_data,
            'this_data' => $this_data,
            'sum_money' => $sum_money
        );
        $data = array(
//            'langData' => $langData,
            'listData' => $listData
        );

        $this->setData($data,1,'');
    }
    /**
     * 当月时间戳
     * @author lee
     * @date 2017-11-25 10:44:54
     */

    public function thisMonth() {
        $year = date("Y");
        $month = date("m");
        $allday = date("t");
        $begin_month = strtotime($year . "-" . $month . "-1");
        $end_month = strtotime($year . "-" . $month . "-" . $allday) + 3600 * 24 - 1;
        $data = array(
            'begin_month' => $begin_month,
            'end_month' => $end_month
        );
        return $data;
    }
    /**
     * 上月时间戳
     * @author lee
     * @date 2017-11-25 10:44:54
     */

    public function lastMonth() {
        //获取上个月时间戳
        $thismonth = date('m');
        $thisyear = date('Y');
        if ($thismonth == 1) {
            $lastmonth = 12;
            $lastyear = $thisyear - 1;
        } else {
            $lastmonth = $thismonth - 1;
            $lastyear = $thisyear;
        }
        $lastStartDay = $lastyear . '-' . $lastmonth . '-1';
        $lastEndDay = $lastyear . '-' . $lastmonth . '-' . date('t', strtotime($lastStartDay));
        $b_time = strtotime($lastStartDay); //上个月的月初时间戳
        $e_time = strtotime($lastEndDay); //上个月的月末时间戳
        $data = array(
            'begin_month' => $b_time,
            'end_month' => $e_time
        );
        return $data;
    }
    /**
     * 分销我的订单
     * @author tangp
     * @date 2018-09-21
     */
    public function fxOrder()
    {
        $fxOrderMod = &m('fxOrder');
        $fxuserMod = &m('fxuser');
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        $sql1 = "select * from bs_fx_user where user_id=".$this->userId;
        $info = $fxuserMod->querySql($sql1);
        if ($info[0]['level'] == 3){
            $sql = "SELECT fo.fx_discount,fs.discount,o.order_id,o.order_state,fs.level,fu.*,fo.add_time,o.goods_amount,fo.order_sn,fo.pay_money,o.order_state,o.order_amount FROM bs_fx_order AS fo 
              LEFT JOIN bs_order AS o ON fo.order_id = o.order_id 
              LEFT JOIN bs_fx_rule AS fu ON fo.rule_id = fu.id 
              LEFT JOIN bs_fx_user AS fs ON fo.fx_user_id = fs.id
              WHERE fo.fx_user_id=".$info[0]['id'];
//            echo $sql;die;
            $data = $fxOrderMod->querySql($sql);
//            echo '<pre>';var_dump($data);die;
            foreach($data as $k=>$v){
                $data[$k]['fxmoney']=number_format($v['pay_money']*($v['lev3_prop']-$v['fx_discount'])/100,2,'.','');
                $data[$k]['discount_money']=number_format($v['goods_amount']*$v['discount']/100,2,'.','');
            }
        }elseif ($info[0]['level'] == 1){
            //查出该用户的二级
            $s = "select id from bs_fx_user where parent_id={$info[0]['id']} and level=2";
            $ss = $fxuserMod->querySql($s);
            foreach($ss as $k=>$v){
                $secondFxUserIdData[]=$v['id'];
            }
            $secondFxUserIds=implode(',',$secondFxUserIdData);
            $sql5 = "select id from bs_fx_user where parent_id in (".$secondFxUserIds.") and level=3";
            $res = $fxuserMod->querySql($sql5);
            foreach($res as $k=>$v){
                $thirdFxUserIdData[]=$v['id'];
            }
            $thirdFxUserIds=implode(',',$thirdFxUserIdData);
            $sql6 = "select fu.discount,fu.level,fr.lev1_prop,fr.lev2_prop,fr.lev3_prop,o.goods_amount,fo.pay_money,o.order_state,fo.add_time,fo.order_id,fo.order_sn from bs_fx_order as fo
                      left join bs_order as o on fo.order_id = o.order_id
                      left join bs_fx_user as fu on fu.id = fo.fx_user_id
                      left join bs_fx_rule as fr on fo.rule_id = fr.id where fx_user_id in (".$thirdFxUserIds.")";
            $data = $fxOrderMod->querySql($sql6);

            foreach($data as $k=>$v){
                $prop = $v['lev1_prop']/100;
                $data[$k]['fxmoney']=number_format($v['pay_money'] *$prop,2,'.','');
                $data[$k]['discount_money']=number_format($v['goods_amount']*$v['discount']/100,2,'.','');
            }
        }elseif ($info[0]['level'] == 2){
            $sss = "select id from bs_fx_user where parent_id={$info[0]['id']} and level=3";
            $result =$fxuserMod->querySql($sss);
            foreach ($result as $v){
                $ids[] = $v['id'];
            }
            $two_ids    = implode(',',$ids);
            $sql = "select fu.discount,fu.level,fr.lev1_prop,fr.lev2_prop,fr.lev3_prop,o.goods_amount,fo.pay_money,o.order_state,fo.add_time,fo.order_id,fo.order_sn,o.order_amount from bs_fx_order as fo
                  left join bs_order as o on fo.order_id = o.order_id
                  left join bs_fx_user as fu on fu.id = fo.fx_user_id
                  left join bs_fx_rule as fr on fo.rule_id = fr.id where fx_user_id in ({$two_ids})";
            $data = $fxOrderMod->querySql($sql);
            foreach ($data as $k => $v){
                $prop = $v['lev2_prop']/100;
                $data[$k]['fxmoney'] = number_format($v['pay_money'] * $prop,2,'.','');
                $data[$k]['discount_money']=number_format($v['goods_amount']*$v['discount']/100,2,'.','');
            }
        }
        $langData = array(
            $this->langData->project->single_commission,
            $this->langData->project->single_preferential,
            $this->langData->project->order_number,
            $this->langData->project->amount,
            $this->langData->public->status
        );
        $da = array(
            'langData' => $langData,
            'listData' => $data
        );
        $this->setData($da,1,'');
    }

    /*
     * 分销订单详情
     */
    public function detail()
    {
        $orderMod = &m('order');
        $orderid = !empty($_REQUEST['orderid']) ? $_REQUEST['orderid'] : "";
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        $sql = 'SELECT o.order_amount,o.`order_id`,o.`buyer_name`,o.`buyer_email`,o.`buyer_phone`,g.`goods_id`,g.`goods_name`,g.`goods_price`,g.`goods_num`,g.goods_pay_price,g.spec_key_name
                 FROM  bs_order AS o
                 LEFT JOIN bs_order_goods AS g ON  o.`order_sn` = g.`order_id`  WHERE o.`order_id` = ' . $orderid;

        $data = $orderMod->querySql($sql);
        $langData = array(
            $this->langData->project->order_detail
        );
        $da = array(
            'listData' => $data,
            'langData' => $langData
        );
        $this->setData($da,1,'');
    }
    /**
     * 分销我的团队
     * @author tangp
     * @date 2018-09-21
     */
    public function fxTeam()
    {
        $fxUserMod = &m('fxuser');
        $whe = "where a.user_id=".$this->userId;
        $sql = " SELECT a.*,b.headimgurl FROM bs_fx_user as a
LEFT JOIN bs_user as b on b.id = a.user_id " .$whe;
        $data = $fxUserMod->querySql($sql);
        $level = $data[0]['level'];
        if ($level == 1){
            foreach ($data as $key => $val) {
                $data[$key]['fx_level'] = $fxUserMod->level[$val['level']];
                $lev2 = $this->getchilds($val['id']);
                if (!empty($lev2)) {
                    // 2 级
                    $data[$key]['childs'] = $lev2;
                    $data[$key]['count'] = count($lev2);
                    // 3级
                    foreach ($lev2 as $k => $v) {
                        $data[$key]['childs'][$k]['fx_level'] = $fxUserMod->level[$v['level']];
                        $lev3 = $this->getchilds($v['id']);
                        if (!empty($lev3)) {
                            $data[$key]['childs'][$k]['childs'] = $lev3;
                            $data[$key]['childs'][$k]['count'] = count($lev3);
                        }
                    }
                }
            }
        }elseif ($level == 2){
            foreach ($data as $key => $val) {
                $lev3 = $this->getchilds($val['id']);
                $data[$key]['fx_level'] = $fxUserMod->level[$val['level']];
                $data[$key]['childs'] = $lev3;
                $data[$key]['count'] = count($lev3);
            }
        }
        $listData = array(
            'listData' => $data,
            'level'    => $level
        );
        $this->setData($listData,'1','');
    }
    /**
     * 获取下级分销人员
     * @author Run
     */
    public function getchilds($pid) {
        $fxuserMod = &m('fxuser');
        $sql = 'select * from bs_fx_user where  status = 1 and parent_id =' . $pid;
        $res = $fxuserMod->querySql($sql);
        return $res;
    }
    /**
     * 订单详情页
     * @author tangp
     * @date 2018-09-21
     */
    public function orderDetails()
    {
        $userId = $this->userId;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;  //所选的站点id
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;  //多语言商品
        $orderid = $_REQUEST['orderid']; //订单id
        $where = ' f.buyer_id =' . $userId . ' and g.order_id =' . $orderid;
        $where .= ' and g.mark =' . 1;
        //查找店铺是总代理还是经销商，总代理显示所有订单，
        $storeMod = &m('store');
        $sqlStore = 'select store_type  from  ' . DB_PREFIX . 'store where id  = ' . $storeid;
        $dataStore = $storeMod->querySql($sqlStore);
        if ($dataStore[0]['store_type'] == 1) {
            //总代理
            //列表页数据
            $sql = 'select f.*,g.*, g.`add_time` from ' . DB_PREFIX . 'order as g left join ' . DB_PREFIX . 'order_goods as f '
                . 'on f.order_id = g.order_sn '
                . 'where' . $where;
        } else {
            //经销商
            //列表页数据
            $sql = 'select f.*,g.*, g.`add_time` from ' . DB_PREFIX . 'order as g left join ' . DB_PREFIX . 'order_goods as f '
                . 'on f.order_id = g.order_sn '
                . 'where' . $where . ' and f.store_id =' . $storeid;
        }

//          print_r($sql);exit;
        $data = $this->orderMod->querySql($sql);
        //获取订单所有商品
        $sql = "select o.*,o.goods_id as ogoods_id,l.* from "
            . DB_PREFIX . "order_goods as o left join "
            . DB_PREFIX . "store_goods as s on o.goods_id = s.id left join "
            . DB_PREFIX . "goods_lang as l  on s.goods_id = l.goods_id "
            . " where o.order_id= '{$data[0]['order_sn']}' and lang_id = " . $lang;
        $list = $this->orderGoodsMod->querySql($sql);
        foreach ($list as $k2 => $v2) {
            if ($v2['spec_key']) {
                $k_info = $this->get_spec($v2['spec_key'], $lang);
                foreach ($k_info as $k5 => $v5) {
                    $list[0][$k5]['spec_key_name'] = $v5['item_name'];
                }
            }
        }
        $data[0]['goods_list'] = $list;
        //买赠活动赠品
        $sql = "select * from " . DB_PREFIX . "gift_goods  as g left join "
            . DB_PREFIX . "store_goods as s on g.goods_id = s.id left join "
            . DB_PREFIX . "goods_lang as l on s.goods_id = l.goods_id " . " where g.id=" . $data[0]['gift_id'] . " and  lang_id = " . $lang;
        $res = $this->giftGoodMod->querySql($sql);
        if ($res[0]['goods_key']) {
            $k_info = $this->get_spec($res[0]['goods_key'], $lang);
            if ($k_info) {
                $res[0]['goods_key_name'] = $k_info[0]['item_name'];
            }
        }
        $data[0]['gift'] = $res;
        if ($data[0]['sendout'] == 1) {
            $shippingMethod = '自提';
        }
        if ($data[0]['sendout'] == 2) {
            $shippingMethod = '配送上门';
        }
        if ($data[0]['sendout'] == 3) {
            $shippingMethod = '邮寄托运';
        }
        $OrderStatus = array(
            "0" => '订单已取消',
            "10" => '订单未付款',
            "20" => '订单已付款',
            "30" => '订单已发货',
            "40" => '区域配送中',
            "50" => '订单已完成',
        );
        $langData = array(
            $this->langData->project->no_toLook,
            $this->langData->project->logistics_name,
            $this->langData->project->goods_subtotal,
            $this->langData->project->distribution_fee,
            $this->langData->project->order_allPrice,
            $this->langData->project->commission,
        );

        $data1 = array(
            'langData'       => $langData,
            'shippingMethod' => $shippingMethod,
            'listData'       => $data,
            'status'         => $OrderStatus
        );
        $this->setData($data1,1,'');
    }
    /**
     * 去赠送的显示
     * @author tangp
     * @date 2018-09-30
     */
    public function checkGiveUserPoint()
    {
        $systemConsoleMod = &m('systemConsole');
        $console_info = $systemConsoleMod->getRow(1);
        $this->setData($console_info['status'],1,'');
    }

    public function MkFolder($path) {
        if (!is_readable($path)) {
            $this->MkFolder(dirname($path));
            if (!is_file($path))
                mkdir($path, 0777);
        }
    }

    /**
     * 换绑手机号页面
     * @author tangp
     * @date 2018-10-11
     */
    public function untie()
    {
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $userId = $this->userId;
        $sql = "select phone from " . DB_PREFIX . "user where id=" . $userId;
        $res = $this->userMod->querySql($sql);
        $langData = array(
            $this->langData->project->bind_mobile_phone,
            $this->langData->project->change_cell_phone_number
        );
        $data = array(
            'langData' => $langData,
            'listData' => $res[0]
        );
        $this->setData($data,1,'');
    }

    /**
     * 解绑操作
     * @author tangp
     * @date 2018-10-11
     */
    public function unbind()
    {
        $userId = $this->userId;
        $res = $this->userMod->doEditSpec(array('id' => $userId), array( 'xcx_openid' => ''));

        if ($res) {
            $this->setData('', '1', '请到登录页重新登录！');
        }
    }

    /**
     * 会员二维码
     * @author tangp
     * @date 2018-11-13
     */
    public function Codecard()
    {
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;  //多语言商品
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;  //所选的站点id
        $user_id = $this->userId;
        $sql = "select phone,username,headimgurl,order_url,id from " . DB_PREFIX . "user where id=" . $user_id;
        $res = $this->userMod->querySql($sql);

        $this->setData($res,1,'');
    }

    /**
     * 小程序生成二维码
     * @author tangp
     * @date 2018-11-13
     */
    public function orderCode()
    {
        $user_id = $this->userId;  //用户ID
        $phone = !empty($_REQUEST['phone']) ? htmlspecialchars(trim($_REQUEST['phone'])) : '';
        //生成2维码
        $codee = $this->orderZcode($phone);
        $urldata = array(
            "table" => "user",
            'cond' => 'id = ' . $user_id,
            'set' => "order_url='" . $codee . "'",
        );
        $ress = $this->userMod->doUpdate($urldata);
        if ($ress) {
            $this->setData(array(),1,'生成成功！');
        }else {
            $this->setData(array(),0,'生成失败！');
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
//        $http_host = $_SERVER['HTTP_HOST'];
//         $system_web = 'www.711home.net';
        $valueUrl = "{$phone}";
        QRcode::png($valueUrl, $filename);
        return $pathName;
    }
    public function doModify()
    {
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;  //多语言商品
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;  //所选的站点id
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';

        $langData = array(
            $this->langData->project->application_for_distribution,
            $this->langData->public->real_name,
            $this->langData->public->phone,
            $this->langData->project->referral_discode,
            $this->langData->project->open_bank,
            $this->langData->project->bank_account,
            $this->langData->public->save,
            $this->langData->public->cancel
        );
        $sql = "select real_name,phone,fx_code,bank_name,bank_account from bs_fx_user where user_id =".$this->userId;
        $res = $this->fxUserMod->querySql($sql);
        $data = array(
            'langData' => $langData,
            'userInfo' => $res
        );
        $this->setData($data,1,'');
    }
    public function modify()
    {
        $fxUserMod = &m('fxuser');
        $storeMod  = &m('store');
        $real_name = !empty($_REQUEST['real_name']) ? $_REQUEST['real_name'] : '';
        $telephone = !empty($_REQUEST['telephone']) ? $_REQUEST['telephone'] : '';
        $tj_code   = !empty($_REQUEST['fx_code']) ? $_REQUEST['fx_code'] : '';
        $bank_name = !empty($_REQUEST['bank_name']) ? $_REQUEST['bank_name'] : '';
        $bank_account = !empty($_REQUEST['bank_account']) ? $_REQUEST['bank_account'] : '';
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        $user_id = $this->userId;
        if (empty($real_name)){
            $this->setData(array(),'0','真实姓名必填！');
        }
        if (empty($telephone)){
            $this->setData(array(),'0','手机号必填！');
        }
        if (!preg_match('/^1[34578]\d{9}$/', $telephone)) {
            $this->setData(array(), '0', '请输入正确的手机号！');
        }
        //验证手机号的唯一性（add）
        if ($this->getPhoneInfo($telephone)) {
            $this->setData(array(), '0', '手机号已存在！');
        }
        if (empty($bank_name)) {
            $this->setData(array(), '0', '开户银行必填！');
        }
        if (empty($bank_account)){
            $this->setData(array(),'0','银行账号必填！');
        }
        if (empty($tj_code)){
            $this->setData(array(),'0','推荐人分销码必填！');
        }
        $fx_info = $fxUserMod->getOne(array("cond" => "fx_code='" . $tj_code . "'"));
        $user = $fxUserMod->getOne(array("cond" => "user_id='" . $user_id . "'"));
        if (empty($fx_info)){
            $this->setData(array(),'0','您写的分销码不存在！');
        }else{
//            $sql = "select * from bs_fx_user where user_id=".$this->userId;
//            $info = $fxUserMod->querySql($sql);
//            if (!empty($info)){
//                $this->setData(array(),'0','已存在该用户！');
//            }
            $user_info = $this->userMod->getOne(array("cond" => "id=" . $this->userId));
            $store_info = $storeMod->getOne(array("cond"=>"id=".$store_id));
            if ($fx_info['level'] == 1){
                $data = array(
                    'parent_id'    => $fx_info['id'],
                    'level'        => 2,
                    'rule_id'      => $fx_info['rule_id'],
                    'user_id'      => $this->userId,
                    'real_name'    => $real_name,
                    'phone'        => $telephone,
                    'fx_code'      => $this->unique_rand(100000,999999,1),
                    'bank_name'    => $bank_name,
                    'bank_account' => $bank_account,
                    'store_cate'   => $store_info['store_cate_id'],
                    'store_id'     => $fx_info['store_id'],
                    'status'       => 1,
                    'is_check'     => 1,
                    'source'       => 4,
                    'discount'     => 0,
                    'add_time'     => time(),
                    'add_user'     => $this->userId,
                    'mark'         => 1,
                    'email'        => $user_info['email']
                );
                $result = $fxUserMod->doEdit($user['id'],$data);
                if ($result){
                    $this->setData(array(),'1','申请成功!请等待管理员审核！');
                }else{
                    $this->setData(array(),'0','申请失败！');
                }
            }elseif ($fx_info['level'] == 2){
                $data = array(
                    'parent_id'    => $fx_info['id'],
                    'level'        => 3,
                    'rule_id'      => $fx_info['rule_id'],
                    'user_id'      => $this->userId,
                    'real_name'    => $real_name,
                    'phone'        => $telephone,
                    'fx_code'      => $this->unique_rand(100000,999999,1),
                    'bank_name'    => $bank_name,
                    'bank_account' => $bank_account,
                    'store_cate'   => $store_info['store_cate_id'],
                    'store_id'     => $fx_info['store_id'],
                    'status'       => 1,
                    'is_check'     => 1,
                    'source'       => 4,
                    'discount'     => 0,
                    'add_time'     => time(),
                    'add_user'     => $this->userId,
                    'mark'         => 1,
                    'email'        => $user_info['email']
                );
                $result = $fxUserMod->doEdit($user['id'],$data);
                if ($result){
                    $this->setData(array(),'1','申请成功!请等待管理员审核！');
                }else{
                    $this->setData(array(),'0','申请失败！');
                }
            }elseif ($fx_info['level'] == 3){
                $this->setData(array(),'0','您写的分销码有误！');
            }
        }
    }
    /*
 * 新我的收藏
 * @author wangs
 * @date 2018-12-26
 */
    public function Collection() {
        $store_id = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;
        $lang_id = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;
        $userId = $this->userId;
        //收藏的店铺
        $bSql = 'SELECT store_id FROM  ' . DB_PREFIX . 'user_store  WHERE  user_id=' . $userId;
        $bData = $this->userStoreMod->querySql($bSql);
        foreach ($bData as $k => $v) {
            $sId[] = $v['store_id'];
        }
        $sIds = implode(',', $sId);
        if (!empty($sIds)) {
            $swhere = " where sl.distinguish=0 AND s.id in (" . $sIds . ')';
            $ssql = 'SELECT  s.id,s.longitude,s.latitude,l.`name` AS lname ,c.`name` AS cname,sl.`store_name` AS sltore_name FROM  ' . DB_PREFIX . 'store AS s LEFT JOIN  ' . DB_PREFIX . 'store_lang AS sl ON sl.`store_id` = s.`id` and  sl.lang_id=' . $this->langid . ' LEFT JOIN  ' . DB_PREFIX . 'language AS l ON s.`lang_id` = l.`id` LEFT JOIN  ' . DB_PREFIX . 'currency AS c ON s.`currency_id` = c.`id`' . $swhere;
            $listData = $this->userStoreMod->querySql($ssql);
        } else {
            $listData = array();
        }
        foreach ($listData as $key => $val) {
            $busSql = "SELECT buss_id FROM " . DB_PREFIX . 'store_business WHERE  store_id=' . $val['id'];
            $busData = $this->userStoreMod->querySql($busSql);
            $listData[$key]['b_id'] = $busData[0]['buss_id'];
        }

        $language = $this->shorthand;
        $this->assign('language', $language);
        //收藏的文章
        $sql_article = 'SELECT article_id FROM  ' . DB_PREFIX . 'user_article  WHERE store_id =  ' . $store_id . ' AND user_id=' . $userId;
        $articleData = $this->colleCtionMod->querySql($sql_article);
        foreach ($articleData as $k => $v) {
            $id[] = $v['article_id'];
        }
        $ids = implode(',', $id);
        if (!empty($ids)) {
            $articleSql = 'SELECT a.id,al.title,al.brif,a.image,a.add_time FROM ' . DB_PREFIX . 'article AS a LEFT JOIN ' . DB_PREFIX . 'article_lang AS al ON a.id=al.article_id WHERE al.lang_id= ' . $lang_id . ' AND a.id in (' . $ids . ')';
            $articleData = $this->colleCtionMod->querySql($articleSql);
        } else {
            $articleData = array();
        }
        //收藏的商品
        $where = ' f.user_id =' . $userId;
        //列表页数据
        $sql = 'select distinct f.*,g.*,l.*,gl.original_img,f.id  from '
            . DB_PREFIX . 'store_goods as g inner join '
            . DB_PREFIX . 'user_collection as f on f.store_good_id = g.id inner join '
            . DB_PREFIX . 'goods_lang as l on l.goods_id = g.goods_id and l.lang_id=' . $lang_id . ' left join '
            . DB_PREFIX . 'goods as gl on g.goods_id = gl.goods_id  where ' . $where . ' and f.store_id =' . $store_id
            . ' group by f.store_good_id order by f.id desc limit 0, 8 ';
//     print_r($sql);exit;
        $data = $this->colleCtionMod->querySql($sql);
        $Collection = array(
            'storeid' => $store_id,
            'lang' => $lang_id,
            'storeData' => $listData,
            'articleData' => $articleData,
            'symbol' => $this->symbol,
            'goodsData' => $data
        );
        $this->setData($Collection,1,'');
    }

    public function couponList() {
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] :0;  //多语言商品
        $userId=!empty($_REQUEST['userId']) ? $_REQUEST['userId'] : 0 ; //用户id
        //抵扣劵
        $userCouponMod=&m('userCoupon');
        $couponData=$userCouponMod->getValidCoupons($userId,$lang,1,0,0);//抵扣劵
        $voucherData=$userCouponMod->getValidCoupons($userId, $lang, 2, 0,0);//兑换劵
        foreach($couponData as $k=>$v){
            $couponData[$k]['start_time']=date("Y-m-d h:i",$v['start_time']);
            $couponData[$k]['end_time']=date("Y-m-d h:i",$v['end_time']);
        }
        foreach($voucherData as $k=>$v){
            $voucherData[$k]['start_time']=date("Y-m-d h:i",$v['start_time']);
            $voucherData[$k]['end_time']=date("Y-m-d h:i",$v['end_time']);
        }
        $data=array(
            'couponData'=>$couponData,
            'voucherData'=>$voucherData
        );
        $this->setData($data,1,'');
    }
    /**
     *抵扣卷领取页面
     * author fup
     * date 2019-07-09
     */
    public function couponReceivableList(){
        $couponMod=&m('coupon');
        $user_id = $this->userId;  //用户ID
        $day = $this->checkUserReceivable($user_id);
//        $user_info = $this->userMod->getOne(array("cond" => "id=" . $this->userId));
        $start_time = strtotime(date('Y-m-d'));
        $end_time = $start_time + (60 * 60 * 24 - 1);
//        $_later = $user_info['add_time'] + 60 * 60 * 24 * 30;
//        $now = time();
//        $day = abs(round(($now-$_later)/3600/24));
        if($day >= 0){
            $is_readable =array('text'=>'仅限新用户领取','code'=>0);
        }else{
            $is_readable =array('text'=>abs($day) .'天后失效','code'=>1);
        }
        $sql = 'SELECT a.*,b.id as bid FROM '.DB_PREFIX. 'coupon as a left join ' . DB_PREFIX . 'user_coupon as b on a.id = b.c_id AND b.user_id = ' . $user_id .' AND b.add_time BETWEEN ' . $start_time . ' AND ' . $end_time . ' WHERE a.type = 1 AND a.is_special_dv = 1 AND a.mark = 1';
//        echo $sql;die;
//        $data = $couponMod->querySql($sql);
        $data['id'] = 21;
        $data['coupon_name'] = '测试特殊卷';
        $data['money'] = '8';
        $data['type'] = '1';
        $data['discount'] = '5';
        $data['total_times'] = '1';
        $data['day_times'] = '1';
        $data['limit_times'] = '1';
        $data['store_value'] = '1';
        $data['total_times'] = '1';
        $data['bid'] = NULL;
//        var_dump($data);die;
        $data['is_readable'] = $is_readable;
        $data['desc'] = array('text'=>'满'.$data['money'] . '元可用','money'=>$data['money'],'discount'=>$data['discount']);
        $this->setData(array($data),1,'');
    }

    /**
     *领取抵扣卷
     * author fup
     * date 2019-07-09
     */
    public function couponReceivable(){
        $couponMod = &m('coupon');
        $userCoupon = &m('userCoupon');
        $user_id = $this->userId;  //用户ID
        $c_id = !empty($_REQUEST['c_id']) ? $_REQUEST['c_id'] : 0;
        if (!$user_id || !$c_id) {
            $this->setData('', 0, '缺少参数！');
        }
        $day = $this->checkUserReceivable($user_id);
//        var_dump($day);die;
        if($day >= 0){
            $this->setData('', 0, '仅限新用户领取哦！');
        }
        $coupon = $couponMod->getOne(array('cond'=>'id = '.$c_id. ' AND type = 1 AND is_special_dv = 1 AND mark = 0'));
        if(empty($coupon)){
            $this->setData('', 0, '卷码不存在！');
        }
        $start_time = strtotime(date('Y-m-d') . '00:00:00');
        $end_time = strtotime(date('Y-m-d') . '23:59:59');
        $info = $userCoupon->getOne(array('cond'=>'user_id = ' . $user_id . ' AND c_id = ' . $c_id . ' AND add_time BETWEEN ' . $start_time . ' AND ' . $end_time));
        if(!empty($info)){
            $this->setData('', 0, '今日已领过劵啦！');
        }
        $data = array(
            'user_id' => $user_id,
            'c_id'    => $c_id,
            'remark'  => '新用户每日领取优惠券',
            'source'  => 3,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'add_user' => $user_id,
            'add_time' => time()

        );
        var_dump($data);die;
//        if($userCoupon->doInsert($data)){
//            $this->setData('',1,'领取成功');
//        }else{
//            $this->setData('',0,'领取失败');
//        }
    }

    /**
     * 获取剩余有效天数
     * @author fup
     * @date 2019-07-09
     */
    private function checkUserReceivable($user_id){
        $user_info = $this->userMod->getOne(array("cond" => "id=" . $user_id));
        $_later = $user_info['add_time'] + 60 * 60 * 24 * 30;
        $now = time();

        return floor(($now-$_later)/3600/24);
    }
}
