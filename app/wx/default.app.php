<?php

/**
 * 手机app
 * @author lvji
 * @date 2015-3-10
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class DefaultApp extends BaseWxApp {
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
    }
    /**
     * 析构函数
     */
    public function __destruct() {
        
    }
    /**
     * 空操作
     * @author lvji
     * @date 2015-03-20
     */
    public function emptyOperate() {
        $info = array();
        $this->setData($info);
    }

    /**
     * 首页
     * @author lvji1
     * @date 2015-3-10
     */
    public function index() {
        $tp = !empty($_REQUEST['tp']) ? $_REQUEST['tp'] : 0;  //区别授权的
        if( $tp ){
            //判断是否登录
            header('Location: wx.php?app=user&act=loginOuth&back_app=' . APP . '&back_act=' . ACT . '&storeid=' . $this->storeid . '&lang=' . $this->langid . '&latlon=' . $_REQUEST['latlon']);
            exit;
        }
        if(!empty($_SESSION['latlon'])){
                $display=1;
                }
        if(!empty($_REQUEST['latng'])){
            $_SESSION['latlon']=$_REQUEST['latng'];
        }
           /* $_SESSION['latlon']='34.68166,135.503';*/

            $address_detail= $this->getAddr($_SESSION['latlon']);
          /*  var_dump($address_detail);exit;*/



            $this->assign('addr',$address_detail);
            //获取店铺信息
            $txData=$this->getStoreData();
            $this->assign('txData',$txData);
            $this->assign('display',$display);
            $article = &m('article'); //文章
            $this->load($this->shorthand, 'WeChat/goods');
            $this->assign('langdata', $this->langData);
            $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;
            $referer = urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"]);
            $address = !empty($_REQUEST['address']) ? $_REQUEST['address'] : '';
            if (!empty($address)) {
                $address = substr($address, 0, -1);
            }
            $this->assign('location', $address);
            $qu = !empty($_REQUEST['qu']) ? $_REQUEST['qu'] : '';
            $this->assign('qu', $qu);
            $this->assign('referer', $referer);
            $this->assign('storeid', $storeid);
            //一级广告
            $banner = $this->getBanner(110000, $this->storeid);
            $this->assign('banner', $banner);
            //类业务型
            $bussSql = "SELECT buss_id FROM " . DB_PREFIX . 'store_business WHERE  store_id=' . $storeid;
            $bussData = $article->querySql($bussSql);
            foreach ($bussData as $key => $val) {
                $buId[] = $val['buss_id'];
            }
            $buIds = implode(',', $buId);
            $this->assign('buIds', $buIds);
            $roomtype = $this->getgoodRoomType($this->langid, $buIds);
            foreach ($roomtype as $key => $val) {
                $where = '    where  t.superior_id=' . $val['id'] . ' and  l.`lang_id`  = ' . $this->langid;
                $sql = 'SELECT  t.`id`  FROM  ' . DB_PREFIX . 'room_type AS t
                 LEFT JOIN  ' . DB_PREFIX . 'room_type_lang  AS  l  ON  t.`id` = l.`type_id`  ' . $where . ' order by t.sort';
                $data = $article->querySql($sql);
                foreach ($data as $key1 => $val1) {
                    $rid[] = $val1['id'];
                }
                $rtid = implode(',', $rid);
                $rid = array();
                $roomtype[$key]['rtid'] = $rtid;
            }
            $naicha = $this->getRoomType($this->langid, $buIds);
            $this->assign('naicha', $naicha);
            $roomtypea = $this->getgoodRoomType($this->langid, 0);
            $roomtypea = array_merge($naicha, $roomtypea);
            $this->assign('roomtypea', $roomtypea);
            $this->assign('roomtype', $roomtype);
            //首篇文章
            $articlesql = 'SELECT al.title,a.id FROM ' . DB_PREFIX . 'article AS a LEFT JOIN ' . DB_PREFIX . 'article_lang AS al ON a.id=al.article_id where  
            a.store_id =' . $storeid . ' AND al.lang_id=' . $this->langid . ' AND a.isrecom = 1 order by a.add_time desc ';
            $articledata = $article->querySql($articlesql);
            $this->assign('articledata', $articledata);
            //限时优惠
            $prom_arr = $this->getGoodsDiscount($this->storeid, $_REQUEST['latlon']);
            $this->assign('prom_arr', $prom_arr);
            //为你推荐
            $recommYou = $this->recommendForYou($this->storeid);
            $this->assign('recommYou', $recommYou);
            //站点地址
            $storeMod = &m('store');
            $aSql = 'SELECT addr_detail,logo FROM ' . DB_PREFIX . 'store WHERE id=' . $storeid;
            $aData = $storeMod->querySql($aSql);
            $this->assign('address', $aData[0]['addr_detail']);
            $this->assign('logo', $aData[0]['logo']);
            $this->assign('lang', $this->langid);
            $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
            $this->assign('auxiliary', $auxiliary);
            //网站配置logo图
            $webconf = $this->webconf();
            $this->assign('webconf', $webconf);

            $this->display('index.html');

    }


    public function getAddr($latlon){
        $url="https://apis.map.qq.com/ws/geocoder/v1/?location={$latlon}&key=SIYBZ-DYBY5-5R3IE-QVADH-4YP5J-BCFFU&get_poi=1";
        $address=file_get_contents($url);
        $address=json_decode($address);

        /*return  $address;*/
       $address_detail=$address->result->address_component->street_number;

       if(empty($address_detail)){
           $address_detail=$address->result->address_component->locality;
         }

        return $address_detail;
    }

   public function getStoreData(){
       $storeMod = &m('store');
       $swhere = ' where s.store_type in (2,3) AND s.is_open=1 AND sl.distinguish=0  and sl.lang_id='.$this->langid;
        $sql = 'SELECT  s.id,s.logo,s.longitude,s.latitude,s.distance,sl.`store_name` AS sltore_name,s.addr_detail FROM  ' . DB_PREFIX . 'store AS s LEFT JOIN  ' . DB_PREFIX . 'store_lang AS sl ON sl.`store_id` = s.`id` and  sl.lang_id=' . $this->langid . ' LEFT JOIN  ' . DB_PREFIX . 'language AS l ON s.`lang_id` = l.`id` LEFT JOIN  ' . DB_PREFIX . 'currency AS c ON s.`currency_id` = c.`id`'.$swhere.' limit 4';
        $storeData=$storeMod->querySql($sql);

        foreach($storeData  as $key=>$val){
            $latlon=$this->coordinate_switch($storeData[$key]['latitude'],$storeData[$key]['longitude']);


            $title='coord:'.$latlon['Latitude'].','.$latlon['Longitude'].';title:'.$storeData[$key]['sltore_name'].';addr:'.$storeData[$key]['addr_detail'];

            $lang .=$title;
        }
        return $lang;
   }
   //百度坐标转化为腾讯坐标
    public function coordinate_switch($a,$b){//百度转腾讯坐标转换


        $x = (double)$b - 0.0065;
        $y = (double)$a - 0.006;
        $x_pi = 3.14159265358979324;
        $z = sqrt($x * $x+$y * $y) - 0.00002 * sin($y * $x_pi);

        $theta = atan2($y,$x) - 0.000003 * cos($x*$x_pi);

        $gb = number_format($z * cos($theta),6);
        $ga = number_format($z * sin($theta),6);


        return   array(
            'Latitude'=>$ga,
            'Longitude'=>$gb
        );;

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
    //网站配置
    public function webconf() {
        $configMod = &m('config');
        $sql = 'select * from bs_config  where  inc_type = "shop_info" ';
        $data = $configMod->querySql($sql);
        $res = array();
        foreach ($data as $key => $val) {
            $res[$val['name']] = $val;
        }
        return $res;
    }

    //附近店铺
    public function store() {

        $latlon = explode(',', $_SESSION['latlon']);

        $lng = $latlon[1]; //经度
        $lat = $latlon[0]; //纬度
        if ($lng && $lat) {
            $latlon=$this->coordinate_switchf($lat,$lng);
            $lng=$latlon['Longitude'];
            $lat=$latlon['Latitude'];
        }
        $storeMod = &m('store');
        $bussId = !empty($_REQUEST['bussId']) ? $_REQUEST['bussId'] : array();
        $bussIds = implode(',', $bussId);
        if (!empty($bussId)) {
            $bSql = "SELECT store_id FROM " . DB_PREFIX . 'store_business WHERE buss_id in (' . $bussIds . ')';
            $bData = $storeMod->querySql($bSql);
            foreach ($bData as $k => $v) {
                $sId[] = $v['store_id'];
            }
            $sIds = implode(',', array_unique($sId));
            $swhere = " where sl.distinguish=0 AND s.id in (" . $sIds . ')';
        } else {
            $swhere = " where sl.distinguish=0  ";
        }
        if (!in_array($this->userId, array(3192,4035,4071,4212,4214,4328,4448,4451,5229,5459,5466,5467,5725,6039,6250,6982,8156,8179,19123))) {
            $swhere .= " and s.id not in (84)";
        }
        if (empty($this->userId)) {
            $swhere .= ' AND s.store_type in (2,3) AND s.is_open=1 ';
            $ssql = 'SELECT  s.id,s.logo,s.longitude,s.latitude,s.distance,sl.`store_name` AS sltore_name,s.store_start_time,s.store_end_time,s.store_notice,s.store_mobile FROM  ' . DB_PREFIX . 'store AS s LEFT JOIN  ' . DB_PREFIX . 'store_lang AS sl ON sl.`store_id` = s.`id` and  sl.lang_id=' . $this->langid . ' LEFT JOIN  ' . DB_PREFIX . 'language AS l ON s.`lang_id` = l.`id` LEFT JOIN  ' . DB_PREFIX . 'currency AS c ON s.`currency_id` = c.`id`' . $swhere;
            $sData = $storeMod->querySql($ssql);
        } else {
            $uSql = "SELECT odm_members FROM " . DB_PREFIX . "user WHERE id=" . $this->userId;
            $uData = $storeMod->querySql($uSql);
            if ($uData[0]['odm_members'] != 1) {
                $swhere .= ' AND s.store_type in (2,3) AND s.is_open=1';
            }else{
                 $swhere .= ' AND s.store_type in (2,3,4) AND s.is_open=1';
            }
            $ssql = 'SELECT  s.id,s.logo,s.longitude,s.latitude,s.distance,sl.`store_name` AS sltore_name,s.store_start_time,s.store_end_time,s.store_notice,s.store_mobile FROM  ' . DB_PREFIX . 'store AS s LEFT JOIN  ' . DB_PREFIX . 'store_lang AS sl ON sl.`store_id` = s.`id` and  sl.lang_id=' . $this->langid . ' LEFT JOIN  ' . DB_PREFIX . 'language AS l ON s.`lang_id` = l.`id` LEFT JOIN  ' . DB_PREFIX . 'currency AS c ON s.`currency_id` = c.`id`' . $swhere;
            $sData = $storeMod->querySql($ssql);
        }
        foreach ($sData as $key => $val) {
            if ($lng && $lat) {
                $s = $this->getdistance($lng, $lat, $val['longitude'], $val['latitude']);
                $distance = number_format(($s / 1000), 2, '.', '');
                $sData[$key]['dis'] = $distance;
            } else {
                $sData[$key]['dis'] = 0;
            }
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
        if ($lng && $lat) {
            $sort = array(
                'direction' => 'SORT_ASC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
                'field' => 'dis', //排序字段
            );
        } else {
            $sort = array(
                'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
                'field' => 'id', //排序字段
            );
        }
        $arrSort = array();
        foreach ($sData AS $uniqid => $row) {
            foreach ($row AS $key => $value) {
                $arrSort[$key][$uniqid] = $value;
            }
        }
        if ($sort['direction']) {
            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $sData);
        }
        $this->assign('data', $sData);
        $str = self::$smarty->fetch("ajaxStore.html");
        $this->setData($str, '1', 'success');
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
     * 风格商品页
     * @author lvji
     * @date 2015-3-10
     */
    public function styleIndex() {
        //语言包
        $this->load($this->shorthand, 'WeChat/goods');
        $this->assign('langdata', $this->langData);
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $filter_param = array(); //筛选数组
        $style = !empty($_REQUEST['s']) ? $_REQUEST['s'] : '';  //风格
        $sort = !empty($_REQUEST['by']) ? $_REQUEST['by'] : 4; // 排序
        $brand = !empty($_REQUEST['b']) ? $_REQUEST['b'] : '';  //品牌
        $start_price = !empty($_REQUEST['start_price']) ? trim($_REQUEST['start_price']) : 0;
        $end_price = !empty($_REQUEST['end_price']) ? trim($_REQUEST['end_price']) : '';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        $this->assign('sprice', $start_price);
        $this->assign('eprice', $end_price);
        $start_price = abs(intval($_REQUEST['start_price']));
        $end_price = abs(intval($_REQUEST['end_price']));
        if ($start_price != 0 || $end_price != 0) {
            if ($end_price < $start_price) {
                $price = $end_price . '_' . $start_price;
            } else {
                $price = $start_price . '_' . $end_price;
            }
        }
        /* 当前页面的 url */
        $query_string = $_SERVER["QUERY_STRING"];
        $query_arr = explode('&', $query_string);
        foreach ($query_arr as $key => $val) {
            if (strstr($val, 'start_price')) {
                unset($query_arr[$key]);
            }
            if (strstr($val, 'end_price')) {
                unset($query_arr[$key]);
            }
        }

        $curUrl = '?' . implode('&', $query_arr);

        //加入筛选条件
        if (!empty($style)) {
            $filter_param['s'] = $style;
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

        $baseUrl = '?app=default&act=styleIndex&storeid=' . $this->storeid . '&lang=' . $lang . '&latlon=' . $latlon . '&';
        $clearAll = '?app=default&act=styleIndex&storeid=' . $this->storeid . '&lang=' . $lang . '&latlon=' . $latlon . '&s=' . $style;

        //品牌
        $brand = $this->getGoodsBrand($this->langid, $filter_param, $baseUrl);
        //该风格下的商品
        $goodsList = $this->getGoodsList($this->langid, $this->storeid, $filter_param);
        //商品排序
        $goodsSort = $this->getGoodsSort($this->langid, $filter_param, $baseUrl);

        $this->assign('goodsList', $goodsList);
        $this->assign('lang', $lang);
        $this->assign('by', $sort);
        $this->assign('clearAll', $clearAll);
        $this->assign('brand', $brand);
        $this->assign('goodsSort', $goodsSort);
        $this->assign('curUrl', $curUrl);
        $this->display('listPage/listPage.html');
    }

    /**
     * 业务类型
     * @author lvji
     * @date 2015-3-10
     */
    public function typeIndex() {
        //语言包
        $this->load($this->shorthand, 'WeChat/goods');
        $this->assign('langdata', $this->langData);
        $recom = !empty($_REQUEST['recom']) ? $_REQUEST['recom'] : 0;
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $filter_param = array(); //筛选数组
        $style = !empty($_REQUEST['s']) ? $_REQUEST['s'] : '';  //风格
        $rtid = !empty($_REQUEST['rtid']) ? $_REQUEST['rtid'] : ''; //业务
        $sort = !empty($_REQUEST['by']) ? $_REQUEST['by'] : 4; // 排序
        $brand = !empty($_REQUEST['b']) ? $_REQUEST['b'] : '';  //品牌
        $start_price = !empty($_REQUEST['start_price']) ? trim($_REQUEST['start_price']) : 0;
        $end_price = !empty($_REQUEST['end_price']) ? trim($_REQUEST['end_price']) : '';
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        $this->assign('auxiliary', $auxiliary);
        $this->assign('sprice', $start_price);
        $this->assign('eprice', $end_price);

        $start_price = abs(intval($_REQUEST['start_price']));
        $end_price = abs(intval($_REQUEST['end_price']));

        if ($start_price != 0 || $end_price != 0) {
            if ($end_price < $start_price) {
                $price = $end_price . '_' . $start_price;
            } else {
                $price = $start_price . '_' . $end_price;
            }
        }
        /* 当前页面的 url */
        $query_string = $_SERVER["QUERY_STRING"];
        $query_arr = explode('&', $query_string);
        foreach ($query_arr as $key => $val) {
            if (strstr($val, 'start_price')) {
                unset($query_arr[$key]);
            }
            if (strstr($val, 'end_price')) {
                unset($query_arr[$key]);
            }
        }
        $curUrl = '?' . implode('&', $query_arr);
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
        if (empty($recom)) {
            $baseUrl = '?app=default&act=typeIndex&storeid=' . $this->storeid . '&lang=' . $lang . '&latlon=' . $latlon . '&auxiliary=' . $auxiliary . '&';
            $clearAll = '?app=default&act=typeIndex&storeid=' . $this->storeid . '&lang=' . $lang . '&latlon=' . $latlon . '&auxiliary=' . $auxiliary . '&rtid=' . $rtid;
        } else {
            $baseUrl = '?app=default&act=typeIndex&storeid=' . $this->storeid . '&lang=' . $lang . '&latlon=' . $latlon . '&auxiliary=' . $auxiliary . '&recom=1&';
            $clearAll = '?app=default&act=typeIndex&storeid=' . $this->storeid . '&lang=' . $lang . '&latlon=' . $latlon . '&auxiliary=' . $auxiliary . '&rtid=' . $rtid . '&recom=1&';
        }


        //业务下的分类
        $roomCtg = $this->getRoomCtg($rtid, $this->langid);
        $cidArr = array();
        foreach ($roomCtg as $val) {
            $cidArr[] = $val['cid'];
        }
        $cids = implode(',', $cidArr);
        //品牌
        $brand = $this->getGoodsBrand($this->langid, $filter_param, $baseUrl);
        //风格
        $goodstyle = $this->getGoodStyle($this->langid, $filter_param, $baseUrl);
        //业务类型下的商品

        $goodsList = $this->getCtgGoods($cids, $this->storeid, $this->langid, $filter_param, $recom);


        //商品排序
        $goodsSort = $this->getGoodsSort($this->langid, $filter_param, $baseUrl);

        $this->assign('goodsList', $goodsList);
        $this->assign('lang', $lang);
        $this->assign('by', $sort);
        $this->assign('brand', $brand);
        $this->assign('clearAll', $clearAll);
        $this->assign('goodstyle', $goodstyle);
        $this->assign('goodsSort', $goodsSort);
        $this->assign('curUrl', $curUrl);
        $this->display('listPage/listPage.html');
    }

//业务分类
    public function getRoomCtg($rtid, $langid) {
        $roomCtgMod = &m('roomTypeCate');
        if (!empty($langid)) {
            $where = '    where  cl.`lang_id`  = ' . $langid;
        } else {
            $where = '    where  cl.`lang_id`  = ' . $this->mrlangid;
        }
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

        // $shorthand = $this->shorthand;
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
                $child_info = $storeGoodsMod->getLangInfo($item['id'], $this->langid);

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
            $where2 = '  where  b.store_id = ' . $this->storeid . ' and  b.mark =1 and g.mark=1 and g.is_on_sale=1';
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
                } else if (($curtime > $val['start_time']) && ($curtime < $val['end_time'])) {
                    //进行中
                    $goodsByMod->doEdit($val['id'], array('is_end' => 1));
                } else if ($curtime > $val['end_time']) {
                    //结束
                    $goodsByMod->doEdit($val['id'], array('is_end' => 2));
                }
            }

            $where3 = 'WHERE  l.`lang_id` = ' . $this->langid . '  and  b.store_id =' . $this->storeid . '  AND b.is_end =1 AND b.mark = 1 and g.mark=1 and g.is_on_sale=1 ';
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
                    . DB_PREFIX . "goods as sgl on s.goods_id = sgl.goods_id  where ps.`store_id` = $this->storeid and ps.`status` in (1,2) and s.mark=1 and s.is_on_sale =1 and ps.`mark` =1 order by ps.status desc,ps.id desc";
            $promotionGoodsArr = $goodPromMod->querySql($sql4);

            foreach ($promotionGoodsArr as &$item) {
                $item['goods_name'] = $cartMod->getGoodNameById($item['id'], $this->langid);


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
            $res['data'] = array_merge($groupByGoodArr, $promotionGoodsArr, $spikeArr);
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
                $where = '  where   s.store_id =' . $storeid . '  and    s.id  in(' . $gids . ')  and rc.room_type_id = ' . $rtid . '  and   s.mark=1   and   s.is_on_sale =1  AND l.`lang_id` = ' . $this->langid;
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

    /**
     * 商品列表
     * @author wangh
     * @date 2017/09/18
     */
    public function getGoodsList($langid, $storeid, $filter_param) {
        $by = !empty($filter_param['by']) ? $filter_param['by'] : 1;
        $style = !empty($filter_param['s']) ? $filter_param['s'] : '';
        $brandFilter = !empty($filter_param['b']) ? explode('_', $filter_param['b']) : array();
        $priceFilter = !empty($filter_param['pr']) ? $filter_param['pr'] : array();

        $userId = $this->userId;


        $storeGoodsMod = &m('areaGood');
        $spikeMod = &m('spikeActivity');
        $goodsByMod = &m('groupbuy');
        $promotionMod = &m('goodPromDetail');
//        if ($by == 1) {
//            $orderBy = '  order by  s.shop_price  asc ';
//        } else {
//            $orderBy = '  order by  s.shop_price  desc ';
//        }
        if ($by == 1) {
            $orderBy = '  order by  s.shop_price  asc ';
        } else if ($by == 2) {
            $orderBy = '  order by  s.shop_price  desc ';
        } else if ($by == 3) {
            $orderBy = '  order by  s.add_time  desc ';
        }
        //获取分类下的商品
        if ($style) {
            //获取分类下的商品
            $where = '  where   mark=1  and  store_id =' . $storeid . '  and   style_id  =' . $style . '  and  is_on_sale =1';
            $sql = 'SELECT   id  FROM  ' . DB_PREFIX . 'store_goods  ' . $where;
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

        //
        $gids = implode(',', $goodsId);


        //多语言商品


        $where = '  where   s.store_id =' . $storeid . '  and   s.id  in(' . $gids . ')   and   s.mark=1   and   s.is_on_sale =1  AND l.`lang_id` = ' . $langid;
        $sql = 'SELECT s.id,s.`goods_id`,s.`cat_id`,s.`store_id`,l.`goods_name`,l.`lang_id`,s.`shop_price`,s.`market_price`,s.`brand_id`,s.`original_img`,s.is_free_shipping
                FROM  ' . DB_PREFIX . 'store_goods AS s LEFT JOIN  ' . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id`' . $where . $orderBy;
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

            //收藏商品
//            $sql_collection = 'select * from ' . DB_PREFIX . 'user_collection where user_id=' . $userId . ' and store_id=' . $storeid;
//            $data_collection = $storeGoodsMod->querySql($sql_collection);
//            foreach ($data_collection as &$collertion) {
//                if ($collertion['good_id'] == $item['id']) {
//                    $item['type'] = 1;
//                }
//            }
        }
        //组装数据
        $res = array();
        $res['data'] = $arr;


//        $res['pagelink'] = $pagelink;
//        $res['count'] = $total;

        return $res;
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

    /**
     * 商品排序
     * @author wangh
     * @date 2017/09/13
     */
    public function getGoodsSort($langid, $filter_param, $baseUrl) {
        //加载语言包
        $this->load($this->shorthand, 'listpage/listpage');
        $a = $this->langData;
        $sortFilter = !empty($filter_param['by']) ? $filter_param['by'] : 1;
        unset($filter_param['by']);
        $sort = array(
            1 => array('by' => 1, 'val' => $a['From']),
            2 => array('by' => 2, 'val' => $a['high']),
            3 => array('by' => 3, 'val' => '新品'),
            4 => array('by' => 4, 'val' => '综合'),
            5 => array('by' => 5, 'val' => '优惠')
        );
        $uri = urldecode(http_build_query($filter_param));

        foreach ($sort as $key => $val) {
            $sort[$key]['href'] = $baseUrl . $uri . '&by=' . $val['by'];
            if ($val['by'] == $sortFilter) {
                $sort['selected'] = $val['val'];
            }
        }

        return $sort;
    }

    /**
     * 1级业务类型
     * @author wangh
     * @date 2017/09/13
     */
    public function getgoodRoomType($langid, $buIds) {
        $roomTypeMod = &m('roomType');
        if (!empty($langid)) {
            if (!empty($buIds)) {
                $where = '    where  t.superior_id=0 and  l.`lang_id`  = ' . $langid . ' and t.id in(' . $buIds . ')';
            } else {
                $where = '    where  t.superior_id=0 and  l.`lang_id`  = ' . $langid;
            }
        } else {
            if (!empty($buIds)) {
                $where = '    where  t.superior_id=0 and  l.`lang_id`  = ' . $this->mrlangid . ' and t.id in(' . $buIds . ')';
            } else {
                $where = '    where  t.superior_id=0 and  l.`lang_id`  = ' . $this->mrlangid;
            }
        }

        if ($langid == $this->mrlangid) {
            $sql = 'SELECT  t.`id`,l.`type_name`,t.`room_img`  FROM  ' . DB_PREFIX . 'room_type AS t
                 LEFT JOIN  ' . DB_PREFIX . 'room_type_lang  AS  l  ON  t.`id` = l.`type_id`  ' . $where . ' and l.type_name not like "%奶茶%"  order by t.sort';
        } else {
            $sql = 'SELECT  t.`id`,l.`type_name`,t.`room_img`  FROM  ' . DB_PREFIX . 'room_type AS t
                 LEFT JOIN  ' . DB_PREFIX . 'room_type_lang  AS  l  ON  t.`id` = l.`type_id`  ' . $where . ' and l.type_name not like "%Tea%"  order by t.sort';
        }


        $data = $roomTypeMod->querySql($sql);

        return $data;
    }

    public function getRoomType($langid, $buIds) {

        $roomTypeMod = &m('roomType');
        if (!empty($langid)) {
            if (!empty($buIds)) {
                $where = '    where  t.superior_id=0 and  l.`lang_id`  = ' . $langid . ' and t.id in(' . $buIds . ')';
            } else {
                $where = '    where  t.superior_id=0 and  l.`lang_id`  = ' . $langid;
            }
        } else {
            if (!empty($buIds)) {
                $where = '    where  t.superior_id=0 and  l.`lang_id`  = ' . $this->mrlangid . ' and t.id in(' . $buIds . ')';
            } else {
                $where = '    where  t.superior_id=0 and  l.`lang_id`  = ' . $this->mrlangid;
            }
        }
        /* var_dump($langid);
          var_dump($this->langid); */
        if ($langid == $this->mrlangid) {

            $sql = 'SELECT  t.`id`,l.`type_name`,t.`room_img`  FROM  ' . DB_PREFIX . 'room_type AS t
                 LEFT JOIN  ' . DB_PREFIX . 'room_type_lang  AS  l  ON  t.`id` = l.`type_id`  ' . $where . ' and l.type_name like "%奶茶%"  order by t.sort';
        } else {
            $sql = 'SELECT  t.`id`,l.`type_name`,t.`room_img`  FROM  ' . DB_PREFIX . 'room_type AS t
                 LEFT JOIN  ' . DB_PREFIX . 'room_type_lang  AS  l  ON  t.`id` = l.`type_id`  ' . $where . ' and  l.type_name like "%Tea%"  order by t.sort';
        }

        $data = $roomTypeMod->querySql($sql);
        return $data;
    }

    /**
     * 2级业务类型
     * @author wangh
     * @date 2017/09/13
     */
    public function getgoodRoomTypearr($langid, $filter_param, $rtid, $arr) {
        $roomTypeMod = &m('roomType');
        $typeFilter = !empty($filter_param['t']) ? explode('_', $filter_param['t']) : array();
        if ($rtid == '') {
            $rtid = $arr['id']; //业务默认id
        } else {
            $rtid = !empty($_REQUEST['rtid']) ? $_REQUEST['rtid'] : ''; //业务
        }

        unset($filter_param['t']);
        $uri = urldecode(http_build_query($filter_param));

        if (!empty($langid)) {
            $where = '    where  t.superior_id=' . $rtid . ' and  l.`lang_id`  = ' . $langid;
        } else {
            $where = '    where  t.superior_id=' . $rtid . ' and  l.`lang_id`  = ' . $this->mrlangid;
        }
        $sql = 'SELECT  t.`id`,l.`type_name`,t.`room_adv_img`  FROM  ' . DB_PREFIX . 'room_type AS t
                 LEFT JOIN  ' . DB_PREFIX . 'room_type_lang  AS  l  ON  t.`id` = l.`type_id`  ' . $where . ' order by t.sort';
        $data = $roomTypeMod->querySql($sql);

        foreach ($data as $key => $val) {
            if (empty($typeFilter)) {  //当没有 类型 筛选的 时候
                $data[$key]['href'] = $baseUrl . $uri . '&t=' . $val['id'];
            } else {  //当有 类型 筛选的 时候
                if (in_array($val['id'], $typeFilter)) {
                    $data[$key]['ison'] = 1;
                    $a = array();
                    $a[] = $val['id'];
                    $dif = array_diff($typeFilter, $a);
                    $itemd = implode('_', $dif);
                    if (!empty($itemd)) {
                        $data[$key]['href'] = $baseUrl . $uri . '&t=' . $itemd;
                    } else {
                        $data[$key]['href'] = $baseUrl . $uri;
                    }
                    //加入筛选菜单
                    $menu = array(' <a href=' . $data[$key]['href'] . ' class="tag">
                                  ' . $val['type_name'] . '<span class="del or">X</span>
                                </a>');
                    $this->filterMenu = array_merge($this->filterMenu, $menu);
                } else {
                    $data[$key]['ison'] = 0;
                    $b = array();
                    $b[] = $val['id'];
                    $meg = array_merge($typeFilter, $b); //取合集
                    $itemm = implode('_', $meg);
                    $data[$key]['href'] = $baseUrl . $uri . '&t=' . $itemm;
                }
            }
        }

        return $data;
    }

    /**
     * 测试项目接口
     * @author lvji
     * @date 2016-09-12
     */
    public function test() {
        $info = array();
        $info['msg'] = '测试接口';
        $data = $this->setData($info);
        echo json_encode($data);
    }

    /**
     * 退出系统
     * @author lvji
     * @date 2015-03-10
     */
    public function logout() {
        unset($_SESSION['adminId']);
        //第一步：删除服务器端
        session_destroy();
        //第二步：删除实际的session
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600);
        }
        $_SESSION = array();  //第三步：删除$_SESSION全部变量数组
        $info['msg'] = '退出成功';
        $this->setData($info);
    }

    /**
     * 获取版本号
     * @author lvji
     * @date 2015-03-20
     */
    public function fetchVersion() {
        $info = array();
        $info['version'] = APPVERSION;
        $this->setData($info);
    }

    /**
     * 商品风格
     * @author wangh
     * @date 2017/09/13
     */
    public function getGoodStyle($langid, $filter_param, $baseUrl) {

        $goodstyleMod = &m('goodsStyle');
        $styleFilter = !empty($filter_param['s']) ? explode('_', $filter_param['s']) : array();
        unset($filter_param['s']);
        $uri = urldecode(http_build_query($filter_param));

        if (!empty($langid)) {
            $where = '    where  l.`lang_id`  = ' . $langid;
        } else {
            $where = '    where  l.`lang_id`  = ' . $this->mrlangid;
        }
        $sql = 'SELECT t.`id`,l.`style_name` FROM  ' . DB_PREFIX . 'goods_style AS t
                 LEFT JOIN  ' . DB_PREFIX . 'goods_style_lang AS l ON  t.`id` = l.`style_id`  ' . $where;
        $data = $goodstyleMod->querySql($sql);
        foreach ($data as $key => $val) {
            if (empty($styleFilter)) { //当没有风格 筛选的 时候
                $data[$key]['href'] = $baseUrl . $uri . '&s=' . $val['id'];
            } else {  //当有 风格 筛选的 时候
                if (in_array($val['id'], $styleFilter)) {  // 减去
                    $data[$key]['ison'] = 1;
                    $a = array();
                    $a[] = $val['id'];
                    $dif = array_diff($styleFilter, $a); //取差集
                    $itemd = implode('_', $dif);
                    if (!empty($itemd)) {
                        $data[$key]['href'] = $baseUrl . $uri . '&s=' . $itemd;
                    } else {
                        $data[$key]['href'] = $baseUrl . $uri;
                    }
                    //加入筛选菜单
                    $menu = array(' <a href=' . $data[$key]['href'] . ' class="tag">
                                  ' . $val['style_name'] . '<span class="del or">X</span>
                                </a>');
                    $this->filterMenu = array_merge($this->filterMenu, $menu);
                } else {  //加上
                    $data[$key]['ison'] = 0;
                    $b = array();
                    $b[] = $val['id'];
                    $meg = array_merge($styleFilter, $b); //取合集
                    $itemm = implode('_', $meg);
                    $data[$key]['href'] = $baseUrl . $uri . '&s=' . $itemm;
                }
            }
        }

        return $data;
    }

    /**
     * 获取国家下的站点
     */
    public function ajaxCateStores() {
        $storeMod = &m('store');
        $cateid = !empty($_REQUEST['cateid']) ? $_REQUEST['cateid'] : $this->mrstorecate;  //所选的站点国家

        $sql = 'SELECT  id,store_name,lang_id,currency_id    FROM  ' . DB_PREFIX . 'store  WHERE  is_open =1 AND   store_cate_id = ' . $cateid;

        $sql = 'SELECT s.id,l.store_name,s.currency_id,s.lang_id  FROM  ' . DB_PREFIX . 'store AS s
                 LEFT JOIN  ' . DB_PREFIX . 'store_lang AS l ON s.`id` = l.`store_id` 
                  WHERE l.lang_id =' . $this->langid . "  and s.is_open =1  and s.store_cate_id =" . $cateid;
//        $sql = 'SELECT  id,store_name,lang_id,currency_id    FROM  ' . DB_PREFIX . 'store  WHERE  is_open =1 AND   store_cate_id = ' . $cateid;

        $sql = 'SELECT s.id,l.store_name,s.currency_id,s.lang_id  FROM  ' . DB_PREFIX . 'store AS s
                 LEFT JOIN  ' . DB_PREFIX . 'store_lang AS l ON s.`id` = l.`store_id` 
                  WHERE l.lang_id =' . $this->langid . "  and s.is_open =1  and s.store_cate_id =" . $cateid;
//        $sql = 'SELECT  id,store_name,lang_id,currency_id    FROM  ' . DB_PREFIX . 'store  WHERE  is_open =1 AND   store_cate_id = ' . $cateid;

        $res = $storeMod->querySql($sql);
        echo json_encode($res);
    }

    /**
     * 语言切换的ajax交互
     * @author wangh
     * @date 2017/08/22
     */
    public function ajaxLangUrll() {
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;
        $cururl = $_REQUEST['cururl'];  //当前页面的url
        $latlon = $_REQUEST['latlon'];

        //
        if (empty($cururl)) {
            $cururl = '?storeid=' . $this->mrstoreid . '&lang=' . $this->mrlangid . '&latlon=' . $latlon;
        }
        if ($cururl == '?app=default&act=index&latlon=' . $latlon) {
            $cururl = '?storeid=' . $this->mrstoreid . '&lang=' . $this->mrlangid . '&latlon=' . $latlon;
        }
        $arr = explode("&", $cururl);

        foreach ($arr as $key => $val) {
            if (strpos($val, 'lang') !== false) {
                $arrlang = explode('=', $val);
                array_pop($arrlang);
                array_push($arrlang, $lang);
                $arr[$key] = implode('=', $arrlang);
            }
        }
        $reurl = implode('&', $arr);

        $urljson = json_encode(array('reurl' => $reurl));
        echo $urljson;
    }

    public function ajaxLangUrl() {
        $langid = !empty($_REQUEST['langid']) ? $_REQUEST['langid'] : $this->mrlangid;
        $cururl = $_REQUEST['cururl'];  //当前页面的url
        //
        if (empty($cururl)) {
            $cururl = '?storeid=' . $this->mrstoreid . '&lang=' . $this->mrlangid;
        }
        if ($cururl == '?app=default&act=index') {
            $cururl = '?storeid=' . $this->mrstoreid . '&lang=' . $this->mrlangid;
        }
        $arr = explode("&", $cururl);
        foreach ($arr as $key => $val) {
            if (strpos($val, 'lang') !== false) {
                $arrlang = explode('=', $val);
                array_pop($arrlang);
                array_push($arrlang, $langid);
                $arr[$key] = implode('=', $arrlang);
            }
        }
        $reurl = implode('&', $arr);
        $urljson = json_encode(array('reurl' => $reurl));
        echo $urljson;
    }

    /**
     * @param $arr
     * @return array
     */
    public function getYiweiArr($arr) {
        $data = array();
        foreach ($arr as $key => $val) {
            $data[] = $val['id'];
        }
        return $data;
    }

    /**
     * 获取商品的多语言信息
     * @param $goodsId
     * @param $langid
     * @return mixed
     */
    public function getStoreGoodsLang($goodsId, $langid) {
        $storeGLMod = &m('storeGoodsLang');
        $sql = 'SELECT  goods_name  FROM  ' . DB_PREFIX . 'store_goods_lang   WHERE  store_good_id = ' . $goodsId . ' AND   lang_id =' . $langid;
        $res = $storeGLMod->querySql($sql);
        return $res[0]['goods_name'];
    }

    /**
     * 商品品牌
     * @author wangh
     * @date 2017/09/13
     */
    public function getGoodsBrand($langid, $filter_param, $baseUrl) {
        $goodsbandMod = &m('goodsBrand');
        $brandFilter = !empty($filter_param['b']) ? explode('_', $filter_param['b']) : array();
        unset($filter_param['b']);
        $uri = urldecode(http_build_query($filter_param));

        if (!empty($langid)) {
            $where = '    where  l.`lang_id`  = ' . $langid;
        } else {
            $where = '    where  l.`lang_id`  = ' . $this->mrlangid;
        }
        $sql = 'SELECT  b.id,l.`brand_name`as bname  FROM  ' . DB_PREFIX . 'goods_brand AS b
                LEFT JOIN  ' . DB_PREFIX . 'goods_brand_lang AS l ON b.id=l.`brand_id`  ' . $where;
        $data = $goodsbandMod->querySql($sql);

        foreach ($data as $key => $val) {
            if (empty($brandFilter)) {  //当没有品牌 筛选的 时候
                $data[$key]['href'] = $baseUrl . $uri . '&b=' . $val['id'];
            } else {  //当有品牌 筛选的 时候
                if (in_array($val['id'], $brandFilter)) {  // 减去
                    $data[$key]['ison'] = 1;
                    $a = array();
                    $a[] = $val['id'];
                    $dif = array_diff($brandFilter, $a); //取差集
                    $itemd = implode('_', $dif);
                    if (!empty($itemd)) {
                        $data[$key]['href'] = $baseUrl . $uri . '&b=' . $itemd;
                    } else {
                        $data[$key]['href'] = $baseUrl . $uri;
                    }
                    //加入筛选菜单
                    $menu = array(' <a href=' . $data[$key]['href'] . ' class="tag">
                                  ' . $val['bname'] . '<span class="del or">X</span>
                                </a>');
                    $this->filterMenu = array_merge($this->filterMenu, $menu);
                } else {  //加上
                    $data[$key]['ison'] = 0;
                    $b = array();
                    $b[] = $val['id'];
                    $meg = array_merge($brandFilter, $b); //取合集
                    $itemm = implode('_', $meg);
                    $data[$key]['href'] = $baseUrl . $uri . '&b=' . $itemm;
                }
            }
        }

        return $data;
    }

    /**
     * 获取首页Banner
     * @author wangh
     * @date 2017/08/22
     */
    public function getBanner($positionName, $storeid) {
        $advMod = &m('adv');
        $where = '   where 1=1  and  p.`position_num`  = ' . $positionName . '  and  a.store_id=' . $storeid;
        $sql = 'SELECT   a.ad_code,a.goods_id,p.`position_id`,p.`position_num`,a.store_id,a.ad_name
                FROM  ' . DB_PREFIX . 'ad  AS a
                LEFT JOIN  ' . DB_PREFIX . 'ad_position  AS p  ON a.`ps_id` = p.`position_id` ' . $where;
        $res = $advMod->querySql($sql);
        return $res;
    }

    /*
     * 获取四种优惠
     * @author lee
     * @date 2017-11-29 14:14:51
     * @param storeid 区域ID
     */

    public function getGoodsDiscount($storeid, $latlon) {
        $lang_id = $this->langid;
        //取四种优惠商品
        $now = time();
        $combinedMod = &m('combinedSale'); //组合销售
        $promSaleMod = &m('goodProm'); //商品促销
        $groupMod = &m('groupbuy'); //团购
        $skillMod = &m('spikeActivity'); //秒杀
        $prom_field = "s.id as prom_id,sgl.original_img as goods_img,s.prom_name,g.goods_name,g.discount_rate as prom_rate,s.end_time,g.goods_id,goods_price as o_price,discount_price as price,g.goods_key";
        $prom_sql = "select " . $prom_field . " from "
                . DB_PREFIX . "promotion_sale as s left join "
                . DB_PREFIX . "promotion_goods as g on s.id=g.prom_id left join  "
                . DB_PREFIX . "store_goods as sg on g.goods_id = sg.id left join  "
                . DB_PREFIX . "goods as sgl on sg.goods_id = sgl.goods_id  where s.store_id=" . $storeid . " and s.status=2 and sg.mark=1 and sg.is_on_sale=1 and s.mark=1 and s.start_time<=" . $now . " and s.end_time>=" . $now;
        $prom_arr = $promSaleMod->querySql($prom_sql);


        if ($prom_arr) {
            $prom_lang = $this->getGoodsLang($prom_arr[0]['goods_id'], $lang_id);
            if ($prom_lang) {
                $prom_arr[0]['goods_name'] = $prom_lang['goods_name'];
            }
            $prom_arr[0]['url'] = "?app=goods&act=goodInfo&storeid={$storeid}&lang={$lang_id}&auxiliary=0&source=3&cid=" . $prom_arr[0]['prom_id'] . "&gid=" . $prom_arr[0]['goods_id'] . "&key=" . $prom_arr[0]['goods_key'] . "&latlon=" . $latlon;
        }
        $sqle = "SELECT  c.id as prom_id,sgl.original_img as goods_img,c.goods_name,c.rebate as prom_rate,c.end_time,c.goods_id,c.goods_price as o_price,c.group_goods_price as price  FROM  "
                . DB_PREFIX . "goods_group_buy  as c left join  "
                . DB_PREFIX . "store_goods as sg on c.goods_id = sg.id left join  "
                . DB_PREFIX . "goods as sgl on sg.goods_id = sgl.goods_id  where c.store_id=" . $storeid . " and c.start_time<=" . $now . " and c.end_time>=" . $now . " and c.is_end=1 and c.mark=1 and sg.mark=1 and sg.is_on_sale=1";
        $group_arr = $groupMod->querySql($sqle);
        if ($group_arr) {
            $group_lang = $this->getGoodsLang($group_arr[0]['goods_id'], $lang_id);
            if ($group_lang) {
                $group_arr[0]['goods_name'] = $group_lang['goods_name'];
            }
            $group_arr[0]['url'] = "?app=goods&act=goodInfo&storeid={$storeid}&lang={$lang_id}&auxiliary=0&source=2&cid=" . $group_arr[0]['prom_id'] . "&gid=" . $group_arr[0]['goods_id'] . "&latlon=" . $latlon;
        }
        $skill_field = "SELECT c.id as prom_id,c.name as prom_name,c.goods_name,c.discount as prom_rate,c.end_time,sgl.original_img as goods_img,c.store_goods_id,c.price ,c.o_price FROM  "
                . DB_PREFIX . "spike_activity  as c left join  "
                . DB_PREFIX . "store_goods as sg on c.store_goods_id = sg.id left join  "
                . DB_PREFIX . "goods as sgl on sg.goods_id = sgl.goods_id where c.store_id=" . $storeid . " and c.start_time<=" . $now . " and sg.mark=1 and sg.is_on_sale=1 and c.end_time>=" . $now;
        $skill_arr = $skillMod->querySql($skill_field);
        if ($skill_arr) {
            $skill_lang = $this->getGoodsLang($skill_arr[0]['store_goods_id'], $lang_id);
            if ($skill_lang) {
                $skill_arr[0]['goods_name'] = $skill_lang['goods_name'];
            }
            $skill_arr[0]['url'] = "?app=goods&act=goodInfo&storeid={$storeid}&lang={$lang_id}&auxiliary=0&source=1&cid=" . $skill_arr[0]['prom_id'] . "&gid=" . $skill_arr[0]['store_goods_id'] . "&latlon=" . $latlon;
        }
        $arr[] = $prom_arr[0];
        $arr[] = $group_arr[0];
        $arr[] = $skill_arr[0];
        return $arr;
    }

    /**
     * 获取为你推荐的商品
     * @author wangh
     * @date 2017/08/22
     */
    public function recommendForYou($storeid, $orderBy, $limit = 0) {
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
            $where = '  WHERE   s.cat_id  in (' . $cids . ')  and  s.mark =1  AND  s.is_on_sale =1  AND s.store_id = ' . $storeid . ' AND l.`lang_id` =' . $this->langid . ' AND s.is_recom=1';
            $sql2 = 'SELECT s.id,s.`goods_id`,s.`cat_id`,s.`store_id`,l.`goods_name`,l.`lang_id`,s.`shop_price`,s.`market_price`,s.`brand_id`,slg.`original_img`  FROM  '
                    . DB_PREFIX . 'store_goods AS s LEFT JOIN  '
                    . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id` LEFT JOIN  '
                    . DB_PREFIX . 'goods AS slg ON s.`goods_id` = slg.`goods_id`' . $where;
            $sql2 .= '  ORDER  BY  s.goods_salenum  desc ,  s.id desc   LIMIT 4';
        } else {
            $where = '  WHERE   s.cat_id  in (' . $cids . ')  and  s.mark =1  AND  s.is_on_sale =1  AND s.store_id = ' . $storeid . ' AND l.`lang_id` =' . $this->langid . ' AND s.is_recommend=1';
            $sql2 = 'SELECT s.id,s.`goods_id`,s.`cat_id`,s.`store_id`,l.`goods_name`,l.`lang_id`,s.`shop_price`,s.`market_price`,s.`brand_id`,slg.`original_img` FROM  '
                    . DB_PREFIX . 'store_goods AS s LEFT JOIN  '
                    . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id` LEFT JOIN  '
                    . DB_PREFIX . 'goods AS slg ON s.`goods_id` = slg.`goods_id`' . $where;
            $sql2 .= $orderBy;
        }

        $arr = $storeGoodsMod->querySql($sql2);


        foreach ($arr as $key => $val) {
            //店铺商品打折
            $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $this->storeid;
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

    public function recommendForYou1() {
        $ctgMod = &m('goodsClass');
        $userId = 359;
        $sql = "select  `id`   from " . DB_PREFIX . "goods_category ";
        $res = $ctgMod->querySql($sql);
        $cid = array();
        foreach ($res as $val) {
            $cid[] = $val['id'];
        }
        $cids = implode(',', $cid);
        //

        $storeGoodsMod = &m('areaGood');
        $where = '  WHERE   s.cat_id  in (' . $cids . ')  and  s.mark =1  AND  s.is_on_sale =1  AND s.store_id = ' . 1 . ' AND l.`lang_id` =' . 29 . ' AND s.is_recom=1';
        $sql2 = 'SELECT s.id,s.`goods_id`,s.`cat_id`,s.`store_id`,l.`goods_name`,l.`lang_id`,s.`shop_price`,s.`market_price`,s.`brand_id`,slg.`original_img`  FROM  '
                . DB_PREFIX . 'store_goods AS s LEFT JOIN  '
                . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id` LEFT JOIN  '
                . DB_PREFIX . 'goods AS slg ON s.`goods_id` = slg.`goods_id`' . $where;
        $sql2 .= '  ORDER  BY  s.id desc   LIMIT 4';


        $arr = $storeGoodsMod->querySql($sql2);


        foreach ($arr as $key => $val) {
            //店铺商品打折
            $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=1';
            $store_arr = $storeGoodsMod->querySql($store_sql);
            $arr[$key]['shop_price'] = number_format($val['shop_price'] * $store_arr[0]['store_discount'], 2);
            //为你推荐的收藏商品
            $sql_collection = 'select * from ' . DB_PREFIX . 'user_collection where user_id=' . $userId . ' and store_id=1';
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
     * 手机短信验证码
     * @author zhangr
     * @date 2017-12-06
     */
    public function phoneCode() {
        include_once ROOT_PATH . "/includes/AliDy/sendSms.lib.php";
        $phone = !empty($_REQUEST['phone']) ? $_REQUEST['phone'] : '';
        if ($phone) {
            if (!preg_match("/^\d{11}$/i", $phone)) {
                $this->setData(array(), 0, '手机号码格式不对！');
                exit;
            }
        } else {
            $this->setData(array(), 0, '请填写手机号码！');
            exit;
        }
        $params = array();
        $params['PhoneNumbers'] = $phone;
        $params['SignName'] = "七一一家居网";
        $params['TemplateCode'] = 'SMS_117585003';
        $code = $this->getCode();
        $params['TemplateParam'] = array(
            "code" => $code,
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
            $smsMod->doInsert($data);
            /*      $systemCodeMod->doInsert($data); */
            echo "验证发送成功！";
        } else {
            echo "验证发送失败！";
        }
    }

    /**
     * 生成验证码
     * @author xiayy
     * @date 2016-11-11
     */
    public function getCode($length = 6) {
        $min = pow(10, ($length - 1));
        $max = pow(10, $length) - 1;
        return rand($min, $max);
    }

    /*
     * 我的分享
     * @author wangs
     * @date 2018-8-22
     */

    public function myShare() {
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;  //多语言商品
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;  //所选的站点id
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $this->assign('auxiliary', $auxiliary);
        $this->assign('latlon', $latlon);
        $this->assign('lang', $lang);
        $this->assign('storeid', $storeid);
        $user = &m('user'); //文章
        $userId = !empty($_REQUEST['userId']) ? $_REQUEST['userId'] : $this->userId;  //获取Id
        $sql = 'select * from ' . DB_PREFIX . 'user where id = ' . $userId . ' and mark =1';
        $res = $user->querySql($sql);
        $this->assign('res', $res[0]);
        //映射页面
        $this->display("userCenter/share.html");
    }
}
?>