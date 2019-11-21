<?php

/**
 * 附近门店
 * @author wangshuo
 * @date 2018-04-18
 */
class mapApp extends BaseWxApp {

    /**
     * 附近门店首页
     * @author wangshuo
     * @date 2018-04-18
     */
    public function index() {
        $lang_id = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $storeid=!empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;
        $this->assign('latlon', $latlon);
        $this->assign('lang',$lang_id);
        $this->assign('storeid',$storeid);
        $this->load($this->shorthand, 'WeChat/goods');
        $this->assign('langdata', $this->langData);
        $this->assign('lang', $lang_id);
        $this->display('public/map.html');
    }

    /**
     * 获取附近门店
     * @author wanyan
     * @date 2017-08-31
     */
    public function getStore() {
        if ($_SESSION['userId']) {
            $user_id = $_SESSION['userId'];
            $userMod = &m('user');
            $sql = 'SELECT *  FROM  ' . DB_PREFIX . 'user WHERE  id  =' . $user_id; //odm_members
            $datas = $userMod->querySql($sql);
            if ($datas[0]['odm_members'] == 0) {
                $where = ' and s.store_type < 4 ';
            } else {
                $where = '';
            }
        } else {
            $where = ' and s.store_type<4 ';
        }
        $latlon=!empty($_REQUEST['latlon']) ? $_REQUEST['latlon'] : '';
        $lang=!empty($_REQUEST['lang']) ? $_REQUEST['lang']:'';
        $storeid=!empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : '';


        $storeMod = &m('store');
        $sql = 'SELECT s.id,l.store_name,s.lang_id,s.addr_detail,s.longitude,s.latitude,s.store_type,s.store_mobile  FROM  ' . DB_PREFIX . 'store AS s
                 LEFT JOIN  ' . DB_PREFIX . 'store_lang AS l ON s.`id` = l.`store_id` 
                  WHERE s.is_open = 1  and l.distinguish = 0  and  l.lang_id =' . $this->langid . $where . '  ORDER BY s.id';
        $data = $storeMod->querySql($sql);
        foreach($data as $key=>$val){
            $data[$key]['latlon']=$latlon;
            $busSql = "SELECT buss_id FROM " . DB_PREFIX . 'store_business WHERE  store_id=' .$val['id'];
            $busData = $storeMod->querySql($busSql);
            $data[$key]['b_id']=$busData[0]['buss_id'];
        }
        echo json_encode($data);
        die;
    }






    public function search(){
        $lang_id = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $storeid=!empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;
        $qu=!empty($_REQUEST['qu']) ? $_REQUEST['qu'] : '';
        $this->assign('latlon', $latlon);
        $this->assign('qu',$qu);
        $latlon = explode(',', $latlon);
        $lng = $latlon[0]; //经度
        $lat = $latlon[1]; //纬度
        $this->assign('lng', $lng);
        $this->assign('lat', $lat);
        $this->assign('storeid',$storeid);
        $this->load($this->shorthand, 'WeChat/goods');
        $this->assign('langdata', $this->langData);
        $this->assign('lang', $lang_id);
        $this->display('public/map1.html');
    }


    public function getLatlon(){
        $address=!empty($_REQUEST['address']) ? $_REQUEST['address'] : '';
        $store_id=!empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : '';
        $lang=!empty($_REQUEST['lang']) ? $_REQUEST['lang'] : '';
        $qu=!empty($_REQUEST['qu']) ? $_REQUEST['qu'] : '';
        $data=file_get_contents('http://api.map.baidu.com/geocoder/v2/?address='.$address.'&output=json&ak=CmfcOlGRQE7OztyHtDGLoiiNGYUu37Te');
        $data=json_decode($data);
        $lat=$data->result->location->lat;
        $lng=$data->result->location->lng;
        $latlon=$lng.','.$lat;
        $info['url']="?app=map&act=search&storeid={$store_id}&lang={$lang}&auxiliary=0&latlon={$latlon}&qu={$qu}";
        $this->setData($info,1,'');

    }




    public function addAddress(){
        $returnUrl = !empty($_REQUEST['returnUrl']) ? $_REQUEST['returnUrl'] : '';
        $this->assign('returnUrl', $returnUrl);
        $lang_id = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $storeid=!empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;
        $this->assign('latlon', $latlon);
        $latlon = explode(',', $latlon);
        $lng = $latlon[0]; //经度
        $lat = $latlon[1]; //纬度
        $this->assign('lng', $lng);
        $this->assign('lat', $lat);
        $this->assign('lang',$lang_id);
        $this->assign('storeid',$storeid);
        $this->load($this->shorthand, 'WeChat/address');
        $this->assign('langdata', $this->langData);
        $this->assign('lang', $lang_id);
        $this->display('userCenter/add-address.html');
    }




}
