<?php

namespace app\api\model;

use \Think\Db;
use \Think\Config;
use app\common\model\FxOrder        as FxOrderModel;
use app\common\model\Distribution   as DistributionModel;
use app\common\model\Store          as StoreModel;
/**
 * 分销接口模型
 * @author  luffy
 * @date    2019-08-08
 */
class Distribution extends DistributionModel{

    /**
     * 我的分销---自10月1号起
     * @author  luffy
     * @date    2019-12-12
     */
    public function index($user_id){
        $info = self::get(['user_id'=>$user_id]);
        if($info){
            $result = [];
            $user_info = Db::name('user')->find($user_id);
            $result['fx_user_id']   = $info['id'];          //分销ID
            $result['real_name']    = $info['real_name'];   //分销用户名
            $result['fx_code']      = $info['fx_code'];     //分销码
            $result['header_img']   = $user_info['headimgurl'];  //微信头像
            $result['level']        = $info['level'];       //分销等级
            $result['apply_money']  = $info['monery'];      //可提现佣金
            $result['total_apply']  = Db::name('fx_outmoney_apply')->where(['fx_user_id'=>$info['id'], 'is_check'=>2])->sum('apply_money'); //累计提现
            //分销订单总量、累计金额
            $FxOrderModel           = new FxOrderModel;
            if($info['level'] == 1){
                $fie    = 'fx_commission_1';
            }elseif($info['level'] == 2){
                $fie    = 'fx_commission_2';
            }elseif($info['level'] == 3){
                $fie    = 'fx_commission';
            }
            $where                  = ['is_on'=>['neq',3],'fx_user_id'=>$info['id'],'add_time'=>['>', 1569859200]];     //刨除退款已扣除
            $result['total_order']  = $FxOrderModel->where($where)->count();
            $result['total_money']  = $FxOrderModel->where($where)->sum($fie);
            //所属会员
            $result['belone_user']  = Db::name('fx_user_account')->where(['fx_user_id'=>$info['id']])->count();
            //是否存在正在申请的提现
            $result['is_apply']     = (Db::name('fx_outmoney_apply')->where(['fx_user_id'=>$info['id'], 'is_check'=>1])->find() ? 1 : 0);
            return $result;
        }
    }

    /**
     * 提现申请---展示
     * @author  luffy
     * @date    2019-12-12
     */
    public function apply($fx_user_id){
        $info = self::get($fx_user_id);
        return ['monery'=>$info['monery'], 'bank_name'=>$info['bank_name'], 'bank_account'=>$info['bank_account']];
    }

