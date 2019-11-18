<?php

namespace app\common\model;

/**
 * 购物车模型
 * @author  luffy
 * @date    2019-07-30
 */
class Cart extends BaseModel
{
    protected $name = 'cart';

    protected $createTime = 'add_time';

    protected $updateTime = false;

    /**
     * 关联店铺商品
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-28
     * Time: 11:33
     */
    public function storeGoods(){
        return $this->belongsTo('StoreGoods','goods_id','id')
            ->field('id,goods_id,room_id,original_img,attributes');
    }
}
