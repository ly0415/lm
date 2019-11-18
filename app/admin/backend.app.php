<?php
/**
 * 超管父类
 * @author luffy
 * @date 2016-09-23
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class BackendApp extends BaseApp {

    public $accountId, $accountInfo, $accountName, $roleName, $roleCountry, $allCountry, $shorthand, $lang_id;
    public $langData = array();   // 中英语言包数据

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->accountId    = $_SESSION['account_id'];
        $this->accountName  = $_SESSION['account_name'];
        $this->assign('account_name', $_SESSION['account_name']);
        $this->roleName = $_SESSION['rolename'];
        $this->assign('roleName', $this->roleName);
        //角色权限判断 modify by lee
        $this->roleCountry = ($_SESSION['admin']['store_country']) ? $_SESSION['admin']['store_country'] : '';
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言（0 中文  1 英文）

        //SSL证书判别
        if( $_SERVER['REQUEST_SCHEME'] == 'https' ){
            $this->assign('SITE_URL', SITE_URL_SSL);
            $this->assign('STATIC_URL', STATIC_URL_SSL);
        } else {
            $this->assign('SITE_URL', SITE_URL);
            $this->assign('STATIC_URL', STATIC_URL);
        }

        //获取当前国家总站对应默认语言ID
        if (empty($_SESSION['admin']['lang_id'])){
            $langMod = &m('language');
            $langInfo = $langMod->getOne(array('cond' => "is_default = 2 AND enable = 1"));
            switch($langInfo['shorthand']){
                case 'ZH':
                    $_SESSION['admin']['lang_num']  = 0;
                    break;
                case 'EN':
                    $_SESSION['admin']['lang_num']  = 1;
                    break;
            }
            $_SESSION['admin']['lang_id']   = $langInfo['id'];
            $_SESSION['admin']['shorthand'] = $langInfo['shorthand'];
        }
        $this->lang_id      = $_SESSION['admin']['lang_id'];
        $this->shorthand    = $_SESSION['admin']['shorthand'];
        $this->assign('lang_id'     , $_SESSION['admin']['lang_id']);
        $this->assign('shorthand'   , $_SESSION['admin']['shorthand']);

        //根据语言ID获取该语言全部国家
        $storeCateMod       = &m('storeCate');
        $this->allCountry   = $storeCateMod->getRelationDatas();

        //加载语言包
        $this->langDataBank = languageFun($this->shorthand);
        $this->assign('language1', $this->langDataBank->project);
        $this->assign('language2', $this->langDataBank->public);

        //不检测登录
        $nologin = array(
            'login',
            'createCode',
            'doLogin',
            'test',
            'goOnLogin'
        );
        if (!in_array(ACT, $nologin)) {
            if (!isset($_SESSION['account_id']) && ACT != 'login' && ACT != "captcha" && ACT != "ajaxlogin") {
                if (IS_AJAX) {
                    header("Location: ?app=account&act=login&url=" . urlencode(pageUrl()));
                } else {
                    header("Location: ?app=account&act=login&url=" . urlencode(pageUrl()));
                }
                exit();
            } else {
                if ($_SESSION['account_id']) { //已登录
                    $this->checkUserStatus();
                    $this->menu();
                }
            }
        }
        //不检测登录
    }

    /**
     * 检测用户是否操作是否过期
     */
    public function  checkUserStatus(){
        $frontAccountMod = &m('adminAccountSession');
        if( $this->accountId){
            $httpHost = $_SERVER['HTTP_HOST'];
            //$url ='http://'.$httpHost."/bspm711/admin.php?app=account&act=login";
            $url ="admin.php?app=account&act=login";
            $accountSessionInfo = $frontAccountMod->getOne(array('cond'=>"`user_id` = {$this->accountId}",'fields' => 'login_time,session_id,id'));
            if($accountSessionInfo['session_id']){
                if (session_id() != $accountSessionInfo['session_id']) {
                    session_destroy();
                    if (isset($_COOKIE[session_name()])) {
                        setcookie(session_name(), '', time() - 3600);
                    }
                    echo '<script>alert("该账号在其他地方登录!"); window.location = "?app=account&act=login"</script>';
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
//                    echo '<script>alert("该账号无操作10分钟后过期!"); window.location = "?app=account&act=login"</script>';
////                    }
//                }else{
                    $sessEditData =array(
                        'login_time' => time()
                    );
                    $id =   $frontAccountMod->doEdit($accountSessionInfo['id'],$sessEditData);
//                }
            }
        }

    }

    /**
     * 菜单管理
     * @author huxw
     * @date 2015-03-10
     */
    public function menu() {
        //中英切换
        $curApp = APP;
        $curAct = ACT;
        $authMod = &m("systemAuth");
        $menuData = $authMod->getAuthList();
        switch ($curApp) {
            case 'systemLog':
                $curApp = 'system';
                break;
            case 'webconfig':
                $curApp = 'system';
                break;
            case 'storeMang':
                $curApp = 'store';
                break;
            case 'storeGrade':
                $curApp = 'store';
                break;
            case 'goodsType':
                $curApp = 'goodsModule';
                break;
            case 'goodsClass':
                $curApp = 'goodsModule';
                break;
            case 'goodsAttribute':
                $curApp = 'goodsModule';
                break;
            case 'goodsSpec':
                $curApp = 'goodsModule';
                break;
            case 'goodsBrand':
                $curApp = 'goodsModule';
                break;
            case 'roomType':
                $curApp = 'goodsModule';
                break;
            case 'roomTypeCate':
                $curApp = 'goodsModule';
                break;
            case 'goodsStyle':
                $curApp = 'goodsModule';
                break;
            case 'goods':
                $curApp = 'goodsModule';
                break;
            case 'storeBrand':
                $curApp = 'store';
                break;
            case 'article':
                $curApp = 'articleModule';
                break;
            case 'articleCtg':
                $curApp = 'articleModule';
                break;
            case 'goodsComment':
                $curApp = 'orderModule';
                break;
            case 'orderList':
                $curApp = 'orderModule';
                break;
            case 'orderInvoice':
                $curApp = 'orderModule';
                break;
            case 'adv':
                $curApp = 'advModule';
                break;
            case 'advPosition':
                $curApp = 'advModule';
                break;
            case 'systemRole':
                $curApp = 'authManage';
                break;
            case 'systemAuth':
                $curApp = 'authManage';
                break;
            case 'account':
                $curApp = 'authManage';
                break;
            case 'storeCate':
                $curApp = 'store';
                break;
            case 'areaStore':
                $curApp = 'store';
                break;
             case 'storeUser':
                $curApp = 'store';
                break;
             case 'regionModule':
                $curApp = 'store';
                break;
             case 'storeUserAdmin':
                $curApp = 'store';
                break;
             case 'odmUser':
                $curApp = 'store';
                break;
            case 'storeUser':
                $curApp = 'store';
                break;
            case 'areaGood':
                $curApp = 'store';
                break;
            case 'user':
                $curApp = 'userMod';
                break;
            case 'userPoint':
                $curApp = 'userMod';
                break;
             case 'pointLog';
                $curApp='userMod';
                break;
              case 'balanceRecharge';
                $curApp='userMod';
                break;
             case 'balanceAudit';
                $curApp='userMod';
                break;
            case 'language':
                $curApp = 'system';
                break;
            case 'currency':
                $curApp = 'system';
                break;
              case 'designerList':
                $curApp = 'system';
                break;
            case 'logistics':
                $curApp = 'orderModule';
                break;
            case 'fxRuler':
                $curApp = 'Distribution';
                break;
            case 'fxStoreSetting':
                $curApp = 'Distribution';
                break;
            case 'fxOrder':
                $curApp = 'Distribution';
                break;
            case 'fxstat':
                $curApp = 'countManage';
                break;
            case 'userStat':
                $curApp = 'countManage';
                break;
               case 'balanceStat':
                $curApp = 'countManage';
                break;
            case 'fxDiscoutLog':
                $curApp = 'Distribution';
                break;
            case 'fxmember':
                $curApp = 'Distribution';
                break;
            case 'fxupgrade':
                $curApp = 'Distribution';
                break;
            case 'keFu':
                $curApp = 'ImModule';
                break;
            case 'msg':
                $curApp = 'ImModule';
                break;
            case 'statistics':
                $curApp = 'countManage';
                break;
            case 'systemConsole':
                $curApp = 'authManage';
                break;
            case 'distributor':
                $curApp = 'Distribution';
                break;

            default:
                break;
        }
        $this->assign('menuData', array_values($menuData));
        $this->assign('app', $curApp);
        $this->assign('act', $curAct);
        $this->assign('curapp', APP);
    }

    /**
     * 根据店铺ID 获取 店铺所属国家
     */
    public function getCountryId($store_id) {
        $storeMod = &m('store');
        $res = $storeMod->getOne(array('cond' => "`id`='{$store_id}'", 'fields' => "store_cate_id"));
        return $res['store_cate_id'];
    }
    /**
     * 根据国家ID 获取 语言id
     */
    public function getLanguageById($country_id) {
        $storeMod = &m('storeCate');
        $res = $storeMod->getOne(array('cond' => "`id`='{$country_id}'", 'fields' => "lang_id"));
        return $res['lang_id'];
    }
    /**
     * 根据国家ID 获取 语言id
     */
    public function getLanguageByIds($country_ids) {
        $storeMod = &m('storeCate');
        $res = $storeMod->getData(array('cond' => "`id` in ({$country_ids})", 'fields' => "lang_id"));
        return $res['lang_id'];
    }

    /**
     * 根据店铺ID 获取 店铺多个所属国家s
     */
    public function getCountryIds($store_id) {
        $storeMod = &m('store');
        $store_id = rtrim($store_id, ',');
        $res = $storeMod->getData(array('cond' => "`id` in ({$store_id})", 'fields' => "GROUP_CONCAT(`store_cate_id`) as store_cate_ids"));
        return $res[0]['store_cate_ids'];
    }

    /**
     * 根据国家分类获取币种
     */
    public function getSymbol($store_cate) {
        $storeCateMod = &m('storeCate');
        $sql = "SELECT c.symbol FROM `bs_store_cate` as sc LEFT JOIN `bs_currency` as c ON sc.currency_id = c.id where sc.id=" . $store_cate;
        $rs = $storeCateMod->querySql($sql);
        return $rs[0]['symbol'];
    }

    /**
     * 获取部门、分类等下拉菜单数据
     * @author jh
     * @date 2017/06/28
     */
    public function getSelectInfo($table = 'system_department', $parentId = 0, $level = 0) {
        $mdl = &m('userUser');
        $sql = 'select * from ' . DB_PREFIX . $table . ' where mark =1 and parent_id = ' . $parentId . ' order by sort asc';
        $data = $mdl->querySql($sql);
        $result = array();
        foreach ($data as $v) {
            $v['name'] = str_repeat(' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $level) . '|—' . $v['name'];
            $result[] = $v;
            //递归调用
            if ($child = $this->getSelectInfo($table, $v['id'], $level + 1)) {
                $result = array_merge($result, $child);
            }
        }
        return $result;
    }
    /*
     * 获取所有的国家
     * @author wanyan
     * @date 2017-9-27 10:40:15
     */
    public function getAllCountry(){
        $storeCateMod = &m('storeCate');
        $sql = "select sc.`id`,scl.cate_name from ".DB_PREFIX."store_cate as sc left join ".DB_PREFIX."store_cate_lang as scl  ON sc.id = scl.cate_id where sc.is_open = 1 and scl.lang_id = {$this->lang_id} ";
        $rs = $storeCateMod->querySql($sql);
        return $rs;
    }

    /*
     * 获取语言列表
     * @author lee
     * @date 2017-9-27 10:40:15
     */

    public function getLanguage($cond = null) {
        $langMod = &m('language');
        $list = $langMod->getData(array("cond" => $cond));
        return $list;
    }

    /**
     * 获取分类下面的所有子分类
     * @author jh
     * @date 2017/07/03
     */
    public function getChildids($table = 'system_department', $idArr = array(0)) {
        $mdl = &m('userUser');
        $ids = implode(',', $idArr);
        $sql = 'select id from ' . DB_PREFIX . $table . ' where mark =1 and parent_id in (' . $ids . ') ';
        $data = $mdl->querySql($sql);
        $result = $idArr;
        if (!empty($data)) {
            $temp = $this->arrayColumn($data, 'id');
            $childArr = $this->getChildids($table, $temp);
            $result = array_merge($idArr, $childArr);
        }
        return $result;
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
//        $data['commonParams'] = array(
//            'adminId' => $this->adminId,
//            'username' => $this->adminInfo['username'],
//            'password' => $this->adminInfo['password'],
//            'head_img' => $this->adminInfo['head_img'],
//            'erwm_img' => $this->adminInfo['erwm_img'],
//        );
        echo json_encode($data);
        exit();
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
     * 附件图片上传
     * @author zhangr
     * @date 2017-6-21
     */
    public function upload_img() {
        $this->load($this->lang_id, 'admin/admin');
        $a = $this->langData;
        if (IS_POST) {
            $fileName = $_FILES['fileName']['name'];
            $type = strtolower(substr(strrchr($fileName, '.'), 1)); //获取文件类型
            $info = array();
            if (!in_array($type, array('jpg', 'png', 'jpeg', 'gif', 'JPG', 'PNG', 'JPEG', 'GIF'))) {
                $this->setData($info, $status = 'error', $a['add_upload']);
            }
            $savePath = "upload/images/" . date("Y-m-d");
            // 判断文件夹是否存在否则创建
            if (!file_exists($savePath)) {
                @mkdir($savePath, 0777, true);
                @chmod($savePath, 0777);
                @exec("chmod 777 {$savePath}");
            }
            $filePath = $_FILES['fileName']['tmp_name']; //文件路径
            //$object = ROOT_PATH."/upload/" . time() . uniqid() . '.' . $type;
            $url = $savePath . '/' . time() . uniqid() . '.' . $type;
            if (!is_uploaded_file($filePath)) {
                exit($a['Temporary']);
            }
            //上传文件
            if (!move_uploaded_file($filePath, $url)) {
                $this->setData(array(), '0', $a['add_Success']);
            }
            $data = array(
                "url" => $url,
            );
            echo json_encode($data);
        } else {
            $this->setData($info = array(), 2, $a['System_error']);
        }
    }

    /**
     * 上传图片
     * @author zhoux
     * @date 2016-10-1
     * */
    public function upload() {
        $img_id = !empty($_REQUEST['img_id']) ? htmlspecialchars($_REQUEST['img_id']) : '';
        $info = array();
        $fileName = $_FILES[$img_id]['name'];
        $type = strtolower(substr(strrchr($fileName, '.'), 1)); //获取文件类型
        $filePath = $_FILES[$img_id]['tmp_name']; //文件路径
        $pic = $_FILES;
        $object = "upload/" . time() . uniqid() . '.' . $type;
        $imgInfo = $this->uploads($pic, $object, $filePath);
        $url = $imgInfo['url'];
        $size = getimagesize($url);
        $width = (int) $size['0'];
        $height = (int) $size['1'];
        $data = array(
            "name" => $fileName,
            "width" => $width,
            "height" => $height,
            "url" => $url,
            "add_time" => time(),
        );
        $mod = &m('image');
        if ($imgInfo['status'] == 'success') {
            $res = $mod->doInsert($data);
            if ($res) {
                $info = array("image_id" => $res, "imgurl" => $url);
                $this->setData($info, $status = '1', $message = '上传成功！');
            } else {
                $this->setData($info, $status = '0', '添加失败！');
            }
        } else {
            $this->setData($info, $status = '0', '上传失败！');
        }
    }

    /* ???? */

    public function ewmImg($result) {
        $info = array();
//    $Model = &m('userAuthentication');
        if (!$result) {
            $this->setData((object) $info, $status = 'error', $message = '缺少参数');
        }

        require_once(dirname(__FILE__) . '/phpqrcode.php');
        $value = "http://" . $_SERVER['HTTP_HOST'] . "/bsp/phone.php?app=user&act=fastReg&hjuser_id=" . $result; //二维码内容
//    $value = "http://139.196.73.211/bsp/phone.php?app=user&act=fastReg&hjuser_id=".$result; //二维码内容

        $errorCorrectionLevel = 'L'; //容错级别
        $matrixPointSize = 6; //生成图片大小
        $qrcode = ROOT_PATH . "/upload/hjuser/erwmimages/" . $result . ".png";

        if (file_exists("$qrcode")) {
            $qrcode = "http://" . $_SERVER['HTTP_HOST'] . "/bsp/upload/hjuser/erwmimages/" . $result . ".png";
//      $qrcode = "http://139.196.73.211/bsp/upload/hjuser/erwmimages/".$result.".png";
        } else {
            //生成二维码图片
            $QRcode = QRcode::png($value, $qrcode, $errorCorrectionLevel, $matrixPointSize, 2);
            $qrcode = "http://" . $_SERVER['HTTP_HOST'] . "/bsp/upload/hjuser/erwmimages/" . $result . ".png";
//      $qrcode = "http://139.196.73.211/bsp/upload/hjuser/erwmimages/".$result.".png";
        }
//    $content = file_get_contents($qrcode);
//    if(!$content){
//      $qrcode = "http://139.224.234.184/bsp/upload/hjuser/erwmimages/".$result.".png";
//    }
        $return = $this->moveImages($qrcode, "upload/hjuser/erwmimages/" . $result . ".png");
        if ($return['status'] == 'success') {
//      $info['ewm_img'] = $return['url'];
            return $return['url'];
        } else {
            $this->setData((object) $info, 'error', '二维码生成失败！');
        }
//    $info['ewm_img'] =  $return['url'];
//    $Model -> edit($info , $result);
    }

    /**
     * 上传图片  上传到阿里云oss
     * @author zhoul
     * @date 2017-5-5
     * @param $pic 图片信息  即$_FILES
     * @param $object 需要上传到的目录层级和图片名
     * @param $filePath 本地图片路径
     * */
//    public function uploads($pic, $object, $filePath){
//        $ossClient = new \OSS\OssClient(self::accessKeyId, self::accessKeySecret, self::endpoint);
////    $object = $pic['fileName']['name'];//文件名
////    $type = strtolower(substr(strrchr($fileName, '.'), 1)); //获取文件类型
////    $object = $object.time().uniqid().'.'.$type;
////    $filePath = $pic['fileName']['tmp_name'];//文件路径
//        try {
//            $res = $ossClient->uploadFile(self::bucket, $object, $filePath);
//            unlink($filePath);//删除上传到本地的文件
//            if ($res) {
//                $return = array('status' => 'success', 'url' => $res['info']['url']);
//            }
//        } catch (OssException $e) {
////      printf(__FUNCTION__ . ": FAILED\n");
////      printf($e->getMessage() . "\n");
//            $return = array('status' => 'error', 'message' => $e->getMessage());
//        }
//        return $return;
//    }
    /**
     * 多图片上传
     * @author zhoul
     * @date 2017-5-5
     * @param $tmpFiles 图片信息  即$_FILES
     * @param $dir 需要上传到的目录层级
     * */
    public function uploadImages($tmpFiles, $dir) {
        $info = array();
        $mod = &m('image');
        foreach ($tmpFiles as $k => $v) {
            $fileName = $v['name'];
            $filePath = $v['tmp_name']; //文件路径
            $type = strtolower(substr(strrchr($fileName, '.'), 1)); //获取文件类型
            $object = $dir . time() . uniqid() . '.' . $type;
            $imgInfo = $this->uploads($tmpFiles[$k], $object, $filePath);
            if ($imgInfo['status'] == 'success') {
                $url = $imgInfo['url'];
                $size = getimagesize($url);
                $width = (int) $size['0'];
                $height = (int) $size['1'];
                $data = array(
                    "name" => $fileName,
                    "width" => $width,
                    "height" => $height,
                    "url" => $url,
                    "add_time" => time(),
                );
                $res = $mod->doInsert($data);
                if ($res) {
                    $images[$k] = array("image_id" => $res, "imgurl" => $url);
                }
            }
        }
        if (count($images) > 0) {
            return $images;
        } else {
            $this->setData((object) $info, $status = 'error', $message = '上传图片失败!');
        }
    }

    /**
     * wanyan
     * 生成二维码方法
     * @return url
     */

    public function goodsCode($store_id,$lang_id,$gid){
        include ROOT_PATH."/includes/classes/class.qrcode.php"; // 生成二维码库
        $mainPath = ROOT_PATH.'/upload/goodCode';
        $this->mkDir($mainPath);
        $timePath = date('Ymd');
        $savePath = $mainPath.'/'.$timePath;
        $this->mkDir($savePath);
        $newFileName = uniqid().".png";
        $filename = $savePath.'/'.$newFileName;
        $pathName = 'upload/goodCode/'.$timePath.'/'.$newFileName;
        $http_host = $_SERVER['HTTP_HOST'];
        $valueUrl = 'http://'.$http_host."/bspm711/wx.php?app=goods&act=goodInfo&storeid={$store_id}&lang={$lang_id}&gid={$gid}";
        QRcode::png($valueUrl,$filename);
        return $pathName;
    }

    /**
     * wanyan
     * 生成目录的方法
    */
    public function mkDir($dir){
        if(!is_dir($dir)){
            @mkdir($dir);
            @chmod($dir,0777);
            @exec('chmod -R 777 {$dir}');
        }
    }
    /**
     * wanyan
     * 生成二维码方法
     * @return url
     */

    public function goodsZcode($goods_id){
        include ROOT_PATH."/includes/classes/class.qrcode.php"; // 生成二维码库
        $mainPath = ROOT_PATH.'/upload/goodCode';
        $this->mkDir($mainPath);
        $timePath = date('Ymd');
        $savePath = $mainPath.'/'.$timePath;
        $this->mkDir($savePath);
        $newFileName = uniqid().".png";
        $filename = $savePath.'/'.$newFileName;
        $pathName = 'upload/goodCode/'.$timePath.'/'.$newFileName;
        $http_host = $_SERVER['HTTP_HOST'];
        if(SYSTEM_WEB=="www.njbsds.cn"){
            $valueUrl = 'http://'.SYSTEM_WEB."/bspm711/wx.php?app=goods&act=goodInfo&goods_id={$goods_id}";
        }else{
            $valueUrl = 'http://'.SYSTEM_WEB."/wx.php?app=goods&act=goodInfo&goods_id={$goods_id}";
        }


        QRcode::png($valueUrl,$filename);
        return $pathName;
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



}

?>