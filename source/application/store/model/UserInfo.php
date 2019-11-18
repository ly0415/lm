<?php

namespace app\store\model;

use app\common\model\UserInfo as UserInfoModel;

/**
 * 画像详细信息
 * @author  fup
 * @date    2019-09-24
 */
class UserInfo extends UserInfoModel{

    /**
     * 画像信息
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-29
     * Time: 10:12
     */
    public static function getList($order_sn){
        return self::all(['order_sn'=>$order_sn]);
    }

    /**
     * 添加用户画像
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-24
     * Time: 20:30
     */
    public function add($data){
        $pro = false;
        foreach ($data['content'] as $item){
            if(isset($item['tag_items']) && !empty($item['tag_items'])){
                $pro = true;
                break;
            }
        }
        if(!$pro){
            $this->error = '请选择标签';
            return false;
        }
        $data['add_user'] = USER_ID;
        return $this->allowField(true)->save($data);

    }

    /**
     * 获取画像信息
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-29
     * Time: 16:26
     */
    public static function detail($id){
        return self::get($id);
    }

    /**
     * 编辑画像标题
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-29
     * Time: 16:28
     */
    public function edit($title){
        $this->title = $title;
        return $this->save();
    }


}