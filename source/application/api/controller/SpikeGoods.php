<?php

namespace app\api\controller;
use app\api\model\SpikeGoods as SpikeGoodsModel;
use app\api\model\SpikeActivity as SpikeActivityModel;
use app\api\model\StoreGoodsSpecPrice;

/**
 * 秒杀活动商品控制器
 * Class SpikeGoods
 * @package app\api\controller
 */
class SpikeGoods extends Controller
{
    /**
     * 获取秒杀商品
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-14
     * Time: 11:22
     */
    public function getList($time_point = 0){
//        dump($time_point);die;
        $model = new SpikeGoodsModel();
        $time_point = $time_point ? $time_point : SpikeGoodsModel::getDefaultTimePoint();
//        dump($time_point);die;
        $list = $model->getList($time_point);
        return $this->renderSuccess($list);
    }

    /**
     * 获取秒杀活动商品时间段
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-14
     * Time: 13:39
     */
    public function getTimePoint(){
        $now = SpikeGoodsModel::getDefaultTimePoint();
        $timePoint = SpikeGoodsModel::getTimePoint($now);
        return $this->renderSuccess($timePoint);
    }


    /**
     * 校验库存
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-17
     * Time: 15:04
     */
    public function getBuyNums($activityGoodsId = null,$num = 1,$activityId = null,$userId = null,$source = 1){
        $spikeModel = new SpikeActivityModel;
        if(!$spikeModel->checkStatus($activityId)){
            return $this->renderError($spikeModel->getError() ? : '活动异常');
        }
        if(!$model = SpikeGoodsModel::detail($activityGoodsId)){
            return $this->renderError('商品不存在');
        }
        if(!$model->checkByNum($num,$userId,$source)){
            return $this->renderError($model->getError() ? : '库存不足');
        }
        return $this->renderSuccess();
    }


}
