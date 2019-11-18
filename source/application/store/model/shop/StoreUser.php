<?php

namespace app\store\model\shop;

use Think\Db;
use app\store\model\Store               as StoreModel;
use app\store\model\store\UserRole      as UserRoleModel;
use app\common\model\store\StoreUser    as StoreUserModel;
use app\store\model\User                as UserModel;

/**
 * 店铺店员模型
 * @author  fup
 * @date    2019-05-20
 */
class StoreUser extends StoreUserModel
{
    /**
     * 验证用户名是否重复
     * @author  fup
     * @date    2019-05-20
     */
    public static function checkExist($user_name)
    {
        return !!static::useGlobalScope(false)
            ->where('user_name', '=', $user_name)
            ->value('id');
    }

    /**
     * 获取用户列表
     * @author  fup
     * @date    2019-05-20
     */
    public function getShopList($query=[],$store_cate_id = STORE_CATE)
    {
        $where = [];
        if(isset($query['store_id']) && $query['store_id'] > 0){
            $where['u.store_id'] = $query['store_id'];
        }
        if(isset($query['user_name']) && !empty($query['user_name'])){
            $where['u.user_name'] = ['like','%'.$query['user_name'].'%'];
        }
        if(BUSINESS_ID){
            $where['s.id'] = ['in', STORE_IDS];
        }
        if(!IS_ADMIN){
            $where['u.store_id'] = STORE_ID;
        }
        return $this->alias('u')
            ->field('u.*,s.id as sid,s.store_name,s.store_type')
            ->join('store s','u.store_id = s.id','left')
            ->where('u.store_id','neq',StoreModel::getAdminStoreId())
            ->where('store_cate_id','=',$store_cate_id)
            ->where('u.mark', '=', 1)
            ->where($where)
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, ['query' => \request()->request()])
            ->each(function ($value){
                return $this->toSwitch($value);
            });
    }

    /**
     * 数据转换
     * @author  luffy
     * @date    2019-08-27
     */
    public function toSwitch($value){
        //根据店铺店员获取所属角色
        $UserRoleModel = new UserRoleModel();
        $value['format_role_name']   = $UserRoleModel->getRoleName($value['id']);
        return $value;
    }

    /**
     * 新增记录
     * @param $data
     * @return bool|false|int
     * @throws \think\exception\DbException
     */
    public function add($data)
    {
        if(self::checkExist($data['user_name'])) {
            $this->error = '用户名已存在';
            return false;
        }
        if($data['password'] !== $data['password_confirm']) {
            $this->error = '确认密码不正确';
            return false;
        }
        //店员设置情况
        if(!isset($data['store_id']) ){
            $data['store_id']       = STORE_ID;
        }
        if(BUSINESS_ID){
            $data['business_id']    = BUSINESS_ID;
        }
        if(empty($data['role_id'])) {
            $this->error = '请选择所属角色';
            return false;
        }
        $user_info = $data['user_phone'] ? (new UserModel)->where(['phone'=>$data['user_phone']])->find() : '';
        if($data['user_phone'] && empty($user_info)) {
             $this->error = '请输入正确的会员手机号';
            return false;
        }
        $this->startTrans();
        try {
            // 新增管理员记录
            if($user_info){
                $data['user_id']    = $user_info['id'];
            }
            $data['password'] = yoshop_hash($data['password']);
            $data['is_super'] = 0;
            $this->allowField(true)->save($data);
            // 新增角色关系记录
            (new UserRoleModel)->add($this['id'], $data['role_id']);
            //新增用户业务类型
            if(isset($data['business_id']) && $data['business_id']) {
                Db::name('store_user_business')->insert(['store_user_id' => $this['id'], 'business_id' => $data['business_id']]);
            }
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
            && !!self::get(['user_name' => $data['user_name']])) {
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
        $user_info = $data['user_phone'] ? (new UserModel)->where(['phone'=>$data['user_phone']])->find() : '';
        if($data['user_phone'] && empty($user_info)) {
            $this->error = '请输入正确的会员手机号';
            return false;
        }
        $this->startTrans();
        try {
            // 更新管理员记录
            if($user_info){
                $data['user_id']    = $user_info['id'];
            }
            $this->allowField(true)->save($data);
            // 更新角色关系记录
            (new UserRoleModel)->edit($this['id'], $data['role_id']);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 获取店员信息
     * @author  luffy
     * @date    2019-08-29
     */
    public function getStoreUserInfo($store_user_id){
        $info               = self::get($store_user_id);
        //关联业务类型
        $business_id        = Db::name('store_user_business')->where(['store_user_id'=>$store_user_id])->value('business_id');
        $info['business_id']= $business_id ? $business_id : 0;
        return $info;
    }
}
