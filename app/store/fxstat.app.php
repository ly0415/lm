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

class FxstatApp extends  BaseStoreApp
{

    private $lang_id;
    private $fxRevenueLogMod;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
        $this->assign('lang_id', $this->lang_id);
        $this->fxRevenueLogMod = &m('fxRevenueLog');
    }
    /**
     * 析构函数
     */
    public function __destruct(){

    }
    public function index(){
        $lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
        $startTime = !empty($_REQUEST['start_time']) ? strtotime($_REQUEST['start_time']) : '';
        $endTime = !empty($_REQUEST['end_time']) ? strtotime($_REQUEST['end_time']) : '';
        $store_id = $_REQUEST['store_id']?$_REQUEST['store_id']:$this->storeId;
        if (empty($startTime) && empty($endTime)) {
            $startTime = strtotime( date('Y-m-d', strtotime('-7 days')) );
            $endTime = strtotime( date('Y-m-d') );
        }
        if (!empty($startTime) && !empty($endTime) && ($startTime > $endTime)) {
            $t = $endTime;
            $endTime = $startTime;
            $startTime = $t;
        }
        if (!empty($endTime)) {
            $endTime = $endTime + 24 * 3600 - 1;
        }
        $this->assign('stime', date('Y/m/d', $startTime));
        $this->assign('etime', date('Y/m/d', $endTime));

        $where = '  where  store_id ='.$store_id;
        // 筛选条件
        if (!empty($startTime)) {
            $where .= '  and  add_time >= ' . $startTime;
        }
        if (!empty($endTime)) {
            $where .= '  and  add_time <= ' . $endTime;
        }
        $sql = 'SELECT  SUM(lev1_revenue)  AS sum1  ,SUM(lev2_revenue)  AS sum2 ,SUM(lev3_revenue) AS sum3  FROM   bs_fx_revenue_log '.$where;
        $data = $this -> fxRevenueLogMod -> querySql($sql);

        if($this->storeInfo['store_type'] == 1){
            //获取店铺名称
            $storeMod = &m('store');
            $store_data = $storeMod->getStoreArr($this->storeInfo['store_cate_id']);
            $this->assign('store_data', $store_data);
            $this->assign('is_all', "all");
        }

        $this->assign('data', $data[0]);
        $this->assign('lang_id', $lang_id);
        $this->assign('store_id', $store_id);
        $this->display('fxstat/index.html');
    }
}