<?php

namespace app\store\controller\store;
use app\store\controller\Controller;
use app\store\model\City;
use app\store\model\StoreCate;
use app\store\model\store\Shop as ShopModel;
use app\store\model\Business as BusinessModel;
use app\store\model\Wxapp as WxappModel;

/**
 * 门店管理
 * Class Shop
 * @package app\store\controller\store
 */
class Shop extends Controller
{
    /**
     * 门店列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index($getpoint = 0){
        //腾讯地图坐标选取器
        if(isset($getpoint) && $getpoint == 1){
            $this->view->engine->layout(false);
            return $this->fetch('getpoint');
        }
        $model = new ShopModel;
        $list = $model->getList();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 添加门店
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function add()
    {
        $model = new ShopModel;
        if (!$this->request->isAjax()) {
            //省
            $province = City::getProvince();
            //站点类型
            $storeType = StoreCate::$storeType;
            //一级业务类型
            $roomType = BusinessModel::getFirstLevelBusiness();
            return $this->fetch('add',compact('storeType','storeCate','roomType','province'));
        }
        // 新增记录
        if ($model->add($this->postData('shop'))) {
            return $this->renderSuccess('添加成功', url('store.shop/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑门店
     * @param $shop_id
     * @return array|bool|mixed
     * @throws \think\exception\DbException
     */
    public function edit($store_id)
    {
        // 门店详情
        $ShopModel  = new ShopModel;
        $model      = $ShopModel->detail($store_id);
        if (!$this->request->isAjax()) {
            //省
            $model['format_province']   = $model['format_store_address'] ?  City::getProvince(1): '';
            //市
            $model['format_city']       = $model['format_store_address'][1] ?  City::getProvince($model['format_store_address'][0]): '';
            //区
            $model['format_region']     = $model['format_store_address'][2] ?  City::getProvince($model['format_store_address'][1]): '';
            //站点类型
            $storeType  = StoreCate::$storeType;
            //一级业务分类
            $business   = BusinessModel::getFirstLevelBusiness();
            return $this->fetch('', compact('model','storeType', 'storeCate', 'business'));
        }
        // 新增记录
        if ($ShopModel->edit($this->postData('shop'))) {
            return $this->renderSuccess('更新成功', url('store.shop/index'));
        }
        return $this->renderError($ShopModel->getError() ?: '更新失败');
    }

    /**
     * 门店开关
     * @author: luffy
     * @date  : 2019-09-09
     */
    public function on($store_id, $state){
        // 商品详情
        $ShopModel  = new ShopModel;
        if (!$ShopModel->setOpen($state, $store_id)) {
            return $this->renderError('操作失败');
        }
        $ShopModel::resetCache();
        return $this->renderSuccess('操作成功');
    }

    /**
     * 查看电子围栏
     * @author: luffy
     * @date  : 2019-10-23
     */
    public function electric_fence($store_id){
        $model = new ShopModel;
        $model = $model::get($store_id);
        if (!$this->request->isAjax()) {
            return $this->fetch('electric_fence', compact('model'));
        }
    }

    /**
     * 查看电子围栏
     * @author: luffy
     * @date  : 2019-10-23
     */
    public function edit_ef(){
        $data   = $this->postData('ef');
        $model = new ShopModel;
        $model = $model::get($data['store_id']);
        // 更新记录
        if ($model->edit_ef($data)) {
            return $this->renderSuccess('操作成功', url('store.shop/electric_fence',['store_id'=>$data['store_id']]));
        }
        $error = $model->getError() ?: '操作失败';
        return $this->renderError($error);
    }

    /**
     * 支付配置
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-22
     * Time: 11:47
     */
    public function setting($store_id = 0)
    {
        // 当前小程序信息
        if (!$this->request->isAjax()) {
            $model = WxappModel::detail($store_id);
            return $this->fetch('setting', compact('model'));
        }
        // 更新小程序设置
        $model = new WxappModel();
        if(!$detail = WxappModel::detail($store_id)){
            if($model->add($this->postData('wxapp'))){
                return $this->renderSuccess('添加成功');
            }
            return $this->renderError($model->getError() ? : '添加失败');
        }
        if ($detail->edit($this->postData('wxapp'))) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }
}