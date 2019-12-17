<?php

namespace app\common\model;

/**
 * 桌号
 * @author  ly
 * @date    2019-12-06
 */
class TableNumber extends BaseModel{

    protected $name = 'table_number';

    //桌号类型
    public $table_type = [
        1 => '堂食',
        2 => '外带'
    ];
}
