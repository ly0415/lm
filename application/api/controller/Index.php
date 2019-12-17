<?php

namespace app\api\controller;

use app\api\model\Index   as IndexModel;

/**
 * 小程序首页
 * @author  liy
 * @date    2019-12-11
 */
class Index extends Controller{

    /**
     * 小程序首页
     * @author  liy
     * @date    2019-12-11
     */
    public function Index($buss_id='')
    {
        $model = new IndexModel;
        $store_id = 58;
        $lang_id = 29;
        $list = $model->getList($buss_id,$store_id,$lang_id);
        return $this->renderSuccess( $list);

    }

    /**
     * 附近店铺接口
     * @author ly
     * @date 2019-12-11
     */
    public function getStore($buss_id='',$latlon='',$user_id=''){
        $model = new IndexModel;
        $lang_id = 29;
        $list = $model->getStore($buss_id,$latlon,$user_id,$lang_id);
        return $this->renderSuccess( $list);

    }

    /**
     * 获取地址
     * @author ly
     * @date 2019-12-11
     */
    public function myAddress($user_id='',$latlon=''){
        $model = new IndexModel;
        $store_id = 58;
        $lang_id = 29;
        $list = $model->myAddress($user_id,$latlon,$store_id,$lang_id);
        return $this->renderSuccess( $list);

    }







}