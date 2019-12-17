<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-18
 * Time: 下午 5:58
 */

namespace app\store\controller\data;

use app\store\controller\Controller;
use app\store\model\Goods as GoodsModel;
use app\store\model\StoreGoods as StoreGoodsModel;
use app\store\model\GoodsCategory as GoodsCategoryModel;
class Goods extends  Controller
{
    /* @var \app\store\model\Goods $model */
    private $model;

    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function _initialize(){
        parent::_initialize();
        $this->model = IS_ADMIN ? new GoodsModel : new StoreGoodsModel;
        $this->view->engine->layout(false);
    }

    /**
     * 商品列表
     * @param null $status
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function lists($category_id = null, $business_id = 0, $goods_name = '', $goods_sn = '')
    {
        // 商品分类
        $category = GoodsCategoryModel::getCacheTree();
        // 商品列表
        $list = $this->model->getList(true, $business_id, $category_id, $goods_name,'', $goods_sn);
        return $this->fetch('list', compact('list', 'category'));
    }

    /**
     * 秒杀商品选择列表
     * @param null $status
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function spike_activity_lists($status = null)
    {
        $goods_ids = explode(',',input('goods_ids'));
        $list = $this->model->getList($status);
        $view       = new \think\View();
        $view->engine->layout(false);
        $spec    = $view->fetch('layouts/spec');
        $this->assign('spec', $spec);
        return $this->fetch('spike_activity_lists', compact('list','goods_ids','spec'));
    }
}