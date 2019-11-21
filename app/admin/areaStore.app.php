<?php

/**
 * 区域分类管理模块
 * @author wanyan
 * @date 2017-08-29
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class areaStoreApp extends BackendApp
{

    private $storeCateMod;
    private $storeMod;
    private $accountMod;
    private $cityMod;
    private $countryMod;
    private $storeUserMod;
    private $storeSiteMod;
    private $langMod;
    private $currencyMod;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->storeCateMod = &m('storeCate');
        $this->storeMod = &m('store');
        $this->accountMod = &m('account');
        $this->cityMod = &m('city');
        $this->countryMod = &m('country');
        $this->storeUserMod = &m('storeUser');
        $this->storeSiteMod = &m('site');
        $this->langMod = &m('language');
        $this->currencyMod = &m('currency');
    }

    /**
     * 区域店铺档案
     * @author wanyan
     * @date 2017-08-30
     */
    public function index(){
        $store_type = !empty($_REQUEST['store_cate_id']) ? htmlspecialchars($_REQUEST['store_cate_id']) : '';
        $store_name = !empty($_REQUEST['store_name']) ? trim($_REQUEST['store_name']) : '';
        $where = " where sl.distinguish=0";

//        if (!empty($store_type)) {
//            $where .= " and  s.`store_type` = '{$store_type}'";
//        }
        if (!empty($store_name)) {
            $where .= ' and  sl.`store_name` like "%' . addslashes(addslashes($store_name)) . '%"';
        }
        //判断所属区域国家 modify by lee
        if ($this->roleCountry) {
            $where .= " and s.store_cate_id=" . $this->roleCountry;
        } else {
            if (!empty($store_type)) {
                $where .= " and s.store_cate_id =" . $store_type;
            }
        }
        //end
        $where .= " order by s.add_time desc,s.sort desc";
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "store as s " . $where;
        $totalCount = $this->storeMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;

        $sql = 'SELECT  s.*,l.`name` AS lname ,c.`name` AS cname,sl.`store_name` AS sltore_name     FROM  ' . DB_PREFIX . 'store AS s
                LEFT JOIN  ' . DB_PREFIX . 'store_lang AS sl ON sl.`store_id` = s.`id` and  sl.lang_id=' . $this->lang_id .
            ' LEFT JOIN  ' . DB_PREFIX . 'language AS l ON s.`lang_id` = l.`id`
                LEFT JOIN  ' . DB_PREFIX . 'currency AS c ON s.`currency_id` = c.`id`' . $where;
        $rs = $this->storeMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        foreach ($rs['list'] as $k => $v) {
            $storeCateName = $this->getCate($v['store_cate_id']);
            $rs['list'][$k]['store_cate_name'] = $storeCateName['cate_name'];
            $rs['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            if ($v['is_hot'] == 1) {
                $rs['list'][$k]['hot_name'] = $this->langDataBank->public->yes;
            } else {
                $rs['list'][$k]['hot_name'] = $this->langDataBank->public->no;
            }
            if ($v['is_open'] == 1) {
                $rs['list'][$k]['open_name'] = $this->langDataBank->public->yes;
            } else {
                $rs['list'][$k]['open_name'] = $this->langDataBank->public->no;
            }
            if ($v['is_site'] == 1) {
                $rs['list'][$k]['site_name'] = $this->langDataBank->public->yes;
            } else {
                $rs['list'][$k]['site_name'] = $this->langDataBank->public->no;
            }
            if ($v['add_time']) {
                $rs['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            } else {
                $rs['list'][$k]['add_time'] = '';
            }
            $rs['list'][$k]['store_name'] = stripslashes($v['store_name']);
            $rs['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
//            $rs['list'][$k]['sort'] = $total - $k - 20 * ($p - 1); //倒叙
        }
//        echo '<pre>';
//        var_dump($rs['list']);die;
        $this->assign('store_cate_id', $store_type);
        $this->assign('p', $p);
        $this->assign('store_name', stripslashes($store_name));
        $this->assign('list', $rs['list']);
        $this->assign('page', $rs['ph']);
        $this->assign('countrys', $this->getACountry($this->lang_id));
        $this->assign('roleCountry', $this->roleCountry);
        $this->display('areaStore/index.html');
    }

    /**
     * 店铺分类
     * @author wanyan
     * @date 2017-08-30
     */
    public function getCate($store_cate_id)
    {
        $sql = 'SELECT c.id,l.`lang_id`,l.`cate_name`  FROM bs_store_cate AS c LEFT JOIN bs_store_cate_lang AS l ON c.id = l.`cate_id`
                 WHERE c.id = ' . $store_cate_id . ' and  l.lang_id  =' . $this->lang_id;
        $rs = $this->storeCateMod->querySql($sql);
        return $rs[0];
    }

    /**
     * 店铺国家
     * @author wanyan
     * @date 2017-11-30
     */
    public function getACountry($lang)
    {
        $sql = "select SC.`id`,SCL.`cate_name`  from  " . DB_PREFIX . "store_cate AS SC LEFT JOIN " . DB_PREFIX . "store_cate_lang  
        AS SCL ON SC.id = SCL.cate_id where SCL.lang_id = " . $lang . " and SC.is_open=1";
        $rs = $this->storeCateMod->querySql($sql);
        return $rs;
    }

    /**
     * 处理数组
     * @author wanyan
     * @date 2017-11-30
     */
    public function handleArray()
    {

    }

    /**
     * 区域店铺档案
     * @author wanyan
     * @date 2017-08-30
     */
    public function add(){
        if ($this->shorthand == 'ZH') {
            $shipping_method = G('shipping_method');
            $store_type = G('store_type');
        } else if ($this->shorthand == 'EN'){
            $shipping_method = G('shipping_method_en');
            $store_type = G('store_type_en');
        }
//        $langInfo = $this->langMod->getLanguage();
//        $currencyInfo = $this->currencyMod->getCurrency();
//        var_dump($this->cityMod->getParentNodes());die;
        $lang = $this->langMod->getLanguage();
        $html = '';
        $storeHtml = '';
        foreach ($lang as $val) {
            $html .= ' <span class="mt10 mb5 inblock">' . $val['name'] . '：</span><input type="text" class="form-control" name="store_tag[' . $val['id'] . ']" >';
            $storeHtml .= ' <span class="mt10 mb5 inblock">' . $val['name'] . '：</span><input type="text" class="form-control" name="store_name[' . $val['id'] . ']" >';
        }
        $this->assign('storeHtml', $storeHtml);
        $this->assign('html', $html);
        //多语言版本
        $langMod = &m('language');
        $sql = 'select  id,name  from   ' . DB_PREFIX . 'language';
        $data = $langMod->querySql($sql);
        $htmls = '';
        foreach ($data as $val) {
            $htmls .= ' <span class="mt10 mb5 inblock">' . $val['name'] . '：</span><input type="text" class="form-control" name="name[' . $val['id'] . ']" >';
        }
        $this->assign('htmls', $htmls);
        $this->assign('data', $data);
//        $this->assign('langInfo', $langInfo);
//        $this->assign('currencyInfo', $currencyInfo);
        //获取业务类型
        $rWhere = ' where 1=1  and  l.lang_id =' . $this->lang_id;
        $rsql = "SELECT  r.`id`,l.`type_name`,r.`room_img`,r.`superior_id`,r.`room_adv_img`,r.`add_time`,l.`type_id`,r.`sort`   FROM  " . DB_PREFIX . "room_type  AS r
                 LEFT JOIN   " . DB_PREFIX . "room_type_lang  l  ON  r.id = l.`type_id`" . $rWhere;
        $rsql .= ' AND r.superior_id=0   order by r.sort';
        $res = $langMod->querySql($rsql);
        $this->assign('res', $res);
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $this->assign('prrp', $p);
        $this->assign('pros', $this->cityMod->getParentNodes());
        $this->assign('countrys', $this->countryMod->getCountryNodes());
        $this->assign('accounts', $this->getAreaMem());
        $this->assign('store_cate', $this->getAreaCate());
        $this->assign('store_type', $store_type); //
        $this->assign('shipping_method', $shipping_method);
        $this->assign('act', 'index');
        $this->display('areaStore/add.html');
    }

    /**
     * 获取站点国家的信息
     */
    public function getStoreCate()
    {
        $storeCateMod = &m('storeCate');
        $cateid = !empty($_REQUEST['cateid']) ? intval($_REQUEST['cateid']) : 1;
        $sql = 'select  lang_id,currency_id from  bs_store_cate where id =' . $cateid;
        $data = $storeCateMod->querySql($sql);
        echo json_encode($data[0]);
        exit;
    }

    /**
     * 获取区域站点分类
     * @author wanyan
     * @date 2017-08-31
     */
    public function getAreaCate($cate_id = null)
    {
        $where = " where 1=1 ";
        if ($cate_id) {
            $where .= " and sc.id=" . $cate_id;
        } else {
            $where .= "  and scl.lang_id = '{$this->lang_id}'";
        }
        $sql = "SELECT sc.id,scl.cate_name FROM `bs_store_cate` as sc LEFT JOIN `bs_store_cate_lang`
        as scl ON sc.id=scl.cate_id " . $where;
        $rs = $this->storeCateMod->querySql($sql);
        return $rs;
    }

    /**
     * 获取区域站点管理员
     * @author wanyan
     * @date 2017-08-31
     */
    public function getAreaMem()
    {
        $query = array(
            'cond' => "`mark`=1",
            'fields' => "`id`,`account_name`,`phone`"
        );
        $rs = $this->accountMod->getData($query);
        $rs_1 = array_filter($rs);
        $rs_s = count($rs_1);
        if ($rs_s) {
            foreach ($rs as $k => $v) {
                $rs[$k]['account_name'] = $v['account_name'] . '@' . $v['phone'];
            }
        }
        return $rs;
    }

    /**
     * 获取城市和区域列表
     * @author wanyan
     * @date 2017-08-31
     */
    public function getAjaxData()
    {
        $id = !empty($_REQUEST['pro_id']) ? intval($_REQUEST['pro_id']) : '0';
        $rs = $this->cityMod->getData(array('cond' => "`parent_id`='{$id}'", 'fields' => "`id`,`name`"));
        foreach ($rs as $k => $v) {
            if ($v['id'] == 1) {
                unset($rs[0]);
            }
        }
        echo json_encode($rs);
        die;
    }

    /**
     * 获取城市和区域列表
     * @author wanyan
     * @date 2017-08-31
     */
    public function getZoneData()
    {
        $id = !empty($_REQUEST['pro_id']) ? intval($_REQUEST['pro_id']) : '0';
        $sql = "select `zone_id`,`name` from " . DB_PREFIX . "zone where `status` =1 and `country_id`='{$id}'";
        $rs = $this->storeCateMod->querySql($sql);
        echo json_encode($rs);
        die;
    }

    /**
     * 附件图片上传
     * @author zhangr
     * @date 2017-6-21
     */
    public function upload()
    {
        if (IS_POST) {
            $fileName = $_FILES['fileName']['name'];
            $type = strtolower(substr(strrchr($fileName, '.'), 1)); //获取文件类型
            $info = array();
            if (!in_array($type, array('jpg', 'png', 'jpeg', 'gif', 'JPG', 'PNG', 'JPEG', 'GIF'))) {
                $this->setData($info, $status = 'error', $this->langDataBank->project->upload_picture);
            }
            $savePath = "upload/images/store/" . date("Ymd");
            // 判断文件夹是否存在否则创建
            if (!file_exists($savePath)) {
                @mkdir($savePath, 0777, true);
                @chmod($savePath, 0777);
                @exec("chmod 777 {$savePath}");
            }
            $filePath = $_FILES['fileName']['tmp_name']; //文件路径
            $url = $savePath . '/' . time() . '.' . $type;
            if (!is_uploaded_file($filePath)) {
                exit($a['Temporary']);
            }
            //上传文件
            if (!move_uploaded_file($filePath, $url)) {
                $this->setData(array(), '0', $this->langDataBank->public->add_success);
            }
            $data = array(
                "name" => $fileName,
                "status" => 1,
                "url" => $url,
                "add_time" => time()
            );
            //$this->addLog('图片上传操作');
            echo json_encode($data);
        } else {
            $this->setData($info = array(), 2, $this->langDataBank->public->system_error);
        }
    }

    /**
     * 区域店铺
     * @author zhangr
     * @date 2017-9-05
     */
    public function doAdd()
    {
        $store_type = !empty($_REQUEST['store_type']) ? intval($_REQUEST['store_type']) : 0;  //
        $store_logo = !empty($_REQUEST['store_logo']) ? htmlspecialchars(trim($_REQUEST['store_logo'])) : '';
        $background_img = !empty($_REQUEST['background_img']) ? $_REQUEST['background_img'] : '';
        $store_Start = !empty($_REQUEST['store_Start']) ? ($_REQUEST['store_Start']) : '';  //
        $store_End = !empty($_REQUEST['store_End']) ? ($_REQUEST['store_End']) : '';  //
        $store_Notice = !empty($_REQUEST['store_Notice']) ? htmlspecialchars(trim($_REQUEST['store_Notice'])) : '';  //
        if (strlen($store_Notice) > 45) {
                $this->setData(array(), $status = '0', $this->langDataBank->project->announce_length);
        }
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
//        $store_name = !empty($_REQUEST['store_name']) ? trim($_REQUEST['store_name']) : '';
        $name = !empty($_REQUEST['name']) ? $_REQUEST['name'] : array();
        $store_cate_id = !empty($_REQUEST['store_cate_id']) ? htmlspecialchars(trim($_REQUEST['store_cate_id'])) : '0';  //站点国家
        // $is_site = !empty($_REQUEST['is_site']) ? intval($_REQUEST['is_site']) : '0';  //默认站点
        //  $store_code = !empty($_REQUEST['store_code']) ? htmlspecialchars(trim($_REQUEST['store_code'])) : '';
        // $store_tag = !empty($_REQUEST['store_tag']) ? htmlspecialchars(trim($_REQUEST['store_tag'])) : '';
//        $store_userid  = !empty($_REQUEST['store_userid']) ? htmlspecialchars(trim($_REQUEST['store_userid'])):'0';

        $store_mobile = !empty($_REQUEST['store_mobile']) ? htmlspecialchars(trim($_REQUEST['store_mobile'])) : '';
        $switchLan = !empty($_REQUEST['switch-lan']) ? htmlspecialchars(trim($_REQUEST['switch-lan'])) : '';  //所属区域

        $pro_id = !empty($_REQUEST['pro_id']) ? htmlspecialchars(trim($_REQUEST['pro_id'])) : ''; //站点地址
        $city_id = !empty($_REQUEST['city_id']) ? htmlspecialchars(trim($_REQUEST['city_id'])) : '';
        $area_id = !empty($_REQUEST['area_id']) ? htmlspecialchars(trim($_REQUEST['area_id'])) : '';

        $country_id = !empty($_REQUEST['country_id']) ? htmlspecialchars(trim($_REQUEST['country_id'])) : '';  //站点地址
        $zhou_id = !empty($_REQUEST['zhou_id']) ? htmlspecialchars(trim($_REQUEST['zhou_id'])) : '';

        $addr_detail = !empty($_REQUEST['addr_detail']) ? htmlspecialchars(trim($_REQUEST['addr_detail'])) : '';

        //  $image_id = !empty($_REQUEST['image_id']) ? htmlspecialchars(trim($_REQUEST['image_id'])) : '';
        // $shipping_method = !empty($_REQUEST['shipping_method']) ? $_REQUEST['shipping_method'] : '';  //配送方式

        $area = !empty($_REQUEST['area']) ? htmlspecialchars(trim($_REQUEST['area'])) : '';
        $g_area = !empty($_REQUEST['g_area']) ? htmlspecialchars(trim($_REQUEST['g_area'])) : '';  //配送范围
        // 国内 配送范围
        $shen_id = !empty($_REQUEST['shen_id']) ? intval($_REQUEST['shen_id']) : 0;
        $shi_id = !empty($_REQUEST['shi_id']) ? intval($_REQUEST['shi_id']) : 0;
        $qu_id = !empty($_REQUEST['qu_id']) ? intval($_REQUEST['qu_id']) : 0;
        // 国际 配送范围
        $delivery_country_id = !empty($_REQUEST['delivery_country_id']) ? intval($_REQUEST['delivery_country_id']) : 0;
        $delivery_zone_id = !empty($_REQUEST['delivery_zone_id']) ? intval($_REQUEST['delivery_zone_id']) : 0;

        //$mer_recommd = !empty($_REQUEST['mer_recommd']) ? htmlspecialchars(trim($_REQUEST['mer_recommd'])) : '';
        //$sort = !empty($_REQUEST['sort']) ? htmlspecialchars(trim($_REQUEST['sort'])) : '0';

        $lang_id = !empty($_REQUEST['language_id']) ? htmlspecialchars($_REQUEST['language_id']) : '0';
        $currency_id = !empty($_REQUEST['currency_id']) ? htmlspecialchars($_REQUEST['currency_id']) : '0';
        $lang_id_1 = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0';
        $longitude = !empty($_REQUEST['longitude']) ? htmlspecialchars($_REQUEST['longitude']) : '';
        $latitude = !empty($_REQUEST['latitude']) ? htmlspecialchars($_REQUEST['latitude']) : '';
        $name_fu = $_REQUEST['name_fu'];
        $busiId = !empty($_REQUEST['cate_id']) ? $_REQUEST['cate_id'] : array();
        $distance = !empty($_REQUEST['distance']) ? $_REQUEST['distance'] : 0;
        $fee = !empty($_REQUEST['fee']) ? $_REQUEST['fee'] : 0;

        $info = array();
        if (empty($store_type)) {
            $this->setData($info, $status = '0', $a['store_type']);
        }
        if (!preg_match("/^([1-9]\d*|0)(\.\d{1,2})?$/", $distance)) {
            $this->setData($info, $status = '0', $this->langDataBank->project->max_distance);
        }
        $distance = sprintf("%.2f", $distance);

        if (!preg_match("/^([1-9]\d*|0)(\.\d{1,2})?$/", $fee)) {
            $this->setData($info, $status = '0', $this->langDataBank->project->ship_fee);
        }
        $fee = sprintf("%.2f", $fee);

        //判断数据
        foreach ($name as $key => $val) {
            $name[$key] = trim($val);
        }
        /*    foreach ($name as $val) {
          if (empty($val)) {
          $this->setData($info = array(), $status = '0', $a['store_name']);
          break; */
////            } else {
////                $aff = $this->getOneInfo($val);
////                if (!empty($aff)) {
////                    $this->setData($info = array(), $status = '0', $a['store_names']);
////                    break;
////                }
        /*         }
          } */
////        if (empty($store_name)) {
////            $this->setData($info, $status = '0', $a['store_name']);
////        } else {
////            $query = array(
////                'cond' => "`store_name`='{$store_name}'",
////                'fields' => 'store_name'
////            );
////            $rs = $this->storeMod->getOne($query);
////            if ($rs['store_name']) {
////                $this->setData($info, $status = '0', $a['store_names']);
////            }
////        }

        if (empty($store_cate_id)) {
            $this->setData($info, $status = '0', $this->langDataBank->project->site_country_required);
        }
        //如果国家下的没有总代理的话，要先添加总代理
        if ($store_type == 2) {
            $iszd = $this->iszongDai($store_cate_id);
            if (!$iszd) { //没有总代
                $this->setData($info, $status = '0', $this->langDataBank->project->general_agent_first);
            }
        }

        //站点的参考类型
        if ($store_type == 1) {
            $istype = $this->storeType($store_cate_id);
            if (!empty($istype)) {
                $this->setData($info, $status = '0', $this->langDataBank->project->general_agent_exist);
            }
        }

        if (empty($lang_id)) {
            $this->setData($info, $status = '0', $this->langDataBank->project->language_empty);
        }
        if (empty($currency_id)) {
            $this->setData($info, $status = '0', $this->langDataBank->project->currency_empty);
        }
        if (empty($store_logo)) {
            $this->setData($info, $status = '0', $this->langDataBank->project->upload_logo);
        }
        if (empty($background_img)) {
            $this->setData($info, $status = '0', $this->langDataBank->project->upload_back);
        }

//        //        if (empty($store_code)) {
////            $this->setData($info, $status = '0', $a['store_code']);
////        } else {
////            $query = array(
////                'cond' => "`store_code`='{$store_code}'",
////                'fields' => 'store_code'
////            );
////            $rs = $this->storeMod->getOne($query);
////            if ($rs['store_code']) {
////                $this->setData($info, $status = '0', $a['store_codes']);
////            }
////        }
////        if (empty($store_tag)) {
////            $this->setData($info, $status = '0', $a['store_tag']);
////        } else {
////            $query = array(
////                'cond' => "`store_tag`='{$store_tag}'",
////                'fields' => 'store_tag'
////            );
////            $rs = $this->storeMod->getOne($query);
////            if ($rs['store_tag']) {
////                $this->setData($info, $status = '0', $a['store_tags']);
////            }
////        }
////        if(empty($store_userid)){
////            $this->setData($info,$status='0',$message='站点管理员不能为空！');
////        }
//        //        if (empty($image_id)) {
////            $this->setData($info, $status = '0', $a['store_image_id']);
////        }
////        if (empty($shipping_method)) {
////            $this->setData($info, $status = '0', $a['store_method']);
////        } else {
////            $method = '';
////            foreach ($shipping_method as $k => $v) {
////                $method .=$v . ',';
////            }
////        }
//
//
//        if (empty($store_mobile)) {
//            $this->setData($info, $status = '0', $a['store_mobile']);
//        }
////        if (strlen($store_mobile) != 11) {
////            $this->setData($info, $status = '0', $a['store_mobiles']);
////        }
////        if (!preg_match('/^1[34578]\d{9}$/', $store_mobile)) {
////            $this->setData($info, $status = '0', $a['store_mobilesx']);
////        }
        if ($switchLan == 1) {  //国内
            if (empty($pro_id) || empty($city_id) || empty($area_id)) {
                $this->setData($info, $status = '0', $this->langDataBank->project->province_required);
            }
            $store_address = $pro_id . '_' . $city_id . '_' . $area_id;
        } else {
            if (empty($country_id) || empty($zhou_id)) {
                $this->setData($info, $status = '0', $this->langDataBank->project->site_country_required);
            }
            $store_address = $country_id . '_' . $zhou_id;
        }
        if (empty($addr_detail)) {
            $this->setData($info, $status = '0', $this->langDataBank->project->address_required);
        }
        foreach ($busiId as $k => $v) {
            if ($v == 0) {
                    $this->setData(array(), $status = '0', $this->langDataBank->project->business_required);//type_required
            }
        }

        $length = count($busiId);
        for ($i = 0; $i < $length; $i++) {
            $cate = $busiId[$i];
            for ($j = $i + 1; $j < $length; $j++) {
                if ($cate == $busiId[$j]) {
                        $this->setData(array(), $status = '0', $this->langDataBank->project->business_exist);
                }
            }
        }

//        // 配送范围
        if ($switchLan == 1) {  //国内
////            if(empty($shi_id)){
////                $this->setData($info, $status = '0', '最小要选到城市');
////            }
            if (empty($area)) {
                $this->setData($info, $status = '0', $this->langDataBank->project->delivery_area_required);
            }

            $shipping_scope = $area;
        } else { //国际
////            if (empty($delivery_country_id)) {
////                $this->setData($info, $status = '0', $a['store_ps_area']);
////            }
            if (empty($g_area)) {
                $this->setData($info, $status = '0', $this->langDataBank->project->delivery_area_required);
            }
            $shipping_scope = $g_area;
        }
        if (empty($longitude)) {
            $this->setData($info, $status = '0', $this->langDataBank->project->longitude_required);
        }
        if (empty($latitude)) {
            $this->setData($info, $status = '0', $this->langDataBank->project->latitude_required);
        }
        $backgroundList = array();
        foreach ($background_img as $key => $value) {
            $backgroundList[$key]['background'] = $value;
            $backgroundList[$key]['activity_id'] = 0;
        }
        $background = serialize($backgroundList);
        $insert_data = array(
            'store_type' => $store_type,
            'is_site' => 2,
            // 'store_code' => $store_code,
            'store_name' => addslashes($store_name),
            // 'store_tag' => $store_tag,
            'store_cate_id' => $store_cate_id,
            'store_userid' => $store_userid,
            'store_username' => $this->getAccountName($store_userid),
            'store_mobile' => $store_mobile,
            'store_address' => $store_address,
            'addr_detail' => $addr_detail,
            // 'image_url' => $image_id,
            // 'shipping_method' => $method,
            'shipping_scope' => $shipping_scope,
            // 'mer_recommd' => $mer_recommd,
            // 'sort' => $sort,
            'add_time' => time(),
            'lang_id' => $lang_id,
            'longitude' => $longitude,
            'latitude' => $latitude,
            'currency_id' => $currency_id,
            'logo' => $store_logo,
            'distance' => $distance,
            'fee' => $fee,
            'store_start_time' => $store_Start,
            'store_end_time' => $store_End,
            'store_notice' => $store_Notice,
            'background_img'=>$background
        );
        $insert_id = $this->storeMod->doInsert($insert_data);

        if ($insert_id){
            $initMod = &m('init');
            $initMod->initTable($insert_id);
        }
        //区域业务关联
        $busiMod = &m('storebusiness');
        foreach ($busiId as $v) {
            $cateData[] = array('store_id' => $insert_id, 'buss_id' => $v);
        }
        foreach ($cateData as $v) {
            $busiMod->doInsert($v);
        }
        //生成2维码
        $code = $this->goodsZcode($insert_id, $store_type, $lang_id);
        $urldata = array(
            "table" => "store",
            'cond' => 'id = ' . $insert_id,
            'set' => "store_url='" . $code . "'",
        );
        $this->storeMod->doUpdate($urldata);
        //结果判断
        if ($insert_id) {
            //modify by lee 添加区域广告位
            $this->add_position($insert_id);
            //添加区域角色权限
            $this->add_storeUserAdmin($insert_id);
            if ($store_type == 1) { //总代里 才有支付配额
                // 生成默认三种支付方式
                $this->add_pay($insert_id);
            }
            //生成分销配置
            $this->add_fx_site($insert_id);
            //如果就一个站点，
            //添加多语言版本信息
            $this->doLangData($name, $insert_id);
            if ($name_fu) {
                //添加多语言版本信息
                $this->doLangDatas($name_fu, $insert_id);
            }
            $initMod=&m('init');
            $initMod->appoint($insert_id);
            $this->addLog('站点添加操作');
            $info['url'] = "?app=areaStore&act=index&p={$p}";
            $this->setData($info, $status = '1', $this->langDataBank->public->add_success);
        } else {
            $this->setData($info, $status = '0', $this->langDataBank->public->add_error);
        }
    }

    //二维码
    public function goodsZcode($store_id, $store_type, $lang_id)
    {
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
        if ($store_type == 1) {
            $valueUrl = 'http://' . $system_web . "/wx.php?app=default&act=index&storeid={$store_id}&lang={$lang_id}&auxiliary=0";
        } else {
            $valueUrl = 'http://' . $system_web . "/wx.php?app=goodList&act=index&storeid={$store_id}&lang={$lang_id}&auxiliary=0&order=1";
        }
        QRcode::png($valueUrl, $filename);
        return $pathName;
    }

    /**
     * 添加多语言版本信息
     */
    public function doLangData($name, $insert_id)
    {
        $gCLangMod = &m('areaStoreLang');
        $data = array();
        foreach ($name as $key => $val) {
            $data[] = array(
                'lang_id' => $key,
                'store_name' => addslashes(trim($val)),
                'store_id' => $insert_id,
                'add_time' => time()
            );
        }
        // 循环插入数据
        foreach ($data as $v) {
            $res = $gCLangMod->doInsert($v);
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
     * 添加多语言版本信息
     */
    public function doLangDatas($name_fu, $insert_id)
    {
        $gCLangMod = &m('areaStoreLang');
        $data = array();
        $i = 0;
        foreach ($name_fu as $key => $val) {
            $i++;
            foreach ($val as $k => $v) {
                $data[] = array(
                    'lang_id' => $k,
                    'store_name' => addslashes(trim($v)),
                    'store_id' => $insert_id,
                    'distinguish' => $i,
                    'add_time' => time()
                );
            }
        }
        // 循环插入数据
        foreach ($data as $v) {
            $res = $gCLangMod->doInsert($v);
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
     * 获取分类信息
     * @author wanyan
     * @date 2017-8-1
     */
    public function getOneInfo($name, $id = 0)
    {
        $gCLangMod = &m('areaStoreLang');
        $where = '  where 1=1';
        if (empty($id)) {
            //添加
            $where .= '  and  store_name = "' . $name . '"';
        } else {
            //编辑
            $where .= '  and   id!=' . $id . '  and  store_name = "' . $name . '"';
        }
        $sql = 'select id  from  ' . DB_PREFIX . 'store_lang' . $where;
        $res = $gCLangMod->querySql($sql);
        return $res;
    }

    /**
     *
     */
    public function iszongDai($cateid)
    {
        $sql = 'SELECT  s.id  FROM bs_store_cate  AS c  LEFT JOIN  bs_store  AS s ON c.id = s.`store_cate_id`
                WHERE s.store_type = 1  AND c.id = ' . $cateid;
        $data = $this->storeMod->querySql($sql);
        return $data[0]['id'];
    }

    /*
     * 生成广告位
     * @author lee
     * @date 2017-11-20 15:49:52
     */

    public function add_position($insert_id)
    {
        $position_data1 = array(
            "position_num" => 110000, //首页
            "position_name" => "首页横幅",
            "english_name" => "home banners",
            "ad_width" => "1200",
            "ad_height" => "470",
            "store_id" => $insert_id,
            "is_open" => 1,
        );
        $position_data2 = array(
            "position_num" => 110001, //首页
            "position_name" => "二级广告位",
            "english_name" => "Two level ad position",
            "ad_width" => "1200",
            "ad_height" => "181",
            "store_id" => $insert_id,
            "is_open" => 1,
        );
        $position_data3 = array(
            "position_num" => 110002, //首页
            "position_name" => "二级广告位2",
            "english_name" => "Two level ad position2",
            "ad_width" => "1200",
            "ad_height" => "181",
            "store_id" => $insert_id,
            "is_open" => 1,
        );
        $positionMod = &m('advPosition');
        $positionMod->doInsert($position_data1);
        $positionMod->doInsert($position_data2);
        $positionMod->doInsert($position_data3);
    }

    /*
     * 生成区域角色
     * @author wangshuo
     * @date 2018-4-12 15:49:52
     */

    public function add_storeUserAdmin($insert_id)
    {
        $storeUserAdmin_data1 = array(
            "name" => '超级管理员', //角色名称
            "english_name" => "Superadministrator", //角色英文名称
            "level" => 1, //默认超级管理员
            "store_id" => "$insert_id", //对应的站点
            "add_time" => time(),
            "modify_time" => time(),
            "mark" => 1,
        );
        $storeUserAdmin = &m('storeUserAdmin');
        $storeUserAdmin->doInsert($storeUserAdmin_data1);
    }

    /*
     * 生成支付方式
     * @author lee
     * @date 2017-11-20 15:49:52
     */

    public function add_pay($insert_id)
    {
        $insert_chanpay_data = array(// 畅捷支付
            'code' => 'chanpay',
            'pay_name' => '畅捷支付',
            'store_id' => $insert_id,
            'add_time' => time(),
        );
        $insert_alipay_data = array(// 支付宝
            'code' => 'alipay',
            'pay_name' => '支付宝',
            'store_id' => $insert_id,
            'add_time' => time(),
        );
        $insert_weixin_data = array(// 微信支付
            'code' => 'weixin',
            'pay_name' => '微信支付',
            'store_id' => $insert_id,
            'add_time' => time(),
        );
        $insert_paypal_data = array(// 贝宝支付
            'code' => 'paypal',
            'pay_name' => '贝宝支付',
            'store_id' => $insert_id,
            'add_time' => time(),
        );
        $payMod = &m('payConfig');
        $payMod->doInsert($insert_chanpay_data);
        $payMod->doInsert($insert_alipay_data);
        $payMod->doInsert($insert_weixin_data);
        $payMod->doInsert($insert_paypal_data);
    }

    public function storeType($cateid)
    {
        $sql = 'SELECT id FROM bs_store WHERE store_cate_id = ' . $cateid . ' AND store_type = 1';
        $res = $this->storeMod->querySql($sql);
        return $res[0]['id'];
    }

    /*
     * 生成分销设置
     */

    public function add_fx_site($insert_id)
    {
        $fxSiteMod = &m('fxSite');
        $data = array(
            'is_order_day' => 1,
            'order_day' => 10,
            'is_money' => 1,
            'money' => 1,
            'is_time' => 1,
            'time' => 1,
            'is_drawing_day' => 1,
            'drawing_day' => 1,
            'store_id' => $insert_id,
            'add_time' => time()
        );
        $res = $fxSiteMod->doInsert($data);
    }

    /**
     * 获取用户的username
     * @author zhangr
     * @date 2017-9-05
     */
    public function getAccountName($account_id)
    {
        $query = array(
            'cond' => "`id`='{$account_id}'",
            'fields' => 'account_name'
        );
        $rs = $this->accountMod->getOne($query);
        return $rs['account_name'];
    }

    /**
     * 改变状态
     * @author zhangr
     * @date 2017-9-05
     */
    public function getStatus()
    {
        $id = $_REQUEST['cate_id'];
        $is_hot = $_REQUEST['is_hot'];
        $data = array(
            'is_hot' => $is_hot
        );
        $rs = $this->storeMod->doEdit($id, $data);
        if ($rs) {
            $this->addLog('站点是否热门设置操作');
            $this->setData($info = array(), $status = '1', $message = '');
        } else {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->enable_fail);
        }
    }

    /**
     * 改变启用状态
     * @author zhangr
     * @date 2017-9-05
     */
    public function getOpenStatus()
    {
        $id = $_REQUEST['cate_id'];
        $is_open = $_REQUEST['is_open'];
        $data = array(
            'is_open' => $is_open
        );
        $rs = $this->storeMod->doEdit($id, $data);
        if ($rs) {
            $info['status'] = $is_open;
            $this->addLog('站点启用禁用设置操作');
            $this->setData($info, $status = '1', $message = '');
        } else {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->enable_fail);
        }
    }

    /**
     * 改变是默认状态
     * @author zhangr
     * @date 2017-9-05
     */
    public function getSiteStatus()
    {
        $id = $_REQUEST['id'];
        $is_site = $_REQUEST['is_site'];
        $cateid = $_REQUEST['cateid'];
        //
        $sql = 'update  ' . DB_PREFIX . 'store  set  is_site = 2  where  store_cate_id =' . $cateid . '  and id !=' . $id;
        $this->storeMod->doEditSql($sql);
        //
        $data = array(
            'is_site' => $is_site
        );
        $rs = $this->storeMod->doEdit($id, $data);

        if ($rs) {
            $this->addLog('默认站点设置操作');
            $this->setData($info = array(), $status = '1', $message = '');
        } else {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->switch_fail);
        }
    }

    /**
     * 店铺编辑页面
     * @author wanyan
     * @date 2017-9-05
     */
    public function edit()
    {
        $paging = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $store_id = !empty($_REQUEST['id']) ? (int)$_REQUEST['id'] : '0';
        $rs = $this->storeMod->getOne(array('cond' => "`id`='{$store_id}'"));
        $backgroundInfo = unserialize($rs['background_img']);
        $activityMod = &m('storeActivity');
        foreach ($backgroundInfo as $key => &$value) {
            if ($value['activity_id']) {
                $activity = $activityMod->getRow($value['activity_id']);
                $value['activity'] = $activity['name'];
            }
        }
//        echo '<pre>';print_r($backgroundInfo);die;
        $this->assign('backgroundInfo', $backgroundInfo);
        $this->assign('flag', count($backgroundInfo));
        $store_address = explode('_', $rs['store_address']);
        if (count($store_address) == 3) {
            $ch_store_address = $store_address;
            $ch_city = $this->getCity($ch_store_address[0]);
            $ch_area = $this->getCity($ch_store_address[1]);
            $this->assign('switch', 1);
            $this->assign('ch_city', $ch_city);
            $this->assign('ch_area', $ch_area);
            $this->assign('ch_store_address', $ch_store_address);
        } else {
            $this->assign('switch', 2);
            $en_store_address = $store_address;
            $this->assign('en_store_address', $en_store_address);
            $en_zhou = $this->getGzone($en_store_address[0]);
            $this->assign('en_zhou', $en_zhou);
        }
        if ($this->shorthand == 'ZH') {
            $shipping_method = G('shipping_method');
            $store_type = G('store_type');
        } else if ($this->shorthand == 'EN'){
            $shipping_method = G('shipping_method_en');
            $store_type = G('store_type_en');
        }
        $shipping = $rs['shipping_method'];
        $shipping = explode(',', $shipping);
        $this->assign('shipping', array_filter($shipping));

//        var_dump($this->cityMod->getParentNodes());die;
        $shipping_scope = explode(':', $rs['shipping_scope']);
        $first = substr($shipping_scope[0], 0, 3);
        if (matchZhongWen($first)) {
            $ch_shipping_scope = array_filter($shipping_scope);
            $ch_scope = $rs['shipping_scope'];
            $this->assign('ch_scope', $ch_scope);
            $this->assign('ch_shipping_scope', $ch_shipping_scope);
        } else {
            $en_shipping_scope = array_filter($shipping_scope);
            $en_scope = $rs['shipping_scope'];
            $this->assign('en_scope', $en_scope);
            $this->assign('en_shipping_scope', $en_shipping_scope);
        }
        //多语言版本
        $langMod = &m('language');
        $sql = 'select  id,name  from   ' . DB_PREFIX . 'language';
        $res = $langMod->querySql($sql);
        $this->assign('res', $res);
        // 商品模型的具体信息
        $gCLangMod = &m('areaStoreLang');
        $sql = 'select  id,lang_id,store_name,store_id  from   ' . DB_PREFIX . 'store_lang  where distinguish =0 and  store_id =' . $store_id;
        $data = $gCLangMod->querySql($sql);
        $html = '';
        foreach ($res as $key => $val) {
            foreach ($data as $item) {
                if ($val['id'] == $item['lang_id']) {
                    $html .= ' <span class="mt10 mb5 inblock">' . $val['name'] . '：</span>
                               <input type="text" class="form-control" name="name[' . $item['lang_id'] . ']"  value="' . stripslashes($item['store_name']) . '"  >';
                    unset($res[$key]);
                }
            }
        }
        //如果以后再添加新的语言
        if (!empty($res)) {
            foreach ($res as $v) {
                $html .= ' <span class="mt10 mb5 inblock">' . $v['name'] . '：</span>
                               <input type="text" class="form-control" name="name[' . $v['id'] . ']"  value=""  >';
            }
        }
        $gCLangMod = &m('areaStoreLang');
        $sql = 'select  id,lang_id,store_name,store_id,distinguish  from   ' . DB_PREFIX . 'store_lang  where distinguish >0 and  store_id =' . $store_id . ' order by id';

        $datas = $gCLangMod->querySql($sql);
        $sData = array();
        foreach ($datas as $k => $v) {
            $sData[$v['distinguish']][] = $v;
        }
        //业务区域关联
        $rrWhere = ' where 1=1  and  l.lang_id =' . $this->lang_id;
        $rrsql = "SELECT  r.`id`,l.`type_name`,r.`room_img`,r.`superior_id`,r.`room_adv_img`,r.`add_time`,l.`type_id`,r.`sort`   FROM  " . DB_PREFIX . "room_type  AS r
                 LEFT JOIN   " . DB_PREFIX . "room_type_lang  l  ON  r.id = l.`type_id`" . $rrWhere;
        $rrsql .= ' AND r.superior_id=0   order by r.sort';
        $rres = $langMod->querySql($rrsql);
        $this->assign('rres', $rres);

        $listSql = 'SELECT *  FROM ' . DB_PREFIX . 'store_business WHERE store_id=' . $store_id;
        $listData = $gCLangMod->querySql($listSql);
        foreach ($listData as $k => $v) {
            $cateId[] = $v['buss_id'];
        }

        $cateIds = implode(',', $cateId);
        $rWhere = ' where 1=1  and r.id in (' . $cateIds . ') and  l.lang_id =' . $this->lang_id;
        $rsql = "SELECT  r.`id`,l.`type_name`,r.`room_img`,r.`superior_id`,r.`room_adv_img`,r.`add_time`,l.`type_id`,r.`sort`   FROM  " . DB_PREFIX . "room_type  AS r
                 LEFT JOIN   " . DB_PREFIX . "room_type_lang  l  ON  r.id = l.`type_id`" . $rWhere;
        $rsql .= ' AND r.superior_id=0   order by r.sort';
        $cateData = $gCLangMod->querySql($rsql);
        if (empty($cateData)) {

            $cateData = array(array());
        }

        $this->assign('cateData', $cateData);

        $this->assign('datas', $sData);
        $this->assign('html', $html);
//        $langInfo = $this->langMod->getLanguage();
        $currencyInfo = $this->currencyMod->getCurrency();
//        $this->assign('langInfo', $langInfo);
        $this->assign('currencyInfo', $currencyInfo);
        $store_name = stripslashes($rs['store_name']);
        $this->assign('pros', $this->cityMod->getParentNodes());
        $this->assign('countrys', $this->countryMod->getCountryNodes());
        $this->assign('accounts', $this->getAreaMem());
        $this->assign('store_cate', $this->getAreaCate());
        $this->assign('store_type', $store_type);
        $this->assign('shipping_method', $shipping_method);
        $this->assign('store_name', $store_name);
        $this->assign('act', 'index');
        $this->assign('list', $rs);
        $this->assign('paging', $paging);
        $this->assign('lang_id', $lang_id);
        $this->display('areaStore/edit.html');
    }

    /**
     * 获取中国地址
     * @author wanyan
     * @date 2017-9-05
     */
    public function getCity($id)
    {
        $sql = "select `id`,`name` from " . DB_PREFIX . "city where `parent_id`='{$id}'";
        $rs = $this->storeMod->querySql($sql);
        return $rs;
    }

    /**
     * 获取国外地址
     * @author wanyan
     * @date 2017-9-05
     */
    public function getGzone($id)
    {
        $sql = "select z.zone_id,z.name from " . DB_PREFIX . "country as c left join " . DB_PREFIX . "zone as z on c.country_id = z.country_id where c.country_id = {$id}";
        $rs = $this->storeMod->querySql($sql);
        return $rs;
    }

    /**
     * 编辑店铺
     * @author wanyan
     * @date 2017-9-05
     */
    public function doEdit(){
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $store_id = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : '0';
        $store_Start = !empty($_REQUEST['store_Start']) ? ($_REQUEST['store_Start']) : '';  //
        $store_End = !empty($_REQUEST['store_End']) ? ($_REQUEST['store_End']) : '';  //
        $store_Notice = !empty($_REQUEST['store_Notice']) ? htmlspecialchars(trim($_REQUEST['store_Notice'])) : '';  //
        if (strlen($store_Notice) > 45) {
                $this->setData(array(), $status = '0', $this->langDataBank->project->announce_length);
        }
        //  $store_type = !empty($_REQUEST['store_type']) ? intval($_REQUEST['store_type']) : '0';
        //$is_site = !empty($_REQUEST['is_site']) ? intval($_REQUEST['is_site']) : '0';
        // $store_code = !empty($_REQUEST['store_code']) ? htmlspecialchars(trim($_REQUEST['store_code'])) : '';
        $name = !empty($_REQUEST['name']) ? $_REQUEST['name'] : array();
//        $store_name = !empty($_REQUEST['store_name']) ? htmlspecialchars(trim($_REQUEST['store_name'])) : '';
        //  $store_tag = !empty($_REQUEST['store_tag']) ? htmlspecialchars(trim($_REQUEST['store_tag'])) : '';
        $store_cate_id = !empty($_REQUEST['store_cate_id']) ? htmlspecialchars(trim($_REQUEST['store_cate_id'])) : '0';
//        $store_userid  = !empty($_REQUEST['store_userid']) ? htmlspecialchars(trim($_REQUEST['store_userid'])):'0';
        $store_mobile = !empty($_REQUEST['store_mobile']) ? htmlspecialchars(trim($_REQUEST['store_mobile'])) : '';
        $switchLan = !empty($_REQUEST['switch-lan']) ? htmlspecialchars(trim($_REQUEST['switch-lan'])) : '';
        $pro_id = !empty($_REQUEST['pro_id']) ? htmlspecialchars(trim($_REQUEST['pro_id'])) : '';
        $city_id = !empty($_REQUEST['city_id']) ? htmlspecialchars(trim($_REQUEST['city_id'])) : '';
        $area_id = !empty($_REQUEST['area_id']) ? htmlspecialchars(trim($_REQUEST['area_id'])) : '';
        $country_id = !empty($_REQUEST['country_id']) ? htmlspecialchars(trim($_REQUEST['country_id'])) : '';
        $zhou_id = !empty($_REQUEST['zhou_id']) ? htmlspecialchars(trim($_REQUEST['zhou_id'])) : '';
        $addr_detail = !empty($_REQUEST['addr_detail']) ? htmlspecialchars(trim($_REQUEST['addr_detail'])) : ($_REQUEST['addr_add']);
        //  $image_id = !empty($_REQUEST['image_id']) ? htmlspecialchars(trim($_REQUEST['image_id'])) : '';
        //  $shipping_method = !empty($_REQUEST['shipping_method']) ? $_REQUEST['shipping_method'] : '';
        $area = !empty($_REQUEST['area']) ? htmlspecialchars(trim($_REQUEST['area'])) : '';
        $g_area = !empty($_REQUEST['g_area']) ? htmlspecialchars(trim($_REQUEST['g_area'])) : '';
        $store_logo = !empty($_REQUEST['store_logo']) ? htmlspecialchars(trim($_REQUEST['store_logo'])) : '';
        $background_img = !empty($_REQUEST['background_img']) ? $_REQUEST['background_img'] : '';
        $type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : '';
        $activity_id = !empty($_REQUEST['activity_id']) ? $_REQUEST['activity_id'] : '';

        // 国内 配送范围
        $shen_id = !empty($_REQUEST['shen_id']) ? intval($_REQUEST['shen_id']) : 0;
        $shi_id = !empty($_REQUEST['shi_id']) ? intval($_REQUEST['shi_id']) : 0;
        $qu_id = !empty($_REQUEST['qu_id']) ? intval($_REQUEST['qu_id']) : 0;
        // 国际 配送范围
        $delivery_country_id = !empty($_REQUEST['delivery_country_id']) ? intval($_REQUEST['delivery_country_id']) : 0;
        $delivery_zone_id = !empty($_REQUEST['delivery_zone_id']) ? intval($_REQUEST['delivery_zone_id']) : 0;

        // $mer_recommd = !empty($_REQUEST['mer_recommd']) ? htmlspecialchars(trim($_REQUEST['mer_recommd'])) : '';
        // $sort = !empty($_REQUEST['sort']) ? htmlspecialchars(trim($_REQUEST['sort'])) : '0';

        $lang_id = !empty($_REQUEST['language_id']) ? htmlspecialchars($_REQUEST['language_id']) : '0';
        $currency_id = !empty($_REQUEST['currency_id']) ? htmlspecialchars($_REQUEST['currency_id']) : '0';
        $longitude = !empty($_REQUEST['longitude']) ? htmlspecialchars($_REQUEST['longitude']) : '';
        $latitude = !empty($_REQUEST['latitude']) ? htmlspecialchars($_REQUEST['latitude']) : '';
        $name_fu = $_REQUEST['name_fu'];
        $catId = !empty($_REQUEST['cate_id']) ? $_REQUEST['cate_id'] : array();
        $distance = !empty($_REQUEST['distance']) ? $_REQUEST['distance'] : 0;
        $fee = !empty($_REQUEST['fee']) ? $_REQUEST['fee'] : 0;

        $info = array();
//        if (empty($store_name)) {
//            $this->setData($info, $status = '0', $a['store_name']);
//        } else {
//            $query = array(
//                'cond' => "`store_name`='{$store_name}' and `id`!='{$store_id}'",
//                'fields' => 'store_name'
//            );
//            $rs = $this->storeMod->getOne($query);
//            if ($rs['store_name']) {
//                $this->setData($info, $status = '0', $a['store_names']);
//            }
//        }
        if (!preg_match("/^([1-9]\d*|0)(\.\d{1,2})?$/", $distance)) {
            $this->setData($info, $status = '0', max_distance);
        }
        //判断数据
        $distance = sprintf("%.2f", $distance);
        if (!preg_match("/^([1-9]\d*|0)(\.\d{1,2})?$/", $fee)) {
            $this->setData($info, $status = '0', ship_fee);
        }
        //判断数据
        $fee = sprintf("%.2f", $fee);


        foreach ($name as $key => $val) {
            $name[$key] = trim($val);
        }
        //判断数据
        foreach ($name as $val) {
            if (empty($val)) {
                $this->setData($info = array(), $status = '0', $this->langDataBank->project->site);
                break;
//            } else {
//                //
//                $styleId = $this->getCtgLang($val, $store_id);  //style_lang 表里的id
//                $aff = $this->getOneInfo($val, $styleId); //判断是否命名重复
//                if (!empty($aff)) {
//                    $this->setData($info = array(), $status = '0', $a['store_names']);
//                    break;
//                }
            }
        }
//        if (empty($store_cate_id)) {
//            $this->setData($info, $status = '0', $a['store_cate_id']);
//        }

        if (empty($lang_id)) {
            $this->setData($info, $status = '0', $this->langDataBank->project->language_empty);
        }
        if (empty($currency_id)) {
            $this->setData($info, $status = '0', $this->langDataBank->project->currency_empty);
        }

        foreach ($catId as $k => $v) {
            if ($v == 0) {
                    $this->setData(array(), $status = '0', $this->langDataBank->project->business_required);
            }
        }
        $length = count($catId);
        for ($i = 0; $i < $length; $i++) {
            $cate = $catId[$i];
            for ($j = $i + 1; $j < $length; $j++) {
                if ($cate == $catId[$j]) {
                        $this->setData(array(), $status = '0', $this->langDataBank->project->business_exist);
                }
            }
        }

//        if (empty($store_code)) {
//            $this->setData($info, $status = '0', $a['store_code']);
//        } else {
//            $query = array(
//                'cond' => "`store_code`='{$store_code}' and `id` !='{$store_id}'",
//                'fields' => 'store_code'
//            );
//            $rs = $this->storeMod->getOne($query);
//            if ($rs['store_code']) {
//                $this->setData($info, $status = '0', $a['store_codes']);
//            }
//        }
//        if (empty($store_tag)) {
//            $this->setData($info, $status = '0', $a['store_tag']);
//        } else {
//            $query = array(
//                'cond' => "`store_tag`='{$store_tag}' and `id` !='{$store_id}'",
//                'fields' => 'store_tag'
//            );
//            $rs = $this->storeMod->getOne($query);
//            if ($rs['store_tag']) {
//                $this->setData($info, $status = '0', $a['store_tags']);
//            }
//        }
//        if(empty($store_userid)){
//            $this->setData($info,$status='0',$message='站点管理员不能为空！');
//        }
        //        if (empty($image_id)) {
//            $this->setData($info, $status = '0', $a['store_image_id']);
//        }
//        if (empty($shipping_method)) {
//            $this->setData($info, $status = '0', $a['store_method']);
//        } else {
//            $method = '';
//            foreach ($shipping_method as $k => $v) {
//                $method .=$v . ',';
//            }
//        }
        if (empty($store_logo)) {
            $this->setData($info, $status = '0', $this->langDataBank->project->upload_logo);
        }
//        if (empty($background_img)) {
//            $this->setData($info, $status = '0', $this->langDataBank->project->upload_back);
//        }

        if (empty($store_mobile)) {
            $this->setData($info, $status = '0', $this->langDataBank->project->contact_length);
        }
//        if (strlen($store_mobile) != 11) {
//            $this->setData($info, $status = '0', $a['store_mobiles']);
//        }
//        if (!preg_match('/^1[34578]\d{9}$/', $store_mobile)) {
//            $this->setData($info, $status = '0', $a['store_mobilesx']);
//        }
        if ($switchLan == 1) {
            if (empty($pro_id) || empty($city_id) || empty($area_id)) {
                $this->setData($info, $status = '0', $this->langDataBank->project->province_required);
            }
            $store_address = $pro_id . '_' . $city_id . '_' . $area_id;
        } else {
            if (empty($country_id) || empty($zhou_id)) {
                $this->setData($info, $status = '0', site_country_required);
            }
            $store_address = $country_id . '_' . $zhou_id;
        }
        if (empty($addr_detail)) {
            $this->setData($info, $status = '0', $this->langDataBank->project->address_required);
        }

        if ($switchLan == 1) {
//            if(empty($shi_id)){
//                $this->setData($info, $status = '0', $a['store_ps_area']);
//            }
            if (empty($area)) {
                $this->setData($info, $status = '0', $this->langDataBank->project->delivery_area_required);
            }
            $shipping_scope = $area;
        } else {
//            if (empty($delivery_country_id)) {
//                $this->setData($info, $status = '0', $a['store_ps_area']);
//            }
            if (empty($g_area)) {
                $this->setData($info, $status = '0', $this->langDataBank->project->delivery_area_required);
            }
            $shipping_scope = $g_area;
        }
        if ($type) {
            foreach ($type as $key => $value) {
                if ($value && empty($activity_id[$key])) {
                    $this->setData($info, $status = '0', $this->langDataBank->project->select_activity);
                }
            }
        }
        if (empty($longitude)) {
            $this->setData($info, $status = '0', $this->langDataBank->project->longitude_required);
        }
        if (empty($latitude)) {
            $this->setData($info, $status = '0', $this->langDataBank->project->latitude_required);
        }
        $backgroundList = array();
        foreach ($background_img as $key => $value) {
            $backgroundList[$key]['background'] = $value;
            $backgroundList[$key]['type'] = $type[$key];
            $backgroundList[$key]['activity_id'] = $activity_id[$key];
        }
        $background = serialize($backgroundList);
        $insert_data = array(
            // 'is_site' => 2,
            //  'store_code' => $store_code,
//            'store_name' => addslashes(stripslashes($store_name)),
            //  'store_tag' => $store_tag,
            //  'store_cate_id' => $store_cate_id,
//            'store_userid'=>$store_userid,
//            'store_username'=>$this->getAccountName($store_userid),
            'store_mobile' => $store_mobile,
            'store_address' => $store_address,
            'addr_detail' => $addr_detail,
            //  'image_url' => $image_id,
            //  'shipping_method' => $method,
            'shipping_scope' => $shipping_scope,
            //   'mer_recommd' => $mer_recommd,
            //  'sort' => $sort,
            'modity_time' => time(),
            'lang_id' => $lang_id,
            'longitude' => $longitude,
            'latitude' => $latitude,
            'currency_id' => $currency_id,
            'logo' => $store_logo,
            'distance' => $distance,
            'fee' => $fee,
            'store_start_time' => $store_Start,
            'store_end_time' => $store_End,
            'store_notice' => $store_Notice,
            'background_img'=>$background
        );
        $insert_id = $this->storeMod->doEdit($store_id, $insert_data);
        $busiMod = &m('storebusiness');
        $where = 'store_id =' . $store_id;
        $busiMod->doDrops($where);

        foreach ($catId as $v) {
            $cateData[] = array('store_id' => $store_id, 'buss_id' => $v);
        }
        foreach ($cateData as $v) {
            $busiMod->doInsert($v);
        }
        $sql = "SELECT store_type,lang_id FROM " . DB_PREFIX . 'store where id=' . $store_id;
        $store_type = $this->storeMod->querySql($sql);
        //生成2维码
        $code = $this->goodsZcode($store_id, $store_type[0]['store_type'], $store_type[0]['lang_id']);
        $urldata = array(
            "table" => "store",
            'cond' => 'id = ' . $store_id,
            'set' => "store_url='" . $code . "'",
        );
        $this->storeMod->doUpdate($urldata);


        if ($insert_id) {
            //生成店铺对应的订单表（order表,order_details表,order_relation表）
            $initMod = &m('init');
            $initMod->initTable($store_id);
            //删除原来的多版本信息
            $gCLangMod = &m('areaStoreLang');
            $where = '  store_id =' . $store_id;
            $gCLangMod->doDrops($where);
            //添加多语言版本信息
            $this->doLangData($name, $store_id);
            if ($name_fu) {
                //添加多语言版本信息
                $this->doLangDatas($name_fu, $store_id);
            }
            $this->addLog('站点修改操作');
            $info['url'] = "?app=areaStore&act=index&p={$p}";
            $this->setData($info, $status = '1', $this->langDataBank->public->edit_success);
        } else {
            $this->setData($info, $status = '0', $this->langDataBank->public->edit_fail);
        }
    }

    /**
     * 获取多语言的版本的id
     */
    public function getCtgLang($name, $id)
    {
        //多语言版本
        $gCLangMod = &m('areaStoreLang');
        $sql = 'select  id,lang_id,store_name,store_id  from   ' . DB_PREFIX . 'store_lang
                 where  store_id =' . $id . '   and   store_name = "' . $name . '"';
        $item = $gCLangMod->querySql($sql);
        if (!empty($item)) {
            return $item[0]['id'];
        } else {
            return 0;
        }
    }

    /**
     * 单个删除操作
     * @author wanyan
     * @date 2017-9-06
     */
    public function dele()
    {
        $id = !empty($_REQUEST['id']) ? htmlspecialchars($_REQUEST['id']) : '0';
        $rs = $this->storeMod->doDrop($id);
        //删除子表的信息
        $gCLangMod = &m('areaStoreLang');
        $where = '  store_id =' . $id;
        $ctglangids = $gCLangMod->doDrops($where);
        //删除管理角色的信息
        $storeUserAdminMod = &m('storeUserAdmin');
        $where = '  store_id =' . $id;
        $storeUserAdmin = $storeUserAdminMod->doDrops($where);
        if ($rs && $ctglangids) {

            $this->addLog('站点删除操作');
            $this->setData($info = array(), $status = '1', $this->langDataBank->public->drop_success);
        } else {
            $this->setData($info = array(), $status = '0', $this->langDataBank->public->drop_fail);
        }
    }

    //区域规则
    public function storeIndex()
    {
        $store_id = $_REQUEST['store_id'] ? $_REQUEST['store_id'] : 0;
        $sql = "SELECT * FROM " . DB_PREFIX . 'store_point_site where store_id=' . $store_id;
        $storePointMod = &m('storePoint');
        $data = $storePointMod->querySql($sql);
        if (empty($data)) {
            $data[0]['store_id'] = $store_id;
        }
        $this->assign('res', $data[0]);
        $this->display('userPoint/storeSite.html');
    }

    /**
     * 店铺的小程序二维码
     * @author tangp
     * @date 2018-10-25
     */
    public function getStoreXcxCode()
    {
        $storeMod = &m('store');
        $store_id = $_REQUEST['id'];
//        var_dump($store_id);die;
        $sql = "SELECT * FROM bs_store_business WHERE store_id=".$store_id;
        $buss = $storeMod->querySql($sql);
        $buss_id = $buss[0]['buss_id'];
        $post_data = json_encode(array(
            'width' => 120,
            "scene"=>"$buss_id,$store_id",
            "page"=>"pages/storeList/storeList"
        ));
        $access_token = $this->getAccessToken();
        // 为二维码创建一个文件
        $mainPath = ROOT_PATH . '/upload/xcxCode';
        $this->mkDir($mainPath);
        $timePath = date('Ymd');
        $savePath = $mainPath . '/' . $timePath;
        $this->mkDir($savePath);
        $newFileName = uniqid() . ".png";
        $filename = $savePath . '/' . $newFileName;
        $pathName = 'upload/xcxCode/' . $timePath . '/' . $newFileName;
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$access_token;
        $result = $this->httpRequest($url,$post_data,'POST');
        $res = file_put_contents($pathName,$result);
        $urldata = array(
            "table" => "store",
            'cond' => 'id = ' . $store_id,
            'set' => "xcx_url='" . $pathName . "'",
        );
        $resss = $this->storeMod->doUpdate($urldata);
        if ($res){
            $info['url'] = "?app=areaStore&act=index";
            $this->setData($info,1,'生成成功');
        }else{
            $this->setData(array(),0,'生成失败');
        }
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
     * 读取access_token
     */
//    public function getAccessToken()
//    {
//        $appid = 'wx9346f7520e980329';
//        $secret = '1f9eb93c8b71e58998334853d4b2eb83';
//        $file = file_get_contents("./access_token.json",true);
//        $result = json_decode($file,true);
//        if (time() > $result['expires']){
//            $data = array();
//            $data['access_token'] = $this->getNewToken($appid,$secret);
//            $data['expires']      = time() + 7000;
//            $jsonStr = json_encode($data);
//            $fp = fopen("./access_token.json","w");
//            fwrite($fp,$jsonStr);
//            fclose($fp);
//            return $data['access_token'];
//        } else {
//            return $result['access_token'];
//        }
//    }

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

        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }
}

?>