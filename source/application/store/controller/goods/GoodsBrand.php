<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-24
 * Time: 下午 2:14
 */

namespace app\store\controller\goods;

use app\store\controller\Controller;
use app\store\model\GoodsBrand as GoodsBrandModel;

class GoodsBrand extends Controller
{
    /**
     * 商品模型列表
     * @return mixed
     */
    public function index($name = ''){
        $model = new GoodsBrandModel;
        $list = $model->getList($name);
//        dump($list);die;
        return $this->fetch('index', compact('list'));
    }

    /**
     * 添加商品品牌
     * @return mixed
     */
    public function add(){
        $model = new GoodsBrandModel();
        if(!$this->request->isAjax()){
            return $this->fetch('add',compact('list'));
        }
        if($model->add($this->postData('goods_brand'))){
            return $this->renderSuccess('添加成功','goods.goods_brand/index');
        }
        return $this->renderError('添加失败');
    }

    /**
     * 编辑商品品牌
     * @return mixed
     */
    public function edit($id){
        $model = GoodsBrandModel::detail($id);
//        dump($model->toArray());die;
        if(!$this->request->isAjax()){
            return $this->fetch('edit',compact('model'));
        }
        // 更新记录
        if ($model->edit($this->postData('goods_brand'))) {
            return $this->renderSuccess('更新成功', url('goods.goods_brand/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }
}