<?php

namespace app\common\service;

use app\common\model\User as UserModel;
use app\common\model\Setting as SettingModel;
use app\common\model\wxapp\Formid as FormidModel;
use app\common\model\WxappPrepayId as WxappPrepayIdModel;
use app\common\model\dealer\Setting as DealerSettingModel;
use app\common\model\sharing\Setting as SharingSettingModel;
use app\common\enum\OrderType as OrderTypeEnum;
use app\common\library\wechat\WxTplMsg;
use app\common\library\sms\Driver as SmsDriver;

/**
 * 消息通知服务
 * Class Message
 * @package app\common\service
 */
class Message
{
    /**
     * 订单支付成功后通知
     * @param \think\Model $order
     * @param int $orderType 订单类型 (10商城订单 20拼团订单)
     * @return bool
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function payment($order, $orderType = OrderTypeEnum::MASTER)
    {
        // 1. 微信模板消息
        $template = SettingModel::getItem('tplMsg', $order['wxapp_id'])['payment'];
        if ($template['is_enable'] && !empty($template['template_id'])) {
            // 获取 prepay_id
            $prepayId = $this->getPrepayId($order['order_id'], $orderType);
            // 页面链接
            $urls = [
                OrderTypeEnum::MASTER => 'pages/order/detail',
                OrderTypeEnum::SHARING => 'pages/sharing/order/detail/detail',
            ];
            // 发送模板消息
            $status = $this->sendTemplateMessage($order['wxapp_id'], [
                'touser' => $order['user']['open_id'],
                'template_id' => $template['template_id'],
                'page' => $urls[$orderType] . '?order_id=' . $order['order_id'],
                'form_id' => $prepayId['prepay_id'],
                'data' => [
                    // 订单编号
                    'keyword1' => $order['order_no'],
                    // 支付时间
                    'keyword2' => date('Y-m-d H:i:s', $order['pay_time']),
                    // 订单金额
                    'keyword3' => $order['pay_price'],
                    // 商品名称
                    'keyword4' => $this->formatGoodsName($order['goods']),
                ]
            ]);
            // 标记已使用次数
            $status === true && $prepayId->updateUsedTimes();
        }
        // 2. 商家短信通知
        $smsConfig = SettingModel::getItem('sms', $order['wxapp_id']);
        $SmsDriver = new SmsDriver($smsConfig);
        return $SmsDriver->sendSms('order_pay', ['order_no' => $order['order_no']]);
    }

    /**
     * 后台核销接单通知
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-11-26
     * Time: 16:30
     */
    public function take($order, $orderType = OrderTypeEnum::MASTER)
    {
        $order['prepay_id'] = 'wx0314480888444580c5a7793d1357859800';
        // 微信模板消息
        $template = [
            20 => 'IYZNOJ87ircX-gO-kbLPXV-qCmMZ8ny-hvUBSZffn7A',
            25 => 'Yudxhzquai1NJ2lb-2cooslyFU_uuOpX5-p7AlTxubE'
        ];

        // 页面链接
        $urls = [
            OrderTypeEnum::MASTER => 'pages/MyAllOrder/MyAllOrder',
        ];
        // 发送模板消息
        $status = $this->sendTemplateMessage([
            'touser' => $order['xcx_openid'],
            'template_id' => $template[$order['order_state']],
            'page' => $urls[$orderType] . '?code='.($order['order_state'] == 20 ? 'delivery' : 'comment'),
            'form_id' => $order['prepay_id'],
            'data' => [
                // 商品名称
                'keyword1' => $order['goods'][0]['goods_name'],
                // 门店信息
                'keyword2' => $order['format_store_name'],
                // 订单号
                'keyword3' => $order['order_sn'],
                // 提货时间
                'keyword4' => $order['order_state'] == 20 ? $order['order_amount'] : $order['format_sendout_time'],
                // 备注
                'keyword5' => $order['order_state'] == 20 ? '您好，商家已成功接单。' : '您好，您已提货成功。',
            ]
        ]);
        return $status;
    }



