<?php

/**
 * Created by PhpStorm.
 * User: wangh
 * Date: 2017/11/16
 * Time: 15:44
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class FxmemberApp extends BaseStoreApp {

    private $lang_id;
    private $fxuserMod;
    private $fxruleMod;
    private $fxuserMoneyMod;
    private $fxuserRuleMod;
    private $fxuserTreeMod;
    private $userMod;
    private $storeMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
        $this->assign('lang_id', $this->lang_id);
        $this->fxuserMod = &m('fxuser');
        $this->fxuserMoneyMod = &m('fxuserMoney');
        $this->fxuserRuleMod = &m('fxuserRule');
        $this->fxuserTreeMod = &m('fxuserTree');
        $this->fxruleMod = &m('fxrule');
        $this->userMod = &m('user');
        $this->storeMod = &m('store');
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    /**
     * 分销人员树
     */
    public function memlist() {
        if($this->storeInfo['store_type'] == 1 ){
            $ids = $this->getStoreIds($this->storeId);
        }else{
            $ids = $this->storeId;
        }
        //获取一级分销
//        $sql = ' SELECT  t.*,u.real_name,u.`bank_name`,u.`bank_account`,u.`email`,u.`add_time`,u.`telephone`,u.`freeze`,u.`id` as uid,m.`money`
//                  FROM  bs_fx_usertree AS t LEFT JOIN bs_fx_user AS u ON t.`user_id` = u.`user_id`
//                  LEFT JOIN  bs_fx_user_money AS m ON m.`user_id` = t.`user_id`
//                  WHERE  t.`fx_level` = 1  AND  t.pid=0  and is_check =2  AND  u.`store_id`  = ' . $this->storeId . '  and   m.`store_id` =' . $this->storeId;

        $sql = ' SELECT  t.*,u.real_name,u.`bank_name`,u.`bank_account`,u.`email`,u.`add_time`,u.`telephone`,u.`freeze`,u.`id` as uid,u.`store_id`
                  FROM  bs_fx_usertree AS t LEFT JOIN bs_fx_user AS u ON t.`user_id` = u.`user_id`
                  WHERE  t.`fx_level` = 1  AND  t.pid=0  and is_check =2  AND  u.`store_id`  in (' . $ids .')' ;


        $res = $this->fxuserTreeMod->querySql($sql);
        foreach ($res as $key => $val) {
            $store_name = $this->storeMod->getNameById($val['store_id'],$this->defaulLang);
            $res[$key]['store_name'] = $store_name;
            $lev2 = $this->getlev2($val['id']);
            if (!empty($lev2)) {
                // 2 级
                $res[$key]['childs'] = $lev2;
                // 3级
                foreach ($lev2 as $k => $v) {
                    $lev3 = $this->getlev3($v['id']);
                    if (!empty($lev3)) {
                        $res[$key]['childs'][$k]['childs'] = $lev3;
                    }
                }
            }
        }
        $this->assign('res', $res);
        $this->display('fxmember/memberList.html');
    }

    public function getlev2($pid) {
        $sql = ' SELECT  t.*,u.real_name,u.`bank_name`,u.`bank_account`,u.`email`,u.`add_time`,u.`telephone`,u.`freeze`,u.`id` as uid
                 FROM  bs_fx_usertree AS t LEFT JOIN bs_fx_user AS u ON t.`user_id` = u.`user_id`
                 WHERE  t.`fx_level` = 2  AND  t.pid= ' . $pid ;
        $res = $this->fxuserTreeMod->querySql($sql);

        return $res;
    }

    public function getlev3($pid) {
        $sql = ' SELECT  t.*,u.real_name,u.`bank_name`,u.`bank_account`,u.`email`,u.`add_time`,u.`telephone`,u.`freeze`,u.`id` as uid
                 FROM  bs_fx_usertree AS t LEFT JOIN bs_fx_user AS u ON t.`user_id` = u.`user_id`
                  WHERE  t.`fx_level` = 3  AND  t.pid= ' . $pid ;
        $res = $this->fxuserTreeMod->querySql($sql);
        return $res;
    }

    /**
     * 分销人员的添加
     */
    public function memadd() {
        //一级分销人员
        $sql_lev1 = 'SELECT  t.id,u.`real_name`,t.`user_id`  FROM  bs_fx_usertree AS t
                      LEFT JOIN  bs_fx_user  AS u ON t.`user_id` = u.`user_id`
                      WHERE  t.`fx_level` = 1  AND  u.is_check = 2  and  u.freeze = 1  AND  u.store_id =' . $this->storeId;
        $res_lev1 = $this->fxuserTreeMod->querySql($sql_lev1);

        $this->assign('res_lev1', $res_lev1);
        //分销规则
        $sql_rule = "SELECT  *  FROM  bs_fx_rule  WHERE  mark = 1 AND  (FIND_IN_SET('" . $this->storeId . "',store_id)  OR store_id =0)";
        $res_rule = $this->fxruleMod->querySql($sql_rule);
        $this->assign('res_rule', $res_rule);

        $this->assign('act', 'memlist');
        if($this->storeInfo['store_type'] == 1){
            //获取店铺名称
            $storeMod = &m('store');
            $store_data = $storeMod->getStoreArr($this->storeInfo['store_cate_id']);
            $this->assign('store_data', $store_data);
            $this->display('fxmember/memberAdd_all.html');
        }else{
            $this->display('fxmember/memberAdd.html');
        }

    }

    public function doadd() {
        $lang_id = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : 0;
        $this->load($lang_id, 'store/store');
        $a = $this->langData;
        $user_id = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;
        $fxlev = !empty($_REQUEST['fxlev']) ? $_REQUEST['fxlev'] : 0;
        $pidpid3 = !empty($_REQUEST['pidpid3']) ? $_REQUEST['pidpid3'] : 0;
        $pid2 = !empty($_REQUEST['pid2']) ? $_REQUEST['pid2'] : 0;
        $pid3 = !empty($_REQUEST['pid3']) ? $_REQUEST['pid3'] : 0;
        $fx_rule = !empty($_REQUEST['fx_rule']) ? $_REQUEST['fx_rule'] : 0;
        $real_name = !empty($_REQUEST['real_name']) ? htmlspecialchars(trim($_REQUEST['real_name'])) : '';
        $telephone = !empty($_REQUEST['telephone']) ? trim($_REQUEST['telephone']) : '';
        $email = !empty($_REQUEST['email']) ? trim($_REQUEST['email']) : '';
        $bank_name = !empty($_REQUEST['bank_name']) ? htmlspecialchars(trim($_REQUEST['bank_name'])) : '';
        $bank_account = !empty($_REQUEST['bank_account']) ? trim($_REQUEST['bank_account']) : '';
        $fx_discount = !empty($_REQUEST['fx_discount']) ? htmlspecialchars(trim($_REQUEST['fx_discount'])) : 0;  //推荐用户优惠比例
        $type = !empty($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
        if($type == "all"){
            $store_id = !empty($_REQUEST['store_id']) ? trim($_REQUEST['store_id']) : $this->storeId;
        }else{
            $store_id =  $this->storeId;
        }

        //数据判断
        if (empty($user_id)) {
            $this->setData(array(), '0', $a['personnel_name']);
        }
        if (empty($fxlev)) {
            $this->setData(array(), '0', $a['personnel_Grade']);
        }
        if (empty($real_name)) {
            $this->setData(array(), '0', $a['personnel_Fullname']);
        }
//        $nameinfo = $this -> getNameInfo($real_name);
//        if(!empty($nameinfo)){
//            $this->setData(array(), '0', '该人员已经存在！');
//        }
        //
        if (empty($telephone)) {
            $this->setData(array(), '0', $a['personnel_phone']);
        }
        if (!preg_match('/^1[34578]\d{9}$/', $telephone)) {
            $this->setData(array(), '0', $a['personnel_legitimate']);
        }
        $phoneinfo = $this->getPhoneInfo($telephone);
        if (!empty($phoneinfo)) {
            $this->setData(array(), '0', $a['personnel_existence']);
        }
        //
        if (empty($email)) {
            $this->setData(array(), '0', $a['personnel_mailbox']);
        }
        if (!preg_match('/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/', $email)) {
            $this->setData(array(), '0', $a['personnel_accounts']);
        }
//        $emailinfo = $this->getEmailInfo($email);
//        if (!empty($emailinfo)) {
//            $this->setData(array(), '0', $a['personnel_mail_existence']);
//        }
        //
        if (empty($bank_name)) {
            $this->setData(array(), '0', $a['personnel_Bank']);
        }
        if (empty($bank_account)) {
            $this->setData(array(), '0', $a['personnel_Bankname']);
        }
//        if(!$this -> chekBankAccount($bank_account)){
//            $this->setData(array(), '0', '请填写正确的银行帐号！');
//        }
//        $bankinfo = $this->getBankAccountInfo($bank_account);
//        if(!empty($bankinfo)){
//            $this->setData(array(), '0', '该银行帐号已经存在！');
//        }


        /* 添加数据 */
        $fxcode = $this->make_fxcode(); //分销二维码
        $qrcode = $this->shareMyfxcode($fxcode);

        //处理逻辑
        if ($fxlev == 1) { //添加一级分销人员
            if (empty($fx_rule)) {
                $this->setData(array(), '0', $a['personnel_distribution']);
            }
            //1.向fx_user插入数据
            $data_fxuser = array(
                'user_id' => $user_id,
                'fx_code' => $fxcode,
                'real_name' => $real_name,
                'telephone' => $telephone,
                'email' => $email,
                'bank_name' => $bank_name,
                'bank_account' => $bank_account,
                'is_check' => 2,
                'freeze' => 1,
                'store_cate' => $this->country_id,
                'store_id' => $store_id,
                'fx_img' => $qrcode,
                'add_time' => time()
            );
            $insert_userid = $this->fxuserMod->doInsert($data_fxuser);
            // 2.fx_usertree 插入数据
            $data_usertree = array(
                'user_id' => $user_id,
                'fx_level' => 1,
                'pid' => 0,
                'pidpid' => 0
            );
            $insert_utreeid = $this->fxuserTreeMod->doInsert($data_usertree);
            if (!$insert_utreeid) {
                $this->setData(array(), '0', $a['rule_fail']);
                return false;
            }
            //3. fx_user_rule 插入数据
            $data_userrule = array(
                'user_id' => $user_id,
                'rule_id' => $fx_rule
            );
            $insert_res = $this->fxuserRuleMod->doInsert($data_userrule);
        } else if ($fxlev == 2) { //添加二级分销人员
            if (empty($pid2)) {
                $this->setData(array(), '0', $a['personnel_Recommend']);
            }
            //1.向fx_user插入数据
            $data_fxuser = array(
                'user_id' => $user_id,
                'fx_code' => $fxcode,
                'real_name' => $real_name,
                'telephone' => $telephone,
                'email' => $email,
                'bank_name' => $bank_name,
                'bank_account' => $bank_account,
                'is_check' => 2,
                'freeze' => 1,
                'store_cate' => $this->country_id,
                'store_id' => $store_id,
                'fx_img' => $qrcode,
                'add_time' => time()
            );

            $insert_userid = $this->fxuserMod->doInsert($data_fxuser);
            // 2.fx_usertree 插入数据
            $data_usertree = array(
                'user_id' => $user_id,
                'fx_level' => 2,
                'pid' => $pid2,
                'pidpid' => 0
            );
            $insert_utreeid = $this->fxuserTreeMod->doInsert($data_usertree);
            if (!$insert_utreeid) {
                $this->setData(array(), '0', $a['rule_fail']);
                return false;
            }
        } else if ($fxlev == 3) { //添加三级分销人员
            if (empty($pid3)) {
                $this->setData(array(), '0', $a['personnel_Recommend']);
            }
            //推荐用户优惠比例
            if (!empty($fx_discount)) {

                if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $fx_discount)) {
                    $this->setData($info = array(), $status = '0', $a['personnel_Discount']);
                }
                $lev3prop = $this->getLev3Prop($pidpid3);
                if ($fx_discount >= $lev3prop) {
                    $this->setData($info = array(), $status = '0', $a['personnel_greaterthan']);
                }
            }

            //1.向fx_user插入数据
            $data_fxuser = array(
                'user_id' => $user_id,
                'fx_code' => $fxcode,
                'real_name' => $real_name,
                'telephone' => $telephone,
                'email' => $email,
                'bank_name' => $bank_name,
                'bank_account' => $bank_account,
                'is_check' => 2,
                'freeze' => 1,
                'store_cate' => $this->country_id,
                'store_id' => $store_id,
                'fx_img' => $qrcode,
                'fx_discount' => $fx_discount,
                'add_time' => time()
            );
            $insert_userid = $this->fxuserMod->doInsert($data_fxuser);
            // 2.fx_usertree 插入数据
            $data_usertree = array(
                'user_id' => $user_id,
                'fx_level' => 3,
                'pid' => $pid3,
                'pidpid' => $pidpid3
            );
            $insert_utreeid = $this->fxuserTreeMod->doInsert($data_usertree);
            if (!$insert_utreeid) {
                $this->setData(array(), '0', $a['rule_fail']);
                return false;
            }
        }

        // 4.fx_uer_money 插入数据
        $arr_store = $this->getstores();
        if (!empty($arr_store)) {
            foreach ($arr_store as $key => $val) {
                $arr_store[$key]['user_id'] = $user_id;
                $arr_store[$key]['store_cate'] = $this->country_id;
                $arr_store[$key]['money'] = 0.00;
            }
        }
        foreach ($arr_store as $k => $v) {
            $res = $this->fxuserMoneyMod->doInsert($v);
        }

        //5.更新user表
        $data_user = array(
            'is_fx' => 1
        );
        $res2 = $this->userMod->doEdit($user_id, $data_user);

        if ($res && $res2) {
            $info['url'] = "store.php?app=fxmember&act=memlist&lang_id={$this->lang_id}";
            $this->setData($info, '1', $a['rule_Success']);
        } else {
            $this->setData(array(), '0', $a['rule_fail']);
        }
    }

    public function getLev3Prop($pidpid) {

        $sql = 'SELECT  t.id,r.`lev3_prop`  FROM bs_fx_usertree AS t  LEFT JOIN  bs_fx_user_rule AS ur  ON t.`user_id` = ur.`user_id`
                LEFT JOIN bs_fx_rule AS r ON ur.`rule_id` = r.`id`  WHERE t.`id` = ' . $pidpid;

        $data = $this->fxuserTreeMod->querySql($sql);

        return $data[0]['lev3_prop'];
    }

    /**
     * 分销人员的编辑
     */
    public function edit() {
        $treeid = $_REQUEST['treeid'];
        $sql = 'SELECT  t.`id` AS tid,t.`fx_level`,t.`pid`,t.`pidpid`,us.`username`,u.*   FROM bs_fx_usertree AS t
                 LEFT JOIN bs_fx_user AS u ON t.`user_id` = u.`user_id`
                 LEFT JOIN  bs_user AS us ON t.`user_id` = us.`id`   WHERE  t.id = ' . $treeid;
        $data = $this->fxuserTreeMod->querySql($sql);

        //
        if ($data[0]['fx_level'] == 1) {
            $sql_r = 'select  * from bs_fx_user_rule where  user_id =' . $data[0]['user_id'];
            $data_r = $this->fxuserRuleMod->querySql($sql_r);
            $this->assign('data_r', $data_r[0]);
        } elseif ($data[0]['fx_level'] == 2) {
            //pid
            $sql_pid = 'select  u.real_name  from bs_fx_usertree as t  left join  bs_fx_user as u on t.user_id = u.user_id   where t.id =' . $data[0]['pid'];
            $data_pid = $this->fxuserTreeMod->querySql($sql_pid);
            $this->assign('data_pid', $data_pid[0]);
        } else {
            //pid
            $sql_pid = 'select  t.id,u.real_name  from bs_fx_usertree as t  left join  bs_fx_user as u on t.user_id = u.user_id  where t.id =' . $data[0]['pid'];
            $data_pid = $this->fxuserTreeMod->querySql($sql_pid);
            $this->assign('data_pid', $data_pid[0]);
            //pidpid
            $sql_pid = 'select  t.id,u.real_name  from bs_fx_usertree as t  left join  bs_fx_user as u on t.user_id = u.user_id  where t.id =' . $data[0]['pidpid'];
            $data_pidpid = $this->fxuserTreeMod->querySql($sql_pid);
            $this->assign('data_pidpid', $data_pidpid[0]);
        }
        //分销规则
        $sql_rule = "SELECT  *  FROM  bs_fx_rule  WHERE  mark = 1 AND  (FIND_IN_SET('" . $this->storeId . "',store_id)  OR store_id =0)";
        $res_rule = $this->fxruleMod->querySql($sql_rule);
        $this->assign('res_rule', $res_rule);

        //
        $this->assign('data', $data[0]);
        $this->assign('act', 'memlist');
        $this->display('fxmember/memberEdit.html');
    }

    public function doedit() {
        $lang_id = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : 0;
        $this->load($lang_id, 'store/store');
        $a = $this->langData;
        $user_id = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;
        $fxlev = !empty($_REQUEST['fx_level']) ? $_REQUEST['fx_level'] : 0; //分销等级
        $pidpid3 = !empty($_REQUEST['pidpid3']) ? $_REQUEST['pidpid3'] : 0;
//        $pid2 = !empty($_REQUEST['pid2']) ? $_REQUEST['pid2'] : 0;
//        $pid3 = !empty($_REQUEST['pid3']) ? $_REQUEST['pid3'] : 0;
        $fx_rule = !empty($_REQUEST['fx_rule']) ? $_REQUEST['fx_rule'] : 0;
        $real_name = !empty($_REQUEST['real_name']) ? htmlspecialchars(trim($_REQUEST['real_name'])) : '';
        $telephone = !empty($_REQUEST['telephone']) ? trim($_REQUEST['telephone']) : '';
        $email = !empty($_REQUEST['email']) ? trim($_REQUEST['email']) : '';
        $bank_name = !empty($_REQUEST['bank_name']) ? htmlspecialchars(trim($_REQUEST['bank_name'])) : '';
        $bank_account = !empty($_REQUEST['bank_account']) ? trim($_REQUEST['bank_account']) : '';
        //
        $usertree_id = !empty($_REQUEST['usertree_id']) ? $_REQUEST['usertree_id'] : 0;
        $fx_user_id = !empty($_REQUEST['fx_user_id']) ? $_REQUEST['fx_user_id'] : 0;
        $user_rule_id = !empty($_REQUEST['user_rule_id']) ? $_REQUEST['user_rule_id'] : 0;
        $fx_discount = !empty($_REQUEST['fx_discount']) ? htmlspecialchars(trim($_REQUEST['fx_discount'])) : 0;  //推荐用户优惠比例
        //数据判断
        if (empty($real_name)) {
            $this->setData(array(), '0', $a['personnel_Fullname']);
        }
        //
//        $nameinfo = $this -> getNameInfo($real_name,$fx_user_id);
//        if(!empty($nameinfo)){
//            $this->setData(array(), '0', '该人员已经存在！');
//        }
        if (empty($telephone)) {
            $this->setData(array(), '0', $a['personnel_phone']);
        }
        if (!preg_match('/^1[34578]\d{9}$/', $telephone)) {
            $this->setData(array(), '0', $a['personnel_legitimate']);
        }
        $phoneinfo = $this->getPhoneInfo($telephone, $fx_user_id);
        if (!empty($phoneinfo)) {
            $this->setData(array(), '0', $a['personnel_existence']);
        }
        //
        if (empty($email)) {
            $this->setData(array(), '0', $a['personnel_mailbox']);
        }
        if (!preg_match('/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/', $email)) {
            $this->setData(array(), '0', $a['personnel_accounts']);
        }
//        $emailinfo = $this->getEmailInfo($email, $fx_user_id);
//        if (!empty($emailinfo)) {
//            $this->setData(array(), '0', $a['personnel_mail_existence']);
//        }
        //
        if (empty($bank_name)) {
            $this->setData(array(), '0', $a['personnel_Bank']);
        }
        if (empty($bank_account)) {
            $this->setData(array(), '0', $a['personnel_Bankname']);
        }
//        if(!$this -> chekBankAccount($bank_account)){
//            $this->setData(array(), '0', '请填写正确的银行帐号！');
//        }
//        $bankinfo = $this->getBankAccountInfo($bank_account,$fx_user_id);
//        if(!empty($bankinfo)){
//            $this->setData(array(), '0', '该银行帐号已经存在！');
//        }
        //******数据处理***

        if ($fxlev == 1) {
            //数据判断
            if (empty($fx_rule)) {
                $this->setData(array(), '0', $a['personnel_distribution']);
            }
            //bs_fx_user
            $data_fuser = array(
                'real_name' => $real_name,
                'telephone' => $telephone,
                'email' => $email,
                'bank_name' => $bank_name,
                'bank_account' => $bank_account,
            );
            $res1 = $this->fxuserMod->doEdit($fx_user_id, $data_fuser);
            // bs_fx_user_rule
            $data_usert = array(
                'rule_id' => $fx_rule,
            );
            $res3 = $this->fxuserRuleMod->doEdit($user_rule_id, $data_usert);
            //
            if ($res1 && $res3) {
                $info['url'] = "store.php?app=fxmember&act=memlist&lang_id={$this->lang_id}";
                $this->setData($info, '1', $a['Update_success']);
            } else {
                $this->setData(array(), '0', $a['Update_fail']);
            }
        } else if ($fxlev == 3) {

            //推荐用户优惠比例
            if (!empty($fx_discount)) {
                if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $fx_discount)) {
                    $this->setData($info = array(), $status = '0', $a['personnel_Discount']);
                }
                $lev3prop = $this->getLev3Prop($pidpid3);
                if ($fx_discount >= $lev3prop) {
                    $this->setData($info = array(), $status = '0', $a['personnel_greaterthan']);
                }
            }
            //bs_fx_user
            $data_fuser = array(
                'real_name' => $real_name,
                'telephone' => $telephone,
                'email' => $email,
                'bank_name' => $bank_name,
                'bank_account' => $bank_account,
                'fx_discount' => $fx_discount
            );
            $res1 = $this->fxuserMod->doEdit($fx_user_id, $data_fuser);

            if ($res1) {
                $info['url'] = "store.php?app=fxmember&act=memlist&lang_id={$this->lang_id}";
                $this->setData($info, '1', $a['Update_success']);
            } else {
                $this->setData(array(), '0', $a['Update_fail']);
            }
        } else {

            //bs_fx_user
            $data_fuser = array(
                'real_name' => $real_name,
                'telephone' => $telephone,
                'email' => $email,
                'bank_name' => $bank_name,
                'bank_account' => $bank_account,
            );
            $res1 = $this->fxuserMod->doEdit($fx_user_id, $data_fuser);

            if ($res1) {
                $info['url'] = "store.php?app=fxmember&act=memlist&lang_id={$this->lang_id}";
                $this->setData($info, '1', $a['Update_success']);
            } else {
                $this->setData(array(), '0', $a['Update_fail']);
            }
        }
    }

    public function getstores() {
        $storeMod = &m('store');
        $sql = 'select id as store_id  from  bs_store  where   store_cate_id =' . $this->country_id;
        $res = $storeMod->querySql($sql);
        return $res;
    }

    public function getNameInfo($realname, $id = 0) {
        $where = '  where  (is_check = 1 or is_check = 2)  and  real_name = "' . $realname . '"';
        if (!empty($id)) {
            $where .= '   and  id !=' . $id;
        }
        $sql = 'select  id  from bs_fx_user ' . $where;
        $res = $this->fxuserMod->querySql($sql);
        return $res;
    }

    public function getPhoneInfo($phone, $id = 0) {
        $where = '  where  (is_check = 1 or is_check = 2)  and  telephone =' . $phone;
        if (!empty($id)) {
            $where .= '   and  id !=' . $id;
        }
        $sql = 'select  id  from bs_fx_user ' . $where;
        $res = $this->fxuserMod->querySql($sql);
        return $res;
    }

    public function getEmailInfo($email, $id = 0) {
        $where = '  where  (is_check = 1 or is_check = 2)  and  email = "' . $email . '"';
        if (!empty($id)) {
            $where .= '   and  id !=' . $id;
        }
        $sql = 'select  id  from bs_fx_user ' . $where;
        $res = $this->fxuserMod->querySql($sql);
        return $res;
    }

    public function getBankAccountInfo($bankAccount, $id = 0) {
        $where = '  where  (is_check = 1 or is_check = 2)  and  bank_account =' . $bankAccount;
        if (!empty($id)) {
            $where .= '   and  id !=' . $id;
        }
        $sql = 'select  id  from bs_fx_user ' . $where;
        $res = $this->fxuserMod->querySql($sql);
        return $res;
    }

    public function ajaxgetchids() {
        $ppid = $_REQUEST['ppid'];
        $sql = 'select  t.id,u.`real_name`,t.`user_id`  from bs_fx_usertree as t left join bs_fx_user as u on t.user_id = u.user_id  where  pid =' . $ppid;
        $data = $this->fxuserTreeMod->querySql($sql);
        echo json_encode($data);
    }

    /**
     * @return int|mixed
     * 推荐码生成
     */
