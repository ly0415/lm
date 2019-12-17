<?php
/**
 * 商品列表接口控制器
 * @author: gao
 * @date: 2018-08-14
 */
class GoodsListApp extends BasePhApp
{
    private $goodsCommentMod;
    private $footPrintMod;
    private $cartMod;
    private $areaGoodMod;
    private $storeGoodItemPriceMod;
    private $goodsSpecPriceMod;
    private $goodsMod;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->goodsCommentMod = &m('goodsComment');
        $this->footPrintMod = &m('footprint');
        $this->cartMod = &m('cart');
        $this->areaGoodMod = &m('areaGood');
        $this->storeGoodItemPriceMod = &m('storeGoodItemPrice');
        $this->goodsSpecPriceMod=&m('goodsSpecPrice');
        $this->goodsMod=&m('goods');
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
    }

    /**
     * 更多推荐商品列表/限时优惠接口
     * @author: luffy
     * @date  : 2018-08-15
     */
    public function index()
    {
        $select_1 = !empty($_REQUEST['select_1']) ? intval($_REQUEST['select_1']) : 0; //新品
        $select_2 = !empty($_REQUEST['select_2']) ? intval($_REQUEST['select_2']) : 0; //优惠
        $select_3 = !empty($_REQUEST['select_3']) ? intval($_REQUEST['select_3']) : 0; //价格
        $select_4 = !empty($_REQUEST['select_4']) ? intval($_REQUEST['select_4']) : 0; //商品类型
        $select_5 = !empty($_REQUEST['select_5']) ? intval($_REQUEST['select_5']) : 0; //价格区间最小
        $select_6 = !empty($_REQUEST['select_6']) ? intval($_REQUEST['select_6']) : 99999999;  //价格区间最大
        $storeGoodsMod = &m('areaGood');
        $goodsList = $storeGoodsMod->getGoodsList(array(
            'store_id' => $this->store_id,
            'lang_id' => $this->lang_id,
            'shorthand' => $this->shorthand,
//            'is_recom' => 1,           //推荐
            'select_1' => $select_1,
            'select_2' => $select_2,
            'select_3' => $select_3,
            'select_4' => $select_4,
            'select_5' => $select_5,
            'select_6' => $select_6,
        ));
        if ($goodsList) {
            $this->setData($goodsList, 1);
        }
    }

    /**
     * 限时优惠商品列表接口
     * @author: luffy
     * @date  : 2018-08-15
     */
    public function timeLimitGoods()
    {
        $storeGoodsMod = &m('areaGood');
        $goodsList = $storeGoodsMod->getGoodsList(array(
            'store_id' => $this->store_id,
            'lang_id' => $this->lang_id,
            'shorthand' => $this->shorthand,
            'time_limit' => 1,
        ));
        if ($goodsList) {
            $this->setData($goodsList, 1);
        }
    }

    /**
     * 商品类型接口
     * @author gao
     * @date 2018/08/14
     */
    public function getFilter()
    {
        $goodsbandMod = &m('goodsBrand');
        $langData = array(
            $this->langData->project->height_price,
            $this->langData->project->low_price,
            $this->langData->project->goods_type,
            $this->langData->project->price_range,
            $this->langData->public->reset,
            $this->langData->public->complete,
            $this->langData->public->screen,
        );
        $where = '    where  l.`lang_id`  = ' . $this->lang_id;
        $bsql = 'SELECT  b.id,l.`brand_name`as bname  FROM  ' . DB_PREFIX . 'goods_brand AS b
                LEFT JOIN  ' . DB_PREFIX . 'goods_brand_lang AS l ON b.id=l.`brand_id`  ' . $where;
        $brandData = $goodsbandMod->querySql($bsql);
        $data = array('langData' => $langData, 'brandData' => $brandData);
        if ($brandData) {
            $this->setData($data, '1', '');
        }
    }


    /**
     * 二级分类页面接口
     * @author gao
     * @date 2018/08/14
     */
    public function ctgPage()
    {
        $oneCategory = &m('goodsCategory');
        $ctglev1 = $oneCategory->getOneCategory();

        $ctglev1 = array_values($ctglev1);
        //获取第一级分类数据
        $threeChilds = $oneCategory->getRelationDatas();
        reset_arr_key($threeChilds);

        $ctglev1 = array();
        foreach ($threeChilds as $key => $value) {
            $t = $value['childs'];
            unset($value['childs']);
            $value['childsCtg'] = $t;
            $ctglev1[] = $value;
        }

        if ($ctglev1) {
            $this->setData($ctglev1, '1', '');
        }
    }

    //获取三级分类
//    public function getThreeChilds($langid, $pid) {
//        $ctgMod = &m('goodsClass');
//        $and = '    AND  l.`lang_id`  = ' . $langid;
//        $sql = 'SELECT c.id,c.`parent_id`,c.`parent_id_path`,l.`category_name`,l.`lang_id`
//                FROM  ' . DB_PREFIX . 'goods_category  AS c  LEFT JOIN  ' . DB_PREFIX . 'goods_category_lang AS l  ON c.id=l.`category_id`
//                WHERE   c.parent_id = ' . $pid . $and . ' ORDER BY  c.sort_order';
//        $data = $ctgMod->querySql($sql);
//        foreach ($data as $key => $val) {
//            $sql_1 = "select l.id,l.category_name,l.category_id,c.image,c.adv_img,c.parent_id_path,c.parent_id from " . DB_PREFIX . "goods_category as c left join "
//                . DB_PREFIX . "goods_category_lang as l on c.id = l.`category_id` where c.parent_id = {$val['id']} " . $and . " ORDER BY  c.sort_order";
//            $data[$key]['childs'] = $ctgMod->querySql($sql_1);
//        }
//        return $data;
//    }


    /**
     * 二级业务页面接口
     * @author gao
     * @date 2018/08/15
     */
   public function goodsList()
    {
        $storeGoodsMod = &m('areaGood');
        $rtid = !empty($_REQUEST['rtid']) ? $_REQUEST['rtid'] : 83;
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : 29;
         $langData = array(
//            $this->langData->project->preferential_goods,
//            $this->langData->project->hot_selling_goods,
            $this->langData->project->monthly_sale,
            $this->langData->project->select_spec,
        );

        $storeGoodsMod=&m('storeGoods');
        $data=$storeGoodsMod->getStoreGoods($storeid,$rtid,$lang,$this->userId);
        $goods=$data['goods']; //业务类型商品
        $room=$data['room'];  //业务类型\
        $hotGoods=$data['hotGoods']; //热销商品
        $sqls = 'select * from bs_config  where  inc_type = "shop_info" ';
        $datas = $storeGoodsMod->querySql($sqls);
        $res = array();
        foreach ($datas as $key => $val) {
            $res[$val['name']] = $datas[$key];
        }
        $store_sql = 'select store_discount,background_img from  ' . DB_PREFIX . 'store where id=' . $storeid;
        $store_arr = $storeGoodsMod->querySql($store_sql);
        $data = array(
            'res' => $res,
            'langData' => $langData,
            'activeData' => $activiData,
            'roomtypData' => $room,
            'hotGoods' => $hotGoods,
            'symbol' => $this->symbol,
            'storeData' => $store_arr
        );
        if ($data) {
            $this->setData($data, '1', '');
        }
    }
