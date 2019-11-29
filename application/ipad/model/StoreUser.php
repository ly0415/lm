<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-04
 * Time: 上午 11:48
 */

namespace app\ipad\model;
use app\common\model\BaseModel;
use app\ipad\validate\Login;
use think\Cookie;
use think\Session;

class StoreUser extends BaseModel
{
    /**
     * 用户登录
     * @param $data
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login($data)
    {
        $validate = new Login();
        if(!$validate->check($data)){
            $this->error = $validate->getError();
            return false;
        }
        // 验证用户名密码是否正确
        if (!$user = $this->getLoginUser($data['user_name'], $data['password'])){
            $this->error = '账号或密码错误';
            return false;
        }
        // 保存登录状态
        $this->loginState($user);
        return true;
    }

    /**
     * 获取登录用户信息
     * @param $user_name
     * @param $password
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getLoginUser($user_name, $password)
    {
        return self::useGlobalScope(false)->where([
            'user_name' => $user_name,
            'password' => yoshop_hash($password),
            'is_admin'  =>0,
            'is_delete' => 0
        ])->find();
    }

    /**
     * 保存登录状态
     * @param $user
     * @throws \think\Exception
     */
    public function loginState($user)
    {
        // 保存登录状态
        Session::set('yoshop_ipad', [
            'user' => [
                'store_user_id' => $user['store_user_id'],
                'user_name' => $user['user_name'],
                'store_id'  => $user['store_id'],
            ],
            'is_login' => true,
        ]);
        Cookie::set('yoshop_ipad',[
           'user'   => [
               'token'  => sha1(md5('yoshop_ipad' . $user['store_user_id'] . $user['password']))
           ]
        ],3600 * 24 * 30 * 12);
    }
}