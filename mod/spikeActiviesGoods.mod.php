<?php
/**
 * 秒杀活动模型
 * @author: gao
 * @date: 2018/12/18
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class SpikeActiviesGoodsMod extends BaseMod{
    /**
     * 时间段
     */
    public static $time = array(
        1   =>  '08',
        5   =>  '10',
        10  =>  '12',
        15  =>  '14',
        20  =>  '16'
    );
    //活动状态
    public static $status = array(
        '即将开始',
        '抢购中',
        '已结束'
    );

    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct("spike_goods");
    }


    //获取活动商品

    public  function getSpikeGoods($spikeId,$activityData){
        $orderGoodsMod=&m('orderGoods');
        $sql="SELECT * FROM ".DB_PREFIX."spike_goods WHERE `spike_id`={$spikeId} and mark = 1";
        $spikeGoodsData=$this->querySql($sql);
        foreach($spikeGoodsData as $k=>$v){
            $spikeGoodsData[$k]['soldNum']=$orderGoodsMod->getActivityOrderNum($activityData['source'],$activityData['id'],$v['store_goods_id']);
            $spikeGoodsData[$k]['percent'] = ($spikeGoodsData[$k]['soldNum']/($spikeGoodsData[$k]['soldNum'] + $v['goods_num'])) * 100;
            $spikeGoodsData[$k]['percents'] = 100 - $spikeGoodsData[$k]['percent'];
        }
        return $spikeGoodsData;
    }



}
?>