<?php

namespace app\store\controller;

use app\store\model\Store as StoreModel;

/**
 * 后台首页控制管理
 * @author  luffy
 * @date    2019-07-22
 */
class Index extends Controller{

    /**
     * 后台首页
     * @author  luffy
     * @date    2019-07-22
     */
    public function index($store_id = ''){
        $StoreModel = new StoreModel;

        if(empty($store_id)){
            if( T_GENERAL ){
                $store_id   = STORE_ID;
            } else {
                $store_id   = SELECT_STORE_ID;
            }
        }

        // 当前用户菜单url
        $menus = $this->menus();
        $url = current(array_values($menus))['index'];
        if ($url !== 'index/index') {
            $this->redirect($url);
        }
        $storeList = [];
        if(IS_ADMIN){
            //门店列表
            $storeList  = $StoreModel -> getStoreList(TRUE, BUSINESS_ID);
        }
        return $this->fetch('index', ['data' => $StoreModel->getHomeData($store_id),'storeList' => $storeList]);
    }

}