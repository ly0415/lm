<?php

/**
 * Created by PhpStorm.
 * User: jh
 * Date: 2019/2/19
 * Time: 14:46
 */
class ZzscriptApp extends BaseStoreApp
{
    private $defaultMod;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->defaultMod = &m('order');
    }

    /**
     * 订单拆表数据脚本
     */
    public function orderDataMove($orderSn = '')
    {
        $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars(trim($_REQUEST['store_id'])) : 0;//店铺id
        $page = !empty($_REQUEST['page']) ? htmlspecialchars(trim($_REQUEST['page'])) : 1;
        if (empty($orderSn)) {
            $orderSn = !empty($_REQUEST['order_sn']) ? htmlspecialchars(trim($_REQUEST['order_sn'])) : '';
        }
        if (empty($orderSn) && empty($store_id)) {
            echo '店铺id哪去了？';
            exit();
        }
//        $page = 1;
        $pagesize = 1000;
        $count = 0;
        $total = 0;
//        do {
        $start = ($page - 1) * $pagesize;
        if ($orderSn) {
            $sql = "select * from bs_order where order_sn in ({$orderSn}) order by order_id asc limit {$start},{$pagesize}";
        } else {
            $sql = "select * from bs_order where store_id = {$store_id} order by order_id asc limit {$start},{$pagesize}";
        }
//            $sql = "select * from bs_order where store_id = {$store_id} order by order_id asc limit {$start},{$pagesize}";
//            $sql = "select * from bs_order where store_id in (59,68,72,76,78,79,82,84,92,93,94) order by order_id asc limit {$start},{$pagesize}";
        $data = $this->defaultMod->querySql($sql);
        $count = count($data);
        foreach ($data as $k => $v) {
            $orderInfo = array();//order表插入数据
            $orderDetailInfo = array();//order_details表插入数据
            $orderRelationInfo = array();//order_relation表插入数据
            $userOrderInfo = array();//user_order表插入数据
            //order表
            $source = 0;//下单来源
            switch ($v['is_source']) {
                case 1:
                    $source = 2;
                    break;
                case 2:
                    $source = 3;
                    break;
                case 3:
                    $source = 1;
                    break;
                default:
                    break;
            }
            if (!$source && $v['is_old'] == 2) {
                $source = 3;
            }
            //订单状态
            if ($v['refund_state'] == 1) {
                $order_state = 60;//退款中
            } elseif($v['refund_state'] == 2) {
                $order_state = 70;//已退款
            } else {
                $order_state = !empty($v['order_state']) ? $v['order_state'] : 0;
            }
            $orderInfo['order_sn'] = !empty($v['order_sn']) ? $v['order_sn'] : '';
            $orderInfo['store_id'] = !empty($v['store_id']) ? $v['store_id'] : 0;
            $orderInfo['buyer_id'] = !empty($v['buyer_id']) ? $v['buyer_id'] : 0;
            $orderInfo['goods_amount'] = !empty($v['goods_amount']) ? $v['goods_amount'] : 0;
            $orderInfo['order_amount'] = !empty($v['order_amount']) ? $v['order_amount'] : 0;
            $orderInfo['order_state'] = $order_state;
            $orderInfo['sendout'] = !empty($v['sendout']) ? $v['sendout'] : '';
            $orderInfo['evaluation_state'] = !empty($v['evaluation_state']) ? $v['evaluation_state'] : 0;
            $orderInfo['source'] = $source;
            $orderInfo['add_time'] = !empty($v['add_time']) ? $v['add_time'] : 0;
            $orderInfo['mark'] = !empty($v['mark']) ? $v['mark'] : 0;
            $orderMod = &m("order" . $orderInfo['store_id']);
            $orderId = $orderMod->doInsert($orderInfo);
            if ($orderId) {
                //order_details表
                $orderDetailInfo['id'] = $orderId;
                $orderDetailInfo['order_id'] = $orderId;
                $orderDetailInfo['pay_sn'] = !empty($v['pay_sn']) ? $v['pay_sn'] : '';
                $orderDetailInfo['discount_num'] = !empty($v['discount_num']) ? $v['discount_num'] : 0;
                $orderDetailInfo['discount'] = !empty($v['discount']) ? $v['discount'] : 0;
                $orderDetailInfo['fx_user_id'] = !empty($v['fx_user_id']) ? $v['fx_user_id'] : 0;
//                $orderDetailInfo['fx_money'] = !empty($v['pd_amount']) ? $v['pd_amount'] : 0;
                $orderDetailInfo['point_discount'] = !empty($v['pd_amount']) ? $v['pd_amount'] : 0;
                $orderDetailInfo['coupon_discount'] = !empty($v['cp_amount']) ? $v['cp_amount'] : 0;
                $orderDetailInfo['shipping_fee'] = !empty($v['shipping_fee']) ? $v['shipping_fee'] : 0;
                $orderDetailInfo['seller_msg'] = !empty($v['seller_msg']) ? $v['seller_msg'] : '';
                $orderDetailInfo['warning_tone'] = !empty($v['warning_tone']) ? $v['warning_tone'] : 1;
                $orderDetailInfo['sendout_time'] = !empty($v['pei_time']) ? $v['pei_time'] : 0;
                $orderDetailInfo['number_order'] = !empty($v['number_order']) ? $v['number_order'] : '';
                $orderDetailInfo['clickandview'] = !empty($v['clickandview']) ? $v['clickandview'] : 1;
                if ($v['is_old'] == 2) {
                    $orderDetailInfo['valet_order_user_id'] = !empty($v['singleperson']) ? $v['singleperson'] : 0;
                    $orderDetailInfo['valet_order_time'] = !empty($v['add_time']) ? $v['add_time'] : 0;
                }
                $orderDetailsMod =& m("orderDetails" . $orderInfo['store_id']);
                $orderDetailsMod->doInsert($orderDetailInfo);
                //order_relation表
                $payment_type = 0;
                switch ($v['payment_code']) {
                    case 'aliPay':
                        $payment_type = 1;
                        break;
                    case 'wxpay':
                        $payment_type = 2;
                        break;
                    case '余额支付':
                        $payment_type = 3;
                        break;
                    case '线下打款':
                    case '现金付款':
                        $payment_type = 4;
                        break;
                    case '免费兑换':
                        $payment_type = 5;
                        break;
                    default:
                        break;
                }
                $orderRelationInfo['id'] = $orderId;
                $orderRelationInfo['order_id'] = $orderId;
//                $orderRelationInfo['cancel_time'] = !empty($v['order_sn']) ? $v['order_sn'] : '';
                $orderRelationInfo['payment_type'] = $payment_type;
                $orderRelationInfo['payment_source'] = !empty($v['source_id']) ? $v['source_id'] : '';
                $orderRelationInfo['payment_time'] = !empty($v['payment_time']) ? $v['payment_time'] : 0;
//                $orderRelationInfo['ship_time'] = !empty($v['order_sn']) ? $v['order_sn'] : 0;
                $orderRelationInfo['delivery_time'] = !empty($v['pei_time']) ? $v['pei_time'] : 0;
                $orderRelationInfo['receipt_time'] = !empty($v['finished_time']) ? $v['finished_time'] : 0;
                $orderRelationInfo['receipt_time_difference'] = $orderRelationInfo['receipt_time'] - $orderRelationInfo['payment_time'];
                $orderRelationInfo['receipt_time_difference'] = $orderRelationInfo['receipt_time_difference'] > 0 ? $orderRelationInfo['receipt_time_difference'] : 0;
//                $orderRelationInfo['receipt_source'] = !empty($v['order_sn']) ? $v['order_sn'] : '';
//                $orderRelationInfo['refund_time'] = !empty($v['order_sn']) ? $v['order_sn'] : '';
//                $orderRelationInfo['refund_source'] = !empty($v['order_sn']) ? $v['order_sn'] : '';
//                $orderRelationInfo['comment_time'] = !empty($v['order_sn']) ? $v['order_sn'] : '';
//                $orderRelationInfo['comment_source'] = !empty($v['order_sn']) ? $v['order_sn'] : '';
                $orderRelationMod =& m("orderRelation" . $orderInfo['store_id']);
                $orderRelationMod->doInsert($orderRelationInfo);
                //user_order表
                $userOrderInfo['user_id'] = !empty($v['buyer_id']) ? $v['buyer_id'] : 0;
                $userOrderInfo['store_id'] = !empty($v['store_id']) ? $v['store_id'] : 0;
                $userOrderInfo['order_sn'] = !empty($v['order_sn']) ? $v['order_sn'] : '';
                $userOrderInfo['pay_money'] = !empty($v['order_amount']) ? $v['order_amount'] : 0;
                $userOrderInfo['add_time'] = !empty($v['add_time']) ? $v['add_time'] : 0;
                $userOrderMod =& m('userOrder');
                $userOrderMod->doInsert($userOrderInfo);
            }
        }
        $total += $count;
        $page++;
//        } while ($count >= $pagesize);
        echo 'success_' . $total;
    }

    /**
     * 老order表pd_amount字段导入到新order_details表
     */
    public function pdAmountMove()
    {
        $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars(trim($_REQUEST['store_id'])) : 0;//店铺id
        if (empty($store_id)) {
            echo '店铺id哪去了？';
            exit();
        }
        $page = 1;
        $pagesize = 5000;
        $count = 0;
        $total = 0;
        $newOrderName = "bs_order_{$store_id}";
        $orderDetailsMod =& m("orderDetails" . $store_id);
        do {
            $start = ($page - 1) * $pagesize;
            $sql = "select a.order_sn,a.pd_amount,b.id as new_order_id from bs_order as a left join {$newOrderName} as b on a.order_sn = b.order_sn where a.store_id = {$store_id} and b.store_id = {$store_id} order by a.order_id asc limit {$start},{$pagesize}";
//            $sql = "select * from bs_order where store_id in (59,68,72,76,78,79,82,84,92,93,94) order by order_id asc limit {$start},{$pagesize}";
            $data = $this->defaultMod->querySql($sql);
            $count = count($data);
            foreach ($data as $k => $v) {
                //order_details表
                $orderDetailInfo = array();//order_details表插入数据
                $orderDetailInfo['point_discount'] = !empty($v['pd_amount']) ? $v['pd_amount'] : 0;
                $orderDetailsMod->doEditSpec(array('order_id' => $v['new_order_id']), $orderDetailInfo);
            }
            $total += $count;
            $page++;
        } while ($count >= $pagesize);
        echo 'success_' . $total;
    }

    /**
     * 老order表数据导入到新order_details表
     */
    public function orderDetailsChange()
    {
        $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars(trim($_REQUEST['store_id'])) : 0;//店铺id
        if (empty($store_id)) {
            echo '店铺id哪去了？';
            exit();
        }
        $page = 1;
        $pagesize = 5000;
        $count = 0;
        $total = 0;
        $newOrderName = "bs_order_{$store_id}";
        $newOrderDetailsName = "bs_order_details_{$store_id}";
        $orderDetailsMod =& m("orderDetails" . $store_id);
        do {
            $start = ($page - 1) * $pagesize;
            $sql = "select a.pay_sn,a.discount_num,a.discount,a.fx_user_id,a.pd_amount,a.cp_amount,a.shipping_fee,a.seller_msg,a.warning_tone,a.singleperson,a.add_time,b.id as new_order_id,c.id as order_details_id from bs_order as a left join {$newOrderName} as b on a.order_sn = b.order_sn left join {$newOrderDetailsName} as c on b.id = c.order_id where a.store_id = {$store_id} and b.store_id = {$store_id} order by b.id desc limit {$start},{$pagesize}";
//            $sql = "select * from bs_order where store_id in (59,68,72,76,78,79,82,84,92,93,94) order by order_id asc limit {$start},{$pagesize}";
            $data = $this->defaultMod->querySql($sql);
            $count = count($data);
            foreach ($data as $k => $v) {
                //order_details表
                $discount_num = 0;
                if ($v['discount'] > 0) {
                    $discount_num = !empty($v['discount_num']) ? $v['discount_num'] : 0;
                }
                $orderDetailInfo = array();//order_details表插入数据
                $orderDetailInfo['id'] = !empty($v['new_order_id']) ? $v['new_order_id'] : 0;
                $orderDetailInfo['order_id'] = !empty($v['new_order_id']) ? $v['new_order_id'] : 0;
                $orderDetailInfo['pay_sn'] = !empty($v['pay_sn']) ? $v['pay_sn'] : '';
                $orderDetailInfo['discount_num'] = $discount_num;
                $orderDetailInfo['discount'] = !empty($v['discount']) ? $v['discount'] : 0;
                $orderDetailInfo['fx_user_id'] = !empty($v['fx_user_id']) ? $v['fx_user_id'] : 0;
//                $orderDetailInfo['fx_money'] = !empty($v['pd_amount']) ? $v['pd_amount'] : 0;
                $orderDetailInfo['point_discount'] = !empty($v['pd_amount']) ? $v['pd_amount'] : 0;
                $orderDetailInfo['coupon_discount'] = !empty($v['cp_amount']) ? $v['cp_amount'] : 0;
                $orderDetailInfo['shipping_fee'] = !empty($v['shipping_fee']) ? $v['shipping_fee'] : 0;
                $orderDetailInfo['seller_msg'] = !empty($v['seller_msg']) ? $v['seller_msg'] : '';
                $orderDetailInfo['warning_tone'] = !empty($v['warning_tone']) ? $v['warning_tone'] : 1;
                if ($v['is_old'] == 2) {
                    $orderDetailInfo['valet_order_user_id'] = !empty($v['singleperson']) ? $v['singleperson'] : 0;
                    $orderDetailInfo['valet_order_time'] = !empty($v['add_time']) ? $v['add_time'] : 0;
                }
                if ($v['order_details_id']) {
                    $orderDetailsMod->doEditSpec(array('order_id' => $v['new_order_id']), $orderDetailInfo);
                } else {
                    $orderDetailsMod->doInsert($orderDetailInfo);
                }
            }
            $total += $count;
            $page++;
        } while ($count >= $pagesize);
        echo 'success_' . $total;
    }

    /**
     * 老order表singleperson，add_time字段导入到新order_details表
     */
    public function seederOrder()
    {
        $store_id = $_REQUEST['store_id'];

        if (empty($store_id) || !is_numeric($store_id)) {
            echo 'store_id 必传';
            exit;
        }

        $orderTable = 'bs_order_' . $store_id;
        $orderDetailsMod =& m("orderDetails" . $store_id);

        $page = 1;
        $pagesize = 500;
        $total = 0;
        do {
            $start = ($page - 1) * $pagesize;

            $orderMod = &m('order');
            $sql = <<<SQL
select o.singleperson,o.add_time,t.id as new_order_id from 
bs_order as o left join {$orderTable} as t on o.order_sn = t.order_sn 
where o.store_id = {$store_id} and o.is_old = 2 
order by o.order_id asc limit {$start},{$pagesize}
SQL;
            $orders = $orderMod->querySql($sql);
            $count = count($orders);
            foreach ($orders as $k => $v) {
                //order_details表
                if ($v['new_order_id']) {
                    $orderDetailInfo['key'] = 'order_id';
                    $orderDetailInfo['valet_order_user_id'] = !empty($v['singleperson']) ? $v['singleperson'] : 0;
                    $orderDetailInfo['valet_order_time'] = !empty($v['add_time']) ? $v['add_time'] : 0;
                    $orderDetailsMod->doEdit($v['new_order_id'], $orderDetailInfo);
                }
            }
            $total += $count;
            $page++;
        } while ($count >= $pagesize);

        echo 'success_' . $total;
    }


    //比较2表的差异
    public function  differenceOrder()
    {
        $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars(trim($_REQUEST['store_id'])) : 0;//店铺id
        $orderMod =& m('order');
        $sql = "select a.order_sn from bs_order as a left join bs_order_{$store_id} as b on a.order_sn = b.order_sn where a.store_id={$store_id} and b.id is null ";
        $orders = $orderMod->querySql($sql);
        foreach ($orders as $k => $v) {
            $orderRes[] = $v['order_sn'];
        }
        $orderStr = implode(',', $orderRes);
        $res = $this->orderDataMove($orderStr);
    }

    /**
     * 刷order_details表的coupon_discount数据
     * @author tangp
     * @date 2019-02-21
     */
    public function synchronize()
    {
        $store_id = $_REQUEST['store_id'];
        $orderMod = &m('order');
        $sql = "SELECT os.id,o.cp_amount,o.order_sn,o.order_amount,cl.coupon_id FROM bs_order as o 
                LEFT JOIN bs_coupon_log as cl on o.order_id = cl.order_id
                LEFT JOIN bs_order_{$store_id} as os on os.order_sn = o.order_sn
                WHERE o.store_id=" . $store_id;
//            echo $sql;die;
        $res = $orderMod->querySql($sql);

        foreach ($res as $key => $val) {
            if (!$val['coupon_id']) unset($res[$key]);
        }
//        dd($res);
        foreach ($res as $key => $val) {
            $sqls = "UPDATE bs_order_details_{$store_id} SET coupon_discount = {$val['cp_amount']} WHERE order_id=" . $val['id'];

            $result = $orderMod->doEditSql($sqls);
        }

        if ($result) {
            echo '成功';
        }

    }

    /**
     * jh
     * 补录订单刷数据
     */
    public function buLuOrder()
    {
        $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars(trim($_REQUEST['store_id'])) : 0;//店铺id
        if (empty($store_id)) {
            echo '店铺id哪去了？';
            exit();
        }
        $page = 1;
        $pagesize = 5000;
        $count = 0;
        $total = 0;
        $newOrderName = "bs_order_{$store_id}";
        $oldOrderMod =& m("order");
        $orderMod =& m("order" . $store_id);
        $orderDetailsMod =& m("orderDetails" . $store_id);
        do {
            $start = ($page - 1) * $pagesize;
            $sql = "select a.order_id,a.buyer_address,b.id as new_order_id from bs_order as a left join {$newOrderName} as b on a.order_sn = b.order_sn where a.is_source = 2 and a.source_id != '1758421' and a.store_id = {$store_id} and b.store_id = {$store_id} order by a.order_id asc limit {$start},{$pagesize}";
//            $sql = "select * from bs_order where store_id in (59,68,72,76,78,79,82,84,92,93,94) order by order_id asc limit {$start},{$pagesize}";
            $data = $this->defaultMod->querySql($sql);
            $count = count($data);
            foreach ($data as $k => $v) {
                //老order表
                $oldOrderInfo = array();//order表插入数据
                $oldOrderInfo['sendout'] = '';
                $oldOrderInfo['pei_time'] = 0;
                $oldOrderMod->doEditSpec(array('order_id' => $v['order_id']), $oldOrderInfo);
                //新order表
                $orderInfo = array();//order表插入数据
                $orderInfo['sendout'] = '';
                $orderMod->doEdit($v['new_order_id'], $orderInfo);
                //order_details表
                $orderDetailInfo = array();//order_details表插入数据
                $orderDetailInfo['delivery'] = !empty($v['buyer_address']) ? $v['buyer_address'] : '';
                $orderDetailInfo['sendout_time'] = 0;
                $orderDetailsMod->doEditSpec(array('order_id' => $v['new_order_id']), $orderDetailInfo);
            }
            $total += $count;
            $page++;
        } while ($count >= $pagesize);
        echo 'success_' . $total;
    }

    /**
     * 订单表数据差异维护
     */
    public function orderDataCorrect()
    {
        $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars(trim($_REQUEST['store_id'])) : 0;//店铺id
        if (empty($store_id)) {
            echo '店铺id哪去了？';
            exit();
        }
        $orderDetailsMod =& m("orderDetails" . $store_id);
        $orderRelationMod =& m("orderRelation" . $store_id);
        $userOrderMod =& m('userOrder');
        //清除details多余数据
        $sql = "select a.id from bs_order_details_{$store_id} as a left join bs_order_{$store_id} as b on a.order_id = b.id where a.order_id = 0 or b.id is null ";
        $detailsExists = $this->defaultMod->querySql($sql);
        if (!empty($detailsExists)) {
            $detailsIds = array_column($detailsExists, 'id');
            $orderDetailsMod->doDrop(implode(',', $detailsIds));
        }
        //清除relation多余数据
        $sql = "select a.id from bs_order_relation_{$store_id} as a left join bs_order_{$store_id} as b on a.order_id = b.id where a.order_id = 0 or b.id is null ";
        $relationExists = $this->defaultMod->querySql($sql);
        if (!empty($relationExists)) {
            $relationIds = array_column($relationExists, 'id');
            $orderRelationMod->doDrop(implode(',', $relationIds));
        }
        //重置details表自增id，让它保持和order_id一致，然后重置自动增量
        $sql1 = "UPDATE bs_order_details_{$store_id} SET id = order_id WHERE id > order_id ORDER BY id ASC";
        $sql2 = "UPDATE bs_order_details_{$store_id} SET id = order_id WHERE id < order_id ORDER BY id DESC";
        $sql3 = "ALTER TABLE bs_order_details_{$store_id} AUTO_INCREMENT = 1";
        $orderDetailsMod->doEditSql($sql1);
        $orderDetailsMod->doEditSql($sql2);
        $orderDetailsMod->doEditSql($sql3);//别怕，如果表里有数据，自动增量会往后顺延，比最大的id大1
        //重置relation表自增id，让它保持和order_id一致，然后重置自动增量
        $sql1 = "UPDATE bs_order_relation_{$store_id} SET id = order_id WHERE id > order_id ORDER BY id ASC";
        $sql2 = "UPDATE bs_order_relation_{$store_id} SET id = order_id WHERE id < order_id ORDER BY id DESC";
        $sql3 = "ALTER TABLE bs_order_relation_{$store_id} AUTO_INCREMENT = 1";
        $orderRelationMod->doEditSql($sql1);
        $orderRelationMod->doEditSql($sql2);
        $orderRelationMod->doEditSql($sql3);//别怕，如果表里有数据，自动增量会往后顺延，比最大的id大1
        //获取主表有而副表没有的记录
        $sql = "select a.*,b.id as new_orderid,c.id as details_id,d.id as relation_id from bs_order as a " .
            " left join bs_order_{$store_id} as b on a.order_sn = b.order_sn " .
            " left join bs_order_details_{$store_id} as c on b.id = c.order_id " .
            " left join bs_order_relation_{$store_id} as d on b.id = d.order_id " .
            " where a.store_id = {$store_id} and b.id > 0 and (c.id is null or d.id is null) order by a.order_id asc ";
        $info1 = $this->defaultMod->querySql($sql);
        //获取旧表有而新表没有的记录
        $sql = "select a.* from bs_order as a " .
            " left join bs_order_{$store_id} as b on a.order_sn = b.order_sn " .
            " where a.store_id = {$store_id} and b.id is null order by a.order_id asc ";
        $info2 = $this->defaultMod->querySql($sql);
        $data = array_merge($info1, $info2);
        $count = count($data);
        foreach ($data as $k => $v) {
            $orderInfo = array();//order表插入数据
            $orderDetailInfo = array();//order_details表插入数据
            $orderRelationInfo = array();//order_relation表插入数据
            $userOrderInfo = array();//user_order表插入数据
            if (isset($v['new_orderid'])) {
                $orderId = $v['new_orderid'];
            } else {
                //order表
                $source = 0;//下单来源
                switch ($v['is_source']) {
                    case 1:
                        $source = 2;
                        break;
                    case 2:
                        $source = 3;
                        break;
                    case 3:
                        $source = 1;
                        break;
                    default:
                        break;
                }
                if (!$source && $v['is_old'] == 2) {
                    $source = 3;
                }
                //订单状态
                if ($v['refund_state'] == 1) {
                    $order_state = 60;//退款中
                } elseif($v['refund_state'] == 2) {
                    $order_state = 70;//已退款
                } else {
                    $order_state = !empty($v['order_state']) ? $v['order_state'] : 0;
                }
                $orderInfo['order_sn'] = !empty($v['order_sn']) ? $v['order_sn'] : '';
                $orderInfo['store_id'] = !empty($v['store_id']) ? $v['store_id'] : 0;
                $orderInfo['buyer_id'] = !empty($v['buyer_id']) ? $v['buyer_id'] : 0;
                $orderInfo['goods_amount'] = !empty($v['goods_amount']) ? $v['goods_amount'] : 0;
                $orderInfo['order_amount'] = !empty($v['order_amount']) ? $v['order_amount'] : 0;
                $orderInfo['order_state'] = $order_state;
                $orderInfo['sendout'] = !empty($v['sendout']) ? $v['sendout'] : '';
                $orderInfo['evaluation_state'] = !empty($v['evaluation_state']) ? $v['evaluation_state'] : 0;
                $orderInfo['source'] = $source;
                $orderInfo['add_time'] = !empty($v['add_time']) ? $v['add_time'] : 0;
                $orderInfo['mark'] = !empty($v['mark']) ? $v['mark'] : 0;
                $orderMod = &m("order" . $orderInfo['store_id']);
                $orderId = $orderMod->doInsert($orderInfo);
                //user_order表
                $userOrderInfo['user_id'] = !empty($v['buyer_id']) ? $v['buyer_id'] : 0;
                $userOrderInfo['store_id'] = !empty($v['store_id']) ? $v['store_id'] : 0;
                $userOrderInfo['order_sn'] = !empty($v['order_sn']) ? $v['order_sn'] : '';
                $userOrderInfo['pay_money'] = !empty($v['order_amount']) ? $v['order_amount'] : 0;
                $userOrderInfo['add_time'] = !empty($v['add_time']) ? $v['add_time'] : 0;
                $userOrderMod->doInsert($userOrderInfo);
            }
            if ($orderId) {
                //order_details表
                if (!isset($v['details_id']) || empty($v['details_id'])) {
                    $orderDetailInfo['id'] = $orderId;
                    $orderDetailInfo['order_id'] = $orderId;
                    $orderDetailInfo['pay_sn'] = !empty($v['pay_sn']) ? $v['pay_sn'] : '';
                    $orderDetailInfo['discount_num'] = !empty($v['discount_num']) ? $v['discount_num'] : 0;
                    $orderDetailInfo['discount'] = !empty($v['discount']) ? $v['discount'] : 0;
                    $orderDetailInfo['fx_user_id'] = !empty($v['fx_user_id']) ? $v['fx_user_id'] : 0;
//                $orderDetailInfo['fx_money'] = !empty($v['pd_amount']) ? $v['pd_amount'] : 0;
                    $orderDetailInfo['point_discount'] = !empty($v['pd_amount']) ? $v['pd_amount'] : 0;
                    $orderDetailInfo['coupon_discount'] = !empty($v['cp_amount']) ? $v['cp_amount'] : 0;
                    $orderDetailInfo['shipping_fee'] = !empty($v['shipping_fee']) ? $v['shipping_fee'] : 0;
                    $orderDetailInfo['seller_msg'] = !empty($v['seller_msg']) ? $v['seller_msg'] : '';
                    $orderDetailInfo['warning_tone'] = !empty($v['warning_tone']) ? $v['warning_tone'] : 1;
                    $orderDetailInfo['sendout_time'] = !empty($v['pei_time']) ? $v['pei_time'] : 0;
                    $orderDetailInfo['number_order'] = !empty($v['number_order']) ? $v['number_order'] : '';
                    $orderDetailInfo['clickandview'] = !empty($v['clickandview']) ? $v['clickandview'] : 1;
                    if ($v['is_old'] == 2) {
                        $orderDetailInfo['valet_order_user_id'] = !empty($v['singleperson']) ? $v['singleperson'] : 0;
                        $orderDetailInfo['valet_order_time'] = !empty($v['add_time']) ? $v['add_time'] : 0;
                    }
                    $orderDetailsMod->doInsert($orderDetailInfo);
                }
                //order_relation表
                if (!isset($v['relation_id']) || empty($v['relation_id'])) {
                    $payment_type = 0;
                    switch ($v['payment_code']) {
                        case 'aliPay':
                            $payment_type = 1;
                            break;
                        case 'wxpay':
                            $payment_type = 2;
                            break;
                        case '余额支付':
                            $payment_type = 3;
                            break;
                        case '线下打款':
                        case '现金付款':
                            $payment_type = 4;
                            break;
                        case '免费兑换':
                            $payment_type = 5;
                            break;
                        default:
                            break;
                    }
                    $orderRelationInfo['id'] = $orderId;
                    $orderRelationInfo['order_id'] = $orderId;
//                $orderRelationInfo['cancel_time'] = !empty($v['order_sn']) ? $v['order_sn'] : '';
                    $orderRelationInfo['payment_type'] = $payment_type;
                    $orderRelationInfo['payment_source'] = !empty($v['source_id']) ? $v['source_id'] : '';
                    $orderRelationInfo['payment_time'] = !empty($v['payment_time']) ? $v['payment_time'] : 0;
//                $orderRelationInfo['ship_time'] = !empty($v['order_sn']) ? $v['order_sn'] : 0;
                    $orderRelationInfo['delivery_time'] = !empty($v['pei_time']) ? $v['pei_time'] : 0;
                    $orderRelationInfo['receipt_time'] = !empty($v['finished_time']) ? $v['finished_time'] : 0;
                    $orderRelationInfo['receipt_time_difference'] = $orderRelationInfo['receipt_time'] - $orderRelationInfo['payment_time'];
                    $orderRelationInfo['receipt_time_difference'] = $orderRelationInfo['receipt_time_difference'] > 0 ? $orderRelationInfo['receipt_time_difference'] : 0;
//                $orderRelationInfo['receipt_source'] = !empty($v['order_sn']) ? $v['order_sn'] : '';
//                $orderRelationInfo['refund_time'] = !empty($v['order_sn']) ? $v['order_sn'] : '';
//                $orderRelationInfo['refund_source'] = !empty($v['order_sn']) ? $v['order_sn'] : '';
//                $orderRelationInfo['comment_time'] = !empty($v['order_sn']) ? $v['order_sn'] : '';
//                $orderRelationInfo['comment_source'] = !empty($v['order_sn']) ? $v['order_sn'] : '';
                    $orderRelationMod->doInsert($orderRelationInfo);
                }
            }
        }
        $sql1 = "select count(*) as num from bs_order where store_id = {$store_id}";
        $sql2 = "select count(*) as num from bs_order_{$store_id}";
        $sql3 = "select count(*) as num from bs_order_details_{$store_id}";
        $sql4 = "select count(*) as num from bs_order_relation_{$store_id}";
        $info1 = $this->defaultMod->querySql($sql1);
        $info2 = $this->defaultMod->querySql($sql2);
        $info3 = $this->defaultMod->querySql($sql3);
        $info4 = $this->defaultMod->querySql($sql4);
        echo 'success::' . $count . "<br>";
        echo $info1[0]['num'] . "<br>";
        echo $info2[0]['num'] . "<br>";
        echo $info3[0]['num'] . "<br>";
        echo $info4[0]['num'] . "<br>";
    }
}