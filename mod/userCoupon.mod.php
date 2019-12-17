<?php
/**
 * 电子劵
 * @date: 2017/9/25
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class UserCouponMod extends BaseMod
{

    const EXPECT_TIME = 86400;  // 购物车存在时间  默认24h  by xt 2019.02.11

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct("user_coupon");
    }

    /**
     * 获取用户有效点子券
     * $type:劵类型1:抵扣券2:兑换券
     * $store_id:店铺id,0表示所有店铺
     * $money:金额,0表示不限制
     * $room_type_prices:二级业务类型及其商品价格关联二维数组，兑换券专有，array(array('room_type_id'=>0,'money'=>0))
     */
    public function getValidCoupons($user_id, $lang_id, $type = 1, $store_id = 0, $money = 0, $room_type_prices = array(),$cart_ids = 0)
    {
        $time_today = strtotime(date('Ymd'));
        $time_current = time();
        //获取满足使用条件的劵
        $sql = "select a.id as user_coupon_id,a.start_time,a.end_time,b.*,c.total_use,c.day_use,CASE WHEN (b.money > {$money}) or (b.day_times <= c.day_use) THEN 2 ELSE 1 END as usetype,d.type_name from " .
            DB_PREFIX . "user_coupon as a left join " .
            DB_PREFIX . "coupon as b on a.c_id = b.id left join " .
            "(select t1.user_coupon_id,count(t1.id) as total_use,sum(CASE WHEN t1.add_time>={$time_today} and t1.add_time<={$time_current} THEN 1 ELSE 0 END) as day_use from " .
            DB_PREFIX . "coupon_log as t1 where t1.user_id = {$user_id} group by t1.user_coupon_id) as c on a.id = c.user_coupon_id left join  " .
            DB_PREFIX . "room_type_lang as d on b.room_type_id = d.type_id and d.lang_id = {$lang_id} " .
            " where a.user_id = {$user_id} and b.type = {$type} and a.start_time <= {$time_current} and a.end_time >= {$time_current} and ((b.total_times > c.total_use) or (c.total_use is null) or (b.total_times = 0)) ";
        if ($store_id != 0) {
            $sql .= " and FIND_IN_SET({$store_id}, b.store_id) ";
        }

        $res = $this->querySql($sql . " order by usetype ");

        //处理$room_type_prices数组
        $room_type_price_unique = array();//业务类型id=>金额，一维数组键值对
        if (!empty($room_type_prices)) {
            foreach ($room_type_prices as $v) {
                if (strpos($v['room_type_id'], ',')) {
                    $room_type = explode(',', $v['room_type_id']);
                    foreach ($room_type as $key => $value) {
                        $sql2 = "select superior_id from " . DB_PREFIX . "room_type where id = {$value}";
                        $roomTypeInfo = $this->querySql($sql2);
//                        echo '<pre>';print_r($roomTypeInfo);die;
                        $room_type_pid = $roomTypeInfo[0]['superior_id'] ?: 0;
                        if (!isset($room_type_price_unique[$value]) || ($room_type_price_unique[$value] > $v['money'])) {
                            $room_type_price_unique[$value] = $v['money'];
                        }
                        if ($room_type_pid > 0 && (!isset($room_type_price_unique[$room_type_pid]) || ($room_type_price_unique[$room_type_pid] > $v['money']))) {
                            $room_type_price_unique[$room_type_pid] = $v['money'];
                        }
                    }
                } else {
                    $sql2 = "select superior_id from " . DB_PREFIX . "room_type where id = {$v['room_type_id']}";
                    $roomTypeInfo = $this->querySql($sql2);
                    $room_type_pid = $roomTypeInfo[0]['superior_id'] ?: 0;
                    if (!isset($room_type_price_unique[$v['room_type_id']]) || ($room_type_price_unique[$v['room_type_id']] > $v['money'])) {
                        $room_type_price_unique[$v['room_type_id']] = $v['money'];
                    }
                    if ($room_type_pid > 0 && (!isset($room_type_price_unique[$room_type_pid]) || ($room_type_price_unique[$room_type_pid] > $v['money']))) {
                        $room_type_price_unique[$room_type_pid] = $v['money'];
                    }
                }
            }
        }
 

 	   $cartMod=&m('cart');
            $query=array(
                'cond' =>"`id` in  ({$cart_ids}) ",
            );
            $goodIdss = $cartMod->getIds($query,'','goods_id');

            $goodIdss && $ccc = implode(',',$goodIdss);
            $storeGoodsMod=&m('storeGoods');
            $query1=array(
                'cond' =>"`id` in  ({$ccc}) ",
            );
             $goodIds = $storeGoodsMod->getIds($query1,'','goods_id');

           //获取劵对应店铺
            $storeMod = &m("store");
            $data1 = $data2 = array();
            foreach ($res as $v) {
                $temp = $v;
                $temp['total_use'] = $v['total_use'] ?: 0;
                $temp['day_use'] = $v['day_use'] ?: 0;
                $temp['left_times'] = $v['total_times'] - $v['total_use'];
                if($temp['store_value'] == 1 && empty($temp['goods_id'])){
                    $temp['store_names'] = '艾美新零售所有门店';
                } elseif($temp['store_value'] == 1 && $temp['goods_id']) {
                    $goods_info = $this->querySql("select b.goods_name from " . DB_PREFIX . "goods as a LEFT JOIN bs_goods_lang b ON a.goods_id = b. goods_id where b.lang_id = 29 AND a.goods_id = {$v['goods_id']}");
                    $temp['store_names'] = '艾美新零售所有门店：'.$goods_info[0]['goods_name'];
                } elseif($temp['store_value'] == 2 && $temp['goods_id']) {
                    $store_info = $storeMod->getNameByIds($v['store_id'], $lang_id);
                    $goods_info = $this->querySql("select b.goods_name from " . DB_PREFIX . "goods as a LEFT JOIN bs_goods_lang b ON a.goods_id = b. goods_id where b.lang_id = 29 AND a.goods_id = {$v['goods_id']}");
                    $temp['store_names'] = '门店'.$store_info.'：'.$goods_info[0]['goods_name'];
                } elseif($temp['store_value'] == 2 && empty($temp['goods_id'])) {
                    $store_info = $storeMod->getNameByIds($v['store_id'], $lang_id);
                    $temp['store_names'] = '门店'.$store_info;
                } else {
                    $temp['store_names'] = $storeMod->getNameByIds($v['store_id'], $lang_id);
                }
                if (!empty($room_type_price_unique)) {
                    if (isset($room_type_price_unique[$v['room_type_id']]) && ($room_type_price_unique[$v['room_type_id']] <= $v['money'])) {
                        $temp['usetype'] = 1;
                    } else {
                        $temp['usetype'] = 2;
                    }
                }
                if (!empty($temp['goods_id'])) {
                    if(in_array($temp['goods_id'], $goodIds)){
                        $temp['usetype'] = 1;
                    } else {
                        $temp['usetype'] = 2;
                    }
                }
                if ($temp['usetype'] == 1) {
                    $data1[] = $temp;
                } else {
                    $data2[] = $temp;
                }
            }

        $info = array_merge($data1, $data2);

        return $info;
    }

    /**
     * 根据充值规则分发卷
     */
    public function addCouponByRecharge($user_id, $recharge_id, $order_id,$order_sn)
    {
        //获取规则对应的所有劵
        $sql = "select id,limit_times from " . DB_PREFIX . "coupon where mark = 1 and  recharge_id = {$recharge_id}";
        $couponInfo = $this->querySql($sql);
        $nowTime = time();
        //循环分发劵
        $data = array(
            'user_id' => $user_id,
            'order_id' => $order_id,
            'order_sn' =>$order_sn,
            'source' => 2,
            'remark' => '充值赠送',
            'add_time' => time(),
            'start_time' => time(),
        );
        foreach ($couponInfo as $v) {
            $data['start_time'] = $nowTime;
            $data['end_time'] = $nowTime + $v['limit_times'] * 3600 * 24;
            $data['add_user'] = $user_id;
            $data['c_id'] = $v['id'];
            $this->doInsert($data);
        }
        return true;
    }

    /**
     * 根据 coupon_id 获取对应的 user_coupon 数据
     * @param $coupon_id
     * @param null $warning_time
     * @return array
     */
    public function userCoupons($coupon_id, $warning_time = null)
    {

        $where = '';
        if ($warning_time) {
            $currentTime = time();
            $where = " AND (uc.end_time - {$currentTime}) < {$warning_time}  AND (uc.end_time - {$currentTime}) > 0 ";
        }

        $sql = <<<SQL
                    SELECT
                        uc.id,
                        uc.c_id,
                        uc.start_time,
                        uc.end_time,
                        u.username,
                        u.phone,
                        c.coupon_name 
                    FROM
                        bs_user_coupon uc
                        LEFT JOIN bs_user u ON uc.user_id = u.id 
                        LEFT JOIN bs_coupon c ON uc.c_id = c.id 
                    WHERE
                        uc.c_id = {$coupon_id}
                        {$where}
                    ORDER BY
                        uc.add_time DESC
SQL;
        $userCoupons = $this->querySqlPageData($sql);

        return $userCoupons;
    }
}

?>