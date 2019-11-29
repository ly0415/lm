<?php

namespace app\store\controller;

use Think\Db;
use app\store\model\Order as OrderModel;
use app\store\model\StoreGoods as StoreGoodsModel;
use app\store\model\GoodsCategory as GoodsCategoryModel;

/**
 * 商品管理控制器
 * Class Goods
 * @package app\store\controller
 */
class StoreGoods extends Controller
{
    /**
     * 商品列表
     * @author: luffy
     * @date  : 2019-08-01
     */
    public function index($goods_status = null, $category_id = null, $goods_name = '')
    {
        // 商品分类
        $category = GoodsCategoryModel::getCacheTree();
        // 商品列表
        $model = new StoreGoodsModel;
        $list = $model->getList(false, $category_id, $goods_name, $goods_status);
        return $this->fetch('index', compact('list', 'category'));
    }

    /**
     * 添加商品
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-20
     * Time: 15:42
     */
    public function add(){
        if ($this->request->isAjax()) {
            $model = new StoreGoodsModel;
            if ($model->add($this->postData('goods'))) {
                return $this->renderSuccess('添加成功', url('goods/index'));
            }
            return $this->renderError($model->getError() ?: '添加失败');
        }
    }

    /**
     * 商品编辑
     * @param $goods_id
     * @return array|mixed
     * @throws \think\exception\PDOException
     */
    public function edit($goods_id){
        //获取商品信息
        $model      = new StoreGoodsModel;
        $details    = $model ->getStoreDetails($goods_id);

        if ($this->request->isAjax()) {
            if ($model->edit($this->postData())) {
                return $this->renderSuccess('编辑成功', url('goods/index'));
            }
            return $this->renderError($model->getError() ?: '编辑失败');
        }

        //获取配送方式
        $OrderModel     = new OrderModel;
        $deliveryType   = $OrderModel->delivery_type;
        return $this->fetch('edit', compact('details','deliveryType'));
    }


    /**
     * 获取 规格的 笛卡尔积
     * @param $goods_id 商品 id
     * @param $spec_arr 笛卡尔积
     * @return string 返回表格字符串
     */
    public function getSpecInput($goods_id) {
        //获取网格规格信息
        $model      = new StoreGoodsModel;
        $spec_arr   = $model -> getPackageSpecData($goods_id);

        // 排序
        foreach ($spec_arr as $k => $v) {
            $spec_arr_sort[$k] = count($v);
        }
        asort($spec_arr_sort);

        foreach ($spec_arr_sort as $key => $val) {
            $spec_arr2[$key] = $spec_arr[$key];
        }
        $clo_name   = array_keys($spec_arr2);
        $spec_arr2  = combineDika($spec_arr2); //  获取 规格的 笛卡尔积
        $specInfo   = Db::name('goods_spec')->field('id,name')->select()->toArray();
        $spec = [];
        foreach ($specInfo as $k => $v) {
            $spec[$v['id']] = $v['name'];
        }
        // 获取所有的规格项
        $specItemInfo = Db::name('goods_spec_item')->select()->toArray();

        $specItem = [];
        foreach ($specItemInfo as $k => $v) {
            $specItem[$v['id']]['id']       = $v['id'];
            $specItem[$v['id']]['item']     = $v['item_names'];
            $specItem[$v['id']]['spec_id']  = $v['spec_id'];
        }

        $keySpecGoodsPrice = [];
        // 获取所有的规格项图片
        $keySpecGoodsPriceInfo = Db::name('store_goods_spec_price')->field('key,price,goods_storage as stock,sku')->where(['store_goods_id'=>$goods_id])->select()->toArray();
        foreach ($keySpecGoodsPriceInfo as $k => $v) {
            $keySpecGoodsPrice[$v['key']]['price']  = $v['price'];
            $keySpecGoodsPrice[$v['key']]['stock']  = $v['stock'];
            $keySpecGoodsPrice[$v['key']]['sku']    = $v['sku'];
        }

        $str = "<table class='am-table am-table-bd am-table-striped' id='spec_input_tab'>";
        $str .= "<tr>";
        // 显示第一行的数据
        foreach ($clo_name as $k => $v) {
            if ($v) {
                $str .= " <td><b>{$spec[$v]}</b></td>";
            } else {
                $str .= "  <td><b> </b></td>";
            }
        }
        $str .= "<td><b>价格</b></td><td><b>库存</b></td><td><b>SKU</b></td></tr>";
        // 显示第二行开始
        foreach ($spec_arr2 as $k => $v) {
            $str .= "<tr>";
            $item_key_name = array();
            foreach ($v as $k2 => $v2) {
                $str .= "<td>{$specItem[$v2]['item']}</td>";
                $item_key_name[$v2] = $spec[$specItem[$v2]['spec_id']] . ':' . $specItem[$v2]['item'];
            }
            ksort($item_key_name);
            $item_key       = implode('_', array_keys($item_key_name));
            $item_name      = implode(' ', $item_key_name);
            $format_price   = isset($keySpecGoodsPrice[$item_key]['price']) ? floatval($keySpecGoodsPrice[$item_key]['price']) : 0; // 价格默认为0
            $format_stock   = isset($keySpecGoodsPrice[$item_key]['stock']) ? $keySpecGoodsPrice[$item_key]['stock'] : 0; //库存默认为0
            $format_sku     = isset($keySpecGoodsPrice[$item_key]['sku']) ? $keySpecGoodsPrice[$item_key]['sku'] : 0; //库存默认为0
            $str .= "<td><input class='am-form-field j-edit-data1' j-item-key='{$item_key}' name='item[{$item_key}][price]' value='{$format_price}' onkeyup='this.value=this.value.replace(/[^\d.]/g,\"\")' onpaste='this.value=this.value.replace(/[^\d.]/g,\"\")' /></td>";
            $str .= "<td><input class='am-form-field j-edit-data2' j-item-key='{$item_key}' name='item[{$item_key}][stock]' value='{$format_stock}' onkeyup='this.value=this.value.replace(/[^\d.]/g,\"\")' onpaste='this.value=this.value.replace(/[^\d.]/g,\"\")'/></td>";
            $str .= "<td><input class='am-form-field j-edit-data3' j-item-key='{$item_key}' name='item[{$item_key}][sku]' value='{$format_sku}' /><input type='hidden' name='item[{$item_key}][key_name]' value='{$item_name}' /></td>";
            $str .= "</tr>";
        }
        $str .= "</table>";
        return $this->renderSuccess('ok', '', $str);
    }

    /**
     * 修改商品状态
     * @author: luffy
     * @date  : 2019-09-09
     */
    public function on($goods_id, $state)
    {
        // 商品详情
        $model = (new StoreGoodsModel)->getStoreDetails($goods_id);
        if (!$model->setStatus($state)) {
            return $this->renderError('操作失败');
        }
        return $this->renderSuccess('操作成功');
    }

    /**
     * 删除商品
     * @param $goods_id
     * @return array
     */
    public function delete($goods_id)
    {
        // 商品详情
        $model = GoodsModel::detail($goods_id);
        if (!$model->setDelete()) {
            return $this->renderError('删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

}
