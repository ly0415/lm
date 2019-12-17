<?php

/**
 * 订单中心
 * @author lvji
 *
 */
//include_once 'sms.app.php';
//include_once 'mail.app.php';
class OrderApp extends BaseWxApp {

    private $orderMod;
    private $orderGoodsMod;
    private $giftGoodMod;
    private $fxRuleMod;
//    private $fxUserTreeMod;
    private $fxUserMod;
    private $commentMod;
    private $fxRevenueLogMod;
    private $pointMod;
    private $pointOrderMod;
    private $userMod;

    public function __construct() {
        parent::__construct();
        $this->orderMod = &m('order');
        $this->orderGoodsMod = &m('orderGoods');
        $this->giftGoodMod = &m('giftGood');
        $this->fxRuleMod = &m('fxrule');
        $this->fxUserMod = &m('fxuser');
        $this->commentMod = &m('goodsComment');
        $this->fxRevenueLogMod = &m('fxRevenueLog');
        $this->pointMod = &m('point');
        $this->pointOrderMod = &m('pointOrder');
        $this->userMod = &m('user');
    }

    /**
     * 订单列表
     */
    public function orderindex()
    {
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;
        $type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : 1;//入口类型1:全部订单2:待付款3:待发货4:待收货5:待评价
        //where条件
        $where = " where a.mark = 1 ";
        switch ($type) {
            case 1:
                break;
            case 2:
                $where .= " and a.order_state = 10";
                break;
            case 3:
                $where .= " and a.order_state in (20,25)";
                break;
            case 4:
                $where .= " and a.order_state in (30,40)";
                break;
            case 5:
                $where .= " and a.order_state = 50 and a.evaluation_state = 0 ";
                break;
        }
        $orderBy = " order by a.id desc ";
        //查找店铺是总代理还是经销商，总代理显示所有订单，
        $storeMod = &m('store');
        $sqlStore = 'select store_type  from  ' . DB_PREFIX . 'store where id  = ' . $this->storeid;
        $dataStore = $storeMod->querySql($sqlStore);
        $data = array();//订单列表数组
        if ($dataStore[0]['store_type'] == 1) {//总代理
            $sql_1 = "select order_sn,store_id from bs_order where buyer_id = {$this->userId} AND mark = 1";
            $storeInfo = $this->orderMod->querySql($sql_1);
            //循环获取订单列表
            foreach($storeInfo as $v) {
                $sql = "select a.order_sn,a.store_id,a.order_state,a.order_amount,a.sendout,a.evaluation_state,b.store_name,c.shipping_fee,d.payment_type from bs_order_{$v['store_id']} as a " .
                    " left join bs_store_lang as b on a.store_id = b.store_id and b.lang_id=29 and b.distinguish = 0 " .
                    " left join bs_order_details_{$v['store_id']} as c on a.id = c.order_id " .
                    " left join bs_order_relation_{$v['store_id']} as d on a.id = d.order_id ";
                $temp = $this->orderMod->querySql($sql . $where . ' AND a.order_sn = '. $v['order_sn'] . $orderBy);
                $data = array_merge($data, $temp);
            }
        } else {//经销商
            //获取订单列表
            $sql = "select a.order_sn,a.store_id,a.order_state,a.order_amount,a.sendout,a.evaluation_state,b.store_name,c.shipping_fee,d.payment_type from bs_order_{$storeid} as a " .
                " left join bs_store_lang as b on a.store_id = b.store_id and b.lang_id=29 and b.distinguish = 0 " .
                " left join bs_order_details_{$storeid} as c on a.id = c.order_id " .
                " left join bs_order_relation_{$storeid} as d on a.id = d.order_id ";
            $data = $this->orderMod->querySql($sql . $where . ' and a.buyer_id = '. $this->userId . $orderBy);
        }
        unset($v);
        foreach ($data as $k => &$v) {
            $num_sql = "select count(*) as num from " . DB_PREFIX . "order_goods where order_id = '{$v['order_sn']}'"  ;
            $num = $this->orderGoodsMod->querySql($num_sql);
            $v['num'] = $num[0]['num'];
            $sql = "select o.*,o.goods_id as ogoods_id,l.*,gsl.original_img from "
                . DB_PREFIX . "order_goods as o left join "
                . DB_PREFIX . "store_goods as s on o.goods_id = s.id left join "
                . DB_PREFIX . "goods_lang as l  on s.goods_id = l.goods_id   left join "
                . DB_PREFIX . "goods as gsl  on s.goods_id = gsl.goods_id "
                . " where o.order_id='{$v['order_sn']}' and l.lang_id = 29 ";
            $list = $this->orderGoodsMod->querySql($sql);
            foreach ($list as $k2 => $v2) {
                if ($v2['spec_key']) {
                    $k_info = $this->get_spec($v2['spec_key'], 29);
                    foreach ($k_info as $k5 => $v5) {
                        $list[0][$k5]['spec_key_name'] = $v5['item_name'];
                    }
                }
            }
            $v['goods_list'] = $list;
            //订单状态
            $v['statusName'] = $this->orderMod->getOrderStatusName($v['sendout'], $v['order_state'], $v['evaluation_state']);
        }
        $this->assign('data', $data);
        $this->assign('storeid', $storeid);
        $this->assign('type', $type);
        $this->display("order/index.html");
    }

    /*
     * 我的订单
     * @author wangshuo
     * @date 2017-11-29
     */

