<?php

namespace app\common\model\user;

use app\common\model\BaseModel;
use app\common\enum\user\balanceLog\Scene as SceneEnum;

/**
 * 用户余额变动明细模型
 * Class BalanceLog
 * @package app\common\model\user
 */
class BalanceLog extends BaseModel
{
    protected $name = 'amount_log';
    protected $updateTime = false;
    protected $createTime = 'add_time';
    protected $dateFormat = 'Y-m-d H:i';
//余额记录表：'充值/扣除来源（1  公众号  2 小程序  3 PC 4 代客下单）',
//订单表：1、小程序 2、公众号 3、代课下单 4、PC前台下单
    public static  $source = [0,2,1,4,3];


    /**
     * 获取当前模型属性
     * @return array
     */
    public static function getAttributes()
    {
        return [
            // 充值方式
            'scene' => SceneEnum::data(),
        ];
    }

    /**
     * 关联会员记录表
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('app\common\model\User');
    }

    //关联充值表
    public function rechargePoint(){
        return $this->belongsTo('app\common\model\RechargePoint','point_rule_id','id')->where('mark','=',1);
    }

    //状态
    public function getStatusAttr($value,$data){
        if($data['type'] == 1 ||  $data['type'] == 4){
            $status = [
                1 => $data['type'] == 1 ? '待支付' : '待审核',
                2 => $data['type'] == 1 ? '已支付' : '审核成功',
                3 => $data['type'] == 1 ? '支付失败' : '审核失败',
                4 => '已赠送'
            ];
            return ['text' => $status[$value],'value' => $value];
        }
        if($data['type'] == 3){
            return ['text' => $value == 4 ? '已赠送': '','value' => $value];
        }
        return ['text' => '','value' => $value];
    }

    //类型
    public function getTypeAttr($value){
        $type = [
            1 => '微信充值',
            2 => '余额扣除',
            3 => '注册赠送',
            4 => '线下付款',
            5 => '卷码充值',
            6 => '订单退款',
            7 => '系统处理',
            20 => '余额冻结'
        ];
        return ['text' => $type[$value],'value' => $value];
    }

    /**
     * 余额变动场景
     * @param $value
     * @return array
     */
    public function getSceneAttr($value)
    {
        return ['text' => SceneEnum::data()[$value]['name'], 'value' => $value];
    }

    /**
     * 新增记录
     * @param $scene
     * @param $data
     */
    public static function add($scene, $data)
    {
        $model = new static;
        $model->save(array_merge([
            'type' => $scene,
        ], $data));
    }

}