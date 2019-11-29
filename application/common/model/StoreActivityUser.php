<?php

namespace app\common\model;

/**
 * 店铺活动报名用户表
 * Class StoreActivityUser
 * @package app\common\model
 */
class StoreActivityUser extends BaseModel
{
    protected $name = 'store_activity_user';

    protected $updateTime = false;


    //关联用户表
    public function user(){
        return $this->belongsTo('User','user_id','id')
            ->field('id,username,phone');
    }

    //关联活动表
    public function storeActivity(){
        return $this->belongsTo('StoreActivity','activity_id','id');
    }

    /**
     * 详情
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-12
     * Time: 09:17
     */
    public function detail($activity_id,$user_id){
        return self::get(['activity_id'=>$activity_id,'user_id'=>$user_id]);
    }

}