    public function orderindex2() {
        //语言包
        $this->load($this->shorthand, 'WeChat/order');
        $this->assign('langdata', $this->langData);
        $a = $this->langData;
        $userId = $this->userId;
        $this->assign('symbol', $this->symbol);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;  //所选的站点id
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $referer = urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"]);//
        $where = ' buyer_id =' . $userId . ' and mark =' . 1;
        //查找店铺是总代理还是经销商，总代理显示所有订单，
        $storeMod = &m('store');
        $sqlStore = 'select store_type  from  ' . DB_PREFIX . 'store where id  = ' . $this->storeid;
        $dataStore = $storeMod->querySql($sqlStore);
        if ($dataStore[0]['store_type'] == 1) {
            //总代理
            //列表页数据
            $sql = 'select * from ' . DB_PREFIX . 'order'
                . ' where' . $where . ' order by order_id desc';
        } else {
            //经销商
            //列表页数据
            $sql = 'select * from ' . DB_PREFIX . 'order'
                . ' where' . $where . ' and store_id =' . $storeid
                . ' order by order_id desc';
        }
        $data = $this->orderMod->querySql($sql);
        foreach ($data as $k => $v) {
            $num_sql = "select count(*) as num from " . DB_PREFIX . "order_goods where order_id = '{$v['order_sn']}'"  ;
            $num = $this->orderGoodsMod->querySql($num_sql);
            $data[$k]['num'] = $num;
            $sql = "select o.*,o.goods_id as ogoods_id,l.*,gsl.original_img from "
                . DB_PREFIX . "order_goods as o left join "
                . DB_PREFIX . "store_goods as s on o.goods_id = s.id left join "
                . DB_PREFIX . "goods_lang as l  on s.goods_id = l.goods_id   left join "
                . DB_PREFIX . "goods as gsl  on s.goods_id = gsl.goods_id "
                . " where o.order_id='{$v['order_sn']}' and l.lang_id = " . $lang;
            $list = $this->orderGoodsMod->querySql($sql);
            foreach ($list as $k2 => $v2) {
                if ($v2['spec_key']) {
                    $k_info = $this->get_spec($v2['spec_key'], $lang);
                    foreach ($k_info as $k5 => $v5) {
                        $list[0][$k5]['spec_key_name'] = $v5['item_name'];
                    }
                }
            }
            $data[$k]['goods_list'] = $list;
            //赠品
            //赠品
            $sql = "select * from " . DB_PREFIX . "gift_goods  as g left join "
                . DB_PREFIX . "store_goods as s on g.goods_id = s.id left join "
                . DB_PREFIX . "goods_lang as l on s.goods_id = l.goods_id " . " where g.id=" . $v['gift_id'] . " and  lang_id = " . $lang;
            $res = $this->giftGoodMod->querySql($sql);
            if ($res[0]['goods_key']) {
                $k_info = $this->get_spec($res[0]['goods_key'], $lang);
                if ($k_info) {
                    $res[0]['goods_key_name'] = $k_info[0]['item_name'];
                }
            }
            $data[$k]['gift'] = $res;
        }
        $OrderStatus = array(
            "0" => $a['Canceled'],
            "10" => $a['Unpaid'],
            "20" => $a['payment'],
            "30" => $a['Shipped'],
            "40" => $a['Shipped_1'],
            "50" => $a['Receivedgoods'],
        );
        $this->assign('data', $data);
        $this->assign('lang', $lang);
        $this->assign('storeid', $storeid);
        $this->assign('status', $OrderStatus);
        $lang_id = $this->shorthand;
        $this->assign('lang_id', $lang_id);
        $this->assign('referer',$referer);
        $this->display("order/order.html");
    }

    /*
     * 我的订单详情
     * @author wangs
     * @2017-10-24 13:59:10
     */

    public function order_details() {
        //语言包
        $this->load($this->shorthand, 'WeChat/order');
        $this->assign('langdata', $this->langData);
        $a = $this->langData;
        //币种符号
        $symbol = $this->symbol;
        $this->assign('symbol', $symbol);
        $userId = $this->userId;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $orderid = $_REQUEST['orderid']; //订单id
        $where = ' f.buyer_id =' . $userId . " and g.order_id = '{$orderid}'"  ;
        $where .= ' and g.mark =' . 1;
        //查找店铺是总代理还是经销商，总代理显示所有订单，
        $storeMod = &m('store');
        $sqlStore = 'select store_type  from  ' . DB_PREFIX . 'store where id  = ' . $this->storeid;
        $dataStore = $storeMod->querySql($sqlStore);
        if ($dataStore[0]['store_type'] == 1) {
            //总代理
            //列表页数据
            $sql = 'select f.*,g.*, g.`add_time` from ' . DB_PREFIX . 'order as g left join ' . DB_PREFIX . 'order_goods as f '
                    . 'on f.order_id = g.order_sn '
                    . 'where' . $where;
        } else {
            //经销商
            //列表页数据
            $sql = 'select f.*,g.*, g.`add_time` from ' . DB_PREFIX . 'order as g left join ' . DB_PREFIX . 'order_goods as f '
                    . 'on f.order_id = g.order_sn '
                    . 'where' . $where . ' and f.store_id =' . $storeid;
        }

//          print_r($sql);exit;
        $data = $this->orderMod->querySql($sql);
        //获取订单所有商品
        $sql = "select o.*,o.goods_id as ogoods_id,l.* from "
                . DB_PREFIX . "order_goods as o left join "
                . DB_PREFIX . "store_goods as s on o.goods_id = s.id left join "
                . DB_PREFIX . "goods_lang as l  on s.goods_id = l.goods_id "
                . " where o.order_id= '{$data[0]['order_sn']}'and lang_id = " . $lang;
        $list = $this->orderGoodsMod->querySql($sql);
        foreach ($list as $k2 => $v2) {
            if ($v2['spec_key']) {
                $k_info = $this->get_spec($v2['spec_key'], $lang);
                foreach ($k_info as $k5 => $v5) {
                    $list[0][$k5]['spec_key_name'] = $v5['item_name'];
                }
            }
        }
        $data[0]['goods_list'] = $list;
        //买赠活动赠品
        $sql = "select * from " . DB_PREFIX . "gift_goods  as g left join "
                . DB_PREFIX . "store_goods as s on g.goods_id = s.id left join "
                . DB_PREFIX . "goods_lang as l on s.goods_id = l.goods_id " . " where g.id=" . $data[0]['gift_id'] . " and  lang_id = " . $lang;
        $res = $this->giftGoodMod->querySql($sql);
        if ($res[0]['goods_key']) {
            $k_info = $this->get_spec($res[0]['goods_key'], $lang);
            if ($k_info) {
                $res[0]['goods_key_name'] = $k_info[0]['item_name'];
            }
        }
        $data[0]['gift'] = $res;
        if ($data[0]['sendout'] == 1) {
            $shippingMethod = '自提';
        }
        if ($data[0]['sendout'] == 2) {
            $shippingMethod = '配送上门';
        }
        if ($data[0]['sendout'] == 3) {
            $shippingMethod = '邮寄托运';
        }
        $this->assign('shippingMethod', $shippingMethod);
        $this->assign('storeid', $storeid);
        $this->assign('info', $data[0]);
        $OrderStatus = array(
            "0" => $a['Canceled'],
            "10" => $a['Unpaid'],
            "20" => $a['payment'],
            "30" => $a['Shipped'],
            "40" => $a['Shipped_1'],
            "50" => $a['Receivedgoods'],
        );
        $this->assign('status', $OrderStatus);
        $this->assign('lang', $lang);
        $this->display("order/order-detail.html");
    }

    /*
     * 待付款
     * @author wangshuo
     * @date 2017-11-29
     */

