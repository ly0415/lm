<?php

/**
 * 代客下单
 * @author  luffy
 * @date    2016-08-20
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class replaceOrderApp extends BaseStoreApp {
    private $lang_id;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();

        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0';
    }

    /**
     * 代客下单
     * @author wangshuo
     * @date 2018-5-10
     */
    public function index() {
        $storeid = $this->storeId;
        $ctgMod = &m('goodsClass');
        $sql = 'SELECT  c.id,l.`category_name`,c.`parent_id`  FROM  ' . DB_PREFIX . 'goods_category AS c
                 LEFT JOIN  ' . DB_PREFIX . 'goods_category_lang AS l  ON c.`id` = l.`category_id`
                 WHERE c.`parent_id` = 0  AND  l.`lang_id` =' . $this->defaulLang;
        $res = $ctgMod->querySql($sql);
        //echo '<pre>';
        //var_dump($res);
        $bussSql = "SELECT buss_id FROM " . DB_PREFIX . 'store_business WHERE  store_id=' . $storeid;
        $bussData = $ctgMod->querySql($bussSql);
//        echo '<pre>';
//        var_dump($bussData);
        foreach ($bussData as $key => $val){
            $buId[] = $val['buss_id'];
        }
        $buIds = implode(',',$buId);
        //获取业务类型
        $naicha = $this->getRoomType($this->languageId,$buIds);
//        echo '<pre>';
//        var_dump($naicha);
        $storeGoodsMod = &m('areaGood');
        //获取二级业务类型
        $roomtypearr = $this->getNextType($this->languageId,$naicha[0]['id']);
//        echo '<pre>';
//        var_dump($roomtypearr);
        foreach ($roomtypearr as $key => $val){
            $where = ' where s.store_id = ' . $storeid . ' and rc.room_type_id = ' . $val['id'] . ' and s.mark=1 and s.is_on_sale = 1 and l.lang_id = ' . $this->languageId;
            //查出子类的商品
            $rsql = 'SELECT s.id,rc.sort,s.`goods_id`,s.`cat_id`,s.`store_id`,l.`goods_name`,l.`lang_id`,s.`shop_price`,s.`market_price`,s.`brand_id`,gl.`original_img`,s.is_free_shipping,s.add_time,l.goods_remark
                FROM  ' . DB_PREFIX . 'store_goods AS s LEFT JOIN  '
                . DB_PREFIX . 'room_category AS rc ON s.cat_id=rc.category_id LEFT JOIN '
                . DB_PREFIX . 'goods_lang AS l ON s.goods_id = l.goods_id  LEFT JOIN '
                . DB_PREFIX . 'goods AS gl ON s.goods_id = gl.goods_id ' . $where;
//            echo $rsql;
            $roomtypearr[$key]['goods'] = $storeGoodsMod->querySql($rsql);
//            echo '<pre>';
//            var_dump($roomtypearr[$key]['goods']);
            foreach ($roomtypearr[$key]['goods'] as &$item) {
                $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $this->storeId;
                $store_arr = $storeGoodsMod->querySql($store_sql);
                echo '<pre>';
                var_dump($store_arr);
                $item['shop_price'] = number_format($item['shop_price'] * $store_arr[0]['store_discount'], 2);
//                echo '<pre>';
//                var_dump($item['shop_price']);
            }
        }

    }
    //获取业务类型
    public function getRoomType($langid,$buIds)
    {
        $lang_id = $this->lang_id;
        if ($lang_id == 1) {
            $langid = 30;
        } else {
            $langid = $this->languageId;
        }
        $roomTypeMod = &m('roomType');
        if (!empty($buIds)) {
            $where = '    where  t.superior_id=0 and  l.`lang_id`  = ' . $langid . ' and t.id in(' . $buIds . ')';
        } else {
            $where = '    where  t.superior_id=0 and  l.`lang_id`  = ' . $langid;
        }
//        var_dump($langid);
//        var_dump($this->langid);
        if ($lang_id == 0) {
            $sql = 'SELECT  t.`id`,l.`type_name`,t.`room_img`  FROM  ' . DB_PREFIX . 'room_type AS t
                 LEFT JOIN  ' . DB_PREFIX . 'room_type_lang  AS  l  ON  t.`id` = l.`type_id`  ' . $where . ' and l.type_name like "%奶茶%"  order by t.sort';
        } else {
            $sql = 'SELECT  t.`id`,l.`type_name`,t.`room_img`  FROM  ' . DB_PREFIX . 'room_type AS t
                 LEFT JOIN  ' . DB_PREFIX . 'room_type_lang  AS  l  ON  t.`id` = l.`type_id`  ' . $where . ' and  l.type_name like "%Tea%"  order by t.sort';
        }
        $data = $roomTypeMod->querySql($sql);
        return $data;
    }
    //获取2级业务类型
    public function getNextType($langid,$rtid)
    {
        $roomTypeMod = &m('roomType');
        if (!empty($langid)) {
            $where = '    where  t.superior_id=' . $rtid . ' and  l.`lang_id`  = ' . $langid;
        } else {
            $where = '    where  t.superior_id=' . $rtid . ' and  l.`lang_id`  = ' . $this->mrlangid;
        }
        $sql = 'SELECT  t.`id`,l.`type_name`,t.`room_adv_img`  FROM  ' . DB_PREFIX . 'room_type AS t
                 LEFT JOIN  ' . DB_PREFIX . 'room_type_lang  AS  l  ON  t.`id` = l.`type_id`  ' . $where . ' order by t.sort';
        $data = $roomTypeMod->querySql($sql);
        return $data;
    }
}
