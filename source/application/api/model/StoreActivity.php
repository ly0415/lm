<?php

namespace app\api\model;

use app\common\model\StoreActivity as StoreActivityModel;

/**
 * 店铺活动
 * Class StoreActivities
 * @package app\api\model
 */
class StoreActivity extends StoreActivityModel
{

    /**
     * 列表
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-11
     * Time: 16:21
     */
    public function getList($page){
        return $this
            ->where('mark', '=', 1)
            ->where('status','=',1)
            ->order(['create_time' => 'desc'])
            ->page($page,15)->select()
            ->each(function ($item){
                $item['store_name'] = \app\common\model\Store::getStoreList()[$item['store_id']]['store_name'];
                return $item;
            });
    }


    /**
     * 校验活动状态
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-12
     * Time: 09:28
     */
    public function checkActivityStatus($activity_id){
        $time = time();
        if(!$activity = self::detail($activity_id)){
            $this->error = '活动不存在';
            return false;
        }
        if($activity['status']['value'] != 1){
            $this->error = '活动未开启';
            return false;
        }
        if($activity['start_time']['value'] > $time ){
            $this->error = '活动未开始';
            return false;
        }
        if($activity['end_time']['value'] <= $time){
            $this->error = '活动已结束';
            return false;
        }
        if((new StoreActivityUser)->getUserTotal(['activity_id'=>$activity_id]) >= $activity['limit_num'] && $activity['limit_num'] > 0){
            $this->error = '报名人数已达上线';
            return false;
        }
        return true;
    }

}
