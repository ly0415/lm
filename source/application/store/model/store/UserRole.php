<?php

namespace app\store\model\store;

use app\common\model\store\UserRole as UserRoleModel;

/**
 * 店铺店员角色关联模型
 * @author  luffy
 * @date    2019-08-27
 */
class UserRole extends UserRoleModel
{

    /**
     * 根据店铺店员获取所属角色
     * @author  luffy
     * @date    2019-08-27
     */
    public function getRoleName($store_user_id)
    {
        $roleArr= [];
        $roles  = $this->alias('a')->field('b.role_name')->join('store_role b','a.role_id = b.role_id')->where('a.store_user_id','=', $store_user_id)->select();
        if(!empty($roles)){
            foreach ($roles as $val){
                $roleArr = array_merge($roleArr, [$val['role_name']]);
            }
        }
        return implode(',', $roleArr);
    }

    /**
     * 获取指定管理员的所有角色id
     * @author  fup
     * @date    2019-05-20
     */
    public static function getRoleIds($store_user_id)
    {
        return (new self)->where('store_user_id', '=', $store_user_id)->column('role_id');
    }

    /**
     * 新增关系记录
     * @author  fup
     * @date    2019-05-20
     */
    public function add($store_user_id, $roleIds)
    {
        $data = [];
        foreach ($roleIds as $role_id) {
            $data[] = [
                'store_user_id' => $store_user_id,
                'role_id' => $role_id,
            ];
        }
        return $this->saveAll($data);
    }

    /**
     * 更新关系记录
     * @author  fup
     * @date    2019-05-20
     */
    public function edit($store_user_id, $newRole)
    {
        // 已分配的角色集
        $assignRoleIds = self::getRoleIds($store_user_id);

        /**
         * 找出删除的角色
         * 假如已有的角色集合是A，界面传递过得角色集合是B
         * 角色集合A当中的某个角色不在角色集合B当中，就应该删除
         * 使用 array_diff() 计算补集
         */
        if ($deleteRoleIds = array_diff($assignRoleIds, $newRole)) {
            self::deleteAll(['store_user_id' => $store_user_id, 'role_id' => ['in', $deleteRoleIds]]);
        }

        /**
         * 找出添加的角色
         * 假如已有的角色集合是A，界面传递过得角色集合是B
         * 角色集合B当中的某个角色不在角色集合A当中，就应该添加
         * 使用 array_diff() 计算补集
         */
        $newRoleIds = array_diff($newRole, $assignRoleIds);
        $data = [];
        foreach ($newRoleIds as $role_id) {
            $data[] = [
                'store_user_id' => $store_user_id,
                'role_id' => $role_id
            ];
        }
        return $this->saveAll($data);
    }

    /**
     * 删除记录
     * @author  fup
     * @date    2019-05-20
     */
    public static function deleteAll($where)
    {
        return self::where($where)->delete();
    }

}