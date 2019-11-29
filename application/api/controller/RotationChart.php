<?php

namespace app\api\controller;

use app\api\model\RotationChart   as RotationChartModel;

/**
 * 轮播图控制器
 * @author  liy
 * @date    2019-10-26
 */
class RotationChart extends Controller{

    /**
     * 轮播图
     * @author  liy
     * @date    2019-10-26
     */
    public function getType($type='')
    {
        $model = new RotationChartModel;
        $list['data'] = $model->getList($type);
        return $this->renderSuccess( ['list'=>$list]);
    }



}