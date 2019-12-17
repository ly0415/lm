<?php

namespace app\api\model;

use Think\Db;
use think\Config;
use app\common\exception\BaseException;
use app\common\model\Store as StoreModel;
use app\api\model\Index   as IndexModel;

/**
 * 商品详情
 * Class UserAddress
 * @package app\common\model
 */
class GoodsList extends StoreModel
{

    /**
     * 店铺是否营业
     * @author  liy
     * @date    2019-12-12
     */
    public function storeIsOpen($store_id=''){
        if(!$this->getResult($store_id,2)){
            throw new BaseException(['msg' => '暂停营业']);
        }
        $store  = $this
            ->field('id,store_start_time,store_end_time')
            ->where('is_open',1)
            ->where('id',$store_id)
            ->find();
        $now = time();
        $_now = date('Y-m-d',$now);
        if(!empty($store)){
            $start_time = strtotime($_now . ' ' .$store['store_start_time'] . ':00');
            $end_time = strtotime($_now . ' ' . $store['store_end_time'] . ':00');
            if($now > $start_time && $now < $end_time){
                throw new BaseException(['msg' => '正常营业']);
            }
            throw new BaseException(['msg' => '暂停营业']);
        }
        throw new BaseException(['msg' => '暂停营业']);

    }

    /**
     * 是否营业，是否允许使用余额
     * author ly
     * date 2019-12-12
     */
    private function getResult($storeId=0,$type=0){
        $list = Db::name('store_console')
            ->field('id,type,status,relation_1,relation_2,create_time,mark')
            ->where('type',$type)
            ->where('mark',1)
            ->limit(1)
            ->select();
        $storeColsed = explode(',', $list[0]['relation_1']);
        if(!in_array($storeId,$storeColsed)){
            return true;
        }
        return false;
    }
    /**
     * 设置错误信息
     * Created by PhpStorm.
     * Author: ly
     * Date: 2019-12-12
     */
    private function setError($error)
    {
        empty($this->error) && $this->error = $error;
    }

