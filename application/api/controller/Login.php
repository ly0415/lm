<?php

namespace app\api\controller;

use app\api\model\User   as UserModel;

/**
 * 登录验证
 * @author  liy
 * @date    2019-11-27
 */
class Login extends Controller{

    /**
     * 登录验证
     * @author  liy
     * @date    2019-11-27
     */
    public function logonValidate($user_id='')
    {
        if(empty($user_id)){
            return $this->renderError('参数错误！');
        }
        $user = UserModel::get($user_id);
        if(empty($user)){
            return $this->renderError('参数错误！');
        }
        return $this->renderSuccess( $user['is_use']);

    }






}