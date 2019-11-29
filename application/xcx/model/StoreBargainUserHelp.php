<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-17
 * Time: 下午 7:36
 */

namespace app\xcx\model;


class StoreBargainUserHelp extends Base
{
    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'add_time';

    protected $updateTime = false;

    /**
     * 帮忙砍价
     * @param int $bargainId
     * @param int $bargainUserId
     * @param int $uid
     * @return bool|object
     */
    public static function setBargainUserHelp($bargainId = 0,$bargainUserId = 0,$uid = 0){
        if(!self::isBargainUserHelpCount($bargainId,$bargainUserId,$uid) || !$bargainId || !$bargainUserId || !$uid || !StoreBargain::validBargain($bargainId) || !StoreBargainUser::isHas(['bargain_id'=>$bargainId,'uid'=>$bargainUserId,'status'=>1])) return false;
        $bargainUserTableId = StoreBargainUser::getBargainUserTableId($bargainId,$bargainUserId);//获取砍价记录id
        $priceSection = StoreBargain::getBargainMaxMinPrice($bargainId);
        //获取砍价的价格区间
//        $totalTimes = StoreBargain::getBargainTimes($bargainId);//总砍价人数
//        $alreadyTimes = StoreBargainUserHelp::getBargainUserTimes($bargainId,$bargainUserId); //已砍价人数
//        $surplusTimes = (int)bcsub($totalTimes,$alreadyTimes);
        $coverPrice = StoreBargainUser::getBargainUserDiffPrice($bargainId,$bargainUserId);//用户可以砍掉的金额
        $alreadyPrice= StoreBargainUser::getBargainUserPrice($bargainUserTableId);//用户已经砍掉的价格
        $surplusPrice = (float)bcsub($coverPrice,$alreadyPrice,2);//用户剩余要砍掉的价格
        $data['uid'] = $uid;
        $data['bargain_id'] = $bargainId;
        $data['bargain_user_id'] = $bargainUserTableId;
        $data['price'] = self::randomFloat($priceSection['bargain_min_price'],$priceSection['bargain_max_price']);
        $data['add_time'] = time();
        if($data['price'] > $surplusPrice) $data['price'] = $surplusPrice;
        $price = bcadd($alreadyPrice,$data['price'],2);
        $bargainUserData['price'] = $price;
        self::startTrans();
        try{
            StoreBargainUser::setBargainUserPrice($bargainUserTableId,$bargainUserData);
            self::create($data);
            self::commit();
            return $data;
        }catch (\Exception $e){
            self::rollback();
            return false;
        }
    }

    /**
     * 判断用户是否还可以砍价
     * @param int $bargainId
     * @param int $bargainUserUid
     * @param int $bargainUserHelpUid
     * @return bool
     */
    public static function isBargainUserHelpCount($bargainId = 0,$bargainUserUid = 0,$bargainUserHelpUid = 0){
        $bargainUserTableId = StoreBargainUser::getBargainUserTableId($bargainId,$bargainUserUid);
        $count = self::where('bargain_id',$bargainId)->where('bargain_user_id',$bargainUserTableId)->where('uid',$bargainUserHelpUid)->count();
        if(!$count) return true;
        else return false;
    }

    /**
     * 获取上一位用户砍价金额
     * @param int $bargainId
     * @return array
     */
    public static function getBargainLastPrice($bargainId = 0,$bargainUserId = 0){
        if(!$bargainId || !$bargainUserId) return [];
        $lastPrice =  self::where('bargain_id','=',$bargainId)
            ->where('bargain_user_id','=',$bargainUserId)
            ->order('id DESC')
            ->value('price');
        return $lastPrice ? $lastPrice : [];
    }

    /**
     * 获取俩个数之间的随机数
     * @param int $min
     * @param int $max
     * @return string
     */
    public static function randomFloat($min = 0,$max = 1){
        $num = $min + mt_rand() / mt_getrandmax() * ($max - $min);
        return sprintf("%.2f",$num);
    }

    /**
     * 获取已砍价次数
     * @param int $bargainId
     * @param int $bargainUserId
     * @return mixed
     */
    public static function getBargainUserTimes($bargainId,$bargainUserId){
        return self::where('bargain_id','=',$bargainId)
            ->where('bargain_user_id','=',$bargainUserId)
            ->count();
    }
    /**
     * 获取可以砍的金额
     * @param int $surplusTimes
     * @param int $surplusPrice
     * @return mixed
     */
    public static function getBargainCanPrice($surplusTimes,$surplusPrice){
        return $surplusPrice - ($surplusTimes-1) * 0.01;
    }

    /**
     * 获取砍价帮
     * @param int $bargainUserId
     * @return array
     */
    public static function getList($bargainUserId = 0,$limit = 15){
        if(!$bargainUserId) return [];
        $list = self::where('bargain_user_id',$bargainUserId)->limit($limit)->column('uid,price,add_time','id');
        if($list){
            foreach ($list as $k=>&$v){
                $v['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
                $userInfo = self::getBargainUserHelpUserInfo($v['uid']);
                $list[$k]['nickname'] = $userInfo[$v['uid']]['nickname'];
                $list[$k]['headimgurl'] = $userInfo[$v['uid']]['headimgurl'];
            }
        }
        return $list;
    }

    /**
     * 获取用的昵称和头像
     * @param int $uid
     * @return array
     */
    public static function getBargainUserHelpUserInfo($uid = 0){
        if(!$uid) return [];
        $userInfo = User::where('id',$uid)->column('nickname,headimgurl','id');
        return $userInfo;
    }

    /**
     * 获取砍价帮总人数
     * @param int $bargainId
     * @param int $bargainUserId
     * @return int|string
     */
    public static function getBargainUserHelpPeopleCount($bargainId = 0,$bargainUserId = 0){
        if(!$bargainId || !$bargainUserId) return 0;
        $bargainUserTableId = StoreBargainUser::getBargainUserTableId($bargainId,$bargainUserId);
        if($bargainUserTableId) return self::where('bargain_user_id',$bargainUserTableId)->where('bargain_id',$bargainId)->count();
        else return 0;
    }

    /**
     * 获取用户还剩余的砍价金额
     * @param int $bargainId
     * @param int $bargainUserId
     * @return float
     */
    public static function getSurplusPrice($bargainId = 0,$bargainUserId = 0){
        $bargainUserTableId = StoreBargainUser::getBargainUserTableId($bargainId,$bargainUserId);
        $coverPrice = StoreBargainUser::getBargainUserDiffPrice($bargainId,$bargainUserId);//用户可以砍掉的金额
        $alreadyPrice= StoreBargainUser::getBargainUserPrice($bargainUserTableId);//用户已经砍掉的价格
        $surplusPrice = (float)bcsub($coverPrice,$alreadyPrice,2);//用户剩余要砍掉的价格
        return $surplusPrice;
    }

    /**
     * 获取砍价进度条
     * @param int $bargainId
     * @param int $bargainUserId
     * @return string
     */
    public static function getSurplusPricePercent($bargainId = 0,$bargainUserId = 0){
        $coverPrice = StoreBargainUser::getBargainUserDiffPrice($bargainId,$bargainUserId);//用户可以砍掉的金额
        $bargainUserTableId = StoreBargainUser::getBargainUserTableId($bargainId,$bargainUserId);
        $alreadyPrice= StoreBargainUser::getBargainUserPrice($bargainUserTableId);//用户已经砍掉的价格
        if($alreadyPrice)
            return bcmul(bcdiv($alreadyPrice,$coverPrice,2),100,2);
        else
            return 100;
    }
}