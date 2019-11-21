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

class userStatApp extends BackendApp {


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
        $userMod = &m('user');
        $time = strtotime(date('Y-m-d', strtotime('-7  days')));
        $baseD = $this->getData($time);
        //所以站点
        $stores = $this->getUseStore();
        $userArr = $storesArr = array();
        foreach ($stores as $key => $val) {
            $storesArr[] = $val['store_name'];
            $sql = 'SELECT  COUNT(id)  AS total  FROM  bs_user  WHERE  store_id = ' . $val['id'] . ' and mark =1';
            $res = $userMod->querySql($sql);
            $stores[$key]['userTotal'] = $res[0]['total'];
            $userArr[] = $stores[$key]['userTotal'];
        }
        foreach ($storesArr as $key => $val) {
            $storesArr[$key] = '"' . $val . '"';
        }
        $storeSting = implode(',', $storesArr);
        $userSting = implode(',', $userArr);
        $this->assign('storeSting', $storeSting);
        $this->assign('userSting', $userSting);
        $this->assign('stores', $stores);
        $this->assign('baseD', $baseD);
        $this->display('userstat/index.html');
    }

    /**
     * 基本统计
     */
    public function baseStat() {
        $opInfo = $_REQUEST['opInfo'];
        //一周之前
        $time1 = strtotime(date('Y-m-d', strtotime('-7  days')));
        //一个月之前
        $time2 = strtotime(date('Y-m-d', strtotime('-30  days')));
        //三个月之前
        $time3 = strtotime(date('Y-m-d', strtotime('-90  days')));
        //一年之前
        $time4 = strtotime(date('Y-m-d', strtotime('-365  days')));

        switch ($opInfo) {
            case 'day':
                $time = $time1;
                break;
            case 'days':
                $time = $time2;
                break;
            case 'month':
                $time = $time3;
                break;
            case 'year':
                $time = $time4;
                break;
            default :
                $time = $time1;
        }

        $data = $this->getData($time);
        echo json_encode($data);
        exit;
    }

    public function getData($time) {
        $data = array();
        $userMod = &m('user');
        //会员总数
        $sql1 = 'SELECT  COUNT(id)  AS total  FROM  bs_user';
        $res1 = $userMod->querySql($sql1);
        $data['total'] = $res1[0]['total'];
        //分销人员数
        $sql2 = 'SELECT  COUNT(id)  AS total_fxuser  FROM  bs_user  where  is_fx = 1';
        $res2 = $userMod->querySql($sql2);
        $data['total_fxuser'] = $res2[0]['total_fxuser'];
        //新增人员
        $sql3 = 'SELECT  COUNT(id)  AS add_user  FROM  bs_user  where  add_time > ' . $time;
        $res3 = $userMod->querySql($sql3);
        $data['add_user'] = $res3[0]['add_user'];
        //新增分销人员
        $sql4 = 'SELECT  COUNT(id)  AS add_fxuser  FROM  bs_user  where   is_fx = 1  and  add_time > ' . $time;
        $res4 = $userMod->querySql($sql4);
        $data['add_fxuser'] = $res4[0]['add_fxuser'];

        return $data;
    }

    /**
     * 获取启用的站点
     * @author wanyan
     * @date 2017-09-07
     */
    public function getUseStore() {
        $storeMod = &m('store');
//        $where = '  where  1=1  and  is_open =1';
//        $sql = 'SELECT  id,store_name  FROM  bs_store  ' . $where . '  order by id ';
//        $rs = $storeMod->querySql($sql);
        $sql = 'SELECT  c.id,l.store_name  FROM  ' . DB_PREFIX . 'store  as c
                 left join  ' . DB_PREFIX . 'store_lang as l on  c.id = l.store_id
                 WHERE c.is_open = 1 and l.distinguish = 0 and  l.lang_id =' . $this->lang_id . '  order by c.id';
        $rs = $storeMod->querySql($sql);
        return $rs;
    }

}
