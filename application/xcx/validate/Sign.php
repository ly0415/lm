<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-10
 * Time: 下午 4:06
 */

namespace app\xcx\validate;


class Sign extends Base
{
    protected $rule = [
        'uid|用户id' => 'require|number',
    ];
}