<?php

namespace app\api\controller;

use app\api\model\TableNumber   as TableNumberModel;
use app\api\model\Cart as CartModel;

/**
 * 桌号
 * @author  liy
 * @date    2019-12-09
 */
class TableNumber extends Controller{

    /**
     * 获取桌号
     * @author  liy
     * @date    2019-12-09
     */
    public function getStoreTableNumber($store_id='')
    {
        $model = new TableNumberModel;
        $tablenumber = $model->getStoreTableNumber($store_id);
        return $this->renderSuccess( ['tablenumber'=>$tablenumber]);

    }





}