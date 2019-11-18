<?php

namespace app\common\model;

/**
 * 积分模型
 * Class PointLog
 * @package app\common\model
 */
class PointLog extends BaseModel
{
    protected $name = 'point_log';

    protected $createTime = 'add_time';

    protected $updateTime = false;


    /**
     * 新增积分记录
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-11-05
     * Time: 16:06
     */
    public static function add($data)
    {
        $model = new static;
        $model->save( $data );
    }
}
