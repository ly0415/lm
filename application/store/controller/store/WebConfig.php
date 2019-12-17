<?php

namespace app\store\controller\store;

use app\store\controller\Controller;
use app\store\model\WebConfig as WebConfigModel;

/**
 * 网站配置
 * Class GoodsCategory
 * @package app\store\controller\source
 */
class WebConfig extends Controller
{
    /**
     * 网站配置
     * @author ly
     * @date 2019-12-5
     */
    public function index()
    {
        $model = new WebConfigModel;
        $value = $model->getList();
        if (!$this->request->isPost()) {
            return $this->fetch('index',compact('value'));
        }
        // 更新记录
        if ($model->edit($this->postData('old'),$this->postData('config'))) {
            return $this->renderSuccess('保存成功', url('store.web_config/index'));
        }
        return $this->renderError($model->getError() ?: '保存失败');
    }



}
