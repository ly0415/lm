<?php

/**
 * 商铺分润规则
 * @author wanyan
 * @date 2017-11-17
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class fxRulerApp extends BaseStoreApp {

    private $storeCateMod;
    private $storeMod;
    private $langId;
    private $fxRulerMod;
    private $land_id;
    private $fxStoreSettingMod;
    private $storeLangMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->storeCateMod = &m('storeCate');
        $this->storeMod = &m('store');
        $this->fxRulerMod = &m('fxrule');
        $this->fxStoreSettingMod = &m('fxStoreSetting');
        $this->storeLangMod = &m('areaStoreLang');
        $this->langId = $this->storeInfo['lang_id'];
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : 0;
    }

    /**
     * 商铺分润规则首页
     * @author wanyan
     * @date 2017-11-17
     */
    public function ruleList() {
        $land_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : $this->lang_id;
        $ruler_name = !empty($_REQUEST['ruler_name']) ? htmlspecialchars(trim($_REQUEST['ruler_name'])) : '';
        $store_id =  !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : $this->storeId;
        $where = " where `mark` =1 and (find_in_set($store_id,store_id) or store_id = 0 ) ";
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
            $storeInfo = $this->getStoreById($v['store_id']);
            $rs['list'][$k]['store_name'] = $storeInfo[0]['store_name'];
            $rs['list'][$k]['lev1_prop'] = $this->isXiaoShu($v['lev1_prop']);
            $rs['list'][$k]['lev2_prop'] = $this->isXiaoShu($v['lev2_prop']);
            $rs['list'][$k]['lev3_prop'] = $this->isXiaoShu($v['lev3_prop']);
            if (empty($rs['list'][$k]['store_name'])) {
                $rs['list'][$k]['store_name'] = '不限制';
            }
            $rs['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
        }
        $buttonAuth = $this->fxStoreSettingMod->getOne(array('cond' => " find_in_set($this->storeId,store_id)"));
        if($this->storeInfo['store_type'] == 1){
            //获取店铺名称
            $storeMod = &m('store');
            $store_data = $storeMod->getStoreArr($this->storeInfo['store_cate_id']);
            $this->assign('store_data', $store_data);
            $this->assign('store_all', "all");
        }
        $this->assign('store_id', $store_id);
        $this->assign('my_store', $this->storeId);
        $this->assign('p', $p);
        $this->assign('ba', $buttonAuth);
        $this->assign('list', $rs['list']);
        $this->assign('page_html', $rs['ph']);
        $this->assign('lang_id', $land_id);
        $this->assign('ruler_name', $ruler_name);
        $this->display('fxRuler/rulerList.html');
    }

    public function isXiaoShu($lev_prop) {
        $a = explode('.', $lev_prop);
        $b = "0." . $a[1];
        if ($b == '0.00') {
            return $a[0];
        } else {
            $c = strrev($lev_prop);
            if ($c[0] == "0") {
                $c = substr($c, 1);
                $c = strrev($c);
                return $c;
            }
            return $lev_prop;
        }
    }

    /**
     * 商铺分润规则添加
     * @author wanyan
     * @date 2017-11-20
     */
    public function getStoreById($id) {
//        $rs = $this->storeMod->getData(array('cond' => "`id` in ({$id})", 'fields' => "GROUP_CONCAT(store_name) as store_name"));
        $rs = $this->storeLangMod->getData(array('cond' => "`store_id` in ({$id}) and distinguish =0  and lang_id =" . $this->defaulLang, 'fields' => "GROUP_CONCAT(store_name) as store_name"));
        return $rs;
    }

    /**
     * 商铺分润规则添加
     * @author wanyan
     * @date 2017-11-17
     */
    public function add() {
        $land_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : 0;
        $this->assign('act', 'ruleList');
        $this->assign('lang_id', $land_id);
        $this->display('fxRuler/rulerAdd.html');
    }

    /**
     * 商铺分润规则添加
     * @author wanyan
     * @date 2017-11-17
     */
    public function doAdd() {
        $lang_id = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : 0;
        $this->load($lang_id, 'store/store');
        $a = $this->langData;
        $ruler_name = !empty($_REQUEST['ruler_name']) ? htmlspecialchars(trim($_REQUEST['ruler_name'])) : '';
        $lev1_prop = !empty($_REQUEST['lev1_prop']) ? $_REQUEST['lev1_prop'] : 0;
        $lev2_prop = !empty($_REQUEST['lev2_prop']) ? $_REQUEST['lev2_prop'] : 0;
        $lev3_prop = !empty($_REQUEST['lev3_prop']) ? $_REQUEST['lev3_prop'] : 0;
        if (empty($ruler_name)) {
            $this->setData($info = array(), $status = '0', $a['rule_name']);
        } else {
            $rs = $this->fxRulerMod->getOne(array('cond' => "`rule_name` = '{$ruler_name}' and  mark =1", 'fields' => '`id`'));
            if ($rs['id']) {
                $this->setData($info = array(), $status = '0', $a['rule_repeat']);
            }
        }
        if (!empty($ruler_name)) {
            if ((strlen($ruler_name) < 6) || (strlen($ruler_name) > 30)) {
                $this->setData($info = array(), $status = '0', $a['rule_length']);
            }
        }
//        if (empty($lev1_prop)) {
//            $this->setData($info = array(), $status = '0', $a['rule_Class_A']);
//        }
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $lev1_prop)) {
            $this->setData($info = array(), $status = '0', $a['rule_Class_A1']);
        }
        // 判断规则分润格式
