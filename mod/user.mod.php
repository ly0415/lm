<?php
/**
 * 管理员模块模型
 * @author: jh
 * @date: 2017/6/21
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class UserMod extends BaseMod {
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct("user");
    }

    /**
     * 注册来源
     * @var array
     */
    public $resource = array(
        1 => 'PC注册',
        2 => '代客下单注册',
        3 => '微信自主注册',
        4 => '微信推荐注册',
    );

    /**
     * 检测注册名称等是否存在
     * @author jh
     * @date 2017/06/21
     */
    public function isExist($type, $value, $id = 0) {
        $cond = "{$type}='{$value}'";
        $cond .= '  and mark =1  ';
        if ($id) {
            $cond .= " AND id!={$id}";
        }
        $query = array('fields' => 'id', 'cond' => $cond);
        $info = $this->getOne($query);
        $id = (int)$info['id'];
        return $id;
    }
    /**
     * 获取单行数据
     * @author jh
     * @date 2017/06/21
     */
    public function getInfo($cond) {
        $query = array('fields' => '*', 'cond' => $cond);
        $info = $this->getOne($query);
        return $info;
    }
    /*
     * 获取一条商家记录
     * @param username
     */
    public function getStoreInfo($username){
        $sql="select u.*,s.store_id,s.store_name from bs_user as u INNER JOIN bs_store as s on u.id = s.user_id  WHERE u.username= '{$username}' AND u.login_type= 'business' AND s.store_state=1 limit 0,1";
        $res=$this->querySql($sql);
        return $res[0];
    }

    /*
     * 根据openid获取用户信息
     * @author  luffy
     * @date    2016-08-20
     */
    public function getUserInfo($wx_openid){
        $sql = "select id,username,phone FROM bs_user WHERE openid = '{$wx_openid}' AND mark = 1 AND is_use = 1";
        $res = $this->querySql($sql);
        return $res[0];
    }


    /*
     * 获取用户的下属推荐会员
     * @author  gao
     * @date    2019-01-21
     */
    public function getChildUser($pid = 0, $level = 1,$limit=0,$personUserData=0,$parentUserData=0) {
        $sql = "select id,username,phone,point,amount,add_time,phone_email from bs_user  where phone_email = {$pid}";
        $data = $this->querySql($sql);
        $res = array();
        if(!empty($parentUserData)){
            $parentUserData['level']=-1;
            $res[]=$parentUserData;
        }
        if(!empty($personUserData)){
            $personUserData['level']=0;
            $res[]=$personUserData;

        }
        foreach($data as $v){
            if($level < $limit){
                $v['level'] = $level;
                $child = $this->getChildUser($v['phone'], $level + 1,$limit);
                $res[] = $v;
                if (!empty($child)) {
                    $res = array_merge($res,$child);
                }
            }
        }
        return $res;
    }

    /*
    * 获取用户的上级推荐会员
    * @author  gao
    * @date    2019-01-21
    */
    public function getParentUser($list,$pid = 0, $level = 1,$personUserData=0) {
         static $tree = array();
            if(!empty($personUserData)){
                $tree[]=$personUserData;
            }
        foreach ($list as $k=> $v) {

                if ($v['phone'] == $pid) {
                    $v['level']=$level;
                    $tree[] = $v;
                    unset($list[$k]);
                    if($v['phone_email']!=0){
                        $this->getParentUser($list,$v['phone_email'],$level+1);
                    }
                }

        }
        return $tree;
    }

    /**
     * 注册成功发送消息提醒
     * @author tangp
     * @date 2019-02-13
     * @param int $phone
     * @return bool
     */
    public function sendSms($phone)
    {
        include_once ROOT_PATH."/includes/AliDy/sendSms.lib.php";

        $params = array();
        $params['PhoneNumbers'] = $phone;
        $params['SignName'] = "艾美睿零售";
        $params['TemplateCode'] = 'SMS_157685577';
        $phoneCode = new sendSms($params);
        $info = $phoneCode->sendSms();
        $info1 = json_decode(json_encode($info),true);

        if ($info1['Message']=='OK'){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 赠券发送提醒的短信
     * @param
     * @return bool
     * @author tangp
     * @date 2019-02-13
     */
    public function sendMessage($id)
    {
        include_once ROOT_PATH."/includes/AliDy/sendSms.lib.php";
        $params = array();
        $sql = "SELECT phone,username FROM bs_user WHERE id=".$id;
        $userMod = &m('user');
        $res = $userMod->querySql($sql);

        $params['PhoneNumbers'] = $res[0]['phone'];
        $params['SignName'] = "艾美睿零售";
        $params['TemplateCode'] = 'SMS_159620070';
        $params['TemplateParam'] = array(
            "name" => $res[0]['username']
        );
        $phoneCode = new sendSms($params);
        $info = $phoneCode->sendSms();
        $info1 = json_encode(json_encode($info),true);

        if ($info1['Message']=='Ok'){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 根据手机号获取用户信息，没有则注册
     */
    public function getInfoByPhone($phone, $store_id = 0, $store_cate_id = 0)
    {
        //获取用户信息
        $sql = 'select id,phone,point,amount from  ' . DB_PREFIX . 'user  where  mark =1 and is_use=1 and phone = ' . $phone;
        $res = $this->querySql($sql);
        $userInfo = $res[0];
        if (empty($userInfo)) {//用户不存在，自动注册
            //获取注册积分
            $pSql = "SELECT *  FROM " . DB_PREFIX . 'user_point_site';
            $res = $this->querySql($pSql);
            $register_point = $res[0]['register_point'];
            $tmp = array(
                'phone' => $phone,
                'username' => $phone,
                'password' => md5('123456'),
                'add_time' => time(),
                'login_type' => 'member',
                'store_id' => $store_id,
                'store_cate_id' => $store_cate_id,
                'point' => $register_point,
                'resource' => 2,
            );
            $user_id = $this->doInsert($tmp);
            //注册日志
            $logData = array(
                'operator' => '--',
                'username' => $phone,
                'add_time' => time(),
                'note' => '注册获得' . $register_point . '睿积分',
                'userid' => $user_id,
                'deposit' => $register_point,
                'expend' => '-',
            );
            $pointLogMod = &m("pointLog");
            $pointLogMod->doInsert($logData);

            //注册成功发短信
            $this->sendSms($phone);
            //获取设置注册送电子券
            $systemConsoleMod = &m('systemConsole');
            $userCoupon =& m('userCoupon');
            $getCouponActivityStatus = $systemConsoleMod->getCouponActivityStatus();//获取设置注册送电子券是否开启
            if ($getCouponActivityStatus['1'] == 1) {
                $coupon = $systemConsoleMod->getSetCoupon();
                $duiCoupon = $systemConsoleMod->getSetDuiCoupon();
                $limitTiems = $coupon['limit_times'] * 3600 * 24;
                if (!empty($coupon)) {
                    $data = array(
                        'c_id' => $coupon[0]['id'],
                        'remark' => '注册送抵扣券',
                        'add_time' => time(),
                        'start_time' => time(),
                        'end_time' => time() + $limitTiems,
                        'user_id' => $user_id
                    );
                    $userCoupon->doInsert($data);
                    //赠券发短信提醒
                    $this->sendMessage($user_id);
                }
                if (!empty($duiCoupon)) {
                    $data = array(
                        'c_id' => $coupon[0]['id'],
                        'remark' => '注册送兑换券',
                        'add_time' => time(),
                        'start_time' => time(),
                        'end_time' => time() + $limitTiems,
                        'user_id' => $user_id
                    );
                    $userCoupon->doInsert($data);
                    //赠券发短信提醒
                    $this->sendMessage($user_id);
                }

            }
            $res = $this->querySql($sql);
            $userInfo = $res[0];
        }
        return $userInfo;
    }
}