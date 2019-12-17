<?php

/**
 * 区域分类管理模块
 * @author wanyan
 * @date 2017-08-29
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class StoreCateApp extends BackendApp {

    private $storeCateMod;
    private $storeMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->storeCateMod = &m('storeCate');
        $this->storeMod = &m('store');
    }

    /**
     * 区域分类首页
     * @author wanyan
     * @date 2017-08-29
     */
    public function index() {
        $cate_name = !empty($_REQUEST['cate_name']) ? htmlspecialchars(trim($_REQUEST['cate_name'])) : '';
        $cate_names = !empty($_REQUEST['cate_names']) ? htmlspecialchars(trim($_REQUEST['cate_names'])) : '';
        $where = ' where 1=1  and  sl.lang_id =' . $this->lang_id;
        //搜索
        if ($this->lang_id == 1) {
            if (!empty($cate_names)) {
                $where .= '   and  sl.`cate_name`  like  "%' . $cate_names . '%"';
            }
        } else {
            if (!empty($cate_name)) {
                $where .= " and  sl.`cate_name` like '%" . $cate_name . "%'";
            }
        }
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "store_cate ";
        $totalCount = $this->storeCateMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $where .= " order by  s.id  desc  ";
        $sql = "SELECT  s.*,l.`name` AS lname ,c.`name` AS cname,sl.cate_name  as  lcatename   FROM  " . DB_PREFIX . "store_cate AS s
                LEFT JOIN  " . DB_PREFIX . "language AS l ON s.`lang_id` = l.`id`
                LEFT JOIN  " . DB_PREFIX . "currency AS c ON s.`currency_id` = c.`id`  left join  bs_store_cate_lang as sl on s.id = sl.cate_id  " . $where;

        $rs = $this->storeCateMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        foreach ($rs['list'] as $k => $v) {
            $rs['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            $sql_store = "select count(*) as total_count from " . DB_PREFIX . "store where `store_cate_id` = '{$v['id']}'";
            $sc = $this->storeCateMod->querySql($sql_store);
            $rs['list'][$k]['station_count'] = $sc[0]['total_count'] ? $sc[0]['total_count'] : '0';
            if ($v['add_time']) {
                $rs['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            } else {
                $rs['list'][$k]['add_time'] = '';
            }
            $rs['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
//            $rs['list'][$k]['sort'] = $total - $k - 20 * ($p - 1); //倒叙
        }
        $this->assign('cate_name', $cate_name);
        $this->assign('cate_names', $cate_names);
        $this->assign('list', $rs['list']);
        $this->assign('page', $rs['ph']);
        $this->display('storeCate/index.html');
    }

    /**
     * 区域新增页面
     * @author wanyan
     * @date 2017-08-29
     */
    public function add() {
        $langMod = &m('language');
        $currencyMod = &m('currency');

        $langInfo = $langMod->getLanguage();
        $currencyInfo = $currencyMod->getCurrency();

        $this->assign('langInfo', $langInfo);
        $this->assign('currencyInfo', $currencyInfo);
        $this->display('storeCate/add.html');
    }

    /**
     * 区域添加页面
     * @author wanyan
     * @date 2017-08-29
     */
    public function doAdd() {
        $name = !empty($_REQUEST['name']) ? $_REQUEST['name'] : array();
//        $cate_name = !empty($_REQUEST['cate_name']) ? htmlspecialchars(trim($_REQUEST['cate_name'])) : '';
//        $english_name = $_REQUEST['english_name'] ? htmlspecialchars(trim($_REQUEST['english_name'])) : '';

        $cate_code = !empty($_REQUEST['cate_code']) ? htmlspecialchars(trim($_REQUEST['cate_code'])) : '';  //分类编码

        $fx_discount = !empty($_REQUEST['fx_discount']) ? htmlspecialchars(trim($_REQUEST['fx_discount'])) : 0; //三级分销优惠

        $language_id = !empty($_REQUEST['language_id']) ? $_REQUEST['language_id'] : '';
        $currency_id = !empty($_REQUEST['currency_id']) ? $_REQUEST['currency_id'] : '';


//        if (empty($cate_name)) {
//            $this->setData($info = array(), $status = '0', $a['cate_cate']);
//        } else {
//            $query = array(
//                'cond' => "`cate_name`='{$cate_name}'",
//                'fields' => 'cate_name'
//            );
//            $rs = $this->storeCateMod->getOne($query);
//            if ($rs['cate_name']) {
//                $this->setData($info = array(), $status = '0', $a['cate_ch_name']);
//            }
//        }
//        if (empty($english_name)) {
//            $this->setData(array(), '0', $a['cate_en_name']);
//        }
        //

        foreach ($name as $key => $val) {
            $name[$key] = trim($val);
        }

        if (count(array_filter($name)) == 0) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->country_required);
        }
        $nameSpec = array_filter($name);
        if (empty($nameSpec[$this->lang_id])) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->cn_site_required);
        }
        foreach ($name as $val) {
//            if (empty($val)) {
//                $this->setData($info = array(), $status = '0', '站点国家名称必填');
//                break;
//            } else {
            $sql = 'select id  from  ' . DB_PREFIX . 'store_cate_lang where cate_name = ' . $val;
            $aff = $this->storeCateMod->querySql($sql);
            if (!empty($aff)) {
                $this->setData($info = array(), $status = '0', $this->langDataBank->project->country_repeat);
                break;
            }
//            }
        }
        //
        if (empty($fx_discount)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->three_discount_required);
        }

        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $fx_discount)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->three_illegal);
        }

        //
        if (empty($language_id)) {
            $this->setData(array(), '0', $this->langDataBank->project->lang_required);
        }

        if (empty($currency_id)) {
            $this->setData(array(), '0', $this->langDataBank->project->currency_required);
        }

