<?php

namespace app\api\model;

use app\common\model\City   as CityModel;
/**
 * 城市
 * @author  ly
 * @date    2019-10-30
 */
class City extends CityModel{

    /**
     *获取城市 省份
     * @author ly
     * @date 2019-10-22
     */
    public static function getProvince($parent_id = 1){
        return self::all(['parent_id'=>$parent_id]);
    }


}
