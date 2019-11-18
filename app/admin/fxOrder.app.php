<?php

/**
 * 商铺订单查看
 * @author wanyan
 * @date 2017-11-17
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class fxOrderApp extends BackendApp {

    private $storeCateMod;
    private $storeMod;
    private $storeLangMod;
    private $langId;
    private $fxRulerMod;
    private $lang_id;
    private $fxStoreSettingMod;
    private $fxRevenueLogMod;
    private $orderMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->storeCateMod = &m('storeCate');
        $this->storeMod = &m('store');
        $this->storeLangMod = &m('areaStoreLang');
        $this->fxRulerMod = &m('fxrule');
        $this->orderMod = &m('order');
        $this->fxStoreSettingMod = &m('fxStoreSetting');
        $this->fxRevenueLogMod = &m('fxRevenueLog');
        $this->langId = $this->storeInfo['lang_id'];
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : 0;
    }

    /**
     * 商铺分销订单页面
     * @author wanyan
     * @date 2017-11-22
     */
    public function orderlist() {
        $lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : 0;
        // var_dump($_REQUEST);
        $fx_name = !empty($_REQUEST['fx_name']) ? htmlspecialchars(trim($_REQUEST['fx_name'])) : '';
        $start_time = !empty($_REQUEST['start_time']) ? htmlspecialchars(trim($_REQUEST['start_time'])) : '';
        $end_time = !empty($_REQUEST['end_time']) ? htmlspecialchars(trim($_REQUEST['end_time'])) : '';
        $where = " where 1=1";
        if (!empty($fx_name)) {
            $where .= "  and (`lev1_user_name` like '%$fx_name%') OR (`lev2_user_name` like '%$fx_name%') OR (`lev3_user_name` like '%$fx_name%') ";
        }
        if (!empty($start_time) && !empty($end_time)) {
            $startTime = strtotime($start_time);
            $endTime = strtotime($end_time);
            if ($startTime > $endTime) {
                $temp = $start_time;
                $start_time = $end_time;
                $end_time = $temp;
            }
            $start = $start_time;
            $end = $end_time . '23:59:59';
            $start_time = strtotime($start);
            $end_time = strtotime($end);
            $where .= " and `add_time` >=$start_time and `add_time` <=$end_time";
        }

        $sql = "select * from " . DB_PREFIX . "fx_revenue_log" . $where;
        $info = $this->fxRevenueLogMod->querySqlPageData($sql);
        foreach ($info['list'] as $k => $v) {
            $buyInfo = $this->getOrderAddress($v['order_sn']);
            $info['list'][$k]['buyer_name'] = $buyInfo['buyer_name'];
            $info['list'][$k]['buyer_address'] = $buyInfo['buyer_address'];
            $info['list'][$k]['xd_time'] = date('Y-m-d H:i:s', $buyInfo['add_time']);
            $info['list'][$k]['finished_time'] = date('Y-m-d H:i:s', $buyInfo['finished_time']);
            $info['list'][$k]['country_name'] = $this->getCountryName($v['store_cate']);
            $info['list'][$k]['store_name'] = $this->getStoreName($v['store_id']);
            $info['list'][$k]['order_money'] = $this->getSymbol($v['store_cate']) . ' ' . $v['order_money'];
            $info['list'][$k]['lev1_revenue'] = $this->getSymbol($v['store_cate']) . ' ' . $v['lev1_revenue'];
            $info['list'][$k]['lev2_revenue'] = $this->getSymbol($v['store_cate']) . ' ' . $v['lev2_revenue'];
            $info['list'][$k]['lev3_revenue'] = $this->getSymbol($v['store_cate']) . ' ' . $v['lev3_revenue'];
        }
//        dd($info['list']);die;
        $this->assign('fx_name', $fx_name);
        $this->assign('start_time', date('Y-m-d', $start_time));
        $this->assign('end_time', date('Y-m-d', $end_time));
        $this->assign('list', $info['list']);
        $this->assign('$page_html', $info['ph']);
        $this->assign('lang_id', $lang_id);
        $this->display('fxOrder/orderList.html');
    }

    /**
     * 查询订单收货地址
     * @author wanyan
     * @date 2017-11-22
     */
    public function getOrderAddress($order_sn) {
        $rs = $this->orderMod->getOne(array('cond' => "`order_sn`='{$order_sn}'", 'fields' => 'buyer_name,buyer_address,add_time,finished_time'));
        return $rs;
    }

    /**
     * 国家名称
     * @author wanyan
     * @date 2017-11-22
     */
    public function getCountryName($store_cate) {
        $rs = $this->storeCateMod->getOne(array('cond' => "`id`='{$store_cate}'", 'fields' => 'cate_name'));
        return $rs['cate_name'];
    }

    /**
     * 店铺名称
     * @author wanyan
     * @date 2017-11-22
     */
    public function getStoreName($store_id) {
        $rs = $this->storeLangMod->getOne(array('cond' => "`store_id`='{$store_id}' and lang_id = " . $this->lang_id, 'fields' => 'store_name'));
        return $rs['store_name'];
    }

    /**
     * 查看商品详情
     * Date: 2017/10/27
     */
    public function getGoods() {
        $order_id = $_REQUEST['order_id'];
        $orderGoodMod = &m('orderDetail');
        $info = $orderGoodMod->getData(array('cond' => "`order_id`='{$order_id}'", 'fields' => "store_id,spec_key_name,goods_name,goods_image,goods_price,goods_pay_price"));
        foreach ($info as $k => $v) {
            $store_cate = $this->getCountryId($v['store_id']);
            $info[$k]['symbol'] = $this->getSymbol($store_cate);
        }
        $lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : 0;
        $this->assign('lang_id', $lang_id);
        $this->assign('info', $info);
        $this->display('fxOrder/dialog.html');
    }

}
