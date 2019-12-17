<?php

// [ 应用入口文件 ]

// 定义网站域名
define('DOMAIN_NAME', 'http://www.lmeri.com/');

// 定义运行目录
define('WEB_PATH', __DIR__ . '/');

// 定义应用目录
define('APP_PATH', WEB_PATH . '../source/application/');

//定义图片路径常量
define('BIG_IMG', DOMAIN_NAME.'web/uploads/big/');     //大图
define('SIM_IMG', DOMAIN_NAME.'web/uploads/small/');     //小图

//定义小程序
//define('STORE_ID', 58);     //store_id
//define('LANG_ID', 29);     //lang_id
// 加载框架引导文件
require APP_PATH . '../thinkphp/start.php';
