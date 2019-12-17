<?php

//

/**
 * 微信操作类.
 * @author lvj
 * @date 2016-11-21
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class WxApp extends BaseWxApp {


    public $appId;
    public $appSecret;
    public $token;

    public function __construct() {
        parent::__construct();
        $this->token = 'weixin';
    }

    public function __destruct() {
        
    }

    /**
     * 微信验证检测
     * @author lvj
     * @date 2016-11-21
     */
    public function validWx() {
        import("wx.lib");
        define("TOKEN", $this->token);
        $wechatObj = new wechatCallbackapiTest();
        $wechatObj->valid();
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
    public function getAccessToken(){
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
    /**
     * 获取微信用户信息
     * @author lvj
     * @date 2016-11-21
     */
    public function sendCurlXml($url, $data) {
        $headers = array("Content-Type: text/xml; charset=utf-8");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    /*
     * 获取url
     * @author wl
     * @date 2017-2-21 11:48:41
     */

    public function httpGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }
}
