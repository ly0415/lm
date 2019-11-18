<?php

namespace app\store\model;

use think\Cache;
use app\common\model\GoodsModel as GoodsModelModel;

/**
 * 商品分类模型
 * Class Category
 * @package app\store\model
 */
class GoodsModel extends GoodsModelModel
{
    /**
     * 获取模型记录
     * @param $data
     * @return false|int
     */
    public function getList($name = ''){
        $filter = [];
        !empty($name) && $filter['name'] = ['like', '%' . trim($name) . '%'];
            $list = $this
                ->where($filter)
                ->where('mark', '=', 1)
                ->order(['sort' => 'asc', 'create_time' => 'desc'])
                ->paginate(15, false, [
                    'query' => request()->request()
                ]);
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
            $this->error = '请填写模型名称';
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
//        dump($data);die;
        if(!$data['name']){
            $this->error = '请填写模型名称';
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
        return $this->allowField(true)->save(['mark'=>0]);
    }

    /**
     * 获取所有商品模型
     * @return bool|int
     * @throws \think\Exception
     */
    public static function getAllModel(){
        return self::where('mark','=',1)
            ->order('sort','ASC')
            ->select();
    }

}
