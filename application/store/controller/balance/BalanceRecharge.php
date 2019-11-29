<?php

namespace app\store\controller\balance;

use app\store\controller\Controller;
use app\store\model\balance\BalanceRecharge as BalanceRechargeModel;
use app\api\controller\Balance as BalanceModel;

/**
 *余额充值中心
 * Class GoodsCategory
 * @package app\store\controller\source
 */
class BalanceRecharge extends Controller
{
    /**
     *余额充值日志
     * @author ly
     * @date 2019-10-30
     */
    public function index($username='',$phone='',$add_time='',$end_time='',$type='',$status='',$source='')
    {
        $balancerecharge = new BalanceRechargeModel;
        $balanceModel    = new BalanceModel;
        $list            = $balancerecharge -> getList($username,$phone,$add_time,$end_time,$type,$status,$source);
        $typelist        = $balancerecharge -> getTypeList();
        $sourcelist      = $balancerecharge -> getSourcesList();
        if(!empty($type) && $type != -1){
            $statuslist      = $balanceModel -> getBalanceTypeList($type);
        }else{
            $statuslist     =[];
        }
        return $this->fetch('index', compact('list','typelist','sourcelist','statuslist'));
    }

    /**
     *数据导出
     * @author ly
     * @date 2019-10-30
     */
    public function export($username='',$phone='',$add_time='',$end_time='',$type='',$status='',$source='')
    {
        $balancerecharge = new BalanceRechargeModel;
        return $balancerecharge->exportList($username,$phone,$add_time,$end_time,$type,$status,$source);
    }



}
