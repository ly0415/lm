<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date:
 * Time:
 */

namespace app\store\model;

use app\common\model\RotationChart as RotationChartModel;

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
        if(!empty($data)){
            for($i=1;$i<=count($data['url']);$i++){
                    $item["a$i"]['img']=$data['img'][$i-1];
                    $items["a$i"]['url']=$data['url'][$i-1];
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
        $rotionm = RotationChart::get($id);
        $imgurl['update_time'] = time();
        $imgurl['update_user'] = USER_ID;
        return $rotionm->allowField(true)->save($imgurl) !== false;
    }

}