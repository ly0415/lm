<?php

namespace app\common\model;

/**
 * 分销管理模型
 * @author  luffy
 * @date    2019-08-15
 */
class Distribution extends BaseModel{

    protected $name = 'fx_user';

    //分销人员状态
    public $status = [
        '1'     => '正常',
        '2'     => '冻结',
    ];

    //关联分销规则
    public function fxRule(){
        return $this->belongsTo('FxRule','rule_id','id');
    }
}
