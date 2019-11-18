<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-17
 * Time: 下午 5:18
 */

namespace app\xcx\controller;

use app\xcx\model\SpikeActivity as SpikeActivityModel;

class SpikeActivity extends Base
{
    /**
     * 秒杀活动
     * @return \think\response\Json
     */
    public function getSpikeList($time=''){
        $list = SpikeActivityModel::getList($time);
    }


}