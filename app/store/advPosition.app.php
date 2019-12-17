<?php

/**
 * 广告位置列表
 * @author  wh
 * @date 2017-8-16
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class AdvPositionApp extends BaseStoreApp {

    private $advPositionMod;
    private $lang_id;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->advPositionMod = &m('advPosition');
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    /**
     * 广告位置展示列表
     * @author  wangshuo
     * @date 2017-9-13
     */
    public function positionList() {
        //中英切换
        $this->assign('lang_id', $this->lang_id);
//        //搜索
//        $positionName = !empty($_REQUEST['position_name']) ? (trim($_REQUEST['position_name'])) : '';
//        $this->assign('positionName', $positionName);
//        $english_name = !empty($_REQUEST['english_name']) ? (trim($_REQUEST['english_name'])) : '';
//        $this->assign('english_name', $english_name);
        $store_id = $this->storeId;
        $where = '  where store_id =  ' . $store_id;
//        if ($this->lang_id == 1) {
//            $where .= '  and   english_name  like "%' . $english_name . '%"';
//        } else {
//            $where .= '  and   position_name  like "%' . $positionName . '%"';
//        }
        $sql = 'select * from   ' . DB_PREFIX . 'ad_position  ' . $where;
        $sql .= '  order by  position_id  desc';
        $res = $this->advPositionMod->querySqlPageData($sql);
        $list = $res['list'];
        foreach ($list as &$val) {
            //广告名称
            $val['position_desc'] = mb_substr($val['position_desc'], 0, 25, 'utf-8');
        }
        $this->assign('list', $list);
        $this->assign('page_html', $res['ph']);
        if ($this->lang_id == 1) {
            $this->display('advPosition/positionList_1.html');
        } else {
            $this->display('advPosition/positionList.html');
        }
    }

    /**
     * 切换开关
     * @author wangshuo
     * @date 2017-09-12
     */
    public function getStatus() {
        $cate_id = !empty($_REQUEST['cate_id']) ? intval($_REQUEST['cate_id']) : '0';
        $is_open = !empty($_REQUEST['is_open']) ? intval($_REQUEST['is_open']) : '0';
        $data = array(
            'is_open' => $is_open,
        );
        $rs = $this->advPositionMod->doEdit($cate_id, $data);
        if ($rs) {
            $this->setData($info = array(), $status = 1, $message = '');
        } else {
            $this->setData($info = array(), $status = 0, $message = '');
        }
    }

}
