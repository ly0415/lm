<?php

namespace app\store\model\balance;

use think\Config;
use think\db;
use app\common\model\AmountLog   as AmountLogModel;

/**
 *
 * Class UserComment
 * @package app\store\model
 */
class BalanceRecharge extends AmountLogModel
{

    /**
     *余额充值中心列表
     * @author ly
     * @date 2019-11-25
     */
    public function getList($username='',$phone='',$add_time='',$end_time='',$type='',$status='',$source='')
    {
        $this->getBalanceWhere($username,$phone,$add_time,$end_time,$type,$status,$source);
        $list['type']   = !empty($type)?$type:'';
        $list['status'] = !empty($status)?$status:'';
        $list['source'] = !empty($source)?$source:'';
        $list['data']=$this
                ->alias('g')
                ->field('re.c_money as re_c_money,re.s_money as re_s_money,re.integral as re_integral,re.percent as re_percent,u.username,u.phone,g.id,g.order_sn,g.type,g.status,g.c_money,g.old_money,g.new_money,g.point_rule_id,g.source,g.add_user,g.add_time,g.check_user,g.pay_time,g.transaction_id,g.mark')
                ->join('user u','u.id=g.add_user','LEFT')
                ->join('recharge_point re','re.id=g.point_rule_id','LEFT')
                ->order('g.id','DESC')
                ->where('g.type','in',[1,3,4,5,6,7,8])
                ->where('g.mark',1)
                ->order('g.add_time','DESC')
                ->paginate(15, false, ['query' => \request()->request()])
                ->each(function($item){
                   $this->getFormData($item);
                });
        return $list;
    }

    /**
     *数据导出列表
     * @author ly
     * @date 2019-11-25
     */
    public function getListAll($username='',$phone='',$add_time='',$end_time='',$type='',$status='',$source=''){
        $this->getBalanceWhere($username,$phone,$add_time,$end_time,$type,$status,$source);
        $list =$this
            ->alias('g')
            ->field('re.c_money as re_c_money,re.s_money as re_s_money,re.integral as re_integral,re.percent as re_percent,u.username,u.phone,g.id,g.order_sn,g.type,g.status,g.c_money,g.old_money,g.new_money,g.point_rule_id,g.source,g.add_user,g.add_time,g.check_user,g.pay_time,g.transaction_id,g.mark')
            ->join('user u','u.id=g.add_user','LEFT')
            ->join('recharge_point re','re.id=g.point_rule_id','LEFT')
            ->order('g.id','DESC')
            ->where('g.type','in',[1,3,4,5,6,7,8])
            ->where('g.mark',1)
            ->order('g.add_time','DESC')
            ->select()
            ->each(function($item){
                $this->getFormData($item);
            });
        return $list;
    }

    /**
     * 数据导出
     * @author ly
     * @date 2019-11-25
     */
    public function exportList($username='',$phone='',$add_time='',$end_time='',$type='',$status='',$source=''){
        $list = $this->getListAll($username,$phone,$add_time,$end_time,$type,$status,$source);
        $tileArray = ['ID', '用户名称', '联系方式', '充值金额', '变更前余额', '账户余额', '充值描述', '充值类型','充值状态','来源','操作时间'];
        $dataArray = [];
        foreach ($list as $coupon) {
            $dataArray[] = [
                'ID' => $this->filterValue($coupon['id']),
                '用户名称' => $this->filterValue($coupon['username']),
                '联系方式' => $coupon['phone'],
                '充值金额' => $this->filterValue($coupon['c_money']),
                '变更前余额' => $this->filterValue($coupon['old_money']),
                '账户余额' => $this->filterValue($coupon['new_money']),
                '充值描述' => $this->filterValue($coupon['description']),
                '充值类型' => $this->filterValue($coupon['type_name']),
                '充值状态' => $this->filterValue($coupon['type_status']),
                '来源' =>$this->filterValue( $coupon['source']),
                '操作时间' =>date('Y-m-d', $coupon['add_time']),
            ];
        }
        // 导出csv文件
        $filename = 'coupon-' . date('YmdHis');
        return export_excel($filename . '.csv', $tileArray, $dataArray);
    }


    /**
     * 表格值过滤
     * @param $value
     * @return string
     */
    private function filterValue($value)
    {
        return "\t" . $value . "\t";
    }

