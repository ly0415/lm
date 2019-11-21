<?php

/**
 * 商铺分润按钮设置
 * @author wanyan
 * @date 2017-11-20
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class fxStoreSettingApp extends BackendApp {

    private $storeCateMod;
    private $storeMod;
    private $storeLangMod;
    private $langId;
    private $fxRulerMod;
    public $lang_id;
    public $fxStoreSettingMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->storeCateMod = &m('storeCate');
        $this->storeMod = &m('store');
        $this->storeLangMod = &m('areaStoreLang');
        $this->fxRulerMod = &m('fxrule');
        $this->langId = $this->lang_id;
        $this->fxStoreSettingMod = &m('fxStoreSetting');
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : 0;
    }

    /**
     * 商铺分润按钮设置页面
     * @author wanyan
     * @date 2017-11-20
     */
    public function settingList() {
        $this->load($this->lang_id, 'admin/admin');
        $a = $this->langData;
        $lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "fxstore_setting ";
        $totalCount = $this->fxStoreSettingMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;

        $sql = "select * from " . DB_PREFIX . "fxstore_setting order by `id` desc";
        $rs = $this->fxStoreSettingMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        foreach ($rs['list'] as $k => $v) {
            $rs['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            $storeInfo = $this->getStoreById($v['store_id']);
            $rs['list'][$k]['store_name'] = $storeInfo[0]['store_name'];
            $rs['list'][$k]['country_name'] = $this->getCountryName($v['store_cate'], $this->langId);
            if ($v['addButton'] == 1) {
                $rs['list'][$k]['add_name'] = $a['fenx_yes'];
            } else {
                $rs['list'][$k]['add_name'] = $a['fenx_no'];
            }
            if ($v['editButton'] == 1) {
                $rs['list'][$k]['edit_name'] = $a['fenx_yes'];
            } else {
                $rs['list'][$k]['edit_name'] = $a['fenx_no'];
            }
            if ($v['delButton'] == 1) {
                $rs['list'][$k]['del_name'] = $a['fenx_yes'];
            } else {
                $rs['list'][$k]['del_name'] = $a['fenx_no'];
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
        $this->assign('page_html', $rs['ph']);
        $this->assign('lang_id', $lang_id);
        $this->display('fxStoreSetting/settingList.html');
    }

    /**
     * 商铺分润规则添加
     * @author wanyan
     * @date 2017-11-20
     */
    public function getStoreById($id) {
        $rs = $this->storeLangMod->getData(array('cond' => "`store_id` in ({$id}) and distinguish = 0 and lang_id =" . $this->lang_id, 'fields' => "GROUP_CONCAT(store_name) as store_name"));
        return $rs;
    }

    /**
     * 商铺分润按钮设置编辑
     * @author wanyan
     * @date 2017-11-20
     */
    public function getCountryName($store_cate, $lang_id) {
        $sql = "select scl.cate_name from " . DB_PREFIX . "store_cate as sc left join " . DB_PREFIX . "store_cate_lang as scl 
        on sc.id = scl.cate_id where sc.id = '{$store_cate}' and scl.lang_id = '{$lang_id}'";
        $rs = $this->storeCateMod->querySql($sql);
        return $rs[0]['cate_name'];
    }

    /**
     * 商铺分润按钮设置添加
     * @author wanyan
     * @date 2017-11-20
     */
    public function add() {
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $this->assign('p', $p);
        $lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
        $this->assign('act', 'settingList');
        $this->assign('lang_id', $this->landid);
        $storeCateList = $this->allCountry;
        $this->assign('storeCate', $storeCateList);
        $this->assign('lang_id', $lang_id);
        $this->display('fxStoreSetting/settingAdd.html');
    }

    /**
     * 商铺分润按钮设置添加
     * @author wanyan
     * @date 2017-11-20
     */
    public function doAdd() {
        $this->load($this->lang_id, 'admin/admin');
        $a = $this->langData;
        $store_cate = !empty($_REQUEST['store_cate']) ? intval($_REQUEST['store_cate']) : 0;
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : '';
        $addButton = !empty($_REQUEST['add']) ? intval($_REQUEST['add']) : 0;
        $editButton = !empty($_REQUEST['edit']) ? intval($_REQUEST['edit']) : 0;
        $delButton = !empty($_REQUEST['del']) ? intval($_REQUEST['del']) : 0;
        $lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : 0;
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        if (empty($store_cate) || empty($store_id)) {
            $this->setData($info = array(), $status = '0', $a['fenx_Country']);
        }
        $store_ids = implode(',', $store_id);
        if (!empty($store_ids)) {
            $rs = $this->fxStoreSettingMod->getOne(array('cond' => "`store_id` in ({$store_ids})"));
            if ($rs) {
                $this->setData($info = array(), $status = '0', $a['fenx_shop']);
            }
        }
        $insert_data = array(
            'store_id' => $store_ids,
            'store_cate' => $store_cate,
            'addButton' => $addButton,
            'editButton' => $editButton,
            'delButton' => $delButton,
            'add_time' => time()
        );
        $rs = $this->fxStoreSettingMod->doInsert($insert_data);
        if ($rs) {
            $info['url'] = "?app=fxStoreSetting&act=settingList&lang_id=" . $lang_id . '&p=' . $p;
            $this->setData($info, $status = '1', $a['add_Success']);
        } else {
            $this->setData($info = array(), $status = '0', $a['add_fail']);
        }
    }

    /**
     * 商铺分润按钮设置编辑
     * @author wanyan
     * @date 2017-11-20
     */
    public function edit() {
        $lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
        $id = !empty($_REQUEST['id']) ? (int) $_REQUEST['id'] : '';
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $rs = $this->fxStoreSettingMod->getOne(array('cond' => "`id`='{$id}'", 'fields' => '*'));
        $rs['store_id'] = explode(',', $rs['store_id']);
        $this->assign('rs', $rs);
        $this->assign('act', 'settingList');
        $this->assign('lang_id', $lang_id);
        $storeCateList = $this->allCountry;
        $this->assign('storeCate', $storeCateList);
        $sql = 'SELECT  c.id,l.store_name  FROM  ' . DB_PREFIX . 'store  as c
                 left join  ' . DB_PREFIX . 'store_lang as l on  c.id = l.store_id
                 WHERE c.is_open = 1  and l.distinguish = 0 and  l.lang_id =' . $this->lang_id . '  and c.store_cate_id=' . $rs['store_cate'];
        $storeList = $this->storeMod->querySql($sql);
//        $storeList = $this->storeMod->getData(array('cond' => "`store_cate_id` = '{$rs['store_cate']}' and is_open=1", 'fields' => "id,store_name"));
        $this->assign('storeInfo', $storeList);
        $this->assign('p', $p);
        $this->display('fxStoreSetting/settingEdit.html');
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
                 WHERE c.is_open = 1 and l.distinguish = 0  and  l.lang_id =' . $this->lang_id . '  and c.store_cate_id=' . $id;
        $rs = $this->storeMod->querySql($sql);
//        $rs = $this->storeMod->getData(array('cond' => "`store_cate_id` = '{$id}' and `is_open` =1 ", 'fields' => "`id`,store_name,store_cate_id"));
        if (!empty($t_id)) {
            $ruleInfo = $this->fxStoreSettingMod->getOne(array('cond' => "`id` = '{$t_id}'", 'fields' => "store_id,store_cate"));
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
     * 商铺分润按钮设置编辑
     * @author wanyan
     * @date 2017-11-20
     */
    public function doEdit() {
        $this->load($this->lang_id, 'admin/admin');
        $a = $this->langData;
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
        $store_cate = !empty($_REQUEST['store_cate']) ? intval($_REQUEST['store_cate']) : 0;
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : '';
        $addButton = !empty($_REQUEST['add']) ? intval($_REQUEST['add']) : 0;
        $editButton = !empty($_REQUEST['edit']) ? intval($_REQUEST['edit']) : 0;
        $delButton = !empty($_REQUEST['del']) ? intval($_REQUEST['del']) : 0;
        $lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : 0;
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        if (empty($store_cate) || empty($store_id)) {
            $this->setData($info = array(), $status = '0', $a['fenx_Country']);
        }
        $store_ids = implode(',', $store_id);
        if (!empty($store_ids)) {
            $rs = $this->fxStoreSettingMod->getOne(array('cond' => "`store_id` in ({$store_ids}) and `id` != '{$id}' "));
            if ($rs) {
                $this->setData($info = array(), $status = '0', $a['fenx_shop']);
            }
        }
        $insert_data = array(
            'store_id' => $store_ids,
            'store_cate' => $store_cate,
            'addButton' => $addButton,
            'editButton' => $editButton,
            'delButton' => $delButton,
        );
        $rs = $this->fxStoreSettingMod->doEdit($id, $insert_data);
        if ($rs) {
            $info['url'] = "?app=fxStoreSetting&act=settingList&lang_id={$lang_id}&p={$p}";
            $this->setData($info, $status = '1', $a['edit_Success']);
        } else {
            $this->setData($info = array(), $status = '0', $a['edit_fail']);
        }
    }

    /**
     * 商铺分润按钮删除
     * @author wanyan
     * @date 2017-11-20
     */
    public function dele() {
        $lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : 0;
        $this->load($lang_id, 'admin/admin');
        $a = $this->langData;
        $id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : '';
        $rs = $this->fxStoreSettingMod->doDropEs($id);
        if ($rs) {
            $this->setData($info = array(), $status = '1', $a['delete_Success']);
        } else {
            $this->setData($info = array(), $status = '0', $a['delete_fail']);
        }
    }

}
