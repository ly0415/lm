<?php

namespace app\store\controller\distribution;

use app\store\model\DiscountChange as DiscountChangeModel;
use app\common\model\Store as StoreModel;
use app\store\controller\Controller;

/**
 * 优惠变更管理
 * @author  liy
 * @date    2019-10-23
 */
class DiscountChange extends Controller
{
    /**
     * 优惠变更列表
     * @author  liy
     * @date    2019-10-23
     */
    public function index($check=''){
        $DiscountChangeModel  = new DiscountChangeModel;
        $discountchangeList   = $DiscountChangeModel->getList($check);
        return $this->fetch('index', compact('discountchangeList','check'));
    }

    /**
     * 编辑状态
     * @author  liy
     * @date    2019-10-23
     */
    public function edit($id,$status){
        $model = DiscountChangeModel::detail($id);
        $fxDiscount=$model['fx_discount'];
        $fx_id=$model['fx_user_id'];
        $model->edit($status,$fx_id,$fxDiscount);
        return $this->renderSuccess('更改成功', url('distribution.discount_change/index'));
    }

    /**
     * 删除
     * @author  liy
     * @date    2019-10-23
     */
    public function delete($id)
    {
        $model = DiscountChangeModel::get($id);
        if (!$model->remove($id)) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }


}