//        if (empty($lev2_prop)) {
//            $this->setData($info = array(), $status = '0', $a['rule_Class_B']);
//        }
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $lev2_prop)) {
            $this->setData($info = array(), $status = '0', $a['rule_Class_B1']);
        }
        if (empty($lev3_prop)) {
            $this->setData($info = array(), $status = '0', $a['rule_Class_C']);
        }
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $lev3_prop)) {
            $this->setData($info = array(), $status = '0', $a['rule_Class_C1']);
        }
//        if (($lev1_prop < $lev2_prop) || ($lev1_prop < $lev3_prop) || ($lev2_prop < $lev3_prop)) {
//            $this->setData($info = array(), $status = '0', $a['rule_Decline']);
//        }
        /* 判断当前所属国家利率 */
        if ($lev3_prop < $this->fx_discount) {
            $this->setData($info = array(), $status = 0, $message = "{$a['rule_Maximum']}（{$this->fx_discount}）%");
        }
        // 当1,2级分销商为空,2,3级不为空！
        if (empty($lev1_prop) || empty($lev2_prop)) {
            $lev1 = $lev1_prop;
            $lev2 = $lev2_prop;
            $lev3 = $lev3_prop;
            // 判断store_cate && store_id 是空的，
            $res = $this->fxRulerMod->getOne(array('cond' => "`lev1_prop` = '{$lev1}' and `lev2_prop` = '{$lev2}' and `lev3_prop` = '{$lev3}' and store_id = 0 and mark =1 ", 'fields' => 'id'));
            if ($res['id']) {
                $this->setData($info = array(), $status = '0', $a['rule_Existence']);
            }
            $rs = $this->fxRulerMod->getOne(array('cond' => "`lev1_prop` = '{$lev1}' and `lev2_prop` = '{$lev2}' and `lev3_prop` = '{$lev3}' and store_id = $this->storeId and mark =1", 'fields' => 'id'));
            if ($rs['id']) {
                $this->setData($info = array(), $status = '0', $a['rule_Existence']);
            }
        }
        if (!empty($lev1_prop) && !empty($lev2_prop) && !empty($lev3_prop)) {
            $lev1 = $lev1_prop;
            $lev2 = $lev2_prop;
            $lev3 = $lev3_prop;
//            if($lev3 > $lev2 || $lev3 >  $lev1 || $lev2 > $lev1){
//                $this->setData($info=array(),$status='0',$a['reduce']);
//            }
            // 判断store_cate && store_id 是空的，
            $res = $this->fxRulerMod->getOne(array('cond' => "`lev1_prop` = '{$lev1}' and `lev2_prop` = '{$lev2}' and `lev3_prop` = '{$lev3}' and store_id = 0 and mark =1 ", 'fields' => 'id'));
            if ($res['id']) {
                $this->setData($info = array(), $status = '0', $a['rule_Existence']);
            }
            $rs = $this->fxRulerMod->getOne(array('cond' => "`lev1_prop` = '{$lev1}' and `lev2_prop` = '{$lev2}' and `lev3_prop` = '{$lev3}' and store_id = $this->storeId and mark =1", 'fields' => 'id'));
            if ($rs['id']) {
                $this->setData($info = array(), $status = '0', $a['rule_Existence']);
            }
        }
        $insert_data = array(
            'rule_name' => $ruler_name,
            'lev1_prop' => ($lev1_prop),
            'lev2_prop' => ($lev2_prop),
            'lev3_prop' => ($lev3_prop),
            'store_id' => $this->storeId,
            'store_cate' => $this->country_id,
            'add_time' => time()
        );
        $rs = $this->fxRulerMod->doInsert($insert_data);
        if ($rs) {
            $info['url'] = "?app=fxRuler&act=ruleList&lang_id={$lang_id}";
            $this->setData($info, $status = '1', $a['rule_Success']);
        } else {
            $this->setData($info = array(), $status = '0', $a['rule_fail']);
        }
    }

    /**
     * 商铺分润规则编辑
     * @author wanyan
     * @date 2017-11-17
     */
    public function edit() {
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : '';
        $lang_id = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : 0;
        $rs = $this->fxRulerMod->getOne(array('cond' => "`id`='{$id}'"));
        $this->assign('rs', $rs);
        $this->assign('p', $p);
        $this->assign('act', 'ruleList');
        $this->assign('lang_id', $lang_id);
        $this->display('fxRuler/rulerEdit.html');
    }

    /**
     * 商铺分润规则编辑功能
     * @author wanyan
     * @date 2017-11-17
     */
    public function doEdit() {
        $lang_id = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : 0;
        $this->load($lang_id, 'store/store');
        $a = $this->langData;
        $id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : '';
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $ruler_name = !empty($_REQUEST['ruler_name']) ? htmlspecialchars(trim($_REQUEST['ruler_name'])) : '';
        $lev1_prop = !empty($_REQUEST['lev1_prop']) ? $_REQUEST['lev1_prop'] : 0;
        $lev2_prop = !empty($_REQUEST['lev2_prop']) ? $_REQUEST['lev2_prop'] : 0;
        $lev3_prop = !empty($_REQUEST['lev3_prop']) ? $_REQUEST['lev3_prop'] : 0;
        if (empty($ruler_name)) {
            $this->setData($info = array(), $status = '0', $a['rule_name']);
        } else {
            $rs = $this->fxRulerMod->getOne(array('cond' => "`rule_name` = '{$ruler_name}' and `id` != '{$id}' and mark =1", 'fields' => '`id`'));
            if ($rs['id']) {
                $this->setData($info = array(), $status = '0', $a['rule_repeat']);
            }
        }
        if (!empty($ruler_name)) {
            if ((strlen($ruler_name) < 6) || (strlen($ruler_name) > 30)) {
                $this->setData($info = array(), $status = '0', $a['rule_length']);
            }
        }
//        if (empty($lev1_prop)) {
//            $this->setData($info = array(), $status = '0', $a['rule_Class_A']);
//        }
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $lev1_prop)) {
            $this->setData($info = array(), $status = '0', $a['rule_Class_A1']);
        }
