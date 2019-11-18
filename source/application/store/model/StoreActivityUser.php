<?php

namespace app\store\model;

use app\common\model\StoreActivityUser as StoreActivityUserModel;

/**
 * 规格/属性(组)模型
 * Class Tag
 * @package app\store\model
 */
class StoreActivityUser extends StoreActivityUserModel
{

    /**
     * 获取活动报名用户
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-11
     * Time: 20:28
     */
    public function getActivityUser($activity_id,$username,$phone){

        $where['a.activity_id']      = $activity_id;
        if(!empty($username)){
            $where['u.username']    = [ 'like', "%$username%"];
        }
        if(!empty($phone)){
            $where['u.phone']       = [ 'like', "%$phone%"];
        }
        return $this->alias('a')
            ->field('a.user_id,a.create_time,u.username,u.phone')
            ->join('user u','a.user_id = u.id','LEFT')
            ->where($where)
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);

    }

    
}
