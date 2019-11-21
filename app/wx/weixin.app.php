<?php
/**
 * 微信公众号 操作类
 * wangh
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class WeixinApp extends  BaseWxApp{

    public function __construct() {
        parent::__construct();
    }

    public function __destruct() {

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








}
