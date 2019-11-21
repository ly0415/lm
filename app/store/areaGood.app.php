<?php

/**
 * 区域商品管理模块
 * @author wanyan
 * @date 2017-08-29
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class AreaGoodApp extends BaseStoreApp {

    private $storeCateMod;
    private $storeMod;
    private $storeLangMod;
    private $accountMod;
    private $cityMod;
    private $countryMod;
    private $storeUserMod;
    private $goodItemPriceMod;
    private $langId;
    private $currencyMod;
    private $lang_id;
    private $roomTypeMod;
    private $areaGoodMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->storeCateMod = &m('storeCate');
        $this->storeMod = &m('store');
        $this->storeLangMod = &m('areaStoreLang');
        $this->accountMod = & m('account');
        $this->cityMod = & m('city');
        $this->countryMod = & m('country');
        $this->storeUserMod = &m('storeUser');
        $this->areaGoodMod = &m('areaGood');
        $this->goodItemPriceMod = &m('storeGoodItemPrice');
        $this->langId = $this->storeInfo['lang_id'];
        $this->currencyMod = &m('currency');
        $this->roomTypeMod = &m('roomType');
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : 0;
    }

    /**
     * 区域商品首页
     * @author wanyan
     * @date 2017-09-07
     */
    public function index() {
        $is_sale = !empty($_REQUEST['is_sale']) ? htmlspecialchars($_REQUEST['is_sale']) : '';
        $goods_sn = !empty($_REQUEST['goods_sn']) ? htmlspecialchars(trim($_REQUEST['goods_sn'])) : '';
        $goods_name = !empty($_REQUEST['goods_name']) ? htmlspecialchars(trim(addslashes($_REQUEST['goods_name']))) : '';
        $room_id = !empty($_REQUEST['room_id']) ? intval($_REQUEST['room_id']) : '0';
        $start_time = !empty($_REQUEST['start_time']) ? htmlspecialchars($_REQUEST['start_time']) : '';
        $end_time = !empty($_REQUEST['end_time']) ? htmlspecialchars($_REQUEST['end_time']) : '';
        $where = " where sg.store_id = $this->storeId and  sg.mark =1  ";
        if (!empty($_REQUEST['start_time']) && empty($_REQUEST['end_time'])) {
            $start_time = '';
        }
        if (empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])) {
            $end_time = '';
        }

        if (!empty($start_time) && !empty($end_time)) {
            if (strtotime($start_time) == strtotime($end_time)) {
                $start_time = strtotime($start_time . '00:00:00');
                $end_time = strtotime($end_time . '23:59:59');
                $where .= " and sg.add_time >= '{$start_time}' and sg.add_time <= '{$end_time}'";
            }
        }
        if (!empty($start_time) && !empty($end_time)) {
            if (strtotime($start_time) > strtotime($end_time)) {
                $temp = $start_time;
                $start_time = $end_time;
                $end_time = $temp;
            }
        }
        if (!empty($start_time) && !empty($_REQUEST['end_time'])) {
            if (strtotime($end_time) > strtotime($start_time)) {
                $start_time = strtotime($start_time . '00:00:00');
                $end_time = strtotime($end_time . '23:59:59');
                $where .= " and sg.add_time >= '{$start_time}' and sg.add_time <= '{$end_time}'";
            }
        }
        $currency = $this->currencyMod->getCurrencyById($this->storeId);
        if (!empty($is_sale)) {
            $where .= " and sg.is_on_sale ='{$is_sale}'";
        }
        if (!empty($goods_name)) {
            $where .= " and (sg.goods_name like '%" . $goods_name . "%' or sgl.goods_name like '%" . $goods_name . "%')";
        }
        if (!empty($goods_sn)) {
            $where .= " and sg.goods_sn = '{$goods_sn}'";
        }
        if (!empty($room_id)) {
            $where .= " and sg.room_id = '{$room_id}'";
        }
        $where .= " order by sg.is_on_sale desc ";
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "store_goods " . $where;
        $totalCount = $this->areaGoodMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;

        $sql = "select sg.id,sg.goods_id,sg.selected,sg.goods_sn,sg.code_url, (CASE
           WHEN sgl.goods_name <> '' THEN sgl.goods_name
        ELSE sg.goods_name END) as goods_name, sgl.original_img as sgl_img,sg.shop_price,sg.store_id,sg.goods_storage,sg.add_time,sg.is_on_sale,sg.is_free_shipping,sg.is_recommend,sg.is_hot,sg.is_recom
                from " . DB_PREFIX . "store_goods as sg left join  " . DB_PREFIX . "goods as sgl 
                on sg.goods_id = sgl.goods_id" . $where;
