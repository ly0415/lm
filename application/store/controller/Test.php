<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-07-05
 * Time: 下午 3:15
 */

namespace app\store\controller;

use think\Config;
use think\Db;
use app\store\service\Payment       as PaymentService;

class Test extends \think\Controller
{



    /**
     * 中行对账     http://localhost/lmeriPro/web/index.php?s=/store/test/zhdz
     * @author: luffy
     * @date  : 2019-10-30
     */
    public function zhdz(){
//        header('Content-Type: text/html; charset=utf-8');
//        PaymentService::wxRefund('201911040931174066');
//        PaymentService::refundQuery('201911040931174066');

//        pre($res);die;
//        include_once VENDOR_PATH."/shijicloud/SearchOrder.php";
//        $SearchOrder = new \SearchOrder();
//
//        $res = $SearchOrder->getOrderInfo(1, ['2019112115365221912164', '952019112122001427635712667831_ZFBA'])->index();
//        $res = $SearchOrder->getOrderInfo(2, ['2019112114484149551893', '2019112114484149555785', '4200000442201911211344759928_WEIX'])->index();
//        pre($res);
    }

    /**
     * 核对分销订单
     * @author: luffy
     * @date  : 2019-09-18
     */
    public function fxOrder1($store_id){
        $data = Db::table('bs_order_'.$store_id)->alias('a')
            ->field('a.order_sn')
            ->join('bs_order_details_'.$store_id.' b','a.order_sn = b.order_sn')
            ->join('bs_order_relation_'.$store_id.' c','b.order_sn = c.order_sn')
            ->where(['b.fx_user_id'=>['neq', 0],'c.payment_type'=>['neq',5],'a.order_state'=>['>', 10],'a.add_time'=>['>=',1572537600],'a.mark'=>1])
            ->select();
        echo '<pre>';print_r( count($data ));
        $data1 = Db::table('bs_fx_order')->alias('a')
            ->join('bs_order_'.$store_id.' b','a.order_sn = b.order_sn')
            ->where(['a.store_id'=>$store_id,'b.add_time'=>['>=',1572537600],'b.mark'=>1])->select();
        echo '<pre>';print_r( count($data1 ));die;
    }

    /**
     * 核对分销订单
     * @author: luffy
     * @date  : 2019-09-18
     */
    public function fxOrder2($store_id){
        $data = Db::table('bs_order_'.$store_id)->alias('a')
            ->field('a.order_sn')
            ->join('bs_order_details_'.$store_id.' b','a.order_sn = b.order_sn')
            ->join('bs_order_relation_'.$store_id.' c','b.order_sn = c.order_sn')
            ->where(['b.fx_user_id'=>['neq', 0],'c.payment_type'=>['neq',5],'a.order_state'=>['>', 10],'a.add_time'=>['>=',1572537600],'a.mark'=>1])
            ->select();
        foreach($data as $value){
            $a[] = $value['order_sn'];
        }
        $data1 = Db::table('bs_fx_order')->alias('a')
            ->join('bs_order_'.$store_id.' b','a.order_sn = b.order_sn')
            ->where(['a.store_id'=>$store_id,'b.add_time'=>['>=',1572537600],'b.mark'=>1])->select();
        foreach ($data1 as $value){
            if(!in_array($value['order_sn'], $a)){
                echo '<pre>';print_r( $value['order_sn'] );die;
            }
        }
        foreach($data1 as $value){
            $b[] = $value['order_sn'];
        }
        // 获取去掉重复数据的数组
        $unique_arr = array_unique($b);

        // 获取重复数据的数组
        $repeat_arr = array_diff_assoc($b, $unique_arr);
        echo '<pre>';print_r( $repeat_arr );die;

        echo '<pre>';print_r( $a );
        echo '<pre>';print_r( $b );die;


    }

