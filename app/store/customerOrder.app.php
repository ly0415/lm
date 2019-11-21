<?php

/**
 * 代客下单
 * @author wangshuo
 * @date 2018-5-10
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class customerOrderApp extends BaseStoreApp {

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
    private $goodsSpecPriceMod;
    private $goodsMod;
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
        $this->storeGoodItemPriceMod = &m('storeGoodItemPrice');
        $this->fxRuleMod = &m('fxrule');
        $this->fxRevenueLogMod = &m('fxRevenueLog');
        $this->fxTreeMod = &m('fxuserTree');
        $this->fxUserMod = &m('fxuser');
        $this->fxUserTreeMod = &m('fxuserTree');
        $this->fxuserMoneyMod = &m('fxuserMoney');
        $this->areaGoodMod = &m('areaGood');
        $this->goodsSpecPriceMod = &m('goodsSpecPrice');
        $this->goodsMod=&m('goods');
        $this->amountLogMod = &m('amountLog');
        $this->userMod=&m('user');

    }

 /**
     * 代客下单
     * @author wangshuo
     * @date 2018-5-10
     */
    public function index() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $userCouponMod=&m("userCoupon");//用户劵表
        $goods_name = !empty($_REQUEST['goods_name']) ? htmlspecialchars(trim(addslashes($_REQUEST['goods_name']))) : '';
        $payment_code = !empty($_REQUEST['payment_code']) ? htmlspecialchars(trim($_REQUEST['payment_code'])) : '';
        $buyer_email = !empty($_REQUEST['buyer_email']) ? htmlspecialchars(trim($_REQUEST['buyer_email'])) : '';
        $buyer_name = !empty($_REQUEST['buyer_name']) ? htmlspecialchars(trim($_REQUEST['buyer_name'])) : '';
        $buyer_phone = !empty($_REQUEST['buyer_phone']) ? htmlspecialchars(trim($_REQUEST['buyer_phone'])) : '';
        $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars(trim($_REQUEST['order_sn'])) : '';
        $shipping_code = !empty($_REQUEST['shipping_code']) ? htmlspecialchars(trim($_REQUEST['shipping_code'])) : '';
        $clickandview = !empty($_REQUEST['clickandview']) ? htmlspecialchars(trim($_REQUEST['clickandview'])) : '0';
        $orderState=!empty($_REQUEST['orderState']) ? htmlspecialchars(trim($_REQUEST['orderState'])) : '0';
        $refund_state=!empty($_REQUEST['refund_state']) ? htmlspecialchars(trim($_REQUEST['refund_state'])) : '0';
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $where = " where (g.sendout=1 or g.sendout='') ";
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
        if (!empty($buyer_phone)) {
            $where .= " and g.buyer_phone like '%" . $buyer_phone . "%'";
        }
        if (!empty($shipping_code)) {
            $where .= " and g.shipping_code like '%" . $shipping_code . "%'";
        }
        if (!empty($clickandview)) {
            $where .= " and g.order_state >0  and g.clickandview like '%" . $clickandview . "%'";
        }
        if(!empty($orderState)){
            $this->assign('orderState',$orderState);
            if($orderState==60){
                $orderState=0;
            }
            $where .= " and g.order_state ={$orderState} ";
        };
        if (!empty($refund_state)) {
            $where .= " and f.refund_state like '%" . $refund_state . "%'";
        }

        $this->assign("p", $p);
        $this->assign('goods_name', $goods_name);
        $this->assign('payment_code', $payment_code);
        $this->assign('buyer_email', $buyer_email);
        $this->assign('buyer_name', $buyer_name);
        $this->assign('buyer_phone',$buyer_phone);
        $this->assign('order_sn', $order_sn);
        $this->assign('shipping_code', $shipping_code);
        $this->assign('clickandview', $clickandview);
         $this->assign('refund_state', $refund_state);
        //获取站点类型
        $storeMod = &m('store');
        $storeInfo = $storeMod->getRow($this->storeId, 'id,store_type,store_cate_id');
        if ($storeInfo['store_type'] != 1) {    //区域站点 斐总代理
            $where .= ' AND g.store_id = ' . $this->storeId;
        } elseif ($storeInfo['store_type'] == 1) {
            $ids = $storeMod->getIds(array(
                'cond' => ' store_cate_id = ' . $storeInfo['store_cate_id'],
            ));
            $ids = implode(',', $ids);
            $where .= ' AND g.store_id in (' . $ids . ')';
        }
        //订单列表页数据

        $sql = 'select distinct g.order_sn, g.is_source,g.order_state,g.sendout,g.pei_time,g.fx_phone,g.source_id,g.order_id,g.goods_amount,g.order_amount,g.discount,g.pd_amount,g.cp_amount,g.shipping_fee,g.buyer_name,g.buyer_phone,g.sub_user,g.add_time,se.img,g.Appoint,g.buyer_phone from '
                . DB_PREFIX . 'order as g left join  '
                . DB_PREFIX . 'order_goods as f ' . ' on f.order_id = g.order_sn left join '
                . DB_PREFIX . 'store_source as se ' . ' on g.source_id = se.id '
