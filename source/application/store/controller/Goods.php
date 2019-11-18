<?php

namespace app\store\controller;

use Think\Db;
use app\store\service\Bcode;
use app\common\model\Business;
use app\store\model\StoreGoodsSpecPrice;
use app\store\model\Order           as OrderModel;
use app\store\model\StoreGoods      as StoreGoodsModel;
use app\store\model\GoodsCategory   as GoodsCategoryModel;
use app\store\model\GoodsSpec       as GoodsSpecModel;

/**
 * 商品管理控制器
 * Class Goods
 * @package app\store\controller
 */
class Goods extends Controller
{
    /**
     * 商品列表
     * @author: luffy
     * @date  : 2019-08-01
     */
    public function index($goods_status = null, $business_id = 0, $category_id = null, $goods_name = '', $goods_sn = '')
    {
        // 商品分类
        $category = GoodsCategoryModel::getCacheTree();
        // 业务类型
        $business = isset(Business::getCacheTree()[BUSINESS_ID]) ? Business::getCacheTree()[BUSINESS_ID]['child'] : '';
        // 商品列表
        $model = new StoreGoodsModel;
        $list = $model->getList(false, $business_id, $category_id, $goods_name, $goods_status, $goods_sn);
        return $this->fetch('index', compact('list', 'business', 'category'));
    }

    /**
     * 拉取后保存商品
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
     * 拉取后保存组合商品
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-20
     * Time: 15:42
     */
    public function add_joint(){
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
                return $this->renderSuccess('编辑成功', url('goods/edit', ['goods_id'=>$goods_id]));
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
        $disabled   = '';
        $model      = new StoreGoodsModel;
        //获取商品库存扣除方式
        $store_goods_info   = $model::get($goods_id);
        if($store_goods_info['deduction'] == 1){
            $disabled = 'disabled';
        }
        //获取网格规格信息
        $spec_arr   = $model -> getPackageSpecData($goods_id);
        if(empty($spec_arr)){
            return $this->renderError('error');
        }

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

        //同步扣除获取总站的规格项
//        if($store_goods_info['deduction'] == 1){
//            //得到总站store_goods_id
//            $store_goods_info_1     = $model::get(['goods_id'=>$store_goods_info['goods_id'],'mark'=>1,'store_id'=>58]);    //原始商品删除总站也跟着删除(暂时都没有删除功能)
//            $store_goods_info_1     && $goods_id = $store_goods_info_1['id'];
//        }

        $keySpecGoodsPrice = [];
        // 获取所有的规格项图片
        $keySpecGoodsPriceInfo = Db::name('store_goods_spec_price')->field('key,price,goods_storage as stock,sku,bar_code')->where(['store_goods_id'=>$goods_id])->select()->toArray();
        foreach ($keySpecGoodsPriceInfo as $k => $v) {
            $keySpecGoodsPrice[$v['key']]['price']  = $v['price'];
            $keySpecGoodsPrice[$v['key']]['stock']  = $v['stock'];
            $keySpecGoodsPrice[$v['key']]['sku']    = $v['sku'];
            $keySpecGoodsPrice[$v['key']]['bar_code']  = $v['bar_code'];
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
        $str .= "<td><b>价格</b></td><td><b>库存</b></td><td><b>SKU</b></td><td><b>条形码</b></td></tr>";
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
            $format_bcode     = isset($keySpecGoodsPrice[$item_key]['bar_code']) ? $keySpecGoodsPrice[$item_key]['bar_code'] : ''; //库存默认为0
            $str .= "<td><input class='am-form-field j-edit-data1' j-item-key='{$item_key}' name='item[{$item_key}][price]' value='{$format_price}' onkeyup='this.value=this.value.replace(/[^\d.]/g,\"\")' onpaste='this.value=this.value.replace(/[^\d.]/g,\"\")' {$disabled} /></td>";
            $str .= "<td><input class='am-form-field j-edit-data2' j-item-key='{$item_key}' name='item[{$item_key}][stock]' value='{$format_stock}' onkeyup='this.value=this.value.replace(/[^\d.]/g,\"\")' onpaste='this.value=this.value.replace(/[^\d.]/g,\"\")' {$disabled} /></td>";
            $str .= "<td><input class='am-form-field j-edit-data3' j-item-key='{$item_key}' name='item[{$item_key}][sku]' value='{$format_sku}' {$disabled} /><input type='hidden' name='item[{$item_key}][key_name]' value='{$item_name}' /></td>";
            $str .= "<td><input class='am-form-field j-edit-data4' j-item-key='{$item_key}' name='item[{$item_key}][bar_code]' value='{$format_bcode}' disabled /><input type='hidden' name='item[{$item_key}][bar_code]' value='' /></td>";
            $str .= "</tr>";
        }
        $str .= "</table>";
        return $this->renderSuccess('ok', '', $str);
    }

