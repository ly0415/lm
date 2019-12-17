<?php

namespace app\store\controller\balance;

use app\store\controller\Controller;
use app\store\model\balance\BalanceAudit as BalanceAuditModel;
use app\store\model\balance\BalanceRecharge as BalanceRechargeModel;
use app\api\controller\Balance as BalanceModel;

/**
 *余额充值中心
 * Class GoodsCategory
 * @package app\store\controller\source
 */
class BalanceAudit extends Controller
{
    /**
     *余额线下充值审核 列表
     * @author ly
     * @date 2019-11-25
     */
    public function index($username='',$phone='',$source='')
    {
        $balancerecharge   = new BalanceRechargeModel;
        $balanceAuditModel = new BalanceAuditModel;
        $sourcelist        = $balancerecharge -> getSourcesList();
        $list              = $balanceAuditModel -> getList($username,$phone,$source);
//        print_r($list);die;
        return $this->fetch('index', compact('list','sourcelist'));
    }

    /**
     *余额线下充值审核 是否通过
     * @author ly
     * @date 2019-11-25
     */
    public function passOrNoAudit($id='',$passorno=''){
        $balanceAuditModel = new BalanceAuditModel;
        if ($balanceAuditModel->passOrNoAudit($id,$passorno)) {
            return $this->renderSuccess('操作成功', url('balance.balance_audit/index'));
        }
        return $this->renderError($balanceAuditModel->getError() ?: '操作失败');

    }




}
