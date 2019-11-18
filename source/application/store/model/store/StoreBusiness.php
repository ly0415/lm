<?php

namespace app\store\model\store;

use app\common\model\StoreBusiness as StoreBusinessModel;

/**
 * 业务类型模型
 * Class StoreBusiness
 * @package app\store\model
 */
class StoreBusiness extends StoreBusinessModel
{
    /**
     * 构造方法
     */
    public function initialize()
    {
        parent::initialize();
    }


    /**
     * 获取店铺的业务类型id
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-24
     * Time: 11:13
     */
    public static function getStoreBusinessId($storeId = STORE_ID){
        return self::where('store_id','=',$storeId)
            ->value('buss_id');
    }

}