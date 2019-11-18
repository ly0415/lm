<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-17
 * Time: 下午 5:32
 */

namespace app\xcx\model;
use app\common\model\SpikeActivity as SpikeActivityModel;

class SpikeActivity extends SpikeActivityModel{
    /**
     * 获取正在进行中的秒杀活动商品
     * @param string $field
     */
    public static function getList($time=''){
        !empty($time) && $query[''] =
        $model = self::where('');
        return self::with('spike_goods')
            ->where('mark','=',1)
            ->where('begin_time','>',time())
            ->where('end_time','<',time())
            ->select();
    }

    /**
     * 验证活动状态
     * @param string $field
     */
    public static function checkStatus($activity_id){
        return self::where('id','=',$activity_id)
            ->where('begin_time','<',time())
            ->where('end_time','>',time())
            ->where('status','=',1)
            ->count();
    }

}