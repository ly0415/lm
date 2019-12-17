<?php
/**
 * 消费统计
 * User: xt
 * Date: 2019/2/2
 * Time: 14:12
 */

class consumeCountApp extends BackendApp
{
    /**
     * consumeCountApp constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 统计列表
     */
    public function index()
    {
        $buyer_name = empty($_REQUEST['buyer_name']) ? '' : htmlspecialchars(trim($_REQUEST['buyer_name']));
        $phone = empty($_REQUEST['phone']) ? '' : htmlspecialchars(trim($_REQUEST['phone']));
        $start_time = empty($_REQUEST['start_time']) ? '' : strtotime(htmlspecialchars(trim($_REQUEST['start_time'])));
        $end_time = empty($_REQUEST['end_time']) ? '' : strtotime(htmlspecialchars(trim($_REQUEST['end_time'])));

        $where = ' where order_state = 50 ';

        if ($buyer_name) {
            $where .= ' and buyer_name like "%' . $buyer_name . '%"';
        }

        if ($phone) {
            $where .= ' and u.phone like "%' . $phone . '%"';
        }

        if ($start_time) {
            $where .= ' and payment_time >= ' . $start_time;
        }

        if ($end_time) {
            $where .= ' and payment_time < ' . $end_time;
        }

        // 订单主表
        $orderMod = &m('order');
        $sql = 'select count(order_id) as order_count,sum(order_amount) as amounts,buyer_id,buyer_name,u.phone from bs_order left join bs_user as u on buyer_id= u.id ' . $where . ' group by buyer_id order by order_count desc';
        $orders = $orderMod->querySqlPageData($sql, $array = array("pre_page" => 15));

        foreach ($orders['list'] as $index => $order) {
            $stores = $orderMod->getStoreCount($order['buyer_id'], $start_time, $end_time);
            $goods = $orderMod->getGoodsCount($order['buyer_id'], $start_time, $end_time);
            $orders['list'][$index]['stores'] = $stores;
            $orders['list'][$index]['goods'] = $goods;
        }

        $this->assign('orders', $orders['list']);
        $this->assign('page_html', $orders['ph']);
        $this->assign('buyer_name', $buyer_name);
        $this->assign('phone', $phone);
        $this->assign('start_time', date('Y-m-d H:i', $start_time));
        $this->assign('end_time', date('Y-m-d H:i', $end_time));
        $this->display('consumeCount/index.html');
    }

    /**
     * 导出 【测试版】
     */
    public function exportTest()
    {
        $buyer_name = empty($_REQUEST['buyer_name']) ? '' : htmlspecialchars(trim($_REQUEST['buyer_name']));
        $phone = empty($_REQUEST['phone']) ? '' : htmlspecialchars(trim($_REQUEST['phone']));
        $start_time = empty($_REQUEST['start_time']) ? '' : strtotime(htmlspecialchars(trim($_REQUEST['start_time'])));
        $end_time = empty($_REQUEST['end_time']) ? '' : strtotime(htmlspecialchars(trim($_REQUEST['end_time'])));

        $where = ' where order_state = 50 ';

        if ($buyer_name) {
            $where .= ' and buyer_name like "%' . $buyer_name . '%"';
        }

        if ($phone) {
            $where .= ' and u.phone like "%' . $phone . '%"';
        }

        if ($start_time) {
            $where .= ' and payment_time >= ' . $start_time;
        }

        if ($end_time) {
            $where .= ' and payment_time < ' . $end_time;
        }

        $title = array('用户名', '手机号', '消费总次数', '消费总额', '门店', '次数', '商品', '次数');
        $limit = 10;

        // 订单主表
        $orderMod = &m('order');
        $sql = 'select buyer_name,u.phone,count(order_id) as order_count,sum(order_amount) as amounts,buyer_id from bs_order left join bs_user as u on buyer_id= u.id ' . $where . ' group by buyer_id order by order_count desc';
        $orders = $orderMod->querySql($sql);

        $stores = $orderMod->getStoreCount(); // 店铺
        $goods = $orderMod->getGoodsCount(); // 商品

        $store_container = array();
        $good_container = array();

        foreach ($stores as $store) {
            $store_container[$store['buyer_id']][] = $store;
        }

        foreach ($goods as $good) {
            $good_container[$good['buyer_id']][] = $good;
        }

        // echo '<pre>';print_r($orders);die;

        $count = count($orders);
        // $count = 10;

        $size = ceil($count / $limit);

        for ($i = 1; $i <= $size; $i++) {
            $start = ($i - 1) * $limit;
            $sql = "select buyer_name,u.phone,count(order_id) as order_count,sum(order_amount) as amounts,buyer_id from bs_order left join bs_user as u on buyer_id= u.id " . $where . " group by buyer_id order by order_count desc limit {$start},{$limit}";
            $orderlist = $orderMod->querySql($sql);

            foreach ($orderlist as $index => $order) {
                unset($order['buyer_id']);

                if (isset($store_container[$order['buyer_id']])) {
                    $store_data = array_slice($store_container[$order['buyer_id']], 0, 3);
                }

                if (isset($good_container[$order['buyer_id']])) {
                    $good_data = array_slice($store_container[$order['buyer_id']], 0, 3);
                }

                $order['store_name'] = implode(',', array_map(function ($i) {
                    return $i['store_name'];
                }, $store_data));
                $order['store_times'] = implode(',', array_map(function ($i) {
                    return $i['store_count'];
                }, $store_data));
                $order['goods_name'] = implode(',', array_map(function ($i) {
                    return $i['goods_name'];
                }, $good_data));
                $order['goods_times'] = implode(',', array_map(function ($i) {
                    return $i['goods_count'];
                }, $good_data));

                $data[] = $order;
            }
        }

        include_once ROOT_PATH . '/includes/libraries/csvExport.lib.php';
        $csvExport = new csvExport();
        $csvExport->export($title, $count, $limit, $data);
    }

