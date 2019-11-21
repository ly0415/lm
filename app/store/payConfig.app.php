<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/24
 * Time: 19:17
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class payConfigApp extends BaseStoreApp{

    private $payConfigMod;
    private $payDetailMod;
    private $lang_id;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->payConfigMod = &m('payConfig');
        $this->payDetailMod = &m('payDetail');
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
    }
    /**
     * 支付方式配置页面
     * 构造函数
     * @auther wanyan
     */
    public function index(){
        $sql = "select * from ".DB_PREFIX."pay where  `store_id` = $this->storeId";
        $rs = $this->payConfigMod->querySql($sql);
        foreach ($rs as $k=>$v){
            switch ($v['is_use']){
                case '1':
                    $rs[$k]['use_name'] = '启用';
                    break;
                case '2':
                    $rs[$k]['use_name'] = '停用';
                    break;
            }
            switch ($v['platform']){
                case '1':
                    $rs[$k]['platformName'] = 'PC端';
                    break;
                case '2':
                    $rs[$k]['platformName'] = '手机端';
                    break;
                case '3':
                    $rs[$k]['platformName'] = '微信端';
                    break;
            }
        }
        $this->assign('lang_id',$this->lang_id);
        $this->assign('payInfo',$rs);
        $this->display('pay/index.html');
    }
    /**
     * 支付方式编辑页面
     * 构造函数
     * @auther wanyan
     */
    public function edit(){
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '0';
        $sql = "select p.*,pd.* from ".DB_PREFIX."pay as p left join ".DB_PREFIX."pay_detail AS pd ON pd.pay_id = p.id where p.`id` = ".$id;
        $rs = $this->payConfigMod->querySql($sql);
        foreach ($rs as $k=>$v){
            $canshu[$v['mkey']] = $v['key_name'];
        }
//        var_dump($canshu);die;
        $this->assign('payInfo',$rs[0]);
        $this->assign('canshu',$canshu);
        $this->assign('lang_id',$this->lang_id);
        $this->assign('act','index');
        $this->display('pay/edit.html');
    }
    /**
     * 支付方式编辑页面
     * 构造函数
     * @auther wanyan
     */
    public function doEdit(){
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '0';
        $code = !empty($_REQUEST['code']) ? htmlspecialchars($_REQUEST['code']) : '';
        $pay_desc = !empty($_REQUEST['desc']) ? htmlspecialchars($_REQUEST['desc']) : '';
        $sort = !empty($_REQUEST['sort']) ? intval($_REQUEST['sort']) : '0';
        $is_use = !empty($_REQUEST['is_use']) ? intval($_REQUEST['is_use']) : '0';
        $pay_name = !empty($_REQUEST['pay_name']) ? htmlspecialchars($_REQUEST['pay_name']) : '';
        if(empty($pay_name)){
            $this->setData($info=array(),$status=0,$message="支付方式名称不能空！");
        }else{
            $query=array(
                'cond'=>"`id` !='{$id}' and `store_id` = $this->storeId and `pay_name` = '{$pay_name}'",
                'fields'=>"`pay_name`"
            );
            $r = $this->payConfigMod->getOne($query);
            if($r){
                $this->setData($info=array(),$status=0,$message="支付方式名称已存在");
            }
        }
        if($code == "chanpay"){
            $chanpay_account = !empty($_REQUEST['chanpay_account']) ? htmlspecialchars(trim($_REQUEST['chanpay_account'])) : '';
            $chanpay_privatekey = !empty($_REQUEST['chanpay_privatekey']) ? htmlspecialchars(trim($_REQUEST['chanpay_privatekey'])) : '';
            $chanpay_publickey = !empty($_REQUEST['chanpay_publickey']) ? htmlspecialchars(trim($_REQUEST['chanpay_publickey'])) : '';
            if(empty($chanpay_account)){
                $this->setData($info=array(),$status=0,$message="畅捷支付账号不能空！");
            }
            if(empty($chanpay_privatekey)){
                $this->setData($info=array(),$status=0,$message="畅捷支付商户密钥不能空！");
            }
            if(empty($chanpay_publickey)){
                $this->setData($info=array(),$status=0,$message="畅捷支付商户公钥不能空！");
            }
            $Info = array(
                'chanpay_account' =>$chanpay_account,
                'chanpay_privatekey' =>$chanpay_privatekey,
                'chanpay_publickey' =>$chanpay_publickey
            );
        }elseif ($code == "alipay"){
            $alipay_account = !empty($_REQUEST['alipay_account']) ? htmlspecialchars(trim($_REQUEST['alipay_account'])) : '';
            $alipay_PID = !empty($_REQUEST['alipay_PID']) ? htmlspecialchars(trim($_REQUEST['alipay_PID'])) : '';
            $alipay_KEY = !empty($_REQUEST['alipay_KEY']) ? htmlspecialchars(trim($_REQUEST['alipay_KEY'])) : '';
            if(empty($alipay_account)){
                $this->setData($info=array(),$status=0,$message="商家支付宝账号不能空！");
            }
            if(empty($alipay_PID)){
                $this->setData($info=array(),$status=0,$message="合作者身份不能空！");
            }
            if(empty($alipay_KEY)){
                $this->setData($info=array(),$status=0,$message="安全校验码不能空！");
            }
            $Info = array(
                'alipay_account' =>$alipay_account,
                'alipay_PID' =>$alipay_PID,
                'alipay_KEY' =>$alipay_KEY
            );
        }elseif ($code == "weixin"){
            $weixin_APPID = !empty($_REQUEST['weixin_APPID']) ? htmlspecialchars(trim($_REQUEST['weixin_APPID'])) : '';
            $weixin_APPSECRET = !empty($_REQUEST['weixin_APPSECRET']) ? htmlspecialchars(trim($_REQUEST['weixin_APPSECRET'])) : '';
            $weixin_account = !empty($_REQUEST['weixin_account']) ? htmlspecialchars(trim($_REQUEST['weixin_account'])) : '';
            $weixin_KEY = !empty($_REQUEST['weixin_KEY']) ? htmlspecialchars(trim($_REQUEST['weixin_KEY'])) : '';
            if(empty($weixin_APPID)){
                $this->setData($info=array(),$status=0,$message="AppID不能空！");
            }
            if(empty($weixin_APPSECRET)){
                $this->setData($info=array(),$status=0,$message="APPSecret不能空！");
            }
            if(empty($weixin_account)){
                $this->setData($info=array(),$status=0,$message="商户号不能空！");
            }
            if(empty($weixin_KEY)){
                $this->setData($info=array(),$status=0,$message="交易安全密钥不能空！");
            }
            $Info = array(
                'weixin_APPID' =>$weixin_APPID ,
                'weixin_APPSECRET' =>$weixin_APPSECRET ,
                'weixin_account' =>$weixin_account,
                'weixin_KEY'    =>$weixin_KEY
            );
        }elseif ($code == "paypal"){
            $paypal_APPID = !empty($_REQUEST['paypal_APPID']) ? htmlspecialchars(trim($_REQUEST['paypal_APPID'])) : '';
            $paypal_APPSECRET = !empty($_REQUEST['paypal_APPSECRET']) ? htmlspecialchars(trim($_REQUEST['paypal_APPSECRET'])) : '';
//            $paypal_account = !empty($_REQUEST['paypal_account']) ? htmlspecialchars(trim($_REQUEST['paypal_account'])) : '';
//            $paypal_KEY = !empty($_REQUEST['paypal_KEY']) ? htmlspecialchars(trim($_REQUEST['paypal_KEY'])) : '';
            if(empty($paypal_APPID)){
                $this->setData($info=array(),$status=0,$message="AppID不能空！");
            }
            if(empty($paypal_APPSECRET)){
                $this->setData($info=array(),$status=0,$message="APPSecret不能空！");
            }
//            if(empty($paypal_account)){
//                $this->setData($info=array(),$status=0,$message="商户号不能空！");
//            }
//            if(empty($paypal_KEY)){
//                $this->setData($info=array(),$status=0,$message="交易安全密钥不能空！");
//            }
            $Info = array(
                'paypal_APPID' =>$paypal_APPID ,
                'paypal_APPSECRET' =>$paypal_APPSECRET ,
//                'paypal_account' =>$paypal_account,
//                'paypal_KEY'    =>$paypal_KEY
            );
        }
        $edit_pay_data=array(
            'pay_name' =>$pay_name,
            'pay_desc' =>$pay_desc,
            'sort'     =>$sort,
            'is_use' =>$is_use
        );
        $rs = $this->payConfigMod->doEdit($id,$edit_pay_data);
        if($rs){
            $this->payDetailMod->doDelete(array('cond'=>"`pay_id`='{$id}'"));
            foreach ($Info as $k=>$v){
                $res[] =  $this->payDetailMod->doInsert(array('pay_id'=>$id,'mkey'=>$k,'key_name'=>$v,'add_time'=>time()));
            }
        }
        $res = array_filter($res);
        if(count($res)){
            $info['url'] = "?app=payConfig&act=index&lang_id=$this->lang_id";
            $this->setData($info,$status=1,$message="添加成功！");
        }else{
            $this->setData($info=array(),$status=0,$message="添加失败！");
        }
    }

}