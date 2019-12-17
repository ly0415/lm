<?php

namespace app\store\controller\goods;

use app\store\controller\Controller;
use app\store\model\BusinessCategory as BusinessCategoryModel;
use app\store\model\Business as BusinessModel;
use app\store\model\GoodsCategory as GoodsCategoryModel;

/**
 * 商品分类
 * Class Category
 * @package app\store\controller\goods
 */
class BusinessCategory extends Controller
{
    /**
     * 商品分类列表
     * @return mixed
     */
    public function index()
    {
        $model = new BusinessCategoryModel;
        $list = $model->getList();
//        dump($list->toArray());die;
        return $this->fetch('index', compact('list'));
    }

    /**
     * 删除商品分类
     * @param $category_id
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function delete($id)
    {
        $model = BusinessCategoryModel::get($id);
        if (!$model->remove($id)) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 添加商品分类
     * @return array|mixed
     */
    public function add()
    {
        $model = new BusinessCategoryModel;
        if (!$this->request->isAjax()) {
            // 获取业务名称
            $list = (new BusinessModel)->getListAll(['level'=>1]);
            $category = GoodsCategoryModel::getCate();
            return $this->fetch('add', compact('list','category'));
        }
        // 新增记录
        if ($model->add($this->postData('business_category'))) {
            return $this->renderSuccess('添加成功', url('goods.business_category/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑商品分类
     * @param $category_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function edit($id)
    {
        // 模板详情
        $model = BusinessCategoryModel::detail($id);
        if (!$this->request->isAjax()) {
            // 获取业务名称
            $list = (new BusinessModel)->getListAll(['level'=>1]);
            $category_1 = GoodsCategoryModel::getCate();
            $category_2 = GoodsCategoryModel::getCate($model['cate_path_id'][2]);
            $category_3 = GoodsCategoryModel::getCate($model['cate_path_id'][1]);
            return $this->fetch('edit', compact('model','list','category_1','category_2','category_3'));
        }
        // 更新记录
        if ($model->edit($this->postData('business_category'))) {
            return $this->renderSuccess('更新成功', url('goods.business_category/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }


    /**
     * 查询业务类型
     * @param $cate_id int
     * @return JSON
     */
    public function getJsonCate($cate_id){
        $data =BusinessCategoryModel::getBusinessCateName($cate_id);
        return $this->renderSuccess('SUCCESS','',$data);
    }

}