    /**
     * 核对分销订单---核查订单表未生成分销数据，补数据的
     * @author: luffy
     * @date  : 2019-09-18
     */
    public function fxOrder3($store_id){
        $data = Db::table('bs_order_'.$store_id)->alias('a')
            ->field('a.id,a.order_sn,a.mark')
            ->join('bs_order_details_'.$store_id.' b','a.order_sn = b.order_sn')
            ->join('bs_order_relation_'.$store_id.' c','b.order_sn = c.order_sn')
            ->where(['b.fx_user_id'=>['neq', 0],'c.payment_type'=>['neq',5],'a.order_state'=>['>', 10],'a.add_time'=>['>=',1572537600],'a.mark'=>1])
            ->select();
        $data1 = Db::table('bs_fx_order')->alias('a')
            ->join('bs_order_'.$store_id.' b','a.order_sn = b.order_sn')
            ->where(['a.store_id'=>$store_id,'b.add_time'=>['>=',1572537600],'b.mark'=>1])->select();

        foreach($data1 as $value){
            $a[] = $value['order_sn'];
        }

        foreach ($data as $value){
            if(!in_array($value['order_sn'], $a)){
                echo '<pre>';print_r( $value['order_sn'] );die;
            }
        }
    }


    //lmeri_goods表数据
    public function goods(){
        $data = Db::table('bs_goods')->field('a.goods_id as id,a.goods_sn,a.original_img as goods_original_image,b.goods_name,a.cat_id as cate_id,a.spec_type,a.goods_type as goods_model_id,a.market_price,a.shop_price as sale_price,a.cost_price,a.is_on_sale as is_shelf,a.deduction as stock_type,b.goods_content as content,last_update as update_time,on_time as create_time')->alias('a')->join('bs_goods_lang b','a.goods_id = b.goods_id and b.lang_id = 29','left')->select();
        !empty($data) && $data = $data->toArray();
//        dump($data);die;
        foreach ($data as $k =>&$v){
            if(is_null($v['goods_sn'])){
                $v['goods_sn'] = '';
            }
            if(is_null($v['goods_original_image'])){
                $v['goods_original_image'] = '';
            }
            if(is_null($v['goods_original_image'])){
                $v['goods_original_image'] = '';
            }
            if(is_null($v['goods_name'])){
                $v['goods_name'] = '';
            }
            if(is_null($v['cate_id'])){
                $v['cate_id'] = 0;
            }
            if(is_null($v['spec_type'])){
                $v['spec_type'] = 20;
            }
            if(is_null($v['goods_model_id'])){
                $v['goods_model_id'] = 0;
            }
            if(is_null($v['market_price'])){
                $v['market_price'] = 0;
            }
            if(is_null($v['sale_price'])){
                $v['sale_price'] = 0;
            }
            if(is_null($v['cost_price'])){
                $v['cost_price'] = 0;
            }
            if(is_null($v['is_shelf'])){
                $v['cost_price'] = 0;
            }
            if(is_null($v['stock_type'])){
                $v['stock_type'] = 0;
            }
            if(is_null($v['content'])){
                $v['content'] = '';
            }
        }
        $res = Db::table('lmeri_goods')->insertALL($data);
        echo $res;
    }

    //商品分类goods_category
    public function category(){
        $data = Db::table('bs_goods_category')->field('a.id,a.parent_id as pid,a.image,a.level,a.sort_order as sort,a.add_time as create_time,a.modify_time as update_time,b.category_name as name')->alias('a')->join('bs_goods_category_lang b','a.id = b.category_id and b.lang_id = 29','left')->select();
        $data = $data->toArray();
        echo Db::table('lmeri_goods_category')->insertAll($data);
//        dump($data);
//die;
//        foreach ($data as &$v){
//            if($v[''])
//        }
    }

    //商品分类goods_brand
    public function brand(){
        $data = Db::table('bs_goods_brand')->field('a.id,a.logo,a.descrption as `desc`,a.sort,a.modify_time as update_time,a.add_time as create_time, b.brand_name as name')->alias('a')->join('bs_goods_brand_lang b','a.id = b.brand_id and b.lang_id = 29','left')->select();
        $data = $data->toArray();
//        dump($data);die;
        echo Db::table('lmeri_goods_brand')->insertAll($data);
    }

