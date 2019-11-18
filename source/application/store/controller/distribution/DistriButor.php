<?php

namespace app\store\controller\distribution;

use app\store\model\FxRule as FxRuleModel;
use app\common\model\Store as StoreModel;
use app\store\controller\Controller;

/**
 * 分销规则管理
 * @author liy
 * @date    2019-10-23
 */
class DistriButor extends Controller
{
    /**
     * 分销规则显示
     * @author  liy
     * @date    2019-10-23
     */
    public function index($name=''){
//        print_r($this->yoshop_store);die;
        $FxRule  = new FxRuleModel;
        $fxruleList   = $FxRule->getList($name);
        return $this->fetch('index', compact('fxruleList'));
    }

    /**
     *分销规则编辑
     * @author  liy
     * @date    2019-10-23
     */
    public function add(){
        $model = new FxRuleModel;
        if (!$this->request->isAjax()) {
            $stores=$model->getStore();
            return $this->fetch('add', compact('stores'));
        }
        // 新增记录
//        print_r($this->postData('disbut'));die;
        if ($model->add($this->postData('disbut'))) {
            return $this->renderSuccess('添加成功', url('distribution.distri_butor/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     *分销规则编辑
     * @author  liy
     * @date    2019-10-23
     */
    public function edit($id){
        $model = new FxRuleModel;
        $storelist=$model->getStore();
        $distublist = $model->getFxRule($id);
//        print_r($distublist);die;
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('distublist','storelist'));
        }
        // 更新记录
        if ($model->edit($id,$this->postData('disbut'))) {
            return $this->renderSuccess('更新成功', url('distribution.distri_butor/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除
     * @author  liy
     * @date    2019-10-23
     */
    public function delete($id)
    {
        $model = FxRuleModel::get($id);
        if (!$model->remove($id)) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

}
