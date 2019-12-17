<?php

/**
 * 订单列表
 * @author wangshuo
 * @date 2017-10-20
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class orderListApp extends BackendApp
{
    private $orderMod;
    private $storeMod;
    private $orderGoodsMod;
    private $giftGoodMod;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->orderGoodsMod = &m('orderGoods');
        $this->orderMod = &m('order');
        $this->storeMod = &m('store');
        $this->giftGoodMod = &m('giftGood');
    }

    public function index()
    {
        $_REQUEST['service_area_id'] = !empty($_REQUEST['service_area_id']) ? htmlspecialchars(trim($_REQUEST['service_area_id'])) : 17;
        $_REQUEST['selectStoreId'] = !empty($_REQUEST['selectStoreId']) ? htmlspecialchars(trim($_REQUEST['selectStoreId'])) : 58;
        $_REQUEST['storeId'] = $_REQUEST['selectStoreId'];
        //获取订单列表
        $data = $this->orderMod->orderList($_REQUEST, $_REQUEST['p']);
        $orderData = $data['orderData']['list'];
        $page = $data['orderData']['ph'];
        $coditionData = $data['coditionData'];
        $this->assign('orderData', $orderData);
        $this->assign('coditionData', $coditionData);
        $this->assign('page', $page);
        //测试环境允许查看测试站点
        $http_host = $_SERVER['HTTP_HOST'];
        if ($http_host == 'www.njbsds.cn') {
            $notAllowStore = array();
        } else {
            $notAllowStore = array(84, 98);
        }
        $this->assign('notAllowStore', $notAllowStore);
        //获取区域国家
        $area_data = &m('storeCate')->getAreaArr(1, $this->lang_id);

        $service_area_data = array_map(function ($i, $m) {
            return array('id' => $i, 'name' => $m);
        }, array_keys($area_data), $area_data);

        $this->assign('service_area_data', $service_area_data);
        //获取区域店铺
        $selectStoreInfo = $this->storeMod->getStore($_REQUEST['service_area_id'], 1, 0);
        $selectStoreInfo = &m('api')->convertArrForm($selectStoreInfo);
        $this->assign('selectStoreInfo', $selectStoreInfo);
        $this->display("orderList/index.html");
    }

    /**
     * 联动店铺
     */
    public function getStoreSelect()
    {
        $type = $_REQUEST['type'] ? (int)$_REQUEST['type'] : 0;
        $id = $_REQUEST['id'] ? (int)$_REQUEST['id'] : 0;
        $store_ids = $_REQUEST['store_ids'];
        switch ($type) {
            // 获取店铺
            case 1:
                $data = &m('store')->getStore($id, 1, $store_ids);
                $data = &m('api')->convertArrForm($data);
                break;
        }
        $this->setData($data, 1);
    }

    /**
     * 订单详情页面
     * @author wangs
     * @date 2017/10/24
     */
    public function orderDetails()
    {
        $order_sn = !empty($_REQUEST['order_sn']) ? $_REQUEST['order_sn'] : '201903060956463646'; //TODO  待定取死的订单号
        $orderMod = &m('order');
        $res = $orderMod->selectOrderInfo($order_sn);
        $sqls = "SELECT cid FROM bs_order WHERE order_sn = {$order_sn}";
        $result = $orderMod->querySql($sqls);
        $fxCode = $orderMod->getFxCode($order_sn);
        $this->assign('fxCode',$fxCode);
        $couponData = "SELECT `type` FROM `bs_coupon` WHERE id = " . $result[0]['cid'];
        $data = $orderMod->querySql($couponData);
        if ($data[0]['type'] == 1) {
            $sql1 = "SELECT `type`,`discount`,`money` FROM `bs_coupon` WHERE id = " . $result[0]['cid'];
            $datass = $orderMod->querySql($sql1);
            $this->assign('datass',$datass);
            $order_sn = explode('-', $order_sn);
            $logSql = "SELECT user_coupon_id FROM bs_coupon_log WHERE order_sn = {$order_sn[0]}";
            $ress = $orderMod->querySql($logSql);
            $sss = "SELECT start_time,end_time FROM bs_user_coupon WHERE id = " . $ress[0]['user_coupon_id'];
            $userCouponData = $orderMod->querySql($sss);
            $this->assign('userCouponData',$userCouponData);
        }else if ($data[0]['type'] == 2){
            $sqlsss = "SELECT cid FROM bs_order WHERE order_sn = '{$order_sn}'";
            $results = $orderMod->querySql($sqlsss);
            $sql1 = "SELECT `type`,`discount`,`money` FROM `bs_coupon` WHERE id = " . $results[0]['cid'];
            $datass = $orderMod->querySql($sql1);
            $this->assign('datass',$datass);
            $logSql = "SELECT user_coupon_id FROM bs_coupon_log WHERE order_sn = '{$order_sn}'";
            $ress = $orderMod->querySql($logSql);
            $sss = "SELECT start_time,end_time FROM bs_user_coupon WHERE id = " . $ress[0]['user_coupon_id'];
            $userCouponData = $orderMod->querySql($sss);
            $this->assign('userCouponData',$userCouponData);
        }

        $refundInfo = $orderMod->getRefundRecord($order_sn, $res[0]['store_id'], $res[0]['goods']);
        $userInfo = &m('userInfo');
        $sourceData= $userInfo->source;
        $this->assign('sourceData',$sourceData);
        $infoData = $userInfo->getUserInfo($order_sn);
        $this->assign('infoData',$infoData);
        $datas = $userInfo->countUserInfo($order_sn);
        $this->assign('datas',$datas);
        $sql = "SELECT * FROM bs_user_order WHERE order_sn = {$order_sn}";
        $store_id = &m('userOrder')->querySql($sql);
        $userAddress = $orderMod->getOrderAddress($order_sn,$store_id[0]['store_id']);
        $goodsCount = count($res[0]['goods']);
        $this->assign('userAddress',$userAddress);
        $this->assign('result',$result);
        $this->assign('res',$res);
        $this->assign('goodsCount',$goodsCount);
        $this->assign('refundInfo',$refundInfo);
        $this->assign('payment_type',$res[0]['orderRelation'][0]);
        $this->assign('pay_sn',$res[0]['orderDetail'][0]);
        $this->display('orderList/details.html');

    }
}