    //商品分类goods_brand
    public function goodsModel(){
        $data = Db::table('bs_goods_type')->field('a.id,a.category_id as cate_id,b.type_name as name,b.add_time as create_time,a.mark')->alias('a')->join('bs_goods_type_lang b','a.id = b.type_id and b.lang_id = 29','left')->select();
        $data = $data->toArray();
//        dump($data);die;
        echo Db::table('lmeri_goods_model')->insertAll($data);
    }

    /**
     * 业务类型
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-23
     * Time: 16:08
     */
    public function business(){
        $data = Db::name('room_type')
            ->field('id,room_name as name,room_img as image,room_url as xcx_code_image,superior_id as pid,room_adv_imgs as images,sort,add_time as create_time,modify_time as update_time')
            ->select()->toArray();
        foreach ($data as &$v){
            foreach ($v as $k => &$vv){
                if($k == 'name' && is_null($vv)){
                    $vv = '';
                }
                  if($k == 'xcx_code_image' && is_null($vv)){
                      $vv = '';
                  }
                if($k == 'update_time' && is_null($vv)){
                    $vv = 0;
                }

            }

        }
//        echo Db::name('business')->insertAll($data);
    }

    //业务分类business_category
    public function businessCategory(){
        $data = Db::table('bs_room_category')->field('a.id,a.room_type_id as name,a.sort,a.add_time as create_time,a.category_id as cate_id')->alias('a')->select();
        $data = $data->toArray();
//        dump($data);die;
        echo Db::table('lmeri_business_category')->insertAll($data);
    }

    //业务分类goods_model_spec
    public function goodsModelSpec(){
        $data = Db::table('bs_goods_spec')->field('a.id,a.type_id as goods_model_id,a.order as sort,b.spec_name,b.add_time as create_time')->alias('a')->join('bs_goods_spec_lang b','a.id = b.spec_id and b.lang_id = 29','left')->select();
        $data = $data->toArray();
//                dump($data);die;

        foreach ($data as &$v){
            if(is_null($v['spec_name']))$v['spec_name'] = '';
            if(is_null($v['create_time']))$v['create_time'] = 0;
        }
        echo Db::table('lmeri_goods_model_spec')->insertAll($data);
    }

    //业务分类goods_model_spec_value
    public function goodsModelSpecValue(){
        $data = Db::table('bs_goods_spec_item')->field('a.id as spec_value_id,a.spec_id,b.item_name as spec_value,b.add_time as create_time')->alias('a')->join('bs_goods_spec_item_lang b','a.id = b.item_id and b.lang_id = 29','left')->select();
        $data = $data->toArray();
//                dump($data);die;

//        foreach ($data as &$v){
//            if(is_null($v['spec_name']))$v['spec_name'] = '';
//            if(is_null($v['create_time']))$v['create_time'] = 0;
//        }
        echo Db::table('lmeri_goods_model_spec_value')->insertAll($data);
    }

    //模型属性lmeri_goods_model_attr
    public function goodsAttribute(){
        $data = Db::table('bs_goods_attribute')->field('a.attr_id as id,b.name as attr_name,a.type_id as goods_model_id,a.order as sort,b.add_time as create_time')->alias('a')->join('bs_goods_attr_lang b','a.attr_id = b.a_id and b.lang_id = 29','left')->select();
        $data = $data->toArray();
//                dump($data);die;

        foreach ($data as &$v){
            if(is_null($v['attr_name']))$v['attr_name'] = '';
            if(is_null($v['create_time']))$v['create_time'] = 0;
        }
        echo Db::table('lmeri_goods_model_attr')->insertAll($data);
    }

    //lmeri_goods_attr
    public function goodsAttr(){
        $data = Db::table('bs_goods_attr')->field('a.goods_attr_id as id,a.goods_id,a.attr_id as model_attr_id,a.attr_value ')->alias('a')->select();
        $data = $data->toArray();
//                dump($data);die;

//        foreach ($data as &$v){
//            if(is_null($v['attr_name']))$v['attr_name'] = '';
//            if(is_null($v['create_time']))$v['create_time'] = 0;
//        }
        echo Db::table('lmeri_goods_attr')->insertAll($data);
    }
    //lmeri_goods_spec
    public function goodsSpec(){
        $data = Db::table('bs_goods_spec_price')->field('a.goods_id,a.key as spec_name,a.key_name as spec_value,a.price,a.sku as goods_sku')->alias('a')->select();
        $data = $data->toArray();
//                dump($data);die;

//        foreach ($data as &$v){
//            if(is_null($v['attr_name']))$v['attr_name'] = '';
//            if(is_null($v['create_time']))$v['create_time'] = 0;
//        }
        echo Db::table('lmeri_goods_spec')->insertAll($data);
    }

