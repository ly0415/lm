<?php

namespace app\store\model;

use app\common\model\Business as BusinessModel;

/**
 * 业务类型模型
 * @author  fup
 * @date    2019-08-23
 */
class Business extends BusinessModel
{
    /**
     * 获取全部业务类型列表
     * @author  fup
     * @date    2019-08-23
     */
    public  function getListAll($where = []){
        $list = $this->where(['mark'=>1])
            ->where($where)
            ->order(['sort' => 'asc', 'create_time' => 'desc'])
            ->select();
        return $list;
    }

    /**
     * 添加业务类型
     * @author  fup
     * @date    2019-08-23
     */
    public function add($data)
    {
        $data['level'] = 1;
        if(!$data['name']){
            $this->error = '请选择业务名称';
            return false;
        }
        if($data['pid'] > 0){

            $data['level'] = 2;
        }
        $data['create_user'] = USER_ID;
        return $this->allowField(true)->save($data);
    }

    /**
     * 添加业务类型商品分类
     * @author  fup
     * @date    2019-08-23
     */
    public function _add($data)
    {
        $businessCategoryModel = new RoomCategory();
        if(!$data['b_pid_1'] || !$data['b_pid_2'] || !$data['category_id']){
            $this->error = '请选择商品分类';
            return false;
        }
        if($businessCategoryModel::detail(['room_type_id'=>$data['room_type_id'],'category_id'=>$data['category_id']])){
            $this->error = '已存在该商品分类的业务类型';
            return false;
        }
        return $businessCategoryModel->add($data);
    }

    /**
     * 编辑业务类型
     * @author  fup
     * @date    2019-08-23
     */
    public function edit($data)
    {
        $data['level'] = 1;
        if(!$data['name']){
            $this->error = '请选择业务名称';
            return false;
        }
        if($data['pid'] > 0){

            $data['level'] = 2;
        }
        $data['update_user'] = USER_ID;
        return $this->allowField(true)->save($data) !== false;
    }

    /**
     * 编辑业务类型商品分类
     * @author  fup
     * @date    2019-08-23
     */
    public function _edit($data)
    {
        $businessCategoryModel = new RoomCategory();
        if(!$data['category_id']){
            $this->error = '请选择商品分类';
            return false;
        }
        if($businessCategoryModel::detail(['room_type_id'=>$data['room_type_id'],'category_id'=>$data['category_id'],'id'=>['neq',$data['id']]])){
            $this->error = '已存在该商品分类的业务类型';
            return false;
        }
        return $businessCategoryModel->edit($data);
    }

    /**
     * 业务类型软删除
     * @author  fup
     * @date    2019-08-23
     */
    public function setDelete($category_id)
    {
        // 判断是否存在子分类
        if ((new self)->where(['pid' => $category_id])->count()) {
            $this->error = '该业务类型下存在子类型，请先删除';
            return false;
        }
        $this->category()->delete();
        return $this->save(['mark'=>0]);
    }

    /**
     * 递归查询
     * @author  fup
     * @date    2019-08-23
     */
    public function tree($array, $pid = 0 )
    {
        $tree = array();
        foreach ($array as $key => $value) {
            if ($value['pid'] == $pid) {
                $value['child'] = $this->tree($array, $value['id']);
                if (!$value['child']) {
                    unset($value['child']);
                }
                $tree[] = $value;
            }
        }
        return $tree;
    }

    /**
     * 获取业务类型所属商品列表
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-11-21
     * Time: 09:17
     */
    public function getCategoryList(){
        foreach ($this['category'] as &$item){
            $item['format_category'] = GoodsCategory::getCateByThreeId($item['category_id']);
        }
        return $this;
    }

}
