<?php

namespace app\api\controller;

use app\api\model\Cart as CartModel;

/**
 * 购物车管理
 * Class Cart
 * @package app\api\controller
 */
class Cart extends Controller
{
    private $model;

    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new CartModel();
    }

    /**
     * 购物车列表
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function lists()
    {
        return $this->renderSuccess();
    }

    /**
     * 加入购物车
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-10
     * Time: 10:39
     */
    public function add($user_id,$store_id,$shipping_store_id,$store_goods_id, $goods_num, $spec_key,$order_from = 2,$deliveryType = 1)
    {
        if(!$user_id || !$store_id || !$shipping_store_id || !$store_goods_id || !$goods_num){
            return $this->renderError('缺少必要参数');
        }

        if (!$id = $this->model->checkAdd($user_id,$store_id,$shipping_store_id,$store_goods_id, $goods_num, $spec_key,$order_from,$deliveryType)) {
            return $this->renderError($this->model->getError() ?: '加入购物车失败');
        }
        return $this->renderSuccess(['id' => $id,'store_id'=>$store_id], '加入购物车成功');
    }


    /**
     * 改变购物车数量
     * @author ly
     * @date  2019-12-10
     *
     */
    public function doChangeNum($goods_num='',$cart_id='',$store_id='',$user_id='')
    {
        $cartmodel = new CartModel;
        if (!$cartmodel->doChangeNum($goods_num,$cart_id,$store_id,$user_id)) {
            return $this->renderError($cartmodel->getError() ?: '更改失败');
        }
        return $this->renderSuccess('更改成功');
    }

    /**
     * 获得用户购物车总数
     * @author ly
     * @date  2019-12-10
     *
     */
    public function getUserCartNumber($user_id='')
    {
        $model = new CartModel;
        $number = $model->getStoreTableNumber($user_id);
        return $this->renderSuccess( ['number'=>$number]);
    }


    /**
     * 减少购物车商品数量
     * @param $goods_id
     * @param $goods_sku_id
     * @return array
     */
    public function sub($goods_id, $goods_sku_id)
    {
        $this->model->sub($goods_id, $goods_sku_id);
        return $this->renderSuccess();
    }

    /**
     * 删除购物车中指定商品
     * @param $goods_sku_id (支持字符串ID集)
     * @return array
     */
    public function delete($goods_sku_id)
    {
        $this->model->delete($goods_sku_id);
        return $this->renderSuccess();
    }

}
