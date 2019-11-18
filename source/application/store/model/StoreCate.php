<?php

namespace app\store\model;

use app\common\model\StoreCate as StoreCateModel;

/**
 * 商城模型
 * Class Store
 * @package app\store\model
 */
class StoreCate extends StoreCateModel
{
    //    'store_type' => array(
    //        '1' => '总代理',
    //        '2' => '经销商',
    //        '3' => '门店',
    //        '4' => 'ODM'
    //    ),

    public static $storeType = [
        1 => '总代理',
        2 => '经销商',
        3 => '门店',
        4 => 'ODM'
    ];

    /**
     * 构造方法
     */
    public function initialize()
    {
        parent::initialize();
    }

}