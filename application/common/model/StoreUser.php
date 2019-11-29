<?php

namespace app\common\model;

/**
 * 门店店员模型
 * @author  luffy
 * @date    2019-10-13
 */
class StoreUser extends BaseModel{

    protected $name = 'store_user';

    /**
     * 获取所有门店店员
     * @author  luffy
     * @date    2019-10-13
     */
    public function getAllUser(){
        return $this->field('id,real_name')->where(['store_id'=>STORE_ID])->select();
    }

}
