<?php

namespace app\api\model;

use think\Config;
use think\Db;
use app\common\model\UserComment   as UserCommentModel;
use app\common\model\StoreComment   as StoreCommentModel;
use app\api\model\Order   as OrderModel;
use app\api\model\User   as UserModel;
use app\api\model\OrderGoods   as OrderGoodsModel;
/**
 * 用户评论
 * @author  ly
 * @date    2019-10-29
 */
class UserComment extends UserCommentModel{

    /**
     *用户评论列表
     * @author ly
     * @date 2019-10-29
     */
    public function getList($order_sn='',$store_id='',$user_id='',$is_good='',$image='',$page=''){
        if(empty($order_sn) && empty($store_id)) return array();
        !empty($order_sn) && $this->where('a.order_sn', $order_sn);
        !empty($store_id) && $this->where('a.store_id', $store_id);
        !empty($user_id) && $this->where('a.user_id', $user_id);
        !empty($is_good) && $this->where('a.is_good', $is_good);
        !empty($image) && $this->where('a.image','<>','');
        $data=$this->alias('a')
            ->field('a.comment_id,a.order_sn,a.user_id,a.username,c.headimgurl,a.store_id,b.store_name,a.content,a.image,a.star_val,a.star_val,a.add_time,a.is_good,b.sendout')
            ->join('order b','a.order_sn=b.order_sn')
            ->join('user c','a.user_id=c.id')
            ->where('a.is_show',1)
            ->order('a.add_time desc')
            ->limit(($page-1)*15,15)
            ->select()
//            ->paginate(15, false, ['query' => \request()->request()])
            ->each(function ($item){
                $item['content']=(!empty($item['content']))?(preg_replace_callback('/@E(.{6}==)/',function ($r){return base64_decode($r[1]);},$item['content'])):'';
                $item['image']=(!empty($item['image']))?json_decode($item['image'],true):'';
                $item['star_val']=(!empty($item['star_val']))?json_decode($item['star_val'],true):'';
                $item['add_time']=(!empty($item['add_time']))?date('Y-m-d h:i',$item['add_time']):'';
                $it=[];
                switch((count($item['star_val']))){
                    case 2:
                        $it['服务态度']=$item['star_val'][0];
                        $it['门店环境']=$item['star_val'][1];
                        $it['avg']=number_format(((array_sum($item['star_val'])/count($item['star_val']))),1);
                        break;
                    case 3:
                        $it['产品包装'] = $item['star_val'][0];
                        $it['配送速度'] = $item['star_val'][1];
                        $it['配送人员'] =$item['star_val'][2];
                        $it['avg']=number_format(((array_sum($item['star_val'])/count($item['star_val']))),1);
                        break;
                }
                $item['sendtype']=$it;
            });
        if(!empty($data)){
            $storemodel=new StoreCommentModel();
            foreach($data as $val){
                $it=[];
                $storecomment='';
                $items=$storemodel
                    ->alias('f')
                    ->field('c.user_name,c.real_name,f.content,f.creater_time')
                    ->where('f.comment_id',$val['comment_id'])
                    ->order('creater_time desc')
                    ->join('store_user c', 'c.id = f.creater_user ')
                    ->select();
                if(!empty($items)){
                    foreach($items as $rol){
                        $storecomment['content']=$rol['content'];
                        $storecomment['user_name']=$rol['user_name'];
                        $storecomment['real_name']=$rol['real_name'];
                        $storecomment['creater_time']=date('Y-m-d h:i',$rol['creater_time']);
                        $it[]=$storecomment;
                    }
                }
                $val['storecomment']=$it;
            }

        }
        return $data;
    }
    /**
     *用户评论  添加
     * @author ly
     * @date 2019-10-29
     */
    public function addComment($user_id='',$gid='',$rec_id='',$order_sn='',$order_id='',$star_num='',$evaluete_content='',$goods_images='',$store_id=''){

        $prefix = Config::get('database.prefix');
        $username=UserModel::get($user_id)['username'];
        if(empty($evaluete_content)||(mb_strlen($evaluete_content,"utf-8"))>100){
            $this->error = '请添加评论,并且不要超过100个字符！';
            return false;
        }else{
            $data['content']=htmlspecialchars($evaluete_content);
            $data['content']= preg_replace_callback('/[\xf0-\xf7].{3}/', function ($r){return '@E' . base64_encode($r[0]);}, $data['content']);

        }
        $data['order_sn']=$order_sn;
        if($this->where('order_sn',$data['order_sn'])->find()){
            $this->error = '该订单已经评论！';
            return false;
        }
        $data['user_id']=$user_id;
        $data['username']=$username;
        $data['store_id']=$store_id;
        $data['image']=(empty($goods_images))?'':json_encode(explode(',',$goods_images));
        $data['star_val']=(empty($star_num))?'':json_encode(explode(',',$star_num));
        $data['add_time']=time();
        $data['is_show']=1;
        $data['is_good']=1;
//        print_r($data);die;
        $ordermodel= new OrderModel;
        $OrderGoodsModel=new OrderGoodsModel;
        $this->startTrans();
        try {
            $this->allowField(true)->save($data);
            $ordermodel->where('order_sn',"$order_sn")->update(['evaluation_state'=>1]);
            //更改 order_店铺id  表状态
            Db::table( $prefix.'order_'.$store_id)->where('order_sn',"$order_sn")->update(['evaluation_state'=>1]);
            //更改order_goods表状态
            $OrderGoodsModel->where('order_id',"$order_sn")->update(['evaluation_state'=>1]);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

   /**
     * 获取订单详情
     * @author  ly
     * @date    2019-10-29
     */
    public function getOrderDetail($order_sn=''){

//        $order_sn='201910121612298324';
//        $store_id='58';
        $ordermodel= new OrderModel;
        if(empty($order_sn)) return array();
        !empty($order_sn) && $ordermodel->where('order_sn', $order_sn);
        $item=$ordermodel->alias('a')
            ->field('a.order_id,a.order_sn,a.pay_sn,a.store_id,a.store_name,a.buyer_id,a.buyer_name,a.sendout,b.goods_id,b.goods_name,b.goods_image,b.goods_image,b.spec_key,b.spec_key_name')
            ->join('order_goods b','a.order_sn=b.order_id')
            ->group('b.order_id')
            ->select()
            ->each(function ($item){
                $item['goods_image']=(!empty($item['goods_image']))?$item['goods_image']:'';
                if(!empty($item['sendout'])){
                    $it=[];
                    switch((($item['sendout']['value']))){
                        case 1:
                            $it['service_attitude']['name']='服务态度';
                            $it['store_environment']['name']='门店环境';
                            break;
                        case 2:
                            $it['product_packaging']['name'] = '产品包装';
                            $it['delivery_speed']['name'] = '配送速度';
                            $it['distribution_personnel']['name'] = ' 配送人员';
                            break;
                    }
                    $item['sendtype']=$it;
            }else{
                    $item['sendtype']='';
                }
            });

        return $item;
    }

    /**
     * 用户评论总数
     * @author  liy
     * @date    2019-10-29
     */
    public function getTotal($store_id='')
    {
        $data['totalAll']=$this->field('count(comment_id) as count')
                                ->where('store_id',$store_id)
                                ->where('is_show',1)
                                ->select();
        $data['totalImage']=$this->field('count(comment_id) as count')
                                    ->where('store_id',$store_id)
                                    ->where('is_show',1)
                                    ->where('image','<>','')
                                    ->select();
        $data['totalIsgood']=$this->field('count(comment_id) as count')
                                    ->where('store_id',$store_id)
                                    ->where('is_show',1)
                                    ->where('is_good',1)
                                    ->select();
        return $data;
    }


}
