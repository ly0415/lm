<?php

namespace app\store\controller\shop;

use app\store\controller\Controller;
use app\store\model\Store;
use app\store\model\store\UserRole;
use app\store\model\shop\Role as RoleModel;
use app\store\model\shop\StoreUser as StoreUserModel;

/**
 * 商家用户控制器
 * Class StoreUser
 * @package app\store\controller
 */
class StoreUser extends Controller
{
    /**
     * 用户列表
     * @author  fup
     * @date    2019-05-20
     */
    public function index(){
        $shopList = (new Store)->getStoreList(TRUE,BUSINESS_ID);
        $model = new StoreUserModel;
        $list = $model->getShopList($this->request->param());

        if($this->postData('get_role_list')){
            //根据店铺ID获取对应业务类型所属的角色
            $model = new RoleModel;
            $roleList = $model->getStoreRole($this->postData('store_id'));
            return $this->renderSuccess('ok','', $roleList);
        }
        return $this->fetch('index', compact('list','shopList','where'));
    }

    /**
     * 添加管理员
     * @author  fup
     * @date    2019-05-20
     */
    public function add()
    {
        $model = new StoreUserModel;
        if (!$this->request->isAjax()) {
            //门店列表
            $shopList = Store::getStoreList(TRUE,BUSINESS_ID);
            // 角色列表
            $where['type'] = 2;
            $roleList = (new RoleModel)->getList($where);
            return $this->fetch('add', compact('shopList','roleList'));
        }
        // 新增记录
        if ($model->add($this->postData('user'))) {
            return $this->renderSuccess('添加成功', url('shop.store_user/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 更新管理员
     * @author  fup
     * @date    2019-05-20
     */
    public function edit($user_id)
    {
        // 获取管理人员详情
        $StoreUserModel     = new StoreUserModel;
        $model              = $StoreUserModel->getStoreUserInfo($user_id);
        $model['roleIds']   = UserRole::getRoleIds($model['id']);
        if (!$this->request->isAjax()) {
            //门店列表
            $shopList = Store::getStoreList(TRUE,BUSINESS_ID);

            return $this->fetch('edit', [
                'model' => $model,
                // 角色列表
                'roleList' => (new RoleModel)->getStoreRole($model['store_id']),
                'shopList'=> $shopList,
                // 所有角色id
                'roleIds' => UserRole::getRoleIds($model['id']),
            ]);
        }
        // 更新记录
        if ($model->edit($this->postData('user'))) {
            return $this->renderSuccess('更新成功', url('shop.store_user/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');

    }

    /**
     * 删除管理员
     * @param $user_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($user_id)
    {
        // 管理员详情
        $model = StoreUserModel::detail($user_id);
        if (!$model->setDelete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 更新当前管理员信息
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function renew()
    {
        // 管理员详情
        $model = StoreUserModel::detail($this->store['user']['store_user_id']);
        if ($this->request->isAjax()) {
            if ($model->renew($this->postData('user'))) {
                return $this->renderSuccess('更新成功');
            }
            return $this->renderError($model->getError() ?: '更新失败');
        }
        return $this->fetch('renew', compact('model'));
    }
}
