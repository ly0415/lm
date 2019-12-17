<?php

/**
 * 广告列表
 * @author  wangshuo
 * @date 2017-9-13
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class AdvApp extends BaseStoreApp {

    public $storeGoodsMod;
    private $advMod;
    private $lang_id;
    private $pagesize = 10;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->storeGoodsMod = &m('storeGoods');
        $this->advMod = &m('adv');
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    /**
     * 广告列表
     * @author  wangshuo
     * @date 2017-9-14
     */
    public function advList() {
        $this->assign('lang_id', $this->lang_id);
        $pstid = $_REQUEST['pstid'];  //广告位置id
        $adName = !empty($_REQUEST['ad_name']) ? htmlspecialchars(trim($_REQUEST['ad_name'])) : '';
        $english_name = !empty($_REQUEST['ad_english_name']) ? htmlspecialchars(trim($_REQUEST['ad_english_name'])) : '';


        if (!empty($pstid)) {
            $options = $this->getOptions($pstid);
        } else {
            $options = $this->getOptions();
        }
        $storeId = $this->storeId;
        $where = '  where is_open =1 and p.store_id =' . $storeId;

        if ($this->lang_id == 1) {
            $where .= '   and  a.`ad_english_name`  like  "%' . $english_name . '%"';
        } else {
            $where .= '   and   a.ad_name  like "%' . $adName . '%"';
        }
        if (!empty($pstid)) {
            $where .= '   and   a.ps_id = ' . $pstid;
        }
        $sql = 'SELECT   a.*,p.`position_id`,p.`position_num`,p.`position_name`,p.`english_name`,p.`ad_height`,p.`ad_width`
                FROM  ' . DB_PREFIX . 'ad  AS a
                LEFT JOIN  ' . DB_PREFIX . 'ad_position  AS p  ON a.`ps_id` = p.`position_id` ' . $where;
        $sql .= '   order by   a.ad_id  desc';
        $res = $this->advMod->querySqlPageData($sql, array("pre_page" => 10, "is_sql" => false, "mode" => 1));
        $list = $res['list'];

        $this->assign('list', $list);
        $this->assign('page_html', $res['ph']);
        $this->assign('adName', $adName);
        $this->assign('english_name', $english_name);
        $this->assign('options', $options);
        if ($this->lang_id == 1) {
            $this->display('adv/advList_1.html');
        } else {
            $this->display('adv/advList.html');
        }
    }

    public function getOptions($selected = 0) {
        //中英切换
        if ($_GET['lang_id'] == 0) {

            $option = '';
            $option .= '<option value="0" >--请选择广告位置--</option>';
            $advpMod = &m('advPosition');
            $store_id = $this->storeId;
            $where = '  where is_open = 1 and  store_id =  ' . $store_id;
            $sql = 'select  position_id,position_name  from   bs_ad_position' . $where;
            $data = $advpMod->querySql($sql);
            if (!empty($selected)) {
                foreach ($data as $val) {
                    if ($selected == $val['position_id']) {
                        $option .= '<option value=' . $val['position_id'] . ' selected  >' . $val['position_name'] . '</option>';
                    } else {
                        $option .= '<option value=' . $val['position_id'] . '  >' . $val['position_name'] . '</option>';
                    }
                }
            } else {
                foreach ($data as $val) {
                    $option .= '<option value=' . $val['position_id'] . ' >' . $val['position_name'] . '</option>';
                }
            }
            return $option;
        } else {
            $option = '';
            $option .= '<option value="0" >--Please select an ad slot--</option>';
            $advpMod = &m('advPosition');
            $store_id = $this->storeId;
            $where = '  where is_open = 1 and  store_id =  ' . $store_id;
            $sql = 'select  position_id,english_name  from   bs_ad_position' . $where;
            $data = $advpMod->querySql($sql);
            if (!empty($selected)) {
                foreach ($data as $val) {
                    if ($selected == $val['position_id']) {
                        $option .= '<option value=' . $val['position_id'] . ' selected  >' . $val['english_name'] . '</option>';
                    } else {
                        $option .= '<option value=' . $val['position_id'] . '  >' . $val['english_name'] . '</option>';
                    }
                }
            } else {
                foreach ($data as $val) {
                    $option .= '<option value=' . $val['position_id'] . ' >' . $val['english_name'] . '</option>';
                }
            }
            return $option;
        }
    }

