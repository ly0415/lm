<?php

namespace app\store\model;

use app\common\model\GoodsAttribute as GoodsAttributeModel;

/**
 * 商品分类模型
 * Class Business
 * @package app\store\model
 */
class GoodsAttribute extends GoodsAttributeModel
{

    /**
     * 获取属性列表
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-13
     * Time: 17:09
     */
    public function getList($model_id,$model_id1='',$type='',$listRows = 15)
    {
        $filter = [];
        if($type == 1){
            $model_id1 > 0 && $filter['type_id'] = $model_id1;
        }else{
            $model_id > 0 && $filter['type_id'] = $model_id;
        }
        $list = $this->with('goodsModel')->where($filter)
            ->order(['attr_id' => 'desc'])
            ->paginate($listRows, false, [
                'query' => \request()->request()
            ]);
        return $list;
    }


    /**
     * 获取全部业务类型列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public  function getListAll($where = []){
        $list = $this->where(['mark'=>1])
            ->where($where)
            ->order(['sort' => 'asc', 'create_time' => 'desc'])
            ->select();
        return $list;
    }

    /**
     * 添加新记录
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
//        dump($data);die;
        if(empty($data['attr_name'])){
            $this->error = '请设置属性名称';
            return false;
        }
        if(empty($data['type_id'])){
            $this->error = '请设置商品模型';
            return false;
        }
        if($this->getAttrIdByName($data['attr_name'],$data['type_id'])){
            $this->error = '属性名称已存在';
            return false;
        }
        $data['attr_input_type'] = 1;
        return $this->allowField(true)->save($data);
    }

    /**
     * 根据规格名称,模型id查询规格id
     * @param $spec_id
     * @param $spec_value
     * @return mixed
     */
    public function getAttrIdByName($attr_name, $type_id,$isUpdate = false)
    {
        $isUpdate && $this->where('attr_id','<>',$this['attr_id']);
        return $this->where(compact('type_id', 'attr_name'))->value('attr_id');
    }

    /**
     * 编辑记录
     * @param $data
     * @return bool|int
     */
    public function edit($data)
    {
        if($this->getAttrIdByName($data['attr_name'],$data['type_id'],true)){
            $this->error = '属性名称已存在';
            return false;
        }
        return $this->allowField(true)->save($data) !== false;
    }

    /**
     * 删除
     * @param $data   ly
     * @return bool|int
     */
    public function remove($attr_id)
    {
        if(empty($attr_id)){
            $this->error = '请选中需要删除的';
            return false;
        }
        if(is_array($attr_id)){
            foreach($attr_id as $val){
                $list = $this->where('attr_id',$val)->delete();
            }
            return $list;

        }else{
            return $this->where('attr_id',$attr_id)->delete() !== false;
        }
    }



}