    /**
     * 提现申请---提交
     * @author  luffy
     * @date    2019-12-12
     */
    public function applyAdd($fx_user_id, $money){
        $info = self::get($fx_user_id);
        $setting = Db::name('fx_site')->where(['store_id'=>$info['store_id'], 'mark'=>1])->find();
        //提现校验
        if($setting['is_money'] == 1 && $money < $setting['money']){
            $this->error = '满足'.$setting['money'].'元可提现';
            return false;
        }
        if($money > $info['monery']){
            $this->error = '超出可提现额度';
            return false;
        }
        //获取当前月份提现次数
        $f      = strtotime(date('Y-m-01'));
        $e      = strtotime(date('Y-m-t'));
        $num    = Db::name('fx_outmoney_apply')->where(['fx_user_id'=>$fx_user_id, 'add_time'=>['BETWEEN',[$f,$e ]]])->count();
        if($setting['is_time'] == 1 && $num > $setting['time']){
            $this->error = '超出可提现次数';
            return false;
        }
        // 开启事务
        $this->startTrans();
        try {
            Db::name('fx_outmoney_apply')->insert([
                'fx_user_id'    => $fx_user_id,
                'apply_money'   => $money,
                'bank_name'     => $info['bank_name'],
                'bank_account'  => $info['bank_account'],
                'source'        => 3,
                'is_check'      => 1,
                'add_time'      => time(),
            ]);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 提现记录---自10月1号起
     * @author  luffy
     * @date    2019-12-12
     */
    public function applyLog($fx_user_id){
        $data = Db::name('fx_outmoney_apply')
            ->field('apply_money, is_check, bank_name, bank_account, add_time')
            ->where(['fx_user_id'=>$fx_user_id])
            ->order('add_time DESC')
            ->select();
        return $data ? $data->toArray() : [];
    }

    /**
     * 获取分销列表
     * @author  luffy
     * @date    2019-08-08
     */
    public function getList($user_id, $page = 1){
        //获取分销用户ID
        $info = self::get(['user_id'=>$user_id]);
        if(empty($info)){
            return false;
        }
        $where['fx_user_id']    = $info['id'];
        $FxOrderModel           = new FxOrderModel;
        $StoreModel             = new StoreModel;
        $result = $FxOrderModel->where($where)->order(['id' => 'DESC'])
            ->page($page, Config::get('paginate.list_rows'))
            ->select()->toArray();

        if($result){
            foreach($result as $key => $value){
                //获取订单下单和支付时间
                $data = Db::name('order_'.$value['store_id'])->field('add_time')->where(['order_sn'=>$value['order_sn']])->find();
                $result[$key]['format_add_time']    = date('Y-m-d H:i:s', $data['add_time']);
                //入账状态
                $result[$key]['format_is_on']       = $FxOrderModel->is_on[$value['is_on']];
                //获取店铺名称
                $result[$key]['format_store_name']  = isset($StoreModel -> getCacheAll()[$value['store_id']]) ? $StoreModel -> getCacheAll()[$value['store_id']]['store_name'] : '';
            }
        }
        return $result;
    }

    /**
     * 获取分销人员
     * @author  luffy
     * @date    2019-10-17
     */
    public function getFxUserList($fx_user_id, $page, $phone = ''){
        //查询分销人员信息
        $result = []; $num = 0;
        $fx_info    = self::get($fx_user_id);
        $user_info  = Db::name('user')->find($fx_info['user_id']);
        if($fx_info['level'] == 3){
            $where['a.fx_user_id']  = $fx_user_id;
            !empty($phone)     && $where['b.phone'] = ['like', '%' . trim($phone) . '%'];
            $result = Db::name('fx_user_account')->alias('a')
                ->field('b.id,b.headimgurl,b.username,b.phone')
                ->join('user b', 'a.user_id = b.id')->where($where)->order(['b.id' => 'DESC'])
                ->page($page, Config::get('paginate.list_rows'))
                ->select()->toArray();
            $num    = Db::name('fx_user_account')->alias('a')->join('user b', 'a.user_id = b.id')->where($where)->count();
        } else{
            $where['parent_id']     = $fx_user_id;
            !empty($phone)  && $where['phone'] = ['like', '%' . trim($phone) . '%'];
            $result = $this->field('id,real_name,phone')
                ->where($where)->order(['add_time' => 'DESC'])
                ->page($page, Config::get('paginate.list_rows'))
                ->select()->toArray();
            $num    = $this->where($where)->count();
        }
        if($fx_info['level']==1){
            $fx_info['format_level'] = '一级分销';
        }elseif($fx_info['level']==2){
            $fx_info['format_level'] = '二级分销';
        }elseif($fx_info['level']==3){
            $fx_info['format_level'] = '三级分销';
        }

        return [
            'info'  => ['img'=> $user_info['headimgurl'], 'name'=> $fx_info['real_name'], 'format_level'=> $fx_info['format_level'], 'num'=> $num],
            'result'=> $result
        ];
    }

    /**
     * 设置三级分销人员单独优惠比例
     * @author  luffy
     * @date    2019-10-13
     */
    public function setDiscount($user_id, $fx_user_id, $discount){
        //查询分销人员信息
        $fx_info = self::get($fx_user_id);
        if($fx_info['discount'] < $discount){
            $this->error = '最大设置比例为 '.floatval($fx_info['discount']).'%';
            return false;
        }
        $info = Db::name('fx_user_account')->where(['user_id'=>$user_id, 'fx_user_id'=>$fx_user_id])->find();
        if($info['discounts'] == $discount){
            $this->error = '请输入与原优惠比例不一样的比例';
            return false;
        }
        if(!empty($info)){
            return Db::name('fx_user_account')->where('user_id', $user_id)->update(['discounts' => $discount]);        //更新
        }
    }

}
