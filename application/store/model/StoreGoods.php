<?php

namespace app\store\model;

use app\store\model\Goods               as GoodsModel;
use app\common\model\StoreGoods         as StoreGoodsModel;
use app\common\model\Business           as BusinessModel;
use app\common\model\GoodsCategory      as GoodsCategoryModel;
use app\store\model\StoreGoodsSpecPrice as StoreGoodsSpecPriceModel;
use app\common\model\GoodsSpec  as GoodsSpecModel;
use think\Db;

/**
 * 店铺商品模型
 * @author  luffy
 * @date    2019-08-27
 */
class StoreGoods extends StoreGoodsModel{

        protected $models;
        protected $indexs = 0;


    /**
     * 获取店铺商品列表
     * Created by PhpStorm
     * @param int $status
     * @param int $category_id
     * @param string $search
     * @param int $listRows.
     * Author: fup
     * Date: 2019-08-20
     * Time: 10:05
     */
    public function getList($is_list = false, $business_id = 0, $category_id = 0, $search = '', $status = null, $goods_sn = '',$attributes = 0){
        $filter['a.store_id'] = STORE_ID;
        $is_list && $filter['a.goods_id'] = ['NOT IN', self::getHasStoreGoodsId(STORE_ID)];
        $is_list && $filter['a.goods_sn'] = ['NOT IN', self::getHasStoreGoodsSn(STORE_ID)];
        $is_list && $filter['a.store_id'] = Store::getAdminStoreId();
        if($is_list && T_GENERAL){
            $this->join('bs_business b', 'a.room_id = b.id');
            $this->where('b.pid', '=', BUSINESS_ID);
        }
        //根据二级业务类型查找数据，囊括辅助分类
        if($business_id > 0){
            $this->field('b.business_id');
            $this->join('goods_auxiliary_class b', 'a.goods_id = b.goods_id');
            $this->where(['b.business_id' => $business_id]);
        }
        if($category_id > 0){
            $categoryIds    = GoodsCategoryModel::getSubCategoryId($category_id);
            $categoryIds    = (!empty($categoryIds) ? implode(',', $categoryIds) : [-1]);
            $categoryIds    && $filter['a.cat_id'] = ['IN', $categoryIds];
        }
        $status > 0         && $filter['a.is_on_sale'] = $status;
        !empty($search)     && $filter['a.goods_name'] = ['like', '%' . trim($search) . '%'];
        !empty($goods_sn) && $filter['a.goods_sn'] = ['like', '%' . trim($goods_sn) . '%'];
        $attributes > 0 && $this->where('FIND_IN_SET('.$attributes.',attributes)');

        // 执行查询
        $list = $this->field('a.id,a.goods_id,a.cat_id,a.goods_sn,a.market_price,a.shop_price,a.goods_name,a.goods_storage,a.is_on_sale,a.original_img,a.sort,a.attributes,a.add_time,a.room_id')->alias('a')
            ->where('a.mark', '=', 1)
            ->where($filter)
            ->order(['a.sort ASC','a.is_on_sale'=>'ASC', 'a.id'=>'DESC'])
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
        $value['goods_name']        = str_replace('\'','’',$value['goods_name']);
        $value['has_spec']          = $this->isExistSpec($value['id']);
        $GoodsCategoryInfo          = GoodsCategoryModel::getCacheAll();
        $value['format_category']   = (isset($value['cat_id']) && isset($GoodsCategoryInfo[$value['cat_id']]['name_string'])) ? $GoodsCategoryInfo[$value['cat_id']]['name_string'] : '';
        //获取原始商品信息
        $GoodsModel     = new GoodsModel();
        $goods_info     = $GoodsModel->field('room_id,deduction,attributes')->find($value['goods_id']);
        if($goods_info){
            //业务类型
            $value['format_business_name']  = Db::name('business')->where(['id'=>$goods_info['room_id']])->value('name');
            //库存扣除方式
            $value['deduction']             = $goods_info['deduction'];
            $value['format_deduction']      = ($goods_info['deduction'] ? $GoodsModel->deduction[$goods_info['deduction']] : '');
            //配送属性--获取门店商品的
            $value['format_attributes_arr'] = explode(',', $value['attributes']);
        }
        //获取辅助业务分类
        if(isset($value['room_id'])){
            $value['format_business_name']  = isset(BusinessModel::getCacheAll()[$value['room_id']]) ? BusinessModel::getCacheAll()[$value['room_id']]['name'] : '';
            $auxiliarys                     = Db::name('goods_auxiliary_class')->where(['goods_id'=>$value['goods_id'], 'business_id'=>['neq', $value['room_id']]])->select();
        }
        if(!empty($auxiliarys)){
            $bus        = [];
            foreach ($auxiliarys as $val){
                $bus[]  = isset(BusinessModel::getCacheAll()[$val['business_id']]) ? BusinessModel::getCacheAll()[$val['business_id']]['name'] : '';
            }
            $value['format_auxiliarys']     = implode(' , ', $bus);
        }
        return $value;
    }

