<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/26
 * Time: 15:44
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class GroupbuyApp extends BaseStoreApp {

    private $lang_id;
    private $groupbuyMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
        $this->groupbuyMod = &m('groupbuy');
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    /**
     * 团购的列表
     */
    public function grouplist() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $storeid = $this->storeId;
        $startTime = !empty($_REQUEST['start_time']) ? strtotime($_REQUEST['start_time']) : '';
        $endTime = !empty($_REQUEST['end_time']) ? strtotime($_REQUEST['end_time']) : '';
        $isend = !empty($_REQUEST['is_end']) ? $_REQUEST['is_end'] : 0;
        $curtime = time();
        if (!empty($startTime) && !empty($endTime) && ($startTime > $endTime)) {
            $t = $endTime;
            $endTime = $startTime;
            $startTime = $t;
        }
        if (!empty($endTime)) {
            $endTime = $endTime + 24 * 3600 - 1;
        }
        $this->assign('isend', $isend);
        $this->assign('stime', date('Y/m/d', $startTime));
        $this->assign('etime', date('Y/m/d', $endTime));
        //where 条件
        $where = '  where  store_id = ' . $storeid . ' and  mark =1';
        //活动的状态的更改
        $sqle = 'select  * from  ' . DB_PREFIX . 'goods_group_buy  ' . $where;
        $result = $this->groupbuyMod->querySql($sqle);
        foreach ($result as $val) {
            //活动的状态的更改
            if ($curtime < $val['start_time']) {
                //未开始
                $this->groupbuyMod->doEdit($val['id'], array('is_end' => 3));
            } else if (( $curtime > $val['start_time'] ) && ( $curtime < $val['end_time'] )) {
                //进行中
                $this->groupbuyMod->doEdit($val['id'], array('is_end' => 1));
            } else if ($curtime > $val['end_time']) {
                //结束
                $this->groupbuyMod->doEdit($val['id'], array('is_end' => 2));
            }
        }
        // 筛选条件
        if (!empty($startTime)) {
            $where .= '  and  start_time >= ' . $startTime;
        }
        if (!empty($endTime)) {
            $where .= '  and  end_time <= ' . $endTime;
        }
        if (!empty($isend)) {
            $where .= '  and is_end =' . $isend;
        }
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "goods_group_buy " . $where;
        $totalCount = $this->groupbuyMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;

        $sql = 'select  * from  ' . DB_PREFIX . 'goods_group_buy  ' . $where . '  order by id desc';
        $res = $this->groupbuyMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        $data = $res['list'];
        foreach ($data as $key => $val) {
            $data[$key]['cantan_num'] = $val['virtual_num'] + $val['buy_num'];
            $data[$key]['title'] = mb_substr($val['title'], 0, 10, 'utf-8');
            $data[$key]['goods_name'] = mb_substr($val['goods_name'], 0, 10, 'utf-8');
            //活动的状态显示
            if ($this->lang_id == 1) {
                switch ($val['is_end']) {
                    case 1 :
                        $data[$key]['status'] = '<span class="bg-had">Have in hand</span>';
                        break;
                    case 2 :
                        $data[$key]['status'] = '<span class="bg-fail">Has ended</span>';
                        break;
                    case 3 :
                        $data[$key]['status'] = '<span class="bg-no">Not started</span>';
                        break;
                    default:
                        $data[$key]['status'] = '<span class="bg-fail">Has ended</span>';
                }
            } else {
                switch ($val['is_end']) {
                    case 1 :
                        $data[$key]['status'] = '<span class="bg-had">进行中</span>';
                        break;
                    case 2 :
                        $data[$key]['status'] = '<span class="bg-fail">已结束</span>';
                        break;
                    case 3 :
                        $data[$key]['status'] = '<span class="bg-no">未开始</span>';
                        break;
                    default:
                        $data[$key]['status'] = '<span class="bg-fail">已结束</span>';
                }
            }
            $data[$key]['sort_id'] = $key + 20 * ($p - 1) + 1; //正序
        }
        $this->assign('p', $p);
        $this->assign('list', $data);
        $this->assign('page_html', $res['ph']);
        $this->assign('lang_id', $this->lang_id);
        if ($this->lang_id == 1) {
            $this->display('groupbuy/grouplist_1.html');
        } else {
            $this->display('groupbuy/grouplist.html');
        }
    }

    /**
     * 团购的添加
     */
    public function add() {
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $this->assign('p', $p);
        $this->assign('act', 'grouplist');
        $this->assign('lang_id', $this->lang_id);
        if ($this->lang_id == 1) {
            $this->display('groupbuy/groupadd_1.html');
        } else {
            $this->display('groupbuy/groupadd.html');
        }
    }

    /**
     * 处理添加
     */
    public function doadd() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $lang_id = !empty($_REQUEST['lang_id']) ? (int) ($_REQUEST['lang_id']) : '0';
        $storeid = $this->storeId;
         $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $title = !empty($_REQUEST['title']) ? addslashes(trim($_REQUEST['title'])) : '';
        $startTime = !empty($_REQUEST['start_time']) ? strtotime($_REQUEST['start_time']) : '';
        $endTime = !empty($_REQUEST['end_time']) ? strtotime($_REQUEST['end_time']) : '';
        $groupprice = !empty($_REQUEST['group_goods_price']) ? $_REQUEST['group_goods_price'] : '';
        $groupnum = !empty($_REQUEST['group_goods_num']) ? $_REQUEST['group_goods_num'] : '';
        $virnum = !empty($_REQUEST['virtual_num']) ? $_REQUEST['virtual_num'] : '';
        $imgurl = !empty($_REQUEST['imgurl']) ? $_REQUEST['imgurl'] : '';
        $gname = !empty($_REQUEST['gname']) ? addslashes($_REQUEST['gname']) : '';
        $gprice = !empty($_REQUEST['gprice']) ? $_REQUEST['gprice'] : '';
        $key = !empty($_REQUEST['key']) ? $_REQUEST['key'] : '';
        $key_name = !empty($_REQUEST['key_name']) ? $_REQUEST['key_name'] : '';
        $gid = !empty($_REQUEST['gid']) ? $_REQUEST['gid'] : '';

        if (empty($title)) {
            $this->setData(array(), '0', $a['group__title']);
        }
        if (empty($startTime)) {
            $this->setData(array(), '0', $a['group__start']);
        }
        if (empty($endTime)) {
            $this->setData(array(), '0', $a['group__End']);
        }
        if ($startTime > $endTime) {
            $this->setData(array(), '0', $a['group__Small']);
        }
