<?php

/**
 * 我的分销订单
 * @author wanyan
 * @date 2017/08/22
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class MyFxOrderApp extends BaseFrontApp {

    private $fxRevenueLogMod;
    private $orderDetailMod;
    private $storeMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->fxRevenueLogMod = &m('fxRevenueLog');
        $this->orderDetailMod = &m('orderGoods');
        $this->storeMod = &m('store');
    }

    /**
     *  我的分销订单
     * @author wanyan
     * @date 2017/11/24
     */
    public function index() {
        $sql = "select * FROM " . DB_PREFIX . "fx_revenue_log WHERE (`lev1_user_id` = $this->userId OR lev2_user_id=$this->userId OR lev3_user_id = $this->userId)";
        $rs = $this->fxRevenueLogMod->querySqlPageData($sql);
        $info = array();
        $this->load($this->shorthand, 'myOrder/myOrder');
        $a = $this->langData;
        foreach ($rs['list'] as $k => $v) {
            $data['order_sn'] = $v['order_sn'];
            $data['order_money'] = $v['order_money'];
            $data['order_id'] = $v['order_id'];
            $data['order_state'] = $a['myacc_receipt'];
            if ($v['lev1_user_id'] == $this->userId) {
                $data['user_name'] = $v['lev1_user_name'];
                $data['revenue'] = $v['lev1_revenue'];
                $info[] = $this->combine($data);
            } elseif ($v['lev2_user_id'] == $this->userId) {
                $data['user_name'] = $v['lev2_user_name'];
                $data['revenue'] = $v['lev2_revenue'];
                $info[] = $this->combine($data);
            } elseif ($v['lev3_user_id'] == $this->userId) {
                $data['user_name'] = $v['lev3_user_name'];
                $data['revenue'] = $v['lev3_revenue'];
                $info[] = $this->combine($data);
            }
        }
        $this->assign('list', $info);
        $this->assign('page', $rs['ph']);
        $this->assign('langid', $this->langid);
        $this->assign('storeid', $this->storeid);
        $this->assign('langdata', $this->langData);
        $this->display('myFxOrder/myOrder.html');
    }

    /**
     *  弹出框
     * @author wanyan
     * @date 2017/11/24
     */
    public function dialog() {
        $this->load($this->shorthand, 'myOrder/myOrder');
        $this->assign('langdata', $this->langData);
        $id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : '0';
        $rs = $this->orderDetailMod->getOne(array('cond' => "`order_id` ='{$id}'", 'fields' => "`goods_name`,goods_price,goods_num,goods_image,goods_pay_price,spec_key_name,prom_type,order_id"));
        $rs['store_name'] = $this->getStoreNameById($this->storeid);
        $this->assign('rs', $rs);
        $this->display('myFxOrder/dialog.html');
    }

    /**
     *  获取店铺名称
     * @author wanyan
     * @date 2017/11/24
     */
    public function getStoreNameById($store_id) {
        $rs = $this->storeMod->getOne(array('cond' => "`id` = '{$store_id}' ", 'fields' => 'store_name'));
        return $rs['store_name'];
    }

    /**
     *  组合参数
     * @author wanyan
     * @date 2017/11/24
     */
    public function combine($data) {
        $rs = array(
            'order_sn' => $data['order_sn'],
            'fx_username' => $data['user_name'], // 分销人员用户名称
            'order_money' => $data['order_money'],
            'revenue' => $data['revenue'], //佣金
            'order_state' => $data['order_state'],
            'order_id' => $data['order_id'],
        );
        return $rs;
    }

}
