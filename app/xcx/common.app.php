<?php
/**
 * 公用接口
 * User: xt
 * Date: 2019/3/25
 * Time: 09:28
 */

class CommonApp extends BasePhApp
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取城市信息  by xt 2019.03.25
     */
    public function areas()
    {
        $systemCityMod = &m('systemCity');

        $sql = "select id,parent_id,name from bs_system_city where parent_id != 377";
        $city = $systemCityMod->querySql($sql);
        $data = $systemCityMod->getTree($city);

        $this->setData(array('data' => $data), 1);
    }

}