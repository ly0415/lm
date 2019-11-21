<?php

/**
 * 支付选择页面
 * @author wanyan
 * @date 2017/07/19
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class OrderPayApp extends BaseFrontApp {

    private $orderMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->orderMod = &m('order');
    }

    /**
     * 支付页面展示
     * @author wanyan
     * @date 2017/07/19
     */
    public function index() {
        //加载语言包
        $this->load($this->shorthand, 'comfirmOrder/index');
        $a = $this->langData;
        $order_id = !empty($_REQUEST['order_id']) ? htmlspecialchars($_REQUEST['order_id']) : '';
        $storeid = !empty($_REQUEST['storeid']) ? htmlspecialchars($_REQUEST['storeid']) : '';
        $lang = !empty($_REQUEST['lang']) ? htmlspecialchars($_REQUEST['lang']) : '';
        $user_id = $this->userId;
        $userMod = &m('user');
        $user_info = $userMod->getOne(array("cond" => "id=" . $user_id));
        $point = empty($user_info['point']) ? 0 : $user_info['point'];
        $list = $this->orderMod->getOne(array('cond' => "`order_sn` ='{$order_id}'", 'fields' => "order_sn,order_amount,order_id,pd_amount,cp_amount"));
        if (empty($order_id)) {
            $data['message'] = $a['Payment_parameters'];
            $this->error_404($data, 'public/error.html');
        } else {
            if (empty($list['order_sn'])) {
                $data['message'] = $a['order_order_sn'];
                $this->error_404($data, 'public/error.html');
            } elseif ($list['order_state'] == 20) {
                $data['message'] = $a['order_repeat'];
                $this->error_404($data, 'public/error.html');
            }
        }
        $sql="select c_id from ".DB_PREFIX.'user_coupon where user_id='.$this->userId.' and store_id= '.$storeid;

        $info = $this->orderMod->querySql($sql);
        foreach($info as $key=>$val){
            $cIds[]=$val['c_id'];
        }
        $cIds=implode(',',$cIds);
        $cSql="select * from ".DB_PREFIX. 'coupon  where id in ('.$cIds.') and start_time < '.time() .' and end_time > '.time();
        $cData = $this->orderMod->querySql($cSql);
        if($list['order_amount']<=0){
            $list['order_amount']=0.01;
        }
        $this->assign('cData',$cData);
        $this->assign('point', $point);
        $this->assign('list', $list);
        $this->assign('storeid', $storeid);
        $this->assign('lang', $lang);
        $lang_id = $this->shorthand;
        $this->assign('lang_id', $lang_id);
        $this->assign('langdata', $this->langData);
        $this->assign('order_id', $list['order_id']);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $this->display('native/orderPay.html');
    }

}
