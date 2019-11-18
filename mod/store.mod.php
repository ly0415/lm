<?php

/**
 * 店铺模块模型
 * @author: jh
 * @date: 2017/6/21
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class StoreMod extends BaseMod {

    public $local_ip = '';
    public $lang_id;
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct("store");
        $this->local_ip = '114.222.189.229';
        $this->lang_id = 29;
    }

    /**
     * 获取当前所在国家总站
     * @author  : luffy
     * @date    : 2018-12-27
     */
    public function getCountryCode(){
        if(empty($_SESSION['country_code'])){
            $current_ip     = get_client_ip();
            if($current_ip == '127.0.0.1'){
                $current_ip = $this->local_ip;
            }
            $countryCode    = getCountryCode($current_ip, 5);
            if($countryCode){
                $_SESSION['country_code'] = $countryCode;
            } else {
                $_SESSION['country_code'] = 'CN';
            }
        }
        return $_SESSION['country_code'];
    }

    /**
     * 获取店铺的name
     * @author: wanyan
     * @date: 2017/10/19
     */
    public function getNameById($id , $lang_id) {
//        $query = array(
//            'cond' => "`id` = '{$id}'",
//            'fields' => "store_name"
//        );
//        $rs = $this->getOne($query);
        $sql = 'SELECT  l.store_name  FROM  ' . DB_PREFIX . 'store  as c
                 left join  ' . DB_PREFIX . 'store_lang as l on  c.id = l.store_id
                 WHERE  l.lang_id =' . $lang_id . '  and  c.id=' . $id ." and l.distinguish = 0";
        $res = $this->querySql($sql);
        return $res[0]['store_name'];
    }

    /*
     * 根据区域获取店铺Ids
     * @param $area_id      区域ID
     * @param $op           是否组装成字符串
     * @author luffy
     * @date 2017-11-07
     */

    public function getStoreIds($area_id = 0, $op = 0) {
        $sql = ' is_open = 1 ';
        if ($area_id) {
            $sql .= ' AND store_cate_id = ' . $area_id;
        }
        //获取区域下店铺
        $storeIds = $this->getIds(array('conf' => $sql));
        if ($storeIds && $op) {
            $storeIds = implode(',', $storeIds);
        }
        return $storeIds;
    }

    /**
     * 获取区域店铺
     * @author: luffy
     * @date  : 2017-11-09
     */
    public function getStoreArr($area_id, $type = 0) {
        $sql = 'select  id from  ' . DB_PREFIX . 'language where is_default =2 ';
        $lang = $this->querySql($sql);
        $sql = 'SELECT  c.id,l.store_name  FROM  ' . DB_PREFIX . 'store  as c
                 left join  ' . DB_PREFIX . 'store_lang as l on  c.id = l.store_id
                 WHERE c.is_open = 1 and  l.distinguish = 0  and  l.lang_id =' . $lang[0]["id"] . '  and c.store_cate_id=' . $area_id . ' order by c.id';
//        $query = array(
//            'cond' => "is_open = 1 AND store_cate_id = ".$area_id,
//            'fields' => 'id, store_name'
//        );
        $storeArr = $this->querySql($sql);
        if ($type) {
            $result = array();
            foreach ($storeArr as $k => $v) {
                $result[$v['id']] = $v['store_name'];
            }
            $storeArr = $result;
        }
        return $storeArr;
    }
    public function getStore($area_id, $type = 0,$store_id) {
        $sql = 'select  id from  ' . DB_PREFIX . 'language where is_default =2 ';
        $lang = $this->querySql($sql);
        $sql = 'SELECT  c.id,l.store_name  FROM  ' . DB_PREFIX . 'store  as c
                 left join  ' . DB_PREFIX . 'store_lang as l on  c.id = l.store_id
                 WHERE c.is_open = 1 and c.id != ' . $store_id . ' and  l.distinguish = 0  and  l.lang_id =' . $lang[0]["id"] . '  and c.store_cate_id=' . $area_id . ' order by c.id';
        $storeArr = $this->querySql($sql);
        if ($type) {
            $result = array();
            foreach ($storeArr as $k => $v) {
                $result[$v['id']] = $v['store_name'];
            }
            $storeArr = $result;
        }
        return $storeArr;
    }
    /**
     * 根据定位获取其国家下的站点(有总代理默认总代理)
     * @author: luffy
     * @date  : 2018-08-14
     */
    public function getOpenStoreArr() {
        if (SYSTEM_WEB == "www.njbsds.cn") {
            $where = ' WHERE a.is_open = 1 AND b.id = 1 ';  //暂时默认获取中国

            $sql = 'SELECT b.id,b.lang_id FROM ' . DB_PREFIX . 'store AS a LEFT JOIN ' .
                DB_PREFIX . 'store_cate AS b ON a.store_cate_id = b.id AND b.is_open = 1 LEFT JOIN ' .
                DB_PREFIX . 'store_cate_lang AS c ON c.cate_id = b.id AND b.lang_id = c.lang_id ' .
                $where . ' order by a.store_type ASC,a.add_time DESC limit 1';
            $result = $this->querySql($sql);

            return $result[0];
        }else{
            $where = ' WHERE a.is_open = 1 AND a.id = 58';  //暂时默认获取中国

            $sql = 'SELECT a.id,b.lang_id FROM ' . DB_PREFIX . 'store AS a LEFT JOIN ' .
                DB_PREFIX . 'store_cate AS b ON a.store_cate_id = b.id AND b.is_open = 1 LEFT JOIN ' .
                DB_PREFIX . 'store_cate_lang AS c ON c.cate_id = b.id AND b.lang_id = c.lang_id ' .
                $where . ' order by a.store_type ASC,a.add_time DESC limit 1';
            $result = $this->querySql($sql);
//            var_dump($result);die;
            return $result[0];
        }
    }

    /**
     * 获取店铺的name
     * @author zhangkx
     * @date 2018-10-20
     */
    public function getNameByIds($ids , $lang_id)
    {
        $sql = 'SELECT  l.store_name  FROM  ' . DB_PREFIX . 'store  as c
                 left join  ' . DB_PREFIX . 'store_lang as l on  c.id = l.store_id
                 WHERE  l.lang_id =' . $lang_id . '  and  c.id in (' . $ids . ") and l.distinguish = 0";
        $res = $this->querySql($sql);
        $storeName = '';
        if (count($res) > 1) {
            foreach ($res as $key => $value) {
                $storeName .= $value['store_name'].',';
            }
            $storeName = substr($storeName,0,strlen($storeName)-1);;
        } else {
            $storeName = $res[0]['store_name'];
        }
        return $storeName;
    }

    /**
     * 获取store_type不为1的站点
     * @param $lang_id
     * @return array
     */
    public function getStores($lang_id)
    {
        $sql = 'SELECT  l.store_name,l.store_id  FROM  ' . DB_PREFIX . 'store  as s
                 left join  ' . DB_PREFIX . 'store_lang as l on  s.id = l.store_id
                 WHERE l.lang_id = ' . $lang_id . '  and  s.store_type != 1 and l.distinguish = 0';
        $data = $this->querySql($sql);
        return $data;
    }

    /*
    * 根据店铺id获取到对应的区域的店铺
    * @author gao
    * @date 2019-03-21
    */
    public function getSelectStore($storeCateId,$langId){
     $sql=<<<SQL
        SELECT
	    s.id,sl.store_name
	    FROM
	    bs_store AS s
        LEFT JOIN bs_store_lang AS sl ON s.id = sl.store_id
        WHERE
	    s.is_open = 1
        AND sl.distinguish = 0
        AND sl.lang_id ={$langId}
        AND s.store_cate_id ={$storeCateId}
        ORDER BY s.id
SQL;

     $data = $this->querySql($sql);
     return $data;
    }

    /**
     * 获取正常的店铺id
     * @return array
     */
    public function achieveStore()
    {
        $sql = 'SELECT  s.id     FROM  ' . DB_PREFIX . 'store AS s
                LEFT JOIN  ' . DB_PREFIX . 'store_lang AS sl ON sl.`store_id` = s.`id` and  sl.lang_id=' . $this->lang_id .
            ' LEFT JOIN  ' . DB_PREFIX . 'language AS l ON s.`lang_id` = l.`id`
                LEFT JOIN  ' . DB_PREFIX . 'currency AS c ON s.`currency_id` = c.`id`';
        $rs = $this->querySql($sql);
        $arr = array();
        foreach ($rs as $v){
            $arr[] = $v['id'];
        }
        return $rs;
    }

}
