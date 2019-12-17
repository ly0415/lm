<?php

/**
 * 充值睿积分确认页面
**/
class pointConfirmApp extends BaseFrontApp
{

    private $userMod;
    private $pointMod;
    private $pointOrderMod;


    public function __construct()
    {
        parent::__construct();
        $this->userMod =& m('user');
        $this->pointMod =& m('point');
        $this->pointOrderMod=&m('pointOrder');

    }
    //支付选择页面
    public function index(){
        $this->load($this->shorthand, 'comfirmOrder/index');
        $a = $this->langData;
        $order_id = !empty($_REQUEST['order_id']) ? htmlspecialchars($_REQUEST['order_id']) : '';
        $storeid = !empty($_REQUEST['storeid']) ? htmlspecialchars($_REQUEST['storeid']) : '';
        $lang = !empty($_REQUEST['lang']) ? htmlspecialchars($_REQUEST['lang']) : '';
        $list = $this->pointOrderMod->getOne(array('cond' => "`order_sn` ='{$order_id}'", 'fields' => "order_sn,amount,id,point"));
        if (empty($order_id)) {
            $data['message'] = $a['Payment_parameters'];
            $this->error_404($data, 'public/error.html');
        } else {
            if (empty($list['order_sn'])) {
                $data['message'] = $a['order_order_sn'];
                $this->error_404($data, 'public/error.html');
            } elseif ($list['status'] == 1) {
                $data['message'] = $a['order_repeat'];
                $this->error_404($data, 'public/error.html');
            }
        }
        $this->assign('list', $list);
        $this->assign('storeid', $storeid);
        $this->assign('lang', $lang);
        $this->assign('langdata', $this->langData);
        $this->assign('order_id', $list['id']);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $this->display('pointConfirm/surePoint.html');
        }
    //睿积分订单生成
    public function point_order(){
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $a=$this->langData;
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;
        $lang=$_REQUEST['lang'];
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $rate=!empty($_REQUEST['rate']) ? intval($_REQUEST['rate']):'0';
        $point_num=!empty($_REQUEST['point_num'])?$_REQUEST['point_num']:'0';
        $amount=number_format(($point_num/$rate), 2, '.', '');
        $userid=!empty($_REQUEST['userid']) ? $_REQUEST['userid'] : $this->userId ;
        $rand = $this->buildNo(1);
        $orderNo = date('YmdHis') . $rand[0];
        if(empty($point_num)){
            $this->setData($info=array(), $status = 0, $a['rui_num']);
        }
        if(!preg_match("/^[1-9][0-9]*$/",$point_num)) {
            $this->setData($info=array(),$status=0,$a['rui_z']);
            }


        $orderData=array(
            'amount'=>$amount,
            'point'=>$point_num,
            'order_sn'=>$orderNo,
            'status'=>0,
            'add_time'=>time(),
            'buyer_id'=>$userid
        );
        $res=$this->pointOrderMod->doInsert($orderData);
        if($res){
            $info['url'] = "?app=pointConfirm&act=index&order_id={$orderNo}&storeid={$storeid}&lang={$lang}&auxiliary={$auxiliary}";
            $this->setData($info, $status = 1, $a['rui_success']);
            }else {
            $this->setData($info=array(), $status = 0, $a['rui_fail']);
        }

    }
    //订单号生成
    public function buildNo($limit) {
        $begin = pow(10, 3);
        $end = (pow(10, 4) - 1);
        $rand_array = range($begin, $end);
        shuffle($rand_array); //调用现成的数组随机排列函数
        return array_slice($rand_array, 0, $limit); //截取前$limit个
    }

    public  function addPointLog($username,$note,$userid,$deposit,$expend){

        if(empty($this->accountName)){
            $accountName='--';
        }
        $logData = array(
            'operator' => $accountName,
            'username' => $username,
            'add_time' => time(),
            'deposit'=>$deposit,
            'expend'=>$expend,
            'note'=>$note,
            'userid'=>$userid
        );
        $pointLogMod = &m("pointLog");
        $pointLogMod->doInsert($logData);
    }

}