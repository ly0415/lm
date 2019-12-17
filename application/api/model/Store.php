<?php

namespace app\api\model;

use app\common\model\Region;
use app\common\model\Store as StoreModel;
use app\common\exception\BaseException;

/**
 * 用户收货地址模型
 * Class UserAddress
 * @package app\common\model
 */
class Store extends StoreModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        
    ];

    /**
     * @param $user_id
     * @return false|static[]
     * @throws \think\exception\DbException
     */
    public function getList($user_id)
    {
        return self::all(compact('user_id'));
    }


    /**
     * 店铺详情
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-31
     * Time: 15:38
     */
    public static function detail($id=58,$is_open=1)
    {
        return self::get(compact('id','is_open'));
    }


    /**
     * 判断收货地址是否在配送范围内(改)
     * Created by PhpStorm.
     * @param $address -- 用户地址
     * Author: fup
     * Date: 2019-10-31
     * Time: 15:50
     */
    public function doCheckAddressRange($address)
    {
        if($address['latlon'] && strpos($address['latlon'],',') > 0){
//            $url = "https://restapi.amap.com/v3/geocode/geo?output=JSON&key=270d48097fc1e3755e8e11c70ca2df01&address=中华门";
//            $data = $this->gets($url);
//            $result = json_decode($data, true);
//            // 请求失败
//            if ($result['status'] == 0) {
//                throw new BaseException(['msg' => "位置解析api：{$result['info']}", 'code' => 0]);
//            }
//            $point = explode(',',$result['geocodes'][0]['location']);
            //判断地址是否在配送范围内
//            $point=array(
//                'lng'=> 118.774543762207,
//                'lat'=> 32.00679397583009
//            );
            $latlon = explode(',',$address['latlon']);
//            //腾讯地图经纬度转换为百度地图经纬度
//            $latlon = coordinate_switchf($latlon[1],$latlon[0]);
            $point = [
                'lng'=> $latlon[1],
                'lat'=> $latlon[0]
            ];
            $number = $this['delivery_area'];
            $pts = array();
            $number = explode(';', $number);
            foreach ($number as $key => $value) {
                $lngLat = explode(',', $value);
                $array = array(
                    'lng'=> $lngLat[0],
                    'lat'=> $lngLat[1],
                );
                array_push($pts, $array);
            }
//            dump($point);dump($pts);die;
            $result = $this->isPointInPolygon($point, $pts);
//            dump($result);die;
            if ($result) {
                return true;
            } else {
                return false;
            }
        }
        return false;


    }

    /**
     * 判断点是否在多边形范围内
     * @param $point
     * @param $polygon
     * @return int
     */
    public function isPointInPolygon($point, $polygon)
    {
        $N = count($polygon);
        $boundOrVertex = 1; //如果点位于多边形的顶点或边上，也算做点在多边形内，直接返回true
        $intersectCount = 0;//cross points count of x
        $precision = 2e-10; //浮点类型计算时候与0比较时候的容差
        $p1 = 0;//neighbour bound vertices
        $p2 = 0;
        $p = $point; //测试点

        $p1 = $polygon[0];//left vertex
        for ($i = 1; $i <= $N; ++$i) {//check all rays
            // dump($p1);
            if ($p['lng'] == $p1['lng'] && $p['lat'] == $p1['lat']) {
                return $boundOrVertex;//p is an vertex
            }
            $p2 = $polygon[$i % $N];//right vertex
            if ($p['lat'] < min($p1['lat'], $p2['lat']) || $p['lat'] > max($p1['lat'], $p2['lat'])) {//ray is outside of our interests
                $p1 = $p2;
                continue;//next ray left point
            }
            if ($p['lat'] > min($p1['lat'], $p2['lat']) && $p['lat'] < max($p1['lat'], $p2['lat'])) {//ray is crossing over by the algorithm (common part of)
                if($p['lng'] <= max($p1['lng'], $p2['lng'])){//x is before of ray
                    if ($p1['lat'] == $p2['lat'] && $p['lng'] >= min($p1['lng'], $p2['lng'])) {//overlies on a horizontal ray
                        return $boundOrVertex;
                    }
                    if ($p1['lng'] == $p2['lng']) {//ray is vertical
                        if ($p1['lng'] == $p['lng']) {//overlies on a vertical ray
                            return $boundOrVertex;
                        } else {//before ray
                            ++$intersectCount;
                        }
                    } else {//cross point on the left side
                        $xinters = ($p['lat'] - $p1['lat']) * ($p2['lng'] - $p1['lng']) / ($p2['lat'] - $p1['lat']) + $p1['lng'];//cross point of lng
                        if (abs($p['lng'] - $xinters) < $precision) {//overlies on a ray
                            return $boundOrVertex;
                        }
                        if ($p['lng'] < $xinters) {//before ray
                            ++$intersectCount;
                        }
                    }
                }
            } else {//special case when ray is crossing through the vertex
                if ($p['lat'] == $p2['lat'] && $p['lng'] <= $p2['lng']) {//p crossing over p2
                    $p3 = $polygon[($i+1) % $N]; //next vertex
                    if ($p['lat'] >= min($p1['lat'], $p3['lat']) && $p['lat'] <= max($p1['lat'], $p3['lat'])) { //p.lat lies between p1.lat & p3.lat
                        ++$intersectCount;
                    } else {
                        $intersectCount += 2;
                    }
                }
            }
            $p1 = $p2;//next ray left point
        }
        if ($intersectCount % 2 == 0) {//偶数在多边形外
            return false;
        } else { //奇数在多边形内
            return true;
        }

    }


    /**
     * 模拟GET请求 HTTPS的页面
     * @param string $url 请求地址
     * @return string $result
     */
    protected function gets($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }


}
