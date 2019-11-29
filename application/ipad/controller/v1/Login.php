<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-04
 * Time: 上午 10:05
 */

namespace app\ipad\controller\v1;


use app\ipad\controller\BaseController;
use app\ipad\model\StoreUser;

class Login extends BaseController
{
    public function userLogin(){
        if($this->request->isAjax()){
            $model = new StoreUser();
            if ($model->login($this->postData('user'))) {
                return $this->renderSuccess('登录成功', url('v1.index/index'));
            }
            return $this->renderError($model->getError() ?: '登录失败');
        }
        return view();
    }
}