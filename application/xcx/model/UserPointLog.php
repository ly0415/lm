<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-10
 * Time: 上午 11:34
 */

namespace app\xcx\model;


use think\Error;

class UserPointLog extends Base
{
    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'add_time';

    protected $updateTime = false;

    protected $hidden = [
        'relation_id','type','point_remaining'
    ];
    /**
     * desc: 组装签到记录
     * path: buildData
     * auth：fp
     * method: post
     * time: 2019-06-10
     * param: uid - {int} 用户uid
     */
    public static function buildData($uid,$sign_id,$point,$point_remaining,$desc){
        $data['user_id'] = $uid;
        $data['relation_id'] = $sign_id;
        $data['type'] = 30;
        $data['point'] = $point;
        $data['point_remaining'] = $point_remaining;
        $data['source'] = 3;
        $data['desc'] = $desc;
        return $data;
    }

    /**
     * desc: 签到记录
     * path: getSignList
     * auth：fp
     * time: 2019-06-10
     * param: uid - {int} 用户uid
     */
    public static function getSignList($uid,$page=0,$year='',$month=''){
        $year = $year ? $year : date('Y');
        $month = $month ? $month : date('m');
        $day = date('t',strtotime($year.$month));
        $start = strtotime($year . '-'.$month . '-01');
        $end = strtotime($year . '-' . $month . '-' . $day . ' 23:59:59');
        $list = self::where('user_id','=',$uid)
            ->where('add_time','between',[$start,$end])->paginate(1,1);
        return $list;
    }

    /**
     * desc: 最后一条签到记录
     * path: getLastSign
     * auth：fp
     * time: 2019-06-10
     * param: uid - {int} 用户uid
     */
    public static function getLastSign($uid){
        return self::where(['user_id'=>$uid])->order('id DESC')->find();
    }
}