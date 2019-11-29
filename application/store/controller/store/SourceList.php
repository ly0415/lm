<?php

namespace app\store\controller\store;

use app\store\controller\Controller;
use app\store\model\StoreSource as StoreSourceModel;

/**
 * 来源
 * Class GoodsCategory
 * @package app\store\controller\source
 */
class SourceList extends Controller
{
    /**
     * 来源列表显示
     * @author ly
     * @date 2019-10-22
     */
    public function index($sourcename = '')
    {
        $model = new StoreSourceModel;
        $list = $model->getList($sourcename);
//        dump($list->toArray());die;
        return $this->fetch('index', compact('list'));
    }

    /**
     * 来源列表删除
     * @author ly
     * @date 2019-10-22
     */
    public function delete($id)
    {
        $model = StoreSourceModel::get($id);
        if (!$model->remove($id)) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 来源列表添加
     * @author ly
     * @date 2019-10-22
     */
    public function add()
    {
        $model = new StoreSourceModel;
        if (!$this->request->isAjax()) {
            return $this->fetch('add');
        }
        // 新增记录
        if ($model->add($this->postData('source_list'))) {
            return $this->renderSuccess('添加成功', url('store.source_list/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 来源列表编辑
     * @author ly
     * @date 2019-10-22
     */
    public function edit($id)
    {
        // 模板详情
        $model = StoreSourceModel::get($id);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit',compact('model'));
    }
        // 更新记录
        if ($model->edit($this->postData('source_list'))) {
            return $this->renderSuccess('更新成功', url('store.source_list/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

}
