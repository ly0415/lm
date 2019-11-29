<?php

namespace app\store\model;

use Think\Db;
use app\common\model\User as UserModel;
use app\store\model\user\BalanceLog as BalanceLogModel;
use app\common\enum\user\balanceLog\Scene as SceneEnum;
use app\common\exception\BaseException;

/**
 * 用户模型
 * Class User
 * @package app\store\model
 */
class User extends UserModel
{
    /**
     * 获取当前用户总数
     * @param null $day
     * @return int|string
     * @throws \think\Exception
     */
    public function getUserTotal($day = null)
    {
        if (!is_null($day)) {
            $startTime = strtotime($day);
            $this->where('create_time', '>=', $startTime)
                ->where('create_time', '<', $startTime + 86400);
        }
        return $this->where('is_delete', '=', '0')->count();
    }

    /**
     * 获取用户列表
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-02
     * Time: 10:33
     */
    public function getList($nickName = '', $gender = -1, $username = '', $phone = '')
    {
        // 检索条件：用户昵称
        !empty($username) && $this->where('username', 'like', "%$username%");
        // 检索条件：微信昵称
        !empty($nickName) && $this->where('nickName', 'like', "%$nickName%");
        // 检索条件：手机号
        !empty($phone) && $this->where('phone', 'like', "%$phone%");
        // 检索条件：性别
        if ($gender !== '' && $gender > -1) {
            $this->where('sex', '=', (int)$gender);
        }
        return $this->where('mark', '=', 1)
            ->order(['add_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 推荐
     * @author  luffy
     * @date    2019-10-07
     */
    public function recomend($user_id){
        $info = self::get($user_id)->toArray();
        //直属推荐我的
        if($info['phone_email']){
            $info['r1']             = self::get(['phone'=>$info['phone_email']]);
            //原始推荐我的
            $info['r2']             = $this->getOldRecomendUser($info);
        }
        //我的直属推荐，包含下属数量
        $info['r3'] = $this->field('id,username,sex,phone,point,amount,add_time')->where(['phone_email'=>$info['phone']])->order(['id' => 'desc'])
            ->paginate(30, false, ['query' => \request()->request()])
            ->each(function ($value){
                return $this->toSwitch($value);
            });
        return $info;
    }

    /**
     * 数据转换
     * @author  luffy
     * @date    2019-10-08
     */
    public function toSwitch($value){
        //获取消费订单数量
        $value['order_num'] = Db::name('order')->where(['buyer_id'=>$value['id']])->count();
        return $value;
    }

    /**
     * 递归获取原始推荐人
     * @return false|int
     */
    public function getOldRecomendUser($info){
        //直属推荐我的
        if(isset($info['phone_email']) && $info['phone_email']){
            $parent_info    = self::get(['phone'=>$info['phone_email']]);
            return $this->getOldRecomendUser($parent_info);
        }
        return $info;
    }

    /**
     * 软删除
     * @return false|int
     */
    public function setDelete()
    {
        return $this->save(['is_delete' => 1]);
    }

    /**
     * 用户充值
     * @param string $storeUserName 当前操作人用户名
     * @param $data
     * @return bool
     */
    public function recharge($storeUserName, $data)
    {
        if (!isset($data['money']) || $data['money'] === '' || $data['money'] < 0) {
            $this->error = '请输入正确的金额';
            return false;
        }
        // 判断充值方式，计算最终金额
        if ($data['mode'] === 'inc') {
            $diffMoney = $data['money'];
        } elseif ($data['mode'] === 'dec') {
            $diffMoney = -$data['money'];
        } else {
            $diffMoney = $data['money'] - $this['balance'];
        }
        // 更新记录
        $this->transaction(function () use ($storeUserName, $data, $diffMoney) {
            // 更新账户余额
            $this->setInc('balance', $diffMoney);
            // 新增余额变动记录
            BalanceLogModel::add(SceneEnum::ADMIN, [
                'user_id' => $this['user_id'],
                'money' => $diffMoney,
                'remark' => $data['remark'],
            ], [$storeUserName]);
        });
        return true;
    }

    /**
     * 新增用户
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-29
     * Time: 21:18
     */
    public function createUser($phone = null){

        // 查询用户是否已存在
        $user = self::detail(['phone' => $phone]);
        if(!$user){
            $user = $this;
            $point = (new UserPointSite)->getList();
            $coupon = Coupon::get(92);
            $data = [
                'phone' => $phone,
                'point' => $point ? $point['register_point'] : 0,
                'username' => $phone,
                'password' => md5('123456'),
                'login_type' => 'member',
                'store_id' => STORE_ID,
                'store_cate_id' => STORE_CATE,
                'resource' => 2,
            ];
            $time = time();
            $couponData = array(
                'c_id' => $coupon ? $coupon['id'] : 0,
                'remark' => '新用户注册赠卷',
                'source' => 4,
                'start_time' => $time,
                'end_time' => $coupon ? $time + 3600 * 24 * $coupon['limit_times'] : 0,
            );
            //注册日志
            $logData = array(
                'operator' => '--',
                'username' => $phone,
                'note' => '注册获得' . ($point ? $point['register_point'] : 0) . '睿积分',
                'deposit' => $point ? $point['register_point'] : 0,
                'expend' => '-',
            );
            $this->startTrans();
            try {
                // 保存用户记录
                if (!$user->allowField(true)->save($data)) {
                    throw new BaseException(['msg' => '用户注册失败']);
                }
                $this->addUserCoupon($couponData);
                $this->addUserPointLog($logData);
                $this->commit();
            } catch (\Exception $e) {
                $this->rollback();
                throw new BaseException(['msg' => $e->getMessage()]);
            }
        }
        return $user;
    }


    /**
     * 添加优惠券
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-03
     * Time: 14:39
     */
    public function addUserCoupon($data){
        return $this->userCoupon()->save($data);
    }

    /**
     * 添加积分日志
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-03
     * Time: 14:39
     */
    public function addUserPointLog($data){
        return $this->pointLog()->save($data);
    }

    /**
     * 用户编辑
     * @author  ly
     * @date    2019-11-1
     */
    public function editInfo($userid='',$data=''){

        $model=new UserModel;
        $data['modify_time']=time();
//        $data['W']=USER_ID;
        if($data['email']){
            if (!preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/', $data['email'])) {
                $this->error= '邮箱格式不正确';
                return false;
            }
            $emailone=$model->where('email',$data['email'])->select();
            if($emailone){
                foreach($emailone as $val){
                    if($val['id']!=$userid){
                        $this->error= '邮箱用户名已存在';
                        return false;
                    }
                }
            }
        }
        return $model->get($userid)->save($data);
    }


}