    /**
     * 导出
     */
    public function export()
    {
        $buyer_name = empty($_REQUEST['buyer_name']) ? '' : htmlspecialchars(trim($_REQUEST['buyer_name']));
        $phone = empty($_REQUEST['phone']) ? '' : htmlspecialchars(trim($_REQUEST['phone']));
        $start_time = empty($_REQUEST['start_time']) ? '' : strtotime(htmlspecialchars(trim($_REQUEST['start_time'])));
        $end_time = empty($_REQUEST['end_time']) ? '' : strtotime(htmlspecialchars(trim($_REQUEST['end_time'])));

        $where = ' where order_state = 50 ';

        if ($buyer_name) {
            $where .= ' and buyer_name like "%' . $buyer_name . '%"';
        }

        if ($phone) {
            $where .= ' and u.phone like "%' . $phone . '%"';
        }

        if ($start_time) {
            $where .= ' and payment_time >= ' . $start_time;
        }

        if ($end_time) {
            $where .= ' and payment_time < ' . $end_time;
        }


        // 订单主表
        $orderMod = &m('order');


        $stores = $orderMod->getStoreCount('', $start_time, $end_time); // 店铺
        $goods = $orderMod->getGoodsCount('', $start_time, $end_time); // 商品

        $store_container = array();
        $good_container = array();

        foreach ($stores as $store) {
            $store_container[$store['buyer_id']][] = $store;
        }

        foreach ($goods as $good) {
            $good_container[$good['buyer_id']][] = $good;
        }

        $sql = "select buyer_name,u.phone,count(order_id) as order_count,sum(order_amount) as amounts,buyer_id from bs_order left join bs_user as u on buyer_id= u.id " . $where . " group by buyer_id order by order_count desc limit 0,15000";
        $orderlist = $orderMod->querySql($sql);

        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=消费统计.xls");
        echo iconv('utf-8', 'gb2312', "用户名") . "\t";
        echo iconv('utf-8', 'gb2312', "手机号") . "\t";
        echo iconv('utf-8', 'gb2312', "消费总次数") . "\t";
        echo iconv('utf-8', 'gb2312', "消费总额") . "\t";
        echo "\n";

        foreach ($orderlist as $index => $order) {

            echo iconv('utf-8', 'gb2312', $order['buyer_name']) . "\t";
            echo iconv('utf-8', 'gb2312', $order['phone']) . "\t";
            echo iconv('utf-8', 'gb2312', $order['order_count']) . "\t";
            echo iconv('utf-8', 'gb2312', $order['amounts']) . "\t";
            echo "\n";

            echo iconv('utf-8', 'gb2312') . "\t";
            echo iconv('utf-8', 'gb2312', '门店') . "\t";
            echo iconv('utf-8', 'gb2312', '次数') . "\t";
            echo "\n";

            $store_data = array_slice($store_container[$order['buyer_id']], 0, 3);

            foreach ($store_data as $store_value) {
                $store_convert_name = str_replace('艾美睿®️', '艾美睿', $store_value['store_name']);
                $store_convert_name = str_replace('艾美睿®', '艾美睿', $store_convert_name);
                echo iconv('utf-8', 'gb2312') . "\t";
                echo iconv('utf-8', 'gb2312', $store_convert_name) . "\t";
                echo iconv('utf-8', 'gb2312', $store_value['store_count']) . "\t";
                echo "\n";
            }

            echo iconv('utf-8', 'gb2312') . "\t";
            echo iconv('utf-8', 'gb2312', '商品') . "\t";
            echo iconv('utf-8', 'gb2312', '次数') . "\t";
            echo "\n";

            $goods_data = array_slice($good_container[$order['buyer_id']], 0, 3);

            foreach ($goods_data as $good_value) {
                echo iconv('utf-8', 'gb2312') . "\t";
                echo iconv('utf-8', 'gb2312', $good_value['goods_name']) . "\t";
                echo iconv('utf-8', 'gb2312', $good_value['goods_count']) . "\t";
                echo "\n";
            }
        }

    }

}