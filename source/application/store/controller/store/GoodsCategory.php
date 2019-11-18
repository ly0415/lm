<?php

namespace app\store\controller\store;

use app\store\controller\Controller;
use app\store\model\GoodsCategory as GoodsCategoryModel;

/**
 * 商品分类
 * Class GoodsCategory
 * @package app\store\controller\goods
 */
class GoodsCategory extends Controller
{
    /**
     * 商品分类列表
     * @return mixed
     */
    public function index()
    {
        $model = new GoodsCategoryModel;
        $list = $model->getCacheTree();
//        dump($list);die;
        return $this->fetch('index', compact('list'));
    }

    /**
     * 删除商品分类
     * Created by PhpStorm.
     * Author: fup
     * @param $id
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function delete($id)
    {
        $model = GoodsCategoryModel::get($id);
        if (!$model->remove($id)) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 添加商品分类
     * Created by PhpStorm.
     * Author: fup
     * @return array|mixed
     */
    public function add()
    {
        $model = new GoodsCategoryModel;
        if (!$this->request->isAjax()) {
            // 获取所有分类
            $list = $model->getCacheTree();
//            dump($list);die;
            return $this->fetch('add', compact('list'));
        }
        // 新增记录
        if ($model->add($this->postData('goods_category'))) {
            return $this->renderSuccess('添加成功', url('store.goods_category/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑商品分类
     * Created by PhpStorm.
     * Author: fup
     * @param $id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function edit($id)
    {
        // 模板详情
        $model = GoodsCategoryModel::get($id);
//        dump($model->toArray());die;
        if (!$this->request->isAjax()) {
            // 获取所有地区
            $list = $model->getCacheTree();
            return $this->fetch('edit', compact('model', 'list'));
        }
        // 更新记录
        if ($model->edit($this->postData('goods_category'))) {
            return $this->renderSuccess('更新成功', url('store.goods_category/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 分类三级联动
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-14
     * Time: 16:59
     */
    public function get_category($parent_id = 0){
        $data = GoodsCategoryModel::getGoodsCategoryByPid($parent_id);
        return $this->renderSuccess('','',$data);
    }

}
