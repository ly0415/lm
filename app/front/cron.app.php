<?php

    /**
    * 定时任务
    * @author wanyan
    * @date 2017/07/19
    */
    if (!defined('IN_ECM')) {
         die('Forbidden');
    }

class CronApp extends BaseFrontApp
{
    private $model;
    private $orderMod;
    private $orderGoodsMod;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->orderMod = &m('order');
        $this->orderGoodsMod = &m('orderGoods');
        $this->model = &m('systemConsole');
    }

    /**
     * 测试
     * @author wanyan
     * @date 2018/01/20
     */
    public function test(){
//        $path = ROOT_PATH.'/logs/wanyan.txt';
//        error_log(print_r('1',true),'3',$path);
        echo '1111';
    }
    /**
     * 获取未付款的订单
     * @author wanyan
     * @date 2017/07/19
     */
    public function getUnPayOrder(){
        $sql ="select r.`order_id`,r.order_sn,r.add_time from ".DB_PREFIX."order as r left join ".DB_PREFIX."order_goods as og
         ON r.order_sn = og.order_id where r.order_state = '10'  and  r.mark =1 and og.order_state ='10'";
        $rs = $this->orderMod->querySql($sql);
        foreach ($rs as $k =>$v){
          $expireTime = $v['add_time'] + (7*24*3600);
          if($expireTime < time()){
              $order_data =array(
                  'key' => 'order_id',
                  'order_state' => '0'
              );
              $this->orderMod->doEdit($v['order_id'],$order_data);
              $order_goods_id = $this->getOrderGoodsId($v['order_sn']);
              $sql = "update ".DB_PREFIX."order_goods set order_state = '0' where rec_id in ({$order_goods_id})";
              $this->orderGoodsMod->doEditSql($sql);
          }
        }


    }
    /**
     * 查询子订单的ID
     * @author wanyan
     * @date 2018/01/20
     */

    public function getOrderGoodsId($order_sn){
        $info=array();
        $rs = $this->orderGoodsMod->getData(array('cond' =>"`order_id` = '{$order_sn}' and `order_state` = '10'",'fields'=>"rec_id"));
        foreach ($rs as $k=>$v){
            array_push($info,$v['rec_id']);
        }
        return implode(',',$info);
    }

    /**
     * 获取待确认收货的的订单
     * @author wangshuo    
     * @date 2018/09/12
     */
    public function getPayOrder(){
        //获取自动收货时间天数
        $allDelivery = $this->model->getAllDelivery();
        // $sql ="select r.`order_id`,r.order_sn,r.install_time from ".DB_PREFIX."order as r left join ".DB_PREFIX."order_goods as og
        //  ON r.order_sn = og.order_id where r.order_state = '40'  and  r.mark =1 and r.region_install ='20'";
        $sql ="select r.`order_id`,r.order_sn,r.install_time from ".DB_PREFIX."order as r where r.order_state = '40' and r.mark =1 and r.region_install ='20'";  // by xt 2019.01.22
        $rs = $this->orderMod->querySql($sql);
        foreach ($rs as $k =>$v){
            $expireTime = $v['install_time'] + ($allDelivery[1]['delivery_time']*24*3600);
            if($expireTime < time()){
                $order_data =array(
                    'key' => 'order_id',
                    'order_state' => '50'
                );
                $this->orderMod->doEdit($v['order_id'],$order_data);
                $order_goods_id = $this->getOrderGoodsId($v['order_sn']);
                $sql = "update ".DB_PREFIX."order_goods set order_state = '50' where rec_id in ({$order_goods_id})";
                $this->orderGoodsMod->doEditSql($sql);
                $fxUserMod=&m('fxuser');
                $fxUserMod->getAccount($v['order_sn']);
            }
        }
        
    }



}