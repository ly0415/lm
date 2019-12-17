<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-04
 * Time: 下午 3:27
 */

namespace app\ipad\validate;

class Login extends BaseValidate
{
    protected $rule = [
        ['user_name', 'require','请输入账号'],
        ['password', 'require','请输入密码']
    ];
}