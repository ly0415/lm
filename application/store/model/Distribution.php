<?php

namespace app\store\model;

use app\common\model\Distribution   as DistributionModel;
use app\common\model\FxUserAccount  as FxUserAccountModel;
use app\common\model\FxOrder        as FxOrderModel;
use app\common\model\Store          as StoreModel;
use app\common\model\FxDiscountLog  as FxDiscountLogModel;
use think\Db;

/**
 * 分销管理模型
 * @author  luffy
 * @date    2019-08-15
 */
class Distribution extends DistributionModel{

    /**
     * 分销列表
     * @author  luffy
     * @date    2019-08-15
     */
    public function getList(){
        $where['a.mark'] = 1;
        if(T_BUSINESS){
            $where['a.store_id']    = ['in', STORE_IDS];
        }elseif(T_GENERAL){
            $where['a.store_id']    = STORE_ID;
        }
        $result = $this->alias('a')
            ->field('a.id,a.parent_id,a.user_id,a.real_name,a.phone,a.fx_code,a.bank_name,a.bank_account,a.store_id,a.discount,a.status,a.add_time,d.username,d.phone')
            ->join('user d', 'd.id = a.user_id')
            ->where($where)
            ->order('a.id DESC')
            ->select()->toArray();

        if(!empty($result)){
            $a = $b = $res = [];
            //获取初始ID
            foreach($result as $value){
                $a[]    = $value['id'];
                $res[$value['id']] = $value;
            }
            $_result    = $res;
            if(!T_GENERAL){
                $res =  $this->formatTreeData($res);
            }
            foreach($res as $key => $value){
                $res[$key] = $this->toSwitch($value);
            }
            foreach($res as $key => $value){
                $b[]    = $value['id'];
            }
            //差集
            $diff = array_diff($a,$b);
            if($diff){
                foreach($diff as $val){
                    $res = array_merge($res, [$this->toSwitch($_result[$val])]);
                }
            }
            return $res;
        } else {
            return false;
        }
    }

