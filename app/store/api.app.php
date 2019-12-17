<?php

if (!defined('IN_ECM')) {
    die('Forbidden');
}

/**
 * 公共接口管理
 * @author luffy
 * @date 2018-07-25
 */
class ApiApp extends BaseApp {

    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct();
    }

    /**
     * 析构函数
     */
    public function __destruct(){
    }

    /**
     * json返回
     * @author luffy
     * @param $status   返回数据状态
     * @param $message  对返回状态说明
     * @param $info     返回数据信息
     * @date  2018-07-25
     */
    public function setData($info = array(), $status = 'success', $message = 'ok') {
        $data = array(
            'status' => $status,
            'message' => $message,
            'info' => $info,
        );
        echo json_encode($data);
        exit();
    }

    /**
     * 订单提醒 和 获取未指定订单和未手动接单的订单
     * @author  luffy
     * @date    2018-12-08
     */
    public function sendOrderNotice()
    {
        $store_id = $_SESSION['store']['storeId'];
        //查询order表是否有新订单
        $orderMod = &m("order");
        //获取新订单数量
        $orderNum = $orderMod->getPublicHeadOrderNum($store_id);
        //更新session
        $_SESSION['store']['headOrderNum'] = $orderNum;
        //获取提示音和自动打印权限的用户
        $sql = "select voucher_id from bs_system_console where type=5 and rebate_id={$store_id}";
        $userInfo = $orderMod->querySql($sql);
        if (!empty($userInfo) && ($userInfo[0]['voucher_id'] == $_SESSION['store']['userId'])) {
            $ordersnArr = $orderMod->getPrintOrders();
            if (!empty($ordersnArr)) {
                $this->setData(array('order_num' => $orderNum, "snResult" => implode(',', $ordersnArr)), $status = 1);
            }
        }
        $this->setData(array('order_num' => $orderNum), 0);
    }

    /**
     * 订单提醒
     * @author  luffy
     * @date    2018-07-25
     */
    public function sendOrderNotice_1()
    {
        $orderSn = !empty($_REQUEST['orderSn']) ? $_REQUEST['orderSn'] : '';
        //已经提示过的订单需要变成未提示状态
        if (!empty($orderSn)) {
            $orderMod = &m('order');
            $orderMod->update_warning_tone($_SESSION['store']['storeId'], $orderSn);
        }
    }

    /**
     * 自动打印
     * @author  luffy
     * @date    2019-03-27
     */
    public function automatic_print()
    {
        $orderSn = !empty($_REQUEST['orderSn']) ? $_REQUEST['orderSn'] : '';
        $print_data = $this->getPrintData($orderSn);
        $this->assign('print_data', $print_data);
        $str = self::$smarty->fetch("customerOrder/automatic-print.html");
        $this->setData($str, 1, 'ok');
    }

    /**
     * 打印接口
     * @author  luffy
     * @date   2019-03-11
     */
    public function getPrintData($order_sn){
        if( empty($order_sn) ){
            $this->setData(array(), 1, "订单号必传");
        }
        $store_id     = $_SESSION['store']['storeId'];
        $orderMod     = &m('order'.$store_id);
        $modString_1  = DB_PREFIX.'order_'.$store_id;
        $modString_2  = DB_PREFIX.'order_details_'.$store_id;

        //获取新订单表订单数据
        $fields = 'b.*,a.order_sn,a.order_state,a.goods_amount,a.sendout,a.order_amount,a.add_time,c.username,c.phone';
        $sql    = 'select ' . $fields . ' from ' . $modString_1 . ' as a left join '
            . $modString_2 . ' as b on b.order_id = a.id left join '
            . DB_PREFIX . 'user as c on c.id = a.buyer_id '
            . 'where a.order_sn in ("'.str_replace(',', '","', $order_sn).'")' ;
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
        $total_num                  = end($print_data);
        $print_data[0]['total_num']    = $total_num['good_num'];
        return $print_data;
    }

    /**
     * @author luffy
     * @date 2018-07-25
     */
    public function user_name() {
        $user_name = !empty($_REQUEST['user_name']) ? trim($_REQUEST['user_name']) : '';
        //获取表内容
        if (!empty($user_name)) {
            $where = ' and phone like "%' . addslashes(addslashes($user_name)) . '%"';
        }
        $userMod= &m('user');
        $data   = $userMod->get_data(array(
            'fields' => 'id,phone',
            'cond'  => ' mark = 1 AND is_use = 1 and is_kefu = 0 '.$where
        ));
        echo json_encode($data);
        die;
    }

    /**
     * 取消重复打印 by xt 2019.04.01
     */
    public function setPrintOrders()
    {
        $orderSn = empty($_REQUEST['print_order_ids']) ? '' : htmlspecialchars($_REQUEST['print_order_ids']);
        $type = empty($_REQUEST['type']) ? '' : htmlspecialchars($_REQUEST['type']);

        $_SESSION['print_return'] = 1;

        if ($type == 1) {
            $this->setData(array(), 1, '打印');
        }

        if (empty($orderSn)) {
            $this->setData(array(), 0, '订单不能为空');
        }

        $orderMod = &m('order');
        $orderMod->update_warning_tone($_SESSION['store']['storeId'], $orderSn);

        $this->setData(array(), 1, '取消成功');
    }

    /**
     * 订单查询 by xt 2019.04.02
     */
    public function orderQuery()
    {
        $storeId = empty($_REQUEST['store_id']) ? '' : htmlspecialchars($_REQUEST['store_id']);

        if (empty($storeId)) {
            echo '缺少 store_id 参数';
            exit;
        }

        $sql = "select ro.order_id,o.order_state from bs_order_relation_{$storeId} as ro left join bs_order_{$storeId} as o on o.id = ro.order_id where ro.payment_type = 0 and o.order_state in (20,30,40,50,60)";
        $orders = &m('order')->querySql($sql);

        echo '共' . count($orders) . '条数据';
        echo '<br>';

        echo '<pre>';print_r($orders);exit;
    }

    public function updateOrderPaySn()
    {
        $storeId = empty($_REQUEST['store_id']) ? '' : htmlspecialchars($_REQUEST['store_id']);

        if (empty($storeId)) {
            echo '缺少 store_id 参数';
            exit;
        }

        $orderMod = &m('order');

        $sql = <<<SQL
                    SELECT
                        od.order_id,
                        od.pay_sn 
                    FROM
                        bs_order_details_{$storeId} AS od 
                    WHERE
                        od.order_id IN ( SELECT ro.order_id FROM bs_order_relation_{$storeId} AS ro WHERE ro.payment_type IN ( 3, 4, 5 ) ) 
                        AND od.pay_sn != '';
SQL;
        $orders = $orderMod->querySql($sql);
        echo '共' . count($orders) . '条数据';
        echo '<br>';

        $sql = <<<SQL
                    UPDATE bs_order_details_{$storeId} 
                    SET pay_sn = '' 
                    WHERE
                        order_id IN (
                    SELECT
                        fo.order_id 
                    FROM
                        (
                    SELECT
                        od.order_id 
                    FROM
                        bs_order_details_{$storeId} AS od 
                    WHERE
                        od.order_id IN ( SELECT ro.order_id FROM bs_order_relation_{$storeId} AS ro WHERE ro.payment_type IN ( 3, 4, 5 ) ) 
                        AND od.pay_sn != '' 
                        ) AS fo 
                        );
SQL;
        $orderMod->db->Execute($sql);
        $affectedRows = mysql_affected_rows();
        if ($affectedRows > 0) {
            echo '更新成功，共' . $affectedRows . '条';
        }
        exit;
    }

    /**
     * 区域国家地下的店铺
     * @author wanyan
     * @date 2017-11-20
     */
    public function getChild() {
        $id = !empty($_REQUEST['parent_id']) ? $_REQUEST['parent_id'] : '';
        $languageId = !empty($_REQUEST['language_id']) ? $_REQUEST['language_id'] : '';
        $t_id = !empty($_REQUEST['t_id']) ? $_REQUEST['t_id'] : '';
        if (empty($id)) {
            $this->setData($info = array(), $status = '0', $message = '');
        }
        $storeMod = &m('store');
        $fxRulerMod = &m('fxrule');
        $sql = 'SELECT  c.id,l.store_name,c.store_cate_id  FROM  ' . DB_PREFIX . 'store  as c
                 left join  ' . DB_PREFIX . 'store_lang as l on  c.id = l.store_id
                 WHERE c.is_open = 1  and l.distinguish = 0 and  l.lang_id =' . $languageId . '  and c.store_cate_id=' . $id;
        $rs = $storeMod->querySql($sql);
        if (!empty($t_id)) {
            $ruleInfo = $fxRulerMod->getOne(array('cond' => "`id` = '{$t_id}'", 'fields' => "store_id,store_cate"));

            if ($ruleInfo['store_id']) {
                $store_ids = explode(',', $ruleInfo['store_id']);
                foreach ($rs as $k => $v) {
                    if ($v['store_cate_id'] == $ruleInfo['store_cate']) {
                        if (in_array($v['id'], $store_ids)) {
                            $rs[$k]['flag'] = 1;
                        } else {
                            $rs[$k]['flag'] = 0;
                        }
                    }
                }
            }
        }
        $this->setData($info = $rs, $status = '1', $message = '');
    }
}