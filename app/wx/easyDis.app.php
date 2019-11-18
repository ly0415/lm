<?php

/**
 * 分销页面
 * @author wangh
 * @date 2017/08/22
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class EasyDisApp extends BaseWxApp {

    private $isFx = 2;
    private $freeze = 2;
    private $status =2;
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        //语言包
        $this->load($this->shorthand, 'WeChat/Easydis');
        $this->assign('langdata', $this->langData);
        $this->assign('lang', $this->langid);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);

        //判断是否登录 -> 登录页面
        if (empty($this->userId)) {
            $url = "wx.php?app=user&act=loginOuth&storeid=" . $this->storeid . "&lang=" . $this->langid . "&auxiliary=" . $auxiliary.'&latlon='.$latlon;
            header("Location:$url");
        } else {
            //判断是否为分销人员
//            $this->getIsfx($this->userId);
//            if ($this->isFx == 2) {  //如果不是成为分销人员 -> 申请页面
//                $is_check = $this->isfxUser();
//                if (empty($is_check)) {
//                    $url = "wx.php?app=fxUser&act=apply&storeid={$this->storeid}&lang={$this->langid}&auxiliary={$auxiliary}&latlon={$latlon}";
//                    header("Location:$url");
//                } elseif ($is_check == 1) {
//                    $this->display('fxuser/checking.html');
//                } elseif ($is_check == 3) {
//                    $this->display('fxuser/fail.html');
//                }
//            } else { //是分销人员
////                $this->getfreeze();
////                if ($this->freeze == 2) {
////                    $this->display('easydis/freeze.html');
////                    exit;
////                }
//                $ress= $this->getStatus();
//                if ($ress==2){
//                    $this->display('easydis/freeze.html');
//                }
//            }
            $fxUserMod =&m('fxuser');
            $sql = "SELECT * FROM bs_fx_user WHERE user_id =".$this->userId;
            $res = $fxUserMod->querySql($sql);
            if (empty($res)){
                $url = "wx.php?app=fxUser&act=apply&storeid={$this->storeid}&lang={$this->langid}&auxiliary={$auxiliary}&latlon={$latlon}";
                header("Location:$url");
            }else{
                if ($res[0]['is_check'] == 1){
                    $this->display('fxuser/checking.html');
                }elseif ($res[0]['is_check'] == 3){
                    $this->display('fxuser/fail.html');
                }elseif ($res[0]['status'] == 2){
                    $this->display('easydis/freeze.html');
                }
            }
        }
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }
    public function getStatus(){
        $userMod = &m('fxuser');
        $sql = 'select status from bs_fx_user where user_id =' . $this->userId;
        $data = $userMod->querySql($sql);
//        if ($data) {
//            $this->status = $data[0]['status'];
//        }
        return $data[0]['status'];
    }
    public function isfxUser() {
        $fxUserMod = &m('fxuser');
        $sql = 'select  id,is_check   from bs_fx_user  where  store_cate = ' . $this->countryId . '  AND  user_id = ' . $this->userId;
        $data = $fxUserMod->querySql($sql);
        if (!empty($data)) {
            return $data[0]['is_check'];
        } else {
            return array();
        }
    }

    /**
     * 微信授权，静默方式
     */
