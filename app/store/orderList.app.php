<?php

/**
 * 订单列表
 * @author wangshuo
 * @date 2017-10-20
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class orderListApp extends BaseStoreApp {

    private $lang_id;
    private $orderGoodsMod;
    private $orderMod;
    private $corplistMod;
    private $storeMod;
    private $giftGoodMod;
    private $areaGoodMod;
    private $storeGoodItemPriceMod;
    private $fxRuleMod;
    private $fxRevenueLogMod;
    private $fxUserMod;
    private $fxUserTreeMod;
    private $fxTreeMod;
    private $fxuserMoneyMod;
    private $goodsMod;
    private $goodsSpecPriceMod;
    private $storeCateMod;
    private $amountLogMod;
    private $userMod;
    

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
        $this->orderGoodsMod = &m('orderGoods');
        $this->orderMod = &m('order');
        $this->corplistMod = &m('corplist');
        $this->storeMod = &m('store');
        $this->giftGoodMod = &m('giftGood');
        $this->areaGoodMod = &m('areaGood');
        $this->storeGoodItemPriceMod = &m('storeGoodItemPrice');
        $this->fxRuleMod = &m('fxrule');
        $this->fxRevenueLogMod = &m('fxRevenueLog');
        $this->fxTreeMod = &m('fxuserTree');
        $this->fxUserMod = &m('fxuser');
        $this->fxUserTreeMod = &m('fxuserTree');
        $this->fxuserMoneyMod = &m('fxuserMoney');
        $this->goodsMod =&m('goods');
        $this->goodsSpecPriceMod =&m('goodsSpecPrice');
        $this->storeCateMod = &m('storeCate');
        $this->amountLogMod = &m('amountLog');
        $this->userMod=&m('user');
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    


    /**
     * 订单分配
     * @author wangshuo
     * @date 2017-12-25
     */
    public function editAppoint() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $lang_id = !empty($_REQUEST['lang_id']) ? (int) ($_REQUEST['lang_id']) : '0';
        $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars($_REQUEST['store_id']) : '';
        $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars($_REQUEST['order_sn']) : '';
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $arrs = array(
            'Appoint' => 2,
            'Appoint_store_id' => $store_id,
        );
        $datas = array(
            "table" => "order",
            'cond' => "order_sn= '{$order_sn}'"  ,
            'set' => $arrs,
        );
        $res = $this->orderMod->doUpdate($datas);
        $arr = array(
            'shipping_store_id' => $store_id,
        );
        $data = array(
            "table" => "order_goods",
            'cond' =>"order_id = '{$order_sn}'",
            'set' => $arr,
        );
        $ress = $this->orderGoodsMod->doUpdate($data);
        if ($res && $ress) {
            $info['url'] = "store.php?app=orderList&act=index_en&lang_id={$lang_id}&p={$p}";
            $this->setData($info, $status = 1, $a['Distribution_success']);
        } else {
            $this->setData(array(), $status = 0, $a['Distribution_fail']);
        }
    }

    /**
     * 订单分配
     * @author wangshuo
     * @date 2017-12-25
     */
    public function editAAppoint() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $lang_id = !empty($_REQUEST['lang_id']) ? (int) ($_REQUEST['lang_id']) : '0';
        $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars($_REQUEST['store_id']) : '';
        $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars($_REQUEST['order_sn']) : '';
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $arrs = array(
            'Appoint' => 2,
            'Appoint_store_id' => $store_id,
        );
        $datas = array(
            "table" => "order",
            'cond' => "order_sn='{$order_sn}' "  ,
            'set' => $arrs,
        );
        $res = $this->orderMod->doUpdate($datas);
        $arr = array(
            'shipping_store_id' => $store_id,
        );
        $data = array(
            "table" => "order_goods",
            'cond' => "order_id = '{$order_sn}'",
            'set' => $arr,
        );
        $ress = $this->orderGoodsMod->doUpdate($data);
        if ($res && $ress) {
            $sql = "select order_id from bs_order where order_sn ='{$order_sn}' ";
            $order_id = $this->storeMod->querySql($sql);
            $info['url'] = "store.php?app=orderList&act=details&lang_id={$lang_id}&order_id={$order_id[0]['order_id']}&p={$p}";
            $this->setData($info, $status = 1, '确认订单成功');
        } else {
            $this->setData(array(), $status = 0, '确认订单失败');
        }
    }

    /**
     * 获取启用的站点
     * @author wang'shuo
     * @date 2017-12-25
     */
    public function getUseStore() {
        $sql = 'SELECT  c.id,l.store_name  FROM  ' . DB_PREFIX . 'store  as c
                 left join  ' . DB_PREFIX . 'store_lang as l on  c.id = l.store_id
                 WHERE c.is_open = 1 and l.distinguish = 0  and  l.lang_id =' . $this->defaulLang . '  and c.store_cate_id=' . $this->country_id . ' order by c.id';
        $res = $this->storeMod->querySql($sql);
        return $res;
    }

    /**
     * 已指定订单展示页面
     * @author wangs
     * @date 2017/10/24
     */
    public function index() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $goods_name = !empty($_REQUEST['goods_name']) ? htmlspecialchars(trim(addslashes($_REQUEST['goods_name']))) : '';
        $store_id = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : '';
        $payment_code = !empty($_REQUEST['payment_code']) ? htmlspecialchars(trim($_REQUEST['payment_code'])) : '';
        $buyer_email = !empty($_REQUEST['buyer_email']) ? htmlspecialchars(trim($_REQUEST['buyer_email'])) : '';
        $buyer_name = !empty($_REQUEST['buyer_name']) ? htmlspecialchars(trim($_REQUEST['buyer_name'])) : '';
        $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars(trim($_REQUEST['order_sn'])) : '';
        $shipping_code = !empty($_REQUEST['shipping_code']) ? htmlspecialchars(trim($_REQUEST['shipping_code'])) : '';
        $state = !is_null($_REQUEST['state']) ? htmlspecialchars(trim($_REQUEST['state'])) : 'month_this';
        $clickandview = !empty($_REQUEST['clickandview']) ? htmlspecialchars(trim($_REQUEST['clickandview'])) : '0';
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $where = '';
        if (!empty($goods_name)) {
            $where .= " and f.goods_name like '%" . $goods_name . "%'";
        }
        if (!empty($payment_code)) {
            $where .= " and g.payment_code like '%" . $payment_code . "%'";
        }
        if (!empty($buyer_email)) {
            $where .= " and g.buyer_email like '%" . $buyer_email . "%'";
        }
        if (!empty($buyer_name)) {
            $where .= " and g.buyer_name like '%" . $buyer_name . "%'";
        }
        if (!empty($order_sn)) {
            $where .= " and g.order_sn like '%" . $order_sn . "%'";
        }
        if (!empty($store_id)) {
            $where .= " and g.Appoint_store_id = '{$store_id}'";
        }
        if (!empty($shipping_code)) {
            $where .= " and g.shipping_code like '%" . $shipping_code . "%'";
        }
        if (!empty($clickandview)) {
            $where .= " and g.order_state >0  and g.clickandview like '%" . $clickandview . "%'";
        }
        $now = time();
        if (!is_null($state)) {
            if ($state == 'month_pre') { //3个月钱 year month day
                $pre_three_month = strtotime('-3 months', $now);
                $where .= " and g.add_time <= ' {$pre_three_month}'";
            } elseif ($state == 'month_this') {//3个月内year month day
                $pre_three_month = strtotime('-3 months', $now);
                $where .= " and g.add_time >= ' {$pre_three_month}'";
            } elseif ($state == 'uncash') {
                $pre_three_month = strtotime('-7 days', $now);
                $where .= " and g.add_time >= ' {$pre_three_month}' and g.order_state=10";
            } else {
                $where .= " and g.order_state = ' {$state}'";
            }
        }
        $this->assign("p", $p);
        $this->assign("state", $state);
        $this->assign('goods_name', $goods_name);
        $this->assign('payment_code', $payment_code);
        $this->assign('buyer_email', $buyer_email);
        $this->assign('buyer_name', $buyer_name);
        $this->assign('order_sn', $order_sn);
        $this->assign("store_id", $store_id);
        $this->assign('shipping_code', $shipping_code);
        // 1总代理 2经销商
        $auth = $this->auth;

        if ($auth == 1) {
            // 1总代理
            //订单列表页数据
            $sql = 'select distinct g.order_sn, g.*, g.add_time from '
                    . DB_PREFIX . 'order as g left join '
                    . DB_PREFIX . 'order_goods as f ' . ' on f.order_id = g.order_sn'
                    . ' where   g.Appoint =2  ' . $where
                    . '  and g.sendout in (2,3) order by g.order_id desc';
        } else {
            //2经销商
            //订单列表页数据
            $sql = 'select distinct g.order_sn, g.*,st.*, cy.*, g.add_time from '
                    . DB_PREFIX . 'order as g  left join '
                    . DB_PREFIX . 'store as st ' . ' on g.Appoint_store_id = st.id left join '
                    . DB_PREFIX . 'currency as cy ' . ' on st.currency_id = cy.id left join '
                    . DB_PREFIX . 'order_goods as f ' . ' on f.order_id = g.order_sn'
                    . ' where  g.Appoint =2  and g.Appoint_store_id = ' . $this->storeId . $where
                    . ' and g.sendout in (2,3)    order by g.order_id desc';
        }
        $result = $this->orderMod->querySqlPageData($sql);
        $data = $result['list'];
        //订单商品数据
        foreach ($data as $k => $v) {
            $v_where = "order_id= '{$v['order_sn']}'";
            $cond = array(
                'cond' => $v_where
            );
            $list = $this->orderGoodsMod->getData($cond);
            $data[$k]['goods_list'] = $list;
            //赠品
            $sql = "select * from " . DB_PREFIX . "gift_goods where id=" . $v['gift_id'];
            $res = $this->giftGoodMod->querySql($sql);
            $data[$k]['gift'] = $res;
            if ($data[$k]['sendout'] == 1) {
                $data[$k]['shippingMethod'] = $a['order_ziti'];
            }
            if ($data[$k]['sendout'] == 2) {
                $data[$k]['shippingMethod'] = $a['order_pssm'];
            }
            if ($data[$k]['sendout'] == 3) {
                $data[$k]['shippingMethod'] = $a['order_yjty'];
            }
        }
        $this->assign('data', $data);
        $this->assign('page_html', $result['ph']);
        $OrderStatus = array(
            "0" => $a['Canceled'],
            "10" => $a['Unpaid'],
            "20" => $a['payment'],
            "30" => $a['Shipped'],
            "40" => $a['Shipped_1'],
            "50" => $a['Receivedgoods'],
        );
        if (!empty($clickandview)) {
            $orderMod = &m('order');
            $sql=<<<SQL
                UPDATE bs_order SET clickandview = 2 WHERE  store_id = {$this->storeId} AND sendout in('2','3')
SQL;
            $orderMod->doEditSql($sql);
        }
        $this->assign('symbol', $this->symbol);
        $this->assign('status', $OrderStatus);
        $this->assign('auth', $auth);
        $this->assign('store', $this->getUseStore());
        $this->assign('lang_id', $this->lang_id);
        $this->display('orderList/index.html');
    }

    /**
     * 订单导出excel
     * @author wangshuo 
     * @date 2018-6-27
     */
    public function exportOrder() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $orderMod = &m('order');
        $con = ' 1 = 1 ';
        $sql = " select * from " . DB_PREFIX . "order where Appoint =2  and sendout>1 and source_id=0  and Appoint_store_id = " . $this->storeId . " order by order_id DESC ";
        $orderlist = $orderMod->querySql($sql);
        $userMod = &m('user');
        $totalMoney = 0;
        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=订单统计报表.xls");
        echo iconv('utf-8', 'gb2312', "订单编号") . "\t";
        echo iconv('utf-8', 'gb2312', "用户昵称") . "\t";
        echo iconv('utf-8', 'gb2312', "微信昵称") . "\t";
        echo iconv('utf-8', 'gb2312', "收货地址") . "\t";
        echo iconv('utf-8', 'gb2312', "手机号") . "\t";
        echo iconv('utf-8', 'gb2312', "订单状态") . "\t";
        echo iconv('utf-8', 'gb2312', "支付方式") . "\t";
        echo iconv('utf-8', 'gb2312', "支付金额") . "\t";
        echo iconv('utf-8', 'gb2312', "支付状态") . "\t";
        echo iconv('utf-8', 'gb2312', "添加时间") . "\t";
        echo "\n";
        $totalMoney = 0;
        foreach ($orderlist as $k => $v) {
            $sql = " select * from " . DB_PREFIX . "user where id='.{$v['buyer_id']}.' order by add_time DESC ";
            $user = $userMod->querySql($sql);
            if ($v['order_state'] == 0) {
                $orderState = '订单已关闭';
            } else if ($v['order_state'] == 10) {
                $orderState = '买家未付款';
            } else if ($v['order_state'] == 20) {
                $orderState = '买家已付款';
            } else if ($v['order_state'] == 30) {
                $orderState = '卖家已发货';
            } else if ($v['order_state'] == 40) {
                $orderState = '区域配送中';
            } else if ($v['order_state'] == 50) {
                $orderState = '买家已收货';
            }
            if ($v['order_state'] < 10) {
                $orderPayment = '未支付';
            } else if ($v['order_state'] == 10) {
                $orderPayment = '未支付';
            } else if ($v['order_state'] > 10) {
                $orderPayment = '已支付';
            }
            echo iconv('utf-8', 'gb2312', "'" . $v['order_sn']) . "\t";
            echo iconv('utf-8', 'gb2312', $v['buyer_name']) . "\t";
            echo iconv('utf-8', 'gb2312', $user['nickname']) . "\t";
            echo iconv('utf-8', 'gb2312', $v['buyer_address']) . "\t";
            echo iconv('utf-8', 'gb2312', $user['phone']) . "\t";
            echo iconv('utf-8', 'gb2312', $orderState) . "\t";
            echo iconv('utf-8', 'gb2312', $v['payment_code']) . "\t";
            echo iconv('utf-8', 'gb2312', $v['order_amount']) . "\t";
            echo iconv('utf-8', 'gb2312', $orderPayment) . "\t";
            echo iconv('utf-8', 'gb2312', date('Y-m-d', $v['add_time'])) . "\t";
            echo "\n";
        }
    }

    /**
     * 已指定订单详情页面
     * @author wangs
     * @date 2017/10/24
     */
    public function details() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $store_id = $this->storeId;
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $order_id = $_REQUEST['order_id']; //订单id
        $arr = array(
            "clickandview" => 2,
        );
        $datas = array(
            "table" => "order",
            'cond' => "order_id = '{$order_id}'",
            'set' => $arr,
        );
        $res = $this->orderMod->doUpdate($datas);
        // 1总代理 2经销商
        $auth = $this->auth;
