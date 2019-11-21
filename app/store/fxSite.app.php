<?php

/**
 * 商铺分销设置
 * @author wanyan
 * @date 2017-11-17
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class fxSiteApp extends BaseStoreApp {

    private $storeCateMod;
    private $storeMod;
    private $langId;
    private $land_id;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->storeCateMod = &m('storeCate');
        $this->storeMod = &m('store');
        $this->fxSiteMod = &m('fxSite');
        $this->langId = $this->storeInfo['lang_id'];
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : 0;
    }

    /**
     * 商铺规则首页
     * @author wanyan
     * @date 2017-11-17
     */
    public function index() {
        $store_id = $_REQUEST['store_id']?$_REQUEST['store_id']:$this->storeId;
        $info = $this->fxSiteMod->getOne(array("cond"=>"store_id=" . $store_id));
        $this->assign("datas", $info);
        $land_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : 0;
        if($this->storeInfo['store_type'] == 1){
            //获取店铺名称
            $storeMod = &m('store');
            $store_data = $storeMod->getStoreArr($this->storeInfo['store_cate_id']);
            $this->assign('store_data', $store_data);
            $this->assign('is_all', "all");
        }
        $this->assign('store_id',$store_id);
        $this->assign('lang_id', $land_id);
        $this->display('fxSite/site.html');
    }

    /**
     * 区域分销配置
     * @author lee
     * @date 2017-11-20 15:27:05
     */
    public function doEdit() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $id = ($_REQUEST['id']) ? $_REQUEST['id'] : '';
//        $is_order_day=($_REQUEST['is_order_day'])?$_REQUEST['is_order_day']:2;
        $order_day = ($_REQUEST['order_day']) ? $_REQUEST['order_day'] : '';
        $is_money = ($_REQUEST['is_money']) ? $_REQUEST['is_money'] : 2;
        $money = ($_REQUEST['money']) ? $_REQUEST['money'] : '';
        $is_time = ($_REQUEST['is_time']) ? $_REQUEST['is_time'] : 2;
        $time = ($_REQUEST['time']) ? $_REQUEST['time'] : '';
        $store_id = $_REQUEST['single_store']?$_REQUEST['single_store']:'';
//        $is_drawing_day=($_REQUEST['is_drawing_day'])?$_REQUEST['is_drawing_day']:2;
//        $drawing_day=($_REQUEST['drawing_day'])?$_REQUEST['drawing_day']:'';
        if (empty($order_day)) {
            $this->setData(array(), $status = '0', $a['fx_order']);
        }

        if (($is_money == 1) && empty($money)) {
            $this->setData(array(), $status = '0', $a['fx_money']);
        }
        if (($is_time == 1) && empty($time)) {
            $this->setData(array(), $status = '0', $a['fx_time']);
        }
//        if(($is_drawing_day==1) && empty($drawing_day)){
//            $this->setData(array(), $status = '0', $a['fx_drawing']);
//        }
        $data = array(
            'order_day' => $order_day,
            'is_money' => $is_money,
            'money' => $money,
            'is_time' => $is_time,
            'time' => $time,
//            'is_drawing_day'=>$is_drawing_day,
//            'drawing_day'=>$drawing_day
        );
        $res = $this->fxSiteMod->doEdit($id, $data);
        if ($res) {
            $this->setData(array('url' => "?app=fxSite&act=index&lang_id={$this->land_id}&store_id={$store_id}"), $status = '1', $a['edit_Success']);
        } else {
            $this->setData(array(), $status = '0', $a['edit_fail']);
        }
    }

}
