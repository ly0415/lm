<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-10
 * Time: 上午 10:54
 */

namespace app\xcx\model;


class Sign extends Base
{
    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'create_time';

    protected $updateTime = false;

    protected $hidden = ['create_time'];

    public static $point = [0,10,20,0,40,50,60,100];
    /**
     * desc: 组装数据
     * path: buildData
     * auth：fp
     * method: post
     * time: 2019-06-10
     * param: uid - {int} 用户uid
     */
    public static function buildData($uid){
        $data['uid'] = $uid;
        $data['last_sign_time'] = time();
        $data['total_day'] = 1;
        return $data;
    }
    /**
     * desc: 获取今日是否签到
     * path: buildData
     * auth：fp
     * method: post
     * time: 2019-06-10
     * param: uid - {int} 用户uid
     */
    public static function isSign($uid){
//        $start = mktime(0,0,0,date('m'),date('d'),date('Y'));
//        $end = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d',strtotime('-1 days'));
        $sign = self::where(['uid'=>$uid])->find();
        if($sign && date('Y-m-d',$sign['last_sign_time']) == $today){
            $sign['is_sign'] = 1;
        }else if($sign && date('Y-m-d',$sign['last_sign_time']) != $yesterday){
            $sign['total_day'] = 0;
        }
        return $sign;
    }
}