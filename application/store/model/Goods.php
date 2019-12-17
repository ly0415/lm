<?php

namespace app\store\model;

use app\common\model\Goods as GoodsModel;
use app\common\model\GoodsCategory as GoodsCategoryModel;

/**
 * 商品模型
 * Class Goods
 * @package app\store\model
 */
class Goods extends GoodsModel
{

    /**
     * 获取原始商品
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-20
     * Time: 15:44
     */
    //$is_list = false, $business_id = 0, $category_id = 0, $search = '', $status = null, $goods_sn = '',$attributes = 0
    public function getList($is_list = false, $business_id = 0, $category_id = 0, $search = '', $status = null,$goods_sn = '')
    {
        // 筛选条件
        $filter = ['is_on_sale'=>1];
        $is_list && $filter['goods_id'] = ['NOT IN', StoreGoods::getHasStoreGoodsId(STORE_ID)];

        if($category_id > 0){
            $categoryIds    = GoodsCategoryModel::getSubCategoryId($category_id);
            $categoryIds    = (!empty($categoryIds) ? implode(',', $categoryIds) : [-1]);
            $categoryIds    && $filter['cat_id'] = ['IN', $categoryIds];
        }
        !empty($search)     && $filter['goods_name'] = ['like', '%' . trim($search) . '%'];
        !empty($goods_sn)   && $filter['goods_sn'] = ['like', '%' . trim($goods_sn) . '%'];
        // 执行查询
        $list = $this->with(['specPrice'])
            ->where($filter)
            ->order(['goods_id'=>'DESC'])
            ->paginate(15, false, ['query' => \request()->request()])
            ->each(function ($value){
                return $this->toSwitch($value);
            });
        return $list;
    }

    /**
     * 数据转换
     * @author  luffy
     * @date    2019-08-27
     */
    public function toSwitch($value){
        if(isset($value['is_on_sale']))   $value['format_is_on_sale']   = ($value['is_on_sale'] == 1 ? '上架' : '下架');
        if(isset($value['cat_id']))       $value['format_category']     = GoodsCategoryModel::getCacheAll()[$value['cat_id']]['name_string'];
        return $value;
    }

    /**
     * 添加商品
     * @param array $data
     * @return bool
     * @throws \think\exception\PDOException
     */
    public function add(array $data)
    {
        if (!isset($data['goods_images']) || empty($data['goods_images'])) {
            $this->error = '请上传商品图片';
            return false;
        }
        $data['goods_sn'] = $data['goods_sn'] ? $data['goods_sn'] : 'AM' . str_pad((int)$this->getLastGoodsId() + 1,7,0,STR_PAD_LEFT );
        $data['goods_images'] = implode(',',$data['goods_images']);
        $data['content'] = isset($data['content']) ? $data['content'] : '';
        $data['create_user'] = session('yoshop_store.user')['store_user_id'];
        $data['wxapp_id'] =  self::$wxapp_id;
        // 开启事务
        $this->startTrans();
        try {
            // 添加商品
            $this->allowField(true)->save($data);
            // 商品规格价格
            $this->addGoodsSpec($data);
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
     * 添加商品图片
     * @param $images
     * @return int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    private function addGoodsImages($images)
    {
        $this->image()->delete();
        $data = array_map(function ($image_id) {
            return [
                'image_id' => $image_id,
                'wxapp_id' => self::$wxapp_id
            ];
        }, $images);
        return $this->image()->saveAll($data);
    }

    /**
     * 编辑商品
     * @param $data
     * @return bool
     * @throws \think\exception\PDOException
     */
    public function edit($data)
    {
        if (!isset($data['images']) || empty($data['images'])) {
            $this->error = '请上传商品图片';
            return false;
        }
        $data['goods_images'] = implode(',',$data['goods_images']);
        $data['content'] = isset($data['content']) ? $data['content'] : '';
        $data['wxapp_id'] = self::$wxapp_id;
        // 开启事务
        $this->startTrans();
        try {
            // 保存商品
            $this->allowField(true)->save($data);
            // 商品规格
            $this->addGoodsSpec($data, true);
            // 商品图片
            $this->addGoodsImages($data['images']);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 添加商品规格
     * @param $data
     * @param $isUpdate
     * @throws \Exception
     */
    private function addGoodsSpec(&$data, $isUpdate = false)
    {
        // 更新模式: 先删除所有规格
        $model = new GoodsSku;
        $isUpdate && $model->removeAll($this['goods_id']);
        // 添加规格数据
        if ($data['spec_type'] == '10') {
            // 单规格
            $this->sku()->save($data['sku']);
        } else if ($data['spec_type'] == '20') {
            // 添加商品与规格关系记录
            $model->addGoodsSpecRel($this['goods_id'], $data['spec_many']['spec_attr']);
            // 添加商品sku
            $model->addSkuList($this['goods_id'], $data['spec_many']['spec_list']);
        }
    }

    /**
     * 修改商品状态
     * @param $state
     * @return false|int
     */
    public function setStatus($state)
    {
        return $this->save(['is_shelf' => $state ? 1 : 2]) !== false;
    }

    /**
     * 软删除
     * @return false|int
     */
    public function setDelete()
    {
        return $this->save(['mark' => 0]);
    }

    /**
     * 获取当前商品总数
     * @param array $where
     * @return int|string
     */
    public function getGoodsTotal($where = [])
    {
        !empty($where) && $this->where($where);
        return $this->count();
    }

    /**
     * 获取当前商品表最后id
     * @param array $where
     * @return int|string
     */
    public function getLastGoodsId($where = [])
    {
        !empty($where) && $this->where($where);
        return $this->order('id','DESC')
            ->value('id') ? : 0;
    }

    /**
     * 通过id获取商品
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-20
     * Time: 15:04
     */
    public static function getGoodsById($data){
        return self::with('specPrice')
            ->where('goods_sn','IN',$data)
            ->field('attributes,goods_id,cat_id,goods_sn,goods_name,goods_storage,goods_type,
        spec_type,brand_id,brand_name,shop_price,market_price,cost_price,goods_remark,goods_content,
        original_img,is_free_shipping,is_free_shipping,is_recommend,is_new,is_hot,suppliers_id,
        spu,sku,shipping_area_ids,on_time,style_id,room_id,lang_id,keywords,delivery_fee,deduction')
            ->select()->each(function ($item){
                $item['store_id'] = STORE_ID;
                return $item;
            })->toArray();
    }


    /**
     * 校验goods_sn是否存在
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-11-29
     * Time: 17:41
     */
    public static function checkGoodsSn($goodsSn){
        if(self::get(['goods_sn'=>$goodsSn])){
            return false;
        }
        return true;
    }

}