    /**
     * 商品运费
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-28
     * Time: 09:30
     */
    public function goodsFee($cate = 0,$seach = 6,$fee = 8){
        $child = Db::name('goods_category')
            ->alias('c')
            ->field('c.*,g.goods_id,g.cat_id,g.delivery_fee')
            ->join('goods g','c.id = g.cat_id','LEFT')
            ->where('c.parent_id','=',$cate)
            ->where('g.delivery_fee','=',$seach)
            ->select();
            dump($child->toArray());die;

    }

    /**
     * 计算三级分销人员所得佣金
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-19
     * Time: 14:00
     */
    public function getFxCommission($page = 1){

        $data = Db::name('fx_order')->order('id ASC')->page($page,5000)->select()->toArray();
        $i = 0;
        dump($data);die;
//        die;
        foreach ($data as $v){
            $i++;
            Db::name('fx_order')->where('id','=',$v['id'])
                ->update([
                    'fx_commission' => number_format($v['pay_money'] * $v['fx_commission_percent'] * 0.01, 2, '.', '')
                ]);
        }
        echo $i;
    }

    /**
     * 计算一级分销人员所得佣金
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-19
     * Time: 14:00
     */
    public function getFxCommission1($page = 1){

        $data = Db::name('fx_order')
            ->field('a.*,b.id as bid,b.lev1_prop,b.lev2_prop')
            ->alias('a')
            ->join('fx_rule b','a.rule_id = b.id','LEFT')
            ->where('a.id','>',64070)
            ->order('id ASC')->page($page,1000)->select()->toArray();
        $i = 0;
        dump($data);die;
//        die;
        foreach ($data as $v){
            $i++;
            Db::name('fx_order')->where('id','=',$v['id'])
                ->update([
                    'fx_commission_1' => number_format($v['pay_money'] * $v['lev1_prop'] * 0.01, 2, '.', ''),
                    'fx_commission_2' => number_format($v['pay_money'] * $v['lev2_prop'] * 0.01, 2, '.', '')
                ]);
        }
        echo $i;
    }


