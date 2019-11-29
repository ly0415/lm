<?php

namespace app\store\model;

use app\common\model\TagValue as TagValueModel;

/**
 * 规格/属性(值)模型
 * Class TagValue
 * @package app\store\model
 */
class TagValue extends TagValueModel
{

    /**
     * 根据标签组名称查询规格id
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-23
     * Time: 15:39
     */
    public function getTagValueIdByName($tag_id, $tag_value)
    {
        return self::where(compact('tag_id', 'tag_value'))->where('mark','=',1)->value('tag_value_id');
    }

    /**
     * 添加标签格值
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-23
     * Time: 15:39
     */
    public function add($tag_id, $tag_value)
    {
        return $this->save(compact('tag_id', 'tag_value'));
    }

    /**
     * 删除标签格值
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-23
     * Time: 15:38
     */
    public function del($tag_id, $tag_value_id)
    {
        return $this->where(compact('tag_id', 'tag_value_id'))->update(['mark'=>0]);
    }

}
