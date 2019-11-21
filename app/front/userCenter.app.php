<?php

/**
 * 用户中心
 * @author lvji
 *
 */
//include_once 'sms.app.php';
//include_once 'mail.app.php';
class userCenterApp extends BaseFrontApp {

    private $footPrintMod;
    private $colleCtionMod;
    private $userArticleMod;
    private $orderMod;
    private $orderGoodsMod;
    private $commentMod;
    private $fxUserMod;
    private $goodsCommentMod;
    private $fxRuleMod;
    private $fxUserTreeMod;
    private $fxRevenueLogMod;
    private $userMod;
    private $storeMod;
    private $fxTreeMod;
    private $cityMod;
    private $fxuserMoneyMod;
    private $countryMod;
    private $giftGoodMod;
    private $storeCateMod;
    private $pointMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->userMod = &m('user');
        $this->cityMod = &m('city');
        $this->storeCateMod = &m('storeCate');
        $this->fxuserMoneyMod = &m('fxuserMoney');
        $this->fxTreeMod = &m('fxuserTree');
        $this->storeMod = &m('store');
        $this->footPrintMod = &m('footprint');
        $this->colleCtionMod = &m('colleCtion');
        $this->userArticleMod = &m('userArticle');
        $this->orderMod = &m('order');
        $this->orderGoodsMod = &m('orderGoods');
        $this->commentMod = &m('goodsComment');
        $this->fxUserMod = &m('fxuser');
        $this->fxRuleMod = &m('fxrule');
        $this->fxRevenueLogMod = &m('fxRevenueLog');
        $this->assign('storeid', $this->storeid);
        $this->goodsCommentMod = &m('goodsComment');
        $this->storeGoodsMod = &m('goods');
        $this->fxUserTreeMod = &m('fxuserTree');
        $this->countryMod = &m('country');
        $this->giftGoodMod = &m('giftGood');
        $this->pointMod = &m('point');
        $this->load($this->shorthand, 'user_login/user_login');
        $this->assign('langdata', $this->langData);

        if (!isset($_SESSION['userId'])) {
            $this->display('public/login.html');
            exit();
        }
        //判断是否新增区域店铺 新增分销人员信息
        $this->addFxStore();
        //区域店铺余额判断
//         $this->doFxStoreMoney();
    }

    /*
     * 判断是否新增区域店铺 新增分销人员信息
     * @author lee
     * @date 2017-11-22 16:39:43
     */

    public function addFxStore() {
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $storeMod = &m('store');
        $store_info = $this->storeMod->getOne(array("cond" => "id=" . $storeid));
        $fx_info = $this->fxUserMod->getOne(array("cond" => "user_id=" . $this->userId . " and store_cate=" . $store_info['store_cate_id'] . " and freeze =1 and is_check =2"));
        $sql = 'select id as store_id  from  bs_store  where   store_cate_id =' . $store_info['store_cate_id'];
        $has_id = $this->fxuserMoneyMod->getData(array("cond" => "user_id=" . $this->userId . " and store_cate=" . $store_info['store_cate_id'], "fields" => "store_id"));
        $ids = $this->arrayColumn($has_id, 'store_id');
        $res = $storeMod->querySql($sql);
//        $user_info = array(
//            'is_fx' => 1
//        );
        if (!empty($res) && !empty($fx_info)) {
            foreach ($res as $key => $val) {
                if (!in_array($val['store_id'], $ids)) {
                    $arr_store['user_id'] = $this->userId;
                    $arr_store['store_cate'] = $store_info['store_cate_id'];
                    $arr_store['money'] = 0.00;
                    $arr_store['store_id'] = $val['store_id'];
                    $r = $this->fxuserMoneyMod->doInsert($arr_store);
//                    if ($r) {
//                        $this->userMod->doEdit($this->userId, $user_info);
//                    }
                }
            }
        }
    }

