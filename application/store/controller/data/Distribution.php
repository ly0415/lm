<?php

/**
 * 分销人员弹框
 * @author  luffy
 * @date    2019-09-21
 */
namespace app\store\controller\data;

use app\store\controller\Controller;
use app\store\model\Distribution        as DistributionModel;

class Distribution extends  Controller{

    /**
     * 构造方法
     * @author  luffy
     * @date    2019-09-21
     */
    public function _initialize(){
        parent::_initialize();
        $this->view->engine->layout(false);
    }

    /**
     * 分销人员列表
     * @author  luffy
     * @date    2019-09-21
     */
    public function lists(){
        //分销人员
        $DistributionModel = new DistributionModel;
        //不能指派给冻结的、删除的、非本店的、非三级分销人员
        $list = $DistributionModel->getPageList(['status'=>1,'is_check'=>2,'level'=>3, 'store_id'=>STORE_ID]);
        return $this->fetch('list', compact('list'));
    }
}