<?php

namespace app\store\controller;

use app\store\model\Cart as CartModel;

/**
 * 购物车
 * Class Cart
 * @package app\store\controller\Cart
 */
class Cart extends Controller
{

    /**
     * 添加购物车
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-27
     * Time: 10:08
     */
    public function add()
    {
        $model = new CartModel();
        if ($this->request->isAjax()) {
            // 新增记录
            if ($model->add($this->postData('cart'))) {
                return $this->renderSuccess('添加成功', url('order/orderList',$model->id));
            }
            return $this->renderError($model->getError() ?: '添加失败');
        }

    }

}
