<?php

namespace app\store\controller\store;

use app\store\controller\Controller;
use app\store\model\Store;
use app\store\model\StoreActivity as StoreActivityModel;
use app\store\model\StoreActivity;
use app\store\model\StoreActivityUser as StoreActivityUserModel;

/**
 * 店铺活动
 * Class Activity
 * @package app\store\controller
 */
class Activity extends Controller
{

    /**
     * 店铺活动列表
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-11
     * Time: 15:13
     */
    public function index(){
        $model = new StoreActivityModel();
        $list = $model->getList();
        return $this->fetch('index',compact('list'));

    }

    /**
     * 添加活动
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-11
     * Time: 15:13
     */
    public function add()
    {
        if(!$this->request->isAjax()){
            $type = StoreActivityModel::$activity_type;
            $stores = Store::getStoreList(true);
            return $this->fetch('add',compact('type','stores'));
        }
        $model = new StoreActivityModel();
        if($model->add($this->postData('activity'))){
            return $this->renderSuccess('添加成功',url('store.activity/index'));
        }
        return $this->renderError($model->getError() ? : '添加失败');
    }

    /**
     * 编辑活动
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-11
     * Time: 15:14
     */
    public function edit($id)
    {
        // 模板详情
        $model = StoreActivityModel::detail($id);
//        dump($model->toArray());die;
        if (!$this->request->isAjax()) {
            $type = StoreActivityModel::$activity_type;
            $stores = Store::getStoreList(true);
//            dump($stores);die;
            return $this->fetch('edit', compact('model','type','stores'));
        }
        // 更新记录
        if ($model->edit($this->postData('activity'))) {
            return $this->renderSuccess('更新成功', url('store.activity/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }


    /**
     * 修改活动状态
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-12
     * Time: 15:14
     */
    public function state($activity_id, $state)
    {
        // 商品详情
        $model = StoreActivity::detail($activity_id);
        if (!$model->setStatus($state)) {
            return $this->renderError('操作失败');
        }
        return $this->renderSuccess('操作成功');
    }

    /**
     * 删除活动
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-23
     * Time: 15:50
     */
    public function delete($id)
    {
        // 商品详情
        $model = StoreActivity::detail($id);
        if (!$model->setDelete()) {
            return $this->renderError('删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 报名人员
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-11
     * Time: 20:05
     */
    public function view($activity_id,$username = null,$phone = null){
        $list = StoreActivity::detail($activity_id);
        $user = (new StoreActivityUserModel)->getActivityUser($activity_id,$username,$phone);
//        dump($user->toArray());die;

        return $this->fetch('view',compact('list','user'));
    }

}
