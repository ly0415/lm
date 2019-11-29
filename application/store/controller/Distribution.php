<?php

namespace app\store\controller;

use app\store\model\Distribution as DistributionModel;
use app\common\model\Store as StoreModel;

/**
 * 分销管理
 * @author  luffy
 * @date    2019-08-15
 */
class Distribution extends Controller
{
    /**
     * 分销人员列表
     * @author  luffy
     * @date    2019-08-15
     */
    public function index(){
        $DistributionModel  = new DistributionModel;
        $distributionList   = $DistributionModel->getList();
        if(IS_ADMIN){
            //门店列表
            $StoreModel = new StoreModel;
            $storeList  = $StoreModel::getStoreList(TRUE);
        }
        return $this->fetch('index', compact('distributionList','storeList'));
    }

    /**
     * 编辑分销人员
     * @author  luffy
     * @date    2019-09-24
     */
    public function edit($id){
        $model = DistributionModel::get($id);
        if (!$this->request->isAjax()) {
            if(IS_ADMIN){
                //门店列表
                $StoreModel = new StoreModel;
                $storeList  = $StoreModel::getStoreList(TRUE);
            }
            return $this->fetch('edit', compact('model', 'storeList'));
        }
        // 更新记录
        if ($model->edit($this->postData('fx'))) {
            return $this->renderSuccess('更新成功', url('distribution/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 分销人员列表
     * @author  luffy
     * @date    2019-09-18
     */
    public function show($id, $is_refund = 0, $is_on = '', $order_sn = '', $start_time = '', $end_time = ''){
        //获取分销人员所属会员列表
        $DistributionModel  = new DistributionModel;
        $list    = $DistributionModel->getFxOrderList($id, $is_refund, $is_on, $order_sn, $start_time, $end_time);
        return $this->fetch('show', compact('list'));
    }

    /**
     * 所属会员列表
     * @author  luffy
     * @date    2019-08-20
     */
    public function own_user($id, $username = '', $phone = ''){
        //获取分销人员所属会员列表
        $DistributionModel  = new DistributionModel;
        $userList           = $DistributionModel->getOwnUserList($id, $username, $phone);
        $list               = $userList->ownUser;
        return $this->fetch('own_user', compact('userList','list', 'distributionList'));
    }

    /**
     * 所属会员转移
     * @author  luffy
     * @date    2019-08-21
     */
    public function exchange($fx_code, $old_fx_code, $user_id = 0){
        if(empty($fx_code) || empty($old_fx_code)){
            return $this->renderError('参数错误！');
        }
        //会员转移校验
        $DistributionModel  = new DistributionModel;
        $result             = $DistributionModel->setOwnUser($fx_code, $old_fx_code, $user_id);
        if(isset($result->errorMsg) && !empty($result->errorMsg)){
            return $this->renderError($result->errorMsg);
        }elseif($result === true){
            return $this->renderSuccess('设置成功！');
        }else{
            return $this->renderError('设置失败！');
        }
    }
}
