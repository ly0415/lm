<?php

namespace app\store\controller\goods;

use app\store\controller\Controller;
use app\store\model\GoodsCategory as GoodsCategoryModel;
use app\store\model\RoomType as RoomTypeModel;

use app\store\model\RoomCategory as RoomCategoryModel;
/**
 * 商品分类
 * Class Category
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
     * @param $category_id
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
            return $this->renderSuccess('添加成功', url('goods.goods_category/index'));
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
        $model = GoodsCategoryModel::get($id);
//        dump($model->toArray());die;
        if (!$this->request->isAjax()) {
            // 获取所有地区
            $list = $model->getCacheTree();
            return $this->fetch('edit', compact('model', 'list'));
        }
        // 更新记录
        if ($model->edit($this->postData('goods_category'))) {
            return $this->renderSuccess('更新成功', url('goods.goods_category/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 获取商品分类
     * @return mixed
     */
    public function getJsonCate($id=0){
        $cate = GoodsCategoryModel::getCate($id);
        return $this->renderSuccess('SUCCESS','',$cate);
    }

    /**
     * 根据商品分类 获得其下商品
     * @return mixed
     */
    public function getRoomCategoryGoods($roomtype='',$storeid=''){
        $model=new RoomCategoryModel;
        $cate = $model->getRoomCategoryGoods($roomtype,$storeid);
        return $this->renderSuccess('SUCCESS','',$cate);
    }
}
