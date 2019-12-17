<?php

/**
 * 分销角色控制器
 * @author zhangkx
 * @date 2018-10-16
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class DistributorApp extends BaseStoreApp
{
    private $lang_id;
    private $fxuserMod;
    private $fxruleMod;
    private $fxuserMoneyMod;
    private $fxuserRuleMod;
    private $userMod;
    private $storeMod;
    private $storeCateMod;
    private $storeLangMod;
    private $fxRulerMod;
    private $storeCateLangMod;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
        $this->assign('lang_id', $this->lang_id);
        $this->fxuserMod = &m('fxuser');
        $this->fxuserMoneyMod = &m('fxuserMoney');
        $this->fxuserRuleMod = &m('fxuserRule');
        $this->fxruleMod = &m('fxrule');
        $this->userMod = &m('user');
        $this->storeMod = &m('store');
        $this->storeCateMod = &m('storeCate');
        $this->storeLangMod = &m('areaStoreLang');
        $this->fxRulerMod = &m('fxrule');
        $this->storeCateLangMod = &m('storeCateLang');
    }

    /**
     * 析构函数
     */
    public function __destruct() {

    }

    /**
     * 分销人员树
     *
     * @author zhangkx
     * @date 2018-10-17
     */
    public function memberList() {
       //获取当前店铺分销规则
        $sql_rule = "select * from bs_fx_rule  WHERE mark = 1 and find_in_set($this->storeId, store_id)";
        $res_rule = $this->fxuserMod->querySql($sql_rule);
        $str='';
        for($i=0; $i<count($res_rule);$i++ ){
            $str.=$res_rule[$i]['id'].",";
        }
        $str = substr($str,0,-1);
//      print_r($str1);exit;
        $source = $_REQUEST['source'] ? $_REQUEST['source'] : 0;
        $level = $_REQUEST['level'] ? $_REQUEST['level'] : 0;
        $where = " WHERE  rule_id in(" . $str . ") and is_check=2 and mark = 1";
        if ($source) {
            $where .= " and source =" . $source;
            $this->assign('source', $source);
        }
         if ($level) {
            $where .= " and level =" . $level;
            $this->assign('level', $level);
        }
        //账号来源
        $this->assign('sourceList', $this->fxuserMod->source);
        $sql = 'select * from bs_fx_user ' . $where;
        $res = $this->fxuserMod->querySql($sql);
        foreach ($res as $key => $val) {
            $store_name = $this->storeMod->getNameById($val['store_id'],$this->defaulLang);
            $res[$key]['store_name'] = $store_name;
            $res[$key]['source_name'] = $this->fxuserMod->source[$val['source']];
            //$lev2 = $this->fxuserMod->getUserListByLevel(2, $val['id']);
            $rule = $this->fxruleMod->getRow($val['rule_id']);
            $res[$key]['rule_name'] = $rule['rule_name'];
//            if (!empty($lev2)) {
//                // 2 级
//                $res[$key]['childs'] = $lev2;
//                // 3级
//                foreach ($lev2 as $k => $v) {
//                    $res[$key]['childs'][$k]['source_name'] = $this->fxuserMod->source[$v['source']];
//                    $res[$key]['childs'][$k]['rule_id']=$val['rule_id'];
////                    print_r($res);exit;
//                    $lev3 = $this->fxuserMod->getUserListByLevel(3, $v['id']);
//                    if (!empty($lev3)) {
//                        foreach ($lev3 as $k1=>$v1){
//                            $res[$key]['childs'][$k]['childs'][$k1]['source_name'] = $this->fxuserMod->source[$v1['source']];
//                        }
//                        $res[$key]['childs'][$k]['childs'] = $lev3;
//                    }
//                }
//            }
        }
        $this->assign('res', $res);
        $this->display('distributor/memberList.html');
    }

    /**
     * 分润规则列表
     *
     * @author zhangkx
     * @date 2018-10-17
     */
    public function ruleList() {
        $ruler_name = !empty($_REQUEST['ruler_name']) ? htmlspecialchars(trim($_REQUEST['ruler_name'])) : '';
        $where = " where `mark` =1 and store_id = " . $this->storeId;
        if (!empty($ruler_name)) {
            $where .= "  and `rule_name` like '%" . $ruler_name . "%'";
        }
        $where .= " order by add_time desc";
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "fx_rule " . $where;
        $totalCount = $this->fxRulerMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $sql = "select * from  " . DB_PREFIX . "fx_rule " . $where;
        $rs = $this->fxRulerMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        foreach ($rs['list'] as $k => $v) {
            $rs['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            $storeInfo = $this->storeMod->getNameById($v['store_id'], $this->defaulLang);
            $rs['list'][$k]['store_name'] = $storeInfo;
            $rs['list'][$k]['lev1_prop'] = $this->fxruleMod->isDecimal($v['lev1_prop']);
            $rs['list'][$k]['lev2_prop'] = $this->fxruleMod->isDecimal($v['lev2_prop']);
            $rs['list'][$k]['lev3_prop'] = $this->fxruleMod->isDecimal($v['lev3_prop']);
            if (empty($rs['list'][$k]['store_name'])) {
                $rs['list'][$k]['store_name'] = '不限制';
            }
            if ($v['add_time']) {
                $rs['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            } else {
                $rs['list'][$k]['add_time'] = '';
            }
            $rs['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
//            $rs['list'][$k]['sort'] = $total - $k - 20 * ($p - 1); //倒叙
        }
        $this->assign('p', $p);
        $this->assign('list', $rs['list']);
        $this->assign('page_html', $rs['ph']);
        $this->assign('ruler_name', $ruler_name);
        $this->display('distributor/rulerList.html');
    }
    public function show()
    {
        $user_id = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';
        $is_on= isset($_REQUEST['is_on']) ? intval($_REQUEST['is_on']) : 99;
        $area_id = !empty($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : '';
        $store_id = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : '';
        $order_sn = !empty($_REQUEST['order_sn']) ? $_REQUEST['order_sn'] : '';
        $start_time = !empty($_REQUEST['start_time']) ? $_REQUEST['start_time'] : '';
        $end_time = !empty($_REQUEST['end_time']) ? $_REQUEST['end_time'] : '';
        $this->assign('start_time',$start_time);
        $this->assign('end_time',$end_time);
        $start_times = !empty($_REQUEST['start_times']) ? $_REQUEST['start_times'] : '';
        $end_times = !empty($_REQUEST['end_times']) ? $_REQUEST['end_times'] : '';
        $this->assign('start_times',$start_times);
        $this->assign('end_times',$end_times);
        $fxuserMod = &m('fxuser');
        $fxOrderMod= &m('fxOrder');
        $storeMod = &m('store');
        $where = ' and 1=1 ';
        if ($is_on != 99){
            $where .= " and fo.is_on=".$is_on;
        }
        if (!empty($order_sn)){
            $where .= " and fo.order_sn like '%".$order_sn."%'";
        }
        if (!empty($store_id)) {
            $where .= " and fo.store_id like '%" . $store_id . "%'";
        }
        if (!empty($area_id)) {
            $where .= " and fo.store_cate =000000" . $area_id ;
        }
        if (!empty($start_time) && !empty($end_time)){
            $start_time = strtotime($start_time);
            $end_time   = strtotime($end_time);
            $where .= " and o.add_time >= $start_time and o.add_time <= $end_time";
        }
        if (!empty($start_times) && !empty($end_times)){
            $start_times = strtotime($start_times);
            $end_times   = strtotime($end_times);
            $where .= " and o.payment_time >= $start_times and o.payment_time <= $end_times";
        }
        $this->assign('order_sn',$order_sn);
        $storeCateMod = &m('storeCate');
        $areaArr = $storeCateMod->getAreaArr(1,$this->defaulLang);
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

        $sql1= "select * from bs_fx_user where user_id=".$user_id;
        $info = $fxuserMod->querySql($sql1);
        if ($info[0]['level'] == 3){
            $sql = "SELECT fs.discount,fo.order_sn,fo.is_on,o.add_time,o.payment_time,fo.store_id,fo.id,fu.lev1_prop,fu.lev2_prop,fu.lev3_prop,fo.fx_discount,o.order_id,fs.level,o.goods_amount,fo.pay_money,o.order_state,o.order_amount,fo.fx_commission_percent FROM bs_fx_order AS fo 
              LEFT JOIN bs_order AS o ON fo.order_id = o.order_id 
              LEFT JOIN bs_fx_rule AS fu ON fo.rule_id = fu.id 
              LEFT JOIN bs_fx_user AS fs ON fo.fx_user_id = fs.id
              WHERE fo.fx_user_id={$info[0]['id']}{$where} ORDER BY o.payment_time DESC";
            $data = $fxOrderMod->querySqlPageData($sql);
            $sql2 = "select * from bs_fx_user where user_id=".$user_id;
            $res = $fxuserMod->querySql($sql2);
            foreach($data['list'] as $k=>$v){
                $data['list'][$k]['fxmoney']=number_format($v['pay_money']*($v['fx_commission_percent'])/100,2);
                $data['list'][$k]['discount_money']=number_format($v['goods_amount']*$v['discount']/100,2);
                $data['list'][$k]['prop'] = $v['fx_commission_percent'];
                $data['list'][$k]['storeName'] = $storeMod->getNameById($v['store_id'],$this->defaulLang);
                $data['list'][$k]['add_time'] = date("Y-m-d H:i",$v['add_time']);
                $data['list'][$k]['payment_time'] = date("Y-m-d H:i",$v['payment_time']);
                $data['list'][$k]['type_name'] = $fxOrderMod->getRoomType($v['order_sn']);
            }
            $sqls = "SELECT fu.lev3_prop,fs.discount,fo.fx_discount,fo.pay_money,fo.fx_commission_percent FROM bs_fx_order AS fo 
              LEFT JOIN bs_order AS o ON fo.order_id = o.order_id 
              LEFT JOIN bs_fx_rule AS fu ON fo.rule_id = fu.id 
              LEFT JOIN bs_fx_user AS fs ON fo.fx_user_id = fs.id
              WHERE fo.fx_user_id={$info[0]['id']} and is_on=0";
            $a = $fxOrderMod->querySql($sqls);
            foreach($a as $k=>$v){
                // $a[$k]['fxmoney']=number_format($v['pay_money']*($v['lev3_prop']-$v['discount'])/100,2);
                // $a[$k]['fxmoney']=number_format($v['pay_money']*($v['fx_discount'])/100,2);
                $a[$k]['fxmoney']=number_format($v['pay_money']*($v['fx_commission_percent'])/100,2);
            }
            $sumss = 0;
            foreach($a as $item){
                $sumss += $item['fxmoney'];
            }
            //搜索佣金
            $sqlss = "SELECT fs.discount,fu.lev3_prop,fo.fx_discount,fo.pay_money,fo.fx_commission_percent FROM bs_fx_order AS fo 
              LEFT JOIN bs_order AS o ON fo.order_id = o.order_id 
              LEFT JOIN bs_fx_rule AS fu ON fo.rule_id = fu.id 
              LEFT JOIN bs_fx_user AS fs ON fo.fx_user_id = fs.id
              WHERE fo.fx_user_id={$info[0]['id']}".$where;
            $as = $fxOrderMod->querySql($sqlss);
            foreach($as as $k=>$v){
                // $as[$k]['fxmoney']=number_format($v['pay_money']*($v['lev3_prop']-$v['discount'])/100,2);
                // $as[$k]['fxmoney']=number_format($v['pay_money']*($v['fx_discount'])/100,2);
                $as[$k]['fxmoney']=number_format($v['pay_money']*($v['fx_commission_percent'])/100,2);
            }
            $sums = 0;
            foreach($as as $item){
                $sums += $item['fxmoney'];
            }
            $sql4="SELECT SUM(apply_money) as fx_applymoney FROM bs_fx_outmoney_apply WHERE fx_user_id={$info[0]['id']} AND is_check = 2";
            $fxOutmoneyApplyMod = &m('fxOutmoneyApply');
            $i = $fxOutmoneyApplyMod->querySql($sql4);
            $OrderStatus = array(
                '0' => '未入账',
                '1' => '已入账'
            );
            $this->assign('data',$data['list']);
            $this->assign('monery',$res[0]['monery']);
            $this->assign('sumss',$sumss);
            $this->assign('sums',$sums);
            $this->assign('page_html', $data['ph']);
            $this->assign('statusList',$OrderStatus);
            $this->assign('user_id',$user_id);
            $this->assign('is_on', $is_on);
            $this->assign('fx_applymoney',$i[0]['fx_applymoney']);
            $this->assign('real_name',$info[0]['real_name']);
            $this->display('distributor/show.html');
        }elseif ($info[0]['level'] == 1){
            $s = "select id from bs_fx_user where parent_id={$info[0]['id']} and level=2";
            $ss = $fxuserMod->querySql($s);
            foreach ($ss as $k => $v){
                $secondFxUserIdData[]=$v['id'];
            }
            $secondFxUserIds=implode(',',$secondFxUserIdData);
            $sql5 = "select id from bs_fx_user where parent_id in (".$secondFxUserIds.") and level=3";
            $res = $fxuserMod->querySql($sql5);
//            echo '<pre>';var_dump($res);die;
            foreach($res as $k=>$v){
                $thirdFxUserIdData[]=$v['id'];
            }
            $thirdFxUserIds=implode(',',$thirdFxUserIdData);
            $sql6 = "select fo.is_on,o.add_time,o.payment_time,fo.store_id,fo.id,fu.discount,fu.level,fr.lev1_prop,fr.lev2_prop,fr.lev3_prop,o.goods_amount,fo.pay_money,o.order_state,fo.add_time,fo.order_id,fo.order_sn from bs_fx_order as fo
                      left join bs_order as o on fo.order_id = o.order_id
                      left join bs_fx_user as fu on fu.id = fo.fx_user_id
                      left join bs_fx_rule as fr on fo.rule_id = fr.id where fo.fx_user_id in (".$thirdFxUserIds."){$where} ORDER BY o.payment_time DESC";
//            echo $sql6;die;
            $data = $fxOrderMod->querySqlPageData($sql6);
            $sql2 = "select * from bs_fx_user where user_id=".$user_id;
            $res = $fxuserMod->querySql($sql2);
            foreach($data['list'] as $k=>$v){
                $prop = $v['lev1_prop']/100;
                $data['list'][$k]['fxmoney']=number_format($v['pay_money'] *$prop,2);
                $data['list'][$k]['prop'] = $v['lev1_prop'];
                $data['list'][$k]['storeName'] = $storeMod->getNameById($v['store_id'],$this->defaulLang);
                $data['list'][$k]['add_time'] = date("Y-m-d H:i",$v['add_time']);
                $data['list'][$k]['payment_time'] = date("Y-m-d H:i",$v['payment_time']);
                $data['list'][$k]['type_name']=$fxOrderMod->getRoomType($v['order_sn']);
            }
            $sqlss = "SELECT fo.pay_money,sum(ROUND((fo.pay_money * 0.1),2)) as money FROM bs_fx_order AS fo
                      LEFT JOIN bs_order AS o ON fo.order_id = o.order_id
                      LEFT JOIN bs_fx_user AS fu ON fo.fx_user_id = fu.id
                      LEFT JOIN bs_fx_rule AS fr ON fu.rule_id = fr.id
                      WHERE fo.fx_user_id in (".$thirdFxUserIds.")".$where;
//            echo $sqlss;die;
            $as = $fxOrderMod->querySql($sqlss);
//            dd($as);die;
            //未入账
            $sqlsss = "select fr.lev1_prop,fo.pay_money from bs_fx_order as fo
                      left join bs_order as o on fo.order_id = o.order_id
                      left join bs_fx_user as fu on fu.id = fo.fx_user_id
                      left join bs_fx_rule as fr on fo.rule_id = fr.id where fo.fx_user_id in (".$thirdFxUserIds.") AND fo.is_on=0";
            $ass = $fxOrderMod->querySql($sqlsss);
            foreach($ass as $k=>$v){
                $ass[$k]['fxmoney']=number_format($v['pay_money']*$v['lev1_prop']/100,2);
            }
            $sumss = 0;
            foreach($ass as $item){
                $sumss += $item['fxmoney'];
            }
            $sql4="SELECT SUM(apply_money) as fx_applymoney FROM bs_fx_outmoney_apply WHERE fx_user_id={$info[0]['id']} AND is_check = 2";
            $fxOutmoneyApplyMod = &m('fxOutmoneyApply');
            $i = $fxOutmoneyApplyMod->querySql($sql4);
            $OrderStatus = array(
                '0' => '未入账',
                '1'=>'已入账'
            );
            $this->assign('statusList',$OrderStatus);
            $this->assign('data',$data['list']);
            $this->assign('monery',$res[0]['monery']);
            $this->assign('page_html', $data['ph']);
            $this->assign('user_id',$user_id);
            $this->assign('is_on', $is_on);
            $this->assign('fx_applymoney',$i[0]['fx_applymoney']);
            $this->assign('real_name',$info[0]['real_name']);
            $this->assign('sums',$as[0]['money']);
            $this->assign('sumss',$sumss);
            $this->display('distributor/show.html');
        }elseif ($info[0]['level'] == 2){
            $sss = "select id from bs_fx_user where parent_id={$info[0]['id']} and level=3";
            $result =$fxuserMod->querySql($sss);
            foreach ($result as $v){
                $ids[] = $v['id'];
            }
            $two_ids    = implode(',',$ids);
            $sql = "select fo.is_on,o.add_time,o.payment_time,fo.store_id,fo.id,fu.discount,fu.level,fr.lev1_prop,fr.lev2_prop,fr.lev3_prop,o.goods_amount,fo.pay_money,o.order_state,fo.order_id,fo.order_sn,o.order_amount from bs_fx_order as fo
                  left join bs_order as o on fo.order_id = o.order_id
                  left join bs_fx_user as fu on fu.id = fo.fx_user_id
                  left join bs_fx_rule as fr on fo.rule_id = fr.id where fo.fx_user_id in ({$two_ids}){$where} ORDER BY o.payment_time DESC";
            $data = $fxOrderMod->querySqlPageData($sql);
            $sql2 = "select * from bs_fx_user where user_id =".$user_id;
            $res = $fxuserMod->querySql($sql2);
            foreach ($data['list'] as $k => $v){
                $prop = $v['lev2_prop']/100;
                $data['list'][$k]['fxmoney'] = number_format($v['pay_money'] * $prop,2);
                $data['list'][$k]['prop'] = $v['lev2_prop'];
                $data['list'][$k]['storeName'] = $storeMod->getNameById($v['store_id'],$this->defaulLang);
                $data['list'][$k]['add_time'] = date("Y-m-d H:i",$v['add_time']);
                $data['list'][$k]['payment_time'] = date("Y-m-d H:i",$v['payment_time']);
                $data['list'][$k]['type_name']=$fxOrderMod->getRoomType($v['order_sn']);
            }
            //搜索佣金
            $sqlss = "select fr.lev2_prop,fo.pay_money from bs_fx_order as fo
                  left join bs_order as o on fo.order_id = o.order_id
                  left join bs_fx_user as fu on fu.id = fo.fx_user_id
                  left join bs_fx_rule as fr on fo.rule_id = fr.id where fo.fx_user_id in ({$two_ids})".$where;
            $as = $fxOrderMod->querySql($sqlss);
            foreach($as as $k=>$v){
                $as[$k]['fxmoney']=number_format($v['pay_money']*$v['lev2_prop']/100,2);
            }
            $sums = 0;
            foreach($as as $item){
                $sums +=  $item['fxmoney'];
            }
            //未入账佣金
            $sqlsss = "select fr.lev2_prop,fo.pay_money from bs_fx_order as fo
                  left join bs_order as o on fo.order_id = o.order_id
                  left join bs_fx_user as fu on fu.id = fo.fx_user_id
                  left join bs_fx_rule as fr on fo.rule_id = fr.id where fo.fx_user_id in ({$two_ids}) AND fo.is_on=0";
            $ass = $fxOrderMod->querySql($sqlsss);
            foreach($ass as $k=>$v){
                $ass[$k]['fxmoney']=number_format($v['pay_money']*$v['lev2_prop']/100,2);
            }
            $sumss = 0;
            foreach($ass as $item){
                $sumss +=  $item['fxmoney'];
            }
            $OrderStatus = array(
                '0' => '未入账',
                '1'=>'已入账'
            );
            $sql4="SELECT SUM(apply_money) as fx_applymoney FROM bs_fx_outmoney_apply WHERE fx_user_id={$info[0]['id']} AND is_check = 2";
            $fxOutmoneyApplyMod = &m('fxOutmoneyApply');
            $i = $fxOutmoneyApplyMod->querySql($sql4);
            $this->assign('statusList',$OrderStatus);
            $this->assign('data',$data['list']);
            $this->assign('monery',$res[0]['monery']);
            $this->assign('page_html', $data['ph']);
            $this->assign('real_name',$info[0]['real_name']);
            $this->assign('is_on', $is_on);
            $this->assign('user_id',$user_id);
            $this->assign('sumss',$sumss);
            $this->assign('sums',sprintf("%.2f", $sums));
            $this->assign('fx_applymoney',$i[0]['fx_applymoney']);
            $this->display('distributor/show.html');
        }
    }

    /**
     * 单个导出订单
     * @author tangp
     * @date 2018-12-04
     */
    public function export()
    {
        $user_id = $_REQUEST['user_id'];
        $is_on = isset($_REQUEST['is_on']) ? $_REQUEST['is_on'] : 99;
        $area_id = !empty($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : '';
        $store_id = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : '';
        $order_sn = !empty($_REQUEST['order_sn']) ? $_REQUEST['order_sn'] : '';
        $start_time = !empty($_REQUEST['start_time']) ? $_REQUEST['start_time'] : '';
        $end_time = !empty($_REQUEST['end_time']) ? $_REQUEST['end_time'] : '';
        $start_times = !empty($_REQUEST['start_times']) ? $_REQUEST['start_times'] : '';
        $end_times = !empty($_REQUEST['end_times']) ? $_REQUEST['end_times'] : '';
        $where = ' and 1=1 ';
        $limit = 10000;
        if ($is_on != 99){
            $where .= " and fo.is_on=".$is_on;
        }
        if (!empty($order_sn)){
            $where .= " and fo.order_sn like '%".$order_sn."%'";
        }
        if (!empty($store_id)) {
            $where .= " and fo.store_id like '%" . $store_id . "%'";
        }
        if (!empty($area_id)) {
            $where .= " and fo.store_cate =000000" . $area_id ;
        }
        if (!empty($start_time) && !empty($end_time)){
            $start_time = strtotime($start_time);
            $end_time   = strtotime($end_time);
            $where .= " and o.add_time >= $start_time and o.add_time <= $end_time";
        }
        if (!empty($start_times) && !empty($end_times)){
            $start_times = strtotime($start_times);
            $end_times   = strtotime($end_times);
            $where .= " and o.payment_time >= $start_times and o.payment_time <= $end_times";
        }
        $fxuserMod = &m('fxuser');
        $fxOrderMod= &m('fxOrder');
        $storeMod = &m('store');
        $sql1= "select * from bs_fx_user where user_id=".$user_id;
        $info = $fxuserMod->querySql($sql1);
        if ($info[0]['level'] == 3){
//            $countSql = "SELECT COUNT(order_id) as count FROM bs_fx_order as fo WHERE fo.fx_user_id={$info[0]['id']}".$where;
            $countSql = "SELECT COUNT(fo.order_id) as count FROM bs_fx_order AS fo 
                  LEFT JOIN bs_order AS o ON fo.order_id = o.order_id 
                  LEFT JOIN bs_fx_rule AS fu ON fo.rule_id = fu.id 
                  LEFT JOIN bs_fx_user AS fs ON fo.fx_user_id = fs.id
                  WHERE fo.fx_user_id={$info[0]['id']}".$where;
            $order_count = $fxOrderMod->querySql($countSql);
            $size = ceil($order_count[0]['count']/$limit);
            for ($i = 1;$i <= $size;$i++){
                $start = ($i - 1) * $limit;
                $sql = "SELECT fs.discount,fo.order_sn,fo.is_on,fo.fx_discount,o.add_time,o.payment_time,fo.store_id,fo.id,fu.lev1_prop,fu.lev2_prop,fu.lev3_prop,fs.discount,o.order_id,o.order_state,fs.level,o.goods_amount,fo.pay_money,o.order_state,o.order_amount,fo.fx_commission_percent FROM bs_fx_order AS fo 
                  LEFT JOIN bs_order AS o ON fo.order_id = o.order_id 
                  LEFT JOIN bs_fx_rule AS fu ON fo.rule_id = fu.id 
                  LEFT JOIN bs_fx_user AS fs ON fo.fx_user_id = fs.id
                  WHERE fo.fx_user_id={$info[0]['id']}".$where." order by o.payment_time DESC limit {$start},{$limit}";
//                echo $sql;die;
                $data = $fxOrderMod->querySql($sql);
                $exportData = array();
                foreach($data as $k=>$v){
                    // $data[$k]['fxmoney']=number_format($v['pay_money']*($v['lev3_prop']-$v['discount'])/100,2);
                    // $data[$k]['fxmoney']=number_format($v['pay_money']*($v['fx_discount'])/100,2);
                    $data[$k]['fxmoney']=number_format($v['pay_money']*($v['fx_commission_percent'])/100,2);
                    $data[$k]['order_state_name'] = $fxOrderMod->state[$v['is_on']];
                    $data[$k]['storeName'] = $storeMod->getNameById($v['store_id'],$this->defaulLang);
                    $data[$k]['add_time'] = date("Y-m-d H:i",$v['add_time']);
                    $data[$k]['type_name']=$fxOrderMod->getRoomType($v['order_sn']);
                    if ($v['payment_time'] == 0){
                        $data[$k]['payment_time'] = '---';
                    }else{
                        $data[$k]['payment_time'] = date("Y-m-d H:i",$v['payment_time']);
                    }
                    // if (($v['lev3_prop'] - $v['fx_discount']) < 0){
                    //     $data[$k]['prop'] = 0;
                    // }else{
                    //     $data[$k]['prop'] = $v['lev3_prop'] - $v['fx_discount'];
                    // }
                    // by xt 2019.03.05
                    if ($v['fx_commission_percent'] < 0){
                        $data[$k]['prop'] = 0;
                    }else{
                        $data[$k]['prop'] = $v['fx_commission_percent'];
                    }
                    $exportData[$k][] = $k+1;
                    $exportData[$k][] = $v['order_sn']."\t";
                    $exportData[$k][] = $data[$k]['type_name'];
                    $exportData[$k][] = $v['pay_money'];
                    $exportData[$k][] = $data[$k]['fxmoney'];
                    $exportData[$k][] = $data[$k]['prop'];
                    $exportData[$k][] = $data[$k]['storeName'];
                    $exportData[$k][] = $data[$k]['order_state_name'];
                    $exportData[$k][] = $data[$k]['add_time'];
                    $exportData[$k][] = $data[$k]['payment_time'];
                }

            }
//            dd($exportData);
//            die;
        }elseif ($info[0]['level'] == 1){
            $s = "select id from bs_fx_user where parent_id={$info[0]['id']} and level=2";
            $ss = $fxuserMod->querySql($s);
            foreach ($ss as $k => $v){
                $secondFxUserIdData[]=$v['id'];
            }
            $secondFxUserIds=implode(',',$secondFxUserIdData);
            $sql5 = "select id from bs_fx_user where parent_id in (".$secondFxUserIds.") and level=3";
            $res = $fxuserMod->querySql($sql5);
            foreach($res as $k=>$v){
                $thirdFxUserIdData[]=$v['id'];
            }
            $thirdFxUserIds=implode(',',$thirdFxUserIdData);
            $countSql =  "select COUNT(fo.order_id) as count from bs_fx_order as fo
                      left join bs_order as o on fo.order_id = o.order_id
                      left join bs_fx_user as fu on fu.id = fo.fx_user_id
                      left join bs_fx_rule as fr on fo.rule_id = fr.id where fo.fx_user_id in (".$thirdFxUserIds.")".$where;
            $order_count = $fxOrderMod->querySql($countSql);
//            dd($order_count);die;
            $size = ceil($order_count[0]['count']/$limit);
            for ($i=1;$i<=$size;$i++){
                $start = ($i - 1) * $limit;
                $sql6 = "select fo.order_sn,fo.is_on,o.add_time,o.payment_time,fo.store_id,fo.id,fu.discount,fu.level,fr.lev1_prop,fr.lev2_prop,fr.lev3_prop,o.goods_amount,fo.pay_money,o.order_state,fo.order_id from bs_fx_order as fo
                      left join bs_order as o on fo.order_id = o.order_id
                      left join bs_fx_user as fu on fu.id = fo.fx_user_id
                      left join bs_fx_rule as fr on fo.rule_id = fr.id where fo.fx_user_id in (".$thirdFxUserIds.")".$where." order by o.payment_time DESC limit {$start},{$limit}";
                $data = $fxOrderMod->querySql($sql6);
                $exportData = array();
                foreach($data as $k=>$v){
                    $prop = $v['lev1_prop']/100;
                    $data[$k]['fxmoney']=number_format($v['pay_money'] *$prop,2);
                    $data[$k]['order_state_name'] = $fxOrderMod->state[$v['is_on']];
                    $data[$k]['storeName'] = $storeMod->getNameById($v['store_id'],$this->defaulLang);
                    $data[$k]['add_time'] = date("Y-m-d H:i",$v['add_time']);
                    $data[$k]['type_name']=$fxOrderMod->getRoomType($v['order_sn']);
                    if ($v['payment_time'] == 0){
                        $data[$k]['payment_time'] = '---';
                    }else{
                        $data[$k]['payment_time'] = date("Y-m-d H:i",$v['payment_time']);
                    }
                    $exportData[$k][] = $k+1;
                    $exportData[$k][] = $v['order_sn']."\t";
                    $exportData[$k][] = $data[$k]['type_name'];
                    $exportData[$k][] = $v['pay_money'];
                    $exportData[$k][] = $data[$k]['fxmoney'];
                    $exportData[$k][] = $v['lev1_prop'];
                    $exportData[$k][] = $data[$k]['storeName'];
                    $exportData[$k][] = $data[$k]['order_state_name'];
                    $exportData[$k][] = $data[$k]['add_time'];
                    $exportData[$k][] = $data[$k]['payment_time'];
                }
            }
        }elseif ($info[0]['level'] == 2) {
            $sss = "select id from bs_fx_user where parent_id={$info[0]['id']} and level=3";
            $result = $fxuserMod->querySql($sss);
            foreach ($result as $v) {
                $ids[] = $v['id'];
            }
            $two_ids = implode(',', $ids);
            $countSql = "select COUNT(fo.order_id) as count  from bs_fx_order as fo
                  left join bs_order as o on fo.order_id = o.order_id
                  left join bs_fx_user as fu on fu.id = fo.fx_user_id
                  left join bs_fx_rule as fr on fo.rule_id = fr.id where fo.fx_user_id in ({$two_ids})".$where;
            $order_count = $fxOrderMod->querySql($countSql);
            $size = ceil($order_count[0]['count']/$limit);
            for ($i=1;$i <= $size;$i++){
                $start = ($i - 1) * $limit;
                $sql = "select fo.order_sn,fo.is_on,o.add_time,o.payment_time,fo.store_id,fo.id,fu.discount,fu.level,fr.lev1_prop,fr.lev2_prop,fr.lev3_prop,o.goods_amount,fo.pay_money,o.order_state,fo.order_id,o.order_amount from bs_fx_order as fo
                  left join bs_order as o on fo.order_id = o.order_id
                  left join bs_fx_user as fu on fu.id = fo.fx_user_id
                  left join bs_fx_rule as fr on fo.rule_id = fr.id where fo.fx_user_id in ({$two_ids})".$where." order by o.payment_time DESC limit {$start},{$limit}";
                $data = $fxOrderMod->querySql($sql);
                foreach ($data as $k => $v) {
                    $prop = $v['lev2_prop'] / 100;
                    $data[$k]['fxmoney'] = number_format($v['pay_money'] * $prop, 2);
                    $data[$k]['order_state_name'] = $fxOrderMod->state[$v['is_on']];
                    $data[$k]['storeName'] = $storeMod->getNameById($v['store_id'], $this->defaulLang);
                    $data[$k]['add_time'] = date("Y-m-d H:i",$v['add_time']);
                    $data[$k]['type_name']=$fxOrderMod->getRoomType($v['order_sn']);
                    if ($v['payment_time'] == 0){
                        $data[$k]['payment_time'] = '---';
                    }else{
                        $data[$k]['payment_time'] = date("Y-m-d H:i",$v['payment_time']);
                    }
                    $exportData[$k][] = $k+1;
                    $exportData[$k][] = $v['order_sn']."\t";
                    $exportData[$k][] = $data[$k]['type_name'];
                    $exportData[$k][] = $v['pay_money'];
                    $exportData[$k][] = $data[$k]['fxmoney'];
                    $exportData[$k][] = $v['lev2_prop'];
                    $exportData[$k][] = $data[$k]['storeName'];
                    $exportData[$k][] = $data[$k]['order_state_name'];
                    $exportData[$k][] = $data[$k]['add_time'];
                    $exportData[$k][] = $data[$k]['payment_time'];
                }
            }
        }
        $fileheader = array('序号','订单号','商品所属类型','实付金额','本单佣金','分销比例','所属店铺','收益状态','下单时间','付款时间');
        include_once ROOT_PATH . '/includes/libraries/csvExport.lib.php';
        $csvExport = new csvExport();
        $csvExport->export_fx_order( $fileheader, $order_count[0]['count'], $info[0]['real_name'],$limit, $exportData);
    }
    /**
     * 导出分销人员
     * @author tangp
     * @date 2018-12-04
     */
    public function exportPerson()
    {
        $sql_rule = "select * from bs_fx_rule  WHERE mark = 1 and find_in_set($this->storeId, store_id)";
        $res_rule = $this->fxuserMod->querySql($sql_rule);
        $str='';
        for($i=0; $i<count($res_rule);$i++ ){
            $str.=$res_rule[$i]['id'].",";
        }
        $str = substr($str,0,-1);
//      print_r($str1);exit;
        $source = $_REQUEST['source'] ? $_REQUEST['source'] : 0;
        $level = $_REQUEST['level'] ? $_REQUEST['level'] : 0;
        $where = " WHERE  rule_id in(" . $str . ") and is_check=2 and mark = 1";
        if ($source) {
            $where .= " and source =" . $source;
            $this->assign('source', $source);
        }
        if ($level) {
            $where .= " and level =" . $level;
            $this->assign('level', $level);
        }
        $sql = 'select * from bs_fx_user ' . $where;
        $res = $this->fxuserMod->querySql($sql);
        foreach ($res as $key => $val) {
            $store_name = $this->storeMod->getNameById($val['store_id'],$this->defaulLang);
            $res[$key]['store_name'] = $store_name;
            $res[$key]['source_name'] = $this->fxuserMod->source[$val['source']];
            //$lev2 = $this->fxuserMod->getUserListByLevel(2, $val['id']);
            $rule = $this->fxruleMod->getRow($val['rule_id']);
            $res[$key]['rule_name'] = $rule['rule_name'];
            $res[$key]['level_name'] =$this->fxuserMod->level[$val['level']];
        }
//        echo '<pre>';print_r($res);
        $fileName = "分销人员.xls";
        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename={$fileName}");
//        echo iconv('utf-8', 'gb2312', "会员名称") . "\t";
//        echo iconv('utf-8', 'gb2312', "分销等级") . "\t";
//        echo iconv('utf-8', 'gb2312', "分销码") . "\t";
//        echo iconv('utf-8', 'gb2312', "手机号") . "\t";
//        echo iconv('utf-8', 'gb2312', "分销规则") . "\t";
//        echo iconv('utf-8', 'gb2312', "优惠比例") . "\t";
//        echo iconv('utf-8', 'gb2312', "开户银行") . "\t";
//        echo iconv('utf-8', 'gb2312', "银行账号") . "\t";
//        echo "\n";
//        foreach ($res as $k => $v) {
//            echo iconv('utf-8', 'gb2312', $v['real_name']) . "\t";
//            echo iconv('utf-8', 'gb2312', $v['level_name']) . "\t";
//            echo iconv('utf-8', 'gb2312', $v['fx_code']) . "\t";
//            echo iconv('utf-8', 'gb2312', $v['phone']) . "\t";
//            echo iconv('utf-8', 'gb2312', $v['rule_name']) . "\t";
//            echo iconv('utf-8', 'gb2312', $v['discount']) . "\t";
//            echo iconv('utf-8', 'gb2312', $v['bank_name']) . "\t";
//            echo iconv('utf-8', 'gb2312', $v['bank_account']) . "\t";
//            echo "\n";
//        }
        echo "<table border='1'>
            <tr>
                <th>".iconv("UTF-8","GB2312//IGNORE","序号")."</th>
                <th>".iconv("UTF-8","GB2312//IGNORE","会员名称")."</th>
                <th>".iconv("UTF-8","GB2312//IGNORE","分销等级")."</th>
                <th>".iconv("UTF-8","GB2312//IGNORE","分销码")."</th>
                <th>".iconv("UTF-8","GB2312//IGNORE","手机号")."</th>
                <th>".iconv("UTF-8","GB2312//IGNORE","分销规则")."</th>
                <th>".iconv("UTF-8","GB2312//IGNORE","优惠比例")."</th>
                <th>".iconv("UTF-8","GB2312//IGNORE","开户银行")."</th>
                <th>".iconv("UTF-8","GB2312//IGNORE","银行账号")."</th>
            </tr>";
        foreach ($res as $k => $v){
            echo "<tr>";
            echo "<td>".iconv("UTF-8","GB2312//IGNORE",$k+1)."</td>";
            echo "<td>".iconv("UTF-8","GB2312//IGNORE",$v['real_name'])."</td>";
            echo "<td>".iconv("UTF-8","GB2312//IGNORE",$v['level_name'])."</td>";
            echo "<td>".iconv("UTF-8","GB2312//IGNORE",$v['fx_code'])."</td>";
            echo "<td style='vnd.ms-excel.numberformat:@'>".$v['phone']."</td>";
            echo "<td>".iconv("UTF-8","GB2312//IGNORE",$v['rule_name'] ? $v['rule_name'] : '---')."</td>";
            echo "<td>".iconv("UTF-8","GB2312//IGNORE",$v['discount'] ? $v['discount'] : '---')."</td>";
            echo "<td>".iconv("UTF-8","GB2312//IGNORE",$v['bank_name'] ? $v['bank_name'] : '---')."</td>";
            echo "<td style='vnd.ms-excel.numberformat:@'>".$v['bank_account']."</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    public function getAreaToStoreAjax($store_id) {
        $area_id = !empty($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) : '';
        //获取区域店铺
        $storeMod = &m('store');
        $storeArr = $storeMod->getStoreArr($area_id, 1);
        $storeOption = make_option($storeArr, $store_id);
        $this->setData($storeOption, $status = '1', '');
    }
}