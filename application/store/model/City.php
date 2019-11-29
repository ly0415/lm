<?php

namespace app\store\model;

use app\common\model\City as CityModel;

/**
 * 商品分类模型
 * Class City
 * @package app\store\model
 */
class City extends CityModel
{

    /**
     * 获取省份
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-23
     * Time: 13:50
     */
    public static function getProvince($parent_id = 1){
        return self::all(['parent_id'=>$parent_id]);
    }

}
