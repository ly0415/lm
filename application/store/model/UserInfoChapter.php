<?php

namespace app\store\model;

use app\common\model\UserInfoChapter as UserInfoChapterModel;

/**
 * 画像详细信息
 * @author  fup
 * @date    2019-09-24
 */
class UserInfoChapter extends UserInfoChapterModel{

    /**
     * 添加用户画像
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-24
     * Time: 20:32
     */
    public function add($info){
        $pro = false;
        $info['persons'] = 1;
        if(!isset($info['order_sn']) || empty($info['order_sn'])){
            $this->error = '订单号不存在';
            return false;
        }
        foreach ($info['tag_attr'] as $item){
            if(isset($item['tag_items']) && !empty($item['tag_items'])){
                $pro = true;
                break;
            }
        }
        if(!$pro){
            $this->error = '请选择标签';
            return false;
        }
        $this->startTrans();
        try {
            // 添加画像
            $this->allowField(true)->save($info);
            // 商品画像详细信息
            $this->addUserTag($info);
            // 商品图片
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }

    }

    /**
     * 详情
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-24
     * Time: 20:55
     */
    public static function detail($order_sn){
        return self::get(['order_sn'=>$order_sn],'userInfo');
    }

    /**
     * 编辑
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-24
     * Time: 20:55
     */
    public function edit($data){
        $pro = false;
        if(!isset($data['order_sn']) || empty($data['order_sn'])){
            $this->error = '订单号不存在';
            return false;
        }
        foreach ($data['tag_attr'] as $item){
            if(isset($item['tag_items']) && !empty($item['tag_items'])){
                $pro = true;
                break;
            }
        }
        if(!$pro){
            $this->error = '请选择标签';
            return false;
        }
        $this->startTrans();
        try {
            // 添加画像
            $this->setInc('persons');
            // 商品画像详细信息
            $this->addUserTag($data);
            // 商品图片
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 添加画像详细信息
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-24
     * Time: 21:07
     */
    public function addUserTag($data){

        $model = new UserInfo();
        $model->add($this['order_sn'],$data['tag_attr']);

    }

    /**
     * 校验数据
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-24
     * Time: 21:37
     */
    public function checkFormData($info){

        return false;
    }
}