//广告商品
    public function getOptionsGoods($selected = 0) {
        $option = '';
        $option .= '<option value="0" >--请选择广告商品--</option>';
        $storeGoodsMod = &m('storeGoods');
        $store_id = $this->storeId;
        $where = '  where  store_id =  ' . $store_id;
        $sql = 'select id,goods_name  from   bs_store_goods' . $where;
        $data = $storeGoodsMod->querySql($sql);
        if (!empty($selected)) {
            foreach ($data as $val) {
                if ($selected == $val['id']) {
                    $option .= '<option value=' . $val['id'] . ' selected  >' . $val['goods_name'] . '</option>';
                } else {
                    $option .= '<option value=' . $val['id'] . '  >' . $val['goods_name'] . '</option>';
                }
            }
        } else {
            foreach ($data as $val) {
                $option .= '<option value=' . $val['id'] . ' >' . $val['goods_name'] . '</option>';
            }
        }
        return $option;
    }

    /**
     * 广告列表添加
     * @author  wangshuo
     * @date 2017-9-15
     */
    public function add() {
        //中英切换
        if ($_GET['lang_id'] == 0) {
            $str = 'ad_name';
        } else {
            $str = 'ad_english_name';
        }
        $this->assign('lang_id', $this->lang_id);
        $options = $this->getOptions();
        $this->assign('options', $options);
//        $store_goods = $this->getOptionsGoods();
//        $this->assign('store_goods', $store_goods);
        if ($this->lang_id == 1) {
            $this->display('adv/advAdd_1.html');
        } else {
            $this->display('adv/advAdd.html');
        }
    }

    public function doAdd() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $lang_id = !empty($_REQUEST['lang_id']) ? (int) ($_REQUEST['lang_id']) : '0';
        $adName = !empty($_REQUEST['ad_name']) ? htmlspecialchars(trim($_REQUEST['ad_name'])) : '';
        $english_name = !empty($_REQUEST['ad_english_name']) ? htmlspecialchars(trim($_REQUEST['ad_english_name'])) : '';
        $psId = $_REQUEST['ps_id'];
        $goods_id = $_REQUEST['goods_id'];
        $imageId = $_REQUEST['image_id'];
        $storeId = $this->storeId;

        if (empty($adName)) {
            $this->setData(array(), '0', $a['gg_name']);
        }
        if (mb_strlen($adName, 'UTF-8') > 15) {
            $this->setData(array(), '0', $a['gg_names']);
        }
        if (empty($english_name)) {
            $this->setData(array(), '0', $a['gg_english_name']);
        }
        if (mb_strlen($english_name, 'UTF-8') > 35) {
            $this->setData(array(), '0', $a['gg_english_names']);
        }
        if (empty($psId)) {
            $this->setData(array(), '0', $a['gg_psId']);
        }
        if (empty($goods_id)) {
            $this->setData(array(), '0', $a['gg_adLink']);
        }
        if (empty($imageId)) {
            $this->setData(array(), '0', $a['gg_imageId']);
        }
        //获取广告位置编号
        $postionMod = &m('advPosition');
        $p_info = $postionMod->getOne(array("cond" => "position_id=" . $psId));
        //验证广告编辑
        $d = $this->getAdvInfo($storeId, $p_info['position_num']);
        if (empty($d)) {
            $this->setData(array(), '0', $a['gg_class_name']);
        }
        $data = array(
            'ad_name' => $adName,
            'ad_english_name' => $english_name,
            'ps_id' => $psId,
            'goods_id' => $goods_id,
//            'ad_link' => $adLink,
            'ad_code' => $imageId,
            'store_id' => $storeId
        );
        $res = $this->advMod->doInsert($data);
        if ($res) {
            $info['url'] = "store.php?app=adv&act=advList&lang_id={$lang_id}";
            $this->setData($info, $status = 1, $a['add_Success']);
        } else {
            $this->setData(array(), '0', $a['add_fail']);
        }
    }

    /**
     * 商品的单选弹窗
     */
    public function goodsDialog() {
        //获取第一页数据
        $lang_id = $_REQUEST['lang_id'];
        $storeid = $this->storeId;
        $where = '  where  l.`lang_id` =29  and g.is_on_sale =1 and g.mark=1 and g.store_id = ' . $storeid;
        $sql = 'select  COUNT(*)  as total  from  ' . DB_PREFIX . 'store_goods  as g
                LEFT JOIN  bs_goods_lang AS l ON g.`goods_id` = l.`goods_id`  ' . $where;
        $res = $this->storeGoodsMod->querySql($sql);
        $total = $res[0]['total'];
        $pagesize = $this->pagesize;
        $totalpage = ceil($total / $pagesize);
        if (empty($totalpage)) {
            $totalpage = 1;
        }
        $this->assign('totalpage', $totalpage);
        //分页定义
        $currentPage = 1;
        $start = ($currentPage - 1) * $pagesize;
        $end = $pagesize;
        $limit = '    limit  ' . $start . ',' . $end;
        $sql = 'select  g.id,l.goods_name,g.market_price,g.shop_price,gl.original_img,g.goods_id  from  ' . DB_PREFIX . 'store_goods  as g
                LEFT JOIN  bs_goods_lang AS l ON g.`goods_id` = l.`goods_id`  LEFT JOIN  bs_goods AS gl ON g.`goods_id` = gl.`goods_id` ' . $where . $limit;
        $data = $this->storeGoodsMod->querySql($sql);
        $this->assign('data', $data);
        if ($lang_id == 1) {
            $this->display('adv/goodsdialog_1.html');
        } else {
            $this->display('adv/goodsdialog.html');
        }
    }

    /**
     * 搜索物品，统计条数
     * @author wangh
     * @date 2017-06-26
     */
    public function totalPage() {
        $storeid = $this->storeId;
        $gname = $_REQUEST['gname'];
        $where = '  where  g.is_on_sale =1 and g.mark=1 and g.store_id = ' . $storeid;
        if (!empty($gname)) {
            $where .= '  and  l.goods_name  "%' . $gname . '%"';
        }
        $sql = 'select  COUNT(*)  as total  from  ' . DB_PREFIX . 'store_goods  as g
                LEFT JOIN  bs_goods_lang AS l ON g.`goods_id` = l.`goods_id` ' . $where;
        $res = $this->storeGoodsMod->querySql($sql);
        $total = $res[0]['total'];
        $pagesize = $this->pagesize;
        $totalpage = ceil($total / $pagesize);
        if (!empty($totalpage)) {
            echo json_encode(array('total' => $totalpage));
            exit;
        } else {
            echo json_encode(array('total' => 1));
            exit;
        }
    }

    /**
     * 广告列表编辑
     * @author  wangshuo
     * @date 2017-9-14
     */
    public function edit() {
        $storeId = $this->storeId;
        $id = $_REQUEST['id'];
        $where = '  where  ad_id =' . $id;
        $sql = 'SELECT  *    FROM  ' . DB_PREFIX . 'ad    ' . $where;
        $data = $this->advMod->querySql(
                $sql);
        $options = $this->getOptions($data[0]['ps_id']);
        $this->assign('data', $data[0]);
        $store_goods = $this->getOptionsGoods($data[0]['goods_id']);
        $this->assign('store_goods', $store_goods);
        $giftGood = $this->storeGoodsMod->getData(array('cond' => "id= {$data[0]['goods_id']}"));
        $this->assign('giftGood', $giftGood[0]); // 满件
        $this->assign('options', $options);
        //中英切换
        if ($_GET['lang_id'] == 0) {
            $str = 'ad_name';
        } else {
            $str = 'ad_english_name';
        }
        $this->assign('lang_id', $this->lang_id);
        if ($this->lang_id == 1) {
            $this->display('adv/advEdit_1.html');
        } else {
            $this->display('adv/advEdit.html');
        }
    }

    public function doEdit() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $lang_id = !empty($_REQUEST['lang_id']) ? (int) ($_REQUEST['lang_id']) : '0';
        $storeId = $this->storeId;
        $adId = $_REQUEST['ad_id'];
        $adName = !empty($_REQUEST['ad_name']) ? htmlspecialchars(trim($_REQUEST['ad_name'])) : '';
        $english_name = !empty($_REQUEST['ad_english_name']) ? htmlspecialchars(trim($_REQUEST['ad_english_name'])) : '';
        $psId = $_REQUEST['ps_id'];
        $goods_id = $_REQUEST['goods_id'];
