<?php

namespace app\store\model;

use app\common\model\GoodsModelAttr as GoodsModelAttrModel;

/**
 * 商品分类模型
 * Class Business
 * @package app\store\model
 */
class GoodsModelAttr extends GoodsModelAttrModel
{

    /**
     * 获取业务类型列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        $list = $this->where('mark', '=', 1)
            ->order(['sort' => 'asc', 'create_time' => 'desc'])
            ->select();
        if($list)$list = $list->toArray();
        $data = $this->tree($list);
        return $data;
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
        $goods_model_id = $data['goods_model_id'];
        $attr_name = array_filter($data['attr_name']);
        $sort = $data['sort'];
        $data = [];
        foreach ($attr_name as $k => $v){
            $data[] = [
                'goods_model_id' => $goods_model_id,
                'attr_name' => $v,
                'sort'      => $sort[$k],
                'wxapp_id'  => self::$wxapp_id,
                'create_user'=> session('yoshop_store.user')['store_user_id'],
            ];
        }
        return $this->allowField(true)->saveAll($data);
    }

    /**
     * 编辑记录
     * @param $data
     * @return bool|int
     */
    public function edit($data)
    {
        if($data['pid'] > 0){
            $data['level'] = 1;
        }
//        dump($data);die;
        $data['wxapp_id'] = self::$wxapp_id;
        $data['update_user'] = session('yoshop_store.user')['store_user_id'];
        return $this->allowField(true)->save($data) !== false;
    }

    /**
     * 软删除
     * @param $id
     * @return bool|int
     */
    public function setDelete()
    {

        return $this->save(['mark'=>0]);
    }

    /**
     * 递归查询
     * @param $array array
     * @param $pid int
     * @return bool|int
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

}
