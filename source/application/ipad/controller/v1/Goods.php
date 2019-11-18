<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-05
 * Time: 上午 11:16
 */

namespace app\ipad\controller\v1;


use app\ipad\model\Category;
use app\ipad\model\StoreGoods;

class Goods extends Api
{
    public function index(){
        $model = new Category();
        $list = $model->getList($this->user['user']['store_id']);
        $this->assign(compact('list'));
        return view();
    }
}