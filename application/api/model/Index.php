<?php

namespace app\api\model;

use app\common\model\Business;
use Think\Db;
use app\common\model\City    as CityModel;
use app\api\model\Business   as BusinessModel;
use app\api\model\Ad         as AdModel;
use app\store\model\Store    as StoreModel;
/**
 * 小程序首页
 * @author  ly
 * @date    2019-12-11
 */
class Index extends CityModel{

    /**
     *小程序首页
     * @author ly
     * @date 2019-12-11
     */
    public function getList($buss_id='',$store_id='',$lang_id=''){
//        $bussinss = Business::getCacheTree();
//        $storelist            = StoreModel::getStoreList(TRUE,120,['id','store_name','is_open']);
        $data=[];
        //获取文章
        $data['articleData'] = $this->getArticle($store_id,$lang_id);
        //获取轮播图
        if($buss_id){
            $data['bannerData'] = $this->getBusinessBanner($buss_id);
        }else{
        //轮播广告接口
            $data['bannerData'] = $this->getBanner($store_id);
        }
        //头部业务类型
        $business = $this->getBussinessData($store_id);
        foreach($business as $key=>$value){
            if(in_array($value['id'],[71,59,94,95])){
                continue;
//                unset($business[$key]);
            }
            $datas[] = $value;
        }
        $data['businessData'] = $datas;
        $data['store_id']     = $store_id;
        $data['lang_id']      = $lang_id;
        return $data;
    }

    /**
     *获取文章
     * @author ly
     * @date 2019-12-11
     */
    public function getArticle($store_id = '',$lang_id = ''){
        $result =Db::name('article')
            ->alias('a')
            ->field('a.id,al.title')
            ->join('article_lang al','a.id=al.article_id','LEFT')
            ->where('a.store_id',$store_id)
            ->where('a.isrecom',1)
            ->where('al.lang_id',$lang_id)
            ->order('a.add_time','DESC')
            ->select();
        return $result->toArray();

    }

    /**
     * 获取首页业务类型banner图
     * @author ly
     * @date 2019-12-11
     */
    public function getBusinessBanner($bussId = ''){
        $data = (new BusinessModel)->field('images')->where('id',$bussId)->find();
        $arr = array();
        if($data && !empty($data['images'])){
            $res = explode(',',$data['images']);
            foreach ($res as $v){
                $arr[] = array('ad_code'=>$v,'goods_id'=>0);
            }
            return $arr;
        }
        return array();
    }

    /**
     * 轮播广告接口
     * @author ly
     * @date 2019-12-11
     */
    public function getBanner($store_id='') {
        $list =(new AdModel)
            ->alias('a')
            ->field('a.ad_code,a.goods_id')
            ->join('ad_position p','a.ps_id=p.position_id','LEFT')
            ->where('p.position_num',110000)
            ->where('a.store_id',$store_id)
            ->select();
        return $list->toArray();
    }

    /**
     *头部业务类
     * @author ly
     * @date 2019-12-11
     */
    public function getBussinessData($store_id='') {
//        $list =Db::name('store_business')
//            ->alias('a')
//            ->field('a.buss_id,b.image,b.name')
//            ->join('business b','a.buss_id = b.id','LEFT')
//            ->where('store_id',$store_id)
//            ->order('b.sort','asc')
//            ->select();
        $list = (new BusinessModel)
                ->field('id,name,image as room_img')
                ->where('pid',0)
                ->where('level',1)
                ->where('mark',1)
                ->order('sort','asc')
                ->select()
                ->each(function($item){
                    $sid = Db::name('business')->where('pid',$item['id'])->column('id');
                    $item['rtid'] = implode(',',$sid);
                    $item['room_img'] = '/web/uploads/small/'.$item['room_img'];
                });
        return $list->toArray();
    }