//    public function make_tjcode(){
//        $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
//        $string=time();
//        for($len = 3;$len>=1;$len--) {
//            $position=rand()%strlen($chars);
//            $position2=rand()%strlen($string);
//            $string=substr_replace($string,substr($chars,$position,1),$position2,0);
//        }
//        return $string.'tj';
//    }

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

    /**
     * @param $account
     * 验证银行帐号
     */
    public function chekBankAccount($bankAccount) {
        //$no = '7432810010473523';
        $no = $bankAccount;
        $arr_no = str_split($no);
        $last_n = $arr_no[count($arr_no) - 1];
        krsort($arr_no);
        $i = 1;
        $total = 0;
        foreach ($arr_no as $n) {
            if ($i % 2 == 0) {
                $ix = $n * 2;
                if ($ix >= 10) {
                    $nx = 1 + ($ix % 10);
                    $total += $nx;
                } else {
                    $total += $ix;
                }
            } else {
                $total += $n;
            }
            $i++;
        }
        $total -= $last_n;
        $x = 10 - ($total % 10);
        if ($x == $last_n) {
            // echo '符合Luhn算法';
            return true;
        } else {
            return false;
        }
    }

    /**
     * 冻结账户
     */
    public function ajaxfreeze() {
        $uid = $_REQUEST['uid'];
        $freeze = $_REQUEST['freeze'];
        $data = array('freeze' => $freeze);
        $res = $this->fxuserMod->doEdit($uid, $data);

        if ($res) {
            echo json_encode(array('status' => 1));
            exit;
        } else {
            echo json_encode(array('status' => 0));
            exit;
        }
    }

    /**
     * 获取分销优惠
     */
    public function ajaxDiscount() {
        $storeCateMod = &m('storeCate');
        $sql = 'select  id,fx_discount  from  bs_store_cate  where id=' . $this->country_id;
        $data = $storeCateMod->querySql($sql);
        echo json_encode(array('dis' => $data[0]['fx_discount']));
        exit;
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
        $value = "http://" . SYSTEM_WEB ."/". SYSTEM_FILE_NAME . "/wx.php?app=user&act=doFxuser&fx_code=" . $fx_code; //二维码内容
        if ($_SERVER["PHP_SELF"]) {
            $_SERVER["PHP_SELF"] = str_replace("store.php", "", $_SERVER["PHP_SELF"]);
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

    /*
     * 获取一级分销商
     */
    public function ajaxgetfirst(){
        $store_id = $_REQUEST['store_id'];
        $sql_lev1 = 'SELECT  t.id,u.`real_name`,t.`user_id`  FROM  bs_fx_usertree AS t
                      LEFT JOIN  bs_fx_user  AS u ON t.`user_id` = u.`user_id`
                      WHERE  t.`fx_level` = 1  AND  u.is_check = 2  and  u.freeze = 1  AND  u.store_id =' . $store_id;
        $res_lev1 = $this->fxuserTreeMod->querySql($sql_lev1);
        echo json_encode($res_lev1);
    }

    /*
     * 获取分销规则
     */
    public function ajaxgetrule(){
        $store_id = $_REQUEST['store_id'];
        $sql_rule = "SELECT  *  FROM  bs_fx_rule  WHERE  mark = 1 AND  (FIND_IN_SET('" . $store_id . "',store_id)  OR store_id =0)";
        $res_rule = $this->fxruleMod->querySql($sql_rule);
        echo json_encode($res_rule);

    }
}
