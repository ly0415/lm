<?php

namespace app\store\controller\store;

use app\store\controller\Controller;
use app\store\model\store\UserRole;
use app\store\model\Business as BusinessModel;
use app\store\model\store\Role as RoleModel;
use app\store\model\store\StoreUser as StoreUserModel;

/**
 * 总站管理人员控制器
 * @author  fup
 * @date    2019-05-20
 */
class StoreUser extends Controller
{
    /**
     * 用户列表
     * @author  fup
     * @date    2019-05-20
     */
    public function index()
    {
        $model = new StoreUserModel;
        $list = $model->getList();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 添加管理员
     * @author  fup
     * @date    2019-05-20
     */
    public function add()
    {
        $model = new StoreUserModel;

        //获取一级业务类型
        $BusinessModel      = new BusinessModel;
        $model['business']  = $BusinessModel -> getListAll(['pid'=>0]);

        if (!$this->request->isAjax()) {
            $where = ['type'=>1,'business_id'=>0];
            if(BUSINESS_ID){
                $where['business_id'] = BUSINESS_ID;
            }
            // 角色列表
            $roleList = (new RoleModel)->getList($where);
            return $this->fetch('add', compact('roleList', 'model'));
        }
        // 新增记录
        if ($model->add($this->postData('user'))) {
            return $this->renderSuccess('添加成功', url('store.store_user/index'));
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
        $model['roleIds']   = UserRole::getRoleIds($user_id);
        //获取一级业务类型
        $BusinessModel      = new BusinessModel;
        $model['business']  = $BusinessModel -> getListAll(['pid'=>0]);
        if (!$this->request->isAjax()) {
            $where['type']          = 1;
            $where['business_id']   = $model['business_id'] ? $model['business_id'] : 0;
            return $this->fetch('edit', [
                'model' => $model,
                // 角色列表
                'roleList' => (new RoleModel)->getList($where)
            ]);
        }
        // 更新记录
        if ($model->edit($this->postData('user'))) {
            return $this->renderSuccess('更新成功', url('store.store_user/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除管理员
     * @author  fup
     * @date    2019-05-20
     */
    public function delete($user_id)
    {
        // 管理员详情
        $model = StoreUserModel::get($user_id);
        if (!$model->setDelete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }
}
