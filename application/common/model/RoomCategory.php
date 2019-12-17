<?php

namespace app\common\model;

/**
 * 业务类型分类
 * @author  fp
 * @date    2019-09-10
 */
class RoomCategory extends BaseModel{

    protected $name = 'room_category';

    protected $createTime = 'add_time';

    protected $updateTime = false;

    public function roomType(){
    	return $this->belongsTo('RoomType','room_type_id','id');
    }

    //业务
    public function business(){
        return $this->belongsTo('Business','room_type_id','id');
    }


    /**
     * 根据商品分类获取业务类型名称
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-15
     * Time: 11:36
     */
    public static function getRoomTypeNameByCategoryId($category_id){
        return self::all(['category_id'=>$category_id],'business');
    }

    /**
     * 分类详情
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-11-21
     * Time: 15:32
     */
    public static function detail($id){
        $filter = [];
        if (is_array($id)) {
            $filter = array_merge($filter, $id);
        } else {
            $filter['id'] = (int)$id;
        }
        return self::get($filter,'business');
    }

}
