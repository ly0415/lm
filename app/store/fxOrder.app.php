<?php

/**
 * 商铺订单查看
 * @author wanyan
 * @date 2017-11-17
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class fxOrderApp extends BaseStoreApp
{
    private $lang_id;
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : 0;
    }


    public function orderlist()
    {
        $this->load($this->lang_id, 'admin/admin');
        $a = $this->langData;
        $goods_name = !empty($_REQUEST['goods_name']) ? htmlspecialchars(trim(addslashes($_REQUEST['goods_name']))) : '';
        $store_id = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : '';
        $area_id = !empty($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : '';
        $payment_code = !empty($_REQUEST['payment_code']) ? htmlspecialchars(trim($_REQUEST['payment_code'])) : '';
        $buyer_email = !empty($_REQUEST['buyer_email']) ? htmlspecialchars(trim($_REQUEST['buyer_email'])) : '';
        $buyer_name = !empty($_REQUEST['buyer_name']) ? htmlspecialchars(trim($_REQUEST['buyer_name'])) : '';
        $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars(trim($_REQUEST['order_sn'])) : '';
        $shipping_code = !empty($_REQUEST['shipping_code']) ? htmlspecialchars(trim($_REQUEST['shipping_code'])) : '';
        $store = !empty($_REQUEST['store']) ? htmlspecialchars(trim($_REQUEST['store'])) : '';
        $state = !is_null($_REQUEST['state']) ? htmlspecialchars(trim($_REQUEST['state'])) : 'month_this';
        $username = !empty($_REQUEST['username']) ? htmlspecialchars(trim($_REQUEST['username'])) :'';
        $source = !empty($_REQUEST['source']) ? intval($_REQUEST['source']) : '';
        $orderState = !is_null($_REQUEST['order_state']) ? intval($_REQUEST['order_state']) : 99;
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $where = '  where  o.store_id='.$this->storeId;
        if (!empty($goods_name)) {
            $where .= " and og.goods_name like '%" . $goods_name . "%'";
        }
        if (!empty($payment_code)) {
            $where .= " and o.payment_code like '%" . $payment_code . "%'";
        }
        if (!empty($buyer_email)) {
            $where .= " and o.buyer_email like '%" . $buyer_email . "%'";
        }
        if (!empty($buyer_name)) {
            $where .= " and o.buyer_name like '%" . $buyer_name . "%'";
        }
        if (!empty($order_sn)) {
            $where .= " and o.order_sn like '%" . $order_sn . "%'";
        }
//        if (!empty($store_id)) {
//            $where .= " and o.store_id like '%" . $store_id . "%'";
//        }
//        if (!empty($area_id)) {
//            $where .= " and fo.store_cate =000000" . $area_id ;
//        }
        if (!empty($shipping_code)) {
            $where .= " and o.shipping_code like '%" . $shipping_code . "%'";
        }
        if (!empty($username)){
            $where .= " and fu.real_name like '%" . $username . "%'";
        }
        if (!empty($source)){
            $where .= " and fo.source =" . $source;
        }
        if ($orderState != 99){
            $where .= " and o.order_state =" . $orderState;
        }
        $this->assign("p", $p);
        $this->assign("state", $state);
        $this->assign('goods_name', $goods_name);
        $this->assign('payment_code', $payment_code);
        $this->assign('buyer_email', $buyer_email);
        $this->assign('buyer_name', $buyer_name);
        $this->assign('order_sn', $order_sn);
        $this->assign('shipping_code', $shipping_code);
        $this->assign('username',$username);
        $this->assign('store_id', $store_id);
        $this->assign('area_id', $area_id);
        $this->assign('source', $source);
        $this->assign('orderState', $orderState);
        $sql = "SELECT fu.real_name as username,fu.discount as ddiscount,o.delivery_status,fo.source,fo.add_user,fo.order_sn,o.add_time,o.order_state,o.goods_amount,o.order_amount, fo.source as order_source FROM bs_fx_order AS fo
              LEFT JOIN bs_order AS o ON fo.order_id = o.order_id
              LEFT JOIN bs_fx_user AS fu ON fu.id = fo.fx_user_id
              LEFT JOIN bs_user AS u ON fo.user_id = u.id {$where} order by o.order_id desc " ;

//        echo $sql;die;
        $orderGoodsMod = &m('orderGoods');
        $giftGood = &m('giftGood');
        $fxOrder = &m('fxOrder');
        $result = $fxOrder->querySqlPageData($sql);
        $this->assign('sourceList', $fxOrder->source);
        $data = $result['list'];
        foreach ($data as $k => $v){
            $cond = array(
                'cond' => "order_id =".$v['order_sn']
            );
            $list = $orderGoodsMod->getData($cond);
            $data[$k]['goods_list'] = $list;
            //赠品
            $sql = "select * from " . DB_PREFIX . "gift_goods where id=" . $v['gift_id'];
            $res = $giftGood->querySql($sql);
            $data[$k]['gift'] = $res;
            $data[$k]['source_name'] = $fxOrder->source[$v['order_source']];
        }
        $OrderStatus = array(
            "0" => $a['Canceled'],
            "10" => $a['Unpaid'],
            "20" => $a['payment'],
            "30" => $a['Shipped'],
            "40" => $a['Shipped_1'],
            "50" => $a['Receivedgoods'],
        );
        $this->assign('statusList', $OrderStatus);
        $this->assign('page_html',$result['ph']);
        $this->assign('p',$p);
        $this->assign('data',$data);
        $this->display('fxOrder/orderList.html');
    }
    /**
     * 导出分销订单
     * @author gao
     * @date 2018-01-04
     */
    public function exportFxOrder()
    {
        $fxOrderMod=&m("fxOrder");
        $fxUserMod=&m("fxuser");
        $fxRuleMod=&m("fxrule");
        $sql="SELECT fo.order_sn,fo.pay_money,fo.fx_money,fo.user_id,fo.fx_user_id,fo.rule_id,fu.level,fu.real_name,fu.phone,fu.discount,o.goods_amount FROM ".DB_PREFIX."fx_order AS fo
         LEFT JOIN ".DB_PREFIX."fx_user AS fu ON fo.fx_user_id=fu.id
         LEFT JOIN ".DB_PREFIX."order AS o ON o.order_sn=fo.order_sn 
        WHERE     fo.store_id = 78 ".
        " order by fu.real_name,fo.add_time";
        $fxData=$fxUserMod->querySql($sql);
        foreach($fxData as $k=>$v){
            $fxData[$k]['fxRuleData']=$fxRuleMod->getOne(array('cond'=>"`id`= '{$v['rule_id']}'",'fields'=>"lev1_prop,lev2_prop,lev3_prop"));
            $fxData[$k]['bountyMoney']=number_format($fxData[$k]['fxRuleData']['lev'.$v['level'].'_prop']*$v['pay_money']/100,2);
            $fxData[$k]['discountMoney']=number_format($v['goods_amount']*$v['discount']/100,2);
        }
        $fileName = "分销订单.xls";
        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename={$fileName}");
        echo "<table border='1'>
            <tr>
                <th>" . iconv("UTF-8", "GB2312//IGNORE", "序号") . "</th>
                <th>" . iconv("UTF-8", "GB2312//IGNORE", "会员名称") . "</th>
                <th>" . iconv("UTF-8", "GB2312//IGNORE", "订单号") . "</th>
                <th>" . iconv("UTF-8", "GB2312//IGNORE", "手机号") . "</th>
                <th>" . iconv("UTF-8", "GB2312//IGNORE", "本单佣金") . "</th>
                <th>" . iconv("UTF-8", "GB2312//IGNORE", "本单优惠") . "</th>
            </tr>";
        foreach ($fxData as $k => $v) {
            echo "<tr>";
            echo "<td>" . iconv("UTF-8", "GB2312//IGNORE", $k + 1) . "</td>";
            echo "<td>" . iconv("UTF-8", "GB2312//IGNORE", $v['real_name']) . "</td>";
            echo "<td style='vnd.ms-excel.numberformat:@'>" . iconv("UTF-8", "GB2312//IGNORE", $v['order_sn']) . "</td>";
            echo "<td>" . iconv("UTF-8", "GB2312//IGNORE", $v['phone']) . "</td>";
            echo "<td>" . iconv("UTF-8", "GB2312//IGNORE", $v['bountyMoney']) . "</td>";
            echo "<td>" . iconv("UTF-8", "GB2312//IGNORE", $v['discountMoney']) . "</td>";
            echo "</tr>";
        }
    }



    }
