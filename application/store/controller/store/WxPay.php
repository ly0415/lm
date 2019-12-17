<?php

namespace app\store\controller\store;

use app\store\controller\Controller;
use app\store\model\Store;
use app\store\model\Order as OrderModel;

/**
 * 店铺订单管理
 * @author  luffy
 * @date    2019-07-15
 */
class WxPay extends Controller
{
    /**
     * 微信收款列表
     * @author  fup
     * @date    2019-08-07
     */
    public function index($store_id=STORE_ID,$start_time=0,$end_time=0,$pay_sn=0){
        $orderMod = new OrderModel();
        $stores = Store::getStoreList(true);
        $totalMoney = $orderMod->getTotalMoney($store_id,$start_time,$end_time,$pay_sn);
        $list = $orderMod->getWxList($store_id,$start_time,$end_time,$pay_sn);

        return $this->fetch('index',compact('stores','list','totalMoney'));
    }

    /**
     * 小程序微信银收导出
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-08
     * Time: 10:31
     */

    public function export($store_id=STORE_ID,$start_time=0,$end_time=0,$pay_sn=0){
        $orderMod = new OrderModel();
        return $orderMod ->exportWxList($store_id,$start_time,$end_time,$pay_sn);
    }



}
