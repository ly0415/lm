<?php

/**
 * 手机APP
 * @author lvji
 * @date 2016-08-01
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class BaseWxApp extends BaseApp {

    public $userId, $userName;
    public $mrstoreid, $mrlangid, $mrstorecate; //默认站点
    public $storeid, $langid, $shorthand, $countryId;
    public $langData = array();   // 语言包数据
    public $symbol, $syshort;  //币种符号
    public $pagesize = 15; //每页显示多少个商品
    public $commpagesize = 5; // 商品评论的每页的显示条数
    public $location; //定位
    public $latlon;   //经纬度
    //微信的信息
    public $appId = 'wxb9ad06ed40bf2fda';
    public $appSecret = 'e8fbc6522ea3a1038a10cb6cbed3ba91';
    public $accessToken, $wx_openid;

    /**
     * 构造函数
     */
    public function __construct() {

        parent::__construct();

        if (strpos($_SERVER['HTTP_HOST'], 'njbsds')) {
            $this->appId = 'wx80d07d72079c04db';
            $this->appSecret = 'cb06dfe09354ada01688cea7173b3c45';
        }

        //授权回调
        if(empty($_COOKIE['wx_openid']) && $_GET['code'] ){
            $this->wxAuthCode();
        }

        //设置COOKIE
        if(empty($_COOKIE['wx_openid']) && $_GET['system_token'] && $_GET['system_openid'] ){
            $this->setSystemCookie();
        }

        //session失效重新赋值
        if($_COOKIE['wx_openid'] && empty($_SESSION['userId'])){
            //查询用户是否存在
            $userMod = &m('user');
            $userInfo = $userMod->getOne(array(
                'fields' => 'id',
                'cond' => "openid = '".$_COOKIE['wx_openid']."' and is_use = 1 AND mark =1"
            ));
            if( $userInfo ){   //快速登录
                $_SESSION['userId']     = $userInfo['id'];
                $_SESSION['userName']   = $userInfo['username'];
            }
        }

        if( $_SERVER['REQUEST_SCHEME'] == 'https' ){
            $this->assign('SITE_URL', SITE_URL_SSL);
            $this->assign('STATIC_URL', STATIC_URL_SSL);
        } else {
            $this->assign('SITE_URL', SITE_URL);
            $this->assign('STATIC_URL', STATIC_URL);
        }
        
        $this->assign('app', APP);
        $this->assign('act', ACT);

        //默认加载wx.config 参数
        require_once "jssdk.php";
        $jssdk = new JSSDK($this->appId, $this->appSecret);
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage', $signPackage);

        //获取客户端IP
        /*  $ipInfo = $this->GetIpLookup(); */

        //在没选择站点的情况下 根据 国家来获取 默认的站点
        if (empty($this->mrstoreid)) {
            if (!empty($ipInfo)) {
                $country = $ipInfo['country'];
            } else {
                $country = '中国';
            }
            $this->location = $country;
            //获取国家分类id（有默认的站点的国家），如果没定位到则获取有总代理的第一个国家
            $storecate = $this->getStorecateid($country);
            $storecateid = $storecate['id'];
            //获取总代理的站点为 默认站点 ，如果都没有则获取第一个站点的数据
            $mrdata = $this->getMrStore($storecateid);
            $this->mrstoreid = $mrdata['id'];
            $this->mrlangid = $mrdata['lang_id'];
        }

        //接受前台数据
        $this->storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;   //所选的站点id
        $this->langid = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //所选站点的语言
        //站点国家分类
        $storeCate = $this->getStoreCate();
        foreach ($storeCate as $k => $v) {
            //当前所选的同类站点
            if ($_SESSION['userId']) {
                $user_id = $_SESSION['userId'];
                $userMod = &m('user');
                $sql = 'SELECT *  FROM  ' . DB_PREFIX . 'user WHERE  id  =' . $user_id; //odm_members
                $datas = $userMod->querySql($sql);
                if ($datas[0]['odm_members'] == 0) {
                    $where = ' and s.store_type <4 ';
                } else {
                    $where = '';
                }
            } else {
                $where = ' and store_type <4 ';
            }
            $storeMod = &m('store');
            $sql = 'SELECT s.id,l.store_name,s.store_cate_id,s.lang_id,l.distinguish  FROM  ' . DB_PREFIX . 'store AS s
                 LEFT JOIN  ' . DB_PREFIX . 'store_lang AS l ON s.`id` = l.`store_id`
                  WHERE s.is_open = 1  and  store_cate_id =' . $v['id'] . ' and  l.lang_id =' . $this->langid . $where;
            $data = $storeMod->querySql($sql);
            $storeCate[$k]['store'] = $data;
        }
        $this->assign('storeCate', $storeCate);
        //当前所选的国家
        $curstoreid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;
        $curCountry = $this->getCurCountry($curstoreid);
        $this->countryId = $curCountry['cid'];
        $this->assign('curCountry', $curCountry);
        $this->assign('cid', $curCountry['cid']);
        //当前所选的同类站点
        $curstores = $this->getCurStores($curCountry['cid']);
        $this->assign('curstores', $curstores);
        //当前站点信息
        $auxiliary = !empty($_REQUEST['auxiliary']) ? $_REQUEST['auxiliary'] : '0';
        $curStoreInfo = $this->getCurStoreInfo($curstoreid, $auxiliary);
        $this->assign('curStoreInfo', $curStoreInfo);
        //语言数据
        $langs = $this->getLangs();
        $this->assign('langs', $langs);
        //经纬度
        $this->latlon = !empty($_REQUEST['latlon']) ? $_REQUEST['latlon'] : '0';
        $this->assign('latlon', $this->latlon);
        //当前语言信息
        $this->assign('defaultStore', $this->mrstoreid);
        $langinfo = $this->getShorthand($this->langid);
        $this->shorthand = $langinfo['shorthand'];
        $this->assign('langinfo', $langinfo);
        // 获取币种符号
        $syData = $this->getStoreSymbol($this->storeid);
        $this->symbol = $syData['symbol'];   // 符号
        $this->syshort = $syData['short'];  //简称

        $webconf = $this->webconf();
        $this->assign('webconf', $webconf);

        //搜索关键字
        $searchD = !empty($_REQUEST['gname']) ? $_REQUEST['gname'] : '';

        $this -> userId   = $_SESSION['userId'];
        $this -> userName = $_SESSION['userName'];
        $this -> assign('userId', $_SESSION['userId']);
        $this -> assign('userName', $_SESSION['userName']);

        $this->assign('searchD', $searchD);
        $this->assign('symbol', $this->symbol); //币种符号
        $this->assign('storeid', $this->storeid);
        $this->assign('lang', $this->langid);
        $this->assign('shorthand', $this->syshort);
        $this->assign('cart_count', $this->getCarNum());
        $this->assign('cart_total', $this->getCarTotal());
        //获取订单未付款数量
        $this->assign('dfkOrder', $this->dfkNum());
        //获取订单待发货数量
        $this->assign('dfhOrder', $this->dfhNum());
        //获取订单待收货数量
        $this->assign('dshOrder', $this->dshNum());
        //获取订单待评价数量
        $this->assign('dpjOrder', $this->dpjNum());
        //获取订单待退款数量
        $this->assign('dtkOrder', $this->dtkNum());


        //时间戳-精确到分
        $web_redis_time = strtotime(date('Y-m-d H:i', time()));
        $this->assign('web_redis_time', $web_redis_time);

        //加载当前控制器方法所对应的Css文件
        $this->viewCss($web_redis_time);

        //加载当前控制器方法所对应的Js文件
        $this->viewJs($web_redis_time);

    }

    /**
     * 校验登录接口
     * @author luffy
     * @date:  2018-08-21
     */
    private function wxAuthCode() {
        $code       = $_GET['code'];
        $data       = $this->get_access_token($code);//获取网页授权access_token和用户openid
        $data_all   = $this->get_user_info($data['access_token'], $data['openid']);//获取微信用户信息

        if( $_REQUEST['goods_tp'] == 1 ){
            $goods_url = '&storeid=' . $_REQUEST['storeid'] . '&lang=' . $_REQUEST['lang'] . '&latlon=' . $_REQUEST['latlon']. '&auxiliary=' . $_REQUEST['auxiliary']. '&source=' . $_REQUEST['source']. '&cid=' . $_REQUEST['cid']. '&gid=' . $_REQUEST['gid']. '&rtid=' . $_REQUEST['rtid'];
        } elseif( $_REQUEST['goods_tp'] == 2 ) {
            $goods_url = '&storeid=' . $_REQUEST['storeid'] . '&lang=' . $_REQUEST['lang'] . '&activityId=' . $_REQUEST['activityId']. '&goodsNum=' . $_REQUEST['goodsNum']. '&source=' . $_REQUEST['source']. '&activityGoodsId=' . $_REQUEST['activityGoodsId'];
        }

        //重新从入口文件进入项目
        $redirectUrl = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?app='.$_GET['back_app'].'&act='.$_GET['back_act'].'&system_token='.$data['access_token'].'&system_openid='.$data['openid'].'&wx_nickname='.$data_all['nickname'].'&wx_sex='.$data_all['sex'].'&wx_headimgurl='.$data_all['headimgurl'].$goods_url;
        header("Location:" . $redirectUrl);
        exit;
    }

    /**
     * 3、使用code换取access_token
     * @param string 用于换取access_token的code，微信提供
     * @return array access_token和用户openid数组
     * @author luffy
     * @date:  2018-08-21
     */
    private function get_access_token($code){
        $appid = $this->appId;
        $appsecret = $this->appSecret;
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $appid . '&secret=' . $appsecret . '&code=' . $code . '&grant_type=authorization_code';
        $user = json_decode(file_get_contents($url));
        if (isset($user->errcode)) {
            echo 'error:' . $user->errcode.'<hr>msg  :' . $user->errmsg;exit;
        }
        $data = json_decode(json_encode($user),true);//返回的json数组转换成array数组
        return $data;
    }

    /**
     * 4、使用access_token获取用户信息
     * @param string access_token
     * @param string 用户的openid
     * @return array 用户信息数组
     */
    private function get_user_info($access_token, $openid){
        $url = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $access_token . '&openid=' . $openid . '&lang=zh_CN';
        $user = json_decode(file_get_contents($url));
        if (isset($user->errcode)) {
            echo 'error:' . $user->errcode.'<hr>msg  :' . $user->errmsg;exit;
        }
        $data = json_decode(json_encode($user),true);//返回的json数组转换成array数组
        return $data;
    }

    /**
     * 校验登录接口
     * @author luffy
     * @date:  2018-08-21
     */
    private function setSystemCookie() {
        $expire = time() + 60*60*24*90;
        setcookie("wx_accessToken"  , $_GET['system_token'],   $expire);
        setcookie("wx_openid"       , $_GET['system_openid'],  $expire);
        setcookie("wx_nickname"    , $_GET['wx_nickname'],   $expire);
        setcookie("wx_headimgurl"    , $_GET['wx_headimgurl'],   $expire);
        setcookie("wx_sex"    , $_GET['wx_sex'],   $expire);
        //根据openid获取用户信息(1、绑定手机号直接进入  2、未绑定则进入快捷登录页面)
        $userMod    = &m('user');
        $user_info  = $userMod -> getUserInfo($_GET['system_openid']);
        if( $user_info['phone'] ){
            //登录的session信息
            $_SESSION['userId']     = $user_info['id'];
            $_SESSION['userName']   = $user_info['username'];
            if($_REQUEST['act']=='goodInfo'){
                $goods_url = '&storeid=' . $_REQUEST['storeid'] . '&lang=' . $_REQUEST['lang'] . '&latlon=' . $_REQUEST['latlon']. '&auxiliary=' . $_REQUEST['auxiliary']. '&source=' . $_REQUEST['source']. '&cid=' . $_REQUEST['cid']. '&gid=' . $_REQUEST['gid']. '&rtid=' . $_REQUEST['rtid'];
            } elseif($_REQUEST['app']=='goodList'){
                if( $_REQUEST['storeid'] != 58 ){
                    $sqlBank = '&order=1';
                }
                $goods_url = '&storeid=' . $_REQUEST['storeid'] . '&lang=' . $_REQUEST['lang'] . '&latlon=' . $_REQUEST['latlon']. '&auxiliary=' . $_REQUEST['auxiliary']. '&source=' . $_REQUEST['source']. '&rtid=' . $_REQUEST['rtid'] . $sqlBank;
            } elseif($_REQUEST['app']=='activityOrder'){
                $goods_url = '&storeid=' . $_REQUEST['storeid'] . '&lang=' . $_REQUEST['lang'] . '&activityId=' . $_REQUEST['activityId']. '&goodsNum=' . $_REQUEST['goodsNum']. '&source=' . $_REQUEST['source']. '&activityGoodsId=' . $_REQUEST['activityGoodsId'];
            }
            header('Location: wx.php?app='.$_GET['app'].'&act='.$_GET['act'].$goods_url);
            exit;
        } else {
            header('Location: wx.php?app=user&act=quickLogin');
            exit;
        }
    }

    /**
     * 普通登录校验
     * @author luffy
     * @date:  2018-08-21
     */
    public function ischeckLogin() {
        //判断是否登录
        if (!isset($_SESSION['userId']) && $_COOKIE['wx_openid']) {
            header('Location: wx.php?app=user&act=quickLogin');
            exit;
        }
    }

    public function getWxOpenid() {
        $page_url = $this->curPageURL();
        $url = $this->getOAuthUrl($page_url, 'snsapi_userinfo', 1);
        header("Location:" . $url);
    }

    public function checkUser($openid) {
        $userMod = &m('user');
        $res = $userMod->getOne(array("cond" => "openid='" . $openid . "' and mark =1"));
        if ($res) {
            $_SESSION['userName'] = $res['username'];
            $_SESSION['userId'] = $res['id'];
            $_SESSION['wx_openid'] = $openid;
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取微信的access_token
     *  get
     */
    public function getAccessToken() {
        $APPID = $this->appId;
        $APPSECRET = $this->appSecret;
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $APPID . '&secret=' . $APPSECRET;
        //创建curl资源
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $output = curl_exec($ch);
        curl_close($ch);
        $obj = json_decode($output, true);
        return $obj['access_token'];
    }

    /** 所选的国家的信息
     * @param $storeid
     */
    public function getCurCountry($storeid) {
        $storeMod = &m('store');
        $sql = 'SELECT s.id,c.`id` AS cid,c.`cate_name`,l.cate_name as lcatename  FROM  ' . DB_PREFIX . 'store AS s
                 LEFT JOIN  ' . DB_PREFIX . 'store_cate AS c ON s.`store_cate_id` = c.`id`  left join bs_store_cate_lang  as l on c.id=l.cate_id
                  WHERE s.id =' . $storeid . '  and  l.lang_id =' . $this->langid;
        $data = $storeMod->querySql($sql);
        return $data[0];
    }

    public function getCurStores($cateid) {
        if ($_SESSION['userId']) {
            $user_id = $_SESSION['userId'];
            $userMod = &m('user');
            $sql = 'SELECT *  FROM  ' . DB_PREFIX . 'user WHERE  id  =' . $user_id; //odm_members
            $datas = $userMod->querySql($sql);
            if ($datas[0]['odm_members'] == 0) {
                $where = ' and s.store_type <4 ';
            } else {
                $where = '';
            }
        } else {
            $where = ' and s.store_type <4 ';
        }
        $storeMod = &m('store');
        $sql = 'SELECT s.id,l.store_name,s.store_cate_id,s.lang_id  FROM  ' . DB_PREFIX . 'store AS s
                 LEFT JOIN  ' . DB_PREFIX . 'store_lang AS l ON s.`id` = l.`store_id` 
                  WHERE s.is_open = 1  and  store_cate_id =' . $cateid . ' and  l.lang_id =' . $this->langid . $where;
//        $sql = 'SELECT  id,store_name,store_cate_id,lang_id   FROM  ' . DB_PREFIX . 'store WHERE  is_open = 1 and store_cate_id =' . $cateid;
        $data = $storeMod->querySql($sql);
        return $data;
    }

    public function getCurStoreInfo($storeid, $auxiliary = 0) {
        $storeMod = &m('store');
        $sql = 'SELECT s.id,l.store_name,s.store_cate_id,s.lang_id  FROM  ' . DB_PREFIX . 'store AS s
                 LEFT JOIN  ' . DB_PREFIX . 'store_lang AS l ON s.`id` = l.`store_id`  and l.distinguish=' . $auxiliary . '
                  WHERE  s.id =' . $storeid . ' and  l.lang_id =' . $this->langid;
//        $sql = 'SELECT  id,store_name,store_cate_id,lang_id   FROM  ' . DB_PREFIX . 'store WHERE  id =' . $storeid;
        $data = $storeMod->querySql($sql);
        return $data[0];
    }

    //获取网站配置信息
    public function webconf() {
        $configMod = &m('config');
        $sql = 'select * from bs_config  where  inc_type = "shop_info" ';
        $data = $configMod->querySql($sql);
        $res = array();
        foreach ($data as $key => $val) {
            $res[$val['name']] = $val;
        }
        return $res;
    }

    /**
     * 获取默认站点
     * @param $cateid
     */
    public function getMrStore($cateid) {
        $storeMod = &m('store');
        $sql = 'select  id,store_name,lang_id,currency_id  from  ' . DB_PREFIX . 'store where store_type  = 1 and store_cate_id =' . $cateid;
        $data = $storeMod->querySql($sql);
        if (empty($data)) {
            $stores = $this->getStores();
            $firstItem = array_shift($stores);
            return $firstItem;
        } else {
            return $data[0];
        }
    }

    /**
     * 根据店铺找到国家所带的语言
     * @param $country
     */
    public function getCountryLang($store_id) {
        $storeCateMod = &m('storeCate');
        $sql = "select sc.lang_id from " . DB_PREFIX . "store as s left join " . DB_PREFIX . "store_cate as sc 
        on s.store_cate_id = sc.id where s.id = '{$store_id}'  order by s.id";
        $rs = $storeCateMod->querySql($sql);
        return $rs[0]['lang_id'];
    }

    /**
     * 获取站点国家的分类
     * @param $country
     */
    public function getStorecateid($country) {
        $storeCateMod = &m('storeCate');
        $sql = ' SELECT c.id,l.`cate_name`,l.`lang_id` FROM bs_store_cate AS c LEFT JOIN bs_store_cate_lang AS l ON c.id = l.`cate_id`
                 WHERE l.`lang_id` = 29  and  c.is_open = 1 AND l.`cate_name` = "' . $country . '"  order by c.id';
        $data = $storeCateMod->querySql($sql);
        if (empty($data)) {
            $storeCate = $this->getStoreCate1();  //取有总代理的国家分类
            $firstcate = array_shift($storeCate);
            return $firstcate;
        } else {
            return $data[0];
        }
    }

    /**
     * 获取当前用户购物车商品数量
     * @author wanyan
     * @date 2017/10/18
     */
    public function getCarNum() {
        $cartMod = &m('cart');
        $sql = "select sum(goods_num) as count from " . DB_PREFIX . "cart where `user_id` = '{$this->userId}'";
        $countInfo = $cartMod->querySql($sql);
        if(!empty($this->userId)){
            return $countInfo[0]['count'];
        }else{
            return  0;
        }

    }

    /**
     * 获取当前用户购物车商品价格综合
     * @author wanyan
     * @date 2017/10/18
     */
    public function getCarTotal() {
        $cartMod = &m('cart');
        $sql = "select sum(goods_price*goods_num) as total from " . DB_PREFIX . "cart where `user_id` = '{$this->userId}' and `store_id` = $this->storeid";
        $countInfo = $cartMod->querySql($sql);
        if (empty($countInfo[0]['total'])) {
            return 0;
        }
        return $countInfo[0]['total'];
    }

    /**
     * 获取当前站点所属币种
     * @author wanyan
     * @date 2017/10/17
     */
    public function getStoreCurrency() {
        $storeMod = &m('store');
        $sql = "select c.short,c.symbol from " . DB_PREFIX . "store  as s left join  " . DB_PREFIX . "currency as c on s.currency_id = c.id where s.id =$this->storeid";
        $rs = $storeMod->querySql($sql);
        if (empty($rs)) {
            return array();
        } else {
            return $rs[0];
        }
    }

    public function getStoreSymbol($storeid) {
        $storeMod = &m('store');
        $sql = 'SELECT  c.`symbol`,c.`short`   FROM    ' . DB_PREFIX . 'store AS s LEFT JOIN  ' . DB_PREFIX . 'currency AS c ON s.`currency_id` = c.`id`  WHERE  s.`id` = ' . $storeid;
        $res = $storeMod->querySql($sql);
        return $res[0];
    }

    /**
     * 有总代理的站点国家数据
     * 获取站点国家的数据
     */
    public function getStoreCate1() {
        $storeCateMod = &m('storeCate');

        $sql = 'SELECT c.id,c.`lang_id` FROM  bs_store  AS s LEFT JOIN  bs_store_cate AS c ON s.`store_cate_id`  = c.`id`
                WHERE s.`store_type` = 1 AND c.`is_open` = 1  order by c.id';

        $res = $storeCateMod->querySql($sql);
        return $res;
    }

    /**
     * 获取站点国家的数据
     */
    public function getStoreCate() {
        $storeCateMod = &m('storeCate');
        $sql = 'SELECT  c.id,l.cate_name as lcatename  FROM  ' . DB_PREFIX . 'store_cate  as c
                 left join  ' . DB_PREFIX . 'store_cate_lang as l on  c.id = l.cate_id
                  LEFT  JOIN  bs_store AS s ON c.id = s.`store_cate_id`
                 WHERE c.is_open = 1  and  l.lang_id =' . $this->langid . '   AND s.`store_type` = 1  order by c.id';
        $res = $storeCateMod->querySql($sql);
        return $res;
    }

    /**
     * 站点
     * @author wangh
     * @date 2017/09/14
     */
    public function getStores() {
        $storeMod = &m('store');
        $sql = 'select  id,store_name,lang_id,currency_id  from ' . DB_PREFIX . 'store  WHERE  is_open = 1  order by id asc ';
        $data = $storeMod->querySql($sql);
        return $data;
    }

    /**
     * 语言
     * @author wangh
     * @date 2017/09/14
     */
    public function getLangs() {
        $languageMod = &m('language');
        $sql = 'select  *  from ' . DB_PREFIX . 'language where enable=1';
        $data = $languageMod->querySql($sql);
        return $data;
    }

    /**
     * 语言的简写
     * @author wangh
     * @date 2017/09/14
     */
    public function getShorthand($langid) {
        $languageMod = &m('language');
        $sql = 'select  id,name,shorthand,logo  from ' . DB_PREFIX . 'language  where id=' . $langid;
        $data = $languageMod->querySql($sql);
        return $data[0];
    }

    /**
     * 获取站点的数据
     * @author wangh
     * @date 2017/09/14
     */
    public function getStoreData($storeid) {
        $storeMod = &m('store');
        $sql = 'select  id,store_name,lang_id  from ' . DB_PREFIX . 'store  WHERE  id =  ' . $storeid;
        $data = $storeMod->querySql($sql);
        return $data[0];
    }

    /**
     * 获取业务分类
     * @author wangh
     * @date 2017/08/22
     */
    public function getRoomType($langid) {
        $roomTypeMod = &m('roomType');
        if (!empty($langid)) {
            $where = '    where  l.`lang_id`  = ' . $langid;
        } else {
            $where = '    where  l.`lang_id`  = ' . $this->mrlangid;
        }

        $sql = 'SELECT  t.`id`,l.`type_name` ,t.`room_img`  as rimg ,t.`room_adv_img`  as advimg ,t.`adv_url` as  advurl    FROM  ' . DB_PREFIX . 'room_type AS t
                 LEFT JOIN   ' . DB_PREFIX . 'room_type_lang AS l  ON t.`id` = l.`type_id`  ' . $where . '   ORDER BY  t.`id`  LIMIT  10  ';

        $res = $roomTypeMod->querySql($sql);
        return $res;
    }

    /**
     * 数据封装
     * @author lvji
     * @param $status 表示返回数据状态
     * @param $message 对返回状态说明
     * @param $info 返回数据信息
     * @date 2015-03-10
     */
    public function setData($info = array(), $status = 'success', $message = 'ok') {
        $data = array(
            'status' => $status,
            'message' => $message,
            'info' => $info,
        );
        echo json_encode($data);
        exit();
    }

    /**
     * 获取当前页面的url
     * @return string
     */
    public function curPageURL() {
        $pageURL = 'http';
//        if ($_SERVER["HTTPS"] == "on") {
//            $pageURL .= "s";
//        }
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        } else {
            if ($_SERVER["SERVER_NAME"] == "-" || empty($_SERVER["SERVER_NAME"])) {
                $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
            } else {
                $pageURL .= SYSTEM_WEB . $_SERVER["REQUEST_URI"];
            }
        }
        return $pageURL;
    }

    /**
     * wangh
     * 加载语言包文件
     * @param $langid
     * @param $filename  如: default/index
     * @return array
     */
    public function load($shorthand, $filename) {

        $_ = array(); //语言包数组

        if ($shorthand == 'ZH') {
            $folders = 'chinese';
        } else if ($shorthand == 'EN') {
            $folders = 'english';
        } else {
            $folders = 'english';
        }

        $file = ROOT_PATH . '/languages/' . $folders . '/' . $filename . '.php';

        if (is_file($file)) {
            require($file);
        } else {
            return array();
        }

        $this->langData = array_merge($this->langData, $_);
    }

    /*
     * 报错页面
     */

    public function getError() {
        $url = "?app=baseFront&act=errorPage";
        header("location:" . $url);
        exit;
    }

    public function errorPage() {
        $this->display("public/error.html");
    }

    /**
     * 生成不重复的四位随机数
     * @author wanyan
     * @date 2017-10-23
     */
    public function buildNo($limit) {
        $begin = pow(10, 3);
        $end = (pow(10, 4) - 1);
        $rand_array = range($begin, $end);
        shuffle($rand_array); //调用现成的数组随机排列函数
        return array_slice($rand_array, 0, $limit); //截取前$limit个
    }

    /**
     * 根据用户的IP地址，定位用户所在的城市
     * @return string
     */
    public function GetIp() {
        $realip = '';
        $unknown = 'unknown';
        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                foreach ($arr as $ip) {
                    $ip = trim($ip);
                    if ($ip != 'unknown') {
                        $realip = $ip;
                        break;
                    }
                }
            } else if (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP']) && strcasecmp($_SERVER['HTTP_CLIENT_IP'], $unknown)) {
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            } else if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)) {
                $realip = $_SERVER['REMOTE_ADDR'];
            } else {
                $realip = $unknown;
            }
        } else {
            if (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), $unknown)) {
                $realip = getenv("HTTP_X_FORWARDED_FOR");
            } else if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), $unknown)) {
                $realip = getenv("HTTP_CLIENT_IP");
            } else if (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), $unknown)) {
                $realip = getenv("REMOTE_ADDR");
            } else {
                $realip = $unknown;
            }
        }
        $realip = preg_match("/[\d\.]{7,15}/", $realip, $matches) ? $matches[0] : $unknown;
        return $realip;
    }

    /** 根据新浪的接口获取所在的城市
     * @param string $ip
     * @return bool|mixed
     */
    public function GetIpLookup($ip = '') {
        if (empty($ip)) {
            $ip = $this->GetIp();
        }
        $res = @file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip=' . $ip);
        if (empty($res)) {
            return false;
        }
        $jsonMatches = array();
        preg_match('#\{.+?\}#', $res, $jsonMatches);
        if (!isset($jsonMatches[0])) {
            return false;
        }
        $json = json_decode($jsonMatches[0], true);
        if (isset($json['ret']) && $json['ret'] == 1) {
            $json['ip'] = $ip;
            unset($json['ret']);
        } else {
            return false;
        }
        return $json;
    }

    /**
     * 模仿array_column方法，获取二位数组中的某个字段
     * @author jh
     * @date 2017/07/03
     */
    public function arrayColumn($info, $value, $key = '') {
        $data = array();
        if ($key) {
            foreach ($info as $v) {
                $data[$v[$key]] = $v[$value];
            }
        } else {
            foreach ($info as $v) {
                $data[] = $v[$value];
            }
        }
        return $data;
    }

