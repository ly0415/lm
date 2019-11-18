<?php

class OrderRefundMod extends BaseMod
{
    public function __construct()
    {
        parent::__construct('order_refund');
    }
    /**
     * 获取市场价
     * @param $store_goods_id
     * @return mixed
     */
    public function getMarketPrice($store_goods_id)
    {
        $sql = "SELECT market_price FROM bs_store_goods WHERE `id` =".$store_goods_id;
//        echo '<pre>';print_r($sql);die;
        $storeGoodsMod = &m('storeGoods');
        $res = $storeGoodsMod->querySql($sql);

        return $res[0]['market_price'];
    }

    /**
     * base64位加密图片转化
     * @param $base64
     * @return bool|string
     */
    public function base64_upload($base64) {
        $base64_image = str_replace(' ', '+', $base64);
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image, $result)){
            //匹配成功
            if($result[2] == 'jpeg'){
                $image_name = uniqid().'.jpg';
            }else{
                $image_name = uniqid().'.'.$result[2];
            }
            $savePath = "upload/images/refund/phone";
            if (!file_exists($savePath)){
                @mkdir($savePath, 0777, true);
                @chmod($savePath, 0777);
                @exec("chmod 777 {$savePath}");
            }
            $image_file = $savePath . '/' . $image_name;

            //服务器文件存储路径
            if (file_put_contents($image_file, base64_decode(str_replace($result[1], '', $base64_image)))){
                return $image_file;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * 退款总金额
     * @param $order_id
     * @return mixed
     */
    public function returnAmount($order_id)
    {
        $sql = "SELECT refund_amount FROM bs_order_refund WHERE order_id =" .$order_id;
        $res = $this->querySql($sql);
        return $res[0]['refund_amount'];
    }
}