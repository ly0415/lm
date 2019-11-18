<?php

namespace app\store\model;

use app\common\model\Spec as SpecModel;

/**
 * 规格/属性(组)模型
 * Class Spec
 * @package app\store\model
 */
class Spec extends SpecModel
{
    /**
     * 根据规格组名称查询规格id
     * @param $spec_name
     * @return mixed
     */
    public function getSpecIdByName($goods_model_id,$spec_name)
    {
        return self::where(compact('spec_name','goods_model_id'))->value('spec_id');
    }

    /**
     * 新增规格组
     * @param $spec_name
     * @return false|int
     */
    public function add($goods_model_id,$spec_name)
    {
        $wxapp_id = self::$wxapp_id;
        $create_user = session('yoshop_store.user')['store_user_id'];
        return $this->save(compact('spec_name', 'wxapp_id','goods_model_id','create_user'));
    }


    /**
     * 获取商品模型对应规格
     * @param $goods_model_id int
     * @return array
     */
    public function getSpecValue($goods_model_id=0)
    {
        if(empty($goods_model_id))return [];
        return $this->with('specValue')
            ->where('goods_model_id','=',$goods_model_id)
            ->order('sort','DESC')
            ->select();
    }


}
