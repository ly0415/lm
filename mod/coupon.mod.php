<?php
/**
* 电子劵
* @date: 2017/9/25
*/
if (!defined('IN_ECM')) { die('Forbidden'); }
class CouponMod extends BaseMod{
    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct("coupon");
    }

    //统计折扣券张数(不传递店铺id)
    public function getCouponCount($type, $time)
    {
        $beTime = strtotime($time . '-' . '01');
        $fiTime = strtotime($time . '-' . '12');

        switch ($type){
            case 1://抵扣券已发出的总数
                $sql = "SELECT id FROM bs_coupon WHERE type=1 and add_time between {$beTime} and {$fiTime}";
                $res = $this->querySql($sql);
                foreach ($res as $k => $v){
                    $secondData[]=$v['id'];
                }
                $secondIds=implode(',',$secondData);
                $sqls = "SELECT count(*) as sums FROM bs_user_coupon WHERE c_id in (".$secondIds.")";
                $userCouponMod = &m('userCoupon');
                $result = $userCouponMod->querySql($sqls);
                return $result[0]['sums'];
                break;
            case 2:
                $sql = "SELECT id FROM bs_coupon WHERE type=1 and add_time between {$beTime} and {$fiTime}";
                $res = $this->querySql($sql);
                foreach ($res as $k => $v){
                    $secondData[]=$v['id'];
                }
                $secondIds=implode(',',$secondData);
                $sqls = "SELECT count(*) as sums FROM bs_coupon_log WHERE coupon_id in (".$secondIds.")";
                $couponLogMod = &m('couponLog');
                $result = $couponLogMod->querySql($sqls);

                return $result[0]['sums'];
                break;
            case 3:
                $sql = "SELECT id FROM bs_coupon WHERE type=1 and add_time between {$beTime} and {$fiTime}";
                $res = $this->querySql($sql);
                foreach ($res as $k => $v){
                    $secondData[]=$v['id'];
                }
                $secondIds=implode(',',$secondData);
                $sqls = "SELECT count(*) as sums FROM bs_user_coupon WHERE c_id in (".$secondIds.")";
                $userCouponMod = &m('userCoupon');
                $result = $userCouponMod->querySql($sqls);

                $sqlss = "SELECT count(*) as sums FROM bs_coupon_log WHERE coupon_id in (".$secondIds.")";
                $couponLogMod = &m('couponLog');
                $results = $couponLogMod->querySql($sqlss);

                $counts = $result[0]['sums'] - $results[0]['sums'];

                return $counts;
        }
    }
    //统计折扣券张数(传递店铺id)
    public function getCouponCounts($type,$store_id,$time)
    {
        $beTime = strtotime($time . '-' . '01');
        $fiTime = strtotime($time . '-' . '12');

        $where = " and FIND_IN_SET({$store_id}, store_id) and add_time between {$beTime} and {$fiTime}";
        switch ($type){
            case 1://抵扣券已发出的总数
                $sql = "SELECT id FROM bs_coupon WHERE type=1 ".$where;
                $res = $this->querySql($sql);
                foreach ($res as $k => $v){
                    $secondData[]=$v['id'];
                }
                $secondIds=implode(',',$secondData);
                $sqls = "SELECT count(*) as sums FROM bs_user_coupon WHERE c_id in (".$secondIds.")";
                $userCouponMod = &m('userCoupon');
                $result = $userCouponMod->querySql($sqls);
                return $result[0]['sums'];
                break;
            case 2:
                $sql = "SELECT id FROM bs_coupon WHERE type=1".$where;
                $res = $this->querySql($sql);
                foreach ($res as $k => $v){
                    $secondData[]=$v['id'];
                }
                $secondIds=implode(',',$secondData);
                $sqls = "SELECT count(*) as sums FROM bs_coupon_log WHERE coupon_id in (".$secondIds.")";
                $couponLogMod = &m('couponLog');
                $result = $couponLogMod->querySql($sqls);

                return $result[0]['sums'];
                break;
            case 3:
                $sql = "SELECT id FROM bs_coupon WHERE type=1".$where;
                $res = $this->querySql($sql);
                foreach ($res as $k => $v){
                    $secondData[]=$v['id'];
                }
                $secondIds=implode(',',$secondData);
                $sqls = "SELECT count(*) as sums FROM bs_user_coupon WHERE c_id in (".$secondIds.")";
                $userCouponMod = &m('userCoupon');
                $result = $userCouponMod->querySql($sqls);

                $sqlss = "SELECT count(*) as sums FROM bs_coupon_log WHERE coupon_id in (".$secondIds.")";
                $couponLogMod = &m('couponLog');
                $results = $couponLogMod->querySql($sqlss);

                $counts = $result[0]['sums'] - $results[0]['sums'];

                return $counts;
        }
    }
    /**
     * 统计兑换券的张数
     * @param $type
     * @return mixed
     */
    public function getDuiCouponCount($type, $time)
    {
        $beTime = strtotime($time . '-' . '01');
        $fiTime = strtotime($time . '-' . '12');

        switch ($type){
            case 1://抵扣券已发出的总数
                $sql = "SELECT id FROM bs_coupon WHERE type=2 and add_time between {$beTime} and {$fiTime}";
                $res = $this->querySql($sql);
                foreach ($res as $k => $v){
                    $secondData[]=$v['id'];
                }
                $secondIds=implode(',',$secondData);
                $sqls = "SELECT count(*) as sums FROM bs_user_coupon WHERE c_id in (".$secondIds.")";
                $userCouponMod = &m('userCoupon');
                $result = $userCouponMod->querySql($sqls);

                return $result[0]['sums'];
                break;
            case 2:
                $sql = "SELECT id FROM bs_coupon WHERE type=2 and add_time between {$beTime} and {$fiTime}";
                $res = $this->querySql($sql);
                foreach ($res as $k => $v){
                    $secondData[]=$v['id'];
                }
                $secondIds=implode(',',$secondData);
                $sqls = "SELECT count(*) as sums FROM bs_coupon_log WHERE coupon_id in (".$secondIds.")";
                $couponLogMod = &m('couponLog');
                $result = $couponLogMod->querySql($sqls);

                return $result[0]['sums'];
                break;

        }
    }

    /**
     * 单个券码的使用详情  by xt 2019.03.13
     * @param $type
     * @param $store_id
     * @return array
     */
    public function couponRelations($type, $time, $store_id = null)
    {
        $beTime = strtotime($time . '-' . '01');
        $fiTime = strtotime($time . '-' . '12');

        $where = " c.type = {$type}  and c.add_time between {$beTime} and {$fiTime} ";

        if (!empty($store_id)) {
            if ($type == 1) {
                // 抵扣券
                $where .= " and FIND_IN_SET({$store_id}, c.store_id) ";
            } else {
                // 兑换券
                $thirds = $this->changeCoupons($store_id);
                $where .= " and c.room_type_id in ({$thirds})";
            }

        }

        $sql = <<<SQL
                    SELECT
                        c.id,
                        c.coupon_name,
                        c.money,
                        c.discount,
                        c.add_time,
                        c.limit_times,
                        count( uc.id ) AS counts 
                    FROM
                        bs_coupon c
                        LEFT JOIN bs_user_coupon uc ON c.id = uc.c_id 
                    WHERE
                        {$where} 
                    GROUP BY
                        c.id 
                    ORDER BY
                        counts DESC
SQL;

        $totalCoupons = $this->querySql($sql);  // 总发放券

        $sql = <<<SQL
                    SELECT
                        c.id,
                        count( cl.id ) AS counts 
                    FROM
                        bs_coupon c
                        LEFT JOIN bs_coupon_log cl ON c.id = cl.coupon_id 
                    WHERE
                        {$where} 
                    GROUP BY
                        c.id
SQL;
        $usedCoupons = $this->querySql($sql);  // 已使用券

        // 转换数据结构
        $usedCouponsIndex = array();
        foreach ($usedCoupons as $usedCoupon) {
            $usedCouponsIndex[$usedCoupon['id']] = $usedCoupon;
        }

        // 组装数据
        foreach ($totalCoupons as $index => $totalCoupon) {
            $totalCoupons[$index]['used_coupon'] = $usedCouponsIndex[$totalCoupon['id']]['counts'];
            $totalCoupons[$index]['unused_coupon'] = $totalCoupon['counts'] - $usedCouponsIndex[$totalCoupon['id']]['counts'];
            $totalCoupons[$index]['add_time'] = date('Y-m-d H:i', $totalCoupon['add_time']);
        }

        return $totalCoupons;
    }

    /**
     * 兑换券
     * @param $store_id
     * @return string
     */
    public function changeCoupons($store_id)
    {
        // 获取 room_type_id
        $sqls = "select * from bs_store_business where store_id=".$store_id;
        $storeBusinessMod = &m('storebusiness');
        $res = $storeBusinessMod->querySql($sqls);
        foreach ($res as $k => $v){
            $secondData[]=$v['buss_id'];
        }
        $secondIds=implode(',',$secondData);
        $sqlss = "select id from bs_room_type where superior_id in(".$secondIds.")";
        $roomTypeMod=&m('roomType');
        $in = $roomTypeMod->querySql($sqlss);
        foreach ($in as $k => $v){
            $thirdData[]=$v['id'];
        }
        $arrs = array_merge($secondData,$thirdData);
        $thirds=implode(',',$arrs);

        return $thirds;
    }

    /**
     * 券码到期发送消息提醒
     * @param
     * @return bool
     * @author xt
     * @date 2019-03-18
     */
    public function sendSms($phone, $username)
    {
        include_once ROOT_PATH."/includes/AliDy/sendSms.lib.php";
        $params = array();

        $params['PhoneNumbers'] = $phone;
        $params['SignName'] = "艾美睿零售";
        $params['TemplateCode'] = 'SMS_160630304';
        $params['TemplateParam'] = array(
            "name" => $username
        );
        $phoneCode = new sendSms($params);
        $info = $phoneCode->sendSms();
        $info1 = json_decode(json_encode($info),true);

        if ($info1['Message']=='OK'){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 秒转换日期格式
     * @param  [type] $seconds
     * @return [type]
     */
    public function timeString($seconds)
    {
        if ($seconds < 60) {
            return '';
        }

        $days = floor($seconds/(3600*24));
        $seconds = $seconds%(3600*24);
        $hours = floor($seconds/3600);
        $seconds = $seconds%3600;
        $minutes = floor($seconds/60);

        return $days . '天' . $hours . '小时' . $minutes . '分';
    }

    /**
     * 预警时间数组
     * @return array
     */
    public function warningTime()
    {
        return array(
            array(
                'id' => 86400,
                'name' => '1天',
            ),
            array(
                'id' => 86400 * 2,
                'name' => '2天',
            ),
            array(
                'id' => 86400 * 3,
                'name' => '3天',
            ),
            array(
                'id' => 86400 * 4,
                'name' => '4天',
            ),
            array(
                'id' => 86400 * 5,
                'name' => '5天',
            ),
            array(
                'id' => 86400 * 6,
                'name' => '6天',
            ),
            array(
                'id' => 86400 * 7,
                'name' => '7天',
            ),
        );
    }

}
?>