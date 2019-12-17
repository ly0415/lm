<?php

namespace app\store\model;

use app\common\model\StoreGoodsJoint as StoreGoodsJointModel;

/**
 * 组合商品模型
 * Class StoreGoodsJoint
 * @package app\store\model
 */
class StoreGoodsJoint extends StoreGoodsJointModel
{
    /**
     * 批量添加组合商品记录
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-11-14
     * Time: 10:42
     */
    public function addJointList($goods_id, $spec_list)
    {
        $data = [];
        foreach ($spec_list as $item) {
            $data[] =  [
                'store_goods_id' => $goods_id,
                'store_goods_ids' => $item['joint']['id'],
                'key' => $item['key'],
                'key_name' => $item['key_name'],
                'num' => $item['num']
            ];
        }
        return $this->allowField(true)->saveAll($data);
    }

    /**
     * 添加商品规格关系记录
     * @param $goods_id
     * @param $spec_attr
     * @return array|false
     * @throws \Exception
     */
    public function addGoodsSpecRel($goods_id, $spec_attr)
    {
        $data = [];
        array_map(function ($val) use (&$data, $goods_id) {
            array_map(function ($item) use (&$val, &$data, $goods_id) {
                $data[] = [
                    'goods_id' => $goods_id,
                    'spec_id' => $val['group_id'],
                    'spec_value_id' => $item['item_id'],
                    'wxapp_id' => self::$wxapp_id,
                ];
            }, $val['spec_items']);
        }, $spec_attr);
        $model = new GoodsSpecRel;
        return $model->saveAll($data);
    }

    /**
     * 移除指定商品的所有sku
     * @param $goods_id
     * @return int
     */
    public function remove($store_goods_id)
    {
        return $this->where('store_goods_id','=', $store_goods_id)->delete();
    }

}
