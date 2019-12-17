<?php
/**
 * 首页控制器
 * @author: gao
 * @date: 2018-08-14
 */
class DefaultApp extends BasePhApp{

    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct();

    }

    /**
     * 析构函数
     */
    public function __destruct(){
    }


    public function index(){
        $latlon=!empty($_REQUEST['latlon']) ? $_REQUEST['latlon'] : 0;
   $ccc=!empty($_REQUEST['ccc']) ? $_REQUEST['ccc'] : 0;

        $bussId = !empty($_REQUEST['buss_id']) ? $_REQUEST['buss_id'] : 0;
        $userId = $this->userId;
        $roomTypeMod = &m('roomType');
        $langData=array(
            $this->langData->public->more,
            $this->langData->project->choice_article,
            $this->langData->project->recommend,
            $this->langData->project->more_details,
            $this->langData->project->time_limit,
            $this->langData->project->recommend_for_you,
            $this->langData->project->nearby_merchants,
            $this->langData->project->distance_lately,
           $this->langData->project->business_type,
        );
        //获取推荐商品
        $storeGoodsMod   = &m('areaGood');
        $recommendGoods  = $storeGoodsMod -> getGoodsList(array(
            'store_id'  =>  $this->store_id,
            'lang_id'   =>  $this->lang_id,
            'is_recom'  =>  1,   //推荐
            'limit'     =>  4    //4条记录
        )); 

        $articleData=$this->getArticle();
        if($bussId){
            $bannerData = $this->getBusinessBanner($bussId);
        }else{
            $bannerData=$this->getBanner();
        }

        $bussSql = "SELECT buss_id FROM " . DB_PREFIX . 'store_business WHERE  store_id=' . $this->store_id;
        $bussData = $roomTypeMod->querySql($bussSql);
        foreach ($bussData as $key => $val) {
            $buId[] = $val['buss_id'];
        }
        $buIds = implode(',', $buId);
        $naichaData=$this->getBusiness($this->lang_id,1,$buIds);
        $businessData=$this->getBusiness($this->lang_id,0,$buIds);
        foreach($businessData as $key=>$val){
            $where = '    where  t.superior_id=' . $val['id'] . ' and  l.`lang_id`  = ' . $this->lang_id;
            $sql = 'SELECT  t.`id`  FROM  ' . DB_PREFIX . 'room_type AS t
                 LEFT JOIN  ' . DB_PREFIX . 'room_type_lang  AS  l  ON  t.`id` = l.`type_id`  ' . $where . ' order by t.sort';
            $data = $roomTypeMod->querySql($sql);
            foreach($data as $key1=>$val1){
                $rid[] = $val1['id'];

            }
            $rtid=implode(',',$rid);
            $rid=array();
            $businessData[$key]['rtid']=$rtid;
            }
            $youhuiData=$this->getGoodsDiscount();
            $recommYou = $this->recommendForYou1($this->store_id);
            $pageData=array(
                'langData'=>$langData,
                'recommendGoodsData'=>$recommendGoods,
                'articleData'=>$articleData,
                'bannerData'=>$bannerData,
                'naichaData'=>$naichaData,
                'businessData'=>$businessData,
                'youhuiData'=>$youhuiData,
                'recommYou'=>$recommYou,
                'store_id'=>$this->store_id,
                'lang_id'=>$this->lang_id
            );
            $this->setData($pageData,'1','');

    }


    public function recommendForYou1($storeid, $orderBy, $limit = 0) {
        $ctgMod = &m('goodsClass');
        $userId = $this->userId;
        $sql = "select  `id`   from " . DB_PREFIX . "goods_category ";
        $res = $ctgMod->querySql($sql);
        $cid = array();
        foreach ($res as $val) {
            $cid[] = $val['id'];
        }
        $cids = implode(',', $cid);
        //

        $storeGoodsMod = &m('areaGood');
        if ($limit == 0) {
            $where = '  WHERE   s.cat_id  in (' . $cids . ')  and  s.mark =1  AND  s.is_on_sale =1  AND s.store_id = ' . $storeid . ' AND l.`lang_id` =' . $this->lang_id . ' AND s.is_recom=1';
            $sql2 = 'SELECT s.id,s.`goods_id`,s.`cat_id`,s.`store_id`,l.`goods_name`,l.`lang_id`,s.`shop_price`,s.`market_price`,s.`brand_id`,slg.`original_img`  FROM  '
                . DB_PREFIX . 'store_goods AS s LEFT JOIN  '
                . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id` LEFT JOIN  '
                . DB_PREFIX . 'goods AS slg ON s.`goods_id` = slg.`goods_id`' . $where;
            $sql2 .= '  ORDER  BY  s.goods_salenum  desc ,  s.id desc   LIMIT 4';
        } else {
            $where = '  WHERE   s.cat_id  in (' . $cids . ')  and  s.mark =1  AND  s.is_on_sale =1  AND s.store_id = ' . $storeid . ' AND l.`lang_id` =' . $this->lang_id . ' AND s.is_recommend=1';
            $sql2 = 'SELECT s.id,s.`goods_id`,s.`cat_id`,s.`store_id`,l.`goods_name`,l.`lang_id`,s.`shop_price`,s.`market_price`,s.`brand_id`,slg.`original_img` FROM  '
                . DB_PREFIX . 'store_goods AS s LEFT JOIN  '
                . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id` LEFT JOIN  '
                . DB_PREFIX . 'goods AS slg ON s.`goods_id` = slg.`goods_id`' . $where;
            $sql2 .= $orderBy;
        }

        $arr = $storeGoodsMod->querySql($sql2);


        foreach ($arr as $key => $val) {
            //店铺商品打折
            $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $this->store_id;
            $store_arr = $storeGoodsMod->querySql($store_sql);
            $arr[$key]['shop_price'] = number_format($val['shop_price'] * $store_arr[0]['store_discount'], 2);
            //为你推荐的收藏商品
            $sql_collection = 'select * from ' . DB_PREFIX . 'user_collection where user_id=' . $userId . ' and store_id=' . $storeid;
            $data_collection = $storeGoodsMod->querySql($sql_collection);
            foreach ($data_collection as &$collertion) {
                if ($collertion['store_good_id'] == $val['id']) {
                    $arr[$key]['type'] = 1;
                }
            }
        }
        return $arr;
    }
    /**
     * 附近店铺接口
     * @author gao
     * @date 2018-08-15
     */
    public function getStore(){
        $latlon = !empty($_REQUEST['latlon']) ? $_REQUEST['latlon']:'31.99226,118.7787';
        $test = !empty($_REQUEST['test']) ? $_REQUEST['test']:0;
        $latlon=explode(',',$latlon);
        $lng = $latlon[1]; //经度
        $lat = $latlon[0]; //纬度
        $latlon=$this->coordinate_switchf($lat,$lng);
        $lng=$latlon['Longitude'];
        $lat=$latlon['Latitude'];
        $storeMod = &m('store');
        $bussId = !empty($_REQUEST['bussId']) ? $_REQUEST['bussId'] : "";

        if (!empty($bussId)) {
            $bSql = "SELECT store_id FROM " . DB_PREFIX . 'store_business WHERE buss_id in (' . $bussId. ')';
            $bData = $storeMod->querySql($bSql);
            foreach ($bData as $k => $v) {
                $sId[] = $v['store_id'];
            }
            $sIds = implode(',', array_unique($sId));
            $swhere = " where sl.distinguish=0 AND s.id in (" . $sIds . ')';
        } else {
            $swhere = " where sl.distinguish=0  ";
        }
 
        if(!in_array($this->userId,array(31076,1264,18918,19103,30419,11826))){ 
	 $swhere .= ' AND s.id != 98 ';
        }

        if (empty($this->userId)) {
            $swhere .= ' AND s.store_type in (2,3) AND s.is_open=1 ';
            $ssql = 'SELECT  s.id,s.logo,s.longitude,s.latitude,s.distance,sl.`store_name` AS sltore_name,s.store_start_time,s.store_end_time,s.store_notice,s.store_mobile FROM  ' . DB_PREFIX . 'store AS s LEFT JOIN  ' . DB_PREFIX . 'store_lang AS sl ON sl.`store_id` = s.`id` and  sl.lang_id=' . $this->lang_id . ' LEFT JOIN  ' . DB_PREFIX . 'language AS l ON s.`lang_id` = l.`id` LEFT JOIN  ' . DB_PREFIX . 'currency AS c ON s.`currency_id` = c.`id`' . $swhere;
            $sData = $storeMod->querySql($ssql);
        } else {
            $swhere .= ' AND s.store_type in (2,3) AND s.is_open=1';
            $ssql = 'SELECT  s.id,s.logo,s.longitude,s.latitude,s.distance,sl.`store_name` AS sltore_name,s.store_start_time,s.store_end_time,s.store_notice,s.store_mobile FROM  ' . DB_PREFIX . 'store AS s LEFT JOIN  ' . DB_PREFIX . 'store_lang AS sl ON sl.`store_id` = s.`id` and  sl.lang_id=' . $this->lang_id . ' LEFT JOIN  ' . DB_PREFIX . 'language AS l ON s.`lang_id` = l.`id` LEFT JOIN  ' . DB_PREFIX . 'currency AS c ON s.`currency_id` = c.`id`' . $swhere;
            $sData = $storeMod->querySql($ssql);
        }
        foreach ($sData as $key => $val) {
            $s = $this->getdistance($lng, $lat, $val['longitude'], $val['latitude']);
            $distance = number_format(($s / 1000), 2, '.', '');
            $sData[$key]['dis'] = $distance;
            $sData[$key]['logo'] = '/web/uploads/small/'.$val['logo'];
            $oSql = "SELECT count(*) as total FROM " . DB_PREFIX . 'order WHERE store_id=' . $val['id'];
            $oData = $storeMod->querySql($oSql);
            $order_num = $oData[0]['total'];
            $storeSql = 'select store_id from ' . DB_PREFIX . 'user_store where user_id=' . $this->userId;
            $data_store = $storeMod->querySql($storeSql);
            foreach ($data_store as $k1 => $v1) {
                if ($val['id'] == $v1['store_id']) {
                    $sData[$key]['type'] = 1;
                }
            }
            if (empty($order_num)) {
                $sData[$key]['order_num'] = 0;
            } else {
                $sData[$key]['order_num'] = $order_num;
            }
            $busSql = "SELECT buss_id FROM " . DB_PREFIX . 'store_business WHERE  store_id=' . $val['id'];
            $busData = $storeMod->querySql($busSql);
            $sData[$key]['b_id'] = $busData[0]['buss_id'];
        }
        $sort = array(
            'direction' => 'SORT_ASC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
            'field' => 'dis', //排序字段
        );
        $arrSort = array();
        foreach ($sData AS $uniqid => $row) {
            foreach ($row AS $key => $value) {
                $arrSort[$key][$uniqid] = $value;
            }
        }
        if ($sort['direction']) {
            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $sData);
        }
        $this->setData($sData, 1, '');
    }

    //转化距离
    function getdistance($lng1, $lat1, $lng2, $lat2) {
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
     * 轮播广告接口
     * @author gao
     * @date 2018-08-15
     */
    public function getBanner() {
        $advMod = &m('adv');
        $where = '   where 1=1  and  p.`position_num`  = 110000    and  a.store_id=' . $this->store_id;
        $sql = 'SELECT   a.ad_code,a.goods_id FROM  ' . DB_PREFIX . 'ad  AS a
                LEFT JOIN  ' . DB_PREFIX . 'ad_position  AS p  ON a.`ps_id` = p.`position_id` ' . $where;
        $res = $advMod->querySql($sql);
        return $res;
    }

    /**
     * 获取首页业务类型banner图
     * @author fup
     * @date 2019-08-01
     */
    public function getBusinessBanner($bussId){
        $roomTypeMod = &m('roomType');
        $data = $roomTypeMod->getOne(array('cond'=>'id = '.$bussId,'fields'=>'room_adv_imgs'));
        $arr = array();
        if($data && !empty($data['room_adv_imgs'])){
            $res = explode(',',$data['room_adv_imgs']);
            foreach ($res as $v){
                $arr[] = array('ad_code'=>$v,'goods_id'=>0);
            }
            return $arr;
        }
        return array();
    }

    /**
     * 首页文章接口
     * @author gao
     * @date 2018-08-14
     */
    public function getArticle() {
        $article = &m('article'); //文章
        $articlesql = 'SELECT al.title,a.id FROM ' . DB_PREFIX . 'article AS a LEFT JOIN ' . DB_PREFIX . 'article_lang AS al ON a.id=al.article_id where
            a.store_id =' . $this->store_id . ' AND al.lang_id=' . $this->lang_id . ' AND a.isrecom = 1 order by a.add_time desc ';
        $articledata = $article->querySql($articlesql);
        return $articledata;
    }


    /**
     * 业务接口
     * @author gao
     * @date 2018-08-15
     */

    public function getBusiness($langid,$type ,$buIds) {

        $roomTypeMod = &m('roomType');
        if (!empty($langid)) {
            if (!empty($buIds)) {
                $where = '    where  t.superior_id=0 and  l.`lang_id`  = ' . $langid . ' and t.id in(' . $buIds . ')';
            } else {
                $where = '    where  t.superior_id=0 and  l.`lang_id`  = ' . $langid;
            }
        } else {
            if (!empty($buIds)) {
                $where = '    where  t.superior_id=0 and  l.`lang_id`  = ' . $this->lang_id . ' and t.id in(' . $buIds . ')';
            } else {
                $where = '    where  t.superior_id=0 and  l.`lang_id`  = ' . $this->lang_id;
            }
        }
        if($langid==$this->lang_id){
            if($type==1){
                $sql = 'SELECT  t.`id`,l.`type_name`,t.`room_img`  FROM  ' . DB_PREFIX . 'room_type AS t
                 LEFT JOIN  ' . DB_PREFIX . 'room_type_lang  AS  l  ON  t.`id` = l.`type_id`  ' . $where . ' and l.type_name like "%奶茶%"  order by t.sort';
            }else{
                $sql = 'SELECT  t.`id`,l.`type_name`,t.`room_img`  FROM  ' . DB_PREFIX . 'room_type AS t
                 LEFT JOIN  ' . DB_PREFIX . 'room_type_lang  AS  l  ON  t.`id` = l.`type_id`  ' . $where . ' and l.type_name  not like "%奶茶%"  order by t.sort';
            }

        }else{
            if($type==1){
                $sql = 'SELECT  t.`id`,l.`type_name`,t.`room_img`  FROM  ' . DB_PREFIX . 'room_type AS t
                 LEFT JOIN  ' . DB_PREFIX . 'room_type_lang  AS  l  ON  t.`id` = l.`type_id`  ' . $where . ' and  l.type_name like "%Tea%"  order by t.sort';
            }else{
                $sql = 'SELECT  t.`id`,l.`type_name`,t.`room_img`  FROM  ' . DB_PREFIX . 'room_type AS t
                 LEFT JOIN  ' . DB_PREFIX . 'room_type_lang  AS  l  ON  t.`id` = l.`type_id`  ' . $where . ' and  l.type_name not like "%Tea%"  order by t.sort';
            }

        }

        $data = $roomTypeMod->querySql($sql);
        return $data;
    }



    /**
     * 优惠商品
     * @author gao
     * @date 2018-08-15
     */
    public function getGoodsDiscount() {
        $lang_id = $this->lang_id;
        //取四种优惠商品
        $now = time();
        $combinedMod = &m('combinedSale'); //组合销售
        $promSaleMod = &m('goodProm'); //商品促销
        $groupMod = &m('groupbuy'); //团购
        $skillMod = &m('spikeActivity'); //秒杀
        $prom_field = "sgl.goods_id,sg.id as store_goods_id,s.id as prom_id,sgl.original_img as goods_img,s.prom_name,g.goods_name,g.discount_rate as prom_rate,s.end_time,goods_price as o_price,discount_price as price,g.goods_key";
        $prom_sql = "select " . $prom_field . " from "
            . DB_PREFIX . "promotion_sale as s left join "
            . DB_PREFIX . "promotion_goods as g on s.id=g.prom_id left join  "
            . DB_PREFIX . "store_goods as sg on g.goods_id = sg.id left join  "
            . DB_PREFIX . "goods as sgl on sg.goods_id = sgl.goods_id  where s.store_id=" . $this->store_id . " and s.status=2 and sg.mark=1 and sg.is_on_sale=1 and s.mark=1 and s.start_time<=" . $now . " and s.end_time>=" . $now;
        $prom_arr = $promSaleMod->querySql($prom_sql);


        if ($prom_arr) {
            $prom_lang = $this->getGoodsLang($prom_arr[0]['goods_id'], $lang_id);
            if ($prom_lang) {
                $prom_arr[0]['goods_name'] = $prom_lang['goods_name'];
            }
//            $prom_arr[0]['url'] = "?app=goods&act=goodInfo&storeid={$this->store_id}&lang={$lang_id}&auxiliary=0&source=3&cid=" . $prom_arr[0]['prom_id'] . "&gid=" . $prom_arr[0]['goods_id']."&key=".$prom_arr[0]['goods_key'];
            $prom_arr[0]['source'] = 3;
        }
        $sqle = "SELECT  sgl.goods_id,sg.id as store_goods_id,c.id as prom_id,sgl.original_img as goods_img,c.goods_name,c.rebate as prom_rate,c.end_time,c.goods_id,c.goods_price as o_price,c.group_goods_price as price  FROM  "
            . DB_PREFIX . "goods_group_buy  as c left join  "
            . DB_PREFIX . "store_goods as sg on c.goods_id = sg.id left join  "
            . DB_PREFIX . "goods as sgl on sg.goods_id = sgl.goods_id  where c.store_id=" . $this->store_id . " and c.start_time<=" . $now . " and c.end_time>=" . $now . " and c.is_end=1 and c.mark=1 and sg.mark=1 and sg.is_on_sale=1";
        $group_arr = $groupMod->querySql($sqle);
        if ($group_arr) {
            $group_lang = $this->getGoodsLang($group_arr[0]['goods_id'], $lang_id);
            if ($group_lang) {
                $group_arr[0]['goods_name'] = $group_lang['goods_name'];
            }
//            $group_arr[0]['url'] = "?app=goods&act=goodInfo&storeid={$this->store_id}&lang={$lang_id}&auxiliary=0&source=2&cid=" . $group_arr[0]['prom_id'] . "&gid=" . $group_arr[0]['goods_id'];
            $group_arr[0]['source'] = 2;
        }$skill_field = "SELECT sgl.goods_id,c.id as prom_id,c.name as prom_name,c.goods_name,c.discount as prom_rate,c.end_time,sgl.original_img as goods_img,c.store_goods_id,c.price ,c.o_price FROM  "
            . DB_PREFIX . "spike_activity  as c left join  "
            . DB_PREFIX . "store_goods as sg on c.store_goods_id = sg.id left join  "
            . DB_PREFIX . "goods as sgl on sg.goods_id = sgl.goods_id where c.store_id=" . $this->store_id . " and c.start_time<=" . $now . " and sg.mark=1 and sg.is_on_sale=1 and c.end_time>=" . $now;
        $skill_arr = $skillMod->querySql($skill_field);
        if ($skill_arr) {
            $skill_lang = $this->getGoodsLang($skill_arr[0]['store_goods_id'], $lang_id);
            if ($skill_lang) {
                $skill_arr[0]['goods_name'] = $skill_lang['goods_name'];
            }
//            $skill_arr[0]['url'] = "?app=goods&act=goodInfo&storeid={$this->store_id}&lang={$lang_id}&auxiliary=0&source=1&cid=" . $skill_arr[0]['prom_id'] . "&gid=" . $skill_arr[0]['store_goods_id'];
            $skill_arr[0]['source'] = 1;
        }

        $arr[] = $prom_arr[0];
        $arr[] = $group_arr[0];
        $arr[] = $skill_arr[0];
        $arr1=array_filter($arr);
        $arr2 = array_values($arr1);
//        $arr=array_diff($arr,array(NULL));
        return $arr2;
    }

    //获取商品
    public function getGoodsLang($store_goods_id, $lang_id) {
        $areaGMod = &m('areaGood');
        $where = " where sg.id=" . $store_goods_id . " and gl.lang_id=" . $lang_id;
        $sql = "select gl.goods_name from " . DB_PREFIX . "store_goods as sg left join " . DB_PREFIX . "goods as g on sg.goods_id=g.goods_id
              left join " . DB_PREFIX . "goods_lang as gl on g.goods_id=gl.goods_id
              " . $where;
        $res = $areaGMod->querySql($sql);
        return $res[0];
    }


    /**
     * 推荐商品接口
     * @author gao
     * @date 2018-08-14
     */

    public function recommendForYou(){
        $ctgMod = &m('goodsClass');
        $sql = "select  `id`   from " . DB_PREFIX . "goods_category ";
        $res = $ctgMod->querySql($sql);
        $userId=359;
        $cid = array();
        foreach ($res as $val) {
            $cid[] = $val['id'];
        }
        $cids = implode(',', $cid);
        $storeGoodsMod = &m('areaGood');
        $where = '  WHERE   s.cat_id  in (' . $cids . ')  and  s.mark =1  AND  s.is_on_sale =1  AND s.store_id = ' . $this->store_id . ' AND l.`lang_id` =' . $this->lang_id . ' AND s.is_recom=1';
        $sql2 = 'SELECT s.id,s.`goods_id`,s.`cat_id`,s.`store_id`,l.`goods_name`,s.`shop_price`,s.`market_price`,slg.`original_img`  FROM  '
            . DB_PREFIX . 'store_goods AS s LEFT JOIN  '
            . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id` LEFT JOIN  '
            . DB_PREFIX . 'goods AS slg ON s.`goods_id` = slg.`goods_id`' . $where;
        $sql2 .= '  ORDER  BY  s.id desc   LIMIT 4';
        $arr = $storeGoodsMod->querySql($sql2);
        foreach ($arr as $key => $val) {
            //店铺商品打折
            $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id='.$this->store_id  ;
            $store_arr = $storeGoodsMod->querySql($store_sql);
            $arr[$key]['shop_price'] = number_format($val['shop_price'] * $store_arr[0]['store_discount'],2);
            //为你推荐的收藏商品
            $sql_collection = 'select * from ' . DB_PREFIX . 'user_collection where user_id=' . $userId . ' and store_id='.$this->store_id ;
            $data_collection = $storeGoodsMod->querySql($sql_collection);
            foreach ($data_collection as &$collertion) {
                if ($collertion['store_good_id'] == $val['id']) {
                    $arr[$key]['type'] = 1;
                }
            }
        }
        if($arr) {
            $this->setData($arr, 1, '');
        }
    }

    /**
     * 商品列表接口
     * @author gao
     * @date 2018-08-14
     */
    public function goodsList(){
        //加载语言包
        $this->load($this->shorthand, 'goods/goods');
        $recom = !empty($_REQUEST['recom']) ? $_REQUEST['recom'] : 1;
        $filter_param = array(); //筛选数组
        $style = !empty($_REQUEST['s']) ? $_REQUEST['s'] : '';  //风格
        $rtid = !empty($_REQUEST['rtid']) ? $_REQUEST['rtid'] : ''; //业务
        $sort = !empty($_REQUEST['by']) ? $_REQUEST['by'] : 4; // 排序
        $brand = !empty($_REQUEST['b']) ? $_REQUEST['b'] : '';  //品牌
        $start_price = !empty($_REQUEST['start_price']) ? trim($_REQUEST['start_price']) : 0;
        $end_price = !empty($_REQUEST['end_price']) ? trim($_REQUEST['end_price']) : 0;
        $start_price = abs(intval($_REQUEST['start_price']));
        $end_price = abs(intval($_REQUEST['end_price']));
        if ($start_price != 0 || $end_price != 0) {
            if ($end_price < $start_price) {
                $price = $end_price . '_' . $start_price;
            } else {
                $price = $start_price . '_' . $end_price;
            }
        }

        //加入筛选条件
        if (!empty($style)) {
            $filter_param['s'] = $style;
        }
        if (!empty($rtid)) {
            $filter_param['rtid'] = $rtid;
        }
        if (!empty($sort)) {
            $filter_param['by'] = $sort;
        }
        if (!empty($brand)) {
            $filter_param['b'] = $brand;
        }
        if (!empty($price)) {
            $filter_param['pr'] = $price;
        }
        //业务下的分类
        $roomCtg = $this->getRoomCtg($rtid, $this->lang_id);
        $cidArr = array();
        foreach ($roomCtg as $val) {
            $cidArr[] = $val['cid'];
        }
        $cids = implode(',', $cidArr);
        //业务类型下的商品
        $goodsList = $this->getCtgGoods($cids, $this->store_id, $this->lang_id, $filter_param, $recom);
        if ($goodsList) {
            $this->setData($goodsList, '1', '');
        }
    }




    //业务分类
    public function getRoomCtg($rtid, $langid) {
        $roomCtgMod = &m('roomTypeCate');
        $where = '    where  cl.`lang_id`  = ' . $langid;
        $sql = 'SELECT  rc.`room_type_id`  as rtid ,rc.`category_id`  as cid  ,cl.`category_name`  as cname,c.`image`   FROM   ' . DB_PREFIX . 'room_category  AS rc
              LEFT JOIN  ' . DB_PREFIX . 'room_type as t ON  rc.`room_type_id` =t.`id`
              LEFT  JOIN   ' . DB_PREFIX . 'goods_category_lang   AS cl   ON rc.`category_id`  = cl.`category_id`
              LEFT JOIN  ' . DB_PREFIX . 'goods_category  AS c ON c.`id` = rc.`category_id`  ' . $where . '  AND  rc.`room_type_id` = ' . $rtid . " order by rc.sort";
        $data = $roomCtgMod->querySql($sql);
        return $data;
    }




    /**
         * 获取业务类型下的商品
         * @author wangh
         * @date 2017/09/13
         */
        public function getCtgGoods($cids, $storeid, $lang, $filter_param, $recom) {
            $storeGoodsMod = &m('areaGood');
            $by = !empty($filter_param['by']) ? $filter_param['by'] : 1;
            $rtid = !empty($filter_param['rtid']) ? $filter_param['rtid'] : '';
            $brandFilter = !empty($filter_param['b']) ? explode('_', $filter_param['b']) : array();
            $styleFilter = !empty($filter_param['s']) ? explode('_', $filter_param['s']) : array();
            $priceFilter = !empty($filter_param['pr']) ? $filter_param['pr'] : array();

            if ($by == 1) {
                $orderBy = '  order by  s.shop_price  asc ';
            } else if ($by == 2) {
                $orderBy = '  order by  s.shop_price  desc ';
            } else if ($by == 3) {
                $orderBy = '  order by  s.add_time  desc  , s.id desc';
            } else if ($by == 4) {
                $orderBy = '  order by  rc.sort asc , s.goods_id desc';
            } else if ($by == 5) {
                $orderBy = '  order by  rc.sort asc , s.goods_id desc';
            }
            if ($cids) {
                //获取分类下的商品id
                $where = '  where   s.mark=1  and  s.store_id =' . $storeid . '  and   r.category_id  in(' . $cids . ')  and r.room_type_id = ' . $rtid . '  and  s.is_on_sale =1 order by r.sort';
                $sql = 'SELECT s.id,r.sort FROM  ' . DB_PREFIX . 'room_category AS r  LEFT JOIN ' . DB_PREFIX . 'store_goods as s ON s.cat_id=r.category_id' . $where;
                $dataC = $storeGoodsMod->querySql($sql);
                $goodsId = $this->getYiweiArr($dataC);
            } else {
                $goodsId = array();
            }
            //品牌
            if (!empty($brandFilter)) {
                $brandids = implode(',', $brandFilter);
                $sql = 'SELECT  id   FROM   ' . DB_PREFIX . 'store_goods  WHERE   mark=1  and   store_id =' . $storeid . '   and  brand_id in(' . $brandids . ')';
                $dataB = $storeGoodsMod->querySql($sql);
                $arrB = $this->getYiweiArr($dataB);
                $goodsId = array_intersect($goodsId, $arrB);
            }
            //风格
            if (!empty($styleFilter)) {
                $styleids = implode(',', $styleFilter);
                $sql = 'SELECT  id   FROM   ' . DB_PREFIX . 'store_goods  WHERE   mark=1  and   store_id =' . $storeid . '   and  style_id in(' . $styleids . ')';
                $dataS = $storeGoodsMod->querySql($sql);
                $arrS = $this->getYiweiArr($dataS);
                $goodsId = array_intersect($goodsId, $arrS);
            }
            //价格
            if (!empty($priceFilter)) {
                $arr2 = array();
                $arr1 = explode('-', $priceFilter);
                foreach ($arr1 as $key => $val) {
                    $arr1[$key] = explode('_', $val);
                }

                foreach ($arr1 as $val) {
                    if (in_array('*', $val)) {
                        $arr2[] = '  and  shop_price  >=' . $val[0];
                    } else {
                        $arr2[] = '  and  shop_price  >=' . $val[0] . '  and  shop_price <= ' . $val[1];
                    }
                }

                $res = array();
                foreach ($arr2 as $v) {
                    $sql = 'SELECT  id   FROM   ' . DB_PREFIX . 'store_goods  WHERE   mark=1  and  store_id =' . $storeid . $v;
                    $dataP = $storeGoodsMod->querySql($sql);
                    $res = array_merge($res, $dataP);
                }
                $dataPr = $this->getYiweiArr($res);
                $goodsId = array_intersect($goodsId, $dataPr);
            }

            $gids = implode(',', $goodsId);
            //所以子类
            if ($by == 5) {
                $this->load($this->shorthand, 'listpage/listpage');
                $a = $this->langData;

                $storeGoodsMod = &m('areaGood');
                //秒杀商品
                $seckMod = &m('spikeActivity');
                $goodsByMod = &m('groupbuy');
                $goodPromMod = &m('goodProm');
                $promotionMod = &m('goodPromDetail');
                $cartMod = &m('cart');
                $curtime = time();
                $today = strtotime(date('Y-m-d', time()));
                $now = $curtime - $today;
                $where1 = 'WHERE s.store_id =' . $storeid . '  and  ' . $curtime . ' > s.start_time and g.mark=1 and g.is_on_sale=1  ';
                $sql1 = 'SELECT  s.id as cid,s.`name`,s.start_time,s.end_time,s.start_our,s.end_our,s.store_id,s.store_goods_id as id,gl.original_img,s.content,s.goods_name,s.item_name,s.item_key,s.discount,s.o_price,s.price,s.goods_num,g.is_free_shipping FROM  '
                    . DB_PREFIX . 'spike_activity as s left join '
                    . DB_PREFIX . 'store_goods as g on  s.store_goods_id = g.id  left join '
                    . DB_PREFIX . 'goods as gl on  g.goods_id = gl.goods_id ' . $where1;
                $spikeArr = $seckMod->querySql($sql1);

                foreach ($spikeArr as $k => $item) {
                    if (($curtime > $item['start_time']) && ($curtime < $item['end_time'])) {
                        if (($now >= $item['start_our']) && ($now <= $item['end_our'])) {
                            $spikeArr[$k]['in_time'] = 2;
                        } else {
                            $spikeArr[$k]['in_time'] = 1;
                        }
                    } else {
                        $spikeArr[$k]['in_time'] = 3;
                    }


                    //翻译处理
                    $child_info = $storeGoodsMod->getLangInfo($item['id'], $this->lang_id);

                    if ($child_info) {
                        $k_name = $child_info['goods_name'];
                        $spikeArr[$k]['goods_name'] = $k_name;
                    }
                }



                foreach ($spikeArr as &$item) {
                    if ($item['shipping_price'] == '') {
                        $item['shipping_price'] = '0.00';
                    }

                    $item['preferential'] = $a['spike'];
                    $item['source'] = 1; //优惠商品标记
                    //是否包邮
                    switch ($item['is_free_shipping']) {
                        case 1:
                            $item['isfree'] = $a['Package'];  // Package mail
                            break;
                        case 2:
                            $item['isfree'] = $a['pack']; //Don't pack mail
                            break;
                        default:
                            $item['isfree'] = $a['pack'];
                    }
                }


                //团购商品
                $where2 = '  where  b.store_id = ' . $this->store_id . ' and  b.mark =1 and g.mark=1 and g.is_on_sale=1';
                $sql2 = 'SELECT  b.id,b.goods_id,b.store_id,b.start_time,b.end_time,b.group_goods_price,b.virtual_num,l.original_img,b.goods_price,b.goods_name  FROM  '
                    . DB_PREFIX . 'goods_group_buy  AS b  LEFT JOIN  '
                    . DB_PREFIX . 'store_goods AS g ON b.`goods_id` = g.id  LEFT JOIN '
                    . DB_PREFIX . 'goods AS l ON g.`goods_id` = l.`goods_id` ' . $where2;
                $data = $goodsByMod->querySql($sql2);

                foreach ($data as $k => $val) {
                    //活动的状态的更改
                    if ($curtime < $val['start_time']) {
                        //未开始
                        $goodsByMod->doEdit($val['id'], array('is_end' => 3));
                    } else if (( $curtime > $val['start_time'] ) && ( $curtime < $val['end_time'] )) {
                        //进行中
                        $goodsByMod->doEdit($val['id'], array('is_end' => 1));
                    } else if ($curtime > $val['end_time']) {
                        //结束
                        $goodsByMod->doEdit($val['id'], array('is_end' => 2));
                    }
                }

                $where3 = 'WHERE  l.`lang_id` = ' . $this->lang_id . '  and  b.store_id =' . $this->store_id . '  AND b.is_end =1 AND b.mark = 1 and g.mark=1 and g.is_on_sale=1 ';
                $sql3 = 'SELECT  b.id as cid,b.goods_id as id,b.store_id,b.end_time,b.group_goods_price as price,b.virtual_num,gsl.original_img,b.goods_price as o_price,l.`goods_name`,b.goods_spec_key as item_key  FROM  '
                    . DB_PREFIX . 'goods_group_buy   AS b  LEFT JOIN  '
                    . DB_PREFIX . 'store_goods AS g ON b.`goods_id` = g.id LEFT JOIN  '
                    . DB_PREFIX . 'goods_lang AS l ON g.`goods_id` = l.`goods_id`  LEFT JOIN  '
                    . DB_PREFIX . 'goods AS gsl ON g.`goods_id` = gsl.`goods_id` ' . $where3;
                $groupByGoodArr = $goodsByMod->querySql($sql3);
                foreach ($groupByGoodArr as &$item) {
                    $item['preferential'] = $a['groupBuy'];
                    $item['source'] = 2; //优惠商品标记
                    //是否包邮
                    switch ($item['is_free_shipping']) {
                        case 1:
                            $item['isfree'] = $a['Package'];  // Package mail
                            break;
                        case 2:
                            $item['isfree'] = $a['pack']; //Don't pack mail
                            break;
                        default:
                            $item['isfree'] = $a['pack'];
                    }
                }
                //促销商品
                $this->checkOver();
                // 获取正在进行或者未开始的促销活动
                $sql4 = " select ps.id as cid,ps.*,pg.goods_id as id,pg.goods_key as item_key,pg.goods_key_name,pg.goods_name,sgl.original_img,pg.goods_price as o_price,pg.discount_price as price,s.is_free_shipping from "
                    . DB_PREFIX . "promotion_sale as ps left join "
                    . DB_PREFIX . "promotion_goods as pg on  ps.id = pg.prom_id left join "
                    . DB_PREFIX . "store_goods as s on pg.goods_id = s.id  left join "
                    . DB_PREFIX . "goods as sgl on s.goods_id = sgl.goods_id  where ps.`store_id` = $this->store_id and ps.`status` in (1,2) and s.mark=1 and s.is_on_sale =1 and ps.`mark` =1 order by ps.status desc,ps.id desc";
                $promotionGoodsArr = $goodPromMod->querySql($sql4);

                foreach ($promotionGoodsArr as &$item) {
                    $item['goods_name']= $cartMod->getGoodNameById($item['id'], $this->lang_id);


                    $item['preferential'] = $a['promotion'];
                    $item['source'] = 3; //优惠商品标记
                    //是否包邮
                    switch ($item['is_free_shipping']) {
                        case 1:
                            $item['isfree'] = $a['Package'];  // Package mail
                            break;
                        case 2:
                            $item['isfree'] = $a['pack']; //Don't pack mail
                            break;
                        default:
                            $item['isfree'] = $a['pack'];
                    }
                }
                $res = array();
                $res['data'] = array_merge( $groupByGoodArr, $promotionGoodsArr,$spikeArr);
                return $res;
            } else {

                if ($recom == 1) {
                    if ($by == 1) {
                        $orderBy = '  order by  s.shop_price  asc ';
                    } else if ($by == 2) {
                        $orderBy = '  order by  s.shop_price  desc ';
                    } else if ($by == 3) {
                        $orderBy = '  order by  s.add_time  desc , s.id desc';
                    } else if ($by == 4) {
                        $orderBy = '  order by   s.goods_id desc';
                    } else if ($by == 5) {
                        $orderBy = '  order by  s.goods_id desc';
                    }

                    $recomData = $this->recommendForYou($storeid, $orderBy, 1);
                    $this->load($this->shorthand, 'listpage/listpage');
                    $a = $this->langData;
                    foreach ($recomData as &$item) {
                        //是否包邮
                        switch ($item['is_free_shipping']) {
                            case 1:
                                $item['isfree'] = $a['Package'];  // Package mail
                                break;
                            case 2:
                                $item['isfree'] = $a['pack']; //Don't pack mail
                                break;
                            default:
                                $item['isfree'] = $a['pack'];
                        }
                    }

                    //组装数据
                    $res = array();
                    $res['data'] = $recomData;
                    return $res;
                } else {
                    $where = '  where   s.store_id =' . $storeid . '  and    s.id  in(' . $gids . ')  and rc.room_type_id = ' . $rtid . '  and   s.mark=1   and   s.is_on_sale =1  AND l.`lang_id` = ' . $this->lang_id;
                    //所以子类的商品
                    $sql = 'SELECT s.id,rc.sort,s.`goods_id`,s.`cat_id`,s.`store_id`,l.`goods_name`,l.`lang_id`,s.`shop_price`,s.`market_price`,s.`brand_id`,sgl.`original_img`,s.is_free_shipping,s.add_time FROM  '
                        . DB_PREFIX . 'store_goods AS s LEFT JOIN  '
                        . DB_PREFIX . 'room_category AS rc on s.cat_id=rc.category_id LEFT JOIN '
                        . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id` LEFT JOIN '
                        . DB_PREFIX . 'goods AS l ON sgl.`goods_id` = sgl.`goods_id`' . $where . $orderBy;
                    $arr = $storeGoodsMod->querySql($sql);
                    //加载语言包
                    $this->load($this->shorthand, 'listpage/listpage');
                    $a = $this->langData;
                    foreach ($arr as &$item) {
                        //是否包邮
                        switch ($item['is_free_shipping']) {
                            case 1:
                                $item['isfree'] = $a['Package'];  // Package mail
                                break;
                            case 2:
                                $item['isfree'] = $a['pack']; //Don't pack mail
                                break;
                            default:
                                $item['isfree'] = $a['pack'];
                        }
                    }

                    //组装数据
                    $res = array();
                    $res['data'] = $arr;
                    return $res;
                }
            }
        }

        //首页更多店铺接口
        public  function getMoreStore(){
                $storeMod = &m('store');
                $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
                $swhere = " where sl.distinguish=0";
                $latlon = explode(',',$latlon);
                $lng=$latlon[0];//经度
                $lat=$latlon[1];//纬度
                if (empty($this->userId)) {
                    $swhere .= ' AND s.store_type in (2,3) AND s.is_open=1 ';
                    $ssql = 'SELECT  s.id,s.logo,s.longitude,s.latitude,s.distance,sl.`store_name` AS sltore_name FROM  ' . DB_PREFIX . 'store AS s LEFT JOIN  ' . DB_PREFIX . 'store_lang AS sl ON sl.`store_id` = s.`id` and  sl.lang_id=' . $this->lang_id . ' LEFT JOIN  ' . DB_PREFIX . 'language AS l ON s.`lang_id` = l.`id` LEFT JOIN  ' . DB_PREFIX . 'currency AS c ON s.`currency_id` = c.`id`' . $swhere;
                    $sData = $storeMod->querySql($ssql);
                } else {
                    $uSql = "SELECT odm_members FROM " . DB_PREFIX . "user WHERE id=" . $this->userId;
                    $uData = $storeMod->querySql($uSql);
                    if ($uData[0]['odm_members'] != 1) {
                        $swhere .= ' AND s.store_type in (2,3) AND s.is_open=1';
                    }
                    $ssql = 'SELECT  s.id,s.logo,s.longitude,s.latitude,s.distance,sl.`store_name` AS sltore_name FROM  ' . DB_PREFIX . 'store AS s LEFT JOIN  ' . DB_PREFIX . 'store_lang AS sl ON sl.`store_id` = s.`id` and  sl.lang_id=' . $this->lang_id . ' LEFT JOIN  ' . DB_PREFIX . 'language AS l ON s.`lang_id` = l.`id` LEFT JOIN  ' . DB_PREFIX . 'currency AS c ON s.`currency_id` = c.`id`' . $swhere;
                    $sData = $storeMod->querySql($ssql);
                }
                foreach ($sData as $key => $val) {
                    $s = $this->getdistance(118.77807441, 32.0572355, $val['longitude'], $val['latitude']);
                    $distance = number_format(($s / 1000), 2, '.', '');
                    $sData[$key]['dis'] = $distance;
                    $oSql = "SELECT count(*) as total FROM " . DB_PREFIX . 'order WHERE store_id=' . $val['id'];
                    $oData = $storeMod->querySql($oSql);
                    $order_num = $oData[0]['total'];
                    $storeSql = 'select store_id from ' . DB_PREFIX . 'user_store where user_id=' . $this->userId;
                    $data_store = $storeMod->querySql($storeSql);
                    foreach ($data_store as $k1 => $v1) {
                        if ($val['id'] == $v1['store_id']) {
                            $sData[$key]['type'] = 1;
                        }
                    }
                    if(empty($order_num)) {
                        $sData[$key]['order_num'] = 0;
                    }else {
                        $sData[$key]['order_num'] = $order_num;
                    }
                    $busSql = "SELECT buss_id FROM " . DB_PREFIX . 'store_business WHERE  store_id=' .$val['id'];
                    $busData = $storeMod->querySql($busSql);
                    $sData[$key]['b_id']=$busData[0]['buss_id'];
                    if($val['distance'] < $distance){
                        unset($sData[$key]);
                    }
                    }
                $sort = array(
                    'direction' => 'SORT_ASC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
                    'field' => 'dis', //排序字段
                );
                $arrSort = array();
                foreach ($sData AS $uniqid => $row) {
                    foreach ($row AS $key => $value) {
                        $arrSort[$key][$uniqid] = $value;
                    }
                }
                if ($sort['direction']) {
                    array_multisort($arrSort[$sort['field']], constant($sort['direction']), $sData);
                }
                if($sData){
                    $this->setData($sData,'1','');
                }

        }

    /**
     * 获取业务类型
     *
     * @author zhangkx
     * @date 2018-08-20
     */
    public function getType()
    {
        $article = &m('article'); //文章
        $bussSql = "SELECT buss_id FROM " . DB_PREFIX . 'store_business WHERE  store_id=' . $this->store_id;
        $bussData = $article->querySql($bussSql);
        $busId = array();
        foreach ($bussData as $key => $val) {
            $busId[] = $val['buss_id'];
        }
        $busIds = implode(',', $busId);
        $roomType = $this->getRoomType($this->lang_id, $busIds);
        $goodRoomType = $this->getgoodRoomType($this->lang_id, 0);
        $goodRoomType = array_merge($roomType, $goodRoomType);
        $this->setData($goodRoomType,'1','');
    }

    public function getRoomType($langid, $buIds)
    {
        $roomTypeMod = &m('roomType');
        if (!empty($langid)) {
            if (!empty($buIds)) {
                $where = ' where  t.superior_id=0 and l.`lang_id` = ' . $langid . ' and t.id in(' . $buIds . ')';
            } else {
                $where = ' where  t.superior_id=0 and l.`lang_id` = ' . $langid;
            }
        } else {
            if (!empty($buIds)) {
                $where = ' where  t.superior_id=0 and l.`lang_id` = ' . $this->lang_id . ' and t.id in(' . $buIds . ')';
            } else {
                $where = ' where  t.superior_id=0 and l.`lang_id` = ' . $this->lang_id;
            }
        }
        if ($langid == $this->lang_id) {
            $sql = 'SELECT t.`id`,l.`type_name`,t.`room_img` FROM ' . DB_PREFIX . 'room_type AS t
                 LEFT JOIN ' . DB_PREFIX . 'room_type_lang AS l ON t.`id` = l.`type_id` ' . $where . ' and l.type_name like "%奶茶%" order by t.sort';
        } else {
            $sql = 'SELECT t.`id`,l.`type_name`,t.`room_img` FROM ' . DB_PREFIX . 'room_type AS t
                 LEFT JOIN ' . DB_PREFIX . 'room_type_lang AS l ON t.`id` = l.`type_id` ' . $where . ' and  l.type_name like "%Tea%" order by t.sort';
        }
        $data = $roomTypeMod->querySql($sql);
        return $data;
    }

    /**
     * 1级业务类型
     * @author wangh
     * @date 2017/09/13
     */
    public function getgoodRoomType($langid, $buIds)
    {
        $roomTypeMod = &m('roomType');
        if (!empty($langid)) {
            if (!empty($buIds)) {
                $where = ' where t.superior_id=0 and l.`lang_id` = ' . $langid . ' and t.id in(' . $buIds . ')';
            } else {
                $where = ' where t.superior_id=0 and l.`lang_id` = ' . $langid;
            }
        } else {
            if (!empty($buIds)) {
                $where = ' where t.superior_id=0 and l.`lang_id` = ' . $this->lang_id . ' and t.id in(' . $buIds . ')';
            } else {
                $where = ' where t.superior_id=0 and l.`lang_id` = ' . $this->lang_id;
            }
        }
        if ($langid == $this->lang_id) {
            $sql = 'SELECT  t.`id`,l.`type_name`,t.`room_img` FROM ' . DB_PREFIX . 'room_type AS t
                 LEFT JOIN ' . DB_PREFIX . 'room_type_lang AS l  ON  t.`id` = l.`type_id` ' . $where . ' and l.type_name not like "%奶茶%"  order by t.sort';

        } else {
            $sql = 'SELECT  t.`id`,l.`type_name`,t.`room_img` FROM ' . DB_PREFIX . 'room_type AS t
                 LEFT JOIN ' . DB_PREFIX . 'room_type_lang AS l ON t.`id` = l.`type_id` ' . $where . ' and l.type_name not like "%Tea%"  order by t.sort';
        }
        $data = $roomTypeMod->querySql($sql);
        return $data;
    }

    /**
     * 获取语言接口
     *
     * @author zhagnkx
     * @date 2018-08-20
     */
    public function getLang() {
        $languageMod = &m('language');
        $sql = 'select id, name, name_en, shorthand from ' . DB_PREFIX . 'language where enable=1';
        $data = $languageMod->querySql($sql);
        $this->setData($data,'1','');
    }

    /**
    * 发送验证码
    * @author tangbei
    * @date 2018-08-20
    *
    */
   public function phoneCode()
    {
        include_once ROOT_PATH."/includes/AliDy/sendSms.lib.php";
        $phone = !empty($_REQUEST['phone']) ? $_REQUEST['phone'] : '';
        if ($phone) {
            if (!preg_match("/^\d{11}$/i", $phone)) {
                $this->setData(array(),0,'手机号码格式不对！');
                exit;
            }
        } else { $this->setData(array(),0,'请填写手机号码！');
            exit;
        }
        $params = array();
        $params['PhoneNumbers'] = $phone;
        $params['SignName'] = "艾美睿零售";
        $params['TemplateCode'] = 'SMS_117585003';
        $code=$this->getCode();
        $params['TemplateParam'] = array(
            "code" =>$code ,
            "product" => "dsd"
        );
        $phoneCode = new sendSms($params);
        $info = $phoneCode->sendSms();
        $info1 = json_decode(json_encode($info), true);
        if ($info1['Message'] == 'OK') {
            $smsMod = &m('sms');
            $data = array(
                'phone' => $phone,
                'code' => $code,
                'send_time' => time()
            );
            $smsMod -> doInsert($data);

             $this->setData(array(),1,'验证码发送成功！');
        } else {
             $this->setData(array(),0,'验证码发送失败！');
        }

    }


    /**
     * 生成验证码
     * @author tangbei
     * @date 2018-08-20
     */
    public function getCode($length =6 ){
        $min = pow(10 , ($length - 1));
        $max = pow(10, $length) - 1;
        return rand($min, $max);
    }

    /**
    * 公共头部
    * @author:tangp
    * @date:2018-09-05
    */
    public function getHead()
    {
      $langData = array(

      );

      $logo = $webconf.store_logo.value;

      $data = array(
        'langData' => $langData,
        'logo'     => $logo
      );

      $this->setData($data,1,'');
    }

    /**
    * 公共尾部
    * @author:tangp
    * @date:2018-09-05
    */
    public function getFoot()
    {
      $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;  //多语言商品
      $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;  //所选的站点id
      $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
      $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
      $userId = $this->userId;
      $cart = &m('cart');
      $sql = "select count(*) from " . DB_PREFIX . "cart where user_id=" .$userId;
      $res = $cart->querySql($sql);
      $langData = array(

      );
      $data = array(
        'cart_nums' => $res,
        'langData'  => $langData
      );

      $this->setData($data,1,'');
    }

    public function listPage()
    {
        $lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : $this->lang_id;
        $cid = !empty($_REQUEST['cid']) ? intval($_REQUEST['cid']) : "";
        $page = !empty($_REQUEST['p']) ? $_REQUEST['p'] : 1;  //当前第几页
        $filter_param = array(); //筛选数组
        $brand = !empty($_REQUEST['b']) ? $_REQUEST['b'] : '';  //品牌
        $sort = !empty($_REQUEST['by']) ? $_REQUEST['by'] : 4; // 排序
        $start_price = !empty($_REQUEST['start_price']) ? trim($_REQUEST['start_price']) : 0;
        $end_price = !empty($_REQUEST['end_price']) ? trim($_REQUEST['end_price']) : '';
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon=!empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '';

        $goodsList = $this->getGoodsList($lang_id, $this->store_id, $filter_param, $cid, $page);

        $this->setData($goodsList,1,'');
    }
    public function getYiweiArr($arr) {
        $data = array();
        foreach ($arr as $key => $val) {
            $data[] = $val['id'];
        }
        return $data;
    }
    public function getGoodsList($langid, $storeid, $filter_param, $cid, $page) {
        $userId = $this->userId;
        $by = !empty($filter_param['by']) ? $filter_param['by'] : 1;
        $brandFilter = !empty($filter_param['b']) ? explode('_', $filter_param['b']) : array();
        $styleFilter = !empty($filter_param['s']) ? explode('_', $filter_param['s']) : array();
        $typeFilter = !empty($filter_param['t']) ? explode('_', $filter_param['t']) : array();
        $specFilter = !empty($filter_param['sp']) ? explode('_', $filter_param['sp']) : array();
        $priceFilter = !empty($filter_param['pr']) ? $filter_param['pr'] : array();
        $storeGoodsMod = &m('areaGood');

        if ($by == 1) {
            $orderBy = '  order by  shop_price  asc ';
        } else if ($by == 2) {
            $orderBy = '  order by  shop_price  desc ';
        } else if ($by == 3) {
            $orderBy = '  order by  s.add_time  desc ';
        }



        //获取分类下的商品
        if ($cid) {
            $goodsMod = &m('goods');
            $sql = ' SELECT goods_id, auxiliary_class  FROM   ' . DB_PREFIX . 'goods';
            $class = $goodsMod->querySql($sql);
            $goods = array();
            foreach ($class as $v) {
                if (empty($v['auxiliary_class']))
                    continue;
                $auxiliary_class = explode(":", $v['auxiliary_class']);
                foreach ($auxiliary_class as $ko => $vo) {
                    $cat_arrs = explode("_", $vo);
                    if (end($cat_arrs) == $cid) {
                        $goods[] = $v['goods_id'];
                    }
                }
            }
            $ids = implode(',', $goods);
            $where = ' where mark = 1 and store_id = ' . $storeid . ' and goods_id  in(' . $ids . ') and is_on_sale = 1';
            $sql = 'SELECT id FROM ' . DB_PREFIX . 'store_goods ' . $where;

            $dataD = $storeGoodsMod->querySql($sql);
            $good = $this->getYiweiArr($dataD);



            $where = '  where   mark=1  and  store_id =' . $storeid . '  and   cat_id  =' . $cid . '  and  is_on_sale =1';
            $sql = 'SELECT   id  FROM  ' . DB_PREFIX . 'store_goods  ' . $where;
            $dataC = $storeGoodsMod->querySql($sql);
            $goodsId = $this->getYiweiArr($dataC);
            $goodsId = array_merge($good, $goodsId);
        } else {
            $goodsId = array();
        }

        //品牌
        if (!empty($brandFilter)) {
            $brandids = implode(',', $brandFilter);
            $sql = 'SELECT  id   FROM   ' . DB_PREFIX . 'store_goods  WHERE   mark=1  and   store_id =' . $storeid . '   and  brand_id in(' . $brandids . ')';
            $dataB = $storeGoodsMod->querySql($sql);
            $arrB = $this->getYiweiArr($dataB);
            $goodsId = array_intersect($goodsId, $arrB);
        }
        //风格
        if (!empty($styleFilter)) {
            $styleids = implode(',', $styleFilter);
            $sql = 'SELECT  id   FROM   ' . DB_PREFIX . 'store_goods  WHERE   mark=1  and   store_id =' . $storeid . '   and  style_id in(' . $styleids . ')';
            $dataS = $storeGoodsMod->querySql($sql);
            $arrS = $this->getYiweiArr($dataS);
            $goodsId = array_intersect($goodsId, $arrS);
        }
        // 类型
        if (!empty($typeFilter)) {
            $typeids = implode(',', $typeFilter);
            $sql = 'SELECT  id   FROM   ' . DB_PREFIX . 'store_goods  WHERE  mark=1  and  store_id =' . $storeid . '   and  room_id in(' . $typeids . ')';
            $dataT = $storeGoodsMod->querySql($sql);
            $arrT = $this->getYiweiArr($dataT);
            $goodsId = array_intersect($goodsId, $arrT);
        }
        // 价格
        if (!empty($priceFilter)) {
            $arr2 = array();
            $arr1 = explode('-', $priceFilter);
            foreach ($arr1 as $key => $val) {
                $arr1[$key] = explode('_', $val);
            }
            foreach ($arr1 as $val) {
                if (in_array('*', $val)) {
                    $arr2[] = '  and  shop_price  >=' . $val[0];
                } else {
                    $arr2[] = '  and  shop_price  >=' . $val[0] . '  and  shop_price <= ' . $val[1];
                }
            }
            $res = array();
            foreach ($arr2 as $v) {
                $sql = 'SELECT  id   FROM   ' . DB_PREFIX . 'store_goods  WHERE   mark=1  and  store_id =' . $storeid . $v;
                $dataP = $storeGoodsMod->querySql($sql);
                $res = array_merge($res, $dataP);
            }
            $dataPr = $this->getYiweiArr($res);
            $goodsId = array_intersect($goodsId, $dataPr);
        }

        //规格筛选
        if (!empty($specFilter)) {
            $like = array();
            foreach ($specFilter as $val) {
                $like[] = '  `key` like "%' . $val . '%"  ';
            }
            $or = '(' . implode(' or', $like) . ')';

            $storeGoodsSpecPrice = &m('storeGoodItemPrice');
            $sql = 'select  store_goods_id  AS id  from  ' . DB_PREFIX . 'store_goods_spec_price  where  ' . $or;
            $dataSp = $storeGoodsSpecPrice->querySql($sql);
            $dataSp = $this->getYiweiArr($dataSp);
            $dataSp = array_unique($dataSp);
            $goodsId = array_intersect($goodsId, $dataSp);
        }
        //添加分页类
        include(ROOT_PATH . '/data/page/pageClass.php');
        //商品列表
        $total = count($goodsId);  //总条数
        $uri = urldecode(http_build_query($filter_param));
        $url = '?app=listPage&act=index&storeid=' . $this->store_id . '&lang=' . $this->lang_id . '&cid=' . $cid . '&' . $uri; //
        $pagesize = $this->pagesize; //每页显示条数
        $curpage = $page;  //当前页数
        $limit = '  limit ' . ($curpage - 1 ) * $pagesize . ',' . $pagesize;
        //实例化分页类
        $pageClass = new page2($total, $pagesize, $curpage, $url, 2);
        // 输出分页html
        $pagelink = $pageClass->myde_write();
        //
        $gids = implode(',', $goodsId);
        //多语言商品
        if ($by == 5) {
            //秒杀商品
            $seckMod = &m('spikeActivity');
            $goodsByMod = &m('groupbuy');
            $goodPromMod = &m('goodProm');
            $promotionMod = &m('goodPromDetail');
            $cartMod = &m('cart');
            $curtime = time();
            $today = strtotime(date('Y-m-d', time()));
            $now = $curtime - $today;
            $where1 = 'WHERE s.store_id =' . $storeid . '  and  ' . $curtime . ' > s.start_time and g.mark=1 and g.is_on_sale=1 ';
            $sql1 = 'SELECT  s.id as cid,s.`name`,s.start_time,s.end_time,s.start_our,s.end_our,s.store_id,s.store_goods_id as id,gl.original_img,s.content,s.item_name,s.item_key,s.discount,s.o_price,s.price,s.goods_num,g.is_free_shipping FROM  '
                . DB_PREFIX . 'spike_activity as s left join '
                . DB_PREFIX . 'store_goods as g on  s.store_goods_id = g.id  left join '
                . DB_PREFIX . 'goods as gl on  g.goods_id = gl.goods_id ' . $where1;
            $spikeArr = $seckMod->querySql($sql1);
            foreach ($spikeArr as $k => $item) {
                if (($curtime > $item['start_time']) && ($curtime < $item['end_time'])) {
                    if (($now >= $item['start_our']) && ($now <= $item['end_our'])) {
                        $spikeArr[$k]['in_time'] = 2;
                    } else {
                        $spikeArr[$k]['in_time'] = 1;
                    }
                } else {
                    $spikeArr[$k]['in_time'] = 3;
                }

                $child_info = $storeGoodsMod->getLangInfo($item['id'], $this->lang_id);

                if ($child_info) {
                    $k_name = $child_info['goods_name'];
                    $spikeArr[$k]['goods_name'] = $k_name;
                }

            }

            foreach ($spikeArr as &$item) {
                //翻译处理

                $item['preferential'] = '秒杀';
                $item['source'] = 1; //优惠商品标记
                //是否包邮
                switch ($item['is_free_shipping']) {
                    case 1:
                        $item['isfree'] = '包邮';  // Package mail
                        break;
                    case 2:
                        $item['isfree'] = '不包邮'; //Don't pack mail
                        break;
                    default:
                        $item['isfree'] = '不包邮';
                }
            }


            //团购商品
            $where2 = '  where  b.store_id = ' . $this->store_id . ' and  b.mark =1 and g.mark=1 and g.is_on_sale=1';
            $sql2 = 'SELECT  b.id,b.goods_id,b.store_id,b.start_time,b.end_time,b.group_goods_price,b.virtual_num,l.original_img,b.goods_price,b.goods_name  FROM  '
                . DB_PREFIX . 'goods_group_buy  AS b  LEFT JOIN  '
                . DB_PREFIX . 'store_goods AS g ON b.`goods_id` = g.id  LEFT JOIN '
                . DB_PREFIX . 'goods AS l ON g.`goods_id` = l.`goods_id` ' . $where2;
            $data = $goodsByMod->querySql($sql2);

            foreach ($data as $k => $val) {
                //活动的状态的更改
                if ($curtime < $val['start_time']) {
                    //未开始
                    $goodsByMod->doEdit($val['id'], array('is_end' => 3));
                } else if (( $curtime > $val['start_time'] ) && ( $curtime < $val['end_time'] )) {
                    //进行中
                    $goodsByMod->doEdit($val['id'], array('is_end' => 1));
                } else if ($curtime > $val['end_time']) {
                    //结束
                    $goodsByMod->doEdit($val['id'], array('is_end' => 2));
                }
            }

            $where3 = 'WHERE  l.`lang_id` = ' . $this->lang_id . '  and  b.store_id =' . $this->store_id . '  AND b.is_end =1 AND b.mark = 1 and g.mark=1 and g.is_on_sale=1 ';
            $sql3 = 'SELECT  b.id as cid,b.goods_id as id,b.store_id,b.end_time,b.group_goods_price as price,b.virtual_num,gsl.original_img,b.goods_price as o_price,l.`goods_name`,b.goods_spec_key as item_key  FROM  '
                . DB_PREFIX . 'goods_group_buy   AS b  LEFT JOIN  '
                . DB_PREFIX . 'store_goods AS g ON b.`goods_id` = g.id LEFT JOIN  '
                . DB_PREFIX . 'goods_lang AS l ON g.`goods_id` = l.`goods_id`  LEFT JOIN  '
                . DB_PREFIX . 'goods AS gsl ON g.`goods_id` = gsl.`goods_id` ' . $where3;
            $groupByGoodArr = $goodsByMod->querySql($sql3);
            foreach ($groupByGoodArr as &$item) {
                $item['goods_name']= $cartMod->getGoodNameById($item['id'], $this->lang_id);
                $item['preferential'] = '团购';
                $item['source'] = 2; //优惠商品标记
                //是否包邮
                switch ($item['is_free_shipping']) {
                    case 1:
                        $item['isfree'] = '包邮';  // Package mail
                        break;
                    case 2:
                        $item['isfree'] = '不包邮'; //Don't pack mail
                        break;
                    default:
                        $item['isfree'] = '不包邮';
                }
            }
            //促销商品
            $this->checkOver();
            // 获取正在进行或者未开始的促销活动
            $sql4 = " select ps.id as cid,ps.*,pg.goods_id as id,pg.goods_key as item_key,pg.goods_key_name,pg.goods_name,sgl.original_img,pg.goods_price as o_price,pg.discount_price as price,s.is_free_shipping from "
                . DB_PREFIX . "promotion_sale as ps left join "
                . DB_PREFIX . "promotion_goods as pg on  ps.id = pg.prom_id left join "
                . DB_PREFIX . "store_goods as s on pg.goods_id = s.id  left join "
                . DB_PREFIX . "goods as sgl on s.goods_id = sgl.goods_id  where ps.`store_id` = $this->store_id and ps.`status` in (1,2) and ps.`mark` =1 order by ps.status desc,ps.id desc";
            $promotionGoodsArr = $goodPromMod->querySql($sql4);
            foreach ($promotionGoodsArr as &$item) {
                $item['goods_name']= $cartMod->getGoodNameById($item['id'], $this->lang_id);
                $item['preferential'] = '促销';
                $item['source'] = 3; //优惠商品标记
                //是否包邮
                switch ($item['is_free_shipping']) {
                    case 1:
                        $item['isfree'] = '包邮';  // Package mail
                        break;
                    case 2:
                        $item['isfree'] = '不包邮'; //Don't pack mail
                        break;
                    default:
                        $item['isfree'] = '不包邮';
                }
            }
            $res = array();
            $res['data'] = array_merge( $groupByGoodArr, $promotionGoodsArr,$spikeArr);
            return $res;
        } else {
            $where = '  where   s.store_id =' . $storeid . '  and   s.id  in(' . $gids . ')   and   s.mark=1   and   s.is_on_sale =1  AND l.`lang_id` = ' . $this->lang_id;

            $sql = 'SELECT s.id,s.`goods_id`,s.`cat_id`,s.`store_id`,l.`goods_name`,l.`lang_id`,s.`shop_price`,s.`market_price`,s.`brand_id`,gl.`original_img`,s.is_free_shipping
                FROM  '
                . DB_PREFIX . 'store_goods AS s LEFT JOIN  '
                . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id` LEFT JOIN '
                . DB_PREFIX . 'goods AS gl ON s.`goods_id` = gl.`goods_id` ' . $where . $orderBy . $limit;
            $arr = $storeGoodsMod->querySql($sql);
            foreach ($arr as &$item) {
                //店铺商品打折
                $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $this->store_id;
                $store_arr = $storeGoodsMod->querySql($store_sql);
                $item['shop_price'] = number_format($item['shop_price'] * $store_arr[0]['store_discount'],2);
                //是否包邮
                switch ($item['is_free_shipping']) {
                    case 1:
                        $item['isfree'] = '包邮';  // Package mail
                        break;
                    case 2:
                        $item['isfree'] = '不包邮'; //Don't pack mail
                        break;
                    default:
                        $item['isfree'] = '不包邮';
                }
                //收藏商品
                $sql_collection = 'select * from ' . DB_PREFIX . 'user_collection where user_id=' . $userId . ' and store_id=' . $storeid;
//            echo $sql_collection;exit;
                $data_collection = $storeGoodsMod->querySql($sql_collection);
//            var_dump($data_collection);exit;
                foreach ($data_collection as &$collertion) {
                    if ($collertion['good_id'] == $item['id']) {
                        $item['type'] = 1;
                    }
                }
            }
            //组装数据
            $res = array();
            $res['data'] = $arr;
            $res['pagelink'] = $pagelink;
            $res['count'] = $total;

            return $res;
        }
    }
    //检查促销商品是否过期
    public function checkOver() {
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


    //附近店铺
    public function store() {
        $latlon=!empty($_REQUEST['latlon']) ? $_REQUEST['latlon'] : 0;
        $latlon=explode(',',$latlon);
        $lng = $latlon[1]; //经度
        $lat = $latlon[0]; //纬度
        $latlon=$this->coordinate_switchf($lat,$lng);
        $lng=$latlon['Longitude'];
        $lat=$latlon['Latitude'];
        $storeMod = &m('store');
        $bussId = !empty($_REQUEST['bussId']) ? $_REQUEST['bussId'] : 0;
        $sql="select room_adv_imgs from ".DB_PREFIX.'room_type where id='.$bussId;
        $typeImages= $storeMod->querySql($sql);
        $typeImages=explode(',',$typeImages[0]['room_adv_imgs']);
        if (!empty($bussId)) {
            $bSql = "SELECT store_id FROM " . DB_PREFIX . 'store_business WHERE buss_id in (' . $bussId . ')';
            $bData = $storeMod->querySql($bSql);
            foreach ($bData as $k => $v) {
                $sId[] = $v['store_id'];
            }
            $sIds = implode(',', array_unique($sId));
            $swhere = " where sl.distinguish=0 AND s.id in (" . $sIds . ')';
        } else {
            $swhere = " where sl.distinguish=0  ";
        }

            $swhere .= ' AND s.store_type in (2,3) AND s.is_open=1 ';
            $ssql = 'SELECT  s.id,s.logo,s.longitude,s.latitude,s.distance,sl.`store_name` AS sltore_name,s.store_start_time,s.store_end_time,s.store_notice,s.store_mobile FROM  '
                . DB_PREFIX . 'store AS s LEFT JOIN  '
                . DB_PREFIX . 'store_lang AS sl ON sl.`store_id` = s.`id` and  sl.lang_id=' . $this->lang_id . ' LEFT JOIN  ' . DB_PREFIX . 'language AS l ON s.`lang_id` = l.`id` LEFT JOIN  ' . DB_PREFIX . 'currency AS c ON s.`currency_id` = c.`id`' . $swhere;
            $sData = $storeMod->querySql($ssql);

        foreach ($sData as $key => $val) {
            $s = $this->getdistance($lng, $lat, $val['longitude'], $val['latitude']);
            $distance = number_format(($s / 1000), 2, '.', '');
            $sData[$key]['dis'] = $distance;
            $oSql = "SELECT count(*) as total FROM " . DB_PREFIX . 'order WHERE store_id=' . $val['id'];
            $oData = $storeMod->querySql($oSql);
            $order_num = $oData[0]['total'];
            $storeSql = 'select store_id from ' . DB_PREFIX . 'user_store where user_id=' . $this->userId;
            $data_store = $storeMod->querySql($storeSql);
            foreach ($data_store as $k1 => $v1) {
                if ($val['id'] == $v1['store_id']) {
                    $sData[$key]['type'] = 1;
                }
            }
            if (empty($order_num)) {
                $sData[$key]['order_num'] = 0;
            } else {
                $sData[$key]['order_num'] = $order_num;
            }
            $busSql = "SELECT buss_id FROM " . DB_PREFIX . 'store_business WHERE  store_id=' . $val['id'];
            $busData = $storeMod->querySql($busSql);
            $sData[$key]['b_id'] = $busData[0]['buss_id'];
            /*  if ($val['distance'] < $distance) {
                  unset($sData[$key]);
              }*/
        }
        $sort = array(
            'direction' => 'SORT_ASC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
            'field' => 'dis', //排序字段
        );
        $arrSort = array();
        foreach ($sData AS $uniqid => $row) {
            foreach ($row AS $key => $value) {
                $arrSort[$key][$uniqid] = $value;
            }
        }
        if ($sort['direction']) {
            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $sData);
        }
        $data=array(
          'advData'=>$typeImages,
          'storeData'=>$sData
        );
        $this->setData($data, 1, '');
    }


}
