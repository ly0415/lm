<?php

namespace app\common\model;

/**
 * 业务类型分类
 * @author  fp
 * @date    2019-09-10
 */
class RoomCategory extends BaseModel{

    protected $name = 'room_category';

    public function roomType(){
    	return $this->belongsTo('RoomType','room_type_id','id');
    }


    /**
     * 根据商品分类获取业务类型名称
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-15
     * Time: 11:36
     */
    public static function getRoomTypeNameByCategoryId($category_id){
        return self::all(['category_id'=>$category_id],'roomType');
    }

}