//        // 判断规则分润格式
//        if (empty($lev2_prop)) {
//            $this->setData($info = array(), $status = '0', $a['rule_Class_B']);
//        }
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $lev2_prop)) {
            $this->setData($info = array(), $status = '0', $a['rule_Class_B1']);
        }
        if (empty($lev3_prop)) {
            $this->setData($info = array(), $status = '0', $a['rule_Class_C']);
        }
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $lev3_prop)) {
            $this->setData($info = array(), $status = '0', $a['rule_Class_C1']);
        }
//        if (($lev1_prop < $lev2_prop) || ($lev1_prop < $lev3_prop) || ($lev2_prop < $lev3_prop)) {
//            $this->setData($info = array(), $status = '0', $a['rule_Decline']);
//        }
        /* 判断当前所属国家利率 */
        if ($lev3_prop < $this->fx_discount) {
            $this->setData($info = array(), $status = 0, $message = "{$a['rule_Maximum']}（{$this->fx_discount}）%");
        }
        if (empty($lev1_prop) || empty($lev2_prop)) {
            $lev1 = $lev1_prop;
            $lev2 = $lev2_prop;
            $lev3 = $lev3_prop;
            // 判断store_cate && store_id 是空的，
            $res = $this->fxRulerMod->getOne(array('cond' => "`lev1_prop` = '{$lev1}' and `lev2_prop` = '{$lev2}' and `lev3_prop` = '{$lev3}' and store_id = 0 and mark =1", 'fields' => 'id'));
            if ($res['id']) {
                $this->setData($info = array(), $status = '0', $a['rule_Existence']);
            }
            $rs = $this->fxRulerMod->getOne(array('cond' => "`lev1_prop` = '{$lev1}' and `lev2_prop` = '{$lev2}' and `lev3_prop` = '{$lev3}' and store_id = $this->storeId and  `id` <> '{$id}' and mark =1", 'fields' => 'id'));
            if ($rs['id']) {
                $this->setData($info = array(), $status = '0', $a['rule_Existence']);
            }
        }
        if (!empty($lev1_prop) && !empty($lev2_prop) && !empty($lev3_prop)) {
            $lev1 = $lev1_prop;
            $lev2 = $lev2_prop;
            $lev3 = $lev3_prop;
//            if($lev3 > $lev2 || $lev3 >  $lev1 || $lev2 > $lev1){
//                $this->setData($info=array(),$status='0',$a['reduce']);
//            }
            // 判断store_cate && store_id 是空的，
            $res = $this->fxRulerMod->getOne(array('cond' => "`lev1_prop` = '{$lev1}' and `lev2_prop` = '{$lev2}' and `lev3_prop` = '{$lev3}' and store_id = 0 and mark =1", 'fields' => 'id'));
            if ($res['id']) {
                $this->setData($info = array(), $status = '0', $a['rule_Existence']);
            }
            $rs = $this->fxRulerMod->getOne(array('cond' => "`lev1_prop` = '{$lev1}' and `lev2_prop` = '{$lev2}' and `lev3_prop` = '{$lev3}' and store_id = $this->storeId and  `id` <> '{$id}' and mark =1", 'fields' => 'id'));
            if ($rs['id']) {
                $this->setData($info = array(), $status = '0', $a['rule_Existence']);
            }
        }
        $insert_data = array(
            'rule_name' => $ruler_name,
            'lev1_prop' => ($lev1_prop),
            'lev2_prop' => ($lev2_prop),
            'lev3_prop' => ($lev3_prop),
            'store_id' => $this->storeId,
            'store_cate' => $this->country_id,
        );
        $rs = $this->fxRulerMod->doEdit($id, $insert_data);
        if ($rs) {
            $info['url'] = "?app=fxRuler&act=ruleList&lang_id={$lang_id}&p={$p}";
            $this->setData($info, $status = '1', $a['Update_success']);
        } else {
            $this->setData($info = array(), $status = '0', $a['Update_fail']);
        }
    }

    /**
     * 商铺分润规则编辑功能
     * @author wanyan
     * @date 2017-11-17
     */
    public function dele() {
        $lang_id = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : 0;
        $this->load($lang_id, 'store/store');
        $a = $this->langData;
        $id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : '';
        $rs = $this->fxRulerMod->doMark($id);
        if ($rs) {
            $this->setData($info = array(), $status = '1', $a['Delete_success']);
        } else {
            $this->setData($info = array(), $status = '0', $a['Delete_fail']);
        }
    }

}