//    /*
//     * 区域店铺余额判断
//     * @author lee
//     * @date 2017-11-23 15:27:24
//     */
//    public function doFxStoreMoney(){
//        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
//        $store_info = $this->storeMod->getOne(array("cond" => "id=" . $storeid));
//        //分销人员信息
//        $fx_info = $this->fxUserMod->getOne(array("cond" => "user_id=" . $this->userId . " and store_cate=" . $store_info['store_cate_id']));
//        $fx_tree=$fx_tree=$this->fxTreeMod->getOne(array("cond"=>"user_id=".$this->userId));
//        if($fx_info){
//            $siteMod=&m('fxSite');
//            $siteInfo=$siteMod->getOne(array("cond"=>"store_id=".$storeid));
//            //开启订单进账时间 1.有时间
//            if($siteInfo['is_order_day']==1){
//                //判断用户等级
//                if($fx_tree['fx_level']==3){
//
//                }elseif($fx_tree['fx_level']==2){
//
//                }elseif($fx_tree['fx_level']==1){
//
//                }
//                $time=$siteInfo['order_day']*3600*24;
//                $where=" and f.flag=1";
//                $sql="select o.*,f.id as log_id from ".DB_PREFIX."order as o LEFT JOIN  ".DB_PREFIX."fx_revenue_log as f on o.order_id=f.order_id";
//            }
//        }
//
//    }

    /*
     * 个人中心
     * @author lee
     * @date 2017-8-11 10:22:12
     */

    public function myCenter() {
        //语言包
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        //当前站点信息
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $store_info = $this->storeMod->getOne(array("cond" => "id=" . $storeid));
        //分销人员信息
        $fx_info = $this->fxUserMod->getOne(array("cond" => "user_id=" . $this->userId . " and store_cate=" . $store_info['store_cate_id']));
        $tree_info = $this->fxTreeMod->getOne(array("cond" => "user_id=" . $this->userId));
//        $r=$this->shareMyfxcode($fx_info['fx_code']);
        $value = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER["PHP_SELF"] . "?app=user&act=doFxuser&fx_code=" . $fx_info['fx_code']; //二维码内容
        $this->assign('fx_code', $value);
        $this->assign('tree_info', $tree_info);
        $this->assign('fx_info', $fx_info);
        $this->display("userCenter/myaccount.html");
    }

    /*
     * 设置分销优惠
     * @author lee
     * @date 2017-11-27 10:34:00
     */

    public function setDiscount() {
        $this->load($this->shorthand, 'userCenter/userCenter');
        $user_id = $this->userId;
        $mod = &m('fxDiscountLog');
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $store_info = $this->storeMod->getOne(array("cond" => "id=" . $storeid));

        $fx_info = $this->fxUserMod->getOne(array("cond" => "user_id=" . $user_id . " and store_cate=" . $store_info['store_cate_id']));
        $fx_tree = $this->fxTreeMod->getOne(array("cond" => "user_id=" . $user_id));

        $pid_tree = $this->fxTreeMod->getOne(array("cond" => "id=" . $fx_tree['pidpid']));
        $where = " where r.user_id=" . $pid_tree['user_id'];
        $sql = "select u.*,ru.lev3_prop,ru.id as r_id from " . DB_PREFIX . "fx_user as u left join " . DB_PREFIX . "fx_user_rule as r on u.user_id=r.user_id
                left join " . DB_PREFIX . "fx_rule as ru on ru.id=.r.rule_id " . $where;
        $res = $this->fxTreeMod->querySql($sql);
        $has_log = $mod->getOne(array("cond" => "user_id=" . $user_id . " and store_cate=" . $store_info['store_cate_id'] . " and status=1"));
        $this->assign('langdata', $this->langData);
        $this->assign('has_log', $has_log);
        $this->assign('fx_info', $fx_info);
        $this->assign('fx_rule', $res[0]);
        $this->display("fx/set-discount.html");
    }

    /*
     * 三级分销推荐优惠处理
     * @author lee
     * @2017-11-27 16:48:08
     */

    public function doFxdiscount() {
        $this->load($this->shorthand, 'userCenter/userCenter');
        $a = $this->langData;
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $store_info = $this->storeMod->getOne(array("cond" => "id=" . $storeid));
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : '';  //所选的站点id
        $user_id = ($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';
        $fx_discount = ($_REQUEST['fx_discount']) ? trim($_REQUEST['fx_discount']) : '';
        $old_discount = ($_REQUEST['old_discount']) ? trim($_REQUEST['old_discount']) : '';
        $rule_id = ($_REQUEST['rule_prop']) ? trim($_REQUEST['rule_prop']) : '';
        $rule_info = $this->fxRuleMod->getOne(array("cond" => "id=" . $rule_id));
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $mod = &m('fxDiscountLog');
        if ($rule_info['lev3_prop'] < $fx_discount) {
            $this->setData(array(), $status = '0', $message = "优惠比例不能大于您的分润比例");
        }
        if ($fx_discount === false) {
            $this->setData(array(), $status = '0', $message = "请填写优惠比例");
        }
        $arr = array(
            'user_id' => $user_id,
            'store_cate' => $store_info['store_cate_id'],
            'fx_discount' => $fx_discount,
            'old_discount' => $old_discount,
            'add_time' => time(),
            'lev3_prop' => $rule_info['lev3_prop'],
        );
        $r = $mod->doInsert($arr);
        if ($r) {
            $this->setData(array("url" => "?app=userCenter&act=myCenter&storeid={$storeid}&lang={$lang}&auxiliary={$auxiliary}"), $status = '1', $a['saveAdd_Success']);
        } else {
            $this->setData(array(), $status = '0', $message = $a['saveAdd_fail']);
        }
    }

    /*
     * 申请成为分销
     * @author lee
     * @date 2017-11-21 09:55:35
     */

    public function beFxuser() {
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $this->display("userCenter/myfx-info.html");
    }

    /*
     * 重新申请成为分销
     * @author lee
     * @date 2017-11-21 09:55:35
     */

    public function reFxuser() {
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $store_info = $this->storeMod->getOne(array("cond" => "id=" . $storeid));
        $res = $this->fxUserMod->doDrops("user_id=" . $this->userId . " and store_cate=" . $store_info['store_cate_id']);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $this->display("userCenter/myfx-info.html");
    }

    /*
     * 成为分销
     * @author lee
     * @date 2017-11-21 09:55:42
     */

    public function doFxuser() {
        $this->load($this->shorthand, 'userCenter/userCenter');
        $a = $this->langData;
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : '';  //所选的站点id
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $real_name = ($_REQUEST['real_name']) ? trim($_REQUEST['real_name']) : '';
        $telephone = ($_REQUEST['telephone']) ? trim($_REQUEST['telephone']) : '';
        $tj_code = ($_REQUEST['tj_code']) ? trim($_REQUEST['tj_code']) : '';
        $bank_name = ($_REQUEST['bank_name']) ? trim($_REQUEST['bank_name']) : '';
        $bank_account = ($_REQUEST['bank_account']) ? trim($_REQUEST['bank_account']) : '';
        $is_fx = ($_REQUEST['is_fx']) ? $_REQUEST['is_fx'] : '';
        $has_info = $this->fxUserMod->getOne(array("cond" => "user_id=" . $this->userId));
        $has_phone = $this->fxUserMod->getOne(array("cond" => "telephone=" . $telephone));
        if ($has_info) {
            $this->setData(array(), $status = '0', $a['fx_has_name']);
        }
        if ($has_phone) {
            $this->setData(array(), $status = '0', $a['fx_has_name']);
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
        if (empty($tj_code) && ($is_fx == 2)) {
            $this->setData(array(), $status = '0', $a['fx_tj_code']);
        } elseif ($tj_code) {
            $fx_info = $this->fxUserMod->getOne(array("cond" => "fx_code='" . $tj_code . "'"));
            $fx_tree = $this->fxTreeMod->getOne(array("cond" => "user_id=" . $fx_info['user_id']));
            if (empty($fx_info)) {
                $this->setData(array(), $status = '0', $a['fx_tj']);
            } elseif ($fx_tree['fx_level'] == 3) {
                $this->setData(array(), $status = '0', $a['fx_no_tree']);
            }
        }

        $user_info = $this->userMod->getOne(array("cond" => "id=" . $this->userId));
        $store_info = $this->storeMod->getOne(array("cond" => "id=" . $storeid));
        $store_cate = $this->storeCateMod->getOne(array("cond" => "id=" . $store_info['store_cate_id']));
        $fx_code = $this->make_fxcode();
        $fx_img = $this->shareMyfxcode($fx_code);
        $data = array(
            'user_id' => $this->userId,
            'real_name' => $real_name,
            'email' => $user_info['email'],
            'telephone' => $telephone,
            'bank_name' => $bank_name,
            'bank_account' => $bank_account,
            'store_id' => $this->storeid,
            'store_cate' => $store_info['store_cate_id'],
            'add_time' => time(),
            'fx_code' => $fx_code,
            'source' => 2,
            'is_check' => 1,
            'fx_img' => $fx_img,
            'fx_discount' => $store_cate['fx_discount']
        );
        if ($fx_info) {
            $data['tj_code'] = $tj_code;
            $data['is_check'] = 2;
        }
        $res = $this->fxUserMod->doInsert($data);
        if ($res) {
            if (empty($fx_info)) {
                $r = true;
            } else {
                $level = $fx_tree['fx_level'] + 1;
                $tree_info = array(
                    'user_id' => $this->userId,
                    'fx_level' => $level,
                    'pid' => $fx_tree['id'],
                    'pidpid' => $fx_tree['pid']
                );
                $r = $this->fxTreeMod->doInsert($tree_info);
                $this->doFxmoney($store_info['store_cate_id']);
            }
            if ($r) {
                //推荐码进来直接user is_fx 字段
                if ($fx_info) {
                    $this->userMod->doEdit($this->userId, array("is_fx" => 1));
                }

                $this->setData(array("url" => "?app=userCenter&act=myCenter&storeid={$storeid}&lang={$lang}&auxiliary={$auxiliary}"), $status = '1', $a['saveAdd_Success']);
            } else {
                $this->setData(array(), $status = '0', $message = $a['saveAdd_fail']);
            }
        } else {
            $this->setData(array(), $status = '0', $message = $a['saveAdd_fail']);
        }
    }

    /*
     * 新增分销用户关系树，余额
     * @author lee
     * @date 2017-11-21 20:29:31W
     */

    public function doFxmoney($cate_id) {
        $storeMod = &m('store');
        $sql = 'select id as store_id  from  ' . DB_PREFIX . 'store  where   store_cate_id =' . $cate_id;
        $res = $storeMod->querySql($sql);
        if (!empty($res)) {
            foreach ($res as $key => $val) {
                $arr_store[$key]['user_id'] = $this->userId;
                $arr_store[$key]['store_cate'] = $cate_id;
                $arr_store[$key]['money'] = 0.00;
                $arr_store[$key]['store_id'] = $val['store_id'];
                // $this->fxuserMoneyMod->doInsert($arr_store);
            }
            foreach ($arr_store as $k => $v) {
                $res = $this->fxuserMoneyMod->doInsert($v);
            }
        }
    }

    /*
     * 提现
     * @author lee
     * @date 2017-11-22 14:52:13
     */

    public function fx_cash() {
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $info = $this->fxuserMoneyMod->getOne(array("cond" => "user_id=" . $this->userId . " and store_id=" . $storeid));
        $user_info = $this->fxUserMod->getOne(array("cond" => "user_id=" . $this->userId));
        $this->assign("info", $info);
        $this->assign("user_info", $user_info);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $this->display("fx/myfx-cash.html");
    }

    /*
     * 提现申请处理
     * @author lee
     * @date 2017-11-23 09:40:43
     */

    public function do_cash() {
        $this->load($this->shorthand, 'userCenter/userCenter');
        $a = $this->langData;
        $siteMod = &m('fxSite');
        $fxApplyMod = &m('fxOutmoneyApply');
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : '';  //所选的站点id
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $store_id = ($_POST['store_cash_id']) ? $_POST['store_cash_id'] : '';
        $store_cate = ($_POST['store_cate']) ? $_POST['store_cate'] : '';
        $fx_money = ($_POST['fx_money']) ? $_POST['fx_money'] : '';
        $siteInfo = $siteMod->getOne(array("cond" => "store_id=" . $store_id));
        $info = $this->fxuserMoneyMod->getOne(array("cond" => "user_id=" . $this->userId . " and store_id=" . $storeid));
        if (empty($fx_money)) {
            $this->setData(array(), $status = '0', $a['fx_no_money']);
        }
        if ($fx_money > $info['money']) {
            $this->setData(array(), $status = '0', $a['fx_en_money']);
        }
        //判断提现规则是否存在
        if (empty($siteInfo)) {
            $this->setData(array(), $status = '0', $a['fx_no_site']);
        }
        if (!is_numeric($fx_money)) {
            $this->setData(array(), $status = '0', $a['fx_money_err']);
        }
        if ($siteInfo['is_money'] == 1) {
            //判断提现金额是否满足规则设定金额
            if ($siteInfo['money'] > $fx_money) {
                $this->setData(array(), $status = '0', $a['fx_full'] . $siteInfo['money'] . $a['fx_full_money']);
            }
        }

        if ($siteInfo['is_time'] == 1) {
            //判断提现次数是否满足当月次数
            $year = date("Y");
            $month = date("m");
            $allday = date("t");
            $begin_month = strtotime($year . "-" . $month . "-1");
            $end_month = strtotime($year . "-" . $month . "-" . $allday) + 3600 * 24 - 1;
            $count = $fxApplyMod->getCount(array("cond" => "status in (1,2) and store_id=" . $store_id . " and add_time>=" . $begin_month . " and add_time<=" . $end_month . " and user_id=" . $this->userId));
            if ($count >= $siteInfo['time']) {
                $this->setData(array(), $status = '0', $a['fx_no_time']);
            }
        }
        $apply_arr = array(
            'user_id' => $this->userId,
            'apply_money' => $fx_money,
            'store_cate' => $store_cate,
            'store_id' => $store_id,
            'add_time' => time(),
            'status' => 1
        );
        $res = $fxApplyMod->doInsert($apply_arr);
        if ($res) {
            $this->setData(array("url" => "?app=userCenter&act=myCenter&storeid={$storeid}&lang={$lang}&auxiliary={$auxiliary}"), $status = '1', $a['saveAdd_Success']);
        } else {
            $this->setData(array(), $status = '0', $a['saveAdd_fail']);
        }
    }

    /*
     * 提现记录
     * @author lee
     * @date 2017-11-24 16:48:20
     */

    public function cash_list() {
        $user_id = $this->userId;
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $fxApplyMod = &m('fxOutmoneyApply');
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        //本月提现记录
        $this_month = $this->thisMonth();
        $where = "user_id = " . $user_id . " and  status =2  and  store_id = " . $storeid . " and add_time >= " . $this_month['begin_month'] . " and add_time <= " . $this_month['end_month'];
        $this_data = $fxApplyMod->getData(array("cond" => $where));
        $this->assign("this_data", $this_data);

        //上月提现记录
        $last_month = $this->lastMonth();
        $where = "user_id = " . $user_id . " and  status =2  and  store_id = " . $storeid . " and add_time >= " . $last_month['begin_month'] . " and add_time <= " . $last_month['end_month'];
        $last_data = $fxApplyMod->getData(array("cond" => $where));
        $this->assign("last_data", $last_data);

        //提现总金额
        $where = "user_id = " . $user_id . " and store_id = " . $storeid . " and status = 2";
        $sum_money = $fxApplyMod->getData(array("cond" => $where, "fields" => "sum(apply_money) as money"));
        $this->assign("sum_money", $sum_money[0]);

        $this->display("fx/myfx-cash-list.html");
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

    /*
     * 推荐分享自己的推荐码
     * @author lee
     * @date 2017-11-25 13:38:40
     * @param $fx_code 分销码
     */

    public function shareMyfxcode($fx_code) {
        if (!$fx_code) {
            $this->setData($info, $status = 'error', $message = 'Lack of order number');
        }
        include_once ROOT_PATH . "/includes/classes/class.qrcode.php";
        $value = "http://" . SYSTEM_WEB . "/" . SYSTEM_FILE_NAME . "/wx.php?app=user&act=doFxuser&fx_code=" . $fx_code; //二维码内容
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
     * 个人资料
     * @author lee
     * @date 2017-8-16 10:13:47
     */

    public function userInfo() {
        //语言包
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $userId = $this->userId;
        $langid = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;
        $sql = "select u.*,i.surname,i.name as i_name,i.nickname from " . DB_PREFIX . "user as u LEFT JOIN " . DB_PREFIX . "user_info as i on u.id=i.user_id where u.id= " . $userId;
        $info = $this->userMod->querySql($sql);
        $this->assign("info", $info[0]);
        $this->assign('langid', $langid);
        $this->display("userCenter/myaccount-info.html");
    }

    public function userInfo1() {
        //语言包
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $this->assign('auxiliary', $auxiliary);
        $userId = $this->userId;
        $langid = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;
        $sql = "select c_id from " . DB_PREFIX . 'user_coupon where user_id=' . $this->userId . ' and store_id= ' . $storeid;

        $info = $this->userMod->querySql($sql);
        foreach ($info as $key => $val) {
            $cIds[] = $val['c_id'];
        }
        $cIds = implode(',', $cIds);
        $cSql = "select * from " . DB_PREFIX . 'coupon  where id in (' . $cIds . ') and start_time < ' . time() . ' and end_time > ' . time();
        $cData = $this->userMod->querySql($cSql);
        $this->assign('cData', $cData);
        $this->assign('langid', $langid);



        $this->assign('symbol', $this->symbol);

        $this->display("userCenter/coupon.html");
    }

    /*
     * 保存个人资料
     * @author lee
     * @date 2017-8-17 14:53:02
     */

    public function saveInfo() {
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;
        $storeid = !empty($_REQUEST['storeid']) ? (int) ($_REQUEST['storeid']) : '0';
        $this->load($this->shorthand, 'userCenter/userCenter');
        $a = $this->langData;
        $nickname = $_REQUEST['nickname'] ? $_REQUEST['nickname'] : "";
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $email = $_REQUEST['email'];
        $phone = $_REQUEST['phone'];
        $old_password = $_REQUEST['old_password'] ? $_REQUEST['old_password'] : "";
        $new_password = $_REQUEST['new_password'] ? $_REQUEST['new_password'] : "";
        $new_again = $_REQUEST['new_again'] ? $_REQUEST['new_again'] : "";
        $userId = $this->userId;
        $userMod = &m('user');
        $user_info = $userMod->getOne(array("cond" => "id=" . $userId));
        //用户资料更新
        if ($nickname) {
            $data = array(
                'username' => $nickname,
            );
            $res = $userMod->doEdit($userId, $data);
            if ($res) {
                $_SESSION['userName'] = $nickname;
            }
        }
        //邮箱更新
        if ($email) {
            if (!preg_match("/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/", $email)) {
                $this->setData(array(), $status = '0', $a['userinfo_Email_Error']);
            }
            if ($this->userMod->isExist($type = 'email', $email, 'mark', 1)) {
                $this->setData(array(), $status = '0', $a['userinfo_Hasbeen']);
            }
            $data = array(
                'email' => $email,
            );
            $res = $userMod->doEdit($userId, $data);
            if ($res == false) {
                $this->setData(array(), $status = '0', $a['userinfo_fail']);
            }
        }
        //手机更新
        if ($phone) {
            if (!preg_match('/^(13[0-9]|14[0-9]|15[0-9]|17[0-9]|18[0-9])\d{8}$/', $phone)) {
                $this->setData(array(), $status = '0', $a['userinfo_Phone_Error']);
            }
            if ($this->getPhoneInfo($phone)) {
                $this->setData(array(), $status = '0', $a['userinfo_Hasbeen_Phone']);
            }
            $data = array(
                'phone' => $phone,
            );
            $res = $userMod->doEdit($userId, $data);
            if ($res == false) {
                $this->setData(array(), $status = '0', $a['userinfo_fail']);
            }
        }
        if ($new_password) {
            //更新用户密码
            if ((md5($old_password) != $user_info['password']) && $old_password != '') {
                $this->setData(array(), $status = '0', $a['userinfo_Verifications']);
            }
            if ($new_password != $new_again) {
                $this->setData(array(), $status = '0', $a['userinfo_x_password']);
            }
            $data = array(
                'password' => md5($new_password)
            );
            $res = $userMod->doEdit($user_info['id'], $data);
            if ($res == false) {
                $this->setData(array(), $status = '0', $a['userinfo_fail']);
            }
        }
        $info['url'] = "?app=userCenter&act=userInfo&storeid={$storeid}&lang={$lang}&auxiliary={$auxiliary}";
        $this->setData($info, $status = '1', $a['userinfo_Success']);
    }

    //手机验证
    public function getPhoneInfo($edit_phone) {
        $sql = 'select id from  bs_user where mark =1 and  phone = ' . $edit_phone . '  limit 1';
        $data = $this->userMod->querySql($sql);
        return $data[0]['id'];
    }

    //验证码验证
    public function getSmsCode($phone) {
        $smsMod = &m('sms');
        $sql = 'select  phone,code  from bs_sms where  phone =' . $phone . '  order by id desc  limit 1';
        $data = $smsMod->querySql($sql);
        return $data[0]['code'];
    }

    /*
     * 验证邮箱弹窗
     */

    public function doEmail() {
        $this->load($this->shorthand, 'userCenter/userCenter');
        $a = $this->langData;
        $this->assign('langdata', $this->langData);
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $storeid = !empty($_REQUEST['storeid']) ? (int) ($_REQUEST['storeid']) : '0';
        $this->assign('lang', $lang);
        $userId = $this->userId;
        if ($_POST) {
            $email = $_POST['email'] ? trim($_POST['email']) : '';
            $password = $_POST['password'] ? trim($_POST['password']) : '';
            $userMod = &m('user');
            $user_info = $userMod->getOne(array("cond" => "id=" . $userId));
            //邮箱更新
            if (!$email) {
                $this->setData(array(), $status = '0', $a['userinfo_Email_Cannot_Error']);
            }
            if (!preg_match("/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/", $email)) {
                $this->setData(array(), $status = '0', $a['userinfo_Email_Error']);
            }
            if ($this->userMod->isExist($type = 'email', $email, 'mark', 1)) {
                $this->setData(array(), $status = '0', $a['userinfo_Hasbeen']);
            }
            if (!$password) {
                $this->setData(array(), $status = '0', $a['userinfo_Password_Error']);
            }
            if (md5($password) != $user_info['password']) {
                $this->setData(array(), $status = '0', $a['userinfo_Apply']);
            }
            $info = array(
                "email" => $email,
            );
            $res = $userMod->doEdit($user_info['id'], $info);
            if ($res) {
                $info['url'] = "?app=userCenter&act=userInfo&storeid={$storeid}&lang={$lang}&auxiliary={$auxiliary}";
                $this->setData($info, $status = '1', $a['userinfo_Success']);
            } else {
                $this->setData(array(), $status = '0', $a['userinfo_fail']);
            }
        } else {

            $this->display("userCenter/doEmail.html");
        }
    }

    /*
     * 验证手机弹窗
     */

    public function doPhone() {
        $this->load($this->shorthand, 'userCenter/userCenter');
        $a = $this->langData;
        $this->assign('langdata', $this->langData);
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;
        $storeid = !empty($_REQUEST['storeid']) ? (int) ($_REQUEST['storeid']) : '0';
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('lang', $lang);
        $userId = $this->userId;
        if ($_POST) {
            $phone = trim($_REQUEST['phone']);
            $code = $_REQUEST['code'];
            $userMod = &m('user');
            $user_info = $userMod->getOne(array("cond" => "id=" . $userId));
            //手机更新
            if (!$phone) {
                $this->setData(array(), $status = '0', $a['userinfo_Mobile_Phone_Error']);
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
            $info = array(
                "phone" => $phone,
            );
            $res = $userMod->doEdit($user_info['id'], $info);
            if ($res) {
                $info['url'] = "?app=userCenter&act=userInfo&storeid={$storeid}&lang={$lang}&auxiliary={$auxiliary}";
                $this->setData($info, $status = '1', $a['userinfo_Success']);
            } else {
                $this->setData(array(), $status = '0', $a['userinfo_fail']);
            }
        } else {
            $this->display("userCenter/doPhone.html");
        }
    }

    /*
     * 我的订单
     * @author wangs
     * @2017-10-23 13:59:10
     */

    public function myOrder() {
        //语言包
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $a = $this->langData;
        $userId = $this->userId;
        $this->assign('symbol', $this->symbol);
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //1中文，2英语
        $state = !empty($_REQUEST['state']) ? htmlspecialchars(trim($_REQUEST['state'])) : '';
        $order_type = isset($_REQUEST['order_type']) ? trim($_REQUEST['order_type']) : 'order_six_month'; //近6个个月
        $now = time();
        $where = ' buyer_id =' . $userId;
        $six_month_pre = strtotime('-6 months', $now);
        $twelve_month_pre = strtotime('-12 months', $now);
        if ($order_type && $order_type == 'order_twelve_month') {
            $where .= ' and add_time >=' . $twelve_month_pre;
        } else {
            $where .= ' and add_time >=' . $six_month_pre;
        }
        $where .= ' and mark =1';
        if (!empty($state) && $state == 'undeal') {
            $stauts = '10,20,30,40';
            $where .= " and order_state in ( {$stauts})";
        }
        //查找店铺是总代理还是经销商，总代理显示所有订单，
        $storeMod = &m('store');
        $sqlStore = 'select store_type  from  ' . DB_PREFIX . 'store where id  = ' . $this->storeid;
        $dataStore = $storeMod->querySql($sqlStore);
        if ($dataStore[0]['store_type'] == 1) {
            //总代理
            //订单列表数据
            $sql = 'select * from '
                    . DB_PREFIX . 'order where '
                    . $where . ' order by order_id desc';
        } else {
            //经销商
            //订单列表数据
            $sql = 'select * from '
                    . DB_PREFIX . 'order where '
                    . $where . ' and store_id =' . $storeid . ' order by order_id desc';
        }
        $data = $this->orderMod->querySql($sql);  //获取订单商品数量
        foreach ($data as $k => $v) {
            //数量
            $num_sql = "select count(*) as num from " . DB_PREFIX . "order_goods where order_id = " . $v['order_sn'];
            $num = $this->orderGoodsMod->querySql($num_sql);
            $data[$k]['num'] = $num;
            //获取订单对应商品信息
            $sql = "select o.*,o.goods_id as ogoods_id,l.* from "
                    . DB_PREFIX . "order_goods as o left join "
                    . DB_PREFIX . "store_goods as s on o.goods_id = s.id left join "
                    . DB_PREFIX . "goods_lang as l  on s.goods_id = l.goods_id "
                    . " where o.order_id=" . $v['order_sn'] . " and lang_id = " . $lang;
            $list = $this->orderGoodsMod->querySql($sql);
            foreach ($list as $k2 => $v2) {
                if ($v2['spec_key']) {
                    $k_info = $this->get_spec($v2['spec_key'], $lang);
                    foreach ($k_info as $k5 => $v5) {
                        $list[0][$k5]['spec_key_name'] = $v5['item_name'];
                    }
                }
            }
            $data[$k]['goods_list'] = $list;
//            print_r($data[$k]['goods_list']);exit;
            //赠品
            $sqle = "select * from " . DB_PREFIX . "gift_goods  as g left join "
                    . DB_PREFIX . "store_goods as s on g.goods_id = s.id left join "
                    . DB_PREFIX . "goods_lang as l on s.goods_id = l.goods_id " . " where g.id=" . $v['gift_id'] . " and  lang_id = " . $lang;
            $res = $this->giftGoodMod->querySql($sqle);
            if ($res[0]['goods_key']) {
                $k_info = $this->get_spec($res[0]['goods_key'], $lang);
                if ($k_info) {
                    $res[0]['goods_key_name'] = $k_info[0]['item_name'];
                }
            }

            if ($data[$k]['sendout'] == 1) {
                $data[$k]['shippingMethod'] = '自提';
            }
            if ($data[$k]['sendout'] == 2) {
                $data[$k]['shippingMethod'] = '配送上门';
            }
            if ($data[$k]['sendout'] == 3) {
                $data[$k]['shippingMethod'] = '邮寄托运';
            }

            $data[$k]['gift'] = $res;
        }

//        print_r($data);exit;
        $this->assign('data', $data);
        $this->assign('storeid', $storeid);
        $this->assign('lang', $lang);
        $OrderStatus = array(
            "0" => $a['Canceled'],
            "10" => $a['Unpaid'],
            "20" => $a['payment'],
            "30" => $a['Shipped'],
            "40" => $a['Shipped_1'],
            "50" => $a['Receivedgoods'],
        );

        $this->assign("state", $state);
        $this->assign('status', $OrderStatus);
        $this->assign('order_type', $order_type);
        $lang_id = $this->shorthand;
        $this->assign('lang_id', $lang_id);
        $this->display("userCenter/my-order.html");
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

    /*
     * 我的订单详情
     * @author wangs
     * @2017-10-24 13:59:10
     */

    public function order_details() {
        $symbol = $this->symbol;
        $this->assign('symbol', $symbol);
        //语言包
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $a = $this->langData;
        $userId = $this->userId;
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //1中文，2英语
        $orderid = $_REQUEST['orderid']; //订单id
        $where = ' f.buyer_id =' . $userId . ' and g.order_id =' . $orderid;
        $where .= ' and g.mark =' . 1;
        //查找店铺是总代理还是经销商，总代理显示所有订单，
        $storeMod = &m('store');
        $sqlStore = 'select store_type  from  ' . DB_PREFIX . 'store where id  = ' . $this->storeid;
        $dataStore = $storeMod->querySql($sqlStore);
        if ($dataStore[0]['store_type'] == 1) {
            //总代理
            //订单列表页数据
            $sql = 'select f.*,g.*,a.*, g.`add_time` from ' . DB_PREFIX . 'order as g left join ' . DB_PREFIX . 'user_address a'
                    . ' on a.user_id = g.buyer_id'
                    . ' inner join ' . DB_PREFIX . 'order_goods as f '
                    . 'on f.order_id = g.order_sn '
                    . 'where' . $where;
        } else {
            //经销商
            //订单列表页数据
            $sql = 'select f.*,g.*,a.*, g.`add_time` from ' . DB_PREFIX . 'order as g left join ' . DB_PREFIX . 'user_address a'
                    . ' on a.user_id = g.buyer_id'
                    . ' inner join ' . DB_PREFIX . 'order_goods as f '
                    . 'on f.order_id = g.order_sn '
                    . 'where' . $where . ' and g.store_id =' . $storeid;
        }
        $data = $this->orderMod->querySql($sql);
        //获取订单所有商品
//        $cond = array(
//            'cond' => "order_id=" . $data[0]['order_sn']
//        );
//        $data[0]['goods_list'] = $this->orderGoodsMod->getData($cond);
        $sql = "select o.*,o.goods_id as ogoods_id,l.* from "
                . DB_PREFIX . "order_goods as o left join "
                . DB_PREFIX . "store_goods as s on o.goods_id = s.id left join "
                . DB_PREFIX . "goods_lang as l  on s.goods_id = l.goods_id "
                . " where o.order_id=" . $data[0]['order_sn'] . " and lang_id = " . $lang;
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
        if ($data[0]['sendout'] == 1) {
            $shippingMethod = '自提';
        }
        if ($data[0]['sendout'] == 2) {
            $shippingMethod = '配送上门';
        }
        if ($data[0]['sendout'] == 3) {
            $shippingMethod = '邮寄托运';
        }
        $this->assign('shippingMethod', $shippingMethod);
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
        $this->assign('storeid', $storeid);
        $this->assign('lang', $lang);
        $this->assign('info', $data[0]);
        $OrderStatus = array(
            "0" => $a['Canceled'],
            "10" => $a['Unpaid'],
            "20" => $a['payment'],
            "30" => $a['Shipped'],
            "40" => $a['Shipped_1'],
            "50" => $a['Receivedgoods'],
        );
        $this->assign('status', $OrderStatus);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $this->display("userCenter/myorder-detail.html");
    }

    /*
     * 我的地址
     */

    public function myAddress() {
        $pageUrl = !empty($_REQUEST['pageUrl']) ? $_REQUEST['pageUrl'] : '';
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $cart_ids = !empty($_REQUEST['item_id']) ? $_REQUEST['item_id'] : '';
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $addressMod = &m("userAddress");
        $userId = $this->userId;
//        $zoneMod = &m('zone');
//        $countryMod = &m('country');
//        $cityMod = &m('city');
//        $res = $addressMod->getOne(array("cond" => "user_id=" . $userId));
//        $store_address = explode('_', $res['store_address']);
//        if (count($store_address) == 3) {
//            $this->assign('switch', 1);
//            $ch_store_address = $store_address;
//            $pros = $this->cityMod->getParentNodes();
//            $pro = $cityMod->getOne(array("cond" => "id=" . $ch_store_address[0]));
//            $pros['pro_name'] = $pro['name'];
//            $city = $cityMod->getOne(array("cond" => "id=" . $ch_store_address[1]));
//            $pros['city_name'] = $city['name'];
//            $area = $cityMod->getOne(array("cond" => "id=" . $ch_store_address[2]));
//            $pros['area_name'] = $area['name'];
//            $this->assign('pros', $pros);
//        } else {   
//            $this->assign('switch', 2);
//            $countrys = $this->countryMod->getCountryNodes();
//            $en_store_address = $store_address;
//            $country = $countryMod->getOne(array("cond" => "country_id=" . $en_store_address[0]));
//            $countrys['country_name'] = $country['name'];
//            $province = $zoneMod->getOne(array("cond" => "zone_id=" . $en_store_address[1]));
//            $countrys['province_name'] = $province['name'];
//            $this->assign('countrys', $countrys);
//        }
//        $count = strpos($res['address'], "_");
//        $str = substr_replace($res['address'], "", $count, 1);
//        $this->assign('address1', $str);
//        $this->assign("info", $res);
        $mr_sql = "select * from " . DB_PREFIX . "user_address where `user_id`='{$userId}' and default_addr=1 and distinguish=2";
        $mr_res = $addressMod->querySql($mr_sql);
        $this->assign("mr_res", $mr_res);
        $sql = "select * from " . DB_PREFIX . "user_address where `user_id`='{$userId}' and default_addr!=1  and distinguish=2";
        $res = $addressMod->querySql($sql);
        $this->assign("res", $res);
        $this->assign('item_ids', $cart_ids);
        $this->assign('auxiliary', $auxiliary);
        $lang_id = $this->shorthand;
        $this->assign('lang_id', $lang_id);
        $this->assign("pageUrl", $pageUrl);
       $this->assign("url", urlencode($pageUrl));
        $this->display("userCenter/managedress.html");
    }

    /*
     * 设置默认当前地址
     * @author wangs
     * @date 2018-8-28
     */

    //设置默认当前地址
    public function addr_default() {
        $data_id = !empty($_REQUEST['data_id']) ? htmlspecialchars(trim($_REQUEST['data_id'])) : '0';
        $sql = "select * from " . DB_PREFIX . "user_address where user_id=" . $this->userId . ' and default_addr =1  and distinguish=2';
        $userAddressMod = &m('userAddress');
        $addrinfo = $userAddressMod->querySql($sql);
        if ($addrinfo[0]['default_addr'] == 1) {
            $data = array(
                'default_addr' => 0
            );
            $userAddressMod->doEdits($addrinfo[0]['id'], $data);
        }
        $datas = array(
            'default_addr' => 1
        );
        $res = $userAddressMod->doEdits($data_id, $datas);
        if ($res) {
            $this->setData(array(), $status = 1, '设置成功');
        } else {
            $this->setData(array(), $status = 1, '设置失败');
        }
    }

    /*
     * 添加地址
     */

    public function addAddress() {
        $pageUrl = !empty($_REQUEST['pageUrl']) ? $_REQUEST['pageUrl'] : '';
        //语言包
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $this->assign('pros', $this->cityMod->getParentNodes());
        $this->assign('countrys', $this->countryMod->getCountryNodes());
        $this->assign("pageUrl", $pageUrl);
        $lang_id = $this->shorthand;
        $this->assign('lang_id', $lang_id);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $this->display("userCenter/addadress.html");
    }

    /**
     * 获取城市和区域列表
     * @author wanyan
     * @date 2017-08-31
     */
    public function getZoneData() {
        $id = !empty($_REQUEST['pro_id']) ? intval($_REQUEST['pro_id']) : '0';
        $sql = "select `zone_id`,`name` from " . DB_PREFIX . "zone where `status` =1 and `country_id`='{$id}'";
        $rs = $this->storeCateMod->querySql($sql);
        echo json_encode($rs);
        die;
    }

    /**
     * 获取城市和区域列表
     * @author wanyan
     * @date 2017-08-31
     */
    public function getAjaxData() {
        $id = !empty($_REQUEST['pro_id']) ? intval($_REQUEST['pro_id']) : '0';
        $rs = $this->cityMod->getData(array('cond' => "`parent_id`='{$id}'", 'fields' => "`id`,`name`"));
        foreach ($rs as $k => $v) {
            if ($v['id'] == 1) {
                unset($rs[0]);
            }
        }
        echo json_encode($rs);
        die;
    }

    /*
     * 删除地址
     * @author lee
     * @date 2017-9-25 13:48:06
     */

    public function addDelete() {
        $this->load($this->shorthand, 'userCenter/userCenter');
        $a = $this->langData;
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //1中文，2英语
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $id = empty($_REQUEST['id']) ? 0 : $_REQUEST['id'];
        $addressMod = &m("userAddress");
        $res = $addressMod->doDrop($id);
        if ($res) {
            $this->setData(array("url" => "?app=userCenter&act=myCenter&storeid={$storeid}&lang={$lang}&auxiliary={$auxiliary}"), $status = '1', $a['saveAdd_Success']);
        } else {
            $this->setData(array(), $status = '1', $a['saveAdd_fail']);
        }
    }

    /*
     * 保存地址
     * @author lee
     * @date 2017-8-18 13:52:51
     */

    public function saveAddress() {
        //加载语言包
        $this->load($this->shorthand, 'userCenter/userCenter');
        $a = $this->langData;
        $langid = !empty($_REQUEST['lang']) ? (int) ($_REQUEST['lang']) : '0';
        $storeid = !empty($_REQUEST['storeid']) ? (int) ($_REQUEST['storeid']) : '0';
        $returnUrl = !empty($_REQUEST['returnUrl']) ? $_REQUEST['returnUrl'] : '0';
        $returnUrl= urlencode($returnUrl);
//        $book_name = $_REQUEST['book_name'] ? $_REQUEST['book_name'] : "";
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) : ''; //收货人  
        $address = !empty($_REQUEST['address']) ? htmlspecialchars(trim($_REQUEST['address'])) : '';
//        $postal = !empty($_REQUEST['postal']) ? htmlspecialchars(trim($_REQUEST['postal'])) : '';
//        $type = $_REQUEST['type'] ? $_REQUEST['type'] : 0;
//        $company_name = !empty($_REQUEST['company_name']) ? htmlspecialchars(trim($_REQUEST['company_name'])) : '';
        $city = $_REQUEST['city'] ? $_REQUEST['city'] : "";
        $phone = !empty($_REQUEST['phone']) ? htmlspecialchars(trim($_REQUEST['phone'])) : '';
//        $is_receipt = $_REQUEST['is_receipt'] ? $_REQUEST['is_receipt'] : 1;
//        $is_bill = $_REQUEST['is_bill'] ? $_REQUEST['is_bill'] : 1;
        $add_id = $_REQUEST['add_id'] ? $_REQUEST['add_id'] : 0;
        $id = !empty($_REQUEST['id']) ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        $switchLan = !empty($_REQUEST['switch_lan']) ? htmlspecialchars(trim($_REQUEST['switch_lan'])) : '';
        $pro_id = !empty($_REQUEST['pro_id']) ? htmlspecialchars(trim($_REQUEST['pro_id'])) : ''; //国内表省
        $city_id = !empty($_REQUEST['city_id']) ? htmlspecialchars(trim($_REQUEST['city_id'])) : ''; //国内表市
        $area_id = !empty($_REQUEST['area_id']) ? htmlspecialchars(trim($_REQUEST['area_id'])) : ''; //国内表区 
        $country_id = !empty($_REQUEST['country_id']) ? htmlspecialchars(trim($_REQUEST['country_id'])) : ''; //国家表
        $zhou_id = !empty($_REQUEST['zhou_id']) ? htmlspecialchars(trim($_REQUEST['zhou_id'])) : ''; //国家省表 
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $userAddressMod = &m('userAddress');
//        if (empty($book_name)) {
//            $this->setData(array(), $status = '0', $a['saveAdd_book_name']);
//        }
        if (empty($name)) {
            $this->setData(array(), $status = '0', $a['saveAdd_name']);
        }
        if (empty($address)) {
            $this->setData(array(), $status = '0', $a['saveAdd_address']);
        }
        if (empty($phone)) {
            $this->setData(array(), $status = '0', $a['saveAdd_phone']);
        }
        if ($switchLan == 1) {
            if (empty($pro_id) || empty($city_id) || empty($area_id)) {
                $res = $this->setData(array(), $status = '0', $a['saveAdd_province_city']);
            }
            $cityName = $this->cityMod->getData(array('cond' => "`id`='{$city_id}'", 'fields' => "`name`"));
            $areaName = $this->cityMod->getData(array('cond' => "`id`='{$area_id}'", 'fields' => "`name`"));
            $proName = $this->cityMod->getData(array('cond' => "`id`='{$pro_id}'", 'fields' => "`name`"));
            $store_address = $pro_id . '_' . $city_id . '_' . $area_id;
            $address1 = $proName[0]['name'] . $cityName[0]['name'] . $areaName[0]['name'] . '_' . $address;
            $latlonAddress = $proName[0]['name'] . $cityName[0]['name'] . $areaName[0]['name'] . $address;
            $addressName = $proName[0]['name'] . $cityName[0]['name'] . $areaName[0]['name'];
        } else {
            if (empty($country_id) || empty($zhou_id)) {
                $res = $this->setData(array(), $status = '0', $a['saveAdd_Country_State']);
            }
            if (empty($city)) {
                $res = $this->setData(array(), $status = '0', $a['saveAdd_City1']);
            }
            $countryMod = &m('country');
            $zoneMod = &m('zone');
            $store_address = $country_id . '_' . $zhou_id;
            $countrys = $this->countryMod->getCountryNodes();
            $country = $countryMod->getOne(array("cond" => "country_id=" . $country_id));
            $province = $zoneMod->getOne(array("cond" => "zone_id=" . $zhou_id));
            $address1 = $country['name'] . $province['name'] . $city . '_' . $address;
            $latlonAddress = $country['name'] . $province['name'] . $city . '_' . $address;
            $addressName = $country['name'] . $province['name'] . $city;
        }
        //详细地址转化为经纬度
        $res = file_get_contents("http://api.map.baidu.com/geocoder/v2/?address={$latlonAddress}&output=json&ak=CmfcOlGRQE7OztyHtDGLoiiNGYUu37Te");
        $res = json_decode($res);
        $latlon = $res->result->location->lng . ',' . $res->result->location->lat;

        $addr_sql = "select * from " . DB_PREFIX . "user_address where user_id=" . $this->userId . ' and default_addr =1  and distinguish=2';
        $info = $userAddressMod->querySql($addr_sql);
        if ($info[0]['default_addr'] != '1') {
            $data = array(
//                "book_name" => $book_name,
                "name" => $name, //收货人
                "address" => $address1, //地址
                "store_address" => $store_address, //地址
                "app" => $address,
                "city" => $city, //国际表的城市
//                "postal_code" => $postal, //邮编
                "phone" => $phone, //电话
//                "type" => $type,
//                "is_receipt" => $is_receipt,
//                "company_name" => $company_name,
//                "is_bill" => $is_bill,
                "user_id" => $this->userId,
                'pays' => $addressName,
                'latlon' => $latlon,
                'distinguish' => 2,
                'default_addr' => 1,
            );
        } else {
            $data = array(
//                "book_name" => $book_name,
                "name" => $name, //收货人
                "address" => $address1, //地址
                "store_address" => $store_address, //地址
                "app" => $address,
                "city" => $city, //国际表的城市
//                "postal_code" => $postal, //邮编
                "phone" => $phone, //电话
//                "type" => $type,
//                "is_receipt" => $is_receipt,
//                "company_name" => $company_name,
//                "is_bill" => $is_bill,
                "user_id" => $this->userId,
                'pays' => $addressName,
                'latlon' => $latlon,
                'distinguish' => 2,
            );
        }


        //删除原来的地址
        if ($id) {
            $res = $userAddressMod->doEdit($id, $data);
        } else {
            $res = $userAddressMod->doInsert($data);
        }
        //添加最新的地址
        if ($res) {
            if ($returnUrl) {
                $info['url'] = "?app=userCenter&act=myAddress&storeid={$storeid}&lang={$langid}&auxiliary={$auxiliary}&pageUrl={$returnUrl}";
            } else {
                $info['url'] = "?app=userCenter&act=myAddress&storeid={$storeid}&lang={$langid}&auxiliary={$auxiliary}";
            }
            $this->setData($info, $status = '1', $a['saveAdd_Success']);
        } else {
            if ($returnUrl) {
                $info['url'] = "?app=userCenter&act=myAddress&storeid={$storeid}&lang={$langid}&auxiliary={$auxiliary}&pageUrl={$returnUrl}";
            } else {
                $info['url'] = "?app=userCenter&act=myAddress&storeid={$storeid}&lang={$langid}&auxiliary={$auxiliary}";
            }
//            $info['url'] = "?app=userCenter&act=myAddress&storeid={$storeid}&lang={$langid}";
            $this->setData($info, $status = '0', $a['saveAdd_fail']);
        }
    }

    /*
     * 修改地址
     * @author lee
     * @saveAddress 2017-8-22 16:02:18
     */

    public function editAddress() {
        if (!empty($this->userId)) {
            $returnUrl = $_SERVER['HTTP_REFERER'];
            if ($returnUrl) {
                $this->assign('returnUrl', $returnUrl);
            }
        }
        //语言包
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $address_id = $_REQUEST['id'] ? $_REQUEST['id'] : '';
        $userAddressMod = &m('userAddress');
        $info = $userAddressMod->getOne(array("cond" => "id=" . $address_id . " and user_id=" . $this->userId));
        $store_address = explode('_', $info['store_address']);
        if (count($store_address) == 3) {
            $ch_store_address = $store_address;
            $ch_city = $this->getCity($ch_store_address[0]);
            $ch_area = $this->getCity($ch_store_address[1]);
            $this->assign('switch', 1);
            $this->assign('ch_city', $ch_city);
            $this->assign('ch_area', $ch_area);
            $this->assign('ch_store_address', $ch_store_address);
        } else {
            $this->assign('switch', 2);
            $en_store_address = $store_address;
            $this->assign('en_store_address', $en_store_address);
            $en_zhou = $this->getGzone($en_store_address[0]);
            $this->assign('en_zhou', $en_zhou);
        }

        $this->assign('pros', $this->cityMod->getParentNodes());
        $this->assign('countrys', $this->countryMod->getCountryNodes());
        $this->assign("info", $info);
        $lang_id = $this->shorthand;
        $this->assign('lang_id', $lang_id);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $this->display("userCenter/edit-address.html");
    }

    /**
     * 获取中国地址
     * @author wanyan
     * @date 2017-9-05
     */
    public function getCity($id) {
        $sql = "select `id`,`name` from " . DB_PREFIX . "city where `parent_id`='{$id}'";
        $rs = $this->storeMod->querySql($sql);
        return $rs;
    }

    /**
     * 获取国外地址
     * @author wanyan
     * @date 2017-9-05
     */
    public function getGzone($id) {
        $sql = "select z.zone_id,z.name from " . DB_PREFIX . "country as c left join " . DB_PREFIX . "zone as z on c.country_id = z.country_id where c.country_id = {$id}";
        $rs = $this->storeMod->querySql($sql);
        return $rs;
    }

    /*
     * 收藏的文章
     * @author lee
     * @date 2017-8-17 10:08:56
     */

    public function collectionStore() {
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $store_id = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : 0;
        $lang_id = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : 0;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $userId = $this->userId;
        $sql = 'SELECT article_id FROM  ' . DB_PREFIX . 'user_article  WHERE store_id =  ' . $store_id . ' AND user_id=' . $userId;
        $articleData = $this->colleCtionMod->querySql($sql);
        foreach ($articleData as $k => $v) {
            $id[] = $v['article_id'];
        }
        $ids = implode(',', $id);
        if (!empty($ids)) {
            $articleSql = 'SELECT a.id,al.title,al.brif,a.image,a.add_time FROM ' . DB_PREFIX . 'article AS a LEFT JOIN ' . DB_PREFIX . 'article_lang AS al ON a.id=al.article_id WHERE al.lang_id= ' . $lang_id . ' AND a.id in (' . $ids . ')';
            $listData = $this->colleCtionMod->querySql($articleSql);
        } else {
            $listData = array();
        }

        $this->assign('id', $ids);
        $this->assign('lang', $lang_id);
        $this->assign('listData', $listData);


        $this->display('userCenter/article.html');
    }

    /*
     * 移除收藏的文章
     * @author wangshuo
     * @date 2017-11-6
     */

    public function doArticleDelete() {
        return $this->userArticleMod->doDrops('article_id =' . $_REQUEST['id']);
    }

    /*
     * 收藏的商品
     * @author wangshuo
     * @date 2017-9-18 
     */

    public function collectionGoods() {
        //语言包
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $userId = $this->userId;
        $this->assign('symbol', $this->symbol);
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言
        $where = ' f.user_id =' . $userId;
        //列表页数据
        $sql = 'select distinct f.*,g.*,l.*,gl.original_img from '
                . DB_PREFIX . 'store_goods as g inner join '
                . DB_PREFIX . 'user_collection as f on f.store_good_id = g.id inner join '
                . DB_PREFIX . 'goods as gl on f.good_id = gl.goods_id inner join '
                . DB_PREFIX . 'goods_lang as l on l.goods_id = g.goods_id and l.lang_id=' . $lang . ' where ' . $where . ' and f.store_id =' . $storeid
                . ' group by f.store_good_id order by f.id desc limit 0, 8 ';
        $data = $this->colleCtionMod->querySql($sql);
        foreach ($data as $k => $v) {
            $good_id = $v['store_good_id'];
            $sql = 'select  sum(goods_rank) / count(goods_rank) as res from ' . DB_PREFIX . 'goods_comment where goods_id = ' . $good_id;
            $trance = $this->goodsCommentMod->querySql($sql);
            $data[$k]['rate'] = $trance[0]['res'];
        }
        $this->assign('storeid', $storeid);
        $this->assign('lang', $lang);
        $this->assign('data', $data);
        $lang_id = $this->shorthand;
        $this->assign('lang_id', $lang_id);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $this->display("userCenter/collectionGoods.html");
    }

    /*
     * 移除收藏的商品
     * @author wangshuo
     * @date 2017-9-25 
     */

    public function doDelete() {
        return $this->colleCtionMod->doDrops('store_good_id =' . $_REQUEST['id']);
    }

    /*
     * 收藏的商品ajax判断
     * @author wangshuo
     * @date 2017-9-25 
     */

    public function docollectionGoods() {
        $type = $_GET['type'];
        $good_id = $_GET['id'];
        $userId = $this->userId;
        $storeid = $_GET['store_id'];
        $store_good_id = $_GET['store_good_id'];
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : 1;  //1中文，2英语
        if ($type == 'false') {
            $data = array(
                'table' => 'user_collection',
                'user_id' => $userId,
                'good_id' => $store_good_id,
                'store_id' => $storeid,
                'store_good_id' => $good_id,
                'adds_time' => time(),
                    // 'statu' => 1,
            );

            $res = $this->colleCtionMod->doInsert($data);

            $data['statu'] = 1;
        } else {
            $res = $this->colleCtionMod->doDrops('store_good_id =' . $good_id);
            $data['statu'] = 0;
        }
        $data['id'] = $good_id;
        echo json_encode($data);
        exit;
    }

    /*
     * 我的足迹
     * @author wangshuo
     * @date 2017-9-20
     */

    public function footPrint() {
        //语言包
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $userId = $this->userId;
        $this->assign('symbol', $this->symbol);
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言
        $where = ' f.user_id =' . $userId . ' and f.store_good_id =g.id';
        //列表页数据
        $sql = 'select distinct f.*,g.*,l.*,l.goods_name,gl.original_img,f.store_good_id  from '
                . DB_PREFIX . 'user_footprint as f inner join '
                . DB_PREFIX . 'store_goods as g on f.good_id = g.goods_id inner join '
                . DB_PREFIX . 'goods as gl on f.good_id = gl.goods_id inner join '
                . DB_PREFIX . 'goods_lang as l on l.goods_id = g.goods_id and l.lang_id=' . $lang . ' where '
                . $where . ' and g.mark = 1  and f.store_id =' . $storeid .
                ' group by f.good_id order by f.adds_time desc limit 0, 8 ';
        $data = $this->footPrintMod->querySql($sql);
        foreach ($data as $k => $v) {
            $good_id = $v['store_good_id'];
            $sql = 'select  sum(goods_rank) / count(goods_rank) as res from ' . DB_PREFIX . 'goods_comment where goods_id = ' . $good_id;
            $trance = $this->goodsCommentMod->querySql($sql);
            $data[$k]['rate'] = $trance[0]['res'];
        }
        $this->assign('storeid', $storeid);
        $this->assign('lang', $lang);
        $this->assign('data', $data);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        //映射页面
        $this->display("userCenter/footPrint.html");
    }

    /*
     * 我的推荐     
     * @author wangs
     * @date 2018-8-21
     */

    public function myRecommendation() {
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('storeid', $storeid);
        $this->assign('lang', $lang);
        $this->assign('auxiliary', $auxiliary);
        //语言包
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $userId = $this->userId;
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        //获取当前用户的邀请手机号
        $phone_sql = 'select phone,phone_email from ' . DB_PREFIX . 'user  where id =' . $userId . ' and mark =1';
        $phone_data = $this->userMod->querySql($phone_sql);
        //时间筛选
        $startTime = !empty($_REQUEST['start_time']) ? htmlspecialchars(trim($_REQUEST['start_time'])) : '';
        $endTime = !empty($_REQUEST['end_time']) ? htmlspecialchars(trim($_REQUEST['end_time'])) : '';

        if (empty($startTime) && empty($endTime)) {
            $startTime = date('Y-m-d', strtotime('-7 days'));
            $endTime = date('Y-m-d');
        }
        if ($startTime && $endTime && ($startTime > $endTime)) {
            $temp = $startTime;
            $startTime = $endTime;
            $endTime = $temp;
        }
        $this->assign('startTime', $startTime);
        $this->assign('endTime', $endTime);

        //where条件
        $where = " where phone_email = " . $phone_data[0]['phone'] . " and mark =1 ";
        if ($this->lang == 29) {
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
        //推荐我的人
        $Recommendme_sql = 'select phone,add_time from ' . DB_PREFIX . 'user where phone = ' . $phone_data[0]['phone_email'] . ' and mark =1';
        $Recommendme_data = $this->userMod->querySql($Recommendme_sql);
        $this->assign('Recommendme_data', $Recommendme_data[0]);
        //我推荐的数量
        $num_sql = "select count(*) as count from  " . DB_PREFIX . "user where phone_email = " . $phone_data[0]['phone'] . " and mark =1 order by id";
        $num_res = $this->userMod->querySql($num_sql);
        $this->assign('num_res', $num_res[0]);
        //我推荐的人
        $Irecommend_sql = "select phone,add_time  from  " . DB_PREFIX . "user " . $where . "  order by id";
        $res = $this->userMod->querySqlPageData($Irecommend_sql);
        foreach ($res['list'] as $k => $v) {
            $res['list'][$k]['add_time'] = date('Y-m-d H:i', $v['add_time']);
            if ($v['add_time']) {
                $res['list'][$k]['add_time'] = date('Y-m-d H:i', $v['add_time']);
            } else {
                $res['list'][$k]['add_time'] = '';
            }
            $res['list'][$k]['sort_id'] = $k + 2000 * ($p - 1) + 1; //正序
        }
        $this->assign('list', $res['list']);
        //映射页面
        $this->display("userCenter/myRecommend.html");
    }

    /*
     * 我的分享
     * @author wangs
     * @date 2018-8-22
     */

    public function myShare() {
        $userId = $this->userId;
        $sql = 'select * from ' . DB_PREFIX . 'user where id = ' . $userId . ' and mark =1';
        $res = $this->userMod->querySql($sql);
        $this->assign('res', $res[0]);
        //映射页面
        $this->display("userCenter/share.html");
    }

    /**
     * 获取国外相对应省市
     * @author wanyan
     * @date 2017-08-10
     */
    public function getCityAjaxData() {
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
        $sql = "SELECT `zone_id`,`name`,`code`FROM " . DB_PREFIX . "zone WHERE `country_id` ='{$id}' AND `status` ='1'";
        $rs = $this->footPrintMod->querySql($sql);
        echo json_encode($rs);
        die;
    }

    /*
     * 获取国内省市区
     * @author lee
     * @date 2017-12-5 14:16:17
     */

    public function getHomeCity() {
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 1;
        $sql = "SELECT `id`,`name`,`code`FROM " . DB_PREFIX . "city WHERE `parent_id` ='{$id}'";
        $rs = $this->footPrintMod->querySql($sql);
        echo json_encode($rs);
        die;
    }

    /**
     * 订单ajax判断
     * @author wangs
     * @date 2017-10-26
     */
    public function editOrderState() {
        $fxRevenueLogMod = &m('fxRevenueLog');
        $this->load($this->shorthand, 'userCenter/userCenter');
        $a = $this->langData;
        $_data = explode("_", $_REQUEST['data']);
        $id = $_data[0];
        $state = $_data[1];
        $ops = $_data[2];
        $store_id = $_data[3];

        switch ($state) {
            case 10:
                if ($ops == "cancel") {
                    $set = array(
                        "order_state" => 0,
                    );
                    //取消订单退还积分
                    $this->returnPoint($id);
                }
                break;
            case 40:
                if ($ops == "receive") {

                    $set = array(
                        "order_state" => 50,
                        'finished_time' => time()
                    );
                    //添加订单积分获取 modify by lee
                    $this->doOnePoint($id);
                    $this->doOrderPoint($id);
                }
                break;
        }

        $data = array(
            "table" => "order",
            'cond' => 'order_sn=' . $id,
            'set' => $set
        );
        if ($ops == "cancel") {
            $data['set']['Appoint'] = 2;
            $data['set']['Appoint_store_id'] = $store_id;
        }
        $sql = " select `fx_phone` from " . DB_PREFIX . "order where `order_sn`='{$id}'";
        $ifo = $this->orderGoodsMod->querySql($sql);
        if ($ifo[0]['fx_phone']) {
            $isExist = $fxRevenueLogMod->getOne(array('cond' => "`order_sn` = '{$id}'", 'fields' => 'order_sn'));
            if ($isExist['order_sn']) {
                $this->setData(array(), '0', $a['order_exist']);
            }
            $rs = $this->distrCom($id); // 分销按钮
        }
        $res = $this->orderMod->doUpdate($data);
        $datas = array(
            "table" => "order_goods",
            'cond' => 'order_id=' . $id,
            'set' => $set,
        );
        $res_goods = $this->orderGoodsMod->doUpdate($datas);
        if ($res && $res_goods) {   //删除成功
            $this->setData(array(), '1', $a['operation_Success']);
        } else {
            $this->setData(array(), '0', $a['operation_fail']);
        }
    }

    public function doOnePoint($id) {
        $pointLogMod = &m("pointLog");
        $res = $pointLogMod->getOne(array("cond" => "order_sn=" . $id));
        return $res;
    }

    /*
     * 订单积分获取
     * @author  lee
     * @date 2018-6-21 16:11:31
     */

    public function doOrderPoint($id) {
        $pointSiteMod = &m('point');
        $storePointMod = &m('storePoint');
        $storeMod = &m('store');
        $curMod = &m('currency');
        $userMod = &m('user');
        $order_info = $this->orderMod->getOne(array("cond" => "order_sn=" . $id));
        //获取该订单获取的积分值
        $point_price_site = $pointSiteMod->getOne(array("cond" => "1=1"));
        $store_point_site = $storePointMod->getOne(array("cond" => "store_id=" . $order_info['store_id']));
        $store_id = $order_info['store_id'];
        $store_info = $storeMod->getOne(array("cond" => "id=" . $store_id));
        $rate = $curMod->getCurrencyRate($store_info['currency_id']);

        $money = $store_point_site['order_point'] * $order_info['order_amount'] / 100;
        $point = ceil($money);
        //更新用户的积分值
        $user_id = $this->userId;
        $user_info = $userMod->getOne(array("cond" => "id=" . $user_id));
        $user_point = $user_info['point'] + $point;
        $res = $userMod->doEdit($user_id, array("point" => $user_point));
        //积分日志
        if ($res) {
            $logMessage = "消费订单：" . $order_info['order_sn'] . " 获取：" . $point . "睿积分";
            $this->addPointLog($user_info['phone'], $logMessage, $user_id, $point, '-', $order_info['order_sn']);
        }
    }

    /*
     * 取消订单退还积分
     * @author lee
     * @date 2018-6-22 15:03:17
     */

    public function returnPoint($id) {
        $userMod = &m('user');
        $pointLogMod = &m("pointLog");
        $point_log = $pointLogMod->getOne(array("cond" => "order_sn=" . $id));
        //更新用户的积分值
        if ($point_log) {
            $user_id = $this->userId;
            $user_info = $userMod->getOne(array("cond" => "id=" . $user_id));
            $user_point = $user_info['point'] + $point_log['expend'];
            $res = $userMod->doEdit($user_id, array("point" => $user_point));
            //积分日志
            if ($res) {
                $logMessage = "取消订单：" . $id . " 获取：" . $point_log['expend'] . "睿积分";
                $this->addPointLog($user_info['phone'], $logMessage, $user_id, $point_log['expend'], '-');
            }
        }
    }

    //生成日志
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
     * 根据分销码佣金分配
     * @author wanyan
     * @date 2017-11-21
     */
//    public function distrComByFxCode($order_id) {
//        $fxMainOrder = $this->orderMod->getOne(array('cond' => "`order_sn` = '{$order_id}'", 'fields' => '`store_id`,buyer_id,buyer_name,buyer_email,buyer_address,store_id'));
//        $fxMainOrder['phone'] = $this->getUserPhone($fxMainOrder['buyer_id']);
//        $fxGoodInfo = $this->orderGoodsMod->getData(array('cond' => "`order_id`='{$order_id}' and `fx_code` is not null ", 'fields' => "*"));
//        foreach ($fxGoodInfo as $k => $v) {
//            $res[] = $this->getRuler($fxMainOrder, $v, $v['fx_code']);
//        }
//        return $res;
//    }
    /**
     * 根据分销码佣金分配
     * @author wanyan
     * @date 2017-11-21
     */
    public function distrCom($order_id) {
        $fxMainOrder = $this->orderMod->getOne(array('cond' => "`order_sn` = '{$order_id}'", 'fields' => '`order_id`,`order_sn`,`store_id`,buyer_id,buyer_name,buyer_email,buyer_address,store_id,order_amount,discount,fx_phone,fx_discount_rate'));
        $fxMainOrder['phone'] = $this->getUserPhone($fxMainOrder['buyer_id']);
        $res = $this->getRuler($fxMainOrder);

        return $res;
    }

    /**
     * 根据分销码获取分润规则
     * @author wanyan
     * @date 2017-11-21
     */
//    public function getRulerByFxCode($mainInfo, $goodInfo, $fx_code) {
//        $sql = "SELECT fu.user_id,fu.real_name,fur.fx_level,fur.pid,fur.pidpid FROM " . DB_PREFIX . "fx_user as fu
//            LEFT JOIN " . DB_PREFIX . "fx_usertree as fur ON fu.user_id = fur.user_id WHERE fu.fx_code = '{$fx_code}'";
//        $info = $this->fxRuleMod->querySql($sql);
//        if ($info[0]['fx_level'] == 3) { // 如果三级分销商的分销码
//            $firstUserId = $this->getUserTreeId($info[0]['pidpid']);
//            $secondUserId = $this->getUserTreeId($info[0]['pid']);
//            $fxRule = $this->getRuleDetail($firstUserId);
//            $insert_data_main['lev1_revenue'] = ($fxRule['lev1_prop'] * 0.01 * $goodInfo['goods_pay_price']); // 一级佣金
//            $insert_data_main['lev2_revenue'] = ($fxRule['lev2_prop'] * 0.01 * $goodInfo['goods_pay_price']); // 二级佣金
//            $insert_data_main['lev3_revenue'] = ($fxRule['lev3_prop'] * 0.01 * $goodInfo['goods_pay_price']); // 三级佣金
//            $insert_data_main['lev2_user_id'] = $secondUserId; // 二级分销商ID
//            $insert_data_main['lev2_user_name'] = $this->getDisUser($secondUserId); // 二级分销商姓名
//            $insert_data_main['lev3_user_id'] = $info[0]['user_id']; // 三级分销商ID
//            $insert_data_main['lev3_user_name'] = $this->getDisUser($info[0]['user_id']); //  三级分销商姓名
//            //var_dump($firstUserId);die;
//        } elseif ($info[0]['fx_level'] == 2) { // 如果二级分销商的分销码
//            $firstUserId = $this->getUserTreeId($info[0]['pid']);  // 一级分销商ID
//            $fxRule = $this->getRuleDetail($firstUserId);
//            $insert_data_main['lev1_revenue'] = ($fxRule['lev1_prop'] * 0.01 * $goodInfo['goods_pay_price']); // 一级佣金
//            $insert_data_main['lev2_revenue'] = ($fxRule['lev2_prop'] * 0.01 * $goodInfo['goods_pay_price']); // 二级佣金
//            $insert_data_main['lev3_revenue'] = 0.00; // 三级佣金
//            $insert_data_main['lev2_user_id'] = $info[0]['user_id']; // 二级分销商ID
//            $insert_data_main['lev2_user_name'] = $this->getDisUser($info[0]['user_id']); // 二级分销商姓名
//            $insert_data_main['lev3_user_id'] = 0; // 三级分销商ID
//            $insert_data_main['lev3_user_name'] = ''; // 三级分销商姓名
//        } elseif ($info[0]['fx_level'] == 1) {
////          $firstUserId = $this->getUserTreeId($info[0]['user_id']);  // 一级分销商ID
//            $firstUserId = $info[0]['user_id'];
//            $fxRule = $this->getRuleDetail($firstUserId);
//
//            $insert_data_main['lev1_revenue'] = ($fxRule['lev1_prop'] * 0.01 * $goodInfo['goods_pay_price']); // 一级佣金
//            $insert_data_main['lev2_revenue'] = 0.00; // 二级佣金
//            $insert_data_main['lev3_revenue'] = 0.00; // 三级佣金
//            $insert_data_main['lev2_user_id'] = 0; // 二级分销商ID
//            $insert_data_main['lev2_user_name'] = ''; // 二级分销商姓名
//            $insert_data_main['lev3_user_id'] = 0; // 三级分销商ID
//            $insert_data_main['lev3_user_name'] = ''; // 三级分销商姓名
//        }
//        $store_cate = $this->getCurStoreInfo($mainInfo['store_id']);
//        $insert_data = array(
//            'user_id' => $mainInfo['buyer_id'], // 购买人用户ID
//            'user_name' => $mainInfo['buyer_name'],
//            'phone' => $mainInfo['phone'],
//            'fx_rule_id' => $fxRule['id'],
//            'lev1_prop' => $fxRule['lev1_prop'],
//            'lev2_prop' => $fxRule['lev2_prop'],
//            'lev3_prop' => $fxRule['lev3_prop'],
//            'lev1_user_id' => $firstUserId,
//            'lev1_user_name' => $this->getDisUser($firstUserId),
////          'lev1_revenue' => ($fxRule['lev1_prop']*0.01*$goodInfo['goods_pay_price']), // 一级佣金
////          'lev2_user_id' => $secondUserId,
////          'lev2_user_name' => $this->getDisUser($secondUserId),
////          'lev2_revenue' => ($fxRule['lev2_prop']*0.01*$goodInfo['goods_pay_price']),// 二级佣金
////          'lev3_user_id' => $info[0]['user_id'],
////          'lev3_user_name' => $this->getDisUser($info[0]['user_id']),
//            // 'lev3_revenue' => ($fxRule['lev3_prop']*0.01*$goodInfo['goods_pay_price']),// 三级佣金
//            'order_id' => $goodInfo['rec_id'],
//            'order_sn' => $goodInfo['order_id'],
//            'order_money' => $goodInfo['goods_pay_price'],
//            'store_cate' => $store_cate['store_cate_id'],
//            'store_id' => $mainInfo['store_id'],
//            'add_time' => time()
//        );
//        $insert_data_total = array_merge($insert_data, $insert_data_main);
//        $rs = $this->fxRevenueLogMod->doInsert($insert_data_total);
//        return $rs;
//    }
    public function getRuler($mainInfo) {
        $sql = "SELECT fu.user_id,fu.real_name,fur.fx_level,fur.pid,fur.pidpid FROM " . DB_PREFIX . "fx_user as fu
            LEFT JOIN " . DB_PREFIX . "fx_usertree as fur ON fu.user_id = fur.user_id WHERE fu.telephone = '{$mainInfo['fx_phone']}'";
        $info = $this->fxRuleMod->querySql($sql);
        if ($info[0]['fx_level'] == 3) { // 如果三级分销商的分销码
            $firstUserId = $this->getUserTreeId($info[0]['pidpid']);
            $secondUserId = $this->getUserTreeId($info[0]['pid']);
            $fxRule = $this->getRuleDetail($firstUserId);
            $insert_data_main['lev1_revenue'] = ($fxRule['lev1_prop'] * 0.01 * $mainInfo['order_amount']); // 一级佣金
            $insert_data_main['lev2_revenue'] = ($fxRule['lev2_prop'] * 0.01 * $mainInfo['order_amount']); // 二级佣金
            $insert_data_main['lev3_revenue'] = (($fxRule['lev3_prop'] - $mainInfo['fx_discount_rate']) * 0.01 * $mainInfo['order_amount']); // 三级佣金
            $insert_data_main['lev2_user_id'] = $secondUserId; // 二级分销商ID
            $insert_data_main['lev2_user_name'] = $this->getDisUser($secondUserId); // 二级分销商姓名
            $insert_data_main['lev3_user_id'] = $info[0]['user_id']; // 三级分销商ID
            $insert_data_main['lev3_user_name'] = $this->getDisUser($info[0]['user_id']); //  三级分销商姓名
            //var_dump($firstUserId);die;
        } elseif ($info[0]['fx_level'] == 2) { // 如果二级分销商的分销码
            $firstUserId = $this->getUserTreeId($info[0]['pid']);  // 一级分销商ID
            $fxRule = $this->getRuleDetail($firstUserId);
            $insert_data_main['lev1_revenue'] = ($fxRule['lev1_prop'] * 0.01 * $mainInfo['order_amount']); // 一级佣金
            $insert_data_main['lev2_revenue'] = (($fxRule['lev2_prop'] - $mainInfo['fx_discount_rate']) * 0.01 * $mainInfo['order_amount']); // 二级佣金
            $insert_data_main['lev3_revenue'] = 0.00; // 三级佣金
            $insert_data_main['lev2_user_id'] = $info[0]['user_id']; // 二级分销商ID
            $insert_data_main['lev2_user_name'] = $this->getDisUser($info[0]['user_id']); // 二级分销商姓名
            $insert_data_main['lev3_user_id'] = 0; // 三级分销商ID
            $insert_data_main['lev3_user_name'] = ''; // 三级分销商姓名
        } elseif ($info[0]['fx_level'] == 1) {
//          $firstUserId = $this->getUserTreeId($info[0]['user_id']);  // 一级分销商ID
            $firstUserId = $info[0]['user_id'];
            $fxRule = $this->getRuleDetail($firstUserId);

            $insert_data_main['lev1_revenue'] = (($fxRule['lev1_prop'] - $mainInfo['fx_discount_rate']) * 0.01 * $mainInfo['order_amount']); // 一级佣金
            $insert_data_main['lev2_revenue'] = 0.00; // 二级佣金
            $insert_data_main['lev3_revenue'] = 0.00; // 三级佣金
            $insert_data_main['lev2_user_id'] = 0; // 二级分销商ID
            $insert_data_main['lev2_user_name'] = ''; // 二级分销商姓名
            $insert_data_main['lev3_user_id'] = 0; // 三级分销商ID
            $insert_data_main['lev3_user_name'] = ''; // 三级分销商姓名
        }
        $store_cate = $this->getCurStoreInfo($mainInfo['store_id'], 0);
        $insert_data = array(
            'user_id' => $mainInfo['buyer_id'], // 购买人用户ID
            'user_name' => $mainInfo['buyer_name'],
            'phone' => $mainInfo['phone'],
            'fx_rule_id' => $fxRule['id'],
            'lev1_prop' => $fxRule['lev1_prop'],
            'lev2_prop' => $fxRule['lev2_prop'],
            'lev3_prop' => $fxRule['lev3_prop'],
            'lev1_user_id' => $firstUserId,
            'lev1_user_name' => $this->getDisUser($firstUserId),
//          'lev1_revenue' => ($fxRule['lev1_prop']*0.01*$goodInfo['goods_pay_price']), // 一级佣金
//          'lev2_user_id' => $secondUserId,
//          'lev2_user_name' => $this->getDisUser($secondUserId),
//          'lev2_revenue' => ($fxRule['lev2_prop']*0.01*$goodInfo['goods_pay_price']),// 二级佣金
//          'lev3_user_id' => $info[0]['user_id'],
//          'lev3_user_name' => $this->getDisUser($info[0]['user_id']),
            // 'lev3_revenue' => ($fxRule['lev3_prop']*0.01*$goodInfo['goods_pay_price']),// 三级佣金
            'order_id' => $mainInfo['order_id'],
            'order_sn' => $mainInfo['order_sn'],
            'order_money' => $mainInfo['order_amount'],
            'store_cate' => $store_cate['store_cate_id'],
            'store_id' => $mainInfo['store_id'],
            'discount' => $mainInfo['discount'],
            'discount_rate' => $mainInfo['fx_discount_rate'],
            'add_time' => time()
        );
        $insert_data_total = array_merge($insert_data, $insert_data_main);
        $rs = $this->fxRevenueLogMod->doInsert($insert_data_total);
        return $rs;
    }

    /**
     * 获取分润规则
     * @author wanyan
     * @date 2017-11-21
     */
    public function getUserTreeId($parent_id) {
        $rs = $this->fxUserTreeMod->getOne(array('cond' => "`id` = '{$parent_id}'", 'fields' => "user_id"));
        return $rs['user_id'];
    }

    /**
     * 获取分销商的姓名
     * @author wanyan
     * @date 2017-11-21
     */
    public function getDisUser($user_id) {
        $rs = $this->fxUserMod->getOne(array('cond' => "`user_id` = '{$user_id}'", 'fields' => "real_name"));
        return $rs['real_name'];
    }

    /**
     * 获取用户的电话号码
     * @author wanyan
     * @date 2017-11-21
     */
    public function getUserPhone($user_id) {
        $userAddress = &m('userAddress');
        $rs = $userAddress->getOne(array('cond' => "`user_id` = '{$user_id}'", 'fields' => "phone"));
        return $rs['phone'];
    }

    /**
     * 获取分润规则
     * @author wanyan
     * @date 2017-11-21
     */
    public function getRuleDetail($user_id) {
        $sql = "SELECT  fr.id,fr.lev1_prop,fr.lev2_prop,fr.lev3_prop,fr.store_cate,fr.store_id 
        FROM `bs_fx_user_rule` as fur
        LEFT JOIN `bs_fx_usertree` fut ON  fur.user_id = fut.user_id  
        LEFT JOIN `bs_fx_rule` as fr ON fur.rule_id = fr.id WHERE fur.user_id =" . $user_id;
        $rs = $this->fxRuleMod->querySql($sql);
        return $rs[0];
    }

    /**
     * 订单删除
     * @author wangshuo
     * @date 2017-11-20
     */
    public function dele() {
        $this->load($this->lang_id, 'userCenter/userCenter');
        $a = $this->langData;
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        $set = array(
            "mark" => 2,
        );
        $data = array(
            "table" => "order",
            'cond' => 'order_id=' . $id,
            'set' => $set,
        );
        $res = $this->orderMod->doUpdate($data);
        if ($res) {   //删除成功
            $this->setData(array(), '1', $a['delete_Success']);
        } else {
            $this->setData(array(), '0', $a['delete_fail']);
        }
    }

    /**
     * 退货退款
     * @author wangs
     * @date 2017-10-27
     */
    public function refund() {
        //语言包
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;  //所选的站点id
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言
        $this->assign('lang', $lang);
        $this->assign('storeid', $storeid);
        $this->assign("order_id", $_REQUEST['order_id']);
        $this->assign("order_sn", $_REQUEST['order_sn']);
        //映射页面
        $this->display("userCenter/my-droder-form.html");
    }

//    /**
//     * 退货退款
//     * @author wangs
//     * @date 2017-10-27
//     */
//    public function refundGoods() {
//        //加载语言包
//        $this->load($this->shorthand, 'userCenter/userCenter');
//        $a = $this->langData;
//        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;  //所选的站点id
//        $langid = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言
//        $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars(trim($_REQUEST['order_sn'])) : '';
//        $reason_info = !empty($_REQUEST['reason_info']) ? htmlspecialchars(trim($_REQUEST['reason_info'])) : '';
//        $rec_id = !empty($_REQUEST['rec_id']) ? $_REQUEST['rec_id'] : 0;
//        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
//        if (empty($reason_info)) {
//            $this->setData($info, $status = '0', $a['fill_refund_Reason']);
//        }
//        if (!empty($order_sn)) {
//            //查询订单是否存在有效   有效么判断
//            $data = array(
//                "table" => "order",
//                "cond" => "order_sn=" . $order_sn,
//            );
//            if ($rec_id == 0) {
//                $this->refundGoods2();
//            }
//            $order_info = $this->orderMod->getData($data); //订单详细
//            if (is_array($order_info) && !empty($order_info)) {
//                $_data = array(
//                    "table" => "order_goods",
//                    "cond" => "order_id=" . $order_sn,
//                );
//
//                if (!empty($rec_id)) {
//                    $_data['cond'] = $_data['cond'] . '  and rec_id=' . $rec_id;
//                }
//                $order_good_info = $this->orderMod->getData($_data); //当前退款商品详细
//                //2.退款退货表 插入
//                if (!empty($rec_id)) {
//                    $refund_state = 1;
//                    $order_refund_state = 1;
//                    $order_amount = $order_info[0]['refund_amount'] + $order_good_info[0]['goods_pay_price'] * $order_good_info[0]['goods_num'];
//                } else {
//                    $refund_state = 1;
//                    $order_refund_state = 2;
//                    $order_amount = $order_info[0]['order_amount'];
//                }
//                $refund_return_data = array(
//                    "table" => "refund_return",
//                    "order_id" => $order_info[0]['order_id'], //订单ID
//                    "order_sn" => $order_info[0]['order_sn'], //订单编号
//                    "rec_id" => $rec_id ?: 0, //订单商品ID
//                    "goods_name" => addslashes($order_good_info[0]['goods_name']), //订单商品名称
//                    "order_state" => $order_info[0]['order_state'], //订单状态：0(已取消)10(默认):未付款;20:已付款;30:已发货;40:已收货;',
//                    "reason_info" => $reason_info, //退货原因内容
//                    "store_id" => $order_info[0]['store_id'], //店铺ID
//                    "store_name" => $order_info[0]['store_name'], //店铺名称
//                    "buyer_id" => $order_info[0]['buyer_id'], //买家ID
//                    "buyer_name" => $order_info[0]['buyer_name'], //买家会员名
//                    "goods_num" => $order_good_info[0]['goods_num'], //商品退款数量
//                    "refund_amount" => $order_good_info[0]['goods_pay_price'] * $order_good_info[0]['goods_num'], //订单商品总价格
//                    "refund_amounts" => $order_good_info[0]['goods_pay_price'] * $order_good_info[0]['goods_num'], //退款价格
//                    "add_time" => time(), //添加时间    
//                );
//                $refund_return_id = $this->orderMod->doInsert($refund_return_data);
//                //3.订单商品表 更新
//
//                $order_goods_data = array(
//                    "table" => "order_goods",
//                    'cond' => "order_id=" . $order_sn,
//                    "set" => array(
//                        "refund_state" => $refund_state,
//                    ),
//                );
//                if (!empty($rec_id)) {
//                    $order_goods_data['cond'] = $order_goods_data['cond'] . '  and rec_id=' . $rec_id;
//                }
//                $order_goods_result = $this->orderMod->doUpdate($order_goods_data);
//                //4.订单表  更新
//                $order_data = array(
//                    "table" => "order",
//                    'cond' => "order_sn=" . $order_sn,
//                    "set" => array(
//                        "refund_state" => $order_refund_state,
//                        "refund_amount" => $order_amount,
//                    ),
//                );
//                $order_goods_result = $this->orderMod->doUpdate($order_data);
//
//                if ($refund_return_id && $order_goods_result && $order_goods_result) {
//                    //申请退款成功
//                    $info['url'] = "?app=userCenter&act=myOrder&storeid={$storeid}&lang={$langid}&auxiliary={$auxiliary}";
//                    $this->setData($info, $status = '1', $a['refund_Success']);
//                } else {
//                    //申请退款失败
//                    $info['url'] = "?app=userCenter&act=myOrder&storeid={$storeid}&lang={$langid}&auxiliary={$auxiliary}";
//                    $this->setData($info, $status = '0', $a['refund_fail']);
//                }
//            } else {
//                //提示订单错误
//                $info['url'] = "?app=userCenter&act=myOrder&storeid={$storeid}&lang={$langid}&auxiliary={$auxiliary}";
//                $this->setData($info, $status = '0', $a['refund_order']);
//            }
//        }
//    }

    /**
     * 全部退款
     * @author wangs
     * @date 2017-10-27
     */
    public function refundGoods() {
        //加载语言包
        $this->load($this->shorthand, 'userCenter/userCenter');
        $a = $this->langData;
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;  //所选的站点id
        $langid = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言
        $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars(trim($_REQUEST['order_sn'])) : '';
        $reason_info = !empty($_REQUEST['reason_info']) ? htmlspecialchars(trim($_REQUEST['reason_info'])) : '';
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        if (empty($reason_info)) {
            $this->setData(array(), $status = '0', $a['fill_refund_Reason']);
        }
        if (!empty($order_sn)) {
            //查询订单是否存在有效   有效么判断
            $data = array(
                "table" => "order",
                "cond" => "order_sn=" . $order_sn,
            );
            $order_info = $this->orderMod->getData($data); //订单详细
            if (is_array($order_info) && !empty($order_info)) {
                //2.退款退货表 插入
                $refund_return_data = array(
                    "table" => "refund_return",
                    "order_id" => $order_info[0]['order_id'], //订单ID
                    "order_sn" => $order_info[0]['order_sn'], //订单编号
                    "order_state" => $order_info[0]['order_state'], //订单状态：0(已取消)10(默认):未付款;20:已付款;30:已发货;40:已收货;',
                    "reason_info" => $reason_info, //退货原因内容
                    "store_id" => $order_info[0]['store_id'], //店铺ID
                    "store_name" => $order_info[0]['store_name'], //店铺名称
                    "buyer_id" => $order_info[0]['buyer_id'], //买家ID
                    "buyer_name" => $order_info[0]['buyer_name'], //买家会员名
                    "refund_amount" => $order_info[0]['order_amount'], //订单总价格
                    "refund_amounts" => $order_info[0]['order_amount'], //退款金额
                    "add_time" => time(), //添加时间    
                );
                $refund_return_id = $this->orderMod->doInsert($refund_return_data);
                $refund_state = 1;
                //3.订单商品表 更新
                $order_goods_data = array(
                    "table" => "order_goods",
                    'cond' => "order_id=" . $order_sn . " and refund_state = 0",
                    "set" => array(
                        "refund_state" => $refund_state,
                    ),
                );
                $order_goods_result = $this->orderMod->doUpdate($order_goods_data);
                //4.订单表  更新
                $order_data = array(
                    "table" => "order",
                    'cond' => "order_sn=" . $order_sn,
                    "set" => array(
                        "refund_state" => $refund_state,
                        "refund_amount" => $order_info[0]['order_amount'],
                    ),
                );
                $order_goods_result = $this->orderMod->doUpdate($order_data);

                if ($refund_return_id && $order_goods_result && $order_goods_result) {
                    //申请退款成功
                    $info['url'] = "?app=userCenter&act=myOrder&storeid={$storeid}&lang={$langid}&auxiliary={$auxiliary}";
                    $this->setData($info, $status = '1', $a['refund_Success']);
                } else {
                    //申请退款失败
                    $info['url'] = "?app=userCenter&act=myOrder&storeid={$storeid}&lang={$langid}&auxiliary={$auxiliary}";
                    $this->setData($info, $status = '0', $a['refund_fail']);
                }
            } else {
                //提示订单错误
                $info['url'] = "?app=userCenter&act=myOrder&storeid={$storeid}&lang={$langid}&auxiliary={$auxiliary}";
                $this->setData($info, $status = '0', $a['refund_order']);
            }
        }
    }

    /**
     * 订单评价展示页
     * @author wangs
     * @date 2017-10-27
     */
    public function evaluate() {
        //语言包
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $userId = $this->userId;
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;
        $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars(trim($_REQUEST['order_sn'])) : '';
        $rec_id = !empty($_REQUEST['recid']) ? htmlspecialchars(trim($_REQUEST['recid'])) : '';
        $goods_id = !empty($_REQUEST['gid']) ? htmlspecialchars(trim($_REQUEST['gid'])) : '';
        $storeid = !empty($_REQUEST['storeid']) ? htmlspecialchars(trim($_REQUEST['storeid'])) : '';
        $order_id = !empty($_REQUEST['order_id']) ? htmlspecialchars(trim($_REQUEST['order_id'])) : '';
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $where = ' where buyer_id = ' . $userId . ' and goods_id =' . $goods_id;
        //列表页数据
        $sql = "select * from " . DB_PREFIX . "order_goods" . $where . ' and store_id =' . $storeid . ' and order_id =' . $order_sn;
        $data = $this->orderGoodsMod->querySql($sql);
        $this->assign('data', $data[0]);
        $this->assign('lang', $lang);
        $this->assign('rec_id', $rec_id);
        $this->assign('storeid', $storeid);
        $this->assign('goods_id', $goods_id);
        $this->assign('user_id', $userId);
        $this->assign('order_id', $order_id);
        $this->assign('order_sn', $order_sn);
        $lang_id = $this->shorthand;
        $this->assign('lang_id', $lang_id);
        $this->display("userCenter/order-goods-evaluate.html");
    }

    /**
     * 订单评价添加处理
     * @author wangs
     * @date 2017-11-7
     */
    public function addEvaluate() {
        //加载语言包
        $this->load($this->shorthand, 'userCenter/userCenter');
        $a = $this->langData;
        $userName = $this->userName;
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;  //1中文，2英语
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $user_id = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : $this->userId;  //所选的站点登陆的id
        $store_id = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $goods_id = !empty($_REQUEST['gid']) ? htmlspecialchars(trim($_REQUEST['gid'])) : '';
        $rec_id = !empty($_REQUEST['rec_id']) ? htmlspecialchars(trim($_REQUEST['rec_id'])) : '';
        $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars(trim($_REQUEST['order_sn'])) : '';
        $order_id = !empty($_REQUEST['order_id']) ? htmlspecialchars(trim($_REQUEST['order_id'])) : '';
        $star_num = !empty($_REQUEST['star_num']) ? htmlspecialchars(trim($_REQUEST['star_num'])) : '';
        $evaluete_content = !empty($_REQUEST['evaluete_content']) ? htmlspecialchars(trim($_REQUEST['evaluete_content'])) : '';
        $goods_images = ($_POST['goods_images']) ? $_POST['goods_images'] : '';
        $arr = implode(',', $goods_images);
        $list = rtrim($arr, ',');
        // 数据添加
        $data = array(
            'goods_id' => $goods_id,
            'user_id' => $user_id,
            'order_id' => $order_id,
            'rec_id' => $rec_id,
            'store_id' => $store_id,
            'content' => $evaluete_content,
            'goods_rank' => $star_num,
            'img' => $list,
            'username' => $userName,
            'add_time' => time()
        );
//        print_r($data);exit;
        $res = $this->commentMod->doInsert($data);
        //3.订单商品表 更新
        $order = array(
            "table" => "order",
            'cond' => "order_sn=" . $order_sn,
            "set" => array(
                "evaluation_state" => 1,
            ),
        );
        $order_result = $this->orderMod->doUpdate($order);
        //3.订单商品表 更新
        $order_goods = array(
            "table" => "order_goods",
            'cond' => "order_id=" . $order_sn . " and goods_id=" . $goods_id . " and rec_id=" . $rec_id,
            "set" => array(
                "evaluation_state" => 1,
            ),
        );
        $order_goods_result = $this->orderGoodsMod->doUpdate($order_goods);
        if ($res && $order_goods_result && $order_result) {
            $info['url'] = "index.php?app=userCenter&act=myOrder&storeid={$store_id}&lang={$lang}&auxiliary={$auxiliary}";
            $this->setData($info, $status = 1, $a['evaluate_Success']);
        } else {
            $this->setData(array(), '0', $a['evaluate_fail']);
        }
    }

    /**
     * 获取上传页面
     * @auth wanyan
     * @date 2017-08-07
     */
    public function upload() {
        //语言包
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $num = !empty($_REQUEST['num']) ? intval($_REQUEST['num']) : '0';
        $input = !empty($_REQUEST['input']) ? $_REQUEST['input'] : '';
        $path = !empty($_REQUEST['path']) ? $_REQUEST['path'] : 'temp';
        $func = !empty($_REQUEST['func']) ? $_REQUEST['func'] : 'undefined';
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //1中文，2英语
        $info = array(
            'num' => $num,
            'title' => '',
            'fileList' => '',
            'size' => '4M',
            'type' => 'jpg,png,gif,jpeg',
            'input' => $input,
            'func' => empty($func) ? 'undefined' : $func,
        );
//        print_r($info);die;
        $this->assign('info', $info);
        $lang_id = $this->shorthand;
        $this->assign('lang_id', $lang_id);
        $this->display('goods/upload.html');
    }

    /**
     * 获取上传页面
     * @auth wanyan
     * @date 2017-08-07
     */
    public function uploadfy() {
        $title = 'banners';
        $fileName = $_FILES['file']['name'];
        $type = strtolower(substr(strrchr($fileName, '.'), 1)); //获取文件类型
        if (!in_array($type, array('jpg', 'png', 'jpeg', 'gif', 'JPG', 'PNG', 'JPEG', 'GIF', 'jpg!pm'))) {
            $state = 'ERROR' . "图片格式不正确！";
        }
        //var_dump($type);die;
        $savePath = "upload/images/userCenter/" . date("Ymd") . mt_rand(10, 100);
        // 判断文件夹是否存在否则创建
        if (!file_exists($savePath)) {
            @mkdir($savePath, 0777, true);
            @chmod($savePath, 0777);
            @exec("chmod 777 {$savePath}");
        }
        $filePath = $_FILES['file']['tmp_name']; //文件路径
        $url = $savePath . '/' . time() . '.' . $type;
        if (!is_uploaded_file($filePath)) {
            $state = 'ERROR' . "临时文件错误！";
        }
        //上传文件
        if (!move_uploaded_file($filePath, $url)) {
            $state = 'ERROR' . "上传出错！";
        } else {
            $state = 'SUCCESS';
        }
        $return_data['title'] = $title;
        $return_data['original'] = ''; // 这里好像没啥用 暂时注释起来
        $return_data['state'] = $state;
        $return_data['path'] = 'goods';
        $return_data['url'] = $url;
        echo json_encode($return_data);
    }

    /**
     * 获取上传页面
     * @auth wanyan
     * @date 2017-08-07
     */
    public function delUpload() {
        //加载语言包
        $this->load($this->shorthand, 'userCenter/userCenter');
        $a = $this->langData;
        $action = !empty($_REQUEST['action']) ? htmlspecialchars($_REQUEST['action']) : '';
        //$filename= I('filename');
        $filename = !empty($_REQUEST['filename']) ? htmlspecialchars($_REQUEST['filename']) : '';
        $filename = str_replace('../', '', $filename);
        $filename = trim($filename, '.');
        $filename = trim($filename, '/');
        if ($action == 'del' && !empty($filename) && file_exists($filename)) {
            $size = getimagesize($filename);
            $filetype = explode('/', $size['mime']);
            if ($filetype[0] != 'image') {
                exit;
            }
            if (unlink($filename)) {
                $this->setData(array(), $status = 1, $a['delete_Success']);
            } else {
                $this->setData(array(), $status = 0, $a['delete_fail']);
            }
            exit;
        }
    }

    //个人睿积分
    public function personPoint() {
        $pointLogMod = &m('pointLog');
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        //语言包
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;
        $this->assign('lang', $lang);
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;
        $this->assign('storeid', $storeid);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $startTime = !empty($_REQUEST['start_time']) ? htmlspecialchars(trim($_REQUEST['start_time'])) : '';
        $endTime = !empty($_REQUEST['end_time']) ? htmlspecialchars(trim($_REQUEST['end_time'])) : '';

        if (empty($startTime) && empty($endTime)) {
            $startTime = date('Y-m-d', strtotime('-7 days'));
            $endTime = date('Y-m-d');
        }
        if ($startTime && $endTime && ($startTime > $endTime)) {
            $temp = $startTime;
            $startTime = $endTime;
            $endTime = $temp;
        }
        $this->assign('startTime', $startTime);
        $this->assign('endTime', $endTime);

        //where条件
        $where = ' where 1=1 ';
        if ($this->lang == 29) {
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

        $uSql = "select point from " . DB_PREFIX . 'user where id=' . $this->userId;
        $uData = $this->userMod->querySql($uSql);
        $this->assign('point', $uData[0]['point']);

        //列表页数据
        $sql = ' select  *   from  ' . DB_PREFIX . 'point_log   ' . $where . '  order by id desc ';
        $data = $pointLogMod->querySqlPageData($sql, array("pre_page" => 10, "is_sql" => false, "mode" => 1));

        $list = $data['list'];
        $this->assign('list', $list);
        $this->assign('lang', $lang);
        $this->assign('page_html', $data['ph']);

        //赠送睿积分功能是否关闭
        $systemConsoleMod = &m('systemConsole');
        $console_info = $systemConsoleMod->getRow(1);
        $this->assign('console_status', $console_info['status']);

        $this->display("userCenter/personPoint.html");
    }

    //充值睿积分页面
    public function rechargeRui() {
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;
        $this->assign('lang', $lang);
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;
        $this->assign('storeid', $storeid);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $sql = "SELECT * FROM " . DB_PREFIX . 'user_point_site';
        $data = $this->pointMod->querySql($sql);
        $this->assign('res', $data[0]);
        $this->assign('userid', $this->userId);
        $this->display('userCenter/points.html');
    }

    /*
     * 赠送睿积分页面
     * @author lee
     * @date 2018-6-22 09:39:05
     */

    public function giveUserPoint() {
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;
        $this->assign('lang', $lang);
        $user_info = $this->userMod->getOne(array("cond" => "id=" . $this->userId));
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $this->assign('info', $user_info);
        $this->display('userCenter/givePoints.html');
    }

    /*
     * 处理赠送积分
     * @author lee
     * @date  2018-6-22 09:57:26
     */

    public function doGivePoint() {
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $a = $this->langData;
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->storeid;
        $name = !empty($_REQUEST['user_name']) ? trim($_REQUEST['user_name']) : '';
        $point = !empty($_REQUEST['point']) ? trim($_REQUEST['point']) : '';
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $sql = "select * from " . DB_PREFIX . "user where phone = '" . $name . "' or email = '" . $name . "'";
        $res = $this->userMod->querySql($sql);
        //$receive_info = $this->userMod->getOne(array("cond"=>"phone =".$name." or email=".$name));
        $receive_info = $res[0];
        $give_info = $this->userMod->getOne(array("cond" => "id =" . $this->userId));
        if (empty($name)) {
            $this->setData(array(), $status = 0, $a['no_giver']);
        }
        if ($receive_info['id'] == $this->userId) {
            $this->setData(array(), $status = 0, $a['no_power']);
        }
        if (empty($receive_info)) {
            $this->setData(array(), $status = 0, $a['no_give']);
        }
        if (!preg_match("/^[1-9][0-9]*$/", $point)) {
            $this->setData(array(), $status = 0, $a['rui_z']);
        }
        if ($point > $give_info['point']) {
            $this->setData(array(), $status = 0, $a['no_point']);
        }
        $give_point = $give_info['point'] - $point;
        $give_arr = array(
            "point" => $give_point
        );
        $receive_arr = array(
            "point" => $receive_info['point'] + $point
        );
        $res = $this->userMod->doEdit($give_info['id'], $give_arr);
        $this->addPointLog($give_info['phone'], "赠予" . $receive_info['username'] . " " . $point . "睿积分", $give_info['id'], 0, $point);
        if ($res) {
            $res2 = $this->userMod->doEdit($receive_info['id'], $receive_arr);
            $this->addPointLog($receive_info['phone'], $give_info['username'] . "赠予" . $point . "睿积分", $receive_info['id'], $point, 0);
        }
        if ($res && $res2) {
            $this->setData(array("url" => "?app=userCenter&act=personPoint&storeid={$store_id}&lang={$lang}&auxiliary={$auxiliary}"), $status = 1, $a['userinfo_Success']);
        } else {
            $this->setData(array(), $status = 0, $a['saveAdd_fail']);
        }
    }

    /**
     * 我的余额
     * @author zhangkx
     * @date 2019/7/3
     */
    public function myAmount() {
//        $amountLogMod = &m('amountLog');
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        //语言包
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;
        $this->assign('lang', $lang);
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;
        $this->assign('storeid', $storeid);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $startTime = !empty($_REQUEST['start_time']) ? htmlspecialchars(trim($_REQUEST['start_time'])) : '';
        $endTime = !empty($_REQUEST['end_time']) ? htmlspecialchars(trim($_REQUEST['end_time'])) : '';

        if (empty($startTime) && empty($endTime)) {
            $startTime = date('Y-m-d', strtotime('-7 days'));
            $endTime = date('Y-m-d');
        }
        if ($startTime && $endTime && ($startTime > $endTime)) {
            $temp = $startTime;
            $startTime = $endTime;
            $endTime = $temp;
        }
        $this->assign('startTime', $startTime);
        $this->assign('endTime', $endTime);

        //where条件
        $where = ' where 1=1 ';
        if ($this->lang == 29) {
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

        $uSql = "select amount from " . DB_PREFIX . 'user where id=' . $this->userId;
        $uData = $this->userMod->querySql($uSql);
        $this->assign('amount', $uData[0]['amount']);
        $amountLogMod = &m('amountLog');
        //列表页数据
        $sql = ' select  *   from  ' . DB_PREFIX . 'amount_log   ' . $where . '  order by id desc ';
        $data = $this->userMod->querySqlPageData($sql, array("pre_page" => 10, "is_sql" => false, "mode" => 1));
        foreach ($data['list'] as $key => &$value)  {
            $amount = $amountLogMod->getAmountData($value['id']);
            $value['recharge_rule'] = $amount['recharge_rule'];
            if (in_array($value['type'], array(2,4))) {
                $value['amount_type'] = 1;
            } else {
                $value['amount_type'] = 2;
            }
//            echo '<pre>';print_r($data);die;
        }
//        echo '<pre>';print_r($data);die;
        $list = $data['list'];
        $this->assign('list', $list);
        $this->assign('lang', $lang);
        $this->assign('page_html', $data['ph']);

        $this->display("userCenter/myAmount.html");
    }

}
