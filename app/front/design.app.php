<?php
/**
 * 手机app
 * @author lvji
 * @date 2015-3-10
 */
if (!defined('IN_ECM')) {die('Forbidden');}
class DesignApp extends BaseFrontApp {
    /**
     * 构造函数
     */
    private  $appkey;
    private  $appsecret;
    private  $appuid;
    private  $userMod;
    public  $storeid;
    public  $langid;
    public  $userInfo;

    public function __construct() {
        parent::__construct();
        date_default_timezone_set("Asia/Shanghai");
        $this->appkey="JfhoNTZMdz";
        $this->appsecret="8yAqQ8SPET6gAmDZdZJ8uYkWzdWRpgFz";
        $this->appuid =$this->userId;
        $this->userMod = &m('user');
        $this->storeid = !empty($_REQUEST['storeid']) ? intval($_REQUEST['storeid']) : '';
        $this->langid = !empty($_REQUEST['lang']) ? intval($_REQUEST['lang']) : '';
    }

    /**
     *  检用户是否登录
    */
    public function is_login(){
        $pageUrl = urlencode($_SERVER['HTTP_REFERER']);
        if(!isset($this->userId)){
            $url = "?app=user&act=login&storeid={$this->storeid}&langid= $this->langid&pageUrl=".$pageUrl;
            header("Location:".$url);
        }else{
            $this->userInfo = $this->userMod->getOne(array('cond'=>"`id` = $this->userId",'fields'=>"`username`,phone,email"));
        }
    }
    /**
     * 授权登录页面
     * @author wanyan
     * @date 2015-3-10
     */
    public function index(){
        $this->is_login();
       // 酷家乐授权登录
       $url = $this->loginInfo();
       $data = json_decode($url,true);
       if($data['errorCode'] == 0){
           $this->assign('authUrl',$data['errorMsg']);
       }else{
           echo "授权失败！请联系服务商！";die;
       }
       $this->assign('storeid',$this->storeid);
       $this->assign('lang',$this->langid);
       $this -> load($this -> shorthand,'design/index');
       $this->assign('langdata', $this->langData);
       $this->display('design/design_index.html');
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
       $hSign = $this->appsecret.$this->appkey.$this->userId.$timestamp;
       $sign = md5($hSign);

       $data['sign'] = $sign;
       $data['appuname']    = $this->userInfo['username'];
       $data['appuemail']   = $this->userInfo['email'];
       $data['appuphone']   = $this->userInfo['phone'];
       $data['appussn'] ='';
       $data['appuaddr'] ='';
       $data['appuavatar']= '';
       $data['appuid']   = $this->userId;
       $data['dest']     = 0;
       $url = "http://www.kujiale.com/p/openapi/login";
//       $url .= "timestamp={$timestamp}&appkey={$appkey}&sign={$sign}";
//       $url .= "&appuid=1&appuname={$appuname}&appuemail={$appuemail}&appuphone={$appuphone}&appussn={$appussn}&appuaddr={$appuaddr}&appuavatar={$appuavatar}";
        $res = $this->request_post($url,$ispost=true,$data);
         return  $res;
    }
    /**
     * @return  $floorplan 返回户型数据
     * @author wanyan
     * @date 2015-3-10
     */
    public function getFloorPlan(){
        $appkey =$this->appkey;
        $timestamp =$this->get_total_millisecond(); //long 13位
        $hSign = $this->appsecret.$this->appkey.$this->userId.$timestamp;
        $sign = md5($hSign);
        $url = "http://www.kujiale.com/p/openapi/user/floorplan?";
        $url .= "timestamp={$timestamp}&appkey={$appkey}&sign={$sign}";
        $url .= "&start=1&num=10&appuid=".$this->userId;
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
        $this->assign('storeid',$this->storeid);
        $this->assign('langid',$this->langid);
        $floorPlan = $this->getFloorPlan();
        $this->assign('floorPlan',$floorPlan);
        $this -> load($this -> shorthand,'design/index');
        $this->assign('langdata', $this->langData);
        $this->display('design/floor_index.html');
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
       $hSign = $this->appsecret.$this->appkey.$this->userId.$timestamp;
       $sign = md5($hSign);
       $data['sign'] = $sign;
       $data['appuname'] = $this->userInfo['username'];
       $data['appuemail'] = $this->userInfo['email'];
       $data['appuphone'] =$this->userInfo['phone'];
       $data['appussn'] ='';
       $data['appuaddr'] ='';
       $data['appuavatar']= '';
       $data['appuid']   = $this->userId;
       $data['dest']     = 2;
       $data['planid']   = $obsPlanId;
       $url = "http://www.kujiale.com/p/openapi/login";
       $res = $this->request_post($url,$ispost=true,$data);
       $errmsg =json_decode($res,true);
       $this->assign('storeid',$this->storeid);
       $this->assign('langid',$this->langid);
       $this->assign('errormsg',$errmsg['errorMsg']);
       $this -> load($this -> shorthand,'design/index');
       $this->assign('langdata', $this->langData);
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
        $hSign = $this->appsecret.$this->appkey.$this->userId.$timestamp;
        $sign = md5($hSign);
        $url = "http://www.kujiale.com/p/openapi/user/design?";
        $url .= "timestamp={$timestamp}&appkey={$appkey}&sign={$sign}";
        $url .= "&start=1&num=10&appuid=".$this->userId;
        $floorPlan =file_get_contents($url);
        $floorPlan =json_decode($floorPlan,true);

        $this->assign('storeid',$this->storeid);
        $this->assign('langid',$this->langid);
        $this->assign('floorDesign',$floorPlan);
        $this -> load($this -> shorthand,'design/index');
        $this->assign('langdata', $this->langData);
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
        $hSign = $this->appsecret.$this->appkey.$this->userId.$timestamp;
        $sign = md5($hSign);
        $data['sign'] = $sign;
        $data['appuname'] = $this->userInfo['username'];
        $data['appuemail'] = $this->userInfo['email'];
        $data['appuphone'] =$this->userInfo['phone'];
        $data['appussn'] ='';
        $data['appuaddr'] ='';
        $data['appuavatar']= '';
        $data['appuid']   = $this->userId;
        $data['dest']     = 1;
        $data['designid']   = $obsDesignId;
        $url = "http://www.kujiale.com/p/openapi/login";
        $res = $this->request_post($url,$ispost=true,$data);
        $errmsg =json_decode($res,true);
        $this->assign('errormsg',$errmsg['errorMsg']);
        $this -> load($this -> shorthand,'design/index');
        $this->assign('langdata', $this->langData);
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

    /**
     * 户型查询页面
     * @author wanyan
     * @date 2018-2-27
     */
    public function apartSearch(){
       $url = $this->Auth();
       $result = file_get_contents($url);
       $areas = json_decode($result,true);
       // 热门城市
       $hotCity[] = $areas[0]['cities'][0];
       $hotCity[] = $areas[8]['cities'][0];
       $hotCity[] = $areas[10]['cities'][0];
       $hotCity[] = $areas[18]['cities'][0];
       $this->assign('hotCity',$hotCity);
       unset($areas[0]);
       unset($areas[8]);
       unset($areas[10]['cities'][0]);
       unset($areas[18]['cities'][0]);
       $this->assign('areas',$areas);
       $this -> load($this -> shorthand,'design/index');
       $this->assign('langdata', $this->langData);
       $this->assign('store_id',$this->storeid);
       $this->assign('lang_id',$this->langid);
       $this->display('design/design_new_index.html');
    }
    /**
     * 获取酷家乐查询户型的授权参数
     * @author wanyan
     * @date 2017-1-23
     */
    public function Auth(){
        $timestamp = $this->get_total_millisecond();
        $sign = md5($this->appsecret.$this->appkey.$timestamp);
        $getUrl = "http://www.kujiale.com/p/openapi/city";
        $getUrl .="?timestamp={$timestamp}";
        $getUrl .="&appkey={$this->appkey}";
        $getUrl .="&sign={$sign}";
        return $getUrl;
    }
    /**
     * 获取酷家乐城市接口
     * @author wanyan
     * @date 2017-1-23
     */
    public function getCityList(){
        $url = $this->Auth();
        $result = file_get_contents($url);
        var_dump(json_decode($result,true));exit();
    }
    /**
     * 获取酷家乐户型数据
     * @author wanyan
     * @date 2017-1-23
     */
    public function getHuXing(){
        $q = !empty($_REQUEST['q']) ? htmlspecialchars($_REQUEST['q']) :'';
        $city_id = !empty($_REQUEST['cityId']) ? intval($_REQUEST['cityId']) :'0';
//        $city_id = 162;
        $timestamp = $this->get_total_millisecond();
        $sign = md5($this->appsecret.$this->appkey.$timestamp);
        $getUrl = "http://www.kujiale.com/p/openapi/floorplan";
        $getUrl .="?timestamp={$timestamp}";
        $getUrl .="&appkey={$this->appkey}";
        $getUrl .="&sign={$sign}";
        $getUrl .="&q={$q}";
        $getUrl .="&start=0";
        $getUrl .="&num=6";
        $getUrl .="&cityid={$city_id}";
        $result = file_get_contents($getUrl);
        echo $result;die;

    }
    /**
     * 获取酷家乐户型创建
     * @author wanyan
     * @date 2018-2-27
     */
    public function searchCreate(){
        $plan_id = !empty($_REQUEST['plan_id']) ? htmlspecialchars($_REQUEST['plan_id']) :'';
        $hx = !empty($_REQUEST['hx']) ? htmlspecialchars($_REQUEST['hx']) :'';
        $xq = !empty($_REQUEST['xq']) ? htmlspecialchars($_REQUEST['xq']) :'';
        $imgUrl = !empty($_REQUEST['imgUrl']) ? htmlspecialchars($_REQUEST['imgUrl']) :'';
        $city = !empty($_REQUEST['city']) ? htmlspecialchars($_REQUEST['city']) :'';
        $obsUserId = !empty($_REQUEST['obsUserId']) ? htmlspecialchars($_REQUEST['obsUserId']) :'';
        if(empty($plan_id)){
            return false;
        }
        $this->assign('plan_id',$plan_id);
        $this->assign('hx',$hx);
        $this->assign('xq',$xq);
        $this->assign('imgUrl',$imgUrl);
        $this->assign('city',$city);
        $this->assign('obsUserId',$obsUserId);
        $this -> load($this -> shorthand,'design/index');
        $this->assign('langdata', $this->langData);
        $this->assign('store_id',$this->storeid);
        $this->assign('lang_id',$this->langid);
        $this->display('design/design_new_create.html');
    }

    /**
     * 编辑非指定用户方案副本ID
     * @author wanyan
     * @date 2015-3-10
     */
    public function planCopy($obsPlanId){
        $timestamp =$this->get_total_millisecond(); //long 13位
        $hSign = $this->appsecret.$this->appkey.$this->userId.$timestamp;
        $sign = md5($hSign);
        $url = "http://www.kujiale.com/p/openapi/floorplan/".$obsPlanId.'/copy?';
        $url .= "timestamp={$timestamp}&appkey={$this->appkey}&sign={$sign}";
        $url .= "&appuid=".$this->userId;
        $planId =file_get_contents($url);
        return $planId;
    }


    /**
     * 编辑非指定用户方案装修数据
     * @author wanyan
     * @date 2015-3-10
     */
    public function floorEdit(){
        $this->is_login();
        $obsPlanId = !empty($_REQUEST['obsPlanId']) ? htmlspecialchars($_REQUEST['obsPlanId']):'';
        $copyPlanId = $this->planCopy($obsPlanId);
        $data['appkey'] =$this->appkey;
        $timestamp =$this->get_total_millisecond(); //long 13位
        $data['timestamp '] = $timestamp;
        $hSign = $this->appsecret.$this->appkey.$this->userId.$timestamp;
        $sign = md5($hSign);
        $data['sign'] = $sign;
        $data['appuname'] = $this->userInfo['username'];
        $data['appuemail'] = $this->userInfo['email'];
        $data['appuphone'] =$this->userInfo['phone'];
        $data['appussn'] ='';
        $data['appuaddr'] ='';
        $data['appuavatar']= '';
        $data['appuid']   = $this->userId;
        $data['dest']     = 2;
        $data['planid']   = $copyPlanId;
        $url = "http://www.kujiale.com/p/openapi/login";
        $res = $this->request_post($url,$ispost=true,$data);
        $errmsg =json_decode($res,true);
        $this->assign('errormsg',$errmsg['errorMsg']);
        $this -> load($this -> shorthand,'design/index');
        $this->assign('langdata', $this->langData);
        $this->display('design/floorEdit.html');
    }



}
?>