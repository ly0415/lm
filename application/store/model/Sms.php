<?php

namespace app\store\model;

use app\common\model\Sms as SmsModel;



/**
 * 发送短信模型
 * Class Sms
 * @package app\store\model
 */
class Sms extends SmsModel
{

    /**
     * 添加短信验证码
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-12
     * Time: 12:56
     */
    public function add($data){

        return $this->allowField(true)->save($data);
    }

    /**
     * 编辑短信验证码
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-12
     * Time: 13:43
     */
    public function edit(){

        return $this->allowField(true)->save(['type'=>1]);

    }


        /**
     * 获取短信验证码
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-12
     * Time: 12:58
     */
    public static function detail($phone = null){
         $model = new self;
         return $model->where('phone','=',$phone)
             ->where('send_time','>',time() - 300)
             ->where('type','=',0)
             ->order('send_time DESC')
             ->find();
    }


}
