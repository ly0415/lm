<?php

namespace app\common\model;

use think\Cache;

/**
 * 业务分类
 * Class BusinessCategory
 * @package app\common\model
 */
class BusinessCategory extends BaseModel
{
    protected $name = 'business_category';

    /**
     * 查询业务名称
     * @return mixed
     */
    public function business(){
        return $this->hasOne('Business','id','name')->field('id,name');
    }

    /**
     * 所有分类
     * @return mixed
     */
    public static function getALL()
    {

        $model = new static;
//        Cache::rm('business_category_' . $model::$wxapp_id);die;
        if (!Cache::get('business_category_' . $model::$wxapp_id)) {
            $data = $model->where('mark','=',1)->order(['sort' => 'asc', 'create_time' => 'asc'])->select();
            $all = !empty($data) ? $data->toArray() : [];
            $tree = [];
            foreach ($all as $first) {
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
            Cache::tag('cache')->set('business_category_' . $model::$wxapp_id, compact('all', 'tree'));
        }
        return Cache::get('business_category_' . $model::$wxapp_id);
    }

    /**
     * 获取所有分类
     * @return mixed
     */
    public static function getCacheAll()
    {
        return self::getALL()['all'];
    }

    /**
     * 获取所有分类(树状结构)
     * @return mixed
     */
    public static function getCacheTree()
    {
        return self::getALL()['tree'];
    }

    /**
     * 获取所有分类(树状结构)
     * @return string
     */
    public static function getCacheTreeJson()
    {
        return json_encode(static::getCacheTree());
    }

    /**
     * 获取指定分类下的所有子分类id
     * @param $parent_id
     * @param array $all
     * @return array
     */
    public static function getSubCategoryId($parent_id, $all = [])
    {
        $arrIds = [$parent_id];
        empty($all) && $all = self::getCacheAll();
        foreach ($all as $key => $item) {
            if ($item['parent_id'] == $parent_id) {
                unset($all[$key]);
                $subIds = self::getSubCategoryId($item['category_id'], $all);
                !empty($subIds) && $arrIds = array_merge($arrIds, $subIds);
            }
        }
        return $arrIds;
    }


    /**
     * 根据cate_id查询业务分类
     * Class BusinessCategory
     * @package app\common\model
     */
    public static function getBusinessCateName($cateId){
        return self::with('business')
            ->field('id,name')
            ->where('cate_id','=',$cateId)
            ->where('mark','=',1)
            ->order('sort')
            ->select();
    }
}