    /**
     * 更新分销人员
     * @author  luffy
     * @date    2019-09-24
     */
    public function edit($data){
        if (empty($data['store_id'])) {
            $this->error = '请选择所属门店';
            return false;
        }
        if (isset($data['discount']) && is_numeric($data['discount']) === false) {
            $this->error = '请输入下单优惠比例';
            return false;
        }

        $this->startTrans();
        try {
            if(isset($data['discount'])){
                //不一样则更新生成记录
                if($data['discount'] == $this->discount){
                    unset($data['discount']);
                } else {
                    //生成分销变更记录
                    (new FxDiscountLogModel)->addLog([
                        'fx_user_id' => $this->id,
                        'new_discount' => $data['discount'],
                        'discount' => $this->discount,
                        'rule_id' => $this->rule_id
                    ]);
                }
            }
            $this->allowField(true)->save($data);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 分销列表-带分页
     * @author  luffy
     * @date    2019-09-21
     */
    public function getPageList($where = []){
        $where['mark'] = 1;
        return $this->field('id,real_name,fx_code')
            ->where($where)
            ->order('id DESC')
            ->paginate(8, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 获取分销人员所属会员列表
     * @author  luffy
     * @date    2019-08-20
     */
    public function getFxOrderList($id, $is_refund, $is_on, $order_sn, $start_time, $end_time){
        //判断分销人员信息
        $where      = [];
        $info       = self::get($id);
        if($info['level'] == 3){
            $where['a.fx_user_id']      = $id;
        } elseif($info['level'] == 2) {
            //获取二级分销人员下属所有三级分销人员
            $ids                        = $this->where(['parent_id'=>$id])->column('id');
            $where['a.fx_user_id']      = !empty($ids) ? ['in', implode(',', $ids)] : '-1';
        } elseif($info['level'] == 1) {

        }
        if( $is_on != null ){         //入账状态
            $where['a.is_on'] = $is_on;
        }
        if(!empty($start_time) || !empty($end_time)){            //付款时间
            $where = timeCond($where,'a.add_time',$start_time, $end_time);
        }
        if(!empty($order_sn)){        //订单号
            $where['a.order_sn'] = [ 'like', "%$order_sn%"];
        }
        $FxOrderModel   = new FxOrderModel;
        $StoreModel     = new StoreModel;
        $result         = $FxOrderModel->alias('a')
            ->field('a.order_sn,a.pay_money,a.fx_discount,a.fx_money,a.fx_commission_percent,a.fx_commission,a.fx_commission_2,a.fx_commission_1,a.is_on,a.rule_id,a.store_id,a.add_time,b.lev1_prop,b.lev2_prop,b.lev3_prop')
            ->join('fx_rule b','a.rule_id = b.id')
            ->where($where)->order(['a.id' => 'DESC'])
            ->paginate(15, false, ['query' => \request()->request()])
            ->each(function ($value) use ($FxOrderModel, $StoreModel, $info){
                if($info['level'] == 1){
                    $value['fx_commission_percent'] = $value['lev1_prop'];
                    $value['fx_commission']         = number_format(($value['pay_money'] * $value['lev1_prop'] / 100), 2, '.', '');
                }elseif($info['level'] == 2){
                    $value['fx_commission_percent'] = $value['lev2_prop'];
                    $value['fx_commission']         = number_format(($value['pay_money'] * $value['lev2_prop'] / 100), 2, '.', '');
                }
                return $this->toFxSwitch($value, $FxOrderModel->is_on, $StoreModel);
            });
        //得到分销人员信息
        $result->fx_user    = $info;
        //获取分销人员佣金数据
        $result->fx_money   = $this->getFxMoneyAll($FxOrderModel, $where, $info);
        return $result;
    }

    /**
     * 数据转换
     * @author  luffy
     * @date    2019-09-18
     */
    public function toFxSwitch($value, $is_on, $StoreModel){
        //获取订单下单和支付时间
        $data = Db::name('order_'.$value['store_id'])->alias('a')
            ->field('a.order_state,a.add_time,b.payment_time')
            ->join('order_relation_'.$value['store_id'].' b', 'a.order_sn = b.order_sn')
            ->where(['a.order_sn'=>$value['order_sn']])->find();
        $value['format_add_time']       = date('Y-m-d H:i:s', $data['add_time']);
        $value['format_payment_time']   = date('Y-m-d H:i:s', $data['payment_time']);
        //入账状态
        $value['format_is_on']          = $is_on[$value['is_on']];
        //获取店铺名称
        $value['format_store_name']     = isset($StoreModel -> getCacheAll()[$value['store_id']]) ? $StoreModel -> getCacheAll()[$value['store_id']]['store_name'] : '';

        //退款标记
        $value['tick']                  = 0;
        if($data['order_state'] == 60){
            $value['tick']              = '退款审核中';
        }elseif($data['order_state'] == 70){
            $value['tick']              = '已退款';
        }
        return $value;
    }

    /**
     * 获取分销人员佣金数据
     * @author  luffy
     * @date    2019-09-18
     */
    public function getFxMoneyAll($FxOrderModel, $where, $info){
        $field          = 'fx_commission';
        $fx_user_id     = $info['id'];
        if($info['level'] == 1){
            $field      = 'fx_commission_1';
        }elseif($info['level'] == 2){
            $fx_user_id = $where['a.fx_user_id'];
            $field      = 'fx_commission_2';
        }

        //未入账佣金
        $total_1 = $fx_user_id ? $FxOrderModel->where(['fx_user_id'=>$fx_user_id, 'is_on'=>0])->sum($field) : 0;

        //搜索佣金总计
        return [
            'w' => $total_1,
            'y' => Db::name('fx_outmoney_apply')->where(['fx_user_id'=>$fx_user_id, 'is_check'=>2])->sum('apply_money'),   //已提现佣金----审核成功为准
            's' => (count($where) > 1 ? $FxOrderModel->alias('a')->where($where)->sum($field) : 0),      //搜索佣金总计
        ];
    }

    /**
     * 获取分销人员所属会员列表
     * @author  luffy
     * @date    2019-08-20
     */
    public function getOwnUserList($fx_user_id, $username, $phone){
        if(empty($fx_user_id)){
            return false;
        }
        $where['a.fx_user_id']      = $fx_user_id;
        if(!empty($username)){
            $where['b.username']    = [ 'like', "%$username%"];
        }
        if(!empty($phone)){
            $where['b.phone']       = [ 'like', "%$phone%"];
        }
        $fxUserInfo = self::get($fx_user_id);
        $result = Db::name('fx_user_account')->alias('a')
            ->field('a.id,a.discounts,a.user_id,b.username,b.phone,b.add_time')
            ->join('user b', 'a.user_id = b.id')
            ->where($where)
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
        $fxUserInfo->ownUser = $result;
        return  $fxUserInfo;
    }

    /**
     * 所属会员转移
     * @author  luffy
     * @date    2019-08-21
     */
    public function setOwnUser($fx_code, $old_fx_code, $user_id){
        $fxUserInfo = self::get(['fx_code'=>$fx_code,'mark'=>1]);
        if($fx_code == $old_fx_code){
            $fxUserInfo = (object)[];
            $fxUserInfo->errorMsg = '分销码不能为当前分销人员！';
            return $fxUserInfo;
        }elseif(empty($fxUserInfo)){
            $fxUserInfo = (object)[];
            $fxUserInfo->errorMsg = '分销码有误！';
            return $fxUserInfo;
        }elseif($fxUserInfo['status'] == 2){
            $fxUserInfo->errorMsg = '该分销人员账号已被冻结！';
            return $fxUserInfo;
        }elseif($fxUserInfo['is_check'] != 2){
            $fxUserInfo->errorMsg = '该分销人员账号非审核通过！';
            return $fxUserInfo;
        }else{
            //变更
            $this->startTrans();
            try{
                $FxUserAccountModel = new FxUserAccountModel;
                if($user_id == 0){
                    //获取当前分销人员下所有会员
                    $oldFxUserInfo  = self::get(['fx_code'=>$old_fx_code,'mark'=>1]);
                    if($oldFxUserInfo['level'] != 3){
                        $fxUserInfo->errorMsg = '该分销人员非三级分销人员！';
                        return $fxUserInfo;
                    }
                    $fxUserArr      = $FxUserAccountModel->where(['fx_user_id'=>$oldFxUserInfo['id']])->column('user_id');
                    if(empty($fxUserArr)){
                        $fxUserInfo->errorMsg = '该分销人员下没有会员！';
                        return $fxUserInfo;
                    }
                    $user_id        = implode(',', $fxUserArr);
                }
                $FxUserAccountModel->where(['user_id'=>['in', $user_id]])->update(['fx_user_id'=>$fxUserInfo['id']]);
                Db::name('fx_user_change_log')->insert([
                    'type'          => 1,
                    'user_arr'      => $user_id,
                    'old_fx_code'   => $old_fx_code,
                    'new_fx_code'   => $fx_code,
                    'create_user'   => USER_ID,
                    'create_time'   => time(),
                ]);
                $this->commit();
                return true;
            }catch (\Exception $e){
                $this->rollback();
                return false;
            }
        }
    }

    /**
     * 数据转换
     * @author  luffy
     * @date    2019-08-20
     */
    public function toSwitch($value){
        $value['format_status']         = isset($value['status'])   ? $this->status[$value['status']] : '';
        if(isset($value['add_time']))   $value['format_add_time']   = date('Y-m-d H:i:s', $value['add_time']);
        $value['format_discount']       = ($value['discount'] > 0)  ? floatval($value['discount']).'%' : '---';
        if(isset($value['store_id']) && $value['store_id']){
            $value['format_store_name'] = (new StoreModel)->getCacheAll()[$value['store_id']]['store_name'];
        }
        return $value;
    }

    /**
     * 获取分销人员列表
     * @author  luffy
     * @date    2019-08-20
     */
    private function formatTreeData(&$all, $parent_id = 0, $deep = 1){
        static $tempTreeArr = [];
        foreach ($all as $key => $val) {
            if ($val['parent_id'] == $parent_id) {
                // 记录深度
                $val['deep'] = $deep;
                // 根据角色深度处理名称前缀
                $val['real_name'] = $this->htmlPrefix($deep) . $val['real_name'];
                $tempTreeArr[] = $val;
                $this->formatTreeData($all, $val['id'], $deep + 1);
            }
        }
        return $tempTreeArr;
    }

    /**
     * 分销名称 html格式前缀
     * @author  luffy
     * @date    2019-08-20
     */
    private function htmlPrefix($deep){
        // 根据角色深度处理名称前缀
        $prefix = '';
        if ($deep > 1) {
            for ($i = 1; $i <= $deep - 1; $i++) {
                $prefix .= '&nbsp;&nbsp;&nbsp;├ ';
            }
            $prefix .= '&nbsp;';
        }
        return $prefix;
    }
}
