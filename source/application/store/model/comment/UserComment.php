<?php

namespace app\store\model\comment;

use think\Config;
use app\common\model\UserComment   as UserCommentModel;
use app\common\model\StoreComment   as StoreCommentModel;

/**
 *
 * Class UserComment
 * @package app\store\model
 */
class UserComment extends UsercommentModel
{

    /**
     *评价列表
     * @author ly
     * @date 2019-10-30
     */
    public function getList($store_id='')
    {
//        echo $store_id;die;
//        $store_id=98;
//        $store_id='';
        !empty($store_id) && $this->where('a.store_id', $store_id);
        $prefix = Config::get('database.prefix');
        $data= $this
             ->alias('a')
            ->field('a.comment_id,a.order_sn,a.user_id,a.username,b.headimgurl,a.store_id,a.content,a.image,a.is_show,a.star_val,a.is_good,a.add_time')
             ->join('user b','a.user_id=b.id')
//             ->where('a.store_id',$store_id)
//                ->select()
                ->order('a.add_time desc')
                     ->paginate(15, false, ['query' => \request()->request()])
                     ->each(function ($item){
//                         $item['image_id']='web/'.$item['image_id'];

                         $item['content']=(!empty($item['content']))?(preg_replace_callback('/@E(.{6}==)/',function ($r){return base64_decode($r[1]);},$item['content'])):'';
                         $item['image']=(!empty($item['image']))?json_decode($item['image']):'';
                         $item['star_val']=(!empty($item['star_val']))?json_decode($item['star_val']):'';
                         $it=[];
                         switch((count($item['star_val']))){
                            case 2:
                                $it['服务态度']=$item['star_val'][0];
                                $it['门店环境']=$item['star_val'][1];
                                break;
                            case 3:
                                $it['产品包装'] = $item['star_val'][0];
                                $it['配送速度'] = $item['star_val'][1];
                                $it['配送人员'] =$item['star_val'][2];
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
//                        $storecomment['content']=$rol['content'];
                        $storecomment['content']=(!empty($rol['content']))?(preg_replace_callback('/@E(.{6}==)/',function ($r){return base64_decode($r[1]);},$rol['content'])):'';
                        $storecomment['user_name']=$rol['user_name'];
                        $storecomment['real_name']=$rol['real_name'];
                        $storecomment['creater_time']=$rol['creater_time'];
                        $it[]=$storecomment;
                    }
                }
                $val['storecomment']=$it;
            }

        }
//        print_r($data->toArray());die;
        return $data;
    }

    /**
     *商家添加评论
     * @author ly
     * @date 2019-10-30
     */
    public function add($id='',$content='')
    {
        $storemodel=new StoreCommentModel();
        if(empty($content)){
            $this->error="回复内容不能为空！";
            return false;
        }
        $this->startTrans();
        try {
            $data['comment_id']=$id;
            $data['content']=htmlspecialchars($content);
            $data['content']= preg_replace_callback('/[\xf0-\xf7].{3}/', function ($r){return '@E' . base64_encode($r[0]);}, $data['content']);
            $data['creater_time']=time();
            $data['creater_user'] = USER_ID;
            $storemodel->allowField(true)->save($data);
            $this->commit();
            return $res['code'] = 1;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }


    }

    /**
     *商家  更改是否屏蔽用户评论状态
     * @author ly
     * @date 2019-10-30
     */
    public function edit($isshow='',$commentid='')
    {
        $usercommentmodel=new UserCommentModel();
        $this->startTrans();
        try {
            if($isshow==1){
                $data['is_show']=2;
            }else{
                $data['is_show']=1;
            }
            $usercommentmodel->where('comment_id',$commentid)->update($data);
            $this->commit();
            return $res['code'] = 1;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }


    }

    /**
     *商家  更改是否优质评论
     * @author ly
     * @date 2019-10-30
     */
    public function editIsgood($isgood='',$commentid='')
    {
        $usercommentmodel=new UserCommentModel();
        $this->startTrans();
        try {
            if($isgood==1){
                $data['is_good']=0;
            }else{
                $data['is_good']=1;
            }
            $usercommentmodel->where('comment_id',$commentid)->update($data);
            $this->commit();
            return $res['code'] = 1;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }


    }
}
