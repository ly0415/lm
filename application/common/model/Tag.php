<?php

namespace app\common\model;

/**
 * 规格/属性(组)模型
 * Class Tag
 * @package app\common\model
 */
class Tag extends BaseModel
{
    protected $name = 'tag';
    protected $updateTime = false;


    /**
     * 关联标签值
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-23
     * Time: 17:46
     */
    public function specItems(){
        return $this->hasMany('TagValue','tag_id','group_id')
            ->field('tag_value_id item_id,tag_id,tag_value spec_value')
            ->where('mark','=',1);
    }

}
