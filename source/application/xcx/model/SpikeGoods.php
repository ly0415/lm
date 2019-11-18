<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-21
 * Time: 上午 11:44
 */

namespace app\xcx\model;
use app\common\model\SpikeGoods as SpikeGoodsModel;

class SpikeGoods extends SpikeGoodsModel
{
    /**
     * 获取秒杀活动商品
     */
    public static function getActivityGoodsList($activity_id,$time_point){
        return self::with(['activity','goods'])
            ->where('spike_activity_id','=',$activity_id)
            ->where('time_point','=',$time_point)
            ->where('mark','=',1)
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
//        dump($data->toArray());die;
    }

}