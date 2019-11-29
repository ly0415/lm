<?php

namespace app\store\controller\store;

use app\store\controller\Controller;
use app\store\model\store\Role as RoleModel;
use app\store\model\Business    as BusinessModel;
use app\store\model\store\Access as AccessModel;

/**
 * 总站角色控制器
 * @author  fup
 * @date    2019-05-20
 */
class Role extends Controller
{
    /**
     * 角色列表
     * @author  fup
     * @date    2019-05-20
     */
    public function index()
    {
        $model = new RoleModel;
        $where = ['type'=>1];
        if(T_BUSINESS){
            $where['business_id'] = BUSINESS_ID;
        }
        $list = $model->getList($where);
        return $this->fetch('index', compact('list'));
    }

    /**
     * 添加角色
     * @author  fup
     * @date    2019-05-20
     */
    public function add()
    {
        $model = new RoleModel;
        if (!$this->request->isAjax()) {
            //业务类型
            $bussinessList = (new BusinessModel)->getListAll(['pid'=>0]);
            // 权限列表
            $accessList = (new AccessModel)->getJsTree();

            $where = ['type'=>1,'business_id'=>0];
            if(BUSINESS_ID){
                $where['business_id'] = BUSINESS_ID;
            }
            // 角色列表
            $roleList = $model->getList($where);
            return $this->fetch('add', compact('accessList', 'roleList', 'bussinessList'));
        }
        // 新增记录
        if ($model->add($this->postData('role'))) {
            return $this->renderSuccess('添加成功', url('store.role/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 更新角色
     * @author  fup
     * @date    2019-05-20
     */
    public function edit($role_id)
    {
        // 角色详情
        $model = RoleModel::detail($role_id);
        if (!$this->request->isAjax()) {
            // 权限列表
            $accessList = (new AccessModel)->getJsTree($model['role_id'], [], 1);
            // 角色列表
            $roleList = $model->getList(['business_id'=>$model['business_id'], 'type'=>1]);
            return $this->fetch('edit', compact('model', 'accessList', 'roleList'));
        }
        // 更新记录
        if ($model->edit($this->postData('role'))) {
            return $this->renderSuccess('更新成功', url('store.role/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除角色
     * @author  fup
     * @date    2019-05-20
     */
    public function delete($role_id)
    {
        // 角色详情
        $model = RoleModel::detail($role_id);
        if (!$model->remove()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 根据业务品牌获取所属角色
     * @author  luffy
     * @date    2019-09-02
     */
    public function getRole($business_id, $type)
    {
        $model = new RoleModel;
        $roleList = $model->getBusinessRole($business_id, $type);
        return $this->renderSuccess('ok','', $roleList);
    }
}
