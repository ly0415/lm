<?php

namespace app\store\controller\store;

use app\store\controller\Controller;
use app\store\model\Coupon;
use app\store\model\UserCoupon;

/**
 * 商家用户控制器
 * Class StoreUser
 * @package app\store\controller
 */
class Point extends Controller
{
    /**
     * 用户列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {

//            dump($this->request->param());die;
//        $this->fuzzyCond($where,'real_name');
//        $this->queryCond($where,'store_id');
			$list = [];
        // $list = (new UserCoupon())->getList($this->request->param());
//        dump($list->toArray());die;
$this->assign('list',$list);
      return $this->fetch('index');
    }

    /**
     * 用户列表导出
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function export(){
        return (new UserCoupon())->exportList($this->request->param());
    }
}
