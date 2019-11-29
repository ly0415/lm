<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date:
 * Time:
 */

namespace app\store\model;

use think\Db;
use app\common\model\RotationChart as RotationChartModel;
use app\store\model\SpikeActivity as SpikeActivityModel;

class RotationChart extends RotationChartModel
{
    /**
     *轮播列表
     * @author ly
     * @date 2019-10-22
     */
    public function getList($id=''){
        !empty($id) && $this->where('a.type', $id);
        return $this->alias('a')
            ->field('a.id,a.img_url,a.type,a.update_user,a.update_time,c.user_name')
            ->order(['id' => 'asc'])
            ->join('store_user c', 'c.id = a.update_user ','LEFT')
            ->paginate(15, false, [
                'query' => \request()->request()
            ])->each(function ($item){
                    $item['imgs']=(!empty($item['img_url']))?json_decode($item['img_url'],true):'';
                    $item['typename']='';
                    switch($item['type']){
                        case 1:
                            $item['typename']='首页 banner 轮播图';
                            break;
                        case 2:
                            $item['typename']='活动页面 banner 轮播图';
                            break;
                        case 3:
                            $item['typename']='秒杀页面 banner 轮播图';
                            break;
                    }
                return $item;
            });
    }


    public function getRotionChart($id){

        $data=RotationChart::get($id);
        $data['imgs']=(!empty($data['img_url']))?json_decode($data['img_url'],true):'';
//        if(!empty($data['imgs']) ){
//            foreach($data['imgs'] as $lis){
//                $data['url']=$lis['url'];
//            }
//        }
//        print_r($data);die;
        return $data;
    }

    /**
     *轮播编辑
     * @author ly
     * @date 2019-10-24
     */
    public function edit($id,$data)
    {
//        $spikmode = new SpikeActivityModel;
//        switch($rotionm['type']){
//            case 1:
//                $data['img_url']='首页 banner 轮播图';
//                $its=1;
//                break;
//            case 2:
//                $data['img_url']='pages/exercise/exercise';
//                $its=2;
//                break;
//            case 3:
//                $data['img_url']='pages/miaosha/miaosha';
//                $its=3;
//                break;
//        }
        if(!empty($data)){
            for($i=1;$i<=count($data['url']);$i++){
                    $item["a$i"]['img']=$data['img'][$i-1];
//                    switch($its){
//                        case 1:
//                            echo 1;die;
//                            break;
//                        case 2:
//                            echo 2;die;
//                            break;
//                        case 3:
//                            if($data['url'][$i-1]){
//                                $spikone=$spikmode
//                                    ->alias('a')
//                                    ->field('a.id as activeid,s.store_goods_id,a.store_id')
//                                    ->join('spike_goods s','s.spike_id=a.id')
//                                    ->where('a.mark',1)
//                                    ->where('a.id',$data['url'][$i-1])
//                                    ->select();
//    //                                            ->each(function($ite){
//    //                                                $ite['url']=$ite['activeid'].','.$ite['store_goods_id'].','.$ite['store_id'];
//    //                                            });
//                                $spikone=json_encode($spikone->toArray());
//                            }else{
//                                $spikone='';
//                            }
//                            break;
//                    }
//                    $items["a$i"]['url']=$spikone;
                    $items["a$i"]['url']=$data['url'][$i-1];
//                    $itemss["a$i"]['img_url']=$data['img_url'];
            }
            $iitem=array_merge_recursive($item,$items);
            foreach($iitem as $rol){
                $datas[]=$rol;
            }
            $imgurl['img_url']=json_encode($datas,true);
        }else{
            $this->error = '请添加图片！';
            return false;

        }
//        print_r($imgurl);die;
        $rotionm  = RotationChart::get($id);
        $imgurl['update_time'] = time();
        $imgurl['update_user'] = USER_ID;
        return $rotionm->allowField(true)->save($imgurl) !== false;
    }

}