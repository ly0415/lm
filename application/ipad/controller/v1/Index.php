<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-04
 * Time: 下午 2:57
 */

namespace app\ipad\controller\v1;


use app\ipad\model\StoreShop;

class Index extends Api
{
    public function index(){
//            dump($this->user);die;
        $model = new StoreShop();                                       $store = $model->getInfo($this->user['user']['store_id']);
//        dump($store->toArray());die;
        $this->assign(compact('store'));
        return view();
    }
}