    public function getStore($buss_id='',$latlon='',$user_id='',$lang_id=''){
        $latlon = !empty($latlon) ? $latlon:'31.99226,118.7787';
        $bussId = !empty($buss_id) ? $buss_id : "";
        //根据定位排序
        $latlon = explode(',',$latlon);
        $lng    = $latlon[1]; //经度
        $lat    = $latlon[0]; //纬度
        $latlon = $this->coordinate_switchf($lat,$lng);
        $lng    = $latlon['Longitude'];
        $lat    = $latlon['Latitude'];

        $storemodel = new StoreModel;
        if (!empty($bussId)) {
            $storemodel->where('s.business_id','in',$bussId)->where('sl.distinguish',0);
        } else {
            $storemodel->where('sl.distinguish',0);
        }
        if(!in_array($user_id,array(31076,1264,18918,19103,30419,11826))){
            $storemodel->where('s.id','<>',98);
        }
        $sData = $storemodel->alias('s')
            ->field('s.id,s.logo,s.longitude,s.latitude,s.distance,s.store_name,sl.store_name as sltore_name,s.store_start_time,s.store_end_time,s.store_notice,s.store_mobile,s.business_id as b_id')
            ->join('store_lang sl','sl.store_id=s.id','LEFT')
            ->where('sl.lang_id',$lang_id)
            ->where('s.store_type','in',[2,3])
            ->where('s.is_open',1)
            ->select()
            ->each(function($item)use($lng,$lat){
                $s = $this->getdistance($lng, $lat, $item['longitude'], $item['latitude']);
                $item['dis']   = number_format(($s / 1000), 2, '.', '');
                $item['logo']  = '/web/uploads/small/'.$item['logo'];
                //获得店铺销售总数
                $number =Db::name('order')->field('count(order_sn) as total')->where('store_id',$item['id'])->select();
                $item['order_num'] =  $number[0]['total'];
            });
        $sData = $sData?($sData->toArray()):[];
        $arrSort = array();
        foreach ($sData AS $uniqid => $row) {
            foreach ($row AS $key => $value) {
                $arrSort[$key][$uniqid] = $value;
            }
        }
        array_multisort($arrSort['dis'], SORT_ASC, $sData);
        return $sData;
    }

    /**
     *腾讯转百度坐标转换
     * @author ly
     * @date 2019-12-11
     */
    function coordinate_switchf($a, $b){
        $x = (double)$b ;
        $y = (double)$a;
        $x_pi = 3.14159265358979324;
        $z = sqrt($x * $x+$y * $y) + 0.00002 * sin($y * $x_pi);
        $theta = atan2($y,$x) + 0.000003 * cos($x*$x_pi);
        $gb = number_format($z * cos($theta) + 0.0065,6);
        $ga = number_format($z * sin($theta) + 0.006,6);
        return array(
            'Latitude'=>$ga,
            'Longitude'=>$gb
        );
    }

    /**
     * //转化距离
     * @author ly
     * @date 2019-12-11
     */

    function getdistance($lng1, $lat1, $lng2, $lat2) {
        // 将角度转为狐度
        $radLat1 = deg2rad($lat1); //deg2rad()函数将角度转换为弧度
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137 * 1000;
        return $s;
    }

    /**
     * 获取地址
     * @author ly
     * @date 2019-12-11
     */

    public function myAddress($user_id='',$latlon='',$store_id = '',$lang_id = '') {
        if(empty($user_id)){
            return "参数错误";
        }
        $res = Db::name('userAddress')->where('user_id',$user_id)->select();
        $res = $res->toArray();
        if(is_array($res)){
            foreach ($res as $k => $v) {
                $store_address = explode('_', $v['address']);
                $res[$k]['address'] = $store_address[0];
                if(!empty($v['mailing_address'])){
                    if($v['pays'] == 1){
                        $maiadd['addre']   = $v['city'].$v['mailing_address'];
                        $maiadd['id']      = $v['id'];
                        $maiadd['default_addr']      = $v['default_addr'];
                        $maiadd['phone']      = $v['phone'];
                        $maiadd['name']      = $v['name'];
                        $delivery_address[] = $maiadd;
                    }else{
                        $maiadd['addre']   = $v['mailing_address'];
                        $maiadd['id']      = $v['id'];
                        $maiadd['default_addr']      = $v['default_addr'];
                        $maiadd['phone']      = $v['phone'];
                        $maiadd['name']      = $v['name'];
                        $mailing_address[] = $maiadd;

                    }
                }
            }
            $address['a'] = !empty($delivery_address)?$delivery_address:[];
            $address['b'] = !empty($mailing_address)?$mailing_address:[];
        }
        $data['listData'] = $res?$res:[];
        $data['address']  = $address?$address:[];
        $data['lang_id']  = $lang_id;
        $data['store_id']  = $store_id;
        $data['latlon']  = $latlon;
        return $data;
    }
}