//                . DB_PREFIX . 'fx_order as d ' . ' on g.order_id = d.order_id '
                . $where
                . ' order by g.order_id desc';
//        echo $sql;die;
        $result = $this->orderMod->querySqlPageData($sql);
        $data = $result['list'];
        //订单商品数据
        foreach ($data as $k => $v) {
            $v_where = "order_id='{$v['order_sn']}'"  ;
            $cond = array(
                'cond' => $v_where
            );
            $sendVoucher=$userCouponMod->getOne(array('cond'=>"`order_sn`='{$v['order_sn']}'",'id'));//是否赠送了兑换券
            $list = $this->orderGoodsMod->getData($cond);

            $data[$k]['goods_list'] = $list;
            $data[$k]['sendVoucher']=$sendVoucher;
            if ($data[$k]['sendout'] == 1) {
                if ($this->lang_id == 1) {
                    $data[$k]['shippingMethod'] = 'Self lifting';
                } else {
                    $data[$k]['shippingMethod'] = '自提';
                }
            }
            //赠品
            $sqle = "select * from " . DB_PREFIX . "gift_goods  as g left join "
                    . DB_PREFIX . "store_goods as s on g.goods_id = s.id left join "
                    . DB_PREFIX . "goods_lang as l on s.goods_id = l.goods_id " . " where g.id=" . $v['gift_id'] . " and  lang_id = " . $this->languageId;
            $res = $this->giftGoodMod->querySql($sqle);
            if ($res[0]['goods_key']) {
                $k_info = $this->get_spec($res[0]['goods_key'], $lang);
                if ($k_info) {
                    $res[0]['goods_key_name'] = $k_info[0]['item_name'];
                }
            }
            $data[$k]['gift'] = $res;
            //代客订单通过兑换券兑换的订单不能显示赠送兑换券按钮

                // $sql="select c.id from  ".DB_PREFIX."coupon as c left join ".DB_PREFIX."coupon_log as cl on  c.id =cl.coupon_id
                // where c.type = 2 and  cl.order_id = '{$v['order_id']}'";
                $sql="select c.id from  ".DB_PREFIX."coupon as c left join ".DB_PREFIX."coupon_log as cl on  c.id =cl.coupon_id 
                where c.type = 2 and  cl.order_sn = '{$v['order_sn']}'";  // by xt 2019.03.21
                $dui=$this->giftGoodMod->querySql($sql);
                if($dui || $v['buyer_phone'] == '18005700102'){
                    $data[$k]['dui']=1;
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
        //赠送兑换劵活动开启
        $systemConsoleMod=&m('systemConsole');
        $timeData=$systemConsoleMod->getOne(array('cond'=>"`type` =3 and status=1",'fields'=>'start_time,end_time'));

        if(!empty($timeData)){
            if($timeData['start_time']<time() && $timeData['end_time']>time()){
                $this->assign('sendVoucher',1);
            }
        }
        if (!empty($clickandview)) {
            $orderMod = &m('order');
            $sql=<<<SQL
                UPDATE bs_order SET clickandview = 2 WHERE  store_id = {$this->storeId} AND sendout='1'
SQL;
            $orderMod->doEditSql($sql);
        }
        $this->assign('symbol', $this->symbol);
        $this->assign('status', $OrderStatus);
        $this->assign('store_id', $this->storeId);
        $this->assign('store', $this->getUseStore());
        $this->assign('lang_id', $this->lang_id);
        $this->display('customerOrder/index.html');
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
     * 订单提醒
     */
    public function sendOrderNotice() {
        //查询order表是否有新订单
        $result = $this->orderMod->getIds(array(
            'cond' => ' warning_tone = 1 AND mark = 1 AND store_id = ' . $this->storeId,
        ),'','order_id');
        if($result){
            $result  = implode(',', $result);
            $this->setData($result, $status = 1);
        } else {
            $this->setData(array(), $status = 0);
        }
    }

    /**
     * 订单提醒
     */
    public function sendOrderNotice_1() {
        $val = !empty($_REQUEST['val']) ? $_REQUEST['val'] : 0;
        //已经提示过的订单需要变成未提示状态
        $upd_sql = 'UPDATE bs_order SET warning_tone = 2 WHERE order_id in ('.$val.')';
        $this->orderMod->querySql($upd_sql);
    }

    /**
     * 打印整合
     * @author tangp
     * @date 2018-11-07
     */
    public function windowPrint(){
        $order_sn = !empty($_REQUEST['order_sn']) ? $_REQUEST['order_sn'] : '';

        $store_id     = $_SESSION['store']['storeId'];
        $orderMod     = &m('order'.$store_id);
        $modString_1  = DB_PREFIX.'order_'.$store_id;
        $modString_2  = DB_PREFIX.'order_details_'.$store_id;
        $modString_3  = DB_PREFIX.'order_relation_'.$store_id;
        //获取新订单表订单数据
        $fields = 'b.*,a.id,a.order_sn,a.order_state,a.goods_amount,a.sendout,a.order_amount,a.add_time,c.username,c.phone';
        $sql    = 'select ' . $fields . ' from ' . $modString_1 . ' as a left join '
            . $modString_2 . ' as b on b.order_id = a.id left join '
            . DB_PREFIX . 'user as c on c.id = a.buyer_id '
            . 'where a.order_sn  = \''. $order_sn . '\'' ;
        $print_data = $orderMod -> querySql($sql);

        $this->langDataBank = languageFun('ZH');
        //获取订单状态语言
        $orderStatus = array(
            0  => $this->langDataBank->public->canceled,
            10 => $this->langDataBank->public->no_pay,
            20 => $this->langDataBank->public->has_paid,
            25 => '已接单',
            30 => $this->langDataBank->public->shipped,
            40 => $this->langDataBank->public->regional_distribution,
            50 => $this->langDataBank->public->received,
            60 => $this->langDataBank->project->refunded,
        );
        //获取订单支付信息
        $paymentType = array(
            1 => '支付宝支付',
            2 => '微信支付',
            3 => '余额支付',
            4 => '线下支付',
            5 => '免费兑换'
        );

        //获取订单支付信息
        $paymentType = array(
            1 => '支付宝支付',
            2 => '微信支付',
            3 => '余额支付',
            4 => '线下支付',
            5 => '免费兑换'
        );

        $print_data[0]['delivery']      && $print_data[0]['format_delivery']       = unserialize( $print_data[0]['delivery']);
        $print_data[0]['sendout_time']  && $print_data[0]['format_sendout_time']   = date('H:i', $print_data[0]['sendout_time']);
        $print_data[0]['phone']         && $print_data[0]['format_phone']          = substr_replace($print_data[0]['phone'], '****', 3, 4);

        //小票打印商品总数
        $good_num = 0;
        foreach ($print_data as $key => $val) {
            //获取订单商品
            $sql    = "select goods_name,spec_key_name,goods_num,goods_pay_price from " . DB_PREFIX . "order_goods where order_id = '{$val['order_sn']}' " ;
            $list   = $orderMod->querySql($sql);
            $print_data[$key]['goods_list']     = $list;
            //获取订单的支付状态
            $sql            = "select payment_type from " . DB_PREFIX . "order_relation_{$store_id} where order_id = '{$val['id']}' " ;
            $paymentStatus  = $orderMod->querySql($sql);
            foreach($list as $k => $v){
                $good_num += intval($v['goods_num']);
                $print_data[$key]['goods_list'][$k]['current_num']  = $good_num;
            }
            $print_data[$key]['good_num']       = $good_num;
            //获取分销人员
            if( $val['fx_user_id'] ){
                $fxUserMod  = &m('fxuser');
                $fxUserInfo = $fxUserMod ->getRow($val['fx_user_id']);
                $print_data[$key]['fx_code']    = $fxUserInfo['fx_code'];
            }

            $sql_1 = 'select a.store_mobile,b.store_name from bs_store as a left join bs_store_lang as b on a.id = b.store_id where a.id = '.$store_id.' AND b.lang_id = 29';
            $store_name = $orderMod -> querySql($sql_1);
            $print_data[$key]['store_mobile']           = $store_name[0]['store_mobile'];
            $print_data[$key]['store_name']             = $store_name[0]['store_name'];
            $print_data[$key]['format_payment_type']    = $paymentType[$paymentStatus[0]['payment_type']];
            $print_data[$key]['format_order_state']     = $orderStatus[$val['order_state']];
        }

        //计算规格总数
        $total_num                      = end($print_data);
        $print_data[0]['total_num']     = $total_num['good_num'];
        $this->assign('print_all'       , $print_data);
        $this->assign('print_data'      , $print_data[0]);
        $this->display('customerOrder/windowsPrint.html');
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
        $sql = " select * from " . DB_PREFIX . "order where sendout=1 and store_id =" . $this->storeId . " order by order_id DESC ";
        $orderlist = $orderMod->querySql($sql);
        $userMod = &m('user');
        $totalMoney = 0;
        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=订单统计报表.xls");
        echo iconv('utf-8', 'gb2312', "订单编号") . "\t";
        echo iconv('utf-8', 'gb2312', "用户昵称") . "\t";
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
     * 订单详情页面
     * @author wangs
     * @date 2017/10/24
     */
    public function details() {
        $order_id = $_REQUEST['order_id']; //订单id
        $arr = array(
            "clickandview" => 2,
        );
        $datas = array(
            "table" => "order",
            'cond' => "order_id = '{$order_id}' " . $order_id,
            'set' => $arr,
        );
        $res = $this->orderMod->doUpdate($datas);
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $store_id = $this->storeId;
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $where = " where g.order_id = '{$order_id}'";
        //列表页数据
        $sql = 'select *, g.add_time from '
                . DB_PREFIX . 'order as g left join '
                . DB_PREFIX . 'user_address a' . ' on a.user_id = g.buyer_id' . $where;
        $info = $this->orderMod->querySql($sql);
        foreach ($info as $k => $v) {
            $v_where = "order_id= '{$v['order_sn']}'"  ;
            $cond = array(
                'cond' => $v_where
            );
            $list = $this->orderGoodsMod->getData($cond);
            $info[$k]['goods_list'] = $list;
        }
        $this->assign('info', $info[0]);

        $userInfo = &m('userInfo');
        $sourceData= $userInfo->source;
        $this->assign('sourceData',$sourceData);
        $sqls = "SELECT order_sn FROM bs_order WHERE order_id = " .$order_id;
        $orderData = $this->orderMod->querySql($sqls);
        $infoData = $userInfo->getUserInfo($orderData[0]['order_sn']);
//        dd($infoData);die;
        $this->assign('infoData',$infoData);

        $datas = $userInfo->countUserInfo($orderData[0]['order_sn']);
        $this->assign('datas',$datas);
        //以order_sn 查询退款商品记录
        $refund_sql = "select * from " . DB_PREFIX . "refund_return as r"
                . " where r.order_sn= '{$info[0]['order_sn']}'"
                . " and r.order_id= '{$info[0]['order_id']}'";

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
        $auth = $this->auth;
        $user_sql = 'select username from ' . DB_PREFIX . 'user where id = ' . $info[0]['buyer_id'];
        $username = $this->orderMod->querySql($user_sql);
        $this->assign('username', $username[0]['username']);
        $this->assign('p', $p);
        $this->assign('status', $OrderStatus);
        $this->assign('symbol', $this->symbol);
        $this->assign('lang_id', $this->lang_id);
        $this->assign('store_id', $store_id);
        $this->assign('auth', $auth);
        $this->assign('store', $this->getUseStore());
        $this->display('customerOrder/details_edit.html');
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
        $ops = $_data[3];


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
                    'goods_storage' => $specInfo[0]['goods_storage'] + $v['goods_num']
                );
                $this->areaGoodMod->doEdit($v['goods_id'], $condition);
            }
        }

        switch ($state) {
            case 1:
                if ($ops == "Agree") {
                    $set = array(
                        "refund_state" => 2,
                        'order_state' => 0,
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
                        'type'=>6,
                    );
                    $this->createAmountlog($amountLogData);
                }
        }
        $datas = array(
            "table" => "order_goods",
            'cond' => "order_id = '{$order_sn}'"  . ' and refund_state = 1',
            'set' => $set,
        );
//        print_r($datas);exit;
        $this->orderGoodsMod->doUpdate($datas);
        $data = array(
            "table" => "order",
            'cond' => "order_sn = '{$order_sn}'"  . " and order_id = '{$order_id}'" ,
            'set' => $set,
        );
        $res = $this->orderMod->doUpdate($data);
        $sql_r = "select count(*)  as num from " . DB_PREFIX . "order_goods where order_id = '{$order_sn}'  and refund_state = 2 ";
        $sql_o = "select count(*)  as num from " . DB_PREFIX . "order_goods where order_id = '{$order_sn}''";
        $order_goods_r = $this->orderMod->querySql($sql_r);
        $order_goods_o = $this->orderMod->querySql($sql_o);
        if ($order_goods_r) {
            if ($order_goods_r[0]['num'] == $order_goods_o['num']) {
                $this->returnPoint($order_sn);
            }
        }
        if ($res) {
            $this->setData(array(), 1, "操作成功");
        } else {
            $this->setData(array(), 0, "操作失败");
        }
    }
    //生成充值记录
    public  function  createAmountlog($data){
        $amountLogId=$this->amountLogMod->doInsert($data);
        return $amountLogId;
    }
    /*
     * 取消订单退还积分
     * @author lee
     * @date 2018-6-22 15:03:17
     */

    public function returnPoint($id) {
        $userMod = &m('user');
        $pointLogMod = &m("pointLog");
        $point_log = $pointLogMod->getOne(array("cond" => "order_sn='{$id}'"));
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
     * 确认接单
     * @author wangshuo
     * @date 2017-12-25
     */
    public function editAAppoint() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $lang_id = !empty($_REQUEST['lang_id']) ? (int) ($_REQUEST['lang_id']) : '0';
        $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars($_REQUEST['order_sn']) : '';
        $p = !empty($_REQUEST['p']) ? htmlspecialchars($_REQUEST['p']) : '1';

        // 主订单修改
        $data = array(
            'order_state' => 40, //收货状态
            'Appoint' => 2, //1未被指定 2被指定
            'Appoint_store_id' => $this->storeId, //被指定的站点
            'install_time' => time(), //区域配送安装完成时间
            'region_install' => 20, //10未配送 20已配送
            'singleperson' => $_SESSION['store']['userId'], //操作人员ID
        );
        // 注定但订单修改
        $cond = array(
            'order_sn' => "{$order_sn}"
        );
        $this->orderMod->update_delivery_time($this->storeId,$order_sn);
        $res = $this->orderMod->doEditSpec($cond, $data);


        if ($res) {
            // 子订单修改
            $condel = array(
                'order_id' => "{$order_sn}"
            );
            $detail = array(
                'order_state' => 40,
                'shipping_store_id' => $this->storeId,
            );

            $detailRes = $this->orderGoodsMod->doEditSpec($condel, $detail);


            if ($detailRes) {
                if ($lang_id == 1) {
                    $this->setData(array(), $status = 1, 'Succeeds');
                } else {
                    $this->setData(array(), $status = 1, '接单成功');
                }
            } else {
                if ($lang_id == 1) {
                    $this->setData(array(), $status = 0, 'Failure of receipt');
                } else {
                    $this->setData(array(), $status = 0, '接单失败');
                }
            }
        }
    }

        public function voucherList(){
            //获取当前店铺业务类型
            $storebusinessMod = &m('storebusiness');
            $bussInfo1 = $storebusinessMod->getInfoByStoreid($this->storeId);
            $bussIds = array();
            foreach($bussInfo1 as $v) {
                $bussIds[] = $v['buss_id'];
            }
            $sql = "select id from " . DB_PREFIX ."room_type where superior_id in (".implode(',', $bussIds).")";
            $bussInfo2 = $this->orderMod->querySql($sql);
            foreach($bussInfo2 as $v) {
                $bussIds[] = $v['id'];
            }

            $couponMod=&m('coupon');
            $sql="select count(*) as total from bs_coupon where type=2 and mark=1 and source=2 and room_type_id in (" . implode(',', $bussIds) . ")";
            $res = $couponMod->querySql($sql);
            $total = $res[0]['total'];
            $pagesize = 10;
            $totalpage = ceil($total / $pagesize);
            if (empty($totalpage)) {
                $totalpage = 1;
            }
            $this->assign('totalpage', $totalpage);
            //分页定义
            $currentPage = 1;
            $start = ($currentPage - 1) * $pagesize;
            $end = $pagesize;
            $limit = '  limit  ' . $start . ',' . $end;
            $sql = "SELECT c.id,c.add_time,c.money,rtl.type_name FROM bs_coupon as c LEFT JOIN bs_room_type_lang as rtl ON c.room_type_id =rtl.type_id 
            WHERE c.type=2 and c.mark=1 and c.source=2 and c.room_type_id in (".implode(',', $bussIds).") AND rtl.lang_id = {$this->defaulLang}   order by c.id desc".$limit;
            $voucherData = $couponMod->querySql($sql);
            $this->assign('data', $voucherData);
            $this->display('customerOrder/voucherList.html');
        }


    //获取每页的电子劵
    public function getVoucherList()
    {
        $p = $_REQUEST['p'];
        $couponMod=&m('coupon');
        $cat_1 = !empty($_REQUEST['cat_1']) ? htmlspecialchars(trim($_REQUEST['cat_1'])) : 0;
        $cat_2 = !empty($_REQUEST['cat_2']) ? htmlspecialchars(trim($_REQUEST['cat_2'])) : 0;
        //获取当前店铺业务类型
        $storebusinessMod = &m('storebusiness');
        $bussInfo1 = $storebusinessMod->getInfoByStoreid($this->storeId);
        $bussIds = array();
        foreach($bussInfo1 as $v) {
            $bussIds[] = $v['buss_id'];
        }
        $sql = "select id from " . DB_PREFIX ."room_type where superior_id in (".implode(',', $bussIds).")";
        $bussInfo2 = $this->orderMod->querySql($sql);
        foreach($bussInfo2 as $v) {
            $bussIds[] = $v['id'];
        }
        //分页定义
        $currentPage = !empty($p) ? $p : 1;
        $pagesize = 10; //每页显示的条数
        $start = ($currentPage - 1) * $pagesize;
        $end = $pagesize;
        $limit = '  limit  ' . $start . ',' . $end;
        $where = '';
        if (!empty($cat_2)) {
            $where = ' and c.room_type_id = ' . $cat_2;
        } elseif (!empty($cat_1)) {
            //获取对应二级业务类型id
            $sql = "select id from " . DB_PREFIX ."room_type where superior_id = {$cat_1} ";
            $cateInfo2 = $this->orderMod->querySql($sql);
            $cateids = array($cat_1);
            foreach ($cateInfo2 as $v) {
                $cateids[] = $v['id'];
            }
            $where .= " and c.room_type_id in (".implode(',', $cateids).")";
        }
        $sql = "SELECT c.id,c.add_time,c.money,rtl.type_name FROM bs_coupon as c LEFT JOIN bs_room_type_lang as rtl ON c.room_type_id =rtl.type_id
            WHERE c.type=2 and c.mark=1 and c.source=2 and c.room_type_id in (" . implode(',', $bussIds) . ") AND rtl.lang_id = {$this->defaulLang}  ". $where. "   order by c.id desc".$limit;
        $voucherData = $couponMod->querySql($sql);
        $this->assign('data', $voucherData);
        $this->display('customerOrder/pageVoucherList.html');
    }
    //电子劵总数
    public function voucherTotal() {
        //获取当前店铺业务类型
        $storebusinessMod = &m('storebusiness');
        $bussInfo1 = $storebusinessMod->getInfoByStoreid($this->storeId);
        $bussIds = array();
        foreach($bussInfo1 as $v) {
            $bussIds[] = $v['buss_id'];
        }
        $sql = "select id from " . DB_PREFIX ."room_type where superior_id in (".implode(',', $bussIds).")";
        $bussInfo2 = $this->orderMod->querySql($sql);
        foreach($bussInfo2 as $v) {
            $bussIds[] = $v['id'];
        }

        $couponMod=&m('coupon');
        $cat_1 = !empty($_REQUEST['cat_1']) ? htmlspecialchars(trim($_REQUEST['cat_1'])) : 0;
        $cat_2 = !empty($_REQUEST['cat_2']) ? htmlspecialchars(trim($_REQUEST['cat_2'])) : 0;
        $where = '';
        if (!empty($cat_2)) {
            $where = ' and room_type_id = ' . $cat_2;
        } elseif (!empty($cat_1)) {
            //获取对应二级业务类型id
            $sql = "select id from " . DB_PREFIX ."room_type where superior_id = {$cat_1} ";
            $cateInfo2 = $this->orderMod->querySql($sql);
            $cateids = array($cat_1);
            foreach ($cateInfo2 as $v) {
                $cateids[] = $v['id'];
            }
            $where .= " and room_type_id in (".implode(',', $cateids).")";
        }
        $sql="select count(*) as total from bs_coupon where type=2 and mark=1 and source=2 and room_type_id in (" . implode(',', $bussIds) . ") ".$where;
        $res = $couponMod->querySql($sql);
        $total = $res[0]['total'];
        $pagesize = 10;
        $totalpage = ceil($total / $pagesize);
        if (!empty($totalpage)) {
            echo json_encode(array('total' => $totalpage));
            exit;
        } else {
            echo json_encode(array('total' => 1));
            exit;
        }
    }

    //后台赠送兑换劵
    public function sendVoucher(){
        $userCouponMod=&m('userCoupon');
        $orderMod=&m('order');
        $userMod = &m('user');
        $couponMod=&m('coupon');//电子劵表
        $orderSn=!empty($_REQUEST['order_sn']) ? $_REQUEST['order_sn'] : '';
        $voucherId=!empty($_REQUEST['voucherId']) ? $_REQUEST['voucherId']: "";
        $orderInfo=$orderMod->getOne(array('cond'=>"`order_sn`='{$orderSn}' and order_state >=20 ",'fields'=>'buyer_id,order_id'));
        $userCouponLogData=$userCouponMod->getOne(array('cond'=>"`order_sn`='{$orderSn}'",'id'));
        $couponData=$couponMod->getOne(array('cond'=>"`id`='{$voucherId}'",'fields'=>'limit_times'));
        $limitTiems=$couponData['limit_times']*3600*24;
        $nowTime=time();
        if(empty($voucherId)){
            $this->setData("",0,'请选择兑换券');
        }
        if(empty($orderInfo)){
            $this->setData("",0,'该笔订单订单未付款');
        }
        if(!empty($userCouponLogData)){
            $this->setData("",0,'该笔订单已经赠送过劵了');
        }
        $userCouponData=array(
            "user_id"=>$orderInfo['buyer_id'],
            'c_id'=>$voucherId,
            'remark'=>"后台赠送",
            'add_time'=>time(),
            'source'=>1,
            'order_id'=>$orderInfo['order_id'],
            'order_sn'=>$orderSn,
            'start_time'=>$nowTime,
            'end_time'=>$nowTime+$limitTiems,
            'add_user'=>$this->storeUserId
        );
        $res=$userCouponMod->doInsert($userCouponData);

        $userMod->sendMessage($orderInfo['buyer_id']);
        if($res){
            $this->setData("",1,"赠送成功");
        }else{
            $this->setData("",0,'赠送失败');
        }
    }
    /**
     * 添加订单画像
     * @author tangp
     * @date 2019-03-13
     */
    public function confirmPortrait()
    {
        $userInfoMod = &m('userInfo');
        $select1 = !empty($_REQUEST['select1']) ? htmlspecialchars($_REQUEST['select1']) : '';
        $content1 = !empty($_REQUEST['content1']) ? htmlspecialchars($_REQUEST['content1']) : '';
        $select2 = !empty($_REQUEST['select2']) ? htmlspecialchars($_REQUEST['select2']) : '';
        $content2 = !empty($_REQUEST['content2']) ? htmlspecialchars($_REQUEST['content2']) : '';
        $select3 = !empty($_REQUEST['select3']) ? htmlspecialchars($_REQUEST['select3']) : '';
        $content3 = !empty($_REQUEST['content3']) ? htmlspecialchars($_REQUEST['content3']) : '';
        $select4 = !empty($_REQUEST['select4']) ? htmlspecialchars($_REQUEST['select4']) : '';
        $content4 = !empty($_REQUEST['content4']) ? htmlspecialchars($_REQUEST['content4']) : '';
        $select5 = !empty($_REQUEST['select5']) ? htmlspecialchars($_REQUEST['select5']) : '';
        $content5 = !empty($_REQUEST['content5']) ? htmlspecialchars($_REQUEST['content5']) : '';
        $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars($_REQUEST['order_sn']) : '';
        if ($select1 == '请选择'){
            $this->setData(array(),0,'请选择第一个类型！');
        }
        if (empty($content1)){
            $this->setData(array(),0,'请输入第一个内容！');
        }
        $sql = "SELECT * FROM bs_user_info WHERE order_sn = " . $order_sn;
        $userInfoData = $userInfoMod->querySql($sql);
        if (!$userInfoData){
            $array = array();
            $a1=array(
                "typename" => $select1,
                'content'  => $content1
            );
            array_push($array,$a1);
            if ($select2 !== '请选择' && $content2 !== ''){
                $a2=array(
                    "typename" => $select2,
                    'content'  => $content2
                );
                array_push($array,$a2);
            }
            if ($select3 !== '请选择' && $content3 !== ''){
                $a3=array(
                    "typename" => $select3,
                    'content'  => $content3
                );
                array_push($array,$a3);
            }
            if ($select4 !== '请选择' && $content4 !== ''){
                $a4=array(
                    "typename" => $select4,
                    'content'  => $content4
                );
                array_push($array,$a4);
            }
            if ($select5 !== '请选择' && $content5 !== ''){
                $a5=array(
                    "typename" => $select5,
                    'content'  => $content5
                );
                array_push($array,$a5);
            }
            $arrs = serialize($array);
            $data = array(
                'add_user' => $this->storeUserId,
                'order_sn' => $order_sn,
                'add_time' => time(),
                'content'  => $arrs,
            );
            $res = $userInfoMod->doInsert($data);
            if ($res){
                $this->setData(array(),1,'添加成功！');
            }else{
                $this->setData(array(),0,'添加失败！');
            }
        }else{
            $array = array();
            $a1=array(
                'typename' => $select1,
                'content'  => $content1
            );
            array_push($array,$a1);
            if ($select2 !== '请选择' && $content2 !== ''){
                $a2=array(
                    'typename' => $select2,
                    'content'  => $content2
                );
                array_push($array,$a2);
            }
            if ($select3 !== '请选择' && $content3 !== ''){
                $a3=array(
                    'typename' => $select3,
                    'content'  => $content3
                );
                array_push($array,$a3);
            }
            if ($select4 !== '请选择' && $content4 !== ''){
                $a4=array(
                    'typename' => $select4,
                    'content'  => $content4
                );
                array_push($array,$a4);
            }
            if ($select5 !== '请选择' && $content5 !== ''){
                $a5=array(
                    'typename' => $select5,
                    'content'  => $content5
                );
                array_push($array,$a5);
            }
            $arrs = serialize($array);
            $data = array(
                'add_user' => $this->storeUserId,
                'upd_time' => time(),
                'content'  => addslashes($arrs)
            );
            $sqls = "select id from bs_user_info where order_sn = " .$order_sn;
            $infoData = $userInfoMod->querySql($sqls);
            $res = $userInfoMod->doEdit($infoData[0]['id'],$data);
            if ($res){
                $this->setData(array(),1,'编辑成功！');
            }else{
                $this->setData(array(),0,'编辑失败！');
            }
        }
    }
    public function resetInfo()
    {
        $order_sn = $_REQUEST['order_sn'];

        $res = &m('userInfo')->doDelete(array("cond" => "order_sn = ".$order_sn));
        if ($res){
            $this->setData(array(),1,'重置成功！');
        }else{
            $this->setData(array(),0,'重置失败！');
        }
    }
    public function portrait()
    {
        $order_sn = !empty($_REQUEST['order_sn']) ? $_REQUEST['order_sn'] : '';
        $userInfoChapterMod = &m('userInfoChapter');
        $result = $userInfoChapterMod->getData(array(
            'cond'   => 'order_sn = ' .$order_sn
        ));
        $sql = "SELECT content FROM bs_user_info WHERE order_sn = {$order_sn}";
        $data = &m('userInfo')->querySql($sql);
        $arr = array();
        foreach ($data as $key => $val){
            $arr[] = unserialize($val['content']);
        }
//        echo '<pre>';print_r($arr);
        $this->assign('result',$result);
        $this->assign('arr',$arr);
        $this->assign('order_sn',$_REQUEST['order_sn']);
        $this->display('customerOrder/portrait.html');
    }

    public function doPortrait()
    {
        $person = !empty($_REQUEST['person']) ? htmlspecialchars(trim($_REQUEST['person'])) : '';
        $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars($_REQUEST['order_sn']) : '';
        $type = $_REQUEST['type'];
        $content = $_REQUEST['content'];
//        echo '<pre>';print_r($content);die;
        if (empty($person)){
            $this->setData(array(),0,'请填写顾客数！');
        }
        foreach ($_REQUEST['content'] as $key => $value){
            foreach($value as $k => $v){
                $name = htmlspecialchars(trim($v));
                if (empty($name)) {
                    $this->jsonError('请填写内容！');
                }
            }
        }
        $arr = array();
        $len = count($type);
        for ($i=0;$i<$len;$i++){
            $con = array();
            for ($j=0;$j<count($type[$i]);$j++){
                $con[] = array($type[$i][$j] =>$content[$i][$j]);
            }
            $con = serialize($con);
            $arr[] = array('content' => $con,'order_sn' => $order_sn,'add_user' => $this->storeUserId);
        }

        $sql = "SELECT * FROM bs_user_info WHERE order_sn = {$order_sn}";
        $data = &m('userInfo')->querySql($sql);
        if ($data){
            $ress  = &m('userInfo')->doDelete(array("cond"=>"`order_sn` = '{$order_sn}'"));
            $resss = &m('userInfoChapter')->doDelete(array("cond"=>"`order_sn` = '{$order_sn}'"));

            $result = &m('userInfoChapter')->doInsert(array(
                'order_sn' => $order_sn,
                'persons'  => $person
            ));

            foreach ($arr as $key => $value){
                $res = &m('userInfo')->doInsert($value);
            }

        }else{
            $result = &m('userInfoChapter')->doInsert(array(
                'order_sn' => $order_sn,
                'persons'  => $person
            ));
            foreach ($arr as $key => $value){
                $res = &m('userInfo')->doInsert($value);
            }
        }

        if ($result && $res){
            $this->setData(array(),1,'成功！');
        }
    }

}
