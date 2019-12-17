<?php

/**
 * 区域商品管理模块
 * @author wanyan
 * @date 2017-08-29
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class AreaGoodApp extends BackendApp {

    private $storeCateMod;
    private $storeMod;
    private $accountMod;
    private $cityMod;
    private $countryMod;
    private $storeUserMod;
    private $currencyMod;
    private $goodItemPriceMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->storeCateMod = &m('storeCate');
        $this->storeMod = &m('store');
        $this->accountMod = & m('account');
        $this->cityMod = & m('city');
        $this->countryMod = & m('country');
        $this->storeUserMod = &m('storeUser');
        $this->areaGoodMod = &m('areaGood');
        $this->currencyMod = &m('currency');
        $this->goodItemPriceMod = &m('storeGoodItemPrice');
    }

    /**
     * 区域商品首页
     * @author wanyan
     * @date 2017-09-07
     */
    public function index() {
        $store_id = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : '';
        $goods_name = !empty($_REQUEST['goods_name']) ? htmlspecialchars(addslashes($_REQUEST['goods_name'])) : '';
        $where = " where `mark` =1";
        $wherei = " where `mark` =1";
        if (!empty($store_id)) {
            $wherei .= " and sg.store_id = '{$store_id}'";
        }
        if (!empty($goods_name)) {
            $wherei .= " and sg.goods_name like '%" . $goods_name . "%'";
        }
        $country_id = $this->roleCountry;
        if ($country_id) {
            $store_ids = $this->storeMod->getData(array("cond" => "store_cate_id=" . $country_id, "fields" => "id"));
            $ids = implode(',', $this->arrayColumn($store_ids, "id"));
            $where .= " and sg.store_id in (" . $ids . ")";
        }
        $where .= " order by id desc ";
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "store_goods " . $where;
        $totalCount = $this->areaGoodMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $this->assign('p', $p);
        $wherei.=' ORDER BY c.sales_Volume DESC,sg.add_time DESC ';
        $sql = "select sg.id,sg.goods_id,sg.goods_sn,sg.goods_name,sgl.original_img as sgl_img,sg.shop_price,sg.store_id,sg.goods_storage,sg.add_time,sg.is_recommend,sg.is_on_sale,c.sales_Volume from "
                . DB_PREFIX . "store_goods as sg left join " 
                . DB_PREFIX . "goods as sgl on sg.goods_id = sgl.goods_id  left join (select goods_id,SUM(goods_num) sales_Volume from bs_order_goods GROUP BY goods_id) as c on  sg.id= c.goods_id "
                . $wherei;
        $rs = $this->areaGoodMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        foreach ($rs['list'] as $k => $v) {
            $itemList = $this->goodItemPriceMod->getData(array("cond" => "store_goods_id=" . $v['id']));
            $rs['list'][$k]['itemList'] = $itemList[0]['key_name'];
//            //商品总销量
//            $order_salesSql = 'SELECT sum(goods_num) as goods_num FROM  ' . DB_PREFIX . 'order_goods  WHERE store_id = ' . $v['store_id'] . ' and  goods_id =' . $v['id'] . ' and order_state>10';
//            $order_salesRes = $this->storeMod->querySql($order_salesSql);
//            if(!empty($order_salesRes[0]['goods_num'])){
//                $rs['list'][$k]['sales_Volume'] = $order_salesRes[0]['goods_num'];
//            }else{
//                $rs['list'][$k]['sales_Volume']=0;
//            }
            $sql = 'SELECT  c.currency_id,l.store_name  FROM  ' . DB_PREFIX . 'store  as c
                 left join  ' . DB_PREFIX . 'store_lang as l on  c.id = l.store_id
                 WHERE c.is_open = 1  and  l.lang_id =' . $this->lang_id . ' and l.distinguish=0  and c.id=' . $v['store_id'];
            $res = $this->storeMod->querySql($sql);
            $rs['list'][$k]['store_name'] = $res[0]['store_name'];
            $curInfo = $this->currencyMod->getCurrencyName($res[0]['currency_id']);
            $rs['list'][$k]['symbol'] = $curInfo['symbol'] ? $curInfo ['symbol'] : '';
            $rs['list'][$k]['short'] = $curInfo ['short'] ? $curInfo ['short'] : '';
            if (empty($v['add_time'])) {
                $rs['list'][$k]['add_time'] = '';
            } else {
                $rs['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            }
            $rs['list'][$k]['goods_name'] = htmlspecialchars_decode($v['goods_name']);

            if ($v['add_time']) {
                $rs['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            } else {
                $rs['list'][$k]['add_time'] = '';
            }
            $rs['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
//            $rs['list'][$k]['sort'] = $total - $k - 20 * ($p - 1); //倒叙
        };

     /*   $sort = array(
            'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
            'field' => 'sales_Volume', //排序字段
        );
        $arrSort = array();
        foreach ($rs['list'] AS $uniqid => $row) {
            foreach ($row AS $key => $value) {
                $arrSort[$key][$uniqid] = $value;
            }
        }
        if ($sort['direction']) {
            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $rs['list']);
        }
        foreach ($rs['list'] as $k => $v) {
            $rs['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1;
        }*/
        $this->assign('store_id', $store_id);
        $this->assign('goods_name', stripslashes($goods_name));
        $this->assign('list', $rs['list']);
        $this->assign('page', $rs['ph']);
        $this->assign('store', $this->getUseStore());
        $this->display('areaGood/index.html');
    }

    /**
     * 商品规格明细
     * @author wangshuo
     * @date 2018-09-5
     */
    public function specIfications() {
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '';
        $store_id=!empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : '';
//        $areaGoodLangMod = &m('areaGoodLang');
        $query = array(
            'cond' => "`id` = '{$id}'"
        );
        $rs = $this->areaGoodMod->getOne($query);
        $itemList = $this->goodItemPriceMod->getData(array("cond" => "store_goods_id=" . $id));
        //获取当前币种
        $currencyInfo = $this->currencyMod->getCurrencyById($rs['store_id']);
        //商品规格处理
        foreach ($itemList as $k => $v) {
            
        }
        $this->assign('store_id',$store_id);
        //end
        $this->assign('currencyInfo', $currencyInfo);
        $this->assign('goodInfo', $rs);
        $this->assign('p', $p);
        $this->assign('itemList', $itemList);
        $this->display('areaGood/edit_Specifications.html');
    }

    /**
     * 商品明细
     * @author wangshuo
     * @date 2018-09-5
     */
    public function orderDetails() {
        $startTime = !empty($_REQUEST['start_time']) ? strtotime($_REQUEST['start_time']) : '';
        $endTime = !empty($_REQUEST['end_time']) ? strtotime($_REQUEST['end_time']) : '';
        $store_id=!empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : '' ;
        if (!empty($startTime) && !empty($endTime) && ($startTime > $endTime)) {
            $t = $endTime;
            $endTime = $startTime;
            $startTime = $t;
        }
        if (!empty($endTime)) {
            $endTime = $endTime + 24 * 3600 - 1;
        }
        $this->assign('isend', $isend);
        $this->assign('stime', date('Y/m/d', $startTime));
        $this->assign('etime', date('Y/m/d', $endTime));
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '';
        // 筛选条件
        if (!empty($startTime)) {
            $where .= '  and  g.add_time >= ' . $startTime;
        }
        if (!empty($endTime)) {
            $where .= '  and  g.add_time <= ' . $endTime;
        }
        $sql = "select * from " . DB_PREFIX . "order_goods as g left join 
        " . DB_PREFIX . "order as gl on  g.order_id =gl.order_sn where g.goods_id= " . $id . $where . " order by rec_id desc";
        $rs = $this->storeMod->querySqlPageData($sql, $array = array("pre_page" => 5000000000, "is_sql" => false, "mode" => 1));
        foreach ($rs['list'] as $k => $v) {
            $rs['list'][$k]['sort_id'] = $k + 1; //正序
        }
        $this->assign('store_id',$store_id);
        $this->assign('id', $id);
        $this->assign('goodInfo', $rs['list']);
        $this->assign('p', $p);
        $this->display('areaGood/orderDetails.html');
    }

    /**
     * 区域商品添加
     * @author wanyan
     * @date 2017-09-07
     */
    public function add() {
        $type = !empty($_REQUEST['type']) ? htmlspecialchars($_REQUEST['type']) : '';
        $select_type = !empty($_REQUEST['select_type']) ? htmlspecialchars($_REQUEST['select_type']) : '';
        $other = !empty($_REQUEST['other']) ? htmlspecialchars($_REQUEST['other']) : '';
        $cate = !empty($_REQUEST['Lcate']) ? htmlspecialchars($_REQUEST['Lcate']) : '';
        $cate_name = !empty($_REQUEST['cate']) ? htmlspecialchars($_REQUEST['cate']) : '';
        $good_name = !empty($_REQUEST['good_name']) ? addslashes(trim($_REQUEST['good_name'])) : '';
        $store_id = !empty($_REQUEST['store_id']) ? rtrim($_REQUEST['store_id'], ',') : '';
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $this->assign('p', $p);
        $where = " where g.`is_on_sale` =1";
        if (empty($store_id)) {
            $where .= " and gl.lang_id = {$this->lang_id}";
        }
        // 获取当前区域的国家的语言ID
        if (!empty($store_id)) {
            if ($type == 'single') {
                $country_id = $this->getCountryId($store_id);
                $language_id = $this->getLanguageById($country_id);
                $where .= " and  gl.lang_id = '" . $language_id . "'";
            } elseif ($type == "multi") {
                $country_ids = $this->getCountryId($store_id);
                $language_ids = $this->getLanguageById($country_ids);
                $where .= " and  gl.lang_id  = '" . $language_ids . "'";
            }
        }
        if (!empty($store_id)) {
            if ($type == 'single') {
                $country_id = $this->getCountryId($store_id);
                $where .= " and ( find_in_set ($country_id,g.store_cate_ids) or g.`store_cate_ids` = '')";
            }
            if ($type == 'multi') {
                $country_ids = $this->getCountryIds($store_id);
                $where .= " and (g.store_cate_ids in ({$country_ids}) or g.`store_cate_ids` = '')";
            }
        }
        if (!empty($store_id)) {

            if ($type == "single") {
                $store_data = $this->areaGoodMod->getAreaGood($store_id);
                if (!empty($store_data)) {
                    $store_data = implode(',', $store_data);
                    $where .= " and g.`goods_id` not in ({$store_data})";
                }
            }
            if ($type == "multi") {
                $country_ids = $this->getCountryId($store_id);
                $language_ids = $this->getLanguageById($country_ids);
                $store_data = $this->areaGoodMod->getNuAreaGood($store_id, $language_ids);
//                    var_dump($store_data);die;
                $where .= " and g.`goods_id`  in ({$store_data})";
            }
        }
        // var_dump($this->areaGoodMod->getAreaGood($store_id));die;
        if (!empty($select_type) && !empty($other)) {
            $where .= " and `{$select_type}` = '{$other}'";
            $this->assign('other', $other);
        } elseif (!empty($select_type) && !empty($cate)) {
            $where .= " and `{$select_type}` = '{$cate}'";
            $this->assign('Lcate', $cate);
            $this->assign('cate_name', $cate_name);
        }
        if (!empty($_REQUEST['good_name'])) {
            $where .= " and gl.goods_name like '%" . $good_name . "%'";
            $this->assign('good_name', stripslashes($good_name));
        }
        if (!empty($select_type) && $select_type == 'goods_type') {
            $good_type = $this->getGoodsType();
            $this->assign('canshu', $good_type);
        } elseif (!empty($select_type) && $select_type == 'brand_id') {
            $brand = $this->getBrand();
            $this->assign('canshu', $brand);
        }
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "goods " . $where;
        $totalCount = $this->storeMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;

        $where .= " order by g.`goods_id` desc";
        $sql = "select g.`goods_id`,gl.`goods_name`,g.`market_price`,g.`shop_price`,g.original_img, g.`cat_id`,g.`brand_id`,g.`goods_type`,gl.`lang_id` from " . DB_PREFIX . "goods as g left join 
        " . DB_PREFIX . "goods_lang as gl on  g.goods_id = gl.goods_id" . $where;
        $rs = $this->storeMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        foreach ($rs['list'] as $k => $v) {
            $rs['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
//            $rs['list'][$k]['sort'] = $total - $k - 20 * ($p - 1); //倒叙
        }
        $list_cate = $this->getCate();
        $this->assign('list_cate', $list_cate);
        $this->assign('list', $rs['list']);
        $this->assign('page', $rs['ph']);
        $this->assign('selectType', $select_type);
        $this->assign('countrys', $this->getCountry($this->lang_id));
        if ($this->shorthand == 'ZH') {
            $good_type = G('good_type');
        } else if ($this->shorthand == 'EN'){
            $good_type = G('good_type_en');
        }
        $this->assign('good_type', $good_type);
        $this->assign('type', $type);
        if ($type == 'single') {
            $this->assign('store', $this->getUseStore());
        }
        $this->assign('act', 'index');
        $this->assign('lang_id', $this->lang_id);
        if (!empty($store_id)) {
            $this->assign('store_id', $store_id);
            if ($type == 'multi') {
                $country_ids = $this->getCountryIds($store_id);
                $country_ids = array_unique(explode(',', $country_ids)); // 分割成数组
                $country_id = $country_ids[0];
                $this->assign('store', $this->getCountryStore($country_id));
                //var_dump($this->getCountryStore($country_id));die;
                $storeArr = explode(',', $store_id);
                $this->assign('storeArr', $storeArr);
                $this->assign('storeCountry', $country_id);
            }
        }
        $this->display('areaGood/add.html');
    }

    /**
     * 获取区域国家
     * @author wanyan
     * @date 2017-11-23
     */
    public function getCountry($lang) {
        $sql = "select SC.`id`,SCL.`cate_name`  from  " . DB_PREFIX . "store_cate AS SC LEFT JOIN " . DB_PREFIX . "store_cate_lang  
        AS SCL ON SC.id = SCL.cate_id where SCL.lang_id = " . $lang;
        $rs = $this->storeCateMod->querySql($sql);
        return $rs;
    }

    /**
     * 获取品牌数据
     * @author wanyan
     * @date 2017-09-12
     */
    public function getBrand() {
        $sql = "select gc.id, gcl.brand_name as name from " . DB_PREFIX . "goods_brand as gc left join " . DB_PREFIX . "goods_brand_lang as gcl ON gc.id =  gcl.brand_id where gcl.lang_id = {$this->lang_id}";
        $rs = $this->storeMod->querySql($sql);
        return $rs;
    }

    /**
     * 获取商品类型
     * @author wanyan
     * @date 2017-09-12
     */
    public function getGoodsType() {
        $sql = "select gc.id, gcl.type_name as name from " . DB_PREFIX . "goods_type as gc left join " . DB_PREFIX . "goods_type_lang as gcl ON gc.id = gcl.type_id where gcl.lang_id = {$this->lang_id} and gc.mark =1";
        $rs = $this->storeMod->querySql($sql);
        return $rs;
    }

    /**
     * 获取启用的站点
     * @author wanyan
     * @date 2017-09-07
     */
    public function getUseStore() {
//        $query = array(
//            'cond' => "`is_open` = '1'",
//            'fields' => '`id`,`store_name`'
//        );   
//        $rs = $this->storeMod->getData($query);
        $sql = 'SELECT  c.id,l.store_name  FROM  ' . DB_PREFIX . 'store  as c
                 left join  ' . DB_PREFIX . 'store_lang as l on  c.id = l.store_id
                 WHERE c.is_open = 1  and  l.lang_id =' . $this->lang_id . ' and l.distinguish = 0';
        $rs = $this->storeMod->querySql($sql);

        return $rs;
    }

    /**
     * 获取启用的站点
     * @author wanyan
     * @date 2017-09-07
     */
    public function getCountryStore($country_id) {
//        $query = array(
//            'cond' => "`is_open` = '1' and store_cate_id = '{$country_id}'",
//            'fields' => '`id`,`store_name`'
//        );
        $sql = "select s.id, sl.store_name  from " . DB_PREFIX . "store as s
                left join " . DB_PREFIX . "store_lang as sl ON s.id =  sl.store_id
                where sl.lang_id = {$this->lang_id} and sl.distinguish = 0  and s.is_open ='1' and s.store_cate_id = '{$country_id}'";
        $rs = $this->storeMod->querySql($sql);
        return $rs;
    }

    /**
     * 获取分类
     * @author wanyan
     * @date 2017-09-12
     */
    public function getCate() {
        $sql = "select gc.id, gcl.category_name as name,gc.parent_id from " . DB_PREFIX . "goods_category as gc left join " . DB_PREFIX . "goods_category_lang as gcl ON gc.id =  gcl.category_id where gcl.lang_id = {$this->lang_id}"; // 获取所有的分类
        $catRs = $this->storeMod->querySql($sql);
        $rs = $this->tree($catRs, $p_id = 0);
        return $rs;
    }

    /**
     * 获取不同类型的数据
     * @author wanyan
     * @date 2017-09-08
     */
    public function getData() {
        $id = !empty($_REQUEST['id']) ? htmlspecialchars($_REQUEST['id']) : '0';
        if ($id == 'goods_type') {//获取商品类型的数据
            $sql = "select gc.id, gcl.type_name as name from " . DB_PREFIX . "goods_type as gc left join " . DB_PREFIX . "goods_type_lang as gcl ON gc.id = gcl.type_id where gcl.lang_id = {$this->lang_id} and gc.mark =1";
            $rs = $this->storeMod->querySql($sql);
            echo json_encode($rs);
            die;
        } elseif ($id == 'cat_id') {
            $sql = "select gc.id, gcl.category_name as name,gc.parent_id from " . DB_PREFIX . "goods_category as gc left join " . DB_PREFIX . "goods_category_lang as gcl ON gc.id =  gcl.category_id where gcl.lang_id = {$this->lang_id}"; // 获取所有的分类
            $catRs = $this->storeMod->querySql($sql);
            $rs = $this->tree($catRs, $p_id = 0);
            $res['status'] = 1;
            $res['data'] = $rs;
            echo json_encode($res);
            die;
        } elseif ($id == 'brand_id') {
            $sql = "select gc.id, gcl.brand_name as name from " . DB_PREFIX . "goods_brand as gc left join " . DB_PREFIX . "goods_brand_lang as gcl ON gc.id =  gcl.brand_id where gcl.lang_id = {$this->lang_id}";
            $rs = $this->storeMod->querySql($sql);
            echo json_encode($rs);
            die;
        }
    }

    /**
     * 获取子分类
     * @author wanyan
     * @date 2017-09-08
     */
    public function tree($table, $p_id = 0) {
        $tree = array();
        foreach ($table as $row) {
            if ($row['parent_id'] == $p_id) {
                $tmp = $this->tree($table, $row['id']);
                if ($tmp) {
                    $row['children'] = $tmp;
                } else {
                    $row['leaf'] = true;
                }
                $tree[] = $row;
            }
        }
        return $tree;
    }

    /**
     * 添加商品
     * @author wanyan
     * @date 2017-09-12
     */
    public function doAddGood() {
        $status = !empty($_REQUEST['status']) ? htmlspecialchars($_REQUEST['status']) : '0';
        $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars($_REQUEST['store_id']) : '';
        $good_id = !empty($_REQUEST['goods_id']) ? htmlspecialchars($_REQUEST['goods_id']) : '';
        $goods_id = array_filter(explode(',', $good_id));
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        // var_dump($goods_id);die;
        if ($status == 1) {
            foreach ($goods_id as $k => $v) {
                $query = array(
                    'cond' => " `goods_id` = '{$v}' and `store_id` = '{$store_id}' and `mark` =1",
                    'fields' => ' goods_id,`goods_name`'
                );
                $rs = $this->areaGoodMod->getOne($query);
                if ($rs['goods_id']) {
                    $this->setData($info = array(), $status = '0', $message = "<font color='red'>{$rs['goods_name']}</font>{$a['area_name']}");
                }
                $country_id = $this->getCountryId($store_id);
                $language_id = $this->getLanguageById($country_id);
                $goods = $this->getGoodById($v, $language_id);
                $insert_id[] = $this->doInsertData($store_id, $goods, $language_id);
            }
        } else {
            $store_ids = array_filter(explode(',', $store_id));
            foreach ($store_ids as $k => $store_idv) {
                foreach ($goods_id as $k1 => $good_idv) {
                    $query = array(
                        'cond' => " `goods_id` = '{$good_idv}' and `store_id` = '{$store_idv}'  and `mark` =1 ",
                        'fields' => ' goods_id,goods_name'
                    );
                    $rs = $this->areaGoodMod->getOne($query);
                    if ($rs['goods_id']) {
                        $this->setData($info = array(), $status = '0', $message = "<font color='red'>{$rs['goods_name']}</font>当前商品在该店铺已存在,不能重复添加");
                    }
                    $country_ids = $this->getCountryId($store_id);
                    $language_ids = $this->getLanguageById($country_ids);
                    $goods = $this->getGoodById($good_idv, $language_ids);
                    $insert_id[] = $this->doInsertData($store_idv, $goods, $language_ids);
                }
            }
        }
        $insert_id = array_filter($insert_id);
        if (count($insert_id)) {
            $this->addLog('商品添加操作');
            $info['url'] = "?app=areaGood&act=index&p={$p}";
            $this->setData($info, $status = '1', $this->langDataBank->public->add_success);
        } else {
            $this->setData($info = array(), $status = '0', $this->langDataBank->public->add_error);
        }
    }

    /**
     * 获取商品信息
     * @author wanyan
     * @date 2017-09-12
     */
    public function getGoodById($good_id, $language_id) {
        $sql = "select g.goods_id,g.cat_id,g.goods_sn,gl.goods_name,g.goods_storage,g.goods_type,
        g.spec_type,g.brand_id,g.brand_name,g.shop_price,g.market_price,g.cost_price,gl.goods_remark,gl.goods_content,
        g.original_img,g.is_free_shipping,g.is_free_shipping,g.is_recommend,g.is_new,g.is_hot,g.suppliers_id,
        g.spu,g.sku,g.shipping_area_ids,g.on_time,g.style_id,g.room_id,g.lang_id,g.keywords
        from " . DB_PREFIX . "goods as g left join " . DB_PREFIX . "goods_lang as gl on g.goods_id = gl.goods_id
       where g.`goods_id` = '{$good_id}' and gl.lang_id = '{$language_id}'";
        $rs = $this->storeMod->querySql($sql);
        return $rs[0];
    }

    /**
     * 插入商品信息
     * @author wanyan
     * @date 2017-09-12
     */
    public function doInsertData($store_id, $goods, $language_id) {
        $rate = $this->currencyMod->getCurrencyRateById($store_id);
        $insert_data = array(
            'goods_id' => $goods['goods_id'],
            'cat_id' => $goods['cat_id'],
            'goods_sn' => $goods['goods_sn'],
            'goods_name' => addslashes($goods['goods_name']),
            'goods_storage' => $goods['goods_storage'],
            'goods_type' => $goods['goods_type'],
            'spec_type' => $goods['spec_type'],
            'brand_id' => $goods['brand_id'],
            'brand_name' => $goods['brand_name'],
            'store_id' => $store_id,
            'shop_price' => round($goods['shop_price'] / $rate, 2),
            'market_price' => round($goods['market_price'] / $rate, 2),
            'cost_price' => round($goods['cost_price'] / $rate, 2),
            'goods_remark' => $goods['goods_remark'],
            'goods_content' => $goods['goods_content'],
            'original_img' => $goods['original_img'],
            'is_on_sale' => 2,
            'is_free_shipping' => $goods['is_free_shipping'],
            'is_recommend' => $goods['is_recommend'],
            'is_new' => $goods['is_new'],
            'is_hot' => $goods['is_hot'],
            'suppliers_id' => $goods['suppliers_id'],
            'spu' => $goods['spu'],
            'sku' => $goods['sku'],
            'shipping_area_ids' => $goods['shipping_area_ids'],
            'on_time' => $goods['on_time'],
            'style_id' => $goods['style_id'],
            'room_id' => $goods['room_id'],
            'lang_id' => $goods['lang_id'],
            'keywords' => $goods['keywords'],
            'add_time' => time(),
            'mark' => 1
        );
        $ins_id = $this->areaGoodMod->doInsert($insert_data);
//        $code_url = $this->goodsCode($store_id,$language_id,$ins_id);
//        $cond['code_url'] = $code_url;
//        $this->areaGoodMod->doEdit($ins_id,$cond);
        //拉去商品的时候同步商品规格价格信息
        //modify by lee 2017-9-18 15:36:25
        $goodsMod = &m("goods");
        $storeGoodMod = &m("storeGoodItemPrice");
        $sql = "select key_name,price,`key` as spec_key,goods_storage from " . DB_PREFIX . "goods_spec_price where goods_id=" . $goods['goods_id'];
        $list = $goodsMod->querySql($sql);
        if ($list) {
            foreach ($list as $k => $v) {
                $sql = "insert into " . DB_PREFIX . "store_goods_spec_price (goods_id,`key`,key_name,price,store_goods_id,goods_storage) VALUES(" . $goods['goods_id'] . ",'" . $v['spec_key'] . "','" . $v['key_name'] . "'," . round($v['price'] / $rate, 2) . "," . $ins_id . "," . $v['goods_storage'] . ")";
                $res = $storeGoodMod->sql_b_spec($sql);
            }
        }
        //end
        return $ins_id;
    }

    /**
     * 编辑商品信息
     * @author wanyan
     * @date 2017-09-13
     */
    public function edit() {
        $lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : 0;
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '';
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $this->assign('p', $p);
        $query = array(
            'cond' => "`id` = '{$id}'"
        );
        $rs = $this->areaGoodMod->getOne($query);
        $this->assign('act', 'index');
        $this->assign('goodInfo', $rs);
        $this->assign('lang_id', $lang_id);
        $currency = $this->currencyMod->getCurrencyById($rs['store_id']);
        $this->assign('currency', $currency);
        if ($this->lang_id == 0) {
            $this->display('areaGood/edit.html');
        } else {
            $this->display('areaGood/edit_en.html');
        }
    }

    /**
     * 改变是否总部状态
     * @author zhangr
     * @date 2017-9-05
     */
    public function getStatus() {
        $id = $_REQUEST['id'];
        $is_on_sale = $_REQUEST['is_on_sale'];
        $data = array(
            'is_on_sale' => $is_on_sale
        );
        $rs = $this->areaGoodMod->doEdit($id, $data);
        if ($rs) {
            $this->addLog('商品上下架设置操作');
            $this->setData($info = array(), $status = '1', $message = '');
        } else {
            $this->setData($info = array(), $status = '0', $this->langDataBank->public->enable_fail);
        }
    }

    /**
     * 单个更新商品价格库存
     * @author zhangr
     * @date 2017-9-05
     */
    public function doSPrice() {
        $id = $_REQUEST['id'];
        $shop_price = $_REQUEST['shop_price'];
        $goods_storage = $_REQUEST['goods_storage'];
        $data = array(
            'shop_price' => $shop_price,
            'goods_storage' => $goods_storage
        );
        $rs = $this->areaGoodMod->doEdit($id, $data);
        if ($rs) {
            $this->addLog('商品修改价格库存操作');
            $this->setData($info = array(), $status = '1', $this->langDataBank->public->edit_success);
        } else {
            $this->setData($info = array(), $status = '0', $this->langDataBank->public->edit_fail);
        }
    }

    /**
     * 批量更新价格库存
     * @author zhangr
     * @date 2017-9-05
     */
    public function doDPrice() {
//        var_dump($_REQUEST);die;
        $id = $_REQUEST['id'];
        $shop_price = $_REQUEST['shop_price'];
        $goods_storage = $_REQUEST['goods_storage'];
        $ids = array_filter(explode(',', $id));
        if (empty($shop_price) && !empty($goods_storage)) {
            $data['goods_storage'] = $goods_storage;
        }
        if (!empty($shop_price) && empty($goods_storage)) {
            $data['shop_price'] = $shop_price;
        }
        if (!empty($shop_price) && !empty($goods_storage)) {
            $data['shop_price'] = $shop_price;
            $data['goods_storage'] = $goods_storage;
        }
//        $data =array(
//            'shop_price' =>$shop_price,
//            'goods_storage' =>$goods_storage
//        );
        foreach ($ids as $k => $v) {
            $rs[] = $this->areaGoodMod->doEdit($v, $data);
        }
        $rs = array_filter($rs);
        $rs = count($rs);
        if ($rs) {
            $this->addLog('商品批量修改价格库存操作');
            $this->setData($info = array(), $status = '1', $this->langDataBank->project->set_success);
        } else {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->set_fail);
        }
    }

    /**
     * 删除商品区域商品
     * @author wanyan
     * @date 2017-9-13
     */
    public function dele() {
        $id = !empty($_REQUEST['id']) ? htmlspecialchars($_REQUEST['id']) : '';
        $rs = $this->areaGoodMod->doMark($id);
        if ($rs) {
            //删除对应规格区域商品规格
            //modify by lee @2017-9-19 10:01:18
            $sql = "delete from " . DB_PREFIX . "store_goods_spec_price where store_goods_id in (" . $id . ")";
            $res = $this->areaGoodMod->sql_b_spec($sql);
            //end
            //删除对应语言信息
            $sql = "delete from " . DB_PREFIX . "store_goods_lang where store_good_id in (" . $id . ")";
            $this->areaGoodMod->sql_b_spec($sql);
            //end
            $this->addLog('商品删除操作');
            $this->setData($info = array(), $status = '1', $this->langDataBank->public->drop_success);
        } else {
            $this->setData($info = array(), $status = '0', $this->langDataBank->public->drop_fail);
        }
    }

    /**
     * 获取某个店铺商品
     * @author wanyan
     * @date 2017-9-13
     */
    public function getGoodData() {
        $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars($_REQUEST['store_id']) : '';
        if (empty($store_id)) {
            $this->setData($info = array(), $status = 0, $message = '');
        }
        $query = array(
            'cond' => " `store_id` = '{$store_id}' and mark =1",
            'fields' => " `goods_id`"
        );
        $rs = $this->areaGoodMod->getData($query);
        $info['data'] = $rs;
        $info['store_id'] = $store_id;
        $this->setData($info, $status = 1, $message = '');
//       echo json_encode($rs);die;
    }

    /**
     * 获取多个店铺商品
     * @author wanyan
     * @date 2017-9-14
     */
    public function getMultiGoodData() {
        $store_ids = !empty($_REQUEST['store_id']) ? htmlspecialchars($_REQUEST['store_id']) : '0';
        $store_ids = rtrim($store_ids, ',');
        if (empty($store_ids)) {
            $this->setData($info = array(), $status = 0, $message = '');
        }
        $query = array(
            'cond' => " `store_id` in ({$store_ids}) and mark =1",
            'fields' => " `goods_id`"
        );
        $rs = $this->areaGoodMod->getData($query);
        $this->setData($rs, $status = 1, $message = '');
    }

    /*
     * 是否推荐
     * @author wanyan
     * @date 2017-09-20
     */

    public function changeSales() {
        $id = $_REQUEST['id'];
        $is_on_sale = $_REQUEST['is_on_sale'];
        $data = array(
            'is_on_sale' => $is_on_sale
        );
        $rs = $this->areaGoodMod->doEdit($id, $data);
        if ($rs) {
            $this->setData($info = array(), $status = 1, $message = '');
        } else {
            $this->setData($info = array(), $status = 0, $this->langDataBank->public->recommend_fail);
        }
    }

    /*
     * 是否推荐
     * @author wanyan
     * @date 2017-09--20
     */

    public function changeRecommend() {
        $id = $_REQUEST['id'];
        $is_recommend = $_REQUEST['is_recommend'];
        $data = array(
            'is_recommend' => $is_recommend
        );
        $rs = $this->areaGoodMod->doEdit($id, $data);
        if ($rs) {
            $this->setData($info = array(), $status = 1, $message = '');
        } else {
            $this->setData($info = array(), $status = 0, $this->langDataBank->public->recommend_fail);
        }
    }

    /**
     * 批量推荐
     * @author wanyan
     * @date 2018-01-01
     */
    public function doRecommend() {
        $goods_id = !empty($_REQUEST['good_ids']) ? $_REQUEST['good_ids'] : '';
        $flag = !empty($_REQUEST['flag']) ? $_REQUEST['flag'] : '';
        foreach ($goods_id as $k => $v) {
            $rs = $this->areaGoodMod->getOne(array('cond' => "`id` = $v", 'fields' => 'is_recommend'));
            if ($rs['is_recommend'] == 1 && $flag == 1) {
                $this->setData($info['return'] = 'return', $status = 0, $this->langDataBank->project->good_recommend);
            } elseif ($rs['is_recommend'] == 0 && $flag == 2) {
                $this->setData($info['return'] = 'return', $status = 0, $this->langDataBank->project->good_not_recommend);
            }
        }
        $goods_id = implode(',', $goods_id);
        if ($flag == 1) { // 批量推荐
            $data = array(
                'is_recommend' => 1
            );
        } else {
            $data = array(
                'is_recommend' => 0
            );
        }
        $id = $this->areaGoodMod->doEdits($goods_id, $data);
        if ($id) {
            $this->setData($info = array(), $status = 1, $this->langDataBank->public->cz_success);
        } else {
            $this->setData($info = array(), $status = 0, $this->langDataBank->public->cz_error);
        }
    }

    /**
     * 批量上下架
     * @author wanyan
     * @date 2018-01-01
     */
    public function doOnSale() {
        $goods_id = !empty($_REQUEST['good_ids']) ? $_REQUEST['good_ids'] : '';
        $flag = !empty($_REQUEST['flag']) ? $_REQUEST['flag'] : '';
        foreach ($goods_id as $k => $v) {
            $rs = $this->areaGoodMod->getOne(array('cond' => "`id` = $v", 'fields' => 'is_on_sale'));
            if ($rs['is_on_sale'] == 1 && $flag == 1) {
                $this->setData($info['return'] = 'return', $status = 0, $this->langDataBank->project->on_good);
            } elseif ($rs['is_on_sale'] == 2 && $flag == 2) {
                $this->setData($info['return'] = 'return', $status = 0, $this->langDataBank->project->off_good);
            }
        }
        $goods_id = implode(',', $goods_id);
        if ($flag == 1) { // 批量上架
            $data = array(
                'is_on_sale' => 1
            );
        } else {
            $data = array(
                'is_on_sale' => 2
            );
        }
        $id = $this->areaGoodMod->doEdits($goods_id, $data);
        if ($id) {
            $this->setData($info = array(), $status = 1, $this->langDataBank->public->cz_success);
        } else {
            $this->setData($info = array(), $status = 1, $this->langDataBank->public->cz_error);
        }
    }

}
