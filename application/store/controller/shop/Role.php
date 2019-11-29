<?php

namespace app\store\controller\shop;

use app\store\controller\Controller;
use app\store\model\shop\Role       as RoleModel;
use app\store\model\store\Shop      as ShopModel;
use app\store\model\Business        as BusinessModel;
use app\store\model\store\Access    as AccessModel;

/**
 * 商家用户角色控制器
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
        $model  = new RoleModel;
        $where['type'] = 2;
        $list   = $model->getList($where);
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
            $accessList = (new AccessModel)->getJsTree('',['is_site'=>0]);
            $storeList = (new ShopModel())->getList();
            // 角色列表
            $roleList = $model->getList(['type'=>2]);
            return $this->fetch('add', compact('accessList', 'roleList','storeList', 'bussinessList'));
        }
        // 新增记录
        if ($model->add($this->postData('role'))) {
            return $this->renderSuccess('添加成功', url('shop.role/index'));
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
            //业务类型
            $bussinessList = (new BusinessModel)->getListAll(['pid'=>0]);
            // 权限列表
            $accessList = (new AccessModel)->getJsTree($model['role_id'],['is_site'=>0]);
            // 角色列表
            $roleList = $model->getList(['business_id'=>$model['business_id'], 'type'=>2]);
            return $this->fetch('edit', compact('model', 'accessList', 'roleList', 'bussinessList'));
        }
        // 更新记录
        if ($model->edit($this->postData('role'))) {
            return $this->renderSuccess('更新成功', url('shop.role/index'));
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
}
