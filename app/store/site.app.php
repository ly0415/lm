<?php

/**
 * 区域站点设置
 * @author  wangshuo
 * @date 2017-9-11
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class siteApp extends BaseStoreApp {

    private $siteMod;
    private $storeMod;
    private $currencyMod;
    private $cityMod;
    private $storeCateMod;
    private $lang_id;
    private $countryMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->storeCateMod = &m('storeCate');
        $this->siteMod = &m('site');
        $this->storeMod = &m('store');
        $this->currencyMod = &m('currency');
        $this->cityMod = &m('city');
        $this->countryMod = &m('country');
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
    }

    /**
     * 区域站点展示页面
     * @author  wangshuo
     * @date 2017-9-13
     */
    public function SiteList() {
        //中英切换
        if ($_GET['lang_id'] == 0) {
            $str = 'ad_name';
        } else {
            $str = 'ad_english_name';
        }
        $this->assign('lang_id', $_GET['lang_id']);
        $storeId = $this->storeId;
//        $sqls = 'select * from ' . DB_PREFIX . 'store  where id =' . $storeId;
        $sqls = 'SELECT c.*,l.store_name as lstorename FROM  ' . DB_PREFIX . 'store  as c
                 left join  ' . DB_PREFIX . 'store_lang as l on c.id = ' . $storeId . ' and  l.store_id = c.id
                 WHERE l.lang_id =' . $this->defaulLang . ' and l.distinguish = 0';
        $currencyInfo = $this->currencyMod->getCurrencyById($storeId);
        //活动列表
        $activityMod = &m('storeActivity');
        $activity = $activityMod->getData(array('cond' => 'mark = 1 and is_use = 1 and store_id='.$this->storeId));
        $this->assign('activity', $activity);
        $this->assign('flag', count($activity));
        $cur_list = $this->currencyMod->getData(array("cond" => "mark=1"));
        $this->assign('cur_list', $cur_list);
        $this->assign('list', $currencyInfo);
        $datas = $this->siteMod->querySql($sqls);
        $backgroundInfo = unserialize($datas[0]['background_img']);
        $this->assign('backgroundInfo', $backgroundInfo);
        $store_address = explode('_', $datas[0]['store_address']);
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
        $this->assign('pros', $this->cityMod->getParentNodes());
        $this->assign('countrys', $this->countryMod->getCountryNodes());
        $store_type = G('store_type');
        $this->assign('store_type', $store_type);
        $this->assign('store_cate', $this->getAreaCate());
        $this->assign('datas', $datas[0]);
        $this->display('site/site.html');
    }

    /**
     * 获取区域站点分类
     * @author wanyan
     * @date 2017-08-31
     */
    public function getAreaCate($cate_id = null) {
        $where = " where 1=1 ";
        if ($cate_id) {
            $where .= " and sc.id=" . $cate_id;
        } else {
            $where .= "  and scl.lang_id =29";
        }
        $sql = "SELECT sc.id,scl.cate_name FROM `bs_store_cate` as sc LEFT JOIN `bs_store_cate_lang`
        as scl ON sc.id=scl.cate_id " . $where;
        $rs = $this->storeCateMod->querySql($sql);
        return $rs;
    }

    /**
     * 获取国外地址
     * @author wanyan
     * @date 2017-9-05
     */
    public function getGzone($id) {
        $sql = "select z.zone_id,z.name from " . DB_PREFIX . "country as c left join " . DB_PREFIX . "zone as z on c.country_id = z.country_id where c.country_id = {$id}";
        $rs = $this->storeMod->querySql($sql);
        return $rs;
    }

    /**
     * 获取中国地址
     * @author wanyan
     * @date 2017-9-05
     */
    public function getCity($id) {
        $sql = "select `id`,`name` from " . DB_PREFIX . "city where `parent_id`='{$id}'";
        $rs = $this->storeMod->querySql($sql);
        return $rs;
    }

    /**
     * 区域站点编辑
     * @author  wangshuo
     * @date 2017-9-16
     */
    public function doEdit() {
        $store_logo = !empty($_REQUEST['store_logo']) ? htmlspecialchars(trim($_REQUEST['store_logo'])) : '';
        $background_img = !empty($_REQUEST['background_img']) ? $_REQUEST['background_img'] : '';
        $activity_id = !empty($_REQUEST['activity_id']) ? $_REQUEST['activity_id'] : '';
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $lang_id = $_REQUEST['lang_id'];
        $store_discount = !empty($_REQUEST['store_discount']) ? htmlspecialchars(trim($_REQUEST['store_discount'])) : '1.00';
        $store_mobile = !empty($_REQUEST['store_mobile']) ? htmlspecialchars(trim($_REQUEST['store_mobile'])) : '';
        if (empty($store_mobile)) {
            $this->setData($info, $status = '0', $a['store_mobile']);
        }
//        if (strlen($store_mobile) != 11) {
//            $this->setData($info, $status = '0', $a['store_mobiles']);
//        }
//        if (!preg_match('/^1[34578]\d{9}$/', $store_mobile)) {
//            $this->setData($info, $status = '0', $a['store_mobilesx']);
//        }
        $backgroundList = array();
        foreach ($background_img as $key => $value) {
            $backgroundList[$key]['background'] = $value;
            $backgroundList[$key]['activity_id'] = $activity_id[$key];
        }
        $background = serialize($backgroundList);
        $data['table'] = 'store';
        $data['cond'] = 'id =' . $_REQUEST['id'];
        $data['set'] = array(
            "id" => $_REQUEST['id'],
            "store_mobile" => $store_mobile,
            'logo' => $store_logo,
            'store_discount' => $store_discount,
            'background_img'=>$background
        );

        $result = $this->storeMod->doUpdate($data);

        if ($result) {

            $this->setData(array('url' => 'store.php?app=site&act=SiteList&lang_id=' . $lang_id), '1', $a['edit_Success']);
        } else {
            $this->setData(array(), '0', $a['edit_fail']);
        }
    }

    /**
     * 获取汇率
     * @author  wangshuo
     * @date 2017-9-16
     */
    public function getStoreCur($store_id) {
        $sqls = 'select `currency_id` from ' . DB_PREFIX . 'store  where id =' . $store_id;
        $rs = $this->storeMod->querySql($sqls);
        return $rs[0]['currency_id'];
    }

    /**
     * 获取城市和区域列表
     * @author wanyan
     * @date 2017-08-31
     */
    public function getAjaxData() {
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

}
