<?php

namespace app\common\model;

/**
 * 商品规格价格模型
 * Class GoodsSpec
 * @package app\common\model
 */
class GoodsSpec extends BaseModel
{
    protected $name = 'goods_spec';
    protected $updateTime = false;

    /**
     * 关联规格值
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-21
     * Time: 21:12
     */
    public function item(){
        return $this->hasMany('GoodsSpecItem','spec_id','id');
    }

    // 关联模型
    public function goodsModel(){
        return $this->belongsTo('GoodsModel','type_id','id');
    }


    /**
     * 获取商品规格
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-21
     * Time: 21:08
     */
    public function getList($item = []){

        $data = $this->alias('a')
            ->field('a.id,a.name,b.id as item_id,b.item_names as item_name')
            ->join('goods_spec_item b','a.id = b.spec_id','LEFT')
            ->where('b.id','IN',$item)
            ->order('b.id ASC')
            ->select()->toArray();
        return $data;
    }

    /**
     * 规格详情
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-13
     * Time: 15:53
     */
    public static function detail($id){
        return self::get($id,['item','goodsModel']);
    }
}