    public function orderPayment() {
        //语言包
        $this->load($this->shorthand, 'WeChat/order');
        $this->assign('langdata', $this->langData);
        $userId = $this->userId;
        $this->assign('symbol', $this->symbol);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : 0;   //所选的站点id
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $this->assign('latlon', $latlon);
        $referer = urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"]);//
        $where = ' buyer_id =' . $userId . ' and order_state =10 and mark =1 ';
        //查找店铺是总代理还是经销商，总代理显示所有订单，
        $storeMod = &m('store');
        $sqlStore = 'select store_type  from  ' . DB_PREFIX . 'store where id  = ' . $this->storeid;
        $dataStore = $storeMod->querySql($sqlStore);
        if ($dataStore[0]['store_type'] == 1) {
            //总代理
            //列表页数据
            $sql = 'select * from ' . DB_PREFIX . 'order'
                    . ' where' . $where . ' order by order_id desc';
        } else {
            //经销商
            //列表页数据
            $sql = 'select * from ' . DB_PREFIX . 'order'
                    . ' where' . $where . ' and store_id =' . $storeid
                    . ' order by order_id desc';
        }
        $data = $this->orderMod->querySql($sql);

        foreach ($data as $k => $v) {
            $num_sql = "select count(*) as num from " . DB_PREFIX . "order_goods where order_id = '{$v['order_sn']}' ";
            $num = $this->orderGoodsMod->querySql($num_sql);
            $data[$k]['num'] = $num;
            $sql = "select o.*,o.goods_id as ogoods_id,l.* from "
                    . DB_PREFIX . "order_goods as o left join "
                    . DB_PREFIX . "store_goods as s on o.goods_id = s.id left join "
                    . DB_PREFIX . "goods_lang as l  on s.goods_id = l.goods_id "
                    . " where o.order_id= '{$v['order_sn']}'  and lang_id = " . $lang;
            $list = $this->orderGoodsMod->querySql($sql);
            foreach ($list as $k2 => $v2) {
                if ($v2['spec_key']) {
                    $k_info = $this->get_spec($v2['spec_key'], $lang);
                    foreach ($k_info as $k5 => $v5) {
                        $list[0][$k5]['spec_key_name'] = $v5['item_name'];
                    }
                }
            }
            $data[$k]['goods_list'] = $list;
            //赠品
            $sql = "select * from " . DB_PREFIX . "gift_goods  as g left join "
                    . DB_PREFIX . "store_goods as s on g.goods_id = s.id left join "
                    . DB_PREFIX . "goods_lang as l on s.goods_id = l.goods_id " . " where g.id=" . $v['gift_id'] . " and  lang_id = " . $lang;
            $res = $this->giftGoodMod->querySql($sql);
            if ($res[0]['goods_key']) {
                $k_info = $this->get_spec($res[0]['goods_key'], $lang);
                if ($k_info) {
                    $res[0]['goods_key_name'] = $k_info[0]['item_name'];
                }
            }
            $data[$k]['gift'] = $res;
        }
        $this->assign('data', $data);
        $this->assign('lang', $lang);
        $this->assign('storeid', $storeid);
        $lang_id = $this->shorthand;
        $this->assign('lang_id', $lang_id);
        $this->assign('referer',$referer);
        $this->display("order/orderPayment.html");
    }

    /*
     * 待发货
     * @author wangshuo
     * @date 2017-11-29
     */

    public function orderHair() {
        //语言包
        $this->load($this->shorthand, 'WeChat/order');
        $this->assign('langdata', $this->langData);
        $userId = $this->userId;
        $this->assign('symbol', $this->symbol);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;   //所选的站点id
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $where = ' buyer_id =' . $userId . ' and order_state =20 and mark =1 ';
        //查找店铺是总代理还是经销商，总代理显示所有订单，
        $storeMod = &m('store');
        $sqlStore = 'select store_type  from  ' . DB_PREFIX . 'store where id  = ' . $this->storeid;
        $dataStore = $storeMod->querySql($sqlStore);
        if ($dataStore[0]['store_type'] == 1) {
            //总代理
            //列表页数据
            $sql = 'select * from ' . DB_PREFIX . 'order'
                    . ' where' . $where . ' order by order_id desc';
        } else {
            //经销商
            //列表页数据
            $sql = 'select * from ' . DB_PREFIX . 'order'
                    . ' where' . $where . ' and store_id =' . $storeid
                    . '  order by order_id desc';
        }

        $data = $this->orderMod->querySql($sql);
        foreach ($data as $k => $v) {
            $num_sql = "select count(*) as num from " . DB_PREFIX . "order_goods where order_id = '{$v['order_sn']}' " ;
            $num = $this->orderGoodsMod->querySql($num_sql);
            $data[$k]['num'] = $num;
            $sql = "select o.*,o.goods_id as ogoods_id,l.* from "
                    . DB_PREFIX . "order_goods as o left join "
                    . DB_PREFIX . "store_goods as s on o.goods_id = s.id left join "
                    . DB_PREFIX . "goods_lang as l  on s.goods_id = l.goods_id "
                    . " where o.order_id= '{$v['order_sn']}'  and lang_id = " . $lang;
            $list = $this->orderGoodsMod->querySql($sql);
            foreach ($list as $k2 => $v2) {
                if ($v2['spec_key']) {
                    $k_info = $this->get_spec($v2['spec_key'], $lang);
                    foreach ($k_info as $k5 => $v5) {
                        $list[0][$k5]['spec_key_name'] = $v5['item_name'];
                    }
                }
            }
            $data[$k]['goods_list'] = $list;
            //赠品
            $sql = "select * from " . DB_PREFIX . "gift_goods  as g left join "
                    . DB_PREFIX . "store_goods as s on g.goods_id = s.id left join "
                    . DB_PREFIX . "goods_lang as l on s.goods_id = l.goods_id " . " where g.id=" . $v['gift_id'] . " and  lang_id = " . $lang;
            $res = $this->giftGoodMod->querySql($sql);
            if ($res[0]['goods_key']) {
                $k_info = $this->get_spec($res[0]['goods_key'], $lang);
                if ($k_info) {
                    $res[0]['goods_key_name'] = $k_info[0]['item_name'];
                }
            }
            $data[$k]['gift'] = $res;
        }
        $this->assign('data', $data);
        $this->assign('lang', $lang);
        $this->assign('storeid', $storeid);
        $this->display("order/orderHair.html");
    }

    /*
     * 待收货
     * @author wangshuo
     * @date 2017-11-29
     */

    public function orderCollect() {
        //语言包
        $this->load($this->shorthand, 'WeChat/order');
        $this->assign('langdata', $this->langData);
        $userId = $this->userId;
        $this->assign('symbol', $this->symbol);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;   //所选的站点id
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $where = ' buyer_id =' . $userId . ' and order_state in(30,40) and mark =1 ';
        //查找店铺是总代理还是经销商，总代理显示所有订单，
        $storeMod = &m('store');
        $sqlStore = 'select store_type  from  ' . DB_PREFIX . 'store where id  = ' . $this->storeid;
        $dataStore = $storeMod->querySql($sqlStore);
        if ($dataStore[0]['store_type'] == 1) {
            //总代理
            //列表页数据
            $sql = 'select * from ' . DB_PREFIX . 'order'
                    . ' where' . $where . ' order by order_id desc';
        } else {
            //经销商
            //列表页数据
            $sql = 'select * from ' . DB_PREFIX . 'order'
                    . ' where' . $where . ' and store_id =' . $storeid
                    . ' order by order_id desc';
        }
        $data = $this->orderMod->querySql($sql);
        foreach ($data as $k => $v) {
            $num_sql = "select count(*) as num from " . DB_PREFIX . "order_goods where order_id = '{$v['order_sn']}'";
            $num = $this->orderGoodsMod->querySql($num_sql);
            $data[$k]['num'] = $num;
            $sql = "select o.*,o.goods_id as ogoods_id,l.* from "
                    . DB_PREFIX . "order_goods as o left join "
                    . DB_PREFIX . "store_goods as s on o.goods_id = s.id left join "
                    . DB_PREFIX . "goods_lang as l  on s.goods_id = l.goods_id "
                    . " where o.order_id= '{$v['order_sn']}'  and lang_id = " . $lang;
            $list = $this->orderGoodsMod->querySql($sql);
            foreach ($list as $k2 => $v2) {
                if ($v2['spec_key']) {
                    $k_info = $this->get_spec($v2['spec_key'], $lang);
                    foreach ($k_info as $k5 => $v5) {
                        $list[0][$k5]['spec_key_name'] = $v5['item_name'];
                    }
                }
            }
            $data[$k]['goods_list'] = $list;
            //赠品
            $sql = "select * from " . DB_PREFIX . "gift_goods  as g left join "
                    . DB_PREFIX . "store_goods as s on g.goods_id = s.id left join "
                    . DB_PREFIX . "goods_lang as l on s.goods_id = l.goods_id " . " where g.id=" . $v['gift_id'] . " and  lang_id = " . $lang;
            $res = $this->giftGoodMod->querySql($sql);
            if ($res[0]['goods_key']) {
                $k_info = $this->get_spec($res[0]['goods_key'], $lang);
                if ($k_info) {
                    $res[0]['goods_key_name'] = $k_info[0]['item_name'];
                }
            }
            $data[$k]['gift'] = $res;
        }
        $this->assign('data', $data);
        $this->assign('lang', $lang);
        $this->assign('storeid', $storeid);
        $lang_id = $this->shorthand;
        $this->assign('lang_id', $lang_id);
        $this->display("order/orderCollect.html");
    }

