<?php

namespace app\store\controller;

use app\store\model\User as UserModel;


/**
 * 用户管理
 * Class User
 * @package app\store\controller
 */
class User extends Controller
{
    /**
     * 用户列表
     * @param string $nickName
     * @param null $gender
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index($nickName = '', $gender = null)
    {
        $model = new UserModel;
        $list = $model->getList($nickName, $gender);
        return $this->fetch('index', compact('list'));
    }

    /**
     * 删除用户
     * @param $user_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($user_id)
    {
        // 用户详情
        $model = UserModel::detail($user_id);
        if ($model->setDelete()) {
            return $this->renderSuccess('删除成功');
        }
        return $this->renderError($model->getError() ?: '删除失败');
    }

    /**
     * 用户充值
     * @param $user_id
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function recharge($user_id)
    {
        // 用户详情
        $model = UserModel::detail($user_id);
        if ($model->recharge($this->store['user']['user_name'], $this->postData('recharge'))) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 通过手机号模糊搜索用户
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-02
     * Time: 13:50
     */
    public function search_user($phone = ''){
        $model = new UserModel();
        $user = $model->getList('','','',$phone);
        return $this->renderSuccess('SUCCESS','',$user);
    }

}
