<?php

namespace app\common\model;

/**
 * 店铺积分模型
 * Class StorePointSite
 * @package app\common\model
 */
class StorePointSite extends BaseModel
{
    protected $name = 'store_point_site';


    /**
     * 详情
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-11-05
     * Time: 14:11
     */
    public static function detail($id){
        $filter['mark'] = 1;
        if (is_array($id)) {
            $filter = array_merge($filter, $id);
        } else {
            $filter['id'] = (int)$id;
        }
        return self::get($filter);
    }
}