    /**
     * 获取业务类型对应的商品
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-21
     * Time: 19:23
     */
    public function getListAll($business = 0){
        $query = [];
        $business > 0 && $query['room_id'] = $business;
        return $this->field('id,goods_name,shop_price,original_img,goods_id,store_id,attributes')
            ->where($query)
            ->where('store_id','=',STORE_ID)
            ->where('FIND_IN_SET(1,attributes)')
            ->where('is_on_sale','=',1)
            ->where('mark','=',1)
            ->order(['sort'=>'ASC','is_on_sale'=>'ASC', 'id'=>'DESC'])
            ->select();
    }

    /**
     * 获取指定店铺上架商品总量
     * @author  luffy
     * @date    2019-07-09
     */
    public function getGoodsTotal($storeId, $type = 0)
    {
        switch ($type)
        {
            case 1:
                $this->where(['is_on_sale' => 1]);
            break;
            case 2:
                $this->where(['is_on_sale' => 2]);
            break;
            default:;
        }
        return $this->where(['store_id' => $storeId, 'mark' => 1])->count();
    }

    /**
     * 编辑商品-基本信息
     * @author  luffy
     * @date    2019-07-09
     */
    public function getPackageSpecData($goods_id){
        $spec_key   = Db::name('store_goods_spec_price')->field('key')->where(['store_goods_id'=>$goods_id, 'mark'=>1])->select();
        if(empty($spec_key)){
            return false;
        }else {
            static $result = [];
            foreach($spec_key as $value){
                $keys   = isset($value) ? str_replace('_',',',$value['key']): [];
                $_data  =  Db::name('goods_spec_item')->field('spec_id,id')->where(['id'=>['in', $keys]])->select();
                foreach ($_data as $val){
                    $result[$val['spec_id']][] = $val['id'];
                }
            }
            foreach($result as $key => $value){
                $result[$key]   = array_unique($value);
            }
            return $result;
        }
    }


    /**
     * 获取门店商品规格
     * @author  luffy
     * @date    2019-11-19
     */
    public function  getStoreGoodsSpecData($store_goods_id){
        $resule = $first_key = [];
        //获取网格规格信息
        $GoodsSpecModel             = new GoodsSpecModel;
        $spec_arr                   = $this->getPackageSpecData($store_goods_id);
        if(!empty($spec_arr)){
            foreach($spec_arr as $key => $value){
                $spec_info              = $GoodsSpecModel::get($key);
                $resule[$key]['_key']   = $spec_info['name'];
                foreach ($value as $k => $v){
                    //得到规格值名称
                    $spec_item_info     = GoodsSpecItem::get($v);
                    $resule[$key]['_value'][] = [
                        $v,
                        $spec_item_info['item_names'],
                    ];
                }
            }
        }
        return $resule;
    }

