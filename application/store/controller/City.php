<?php

namespace app\store\controller;

use app\store\model\City as CityModel;

/**
 * 业务
 * Class City
 * @package app\store\controller\City
 */
class City extends Controller
{
    /**
     * 获取省
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-23
     * Time: 13:49
     */
    public function getProvince($parent_id = 1){
        $province = CityModel::getProvince($parent_id);
        return $this->renderSuccess('success','',$province);
    }
}
