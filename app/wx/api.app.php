<?php
/**
 * API独立接口
 * @author luffy
 * @date 2018-4-9 09:23:12
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}
class ApiApp extends BaseApp {

    /**
     * 数据封装
     * @author lvji
     * @param $status 表示返回数据状态
     * @param $message 对返回状态说明
     * @param $info 返回数据信息
     * @date 2015-03-10
     */
    public function setData($info = array(), $status = 'success', $message = 'ok') {
        $data = array(
            'status' => $status,
            'message' => $message,
            'info' => $info,
        );
        echo json_encode($data);
        exit();
    }

    /**
     * 数据封装
     * @author luffy
     * @date 2018-09-10
     */
    public function getLantion() {
        $latitude   = isset($_REQUEST['latitude']) ? trim($_REQUEST['latitude']) : '32.0572355';
        $longitude  = isset($_REQUEST['longitude']) ? trim($_REQUEST['longitude']) : '118.77807441';
        $txlalton=$latitude.",".$longitude;
        $_SESSION['latlon']=$txlalton;
        exit();


    }

    /**
     * 获取城市信息  by xt 2019.03.25
     */
    public function areas()
    {
        $systemCityMod = &m('systemCity');

        $sql = "select id,parent_id,name as value from bs_system_city where parent_id != 377";
        $city = $systemCityMod->querySql($sql);
        $data = $systemCityMod->getTree($city);

        $this->setData(array(array('data' => $data)), 1);
    }
}