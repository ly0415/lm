<?php

namespace app\store\controller\activity;

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
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new SpikeActivityModel();
        $list = $model->getList();
//        dump($list->toArray());die;
        return $this->fetch('index', compact('list'));
    }

    /**
     * 添加砍价
     * @return array|mixed
     */
    public function add()
    {
        $model = new SpikeActivityModel;
        if (!$this->request->isAjax()) {
            return $this->fetch('add');
        }
        // 新增记录
        if ($model->add($this->postData('activity'))) {
            return $this->renderSuccess('添加成功', url('activity.SpikeActivity/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 更新砍价活动
     * @param $article_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function edit($id)
    {
        // 砍价信息
        $model = StoreBargainModel::detail($id);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        // 更新记录
        if ($model->edit($this->postData('activity'))) {
            return $this->renderSuccess('更新成功', url('activity.kanjia/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除文章
     * @param $article_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($id)
    {
        // 文章详情
        $model = StoreBargainModel::detail($id);
        if (!$model->setDelete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

}