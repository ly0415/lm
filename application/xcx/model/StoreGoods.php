<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-19
 * Time: 下午 4:16
 */

namespace app\xcx\model;
use app\common\model\StoreGoods as StoreGoodsModel;

class StoreGoods extends StoreGoodsModel
{
    //拼接图片完整路径
    protected function getOriginalImgAttr($value){
        return base_url() . $value;
    }
}