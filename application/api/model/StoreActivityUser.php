<?php

namespace app\api\model;

use app\common\model\StoreActivityUser as StoreActivityUserModel;

/**
 * 店铺活动报名用户表
 * Class StoreActivitiesUser
 * @package app\api\model
 */
class StoreActivityUser extends StoreActivityUserModel
{

    /**
     * 列表
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-11
     * Time: 16:21
     */
    public function getList($activity_id){
        return $this->where('mark', '=', 1)
        ->where('activity_id','=',$activity_id)
            ->order(['create_time' => 'desc'])
            ->paginate(1, false, [
                'query' => request()->request()
            ]);
    }

    /**
     * 添加报名
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-12
     * Time: 09:14
     */
    public function add($activity_id,$user_id){
        // 添加报名
        return $this->allowField(true)->save(['activity_id'=>$activity_id,'user_id'=>$user_id]);

    }

    /**
     * 获取当前活动报名总数
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-12
     * Time: 14:02
     */
    public function getUserTotal($where = [])
    {
        !empty($where) && $this->where($where);
        return $this->count();
    }

}