//    public function outh(){
//        $redirectUrl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'?app=easyDis&act=index'; //http://127.0.0.1/bspm711/wx.php?app=easyDis&act=index
//        $url = $this ->getOAuthUrl($redirectUrl,'snsapi_base',1);
//        header("Location:".$url);
//    }

    /**
     * 分销页面
     */
    public function index() {
        //语言包
        $this->load($this->shorthand, 'WeChat/Easydis');
        $this->assign('langdata', $this->langData);
        $fxUserMod = &m('fxuser');
        $fxOrderMod = &m('fxOrder');
        $fxOutmoneyApply = &m('fxOutmoneyApply');
        //如果cookie过期了，，重新获取
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        if (!isset($_COOKIE['wx_openid'])) {
            $url = "wx.php?app=user&act=loginOuth&storeid=" . $this->storeid . '&lang=' . $this->langid . '&auxiliary=' . $auxiliary.'&latlon='.$latlon;
            header("Location:$url");
        }
        //从数据库判断获取数据
        $mysql_uinfo = $this->getOpenid();
        if (empty($mysql_uinfo['openid'])) {
            //从公众号拉取 ，放到数据库
            //回调页面
//            $code = $_REQUEST['code'];
//            $accessTokenInfo = $this -> getoAuthAccessToken($code);
//            $OuthToken = $accessTokenInfo -> access_token;
//            $openid = $accessTokenInfo -> openid;
//            $userInfo = $this ->getUserInfo($OuthToken,$openid);
            //从cookie 里 获取openid
            $wx_openid = $_COOKIE['wx_openid'];
            $wx_nickname = $_COOKIE['wx_nickname'];
            $wx_city = $_COOKIE['wx_city'];
            $wx_province = $_COOKIE['wx_province'];
            $wx_country = $_COOKIE['wx_country'];
            $wx_headimgurl = $_COOKIE['wx_headimgurl'];
            $wx_sex = $_COOKIE['wx_sex'];

            $wx_userInfo = array(
                'openid' => $wx_openid,
                'nickname' => $wx_nickname,
                'city' => $wx_city,
                'province' => $wx_province,
                'country' => $wx_country,
                'headimgurl' => $wx_headimgurl,
                'sex' => $wx_sex,
            );

            //向数据库插入微信信息
            $this->insertWxinfo($wx_userInfo);

            $headimg = $wx_headimgurl;
            $nickname = $wx_nickname;
        } else {
            $headimg = $mysql_uinfo['headimgurl'];
            $nickname = $mysql_uinfo['username'];
        }
        $this->assign('headimg', $headimg);
        $this->assign('nickname', $nickname);

        $userid = $this->userId;
        $sql3= "SELECT * FROM bs_fx_user WHERE user_id=".$userid;
        $info = $fxUserMod->querySql($sql3);
        $sqlll = "SELECT SUM(apply_money) AS moneys FROM bs_fx_outmoney_apply WHERE fx_user_id={$info[0]['id']} AND is_check=2";
        $outmoney = $fxOutmoneyApply->querySql($sqlll);
        if ($info[0]['level'] == 3){
            $sql = "SELECT fs.discount,fo.fx_discount,o.order_id,o.order_state,fs.level,fu.*,fo.add_time,o.goods_amount,fo.order_sn,fo.pay_money,o.order_state,o.order_amount FROM bs_fx_order AS fo 
              LEFT JOIN bs_order AS o ON fo.order_id = o.order_id 
              LEFT JOIN bs_fx_rule AS fu ON fo.rule_id = fu.id 
              LEFT JOIN bs_fx_user AS fs ON fo.user_id = fs.user_id
              WHERE fo.fx_user_id={$info[0]['id']}";
            $data = $fxOrderMod->querySql($sql);

            foreach($data as $k=>$v){
                $data[$k]['fxmoney']=number_format($v['pay_money']*($v['lev3_prop']-$v['discount'])/100,2,'.','');
            }
            $counts = count($data);
            $sum = 0;
            foreach($data as $item){
                $sum += $item['pay_money'];
            }
//            echo '<pre>';var_dump($counts);
            $sql2 = "SELECT fs.discount,o.order_id,o.order_state,fs.level,fu.*,fo.add_time,o.goods_amount,fo.order_sn,fo.pay_money,o.order_state,o.order_amount FROM bs_fx_order AS fo 
              LEFT JOIN bs_order AS o ON fo.order_id = o.order_id 
              LEFT JOIN bs_fx_rule AS fu ON fo.rule_id = fu.id 
              LEFT JOIN bs_fx_user AS fs ON fo.user_id = fs.user_id
              WHERE fo.fx_user_id={$info[0]['id']} AND o.order_state=50";
            $data1 = $fxOrderMod->querySql($sql2);
            foreach($data1 as $k=>$v){
                $data1[$k]['fxmoney']=number_format($v['pay_money']*($v['lev3_prop']-$v['discount'])/100,2,'.','');
            }
            $sums=0;
            foreach($data1 as $item){
                $sums += $item['fxmoney'];
            }
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
        }
        // 优惠变更申请

        $fx_tree = $fxUserMod->getOne(array("cond" => "user_id=" . $userid));
        $this->assign('uname', $this->userName);
        //统计分销人数下级
        $count_user = $fxUserMod->countUser($fx_tree['id'],$fx_tree['level']);
        $this->assign('count_user', $count_user);
        $this->assign('fx_tree', $fx_tree);
        $this->assign('outmoney', $outmoney[0]);
        $this->assign('counts',$counts);
        $this->assign('sum',$sum);
        $this->assign('sums',$sums);
        $this->assign('info',$info);
        $this->assign('lang', $this->langid);
        $this->display('easydis/index.html');
    }

    /**
     * 提现
     */
    public function cash() {
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        //获取fx_user_id
        $fxUserMod = &m('fxuser');
        $fxUser = $fxUserMod->getOne(array('cond'=>'user_id='.$this->userId));
        //判断是否有正在处理的提现申请
        $applyMod = &m('fxOutmoneyApply');
        $cond = 'fx_user_id=' . $fxUser['id'];
        $data = $applyMod->getOne(array('cond'=>$cond . ' and is_check = 1', 'order by id desc'));
        if (!empty($data)) {
            $this->display('easydis/cashing.html');
        }
        $this->assign('data', $data);
        $this->assign('user', $fxUser);
        $this->assign('fx_user_id', $fxUser['id']);
        $this->assign('store_id', $fxUser['store_id']);
        $this->assign('bank_name', $fxUser['bank_name']);
        $this->assign('bank_account', $fxUser['bank_account']);
        $this->assign('lang', $this->langid);
        $this->display('easydis/cash.html');
    }

    /**
     * 提现处理
     */
    public function doCash() {
        //加载语言包
        $this->load($_REQUEST['languages'], 'WeChat/Easydis');
        $a = $this->langData;
        // 结算
        $storeId = !empty($_POST['storeid']) ? $_POST['storeid'] : 0;
        $lang = !empty($_POST['lang']) ? $_POST['lang'] : $this->mrlangid;
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
            'source' => 1,
            'add_time' => time()
        );
        $res = $outmoneyMod->doInsert($data);
        if ($res) {
            $info['url'] = "wx.php?app=easyDis&act=cashLog&storeid={$storeId}&lang={$lang}&auxiliary={$auxiliary}&latlon={$latlon}";
            $this->setData($info, 1, '提现申请成功');
        } else {
            $this->setData(array(), 0, '提现申请失败');
        }
    }

    public function getFxSite($storeid) {
        $fxSiteMod = &m('fxSite');
        $sql = 'select  *  from  bs_fx_site where mark = 1 and store_id = ' . $storeid;
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
     */
    public function cashLog() {

        $user_id = $this->userId;
        $storeid = $this->storeid;  //所选的站点id
        //语言包
        $this->load($this->shorthand, 'WeChat/Easydis');
        $this->assign('langdata', $this->langData);
        $fxLogMod = &m('fxOutmoneyApply');
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon',$latlon);
        $this->assign('auxiliary', $auxiliary);
        //本月提现记录
        $this_month = $this->thisMonth();
     
        $fxUserSql="select id from bs_fx_user where user_id=".$user_id;

        $fxUserInfo=$fxLogMod->querySql($fxUserSql);
        $fxUserId=$fxUserInfo[0]['id'];

        $where1 = "fx_user_id = " . $fxUserId  . " and add_time >= " . $this_month['begin_month'] . " and add_time <= " . $this_month['end_month'];
        $sql1 = 'select * from  bs_fx_outmoney_apply  where ' . $where1;
        $this_data = $fxLogMod->querySql($sql1);
        $this->assign("this_data", $this_data);

        //上月提现记录
        $last_month = $this->lastMonth();
        $where ="fx_user_id = " . $fxUserId  . " and add_time >= " . $last_month['begin_month'] . " and add_time <= " . $last_month['end_month'];
        $last_data = $fxLogMod->getData(array("cond" => $where));
        $this->assign("last_data", $last_data);

        //提现总金额
        $where = "fx_user_id = " . $fxUserId ;
        $sum_money = $fxLogMod->getData(array("cond" => $where, "fields" => "sum(apply_money) as money"));
        $this->assign("sum_money", $sum_money[0]);
        $this->assign('lang', $this->langid);
        $this->display('easydis/cashlog.html');
    }

    /**
     * 我的订单
     */
    public function fxOrder() {
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        $this->assign('auxiliary', $auxiliary);
        $fxOrderMod = &m('fxOrder');
        $fxuserMod = &m('fxuser');
        $sql1 = "select * from bs_fx_user where user_id=".$this->userId;
        $info = $fxuserMod->querySql($sql1);
        if ($info[0]['level'] == 3){
            $sql = "SELECT fs.discount,fo.fx_discount,fs.discount,o.order_id,o.order_state,fs.level,fu.*,fo.add_time,o.goods_amount,fo.order_sn,fo.pay_money,o.order_state,o.order_amount FROM bs_fx_order AS fo 
              LEFT JOIN bs_order AS o ON fo.order_id = o.order_id 
              LEFT JOIN bs_fx_rule AS fu ON fo.rule_id = fu.id 
              LEFT JOIN bs_fx_user AS fs ON fo.fx_user_id = fs.id
              WHERE fo.fx_user_id=".$info[0]['id']." order by fo.add_time desc";
//            echo $sql;die;
            $data = $fxOrderMod->querySql($sql);
//            echo '<pre>';var_dump($data);die;
            foreach($data as $k=>$v){
                $data[$k]['fxmoney']=number_format($v['pay_money']*($v['lev3_prop']-$v['discount'])/100,2,'.','');
                $data[$k]['discount_money']=number_format($v['goods_amount']*$v['discount']/100,2,'.','');
            }

//            echo '</pre>';var_dump($data);die;
            $this->assign('data',$data);
            $this->assign('lang', $this->langid);
            if (empty($data)){
                $this->display('easydis/noOrder.html');
            }else{
                $this->display('easydis/order.html');
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
                      left join bs_fx_rule as fr on fo.rule_id = fr.id where fx_user_id in (".$thirdFxUserIds.")  order by fo.add_time desc";
            $data = $fxOrderMod->querySql($sql6);

            foreach($data as $k=>$v){
                $prop = $v['lev1_prop']/100;
                $data[$k]['fxmoney']=number_format($v['pay_money'] *$prop,2,'.','');
                $data[$k]['discount_money']=number_format($v['goods_amount']*$v['discount']/100,2,'.','');
            }

            $this->assign('data',$data);
            $this->assign('lang', $this->langid);
            if (empty($data)){
                $this->display('easydis/noOrder.html');
            }else{
                $this->display('easydis/order.html');
            }
        }elseif ($info[0]['level'] == 2){
            //查出该二级用户下的三级用户的订单
            $sss = "select id from bs_fx_user where parent_id={$info[0]['id']} and level=3";
            $result =$fxuserMod->querySql($sss);
            foreach ($result as $v){
                $ids[] = $v['id'];
            }
            $two_ids    = implode(',',$ids);
            $sql = "select fu.discount,fu.level,fr.lev1_prop,fr.lev2_prop,fr.lev3_prop,o.goods_amount,fo.pay_money,o.order_state,fo.add_time,fo.order_id,fo.order_sn,o.order_amount from bs_fx_order as fo
                  left join bs_order as o on fo.order_id = o.order_id
                  left join bs_fx_user as fu on fu.id = fo.fx_user_id
                  left join bs_fx_rule as fr on fo.rule_id = fr.id where fx_user_id in ({$two_ids}) order by fo.add_time desc";
            $data = $fxOrderMod->querySql($sql);
            foreach ($data as $k => $v){
                $prop = $v['lev2_prop']/100;
                $data[$k]['fxmoney'] = number_format($v['pay_money'] * $prop,2,'.','');
                $data[$k]['discount_money']=number_format($v['goods_amount']*$v['discount']/100,2,'.','');
            }

            $this->assign('data',$data);
            $this->assign('prop',$prop);
            if (empty($data)){
                $this->display('easydis/noOrder.html');
            }else{
                $this->display('easydis/order.html');
            }
        }



    }

    /**
     * 我的订单
     */
    public function detail() {
        $orderMod = &m('order');
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);

        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        $orderid = $_REQUEST['orderid'];
        $sql = 'SELECT o.order_amount,o.`order_id`,o.`buyer_name`,o.`buyer_email`,o.`buyer_phone`,g.`goods_id`,g.`goods_name`,g.`goods_price`,g.`goods_num`,g.goods_pay_price,g.spec_key_name
                 FROM  bs_order AS o
                 LEFT JOIN bs_order_goods AS g ON  o.`order_sn` = g.`order_id`  WHERE o.`order_id` = ' . $orderid;

        $data = $orderMod->querySql($sql);

        $this->assign("data", $data);
        //语言包
        $this->load($this->shorthand, 'WeChat/Easydis');
        $this->assign('langdata', $this->langData);
        $this->assign('lang', $this->langid);
        $this->display('easydis/orderDetail.html');
    }

    /**
     * 我的团队
     * @author Run
     * @date 2018-10-12
     */
    public function fxTeam() {
        $fxUserMod = &m('fxuser');
//        $whe = " where a.user_id= 523";
//        $whe = " where user_id= 349";
        $whe = " where a.user_id=". $this->userId;
        $sql = " SELECT a.*,b.headimgurl FROM bs_fx_user as a
LEFT JOIN bs_user as b on b.id = a.user_id " .$whe;
        $data = $fxUserMod->querySql($sql);
        $level = $data[0]['level'];
        if ($level == 1) {
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
        } else if ($level == 2) {
            foreach ($data as $key => $val) {
                $lev3 = $this->getchilds($val['id']);
                $data[$key]['fx_level'] = $fxUserMod->level[$val['level']];
                $data[$key]['childs'] = $lev3;
                $data[$key]['count'] = count($lev3);
            }
        }
        $this->assign('level', $level);
        $this->assign('data', $data);
        //语言包
        $this->load($this->shorthand, 'WeChat/Easydis');
        $this->assign('langdata', $this->langData);
        $this->assign('lang', $this->langid);
        $this->display('easydis/myteam.html');
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
     * 我的会员
     * @author Run
     * @date 2018-10-12
     */
    public function fxUser() {
        $fxuserMod = &m('fxuser');
        $fxUserAccountMod = &m('fxUserAccount');
        $id = !empty($_REQUEST['id']) ? htmlspecialchars(trim($_REQUEST['id'])) : '0';
        $info     = $fxuserMod  ->getRow($id);
        $data = $fxUserAccountMod->checkUserAccount($id);
        $info['count'] = count($data);//会员数量
        $this->assign('info', $info);
        $this->assign('data', $data);
        $this->display('easydis/myUser.html');
    }
    /**
     * 分享名片
     */
    public function mycard() {
        $userMod = &m('fxuser');
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);

        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        $sql = 'select id,user_id,fx_img,freeze,fx_code  from bs_fx_user where user_id =' . $this->userId;
        $data = $userMod->querySql($sql);
        $this->assign('data', $data[0]);
        //语言包
        $this->load($this->shorthand, 'WeChat/Easydis');
        $this->assign('langdata', $this->langData);
        $this->assign('lang', $this->langid);
        $this->display('easydis/card.html');
    }

    /**
     * 优惠分销申请
     * 必须是3级分销用户
     */
    public function disApply() {
        //语言包
        $this->load($this->shorthand, 'WeChat/Easydis');
        $this->assign('langdata', $this->langData);
        $fxUserMod = &m('fxuser');
        $fxruleMod = &m('fxrule');
        $disMod = &m('fxDiscountLog');
        $userid = $this->userId;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        //分销人员的个人信息
        $fx_info = $fxUserMod->getOne(array("cond" => "user_id=" . $userid));
        $this->assign('fx_info', $fx_info);
        //判断是否有正在审核的申请
        $is_check = $disMod->haveCheck($fx_info['id']);
        if($is_check){
            $this->display('easydis/disapplying.html');
        }
        //查询分销人员分润比例
        $getLev3Percent = $fxruleMod->getLev3Percent($fx_info['id']);
        $this->assign('getLev3Percent', $getLev3Percent);
        $this->assign('lang', $this->langid);
        $languages = $this->shorthand;
        $this->assign('languages', $languages);
        $this->display('easydis/disapply.html');
    }

    public function doDisApply() {
        //语言包
        $a = $this->langData;
        $this->load($_REQUEST['languages'], 'WeChat/Easydis');
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';

        $userid = ($_REQUEST['userid']) ? $_REQUEST['userid'] : '';
        $old_discount = ($_REQUEST['discount']) ? trim($_REQUEST['discount']) : '';
        $new_discount = ($_REQUEST['new_discount']) ? $_REQUEST['new_discount'] :0;
        $lev3_prop = ($_REQUEST['lev3_prop']) ? trim($_REQUEST['lev3_prop']) : '';
        $mod = &m('fxDiscountLog');
        if (!isset($new_discount) ) {
            $this->setData(array(), $status = '0', $a['order_Pleasefillin']);
        }
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $new_discount)) {
            $this->setData($info = array(), $status = '0', $a['order_Incorrect']);
        }

        if ($lev3_prop < $new_discount) {
            $this->setData(array(), $status = '0', $a['order_Profitratio']);
        }
        if ($old_discount == $new_discount) {
            $this->setData(array(), $status = '0', '申请比例与当前比例一致无需变更！');
        }

        $arr = array(
            'fx_user_id' => $userid,
            'fx_discount' => $new_discount,
            'old_discount' => $old_discount,
            'add_time' => time(),
            // 'lev3_prop' => $lev3_prop,
            'current_rule_percent' => $lev3_prop,  // 更新 current_rule_percent 字段 by xt 2019.03.06
            'source' => 1
        );
        $r = $mod->doInsert($arr);

        if ($r) {
            $this->setData(array("url" => "wx.php?app=easyDis&act=index&storeid=" . $this->storeid . "&lang=" . $this->langid . "&auxiliary=" . $auxiliary.'&latlon='.$latlon), $status = '1', $a['order_Success']);
        } else {
            $this->setData(array(), $status = '0', $a['order_failure']);
        }
    }

    public function getIsfx($userid) {
        $fxuserMod = &m('fxuser');
        $sql = 'select id from bs_fx_user where user_id =' . $userid . '  and  store_cate =' . $this->countryId . '  limit 1';
        $data = $fxuserMod->querySql($sql);

        if (!empty($data)) {
            $this->isFx = 1;
        }
    }

    public function getfreeze() {
        $userMod = &m('fxuser');
        $sql = 'select id,user_id,fx_img,freeze,fx_code  from bs_fx_user where user_id =' . $this->userId;
        $data = $userMod->querySql($sql);
        if ($data) {
            $this->freeze = $data[0]['freeze'];
        }
    }

    /*
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

    /*
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

    public function dd($info) {

        if (is_object($info) || is_array($info)) {
            $info_text = var_export($info, true);
        } elseif (is_bool($info)) {
            $info_text = $info ? 'true' : 'false';
        } else {
            $info_text = $info;
        }

        file_put_contents('./dd.txt', $info_text);
    }

    /**
     *
     * 通过跳转获取用户的openid，跳转流程如下：
     * 1、设置自己需要调回的url及其其他参数，跳转到微信服务器https://open.weixin.qq.com/connect/oauth2/authorize
     * 2、微信服务处理完成之后会跳转回用户redirect_uri地址，此时会带上一些参数，如：code
     *
     * @return 用户的openid
     */
    public function _GetOpenid() {
        //通过code获得openid
        if (!isset($_GET['code'])) {
            //触发微信返回code码
            $baseUrl = urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            $url = $this->__CreateOauthUrlForCode($baseUrl);
            Header("Location: $url");
            exit();
        } else {
            //获取code码，以获取openid
            $code = $_GET['code'];
            $openid = $this->GetOpenidFromMp($code);
//            $this->setWxCook($code);
            return $openid;
        }
    }

    /**
     *
     * 构造获取code的url连接
     * @param string $redirectUrl 微信服务器回跳的url，需要url编码
     *
     * @return 返回构造好的url
     */
    private function __CreateOauthUrlForCode($redirectUrl) {
        $urlObj["appid"] = 'wxa07a37aef375add1';
        $urlObj["redirect_uri"] = $redirectUrl;
        $urlObj["response_type"] = "code";
        $urlObj["scope"] = "snsapi_base";
        $urlObj["state"] = "STATE" . "#wechat_redirect";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?" . $bizString;
    }

    /**
     *
     * 通过code从工作平台获取openid机器access_token
     * @param string $code 微信跳转回来带上的code
     *
     * @return openid
     */
