<?php

namespace app\common\model;

/**
 * 分销用户关系模型
 * @author  luffy
 * @date    2019-08-4
 */
class FxUserAccount extends BaseModel
{
    protected $name = 'fx_user_account';
    protected $updateTime = false;


    //关联分销用户
    public function fxUser(){
        return $this->belongsTo('Distribution','fx_user_id','id');
    }

}
