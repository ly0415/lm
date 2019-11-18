<?php

namespace app\store\model;

use app\common\model\BusinessCategory as BusinessCategoryModel;
/**
 * 商品分类模型
 * Class Category
 * @package app\store\model
 */
class BusinessCategory extends BusinessCategoryModel
{
    /**
     * 查询新记录
     * @param $data
     * @return false|int
     */
    public function getList(){
        $list = $this->with('business')
            ->where('mark','=',1)
            ->order('sort DESC')
            ->select();
        foreach($list as $value){
            $value['category'] = (new GoodsCategory())->getParentCate($value['cate_id']);
        }
        return $list;
    }
    /**
     * 添加新记录
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        if(!$data['name']){
            $this->error = '请选择业务名称';
            return false;
        }
        if(!$data['b_pid_1']){
            $this->error = '请选择一级商品分类';
            return false;
        }
        if(!$data['b_pid_2']){
            $this->error = '请选择二级商品分类';
            return false;
        }
        if(!$data['b_pid_3']){
            $this->error = '请选择三级商品分类';
            return false;
        }
        if($this->where('name','=',$data['name'])
        ->where('cate_id','=',$data['b_pid_3'])){
            $this->error = '业务名称相对应的分类已经存在';
            return false;
        }
        $data['cate_id'] = $data['b_pid_3'];
        $data['wxapp_id'] = self::$wxapp_id;
        $data['create_user'] = session('yoshop_store.user')['store_user_id'];
        return $this->allowField(true)->save($data);
    }

    /**
     * 编辑记录
     * @param $data
     * @return bool|int
     */
    public function edit($data)
    {
        if(!$data['name']){
            $this->error = '请选择业务名称';
            return false;
        }
        if(!$data['b_pid_1']){
            $this->error = '请选择一级商品分类';
            return false;
        }
        if(!$data['b_pid_2']){
            $this->error = '请选择二级商品分类';
            return false;
        }
        if(!$data['b_pid_3']){
            $this->error = '请选择三级商品分类';
            return false;
        }
        if($this->where('name','=',$data['name'])
            ->where('cate_id','=',$data['b_pid_3'])){
            $this->error = '业务名称相对应的分类已经存在';
            return false;
        }
        $data['cate_id'] = $data['b_pid_3'];
        $data['update_user'] = session('yoshop_store.user')['store_user_id'];
        return $this->allowField(true)->save($data) !== false;
    }

    /**
     * 记录详情
     * @param $data
     * @return bool|int
     */
    public static function detail($id)
    {
        $list = self::where('id','=',$id)
            ->where('mark','=',1)
            ->find();
        $data = (new GoodsCategory)->getParentId($list['cate_id']);
        $list['cate_path_id'] = $data;
        return $list;
    }



    /**
     * 删除商品分类
     * @param $category_id
     * @return bool|int
     * @throws \think\Exception
     */
    public function remove()
    {
//        return $this->delete();
        return $this->allowField(true)->save(['mark'=>0]);
    }

    /**
     * 删除缓存
     * @return bool
     */
    private function deleteCache()
    {
        return Cache::rm('category_' . self::$wxapp_id);
    }

    /**
     * 获取分类等级
     * @return array|void
     */
    private function getLevel($id)
    {
        if($id>0){
            $level = $this->where('id','=',$id)->find()->level+1;
        }else{
            $level = 1;
        }

        return $level;
    }
    /**
     * 获取分类分类
     * @return array|void
     */
    public static function getCate($pid = 0){
        $cate = self::where('mark','=',1)
            ->where('pid','=',$pid)
            ->select();
        if($cate)return $cate->toArray();
        else [];
    }

}
