<?php

namespace app\api\controller;

use app\api\model\City   as CityModel;

/**
 * 城市控制器
 * @author  liy
 * @date    2019-10-30
 */
class City extends Controller{

    /**
     * 获取城市 省份
     * @author  liy
     * @date    2019-10-30
     */
    public function getProvince($province='')
    {
        $city = CityModel::getProvince($province);
        return $this->renderSuccess( ['city'=>$city]);

    }



}