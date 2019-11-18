<?php

namespace app\common\model;

use think\Cache;

/**
 * 拼团商品分类模型
 * Class Category
 * @package app\common\model
 */
class GoodsCategory extends BaseModel
{
    protected $name = 'goods_category';

    /**
     * 分类图片
     * @return \think\model\relation\HasOne
     */
    public function image()
    {
        return $this->hasOne('uploadFile', 'file_id', 'image');
    }

    /**
     * 获取所有分类
     * @author  luffy
     * @date    2019-07-25
     */
    public static function getCacheAll()
    {
        return self::getALL()['cateAll'];
    }

    /**
     * 获取所有分类(树状结构)
     * @author  luffy
     * @date    2019-07-25
     */
    public static function getCacheTree()
    {
        return self::getALL()['tree'];
    }


    public function getImageAttr($val){

        if(!empty($val)){
            return ['big_file_path'=>self::$base_url . 'uploads/big/' . $val,'small_file_path'=>self::$base_url . 'uploads/small/' . $val,'value'=>$val];
        }
        return $val;
    }

    /**
     * 获取所有分类(树状结构)
     * @author  luffy
     * @date    2019-07-25
     */
    public static function getCacheTreeJson()
    {
        return json_encode(static::getCacheTree());
    }

    /**
     * 缓存/获取商品分类
     * @author  luffy
     * @date    2019-07-25
     */
    public static function getALL()
    {
//        Cache::rm('goods_category');die;
 //       Cache::clear('goods_category');
        $model = new static;
        if (!Cache::get('goods_category')) {
            //查询分类
            $data = $model->field('id,parent_id pid,name,image,level,add_time create_time,sort_order sort')->order(['sort_order' => 'ASC', 'add_time' => 'DESC'])->select();
            $all = !empty($data) ? $data->toArray() : [];

            $cateAll = $tree = $string = [];
            foreach ($all as $first) {
                $cateAll[$first['id']] = $first;

                if ($first['pid'] != 0) continue;
                $twoTree = [];
                foreach ($all as $two) {
                    if ($two['pid'] != $first['id']) continue;
                    $threeTree = [];
                    foreach ($all as $three)
                        $three['pid'] == $two['id']
                        && $threeTree[$three['id']] = $three;
                    !empty($threeTree) && $two['child'] = $threeTree;
                    $twoTree[$two['id']] = $two;
                }
                if (!empty($twoTree)) {
                    array_multisort(array_column($twoTree, 'sort'), SORT_ASC, $twoTree);
                    $first['child'] = $twoTree;
                }
                $tree[$first['id']] = $first;
            }

            //拼装分类名称
            foreach ($cateAll as $key => $first) {
                if($first['level'] == 3){
                    $cateAll[$key]['name_string'][] = $cateAll[$cateAll[$first['pid']]['pid']]['name'].'>'. $cateAll[$first['pid']]['name'] .'>'. $first['name'];
                    $cateAll[$key]['name_string'][] = $cateAll[$cateAll[$first['pid']]['pid']]['name'].'<br>'.$cateAll[$first['pid']]['name'] .'<br>'. $first['name'];
                }
            }
            Cache::tag('goods_category')->set('goods_category', compact('cateAll', 'tree'));
        }
        return Cache::get('goods_category');
    }

    /**
     * 获取指定分类下的所有三级子分类id，三级分类包括自身
     * @author  luffy
     * @date    2019-08-27
     */
    public static function getSubCategoryId($parent_id, $all = []){
        empty($all) && $all = self::getCacheAll();
        $cate = $all[$parent_id];
        if($cate['level'] == 3){
            return [$parent_id];
        }
        return getLastLevelId($all, $parent_id);
    }

    /**
     * 根据pid获取商品分类
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-14
     * Time: 16:27
     */
    public static function getGoodsCategoryByPid($pid){
        $category = self::getCacheAll();
        $data = [];
        foreach ($category as $c){
            $c['pid'] == $pid && $data[] = $c;
        }
        return $data;

    }


}
