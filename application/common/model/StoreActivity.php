<?php

namespace app\common\model;

/**
 * 店铺活动
 * Class StoreActivities
 * @package app\common\model
 */
class StoreActivity extends BaseModel
{
    protected $name = 'store_activity';

    public static $activity_type = [
        1 => '活动报名'
    ];


    //活动状态
    public function getStatusAttr($value)
    {
        $status = [1 => '开启', 2 => '关闭'];
        return ['text' => $status[$value], 'value' => $value];
    }
    //开始时间
    public function getStartTimeAttr($value)
    {
        return ['text' => date('Y/m/d H:i',$value), 'value' => $value];
    }
    //结束时间
    public function getEndTimeAttr($value)
    {
        return ['text' => date('Y/m/d H:i',$value), 'value' => $value];
    }

    //主页图
    public function getThumbAttr($value){
        return ['text' => '/web/uploads/big/'.$value, 'value' => $value];
    }

    //富文本编辑器
    public function getContentAttr($value){
        return str_replace('/small/','/big/',$value);
    }

    //关联已报名
    public function storeActivityUser(){
        return $this->hasMany('StoreActivityUser','activity_id','id');
    }





    /**
     * 活动详情
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-11
     * Time: 16:42
     */
    public static function detail($id){
        return self::get($id,['storeActivityUser.user']);
    }

}
