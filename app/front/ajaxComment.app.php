<?php

/**
 * wangh
 * 异步获取商品评论
 */
class AjaxCommentApp extends BaseFrontApp {

    private $goodsCommentMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->goodsCommentMod = &m('goodsComment');
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    /**
     * 获取商品评论列表
     */
    public function getCommentList() {
        $this->load($this->lang_id, 'goods/goods');
        $a = $this->langData;
        $gid = $_REQUEST['gid'];
        $storeid = $_REQUEST['storeid'];
        $p = $_REQUEST['p'];
        $currentPage = !empty($p) ? $p : 1;
        $pagesize = $this->commpagesize; //每页显示的条数
        $start = ($currentPage - 1) * $pagesize;
        $end = $pagesize;
        $limit = '  limit  ' . $start . ',' . $end;
        $where = '  where goods_id =' . $gid . '  and  store_id =' . $storeid;
        $sql = 'select  comment_id, username, goods_rank, add_time, content, img, revert  from ' . DB_PREFIX . 'goods_comment  ' . $where . ' order by comment_id desc ' . $limit;
 
        $data = $this->goodsCommentMod->querySql($sql);
        $data = array_map(function($vo) {
            $vo['img'] = explode(',', $vo['img']);
            return $vo;
        }, $data);
        $this->assign('data', $data);
        $this->display('ajaxcomment/commentlist.html');
    }

}
