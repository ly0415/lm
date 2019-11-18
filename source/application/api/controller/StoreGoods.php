<?php

namespace app\api\controller;

use app\api\model\StoreGoods as StoreGoodsModel;
use app\api\model\StoreGoodsSpecPrice;
use app\api\model\GoodsSpecItem;
use app\api\model\Store as StoreModel;

/**
 * 商品控制器
 * Class Goods
 * @package app\api\controller
 */
class StoreGoods extends Controller{

    /**
     * 扫码加入购物车
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-29
     * Time: 13:58
     */
    public function add_cart($bcode = null,$store_id = null){
        if(!$bcode || !$store_id)return $this->renderError('缺少必要参数');
        $sModel = new StoreGoodsSpecPrice();
        $model = new StoreGoodsModel();
        if($data = $sModel->getDetailByBarCode($bcode,$store_id)){
            if($data['is_on_sale'] != 1){
                return $this->renderError('该商品未上架');
            }
            $data['store_goods_ids'] = $data['store_goods_id'];
            $data['key_names'] = implode(':',GoodsSpecItem::geyKeyName(explode('_',$data['key'])));
            $data['stockPrice'] = $this->ajax_goods_price_stock($data['store_goods_id'],$data['key'],$store_id,true);
            if(!isset($data['stockPrice']['stock']) || $data['stockPrice']['stock'] <= 0){
                return $this->renderError('库存不足');
            }
            return $this->renderSuccess($data);
        }
        if($info = $model->getDetailByBarCode($bcode,$store_id)){
            if($info['is_on_sale']['value'] != 1){
                return $this->renderError('该商品未上架');
            }
            $info['store_goods_ids'] = $info['id'];

            $info['key_names'] = '无规格属性';
            $info['stockPrice'] = $this->ajax_goods_price_stock($info['id'],null,$store_id,true);
            if(!isset($info['stockPrice']['stock']) || $info['stockPrice']['stock'] <= 0){
                return $this->renderError('库存不足');
            }
            return $this->renderSuccess($info);
        }

        return $this->renderError('商品不存在');
    }

    /**
     * 获取规格对应价格
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-22
     * Time: 16:14
     */
    public function ajax_goods_price_stock($store_goods_id = 0,$key = null,$store_id = null,$return_array = false){
        $spec_arr = [];
        if ($key) {
            $key_arr = explode('_', $key);
            $key_pailie = arrangement($key_arr, count($key_arr));
            foreach ($key_pailie as $v) {
                $spec_arr[] = implode('_', $v);
            }
        }
        $store_discount = StoreModel::getStoreDiscount($store_id);
        $goodsPriceStock = StoreGoodsSpecPrice::getSpecPriceStock($store_goods_id,$spec_arr);
        $goodsPriceStock['price'] = number_format($goodsPriceStock['price'] * $store_discount,'2','.','');
        if($return_array){
            return $goodsPriceStock;
        }
        return $this->renderSuccess($goodsPriceStock);
    }

    /**
     * 门店商品列表
     * @author  : luffy
     * @date    : 2019-11-12
     */
    public function lists($store_id = 0, $rtid = 0, $type = 1, $page = 1, $search_name = '', $type_id = 0){
        if(empty($store_id)){
            return $this->renderError('参数错误！');
        }
        $model      = new StoreGoodsModel;
        $list       = $model->getStoreGoods($store_id, $rtid, $type, $page, $search_name, $type_id);
        $this->setData(['goods'=>array_values($list['goods']), 'bus_type'=>array_values($list['room']), 'type'=>$type], '1', '');      //用老的方法
    }

    /**
     * 数据封装
     * @author  luffy
     * @date    2018-08-14
     * @param $status 表示返回数据状态
     * @param $message 对返回状态说明
     * @param $info 返回数据信息
     */
    public function setData($info = array(), $status = 'success', $message = 'ok'){
        $data = array(
            'status'    => $status,
            'message'   => $message,
            'info'      => $info,
        );
        echo json_encode($data);
        exit();
    }
}
