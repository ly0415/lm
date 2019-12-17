<?php

namespace app\api\controller;

use app\store\model\store\StoreUser      as StoreUserModel;

/**
 *
 * @author  liy
 * @date    2019-11-19
 */
class UserStatistics extends Controller{

    /**
     *统计数据
     * @author ly
     * @date 2019-11-11
     */
    public function getStoreUser($store_id='')
    {
        $model         = new StoreUserModel;
        $storeUserList = $model->storeUserList($store_id);
        return $storeUserList;
    }

}