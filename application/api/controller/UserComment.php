<?php

namespace app\api\controller;

use app\api\model\UserComment   as UserCommentModel;

/**
 * 用户评论控制器
 * @author  liy
 * @date    2019-10-29
 */
class UserComment extends Controller{



    /**
     * 用户评论添加
     * $order_sn   订单编号
     * $user_id    用户ID
     * $store_id    店铺ID
     * @author  liy
     * @date    2019-10-29
     */
    public function addComment($user_id='',$gid='',$rec_id='',$order_sn='',$order_id='',$star_num='',$evaluete_content='',$goods_images='',$store_id='')
    {
        $model = new UserCommentModel;
        if ($model->addComment($user_id,$gid,$rec_id,$order_sn,$order_id,$star_num,$evaluete_content,$goods_images,$store_id)){
            return $this->renderSuccess('评论成功');
        }
        return $this->renderError($model->getError() ?: '评论失败');
    }
    /**
     * 用户评论
     * @author  liy
     * @date    2019-10-29
     */
    public function getList($order_sn='',$store_id='',$user_id='',$is_good='',$image='',$page='')
    {
        $model = new UserCommentModel;
        $list = $model->getList($order_sn,$store_id,$user_id,$is_good,$image,$page);
        return $this->renderSuccess( ['list'=>$list]);
    }


  /**
     * 用户评论 获得订单
     * @author  liy
     * @date    2019-10-29
     */
    public function getOrderDetail($order_sn='')
    {
        $model = new UserCommentModel;
        //获取订单详情
        $orderDetail        = $model->getOrderDetail($order_sn);
        return $this->renderSuccess( ['orderDetail'=>$orderDetail]);
    }

    /**
     * 用户评论总数
     * @author  liy
     * @date    2019-10-29
     */
    public function getTotal($store_id='')
    {
        $model = new UserCommentModel;
        //获取订单详情
        $total  = $model->gettotal($store_id);
        return $this->renderSuccess( $total);
    }
}