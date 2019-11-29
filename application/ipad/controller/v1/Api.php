<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-04
 * Time: 下午 2:41
 */

namespace app\ipad\controller\v1;


use app\ipad\controller\BaseController;
use think\Cookie;
use think\Request;
use app\ipad\model\StoreUser;
use think\Session;

class Api extends BaseController
{
    protected $token;
    protected $user;

    /**
     * desc 初始化验证登录状态
     * author fp
     * createDay: 2019/06/04
     * createTime: 14:54
     */
    protected function _initialize(){
        parent::_initialize();
        Request::instance()->filter('trim,strip_tags,htmlspecialchars');    //移除HTML标签
        $this->token = Cookie::get('yoshop_ipad')['user']['token'];
        $this->user = Session::get('yoshop_ipad');
        $users = StoreUser::get($this->user['user']['store_user_id']);
//        dump($user->toArray());die;
        if(!$users || !$this->user['is_login'] || sha1(md5('yoshop_ipad' . $users->store_user_id . $users->password)) != $this->token){
            $this->redirect('v1.login/userLogin');
            return false;
//            return $this->renderError('登录状态失效',url('userLogin'));
        }
    }
}