<?php
/**
 * 店铺商品列表接口控制器
 * @author  luffy
 * @date    2019-07-11
 */
class StoreApp extends BasePhApp
{

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
    }

    /**
     * 首页搜索店铺接口
     * @author: luffy
     * @date  : 2019-07-19
     */
    public function search()
    {
        $search_name = !empty($_REQUEST['search_name']) ? htmlspecialchars(trim($_REQUEST['search_name'])) : '';
        if(empty($search_name)){
            return false;
        }

        $storeMod   = &m('store');
        $store_info = $storeMod->querySql("select a.id,a.store_notice,b.store_name from bs_store a LEFT JOIN bs_store_lang b ON a.id = b.store_id where b.lang_id = 29 AND  b.`store_name` like '%" . $search_name . "%'"); 

        if($store_info){
            foreach($store_info as $key => $value){
                $bussSql = "SELECT buss_id FROM " . DB_PREFIX . 'store_business WHERE  store_id =' . $value['id'];
                $bussData = $storeMod->querySql($bussSql);
                $store_info[$key]['rtid'] = $bussData[0]['buss_id'];
            }
        }

        if($store_info){
            $this->setData(array(
                'store_data'    => $store_info,
                'search_name'   => $search_name
            ), 1);
        } else {
            $this->setData(array(), 0);
        }
    }

    /**
     * 店铺商品列表-----默认到店自提
     * @author  luffy
     * @date    2019-07-11
     */
    public function goodsList()
    {
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : '';
        $rtid     = !empty($_REQUEST['rtid']) ? $_REQUEST['rtid'] : '';
        $lang_id  = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : 29;
        $type     = !empty($_REQUEST['type']) ? $_REQUEST['type'] : 1;
        $page     = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 1;
        $search_name= empty($_REQUEST['search_name']) ? '' : htmlspecialchars(trim($_REQUEST['search_name']));
        $type_id    = !empty($_REQUEST['type_id']) ? $_REQUEST['type_id'] : '';
        $goods    = &m('storeGoods')->getGoodsXcx($store_id,$rtid,$lang_id,$this->userId,$type,$type_id,$search_name);

 //
        //计算每次分页的开始位置
        $start    =($page-1) * 20;
//        $totals   =count($goods);
//        $resoult  =ceil($totals/20); #计算总页面数
        $data = array(
            'goods' =>array_slice($goods, $start, 20),
            'type'  =>$type,
        );
        if ($data) {
            $this->setData($data, '1', '');
        }
    }
 
    /**
     * 店铺商品列表-----业务类型
     * @author  luffy
     * @date    2019-07-11
     */ 
    public function getBusinessType()
    {
        $store_id   = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : '';
        $rtid       = !empty($_REQUEST['rtid']) ? $_REQUEST['rtid'] : '';
        $lang_id    = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : 29;

        $roomTypeMod = &m('roomType');
        $roomtypearr = $roomTypeMod->getBusinessType($lang_id, $store_id, $rtid, 1);

        if ($roomtypearr) {
            $this->setData($roomtypearr, '1', '');
        } else {
            $this->setData(array(), '0', '');
        }
    }

    public function goodsList1()
    {
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : '';
        $rtid     = !empty($_REQUEST['rtid']) ? $_REQUEST['rtid'] : '';
        $lang_id  = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : 29;
        $type     = !empty($_REQUEST['type']) ? $_REQUEST['type'] : 1;
        $page     = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 1;
        $search_name= empty($_REQUEST['search_name']) ? '' : htmlspecialchars(trim($_REQUEST['search_name']));
        $type_id    = !empty($_REQUEST['type_id']) ? $_REQUEST['type_id'] : '';
        $goods    = &m('storeGoods')->getGoods($store_id,$rtid,$lang_id,$this->userId,$type,$type_id,$search_name);
 //echo '<pre>';print_r( $goods  );die;  
        //计算每次分页的开始位置
        $start    =($page-1) * 20;
//        $totals   =count($goods['goods']);
//        $resoult  =ceil($totals/20); #计算总页面数
        $data = array(
            'goods'    =>array_slice($goods['goods'], $start, 20),
            'bus_type'  =>$goods['room'],
            'type'     =>$type,
        );
        if ($data) {
            $this->setData($data, '1', '');
        }
    }
}