    /*
     * 待评价
     * @author wangshuo
     * @date 2017-11-29
     */

    public function orderEvaluate() {
        //语言包
        $this->load($this->shorthand, 'WeChat/order');
        $this->assign('langdata', $this->langData);
        $userId = $this->userId;
        $this->assign('symbol', $this->symbol);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;   //所选的站点id
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        $where = ' buyer_id =' . $userId . ' and order_state =50 and mark =1 ';
        //查找店铺是总代理还是经销商，总代理显示所有订单，
        $storeMod = &m('store');
        $sqlStore = 'select store_type  from  ' . DB_PREFIX . 'store where id  = ' . $this->storeid;
        $dataStore = $storeMod->querySql($sqlStore);
        if ($dataStore[0]['store_type'] == 1) {
            //总代理
            //列表页数据
            $sql = 'select * from ' . DB_PREFIX . 'order'
                    . ' where' . $where . ' order by order_id desc';
        } else {
            //经销商
            //列表页数据
            $sql = 'select * from ' . DB_PREFIX . 'order'
                    . ' where' . $where . ' and store_id =' . $storeid
                    . ' order by order_id desc';
        }
        $data = $this->orderMod->querySql($sql);
        //对应订单下商品数量
        foreach ($data as $k => $v) {
            $num_order = "select count(*) as num from " . DB_PREFIX . "order_goods where order_id = '{$v['order_sn'] }'". ' and evaluation_state =0';
            $order_num = $this->orderGoodsMod->querySql($num_order);
            $data[$k]['order_num'] = $order_num;
            $num_sql = "select count(*) as num from " . DB_PREFIX . "order_goods where order_id = '{$v['order_sn']}'"  ;
            $num = $this->orderGoodsMod->querySql($num_sql);
            $data[$k]['num'] = $num;
            //商品列表多语言商品
            $sql = "select o.*,o.goods_id as ogoods_id,l.* from "
                    . DB_PREFIX . "order_goods as o left join "
                    . DB_PREFIX . "store_goods as s on o.goods_id = s.id left join "
                    . DB_PREFIX . "goods_lang as l  on s.goods_id = l.goods_id "
                . " where o.order_id= '{$v['order_sn']}'  and lang_id = " . $lang . ' and o.evaluation_state =0';
            $list = $this->orderGoodsMod->querySql($sql);
            //商品多语言规格
            foreach ($list as $k2 => $v2) {
                if ($v2['spec_key']) {
                    $k_info = $this->get_spec($v2['spec_key'], $lang);
                    foreach ($k_info as $k5 => $v5) {
                        $list[0][$k5]['spec_key_name'] = $v5['item_name'];
                    }
                }
            }
            $data[$k]['goods_list'] = $list;
            //赠品
            $sql = "select * from " . DB_PREFIX . "gift_goods  as g left join "
                    . DB_PREFIX . "store_goods as s on g.goods_id = s.id left join "
                    . DB_PREFIX . "goods_lang as l on s.goods_id = l.goods_id " . " where g.id=" . $v['gift_id'] . " and  lang_id = " . $lang;
            $res = $this->giftGoodMod->querySql($sql);
            //赠品多语言规格
            if ($res[0]['goods_key']) {
                $k_info = $this->get_spec($res[0]['goods_key'], $lang);
                if ($k_info) {
                    $res[0]['goods_key_name'] = $k_info[0]['item_name'];
                }
            }
            $data[$k]['gift'] = $res;
        }
        $this->assign('data', $data);
        $this->assign('lang', $lang);
        $this->assign('storeid', $storeid);
        $this->display("order/orderEvaluate.html");
    }

    /*
     * 评价详情页
     * @author wangshuo
     * @date 2017-11-29
     */

    public function evaluateDetails() {
        //语言包
        $this->load($this->shorthand, 'WeChat/order');
        $this->assign('langdata', $this->langData);
        $userId = $this->userId;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars(trim($_REQUEST['order_sn'])) : '';
        $rec_id = !empty($_REQUEST['rec_id']) ? htmlspecialchars(trim($_REQUEST['rec_id'])) : '';
        $goods_id = !empty($_REQUEST['gid']) ? htmlspecialchars(trim($_REQUEST['gid'])) : '';
        $storeid = !empty($_REQUEST['storeid']) ? htmlspecialchars(trim($_REQUEST['storeid'])) : '';
        $store_id = $_REQUEST['store_id'];
        $order_id = !empty($_REQUEST['order_id']) ? htmlspecialchars(trim($_REQUEST['order_id'])) : '';
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $where = ' and o.buyer_id = ' . $userId . ' and  o.rec_id = ' . $rec_id;
        //列表页数据
        $sql = "select o.*,o.goods_id as ogoods_id,l.* from "
                . DB_PREFIX . "order_goods as o left join "
                . DB_PREFIX . "store_goods as s on o.goods_id = s.id left join "
                . DB_PREFIX . "goods_lang as l  on s.goods_id = l.goods_id "
                . " where o.order_id= '{$order_sn}'" .  " and lang_id = " . $lang . $where;
        $data = $this->orderGoodsMod->querySql($sql);

        $this->assign('access_token', $this->accessToken);

        $this->assign('data', $data[0]);
        $this->assign('lang', $lang);
        $this->assign('rec_id', $rec_id);
        $this->assign('storeid', $storeid);
        $this->assign('goods_id', $goods_id);
        $this->assign('user_id', $userId);
        $this->assign('lang', $lang);
        $this->assign('order_id', $order_id);
        $this->assign('order_sn', $order_sn);
        $this->assign('store_id',$store_id);
        $languages = $this->shorthand;
        $this->assign('languages', $languages);
        $this->display("order/evaluateDetails.html");
    }

    /*
     * 评价
     * @author wangshuo
     * @date 2017-11-29
     */