//        $adLink = $_REQUEST['ad_link'];
        $imageId = $_REQUEST['image_id'];
        if (empty($adName)) {
            $this->setData(array(), '0', $a['gg_name']);
        }
        if (mb_strlen($adName, 'UTF-8') > 15) {
            $this->setData(array(), '0', $a['gg_names']);
        }
        if (empty($english_name)) {
            $this->setData(array(), '0', $a['gg_english_name']);
        }
        if (mb_strlen($english_name, 'UTF-8') > 35) {
            $this->setData(array(), '0', $a['gg_english_names']);
        }
        if (empty($psId)) {
            $this->setData(array(), '0', $a['gg_psId']);
        }
//        if (empty($adLink)) {
//            $this->setData(array(), '0', $a['gg_adLink']);
//        }
        if (empty($imageId)) {
            $this->setData(array(), '0', $a['gg_imageId']);
        }
        //获取广告位置编号
        $postionMod = &m('advPosition');
        $p_info = $postionMod->getOne(array("cond" => "position_id=" . $psId));
        //验证广告编辑
        $d = $this->getAdvInfo($storeId, $p_info['position_num'], $adId);
        if (empty($d)) {
            $this->setData(array(), '0', $a['gg_class_name']);
        }
        $data = array(
            'key' => 'ad_id',
            'ad_name' => $adName,
            'ad_english_name' => $english_name,
            'ps_id' => $psId,
            'goods_id' => $goods_id,
//            'ad_link' => $adLink,
            'ad_code' => $imageId,
            'store_id' => $storeId,
        );
        $res = $this->advMod->doEdit($adId, $data);
        if ($res) {
            $info['url'] = "store.php?app=adv&act=advList&lang_id={$lang_id}";
            $this->setData($info, $status = 1, $a['edit_Success']);
        } else {
            $this->setData(array(), '0', $a['edit_fail']);
        }
    }

    /**
     * 广告信息
     * @author wangshuo
     * @param 110000 首页banner 110001 广告位置1 110002 广告位置2
     * @date 2017-9-14
     */
    public function getAdvInfo($store_id, $position_num, $ad_id = 0) {
        if ($ad_id == 0) {
            $sql = "select a.* from " . DB_PREFIX . "ad as a left join " . DB_PREFIX . "ad_position as p on a.ps_id=p.position_id where a.store_id=" . $store_id . " and p.position_num=" . $position_num;
        } else {
            $sql = "select a.* from " . DB_PREFIX . "ad as a left join " . DB_PREFIX . "ad_position as p on a.ps_id=p.position_id where a.store_id=" . $store_id . " and p.position_num=" . $position_num . " and a.ad_id !=" . $ad_id;
        }

        $data = $this->advMod->querySql($sql);
        if (!empty($data)) {
            if ($position_num == 110000) {
                // $res = (count($data) >= 3) ? false : true;
                $res = true;
            } else {
                $res = (count($data) >= 1) ? false :
                        true;
            }
            return $res;
        } else {
            return true;
        }
    }

    /**
     * 图片上传
     * @author wangshuo
     * @date 2017-9-14
     */
    public function upload() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        if (IS_POST) {
            $fileName = $_FILES['fileName']['name'];
            $type = strtolower(substr(strrchr($fileName, '.'), 1)); //获取文件类型
            $info = array();
            if (!in_array($type, array('jpg', 'png', 'jpeg', 'gif', 'JPG', 'PNG', 'JPEG', 'GIF'))) {
                $this->setData($info, $status = 'error', $a['please_upload']);
            }
            $savePath = "upload/images/adv/" . date("Ymd");
            // 判断文件夹是否存在否则创建
            if (!file_exists($savePath)) {
                @mkdir($savePath, 0777, true);
                @ chmod($savePath, 0777);
                @exec("chmod 777  {
            $savePath}");
            }
            $filePath = $_FILES['fileName']['tmp_name']; //文件路径
            $url = $savePath . '/' . time() . '.' . $type;
            if (!is_uploaded_file($filePath)) {
                exit($a['please_temporary']);
            }
            //上传文件
            if (!move_uploaded_file($filePath, $url)) {
                $this->setData(array(), '0', $a['add_Success']);
            }
            $data = array(
                "name" => $fileName,
                "status" => 1,
                "url" => $url,
                "add_time" => time()
            );
            echo json_encode($data);
            exit;
        } else {
            $this->setData($info = array(), 2, $a['System_error']);
        }
    }

    /**
     * 广告删除
     * @author  wangshuo
     * @date 2017-9-13
     */
    public function dele() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        if (empty($id)) {
            $this->setData(array(), '0', $a['System_error']);
        }
        // 删除表数据
        $where = 'ad_id  in(' . $id . ')';
        $res = $this->advMod->doDrops($where);
        if ($res) {   //删除成功
            $this->setData(array(), '1', $a['delete_Success']);
        } else {
            $this->setData(array(), '0', $a['delete_fail']);
        }
    }

}
