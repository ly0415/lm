<?php

namespace app\store\model;

use app\common\model\GoodsAuxiliaryClass as GoodsAuxiliaryClassModel;

/**
 * 商品辅助分类表
 * @author  fup
 * @date    2019-011-21
 */
class GoodsAuxiliaryClass extends GoodsAuxiliaryClassModel
{


    public function add($data,$goods_sn){
        return $this->allowField(true)->save(['goods_sn'=>$goods_sn,'cate_id'=>$data['cat_id'],'business_id'=>!empty($data['room_id']) ? $data['room_id'] : 0]);
    }


    /**
     * 删除商品辅助分类
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-11-29
     * Time: 17:55
     */
    public function remove($goods_sn){
        return $this->where('goods_sn','=', $goods_sn)->delete();
    }

}
