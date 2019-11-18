<?php

namespace app\common\model;

/**
 * 商品模型
 * Class Goods
 * @package app\common\model
 */
class Goods extends BaseModel
{
    protected $name = 'goods';

    //库存扣除方式
    public $deduction = [
        '1'     => '同步扣除',
        '2'     => '分开扣除'
    ];

    //上下架
    public $is_on_sale = [
        '1'     => '上架',
        '2'     => '下架'
    ];

    /**
     * 获取商品列表
     * @param array $data
     * @return bool
     * @throws \think\exception\PDOException
     */
    public function getGoodsList(){
        return $this->where(['goods_id'=>1])->find();
    }

    /**
     * 关联商品分类表
     * @return \think\model\relation\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo('GoodsCategory','cat_id','id');
    }
    /**
     * 关联图片库表
     * @return \think\model\relation\BelongsTo
     */
    public function image()
    {
        return $this->belongsTo('UploadFile','goods_original_image','file_id');
    }


    /**
     * 关联商品规格表
     * @return \think\model\relation\HasMany
     */
    public function sku()
    {
        return $this->hasMany('GoodsSku')->order(['goods_sku_id' => 'asc']);
    }

    /**
     * 关联商品规格关系表
     * @return \think\model\relation\BelongsToMany
     */
    public function specRel()
    {
        return $this->belongsToMany('SpecValue', 'GoodsSpecRel');
    }

    /**
     * 关联运费模板表
     * @return \think\model\relation\BelongsTo
     */
    public function delivery()
    {
        return $this->BelongsTo('Delivery');
    }

    /**
     * 关联原始商品规格价格
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-20
     * Time: 19:22
     */
    public function specPrice(){
        return $this->hasMany('GoodsSpecPrice','goods_id','goods_id')->field('`goods_id`,`key_name`,`price`,`key`,`goods_storage`');
    }

    /**
     * 关联订单评价表
     * @return \think\model\relation\HasMany
     */
    public function commentData()
    {
        return $this->hasMany('Comment');
    }

    /**
     * 计费方式
     * @param $value
     * @return mixed
     */
    public function getIsShelfAttr($value)
    {
        $status = [1 => '上架', 2 => '下架'];
        return ['text' => $status[$value], 'value' => $value];
    }


    /**
     * 根据商品id集获取商品列表
     * @param array $goodsIds
     * @param null $status
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getListByIds($goodsIds, $status = null)
    {
        // 筛选条件
        $filter = ['goods_id' => ['in', $goodsIds]];
        $status > 0 && $filter['goods_status'] = $status;
        if (!empty($goodsIds)) {
            $this->orderRaw('field(goods_id, ' . implode(',', $goodsIds) . ')');
        }
        // 获取商品列表数据
        $data = $this->with(['category', 'image.file', 'sku', 'spec_rel.spec', 'delivery.rule'])
            ->where($filter)
            ->select();
        if ($data->isEmpty()) return $data;
        // 格式化数据
        foreach ($data as &$item) {
            $item['goods_image'] = $item['image'][0]['file_path'];
        }
        return $data;
    }

    /**
     * 商品多规格信息
     * @param \think\Collection $spec_rel
     * @param \think\Collection $skuData
     * @return array
     */
    public function getManySpecData($spec_rel, $skuData)
    {
        // spec_attr
        $specAttrData = [];
        foreach ($spec_rel->toArray() as $item) {
            if (!isset($specAttrData[$item['spec_id']])) {
                $specAttrData[$item['spec_id']] = [
                    'group_id' => $item['spec']['spec_id'],
                    'group_name' => $item['spec']['spec_name'],
                    'spec_items' => [],
                ];
            }
            $specAttrData[$item['spec_id']]['spec_items'][] = [
                'item_id' => $item['spec_value_id'],
                'spec_value' => $item['spec_value'],
            ];
        }
        // spec_list
        $specListData = [];
        foreach ($skuData->toArray() as $item) {
            $image = (isset($item['image']) && !empty($item['image'])) ? $item['image'] : ['file_id' => 0, 'file_path' => ''];
            $specListData[] = [
                'goods_sku_id' => $item['goods_sku_id'],
                'spec_sku_id' => $item['spec_sku_id'],
                'rows' => [],
                'form' => [
                    'image_id' => $image['file_id'],
                    'image_path' => $image['file_path'],
                    'goods_no' => $item['goods_no'],
                    'goods_price' => $item['goods_price'],
                    'goods_weight' => $item['goods_weight'],
                    'line_price' => $item['line_price'],
                    'stock_num' => $item['stock_num'],
                ],
            ];
        }
        return ['spec_attr' => array_values($specAttrData), 'spec_list' => $specListData];
    }

    /**
     * 多规格表格数据
     * @param $goods
     * @return array
     */
    public function getManySpecTable(&$goods)
    {
        $specData = $this->getManySpecData($goods['spec_rel'], $goods['sku']);
        $totalRow = count($specData['spec_list']);
        foreach ($specData['spec_list'] as $i => &$sku) {
            $rowData = [];
            $rowCount = 1;
            foreach ($specData['spec_attr'] as $attr) {
                $skuValues = $attr['spec_items'];
                $rowCount *= count($skuValues);
                $anInterBankNum = ($totalRow / $rowCount);
                $point = (($i / $anInterBankNum) % count($skuValues));
                if (0 === ($i % $anInterBankNum)) {
                    $rowData[] = [
                        'rowspan' => $anInterBankNum,
                        'item_id' => $skuValues[$point]['item_id'],
                        'spec_value' => $skuValues[$point]['spec_value']
                    ];
                }
            }
            $sku['rows'] = $rowData;
        }
        return $specData;
    }

    /**
     * 获取商品详情
     * @param $goods_id
     * @return static|false|\PDOStatement|string|\think\Model
     */
    public static function detail($goods_id)
    {
        $model = new static;
        $data =  $model->with([
            'category',
            'image',
//            'image.file',
//            'sku.image',
//            'spec_rel.spec',
//            'delivery.rule',
//            'commentData' => function ($query) {
//                $query->with('user')->where(['mark' => 1, 'status' => 1])->limit(2);
//            }
        ])
//            ->withCount(['commentData' => function ($query) {
//            $query->where(['mark' => 1, 'status' => 1]);
//        }])
            ->where('id', '=', $goods_id)->find();
        $data['cate_path_id'] = (new GoodsCategory)->getParentId($data['cate_id']);
        return $data;
    }

    /**
     * 商品多规格信息
     * @param $goods_sku_id
     * @return array|bool
     */
    public function getGoodsSku($goods_sku_id)
    {
        $goodsSkuData = array_column($this['sku']->toArray(), null, 'spec_sku_id');
        if (!isset($goodsSkuData[$goods_sku_id])) {
            return false;
        }
        $goods_sku = $goodsSkuData[$goods_sku_id];
        // 多规格文字内容
        $goods_sku['goods_attr'] = '';
        if ($this['spec_type'] == 20) {
            $attrs = explode('_', $goods_sku['spec_sku_id']);
            $spec_rel = array_column($this['spec_rel']->toArray(), null, 'spec_value_id');
            foreach ($attrs as $specValueId) {
                $goods_sku['goods_attr'] .= $spec_rel[$specValueId]['spec']['spec_name'] . ':'
                    . $spec_rel[$specValueId]['spec_value'] . '; ';
            }
        }
        return $goods_sku;
    }

}
