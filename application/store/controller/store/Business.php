<?php

namespace app\store\controller\store;

use app\store\controller\Controller;
use app\store\model\Business as BusinessModel;
use app\store\model\GoodsCategory;
use app\store\model\RoomCategory;

/**
 * 业务类型控制器
 * @author  fup
 * @date    2019-08-23
 */
class Business extends Controller
{
    /**
     * 业务类型列表
     * @author  fup
     * @date    2019-08-23
     */
    public function index()
    {
        $model = new BusinessModel;
        $list = $model->getCacheTree();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 添加业务类型
     * @author  fup
     * @date    2019-08-23
     */
    public function add()
    {
        $model = new BusinessModel;
        if (!$this->request->isAjax()) {
            $business = $model->getListAll(['level'=>1]);
            $category = GoodsCategory::getCacheTree();
            return $this->fetch('add', compact('category','business'));
        }
        // 新增记录
        if ($model->add($this->postData('business'))) {
            $model::resetCache();
            return $this->renderSuccess('添加成功', url('store.business/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑业务类型
     * @author  fup
     * @date    2019-08-23
     */
    public function edit($id)
    {
        // 模板详情
        $model = BusinessModel::get($id);
        if (!$this->request->isAjax()) {
            $business = $model->getListAll(['level'=>1]);
            $category = GoodsCategory::getCacheTree();
            $_category = GoodsCategory::getCateByThreeId($model['cate_id']);
            return $this->fetch('edit', compact('model','business','category','_category'));
        }
        // 更新记录
        if ($model->edit($this->postData('business'))) {
            $model::resetCache();
            return $this->renderSuccess('更新成功', url('store.business/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除业务类型
     * @author  fup
     * @date    2019-08-23
     */
    public function delete($id)
    {
        $model = BusinessModel::get($id);
        if (!$model->setDelete($id)) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        $model::resetCache();
        return $this->renderSuccess('删除成功');
    }


    /**
     * 根据商品分类获取业务类型
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-15
     * Time: 11:59
     */
    public function get_room_name($category_id = 0){
        $roomType = RoomCategory::getRoomTypeNameByCategoryId($category_id);
        return $this->renderSuccess('SUCCESS','',$roomType);
    }


    /**
     * 所属商品分类
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-11-20
     * Time: 20:24
     */
    public function view($id){
        $model = BusinessModel::get($id,'category');
        $model->getCategoryList();
        return $this->fetch('view',compact('model'));
    }

    /**
     * 添加业务类型所属业务类型分类
     * @author  fup
     * @date    2019-08-23
     */
    public function _add($id)
    {
        if (!$this->request->isAjax()) {
            $model = BusinessModel::get($id);
            $category = GoodsCategory::getCacheTree();
            return $this->fetch('_add', compact('category','model'));
        }
        // 新增记录
        $model = new BusinessModel;
        if ($model->_add($this->postData('business_category'))) {
            $model::resetCache();
            return $this->renderSuccess('添加成功', url('store.business/view',['id'=>$id]));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }


    /**
     * 添加业务类型所属业务类型分类
     * @author  fup
     * @date    2019-08-23
     */
    public function _edit($id,$business_id)
    {
        if (!$this->request->isAjax()) {
            $model = RoomCategory::detail($id);
            $category = GoodsCategory::getCacheTree();
            $_category = GoodsCategory::getCateByThreeId($model['category_id']);
            return $this->fetch('_edit', compact('category','model','_category'));
        }
        // 更新记录
        $model = new BusinessModel;
        if ($model->_edit($this->postData('business_category'))) {
            $model::resetCache();
            return $this->renderSuccess('更新成功', url('store.business/view',['id'=>$business_id]));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除业务类型所属商品分类
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-11-21
     * Time: 20:16
     */
    public function _delete($id)
    {
        $model = RoomCategory::detail($id);
        if (!$model->setDelete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        BusinessModel::resetCache();
        return $this->renderSuccess('删除成功');
    }

}
