<?php

namespace app\store\model;

use app\common\model\FxUser as FxUserModel;

/**
 * 分销人员
 * @author  fup
 * @date    2019-09-10
 */
class FxUser extends FxUserModel{
    /**
     * 获取分销码
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-10
     * Time: 16:04
     */
    public function getCodeByUser($user){
        if(!$user) return [];
        $data = $this->alias('a')
            ->field('a.id,a.discount,b.id as bid,b.discounts,a.fx_code')
        ->join('fx_user_account b','a.id = b.fx_user_id','LEFT')
        ->join('user u','b.user_id = u.id','LEFT')
        ->where('a.mark','=',1)
        ->where('a.is_check','=',2)
        ->where('a.level','=',3)
        ->where('u.mark','=',1)
        ->where('u.is_use','=',1)
        ->where('u.phone','=',$user['phone'])
        ->find();
        return $data;
    }

    /**
     * 获取分销数据对应的一级分销信息,最多递归10次
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-10
     * Time: 19:19
     */
    public static function getLevel1Info($childid, $count = 1)
    {
        $data = self::get($childid);
        if ($data && $data['level'] == 1) {
            return $data;
        } elseif (($count < 10) && ($data['parent_id'] > 0)) {
            return self::getLevel1Info($data['parent_id'], $count + 1);
        } else {
            return array();
        }
    }
}