<?php

namespace app\store\model\balance;

use think\db;
use app\common\model\BalanceRechargeCoupon   as BalanceRechargeCouponModel;

/**
 *
 * Class UserComment
 * @package app\store\model
 */
class BalanceCoupon extends BalanceRechargeCouponModel
{

    /**
     *余额线下充值审核 列表
     * @author ly
     * @date 2019-11-25
     */
    public function getList($sn='',$is_use='',$source='',$add_time='',$end_time='',$service_user_id='',$store_id='')
    {
//        if($is_use !=3 ){
//            $this->where('brc.mark',1);
//        }
        $this->getBalanceCouponWhere($sn,$is_use,$source,$add_time,$end_time,$service_user_id,$store_id);
        $list['data'] = $this
                ->alias('brc')
                ->field('st.real_name as store_user_name,u.phone,u.username,ac.account_name,brc.id,brc.sn,brc.money,brc.is_use,brc.use_source,brc.store_user,brc.add_time,brc.add_user,brc.use_user,brc.mark,brc.use_time')
                ->join('account ac','brc.add_user = ac.id','LEFT')
                ->join('user u','brc.use_user = u.id ','LEFT')
                ->order('brc.id','DESC')
                ->join('store_user st','brc.store_user = st.id ','LEFT')
                ->paginate(15, false, ['query' => \request()->request()]);
//        if($service_user_id == -1)  $store_id = -1;
        $list['store_id']        = $store_id;
        $list['service_user_id'] = $service_user_id;
        $list['is_use']          = $is_use;
//        print_r($list);die;
        return $list;
    }

    /**
     *余额线下充值审核 列表
     * @author ly
     * @date 2019-11-25
     */
    public function getListAll($sn='',$is_use='',$source='',$add_time='',$end_time='',$service_user_id='',$store_id='')
    {
//        if($is_use !=3 ){
//            $this->where('brc.mark',1);
//        }
        $this->getBalanceCouponWhere($sn,$is_use,$source,$add_time,$end_time,$service_user_id,$store_id);
        $list = $this
            ->alias('brc')
            ->field('st.real_name as store_user_name,u.phone,u.username,ac.account_name,brc.id,brc.sn,brc.money,brc.is_use,brc.use_source,brc.store_user,brc.add_time,brc.add_user,brc.use_user,brc.mark,brc.use_time')
            ->join('account ac','brc.add_user = ac.id','LEFT')
            ->join('user u','brc.use_user = u.id ','LEFT')
            ->order('brc.id','DESC')
            ->join('store_user st','brc.store_user = st.id ','LEFT')
            ->select()
            ->each(function($item){
                if(!empty($item['use_source'])){
                    if($item['use_source'] == 1){
                        $item['use_source'] = '公众号';
                    }if($item['use_source'] == 2 ){
                        $item['use_source'] = '小程序';
                    }
                }else{
                    $item['use_source'] = '---';
                }
            });
        return $list;
    }