    public function addEvaluate() {
        //语言包
        $this->load($_REQUEST['languages'], 'WeChat/order');
        $a = $this->langData;
        $userName = $this->userName ?: '';
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;  //1中文，2英语
        $user_id = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : $this->userId;  //所选的站点登陆的id
        $store_id = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $goods_id = !empty($_REQUEST['gid']) ? htmlspecialchars(trim($_REQUEST['gid'])) : '';
        $rec_id = !empty($_REQUEST['rec_id']) ? htmlspecialchars(trim($_REQUEST['rec_id'])) : '';
        $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars(trim($_REQUEST['order_sn'])) : '';
        $order_id = !empty($_REQUEST['order_id']) ? htmlspecialchars(trim($_REQUEST['order_id'])) : '';
        $star_num = !empty($_REQUEST['star_num']) ? htmlspecialchars(trim($_REQUEST['star_num'])) : '';
        $storeId  = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : '';
        $evaluete_content = !empty($_REQUEST['evaluete_content']) ? htmlspecialchars(trim($_REQUEST['evaluete_content'])) : '';
        $goods_images = ($_POST['order_pic']) ? $_POST['order_pic'] : '';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        $arr = implode(',', $goods_images);
        $list = rtrim($arr, ',');
        // 数据添加
        $data = array(
            'goods_id' => $goods_id,
            'user_id' => $user_id,
            'order_id' => $order_id,
            'rec_id' => $rec_id,
            'store_id' => $store_id,
            'content' => $evaluete_content,
            'goods_rank' => $star_num,
            'img' => $list,
            'username' => $userName,
            'add_time' => time()
        );
        $res = $this->commentMod->doInsert($data);
        //3.订单商品表 更新
        $order = array(
            "table" => "order",
            'cond' => "order_sn= '{$order_sn}'",
            "set" => array(
                "evaluation_state" => 1,
            ),
        );
        $res_order = $this->orderMod->doUpdate($order);
        $userOrderMod = &m('userOrder');
        //加入评价到新的店铺订单表
        $result = $userOrderMod->getOne(array('cond' => "`order_sn` = '{$order_sn}'", 'fields' => "store_id"));
        $this->orderMod->update_comment_time($result['store_id'],$order_sn,2);
        //3.订单商品表 更新
        $order_goods = array(
            "table" => "order_goods",
            'cond' => "order_id = '{$order_sn}'" . " and goods_id=" . $goods_id . " and rec_id=" . $rec_id,
            "set" => array(
                "evaluation_state" => 1,
            ),
        );
        $res_order_goods = $this->orderGoodsMod->doUpdate($order_goods);
        if ($res && $res_order && $res_order_goods) {
            $info['url'] = "wx.php?app=order&act=orderEvaluate&storeid={$store_id}&lang={$lang}&auxiliary={$auxiliary}&latlon={$latlon}";
            $this->setData($info, $status = 1, $a['evaluate_Success']);
        } else {
            $this->setData(array(), '0', $a['evaluate_fail']);
        }
    }

    /*
     * 退款/售后
     * @author wangshuo
     * @date 2017-11-29
     */
    public function orderRefund()
    {
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;
        //where条件
        $where = " where a.mark=1 and a.buyer_id={$this->userId} and a.order_state in (60,70) ";
        $orderBy = " order by a.id desc ";
        //查找店铺是总代理还是经销商，总代理显示所有订单，
        $storeMod = &m('store');
        $sqlStore = 'select store_type  from  ' . DB_PREFIX . 'store where id  = ' . $this->storeid;
        $dataStore = $storeMod->querySql($sqlStore);
        $data = array();//订单列表数组
        if ($dataStore[0]['store_type'] == 1) {//总代理
            $sql = "select DISTINCT(store_id) AS store_id from bs_order where buyer_id = {$this->userId}";
            $storeInfo = $this->orderMod->querySql($sql);
            //循环获取订单列表
            foreach($storeInfo as $v) {
                $sql = "select a.order_sn,a.store_id,a.order_state,a.order_amount,a.sendout,a.evaluation_state,b.store_name,c.shipping_fee from bs_order_{$v['store_id']} as a " .
                    " left join bs_store_lang as b on a.store_id = b.store_id and b.lang_id=29 and b.distinguish=0 " .
                    " left join bs_order_details_{$v['store_id']} as c on a.id = c.order_id ";
                $temp = $this->orderMod->querySql($sql . $where . $orderBy);
                $data = array_merge($data, $temp);
            }
        } else {//经销商
            //获取订单列表
            $sql = "select a.order_sn,a.store_id,a.order_state,a.order_amount,a.sendout,a.evaluation_state,b.store_name,c.shipping_fee from bs_order_{$storeid} as a " .
                " left join bs_store_lang as b on a.store_id = b.store_id and b.lang_id=29 and b.distinguish=0 " .
                " left join bs_order_details_{$storeid} as c on a.id = c.order_id ";
            $data = $this->orderMod->querySql($sql . $where . $orderBy);
        }
        unset($v);
        foreach ($data as $k => &$v) {
            $num_sql = "select count(*) as num from " . DB_PREFIX . "order_goods where order_id = '{$v['order_sn']}'"  ;
            $num = $this->orderGoodsMod->querySql($num_sql);
            $v['num'] = $num[0]['num'];
            $sql = "select o.*,o.goods_id as ogoods_id,l.* from "
                . DB_PREFIX . "order_goods as o left join "
                . DB_PREFIX . "store_goods as s on o.goods_id = s.id left join "
                . DB_PREFIX . "goods_lang as l  on s.goods_id = l.goods_id "
                . " where o.order_id='{$v['order_sn']}' and l.lang_id = 29 ";
            $list = $this->orderGoodsMod->querySql($sql);
            foreach ($list as $k2 => $v2) {
                if ($v2['spec_key']) {
                    $k_info = $this->get_spec($v2['spec_key'], 29);
                    foreach ($k_info as $k5 => $v5) {
                        $list[0][$k5]['spec_key_name'] = $v5['item_name'];
                    }
                }
            }
            $v['goods_list'] = $list;
            //订单状态
            $v['statusName'] = $this->orderMod->getOrderStatusName($v['sendout'], $v['order_state'], $v['evaluation_state']);
        }
        $this->assign('data', $data);
        $this->assign('storeid', $storeid);
        $this->display("order/orderRefund.html");
    }