//        if ($auth == 1) {
        $where = " where g.order_id = '{$order_id}' ";
//        } else {
//            $where = ' where g.order_id = ' . $order_id . ' and g.Appoint_store_id = ' . $store_id;
//        }
        //列表页数据
        $sql = 'select *, g.add_time from '
                . DB_PREFIX . 'order as g left join '
                . DB_PREFIX . 'user_address a' . ' on a.user_id = g.buyer_id' . $where;
        $info = $this->orderMod->querySql($sql);
        foreach ($info as $k => $v) {
            $v_where = "order_id= '{$v['order_sn']}'";
            $cond = array(
                'cond' => $v_where
            );
            $list = $this->orderGoodsMod->getData($cond);
            $info[$k]['goods_list'] = $list;
        }
        $orderInfo = $info[0];
        //获取配送地址等数据
        if ($orderInfo['sendout'] == 2) {
            $address = $this->orderMod->getOrderAddress($orderInfo['order_sn'], $this->storeId);
            $orderInfo['address'] = $address;
        }
        $this->assign('info', $orderInfo);
        //以order_sn 查询退款商品记录
        $refund_sql = "select * from " . DB_PREFIX . "refund_return as r"
                . " where r.order_sn= '{$info[0]['order_sn']}'"
                . " and r.order_id = '{$info[0]['order_id']}'";

        $sql = "select count(*)  as num from " . DB_PREFIX . "order_goods where order_id = '{$info[0]['order_sn']}'  and refund_state != 2 ";
        $order_goods_num = $this->orderMod->querySql($sql);  // 退款商品列表
        $this->assign("order_goods_num", $order_goods_num[0]['num']);
        $refund_goods = $this->orderMod->querySql($refund_sql);  // 退款商品列表
        $this->assign("refund_goods", $refund_goods);
        $OrderStatus = array(
            "0" => $a['Canceled'],
            "10" => $a['Unpaid'],
            "20" => $a['payment'],
            "30" => $a['Shipped'],
            "40" => $a['Shipped_1'],
            "50" => $a['Receivedgoods'],
        );
        $user_sql = 'select username from ' . DB_PREFIX . 'user where id = ' . $info[0]['buyer_id'];
        $username = $this->orderMod->querySql($user_sql);
        $this->assign('p', $p);
        $this->assign('username', $username[0]['username']);
        $this->assign('p', $p);
        $this->assign('status', $OrderStatus);
        $this->assign('symbol', $this->symbol);
        $this->assign('lang_id', $this->lang_id);
        $this->assign('store_id', $store_id);
        $this->assign('auth', $auth);
        $this->assign('store', $this->getUseStore());
        $this->display('orderList/details_edit.html');
    }

    /**
     * 确认发货物流
     * @author wangs
     * @date 2017/10/24
     */
    public function add() {
        $sql = "select * from " . DB_PREFIX . "corplist where is_use =1";
        $info = $this->corplistMod->querySql($sql);
        $this->assign("info", $info);
        $this->assign("lang_id", $this->lang_id);
        $this->assign("order_id", $_REQUEST['order_id']);
        $this->assign("order_sn", $_REQUEST['order_sn']);
        $this->display('orderList/add.html');
    }

    /**
     * 确认配送
     * @author wangs
     * @date 2017/10/24
     */
    public function doPsong() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $_data = explode("_", $_REQUEST['data']);
        $order_sn = $_data[0];
        $state = $_data[1];
        $arr = array(
            'order_state' => 30,
        );
        $data = array(
            "table" => "order",
            'cond' => " order_sn = '{$order_sn}' " ,
            'set' => $arr,
        );
        $this->orderMod->update_ship_time($this->storeId,$order_sn);
        $res = $this->orderMod->doUpdate($data);
        $arrs = array(
            'order_state' => 30,
        );
        $datas = array(
            "table" => "order_goods",
            'cond' => "order_id = '{$order_sn}' "  . ' and refund_state!=2',
            'set' => $arrs,
        );
        $res = $this->orderGoodsMod->doUpdate($datas);
        if ($res) {
            $info['url'] = "store.php?app=order&act=details&lang_id={$lang_id}";
            $this->setData($info, '1', $message = "{$a['Delivergoods_ok']}");
        } else {
            $this->setData(array(), '0', $message = "{$a['Delivergoods_no']}");
        }
    }

    /**
     * 确认发货物流
     * @author wangs
     * @date 2017/10/24
     */
    public function doAdd() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $lang_id = !empty($_REQUEST['lang_id']) ? (int) ($_REQUEST['lang_id']) : '0';
        $id = !empty($_REQUEST['order_id']) ? htmlspecialchars(($_REQUEST['order_id'])) : '';
        $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars(($_REQUEST['order_sn'])) : '';
        $logistics = !empty($_REQUEST['logistics']) ? htmlspecialchars(($_REQUEST['logistics'])) : '';
        $odd_Numbers = trim($_REQUEST['odd_Numbers']);
        if (empty($logistics)) {
            $this->setData(array(), '0', $a['logistics_name']);
        }
        if (empty($odd_Numbers)) {
            $this->setData(array(), '0', $a['logistics_Number']);
        }
        if (!preg_match('/^[0-9a-zA-Z]+$/', $odd_Numbers)) {
            $this->setData(array(), $status = '0', $a['logistics_Number_NO']); //格式
        }
        if (empty($odd_Numbers)) {
            $this->setData(array(), '0', '');
        }
        $info = $this->corplistMod->getOne(array("cond" => "id=" . $logistics));
        $arr = array(
            'orplist_id' => $logistics,
            'orplist_name' => $info['name'],
            'shipping_code' => $odd_Numbers,
            'order_state' => 30,
        );
        $data = array(
            "table" => "order",
            'cond' => "order_id ='{$id}'" . " and order_sn = '{$order_sn}'",
            'set' => $arr,
        );
        $res = $this->orderMod->doUpdate($data);
        $arrs = array(
            'order_state' => 30,
        );
        $datas = array(
            "table" => "order_goods",
            'cond' =>"order_id = '{$order_sn}'"  . ' and refund_state!=2',
            'set' => $arrs,
        );
        $res = $this->orderGoodsMod->doUpdate($datas);
        if ($res) {
            $info['url'] = "store.php?app=order&act=details&lang_id={$lang_id}";
            $this->setData($info, '1', $message = "{$a['Delivergoods_ok']}");
        } else {
            $this->setData(array(), '0', $message = "{$a['Delivergoods_no']}");
        }
    }

    /**
     * 订单ajax判断
     * @author wangs
     * @date 2017-10-26
     */
    public function editOrderState() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $_data = explode("_", $_REQUEST['data']);
        $id = $_data[0];
        $state = $_data[1];
        $ops = $_data[2];
        $store_Id = $_data[3];
        if ($store_Id) {
            $sql = " select `fx_phone` from " . DB_PREFIX . "order where `order_sn`='{$id}'";
            $ifo = $this->orderGoodsMod->querySql($sql);
            switch ($state) {
                case 10:
                    if ($ops == "confirm") {
                        $set = array(
                            "order_state" => 20,
                            "Appoint" => 2,
                            "Appoint_store_id" => $store_Id,
                        );
                    }
                    break;
            }
            $data = array(
                "table" => "order",
                'cond' => "order_sn = '{$id}'",
                'set' => $set,
            );
            if ($ops == "confirm") {
                $rand = $this->buildNo(1);
                $orderNo = date('YmdHis') . $rand[0];
                $data['set']['payment_code'] = '线下打款';
                $data['set']['pay_sn'] = $orderNo;
                $data['set']['payment_time'] = time();
                $this->orderMod->update_pay_time($store_Id,$id,$orderNo,4);
            }
            //更新原来老表数据
          $res = $this->orderMod->doUpdate($data,1);


            $arr = array(
                "order_state" => 20,
                'shipping_store_id' => $store_Id,
            );
            $datas = array(
                "table" => "order_goods",
                'cond' => "order_id ='{$id}' " ,
                'set' => $arr,
            );
            $res = $this->orderGoodsMod->doUpdate($datas);

            //  更新库存
            if ($ops == "confirm") {
                //  更新库存
                $this->updateStock($id);
            }
            if ($res) {
                $this->setData(array(), $status = 1, $message = "{$a['operation_ok']}");
            } else {
                $this->setData(array(), $status = 0, $message = "{$a['operation_no']}");
            }
        } else {
            $rand = $this->buildNo(1);
            $orderNo = date('YmdHis') . $rand[0];
            $sql = " select `fx_phone`,store_id from " . DB_PREFIX . "order where `order_sn`='{$id}'";
            $ifo = $this->orderGoodsMod->querySql($sql);
            $storeId=$ifo[0]['store_id'];
            switch ($state) {
                case 10:
                    if ($ops == "confirm") {
                        $set = array(
                            "order_state" => 20,
                        );
                        $this->orderMod->update_pay_time($storeId,$id,$orderNo,4);
                    }
                    break;
                case 30:
                    if ($ops == "Distribution") {
                        $set = array(
                            "order_state" => 40,
                        );
                        $this->orderMod->update_delivery_time($storeId,$id);
                    }
                    break;
            }
            $data = array(
                "table" => "order",
                'cond' => "order_sn = '{$id}'",
                'set' => $set,
            );
            if ($ops == "confirm") {
                $data['set']['payment_code'] = '线下打款';
                $data['set']['pay_sn'] = $orderNo;
                $data['set']['payment_time'] = time();
            }
            $res = $this->orderMod->doUpdate($data);
            $datas = array(
                "table" => "order_goods",
                'cond' => "order_id ='{$id}' ",
                'set' => $set,
            );
            $res = $this->orderGoodsMod->doUpdate($datas);

            //  更新库存
            if ($ops == "confirm") {
                $this->updateStock($id);
            }
            if ($res) {
                $this->setData(array(), $status = 1, $message = "{$a['operation_ok']}");
            } else {
                $this->setData(array(), $status = 0, $message = "{$a['operation_no']}");
            }
        }
    }

    /**
     * 区域配送安装
     * @author wangs
     * @date 2017-10-26
     */
    public function installState() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $_data = explode("_", $_REQUEST['data']);
        $id = $_data[0];
        $state = $_data[1];
        $ops = $_data[2];
        switch ($state) {
            case 10:
                if ($ops == "install") {
                    $set = array(
                        "region_install" => 20,
                        "install_time" => time(),
                    );
                }
                break;
        }
        $data = array(
            "table" => "order",
            'cond' => "order_sn = '{$id}'",
            'set' => $set,
        );
        $res = $this->orderMod->doUpdate($data);
        if ($res) {
            $this->setData(array(), $status = 1, $message = "{$a['operation_ok']}");
        } else {
            $this->setData(array(), $status = 0, $message = "{$a['operation_no']}");
        }
    }

    /**
     * 生成不重复的四位随机数
     * @author wanyan
     * @date 2017-10-23
     */
    public function buildNo($limit) {
        $begin = pow(10, 3);
        $end = (pow(10, 4) - 1);
        $rand_array = range($begin, $end);
        shuffle($rand_array); //调用现成的数组随机排列函数
        return array_slice($rand_array, 0, $limit); //截取前$limit个
    }

    /**
     * 订单退款ajax判断
     * @author wangs
     * @date 2017-10-28
     */
    public function editRefund() {
        $_data = explode("_", $_REQUEST['data']);
        $order_sn = $_data[0];
        $state = $_data[1];
        $order_id = $_data[2];
        $rec_id = $_data[3];
        $ops = $_data[4];

        //退款表
        $sql = "select *  from " . DB_PREFIX . "refund_return where rec_id = {$rec_id}";
        $order_return = $this->orderMod->querySql($sql);
        $refund_amounts = $order_return[0]['refund_amounts'];
        //订单表
        $sql = "select *  from " . DB_PREFIX . "order where order_sn = '{$order_sn}'";
        $order = $this->orderMod->querySql($sql);
        $total_refund = $order[0]['refund_amount'];
        if ($total_refund < $refund_amounts) {
            $this->setData(array(), 0, "参数错误");
        }

        $sql = "SELECT sg.goods_id,sg.spec_key,sg.goods_num FROM ".DB_PREFIX."order as r LEFT JOIN ".DB_PREFIX."order_goods as sg ON  r.order_sn = sg.order_id WHERE sg.order_id = '{$order_sn}'";
        $orderRes = $this->areaGoodMod->querySql($sql);


        foreach ($orderRes as $k =>$v) {
            if (!empty($v['spec_key'])) {
                $query_id = "select `id` from " . DB_PREFIX . "store_goods_spec_price where `store_goods_id` = '{$v['goods_id']}' and `key` = '{$v['spec_key']}' ";
                $res_query = $this->storeGoodItemPriceMod->querySql($query_id);
                $specInfoSql = "select goods_storage from  " . DB_PREFIX . "store_goods_spec_price WHERE `store_goods_id` = '{$v['goods_id']}' and `key` = '{$v['spec_key']}'";
                $specInfo = $this->areaGoodMod->querySql($specInfoSql);
                $condition = array(
                    'goods_storage' => $specInfo[0]['goods_storage'] + $v['goods_num']
                );
                $res = $this->storeGoodItemPriceMod->doEdit($res_query[0]['id'], $condition);
                if ($res) {
                    $infoSql = "select goods_storage from  " . DB_PREFIX . "store_goods WHERE `id` = '{$v['goods_id']}'";
                    $Info = $this->areaGoodMod->querySql($infoSql);
                    $cond = array(
                        'goods_storage' => $Info[0]['goods_storage'] + $v['goods_num']
                    );
                    $this->areaGoodMod->doEdit($v['goods_id'], $cond);
                }

            } else {
                $specInfoSql = "select goods_storage from  " . DB_PREFIX . "store_goods WHERE `id` = '{$v['goods_id']}'";
                $specInfo = $this->areaGoodMod->querySql($specInfoSql);
                $condition = array(
                    'goods_storage' => $specInfo[0]['goods_storage'] +$v['goods_num']
                );
                $this->areaGoodMod->doEdit($v['goods_id'],$condition);
            }
        }
        //$state  zhege 
        switch ($state) {
            case 1:
                if ($ops == "Agree") {
                    $set = array(
                        "refund_state" => 2,
                    );
                }
                if ($ops == "Disagree") {
                    $set = array(
                        "refund_state" => 3,
                    );
                }
                break;
        }
           if($ops == "Agree"){
            $amount_sql = "select order_sn,c_money,add_user,source from " . DB_PREFIX . "amount_log where order_sn = '{$order_sn}' and type=2";
            $amount_sn = $this->orderMod->querySql($amount_sql);
                if($order_sn==$amount_sn[0]['order_sn']){
                    $userData=$this->userMod->getOne(array('cond'=>"`id` = '{$amount_sn[0]['add_user']}' and mark=1",'fields'=>'amount'));
                    $amount=$userData['amount']+$amount_sn[0]['c_money'];
                    //修改记录状态
                     $data_edit=array(
                         "amount" => $amount,
                     );
                    $user_edit = array(
                        "table" => "user",
                        'cond' => 'id = ' . $amount_sn[0]['add_user'],
                        'set' => $data_edit,
                    );
                    $this->userMod->doUpdate($user_edit);
                    $amountLogData=array(
                        'c_money'=>$amount_sn[0]['c_money'],
                        'old_money'=>$userData['amount'],
                        'new_money'=>$userData['amount']+$amount_sn[0]['c_money'],
                        'source'=>$amount_sn[0]['source'],
                        'add_user'=>$amount_sn[0]['add_user'],
                        'add_time'=>time(),
                        'mark'=>1,
                        'order_sn'=>$amount_sn[0]['order_sn'],
                        'status'=>4,
                        'type'=>1,
                        'class'=>4
                    );
                    $this->createAmountlog($amountLogData);
                }
        }
        $datas = array(
            "table" => "order_goods",
            'cond' => "order_id = '{$order_sn}'"  . ' and refund_state = 1',
            'set' => $set,
        );
        if ($rec_id) {
            $datas['cond'] .= ' and rec_id = ' . $rec_id;
            if ($ops == "Agree") {
                $datas['set']['order_state'] = 0;
            }
        }
//        print_r($datas);exit;
        $this->orderGoodsMod->doUpdate($datas);
        if ($ops == "Disagree") {
            $final_refund = $total_refund - $refund_amounts;
            $set["refund_amount"] = $final_refund;
        }
        $data = array(
            "table" => "order",
            'cond' => "order_sn = '{$order_sn}' and order_id = '{$order_id}' ",
            'set' => $set,
        );
        $sql = "select count(*)  as num from " . DB_PREFIX . "order_goods where order_id = '{$order_sn}'  and refund_state != 2 ";
        $order_goods = $this->orderMod->querySql($sql);  // 退款商品列表
        $unrefund_num = $order_goods[0]['num'];
        if (!$unrefund_num) {
            $data['set']['refund_state'] = 2;
            $data['set']['order_state'] = 0;
        } else {
            $data['set']['refund_state'] = 1;
        }
        $res = $this->orderMod->doUpdate($data);
        $sql_r = "select count(*)  as num from " . DB_PREFIX . "order_goods where order_id = '{$order_sn}'  and refund_state = 2 ";
        $sql_o = "select count(*)  as num from " . DB_PREFIX . "order_goods where order_id = '{$order_sn}'";
        $order_goods_r = $this->orderMod->querySql($sql_r);
        $order_goods_o = $this->orderMod->querySql($sql_o);
        if ($order_goods_r) {
            if ($order_goods_r[0]['num'] == $order_goods_o['num']) {
                $this->returnPoint($order_sn);
            }
        }
        if ($res) {
            $this->setData(array(), 1, "退款成功");
        } else {
            $this->setData(array(), 0, "退款失败");
        }
    }
    //生成充值记录
    public  function  createAmountlog($data){
        $amountLogId=$this->amountLogMod->doInsert($data);
        return $amountLogId;
    }
    //分销订单相关修改
    /**
     * 根据分销码佣金分配
     * @author wanyan
     * @date 2017-11-21
     */
    public function distrCom($order_id) {
        $fxMainOrder = $this->orderMod->getOne(array('cond' => "`order_sn` = '{$order_id}'", 'fields' => '`order_id`,`order_sn`,`store_id`,buyer_id,buyer_name,buyer_email,buyer_address,store_id,order_amount,discount,fx_phone,fx_discount_rate'));
        $fxOrderMod = &m('fxOrder');
        $fxOrderData = $fxOrderMod->getOne(array('cond'=>'order_sn="'.$order_id.'"','fields'=>'fx_discount'));
        $fxMainOrder['fx_discount'] = $fxOrderData['fx_discount'];
        $fxMainOrder['phone'] = $this->getUserPhone($fxMainOrder['buyer_id']);
        $res = $this->getRuler($fxMainOrder);
        return $res;
    }

    /**
     * 获取用户的电话号码
     * @author wanyan
     * @date 2017-11-21
     */
    public function getUserPhone($user_id) {
        $userAddress = &m('userAddress');
        $rs = $userAddress->getOne(array('cond' => "`user_id` = '{$user_id}'", 'fields' => "phone"));
        return $rs['phone'];
    }

    public function getRuler($mainInfo) {
        $info = $this->fxUserMod->getOne(array('cond' => 'fx_code = ' . $mainInfo['fx_phone']));
        if ($info['level'] == 3) { // 如果三级分销商的分销码
            $secondUser = $this->fxUserMod->getRow($info['parent_id']);
            $secondUserId = $secondUser['user_id'];
            $firstUser = $this->fxUserMod->getRow($secondUser['parent_id']);
            $firstUserId = $firstUser['user_id'];
            $fxRule = $this->fxRuleMod->getRow($firstUser['rule_id']);
            $lev1Revenue = ($fxRule['lev1_prop'] * 0.01 * $mainInfo['order_amount']); // 一级佣金
            $lev2Revenue = ($fxRule['lev2_prop'] * 0.01 * $mainInfo['order_amount']); // 二级佣金
            $lev3Revenue = (($fxRule['lev3_prop'] - $mainInfo['fx_discount']) * 0.01 * $mainInfo['order_amount']); // 三级佣金
            $insert_data_main['lev1_revenue'] = $lev1Revenue;
            $insert_data_main['lev2_revenue'] = $lev2Revenue;
            $insert_data_main['lev3_revenue'] = $lev3Revenue;
            $insert_data_main['lev2_user_id'] = $secondUserId; // 二级分销商ID
            $insert_data_main['lev2_user_name'] = $this->getDisUser($secondUserId); // 二级分销商姓名
            $insert_data_main['lev3_user_id'] = $info['user_id']; // 三级分销商ID
            $insert_data_main['lev3_user_name'] = $this->getDisUser($info['user_id']); //  三级分销商姓名
            $this->fxUserMod->doEdit($info['id'], array('monery' => $lev3Revenue+$info['monery']));  //三级分销人佣金
            $this->fxUserMod->doEdit($secondUser['id'], array('monery' => $lev2Revenue+$secondUser['monery']));  //二级分销人佣金
            $this->fxUserMod->doEdit($firstUser['id'], array('monery' => $lev1Revenue+$firstUser['monery']));  //一级分销人佣金
        } elseif ($info['level'] == 2) { // 如果二级分销商的分销码
            $firstUser = $this->fxUserMod->getRow($info['parent_id']);
            $firstUserId = $firstUser['user_id'];  // 一级分销商ID
            $fxRule = $this->fxRuleMod->getRow($firstUser['rule_id']);
            $lev1Revenue = ($fxRule['lev1_prop'] * 0.01 * $mainInfo['order_amount']); // 一级佣金
            $lev2Revenue = ($fxRule['lev2_prop'] * 0.01 * $mainInfo['order_amount']); // 二级佣金
            $insert_data_main['lev1_revenue'] = $lev1Revenue;
            $insert_data_main['lev2_revenue'] = $lev2Revenue;
            $insert_data_main['lev3_revenue'] = 0.00; // 三级佣金
            $insert_data_main['lev2_user_id'] = $info['user_id']; // 二级分销商ID
            $insert_data_main['lev2_user_name'] = $this->getDisUser($info[0]['user_id']); // 二级分销商姓名
            $insert_data_main['lev3_user_id'] = 0; // 三级分销商ID
            $insert_data_main['lev3_user_name'] = ''; // 三级分销商姓名
            $this->fxUserMod->doEdit($info['id'], array('monery' => $lev2Revenue+$info['monery']));  //二级分销人佣金
            $this->fxUserMod->doEdit($firstUser['id'], array('monery' => $lev1Revenue+$firstUser['monery']));  //一级分销人佣金
        } elseif ($info['level'] == 1) {
            $firstUser = $this->fxUserMod->getRow($info['user_id']);
            $firstUserId = $firstUser['user_id'];  // 一级分销商ID
            $fxRule = $this->fxRuleMod->getRow($firstUser['rule_id']);
            $lev1Revenue = ($fxRule['lev1_prop'] * 0.01 * $mainInfo['order_amount']); // 一级佣金
            $insert_data_main['lev1_revenue'] = $lev1Revenue;
            $insert_data_main['lev2_revenue'] = 0.00; // 二级佣金
            $insert_data_main['lev3_revenue'] = 0.00; // 三级佣金
            $insert_data_main['lev2_user_id'] = 0; // 二级分销商ID
            $insert_data_main['lev2_user_name'] = ''; // 二级分销商姓名
            $insert_data_main['lev3_user_id'] = 0; // 三级分销商ID
            $insert_data_main['lev3_user_name'] = ''; // 三级分销商姓名
            $this->fxUserMod->doEdit($firstUser['id'], array('monery' => $lev1Revenue+$firstUser['monery']));  //一级分销人佣金
        }
        $store_cate = $this->storeInfo;
        $insert_data = array(
            'user_id' => $mainInfo['buyer_id'], // 购买人用户ID
            'user_name' => $mainInfo['buyer_name'],
            'phone' => $mainInfo['phone'],
            'fx_rule_id' => $fxRule['id'],
            'lev1_prop' => $fxRule['lev1_prop'],
            'lev2_prop' => $fxRule['lev2_prop'],
            'lev3_prop' => $fxRule['lev3_prop'],
            'lev1_user_id' => $firstUserId,
            'lev1_user_name' => $this->getDisUser($firstUserId),
//          'lev1_revenue' => ($fxRule['lev1_prop']*0.01*$goodInfo['goods_pay_price']), // 一级佣金
//          'lev2_user_id' => $secondUserId,
//          'lev2_user_name' => $this->getDisUser($secondUserId),
//          'lev2_revenue' => ($fxRule['lev2_prop']*0.01*$goodInfo['goods_pay_price']),// 二级佣金
//          'lev3_user_id' => $info[0]['user_id'],
//          'lev3_user_name' => $this->getDisUser($info[0]['user_id']),
            // 'lev3_revenue' => ($fxRule['lev3_prop']*0.01*$goodInfo['goods_pay_price']),// 三级佣金
            'order_id' => $mainInfo['order_id'],
            'order_sn' => $mainInfo['order_sn'],
            'order_money' => $mainInfo['order_amount'],
            'store_cate' => $store_cate['store_cate_id'],
            'store_id' => $mainInfo['store_id'],
            'discount' => $mainInfo['discount'],
            'discount_rate' => $mainInfo['fx_discount_rate'],
            'add_time' => time()
        );
        $insert_data_total = array_merge($insert_data, $insert_data_main);
        $rs = $this->fxRevenueLogMod->doInsert($insert_data_total);
        return $rs;
    }

    /**
     * 获取分润规则
     * @author wanyan
     * @date 2017-11-21
     */
    public function getUserTreeId($parent_id) {
        $rs = $this->fxUserTreeMod->getOne(array('cond' => "`id` = '{$parent_id}'", 'fields' => "user_id"));
        return $rs['user_id'];
    }

    /**
     * 获取分润规则
     * @author wanyan
     * @date 2017-11-21
     */
    public function getRuleDetail($user_id) {
        $sql = "SELECT  fr.id,fr.lev1_prop,fr.lev2_prop,fr.lev3_prop,fr.store_cate,fr.store_id
        FROM `bs_fx_user_rule` as fur
        LEFT JOIN `bs_fx_usertree` fut ON  fur.user_id = fut.user_id
        LEFT JOIN `bs_fx_rule` as fr ON fur.rule_id = fr.id WHERE fur.user_id =" . $user_id;
        $rs = $this->fxRuleMod->querySql($sql);
        return $rs[0];
    }

    /**
     * 获取分销商的姓名
     * @author wanyan
     * @date 2017-11-21
     */
    public function getDisUser($user_id) {
        $rs = $this->fxUserMod->getOne(array('cond' => "`user_id` = '{$user_id}'", 'fields' => "real_name"));
        return $rs['real_name'];
    }

    /*
     * 取消订单退还积分
     * @author lee
     * @date 2018-6-22 15:03:17
     */

    public function returnPoint($id) {
        $userMod = &m('user');
        $pointLogMod = &m("pointLog");
        $point_log = $pointLogMod->getOne(array("cond" => "order_sn= '{$id}'"));
        //更新用户的积分值
        if ($point_log) {
            $user_id = $this->userId;
            $user_info = $userMod->getOne(array("cond" => "id=" . $user_id));
            $user_point = $user_info['point'] + $point_log['expend'];
            $res = $userMod->doEdit($user_id, array("point" => $user_point));
            //积分日志
            if ($res) {
                $logMessage = "取消订单：" . $id . " 获取：" . $point_log['expend'] . "分";
                $this->addPointLog($this->userName, $logMessage, $user_id, $point_log['expend'], '-');
            }
        }
    }

    //生成日志
    public function addPointLog($username, $note, $userid, $deposit, $expend, $order_sn = null) {
        $logData = array(
            'operator' => '--',
            'username' => $username,
            'add_time' => time(),
            'deposit' => $deposit,
            'expend' => $expend,
            'note' => $note,
            'userid' => $userid
        );
        if ($order_sn) {
            $logData['order_sn'] = $order_sn;
        }
        $pointLogMod = &m("pointLog");
        $pointLogMod->doInsert($logData);
    }

    /**
     * 订单指定三级分销人员
     *
     * @author zhangkx
     * @date 2018/11/15
     */
    public function appoint_en()
    {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $orderSn = $_REQUEST['order_sn'];
        $action = $_REQUEST['action'];
        $application = $_REQUEST['application'];
        $order = $this->orderMod->getOne(array('cond'=>'order_sn="'.$orderSn.'"'));
        if (IS_POST) {
            $id = $_REQUEST['fx_level_3'] ? $_REQUEST['fx_level_3'] : 0;
            $orderId = $_REQUEST['order_id'] ? $_REQUEST['order_id'] : 0;
            $application = $_REQUEST['application'] ? $_REQUEST['application'] : '';
            $action = $_REQUEST['action'] ? $_REQUEST['action'] : '';
            if (empty($id)) {
                $this->setData(array(), $status = 0, '请选择三级分销人员');
            }
            if (empty($orderId)) {
                $this->setData(array(), $status = 0, '参数错误');
            }
            //分销人员信息
            $userData = $this->fxUserMod->getRow($id);
            //订单信息
            $orderData = $this->orderMod->getOne(array('cond'=>"order_id= '{$orderId}'"));
            $rule_id = $userData['rule_id'];
            //插入分销订单表
            $fxOrderMod = &m('fxOrder');
            //分销优惠金额及比例
            $fxMoney = $fxOrderMod->calFxMoney($orderData['order_amount'], $id);
            $fxOrderData = array(
                'order_id' => $orderId,
                'order_sn' => $orderData['order_sn'],
                'source' => 5,
                'user_id' => $orderData['buyer_id'],
                'fx_user_id' => $id,
                'rule_id' => $rule_id,
                'store_cate' => $this->storecate,
                'store_id' => $orderData['store_id'],
                'add_time' => time(),
                'add_user' => $this->storeUserId,
                'pay_money' => $orderData['order_amount'],
                'fx_money' => $fxMoney['money'],
                'fx_discount' => $userData['discount'],
                'fx_commission_percent' => $fxMoney['discount'],
                'is_on' => 0
            );
            $fxResult = $fxOrderMod->doInsert($fxOrderData);
            if (!$fxResult) {
                $this->setData(array(), $status = 0, '分销订单表插入失败');
            }
            //会员绑定分销人员
            $fxUserAccountMod = &m('fxUserAccount');
            if (!empty($orderData['buyer_id'])) {
                 $fxUserAccountMod->addFxUser($id, $orderData['buyer_id'], 4);
            }
            //更新订单表fx_phone字段
            $sql = "update bs_order set fx_phone = '{$userData['fx_code']}' where order_id='{$orderId}'";
            $this->orderMod->doEditSql($sql);
            //更新新订单详情表fx_user_id字段
            $newSql = "select a.id, a.order_state from ".DB_PREFIX."order_{$this->storeId} as a 
                    left join ".DB_PREFIX."order_details_{$this->storeId} as b on a.id=b.order_id where a.order_sn = '{$orderData['order_sn']}'";
            $newOrder = $this->orderMod->querySql($newSql);
            $sql1 = "update bs_order_details_{$this->storeId} set fx_user_id = '{$id}' where order_id='{$newOrder[0]['id']}'";
            $this->orderMod->doEditSql($sql1);
            if ($newOrder[0]['order_state'] == 50) {//已收货
                $this->fxUserMod->getAccount($orderData['order_sn']);
            }
            $info['url'] = "store.php?app={$application}&act={$action}&lang_id={$this->lang_id}";
            $this->setData($info, $status = 1, '指定成功');
        }
        //获取国家
         $this->assign('countrys', $this->getACountry($this->defaulLang));
        
        
//        $level1 = $this->fxUserMod->getData(array('cond'=>'mark = 1 and level = 1 and is_check = 2 and status = 1 and store_id = ' . $this->storeId));
//        $level1 = $this->fxUserMod->getData(array('cond'=>'mark = 1 and level = 1 and is_check = 2 and status = 1'));
        $this->assign('orderId', $order['order_id']);
//        $this->assign('level1', $level1);
        $this->assign('application', $application);
        $this->assign('action', $action);
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
        $this->assign('lang_id', $this->lang_id);
        $this->display('orderList/appoint_en.html');
    }
    /**
     * 店铺国家
     * @author wangshuo
     * @date 2018-12-18
     */
    public function getACountry($lang)
    {
        $sql = "select SC.`id`,SCL.`cate_name`  from  " . DB_PREFIX . "store_cate AS SC LEFT JOIN " . DB_PREFIX . "store_cate_lang  
        AS SCL ON SC.id = SCL.cate_id where SCL.lang_id = " . $lang . " and SC.is_open=1";
        $rs = $this->storeCateMod->querySql($sql);
        return $rs;
    }
    /**
     * ajax获取店铺
     *
     * @author wangshuo
     * @date 2018/11/16
     */
    public function ajaxCateld() {
        $parentId = $_REQUEST['parent_id'];
        $sql = "select SC.`id`,SCL.`store_name`  from  " . DB_PREFIX . "store AS SC LEFT JOIN " . DB_PREFIX . "store_lang  
        AS SCL ON SC.id = SCL.store_id where SCL.lang_id = " . $this->defaulLang . " and SC.store_cate_id= ".$parentId;
        $rs = $this->storeCateMod->querySql($sql);
        echo json_encode($rs);
    }
    /**
     * ajax获取分销人员
     *
     * @author zhangkx
     * @date 2018/11/16
     */
    public function ajaxGetChild() {
        $parentId = $_REQUEST['parent_id'];
        $store_id = $_REQUEST['store_id'];
        $data = $this->fxUserMod->getData(array('cond' => 'mark = 1 and is_check = 2 and status = 1 and parent_id = ' . $parentId));
        echo json_encode($data);
    }
