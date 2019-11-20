<?php

namespace app\store\controller\statistics;

use app\store\controller\Controller;

use app\api\controller\UserStatistics      as UserStatisticsModel;
use app\store\model\store\StoreUser      as StoreUserModel;
use app\store\model\Store                as StoreModel;
/**
 *统计数据
 * Class GoodsCategory
 * @package app\store\controller\source
 */
class UserStatistics extends Controller
{
    /**
     *统计数据
     * @author ly
     * @date 2019-11-11
     */
    public function index($store_id='',$storeuser_id='',$endtime='')
    {
        $storeusermodel = new StoreUserModel;
        $userstatisticsmodel = new UserStatisticsModel;
        if(!T_GENERAL){
            $storelist  = StoreModel::getStoreList(TRUE, BUSINESS_ID);
        }
        $store_id       = $store_id?$store_id:STORE_ID;
        $userlist       = $userstatisticsmodel->getStoreUser($store_id);
        $storeuserlist  = $storeusermodel->getstoreuserList($store_id,$storeuser_id,$endtime);
        return $this->fetch('index', compact('storeuserlist','storelist','userlist'));
    }


}
