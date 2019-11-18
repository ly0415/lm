<?php

namespace app\common\model;

/**
 * 分销规则
 * @author  luffy
 * @date    2019-10-17
 */
class FxDiscountLog extends BaseModel{

    protected $name = 'fx_discount_log';

    /**
     * 生成分销变更记录
     * @author  luffy
     * @date    2019-10-17
     */
    public function addLog($data){
        return $this->insert([
            'fx_user_id'    => $data['fx_user_id'],
            'fx_discount'   => $data['new_discount'],
            'old_discount'  => $data['discount'],
            'current_rule_percent'  => $data['rule_id'],
            'is_check'      => 2,
            'source'        => 2,
            'add_time'      => time(),
            'check_time'    => time(),
            'check_user'    => USER_ID
        ]);
    }

}