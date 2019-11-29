<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-21
 * Time: 上午 11:44
 */

namespace app\xcx\model;


class ActivityGoods extends Base
{
    /**
     * 获取商品
     * @param string $field
     */
    public function goods(){
        return $this->belongsTo('StoreGoods','goods_id','id')->field('id,goods_name,original_img');
    }

}