//        if (mb_strlen($english_name) > 30) {
//            $this->setData(array(), '0', $a['cate_en_names']);
//        }
        if (empty($cate_code)) {
            $this->setData($info = array(), $status = '0',$this->langDataBank->project->classify_code_required);
        } else {
            $query = array(
                'cond' => "`cate_code`='{$cate_code}'",
                'fields' => 'cate_code'
            );
            $rs = $this->storeCateMod->getOne($query);
            if ($rs['cate_code']) {
                $this->setData($info = array(), $status = '0', $this->langDataBank->project->code_repeat);
            }
        }
//        if (empty($cate_sort)) {
//            $this->setData($info = array(), $status = '0', $a['cate_sort']);
//        }
//        if (!preg_match('/^[1-9][0-9]{0,2}$/', $cate_sort)) {
//            $this->setData($info = array(), $status = '0', $a['cate_whole']);
//        }
        // 统计该分类的站点数
//        $sql = "select count(*) from ".DB_PREFIX."store where `store_cate_id` = ".$

        $station_count = 0; // 虚拟设置区域站点数
        $data = array(
//            'cate_name' => $cate_name,
//            'english_name' => $english_name,
            'cate_code' => $cate_code,
            'fx_discount' => $fx_discount,
            'station_count' => $station_count,
            'is_open' => 1,
            'lang_id' => $language_id,
            'currency_id' => $currency_id,
            'add_time' => time()
        );
        $insert_id = $this->storeCateMod->doInsert($data);
        if (!empty($insert_id)) {
            //添加多语言版本信息
            $this->doLangData(array_filter($name), $insert_id);
            //
            $info['url'] = "?app=storeCate&act=index";
            $this->setData($info = array(), $status = '1', $this->langDataBank->public->add_success);
        } else {
            $this->setData($info = array(), $status = '0', $this->langDataBank->public->add_error);
        }
    }

    /**
     * 获取分类信息
     * @author wanyan
     * @date 2017-8-1
     */
    public function getOneInfo($name, $id = 0) {
        $scLangMod = &m('storeCateLang');
        $where = '  where 1=1';
        if ($id == 0) {
            //添加
            $where .= '  and  cate_name = "' . $name . '"';
        } else {
            //编辑
            $where .= '  and   id!=' . $id . '   and  cate_name = "' . $name . '"';
        }
        $sql = 'select id  from  ' . DB_PREFIX . 'store_cate_lang' . $where;
        var_dump($sql);
        $res = $scLangMod->querySql($sql);
        return $res;
    }

    /**
     * 添加多语言版本信息
     */
    public function doLangData($name, $insert_id) {
        $scLangMod = &m('storeCateLang');
        $data = array();
        foreach ($name as $key => $val) {
            $data[] = array(
                'lang_id' => $key,
                'cate_name' => $val,
                'cate_id' => $insert_id,
            );
        }
        // 循环插入数据
        foreach ($data as $v) {
            $res = $scLangMod->doInsert($v);
            if ($res) {
                continue;
            } else {
                return false;
                break;
            }
        }
        return true;
    }

    /**
     * 区域编辑页面
     * @author wanyan
     * @date 2017-08-29
     */
    public function edit() {
        $langMod = &m('language');
        $currencyMod = &m('currency');

        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '';
        $query = array(
            'cond' => "`id`='{$id}'"
        );
        $rs = $this->storeCateMod->getOne($query);
        //多语言版本
        $scLangMod = &m('storeCateLang');
        $sql = 'select   *   from   ' . DB_PREFIX . 'store_cate_lang  where  cate_id =' . $id;
        $langD = $scLangMod->querySql($sql);
        $totalCount = count($langD);
        $this->assign('langD', $langD);
        $this->assign('count', intval($totalCount) - 1);
        //
        $langInfo = $langMod->getLanguage();
        $currencyInfo = $currencyMod->getCurrency();
        //

        $this->assign('extralInfo', $this->getOneArray($langInfo, $langD));
        $this->assign('langInfo', $langInfo);
        $this->assign('currencyInfo', $currencyInfo);
        $this->assign('data', $rs);
        $this->display('storeCate/edit.html');
    }

    /**
     * 区域编辑页面
     * @author wanyan
     * @date 2017-08-29
     */
    public function getOneArray($langInfo2, $langD2) {
        $langInfo1 = $this->getOneArray2($langInfo2, 'id');
        $langD1 = $this->getOneArray2($langD2, 'lang_id');
        foreach ($langInfo1 as $k => $v) {
            if (!in_array($v, $langD1)) {
                $tree1[] = $v;
            }
        }
        foreach ($langInfo2 as $k2 => $v2) {
            foreach ($tree1 as $k3 => $v3) {
                if ($v2['id'] == $v3) {
                    $tree[] = $v2;
                }
            }
        }
        return $tree;
    }

    /**
     * 区域编辑页面
     * @author wanyan
     * @date 2017-08-29
     */
    public function getOneArray2($arr, $key) {
        if (!is_array($arr)) {
            return false;
        }
        foreach ($arr as $k => $v) {
            $tree[] = $v[$key];
        }
        return $tree;
    }

    /**
     * 区域编辑页面
     * @author wanyan
     * @date 2017-08-29
     */
    public function doEdit() {
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '0';
        $name = !empty($_REQUEST['name']) ? $_REQUEST['name'] : array();
//        $cate_name = !empty($_REQUEST['cate_name']) ? htmlspecialchars(trim($_REQUEST['cate_name'])) : '';
//        $english_name = $_REQUEST['english_name'] ? htmlspecialchars(trim($_REQUEST['english_name'])) : '';
        $cate_code = !empty($_REQUEST['cate_code']) ? htmlspecialchars(trim($_REQUEST['cate_code'])) : '';
        $fx_discount = !empty($_REQUEST['fx_discount']) ? htmlspecialchars(trim($_REQUEST['fx_discount'])) : 0; //三级分销优惠

        $language_id = !empty($_REQUEST['language_id']) ? $_REQUEST['language_id'] : '';
        $currency_id = !empty($_REQUEST['currency_id']) ? $_REQUEST['currency_id'] : '';
        //
        foreach ($name as $key => $val) {
            $name[$key] = trim($val);
        }


//        var_dump($name);die;
//        foreach ($name as $val) {
//            if (empty($val)) {
//                $this->setData($info = array(), $status = '0', '站点国家名称必填');
//                break;
//            }
//        }

        if (count(array_filter($name)) == 0) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->country_required);
        }
        $namespec = array_filter($name);
        if (empty($namespec[$this->lang_id])) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->cn_site_required);
        }
        //
        if (empty($fx_discount)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->three_discount_required);
        }

        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $fx_discount)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->three_illegal);
        }

