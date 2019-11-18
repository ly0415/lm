<?php

namespace app\store\controller\store;

use app\store\controller\Controller;
use app\store\model\Business as BusinessModel;
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
            return $this->fetch('add', compact('list','business'));
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
            return $this->fetch('edit', compact('model'));
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
        if (!$model->setDelete()) {
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
        if($roomType){
            $this->renderSuccess('SUCCESS','',$roomType);
        }
        return $this->renderError('','',$roomType);
    }
}
