<?php

namespace app\store\controller\store;

use app\store\controller\Controller;
use app\store\model\RotationChart as RotationChartModel;

/**
 * 来源
 * Class GoodsCategory
 * @package app\store\controller\source
 */
class RotationChart extends Controller
{
    /**
     * 轮播列表
     * @author ly
     * @date 2019-10-22
     */
    public function index($id='')
    {
        $model = new RotationChartModel;
        $list  = $model->getList($id);
//        print_r($list->toArray());die;
        $smallimg ='/'.BIG_IMG;
        return $this->fetch('index', compact('list','smallimg'));
    }

    /**
     * 轮播编辑
     * @author ly
     * @date 2019-10-22
     */
    public function edit($id)
    {
        // 模板详情
        $model = new RotationChartModel;
        $roionList=$model->getRotionChart($id);
        $smallimg='/'.BIG_IMG;
        if (!$this->request->isAjax()) {
            return $this->fetch('edit',compact('roionList','smallimg'));
    }
        // 更新记录
        if ($model->edit($id,$this->postData('rotionc'))) {
            return $this->renderSuccess('更新成功', url('store.rotation_chart/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

}