//开始时间要大于当前时间
        if (($startTime < time()) || ($endTime < time())) {
            $this->setData(array(), '0', $a['group__large']);
        }
        if ($startTime == $endTime) {
            if ($lang_id == 0) {
                $this->setData(array(), '0', '开始时间不能等于结束时间');
            } else {
                $this->setData(array(), '0', 'The start time is not equal to the end time');
            }
        }
        if (empty($groupprice)) {
            $this->setData(array(), '0', $a['group__haveto']);
        }
//
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $groupprice)) {
            $this->setData(array(), '0', $a['group__just']);
        }
        if (empty($groupnum)) {
            $this->setData(array(), '0', $a['group__Number']);
        }
        if (!preg_match('/^[1-9]\d*$/', $groupnum)) {
            $this->setData(array(), '0', $a['group__Need']);
        }
        if (empty($gid)) {
            $this->setData(array(), '0', $a['group__Choice']);
        }
//判断所选商品库存
//        $goodsStorage = $this->getGoodsKucun($gid, $key);
//
//        if ($groupnum > $goodsStorage) {
//            $this->setData(array(), '0', $a['group__Stock']);
//        }
//
        if (empty($virnum)) {
            $this->setData(array(), '0', $a['group__fictitious']);
        }
        if (!preg_match('/^[1-9]\d*$/', $virnum)) {
            $this->setData(array(), '0', $a['group__fictitious1']);
        }
        if ($virnum > $groupnum) {
            $this->setData(array(), '0', $a['group__fictitious2']);
        }

