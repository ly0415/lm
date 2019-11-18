<?php

namespace app\store\controller;

use app\store\model\store\StoreUser as StoreUserModel;
use think\Session;

/**
 * 商户认证
 * @author  fup
 * @date    2019-05-20
 */
class Passport extends Controller
{
    /**
     * 商户后台登录
     * @author  fup
     * @date    2019-05-20
     */
    public function login()
    {
        if ($this->request->isAjax()) {
            $model = new StoreUserModel;
            if ($model->login($this->postData('User'))) {
                return $this->renderSuccess('登录成功', url('index/index'));
            }
            return $this->renderError($model->getError() ?: '登录失败');
        }
        $this->view->engine->layout(false);
        return $this->fetch('login', [
            // 系统版本号
            'version' => get_version()
        ]);
    }

    /**
     * 退出登录
     * @author  fup
     * @date    2019-05-20
     */
    public function logout()
    {
        Session::clear('yoshop_store');
        $this->redirect('passport/login');
    }

}
