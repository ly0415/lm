<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-17
 * Time: 下午 5:32
 */

namespace app\xcx\model;


class StoreBargain extends Base{
    /**
     * 获取正在进行中的砍价产品
     * @param string $field
     */
    public function sku(){
        return $this->hasMany('Sku','id','goods_id')->where('is_delete','=',1);
    }

    /**
     * 获取正在进行中的砍价产品
     * @param string $field
     */
    public function activityGoods(){
        return $this->hasMany('ActivityGoods','store_bargain_id','id')->field('id,store_bargain_id,goods_id');
    }

    /**
     * 获取正在进行中的砍价
     * @param string $field
     */
    public static function getList($uid){
//        $list = self::alias('sb')
//            ->with(['activityGoods.goods'])
//            ->paginate(15);
////        dump($list->toArray());die;
//        foreach ($list as &$item) {
//            foreach ($item['activity_goods'] as &$v){
//                $v['store_bargain_user'] = StoreBargainUser::getBargainUser($v['store_bargain_id'],$v['goods_id'],$uid);
//            }
//        }
//        dump($list->toArray());die;

        $list = self::alias('sb')
            ->field('sb.id as sb_id,sb.title,sb.start_time,sb.end_time,sb.expiry_time,sbu.id as sbu_id,sbu.uid,sbu.bargain_id,sbu.price,sg.id as sg_id,sg.goods_name,sg.goods_content,ag.id as ag_id,ag.goods_id,ag.goods_price,sg.original_img')
            ->join('activity_goods ag','sb.id = ag.store_bargain_id','left')
            ->join('store_goods sg','sg.id=ag.goods_id','left')
            ->join('store_bargain_user sbu','sb.id = sbu.bargain_id and ag.goods_id = sbu.goods_id and sbu.uid = '.$uid,'left')
            ->where('sg.mark','=',1)
            ->where('sg.is_on_sale','=',1)
            ->where('sb.is_delete',0)
            ->where('sb.start_time','LT',time())
            ->where('sb.end_time','GT',time())
            ->select();
//        dump($list->toArray());die;
        foreach ($list as &$item){
            $item['original_img'] = base_url() . $item['original_img'];
        }
        if($list) return $list->toArray();
        else return [];
    }
//$list = self::alias('sb')
//->field('sb.*,sg.goods_name,sg.shop_price,sg.goods_content,sg.original_img')
//->join('store_goods sg','sb.goods_id = sg.id','left')
//->where('sb.is_delete',0)
//->where('sb.start_time','LT',time())
//->where('sb.end_time','GT',time())
//->where('sg.is_delete','=',1)
//->select();
//dump($list->toArray());die;
    /**
     * 获取一条正在进行中的砍价产品
     * @param int $bargainId
     * @param string $field
     * @return array
     */
    public static function getBargainTerm($bargainId = 0){
        if(!$bargainId) return [];
//        $model = self::validWhere();
        $bargain = self::field('s.*,g.goods_name,shop_price,original_img,g.goods_content')
            ->alias('s')
            ->join('goods g','s.goods_id = g.goods_id','left')
            ->where('s.id','=',$bargainId)
            ->where('s.is_delete',0)
            ->where('s.start_time','LT',time())
            ->where('s.end_time','GT',time())
            ->find();
        if($bargain){
            $bargain = $bargain->toArray();
            $bargain['specification'] = [
                'color_title'=>"颜色",
      'color_kinds'=>['红色', '蓝色', '白色', '黑色', '紫色', '藏青色', '荧光绿色'],
      'size_title'=>'尺寸',
      'size_kinds'=> ['S', 'M', 'L', 'XL', 'XXL']
            ];
            $bargain['original_img'] = base_url() . $bargain['original_img'];
            return $bargain;
        }
        else return [];
    }

    /**
     * 正在开启的砍价活动
     * @return $this
     */
    public static function validWhere(){
        return  self::where('is_delete',0)
            ->where('start_time','LT',time())
            ->where('end_time','GT',time());
    }

    /**
     * 判断砍价产品是否开启
     * @param int $bargainId
     * @return int|string
     */
    public static function validBargain($bargainId = 0){
        $model = self::validWhere();
        return $model->where('id',$bargainId)->count();
    }

    /**
     * 获取砍价次数
     * @param int $bargainId
     * @return array
     */
    public static function getBargainTimes($bargainId = 0){
        if(!$bargainId) return [];
        return self::where('id','=',$bargainId)
            ->value('peoples');
    }

    /**
     * 获取最高价和最低价
     * @param int $bargainId
     * @return array
     */
    public static function getBargainMaxMinPrice($bargainId = 0){
        if(!$bargainId) return [];
        return self::where('id',$bargainId)->field('bargain_min_price,bargain_max_price')->find()->toArray();
    }

    /**
     * 获取商品砍价金额
     * @param int $bargainId
     * @return array
     */
    public static function getGoodsPrice($bargainId=0){
        if(!$bargainId)return [];
        $data = self::alias('s')
            ->field('g.shop_price')
            ->join('goods g','s.goods_id = g.goods_id','left')
            ->where('s.id','=',$bargainId)
            ->find()->toArray();
        return $data['shop_price'];
    }
    /**
     * 添加砍价产品浏览次数
     * @param int $bargainId
     * @return bool
     */
    public static function addBargainLook($bargainId = 0){
        if(!$bargainId)return false;
        return self::where('id','=',$bargainId)
            ->setInc('look',1);
    }

    /**
     * 添加砍价产品分享次数
     * @param int $bargainId
     * @return bool
     */
    public static function addBargainShare($bargainId = 0){
        if(!$bargainId)return false;
        return self::where('id','=',$bargainId)
            ->setInc('share',1);
    }

}