    /**
     * 添加商品
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-20
     * Time: 14:20
     */
    public function add($data){
        $goods = $this->getGoodsById($data);

        Db::startTrans();
        try{
            foreach ($goods as $v){
                if($datas = self::get(['goods_sn'=>$v['goods_sn'],'store_id'=>STORE_ID,'mark'=>1])){
                    $this->error = '商品【'.$v['goods_name'].'】本店铺已存在';
                    return false;
                }
                //总站商品和对应规格数据新增
                if(isset($v['is_joint']) && $v['is_joint'] == 1 && $v['store_goods_joint']){
                    foreach ($v['store_goods_joint'] as &$joint){
                        $spec_arr = [];
                        if ($joint['key']) {
                            $key_arr = explode('_', $joint['key']);
                            $key_pailie = arrangement($key_arr, count($key_arr));
                            foreach ($key_pailie as $val) {
                                $spec_arr[] = implode('_', $val);
                            }
                        }
                        if(!$this->checkAddStoreGoods($v['goods_name'],$joint['store_goods']['goods_sn'],STORE_ID,$joint['store_goods']['goods_name'],$spec_arr,$joint['key_name'],$joint['num'],$joint)){
                            return false;
                        }
                    }
                }
                unset($v['id']);
                $v['add_time'] = time();
                $store_goods_id = DB::name('store_goods')
                    ->strict(false)
                    ->insertGetId($v);
                $this->addGoodsPrice($store_goods_id,$v['spec_price']);
                if(isset($v['is_joint']) && $v['is_joint'] == 1 && $v['store_goods_joint']){
                    $this->addGoodsJoint($store_goods_id,$v['store_goods_joint']);

                }
            }
            Db::commit();
            return true;
        }catch (\Exception $e){
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 编辑商品-基本信息
     * @author  luffy
     * @date    2019-07-09
     */
    public function edit($data){
        if(isset($data['goods_storage']) && $data['goods_storage'] == null){
            $this->error = '请设置本店库存！';
            return false;
        }
        if($data['type'] == 1 && empty($data['attributes'])){
            $this->error = '请选择配送属性！';
            return false;
        }
        // 开启事务
        $this->startTrans();
        try {
            if($data['type'] == 1){
                isset($data['shop_price'])  && $saveData['shop_price']   = $data['shop_price'];
                isset($data['bar_code'])    && $saveData['bar_code']   = $data['bar_code'];
                isset($data['goods_storage'])   && $saveData['goods_storage']   = $data['goods_storage'];
                if(isset($data['sort'])){
                    $saveData['sort']       = $data['sort'];
                    //更新所属门店所有商品排序
                    $goods_info             = self::get($data['store_goods_id']);
                    $this->save(['sort' => $data['sort']], ['goods_id' => $goods_info['goods_id'], 'mark' => 1]);
                }
                $saveData['attributes']     = implode(',', $data['attributes']);
                $this->save($saveData, ['id' => $data['store_goods_id']]);
            }
            if($data['type'] == 2){
                $StoreGoodsSpecPriceModel = new StoreGoodsSpecPriceModel;
                if($data['tp'] == 1){
                    $saveData['price'] = $data['value_data'];
                }elseif($data['tp'] == 2){
                    $saveData['goods_storage'] = $data['value_data'];
                    //分开扣除的商品更新主信息库存，为规格库存总和 --- 获取库存扣除方式
                    $goods_info = self::get($data['goods_id']);
                    if($goods_info['deduction'] == 2){
                        //得到规格库存总和
                        $total = $StoreGoodsSpecPriceModel->where(['store_goods_id' => $data['goods_id'], 'key' => ['neq', $data['spec_key']]])->sum('goods_storage');
                        $total = $total + $data['value_data'];
                        $this->save(['goods_storage'=>$total],['id' => $data['goods_id']]);
                    }
                }elseif($data['tp'] == 3){
                    $saveData['sku'] = $data['value_data'];
                }elseif($data['tp'] == 4){
                    $saveData['bar_code'] = $data['value_data'];
                }
                $StoreGoodsSpecPriceModel ->save($saveData,['store_goods_id' => $data['goods_id'], 'key' => $data['spec_key']]);
            }
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }


    /**
     * 批量修改商品规格价格和库存
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-11-27
     * Time: 15:51
     */
    public function batch($data){
        $list   = $this -> getPackageSpecData($data['store_goods_id']);
        if(isset($data['_batch'])){
            foreach ($list as $k=> $batch){
                if(!array_key_exists($k,$data['_batch'])){
                    array_push($data['_batch'],$batch);
                }
            }

        }else{
            $data['_batch'] = $list;
        }
        $result = $this->combineDika($data['_batch']);
        $storeGoodsPrice = new StoreGoodsSpecPrice();
        return $storeGoodsPrice->where(['key'=>['IN',$result],'store_goods_id'=>$data['store_goods_id'],'mark'=>1])
           ->update(['price'=>$data['price'],'goods_storage'=>$data['stock']]);

    }

    /**
     * 添加组合商品
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-14
     * Time: 15:15
     */
    public function joint($data){

        if(!empty($data['goods_sn'])){
            $data['goods_sn'] = 'ZH_' . $data['goods_sn'];
        }
        if (!isset($data['cat_id']) || empty($data['cat_id'])) {
            $this->error = '请选择商品分类';
            return false;
        }
        if (!isset($data['original_img']) || empty($data['original_img'])) {
            $this->error = '请上传商品图片';
            return false;
        }
        if (!isset($data['joint']) || empty($data['joint'])) {
            $this->error = '请选择商品';
            return false;
        }
        $data['attributes'] = implode(',',$data['attributes']);
        $data['store_id'] = STORE_ID;
        $data['is_joint'] = 1;
//        dump($data);die;
        // 开启事务
        $this->startTrans();
        try {
            // 保存商品
            $this->allowField(true)->save($data);
            if(empty($data['goods_sn'])){
                $this->save(['goods_sn'=>$this->makeGoodsSn($this->id)]);
            }
            // 商品规格
            $this->addGoodsJoint('',$data['joint']);
            // 商品图片
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }


    /**
     * 校验门店是否拉取单个商品
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-11-12
     * Time: 20:57
     */
    public function checkAddStoreGoods($joint_goods_name,$goods_id,$store_id,$goods_name,$key,$key_name,$num,&$joint){

        if(!$data = self::get(['goods_sn'=>$goods_id,'store_id'=>$store_id,'mark'=>1])){
            $this->error = '组合商品【'.$joint_goods_name.'】中【'.$goods_name.'】本店铺暂未拉取';
            return false;
        }

        if($data['is_on_sale']['value'] != 1){
            $this->error = '组合商品【'.$joint_goods_name.'】中【'.$goods_name.'】未上架';
            return false;
        }

        if(!$stock = StoreGoodsSpecPrice::getSpecPriceStock($data['id'],$key)){

            $this->error = '组合商品【'.$joint_goods_name.'】中【'.$goods_name.'】规格为【'.($key_name ? : '无规格').'】库存不足';
            return false;
        }

        if($stock['stock'] <= 0 || $stock['stock'] < $num){

            $this->error = '组合商品【'.$joint_goods_name.'】中【'.$goods_name.'】规格为【'.($key_name ? : '无规格').'】库存不足';
            return false;
        }
        $joint['joint'] = $data->toArray();
        return true;
    }

    /**
     * 获取店铺商品详情
     * @author  luffy
     * @date    2019-07-09
     */
    public function getStoreDetails($store_goods_id){
        $storeGoodsInfo                 = self::get($store_goods_id);
        //判断有无规格
        $storeGoodsInfo['isExistSpec']  = $this->isExistSpec($store_goods_id);
        if(!empty($storeGoodsInfo)){
            $storeGoodsInfo             = $this->toSwitch($storeGoodsInfo);
        }
        return $storeGoodsInfo;
    }

    /**
     * 添加店铺商品价格
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-20
     * Time: 17:17
     */
    private function addGoodsPrice($store_goos_id,$spec_price,$isUpdate = false)
    {
        $model = new StoreGoodsSpecPrice();
        $isUpdate && $model->remove($store_goos_id);
        $spec_price && $model->add($store_goos_id,$spec_price);
    }

    /**
     * 添加组合商品
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-14
     * Time: 15:45
     */
    private function addGoodsJoint($store_goods_id,$data,$isUpdate = false){
        $model = new StoreGoodsJoint();
        $isUpdate && $model->remove($this['id']);
        $store_goods_id ? $model->addJointList($store_goods_id,$data) : $this->storeGoodsJoint()->saveAll($data);
    }

    /**
     * 商品上下架
     * @author  luffy
     * @date    2019-09-09
     */
    public function setStatus($state){
        return $this->allowField(['is_on_sale'])->save(['is_on_sale' => $state ? 1 : 2]) !== false;
    }

    /**
     * 软删除
     * @return false|int
     */
    public function setDelete(){
        return $this->save(['mark' => 0]);
    }

    /**
     * 获取已经存在的商品id
     * author fup
     * date 2019-07-26
     */
    public static function getHasStoreGoodsId($storeId = 0){
        //正常商品
        return self::where('store_id','=',$storeId)
            ->where(['mark'=>1, 'goods_id'=>['neq', 0]])
            ->column('goods_id');
    }

    /**
     * 获取已经存在的商品SN
     * author fup
     * date 2019-07-26
     */
    public static function getHasStoreGoodsSn($storeId = 0){
        //特殊商品,商品ID为0的
        return self::where('store_id','=',$storeId)
            ->where(['mark'=>1, 'goods_id'=>0])
            ->column('goods_sn');
    }

    /**
     * 获取店铺拉取的商品
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-20
     * Time: 17:10
     */
    public static function getGoodsByIds($data){
        return self::with(['specPrice','storeGoodsJoint.storeGoods'])
            ->where('goods_sn','IN',$data)
            ->where('store_id','=',Store::getAdminStoreId())
            ->where('mark','=',1)
            ->field('id,attributes,goods_id,cat_id,goods_sn,goods_name,goods_storage,goods_type,
        spec_type,brand_id,brand_name,shop_price,market_price,cost_price,goods_remark,goods_content,
        original_img,is_free_shipping,is_recommend,is_new,is_hot,is_joint,suppliers_id,
        spu,sku,shipping_area_ids,on_time,style_id,room_id,keywords,delivery_fee,deduction')
            ->select()->each(function ($item){
                $item['store_id'] = STORE_ID;
                return $item;
            })->toArray();
    }

    /**
     * 获取添加的商品
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-20
     * Time: 15:00
     */
    public function getGoodsById($data){
        if(IS_ADMIN){
            $goods = Goods::getGoodsById($data);

        }else{
            $goods = self::getGoodsByIds($data);
        }
        return $goods;
    }

    /**
     * 根据店铺商品id获取生成条形码的商品
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-27
     * Time: 14:40
     */
    public static function getBcodeGoods($store_goods_id){
        return self::with('specPrice')
            ->where('id','IN',$store_goods_id)
            ->select()->toArray();
    }

    /**
     *获得业务类型下的商品
     * Created by PhpStorm.
     * Author:ly
     * Date:2019-11-11
     * Time:
     */
    public function getgoodsList($store_id='',$cat_id='',$starttime='',$endtime=''){
        if(IS_ADMIN){
            if($store_id){
                $store_id=$store_id;
            }else{
                $store_id=STORE_ID;
            }
        }else{
            $store_id=STORE_ID;
        }
        $nowendtime   = strtotime(date("Y-m-d",time()))+(60*60*24)-1;
        $starttime    = $starttime?strtotime($starttime):strtotime("-1 year -0 month -0 day");
        $endtime      = $endtime?(strtotime($endtime)+86399):$nowendtime;
        if($endtime>$nowendtime){
            $endtime  = $nowendtime;
        }
        if($starttime > $endtime){
            $starttime = $endtime-86399;
        }
        if(($endtime-$starttime)>3600*24*365){
            $endtime   = $starttime+3600*24*365;
        }
        !empty($cat_id) && $this->where('room_id', $cat_id);
        !empty($store_id) && $this->where('or.store_id','=',$store_id) && $this->where('a.store_id','=',$store_id);
        $list['data']  = $this->alias('a')
            ->field('a.id,a.goods_name,a.shop_price,a.original_img,a.goods_id,a.store_id,s.store_name,a.room_id,b.name,count(or.rec_id) as count')
            ->join('business b','a.room_id=b.id')
            ->join('order_goods or','or.goods_id=a.id ')
            ->join("order_$store_id g",'or.order_id=g.order_sn ')
            ->join("order_relation_$store_id h",'or.order_id=h.order_sn ')
            ->join('store s','s.id=a.store_id')
            ->group('or.goods_id')
            ->where('g.order_state','between',[20,59])
            ->where('h.payment_time','between',[$starttime,$endtime])
            ->where('a.is_on_sale','=',1)
            ->where('a.mark','=',1)
            ->order('count','desc')
            ->limit(10)
            ->select();
        $list['startime']  = date('Y-m-d ',$starttime);
        $list['endtime']   = date('Y-m-d ',$endtime);
        $list['store_id']  = $store_id;
        $list['cat_id']    = $cat_id;
//        print_r($list);die;
        return $list;
    }


    /**
     * 组合数据
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-11-27
     * Time: 18:17
     */
    public function combineDika($data) {
//        $data1 = func_get_args();
        $data = array_values($data);
        $cnt = count($data);
        $result = $info = array();
        foreach($data[0] as $item) {
            $result[] = array($item);
        }
        for($i = 1; $i < $cnt; $i++) {
            $result = $this->combineArray($result,$data[$i]);
        }
        foreach ($result as  &$item){
            sort($item);
            $info[] = implode('_',$item);
        }
        return $info;
    }


    /**
     *  两个数组的笛卡尔积
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-11-27
     * Time: 18:09
     */
    public function combineArray($arr1,$arr2) {
        $result = array();
        foreach ($arr1 as $item1) {
            foreach ($arr2 as $item2) {
                $temp = $item1;
                $temp[] = $item2;
                $result[] = $temp;
            }
        }
        return $result;
    }

}
