<?php

namespace app\store\controller\statistics;

use app\store\controller\Controller;
use app\store\model\BalanceRechargeCoupon           as BalanceRechargeCouponModel;

use app\store\model\store\StoreUser      as StoreUserModel;
use app\store\model\Store                as StoreModel;
/**
 *统计数据
 * Class GoodsCategory
 * @package app\store\controller\source
 */
class RechargeStatistics extends Controller
{
    /**
     *统计数据
     * @author ly
     * @date 2019-11-11
     */
    public function index($store_id='',$storeuser_id='',$endtime='')
    {
        $storeusermodel        = new StoreUserModel;
        $balanceRechargeCoupon = new BalanceRechargeCouponModel;
        if(!T_GENERAL){
            $storelist         = StoreModel::getStoreList(TRUE, BUSINESS_ID);
        }
        $store_id              = $store_id?$store_id:STORE_ID;
        $userlist              = $storeusermodel->storeUserList($store_id);
        $storeuserlist         = $storeusermodel->getRechargeList($store_id,$storeuser_id,$endtime);
        return $this->fetch('index', compact('storeuserlist','storelist','userlist'));
    }



}
