<?php

/**
 * 商家后台
 * @author lee
 * @date 2017/07/19
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class orderReportApp extends BaseStoreApp
{

    private $orderMod;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->orderMod = &m('order');
    }

    public function index()
    {
        // 获取今年第一天和最后一天
        $first_time = strtotime(date('Y-01-01'));
        $end_time = strtotime(date('Y-12-31'));
        //删选条件
        $sendout = !empty($_REQUEST['sendout']) ? htmlspecialchars(trim($_REQUEST['sendout'])) : '';
        $storeUserId = !empty($_REQUEST['storeUserId']) ? htmlspecialchars(trim($_REQUEST['storeUserId'])) : '';
        $paymentType = !empty($_REQUEST['paymentType']) ? htmlspecialchars(trim($_REQUEST['paymentType'])) : '';
        $sourceId = !empty($_REQUEST['sourceId']) ? htmlspecialchars(trim($_REQUEST['sourceId'])) : '';
        $startTime = !empty($_REQUEST['start_time']) ? strtotime($_REQUEST['start_time']) : $first_time;
        $endTime = !empty($_REQUEST['end_time']) ? strtotime($_REQUEST['end_time']) : $end_time;
        $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars(trim($_REQUEST['store_id'])) : 58;
        $this->assign('sendout', $sendout);
        $this->assign('storeUserId', $storeUserId);
        $this->assign('paymentType', $paymentType);
        $this->assign('sourceId', $sourceId);
        $this->assign('startTime', date('Y-m-d', $startTime));
        $this->assign('endTime', date('Y-m-d', $endTime));
        $this->assign('store_id', $store_id);
        //where条件
        $where = " where a.mark=1 and a.order_state in (20,25,30,40,50,60,70) ";
        if (!empty($sendout)) {
            $where .= " and a.sendout = {$sendout}";
        }
        if (!empty($storeUserId)) {
            $where .= " and b.valet_order_user_id = {$storeUserId}";
        }
        if (!empty($paymentType)) {
            $where .= " and c.payment_type = {$paymentType}";
        }
        if (!empty($sourceId)) {
            $where .= " and c.payment_source = {$sourceId}";
        }
        if (!empty($startTime)) {
            $where .= " and c.payment_time >= {$startTime}";
        }
        if (!empty($endTime)) {
            $endTime = $endTime + 86400;
            $where .= " and c.payment_time < {$endTime}";
        }
        //判断店铺是总代理还是经销商
        $storeInfo = &m('store')->getOne(
            array(
                'cond' => 'id = ' . $this->storeId,
                'fields' => 'store_type',
            )
        );
        if ($storeInfo['store_type'] == 1) {//总代理
            // 店铺列表
            $stores = &m('store')->getStores($this->defaulLang);
            $this->assign('stores', $stores);
        } else {//经销商
            $store_id = $this->storeId;
        }
        $sql = "select a.order_sn,a.order_amount,a.order_state,a.sendout,a.evaluation_state,b.discount,b.fx_money,b.point_discount,b.coupon_discount,b.shipping_fee,c.payment_type,c.payment_source,c.payment_time,d.username,d.phone,e.name as sourceName,sum(g.market_price) as market_prices from bs_order_{$store_id} as a " .
            " left join bs_order_details_{$store_id} as b on a.id = b.order_id " .
            " left join bs_order_relation_{$store_id} as c on a.id = c.order_id " .
            " left join bs_user as d on a.buyer_id = d.id " .
            " left join bs_store_source as e on c.payment_source = e.id " .
            " left join bs_order_goods as f on a.order_sn = f.order_id " .
            " left join bs_store_goods as g on f.goods_id = g.id ";
//        echo $sql.$where . " group by a.id order by a.id desc ";die;
        $result = $this->orderMod->querySqlPageData($sql . $where . " group by a.id order by a.id desc ");
        $data = $result['list'];
        foreach ($data as &$v) {
            //订单状态
            $v['statusName'] = $this->orderMod->getOrderStatusName($v['sendout'], $v['order_state'], $v['evaluation_state']);
            //配送方式
            switch ($v['sendout']) {
                case 1:
                    $v['sendoutName'] = '自提';
                    break;
                case 2:
                    $v['sendoutName'] = '区域配送';
                    break;
                case 3:
                    $v['sendoutName'] = '邮寄托运';
                    break;
                case 4:
                    $v['sendoutName'] = '海外代购';
                    break;
                default:
                    $v['sendoutName'] = '';
                    break;
            }
            //订单来源
            if ($v['payment_source'] == 1758421) {
                $v['sourceName'] = '艾美睿';
            }
            //支付方式
            switch ($v['payment_type']) {
                case 1:
                    $v['paymentName'] = '支付宝支付';
                    break;
                case 2:
                    $v['paymentName'] = '微信支付';
                    break;
                case 3:
                    $v['paymentName'] = '余额支付';
                    break;
                case 4:
                    $v['paymentName'] = '线下支付';
                    break;
                case 5:
                    $v['paymentName'] = '免费兑换';
                    break;
                default:
                    $v['paymentName'] = '';
                    break;
            }
            //付款时间
            $v['payment_time'] = date('Y-m-d H:i', $v['payment_time']);
            $v['shipping_fee'] = $v['sendout']==1 ? 0 : $v['shipping_fee'];
        }
        $this->assign('data', $data);
        $this->assign('page_html', $result['ph']);
        $this->assign('totalNum', $result['total']);
        //统计订单
        $sql_money = "select sum(case when a.order_state=70 then 0 else 1 end) as num1,sum(case when a.order_state=70 then 1 else 0 end) as num2,sum(case when a.order_state=70 then 0 else a.order_amount end) as money1,sum(case when a.order_state=70 then a.order_amount else 0 end) as money2 from bs_order_{$store_id} as a " .
            " left join bs_order_details_{$store_id} as b on a.id = b.order_id " .
            " left join bs_order_relation_{$store_id} as c on a.id = c.order_id ";
        $countOrder = $this->orderMod->querySql($sql_money . $where);
        $this->assign('countOrder', $countOrder[0]);
        //店员用户
        $sql = "select id,real_name as username from bs_store_user where mark=1 and enable=1 and store_id={$store_id}";
        $czRy = $this->orderMod->querySql($sql);
        $this->assign('czRy', $czRy);
        //订单来源
        $sql = "select id,name from bs_store_source where store_id={$store_id}";
        $laiYuan = $this->orderMod->querySql($sql);
        $this->assign('laiYuan', $laiYuan);
        $this->display('orderReport/index.html');
    }

    public function exportOrder()
    {
        // 获取今年第一天和最后一天
        $first_time = strtotime(date('Y-01-01'));
        $end_time = strtotime(date('Y-12-31'));
        //删选条件
        $sendout = !empty($_REQUEST['sendout']) ? htmlspecialchars(trim($_REQUEST['sendout'])) : '';
        $storeUserId = !empty($_REQUEST['storeUserId']) ? htmlspecialchars(trim($_REQUEST['storeUserId'])) : '';
        $paymentType = !empty($_REQUEST['paymentType']) ? htmlspecialchars(trim($_REQUEST['paymentType'])) : '';
        $sourceId = !empty($_REQUEST['sourceId']) ? htmlspecialchars(trim($_REQUEST['sourceId'])) : '';
        $startTime = !empty($_REQUEST['start_time']) ? strtotime($_REQUEST['start_time']) : $first_time;
        $endTime = !empty($_REQUEST['end_time']) ? strtotime($_REQUEST['end_time']) : $end_time;
        $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars(trim($_REQUEST['store_id'])) : 58;
        //where条件
        $where = " where a.mark=1 and a.order_state in (20,25,30,40,50,60,70) ";
        if (!empty($sendout)) {
            $where .= " and a.sendout = {$sendout}";
        }
        if (!empty($storeUserId)) {
            $where .= " and b.valet_order_user_id = {$storeUserId}";
        }
        if (!empty($paymentType)) {
            $where .= " and c.payment_type = {$paymentType}";
        }
        if (!empty($sourceId)) {
            $where .= " and c.payment_source = {$sourceId}";
        }
        if (!empty($startTime)) {
            $where .= " and c.payment_time >= {$startTime}";
        }
        if (!empty($endTime)) {
            $endTime = $endTime + 86400;
            $where .= " and c.payment_time < {$endTime}";
        }
        //判断店铺是总代理还是经销商
        $storeInfo = &m('store')->getOne(
            array(
                'cond' => 'id = ' . $this->storeId,
                'fields' => 'store_type',
            )
        );
        if ($storeInfo['store_type'] == 1) {//总代理
            // 店铺列表
            $stores = &m('store')->getStores($this->defaulLang);
            $this->assign('stores', $stores);
        } else {//经销商
            $store_id = $this->storeId;
        }
        //获取订单数据
        $sql = "select a.id,a.order_sn,a.goods_amount,a.order_amount,a.order_state,a.sendout,a.evaluation_state,b.pay_sn,b.discount,b.fx_money,b.point_discount,b.coupon_discount,b.shipping_fee,c.payment_type,c.payment_source,c.payment_time,d.username,d.phone,e.name as sourceName,f.order_id,f.goods_name,f.goods_price,f.goods_num,f.goods_pay_price,f.spec_key_name,g.market_price from bs_order_{$store_id} as a " .
            " left join bs_order_details_{$store_id} as b on a.id = b.order_id " .
            " left join bs_order_relation_{$store_id} as c on a.id = c.order_id " .
            " left join bs_user as d on a.buyer_id = d.id " .
            " left join bs_store_source as e on c.payment_source = e.id " .
            " left join bs_order_goods as f on a.order_sn = f.order_id " .
            " left join bs_store_goods as g on f.goods_id = g.id ";
        $data = $this->orderMod->querySql($sql . $where . " order by a.id desc limit 0,30000");
        //整理订单数据
        $orderInfo = array();
        $orderGoodsInfo = array();
        foreach ($data as $k => $v) {
            if (!isset($orderGoodsInfo[$v['id']])) {
                //订单状态
                $v['statusName'] = $this->orderMod->getOrderStatusName($v['sendout'], $v['order_state'], $v['evaluation_state']);
                //配送方式
                switch ($v['sendout']) {
                    case 1:
                        $v['sendoutName'] = '自提';
                        break;
                    case 2:
                        $v['sendoutName'] = '区域配送';
                        break;
                    case 3:
                        $v['sendoutName'] = '邮寄托运';
                        break;
                    case 4:
                        $v['sendoutName'] = '海外代购';
                        break;
                    default:
                        $v['sendoutName'] = '';
                        break;
                }
                //订单来源
                if ($v['payment_source'] == 1758421) {
                    $v['sourceName'] = '艾美睿';
                }
                //支付方式
                switch ($v['payment_type']) {
                    case 1:
                        $v['paymentName'] = '支付宝支付';
                        break;
                    case 2:
                        $v['paymentName'] = '微信支付';
                        break;
                    case 3:
                        $v['paymentName'] = '余额支付';
                        break;
                    case 4:
                        $v['paymentName'] = '线下支付';
                        break;
                    case 5:
                        $v['paymentName'] = '免费兑换';
                        break;
                    default:
                        $v['paymentName'] = '';
                        break;
                }
                //付款时间
                $v['payment_time'] = date('Y-m-d', $v['payment_time']);
                $v['shipping_fee'] = $v['sendout']==1 ? 0 : $v['shipping_fee'];
                $orderInfo[] = $v;
            }
            $orderGoodsInfo[$v['id']][] = array(
                'order_id' => $v['order_id'],
                'goods_name' => $v['goods_name'],
                'goods_price' => $v['goods_price'],
                'goods_num' => $v['goods_num'],
                'goods_pay_price' => $v['goods_pay_price'],
                'spec_key_name' => $v['spec_key_name'],
                'market_price' => $v['market_price'],
            );
            unset($data[$k]);//清理数据，节约缓存
        }
        $total = array();
        foreach ($orderGoodsInfo as $key => $value) {
            $price = 0;
            foreach ($value as $k => $v) {
                $price += $v['goods_price'] * $v['goods_num'];
            }
            $total[] = array('id' => $key, 'original_price' => $price);
        }
        foreach ($orderInfo as $key => &$value) {
            foreach ($total as $k => $v) {
                if ($value['id'] == $v['id']) {
                    $orderInfo[$key]['original_price'] = number_format($v['original_price'], 2, '.', '');
                }
            }
        }
        //需要统计的变量
        $orderNumTotal = 0;
        $goodsAmountTotal = 0.00;
        $orderAmountTotal = 0.00;
        $discountTotal = 0.00;
        $fxMoneyTotal = 0.00;
        $pointDiscountTotal = 0.00;
        $couponDiscountTotal = 0.00;
        //输入到excel表格
        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=交班订单统计报表.xls");
        echo iconv('utf-8', 'gb2312', "订单编号") . "\t";
        echo iconv('utf-8', 'gb2312', "配送方式") . "\t";
        echo iconv('utf-8', 'gb2312', "买家姓名") . "\t";
        echo iconv('utf-8', 'gb2312', "买家手机") . "\t";
        echo iconv('utf-8', 'gb2312', "订单来源") . "\t";
        echo iconv('utf-8', 'gb2312', "支付方式") . "\t";
        echo iconv('utf-8', 'gb2312', "支付单号") . "\t";
        echo iconv('utf-8', 'gb2312', "付款时间") . "\t";
        echo iconv('utf-8', 'gb2312', "订单运费") . "\t";
        echo iconv('utf-8', 'gb2312', "订单原价格") . "\t";
        echo iconv('utf-8', 'gb2312', "订单总价格") . "\t";
        echo iconv('utf-8', 'gb2312', "订单状态") . "\t";
        echo iconv('utf-8', 'gb2312', "优惠额抵扣") . "\t";
        echo iconv('utf-8', 'gb2312', "分销码") . "\t";
        echo iconv('utf-8', 'gb2312', "睿积分抵扣") . "\t";
        echo iconv('utf-8', 'gb2312', "优惠券抵扣") . "\t";
        echo "\n";
        foreach ($orderInfo as $k => $v) {
            //订单主表信息
            echo iconv('utf-8', 'gb2312', "'" . $v['order_sn']) . "\t";          //订单编号
            echo iconv('utf-8', 'gb2312', $v['sendoutName']) . "\t";              //配送方式
            echo iconv('utf-8', 'gb2312', $v['username']) . "\t";              //买家姓名
            echo iconv('utf-8', 'gb2312', $v['phone']) . "\t";                   //买家手机
            echo iconv('utf-8', 'gb2312', $v['sourceName']) . "\t";                  //订单来源
            echo iconv('utf-8', 'gb2312', $v['paymentName']) . "\t";            //支付方式
            echo iconv('utf-8', 'gb2312', "'" . $v['pay_sn']) . "\t";            //支付单号
            echo iconv('utf-8', 'gb2312', $v['payment_time']) . "\t";        //付款时间
            echo iconv('utf-8', 'gb2312', $v['shipping_fee']) . "\t";            //订单运费
            echo iconv('utf-8', 'gb2312', $v['original_price']) . "\t";            //订单原价格
            echo iconv('utf-8', 'gb2312', $v['order_amount']) . "\t";            //订单总价格
            echo iconv('utf-8', 'gb2312', $v['statusName']) . "\t";              //订单状态
            echo iconv('utf-8', 'gb2312', $v['discount']) . "\t";                //优惠额抵扣
            echo iconv('utf-8', 'gb2312', $v['fx_money']) . "\t";               //分销码抵扣
            echo iconv('utf-8', 'gb2312', $v['point_discount']) . "\t";               //睿积分抵扣
            echo iconv('utf-8', 'gb2312', $v['coupon_discount']) . "\t";               //优惠劵抵扣
            echo "\n";
            //订单商品信息
            echo iconv('utf-8', 'gb2312', '') . "\t";                                //NULL
            echo iconv('utf-8', 'gb2312', '商品编号') . "\t";
            echo iconv('utf-8', 'gb2312', '商品名称') . "\t";
            echo iconv('utf-8', 'gb2312', '商品价格') . "\t";
            echo iconv('utf-8', 'gb2312', '商品数量') . "\t";
            echo iconv('utf-8', 'gb2312', '商品实际成交价') . "\t";
            echo iconv('utf-8', 'gb2312', '规格名称') . "\t";
            echo iconv('utf-8', 'gb2312', '市场价') . "\t";
            echo "\n";

            $order_goods_data = $orderGoodsInfo[$v['id']];

            foreach ($order_goods_data as $gv) {
                echo iconv('utf-8', 'gb2312', '') . "\t";                                //NULL
                echo iconv('utf-8', 'gb2312', "'" . $gv['order_id']) . "\t";         //订单编号
                echo iconv('utf-8', 'gb2312', $gv['goods_name']) . "\t";             //商品名称
                echo iconv('utf-8', 'gb2312', $gv['goods_price']) . "\t";            //商品价格
                echo iconv('utf-8', 'gb2312', $gv['goods_num']) . "\t";              //商品数量
                echo iconv('utf-8', 'gb2312', $gv['goods_pay_price']) . "\t";        //商品实际成交价
                echo iconv('utf-8', 'gb2312', $gv['spec_key_name']) . "\t";          //规格名称
                echo iconv('utf-8', 'gb2312', $gv['market_price']) . "\t";          //市场价
                echo "\n";
            }
            //统计
            if ($v['order_state'] != 70) {
                $orderNumTotal += 1;
                $goodsAmountTotal += $v['goods_amount'];
                $orderAmountTotal += $v['order_amount'];
                $discountTotal += $v['discount'];
                $fxMoneyTotal += $v['fx_money'];
                $pointDiscountTotal += $v['point_discount'];
                $couponDiscountTotal += $v['coupon_discount'];
            }
        }
        //获取筛选人员的昵称
        if ($storeUserId == '') {
            $storeUserName = '全部人员';
        } else {
            $sql_user = 'select * from ' . DB_PREFIX . 'store_user where id = ' . $storeUserId;
            $order_user = $this->orderMod->querySql($sql_user);
            $storeUserName = $order_user[0]['real_name'];
        }
        echo "\n";
        echo iconv('utf-8', 'gb2312', '有效订单总笔数:') . "\t";
        echo iconv('utf-8', 'gb2312', $orderNumTotal) . "\t";
        echo iconv('utf-8', 'gb2312', '订单原金额:') . "\t";
        echo iconv('utf-8', 'gb2312', number_format($goodsAmountTotal, 2)) . "\t";
        echo iconv('utf-8', 'gb2312', '实收营业额:') . "\t";
        echo iconv('utf-8', 'gb2312', number_format($orderAmountTotal, 2)) . "\t";
        echo iconv('utf-8', 'gb2312', '优惠金额:') . "\t";
        echo iconv('utf-8', 'gb2312', number_format($discountTotal, 2)) . "\t";
        echo iconv('utf-8', 'gb2312', '分销码抵扣金额:') . "\t";
        echo iconv('utf-8', 'gb2312', number_format($fxMoneyTotal, 2)) . "\t";
        echo iconv('utf-8', 'gb2312', '睿币抵扣金额:') . "\t";
        echo iconv('utf-8', 'gb2312', number_format($pointDiscountTotal, 2)) . "\t";
        echo iconv('utf-8', 'gb2312', '优惠卷抵扣金额:') . "\t";
        echo iconv('utf-8', 'gb2312', number_format($couponDiscountTotal, 2)) . "\t";
        echo "\n";
        echo iconv('utf-8', 'gb2312', '交班人员:') . "\t";
        echo iconv('utf-8', 'gb2312', $storeUserName) . "\t";
        if (!empty($startTime)) {
            echo iconv('utf-8', 'gb2312', '筛选时间开始:') . "\t";
            echo iconv('utf-8', 'gb2312', date('Y-m-d', $startTime)) . "\t";
        }
        if (!empty($endTime)) {
            echo iconv('utf-8', 'gb2312', '筛选时间结束:') . "\t";
            echo iconv('utf-8', 'gb2312', date('Y-m-d', $endTime - 86400)) . "\t";
        }
        echo "\n";
    }
}

?>