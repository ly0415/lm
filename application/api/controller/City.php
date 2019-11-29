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
     * 获取城市 省份  小程序
     * @author  liy
     * @date    2019-10-30
     */
    public function getProvince($province='')
    {
        $city = CityModel::getProvince($province);
        return $this->renderSuccess( ['city'=>$city]);

    }


    /**
     * 获取城市 省份  添加门店
     * @author  liy
     * @date    2019-10-30
     */
    public function getCityProvince($parent_id =''){
        $citymodel = new CityModel;
        $city = $citymodel->getCityProvince($parent_id);
        return $city;
    }



}