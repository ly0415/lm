<?php

namespace app\api\model;

use app\common\model\TableNumber   as TableNumberModel;
/**
 * 桌号
 * @author  ly
 * @date    2019-12-09
 */
class TableNumber extends TableNumberModel{

    /**
     *获取桌号
     * @author ly
     * @date 2019-12-09
     */
    public function getStoreTableNumber($store_id = ''){
        return $this->where('store_id',$store_id)->column('number');
    }


}
