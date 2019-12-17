<?php

namespace app\api\controller\user;

use app\api\controller\Controller;

use app\api\model\Order as OrderModel;
use app\api\model\OrderGoods;
use app\api\model\SpikeActivity;
use app\api\model\SpikeGoods;
use app\api\model\StoreGoodsSpecPrice;
use app\api\service\Bcode;


/**
 * 用户订单管理
 * Class Order
 * @package app\api\controller\user
 */
class Order extends Controller
{
    /* @var \app\api\model\User $user */
    private $user;

    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 订单列表
     * Created by PhpStorm.
     * Author: fup
     * $user_id --int 用户id
     * $page -- int 页码
     * $dataType -- string 订单类型
     * Date: 2019-08-15
     * Time: 09:58
     */
    public function lists($user_id = 0,$page = 1,$dataType = 'all')
    {
        if(!$user_id){
            return $this->renderError('缺少必要参数');
        }
        $model = new OrderModel();

        $list = $model->orderList($user_id, $page,$dataType);

        return $this->renderSuccess( ['langData' => $this->getOrderLangData(),'page'=>$list['page'], 'listData' => $list['list']]);
    }

    /**
     * 订单详情
     * Created by PhpStorm.
     * Author: fup
     * $order_sn -- string 订单编号
     * $user_id -- int 用户id
     * Date: 2019-08-15
     * Time: 09:56
     */
    public function detail($order_sn = 0,$user_id = 0)
    {
        if(!$order_sn || !$user_id){
            return $this->renderError('缺少必要参数');
        }
        // 订单详情
        $order = OrderModel::getUserOrderDetails($order_sn, $user_id);
        return $this->renderSuccess($order);
    }


    /**
     * 删除订单
     * Created by PhpStorm.
     * Author: fup
     * $order_sn -- string 订单编号
     * $user_id -- int 用户id
     * Date: 2019-08-15
     * Time: 10:03
     */
    public function del($order_sn = 0,$user_id = 0){
        if(!$order_sn || !$user_id){
            return $this->renderError('缺少必要参数');
        }

        $model = OrderModel::getUserOrderDetail($order_sn, $user_id);
        if($model->del()){
            return $this->renderSuccess([],'删除成功');
        }
        return $this->renderError($model->getError() ?: '删除失败');
    }

    /**
     * 取消订单
     * Created by PhpStorm.
     * Author: fup
     * $order_sn -- string 订单编号
     * $user_id -- int 用户id
     * Date: 2019-08-15
     * Time: 09:55
     */
    public function cancel($order_sn = 0,$user_id = 0)
    {
        if(!$order_sn || !$user_id){
            return $this->renderError('缺少必要参数');
        }

        $model = OrderModel::getUserOrderDetail($order_sn, $user_id);
        if ($model->cancel()) {
            return $this->renderSuccess([], '订单取消成功');
        }
        return $this->renderError($model->getError() ?: '订单取消失败');
    }


    /**
     * 获取订单核销二维码
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-17
     * Time: 14:24
     */

    public function extractQrcode($order_sn = null,$user_id = null)
    {
        if(!$order_sn || !$user_id)  return $this->renderError('缺少必要参数');
        require VENDOR_PATH.'/phpqrcode/phpqrcode.php'; //引入二维码

        $orderModel = new OrderModel();
        // 订单详情
        $order = $orderModel->getOrderDetail($order_sn,$user_id);
        // 判断是否为待核销订单
        if (!$orderModel->checkExtractOrder($order)) {
            return $this->renderError($orderModel->getError());
        }
        $params = $order['order_sn'];
        //$text, $outfile = false, $level = QR_ECLEVEL_L, $size = 3, $margin = 4
        \QRcode::png($params,false,QR_ECLEVEL_L,3,0);
    }

    /**
     * 获取订单核销条形码
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-11-18
     * Time: 17:02
     */
    public function extractBcode($order_sn = null,$user_id = null)
    {

        if(!$order_sn || !$user_id)  return $this->renderError('缺少必要参数');
        $orderModel = new OrderModel();
        $model = new Bcode();
        // 订单详情
        $order = $orderModel->getOrderDetail($order_sn,$user_id);
        // 判断是否为待核销订单
        if (!$orderModel->checkExtractOrder($order)) {
            return $this->renderError($orderModel->getError());
        }
        $model->createCode($order['order_sn']);
    }


    /**
     * 订单核销
     * @param $order_id
     * @param int $order_type
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-18
     * Time: 12:07
     */
    public function extract($order_sn = null)
    {
        if(!$order_sn){
            return $this->renderError('缺少必填参数order_sn');
        }
        $orderModel = new OrderModel();
        // 订单详情
        $order = $orderModel->getOrderDetail($order_sn);
        // 确认核销
        if ($orderModel->verificationOrder($order)) {
            return $this->renderSuccess([],'订单核销成功');
        }
        return $this->renderError($orderModel->getError() ?: '核销失败');
    }

    public function extract1($order_sn = null)
    {
        if(!$order_sn){
            return $this->renderError('缺少必填参数order_sn');
        }
        $orderModel = new OrderModel();
        // 订单详情
        $order = $orderModel->getOrderDetail($order_sn);
        // 确认核销
        if ($orderModel->verificationOrderSelf($order,2)) {
            return $this->renderSuccess([],'订单核销成功');
        }
        return $this->renderError($orderModel->getError() ?: '核销失败');
    }


    /**
     * 获取订单菜单按钮
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-12
     * Time: 15:21
     */
    public function getOrderLangData(){
        $langData = array(
            $this->langData->public->whole,
            $this->langData->project->pending_payment,
            $this->langData->project->pending_delivery,
            $this->langData->project->pending_collect_goods,
            $this->langData->project->to_be_evaluated,
            $this->langData->project->immediately_payment,
            $this->langData->project->cancel_order,
            $this->langData->public->total,
            '配送金额',
            $this->langData->project->product_spec,
            $this->langData->project->total_start,
            $this->langData->project->total_end
        );
        return $langData;
    }

    /**
     * 支付校验
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-24
     * Time: 16:02
     */
    public function pay($order_sn = null,$user_id = 0){
        $orderModel = new OrderModel();
        // 订单详情
        $order = $orderModel->getUserOrderDetails($order_sn,$user_id);
        if(!$orderModel->checkOrderStatusFromOrder($order['orderData'])){
            return $this->renderError($orderModel->getError() ? : '订单异常');
        }
        if(!$orderModel->checkGoodsStatusFromOrder($order['orderGoodsData'])){
            return $this->renderError($orderModel->getError() ? : '参数错误');
        }
        return $this->renderSuccess();

    }



}
