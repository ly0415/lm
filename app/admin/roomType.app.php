<?php

/**
 * 业务类型控制器
 * @author  wanyan
 * @date 2017-07-31
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class RoomTypeApp extends BackendApp {

    private $roomTypeMod;
    private $aCLangMod;
    private $articleCtgMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->articleCtgMod = &m('articleCate');
        $this->aCLangMod = &m('articleCateLang');
        $this->roomTypeMod = &m('roomType');
    }

    /** 业务类型首页
     * wanyan
     * 2017-08-03
     */
    public function roomTypeIndex() {
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) : '';
        $this->assign('name', $name);
        $where = ' where 1=1  and  l.lang_id =' . $this->lang_id;
        //搜索
            if (!empty($name)) {
                $where .= '  and  l.type_name like "%' . $name . '%"';
            }
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "room_type ";
        $totalCount = $this->roomTypeMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;

        //展示页面
        $sql = "SELECT  r.`id`,l.`type_name`,r.`room_img`,r.`superior_id`,r.`room_adv_img`,r.`add_time`,r.`room_url`,l.`type_id`,r.`sort`   FROM  " . DB_PREFIX . "room_type  AS r
                 LEFT JOIN   " . DB_PREFIX . "room_type_lang  l  ON  r.id = l.`type_id`" . $where;
        $sql .= '   order by r.sort';
//        $res = $this->roomTypeMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        $res = $this->roomTypeMod->querySql($sql);
        foreach ($res as $k => $v) {
            $res[$k]['add_time'] = !empty($v['add_time']) ? date('Y-m-d H:i:s', $v['add_time']) : '';
            $res[$k]['app_url']='pages/index/index?bussId='.$v['id'];
        }

        $res = $this->getTree($res, $pid = 0);
        // var_dump($res);die;
        $this->assign('res', $res);
