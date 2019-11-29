<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-05
 * Time: 下午 4:10
 */

namespace app\ipad\model;


class Category extends BaseModel
{
    public function getList(){
        $model = $this->field()->with(['goods'])->select();
        dump($model);die;
    }
    public function goods(){
        return $this->hasMany('StoreGoods','cat_id','category_id');
    }

}