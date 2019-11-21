<?php
/**
 * 积分管理模块
 * @author wanyan
 * @date 2018-1-2
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class PointApp extends BaseStoreApp{

    private $lang_id;
    private $pointMod;
    private $userMod;
    private $storePointMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->pointMod = &m('point');
        $this->storePointMod = &m('storePoint');
        $this->userMod = &m('user');
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
    }
    //区域规则
    public function storeIndex(){
        $this->load($this->lang_id, 'admin/admin');
        $this->assign('langdata', $this->langData);
        $store_id = $this->storeId;
        $sql="SELECT * FROM ".DB_PREFIX.'store_point_site where store_id='.$store_id;
        $data = $this->storePointMod->querySql($sql);
        if(empty($data)){
            $data[0]['store_id']= $store_id;
        }
        $this->assign('res', $data[0]);
        $this->assign('lang_id',$this->lang_id);
        $this->display('userPoint/storeSite.html');
    }

    /*
     * 保存区域规则配置
     * @author lee
     * @date 2018-6-20 16:18:42
     */
    public function saveStorePoint(){
        $this->load($this->lang_id, 'admin/admin');
        $this->assign('langdata', $this->langData);
        $store_id = !empty($_REQUEST['store_id']) ? intval(trim($_REQUEST['store_id'])) : 0;
        $order_point = !empty($_REQUEST['order_point']) ? intval(trim($_REQUEST['order_point'])) : 0;
        $point_price = !empty($_REQUEST['point_price']) ? intval(trim($_REQUEST['point_price'])) : 0;
        $point_id = !empty($_REQUEST['point_id']) ? intval(trim($_REQUEST['point_id'])) : 0;

        if(!isset($order_point)){
            if($this->lang_id==0){
                $this->setData(array(),'0','订单获取睿积分百分比不能为空');
            }else{
                $this->setData(array(),'0','Recommending members to obtain rui can not be empty');
            }
        }
        if(!isset($point_price)){
            if($this->lang_id==0){
                $this->setData(array(),'0','睿积分兑换百分比上限不能为空');
            }else{
                $this->setData(array(),'0','It is recommended that the two level members get the rui not to be empty');
            }

        }
        if (!preg_match("/^[0-9]*$/",$order_point) || $order_point>100) {
            if($this->lang_id==0){
                $this->setData(array(),'0','请输入合理订单获取睿积分百分比');
            }else{
                $this->setData(array(),'0','Please enter a reasonable order to get the percentage of the currency');
            }
        }
        if (!preg_match("/^[0-9]*$/",$point_price) || $order_point>100) {
            if($this->lang_id==0){
                $this->setData(array(),'0','请输入合理睿积分兑换百分比');
            }else{
                $this->setData(array(),'0','Please enter a reasonable currency exchange percentage');
            }
        }
        $data=array(
            'store_id'=>$store_id,
            'order_point'=>$order_point,
            'add_time'=>time(),
            'point_price'=>$point_price,
        );

        if (empty($point_id)){
            $res = $this->storePointMod->doInsert($data);
        } else {
            $res = $this->storePointMod->doEdit($point_id, $data);
        }
        if($res){
            $this->setData(array("url"=>"?app=areaStore&act=index&lang_id=".$this->lang_id), '0',$this->langData['save_success']);
        }
    }



}