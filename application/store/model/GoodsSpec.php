<?php

namespace app\store\model;

use app\common\model\GoodsSpec as GoodsSpecModel;

/**
 * 规格模型
 * Class GoodsSpec
 * @package app\store\model
 */
class GoodsSpec extends GoodsSpecModel
{

    public function getSpecList($model_id,$name,$model_id1='',$type='')
    {
        $filter = [];
        if($type == 1){
            $model_id1 > 0 && $filter['type_id'] = $model_id1;
        }else{
            $model_id > 0 && $filter['type_id'] = $model_id;
        }
        !empty($name) && $filter['name'] = ['like','%'.trim($name).'%'];
        // 执行查询
        $list = $this->with(['item','goodsModel'])
            ->where($filter)
            ->order(['sort'=>'ASC'])
            ->paginate(15, false, ['query' => \request()->request()])
        ->each(function ($item){
             $item['format_item'] = implode(';',array_column($item['item']->toArray(),'item_names'));
            return $item;
        });
        return $list;
    }

    /**
     * 添加规格
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-13
     * Time: 14:38
     */
    public function add($data){
        if($specId = $this->getSpecIdByName($data['type_id'],$data['name'])){
            $this->error = '规格名称已存在';
            return false;
        }
        // 开启事务
        $this->startTrans();
        try {
            // 添加商品
            $this->allowField(true)->save($data);
            // 商品规格
            $this->addSpecItem($data);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }


    /**
     * 编辑规格
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-13
     * Time: 14:38
     */
    public function edit($data){
        if($specId = $this->getSpecIdByName($data['type_id'],$data['name'],true)){
            $this->error = '规格名称已存在';
            return false;
        }
        // 开启事务
        $this->startTrans();
        try {
            // 添加商品
            $this->allowField(true)->save($data);
            // 商品规格
            $this->addSpecItem($data,true);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }


    /**
     * 添加商品规格
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-13
     * Time: 14:44
     */
    private function addSpecItem(&$data, $isUpdate = false)
    {
        // 更新模式: 先删除所有规格
        $model = new GoodsSpecItem();
        $isUpdate && $model->removeAll($this['id']);
        // 添加规格数据
        $model->addSpecItemRel($this['id'], $data['spec_item']['item_specs'],$data['spec_item']['values']);
    }



    /**
     * 根据规格名称,模型id查询规格id
     * @param $spec_id
     * @param $spec_value
     * @return mixed
     */
    public function getSpecIdByName($type_id, $name,$isUpdate = false)
    {
        $isUpdate && $this->where('id','<>',$this['id']);
        return $this->where(compact('type_id', 'name'))->value('id');
    }


    /**
     * 删除规格
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-13
     * Time: 16:35
     */
    public function remove(){
        $this->item()->delete();
        return $this->delete();
    }


}
