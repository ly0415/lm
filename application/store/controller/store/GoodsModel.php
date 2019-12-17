<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-24
 * Time: 下午 2:14
 */

namespace app\store\controller\store;

use app\store\controller\Controller;
use app\store\model\GoodsModel as GoodsModelModel;
use app\store\model\GoodsModelAttr;
use app\store\model\GoodsCategory as GoodsCategoryModel;
use app\store\model\GoodsAttribute as GoodsAttributeModel;

class GoodsModel extends Controller
{
    /**
     * 商品模型列表
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-12
     * Time: 17:29
     */
    public function index($name=''){
        $model = new GoodsModelModel;
        $list = $model->getList($name);
        return $this->fetch('index', compact('list'));
    }

    /**
     * 添加商品模型
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-13
     * Time: 10:08
     */
    public function add(){
        $model = new GoodsModelModel();
        if(!$this->request->isAjax()){
            // 商品分类
            $category = GoodsCategoryModel::getCacheTree();
            return $this->fetch('add',compact('category'));
        }
        if($model->add($this->postData('goods_model'))){
            return $this->renderSuccess('添加成功',url('store.goods_model/index'));
        }
        return $this->renderError('添加失败');
    }

    /**
     * 编辑模型
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-13
     * Time: 10:07
     */
    public function edit($id)
    {
        // 模板详情
        $model = GoodsModelModel::detail($id);
        if (!$this->request->isAjax()) {
            // 获取业务名称
            $category = GoodsCategoryModel::getCacheTree();

            return $this->fetch('edit', compact('model','category'));
        }
        // 更新记录
        if ($model->attrEdit($this->postData('goods_model'))) {
            return $this->renderSuccess('更新成功', url('store.goods_model/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 设置商品模型规格
     * @return mixed
     */
    public function setSpec($id){
        $model = GoodsModelModel::detail($id);
        if(!$this->request->isAjax()){
            return $this->fetch('set_spec',compact('model'));
        }
        if($model->add($this->postData('goods_model_attr'))){
            return $this->renderSuccess('添加成功',url('goods.goods_model/index'));
        }
        return $this->renderError('添加失败');
    }

    /**
     * 设置商品模型规格
     * @return mixed
     */
    public function addSpec($spec_name,$spec_value){
        // 判断规格组是否存在
        if (!$specId = (new \app\store\model\Spec())->getSpecIdByName($spec_name)) {
            // 新增规格组and规则值
            if ($spec = (new \app\store\model\Spec())->add($spec_name,$spec_value))
                return $this->renderSuccess('', '', [
                    'spec_id' => (int)$spec['spec_id'],
                    'spec_value_id' => (int)$this->SpecValueModel['spec_value_id'],
                ]);
            return $this->renderError();
        }
        // 判断规格值是否存在
        if ($specValueId = $this->SpecValueModel->getSpecValueIdByName($specId, $spec_value)) {
            return $this->renderSuccess('', '', [
                'spec_id' => (int)$specId,
                'spec_value_id' => (int)$specValueId,
            ]);
        }
        // 添加规则值
        if ($this->SpecValueModel->add($specId, $spec_value))
            return $this->renderSuccess('', '', [
                'spec_id' => (int)$specId,
                'spec_value_id' => (int)$this->SpecValueModel['spec_value_id'],
            ]);
        return $this->renderError();
    }

    /**
     * 设置商品模型属性
     * @return mixed
     */
    public function setAttribute($id){
        $model = GoodsModelModel::detail($id);
//        dump($model);die;
        if(!$this->request->isAjax()){
            return $this->fetch('set_attribute',compact('model'));
        }
        if((new GoodsModelAttr())->add($this->postData('goods_model_attr'))){
            return $this->renderSuccess('添加成功',url('goods.goods_model/index'));
        }
        return $this->renderError('添加失败');
    }

    /**
     * 获取商品分类
     * @return mixed
     */
    public function getJsonCate($id=0){
        $cate = GoodsCategory::getCate($id);
        return $this->renderSuccess('SUCCESS','',$cate);
    }

    /**
 * 删除模型
 * $param $id int
 * @return mixed
 */
    public function delete($id)
    {
        $model = GoodsModelModel::get($id);
        if (!$model->remove()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }



}