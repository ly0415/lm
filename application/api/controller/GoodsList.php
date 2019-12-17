<?php

namespace app\api\controller;

use app\api\model\GoodsList   as GoodsListModel;

/**
 * 商品详情
 * @author  liy
 * @date    2019-12-12
 */
class GoodsList extends Controller{

    /**
     * 店铺是否营业
     * @author  liy
     * @date    2019-12-12
     */
    public function storeIsOpen($store_id='')
    {
        if(empty($store_id)){
            return $this->renderError('参数错误！');
        }
        $model = new GoodsListModel;
        $list = $model->storeIsOpen($store_id);
        return $this->renderSuccess( $list);

    }

    /**
     * 为您推荐的商品详情
     * @author  liy
     * @date    2019-12-12
     * $source='',$cid='',$goods_key='',$auxiliary='',$latlon='',$lang_id='',$store_id='',$goods_id='',$gid=''
     */
    public function tuiInfo($store_id='',$goods_id='')
    {
        $store_id = !empty($store_id)?$store_id:58;
        $lang_id = 29;
//        if(empty($goods_id)){
//            return $this->renderError('参数错误！');
//        }
        $model = new GoodsListModel;
        //$source,$cid,$goods_key,$auxiliary,$latlon,$lang_id,$store_id,$goods_id,$gid
        $list = $model->tuiInfo($lang_id,$store_id,$goods_id);
        return $this->renderSuccess( $list);

    }

    /**
     * 获取配送店铺
     * @author  liy
     * @date    2019-12-13
     */
    public function getStore($latlon='',$store_good_id='',$user_id='')
    {
//        $user_id = 21;$store_good_id=510;
        if(empty($user_id)){
            return $this->renderError('参数错误！');
        }
        $lang_id = 29;
        $countryId = 17;
        $model = new GoodsListModel;
        $list = $model->getStore($latlon,$store_good_id,$user_id,$lang_id,$countryId);
        return $this->renderSuccess( $list);

    }

    /**
     * 用户收藏
     * @author  liy
     * @date    2019-12-13
     */
    public function collection($style='',$good_id='',$store_good_id='',$store_id='',$user_id='',$style='',$type='')
    {
        if(empty($style)){
            return $this->renderError('请传递收藏类型！');
        }
        $model = new GoodsListModel;
        $list = $model->collection($style,$good_id,$store_good_id,$store_id,$user_id,$style,$type);
        return $this->renderSuccess( $list);

    }





}