//        var_dump($sql);die;
        $rs = $this->areaGoodMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        foreach ($rs['list'] as $k => $v) {
            $sql = 'select store_name from  ' . DB_PREFIX . 'store_lang  where store_id =' . $v['store_id'] . ' and distinguish = 0 and  lang_id =' . $this->defaulLang;
//            $query = array(
//                'cond' => "`id` = '{$v['store_id']}'",
//                'fields' => "`store_name`"
//            );
            $res = $this->storeLangMod->querySql($sql);
            $rs['list'][$k]['store_name'] = $res[0]['store_name'];
//            $sql_1 = "select `goods_name` from " . DB_PREFIX . "store_goods_lang where `store_good_id`='{$v['id']}' and `lang_id` = '{$this->langId}'";
//            $store_good_info = $this->storeMod->querySql($sql_1);
//            if ($store_good_info[0]['goods_name']) {
//                $rs['list'][$k]['goods_name'] = $store_good_info[0]['goods_name'];
//            }
            $rs['list'][$k]['currency'] = $currency['name'] . "(" . $currency['symbol'] . ")";
            if (empty($v['add_time'])) {
                $rs['list'][$k]['add_time'] = '';
            } else {
                $rs['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            }
            if ($v['add_time']) {
                $rs['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            } else {
                $rs['list'][$k]['add_time'] = '';
            }
            $rs['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
//            $rs['list'][$k]['sort'] = $total - $k - 20 * ($p - 1); //倒叙
        }
        // if($this->storeId == 84){
        //      echo "<pre/>";
        // var_dump($rs['list']);die;
        // }
       
        //查看店铺是总代理还是分销商
        $sqll = "select store_type from bs_store where id =".$this->storeId;
        $type = $this->storeMod->querySql($sqll);
//        $this->pre($type);die;
        $returnUrl =  urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"]);
        $this->assign('returnUrl',$returnUrl);
        $this->assign('start_time', date('Y-m-d', $start_time));
        $this->assign('end_time', date('Y-m-d', $end_time));
        $this->assign('room_type', $this->getRoom());
        $this->assign('goods_sn', $goods_sn);
        $this->assign('goods_name', stripslashes($goods_name));
        $this->assign('is_sale', $is_sale);
        $this->assign('list', $rs['list']);
        $this->assign('p', $p);
        $this->assign('page', $rs['ph']);
        $this->assign('lang_id', $this->lang_id);
        $this->assign('room_id', $room_id);
        $this->assign('storeType',$type[0]['store_type']);
        if ($this->lang_id == 1) {
            $this->display('areaGood/index_en.html');
        } else {
            $this->display('areaGood/index.html');
        }
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
        $room_id = !empty($_REQUEST['room_id']) ? intval($_REQUEST['room_id']) : '0';
//        $start_time   =!empty($_REQUEST['start_time']) ? htmlspecialchars($_REQUEST['start_time']) : '';
//        $end_time =!empty($_REQUEST['end_time']) ? htmlspecialchars($_REQUEST['end_time']) :'';
        $where = " where g.`is_on_sale` =1 and ( find_in_set ($this->country_id,g.store_cate_ids) or g.`store_cate_ids` = '')";
//        if(!empty($_REQUEST['start_time']) && empty($_REQUEST['end_time'])){
//            $start_time = '';
//        }
//        if(empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
//            $end_time = '';
//        }
//        if(!empty($start_time) && !empty($end_time)){
//            if(strtotime($start_time) == strtotime($end_time)){
//                $start_time = strtotime($start_time.'00:00:00');
//                $end_time = strtotime($end_time.'23:59:59');
//                $where .=" and g.add_time >= '{$start_time}' and g.add_time <= '{$end_time}'";
//            }
//
//        }
//        if(!empty($start_time) && !empty($end_time)){
//            if(strtotime($start_time) > strtotime($end_time)){
//                $temp = $start_time;
//                $start_time = $end_time;
//                $end_time = $temp;
//            }
//        }
//        if(!empty($start_time) && !empty($_REQUEST['end_time'])){
//            if(strtotime($end_time) > strtotime($start_time)){
//                $start_time =strtotime($start_time.'00:00:00');
//                $end_time =strtotime($end_time.'23:59:59');
//                $where .=" and g.add_time >= '{$start_time}' and g.add_time <= '{$end_time}'";
//            }
//        }
        if (!empty($room_id)) {
            $where .= " and g.room_id = '{$room_id}'";
        }
        if (!empty($this->storeId)) {
            $store_data = $this->areaGoodMod->getAreaGood($this->storeId);
            if (!empty($store_data)) {
                $store_data = implode(',', $store_data);
                $where .= " and g.`goods_id` not in ({$store_data})";
            }
        }
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
        if (!empty($this->languageId)) {
            $where .= "  and gl.lang_id={$this->languageId}";
        }
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "goods " . $where;
        $totalCount = $this->storeMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;

        $where .= " order by `goods_id` desc";
        $sql = "select g.`goods_id`,gl.`goods_name`,g.`market_price`,g.`shop_price`,g.original_img, g.`cat_id`,g.`brand_id`,g.`goods_type`,gl.`lang_id` from " . DB_PREFIX . "goods as g left join 
        " . DB_PREFIX . "goods_lang as gl on  g.goods_id = gl.goods_id" . $where;
        $rs = $this->storeMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        foreach ($rs['list'] as $k => $v) {
            $rs['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
//            $rs['list'][$k]['sort'] = $total - $k - 20 * ($p - 1); //倒叙
        }
        $list_cate = $this->getCate();
        $this->assign('room_type', $this->getRoom());
        $this->assign('list_cate', $list_cate);
        $this->assign('list', $rs['list']);
        $this->assign('page', $rs['ph']);
        $this->assign('selectType', $select_type);
        $this->assign('type', $type);
        $this->assign('store', $this->getUseStore());
        $this->assign('act', 'index');
        $this->assign('lang_id', $this->lang_id);
        $this->assign('store_id', $this->storeId);
        $this->assign('room_id', $room_id);
        if ($this->lang_id == 1) {
            $good_type = G('good_type_en');
            $this->assign('good_type', $good_type);
            $this->display('areaGood/add_en.html');
        } else {
            $good_type = G('good_type');
            $this->assign('good_type', $good_type);
            $this->display('areaGood/add.html');
        }
    }

    /**
     * 获取业务类型数据
     * @author wanyan
     * @date 2017-09-12
     */
    public function getRoom() {
        $sql = "SELECT rt.id,rtl.type_name FROM " . DB_PREFIX . "room_type as rt LEFT JOIN " . DB_PREFIX . "room_type_lang as rtl ON rt.id = rtl.type_id WHERE rt.superior_id!=0 and rtl.lang_id = $this->languageId";
        $roomInfo = $this->roomTypeMod->querySql($sql);
        return $roomInfo;
    }

    /**
     * 获取品牌数据
     * @author wanyan
     * @date 2017-09-12
     */
    public function getBrand() {
        $sql = "select gc.id, gcl.brand_name as name from " . DB_PREFIX . "goods_brand as gc left join " . DB_PREFIX . "goods_brand_lang as gcl ON gc.id =  gcl.brand_id where gcl.lang_id = {$this->langId}";
        $rs = $this->storeMod->querySql($sql);
        return $rs;
    }

    /**
     * 获取商品类型
     * @author wanyan
     * @date 2017-09-12
     */
    public function getGoodsType() {
        $sql = "select gc.id, gcl.type_name as name from " . DB_PREFIX . "goods_type as gc left join " . DB_PREFIX . "goods_type_lang as gcl ON gc.id = gcl.type_id where gcl.lang_id = {$this->langId} and gc.mark =1";
        $rs = $this->storeMod->querySql($sql);
        return $rs;
    }

    /**
     * 获取启用的站点
     * @author wanyan
     * @date 2017-09-07
     */
    public function getUseStore() {
        $query = array(
            'cond' => "`is_open` = '1'",
            'fields' => '`id`,`store_name`'
        );
        $rs = $this->storeMod->getData($query);
        return $rs;
    }

    /**
     * 获取分类
     * @author wanyan
     * @date 2017-09-12
     */
    public function getCate() {
        $sql = "select gc.id, gcl.category_name as name,gc.parent_id from " . DB_PREFIX . "goods_category as gc left join " . DB_PREFIX . "goods_category_lang as gcl ON gc.id =  gcl.category_id where gcl.lang_id = {$this->langId}"; // 获取所有的分类
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
            $sql = "select gc.id, gcl.type_name as name from " . DB_PREFIX . "goods_type as gc left join " . DB_PREFIX . "goods_type_lang as gcl ON gc.id = gcl.type_id where gcl.lang_id = {$this->langId} and gc.mark =1";
            $rs = $this->storeMod->querySql($sql);
            echo json_encode($rs);
            die;
        } elseif ($id == 'cat_id') {
            $sql = "select gc.id, gcl.category_name as name,gc.parent_id from " . DB_PREFIX . "goods_category as gc left join " . DB_PREFIX . "goods_category_lang as gcl ON gc.id =  gcl.category_id where gcl.lang_id = {$this->langId}"; // 获取所有的分类
            $catRs = $this->storeMod->querySql($sql);
            $rs = $this->tree($catRs, $p_id = 0);
            $res['status'] = 1;
            $res['data'] = $rs;
            echo json_encode($res);
            die;
        } elseif ($id == 'brand_id') {
            $sql = "select gc.id, gcl.brand_name as name from " . DB_PREFIX . "goods_brand as gc left join " . DB_PREFIX . "goods_brand_lang as gcl ON gc.id =  gcl.brand_id where gcl.lang_id = {$this->langId}";
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
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $status = !empty($_REQUEST['status']) ? htmlspecialchars($_REQUEST['status']) : '0';
        $store_id = !empty($_REQUEST['store_id']) ? '' : $this->storeId;
        $good_id = !empty($_REQUEST['goods_id']) ? htmlspecialchars($_REQUEST['goods_id']) : '';
        $goods_id = array_filter(explode(',', $good_id));
        if ($status == 1) {
            foreach ($goods_id as $k => $v) {
                $query = array(
                    'cond' => " `goods_id` = '{$v}' and `store_id` = '{$store_id}' and `mark` =1",
                    'fields' => ' goods_id,`goods_name`'
                );
                $rs = $this->areaGoodMod->getOne($query);
                if ($rs['goods_id']) {
                    $this->setData($info = array(), $status = '0', $message = "<font color='red'>{$rs['goods_name']}</font>{$a['goods_name']}");
                }
                $language_id = $this->getLanguageById();
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
                        $this->setData($info = array(), $status = '0', $message = "<font color='red'>{$rs['goods_name']}</font>{$a['goods_name']}");
                    }
                    //  $country_ids = $this->getCountryId($store_id);
                    $language_ids = $this->getLanguageById();
                    $goods = $this->getGoodById($good_idv, $language_ids);
                    $insert_id[] = $this->doInsertData($store_idv, $goods, $language_ids);
                }
            }
        }

        $insert_id = array_filter($insert_id);

        if (count($insert_id)) {
            $info['url'] = "?app=areaGood&act=index&lang_id=$this->lang_id";
            $this->setData($info, $status = '1', $a['add_Success']);
        } else {
            $this->setData($info = array(), $status = '0', $a['add_fail']);
        }
    }

    /**
     * 获取商品信息
     * @author wanyan
     * @date 2017-09-12
     */
    public function getGoodById($good_id, $language_id) {
        $sql = "select g.attributes,g.goods_id,g.cat_id,g.goods_sn,gl.goods_name,g.goods_storage,g.goods_type,
        g.spec_type,g.brand_id,g.brand_name,g.shop_price,g.market_price,g.cost_price,gl.goods_remark,gl.goods_content,
        g.original_img,g.is_free_shipping,g.is_free_shipping,g.is_recommend,g.is_new,g.is_hot,g.suppliers_id,
        g.spu,g.sku,g.shipping_area_ids,g.on_time,g.style_id,g.room_id,g.lang_id,g.keywords,g.delivery_fee,g.deduction
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
            'goods_remark' => addslashes($goods['goods_remark']),
            'goods_content' => addslashes($goods['goods_content']),
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
            'add_time' => time(),
            'mark' => 1,
            'deduction'=>$goods['deduction'],
            'attributes'=>$goods['attributes'],
            'delivery_fee'=>$goods['delivery_fee'],
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
     * 改变状态
     * @author wanyan
     * @date 2018-03-12
     */
    public function checkStatus() {
        $good_id = !empty($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : '';
        $flag = !empty($_REQUEST['flag']) ? intval($_REQUEST['flag']) : '';
        $data = array(
            'selected' => $flag
        );
        $rs = $this->areaGoodMod->doEdit($good_id, $data);
        if ($rs) {
            $this->setData($info = array(), $status = 1, $message = "操作成功！");
        } else {
            $this->setData($info = array(), $status = 0, $message = "操作失败！");
        }
    }

    /**
     * 编辑商品信息
     * @author wanyan
     * @date 2017-09-13
     */
    public function edit() {
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '';
        $returnUrl=!empty($_REQUEST['returnUrl']) ? $_REQUEST['returnUrl'] : '';
        $this->assign('returnUrl',$returnUrl);
//        $areaGoodLangMod = &m('areaGoodLang');
        $query = array(
            'cond' => "`id` = '{$id}'"
        );
        $rs = $this->areaGoodMod->getOne($query);
        $catMod = &m('goodsClass');
        //三级分类
        $class = $catMod->getOne(array("cond" => "id=" . $rs['cat_id']));
        $cat_arr = explode("_", $class['parent_id_path']);
        $cat_list_1 = $catMod->getLangData(0, $this->defaulLang);
        $cat_list_2 = $catMod->getLangData($cat_arr[1], $this->defaulLang);
        $cat_list_3 = $catMod->getLangData($cat_arr[2], $this->defaulLang);
        $this->assign('cat_1', $cat_arr[1]);
        $this->assign('cat_2', $cat_arr[2]);
        $this->assign('cat_list_1', $cat_list_1);
        $this->assign('cat_list_class', $cat_list_1);
        $this->assign('cat_list_2', $cat_list_2);
        $this->assign('cat_list_3', $cat_list_3);
        $goodMod = &m('goods');
        $goodsClass = $goodMod->getOne(array("cond" => "goods_id=" . $rs['goods_id']));
        //业务类型
        $roomMod = &m('roomTypeCate');
        $sql = "select r.id,l.type_name as room_name from " . DB_PREFIX . "room_type as r
               left join " . DB_PREFIX . "room_category as c on r.id=c.room_type_id
               left join " . DB_PREFIX . "room_type_lang as l on l.type_id=r.id
               where c.category_id=" . $goodsClass['cat_id'] . " and l.lang_id=" . $this->defaulLang;
        $room_list = $roomMod->querySql($sql);
        $this->assign('room_list', $room_list);
        //辅助分类
        $auxiliary_arr = explode(":", $goodsClass['auxiliary_class']);
        foreach ($auxiliary_arr as $k => $v) {
            $cat_arrs = explode("_", $v);
            $auxiliary_class = $catMod->getOne(array("cond" => "id=" . $cat_arrs[3]));
            $cat_arre = explode("_", $auxiliary_class['parent_id_path']);
            $auxiliary_list_1 = $this->getCategoryLang($cat_arre[1], $this->defaulLang);
            $auxiliary_list_2 = $this->getCategoryLang($cat_arre[2], $this->defaulLang);
            $auxiliary_list_3 = $this->getCategoryLang($cat_arre[3], $this->defaulLang);
            $auxiliary[$k]['auxiliary_list'] = $auxiliary_list_1[0];
            $auxiliary[$k]['auxiliary_lists'] = $auxiliary_list_2[0];
            $auxiliary[$k]['auxiliary_liste'] = $auxiliary_list_3[0];
        }
        $this->assign('auxiliary', $auxiliary);
        $attributes_arr = explode(",", $rs['attributes']);
        foreach ($attributes_arr as $k => $v) {
            if($v ==1){
                $attributes_1=$v;
            }else if($v ==2){
                $attributes_2=$v;
            }else if($v ==3){
                $attributes_3=$v;
            }else if($v == 4){
                $attributes_4=$v;
            }
        }
//        print_r($attributes_arr);exit;
        $this->assign('attributes_1', $attributes_1);
        $this->assign('attributes_2', $attributes_2);
        $this->assign('attributes_3', $attributes_3);
        $this->assign('attributes_4', $attributes_4);
        //品牌
        $brandMod = &m('goodsBrand');
        $brand_list = $brandMod->getLangData($this->defaulLang);
        $this->assign('brand_list', $brand_list);
        //风格
        $styleMod = &m('goodsStyle');
        $style_list = $styleMod->getLangData($this->defaulLang);
        $this->assign('style_list', $style_list);
        //关键字
        $GoodLangMod = &m('goodsLang');
        $sql = "select keywords from " . DB_PREFIX . "goods_lang  where goods_id=" . $rs['goods_id'] . " and lang_id=" . $this->defaulLang;
        $keywords = $GoodLangMod->querySql($sql);
        $this->assign('keywords', $keywords[0]);
//        $re_lang = $areaGoodLangMod->getOne(array("cond" => "store_good_id=" . $id . " and lang_id=" . $this->langId));
//        if ($re_lang) {
//            $rs['goods_name'] = $re_lang['goods_name'];
//            $rs['goods_remark'] = $re_lang['goods_remark'];
//            $rs['keywords'] = $re_lang['keywords'];
//            $rs['goods_content'] = $re_lang['goods_content'];
//        }
        $itemList = $this->goodItemPriceMod->getData(array("cond" => "store_goods_id=" . $id));
        if(!empty($itemList)){
            $disabled=0;
        }else{
            $disabled=1;
        }



        //获取当前币种
        $currencyInfo = $this->currencyMod->getCurrencyById($this->storeId);
        //商品规格处理
        foreach ($itemList as $k => $v) {
        }
        //end
        $this->assign('disabled',$disabled);
        $this->assign('currencyInfo', $currencyInfo);
        $this->assign('lang_id', $this->langId);
        $this->assign('act', 'index');

        $this->assign('goodInfo', $rs);
        $this->assign('p', $p);

        $this->assign('deduction',$rs['deduction']);
        $this->assign('itemList', $itemList);
        $this->assign('lang_id', $this->lang_id);
        if ($this->lang_id == 1) {
            $this->display('areaGood/edit_en.html');
        } else {
            $this->display('areaGood/edit.html');
        }
    }

    /**
     * 获取分类名称
     * @author wang'shuo
     * @date 2018-4-03
     */
    public function getCategoryLang($id, $lang) {
        $catlangMod = &m('goodsClassLang');
        $sql = "select `category_id`,`category_name` from " . DB_PREFIX . "goods_category_lang where `category_id`='{$id}' and lang_id = '{$lang}'";
        $rs = $catlangMod->querySql($sql);
        return $rs;
    }

    /**
     * 改变是否总部状态
     * @author zhangr
     * @date 2017-9-05
     */
    public function getStatus() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $id = $_REQUEST['id'];
        $is_on_sale = $_REQUEST['is_on_sale'];
        $data = array(
            'is_on_sale' => $is_on_sale
        );
        $rs = $this->areaGoodMod->doEdit($id, $data);
        if ($rs) {
            $this->setData($info = array(), $status = '1', $message = '');
        } else {
            $this->setData($info = array(), $status = '0', $a['Enable_failure']);
        }
    }

    /**
     * 批量推荐
     * @author wanyan
     * @date 2018-01-01
     */
    public function doRecommend() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $goods_id = !empty($_REQUEST['good_ids']) ? $_REQUEST['good_ids'] : '';
        $flag = !empty($_REQUEST['flag']) ? $_REQUEST['flag'] : '';
        foreach ($goods_id as $k => $v) {
            $rs = $this->areaGoodMod->getOne(array('cond' => "`id` = $v", 'fields' => 'is_recommend'));
            if ($rs['is_recommend'] == 1 && $flag == 1) {
                $this->setData($info['return'] = 'return', $status = 0, $a['unRecommend']);
            } elseif ($rs['is_recommend'] == 2 && $flag == 2) {
                $this->setData($info['return'] = 'return', $status = 0, $a['recommend']);
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
            $affact_id = $this->areaGoodMod->doMarkSpec($goods_id, 0);
            $this->setData($info = array(), $status = 1, $a['operation_ok']);
        } else {
            $this->setData($info = array(), $status = 0, $a['operation_no']);
        }
    }

    /**
     * 批量上下架
     * @author wanyan
     * @date 2018-01-01
     */
    public function doOnSale() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $goods_id = !empty($_REQUEST['good_ids']) ? $_REQUEST['good_ids'] : '';
        $flag = !empty($_REQUEST['flag']) ? $_REQUEST['flag'] : '';
        foreach ($goods_id as $k => $v) {
            $rs = $this->areaGoodMod->getOne(array('cond' => "`id` = $v", 'fields' => 'is_on_sale'));
            if ($rs['is_on_sale'] == 1 && $flag == 1) {
                $this->setData($info['return'] = 'return', $status = 0, $a['onSale']);
            } elseif ($rs['is_on_sale'] == 2 && $flag == 2) {
                $this->setData($info['return'] = 'return', $status = 0, $a['offSale']);
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
            import('redis.lib');
            $redis = new RedisCacheServer();
            $storeGoodsRedis = $redis->get('storeGoodsMod_relation');
            $goodsIds = explode(',', $goods_id);

            if ($flag == 1) {
                // 添加缓存信息 by xt 2019.03.04
                $storeGoodsMod = &m('storeGoods');

                foreach ($goodsIds as $item) {
                    $storeInfo = $storeGoodsMod->getGoodsSpec($item);
                    $storeGoodsRedis[$item] = $storeInfo;
                }
            } else {
                // 删除缓存信息 by xt 2019.03.04
                foreach ($goodsIds as $item) {
                    unset($storeGoodsRedis[$item]);
                }
            }

            //缓存数据
            $redis->set('storeGoodsMod_relation', $storeGoodsRedis);

            $this->areaGoodMod->doMarkSpec($goods_id, 0);
            $this->setData($info = array(), $status = 1, $a['operation_ok']);
        } else {
            $this->setData($info = array(), $status = 1, $a['operation_no']);
        }
    }

    /**
     * 单个更新商品价格库存
     * @author zhangr
     * @date 2017-9-05
     */
    public function doSPrice() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $id = $_REQUEST['id'];
        $shop_price = $_REQUEST['shop_price'];
        $goods_storage = $_REQUEST['goods_storage'];
        $data = array(
            'shop_price' => $shop_price,
            'goods_storage' => $goods_storage
        );
        $rs = $this->areaGoodMod->doEdit($id, $data);
        if ($rs) {
            $this->setData($info = array(), $status = '1', $a['edit_Success']);
        } else {
            $this->setData($info = array(), $status = '0', $a['edit_fail']);
        }
    }

    /*
     * 修改区域商品信息
     * @author lee
     * @date 2017-10-12 15:11:35
     */

    public function doInfo() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $lan_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0';
        $goods_name = !empty($_REQUEST['goods_name']) ? htmlspecialchars($_REQUEST['goods_name']) : '';
        $shop_price = !empty($_REQUEST['shop_price']) ? htmlspecialchars($_REQUEST['shop_price']) : 0;
        $goods_storage = !empty($_REQUEST['goods_storage']) ? htmlspecialchars($_REQUEST['goods_storage']) : 0;
        $market_price = !empty($_REQUEST['market_price']) ? htmlspecialchars($_REQUEST['market_price']) : 0;
        $id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : '';
        $item = !empty($_REQUEST['item']) ? $_REQUEST['item'] : '';
        $is_free_shipping = !empty($_REQUEST['is_free_shipping']) ? $_REQUEST['is_free_shipping'] : 2;

        $shipping_price = !empty($_REQUEST['shipping_price']) ? $_REQUEST['shipping_price'] : '';
        $returnUrl=!empty($_REQUEST['returnUrl'])  ? $_REQUEST['returnUrl'] : '';
        $attributes = ($_REQUEST['attributes']) ? $_REQUEST['attributes'] : '';
        $delivery_fee = 0;
        $attributes_str = '';
        for ($i=0;$i<count($attributes);$i++){
            $attributes_str .= $attributes[$i].",";
        }
        $attributes_str=substr($attributes_str,0,-1);

        // by xt
        if (in_array(2, explode(',', $attributes_str))) {
            $delivery_fee = $_REQUEST['delivery_fee'] ?: 0;
        }

        //修改区域商品主体信息
        $areaGoodMod = &m('areaGood');
        $areaGoodLangMod = &m('areaGoodLang');
        $sgtpMod = &m('storeGoodItemPrice');

        if (empty($goods_name) || empty($shop_price) || (empty($goods_storage) && $goods_storage<0)) {

            $this->setData($info = array(), $status = '2', $a['goods_Please']);
        }
        if ($is_free_shipping == 2 && empty($shipping_price)) {
            $this->setData($info = array(), $status = '2', $a['goods_Postage']);
        }

        if ($item) {
            foreach ($item as $k => $v) {
                $goods_num +=$v['goods_storage'];

            }
        }
//        if($goods_num>$goods_storage){
//            $this->setData($info=array(),$status = '2','规格项库存不允许超过总库存');
//        }
        if(empty($goods_num)){
            $goods_num = $goods_storage;
        }
        $info_g = array(
            'market_price' => $market_price,
            'shop_price' => $shop_price,
            'goods_storage' => $goods_num,
            'is_free_shipping' => $is_free_shipping,
            'shipping_price' => $shipping_price,
            'attributes'=>$attributes_str,
            'delivery_fee' => $delivery_fee,
        );
        $res1 = $areaGoodMod->doEdit($id, $info_g);

        //判断改语言ID是否存在记录
//        $info_l = array(
//            'goods_name' => $goods_name,
//            'goods_remark' => $goods_remark,
//            'keywords' => $keywords,
//            'goods_content' => $goods_content,
//            'store_good_id' => $id
//        );
//        $hasSql = "select id from " . DB_PREFIX . "store_goods_lang where store_good_id=" . $id . " and lang_id=" . $lang_id;
//        $has = $areaGoodMod->querySql($hasSql);
//        if ($has) {
//            $res2 = $areaGoodLangMod->doEdit($has[0]['id'], $info_l);
//        } else {
//            $info_l['lang_id'] = $lang_id;
//            $info_l['add_time'] = time();
//            $res2 = $areaGoodLangMod->doInsert($info_l);
//        }
        //规格处理
        if ($item) {
            foreach ($item as $k => $v) {
                $sgtpMod->doEdit($k, $v);
            }
        }
        if ($res1) {
            // 更新缓存信息  by xt 2019.03.04
            import('redis.lib');
            $redis = new RedisCacheServer();
            $storeGoodsRedis = $redis->get('storeGoodsMod_relation');

            $storeGoodsMod = &m('storeGoods');
            $storeGoodsRedis[$id] = $storeGoodsMod->getGoodsSpec($id);

            //缓存数据
            $redis->set('storeGoodsMod_relation', $storeGoodsRedis);


            $this->setData($info = array('url' => $returnUrl), $status = '1', $a['edit_Success']);
        } else {
            $this->setData($info = array(), $status = '2', $a['edit_fail']);
        }
    }

    /**
     * 批量更新价格库存
     * @author zhangr
     * @date 2017-9-05
     */
    public function doDPrice() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
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
            $this->setData($info = array(), $status = '1', $a['goods_Success']);
        } else {
            $this->setData($info = array(), $status = '0', $a['goods_fail']);
        }
    }

    /**
     * 删除商品区域商品
     * @author wanyan
     * @date 2017-9-13
     */
    public function dele() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $id = !empty($_REQUEST['id']) ? htmlspecialchars($_REQUEST['id']) : '';
        $rs = $this->areaGoodMod->doMark($id);
        if ($rs) {
            //删除对应规格区域商品规格
            //modify by lee @2017-9-19 10:01:18
            $storeGoodItemPriceMod = &m("storeGoodItemPrice");
            $storeGoodItemPriceMod->doMarkKey('store_goods_id', $id);
//            $sql = "delete from " . DB_PREFIX . "store_goods_spec_price where store_goods_id in (" . $id . ")";
//            $res = $this->areaGoodMod->sql_b_spec($sql);
            //end
            //删除对应语言信息
            $sql = "delete from " . DB_PREFIX . "store_goods_lang where store_good_id in (" . $id . ")";
            $this->areaGoodMod->sql_b_spec($sql);

            // 删除缓存信息 by xt 2019.03.04
            import('redis.lib');
            $redis = new RedisCacheServer();
            $storeGoodsRedis = $redis->get('storeGoodsMod_relation');

            $ids = explode(',', $id);

            foreach ($ids as $item) {
                unset($storeGoodsRedis[$item]);
            }

            //缓存数据
            $redis->set('storeGoodsMod_relation', $storeGoodsRedis);

            //end
            $this->setData($info = array(), $status = '1', $a['delete_Success']);
        } else {
            $this->setData($info = array(), $status = '0', $a['delete_fail']);
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
        $this->setData($rs, $status = 1, $message = '');
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
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $id = $_REQUEST['id'];
        $is_on_sale = $_REQUEST['is_on_sale'];
        $data = array(
            'is_on_sale' => $is_on_sale,
            'is_recom' => 0
        );
        $rs = $this->areaGoodMod->doEdit($id, $data);
        if ($rs) {
            import('redis.lib');
            $redis = new RedisCacheServer();
            $storeGoodsRedis = $redis->get('storeGoodsMod_relation');

            if ($is_on_sale == 1) {
                // 添加缓存信息 by xt 2019.03.04
                $storeGoodsMod = &m('storeGoods');
                $storeInfo = $storeGoodsMod->getGoodsSpec($id);
                $storeGoodsRedis[$id] = $storeInfo;
//                array_push($storeGoodsRedis, $storeInfo);

            } else {
                // 删除缓存信息 by xt 2019.03.04
                $ids = explode(',', $id);

                foreach ($ids as $item) {
                    unset($storeGoodsRedis[$item]);
                }
            }

            //缓存数据
            $redis->set('storeGoodsMod_relation', $storeGoodsRedis);

            $this->setData($info = array(), $status = 1, $message = '');
        } else {
            $this->setData($info = array(), $status = 0, $a['goods_Recommend']);
        }
    }

    /*
     * 是否推荐
     * @author wanyan
     * @date 2017-09--20
     */

    public function changeRecommend() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $id = $_REQUEST['id'];
        $is_recommend = $_REQUEST['is_recommend'];
        $data = array(
            'is_recommend' => $is_recommend
        );
        $rs = $this->areaGoodMod->doEdit($id, $data);
        if ($rs) {
            $this->setData($info = array(), $status = 1, $message = '');
        } else {
            $this->setData($info = array(), $status = 0, $a['goods_Recommend']);
        }
    }

    public function changeRecom() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $id = $_REQUEST['id'];
        $is_recom = $_REQUEST['is_recom'];
        if ($is_recom == 1) {
            $sql = 'select count(is_recom) as num_recom from ' . DB_PREFIX . 'store_goods where is_recom=' . $is_recom;
            $res = $this->areaGoodMod->querySql($sql);
            if ($res[0]['num_recom'] > 3) {
                $this->setData($info = array(), $status = 0, $a['WeChat_home']);
            }
        }
        $data = array(
            'is_recom' => $is_recom
        );
        $rs = $this->areaGoodMod->doEdit($id, $data);
        if ($rs) {
            $this->setData($info = array(), $status = 1, $message = '');
        } else {
            $this->setData($info = array(), $status = 0, $a['goods_Recommend']);
        }
    }

    /*
     * 是否推荐
     * @author wanyan
     * @date 2017-09-20
     */

    public function changeFree() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $id = $_REQUEST['id'];
        $is_free_shipping = $_REQUEST['is_free_shipping'];
        $data = array(
            'is_free_shipping' => $is_free_shipping
        );
        $rs = $this->areaGoodMod->doEdit($id, $data);
        if ($rs) {
            $this->setData($info = array(), $status = 1, $message = '');
        } else {
            $this->setData($info = array(), $status = 0, $a['goods_Recommend']);
        }
    }

        /*
     * 是否推荐
     * @author wanyan
     * @date 2017-09-20
     */

    public function changeHot() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $id = $_REQUEST['id'];
        $is_hot = $_REQUEST['is_hot'];
        $data = array(
            'is_hot' => $is_hot
        );
        $rs = $this->areaGoodMod->doEdit($id, $data);
        if ($rs) {
            $this->setData($info = array(), $status = 1, $message = '');
        } else {
            $this->setData($info = array(), $status = 0, $a['goods_Hot']);
        }
    }

    /*
     * 区间判断
     * @author lee
     * @date 2017-10-20 15:35:12
     */

    public function test() {
        $goodsPriceData['maxprice'] = 2355;
        $goodsPriceData['minprice'] = 10;
        $goodsPriceData['priceStr'] = "10,100,500,20,2355,2333,1600,3000,40,300";
        //算法:计算商品价格的七个区间
        $priceNumber = 7;
        $sprice = ceil(($goodsPriceData['maxprice'] - $goodsPriceData['minprice']) / $priceNumber);
        $firsetPrice = $goodsPriceData['minprice'];
        //接收七个区间的价格范围
        $_priceNumber = array();
        for ($i = 0; $i < $priceNumber; $i++) {
            if ($i < ($priceNumber - 1))
                $_priceNumber[] = (floor($firsetPrice / 10) * 10) . '-' . (floor(($firsetPrice + $sprice) / 10) * 10 - 1);
            else
                $_priceNumber[] = (floor($firsetPrice / 10) * 10) . '-' . ceil($goodsPriceData['maxprice'] / 10) * 10;

            $firsetPrice += $sprice;
        }
        //把从商品中取出来的价格字符串转化成数组后,
        $goodsPrice = explode(',', $goodsPriceData['priceStr']);
        sort($goodsPrice);
        //在价格区间中做比对，如果区间中有商品保存价格区间，否则删除
        foreach ($_priceNumber as $k => $v) {
            $a = explode('-', $v);
            $start = $a[0];
            $end = $a[1];
            $panduan = array();
            foreach ($goodsPrice as $k1 => $v1) {
                $v1 = floor($v1);
                //价格在此区间，把该价格保存在数组中
                if ($v1 >= $start && $v1 <= $end)
                    $panduan[] = $v1;
            }
            //如果取出的商品没有在此价格区间的，删除该区间范围
            if (empty($panduan))
                unset($_priceNumber[$k]);
        }
        print_r($_priceNumber);
    }



        /**
     * 店铺的小程序二维码
     * @author tangp
     * @date 2018-10-25
     */
    public function getStoreXcxCode()
    {
        set_time_limit(0);
        $userMod = &m('user');
        $page = $_REQUEST['page'] ? $_REQUEST['page'] : 1;
        $sql = 'SELECT id,xcx_code_url FROM bs_user WHERE mark = 1 and  xcx_code_url is NOT NULL ORDER BY id ASC LIMIT ' . ($page-1)*150 . ',150';
        $list = $userMod->querySql($sql);
//        $list = $userMod->getData(array('fields'=>'id,xcx_code_url','cond'=>'mark = 1 and  xcx_code_url is NOT NULL','limit'=>'0,150','order_by'=>'id ASC'));
         echo "<pre/>";
         var_dump($list);die;
//         echo $access_token = $this->getAccessToken();die;
        $access_token = '24_Dyp4VLwwEUfwaW794NrLviAMW7D0C7mZ4USQbvGHDcvHDgGMbE_PLaGNBtuVIDrpMXtZkNrLyq988h3J35Tm1IIIeLZvZdZwuMMgVmEOcvYMhziYT8qsmE-rFftEfFqaxSIoIl1jpyy4_e7mEUWiAAAQGI';
        $ids = array();
        foreach ($list as $key => $value) {
            $user_id = $value['id'];
            $post_data = json_encode(array(
                "width" => 280,
                "scene" => "$user_id",
                "page"  => "pages/register/register"
            ));
            // $access_token = $this->getAccessToken();
            // 为二维码创建一个文件
            $mainPath = ROOT_PATH . '/upload/user/xcxCode';
            if(!is_dir($mainPath)){
                mkdir($mainPath,0777,true);
            }
            $timePath = date('Ymd');
            $savePath = $mainPath . '/' . $timePath;
            if(!is_dir($savePath)){
                mkdir($savePath,0777,true);
            }
            $newFileName = $value['id'] . ".png";
            $filename = $savePath . '/' . $newFileName;
            $pathName = 'upload/user/xcxCode/' . $timePath . '/' . $newFileName;
            $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$access_token;
            $result = $this->httpRequest($url,$post_data,'POST');
            if(array_key_exists('errcode',json_decode($res,true))){
                array_push($ids,$value['id']);
            }
            $res = file_put_contents($pathName,$result);
            $urldata = array(
                'cond' => 'id = ' . $value['id'],
                'set' => "xcx_code_url='" . $pathName . "'",
            );
            $resss = $userMod->doUpdate($urldata);
                ob_flush();
    flush();
    ob_end_clean();

        }
        var_dump($ids);
    }

    /**
     * 读取access_token
     */
    public function getAccessToken()
    {
        $appid = 'wxd483c388c3d545f3';
        $secret = 'd19b0561679a32122f10d524153f7ea5';
        return $this->getNewToken($appid,$secret);
    }

    /**
     * 获取微信accesstoken
     * @param $appid
     * @param $secret
     * @return mixed
     */
    public function getNewToken($appid,$secret)
    {
        $tokenUrl = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $secret;
        $access_token_arr = $this->httpRequest($tokenUrl);
        $access = json_decode($access_token_arr,true);
        return $access['access_token'];
    }

        /**
     * curl方法
     * @param $url
     * @param string $data
     * @param string $method
     * @return mixed
     */
    public function httpRequest($url, $data='', $method='GET'){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        if($method=='POST')
        {
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data != '')
            {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
        }

        curl_setopt($curl, CURLOPT_TIMEOUT, 120);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }
}
