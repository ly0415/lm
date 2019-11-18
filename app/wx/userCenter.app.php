<?php

/**
 * 用户中心
 * @author lvji
 *
 */
//include_once 'sms.app.php';
//include_once 'mail.app.php';
class userCenterApp extends BaseWxApp {

    private $footPrintMod;
    private $colleCtionMod;
    private $userArticleMod;
    private $orderMod;
    private $orderGoodsMod;
    private $commentMod;
    private $fxUserMod;
    private $goodsCommentMod;
    private $fxRuleMod;
//    private $fxUserTreeMod;
    private $fxRevenueLogMod;
    private $userMod;
    private $storeMod;
    private $fxTreeMod;
    private $cityMod;
    private $countryMod;
    private $fxuserMoneyMod;
    private $storeCateMod;
    private $pointLogMod;
    private $userStoreMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();

        $this->storeCateMod = &m('storeCate');
        $this->userMod = &m('user');
        $this->fxuserMoneyMod = &m('fxuserMoney');
//        $this->fxTreeMod = &m('fxuserTree');
        $this->storeMod = &m('store');
        $this->footPrintMod = &m('footprint');
        $this->colleCtionMod = &m('colleCtion');
        $this->countryMod = &m('country');
        $this->userArticleMod = &m('userArticle');
        $this->orderMod = &m('order');
        $this->orderGoodsMod = &m('orderGoods');
        $this->commentMod = &m('goodsComment');
        $this->fxUserMod = &m('fxuser');
        $this->fxRuleMod = &m('fxrule');
        $this->fxRevenueLogMod = &m('fxRevenueLog');
        $this->assign('storeid', $this->storeid);
        $this->goodsCommentMod = &m('goodsComment');
        $this->storeGoodsMod = &m('goods');
        $this->cityMod = &m('city');
//        $this->fxUserTreeMod = &m('fxuserTree');
        $this->pointLogMod = &m('pointLog');
        $this->userStoreMod = &m('userStore');
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;

        //判断是否登录
      /*  if (!isset($_SESSION['userId'])) {
            header('Location: wx.php?app=user&act=loginOuth&back_app=' . APP . '&back_act=' . ACT . '&storeid=' . $this->storeid . '&lang=' . $this->langid . '&latlon=' . $latlon);
        }*/
    }

    /**
     * 微信授权，静默方式
     */
