<?php

/**
 * 分销角色控制器
 * @author zhangkx
 * @date 2018-10-11
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class DistributorApp extends BackendApp
{
    private $fxuserMod;
    private $fxruleMod;
    private $fxuserMoneyMod;
    private $fxuserRuleMod;
    private $fxuserTreeMod;
    private $userMod;
    private $storeMod;
    private $storeCateMod;
    private $storeLangMod;
    private $fxRulerMod;
    private $storeCateLangMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->fxuserMod = &m('fxuser');
        $this->fxuserMoneyMod = &m('fxuserMoney');
        $this->fxuserRuleMod = &m('fxuserRule');
//        $this->fxuserTreeMod = &m('fxuserTree');
        $this->fxruleMod = &m('fxrule');
        $this->userMod = &m('user');
        $this->storeMod = &m('store');
        $this->storeCateMod = &m('storeCate');
        $this->storeLangMod = &m('areaStoreLang');
        $this->fxRulerMod = &m('fxrule');
        $this->storeCateLangMod = &m('storeCateLang');
    }

    /**
     * 析构函数
     */
    public function __destruct() {

    }

    /**  分销人员begin  ***/

    /**
     * 分销人员树
     */
    public function memberList() {
        $fxUserAccountMod = &m('fxUserAccount');
        $area_id = $_REQUEST['area_id'] ? htmlspecialchars(trim($_REQUEST['area_id'])) : '';
        $store_id = $_REQUEST['store_id'] ? $_REQUEST['store_id'] : 0;
        $source = $_REQUEST['source'] ? $_REQUEST['source'] : 0;
        //账号来源
        $this->assign('sourceList', $this->fxuserMod->source);
        //获取区域数组
        $storeCateMod = &m('storeCate');
        $areaArr = $storeCateMod->getAreaArr(1,$this->lang_id);
        $areaOption = make_option($areaArr, $area_id);
        $this->assign('areaOption', $areaOption);
        if ($area_id) {
            $this->assign('area_id', $area_id);
            if ($store_id) {
                $this->assign('store_id', $store_id);
            }
            //获取区域店铺
            $storeMod = &m('store');
            $storeArr = $storeMod->getStoreArr($area_id, 1);
            $storeOption = make_option($storeArr, $store_id);
            $this->assign('storeOption', $storeOption);
        }
        $where = " WHERE level=1 and mark = 1";
        if($store_id){
            $where .= " and store_id =" . $store_id;
        }
        if ($source) {
            $where .= " and source =" . $source;
            $this->assign('source', $source);
        }
        $sql = 'select * from bs_fx_user ' . $where;
        $res = $this->fxuserMod->querySql($sql);
        foreach ($res as $key => $val) {
            $store_name = $this->storeMod->getNameById($val['store_id'],$this->lang_id);
            $res[$key]['store_name'] = $store_name;
            $res[$key]['source_name'] = $this->fxuserMod->source[$val['source']];
            $lev2 = $this->fxuserMod->getUserListByLevel(2, $val['id']);
            if (!empty($lev2)) {
                // 2 级
                $res[$key]['childs'] = $lev2;
                // 3级
                foreach ($lev2 as $k => $v) {
                    $res[$key]['childs'][$k]['source_name'] = $this->fxuserMod->source[$v['source']];
                    $lev3 = $this->fxuserMod->getUserListByLevel(3, $v['id']);
                    $res[$key]['childs'][$k]['store_name'] = $this->storeMod->getNameById($v['store_id'],$this->lang_id);
                    if (!empty($lev3)) {
                        $res[$key]['childs'][$k]['childs'] = $lev3;
                        foreach ($lev3 as $k1=>$v1){
                            $res[$key]['childs'][$k]['childs'][$k1]['store_name'] = $this->storeMod->getNameById($v1['store_id'],$this->lang_id);
                            $res[$key]['childs'][$k]['childs'][$k1]['source_name'] = $this->fxuserMod->source[$v1['source']];
                            if($fxUserAccountMod ->checkUserAccount($v1['id'],1) > 0){
                                $res[$key]['childs'][$k]['childs'][$k1]['have_user'] = 1;
                            }
                        }
                    }
                }
            }
        }
//        echo '<pre>';var_dump($res);die;
        $this->assign('res', $res);
        $this->display('distributor/memberList.html');
    }

    /**
     * 分销人员的添加
     */
    public function memberAdd() {
//        $user = $this->fxuserMod->getLevUser(10);
//        echo '<pre>';print_r($user);die;
        $this->assign('act', 'memberList');
        $storeCateList = $this->allCountry;
        $this->assign('storeCate', $storeCateList);
        $this->display('distributor/memberAdd.html');
    }


    public function doAdd() {
        $user_id = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;
        $store_cate = !empty($_REQUEST['store_cate']) ? $_REQUEST['store_cate'] : 0;
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : 0;
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
        //数据判断
        if (empty($user_id)) {
            $this->setData(array(), '0', $this->langDataBank->project->select_member);
        }
        if ($this->fxuserMod->isExist('user_id', $user_id)) {
            $this->setData(array(), '0', $this->langDataBank->project->member_exist);
        }
        if (empty($store_cate)) {
            $this->setData(array(), '0', $this->langDataBank->project->select_regional_country);
        }
        if (empty($store_id)) {
            $this->setData(array(), '0', $this->langDataBank->project->select_regional_store);
        }
        if (empty($fxlev)) {
            $this->setData(array(), '0', $this->langDataBank->project->select_level);
        }
        if (empty($real_name)) {
            $this->setData(array(), '0', $this->langDataBank->project->true_name_required);
        }

        $nameInfo = $this->fxuserMod->isExist('real_name', $real_name);
        if(!empty($nameInfo)){
            $this->setData(array(), '0', $this->langDataBank->project->member_exist);
        }
        //

        if (empty($telephone)) {
            $this->setData(array(), '0', $this->langDataBank->project->fill_phone);
        }
        if (!preg_match('/^1[34578]\d{9}$/', $telephone)) {
            $this->setData(array(), '0', $this->langDataBank->project->format_phone);
        }
//        $phoneInfo = $this->fxuserMod->isExist('phone', $telephone);
//        if (!empty($phoneInfo)) {
//            $this->setData(array(), '0', '该手机号码已经存在');
//        }
        if (empty($email)) {
            $this->setData(array(), '0', $this->langDataBank->project->fill_email);
        }
        if (!preg_match('/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/', $email)) {
            $this->setData(array(), '0', $this->langDataBank->project->format_email);
        }
//        $emailInfo = $this->fxuserMod->isExist('email', $email);
//        if (!empty($emailInfo)) {
//            $this->setData(array(), '0', '该邮箱帐号已经存在');
//        }
        if (empty($bank_name)) {
            $this->setData(array(), '0', $this->langDataBank->project->fill_bank);
        }
        if (empty($bank_account)) {
            $this->setData(array(), '0', $this->langDataBank->project->fill_bank_account);
        }

        /* 添加数据 */
        $fxCode = $this->unique_rand(100000,999999,1); //分销码
        $insertUser = 0;
        //处理逻辑
        if ($fxlev == 1) { //添加一级分销人员
            if (empty($fx_rule)) {
                $this->setData(array(), '0', $this->langDataBank->project->distribution_rule);
            }
            //1.向fx_user插入数据
            $data_fxuser = array(
                'parent_id' => 0,
                'level' => 1,
                'rule_id' => $fx_rule,
                'user_id' => $user_id,
                'real_name' => $real_name,
                'phone' => $telephone,
                'fx_code' => $fxCode,
                'email' => $email,
                'bank_name' => $bank_name,
                'bank_account' => $bank_account,
                'store_cate' => $store_cate,
                'store_id' => $store_id,
                'status' => 1,
                'is_check' => 2,
                'source' => 1,
                'discount' => 0,
                'add_time' => time(),
                'add_user' => $this->accountId
            );
            $insertUser = $this->fxuserMod->doInsert($data_fxuser);
        } else if ($fxlev == 2) { //添加二级分销人员
            if (empty($pid2)) {
                $this->setData(array(), '0', $this->langDataBank->project->recommend_member);
            }
            $level1 = $this->fxuserMod->getRow($pid2);
            //1.向fx_user插入数据
            $data_fxuser = array(
                'parent_id' => $pid2,
                'level' => 2,
                'rule_id' => $level1['rule_id'],
                'user_id' => $user_id,
                'real_name' => $real_name,
                'phone' => $telephone,
                'fx_code' => $fxCode,
                'email' => $email,
                'bank_name' => $bank_name,
                'bank_account' => $bank_account,
                'store_cate' => $store_cate,
                'store_id' => $store_id,
                'status' => 1,
                'is_check' => 2,
                'source' => 1,
                'discount' => 0,
                'add_time' => time(),
                'add_user' => $this->accountId
            );
            $insertUser = $this->fxuserMod->doInsert($data_fxuser);
        } else if ($fxlev == 3) { //添加三级分销人员
            if (empty($pid3)) {
                $this->setData(array(), '0', $this->langDataBank->project->recommend_member);
            }
            //推荐用户优惠比例
            if (!empty($fx_discount)) {

                if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $fx_discount)) {
                    $this->setData($info = array(), $status = '0', $this->langDataBank->project->three_illegal);
                }
                $lev3prop = $this->fxruleMod->getLev3Prop($pidpid3);
                if ($fx_discount > $lev3prop) {
                    $this->setData($info = array(), $status = '0', $this->langDataBank->project->three_level);
                }
            }
            $level1 = $this->fxuserMod->getRow($pidpid3);
            //1.向fx_user插入数据
            $data_fxuser = array(
                'parent_id' => $pid3,
                'level' => 3,
                'rule_id' => $level1['rule_id'],
                'user_id' => $user_id,
                'real_name' => $real_name,
                'phone' => $telephone,
                'fx_code' => $fxCode,
                'email' => $email,
                'bank_name' => $bank_name,
                'bank_account' => $bank_account,
                'store_cate' => $store_cate,
                'store_id' => $store_id,
                'status' => 1,
                'is_check' => 2,
                'source' => 1,
                'discount' => $fx_discount,
                'add_time' => time(),
                'add_user' => $this->accountId
            );

            $insertUser = $this->fxuserMod->doInsert($data_fxuser);
        }
        $this->userMod->doEdit($user_id, array('is_fx'=>1));
        if (!$insertUser) {
            $this->setData(array(), '0', $this->langDataBank->public->add_error);
            return false;
        }
        $info['url'] = "admin.php?app=distributor&act=memberList";
        $this->setData($info, '1', $this->langDataBank->public->add_success);
    }

    /**
     * 分销人员的编辑
     */
    public function edit() {
        $id = $_REQUEST['id'];
        $sql = "select a.*, b.username from bs_fx_user as a left join bs_user as b on a.user_id = b.id where a.id = {$id}";
        $data = $this->fxuserMod->querySql($sql);
        if ($data[0]['level'] == 2) {
            //pid
            $data_pid = $this->fxuserMod->getOne(array('cond'=>'id = '.$data[0]['parent_id']));
            $this->assign('data_pid', $data_pid);
        }
        if ($data[0]['level'] == 3) {
            //pid
            $data_pid = $this->fxuserMod->getOne(array('cond'=>'id = '.$data[0]['parent_id']));
            $this->assign('data_pid', $data_pid);
            //pidpid
            $data_pidpid = $this->fxuserMod->getOne(array('cond'=>'id = '.$data_pid['parent_id']));
            $this->assign('data_pidpid', $data_pidpid);
        }
        //分销规则
        $sql_rule = "SELECT  *  FROM  bs_fx_rule  WHERE  mark = 1 AND  (FIND_IN_SET('" . $data[0]['store_id'] . "',store_id)  OR store_id =0)";
        $res_rule = $this->fxruleMod->querySql($sql_rule);
        $this->assign('res_rule', $res_rule);
        //店铺名称
        $storeData = $this->storeMod->getRow($data[0]['store_id']);
        $storeName = $this->storeLangMod->getOne(array('cond' => 'store_id = '.$storeData['id'].' and distinguish = 0 and lang_id = '.$this->lang_id));
        $data[0]['store_name'] = $storeName['store_name'];
        //站点名称
        $cateName = $this->storeCateLangMod->getOne(array('cond' => 'cate_id = '.$storeData['store_cate_id'].' and lang_id = '.$this->lang_id));
        $data[0]['cate_name'] = $cateName['cate_name'];
        $this->assign('data', $data[0]);
        $this->assign('act', 'memberList');
        $this->display('distributor/memberEdit.html');
    }

    public function doEdit() {
        $fxlev = !empty($_REQUEST['fx_level']) ? $_REQUEST['fx_level'] : 0; //分销等级
        $pidpid3 = !empty($_REQUEST['pidpid3']) ? $_REQUEST['pidpid3'] : 0;
        $fx_rule = !empty($_REQUEST['fx_rule']) ? $_REQUEST['fx_rule'] : 0;
        $real_name = !empty($_REQUEST['real_name']) ? htmlspecialchars(trim($_REQUEST['real_name'])) : '';
        $telephone = !empty($_REQUEST['phone']) ? trim($_REQUEST['phone']) : '';
        $email = !empty($_REQUEST['email']) ? trim($_REQUEST['email']) : '';
        $bank_name = !empty($_REQUEST['bank_name']) ? htmlspecialchars(trim($_REQUEST['bank_name'])) : '';
        $bank_account = !empty($_REQUEST['bank_account']) ? trim($_REQUEST['bank_account']) : '';
        $fx_user_id = !empty($_REQUEST['fx_user_id']) ? $_REQUEST['fx_user_id'] : 0;
        $discount = !empty($_REQUEST['discount']) ? htmlspecialchars(trim($_REQUEST['discount'])) : 0;  //推荐用户优惠比例
        //数据判断
        if (empty($real_name)) {
            $this->setData(array(), '0', $this->langDataBank->project->true_name_required);
        }
        $nameInfo = $this->fxuserMod->isExist('real_name', $real_name, $fx_user_id);
        if(!empty($nameInfo)){
            $this->setData(array(), '0', $this->langDataBank->project->member_exist);
        }
        if (empty($telephone)) {
            $this->setData(array(), '0', $this->langDataBank->project->fill_phone);
        }
        if (!preg_match('/^1[34578]\d{9}$/', $telephone)) {
            $this->setData(array(), '0', $this->langDataBank->project->format_phone);
        }
        $phoneInfo = $this->fxuserMod->isExist('phone', $telephone, $fx_user_id);
        if (!empty($phoneInfo)) {
            $this->setData(array(), '0', $this->langDataBank->project->phone_repeat);
        }
        if (empty($email)) {
            $this->setData(array(), '0', $this->langDataBank->project->fill_email);
        }
        if (!preg_match('/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/', $email)) {
            $this->setData(array(), '0', $this->langDataBank->project->format_email);
        }
        $emailInfo = $this->fxuserMod->isExist('email', $email, $fx_user_id);
        if(!empty($emailInfo)){
            $this->setData(array(), '0', $this->langDataBank->project->member_exist);
        }
        if (empty($bank_name)) {
            $this->setData(array(), '0', $this->langDataBank->project->fill_bank);
        }
        if (empty($bank_account)) {
            $this->setData(array(), '0', $this->langDataBank->project->fill_bank_account);
        }
//        if(!$this -> chekBankAccount($bank_account)){
//            $this->setData(array(), '0', '请填写正确的银行帐号！');
//        }
//        $bankInfo = $this->fxuserMod->isExist('bank_account', $bank_account,$fx_user_id);
//        if(!empty($bankInfo)){
//            $this->setData(array(), '0', '该银行帐号已经存在！');
//        }
        //******数据处理***

        if ($fxlev == 1) {
            //数据判断
            if (empty($fx_rule)) {
                $this->setData(array(), '0', $this->langDataBank->project->distribution_rule);
            }
            //bs_fx_user
            $data_fuser = array(
                'real_name' => $real_name,
                'phone' => $telephone,
                'email' => $email,
                'bank_name' => $bank_name,
                'bank_account' => $bank_account,
                'rule_id' => $fx_rule
            );
            //联动更改二级三级分销人员的rule_id
            $this->fxuserMod->updateRule($fx_user_id, $fx_rule);
        } else if ($fxlev == 3) {
            //推荐用户优惠比例
            if (!empty($discount)) {
                if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $discount)) {
                    $this->setData($info = array(), $status = '0', $this->langDataBank->project->three_illegal);
                }
                $lev3prop = $this->fxruleMod->getLev3Prop($pidpid3);
                if ($discount > $lev3prop) {
                    $this->setData($info = array(), $status = '0', $this->langDataBank->project->three_level);
                }
            }
            //bs_fx_user
            $data_fuser = array(
                'real_name' => $real_name,
                'phone' => $telephone,
                'email' => $email,
                'bank_name' => $bank_name,
                'bank_account' => $bank_account,
                'discount' => $discount
            );
            //更改分销比例生成日志
            $fxUserMod=&m("fxuser");
            $fxDiscountLogMod=&m("fxDiscountLog");
            $fxUserData = $fxUserMod->getOne(array('cond'=>"`id`='{$fx_user_id}'",'fields'=>'discount'));

            // 存入 current_rule_percent 字段 by xt 2019.03.06
            $lev3_prop = &m('fxrule')->getLev3Percent($fx_user_id);

            if($discount != $fxUserData['discount']){
                $fxDiscountLogData=array(
                    "fx_user_id"=>$fx_user_id,
                    'fx_discount'=>$discount,
                    'old_discount'=>$fxUserData['discount'],
                    'current_rule_percent' => $lev3_prop,  // 更新 current_rule_percent 字段
                    'is_check'=>2,
                    'add_time'=>time(),
                    'check_user'=>$this->accountId,
                    'source'=>2
                );
                $fxDiscountLogMod->doInsert($fxDiscountLogData);
            }
        } else {
            //bs_fx_user
            $data_fuser = array(
                'real_name' => $real_name,
                'phone' => $telephone,
                'email' => $email,
                'bank_name' => $bank_name,
                'bank_account' => $bank_account,
            );
        }
        $res1 = $this->fxuserMod->doEdit($fx_user_id, $data_fuser);
        if ($res1) {
            $info['url'] = "admin.php?app=distributor&act=memberList";
            $this->setData($info, '1', $this->langDataBank->public->edit_success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->public->edit_fail);
        }
    }

    /**
     * 审核二级三级分销人员
     *
     * @author zhangkx
     * @date 2018-10-17
     */
    public function audit()
    {
        $id = $_REQUEST['id']? htmlspecialchars((int)$_REQUEST['id']) : 0;
        $level = $_REQUEST['level']? htmlspecialchars((int)$_REQUEST['level']) : 0;
        if (IS_POST) {
            $isCheck = $_REQUEST['is_check']? htmlspecialchars((int)$_REQUEST['is_check']) : 0;
            $discount = $_REQUEST['discount']? htmlspecialchars((int)$_REQUEST['discount']) : 0;
            //三级分销人员需要填写优惠比例
            if ($level == 3) {
                if ($isCheck == 2) {
                    if (empty($discount)) {
                        $this->setData(array(), '0', $this->langDataBank->project->fill_rate);
                    }
                    if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $discount)) {
                        $this->setData($info = array(), $status = '0', $this->langDataBank->project->rate_format);
                    }
                    $discountData = $this->fxruleMod->getLev3Percent($id);
                    if ($discount > $discountData) {
                        $this->setData($info = array(), $status = '0', $this->langDataBank->project->three_level);
                    }
                }
            }
            //更新fx_user表
            $data = array(
                'is_check' => $isCheck,
                'discount' => $discount
            );
            $result = $this->fxuserMod->doEdit($id, $data);
            if (!$result) {
                $this->setData($info = array(), $status = '0', $this->langDataBank->public->cz_error);
            }
            $this->setData($info = array(), $status = '1', $this->langDataBank->public->cz_success);
        }
        $this->assign('id', $id);
        $this->assign('level', $level);
        $this->display('distributor/auditUser.html');
    }

    public function ajaxgetchids() {
        $ppid = $_REQUEST['ppid'];
        $sql = 'select  t.id,u.real_name,t.user_id from bs_fx_usertree as t 
                left join bs_fx_user as u on t.user_id = u.user_id  where  t.pid =' . $ppid;
        $data = $this->fxuserTreeMod->querySql($sql);
        echo json_encode($data);
    }

    /**
     * 获取推荐人
     */
    public function ajaxGetUser() {
        $storeId = $_REQUEST['store_id'];
        $pid = $_REQUEST['pid'];
        $level = $_REQUEST['level'];
        $where = ' and 1 = 1';
        if ($pid) {
            $where .= " and parent_id = {$pid}";
        }
        if ($storeId) {
            $where .= " and store_id = {$storeId}";
        }
        $sql = "select * from bs_fx_user where level = {$level}" . $where;
        $data = $this->fxuserMod->querySql($sql);
        $this->setData($data,1);
    }

    /**
     * 根据店铺获取分润规则
     */
    public function ajaxGetRule()
    {
        $storeId = $_REQUEST['store_id'] ? (int)$_REQUEST['store_id'] : 0;
        //分销规则
        $sql_rule = "SELECT * FROM bs_fx_rule WHERE mark = 1 AND (FIND_IN_SET('" . $storeId . "',store_id)  OR store_id = 0)";
        $data = $this->fxruleMod->querySql($sql_rule);
        $this->setData($data,1);
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
//        echo '<pre>';print_r($_REQUEST);die;
        $data = array('status' => $freeze);
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

    /**  分销人员end  ***/

    /**  分销规则begin  ***/

    /**
     * 商铺分润规则首页
     * @author wanyan
     * @date 2017-11-17
     */
    public function ruleList() {
        $area_id = $_REQUEST['area_id'] ? htmlspecialchars(trim($_REQUEST['area_id'])) : '';
        $store_id = $_REQUEST['store_id'] ? $_REQUEST['store_id'] : 0;
        //获取区域数组
        $storeCateMod = &m('storeCate');
        $areaArr = $storeCateMod->getAreaArr(1,$this->lang_id);
        $areaOption = make_option($areaArr, $area_id);
        $this->assign('areaOption', $areaOption);
        $where = " where `mark` =1";
        if ($area_id) {
            $where .= ' and store_cate = '.$area_id;
            $storeList = $this->storeMod->getStoreArr($area_id);
            $this->assign('storeList', $storeList);
            $this->assign('area_id', $area_id);
        }
        if ($store_id) {
            $where .= ' and store_id in ('.$store_id.')';
            $this->assign('store_id', $store_id);
        }
        $ruler_name = !empty($_REQUEST['ruler_name']) ? htmlspecialchars(trim($_REQUEST['ruler_name'])) : '';
        if (!empty($ruler_name)) {
            $where .= "  and `rule_name` like '%" . $ruler_name . "%'";
        }
        $where .= " order by add_time desc";
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "fx_rule " . $where;
        $totalCount = $this->fxRulerMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;

        $sql = "select * from  " . DB_PREFIX . "fx_rule " . $where;
        $rs = $this->fxRulerMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));


        foreach ($rs['list'] as $k => $v) {
            $rs['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);

            if (empty($v['store_id'])) {
                $rs['list'][$k]['store_name'] = '不限制';
            } else{
                $storeInfo = $this->storeMod->getNameByIds($v['store_id'], $this->lang_id);
                $rs['list'][$k]['store_name'] = $storeInfo;
            }
            $rs['list'][$k]['lev1_prop'] = $this->fxruleMod->isDecimal($v['lev1_prop']);
            $rs['list'][$k]['lev2_prop'] = $this->fxruleMod->isDecimal($v['lev2_prop']);
            $rs['list'][$k]['lev3_prop'] = $this->fxruleMod->isDecimal($v['lev3_prop']);

            if (empty($rs['list'][$k]['store_name'])) {
                $rs['list'][$k]['store_name'] = '不限制';
            }
            if ($v['add_time']) {
                $rs['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            } else {
                $rs['list'][$k]['add_time'] = '';
            }
            $rs['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
//            $rs['list'][$k]['sort'] = $total - $k - 20 * ($p - 1); //倒叙
        }
//        echo '<pre>';print_r($storeName);die;
        $this->assign('p', $p);
        $this->assign('list', $rs['list']);
        $this->assign('page_html', $rs['ph']);
        $this->assign('ruler_name', $ruler_name);
        $this->display('distributor/rulerList.html');
    }

    /**
     * 商铺分润规则添加
     * @author wanyan
     * @date 2017-11-17
     */
    public function add() {
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $this->assign('p', $p);
        $lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : 0;
        $this->assign('act', 'ruleList');
        $this->assign('lang_id', $lang_id);
        $storeCateList = $this->allCountry;
        $this->assign('storeCate', $storeCateList);
        $this->display('distributor/rulerAdd.html');
    }

    /**
     * 区域国家地下的店铺
     * @author wanyan
     * @date 2017-11-20
     */
    public function getChild() {
        $id = !empty($_REQUEST['parent_id']) ? $_REQUEST['parent_id'] : '';
        $t_id = !empty($_REQUEST['t_id']) ? $_REQUEST['t_id'] : '';
        if (empty($id)) {
            $this->setData($info = array(), $status = '0', $message = '');
        }
        $sql = 'SELECT  c.id,l.store_name,c.store_cate_id  FROM  ' . DB_PREFIX . 'store  as c
                 left join  ' . DB_PREFIX . 'store_lang as l on  c.id = l.store_id
                 WHERE c.is_open = 1  and l.distinguish = 0 and  l.lang_id =' . $this->lang_id . '  and c.store_cate_id=' . $id;
        $rs = $this->storeMod->querySql($sql);
//        $rs = $this->storeMod->getData(array('cond' => "`store_cate_id` = '{$id}' and `is_open` =1 ", 'fields' => "`id`,store_name,store_cate_id"));
        if (!empty($t_id)) {
            $ruleInfo = $this->fxRulerMod->getOne(array('cond' => "`id` = '{$t_id}'", 'fields' => "store_id,store_cate"));

            if ($ruleInfo['store_id']) {
                $store_ids = explode(',', $ruleInfo['store_id']);
                foreach ($rs as $k => $v) {
                    if ($v['store_cate_id'] == $ruleInfo['store_cate']) {
                        if (in_array($v['id'], $store_ids)) {
                            $rs[$k]['flag'] = 1;
                        } else {
                            $rs[$k]['flag'] = 0;
                        }
                    }
                }
            }
        }
        $this->setData($info = $rs, $status = '1', $message = '');
    }

    /**
     * 商铺分润规则添加
     * @author wanyan
     * @date 2017-11-17
     */
    public function doRuleAdd() {
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $ruler_name = !empty($_REQUEST['ruler_name']) ? htmlspecialchars(trim($_REQUEST['ruler_name'])) : '';//分润规则名称
        $lev1_prop = !empty($_REQUEST['lev1_prop']) ? $_REQUEST['lev1_prop'] : 0;
        $lev2_prop = !empty($_REQUEST['lev2_prop']) ? $_REQUEST['lev2_prop'] : 0;
        $lev3_prop = !empty($_REQUEST['lev3_prop']) ? $_REQUEST['lev3_prop'] : 0;
        $lang_id = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : 0;
        $store_cate = !empty($_REQUEST['store_cate']) ? $_REQUEST['store_cate'] : '';
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : 0;
        //校验分润规则名称
        if (empty($ruler_name)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->rule_required);
        } else {
            $rs = $this->fxRulerMod->getOne(array('cond' => "rule_name = '{$ruler_name}' and mark =1", 'fields' => 'id'));
            if ($rs['id']) {
                $this->setData($info = array(), $status = '0', $this->langDataBank->project->rule_repeat);
            }
        }
        //校验等级分润规则
        if (empty($lev1_prop)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->one_rule_required);
        }
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $lev1_prop)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->one_rule_format);
        }
        if (empty($lev2_prop)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->two_rule_required);
        }
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $lev2_prop)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->two_rule_format);
        }
        if (empty($lev3_prop)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->three_rule_required);
        }
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $lev3_prop)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->three_rule_format);
        }
        //校验区域店铺
        if (!empty($store_cate) && empty($store_id)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->store_required);
        }
