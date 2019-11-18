<?php

namespace app\store\model\store;

use app\common\model\store\Role as RoleModel;

/**
 * 商家用户角色模型
 * @author  fup
 * @date    2019-05-20
 */
class Role extends RoleModel
{
    /**
     * 获取角色列表
     * @author  fup
     * @date    2019-05-20
     */
    public function getList($where = [])
    {
        $all = $this->getAll($where);
        return $this->formatTreeData($all);
    }

    /**
     * 获取所有角色
     * @author  fup
     * @date    2019-05-20
     */
    private function getAll($where = [])
    {
        $data = $this->with('business')
            ->where($where)->order(['sort' => 'asc', 'create_time' => 'asc'])->select();
        return $data ? $data->toArray() : [];
    }

    /**
     * 关联店铺模型
     * @author  fup
     * @date    2019-05-20
     */
    public function business()
    {
        return $this->hasOne('\app\store\model\Business','id','business_id');
    }

    /**
     * 新增记录
     * @author  fup
     * @date    2019-05-20
     */
    public function add($data)
    {
        if (empty($data['access'])) {
            $this->error = '请选择所属门店';
            return false;
        }
        if(BUSINESS_ID){
            $data['business_id'] = BUSINESS_ID;
        }
        if (empty($data['access'])) {
            $this->error = '请选择权限';
            return false;
        }
        $this->startTrans();
        try {
            // 新增角色记录
            $this->allowField(true)->save($data);
            // 新增角色权限关系记录
            (new RoleAccess)->add($this['role_id'], $data['access']);
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
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit($data)
    {
        if (empty($data['access'])) {
            $this->error = '请选择权限';
            return false;
        }
        // 判断上级角色是否为当前子级
        if ($data['parent_id'] > 0) {
            // 获取所有上级id集
            $parentIds = $this->getTopRoleIds($data['parent_id']);
            if (in_array($this['role_id'], $parentIds)) {
                $this->error = '上级角色不允许设置为当前子角色';
                return false;
            }
        }
        $this->startTrans();
        try {
            // 更新角色记录
            $this->allowField(true)->save($data);
            // 更新角色权限关系记录
            (new RoleAccess)->edit($this['role_id'], $data['access']);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 获取所有上级id集
     * @param $role_id
     * @param null $all
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getTopRoleIds($role_id, &$all = null)
    {
        static $ids = [];
        is_null($all) && $all = $this->getAll();
        foreach ($all as $item) {
            if ($item['role_id'] == $role_id && $item['parent_id'] > 0) {
                $ids[] = $item['parent_id'];
                $this->getTopRoleIds($item['parent_id'], $all);
            }
        }
        return $ids;
    }

    /**
     * 删除记录
     * @return bool|int
     * @throws \think\exception\DbException
     */
    public function remove()
    {
        // 判断是否存在下级角色
        if (self::detail(['parent_id' => $this['role_id']])) {
            $this->error = '当前角色下存在子角色，不允许删除';
            return false;
        }
        // 删除对应的权限关系
        RoleAccess::deleteAll(['role_id' => $this['role_id']]);
        return $this->delete();
    }

    /**
     * 获取权限列表
     * @param $all
     * @param int $parent_id
     * @param int $deep
     * @return array
     */
    private function formatTreeData(&$all, $parent_id = 0, $deep = 1)
    {
        static $tempTreeArr = [];
        foreach ($all as $key => $val) {
            if ($val['parent_id'] == $parent_id) {
                // 记录深度
                $val['deep'] = $deep;
                // 根据角色深度处理名称前缀
                $val['role_name_h1'] = $this->htmlPrefix($deep) . $val['role_name'];
                $tempTreeArr[] = $val;
                $this->formatTreeData($all, $val['role_id'], $deep + 1);
            }
        }
        return $tempTreeArr;
    }

    /**
     * 角色名称 html格式前缀
     * @param $deep
     * @return string
     */
    private function htmlPrefix($deep)
    {
        // 根据角色深度处理名称前缀
        $prefix = '';
        if ($deep > 1) {
            for ($i = 1; $i <= $deep - 1; $i++) {
                $prefix .= '&nbsp;&nbsp;&nbsp;├ ';
            }
            $prefix .= '&nbsp;';
        }
        return $prefix;
    }

    /**
     * 根据业务品牌获取所属角色
     * @author  luffy
     * @date    2019-09-02
     */
    public function getBusinessRole($business_id, $type){
        $all = $this->getAll(['business_id'=>$business_id,'type'=>$type]);
        return $this->formatTreeData($all);
    }
}