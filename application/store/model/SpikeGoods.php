<?php

namespace app\store\model;

use app\common\model\SpikeGoods as SpikeGoodsModel;

/**
 * 活动商品模型
 * Class SpikeGoods
 * @package app\store\model
 */
class SpikeGoods extends SpikeGoodsModel
{

    /**
     * 删除秒杀活动商品
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-15
     * Time: 16:25
     */
    public function remove($spike_id){
        return $this->where('spike_id','=', $spike_id)->update(['mark'=>0]);
    }

}