//        $this->assign('res', $res['list']);
        $this->assign('p', $p);
        $this->assign('page_html', $res['ph']);
        $this->display('roomType/roomType.html');
    }
    /**
     * 无限递归，获取分类树
     * @author wangshuo
     * @date 2018-4-08
     */
    public function getTree($list, $pid = 0) {
        $tree = array();
        foreach ($list as $v) {
            if ($v['superior_id'] == $pid) {
                if ($this->getTree($list, $v['id'])) {
                    $v['child'] = $this->getTree($list, $v['id']);
                }
                $tree[] = $v;
            }
        }
        return $tree;
    }

    /**
     * 业务类型添加
     * @author  wanyan
     * @date 2017-08-03
     */
    public function roomTypeAdd() {
        //多语言版本

        $langMod = &m('language');
        $sql = 'select  id,name  from   ' . DB_PREFIX . 'language';
        $data = $langMod->querySql($sql);
        $html = '';
        foreach ($data as $val) {
            $html .= ' <span class="mt10 mb5 inblock">' . $val['name'] . '：</span><input type="text" class="form-control" name="name[' . $val['id'] . ']" >';
        }
        $this->assign('html', $html);
        $this->assign('act', 'roomTypeIndex');
//        $cates = $this->roomTypeMod->getParent();
//        $this->assign('cates', $cates);
        $options = $this->getRoomType();
        $this->assign('options', $options);
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $this->assign('p', $p);
        $this->display('roomType/roomAdd.html');
    }

    /**
     * 获取商品业务选项
     * @param int $selected
     * @return string
     */
    public function getRoomType($selected = 0) {
        $id = !empty($_REQUEST['id']) ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        if ($id) {
            $where = ' and t.id -' . $id;
        }
        $roomType = &m('roomType');
        $sql = 'select  t.id,l.type_name  from  ' . DB_PREFIX . 'room_type  as t
                left join  ' . DB_PREFIX . 'room_type_lang  as  l  on t.id=l.type_id  where t.superior_id=0  and l.lang_id =' . $this->lang_id . $where . ' order by t.sort';
        $data = $roomType->querySql($sql);
            $options = '<option value="0" >--'. $this->langDataBank->project->top_classification .'--</option>';
        if (empty($selected)) {
            foreach ($data as $val) {
                $options .= '<option value=' . $val['id'] . ' >' . $val['type_name'] . '</option>';
            }
        } else {
            foreach ($data as $val) {
                if ($val['id'] == $selected) {
                    $options .= '<option value=' . $val['id'] . '  selected  >' . $val['type_name'] . '</option>';
                } else {
                    $options .= '<option value=' . $val['id'] . ' >' . $val['type_name'] . '</option>';
                }
            }
        }
        return $options;
    }

    /**
     * 业务类型添加
     * @author  wanyan
     * @date 2017-08-03
     */
    public function doAdd() {
        $room_id = !empty($_REQUEST['room_id']) ? (int) ($_REQUEST['room_id']) : '0';
        $lang_id = !empty($_REQUEST['lang_id']) ? (int) ($_REQUEST['lang_id']) : '0';
        $name = !empty($_REQUEST['name']) ? $_REQUEST['name'] : array();
        $type_images=!empty($_REQUEST['goods_images'])? $_REQUEST['goods_images'] : '';
        $str_images=rtrim(implode($type_images,','),',');
//        $adv_url = !empty($_REQUEST['adv_url']) ? htmlspecialchars(trim($_REQUEST['adv_url'])) : '';
        $image_id = !empty($_REQUEST['image_id']) ? htmlspecialchars(trim($_REQUEST['image_id'])) : '';
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $sort = $_REQUEST['sort'] ? htmlspecialchars(trim($_REQUEST['sort'])) : 5;
        //判断数据
        foreach ($name as $key => $val) {
            $name[$key] = trim($val);
        }
        //判断数据
        foreach ($name as $val) {
            if (empty($val)) {
                $this->setData($info = array(), $status = '0', $this->langDataBank->project->type_name_required);
                break;
            } else {
//                $aff = $this->getOneInfo($val);
//                if (!empty($aff)) {
//                    $this->setData($info = array(), $status = '0', $a['type_city_id']);
//                    break;
//                }
            }
        }
        if (empty($image_id)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->picture_required);
        }
        if ($room_id == 0) {
            $adv_id = !empty($_REQUEST['adv_id']) ? htmlspecialchars(trim($_REQUEST['adv_id'])) : '';
            $adv_id = '';
        } else {
            $adv_id = !empty($_REQUEST['adv_id']) ? htmlspecialchars(trim($_REQUEST['adv_id'])) : '';
            if (empty($adv_id)) {
                $this->setData($info = array(), $status = '0', $this->langDataBank->project->ad_required);
            }
        }
//        if (empty($adv_url)) {
//            $this->setData($info = array(), $status = '0', $a['type_url']);
//        }
//        if (!preg_match('/^^((https|http|ftp|rtsp|mms)?:\/\/)[^\s]+$/', $adv_url)) {
//            $this->setData($info = array(), $status = '0', $a['type_http']);
//        }
//        if(!empty($adv_url)){
//            $query=array(
//                'cond' => "`adv_url`='{$adv_url}'",
//                'fields' =>'adv_url'
//            );
//            $res =$this->roomTypeMod->getOne($query);
//            if($res['adv_url']){
//                $this->setData($info=array(),$status='0',$message='广告位的url地址已经存在！');
//            }
//        }
        if (!empty($sort)) {
            if (!preg_match("/^[1-9][0-9]{0,2}$/", $sort)) {
                $this->setData(array(), '0', $this->langDataBank->project->sort_rule);
            }
        }
        $insert_data = array(
            'room_img' => $image_id,
//            'adv_url' => $adv_url,
            'room_adv_img' => $adv_id,
            'sort' => $sort,
            'add_time' => time(),
            'superior_id' => $room_id,
            'room_adv_imgs'=>$str_images
        );