    /**
     * 后台发货通知
     * @param \think\Model $order
     * @param int $orderType 订单类型 (10商城订单 20拼团订单)
     * @return bool
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function delivery($order, $orderType = OrderTypeEnum::MASTER)
    {
        // 获取 prepay_id
//        $prepayId = $this->getPrepayId($order['order_id'], $orderType);
//        // 微信模板消息
//        $template = SettingModel::getItem('tplMsg', $order['wxapp_id'])['delivery'];
//        if (!$template['is_enable'] || empty($template['template_id'])) {
//            return false;
//        }
        // 页面链接
        $urls = [
            OrderTypeEnum::MASTER => 'pages/MyAllOrder/MyAllOrder',
            OrderTypeEnum::SHARING => 'pages/sharing/order/detail/detail',
        ];
        // 发送模板消息
        $status = $this->sendTemplateMessage(10001, [
            'touser' => 'oTh1H4yjZ2piyimtAAfMtpmJCX28',
            'template_id' => 'Yudxhzquai1NJ2lb-2cooslyFU_uuOpX5-p7AlTxubE',
            'page' => $urls[$orderType] ,
            'form_id' => "wx2615552398216679406fb5481135964000",
            'data' => [
                // 商品名称
                'keyword1' => '商品名称',
                // 门店信息
                'keyword2' => '测试中华门',
                // 订单号
                'keyword3' => 20191126012346,
                // 提货时间
                'keyword4' => '2019-11-26',
                // 物流公司
                'keyword5' => '测试备注',
            ]
        ]);
        return $status;
    }

    /**
     * 后台售后单状态通知
     * @param \think\Model $refund
     * @param $order_no
     * @param int $orderType 订单类型 (10商城订单 20拼团订单)
     * @return bool
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function refund($refund, $order_no, $orderType = OrderTypeEnum::MASTER)
    {
        // 获取formid
        if (!$formId = FormidModel::getAvailable($refund['user_id'])) {
            return false;
        }
        // 微信模板消息
        $template = SettingModel::getItem('tplMsg', $refund['wxapp_id'])['refund'];
        if (!$template['is_enable'] || empty($template['template_id'])) {
            return false;
        }
        // 页面链接
        $urls = [
            OrderTypeEnum::MASTER => 'pages/order/refund/index',
            OrderTypeEnum::SHARING => 'pages/sharing/order/refund/index',
        ];
        // 发送模板消息
        $status = $this->sendTemplateMessage($refund['wxapp_id'], [
            'touser' => $refund['user']['open_id'],
            'template_id' => $template['template_id'],
            'page' => $urls[$orderType],
            'form_id' => $formId['form_id'],
            'data' => [
                // 售后类型
                'keyword1' => $refund['type']['text'],
                // 状态
                'keyword2' => $refund['status']['text'],
                // 订单号
                'keyword3' => $order_no,
                // 商品名称
                'keyword4' => $refund['order_goods']['goods_name'],
                // 申请时间
                'keyword5' => $refund['create_time'],
                // 申请原因
                'keyword6' => $refund['apply_desc'],
            ]
        ]);
        // 标记formid已使用
        $formId->setIsUsed();
        return $status;
    }

    /**
     * 拼团拼单状态通知
     * @param \app\common\model\sharing\Active $active
     * @param string $status_text
     * @return bool
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function sharingActive($active, $status_text)
    {
        // 微信模板消息
        $config = SharingSettingModel::getItem('basic', $active['wxapp_id']);
        if (empty($config['tpl_msg_id'])) {
            return false;
        }
        foreach ($active['users'] as $item) {
            // 获取formid
            if (!$formId = FormidModel::getAvailable($item['user']['user_id'])) {
                continue;
            }
            // 发送模板消息
            $this->sendTemplateMessage($active['wxapp_id'], [
                'touser' => $item['user']['open_id'],
                'template_id' => $config['tpl_msg_id'],
                'page' => 'pages/sharing/active/index?active_id=' . $active['active_id'],
                'form_id' => $formId['form_id'],
                'data' => [
                    // 订单编号
                    'keyword1' => $item['sharing_order']['order_no'],
                    // 商品名称
                    'keyword2' => $active['goods']['goods_name'],
                    // 拼团价格
                    'keyword3' => $item['sharing_order']['pay_price'],
                    // 拼团人数
                    'keyword4' => $active['people'],
                    // 拼团时间
                    'keyword5' => $item['create_time'],
                    // 拼团结果
                    'keyword6' => $status_text,
                ]
            ]);
            // 标记formid已使用
            $formId->setIsUsed();
        }
        return true;
    }

    /**
     * 分销商提现审核通知
     * @param \app\common\model\dealer\Withdraw $withdraw
     * @return bool
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function withdraw($withdraw)
    {
        // 模板消息id
        $template = DealerSettingModel::getItem('template_msg', $withdraw['wxapp_id']);
        if (empty($template['withdraw_tpl'])) {
            return false;
        }
        // 获取formid
        if (!$formId = FormidModel::getAvailable($withdraw['user_id'])) {
            return false;
        }
        // 获取用户信息
        $user = UserModel::detail($withdraw['user_id']);
        // 发送模板消息
        $remark = '无';
        if ($withdraw['apply_status'] == 30) {
            $remark = $withdraw['reject_reason'];
        }
        $status = $this->sendTemplateMessage($withdraw['wxapp_id'], [
            'touser' => $user['open_id'],
            'template_id' => $template['withdraw_tpl'],
            'page' => 'pages/dealer/withdraw/list/list',
            'form_id' => $formId['form_id'],
            'data' => [
                // 提现时间
                'keyword1' => $withdraw['create_time'],
                // 提现方式
                'keyword2' => $withdraw['pay_type']['text'],
                // 提现金额
                'keyword3' => $withdraw['money'],
                // 提现状态
                'keyword4' => $withdraw->applyStatus[$withdraw['apply_status']],
                // 备注
                'keyword5' => $remark,
            ]
        ]);
        // 标记formid已使用
        $formId->setIsUsed();
        return $status;
    }

    /**
     * 分销商入驻审核通知
     * @param \app\common\model\dealer\Apply $dealer
     * @return bool
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function dealer($dealer)
    {
        // 模板消息id
        $template = DealerSettingModel::getItem('template_msg', $dealer['wxapp_id']);
        if (empty($template['apply_tpl'])) {
            return false;
        }
        // 获取formid
        if (!$formId = FormidModel::getAvailable($dealer['user_id'])) {
            return false;
        }
        // 获取用户信息
        $user = UserModel::detail($dealer['user_id']);
        // 发送模板消息
        $remark = '分销商入驻审核通知';
        if ($dealer['apply_status'] == 30) {
            $remark .= "\n\n驳回原因：" . $dealer['reject_reason'];
        }
        $status = $this->sendTemplateMessage($dealer['wxapp_id'], [
            'touser' => $user['open_id'],
            'template_id' => $template['apply_tpl'],
            'page' => 'pages/dealer/index/index',
            'form_id' => $formId['form_id'],
            'data' => [
                // 申请时间
                'keyword1' => $dealer['apply_time'],
                // 审核状态
                'keyword2' => $dealer->applyStatus[$dealer['apply_status']],
                // 审核时间
                'keyword3' => $dealer['audit_time'],
                // 备注信息
                'keyword4' => $remark,
            ]
        ]);
        // 标记formid已使用
        $formId->setIsUsed();
        return $status;
    }

    /**
     * 发送模板消息
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-11-26
     * Time: 16:33
     */
    private function sendTemplateMessage($params)
    {
        // 微信模板消息
        $WxTplMsg = new WxTplMsg('wxd483c388c3d545f3', 'd19b0561679a32122f10d524153f7ea5');
        return $WxTplMsg->sendTemplateMessage($params);
    }

    /**
     * 获取小程序prepay_id记录
     * @param $order_id
     * @param int $orderType 订单类型 (10商城订单 20拼团订单)
     * @return WxappPrepayIdModel|array|false|string|\think\Model
     */
    private function getPrepayId($order_id, $orderType = OrderTypeEnum::MASTER)
    {
        return WxappPrepayIdModel::detail($order_id, $orderType);

    }

    /**
     * 格式化商品名称
     * @param $goodsData
     * @return string
     */
    private function formatGoodsName($goodsData)
    {
        $str = '';
        foreach ($goodsData as $goods) $str .= $goods['goods_name'] . ' ';
        return $str;
    }

}