//    public function GetOpenidFromMp($code) {
//        $url = $this->__CreateOauthUrlForOpenid($code);
//        //初始化curl
//        $ch = curl_init();
//        //设置超时
//        curl_setopt($ch, CURLOPT_TIMEOUT, $this->curl_timeout);
//        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
//        curl_setopt($ch, CURLOPT_HEADER, FALSE);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
////        if(WxPayConfig::CURL_PROXY_HOST != "0.0.0.0"
////            && WxPayConfig::CURL_PROXY_PORT != 0){
////            curl_setopt($ch,CURLOPT_PROXY, WxPayConfig::CURL_PROXY_HOST);
////            curl_setopt($ch,CURLOPT_PROXYPORT, WxPayConfig::CURL_PROXY_PORT);
////        }
//        //运行curl，结果以jason形式返回
//        $res = curl_exec($ch);
//        curl_close($ch);
//        //取出openid
//        $data = json_decode($res, true);
//        $this->data = $data;
//        $openid = $data['openid'];
//        return $openid;
//    }

    /**
     *
     * 构造获取open和access_toke的url地址
     * @param string $code，微信跳转带回的code
     *
     * @return 请求的url
     */
    private function __CreateOauthUrlForOpenid($code) {
        $urlObj["appid"] = 'wxa07a37aef375add1';
        $urlObj["secret"] = 'ce3e519287e84c68fbd63b74b7ea501f';
        $urlObj["code"] = $code;
        $urlObj["grant_type"] = "authorization_code";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://api.weixin.qq.com/sns/oauth2/access_token?" . $bizString;
    }

    /**
     *
     * 拼接签名字符串
     * @param array $urlObj
     *
     * @return 返回已经拼接好的字符串
     */
    private function ToUrlParams($urlObj) {
        $buff = "";
        foreach ($urlObj as $k => $v) {
            if ($k != "sign") {
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }

    public function arraytoxml($data) {
        $str = '<xml>';
        foreach ($data as $k => $v) {
            $str .= '<' . $k . '>' . $v . '</' . $k . '>';
        }
        $str .= '</xml>';
        return $str;
    }

    public function xmltoarray($xml) {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring), true);
        return $val;
    }

    public function curl($param = "", $url) {

        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();                                      //初始化curl
        curl_setopt($ch, CURLOPT_URL, $postUrl);                 //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);                    //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);            //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);                      //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);           // 增加 HTTP Header（头）里的字段
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);        // 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSLCERT, ROOT_PATH . '/includes/libraries/WxPaysdk/cert/apiclient_cert.pem'); //这个是证书的位置绝对路径
        curl_setopt($ch, CURLOPT_SSLKEY, ROOT_PATH . '/includes/libraries/WxPaysdk/cert/apiclient_key.pem'); //这个也是证书的位置绝对路径
        $data = curl_exec($ch);                                 //运行curl
        curl_close($ch);
        return $data;
    }

    /**
     *
     * 产生随机字符串，不长于32位
     * @param int $length
     * @return 产生的随机字符串
     */
    public function getNonceStr($length = 32) {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

}
