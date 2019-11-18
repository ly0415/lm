<?php

/**
 * 代客下单
 * @author wangshuo
 * @date 2018-5-10
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class guestListApp extends BaseStoreApp {

    public $storeGoodsMod;
    private $pagesize = 10;
    private $lang_id;
    private $userMod;
    private $userAddressMod;
    private $cityMod;
    private $countryMod;
    private $zoneMod;
    private $storeMod;
    private $orderMod;
    private $sourceListMod;
    private $orderDetailMod;
    private $areaGoodMod;
    private $storeGoodItemPriceMod;
    private  $goodsSpecPriceMod;
    private $goodsMod;


    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->storeGoodsMod = &m('areaGood');
        $this->userMod = &m('user');
        $this->sourceListMod = &m('sourceList');
        $this->userAddressMod = &m('userAddress');
        $this->cityMod = &m('city');
        $this->countryMod = &m('country');
        $this->zoneMod = &m('zone');
        $this->storeMod = &m('store');
        $this->orderMod = &m('order');
        $this->orderDetailMod = &m('orderDetail');
        $this->areaGoodMod = &m('areaGood');
        $this->storeGoodItemPriceMod = &m('storeGoodItemPrice');
        $this->goodsSpecPriceMod=&m('goodsSpecPrice');
        $this->goodsMod=&m('goods');
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
    }

    /**
     * 代客下单
     * @author wangshuo
     * @date 2018-5-10
     */
    public function add() {
        //获取第一页数据
        $storeid = $this->storeId;
        $where = '  where  g.is_on_sale =1 and g.mark=1 and g.store_id = ' . $storeid;
        $sql = 'select  COUNT(*)  as total  from  ' . DB_PREFIX . 'store_goods  where  is_on_sale =1 and mark=1 and store_id = ' . $storeid;
        $res = $this->storeGoodsMod->querySql($sql);
        $total = $res[0]['total'];
        /*  var_dump($total);exit; */
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
        $sql = 'select  g.id,g.goods_name,g.market_price,g.shop_price,gl.original_img,g.goods_id  from  ' . DB_PREFIX . 'store_goods  as g  left join '
                . DB_PREFIX . 'goods as gl on g.goods_id = gl.goods_id ' . $where . $limit;
        $data = $this->storeGoodsMod->querySql($sql);
        foreach ($data as $key => $val) {
            $data[$key]['specarr'] = $this->getSpecitem($val['id']);
        }
        $this->assign('data', $data);
        $this->assign('lang_id', $this->lang_id);
        $this->assign('symbol', $this->symbol);
        $ctgMod = &m('goodsClass');
        $sql = 'SELECT  c.id,l.`category_name`,c.`parent_id`  FROM  ' . DB_PREFIX . 'goods_category AS c
                 LEFT JOIN  ' . DB_PREFIX . 'goods_category_lang AS l  ON c.`id` = l.`category_id`
                 WHERE c.`parent_id` = 0  AND  l.`lang_id` =' . $this->defaulLang;
        $res = $ctgMod->querySql($sql);
        $this->assign('ctglev1', $res);
        $this->display('guestList/dialog.html');
    }

    public function getSpecitem($goodsid) {
        $gSpProceMod = &m('storeGoodItemPrice');
        $sql = 'select `id`,`key`,`key_name`,`price`  from  ' . DB_PREFIX . 'store_goods_spec_price  where  store_goods_id =' . $goodsid;
        $res = $gSpProceMod->querySql($sql);
        return $res;
    }

    /**
     * 获取商品列表
     */
    public function getGoodsList() {
        $storeid = $this->storeId;
        $p = $_REQUEST['p'];
        $gname = $_REQUEST['ctglev3'];
        //分页定义
        $currentPage = !empty($p) ? $p : 1;
        $pagesize = $this->pagesize; //每页显示的条数
        $start = ($currentPage - 1) * $pagesize;
        $end = $pagesize;
        $limit = '  limit  ' . $start . ',' . $end;
        $where = '  where   g.is_on_sale =1 and  g.mark=1 and  g.store_id = ' . $storeid;
        if (!empty($gname)) {
            $where .= '  and  g.cat_id  like "%' . $gname . '%"';
        }
        $sql = 'select  g.id,g.goods_name,g.market_price,g.shop_price,gl.original_img,g.goods_id   from  ' . DB_PREFIX . 'store_goods as g  left join '
                . DB_PREFIX . 'goods as gl on g.goods_id = gl.goods_id ' . $where . $limit;

        $data = $this->storeGoodsMod->querySql($sql);
        foreach ($data as $key => $val) {
            $data[$key]['specarr'] = $this->getSpecitem($val['id']);
        }
        $this->assign('data', $data);
        $this->assign('symbol', $this->symbol);
        $ctgMod = &m('goodsClass');
        $sql = 'SELECT  c.id,l.`category_name`,c.`parent_id`  FROM  ' . DB_PREFIX . 'goods_category AS c
                 LEFT JOIN  ' . DB_PREFIX . 'goods_category_lang AS l  ON c.`id` = l.`category_id`
                 WHERE c.`parent_id` = 0  AND  l.`lang_id` =' . $this->defaulLang;
        $res = $ctgMod->querySql($sql);
        $this->assign('ctglev1', $res);
        $this->assign('lang_id', $this->lang_id);
        $this->display('guestList/goodslist.html');
    }

    /**
     * 分类的三级联动
     */
    public function getctglist() {
        $id = $_REQUEST['id'];
        $ctgMod = &m('goodsClass');
        $sql = 'SELECT  c.id,l.`category_name`,c.`parent_id`  FROM  ' . DB_PREFIX . 'goods_category AS c
                 LEFT JOIN   ' . DB_PREFIX . 'goods_category_lang AS l  ON c.`id` = l.`category_id`
                 WHERE c.`parent_id` = ' . $id . '  AND  l.`lang_id` =' . $this->defaulLang;
        $data = $ctgMod->querySql($sql);
        echo json_encode($data);
        exit;
    }

    /**
     * 代客下单获取用户
     * @author wangshuo
     * @date 2018-5-10
     */
    public function userid() {
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "user where `mark` =1   and  is_kefu = 0 ";
        $totalCount = $this->userMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        /*  var_dump($total);exit; */
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
        $where = " where `mark` =1   and  is_kefu = 0 and s.lang_id = " . $this->defaulLang;
        $where .= " order by `id` desc";
        $limit = '  limit  ' . $start . ',' . $end;
        $sql = "select u.`id`,u.`username`,u.`phone`,u.`email`,u.`add_time`,u.`point`,u.`is_use`,s.store_name from " . DB_PREFIX . "user as u
            left join " . DB_PREFIX . "store_lang as s on u.store_id=s.store_id  and s.distinguish = 0" . $where . $limit;
        $rs = $this->userMod->querySql($sql);
        $this->assign('username', $username);
        $this->assign('list', $rs);
        $this->assign('lang_id', $this->lang_id);
        $this->display('guestList/username.html');
    }

    /**
     * 获取商品列表
     */
    public function getGoodsUser() {
        $p = $_REQUEST['p'];
        $gname = $_REQUEST['gname'];
        $phone = $_REQUEST['phone'];
        $mailbox = $_REQUEST['mailbox'];
        //分页定义
        $currentPage = !empty($p) ? $p : 1;
        $pagesize = $this->pagesize; //每页显示的条数
        $start = ($currentPage - 1) * $pagesize;
        $end = $pagesize;
        $where = " where `mark` =1   and  is_kefu = 0 and s.lang_id = " . $this->defaulLang;
        if (!empty($gname)) {
            $where .= '  and  u.username  like "%' . $gname . '%"';
        }
        if (!empty($phone)) {
            $where .= '  and  u.phone  like "%' . $phone . '%"';
        }
        if (!empty($mailbox)) {
            $where .= '  and  u.email  like "%' . $mailbox . '%"';
        }
        $where .= " order by `id` desc";
        $limit = '  limit  ' . $start . ',' . $end;
        $sql = "select u.`id`,u.`username`,u.`phone`,u.`email`,u.`add_time`,u.`point`,u.`is_use`,s.store_name from " . DB_PREFIX . "user as u
            left join " . DB_PREFIX . "store_lang as s on u.store_id=s.store_id  and s.distinguish = 0" . $where . $limit;
        $data = $this->userMod->querySql($sql);
        $this->assign('data', $data);
        $this->assign('symbol', $this->symbol);
        $this->assign('lang_id', $this->lang_id);
        $this->display('guestList/usernamelist.html');
    }

    /**
     * 搜索用户，统计条数
     */
    public function totalUser() {
        $gname = $_REQUEST['gname'];
        $phone = $_REQUEST['phone'];
        $mailbox = $_REQUEST['mailbox'];
        if (!empty($gname)) {
            $where .= '  and  username like  "%' . $gname . '%"';
        }
        if (!empty($phone)) {
            $where .= '  and  phone like  "%' . $phone . '%"';
        }
        if (!empty($mailbox)) {
            $where .= '  and  email like  "%' . $mailbox . '%"';
        }
        $where .= " order by `id` desc";
        // 获取总数
        $sql = "select count(*) as totalCount from " . DB_PREFIX . "user as u" . $where;
        $res = $this->userMod->querySql($sql);
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

    /**
     * 搜索物品，统计条数
     */
    public function totalPage() {
        $storeid = $this->storeId;
        $gname = $_REQUEST['ctglev3'];
        $where = '  where  is_on_sale =1 and mark=1 and store_id = ' . $storeid;
        if (!empty($gname)) {
            $where .= '  and  cat_id like  "%' . $gname . '%"';
        }
        $sql = 'select  COUNT(*)  as total  from  ' . DB_PREFIX . 'store_goods ' . $where;
        $res = $this->storeGoodsMod->querySql($sql);
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

    //获取商品id

    function  getGoodId($id){
        $sql="select goods_id from ".DB_PREFIX.'store_goods where id='.$id;
        $goodInfo=$this->orderDetailMod->querySql($sql);
        return $goodInfo[0]['goods_id'];

    }

    //获取商品扣除方式
    function getDeduction($id){
        $sql="select deduction from ".DB_PREFIX.'store_goods where id='.$id;
        $goodInfo=$this->orderDetailMod->querySql($sql);
        return $goodInfo[0]['deduction'];
    }


    /**
     * 生成小票编号
     * @author: luffy
     * @date: 2018-08-09
     */
    public function createNumberOrder() {
        //获取当天开始结束时间
        $startDay = strtotime(date('Y-m-d'));
        $endDay = strtotime(date('Y-m-d 23:59:59'));
        $sql = 'select order_sn,number_order from  '
                . DB_PREFIX . 'order where add_time BETWEEN ' . $startDay . ' AND ' . $endDay
                . ' AND mark = 1 and store_id = ' . $this->storeId . ' order by add_time DESC limit 1';
        $res = $this->orderMod->querySql($sql);
        //不管订单存在与否直接加
        $number_order = (int) $res[0]['number_order'] + 1;
        $number_order = str_pad($number_order, 4, 0, STR_PAD_LEFT);
        return $number_order;
    }

    /**
     * 线下找零
     * @author wangshuo
     * @date 2018-5-10
     */
    public function giveChange() {
        $order_id = $_REQUEST['order_sn'];
        $source = $_REQUEST['source'];
        $list = $this->orderMod->getOne(array('cond' => "`order_sn` ='{$order_id}'", 'fields' => "order_sn,order_amount,order_id,pd_amount"));
        $this->assign('symbol', $this->symbol);
        $this->assign('order_id', $list['order_id']);
        $this->assign('list', $list);
        $this->assign('source', $source);
        $this->display('guestList/add.html');
    }

    /**
     * 生成不重复的四位随机数
     * @author wangshuo 
     * @date 2018-5-14
     */
    public function buildNo($limit) {
        $begin = pow(10, 3);
        $end = (pow(10, 4) - 1);
        $rand_array = range($begin, $end);
        shuffle($rand_array); //调用现成的数组随机排列函数
        return array_slice($rand_array, 0, $limit); //截取前$limit个
    }

    /**
     * 获取省市区的地址
     * @author wangshuo 
     * @date 2018-5-14
     */
    public function getAddress($areaAddress) {
        $areaAddress = explode('_', $areaAddress);

        if (count($areaAddress) == 3) {
            $result = $this->cityMod->getAreaName($areaAddress[0]) . ' ' . $this->cityMod->getAreaName($areaAddress[1]) . ' ' . $this->cityMod->getAreaName($areaAddress[2]);
        } elseif (count($areaAddress) == 2) {
            $country = $this->countryMod->getCountryName($areaAddress[0]);
            $zone = $this->zoneMod->getZoneName($areaAddress[1]);
            $result = $country . ' ' . $zone;
        }
        return $result;
    }

    //二维码
    public function goodsZcode($storeid, $order_id) {
        include ROOT_PATH . "/includes/classes/class.qrcode.php"; // 生成二维码库
        $mainPath = ROOT_PATH . '/upload/orderCode';
        $this->mkDir($mainPath);
        $timePath = date('Ymd');
        $savePath = $mainPath . '/' . $timePath;
        $this->mkDir($savePath);
        $newFileName = uniqid() . ".png";
        $filename = $savePath . '/' . $newFileName;
        $pathName = 'upload/orderCode/' . $timePath . '/' . $newFileName;
        $http_host = $_SERVER['HTTP_HOST'];
        $system_web = 'www.711home.net';
        $valueUrl = 'http://' . $system_web . "/index.php?app=print&act=index&orderid={$order_id}";
        QRcode::png($valueUrl, $filename);
        return $pathName;
    }

    /**
     * 订单线下付款ajax判断
     * @author wangs
     * @date 2017-10-26
     */
    public function editOrderState() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
//        $_data = explode("_", $_REQUEST['order_sn']);
        $order_id = $_REQUEST['order_sn'];
//        $ops = $_data[1];
        $rand = $this->buildNo(1);
        $orderNo = date('YmdHis') . $rand[0];
//        $zhaoling = $_REQUEST['zhaoling'];
//        $fukuan = $_REQUEST['youhuidiscount'];
        $source = $_REQUEST['source'];
        $laiyuanshouhuo = $_REQUEST['laiyuanshouhuo'];
        // 主订单修改
        if ($source == '1758421') {
            $data = array(
                'payment_code' => '现金付款',
                'payment_time' => time(),
                'order_state' => 40, //已付款状态
                'Appoint' => 2, //1未被指定 2被指定
                'Appoint_store_id' => $this->storeId, //被指定的站点
                'install_time' => time(), //区域配送安装完成时间
                'buyer_address' => $laiyuanshouhuo, //收货地址补充
                'source_id' => $source, //来源订单的ID 0是默认为艾美商城
                'region_install' => 20, //10未配送 20已配送
                'singleperson' => $_SESSION['store']['userId'], //操作人员ID
            );
        } else {
            $data = array(
                'payment_code' => '现金付款',
                'payment_time' => time(),
                'order_state' => 20, //已付款状态
                'Appoint' => 2, //1未被指定 2被指定
                'Appoint_store_id' => $this->storeId, //被指定的站点
                'install_time' => time(), //区域配送安装完成时间
                'buyer_address' => $laiyuanshouhuo, //收货地址补充
                'source_id' => $source //来源订单的ID 0是默认为艾美商城
            );
        }
        // 子订单修改
        $cond = array(
            'order_sn' => $order_id
        );
        $detail = array(
            'order_state' => 40,
            'shipping_store_id' => $this->storeId
        );
        $res = $this->orderMod->doEditSpec($cond, $data);
        if ($res) {
            $detailRes = $this->orderDetailMod->doEditSpec(array('order_id' => $order_id), $detail);
        }
        //$out_trade_no = '201803151611313930';
        //  更新库存
        $sql = "SELECT sg.goods_id,sg.spec_key,sg.goods_num,sg.deduction,sg.good_id FROM ".
            DB_PREFIX."order as r LEFT JOIN ".
            DB_PREFIX."order_goods as sg ON  r.order_sn = sg.order_id WHERE sg.order_id =".$order_id;
        $orderRes = $this->areaGoodMod->querySql($sql);
        foreach ($orderRes as $k =>$v) {
            if (!empty($v['spec_key'])) {
                if($v['deduction']==1){
                    $query_id = "select `id` from " . DB_PREFIX . "store_goods_spec_price where `goods_id` = '{$v['good_id']}' and `key` = '{$v['spec_key']}' ";
                    $res_query = $this->storeGoodItemPriceMod->querySql($query_id);
                    $specInfoSql = "select goods_storage from  " . DB_PREFIX . "store_goods_spec_price WHERE `goods_id` = '{$v['good_id']}' and `key` = '{$v['spec_key']}'";
                    $specInfo = $this->areaGoodMod->querySql($specInfoSql);
                    foreach($res_query as $key=>$val){
                        $goodStorage=$specInfo[0]['goods_storage'] - $v['goods_num'];
                        if($goodStorage<=0){
                            $goodStorage=0;
                        }
                        $condition = array(
                            'goods_storage' => $goodStorage
                        );
                        $res = $this->storeGoodItemPriceMod->doEdit($val['id'], $condition);
                    }
                    if ($res) {
                        $infoSql = "select goods_storage,id from  " . DB_PREFIX . "store_goods WHERE `goods_id` = '{$v['good_id']}'";
                        $Info = $this->areaGoodMod->querySql($infoSql);
                        $goodsStorage=$Info[0]['goods_storage'] - $v['goods_num'];
                        if($goodsStorage<=0){
                            $goodsStorage=0;
                        }
                        $cond = array(
                            'goods_storage' => $goodsStorage
                        );
                        foreach($Info as $key1=>$val1 ){
                            $this->areaGoodMod->doEdit($val1['id'], $cond);
                        }
                    }
                    $Sql = "select goods_storage from  " . DB_PREFIX . "goods_spec_price WHERE `goods_id` = '{$v['good_id']}' and `key` = '{$v['spec_key']}'";

                    $goodsSpec = $this->areaGoodMod->querySql($Sql);
                    $conditionalStorage=$goodsSpec[0]['goods_storage']-$v['goods_num'];
                    if($conditionalStorage<=0){
                        $conditionalStorage=0;
                    }
                    $conditional=array(
                        'goods_storage'=>$conditionalStorage
                    );
                    $goodsSpecSql="update ".DB_PREFIX."goods_spec_price set goods_storage = ".$conditional['goods_storage']." where goods_id=".$v['good_id']." and `key` ='{$v['spec_key']}'" ;
                    $result=$this->goodsSpecPriceMod->doEditSql($goodsSpecSql);
                    if($result){
                        $goodSql="select goods_storage from  " . DB_PREFIX . "goods WHERE `goods_id` = '{$v['good_id']}'";
                        $goodInfo = $this->areaGoodMod->querySql($goodSql);
                        $goodCondStorage=$goodInfo[0]['goods_storage'] - $v['goods_num'];
                        if($goodCondStorage<=0){
                            $goodCondStorage=0;
                        }
                        $goodCond = array(
                            'goods_storage' => $goodCondStorage
                        );
                        $this->goodsMod->doEdit($v['good_id'],$goodCond);
                    }
                }else{
                    $query_id = "select `id` from " . DB_PREFIX . "store_goods_spec_price where `store_goods_id` = '{$v['goods_id']}' and `key` = '{$v['spec_key']}' ";
                    $res_query = $this->storeGoodItemPriceMod->querySql($query_id);
                    $specInfoSql = "select goods_storage from  " . DB_PREFIX . "store_goods_spec_price WHERE `store_goods_id` = '{$v['goods_id']}' and `key` = '{$v['spec_key']}'";
                    $specInfo = $this->areaGoodMod->querySql($specInfoSql);
                    $conditionStorage=$specInfo[0]['goods_storage'] - $v['goods_num'];
                    if($conditionStorage<=0){
                        $conditionStorage=0;
                    }
                    $condition = array(
                        'goods_storage' =>$conditionStorage
                    );
                    $res = $this->storeGoodItemPriceMod->doEdit($res_query[0]['id'], $condition);
                    if ($res) {
                        $infoSql = "select goods_storage from  " . DB_PREFIX . "store_goods WHERE `id` = '{$v['goods_id']}'";
                        $Info = $this->areaGoodMod->querySql($infoSql);
                        $condStorage=$Info[0]['goods_storage'] - $v['goods_num'];
                        if($condStorage<=0){
                            $condStorage=0;
                        }
                        $cond = array(
                            'goods_storage' => $condStorage
                        );
                        $this->areaGoodMod->doEdit($v['goods_id'], $cond);
                    }
                }
            } else {
                if($v['deduction']==1){
                    $infoSql = "select goods_storage,id from  " . DB_PREFIX . "store_goods WHERE `goods_id` = '{$v['good_id']}'";
                    $Info = $this->areaGoodMod->querySql($infoSql);
                    $condStorage= $Info[0]['goods_storage'] - $v['goods_num'];
                    if($condStorage<=0){
                        $condStorage=0;
                    }
                    $cond = array(
                        'goods_storage' =>$condStorage
                    );
                    foreach($Info as $key1=>$val1 ){
                        $this->areaGoodMod->doEdit($val1['id'], $cond);
                    }
                    $goodSql="select goods_storage from  " . DB_PREFIX . "goods WHERE `goods_id` = '{$v['good_id']}'";

                    $goodInfo = $this->areaGoodMod->querySql($goodSql);
                    $goodCondStorage= $goodInfo[0]['goods_storage'] - $v['goods_num'];
                    if($goodCondStorage<=0){
                        $goodCondStorage=0;
                    }
                    $goodCond = array(
                        'goods_storage' =>$goodCondStorage
                    );
                    $this->goodsMod->doEdit($v['good_id'],$goodCond);
                }else{
                    $specInfoSql = "select goods_storage from  " . DB_PREFIX . "store_goods WHERE `id` = '{$v['goods_id']}'";
                    $specInfo = $this->areaGoodMod->querySql($specInfoSql);
                    $condition=$specInfo[0]['goods_storage'] - $v['goods_num'];
                    if($condition<=0){
                        $condition=0;
                    }
                    $condition = array(
                        'goods_storage' =>$condition
                    );
                    $this->areaGoodMod->doEdit($v['goods_id'],$condition);
                }

            }
        }
        $lang_id = $_REQUEST['lang_id'];
        if ($detailRes) {
//            $info['url'] = "store.php?app=customerOrder&act=index&lang_id={$lang_id}";
            $info['url'] = "store.php?app=order&act=index&lang_id={$lang_id}";
            $this->setData($info, $status = 1, $a['Paymentsuccess']);
        } else {
            $this->setData(array(), $status = 0, $a['Failureofpayment']);
        }
    }

    //生成日志
    public function addPointLog($username, $note, $userid, $deposit, $expend, $order_sn = null) {
        $logData = array(
            'operator' => '--',
            'username' => $username,
            'add_time' => time(),
            'deposit' => $deposit,
            'expend' => $expend,
            'note' => $note,
            'userid' => $userid
        );
        if ($order_sn) {
            $logData['order_sn'] = $order_sn;
        }
        $pointLogMod = &m("pointLog");
        $pointLogMod->doInsert($logData);
    }

    /**
     * 使用优惠券
     * @author wangshuo
     * @date 2018-7-2
     */
    public function getCouponPrice() {
        $this->load($this->shorthand, 'store/store');
        $a = $this->langData;
        $userMod = &m('user');
        $pointSiteMod = &m('point');
        $storePointMod = &m('storePoint');
        $storeMod = &m('store');
        $curMod = &m('currency');
        $userCounponMod = &m('userCoupon');
        $couponMod = &m('coupon');
        $user_id = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;
        $storeid = $_REQUEST['storeid'] ? $_REQUEST['storeid'] : 0;
        $cid = $_REQUEST['cid'] ? (int) $_REQUEST['cid'] : 0;
        $user_info = $userMod->getOne(array("cond" => "id=" . $user_id));
        $order_id = !empty($_REQUEST['order_id']) ? $_REQUEST['order_id'] : '';
        $order_info = $this->orderMod->getOne(array("cond" => "order_sn=" . $order_id));
        $store_id = $_REQUEST['store_id'] ? $_REQUEST['store_id'] : $this->storeId;
        //获取订单总金额
        $totalMoney = $order_info['order_amount']; //原订单价格
        //获取最大睿积分支付比例
        $point_price_site = $pointSiteMod->getOne(array("cond" => "1=1"));
        $store_point_site = $storePointMod->getOne(array("cond" => "store_id=" . $order_info['store_id']));
        $point_price_site['point_price'] = empty($store_point_site) ? 0 : $store_point_site['point_price'];
        //优惠券
        $sql = "select * from " . DB_PREFIX . "coupon where id=" . $cid;
        $cData = $couponMod->querySql($sql);


        if ($totalMoney < $cData[0]['money']) {
            $this->setData('', 0, $a['Rui_most']);
        }
        $pd_amount = (float) $order_info['pd_amount'];
        if (!empty($pd_amount)) {
            $this->setData('', 0, $a['Use_Rui']);
        }

        if (!empty($order_info['cp_amount'])) {
            $order_price = number_format(($totalMoney - $cData[0]['discount'] + $order_info['cp_amount']), 2, '.', '');
        } else {
            $order_price = number_format(($totalMoney - $cData[0]['discount']), 2, '.', '');
        }
        if (!empty($order_info['cid'])) {
            $info = array('user_id' => $user_id, 'c_id' => $order_info['cid'], 'store_id' => $this->storeId, 'remark' => '发送优惠券', 'type' => 1);
            $rs = $userCounponMod->doInsert($info);
        }

        //优惠金额
        if ($order_price == '0.00') {
            $order_price = 0.01;
        }
        $order_arr = array(
            'pd_amount' => 0.00,
            'order_amount' => $order_price,
            'cp_amount' => $cData[0]['discount'],
            'cid' => $cid
        );
        $order_cond = array(
            'order_sn' => $order_id
        );
        $order_res = $this->orderMod->doEditSpec($order_cond, $order_arr);
        if ($order_res) {
            $where = " c_id=" . $cid . " and user_id=" . $user_id;
            $res = $userCounponMod->doDrops($where);
            $this->setData(array(), $status = 1, $a['Rui_chenggong']);
        } else {
            $this->setData(array(), $status = 0, $a['Rui_shibai']);
        }
    }

}
