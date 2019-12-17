<?php

/**
 * 店铺运费规则模块模型
 * @author zhangkx
 * @date 2019/4/9
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class StoreFareRuleMod extends BaseMod {

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct("store_fare_rule");
    }

    /**
     * 根据商品获取运费
     * @author zhangkx
     * @date 2019/4/9
     * @param $goodsList  格式：array(array('goods_id' => 35,'number' => 2))
     * @param $storeId
     * @return string
     */
    public function getFare($goodsList, $storeId)
    {
        $storeGoodsMod = &m('storeGoods');
        $goodsCount = 0;
        $goodsFare = array();
        foreach ($goodsList as $key => $value) {
            //获取商品运费
            $goods = $storeGoodsMod->getOne(array('cond'=>'goods_id = '.$value['goods_id'].' and store_id = '.$storeId.' and mark = 1'));
            $goodsFare[] = $goods['delivery_fee'] * $value['number'];
            //商品数量
            $goodsCount += $value['number'];
        }
        $fare = array_sum($goodsFare);
        //运费规则
        $data = $this->getData(array('cond'=>'mark = 1 and store_id = '.$storeId));
        $percent = 0;
        //判断运费应属于哪种规则
        if ($data) {
            foreach ($data as $key => $value) {
                $min = 0;
                $max = 0;
                if ($value['min_symbol'] == 1 && $value['max_symbol'] == 1) {
                    $min = $value['min_number'] + 1;
                    $max = $value['max_number'] - 1;
                }
                if ($value['min_symbol'] == 1 && $value['max_symbol'] == 2) {
                    $min = $value['min_number'] + 1;
                    $max = $value['max_number'];
                }
                if ($value['min_symbol'] == 2 && $value['max_symbol'] == 1) {
                    $min = $value['min_number'];
                    $max = $value['max_number'] - 1;
                }
                if ($value['min_symbol'] == 2 && $value['max_symbol'] == 2) {
                    $min = $value['min_number'];
                    $max = $value['max_number'];
                }
                $array = array();
                for ($i=$min;$i<=$max;$i++) {
                    $array[] = $i;
                }

                if (in_array($goodsCount, $array)) {
                    $percent = $value['percent'];
                }
            }
        }
        //计算运费
        if ($percent) {
            $fare = $fare * $percent * 0.01;
        }
        return $fare;
    }

}

?>