<?php

namespace app\store\model;

use app\common\model\FxUserAccount as FxUserAccountModel;

/**
 * 分销人员对应会员模型
 * @author  luffy
 * @date    2019-08-20
 */
class FxUserAccount extends FxUserAccountModel{

    /**
     * 指定分销人员
     * @author  luffy
     * @date   2019-08-08
     */
    public function setAppoint($id, $buyer_id){
        //先校验
        if(self::get(['fx_user_id'=>$id, 'user_id'=>$buyer_id])){
            $this->error = '该用户已有所属分销人员！';
        }
        // 开启事务
        $this->startTrans();
        try {
            // 添加商品
            $this->allowField(true)->save(['fx_user_id'=>$id, 'user_id'=>$buyer_id]);
            // 商品图片
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

}