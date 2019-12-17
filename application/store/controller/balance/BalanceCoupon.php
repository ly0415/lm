<?php

namespace app\store\controller\balance;

use app\store\controller\Controller;
use app\store\model\balance\BalanceCoupon as BalanceCouponModel;
use app\api\controller\Balance as BalanceModel;
use app\store\model\Store                as StoreModel;
use app\store\model\store\StoreUser      as StoreUserModel;

/**
 *余额充值中心
 * Class GoodsCategory
 * @package app\store\controller\source
 */
class BalanceCoupon extends Controller
{
    /**
     *余额充值券 列表
     * @author ly
     * @date 2019-11-25
     */
    public function index($sn='',$is_use='',$source='',$add_time='',$end_time='',$service_user_id='',$store_id='')
    {
        $balanceCouponModel   = new BalanceCouponModel;
        $storeusermodel       = new StoreUserModel;
        $list                 = $balanceCouponModel -> getList($sn,$is_use,$source,$add_time,$end_time,$service_user_id,$store_id);
        $storelist            = StoreModel::getStoreList(TRUE, BUSINESS_ID);

        if(!empty($store_id) && $store_id != -1){
            $userlist      = $storeusermodel->storeUserList($store_id);
        }else{
            $userlist     =[];
        }
        return $this->fetch('index', compact('list','storelist','userlist'));
    }


    /**
     *添加
     * @author ly
     * @date 2019-11-26
     */
    public function add($money='',$number='')
    {
        $balanceCouponModel   = new BalanceCouponModel;
        // 新增记录
        if ($balanceCouponModel->add($money,$number)) {
            return $this->renderSuccess('添加成功', url('balance.balance_coupon/index'));
        }
        return $this->renderError($balanceCouponModel->getError() ?: '添加失败');
    }

    /**
     *指派
     * @author ly
     * @date 2019-11-26
     */
    public function designate($store_id1='',$service_user_id1='',$designate_type='',$_type='')
    {
        $balanceCouponModel   = new BalanceCouponModel;
        if ($balanceCouponModel->designate($store_id1,$service_user_id1,$designate_type,$_type)) {
            return $this->renderSuccess('指派成功', url('balance.balance_coupon/index'));
        }
        return $this->renderError($balanceCouponModel->getError() ?: '指派失败');
    }

    /**
     *数据导出
     * @author ly
     * @date 2019-11-26
     */
    public function export($sn='',$is_use='',$source='',$add_time='',$end_time='',$service_user_id='',$store_id='')
    {
        $balanceCouponModel   = new BalanceCouponModel;
        return $balanceCouponModel->exportList($sn,$is_use,$source,$add_time,$end_time,$service_user_id,$store_id);
    }

    /**
     * 余额充值券  删除 软删
     * @author ly
     * @date 2019-11-27
     */
    public function delete($id=[])
    {

        $balanceCouponModel   = new BalanceCouponModel;
        if (!$balanceCouponModel->delete($id)) {
            return $this->renderError($balanceCouponModel->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }





}
