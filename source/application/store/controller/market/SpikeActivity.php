<?php

namespace app\store\controller\market;

use app\store\controller\Controller;
use app\store\model\SpikeActivity as SpikeActivityModel;

/**
 * 秒杀控制器
 * Class SpikeActivity
 * @package app\store\controller\content
 */
class SpikeActivity extends Controller
{
    /**
     * 秒杀列表
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-15
     * Time: 16:56
     */
    public function index()
    {
        $model = new SpikeActivityModel();
        $list = $model->getList();
//        dump($list->toArray());die;
        return $this->fetch('index', compact('list'));
    }

    /**
     * 添加秒杀活动
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-15
     * Time: 16:54
     */
    public function add()
    {
        $model = new SpikeActivityModel;
        if (!$this->request->isAjax()) {
            $type = $model->types;
            return $this->fetch('add',compact('type'));
        }
        // 新增记录
        if ($model->add($this->postData('spike'))) {
            return $this->renderSuccess('添加成功', url('market.spike_activity/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 更新秒杀活动
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-15
     * Time: 16:54
     */
    public function edit($id)
    {
        // 秒杀信息
        $model = SpikeActivityModel::detail($id);
//        dump($model->toArray());die;
        if (!$this->request->isAjax()) {
            $type = $model->types;
            $time = $model->time;
            $model->formatData();
//            dump($model->toArray());die;
            return $this->fetch('edit', compact('model','type','time'));
        }
        // 更新记录
        if ($model->edit($this->postData('spike'))) {
            return $this->renderSuccess('更新成功', url('market.spike_activity/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除秒杀活动
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-15
     * Time: 16:56
     */
    public function delete($id)
    {
        // 秒杀详情
        $model = SpikeActivityModel::detail($id);
        if (!$model->setDelete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 开关活动
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-18
     * Time: 15:03
     *
     */
    public function state($id, $state){
        $model = SpikeActivityModel::detail($id);
        if(!$model->setState($state)){
            return $this->renderError('操作失败');
        }
        return $this->renderSuccess('操作成功');
    }

}