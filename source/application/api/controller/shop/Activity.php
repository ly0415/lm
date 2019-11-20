<?php

namespace app\api\controller\shop;

use app\api\controller\Controller;
use app\api\model\StoreActivity as StoreActivityModel;
use app\api\model\StoreActivity;
use app\api\model\StoreActivityUser;

/**
 * 店铺活动
 * Class Activity
 * @package app\store\controller
 */
class Activity extends Controller
{

    /**
     * 店铺活动列表
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-11
     * Time: 15:13
     */
    public function lists($page = 1,$store_id=''){
        $model = new StoreActivityModel();
        $list = $model->getList($page,$store_id);
        $banner = ['web/uploads/big/120/Store/20191012/lADPDgQ9rGUPyFnNASzNArI_690_300.jpg','web/uploads/big/120/Store/20191012/lADPDgQ9rGUQdn3NASzNArI_690_300.jpg','web/uploads/big/120/Store/20191012/lADPDgQ9rGUVKxzNASzNArI_690_300.jpg'];
//        dump($list->toArray());die;
        return $this->renderSuccess(['banner' =>$banner ,'page'=>$page, 'data' => $list]);
    }

    /**
     * 活动详情
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-11
     * Time: 16:36
     */
    public function detail($activity_id = null,$user_id = null){
        if(!$data = StoreActivityModel::detail($activity_id)){
            return $this->renderError('活动不存在');
        }
        $data['is_apply'] = 2;
        if(in_array($user_id,array_column($data->toArray()['store_activity_user'],'user_id'))){
            $data['is_apply'] = 1;
        }
        return $this->renderSuccess($data);
    }

    /**
     * 活动报名
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-12
     * Time: 09:09
     */
    public function apply($activity_id = 0,$user_id = 0){
        $model = new StoreActivityUser();
        $activity = new StoreActivity();
        if(!$activity_id || !$user_id){
            return $this->renderError('缺少必要参数');
        }
        if(!$activity->checkActivityStatus($activity_id)){
            return $this->renderError($activity->getError() ? :'报名失败');
        }
        if($data = $model->detail($activity_id,$user_id)){
            return $this->renderError('您已报名',$data);
        }
        if($model->add($activity_id,$user_id)){
            return $this->renderSuccess($model,'报名成功');
        }
        return $this->renderError($model->getError() ? : '报名失败');

    }

}
