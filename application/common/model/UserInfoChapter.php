<?php

namespace app\common\model;

/**
 * 用户画像模型
 * @author  fup
 * @date    2019-09-24
 */
class UserInfoChapter extends BaseModel{

    protected $name = 'user_info_chapter';


    /**
     * 关联用户画像详情
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-24
     * Time: 20:32
     */
    public function userInfo(){
        return $this->hasMany('UserInfo','order_sn','order_sn');
    }

}