/**
     * 鏈粯娆惧湴鍧�鏇存敼
     * @author wangshuo
     * @date 2018-11-27
     */
    public function editdizhi() {
        $lang_id = !empty($_REQUEST['lang_id']) ? (int) ($_REQUEST['lang_id']) : '0';
        $user_name = !empty($_REQUEST['user_name']) ? htmlspecialchars($_REQUEST['user_name']) : '';
        $user_addr = !empty($_REQUEST['user_addr']) ? htmlspecialchars($_REQUEST['user_addr']) : '';
        $user_phone = !empty($_REQUEST['user_phone']) ? htmlspecialchars($_REQUEST['user_phone']) : '';
        $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars($_REQUEST['order_sn']) : '';
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $arrs = array(
            'buyer_name' => $user_name,
            'buyer_address' => $user_addr,
            'buyer_phone' => $user_phone,
        );
        $datas = array(
            "table" => "order",
            'cond' => "order_sn='{$order_sn}'",
            'set' => $arrs,
        );
        $this->orderMod->doUpdate($datas,TRUE);
    }


    //更新库存
        public function updateStock($id){
            $sql = "SELECT sg.goods_id,sg.spec_key,sg.goods_num,sg.deduction,sg.good_id FROM " .
                DB_PREFIX . "order as r LEFT JOIN " .
                DB_PREFIX . "order_goods as sg ON  r.order_sn = sg.order_id WHERE sg.order_id = '{$id}'";
            $orderRes = $this->orderGoodsMod->querySql($sql);
            foreach ($orderRes as $k =>$v) {
                if (!empty($v['spec_key'])) {
                    if($v['deduction']==1){
                        $query_id = "select `id` from " . DB_PREFIX . "store_goods_spec_price where `goods_id` = '{$v['good_id']}' and `key` = '{$v['spec_key']}' ";
                        $res_query = $this->storeGoodItemPriceMod->querySql($query_id);
                        $specInfoSql = "select goods_storage from  " . DB_PREFIX . "store_goods_spec_price WHERE `goods_id` = '{$v['good_id']}' and `key` = '{$v['spec_key']}'";
                        $specInfo = $this->areaGoodMod->querySql($specInfoSql);
                        foreach($res_query as $key=>$val){
                            $goodStorage=$specInfo[0]['goods_storage'] - $v['goods_num'];
                            if($goodStorage<=0){
                                $goodStorage=0;
                            }
                            $condition = array(
                                'goods_storage' => $goodStorage
                            );
                            $res = $this->storeGoodItemPriceMod->doEdit($val['id'], $condition);
                        }
                        if ($res) {
                            $infoSql = "select goods_storage,id from  " . DB_PREFIX . "store_goods WHERE `goods_id` = '{$v['good_id']}'";
                            $Info = $this->areaGoodMod->querySql($infoSql);
                            $goodsStorage=$Info[0]['goods_storage'] - $v['goods_num'];
                            if($goodsStorage<=0){
                                $goodsStorage=0;
                            }
                            $cond = array(
                                'goods_storage' => $goodsStorage
                            );
                            foreach($Info as $key1=>$val1 ){
                                $this->areaGoodMod->doEdit($val1['id'], $cond);
                            }
                        }
                        $Sql = "select goods_storage from  " . DB_PREFIX . "goods_spec_price WHERE `goods_id` = '{$v['good_id']}' and `key` = '{$v['spec_key']}'";

                        $goodsSpec = $this->areaGoodMod->querySql($Sql);
                        $conditionalStorage=$goodsSpec[0]['goods_storage']-$v['goods_num'];
                        if($conditionalStorage<=0){
                            $conditionalStorage=0;
                        }
                        $conditional=array(
                            'goods_storage'=>$conditionalStorage
                        );
                        $goodsSpecSql="update ".DB_PREFIX."goods_spec_price set goods_storage = ".$conditional['goods_storage']." where goods_id=".$v['good_id']." and `key` ='{$v['spec_key']}'" ;
                        $result=$this->goodsSpecPriceMod->doEditSql($goodsSpecSql);
                        if($result){
                            $goodSql="select goods_storage from  " . DB_PREFIX . "goods WHERE `goods_id` = '{$v['good_id']}'";
                            $goodInfo = $this->areaGoodMod->querySql($goodSql);
                            $goodCondStorage=$goodInfo[0]['goods_storage'] - $v['goods_num'];
                            if($goodCondStorage<=0){
                                $goodCondStorage=0;
                            }
                            $goodCond = array(
                                'goods_storage' => $goodCondStorage
                            );
                            $this->goodsMod->doEdit($v['good_id'],$goodCond);
                        }
                    }else{
                        $query_id = "select `id` from " . DB_PREFIX . "store_goods_spec_price where `store_goods_id` = '{$v['goods_id']}' and `key` = '{$v['spec_key']}' ";
                        $res_query = $this->storeGoodItemPriceMod->querySql($query_id);
                        $specInfoSql = "select goods_storage from  " . DB_PREFIX . "store_goods_spec_price WHERE `store_goods_id` = '{$v['goods_id']}' and `key` = '{$v['spec_key']}'";
                        $specInfo = $this->areaGoodMod->querySql($specInfoSql);
                        $conditionStorage=$specInfo[0]['goods_storage'] - $v['goods_num'];
                        if($conditionStorage<=0){
                            $conditionStorage=0;
                        }
                        $condition = array(
                            'goods_storage' =>$conditionStorage
                        );
                        $res = $this->storeGoodItemPriceMod->doEdit($res_query[0]['id'], $condition);
                        if ($res) {
                            $infoSql = "select goods_storage from  " . DB_PREFIX . "store_goods WHERE `id` = '{$v['goods_id']}'";
                            $Info = $this->areaGoodMod->querySql($infoSql);
                            $condStorage=$Info[0]['goods_storage'] - $v['goods_num'];
                            if($condStorage<=0){
                                $condStorage=0;
                            }
                            $cond = array(
                                'goods_storage' => $condStorage
                            );
                            $this->areaGoodMod->doEdit($v['goods_id'], $cond);
                        }
                    }
                } else {
                    if($v['deduction']==1){
                        $infoSql = "select goods_storage,id from  " . DB_PREFIX . "store_goods WHERE `goods_id` = '{$v['good_id']}'";
                        $Info = $this->areaGoodMod->querySql($infoSql);
                        $condStorage= $Info[0]['goods_storage'] - $v['goods_num'];
                        if($condStorage<=0){
                            $condStorage=0;
                        }
                        $cond = array(
                            'goods_storage' =>$condStorage
                        );
                        foreach($Info as $key1=>$val1 ){
                            $this->areaGoodMod->doEdit($val1['id'], $cond);
                        }
                        $goodSql="select goods_storage from  " . DB_PREFIX . "goods WHERE `goods_id` = '{$v['good_id']}'";

                        $goodInfo = $this->areaGoodMod->querySql($goodSql);
                        $goodCondStorage= $goodInfo[0]['goods_storage'] - $v['goods_num'];
                        if($goodCondStorage<=0){
                            $goodCondStorage=0;
                        }
                        $goodCond = array(
                            'goods_storage' =>$goodCondStorage
                        );
                        $this->goodsMod->doEdit($v['good_id'],$goodCond);
                    }else{
                        $specInfoSql = "select goods_storage from  " . DB_PREFIX . "store_goods WHERE `id` = '{$v['goods_id']}'";
                        $specInfo = $this->areaGoodMod->querySql($specInfoSql);
                        $condition=$specInfo[0]['goods_storage'] - $v['goods_num'];
                        if($condition<=0){
                            $condition=0;
                        }
                        $condition = array(
                            'goods_storage' =>$condition
                        );
                        $this->areaGoodMod->doEdit($v['goods_id'],$condition);
                    }

                }
            }
        }

    /**
     * 选择店铺
     * @author tangp
     * @date 2019/03/22
     */
    public function getSendStore()
    {
        $order_sn = !empty($_REQUEST['order_sn']) ? $_REQUEST['order_sn'] : '';
        $store_ids = !empty($_REQUEST['store_ids']) ? $_REQUEST['store_ids'] : '';
        $this->assign('service_area_id',$_REQUEST['service_area_id']);
        $this->assign('service_store_id',$_REQUEST['service_store_id']);
        $this->assign('service_top_id',$_REQUEST['service_top_id']);
        $this->assign('service_second_id',$_REQUEST['service_second_id']);

        // 区域列表
        $area_data = &m('storeCate')->getAreaArr(1,$this->defaulLang);

        $service_area_data = array_map(function ($i, $m) {
            return array('id' => $i, 'name' => $m);
        }, array_keys($area_data), $area_data);

        $this->assign('service_area_data', $service_area_data);
        $sql = "SELECT `area_id`,`appoint_store`,`original_store` FROM bs_appoint_log WHERE `order_sn` = {$order_sn} AND `original_store` = " . $store_ids;
        $res_data = &m('appointLog')->querySql($sql);

        // 店铺列表
        $service_store_data = &m('store')->getStore($_REQUEST['service_area_id'], 1,$_REQUEST['service_store_id']);

        $service_store_data = &m('api')->convertArrForm($service_store_data);

        $this->assign('service_store_data', $service_store_data);

        $this->assign('order_sn',$order_sn);
        $this->assign('store_ids',$store_ids);
        $this->assign('res_data',$res_data);
        $this->display('orderList/sendStore.html');
    }

    /**
     * 指派店铺
     * @author tangp
     * @date 2019-03-22
     */
    public function doSend()
    {
        $service_area_id  = !empty($_REQUEST['service_area_id']) ? htmlspecialchars($_REQUEST['service_area_id']) : '';
        $service_store_id = !empty($_REQUEST['service_store_id']) ? htmlspecialchars($_REQUEST['service_store_id']) : '';
        $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars($_REQUEST['order_sn']) : '';
        $store_ids = !empty($_REQUEST['store_ids']) ? htmlspecialchars($_REQUEST['store_ids']) : '';
        if (empty($service_area_id)){
            $this->setData(array(),'0','请选择区域国家！');
        }
        if (empty($service_store_id)){
            $this->setData(array(),'0','请选择区域店铺！');
        }
        $sql = "SELECT * FROM bs_appoint_log WHERE `order_sn` = {$order_sn} AND `is_ckeck` = 0" ;
        $appointLogMod = &m('appointLog');
        $appointLogData = $appointLogMod->querySql($sql);
        if ($appointLogData){
            $this->setData(array(),0,'您已经指派了店铺，不能再执行此操作！');
        }
        $sqlss = "SELECT store_type FROM bs_store WHERE id = " . $this->storeId;
        $rrr = &m('store')->querySql($sqlss);
        if ($rrr[0]['store_type'] == 1) {
            $this->setData(array(),0,'总站无法指派店铺操作！请移步各店铺站点进行指派！');
        }

        $sqls = "SELECT goods_id FROM bs_order_goods WHERE order_id = " . $order_sn;
        $datas = &m('orderGoods')->querySql($sqls);
        $sqll = "SELECT * FROM bs_store_goods WHERE id = " . $datas[0]['goods_id'];
        $datass = &m('storeGoods')->querySql($sqll);
        if(empty($datass)){
            $this->setData(array(),0,'您指派的该店铺不存在该商品！');
        }
        $data = array(
            'area_id'  => $service_area_id,
            'order_sn' => $order_sn,
            'original_store'=> $store_ids,
            'appoint_store' => $service_store_id,
            'add_user' => $this->storeUserId,
            'add_time'=>time()
        );

        $res = $appointLogMod->doInsert($data);

        if ($res){
            $this->setData(array(),1,'指派成功！');
        }else{
            $this->setData(array(),0,'失败');
        }
    }
    /**
     * 联动店铺
     * @author tangp
     * @date 2019/03/22
     */
    public function getStoreSelect()
    {
        $type = $_REQUEST['type'] ? (int)$_REQUEST['type'] : 0;
        $id = $_REQUEST['id'] ? (int)$_REQUEST['id'] : 0;
        $store_ids = $_REQUEST['store_ids'];
        switch ($type) {
            // 获取店铺
            case 1:
                $data = &m('store')->getStore($id, 1,$store_ids);
                $data = &m('api')->convertArrForm($data);
                break;
        }
        $this->setData($data, 1);
    }

    /**
     * 接单
     */
    public function sureOrder() {
        $orderMod = &m('order');
        $orderGoodsMod = &m('orderGoods');
        $order_sn= !empty($_REQUEST['order_sn']) ? $_REQUEST['order_sn'] : 0;
        $sql = "SELECT * FROM bs_appoint_log WHERE `order_sn` = {$order_sn} AND `is_ckeck` = 0";
        $data = &m('appointLog')->querySql($sql);
        if (!empty($data)){
            $this->setData(array(),0,'该订单正在审核！无法进行此操作！');
        }
        // 主订单修改
        $data = array(
            'order_state' => 40, //收货状态
            'Appoint' => 2, //1未被指定 2被指定
            'Appoint_store_id' => $this->storeId, //被指定的站点
            'install_time' => time(), //区域配送安装完成时间
            'region_install' => 20, //10未配送 20已配送
            'singleperson' => $_SESSION['store']['userId'], //操作人员ID
        );
        $cond = array(
            'order_sn' => "{$order_sn}"
        );
        $condel = array(
            'order_id' => "{$order_sn}"
        );
        $detail = array(
            'order_state' => 40,
            'shipping_store_id' => $this->storeId,
        );
        $newRes=$orderMod->update_receive_time($this->storeId,$order_sn,$this->storeUserId);
        $res=$orderMod->doEditSpec($cond, $data);
        $detailRes = $orderGoodsMod->doEditSpec($condel, $detail);
        if ($newRes && $res && $detailRes) {
            $info['url'] = "store.php?app=order&act=index";
            $this->setData($info, $status = 1, '接单成功');
        }else{
            $this->setData(array(), $status = 1, '接单失败');
        }
    }
    
    public function sureOrder1() {
        $orderMod = &m('order');
        $orderGoodsMod = &m('orderGoods');
        $order_sn= !empty($_REQUEST['order_sn']) ? $_REQUEST['order_sn'] : 0;
        $sql = "SELECT * FROM bs_appoint_log WHERE `order_sn` = {$order_sn} AND `is_ckeck` = 0";
        $data = &m('appointLog')->querySql($sql);
        if (!empty($data)){
            $this->setData(array(),0,'该订单正在审核！无法进行此操作！');
        }
        // 主订单修改
        $data = array(
            'order_state' => 40, //收货状态
            'Appoint' => 2, //1未被指定 2被指定
            'Appoint_store_id' => $this->storeId, //被指定的站点
            'install_time' => time(), //区域配送安装完成时间
            'region_install' => 20, //10未配送 20已配送
            'singleperson' => $_SESSION['store']['userId'], //操作人员ID
        );
        $cond = array(
            'order_sn' => "{$order_sn}"
        );
        $condel = array(
            'order_id' => "{$order_sn}"
        );
        $detail = array(
            'order_state' => 40,
            'shipping_store_id' => $this->storeId,
        );
        $newRes=$orderMod->update_receive_time($this->storeId,$order_sn,$this->storeUserId);
        $res=$orderMod->doEditSpec($cond, $data);
        $detailRes = $orderGoodsMod->doEditSpec($condel, $detail);
        if ($newRes && $res && $detailRes) {
            $info['url'] = "store.php?app=order&act=index";
            $this->setData($info, $status = 1, '接单成功');
        }else{
            $this->setData(array(), $status = 1, '接单失败');
        }
    }
    /**
     * 发货
     */
    public function send() {
        $order_sn = $_REQUEST['order_sn'];
        $sql = "SELECT * FROM bs_appoint_log WHERE `order_sn` = {$order_sn} AND `is_ckeck` = 0";
        $data = &m('appointLog')->querySql($sql);
        if (!empty($data)){
            $this->setData(array(),0,'该订单正在审核！无法进行此操作！');
        }
        $arr = array(
            'order_state' => 30,
        );
        $data = array(
            "table" => "order",
            'cond' => " order_sn = '{$order_sn}' " ,
            'set' => $arr,
        );
        $this->orderMod->update_ship_time($this->storeId,$order_sn);
        $res = $this->orderMod->doUpdate($data);
        $arrs = array(
            'order_state' => 30,
        );
        $datas = array(
            "table" => "order_goods",
            'cond' => "order_id = '{$order_sn}' "  . ' and refund_state!=2',
            'set' => $arrs,
        );
        $res = $this->orderGoodsMod->doUpdate($datas);
        if ($res) {
            $info['url'] = "store.php?app=order&act=index";
            $this->setData($info, '1', $message = "发货成功！");
        } else {
            $this->setData(array(), '0', $message = "发货失败！");
        }
    }

    /**
     * 配送
     */
    public function pei() {
        $order_sn = $_REQUEST['order_sn'];
        $sql = "SELECT * FROM bs_appoint_log WHERE `order_sn` = {$order_sn} AND `is_ckeck` = 0";
        $data = &m('appointLog')->querySql($sql);
        if (!empty($data)){
            $this->setData(array(),0,'该订单正在审核！无法进行此操作！');
        }
        $sql = " select `fx_phone`,store_id from " . DB_PREFIX . "order where `order_sn`='{$order_sn}'";
        $ifo = $this->orderGoodsMod->querySql($sql);
        $storeId=$ifo[0]['store_id'];
        $set = array(
            "order_state" => 40,
        );
        $this->orderMod->update_delivery_time($storeId,$order_sn);

        $data = array(
            "table" => "order",
            'cond' => "order_sn = '{$order_sn}'",
            'set' => $set,
        );
        $this->orderMod->doUpdate($data);
        $datas = array(
            "table" => "order_goods",
            'cond' => "order_id ='{$order_sn}' ",
            'set' => $set,
        );
        $res = $this->orderGoodsMod->doUpdate($datas);

        if ($res) {
            $info['url'] = "store.php?app=order&act=index";
            $this->setData($info, $status = 1, $message = "操作成功！");
        } else {
            $this->setData(array(), $status = 0, $message = "操作失败！");
        }
    }

}
