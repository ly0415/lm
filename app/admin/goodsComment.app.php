<?php

/**
 * 商品评论列表
 * @author wh
 * @date 2017-8-14
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class GoodsCommentApp extends BackendApp {

    private $goodsCommentMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->goodsCommentMod = &m('goodsComment');
    }

    /**
     * 商品评论列表展示
     * @author wangs
     * @date 2017-11-9
     */
    public function commetList() {
        $goodsName = !empty($_REQUEST['goods_name']) ? htmlspecialchars(trim($_REQUEST['goods_name'])) : '';
        $storeName = !empty($_REQUEST['store_name']) ? htmlspecialchars(trim($_REQUEST['store_name'])) : '';
        $this->assign('goodsName', $goodsName);
        $this->assign('storeName', $storeName);
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $where = '  where t.distinguish = 0 ';
        if (!empty($goodsName)) {
            $where .= '   and   gl.`goods_name`  like  "%' . $goodsName . '%"';
        }
        if (!empty($storeName)) {
            $where .= '   and   t.`store_name`  like  "%' . $storeName . '%"';
        }
        $sql = 'SELECT  c.`comment_id`,c.`username`,c.`goods_rank`,c.`goods_id`,c.`add_time`,c.`order_id`,c.`content`,gl.`goods_name`,t.`store_name`   FROM   ' . DB_PREFIX . 'goods_comment  AS c
                LEFT JOIN   ' . DB_PREFIX . 'store_goods  AS g   ON c.`goods_id` =  g.`id`
                LEFT JOIN   ' . DB_PREFIX . 'goods_lang  AS gl   ON gl.`goods_id` =  c.`goods_id`  and gl.lang_id = ' . $this->lang_id . '
                LEFT  JOIN  ' . DB_PREFIX . 'store_lang  AS t  ON c.`store_id` = t.`store_id` and t.lang_id = ' . $this->lang_id . $where . ' order by   c.`comment_id`  desc ';
        $data = $this->goodsCommentMod->querySqlPageData($sql);
        $list = $data['list'];
        foreach ($list as &$val) {
            //商品名称
            $val['gname'] = mb_substr($val['goods_name'], 0, 20, 'utf-8');
            $val['gcontent'] = mb_substr($val['content'], 0, 25, 'utf-8');
        }
        $this->assign('p', $p);
        $this->assign('list', $list);
        $this->assign('page_html', $data['ph']);
        $this->display('goodsComm/commentList.html');
    }

    /**
     * 商品评论查看
     * @author wangs
     * @date 2017-11-9
     */
    public function detail() {
        $id = $_REQUEST['id'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        if (!empty($id)) {
            $where = '  where  c.`comment_id` = ' . $id . ' and t.distinguish = 0 ';
        }
        $sql = 'select c.comment_id, c.username, c.goods_rank, c.goods_id, c.add_time, c.order_id, c.content, c.revert, gl.goods_name, t.store_name, c.img from '
                . DB_PREFIX . 'goods_comment  AS c left join '
                . DB_PREFIX . 'store_goods AS g ON c.goods_id = g.id left join '
                . DB_PREFIX . 'goods_lang  AS gl   ON gl.`goods_id` =  c.`goods_id`  and gl.lang_id = ' . $this->lang_id . '  left join '
                . DB_PREFIX . 'store_lang AS t ON c.store_id = t.store_id  and t.lang_id = ' . $this->lang_id . $where;
        $data = $this->goodsCommentMod->querySql($sql);
        //评分 
        if (!empty($data[0]['goods_rank'])) {
            $px = ' style="width:' . ($data[0]['goods_rank'] * 22) . 'px;"';
        } else {
            $px = 'style="width:0px;"';
        }
        //图片
        if (!empty($data[0]['img'])) {
            $imgList = explode(',', $data[0]['img']);
        } else {
            $imgList = array();
        }
        $this->assign('imgList', $imgList);
        $this->assign('px', $px);
        $this->assign('p', $p);
        $this->assign('data', $data[0]);
        $this->display('goodsComm/commentDetail.html');
    }

    /**
     * 商品评论删除
     * @author wangs
     * @date 2017-11-9
     */
    public function dele() {
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        if (empty($id)) {
            $this->setData(array(), '0', $this->langDataBank->public->system_error);
        }
        // 删除表数据
        $where = 'comment_id  in(' . $id . ')';
        $res = $this->goodsCommentMod->doDrops($where);
        if ($res) {   //删除成功
            $this->addLog('删除评论');
            $this->setData(array(), '1', $this->langDataBank->public->drop_success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->public->drop_fail);
        }
    }

}
