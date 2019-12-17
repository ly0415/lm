<?php

namespace app\store\model;

use think\Cache;
use app\common\model\GoodsStyle as GoodsStyleModel;

/**
 * 商品风格模型
 * Class GoodsStyle
 * @package app\store\model
 */
class GoodsStyle extends GoodsStyleModel
{

    /**
     * 获取风格记录
     * @param $data
     * @return false|int
     */
    public function getList($name = ''){
        $filter = [];
        !empty($name) && $filter['name'] = ['like', '%' . trim($name) . '%'];
        $list = $this
            ->where($filter)
            ->order(['add_time' => 'desc'])
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
        !array_key_exists('image', $data) && $data['logo'] = 0;
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
        !array_key_exists('logo', $data) && $data['logo'] = 0;
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
        return self::with('logo')
        ->where('id','=',$id)
            ->find();
    }

    /**
     * 删除商品分类
     * @param $category_id
     * @return bool|int
     * @throws \think\Exception
     */
    public function remove($category_id)
    {
        // 判断是否存在商品
        if ($goodsCount = (new Goods)->getGoodsTotal(['category_id' => $category_id])) {
            $this->error = '该分类下存在' . $goodsCount . '个商品，不允许删除';
            return false;
        }
        // 判断是否存在子分类
        if ((new self)->where(['parent_id' => $category_id])->count()) {
            $this->error = '该分类下存在子分类，请先删除';
            return false;
        }
        $this->deleteCache();
        return $this->delete();
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
