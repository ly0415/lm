<?php

namespace app\store\model;

use app\common\model\Tag as TagModel;

/**
 * 规格/属性(组)模型
 * Class Tag
 * @package app\store\model
 */
class Tag extends TagModel
{

    /**
     * 获取顾客画像列表
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-23
     * Time: 17:44
     */
    public function getList(){
        return $this->with('specItems')
            ->where('mark','=',1)
            ->where('business_id','=',BUSINESS_ID)
            ->field('tag_id as group_id,tag_name as group_name')
            ->select();
    }

    /**
     * 根据规格组名称查询规格id
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-23
     * Time: 15:47
     */
    public function getTagIdByName($tag_name)
    {
        return self::where(compact('tag_name'))->where('mark','=',1)->value('tag_id');
    }

    /**
     * 新增标签组
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-23
     * Time: 15:46
     */
    public function add($tag_name)
    {
//        $store_id = STORE_ID;
        $business_id = BUSINESS_ID;
        return $this->save(compact('tag_name','business_id'));
    }

    /**
     * 删除标签组
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-23
     * Time: 15:46
     */
    public function del($tag_id)
    {
//        $store_id = STORE_ID;
        return $this->where(compact('tag_id'))->update(['mark'=>0]);
    }

}
