<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-21
 * Time: 上午 11:43
 */

namespace app\xcx\controller;
use app\xcx\model\SpikeActivity as SpikeActivityModel;
use app\xcx\model\SpikeGoods as SpikeGoodsModel;

class SpikeGoods extends Base
{
    public function getSpikeGoodsList($activity_id=0,$time_point=''){
//        if(!$activity_id)return $this->renderError('参数错误');
        $time_point = $time_point ? $time_point : $this->getDefaultTimePoint();

        if(!SpikeActivityModel::checkStatus($activity_id)){
            return $this->renderError('该活动已结束','',[]);
        }
        $activity_goods = SpikeGoodsModel::getActivityGoodsList($activity_id,$time_point);
//        dump($activity_goods);die;
        return $this->renderSuccess('SUCCESS','',$activity_goods);
    }


    /**
     * 获取秒杀活动时间段
     */
    public function getTimePoint(){
        $time = SpikeGoodsModel::$time;
        foreach ($time as $k => $item){
            $data[] = array(
                'time_point' => $k,
                'time' => $item . ':00',
                'state' => $this->getSpikeStatus($item),
                'startTime' => date('Y-m-d') .' '.$item . ':00:00',
                'perendTime' => date('Y-m-d H:i:s',strtotime(date('Y-m-d') .' '.$item . ':00:00') + 3600 * 2 -1),
            );
        }
        return $data;
    }

    /**
     * 获取当前时间段状态
     * author fup
     * date 2019-07-15
     */
    public function getSpikeStatus($time_point){
        $time = time();
        $start_time = strtotime(date('Y-m-d') .' '.$time_point . ':00:00');
        $end_time = strtotime(date('Y-m-d') .' '.$time_point . ':00:00') + 3600 * 2 -1;
        if($time > $start_time && $time < $end_time){
            $status = '进行中';
        }else if($time < $start_time){
            $status = '未开始';
        }else if($time > $end_time){
            $status = '已结束';
        }
        return $status ? : '';
        
    }

    /**
     * 获取当前时间段
     */
    public function getDefaultTimePoint(){
        $now = date('H')-4;
        $time = SpikeGoodsModel::$time;
        if(!in_array($now,$time)){
            $time[] = $now;
            sort($time);
            return $time[array_search($now,$time) - 1];

        }
        return $now;
    }
}