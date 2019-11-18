<?php
/**
 * 秒杀活动模型
 * @author: gao
 * @date: 2018/12/18
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class SpikeActiviesMod extends BaseMod{
    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct("spike_activity");
    }
    //判断商品在交集时间类不能重复添加

    public function checkRepeat($good_id, $store_id, $start_time, $end_time,$id) {
        if(empty($id)){
            $sql = "SELECT sg.store_goods_id,sg.goods_key FROM  `bs_spike_activity` AS sa LEFT JOIN `bs_spike_goods` AS sg ON sa.id = sg.spike_id
        WHERE sa.mark = 1 and  sa.`store_id` = '{$store_id}' AND 	((start_time >= '{$start_time}' AND start_time <= '{$end_time}') OR 
        (start_time <= '{$start_time}' AND end_time >= '{$end_time}') OR
        (end_time >= '{$start_time}' AND end_time <= '{$end_time}'))";
        } else{
            $sql = "SELECT sg.store_goods_id,sg.goods_key FROM  `bs_spike_activity` AS sa LEFT JOIN `bs_spike_goods` AS sg ON sa.id = sg.spike_id
        WHERE sa.mark = 1 and  sa.`store_id` = '{$store_id}' AND 	((start_time >= '{$start_time}' AND start_time <= '{$end_time}') OR 
        (start_time <= '{$start_time}' AND end_time >= '{$end_time}') OR
        (end_time >= '{$start_time}' AND end_time <= '{$end_time}')) and sa.id !='{$id}'";
        }
        $goodsInfo = $this->querySql($sql);
        foreach ($goodsInfo as $k => $v) {
            $new[] = $v['store_goods_id'] . '-' . $v['goods_key'];
        }
        foreach ($good_id as $k1 => $v1) {
            if (in_array($v1, $new)) {
                $ngood[] = $v1;
            }
        }
        if (count($ngood)) {
            return $ngood;
        } else {
            return 0;
        }
    }


}
?>