//        print_r($insert_data);exit;
        $insert_id = $this->roomTypeMod->doInsert($insert_data);
        if ($insert_id) {
            //添加多语言版本信息
            $this->doLangData($name, $insert_id);

            $this->addLog('业务类型添加操作');
            $info['url'] = "admin.php?app=roomType&act=roomTypeIndex&p={$p}";
            $this->setData($info, $status = '1', $this->langDataBank->public->add_success);
        } else {
            $this->setData($info, $status = '0', $this->langDataBank->public->add_error);
        }
    }

    /**
     * 添加多语言版本信息
     */
    public function doLangData($name, $insert_id) {
        $gCLangMod = &m('roomTypeLang');
        $data = array();
        foreach ($name as $key => $val) {
            $data[] = array(
                'lang_id' => $key,
                'type_name' => $val,
                'type_id' => $insert_id,
                'add_time' => time()
            );
        }
        // 循环插入数据
        foreach ($data as $v) {
            $res = $gCLangMod->doInsert($v);
            if ($res) {
                continue;
            } else {
                return false;
                break;
            }
        }
        return true;
    }

    /**
     * 模型属性名称
     * @author wanyan
     * @date 2017-8-1
     */
    public function getOneInfo($name, $id = 0) {
        $gCLangMod = &m('roomTypeLang');
        $where = '  where 1=1';
        if (empty($id)) {
            //添加
            $where .= ' and  l.type_name = "' . $name . '"';
        } else {
            //编辑
            $where .= '  and  id!=' . $id . '  and  l.type_name = "' . $name . '"';
        }
        $sql = 'select * from  ' . DB_PREFIX . 'room_type as r  left join ' . DB_PREFIX . 'room_type_lang
          as l on r.id=l.type_id' . $where;
        $res = $gCLangMod->querySql($sql);
        return $res;
    }

    /**
     * 业务类型编辑
     * @author  wanyan
     * @date 2017-08-03
     */
    public function roomEdit() {
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '0';
        $display=!empty($_REQUEST['display']) ? $_REQUEST['display'] : 0;
        //多语言版本
        $gCLangMod = &m('roomTypeLang');
        $sql = 'select  id,lang_id,type_name,type_id  from   ' . DB_PREFIX . 'room_type_lang  where  type_id =' . $id;

        $data = $gCLangMod->querySql($sql);
        $langMod = &m('language');
        $sql = 'select  id,name  from   ' . DB_PREFIX . 'language';
        $res = $langMod->querySql($sql);
        $html = '';
        foreach ($res as $key => $val) {
            foreach ($data as $item) {
                if ($val['id'] == $item['lang_id']) {
                    $html .= ' <span class="mt10 mb5 inblock">' . $val['name'] . '：</span>
                               <input type="text" class="form-control" name="name[' . $item['lang_id'] . ']"  value="' . $item['type_name'] . '"  >';
                    unset($res[$key]);
                }
            }
        }
        //如果以后再添加新的语言
        if (!empty($res)) {
            foreach ($res as $v) {
                $html .= ' <span class="mt10 mb5 inblock">' . $v['name'] . '：</span>
                               <input type="text" class="form-control" name="name[' . $v['id'] . ']"  value=""  >';
            }
        }

        $this->assign('html', $html);
        $query = array(
            'cond' => "`id` ='{$id}'"
        );


        $res = $this->roomTypeMod->getOne($query);
        $type_images=explode(',',$res['room_adv_imgs']);
        $this->assign('type_images',$type_images);
        $this->assign('act', 'roomTypeIndex');
        $options = $this->getRoomType($res['superior_id']);
        $this->assign('options', $options);
        $this->assign('room', $res);
        $this->assign('p', $p);
        $this->assign('id', $_GET['id']);
        $this->assign('display',$display);
        $this->display('roomType/roomEdit.html');
    }

    /**
     * 业务类型编辑功能
     * @author  wanyan
     * @date 2017-08-03
     */
    public function doEdit() {
        $room_id = !empty($_REQUEST['room_id']) ? (int) ($_REQUEST['room_id']) : '0';
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '0';
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $name = !empty($_REQUEST['name']) ? $_REQUEST['name'] : '';
        $type_images=!empty($_REQUEST['goods_images'])? $_REQUEST['goods_images'] : '';
        $str_images=rtrim(implode($type_images,','),',');
        if($str_images){
            $image_data=array(
                'room_adv_imgs'=>''
            );
            $this->roomTypeMod->doEdit($id, $image_data);
        }
//        $adv_url = !empty($_REQUEST['adv_url']) ? htmlspecialchars(trim($_REQUEST['adv_url'])) : '';
        $image_id = !empty($_REQUEST['image_id']) ? htmlspecialchars(trim($_REQUEST['image_id'])) : '';
        $sort = $_REQUEST['sort'] ? htmlspecialchars(trim($_REQUEST['sort'])) : 5;
        //判断数据
        foreach ($name as $key => $val) {
            $name[$key] = trim($val);
        }
        //判断数据
        foreach ($name as $val) {
            if (empty($val)) {
                $this->setData($info = array(), $status = '0', $this->langDataBank->project->type_name_required);
                break;
            } else {
                //
//                $typeId = $this->getCtgLang($val, $id);  //type_lang 表里的id
//                $aff = $this->getOneInfo($val, $typeId); //判断是否命名重复
//                if (!empty($aff)) {
//                    $this->setData($info = array(), $status = '0', $a['type_city_id']);
//                    break;
//                }
            }
        }
        if ($room_id == 0) {
            $adv_id = !empty($_REQUEST['adv_id']) ? htmlspecialchars(trim($_REQUEST['adv_id'])) : '';
            $adv_id = '';
        } else {
            $adv_id = !empty($_REQUEST['adv_id']) ? htmlspecialchars(trim($_REQUEST['adv_id'])) : '';
            if (empty($adv_id)) {
                $this->setData($info = array(), $status = '0', $this->langDataBank->project->ad_required);
            }
        }
        if (empty($image_id)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->picture_required);
        }
