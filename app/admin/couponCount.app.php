<?php
/**
 * 电子券统计
 * @author tangp
 * @date 2019-01-24
 */
if (!defined('IN_ECM')){
    die('Forbidden');
}
class CouponCountApp extends BackendApp
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {

    }

    /**
     * 电子券统计
     * @author tangp
     * @date 2019-01-24
     */
    public function index()
    {
        $area_id = $_REQUEST['area_id'] ? htmlspecialchars(trim($_REQUEST['area_id'])) : '';
        $store_id = $_REQUEST['store_id'] ? htmlspecialchars(trim($_REQUEST['store_id'])) : '';
        $tips = !empty($_REQUEST['op']) ? htmlspecialchars(trim($_REQUEST['op'])) : '';
        $start_time = $_REQUEST['start_time'] ? htmlspecialchars(trim($_REQUEST['start_time'])) : date('Y');
        $service_store_id = $_REQUEST['store_id'];

        $beTime = strtotime($start_time . '-' . '01');
        $fiTime = strtotime($start_time . '-' . '12');
        if ($tips == 'dui'){
            // echo '<pre>';print_r($_REQUEST);
            $storeCateMod = &m('storeCate');
            $roomTypeMod=&m('roomType');
            $couponMod = &m('coupon');
            $areaArr = $storeCateMod->getAreaArr(1, $this->lang_id);
            $areaOption = make_option($areaArr, $area_id);
            $this->assign('areaOption', $areaOption);
            if ($area_id) {
                $this->assign('area_id', $area_id);
                if ($store_id) {
                    $this->assign('store_id', $store_id);
                }
                //获取区域店铺
                $storeMod = &m('store');
                $storeArr = $storeMod->getStoreArr($area_id, 1);
                $storeOption = make_option($storeArr, $store_id);
                $this->assign('storeOption', $storeOption);
            }

            $this->assign('service_area_id',$_REQUEST['area_id']);
            $this->assign('service_store_id',$_REQUEST['store_id']);
            $this->assign('service_top_id',$_REQUEST['service_top_id']);
            $this->assign('service_second_id',$_REQUEST['service_second_id']);

            // 区域列表
            $area_data = &m('storeCate')->getAreaArr(1,$this->lang_id);

            $service_area_data = array_map(function ($i, $m) {
                return array('id' => $i, 'name' => $m);
            }, array_keys($area_data), $area_data);

            $this->assign('service_area_data', $service_area_data);

            // 店铺列表
            $service_store_data = &m('store')->getStoreArr($_REQUEST['area_id'], 1);
            $service_store_data = &m('api')->convertArrForm($service_store_data);

            $this->assign('service_store_data', $service_store_data);

            // 一级分类
            $service_top_data = &m('api')->getTop($_REQUEST['store_id']);

            $this->assign('service_top_data', $service_top_data);

            // 二级分类
            $service_second_data = &m('api')->getSecond($_REQUEST['service_top_id']);

            $this->assign('service_second_data', $service_second_data);
            if (!empty($service_store_id)){
                $sqls = "select * from bs_store_business where store_id=".$service_store_id;
                $storeBusinessMod = &m('storebusiness');
                $res = $storeBusinessMod->querySql($sqls);
                foreach ($res as $k => $v){
                    $secondData[]=$v['buss_id'];
                }
                $secondIds=implode(',',$secondData);
                $sqlss = "select id from bs_room_type where superior_id in(".$secondIds.")";
                $in = $roomTypeMod->querySql($sqlss);
                foreach ($in as $k => $v){
                    $thirdData[]=$v['id'];
                }
                $arr = array_merge($secondData,$thirdData);
                $thirds=implode(',',$arr);
                // $sql="SELECT id  FROM bs_coupon WHERE room_type_id in (". $thirds.") AND type =2";
                $sql="SELECT id  FROM bs_coupon WHERE room_type_id in (". $thirds.") AND type =2 and add_time between {$beTime} and {$fiTime}"; // by xt
                $rr = $couponMod->querySql($sql);
                foreach ($rr as $kk => $vv){
                    $secondDatas[] = $vv['id'];
                }
                $secondsIds=implode(',',$secondDatas);
                $sql2 = "SELECT count(*) as sums FROM bs_user_coupon WHERE c_id in (".$secondsIds.")";
                $userCouponMod = &m('userCoupon');
                $result = $userCouponMod->querySql($sql2);
                $sql3 = "SELECT COUNT(*) as sums FROM bs_coupon_log WHERE coupon_id in (".$secondsIds.")";
                $couponLogMod = &m('couponLog');
                $results = $couponLogMod->querySql($sql3);
                $sendSums = $result[0]['sums'];
                $useSums = $results[0]['sums'];
                $counts = $sendSums-$useSums;

                // 单个券码的使用详情 by xt 2019.03.13
                $coupons = $couponMod->couponRelations(2, $start_time, $service_store_id);

            }else{
                $couponMod = &m('coupon');
                $sendSums = $couponMod->getDuiCouponCount(1, $start_time);
                //找出抵扣券已使用的总张数
                $useSums = $couponMod->getDuiCouponCount(2, $start_time);
                //找出抵扣券剩余的张数
                $counts = $sendSums - $useSums;

                // 单个券码的使用详情 by xt 2019.03.13
                $coupons = $couponMod->couponRelations(2, $start_time);
            }

            $stores = array(
                //1月份
                '0' => array(
                    'kaishi_time' => (date($start_time.'-m-01', strtotime('-11 month'))),
                    'jieshu_time' => (date($start_time.'-m-t 23:59:59', strtotime('-11 month'))),
                ),
                //2月份
                '1' => array(
                    'kaishi_time' => (date($start_time.'-m-01', strtotime('-10 month'))),
                    'jieshu_time' => (date($start_time.'-m-t 23:59:59', strtotime('-10 month'))),
                ),
                //3月份
                '2' => array(
                    'kaishi_time' => (date($start_time.'-m-01', strtotime('-9 month'))),
                    'jieshu_time' => (date($start_time.'-m-t 23:59:59', strtotime('-9 month'))),
                ),
                //4月份
                '3' => array(
                    'kaishi_time' => (date($start_time.'-m-01', strtotime('-8 month'))),
                    'jieshu_time' => (date($start_time.'-m-t 23:59:59', strtotime('-8 month'))),
                ),
                //5月份
                '4' => array(
                    'kaishi_time' => (date($start_time.'-m-01', strtotime('-7 month'))),
                    'jieshu_time' => (date($start_time.'-m-t 23:59:59', strtotime('-7 month'))),
                ),
                //6月份
                '5' => array(
                    'kaishi_time' => (date($start_time.'-m-01', strtotime('-6 month'))),
                    'jieshu_time' => (date($start_time.'-m-t 23:59:59', strtotime('-6 month'))),
                ),
                //7月份
                '6' => array(
                    'kaishi_time' => (date($start_time.'-m-01', strtotime('-5 month'))),
                    'jieshu_time' => (date($start_time.'-m-t 23:59:59', strtotime('-5 month'))),
                ),
                //8月份
                '7' => array(
                    'kaishi_time' => (date($start_time.'-m-01', strtotime('-4 month'))),
                    'jieshu_time' => (date($start_time.'-m-t 23:59:59', strtotime('-4 month'))),
                ),
                //9月份
                '8' => array(
                    'kaishi_time' => (date($start_time.'-m-01', strtotime('-3 month'))),
                    'jieshu_time' => (date($start_time.'-m-t 23:59:59', strtotime('-3 month'))),
                ),
                //10月份
                '9' => array(
                    'kaishi_time' => (date($start_time.'-m-01', strtotime('-2 month'))),
                    'jieshu_time' => (date($start_time.'-m-t 23:59:59', strtotime('-2 month'))),
                ),
                //11月份
                '10' => array(
                    'kaishi_time' => (date($start_time.'-m-01', strtotime('-1 month'))),
                    'jieshu_time' => (date($start_time.'-m-t 23:59:59', strtotime('-1 month'))),
                ),
                //12月份
                '11' => array(
                    'kaishi_time' => (date($start_time.'-m-01', strtotime(date('Y-m-d')))),
                    'jieshu_time' => (date($start_time.'-m-t 23:59:59', strtotime(date('Y-m-d ')))),
                ),
            );

            $arr = array();
            $att1 = array();
            $att2 = array();
            $att3 = array();
            foreach ($stores as $key => $val) {
                $str = $val['kaishi_time'];
                $strs = substr($val['kaishi_time'], 0, 7);
                $arr[] = $strs;
                $ks_time = strtotime($val['kaishi_time']);
                $js_time = strtotime($val['jieshu_time']);
                if ($_REQUEST['store_id']){
                    $sqls = "select * from bs_store_business where store_id=".$service_store_id;
                    $storeBusinessMod = &m('storebusiness');
                    $res = $storeBusinessMod->querySql($sqls);
                    foreach ($res as $k => $v){
                        $secondData[]=$v['buss_id'];
                    }
                    $secondIds=implode(',',$secondData);
                    $sqlss = "select id from bs_room_type where superior_id in(".$secondIds.")";
                    $in = $roomTypeMod->querySql($sqlss);
                    foreach ($in as $k => $v){
                        $thirdData[]=$v['id'];
                    }
                    $arrs = array_merge($secondData,$thirdData);
                    $thirds=implode(',',$arrs);

                    $sql="SELECT id  FROM bs_coupon WHERE room_type_id in (". $thirds.") AND type =2";


                    $rr = $couponMod->querySql($sql);
                    foreach ($rr as $kk => $vv){
                        $secondDatas[] = $vv['id'];
                    }
                    $secondsIds=implode(',',$secondDatas);
                    $sql2 = "SELECT count(*) as sums FROM bs_user_coupon WHERE c_id in (".$secondsIds.") AND add_time > {$ks_time} AND add_time < {$js_time}";
                    $userCouponMod = &m('userCoupon');
                    $result = $userCouponMod->querySql($sql2);
                    $att1[] = $result[0]['sums'];
                    $sql3 = "SELECT COUNT(*) as sums FROM bs_coupon_log WHERE coupon_id in (".$secondsIds.") AND add_time > {$ks_time} AND add_time < {$js_time}";
                    $couponLogMod = &m('couponLog');
                    $results = $couponLogMod->querySql($sql3);
                    $att2[] = $results[0]['sums'];
                    $att3[] = $result[0]['sums'] - $results[0]['sums'];
                }else{
                    //抵扣券数量
                    $sql = "SELECT id FROM bs_coupon WHERE type=2";
                    $couponMod = &m('coupon');
                    $res = $couponMod->querySql($sql);
                    foreach ($res as $k => $v){
                        $secondData[]=$v['id'];
                    }
                    $secondIds=implode(',',$secondData);
                    $sqls = "SELECT count(*) as sums FROM bs_user_coupon WHERE c_id in (".$secondIds.") and add_time > {$ks_time} and add_time < {$js_time}";
                    $userCouponMod = &m('userCoupon');
                    $result = $userCouponMod->querySql($sqls);
                    $att1[] = $result[0]['sums'];
                    //抵扣券发出的数量
                    $sqlss = "SELECT count(*) as sums FROM bs_coupon_log WHERE coupon_id in (".$secondIds.") and add_time > {$ks_time} and add_time < {$js_time}";
                    $couponLogMod = &m('couponLog');
                    $rr = $couponLogMod->querySql($sqlss);
                    $att2[] = $rr[0]['sums'];
                    //抵扣券剩余的数量
                    $att3[] = $result[0]['sums'] - $rr[0]['sums'];
                }
            }
            foreach ($arr as $key => $val){
                $arr[$key] = '"' . $val . '"';
            }
            foreach ($att1 as $key => $val){
                $att1[$key] = '"' . $val . '"';
            }
            foreach ($att2 as $key => $val){
                $att2[$key] = '"' . $val . '"';
            }
            foreach ($att3 as $key => $val){
                $att3[$key] = '"' . $val . '"';
            }
            $sting = implode(',', $arr);
            $s1 = implode(',', $att1);
            $s2 = implode(',', $att2);
            $s3 = implode(',', $att3);
            $this->assign('sendSums',$sendSums);
            $this->assign('useSums',$useSums);
            $this->assign('counts',$counts);
            $this->assign('start_time',$start_time);
            $this->assign('s1', $s1);
            $this->assign('s2', $s2);
            $this->assign('s3', $s3);
            $this->assign('sting', $sting);
            $this->assign('stores', $stores);
            $this->assign('coupons', $coupons);
            // $this->display('couponCount/duiCoupon.html');
            $this->display('couponCount/change.html');
        }else{
            // echo '<pre>';print_r($_REQUEST);
            //区域店铺展示
            $storeCateMod = &m('storeCate');
            $areaArr = $storeCateMod->getAreaArr(1, $this->lang_id);
            $areaOption = make_option($areaArr, $area_id);
            $this->assign('areaOption', $areaOption);
            if ($area_id) {
                $this->assign('area_id', $area_id);
                if ($store_id) {
                    $this->assign('store_id', $store_id);
                }
                //获取区域店铺
                $storeMod = &m('store');
                $storeArr = $storeMod->getStoreArr($area_id, 1);
                $storeOption = make_option($storeArr, $store_id);
                $this->assign('storeOption', $storeOption);
            }
            $stores = array(
                //1月份
                '0' => array(
                    'kaishi_time' => (date($start_time.'-m-01', strtotime('-11 month'))),
                    'jieshu_time' => (date($start_time.'-m-t 23:59:59', strtotime('-11 month'))),
                ),
                //2月份
                '1' => array(
                    'kaishi_time' => (date($start_time.'-m-01', strtotime('-10 month'))),
                    'jieshu_time' => (date($start_time.'-m-t 23:59:59', strtotime('-10 month'))),
                ),
                //3月份
                '2' => array(
                    'kaishi_time' => (date($start_time.'-m-01', strtotime('-9 month'))),
                    'jieshu_time' => (date($start_time.'-m-t 23:59:59', strtotime('-9 month'))),
                ),
                //4月份
                '3' => array(
                    'kaishi_time' => (date($start_time.'-m-01', strtotime('-8 month'))),
                    'jieshu_time' => (date($start_time.'-m-t 23:59:59', strtotime('-8 month'))),
                ),
                //5月份
                '4' => array(
                    'kaishi_time' => (date($start_time.'-m-01', strtotime('-7 month'))),
                    'jieshu_time' => (date($start_time.'-m-t 23:59:59', strtotime('-7 month'))),
                ),
                //6月份
                '5' => array(
                    'kaishi_time' => (date($start_time.'-m-01', strtotime('-6 month'))),
                    'jieshu_time' => (date($start_time.'-m-t 23:59:59', strtotime('-6 month'))),
                ),
                //7月份
                '6' => array(
                    'kaishi_time' => (date($start_time.'-m-01', strtotime('-5 month'))),
                    'jieshu_time' => (date($start_time.'-m-t 23:59:59', strtotime('-5 month'))),
                ),
                //8月份
                '7' => array(
                    'kaishi_time' => (date($start_time.'-m-01', strtotime('-4 month'))),
                    'jieshu_time' => (date($start_time.'-m-t 23:59:59', strtotime('-4 month'))),
                ),
                //9月份
                '8' => array(
                    'kaishi_time' => (date($start_time.'-m-01', strtotime('-3 month'))),
                    'jieshu_time' => (date($start_time.'-m-t 23:59:59', strtotime('-3 month'))),
                ),
                //10月份
                '9' => array(
                    'kaishi_time' => (date($start_time.'-m-01', strtotime('-2 month'))),
                    'jieshu_time' => (date($start_time.'-m-t 23:59:59', strtotime('-2 month'))),
                ),
                //11月份
                '10' => array(
                    'kaishi_time' => (date($start_time.'-m-01', strtotime('-1 month'))),
                    'jieshu_time' => (date($start_time.'-m-t 23:59:59', strtotime('-1 month'))),
                ),
                //12月份
                '11' => array(
                    'kaishi_time' => (date($start_time.'-m-01', strtotime(date('Y-m-d')))),
                    'jieshu_time' => (date($start_time.'-m-t 23:59:59', strtotime(date('Y-m-d ')))),
                ),
            );
            //找出抵扣券已发送的总张数
            $couponMod = &m('coupon');
            if(!empty($store_id)){
                $sendSums = $couponMod->getCouponCounts(1,$store_id, $start_time);
                //找出抵扣券已使用的总张数
                $useSums = $couponMod->getCouponCounts(2,$store_id, $start_time);
                //找出抵扣券剩余的张数
                $counts = $couponMod->getCouponCounts(3,$store_id, $start_time);

                // 单个券码的使用详情 by xt 2019.03.13
                $coupons = $couponMod->couponRelations(1, $start_time, $store_id);
            }else{
                $sendSums = $couponMod->getCouponCount(1, $start_time);
                //找出抵扣券已使用的总张数
                $useSums = $couponMod->getCouponCount(2, $start_time);
                //找出抵扣券剩余的张数
                $counts = $couponMod->getCouponCount(3, $start_time);

                // 单个券码的使用详情 by xt 2019.03.13
                $coupons = $couponMod->couponRelations(1, $start_time);
            }
            $arr = array();
            $att1 = array();
            $att2 = array();
            $att3 = array();
            foreach ($stores as $key => $val) {
                $str = $val['kaishi_time'];
                $strs = substr($val['kaishi_time'], 0, 7);
                $arr[] = $strs;
                $ks_time = strtotime($val['kaishi_time']);
                $js_time = strtotime($val['jieshu_time']);
                if (!empty($store_id)){
                    $where = " and FIND_IN_SET({$store_id}, store_id) ";
                    //抵扣券数量
                    $sql = "SELECT id FROM bs_coupon WHERE type=1".$where;
                    $couponMod = &m('coupon');
                    $res = $couponMod->querySql($sql);
                    foreach ($res as $k => $v){
                        $secondData[]=$v['id'];
                    }
                    $secondIds=implode(',',$secondData);
                    $sqls = "SELECT count(*) as sums FROM bs_user_coupon WHERE c_id in (".$secondIds.") and add_time > {$ks_time} and add_time < {$js_time}";
                    $userCouponMod = &m('userCoupon');
                    $result = $userCouponMod->querySql($sqls);
                    $att1[] = $result[0]['sums'];
                    //抵扣券发出的数量
                    $sqlss = "SELECT count(*) as sums FROM bs_coupon_log WHERE coupon_id in (".$secondIds.") and add_time > {$ks_time} and add_time < {$js_time}";
                    $couponLogMod = &m('couponLog');
                    $rr = $couponLogMod->querySql($sqlss);
                    $att2[] = $rr[0]['sums'];
                    //抵扣券剩余的数量
                    $att3[] = $result[0]['sums'] - $rr[0]['sums'];
                }else{
                    //抵扣券数量
                    $sql = "SELECT id FROM bs_coupon WHERE type=1";
                    $couponMod = &m('coupon');
                    $res = $couponMod->querySql($sql);
                    foreach ($res as $k => $v){
                        $secondData[]=$v['id'];
                    }
                    $secondIds=implode(',',$secondData);
                    $sqls = "SELECT count(*) as sums FROM bs_user_coupon WHERE c_id in (".$secondIds.") and add_time > {$ks_time} and add_time < {$js_time}";
                    $userCouponMod = &m('userCoupon');
                    $result = $userCouponMod->querySql($sqls);
                    $att1[] = $result[0]['sums'];
                    //抵扣券发出的数量
                    $sqlss = "SELECT count(*) as sums FROM bs_coupon_log WHERE coupon_id in (".$secondIds.") and add_time > {$ks_time} and add_time < {$js_time}";
                    $couponLogMod = &m('couponLog');
                    $rr = $couponLogMod->querySql($sqlss);
                    $att2[] = $rr[0]['sums'];
                    //抵扣券剩余的数量
                    $att3[] = $result[0]['sums'] - $rr[0]['sums'];
                }
            }
            foreach ($arr as $key => $val){
                $arr[$key] = '"' . $val . '"';
            }
            foreach ($att1 as $key => $val){
                $att1[$key] = '"' . $val . '"';
            }
            foreach ($att2 as $key => $val){
                $att2[$key] = '"' . $val . '"';
            }
            foreach ($att3 as $key => $val){
                $att3[$key] = '"' . $val . '"';
            }
            $sting = implode(',', $arr);
            $s1 = implode(',', $att1);
            $s2 = implode(',', $att2);
            $s3 = implode(',', $att3);
            $this->assign('sendSums',$sendSums);
            $this->assign('useSums',$useSums);
            $this->assign('counts',$counts);
            $this->assign('start_time',$start_time);
            $this->assign('s1', $s1);
            $this->assign('s2', $s2);
            $this->assign('s3', $s3);
            $this->assign('sting', $sting);
            $this->assign('stores', $stores);
            $this->assign('coupons', $coupons);
            // $this->display('couponCount/index.html');
            $this->display('couponCount/deduction.html');
        }

    }

    /**
     * 券码使用者展示
     */
    public function showUserCoupons()
    {
        $coupon_id = empty($_REQUEST['coupon_id']) ? 0 : htmlspecialchars(trim($_REQUEST['coupon_id']));
        $type = empty($_REQUEST['type']) ? 0 : htmlspecialchars(trim($_REQUEST['type']));
        $warning_time = empty($_REQUEST['warning_time']) ? 0 : htmlspecialchars(trim($_REQUEST['warning_time']));

        $userCouponMod = &m('userCoupon');
        $userCoupons = $userCouponMod->userCoupons($coupon_id, $warning_time); // 根据 coupon_id 获取对应的 user_coupon 数据

        $couponMod = &m('coupon');
        $coupons = array_map(function ($item) use($couponMod) {
            $item['warning_time'] = $couponMod->timeString($item['end_time'] - time());
            return $item;
        }, $userCoupons['list']);

        $this->assign('warningTime', $couponMod->warningTime());  // 预警时间
        $this->assign('warning_time', $warning_time);
        $this->assign('coupon_id', $coupon_id);
        $this->assign('type', $type);
        $this->assign('coupons', $coupons);
        $this->assign('type', $type);
        $this->assign('page_html', $userCoupons['ph']);
        $this->display('couponCount/show.html');
    }

    /**
     * 券码到期短信提醒
     */
    public function sendSms()
    {
        $phone = empty($_REQUEST['phone']) ? 0 : htmlspecialchars(trim($_REQUEST['phone']));

        $users = &m('user')->getData(array(
                'cond' => " phone in ({$phone}) ",
                'fields' => 'phone, username',
            ));

        // $phones = array_unique(explode(',', $phone));

        if (empty($users)) {
            $this->setData(array(), 0, '发送失败');
        }

        $couponMod = &m('coupon');

        foreach ($users as $user) {
            $res = $couponMod->sendSms($user['phone'], $user['username']);

            if (!$res) {
                $this->setData(array(), 0, '发送失败');
            }
        }

        $this->setData(array(), 1, '发送成功');
    }

}