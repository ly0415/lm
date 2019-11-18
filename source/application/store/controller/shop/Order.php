<?php

namespace app\store\controller\shop;

use app\store\controller\Controller;
use app\store\model\Order as OrderModel;
use app\store\model\Store;

/**
 * 交班报表
 * @author  fup
 * @date    2019-05-20
 */
class Order extends Controller{

    /**
     * 交班报表
     * @author  fup
     * @date    2019-05-20
     */
    public function orderList() {
        $OrderModel = new OrderModel;
        $list = $OrderModel->getExpList($this->request->param());
        $send = $OrderModel->delivery_type;
        //门店列表
        $StoreModel = new Store;
        $stores     = $StoreModel -> getStoreList(TRUE, BUSINESS_ID);
        return $this->fetch('orderList',compact('list','stores','send'));
    }

    /**
     * 交接班报表导出
     * author fup
     * date 2019-07-24
     */
    public function excelOut()
    {
        $orderModel = new OrderModel;
        //获取订单数据
        $orderModel->excelOut($this->request->param());
    }

}