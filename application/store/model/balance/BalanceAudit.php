<?php

namespace app\store\model\balance;

use think\db;
use app\store\model\balance\BalanceRecharge as BalanceRechargeModel;
use app\common\model\AmountLog   as AmountLogModel;

/**
 *
 * Class UserComment
 * @package app\store\model
 */
class BalanceAudit extends AmountLogModel
{

    /**
     *余额线下充值审核 列表
     * @author ly
     * @date 2019-11-25
     */
    public function getList($username='',$phone='',$source='')
    {
        $balanceRechargeModel=  new BalanceRechargeModel;
        !empty($username) && $this->where('u.username','like', "%$username%");
        !empty($phone) && $this->where('u.phone','like', "%$phone%");
        if (isset($source) && !empty($source) && $source != '-1') {
            $this->where('g.source','=',$source);
        }
        $list['type']   = !empty($type)?$type:'';
        $list['status'] = !empty($status)?$status:'';
        $list['source'] = !empty($source)?$source:'';
        $list['data']=$this
                ->alias('g')
                ->field('ac.account_name,re.c_money as re_c_money,re.s_money as re_s_money,re.integral as re_integral,re.percent as re_percent,u.username,u.phone,g.id,g.order_sn,g.type,g.status,g.c_money,g.old_money,g.new_money,g.point_rule_id,g.source,g.add_user,g.add_time,g.check_user,g.pay_time,g.transaction_id,g.mark')
                ->join('user u','u.id=g.add_user','LEFT')
                ->join('account ac','ac.id = g.check_user','LEFT')
                ->join('recharge_point re','re.id=g.point_rule_id','LEFT')
                ->where('g.type',4)
                ->where('g.mark',1)
                ->order('g.status','ASC')
                ->order('g.add_time','DESC')
                ->paginate(15, false, ['query' => \request()->request()])
                ->each(function($item)use($balanceRechargeModel){
                    $balanceRechargeModel->getFormData($item);
                });
        return $list;
    }


    /**
     *余额线下充值审核 通过
     * @author ly
     * @date 2019-10-30
     */
    public function passOrNoAudit($id='',$passorno='')
    {
        $this->startTrans();
        try {
            if($passorno==2){
                $data['status']=2;
            }else{
                $data['status']=3;
            }

            $this->where('id',$id)->update($data);
            $this->commit();
            return $res['code'] = 1;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }


    }




}
