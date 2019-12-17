<?php

/**
 * 商品评论列表
 * @author wh
 * @date 2017-8-14
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class GoodsCommentApp extends BaseStoreApp {

    private $goodsCommentMod;
    private $lang_id;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->goodsCommentMod = &m('goodsComment');
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
    }

    /**
     * 商品评论列表展示
     * @author wh
     * @date 2017-8-14
     */
    public function commetList() {
        //中英切换
        $this->assign('lang_id', $this->lang_id);
        $goodsName = !empty($_REQUEST['goods_name']) ? htmlspecialchars(trim($_REQUEST['goods_name'])) : '';
        $store_id = $this->storeId;
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $this->assign('goodsName', $goodsName);
        $where = '  where 1=1 and  c.store_id = ' . $store_id;
        if (!empty($goodsName)) {
            $where .= ' and g.goods_name like  "%' . $goodsName . '%"';
        }
        $sql = 'select  c.comment_id, c.username, c.goods_rank, c.goods_id, c.add_time, c.order_id, c.content, gl.goods_name  from '
                . DB_PREFIX . 'goods_comment  AS c left join '
                . DB_PREFIX . 'store_goods  AS g   ON c.goods_id =  g.id  LEFT JOIN   ' 
                . DB_PREFIX . 'goods_lang  AS gl   ON gl.`goods_id` =  c.`goods_id`  and gl.lang_id = ' . $this->defaulLang .  $where . ' order by c.comment_id desc';
//        print_r($sql);exit;
        $data = $this->goodsCommentMod->querySqlPageData($sql);
        $list = $data['list'];
        foreach ($list as &$val) {
            //商品名称
            $val['gname'] = mb_substr($val['goods_name'], 0, 20, 'utf-8');
            $val['gcontent'] = mb_substr($val['content'], 0, 35, 'utf-8');
        }
        $this->assign('p', $p);
        $this->assign('list', $list);
        $this->assign('page_html', $data['ph']);
        $this->display('goodsComm/commentList.html');
    }

    /**
     * 商品评论查看
     * @author wh
     * @date 2017-8-14
     */
    public function detail() {
        $id = $_REQUEST['id'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        if (!empty($id)) {
            $where = '  where  c.`comment_id` = ' . $id . ' and t.distinguish = 0 and t.lang_id=' . $this->defaulLang;
        }
        $sql = 'select  c.comment_id, c.username, c.goods_rank, c.goods_id, c.add_time, c.order_id, c.content, c.revert, gl.goods_name, t.store_name, c.img   from   '
                . DB_PREFIX . 'goods_comment  AS c left join '
                . DB_PREFIX . 'store_goods  AS g ON c.goods_id = g.id left join '
                . DB_PREFIX . 'store_lang  AS t ON c.store_id = t.store_id left join ' 
                . DB_PREFIX . 'goods_lang  AS gl   ON gl.`goods_id` =  c.`goods_id`  and gl.lang_id = ' . $this->defaulLang . $where;
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
        $this->assign('p', $p);
        $this->assign('imgList', $imgList);
        $this->assign('px', $px);
        $this->assign('data', $data[0]);
        $this->assign('lang_id', $this->lang_id);
        $this->display('goodsComm/commentDetail.html');
    }

    /**
     * 商品评论回复
     * @author wh
     * @date 2017-8-14
     */
    public function revert() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
//        print_r($_REQUEST);exit;
        $id = $_REQUEST['id'];
        $d = !empty($_REQUEST['data']) ? htmlspecialchars(trim($_REQUEST['data'])) : '';
        $data = array(
            'key' => 'comment_id',
            'revert' => $d
        );
        $res = $this->goodsCommentMod->doEdit($id, $data);
        if ($res) {
            $this->setData(array(), '1', $a['edit_Successful']);
        } else {
            $this->setData(array(), '0', $a['edit_Reply']);
        }
    }

}