    /**
     * 商品退款
     * @author wangs
     * @date 2017-10-27
     */
    public function refund() {
        //语言包
        $this->load($this->shorthand, 'WeChat/order');
        $this->assign('langdata', $this->langData);
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;  //所选的站点id
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        $userId = $this->userId;
        $where = ' buyer_id =' . $userId . " and order_sn ='{$_REQUEST['order_sn']}'";
        //列表页数据
        $sql = 'select * from ' . DB_PREFIX . 'order'
                . ' where' . $where . ' and store_id =' . $storeid;
        $data = $this->orderMod->querySql($sql);
        foreach ($data as $k => $v) {
            $sql = "select o.*,o.goods_id as ogoods_id,l.* from "
                    . DB_PREFIX . "order_goods as o left join "
                    . DB_PREFIX . "store_goods as s on o.goods_id = s.id left join "
                    . DB_PREFIX . "goods_lang as l  on s.goods_id = l.goods_id "
                    . " where o.order_id = '{$v['order_sn']}'"  . " and o.rec_id =" . $_REQUEST['rec_id'] . " and lang_id = " . $lang;
            $list = $this->orderGoodsMod->querySql($sql);
            foreach ($list as $k2 => $v2) {
                if ($v2['spec_key']) {
                    $k_info = $this->get_spec($v2['spec_key'], $lang);
                    foreach ($k_info as $k5 => $v5) {
                        $list[0][$k5]['spec_key_name'] = $v5['item_name'];
                    }
                }
            }
            $data[$k]['goods_list'] = $list;
        }
        $this->assign('data', $data);
        $this->assign('lang', $lang);
        $languages = $this->shorthand;
        $this->assign('languages', $languages);
        $this->assign('storeid', $storeid);
        $this->assign("order_id", $_REQUEST['order_id']);
        $this->assign("order_sn", $_REQUEST['order_sn']);
        $this->assign("rec_id", $_REQUEST['rec_id']);
        //映射页面
        $this->display("order/sqtk.html");
    }

    /**
     * 订单退款
     * @author wangs
     * @date 2017-10-27
     */
    public function qbRefund() {
        //语言包
        $this->load($this->shorthand, 'WeChat/order');
        $this->assign('langdata', $this->langData);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;  //所选的站点id
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $userId = $this->userId;
        $where = ' buyer_id =' . $userId . ' and order_sn =' . $_REQUEST['order_sn'];
        //列表页数据
        $sql = 'select * from ' . DB_PREFIX . 'order'
                . ' where' . $where . ' and store_id =' . $storeid;
        $data = $this->orderMod->querySql($sql);
        foreach ($data as $k => $v) {
            $sql = "select o.*,o.goods_id as ogoods_id,l.* from "
                    . DB_PREFIX . "order_goods as o left join "
                    . DB_PREFIX . "store_goods as s on o.goods_id = s.id left join "
                    . DB_PREFIX . "goods_lang as l  on s.goods_id = l.goods_id "
                    . " where o.order_id= '{$v['order_sn']}'"  . " and o.refund_state = 0  and lang_id = " . $lang;
            $list = $this->orderGoodsMod->querySql($sql);
            foreach ($list as $k2 => $v2) {
                if ($v2['spec_key']) {
                    $k_info = $this->get_spec($v2['spec_key'], $lang);
                    foreach ($k_info as $k5 => $v5) {
                        $list[0][$k5]['spec_key_name'] = $v5['item_name'];
                    }
                }
            }
            $data[$k]['goods_list'] = $list;
            ;
        }
        $this->assign('data', $data);
        $this->assign('lang', $lang);
        $languages = $this->shorthand;
        $this->assign('languages', $languages);
        $this->assign('storeid', $storeid);
        $this->assign("order_id", $_REQUEST['order_id']);
        $this->assign("order_sn", $_REQUEST['order_sn']);
        //映射页面
        $this->display("order/order-refund.html");
    }

    /**
     * 全部退款
     * @author wangs
     * @date 2018-7-25
     */
    public function refundGoods() {
        //语言包
        $this->load($_REQUEST['languages'], 'WeChat/order');
        $a = $this->langData;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;  //所选的站点id
        $store_id = $_REQUEST['store_id'];
        $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars(trim($_REQUEST['order_sn'])) : '';
        $reason_info = !empty($_REQUEST['reason_info']) ? htmlspecialchars(trim($_REQUEST['reason_info'])) : '';
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        if (empty($reason_info)) {
            $this->setData(array(), $status = '0', $a['refund_Reason']);
        }
        if (!empty($order_sn)) {
            //查询订单是否存在有效   有效么判断
            $data = array(
                "table" => "order",
                "cond" => "order_sn='{$order_sn}'",
            );
            $order_info = $this->orderMod->getData($data); //订单详细
            //2.退款退货表 插入
            if (is_array($order_info) && !empty($order_info)) {
                //2.退款退货表 插入
                $refund_return_data = array(
                    "table" => "refund_return",
                    "order_id" => $order_info[0]['order_id'], //订单ID
                    "order_sn" => $order_info[0]['order_sn'], //订单编号
                    "order_state" => $order_info[0]['order_state'], //订单状态：0(已取消)10(默认):未付款;20:已付款;30:已发货;40:已收货;',
                    "reason_info" => $reason_info, //退货原因内容
                    "store_id" => $order_info[0]['store_id'], //店铺ID
                    "store_name" => $order_info[0]['store_name'], //店铺名称
                    "buyer_id" => $order_info[0]['buyer_id'], //买家ID
                    "buyer_name" => $order_info[0]['buyer_name'], //买家会员名
                    "refund_amount" => $order_info[0]['order_amount'], //订单总价格
                    "refund_amounts" => $order_info[0]['order_amount'], //退款金额
                    "add_time" => time(), //添加时间    
                );
                $refund_return_id = $this->orderMod->doInsert($refund_return_data);
                $this->orderMod->update_refund_time($store_id,$order_sn,2);
                $refund_state = 1;
                //3.订单商品表 更新
                $order_goods_data = array(
                    "table" => "order_goods",
                    'cond' => "order_id= '{$order_sn}'". " and refund_state = 0",
                    "set" => array(
                        "refund_state" => $refund_state,
                    ),
                );
                $order_goods_result = $this->orderMod->doUpdate($order_goods_data);
                //4.订单表  更新
                $order_data = array(
                    "table" => "order",
                    'cond' => "order_sn='{$order_sn}'",
                    "set" => array(
                        "refund_state" => $refund_state,
                        "refund_amount" => $order_info[0]['order_amount'],
                    ),
                );
                $order_goods_result = $this->orderMod->doUpdate($order_data);

                if ($order_goods_result) {
                    //申请退款成功
                    $info['url'] = "?app=order&act=orderRefund&storeid={$storeid}&lang={$lang}&auxiliary={$auxiliary}&latlon={$latlon}";
                    $this->setData($info, $status = '1', $a['refund_Success']);
                } else {
                    //申请退款失败
                    $info['url'] = "?app=order&act=refund&storeid={$storeid}&lang={$lang}&auxiliary={$auxiliary}&latlon={$latlon}";
                    $this->setData($info, $status = '0', $a['refund_fail']);
                }
            } else {
                //提示订单错误
                $info['url'] = "?app=order&act=refund&storeid={$storeid}&lang={$lang}&auxiliary={$auxiliary}&latlon={$latlon}";
                $this->setData($info, $status = '0', $a['refund_error']);
            }
        }
    }

    /**
     * 获取商品规格
     * @param $goods_id|商品id  $type=1 读取商品原有规格属性  2 读取区域编辑后的规格属性
     * @return array
     */
    public function get_spec($k, $lang) {
        $storeGoodMod = &m("storeGoodItemPrice");
        $k = str_replace('_', ',', $k);
        $sql4 = "SELECT a.`order`,b.*,al.spec_name,bl.`item_name`  FROM " . DB_PREFIX . "goods_spec AS a
                     INNER JOIN " . DB_PREFIX . "goods_spec_item AS b ON a.id = b.spec_id
                     LEFT JOIN " . DB_PREFIX . "goods_spec_lang AS al ON a.id=al.spec_id
                     LEFT JOIN " . DB_PREFIX . "goods_spec_item_lang as bl ON b.id=bl.item_id
                     WHERE b.id IN($k) and al.lang_id=" . $lang . " and bl.lang_id=" . $lang . " ORDER BY b.id";
        $filter_spec2 = $storeGoodMod->querySql($sql4);
        return $filter_spec2;
    }

