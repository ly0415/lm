<?php

namespace app\common\model;

use Think\Db;
/**
 * 店铺商品模型
 * @author  luffy
 * @date    2019-08-2
 */
class StoreGoods extends BaseModel
{
    protected $name = 'store_goods';
    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'add_time';
    protected $updateTime = false;

    public $is_on_sale = [1=>'上架',2=>'下架',3=>'强制下架'];

    /**
     * 关联店铺
     * author   luffy
     * date     2019-07-25
     */
    public function store()
    {
        return $this->belongsTo('Store', 'store_id', 'id')->bind(['store_name']);
    }


    /**
     * 关联业务类型
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-28
     * Time: 11:56
     */
    public function roomType(){
        return $this->belongsTo('RoomType','room_id','id');
    }

    /**
     * 关联业务类型
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-28
     * Time: 11:56
     */
    public function business(){
        return $this->belongsTo('Business','room_id','id');
    }

    /**
     * 是否上架
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-02
     * Time: 18:33
     */
    public function getIsOnSaleAttr($value){
        return ['text'=>$this->is_on_sale[$value],'value'=>$value];
    }

    /**
     * 关联原始商品
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-28
     * Time: 12:01
     */
    public function goods(){
        return $this->belongsTo('Goods','goods_id','goods_id')->field('auxiliary_type,goods_id');
    }

    /**
     * 关联店铺商品规格价格
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-20
     * Time: 19:22
     */
    public function specPrice(){
        return $this->hasMany('StoreGoodsSpecPrice','store_goods_id','id')->field('id sp_id,`goods_id`,`key_name`,`price`,`key`,`goods_storage`,`store_goods_id`,`bar_code`');
    }

    //关联组合商品
    public function storeGoodsJoint(){
        return $this->hasMany('StoreGoodsJoint','store_goods_id','id')->where('mark','=',1);
    }

    /**
     * 添加时间
     * author fup
     * date 2019-07-13
     */
    public function getAddTimeAttr($value){
        return date('Y-m-d H:i',$value);
    }

    /**
     * 店铺商品详情
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-27
     * Time: 10:37
     */
    public static function detail($storeGoodsId){
        $model = new static;
        return $model->where('id','=',$storeGoodsId)
            ->find();
    }

    /**
     * 获取规格信息
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-09
     * Time: 14:20
     */
    public function getDetailByBarCode($bcode,$store_id = null){
        $model = new static;
        return $model::get(['bar_code' => $bcode,'store_id' => $store_id ? : STORE_ID,'mark'=>1]);
    }


    /**
     * 获取商品的价格和库存
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-26
     * Time: 15:52
     */
    public static function getSpecPriceStock($store_goods_id){
        return self::field('goods_storage as stock,shop_price as price')
            ->where('id','=',$store_goods_id)
            ->where('mark','=',1)
            ->find();
    }

    /**
     * 更新商品库存
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-29
     * Time: 17:02
     */
    public function updateStockSales($goodsList = []){
        // 批量更新商品规格：库存
        foreach ($goodsList as $goods){
            $where = [];
            if($goods['deduction'] == 1){
                $where = [
                    'goods_id' => $goods['good_id'],
                    'store_id' => \app\store\model\Store::getAdminStoreId(),
                    'mark' => 1
                ];
                $goodsSpecSave = [
                    'goods_storage' => ['dec', $goods['goods_num']]
                ];
                (new StoreGoods)->where($where)->update($goodsSpecSave);
            }elseif($goods['deduction'] == 2){
                if(!empty($goods['spec_key'])){
                    $where['key'] = ['IN',$this->formatSpec($goods['spec_key'])];
                    $where['store_goods_id'] = $goods['goods_id'];
//                    'stock' => ['dec', $goods['goods_num']]
                    $goodsSpecSave = [
                        'goods_storage' => ['dec', $goods['goods_num']]
                    ];
                    (new StoreGoodsSpecPrice())->where($where)->update($goodsSpecSave);

                }else{
                    $where['id'] = $goods['goods_id'];
                    $goodsSpecSave = [
                        'goods_storage' => ['dec', $goods['goods_num']]
                    ];
                    (new StoreGoods)->where($where)->update($goodsSpecSave);
                }
            }

        }
    }

    /**
     * 根据商品id集获取商品列表
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-02
     * Time: 17:29
     */
    public function getListByIds($goodsIds, $status = null)
    {
        // 筛选条件
        $filter = ['id' => ['in', $goodsIds]];
        $status > 0 && $filter['is_on_sale'] = $status;
        if (!empty($goodsIds)) {
            $this->orderRaw('field(id, ' . implode(',', $goodsIds) . ')');

        }
        // 获取商品列表数据
        $data = $this->with(['business','goods','StoreGoodsJoint.storeGoods'])
            ->where($filter)
            ->select();

        return $data;
    }

    /**
     * 格式化规格
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-02
     * Time: 18:19
     */
    public function formatSpec($specKey){
        $spec_arr = [];
        if ($specKey) {
            $key_arr = explode('_', $specKey);
            $key_pailie = arrangement($key_arr, count($key_arr));
            foreach ($key_pailie as $v) {
                $spec_arr[] = implode('_', $v);
            }
        }
        return $spec_arr;
    }

    /**
     * 判断商品有无规格
     * @author  luffy
     * @date    2019-09-26
     */
    public function isExistSpec($store_goods_id){
        return (!empty(Db::name('store_goods_spec_price')->field('key')
            ->where(['store_goods_id'=>$store_goods_id])->select()->toArray()) ? TRUE : FALSE);
    }
}
