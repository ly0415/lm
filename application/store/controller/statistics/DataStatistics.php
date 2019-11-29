<?php

namespace app\store\controller\statistics;

use app\store\controller\Controller;
use app\store\model\StoreGoods  as StoreGoodsModel;
use app\store\model\Business    as BusinessModel;
use app\store\model\Store       as StoreModel;

/**
 *统计数据
 * Class GoodsCategory
 * @package app\store\controller\source
 */
class DataStatistics extends Controller
{
    /**
     *统计数据
     * @author ly
     * @date 2019-11-11
     */
    public function index($store_id='',$cat_id='',$starttime='',$endtime='')
    {
        $model          = new StoreGoodsModel;
        $store_category = BusinessModel::getCacheTree()[BUSINESS_ID]['child'];

        if(!T_GENERAL){
            //门店列表
            $StoreModel = new StoreModel;
            $storeList  = $StoreModel::getStoreList(TRUE, BUSINESS_ID);
        }

        $list           = $model->getgoodsList($store_id,$cat_id,$starttime,$endtime);
        return $this->fetch('index', compact('list','store_category', 'storeList'));
    }


}
