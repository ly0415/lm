<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-17
 * Time: 下午 7:38
 */

namespace app\xcx\model;


class StoreBargainUser extends  Base
{
    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'add_time';

    protected $updateTime = false;

    /**
     * 获取砍价表ID
     * @param int $bargainId
     * @param int $bargainUserId
     * @return mixed
     */
    public static function getBargainUserTableId($bargainId = 0,$bargainUserId = 0,$status = 1){
        return self::where('bargain_id',$bargainId)->where('uid',$bargainUserId)->where('status',$status)->value('id');
    }
    /**
     * 获取记录
     * @param int $bargainId
     * @param int $bargainUserId
     * @return mixed
     */
    public static function isHas($query){
        return 0 < self::where($query)->count();
    }

    /**
     * 获取用户可以砍掉的价格
     * @param int $bargainUserId
     * @return string
     */
    public static function getBargainUserDiffPrice($bargainId = 0,$bargainUserId = 0){
        $canPrice = self::field('bargain_price,bargain_price_min')
            ->where('bargain_id','=',$bargainId)
            ->where('uid','=',$bargainUserId)
            ->where('is_del','=',0)
            ->find();
        if($canPrice)$canPrice->toArray();
        return (float)bcsub($canPrice['bargain_price'],$canPrice['bargain_price_min'],2);
    }
    /**
     * 获取用户砍掉的价格
     * @param int $bargainId
     * @param int $bargainUserId
     * @return mixed
     */
    public static function getBargainUserPrice($bargainId = 0){
        return self::field('price')
            ->where('id','=',$bargainId)
            ->value('price');
    }

    /**
     * 获取参与的ID
     * @param int $bargainId
     * @param int $uid
     * @param int $status
     * @return array|mixed
     */
    public static function setUserBargain($bargainId = 0,$uid = 0,$status = 1){
        if(!$bargainId || !$uid) return [];
        $bargainIdUserTableId = self::where('bargain_id',$bargainId)->where('uid',$uid)->where('status',$status)->value('id');
        return $bargainIdUserTableId;
    }

    /**
     * 修改砍价价格
     * @param int $bargainUserTableId
     * @param array $price
     * @return $this|bool
     */
    public static function setBargainUserPrice($bargainUserTableId = 0, $price = array()){
        if(!$bargainUserTableId) return false;
        return self::where('id',$bargainUserTableId)->update($price);
    }


    /**
     * 添加一条砍价记录
     * @param int $bargainId
     * @param int $uid
     * @return bool|object
     */
    public static function setBargain($bargainId=0,$uid = 0){
        if(!$bargainId || !$uid || !StoreBargain::validBargain($bargainId) || self::isHas(['bargain_id'=>$bargainId,'uid'=>$uid,'status'=>1]))return false;
        $data['uid'] = $uid;
        $data['bargain_id'] = $bargainId;
        $data['bargain_price_min'] = StoreBargain::where('id','=',$bargainId)->value('min_price');
        $data['bargain_price'] = StoreBargain::getGoodsPrice($bargainId);
        $data['status'] = 1;
        return self::create($data);
    }

    /**
     * 判断当前人是否已经参与砍价
     * @param int $bargainId
     * @param int $uid
     * @return bool|mixed
     */
    public static function isBargainUser($bargainId = 0,$uid = 0){
        if(!$bargainId || !$uid || !StoreBargain::validBargain($bargainId)) return false;
        return self::where('bargain_id',$bargainId)->where('uid',$uid)->value('uid');
    }

    /**
     * 获取砍价信息
     * @param int $bargainId
     * @param int $goodsId
     * @param int $uid
     * @return bool|mixed
     */
    public static function getBargainUser($bargainId=0,$goodsId=0,$uid=0){
        if(!$bargainId || !$goodsId || !$uid)return [];
        self::setWhere();
        return self::field('id,uid,goods_id,bargain_id,price')
            ->where('bargain_id','=',$bargainId)
            ->where('goods_id','=',$goodsId)
            ->where('uid','=',$uid)
            ->find();
    }
    /**
     * 查询检索
     * @param int $status
     * @param int $is_del
     * @return bool|mixed
     */
    public static function setWhere(){
        return self::where('status','=',1)
            ->where('is_del','=',0);
    }
}