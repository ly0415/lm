<?php

namespace app\common\model;

use Think\Db;
use app\common\model\Distribution       as DistributionModel;

/**
 * 分销规则
 * @author  fp
 * @date    2019-09-10
 */
class FxOrder extends BaseModel{

    protected $name = 'fx_order';

    //入账状态
    public $is_on = [
        '0'     => '未入账',
        '1'     => '已入账',
    ];

    /**
     * 计算各级分销金额，并入库
     * @author  luffy
     * @date    2019-11-14
     */
    public function getFxMoney($order_sn){
        //未入账可处理
        $order_info = self::get(['order_sn'=>$order_sn]);
        if(isset($order_info) && $order_info['is_on'] == 1){    //已入账不计算
            return false;
        }
        $DistributionModel = new DistributionModel;
        //获取二级和一级分销人员
        $order_info_3 = $DistributionModel::get($order_info['fx_user_id']);
        $order_info_2 = $DistributionModel::get($order_info_3['parent_id']);
        $order_info_1 = $DistributionModel::get($order_info_2['parent_id']);
        Db::startTrans();
        try{
            $DistributionModel->allowField(true)->saveAll([
                ['id'=>$order_info['fx_user_id'],  'monery'=>($order_info['fx_commission']+$order_info_3['monery'])], //更新三级
                ['id'=>$order_info_3['parent_id'], 'monery'=>($order_info['fx_commission_2']+$order_info_2['monery'])], //更新二级
                ['id'=>$order_info_2['parent_id'], 'monery'=>($order_info['fx_commission_1']+$order_info_1['monery'])]  //更新一级
            ]);
            $this->allowField(true)->save(['is_on'=>1],['id' => $order_info['id']]);
            Db::commit();
            return true;
        }catch (\Exception $e){
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

}