//判断该商品是不是在团购中
        $resGoods = $this->getGroupGoods($gid);
        if (!empty($resGoods)) {
            $this->setData(array(), '0', $a['group__this']);
        }

        $insertD = array(
            'store_id' => $storeid,
            'goods_id' => $gid,
            'title' => $title,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'group_goods_price' => $groupprice,
            'group_goods_num' => $groupnum,
            'virtual_num' => $virnum,
            'original_img' => $imgurl,
            'goods_name' => $gname,
            'goods_price' => $gprice,
            'goods_spec_key' => $key,
            'key_name' => $key_name,
            'is_end' => 0,
            'mark' => 1,
            'add_time' => time(),
        );

        $res = $this->groupbuyMod->doInsert($insertD);

        if ($res) {
            $info['url'] = "store.php?app=groupbuy&act=grouplist&lang_id={$lang_id}&p={$p}";
            $this->setData($info, 1, $a['add_Success']);
        } else {
            $this->setData(array(), '0', $a['add_fail']);
        }
    }

    /**
     * 获取参团商品的信息
     * @param $goodsid
     * @param int $id
     */
    public function getGroupGoods($goodsid, $id = 0) {

        $where = 'where  store_id = ' . $this->storeId . '  and  mark=1 and is_end in(1,3) ';

        if (empty($id)) {
            $where .= '  and goods_id=' . $goodsid;
        } else {
            $where .= '  and goods_id =' . $goodsid . '  and  id !=' . $id;
        }

        $sql = 'select  id,store_id  from  ' . DB_PREFIX . 'goods_group_buy  ' . $where;

        $res = $this->groupbuyMod->querySql($sql);

        return $res;
    }

    /**
     * 获取商品库存
     * @param $goodsid
     * @param $key
     *
     */
    public function getGoodsKucun($goodsid, $key) {
        $storeGoodsMod = &m('areaGood');

        if (empty($key)) {
            $sql = 'SELECT  id,goods_storage  FROM  ' . DB_PREFIX . 'store_goods WHERE id=' . $goodsid;
        } else {
            $sql = 'SELECT  s.id,sp.`store_goods_id`,sp.`key`,sp.`goods_storage`,sp.`store_goods_id`   FROM  ' . DB_PREFIX . 'store_goods AS s
                     LEFT JOIN  ' . DB_PREFIX . 'store_goods_spec_price AS sp ON s.id = sp.`store_goods_id`
                     WHERE  sp.`store_goods_id` = ' . $goodsid . ' AND sp.`key` = "' . $key . '"';
        }

        $res = $storeGoodsMod->querySql($sql);

        return $res[0]['goods_storage'];
    }

    /**
     * 团购的编辑
     */
    public function edit() {
        $id = !empty($_REQUEST['id']) ? (int) ($_REQUEST['id']) : '0';
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $sql = 'select  *  from  ' . DB_PREFIX . 'goods_group_buy where id =' . $id;
        $data = $this->groupbuyMod->querySql($sql);
        $this->assign('data', $data[0]);
        $this->assign('act', 'grouplist');
        $this->assign('p', $p);
        $this->assign('lang_id', $this->lang_id);
        if ($this->lang_id == 1) {
            $this->display('groupbuy/groupedit_1.html');
        } else {
            $this->display('groupbuy/groupedit.html');
        }
    }

    public function doedit() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $id = $_REQUEST['id'];
        $lang_id = !empty($_REQUEST['lang_id']) ? (int) ($_REQUEST['lang_id']) : '0';
        $storeid = $this->storeId;
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $title = !empty($_REQUEST['title']) ? addslashes(trim($_REQUEST['title'])) : '';
        $startTime = !empty($_REQUEST['start_time']) ? strtotime($_REQUEST['start_time']) : '';
        $endTime = !empty($_REQUEST['end_time']) ? strtotime($_REQUEST['end_time']) : '';
        $groupprice = !empty($_REQUEST['group_goods_price']) ? $_REQUEST['group_goods_price'] : '';
        $groupnum = !empty($_REQUEST['group_goods_num']) ? $_REQUEST['group_goods_num'] : '';
        $virnum = !empty($_REQUEST['virtual_num']) ? $_REQUEST['virtual_num'] : '';
        $imgurl = !empty($_REQUEST['imgurl']) ? $_REQUEST['imgurl'] : '';
        $gname = !empty($_REQUEST['gname']) ? addslashes($_REQUEST['gname']) : '';
        $gprice = !empty($_REQUEST['gprice']) ? $_REQUEST['gprice'] : '';
        $key = !empty($_REQUEST['key']) ? $_REQUEST['key'] : '';
        $key_name = !empty($_REQUEST['key_name']) ? $_REQUEST['key_name'] : '';
        $gid = !empty($_REQUEST['gid']) ? $_REQUEST['gid'] : '';

        if (empty($title)) {
            $this->setData(array(), '0', $a['group__title']);
        }
        if (empty($startTime)) {
            $this->setData(array(), '0', $a['group__start']);
        }
        if (empty($endTime)) {
            $this->setData(array(), '0', $a['group__End']);
        }
        if ($startTime > $endTime) {
            $this->setData(array(), '0', $a['group__Small']);
        }
