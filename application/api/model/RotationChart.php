<?php

namespace app\api\model;

use app\common\model\RotationChart   as RotationChartModel;
/**
 * 轮播图模型
 * @author  ly
 * @date    2019-10-22
 */
class RotationChart extends RotationChartModel{

    /**
     *
     * @author ly
     * @date 2019-10-22
     */
    public function getList($type=''){
        !empty($type) && $this->where('a.type', $type);
        return $this->alias('a')
            ->field('a.id,a.img_url,a.type,a.update_user,a.update_time,c.user_name')
            ->order(['id' => 'asc'])
            ->join('store_user c', 'c.id = a.update_user ','LEFT')
            ->select()
            ->each(function ($item){
                if(!empty($item['img_url'])){
                    $item['img_url']=json_decode($item['img_url'],true);
                    foreach($item['img_url'] as $it){
                        $it['img']='web/uploads/big/'.$it['img'];
                        $its[]=$it;
                    }
                    $item['imgs']=$its;
                }else{
                    $item['imgs']='';
                }
                return $item;
            });
    }


}