    /**
     * 订单ajax判断
     * @author wangs
     * @date 2017-10-26
     */
    public function editOrderState() {
        //语言包
        $this->load($this->shorthand, 'WeChat/order');
        $a = $this->langData;
        $_data = explode("_", $_REQUEST['data']);
        $id = $_data[0];
        $state = $_data[1];
        $ops = $_data[2];
        $store_id = $_data[3];
        switch ($state) {
            case 10:
                if ($ops == "cancel") {
                    $sql = " select `order_state`,order_id from " . DB_PREFIX . "order where `order_sn`='{$id}'";
                    $stateInfo = $this->orderGoodsMod->querySql($sql);
                    if($stateInfo[0]['order_state'] !=0){
                        $set = array(
                            "order_state" => 0,
                        );
                        //取消订单退还积分
                        $res = $this->returnPoint($id);
                        //取消订单退还优惠劵
                        $couponLogMod=&m('couponLog');

                        $couponCrond="order_sn = '{$id}'";  // by xt 2019.03.21

                        $couponLogMod->doDrops($couponCrond);
                        $this->orderMod->update_cancel_time($store_id,$id);
                    }
                }
            case 40:
                if ($ops == "receive") {
                    $set = array(
                        "order_state" => 50,
                        "delivery_status"=>1,
                        'finished_time' => time(),
                    );
                    $this->doOrderPoint($id);//订单积分
                    $fxUserMod=&m('fxuser');
                    $fxUserMod->getAccount($id);

                    $this->orderMod->update_receipt_time($store_id,$id,3);
                }
                break;
        }


        $data = array(
            "table" => "order",
            'cond' => "order_sn= '{$id}'" ,
            'set' => $set
        );
        if ($ops == "cancel") {
            $data['set']['Appoint'] = 2;
            $data['set']['Appoint_store_id'] = $store_id;
        }

        $sql = " select `fx_phone` from " . DB_PREFIX . "order where `order_sn`='{$id}'";
        $ifo = $this->orderGoodsMod->querySql($sql);
        if ($ifo[0]['fx_phone']) {
            $rs = $this->distrCom($id); // 分销按钮
        }
        $res = $this->orderMod->doUpdate($data);
        $datas = array(
            "table" => "order_goods",
            'cond' => "order_id='{$id}'",
            'set' => $set,
        );
        $res_goods = $this->orderGoodsMod->doUpdate($datas);
        if($ops == "receive"){
            $sql = " select `order_state`,order_id from " . DB_PREFIX . "order where `order_sn`='{$id}'";
            $orderInfo = $this->orderGoodsMod->querySql($sql);
            $orderRelationMod = &m('orderRelation');
            $orderRelationMod->insertOrderRelation($orderInfo[0]['order_id'], 3);
        }
        if ($res && $res_goods) {
            $this->setData(array(), $status = '1', $a['Successfuloperation']);
        } else {
            $this->setData(array(), $status = '0', $a['operationfailed']);
        }
    }

    //判断积分日志是否生成
    public function doOnePoint($id) {
        $pointLogMod = &m("pointLog");
        $res = $pointLogMod->getOne(array("cond" => "order_sn='{$id}'"));
        return $res;
    }

    /*
     * 订单积分获取
     * @author  lee
     * @date 2018-6-21 16:11:31
     */

