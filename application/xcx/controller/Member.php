<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-10
 * Time: 上午 10:49
 */

namespace app\xcx\controller;


use app\xcx\model\Sign;
use app\xcx\model\User;
use app\xcx\model\UserPointLog;

class Member extends Base
{




    /**
     * desc: 签到页
     * path: signList
     * auth：fp
     * method: post
     * time: 2019-06-10
     * param: uid - {int} 用户uid
     */
    public function signList(){
        $data = [];
        $params['uid'] = input('uid/d','');
        $validate = new \app\xcx\validate\Sign();
        if(!$validate->check($params))
            return $this->renderError($validate->getError(),'',[]);
        $data['sign'] = Sign::isSign($params['uid']);
        $user = User::where(['id'=>$params['uid'],'mark'=>1])->field('id,headimgurl,nickname,point')->find();
        $data['user'] = $user;
        $list = UserPointLog::getLastSign($params['uid']);
        $data['list'] = $list;
        return $this->renderSuccess('SUCCESS','',$data);
    }
    /**
     * desc: 用户签到
     * path: userSign
     * auth：fp
     * method: post
     * time: 2019-06-10
     * param: uid - {int} 用户uid
     */
    public function userSign(){
        $uid = input('uid/d','');
        $params['uid'] = $uid;
        $validate = new \app\xcx\validate\Sign();
        if(!$validate->check($params))
            return $this->renderError($validate->getError(),'',[]);
        $sign = Sign::get(['uid'=>$uid]);
        $user = User::get(['id'=>$uid,'mark'=>1]);
        $yesterday = date('Y-m-d',strtotime('-1 days'));
        $last = date('Y-m-d',$sign['last_sign_time']);
        $today = date('Y-m-d');
        if($sign){
            if($last == $today){
                return $this->renderError('今日已签到','',[]);
            }else if($last == $yesterday){
                try {
                    $total_days = $sign['total_day'] < 7 ? $sign['total_day'] + 1 : 7;
                    $log = UserPointLog::buildData($uid, $sign->id, Sign::$point[$total_days], (int)($user->point + Sign::$point[$total_days]), '用户累计签到第' . ($sign['total_day'] + 1) . '天');
                    Sign::startTrans();
                    UserPointLog::create($log);
                    $sign->last_sign_time = time();
                    $sign->total_day = $sign['total_day'] + 1;
                    $sign->save();
                    $user->point = $user->point + Sign::$point[$total_days];
                    $user->save();
                    Sign::commit();
                    $sign['point'] = Sign::$point[$total_days];
                    $sign['total_point'] = (int)($user->point);
                    $sign['last_sign'] = UserPointLog::getLastSign($uid);
                    return $this->renderSuccess('签到成功','',$sign);
                }catch (\Exception $e){
                    Sign::rollback();
                    return $this->renderError($e->getMessage(),'',[]);
                }
            }else{
                try{
                $log = UserPointLog::buildData($uid, $sign->id, Sign::$point[1], (int)($user->point + Sign::$point[1]), '用户累计签到第' . ($sign['total_day']) . '天');
                Sign::startTrans();
                UserPointLog::create($log);
                $sign->last_sign_time = time();
                $sign->total_day = 1;
                $sign->save();
                $user->point = $user->point + Sign::$point[1];
                $user->save();
                Sign::commit();
                $sign['point'] = Sign::$point[1];
                $sign['total_point'] = (int)($user->point);
                $sign['last_sign'] = UserPointLog::getLastSign($uid);
                return $this->renderSuccess('签到成功','',$sign);
                }catch (\Exception $e){
                    Sign::rollback();
                    return $this->renderError($e->getMessage(),'',[]);
                }
            }
        }
        try{
            $data = Sign::buildData($uid);
            Sign::startTrans();
            $signs = Sign::create($data);
            $log = UserPointLog::buildData($uid,$signs->id,Sign::$point[$data['total_day']],(int)($user->point + Sign::$point[$data['total_day']]),'用户累计签到第'.$data['total_day'].'天');
            UserPointLog::create($log);
            User::where(['id'=>$uid,'mark'=>1])->setInc('point',Sign::$point[$data['total_day']]);
            Sign::commit();
            $signs['point'] = Sign::$point[$data['total_day']];
            $signs['total_point'] = (int)($user->point + Sign::$point[$data['total_day']]);
            $signs['last_sign'] = UserPointLog::getLastSign($uid);
            return $this->renderSuccess('签到成功','',$signs);
        }catch (\Exception $e){
            Sign::rollback();
            return $this->renderError($e->getMessage(),'',[]);
        }

    }

    /**
     * desc: 用户签到记录
     * path: getUserRecord
     * auth：fp
     * method: post
     * time: 2019-06-10
     * param: uid - {int} 用户uid
     */
    public function getUserRecord(){
        $params['uid'] = input('uid/d','');
        $page = input('page/d',0);
        $y = input('y/d',date('Y'));
        $m = input('m/d',date('m'));
        $validate = new \app\xcx\validate\Sign();
        if(!$validate->check($params))
            return $this->renderError($validate->getError(),'',[]);
        $list = UserPointLog::getSignList($params['uid'],$page,$y,$m);
        return $this->renderSuccess('SUCCESS','',$list);
    }
}