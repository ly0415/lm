<?php

/**
 * 手机app
 * @author lvji
 * @date 2015-3-10
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class DefaultApp extends BackendApp {

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
     * @author lvji
     * @date 2015-3-10
     */
    public function index() {
        $area_id = $_REQUEST['area_id'] ? htmlspecialchars(trim($_REQUEST['area_id'])) : '';
        $store_id = $_REQUEST['store_id'] ? htmlspecialchars(trim($_REQUEST['store_id'])) : '';
        $opInfo = $_REQUEST['opInfo'] ? htmlspecialchars(trim($_REQUEST['opInfo'])) : '';

        $orderMod = &m('order');
        //交易情况异步切换
        if ($opInfo) {
            $start_time = $_REQUEST['start_time'] ? htmlspecialchars(trim($_REQUEST['start_time'])) : '';
            $end_time = $_REQUEST['end_time'] ? htmlspecialchars(trim($_REQUEST['end_time'])) : '';
            $timeSetArr = array(
                'start_time' => $start_time.' 00:00:00',
                'end_time' => $end_time.' 23:59:59'
            );
            //订单信息
            $upOrderInfo = $orderMod->getUpOrderCount(10, $area_id, $store_id, 0, $opInfo, $timeSetArr);
            //付款信息
            $payOrderInfo = $orderMod->getUpOrderCount(20, $area_id, $store_id, 1, $opInfo, $timeSetArr);
            $this->setData(array('upOrderInfo' => $upOrderInfo, 'payOrderInfo' => $payOrderInfo));
        }

        $jyInfo = $_REQUEST['jyInfo'] ? htmlspecialchars(trim($_REQUEST['jyInfo'])) : '';
        //交易趋势分析异步切换
        if ($jyInfo) {
            $start_time = $_REQUEST['start_time'] ? htmlspecialchars(trim($_REQUEST['start_time'])) : '';
            $end_time = $_REQUEST['end_time'] ? htmlspecialchars(trim($_REQUEST['end_time'])) : '';
            $timeSetArr = array(
                'start_time' => $start_time,
                'end_time' => $end_time
            );
            $trendInfo = $orderMod->getTransactionTrend($area_id, $store_id, $jyInfo, 0, $timeSetArr);

            $this->setData(array('trendInfo' => $trendInfo));
        }
        $gdInfo = $_REQUEST['gdInfo'] ? htmlspecialchars(trim($_REQUEST['gdInfo'])) : '';
        //商品TOP10异步切换
        if ($gdInfo) {
            $start_time = $_REQUEST['start_time'] ? htmlspecialchars(trim($_REQUEST['start_time'])) : '';
            $end_time = $_REQUEST['end_time'] ? htmlspecialchars(trim($_REQUEST['end_time'])) : '';
            $timeSetArr = array(
                'start_time' => $start_time,
                'end_time' => $end_time
            );
            $topInfo = $orderMod->getGoodsTop10($area_id, $store_id, $gdInfo, 0, $timeSetArr,$this->lang_id);
            $this->setData(array('topInfo' => $topInfo));
        }
        $this->addLog('登录艾美平台');

        if ($area_id) {
            $this->assign('area_id', $area_id);
            if ($store_id) {
                $this->assign('store_id', $store_id);
            }
            //获取区域店铺
            $storeMod = &m('store');
            $storeArr = $storeMod->getStoreArr($area_id, 1);
            $storeOption = make_option($storeArr, $store_id);
            $this->assign('storeOption', $storeOption);
        }
        //获取区域数组
        $storeCateMod = &m('storeCate');
        $areaArr = $storeCateMod->getAreaArr(1,$this->lang_id);
        $areaOption = make_option($areaArr, $area_id);
        $this->assign('areaOption', $areaOption);

        //获取待办事项
        $backlogDaFuCount = $orderMod->getBacklogCount(10, $area_id, $store_id);
        $this->assign('backlogDaFuCount', $backlogDaFuCount);  //待付款数

        $backlogDaFaCount = $orderMod->getBacklogCount(20, $area_id, $store_id);
        $this->assign('backlogDaFaCount', $backlogDaFaCount);  //待发货数ddd

        $backlogDaTuiCount = $orderMod->getBacklogCount(0, $area_id, $store_id, true);
        $this->assign('backlogDaTuiCount', $backlogDaTuiCount);  //待退货数
        //获取商品信息
        $storeGoodsMod = &m('storeGoods');
        $onSaleGoodsCount = $storeGoodsMod->getGoodsInfoCount($area_id, $store_id);
        $this->assign('onSaleGoodsCount', $onSaleGoodsCount);   //在售商品数

        $recommendGoodsCount = $storeGoodsMod->getGoodsInfoCount($area_id, $store_id, 1, 1);
        $this->assign('recommendGoodsCount', $recommendGoodsCount);   //推荐商品
        //订单信息
        $upOrderInfo = $orderMod->getUpOrderCount(0, $area_id, $store_id);
        $this->assign('upOrderInfo', $upOrderInfo);   //下单信息
        //付款信息
        $payOrderInfo = $orderMod->getUpOrderCount(20, $area_id, $store_id, 1);
        $this->assign('payOrderInfo', $payOrderInfo);   //付款信息
        //交易趋势分析
        $trendInfo = $orderMod->getTransactionTrend($area_id, $store_id, 'day', 1);
        $this->assign('trendInfo', $trendInfo);         //交易趋势
        //商品TOP10
        $top10 = $orderMod->getGoodsTop10($area_id, $store_id, 'day', 1,'',$this->lang_id);
        $this->assign('top10', $top10);         //商品TOP10
        $this->display('index.html');
    }

    //创建验证码
    public function createCode() {
        import('captcha.lib');
        $captchaObj = new Captcha();
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

}

?>