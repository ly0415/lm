<?php

namespace app\api\model;
use app\common\model\FxUser as FxUserModel;

/**
 * 分销人员模型
 * @author  luffy
 * @date    2019-08-4
 */
class FxUser extends FxUserModel
{
    /**
     * 获取用户所属分销人员
     * @author  luffy
     * @date    2019-08-04
     */
    public static function getBeloneFxUser($user_id, $fileds = '*'){
        $model = new self;
        //获取分销人员
        $data = $model->alias('a')
            ->field($fileds)
            ->join('fx_user_account b','a.id = b.fx_user_id')
            ->where('b.user_id', $user_id)->find();
        $data = (!empty($data) ? $data->toArray() : []);
        if($data && $data['discounts'] > 0){
            $data['discount'] = $data['discounts'];
        }
        return $data;
    }

}