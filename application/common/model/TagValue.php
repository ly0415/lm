<?php

namespace app\common\model;

/**
 * 标签/属性(值)模型
 * Class TagValue
 * @package app\common\model
 */
class TagValue extends BaseModel
{
    protected $name = 'tag_value';
    protected $updateTime = false;

    /**
     * 关联规格组表
     * @return $this|\think\model\relation\BelongsTo
     */
    public function tag()
    {
        return $this->belongsTo('Tag');
    }

}
