<?php

namespace app\common\model;
/**
 * 秒杀活动模型
 * Class SpikeActivity
 * @package app\common\model
 */
class SpikeActivity extends BaseModel
{
    protected $name = 'spike_activity';

    protected $createTime = 'add_time';

    protected $updateTime = false;

    public $time = [1=>'08',5=>'10',10=>'12',15=>'14',20=>'16'];

    public $types = [
      [
        'id' => 1,
        'name' => '免预约'
      ],
      [
        'id' => 2,
        'name' => '过期退'
      ],
      [
          'id' => 3,
          'name' => '随时退'
      ]
    ];


    // 关联店铺用户
    public function user(){
        return $this->belongsTo('\app\store\model\store\StoreUser','create_user','id')->bind(['user_name']);
    }

    //关联秒杀活动商品表
    public function spikeGoods(){
        return $this->hasMany('SpikeGoods','spike_id','id')
            ->where('mark','=',1);
    }

    /**
     * 秒杀详情
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-15
     * Time: 16:59
     */
    public static function detail($id)
    {
        return self::get($id, 'spikeGoods');
    }

    //设置开始时间为时间戳
    public function setStartTimeAttr($val){
        return strtotime($val);
    }

    //设置结束时间为时间戳
    public function setEndTimeAttr($val){
        return strtotime($val) + 3600 * 24 -1 ;
    }

    //设置活动状态
    public function getStatusAttr($val){
        $status = [1 => '开启',2 => '关闭'];
        return ['text' => $status[$val],'value' => $val];
    }

    //设置活动特点
    public function getTypeAttr($val){
        if(!empty($val)){
            return ['text' => explode(',',$val) ,'value' => $val];
        }
        return ['text' => [] ,'value' => $val];
    }

    //时间戳转日期格式
    public function getStartTimeAttr($val){
        return ['text'=>date('Y-m-d',$val),'value'=>$val];
    }

    //时间戳转日期格式
    public function getEndTimeAttr($val){
        return ['text'=>date('Y-m-d',$val),'value'=>$val];
    }


    //关联秒杀活动商品表
    public function goodsId()
    {
        return $this->hasMany('SpikeGoods','spike_id','id')->order(['id' => 'asc']);
    }

}
