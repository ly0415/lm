<?php

/**
 * Created by PhpStorm.
 * User: wangh
 * Date: 2017/11/16
 * Time: 15:44
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class balanceStatApp extends BackendApp {


    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    public function index() {
//        print_r($_REQUEST);exit;
        $area_id = $_REQUEST['area_id'] ? htmlspecialchars(trim($_REQUEST['area_id'])) : '';
        $store_id = $_REQUEST['store_id'] ? htmlspecialchars(trim($_REQUEST['store_id'])) : '';
        $start_time = $_REQUEST['start_time'] ? htmlspecialchars(trim($_REQUEST['start_time'])) : '';
        $userMod = &m('user');
         $zxInfo = $_REQUEST['zxInfo'] ? htmlspecialchars(trim($_REQUEST['zxInfo'])) : '';
        //余额统计折现图//余额统计柱状图
        if ($zxInfo) {
            $zxInfo = $this->zxTransactionTrend($area_id, $store_id, $zxInfo);
            $this->setData(array('trendInfo' => $zxInfo));
        }else{
        if($start_time){
        $timeArr = array();
        for ($i = 1; $i <= 12; $i++) {
            $timeArr = array_merge($timeArr, array($start_time .'/'. $i));
            $timesArr[$i][0] = mktime(0, 0, 0, $i, 1, $start_time);
            $timesArr[$i][1] = mktime(23, 59, 59, $i, date('t'), $start_time);
         }  
            $result = $this->qxTransactionTrend($timesArr, $store_cate_id, $store_id);
            $result['xAxis'] = implode(',', $timeArr);
            $this->assign('zxInfo_a', $result[a]);
            $this->assign('zxInfo_b', $result[b]);
            $this->assign('zxInfo_c', $result[c]);
            $this->assign('zxInfo_d', $result[d]);
            $this->assign('zxInfo_xAxis', $result[xAxis]);
        }else{
//           $start_time= date('Y');  
           $zxInfo = $this->zxTransactionTrend($area_id, $store_id);
           $zxInfo['xAxis'] = implode(',', $zxInfo['xAxis']);
           $this->assign('zxInfo_a', $zxInfo[a]);
           $this->assign('zxInfo_b', $zxInfo[b]);
           $this->assign('zxInfo_c', $zxInfo[c]);
           $this->assign('zxInfo_d', $zxInfo[d]);
           $this->assign('zxInfo_xAxis', $zxInfo[xAxis]);
            }
        }
                      
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
        $where = 'where 1 =1';
        if ($store_id > 0) {
            $store_sql = 'SELECT  id FROM  bs_user  where  mark =1 and is_use = 1 and store_id=' . $store_id;
            $store_res = $userMod->querySql($store_sql);
            $storeid = implode(',', $this->arrayColumn($store_res, "id"));
            $where .= " AND add_user in (" . $storeid . ")";
        } else {
            if ($area_id > 0) {
                $store_id_sql = 'SELECT  id FROM  bs_store  where is_open = 1 and store_cate_id=' . $area_id;
                ;
                $res_store_id = $userMod->querySql($store_id_sql);
                $storeids = implode(',', $this->arrayColumn($res_store_id, "id"));
                $area_sql = 'SELECT  id FROM  bs_user  where  mark =1 and is_use = 1 and store_id in(' . $storeids . ')';
                $area_res = $userMod->querySql($area_sql);
                $areaid = implode(',', $this->arrayColumn($area_res, "id"));
                $where .= " AND add_user in (" . $areaid . ")";
            }
        }
        $jyInfo = $_REQUEST['jyInfo'] ? htmlspecialchars(trim($_REQUEST['jyInfo'])) : '';
        //余额统计饼图
        if ($jyInfo) {
            $trendInfo = $this->getTransactionTrend($area_id, $store_id, $jyInfo);
            $this->setData(array('trendInfo' => $trendInfo));
        }else{
            if($start_time){
                $timesArr = array();
                $timesArr[0] = strtotime(date($start_time,time())."-1"."-1"); //本年开始
                $timesArr[1] = strtotime(date($start_time,time())."-12"."-31"."23"."59"."59"); //本年结束
                $result = $this->toTransactionTrend($timesArr, $area_id, $store_id);
                $this->assign('trendInfo_a', $result[a][0]);
                $this->assign('trendInfo_b', $result[b][0]);
                $this->assign('trendInfo_c', $result[c][0]);
                $this->assign('trendInfo_d', $result[d][0]);  
            }else{
                $trendInfo = $this->getTransactionTrend($area_id, $store_id);
                $this->assign('trendInfo_a', $trendInfo[a][0]);
                $this->assign('trendInfo_b', $trendInfo[b][0]);
                $this->assign('trendInfo_c', $trendInfo[c][0]);
                $this->assign('trendInfo_d', $trendInfo[d][0]);  
            }    
        }
        $this->assign('start_time', $start_time);  
        $baseD = $this->getData($where,$start_time);
        $this->assign('baseD', $baseD);
        $this->display('balanceStat/index_1.html');
    }
    
   /**
     * 获取页内数据
     * @author wangshuo
     * @date 2018-12-21
     */
    public function getData($where,$start_time) {
        if($start_time){
            $begin_year = strtotime(date($start_time,time())."-1"."-1"); //本年开始
            $end_year = strtotime(date($start_time,time())."-12"."-31"."23"."59"."59"); //本年结束
            $where1 =' and add_time BETWEEN '.$begin_year.' AND '.$end_year;
        }
        $data = array();
        $userMod = &m('user');
        //会员余额总数
        $sql1 = 'SELECT  sum(c_money)  AS c_money  FROM  bs_amount_log ' . $where .$where1 . ' and type in (1,4,5) and status=2';
        $res1 = $userMod->querySql($sql1); 
        $data['chongZhi'] = $res1[0]['c_money'] ;
        if($data['chongZhi']==''){
            $data['chongZhi']=0;
        }
        //会员赠送余额总数
        $s_sql = 'SELECT  sum(c_money)  AS c_money  FROM  bs_amount_log ' . $where . $where1 . ' and  type =3 and status=4';
        $s_res = $userMod->querySql($s_sql);
        $cs_sql = 'SELECT  point_rule_id  FROM  bs_amount_log ' . $where . $where1 . ' and  type in (1,4) and status = 2';
        $cs_res = $userMod->querySql($cs_sql);
        foreach ($cs_res as $k => $v) {
        $cst_sql = 'SELECT  sum(s_money)  AS s_money   FROM  bs_recharge_point   where id ='.$v['point_rule_id'];
        $cst_res = $userMod->querySql($cst_sql);
        $s_money['s_money'] += $cst_res[0]['s_money'];
        }
        $data['zengSong'] = $s_res[0]['c_money'] + $s_money['s_money'];
        //消费金额总数
        $sql2 = 'SELECT  sum(c_money) AS c_money  FROM  bs_amount_log  ' . $where . $where1 . ' and   type = 2';
        $res2 = $userMod->querySql($sql2);        
        if($res2[0]['c_money']==''){
           $data['xiaoFei'] = 0;  
        }else{
        $data['xiaoFei'] = $res2[0]['c_money'];
        }
        //消费完余额总数
        $data['new_money'] = $res1[0]['c_money'] + $s_res[0]['c_money'] + $s_money['s_money'] - $res2[0]['c_money'];
        return $data;
    }

    /**
     * 获取启用的站点
     * @author wangshuo
     * @date 2019-03-22
     */
    public function getUseStore() {
        $storeMod = &m('store');
        $sql = 'SELECT  c.id,l.store_name  FROM  ' . DB_PREFIX . 'store  as c
                 left join  ' . DB_PREFIX . 'store_lang as l on  c.id = l.store_id
                 WHERE c.is_open = 1 and l.distinguish = 0 and  l.lang_id =' . $this->lang_id . '  order by c.id';
        $rs = $storeMod->querySql($sql);
        return $rs;
    }

    
    
    
    
    
    
     /**
     * 余额统计饼图
     * @param $store_cate_id    区域ID
     * @param $store_id         店铺ID
     * @param $tm               默认每周
     * @author: wangshuo    
     * @date  : 2019-3-21
     */
    public function getTransactionTrend($store_cate_id = 0, $store_id = 0, $tm = 'week') {
        //获取时间组件
        $result = array();
        switch ($tm) {
            case 'week':
                $timesArr = array();
                $y = date('Y', time());
                $w = date('W', time()); 
                $weekStart = date("Y-m-d", strtotime("{$y}-W{$w}-1"));
                $endThisweek=time();
                $timesArr[0] = strtotime($weekStart);
                $timesArr[1] = $endThisweek;
                $result = $this->toTransactionTrend($timesArr, $store_cate_id, $store_id);
                break;
            case 'month':
                $timesArr = array();
                $month_1 = mktime(0, 0, 0, date('m'), 1, date('Y'));
                $endThisweek=time();
                $timesArr[0] = $month_1;
                $timesArr[1] = $endThisweek;
                $result = $this->toTransactionTrend($timesArr, $store_cate_id, $store_id);
                break;
            case 'year':
                $timesArr = array();
                $begin_year = strtotime(date("Y",time())."-1"."-1"); //本年开始  
                $endThisweek=time();
                $timesArr[0] =$begin_year;
                $timesArr[1] = $endThisweek;
                $result = $this->toTransactionTrend($timesArr, $store_cate_id, $store_id);
                break;
        }
        return $result;
    }
    
    
     /**
     * 图标--获取交易趋势
     * @param $store_cate_id    区域ID
     * @param $store_id         店铺ID
     * @param $op
     * @author: wangshuo
     * @date  : 2019-3-21
     */
    public function toTransactionTrend($timesArr, $store_cate_id, $store_id) {
        $result = $a = $b = $c = $d = array();
            $t1 = $this->toUpOrderCount($store_cate_id, $store_id, $timesArr);
            $a = array_merge($a, array($t1['chongzhi_money']));
            $b = array_merge($b, array($t1['zengsong_money']));
            $c = array_merge($c, array($t1['xiaofei_money']));
            $d = array_merge($d, array($t1['shengyu_money']));
            $result['a'] = $a;
            $result['b'] = $b;
            $result['c'] = $c;
            $result['d'] = $d;
        return $result;
    }

        /**
     * 统计--获取交易状况
     * @param $store_cate_id    区域ID
     * @param $store_id         店铺ID
     * @param $timeConf         时间段
     * @author: wangshuo
     * @date  : 2019-3-21
     */
    public function toUpOrderCount($store_cate_id, $store_id, $timeConf = array()) {
        $userMod = &m('user');
        $where = " where 1=1 and add_time BETWEEN {$timeConf[0]} AND {$timeConf[1]}";
          if ($store_id > 0) {
            $store_sql = 'SELECT  id FROM  bs_user  where  mark =1 and is_use = 1 and store_id=' . $store_id;
            $store_res = $userMod->querySql($store_sql);
            $storeid = implode(',', $this->arrayColumn($store_res, "id"));
            $where .= " AND add_user in (" . $storeid . ")";
        } else {
            if ($store_cate_id > 0) {
                $store_id_sql = 'SELECT  id FROM  bs_store  where is_open = 1 and store_cate_id=' . $store_cate_id;
                $res_store_id = $userMod->querySql($store_id_sql);
                $storeids = implode(',', $this->arrayColumn($res_store_id, "id"));
                $area_sql = 'SELECT  id FROM  bs_user  where  mark =1 and is_use = 1 and store_id in(' . $storeids . ')';
                $area_res = $userMod->querySql($area_sql);
                $areaid = implode(',', $this->arrayColumn($area_res, "id"));
                $where .= " AND add_user in (" . $areaid . ")";
            }
        }
            $upOrderCount = array();
            //充值余额总数
            $cZsql = 'SELECT  sum(c_money)  AS c_money  from ' . DB_PREFIX . 'amount_log ' . $where . '  and  type in (1,4,5) and status=2';
            $cZres = $userMod->querySql($cZsql);
            $upOrderCount['chongzhi_money'] = $cZres[0]['c_money'];
             if($upOrderCount['chongzhi_money']==''){
            $upOrderCount['chongzhi_money']=0;
             }
            //赠送金额总数
            $zSsql = 'SELECT  sum(c_money)  AS c_money  FROM  bs_amount_log ' . $where . '  and type =3 and status=4';
            $zSres = $userMod->querySql($zSsql);
            //查询充值的所有ID
            $id_sql = 'SELECT  point_rule_id  FROM  bs_amount_log ' . $where . ' and  type in (1,4) and status = 2';
            $id_res = $userMod->querySql($id_sql);
             foreach ($id_res as $k => $v) {
                //根据ID查询每条数据充值赠送的金额
                $cst_sql = 'SELECT  sum(s_money)  AS s_money   FROM  bs_recharge_point where id ='.$v['point_rule_id'];
                $csTres = $userMod->querySql($cst_sql);
                $s_money['s_money'] += $csTres[0]['s_money'];
                 }
            $upOrderCount['zengsong_money'] = $zSres[0]['c_money'] + $s_money['s_money'];
            //消费金额总数
            $xFsql = 'SELECT  sum(c_money) AS c_money  FROM  bs_amount_log ' . $where . ' and  type = 2 ';
            $xFres = $userMod->querySql($xFsql);
            $upOrderCount['xiaofei_money']= $xFres[0]['c_money'];
            //消费完余额总数
            $upOrderCount['shengyu_money'] = $cZres[0]['c_money'] + $zSres[0]['c_money'] + $s_money['s_money'] - $xFres[0]['c_money'];
        return $upOrderCount;
    }
    
    
    /**
     * 图表--获取余额交易趋势
     * @param $store_cate_id    区域ID
     * @param $store_id         店铺ID
     * @param $tm
     * @author: wangshuo
     * @date  : 2019-3-21
     */
    public function zxTransactionTrend($store_cate_id = 0, $store_id = 0, $tm = 'week') {
        //获取时间组件
        $result = array();
        switch ($tm) {
            case 'week':
                $timeArr = array('周一', '周二', '周三', '周四', '周五', '周六', '周日');
                $timesArr = array();
                $y = date('Y', time());
                $w = date('W', time());
                $weekStart = date("Y-m-d", strtotime("{$y}-W{$w}-1"));
                $weekStart_1 = strtotime($weekStart);
                $weekStart_2 = strtotime($weekStart . ' 23:59:59');
                $diff = 24 * 3600;
                for ($i = 0; $i <= 6; $i++) {
                    $timesArr[$i][0] = $weekStart_1 + $diff * $i;
                    $timesArr[$i][1] = $weekStart_2 + $diff * $i;
                }
                $result = $this->qxTransactionTrend($timesArr, $store_cate_id, $store_id);
                $result['xAxis'] = $timeArr;
                break;
            case 'month':
                $timeArr = array();
                $month_1 = mktime(0, 0, 0, date('m'), 1, date('Y'));
                $diff = 24 * 3600;
                for ($i = 1; $i <= date('t'); $i++) {
                    $timeArr = array_merge($timeArr, array(date('Y/m/') . $i));
                    $timesArr[$i][0] = $month_1 + $diff * ($i - 1);
                    $timesArr[$i][1] = ($month_1 + $diff * $i) - 1;
                }
                $result = $this->qxTransactionTrend($timesArr, $store_cate_id, $store_id);
                $result['xAxis'] = $timeArr;
                break;
            case 'year':
                $timeArr = array();
                for ($i = 1; $i <= 12; $i++) {
                    $timeArr = array_merge($timeArr, array(date('Y/') . $i));
                    $timesArr[$i][0] = mktime(0, 0, 0, $i, 1, date('Y'));
                    $timesArr[$i][1] = mktime(23, 59, 59, $i, date('t'), date('Y'));
                }
                $result = $this->qxTransactionTrend($timesArr, $store_cate_id, $store_id);
                $result['xAxis'] = $timeArr;
                break;
        }
        return $result;
    }
    /**
     * 图标--获取余额消费趋势
     * @param $store_cate_id    区域ID
     * @param $store_id         店铺ID
     * @author: wangshuo    
     * @date  : 2018-3-21
     */
    public function qxTransactionTrend($timesArr, $store_cate_id, $store_id) {
        $result = $a = $b = $c = $d  = array();
        foreach ($timesArr as $key => $value) {
            $t1 = $this->zxUpOrderCount($store_cate_id, $store_id,$value);
            $a = array_merge($a, array($t1['chongzhi_money']));
            $b = array_merge($b, array($t1['zengsong_money']));
            $c = array_merge($c, array($t1['xiaofei_money']));
            $d = array_merge($d, array($t1['shengyu_money']));
        }
            $result['a'] = implode(',', $a);
            $result['b'] = implode(',', $b);
            $result['c'] = implode(',', $c);
            $result['d'] = implode(',', $d);
        return $result;
    }
     /**
     * 统计--会员余额交易状况
     * @param $store_cate_id    区域ID
     * @param $store_id         店铺ID
     * @param $timeConf         时间段
     * @author: wangshuo
     * @date  : 2018-3-21
     */
    public function zxUpOrderCount($store_cate_id, $store_id, $timeConf = array()) {
        $userMod = &m('user');
        $where = " where 1=1 and add_time BETWEEN {$timeConf[0]} AND {$timeConf[1]}";
          if ($store_id > 0) {
            $store_sql = 'SELECT  id FROM  bs_user  where  mark =1 and is_use = 1 and store_id=' . $store_id;
            $store_res = $userMod->querySql($store_sql);
            $storeid = implode(',', $this->arrayColumn($store_res, "id"));
            $where .= " AND add_user in (" . $storeid . ")";
        } else {
            if ($store_cate_id > 0) {
                $store_id_sql = 'SELECT  id FROM  bs_store  where is_open = 1 and store_cate_id=' . $store_cate_id;
                $res_store_id = $userMod->querySql($store_id_sql);
                $storeids = implode(',', $this->arrayColumn($res_store_id, "id"));
                $area_sql = 'SELECT  id FROM  bs_user  where  mark =1 and is_use = 1 and store_id in(' . $storeids . ')';
                $area_res = $userMod->querySql($area_sql);
                $areaid = implode(',', $this->arrayColumn($area_res, "id"));
                $where .= " AND add_user in (" . $areaid . ")";
            }
        }
            $upOrderCount = array();
            //充值余额总数
            $cZsql = 'SELECT  sum(c_money)  AS c_money  from ' . DB_PREFIX . 'amount_log ' . $where . '  and  type in (1,4,5) and status=2';
            $cZres = $userMod->querySql($cZsql);
            $upOrderCount['chongzhi_money'] = $cZres[0]['c_money'];
            if($upOrderCount['chongzhi_money']==''){
             $upOrderCount['chongzhi_money']=0;   
            }
            //赠送金额总数
            $zSsql = 'SELECT  sum(c_money)  AS c_money  FROM  bs_amount_log ' . $where . '  and type =3 and status =4';
            $zSres = $userMod->querySql($zSsql);
            //查询充值的所有ID
            $id_sql = 'SELECT  point_rule_id  FROM  bs_amount_log ' . $where . ' and  type in (1,4) and status = 2';
            $id_res = $userMod->querySql($id_sql);
            foreach ($id_res as $k => $v) {
                //根据ID查询每条数据充值赠送的金额
                $cst_sql = 'SELECT  sum(s_money)  AS s_money   FROM  bs_recharge_point where id ='.$v['point_rule_id'];
                $csTres = $userMod->querySql($cst_sql);
                $s_money['s_money'] += $csTres[0]['s_money'];
                 }
            $upOrderCount['zengsong_money'] = $zSres[0]['c_money'] + $s_money['s_money'];
//            $t ='' ;
//            foreach ($id_res as $v) {
//                $v = join(",",$v); // 可以用implode将一维数组转换为用逗号连接的字符串，join是别名
//                $temp[] = $v;
//            }
//            foreach ($temp as $v) {
//                $t.=$v.",";
//            }
//            $t = substr($t, 0, -1); // 利用字符串截取函数消除最后一个逗号
//            //根据ID查询每条数据充值赠送的金额
//            $cst_sql = 'SELECT  sum(s_money)  AS s_money   FROM  bs_recharge_point  where id in (' .$t .')';
//            $csTres = $userMod->querySql($cst_sql);
//            $upOrderCount['zengsong_money'] = $zSres[0]['c_money'] + $csTres[0]['s_money'];
            //消费金额总数
            $xFsql = 'SELECT  sum(c_money) AS c_money  FROM  bs_amount_log ' . $where . ' and  type = 2 ';
            $xFres = $userMod->querySql($xFsql);
            $upOrderCount['xiaofei_money']= $xFres[0]['c_money'];
            if($upOrderCount['xiaofei_money']==''){
             $upOrderCount['xiaofei_money']=0;   
            }
            //消费完余额总数
            $upOrderCount['shengyu_money'] = $cZres[0]['c_money'] + $zSres[0]['c_money'] + $s_money['s_money'] - $xFres[0]['c_money'];
        return $upOrderCount;
    }

    
    
}
