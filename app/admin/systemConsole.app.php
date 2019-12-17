<?php

/**
 * 控制台
 * @author  luffy
 * @date    2018-09-07
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class SystemConsoleApp extends BackendApp {

    private $model;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->model = &m('systemConsole');
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    /**
     * 控制台
     * @author  luffy
     * @date    2018-09-07
     */
    public function index() {
        //页面语言设置
        $langData   = $this->model->viewLang($this->lang_id);
        $this->assign('langData', $langData);
        //获取各个被控制功能状态
        $allStatus = $this->model->getAllStatus();
        $this->assign('allStatus', $allStatus);
        //获取自动收货时间天数
        $allDelivery = $this->model->getAllDelivery();
        //获取兑换券的时间
        $allCouponTime = $this->model->getCoupon();
        //获取设置注册送电子券状态
        $getCouponActivityStatus = $this->model->getCouponActivityStatus();
        //获取设置的抵扣券
        $getSetCoupon=$this->model->getSetCoupon();
        //获取设置的兑换券
        $getSetDuiCoupon=$this->model->getSetDuiCoupon();
        $this->assign('allCouponTime',$allCouponTime);
        $this->assign('allStatus'   , $allStatus);
        $this->assign('allDelivery' , $allDelivery);
        $this->assign('getCouponActivityStatus',$getCouponActivityStatus);
        $this->assign('getSetCoupon',$getSetCoupon);
        $this->assign('getSetDuiCoupon',$getSetDuiCoupon);
        $this->display('systemConsole/index.html');
    }

    /**
     * 开关设置
     * @author  luffy
     * @date    2018-09-07
     */
    public function set() {
        $id = $_REQUEST['id'] ? intval($_REQUEST['id']) : 0;
        $Enable = $_REQUEST['Enable'] ? intval($_REQUEST['Enable']) : 0;
        if (empty($id)) {
            $this->jsonError($this->langDataBank->public->system_error);
        }
        $res = $this->model->doEdit($id, array(
            'status' => $Enable
        ));
        if ($res) {
            $this->setData(array(), $status = 1, $this->langDataBank->public->cz_success);
        } else {
            $this->setData(array(), $status = 0, $this->langDataBank->public->cz_error);
        }
    }

    /**
     * 收货提交按钮
     * @author  wangshuo
     * @date    2018-09-12
     */
    public function delivery() {
        $id = $_REQUEST['delivery_id'] ? intval($_REQUEST['delivery_id']) : 0;
        $delivery_time = $_REQUEST['delivery_time'] ? intval($_REQUEST['delivery_time']) : 0;
        if (empty($id)) {
            $this->jsonError($this->langDataBank->public->system_error);
        }
        if ($delivery_time < 1 || $delivery_time > 10) {
            $this->setData(array(), $status = 0, $this->langDataBank->project->receipt_day);
        }
        $res = $this->model->doEdit($id, array(
            'delivery_time' => $delivery_time
        ));
        if ($res) {
            $this->setData(array(), $status = 1, $this->langDataBank->public->cz_success);
        } else {
            $this->setData(array(), $status = 0, $this->langDataBank->public->cz_error);
        }
    }

    /**
     * 打印驱动设置
     * @author  luffy
     * @date    2018-10-22
     */
    public function setPrinter() {
        $printer_1 = $_REQUEST['printer_1'] ? intval($_REQUEST['printer_1']) : 0;
        $printer_2 = $_REQUEST['printer_2'] ? intval($_REQUEST['printer_2']) : 0;
        $res1 = $this->model->doEdit(3, array(
            'delivery_time' => $printer_1
        ));
        $res2 = $this->model->doEdit(4, array(
            'delivery_time' => $printer_2
        ));
        if ($res1 && $res2) {
            $this->setData(array(), $status = 1, $this->langDataBank->public->cz_success);
        } else {
            $this->setData(array(), $status = 0, $this->langDataBank->public->cz_error);
        }
    }

    /**
     * 设置兑换券的时间
     * @author tangp
     * @date 2019-01-19
     */
    public function setCouponTime(){
        $id = $_REQUEST['coupon_time_id'] ? intval($_REQUEST['coupon_time_id']) : 0;
        $start_time = $_REQUEST['start_time'] ? $_REQUEST['start_time'] : 0;
        $end_time   = $_REQUEST['end_time'] ? $_REQUEST['end_time'] : 0;
        $systemConsoleMod = &m('systemConsole');
        //先搜索是否存在时间
        $sql = "SELECT * FROM bs_system_console WHERE type=3 AND status = 1";
        $result = $systemConsoleMod->querySql($sql);
        if (!$result){
            $data = array(
                'type' => 3,
                'start_time'   => $start_time -28800,
                'end_time'     => $end_time +57599,
            );
            $res = $systemConsoleMod->doInsert($data);
        }else{
            $sql = "SELECT * FROM bs_system_console WHERE id={$id} AND status = 1 AND type=3";
            $res = $systemConsoleMod->querySql($sql);
            $start = $start_time-28800;
            $end   = $end_time+57599;
            if ($res){
                if($start != intval($res[0]['start_time']) || $end != intval($res[0]['end_time'])){
                    $this->model->doEdit($id, array(
                        'status' => 0
                    ));
                    $data = array(
                        'type' => 3,
                        'start_time'   => $start_time -28800,
                        'end_time'     => $end_time +57599,
                    );
                    $res = $systemConsoleMod->doInsert($data);
                }

            }
        }
        if ($res) {
            $this->setData(array(), $status = 1, $this->langDataBank->public->cz_success);
        } else {
            $this->setData(array(), $status = 0, $this->langDataBank->public->cz_error);
        }
    }

    /**
     * 设置兑换券的时间范围状态
     * @author tangp
     * @date 2019-01-21
     */
    public function setCouponStatus(){
        $id = $_REQUEST['id'] ? intval($_REQUEST['id']) : 0;
        $Enable = $_REQUEST['Enable'] ? intval($_REQUEST['Enable']) : 0;
        $res = $this->model->doEdit($id, array(
            'status' => $Enable
        ));
        if ($res) {
            $this->setData(array(), $status = 1, $this->langDataBank->public->cz_success);
        } else {
            $this->setData(array(), $status = 0, $this->langDataBank->public->cz_error);
        }
    }

    /**
     * 设置注册送电子券状态
     * @author tangp
     * @date 2019-02-14
     */
    public function setCouponActivity()
    {
        $id = $_REQUEST['id'] ? intval($_REQUEST['id']) : 0;
        $Enable = $_REQUEST['Enable'] ? intval($_REQUEST['Enable']) : 0;
        $res = $this->model->doEdit($id,array(
            'status' => $Enable
        ));
        if ($res){
            $this->setData(array(),$status = 1,$this->langDataBank->public->cz_success);
        }else{
            $this->setData(array(),$status = 0,$this->langDataBank->public->cz_error);
        }
    }

    /**
     * 配置抵扣券
     * @author tangp
     * @date 2019-02-14
     */
    public function configCoupon()
    {
        $rebate_id = $_REQUEST['rebate_id'];
        $id = $_REQUEST['id'];
//        var_dump($rebate_id);die;
        $systemConsoleMod = &m('systemConsole');
        $data = array(
            'table' => "system_console",
            'cond'  => "id=".$id,
            'set'   => array(
                'rebate_id' => $rebate_id
            ),
        );
        $res = $systemConsoleMod->doUpdate($data);
        if ($res){
            $info['url'] = "admin.php?app=systemConsole&act=index";
            $this->setData($info,1, $this->langDataBank->public->cz_success);
        }else{
            $this->setData(array(),0, $this->langDataBank->public->cz_error);
        }
    }

    /**
     * 配置兑换券
     * @author tangp
     * @date 2019-02-14
     */
    public function configDuiCoupon()
    {
        $voucher_id = $_REQUEST['voucher_id'];
        $id = $_REQUEST['id'];
//        var_dump($rebate_id);die;
        $systemConsoleMod = &m('systemConsole');
        $data = array(
            'table' => "system_console",
            'cond'  => "id=".$id,
            'set'   => array(
                'voucher_id' => $voucher_id
            ),
        );
        $res = $systemConsoleMod->doUpdate($data);
        if ($res){
            $info['url'] = "admin.php?app=systemConsole&act=index";
            $this->setData($info,1, $this->langDataBank->public->cz_success);
        }else{
            $this->setData(array(),0,$this->langDataBank->public->cz_error);
        }
    }
    /**
     * 选择抵扣券
     * @author tangp
     * @date 2019-02-14
     */
    public function selectCoupon()
    {
        $area_id = !empty($_REQUEST['area_id']) ? $_REQUEST['area_id'] : '';
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : '';
        $couponMod = &m('coupon');
        $sql = "select count(*) as total from bs_coupon where mark=1 and type=1";
        $res = $couponMod->querySql($sql);
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
        $sqll = "select * from bs_coupon where mark=1 and type=1". $limit;
        $data = $couponMod->querySql($sqll);
        $storeCateMod = &m('storeCate');
        $areaArr = $storeCateMod->getAreaArr(1,$this->lang_id);
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
        $sqls = "SELECT * FROM bs_system_console WHERE id =4";
        $rebate_id = $this->model->querySql($sqls);

        $this->assign('data', $data);
        $this->assign('rebate_id',$rebate_id);
        $this->assign('totalpage', $totalPage);
        $this->display('systemConsole/selectCoupon.html');
    }

    /**
     * 选择兑换券
     * @author tangp
     * @date 2019-02-14
     */
    public function selectDuiCoupon()
    {
        $area_id = !empty($_REQUEST['area_id']) ? $_REQUEST['area_id'] : '';
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : '';
        $couponMod = &m('coupon');
        $sql = "SELECT count(*) as total FROM bs_coupon as c
                    LEFT JOIN bs_room_type_lang as rtl ON c.room_type_id =rtl.type_id
                    WHERE c.type=2 and c.mark=1 and c.source=2 AND rtl.lang_id = {$this->lang_id}";
        $res = $couponMod->querySql($sql);
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
//        $sqll = "select * from bs_coupon where mark=1 and type=2". $limit;
        $sqll = "SELECT c.id,c.add_time,c.money,rtl.type_name,c.limit_times,c.coupon_name FROM bs_coupon as c
                    LEFT JOIN bs_room_type_lang as rtl ON c.room_type_id =rtl.type_id
                    WHERE c.type=2 and c.mark=1 and c.source=2 AND rtl.lang_id = {$this->lang_id}".$limit;
        $data = $couponMod->querySql($sqll);
        $storeCateMod = &m('storeCate');
        $areaArr = $storeCateMod->getAreaArr(1,$this->lang_id);
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
        $sqls = "SELECT * FROM bs_system_console WHERE id =4";
        $voucher_id = $this->model->querySql($sqls);
        $this->assign('data', $data);
        $this->assign('voucher_id',$voucher_id);
        $this->assign('totalpage', $totalPage);
        $this->display('systemConsole/selectDuiCoupon.html');
    }
    /**
     * 抵扣券列表
     * @author tangp
     * @date 2019-02-14
     */
    public function getCouponList()
    {
        $p = $_REQUEST['p'];

        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : '';
        if (!empty($store_id)){
            $where = " and FIND_IN_SET({$store_id}, store_id) ";
        }
        //分页定义
        $currentPage = !empty($p) ? $p : 1;
        $pageSize = 10; //每页显示的条数
        $start = ($currentPage - 1) * $pageSize;
        $end = $pageSize;
        $limit = ' limit  ' . $start . ',' . $end;
        $couponMod = &m('coupon');
        $sql = "select * from bs_coupon where mark=1 and type=1" . $where .$limit;
//        echo $sql;die;
        $data = $couponMod->querySql($sql);
        $sqls = "SELECT * FROM bs_system_console WHERE id =4";
        $rebate_id = $this->model->querySql($sqls);

        $this->assign('data',$data);
        $this->assign('rebate_id',$rebate_id);
        $this->display('systemConsole/couponList.html');
    }

    /**
     * 统计抵扣券总条
     * @author tangp
     * @date 2019-02-14
     */
    public function totalPage()
    {
        $couponMod = &m('coupon');
//        $area_id = !empty($_REQUEST['area_id']) ? $_REQUEST['area_id'] : '';
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : '';
        $where = " and FIND_IN_SET({$store_id}, store_id) ";
        $sql="select count(*) as total from bs_coupon where mark=1 and type=1".$where;
        $res = $couponMod->querySql($sql);
        $total=$res[0]['total'];
        $pagesize=10;
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
     * 兑换券列表
     * @author tangp
     * @date 2019-02-15
     */
    public function getDuiCouponList()
    {
        $p = $_REQUEST['p'];
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : '';

        $sqls = "select * from bs_store_business where store_id=".$store_id;
        $storeBusinessMod = &m('storebusiness');
        $roomTypeMod=&m('roomType');
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
        //分页定义
        $currentPage = !empty($p) ? $p : 1;
        $pageSize = 10; //每页显示的条数
        $start = ($currentPage - 1) * $pageSize;
        $end = $pageSize;
        $limit = ' limit  ' . $start . ',' . $end;
        if (!empty($store_id)){
            $sql = "SELECT c.id,c.add_time,c.money,rtl.type_name,c.limit_times,c.coupon_name FROM bs_coupon as c
                    LEFT JOIN bs_room_type_lang as rtl ON c.room_type_id =rtl.type_id
                    WHERE c.type=2 and c.mark=1 and c.source=2 AND rtl.lang_id = {$this->lang_id}  and room_type_id in(".$thirds.") " .$limit;
        }else{
            $sql = "SELECT c.id,c.add_time,c.money,rtl.type_name,c.limit_times,c.coupon_name FROM bs_coupon as c
                    LEFT JOIN bs_room_type_lang as rtl ON c.room_type_id =rtl.type_id
                    WHERE c.type=2 and c.mark=1 and c.source=2 AND rtl.lang_id = {$this->lang_id}" .$limit;
        }


        $couponMod = &m('coupon');
        $data = $couponMod->querySql($sql);
//        dd($data);die;
        $sqls = "SELECT * FROM bs_system_console WHERE id =4";
        $voucher_id = $this->model->querySql($sqls);
        $this->assign('data',$data);
        $this->assign('voucher_id',$voucher_id);
        $this->display('systemConsole/duiCouponList.html');
    }

    /**
     * 统计兑换券的张数
     * @author tangp
     * @date 2019-02-15
     */
    public function totalDuiPage()
    {
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : '';
        $sqls = "select * from bs_store_business where store_id=".$store_id;
        $storeBusinessMod = &m('storebusiness');
        $roomTypeMod=&m('roomType');
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
        $couponMod = &m('coupon');
        if (!empty($store_id)){
            $sql = "SELECT count(*) as total FROM bs_coupon as c
                    LEFT JOIN bs_room_type_lang as rtl ON c.room_type_id =rtl.type_id
                    WHERE c.type=2 and c.mark=1 and c.source=2 AND rtl.lang_id = {$this->lang_id} and room_type_id in(".$thirds.") ";
        }else{
            $sql = "SELECT count(*) as total FROM bs_coupon as c
                    LEFT JOIN bs_room_type_lang as rtl ON c.room_type_id =rtl.type_id
                    WHERE c.type=2 and c.mark=1 and c.source=2 AND rtl.lang_id = {$this->lang_id} ";
        }

        $res = $couponMod->querySql($sql);
        $total=$res[0]['total'];
        $pagesize=10;
        $totalpage = ceil($total / $pagesize);
        if (!empty($totalpage)) {
            echo json_encode(array('total' => $totalpage));
            exit;
        } else {
            echo json_encode(array('total' => 1));
            exit;
        }
    }

    public function reload()
    {
        $id = $_REQUEST['id'];
        $systemConsoleMod = &m('systemConsole');
        $data = array(
            'table' => "system_console",
            'cond'  => "id=".$id,
            'set'   => array(
                'rebate_id' => 0
            ),
        );
        $res = $systemConsoleMod->doUpdate($data);
        if ($res){
            $info['url'] = "?app=systemConsole&act=index";
            $this->setData(array(),1,$this->langDataBank->public->cz_success);
        }else{
            $this->setData(array(),0,$this->langDataBank->public->cz_error);
        }
    }
    public function reloadDui()
    {
        $id = $_REQUEST['id'];
        $systemConsoleMod = &m('systemConsole');
        $data = array(
            'table' => "system_console",
            'cond'  => "id=".$id,
            'set'   => array(
                'voucher_id' => 0
            ),
        );
        $res = $systemConsoleMod->doUpdate($data);
        if ($res){
            $info['url'] = "?app=systemConsole&act=index";
            $this->setData(array(),1, $this->langDataBank->public->cz_success);
        }else{
            $this->setData(array(),0, $this->langDataBank->public->cz_error);
        }
    }
}
