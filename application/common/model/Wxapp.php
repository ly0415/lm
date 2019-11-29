<?php

namespace app\common\model;

use app\common\exception\BaseException;
use think\Cache;
use think\Db;

/**
 * 微信小程序模型
 * Class Wxapp
 * @package app\common\model
 */
class Wxapp extends BaseModel
{
    protected $name = 'wxapp';



    /**
     * 获取小程序信息
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-22
     * Time: 11:50
     */
    public static function detail($store_id)
    {
        return self::get(['store_id' => $store_id] ?: []);
    }



}
