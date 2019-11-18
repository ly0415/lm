<?php

namespace app\store\controller;

use app\store\model\StoreConsole;
use app\common\model\StoreUser      as  StoreUserModel;

/**
 * 控制中心设置
 * Author: fup
 * Date: 2019-08-27
 */
class Setting extends Controller
{
    /**
     * 设置商品配送费折扣比例
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-27
     * Time: 13:56
     */
    public function store(){
        $list       = StoreConsole::detail(5);
        if(!$this->request->isAjax()){
            //获取当前门店打印权限人员
            $print_info = StoreConsole::get(['type'=>11, 'relation_1'=>STORE_ID]);
            //获取门店店员
            $allUser    = (new StoreUserModel)->getAllUser();
            return $this->fetch('store',compact('list', 'allUser', 'print_info'));
        }
        $model = new StoreConsole();
        if($model->addStorePercent($this->postData('store'), $list['relation_1'])){
            return $this->renderSuccess('操作成功');
        }
        return $this->renderSuccess('操作成功');
    }
 
    /**
     * 获取手机验证码（只可获取15分钟内的）
     * @author  luffy
     * @date    2019-10-13
     */
//    public function getPhone(){
//             待开发
//    }
}