//        if (empty($lev1_prop) || empty($lev2_prop)) {
//            $lev1 = $lev1_prop;
//            $lev2 = $lev2_prop;
//            $lev3 = $lev3_prop;
//            if (empty($store_cate) && empty($store_id)) {
//                $res = $this->fxRulerMod->getOne(array('cond' => "`lev1_prop` = '{$lev1}' and `lev2_prop` = '{$lev2}' and `lev3_prop` = '{$lev3}' and mark =1", 'fields' => 'id'));
//                if ($res['id']) {
//                    $this->setData($info = array(), $status = 0, $a['fenx_Hasbeen']);
//                }
//            }
//            if (!empty($store_id)) {
//                foreach ($store_id as $k => $v) {
//                    $rs = $this->fxRulerMod->getOne(array('cond' => "`lev1_prop` = '{$lev1}' and `lev2_prop` = '{$lev2}' and `lev3_prop` = '{$lev3}' and `store_id` = '{$v}' and mark =1", 'fields' => 'id'));
//                    if ($rs['id']) {
//                        $this->setData($info = array(), $status = '0', $a['fenx_distribution']);
//                    }
//                }
//            }
//        }
        if (!empty($lev1_prop) && !empty($lev2_prop) && !empty($lev3_prop)) {
            $lev1 = $lev1_prop;
            $lev2 = $lev2_prop;
            $lev3 = $lev3_prop;
            if (empty($store_cate) && empty($store_id)) {
                $res = $this->fxRulerMod->getOne(array('cond' => "`lev1_prop` = '{$lev1}' and `lev2_prop` = '{$lev2}' and `lev3_prop` = '{$lev3}' and mark =1", 'fields' => 'id'));
                if ($res['id']) {
                    $this->setData($info = array(), $status = 0, $this->langDataBank->project->rule_exist);
                }
            }
            if (!empty($store_id)) {
                foreach ($store_id as $k => $v) {
                    $rs = $this->fxRulerMod->getOne(array('cond' => "`lev1_prop` = '{$lev1}' and `lev2_prop` = '{$lev2}' and `lev3_prop` = '{$lev3}' and `store_id` = '{$v}' and mark =1", 'fields' => 'id'));
                    if ($rs['id']) {
                        $this->setData($info = array(), $status = '0', $this->langDataBank->project->rule_assign);
                    }
                }
            }
        }
        if (!empty($store_cate) && empty($store_id)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->store_select);
        }
        $fx_discount = $this->getFxDiscount($store_cate);
        if ($lev3_prop < $fx_discount) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->rule_max . '（' . $fx_discount . '）%');
        }
        if (is_array($store_id)) {
            $store_id = implode(',', $store_id);
        }
        $insert_data = array(
            'rule_name' => $ruler_name,
            'lev1_prop' => ($lev1_prop),
            'lev2_prop' => ($lev2_prop),
            'lev3_prop' => ($lev3_prop),
            'store_id' => $store_id,
            'store_cate' => $store_cate,
            'add_time' => time()
        );
        $rs = $this->fxRulerMod->doInsert($insert_data);
        if ($rs) {
            $info['url'] = "?app=distributor&act=ruleList&lang_id={$lang_id}&p={$p}";
            $this->setData($info, $status = '1', $this->langDataBank->public->add_success);
        } else {
            $this->setData($info = array(), $status = '0', $this->langDataBank->public->add_error);
        }
    }

    /**
     * 商铺分润规则编辑
     * @author wanyan
     * @date 2017-11-17
     */
    public function editRule() {
        $id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : '';
        $rs = $this->fxRulerMod->getOne(array('cond' => "`id`='{$id}'"));
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $this->assign('p', $p);
        $this->assign('rs', $rs);
        $this->assign('act', 'ruleList');
        $storeCateList = $this->allCountry;
        $this->assign('storeCate', $storeCateList);
        $sql = 'SELECT  c.id,l.store_name  FROM  ' . DB_PREFIX . 'store  as c
                 left join  ' . DB_PREFIX . 'store_lang as l on  c.id = l.store_id
                 WHERE c.is_open = 1  and l.distinguish =0  and  l.lang_id =' . $this->lang_id . '  and c.store_cate_id=' . $rs['store_cate'];
        $storeInfo = $this->storeMod->querySql($sql);
        $this->assign('storeCateInfo', $storeInfo);
        $stores = explode(',', $rs['store_id']);
        $this->assign('storeInfo', $stores);
        $this->display('distributor/rulerEdit.html');
    }

    /**
     * 商铺分润规则编辑功能
     * @author wanyan
     * @date 2017-11-17
     */
    public function doRuleEdit() {
        $id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : '';
        $ruler_name = !empty($_REQUEST['ruler_name']) ? htmlspecialchars(trim($_REQUEST['ruler_name'])) : '';
        $lev1_prop = !empty($_REQUEST['lev1_prop']) ? $_REQUEST['lev1_prop'] : 0;
        $lev2_prop = !empty($_REQUEST['lev2_prop']) ? $_REQUEST['lev2_prop'] : 0;
        $lev3_prop = !empty($_REQUEST['lev3_prop']) ? $_REQUEST['lev3_prop'] : 0;
        $lang_id = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : 0;
        $store_cate = !empty($_REQUEST['store_cate']) ? $_REQUEST['store_cate'] : 0;
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : 0;
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        if (empty($ruler_name)) {
            $this->setData($info = array(), $status = '0',$this->langDataBank->project->rule_required);
        } else {
            $rs = $this->fxRulerMod->getOne(array('cond' => "`rule_name` = '{$ruler_name}' and `id` != '{$id}' and mark =1", 'fields' => '`id`'));
            if ($rs['id']) {
                $this->setData($info = array(), $status = '0', $this->langDataBank->project->rule_repeat);
            }
        }
        // 判断分润规则及格式
        if (empty($lev1_prop)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->one_rule_required);
        }
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $lev1_prop)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->one_rule_format);
        }
        if (empty($lev2_prop)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->two_rule_required);
        }
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $lev2_prop)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->two_rule_format);
        }
        if (empty($lev3_prop)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->three_rule_required);
        }
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $lev3_prop)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->three_rule_format);
        }

        $lev1 = $lev1_prop;
        $lev2 = $lev2_prop;
        $lev3 = $lev3_prop;
        if (empty($store_cate) && empty($store_id)) {
            $res = $this->fxRulerMod->getOne(array('cond' => "`lev1_prop` = '{$lev1}' and `lev2_prop` = '{$lev2}' and `lev3_prop` = '{$lev3}' and  `id` <> '{$id}' and mark =1", 'fields' => 'id'));
            if ($res['id']) {
                $this->setData($info = array(), $status = 0, $this->langDataBank->project->rule_exist);
            }
        }
        if (!empty($store_id)) {
            foreach ($store_id as $k => $v) {
                $rs = $this->fxRulerMod->getOne(array('cond' => "`lev1_prop` = '{$lev1}' and `lev2_prop` = '{$lev2}' and `lev3_prop` = '{$lev3}' and `store_id` = '{$v}' and `id` != '{$id}' and `mark` =1 ", 'fields' => 'id'));
                if ($rs['id']) {
                    $this->setData($info = array(), $status = '0', $this->langDataBank->project->rule_assign);
                }
            }
        }

        if (!empty($store_cate) && empty($store_id)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->store_select);
        }
        $fx_discount = $this->getFxDiscount($store_cate);
        if ($lev3_prop < $fx_discount) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->rule_max . '（' . $fx_discount . '）%');
        }
        if (is_array($store_id)) {
            $store_id = implode(',', $store_id);
        }
        $editData = array(
            'rule_name' => $ruler_name,
            'lev1_prop' => ($lev1_prop),
            'lev2_prop' => ($lev2_prop),
            'lev3_prop' => ($lev3_prop),
            'store_id' => $store_id,
            'store_cate' => $store_cate,
        );
        $oldData = $this->fxRulerMod->getRow($id);
        //比例发生改变时，向fx_rule表插入一条新数据，更改fx_user表中的rule_id，旧数据mark=0
        if ($lev1 != $oldData['lev1_prop'] || $lev2 != $oldData['lev2_prop'] || $lev3 != $oldData['lev3_prop']) {
            //旧数据mark=0
            $oldData['mark'] = 0;
            $editResult = $this->fxRulerMod->doEdit($id, $oldData);
            if (!$editResult) {
                $this->setData($info = array(), $status = '0', $this->langDataBank->public->cz_error);
            }
            //fx_rule表插入新数据
            $editData['add_time'] = $oldData['add_time'];
            $editData['mark'] = 1;
            $addResult = $this->fxRulerMod->doInsert($editData);
            if (!$addResult) {
                $this->setData($info = array(), $status = '0', $this->langDataBank->public->cz_error);
            }
            //更新fx_user表的rule_id
            $userData = $this->fxuserMod->getData(array('cond' => 'rule_id='.$id));
            if ($userData) {
                $userIds = array();
                foreach ($userData as $key => $value) {
                    array_push($userIds, $value['id']);
                }
                $userIds = implode(',', $userIds);
                $editSql = "update bs_fx_user set rule_id = {$addResult} where id in ({$userIds})";
                $result = $this->fxuserMod->doEditSql($editSql);
            } else {
                $result = true;
            }
        } else {
            $result = $this->fxRulerMod->doEdit($id, $editData);
        }
        if ($result) {
            $info['url'] = "?app=distributor&act=ruleList&lang_id={$lang_id}&p={$p}";
            $this->setData($info, $status = '1', $this->langDataBank->public->cz_success);
        } else {
            $this->setData($info = array(), $status = '0', $this->langDataBank->public->cz_error);
        }
    }

    /**
     * 根据店铺ID 获取 店铺所属国家
     */
    public function getFxDiscount($storeCate) {
        $storeCateMod = &m('storeCate');
        $res = $storeCateMod->getOne(array('cond' => "`id`='{$storeCate}'", 'fields' => "fx_discount"));
        return $res['fx_discount'];
    }

    /**
     * 商铺分润规则编辑功能
     * @author wanyan
     * @date 2017-11-17
     */
    public function dele() {
        $id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : '';
        $rs = $this->fxRulerMod->doMark($id);
        if ($rs) {
            $this->setData($info = array(), $status = '1', $this->langData->public->edit_success);
        } else {
            $this->setData($info = array(), $status = '0', $this->langData->public->edit_fail);
        }
    }


    /**  分销规则end  ***/



    /**  分销调级功能  ***/

    /**
     * 获取所有店铺
     * @author Run
     */
    public function allStore(){
        //所有店铺
        $storeMod = &m('store');
        $sqlA =" SELECT s.id,sl.`store_name` AS sltore_name FROM bs_store AS s
            LEFT JOIN bs_store_lang AS sl ON sl.`store_id` = s.`id` AND sl.lang_id = ". $this->lang_id ."
             WHERE	sl.distinguish = 0 ";
        $list = $storeMod->querySql($sqlA);
        return $list;
    }
    /**
     * 店铺分销人员调级
     * @author Run
     * @date 2018-10-15
     */
    public function fxupgrade()
    {

        $fxUserAccountMod = &m('fxUserAccount');
        $land_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : $this->lang_id;
        $this->assign('land_id', $land_id);
        $store_id = $_REQUEST['store_id'] ? $_REQUEST['store_id'] : 0;
        $this->assign('store_id', $store_id);
        $area_id = $_REQUEST['area_id'] ? htmlspecialchars(trim($_REQUEST['area_id'])) : '';
        $this->assign('area_id', $area_id);
        //获取区域数组
        $storeCateMod = &m('storeCate');
        $areaArr = $storeCateMod->getAreaArr(1,$this->lang_id);
        $areaOption = make_option($areaArr, $area_id);
        $this->assign('areaOption', $areaOption);

        if ($area_id) {
            $this->assign('area_id', $area_id);
            if ($store_id) {
                $this->assign('store_id', $store_id);
            }
            //获取区域店铺
            $storeMod = &m('store');
            $storeArr = $storeMod->getStoreArr($area_id, 1);
            $storeOption = make_option($storeArr, $store_id);
            $this->assign('storeOption', $storeOption);
        }
        $whe = " WHERE level=1 and parent_id = 0 and status = 1 ";
        if($store_id){
            $whe .= " and `store_id` =" . $store_id;
        }
        //获取一级分销
        $sql1 = " SELECT * FROM bs_fx_user" .$whe;
        $res = $this->fxuserMod->querySql($sql1);
        foreach ($res as $key => $val) {
            $res[$key]['fx_level'] = $this->fxuserMod->level[$val['level']];
            $lev2 = $this->getchilds($val['id']);
            if (!empty($lev2)) {
                // 2 级
                $res[$key]['childs'] = $lev2;
                // 3级
                foreach ($lev2 as $k => $v) {
                    $res[$key]['childs'][$k]['fx_level'] = $this->fxuserMod->level[$v['level']];
                    $lev3 = $this->getchilds($v['id']);
                    if (!empty($lev3)) {
                        $res[$key]['childs'][$k]['childs'] = $lev3;
                        foreach ($lev3 as $k1 =>$v1){
                            if($fxUserAccountMod ->checkUserAccount($v1['id'],1) > 0){
                                $res[$key]['childs'][$k]['childs'][$k1]['have_user'] = 1;
                            }
                            $res[$key]['childs'][$k]['childs'][$k1]['fx_level'] = $this->fxuserMod->level[$v1['level']];
                        }
                    }
                }
            }
        }
        $this->assign('storeList', $this->allStore());
        $this->assign('res', $res);
        $this->display('distributor/level.html');
    }
    /**
     * 一级人员的替换
     * @author Run
     */
    public function replace() {
        $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars(($_REQUEST['store_id'])) : '';
        $fx_id = $_REQUEST['fx_id'] ? htmlspecialchars(trim($_REQUEST['fx_id'])) : '';
        $area_id = $_REQUEST['area_id'] ? htmlspecialchars(trim($_REQUEST['area_id'])) : '';
        $this->assign('area_id', $area_id);
        //一级分销列表（不包括自己);
        $sql = 'SELECT id,real_name FROM bs_fx_user WHERE status = 1 and parent_id = 0 and id !=' . $fx_id;
        $lev1 = $this->fxuserMod->querySql($sql);
        $this->assign('store_id', $store_id);
        $this->assign('fx_id', $fx_id);
        $this->assign('lev1', $lev1);
        $this->assign('act', 'index');
        $this->display('distributor/replace.html');
    }
    /**
     * 一级人员的替换执行操作
     * @author Run
     */
    public function doreplace() {
        $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars(($_REQUEST['store_id'])) : '';
        $area_id = $_REQUEST['area_id'] ? htmlspecialchars(trim($_REQUEST['area_id'])) : '';
        $this->assign('area_id', $area_id);
        $fx_id = $_REQUEST['fx_id'] ? htmlspecialchars(trim($_REQUEST['fx_id'])) : '';
        $new_fx_id = $_REQUEST['new_fx_id'] ? htmlspecialchars(trim($_REQUEST['new_fx_id'])) : '';
        if (empty($new_fx_id)) {
            $this->setData(array(), '0', $this->langDataBank->project->replace_required);
        }
        //获取所有下级
        $sql = 'SELECT id,real_name FROM bs_fx_user WHERE parent_id =' . $fx_id;
        $levDownInfo = $this->fxuserMod->querySql($sql);
        //获取下级的ID
        foreach ($levDownInfo as $v){
            $ids[] =$v['id'];
        }
        $ids = implode(',',$ids);
        $arr['parent_id'] = $new_fx_id;
        //联动更改二级三级分销人员的rule_id
        $userData = $this->fxuserMod->getRow($new_fx_id);
        $this->fxuserMod->updateRule($fx_id, $userData['rule_id']);
        //更新下级归属
        $upSql = "UPDATE bs_fx_user SET parent_id = {$new_fx_id} WHERE id in({$ids})";//更新所有三级的父级
        $res = $this->fxuserMod->doEditSql($upSql);
//        $data['status'] = 2;
//        $res = $this->fxuserMod->doEdit($fx_id, $data);
        if ($res) {
            $info['url'] = "admin.php?app=distributor&act=memberList&store_id={$store_id}&area_id={$area_id}";
            $this->setData($info, '1', $this->langDataBank->project->replace_success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->project->replace_fail);
        }
    }
    /**
     * 二级分销人员替换
     * @author Run
     */
    public function replaceTwo(){
        $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars(($_REQUEST['store_id'])) : '';
        $area_id = $_REQUEST['area_id'] ? htmlspecialchars(trim($_REQUEST['area_id'])) : '';
        $this->assign('store_id', $store_id);
        $this->assign('area_id', $area_id);
        $fx_id = $_REQUEST['fx_id'] ? htmlspecialchars(trim($_REQUEST['fx_id'])) : '';
        $new_fx_id = $_REQUEST['new_fx_id'] ? htmlspecialchars(trim($_REQUEST['new_fx_id'])) : '';
        $parent_id = $_REQUEST['parent_id'] ? htmlspecialchars(trim($_REQUEST['parent_id'])) : '';
        $this->assign('fx_id', $fx_id);
        //同级分销人员（不包括自己);
        $sql = 'SELECT id,real_name FROM bs_fx_user WHERE status = 1 and parent_id = '.$parent_id.' and id !=' . $fx_id;
        $lev2 = $this->fxuserMod->querySql($sql);
        $this->assign('lev2', $lev2);
        if(IS_POST){
            if(empty($new_fx_id)){
                $this->setData(array(), '0',  $this->langDataBank->project->replace_required);
            }
            //获取所有下级
            $sql = 'SELECT id,real_name FROM bs_fx_user WHERE parent_id =' . $fx_id;
            $levDownInfo = $this->fxuserMod->querySql($sql);
            if($levDownInfo){//有下级的情况下进行替换操作
                //获取下级的ID
                foreach ($levDownInfo as $v){
                    $ids[] =$v['id'];
                }
                $ids = implode(',',$ids);
                $arr['parent_id'] = $new_fx_id;
                //更新下级归属
                $upSql = "UPDATE bs_fx_user SET parent_id = {$new_fx_id} WHERE id in({$ids})";//更新所有三级的父级
                $res = $this->fxuserMod->doEditSql($upSql);
            }
//            $data['status'] = 2;
//            $res = $this->fxuserMod->doEdit($fx_id, $data);
            if ($res) {
                $info['url'] = "admin.php?app=distributor&act=memberList&store_id={$store_id}&area_id={$area_id}";
                $this->setData($info, '1',  $this->langDataBank->project->replace_success);
            } else {
                $this->setData(array(), '0', $this->langDataBank->project->replace_fail);
            }
        }
        $this->display('distributor/replaceTwo.html');
    }
    /**
     * 三级分销人员替换
     * @author Run
     */
    public function replaceThree(){
        $fxUserAccountMod = &m('fxUserAccount');
        $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars(($_REQUEST['store_id'])) : '';
        $area_id = $_REQUEST['area_id'] ? htmlspecialchars(trim($_REQUEST['area_id'])) : '';
        $this->assign('store_id', $store_id);
        $this->assign('area_id', $area_id);
        $fx_id = $_REQUEST['fx_id'] ? htmlspecialchars(trim($_REQUEST['fx_id'])) : '';
        $new_fx_id = $_REQUEST['new_fx_id'] ? htmlspecialchars(trim($_REQUEST['new_fx_id'])) : '';
        $parent_id = $_REQUEST['parent_id'] ? htmlspecialchars(trim($_REQUEST['parent_id'])) : '';
        $this->assign('fx_id', $fx_id);
        //同级分销人员（不包括自己);
        $sql = 'SELECT id,real_name FROM bs_fx_user WHERE status = 1 and parent_id = '.$parent_id.' and id !=' . $fx_id;
        $lev3 = $this->fxuserMod->querySql($sql);
        $this->assign('lev3', $lev3);
        if(IS_POST){
            $res = $fxUserAccountMod ->fxAccountChange($new_fx_id,$fx_id);
//            $data['status'] = 2;
//            $res = $this->fxuserMod->doEdit($fx_id, $data);
            if ($res) {
                $info['url'] = "admin.php?app=distributor&act=memberList&store_id={$store_id}&area_id={$area_id}";
                $this->setData($info, '1', $this->langDataBank->project->replace_success);
            } else {
                $this->setData(array(), '0', $this->langDataBank->project->replace_fail);
            }
        }

        $this->display('distributor/replaceThree.html');
    }
    /**
     * 获取下级分销人员
     * @author Run
     */
    public function getchilds($pid) {
        $sql = 'select * from bs_fx_user where  status = 1 and parent_id =' . $pid;
        $res = $this->fxuserMod->querySql($sql);
        return $res;
    }

    /**
     * 分销调级二级升一级
     * @author Run
     */
    public function levOne() {
        $fx_id =$_REQUEST['fx_id'] ? htmlspecialchars(intval($_REQUEST['fx_id'])) : '';//fen
        $store_id = $_REQUEST['store_id'] ? htmlspecialchars(intval($_REQUEST['store_id'])) : '';
        $area_id = $_REQUEST['area_id'] ? htmlspecialchars(trim($_REQUEST['area_id'])) : '';
        $this->assign('area_id', $area_id);
        //本组的三级分销人员
        $secondFxUserSql="SELECT b.id FROM ".DB_PREFIX."fx_user as b LEFT JOIN ".DB_PREFIX."fx_user as a ON a.parent_id=b.id WHERE a.id=".$fx_id;
        $secondFxUserData=$this->fxuserMod->querySql($secondFxUserSql);
        $firstFxUserId=$secondFxUserData[0]['id'];
        $sql2 = 'SELECT id,real_name FROM bs_fx_user WHERE parent_id =' . $firstFxUserId.' and id !='.$fx_id;
        $lev3 = $this->fxuserMod->querySql($sql2);
        if (!empty($lev3)) {
            $flag = 1;
        } else {
            $flag = 0;
        }
        //分销规则
        $sql_rule = "SELECT  *  FROM  bs_fx_rule  WHERE  mark = 1 AND  (FIND_IN_SET('" . $store_id . "',store_id)  OR store_id =0)";
        $res_rule = $this->fxruleMod->querySql($sql_rule);
        $this->assign('res_rule', $res_rule);
        $this->assign('store_id', $store_id);
        $this->assign('act', 'index');
        $this->assign('lev3', $lev3);
        $this->assign('flag', $flag);
        $this->assign('fx_id', $fx_id);
        $this->display('distributor/levone.html');
    }
    /**
     * 分销调级二级升一级执行方法
     * @author Run
     */
    public function dolevOne() {
        $fx_id =$_REQUEST['fx_id'] ? htmlspecialchars(intval($_REQUEST['fx_id'])) : '';
        $flag =$_REQUEST['flag'] ? htmlspecialchars(intval($_REQUEST['flag'])) : '';
        $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars(($_REQUEST['store_id'])) : '';
        $area_id = $_REQUEST['area_id'] ? htmlspecialchars(trim($_REQUEST['area_id'])) : '';
        $fx_rule = !empty($_REQUEST['fx_rule']) ? $_REQUEST['fx_rule'] : 0;
        $rep_lev3 = !empty($_REQUEST['rep_lev3']) ? $_REQUEST['rep_lev3'] : 0;  //替换的fx_id

        if (empty($fx_rule)) {
            $this->setData(array(), '0', $this->langDataBank->project->rule_empty);
        }
        // 2中情况
        if ($flag) {  //这个组有 三级分销人员
            if (empty($rep_lev3)) {
                $this->setData(array(), '0', $this->langDataBank->project->replace_required);
            }
            //获取组内所有三级人员ID
            $sql = ' SELECT  id  FROM  bs_fx_user   WHERE parent_id=' . $fx_id;
            $newInfo = $this->fxuserMod->querySql($sql);
            foreach ($newInfo as $v){
                $ids[] = $v['id'];
            }
            $ids = implode(',',$ids);
            $arr['parent_id'] = $rep_lev3;
            $upSql = "UPDATE bs_fx_user SET parent_id = {$rep_lev3} WHERE id in({$ids})";//更新所有三级的父级
            $this->fxuserMod->doEditSql($upSql);
//            print_r($upSql);die;
            //2级替换二级
            /*        $data_r = array(
                        'parent_id' => $fx_id,
                        'level' =>2,
                    );
                    $this->fxuserMod->doEdit($rep_lev3, $data_r);*/
            //2级升为一级
            $data_p = array(
                'parent_id' => 0,
                'level' => 1,
                'rule_id' => $fx_rule,
            );
            $res =  $this->fxuserMod->doEdit($fx_id, $data_p);
        } else {  //没有三级分销人员
            //2级升为一级
            $data_p = array(
                'parent_id' => 0,
                'level' => 1,
                'rule_id' => $fx_rule,
            );
            $res = $this->fxuserMod->doEdit($fx_id, $data_p);
        }
        if ($res) {
            $info['url'] = "admin.php?app=distributor&act=memberList&store_id={$store_id}&area_id={$area_id}";
            $this->setData($info, '1', $this->langDataBank->project->adjust_success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->project->adjust_fail);
        }
    }

    /**
     * 三级升二级
     */
    public function upTwo() {
        $fxUserAccountMod = &m('fxUserAccount');
        $store_id   = !empty($_REQUEST['store_id']) ? htmlspecialchars(($_REQUEST['store_id'])) : '';
        $fx_id      = !empty($_REQUEST['fx_id']) ? htmlspecialchars(intval($_REQUEST['fx_id'])) : '';
        $parent_id  = !empty($_REQUEST['parent_id']) ? htmlspecialchars(intval($_REQUEST['parent_id'])) : '';//父级ID
        $area_id = $_REQUEST['area_id'] ? htmlspecialchars(trim($_REQUEST['area_id'])) : '';

        //所属一级
        $sql = ' SELECT  id,real_name FROM  bs_fx_user   WHERE level= 1 and status = 1';
        $OneInfo = $this->fxuserMod->querySql($sql);
        if(IS_POST){
            $count =  $fxUserAccountMod ->checkUserAccount($fx_id,1);
            if($count>0){
                $this->setData(array(), '0', $this->langDataBank->project->replace_exist);
            }
            if(empty($parent_id)){
                $this->setData(array(), '0', $this->langDataBank->project->select_oneFx);
            }
            $data = array(
                'level' => 2,
                'parent_id' => $parent_id,
            );
            $res = $this->fxuserMod->doEdit($fx_id, $data);
            if ($res) {
                $info['url'] = "admin.php?app=distributor&act=memberList&store_id={$store_id}";
                $this->setData($info, '1', $this->langDataBank->project->adjust_success);
            } else {
                $this->setData(array(), '0', $this->langDataBank->project->adjust_fail);
            }
        }
        $this->assign('area_id', $area_id);
        $this->assign('act', 'index');
        $this->assign('store_id', $store_id);
        $this->assign('fx_id', $fx_id);
        $this->assign('OneInfo', $OneInfo);
        $this->display('distributor/uptwo.html');
    }

    public function douptwo() {
        $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars(($_REQUEST['store_id'])) : '';
        $fx_id = $_REQUEST['fx_id'];
        $pid = $_REQUEST['pid'];
        $data = array(
            'level' => 2,
            'parent_id' => $pid,
        );
        $res = $this->fxuserMod->doEdit($fx_id, $data);
        if ($res) {
            $info['url'] = "admin.php?app=distributor&act=memberList&store_id={$store_id}";
            $this->setData($info, '1', $this->langDataBank->project->adjust_success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->project->adjust_fail);
        }
    }


    /**  分销设置功能  ***/
    /**
     * 分销设置列表
     * @author Run
     * @date 2018-10-15 hhn
     */
    public function fxSetUpList() {
        $fxSiteMod = &m('fxSite');
        //获取区域店铺
        $storeMod = &m('store');
        $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars(($_REQUEST['store_id'])) : 0;
        $area_id = !empty($_REQUEST['area_id']) ? htmlspecialchars(($_REQUEST['area_id'])) : 0;

        $whe = " where a.mark = 1 and d.lang_id =".$this->lang_id;
        //获取区域数组
        $storeCateMod = &m('storeCate');
        $areaArr = $storeCateMod->getAreaArr(1,$this->lang_id);
        $areaOption = make_option($areaArr, $area_id);
        $this->assign('areaOption', $areaOption);

        if ($area_id) {
            $this->assign('area_id', $area_id);
            $whe .= " and b.store_cate_id=".$area_id;
            if ($store_id) {
                $this->assign('store_id', $store_id);
                $whe .= " and a.store_id=".$area_id;
            }
            $storeArr = $storeMod->getStoreArr($area_id, 1);
            $storeOption = make_option($storeArr, $store_id);
            $this->assign('storeList', $storeOption);
//            echo "<pre>";print_r($storeArr);
        }
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "fx_site as a 
            LEFT JOIN bs_store_lang as d on d.store_id = a.store_id AND (d.distinguish =0) where a.mark =1 and d.lang_id =".$this->lang_id;
        $totalCount = $this->fxRulerMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $sql = " SELECT a.*,d.store_name,c.symbol FROM bs_fx_site as a
            LEFT JOIN bs_store as b on b.id = a.store_id
            LEFT JOIN bs_currency as c on c.id = b.currency_id
            LEFT JOIN bs_store_lang as d on d.store_id = a.store_id AND (d.distinguish =0) ".$whe;
        $data = $fxSiteMod->querySqlPageData($sql);
        foreach ($data['list'] as $k => $v) {
            $data['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            $data['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
//            $data['list'][$k]['sort_id'] = $total - $k - 20 * ($p - 1); //倒叙
        }
        $this->assign('list', $data['list']);
        $this->assign('page_html', $data['ph']);
        $this->display('distributor/fxSetUpList.html');
    }

    /**
     * 分销设置功能
     * @author Run
     * @date 2018-10-15 hhn
     */
    public function fxSetUp() {
        $fxSiteMod = &m('fxSite');
        //获取区域店铺
        $storeMod = &m('store');
        $id = !empty($_REQUEST['id']) ? htmlspecialchars(($_REQUEST['id'])) : '';
        $info = $fxSiteMod->getOne(array("cond"=>"id=" . $id));
        $storInfo = $storeMod->getOne(array("cond"=>"id=" . $info['store_id']));
        $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars(($_REQUEST['store_id'])) : $info['store_id'];
        $area_id = !empty($_REQUEST['area_id']) ? htmlspecialchars(($_REQUEST['area_id'])) : $storInfo['store_cate_id'];
        //获取区域数组
        $storeCateMod = &m('storeCate');
        $areaArr = $storeCateMod->getAreaArr(1,$this->lang_id);
        $areaOption = make_option($areaArr, $area_id);
        $this->assign('areaOption', $areaOption);

        if ($area_id) {
            $this->assign('area_id', $area_id);
            if ($store_id) {
                $this->assign('store_id', $store_id);
            }
            $storeArr = $storeMod->getStoreArr($area_id, 1);
            $storeOption = make_option($storeArr, $store_id);
            $this->assign('storeList', $storeOption);
//            echo "<pre>";print_r($storeArr);
        }
//        $info = $fxSiteMod->getOne(array("cond"=>"id=" . $id));
        $this->assign("datas", $info);
//        $this->assign('storeList', $this->allStore());
        $this->assign('store_id',$store_id);
        $this->assign('symbol', $this->getSymbol($store_id));
        $this->display('distributor/fxSetUp.html');
    }
    /**
     * 区域分销配置
     * @author lee
     * @date 2018-10-15
     */
    public function fxSetUpEdit() {
        $fxSiteMod = &m('fxSite');
        $id = ($_REQUEST['id']) ? $_REQUEST['id'] : '';
//        $order_day = ($_REQUEST['order_day']) ? $_REQUEST['order_day'] : '';
        $is_money = ($_REQUEST['is_money']) ? $_REQUEST['is_money'] : 2;
        $money = ($_REQUEST['money']) ? $_REQUEST['money'] : '';
        $is_time = ($_REQUEST['is_time']) ? $_REQUEST['is_time'] : 2;
        $time = ($_REQUEST['time']) ? $_REQUEST['time'] : '';
        $store_id = $_REQUEST['single_store']?$_REQUEST['single_store']:'';
        $area_id = !empty($_REQUEST['single_area']) ? htmlspecialchars(($_REQUEST['single_area'])) : '';
        $conf =" store_id=".$store_id." and mark = 1 ";
        if($id){
            $conf.= " and id !=id";
        }
        $info  = $fxSiteMod->getIds(array('conf' => $conf));
        if( $info){
            $this->setData(array(), $status = '0', $this->langDataBank->project->store_set);
        }
//        if (empty($order_day)) {
//            $this->setData(array(), $status = '0', $a['fx_order']);
//        }
        if (($is_money == 1) && empty($money)) {
            $this->setData(array(), $status = '0', $this->langDataBank->project->amount);
        }
        if (($is_time == 1) && empty($time)) {
            $this->setData(array(), $status = '0', $this->langDataBank->project->fill_time);
        }
        $data = array(
//            'order_day' => $order_day,
            'is_money' => $is_money,
            'money' => $money,
            'is_time' => $is_time,
            'time' => $time,
            'add_time' => time(),
        );
        if($id){
            $arr['mark'] = 0;
            $fxSiteMod->doEdit($id, $arr);
        }
        $data['store_id'] =$store_id;
        $res = $fxSiteMod->doInsert($data);
        if ($res) {
            $this->setData(array('url' => "?app=distributor&act=fxSetUpList"), $status = '1', $this->langDataBank->public->edit_success);
        } else {
            $this->setData(array(), $status = '0', $this->langDataBank->public->edit_fail);
        }
    }
    /**
     * 关于钱符号的一个东西
     */
    public function getSymbol($store_id) {
        $storeMod = &m('store');
        $sql = "select c.symbol from " . DB_PREFIX . "store as s
                left join " . DB_PREFIX . "currency as c on s.currency_id = c.id where s.id = '{$store_id}'";
        $rs = $storeMod->querySql($sql);
        return $rs[0]['symbol'];
    }

    /**
     * 获取区域店铺
     */
    public function getAreaToStoreAjax($store_id) {
        $area_id = !empty($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : '';
        //获取区域店铺
        $storeMod = &m('store');
        $storeArr = $storeMod->getStoreArr($area_id, 1);
        $storeOption = make_option($storeArr, $store_id);
        $this->setData($storeOption, $status = '1', '');
    }
    /**
     * 分销订单
     * @author tangp
     * @date 2018-10-16
     */
    public function fxOrder()
    {
//        echo '优化调研中！';die;
        $goods_name = !empty($_REQUEST['goods_name']) ? htmlspecialchars(trim(addslashes($_REQUEST['goods_name']))) : '';
        $store_id = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : '';
        $area_id = !empty($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : '';
        $payment_code = !empty($_REQUEST['payment_code']) ? htmlspecialchars(trim($_REQUEST['payment_code'])) : '';
        $buyer_email = !empty($_REQUEST['buyer_email']) ? htmlspecialchars(trim($_REQUEST['buyer_email'])) : '';
        $buyer_name = !empty($_REQUEST['buyer_name']) ? htmlspecialchars(trim($_REQUEST['buyer_name'])) : '';
        $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars(trim($_REQUEST['order_sn'])) : '';
        $shipping_code = !empty($_REQUEST['shipping_code']) ? htmlspecialchars(trim($_REQUEST['shipping_code'])) : '';
        $store = !empty($_REQUEST['store']) ? htmlspecialchars(trim($_REQUEST['store'])) : '';
        $state = !is_null($_REQUEST['state']) ? htmlspecialchars(trim($_REQUEST['state'])) : 'month_this';
        $username = !empty($_REQUEST['username']) ? htmlspecialchars(trim($_REQUEST['username'])) :'';
        $source = !empty($_REQUEST['source']) ? intval($_REQUEST['source']) : '';
        $orderState = !is_null($_REQUEST['order_state']) ? intval($_REQUEST['order_state']) : 99;
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $fxOrder = &m('fxOrder');
        //获取区域数组
        $storeCateMod = &m('storeCate');
        $areaArr = $storeCateMod->getAreaArr(1,$this->lang_id);
        $areaOption = make_option($areaArr, $area_id);
        $this->assign('areaOption', $areaOption);
        if ($area_id) {
            $this->assign('area_id', $area_id);
            if ($store_id) {
                $this->assign('store_id', $store_id);
            }
            //获取区域店铺
            $storeMod = &m('store');
            $storeArr = $storeMod->getStoreArr($area_id, 1);
            $storeOption = make_option($storeArr, $store_id);
            $this->assign('storeOption', $storeOption);
        }
        //订单来源
        $this->assign('sourceList', $fxOrder->source);
        $where = '  where  1=1 and o.mark = 1 ';
        if (!empty($goods_name)) {
            $where .= " and og.goods_name like '%" . $goods_name . "%'";
        }
        if (!empty($payment_code)) {
            $where .= " and o.payment_code like '%" . $payment_code . "%'";
        }
        if (!empty($buyer_email)) {
            $where .= " and o.buyer_email like '%" . $buyer_email . "%'";
        }
        if (!empty($buyer_name)) {
            $where .= " and o.buyer_name like '%" . $buyer_name . "%'";
        }
        if (!empty($order_sn)) {
            $where .= " and o.order_sn like '%" . $order_sn . "%'";
        }
        if (!empty($store_id)) {
            $where .= " and o.store_id like '%" . $store_id . "%'";
        }
        if (!empty($area_id)) {
            $where .= " and fo.store_cate =000000" . $area_id ;
        }
        if (!empty($shipping_code)) {
            $where .= " and o.shipping_code like '%" . $shipping_code . "%'";
        }
        if (!empty($username)){
            $where .= " and fu.real_name like '%" . $username . "%'";
        }
        if (!empty($source)){
            $where .= " and fo.source =" . $source;
        }
        if ($orderState != 99){
            $where .= " and o.order_state =" . $orderState;
        }
        $this->assign("p", $p);
        $this->assign("state", $state);
        $this->assign('goods_name', $goods_name);
        $this->assign('payment_code', $payment_code);
        $this->assign('buyer_email', $buyer_email);
        $this->assign('buyer_name', $buyer_name);
        $this->assign('order_sn', $order_sn);
        $this->assign('shipping_code', $shipping_code);
        $this->assign('username',$username);
        $this->assign('store_id', $store_id);
        $this->assign('area_id', $area_id);
        $this->assign('source', $source);
        $this->assign('orderState', $orderState);
        $sql = "SELECT fu.real_name as username,fu.discount as ddiscount,o.delivery_status,fo.source,fo.add_user,fo.order_sn,o.add_time,o.order_state,o.goods_amount,o.order_amount, fo.source as order_source FROM bs_fx_order AS fo
              LEFT JOIN bs_order AS o ON fo.order_id = o.order_id
              LEFT JOIN bs_fx_user AS fu ON fu.id = fo.fx_user_id
              LEFT JOIN bs_user AS u ON fo.user_id = u.id {$where}  order by o.order_id desc " ;
//        echo $sql;die;
        $orderGoodsMod = &m('orderGoods');
        $giftGood = &m('giftGood');
        $result = $fxOrder->querySqlPageData($sql);
        $data = $result['list'];
        foreach ($data as $k => $v){
            $cond = array(
                'cond' => "order_id ='{$v['order_sn']}' "
            );
            $list = $orderGoodsMod->getData($cond);
            $data[$k]['goods_list'] = $list;
            //赠品
            $sql = "select * from " . DB_PREFIX . "gift_goods where id=" . $v['gift_id'];
            $res = $giftGood->querySql($sql);
            $data[$k]['gift'] = $res;
            $data[$k]['source_name'] = $fxOrder->source[$v['order_source']];
        }
//        echo '<pre>';print_r($v);die;
        $OrderStatus = array(
            "0" => $this->langDataBank->project->order_cancel,
            "10" => $this->langDataBank->project->buyer_not_pay,
            "20" => $this->langDataBank->project->buyer_paid,
            "30" => $this->langDataBank->project->seller_shipped,
            "40" => $this->langDataBank->project->area_send,
            "50" => $this->langDataBank->project->buyer_received,
        );
//        echo '<pre>';print_r($OrderStatus);die;
        $this->assign("p", $p);
        $this->assign('statusList', $OrderStatus);
        $this->assign('data',$data);
        $this->assign('store', $this->getUseStore());
        $this->assign('page_html',$result['ph']);
        $this->display('distributor/fxOrder.html');
    }
    public function details()
    {
        $order_id = $_REQUEST['order_id'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $where = "where g.order_id= '{$order_id}' ";
        $orderMod= &m('order');
        $orderGoodsMod = &m('orderGoods');
        //列表页数据
        $sql = 'select *,g.add_time from '
            . DB_PREFIX . 'order as g left join '
            . DB_PREFIX . 'user_address a' . ' on a.user_id = g.buyer_id ' . $where;
//        echo $sql;die;
        $info = $orderMod->querySql($sql);
        foreach ($info as $k => $v) {
            $v_where = "order_id= '{$v['order_sn']}'" ;
            $cond = array(
                'cond' => $v_where
            );
            $list = $orderGoodsMod->getData($cond);
            $info[$k]['goods_list'] = $list;
        }
        $this->assign('info', $info[0]);
        //以order_sn 查询退款商品记录
        $refund_sql = "select * from " . DB_PREFIX . "refund_return as r"
            . " where r.order_sn=" . $info[0]['order_sn']
            . " and r.order_id=" . $info[0]['order_id'];
//        print_r($refund_sql);exit;
        $refund_goods = $orderMod->querySql($refund_sql);  // 退款商品列表
        $this->assign("refund_goods", $refund_goods);
        $OrderStatus = array(
            "0" => $this->langDataBank->project->order_cancel,
            "10" => $this->langDataBank->project->buyer_not_pay,
            "20" => $this->langDataBank->project->buyer_paid,
            "30" => $this->langDataBank->project->seller_shipped,
            "40" => $this->langDataBank->project->area_send,
            "50" => $this->langDataBank->project->buyer_received,
        );
        $user_sql = 'select username from ' . DB_PREFIX . 'user where id = ' . $info[0]['buyer_id'];
        $username = $orderMod->querySql($user_sql);
        $this->assign('p', $p);
        $this->assign('username', $username[0]['username']);
        $this->assign('status', $OrderStatus);
        $this->assign('lang_id', $this->lang_id);
        $this->assign('store', $this->getUseStore());
        $this->display('distributor/details.html');
    }
    /**
     * 获取启用的站点
     * @author wang'shuo
     * @date 2017-12-25
     */
    public function getUseStore() {
        $sql = 'SELECT  c.id,l.store_name  FROM  ' . DB_PREFIX . 'store  as c
                 left join  ' . DB_PREFIX . 'store_lang as l on  c.id = l.store_id
                 WHERE c.is_open = 1 and  l.distinguish = 0  and  l.lang_id =' . $this->defaulLang . ' order by c.id';
        $res = $this->storeMod->querySql($sql);
        return $res;
    }

    /**
     *
     */
    public function cashList()
    {

    }
    /**  提现日志  ***/

    /**
     * 提现列表
     * @author Run
     * @date 2018-10-19
     */
    public function cashlistLog(){
        $fxOutmoneyApplyMod = &m('fxOutmoneyApply');
        $land_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : 0;
        $real_name = !empty($_REQUEST['real_name']) ? htmlspecialchars(trim($_REQUEST['real_name'])) : 0;
        $status = !empty($_REQUEST['status']) ? intval($_REQUEST['status']) : 0;
        $source = $_REQUEST['source'] ? $_REQUEST['source'] : 0;
        //账号来源
        $this->assign('sourceList', $fxOutmoneyApplyMod->source);
        $this->assign('lang_id', $land_id);
        $where = ' where 1=1';
        if($real_name){
            $where .= " and b.real_name like '%" . $real_name . "%'";
            $this->assign('real_name', $real_name);
        }
        if($status){
            $where .= " and a.is_check =".$status;
            $this->assign('status', $status);
        }
        if ($source) {
            $where .= " and a.source =".$source;
            $this->assign('source', $source);
        }
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "fx_outmoney_apply " ;
        $totalCount = $fxOutmoneyApplyMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $sql =" SELECT a.*,b.user_id,b.real_name,b.phone from ".DB_PREFIX."fx_outmoney_apply as a 
                LEFT JOIN ".DB_PREFIX."fx_user as b on b.id = a.fx_user_id".$where." order by a.is_check asc,a.id desc";
        $rs = $fxOutmoneyApplyMod ->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        foreach ($rs['list'] as $k => $v){
//            $rs['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
            $rs['list'][$k]['source_name'] = $fxOutmoneyApplyMod->source[$v['source']];
            $rs['list'][$k]['sort_id'] = $total - $k - 20 * ($p - 1); //倒叙
        }

        $this->assign('list', $rs['list']);
        $this->assign('page_html', $rs['ph']);
        $this->display('distributor/cashList.html');
    }

    /**
     * 提现审核
     * @author Run
     * @date 2018-10-19
     */
    public function cashCheck(){
        $fxOutmoneyApplyMod = &m('fxOutmoneyApply');
        $id                 = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
        $land_id            = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : 0;
        $is_check           = !empty($_REQUEST['is_check']) ? intval($_REQUEST['is_check']) : 0;
        $info = $fxOutmoneyApplyMod->getRow($id);
        if(IS_POST){
            if(empty($id)){
                $this->setData(array(), $status = '0', $this->langDataBank->public->parameter_error);
            } if(empty($is_check)){
                $this->setData(array(), $status = '0', $this->langDataBank->project->select_status);
            }
            $data ['is_check']      =$is_check;
            $data ['check_time']    = time();
            $data ['check_user']    = $this->accountId;
            $res = $fxOutmoneyApplyMod->doEdit($id ,$data);
            if ($is_check == 2) {
                $fxUserData = $this->fxuserMod->getRow($info['fx_user_id']);
                $money = $fxUserData['monery'] - $info['apply_money'];
                $this->fxuserMod->doEdit($info['fx_user_id'], array('monery'=>$money));
            }
            if ($res) {
                $this->setData(array('url' => "?app=distributor&act=cashlistLog"), $status = '1', $this->langDataBank->public->success_review);
            } else {
                $this->setData(array(), $status = '0', $this->langDataBank->public->fail_review);
            }
        }

        $this->assign('lang_id', $land_id);
        $this->assign('is_check', $info['is_check']);
        $this->assign('id', $id);
        $this->display('distributor/cashCheck.html');
    }

    /**
     * 分销提现展示
     * @author tangp
     * @date 2018-12-04
     */
    public function show()
    {
        $user_id = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';
        $is_on= isset($_REQUEST['is_on']) ? intval($_REQUEST['is_on']) : 99;
        $area_id = !empty($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : '';
        $store_id = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : '';
        $order_sn = !empty($_REQUEST['order_sn']) ? $_REQUEST['order_sn'] : '';
        $start_time = !empty($_REQUEST['start_time']) ? $_REQUEST['start_time'] : '';
        $end_time = !empty($_REQUEST['end_time']) ? $_REQUEST['end_time'] : '';
        $this->assign('start_time',$start_time);
        $this->assign('end_time',$end_time);
        $start_times = !empty($_REQUEST['start_times']) ? $_REQUEST['start_times'] : '';
        $end_times = !empty($_REQUEST['end_times']) ? $_REQUEST['end_times'] : '';
        $this->assign('start_times',$start_times);
        $this->assign('end_times',$end_times);
        $fxuserMod = &m('fxuser');
        $fxOrderMod= &m('fxOrder');
        $storeMod = &m('store');
        $where = ' and 1=1 ';
        if ($is_on != 99){
            $where .= " and fo.is_on=".$is_on;
        }
        if (!empty($order_sn)){
            $where .= " and fo.order_sn like '%".$order_sn."%'";
        }
        if (!empty($store_id)) {
            $where .= " and fo.store_id like '%" . $store_id . "%'";
        }
        if (!empty($area_id)) {
            $where .= " and fo.store_cate =000000" . $area_id ;
        }
        if (!empty($start_time) && !empty($end_time)){
            $start_time = strtotime($start_time);
            $end_time   = strtotime($end_time);
            $where .= " and o.add_time >= $start_time and o.add_time <= $end_time";
        }
        if (!empty($start_times) && !empty($end_times)){
            $start_times = strtotime($start_times);
            $end_times   = strtotime($end_times);
            $where .= " and o.payment_time >= $start_times and o.payment_time <= $end_times";
        }
        $this->assign('order_sn',$order_sn);
        $storeCateMod = &m('storeCate');
        $areaArr = $storeCateMod->getAreaArr(1,$this->lang_id);
        $areaOption = make_option($areaArr, $area_id);
        $this->assign('areaOption', $areaOption);
        if ($area_id) {
            $this->assign('area_id', $area_id);
            if ($store_id) {
                $this->assign('store_id', $store_id);
            }
            //获取区域店铺
            $storeMod = &m('store');
            $storeArr = $storeMod->getStoreArr($area_id, 1);
            $storeOption = make_option($storeArr, $store_id);
            $this->assign('storeOption', $storeOption);
        }
        $sql1= "select * from bs_fx_user where user_id=".$user_id;
        $info = $fxuserMod->querySql($sql1);
        if ($info[0]['level'] == 3){
            $sql = "SELECT fs.discount,fo.order_sn,fo.is_on,o.add_time,o.payment_time,fo.store_id,fo.id,fu.lev1_prop,fu.lev2_prop,fu.lev3_prop,fo.fx_discount,o.order_id,fs.level,o.goods_amount,fo.pay_money,o.order_state,o.order_amount,fo.fx_commission_percent FROM bs_fx_order AS fo 
              LEFT JOIN bs_order AS o ON fo.order_id = o.order_id 
              LEFT JOIN bs_fx_rule AS fu ON fo.rule_id = fu.id 
              LEFT JOIN bs_fx_user AS fs ON fo.fx_user_id = fs.id
              WHERE fo.fx_user_id={$info[0]['id']}{$where} ORDER BY o.payment_time DESC";
            $data = $fxOrderMod->querySqlPageData($sql);
            $sql2 = "select * from bs_fx_user where user_id=".$user_id;
            $res = $fxuserMod->querySql($sql2);
            foreach($data['list'] as $k=>$v){
                // $data['list'][$k]['fxmoney']=number_format($v['pay_money']*($v['lev3_prop']-$v['discount'])/100,2);
                // $data['list'][$k]['fxmoney']=number_format($v['pay_money']*($v['fx_discount'])/100,2);
                $data['list'][$k]['fxmoney']=number_format($v['pay_money']*($v['fx_commission_percent'])/100,2);
                // $data['list'][$k]['prop'] = $v['lev3_prop']-$v['discount'];
                // $data['list'][$k]['prop'] = $v['fx_discount']; // by xt 2019.03.05
                $data['list'][$k]['prop'] = $v['fx_commission_percent']; // by xt 2019.03.05
                $data['list'][$k]['storeName'] = $storeMod->getNameById($v['store_id'],$this->lang_id);
                $data['list'][$k]['add_time'] = date("Y-m-d H:i",$v['add_time']);
                $data['list'][$k]['payment_time'] = date("Y-m-d H:i",$v['payment_time']);
                $data['list'][$k]['type_name']=$fxOrderMod->getRoomType($v['order_sn']);
            }
            //未入账佣金
            $sqls = "SELECT fu.lev3_prop,fs.discount,fo.fx_discount,fo.pay_money,fo.fx_commission_percent FROM bs_fx_order AS fo 
              LEFT JOIN bs_order AS o ON fo.order_id = o.order_id 
              LEFT JOIN bs_fx_rule AS fu ON fo.rule_id = fu.id 
              LEFT JOIN bs_fx_user AS fs ON fo.fx_user_id = fs.id
              WHERE fo.fx_user_id={$info[0]['id']} and is_on=0";
            $a = $fxOrderMod->querySql($sqls);
            foreach($a as $k=>$v){
                // $a[$k]['fxmoney']=number_format($v['pay_money']*($v['lev3_prop']-$v['discount'])/100,2);
                // $a[$k]['fxmoney']=number_format($v['pay_money']*($v['fx_discount'])/100,2);
                $a[$k]['fxmoney']=number_format($v['pay_money']*($v['fx_commission_percent'])/100,2);
            }
            $sumss = 0;
            foreach($a as $item){
                $sumss += $item['fxmoney'];
            }
            //搜索佣金
            $sqlss = "SELECT fs.discount,fu.lev3_prop,fo.fx_discount,fo.pay_money,fo.fx_commission_percent FROM bs_fx_order AS fo 
              LEFT JOIN bs_order AS o ON fo.order_id = o.order_id 
              LEFT JOIN bs_fx_rule AS fu ON fo.rule_id = fu.id 
              LEFT JOIN bs_fx_user AS fs ON fo.fx_user_id = fs.id
              WHERE fo.fx_user_id={$info[0]['id']}".$where;
            $as = $fxOrderMod->querySql($sqlss);
            foreach($as as $k=>$v){
                // $as[$k]['fxmoney']=number_format($v['pay_money']*($v['lev3_prop']-$v['discount'])/100,2);
                // $as[$k]['fxmoney']=number_format($v['pay_money']*($v['fx_discount'])/100,2);
                $as[$k]['fxmoney']=number_format($v['pay_money']*($v['fx_commission_percent'])/100,2);
            }
            $sums = 0;
            foreach($as as $item){
                $sums += $item['fxmoney'];
            }
            //查出已提现金额
            $sql4="SELECT SUM(apply_money) as fx_applymoney FROM bs_fx_outmoney_apply WHERE fx_user_id={$info[0]['id']} AND is_check=2" ;
            $fxOutmoneyApplyMod = &m('fxOutmoneyApply');
            $i = $fxOutmoneyApplyMod->querySql($sql4);
            $OrderStatus = array(
                '0' => '未入账',
                '1' => '已入账'
            );
            $this->assign('data',$data['list']);
            $this->assign('monery',$res[0]['monery']);
            $this->assign('sumss',$sumss);
            $this->assign('sums',$sums);
            $this->assign('page_html', $data['ph']);
            $this->assign('statusList',$OrderStatus);
            $this->assign('user_id',$user_id);
            $this->assign('is_on', $is_on);
            $this->assign('fx_applymoney',$i[0]['fx_applymoney']);
            $this->assign('real_name',$info[0]['real_name']);
            $this->display('distributor/show.html');
        }elseif ($info[0]['level'] == 1){
            $s = "select id from bs_fx_user where parent_id={$info[0]['id']} and level=2";
            $ss = $fxuserMod->querySql($s);
            foreach ($ss as $k => $v){
                $secondFxUserIdData[]=$v['id'];
            }
            $secondFxUserIds=implode(',',$secondFxUserIdData);
            $sql5 = "select id from bs_fx_user where parent_id in (".$secondFxUserIds.") and level=3";
            $res = $fxuserMod->querySql($sql5);
            foreach($res as $k=>$v){
                $thirdFxUserIdData[]=$v['id'];
            }
            $thirdFxUserIds=implode(',',$thirdFxUserIdData);
            $sql6 = "select fo.is_on,o.add_time,o.payment_time,fo.store_id,fo.id,fu.discount,fu.level,fr.lev1_prop,fr.lev2_prop,fr.lev3_prop,o.goods_amount,fo.pay_money,o.order_state,fo.add_time,fo.order_id,fo.order_sn from bs_fx_order as fo
                      left join bs_order as o on fo.order_id = o.order_id
                      left join bs_fx_user as fu on fu.id = fo.fx_user_id
                      left join bs_fx_rule as fr on fo.rule_id = fr.id where fo.fx_user_id in (".$thirdFxUserIds."){$where} ORDER BY o.payment_time DESC";
//            echo $sql6;die;
            $data = $fxOrderMod->querySqlPageData($sql6);
            $sql2 = "select * from bs_fx_user where user_id=".$user_id;
            $res = $fxuserMod->querySql($sql2);
            foreach($data['list'] as $k=>$v){
                $prop = $v['lev1_prop']/100;
                $data['list'][$k]['fxmoney']=number_format($v['pay_money'] *$prop,2);
                $data['list'][$k]['prop'] = $v['lev1_prop'];
                $data['list'][$k]['storeName'] = $storeMod->getNameById($v['store_id'],$this->lang_id);
                $data['list'][$k]['add_time'] = date("Y-m-d H:i",$v['add_time']);
                $data['list'][$k]['payment_time'] = date("Y-m-d H:i",$v['payment_time']);
                $data['list'][$k]['type_name']=$fxOrderMod->getRoomType($v['order_sn']);
            }

            //搜索佣金
//            $sqlss = "select fr.lev1_prop,fo.pay_money from bs_fx_order as fo
//                      left join bs_order as o on fo.order_id = o.order_id
//                      left join bs_fx_user as fu on fu.id = fo.fx_user_id
//                      left join bs_fx_rule as fr on fo.rule_id = fr.id where fo.fx_user_id in (".$thirdFxUserIds.")".$where;
//            $as = $fxOrderMod->querySql($sqlss);
//            foreach($as as $k=>$v){
//                $as[$k]['fxmoney']=number_format($v['pay_money']*$v['lev1_prop']/100,2);
//            }
//            $sums = 0;
//            foreach($as as $item){
//                $sums += $item['fxmoney'];
//            }
            $sqlss = "SELECT fo.pay_money,sum(ROUND((fo.pay_money * 0.1),2)) as money FROM bs_fx_order AS fo
                      LEFT JOIN bs_order AS o ON fo.order_id = o.order_id
                      LEFT JOIN bs_fx_user AS fu ON fo.fx_user_id = fu.id
                      LEFT JOIN bs_fx_rule AS fr ON fu.rule_id = fr.id
                      WHERE fo.fx_user_id in (".$thirdFxUserIds.")".$where;
//            echo $sqlss;die;
            $as = $fxOrderMod->querySql($sqlss);
//            dd($as);die;
            //未入账
            $sqlsss = "select fr.lev1_prop,fo.pay_money from bs_fx_order as fo
                      left join bs_order as o on fo.order_id = o.order_id
                      left join bs_fx_user as fu on fu.id = fo.fx_user_id
                      left join bs_fx_rule as fr on fo.rule_id = fr.id where fo.fx_user_id in (".$thirdFxUserIds.") AND fo.is_on=0";
            $ass = $fxOrderMod->querySql($sqlsss);
            foreach($ass as $k=>$v){
                $ass[$k]['fxmoney']=number_format($v['pay_money']*$v['lev1_prop']/100,2);
            }
            $sumss = 0;
            foreach($ass as $item){
                $sumss += $item['fxmoney'];
            }
            $sql4="SELECT SUM(apply_money) as fx_applymoney FROM bs_fx_outmoney_apply WHERE fx_user_id={$info[0]['id']} AND is_check=2";
            $fxOutmoneyApplyMod = &m('fxOutmoneyApply');
            $i = $fxOutmoneyApplyMod->querySql($sql4);
            $OrderStatus = array(
                '0' => '未入账',
                '1'=>'已入账'
            );
            $this->assign('statusList',$OrderStatus);
            $this->assign('data',$data['list']);
            $this->assign('monery',$res[0]['monery']);
            $this->assign('page_html', $data['ph']);
            $this->assign('user_id',$user_id);
            $this->assign('is_on', $is_on);
            $this->assign('fx_applymoney',$i[0]['fx_applymoney']);
            $this->assign('real_name',$info[0]['real_name']);
            $this->assign('sums',$as[0]['money']);
            $this->assign('sumss',$sumss);
            $this->display('distributor/show.html');
        }elseif ($info[0]['level'] == 2){
            $sss = "select id from bs_fx_user where parent_id={$info[0]['id']} and level=3";
            $result =$fxuserMod->querySql($sss);
            foreach ($result as $v){
                $ids[] = $v['id'];
            }
            $two_ids    = implode(',',$ids);
            $sql = "select fo.is_on,o.add_time,o.payment_time,fo.store_id,fo.id,fu.discount,fu.level,fr.lev1_prop,fr.lev2_prop,fr.lev3_prop,o.goods_amount,fo.pay_money,o.order_state,fo.order_id,fo.order_sn,o.order_amount from bs_fx_order as fo
                  left join bs_order as o on fo.order_id = o.order_id
                  left join bs_fx_user as fu on fu.id = fo.fx_user_id
                  left join bs_fx_rule as fr on fo.rule_id = fr.id where fo.fx_user_id in ({$two_ids}){$where} ORDER BY o.payment_time DESC";
            $data = $fxOrderMod->querySqlPageData($sql);
            $sql2 = "select * from bs_fx_user where user_id =".$user_id;
            $res = $fxuserMod->querySql($sql2);
            foreach ($data['list'] as $k => $v){
                $prop = $v['lev2_prop']/100;
                $data['list'][$k]['fxmoney'] = number_format($v['pay_money'] * $prop,2);
                $data['list'][$k]['prop'] = $v['lev2_prop'];
                $data['list'][$k]['storeName'] = $storeMod->getNameById($v['store_id'],$this->lang_id);
                $data['list'][$k]['add_time'] = date("Y-m-d H:i",$v['add_time']);
                $data['list'][$k]['payment_time'] = date("Y-m-d H:i",$v['payment_time']);
                $data['list'][$k]['type_name']=$fxOrderMod->getRoomType($v['order_sn']);
            }
            //搜索佣金
            $sqlss = "select fr.lev2_prop,fo.pay_money from bs_fx_order as fo
                  left join bs_order as o on fo.order_id = o.order_id
                  left join bs_fx_user as fu on fu.id = fo.fx_user_id
                  left join bs_fx_rule as fr on fo.rule_id = fr.id where fo.fx_user_id in ({$two_ids})".$where;
            $as = $fxOrderMod->querySql($sqlss);
            foreach($as as $k=>$v){
                $as[$k]['fxmoney']=number_format($v['pay_money']*$v['lev2_prop']/100,2);
            }
            $sums = 0;
            foreach($as as $item){
                $sums +=  $item['fxmoney'];
            }
            //未入账佣金
            $sqlsss = "select fr.lev2_prop,fo.pay_money from bs_fx_order as fo
                  left join bs_order as o on fo.order_id = o.order_id
                  left join bs_fx_user as fu on fu.id = fo.fx_user_id
                  left join bs_fx_rule as fr on fo.rule_id = fr.id where fo.fx_user_id in ({$two_ids}) AND fo.is_on=0";
            $ass = $fxOrderMod->querySql($sqlsss);
            foreach($ass as $k=>$v){
                $ass[$k]['fxmoney']=number_format($v['pay_money']*$v['lev2_prop']/100,2);
            }
            $sumss = 0;
            foreach($ass as $item){
                $sumss +=  $item['fxmoney'];
            }
            $OrderStatus = array(
                '0' => '未入账',
                '1'=>'已入账'
            );
            $sql4="SELECT SUM(apply_money) as fx_applymoney FROM bs_fx_outmoney_apply WHERE fx_user_id={$info[0]['id']} AND is_check = 2";
            $fxOutmoneyApplyMod = &m('fxOutmoneyApply');
            $i = $fxOutmoneyApplyMod->querySql($sql4);
            $this->assign('statusList',$OrderStatus);
            $this->assign('data',$data['list']);
            $this->assign('monery',$res[0]['monery']);
            $this->assign('page_html', $data['ph']);
            $this->assign('real_name',$info[0]['real_name']);
            $this->assign('is_on', $is_on);
            $this->assign('user_id',$user_id);
            $this->assign('sumss',$sumss);
            $this->assign('sums',sprintf("%.2f", $sums));
            $this->assign('fx_applymoney',$i[0]['fx_applymoney']);
            $this->display('distributor/show.html');
        }
    }
    public function export()
    {
        $user_id = $_REQUEST['user_id'];
        $is_on = isset($_REQUEST['is_on']) ? $_REQUEST['is_on'] : 99;
        $area_id = !empty($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : '';
        $store_id = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : '';
        $order_sn = !empty($_REQUEST['order_sn']) ? $_REQUEST['order_sn'] : '';
        $start_time = !empty($_REQUEST['start_time']) ? $_REQUEST['start_time'] : '';
        $end_time = !empty($_REQUEST['end_time']) ? $_REQUEST['end_time'] : '';
        $start_times = !empty($_REQUEST['start_times']) ? $_REQUEST['start_times'] : '';
        $end_times = !empty($_REQUEST['end_times']) ? $_REQUEST['end_times'] : '';
        $where = ' and 1=1 and sl.lang_id = '.$this->lang_id;
        $limit = 10000;
        if ($is_on != 99){
            $where .= " and fo.is_on=".$is_on;
        }
        if (!empty($order_sn)){
            $where .= " and fo.order_sn like '%".$order_sn."%'";
        }
        if (!empty($store_id)) {
            $where .= " and fo.store_id like '%" . $store_id . "%'";
        }
        if (!empty($area_id)) {
            $where .= " and fo.store_cate =000000" . $area_id ;
        }
        if (!empty($start_time) && !empty($end_time)){
            $start_time = strtotime($start_time);
            $end_time   = strtotime($end_time);
            $where .= " and o.add_time >= $start_time and o.add_time <= $end_time";
        }
        if (!empty($start_times) && !empty($end_times)){
            $start_times = strtotime($start_times);
            $end_times   = strtotime($end_times);
            $where .= " and o.payment_time >= $start_times and o.payment_time <= $end_times";
        }
        $fxuserMod = &m('fxuser');
        $fxOrderMod= &m('fxOrder');
        $sql1= "select * from bs_fx_user where user_id=".$user_id;
        $info = $fxuserMod->querySql($sql1);
        $exportData = array();
        $export = array();
        if ($info[0]['level'] == 3){
            $countSql = "SELECT COUNT(fo.order_id) as count FROM bs_fx_order AS fo 
                  LEFT JOIN bs_order AS o ON fo.order_id = o.order_id 
                  LEFT JOIN bs_fx_rule AS fu ON fo.rule_id = fu.id 
                  LEFT JOIN bs_fx_user AS fs ON fo.fx_user_id = fs.id
                  left join bs_store as s on fo.store_id = s.id 
                  left join bs_store_lang as sl on s.id = sl.store_id 
                  WHERE fo.fx_user_id={$info[0]['id']}".$where;
            $order_count = $fxOrderMod->querySql($countSql);
            $size = ceil($order_count[0]['count']/$limit);
            for ($i = 1;$i <= $size;$i++){
                $start = ($i - 1) * $limit;
                $sql = "SELECT fs.discount,fo.order_sn,fo.is_on,fo.fx_discount,sl.store_name,o.add_time,o.payment_time,fo.store_id,fo.id,fu.lev1_prop,fu.lev2_prop,fu.lev3_prop,fs.discount,o.order_id,o.order_state,fs.level,o.goods_amount,fo.pay_money,o.order_state,o.order_amount,fo.fx_commission_percent FROM bs_fx_order AS fo 
                  LEFT JOIN bs_order AS o ON fo.order_id = o.order_id 
                  LEFT JOIN bs_fx_rule AS fu ON fo.rule_id = fu.id 
                  LEFT JOIN bs_fx_user AS fs ON fo.fx_user_id = fs.id
                  left join bs_store as s on fo.store_id = s.id 
                  left join bs_store_lang as sl on s.id = sl.store_id 
                  WHERE fo.fx_user_id={$info[0]['id']}".$where." order by o.payment_time DESC limit {$start},{$limit}";
                $data = $fxOrderMod->querySql($sql);
                foreach($data as $k=>$v){
                    // $data[$k]['fxmoney']=number_format($v['pay_money']*($v['lev3_prop']-$v['discount'])/100,2);
                    // $data[$k]['fxmoney']=number_format($v['pay_money']*($v['fx_discount'])/100,2);
                    $data[$k]['fxmoney']=number_format($v['pay_money']*($v['fx_commission_percent'])/100,2);
                    $data[$k]['order_state_name'] = $fxOrderMod->state[$v['is_on']];
//                    $data[$k]['storeName'] = $storeMod->getNameById($v['store_id'],$this->lang_id);
                    $data[$k]['add_time'] = date("Y-m-d H:i",$v['add_time']);
//                    $data[$k]['type_name']=$fxOrderMod->getRoomType($v['order_sn']);
                    if ($v['payment_time'] == 0){
                        $data[$k]['payment_time'] = '---';
                    }else{
                        $data[$k]['payment_time'] = date("Y-m-d H:i",$v['payment_time']);
                    }
                    // if (($v['lev3_prop'] - $v['fx_discount']) < 0){
                    //     $data[$k]['prop'] = 0;
                    // }else{
                    //     $data[$k]['prop'] = $v['lev3_prop'] - $v['fx_discount'];
                    // }
                    // by xt 2019.03.05
                    if ($v['fx_commission_percent'] < 0){
                        $data[$k]['prop'] = 0;
                    }else{
                        $data[$k]['prop'] = $v['fx_commission_percent'];
                    }
                    $exportData[$k][] = $k+1;
                    $exportData[$k][] = $v['order_sn']."\t";
//                    $exportData[$k][] = $data[$k]['type_name'];
                    $exportData[$k][] = $v['pay_money'];
                    $exportData[$k][] = $data[$k]['fxmoney'];
                    $exportData[$k][] = $data[$k]['prop'];
                    $exportData[$k][] = $data[$k]['store_name'];
                    $exportData[$k][] = $data[$k]['order_state_name'];
                    $exportData[$k][] = $data[$k]['add_time'];
                    $exportData[$k][] = $data[$k]['payment_time'];
                }

            }
//            dd($exportData);
//            die;
        }elseif ($info[0]['level'] == 1){
            $s = "select id from bs_fx_user where parent_id={$info[0]['id']} and level=2";
            $ss = $fxuserMod->querySql($s);
            foreach ($ss as $k => $v){
                $secondFxUserIdData[]=$v['id'];
            }
            $secondFxUserIds=implode(',',$secondFxUserIdData);
            $sql5 = "select id from bs_fx_user where parent_id in (".$secondFxUserIds.") and level=3";
            $res = $fxuserMod->querySql($sql5);
            foreach($res as $k=>$v){
                $thirdFxUserIdData[]=$v['id'];
            }
            $thirdFxUserIds=implode(',',$thirdFxUserIdData);
            $countSql =  "select COUNT(fo.order_id) as count from bs_fx_order as fo
                      left join bs_order as o on fo.order_id = o.order_id
                      left join bs_fx_user as fu on fu.id = fo.fx_user_id
                      left join bs_fx_rule as fr on fo.rule_id = fr.id 
                      left join bs_store as s on fo.store_id = s.id 
                      left join bs_store_lang as sl on s.id = sl.store_id where fo.fx_user_id in (".$thirdFxUserIds.")".$where;
            $order_count = $fxOrderMod->querySql($countSql);
            $size = ceil($order_count[0]['count']/$limit);
            for ($i=1;$i<=$size;$i++){
                $start = ($i - 1) * $limit;
                $sql6 = "select fo.order_sn,fo.is_on,sl.store_name,o.add_time,o.payment_time,fo.store_id,fo.id,fu.discount,fu.level,fr.lev1_prop,fr.lev2_prop,fr.lev3_prop,o.goods_amount,fo.pay_money,o.order_state,fo.order_id,sl.store_name from bs_fx_order as fo
                      left join bs_order as o on fo.order_id = o.order_id
                      left join bs_fx_user as fu on fu.id = fo.fx_user_id
                      left join bs_fx_rule as fr on fo.rule_id = fr.id 
                      left join bs_store as s on fo.store_id = s.id 
                      left join bs_store_lang as sl on s.id = sl.store_id 
                      where fo.fx_user_id in (".$thirdFxUserIds.")".$where." order by o.payment_time DESC limit {$start},{$limit}";
                $data = $fxOrderMod->querySql($sql6);
                foreach($data as $k=>$v){
                    $prop = $v['lev1_prop']/100;
                    $data[$k]['fxmoney']=number_format($v['pay_money'] *$prop,2);
                    $data[$k]['order_state_name'] = $fxOrderMod->state[$v['is_on']];
//                    $data[$k]['storeName'] = $storeMod->getNameById($v['store_id'],$this->lang_id);
                    $data[$k]['add_time'] = date("Y-m-d H:i",$v['add_time']);
//                    $data[$k]['type_name']=$fxOrderMod->getRoomType($v['order_sn']);
                    if ($v['payment_time'] == 0){
                        $data[$k]['payment_time'] = '---';
                    }else{
                        $data[$k]['payment_time'] = date("Y-m-d H:i",$v['payment_time']);
                    }
                    $exportData[$k][] = $k+1;
                    $exportData[$k][] = $v['order_sn']."\t";
//                    $exportData[$k][] = $data[$k]['type_name'];
                    $exportData[$k][] = $v['pay_money'];
                    $exportData[$k][] = $data[$k]['fxmoney'];
                    $exportData[$k][] = $v['lev1_prop'];
                    $exportData[$k][] = $data[$k]['store_name'];
                    $exportData[$k][] = $data[$k]['order_state_name'];
                    $exportData[$k][] = $data[$k]['add_time'];
                    $exportData[$k][] = $data[$k]['payment_time'];
                }
//                $export = array_merge($export, $exportData);
            }
        }elseif ($info[0]['level'] == 2) {
            $sss = "select id from bs_fx_user where parent_id={$info[0]['id']} and level=3";
            $result = $fxuserMod->querySql($sss);
            foreach ($result as $v) {
                $ids[] = $v['id'];
            }
            $two_ids = implode(',', $ids);
            $countSql = "select COUNT(fo.order_id) as count  from bs_fx_order as fo
                  left join bs_order as o on fo.order_id = o.order_id
                  left join bs_fx_user as fu on fu.id = fo.fx_user_id
                  left join bs_fx_rule as fr on fo.rule_id = fr.id 
                  left join bs_store as s on fo.store_id = s.id 
                  left join bs_store_lang as sl on s.id = sl.store_id 
                  where fo.fx_user_id in ({$two_ids})".$where;
            $order_count = $fxOrderMod->querySql($countSql);
            $size = ceil($order_count[0]['count']/$limit);
            for ($i=1;$i <= $size;$i++){
                $start = ($i - 1) * $limit;
                $sql = "select fo.order_sn,fo.is_on,sl.store_name,o.add_time,o.payment_time,fo.store_id,fo.id,fu.discount,fu.level,fr.lev1_prop,fr.lev2_prop,fr.lev3_prop,o.goods_amount,fo.pay_money,o.order_state,fo.order_id,o.order_amount from bs_fx_order as fo
                  left join bs_order as o on fo.order_id = o.order_id
                  left join bs_fx_user as fu on fu.id = fo.fx_user_id
                  left join bs_fx_rule as fr on fo.rule_id = fr.id 
                  left join bs_store as s on fo.store_id = s.id 
                  left join bs_store_lang as sl on s.id = sl.store_id 
                  where fo.fx_user_id in ({$two_ids})".$where." order by o.payment_time DESC limit {$start},{$limit}";

                $data = $fxOrderMod->querySql($sql);
                foreach ($data as $k => $v) {
                    $prop = $v['lev2_prop'] / 100;
                    $data[$k]['fxmoney'] = number_format($v['pay_money'] * $prop, 2);
                    $data[$k]['order_state_name'] = $fxOrderMod->state[$v['is_on']];
//                    $data[$k]['storeName'] = $storeMod->getNameById($v['store_id'], $this->lang_id);
                    $data[$k]['add_time'] = date("Y-m-d H:i",$v['add_time']);
//                    $data[$k]['type_name']=$fxOrderMod->getRoomType($v['order_sn']);
                    if ($v['payment_time'] == 0){
                        $data[$k]['payment_time'] = '---';
                    }else{
                        $data[$k]['payment_time'] = date("Y-m-d H:i",$v['payment_time']);
                    }

                    $exportData[$k][] = $k+1;
                    $exportData[$k][] = $v['order_sn']."\t";
//                    $exportData[$k][] = $data[$k]['type_name'];
                    $exportData[$k][] = $v['pay_money'];
                    $exportData[$k][] = $data[$k]['fxmoney'];
                    $exportData[$k][] = $v['lev2_prop'];
                    $exportData[$k][] = $data[$k]['store_name'];
                    $exportData[$k][] = $data[$k]['order_state_name'];
                    $exportData[$k][] = $data[$k]['add_time'];
                    $exportData[$k][] = $data[$k]['payment_time'];
                }

            }
        }
        $fileheader = array('序号','订单号','实付金额','本单佣金','分销比例','所属店铺','收益状态','下单时间','付款时间');
        include_once ROOT_PATH . '/includes/libraries/csvExport.lib.php';
        $csvExport = new csvExport();
        $csvExport->export_fx_order( $fileheader, $order_count[0]['count'], $info[0]['real_name'],$limit, $exportData);
    }
    /**
     * 导出人员
     * @author tangp
     * @date 2018-12-04
     */
    public function exportPerson()
    {
        $fxUserAccountMod = &m('fxUserAccount');
        $sql = "select * from bs_fx_user where level=1";
        $res = $this->fxuserMod->querySql($sql);
        foreach ($res as $key => $val){
            $data_id[] = $val['id'];
            $sql2 = "select * from bs_fx_user where level=2 and parent_id=".$val['id'];
            $rr = $this->fxuserMod->querySql($sql2);
            $res[$key]['childs']=$rr;
            foreach ($res[$key]['childs'] as $kk => $vv){
                $data_id[] = $vv['id'];
                $sql3 = "select * from bs_fx_user where level=3 and parent_id=".$vv['id'];
                $rrr = $this->fxuserMod->querySql($sql3);
                $res[$key]['childs'][$kk]['child']=$rrr;
                foreach ($res[$key]['childs'][$kk]['child'] as $kkk => $vvv){
                    $data_id[] = $vvv['id'];
                }
            }
        }
        foreach ($data_id as $value){
            $info = $this->fxuserMod->getOne(array('cond'=>"`id`='{$value}'",'fields'=>'id,real_name,level,fx_code,phone,discount,bank_name,bank_account'));
            $result[] = $info;
        }
//        echo '<pre>';print_r($result);die;
        ob_end_clean();
        ob_start();
        header("Content-type:application/octet-stream");
        header("Accept-Ranges:bytes");
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:attachment;filename="."分销人员".".xls");
        header("Pragma: no-cache");
        header("Expires: 0");
//导出xls 开始
        $title = array('序号','会员名称','分销等级','分销码','手机号','优惠比例','开户银行','银行账号');
        if (!empty($title)){
            foreach ($title as $k => $v) {
                $title[$k]=iconv("UTF-8", "GB2312",$v);
            }
            $title= implode("\t", $title);
            echo "$title\n";
        }
        if (!empty($result)){
            foreach($result as $key=>$val){
                foreach ($val as $ck => $cv) {
                    $result[$key][$ck]=iconv("UTF-8", "GB2312", $cv);
                }
                $result[$key]=implode("\t", $result[$key]);
            }
            echo implode("\n",$result);
        }
    }
}