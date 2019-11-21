<?php
//电子劵管理
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class couponApp extends BackendApp {
    private $pagesize = 10;

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

    /**
     * 电子劵列表页
     */
    public function index() {
        $couponMod=&m("coupon");//电子劵模型
        $couponLogMod=&m("couponLog");//电子劵使用记录
        $roomTypeMod=&m('roomType');
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;//页码
        $tips = !empty($_REQUEST['op']) ? htmlspecialchars(trim($_REQUEST['op'])) : '';
        $service_store_id = $_REQUEST['service_store_id'];
        $this->assign('lang',$this->lang_id);
        if ($tips == 'dui'){
            // by xt 2018.01.22 类型搜索
            $this->assign('service_area_id',$_REQUEST['service_area_id']);
            $this->assign('service_store_id',$_REQUEST['service_store_id']);
            $this->assign('service_top_id',$_REQUEST['service_top_id']);
            $this->assign('service_second_id',$_REQUEST['service_second_id']);

            // 区域列表
            $area_data = &m('storeCate')->getAreaArr(1,$this->lang_id);

            $service_area_data = array_map(function ($i, $m) {
                return array('id' => $i, 'name' => $m);
            }, array_keys($area_data), $area_data);

            $this->assign('service_area_data', $service_area_data);

            // 店铺列表
            $service_store_data = &m('store')->getStoreArr($_REQUEST['service_area_id'], 1);
            $service_store_data = &m('api')->convertArrForm($service_store_data);

            $this->assign('service_store_data', $service_store_data);

            // 一级分类
            $service_top_data = &m('api')->getTop($_REQUEST['service_store_id']);

            $this->assign('service_top_data', $service_top_data);

            // 二级分类
            $service_second_data = &m('api')->getSecond($_REQUEST['service_top_id']);

            $this->assign('service_second_data', $service_second_data);


            if (!empty($_REQUEST['service_top_id'])){
                $where = ' and c.room_type_id='.$_REQUEST['service_top_id'];
            }
            if (!empty($_REQUEST['service_second_id'])){
                $where2 = ' and c.room_type_id='.$_REQUEST['service_second_id'];
            }
            $totalSql = "select count(*) as totalCount from bs_coupon where type=2 and mark=1 and source=2";
            $totalCount = $couponMod->querySql($totalSql);
            $total = $totalCount[0]['totalCount'];
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
//                echo '<pre>';print_r($arr);
                $thirds=implode(',',$arr);

                $sql = "SELECT c.id,c.add_time,c.money,rtl.type_name,c.limit_times,c.coupon_name FROM bs_coupon as c
                    LEFT JOIN bs_room_type_lang as rtl ON c.room_type_id =rtl.type_id
                    WHERE c.type=2 and c.mark=1 and c.source=2 AND rtl.lang_id = {$this->lang_id} {$where} and room_type_id in(".$thirds.")  order by c.add_time desc";
                if (!empty($_REQUEST['service_top_id'])){
                    $sql = "SELECT c.id,c.add_time,c.money,rtl.type_name,c.limit_times,c.coupon_name FROM bs_coupon as c
                    LEFT JOIN bs_room_type_lang as rtl ON c.room_type_id =rtl.type_id
                    WHERE c.type=2 and c.mark=1 and c.source=2 AND rtl.lang_id = {$this->lang_id} {$where}   order by c.add_time desc";
                }
                if (!empty($_REQUEST['service_second_id'])){
                    $sql = "SELECT c.id,c.add_time,c.money,rtl.type_name,c.limit_times,c.coupon_name FROM bs_coupon as c
                    LEFT JOIN bs_room_type_lang as rtl ON c.room_type_id =rtl.type_id
                    WHERE c.type=2 and c.mark=1 and c.source=2 AND rtl.lang_id = {$this->lang_id} {$where2}   order by c.add_time desc";
                }
            }else{
                $sql = "SELECT c.id,c.add_time,c.money,rtl.type_name,c.limit_times,c.coupon_name FROM bs_coupon as c
                    LEFT JOIN bs_room_type_lang as rtl ON c.room_type_id =rtl.type_id
                    WHERE c.type=2 and c.mark=1 and c.source=2 AND rtl.lang_id = {$this->lang_id}  order by c.add_time desc";
            }
            $rs = $couponMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
            foreach ($rs['list'] as $k => $v){
                $rs['list'][$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
                $rs['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
            }
            $sqls = "SELECT  s.*,l.`name` AS lname ,sl.`store_name` AS sltore_name FROM  bs_store AS s
                LEFT JOIN bs_store_lang AS sl ON sl.`store_id` = s.`id` and  sl.lang_id={$this->lang_id} LEFT JOIN  bs_language AS l ON s.`lang_id` = l.`id`";
            $storeMod = &m('store');
            $storeInfo = $storeMod->querySql($sqls);
            $this->assign('list',$rs['list']);
            $this->assign('storeInfo',$storeInfo);
            $this->assign('page_html', $rs['ph']);
            $this->assign('p',$p);
            $this->display('coupon/duiCoupon.html');
        }else{
            $where=" where source = 2 and mark =1 and type=1";
            $totalSql = "select count(*) as totalCount from bs_coupon " .$where ;
            $totalCount = $couponMod->querySql($totalSql);
            $total = $totalCount[0]['totalCount'];
            $sql="SELECT id,money,discount,store_value,day_times,total_times,recharge_id,store_id,add_time,limit_times,coupon_name FROM ".DB_PREFIX."coupon WHERE  source = 2 and mark =1 and type=1 ORDER BY add_time desc";
            $rs = $couponMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
            foreach ($rs['list'] as $k => $v) {
                /*       $rs['list'][$k]['start_time'] = date('Y-m-d ', $v['start_time']);
                       $rs['list'][$k]['end_time'] = date('Y-m-d ', $v['end_time']);*/
                $rs['list'][$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
                $rs['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
                $res=$couponLogMod->getOne(array('cond'=>"`coupon_id`= '{$v['id']}'",'fields'=>'id'));
                if(!empty($res)){
                    $rs['list'][$k]['display']=1;
                }
            }
            $this->assign('couponData',$rs['list']);
            $this->assign('page_html', $rs['ph']);
            $this->assign('p',$p);
            $this->display("coupon/index.html");
        }
    }
    //电子劵添加展示页面
    public function add(){
        //充值规则
        $rechargeAmountMod = &m('rechargeAmount');
        $ruleSql="SELECT id,c_money,s_money,integral,percent FROM ".DB_PREFIX.'recharge_point WHERE mark=1';
        $ruleData=$rechargeAmountMod->querySql($ruleSql);
        //根据充值金额排序
        $sort = array(
            'direction' => 'SORT_ASC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
            'field'     => 'c_money',       //排序字段
        );
        $arrSort = array();
        foreach($ruleData AS $uniqid => $row){
            foreach($row AS $key=>$value){
                $arrSort[$key][$uniqid] = $value;
            }
        }
        if($sort['direction']){
            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $ruleData);
        }
        foreach($ruleData as $k=>$v){
            $ruleData[$k]['ruleStr']="充值￥".$v['c_money']."送￥".$v['s_money'];
        }
        $this->assign('ruleData',$ruleData);
        $this->display("coupon/add.html");
    }
    //电子劵数据添加
    public function doAdd(){
        $storeMod=&m('store');//店铺模型
        $money=!empty($_REQUEST['money']) ? $_REQUEST['money'] : 0;//条件金额
        $discount=!empty($_REQUEST['discount']) ? $_REQUEST['discount'] : 0;//抵扣金额
        $end_time=!empty($_REQUEST['end_time']) ? $_REQUEST['end_time'] : 0;//结束时间 插件多了8个小时
        $start_time=!empty($_REQUEST['start_time']) ? $_REQUEST['start_time'] : 0;//开始时间
        $day_times=!empty($_REQUEST['day_times']) ? $_REQUEST['day_times'] : 1;//一天使用次数
        $total_times=!empty($_REQUEST['total_times']) ? $_REQUEST['total_times'] : 0;//总共使用次数
        $store_ids=!empty($_REQUEST['store_ids']) ? $_REQUEST['store_ids'] : 0;//选择店铺的ids
        $store_value=!empty($_REQUEST['store_value']) ? $_REQUEST['store_value'] : 0;//1是全部店铺 2是选择店铺
        $rule_id = !empty($_REQUEST['rule_id']) ? $_REQUEST['rule_id'] : 0 ; //充值规则
        $limit_times=!empty($_REQUEST['limit_times']) ? $_REQUEST['limit_times'] :1; //有效天数
        $coupon_name=!empty($_REQUEST['coupon_name']) ? $_REQUEST['coupon_name'] : '';
        $goods_id=!empty($_REQUEST['goods_id']) ? $_REQUEST['goods_id'] : '';
        //处理开始和结束时间
        $start_time=$start_time-(8*3600);
        $end_time=$end_time+(16*3600-1);
        //全部店铺和选择店铺
        if($store_value==1){
            $where=" WHERE store_type > 1 and is_open = 1";
            $sql = 'SELECT id FROM  '.DB_PREFIX .'store'.$where;
            $store_ids_arrs=$storeMod->querySql($sql);
            foreach($store_ids_arrs as $k=>$v){
                $store_ids_arr[]=$v['id'];
            }
            $store_ids=implode(',',$store_ids_arr);
        }
        $data=array(
            'money'=>$money,
            'discount'=>$discount,
            /* 'end_time'=>$end_time,
             'start_time'=>$start_time,*/
            'goods_id' => $goods_id,
            'day_times'=>$day_times,
            'total_times'=>$total_times,
            'store_id'=>$store_ids,
            'recharge_id'=>$rule_id,
            'source'=>2,
            'add_time'=>time(),
            'store_value'=>$store_value,
            'limit_times'=>$limit_times,
            'add_user'=>$this->accountId,
            'coupon_name'=>$coupon_name
        );
//        var_dump($data);die;
        $couponMod=&m('coupon');
        $res=$couponMod->doInsert($data);
        if($res){
            $info['url']="?app=coupon&act=index";
            $this->setData($info,1,'保存成功');
        }else{
            $this->setData('',0,'保存失败');
        }
    }
    //电子劵编辑
    public function edit(){
        $id=!empty($_REQUEST['id']) ? $_REQUEST['id'] : 45;//电子劵id
        $couponMod=&m("coupon");//电子劵模型
        //充值规则
        $rechargeAmountMod = &m('rechargeAmount');
        $ruleSql="SELECT id,c_money,s_money,integral,percent FROM ".DB_PREFIX.'recharge_point WHERE mark=1';
        $ruleData=$rechargeAmountMod->querySql($ruleSql);
        //根据充值金额排序
        $sort = array(
            'direction' => 'SORT_ASC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
            'field'     => 'c_money',       //排序字段
        );
        $arrSort = array();
        foreach($ruleData AS $uniqid => $row){
            foreach($row AS $key=>$value){
                $arrSort[$key][$uniqid] = $value;
            }
        }
        if($sort['direction']){
            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $ruleData);
        }
        foreach($ruleData as $k=>$v){
            $ruleData[$k]['ruleStr']="充值￥".$v['c_money']."送￥".$v['s_money'];
        }
        //电子劵规则
        $couponData=$couponMod->getOne(array('cond'=>"`id`= '{$id}' AND source =2  ",'fields'=>'id,money,discount,end_time,start_time,store_value,day_times,total_times,recharge_id,store_id'));
        $couponData['start_time']=date("Y-m-d",$couponData['start_time']+(8*3600));
        $couponData['end_time']=date("Y-m-d",$couponData['end_time']-(16*3600-1));
        $storeMod=&m('store');
        $sql = 'SELECT  sl.`store_name`,s.id FROM  '.DB_PREFIX .'store AS s LEFT JOIN  '.
            DB_PREFIX.'store_lang AS sl ON sl.`store_id` = s.`id` and  sl.lang_id=' .$this->lang_id.' WHERE s.id in ('.$couponData['store_id'].')';
        $storeData = $storeMod->querySql($sql);
        foreach($storeData as $k=>$v){
            $storeNameStr .=$v['store_name'].',';
        }
        $storeNameStr=rtrim($storeNameStr,',');
        $this->assign("storeNameStr",$storeNameStr);
        $this->assign("storeData",$storeData);
        $this->assign("couponData",$couponData);
        $this->assign('ruleData',$ruleData);
        $this->display("coupon/edit.html");
    }
    //电子劵数据编辑
    public function doEdit(){
        $storeMod=&m('store');//店铺模型
        $id=!empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;//优惠劵Id
        $money=!empty($_REQUEST['money']) ? $_REQUEST['money'] : 0;//条件金额
        $discount=!empty($_REQUEST['discount']) ? $_REQUEST['discount'] : 0;//抵扣金额
        $end_time=!empty($_REQUEST['end_time']) ? $_REQUEST['end_time'] : 0;//结束时间 插件多了8个小时
        $start_time=!empty($_REQUEST['start_time']) ? $_REQUEST['start_time'] : 0;//开始时间
        $day_times=!empty($_REQUEST['day_times']) ? $_REQUEST['day_times'] : 1;//一天使用次数
        $total_times=!empty($_REQUEST['total_times']) ? $_REQUEST['total_times'] : 1;//总共使用次数
        $store_ids=!empty($_REQUEST['store_ids']) ? $_REQUEST['store_ids'] : 0;//选择店铺的ids
        $store_value=!empty($_REQUEST['store_value']) ? $_REQUEST['store_value'] : 0;//1是全部店铺 2是选择店铺
        $rule_id = !empty($_REQUEST['rule_id']) ? $_REQUEST['rule_id'] : 0 ; //充值规则
        //处理开始和结束时间
        $start_time=$start_time-(8*3600);
        $end_time=$end_time+(16*3600-1);
        //全部店铺和选择店铺
        if($store_value==1){
            $where=" WHERE store_type > 1 and is_open = 1";
            $sql = 'SELECT id FROM  '.DB_PREFIX .'store'.$where;
            $store_ids_arrs=$storeMod->querySql($sql);
            foreach($store_ids_arrs as $k=>$v){
                $store_ids_arr[]=$v['id'];
            }
            $store_ids=implode(',',$store_ids_arr);
        }
        $data=array(
            'money'=>$money,
            'discount'=>$discount,
            'end_time'=>$end_time,
            'start_time'=>$start_time,
            'day_times'=>$day_times,
            'total_times'=>$total_times,
            'store_id'=>$store_ids,
            'recharge_id'=>$rule_id,
            'source'=>2,
            'store_value'=>$store_value
        );
        $couponMod=&m('coupon');
        $res=$couponMod->doEdit($id,$data);
        if($res){
            $info['url']="?app=coupon&act=index";
            $this->setData($info,1,'保存成功');
        }else{
            $this->setData('',0,'保存失败');
        }
    }
    //检查电子劵是否使用
    public function checkEdit(){
        $id=!empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;
        $couponLogMod=&m("couponLog");
        $couponLogData=$couponLogMod->getOne(array(array('cond'=>"`coupon_id`= '{$id}'",'fields'=>'id')));
        if(!empty($couponLogData)){
            $this->setData(array(),0,'不可编辑');
        }
        $info['url']="?app=coupon&act=edit&id={$id}";
        $this->setData($info,1,'前往编辑');
    }
    //电子劵删除
    public function del(){
        $id=!empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;
        $coupondMod=&m('coupon');
        $couponLogMod=&m("couponLog");
        /*    $couponLogData=$couponLogMod->getOne(array(array('cond'=>"`coupon_id`= '{$id}'",'fields'=>'id')));
            if(!empty($couponLogData)){
                $this->setData(array(),0,'不可删除');
            }*/
        $res=$coupondMod->doMark($id);
        if($res){
            $this->setData(array(),1,'删除成功');
        }else{
            $this->setData(array(),0,'删除失败');
        }
    }
    //获取店铺列表
    public function storeList(){
        $storeMod=&m('store');
        $where=" WHERE s.store_type > 1 and s.is_open = 1";
        $sql = 'SELECT  sl.`store_name`,s.id FROM  '.DB_PREFIX .'store AS s LEFT JOIN  '.
            DB_PREFIX.'store_lang AS sl ON sl.`store_id` = s.`id` and  sl.lang_id=' .$this->lang_id.$where;
        $storeData = $storeMod->querySql($sql);
        $this->assign('storeData',$storeData);
        $this->display('coupon/storeList.html');
    }

    /**
     * 选择用户
     */
    public function selectUser()
    {
        $userId = $_REQUEST['user_id'] ? $_REQUEST['user_id'] : '';
        $userName = $_REQUEST['user_name'] ? $_REQUEST['user_name'] : '';
        $userMod = &m('user');
        $where = ' where mark = 1';
        $sql = 'select COUNT(*) as total from ' . DB_PREFIX . 'user ' . $where;
        $res = $userMod->querySql($sql);
        $total = $res[0]['total'];
        $pageSize = 10;
        $totalPage = ceil($total / $pageSize);
        if (empty($totalPage)) {
            $totalPage = 1;
        }
        //分页定义
        $currentPage = 1;
        $start = ($currentPage - 1) * $pageSize;
        $end = $pageSize;
        $limit = '  limit  ' . $start . ',' . $end;
        $sql = 'select * from ' . DB_PREFIX . 'user' . $where . $limit;
        $data = $userMod->querySql($sql);
        if ($userId) {
            $userIdList = explode(',', $userId);
            $this->assign('userIdList', $userIdList);
        }
        $this->assign('userId', $userId);
        $this->assign('userName', $userName);
        $this->assign('data', $data);
        $this->assign('totalpage', $totalPage);
        $this->display('coupon/selectMember.html');
    }

    /**
     * 获取用户列表
     * @author zhangkx
     * @date 2018/11/23
     */
    public function getUserList() {
        $p = $_REQUEST['p'];
        $trueName = $_REQUEST['true_name'];
        $phone = $_REQUEST['phone'];
        $userId = $_REQUEST['user_id'] ? $_REQUEST['user_id'] : '';
//        echo '<pre>';print_r($_REQUEST);die;
        //分页定义
        $currentPage = !empty($p) ? $p : 1;
        $pageSize = 10; //每页显示的条数
        $start = ($currentPage - 1) * $pageSize;
        $end = $pageSize;
        $limit = ' limit  ' . $start . ',' . $end;
        $where = ' where mark = 1';

        if (!empty($trueName)) {
            $where .= ' and username like "%' . $trueName . '%"';
        }
        if (!empty($phone)) {
            $where .= ' and phone like "%' . $phone . '%"';
        }
        $userMod = &m('user');
        $sql = 'select * from ' . DB_PREFIX . 'user ' . $where . $limit;
        $data = $userMod->querySql($sql);
        $userIdList = explode(",",$userId);
        $this->assign('userIdList', $userIdList);
        $this->assign('userId', $userId);
        $this->assign('data', $data);
        $this->display('coupon/userList.html');
    }

    /**
     * 统计数量
     * @author zhangkx
     * @date 2018/11/23
     */
    public function totalPage() {
        $trueName = $_REQUEST['true_name'];
        $phone = $_REQUEST['phone'];
        $where = ' where mark = 1';
        if (!empty($trueName)) {
            $where .= ' and username like "%' . $trueName . '%"';
        }
        if (!empty($phone)) {
            $where .= ' and  phone like "%' . $phone . '%"';
        }
        $userMod = &m('user');
        $sql = 'select COUNT(*) as total from ' . DB_PREFIX . 'user ' . $where;
        $res = $userMod->querySql($sql);
        $total = $res[0]['total'];
        $pageSize = 10;
        $totalpage = ceil($total / $pageSize);
        if (!empty($totalpage)) {
            echo json_encode(array('total' => $totalpage));
            exit;
        } else {
            echo json_encode(array('total' => 1));
            exit;
        }
    }
    //获取店铺列表
    public function getStore()
    {
        $storeMod=&m('store');
        $where=" WHERE s.store_type > 1 and s.is_open = 1";
        $sql = 'select  COUNT(*)  as total  from  '.DB_PREFIX .'store AS s' . $where;
        $res = $storeMod->querySql($sql);
        $total = $res[0]['total'];
        $pagesize = $this->pagesize;
        $totalpage = ceil($total / $pagesize);
        if (empty($totalpage)) {
            $totalpage = 1;
        }
        $this->assign('totalpage', $totalpage);
        //分页定义
        $currentPage = 1;
        $start = ($currentPage - 1) * $pagesize;
        $end = $pagesize;
        $limit = '  limit  ' . $start . ',' . $end;
        $sql = 'SELECT  sl.`store_name`,s.id FROM  '.DB_PREFIX .'store AS s LEFT JOIN  '.
            DB_PREFIX.'store_lang AS sl ON sl.`store_id` = s.`id` and  sl.lang_id=' .$this->lang_id.$where.$limit;
        $storeData = $storeMod->querySql($sql);
        $this->assign('data', $storeData);
        $this->display('coupon/store.html');
    }

    //获取每页的店铺列表
    public function getStoreList()
    {
        $p = $_REQUEST['p'];
        $storeMod = &m('store');
        $storeName = $_REQUEST['storeName'];
        //分页定义
        $currentPage = !empty($p) ? $p : 1;
        $pagesize = $this->pagesize; //每页显示的条数
        $start = ($currentPage - 1) * $pagesize;
        $end = $pagesize;
        $limit = '  limit  ' . $start . ',' . $end;
        $where=" WHERE s.store_type > 1 and s.is_open = 1";
        if (!empty($storeName)) {
            $where .= '  and   sl.store_name  like "%' . $storeName . '%"';
        }
        $sql = 'SELECT  sl.`store_name`,s.id FROM  '.DB_PREFIX .'store AS s LEFT JOIN  '.
            DB_PREFIX.'store_lang AS sl ON sl.`store_id` = s.`id` and  sl.lang_id=' .$this->lang_id.$where.$limit;
        $storeData = $storeMod->querySql($sql);
        $this->assign('data', $storeData);
        $this->display('coupon/storeList.html');
    }
    //店铺列表总数
    public function storePages() {
        $storeName = $_REQUEST['storeName'];
        $where=" WHERE s.store_type > 1 and s.is_open = 1";
        $storeMod = &m('store');
        if (!empty($storeName)) {
            $where .= '  and   sl.store_name  like "%' . $storeName . '%"';
        }
        $sql = 'SELECT  count(*) AS total FROM  '.DB_PREFIX .'store AS s LEFT JOIN  '.
            DB_PREFIX.'store_lang AS sl ON sl.`store_id` = s.`id` and  sl.lang_id=' .$this->lang_id.$where;
        $res = $storeMod->querySql($sql);
        $total = $res[0]['total'];
        $pagesize = $this->pagesize;
        $totalpage = ceil($total / $pagesize);
        if (!empty($totalpage)) {
            echo json_encode(array('total' => $totalpage));
            exit;
        } else {
            echo json_encode(array('total' => 1));
            exit;
        }
    }
    //发送优惠劵
    public function sendCoupon(){
        $id=$_REQUEST['id'];//用户id数组
        $couponId=$_REQUEST['couponId'];//抵扣劵Id
        $userCouponMod=&m('userCoupon');//用户电子劵表
        $couponMod=&m('coupon');//电子劵表
        if(empty($id)){
            $this->setData('',0,'请选择用户');
        }
        $idArr=explode(',',$id);
        $couponData=$couponMod->getOne(array('cond'=>"`id`='{$couponId}'",'fields'=>'limit_times'));
        $limitTiems=$couponData['limit_times']*3600*24;
        $nowTime=time();
        //循环分发劵
        $data = array(
            'c_id' => $couponId,
            'remark' => '后台赠送抵扣劵',
            'add_time' => $nowTime,
            'start_time'=>$nowTime,
            'end_time'=>$nowTime+$limitTiems,
            'add_user'=>$this->accountId
        );
        foreach ($idArr as $v) {
            $data['user_id'] = $v;
            $res=$userCouponMod->doInsert($data);

            $this->sendSms($v);
        }
        if($res){
            $info['url']="admin.php?app=coupon&act=index";
            $this->setData($info,1,'赠送成功');
        }else{
            $info['url']="admin.php?app=coupon&act=index";
            $this->setData($info,0,'赠送失败');
        }
    }

    public function sendDuiCoupon()
    {
        $id = $_REQUEST['id'];
        $couponId = $_REQUEST['couponId'];
        $userCouponMod = &m('userCoupon');
        $couponMod=&m('coupon');//电子劵表
        $couponData=$couponMod->getOne(array('cond'=>"`id`='{$couponId}'",'fields'=>'limit_times'));
        $limitTiems=$couponData['limit_times']*3600*24;
        $nowTime=time();
        if(empty($id)){
            $this->setData('',0,'请选择用户');
        }
        $idArr = explode(',',$id);
        $data = array(
            'c_id'     => $couponId,
            'remark'   => '后台赠送兑换劵',
            'add_time' => $nowTime,
            'start_time'=>$nowTime,
            'end_time'=>$nowTime+$limitTiems,
            'add_user'=>$this->accountId
        );
        foreach ($idArr as $v){
            $data['user_id'] = $v;
            $res = $userCouponMod->doInsert($data);

            $this->sendSms($v);
        }
        if($res){
            $info['url']="admin.php?app=coupon&act=index&op=dui";
            $this->setData($info,1,'赠送成功');
        }else{
            $info['url']="admin.php?app=coupon&act=index";
            $this->setData($info,0,'赠送失败');
        }
    }
    /**
     * 添加兑换券
     * @author tangp
     * @date 2019-01-15
     */
    public function addDuiCoupon()
    {
        $this->assign('lang_id',$this->lang_id);
        $sql ="SELECT brtl.type_id,brtl.type_name FROM bs_room_type as brt LEFT JOIN bs_room_type_lang as brtl ON brt.id = brtl.type_id WHERE brt.superior_id = 0 AND brtl.lang_id=".$this->lang_id;
//        echo $sql;die;
        $mod = &m('roomType');
        $type = $mod->querySql($sql);
        $this->assign('type',$type);
        $this->display('coupon/duiCouponAdd.html');
    }

    /**
     * 执行添加兑换券的方法
     * @author tangp
     * @date 2019-01-15
     */
    public function addCoupon()
    {
        $couponMod = &m('coupon');
        $money = !empty($_REQUEST['money']) ? $_REQUEST['money'] : '';
        $start_time = !empty($_REQUEST['start_time']) ? $_REQUEST['start_time'] : '';
        $end_time = !empty($_REQUEST['end_time']) ? $_REQUEST['end_time'] : '';
        $lang_id = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : '';
        $limit_times=!empty($_REQUEST['limit_times']) ? $_REQUEST['limit_times'] :1; //有效天数
        $goods_id=!empty($_REQUEST['goods_id']) ? $_REQUEST['goods_id'] :0;
        $coupon_name=!empty($_REQUEST['coupon_name']) ? $_REQUEST['coupon_name'] : '';
        $top = $_REQUEST['top'] ;
        $second = $_REQUEST['second'];
        if ($second==0){
            $data = array(
                'type'         => 2,
                'goods_id' => $goods_id,
                /* 'start_time'   => $start_time -28800,
                 'end_time'     => $end_time +57599,*/
                'money'        => $money,
                'source'       => 2,
                'add_time'     => time(),
                'room_type_id' => $top,
                'add_user'=>$this->accountId,
                'limit_times'=>$limit_times,
                'coupon_name'=>$coupon_name
            );
        }else{
            $data = array(
                'type'         => 2,
                /*    'start_time'   => $start_time -28800,
                    'end_time'     => $end_time +57599,*/
                'goods_id' => $goods_id,
                'money'        => $money,
                'source'       => 2,
                'add_time'     => time(),
                'room_type_id' => $second,
                'add_user'=>$this->accountId,
                'limit_times'=>$limit_times,
                'coupon_name'=>$coupon_name
            );
        }

        $ress = $couponMod->doInsert($data);
        if ($ress){
            $info['url'] = "admin.php?app=coupon&act=index&op=dui&lang_id=".$lang_id;
            $this->setData($info,'1','添加成功');
        }else{
            $this->setData(array(),'0','添加失败');
        }
    }
    /**
     * 获取二级分类
     * @author tangp
     * @date 2018-11-12
     */
    public function secondType()
    {
        $id = $_REQUEST['superior_id'];
        $sql ="SELECT brtl.type_id,brtl.type_name FROM bs_room_type as brt LEFT JOIN bs_room_type_lang as brtl ON brt.id = brtl.type_id WHERE brt.superior_id = {$id} AND brtl.lang_id=".$this->lang_id;
//        echo $sql;die;
        $mod = &m('roomType');
        $res = $mod->querySql($sql);
        $this->assign('langdata', $this->langData);
        $this->setData($res,1,'');
    }

    /**
     * 删除兑换券
     * @author tangp
     * @date 2019-01-16
     */
    public function deleteDui()
    {
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '';
        $couponMod = &m('coupon');
        $res=$couponMod->doMark($id);
        /*     $sql = "update bs_room_type_coupon set status =2 where coupon_id={$id} and mark=1";
             $roomTypeCouponMod = &m('roomTypeCoupon');
             $r = $roomTypeCouponMod->querySql($sql);*/
        if($res /*&& $r*/){
            $this->setData(array(),1,'删除成功');
        }else{
            $this->setData(array(),0,'删除失败');
        }
    }

    /**
     * 展示业务类型名
     * @author tangp
     * @date 2019-01-16
     */
    public function getRoomType()
    {
        $id = $_REQUEST['id'];
        $lang = $_REQUEST['lang'];
        $sql = "SELECT * FROM bs_room_type_coupon WHERE coupon_id={$id} AND status=1 AND mark=1";
        $roomTypeCouponMod = &m('roomTypeCoupon');
        $res = $roomTypeCouponMod->querySql($sql);

        $sqll = "SELECT * FROM bs_room_type_lang WHERE type_id={$res[0]['room_type_id']} AND lang_id=".$lang;
//        echo $sqll;die;
        $roomTypeLangMod = &m('roomTypeLang');
        $r = $roomTypeLangMod->querySql($sqll);
        if ($r){
            $this->setData($r,1,'');
        }else{
            $this->setData('',0,'');
        }
    }

    /**
     * 修改兑换券的页面
     * @author tangp
     * @date 2019-01-16
     */
    public function editDui()
    {
        $id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : '';

        $sql = "SELECT * FROM bs_coupon WHERE id={$id}";
        $couponMod = &m('coupon');
        $res = $couponMod->querySql($sql);
        foreach ($res as $k => $v){
            $res[$k]['start_time'] = date("Y-m-d",$v['start_time']+(8*3600));
            $res[$k]['end_time']   = date("Y-m-d",$v['end_time']-(16*3600-1));
        }
//        echo '<pre>';print_r($res);die;
        $this->assign('res',$res);
        $this->assign('id',$id);
        $this->display('coupon/editDui.html');
    }

    /**
     * 执行修改兑换券操作
     * @author tangp
     * @date 2019-01-16
     */
    public function editDuiCoupon()
    {
        $start_time = $_REQUEST['start_time'];
        $end_time   = $_REQUEST['end_time'];
        $money      = $_REQUEST['money'];
        $id         = $_REQUEST['id'];
        $lang_id    = $_REQUEST['lang_id'];
        $data = array(
            'start_time' => $start_time,
            'end_time'   => $end_time,
            'money'      => $money
        );
        $couponMod = &m('coupon');
        $res = $couponMod->doEdit($id,$data);
        if ($res){
            $info['url']="admin.php?app=coupon&act=index&op=dui&lang_id=".$lang_id;
            $this->setData($info,1,'修改成功！');
        }else{
            $this->setData('',0,'修改失败！');
        }
    }

    /**
     * 获取一级业务类型
     * @author tangp
     * @date 2019-01-21
     */
    public function getTop()
    {
        $id = $_REQUEST['id'];
        $sql = "SELECT rtl.type_name,rtl.type_id  FROM bs_store_business AS sb
        LEFT JOIN bs_room_type as rt ON sb.buss_id = rt.id
        LEFT JOIN bs_room_type_lang as rtl ON rt.id = rtl.type_id
        WHERE sb.store_id={$id} AND rt.superior_id=0 AND rtl.lang_id=".$this->lang_id;
        $storeBusinessMod=&m('storebusiness');
        $res = $storeBusinessMod->querySql($sql);
        if ($res){
            $this->setData($res,1,'');
        }else{
            $this->setData(array(),0,'');
        }
    }

    /**
     * 获取二级业务
     * @author tangp
     * @date 2019-01-21
     */
    public function getSecond()
    {
        $id=$_REQUEST['id'];
        $sql ="SELECT brtl.type_id,brtl.type_name FROM bs_room_type as brt LEFT JOIN bs_room_type_lang as brtl ON brt.id = brtl.type_id WHERE brt.superior_id = {$id} AND brtl.lang_id=".$this->lang_id;
        $mod = &m('roomType');
        $res = $mod->querySql($sql);
        if ($res){
            $this->setData($res,1,'');
        }else{
            $this->setData(array(),0,'');
        }
    }

    public function editCouponName()
    {
        $id = $_REQUEST['id'];
        $text = $_REQUEST['text'];
        $p = $_REQUEST['p'];
        $couponMod = &m('coupon');
        $data = array(
            'coupon_name' => $text
        );
        $res = $couponMod->doEdit($id,$data);
        if ($res){
            $info['url'] = "?app=coupon&act=index&p=".$p;
            $this->setData($info,1,'');
        }else{
            $this->setData(array(),0,'');
        }
    }
    public function editDuiCouponName()
    {
        $id = $_REQUEST['id'];
        $text = $_REQUEST['text'];
        $p = $_REQUEST['p'];
        $couponMod = &m('coupon');
        $data = array(
            'coupon_name' => $text
        );
        $res = $couponMod->doEdit($id,$data);
        if ($res){
            $info['url'] = "?app=coupon&act=index&op=dui&p=".$p;
            $this->setData($info,1,'');
        }else{
            $this->setData(array(),0,'');
        }
    }

    /**
     * 赠券发送提醒的短信
     * @param int $id 用户id
     * @return bool
     * @author tangp
     * @date 2019-02-13
     */
    public function sendSms($id)
    {
        include_once ROOT_PATH."/includes/AliDy/sendSms.lib.php";
        //找出id对应的用户手机号
        $sql = "SELECT phone,username FROM bs_user WHERE id=".$id;
        $userMod = &m('user');
        $res = $userMod->querySql($sql);

        $params = array();
        $params['PhoneNumbers'] = $res[0]['phone'];
        $params['SignName'] = "艾美睿零售";
        $params['TemplateCode'] = 'SMS_159620070';
        $params['TemplateParam'] = array(
            "name" => $res[0]['username']
        );
        $phoneCode = new sendSms($params);
        $info = $phoneCode->sendSms();
        $info1 = json_encode(json_encode($info),true);

        if ($info1['Message']=='Ok'){
            return true;
        }else{
            return false;
        }
    }


    /**
     * 商品的单选弹窗
     */
    public function spikeDialog() {
        $storeGoodsMod = &m('areaGood');
        //获取第一页数据
        $storeid = $_REQUEST['store_ids'] ? : 0;
        if($storeid){
            $where = '  where  sg.is_on_sale =1 and sg.mark=1 and sg.store_id in (' . $storeid.')';
            $sql = 'select  COUNT(*)  as total  from  ' . DB_PREFIX . 'store_goods  where  is_on_sale =1 and mark=1 and store_id in ( ' . $storeid .')';
        }else{
            $where = '  where  sg.is_on_sale =1 and sg.mark=1 and sg.store_id = 58';
            $sql = 'select  COUNT(*)  as total  from  ' . DB_PREFIX . 'store_goods  where  is_on_sale =1 and mark=1 and store_id = 58 ';
        }
//        var_dump($storeid);die;
//        $where = '  where  sg.is_on_sale =1 and sg.mark=1 and sg.store_id in (' . $storeid.')';
//        $sql = 'select  COUNT(*)  as total  from  ' . DB_PREFIX . 'store_goods  where  is_on_sale =1 and mark=1 and store_id in ( ' . $storeid .')';
        $res = $storeGoodsMod->querySql($sql);
        $total = $res[0]['total'];
        $pagesize = $this->pagesize;
        $totalpage = ceil($total / $pagesize);
        if (empty($totalpage)) {
            $totalpage = 1;
        }
        $this->assign('totalpage', $totalpage);
        //分页定义
        $currentPage = 1;
        $start = ($currentPage - 1) * $pagesize;
        $end = $pagesize;
        $limit = '  limit  ' . $start . ',' . $end;
        $sql = 'select  sg.id,sg.store_id,sg.goods_name,sg.market_price,sg.shop_price,gl.original_img,sg.goods_id  from  '
            . DB_PREFIX . 'store_goods  AS sg LEFT JOIN '
            . DB_PREFIX . 'goods AS gl ON sg.goods_id = gl.goods_id  ' . $where.' AND sg.goods_storage > 0 ' . $limit;
        $data = $storeGoodsMod->querySql($sql);
        foreach ($data as $key => $val) {
            $data[$key]['specarr'] = $this->get_spec($val['goods_id'],$val['id'],2);
            $data[$key]['spec']=$this->getSpec($val['goods_id'],$val['store_id']);
        }
        $this->assign('data', $data);
        $this->assign('lang_id', $this->lang_id);
        $this->assign('symbol', $this->symbol);
        $this->display('coupon/spikeDialog.html');

    }


    /**
     * 获取商品列表
     */
    public function getSpikeGoodsList() {
        $storeGoodsMod = &m('areaGood');
        $storeid = $_REQUEST['store_ids'] ? : 0;
        $p = $_REQUEST['p'];
        $gname = $_REQUEST['gname'];
        //分页定义
        $currentPage = !empty($p) ? $p : 1;
        $pagesize = $this->pagesize; //每页显示的条数
        $start = ($currentPage - 1) * $pagesize;
        $end = $pagesize;
        $limit = '  limit  ' . $start . ',' . $end;
//        $where = '  where  sg.is_on_sale =1 and sg.mark=1 and sg.store_id = ' . $storeid;
        if($storeid){
            $where = '  where  sg.is_on_sale =1 and sg.mark=1 and sg.store_id in (' . $storeid.')';
//            $sql = 'select  COUNT(*)  as total  from  ' . DB_PREFIX . 'store_goods  where  is_on_sale =1 and mark=1 and store_id in ( ' . $storeid .')';
        }else{
            $where = '  where  sg.is_on_sale =1 and sg.mark=1 and sg.store_id = 58';
//            $sql = 'select  COUNT(*)  as total  from  ' . DB_PREFIX . 'store_goods  where  is_on_sale =1 and mark=1 and store_id = 58 ';
        }
        if (!empty($gname)) {
            $where .= '  and  sg.goods_name  like "%' . $gname . '%"';
        }
        $sql = 'select  sg.id,sg.store_id,sg.goods_name,sg.market_price,sg.shop_price,gl.original_img,sg.goods_id  from  '
            . DB_PREFIX . 'store_goods  AS sg LEFT JOIN '
            . DB_PREFIX . 'goods AS gl ON sg.goods_id = gl.goods_id  ' . $where. $limit;

        $data = $storeGoodsMod->querySql($sql);
        foreach ($data as $key => $val) {
            $data[$key]['specarr'] = $this->get_spec($val['goods_id'],$val['id'],2);
            $data[$key]['specarr'] = $this->get_spec($val['goods_id'],$val['id'],2);
            $data[$key]['spec']=$this->getSpec($val['goods_id'],$val['store_id']);
        }
        $this->assign('data', $data);
        $this->assign('symbol', $this->symbol);
        $this->display('coupon/spikeGoodsList.html');
    }



    //获取规格价格
    public function getSpec($store_goods_id, $store_id){
        $storeMod=&m('storeGoods');
        $where=' and  mark=1   and   is_on_sale =1';
        $sql="SELECT id FROM ".DB_PREFIX.'store_goods WHERE store_id='.$store_id.' AND goods_id='.$store_goods_id.$where;
        $data=$storeMod->querySql($sql);
        $id=$data[0]['id'];
        $storeGoodMod = &m("storeGoodItemPrice");
        $spec_data = $storeGoodMod->getData(array('cond' => "store_goods_id=" . $id));
        foreach ($spec_data as $k => $v) {
            $spec_arr[$v['key']] = $v;
        }
        $spec_arr=json_encode($spec_arr);
        return $spec_arr;
    }

    //获取规格项
    public function get_spec($goods_id, $store_goods_id, $type = 1) {
        $storeGoodMod = &m("storeGoodItemPrice");
        //商品规格 价钱 库存表 找出 所有 规格项id
        //$keys = M('SpecGoodsPrice')->where("goods_id", $goods_id)->getField("GROUP_CONCAT(`key` SEPARATOR '_') ");
        if ($type == 1) {
            $sql = "select `key` as item_key from  " . DB_PREFIX . "goods_spec_price where goods_id=" . $goods_id;
        } else {
            $sql = "select `key` as item_key from  " . DB_PREFIX . "store_goods_spec_price where store_goods_id=" . $store_goods_id;
        }
        $keys = $storeGoodMod->querySql($sql);
        $filter_spec = array();
        if ($keys) {
            $key_str = "";
            foreach ($keys as $k => $v) {
                $key_str .= $v['item_key'] . "_";
            }
            $res_item = substr($key_str, 0, strlen($key_str) - 1);
            $keys = str_replace('_', ',', $res_item);
            $specImage = array();
            $sql3 = "select spec_image_id,src from " . DB_PREFIX . "goods_spec_image where goods_id=" . $goods_id; // 规格对应的 图片表， 例如颜色
            $img_list = $storeGoodMod->querySql($sql3);

            foreach ($img_list as $k => $v) {
                $specImage[$v['spec_image_id']] = $v['src'];
            }
            $sql4 = "SELECT a.`order`,b.*,al.spec_name,bl.`item_name`  FROM " . DB_PREFIX . "goods_spec AS a
                     INNER JOIN " . DB_PREFIX . "goods_spec_item AS b ON a.id = b.spec_id
                     LEFT JOIN " . DB_PREFIX . "goods_spec_lang AS al ON a.id=al.spec_id
                     LEFT JOIN " . DB_PREFIX . "goods_spec_item_lang as bl ON b.id=bl.item_id
                     WHERE b.id IN($keys) and al.lang_id=" . $this->defaulLang . " and bl.lang_id=" . $this->defaulLang . " ORDER BY b.id";
            $filter_spec2 = $storeGoodMod->querySql($sql4);
            foreach ($filter_spec2 as $key => $val) {
                $filter_spec[$val['spec_name']][] = array(
                    'item_id' => $val['id'],
                    'item' => $val['item_name'],
                    'src' => $specImage[$val['id']],
                );
            }
        }
        return $filter_spec;
    }

}
