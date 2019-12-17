<?php

namespace app\store\controller\goods;

use app\store\controller\Controller;
use app\store\model\Spec as SpecModel;
use app\store\model\SpecValue as SpecValueModel;
use app\store\model\GoodsModel as GoodsModelModel;

/**
 * 商品规格控制器
 * Class Spec
 * @package app\store\controller
 */
class Spec extends Controller
{
    /* @var SpecModel $SpecModel */
    private $SpecModel;

    /* @var SpecValueModel $SpecModel */
    private $SpecValueModel;

    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->SpecModel = new SpecModel;
        $this->SpecValueModel = new SpecValueModel;
    }

    /**
     * 添加规则组
     * @param $spec_name
     * @param $spec_value
     * @return array
     */
    public function addSpec($goods_model_id,$spec_name, $spec_value)
    {
        // 判断规格组是否存在
        if (!$specId = $this->SpecModel->getSpecIdByName($goods_model_id,$spec_name)) {
            // 新增规格组and规则值
            if ($this->SpecModel->add($goods_model_id,$spec_name)
                && $this->SpecValueModel->add($this->SpecModel['spec_id'], $spec_value))
                return $this->renderSuccess('', '', [
                    'spec_id' => (int)$this->SpecModel['spec_id'],
                    'spec_value_id' => (int)$this->SpecValueModel['spec_value_id'],
                ]);
            return $this->renderError();
        }
        // 判断规格值是否存在
        if ($specValueId = $this->SpecValueModel->getSpecValueIdByName($specId, $spec_value)) {
            return $this->renderSuccess('', '', [
                'spec_id' => (int)$specId,
                'spec_value_id' => (int)$specValueId,
            ]);
        }
        // 添加规则值
        if ($this->SpecValueModel->add($specId, $spec_value))
            return $this->renderSuccess('', '', [
                'spec_id' => (int)$specId,
                'spec_value_id' => (int)$this->SpecValueModel['spec_value_id'],
            ]);
        return $this->renderError();
    }

    /**
     * 添加规格值
     * @param $spec_id
     * @param $spec_value
     * @return array
     */
    public function addSpecValue($spec_id, $spec_value)
    {
        // 判断规格值是否存在
        if ($specValueId = $this->SpecValueModel->getSpecValueIdByName($spec_id, $spec_value)) {
            return $this->renderSuccess('', '', [
                'spec_value_id' => (int)$specValueId,
            ]);
        }
        // 添加规则值
        if ($this->SpecValueModel->add($spec_id, $spec_value))
            return $this->renderSuccess('', '', [
                'spec_value_id' => (int)$this->SpecValueModel['spec_value_id'],
            ]);
        return $this->renderError();
    }

    public function getSpecByGoodsModelId($goods_model_id){
        $spec = $this->SpecModel->getSpecValue($goods_model_id);
        return $this->renderSuccess('SUCCESS','',$spec);
    }

    /**
     * 根据模型获取商品规格
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-16
     * Time: 18:15
     */
    public function get_spec($model_id = null){
        $model = GoodsModelModel::detail($model_id);
        $this->view->engine->layout(false);
        return $this->fetch('spec', compact('model'));
    }
    /**
     * 根据模型获取商品规格
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-16
     * Time: 18:15
     */
    public function get_attribute($model_id = null){
        $model = GoodsModelModel::detail($model_id);
        $this->view->engine->layout(false);
        return $this->fetch('attribute', compact('model'));
    }

}
