<?php

/**
 * wangh
 * Class SmsApp
 * 短信发送类
 */
class SmsApp  extends  BaseFrontApp{

    private  $accessKeyId;  // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
    private  $accessKeySecret;
    private  $SignName;    // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
    private  $TemplateCode;    // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
    private $OutId='';  // fixme 可选: 设置发送短信流水号
    private $SmsUpExtendCode=''; // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
    private  $TemplateCodeReset;

    public function __construct() {
        parent::__construct();
        $this->accessKeyId = 'LTAIHGCVtIniuaKy';
        $this ->accessKeySecret = 'CsXS0lJKOS8LIo4UCQ2BCTS9GmHhlg';
        $this ->SignName = '艾美睿零售';
        $this ->TemplateCode = 'SMS_117585003';
        $this -> TemplateCodeReset = 'SMS_117585002';

    }


    /**
     * 发送短信(注册)
     */
    public  function sendSms(){
        $phone = trim($_REQUEST['phone']);
        if(empty($phone)){
            $this->setData(array(), $status = '0', $message = '手机号码错误');
        }
        if(!preg_match('/^(13[0-9]|14[0-9]|15[0-9]|17[0-9]|18[0-9])\d{8}$/',$phone)){
            $this->setData(array(), $status = '0', $message = '手机号码格式错误');
        }
        //
        $code = $this -> getCode(); //验证码
        $params = array ();
        $params["PhoneNumbers"] = $phone;
        $params["SignName"] = $this -> SignName;
        $params["TemplateCode"] = $this ->TemplateCode;
        $params['TemplateParam'] = array(
            "code" => $code,
//            "product" => "倍速创恒"
        );
        if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"]);
        }

        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        // @return bool|\stdClass 返回API接口调用结果，当发生错误时返回false
        include_once ROOT_PATH."/includes/alidayu/SignatureHelper.php";

        $helper = new SignatureHelper();

        // 此处可能会抛出异常，注意catch
        $content = $helper->request(
            $this ->accessKeyId,
            $this ->accessKeySecret,
            "dysmsapi.aliyuncs.com",
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            ))
        );

        header("Content-Type: text/plain; charset=utf-8"); // 输出为utf-8的文本格式
        $content = (array)$content ;

       if($content['Code'] == 'OK'){  //发送成功 加入数据库
           $smsMod = &m('sms');
           $data = array(
               'phone' => $phone,
               'code' => $code,
               'send_time' => time()
           );
           $smsMod -> doInsert($data);
       }

    }

    /**
     * 发送短信(重置)
     */
    public  function sendSmsReset(){
        $phone = trim($_REQUEST['phone']);
        if(empty($phone)){
            $this->setData(array(), $status = '0', $message = '手机号码错误');
        }
        if(!preg_match('/^(13[0-9]|14[0-9]|15[0-9]|17[0-9]|18[0-9])\d{8}$/',$phone)){
            $this->setData(array(), $status = '0', $message = '手机号码格式错误');
        }
        //
        $code = $this -> getCode(); //验证码
        $params = array ();
        $params["PhoneNumbers"] = $phone;
        $params["SignName"] = $this -> SignName;
        $params["TemplateCode"] = $this ->TemplateCodeReset;
        $params['TemplateParam'] = array(
            "code" => $code,
//            "product" => "倍速创恒"
        );
        if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"]);
        }

        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        // @return bool|\stdClass 返回API接口调用结果，当发生错误时返回false
        include_once ROOT_PATH."/includes/alidayu/SignatureHelper.php";

        $helper = new SignatureHelper();

        // 此处可能会抛出异常，注意catch
        $content = $helper->request(
            $this ->accessKeyId,
            $this ->accessKeySecret,
            "dysmsapi.aliyuncs.com",
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            ))
        );

        header("Content-Type: text/plain; charset=utf-8"); // 输出为utf-8的文本格式
        $content = (array)$content ;

        if($content['Code'] == 'OK'){  //发送成功 加入数据库
            $smsMod = &m('sms');
            $data = array(
                'phone' => $phone,
                'code' => $code,
                'send_time' => time()
            );
            $smsMod -> doInsert($data);
        }

    }


    /**
     * 生成验证码
     * @author xiayy
     * @date 2016-11-11
     */
    public function getCode($length =6 ){
        $min = pow(10 , ($length - 1));
        $max = pow(10, $length) - 1;
        return rand($min, $max);
    }


}
