<?php

namespace app\store\controller\comment;

use app\store\controller\Controller;
use app\store\model\comment\UserComment as UserCommentModel;

/**
 *评价控制器
 * Class GoodsCategory
 * @package app\store\controller\source
 */
class UserComment extends Controller
{
    /**
     *评价列表
     * @author ly
     * @date 2019-10-30
     */
    public function index()
    {
        $model = new UserCommentModel;
        if (!$this->request->isAjax()) {
            $list = $model->getList($this->yoshop_store['store_id']);
            return $this->fetch('index', compact('list'));
        }
    }

    /**
     *商家恢复
     * @author ly
     * @date 2019-10-30
     */
    public function add($id='',$content='')
    {
        $model = new UserCommentModel;
     if ($model->add($id,$content)) {
        return $this->renderSuccess('添加成功', url('comment.user_comment/index'));
    }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     *商家更改状态
     * @author ly
     * @date 2019-10-30
     */
    public function edit($isshow='',$commentid='')
    {
        $model = new UserCommentModel;
        if ($model->edit($isshow,$commentid)) {
            return $this->renderSuccess('操作成功', url('comment.user_comment/index'));
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     *商家更改用户优质评论状态
     * @author ly
     * @date 2019-10-30
     */
    public function quality($isgood='',$commentid='')
    {
        $model = new UserCommentModel;
        if ($model->editIsgood($isgood,$commentid)) {
            return $this->renderSuccess('操作成功', url('comment.user_comment/index'));
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

}