    public function doOrderPoint($id) {
        $pointSiteMod = &m('point');
        $storePointMod = &m('storePoint');
        $storeMod = &m('store');
        $curMod = &m('currency');
        $userMod = &m('user');
        $order_info = $this->orderMod->getOne(array("cond" => "order_sn= '{$id}'"));
        //获取该订单获取的积分值
        $point_price_site = $pointSiteMod->getOne(array("cond" => "1=1"));
        $store_point_site = $storePointMod->getOne(array("cond" => "store_id=" . $order_info['store_id']));
        $store_id = $order_info['store_id'];
        $store_info = $storeMod->getOne(array("cond" => "id=" . $store_id));
        $rate = $curMod->getCurrencyRate($store_info['currency_id']);
        $money = $store_point_site['order_point'] * $order_info['order_amount']/100;
        $point = ceil($money );
        //更新用户的积分值
        $user_id = $this->userId;
        $user_info = $userMod->getOne(array("cond" => "id=" . $user_id));
        $user_point = $user_info['point'] + $point;
        $res = $userMod->doEdit($user_id, array("point" => $user_point));

        //积分日志
        if ($res) {
            $logMessage = "消费订单：" . $order_info['order_sn'] . " 获取：" . $point . "睿积分";
            $this->addPointLog($user_info['phone'], $logMessage, $user_id, $point, '-', $order_info['order_sn']);
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
     * 订单删除
     * @author wangshuo
     * @date 2017-11-20
     */
    public function dele()
    {
//        $storeid = $_REQUEST['storeid'] ? htmlspecialchars(trim($_REQUEST['storeid'])) : 0;
        $order_sn = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        $storeId = !empty($_REQUEST['storeId']) ? $_REQUEST['storeId'] : '';
        $set = array(
            "mark" => 0,
        );
        //删除旧表
        $datas = array(
            "table" => "order",
            'cond' => "order_sn ='{$order_sn}'",
            'set' => $set,
        );
        $this->orderMod->doUpdate($datas);
        //删除新表
        $datas['table'] = "order_{$storeId}";
        $res = $this->orderMod->doUpdate($datas);
        if ($res) {   //删除成功
            $this->setData(array(), 1, '删除成功');
        } else {
            $this->setData(array(), '0', '删除失败');
        }
    }

    /**
     * 根据分销码佣金分配
     * @author wanyan
     * @date 2017-11-21
     */
    public function distrCom($order_id) {
        $fxMainOrder = $this->orderMod->getOne(array('cond' => "`order_sn` = '{$order_id}'", 'fields' => '`order_id`,`order_sn`,`store_id`,buyer_id,buyer_name,buyer_email,buyer_address,store_id,order_amount,discount,fx_phone,fx_discount_rate'));
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
        $sql = "SELECT fu.user_id,fu.real_name,fur.fx_level,fur.pid,fur.pidpid FROM " . DB_PREFIX . "fx_user as fu
            LEFT JOIN " . DB_PREFIX . "fx_usertree as fur ON fu.user_id = fur.user_id WHERE fu.telephone = '{$mainInfo['fx_phone']}'";
        $info = $this->fxRuleMod->querySql($sql);
        if ($info[0]['fx_level'] == 3) { // 如果三级分销商的分销码
            $firstUserId = $this->getUserTreeId($info[0]['pidpid']);
            $secondUserId = $this->getUserTreeId($info[0]['pid']);
            $fxRule = $this->getRuleDetail($firstUserId);
            $insert_data_main['lev1_revenue'] = ($fxRule['lev1_prop'] * 0.01 * $mainInfo['order_amount']); // 一级佣金
            $insert_data_main['lev2_revenue'] = ($fxRule['lev2_prop'] * 0.01 * $mainInfo['order_amount']); // 二级佣金
            $insert_data_main['lev3_revenue'] = (($fxRule['lev3_prop'] - $mainInfo['fx_discount_rate']) * 0.01 * $mainInfo['order_amount']); // 三级佣金
            $insert_data_main['lev2_user_id'] = $secondUserId; // 二级分销商ID
            $insert_data_main['lev2_user_name'] = $this->getDisUser($secondUserId); // 二级分销商姓名
            $insert_data_main['lev3_user_id'] = $info[0]['user_id']; // 三级分销商ID
            $insert_data_main['lev3_user_name'] = $this->getDisUser($info[0]['user_id']); //  三级分销商姓名
            //var_dump($firstUserId);die;
        } elseif ($info[0]['fx_level'] == 2) { // 如果二级分销商的分销码
            $firstUserId = $this->getUserTreeId($info[0]['pid']);  // 一级分销商ID
            $fxRule = $this->getRuleDetail($firstUserId);
            $insert_data_main['lev1_revenue'] = ($fxRule['lev1_prop'] * 0.01 * $mainInfo['order_amount']); // 一级佣金
            $insert_data_main['lev2_revenue'] = (($fxRule['lev2_prop'] - $mainInfo['fx_discount_rate']) * 0.01 * $mainInfo['order_amount']); // 二级佣金
            $insert_data_main['lev3_revenue'] = 0.00; // 三级佣金
            $insert_data_main['lev2_user_id'] = $info[0]['user_id']; // 二级分销商ID
            $insert_data_main['lev2_user_name'] = $this->getDisUser($info[0]['user_id']); // 二级分销商姓名
            $insert_data_main['lev3_user_id'] = 0; // 三级分销商ID
            $insert_data_main['lev3_user_name'] = ''; // 三级分销商姓名
        } elseif ($info[0]['fx_level'] == 1) {
//          $firstUserId = $this->getUserTreeId($info[0]['user_id']);  // 一级分销商ID
            $firstUserId = $info[0]['user_id'];
            $fxRule = $this->getRuleDetail($firstUserId);
            $insert_data_main['lev1_revenue'] = (($fxRule['lev1_prop'] - $mainInfo['fx_discount_rate']) * 0.01 * $mainInfo['order_amount']); // 一级佣金
            $insert_data_main['lev2_revenue'] = 0.00; // 二级佣金
            $insert_data_main['lev3_revenue'] = 0.00; // 三级佣金
            $insert_data_main['lev2_user_id'] = 0; // 二级分销商ID
            $insert_data_main['lev2_user_name'] = ''; // 二级分销商姓名
            $insert_data_main['lev3_user_id'] = 0; // 三级分销商ID
            $insert_data_main['lev3_user_name'] = ''; // 三级分销商姓名
        }
        $store_cate = $this->getCurStoreInfo($mainInfo['store_id']);
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
        // var_dump($insert_data_total);die;
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
     * 获取分销商的姓名
     * @author wanyan
     * @date 2017-11-21
     */
    public function getDisUser($user_id) {
        $rs = $this->fxUserMod->getOne(array('cond' => "`user_id` = '{$user_id}'", 'fields' => "real_name"));
        return $rs['real_name'];
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
     * 微信服务器图片下载
     * @author wangshuo
     * @date 2018-1-15
     */
    public function getUploadPicture() {
        $serverId = isset($_POST['serverId']) ? htmlspecialchars($_POST['serverId']) : '';
        $access_token = isset($_POST['access_token']) ? htmlspecialchars($_POST['access_token']) : '';
//        echo "<script type='text/javascript'>alert('已全部清除！');</script>";
        //下载图片
        $url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token={$access_token}&media_id={$serverId}";
        $dirName = 'upload/images/order/' . date('Ymd') . '/';
        $imageName = time() . rand(1000, 9999) . '.jpg';
        if (!file_exists($dirName)) {
            mkdir($dirName, 0777, true);
        }
        $uploadPath = $dirName . $imageName;
        $ch = curl_init($url); // 初始化
        $fp = fopen($uploadPath, 'wb'); // 打开写入
        curl_setopt($ch, CURLOPT_FILE, $fp); // 设置输出文件的位置，值是一个资源类型
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        $newDirName = './upload/images/order/' . date('Ymd') . '/';
        $this->setData(array('uploadPath' => $uploadPath, 'newDirName' => $newDirName, 'imageName' => $imageName), $status = 1, $message = '获取成功');
    }

    //充值页面
    public function pointOrder() {
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;
        $this->assign('lang', $lang);
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;
        $this->assign('storeid', $storeid);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        $sql = "SELECT * FROM " . DB_PREFIX . 'user_point_site';
        $data = $this->pointMod->querySql($sql);
        $this->assign('res', $data[0]);
        $this->assign('userid', $this->userId);
        $this->display('personPoint/order.html');
    }

    //睿积分订单确认页面
    public function orderPoint() {
        $this->load($this->shorthand, 'userCenter/userCenter');

        $a = $this->langData;
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;
        $lang = $_REQUEST['lang'];
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $rate = !empty($_REQUEST['rate']) ? intval($_REQUEST['rate']) : '0';
        $point_num = !empty($_REQUEST['point_num']) ? intval($_REQUEST['point_num']) : '0';
        $amount = number_format(($point_num / $rate), 2, '.', '');
        $userid = !empty($_REQUEST['userid']) ? $_REQUEST['userid'] : $this->userId;
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';

        $rand = $this->buildNo(1);
        $orderNo = date('YmdHis') . $rand[0];
        if (empty($point_num)) {
            $this->setData($info = array(), $status = 0, $a['rui_num']);
        }
        if (!preg_match("/^[1-9][0-9]*$/", $point_num)) {
            $this->setData($info = array(), $status = 0, $a['rui_z']);
        }
        $orderData = array(
            'amount' => $amount,
            'point' => $point_num,
            'order_sn' => $orderNo,
            'status' => 0,
            'add_time' => time(),
            'buyer_id' => $userid
        );
        $res = $this->pointOrderMod->doInsert($orderData);
        if ($res) {
            $info['url'] = "?app=jsapi&act=pointJsapi&order_id={$orderNo}&storeid={$storeid}&lang={$lang}&auxiliary={$auxiliary}&latlon={$latlon}";
            $this->setData($info, $status = 1, '提交订单成功，请往支付');
        } else {
            $this->setData($info = array(), $status = 0, '订单提交失败');
        }
    }

    //生成订单号
    public function buildNo($limit) {
        $begin = pow(10, 3);
        $end = (pow(10, 4) - 1);
        $rand_array = range($begin, $end);
        shuffle($rand_array); //调用现成的数组随机排列函数
        return array_slice($rand_array, 0, $limit); //截取前$limit个
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
                $logMessage = "取消订单：" . $id . " 获取：" . $point_log['expend'] . "睿积分";
                $this->addPointLog($user_info['phone'], $logMessage, $user_id, $point_log['expend'], '-');
            }
        }
    }

}