    /**
     * 获取商品规格
     * @author: luffy
     * @date  : 2019-09-09
     */
    public function ajax_get_spec($store_goods_id = 0){
        //获取店铺商品对应规格
        $specKey = StoreGoodsSpecPrice::getSpecKey($store_goods_id);

        //获取规格对应的规格值
        $data = (new GoodsSpecModel)->getList($specKey);

        $list = $this->formatData($store_goods_id,$data);

        $this->view->engine->layout(false);
        return $this->fetch('setGoodsSpecData', compact('list'));
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
        $model = StoreGoodsModel::detail($goods_id);
        if (!$model->setDelete()) {
            return $this->renderError('删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 规格条形码
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-26
     * Time: 16:58
     */
    public function bcode($store_goods_id = null){

        $bcode = new Bcode();

        $store_goods = StoreGoodsModel::getBcodeGoods($store_goods_id);
//        dump($store_goods);die;
        $data = [];
        foreach ($store_goods as $goods){
            if($goods['spec_price']){
                foreach ($goods['spec_price'] as $spec){
                    if($spec['bar_code']){
                        $data[] = ['goods_name'=>$goods['id'].'_'.str_replace(['/','\\',':','*','"','<','>','|','?'],'_',$goods['goods_name']).'/'.$spec['bar_code'],'goods'=>$goods['id'].'_'.str_replace(['/','\\',':','*','"','<','>','|','?'],'_',$goods['goods_name'])];
                        $bcode->createCode($spec['bar_code'],$goods['id'],$goods['goods_name'],$spec['sp_id'],$spec['key_name']);
                    }
                }
            }else{
                if($goods['bar_code']){
                    $data[] = ['goods_name'=>$goods['id'].'_'.str_replace(['/','\\',':','*','"','<','>','|','?'],'_',$goods['goods_name']).'/'.$goods['bar_code'],'goods'=>$goods['id'].'_'.str_replace(['/','\\',':','*','"','<','>','|','?'],'_',$goods['goods_name'])];
                    $bcode->createCode($goods['bar_code'],$goods['id'],$goods['goods_name']);
                }


            }
        }
        $filename = md5(time()).".zip"; //最终生成的文件名（含路径）

        if(!file_exists($filename) ){

            $zip = new \ZipArchive();

            if ($zip->open($filename, \ZIPARCHIVE::CREATE)!==TRUE) {

                exit('无法打开文件，或者文件创建失败');

            }

            foreach ($data as $item){
                $zip->addFile( ROOT_PATH.'upload/bcode/'.$item['goods_name'].'.png', $item['goods'].'/'.basename(ROOT_PATH.'upload/bcode/'.$item['goods_name'].'.png'));
            }
            $zip->close();//关闭
        }
        foreach ($data as $value){
            !file_exists(ROOT_PATH.'upload/bcode/'.$value['goods_name'].'.png') ?: unlink(ROOT_PATH.'upload/bcode/'.$value['goods_name'].'.png');
        }
        if(!file_exists($filename)){
            $this->error('当前页导出商品无录入条形码');
            exit();

        }

        header("Cache-Control: public");

        header("Content-Description: File Transfer");

        header('Content-disposition: attachment; filename='.basename($filename)); //文件名

        header("Content-Type: application/zip"); //zip格式的

        header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件

        header('Content-Length: '.filesize($filename)); //告诉浏览器，文件大小

        @readfile($filename);

        exit;
    }

    /**
     * 新增组合商品
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-14
     * Time: 12:03
     */
    public function joint(){
        if (!$this->request->isAjax()) {
            $category = GoodsCategoryModel::getGoodsCategoryByPid(0);
            return $this->fetch('joint',compact('category'));
        }
        $model = new StoreGoodsModel();
        if ($model->joint($this->postData('goods'))) {
            return $this->renderSuccess('添加成功', url('goods/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 获取商品规格
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-15
     * Time: 20:31
     */
    public function ajax_get_specs($store_goods_id = 0){
        //获取店铺商品对应规格
        $specKey = StoreGoodsSpecPrice::getSpecKey($store_goods_id);

        //获取规格对应的规格值
        $data = (new GoodsSpecModel)->getList($specKey);

        $list = $this->formatData($store_goods_id,$data);
        $this->view->engine->layout(false);
        return $this->fetch('ajaxGetSpec', compact('list'));
    }

}
