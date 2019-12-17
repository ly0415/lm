<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date:
 * Time:
 */

namespace app\store\model;

use think\Db;
use app\common\model\RoomCategory as RoomCategoryModel;
use  app\store\model\RoomType as RoomTypeModel;
use  app\store\model\Business as BusinessModel;
use  app\store\model\RoomCategory as RoomCateModel;
use  app\store\model\GoodsCategory as GoodsCategoryModel;
use  app\store\model\StoreGoods as StoreGoodsModel;

class RoomCategory extends RoomCategoryModel
{

    /**
     * 根据商品分类获取业务类型名称
     * Created by PhpStorm.
     * Author: ly
     * Date: 2019-10-15
     * Time: 11:36
     */
    public function getCategoryId($category_id){
        return $this->alias('a')
            ->field('b.id,b.room_name,b.room_img,b.room_adv_img,b.adv_url,b.modify_time,b.add_time,b.sort,b.superior_id,b.room_url,b.room_adv_imgs')
            ->join('room_type b','a.room_type_id=b.id')
            ->where('a.category_id',$category_id)
            ->select();
    }

    /**
     * 根据商品分类 获得其下商品
     * @return mixed
     */
    public function getRoomCategoryGoods($roomtype='',$storeid=''){
        $roomtype=58;
//        $list=$this->alias('a')
//            ->field('a.*')
////            ->join('business b','a.room_type_id=b.id')
//            ->join('goods_category g','g.id=a.category_id')
////            ->join('store_goods s','s.cat_id=g.id')
//            ->where('a.room_type_id',48)
////            ->where('s.store_id',58)
//            ->order('a.id','asc')
//            ->select();
//        print_r($list);die;
//        items:protected
        $pidroomtype=BusinessModel::where('pid',$roomtype)->select();
        $pidroomtype=$pidroomtype->toArray();
        $roomcategoryid=[];
        $categoryid=[];
        $roid=[];
        if($pidroomtype){
            foreach($pidroomtype as $value){
                $roomcategoryid[]=$value['id'];
            }
        }else{
            $roomcategoryid=$roomtype;
        }
        echo 1;
        print_r($roomcategoryid);
        if(is_array($roomcategoryid)){
            foreach($roomcategoryid as $val){
                $categoryid[]=$this->where('room_type_id',$val)->select();
            }
        }else{
            $categoryid[]=$this->where('room_type_id',$roomcategoryid)->select();
        }
        if($categoryid){
            foreach($categoryid as $value){
                foreach($value as $val){
                    $roid[]=$val['category_id'];
                }
            }
        }
        $roid=array_unique($roid);
        echo 2;
        print_r($roid);
        $goodsCategoryList=GoodsCategoryModel::select();
        if($roid){
            $list='';
            foreach($roid as $val){
                $val=1575;
                $list=$this->getsonList($goodsCategoryList,$val);
                print_r($list);die;
                if($list){
                    $des=[];
                    $vadata=[];
                    foreach($list as $va){
                        if($va['child']){
                            $vadata[]=array_keys($va['child']);
                        }else{
                            $des[]=$va['id'];
                        }

//                        $listli=array_merge($des,$vadata);
                    }
                    $data=array_keys($list);
                }else{
                    $date[]=$val;
                }
            }
            $list=array_merge($data,$date);
        }
        print_r($list);die;
        if($list){
            foreach($list as $val){
                $dats[]=StoreGoodsModel::where('cat_id',$val)->select();
            }
        }
        print_r($dats);die;

    }
//    public function tree($array, $pid='' )
//    {
//        $tree = array();
//        foreach ($array as $key => $value) {
//            if ($value['parent_id'] == $pid) {
//                $value['child'] = $this->tree($array, $value['id']);
//                if (!$value['child']) {
//                    unset($value['child']);
//                }
//                $tree[] = $value;
//            }
//        }
//        return $tree;
//    }


    public function getsonList($data='',$pid){
        $tree=[];
        foreach($data as $key =>$value) {
            if($value['parent_id']==$pid) {
                $tree[$value['id']]=$value;
                unset($data[$key]);
                $tree[$value['id']]['child']=$this->getsonList($data , $value['id']);

            }
        }
        return $tree;
        }

}