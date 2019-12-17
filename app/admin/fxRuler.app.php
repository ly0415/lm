<?php

/**
 * 商铺分润规则
 * @author wanyan
 * @date 2017-11-17
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class fxRulerApp extends BackendApp {

    private $storeCateMod;
    private $storeMod;
    private $storeLangMod;
    private $fxRulerMod;
    public $lang_id;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->storeCateMod = &m('storeCate');
        $this->storeLangMod = &m('areaStoreLang');
        $this->storeMod = &m('store');
        $this->fxRulerMod = &m('fxrule');
    }

    /**
     * 商铺分润规则首页
     * @author wanyan
     * @date 2017-11-17
     */
    public function ruleList() {
        $lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : 0;
        $this->load($lang_id, 'admin/admin');
        $a = $this->langData;
        $ruler_name = !empty($_REQUEST['ruler_name']) ? htmlspecialchars(trim($_REQUEST['ruler_name'])) : '';
        $where = " where `mark` =1";
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
                $rs['list'][$k]['store_name'] = $a['fenx_Unrestricted'];
            }
            if ($v['add_time']) {
                $rs['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            } else {
                $rs['list'][$k]['add_time'] = '';
            }
            $rs['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
//            $rs['list'][$k]['sort'] = $total - $k - 20 * ($p - 1); //倒叙
        }
        $this->assign('p', $p);
        $this->assign('list', $rs['list']);
        $this->assign('lang_id', $lang_id);
        $this->assign('page_html', $rs['ph']);
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
        $rs = $this->storeLangMod->getData(array('cond' => "`store_id` in ({$id})  and distinguish =0  and lang_id =" . $this->lang_id, 'fields' => "GROUP_CONCAT(store_name) as store_name"));
        return $rs;
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
        $this->display('fxRuler/rulerAdd.html');
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
    public function doAdd() {
          $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $ruler_name = !empty($_REQUEST['ruler_name']) ? htmlspecialchars(trim($_REQUEST['ruler_name'])) : '';
        $lev1_prop = !empty($_REQUEST['lev1_prop']) ? $_REQUEST['lev1_prop'] : 0;
        $lev2_prop = !empty($_REQUEST['lev2_prop']) ? $_REQUEST['lev2_prop'] : 0;
        $lev3_prop = !empty($_REQUEST['lev3_prop']) ? $_REQUEST['lev3_prop'] : 0;
        $store_cate = !empty($_REQUEST['store_cate']) ? $_REQUEST['store_cate'] : '';
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : 0;
        if (empty($ruler_name)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->rule_required);
        } else {
            $rs = $this->fxRulerMod->getOne(array('cond' => "`rule_name` = '{$ruler_name}' and mark =1", 'fields' => '`id`'));
            if ($rs['id']) {
                $this->setData($info = array(), $status = '0', $this->langDataBank->project->rule_repeat);
            }
        }
        if (strlen($ruler_name) > 30 || strlen($ruler_name) < 6) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->rule_length);
        }
//        if (empty($lev1_prop)) {
//            $this->setData($info = array(), $status = '0', $a['fenx_Class_A']);
//        }
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $lev1_prop)) {
            $this->setData($info = array(), $status = '0',$this->langDataBank->project->one_rule_format);
        }
//        // 判断规则分润格式
//        if (empty($lev2_prop)) {
//            $this->setData($info = array(), $status = '0', $a['fenx_Class_B']);
//        }
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $lev2_prop)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->two_rule_format);
        }
        if (empty($lev3_prop)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->three_rule_required);
        }
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $lev3_prop)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->three_rule_format);
        }
        if (!empty($store_cate) && empty($store_id)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->store_required);
        }
        if (empty($lev1_prop) || empty($lev2_prop)) {
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
            $info['url'] = "?app=fxRuler&act=ruleList&p={$p}";
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
    public function edit() {
        $id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : '';
        $lang_id = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : 0;
        $rs = $this->fxRulerMod->getOne(array('cond' => "`id`='{$id}'"));
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $this->assign('p', $p);
        $this->assign('rs', $rs);
        $this->assign('act', 'ruleList');
        $this->assign('lang_id', $lang_id);
        $storeCateList = $this->allCountry;
        $this->assign('storeCate', $storeCateList);
//        $storeInfo = $this->storeMod->getData(array('cond' => "`store_cate_id` = '{$rs['store_cate']}' and `is_open` =1", 'fields' => "id,store_name"));
        $sql = 'SELECT  c.id,l.store_name  FROM  ' . DB_PREFIX . 'store  as c
                 left join  ' . DB_PREFIX . 'store_lang as l on  c.id = l.store_id
                 WHERE c.is_open = 1  and l.distinguish =0  and  l.lang_id =' . $this->lang_id . '  and c.store_cate_id=' . $rs['store_cate'];
        $storeInfo = $this->storeMod->querySql($sql);
        $this->assign('storeCateInfo', $storeInfo);
        $stores = explode(',', $rs['store_id']);
        $this->assign('storeInfo', $stores);
//        var_dump($stores);die;
        $this->display('fxRuler/rulerEdit.html');
    }

    /**
     * 商铺分润规则编辑功能
     * @author wanyan
     * @date 2017-11-17
     */
    public function doEdit() {
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
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->rule_required);
        } else {
            $rs = $this->fxRulerMod->getOne(array('cond' => "`rule_name` = '{$ruler_name}' and `id` != '{$id}' and mark =1", 'fields' => '`id`'));
            if ($rs['id']) {
                $this->setData($info = array(), $status = '0', $this->langDataBank->project->rule_repeat);
            }
        }
        if (strlen($ruler_name) > 30 || strlen($ruler_name) < 6) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->rule_length);
        }
//        if (empty($lev1_prop)) {
//            $this->setData($info = array(), $status = '0', $a['fenx_Class_A']);
//        }
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $lev1_prop)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->one_rule_format);
        }
//        // 判断规则分润格式
//        if (empty($lev2_prop)) {
//            $this->setData($info = array(), $status = '0', $a['fenx_Class_B']);
//        }
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $lev2_prop)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->two_rule_format);
        }
        if (empty($lev3_prop)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->three_rule_required);
        }
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $lev3_prop)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->three_rule_format);
        }
        if (empty($lev1_prop) || empty($lev2_prop)) {
            $lev1 = $lev1_prop;
            $lev2 = $lev2_prop;
            $lev3 = $lev3_prop;
//            if ($lev3 > $lev2 || $lev3 > $lev1 || $lev2 > $lev1) {
//                $this->setData($info = array(), $status = '0', $a['fenx_Decline']);
//            }
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
        }
        if (!empty($lev1_prop) && !empty($lev2_prop) && !empty($lev3_prop)) {
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
        );
        $rs = $this->fxRulerMod->doEdit($id, $insert_data);
        if ($rs) {
            $info['url'] = "?app=fxRuler&act=ruleList&p={$p}";
            $this->setData($info, $status = '1', $this->langDataBank->public->edit_success);
        } else {
            $this->setData($info = array(), $status = '0', $this->langDataBank->public->edit_fail);
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
            $this->setData($info = array(), $status = '1', $this->langDataBank->public->drop_success);
        } else {
            $this->setData($info = array(), $status = '0',  $this->langDataBank->public->drop_fail);
        }
    }

}
