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