//    public function goodsList()
//    {
//        $storeGoodsMod = &m('areaGood');
//        $rtid = !empty($_REQUEST['rtid']) ? $_REQUEST['rtid'] : 83;
//        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
//        $langData = array(
////            $this->langData->project->preferential_goods,
////            $this->langData->project->hot_selling_goods,
//            $this->langData->project->monthly_sale,
//            $this->langData->project->select_spec,
//        );
//        $roomtypearr = $this->getgoodRoomTypearr($this->lang_id, $rtid);
//
//        foreach ($roomtypearr as $kk => $vv) {
//            $roomId[] = $vv['id'];
//        }
//        $roomIds = implode(',', $roomId);
//        $hotGoods = $this->getHotGoods($roomIds, $this->store_id);
//
//        foreach ($roomtypearr as $key => $val) {
//            $where = '  where   s.store_id =' . $this->store_id . '   and rc.room_type_id = ' . $val['id'] . '  and   s.mark=1   and   s.is_on_sale =1  AND l.`lang_id` = ' . $this->lang_id;
//            //所以子类的商品
//            $rsql = 'SELECT s.id,rc.sort,s.`goods_id`,s.`cat_id`,s.`store_id`,l.`goods_name`,l.`lang_id`,s.`shop_price`,s.`market_price`,s.`brand_id`,gl.`original_img`,s.is_free_shipping,s.add_time,l.goods_remark,s.goods_storage
//                FROM  ' . DB_PREFIX . 'store_goods AS s LEFT JOIN  '
//                . DB_PREFIX . 'room_category AS rc on s.cat_id=rc.category_id LEFT JOIN '
//                . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id`  left join '
//                . DB_PREFIX . 'goods as gl on s.goods_id = gl.goods_id ' . $where;
//            $roomtypearr[$key]['goods'] = $storeGoodsMod->querySql($rsql);
//        }
//        foreach ($roomtypearr as $k => $v) {
//            foreach ($v['goods'] as $k1 => $v1) {
//                //店铺商品打折
//                $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $this->store_id;
//                $store_arr = $storeGoodsMod->querySql($store_sql);
//                $roomtypearr[$k]['goods'][$k1]['shop_price'] = number_format($v1['shop_price'] * $store_arr[0]['store_discount'], 2);
//                $member_price = $v1['shop_price'] * $store_arr[0]['store_discount'] - ($this->getPointAccount($v1['shop_price'] * $store_arr[0]['store_discount'], $storeid));
//                $roomtypearr[$k]['goods'][$k1]['member_price'] = number_format($member_price, 2);
//                $roomtypearr[$k]['goods'][$k1]['sale_price'] = number_format($v1['market_price'], 2);
//                $oSql = "SELECT rec_id,goods_num  FROM " . DB_PREFIX . 'order_goods WHERE goods_id=' . $v1['id'] . " and order_state in (20,30,40,50)";
//                $oData = $storeGoodsMod->querySql($oSql);
//                if (!empty($oData)) {
//                    $sum = 0;
//                    foreach ($oData as $k2 => $v2) {
//                        $sum += $v2['goods_num'];
//                    }
//                    $roomtypearr[$k]['goods'][$k1]['order_num'] = $sum;
//                } else {
//                    $roomtypearr[$k]['goods'][$k1]['order_num'] = 0;
//                }
//                $goodsCommentMod = &m('goodsComment');
//                $sql = 'select  sum(goods_rank) / count(goods_rank) as res,count(*) as num from ' . DB_PREFIX . 'goods_comment where goods_id = ' . $v1['id'];
//                $trance = $goodsCommentMod->querySql($sql);
//                $roomtypearr[$k]['goods'][$k1]['rate'] = (int)$trance[0]['res'];
//                $roomtypearr[$k]['goods'][$k1]['num'] = $trance[0]['num'];
//            }
//        };
//        $activiData = $this->getACtivity($roomIds, $this->store_id);
//        foreach ($activiData['data'] as $key => $val) {
//            $oSql = "SELECT rec_id,goods_num FROM " . DB_PREFIX . 'order_goods WHERE goods_id=' . $val['gid'] . " and order_state in (20,30,40,50)";
//            $oData = $storeGoodsMod->querySql($oSql);
//            if (!empty($oData)) {
//                $num = 0;
//                foreach ($oData as $k => $v) {
//                    $num += $v['goods_num'];
//                }
//                $activiData['data'][$key]['order_num'] = $num;
//            } else {
//                $activiData['data'][$key]['order_num'] = 0;
//            }
//            $goodsCommentMod = &m('goodsComment');
//            $sql = 'select  sum(goods_rank) / count(goods_rank) as res,count(*) as num from ' . DB_PREFIX . 'goods_comment where goods_id = ' . $val['gid'];
//            $trance = $goodsCommentMod->querySql($sql);
//            $activiData['data'][$key]['rate'] = (int)$trance[0]['res'];
//        }
//        $sqls = 'select * from bs_config  where  inc_type = "shop_info" ';
//        $datas = $goodsCommentMod->querySql($sqls);
//        $res = array();
//        foreach ($datas as $key => $val) {
//            $res[$val['name']] = $datas[$key];
//        }
//        $store_sql = 'select store_discount,background_img from  ' . DB_PREFIX . 'store where id=' . $storeid;
//        $store_arr = $storeGoodsMod->querySql($store_sql);
//        $data = array(
//            'res' => $res,
//            'langData' => $langData,
//            'activeData' => $activiData,
//            'roomtypData' => $roomtypearr,
//            'hotGoods' => $hotGoods,
//            'symbol' => $this->symbol,
//            'storeData' => $store_arr
//        );
//        if ($data) {
//            $this->setData($data, '1', '');
//        }
//
//
//    }
    public function getAttributesList()
    {
        $storeGoodsMod = &m('areaGood');
        $rtid = !empty($_REQUEST['rtid']) ? $_REQUEST['rtid'] : 83;
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : 29;
        $type     = !empty($_REQUEST['type']) ? $_REQUEST['type'] : '';
        $ress = &m('storeGoods')->getTypeGoods($storeid,$rtid,$lang,$this->userId,$type);
        $goods=$ress['goods']; //业务类型商品
        $room=$ress['room'];  //业务类型
        $hotGoods=$ress['hotGoods'];
        $sqls = 'select * from bs_config  where  inc_type = "shop_info" ';
        $datas = $storeGoodsMod->querySql($sqls);
        $res = array();
        foreach ($datas as $key => $val) {
            $res[$val['name']] = $datas[$key];
        }
        $store_sql = 'select store_discount,background_img from  ' . DB_PREFIX . 'store where id=' . $storeid;
        $store_arr = $storeGoodsMod->querySql($store_sql);
        $data = array(
            'res' => $res,
//            'langData' => $langData,
            'activeData' => $activiData,
            'roomtypData' => $room,
            'hotGoods' => $hotGoods,
            'symbol' => $this->symbol,
            'storeData' => $store_arr
        );
        if ($data) {
            $this->setData($data, '1', '');
        }

    }
    //睿积分抵扣金额

    public function getPointAccount($total, $storeid)
    {
        $pointSiteMod = &m('point');
        $storePointMod = &m('storePoint');
        $storeMod = &m('store');
        $curMod = &m('currency');
        //获取订单总金额
        //获取最大积分支付比例
        $point_price_site = $pointSiteMod->getOne(array("cond" => "1=1"));
        $store_point_site = $storePointMod->getOne(array("cond" => "store_id=" . $storeid));
        if ($this->userId) {
            $userSql = "select rc.percent from " . DB_PREFIX . "user as u  LEFT JOIN  "
                . DB_PREFIX . "recharge_point AS rc on u.recharge_id=rc.id where u.id = " . $this->userId;
            $user_id = $storeMod->querySql($userSql);
            if (!empty($user_id[0]['percent'])) {
                $point_price_site['point_price'] = $user_id[0]['percent'] + $store_point_site['point_price'];
            } else {
                $point_price_site['point_price'] = empty($store_point_site) ? 0 : $store_point_site['point_price'];
            }
        } else {
            $point_price_site['point_price'] = empty($store_point_site) ? 0 : $store_point_site['point_price'];
        }
        if ($point_price_site) {
            $point_price = $point_price_site['point_price'] * $total / 100; //积分兑换最大金额
            $rmb_point = $point_price_site['point_rate']; //积分和RMB的比例
        } else {
            $point_price = 0;
            $rmb_point = 0;
        }
//获取当前店铺币种以及兑换比例
        $store_info = $storeMod->getOne(array("cond" => "id=" . $storeid));
//获取当前币种和RMB的比例
        $rate = $curMod->getCurrencyRate($store_info['currency_id']);
        return $point_price;
    }

    /**
     * 2级业务类型
     */
    public function getgoodRoomTypearr($langid, $rtid)
    {
        $roomTypeMod = &m('roomType');
        if (!empty($langid)) {
            $where = '    where  t.superior_id=' . $rtid . ' and  l.`lang_id`  = ' . $langid;
        } else {
            $where = '    where  t.superior_id=' . $rtid . ' and  l.`lang_id`  = ' . $this->lang_id;
        }
        $sql = 'SELECT  t.`id`,l.`type_name`,t.`room_adv_img`  FROM  ' . DB_PREFIX . 'room_type AS t
                 LEFT JOIN  ' . DB_PREFIX . 'room_type_lang  AS  l  ON  t.`id` = l.`type_id`  ' . $where . ' order by t.sort';
        $data = $roomTypeMod->querySql($sql);
        return $data;
    }


    //热销商品
    public function getHotGoods($roomIds, $storeid)
    {
        $storeGoodsMod = &m('areaGood');
        $where = '  where     s.store_id =' . $storeid . '   and rc.room_type_id in (' . $roomIds . ')  and   s.mark=1    and   s.is_on_sale =1  AND l.`lang_id` = ' . $this->lang_id;
        //所以子类的商品
        $hsql = 'SELECT s.id,s.`goods_id`,l.`goods_name`,l.`lang_id`,l.goods_remark,s.`shop_price`,s.`market_price`,gl.`original_img`
                FROM  ' . DB_PREFIX . 'store_goods AS s LEFT JOIN  '
            . DB_PREFIX . 'room_category AS rc on s.cat_id=rc.category_id LEFT JOIN '
            . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id`  left join '
            . DB_PREFIX . 'goods as gl on s.goods_id = gl.goods_id' . $where;
        $hotData = $storeGoodsMod->querySql($hsql);

        foreach ($hotData as $k => $v) {
            $oSql = "SELECT rec_id,goods_num  FROM " . DB_PREFIX . 'order_goods WHERE goods_id=' . $v['id'] . " and order_state in (20,30,40,50)";
            $oData = $storeGoodsMod->querySql($oSql);
            if (!empty($oData)) {
                $sum = 0;
                foreach ($oData as $k1 => $v1) {
                    $sum += $v1['goods_num'];
                }
                $hotData[$k]['goods_num'] = $sum;
            } else {
                $hotData[$k]['goods_num'] = 0;
            }
            $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $storeid;
            $store_arr = $storeGoodsMod->querySql($store_sql);
            $member_price = $v['shop_price'] * $store_arr[0]['store_discount'] - ($this->getPointAccount($v['shop_price'] * $store_arr[0]['store_discount'], $storeid));
            $hotData[$k]['member_price'] = number_format($member_price, 2);
            $hotData[$k]['shop_price'] = number_format($v['shop_price'] * $store_arr[0]['store_discount'], 2);
            $hotData[$k]['sale_price'] = number_format($v['market_price'], 2);
        }
        $sort = array(
            'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
            'field' => 'goods_num', //排序字段
        );
        $arrSort = array();
        foreach ($hotData AS $uniqid => $row) {
            foreach ($row AS $key => $value) {
                $arrSort[$key][$uniqid] = $value;
            }
        }
        if ($sort['direction']) {
            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $hotData);
        }
        $hotData = array_slice($hotData, 0, 4);
        return $hotData;
    }


    //活动商品
    public function getACtivity($roomIds, $storeid)
    {
        //秒杀商品
        $seckMod = &m('spikeActivity');
        $goodsByMod = &m('groupbuy');
        $goodPromMod = &m('goodProm');
        $storeGoodsMod = &m('areaGood');
        $curtime = time();
        $today = strtotime(date('Y-m-d', time()));
        $now = $curtime - $today;
        $where1 = 'GROUP BY s.id   HAVING s.store_id =' . $storeid . '  and  ' . $curtime . ' > stime  AND   etime > ' . $curtime . ' and rc.room_type_id in (' . $roomIds . ') and g.is_on_sale =1 and g.mark=1 ';
        $sql1 = 'SELECT  s.id as cid,s.`name`,s.start_time,s.end_time,s.start_our,s.end_our,s.store_id,s.store_goods_id as id,gl.original_img,s.content,s.item_name,s.item_key,s.discount,s.o_price,s.price,s.goods_num,g.is_free_shipping,(s.start_time+s.start_our) as stime,(s.end_time+s.end_our) as etime,l.goods_remark,gl.goods_id as good_id,g.id as gid,rc.room_type_id,g.is_on_sale,g.mark FROM  '
            . DB_PREFIX . 'spike_activity as s left join '
            . DB_PREFIX . 'store_goods as g on  s.store_goods_id = g.id  LEFT JOIN  '
            . DB_PREFIX . 'room_category AS rc on g.cat_id=rc.category_id LEFT JOIN '
            . DB_PREFIX . 'goods AS gl ON g.`goods_id` = gl.`goods_id` LEFT JOIN  bs_goods_lang AS l ON g.`goods_id` = l.`goods_id`  ' . $where1;
        $spikeArr = $seckMod->querySql($sql1);
        foreach ($spikeArr as $key => $val) {
            $spikeArr[$key]['source'] = 1;
            $child_info = $storeGoodsMod->getLangInfo($val['id'], $this->lang_id);
            if ($child_info) {
                $k_name = $child_info['goods_name'];
                $spikeArr[$key]['goods_name'] = $k_name;
            }
            $member_price = $val['price'] - ($this->getPointAccount($val['price'], $storeid));
            $spikeArr[$key]['member_price'] = number_format($member_price, 2);

        }

        //团购商品
        $where3 = 'WHERE  l.`lang_id` = ' . $this->lang_id . '  and  b.store_id =' . $this->store_id . '  AND b.is_end =1 AND b.mark = 1  and rc.room_type_id in (' . $roomIds . ') and g.is_on_sale=1 and g.mark=1 ';
        $sql3 = 'SELECT b.title as name, b.id as cid,b.goods_id as id,b.store_id,b.end_time,b.group_goods_price as price,b.virtual_num,gl.original_img,b.goods_price as o_price,l.goods_name,b.goods_spec_key as item_key,gl.goods_id as good_id,g.id as gid
                FROM  bs_goods_group_buy  AS b  LEFT JOIN   bs_store_goods AS g ON b.`goods_id` = g.id
                LEFT JOIN  bs_goods_lang AS l ON g.`goods_id` = l.`goods_id`   LEFT JOIN  '
            . DB_PREFIX . 'room_category AS rc on g.cat_id=rc.category_id LEFT JOIN '
            . DB_PREFIX . 'goods AS gl ON g.`goods_id` = gl.`goods_id`' . $where3;
        $groupByGoodArr = $goodsByMod->querySql($sql3);
        foreach ($groupByGoodArr as $key => $val) {
            $groupByGoodArr[$key]['source'] = 2;
        }


        $this->checkOver();
        // 获取正在进行或者未开始的促销活动
        $sql4 = " select  ps.prom_name as name, ps.id as cid,ps.*,pg.goods_id as id,pg.goods_key as item_key,pg.goods_key_name,l.goods_name,gl.original_img,pg.goods_price as o_price,pg.discount_price as price,s.is_free_shipping,gl.goods_id as good_id,s.id as gid,pg.limit_amount,l.goods_remark  from "
            . DB_PREFIX . "promotion_sale as ps left join "
            . DB_PREFIX . "promotion_goods as pg on  ps.id = pg.prom_id left join "
            . DB_PREFIX . "store_goods as s on pg.goods_id = s.id  LEFT JOIN "
            . DB_PREFIX . "goods AS gl ON s.`goods_id` = gl.`goods_id` LEFT JOIN  bs_goods_lang AS l ON s.`goods_id` = l.`goods_id` LEFT JOIN "
            . DB_PREFIX . "room_category AS rc on s.cat_id=rc.category_id
             where ps.`store_id` = $this->store_id and ps.`status` in (1,2) and ps.`mark` =1  and rc.room_type_id in ($roomIds) and s.is_on_sale = 1 and s.mark = 1  and l.lang_id = " . $this->lang_id . "  order by ps.status desc,ps.id desc";
        $promotionGoodsArr = $goodPromMod->querySql($sql4);
        foreach ($promotionGoodsArr as $key => $val) {
            $promotionGoodsArr[$key]['source'] = 3;
        }


        $res = array();
        $res = array_merge($spikeArr, $groupByGoodArr, $promotionGoodsArr);

        return $res;
    }


    //检查促销商品是否过期
    public function checkOver()
    {
        $goodPromMod = &m('goodProm');
        $sql = "select * from " . DB_PREFIX . "promotion_sale where mark =1";
        $rs = $goodPromMod->querySql($sql);
        foreach ($rs as $k => $v) {
            if ($v['start_time'] > time()) {
                $vstatus = 1;
            } elseif ($v['start_time'] <= time() && $v['end_time'] >= time()) {
                $vstatus = 2;
            } elseif ($v['end_time'] < time()) {
                $vstatus = 3;
            }
            $goodPromMod->doEdit($v['id'], array('status' => $vstatus));
        }
    }

    /**
     * 选择规格页面接口
     * @author gao
     * @date 2018/08/21
     */
    public function getSpec()
    {
        $storeGoods = &m('areaGood');
        $id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 124;//区域商品id
        $goods_id = !empty($_REQUEST['goods_id']) ? $_REQUEST['goods_id'] : 46;//商品id
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        $lang = $this->lang_id;
        $source = !empty($_REQUEST['source']) ? $_REQUEST['source'] : 3;//活动类型
        $cid = !empty($_REQUEST['cid']) ? $_REQUEST['cid'] : 25;//活动id
        $latlon = !empty($_REQUEST['latlon']) ? $_REQUEST['latlon'] : '118.77807441,32.0572355';
        $goodinfo = $storeGoods->getLangInfo1($id, $lang);
        $langData = array(
            $this->langData->public->information,
            $this->langData->public->name,
            $this->langData->project->buy_now,
            $this->langData->project->select_spec,
            $this->langData->project->add_to_cart,
            $this->langData->project->current_inventory,
            $this->langData->project->choose_distribution_shop,
            $this->langData->public->num,
        );
        //促销
        if ($source == 3) {
            $sql = " select ps.*,pg.goods_key as item_id,pg.goods_price,pg.discount_price,pg.limit_amount,ps.status  from " . DB_PREFIX . "promotion_sale as ps left join " . DB_PREFIX . "promotion_goods as pg on
                ps.id = pg.prom_id  where ps.`store_id` = $this->store_id and ps.`status` in (1,2) and ps.`mark` =1 and ps.id =$cid  and pg.goods_id=" . $id;
            $arr = $storeGoods->querySql($sql);
            foreach ($arr as $k => $v) {
                $arr1 = explode('_', $v['item_id']);
            }
        }
        // 团购
        if ($source == 2) {
            $where2 = '  where  store_id = ' . $this->store_id . ' and  mark =1 and id=' . $cid;
            $sql2 = 'SELECT  goods_spec_key as item_id,group_goods_price,goods_price,group_goods_num,virtual_num,end_time,title as name,start_time
                FROM  ' . DB_PREFIX . 'goods_group_buy ' . $where2;
            $arr = $storeGoods->querySql($sql2);
            foreach ($arr as $v) {
                $arr1 = explode('_', $v['item_id']);
            }
        }
        $spec_img1 = $this->get_spec($goodinfo['goods_id'], $id, 2);

        if (!empty($arr1)) {
            foreach ($spec_img1 as $key => $value) {
                foreach ($value['spec_data'] as $k1 => $v1) {
                    if (!in_array($v1['item_id'], $arr1))
                        unset($spec_img1[$key][$k1]);

                }
            }
        }
        $sql11 = "select * from bs_store where id=" . $store_id;
        $storeMod = &m('store');
        $r = $storeMod->querySql($sql11);
        $storeList = $this->getCountryStore($this->countryId, $goods_id, $latlon);
        $info = array('langData' => $langData, 'spec_img' => $spec_img1, 'good_info' => $goodinfo, 'lang' => $lang, 'storeList' => $storeList, 'store_discount' => $r[0]['store_discount']);
        if ($info) {
            $this->setData($info, 1, '');
        }
    }


    /**
     * 获取商品规格
     * @param $goods_id |商品id  $type=1 读取商品原有规格属性  2 读取区域编辑后的规格属性
     * @return array
     */
    public function get_spec($goods_id, $store_goods_id, $type = 1)
    {

        $storeGoodMod = &m("storeGoodItemPrice");
        //商品规格 价钱 库存表 找出 所有 规格项id
        //$keys = M('SpecGoodsPrice')->where("goods_id", $goods_id)->getField("GROUP_CONCAT(`key` SEPARATOR '_') ");
        if ($type == 1) {
            $sql = "select `key` as item_key from  " . DB_PREFIX . "goods_spec_price where goods_id=" . $goods_id;
        } else {
            $sql = "select `key` as item_key from  " . DB_PREFIX . "store_goods_spec_price where store_goods_id=" . $store_goods_id;
        }
        $keys = $storeGoodMod->querySql($sql);
        $filter_spec = array();
        if ($keys) {
            $key_str = "";
            foreach ($keys as $k => $v) {
                $key_str .= $v['item_key'] . "_";
            }
            $res_item = substr($key_str, 0, strlen($key_str) - 1);
            $keys = str_replace('_', ',', $res_item);
            $specImage = array();
            $sql3 = "select spec_image_id,src from " . DB_PREFIX . "goods_spec_image where goods_id=" . $goods_id; // 规格对应的 图片表， 例如颜色
            $img_list = $storeGoodMod->querySql($sql3);
            foreach ($img_list as $k => $v) {
                $specImage[$v['spec_image_id']] = $v['src'];
            }
            $sql4 = "SELECT a.`order`,b.*,al.spec_name,bl.`item_name`  FROM " . DB_PREFIX . "goods_spec AS a
                     INNER JOIN " . DB_PREFIX . "goods_spec_item AS b ON a.id = b.spec_id
                     LEFT JOIN " . DB_PREFIX . "goods_spec_lang AS al ON a.id=al.spec_id
                     LEFT JOIN " . DB_PREFIX . "goods_spec_item_lang as bl ON b.id=bl.item_id
                     WHERE b.id IN($keys) and al.lang_id=" . $this->lang_id . " and bl.lang_id=" . $this->lang_id . " ORDER BY b.id";
            $filter_spec2 = $storeGoodMod->querySql($sql4);

            foreach ($filter_spec2 as $key => $val) {
                $filter_spec[$key]['spec_name'] = $val['spec_name'];
                $filter_spec[$key]['spec_data'] = array(
                    'item_id' => $val['id'],
                    'item' => $val['item_name']
                );
            }
        }

        $result = array();
        foreach ($filter_spec as $k => $v) {
            $result[$v['spec_name']]['spec_name'] = $v['spec_name'];
            $result[$v['spec_name']]['spec_data'][] = $v['spec_data'];
        }

        $result = $this->toIndexArr($result);

        return $result;
    }


    function toIndexArr($arr)
    {
        $i = 0;
        foreach ($arr as $key => $value) {
            $newArr[$i] = $value;
            $i++;
        }
        return $newArr;
    }


    //获取配送店铺
    public function getCountryStore($country_id, $goods_id, $latlon)
    {
        if ($_SESSION['userId']) {
            $user_id = $_SESSION['userId'];
            $userMod = &m('user');
            $sql = 'SELECT *  FROM  ' . DB_PREFIX . 'user WHERE  id  =' . $user_id; //odm_members
            $datas = $userMod->querySql($sql);
            if ($datas[0]['odm_members'] == 0) {
                $where = ' and c.store_type < 4  ';
            } else {
                $where = '';
            }
        } else {
            $where = ' and c.store_type < 4  ';
        }
        $mod = &m('store');
        $sql = 'SELECT  c.id,l.store_name,c.distance,c.longitude,c.latitude  FROM  ' . DB_PREFIX . 'store  as c
                 left join  ' . DB_PREFIX . 'store_lang as l on  c.id = l.store_id
                 WHERE c.is_open = 1  and  l.lang_id =' . $this->lang_id . ' and l.distinguish=0  and c.store_cate_id=' . $country_id . $where;
        $data = $mod->querySql($sql);
        $sql1 = 'SELECT store_id FROM ' . DB_PREFIX . 'store_goods  WHERE goods_id=' . $goods_id . ' and mark =1  and is_on_sale =1 ';
        $gData = $mod->querySql($sql1);
        foreach ($gData as $key => $val) {
            $val = join(',', $val);
            $temp[] = $val;
        }
        $temp = array_unique($temp);
        foreach ($data as $k => $v) {
            foreach ($temp as $k1 => $v1) {
                if ($v['id'] == $v1) {
                    $arr[$k1]['id'] = $v['id'];
                    $arr[$k1]['store_name'] = $v['store_name'];
                    $arr[$k1]['distance'] = $v['distance'];
                    $arr[$k1]['latitude'] = $v['latitude'];
                    $arr[$k1]['longitude'] = $v['longitude'];
                }
            }
        }

        $latlon = explode(',', $latlon);
        $lng = $latlon[0]; //经度
        $lat = $latlon[1]; //纬度
        foreach ($arr as $key => $val) {
            $s = $this->getdistance($lng, $lat, $val['longitude'], $val['latitude']);
            $distance = number_format(($s / 1000), 2, '.', '');
            $arr[$key]['dis'] = $distance;
            $busSql = "SELECT buss_id FROM " . DB_PREFIX . 'store_business WHERE  store_id=' . $val['id'];
            $busData = $mod->querySql($busSql);
            $arr[$key]['b_id'] = $busData[0]['buss_id'];
            if ($val['distance'] < $distance) {
                unset($arr[$key]);
            }
        }
        $sort = array(
            'direction' => 'SORT_ASC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
            'field' => 'dis', //排序字段
        );
        $arrSort = array();
        foreach ($arr AS $uniqid => $row) {
            foreach ($row AS $key => $value) {
                $arrSort[$key][$uniqid] = $value;
            }
        }
        if ($sort['direction']) {
            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $arr);
        }
        return $arr;
    }


    //转化距离
    function getdistance($lng1, $lat1, $lng2, $lat2)
    {
        // 将角度转为狐度
        $radLat1 = deg2rad($lat1); //deg2rad()函数将角度转换为弧度
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137 * 1000;
        return $s;
    }


    /**
     * 选择规格库存价格页面接口
     * @author gao
     * @date 2018/08/21
     */
    public function getSpecPrice()
    {
        $goods_id = !empty($_REQUEST['goods_id']) ? $_REQUEST['goods_id'] : 46;//商品id
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        $storeMod =& m('storeGoods');
        $store = &m('store');
        $where = ' and  mark=1   and   is_on_sale =1';
        $sql = "SELECT id FROM " . DB_PREFIX . 'store_goods WHERE store_id=' . $store_id . ' AND goods_id=' . $goods_id . $where;
        $data = $storeMod->querySql($sql);
        $sqll = "select store_discount from bs_store where id=" . $store_id;
        $res = $store->querySql($sqll);
        $id = $data[0]['id'];
        $storeGoodMod = &m("storeGoodItemPrice");
        $spec_data = $storeGoodMod->getData(array('cond' => "store_goods_id=" . $id));
        foreach ($spec_data as $k => $v) {
            $spec_arr[] = $v;
//            $spec_arr['store_discount'] = $res[0]['store_discount'];
        }
        if ($spec_arr) {
            $this->setData($spec_arr, 1, '');
        }
    }


    /**
     * 购物车页面接口
     * @author gao
     * @date 2018/08/27
     */
    public function cart()
    {
        $cartMod =& m('cart');
        $langData = array(
            $this->langData->public->settlement,
            $this->langData->public->free_of_freight,
            $this->langData->public->total,
            $this->langData->public->administration,
            $this->langData->public->car,
            $this->langData->public->total_selection,
        );

        $sql = "select * from " . DB_PREFIX . "cart where `user_id` = '{$this->userId}' AND is_buy = 0";

        $rsSql = $cartMod->querySql($sql);
        foreach ($rsSql as $k1 => $v1) {
            $status = $this->checkOnSale($v1['goods_id']);
            if ($status == 2) {
                $cartMod->doDelete(array('cond' => "`goods_id`='{$v1['goods_id']}'"));
            }
            $mark = $this->checkDelete($v1['goods_id']);
            if ($mark == 0) {
                $cartMod->doDelete(array('cond' => "`goods_id` in ({$v1['goods_id']})"));
            }
        }
        $sql = "select * from " . DB_PREFIX . "cart where `user_id` = '{$this->userId}' AND is_buy = 0";
        $rs = $cartMod->querySql($sql);
//        echo '<pre>';
//        var_dump($rs);die;
//        // 统计购车商品数量
//        // 如果有规格，就去当前商品的规格图片，没有去主商品图片，是组合商品 组合商品的图片
//        foreach ($rs as $k => $v) {
//            $rs[$k]['original_img'] = $this->getGoodImg($v['goods_id']);
//            $rs[$k]['store_name'] = $this->getStoreName($v['store_id']);
//            $rs[$k]['short'] = $this->short;
//            $rs[$k]['totalMoney'] = number_format(round(($v['goods_price'] * $v['goods_num']), 2), 2);
//            if ($v['spec_key']) {
//                $info = explode('_', $v['spec_key']);
//                foreach ($info as $k1 => $v1) {
//                    $sql = "select `item_name` from " . DB_PREFIX . "goods_spec_item_lang where `item_id` = '{$v1}' and  `lang_id` = $this->lang_id";
//                    $spec_1 = $cartMod->querySql($sql);
//                    $spec[] = $spec_1[0]['item_name'];
//                }
//                $spec_key = implode(':', $spec);
//                $rs[$k]['spec_key_name'] = $spec_key;
//                $spec = array();
//            }
//            $rs[$k]['shipping_store'] = $this->getStoreName($v['shipping_store_id']);
//            $rs[$k]['goods_name'] = $cartMod->getGoodNameById($v['goods_id'], $this->lang_id);
//        }
        // 统计购车商品数量
        // 如果有规格，就去当前商品的规格图片，没有去主商品图片，是组合商品 组合商品的图片
        $result = array();

        foreach ($rs as $k => $v) {
            $store_info_1 = $this->getStoreName($v['store_id']);
            $buss_info = $this->getBussInfo($v['store_id']);
            $result[$v['store_id']]['store_name'] = $store_info_1['store_name'];
            $result[$v['store_id']]['logo'] = $store_info_1['logo'];
            $result[$v['store_id']]['buss_id'] = $buss_info;
            $result[$v['store_id']]['store_id'] = $v['store_id'];
            $result[$v['store_id']]['child'][$k] = $v;
            $invalid = $this->cartMod->isInvalid($v['goods_id'], $v['spec_key']);
            $result[$v['store_id']]['child'][$k]['invalid'] = 0;
            if (empty($invalid)) {
                $result[$v['store_id']]['child'][$k]['invalid'] = 1;
            } else {
                if ($invalid < $v['goods_num']) {
                    $result[$v['store_id']]['child'][$k]['invalid'] = 1;
                }
            }
            $result[$v['store_id']]['child'][$k]['original_img'] = $this->getGoodImg($v['goods_id']);
            $result[$v['store_id']]['child'][$k]['totalMoney'] = round(($v['goods_price'] * $v['goods_num']), 2);
            if ($v['spec_key']) {
                $info = explode('_', $v['spec_key']);
                foreach ($info as $k1 => $v1) {
                    $sql = "select `item_name` from " . DB_PREFIX . "goods_spec_item_lang where `item_id` = '{$v1}' and  `lang_id` = $this->lang_id";
                    $spec_1 = $this->cartMod->querySql($sql);
                    $spec[] = $spec_1[0]['item_name'];
                }
                $spec_key = implode(':', $spec);
                $result[$v['store_id']]['child'][$k]['spec_key_name'] = $spec_key;
                $spec = array();
            }
            $store_info_2 = $this->getStoreName($v['shipping_store_id']);
            $result[$v['store_id']]['child'][$k]['shipping_store'] = $store_info_2['store_name'];
            $result[$v['store_id']]['child'][$k]['goods_name'] = $this->cartMod->getGoodNameById($v['goods_id'], $this->lang_id);
        }
        $res = $this->filter_data($result);
        $cartData = array('langData' => $langData, 'cartData' => $res);
        // if($rs){
        $this->setData($cartData, 1, '');
        // }
    }

    function filter_data($data)
    {
        $data = array_values($data);
        foreach ($data as &$item) {
            if (isset($item['child'])) {
                $item['child'] = $this->filter_data($item['child']);
            }
        }
        return $data;
    }

    public function getBussInfo($store_id)
    {
        $busSql = "SELECT buss_id FROM " . DB_PREFIX . 'store_business WHERE  store_id=' . $store_id;
        $busData = $this->storeMod->querySql($busSql);
        return $busData[0]['buss_id'];
    }

    /**
     * 购物车页面接口
     * @author gao
     * @date 2018/08/27
     */
    public function dele()
    {
        $cartMod =& m('cart');
        $cart_id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;
        $query = array(
            'cond' => "`id` in ({$cart_id})"
        );
        $rs = $cartMod->doDelete($query);
        if ($rs) {
            $this->setData(array(), 1, '');
        }
    }

    /**
     * 获取当前商品是否下架
     * @author wanyan
     * @date 2017-11-09
     */
    public function checkOnSale($goods_id)
    {
        $storeGoodsMod =& m('areaGood');
        $query = array(
            'cond' => "`id`='{$goods_id}'",
            'fields' => "is_on_sale"
        );
        $rs = $storeGoodsMod->getOne($query);
        return $rs['is_on_sale'];
    }


    /**
     * 获取当前商品是否删除
     * @author wanyan
     * @date 2017-11-09
     */
    public function checkDelete($goods_id)
    {
        $storeGoodsMod =& m('areaGood');
        $query = array(
            'cond' => "`id`='{$goods_id}'",
            'fields' => "mark"
        );
        $rs = $storeGoodsMod->getOne($query);
        return $rs['mark'];
    }




    /**
     * 获取店铺名称
     * @author wanyan
     * @date 2017-09-21
     */
    public function getStoreName($store_id)
    {
        $storeMod = &m('store');
        $sql = 'SELECT  l.store_name  FROM  ' . DB_PREFIX . 'store  as c
                 left join  ' . DB_PREFIX . 'store_lang as l on  c.id = l.store_id
                 WHERE l.distinguish=0 and  l.lang_id =' . $this->lang_id . '  and  c.id=' . $store_id;
        $res = $storeMod->querySql($sql);

        return $res[0];
    }


    /**
     * 限时优惠商品详情
     * @author:tangp
     * @date:2018-09-04
     */
    public function goodInfo()
    {
        $source = !empty($_REQUEST['source']) ? intval($_REQUEST['source']) : 0;
        $cid = !empty($_REQUEST['cid']) ? intval($_REQUEST['cid']) : 0;
        $goods_key = !empty($_REQUEST['goods_key']) ? $_REQUEST['goods_key'] : '';
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $userId = $this->userId;
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        $langData = array(
            $this->langData->public->mail,
            $this->langData->project->countdown_for_sale,
            $this->langData->public->size,
            $this->langData->public->temperature,
            $this->langData->project->buy_num,
            $this->langData->project->goods_detail,
            $this->langData->project->goods_params,
            $this->langData->public->by,
            $this->langData->project->collection,
            $this->langData->public->car
        );
        $storeGoods = &m('areaGood');

        if ($_REQUEST['goods_id']) {
            $g_info = $storeGoods->getOne(array("cond" => "goods_id=" . $_REQUEST['goods_id'] . " and store_id=" . $storeid . " and mark =1"));
            $id = $g_info['id'];
        } else {
            $id = ($_REQUEST['gid']) ? $_REQUEST['gid'] : 0;
        }
        if (empty($id)) {
            $this->setData(array(), 0, '该商品已下架');
        }
        $fxCode = ($_REQUEST['fxCode']) ? $_REQUEST['fxCode'] : '';
        $goodMod = &m('goods');
        $goodClassMod = &m('goodsClass');
        $goodAttrMod = &m('goodsAttriInfo');
        $goodImgMod = &m('goodsImg');
        $storeGoodMod = &m("storeGoodItemPrice");

        //商品信息
        $info = $storeGoods->getLangInfo($id, $lang);
        if (empty($info)) {
            $this->setData(array(), 0, '该商品已下架');
        }

        /*
         * modify by lee
         */
        //绑定销售
        $comMainMod = &m('combinedSale');
        $comGoodMod = &m('combinedGoods');
        $has_main = $comMainMod->getOne(array("cond" => "main_id =" . $id . " and status = 1"));
        $has_com = $comGoodMod->getOne(array("cond" => "store_goods_id =" . $id));

        if ($has_main || $has_com) {
            if ($has_main) {
                $com_id = $has_main['id'];
            }
            if ($has_com) {
                $com_id = $has_com['com_id'];
            }
            //$com_list = $comGoodMod->getData(array("cond" =>"com_id =".$com_id." and store_goods_id!=".$id,"group by"=>"store_goods_id"));
            $com_sql = "select c.* from " . DB_PREFIX . "combined_goods as c
                        left join  " . DB_PREFIX . "combined_sale as s on s.id = c.com_id
                        where com_id =" . $com_id . " and c.store_goods_id!= " . $id . " and s.status = 1 group by c.store_goods_id";
            $com_list = $comGoodMod->querySql($com_sql);
            foreach ($com_list as $k => $v) {
                $new_info = $storeGoods->getLangInfo($v['store_goods_id'], $lang);
                $com_list[$k]['goods_name'] = $new_info['goods_name'];
            }

            $com_num = count($com_list);
            $promGood = array();
            switch ($com_num) {
                case $com_num < 4:
                    $promGood[] = $com_list;
                    break;
                case $com_num > 3:
                    $promGood[0] = array_slice($com_list, 0, 3);
                    $promGood[1] = array_slice($com_list, 3, 3);
            }
        }
        //print_r($promGood);exit;

        $name = $info['goods_name']; //详情名称
        $cat = $info['cat_id']; //分类
        $style_id = $info ['style_id']; //类型
        $brand_id = $info ['brand_id']; //类型
        $where = "s.goods_name like '%$name%'  or s.cat_id =" . $cat;
        if ($style_id) {
            $where .= " or s.style_id = " . $style_id;
        }
        if ($brand_id) {
            $where .= " or s.brand_id = " . $brand_id;
        }
        $store_sql = "select s.id,gl.original_img from "
            . DB_PREFIX . "store_goods as s LEFT JOIN  "
            . DB_PREFIX . "goods AS gl ON s.`goods_id` = gl.`goods_id` where  (store_id = {$storeid} and s.is_on_sale = 1 and s.mark = 1 and id !={$id}) and
                       (" . $where . ")";

        $store_good = $goodMod->querySql($store_sql);
        foreach ($store_good as $k => $v) {
            $new_info_2 = $storeGoods->getLangInfo($v['id'], $lang);
            $store_good[$k]['goods_name'] = $new_info_2['goods_name'];
        }
        $store_goods_num = count($store_good);
        $storeGood = array();
        switch ($store_goods_num) {
            case $store_goods_num < 4:
                $storeGood[] = $store_good;
                break;
            case $store_goods_num > 3:
                $storeGood[0] = array_slice($store_good, 0, 3);
                $storeGood[1] = array_slice($store_good, 3, 3);
        }
        //推荐
        if (empty($promGood[0])) {
            $promGood = array();
        }
        if (empty($storeGood[0])) {
            $storeGood = array();
        }
//        $this->assign('promGood', $promGood); //组合销售
//        $this->assign('storeGood', $storeGood); //推荐商品
        //end
        //收藏商品
        $sql_collection = 'select * from ' . DB_PREFIX . 'user_collection where user_id=' . $userId . ' and store_id=' . $storeid;
        $data_collection = $storeGoods->querySql($sql_collection);
        foreach ($data_collection as &$collertion) {
            if ($collertion['store_good_id'] == $info['id']) {
                $info['type'] = 1;
            }
        }
        $goods_info = $goodMod->getOne(array("cond" => "goods_id=" . $info['goods_id']));
        //分类信息
        $info['original_img'] = $goods_info['original_img'];

        $cat_3 = $goodClassMod->getLangInfo($goods_info['cat_id'], $lang);
        $cat_2 = $goodClassMod->getLangInfo($cat_3[0]['parent_id'], $lang);
        $cat_1 = $goodClassMod->getLangInfo($cat_2[0]['parent_id'], $lang);
        //商品图片页
        $img_arr = $goodImgMod->getData(array('cond' => "goods_id=" . $info['goods_id']));
        //商品规格
        if (!empty($source)) {
            $seckMod = &m('spikeActivity');
            $goodsByMod = &m('groupbuy');
            $goodPromMod = &m('goodProm');
            $promotionMod = &m('goodPromDetail');
            $curtime = time();
            $tody = strtotime(date('Y-m-d', time()));
            $now = $curtime - $tody;
            //秒杀
            if ($source == 1) {
                $where1 = 'WHERE store_id =' . $storeid . '  and  ' . $curtime . ' > start_time  and id=' . $cid;
                $sql = 'SELECT  store_goods_id,o_price,price,goods_num,start_time,end_time,start_our,end_our,goods_name,name  FROM  ' . DB_PREFIX . 'spike_activity ' . $where1;
//                echo $sql;die;
                $arr = $seckMod->querySql($sql);
                /*   $spec_img1 = $this->get_spec(0, $arr[0]['store_goods_id'], 2); */

                foreach ($arr as $k => $v) {
                    /* $arr1 = explode('_', $v['item_id']); */
                    $info['shop_price'] = $v['price'];
                    $info['market_price'] = $v['o_price'];
                    $info['goods_storage'] = $v['goods_num'];
                    $info['goods_name'] = $v['goods_name'];
                    if ($curtime > $v['start_time'] && $curtime < $v['end_time']) {
                        if ($now > $v['start_our'] && $now < $v['end_our']) {
                            $arr[$k]['in_time'] = 1;
                        } else {
                            $arr[$k]['in_time'] = 2;
                        }
                    } else {
                        $arr[$k]['in_time'] = 3;
                    }
                    $arr[$k]['end_timea'] = $arr[$k]['end_our'] - $now;
                    $arr[$k]['start_our'] = (mktime(0, 0, 0, date("m"), date("d"), date("Y")) + $arr[$k]['start_our']);
                    $arr[$k]['end_our'] = (mktime(0, 0, 0, date("m"), date("d"), date("Y")) + $arr[$k]['end_our']);
                }
//                var_dump($arr);die;
                // $this->assign('arr1', $arr1);
                // $str = $arr[0]['item_id'];
            }

            //优惠
            if ($source == 3) {
                if (!empty($goods_key)) {
                    $sql = " select ps.*,pg.goods_key as item_id,pg.goods_price,pg.discount_price,pg.limit_amount,ps.status  from " . DB_PREFIX . "promotion_sale as ps left join " . DB_PREFIX . "promotion_goods as pg on
                ps.id = pg.prom_id  where ps.`store_id` = $this->store_id and ps.`status` in (1,2) and ps.`mark` =1 and ps.id =$cid and pg.goods_id=" . $id . " and pg.goods_key='{$goods_key}'   order by ps.status desc,ps.id desc";
//                    echo $sql;die;
                    $arr = $seckMod->querySql($sql);
                } else {
                    $sql = " select ps.*,pg.goods_key as item_id,pg.goods_price,pg.discount_price,pg.limit_amount,ps.status  from " . DB_PREFIX . "promotion_sale as ps left join " . DB_PREFIX . "promotion_goods as pg on
                ps.id = pg.prom_id  where ps.`store_id` = $this->store_id and ps.`status` in (1,2) and ps.`mark` =1 and ps.id =$cid and pg.goods_id=" . $id . " order by ps.status desc,ps.id desc";
                    $arr = $seckMod->querySql($sql);
                }

                foreach ($arr as $k => $v) {
                    $arr1 = explode('_', $v['item_id']);
                    $info['shop_price'] = $v['discount_price'];
                    $info['market_price'] = $v['goods_price'];
                    if ($v['status'] == 2) {
                        $arr[$k]['end_timea'] = $v['end_time'] - $curtime;
                    }
                }


//                $this->assign('arr', $arr[0]);
//                $this->assign('arr1', $arr1);
                $str = $arr[0]['item_id'];
//                var_dump($str);die;
            }
            // 团购
            if ($source == 2) {
                $where2 = '  where  store_id = ' . $storeid . ' and  mark =1 and id=' . $cid;
                $sql2 = 'SELECT  goods_spec_key as item_id,group_goods_price,goods_price,group_goods_num,virtual_num,end_time,title as name,start_time
                FROM  ' . DB_PREFIX . 'goods_group_buy ' . $where2;
                $arr = $seckMod->querySql($sql2);
                foreach ($arr as $v) {
                    $arr1 = explode('_', $v['item_id']);
                    $info['shop_price'] = $v['group_goods_price'];
                    $info['market_price'] = $v['goods_price'];
                    $info['goods_storage'] = $v['group_goods_num'];
                }
//                $this->assign('arr', $arr[0]);
//                $this->assign('arr1', $arr1);
                $str = $arr[0]['item_id'];
            }
            //组合销售
            if ($source == 4) {
                $where4 = '  where  com_id=' . $cid;
                $sql2 = "";
                $arr = $seckMod->querySql($sql2);
            }
        } else {
            //店铺商品打折
            $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $storeid;
            $store_arr = $goodAttrMod->querySql($store_sql);
            $info['shop_price'] = number_format($info['shop_price'] * $store_arr[0]['store_discount'], 2);
            $this->assign("store_arr", $store_arr[0]);
        }


        //商品属性(暂时死数据展示，参数一 原始商品ID  参数二 语言ID)
        $attr_arr = $goodAttrMod->getLangData($info['goods_id'], $lang);

        //获取区域商品规格价格
        $spec_data = $storeGoodMod->getData(array('cond' => "store_goods_id=" . $id));
        foreach ($spec_data as $k => $v) {
            $spec_arr[$v['key']] = $v;
        }

        if (!empty($str)) {
            foreach ($spec_arr as $k => $v) {
                if ($k != $str) {
                    unset($spec_arr[$k]);
                } else {
                    $spec_arr[$k]['price'] = $info['shop_price'];
                    $spec_arr[$k]['goods_storage'] = $info['goods_storage'];
                }
            }
        }
//        var_dump($arr1);
        $spec_img = $this->get_spec2($info['goods_id'], $id, 2);
//        echo '<pre>';
//        var_dump($spec_img);die;
//        print_r($arr1);die;
//        if (!empty($arr1) && $source != 1) {
//            foreach ($spec_img as $key => &$value) {
//                foreach ($value['spec_data'] as $k => $v) {
//                    if (!in_array($v['item_id'], $arr1)){
//                        unset($spec_img[$value['spec_data']][$k]);
//                    }
//                }
//            }
//        }
        if (!empty($arr1) && $source != 1) {
            foreach ($spec_img as $key => &$value) {
                foreach ($value['spec_data'] as $k => $v) {
                    if (!in_array($v['item_id'], $arr1))
                        unset($spec_img[$key]['spec_data'][$k]);
                }
            }
        }
        //足迹
        $this->infoFootPrint($info['goods_id'], $info['id']);
        //获取币种信息
        $sql = "select c.* from " . DB_PREFIX . "currency as c inner join " . DB_PREFIX . "store as s on c.id=s.currency_id where s.id=" . $info['store_id'];
        $cur_info = $storeGoods->querySql($sql);

        //获取商品评价数量
        $good_all_num = " select count(1) as all_num from bs_goods_comment  where goods_id ={$info['id']}";
        $good_all_num = $this->goodsCommentMod->querySql($good_all_num);
        $this->assign('good_all_num', $good_all_num[0]);
        //获取评价列表信息
        $eva_sql = 'select  comment_id, username, goods_rank, add_time, content, img, revert  from ' . DB_PREFIX . 'goods_comment
                     where  goods_id  = ' . $info['id'] . ' and store_id = ' . $this->store_id . '   order by comment_id desc ';
        $list = $this->goodsCommentMod->querySql($eva_sql);
        $new_list = array_map(function ($vo) {
            $vo['img'] = explode(',', $vo['img']);
            return $vo;
        }, $list);

        //获取国家下属所有店铺
        $storeList = $this->getCountryStore1($this->countryId);
        //客服聊天参数
        $imMod = &m('user');
        $kf_cond = "is_kefu = 1 and kf_status = 1 and store_id = " . $info['store_id'];
        $kf_arr = $imMod->getData(array("cond" => $kf_cond));
        if (is_array($kf_arr)) {
            $key = array_rand($kf_arr);
            $kf_id = $kf_arr[$key]['id'];
        } else {
            $kf_id = "no";
        }
        //组合销售活动
        $zhhdsql = 'SELECT gs.store_goods_id FROM  ' . DB_PREFIX . 'combined_sale AS cs LEFT JOIN  ' . DB_PREFIX . 'combined_goods as gs on gs.com_id=cs.id  where cs.status=1 and  gs.store_goods_id =' . $id;
        $zhhdData = $imMod->querySql($zhhdsql);

        //限时秒杀活动
        $xsmssql = 'SELECT store_goods_id FROM  ' . DB_PREFIX . 'spike_activity  where store_goods_id =' . $id;
        $xsmsData = $imMod->querySql($xsmssql);

        //促销活动
        $cxql = 'SELECT gs.goods_id FROM  ' . DB_PREFIX . 'promotion_sale AS cs LEFT JOIN  ' . DB_PREFIX . 'promotion_goods as gs on gs.prom_id=cs.id  where cs.status=1 and  gs.goods_id =' . $id;
        $cxData = $imMod->querySql($cxql);

        //团购活动
        $tgql = 'SELECT goods_id FROM  ' . DB_PREFIX . 'goods_group_buy where is_end =1 and goods_id =' . $id;
        $tgData = $imMod->querySql($tgql);

        $data = array(
            'langData' => $langData,
            'info' => $info,
            'img_arr' => $img_arr,
            'cur_info' => $cur_info[0],
            'spec_img' => $spec_img,
            'attr_arr' => $attr_arr,
            // 'spec_key'=> $str,
            'arr' => $arr[0],
            // 'list'    => $list,
            'store_good' => $store_good,
            'promGood' => $promGood,
            'good_all_num' => $good_all_num[0]
        );

        $this->setData($data, 1, '');

    }

    public function get_spec2($goods_id, $store_goods_id, $type = 1)
    {
        $storeGoodMod = &m("storeGoodItemPrice");
        //商品规格 价钱 库存表 找出 所有 规格项id
        //$keys = M('SpecGoodsPrice')->where("goods_id", $goods_id)->getField("GROUP_CONCAT(`key` SEPARATOR '_') ");
        if ($type == 1) {
            $sql = "select `key` as item_key from  " . DB_PREFIX . "goods_spec_price where goods_id=" . $goods_id;
        } else {
            $sql = "select `key` as item_key from  " . DB_PREFIX . "store_goods_spec_price where store_goods_id=" . $store_goods_id;
        }
        $keys = $storeGoodMod->querySql($sql);
//        var_dump($keys);die;
        $filter_spec = array();
        if ($keys) {
            $key_str = "";
            foreach ($keys as $k => $v) {
                $key_str .= $v['item_key'] . "_";
            }
            $res_item = substr($key_str, 0, strlen($key_str) - 1);
            $keys = str_replace('_', ',', $res_item);
            $specImage = array();
            $sql3 = "select spec_image_id,src from " . DB_PREFIX . "goods_spec_image where goods_id=" . $goods_id; // 规格对应的 图片表， 例如颜色
            $img_list = $storeGoodMod->querySql($sql3);
            foreach ($img_list as $k => $v) {
                $specImage[$v['spec_image_id']] = $v['src'];
            }
            $sql4 = "SELECT a.`order`,b.*,al.spec_name,bl.`item_name`  FROM " . DB_PREFIX . "goods_spec AS a
                     INNER JOIN " . DB_PREFIX . "goods_spec_item AS b ON a.id = b.spec_id
                     LEFT JOIN " . DB_PREFIX . "goods_spec_lang AS al ON a.id=al.spec_id
                     LEFT JOIN " . DB_PREFIX . "goods_spec_item_lang as bl ON b.id=bl.item_id
                     WHERE b.id IN($keys) and al.lang_id=" . $this->lang_id . " and bl.lang_id=" . $this->lang_id . " ORDER BY b.id";
//            echo $sql4;die;
            $filter_spec2 = $storeGoodMod->querySql($sql4);
//            echo '<pre>';
//            print_r($filter_spec2);die;
//            foreach ($filter_spec2 as $key => $val) {
//                $filter_spec[$val['spec_name']][] = array(
//                    'item_id' => $val['id'],
//                    'item' => $val['item_name']
//                );
//            }
            foreach ($filter_spec2 as $key => $val) {

                $filter_spec[$key]['spec_name'] = $val['spec_name'];
                $filter_spec[$key]['spec_data'] = array(
                    'item_id' => $val['id'],
                    'item' => $val['item_name']
                );

//                print_r($filter_spec);die;
            }
//            print_r($filter_spec);die;

        }
        $result = array();
        foreach ($filter_spec as $k => $v) {
            $result[$v['spec_name']]['spec_name'] = $v['spec_name'];
            $result[$v['spec_name']]['spec_data'][] = $v['spec_data'];
        }
//        print_r(json_encode($result));die;
        $result = $this->toIndexArr($result);
//        $arrs = array_values($result);
        return $result;
    }

    public function getCountryStore1($country_id)
    {
        if ($_SESSION['userId']) {
            $user_id = $_SESSION['userId'];
            $userMod = &m('user');
            $sql = 'SELECT *  FROM  ' . DB_PREFIX . 'user WHERE  id  =' . $user_id; //odm_members
            $datas = $userMod->querySql($sql);
            if ($datas[0]['odm_members'] == 0) {
                $where = ' and c.store_type <4 ';
            } else {
                $where = '';
            }
        } else {
            $where = ' and c.store_type<4 ';
        }
        $mod = &m('store');
//        $data = $mod->getData(array("cond" => "store_cate_id=" . $country_id . " and  is_open=1"));
        $sql = 'SELECT  c.id,l.store_name  FROM  ' . DB_PREFIX . 'store  as c
                 left join  ' . DB_PREFIX . 'store_lang as l on  c.id = l.store_id
                 WHERE c.is_open = 1  and  l.lang_id =' . $this->lang_id . ' and l.distinguish=0  and c.store_cate_id=' . $country_id . $where;
        $data = $mod->querySql($sql);
        return $data;
    }

    /**
     * 为您推荐的商品详情
     * @author:tangp
     * @date:2018-09-04
     */
    public function tuiInfo()
    {
        $source = !empty($_REQUEST['source']) ? intval($_REQUEST['source']) : 0;
        $cid = !empty($_REQUEST['cid']) ? intval($_REQUEST['cid']) : 0;
        $goods_key = !empty($_REQUEST['goods_key']) ? $_REQUEST['goods_key'] : '';
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $userId = $this->userId;
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        $langData = array(
            $this->langData->public->mail,
            $this->langData->project->select_goods_spec,
            $this->langData->public->size,
            $this->langData->public->clolor,
            $this->langData->project->delivery_cycle,
            $this->langData->project->choose_distribution_shop,
            $this->langData->project->buy_num,
            $this->langData->project->current_inventory,
            $this->langData->project->material_distribution,
            $this->langData->public->ok,
            $this->langData->project->combined_sales,
            $this->langData->project->relation_recommend,
            $this->langData->project->Intelligent_recommend,
            $this->langData->project->goods_evaluate,
            $this->langData->project->no_time_evaluate,
            $this->langData->project->goods_detail,
            $this->langData->project->goods_params,
            $this->langData->project->collection,
            $this->langData->public->car,
            $this->langData->project->add_to_cart,
            $this->langData->public->by

        );
        $storeGoods = &m('areaGood');

        if ($_REQUEST['goods_id']) {
            $g_info = $storeGoods->getOne(array("cond" => "goods_id=" . $_REQUEST['goods_id'] . " and store_id=" . $storeid . " and mark =1"));
            $id = $g_info['id'];
        } else {
            $id = ($_REQUEST['gid']) ? $_REQUEST['gid'] : 0;
        }
        if (empty($id)) {
            $this->setData(array(), 0, '该商品已下架');
        }
        $fxCode = ($_REQUEST['fxCode']) ? $_REQUEST['fxCode'] : '';
        $goodMod = &m('goods');
        $goodClassMod = &m('goodsClass');
        $goodAttrMod = &m('goodsAttriInfo');
        $goodImgMod = &m('goodsImg');
        $storeGoodMod = &m("storeGoodItemPrice");

        //商品信息
        $info = $storeGoods->getLangInfo($id, $lang);
        if (empty($info)) {
            $this->setData(array(), 0, '该商品已下架');
        }

        /*
         * modify by lee
         */
        //绑定销售
        $comMainMod = &m('combinedSale');
        $comGoodMod = &m('combinedGoods');
        $has_main = $comMainMod->getOne(array("cond" => "main_id =" . $id . " and status = 1"));
        $has_com = $comGoodMod->getOne(array("cond" => "store_goods_id =" . $id));

        if ($has_main || $has_com) {
            if ($has_main) {
                $com_id = $has_main['id'];
            }
            if ($has_com) {
                $com_id = $has_com['com_id'];
            }
            //$com_list = $comGoodMod->getData(array("cond" =>"com_id =".$com_id." and store_goods_id!=".$id,"group by"=>"store_goods_id"));
            $com_sql = "select c.* from " . DB_PREFIX . "combined_goods as c
                        left join  " . DB_PREFIX . "combined_sale as s on s.id = c.com_id
                        where com_id =" . $com_id . " and c.store_goods_id!= " . $id . " and s.status = 1 group by c.store_goods_id";
            $com_list = $comGoodMod->querySql($com_sql);
            foreach ($com_list as $k => $v) {
                $new_info = $storeGoods->getLangInfo($v['store_goods_id'], $lang);
                $com_list[$k]['goods_name'] = $new_info['goods_name'];
            }

            $com_num = count($com_list);
            $promGood = array();
            switch ($com_num) {
                case $com_num < 4:
                    $promGood[] = $com_list;
                    break;
                case $com_num > 3:
                    $promGood[0] = array_slice($com_list, 0, 3);
                    $promGood[1] = array_slice($com_list, 3, 3);
            }
        }
        //print_r($promGood);exit;

        $name = $info['goods_name']; //详情名称
        $cat = $info['cat_id']; //分类
        $style_id = $info ['style_id']; //类型
        $brand_id = $info ['brand_id']; //类型
        $where = "s.goods_name like '%$name%'  or s.cat_id =" . $cat;
        if ($style_id) {
            $where .= " or s.style_id = " . $style_id;
        }
        if ($brand_id) {
            $where .= " or s.brand_id = " . $brand_id;
        }
        $store_sql = "select s.id,gl.original_img,s.goods_id from "
            . DB_PREFIX . "store_goods as s LEFT JOIN  "
            . DB_PREFIX . "goods AS gl ON s.`goods_id` = gl.`goods_id` where  (store_id = {$storeid} and s.is_on_sale = 1 and s.mark = 1 and id !={$id}) and
                       (" . $where . ")";

        $store_good = $goodMod->querySql($store_sql);
        foreach ($store_good as $k => $v) {
            $new_info_2 = $storeGoods->getLangInfo($v['id'], $lang);
            $store_good[$k]['goods_name'] = $new_info_2['goods_name'];
        }
        $store_goods_num = count($store_good);
        $storeGood = array();
        switch ($store_goods_num) {
            case $store_goods_num < 4:
                $storeGood[] = $store_good;
                break;
            case $store_goods_num > 3:
                $storeGood[0] = array_slice($store_good, 0, 3);
                $storeGood[1] = array_slice($store_good, 3, 3);
        }
        //推荐
        if (empty($promGood[0])) {
            $promGood = array();
        }
        if (empty($storeGood[0])) {
            $storeGood = array();
        }
        $this->assign('promGood', $promGood); //组合销售
        $this->assign('storeGood', $storeGood); //推荐商品
        //end
        //收藏商品
        $sql_collection = 'select * from ' . DB_PREFIX . 'user_collection where user_id=' . $userId . ' and store_id=' . $storeid;
        $data_collection = $storeGoods->querySql($sql_collection);
        foreach ($data_collection as &$collertion) {
            if ($collertion['store_good_id'] == $info['id']) {
                $info['type'] = 1;
            }
        }
        $goods_info = $goodMod->getOne(array("cond" => "goods_id=" . $info['goods_id']));
        //分类信息
        $info['original_img'] = $goods_info['original_img'];

        $cat_3 = $goodClassMod->getLangInfo($goods_info['cat_id'], $lang);
        $cat_2 = $goodClassMod->getLangInfo($cat_3[0]['parent_id'], $lang);
        $cat_1 = $goodClassMod->getLangInfo($cat_2[0]['parent_id'], $lang);
        //商品图片页
        $img_arr = $goodImgMod->getData(array('cond' => "goods_id=" . $info['goods_id']));
        //商品规格
        if (!empty($source)) {
            $seckMod = &m('spikeActivity');
            $goodsByMod = &m('groupbuy');
            $goodPromMod = &m('goodProm');
            $promotionMod = &m('goodPromDetail');
            $curtime = time();
            $tody = strtotime(date('Y-m-d', time()));
            $now = $curtime - $tody;
            //秒杀
            if ($source == 1) {
                $where1 = 'WHERE store_id =' . $storeid . '  and  ' . $curtime . ' > start_time  and id=' . $cid;
                $sql = 'SELECT  store_goods_id,o_price,price,goods_num,start_time,end_time,start_our,end_our,goods_name,name  FROM  ' . DB_PREFIX . 'spike_activity ' . $where1;
                $arr = $seckMod->querySql($sql);

                /*   $spec_img1 = $this->get_spec(0, $arr[0]['store_goods_id'], 2); */

                foreach ($arr as $k => $v) {
                    /* $arr1 = explode('_', $v['item_id']); */
                    $info['shop_price'] = $v['price'];
                    $info['market_price'] = $v['o_price'];
                    $info['goods_storage'] = $v['goods_num'];
                    $info['goods_name'] = $v['goods_name'];
                    if ($curtime > $v['start_time'] && $curtime < $v['end_time']) {
                        if ($now > $v['start_our'] && $now < $v['end_our']) {
                            $arr[$k]['in_time'] = 1;
                        } else {
                            $arr[$k]['in_time'] = 2;
                        }
                    } else {
                        $arr[$k]['in_time'] = 3;
                    }
                    $arr[$k]['end_timea'] = $arr[$k]['end_our'] - $now;
                    $arr[$k]['start_our'] = (mktime(0, 0, 0, date("m"), date("d"), date("Y")) + $arr[$k]['start_our']);
                    $arr[$k]['end_our'] = (mktime(0, 0, 0, date("m"), date("d"), date("Y")) + $arr[$k]['end_our']);
                }
                $this->assign('arr', $arr[0]);
                // $this->assign('arr1', $arr1);
                // $str = $arr[0]['item_id'];
            }
            //优惠
            if ($source == 3) {
                if (!empty($goods_key)) {
                    $sql = " select ps.*,pg.goods_key as item_id,pg.goods_price,pg.discount_price,pg.limit_amount,ps.status  from " . DB_PREFIX . "promotion_sale as ps left join " . DB_PREFIX . "promotion_goods as pg on
                ps.id = pg.prom_id  where ps.`store_id` = $this->storeid and ps.`status` in (1,2) and ps.`mark` =1 and ps.id =$cid and pg.goods_id=" . $id . " and pg.goods_key=" . $goods_key . "   order by ps.status desc,ps.id desc";
                    $arr = $seckMod->querySql($sql);
                } else {
                    $sql = " select ps.*,pg.goods_key as item_id,pg.goods_price,pg.discount_price,pg.limit_amount,ps.status  from " . DB_PREFIX . "promotion_sale as ps left join " . DB_PREFIX . "promotion_goods as pg on
                ps.id = pg.prom_id  where ps.`store_id` = $this->storeid and ps.`status` in (1,2) and ps.`mark` =1 and ps.id =$cid and pg.goods_id=" . $id . " order by ps.status desc,ps.id desc";
                    $arr = $seckMod->querySql($sql);
                }

                foreach ($arr as $k => $v) {
                    $arr1 = explode('_', $v['item_id']);
                    $info['shop_price'] = $v['discount_price'];
                    $info['market_price'] = $v['goods_price'];
                    if ($v['status'] == 2) {
                        $arr[$k]['end_timea'] = $v['end_time'] - $curtime;
                    }
                }


                $this->assign('arr', $arr[0]);
                $this->assign('arr1', $arr1);
                $str = $arr[0]['item_id'];
                /*  var_dump($str); */
            }
            // 团购
            if ($source == 2) {
                $where2 = '  where  store_id = ' . $storeid . ' and  mark =1 and id=' . $cid;
                $sql2 = 'SELECT  goods_spec_key as item_id,group_goods_price,goods_price,group_goods_num,virtual_num,end_time,title as name,start_time
                FROM  ' . DB_PREFIX . 'goods_group_buy ' . $where2;
                $arr = $seckMod->querySql($sql2);
                foreach ($arr as $v) {
                    $arr1 = explode('_', $v['item_id']);
                    $info['shop_price'] = $v['group_goods_price'];
                    $info['market_price'] = $v['goods_price'];
                    $info['goods_storage'] = $v['group_goods_num'];
                }
                $this->assign('arr', $arr[0]);
                $this->assign('arr1', $arr1);
                $str = $arr[0]['item_id'];
            }
            //组合销售
            if ($source == 4) {
                $where4 = '  where  com_id=' . $cid;
                $sql2 = "";
                $arr = $seckMod->querySql($sql2);
            }
        } else {
            //店铺商品打折
            $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $storeid;
            $store_arr = $goodAttrMod->querySql($store_sql);
            $info['shop_price'] = number_format($info['shop_price'] * $store_arr[0]['store_discount'], 2);
            $this->assign("store_arr", $store_arr[0]);
        }


        //商品属性(暂时死数据展示，参数一 原始商品ID  参数二 语言ID)
        $attr_arr = $goodAttrMod->getLangData($info['goods_id'], $lang);

        //获取区域商品规格价格
        $spec_data = $storeGoodMod->getData(array('cond' => "store_goods_id=" . $id));
        foreach ($spec_data as $k => $v) {
            $spec_arr[$v['key']] = $v;
        }

        if (!empty($str)) {
            foreach ($spec_arr as $k => $v) {
                if ($k != $str) {
                    unset($spec_arr[$k]);
                } else {
                    $spec_arr[$k]['price'] = $info['shop_price'];
                    $spec_arr[$k]['goods_storage'] = $info['goods_storage'];
                }
            }
        }
        $spec_img = $this->get_spec1($info['goods_id'], $id, 2);
        if (!empty($arr1) && $source != 1) {
            foreach ($spec_img as $key => $value) {
                foreach ($value as $k => $v) {
                    if (!in_array($v['item_id'], $arr1))
                        unset($spec_img[$key][$k]);
                }
            }
        }
        //足迹
        $this->infoFootPrint($info['goods_id'], $info['id']);
        $this->assign('store_goods_id', $id);
        //获取币种信息
        $sql = "select c.* from " . DB_PREFIX . "currency as c inner join " . DB_PREFIX . "store as s on c.id=s.currency_id where s.id=" . $info['store_id'];
        $cur_info = $storeGoods->querySql($sql);

        //获取商品评价数量
        $good_all_num = " select count(1) as all_num from bs_goods_comment  where goods_id ={$info['id']}";
        $good_all_num = $this->goodsCommentMod->querySql($good_all_num);
        $this->assign('good_all_num', $good_all_num[0]);
        //获取评价列表信息
        $eva_sql = 'select  comment_id, username, goods_rank, add_time, content, img, revert  from ' . DB_PREFIX . 'goods_comment
                     where  goods_id  = ' . $info['id'] . ' and store_id = ' . $this->store_id . '   order by comment_id desc ';
        $list = $this->goodsCommentMod->querySql($eva_sql);
        $new_list = array_map(function ($vo) {
            $vo['img'] = explode(',', $vo['img']);
            return $vo;
        }, $list);

        //获取国家下属所有店铺
        $storeList = $this->getCountryStore1($this->countryId);
        //客服聊天参数
        $imMod = &m('user');
        $kf_cond = "is_kefu = 1 and kf_status = 1 and store_id = " . $info['store_id'];
        $kf_arr = $imMod->getData(array("cond" => $kf_cond));
        if (is_array($kf_arr)) {
            $key = array_rand($kf_arr);
            $kf_id = $kf_arr[$key]['id'];
        } else {
            $kf_id = "no";
        }
        //组合销售活动
        $zhhdsql = 'SELECT gs.store_goods_id FROM  ' . DB_PREFIX . 'combined_sale AS cs LEFT JOIN  ' . DB_PREFIX . 'combined_goods as gs on gs.com_id=cs.id  where cs.status=1 and  gs.store_goods_id =' . $id;
        $zhhdData = $imMod->querySql($zhhdsql);

        //限时秒杀活动
        $xsmssql = 'SELECT store_goods_id FROM  ' . DB_PREFIX . 'spike_activity  where store_goods_id =' . $id;
        $xsmsData = $imMod->querySql($xsmssql);

        //促销活动
        $cxql = 'SELECT gs.goods_id FROM  ' . DB_PREFIX . 'promotion_sale AS cs LEFT JOIN  ' . DB_PREFIX . 'promotion_goods as gs on gs.prom_id=cs.id  where cs.status=1 and  gs.goods_id =' . $id;
        $cxData = $imMod->querySql($cxql);

        //团购活动
        $tgql = 'SELECT goods_id FROM  ' . DB_PREFIX . 'goods_group_buy where is_end =1 and goods_id =' . $id;
        $tgData = $imMod->querySql($tgql);

        $data = array(
            'langData' => $langData,
            'info' => $info,
            'img_arr' => $img_arr,
            'cur_info' => $cur_info[0],
            'spec_img' => $spec_img,
            'attr_arr' => $attr_arr,
            'spec_key' => $str,
            'promGood' => $promGood,
            'storeGood' => $storeGood,
            'list' => $list,
            'good_all_num' => $good_all_num[0]
        );

        $this->setData($data, 1, '');


    }

    /**
     * 图片商品详情
     * @author:tangp
     * @date:2018-09-04
     */
    public function goodsDetails()
    {
        $storeGoodsMod = &m('areaGood');
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? $_REQUEST['auxiliary'] : 0;
        $rtid = !empty($_REQUEST['rtid']) ? $_REQUEST['rtid'] : 0;
        $latlon = !empty($_REQUEST['latlon']) ? $_REQUEST['latlon'] : 0;
        $order = !empty($_REQUEST['order']) ? $_REQUEST['order'] : 0;
        $roomtypearr = $this->getgoodRoomTypearr1($lang, $rtid);
        foreach ($roomtypearr as $kk => $vv) {
            $roomId[] = $vv['id'];
        }
        $roomIds = implode(',', $roomId);
        $hotGoods = $this->getHotGoods($roomIds, $storeid);
        $this->assign('hotGoods', $hotGoods);
        foreach ($roomtypearr as $key => $val) {
            $where = '  where   s.store_id =' . $storeid . '   and rc.room_type_id = ' . $val['id'] . '  and   s.mark=1   and   s.is_on_sale =1  AND l.`lang_id` = ' . $lang;
            //所以子类的商品
            $rsql = 'SELECT s.id,rc.sort,s.`goods_id`,s.`cat_id`,s.`store_id`,l.`goods_name`,l.`lang_id`,s.`shop_price`,s.`market_price`,s.`brand_id`,gl.`original_img`,s.is_free_shipping,s.add_time,l.goods_remark
                FROM  ' . DB_PREFIX . 'store_goods AS s LEFT JOIN  '
                . DB_PREFIX . 'room_category AS rc on s.cat_id=rc.category_id LEFT JOIN '
                . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id`  left join '
                . DB_PREFIX . 'goods as gl on s.goods_id = gl.goods_id ' . $where;
            $roomtypearr[$key]['goods'] = $storeGoodsMod->querySql($rsql);
        }
        foreach ($roomtypearr as $k => $v) {
            foreach ($v['goods'] as $k1 => $v1) {
//                 print_r($v1['shop_price']);exit;
                //店铺商品打折
                $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $storeid;
                $store_arr = $storeGoodsMod->querySql($store_sql);
                $roomtypearr[$k]['goods'][$k1]['shop_price'] = number_format($v1['shop_price'] * $store_arr[0]['store_discount'], 2);
                $oSql = "SELECT rec_id,goods_num  FROM " . DB_PREFIX . 'order_goods WHERE goods_id=' . $v1['id'] . " and order_state in (20,30,40,50)";
                $oData = $storeGoodsMod->querySql($oSql);
                if (!empty($oData)) {
                    $sum = 0;
                    foreach ($oData as $k2 => $v2) {
                        $sum += $v2['goods_num'];
                    }
                    $roomtypearr[$k]['goods'][$k1]['order_num'] = $sum;
                } else {
                    $roomtypearr[$k]['goods'][$k1]['order_num'] = 0;
                }
                $goodsCommentMod = &m('goodsComment');
                $sql = 'select  sum(goods_rank) / count(goods_rank) as res,count(*) as num from ' . DB_PREFIX . 'goods_comment where goods_id = ' . $v1['id'];
                $trance = $goodsCommentMod->querySql($sql);
                $roomtypearr[$k]['goods'][$k1]['rate'] = (int)$trance[0]['res'];
                $roomtypearr[$k]['goods'][$k1]['num'] = $trance[0]['num'];
            }
        }


        $this->setData($roomtypearr, 1, '');

    }

    public function getgoodRoomTypearr1($langid, $rtid)
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

    public function infoFootPrint($goods_id, $id)
    {
        $userId = $this->userId;
        $sql = "select id,good_id from  " . DB_PREFIX . "user_footprint where user_id=" . $userId . " and store_good_id=" . $id . " order by adds_time desc";
        $keys = $this->footPrintMod->querySql($sql);
        if (empty($keys)) {
            if ($goods_id != $keys[0]['good_id']) {
                $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;  //所选的站点id
                $data['user_id'] = $userId;
                $data['good_id'] = $goods_id;
                $data['store_id'] = $storeid;
                $data['adds_time'] = time();
                $data['store_good_id'] = $id;
                $re = $this->footPrintMod->doInsert($data);
            }
        } else {
            $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;  //所选的站点id
            $data['table'] = "user_footprint";
            $data['cond'] = "id=" . $keys[0]['id'];
            $data['set'] = array(
                'adds_time' => time(),
            );
            $re = $this->footPrintMod->doUpdate($data);
        }
    }

    public function get_spec1($goods_id, $store_goods_id, $type = 1)
    {
        $storeGoodMod = &m("storeGoodItemPrice");
        //商品规格 价钱 库存表 找出 所有 规格项id
        //$keys = M('SpecGoodsPrice')->where("goods_id", $goods_id)->getField("GROUP_CONCAT(`key` SEPARATOR '_') ");
        if ($type == 1) {
            $sql = "select `key` as item_key from  " . DB_PREFIX . "goods_spec_price where goods_id=" . $goods_id;
        } else {
            $sql = "select `key` as item_key from  " . DB_PREFIX . "store_goods_spec_price where store_goods_id=" . $store_goods_id;
        }
        $keys = $storeGoodMod->querySql($sql);
        $filter_spec = array();
        if ($keys) {
            $key_str = "";
            foreach ($keys as $k => $v) {
                $key_str .= $v['item_key'] . "_";
            }
            $res_item = substr($key_str, 0, strlen($key_str) - 1);
            $keys = str_replace('_', ',', $res_item);
            $specImage = array();
            $sql3 = "select spec_image_id,src from " . DB_PREFIX . "goods_spec_image where goods_id=" . $goods_id; // 规格对应的 图片表， 例如颜色
            $img_list = $storeGoodMod->querySql($sql3);
            foreach ($img_list as $k => $v) {
                $specImage[$v['spec_image_id']] = $v['src'];
            }
            $sql4 = "SELECT a.`order`,b.*,al.spec_name,bl.`item_name`  FROM " . DB_PREFIX . "goods_spec AS a
                     INNER JOIN " . DB_PREFIX . "goods_spec_item AS b ON a.id = b.spec_id
                     LEFT JOIN " . DB_PREFIX . "goods_spec_lang AS al ON a.id=al.spec_id
                     LEFT JOIN " . DB_PREFIX . "goods_spec_item_lang as bl ON b.id=bl.item_id
                     WHERE b.id IN($keys) and al.lang_id=" . $this->lang_id . " and bl.lang_id=" . $this->lang_id . " ORDER BY b.id";
            $filter_spec2 = $storeGoodMod->querySql($sql4);
            foreach ($filter_spec2 as $key => $val) {

                $filter_spec[$key]['spec_name'] = $val['spec_name'];
                $filter_spec[$key]['spec_data'] = array(
                    'item_id' => $val['id'],
                    'item' => $val['item_name']
                );
            }
        }

        $result = array();
        foreach ($filter_spec as $k => $v) {
            $result[$v['spec_name']]['spec_name'] = $v['spec_name'];
            $result[$v['spec_name']]['spec_data'][] = $v['spec_data'];
        }

        $result = $this->toIndexArr($result);

        return $result;
    }

    /**
     * 文章详情
     * @author:tangp
     * @date:2018-09-11
     */
    public function article_details()
    {
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';

        $articleMod = &m('article');
        //接受数据
        $artid = !empty($_REQUEST['artid']) ? $_REQUEST['artid'] : '';  // 文章id
        //文章所以分类
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;

        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $langData = array(
            $this->langData->project->information_summary,
            $this->langData->project->cancel_collection
        );
        $artctg = $this->getArticleCtg($lang);

        $sql = 'SELECT * FROM ' . DB_PREFIX . 'article AS a LEFT JOIN ' . DB_PREFIX . 'article_lang AS al ON a.id=al.article_id where a.id=' . $artid . '
       AND al.lang_id=' . $lang;
        $detail = $articleMod->querySql($sql);
        $recommGoods = $this->getRcommGoods($this->storeid);
        // 商品评价星级
        foreach ($recommGoods as $k => $v) {
            $good_id = $v['id'];
            $sql = 'select  sum(goods_rank) / count(goods_rank) as res,count(*) as num from ' . DB_PREFIX . 'goods_comment where goods_id = ' . $good_id;
            $trance = $this->goodsCommentMod->querySql($sql);
            $recommGoods[$k]['rate'] = $trance[0]['res'];
            $recommGoods[$k]['num'] = $trance[0]['num'];
        }
        //更多精彩
        $catid = $detail[0]['cat_id']; //该文章的分类
        $moreArticles = $this->getMoreArticle($this->store_id, $catid, $artid, $limit = 5);
        //收藏文章
        $userId = $this->userId;
        $sql_collection = 'select * from ' . DB_PREFIX . 'user_article where user_id=' . $this->userId . ' and store_id=' . $this->store_id;
//            echo $sql_collection;exit;
        $data_collection = $articleMod->querySql($sql_collection);
//            var_dump($data_collection);exit;
        foreach ($data_collection as &$collertion) {
            if ($collertion['article_id'] == $detail[0]['article_id']) {
                $detail[0]['type'] = 1;
            }
        }
        $data = array(
            'langData' => $langData,
            'listData' => $detail[0]
        );
        $this->setData($data, 1, '');
    }

    /**
     * 更多精彩 5条
     * @author wangh
     * @date 2017/09/13
     */
    public function getMoreArticle($storeid, $catid, $artid, $limit = 5)
    {
        $articleMod = &m('article');

        if (!empty($catid)) {
            $where = '  where   a.store_id =' . $storeid . '  and   a.cat_id  in(' . $catid . ')  and  a.id !=' . $artid;
        } else {
            $where = '  where   a.store_id =' . $storeid . '  and  a.id !=' . $artid;
        }

        $sql = 'SELECT  a.id,a.`title`,a.`english_title`,a.`cover_photo`,a.`brief`,a.store_id,a.br_num,c.`id`  AS  cid,c.`name`
                 FROM   ' . DB_PREFIX . 'article AS a LEFT JOIN  ' . DB_PREFIX . 'article_category AS c ON a.`cat_id` = c.`id` ' . $where;
        $sql .= '  order  by  a.id  desc  limit ' . $limit;
        $res = $articleMod->querySql($sql);
        $data = array();
        $total = 0;
        if (!empty($res)) {
            foreach ($res as $key => $val) {
                $data[] = $val;
                $total++;
            }
        }
        //如果不够5条
        if ($total < 5) {
            $sql2 = 'SELECT  a.id,a.`title`,a.`english_title`,a.`cover_photo`,a.`brief`,a.store_id,a.br_num,c.`id`  AS  cid,c.`name`
                 FROM   ' . DB_PREFIX . 'article AS a LEFT JOIN  ' . DB_PREFIX . 'article_category AS c ON a.`cat_id` = c.`id`
                 where  a.store_id =' . $storeid . '   and  a.id!=' . $artid . '  and  a.cat_id  not in(' . $catid . ')  order by a.id  desc  limit ' . ($limit - $total);

            $res2 = $articleMod->querySql($sql2);
            if (!empty($res2)) {
                foreach ($res2 as $v) {
                    $data[] = $v;
                }
            }
        }

        return $data;
    }

    /**
     * 商品推荐 取销量前5的
     * @author wangh
     * @date 2017/09/13
     */
    public function getRcommGoods($storeid)
    {
        $storeGoodsMod = &m('areaGood');
        $limit = '  limit  5';
        $where = '  where   mark =1  and  store_id =' . $storeid . '  and   is_on_sale =1';
        $sql = 'select g.*,l.*, g.id,gl.original_img  from  '
            . DB_PREFIX . 'store_goods as g inner join '
            . DB_PREFIX . 'goods as gl on g.goods_id = gl.goods_id inner join '
            . DB_PREFIX . 'goods_lang as l on l.goods_id = g.goods_id and l.lang_id=' . $this->lang_id
            . $where;
        $sql .= '  order by l.id  desc' . $limit;
        $data = $storeGoodsMod->querySql($sql);
        foreach ($data as &$item) {
            //是否包邮
            switch ($item['is_free_shipping']) {
                case 1:
                    $item['isfree'] = $a['article_Free'];
                    break;
                case 2:
                    $item['isfree'] = $a['article_No'];
                    break;
                default:
                    $item['isfree'] = $a['article_No'];
            }
            //
        }
        return $data;
    }

    /**
     * 文章分类
     * @author wangh
     * @date 2017/09/19
     */
    public function getArticleCtg($lang)
    {
        $artCtgMod = &m('articleCate');
        $sql = 'SELECT a.id,ac.article_cate_name,ac.lang_id FROM ' . DB_PREFIX . 'article_category AS a LEFT JOIN ' . DB_PREFIX . 'article_category_lang AS ac ON a.id=ac.article_cate_id where ac.lang_id=' . $lang;
        $data = $artCtgMod->querySql($sql);

        return $data;
    }

    public function getType()
    {
        /*        $goodMod = &m('goods');
                $storeGoodMod = &m("storeGoodItemPrice");*/
        $storeGoods = &m('areaGood');
        $id = $_REQUEST['id'];
        $goods_id = $_REQUEST['goods_id'];
        /*  $storeid=$_REQUEST['storeid'];*/
        $lang = $_REQUEST['lang_id'];
        $source = $_REQUEST['source'];
        $cid = $_REQUEST['cid'];
        $latlon = $_REQUEST['latlon'];
        $store_id = $_REQUEST['store_id'];
        $goodinfo = $storeGoods->getLangInfo1($id, $lang);
        if ($source == 3) {
            $sql = " select ps.*,pg.goods_key as item_id,pg.goods_price,pg.discount_price,pg.limit_amount,ps.status  from " . DB_PREFIX . "promotion_sale as ps left join " . DB_PREFIX . "promotion_goods as pg on 
                ps.id = pg.prom_id  where ps.`store_id` = $this->storeid and ps.`status` in (1,2) and ps.`mark` =1 and ps.id =$cid  and pg.goods_id=" . $id;
            $arr = $storeGoods->querySql($sql);
            foreach ($arr as $k => $v) {
                $arr1 = explode('_', $v['item_id']);

            }


        }
        // 团购
        if ($source == 2) {
            $where2 = '  where  store_id = ' . $store_id . ' and  mark =1 and id=' . $cid;
            $sql2 = 'SELECT  goods_spec_key as item_id,group_goods_price,goods_price,group_goods_num,virtual_num,end_time,title as name,start_time
                FROM  ' . DB_PREFIX . 'goods_group_buy ' . $where2;
            $arr = $storeGoods->querySql($sql2);
            foreach ($arr as $v) {
                $arr1 = explode('_', $v['item_id']);
            }
        }
        $spec_img1 = $this->get_spec1($goodinfo['goods_id'], $id, 2);

        if (!empty($arr1)) {
            foreach ($spec_img1 as $key => $value) {
                foreach ($value as $k => $v) {
                    if (!in_array($v['item_id'], $arr1))
                        unset($spec_img1[$key][$k]);
                }
            }
        }

        foreach ($spec_img1 as $key1 => $value1) {
            foreach ($value1 as $k1 => $v1) {
                $spec_img[$key1][] = $v1;
            }
        }

        $storeList = $this->getCountryStore($this->countryId, $goods_id, $latlon);
        $info = array('spec_img' => $spec_img, 'info' => $goodinfo, 'lang' => $lang);
        $this->setData($info, 1, '');

    }

    public function getSpecs()
    {
        $id = $_REQUEST['store_goods_id'];
        // var_dump($id);die;
        $storeGoodMod = &m("storeGoodItemPrice");
        $spec_data = $storeGoodMod->getData(array('cond' => "store_goods_id=" . $id));
//        echo '<pre>';var_dump($spec_data);die;
        foreach ($spec_data as $k => $v) {
//            $spec_arr[$v['key']] = $v;
            $spec_arr[] = $v;
        }
//        $spec_arr=json_encode($spec_arr);
        $info = array('id' => $id, 'spec_arr' => $spec_arr);
        $this->setData($info, 1, '');
    }

    /**
     * 2级业务
     * @author tangp
     * @date 2018-12-18
     */
    public function getRoomTypeArr()
    {
        $storeGoodsMod = &m('areaGood');
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        $lang_id = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? $_REQUEST['auxiliary'] : 0;
        $rtid = !empty($_REQUEST['rtid']) ? $_REQUEST['rtid'] : 0;
        $latlon = !empty($_REQUEST['latlon']) ? $_REQUEST['latlon'] : 0;
        $store_sql = 'select store_discount,background_img from  ' . DB_PREFIX . 'store where id=' . $this->store_id;
        $store_arr = $storeGoodsMod->querySql($store_sql);
        $roomtypearr = $this->getgoodRoomTypearr($this->lang_id, $rtid);
        foreach ($roomtypearr as $kk => $vv) {
            $roomId[] = $vv['id'];
        }
        $roomIds = implode(',', $roomId);
        $hotGoods = $this->getHotGoods($roomIds, $storeid);
        $activiData = $this->getACtivity($roomIds, $storeid);
        $goodsCommentMod = &m('goodsComment');
        if (!empty($activiData['data'])) {
            foreach ($activiData['data'] as $key => $val) {
                $oSql = "SELECT rec_id,goods_num FROM " . DB_PREFIX . 'order_goods WHERE goods_id=' . $val['gid'] . " and order_state in (20,30,40,50)";
                $oData = $storeGoodsMod->querySql($oSql);
                if (!empty($oData)) {
                    $num = 0;
                    foreach ($oData as $k => $v) {
                        $num += $v['goods_num'];
                    }
                    $activiData['data'][$key]['order_num'] = $num;
                } else {
                    $activiData['data'][$key]['order_num'] = 0;
                }
                $sql = 'select  sum(goods_rank) / count(goods_rank) as res,count(*) as num from ' . DB_PREFIX . 'goods_comment where goods_id = ' . $val['gid'];
                $trance = $goodsCommentMod->querySql($sql);
                $activiData['data'][$key]['rate'] = (int)$trance[0]['res'];
                $res = $activiData['data'][$key]['user_num'] = $this->getUserNum($val['source'], $val['cid'], $val['gid']);

            }
        }
        //热销商品和优惠商品名称
        $sql = 'select * from bs_config  where  inc_type = "shop_info" ';
        $data = $goodsCommentMod->querySql($sql);
        $res = array();
        foreach ($data as $key => $val) {
            $res[$val['name']] = $data[$key];
        }
        $langData = array(
//            $this->langData->project->preferential_goods,
//            $this->langData->project->hot_selling_goods,
            $this->langData->project->monthly_sale,
            $this->langData->project->select_spec,
        );
        $listData = array(
            'background_img' => $store_arr[0]['background_img'],
            'activiData' => $activiData,//活动数据
            'hotGoods' => $hotGoods,//热销商品
            'symbol' => $this->symbol,//币种
            'roomtypearr' => $roomtypearr,//业务类别
            'res' => $res
        );
        $info = array(
            'listData' => $listData,
            'langData' => $langData
        );
        $this->setData($info, 1, '');
    }

    /**
     * 异步加载商品
     * @author tangp
     * @date 2018-12-18
     */
    public function getRoomGoods()
    {
        $roomId = !empty($_REQUEST['roomId']) ? $_REQUEST['roomId'] : 0;
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        $roomIds = !empty($_REQUEST['roomids']) ? $_REQUEST['roomids'] : 0;
        $type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : 0;
        $storeGoodsMod = &m('areaGood');
        if ($type == 1) {
            $activiData = $this->getACtivity($roomIds, $storeid);
            $goodsCommentMod = &m('goodsComment');
            foreach ($activiData['data'] as $key => $val) {
                $oSql = "SELECT rec_id,goods_num FROM " . DB_PREFIX . 'order_goods WHERE goods_id=' . $val['gid'] . " and order_state in (20,30,40,50)";
                $oData = $storeGoodsMod->querySql($oSql);
                if (!empty($oData)) {
                    $num = 0;
                    foreach ($oData as $k => $v) {
                        $num += $v['goods_num'];
                    }
                    $activiData['data'][$key]['order_num'] = $num;
                } else {
                    $activiData['data'][$key]['order_num'] = 0;
                }
                $sql = 'select  sum(goods_rank) / count(goods_rank) as res,count(*) as num from ' . DB_PREFIX . 'goods_comment where goods_id = ' . $val['gid'];
                $trance = $goodsCommentMod->querySql($sql);
                $activiData['data'][$key]['rate'] = (int)$trance[0]['res'];
                $activiData['data'][$key]['user_num'] = $this->getUserNum($val['source'], $val['cid'], $val['gid']);
            }
            $gooodInfo = array('data' => $activiData, 'type' => 1);
            if ($activiData) {
                $this->setData($gooodInfo, 1, '');
            }
        }
        if ($type == 2) {
            $hotGoods = $this->getHotGoods($roomIds, $storeid);
            $goodInfo = array('data' => $hotGoods, 'type' => 2);
            if ($hotGoods) {
                $this->setData($goodInfo, 1, '');
            }
        }
        if ($type == 3) {
            $where = '  where   s.store_id =' . $storeid . '   and rc.room_type_id = ' . $roomId . '  and   s.mark=1   and   s.is_on_sale =1  AND l.`lang_id` = ' . $this->lang_id;
            //所以子类的商品
            $rsql = 'SELECT s.id,rc.sort,s.`goods_id`,s.`cat_id`,s.`store_id`,l.`goods_name`,l.`lang_id`,s.`shop_price`,s.`market_price`,s.`brand_id`,gl.`original_img`,s.is_free_shipping,s.add_time,l.goods_remark,s.goods_storage
                FROM  ' . DB_PREFIX . 'store_goods AS s LEFT JOIN  '
                . DB_PREFIX . 'room_category AS rc on s.cat_id=rc.category_id LEFT JOIN '
                . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id`  left join '
                . DB_PREFIX . 'goods as gl on s.goods_id = gl.goods_id ' . $where;
            $goods = $storeGoodsMod->querySql($rsql);
            foreach ($goods as $k1 => $v1) {
                //店铺商品打折
                $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $storeid;
                $store_arr = $storeGoodsMod->querySql($store_sql);
                $goods[$k1]['shop_price'] = number_format($v1['shop_price'] * $store_arr[0]['store_discount'], 2);
                $member_price = $v1['shop_price'] * $store_arr[0]['store_discount'] - ($this->getPointAccount($v1['shop_price'] * $store_arr[0]['store_discount'], $storeid));
                $goods[$k1]['member_price'] = number_format($member_price, 2);
                $goods[$k1]['sale_price'] = number_format($v1['market_price'], 2);
                $oSql = "SELECT rec_id,goods_num  FROM " . DB_PREFIX . 'order_goods WHERE goods_id=' . $v1['id'] . " and order_state in (20,30,40,50)";
                $oData = $storeGoodsMod->querySql($oSql);
                if (!empty($oData)) {
                    $sum = 0;
                    foreach ($oData as $k2 => $v2) {
                        $sum += $v2['goods_num'];
                    }
                    $goods[$k1]['order_num'] = $sum;
                } else {
                    $goods[$k1]['order_num'] = 0;
                }
                $goodsCommentMod = &m('goodsComment');
                $sql = 'select  sum(goods_rank) / count(goods_rank) as res,count(*) as num from ' . DB_PREFIX . 'goods_comment where goods_id = ' . $v1['id'];
                $trance = $goodsCommentMod->querySql($sql);
                $goods[$k1]['rate'] = (int)$trance[0]['res'];
                $goods[$k1]['num'] = $trance[0]['num'];
            }
            $gooodInfo = array('data' => $goods, 'type' => 3);
            if ($goods) {
                $this->setData($gooodInfo, 1, '');
            }
        }
    }

    public function getUserNum($source, $prom_id, $store_goods_id)
    {
        $storeGoodsMod = &m('areaGood');
        $sql = "select sum(og.goods_num) as total from " . DB_PREFIX . 'order  as o  left join ' . DB_PREFIX . 'order_goods as og ON og.order_id = o.order_sn 
        where o.buyer_id=' . $this->userId . ' and  og.prom_type=' . $source . ' and og.prom_id=' . $prom_id . ' and og.goods_id=' . $store_goods_id . ' and o.mark=1 and o.order_state >=20';
        $sum = $storeGoodsMod->querySql($sql);
        if (empty($sum[0]['total'])) {
            $sum[0]['total'] = 0;
        }

        return $sum[0]['total'];
    }


    //获取秒杀和促销商品
    public function getPromotionGoods()
    {
        $storeGoodsId = !empty($_REQUEST['storeGoodsId']) ? $_REQUEST['storeGoodsId'] : 11355;//店铺商品id
        $activityId = !empty($_REQUEST['activityId']) ? $_REQUEST['activityId'] : 40;//活动Id
        $storeId = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : 84;//店铺Id
        $langId = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : 29;//语言Id
        //模型
        $goodsImgMod = &m('goodsImg');
        $storeGoodsMod = &m('areaGood');
        $promotionMod =& m('goodProm');
        $promotionGoodsMod =& m('goodPromDetail');
        $storeGoodsItemMod =& m('storeGoodItemPrice');
        $goodAttrMod = &m('goodsAttriInfo');
        //数据
        $storeGoodsData = $storeGoodsMod->getOne(array('cond' => "`id`= '{$storeGoodsId}'", 'fields' => 'goods_id,shop_price,goods_storage'));//店铺商品信息
        $prommotionData = $promotionMod->getOne(array('cond' => "`id`= '{$activityId}' AND mark = 1 ", 'fields' => 'end_time'));//促销活动信息
        $promotionGoodsData = $promotionGoodsMod->getOne(array('cond' => "`goods_id`= '{$storeGoodsId}'  AND `prom_id`='{$activityId}' ", 'fields' => 'id,discount_rate,goods_name,discount_price,goods_price,goods_img'));//促销活动商品信息
        $storeGoodsItemData=$storeGoodsItemMod->get_relattion_spec($storeGoodsData['goods_id'],$storeGoodsId,$langId);//促销商品规格信息
        $imgArrData = $goodsImgMod->getData(array('cond' => "goods_id=" . $storeGoodsData['goods_id']));//拿轮播图
        $storeGoodsInfo = $storeGoodsMod->getLangInfo($storeGoodsId, $langId);//店铺中英文商品信息
        $attrArr = $goodAttrMod->getLangData($storeGoodsData['goods_id'], $langId);//商品属性(暂时死数据展示，参数一 原始商品ID  参数二 语言ID)
        $discountRate = $promotionGoodsData['discount_rate'];
        if (!empty($storeGoodsItemData)) {
            //有规格
            $storeGoodsItemJsonData = $storeGoodsItemMod->getRelationSpecName($storeGoodsId, $storeId);//促销商品规格信息json格式
            foreach($storeGoodsItemData as $k=>$v){
                foreach($v['spec_data'] as $key=>$val){
                    if($key == 0){
                        $itemId=$val['item_id'];
                        $item = $val['item'];
                    }
                }
                $singleItemKeyName[]=$item;//选定规格名称
                $itemStr .=$itemId.'_';
            }
            $itemStr = rtrim($itemStr, '_');//选定规格组合
            $singleStoreGoodsItemData = $storeGoodsItemMod->getOne(array('cond' => "`store_goods_id`= '{$storeGoodsId}'  AND `key`='{$itemStr}' ", 'fields' => 'goods_storage,price,key_name'));
            $goodsKeyName = $singleStoreGoodsItemData['key_name'];
            $singleStoreGoodsItemData['price'] = number_format($singleStoreGoodsItemData['price'] * $discountRate / 10, 2);//规格价格
            $type=1;
        } else { //无规格
            $storeGoodsItemJsonData = array();
            $itemStr = '';
            $goodsKeyName = '';
            $singleStoreGoodsItemData['goods_storage'] = $storeGoodsData['goods_storage'];//无规格库存
            $singleStoreGoodsItemData['price'] = number_format($storeGoodsData['shop_price'] * $discountRate / 10, 2);//无规格价格
            $type=2;
        }
        $rand = $this->buildNo(1);
        $orderNo = date('YmdHis') . $rand[0];
        $promotionData = array(
            'imgArrData' => $imgArrData,//商品轮播图
            'prommotionData' => $prommotionData,// 促销活动详情
            'promotionGoodsData' => $promotionGoodsData, //促销活动商品详情
            'singleItemKeyName' => $singleItemKeyName, //页面展示规格
            'storeGoodsInfo' => $storeGoodsInfo,// 店铺商品详情数据
            'attrArr' => $attrArr, //商品属性
            'storeGoodsItemJsonData' => $storeGoodsItemJsonData,//商品规格信息json格式
            'singleStoreGoodsItemData' => $singleStoreGoodsItemData, //有无规格的规格和库存
            'storeGoodsItemData' => $storeGoodsItemData,
            'type'=>$type,
            'orderSn'=>$orderNo
        );
        if($promotionData){
            $this->setData($promotionData,1,'');
        }
    }
    //库存查验
    public function checkoutNum(){
        $storeGoodsId=!empty($_REQUEST['storeGoodsId']) ? $_REQUEST['storeGoodsId'] : 0;//店铺商品id
        $activityId=!empty($_REQUEST['activityId']) ? $_REQUEST['activityId'] : 0;//活动Id
        $storeId=!empty($_REQUEST['storeId']) ? $_REQUEST['storeId'] : 0;//店铺Id
        $langId=!empty($_REQUEST['langId']) ? $_REQUEST['langId']  : 0;//语言Id
        $num=!empty($_REQUEST['num']) ? $_REQUEST['num']  : 0;//数量
        $goodsKey=!empty($_REQUEST['goodsKey']) ? $_REQUEST['goodsKey']  : ''; //规格
        $goodsKeyName=!empty($_REQUEST['goodsKeyName']) ? $_REQUEST['goodsKeyName']  : ''; //规格名称
        $type=!empty($_REQUEST['type'])?$_REQUEST['type'] : 0;  //js跳转判定
        $discountPrice=!empty($_REQUEST['discountPrice']) ? $_REQUEST['discountPrice'] : 0;  //促销价格
        $buyerId=!empty($_REQUEST['buyerId']) ? $_REQUEST['buyerId'] : 0;//购买者

        //模型
        $storeGoodsMod = &m('areaGood');
        $promotionGoodsMod=&m('goodPromDetail');
        $storeGoodsItemMod=&m('storeGoodItemPrice');
        $orderGoodsMod=&m('orderGoods');
        //数据
        $prommotionGoodsData=$promotionGoodsMod->getOne(array('cond'=>"`goods_id`= '{$storeGoodsId}'  AND `prom_id`='{$activityId}' ",'fields'=>'limit_amount,id'));//促销活动商品信息
        $limitNum=$prommotionGoodsData['limit_amount'];//限购数量
        $userNum=$orderGoodsMod->getActivityOrderNum(2,$activityId,$storeGoodsId,$buyerId);//用户购买数量
        if(!empty($goodsKey)){
            $singleStoreGoodsItemData=$storeGoodsItemMod->getOne(array('cond'=>"`store_goods_id`= '{$storeGoodsId}'  AND `key`='{$goodsKey}' ",'fields'=>'goods_storage,price,key_name'));//库存信息
            $goodsStorage=$singleStoreGoodsItemData['goods_storage'];//有规格库存
        }else{
            $storeGoodsData=$storeGoodsMod->getOne(array('cond'=>"`id`= '{$storeGoodsId}'",'fields'=>'goods_id,shop_price,goods_storage'));//店铺商品信息
            $goodsStorage=$storeGoodsData['goods_storage'];//无规格库存
        }
        $data=array(
            'limitNum'=>$limitNum,
            'num'=>$num,
            'userNum'=>$userNum,
            'goodsStorage'=>$goodsStorage
        );
        if($data){
            $this->setData($data,1,'');
        }
    }
    //活动商品页面
    public function activityOrder(){
        //模型
        $spikeActiviesMod=&m('spikeActivies');
        $spikeActiviesGoodsMod=&m('spikeActiviesGoods');
        $userMod=&m('user');
        $storeMod=&m('store');
        $storeGoodsMod=&m('storeGoods');
        $promotionGoodsMod=&m('goodPromDetail');
        $promotionMod=&m('goodProm');
        $info=!empty($_REQUEST['info']) ? $_REQUEST['info'] : '';
        $langId = !empty($_REQUEST['langId']) ? intval($_REQUEST['langId']) : 0/*$this->langid*/;
        $storeId = !empty($_REQUEST['storeId']) ? intval($_REQUEST['storeId']) :0/* $this->storeid*/;
        $activityId=!empty($_REQUEST['activityId']) ? intval($_REQUEST['activityId']) : 0; //活动Id
        $activityGoodsId=!empty($_REQUEST['activityGoodsId']) ? intval($_REQUEST['activityGoodsId']) : 0;//活动商品Id
        $source=!empty($_REQUEST['source']) ? intval($_REQUEST['source']) : 0 ; //活动来源 1 ：秒杀 2：促销
        $goodsNum=!empty($_REQUEST['goodsNum']) ? intval($_REQUEST['goodsNum']) : 0; //商品数量
        $goodsKey=!empty($_REQUEST['goodsKey']) ? $_REQUEST['goodsKey'] : '';  //促销活动商品规格
        $goodsKeyName=!empty($_REQUEST['goodsKeyName']) ? $_REQUEST['goodsKeyName'] : '';//促销活动商品规格名称
        $discountPrice=!empty($_REQUEST['discountPrice']) ? $_REQUEST['discountPrice'] : 0;//促销活动商品规格价格
        $orderSn = !empty($_REQUEST['orderSn']) ? $_REQUEST['orderSn'] : ''; //订单编号
        //获取收货地址
        $addr_id = !empty($_REQUEST['addr_id']) ? htmlspecialchars(trim($_REQUEST['addr_id'])) : '';
        if ($addr_id=='') {
            $where = ' and default_addr =1';
        } else {
            $where = ' and id=' . $addr_id;
        }
        $addrSql = "select * from " . DB_PREFIX . 'user_address where distinguish=1 and  user_id=' . $this->userId . $where;
        $userAddress = $userMod->querySql($addrSql); // 获取用户的地址
        if ($addr_id == '0') {
            $addr_id = $userAddress[0]['id'];
        }
        $addresss = explode('_', $userAddress[0]['address']);
        $count = strpos($userAddress[0]['address'], "_");
        $str = substr_replace($userAddress[0]['address'], "", $count, 1);
        //秒杀活动
        if($source==1){
            $activityGoodsData=$spikeActiviesGoodsMod->getOne(array('cond'=>"`id`= '{$activityGoodsId}' AND mark = 1 ",'fields'=>'store_goods_id,discount_price,goods_img,goods_name,goods_key_name,goods_key,goods_price'));
            $activityData=$spikeActiviesMod->getOne(array('cond'=>"`id`= '{$activityId}' AND mark = 1 ",'fields'=>'store_id'));
            $storeGoodsId=$activityGoodsData['store_goods_id'];
            $activityGoodsData['isFreeShipping']=$storeGoodsMod->isFreeShipping($storeGoodsId);
            $activityGoodsData['sendout']=$storeGoodsMod->getGoodsSendoutArr($storeGoodsId);
            $activityGoodsData['sendoutStr']=$storeGoodsMod->getGoodsSendout($storeGoodsId);
            $activityGoodsData['sendoutIndex']=key($activityGoodsData['sendout']);
            $activityGoodsData['sendoutValue']=current($activityGoodsData['sendout']);
            $info['goodsPrice'] = $activityGoodsData['goods_price'];
        }
        //促销活动
        if($source==2){
            $activityGoodsData=$promotionGoodsMod->getOne(array('cond'=>"`id`= '{$activityGoodsId}' ",'fields'=>'goods_id,goods_img,goods_name,goods_price'));
            $activityData=$promotionMod->getOne(array('cond'=>"`id`= '{$activityId}' AND mark = 1 ",'fields'=>'store_id'));
            $storeGoodsId=$activityGoodsData['goods_id'];
            $activityGoodsData['isFreeShipping']=$storeGoodsMod->isFreeShipping($storeGoodsId);
            $activityGoodsData['sendout']=$storeGoodsMod->getGoodsSendoutArr($storeGoodsId);
            $activityGoodsData['sendoutStr']=$storeGoodsMod->getGoodsSendout($storeGoodsId);
            $activityGoodsData['sendoutIndex']=key($activityGoodsData['sendout']);
            $activityGoodsData['sendoutValue']=current($activityGoodsData['sendout']);
            $activityGoodsData['goods_key']=$goodsKey;
            $activityGoodsData['goods_key_name']=$goodsKeyName;
            $activityGoodsData['discount_price']=$discountPrice;
            $info['goodsPrice'] = $activityGoodsData['goods_price'];
        }
        $shippingPrice=$storeGoodsMod->getOne(array('cond'=>"`id`='{$storeGoodsId}'",'fields'=>'shipping_price'));
        $storeName=$storeMod->getNameById($activityData['store_id'],$langId);
        $totalMoney = ( $activityGoodsData['discount_price']* $goodsNum);
        $info['langId'] =   $langId;
        $info['storeId'] = $activityData['store_id'];
        $info['activityId'] = $activityId;
        $info['activityGoodsId'] = $activityGoodsId;
        $info['source'] = $source;
        $info['goodsNum'] = $goodsNum ;
        $info['goodsKey'] = $goodsKey;
        $info['goodsKeyName'] = $goodsKeyName;
        $info['discountPrice'] = $discountPrice;
        $info['storeGoodsId']=$storeGoodsId;
        $info['orderSn'] = $orderSn;
        $data=array(
            'activityData'=>$activityData,
            'activityGoodsData'=>$activityGoodsData,
            'goodsNum'=>$goodsNum,
            'totalMoney'=>$totalMoney,
            'storeId'=>$activityData['store_id'],
            'langId'=>$langId,
            'source'=>$source,
            'activityId'=>$activityId,
            'activityGoodsId'=>$activityGoodsId,
            'storeGoodsId'=>$storeGoodsId,
            'shippingPrice'=>$shippingPrice['shipping_price'],
            'storeName'=>$storeName,
            'userAddress'=>$userAddress[0],
            'activityInfo'=>base64_encode(json_encode($info))
        );
        $this->setData($data,1,'');
    }

    /**
     * 店铺列表头部 ---- 临时   2019-07-24
     */
    public  function newGoodsList()
    {
        $storeId = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        $lang_id = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;

        $storeGoodsMod = &m('areaGood');
        $store_sql = "SELECT store_discount,background_img,logo,store_notice,store_start_time,store_end_time FROM bs_store WHERE id=" . $storeId;
        $store_sql1 = "SELECT count(*) as counts FROM bs_user_comment WHERE is_show = 1 AND store_id=" . $storeId;
        $store_count = $storeGoodsMod->querySql($store_sql1);
        $store_arr = $storeGoodsMod->querySql($store_sql);

        $storeMod = &m('store');
        $storeName = $storeMod->getNameById($storeId, $lang_id);
        $background_img = unserialize($store_arr[0]['background_img']);
        foreach ($background_img as &$background){
            $background['background'] = '/web/uploads/big/' . $background['background'];
            $background['path_url']=!empty($background['url'])?$background['url']:'';
        }
        $listData = array(
            'comments' => $store_count[0]['counts'],
            'notice' => $store_arr[0]['store_notice'],
            'storeName' => $storeName,
            'background_img' => $background_img,
            'logo' => '/web/uploads/small/'.$store_arr[0]['logo'],
            'store_arr' => $store_arr[0]['store_discount'],
            'store_end_time' => $store_arr[0]['store_end_time'],
            'store_start_time' => $store_arr[0]['store_start_time'],
            'symbol' => $this->symbol,
            'storeId' => $storeId,
        );
        $this->setData($listData, 1, '');
    } 

    /**
     * 不同活动数据生成订单
     */
    public function genOrder($source, $genInfo, $goodInfo, $lang_id) {
        $fxUserMod = &m('fxuser');
        $orderMod=&m('order');
        $orderDetailMod=&m('orderDetail');
        $insert_main_data = array(
            'order_sn' => $genInfo['orderNo'],
            'store_id' => $genInfo['store_id'],
            'store_name' => $genInfo['store_name'],
            'buyer_id' => $genInfo['buyer_id'],
            'buyer_name' => $genInfo['buyer_name'],
            'buyer_email' => $genInfo['buyer_email'],
            'shipping_fee' => $genInfo['shipping_fee'],
            'order_state' => 10,
            'order_from' => 2,
            'buyer_address' => $genInfo['buyer_address'],
            'order_amount' => $genInfo['order_amount'],
            'discount' => $genInfo['discount'],
            'fx_phone' => $genInfo['fxPhone'],
            'buyer_phone' => $genInfo['buyer_phone'],
            'add_time' => time(),
            'seller_msg' => $genInfo['seller_msg'],
            'sendout' => $genInfo['sendout'],
            'goods_amount' => $genInfo['goods_amount'],
            'number_order' => $genInfo['number_order'], //生成小票编号
            'sub_user' => 2,
            'fx_user_id'=>0,
            'is_source'=>1
        );


        $insert_sub_data = array(
            'order_id' => $genInfo['orderNo'],
            'store_id' => $genInfo['store_id'],
            'buyer_id' => $genInfo['buyer_id'],
            'prom_id' => $genInfo['prom_id'],
            'prom_type' => $genInfo['prom_type'],
            'order_state' => 10,
            'add_time' => time(),
            'goods_type' => 1,
            'goods_id' => $genInfo['goods_id'],
            'goods_name' => $genInfo['goods_name'],
            'goods_price' => $genInfo['goods_price'],
            'goods_num' => $genInfo['goods_num'],
            'goods_image' => $genInfo['goods_image'],
            'goods_pay_price' => $genInfo['goods_pay_price'],
            'spec_key_name' => $genInfo['spec_key_name'],
            'spec_key' => $genInfo['spec_key'],
            'shipping_store_id' => $genInfo['shipping_store_id'],
            'shipping_price' => $genInfo['shipping_fee'],
            'good_id'=>$genInfo['good_id'],
            'deduction'=>$genInfo['deduction']
        );
        if ($source == 1) {
            if (!empty($genInfo['fxPhone'])) {
                $discount = (($goodInfo['price'] * $genInfo['goods_num'])) * $genInfo['discount_rate'] * 0.01;
            }
            // 先插入主订单数据
            $insert_main_data['goods_amount'] = ($goodInfo['discount_price'] * $genInfo['goods_num']);
            $insert_main_data['order_amount'] = ($goodInfo['discount_price'] * $genInfo['goods_num']) + $genInfo['shipping_fee'];
            $insert_main_data['discount'] = $discount;
            $insert_main_data['discount_rate'] = $genInfo['discount_rate'];
            // 插入子表信息
            $insert_sub_data['prom_type'] = 1;
            $insert_sub_data['goods_id'] = $goodInfo['store_goods_id'];
            $insert_sub_data['goods_name'] = addslashes(stripslashes($goodInfo['goods_name']));
            $insert_sub_data['goods_price'] = $goodInfo['goods_price'];
            $insert_sub_data['goods_image'] = $goodInfo['goods_img'];
            $insert_sub_data['spec_key_name'] = $genInfo['spec_key_name'];
            $insert_sub_data['spec_key'] = $genInfo['spec_key'];
        }else{
            if (!empty($genInfo['fxPhone'])) {
                $discount = (($goodInfo['price'] * $genInfo['goods_num'])) * $genInfo['discount_rate'] * 0.01;
            }
            // 先插入主订单数据
            $insert_main_data['goods_amount'] = ($goodInfo['discount_price'] * $genInfo['goods_num']);
            $insert_main_data['order_amount'] = ($goodInfo['discount_price'] * $genInfo['goods_num']) + $genInfo['shipping_fee'];
            $insert_main_data['discount'] = $discount;
            $insert_main_data['discount_rate']= $genInfo['discount_rate'];
            // 插入子表信息
            $insert_sub_data['prom_type'] = 2;
            $insert_sub_data['goods_id'] = $goodInfo['goods_id'];
            $insert_sub_data['goods_name'] = addslashes(stripslashes($goodInfo['goods_name']));
            $insert_sub_data['goods_price'] = $goodInfo['goods_price'];
            $insert_sub_data['goods_image'] = $goodInfo['goods_img'];
            $insert_sub_data['spec_key_name'] = $genInfo['spec_key_name'];
            $insert_sub_data['spec_key'] = $genInfo['spec_key'];
        }
        if ($insert_main_data['order_amount'] <= 0) {
            $insert_main_data['order_amount'] = 0.01;
        }
        try {
            //事务开始
            $orderMod->begin();
            $main_rs = $orderMod->doInsert($insert_main_data);
            //生成新的订单表数据
            $createOrderRes = $orderMod->createOrder($insert_main_data,2);
            if (empty($main_rs) || empty($createOrderRes)) {
                //事务回滚
                $orderMod->rollback();
                return 0;
            } else {
                //事务提交
                $orderMod->commit();
            }
        } catch (Exception $e) {
            //事务回滚
            $orderMod->rollback();
            writeLog($e->getMessage());
            return 0;
        }

        if ($main_rs) {
            if (!empty($genInfo['fx_user_id']) && !empty($genInfo['rule_id'])){
                $userId = $this->userId;
                $fxUserMod = &m('fxuser');
                $fx_info = $fxUserMod->getOne(array("cond" => "user_id='" . $userId . "'"));
                if ($fx_info['fx_code'] !== $genInfo['fxPhone']){
                    $fxOrderData = array(
                        'order_id' => $main_rs,
                        'order_sn' => $genInfo['orderNo'],
                        'source' => 2,
                        'user_id' => $this->userId,
                        'fx_user_id' => $genInfo['fx_user_id'],
                        'rule_id' => $genInfo['rule_id'],
                        'store_cate' => $genInfo['storeCate'],
                        'store_id' => $genInfo['store_id'],
                        'add_time' => time(),
                        'add_user' => $this->userId,
                        'pay_money' => $genInfo['order_amount']
                    );
                    $fxOrderMod =& m('fxOrder');
                    $fxUserAccountMod = &m('fxUserAccount');
                    $fxOrderMod->doInsert($fxOrderData);
                    $fxUserAccountMod->addFxUser($genInfo['fx_user_id'], $this->userId);
                }
            }
            $rs = $orderDetailMod->doInsert($insert_sub_data);
            return $main_rs;
        } else {
            return 0;
        }
    }

    //腾讯转百度坐标转换
    function coordinate_switchf($a, $b){
        $x = (double)$b ;
        $y = (double)$a;
        $x_pi = 3.14159265358979324;
        $z = sqrt($x * $x+$y * $y) + 0.00002 * sin($y * $x_pi);
        $theta = atan2($y,$x) + 0.000003 * cos($x*$x_pi);
        $gb = number_format($z * cos($theta) + 0.0065,6);
        $ga = number_format($z * sin($theta) + 0.006,6);

        return array(
            'Latitude'=>$ga,
            'Longitude'=>$gb
        );
    }





    /**
     * 获取省市区的地址
     * @author wanyan
     * @date 2017-1-17
     */
    public function getAddress($areaAddress) {
        $areaAddress = explode('_', $areaAddress);
        if (count($areaAddress) == 3) {
            $result = $this->cityMod->getAreaName($areaAddress[0]) . ' ' . $this->cityMod->getAreaName($areaAddress[1]) . ' ' . $this->cityMod->getAreaName($areaAddress[2]);
        } elseif (count($areaAddress) == 2) {
            $country = $this->countryMod->getCountryName($areaAddress[0]);
            $zone = $this->zoneMod->getZoneName($areaAddress[1]);
            $result = $country . ' ' . $zone;
        }
        return $result;
    }






    /**
     * 配送属性找商品
     * @author tangp
     * @date 2019-03-06
     */
    public function getAttributeGoods()
    {
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : '';
        $rtid     = !empty($_REQUEST['rtid']) ? $_REQUEST['rtid'] : '';
        $lang_id  = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : '';
        $userId   = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';
        $type     = !empty($_REQUEST['type']) ? $_REQUEST['type'] : '';
        if (empty($store_id)){
            $this->setData(array(),0,'请传递店铺id！');
        }
        if (empty($rtid)){
            $this->setData(array(),0,'请传递业务id！');
        }
        if (empty($lang_id)){
            $this->setData(array(),0,'请传递语言id！');
        }
        if (empty($userId)){
            $this->setData(array(),0,'请传递用户id！');
        }
        if (empty($type)){
            $this->setData(array(),0,'请传递配送属性数值！');
        }
        $res = &m('storeGoods')->getGoods($store_id,$rtid,$lang_id,$userId,$type);

        $this->setData($res,1,'');
    }

    /**
     * 确认下单按钮操作
     * @author wanyan
     * @date 2017-10-23
     */
    public function comfirm() {
        $cartMod=&m('cart');
        $storeMod=&m('store');
        $userAddressMod=&m('userAddress');
        $couponMod=&m('coupon');
        $userCouponMod=&m('userCoupon');
        $userMod = &m('user');
        $orderMod=&m('order');
        $orderDetailMod=&m('orderDetail');
        $cart_ids = !empty($_REQUEST['cart_ids']) ? htmlspecialchars($_REQUEST['cart_ids']) : ''; //购物车id
        $seller_msg = !empty($_REQUEST['seller_msg']) ? htmlspecialchars($_REQUEST['seller_msg']) : ''; //留言
        $addressId = !empty($_REQUEST['addressId']) ? htmlspecialchars($_REQUEST['addressId']) : ''; //地址id
        $fxPhone = !empty($_REQUEST['fxPhone']) ? $_REQUEST['fxPhone'] : ''; //分销code
        $storeid = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : ''; //店铺
        $lang = !empty($_REQUEST['lang']) ? intval($_REQUEST['lang']) : $this->langid; //语言
        $discount_rate = !empty($_REQUEST['discount_rate']) ? intval($_REQUEST['discount_rate']) : '';//分销抵扣比例
        $point = !empty($_REQUEST['point']) ? $_REQUEST['point'] : ''; //睿积分数值
        $price = !empty($_REQUEST['price']) ? $_REQUEST['price'] : '';//睿积分抵扣金额
        $sendout = !empty($_REQUEST['sendout']) ? $_REQUEST['sendout'] : ''; //配送方式 数组形式 商品id-配送方式
        $shippingfee = !empty($_REQUEST['shippingfee']) ? htmlspecialchars($_REQUEST['shippingfee']) : 0; //邮费
        $fx_user_id = !empty($_REQUEST['fx_user_id']) ? intval($_REQUEST['fx_user_id']) : ''; //分销用户id
        $rule_id = !empty($_REQUEST['rule_id']) ? intval($_REQUEST['rule_id']) : ''; //分销规则id
        $daifu=!empty($_REQUEST['daifu']) ?$_REQUEST['daifu']:''; //是否代付
        $couponId = !empty($_REQUEST['couponId']) ? $_REQUEST['couponId'] : 0;//优惠劵Id
        $userCouponId=!empty($_REQUEST['userCouponId']) ? $_REQUEST['userCouponId']:0;//用户优惠劵Id
        $discount_price=!empty($_REQUEST['discount_price']) ? $_REQUEST['discount_price'] : 0;//优惠劵优惠金额
        //配送方式数组处理
//        $sendoutArr =explode(',',$sendout);
//        foreach($sendoutArr as $key=>$val){
//            $shippingMethodArr = explode('-',$val);
//            $shippingMethod[] =$shippingMethodArr[1];
//        }
//        $uniqueShippingMethod =array_unique($shippingMethod);
//        if(count($uniqueShippingMethod)==1){ //判断是否是同一配送方式
//            $sendoutStr=$uniqueShippingMethod[0];
//        }else{
//            $sendoutStr=$sendout;
//        }
        //生成小票编号
        $number_order = $this->createNumberOrder($storeid);
        if(!empty($cart_ids)){ //购物车商品信息
            //订单信息
            //订单号生成
            $rand = $this->buildNo(1);
            $orderNo = date('YmdHis') . $rand[0];
            $sql = "select c.user_id,c.store_id,c.shipping_store_id,SUM(c.goods_price*goods_num) as goods_amount,u.email,u.username from " .
                DB_PREFIX . "cart as c  LEFT JOIN " . DB_PREFIX . "user as u ON c.user_id = u.id  where c.`id` in  ({$cart_ids}) ";
            $orderInfo = $cartMod->querySql($sql);
            //获取购物车信息
            $goodsInfo = $cartMod->getGoodByCartId($cart_ids);
            //店铺名称
            $orderInfo[0]['store_name'] = $storeMod->getNameById($orderInfo[0]['shipping_store_id'],$lang);
            $storeName = $orderInfo[0]['store_name']; //店铺名称
            $goodsAmount = $orderInfo[0]['goods_amount'] ; //订单商品金额
            $storeId = $orderInfo[0]['shipping_store_id'] ;  //店铺id
            $buyerId=$orderInfo[0]['user_id']; //购买者
            $discount_p = 0;
        }else{  //秒杀商品信息
            //模型
            $spikeActiviesGoodsMod=&m('spikeActiviesGoods');
            $promotionGoodsMod=&m('goodPromDetail');
            $activityInfo = !empty($_REQUEST['activityInfo']) ? $_REQUEST['activityInfo'] : '' ; //活动信息 促销或者秒杀
            $activityInfo=json_decode(base64_decode($activityInfo), true);
            $storeId = $activityInfo['storeId'] ;  //店铺id
            $langId = $activityInfo['langId'] ; //语言id
            $storeName=$storeMod->getNameById($storeId,$langId); //店铺名称
            $goodsNum =$activityInfo['goodsNum']; //商品数量
            $goodsAmount = $activityInfo['goodsPrice'] * $goodsNum; //订单商品金额  10 * 2 = 20   8 * 2 = 16
            $discount_p = $goodsAmount - $activityInfo['discountPrice'] * $goodsNum;
            $buyerId=$this->userId; //购买者
            $source =$activityInfo['source']; //活动来源 1秒杀 2促销
            $activityGoodsId = $activityInfo['activityGoodsId']; //活动商品表id
            $activityId = $activityInfo['activityId']; //活动id
            $orderNo = $activityInfo['orderSn'];
            $orderInfo= $orderMod ->getOne(array('cond'=>"`order_sn` = '{$orderNo}'"));
            if(!empty($orderInfo)){
                $this->setData('',0,'订单已生成,快去付款吧');
            }
            if($source==1){
                $activityGoodsData=$spikeActiviesGoodsMod->getOne(array('cond'=>"`id`= '{$activityGoodsId}' AND mark = 1 ",'fields'=>'store_goods_id,discount_price,goods_img,goods_name,goods_key_name,goods_key,goods_num,limit_num'));
            }
            //促销活动
            if($source==2){
                $activityGoodsData=$promotionGoodsMod->getOne(array('cond'=>"`id`= '{$activityGoodsId}' ",'fields'=>'goods_id,goods_img,goods_name,limit_amount'));
            }
            $goodsInfo=array(
                array(
                    'goods_id'=>$activityInfo['storeGoodsId'],
                    'goods_name'=>$activityGoodsData['goods_name'],
                    'store_id' =>$storeId,
                    'spec_key'=>$activityInfo['goodsKey'],
                    'goods_num'=>$activityInfo['goodsNum'],
                    'spec_key_name'=>$activityInfo['goodsKeyName'],
                    'user_id'=>$buyerId,
                    'fx_code'=>'',
                    'discount_price'=>$activityInfo['discountPrice'],
                    'shipping_price'=>0,
                    'prom_id' =>$activityId,
                    'prom_type' =>$source,
                    'goods_price'=>$activityInfo['goodsPrice']
                )
            );
        }
        //商品库存判断
        foreach($goodsInfo as $k=>$v){
            $invalid=$cartMod->isInvalid($v['goods_id'],$v['spec_key']);
            if($invalid<$v['goods_num']){
                $this->setData(array(),'0',$v['goods_name'].'商品库存不足');
            }
        }
        //获取用户地址信息
        $user_address = $userAddressMod->getAddress($addressId);
        //分销优惠金额计算
        if (!empty($fxPhone)) {
            $fxuserMod      = &m('fxuser');
            $fxuserInfo     = $fxuserMod->getOne(array('cond' => "fx_code = '{$fxPhone}' AND mark = 1"));
            $discount = (($goodsAmount - $price-$discount_price-$discount_p) * $fxuserInfo['discount'] * 0.01);
        } else {
            $discount = 0;
        }
        //是否使用了优惠劵
        if(!empty($couponId)){
            $order_amount = $goodsAmount + $shippingfee - $discount - $price-$discount_price;
        }else{
            $order_amount = $goodsAmount + $shippingfee - $discount - $price - $discount_p;
        }
        if ($order_amount <= 0) {
            $order_amount = 0;
        }
        //收货信息
        $count = strpos($user_address['address'], "_");
        if($count==false){
            $addressStr=$user_address['address'];
        }else{
            $addressStr = substr_replace($user_address['address'], "", $count, 1);
        }
        $userData=$userMod->getOne(array('cond'=>"`id` = '{$this->userId}' and mark=1",'fields'=>'phone'));
        if (empty($user_address)) {
            $user_address['phone'] = $userData['phone'];
            $user_address['name'] = $userData['phone'];
        }
        // 主订单数据
        $insert_main_data = array(
            'order_sn' => $orderNo,
            'store_id' => $storeId,
            'sendout' => $sendout,
            'store_name' => $storeName,
            'buyer_id' => $buyerId,
            'buyer_name' => addslashes($user_address['name']),
            'buyer_email' => '',
            'goods_amount' => $goodsAmount,
            'order_amount' => $order_amount,
            'shipping_fee' => empty($activityInfo) ? $shippingfee : 0,
            'order_state' => 10,
            'order_from' => 2,
            'buyer_address' => $addressStr,
            'buyer_phone' => $user_address['phone'],
            'discount' => $discount,
            'fx_discount_rate' => $discount_rate,
            'fx_phone' => $fxPhone,
            'add_time' => time(),
            'number_order' => $number_order, //生成小票编号
            'seller_msg' => $seller_msg, //订单的留言
            'sub_user' => 2,
            'is_source'=>3,
            'fx_user_id'=>$fx_user_id,
        );
        //优惠劵
        if(!empty($couponId)){
            $insert_main_data['cid']=$couponId;
            $insert_main_data['cp_amount']=$discount_price;
        }
        try {
            //事务开始
            $orderMod->begin();
            //原来生成订单数据
            $main_rs = $orderMod->doInsert($insert_main_data);
            //生成新的订单表数据
            $insert_main_data['cp_amount']=empty($activityInfo) ? $discount_price : 0;
            $insert_main_data['pd_amount']=$price;
            $insert_main_data['fx_money']=$discount;
            $createOrderRes = $orderMod->createOrder($insert_main_data,1);
            if (empty($main_rs) || empty($createOrderRes)) {
                //事务回滚
                $orderMod->rollback();
                $this->setData(array(), 0, '提交订单失败');
            } else {
                //事务提交
                $orderMod->commit();
            }
        } catch (Exception $e) {
            //事务回滚
            $orderMod->rollback();
            writeLog($e->getMessage());
            $this->setData(array(), 0, '提交订单失败');
        }
        if(!empty($couponId)){
            //用户使用优惠劵记录
            $couponLogMod=&m('couponLog');
            $couponLogData=array(
                'user_coupon_id'=>$userCouponId,
                'coupon_id'=>$couponId,
                'user_id'=>$this->userId,
                'order_id'=>$main_rs,
                'order_sn'=>$orderNo,  // by xt 2019.03.21
                'add_time'=>time()
            );
            $couponLogMod->doInsert($couponLogData);
        }
        //生成2维码
  /*      $code = $this->goodsZcode($storeid, $lang, $main_rs);
        $cond['order_url'] = $code;
        $urldata = array(
            "table" => "order",
            'cond' => 'order_id = ' . $main_rs,
            'set' => "order_url='" . $code . "'",
        );
        $orderMod->doUpdate($urldata);*/
        // 先插入子订单
        if ($main_rs) {
            foreach ($goodsInfo as $k => $v) {
                if(!empty($activityInfo)){
                    $goodsPayPrice = $v['discount_price'] ;
                    $goodsPrice = $v['goods_price'];
                    $insert_sub_data['prom_id']=$v['prom_id'];
                    $insert_sub_data['prom_type']=$v['prom_type'];
                }else{
                    $goodsPayPrice =$orderMod->getGoodsPayPrice($v['store_id'],$v['goods_id'],$v['spec_key']);
                    $goodsPrice = $orderMod->getPrice($v['store_id'],$v['goods_id'],$v['spec_key']);
                }
                $insert_sub_data = array(
                    'order_id' => $orderNo,
                    'goods_id' => $v['goods_id'],
                    'goods_name' => addslashes(stripslashes($v['goods_name'])),
                    'goods_price' => $goodsPrice,
                    'goods_num' => $v['goods_num'],
                    'goods_image' => $this->getGoodImg($v['goods_id'], $v['store_id']),
                    'goods_pay_price'=>$goodsPayPrice,
                    'spec_key_name' => $v['spec_key_name'],
                    'spec_key' => $v['spec_key'],
                    'store_id' => $v['store_id'],
                    'buyer_id' => $v['user_id'],
                    'goods_type' => 0,
                    'order_state' => 10,
                    'fx_code' => $v['fx_code'],
                    'discount' => ($v['goods_price'] + $shippingfee) * ($fxuserInfo['discount']) * 0.01,
                    'discount_rate' => $fxuserInfo['discount'],
                    'shipping_price' => $v['shipping_price'],
                    'shipping_store_id' => $v['shipping_store_id'],
                    'add_time' => time(),
                    'good_id'=>$this->getGoodId($v['goods_id']),
                    'deduction'=>$this->getDeduction($v['goods_id'])
                );
                if(!empty($activityInfo)){
                    $insert_sub_data['prom_id']=$v['prom_id'];
                    $insert_sub_data['prom_type']=$v['prom_type'];
                }
                $rs[] = $orderDetailMod->doInsert($insert_sub_data);
            }
            $rs = array_filter($rs);
            $store_cate=$this->getStoreCate($goodsInfo[0]['store_id']);//站点国家
            $store_id = $goodsInfo[0]['store_id'];//选取的购物车商品的区域商品id
            if (count($rs)) {
                if ($this->delCart($cart_ids)) {
                    //添加积分优惠
                    if ($price && $price!='0.00') {
                        $this->getPointPrice($orderNo, $price, $point);
                    }
                    $info = array(
                        'orderNo'=>$orderNo,
                        'orderId'=>$main_rs,
                        'storeId'=>$storeId
                    );
                    //分单
                    $orderMod=&m('order');
                    $orderMod->separateOrder($orderNo,2,1);
                    $this->setData($info, $status = 1, '提交订单成功,前往支付');
                } else {
                    $this->setData($info = array(), $status = 0, '提交订单失败');
                }
            }
        }
    }
 

   /**
     * 确认下单按钮操作
     * @author wanyan
     * @date 2017-10-23
     */
    public function comfirm1() {
        $cartMod=&m('cart');
        $storeMod=&m('store');
        $userAddressMod=&m('userAddress');
        $userMod = &m('user');
        $orderMod=&m('order');
        $orderDetailMod=&m('orderDetail');
        $cart_ids = !empty($_REQUEST['cart_ids']) ? htmlspecialchars($_REQUEST['cart_ids']) : ''; //购物车id
        $seller_msg = !empty($_REQUEST['seller_msg']) ? htmlspecialchars($_REQUEST['seller_msg']) : ''; //留言
        $addressId = !empty($_REQUEST['addressId']) ? htmlspecialchars($_REQUEST['addressId']) : 0; //地址id
        $fxPhone = !empty($_REQUEST['fxPhone']) ? $_REQUEST['fxPhone'] : ''; //分销code
        $storeid = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : ''; //店铺
        $lang = !empty($_REQUEST['lang']) ? intval($_REQUEST['lang']) : $this->langid; //语言
        $discount_rate = !empty($_REQUEST['discount_rate']) ? intval($_REQUEST['discount_rate']) : '';//分销抵扣比例
        $point = !empty($_REQUEST['point']) ? $_REQUEST['point'] : ''; //睿积分数值
        $price = !empty($_REQUEST['price']) ? $_REQUEST['price'] : '';//睿积分抵扣金额
        $sendout = !empty($_REQUEST['sendout']) ? $_REQUEST['sendout'] : 1; //配送方式 数组形式 商品id-配送方式
        $pei_time = !empty($_REQUEST['pei_time']) ? strtotime(date('Y-m-d ').$_REQUEST['pei_time']) : '';
        $shippingfee = !empty($_REQUEST['shippingfee']) ? htmlspecialchars($_REQUEST['shippingfee']) : 0; //邮费
        $fx_user_id = !empty($_REQUEST['fx_user_id']) ? intval($_REQUEST['fx_user_id']) : ''; //分销用户id
        $couponId = !empty($_REQUEST['couponId']) ? $_REQUEST['couponId'] : 0;//优惠劵Id
        $userCouponId=!empty($_REQUEST['userCouponId']) ? $_REQUEST['userCouponId']:0;//用户优惠劵Id
        $discount_price=!empty($_REQUEST['discount_price']) ? $_REQUEST['discount_price'] : 0;//优惠劵优惠金额
        $table_type = !empty($_REQUEST['table_type']) ? $_REQUEST['table_type'] : 0;//桌号类型
        $table_number = !empty($_REQUEST['table_number']) ? $_REQUEST['table_number'] : 0;//桌号人数
        $table_num = !empty($_REQUEST['table_num']) ? $_REQUEST['table_num'] : 0;//桌号

        //生成小票编号
        $number_order = $this->createNumberOrder($storeid);
        if(!empty($cart_ids)){ //购物车商品信息
            //订单信息
            //订单号生成
            $rand = $this->buildNo(1);
            $orderNo = date('YmdHis') . $rand[0];
            $sql = "select c.user_id,c.store_id,c.shipping_store_id,SUM(c.goods_price*goods_num) as goods_amount,u.email,u.username from " .
                DB_PREFIX . "cart as c  LEFT JOIN " . DB_PREFIX . "user as u ON c.user_id = u.id  where c.`id` in  ({$cart_ids}) ";
            $orderInfo = $cartMod->querySql($sql);
            //获取购物车信息
            $goodsInfo = $cartMod->getGoodByCartId($cart_ids);
            //店铺名称
            $orderInfo[0]['store_name'] = $storeMod->getNameById($orderInfo[0]['shipping_store_id'],$lang);
            $storeName = $orderInfo[0]['store_name']; //店铺名称
            $goodsAmount = $orderInfo[0]['goods_amount'] ; //订单商品金额
            $storeId = $orderInfo[0]['shipping_store_id'] ;  //店铺id
            $buyerId=$orderInfo[0]['user_id']; //购买者
        }

        //商品库存判断
        foreach($goodsInfo as $k=>$v){
            $invalid=$cartMod->isInvalid($v['goods_id'],$v['spec_key']);
            if($invalid<$v['goods_num']){
                $this->setData(array(),'0',$v['goods_name'].'商品库存不足');
            }
        }
        //获取用户地址信息
        $user_address = $userAddressMod->getAddress($addressId);
        $fxuserInfo = array();
        //分销优惠金额计算
        if (!empty($fxPhone)) {
            $fxuserMod      = &m('fxuser');
            $fxuserInfo     = $fxuserMod->getOne(array('cond' => "fx_code = '{$fxPhone}' AND mark = 1"));
            $discount = (($goodsAmount - $price-$discount_price) * $fxuserInfo['discount'] * 0.01);
        } else {
            $discount = 0;
        }
        $couponType = 0;
        $couponMod = &m('coupon'); //优惠劵模型
        //是否使用了优惠劵
        if(!empty($couponId)){
            $couponData = $couponMod->getOne(array('cond' => "`id` = '{$couponId}'", 'fields' => "type")); //1代表 抵扣劵 2是兑换券
            $couponType = $couponData['type']; //优惠劵类型  1代表 抵扣劵 2是兑换券
            $cmod = &m('userCoupon');
            $userCoupon = $cmod->getOne(array('cond' => "`id` = '{$userCouponId}' AND `user_id` = '{$buyerId}' AND `c_id` = '{$couponId}'"));
            $order_amount = $goodsAmount + $shippingfee - $discount - $price-$discount_price;
                $lmod = &m('errorlog');
                $lmod->doInsert(array('request_params'=>$orderNo,'deal_params'=>$couponId,'user_id'=>$buyerId,'add_time'=>time()));
        }else{
            $order_amount = $goodsAmount + $shippingfee - $discount - $price;
        }
        if ($order_amount <= 0) {
            $order_amount = 0.01;
        }

        if(!empty($couponId) && !empty($userCoupon) &&  $couponType == 2){
            $order_amount = $shippingfee;
            $discount = 0;//分销价格
            $fxuserInfo = array();
            $lmod = &m('errorlog');
            $lmod->doInsert(array('request_params'=>$orderNo,'deal_params'=>$couponId,'important_params'=>$couponType,'user_id'=>$buyerId,'add_time'=>time()));
        }
        $table_desc = '';
        if($table_type == 1){
            $table_desc = serialize(array(
                'table_num' => $table_num,
                'table_number' => $table_number
            ));
        }

        //收货信息
//        $count = strpos($user_address['address'], "_");
//        if($count==false){
//            $addressStr=$user_address['address'];
//        }else{
//            $addressStr = substr_replace($user_address['address'], "", $count, 1);
//        }
//        $userData=$userMod->getOne(array('cond'=>"`id` = '{$this->userId}' and mark=1",'fields'=>'phone'));
//        if (empty($user_address)) {
//            $user_address['phone'] = $userData['phone'];
//            $user_address['name'] = $userData['phone'];
//        }
        // 主订单数据
        $insert_main_data = array(
            'order_sn' => $orderNo,
            'store_id' => $storeId,
            'sendout' => $sendout,
            'store_name' => $storeName,
            'buyer_id' => $buyerId,
//            'buyer_name' => addslashes($user_address['name']),
            'buyer_email' => '',
            'goods_amount' => $goodsAmount,
            'order_amount' => $order_amount,
            'shipping_fee' => $shippingfee,
            'order_state' => 10,
            'order_from' => 2,
//            'buyer_address' => $addressStr,
//            'buyer_phone' => $user_address['phone'],
            'discount' => $discount,//分销
            'fx_discount_rate' => $discount_rate,
            'fx_phone' => $fxPhone,
            'add_time' => time(),
//            'number_order' => $number_order, //生成小票编号
            'seller_msg' => $seller_msg, //订单的留言
            'sub_user' => 2,
            'is_source'=>3,
            'fx_user_id'=> $fxuserInfo['id'] ? $fxuserInfo['id'] : 0,
        );
        //优惠劵
        if(!empty($couponId)){
            $insert_main_data['cid']=$couponId;
            $insert_main_data['cp_amount']=$discount_price;
        }
        try {
            //事务开始
            $orderMod->begin();
            //原来生成订单数据
            $main_rs = $orderMod->doInsert($insert_main_data);
            //生成新的订单表数据
            $insert_main_data['cp_amount']=$discount_price;
            $insert_main_data['pd_amount']=$price;
            $insert_main_data['fx_money']=$discount;
            $insert_main_data['pei_time']=$pei_time;
            $insert_main_data['address_id']=$addressId;
            $insert_main_data['delivery'] = $user_address;
            $insert_main_data['table_type'] = $table_type;
            $insert_main_data['table_desc'] = $table_desc;
            $createOrderRes = $orderMod->createOrder($insert_main_data,1);
            if (empty($main_rs) || empty($createOrderRes)) {
                //事务回滚
                $orderMod->rollback();
                $this->setData(array(), 0, '提交订单失败');
            } else {
                //事务提交
                $orderMod->commit();
            }
        } catch (Exception $e) {
            //事务回滚
            $orderMod->rollback();
            writeLog($e->getMessage());
            $this->setData(array(), 0, '提交订单失败');
        }
        if(!empty($couponId)){
            //用户使用优惠劵记录
            $couponLogMod=&m('couponLog');
            $couponLogData=array(
                'user_coupon_id'=>$userCouponId,
                'coupon_id'=>$couponId,
                'user_id'=>$this->userId,
                'order_id'=>$main_rs,
                'order_sn'=>$orderNo,  // by xt 2019.03.21
                'add_time'=>time()
            );
            $couponLogMod->doInsert($couponLogData);
        }

        //免费兑换修改支付状态
//        if(!empty($couponId) && !empty($userCoupon) && $couponType == 2){
//            $datas = array(
//                'pay_sn' => '免费兑换',
//                'payment_code' => '免费兑换',
//                'payment_time' => time(),
//                'order_state' => 20, //已付款状态
//                'is_old' => 1
//            ); //区域配送安装完成时间
//            $conds = array(
//                'order_sn' => $orderNo
//            );
//            $lmod = &m('errorlog');
//            $lmod->doInsert(array('request_params'=>$orderNo,'deal_params'=>$couponId,'important_params'=>$couponType,'user_id'=>$buyerId,'add_time'=>time()));
//            $res = $orderMod->doEditSpec($conds, $datas);
//            $orderMod->update_pay_time($storeid, $orderNo, '免费兑换',5);
//            $this->updateStock($orderNo);
//        }

        // 先插入子订单
        if ($main_rs) {
            foreach ($goodsInfo as $k => $v) {
                if(!empty($activityInfo)){
                    $goodsPayPrice = $v['discount_price'] ;
                    $goodsPrice = $v['goods_price'];
                    $insert_sub_data['prom_id']=$v['prom_id'];
                    $insert_sub_data['prom_type']=$v['prom_type'];
                }else{
                    $goodsPayPrice =$orderMod->getGoodsPayPrice($v['store_id'],$v['goods_id'],$v['spec_key']);
                    $goodsPrice = $orderMod->getPrice($v['store_id'],$v['goods_id'],$v['spec_key']);
                }
                $insert_sub_data = array(
                    'order_id' => $orderNo,
                    'goods_id' => $v['goods_id'],
                    'goods_name' => addslashes(stripslashes($v['goods_name'])),
                    'goods_price' => $goodsPrice,
                    'goods_num' => $v['goods_num'],
                    'goods_image' => $this->getGoodImg($v['goods_id'], $v['store_id']),
                    'goods_pay_price'=>$goodsPayPrice,
                    'spec_key_name' => $v['spec_key_name'],
                    'spec_key' => $v['spec_key'],
                    'store_id' => $v['store_id'],
                    'buyer_id' => $v['user_id'],
                    'goods_type' => 0,
                    'order_state' => 10,
                    'fx_code' => $v['fx_code'],
                    'discount' => ($v['goods_price'] + $shippingfee) * ($fxuserInfo['discount']) * 0.01,
                    'discount_rate' => $fxuserInfo['discount'],
                    'shipping_price' => $v['shipping_price'],
                    'shipping_store_id' => $v['shipping_store_id'],
                    'add_time' => time(),
                    'good_id'=>$this->getGoodId($v['goods_id']),
                    'deduction'=>$this->getDeduction($v['goods_id'])
                );
                if(!empty($activityInfo)){
                    $insert_sub_data['prom_id']=$v['prom_id'];
                    $insert_sub_data['prom_type']=$v['prom_type'];
                }
                $rs[] = $orderDetailMod->doInsert($insert_sub_data);
            }
            $rs = array_filter($rs);
            if (count($rs)) {



                if ($this->delCart($cart_ids)) {
                    //添加积分优惠
                    if ($price && $price!='0.00') {
                        $this->getPointPrice($orderNo, $price, $point);
                    }
                    $info = array(
                        'orderNo'=>$orderNo,
                        'orderId'=>$main_rs,
                        'storeId'=>$storeId
                    );
                    //分单
                    $orderMod=&m('order');
                    $orderMod->separateOrder($orderNo,2,1);
                    $this->setData($info, $status = 1, '提交订单成功,请前往支付');
                } else {
                    $this->setData($info = array(), $status = 0, '提交订单失败');
                }
            }
        }
    }





    // 更新规格库存 和 无规格库存
    public function UpdateStock($out_trade_no){
        //  更新库存

        $sql = "SELECT sg.goods_id,sg.spec_key,sg.goods_num,sg.deduction,sg.good_id FROM ".
            DB_PREFIX."order as r LEFT JOIN ".
            DB_PREFIX."order_goods as sg ON  r.order_sn = sg.order_id WHERE sg.order_id = '{$out_trade_no}'";
        $orderRes = $this->areaGoodMod->querySql($sql);
        foreach ($orderRes as $k =>$v) {
            if (!empty($v['spec_key'])) {
                if($v['deduction']==1){
                    $query_id = "select `id` from " . DB_PREFIX . "store_goods_spec_price where `goods_id` = '{$v['good_id']}' and `key` = '{$v['spec_key']}' ";
                    $res_query = $this->storeGoodItemPriceMod->querySql($query_id);
                    $specInfoSql = "select goods_storage from  " . DB_PREFIX . "store_goods_spec_price WHERE `goods_id` = '{$v['good_id']}' and `key` = '{$v['spec_key']}'";
                    $specInfo = $this->areaGoodMod->querySql($specInfoSql);
                    foreach($res_query as $key=>$val){
                        $condition = array(
                            'goods_storage' => $specInfo[0]['goods_storage'] - $v['goods_num']
                        );
                        $res = $this->storeGoodItemPriceMod->doEdit($val['id'], $condition);
                    }
                    if ($res) {
                        $infoSql = "select goods_storage,id from  " . DB_PREFIX . "store_goods WHERE `goods_id` = '{$v['good_id']}'";
                        $Info = $this->areaGoodMod->querySql($infoSql);
                        $cond = array(
                            'goods_storage' => $Info[0]['goods_storage'] - $v['goods_num']
                        );
                        foreach($Info as $key1=>$val1 ){
                            $this->areaGoodMod->doEdit($val1['id'], $cond);
                        }
                    }
                    $Sql = "select goods_storage from  " . DB_PREFIX . "goods_spec_price WHERE `goods_id` = '{$v['good_id']}' and `key` = '{$v['spec_key']}'";

                    $goodsSpec = $this->areaGoodMod->querySql($Sql);
                    $conditional=array(
                        'goods_storage'=>$goodsSpec[0]['goods_storage']-$v['goods_num']
                    );
                    $goodsSpecSql="update ".DB_PREFIX."goods_spec_price set goods_storage = ".$conditional['goods_storage']." where goods_id=".$v['good_id']." and `key` ='{$v['spec_key']}'" ;
                    $result=$this->goodsSpecPriceMod->doEditSql($goodsSpecSql);
                    if($result){
                        $goodSql="select goods_storage from  " . DB_PREFIX . "goods WHERE `goods_id` = '{$v['good_id']}'";

                        $goodInfo = $this->areaGoodMod->querySql($goodSql);
                        $goodCond = array(
                            'goods_storage' => $goodInfo[0]['goods_storage'] - $v['goods_num']
                        );
                        $this->goodsMod->doEdit($v['good_id'],$goodCond);
                    }
                }else{
                    $query_id = "select `id` from " . DB_PREFIX . "store_goods_spec_price where `store_goods_id` = '{$v['goods_id']}' and `key` = '{$v['spec_key']}' ";
                    $res_query = $this->storeGoodItemPriceMod->querySql($query_id);
                    $specInfoSql = "select goods_storage from  " . DB_PREFIX . "store_goods_spec_price WHERE `store_goods_id` = '{$v['goods_id']}' and `key` = '{$v['spec_key']}'";
                    $specInfo = $this->areaGoodMod->querySql($specInfoSql);
                    $condition = array(
                        'goods_storage' => $specInfo[0]['goods_storage'] - $v['goods_num']
                    );
                    $res = $this->storeGoodItemPriceMod->doEdit($res_query[0]['id'], $condition);
                    if ($res) {
                        $infoSql = "select goods_storage from  " . DB_PREFIX . "store_goods WHERE `id` = '{$v['goods_id']}'";
                        $Info = $this->areaGoodMod->querySql($infoSql);
                        $cond = array(
                            'goods_storage' => $Info[0]['goods_storage'] - $v['goods_num']
                        );
                        $this->areaGoodMod->doEdit($v['goods_id'], $cond);
                    }
                }



            } else {
                if($v['deduction']==1){
                    $infoSql = "select goods_storage,id from  " . DB_PREFIX . "store_goods WHERE `goods_id` = '{$v['good_id']}'";
                    $Info = $this->areaGoodMod->querySql($infoSql);

                    $cond = array(
                        'goods_storage' => $Info[0]['goods_storage'] - $v['goods_num']
                    );
                    foreach($Info as $key1=>$val1 ){
                        $this->areaGoodMod->doEdit($val1['id'], $cond);
                    }
                    $goodSql="select goods_storage from  " . DB_PREFIX . "goods WHERE `goods_id` = '{$v['good_id']}'";

                    $goodInfo = $this->areaGoodMod->querySql($goodSql);
                    $goodCond = array(
                        'goods_storage' => $goodInfo[0]['goods_storage'] - $v['goods_num']
                    );
                    $this->goodsMod->doEdit($v['good_id'],$goodCond);
                }else{
                    $specInfoSql = "select goods_storage from  " . DB_PREFIX . "store_goods WHERE `id` = '{$v['goods_id']}'";
                    $specInfo = $this->areaGoodMod->querySql($specInfoSql);
                    $condition = array(
                        'goods_storage' => $specInfo[0]['goods_storage'] - $v['goods_num']
                    );
                    $this->areaGoodMod->doEdit($v['goods_id'],$condition);
                }

            }
        }
    }




    /**
     * 检测Cartid 是否存在
     * @author wanyan
     * @date 2018-01-11
     */
    public function getCartId() {
        //语言包
        $this->load($this->shorthand, 'WeChat/goods');
        $a = $this->langData;
        $cartMod = &m('cart');
        $cart_id = !empty($_REQUEST['cart_ids']) ? htmlspecialchars($_REQUEST['cart_ids']) : '';
        $sql = "select `id` from " . DB_PREFIX . "cart where `id` = '{$cart_id}'";
        $info = $cartMod->querySql($sql);
        if ($info[0]['id']) {
            $this->setData($info = array(), $status = 1, $message = '');
        } else {
            $this->setData($info = array(), $status = 0, $a['order_Havebeen_submitted']);
        }
    }
    //生成二维码
    public function goodsZcode($storeid, $lang, $order_id) {
        include ROOT_PATH . "/includes/classes/class.qrcode.php"; // 生成二维码库
        $mainPath = ROOT_PATH . '/upload/orderCode';
        $this->mkDir($mainPath);
        $timePath = date('Ymd');
        $savePath = $mainPath . '/' . $timePath;
        $this->mkDir($savePath);
        $newFileName = uniqid() . ".png";
        $filename = $savePath . '/' . $newFileName;
        $pathName = 'upload/orderCode/' . $timePath . '/' . $newFileName;
        $http_host = $_SERVER['HTTP_HOST'];
        $system_web = 'www.711home.net';
        $valueUrl = 'http://' . $system_web . "/wx.php?app=order&act=order_details&orderid={$order_id}";
        QRcode::png($valueUrl, $filename);
        return $pathName;
    }
    public function mkDir($dir) {
        if (!is_dir($dir)) {
            @mkdir($dir);
            @chmod($dir, 0777);
            @exec('chmod -R 777 {$dir}');
        }
    }
    //获取站点国家
    public function getStoreCate($storeId){
        $sql="select store_cate_id from ".DB_PREFIX.'store where id='.$storeId;
        $storeMod=&m('store');
        $storeInfo=$storeMod->querySql($sql);
        return $storeInfo[0]['store_cate_id'];
    }

    //获取原始商品id
    function  getGoodId($id){
        $orderDetailMod=&m('orderDetail');
        $sql="select goods_id from ".DB_PREFIX.'store_goods where id='.$id;
        $goodInfo=$orderDetailMod->querySql($sql);
        return $goodInfo[0]['goods_id'];

    }
    //获取商品扣除方式
    function getDeduction($id){
        $orderDetailMod=&m('orderDetail');
        $sql="select deduction from ".DB_PREFIX.'store_goods where id='.$id;
        $goodInfo=$orderDetailMod->querySql($sql);
        return $goodInfo[0]['deduction'];
    }
    /**
     * 删除下单完成后删除购物车中数据
     * @author wanyan
     * @date 2017-11-9
     */
    public function delCart($cart_ids) {
        $cartMod=&m('cart');
        $query = array(
            'cond' => " `id` in ({$cart_ids})"
        );
        $rs = $cartMod->doDelete($query);
        if ($rs) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * 获取当前商品图片
     * @author wanyan
     * @date 2017-10-20
     */
    public function getGoodImg($goods_id) {
        $storeMod=&m('store');
        $sql = 'select is_joint, original_img  from  '
            . DB_PREFIX . 'store_goods '
            . ' where id  = ' . $goods_id;
        $rs = $storeMod->querySql($sql);
        if($rs[0]['is_joint'] == 1){
            return 'web/uploads/small/'.$rs[0]['original_img'];
        }
        return $rs[0]['original_img'];
    }
    /**
     * 生成小票编号
     * @author: luffy
     * @date: 2018-08-09
     */
    public function createNumberOrder($storeid) {
        //获取当天开始结束时间
        $orderMod=&m('order');
        $startDay = strtotime(date('Y-m-d'));
        $endDay = strtotime(date('Y-m-d 23:59:59'));
        $sql = 'select order_sn,number_order from  '
            . DB_PREFIX . 'order where add_time BETWEEN ' . $startDay . ' AND ' . $endDay
            . ' AND mark = 1 and store_id = ' . $storeid . ' order by add_time DESC limit 1';
        $res =$orderMod->querySql($sql);
        //不管订单存在与否直接加
        $number_order = (int) $res[0]['number_order'] + 1;
        $number_order = str_pad($number_order, 4, 0, STR_PAD_LEFT);
        return $number_order;
    }
    /**
     * 生成不重复的四位随机数
     * @author wanyan
     * @date 2017-10-23
     */
    public function buildNo($limit) {
        $begin = pow(10, 3);
        $end = (pow(10, 4) - 1);
        $rand_array = range($begin, $end);
        shuffle($rand_array); //调用现成的数组随机排列函数
        return array_slice($rand_array, 0, $limit); //截取前$limit个
    }



    /*
    * 积分兑换优惠处理
    * @auhtor lee
    * @date 2018-5-7 15:35:33
    */
    public function getPointPrice($order_id, $price, $point) {
        $this->load($this->shorthand, 'userCenter/userCenter');
        $a = $this->langData;
        $orderMod = &m('order');
        $userMod = &m('user');
        $pointSiteMod = &m('point');
        $storePointMod = &m('storePoint');
        $storeMod = &m('store');
        $curMod = &m('currency');
        $user_id = $this->userId;
        $user_info = $userMod->getOne(array("cond" => "id=" . $user_id));
        $order_info = $orderMod->getOne(array("cond" => "order_sn ='{$order_id}'"));
        $store_id = $order_info['store_id'];
        //获取订单总金额
        $totalMoney = $order_info['order_amount']; //原订单价格
        //获取最大积分支付比例
        $point_price_site = $pointSiteMod->getOne(array("cond" => "1=1"));
        $store_point_site = $storePointMod->getOne(array("cond" => "store_id=" . $order_info['store_id']));
        $point_price_site['point_price'] = empty($store_point_site) ? 0 : $store_point_site['point_price'];
        if ($point_price_site) {
            $point_price = $point_price_site['point_price'] * $totalMoney / 100; //积分兑换最大金额
            $rmb_point = $point_price_site['point_rate']; //积分和RMB的比例
        } else {
            $point_price = 0;
            $rmb_point = 0;
        }
        //获取当前店铺币种以及兑换比例
        $store_info = $storeMod->getOne(array("cond" => "id=" . $store_id));
        //获取当前币种和RMB的比例
        $rate = $curMod->getCurrencyRate($store_info['currency_id']);
        //积分和RMB的比例
        if ($rate) {
            // $price_rmb = ceil(($point_price*$rate)/$rmb_point);
            //最大比例使用积分
            $price_rmb_point = ceil($point_price * $rate * $rmb_point);
        }
        $last_price = ($point / $point_price_site['point_rate']) / $rate;
        $order_price = $totalMoney - $last_price;
        $order_arr = array(
            'pd_amount' => $price,
        );
        $order_cond = array(
            'order_sn' => "{$order_id}"
        );
        $order_res = $orderMod->doEditSpec($order_cond, $order_arr);
        if ($order_res) {
            //扣除用户积分
            $user_point = $user_info['point'] - $point;
            $userMod->doEdit($user_id, array("point" => $user_point));
            //积分日志
            $logMessage = "订单：" . $order_id . " 使用：" . $point . "睿积分";
            $this->addPointLog($user_info['phone'], $logMessage, $user_id, 0, $point, $order_id);
            return $order_res;
        } else {
            $this->setData(array(), $status = 0, '使用睿积分失败');
        }
    }

    //生成日志
    public function addPointLog($username, $note, $userid, $deposit, $expend, $order_sn) {
        $logData = array(
            'operator' => '--',
            'username' => $username,
            'add_time' => time(),
            'deposit' => $deposit,
            'expend' => $expend,
            'note' => $note,
            'userid' => $userid,
            'order_sn' => $order_sn
        );
        $pointLogMod = &m("pointLog");
        $pointLogMod->doInsert($logData);
    }

    /**
    * 检测是否超过限购
    *
    */
    public function getBuyNums()
    {
        $activityGoodsId = $_REQUEST['activityGoodsId'];
        $num = $_REQUEST['num'];
        $id = $_REQUEST['id'];
        $activityId = $_REQUEST['activityId'];
        $userId = $_REQUEST['userId'];
        $source = $_REQUEST['source'];
        $spikeActiviesGoodsMod = &m('spikeActiviesGoods');
        $orderGoodsMod = &m('orderGoods');
        $storeGoodsMod = &m('storeGoods');
        $storeGoodsSpecPriceMod = &m('storeGoodsSpecPrice');
        $sql = "select * from bs_spike_goods where id=".$activityGoodsId;
        $res = $spikeActiviesGoodsMod->querySql($sql);
        //限购判断
        if ($num > $res[0]['limit_num']){
            $this->setData(array(),0,'你选择的数量大于该商品的限购数量！');
        }
//        $nums = $orderGoodsMod->getActivityOrderNum($source,$activityId,$id,$userId);
        // $sql4 = "select sum(goods_num) as total from bs_order_goods where prom_type={$source} and prom_id={$activityId} and goods_id={$id} and buyer_id={$userId} ";
        //     $ac = $orderGoodsMod->querySql($sql4);
        if (!empty($userId)){
            $sql4 = "select sum(goods_num) as total from bs_order_goods where prom_type={$source} and prom_id={$activityId} and goods_id={$id} and buyer_id={$userId} ";
            $ac = $orderGoodsMod->querySql($sql4);

            $nums = $ac[0]['total'] ?: 0;
            //购买限购
            if ($res[0]['limit_num'] - $nums < $num){
                $this->setData(array(),0,"限购{$res[0]['limit_num']}件");
            }
        }

        //查库存足不足
        if (empty($res[0]['goods_key'])  && empty($res[0]['goods_key_name'])){
            $sql2 = "SELECT goods_storage FROM bs_store_goods WHERE id=".$id;//无规格的查库存
            $goodsInfo = $storeGoodsMod->querySql($sql2);
            if ($goodsInfo[0]['goods_storage'] == 0){
                $this->setData(array(),0,'该商品的库存不足！');
            }
            if ($num > $goodsInfo[0]['goods_storage']){
                $this->setData(array(),0,'该商品的库存不足！');
            }
        }else{
//            $sql3 = "SELECT goods_storage FROM bs_store_goods_spec_price WHERE store_goods_id={$id} AND `key`='{$res[0]['goods_key']}'";//有规格查库存
            $goods_key = $res[0]['goods_key'];
            $info = $storeGoodsSpecPriceMod->getOne(array('cond' =>"store_goods_id = '{$id}' and `key` ='{$goods_key}'"));

            if ($info['goods_storage'] == 0){
                $this->setData(array(),0,'该商品的库存不足！');
            }
            if ($num > $info['goods_storage']){
                $this->setData(array(),0,'该商品的库存不足！');
            }
        }


        $this->setData(array(),1,'');
    }


    /**
     * 店铺是否营业
     * author fup
     * date 2019-07-10
     */
    public function storeIsOpen(){
        $storeMod = &m('store');
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : 0;
        if(!$store_id){
            $this->setData(array(),0,'缺少参数');
        }
        if(!$this->getResult($store_id,2)){
            $this->setData(array(),0,'暂停营业');
        }
        $store = $storeMod->getOne(array('cond'=>'id = ' . $store_id .' AND is_open = 1','fields'=>'id,store_start_time,store_end_time'));
        $now = time();
        $_now = date('Y-m-d',$now);
        if(!empty($store)){
            $start_time = strtotime($_now . ' ' .$store['store_start_time'] . ':00');
            $end_time = strtotime($_now . ' ' . $store['store_end_time'] . ':00');
            if($now > $start_time && $now < $end_time){
                $this->setData(array(),1,'正常营业');
            }
            $this->setData(array(),0,'暂停营业');
        }
        $this->setData(array(),0,'暂停营业');
    }

    /**
     * 是否允许余额支付
     * author fup
     * date 2019-07-10
     */
    public function isBalancePay(){
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : 0;
        if(!$store_id){
            $this->setData(array(),0,'缺少参数');
        }
        if($this->getResult($store_id,3)){
            $this->setData(array(),1,'允许余额支付');
        }
        $this->setData(array(),0,'暂不允许余额支付');
    }

    /**
     * 是否营业，是否允许使用余额
     * author fup
     * date 2019-07-10
     */
    private function getResult($storeId=0,$type=0){
        $storeMod = &m('store');
        $sql = 'SELECT * FROM '. DB_PREFIX . 'store_console' . ' WHERE type = ' . $type . ' AND mark = 1 limit 1';
        $data = $storeMod->querySql($sql);
        $storeColsed = explode(',', $data[0]['relation_1']);
        if(!in_array($storeId,$storeColsed)){
            return true;
        }
        return false;
    }

    /**
     * 获取秒杀活动时间段
     */
    public function getTimePoint(){
        $spikeGoodsMod = &m('spikeActiviesGoods');
        $time = $spikeGoodsMod::$time;
        foreach ($time as $k => $item){
            $data[] = array(
                'time_point' => $k,
                'time' => $item . ':00',
                'state' => $this->getSpikeStatus($item),
                'startTime' => date('Y-m-d') .' '.$item . ':00:00',
                'perendTime' => date('Y-m-d H:i:s',strtotime(date('Y-m-d') .' '.$item . ':00:00') + 3600 * 2 -1),
            );
        }
        $this->setData($data,1,'SUCCESS');
    }

    /**
     * 获取当前时间段状态
     * author fup
     * date 2019-07-15
     */
    public function getSpikeStatus($time_point){
        $time = time();
        $start_time = strtotime(date('Y-m-d') .' '.$time_point . ':00:00');
        $end_time = strtotime(date('Y-m-d') .' '.$time_point . ':00:00') + 3600 * 2 -1;
        if($time > $start_time && $time < $end_time){
            $status = '进行中';
        }else if($time < $start_time){
            $status = '未开始';
        }else if($time > $end_time){
            $status = '已结束';
        }
        return $status ? : '';

    }

    /**
     * 获取秒杀商品
     * author fup
     * date 2019-07-15
     */
    public function getSpikeActivityGoods(){
        $spikeGoodsMod = &m('spikeActiviesGoods');
        $time_point = $_REQUEST['time_point'] ? $_REQUEST['time_point'] : $this->getDefaultTimePoint();
        $time = $spikeGoodsMod::$time;
        $sql = 'SELECT a.id,a.store_id,g.store_goods_id,g.goods_name,g.goods_img,g.goods_num,g.limit_num,g.goods_price,g.discount_price,s.store_name FROM ' . DB_PREFIX . 'spike_goods as g LEFT JOIN ' .DB_PREFIX . 'spike_activity as a ON a.id = g.spike_id AND g.time_point = ' . $time_point . ' AND g.mark = 1 LEFT JOIN ' . DB_PREFIX . 'store as s on a.store_id = s.id WHERE a.status = 1 AND a.start_time < '.time() . ' AND a.end_time > ' . time();
//        var_dump($sql);die;
        $spike_goods = array(
            array(
                'id' => 170,
                'store_id' => 84,
                'store_goods_id' => 15199,
                'goods_name' => 'A测试香芋拿铁奶茶',
                'goods_img' => 'upload/images/goods/2019010745/1546835064.jpg',
                'goods_num' => 8,
                'goods_remain' => 4,
                'time_point' => $time_point,
                'start_time' => strtotime(date('Y-m-d').'' .$time[$time_point].':00:00'),
                'end_time' => strtotime(date('Y-m-d').'' .$time[$time_point].':00:00') + 3600*2-1,
                'limit_num' => 8,
                'goods_price' => '5',
                'discount_price' => '1',
                'store_name' => '测试站点'
            ),
            array(
                'id' => 170,
                'store_id' => 84,
                'store_goods_id' => 19725,
                'goods_name' => 'A测试黑森林慕斯奶茶有规格',
                'goods_img' => 'upload/images/goods/2019022119/1550730481.jpg',
                'goods_num' => 8,
                'goods_remain' => 2,
                'time_point' => $time_point,
                'start_time' => strtotime(date('Y-m-d').'' .$time[$time_point].':00:00'),
                'end_time' => strtotime(date('Y-m-d').'' .$time[$time_point].':00:00') + 3600*2-1,
                'limit_num' => 8,
                'goods_price' => '5',
                'discount_price' => '1',
                'store_name' => '测试站点'
            ),
        );
//        $spike_goods = $spikeGoodsMod->querySql($sql);
        return $this->setData($spike_goods,1,'SUCCESS');
    }

    /**
     * 获取当前时间段
     */
    public function getDefaultTimePoint(){
        $spikeGoodsMod = &m('spikeActiviesGoods');
        $now = date('H');
        $new_time = $time = $spikeGoodsMod::$time;
        if(!in_array($now,$new_time)){
            $new_time[] = $now;
            sort($new_time);
            $now = $new_time[array_search($now,$new_time) - 1];
        }
        return array_search($now,$time);

    }


     /**
     * 获取客服开启状态
     */
    public function getCustomerStatus(){
        $storeMod = &m('store');
        $sql = 'SELECT * FROM '. DB_PREFIX . 'store_console' . ' WHERE type = 4 AND mark = 1 ORDER BY create_time DESC LIMIT 1';
        $data = $storeMod->querySql($sql);
        if($data && $data[0]['status'] == 1){
            $this->setData(array(),1,'开启');
        }
        $this->setData(array(),0,'未开启');
    }
}
