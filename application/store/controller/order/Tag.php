<?php

namespace app\store\controller\order;

use app\store\controller\Controller;
use app\store\model\Tag as TagModel;
use app\store\model\UserInfo;

/**
 * 顾客画像
 * Class Tag
 * @package app\store\controller\order
 */
class Tag extends Controller
{


    /**
     * 添加顾客画像
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-23
     * Time: 17:39
     */
    public function add($order_sn = null){
        $model = new UserInfo();
        if(!$this->request->isAjax()){
            $tagModel = new TagModel();
            $tagList = $tagModel->getList();
            $list = UserInfo::getList($order_sn);
           return $this->fetch('add',compact('tagList','list'));
        }
        if($model->add($this->postData('tag'))){
            return $this->renderSuccess('添加成功');
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 修改画像标题
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-29
     * Time: 16:23
     */
    public function edit($id,$title){
        $model = UserInfo::detail($id);
        if($model->edit($title)){
            return $this->renderSuccess('修改成功');
        }
        return $this->renderError($model->getError() ?: '修改失败');
    }

}