//开始时间要大于当前时间
        if (($startTime < time()) || ($endTime < time())) {
            $this->setData(array(), '0', $a['group__large']);
        }
        if ($startTime == $endTime) {
            if ($lang_id == 0) {
                $this->setData(array(), '0', '开始时间不能等于结束时间');
            } else {
                $this->setData(array(), '0', 'The start time is not equal to the end time');
            }
        }
        if (empty($groupprice)) {
            $this->setData(array(), '0', $a['group__haveto']);
        }
//
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $groupprice)) {
            $this->setData(array(), '0', $a['group__just']);
        }
        if (empty($groupnum)) {
            $this->setData(array(), '0', $a['group__Number']);
        }
        if (!preg_match('/^[1-9]\d*$/', $groupnum)) {
            $this->setData(array(), '0', $a['group__Need']);
        }
//
        if (empty($virnum)) {
            $this->setData(array(), '0', $a['group__fictitious']);
        }
        if (!preg_match('/^[1-9]\d*$/', $virnum)) {
            $this->setData(array(), '0', $a['group__fictitious1']);
        }
        if ($virnum > $groupnum) {
            $this->setData(array(), '0', $a['group__fictitious2']);
        }
        if (empty($gid)) {
            $this->setData(array(), '0', $a['group__Choice']);
        }
//判断该商品是不是在团购中
        $resGoods = $this->getGroupGoods($gid, $id);
        if (!empty($resGoods)) {
            $this->setData(array(), '0', $a['group__this']);
        }

        $editD = array(
            'store_id' => $storeid,
            'goods_id' => $gid,
            'title' => $title,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'group_goods_price' => $groupprice,
            'group_goods_num' => $groupnum,
            'virtual_num' => $virnum,
            'original_img' => $imgurl,
            'goods_name' => $gname,
            'goods_price' => $gprice,
            'goods_spec_key' => $key,
            'key_name' => $key_name,
            'is_end' => 0,
            'mark' => 1,
            'add_time' => time(),
        );
        $res = $this->groupbuyMod->doEdit($id, $editD);
        if ($res) {
            $info['url'] = "store.php?app=groupbuy&act=grouplist&lang_id={$lang_id}&p={$p}";
            $this->setData($info, 1, $a['edit_Success']);
        } else {
            $this->setData(array(), '0', $a['edit_fail']);
        }
    }

    /**
     * 团购的删除
     */
    public function dele() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        if (empty($id)) {
            $this->setData(array(), '0', $a['System_error']);
        }
// 伪删除表数据
        $res = $this->groupbuyMod->doMark($id);
        if ($res) {   //删除成功
            $this->setData(array(), '1', $a['delete_Success']);
        } else {
            $this->setData(array(), '0', $a['delete_fail']);
        }
    }

}
