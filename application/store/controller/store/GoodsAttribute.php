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
use app\store\model\GoodsAttribute as GoodsAttributeModel;
use app\store\model\GoodsSpec as GoodsSpecModel;
use app\store\model\GoodsCategory as GoodsCategoryModel;

class GoodsAttribute extends Controller
{
    /**
     * 商品属性列表
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-12
     * Time: 17:29
     */
    public function index($model_id = null,$model_id1='',$type=''){
        $model = new GoodsAttributeModel;
        $category = (new GoodsModelModel)->getListAll();
        $list = $model->getList($model_id,$model_id1,$type);
        if($type == 1){
            $models = $model_id1;
        }else{
            $models = $model_id;
        }
        return $this->fetch('index', compact('list','category','models'));
    }

    /**
     * 添加商品规格
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-13
     * Time: 10:08
     */
    public function add(){
        $model = new GoodsAttributeModel();
        if(!$this->request->isAjax()){
            // 商品模型
            $category = (new GoodsModelModel)->getListAll();
            return $this->fetch('add',compact('category'));
        }
        if($model->add($this->postData('goods_spec'))){
            return $this->renderSuccess('添加成功',url('store.goods_attribute/index'));
        }
        return $this->renderError($model->getError() ? : '添加失败');
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
//        $model = GoodsSpecModel::detail($id);
        $model = new GoodsAttributeModel;
        $list = $model->get($id);
        if (!$this->request->isAjax()) {
            // 商品模型
            $category = (new GoodsModelModel)->getListAll();
            return $this->fetch('edit', compact('list','category'));
        }
        // 更新记录
        if ($list->edit($this->postData('goods_spec'))) {
            return $this->renderSuccess('更新成功', url('store.goods_attribute/index'));
        }
        return $this->renderError($list->getError() ?: '更新失败');
    }


    /**
     * 删除规格
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-13
     * Time: 16:25
     */
    public function delete($attr_id=[])
    {
        $model = new GoodsAttributeModel;
        if (!$model->remove($attr_id)) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }
}