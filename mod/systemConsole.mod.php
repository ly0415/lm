<?php
/**
 * 控制台
 * @author  luffy
 * @date    2018-09-07
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class systemConsoleMod extends BaseMod{
    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct("system_console");
    }

    /**
     * 页面语言设置
     * @author  luffy
     * @date    2018-09-07
     */
    public function viewLang($lang_type = 0){
        $langData = $this->getLangData($lang_type);
        return array(
            'title'             => $langData->project->console,
            'crumbs_1'          => $langData->project->index,
            'crumbs_2'          => $langData->project->auth_manage,
            'crumbs_3'          => $langData->project->console,
            'console_desc_1'    => $langData->project->console_desc_1,
            'order_sureDay'     => $langData->project->order_sureDay,
            'setting'           => $langData->public->setting,
            'day'               => $langData->public->day
                
        );
    }

    /**
     * 获取各个被控制功能状态
     * @author  luffy
     * @date    2018-09-07
     */
    public function getAllStatus(){
        $info_1 = $this->getRow(1);
        $result = array(
            1 => $info_1['status']
        );
        return $result;
    }

    /**
     * 获取自动收货时间天数
     * @author  wangshuo
     * @date    2018-10-22
     */
    public function getAllDelivery(){
        $info_1 = $this->getRow(2);
        $result = array(
            1 => $info_1['delivery_time'],
            2 => $info_1['id']
        );
        return $result;
    }

    /**
     * 获取打印机信息---（标签、小票）
     * @author  luffy
     * @date    2018-10-22
     */
    public function getAllPrint(){
        $info_1 = $this->getRow(3);
        $info_2 = $this->getRow(4);
        $result = array(
            1 => $info_1,
            2 => $info_2
        );
        return $result;
    }

    /**
     * 获取兑换券设置的时间
     * @author tangp
     * @date 2019-01-18
     */
    public function getCoupon()
    {
        $sql = "SELECT * FROM bs_system_console WHERE type = 3 order by id desc";
        $systemConsoleMod = &m('systemConsole');
        $result = $systemConsoleMod->querySql($sql);
        if ($result){
            foreach ($result as $key => $val){
                $result[$key]['start_time'] = date("Y-m-d",$result[0]['start_time']+28800);
                $result[$key]['end_time']=date("Y-m-d",$result[0]['end_time']-57599);
                $result[$key]['display'] = 1;
            }
            return $result;
        }else{
            $result = array(
                '0'=>array(
                    'start_time' => date("Y-m-d",time()),
                    'end_time'   => date("Y-m-d",time()),
                    'display'    => 0
                )
            );
            return $result;
        }
    }

    /**
     * 获取设置注册送电子券状态
     * @return array
     */
    public function getCouponActivityStatus()
    {
        $info_1 = $this->getRow(4);
        $result = array(
            1 => $info_1['status'],
            2 => $info_1['id']
        );
        return $result;
    }

    /**
     * 获取设置的抵扣券
     * @author tangp
     * @date 2019-02-14
     */
    public function getSetCoupon()
    {
        $sql = "SELECT rebate_id FROM bs_system_console WHERE type=4";
        $res=$this->querySql($sql);
        $sqll = "SELECT * FROM bs_coupon WHERE type=1 AND mark=1 AND id=".$res[0]['rebate_id'];
        $couponMod= &m('coupon');
        $result = $couponMod->querySql($sqll);

        return $result;

    }

    /**
     * 获取设置的兑换券
     * @author tangp
     * @date 2019-02-14
     */
    public function getSetDuiCoupon()
    {
        $sql = "SELECT voucher_id FROM bs_system_console WHERE type=4";
        $res=$this->querySql($sql);
        $sqll = "SELECT * FROM bs_coupon WHERE type=2 AND mark=1 AND id=".$res[0]['voucher_id'];
        $couponMod = &m('coupon');
        $result = $couponMod->querySql($sqll);

        return $result;
    }

    /**
     * 获取设置的铃铛提醒
     * @author hjp
     * @date 2019-02-14
     */
    public function selectNoticeUser($para)
    {

        $del_where = 'type=5 and rebate_id='.$para['store_id'];
        $rs_del = $this->doDelete($del_where);
        $data = array(
            'rebate_id'  =>$para['store_id'],
            'voucher_id' =>$para['user_id'],
            'type' => 5,
        );
        $rs = $this->doInsert($data);
        return $rs;
    }
}

?>