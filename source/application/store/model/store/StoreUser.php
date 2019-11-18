<?php

namespace app\store\model\store;

use think\Db;
use think\Session;
use app\store\model\Store               as StoreModel;
use app\store\model\store\UserRole      as UserRoleModel;
use app\common\model\store\StoreUser    as StoreUserModel;

/**
 * 店铺店员模型
 * Class StoreUser
 * @package app\store\model
 */
class StoreUser extends StoreUserModel
{

    /**
     * 登录
     * @author  fup
     * @date    2019-05-20
     */
    public function login($data)
    {
        // 验证用户名密码是否正确
        if (!$user = $this->getLoginUser($data['user_name'], $data['password'])) {
            $this->error = '登录失败, 用户名或密码错误';
            return false;
        }
        // 保存登录状态
        $this->loginState($user);
        return true;
    }

    /**
     * 获取登录用户信息
     * @author  fup
     * @date    2019-05-20
     */
    private function getLoginUser($user_name, $password)
    {
        return self::useGlobalScope(false)->where([
            'user_name' => $user_name,
            'password'  => md5($password),
            'mark'      => 1
        ])->find();
    }

    /**
     * 保存登录状态
     * @author  fup
     * @date    2019-05-20
     */
    public function loginState($user)
    {
        // 保存登录状态
        Session::set('yoshop_store', [
            'user' => [
                'store_user_id' => $user['id'],
                'user_name'     => $user['user_name'],
            ],
            'store_id'  => $user['store_id'],
            'is_login'  => true,
            'is_admin'  => $this->isAdmin($user['id'])
        ]);
    }

    /**
     * 获取店员信息
     * @author  luffy
     * @date    2019-08-29
     */
    public function getStoreUserInfo($store_user_id){
        $info = self::get($store_user_id);
        //关联业务类型
        $info['business_id'] = Db::name('store_user_business')->where(['store_user_id'=>$store_user_id])->value('business_id');
        return $info;
    }

    /**
     * 验证用户名是否重复
     * @author  fup
     * @date    2019-05-20
     */
    public static function checkExist($user_name)
    {
        return !!static::useGlobalScope(false)
            ->where('user_name', '=', $user_name)
            ->value('id');
    }

    /**
     * 获取店铺店员列表
     * @author  fup
     * @date    2019-05-20
     */
    public function getList($where=[])
    {
        if(BUSINESS_ID){
            $where['c.business_id'] = BUSINESS_ID;
        }
        return $this->alias('u')
            ->field('u.*,s.id as sid,s.store_type,d.id business_id,d.name business_name')
            ->join('store s','u.store_id = s.id','left')
            ->join('store_user_business c','u.id = c.store_user_id','left')
            ->join('business d','c.business_id = d.id','left')
            ->where('s.store_type','=',1)
            ->where('u.mark', '=', 1)
            ->where('store_id','=',StoreModel::getAdminStoreId())
            ->where($where)
            ->order(['u.create_time' => 'desc'])
            ->paginate(15, false, ['query' => \request()->request()])
            ->each(function ($value){
                return $this->toSwitch($value);
            });
    }

    /**
     * 数据转换
     * @author  luffy
     * @date    2019-08-27
     */
    public function toSwitch($value){
        //根据店铺店员获取所属角色
        $UserRoleModel = new UserRoleModel();
        $value['format_role_name']   = $UserRoleModel->getRoleName($value['id']);
        return $value;
    }