//    public function outh() {
//        $redirectUrl = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?app=userCenter&act=myCenter';
//        $url = $this->getOAuthUrl($redirectUrl, 'snsapi_base', 1);
//        header("Location:" . $url);
//    }

    /*
     * 个人中心
     * @author wangs
     * @date 2017-11-22
     */

    public function myCenter() {
        //如果cookie过期了，，重新获取
        if (!isset($_COOKIE['wx_openid'])) {
            $url = "wx.php?app=user&act=loginOuth&back_app=userCenter&back_act=myCenter&storeid=" . $this->storeid . "&lang=" . $this->langid;
            header("Location:$url");
        } elseif (empty($_SESSION['userId'])) {
            $url = 'wx.php?app=user&act=quickLogin';
            header("Location:$url");
        }
        //
        $userId = $this->userId;
        $sql = "select * from " . DB_PREFIX . "user where id=" . $userId;
        $res = $this->userMod->querySql($sql);
//        $mysql_uinfo = $this->getOpenid();
        if (empty($mysql_uinfo['openid'])) {
            //从公众号拉取 ，放到数据库
            //回调页面
//            $code = $_REQUEST['code'];
//            $accessTokenInfo = $this->getoAuthAccessToken($code);
//            $OuthToken = $accessTokenInfo->access_token;
//            $openid = $accessTokenInfo->openid;
//            $userInfo = $this->getUserInfo($OuthToken, $openid);
            //从cookie 里 获取openid
            $wx_openid = $_COOKIE['wx_openid'];
            $wx_nickname = $_COOKIE['wx_nickname'];
            $wx_city = $_COOKIE['wx_city'];
            $wx_province = $_COOKIE['wx_province'];
            $wx_country = $_COOKIE['wx_country'];
            $wx_headimgurl = $_COOKIE['wx_headimgurl'];
            $wx_sex = $_COOKIE['wx_sex'];
            $systemNickName = '艾美睿'.rand(100000,999999);
            $wx_userInfo = array(
                'openid' => $wx_openid,
                'nickname' => $systemNickName,
                'city' => $wx_city,
                'province' => $wx_province,
                'country' => $wx_country,
                'headimgurl' => $wx_headimgurl,
                'sex' => $wx_sex,
            );

            //向数据库插入微信信息
            $this->insertWxinfo($wx_userInfo);

            $headimg = $wx_headimgurl;
            $nickname = $res[0]['username'];
            $balance = $res[0]['amount'];
            $integral = $res[0]['point'];
        } else {
            $headimg = $mysql_uinfo['headimgurl'];
            $nickname = $res[0]['username'];
            $balance = $res[0]['amount'];
            $integral = $res[0]['point'];
        }
        $this->assign('headimg', $headimg);
        $this->assign('nickname', $nickname);
        $this->assign('balance', $balance);
        $this->assign('integral', $integral);
        $this->assign('symbol', $this->symbol);
        $this->assign('res', $res[0]);
        //语言包
        $this->load($this->shorthand, 'WeChat/userCenter');
        $this->assign('langdata', $this->langData);
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $this->assign('storeid', $storeid);

        $this->assign('lang', $lang);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $this->assign('latlon', $latlon);
        $this->assign('userId', $this->userId);
        $this->display("userCenter/personalCenter.html");
    }

    /*
     * 个人资料
     * @author wangs
     * @date 2017-11-22
     */

    public function userInfo() {
        //语言包
        $this->load($this->shorthand, 'WeChat/userCenter');
        $this->assign('langdata', $this->langData);
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $this->assign('latlon', $latlon);

        $userId = $this->userId;
        $sql = "select * from " . DB_PREFIX . "user where id=" . $userId;
        $res = $this->userMod->querySql($sql);
        $this->assign('access_token', $this->accessToken);
        $this->assign('res', $res[0]);
        $this->assign('lang', $lang);
        $this->assign('storeid', $storeid);
        $this->display("userCenter/person-data.html");
    }

    /*
     * 我的二维码
     * @author wangs
     * @date 2018-6-21
     */

    public function qrCode() {
        //语言包
        $this->load($this->shorthand, 'WeChat/userCenter');
        $this->assign('langdata', $this->langData);
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $this->assign('latlon', $latlon);

        $userId = $this->userId;
        $sql = "select * from " . DB_PREFIX . "user where id=" . $userId;
        $res = $this->userMod->querySql($sql);
        $this->assign('access_token', $this->accessToken);
        $this->assign('res', $res[0]);
        $this->assign('lang', $lang);
        $this->assign('storeid', $storeid);
        $this->display("userCenter/person-qrcode.html");
    }

    /*
     * 生成二维码
     * @author wangs
     * @date 2018-6-21
     */

    public function doCode() {
        //语言包
        $this->load($this->shorthand, 'WeChat/address');
        $a = $this->langData;
        $user_id = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : $this->userId;  //用户ID
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;  //用户ID
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;  //语言ID
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        //生成2维码
        $codee = $this->goodsZcode($user_id, $storeid, $lang, $latlon);
        $urldata = array(
            "table" => "user",
            'cond' => 'id = ' . $user_id,
            'set' => "user_url='" . $codee . "'",
        );
        $ress = $this->userMod->doUpdate($urldata);
        if ($ress) {
            $info['url'] = "?app=default&act=myShare&storeid={$storeid}&lang={$lang}&auxiliary={$auxiliary}&latlon={$latlon}";
            $this->setData($info, $status = '1', $a['Reweima_Success']); //生成二维码失败
        } else {
            $this->setData($info, $status = '0', $a['Reweima_fail']); //生成二维码失败
        }
    }

    public function goodsZcode($user_id, $storeid, $lang, $latlon) {
        include ROOT_PATH . "/includes/classes/class.qrcode.php"; // 生成二维码库
        $mainPath = ROOT_PATH . '/upload/userCode';
        $this->mkDir($mainPath);
        $timePath = date('Ymd');
        $savePath = $mainPath . '/' . $timePath;
        $this->mkDir($savePath);
        $newFileName = uniqid() . ".png";
        $filename = $savePath . '/' . $newFileName;
        $pathName = 'upload/userCode/' . $timePath . '/' . $newFileName;
        $http_host = $_SERVER['HTTP_HOST'];
      $system_web = 'www.njbsds.cn';
//        $system_web = 'www.711home.net';
        $serverName='www.711home.net';
        if($http_host==$system_web){
            $valueUrl = 'http://'.$system_web."/bspm711/wx.php?app=user&act=quickLogin&userId={$user_id}&storeid={$storeid}&lang={$lang}&&auxiliary=0&latlon={$latlon}";
        }else{
            $valueUrl = 'http://'.$serverName."/wx.php?app=user&act=quickLogin&userId={$user_id}&storeid={$storeid}&lang={$lang}&&auxiliary=0&latlon={$latlon}";
        }
        QRcode::png($valueUrl, $filename);
        return $pathName;
    }

    /*
     * 代客下单生成二维码
     * @author wangs
     * @date 2018-6-21
     */

    public function orderCode() {
        //语言包
        $this->load($this->shorthand, 'WeChat/address');
        $a = $this->langData;
        $user_id = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : $this->userId;  //用户ID
        $phone = !empty($_REQUEST['phone']) ? htmlspecialchars(trim($_REQUEST['phone'])) : '';
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;  //用户ID
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;  //语言ID
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        //生成2维码
        $codee = $this->orderZcode($phone);
        $urldata = array(
            "table" => "user",
            'cond' => 'id = ' . $user_id,
            'set' => "order_url='" . $codee . "'",
        );
        $ress = $this->userMod->doUpdate($urldata);
        if ($ress) {
            $info['url'] = "?app=userCenter&act=myCenter&storeid={$storeid}&lang={$lang}&auxiliary={$auxiliary}&latlon={$latlon}";
            $this->setData($info, $status = '1', $a['Reweima_Success']); //生成二维码失败
        } else {
            $this->setData($info, $status = '0', $a['Reweima_fail']); //生成二维码失败
        }
    }

    public function orderZcode($phone) {
        include ROOT_PATH . "/includes/classes/class.qrcode.php"; // 生成二维码库
        $mainPath = ROOT_PATH . '/upload/orderCode';
        $this->mkDir($mainPath);
        $timePath = date('Ymd');
        $savePath = $mainPath . '/' . $timePath;
        $this->mkDir($savePath);
        $newFileName = uniqid() . ".png";
        $filename = $savePath . '/' . $newFileName;
        $pathName = 'upload/orderCode/' . $timePath . '/' . $newFileName;
//        $http_host = $_SERVER['HTTP_HOST'];
//         $system_web = 'www.711home.net';
        $valueUrl = "{$phone}";
        QRcode::png($valueUrl, $filename);
        return $pathName;
    }

    public function mkDir($dir) {
        if (!is_dir($dir)) {
            @mkdir($dir);
            @chmod($dir, 0777);
            @exec('chmod -R 777 {$dir}');
        }
    }

    /*
     * 个人资料修改
     * @author wangs
     * @date 2017-11-22
     */

    public function doUserInfo() {
        //语言包
        $this->load($this->shorthand, 'WeChat/userCenter');
        $a = $this->langData;
        $lang = !empty($_REQUEST['lang']) ? (int) ($_REQUEST['lang']) : '0';
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $goods_images = ($_REQUEST['order_pic']) ? $_REQUEST['order_pic'] : '';
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $arr = implode(',', $goods_images);
        $list = rtrim($arr, ',');
        $username = $_REQUEST['username'] ? $_REQUEST['username'] : "";
        $phone = trim($_REQUEST['phone']);
        $email = $_REQUEST['email'];
        $userId = $this->userId;
        $userMod = &m('user');
        $data = array(
            'username' => $username,
            'headimgurl' => $list,
        );
        $res = $userMod->doEdit($userId, $data);
        if ($phone) {
            if (!preg_match('/^(13[0-9]|14[0-9]|15[0-9]|17[0-9]|18[0-9])\d{8}$/', $phone)) {
                $this->setData(array(), $status = '0', $a['personal_Formattingerror']);
            }
            if ($this->userMod->isExist($type = 'phone', $phone, 'mark', 1)) {
                $this->setData(array(), $status = '0', $a['personal_Alreadyexisted']);
            }
            $data = array(
                'phone' => $phone,
            );
            $res = $userMod->doEdit($userId, $data);
            if ($res == false) {
                $this->setData(array(), $status = '0', $a['Failuretomodify']);
            }
        }
        if ($email) {
            if (!preg_match("/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/", $email)) {
                $this->setData(array(), $status = '0', $a['personal_Incorrect']);
            }
            if ($this->userMod->isExist($type = 'email', $email, 'mark', 1)) {
                $this->setData(array(), $status = '0', $a['personal_Username']);
            }
            $data = array(
                'email' => $email,
            );
            $res = $userMod->doEdit($userId, $data);
            if ($res == false) {
                $this->setData(array(), $status = '0', $a['Failuretomodify']);
            }
        }
        $info['url'] = "?app=userCenter&act=userInfo&storeid={$storeid}&lang={$lang}&auxiliary={$auxiliary}";
        $this->setData($info, $status = '1', $a['Amendthesuccess']);
    }

    /**
     * 微信服务器图片下载R
     * @author wangshuo
     * @date 2018-1-15
     */
    public function getUploadPicture() {
        $serverId = isset($_POST['serverId']) ? htmlspecialchars($_POST['serverId']) : '';
        $access_token = isset($_POST['access_token']) ? htmlspecialchars($_POST['access_token']) : '';
//        echo "<script type='text/javascript'>alert('已全部清除！');</script>";
        //下载图片
        $url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token={$access_token}&media_id={$serverId}";
        $dirName = 'upload/images/user/' . date('Ymd') . '/';
        $imageName = time() . rand(1000, 9999) . '.jpg';
        if (!file_exists($dirName)) {
            mkdir($dirName, 0777, true);
        }
        $uploadPath = $dirName . $imageName;
        $ch = curl_init($url); // 初始化
        $fp = fopen($uploadPath, 'wb'); // 打开写入
        curl_setopt($ch, CURLOPT_FILE, $fp); // 设置输出文件的位置，值是一个资源类型
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        $newDirName = './upload/images/user/' . date('Ymd') . '/';
        $this->setData(array('uploadPath' => $uploadPath, 'newDirName' => $newDirName, 'imageName' => $imageName), $status = 1, $message = '获取成功');
    }

    /*
     * 我的地址
     * @author wangs
     * @date 2017-11-22
     */

    /* public function myAddress() {
      //语言包
      $this->load($this->shorthand, 'WeChat/address');
      $this->assign('langdata', $this->langData);
      $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
      $this->assign('auxiliary', $auxiliary);
      $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
      $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
      $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
      $this->assign('latlon', $latlon);
      $addressMod = &m("userAddress");
      $zoneMod = &m('zone');
      $countryMod = &m('country');
      $cityMod = &m('city');
      $userId = $this->userId;
      $res = $addressMod->getOne(array("cond" => "user_id=" . $userId));
      $store_address = explode('_', $res['store_address']);
      if (count($store_address) == 3) {
      $this->assign('switch', 1);
      $ch_store_address = $store_address;
      $pros = $this->cityMod->getParentNodes();
      $pro = $cityMod->getOne(array("cond" => "id=" . $ch_store_address[0]));
      $pros['pro_name'] = $pro['name'];
      $city = $cityMod->getOne(array("cond" => "id=" . $ch_store_address[1]));
      $pros['city_name'] = $city['name'];
      $area = $cityMod->getOne(array("cond" => "id=" . $ch_store_address[2]));
      $pros['area_name'] = $area['name'];
      $this->assign('pros', $pros);
      } else {
      $this->assign('switch', 2);
      $countrys = $this->countryMod->getCountryNodes();
      $en_store_address = $store_address;
      $country = $countryMod->getOne(array("cond" => "country_id=" . $en_store_address[0]));
      $countrys['country_name'] = $country['name'];
      $province = $zoneMod->getOne(array("cond" => "zone_id=" . $en_store_address[1]));
      $countrys['province_name'] = $province['name'];
      $this->assign('countrys', $countrys);
      }
      $this->assign("info", $res);
      $this->assign('lang', $lang);
      $this->assign('storeid', $storeid);
      $language = $this->shorthand;
      $this->assign('language', $language);
      $this->display("userCenter/shipping-address.html");
      } */

    public function myAddress() {

        //语言包
        $this->load($this->shorthand, 'WeChat/address');
        $this->assign('langdata', $this->langData);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $returnUrl = !empty($_REQUEST['returnUrl']) ? $_REQUEST['returnUrl'] : '';

        if (empty($returnUrl)) {
            $this->assign('returnUrl', $_SESSION['returnUrl']);
        } else {
            $_SESSION['returnUrl'] = $returnUrl;
            $this->assign('returnUrl', $returnUrl);
        }
        $this->assign('url', urlencode($returnUrl));
        $addr_id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;  //多语言商品
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $item_id = !empty($_REQUEST['item_id']) ? htmlspecialchars(trim($_REQUEST['item_id'])) : '';
        $this->assign('latlon', $latlon);
        $this->assign('item_id', $item_id);
        $addressMod = &m("userAddress");
        $userAddress = $addressMod->getAddressById($this->userId);
        /* $detailAddress=$this->setAddress($userAddress['store_address']);
          $Address=$detailAddress.$userAddress['address']; */
        $addresss = explode('_', $userAddress['address']);
        $this->assign('city', $addresss[0]);
        $count = strpos($userAddress['address'], "_");
        $str = substr_replace($userAddress['address'], "", $count, 1);
        $this->assign('address', $str);
        /* $this->assign('detailAddress',$detailAddress); */
        $this->assign('userAddress', $userAddress);
        $userId = $this->userId;
        $sql = "select * from " . DB_PREFIX . "user_address where `user_id`='{$userId}' and distinguish=1";
        $res = $addressMod->querySql($sql);
        foreach ($res as $k => $v) {
            $store_address = explode('_', $v['address']);
            $res[$k]['address'] = $store_address[0];
        }
        $this->assign("res", $res);
        $this->assign('lang', $lang);
        $this->assign('storeid', $storeid);
        $this->assign('addr_id', $addr_id);
        $language = $this->shorthand;
        $this->assign('language', $language);
        $this->display("userCenter/shipping-address.html");
    }

    public function personAddress() {
        //语言包
        $this->load($this->shorthand, 'WeChat/address');
        $this->assign('langdata', $this->langData);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $this->assign('url', urlencode($returnUrl));
        $addr_id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;  //多语言商品
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $item_id = !empty($_REQUEST['item_id']) ? htmlspecialchars(trim($_REQUEST['item_id'])) : '';
        $this->assign('latlon', $latlon);
        $this->assign('item_id', $item_id);
        $addressMod = &m("userAddress");
        $userAddress = $addressMod->getAddressById($this->userId);
        /* $detailAddress=$this->setAddress($userAddress['store_address']);
          $Address=$detailAddress.$userAddress['address']; */
        $addresss = explode('_', $userAddress['address']);
        $this->assign('city', $addresss[0]);
        $count = strpos($userAddress['address'], "_");
        $str = substr_replace($userAddress['address'], "", $count, 1);
        $this->assign('address', $str);
        /* $this->assign('detailAddress',$detailAddress); */
        $this->assign('userAddress', $userAddress);
        $userId = $this->userId;
        $sql = "select * from " . DB_PREFIX . "user_address where `user_id`='{$userId}' and distinguish=1";
        $res = $addressMod->querySql($sql);
        foreach ($res as $k => $v) {
            $store_address = explode('_', $v['address']);
            $res[$k]['address'] = $store_address[0];
        }
        $this->assign("res", $res);
        $this->assign('lang', $lang);
        $this->assign('storeid', $storeid);
        $this->assign('addr_id', $addr_id);
        $language = $this->shorthand;
        $this->assign('language', $language);
        $this->display("userCenter/shipping-addr.html");
    }

    public function setAddress($areaAddress) {
        $areaAddress = explode('_', $areaAddress);
        if (count($areaAddress) == 3) {
            $result = $this->cityMod->getAreaName($areaAddress[0]) . ' ' . $this->cityMod->getAreaName($areaAddress[1]) . ' ' . $this->cityMod->getAreaName($areaAddress[2]);
        } elseif (count($areaAddress) == 2) {
            $country = $this->countryMod->getCountryName($areaAddress[0]);
            $zone = $this->zoneMod->getZoneName($areaAddress[1]);
            $result = $country . ' ' . $zone;
        }
        return $result;
    }

    /**
     * 获取中国地址
     * @author wanyan
     * @date 2017-9-05
     */
    public function getCity($id) {
        $sql = "select `id`,`name` from " . DB_PREFIX . "city where `parent_id`='{$id}'";
        $rs = $this->storeMod->querySql($sql);
        return $rs;
    }

    /**
     * 获取国外地址
     * @author wanyan
     * @date 2017-9-05
     */
    public function getGzone($id) {
        $sql = "select z.zone_id,z.name from " . DB_PREFIX . "country as c left join " . DB_PREFIX . "zone as z on c.country_id = z.country_id where c.country_id = {$id}";
        $rs = $this->storeMod->querySql($sql);
        return $rs;
    }

    /*
     * 添加地址
     * @author wangs
     * @date 2017-11-22
     */

    public function addAddress() {
        //语言包
        $name = !empty($_REQUEST['username']) ? htmlspecialchars(trim($_REQUEST['username'])) : '';
        $phone = !empty($_REQUEST['phone']) ? htmlspecialchars(trim($_REQUEST['phone'])) : '';
        $mp = !empty($_REQUEST['mp']) ? htmlspecialchars(trim($_REQUEST['mp'])) : '';
        $postal = !empty($_REQUEST['postal']) ? htmlspecialchars(trim($_REQUEST['postal'])) : '';
        /*    $address = !empty($_REQUEST['address']) ? htmlspecialchars(trim($_REQUEST['address'])) : $_REQUEST['name']; */
        $latlon = !empty($_REQUEST['latng']) ? trim($_REQUEST['latng']) : '';
        $address = $this->getAddr($latlon);
        if (empty($address)) {
            $address = !empty($_REQUEST['address']) ? htmlspecialchars(trim($_REQUEST['address'])) : '';
        }
        $id = $_REQUEST['id'] ? $_REQUEST['id'] : 0;
        $addr_id = $_REQUEST['addr_id'] ? $_REQUEST['addr_id'] : 0;
        $this->load($this->shorthand, 'WeChat/address');
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latng']) ? trim($_REQUEST['latng']) : '0';
        $this->assign('id', $id);
        $this->assign('addr_id', $addr_id);
        $this->assign('latlon', $latlon);
        $this->assign('name', $name);
        $this->assign('phone', $phone);
        $this->assign('mp', $mp);
        $this->assign('address', $address);
        $this->assign('postal', $postal);
        $latlon = explode(',', $latlon);
        $lng = $latlon[0]; //经度
        $lat = $latlon[1]; //纬度
        $this->assign('lng', $lng);
        $this->assign('lat', $lat);
        $this->assign('langdata', $this->langData);
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $this->assign('lang', $lang);
        $this->assign('storeid', $storeid);
        $language = $this->shorthand;
        $this->assign('language', $language);
        // $this->display("userCenter/addr.html");
        $this->display("userAddress/address.html");  // by xt 2019.03.26
    }

    //添加定位地址
    public function addAddress1() {
        //语言包

        $name = !empty($_REQUEST['username']) ? htmlspecialchars(trim($_REQUEST['username'])) : '';
        $phone = !empty($_REQUEST['phone']) ? htmlspecialchars(trim($_REQUEST['phone'])) : '';
        $mp = !empty($_REQUEST['mp']) ? htmlspecialchars(trim($_REQUEST['mp'])) : '';
        $postal = !empty($_REQUEST['postal']) ? htmlspecialchars(trim($_REQUEST['postal'])) : '';
        /*    $address = !empty($_REQUEST['address']) ? htmlspecialchars(trim($_REQUEST['address'])) : $_REQUEST['name']; */
        $key = !empty($_REQUEST['key']) ? $_REQUEST['key'] : 0;
        $returnUrl = !empty($_REQUEST['url']) ? $_REQUEST['url'] : '';
        if (empty($key) && !empty($returnUrl)) {
            $_SESSION['returnUrl'] = $returnUrl;
        }
        $latlon = !empty($_REQUEST['latng']) ? trim($_REQUEST['latng']) : '';
        $address = $this->getAddr($latlon);
        if (empty($address)) {
            $address = !empty($_REQUEST['address']) ? htmlspecialchars(trim($_REQUEST['address'])) : '';
        }
        $id = $_REQUEST['id'] ? $_REQUEST['id'] : 0;
        $addr_id = $_REQUEST['addr_id'] ? $_REQUEST['addr_id'] : 0;
        $this->load($this->shorthand, 'WeChat/address');
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latng']) ? trim($_REQUEST['latng']) : '0';
        $this->assign('id', $id);
        $this->assign('addr_id', $addr_id);
        $this->assign('latlon', $latlon);
        $this->assign('name', $name);
        $this->assign('phone', $phone);
        $this->assign('mp', $mp);
        $this->assign('address', $address);
        $this->assign('postal', $postal);
        $latlon = explode(',', $latlon);
        $lng = $latlon[0]; //经度
        $lat = $latlon[1]; //纬度
        $this->assign('lng', $lng);
        $this->assign('lat', $lat);
        $this->assign('langdata', $this->langData);
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $url = !empty($_REQUEST['url']) ? $_REQUEST['url'] : '';  //多语言商品
        $returnUrl = !empty($_REQUEST['url']) ? $_REQUEST['url'] : '';  //多语言商品
        $this->assign('lang', $lang);
        $this->assign('storeid', $storeid);
        $this->assign('returnUrl', urlencode($returnUrl));
        $this->assign('url', urlencode($url));
        $language = $this->shorthand;
        $this->assign('language', $language);
         $this->display("userCenter/confirmation.html");
        /*$this->display("userAddress/address.html");  // by xt 2019.03.26*/
    }

    public function getAddr($latlon) {
        $url = "https://apis.map.qq.com/ws/geocoder/v1/?location={$latlon}&key=SIYBZ-DYBY5-5R3IE-QVADH-4YP5J-BCFFU&get_poi=1";
        $address = file_get_contents($url);
        $address = json_decode($address);

        $address_detail = $address->result->address_component->street_number;
        return $address_detail;
    }

    /*
     * 设置默认当前地址
     * @author wangs
     * @date 2018-8-28
     */

    //设置默认当前地址
    public function addr_default() {
        $data_id = !empty($_REQUEST['data_id']) ? htmlspecialchars(trim($_REQUEST['data_id'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $storeid = !empty($_REQUEST['storeid']) ? htmlspecialchars(trim($_REQUEST['storeid'])) : $this->storeid;
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;  //多语言商品
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $sql = "select * from " . DB_PREFIX . "user_address where user_id=" . $this->userId . ' and default_addr =1  and distinguish=1';
        $userAddressMod = &m('userAddress');
        $addrinfo = $userAddressMod->querySql($sql);
        if ($addrinfo[0]['default_addr'] == 1) {
            $data = array(
                'default_addr' => 0
            );
            $userAddressMod->doEdits($addrinfo[0]['id'], $data);
        }
        $datas = array(
            'default_addr' => 1
        );
        $res = $userAddressMod->doEdits($data_id, $datas);
        if ($res) {
            $this->setData(array(), $status = 1, '设置成功');
        } else {
            $this->setData(array(), $status = 1, '设置失败');
        }
    }

    //添加收货地址
    public function doAddress1() {
        //语言包
        $this->load($this->shorthand, 'WeChat/address');
        $a = $this->langData;
        $returnUrl = !empty($_REQUEST['returnUrl']) ? $_REQUEST['returnUrl'] : '0';
        $url = !empty($_REQUEST['url']) ? $_REQUEST['url'] : '0';
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) : ''; //收货人
        $phone = !empty($_REQUEST['phone']) ? htmlspecialchars(trim($_REQUEST['phone'])) : ''; //手机
        $address = !empty($_REQUEST['address']) ? htmlspecialchars(trim($_REQUEST['address'])) : ''; //地址详情
        $mp = !empty($_REQUEST['mp']) ? htmlspecialchars(trim($_REQUEST['mp'])) : ''; //地址详情
        $storeid = !empty($_REQUEST['storeid']) ? (int) ($_REQUEST['storeid']) : '0';
        $id = $_REQUEST['id'] ? $_REQUEST['id'] : 0;
        $addr_id = $_REQUEST['addr_id'] ? $_REQUEST['addr_id'] : 0;
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? $_REQUEST['latlon'] : '0';
        $postal = !empty($_REQUEST['postal']) ? htmlspecialchars(trim($_REQUEST['postal'])) : '';
        $nowlatlon = !empty($_REQUEST['nowlatlon']) ? $_REQUEST['nowlatlon'] : '';
        $url = urldecode($url);
        $nowlatlon1 = explode(',', $nowlatlon);
        $nowlat = $nowlatlon1[1];
        $nowlng = $nowlatlon1[0];
        $latlon1 = explode(',', $latlon);
        $lat = $latlon1[1];
        $lng = $latlon1[0];
        $distance = $this->getdistance($lng, $lat, $nowlng, $nowlat);
        $distance = $distance / 1000;
        $userAddressMod = &m('userAddress');
        $sql = "select  distance from " . DB_PREFIX . "store where id =" . $storeid;
        $storeInfo = $userAddressMod->querySql($sql);
        if (empty($name)) {
            $this->setData(array(), $status = '0', $a['add_Consignee']); //请填写收货人！
        }
        if (empty($phone)) {
            $this->setData(array(), $status = '0', $a['add_phone']); //请填写手机号！
        }
        if (empty($address)) {
            $this->setData(array(), $status = '0', $a['add__Goodsreceipt']); //请填写收货地址
        }
        /*   if(empty($postal)){
          $this->setData(array(),$status='0','邮政编码不能为空');
          } */

        $addr_sql = "select * from " . DB_PREFIX . "user_address where user_id=" . $this->userId . ' and default_addr =1  and distinguish=1';
        $info = $userAddressMod->querySql($addr_sql);
        if ($info[0]['default_addr'] != '1') {
            $data = array(
                "name" => $name, //收货人姓名
                "phone" => $phone, //收货人电话
                "app" => $mp, //地址
                "address" => $address . '_' . $mp, //详细地址
                "user_id" => $this->userId,
                'postal_code' => $postal,
                'latlon' => $latlon,
                'default_addr' => 1
            );
        } else {
            $data = array(
                "name" => $name, //收货人姓名
                "phone" => $phone, //收货人电话
                "app" => $mp, //地址
                "address" => $address . '_' . $mp, //详细地址
                "user_id" => $this->userId,
                'postal_code' => $postal,
                'latlon' => $latlon
            );
        }
        //判断地址
        if ($id) {
            //修改地址
            $res = $userAddressMod->doEdit($id, $data);
        } else {
            //添加地址
            $res = $userAddressMod->doInsert($data);
        }

//添加最新的地址
        if ($res) {
            if ($url) {
                $info['url'] = $url;
            } else {
                $info['url'] = "?app=userCenter&act=myAddress&id={$addr_id}&storeid={$storeid}&lang={$lang}&auxiliary={$auxiliary}&latlon={$latlon}&returnUrl={$returnUrl}";
            }
            $this->setData($info, $status = '1', $a['add_Success']); //添加成功
        } else {
            if ($url) {
                $info['url'] = $url;
            } else {
                $info['url'] = "?app=userCenter&act=myAddress&id={$addr_id}&storeid={$storeid}&lang={$lang}&auxiliary={$auxiliary}&latlon={$latlon}&returnUrl={$returnUrl}";
            }
            $this->setData($info, $status = '0', $a['add_fail']); //添加失败
        }
    }

    //添加收货地址
    public function doAddress() {
        //语言包
        $this->load($this->shorthand, 'WeChat/address');
        $a = $this->langData;
        $returnUrl = !empty($_REQUEST['returnUrl']) ? $_REQUEST['returnUrl'] : '0';
        $url = !empty($_REQUEST['url']) ? $_REQUEST['url'] : '0';
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) : ''; //收货人
        $phone = !empty($_REQUEST['phone']) ? htmlspecialchars(trim($_REQUEST['phone'])) : ''; //手机
        $address = !empty($_REQUEST['address']) ? htmlspecialchars(trim($_REQUEST['address'])) : ''; //地址详情
        $mp = !empty($_REQUEST['mp']) ? htmlspecialchars(trim($_REQUEST['mp'])) : ''; //地址详情
        $storeid = !empty($_REQUEST['storeid']) ? (int) ($_REQUEST['storeid']) : '0';
        $id = $_REQUEST['id'] ? $_REQUEST['id'] : 0;
        $addr_id = $_REQUEST['addr_id'] ? $_REQUEST['addr_id'] : 0;
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? $_REQUEST['latlon'] : '0';
        $postal = !empty($_REQUEST['postal']) ? htmlspecialchars(trim($_REQUEST['postal'])) : '';
        $nowlatlon = !empty($_REQUEST['nowlatlon']) ? $_REQUEST['nowlatlon'] : '';
        $url = urldecode($url);
        $nowlatlon1 = explode(',', $nowlatlon);
        $nowlat = $nowlatlon1[1];
        $nowlng = $nowlatlon1[0];
        $latlon1 = explode(',', $latlon);
        $lat = $latlon1[1];
        $lng = $latlon1[0];
        $distance = $this->getdistance($lng, $lat, $nowlng, $nowlat);
        $distance = $distance / 1000;
        $userAddressMod = &m('userAddress');
        $sql = "select  distance from " . DB_PREFIX . "store where id =" . $storeid;
        $storeInfo = $userAddressMod->querySql($sql);
        if (empty($name)) {
            $this->setData(array(), $status = '0', $a['add_Consignee']); //请填写收货人！
        }
        if (empty($phone)) {
            $this->setData(array(), $status = '0', $a['add_phone']); //请填写手机号！
        } else {
            // by xt 2019.03.26
            if (!preg_match('/^1[34578]\d{9}$/', $phone)) {
                $this->setData(array(), $status = '0', '手机号格式不正确');
            }
        }
        if (empty($address)) {
            $this->setData(array(), $status = '0', $a['add__Goodsreceipt']); //请填写收货地址
        }
        /*   if(empty($postal)){
          $this->setData(array(),$status='0','邮政编码不能为空');
          } */

        $addr_sql = "select * from " . DB_PREFIX . "user_address where user_id=" . $this->userId . ' and default_addr =1  and distinguish=1';
        $info = $userAddressMod->querySql($addr_sql);
        if ($info[0]['default_addr'] != '1') {
            $data = array(
                "name" => $name, //收货人姓名
                "phone" => $phone, //收货人电话
                "app" => $mp, //地址
                "address" => $address . '_' . $mp, //详细地址
                "user_id" => $this->userId,
                'postal_code' => $postal,
                'latlon' => $latlon,
                'default_addr' => 1
            );
        } else {
            $data = array(
                "name" => $name, //收货人姓名
                "phone" => $phone, //收货人电话
                "app" => $mp, //地址
                "address" => $address . '_' . $mp, //详细地址
                "user_id" => $this->userId,
                'postal_code' => $postal,
                'latlon' => $latlon
            );
        }
        //判断地址
        if ($id) {
            //修改地址
            $res = $userAddressMod->doEdit($id, $data);
        } else {
            //添加地址
            $res = $userAddressMod->doInsert($data);
        }

//添加最新的地址
        if ($res) {
            if ($url) {
                $info['url'] = $url;
            } else {
                $info['url'] = "?app=userCenter&act=personAddress&id={$addr_id}&storeid={$storeid}&lang={$lang}&auxiliary={$auxiliary}&latlon={$latlon}&returnUrl={$returnUrl}";
            }
            $this->setData($info, $status = '1', $a['add_Success']); //添加成功
        } else {
            if ($url) {
                $info['url'] = $url;
            } else {
                $info['url'] = "?app=userCenter&act=personAddress&id={$addr_id}&storeid={$storeid}&lang={$lang}&auxiliary={$auxiliary}&latlon={$latlon}&returnUrl={$returnUrl}";
            }
            $this->setData($info, $status = '0', $a['add_fail']); //添加失败
        }
    }

    //转化距离
    function getdistance($lng1, $lat1, $lng2, $lat2) {
        // 将角度转为狐度
        $radLat1 = deg2rad($lat1); //deg2rad()函数将角度转换为弧度
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137 * 1000;
        return $s;
    }

    //获取地址
    public function getAddress() {
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) : '';
        $phone = !empty($_REQUEST['phone']) ? htmlspecialchars(trim($_REQUEST['phone'])) : 0;
        $mp = !empty($_REQUEST['mp']) ? htmlspecialchars(trim($_REQUEST['mp'])) : '';
        $type = !empty($_REQUEST['type']) ? htmlspecialchars(trim($_REQUEST['type'])) : '';
        $storeid = !empty($_REQUEST['storeid']) ? htmlspecialchars(trim($_REQUEST['storeid'])) : '';
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $returnUrl = !empty($_REQUEST['returnUrl']) ? $_REQUEST['returnUrl'] : '';
        $url = !empty($_REQUEST['url']) ? $_REQUEST['url'] : '';
        $postal = !empty($_REQUEST['postal']) ? $_REQUEST['postal'] : '';
        $id = $_REQUEST['id'] ? $_REQUEST['id'] : 0;
        $addr_id = $_REQUEST['addr_id'] ? $_REQUEST['addr_id'] : 0;
        $this->assign('postal', $postal);
        $this->assign('returnUrl', urlencode($returnUrl));
        $this->assign('url', urlencode($url));
        $this->assign('name', $name);
        $this->assign('phone', $phone);
        $this->assign('mp', $mp);
        $this->assign('type', $type);
        $this->assign('storeid', $storeid);
        $this->assign('lang', $lang);
        $this->assign('auxiliary', $auxiliary);
        $this->assign('latlon', $latlon);
        $this->assign('id', $id);
        $this->assign('addr_id', $addr_id);
        $latlon = explode(',', $latlon);
        $lng = $latlon[0]; //经度
        $lat = $latlon[1]; //纬度
        $this->assign('lng', $lng);
        $this->assign('lat', $lat);
        $this->display('userCenter/addadress.html');
    }

    /*
     * 添加地址
     * @author wangs
     * @date 2017-11-22
     */




    /*
     * 编辑地址
     * @author wangs
     * @date 2017-11-22
     */

    public function editaddress() {
        //语言包
        $this->load($this->shorthand, 'WeChat/address');
        $this->assign('langdata', $this->langData);
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $this->assign('latlon', $latlon);
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $returnUrlFirst = !empty($_REQUEST['returnUrl']) ? $_REQUEST['returnUrl'] : '0';  //所选的站点id
        if (!empty($this->userId)) {
            if ($returnUrlFirst) {
                $this->assign('returnUrl', $returnUrlFirst);
            } else {
                $returnUrl = $_SERVER['HTTP_REFERER'];
                if ($returnUrl) {
                    $this->assign('returnUrl', $returnUrl);
                }
            }
        }
        $address_id = $_REQUEST['id'] ? $_REQUEST['id'] : '';
        $userAddressMod = &m('userAddress');
        $zoneMod = &m('zone');
        $countryMod = &m('country');
        $info = $userAddressMod->getOne(array("cond" => "id=" . $address_id . " and user_id=" . $this->userId));

        $store_address = explode('_', $info['store_address']);
        if (count($store_address) == 3) {
            $ch_store_address = $store_address;
            $ch_city = $this->getCity($ch_store_address[0]);
            $ch_area = $this->getCity($ch_store_address[1]);
            $this->assign('switch', 1);
            $this->assign('ch_city', $ch_city);
            $this->assign('ch_area', $ch_area);
            $this->assign('ch_store_address', $ch_store_address);
        } else {
            $this->assign('switch', 2);
            $en_store_address = $store_address;
            $this->assign('en_store_address', $en_store_address);
            $en_zhou = $this->getGzone($en_store_address[0]);
            $this->assign('en_zhou', $en_zhou);
        }
        $this->assign('pros', $this->cityMod->getParentNodes());
        $this->assign('countrys', $this->countryMod->getCountryNodes());
        $this->assign("info", $info);
        $this->assign('lang', $lang);
        $this->assign('storeid', $storeid);
        $language = $this->shorthand;
        $this->assign('language', $language);
        $this->display("userCenter/addshipping-editaddress.html");
    }

    /**
     * 获取城市和区域列表
     * @author wanyan
     * @date 2017-08-31
     */
    public function getZoneData() {
        $id = !empty($_REQUEST['pro_id']) ? intval($_REQUEST['pro_id']) : '0';
        $sql = "select `zone_id`,`name` from " . DB_PREFIX . "zone where `status` =1 and `country_id`='{$id}'";
        $rs = $this->storeCateMod->querySql($sql);
        echo json_encode($rs);
        die;
    }

    /**
     * 获取城市和区域列表
     * @author wanyan
     * @date 2017-08-31
     */
    public function getAjaxData() {
        $id = !empty($_REQUEST['pro_id']) ? intval($_REQUEST['pro_id']) : '0';
        $rs = $this->cityMod->getData(array('cond' => "`parent_id`='{$id}'", 'fields' => "`id`,`name`"));
        foreach ($rs as $k => $v) {
            if ($v['id'] == 1) {
                unset($rs[0]);
            }
        }
        echo json_encode($rs);
        die;
    }

    /*
     * 删除地址
     * @author lee
     * @date 2017-9-25 13:48:06
     */

    public function addDelete() {
        //语言包
        $this->load($this->shorthand, 'weChat/order');
        $a = $this->langData;
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        $addressMod = &m("userAddress");
        //获取收货地址
        $addrSql = "select * from " . DB_PREFIX . 'user_address where user_id=' . $this->userId . ' and id=' . $id . '  and distinguish=1';
        $userAddress = $addressMod->querySql($addrSql); // 获取用户的地址
        if ($userAddress[0]['default_addr'] == 1) {
            $res = $addressMod->doDrop($id);
            if ($res) {
                //获取收货地址
                $editSql = "select * from " . DB_PREFIX . 'user_address where user_id=' . $this->userId . '   and distinguish=1';
                $userEdit = $addressMod->querySql($editSql); // 获取用户的地址 
                $user_addr = $userEdit[0]['id'];
                $data = array(
                    "default_addr" => 1, //收货人姓名
                );
                $res = $addressMod->doEdit($user_addr, $data);
                $this->setData(array("url" => "?app=userCenter&act=myAddress&storeid={$storeid}&lang={$lang}&lang={$auxiliary}"), $status = '1', $a['delete_Success']);
            } else {
                $this->setData(array(), $status = '0', $a['add_fail']);
            }
        } else {
            $res = $addressMod->doDrop($id);
            if ($res) {
                $this->setData(array("url" => "?app=userCenter&act=myAddress&storeid={$storeid}&lang={$lang}&lang={$auxiliary}"), $status = '1', $a['delete_Success']);
            } else {
                $this->setData(array(), $status = '0', $a['add_fail']);
            }
        }
    }

    /*
     * 收藏的商品
     * @author wangs
     * @date 2017-11-22
     */

    public function collectionGoods() {
        //语言包
        $this->load($this->shorthand, 'WeChat/userCenter');
        $this->assign('langdata', $this->langData);
        $userId = $this->userId;
        $this->assign('symbol', $this->symbol);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $this->assign('latlon', $latlon);
        $where = ' f.user_id =' . $userId;
        //列表页数据
        $sql = 'select distinct f.*,g.*,l.*,gl.original_img,f.id  from '
                . DB_PREFIX . 'store_goods as g inner join '
                . DB_PREFIX . 'user_collection as f on f.store_good_id = g.id inner join '
                . DB_PREFIX . 'goods_lang as l on l.goods_id = g.goods_id and l.lang_id=' . $lang . ' left join '
                . DB_PREFIX . 'goods as gl on g.goods_id = gl.goods_id  where ' . $where . ' and f.store_id =' . $storeid
                . ' group by f.store_good_id order by f.id desc limit 0, 8 ';
//     print_r($sql);exit;
        $data = $this->colleCtionMod->querySql($sql);
        $this->assign('storeid', $storeid);
        $this->assign('data', $data);
        $this->assign('lang', $lang);
        $language = $this->shorthand;
        $this->assign('language', $language);
        $this->display("userCenter/favorites.html");
    }

    /*
     * 移除收藏的商品
     * @author wangshuo
     * @date 2017-9-25 
     */

    public function doDelete() {
        //语言包
        $this->load($this->shorthand, 'weChat/order');
        $a = $this->langData;
        $storeid = $this->storeid;  //所选的站点id
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言
        $res = $this->colleCtionMod->doDrop($id);
        if ($res) {
            $this->setData(array("url" => "?app=userCenter&act=collectionGoods&storeid={$storeid}&lang={$lang}"), $status = '1', $a['delete_Success']);
        } else {
            $this->setData(array(), $status = '0', $a['delete_fail']);
        }
    }

    /*
     * 我的足迹
     * @author wangs
     * @date 2017-11-22
     */

    public function footPrint() {
        //语言包
        $this->load($this->shorthand, 'WeChat/userCenter');
        $this->assign('langdata', $this->langData);
        $userId = $this->userId;
        $this->assign('symbol', $this->symbol);
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $where = ' f.user_id =' . $userId . ' and f.store_good_id =g.id';
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $this->assign('latlon', $latlon);
        //列表页数据
        $sql = 'select distinct f.*,g.*,l.*,f.id,l.goods_name,gl.original_img,f.store_good_id  from '
                . DB_PREFIX . 'user_footprint as f inner join '
                . DB_PREFIX . 'store_goods as g on f.good_id = g.goods_id inner join '
                . DB_PREFIX . 'goods_lang as l on l.goods_id = g.goods_id and l.lang_id=' . $lang . '  left join '
                . DB_PREFIX . 'goods as gl on g.goods_id = gl.goods_id  where '
                . $where . ' and g.mark = 1  and f.store_id =' . $storeid .
                ' group by f.good_id order by f.adds_time desc limit 0, 8 ';
        $data = $this->footPrintMod->querySql($sql);
        $this->assign('storeid', $storeid);
        $this->assign('lang', $lang);
        $this->assign('data', $data);
        //映射页面
        $language = $this->shorthand;
        $this->assign('language', $language);
        $this->display("userCenter/footprint.html");
    }

    /*
     * 我的推荐     
     * @author wangs
     * @date 2018-8-21
     */

    public function myRecommendation() {
        //语言包
        $this->load($this->shorthand, 'WeChat/userCenter');
        $this->assign('langdata', $this->langData);
        $userId = $this->userId;
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        //获取当前用户的邀请手机号
        $phone_sql = 'select phone,phone_email from ' . DB_PREFIX . 'user  where id =' . $userId . ' and mark =1';
        $phone_data = $this->userMod->querySql($phone_sql);
        //推荐我的人
        $Recommendme_sql = 'select phone,add_time from ' . DB_PREFIX . 'user where phone = ' . $phone_data[0]['phone_email'] . ' and mark =1';
        $Recommendme_data = $this->userMod->querySql($Recommendme_sql);
        $this->assign('Recommendme_data', $Recommendme_data[0]);
        //我推荐的数量
        $num_sql = "select count(*) as count from  " . DB_PREFIX . "user where phone_email = " . $phone_data[0]['phone'] . " and mark =1 order by id";
        $num_res = $this->userMod->querySql($num_sql);
        $this->assign('num_res', $num_res[0]);
        //我推荐的人
        $Irecommend_sql = "select phone,add_time  from  " . DB_PREFIX . "user where phone_email = " . $phone_data[0]['phone'] . " and mark =1 order by id";
        $res = $this->userMod->querySqlPageData($Irecommend_sql);
        foreach ($res['list'] as $k => $v) {
            $res['list'][$k]['add_time'] = date('Y-m-d H:i', $v['add_time']);
            if ($v['add_time']) {
                $res['list'][$k]['add_time'] = date('Y-m-d H:i', $v['add_time']);
            } else {
                $res['list'][$k]['add_time'] = '';
            }
            $res['list'][$k]['sort_id'] = $k + 2000 * ($p - 1) + 1; //正序
        }
        $this->assign('list', $res['list']);
        //映射页面
        $this->display("userCenter/recommend.html");
    }

    /*
     * 删除足迹
     * @author wangs
     * @date 2017-11-22
     */

    public function DeletefootPrint() {
        //语言包
        $this->load($this->shorthand, 'weChat/order');
        $a = $this->langData;
        $storeid = $this->storeid;  //所选的站点id
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $footPrintMod = &m("footprint");
        $res = $footPrintMod->doDrop($id);
        if ($res) {
            $this->setData(array("url" => "?app=userCenter&act=footPrint&storeid={$storeid}&lang={$lang}&auxiliary={$auxiliary}"), $status = '1', $a['delete_Success']);
        } else {
            $this->setData(array(), $status = '0', $a['delete_fail']);
        }
    }

    /*
     * 足迹管理
     * @author wangs
     * @date 2017-11-22
     */

    public function editFootPrint() {
        //语言包
        $this->load($this->shorthand, 'WeChat/userCenter');
        $this->assign('langdata', $this->langData);
        $userId = $this->userId;
        $this->assign('symbol', $this->symbol);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;  //所选的站点id
        $where = ' f.user_id =' . $userId . ' and f.store_good_id =g.id';
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $this->assign('latlon', $latlon);
        //列表页数据
        $sql = 'select distinct f.*,g.*,l.*,f.id,l.goods_name,gl.original_img,f.store_good_id  from '
                . DB_PREFIX . 'user_footprint as f inner join '
                . DB_PREFIX . 'store_goods as g on f.good_id = g.goods_id inner join '
                . DB_PREFIX . 'goods_lang as l on l.goods_id = g.goods_id and l.lang_id=' . $lang . ' left join '
                . DB_PREFIX . 'goods as gl on g.goods_id = gl.goods_id  where '
                . $where . ' and g.mark = 1  and f.store_id =' . $storeid .
                ' group by f.good_id order by f.adds_time desc';
        $data = $this->footPrintMod->querySql($sql);
        $this->assign('storeid', $storeid);
        $this->assign('data', $data);
        $this->assign('lang', $lang);
        $language = $this->shorthand;
        $this->assign('language', $language);
        //映射页面
        $this->display("userCenter/edit-footprint.html");
    }

    /*
     * 安全中心
     * @author wangs
     * @date 2017-11-22
     */

    public function accountSafe() {
        //语言包
        $this->load($this->shorthand, 'WeChat/userCenter');
        $this->assign('langdata', $this->langData);
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;  //所选的站点id
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $this->assign('latlon', $latlon);
        $userId = $this->userId;
        $sql = "select * from " . DB_PREFIX . "user where id=" . $userId;
        $res = $this->userMod->querySql($sql);
        $this->assign('res', $res[0]);
        $this->assign('lang', $lang);
        $this->assign('storeid', $storeid);
        $this->display("userCenter/accountSafe.html");
    }

    /*
     * 修改密码
     * @author wangs
     * @date 2017-11-22
     */

    public function editPassword() {
        //语言包
        $this->load($this->shorthand, 'WeChat/userCenter');
        $this->assign('langdata', $this->langData);
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;  //所选的站点id
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $this->assign('latlon', $latlon);
        $this->assign('lang', $lang);
        $this->assign('storeid', $storeid);
        $this->display("userCenter/editPassword.html");
    }

    /*
     * 保存修改密码
     * @author lee
     * @date 2017-8-17 14:53:02
     */

    public function saveInfo() {
        //语言包
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;

        $this->load($this->shorthand, 'WeChat/userCenter');
        $a = $this->langData;
        $lang = !empty($_REQUEST['lang']) ? (int) ($_REQUEST['lang']) : '0';
        $storeid = !empty($_REQUEST['storeid']) ? (int) ($_REQUEST['storeid']) : '0';
        $old_password = $_REQUEST['old_password'] ? $_REQUEST['old_password'] : "";
        $new_password = $_REQUEST['new_password'] ? $_REQUEST['new_password'] : "";
        $new_again = $_REQUEST['new_again'] ? $_REQUEST['new_again'] : "";
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $userId = $this->userId;
        $userMod = &m('user');
        $user_info = $userMod->getOne(array("cond" => "id=" . $userId));
        if (empty($old_password)) {
            $res = $this->setData(array(), $status = '0', $a['security_primarycannot']);
        }
        if (empty($new_password)) {
            $res = $this->setData(array(), $status = '0', $a['security_newcannot']);
        }
        if (empty($new_again)) {
            $res = $this->setData(array(), $status = '0', $a['security_Pleaseconfirm']);
        }
        //更新用户密码
        if ((md5($old_password) != $user_info['password']) && $old_password != '') {
            $this->setData(array(), $status = '0', $a['security_Verification']);
        }
        if ($new_password != $new_again) {
            $this->setData(array(), $status = '0', $a['security_Atypism']);
        }
        $data = array(
            'password' => md5($new_password)
        );
        $res = $userMod->doEdit($user_info['id'], $data);
        if ($res) {
            $info['url'] = "?app=userCenter&act=accountSafe&storeid={$storeid}&lang={$lang}&auxiliary={$auxiliary}&latlon={$latlon}";
            $this->setData($info, $status = '1', $a['Amendthesuccess']);
        } else {
            $this->setData(array(), $status = '0', $a['Failuretomodify']);
        }
    }

    /*
     * 修改邮箱
     * @author wangs
     * @date 2017-11-22
     */

    public function editEmail() {
        //语言包
        $this->load($this->shorthand, 'WeChat/userCenter');
        $this->assign('langdata', $this->langData);
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;  //所选的站点id
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $this->assign('latlon', $latlon);
        $userId = $this->userId;
        $sql = "select * from " . DB_PREFIX . "user where id=" . $userId;
        $res = $this->userMod->querySql($sql);
        $this->assign('res', $res[0]);
        $this->assign('lang', $lang);
        $this->assign('storeid', $storeid);
        $this->display("userCenter/editEmail.html");
    }

    /*
     * 保存修改邮箱
     * @author lee
     * @date 2017-8-17 14:53:02
     */

    public function emailInfo() {
        //语言包
        $this->load($this->shorthand, 'WeChat/userCenter');
        $a = $this->langData;
        $lang = !empty($_REQUEST['lang']) ? (int) ($_REQUEST['lang']) : $this->langid;
        $storeid = !empty($_REQUEST['storeid']) ? (int) ($_REQUEST['storeid']) : $this->storeid;
        $primary_email = $_REQUEST['primary_email'] ? $_REQUEST['primary_email'] : "";
        $new_email = $_REQUEST['new_email'] ? trim($_REQUEST['new_email']) : "";
        $password = $_REQUEST['password'] ? $_REQUEST['password'] : "";
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;

        $userId = $this->userId;
        $userMod = &m('user');
        $user_info = $userMod->getOne(array("cond" => "id=" . $userId));
        if (!$new_email) {
            $this->setData(array(), $status = '0', $a['mailbox_Newmailbox']);
        }
        if ($this->userMod->isExist($type = 'email', $new_email, 'mark', 1)) {
            $this->setData(array(), $status = '0', $a['mailbox_Alreadyexisted']);
        }
        if (!preg_match("/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/", $new_email)) {//
            $this->setData(array(), $status = '0', $a['mailbox_Incorrect']);
        };
        if (empty($password)) {
            $res = $this->setData(array(), $status = '0', $a['mailbox_Cipher']);
        }
        //用户密码验证
        if ((md5($password) != $user_info['password']) && $password != '') {
            $this->setData(array(), $status = '0', $a['mailbox_Wrongpassword']);
        }
        $data = array(
            'email' => $new_email,
        );
        $res = $userMod->doEdit($user_info['id'], $data);
        if ($res) {
            $info['url'] = "?app=userCenter&act=accountSafe&storeid={$storeid}&lang={$lang}&auxiliary={$auxiliary}&latlon={$latlon}";
            $this->setData($info, $status = '1', $a['Amendthesuccess']);
        } else {
            $this->setData(array(), $status = '0', $a['Failuretomodify']);
        }
    }

    /*
     * 修改手机
     * @author wangs
     * @date 2017-11-22
     */

    public function editPhone() {
        //语言包
        $this->load($this->shorthand, 'WeChat/userCenter');
        $this->assign('langdata', $this->langData);
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;  //所选的站点id
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $this->assign('latlon', $latlon);
        $userId = $this->userId;
        $sql = "select * from " . DB_PREFIX . "user where id=" . $userId;
        $res = $this->userMod->querySql($sql);
        $this->assign('res', $res[0]);
        $this->assign('lang', $lang);
        $this->assign('storeid', $storeid);
        $language = $this->shorthand;
        $this->assign('language', $language);
        $this->display("userCenter/editPhone.html");
    }

    /*
     * 保存修改手机
     * @author lee
     * @date 2017-8-17 14:53:02
     */

    public function phoneInfo() {
        //语言包
        $this->load($this->shorthand, 'WeChat/userCenter');
        $a = $this->langData;
        $langid = !empty($_REQUEST['lang']) ? (int) ($_REQUEST['lang']) : '0';
        $storeid = !empty($_REQUEST['storeid']) ? (int) ($_REQUEST['storeid']) : '0';
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;

        $phone = trim($_REQUEST['phone']);
        $code = $_REQUEST['code'];
        $userId = $this->userId;
        $userMod = &m('user');
        $user_info = $userMod->getOne(array("cond" => "id=" . $userId));
        if (empty($phone)) {
            $this->setData(array(), $status = '0', $a['phone_phonerequired']);
        }

        if (!preg_match('/^(13[0-9]|14[0-9]|15[0-9]|17[0-9]|18[0-9])\d{8}$/', $phone)) {
            $this->setData(array(), $status = '0', $a['phone_Formattingerror']);
        }
        if ($this->getPhoneInfo($phone)) {
            $this->setData(array(), $status = '0', $a['phone_Beregistered']);
        }
        $smsCode = $this->getSmsCode($phone);
        if (empty($code)) {
            $this->setData(array(), $status = '0', $a['phone_Coderequired']);
        }
        if ($code != $smsCode) {
            $this->setData(array(), $status = '0', $a['phone_CodeIncorrect']);
        }
        $data = array(
            'phone' => $phone,
        );
        $res = $userMod->doEdit($user_info['id'], $data);
        if ($data) {
            $info['url'] = "?app=userCenter&act=accountSafe&storeid={$storeid}&lang={$langid}&auxiliary={$auxiliary}&latlon={$latlon}";
            $this->setData($info, $status = '1', $a['Amendthesuccess']); //修改成功
        } else {
            $this->setData(array(), $status = '0', $a['Failuretomodify']); //修改失败
        }
    }

    //手机验证
    public function getPhoneInfo($edit_phone) {
        $sql = 'select id from  bs_user where mark =1 and  phone = ' . $edit_phone . '  limit 1';
        $data = $this->userMod->querySql($sql);
        return $data[0]['id'];
    }

    //验证码验证
    public function getSmsCode($phone) {
        $smsMod = &m('sms');
        $sql = 'select  phone,code  from bs_sms where  phone =' . $phone . '  order by id desc  limit 1';
        $data = $smsMod->querySql($sql);
        return $data[0]['code'];
    }

    /*
     * 收藏的商品ajax判断
     * @author wangshuo
     * @date 2017-9-25 
     */

    public function docollectionGoods() {
        $type = $_GET['type'];
        $good_id = $_GET['id'];
        $userId = $this->userId;
        $storeid = $_GET['store_id'];
        $store_good_id = $_GET['store_good_id'];
        if ($type == 'false') {
            $data = array(
                'table' => 'user_collection',
                'user_id' => $userId,
                'good_id' => $store_good_id,
                'store_id' => $storeid,
                'store_good_id' => $good_id,
                'adds_time' => time(),
                    // 'statu' => 1,
            );
            $res = $this->colleCtionMod->doInsert($data);
            $data['statu'] = 1;
        } else {
            $res = $this->colleCtionMod->doDrops('store_good_id =' . $good_id);
            $data['statu'] = 0;
        }
        $data['id'] = $good_id;
        echo json_encode($data);
        exit;
    }
   
    //文章收藏
    public function articleCollect() {
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $store_id = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;
        $this->assign('storeid', $store_id);
        $lang_id = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;
        $this->assign('lang', $lang_id);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $this->assign('latlon', $latlon);
        $userId = $this->userId;
        $sql = 'SELECT article_id FROM  ' . DB_PREFIX . 'user_article  WHERE store_id =  ' . $store_id . ' AND user_id=' . $userId;
        $articleData = $this->colleCtionMod->querySql($sql);
        foreach ($articleData as $k => $v) {
            $id[] = $v['article_id'];
        }
        $ids = implode(',', $id);
        if (!empty($ids)) {
            $articleSql = 'SELECT a.id,al.title,al.brif,a.image,a.add_time FROM ' . DB_PREFIX . 'article AS a LEFT JOIN ' . DB_PREFIX . 'article_lang AS al ON a.id=al.article_id WHERE al.lang_id= ' . $lang_id . ' AND a.id in (' . $ids . ')';
            $listData = $this->colleCtionMod->querySql($articleSql);
        } else {
            $listData = array();
        }
        $this->assign('id', $ids);
        $this->assign('lang', $lang_id);
        $this->assign('listData', $listData);
        $language = $this->shorthand;
        $this->assign('language', $language);
        $this->display('userCenter/article.html');
    }

    public function doArticleDelete() {
        return $this->userArticleMod->doDrops('article_id =' . $_REQUEST['id']);
    }

    //店铺收藏



    public function storeCollect() {
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $store_id = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;
        $this->assign('storeid', $store_id);
        $lang_id = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;
        $this->assign('lang', $lang_id);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        $userId = $this->userId;

        $bSql = 'SELECT store_id FROM  ' . DB_PREFIX . 'user_store  WHERE  user_id=' . $userId;
        $bData = $this->userStoreMod->querySql($bSql);
        foreach ($bData as $k => $v) {
            $sId[] = $v['store_id'];
        }
        $sIds = implode(',', $sId);
        if (!empty($sIds)) {
            $swhere = " where sl.distinguish=0 AND s.id in (" . $sIds . ')';
            $ssql = 'SELECT  s.id,s.longitude,s.latitude,l.`name` AS lname ,c.`name` AS cname,sl.`store_name` AS sltore_name FROM  ' . DB_PREFIX . 'store AS s LEFT JOIN  ' . DB_PREFIX . 'store_lang AS sl ON sl.`store_id` = s.`id` and  sl.lang_id=' . $this->langid . ' LEFT JOIN  ' . DB_PREFIX . 'language AS l ON s.`lang_id` = l.`id` LEFT JOIN  ' . DB_PREFIX . 'currency AS c ON s.`currency_id` = c.`id`' . $swhere;
            $listData = $this->userStoreMod->querySql($ssql);
        } else {
            $listData = array();
        }
        foreach ($listData as $key => $val) {
            $busSql = "SELECT buss_id FROM " . DB_PREFIX . 'store_business WHERE  store_id=' . $val['id'];
            $busData = $this->userStoreMod->querySql($busSql);
            $listData[$key]['b_id'] = $busData[0]['buss_id'];
        }

        $this->assign('id', $sIds);
        $this->assign('lang', $lang_id);
        $this->assign('listData', $listData);
        $language = $this->shorthand;
        $this->assign('language', $language);
        $this->display('userCenter/store.html');
    }

    public function dostoreDelete() {
        return $this->userStoreMod->doDrops('store_id =' . $_REQUEST['id']);
    }

    /*    public function point() {
      $this->load($this->shorthand, 'userCenter/userCenter');
      $this->assign('langdata', $this->langData);
      $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
      $this->assign('auxiliary', $auxiliary);
      $pointLogMod = &m('pointLog');
      $userId = $this->userId;
      $sql = 'SELECT note FROM ' . DB_PREFIX . 'point_log WHERE userid=' . $userId;
      $logData = $pointLogMod->querySql($sql);
      $this->assign('logData', $logData);
      $pSql = 'SELECT point_rate FROM ' . DB_PREFIX . 'user_point_site';
      $pData = $this->pointMod->querySql($pSql);
      $point_rate = $pData[0]['point_rate'];
      $rate = (1 * $point_rate);
      $this->assign('rate', $rate);
      $langid = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;
      $this->assign('langid', $langid);
      $this->assign('userid', $userId);
      $this->display();
      } */

    //个人睿积分
    public function pointLog() {

        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;
        $this->assign('lang', $lang);
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;
        $this->assign('storeid', $storeid);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);

        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        $startTime = !empty($_REQUEST['start_time']) ? htmlspecialchars(trim($_REQUEST['start_time'])) : '';
        $endTime = !empty($_REQUEST['end_time']) ? htmlspecialchars(trim($_REQUEST['end_time'])) : '';


        if (empty($startTime) && empty($endTime)) {
            $startTime = date('Y-m-d', strtotime('-7 days'));
            $endTime = date('Y-m-d');
        }
        if ($startTime && $endTime && ($startTime > $endTime)) {
            $temp = $startTime;
            $startTime = $endTime;
            $endTime = $temp;
        }
        $this->assign('startTime', $startTime);
        $this->assign('endTime', $endTime);

        //where条件
        $where = ' where 1=1 ';
        if ($this->lang == 1) {
            if (!empty($startTime)) {
                $where .= ' and  add_time >= ' . strtotime($startTime);
            }
            if (!empty($endTime)) {
                $where .= ' and  add_time < ' . (strtotime($endTime) + 3600 * 24);
            }
        } else {
            if (!empty($startTime)) {
                $where .= ' and  add_time >= ' . strtotime($startTime);
            }
            if (!empty($endTime)) {
                $where .= ' and  add_time < ' . (strtotime($endTime) + 3600 * 24);
            }
        }
        $uSql = "select point from " . DB_PREFIX . 'user where id=' . $this->userId;
        $uData = $this->userMod->querySql($uSql);
        $this->assign('point', $uData[0]['point']);

        //列表页数据
        $sql = ' select  *   from  ' . DB_PREFIX . 'point_log   ' . $where . ' AND userid=' . $this->userId . '  order by id desc ';
        $data = $this->pointLogMod->querySqlPageData($sql);
        $list = $data['list'];
        $this->assign('list', $list);
        $this->assign('lang', $lang);
        $this->assign('page_html', $data['ph']);

        //赠送睿积分功能是否关闭
        $systemConsoleMod = &m('systemConsole');
        $console_info = $systemConsoleMod->getRow(1);
        $this->assign('console_status', $console_info['status']);

        //映射页面
        $this->display('personPoint/points.html');
    }

    /*
     * 赠送睿积分页面
     * @author lee
     * @date 2018-6-22 09:39:05
     */

    public function giveUserPoint() {
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;
        $this->assign('lang', $lang);
        $user_info = $this->userMod->getOne(array("cond" => "id=" . $this->userId));
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $this->assign('info', $user_info);
        $this->display('personPoint/givePoints.html');
    }

    /*
     * 处理赠送积分
     * @author lee
     * @date  2018-6-22 09:57:26
     */

    public function doGivePoint() {
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $a = $this->langData;
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->storeid;
        $name = !empty($_REQUEST['user_name']) ? trim($_REQUEST['user_name']) : '';
        $point = !empty($_REQUEST['point']) ? trim($_REQUEST['point']) : '';
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $sql = "select * from " . DB_PREFIX . "user where phone = '" . $name . "' or email = '" . $name . "'";
        $res = $this->userMod->querySql($sql);
        //$receive_info = $this->userMod->getOne(array("cond"=>"phone =".$name." or email=".$name));
        $receive_info = $res[0];
        $give_info = $this->userMod->getOne(array("cond" => "id =" . $this->userId));
        if (empty($name)) {
            $this->setData(array(), $status = 0, $a['no_giver']);
        }
        if ($receive_info['id'] == $this->userId) {
            $this->setData(array(), $status = 0, $a['no_power']);
        }
        if (empty($receive_info)) {
            $this->setData(array(), $status = 0, $a['no_give']);
        }
        if (!preg_match("/^[1-9][0-9]*$/", $point)) {
            $this->setData(array(), $status = 0, $a['rui_z']);
        }
        if ($point > $give_info['point']) {
            $this->setData(array(), $status = 0, $a['no_point']);
        }
        $give_point = $give_info['point'] - $point;
        $give_arr = array(
            "point" => $give_point
        );
        $receive_arr = array(
            "point" => $receive_info['point'] + $point
        );
        $res = $this->userMod->doEdit($give_info['id'], $give_arr);
        $this->addPointLog($give_info['phone'], "赠予" . $receive_info['username'] . " " . $point . "睿积分", $give_info['id'], 0, $point);
        if ($res) {
            $res2 = $this->userMod->doEdit($receive_info['id'], $receive_arr);
            $this->addPointLog($receive_info['phone'], $give_info['username'] . "赠予" . $point . "睿积分", $receive_info['id'], $point, 0);
        }
        if ($res && $res2) {
            $this->setData(array("url" => "?app=userCenter&act=pointLog&storeid={$store_id}&lang={$lang}&auxiliary={$auxiliary}"), $status = 1, $a['userinfo_Success']);
        } else {
            $this->setData(array(), $status = 0, $a['saveAdd_fail']);
        }
    }

    //生成日志
    public function addPointLog($username, $note, $userid, $deposit, $expend, $order_sn = null) {
        $logData = array(
            'operator' => '--',
            'username' => $username,
            'add_time' => time(),
            'deposit' => $deposit,
            'expend' => $expend,
            'note' => $note,
            'userid' => $userid
        );
        if ($order_sn) {
            $logData['order_sn'] = $order_sn;
        }
        $pointLogMod = &m("pointLog");
        $pointLogMod->doInsert($logData);
    }

    public function coupon() {
        $this->load($this->shorthand, 'WeChat/userCenter');
        $this->assign('langdata', $this->langData);
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        //抵扣劵
        $userCouponMod=&m('userCoupon');
        $couponData=$userCouponMod->getValidCoupons($this->userId,$lang,1,0,0);
        $voucherData=$userCouponMod->getValidCoupons($this->userId, $lang, 2, 0,0);
        $this->assign('couponData',$couponData);
        $this->assign('voucherData',$voucherData);
        $this->display('userCenter/coupon.html');
    }

    /*
     * 个人资料
     * @author wangs
     * @date 2017-11-22
     */

    public function Untie() {
        //语言包
        $this->load($this->shorthand, 'WeChat/userCenter');
        $this->assign('langdata', $this->langData);
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $this->assign('latlon', $latlon);
        $userId = $this->userId;
        $sql = "select * from " . DB_PREFIX . "user where id=" . $userId;
        $res = $this->userMod->querySql($sql);
        $this->assign('access_token', $this->accessToken);
        $this->assign('res', $res[0]);
        $this->assign('lang', $lang);
        $this->assign('storeid', $storeid);
        $this->display("userCenter/Untie.html");
    }

    public function unbind() {
        $userId = !empty($_REQUEST['userId']) ? intval($_REQUEST['userId']) : 0;
        /*    $wx_openid = $_COOKIE['wx_openid'];
          $wx_nickname = $_COOKIE['wx_nickname'];
          $wx_city = $_COOKIE['wx_city'];
          $wx_province = $_COOKIE['wx_province'];
          $wx_country = $_COOKIE['wx_country'];
          $wx_headimgurl = $_COOKIE['wx_headimgurl'];
          $wx_sex = $_COOKIE['wx_sex']; */
        $res = $this->userMod->doEditSpec(array('id' => $userId), array( 'openid' => ''));

        if ($res) {
            unset($_SESSION['userId']);
            unset($_SESSION['userName']);
            $url = 'wx.php?app=user&act=quickLogin';
            $this->setData($url, '1', '解绑成功');
        }
    }

    /*
     * 代客下单二维码展示
     * @author wangs
     * @date 2018-9-27
     */

    public function Codecard() {
        //语言包
        $this->load($this->shorthand, 'WeChat/userCenter');
        $this->assign('langdata', $this->langData);
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $this->assign('latlon', $latlon);
        $userId = $this->userId;
        $sql = "select * from " . DB_PREFIX . "user where id=" . $userId;
        $res = $this->userMod->querySql($sql);
        $this->assign('access_token', $this->accessToken);
        $this->assign('res', $res[0]);
        $this->assign('lang', $lang);
        $this->assign('storeid', $storeid);
        $this->display("userCenter/codecard.html");
    }
       /*
     * 新我的收藏
     * @author wangs
     * @date 2018-12-26
     */
    public function Collection() {
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $store_id = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;
        $this->assign('storeid', $store_id);
        $lang_id = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;
        $this->assign('lang', $lang_id);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        $userId = $this->userId;
        //收藏的店铺
        $bSql = 'SELECT store_id FROM  ' . DB_PREFIX . 'user_store  WHERE  user_id=' . $userId;
        $bData = $this->userStoreMod->querySql($bSql);
        foreach ($bData as $k => $v) {
            $sId[] = $v['store_id'];
        }
        $sIds = implode(',', $sId);
        if (!empty($sIds)) {
            $swhere = " where sl.distinguish=0 AND s.id in (" . $sIds . ')';
            $ssql = 'SELECT  s.id,s.longitude,s.latitude,l.`name` AS lname ,c.`name` AS cname,sl.`store_name` AS sltore_name,s.logo FROM  ' . DB_PREFIX . 'store AS s LEFT JOIN  ' . DB_PREFIX . 'store_lang AS sl ON sl.`store_id` = s.`id` and  sl.lang_id=' . $this->langid . ' LEFT JOIN  ' . DB_PREFIX . 'language AS l ON s.`lang_id` = l.`id` LEFT JOIN  ' . DB_PREFIX . 'currency AS c ON s.`currency_id` = c.`id`' . $swhere;
            $listData = $this->userStoreMod->querySql($ssql);
        } else {
            $listData = array();
        }
        foreach ($listData as $key => $val) {
            $busSql = "SELECT buss_id FROM " . DB_PREFIX . 'store_business WHERE  store_id=' . $val['id'];
            $busData = $this->userStoreMod->querySql($busSql);
            $listData[$key]['b_id'] = $busData[0]['buss_id'];
        }

        $this->assign('store_id', $sIds);
        $this->assign('listData', $listData);
        $language = $this->shorthand;
        $this->assign('language', $language);
        //收藏的文章
        $sql_article = 'SELECT article_id FROM  ' . DB_PREFIX . 'user_article  WHERE store_id =  ' . $store_id . ' AND user_id=' . $userId;
        $articleData = $this->colleCtionMod->querySql($sql_article);
        foreach ($articleData as $k => $v) {
            $id[] = $v['article_id'];
        }
        $ids = implode(',', $id);
        if (!empty($ids)) {
            $articleSql = 'SELECT a.id,al.title,al.brif,a.image,a.add_time FROM ' . DB_PREFIX . 'article AS a LEFT JOIN ' . DB_PREFIX . 'article_lang AS al ON a.id=al.article_id WHERE al.lang_id= ' . $lang_id . ' AND a.id in (' . $ids . ')';
            $articleData = $this->colleCtionMod->querySql($articleSql);
        } else {
            $articleData = array();
        }
        $this->assign('article_id', $ids);
        $this->assign('articleData', $articleData);
        
        //收藏的商品
        $this->assign('symbol', $this->symbol);
        $where = ' f.user_id =' . $userId;
        //列表页数据
        $sql = 'select distinct f.*,g.*,l.*,gl.original_img,f.id  from '
                . DB_PREFIX . 'store_goods as g inner join '
                . DB_PREFIX . 'user_collection as f on f.store_good_id = g.id inner join '
                . DB_PREFIX . 'goods_lang as l on l.goods_id = g.goods_id and l.lang_id=' . $lang_id . ' left join '
                . DB_PREFIX . 'goods as gl on g.goods_id = gl.goods_id  where ' . $where
                . ' group by f.store_good_id order by f.id desc limit 0, 8 ';
//     print_r($sql);exit;
        $data = $this->colleCtionMod->querySql($sql);
        $this->assign('data', $data);
        $this->display('userCenter/Collection.html');
     }
       /*
     * 新安全中心
     * @author wangs
     * @date 2018-12-26
     */

    public function safetyCenter() {
        //语言包
        $this->load($this->shorthand, 'WeChat/userCenter');
        $this->assign('langdata', $this->langData);
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;  //所选的站点id
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $this->assign('latlon', $latlon);
        $userId = $this->userId;
        $sql = "select * from " . DB_PREFIX . "user where id=" . $userId;
        $res = $this->userMod->querySql($sql);
        $this->assign('res', $res[0]);
        $this->assign('lang', $lang);
        $this->assign('storeid', $storeid);
        $this->display("userCenter/SafetyCenter.html");
    }
    
}
