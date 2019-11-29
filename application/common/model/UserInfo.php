<?php

namespace app\common\model;

/**
 * 用户画像模型
 * @author  fup
 * @date    2019-09-24
 */
class UserInfo extends BaseModel{

    protected $name = 'user_info';

    protected $updateTime = false;

    //反序列化
    public function getContentAttr($val){
        return unserialize($val);
    }

    //序列化
    public function setContentAttr($val){
        return serialize($val);
    }

}