//        if (empty($adv_url)) {
//            $this->setData($info = array(), $status = '0', $a['type_url']);
//        }
//        if (!preg_match('/^^((https|http|ftp|rtsp|mms)?:\/\/)[^\s]+$/', $adv_url)) {
//            $this->setData($info = array(), $status = '0', $a['type_http']);
//        }
//        if(!empty($adv_url)){
//            $query=array(
//                'cond' => "`adv_url`='{$adv_url}' and `id` != '{$id}'",
//                'fields' =>'adv_url'
//            );
//            $res =$this->roomTypeMod->getOne($query);
//            if($res['adv_url']){
//                $this->setData($info=array(),$status='0',$message='广告位的url地址已经存在！');
//            }
//        }
        if (!empty($sort)) {
            if (!preg_match("/^[1-9][0-9]{0,2}$/", $sort)) {
                $this->setData(array(), '0', $this->langDataBank->project->sort_rule);
            }
        }
        $insert_data = array(
            'room_img' => $image_id,
//            'adv_url' => $adv_url,
            'room_adv_img' => $adv_id,
            'sort' => $sort,
            'add_time' => time(),
            'superior_id' => $room_id,
            'room_adv_imgs'=>$str_images
        );
        $insert_id = $this->roomTypeMod->doEdit($id, $insert_data);
        if ($insert_id) {
            //删除原来的多版本信息
            $gCLangMod = &m('roomTypeLang');
            $where = '  type_id =' . $id;
            $gCLangMod->doDrops($where);
            //添加多语言版本信息
            $this->doLangData($name, $id);
            $this->addLog('业务类型更新操作');
            $info['url'] = "admin.php?app=roomType&act=roomTypeIndex&p={$p}";
            $this->setData($info, $status = '1', $this->langDataBank->public->edit_success);
        } else {
            $this->setData($info, $status = '0', $this->langDataBank->public->edit_fail);
        }
    }

    /**
     * 获取多语言的版本的id
     */
    public function getCtgLang($name, $id) {
        //多语言版本
        $gCLangMod = &m('roomTypeLang');
        $sql = 'select  id,lang_id,type_name,type_id  from   ' . DB_PREFIX . 'room_type_lang
                 where  type_id =' . $id . '   and   type_name = "' . $name . '"';
        $item = $gCLangMod->querySql($sql);
        if (!empty($item)) {
            return $item[0]['id'];
        } else {
            return 0;
        }
    }

    /**
     * 删除功能
     * @author wanyan
     * @date 2017-08-03
     */
    public function dele() {
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '0';
          //判断是否有子类
        $query_cond = array(
            'cond' => "`superior_id` ='{$id}'"
        );
        $res = $this->roomTypeMod->getOne($query_cond);
        if ($res) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->classification_delete);
        }
        //删除主表的数据
        $query = array(
            'cond' => "`id` ='{$id}'"
        );
        $del_id = $this->roomTypeMod->doDelete($query);
        //删除子表的信息
        $gCLangMod = &m('roomTypeLang');
        $where = '  type_id =' . $id;
        $ctglangids = $gCLangMod->doDrops($where);
        if ($del_id && $ctglangids) {
            $this->addLog('分类删除操作');
            $this->setData($info = array(), $status = '1', $this->langDataBank->public->drop_success);
        } else {
            $this->setData($info = array(), $status = '0', $this->langDataBank->public->drop_fail);
        }
    }
    
 /**
     * 业务一级分类小程序二维码
     * @author wangshuo
     * @date 2018-11-12
     */
    public function getRoomXcxCode(){
        $room_id = $_REQUEST['id'];
        $post_data = json_encode(array(
            'width' => 120,
            "scene"=>"$room_id",
            "page"=>"pages/index/index"
        ));
//        var_dump($post_data);die;
        $access_token = $this->getAccessToken();
        // 为二维码创建一个文件
        $mainPath = ROOT_PATH . '/upload/xcxroomCode';
        $this->mkDir($mainPath);
        $timePath = date('Ymd');
        $savePath = $mainPath . '/' . $timePath;
        $this->mkDir($savePath);
        $newFileName = uniqid() . ".png";
        $filename = $savePath . '/' . $newFileName;
        $pathName = 'upload/xcxroomCode/' . $timePath . '/' . $newFileName;
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$access_token;
//        $url = "https://api.weixin.qq.com/wxa/getwxacode?access_token=".$access_token;
        $result = $this->httpRequest($url,$post_data,'POST');
        $res = file_put_contents($pathName,$result);
        $urldata = array(
            "table" => "room_type",
            'cond' => 'id = ' . $room_id,
            'set' => "room_url='" . $pathName . "'",
        );
//        var_dump($urldata);die;
        $resss = $this->roomTypeMod->doUpdate($urldata);
        if ($res){
            $info['url'] = "?app=roomType&act=roomTypeIndex";
            $this->setData($info,1,$this->langDataBank->project->generate_success);
        }else{
            $this->setData(array(),0,$this->langDataBank->project->generate_fail);
        }
    }

        /**
     * 读取access_token
     */
    public function getAccessToken() {
        $appid = 'wx9346f7520e980329';
        $secret = '1f9eb93c8b71e58998334853d4b2eb83';
        $file = file_get_contents("./access_token.json",true);
        $result = json_decode($file,true);
        if (time() > $result['expires']){
            $data = array();
            $data['access_token'] = $this->getNewToken($appid,$secret);
            $data['expires']      = time() + 7000;
            $jsonStr = json_encode($data);
            $fp = fopen("./access_token.json","w");
            fwrite($fp,$jsonStr);
            fclose($fp);
            return $data['access_token'];
        } else {
            return $result['access_token'];
        }
    }
        /**
     * 获取微信accesstoken
     * @param $appid
     * @param $secret
     * @return mixed
     */
    public function getNewToken($appid,$secret) {
        $tokenUrl = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $secret;
        $access_token_arr = $this->httpRequest($tokenUrl);
        $access = json_decode($access_token_arr,true);
        return $access['access_token'];
    }
  /**
     * curl方法
     * @param $url
     * @param string $data
     * @param string $method
     * @return mixed
     */
    public function httpRequest($url, $data='', $method='GET'){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        if($method=='POST')
        {
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data != '')
            {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
        }

        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }
}

?>