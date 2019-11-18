<?php

namespace app\store\controller\store;

use app\store\controller\Controller;
use app\store\model\User        as UserModel;

/**
 * 用户管理
 * @author  luffy
 * @date    2019-09-02
 */
class User extends Controller{

    /* @var CouponModel $model */
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
        $this->model = new UserModel;
    }

    /**
     * 优惠券列表
     * @author  luffy
     * @date    2019-10-07
     */
    public function index($username = '', $phone = ''){
        $list = $this->model->getList('','', $username, $phone);
        return $this->fetch('index', compact('list'));
    }

    /**
     * 领取记录
     * @author  luffy
     * @date    2019-10-07
     */
    public function recomend($user_id){
        $list   = $this->model->recomend($user_id);
        return $this->fetch('recomend', compact('list'));
    }

    /**
     * 用户编辑
     * @author  ly
     * @date    2019-11-1
     */
    public function editInfo($user_id=''){

        $model = new UserModel;
        $list=$model->get($user_id);
//        print_r($list->toArray());die;
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('list'));
        }
        // 更新记录
        if ($model->editInfo($user_id,$this->postData('user'))) {
            return $this->renderSuccess('更新成功', url('store.user/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

}