    /**
     * 新增记录
     * @param $data
     * @return bool|false|int
     * @throws \think\exception\DbException
     */
    public function add($data)
    {
        if (self::checkExist($data['user_name'])) {
            $this->error = '用户名已存在';
            return false;
        }
        if ($data['password'] !== $data['password_confirm']) {
            $this->error = '确认密码不正确';
            return false;
        }
        if(BUSINESS_ID){
            $data['business_id']    = BUSINESS_ID;
        }
        if(!isset($data['store_id'])){
            $data['store_id']       = STORE_ID;
        }
        if (empty($data['role_id'])) {
            $this->error = '请选择所属角色';
            return false;
        }
        $this->startTrans();
        try {
            // 新增管理员记录
            $data['password'] = yoshop_hash($data['password']);
            $data['is_super'] = 0;
            $this->allowField(true)->save($data);
            // 新增角色关系记录
            (new UserRole)->add($this['id'], $data['role_id']);
            //新增用户业务类型
            if(isset($data['business_id']) && $data['business_id']) {
                Db::name('store_user_business')->insert(['store_user_id' => $this['id'], 'business_id' => $data['business_id']]);
            }
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 更新记录
     * @param array $data
     * @return bool
     * @throws \think\exception\DbException
     */
    public function edit($data)
    {
        if ($this['user_name'] !== $data['user_name']
            && !!self::get(['user_name' => $data['user_name']])) {
            $this->error = '用户名已存在';
            return false;
        }
        if (!empty($data['password']) && ($data['password'] !== $data['password_confirm'])) {
            $this->error = '确认密码不正确';
            return false;
        }
        if (empty($data['role_id'])) {
            $this->error = '请选择所属角色';
            return false;
        }
        if (!empty($data['password'])) {
            $data['password'] = yoshop_hash($data['password']);
        } else {
            unset($data['password']);
        }

        $this->startTrans();
        try {
            // 更新管理员记录
            $this->allowField(true)->save($data);
            // 更新角色关系记录
            (new UserRole)->edit($this['id'], $data['role_id']);
            if(isset($data['business_id'])){
                // 更新用户业务类型关联表
                $business_info = Db::name('store_user_business')->where(['store_user_id'=>$this['id']])->select()->toArray();
                if($business_info){
                    Db::name('store_user_business')->where('store_user_id', $this['id'])->update(['business_id' => $data['business_id']]);    //更新
                } elseif($data['business_id']) {
                    //新增
                    Db::name('store_user_business')->insert(['store_user_id'=> $this['id'], 'business_id'=> $data['business_id']]);
                }
            }
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 软删除
     * @return false|int
     */
    public function setDelete()
    {
        if ($this['is_super']) {
            $this->error = '超级管理员不允许删除';
            return false;
        }
        // 删除对应的角色关系
        UserRole::deleteAll(['store_user_id' => $this['id']]);
        return $this->save(['mark' => 0]);
    }

    /**
     *获取店铺店员
     * @author ly
     * @date 2019-11-13.
     */
    public function storeUserList($store_id=''){
        return $this->where('store_id',$store_id)
            ->where('mark',1)
            ->select();
    }

    /**
     *统计店员下的会员
     * @author ly
     * @date 2019-11-11
     */
    public function getstoreuserList($store_id='',$storeuser_id='',$endtime='')
    {
//        echo strtotime('2019-11-12');die;
        if(IS_ADMIN){
            if($store_id){
                $store_id = $store_id;
            }else{
                $store_id = STORE_ID;
            }
        }else{
            $store_id = STORE_ID;
        }
            //获得指定天的统计
//        $endtime='2018-3';
        if($endtime){
            $storetime = $endtime;
            $storme    = explode('-',$endtime);
            if(count($storme)<2){
                $storetime = $storetime.'-01';
            }
            $days  = date('t', strtotime($endtime));
            $timestr = strtotime($endtime);
            for($i=0;$i<$days;$i++){
                $starttime      = $timestr+($i*86400);
                $endtimee       = $starttime+(86399);
                $list['data'][] = $this->getuserListAll($store_id,$storeuser_id,$starttime,$endtimee);
            }
        }else{
            //获得指定月份的统计
            $storetime = (date('Y ',time()));
            for($i=0;$i<12;$i++){
                $timemonth = (date('Y',time()).'-'.($i+1).'-01 00:00:00');
                $timestr   = strtotime(date('Y',time()).'-'.($i+1).'-01 00:00:00');
                $days      = date('t', strtotime($timemonth));
                $starttime = $timestr;
                $endtimee  = $timestr+($days*(86400)-1);
                $list['data'][] = $this->getuserListAll($store_id,$storeuser_id,$starttime,$endtimee);
//                print_r($list['data']);die;

            }
        }
        $list['endtime']      = $storetime;
        $list['count']        = count($list['data']);
        $list['store_id']     = $store_id;
        $list['storeuser_id'] = $storeuser_id;
        $list['userlist']     = $this->storeUserList($store_id);
//        print_r($list);die;
        return $list;

    }

    /**
     *统计按天、按月 的会员数
     * @author ly
     * @date 2019-11-11
     */
    public function getuserListAll($store_id='',$storeuser_id='',$starttime='',$endtimee=''){
        !empty($store_id) && $this->where('a.store_id','=',$store_id) ;
        !empty($storeuser_id) && $this->where('a.id','=',$storeuser_id);
        $list = $this->alias('a')
                     ->field('a.id as storeuserid,a.store_id,a.real_name,u.phone,u.add_time')
                     ->join('user u','a.user_id=u.id')
                     ->select();
        $item=[];
            foreach($list as $val){
                $data=Db::name('user u')->field('count(id) as count')->where('phone_email',$val['phone'])
                                        ->where('u.add_time','between',[$starttime,$endtimee])
                                        ->select();
                foreach($data as $value){
                    $item[] = $value['count'];
                }
            }
        return array_sum($item);

    }

    /**
     *获取充值额度
     * @author ly
     * @date 2019-11-15.
     */
    public function getRechargeList($store_id='',$storeuser_id='',$endtime=''){
        if(IS_ADMIN){
            if($store_id){
                $store_id = $store_id;
            }else{
                $store_id = STORE_ID;
            }
        }else{
            $store_id = STORE_ID;
        }
        $item=[];
        if($endtime){
            $storetime         = $endtime;
            $storme            = explode('-',$endtime);
            if(count($storme)  <  2){
                $storetime     = $storetime.'-01';
            }
            $days              = date('t', strtotime($endtime));
            $timestr           = strtotime($endtime);
            for($i=0;$i<$days;$i++){
                $starttime      = $timestr+($i*86400);
                $endtimee       = $starttime+(86399);
                $list['data'][] = $this->getRechargeListAll($store_id,$storeuser_id,$starttime,$endtimee);
                if($list['data']){
                    foreach($list['data'] as $val){
                        if(!empty($val['list'])){

                            foreach($val['list'] as $v){

                                $item[] = $v['money'];
                            }
                        }
//                        print_r($item);die;
                    }
                }
            }
        }else{
            //获得指定月份的统计
            $storetime = (date('Y ',time()));
            for($i = 0;$i < 12;$i++){
                $timemonth      = (date('Y',time()).'-'.($i+1).'-01 00:00:00');
                $timestr        = strtotime(date('Y',time()).'-'.($i+1).'-01 00:00:00');
                $days           = date('t', strtotime($timemonth));
                $starttime      = $timestr;
                $endtimee       = $timestr+($days*(86400)-1);
                $list['data'][] = $this->getRechargeListAll($store_id,$storeuser_id,$starttime,$endtimee);
//                print_r($list['data']);die;
                if($list['data']){
                    foreach($list['data'] as $val){
                        if(!empty($val['list'])){

                        foreach($val['list'] as $v){

                            $item[] = $v['money'];
                        }
                        }
//                        print_r($item);die;
                    }
                }

            }
        }
        $item=array_unique($item);
        $list['endtime']      = $storetime;
        $list['count']        = count($list['data']);
        $list['store_id']     = $store_id;
        $list['storeuser_id'] = $storeuser_id;
        $list['type']=$item;
        $list['userlist']     = $this->storeUserList($store_id);
//        print_r($list);die;
        return $list;


    }

    /**
     *统计按天、按月 的充值额度
     * @author ly
     * @date 2019-11-15
     */
    public function getRechargeListAll($store_id='',$storeuser_id='',$starttime='',$endtimee=''){
        !empty($store_id)     && $this->where('a.store_id','=',$store_id) ;
        !empty($storeuser_id) && $this->where('a.id','=',$storeuser_id);
        $list=$this -> alias('a')
            -> field('brc.money,brc.store_user,count(brc.money) as count')
            -> join('balance_recharge_coupon brc','brc.store_user=a.id')
            ->where('a.mark',1)
            -> where(['brc.mark'=>1, 'brc.is_use'=>2])
            ->where('brc.add_time','between',[$starttime,$endtimee])
            -> group('brc.money')
            -> select();
        if($list) $list = $list->toArray();
        $total          = 0;
        $data           = [];
        if(!empty($list)){
            foreach($list as &$value){
                $total                        += $value['money']*$value['count'];
                $value['total']                = ($value['money']*$value['count']);
                $data['list'][$value['money']] = $value;
            }
            unset($value);
            $data['total']                   = $total;
        }
//        print_r($data);die;
        return $data;

    }
}
