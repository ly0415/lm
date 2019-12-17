<?php

namespace app\common\model;

use think\Cache;
use app\store\model\StoreGoods as StoreGoodsModel;

/**
 * 业务类型
 * @author  luffy
 * @date    2019-08-23
 */
class Business extends BaseModel
{
    protected $name = 'business';

    //所属商品分类
    public function category(){
        return $this->hasMany('RoomCategory','room_type_id','id')->order('sort ASC');
    }

    /**
     * 所有业务类型
     * @author  luffy
     * @date    2019-08-23
     */
    public static function getCache(){
        $model          = new static;
        if (!Cache::get('business')) {
            $data       = $model->where(['mark'=>1])->order(['sort' => 'ASC', 'create_time' => 'DESC'])->select();
            if(!empty($data)){
                $all    = [];
                foreach($data->toArray() as $key => $value){
                    $all[$value['id']] = $value;
                }
                $tree   = toTree($all);
                Cache::tag('business')->set('business', compact('all','tree'));
            }
        }
        return Cache::get('business');
    }

    /**
     * 重置所有业务类型缓存
     * @author  luffy
     * @date    2019-07-25
     */
    public static function resetCache(){
        Cache::clear('business');
        return self::getCache();
    }

    /**
     * 获取所有业务类型
     * @author  luffy
     * @date    2019-08-23
     */
    public static function getCacheAll(){
        return self::getCache()['all'];
    }

    /**
     * 获取所有业务类型(树状结构)
     * @author  luffy
     * @date    2019-07-25
     */
    public static function getCacheTree()
    {
        return self::getCache()['tree'];
    }


    /**
     * 获取店铺业务类型
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-24
     * Time: 11:16
     */
    public static function getStoreBusiness($businessId = 0){
        $tree = self::getCacheTree();
        $business = [];
        if(isset($tree[$businessId])){
            $business = $tree[$businessId]['child'];
            foreach ($business as $k => $v){
                if(!$list = (new StoreGoodsModel)->getListAll($v['id'])->toArray()){
                    unset($business[$k]);
                }
            }
        }

        return array_values($business);
    }

    /**
     * 获取一级业务分类
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-24
     * Time: 13:50
     */
    public static function getFirstLevelBusiness(){
        $business = self::getCacheAll();
        $first = [];
        foreach ($business as $v){
            $v['pid'] === 0 && $first[] = $v;
        }
        return $first;
    }

}
