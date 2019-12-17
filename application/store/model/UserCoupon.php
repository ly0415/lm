<?php

namespace app\store\model;

use app\common\model\coupon\UserCoupon as UserCouponModel;

/**
 * 用户优惠券模型
 * Class UserCoupon
 * @package app\store\model
 */
class UserCoupon extends UserCouponModel
{
    /**
     * 获取优惠券列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($query = [])
    {
        $this->setWhere($query);
        $list = $this->alias('uc')
            ->field('uc.*,u.username,u.phone,c.money,c.type,c.discount,l.id as lid')
            ->join('user u','uc.user_id = u.id','left')
            ->join('coupon c','c.id = uc.c_id','left')
            ->join('coupon_log l','uc.id = l.user_coupon_id','left')
            ->where('u.mark','=',1)
            ->order(['uc.add_time' => 'DESC'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
//        dump($list);die;
        return $this->formatData($list);
//        dump($list->toArray());die;
//        return $this->with(['user','coupon','log'])
//            ->order(['add_time' => 'asc'])
//            ->paginate(15, false, [
//                'query' => request()->request()
//            ]);
    }

    /**
     * 获取优惠券列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getListAll($query = [])
    {
        $this->setWhere($query);
        $list = $this->alias('uc')
            ->field('uc.*,u.username,u.phone,c.money,c.type,c.discount,l.id as lid')
            ->join('user u','uc.user_id = u.id and u.mark = 1','left')
            ->join('coupon c','c.id = uc.c_id','left')
            ->join('coupon_log l','uc.id = l.user_coupon_id','left')
            ->order(['uc.add_time' => 'DESC'])
            ->select();

//        dump($list->toArray());die;
        $list = $this->formatData($list);
        return $list;
//        dump($list->toArray());die;
//        return $this->with(['user','coupon','log'])
//            ->order(['add_time' => 'asc'])
//            ->paginate(15, false, [
//                'query' => request()->request()
//            ]);
    }

    public function formatData(&$list){
        foreach ($list as &$item){
            if(isset($item['type']) && $item['type'] == 1){
                $item['type'] = ['text'=>'抵扣劵','value'=>$item['type']];
                $item['desc'] = ['text'=>'满'.$item['money'].'元抵'.$item['discount'].'元', 'money' =>$item['money'],'discount'=>$item['discount']];
            }else{
                $item['type'] = ['text'=>'兑换劵','value'=>$item['type']];
                $item['desc'] = ['text' => '不超过'.$item['money'].'元即可使用', 'money' =>$item['money']];
            }
        }
        return $list;
    }

    /**
     * 订单导出
     * @param $dataType
     * @param $query
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function exportList($where=[])
    {
        // 获取订单列表
        $list = $this->getListAll($where);
        // 表格标题
        $tileArray = ['用户名', '手机号', '类型', '状态', '开始时间', '结束时间', '赠送时间', '描述'];
        // 表格内容
        $dataArray = [];
        foreach ($list as $coupon) {
                $dataArray[] = [
                    '用户名' => $this->filterValue($coupon['username']),
                    '手机号' => $this->filterValue($coupon['phone']),
                    '类型' => $coupon['type']['text'],
                    '状态' => is_null($coupon['lid']) ? '未使用' : '已使用',
                    '开始时间' => $this->filterValue($coupon['start_time']['text']),
                    '结束时间' => $this->filterValue($coupon['end_time']['text']),
                    '赠送时间' =>$this->filterValue( $coupon['add_time']['text']),
                    '描述' => $coupon['desc']['text'],
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
     * 设置检索查询条件
     * @param $query
     */
    private function setWhere($query)
    {
//        dump($query);die;
        if(isset($query['state']) && !empty($query['state'])){
           $this->transferDataType($query['state']);
        }
        if (isset($query['type']) && !empty($query['type']) && $query['type'] != '-1') {
            $this->where('c.type','=',$query['type']);
        }
        if (isset($query['phone']) && !empty($query['phone'])) {
            $this->where('u.phone','=',$query['phone']);
        }
        if (isset($query['add_time']) && !empty($query['add_time'])) {
            $this->where('uc.add_time', '>=', strtotime($query['add_time']));
        }
        if (isset($query['end_time']) && !empty($query['end_time'])) {
            $this->where('uc.add_time', '<', strtotime($query['end_time']) + 86400);
        }
    }

    /**
     * 转义数据类型条件
     * @param $dataType
     * @return array
     */
    private function transferDataType($state)
    {
        // 数据类型
        $coupon_id = CouponLog::getCouponId();
        if($state == 1 && !empty($coupon_id)){
            $this->where('uc.id','not in',$coupon_id);
        }else if($state == 2 && !empty($coupon_id)){
            $this->where('uc.id','in',$coupon_id);
        }
    }

}