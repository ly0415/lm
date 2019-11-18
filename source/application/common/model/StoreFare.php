<?php

namespace app\common\model;

/**
 * 运费模型
 * @author  luffy
 * @date    2019-08-12
 */
class StoreFare extends BaseModel{
    protected $name = 'store_fare';

    /**
     * 关联运费表
     * @author  luffy
     * @date    2019-08-12
     */
    public function fare(){
        return $this->hasMany('store_fare_rule','fare_id','id');
    }

    /**
     * 获取运费比率
     * @author  luffy
     * @date    2019-08-12
     */
    public static function getFare($num, $storeId){
        //运费规则
        $data = self::with(['fare'=>function($query)use($num, $storeId){
            $query->where(['min_number'=>['ELT', $num], 'max_number'=>['EGT', $num], 'mark'=>1])->order('max_number ASC');
        }])->where(['store_id'=>$storeId, 'mark'=>1])->select()->toArray();
        if(empty($data)){
            return false;
        } else {
            $fare   = $data[0]['fare'];
            //过滤边界值
            foreach($fare as $key => $value){
                if($value['min_number'] == $num && $value['min_symbol'] == 1){
                    unset($fare[$key]);
                }
                if($value['max_number'] == $num && $value['max_symbol'] == 1){
                    unset($fare[$key]);
                }
            }
        }
        //计算运费
        if (!empty($fare) && $fare[0]) {
            return $fare[0]['percent'] * 0.01;
        } else {
            return 1;
        }
    }
}