//        if (empty($cate_name)) {
//            $this->setData($info = array(), $status = '0', $a['cate_cate']);
//        } else {
//            $query = array(
//                'cond' => "`cate_name`='{$cate_name}' and `id` <>'{$id}'",
//                'fields' => 'cate_name'
//            );
//            $rs = $this->storeCateMod->getOne($query);
//            if ($rs['cate_name']) {
//                $this->setData($info = array(), $status = '0', $a['cate_ch_name']);
//            }
//        }
//        if (empty($english_name)) {
//            $this->setData($info = array(), '0', $a['cate_en_name']);
//        }

        if (empty($language_id)) {
            $this->setData(array(), '0', $this->langDataBank->project->lang_required);
        }

        if (empty($currency_id)) {
            $this->setData(array(), '0', $this->langDataBank->project->currency_required);
        }

//        if (mb_strlen($english_name) > 30) {
//            $this->setData(array(), '0', $a['cate_en_names']);
//        }
        if (empty($cate_code)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->classify_code_required);
        } else {
            $query = array(
                'cond' => "`cate_code`='{$cate_code}' and `id` !='{$id}'",
                'fields' => 'cate_code'
            );
            $rs = $this->storeCateMod->getOne($query);
            if ($rs['cate_code']) {
                $this->setData($info = array(), $status = '0', $this->langDataBank->project->code_repeat);
            }
        }
//        if (empty($cate_sort)) {
//            $this->setData($info = array(), $status = '0', $a['cate_sort']);
//        }
//        if (!preg_match('/^[1-9][0-9]{0,2}$/', $cate_sort)) {
//            $this->setData($info = array(), $status = '0', $a['cate_whole']);
//        }
        // 统计该分类的站点数
