<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    // +----------------------------------------------------------------------
    // | 应用设置
    // +----------------------------------------------------------------------

    // 应用调试模式
    'app_debug' => true,
    // 应用Trace
    'app_trace' => false,
    // 应用模式状态
    'app_status' => '',
    // 是否支持多模块
    'app_multi_module' => true,
    // 入口自动绑定模块
    'auto_bind_module' => false,
    // 注册的根命名空间
    'root_namespace' => [],
    // 扩展函数文件
    'extra_file_list' => [THINK_PATH . 'helper' . EXT],
    // 默认输出类型
    'default_return_type' => 'json',
    // 默认AJAX 数据返回格式,可选json xml ...
    'default_ajax_return' => 'json',
    // 默认JSONP格式返回的处理方法
    'default_jsonp_handler' => 'jsonpReturn',
    // 默认JSONP处理方法
    'var_jsonp_handler' => 'callback',
    // 默认时区
    'default_timezone' => 'PRC',
    // 是否开启多语言
    'lang_switch_on' => false,
    // 默认全局过滤方法 用逗号分隔多个
    'default_filter' => 'htmlspecialchars',
    // 默认语言
    'default_lang' => 'zh-cn',
    // 应用类库后缀
    'class_suffix' => false,
    // 控制器类后缀
    'controller_suffix' => false,

    // +----------------------------------------------------------------------
    // | 模块设置
    // +----------------------------------------------------------------------

    // 默认模块名
    'default_module' => 'store',
    // 禁止访问模块
    'deny_module_list' => ['common'],
    // 默认控制器名
    'default_controller' => 'Index',
    // 默认操作名
    'default_action' => 'index',
    // 默认验证器
    'default_validate' => '',
    // 默认的空控制器名
    'empty_controller' => 'Error',
    // 操作方法后缀
    'action_suffix' => '',
    // 自动搜索控制器
    'controller_auto_search' => false,

    // +----------------------------------------------------------------------
    // | URL设置
    // +----------------------------------------------------------------------

    // PATHINFO变量名 用于兼容模式
    'var_pathinfo' => 's',
    // 兼容PATH_INFO获取
    'pathinfo_fetch' => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'],
    // pathinfo分隔符
    'pathinfo_depr' => '/',
    // URL伪静态后缀
    'url_html_suffix' => 'html',
    // URL普通方式参数 用于自动生成
    'url_common_param' => false,
    // URL参数方式 0 按名称成对解析 1 按顺序解析
    'url_param_type' => 0,
    // 是否开启路由
    'url_route_on' => true,
    // 路由使用完整匹配
    'route_complete_match' => false,
    // 路由配置文件（支持配置多个）
    'route_config_file' => ['route'],
    // 是否强制使用路由
    'url_route_must' => false,
    // 域名部署
    'url_domain_deploy' => false,
    // 域名根，如thinkphp.cn
    'url_domain_root' => '',
    // 是否自动转换URL中的控制器和操作名
    'url_convert' => true,
    // 默认的访问控制器层
    'url_controller_layer' => 'controller',
    // 表单请求类型伪装变量
    'var_method' => '_method',
    // 表单ajax伪装变量
    'var_ajax' => '_ajax',
    // 表单pjax伪装变量
    'var_pjax' => '_pjax',
    // 是否开启请求缓存 true自动缓存 支持设置请求缓存规则
    'request_cache' => false,
    // 请求缓存有效期
    'request_cache_expire' => null,
    // 全局请求缓存排除规则
    'request_cache_except' => [],

    // +----------------------------------------------------------------------
    // | 模板设置
    // +----------------------------------------------------------------------

    'template' => [
        // 模板引擎类型 支持 php think 支持扩展
        'type' => 'Think',
        // 模板路径
        'view_path' => '',
        // 模板后缀
        'view_suffix' => 'html',
        // 模板文件名分隔符
        'view_depr' => DS,
        // 模板引擎普通标签开始标记
        'tpl_begin' => '{',
        // 模板引擎普通标签结束标记
        'tpl_end' => '}',
        // 标签库标签开始标记
        'taglib_begin' => '{',
        // 标签库标签结束标记
        'taglib_end' => '}',
    ],

    // 视图输出字符串内容替换
    'view_replace_str' => [],
    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl' => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',
    'dispatch_error_tmpl' => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',

    // +----------------------------------------------------------------------
    // | 异常及错误设置
    // +----------------------------------------------------------------------

    // 异常页面的模板文件
    'exception_tmpl' => THINK_PATH . 'tpl' . DS . 'think_exception.tpl',

    // 错误显示信息,非调试模式有效
    'error_message' => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg' => false,
    // 异常处理handle类 留空使用 \think\exception\Handle
    'exception_handle' => '\\app\\common\\exception\\ExceptionHandler',

    // +----------------------------------------------------------------------
    // | 日志设置
    // +----------------------------------------------------------------------

    'log' => [
        // 日志记录方式，内置 file socket 支持扩展
        'type' => 'File',
        // 日志保存目录
        'path' => LOG_PATH,
        // 日志记录级别
        'level' => [],
        // error和sql日志单独记录
        'apart_level' => ['begin', 'error', 'sql', 'yoshop-info'],
    ],

    // +----------------------------------------------------------------------
    // | Trace设置 开启 app_trace 后 有效
    // +----------------------------------------------------------------------
    'trace' => [
        // 内置Html Console 支持扩展
        'type' => 'Html',
    ],

    // +----------------------------------------------------------------------
    // | 缓存设置
    // +----------------------------------------------------------------------

    'cache' => [
        // 驱动方式
        'type' => 'File',
        // 缓存保存目录
        'path' => CACHE_PATH,
        // 缓存前缀
        'prefix' => '',
        // 缓存有效期 0表示永久缓存
        'expire' => 0,
    ],

    // +----------------------------------------------------------------------
    // | 会话设置
    // +----------------------------------------------------------------------

    'session' => [
        'id' => '',
        // SESSION_ID的提交变量,解决flash上传跨域
        'var_session_id' => '',
        // SESSION 前缀
        'prefix' => '',
        // 驱动方式 支持redis memcache memcached
        'type' => '',
        // 是否自动开启 SESSION
        'auto_start' => true,
    ],

    // +----------------------------------------------------------------------
    // | Cookie设置
    // +----------------------------------------------------------------------
    'cookie' => [
        // cookie 名称前缀
        'prefix' => '',
        // cookie 保存时间
        'expire' => 0,
        // cookie 保存路径
        'path' => '/',
        // cookie 有效域名
        'domain' => '',
        //  cookie 启用安全传输
        'secure' => false,
        // httponly设置
        'httponly' => '',
        // 是否使用 setcookie
        'setcookie' => true,
    ],

    //分页配置
    'paginate' => [
        'type' => 'bootstrap',
        'var_page' => 'page',
        'list_rows' => 10,
    ],

    'service_xcx' => [
        'app_id' => 'wxa07a37aef375add1',
        'app_secret' => '',
        'mchid' => '1450526802',
        'apikey' => 'DB4EED2130E6D0CAF383E6B9B66D5528'
    ],
    'xcx' => [
        'app_id' => 'wxd483c388c3d545f3',
        'app_secret' => 'd19b0561679a32122f10d524153f7ea5',
        'mchid' => '1515804821',
        'apikey' => 'Sem68GvhBu2ag5ncyJxsbDrXzZAHT3VK'
    ],
    'xcx1' => [
        'app_id' => 'wxa591f33f3defeae5',
        'app_secret' => '',
        'mchid' => '1551815301',
        'apikey' => 'zjzlmjxw330824196704270024307936'
    ],
    'weixin' => [
        'app_id' => 'wxa07a37aef375add1',
        'app_secret' => 'ce3e519287e84c68fbd63b74b7ea501f',
        'mchid' => '1334480801',
        'apikey' => 'Sem68GvhBu2ag5ncyJxsbDrXzZAHT3VK'
    ],
    'alipay'=>[
        //应用ID,您的APPID。
        'app_id' => "2017011905254523",

        //商户私钥
        'merchant_private_key' => "MIIEowIBAAKCAQEA1/oxCP3C+S/0iJT2XBrljiwD2REn909bf+9kWsnIFpw4GwtWr59no6EQ/BU9LiaC0jDJOd9RIXbNkdIMVTI4fKG5yde3Dkc8pr1v5I+y7IDKGEiTrcL3yQWaO9bOCWKqQyOzX4/eF5GFw8Ih23oXSEPd2QWspJmxVWAFCQDVu+Tikx3cu1mIMa7z9a7lN/tA9ctYafLqCli4iApEWmcXeE5fCMjSYnTwD2fSu48MBBCGSBWwZs3wM3bMI3KP06r5jA5o/AJQKMSH5CQEJ09sLJyeosdYiPHWbG7zdyKH1PZNhUIcOfEm2+a/keTqJ2ZEI01M80H2LSCYC5JKbO6KXwIDAQABAoIBAG5+sMGR2kNUdn2+AEBk/lZ7TEisj07micBtQGF2ZGi06btkVKgrHIHJcIAXeaJ3z2wry3dROhetyUQ2O1sHA4E32G5cb2ndpjkEKA++OOLojPxZfTxjyBNPS3Yb0nNYyBTrWeSlHRHfwJjDZED+OJUfK4vRbF8VxnUQV+MgSzkB0v5DxbX9CflUsELusCbhfU0a0H6MuHlVdh4c8Fsmf1xRPtz/tpZwLU4+FetAll2NJoE35lvoJF+jiCw5AY1PY6Y0SaJIdM/dr4UuSzFXHCkAq/0OHRGKR/ajNGDSBhq9iINhU/sUYT87G9b09MBJ2ySM0d9U3w0Fx1EhAXXvQqkCgYEA8XAqj9JHTNi/TMxLmUWFRV18Qo2QS6Atm4GD4edKal8sBnSKMqTlstlf5cNTZ6ak2cATMiuaCHswBpmAtw9q1U7rpZ19NBVrUHKLG2UU1YAIQQ5O5Ct3C/mPqtYT3W18mKzZ5p8wL9tk9OwjY89c6RpFseRfvHk/LLEtGdUzgIMCgYEA5QDoKyRQgyVVIK2w0Obdovn8I8zI8Ay2204atawl32ZxgZX1SJlXC87oPv3o4S2XAsSRpATtlT8Ltwa3rQ93FaqWK4/rPLS05ZpughlOSJSlAK9OsmF4Xdqp4CBPTj/WGQrgd+fCa7GEsUKPrUoBjooC2IM3HEUUgPYcwgsZr/UCgYBSCBU99mkpT/93XXZWJkvIrKG6jxS2zT6RtmiTyZz8FUgFDXWjDWnJ4Zd2nm3pKrKaFWuwQSY9uXUw2Njl2cQno3/nLmJK3vguRizDaw2wGKc1S2I8nhP9qpZIqiHnuvp5eUkz1WRu7jEYEl9X2y2rObTyYzCv/dYcHjq/qzOrdwKBgBvNyWJ7jT7vCG/oRsCGV0CTY3ahRYBHuufTitCl7w85q+xU3awL2hK382C6iUzVsTEH1rr4UjQ9rFlzeleLuiSqSoNNfP0o35HE90fadLPBQGtd3Ysw5GFYzClHIvnYLFFsDabhP6y9p+OxtioPAzNgNEo/XDCVfpDN0N4KZPsFAoGBALDwQ7AvmEFARfx0mBI5Q577MqVaVvLwhoiEMtL/X6aGTAxQdv2QryYfa2fjRMUBZiNu1am93NkwkWAs6+geDRNqhdRHWp10lesWcj6Eb9xsLbe4oQsd1U71nz6UrZQtmUNyRV+BZ0Bxbh98Jz33KdEl/pkTncl23zASRVMAud+j",
        //异步通知地址
        'notify_url' => 'web/notify_notice.php',

        //同步跳转
        'return_url' => 'web/index.php?s=/store/Notify/returnUrl',

        //编码格式
        'charset' => "UTF-8",

        //签名方式
        'sign_type'=>"RSA2",

        //支付宝网关
        'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

        //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
        'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAnJ5Z9Bo7XsApaPKVl3xgzGxJjCMYjWXMS1dA6hCDkBVb/bMeY/Ta7KVwjhbKO3PZW59bZD7t8BAcsA1/7FUsjZUYJQMh9+FbOclv5pbSShjBzFpS5C7ZdctUxk3AOwHbyXHeh39rY4RX2a/lnLDvt3SFKzLu9ebA2hSeKLkz1m35D9cdbS8Ypxg4XjoJdDe1cwS0VmJrcJ8otxibCZPBTc0vp24d2j/9AZUMTAff/MgeoGQV+R5llKAtd5j48bDZqEKBo+RGjzquJRY1eSuCxHPMfSj4/KXHr+JRHb9+g9hTLT+hYYrOwQKRXDQzBbOG50NkqiACQdhJbnQBeVeqtwIDAQAB",
    ],

    'aliyun' => [
        'AccessKeyId' => 'LTAIHGCVtIniuaKy',
        'AccessKeySecret' => 'CsXS0lJKOS8LIo4UCQ2BCTS9GmHhlg',
        'sign' => '艾美睿零售',
        'template_code' => 'SMS_156277705',
        'is_enable' => 1
    ]


];
