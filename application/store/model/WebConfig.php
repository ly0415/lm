<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date:
 * Time:
 */

namespace app\store\model;

use think\Db;
use app\common\model\WebConfig as WebConfigModel;

class WebConfig extends WebConfigModel
{
    /**
     *网站配置
     * @author ly
     * @date 2019-12-05
     */
    public function getList(){
        $list = $this->where('mark',1)->select();
        return !empty($list[0])?$list[0]:[];
    }

    /**
     *网站编辑
     * @author ly
     * @date 2019-12-05
     */
    public function edit($old,$data=''){
        if(empty($old['id'])){
            $this->error='参数错误！';
            return false;
        }
        $data['add_user'] = USER_ID;
        $data['add_time'] = time();
        $this->startTrans();
        try {
            $item['mark'] = 0;
            $this->where('id',$old['id'])->update($item);
            $this->allowField(true)->save($data);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }


}