//        $sql = "select count(*) from ".DB_PREFIX."store where `store_cate_id` = ".$

        $station_count = 0; // 虚拟设置区域站点数
        $data = array(
//            'cate_name' => $cate_name,
//            'english_name' => $english_name,
            'cate_code' => $cate_code,
            'fx_discount' => $fx_discount,
            'lang_id' => $language_id,
            'currency_id' => $currency_id,
            'modify_time' => time()
        );
        $insert_id = $this->storeCateMod->doEdit($id, $data);
        //编辑bs_store 里的 lang_id 和 currency_id
        $dataStore = array(
            'key' => 'store_cate_id',
            'lang_id' => $language_id,
            'currency_id' => $currency_id,
        );
        $this->storeMod->doEdit($id, $dataStore);
        //
        if (!empty($insert_id)) {
            //删除原来的多版本信息
            $scLangMod = &m('storeCateLang');
            $where = '  cate_id =' . $id;
            $scLangMod->doDrops($where);
            //添加多语言版本信息
            $this->doLangData(array_filter($name), $id);
            //
            $info['url'] = "?app=storeCate&act=index";
            $this->setData($info = array(), $status = '1', $this->langDataBank->public->edit_success);
        } else {
            $this->setData($info = array(), $status = '0', $this->langDataBank->public->edit_fail);
        }
    }

    /**
     * 区域站点删除
     * @author wanyan
     * @date 2017-08-30
     */
    public function dele() {
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '0';
        $query = array(
            'cond' => "`id` = '{$id}' "
        );
        $query_cond = array(
            'cond' => "`store_cate_id` = '{$id}'"
        );
        $query_cond2 = array(
            'cond' => "`cate_id` = '{$id}'"
        );
        $rs = $this->storeMod->getData($query_cond);
        $rs = array_filter($rs);
        if (count($rs)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->delete_first);
        }
        $del_id = $this->storeCateMod->doDelete($query);
        //删除bs_store_cate_lang
        //删除子表的信息
        $scLangMod = &m('storeCateLang');
        $where = '  cate_id =' . $id;
        $ctglangids = $scLangMod->doDrops($where);
        //
        if ($del_id) {
            $this->setData($info = array(), $status = '1', $this->langDataBank->public->drop_success);
        } else {
            $this->setData($info = array(), $status = '0', $this->langDataBank->public->drop_fail);
        }
    }

    /**
     * 区域站点批量删除
     * @author wanyan
     * @date 2017-08-30
     */
    public function delAll() {
        $id = !empty($_REQUEST['id']) ? htmlspecialchars($_REQUEST['id']) : '';
        $ids = explode(',', $id);
        foreach ($ids as $k => $v) {
            $count = $this->getStoreByCate($v);
            if ($count > 0) {
                $this->setData($info = array(), $status = '0', $this->langDataBank->project->delete_first);
            } else {
                $query = array(
                    'cond' => "`id` = '{$v}' "
                );
                $del_id[] = $this->storeCateMod->doDelete($query);
            }
        }
        if ($del_id) {
            $this->setData($info = array(), $status = '1', $this->langDataBank->public->drop_success);
        } else {
            $this->setData($info = array(), $status = '0', $this->langDataBank->public->drop_fail);
        }
    }

    /**
     * 区域站点分类子店铺
     * @author wanyan
     * @date 2017-08-30
     */
    public function getStoreByCate($cate_id) {
        $query_cond = array(
            'cond' => "`id` = '{$cate_id}'"
        );
        $rs = $this->storeMod->getData($query_cond);
        $rs = array_filter($rs);
        return count($rs);
    }

    /**
     * 切换开关
     * @author wanyan
     * @date 2017-08-31
     */
    public function getStatus() {
        $cate_id = !empty($_REQUEST['cate_id']) ? intval($_REQUEST['cate_id']) : '0';
        $is_open = !empty($_REQUEST['is_open']) ? intval($_REQUEST['is_open']) : '0';
        $query = array(
            'cond' => "`id`='{$cate_id}'",
            'fields' => 'is_open'
        );
        $res = $this->storeCateMod->getOne($query);
        $zds = $this->getStoreByCate($cate_id);
        if (!empty($zds) && $res['is_open'] == 1) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->classification_disable);
        }
        $data = array(
            'is_open' => $is_open,
            'modify_time' => time()
        );
        $rs = $this->storeCateMod->doEdit($cate_id, $data);
        if ($rs) {
            $this->setData($info = array(), $status = 1, $message = '');
        } else {
            $this->setData($info = array(), $status = 0, $message = '');
        }
    }

}
