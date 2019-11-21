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

class FxstatApp extends BackendApp {

    private $fxRevenueLogMod;
    private $storeMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->fxRevenueLogMod = &m('fxRevenueLog');
        $this->storeMod = &m('store');
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    public function index() {
        $startTime = !empty($_REQUEST['start_time']) ? strtotime($_REQUEST['start_time']) : '';
        $endTime = !empty($_REQUEST['end_time']) ? strtotime($_REQUEST['end_time']) : '';
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : 0;

        if (empty($startTime) && empty($endTime)) {
            $startTime = strtotime(date('Y-m-d', strtotime('-7 days')));
            $endTime = strtotime(date('Y-m-d'));
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
        $this->assign('store_id', $store_id);
        $where = '   where  1=1 ';
        //站点选择
        if (!empty($store_id)) {
            $where .= '  and  store_id =' . $store_id;
        }
        // 筛选条件
        if (!empty($startTime)) {
            $where .= '  and  add_time >= ' . $startTime;
        }
        if (!empty($endTime)) {
            $where .= '  and  add_time <= ' . $endTime;
        }
        $sql = 'SELECT  SUM(lev1_revenue)  AS sum1  ,SUM(lev2_revenue)  AS sum2 ,SUM(lev3_revenue) AS sum3  FROM   bs_fx_revenue_log ' . $where;
        $data = $this->fxRevenueLogMod->querySql($sql);
        $this->assign('data', $data[0]);
        $this->assign('store', $this->getUseStore());
        $this->display('fxstat/index.html');
    }

    /**
     * 获取启用的站点
     * @author wanyan
     * @date 2017-09-07
     */
    public function getUseStore() {
        $where = '  where  1=1  and   c.is_open =1 and l.distinguish = 0  and l.lang_id=' . $this->lang_id;
        if (!empty($this->roleCountry)) {
            $where .= '  and  c.store_cate_id = ' . $this->roleCountry;
        }
//        $sql = 'SELECT  id,store_name  FROM  bs_store  ' . $where . '  order by id ';

        $sql = 'select  c.id,l.store_name  from ' . DB_PREFIX . 'store  as c
                 left join  ' . DB_PREFIX . 'store_lang as l on  c.id = l.store_id ' . $where . '  order by  c.id ';
        $rs = $this->storeMod->querySql($sql);
        return $rs;
    }

}
