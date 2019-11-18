<?php

namespace app\store\model;

use app\common\model\DiscountChange   as DiscountChangeModel;
use app\common\model\FxOrder        as FxOrderModel;
use app\store\model\FxUser          as FxUserModel;
use app\common\model\FxDiscountLog  as FxDiscountLogModel;
use think\Db;

/**
 * 优惠变更管理
 * @author  liy
 * @date    2019-10-23
 */
class DiscountChange extends FxDiscountLogModel{

    /**
     * 优惠变更列表
     * @author  liy
     * @date    2019-10-23
     */
    public function getList($check=''){

        !empty($check) && $this->where('a.is_check',"$check");
        return $this->alias('a')
            ->field('a.id,a.fx_user_id,d.real_name,a.fx_discount,a.old_discount,a.add_time,a.check_time,a.is_check,c.user_name')
            ->join('fx_user d', 'd.id = a.fx_user_id')
            ->join('store_user c', 'c.id = a.check_user ','LEFT')
            ->order('a.is_check,a.id asc')
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 更新审核状态
     * @author  liy
     * @date    2019-10-23
     */
    public function edit($status,$fx_id,$fxDiscount)
    {

        $FxUser = FxUserModel::get($fx_id);
        $this->startTrans();
        try {
            $ddta['id']=$fx_id;
            $ddta['discount'] = $fxDiscount;
            $FxUser->allowField(true)->save($ddta);
            $data['is_check'] = $status;
            $data['check_time'] = time();
            $data['check_user'] = USER_ID;
            $this->allowField(true)->save($data);
            $this->commit();
            return $res['code'] = 1;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 删除
     * @author  liy
     * @date    2019-10-23
     */
    public function remove($id)
    {
        return $this->delete();
    }

    public static function detail($id){
        return self::get($id);
    }



}
