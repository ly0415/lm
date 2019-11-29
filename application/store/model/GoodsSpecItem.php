<?php

namespace app\store\model;

use app\common\model\GoodsSpecItem as GoodsSpecItemModel;

/**
 * 活动模型
 * Class ActivityImage
 * @package app\store\model
 */
class GoodsSpecItem extends GoodsSpecItemModel
{

    /**
     * 获取规格名称
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-08
     * Time: 20:04
     */
    public static function geyKeyName($key){
        return self::where('id','IN',$key)
            ->order('id ASC')
            ->column('item_names');
    }

}
