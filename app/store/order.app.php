<?php
/**
 * 商家后台
 * @author gao
 * @date 2019/03/19
 */
if (!defined('IN_ECM')) {die('Forbidden');}
class OrderApp extends BaseStoreApp {
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->model = &m('order');
    }
    /*
     * 订单列表
     * @author gao
     * @date 2019-03-19
     */
    public  function index(){
        $orderMod=&m('order');
        $storeMod=&m('store');
        $selectStoreId = $_REQUEST['selectStoreId']; //选择的店铺
        $storeId=$this->storeId; //当前店铺id
        $langId=$this->languageId; //语言id
        $p = $_REQUEST['p'];
        $storeInfo = $storeMod->getOne(array('cond'=>"`id`='{$storeId}'",'store_cate_id,store_type'));//当前店铺信息
        $selectStoreInfo = $storeMod->getSelectStore($storeInfo['store_cate_id'],$langId);
        $_REQUEST['storeId']= $storeId;
        $data = $orderMod->orderList($_REQUEST,$p);
        $orderData = $data['orderData']['list'];
        $page = $data['orderData']['ph'];
        $coditionData = $data['coditionData'];
        $this->assign('selectStoreInfo',$selectStoreInfo);
        $this->assign('selectStoreId',$selectStoreId);
        $this->assign('storeInfo',$storeInfo);
        $this->assign('orderData',$orderData);
        $this->assign('coditionData',$coditionData);
        $this->assign('page',$page);
        $this->assign('langId',$langId);
        $this->assign('storeId',$storeId);
        $this->assign('p',$p);
        //将订单标记为已处理
        $clickandview = !empty($_REQUEST['clickandview']) ? htmlspecialchars(trim($_REQUEST['clickandview'])) : 0;
        if ($clickandview == 1) {
            $orderMod->doEditSql("update bs_order set clickandview=2 where store_id = {$storeId}");
            $orderMod->doEditSql("update bs_order_details_{$storeId} set clickandview=2");
        }
        $this->display("order/index.html");
    }

    /*
     * 接单功能
     * @author gao
     * @date 2019-03-19
     */
    public function sureOrder() {
        $orderMod = &m('order');
        $selectStoreId= $_REQUEST['selectStoreId'];
        $orderGoodsMod = &m('orderGoods');
        $orderSn= !empty($_REQUEST['orderSn']) ? $_REQUEST['orderSn'] : 0;
        // 主订单修改
        $data = array(
            'order_state' => 40, //收货状态
            'Appoint' => 2, //1未被指定 2被指定
            'Appoint_store_id' => $selectStoreId, //被指定的站点
            'install_time' => time(), //区域配送安装完成时间
            'region_install' => 20, //10未配送 20已配送
            'singleperson' => $_SESSION['store']['userId'], //操作人员ID
        );
        $cond = array(
            'order_sn' => "{$orderSn}"
        );
        $condel = array(
            'order_id' => "{$orderSn}"
        );
        $detail = array(
            'order_state' => 40,
            'shipping_store_id' => $selectStoreId,
        );
        $newRes=$orderMod->update_receive_time($selectStoreId,$orderSn,$this->storeUserId);
        $res=$orderMod->doEditSpec($cond, $data);
        $detailRes = $orderGoodsMod->doEditSpec($condel, $detail);
        if ($newRes && $res && $detailRes) {
            $this->setData(array(), $status = 1, '接单成功');
        }else{
            $this->setData(array(), $status = 1, '接单失败');
        }
    }


    public function voucherList(){
        //获取当前店铺业务类型
        $orderMod = &m('order');
        $storebusinessMod = &m('storebusiness');
        $bussInfo1 = $storebusinessMod->getInfoByStoreid($this->storeId);
        $bussIds = array();
        foreach($bussInfo1 as $v) {
            $bussIds[] = $v['buss_id'];
        }
        $sql = "select id from " . DB_PREFIX ."room_type where superior_id in (".implode(',', $bussIds).")";
        $bussInfo2 = $orderMod->querySql($sql);
        foreach($bussInfo2 as $v) {
            $bussIds[] = $v['id'];
        }
        $couponMod=&m('coupon');
        $sql="select count(*) as total from bs_coupon where type=2 and mark=1 and source=2 and room_type_id in (" . implode(',', $bussIds) . ")";
        $res = $couponMod->querySql($sql);
        $total = $res[0]['total'];
        $pagesize = 10;
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
        $sql = "SELECT c.id,c.add_time,c.money,rtl.type_name FROM bs_coupon as c LEFT JOIN bs_room_type_lang as rtl ON c.room_type_id =rtl.type_id 
            WHERE c.type=2 and c.mark=1 and c.source=2 and c.room_type_id in (".implode(',', $bussIds).") AND rtl.lang_id = {$this->defaulLang}   order by c.id desc".$limit;
        $voucherData = $couponMod->querySql($sql);
        $this->assign('data', $voucherData);
        $this->display('order/voucherList.html');
    }


    //获取每页的电子劵
    public function getVoucherList()
    {
        $orderMod = &m('order');
        $p = $_REQUEST['p'];
        $couponMod=&m('coupon');
        $cat_1 = !empty($_REQUEST['cat_1']) ? htmlspecialchars(trim($_REQUEST['cat_1'])) : 0;
        $cat_2 = !empty($_REQUEST['cat_2']) ? htmlspecialchars(trim($_REQUEST['cat_2'])) : 0;
        //获取当前店铺业务类型
        $storebusinessMod = &m('storebusiness');
        $bussInfo1 = $storebusinessMod->getInfoByStoreid($this->storeId);
        $bussIds = array();
        foreach($bussInfo1 as $v) {
            $bussIds[] = $v['buss_id'];
        }
        $sql = "select id from " . DB_PREFIX ."room_type where superior_id in (".implode(',', $bussIds).")";
        $bussInfo2 = $orderMod->querySql($sql);
        foreach($bussInfo2 as $v) {
            $bussIds[] = $v['id'];
        }
        //分页定义
        $currentPage = !empty($p) ? $p : 1;
        $pagesize = 10; //每页显示的条数
        $start = ($currentPage - 1) * $pagesize;
        $end = $pagesize;
        $limit = '  limit  ' . $start . ',' . $end;
        $where = '';
        if (!empty($cat_2)) {
            $where = ' and c.room_type_id = ' . $cat_2;
        } elseif (!empty($cat_1)) {
            //获取对应二级业务类型id
            $sql = "select id from " . DB_PREFIX ."room_type where superior_id = {$cat_1} ";
            $cateInfo2 = $orderMod->querySql($sql);
            $cateids = array($cat_1);
            foreach ($cateInfo2 as $v) {
                $cateids[] = $v['id'];
            }
            $where .= " and c.room_type_id in (".implode(',', $cateids).")";
        }
        $sql = "SELECT c.id,c.add_time,c.money,rtl.type_name FROM bs_coupon as c LEFT JOIN bs_room_type_lang as rtl ON c.room_type_id =rtl.type_id
            WHERE c.type=2 and c.mark=1 and c.source=2 and c.room_type_id in (" . implode(',', $bussIds) . ") AND rtl.lang_id = {$this->defaulLang}  ". $where. "   order by c.id desc".$limit;
        $voucherData = $couponMod->querySql($sql);
        $this->assign('data', $voucherData);
        $this->display('order/pageVoucherList.html');
    }

    //电子劵总数
    public function voucherTotal() {
        //获取当前店铺业务类型
        $orderMod =&m('order');
        $storebusinessMod = &m('storebusiness');
        $bussInfo1 = $storebusinessMod->getInfoByStoreid($this->storeId);
        $bussIds = array();
        foreach($bussInfo1 as $v) {
            $bussIds[] = $v['buss_id'];
        }
        $sql = "select id from " . DB_PREFIX ."room_type where superior_id in (".implode(',', $bussIds).")";
        $bussInfo2 = $orderMod->querySql($sql);
        foreach($bussInfo2 as $v) {
            $bussIds[] = $v['id'];
        }

        $couponMod=&m('coupon');
        $cat_1 = !empty($_REQUEST['cat_1']) ? htmlspecialchars(trim($_REQUEST['cat_1'])) : 0;
        $cat_2 = !empty($_REQUEST['cat_2']) ? htmlspecialchars(trim($_REQUEST['cat_2'])) : 0;
        $where = '';
        if (!empty($cat_2)) {
            $where = ' and room_type_id = ' . $cat_2;
        } elseif (!empty($cat_1)) {
            //获取对应二级业务类型id
            $sql = "select id from " . DB_PREFIX ."room_type where superior_id = {$cat_1} ";
            $cateInfo2 = $orderMod->querySql($sql);
            $cateids = array($cat_1);
            foreach ($cateInfo2 as $v) {
                $cateids[] = $v['id'];
            }
            $where .= " and room_type_id in (".implode(',', $cateids).")";
        }
        $sql="select count(*) as total from bs_coupon where type=2 and mark=1 and source=2 and room_type_id in (" . implode(',', $bussIds) . ") ".$where;
        $res = $couponMod->querySql($sql);
        $total = $res[0]['total'];
        $pagesize = 10;
        $totalpage = ceil($total / $pagesize);
        if (!empty($totalpage)) {
            echo json_encode(array('total' => $totalpage));
            exit;
        } else {
            echo json_encode(array('total' => 1));
            exit;
        }
    }

    //后台赠送兑换劵
    public function sendVoucher(){
        $selectStoreId = $_REQUEST['selectStoreId'];
        $orderSn=!empty($_REQUEST['order_sn']) ? $_REQUEST['order_sn'] : '';
        $voucherId=!empty($_REQUEST['voucherId']) ? $_REQUEST['voucherId']: "";
        $userCouponMod=&m('userCoupon');
        $orderStoreMod=&m('order'.$selectStoreId);
        $orderMod = &m('order');
        $userMod = &m('user');
        $couponMod=&m('coupon');//电子劵表
        $userCouponLogData=$userCouponMod->getOne(array('cond'=>"`order_sn`='{$orderSn}'",'id'));
        $couponData=$couponMod->getOne(array('cond'=>"`id`='{$voucherId}'",'fields'=>'limit_times'));
        $orderData=$orderStoreMod->getOne(array('cond'=>"`order_sn`='{$orderSn}'",'fields'=>'buyer_id')); //新表数据
        $orderInfo = $orderMod -> getOne(array('cond'=>"`order_sn`='{$orderSn}'",'fields'=>'order_id')); //老表数据
        $limitTiems=$couponData['limit_times']*3600*24;
        $nowTime=time();
        if(empty($voucherId)){
            $this->setData("",0,'请选择兑换券');
        }
        if(!empty($userCouponLogData)){
            $this->setData("",0,'该笔订单已经赠送过劵了');
        }
        $userCouponData=array(
            "user_id"=>$orderData['buyer_id'],
            'c_id'=>$voucherId,
            'remark'=>"后台赠送",
            'add_time'=>time(),
            'source'=>1,
            'order_id'=>$orderInfo['order_id'],
            'start_time'=>$nowTime,
            'end_time'=>$nowTime+$limitTiems,
            'add_user'=>$this->storeUserId,
            'order_sn'=>$orderSn
        );
        $res=$userCouponMod->doInsert($userCouponData);
        /*$userMod->sendMessage($orderData['buyer_id']);*/
        if($res){
            $this->setData("",1,"赠送成功");
        }else{
            $this->setData("",0,'赠送失败');
        }
    }

    /**
     * 新的订单详情页面
     * @author tangp
     * @date 2019-03-20
     */
    public function orderDetails()
    {
        $order_sn = !empty($_REQUEST['order_sn']) ? $_REQUEST['order_sn'] : '201903060956463646'; //TODO  待定取死的订单号
        $orderMod = &m('order');
        $res = $orderMod->selectOrderInfo($order_sn);
        $sqls = "SELECT cid FROM bs_order WHERE order_sn = {$order_sn}";
        $result = $orderMod->querySql($sqls);
        $fxCode = $orderMod->getFxCode($order_sn);
        $this->assign('fxCode',$fxCode);
        $couponData = "SELECT `type` FROM `bs_coupon` WHERE id = " . $result[0]['cid'];
        $data = $orderMod->querySql($couponData);
        if ($data[0]['type'] == 1) {
            $sql1 = "SELECT `type`,`discount`,`money` FROM `bs_coupon` WHERE id = " . $result[0]['cid'];
            $datass = $orderMod->querySql($sql1);
            $this->assign('datass',$datass);
            $order_sn1 = explode('-', $order_sn);
            $logSql = "SELECT user_coupon_id FROM bs_coupon_log WHERE order_sn = {$order_sn1[0]}";
            $ress = $orderMod->querySql($logSql);
            $sss = "SELECT start_time,end_time FROM bs_user_coupon WHERE id = " . $ress[0]['user_coupon_id'];
            $userCouponData = $orderMod->querySql($sss);
            $this->assign('userCouponData',$userCouponData);
        }else if ($data[0]['type'] == 2){
            $sqlsss = "SELECT cid FROM bs_order WHERE order_sn = '{$order_sn}'";
            $results = $orderMod->querySql($sqlsss);
            $sql1 = "SELECT `type`,`discount`,`money` FROM `bs_coupon` WHERE id = " . $results[0]['cid'];
            $datass = $orderMod->querySql($sql1);
            $this->assign('datass',$datass);
            $logSql = "SELECT user_coupon_id FROM bs_coupon_log WHERE order_sn = '{$order_sn}'";
            $ress = $orderMod->querySql($logSql);
            $sss = "SELECT start_time,end_time FROM bs_user_coupon WHERE id = " . $ress[0]['user_coupon_id'];
            $userCouponData = $orderMod->querySql($sss);
            $this->assign('userCouponData',$userCouponData);
        }

        $refundInfo = $orderMod->getRefundRecord($order_sn, $this->storeId, $res[0]['goods']);
        $userInfo = &m('userInfo');
        $sourceData= $userInfo->source;
        $this->assign('sourceData',$sourceData);
        $infoData = $userInfo->getUserInfo($order_sn);
        $this->assign('infoData',$infoData);
        $datas = $userInfo->countUserInfo($order_sn);
        $this->assign('datas',$datas);
        $sql = "SELECT * FROM bs_user_order WHERE order_sn = {$order_sn}";
        $store_id = &m('userOrder')->querySql($sql);
        $userAddress = $orderMod->getOrderAddress($order_sn,$store_id[0]['store_id']);
        $goodsCount = count($res[0]['goods']);
        $this->assign('userAddress',$userAddress);
        $this->assign('result',$result);
        $this->assign('res',$res);
        $this->assign('goodsCount',$goodsCount);
        $this->assign('refundInfo',$refundInfo);
        $this->assign('payment_type',$res[0]['orderRelation'][0]);
        $this->assign('pay_sn',$res[0]['orderDetail'][0]);
        $this->display('orderList/details.html');

    }

    /*
   * 指派订单审核
   * @author gao
   * @date 2019-03-19
   */

    public function checkAppointOrder(){
        $orderSn = !empty($_REQUEST['orderSn']) ? $_REQUEST['orderSn'] : '';
        /*$selectStoreId = $_REQUEST['selectStoreId'];*/
        $appointLogMod =&m('appointLog');
        $userOrderMod = &m('userOrder');
        $userOrderdata = $userOrderMod->getOne(array("cond"=>"`order_sn` = '{$orderSn}'"));
        $appointLogData = $appointLogMod->getOne(array('cond'=>"`order_sn`='{$orderSn}'",'fields'=>'*')); //老表数据
        $appointStore = $appointLogData['appoint_store']; //指派店铺
        $originalStore = $appointLogData['original_store']; //原来店铺
        $appointOrderMod = &m('order'.$appointStore);
        $appointDetailOrderMod = &m('orderDetails'.$appointStore);
        $appointRelationOrderMod = &m('orderRelation'.$appointStore);
        $originalOrderMod = &m('order'.$originalStore);
        $originalRelationOrderMod = &m('orderRelation'.$originalStore);
        $originalDetailOrderMod = &m('orderDetails'.$originalStore);
        // 主订单修改
        $logCond = array(
            'order_sn' => "{$orderSn}"
        );
        $appointLog = array(
            'is_ckeck'=>1,
            'is_ckeck_user'=>$this->storeUserId
        );
        $logRes=$appointLogMod->doEditSpec($logCond, $appointLog);
        //删除老表数据
        $originalOrderData = $originalOrderMod->getOne(array("cond"=>"order_sn = '{$orderSn}'"));
        $originalRelationOrderData = $originalRelationOrderMod->getOne(array("cond"=>"order_id = '{$originalOrderData['id']}'"));
        $originalDetailOrderData = $originalDetailOrderMod->getOne(array("cond"=>"order_id = '{$originalOrderData['id']}'"));
        $originalOrderData['store_id'] =  $appointStore;
        $originalOrderMod->doMark($originalOrderData['id']);
        unset($originalOrderData['id']);
        unset($originalRelationOrderData['id']);
        unset($originalDetailOrderData['id']);
        $res=$appointOrderMod->doInsert($originalOrderData);
        $originalDetailOrderData['order_id'] = $res;
        $originalRelationOrderData['order_id'] = $res;
        $detailRes=$appointDetailOrderMod->doInsert($originalDetailOrderData);
        $relationRes=$appointRelationOrderMod->doInsert($originalRelationOrderData);
        $userOrderMod->doEdit($userOrderdata['id'],array("store_id"=>$appointStore));
        if( $logRes && $res && $detailRes && $relationRes){
            $this->setData('',1,'审核成功');
        }else{
            $this->setData('',0,'审核失败');
        }

    }

    //订单数据刷新
    public function  updateData(){
        $orderMod = &m('order');
        $sql1 = "select o.order_sn as orderSn,uc.*  from bs_user_coupon as uc left join bs_order as o  on o.order_id = uc.order_id where uc.order_id !=0 ";
        $data =$orderMod->querySql($sql1);
        foreach($data as $key=>$val){
            $sql = "update bs_user_coupon set order_sn = '{$val['orderSn']}' where id = {$val['id']} ";
            $orderMod->doEditSql($sql);
        }
    }

    /**
     * 订单退款
     * @author zhangkx
     * @date 2019/5/5
     */
    public function auditRefund()
    {
        $orderSn = !empty($_REQUEST['orderSn']) ? $_REQUEST['orderSn'] : '';
        if (IS_POST) {
            $orderSn = $_REQUEST['order_sn'] ? $_REQUEST['order_sn'] : '';
            $this->model->orderRefund($orderSn, $this->storeId, $this->storeUserId);
            $this->setData(array('url'=>'store.php?app=order&act=orderDetails&order_sn='.$orderSn),1,'审核成功');
        }
        $this->assign('orderSn', $orderSn);
        $this->display('orderList/auditRefund.html');
    }





}