    /**
     * 为您推荐的商品详情
     * @author  liy
     * @date    2019-12-12
     * $source='',$cid='',$goods_key='',$auxiliary='',$latlon='',$lang_id='',$store_id='',$goods_id='',$gid=''
     */
    public function tuiInfo($lang_id='',$store_id='',$goods_id='',$gid=''){
//        $goods_id = 955;98 15323
//        $store_id = 58;
        if($goods_id){
            $g_info = Db::name('store_goods')
                ->alias('a')
                ->field('a.id,a.goods_id,a.cat_id,a.goods_sn,a.goods_name as lgoods_name,a.goods_storage,a.goods_type,a.spec_type,
                a.click_count,a.goods_salenum,a.goods_collect,a.brand_id,a.brand_name,a.store_id,
                a.store_name,a.market_price,a.shop_price,a.cost_price,a.comment_count,a.price_ladder,
                a.keywords as lkeywords,a.goods_remark as lgoods_remark,a.goods_content as lgoods_content,a.original_img,a.is_on_sale,a.is_free_shipping,
                a.sort,a.is_recommend,a.is_joint,a.is_new,a.is_hot,a.last_update,a.give_integral,a.exchange_integral,
                a.suppliers_id,a.prom_type,a.prom_id,a.commission,a.spu,a.sku,a.shipping_area_ids,a.on_time,a.add_time,
                a.mark,a.style_id,a.room_id,a.shipping_price,a.code_url,
                a.selected,a.is_recom,a.bar_code,a.deduction,a.attributes,a.delivery_fee,a.store_goods_type,l.goods_name,
                l.goods_remark ,l.keywords,l.goods_content')
                ->join('goods_lang l','l.goods_id =a.goods_id','LEFT')
                ->where(['a.goods_id'=>$goods_id,'a.mark'=>1,'a.store_id'=>$store_id])
                ->where('l.lang_id',$lang_id)
                ->find();
            $id = $g_info['id'];
        }
//        else{
//            $id = !empty($gid) ? $gid : 0;
//        }
        if (empty($id) || empty($g_info)) {
            throw new BaseException(['msg' => '该商品已下架']);
        }
        //商品图片 goods_images
        $img_arr = Db::name('goods_images')
                            ->where('goods_id',$goods_id)
                            ->select();
        $data['img_arr'] = $img_arr ? $img_arr->toArray() : [];
        //商品属性值表  goods_attr
        $attr_arr = Db::name('goods_attr')
                            ->alias('c')
                            ->field('c.goods_attr_id,c.goods_id,c.attr_id,c.attr_value,c.attr_price,l.name as attr_name')
                            ->join('goods_attr_lang l','c.attr_id=l.a_id','LEFT')
                            ->where('goods_id',$goods_id)
                            ->where('l.lang_id',$lang_id)
                            ->select();
        $data['attr_arr'] = $attr_arr ? $attr_arr->toArray() : [];
        //获取评价列表信息
        $list = Db::name('goods_comment')
                            ->field('comment_id,username,goods_rank,add_time,content,img,revert')
                            ->where('goods_id',$goods_id)
                            ->where('store_id',$store_id)
                            ->order('comment_id','DESC')
                            ->select();
        $data['list'] = $list ? $list->toArray() : [];
        //获取商品评价数量
        $storegoodmodel = new StoreGoods;
        $data['good_all_num'] = count($data['list']);
        //推荐
//        if(!empty($g_info['style_id'])){
//            $storegoodmodel->where('s.style_id',$g_info['style_id']);
//        }
//        if(!empty($g_info['brand_id'])){
//            $storegoodmodel->where('s.brand_id',$g_info['brand_id']);
//        }
//        $name = $g_info['goods_name'];
//        $storegoodmodel->where('s.goods_name','like',"%$name%");
//        $store_good = $storegoodmodel
//            ->alias('s')
//            ->field('s.id,gl.original_img,s.goods_id')
//            ->join('goods gl','s.goods_id=gl.goods_id','LEFT')
//            ->where('s.store_id',$store_id)
//            ->where('s.is_on_sale',1)
//            ->where('s.mark',1)
//            ->where('s.id','<>',$id)
//            ->select();
        $data['info'] = $g_info;
        return $data;
    }

    /**
     * 获取配送店铺
     * @author  liy
     * @date    2019-12-13
     */
    public function getStore($latlon='',$store_good_id='',$user_id='',$lang_id='',$countryId=-'')
    {
        $indexmodel = new IndexModel;
        $latlon = !empty($latlon) ? $latlon:'32.0572355,118.77807441';
        //根据定位排序
        $latlon = explode(',',$latlon);
        $lng    = $latlon[1]; //经度
        $lat    = $latlon[0]; //纬度
        $latlon = $indexmodel->coordinate_switchf($lat,$lng);
        $lng    = $latlon['Longitude'];
        $lat    = $latlon['Latitude'];
        //腾讯转百度坐标转换
        $latlon     = $indexmodel->coordinate_switchf($lat,$lng);
        $lng        = $latlon['Longitude'];
        $lat        = $latlon['Latitude'];
        $datas = Db::name('user')->where('id',$user_id)->find();
        if ($datas['odm_members'] == 0) {
            $this->where('c.store_type','in',[2,3]);
        }
        $data = $this
            ->alias('c')
            ->field('c.id,l.store_name,c.distance,c.longitude,c.latitude,c.business_id as b_id')
            ->join('store_lang l','l.store_id=c.id','LEFT')
            ->where('l.lang_id',$lang_id)
            ->where('c.is_open',1)
            ->where('c.store_cate_id',$countryId)
            ->select()
            ->each(function($item)use($lng,$lat,$indexmodel){
                $s = $indexmodel->getdistance($lng, $lat, $item['longitude'], $item['latitude']);
                $item['dis']   = number_format(($s / 1000), 2, '.', '');
                return $item;
            });
        $gData = Db::name('store_goods')
            ->field('store_id,id')
            ->where('goods_id',$store_good_id)
            ->where('mark',1)
            ->where('is_on_sale',1)
            ->select();
        $temp = [];
        foreach ($gData as $value) {
            //查看有没有重复项
            if (isset($temp[$value['store_id']])) {
                //有：销毁
                unset($value['store_id']);
            } else {
                $temp[$value['store_id']] = $value;
            }
        }
        foreach ($data as $k => $v) {
            foreach ($temp as $k1 => $v1) {
                if ($v['id'] == $v1['store_id']) {
                    $arr[$k1]['id'] = $v['id'];
                    $arr[$k1]['store_name'] = $v['store_name'];
                    $arr[$k1]['distance'] = $v['distance'];
                    $arr[$k1]['latitude'] = $v['latitude'];
                    $arr[$k1]['longitude'] = $v['longitude'];
                    $arr[$k1]['store_goods_id'] = $v1['id'];
                    $arr[$k1]['dis'] = $v['dis'];
                }
            }
        }
        foreach ($arr as $key => $val) {
            if ($val['distance'] < $val['dis']) {
                unset($arr[$key]);
            }
        }
        $result = $arr?$arr:[];
        $arrSort = array();
        foreach ($result AS $uniqid => $row) {
            foreach ($row AS $key => $value) {
                $arrSort[$key][$uniqid] = $value;
            }
        }
        if($result){
            array_multisort($arrSort['dis'], SORT_ASC, $result);
        }else{
            $result = [];
        }
        return $result;

    }

    /**
     * 用户收藏
     * @author  liy
     * @date    2019-12-13
     */
    public function collection($style='',$good_id='',$store_good_id='',$store_id='',$user_id='',$style='',$type='')
    {
        //收藏 1为商品收藏
        if ($style == 1){
            if (empty($store_good_id)){
                throw new BaseException(['msg' => '请传递店铺商品id!']);
            }
            if (empty($good_id)){
                throw new BaseException(['msg' => '请传递商品id!']);
            }
            if(empty($type)){
                $data['user_id']   = $user_id;
                $data['store_id']  = $store_id;
                $data['adds_time'] = time();
                $data['good_id']   = $good_id;
                $data['store_good_id'] = $store_good_id;
                $this->startTrans();
                try {
                    Db::name('user_collection')->insert($data);
                    $this->commit();
                    return '收藏成功';
                } catch (\Exception $e) {
                    $this->rollback();
                    throw $e;
                }
            }else{
                $this->startTrans();
                try {
                    Db::name('user_collection')
                        ->where(['store_id'=>$store_id,'user_id'=>$user_id,'good_id'=>$good_id,'store_good_id'=>$store_good_id])
                        ->delete();
                    $this->commit();
                    return '取消收藏';
                } catch (\Exception $e) {
                    $this->rollback();
                    throw $e;
                }
            }
        }elseif($style == 2){
           // 2为店铺收藏
            if (empty($store_id)){
                throw new BaseException(['msg' => '请传递店铺id!']);
            }
            if(empty($type)){
                $data['user_id']   = $user_id;
                $data['store_id']  = $store_id;
                $data['add_time']  = time();
                $this->startTrans();
                try {
                    Db::name('user_store')->insert($data);
                    $this->commit();
                    return '收藏成功';
                } catch (\Exception $e) {
                    $this->rollback();
                    throw $e;
                }
            }else{
                $this->startTrans();
                try {
                    Db::name('user_store')
                        ->where(['store_id'=>$store_id,'user_id'=>$user_id])
                        ->delete();
                    $this->commit();
                    return '取消收藏';
                } catch (\Exception $e) {
                    $this->rollback();
                    throw $e;
                }
            }
        }
    }

}
