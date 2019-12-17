<?php

namespace app\api\controller;

use app\api\model\User as UserModel;

/**
 * 用户管理
 * Class User
 * @package app\api
 */
class User extends Controller
{

    /**
     *用户评论列表
     * @author ly
     * @date 2019-10-29
     */
    public function userInfo($userid='')
    {
        // 当前用户信息
        if(empty($userid))return array();
        $userInfo = UserModel::get($userid);
        return $this->renderSuccess( ['userInfo'=>$userInfo]);
    }

    /**
     *添加用户信息
     * @author ly
     * @date 2019-10-29
     */
    public function editUserInfo()
    {
        // 当前用户信息
        $userid    = $_REQUEST['userid'];
        $data['headimgurl'] = !empty($_REQUEST['order_pic']) ? $_REQUEST['order_pic'] : '';
        $data['username'] = $_REQUEST['username'] ? $_REQUEST['username'] : "";
        $data['phone'] = trim($_REQUEST['phone']);
        $data['email'] = $_REQUEST['email']?$_REQUEST['email']:'';
        $data['sex'] = $_REQUEST['sex'];
        $data['birth'] = $_REQUEST['birth'];
        $model = new UserModel;
        if ($model->editUserInfo($userid,$data)){
            return $this->renderSuccess('修改成功');
        }
        return $this->renderError($model->getError() ?: '修改失败');
    }
}
