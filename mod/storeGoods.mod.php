<?php

/**
 * 店铺商品模型
 * @author: luffy
 * @date: 2017-11-07
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class storeGoodsMod extends BaseMod
{
    public $relation_name = '';
    protected $redis_name;
    protected $redis_hot_goods;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct("store_goods");
        //定义缓存键名
        $this->redis_name   = $this->modName . '_relation';
        $this->redis_hot_goods   = $this->modName . '_hot_goods';
    }

    /**
     * 规格关联数据缓存-分类树
     * @author: xt
     * @date  : 2019-02-25
     */
    public function relationRedis(){
        $storeGoodsMod = &m('storeGoods');

        $page = 1;
        $pageSize = 10000;
        $storeGoodsContainers = array();
        do {
            $start = ($page - 1) * $pageSize;
            $sql = <<<SQL
            SELECT
                s.id,
                s.goods_id,
                s.cat_id,
                s.store_id,
                s.shop_price,
                s.market_price,
                l.goods_name,
                g.original_img,
                p.key,
                p.store_goods_id,
                c.room_type_id,
                s.goods_storage,
                s.attributes,
                g.auxiliary_class,
                g.auxiliary_type,
                g.room_id,
                s.delivery_fee
            FROM
                bs_store_goods s
                LEFT JOIN bs_goods_lang l ON s.goods_id = l.goods_id
                LEFT JOIN bs_goods g ON s.goods_id = g.goods_id
                LEFT JOIN bs_room_category c ON s.cat_id = c.category_id
                LEFT JOIN bs_store_goods_spec_price p ON s.id = p.store_goods_id
                AND p.mark = 1
            WHERE
                s.mark = 1
                AND s.is_on_sale = 1
                AND l.lang_id = {$this->lang_id}
                limit {$start},{$pageSize}
SQL;
            $storeGoods = $storeGoodsMod->querySql($sql);


            // 获取规格 key 的值
            foreach ($storeGoods as $storeGood) {
                $keyIds = array();

                if ($storeGood['key']) {
                    $keyIds = explode('_', $storeGood['key']);
                }

                if (isset($storeGoodsContainers[$storeGood['id']])) {
                    $storeGoodsContainers[$storeGood['id']]['key_ids'] = array_merge($storeGoodsContainers[$storeGood['id']]['key_ids'], $keyIds);
                } else {
                    $storeGoodsContainers[$storeGood['id']]['basic'] = $storeGood;
                    $storeGoodsContainers[$storeGood['id']]['key_ids'] = $keyIds;
                }
            }
            $num = count($storeGoods);
            $page += 1;
        } while ($num >= $pageSize);
        unset($storeGoods);  // 释放 storeGoods 变量

        // 规格 key 去重
        foreach ($storeGoodsContainers as $index => $storeGoodsContainer) {
            $spec = array();

            $uniqueKeyIds = array_unique($storeGoodsContainer['key_ids']);

            unset($storeGoodsContainers[$index]['key_ids']);

            // 是否存在规格id
            if ($uniqueKeyIds) {
                $keyId = implode(',', $uniqueKeyIds);

                $sql = <<<SQL
                SELECT
                    s.id,
                    l.spec_name,
                    il.item_name,
                    i.id as item_id
                FROM
                    bs_goods_spec s
                    LEFT JOIN bs_goods_spec_lang l ON s.id = l.spec_id
                    LEFT JOIN bs_goods_spec_item i ON s.id = i.spec_id
                    LEFT JOIN bs_goods_spec_item_lang il ON i.id = il.item_id 
                WHERE
                    i.id IN ( {$keyId} ) 
                    AND l.lang_id = {$this->lang_id} 
                    AND il.lang_id = {$this->lang_id}
SQL;
                $goodsSpecs = $storeGoodsMod->querySql($sql);


                foreach ($goodsSpecs as $goodsSpec) {
                    if (isset($spec[$goodsSpec['id']])) {
                        $spec[$goodsSpec['id']]['itemInfo'][] = array(
                            'item_id' => $goodsSpec['item_id'],
                            'item_name' => $goodsSpec['item_name'],
                        );
                    } else {
                        $spec[$goodsSpec['id']] = array(
                            'id' => $goodsSpec['id'],
                            'spec_name' => $goodsSpec['spec_name'],
                            'itemInfo' => array(array(
                                'item_id' => $goodsSpec['item_id'],
                                'item_name' => $goodsSpec['item_name'],
                            )),
                        );
                    }
                }
            }
            $storeGoodsContainers[$index]['spec'] = $spec;
        }

        $this->redis->set($this->redis_name, $storeGoodsContainers);

        // 热销商品缓存
        $this->hotGoodsRedis();

    }

    /**
     * 热销商品缓存
     */
    public function hotGoodsRedis()
    {
        $orderGoodsMod = &m('orderGoods');
        $sql = <<<SQL
                SELECT
                    sg.id,
                    og.store_id,
                    sum( og.goods_num ) AS total_amount 
                FROM
                    bs_store_goods sg
                    LEFT JOIN bs_order_goods og ON sg.id = og.goods_id 
                    AND og.order_state IN ( 20, 30, 40, 50 ) 
                WHERE
                    sg.is_on_sale = 1 
                    AND sg.mark = 1 
                GROUP BY
                    sg.store_id,
                    sg.id 
                ORDER BY
                    og.store_id DESC,
                    total_amount DESC
SQL;
        $orderGoods = $orderGoodsMod->querySql($sql);

        //缓存数据
        if( $this->redis->get($this->redis_hot_goods) ){
            $this->redis->drop($this->redis_hot_goods);
        }
        $this->redis->set($this->redis_hot_goods, $orderGoods);
    }

    /**
     * 获取缓存数据
     * @return mixed
     */
    public function getHotGoods()
    {
        return $this->redis->get($this->redis_hot_goods);
    }

    /**
     * 统计--获取商品信息
     * @param $store_cate_id    区域ID
     * @param $store_id         店铺ID
     * @param $is_on_sale       是否在售
     * @param is_recommend      是否推荐
     * @author: luffy
     * @date  : 2017-11-07
     */
    public function getGoodsInfoCount($store_cate_id = 0, $store_id = 0, $is_on_sale = 1, $is_recommend = 0)
    {
        $sql = " mark = 1 ";

        if ($store_id > 0) {
            $sql .= " AND store_id = {$store_id}";
        } else {
            //获取区域下店铺(不传区域则是获取可用店铺)
            $storeMod = &m('store');
            $storeIds = $storeMod->getStoreIds($store_cate_id, 1);
            $sql .= " AND store_id in ({$storeIds}) ";
        }

        if ($is_on_sale > 0) {
            $sql .= " AND is_on_sale = {$is_on_sale} ";
        }

        if ($is_recommend > 0) {
            $sql .= " AND is_recommend = {$is_recommend} ";
        }

        //获取复合条件商品数量
        $storeGoodsMod = &m('storeGoods');
        $query = array('cond' => $sql);
        $storeGoodsCount = $storeGoodsMod->getCount($query);
//        echo '<pre>';print_r( $storeGoodsCount );die;
        return $storeGoodsCount;
    }

    public function getSku($goods_id)
    {
        $rs = $this->getOne(array('cond' => "`id` = '{$goods_id}' and mark =1", 'fields' => "sku"));
        return $rs['sku'];
    }

    public function getGoodsName($goods_id, $lang_id)
    {
        $sql = "SELECT gl.goods_name from " . DB_PREFIX . "goods as g LEFT JOIN " . DB_PREFIX . "goods_lang as gl ON g.goods_id = gl.goods_id
          WHERE g.goods_id =(SELECT `goods_id` from " . DB_PREFIX . "store_goods WHERE id = {$goods_id}) AND gl.lang_id = {$lang_id} ";
        $rs = $this->querySql($sql);
        return $rs[0]['goods_name'];
    }

    /**
     * 获取区域商品的主图
     */
    public function getStoreGoodImg($goods_id)
    {
        $sql = 'select gl.original_img  from  '
            . DB_PREFIX . 'store_goods as g  left join '
            . DB_PREFIX . 'goods as gl on g.goods_id = gl.goods_id where g.id  = ' . $goods_id;
        $rs = $this->querySql($sql);
        return $rs[0]['original_img'];
    }

    public function getGoodsName2($id, $lang_id)
    {
        $info = $this->getOne(array("cond" => "goods_id=" . $id));
        $langMod = &m('goodsLang');
        $lang_info = $langMod->getOne(array("cond" => "goods_id=" . $id . " and lang_id=" . $lang_id));
        if ($lang_info) {
            $info['goods_name'] = $lang_info['goods_name'];
        } else {
            $lang_info = $langMod->getOne(array("cond" => "goods_id=" . $id . " and lang_id=" . $lang_id));
            $info['goods_name'] = $lang_info['goods_name'];
        }
        return $info;
    }

    public function getGoodsInfo($id, $lang_id)
    {
        $info = $this->getOne(array("cond" => "id=" . $id));
        $goods_info = $this->getGoodsName2($info['goods_id'], $lang_id);
        return $goods_info;
    }

    /**
     * 获取代客下单普通商品信息
     * @param $storeId
     * @param $languageId
     * @return array
     */
    public function getDkxdGoodsInfo($storeId, $languageId)
    {

//        $goods = $this->getData(array('cond'=>'store_id='.$storeId.' and mark = 1 and is_on_sale = 1'));
//
//        $idList = array();
//        foreach ($goods as $key => $value) {
//            $idList[] = $value['id'];
//        }
//        $goodsData = $this->redis->get($this->redis_name); //redis获取商品信息
//
//        //店铺所有商品
//        $storeGoods = array();
//        if ($idList) {
//            foreach ($idList as $key => &$val) {
//                $storeGoods[] = $goodsData[$val]['basic'];
//            }
//        }
            $sql = 'SELECT a.id,a.goods_id,a.cat_id,a.store_id,a.shop_price,a.market_price,a.goods_name,a.original_img,a.room_id,a.goods_storage,a.attributes,a.delivery_fee,b.auxiliary_class,b.auxiliary_type FROM bs_store_goods as a LEFT JOIN bs_goods as b ON a.goods_id = b.goods_id WHERE a.store_id = '.$storeId .' AND a.mark = 1 AND a.is_on_sale = 1';
            $storeGoods = $this->querySql($sql);
        //获取二级业务类型
        $sql = 'select d.id,e.type_name from ' .
            DB_PREFIX . 'room_type as a left join ' .
            DB_PREFIX . 'room_type_lang as b on a.id = b.type_id left join ' .
            DB_PREFIX . 'store_business as c on a.id = c.buss_id left join ' .
            DB_PREFIX . 'room_type as d on a.id = d.superior_id left join ' .
            DB_PREFIX . 'room_type_lang as e on d.id = e.type_id ' .
            ' where a.superior_id = 0 and b.lang_id=' . $languageId . ' and c.store_id = ' . $storeId .
            ' and e.lang_id = ' . $languageId . ' order by d.sort asc,d.id asc ';
        $roomInfo = $this->querySql($sql);
        //获取店铺折扣
        $sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $storeId;
        $discountInfo = $this->querySql($sql);
        $discount = $discountInfo[0]['store_discount'];
        //获取普通商品
//        if($storeId == 98){
//            echo '<pre>';
//            print_r($roomInfo);
//            print_r($storeGoods);die;
//        }
        $roomId_arr = array();
        foreach ($roomInfo as &$v) {
            $roomId_arr[] = $v['id'];
            foreach ($storeGoods as $key => &$value) {
                $value['shop_price'] = number_format($value['shop_price'] * $discount, 2, '.', '');
                if ($value['room_id'] == $v['id']) {
                    $v['goodsInfo'][] = $value;
                }else{
                    if ($value['auxiliary_type']) {
                        if (strpos($value['auxiliary_type'],',')) {
                            $type = explode(',', $value['auxiliary_type']);
                            foreach ($type as $k => $va) {
                                if ($va == $v['id']) {
                                    $v['goodsInfo'][] = $value;
                                }
                            }
                        } else {
                            if ($value['auxiliary_type'] == $v['id']) {
                                $v['goodsInfo'][] = $value;
                            }
                        }
                    }
                }


            }
        }
        $info = array(
            'roomId_arr' => $roomId_arr,
            'roomInfo' => $roomInfo,
        );
        return $info;
    }


    /**
     * 获取代客下单热销商品信息
     * $storeid 店铺id
     * $cateids 分类id字符串，逗号分隔
     */
    public function getDkxdRxGoodsInfo($storeid, $cateids)
    {
        $sql = 'select a.id,a.goods_id,a.cat_id,a.goods_name,a.goods_storage,a.shop_price,a.original_img,count(c.rec_id) as num from ' .
            DB_PREFIX . 'store_goods as a left join ' .
            DB_PREFIX . 'room_category as b on a.cat_id = b.category_id left join ' .
            DB_PREFIX . 'order_goods as c on a.id = c.goods_id ' .
            ' where a.mark = 1 and a.is_on_sale = 1 and a.store_id = ' . $storeid . ' and b.room_type_id in (' . $cateids . ') group by a.id order by num desc, a.id desc limit 8 ';
        $info = $this->querySql($sql);
        return $info;
    }

    /**
     * 获取代客下单优惠商品信息
     * $storeid 店铺id
     * $cateids 分类id字符串，逗号分隔
     */
    public function getDkxdYhGoodsInfo($storeid, $cateids)
    {
        $curtime = time();
        $curseconds = $curtime - strtotime(date('Ymd'));
        //秒杀活动
        $sql = 'select a.id,a.goods_id,a.cat_id,a.goods_name,a.goods_storage,a.shop_price,a.original_img,c.discount,c.goods_num,c.id as spikeid from ' .
            DB_PREFIX . 'store_goods as a left join ' .
            DB_PREFIX . 'room_category as b on a.cat_id = b.category_id left join ' .
            DB_PREFIX . 'spike_activity as c on a.id = c.store_goods_id ' .
            ' where a.mark = 1 and a.is_on_sale = 1 and a.store_id = ' . $storeid .
            ' and b.room_type_id in (' . $cateids . ') and c.start_time <= ' . $curtime . ' and c.end_time >= ' . $curtime .
            ' and c.start_our <= ' . $curseconds . ' and c.end_our >=' . $curseconds . ' order by a.id desc ';
        $spikeInfo = $this->querySql($sql);
        foreach ($spikeInfo as &$v) {
            $v['shop_price'] = number_format($v['shop_price'] * $v['discount'] / 10, 2);
        }
        //团购活动
        $sql = 'select a.id,a.goods_id,a.cat_id,a.goods_name,a.goods_storage,a.shop_price,a.original_img,c.group_goods_price,c.group_goods_num,c.id as groupid from ' .
            DB_PREFIX . 'store_goods as a left join ' .
            DB_PREFIX . 'room_category as b on a.cat_id = b.category_id left join ' .
            DB_PREFIX . 'goods_group_buy as c on a.id = c.goods_id ' .
            ' where a.mark = 1 and a.is_on_sale = 1 and a.store_id = ' . $storeid .
            ' and b.room_type_id in (' . $cateids . ') and c.mark = 1 and c.start_time <= ' . $curtime . ' and c.end_time >= ' . $curtime .
            ' order by a.id desc ';
        $groupInfo = $this->querySql($sql);
        //促销活动
        $sql = 'select a.id,a.goods_id,a.cat_id,a.goods_name,a.goods_storage,a.shop_price,a.original_img,c.discount_price,c.limit_amount,c.prom_id from ' .
            DB_PREFIX . 'store_goods as a left join ' .
            DB_PREFIX . 'room_category as b on a.cat_id = b.category_id left join ' .
            DB_PREFIX . 'promotion_goods as c on a.id = c.goods_id left join ' .
            DB_PREFIX . 'promotion_sale as d on c.prom_id = d.id ' .
            ' where a.mark = 1 and a.is_on_sale = 1 and a.store_id = ' . $storeid .
            ' and b.room_type_id in (' . $cateids . ') and d.mark = 1 and d.start_time <= ' . $curtime . ' and d.end_time >= ' . $curtime .
            ' group by c.prom_id, c.goods_id order by a.id desc ';
        $promInfo = $this->querySql($sql);
        return array('spikeInfo' => $spikeInfo, 'groupInfo' => $groupInfo, 'promInfo' => $promInfo);
    }

    /**
     * 代客下单支付后，更新库存
     */
    public function changeDkxdGoodsNum($order_sn)
    {
        $storeGoodItemPriceMod = &m('storeGoodItemPrice');
        $areaGoodMod = &m('areaGood');
        $sql = "SELECT sg.goods_id,sg.spec_key,sg.goods_num FROM " . DB_PREFIX . "order as r LEFT JOIN " . DB_PREFIX . "order_goods as sg ON  r.order_sn = sg.order_id WHERE sg.order_id =" . $order_sn;
        $orderRes = $this->querySql($sql);

        foreach ($orderRes as $k => $v) {
            if (!empty($v['spec_key'])) {
                $query_id = "select `id` from " . DB_PREFIX . "store_goods_spec_price where `store_goods_id` = '{$v['goods_id']}' and `key` = '{$v['spec_key']}' ";
                $res_query = $this->querySql($query_id);
                $specInfoSql = "select goods_storage from  " . DB_PREFIX . "store_goods_spec_price WHERE `store_goods_id` = '{$v['goods_id']}' and `key` = '{$v['spec_key']}'";
                $specInfo = $this->querySql($specInfoSql);
                $condition = array(
                    'goods_storage' => $specInfo[0]['goods_storage'] - $v['goods_num']
                );
                $res = $storeGoodItemPriceMod->doEdit($res_query[0]['id'], $condition);
                if ($res) {
                    $infoSql = "select goods_storage from  " . DB_PREFIX . "store_goods WHERE `id` = '{$v['goods_id']}'";
                    $Info = $this->querySql($infoSql);
                    $cond = array(
                        'goods_storage' => $Info[0]['goods_storage'] - $v['goods_num']
                    );
                    $areaGoodMod->doEdit($v['goods_id'], $cond);
                }
            } else {
                $specInfoSql = "select goods_storage from  " . DB_PREFIX . "store_goods WHERE `id` = '{$v['goods_id']}'";
                $specInfo = $this->querySql($specInfoSql);
                $condition = array(
                    'goods_storage' => $specInfo[0]['goods_storage'] - $v['goods_num']
                );
                $areaGoodMod->doEdit($v['goods_id'], $condition);
            }
        }
        return true;
    }


    /**
     * 获取缓存商品数据
     * @author: gao
     * @date  : 2019-02-27
     */
    public function getRedisStoreGoodsCopy($storeId,$categoryId,$userId)
    {
        $storeMod =& m('store');
        $data = $this->redis->get($this->redis_name); //redis获取商品信息
        foreach ($data as $key => $val) {
            if ($val['basic']['store_id'] == $storeId && $val['basic']['room_type_id'] == $categoryId) {
                $storeGoods[] = $val;
            }
        }
        foreach ($storeGoods as $key=>$val) {
            //店铺商品打折
            $storeInfo = $storeMod->getOne(array('cond' => "`id`='{$storeId}'", 'fields' => 'store_discount'));
            $storeDiscount = $storeInfo['store_discount'];
            $storeGoods[$key]['basic']['sale_price'] = number_format($val['shop_price'], 2);//零售价
            $member_price = $val['basic']['shop_price'] * $storeDiscount - ($this->getPointAccount($val['basic']['shop_price'] * $storeDiscount, $storeId,$userId));
            $storeGoods[$key]['basic']['shop_price'] = number_format($val['basic']['shop_price'] * $storeDiscount, 2);//折扣价
            $storeGoods[$key]['basic']['member_price'] = number_format($member_price, 2);//会员价
            $sql = "SELECT sum(goods_num) as num  FROM " . DB_PREFIX . 'order_goods WHERE goods_id=' . $val['basic']['id'] . " and order_state in (20,30,40,50)";
            $orderGoodsData = $this->querySql($sql);
            $storeGoods[$key]['basic']['order_num']=$orderGoodsData[0]['num'];
            $goodsCommentMod = &m('goodsComment');
            $sql = 'select  sum(goods_rank) / count(goods_rank) as res,count(*) as num from ' . DB_PREFIX . 'goods_comment where goods_id = ' . $val['basic']['id'];
            $trance = $goodsCommentMod->querySql($sql);
            $storeGoods[$key]['basic']['rate'] = (int)$trance[0]['res'];
            $storeGoods[$key]['basic']['num'] = $trance[0]['num'];
        }
        $gooodInfo = array('data' => $storeGoods, 'type' => 3);
        return $gooodInfo;
    }



    /**
     * 获取缓存商品数据
     * @author: gao
     * @date  : 2019-02-27
     */
    public function getRedisStoreGoods($storeId,$rtid,$langId,$userId)
    {
        $storeMod =& m('store');
        $data = $this->redis->get($this->redis_name); //redis获取商品信息
        //店铺商品打折
        $storeInfo = $storeMod->getOne(array('cond' => "`id`='{$storeId}'", 'fields' => 'store_discount'));
        $storeDiscount = $storeInfo['store_discount'];
        //热销商品
        $hotGoods=$this->getHotGoods();
        foreach($hotGoods as $key=>$val){
            if($val['store_id']==$storeId){
                $sortHotGoods[]=$val;
            }
        }
        $sortHotGoods=array_slice($sortHotGoods,0,5);
        //店铺的所有子业务类型
        $roomTypeMod = &m('roomType');
        $roomtypearr = $roomTypeMod->getBusinessType($langId, $storeId, $rtid, 1);
        foreach($roomtypearr as $key=>$val){
            $roomId[]=$val['id'];
        }
        $goodsList = $this->getData(array('cond'=>'store_id='.$storeId.' and mark = 1 and is_on_sale = 1'));
        $idList = array();
        foreach ($goodsList as $key => $value) {
            $idList[] = $value['id'];
        }
        //店铺所有商品
        $storeGoods = array();
        if ($idList) {
            foreach ($idList as $key => &$val) {
                $storeGoods[] = $data[$val];
            }
        } 
        foreach ($storeGoods as $key=>$val) {
            $storeGoods[$key]['basic']['sale_price'] = number_format($val['basic']['market_price'], 2);//零售价 
            $member_price = $val['basic']['shop_price'] * $storeDiscount - ($this->getPointAccount($val['basic']['shop_price'] * $storeDiscount, $storeId,$userId));
            $storeGoods[$key]['basic']['shop_price'] = number_format($val['basic']['shop_price'] * $storeDiscount, 2);//折扣价
            $storeGoods[$key]['basic']['member_price'] = number_format($member_price, 2);//会员价
            $validRoom[] = $val['basic']['room_id'];
            if ($val['basic']['auxiliary_type']) {
                if (strpos($val['basic']['auxiliary_type'],',')) {
                    $type = explode(',', $val['basic']['auxiliary_type']);
                    foreach ($type as $k => $v) {
                        array_push($validRoom, $v);
                    }
                } else {
                    array_push($validRoom, $val['basic']['auxiliary_type']);
                }
            }
        }
        $validRoom = array_unique($validRoom);
        foreach($roomtypearr as $key=>$val){
            if(!in_array($val['id'],$validRoom)){
                unset($roomtypearr[$key]);
            }
        }
        foreach($sortHotGoods as $key=>$val){
            foreach($storeGoods as $k=>$v){
                if($val['id']==$v['basic']['id']){
                    $hotGoodsInfo[]=$v['basic'];
                }
            }
        }
        foreach ($roomtypearr as $k => $v) {
            foreach ($storeGoods as $key => $val) {
                if ($val['basic']['room_id'] == $v['id']) {
                    $val['room_name'] = $v['type_name'];
                    $goods[$v['id']][] = $val;
                } else {
                    if ($val['basic']['auxiliary_type']) {
                        if (strpos($val['basic']['auxiliary_type'], ',')) {
                            $type = explode(',', $val['basic']['auxiliary_type']);
                            foreach ($type as $ke => $va) {
                                if ($va == $v['id']) {
                                    $val['room_name'] = $v['type_name'];
                                    $goods[$v['id']][] = $val;
                                }
                            }
                        } else {
                            if ($val['basic']['auxiliary_type'] == $v['id']) {
                                $val['room_name'] = $v['type_name'];
                                $goods[$v['id']][] = $val;
                            }
                        }
                    }
                }
            }
        }

        $data=array(
            'goods'=>$goods,
            'room'=>$roomtypearr,
            'hotGoods'=>$hotGoodsInfo
        );

        return $data;
    }


    /**
     * 获取缓存商品数据
     * @author: gao
     * @date  : 2019-02-27
     */
    public function getStoreGoods($storeId,$rtid,$langId,$userId)
    {
        $storeMod =& m('store');
        $data = $this->redis->get($this->redis_name); //redis获取商品信息

        //店铺商品打折
        $storeInfo = $storeMod->getOne(array('cond' => "`id`='{$storeId}'", 'fields' => 'store_discount'));
        $storeDiscount = $storeInfo['store_discount'];
        //热销商品
        /*$storeGoodsMod=&m('goods');*/
        $hotGoods=$this->getHotGoods();
        foreach($hotGoods as $key=>$val){
            if($val['store_id']==$storeId){
                $sortHotGoods[]=$val;
            }
        }
        $sortHotGoods=array_slice($sortHotGoods,0,5);
        //店铺的所有子业务类型
        $roomTypeMod = &m('roomType');
        $roomtypearr = $roomTypeMod->getBusinessType($langId, $storeId, $rtid, 1);
        foreach($roomtypearr as $key=>$val){
            $roomId[]=$val['id'];
        }
        foreach ($data as $key => $val) {
            if ($val['basic']['store_id'] == $storeId && in_array($val['basic']['room_type_id'],$roomId) ) {
                $storeGoods[] = $val;
            }
        }

        foreach ($storeGoods as $key=>$val) {
            $storeGoods[$key]['basic']['sale_price'] = number_format($val['basic']['shop_price'], 2);//零售价
            $member_price = $val['basic']['shop_price'] * $storeDiscount - ($this->getPointAccount($val['basic']['shop_price'] * $storeDiscount, $storeId,$userId));
            $storeGoods[$key]['basic']['shop_price'] = number_format($val['basic']['shop_price'] * $storeDiscount, 2);//折扣价
            $storeGoods[$key]['basic']['member_price'] = number_format($member_price, 2);//会员价
            $validRoom[] = $val['basic']['room_type_id'];
            /*$sql = "SELECT sum(goods_num) as num  FROM " . DB_PREFIX . 'order_goods WHERE goods_id=' . $val['basic']['id'] . " and order_state in (20,30,40,50)";
            $orderGoodsData = $this->querySql($sql);
            $storeGoods[$key]['basic']['order_num']=$orderGoodsData[0]['num'];
            $goodsCommentMod = &m('goodsComment');
            $sql = 'select  sum(goods_rank) / count(goods_rank) as res,count(*) as num from ' . DB_PREFIX . 'goods_comment where goods_id = ' . $val['basic']['id'];
            $trance = $goodsCommentMod->querySql($sql);
            $storeGoods[$key]['basic']['rate'] = (int)$trance[0]['res'];
            $storeGoods[$key]['basic']['num'] = $trance[0]['num'];*/
        }

        $validRoom = array_unique($validRoom);
        foreach($roomtypearr as $key=>$val){
            if(!in_array($val['id'],$validRoom)){
                unset($roomtypearr[$key]);
            }
        }
        foreach($sortHotGoods as $key=>$val){
            foreach($storeGoods as $k=>$v){
                if($val['id']==$v['basic']['id']){
                    $hotGoodsInfo[]=$v['basic'];
                }
            }
        }
        foreach($roomtypearr as $k=>$v){
            foreach($storeGoods as $key=>$val){
                if($val['basic']['room_type_id']==$v['id']){
                    $roomtypearr[$k]['goods'][] =$val;
                }
            }
        }
        foreach($roomtypearr as $key =>$val){
            $roomTypeData[] =$val;
        }
        $data=array(
            'room'=>$roomTypeData,
            'hotGoods'=>$hotGoodsInfo
        );
        return $data;
    }


    public function getGoods($storeId,$rtid,$langId,$userId,$type,$type_id = 0, $search_name = '')
    {
//         $data = $this->redis->get($this->redis_name);
         $new_data = array();
//        if($storeId == 76){
            $store_goods  = $this->getData(array('fields'=>'id,goods_id,cat_id,store_id,shop_price,market_price,goods_name,original_img,goods_storage,attributes,room_id as room_type_id,delivery_fee','cond'=>'store_id='.$storeId.' and mark = 1 and is_on_sale = 1 ','order_by'=>'sort ASC,id DESC'));
            foreach ($store_goods as $k => $v){
                $new_data[$v['id']] = array(
                    'basic' => $v
                );
            }
            $data = $new_data;
//        }
        //商品名称模糊
        if (!empty($search_name)) {
            $goods_all = array();
            $tiaojian = ' and goods_name like "%' . $search_name . '%"';
            $goodsArr = $this->getData(array('fields'=>'id','cond'=>'store_id='.$storeId.' and mark = 1 and is_on_sale = 1 '.$tiaojian));
            foreach($goodsArr as $key => $value){
                $goods_all[] = $data[$value['id']];
            }
            $data = $goods_all;
        }

        $storeMod = &m('store');

        //店铺商品打折
        $storeInfo = $storeMod->getOne(array('cond' => "`id`='{$storeId}'", 'fields' => 'store_discount'));
        $storeDiscount = $storeInfo['store_discount'];

        $roomTypeMod = &m('roomType');
        $roomtypearr = $roomTypeMod->getBusinessType($langId, $storeId, $rtid, 1);

        foreach($roomtypearr as $key=>$val){
            $roomId[]=$val['id'];
        }
        if($type_id){
            foreach ($data as $key => $val){  
                if($val['basic']['store_id'] == $storeId && $val['basic']['room_type_id'] == $type_id){
                    $storeGoods[] = $val;
                }
            }
        } else {
            foreach ($data as $key => $val){
                if($val['basic']['store_id'] == $storeId && in_array($val['basic']['room_type_id'], $roomId)){
                    $storeGoods[] = $val;
                }
            }
        }
        foreach ($storeGoods as $k => $v){
            $inArr = explode(',',$v['basic']['attributes']);
            $storeGoods[$k]['basic']['attributes_name'] = $inArr;
        }
        foreach ($storeGoods as $kk => $vv) {
            if (in_array($type,$vv['basic']['attributes_name'])){
                $goods[] = $vv;
            }
        }
        foreach ($goods as $key => $val){
            $goods[$key]['basic']['sale_price'] = number_format($val['basic']['market_price'], 2);//零售价
            $member_price = $this->getPointAccount($val['basic']['shop_price'] * $storeDiscount, $storeId,$userId);
            $goods[$key]['basic']['shop_price'] = number_format($val['basic']['shop_price'] * $storeDiscount, 2);//折扣价
            $goods[$key]['basic']['member_price'] = number_format($member_price, 2);//会员价
            $validRoom[] = $val['basic']['room_type_id'];
        }
        $validRoom = array_unique($validRoom);
        foreach($roomtypearr as $key=>$val){
            if(!in_array($val['id'],$validRoom)){
                unset($roomtypearr[$key]);
            }
        }
        $goodss = array();
        foreach($roomtypearr as $k=>$v){
            foreach($goods as $key=>$val){
                if($val['basic']['room_type_id']==$v['id']) {
                    $goodss[] = $val;
                }
            }
        }
        $data=array(
            'goods'=>$goodss,
            'room'=>$roomtypearr,
        );
        return $data;
    }
 
    /**
     * 店铺商品列表-----默认到店自提
     * @author  luffy
     * @date    2019-07-11
     */
    public function getGoodsXcx($storeId,$rtid,$langId,$userId,$type,$type_id = 0, $search_name = ''){

        $data = $this->redis->get($this->redis_name);

        //商品名称模糊
        if (!empty($search_name)) {
            $goods_all = array();
            $tiaojian = ' and goods_name like "%' . $search_name . '%"';
            $goodsArr = $this->getData(array('fields'=>'id','cond'=>'store_id='.$storeId.' and mark = 1 and is_on_sale = 1 '.$tiaojian));
            foreach($goodsArr as $key => $value){
                $goods_all[] = $data[$value['id']];
            }
            $data = $goods_all;
        }

        $storeMod = &m('store');

        //店铺商品打折
        $storeInfo = $storeMod->getOne(array('cond' => "`id`='{$storeId}'", 'fields' => 'store_discount'));
        $storeDiscount = $storeInfo['store_discount'];

        $roomTypeMod = &m('roomType');
        $roomtypearr = $roomTypeMod->getBusinessType($langId, $storeId, $rtid, 1);
        foreach($roomtypearr as $key=>$val){
            $roomId[]=$val['id'];
        }
        if($type_id){
            foreach ($data as $key => $val){  
                if($val['basic']['store_id'] == $storeId && $val['basic']['room_type_id'] == $type_id){
                    $storeGoods[] = $val;
                }
            }
        } else {
            foreach ($data as $key => $val){
                if($val['basic']['store_id'] == $storeId && in_array($val['basic']['room_type_id'], $roomId)){
                    $storeGoods[] = $val;
                }
            }
        }
        foreach ($storeGoods as $k => $v){
            $inArr = explode(',',$v['basic']['attributes']);
            $storeGoods[$k]['basic']['attributes_name'] = $inArr;
        }
        foreach ($storeGoods as $kk => $vv) {
            if (in_array($type,$vv['basic']['attributes_name'])){
                $goods[] = $vv;
            }
        }
        foreach ($goods as $key => $val){
            $goods[$key]['basic']['sale_price'] = number_format($val['basic']['shop_price'], 2);//零售价
            $member_price = $val['basic']['shop_price'] * $storeDiscount - ($this->getPointAccount($val['basic']['shop_price'] * $storeDiscount, $storeId,$userId));
            $goods[$key]['basic']['shop_price'] = number_format($val['basic']['shop_price'] * $storeDiscount, 2);//折扣价
            $goods[$key]['basic']['member_price'] = number_format($member_price, 2);//会员价
        }
        $goodss = array();
        foreach($roomtypearr as $k=>$v){
            foreach($goods as $key=>$val){
                if($val['basic']['room_type_id']==$v['id']) {
                    $goodss[] = $val;
                }
            }
        }
//echo '<pre>';print_r(  $goodss );die;
        return $goodss;
    }

    public function getTypeGoods($storeId,$rtid,$langId,$userId,$type)
    {
        $storeMod =&m('store');
        $data = $this->redis->get($this->redis_name);
        //店铺商品打折
        $storeInfo = $storeMod->getOne(array('cond' => "`id`='{$storeId}'", 'fields' => 'store_discount'));
        $storeDiscount = $storeInfo['store_discount'];
        $hotGoods=$this->getHotGoods();
        foreach($hotGoods as $key=>$val){
            if($val['store_id']==$storeId){
                $sortHotGoods[]=$val;
            }
        }
        $sortHotGoods=array_slice($sortHotGoods,0,5);
        $roomTypeMod = &m('roomType');
        $roomtypearr = $roomTypeMod->getBusinessType($langId, $storeId, $rtid, 1);
        foreach($roomtypearr as $key=>$val){
            $roomId[]=$val['id'];
        }
        foreach ($data as $key => $val){
            if ($val['basic']['store_id'] == $storeId && in_array($val['basic']['room_type_id'],$roomId) ) {
                $storeGoods[] = $val;
            }
        }

        foreach ($storeGoods as $k => $v){
            $inArr = explode(',',$v['basic']['attributes']);
            $storeGoods[$k]['basic']['attributes_name'] = $inArr;
        }

        foreach ($storeGoods as $kk => $vv) {
            if (in_array($type,$vv['basic']['attributes_name'])){
                $goods[] = $vv;
            }
        }
        foreach ($goods as $key => $val){
            $goods[$key]['basic']['sale_price'] = number_format($val['basic']['shop_price'], 2);//零售价
            $member_price = $val['basic']['shop_price'] * $storeDiscount - ($this->getPointAccount($val['basic']['shop_price'] * $storeDiscount, $storeId,$userId));
            $goods[$key]['basic']['shop_price'] = number_format($val['basic']['shop_price'] * $storeDiscount, 2);//折扣价
            $goods[$key]['basic']['member_price'] = number_format($member_price, 2);//会员价
            $validRoom[] = $val['basic']['room_type_id'];
        }
        $validRoom = array_unique($validRoom);
        foreach($roomtypearr as $key=>$val){
            if(!in_array($val['id'],$validRoom)){
                unset($roomtypearr[$key]);
            }
        }
        foreach($sortHotGoods as $key=>$val){
            foreach($goods as $k=>$v){
                if($val['id']==$v['basic']['id']){
                    $hotGoodsInfo[]=$v['basic'];
                }
            }
        }
//        echo '<pre>';print_r($hotGoodsInfo);die;
        foreach($roomtypearr as $k=>$v){
            foreach($goods as $key=>$val){
                if($val['basic']['room_type_id']==$v['id']) {
//                    $val['room_name'] = $v['type_name'];
////                    $goodss[$v['id']][] = $val;
                    $roomtypearr[$k]['goods'][] =$val;
                }
            }
        }

        foreach($roomtypearr as $key =>$val){
            $roomTypeData[] =$val;
        }
        $data=array(
            'room'=>$roomTypeData,
            'hotGoods'=>$hotGoodsInfo
        );
        return $data;
    }


    //睿积分抵扣金额
    public function  getPointAccount($total,$storeid,$userId){
        $pointSiteMod = &m('point');
        $storePointMod = &m('storePoint');
        $storeMod = &m('store');
        $curMod = &m('currency');
        //获取订单总金额
        //获取最大积分支付比例
        $point_price_site = $pointSiteMod->getOne(array("cond" => "1=1"));
        $store_point_site = $storePointMod->getOne(array("cond" => "store_id=" . $storeid));
        if($userId){
            $userSql = "select rc.percent from " . DB_PREFIX . "user as u  LEFT JOIN  "
                . DB_PREFIX . "recharge_point AS rc on u.recharge_id=rc.id where u.id = ".$userId;
            $user_id = $storeMod->querySql($userSql);
            if(!empty($user_id[0]['percent'])){
                $point_price_site['point_price'] = $user_id[0]['percent'] + $store_point_site['point_price'];
            }else{
                $point_price_site['point_price'] = empty($store_point_site) ? 0 : $store_point_site['point_price'];
            }
        }else{
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
     * 商品规格信息
     * @param $store_goods_id
     * @return array|void
     */
    public function getGoodsSpec($store_goods_id)
    {
        $lang_id = $_SESSION['store']['defal_lang'];
        $storeGoodsMod = &m('storeGoods');

        $sql = <<<SQL
            SELECT
                s.id,
                s.goods_id,
                s.cat_id,
                s.store_id,
                s.shop_price,
                s.market_price,
                l.goods_name,
                g.original_img,
                p.key,
                p.store_goods_id,
                c.room_type_id,
                s.goods_storage,
                g.auxiliary_class,
                g.auxiliary_type,
                g.room_id,
                s.attributes,
                s.delivery_fee
            FROM
                bs_store_goods s
                LEFT JOIN bs_goods_lang l ON s.goods_id = l.goods_id
                LEFT JOIN bs_goods g ON s.goods_id = g.goods_id
                LEFT JOIN bs_room_category c ON s.cat_id = c.category_id
                LEFT JOIN bs_store_goods_spec_price p ON s.id = p.store_goods_id 
                AND p.mark = 1 
            WHERE
                s.mark = 1 
#                 AND s.is_on_sale = 1
                AND l.lang_id = {$lang_id} 
                AND s.id = {$store_goods_id}
SQL;

        $storeGoods = $storeGoodsMod->querySql($sql);

        if (empty($storeGoods)) {
            return;
        }

        $sql = 'select `key` as spec_key from bs_store_goods_spec_price where store_goods_id=' . $store_goods_id;
        $specKeyInfo = $this->querySql($sql);

        //拼凑属性id
        $keyIds = array();
        foreach ($specKeyInfo as $v) {
            $keyIds = array_merge($keyIds, explode('_', $v['spec_key']));
        }
        $keyIds = array_unique($keyIds);

        //获取规格及属性详细信息
        $sql = 'select a.id,b.id as item_id,c.spec_name,d.item_name from ' .
            DB_PREFIX . 'goods_spec as a left join ' .
            DB_PREFIX . 'goods_spec_item as b on a.id = b.spec_id left join ' .
            DB_PREFIX . 'goods_spec_lang as c on a.id = c.spec_id left join ' .
            DB_PREFIX . 'goods_spec_item_lang as d on b.id = d.item_id ' .
            ' where b.id in (' . implode(',', $keyIds) . ') and c.lang_id=' . $lang_id . ' and d.lang_id = ' . $lang_id . ' order by b.id asc ';
        $data = $this->querySql($sql);
        $specInfo = array();

        foreach ($data as $v) {
            if (isset($specInfo[$v['id']])) {
                $specInfo[$v['id']]['itemInfo'][] = array(
                    'item_id' => $v['item_id'],
                    'item_name' => $v['item_name']
                );
            } else {
                $specInfo[$v['id']] = array(
                    'id' => $v['id'],
                    'spec_name' => $v['spec_name'],
                    'itemInfo' => array(array(
                        'item_id' => $v['item_id'],
                        'item_name' => $v['item_name']
                    ))
                );
            }
        }

        return array(
            'basic' => $storeGoods[0],
            'spec' => $specInfo,
        );
    }

    /*
    * 获取店铺商品id 获取店铺商品的配送方式属性
    * @author: gao
    * @date: 2019/03/05
    */
    public function  getGoodsSendout($storeGoodsId){
        $storeGoodsData=$this->getOne(array('cond'=>"`id` = '{$storeGoodsId}'",'fields'=>'attributes'));
        if(empty($storeGoodsData['attributes'])){
            return 1;  //默认自提1
        }
        return $storeGoodsData['attributes'];
    }


    /*
  * 获取店铺商品id 获取店铺商品的配送方式数组
  * @author: gao
  * @date: 2019/03/05
  */
    public function  getGoodsSendoutArr($storeGoodsId){
        $res=$this->getGoodsSendout($storeGoodsId);
        $sendoutArr=explode(',',$res);
        foreach($sendoutArr as $key=>$val){
            switch($val){
                case 1:
                    $sendout[$val]='到店自提';
                    break;
                case 2:
                    $sendout[$val]='配送上门';
                    break;
                case 3:
                    $sendout[$val]='邮寄托运';
                    break;
                case 4:
                    $sendout[$val]='海外代购';
                    break;
            }
        }
        return $sendout;
    }

    public function isFreeShipping($storeGoodsId){
        $shipping=$this->getOne(array('cond'=>"`id` = '{$storeGoodsId}'",'fields'=>'is_free_shipping,attributes'));//  is_free_shipping 1代表包邮
        $sendout=explode(',',$shipping['attributes']);

        if (in_array(3,$sendout) && $shipping['is_free_shipping'] == 1){
            $isFreeShipping = 1; //包邮
        }else{
            $isFreeShipping = 0; //不包邮
        }
        return $isFreeShipping;
    }



}

?>