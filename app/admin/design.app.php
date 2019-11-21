<?php
/**
 * 手机app
 * @author lvji
 * @date 2015-3-10
 */
if (!defined('IN_ECM')) {die('Forbidden');}
class DesignApp extends BackendApp {
    /**
     * 构造函数
     */
    private  $appkey;
    private  $appsecret;
    private  $appuid;
    public function __construct() {
        parent::__construct();
        date_default_timezone_set("Asia/Shanghai");
        $this->appkey="JfhoNTZMdz";
        $this->appsecret="8yAqQ8SPET6gAmDZdZJ8uYkWzdWRpgFz";
        $this->appuid =$this->adminId;
    }
    /**
     * 授权登录页面
     * @author wanyan
     * @date 2015-3-10
     */
    public function index(){
       $this->display('design/index.html');
    }
    /**
     * 授权登录
     * @author wanyan
     * @date 2015-3-10
     */
    public function login(){
        $url = $this->loginInfo();
        $data = json_decode($url,true);
        echo json_encode($data);die;
    }
    /**
     * 签名认证
     * @author wanyan
     * @date 2015-3-10
     */
    public function authUrl(){

    }
    /**
     * 请求参数
     * @author wanyan
     * @date 2015-3-10
     */
    public function loginInfo(){
       $data['appkey'] =$this->appkey;
       $timestamp =$this->get_total_millisecond(); //long 13位
       $data['timestamp '] = $timestamp;
       $hSign = $this->appsecret.$this->appkey.'1'.$timestamp;
       $sign = md5($hSign);
       $data['sign'] = $sign;
       $data['appuname'] = "wanyan";
       $data['appuemail'] = '2428728383@qq.com';
       $data['appuphone'] ='15955055467';
       $data['appussn'] ='341124198910254838';
       $data['appuaddr'] ='安徽省全椒县';
       $data['appuavatar']= '';
       $data['appuid']   = 1;
       $data['dest']     = 0;
       $url = "http://www.kujiale.com/p/openapi/login";
//       $url .= "timestamp={$timestamp}&appkey={$appkey}&sign={$sign}";
//       $url .= "&appuid=1&appuname={$appuname}&appuemail={$appuemail}&appuphone={$appuphone}&appussn={$appussn}&appuaddr={$appuaddr}&appuavatar={$appuavatar}";
        $res = $this->request_post($url,$ispost=true,$data);
         return  $res;
    }
    /**
     * 获取当前用户的户型图数据
     * @author wanyan
     * @date 2015-3-10
     */
    public function getFloorPlan(){
        $appkey =$this->appkey;
        $timestamp =$this->get_total_millisecond(); //long 13位
        $hSign = $this->appsecret.$this->appkey.'1'.$timestamp;
        $sign = md5($hSign);
        $url = "http://www.kujiale.com/p/openapi/user/floorplan?";
        $url .= "timestamp={$timestamp}&appkey={$appkey}&sign={$sign}";
        $url .= "&start=1&num=10&appuid=1";
        $floorPlan =file_get_contents($url);
        $floorPlan =json_decode($floorPlan,true);
        return $floorPlan;
    }
    /**
     * 获取当前用户的户型图数据页面
     * @author wanyan
     * @date 2015-3-10
     */
    public function floorPlan(){
        $floorPlan = $this->getFloorPlan();
        $this->assign('floorPlan',$floorPlan);
        $this->display('design/floorPlan.html');
    }
    /**
     * 编辑当前用户的户型图数据页面
     * @author wanyan
     * @date 2015-3-10
     */
    public function floorPlanEdit(){
       $obsPlanId = !empty($_REQUEST['obsPlanId']) ? htmlspecialchars($_REQUEST['obsPlanId']):'';
       $data['appkey'] =$this->appkey;
       $timestamp =$this->get_total_millisecond(); //long 13位
       $data['timestamp '] = $timestamp;
       $hSign = $this->appsecret.$this->appkey.'1'.$timestamp;
       $sign = md5($hSign);
       $data['sign'] = $sign;
       $data['appuname'] = "wanyan";
       $data['appuemail'] = '2428728383@qq.com';
       $data['appuphone'] ='15955055467';
       $data['appussn'] ='341124198910254838';
       $data['appuaddr'] ='安徽省全椒县';
       $data['appuavatar']= '';
       $data['appuid']   = 1;
       $data['dest']     = 2;
       $data['planid']   = $obsPlanId;
       $url = "http://www.kujiale.com/p/openapi/login";
       $res = $this->request_post($url,$ispost=true,$data);
       $errmsg =json_decode($res,true);
       $this->assign('errormsg',$errmsg['errorMsg']);
       $this->display('design/floorPlanEdit.html');

    }
    /**
     * 获取方案装修数据
     * @author wanyan
     * @date 2015-3-10
     */
    public function floorProgramme(){
        $appkey =$this->appkey;
        $timestamp =$this->get_total_millisecond(); //long 13位
        $hSign = $this->appsecret.$this->appkey.'1'.$timestamp;
        $sign = md5($hSign);
        $url = "http://www.kujiale.com//p/openapi/user/design?";
        $url .= "timestamp={$timestamp}&appkey={$appkey}&sign={$sign}";
        $url .= "&start=1&num=10&appuid=1";
        $floorPlan =file_get_contents($url);
        $floorPlan =json_decode($floorPlan,true);
        $this->assign('floorDesign',$floorPlan);
        $this->display('design/floorProgrammeIndex.html');
    }
    /**
     * 编辑方案装修数据
     * @author wanyan
     * @date 2015-3-10
     */
    public function floorProgrammeEdit(){
        $obsDesignId = !empty($_REQUEST['obsDesignId']) ? htmlspecialchars($_REQUEST['obsDesignId']):'';
        $data['appkey'] =$this->appkey;
        $timestamp =$this->get_total_millisecond(); //long 13位
        $data['timestamp '] = $timestamp;
        $hSign = $this->appsecret.$this->appkey.'1'.$timestamp;
        $sign = md5($hSign);
        $data['sign'] = $sign;
        $data['appuname'] = "wanyan";
        $data['appuemail'] = '2428728383@qq.com';
        $data['appuphone'] ='15955055467';
        $data['appussn'] ='341124198910254838';
        $data['appuaddr'] ='安徽省全椒县';
        $data['appuavatar']= '';
        $data['appuid']   = 1;
        $data['dest']     = 1;
        $data['designid']   = $obsDesignId;
        $url = "http://www.kujiale.com/p/openapi/login";
        $res = $this->request_post($url,$ispost=true,$data);
        $errmsg =json_decode($res,true);
        $this->assign('errormsg',$errmsg['errorMsg']);
        $this->display('design/floorProgrammeEdit.html');
    }

    /**
     * 请求方式
     * @author wanyan
     * @date 2015-3-10
     */
    public function get_total_millisecond()
    {
        $time = explode (" ", microtime () );
        $time = $time [1] . ($time [0] * 1000);
        $time2 = explode ( ".", $time );
        $time = $time2 [0];
        return $time;
    }
    /**
     * 请求方式
     * @author wanyan
     * @date 2015-3-10
     */
    public function request_post($url = '',$ispost=true, $post_data = array()) {
           if (empty($url) || empty($post_data)) {
               return false;
           }
           header("Content-type: text/html; charset=utf-8");
           $ch = curl_init();//初始化curl
           curl_setopt($ch, CURLOPT_URL,$url);//抓取指定网页
           curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
           curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
           if($ispost){
               curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
               curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
           }
           $data = curl_exec($ch);//运行curl
           curl_close($ch);
           return $data;
       }



}
?>