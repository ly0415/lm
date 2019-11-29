<?php

namespace app\store\model\store;

use think\Session;
use app\common\model\store\User as StoreUserModel;
use app\store\model\Store;

/**
 * 商家用户模型
 * Class StoreUser
 * @package app\store\model
 */
class User extends StoreUserModel
{
    /**
     * 商家用户登录
     * @param $data
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login($data)
    {
        // 验证用户名密码是否正确
        if (!$user = $this->getLoginUser($data['user_name'], $data['password'])) {
            $this->error = '登录失败, 用户名或密码错误';
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
            'password'  => md5($password),
            'mark'      => 1
        ])->find();
    }

    /**
     * 获取用户列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($where=[])
    {
        return $this->alias('u')
            ->field('u.*,s.id as sid,s.store_type')
            ->with(['store','roles','role'])
            ->join('store s','u.store_id = s.id','left')
            ->where('s.store_type','=',1)
            ->where('u.mark', '=', 1)
            ->where('store_id','=',Store::getAdminStoreId())
            ->where($where)
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 获取用户列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getShopList($query=[])
    {
        $where = [];
        if(isset($query['store_id']) && $query['store_id'] > 0){
            $where['u.store_id']    = $query['store_id'];
        }
        if(isset($query['user_name']) && !empty($query['user_name'])){
            $where['u.user_name']   = ['like','%'.$query['user_name'].'%'];
        }
        if(!IS_ADMIN){
            $where['u.store_id']    = STORE_ID;
        }
        return $this->alias('u')
            ->field('u.*,s.id as sid,s.store_type')
            ->with(['store','roles','role'])
            ->join('store s','u.store_id = s.id','left')
            ->where('u.store_id', 'in', STORE_IDS)
            ->where('u.mark', '=', 1)
            ->where($where)
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }
    /**
     * 获取所属店铺
     */
    public function store()
    {
        return $this->belongsTo('Store','store_id','id');
    }

    /**
     * 获取所属角色
     */
    public function roles(){
        return $this->hasMany('UserRole','store_user_id','id');
    }
    /**
     * 新增记录
     * @param $data
     * @return bool|false|int
     * @throws \think\exception\DbException
     */
    public function add($data)
    {
        if (self::checkExist($data['user_name'])) {
            $this->error = '用户名已存在';
            return false;
        }
        if ($data['password'] !== $data['password_confirm']) {
            $this->error = '确认密码不正确';
            return false;
        }
        if(!isset($data['store_id'])){
            $data['store_id'] = STORE_ID;
        }
        if (empty($data['role_id'])) {
            $this->error = '请选择所属角色';
            return false;
        }
        $this->startTrans();
        try {
            // 新增管理员记录
            $data['password'] = yoshop_hash($data['password']);
            $data['is_super'] = 0;
            $this->allowField(true)->save($data);
            // 新增角色关系记录
            (new UserRole)->add($this['id'], $data['role_id']);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 更新记录
     * @param array $data
     * @return bool
     * @throws \think\exception\DbException
     */
    public function edit($data)
    {
        if ($this['user_name'] !== $data['user_name']
            && !!self::detail(['user_name' => $data['user_name']])) {
            $this->error = '用户名已存在';
            return false;
        }
        if (!empty($data['password']) && ($data['password'] !== $data['password_confirm'])) {
            $this->error = '确认密码不正确';
            return false;
        }
        if (empty($data['role_id'])) {
            $this->error = '请选择所属角色';
            return false;
        }
        if (!empty($data['password'])) {
            $data['password'] = yoshop_hash($data['password']);
        } else {
            unset($data['password']);
        }

        $this->startTrans();
        try {
            // 更新管理员记录
            $this->allowField(true)->save($data);
            // 更新角色关系记录
            (new UserRole)->edit($this['id'], $data['role_id']);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 软删除
     * @return false|int
     */
    public function setDelete()
    {
        if ($this['is_super']) {
            $this->error = '超级管理员不允许删除';
            return false;
        }
        // 删除对应的角色关系
        UserRole::deleteAll(['store_user_id' => $this['id']]);
        return $this->save(['mark' => 0]);
    }

    /**
     * 更新当前管理员信息
     * @param $data
     * @return bool
     */
    public function renew($data)
    {
        if ($data['password'] !== $data['password_confirm']) {
            $this->error = '确认密码不正确';
            return false;
        }
        // 更新管理员信息
        if ($this->save([
                'user_name' => $data['user_name'],
                'password' => yoshop_hash($data['password']),
            ]) === false) {
            return false;
        }
        // 更新session
        Session::set('yoshop_store.user', [
            'store_user_id' => $this['store_user_id'],
            'user_name' => $data['user_name'],
        ]);
        return true;
    }

}
