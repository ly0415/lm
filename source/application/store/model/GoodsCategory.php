<?php

namespace app\store\model;

use think\Cache;
use app\common\model\GoodsCategory as GoodsCategoryModel;

/**
 * 商品分类模型
 * Class Category
 * @package app\store\model
 */
class GoodsCategory extends GoodsCategoryModel
{
    /**
     * 添加新记录
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        $level = $this->getLevel($data['parent_id']);
        if($level > 3){
            $this->error = '分类等级最多三级';
            return false;
        }
        !array_key_exists('image', $data) && $data['image'] = '';
        $data['level'] = $level;
        $data['add_time'] = time();
        $this->deleteCache();
        return $this->allowField(true)->save($data);
    }

    /**
     * 编辑记录
     * @param $data
     * @return bool|int
     */
    public function edit($data)
    {
        $level = $this->getLevel($data['parent_id']);
        if($level > 3){
            $this->error = '分类等级最多三级';
            return false;
        }
        !array_key_exists('image', $data) && $data['image'] = '';
        $data['level'] = $level;
        $data['modify_time'] = time();
        $this->deleteCache();
        return $this->allowField(true)->save($data) !== false;
    }


    /**
     * 记录详情
     * @param $data
     * @return bool|int
     */
    public static function detail($id)
    {
        return self::with('image')
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
        if ($goodsCount = (new Goods)->getGoodsTotal(['cat_id' => $category_id])) {
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
        return Cache::rm('goods_category');
    }

    /**
     * 获取分类等级
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-07-16
     * Time: 14:42
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
     * 根据pid获取上级分类
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-16
     * Time: 14:35
     */
    public static function getCateById($id = 0){
        $all = self::getCacheAll();
        $data = [];
        foreach ($all as $k => $v){
            if($v['pid'] == $id){
                $data[] = $v;
            }
        }
        return $data;
    }

    /**
     * 根据第三级id获取分类
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-16
     * Time: 15:54
     */
    public static function getCateByThreeId($id = 0){
        $category = [];
        $all = self::getCacheTree();
        foreach ($all as $k => $first){
            if(isset($first['child'])){
                foreach ($first['child'] as $two){
                    if(isset($two['child'])){
                        foreach ($two['child'] as $three){
                            if($three['id'] == $id){
                                $category = [
                                    'first' => ['id'=>$first['id'],'name'=>$first['name'],'sort'=>$first['sort'],'create_time'=>$first['create_time']],
                                    'two' => ['id'=>$two['id'],'name'=>$two['name'],'sort'=>$two['sort'],'create_time'=>$two['create_time']],
                                    'three' => ['id'=>$three['id'],'name'=>$three['name'],'sort'=>$three['sort'],'create_time'=>$three['create_time']],
                                    'category'=>$first
                                ];
                            }
                        }
                    }
                }
            }

        }
        return $category;
    }

}
