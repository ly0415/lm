<?php
/**
 * 余额充值券
 * User: xt
 * Date: 2019/3/7
 * Time: 14:51
 */

class BalanceRechargeCouponMod extends BaseMod
{
    /**
     * BalanceRechargeCouponMod constructor.
     */
    public function __construct()
    {
        parent::__construct('balance_recharge_coupon');
    }

    /**
     * 是否存在 sn
     * @param $sn
     * @return bool
     */
    public function exists($sn)
    {
        $data = $this->getData(array(
            'cond' => "mark = 1 and sn = {$sn}",
            'fields' => "id",
        ));

        return empty($data) ? false : true;
    }

    /**
     * 生成随机字符串
     * @param int $length
     * @param string $char
     * @return string
     */
    function str_rand($length = 8, $char = '0123456789abcdefghjklmnpqrstuvwxyz')
    {
        $string = '';
        for ($i = $length; $i > 0; $i--) {
            $string .= $char[mt_rand(0, strlen($char) - 1)];
        }
        return $string;
    }

    /**
     * 生成券码
     * @return bool|string
     */
    public function findAvailableSn()
    {
        do {
            $sn = 'LM' . $this->str_rand();
        } while (
            $this->exists($sn)
        );

        return $sn;
    }

    /**
     * 图表--统计
     * @param string $tm
     * @param int $op 1 字符串  0 数组
     * @param array $timeSetArr
     * @param string $tips
     * @return array
     * @author xutao
     * @date 2018/07/30
     */
    public function couponCount($tm = 'month', $op = 0, $timeSetArr = array(), $tips = '')
    {
        //获取时间组件
        $result = array();
        switch ($tm) {
            case 'month':
                $timeArr = array();
                $month_1 = mktime(0, 0, 0, date('m'), 1, date('Y'));
                $diff = 24 * 3600;
                for ($i = 1; $i <= date('t'); $i++) {
                    $timeArr = array_merge($timeArr, array(date('Y/m/') . $i));
                    $timesArr[$i][0] = $month_1 + $diff * ($i - 1);
                    $timesArr[$i][1] = ($month_1 + $diff * $i) - 1;
                }
                $result = $this->toCouponTime($timesArr);
                $result['xAxis'] = $timeArr;
                break;
            case 'year':
                $timeArr = array();
                for ($i = 1; $i <= 12; $i++) {
                    $timeArr = array_merge($timeArr, array(date('Y/') . $i));
                    $timesArr[$i][0] = mktime(0, 0, 0, $i, 1, date('Y'));
                    $timesArr[$i][1] = mktime(23, 59, 59, $i, date("t", strtotime("{date('Y')}-$i")), date('Y'));
                }
                $result = $this->toCouponTime($timesArr);
                $result['xAxis'] = $timeArr;
                break;
            case 'setting':
                $timeArr = array();
                $start_time = strtotime($timeSetArr['start_time']);
                $end_time = strtotime($timeSetArr['end_time'] . '23:59:55');
                $sy = date('Y', $start_time);
                $ey = date('Y', $end_time);
                $sm = date('m', $start_time);
                $em = date('m', $end_time);
                $sd = date('d', $start_time);
                $ed = date('d', $end_time);

                //不是同一年
                if ($sy != $ey) {
                    for ($i = 0; $i <= ($ey - $sy); $i++) {
                        $timeArr = array_merge($timeArr, array($sy + $i));
                        if ($i == 0) {
                            $timesArr[$i][0] = $start_time;
                        } else {
                            $timesArr[$i][0] = mktime(0, 0, 0, 1, 1, $sy + $i);
                        }
                        if ($i == ($ey - $sy)) {
                            $timesArr[$i][1] = $end_time;
                        } else {
                            $timesArr[$i][1] = mktime(0, 0, 0, 1, 1, $sy + $i + 1) - 1;
                        }
                    }
                    //同年不同月
                } elseif ($sy == $ey && $sm != $em) {
                    for ($i = 0; $i <= ($em - $sm); $i++) {
                        $timeArr = array_merge($timeArr, array($sm + $i));
                        if ($i == 0) {
                            $timesArr[$i][0] = $start_time;
                        } else {
                            $timesArr[$i][0] = mktime(0, 0, 0, $sm + $i, 1, $sy);
                        }
                        if ($i == ($em - $sm)) {
                            $timesArr[$i][1] = $end_time;
                        } else {
                            $timesArr[$i][1] = mktime(0, 0, 0, $sm + $i + 1, 1, $sy) - 1;
                        }
                    }
                    //同月不同日
                } elseif ($sy == $ey && $sm == $em) {
                    $diff = 24 * 3600;
                    for ($i = 0; $i <= ($ed - $sd); $i++) {
                        $timeArr = array_merge($timeArr, array($sd + $i));
                        $timesArr[$i][0] = $start_time + ($i * $diff);
                        $timesArr[$i][1] = $start_time + (($i + 1) * $diff) - 1;
                    }
                }
                // echo '<pre>';print_r($timeArr);die;
                $result = $this->toCouponTime($timesArr);
                $result['xAxis'] = $timeArr;
                break;
        }
        return $result;
    }

    /**
     * @param array $timesArr
     * @param int $op 1 字符串  0 数组
     * @return array
     * @author xutao
     * @date 2019/03/08
     */
    public function toCouponTime($timesArr = array(), $op = 0)
    {
        $result = $data = array();
        foreach ($timesArr as $key => $value) {
            $count = $this->getCount(array(
                'cond' => ' mark = 1 AND is_use = 2 AND add_time BETWEEN ' . $value[0] . ' AND ' . $value[1]
            ));
            $data = array_merge($data, array($count));
        }
        if ($op) {
            $result['num'] = implode(',', $data);
        } else {
            $result['num'] = $data;
        }
        return $result;
    }

    /**
     * 时间范围内金额
     * @param $startTime
     * @param $endTime
     * @return array
     */
    public function totalAmount($startTime, $endTime)
    {
        $total_amount = $this->getOne(array(
            'cond' => " mark =1 and is_use = 2 and add_time between {$startTime} and {$endTime}",
            'fields' => 'sum(money) as total_amount',
        ));

        return $total_amount;
    }
}