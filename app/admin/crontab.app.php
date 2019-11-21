<?php

/**
 * 定时任务控制器
 * @author lee
 * @date 2017-11-23 18:56:08
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class crontabApp extends BaseApp
{
    private $storeMod;
    private $storeCateMod;
    private $fxSiteMod;
    private $fxUserMod;
    private $fxTreeMod;
    private $todayTime;
    private $fxLogMod;


    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->storeCateMod = &m('storeCate');
        $this->storeMod = &m('store');
        $this->fxSiteMod = &m('fxSite');
        $this->fxUserMod = &m('fxuser');
        $this->fxTreeMod = &m('fxuserTree');
        $this->fxLogMod = &m('fxRevenueLog');
        $this->todayTime = mktime(0,0,0,date('m'),1,date('Y'));

    }

    /*
     * 统计每个区域的定时订单佣金
     * @author lee
     * @date 2017-11-23 18:57:29
     */
    public function doFxmoney(){
        $thismonth = date('m');
        $thisyear = date('Y');
        if ($thismonth == 1) {
            $lastmonth = 12;
            $lastyear = $thisyear - 1;
        } else {
            $lastmonth = $thismonth - 1;
            $lastyear = $thisyear;
        }
        $lastStartDay = $lastyear . '-' . $lastmonth . '-1';
        $lastEndDay = $lastyear . '-' . $lastmonth . '-' . date('t', strtotime($lastStartDay));
        $b_time = strtotime($lastStartDay);//上个月的月初时间戳
        $e_time = strtotime($lastEndDay);//上个月的月末时间戳
        //end

        $where = " add_time>=".$b_time." and add_time <=".$e_time." and flag=1";
        $order_list = $this->fxLogMod->getData(array("cond"=>$where));
        foreach($order_list as $k=>$v){
            $this->doFxuserMoney($v);
        }
    }
    /*
     * 处理分销订单并进账
     */
    public function doFxuserMoney($info=array()){
        //获取区域结算规则
        $store_site = $this->fxSiteMod->getOne(array("cond"=>"store_id=".$info['store_id']));
        $order_day = ($store_site['order_day']-1)*3600*24 + $this->todayTime;
        $now = time();
        if($now >= $order_day) {
            //等级1
            $sql_m_1 = "UPDATE ".DB_PREFIX."fx_user_money SET money=money+". $info['lev1_revenue']." where store_id=" .$info['store_id'] ." and user_id=".$info['lev1_user_id'];
            $res1 = $this->fxSiteMod->sql_b_spec($sql_m_1);
            //等级2
            $sql_m_2 = "UPDATE ".DB_PREFIX."fx_user_money SET money=money+". $info['lev2_revenue']." where store_id=" .$info['store_id'] ." and user_id=".$info['lev2_user_id'];
            $res2=$this->fxSiteMod->sql_b_spec($sql_m_2);
            //等级3
            $sql_m_3 = "UPDATE ".DB_PREFIX."fx_user_money SET money=money+". $info['lev3_revenue']." where store_id=" .$info['store_id'] ." and user_id=".$info['lev3_user_id'];
            $res3=$this->fxSiteMod->sql_b_spec($sql_m_3);

            if($res1 || $res2 || $res3){
                $r=$this->fxLogMod->doEdit($info['id'],array("flag"=>2));
            }
        }
    }
    /*
     * 统计一个国家区域下的会员
     * @author lee
     * 2017-11-23 19:06:32
     * @param $store_id 区域ID
     */
    public function doStoreCash($store_cate){
        //获取该区域下所有的分销商
        $user_data = $this->fxUserMod->getData(array("cond"=>"store_cate=".$store_cate." and freeze=1 and is_check=2"));

        if($user_data){
            foreach($user_data as $k=>$v){
                $this->doUserCash($v['user_id'],$v['id']);
            }
        }

    }
    /*
     * 统计一个会员上个月所有的有效订单分润
     * @author lee
     * @date 2017-11-23 19:16:07
     * @param $user_id用户ID $fx_user_id分销用户ID $order_day佣金到账时间
     */
    public function doUserCash($user_id,$fx_user_id){
        //$fx_tree = $this->fxTreeMod->getOne(array("cond"=>"user_id=".$user_id));
        $res=$this -> doFxLog($user_id);
        $log_edit=array('flag' => 2);
        $log_data=$res['log_data'];
        $log_list=$res['log_list'];
        if($log_data){
            foreach($log_data as $k => $v){
                $store_site = $this -> fxSiteMod -> getOne(array("cond" => "store_id=". $v['store_id']));

                //判断今天时间戳是否等于分销店铺设置的时间戳
                if($this->todayTime >= $store_site['order_day']){
                    $sql_m="UPDATE ".DB_PREFIX."fx_user_money SET money=money+". $v['fx_money']." where store_id=" .$v['store_id'] ." and user_id=".$v['user_id'];
                    $res=$this->fxSiteMod->sql_b_spec($sql_m);

                   if($res){
                       foreach($log_list as $k1=>$v1){
                           $r=$this->fxLogMod->doEdit($v1['id'],$log_edit);
                       }
                   }


                }
            }
        }
    }
    /*
     * 循环处理不同等级分销金额
     * @author lee
     * @date 2017-11-24 14:19:46
     * @param data 三个等级的分销金额统计
     */
    public function doFxLog($fx_user_id){
        //获取上个月时间戳
        $thismonth = date('m');
        $thisyear = date('Y');
        if ($thismonth == 1) {
            $lastmonth = 12;
            $lastyear = $thisyear - 1;
        } else {
            $lastmonth = $thismonth - 1;
            $lastyear = $thisyear;
        }
        $lastStartDay = $lastyear . '-' . $lastmonth . '-1';
        $lastEndDay = $lastyear . '-' . $lastmonth . '-' . date('t', strtotime($lastStartDay));
        $b_time = strtotime($lastStartDay);//上个月的月初时间戳
        $e_time = strtotime($lastEndDay);//上个月的月末时间戳
        //end

        $where=" and add_time>=".$b_time." and add_time <=".$e_time." and flag=1";

        //该用户获取等级一的分销总额
        $sql1="select sum(lev1_revenue)  as fx_money,store_id,lev1_user_id as  user_id from ". DB_PREFIX ."fx_revenue_log where lev1_user_id=".$fx_user_id.$where." group by store_id";

        //该用户获取等级二的分销总额
        $sql2="select sum(lev2_revenue) as fx_money,store_id,lev2_user_id as  user_id from ". DB_PREFIX ."fx_revenue_log where lev2_user_id=".$fx_user_id.$where." group by store_id";

        //该用户获取等级三的分销总额
        $sql3="select sum(lev3_revenue) as fx_money,store_id,lev3_user_id as  user_id from ". DB_PREFIX ."fx_revenue_log where lev3_user_id=".$fx_user_id.$where." group by store_id";

        //该用户所有等级的分销总额
        $sql_a="select * from ". DB_PREFIX ."fx_revenue_log where lev1_user_id=". $fx_user_id ." or lev2_user_id=".$fx_user_id ." or lev3_user_id=". $fx_user_id . $where;

        $log_data1 = $this -> fxSiteMod -> querySql($sql1);
        $log_data2 = $this -> fxSiteMod -> querySql($sql2);
        $log_data3 = $this -> fxSiteMod -> querySql($sql3);
        $log_list = $this -> fxSiteMod -> querySql($sql_a);
        $end_data=$this->mergeFxLog($log_data1,$log_data2,$log_data3);

        return array(
            'log_data' => $end_data,
            'log_list' => $log_list
        );
    }

    /*
     * 合并三个等级分销日志
     * @author lee
     * @date 2017-11-24 15:17:52
     */
    public function mergeFxLog($log_data1,$log_data2,$log_data3){
        $log_data=array();

        if(!empty($log_data1) && !empty($log_data2) && !empty($log_data3)) {
            foreach ($log_data1 as $k1 => $v1) {
                foreach ($log_data2 as $k2 => $v2) {
                    foreach ($log_data3 as $k3 => $v3) {
                        if(($v1['store_id'] == $v2['store_id']) && ($v1['store_id'] == $v3['store_id'])){
                            $log_data[$k1]=array(
                                'fx_money'=>($v1['fx_money']+$v2['fx_money']+$v3['fx_money']),
                                'store_id'=>$v1['store_id'],
                                'user_id'=>$v1['user_id']
                            );
                        }
                    }
                }
            }

        }elseif(empty($log_data1) && !empty($log_data1) && !empty($log_data2)){
            foreach ($log_data2 as $k2 => $v2) {
                foreach ($log_data3 as $k3 => $v3) {
                    if( $v2['store_id'] == $v3['store_id']){
                        $log_data[$k2]=array(
                            'fx_money'=>($v2['fx_money']+$v3['fx_money']),
                            'store_id'=>$v2['store_id'],
                            'user_id'=>$v2['user_id']
                        );
                    }
                }
            }

        }elseif(!empty($log_data1) && empty($log_data2) && !empty($log_data3)){
            foreach ($log_data1 as $k1 => $v1) {
                foreach ($log_data3 as $k3 => $v3) {
                    if( $v1['store_id'] == $v3['store_id']){
                        $log_data[$v3]=array(
                            'fx_money'=>($v3['fx_money']+$v1['fx_money']),
                            'store_id'=>$v1['store_id'],
                            'user_id'=>$v1['user_id']
                        );
                    }
                }
            }


        }elseif(!empty($log_data1) && !empty($log_data2) && empty($log_data3)){
            foreach ($log_data1 as $k1 => $v1) {
                foreach ($log_data2 as $k2 => $v2) {
                    if( $v1['store_id'] == $v2['store_id']){
                        $log_data[$k2]=array(
                            'fx_money'=>($v2['fx_money']+$v1['fx_money']),
                            'store_id'=>$v2['store_id'],
                            'user_id'=>$v2['user_id']
                        );
                    }
                }
            }

        }elseif(!empty($log_data1) && empty($log_data2) && empty($log_data3)){
            $log_data = $log_data1;

        }elseif(empty($log_data1) && !empty($log_data2) && empty($log_data3)){
            $log_data = $log_data2;

        }elseif(empty($log_data1) && empty($log_data2) && !empty($log_data3)){
            $log_data = $log_data3;
        }
        return $log_data;

    }
}
