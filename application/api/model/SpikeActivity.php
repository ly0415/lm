<?php

namespace app\api\model;

use app\common\model\SpikeActivity as SpikeActivityModel;

/**
 * 秒杀活动模型
 * @author  fup
 * @date    2019-08-14
 */
class SpikeActivity extends SpikeActivityModel
{

    /**
     * 校验活动状态
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-17
     * Time: 15:20
     */
    public function checkStatus($activity_id){
        $time = time();
        if(!$activity = self::get(['id'=>$activity_id,'mark'=>1])){
            $this->error = '活动不存在';
            return false;
        }
        if($activity['status']['value'] != 1){
            $this->error = '活动未开启';
            return false;
        }
        if($activity['start_time']['value'] > $time){
            $this->error = '活动未开始';
            return false;
        }
        if($activity['end_time']['value'] < $time){
            $this->error = '活动的已结束';
            return false;
        }
        return true;
    }


}
