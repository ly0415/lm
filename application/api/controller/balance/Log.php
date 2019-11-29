<?php

namespace app\api\controller\balance;

use app\api\controller\Controller;
use app\api\model\user\BalanceLog as BalanceLogModel;

/**
 * 余额账单明细
 * Class Log
 * @package app\api\controller\balance
 */
class Log extends Controller
{
    /**
     * 余额账单明细列表
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-24
     * Time: 13:41
     */
    public function lists($user_id = null)
    {
        $langData = array(
            '充值记录',
            '余额扣除',
            '充值规则',
            '送',
            '送',
            '获得',
            '积分抵扣比例',
            '积分',
            '微信充值'
        );
        $imgData = array(
            '/assets/phone/images/recharge.png',
            '/assets/phone/images/balancededuce.png',
            '/assets/wx/rechargeAmount/payment/images/coupon.png',
        );
        $list = (new BalanceLogModel)->getList($user_id);
        return $this->renderSuccess(['imgUrl'=>$imgData,'langData'=>$langData,'amountLogData'=>$list]);
    }

    /**
     * 删除余额历史记录
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-23
     * Time: 16:06
     */
    public function delete($id = null,$user_id = null){
        if(!$model = BalanceLogModel::get(['id'=>$id,'add_user'=>$user_id])){
            return $this->renderError('删除失败');
        }
        if($model->setDelete()){
            return $this->renderSuccess();
        }
        return $this->renderError($model->getError() ? : '删除失败');
    }

}