<?php

/**
 * 手机APP
 * @author lvji
 * @date 2016-08-01
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class BaseStoreApp extends BaseApp {

    public $storeId, $storeInfo, $symbol, $country_id, $rolename, $storeUserId, $defaulLang, $fx_discount, $languageId, $storecate;
    public $auth = 2; //1是总代理，2是经销商
    public $langData = array();   // 语言包数据
    public $storeIds=array(95); //店铺订单数据
    /**
     * 构造函数
     */

    public function __construct() {
        parent::__construct();

        if( $_SERVER['REQUEST_SCHEME'] == 'https' ){
            $this->assign('SITE_URL', SITE_URL_SSL);
            $this->assign('STATIC_URL', STATIC_URL_SSL);
        } else {
            $this->assign('SITE_URL', SITE_URL);
            $this->assign('STATIC_URL', STATIC_URL);
        }
        //不检测登录
        $nologin = array(
            'login',
            'createCode',
            'doLogin',
            'goOnLogin',
            'baseKf',
            'notify',
            'returnUrl',
            'notifyUrl'
        );
        if (!in_array(ACT, $nologin)) {
            if (!isset($_SESSION['store']['storeId']) && ACT != 'login' && ACT != "captcha" && ACT != "ajaxlogin") {
                if (IS_AJAX) {
                    header("Location: ?app=default&act=login&url=" . urlencode(pageUrl()));
                } else {
                    header("Location: ?app=default&act=login&url=" . urlencode(pageUrl()));
                }
                exit();
            } else {
                if ($_SESSION['store']['storeId']) {  //已登录
                    $this->assign('user_name', $_SESSION['store']['user_name']);
                    $this->storeId = $_SESSION['store']['storeId'];
                    $this->storeUserId = $_SESSION['store']['userId'];
                    $storeData = $this->getStoreInfo();
                    $this->storeInfo = $storeData;
                    $this->storecate = $storeData['store_cate_id'];
                    $this->assign('storeData', $storeData);
                    $this->symbol = $this->getSymbol();
                    $this->assign('symbol', $this->symbol);
                    $this->country_id = $this->getCountryId(); // 店铺所属的国家分类ID
                    $this->languageId = $this->getLanguageById();
                    $this->checkUserStatus();
                    $this->menu();
                    //权限管理
                    $this->storeAuth();
                    $this->assign('auth', $this->auth);
                }
            }
        }
        //默认语言
        if (empty($_SESSION['store']['defal_lang'])) {
            $langMod = &m('language');
            $langInfo = $langMod->getOne(array('cond' => "is_default=2"));
            $_SESSION['store']['defal_lang'] = $langInfo['id'];
            $this->defaulLang = $_SESSION['store']['defal_lang'];
        } else {
            $this->defaulLang = $_SESSION['store']['defal_lang'];
        }
        $this->roleName = $this->storeUseradmin($_SESSION['store']['storeUserInfo']['storeuseradmin_id']);
        $this->assign('roleName', $this->roleName);

        //获取未指定订单和未手动接单的订单
        if( empty($_SESSION['store']['headOrderNum']) ){
            $this->getPublicHeadOrderNum();
        }
        $this->assign('headOrderNum', $_SESSION['store']['headOrderNum']);
        //获取语言包
        if( $this->languageId == 1 ){
            $this->shorthand = 'EN';
        } else {
            $this->shorthand = 'ZH';
        }
        $this->langDataBank = languageFun($this->shorthand);

        $this->assign('language1', $this->langDataBank->project);
        $this->assign('language2', $this->langDataBank->public);
    }

    /**
     * 获取未指定订单和未手动接单的订单
     * @author  luffy
     * @date    2018-12-07
     */
    public function getPublicHeadOrderNum() {
        $orderMod = &m('order');
        $data     = $orderMod->getPublicHeadOrderNum($this->storeId);
        $_SESSION['store']['headOrderNum'] = $data;
    }

    /**
     * 获取管理员昵称
     */
    public function storeUseradmin($id) {
        $storeMod = &m('storeUserAdmin');
        $sql = 'select  name,english_name from  bs_store_user_admin  where  id = ' . $id;
        $data = $storeMod->querySql($sql);
        return $data[0];
    }

    /**
     * 检测用户是否操作是否过期
     */
    public function checkUserStatus() {
        $areaAccountMod = &m('areaAccountSession');
        if ($this->storeUserId) {
            $httpHost = $_SERVER['HTTP_HOST'];
            //$url ='http://'.$httpHost."/bspm711/admin.php?app=account&act=login";
            $url = "admin.php?app=account&act=login";
            $accountSessionInfo = $areaAccountMod->getOne(array('cond' => "`user_id` = {$this->storeUserId}", 'fields' => 'login_time,session_id,id'));
            if ($accountSessionInfo['session_id']) {
                if (session_id() != $accountSessionInfo['session_id']) {
                    session_destroy();
                    if (isset($_COOKIE[session_name()])) {
                        setcookie(session_name(), '', time() - 3600);
                    }
                    echo '<script>alert("该账号在其他地方登录!"); window.location = "?app=default&act=login"</script>';
                    //  header("Location: {$url}");
                }
//                if(time() - $accountSessionInfo['login_time'] > 600){
////                    if (session_id() != $accountSessionInfo['session_id']) {
//                    session_destroy();
//                    if (isset($_COOKIE[session_name()])) {
//                        setcookie(session_name(), '', time() - 3600);
//                    }
////                    echo '<script>alert("该账号无操作10分钟后过期")</script>';
////                    header("Location: {$url}");
//                    echo '<script>alert("该账号无操作10分钟后过期!"); window.location = "?app=default&act=login"</script>';
////                    }
//                }else{
                $sessEditData = array(
                    'login_time' => time()
                );
                $id = $areaAccountMod->doEdit($accountSessionInfo['id'], $sessEditData);
//                }
            }
        }
    }

    /**
     * 权限的判断
     */
    public function storeAuth() {
        $storeinfo = $this->storeInfo;
        $this->auth = $storeinfo['store_type'];
    }

    /**
     * 获取站点信息
     */
    public function getStoreInfo() {
        $storeMod = &m('store');
        if ($_REQUEST['lang_id'] == 1) {
            $where = ' l.distinguish=0 and  l.lang_id =30 and c.id = ' . $_SESSION['store']['storeId'];
        } else {
            $where = 'l.distinguish=0 and l.lang_id =' . $_SESSION['store']['defal_lang'] . ' and c.id = ' . $_SESSION['store']['storeId'];
        }
        $sql = 'select  c.id,l.store_name, c.lang_id, c.store_cate_id,  c.store_type,c.distance,c.longitude,c.latitude from  ' . DB_PREFIX . 'store  as c
                 left join  ' . DB_PREFIX . 'store_lang as l on  c.id = l.store_id where ' . $where;
//        $sql = 'select  id,store_name,lang_id,store_cate_id,store_type  from  bs_store  where  id = ' . $_SESSION['store']['storeId'];
        $data = $storeMod->querySql($sql);
        return $data[0];
    }

    /**
     * 根据店铺ID 获取 店铺所属国家语言
     */
    public function getCountryId() {
        $storeMod = &m('store');
        $res = $storeMod->getOne(array('cond' => "`id`='{$this->storeId}'", 'fields' => "store_cate_id"));
        return $res['store_cate_id'];
    }

    /**
     * 根据店铺ID 获取 店铺所属国家语言
     */
    public function getLanguageById() {
        $storeMod = &m('storeCate');
        $res = $storeMod->getOne(array('cond' => "`id`='{$this->country_id}'", 'fields' => "lang_id"));
        return $res['lang_id'];
    }

    /**
     * 根据店铺ID 获取 店铺所属国家
     */
    public function getFxDiscount() {
        $storeCateMod = &m('storeCate');
        $res = $storeCateMod->getOne(array('cond' => "`id`='{$this->country_id}'", 'fields' => "fx_discount"));
        return $res['fx_discount'];
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
//		$data['commonParams'] = array(
//			'adminId'=>$this->storeId,
//			'username'=>$this->storeInfo['username'],
//			'password'=>$this->storeInfo['password'],
//			'head_img'=>$this->storeInfo['head_img'],
//			'erwm_img'=>$this->storeInfo['erwm_img'],
//		);
        echo json_encode($data);
        exit();
    }

    /**
     * 返回正确消息
     * @author jh
     * @date 2017/06/22
     */
    public function jsonResult($message = 'ok', $info = array()){
        $this->setData($info, 1, $message);
    }
    /**
     * 返回错误消息
     * @author jh
     * @date 2017/06/22
     */
    public function jsonError($message = 'error', $info = array()){
        $this->setData($info, 0, $message);
    }

    /**
     * 菜单管理
     * @author huxw
     * @date 2015-03-10
     */
    public function menu() {
        //中英切换
        if ($_GET['lang_id'] == 0) {
            $str = 'title';
        } else {
            $str = 'english_title';
        }
        $this->assign('lang_id', $_GET['lang_id']);
        $curApp = APP;
        $curAct = ACT;
        $storeUserAuthMod = &m("storeUserAuth");
        $menuData = $storeUserAuthMod->getAuthList();
        switch ($curApp) {
            case 'authManage':
                $curApp = 'authManage';
                break;
            case 'storeUserAdmin':
                $curApp = 'authManage';
                break;
            case 'storeUser':
                $curApp = 'authManage';
                break;
            case 'systemConsole':
                $curApp = 'authManage';
                break;
            case 'orderList':
                $curApp = 'orderList';
                break;
            case 'goodsComment':
                $curApp = 'orderList';
                break;
            case 'guestList':
                $curApp = 'TakealistofGuests';
                break;
            case 'storeDk':
                $curApp = 'TakealistofGuests';
                break;
            case 'sourceList':
                $curApp = 'TakealistofGuests';
                break;
            case 'order':
                $curApp = 'TakealistofGuests';
                break;
            case 'orderReport':
                $curApp = 'TakealistofGuests';
                break;
            case 'customerOrder':
                $curApp = 'TakealistofGuests';
                break;
            case 'orderDk':
                $curApp = 'TakealistofGuests';
                break;
            case 'native':
                $curApp = 'orderList';
            case 'sourceOrder':
                $curApp = 'orderSource';
                break;

            case 'adv':
                $curApp = 'advModule';
                break;
            case 'advPosition':
                $curApp = 'advModule';
                break;
            case 'article':
                $curApp = 'articleModule';
                break;
            case 'articleCtg':
                $curApp = 'articleModule';
                break;
            case 'site':
                $curApp = 'siteModule';
                break;
            case 'payConfig':
                $curApp = 'siteModule';
                break;
            case 'point':
                $curApp = 'siteModule';
                break;
            case 'areaGood':
                $curApp = 'good';
                break;
            case 'groupbuy':
                $curApp = 'promotionModule';
                break;
            case 'goodProm':
                $curApp = 'promotionModule';
                break;
            case 'promotion':
                $curApp = 'promotionModule';
                break;
            case 'seckill':
                $curApp = 'promotionModule';
                break;
            case 'giftActivity':
                $curApp = 'promotionModule';
                break;
            case 'distributor':
                $curApp = 'fxModule';
                break;
            case 'fxRuler':
                $curApp = 'fxModule';
                break;
            case 'fxupgrade':
                $curApp = 'fxModule';
                break;
            case 'fxLev':
                $curApp = 'fxModule';
                break;
            case 'fxcash':
                $curApp = 'fxModule';
                break;
            case 'fxOrder':
                $curApp = 'fxModule';
                break;
            case 'fxstat':
                $curApp = 'fxModule';
                break;
            case 'fxSite':
                $curApp = 'fxModule';
                break;
            case 'statistics':
                $curApp = 'analysis';
                break;
            case 'keFu':
                $curApp = 'ImModule';
                break;
            case 'msg':
                $curApp = 'ImModule';
                break;
            case 'odmUser':
                $curApp = 'omd';
                break;
            case 'coupon';
                $curApp = 'promotionModule';
                break;
            default:
                break;
        }
        $this->assign('menuData', array_values($menuData));
        // print_r($menuData);exit;
        $this->assign('app', $curApp);
        $this->assign('act', $curAct);
        $this->assign('curapp', APP);
    }

    /*
     * 获取当前店铺的币种
     * @author wanyan
     * @date 2017-10-27 14:31:32
     */

    public function getSymbol() {
        $storeMod = &m('store');
        $sql = "select c.symbol from " . DB_PREFIX . "store as s
                left join " . DB_PREFIX . "currency as c on s.currency_id = c.id where s.id = '{$this->storeId}'";
        $rs = $storeMod->querySql($sql);
        return $rs[0]['symbol'];
    }

    /*
     * 输出排序
     * @author lee
     * @date 2017-8-7 14:31:32
     */

    function pre($content) {
        echo "<pre>";
        print_r($content);
        echo "</pre>";
    }

    /**
     * 查询指定的sql语句
     * @author dyg
     * @param string $sql
     * @return array
     */
    public function querySql($sql) {
        $result = array();
        $res = $this->db->Execute($sql);
        $result = $res ? $res->getArray() : array();
        return $result;
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
     * 添加后台操作日志
     * @author lvj
     * @date 2016-11-18
     * @param $note
     */
    public function addLog($note = '') {
//        $authMod = &m("systemAuth");
        $logMod = &m("systemLog");
        $app = APP;
        $act = ACT;
        $data = array(
            'user_id' => $this->accountId,
            'username' => $this->accountName,
            'add_time' => time(),
            'ip' => real_ip(),
            'app' => $app,
            'act' => $act,
            'note' => $note,
        );
        $logMod->doInsert($data);
        return $app;
    }

    /**
     * wangh
     * 加载语言包文件
     * @param $lang_id
     * @param $filename  如: default/index
     * @return array
     */
    public function load($lang_id, $filename) {
        $_ = array(); //语言包数组

        if ($lang_id == 1) {
            $folders = 'english';
        } else {
            $folders = 'chinese';
        }

        $file = ROOT_PATH . '/languages/' . $folders . '/' . $filename . '.php';

        if (is_file($file)) {
            require($file);
        } else {
            return array();
        }
        $this->langData = array_merge($this->langData, $_);
    }

    /**
     * wanyan
     * 生成二维码方法
     * @return url
     */
    public function goodsCode($store_id, $lang_id, $gid) {
        include ROOT_PATH . "/includes/classes/class.qrcode.php"; // 生成二维码库
        $mainPath = ROOT_PATH . '/upload/goodCode';
        $this->mkDir($mainPath);
        $timePath = date('Ymd');
        $savePath = $mainPath . '/' . $timePath;
        $this->mkDir($savePath);
        $newFileName = uniqid() . ".png";
        $filename = $savePath . '/' . $newFileName;
        $pathName = 'upload/goodCode/' . $timePath . '/' . $newFileName;
        $http_host = $_SERVER['HTTP_HOST'];
        $valueUrl = 'http://' . $http_host . "/bspm711/wx.php?app=goods&act=goodInfo&storeid={$store_id}&lang={$lang_id}&gid={$gid}";
        QRcode::png($valueUrl, $filename);
        return $pathName;
    }

    /**
     * wanyan
     * 生成目录的方法
     */
    public function mkDir($dir) {
        if (!is_dir($dir)) {
            @mkdir($dir);
            @chmod($dir, 0777);
            @exec('chmod -R 777 {$dir}');
        }
    }

    /*
     * 获取区域国家所有STORE_ID
     */

    public function getStoreIds($cate_id) {
        $storeMod = &m('store');
        $data = $storeMod->getData(array("cond" => "store_cate_id=" . $cate_id, "fields" => "id"));
        if ($data) {
            $ids = $ids = implode(',', $this->arrayColumn($data, "id"));
        } else {
            $ids = '';
        }
        return $ids;
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


    /**
     * 添加后台操作日志
     * @author lvj
     * @date 2016-11-18
     * @param $note
     */
    public function addStoreLog($note = '') {
//        $authMod = &m("systemAuth");
        $logMod = &m("systemstoreLog");
        $app = APP;
        $act = ACT;
        $data = array(
            'user_id' => $this->storeUserId,
            'username' => $this->rolename,
            'add_time' => time(),
            'ip' => real_ip(),
            'app' => $app,
            'act' => $act,
            'note' => $note,
        );
        $logMod->doInsert($data);
        return $app;
    }


}

?>