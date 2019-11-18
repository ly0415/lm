<?php

namespace app\common\model;

/**
 * 余额记录
 * @author  luffy
 * @date    2019-07-09
 */
class AmountLog extends BaseModel
{
    protected $name = 'amount_log';

    protected $updateTime = false;

    /**
     * 余额记录详情
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-23
     * Time: 16:09
     */
    public static function detail($id){
        return self::get($id);
    }

}



