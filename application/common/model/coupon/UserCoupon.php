<?php

namespace app\common\model\coupon;

use think\Db;
use app\store\model\Goods as GoodsModel;
use app\common\model\Store as StoreModel;
use app\common\model\BaseModel;

/**
 * 用户优惠券模型
 * Class UserCoupon
 * @package app\common\model
 */
class UserCoupon extends BaseModel
{

    protected $name = 'user_coupon';

    protected $createTime = 'add_time';

    protected $updateTime = false;

    /**
     * 获取购物车商品对应原始商品ID
     * @author  luffy
     * @date    2019-09-10
     */
    public function getGoodsAll($good_ids){
        $arr = [];
        foreach(explode(',', $good_ids) as $value){
            //查询goods_id
            $info = Db::name('cart')->alias('a')->field('b.goods_id,a.goods_num')->join('store_goods b','b.id = a.goods_id')->where(['a.id'=>$value])->select();
            $arr['a'][] = $info[0]['goods_id'];
            $arr['b'][] = $info[0]['goods_num'];
        }
        return $arr;
    }

    /**
     * 获取优惠券列表
     * @author  luffy
     * @date    2019-09-03
     */
    public function getCouponAll($user_id = 0, $store_id = 0, $money = 0, $good_ids = []){
        $time = time();

        //得到当前用户拥有的在使用时间范围内的电子券
        $data = $this->alias('a')
            ->field('a.id user_coupon_id,a.start_time,a.end_time,b.id coupon_id,b.money,b.type,b.store_id,b.discount,b.total_times,b.day_times,b.recharge_id,b.room_type_id,b.goods_id')
            ->join('coupon b','a.c_id = b.id')
            ->where([
                'a.user_id'     =>  $user_id,
                'a.start_time'  =>  ['<', $time],
                'a.end_time'    =>  ['>', $time],
            ])
            ->order('a.id DESC')
            ->select()->toArray();

        $arr = [];
        //判断处理
        if(!empty($data)){
            $useCount1 = $useCount2 = 0;
            foreach($data as $key => $value){
                $GoodsModel = new GoodsModel;
                //获取该券已经使用总次数
                $useCount1  = Db::name('coupon_log')->where(['user_coupon_id'=>$value['user_coupon_id']])->count();
                if($value['type'] == 1){    //抵扣券
                    $arr['a'][$key] = $value;
                    if($value['total_times'] = 0 || $useCount1 >= $value['total_times']){   //未设置总次数或者使用次数大于等于总次数
                        $arr['a'][$key]['no_use_tips'] = '该券总计可使用'.$value['total_times'].'次';
                        continue;
                    }
                    //获取该券当天使用次数
                    $useCount2 = Db::name('coupon_log')->whereTime('add_time', 'today')->where(['user_coupon_id'=>$value['user_coupon_id']])->count();
                    if($useCount2 >= $value['day_times']){   //当日次数超过上限
                        $arr['a'][$key]['no_use_tips'] = '该券每天可使用'.$value['day_times'].'次';
                        continue;
                    }
                    $store_arr = ($value['store_id'] ? explode(',', $value['store_id']) : 0);
                    if($store_arr && $store_id && !in_array($store_id, $store_arr)){   //判断当前店铺是否适用
                        //获取店铺名称
                        $StoreModel = new StoreModel;
                        $store_info = $StoreModel->field('store_name')->find($store_id);
                        $arr['a'][$key]['no_use_tips'] = '该券当前店铺：'.$store_info['store_name'].'不可使用';
                        continue;
                    }
                    if( $value['money'] > $money ){   //金额是否满足
                        $arr['a'][$key]['no_use_tips'] = '该券满'.$value['money'].'元即可使用';
                        continue;
                    }
                    if($value['goods_id'] && is_array($good_ids['a']) && !in_array($value['goods_id'], $good_ids['a'])){              //指定的单一商品
                        //获取商品名称
                        $goods_info = $GoodsModel->field('goods_name')->find($value['goods_id']);
                        $arr['a'][$key]['no_use_tips'] = '该券指定商品：'.$goods_info['goods_name'].' 可使用';
                        continue;
                    }
                    $arr['a'][$key] = $value;
                }elseif($value['type'] == 2){   //兑换券
                    $arr['b'][$key] = $value;
                    if($useCount1 > 0){     //已使用
                        $arr['b'][$key]['no_use_tips'] = '该兑换券已经使用';
                        continue;
                    }
                    if(isset($value['goods_id']) && count($good_ids['a']) > 1){
                        $arr['b'][$key]['no_use_tips'] = '选购单一商品下单可兑换使用';
                        continue;
                    }
                    if(count($good_ids['a']) == 1){
                        if($good_ids['b'][0] > 1){
                            $arr['b'][$key]['no_use_tips'] = '选购单个数量商品下单时可兑换使用';
                            continue;
                        }
                    }
                    $goods_info = $GoodsModel->field('goods_name,room_id')->find($good_ids['a'][0]);
                    if(isset($value['goods_id']) && $value['goods_id'] >  0 && $value['goods_id'] != $good_ids['a'][0]){                    //指定商品
                        $arr['b'][$key]['no_use_tips'] = '该券指定商品：'.$goods_info['goods_name'].' 可兑换';
                        continue;
                    }
                    if( $value['money'] > $money ){   //金额是否满足
                        $arr['b'][$key]['no_use_tips'] = '该券满'.$value['money'].'元即可兑换';
                        continue;
                    }
                    if( isset($value['room_type_id']) && $value['room_type_id'] !=  $goods_info['room_id'] ){   //业务类型一致
                        //获取业务类型名称
                        $business_info = Db::name('room_type')->field('room_name')->find($value['room_type_id']);
                        $arr['b'][$key]['no_use_tips'] = '该券指定业务类型 '.$business_info['room_name'].' 可兑换';
                        continue;
                    }
                    //判断
                    $arr['b'][$key] = $value;
                }
            }
            return $arr;
        }
        return false;
    }

    /**
     * 关联用户表
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('User','user_id','id')->field('id,username,phone')->where(['mark'=>1]);
    }

    /**
     * 关联卷码
     * @return \think\model\relation\BelongsTo
     */
    public function coupon()
    {
        return $this->belongsTo('Coupon','c_id','id');
    }

    /**
     * 卷码使用记录
     * @return \think\model\relation\BelongsTo
     */
    public function log()
    {
        return $this->belongsTo('CouponLog','id','user_coupon_id');
    }



    /**
     * 有效期-开始时间
     * @param $value
     * @return mixed
     */
    public function getStartTimeAttr($value)
    {
        return ['text' => date('Y-m-d', $value), 'value' => $value];
    }

    /**
     * 有效期-开始时间
     * @param $value
     * @return mixed
     */
    public function getAddTimeAttr($value)
    {
        return ['text' => date('Y-m-d', $value), 'value' => $value];
    }

    /**
     * 有效期-结束时间
     * @param $value
     * @return mixed
     */
    public function getEndTimeAttr($value)
    {
        return ['text' => date('Y-m-d', $value), 'value' => $value];
    }

    /**
     * 优惠券详情
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-05
     * Time: 12:07
     */
    public static function detail($coupon_id)
    {
        return self::get($coupon_id,'coupon');
    }

}