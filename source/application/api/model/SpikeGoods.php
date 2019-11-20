<?php

namespace app\api\model;

use app\common\model\SpikeGoods as SpikeGoodsModel;
use think\Request;
/**
 * 秒杀商品模型
 * @author  fup
 * @date    2019-08-14
 */
class SpikeGoods extends SpikeGoodsModel
{
    /**
     * 获取秒杀商品
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-14
     * Time: 11:31
     */
    public function getList($time_point,$store_id){

        !empty($store_id)     && $this->where('b.store_id','=',$store_id) ;
        $list = $this->alias('a')
            ->field('a.*,b.store_id')
            ->join('spike_activity b','a.spike_id = b.id','LEFT')
            ->where('a.time_point','=',$time_point)
            ->where('a.mark','=',1)
            ->where('b.start_time','ELT',time())
            ->where('b.end_time','EGT',time())
            ->where('b.status','=',1)
            ->where('b.mark','=',1)
            ->paginate(config('paginate.list_rows'),false,[
                'query' => Request::instance()->request()
            ])->each(function ($item)use($time_point){
                $item['store_name'] = \app\store\model\Store::getStoreList(true)[$item['store_id']]['store_name'];
                $item['goods_remain'] = OrderGoods::getGoodsRemain($item['store_goods_id'],$item['spike_id'],$item['store_id'],1,['b.order_state'=>['GT',10]]);
                $item['goods_surplus'] = bcsub($item['goods_num'],$item['goods_remain']);
                $item['start_time'] = strtotime(date('Y-m-d').'' .self::$time[$time_point].':00:00');
                $item['end_time'] = strtotime(date('Y-m-d').'' .self::$time[$time_point].':00:00') + 3600*2-1;
                return $item;

            });
        return $list;
    }


    /**
     * 获取当前时间段
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-14
     * Time: 13:35
     */
    public static function getDefaultTimePoint(){
        $now = date('H');
        $new_time = $time = self::$time;
        if(!in_array($now,$new_time)){
            $new_time[] = $now;
            sort($new_time);
            $now = $new_time[array_search($now,$new_time) - 1];
        }
        return array_search($now,$time);

    }

    /**
     * 获取秒杀活动商品时间段
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-14
     * Time: 13:36
     */
    public static function getTimePoint($now){
        $time = self::$time;
        foreach ($time as $k => $item){
            $data[] = array(
                'time_point' => $k,
                'time' => $item . ':00',
                'now' => ($k == $now) ? true : false,
                'state' => self::getSpikeStatus($item),
                'startTime' => date('Y-m-d') .' '.$item . ':00:00',
                'perendTime' => date('Y-m-d H:i:s',strtotime(date('Y-m-d') .' '.$item . ':00:00') + 3600 * 2 -1),
            );
        }
        return $data;
    }

    /**
     * 获取当前时间段状态
     * author fup
     * date 2019-07-15
     */
    public static function getSpikeStatus($time_point){
        $time = time();
        $start_time = strtotime(date('Y-m-d') .' '.$time_point . ':00:00');
        $end_time = strtotime(date('Y-m-d') .' '.$time_point . ':00:00') + 3600 * 2 -1;
        $status = '';
        if($time > $start_time && $time < $end_time){
            $status = '进行中';
        }else if($time < $start_time){
            $status = '未开始';
        }else if($time > $end_time){
            $status = '已结束';
        }
        return $status;

    }

    /**
     * 秒杀商品详情
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-17
     * Time: 16:08
     */
    public static function detail($where){
        if (is_array($where)) {
            $filter = array_merge([], $where);
        } else {
            $filter['id'] = (int)$where;
        }
        return self::get($filter,['activity','goods']);
    }

    /**
     * 校验购买数量及库存
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-17
     * Time: 16:30
     */
    public function checkByNum($num,$userId,$source){
        if($this['limit_num'] < $num){
            $this->error = '你选择的数量大于该商品的限购数量！';
            return false;
        }

        if(!isset($this['goods']) || !$this['goods']){
            $this->error = '商品不存在';
            return false;
        }
        if((int)$this['goods']['is_on_sale']['value'] !== 1){
            $this->error = '商品已下架';
            return false;
        }

        if(!in_array(1,explode(',',$this['goods']['attributes']))){
            $this->error = '仅允许秒杀门店自提商品哦~';
            return false;
        }
        if(!OrderGoods::checkActivityTimes($userId)){
            $this->error = '每天只允许秒杀一次哦~';
            return false;
        }
        if(bcadd(OrderGoods::getGoodsRemain($this['store_goods_id'],$this['spike_id'],$this['activity']['store_id'],$source,['b.buyer_id'=>$userId,'b.order_state'=>['EGT',10]]),$num) > $this['limit_num']){
            $this->error = '限购'.$this['limit_num'].'件';
            return false;
        }
        if(OrderGoods::getGoodsRemain($this['store_goods_id'],$this['spike_id'],$this['activity']['store_id'],$source,['b.order_state'=>['GT',10]]) >= $this['goods_num']){
            $this->error = '库存不足';
            return false;
        }
        $spec_arr = [];
        $key = $this['goods_key'];
        if ($key) {
            $key_arr = explode('_', $key);
            $key_pailie = arrangement($key_arr, count($key_arr));
            foreach ($key_pailie as $v) {
                $spec_arr[] = implode('_', $v);
            }
        }
        if(!$stock = StoreGoodsSpecPrice::getSpecPriceStock($this['store_goods_id'],$spec_arr)){
            $this->error = '商品库存不足';
            return false;
        }
        if($stock['stock'] < $num){
            $this->error = '商品库存不足';
            return false;
        }
        return true;
    }

}