    /**
     * 规格条形码
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-26
     * Time: 16:58
     */
    public function bcode(){
        require VENDOR_PATH.'/bcode/BCGFontFile.php';
        require VENDOR_PATH.'/bcode/BCGDrawing.php';
        require VENDOR_PATH.'/bcode/BCGcode39.barcode.php';

            //颜色条形码
            $color_black = new \BCGColor(0, 0, 0);
            $color_white = new \BCGColor(255, 255, 255);
            $drawException = null;
            try
            {
                $code = new \BCGcode39();
                $code->setScale(2);
                $code->setThickness(30); // 条形码的厚度
                $code->setForegroundColor($color_black); // 条形码颜色
                $code->setBackgroundColor($color_white); // 空白间隙颜色
                // $code->setFont($font); //
                $code->parse(123456); // 条形码需要的数据内容
            }
            catch(\Exception $exception)
            {
                $drawException = $exception;
            }
            //根据以上条件绘制条形码
            $drawing = new \BCGDrawing('', $color_white);
            if($drawException) {
                $drawing->drawException($drawException);
            }else{
                $drawing->setBarcode($code);
                $drawing->draw();
            }
            // 生成PNG格式的图片
//            header('Content-Type: image/png');
//         header('Content-Disposition:attachment; filename="1.png"'); //自动下载
            $drawing->finish(\BCGDrawing::IMG_FORMAT_PNG);

die;



        $s1=md5('test1');

        $filename ="$s1.zip"; //最终生成的文件名（含路径）

        if(!file_exists($filename) ){

            $zip = new \ZipArchive();

            if ($zip->open($filename, \ZIPARCHIVE::CREATE)!==TRUE) {

                exit('无法打开文件，或者文件创建失败');

            }
            $data = [
                'upload/bcode/1569501681.png',
                'upload/bcode/1569546590.png',
            ];

            foreach ($data as $item){
                $zip->addFile( ROOT_PATH.$item, basename(ROOT_PATH.$item));

            }

            $zip->close();//关闭

        }

        if(!file_exists($filename)){

            exit("无法找到文件");

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


    //zhifu
    public function wxpay(){
        $model = new \app\store\model\Order();
        $order['store_id'] = 98;
        $order['order_sn'] = mt_rand(10000,99999);
        $order['order_pay_price'] = 0.01;
        $order['goods_list'] = [
          ['goods_id' => 1],
          ['goods_id' => 2]
        ];


//        dump($order);die;
        $payment =$model->paymentByWechat([],$order);
        dump($payment);die;
    }

    //导出
    public function excel(){
        $model = new \app\store\model\Order();
        $model->exportList(['start_time' => '2019-11-11','end_time'=>'2019-11-19']);
    }

    //刷订单表微信支付单号
    public function pay_sn($page = 1){

        $data = Db::name('order')->alias('a')
            ->join('order_details_93 b','a.order_sn = b.order_sn','LEFT')
            ->join('order_relation_93 c' ,'b.order_sn = c.order_sn','LEFT')
            ->field('a.order_id,a.pay_sn apay_sn,a.order_sn,a.store_id,b.id,b.pay_sn,c.payment_type ')
            ->where('a.pay_sn','neq','')
            ->where('b.pay_sn','=','')
            ->where('c.payment_type','=',2)
            ->page($page,5000)
            ->select();
        dump($data->toArray());die;
        $i = 0;
        foreach ($data as $item){
            $i+=1;
            Db::name('order_details_93')->where('id','=',$item['id'])->update(['pay_sn'=>$item['apay_sn']]);
        }
        echo $i;
    }


    //业务类型图片
    public function business_image(){
        $data = Db::name('business')->where('mark','=',1)
            ->select();
        dump($data);die;
        foreach ($data as $item){
            $image = str_replace('upload/images/cates/','120/Store.business/',$item['image']);
            Db::name('business')->where('id','=',$item['id'])
                ->update(['image'=>$image]);
        }
    }

    //组合
    public function spec(){
        $model      = new \app\store\model\StoreGoods();
        $data = $model->combineDika([['2074','2075','2141','2142'],['2144','2145','2147','2247'],['2191','2245']]);
        dump($data);
    }


    //辅助分类
    public function aclass(){
        $data = Db::name('goods')->where('goods_id','>=',5329)->select();
        die;
        $i = 0;
        foreach ($data as $item){
            $i+=1;
            Db::name('goods_auxiliary_class')->where('goods_sn','=',$item['goods_sn'])->update(['cate_id'=>$item['cat_id']]);
        }
        echo $i;die;
    }

    //商品
    public function goodsMark(){
        $data = Db::name('store_goods')->field('goods_id,goods_sn,max(mark) m')
            ->where('store_id','=',58)
            ->where('goods_id','<>',0)
            ->where('is_joint','=',0)
            ->group('goods_id')
            ->select();
        $i = 0;
        die;
        foreach ($data as $k => $item){
            if($item['m'] == 0){
                $i +=1;
                Db::name('store_goods')->where('goods_id','=',$item['goods_id'])
                    ->where('goods_sn','=',$item['goods_sn'])
                    ->where('store_id','<>',58)
                    ->update(['mark'=>0]);
            }
        }
        echo $i;
    }

    //goods_sn
    public function goods_sn(){
        $data = Db::name('store_goods')->alias('a')
            ->field('a.*,g.goods_sn,g.goods_name ggoods_name')
            ->join('goods g','a.goods_id = g.goods_id','LEFT')
            ->where('a.goods_sn','=','')
            ->select();
            dump($data->toArray());die;
    }

}