    /**
     * 数据导出
     * @author ly
     * @date 2019-11-25
     */
    public function exportList($sn='',$is_use='',$source='',$add_time='',$end_time='',$service_user_id='',$store_id=''){
        $list = $this->getListAll($sn,$is_use,$source,$add_time,$end_time,$service_user_id,$store_id);
        $tileArray = ['序号', '券码', '金额', '状态', '使用来源', '用户名', '手机号', '指派人员','操作人员','操作时间','充值时间'];
        $dataArray = [];
        foreach ($list as $coupon) {
            $dataArray[] = [
                '序号' => $this->filterValue($coupon['id']),
                '券码' => $this->filterValue($coupon['sn']),
                '金额' => $coupon['money'],
                '状态' => $coupon['is_use']==0?'未使用':'已使用',
                '使用来源' =>$coupon['use_source'] ,
                '用户名' => $this->filterValue(!empty($coupon['username'])?$coupon['username']:'---'),
                '手机号' => $this->filterValue(!empty($coupon['phone'])?$coupon['phone']:'---'),
                '指派人员' => $this->filterValue(!empty($coupon['store_user_name'])?$coupon['store_user_name']:'---'),
                '操作人员' => $this->filterValue(!empty($coupon['account_name'])?$coupon['account_name']:'---'),
                '操作时间' =>!empty($coupon['add_time'])?date('Y-m-d H:i',$coupon['add_time']):'---',
                '充值时间' =>!empty($coupon['use_time'])?date('Y-m-d H:i',$coupon['use_time']):'---',
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
     *余额充值券列表 查询
     * @author ly
     * @date 2019-11-25
     */
    public function getBalanceCouponWhere($sn='',$is_use='',$source='',$add_time='',$end_time='',$service_user_id='',$store_id=''){
        if(!empty($is_use)){
            if($is_use == 3){
                $this->where('brc.mark',0);
            }elseif($is_use == -1){
                $this->where('brc.mark',1);

            }else{
                $this->where('brc.is_use',$is_use);
                $this->where('brc.mark',1);
            }
        }else{
            $this->where('brc.mark',1);
        }
        !empty($sn) && $this->where('brc.sn','like', "%$sn%");

        if (isset($service_user_id) && !empty($service_user_id) && $service_user_id != '-1') {
            $this->where('brc.store_user', $service_user_id);
        }

        if (isset($add_time) && !empty($add_time)) {
            $this->where('brc.add_time', '>=', strtotime($add_time));
        }
        if (isset($end_time) && !empty($end_time)) {
            $this->where('brc.add_time', '<', strtotime($end_time) + 86400);
        }
    }

    /**
     * 余额充值券  删除 软删
     * @author ly
     * @date 2019-11-27
     */
    public function delete($id=''){
        if(empty($id)){
            $this->error = '请选中需要删除的';
            return false;
        }
        $data['mark']=0;
        if(is_array($id)){
            foreach($id as $val){
                $list = $this->where('id',$val)->update($data);
            }
            return $list;

        }else{
            return $this->where('id',$id)->update($data);
        }

    }
    /**
     * 指派
     * @author ly
     * @date 2019-11-27
     */
    public function designate($store_id1='',$service_user_id1='',$designate_type='',$_type=''){
        if(empty($designate_type)){
            $this->error = '请选中需要指派的';
            return false;
        }
        if($service_user_id1 == -1){
            $this->error = '请选中需要指派的人员';
            return false;
        }
        $designate_type = explode(',',$designate_type);
//        echo $store_id1.'    '.$service_user_id1;print_r($designate_type);echo $_type;die;
        $data['store_user'] = $service_user_id1;
//        $data['is_use']     = 2;
//        $data['use_time']   = time();
        switch($_type){
            case 2;
                foreach($designate_type as $value){
                    $list_one = $this->where('id',$value)->find();
                    if($list_one['is_use'] == 2){
                        $this->error = '被指派的券码中存在已使用';
                        return false;
                    }
                }
                foreach($designate_type as $val){

                    $list = $this->where('id',$val)->update($data);
                }
                return $list;
            break;
            case 1;
                foreach($designate_type as $val){

                    return  $this->where('id',$val)->update($data);
                }
            break;



        }

    }

    /**
     * 生成随机字符串
     * @param int $length
     * @param string $char
     * @return string
     */
    function str_rand($length = 8, $char = '0123456789abcdefghjklmnpqrstuvwxyz')
    {
        $string = '';
        for ($i = $length; $i > 0; $i--) {
            $string .= $char[mt_rand(0, strlen($char) - 1)];
        }
        return $string;
    }

    /**
     * 是否存在 sn
     * @param $sn
     * @return bool
     */
    public function exists($sn)
    {
        $data = $this->where('sn',$sn)->find();
        return empty($data) ? false : true;
    }

    /**
     * 生成券码
     * @return bool|string
     */
    public function findAvailableSn()
    {
        do {
            $sn = 'LM' . $this->str_rand();
        } while (
        $this->exists($sn)
        );

        return $sn;
    }

    /**
     *t添加
     * @author ly
     * @date 2019-11-26
     */
    public function add($money='',$number='')
    {
        if (!is_numeric($money)) {
            $this->error = '金额必须大于0';
            return false;
        }

        if (empty($money) || $money < 0) {
            $this->error = '金额必须大于0';
            return false;
        }

        if ($number <= 0 || $number > 99) {
            $this->error = '每次可添加1～99条';
            return false;
        }

        $this->startTrans();
        try {
            for($i = 1;$i <= $number;$i++){
                $data[$i]['sn']         = $this->findAvailableSn();
                $data[$i]['money']      = $money;
                $data[$i]['add_time']   = time();
                $data[$i]['add_user']   = USER_ID;
//                $data[$i]['use_source'] = 2;
            }
            $this->allowField(true)->saveAll($data);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }


    }

}