    /**
     *余额充值列表 查询
     * @author ly
     * @date 2019-11-25
     */
    public function getBalancewhere($username='',$phone='',$add_time='',$end_time='',$type='',$status='',$source=''){
        !empty($username) && $this->where('u.username','like', "%$username%");
        !empty($phone) && $this->where('u.phone','like', "%$phone%");
        if (isset($type) && !empty($type) && $type != '-1') {
            $this->where('g.type','=',$type);
        }
        if (isset($status) && !empty($status) && $status != '-1') {
            $this->where('g.status','=',$status);
        }
        if (isset($source) && !empty($source) && $source != '-1') {
            $this->where('g.source','=',$source);
        }
        if (isset($add_time) && !empty($add_time)) {
            $this->where('g.add_time', '>=', strtotime($add_time));
        }
        if (isset($end_time) && !empty($end_time)) {
            $this->where('g.add_time', '<', strtotime($end_time) + 86400);
        }
    }

    /**
     * 余额充值 条件
     * @author ly
     * @date 2019-11-25
     */
    public function getFormData($item){
        $item['type_status']='';
        if(!empty($item['source'])){
            switch($item['source']){
                case 1;
                    $item['source'] = '公众号';
                    break;
                case 2;
                    $item['source'] = '小程序';
                    break;
                case 3;
                    $item['source'] = 'PC';
                    break;
                case 4;
                    $item['source'] = 'web端';
                    break;

            }
        }
        if(!empty($item['type'])){
            switch($item['type']){
                case 1:
                    $item['description'] = '充值'.$item['re_c_money'].'元送'.$item['re_s_money'].'元送'.$item['re_integral'].'积分抵扣比例'.$item['re_percent'].'%';
                    $item['type_name']   = '微信充值';
                    if(!empty($item['status'])){
                        switch($item['status']){
                            case 1;
                                $item['type_status'] = '待支付';
                                break;
                            case 2;
                                $item['type_status'] = '已支付';
                                break;
                            case 3;
                                $item['type_status'] = '支付失败';
                                break;

                        }
                    }
                    break;
                case 3:
                    $item['description']  = '注册赠送'.$item['c_money'].'元';
                    $item['type_name']    = '注册赠送';
                    $item['type_status']  = '已赠送';
                    break;
                case 4:
                    $item['description'] = '充值'.$item['re_c_money'].'元送'.$item['re_s_money'].'元送'.$item['re_integral'].'积分抵扣比例'.$item['re_percent'].'%';
                    $item['type_name']   = '线下付款';
                    if(!empty($item['status'])){
                        switch($item['status']){
                            case 1;
                                $item['type_status'] = '待审核';
                                break;
                            case 2;
                                $item['type_status'] = '审核通过';
                                break;
                            case 3;
                                $item['type_status'] = '审核不通过';
                                break;

                        }
                    }
                    break;
                case 5:
                    $item['description'] = '充值码充值'.$item['c_money'];
                    $item['type_name']   = '充值码';
                    $item['type_status'] = '充值成功';
                    break;
                case 6:
                    $item['description'] = '订单退款'.$item['c_money'];
                    $item['type_name']   = '订单退款';
                    $item['type_status'] = '已退款';
                    break;
                case 7:
                    $item['description'] = '';
                    $item['type_name']   = '';
                    $item['type_status'] = '';
                    break;
            }
        }
        return $item;
    }

    /**
     *充值类型
     * @author ly
     * @date 2019-11-25
     */
    public function getTypeList(){
        return [['id'=>1,'name'=>'充值'],
            ['id'=>3,'name'=>'注册赠送'],
            ['id'=>4,'name'=>'线下付款'],
            ['id'=>5,'name'=>'充值码'],
            ['id'=>6,'name'=>'退款'],
            ['id'=>7,'name'=>'系统处理'],];

    }

    /**
     *充值来源
     * @author ly
     * @date 2019-11-25
     */
    public function getSourcesList(){
        return [['id'=>1,'name'=>'公众号'],
                ['id'=>2,'name'=>'小程序'],
                ['id'=>3,'name'=>'PC'],
                ['id'=>4,'name'=>'web端'],];

    }
}
