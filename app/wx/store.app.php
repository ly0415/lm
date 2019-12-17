<?php

/**
 * 文章
 *
 *
 */
class StoreApp extends BaseWxApp {

    private $storeMod;



    public function __construct() {
        parent::__construct();
        $this->storeMod = &m('store');

    }
    public function __destruct() {
        
    }


    public function index()
    {
        $storeMod = &m('store');
        $this->load($this->shorthand, 'WeChat/goods');
        $this->assign('langdata', $this->langData);
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;
        $this->assign('lang', $lang);
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;
        $this->assign('storeid', $storeid);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        $swhere = " where sl.distinguish=0";
        $latlon = explode(',',$latlon);
        $lng=$latlon[0];//经度
        $lat=$latlon[1];//纬度
        $this->assign('lng',$lng);
        $this->assign('lat',$lat);
        if (empty($this->userId)) {
            $swhere .= ' AND s.store_type in (2,3) AND s.is_open=1 ';
            $ssql = 'SELECT  s.id,s.logo,s.longitude,s.latitude,s.distance,sl.`store_name` AS sltore_name FROM  ' . DB_PREFIX . 'store AS s LEFT JOIN  ' . DB_PREFIX . 'store_lang AS sl ON sl.`store_id` = s.`id` and  sl.lang_id=' . $this->langid . ' LEFT JOIN  ' . DB_PREFIX . 'language AS l ON s.`lang_id` = l.`id` LEFT JOIN  ' . DB_PREFIX . 'currency AS c ON s.`currency_id` = c.`id`' . $swhere;
            $sData = $storeMod->querySql($ssql);
        } else {
            $uSql = "SELECT odm_members FROM " . DB_PREFIX . "user WHERE id=" . $this->userId;
            $uData = $storeMod->querySql($uSql);
            if ($uData[0]['odm_members'] != 1) {
                $swhere .= ' AND s.store_type in (2,3) AND s.is_open=1';
            }
            $ssql = 'SELECT  s.id,s.logo,s.longitude,s.latitude,s.distance,sl.`store_name` AS sltore_name FROM  ' . DB_PREFIX . 'store AS s LEFT JOIN  ' . DB_PREFIX . 'store_lang AS sl ON sl.`store_id` = s.`id` and  sl.lang_id=' . $this->langid . ' LEFT JOIN  ' . DB_PREFIX . 'language AS l ON s.`lang_id` = l.`id` LEFT JOIN  ' . DB_PREFIX . 'currency AS c ON s.`currency_id` = c.`id`' . $swhere;
            $sData = $storeMod->querySql($ssql);
        }
        $this->assign('user_id', $this->userId);
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
        $this->assign('sData',$sData);
            $this->display('listPage/storeList.html');

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
}