//	public $adminId , $adminInfo;
//	/**
//	 * 构造函数
//	 */
//	public function __construct() {
//		parent::__construct();
//		$action = $_REQUEST['act'];
//
//		$this->assign('SITE_URL', SITE_URL);
//		$this->assign('STATIC_URL', STATIC_URL);
//		//不检测登录
//		$nologin = array(
//			'login',
//			'register',
//		);
//		/*if(!in_array(ACT, $nologin)){
//			if (!isset($_SESSION['adminId']) && ACT != 'login' && ACT != "captcha" && ACT!="ajaxlogin" ) {
//				if (IS_AJAX) {
//					header("Location: ?act=ajaxlogin&url=". urlencode(pageUrl()));
//				}else{
//					header("Location: ?act=login&url=" . urlencode( pageUrl() ));
//				}
//				exit();
//			}
//		}*/
//
//		
//	}
//	/**
//	 * 数据封装
//	 * @author lvji
//	 * @param $status 表示返回数据状态
//	 * @param $message 对返回状态说明
//	 * @param $info 返回数据信息
//	 * @date 2015-03-10
//	 */
//	public function setData($info=array(), $status='success', $message='ok'){
//		    $data = array(
//			'status'=>$status,
//			'message'=>$message,
//			'info'=>$info,
//		); 
//		$data['commonParams'] = array(
//			'adminId'=>$this->adminId,
//			'username'=>$this->adminInfo['username'],
//			'password'=>$this->adminInfo['password'],
//			'head_img'=>$this->adminInfo['head_img'],
//			'erwm_img'=>$this->adminInfo['erwm_img'],
//		);
//		echo json_encode($data);
//		exit();
//	}
//	
//	/**
//	 * 获取更多用户信息
//	 * @author lvji
//	 * @date 2015-03-10
//	 */
//	public function memberInfo(){
//		$id = $this->adminId;
//		//处理其他数据
//
//		//返回用户其他信息
//		return $memberInfo;
//	}
//	
    /**
     * 获取当前订单用户未付款数量
     * @author wangshuo
     * @date 2017/12/8
     */
    public function dfkNum() {
        $orderMod = &m('order');
        //查找店铺是总代理还是经销商，总代理显示所有订单，
        $storeMod = &m('store');
        $sqlStore = 'select store_type  from  ' . DB_PREFIX . 'store where id  = ' . $this->storeid;
        $dataStore = $storeMod->querySql($sqlStore);
        if ($dataStore[0]['store_type'] == 1) {
            $sql = "select count(*) as count from " . DB_PREFIX . "order where mark = 1 and order_state=10 and `buyer_id` = " . $this->userId;
        } else {
            $sql = "select count(*) as count from " . DB_PREFIX . "order where mark = 1 and order_state=10 and `buyer_id` = '$this->userId' and `store_id` = $this->storeid";
        }
        $countInfo = $orderMod->querySql($sql);
        if ($countInfo[0]['count'] == 0) {
        } else {
            return $countInfo[0]['count'];
        }
    }

    /**
     * 获取当前订单用户待发货数量
     * @author wangshuo
     * @date 2017/12/8
     */
    public function dfhNum() {
        $orderMod = &m('order');
        //查找店铺是总代理还是经销商，总代理显示所有订单，
        $storeMod = &m('store');
        $sqlStore = 'select store_type  from  ' . DB_PREFIX . 'store where id  = ' . $this->storeid;
        $dataStore = $storeMod->querySql($sqlStore);
        if ($dataStore[0]['store_type'] == 1) {
            $sql = "select count(*) as count from " . DB_PREFIX . "order where order_state=20 and mark =1 and `buyer_id` = '$this->userId'";
        } else {
            $sql = "select count(*) as count from " . DB_PREFIX . "order where order_state=20 and  mark =1 and `buyer_id` = '$this->userId' and `store_id` = $this->storeid";
        }
        $countInfo = $orderMod->querySql($sql);

        if ($countInfo[0]['count'] == 0) {
            
        } else {
            return $countInfo[0]['count'];
        }
    }

    /**
     * 获取当前订单用户待收货数量
     * @author wangshuo
     * @date 2017/12/8
     */
    public function dshNum() {
        $orderMod = &m('order');
        //查找店铺是总代理还是经销商，总代理显示所有订单，
        $storeMod = &m('store');
        $sqlStore = 'select store_type  from  ' . DB_PREFIX . 'store where id  = ' . $this->storeid;
        $dataStore = $storeMod->querySql($sqlStore);
        if ($dataStore[0]['store_type'] == 1) {
            $sql = "select count(*) as count from " . DB_PREFIX . "order where order_state in(30,40) and mark =1 and `buyer_id` = '$this->userId'";
        } else {
            $sql = "select count(*) as count from " . DB_PREFIX . "order where order_state in(30,40) and mark =1 and  `buyer_id` = '$this->userId' and `store_id` = $this->storeid";
        }
        $countInfo = $orderMod->querySql($sql);
        if ($countInfo[0]['count'] == 0) {
            
        } else {
            return $countInfo[0]['count'];
        }
    }

    /**
     * 获取当前订单用户待评价数量
     * @author wangshuo
     * @date 2017/12/8
     */
    public function dpjNum() {
        $orderMod = &m('order');
        //查找店铺是总代理还是经销商，总代理显示所有订单，
        $storeMod = &m('store');
        $sqlStore = 'select store_type  from  ' . DB_PREFIX . 'store where id  = ' . $this->storeid;
        $dataStore = $storeMod->querySql($sqlStore);
        if ($dataStore[0]['store_type'] == 1) {
            $sql = "select count(*) as count from " . DB_PREFIX . "order where order_state=50 and mark = 1 and refund_state=0 and evaluation_state=0 and `buyer_id` = '$this->userId'";
        } else {
            $sql = "select count(*) as count from " . DB_PREFIX . "order where order_state=50 and  mark = 1 and refund_state=0 and evaluation_state=0 and `buyer_id` = '$this->userId' and `store_id` =  $this->storeid";
        }
        $countInfo = $orderMod->querySql($sql);
        if ($countInfo[0]['count'] == 0) {
            
        } else {
            return $countInfo[0]['count'];
        }
    }

    /**
     * 获取当前订单用户待退款数量
     * @author wangshuo
     * @date 2017/12/8
     */
    public function dtkNum() {
        $orderMod = &m('order');
        //查找店铺是总代理还是经销商，总代理显示所有订单，
        $storeMod = &m('store');
        $sqlStore = 'select store_type  from  ' . DB_PREFIX . 'store where id  = ' . $this->storeid;
        $dataStore = $storeMod->querySql($sqlStore);
        if ($dataStore[0]['store_type'] == 1) {
            $sql = "select count(*) as count from " . DB_PREFIX . "order where mark = 1 and refund_state!=0 and `buyer_id` = '$this->userId'";
        } else {
            $sql = "select count(*) as count from " . DB_PREFIX . "order where mark = 1 and refund_state!=0 and `buyer_id` = '$this->userId' and `store_id` = $this->storeid";
        }
        $countInfo = $orderMod->querySql($sql);
        if ($countInfo[0]['count'] == 0) {
            
        } else {
            return $countInfo[0]['count'];
        }
    }

    /**
     * 授权地址
     * @author lvj
     * @date 2016-11-21
     * @pararm $redirectUrl 跳转地址，再改地址中获取code
     */
    public function getOAuthUrl($redirectUrl, $openIdOnly = 'snsapi_userinfo', $state = '') {
        //https://open.weixin.qq.com/connect/oauth2/authorize?appid=APPID&redirect_uri=REDIRECT_URI&response_type=code&scope=SCOPE&state=STATE
        $redirectUrl = urlencode($redirectUrl);
        $scope = $openIdOnly ? 'snsapi_base' : 'snsapi_userinfo';
        $oAutUurl = 'https://open.weixin.qq.com/connect/oauth2/authorize?';
        $oAutUurl .= 'appid=' . $this->appId;
        $oAutUurl .= '&redirect_uri=' . $redirectUrl;
        $oAutUurl .= '&response_type=code';
        $oAutUurl .= "&scope={$openIdOnly}";
        $oAutUurl .= '&state=' . $state;
        $oAutUurl .= '&connect_redirect=1#wechat_redirect';
        return $oAutUurl;
    }

    /**
     * 获取access_token
     * @author lvj
     * @date 2016-11-21
     */
    public function getoAuthAccessToken($code) {
        //return json_decode(file_get_contents("https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->appId}&secret={$this->appSecret}&code={$code}&grant_type=authorization_code",true);
        //https://api.weixin.qq.com/sns/oauth2/access_token?appid=APPID&secret=SECRET&code=CODE&grant_type=authorization_code
        $accessToken = 'https://api.weixin.qq.com/sns/oauth2/access_token?';
        $accessToken .= "appid={$this->appId}";
        $accessToken .= "&secret={$this->appSecret}";
        $accessToken .= "&code={$code}";
        $accessToken .= '&grant_type=authorization_code';
        $error = false;
        $accessTokenInfo = $this->sendCurlJson($error, $accessToken, $param = array(), $method = 'GET', $timeout = 15, $exOptions = NULL);
        return json_decode($accessTokenInfo);
    }

    /**
     * 通过refresh_token获取access_token
     * @author lvj
     * @date 2016-11-21
     */
    public function getoAuthAccessTokenByRefreshToken($refreshToken) {
        //https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=APPID&grant_type=refresh_token&refresh_token=REFRESH_TOKEN
        $accessToken = 'https://api.weixin.qq.com/sns/oauth2/refresh_token?';
        $accessToken .= "appid={$this->appId}";
        $accessToken .= '&grant_type=refresh_token';
        $accessToken .= "&refresh_token={$refreshToken}";
        $error = false;
        $accessTokenInfo = $this->sendCurlJson($error, $accessToken, $param = array(), $method = 'GET', $timeout = 15, $exOptions = NULL);
        return json_decode($accessTokenInfo);
    }

    /**
     * 获取access_token
     * @author luffy
     * @date 2017-10-17
     */
    public function getAccessToken2() {
        $accessToken = 'https://api.weixin.qq.com/cgi-bin/token?';
        $accessToken .= "appid={$this->appId}";
        $accessToken .= "&secret={$this->appSecret}";
        $accessToken .= '&grant_type=client_credential';
        $error = false;
        $accessTokenInfo = $this->sendCurlJson($error, $accessToken, $param = array(), $method = 'GET', $timeout = 15, $exOptions = NULL);
        return json_decode($accessTokenInfo);
    }

    /**
     * 获取微信用户信息
     * @author lvj
     * @date 2016-11-21
     */
    public function getUserInfo($accessToken, $openid) {
        //https://api.weixin.qq.com/sns/userinfo?access_token=ACCESS_TOKEN&openid=OPENID&lang=zh_CN
        $url = 'https://api.weixin.qq.com/sns/userinfo?';
        $url .= "access_token={$accessToken}";
        $url .= "&openid={$openid}";
        $url .= "&lang=zh_CN";
        $error = false;
        $userInfo = $this->sendCurlJson($error, $url);
        if ($error == false && $userInfo != '') {
            //返回微信用户信息
            $userInfo = json_decode($userInfo);
        } else {
            //提示错误信息
            if (is_array($error)) {
                $error = json_encode($error);
            }
            exit($error);
        }
        return $userInfo;
    }

    /**
     * @return mixed
     * 获取会员的openid
     */
    public function getOpenid() {
        $userMod = &m('user');
        $sql = 'select  id,username,openid,nickname,headimgurl   from  bs_user where id=' . $this->userId;
        $data = $userMod->querySql($sql);
        return $data[0];
    }

    /**
     * @param $userinfo
     * 向 用户表 插入 微信信息
     */
    public function insertWxinfo($userinfo) {
        $userMod = &m('user');
        $userid = $this->userId;
        $data = array(
            'openid' => $userinfo['openid'],
            'nickname' => $userinfo['nickname'],
            'city' => $userinfo['city'],
            'province' => $userinfo['province'],
            'country' => $userinfo['country'],
            'headimgurl' => $userinfo['headimgurl'],
            'sex' => $userinfo['sex']
        );

        $userMod->doEdit($userid, $data);
    }

    /**
     *  发起一个HTTP(S)请求，并返回响应文本
     * @author lvj
     * @date 2016-11-21
     * @param array 错误信息  array($errorCode, $errorMessage)
     * @param string 请求Url
     * @param array 请求参数
     * @param string 请求类型(GET|POST)
     * @param int 超时时间
     * @param array 额外配置
     * @return string
     */
    public function sendCurlJson(&$error, $url, $param = array(), $method = 'GET', $timeout = 15, $exOptions = NULL) {
        //判断是否开启了curl扩展
        if (!function_exists('curl_init'))
            exit('please open this curl extension');
        //将请求方法变大写
        $method = strtoupper($method);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        }
        if (isset($_SERVER['HTTP_REFERER'])) {
            curl_setopt($ch, CURLOPT_REFERER, $_SERVER['HTTP_REFERER']);
        }
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        switch ($method) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if (!empty($param)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, (is_array($param)) ? http_build_query($param) : $param);
                }
                break;
            case 'GET':
            case 'DELETE':
                if ($method == 'DELETE') {
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                }
                if (!empty($param)) {
                    $url = $url . (strpos($url, '?') ? '&' : '?') . (is_array($param) ? http_build_query($param) : $param);
                }
                break;
        }
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        //设置额外配置
        if (!empty($exOptions)) {
            foreach ($exOptions as $k => $v) {
                curl_setopt($ch, $k, $v);
            }
        }
        $response = curl_exec($ch);
        $error = false;
        //看是否有报错
        $errorCode = curl_errno($ch);
        if ($errorCode) {
            $errorMessage = curl_error($ch);
            $error = array('errorCode' => $errorCode, 'errorMessage' => $errorMessage);
            //将报错写入日志文件里
//            $logText = "$method $url: [$errorCode]$errorMessage";
//            if (!empty($param)) {
//                $logText .= ",$param".json_encode($param);
//            }
//            file_put_contents('/data/error.log', $logText);
        }
        curl_close($ch);
        return $response;
    }

    /*
     * 设置微信cook
     */

    public function setWxCook($code) {
        $accessTokenInfo = $this->getoAuthAccessToken($code);
        $OuthToken = $accessTokenInfo->access_token;
        $openid = $accessTokenInfo->openid;
        $userInfo = $this->getUserInfo($OuthToken, $openid);
        $wx_openid = $userInfo->openid;
        $wx_nickname = $userInfo->nickname;
        $wx_city = $userInfo->city;
        $wx_province = $userInfo->province;
        $wx_country = $userInfo->country;
        $wx_headimgurl = $userInfo->headimgurl;
        $wx_sex = $userInfo->sex;
        //3个月的cookie的生存期
        setcookie('wx_openid', $wx_openid, time() + 3600 * 24 * 30 * 3);
        setcookie('wx_nickname', $wx_nickname, time() + 3600 * 24 * 30 * 3);
        setcookie('wx_city', $wx_city, time() + 3600 * 24 * 30 * 3);
        setcookie('wx_province', $wx_province, time() + 3600 * 24 * 30 * 3);
        setcookie('wx_country', $wx_country, time() + 3600 * 24 * 30 * 3);
        setcookie('wx_headimgurl', $wx_headimgurl, time() + 3600 * 24 * 30 * 3);
        setcookie('wx_sex', $wx_sex, time() + 3600 * 24 * 30 * 3);
    }

    public function dd($info) {

        if (is_object($info) || is_array($info)) {
            $info_text = var_export($info, true);
        } elseif (is_bool($info)) {
            $info_text = $info ? 'true' : 'false';
        } else {
            $info_text = $info;
        }

        file_put_contents('./dd.txt', $info_text);
    }

    /**
     *
     * 通过跳转获取用户的openid，跳转流程如下：
     * 1、设置自己需要调回的url及其其他参数，跳转到微信服务器https://open.weixin.qq.com/connect/oauth2/authorize
     * 2、微信服务处理完成之后会跳转回用户redirect_uri地址，此时会带上一些参数，如：code
     *
     * @return 用户的openid
     */
    public function __GetOpenid() {
        //通过code获得openid
        if (!isset($_GET['code'])) {
            //触发微信返回code码

            $baseUrl = urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . $_SERVER['QUERY_STRING']);
            $url = $this->__CreateOauthUrlForCode($baseUrl);
            Header("Location: $url");
            exit();
        } else {
            //获取code码，以获取openid
            $code = $_GET['code'];
            $openid = $this->GetOpenidFromMp($code);
//            $this->setWxCook($code);
            return $openid;
        }
    }

    /**
     *
     * 构造获取code的url连接
     * @param string $redirectUrl 微信服务器回跳的url，需要url编码
     *
     * @return 返回构造好的url
     */
    private function __CreateOauthUrlForCode($redirectUrl) {
        $urlObj["appid"] = $this->appId;
        $urlObj["redirect_uri"] = "$redirectUrl";
        $urlObj["response_type"] = "code";
        $urlObj["scope"] = "snsapi_base";
        $urlObj["state"] = "STATE" . "#wechat_redirect";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?" . $bizString;
    }

    /**
     *
     * 通过code从工作平台获取openid机器access_token
     * @param string $code 微信跳转回来带上的code
     *
     * @return openid
     */
    public function GetOpenidFromMp($code) {
        $url = $this->__CreateOauthUrlForOpenid($code);
        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->curl_timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
//        if(WxPayConfig::CURL_PROXY_HOST != "0.0.0.0"
//            && WxPayConfig::CURL_PROXY_PORT != 0){
//            curl_setopt($ch,CURLOPT_PROXY, WxPayConfig::CURL_PROXY_HOST);
//            curl_setopt($ch,CURLOPT_PROXYPORT, WxPayConfig::CURL_PROXY_PORT);
//        }
        //运行curl，结果以jason形式返回
        $res = curl_exec($ch);
        curl_close($ch);
        //取出openid
        $data = json_decode($res, true);
        $this->data = $data;
        $openid = $data['openid'];
        return $openid;
    }

    /**
     *
     * 构造获取open和access_toke的url地址
     * @param string $code，微信跳转带回的code
     *
     * @return 请求的url
     */
    private function __CreateOauthUrlForOpenid($code) {
        $urlObj["appid"] = $this->appId;
        $urlObj["secret"] = $this->appSecret;
        $urlObj["code"] = $code;
        $urlObj["grant_type"] = "authorization_code";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://api.weixin.qq.com/sns/oauth2/access_token?" . $bizString;
    }

    /**
     *
     * 拼接签名字符串
     * @param array $urlObj
     *
     * @return 返回已经拼接好的字符串
     */
    private function ToUrlParams($urlObj) {
        $buff = "";
        foreach ($urlObj as $k => $v) {
            if ($k != "sign") {
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }

    public function getGoodsLang($store_goods_id, $lang_id) {
        $areaGMod = &m('areaGood');
        $where = " where sg.id=" . $store_goods_id . " and gl.lang_id=" . $lang_id;
        $sql = "select gl.goods_name from " . DB_PREFIX . "store_goods as sg left join " . DB_PREFIX . "goods as g on sg.goods_id=g.goods_id
              left join " . DB_PREFIX . "goods_lang as gl on g.goods_id=gl.goods_id
              " . $where;
        $res = $areaGMod->querySql($sql);
        return $res[0];
    }

    /**
     * 视图对应CSS
     * @author	luffy
     * @param	array $app
     * @return	void
     */
    protected function viewCss($time){
        $viewCss = '';
        if (file_exists(ROOT_PATH . '/assets/wx/' . APP . '/' . ACT. '/' . ACT . '.css')) {
            $viewCss = STATIC_URL . '/wx/' . APP . '/' . ACT. '/' . ACT . '.css?_t=' . $time;
        }
        $this->assign('viewCss', $viewCss);
    }

    /**
     * 视图对应JS
     * @author	luffy
     * @param	array $app
     * @return	void
     */
    protected function viewJs($time){
        $viewJs = '';
        if (file_exists(ROOT_PATH . '/assets/wx/' . APP . '/' . ACT. '/' . ACT . '.js')) {
            $viewJs = STATIC_URL . '/wx/' . APP . '/' . ACT. '/' . ACT . '.js?_t=' . $time;
        }
        $this->assign('viewJs', $viewJs);
    }



}

?>