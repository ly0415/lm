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

    /**
     * 添加规格与规格项关系记录
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-13
     * Time: 15:07
     */
    public function addSpecItemRel($spec_id, $spec_attr,$item_attr)
    {
        $data = [];
//        array_map(function ($item) use (&$data,$spec_id) {
//            $data[] = [
//                'spec_id' => $spec_id,
//                'item_names' => $item,
//            ];
//        }, $spec_attr);

        foreach ($spec_attr as $k => $item){
            $data[] = [
                'spec_id' => $spec_id,
                'item_names' => $item,
                'item_values' => $item_attr[$k] >= 0 ? $item_attr[$k] : -1
            ];
        }
        return $this->allowField(true)->saveAll($data);
    }

    /**
     * 移除指定规格的所有规格项
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-13
     * Time: 14:46
     */
    public function removeAll($spec_id)
    {
        return $this->where('spec_id','=', $spec_id)->delete();
    }

}
