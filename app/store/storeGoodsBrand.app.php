<?php
/**
 * 商家品牌申请表
 * @author lee
 * @date 2017/07/19
 */
if (!defined('IN_ECM')) {die('Forbidden');}
class storeGoodsBrandApp extends BaseStoreApp {
    private  $storeGoodsBrandMod;
    private  $goodsBrandMod;
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->storeGoodsBrandMod = &m('storeGoodsBrand');
        $this->goodsBrandMod      = &m('goodsBrand');
        $this->goodsClassMod = &m('goodsClass');

    }
    /**
     * 商家品牌申请表
     * @author wanyan
     * @date 2017/08/09
     */
    public function storeBrand(){
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) :'';
        $where = " where 1=1" ;
        if(!empty($name)){
            $where .= " and `name` like '%".$name."%'";
        }
        $where .= " order by sort desc ";
        $sql = "select * from ".DB_PREFIX."store_goods_brand ".$where;
        $res =$this->goodsBrandMod->querySqlPageData($sql);
        foreach ($res['list'] as $k=>$v){
            if($v['status'] ==1){
                $res['list'][$k]['statusName'] = '申请中';
            }elseif($v['status'] ==2){
                $res['list'][$k]['statusName'] = '审核成功';
            }elseif($v['status'] ==3){
                $res['list'][$k]['statusName'] = '审核失败';
            }
            $res['list'][$k]['cate_name'] = $this->getSub($v['max_cat_id']).'>'.$this->getSub($v['parent_cat_id']).'>'.$v['cat_name'];
            $res['list'][$k]['add_time'] =date('Y-m-d H:i:s',$v['add_time']);
        }
        $this->assign('brand_name',$name);
        $this->assign('list',$res['list']);
        $this->assign('page',$res['ph']);
        $this->display('storeBrand/storeBrand.html');
    }
    /**
     * 查找商品子分类
     * @author wanyan
     * @date 2017-8-1
     */
    public function getSub($id){
        $sql = "select `id`,`name` from ".DB_PREFIX."goods_category where `id` = '{$id}'";
        $res =$this->goodsBrandMod->querySql($sql);
        return $res[0]['name'];
    }
    /**
     * 商家品牌添加
     * @author wanyan
     * @date 2017/08/09
     */
    public function brandAdd(){
        $this->assign('act','brandIndex');
        $res = $this->goodsBrandMod->getParent();
        $this->assign('cates',$res);
        $this->display('storeBrand/brandAdd.html');
    }
    /**
     * 商品品牌添加功能
     * @author wanyan
     * @date 2017-08-02
     */
    public function doAdd(){
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) :'';
        $pro_id = !empty($_REQUEST['pro_id']) ? intval($_REQUEST['pro_id']):'0';
        $city_id = !empty($_REQUEST['city_id']) ? intval($_REQUEST['city_id']) :'0';
        $area_id = !empty($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) :'0';
        $image_id = !empty($_REQUEST['image_id']) ? htmlspecialchars(trim($_REQUEST['image_id'])) :'';
        $sort = !empty($_REQUEST['sort']) ? intval($_REQUEST['sort']) :'0';
        $desc = !empty($_REQUEST['desc']) ? htmlspecialchars(trim($_REQUEST['desc'])) :'';
        if(empty($name)){
            $this->setData($info=array(),$status='0',$message='品牌名称不能为空！');
        }
        if(empty($pro_id) || empty($city_id) || empty($area_id)){
            $this->setData($info=array(),$status='0',$message='分类的名称全部不能为空！');
        }
        if(!empty($name)&&!empty($pro_id)&&!empty($city_id)&&!empty($area_id)){
            $query=array(
                'cond' => "`name`='{$name}' and `max_cat_id` ='{$pro_id}' and `parent_cat_id` ='{$city_id}' and `cat_id`='{$area_id}'",
                'fields' =>'name'
            );
            $res =$this->storeGoodsBrandMod->getOne($query);
            if($res['name']){
                $this->setData($info=array(),$status='0',$message='品牌相对应得分类已经存在！');
            }
        }

        if(empty($image_id)){
            $this->setData($info=array(),$status='0',$message='图片名称不能为空！');
        }
        if(empty($sort)){
            $this->setData($info=array(),$status='0',$message='排序不能为空！');
        }
        $query=array(
            'cond' =>"`id`='{$area_id}'",
            'fields'=>'name'
        );
        $res = $this->goodsClassMod->getOne($query);
        $insert_data =array(
            'name'=>$name,
            'logo'=>$image_id,
            'descrption' =>$desc,
            'sort' =>$sort,
            'cat_name'=>$res['name'],
            'store_id' =>1,
            'max_cat_id'=>$pro_id,
            'parent_cat_id'=>$city_id,
            'cat_id' =>$area_id,
            'status' =>1,
            'add_time'=>time()
        );
        $insert_id =$this->storeGoodsBrandMod->doInsert($insert_data);
        if($insert_id){
            $info['url'] = "store.php?app=storeGoodsBrand&act=storeBrand";
            $this->setData($info,$status='1',$message='添加成功！');
        }else{
            $this->setData($info=array(),$status='0',$message='添加失败！');
        }
    }
    /**
     * 根据level 获取相对应的分类
     * @author wanyan
     * @date 2017-08-03
     */
    public function getCateByLevel($level,$parent_id){
        $sql = "select `id`,`name`,`is_hot`,`parent_id`,`is_show`,`sort_order`,`add_time` from ".DB_PREFIX."goods_category where `level` = '{$level}' and `parent_id` ='{$parent_id}'";
        $res =$this->storeGoodsBrandMod->querySql($sql);
        return $res;
    }
    /**
     * 商品品牌查看页面
     * @author wanyan
     * @date 2017-08-03
     */
    public function brandScan(){
        $brand_id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) :'0';
        $query =array(
            'cond' =>"`id` ='{$brand_id}'",
            'fields' =>'*'
        );
        $brandInfo = $this->storeGoodsBrandMod->getOne($query);
        $res = $this->goodsBrandMod->getParent();
        $this->assign('secCate',$this->getCateByLevel(2,$brandInfo['max_cat_id']));
        $this->assign('thirdCate',$this->getCateByLevel(3,$brandInfo['parent_cat_id']));
        $this->assign('brandInfo',$brandInfo);
        $this->assign('cates',$res);
        $this->assign('act','brandIndex');
        $this->display('storeBrand/brandScan.html');
    }
    /**
     * 商品品牌编辑页面
     * @author wanyan
     * @date 2017-08-03
     */
    public function brandEdit(){
        $brand_id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) :'0';
        $query =array(
            'cond' =>"`id` ='{$brand_id}'",
            'fields' =>'*'
        );
        $brandInfo = $this->storeGoodsBrandMod->getOne($query);
        $res = $this->goodsBrandMod->getParent();
        $this->assign('secCate',$this->getCateByLevel(2,$brandInfo['max_cat_id']));
        $this->assign('thirdCate',$this->getCateByLevel(3,$brandInfo['parent_cat_id']));
        $this->assign('brandInfo',$brandInfo);
        $this->assign('cates',$res);
        $this->assign('act','brandIndex');
        $this->display('storeBrand/brandEdit.html');
    }
    /**
     * 处理品牌编辑功能
     * @author wanyan
     * @date 2017-08-03
     */
    public function doEdit(){
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) :'0';
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) :'';
        $pro_id = !empty($_REQUEST['pro_id']) ? intval($_REQUEST['pro_id']):'0';
        $city_id = !empty($_REQUEST['city_id']) ? intval($_REQUEST['city_id']) :'0';
        $area_id = !empty($_REQUEST['area_id']) ? intval($_REQUEST['area_id']) :'0';
        $image_id = !empty($_REQUEST['image_id']) ? htmlspecialchars(trim($_REQUEST['image_id'])) :'';
        $sort = !empty($_REQUEST['sort']) ? intval($_REQUEST['sort']) :'0';
        $desc = !empty($_REQUEST['desc']) ? htmlspecialchars(trim($_REQUEST['desc'])) :'';
        if(empty($name)){
            $this->setData($info=array(),$status='0',$message='品牌名称不能为空！');
        }
        if(empty($pro_id) || empty($city_id) || empty($area_id)){
            $this->setData($info=array(),$status='0',$message='分类的名称全部不能为空！');
        }
        if(!empty($name)&&!empty($pro_id)&&!empty($city_id)&&!empty($area_id)){
            $query=array(
                'cond' => "`name`='{$name}' and `max_cat_id` ='{$pro_id}' and `parent_cat_id` ='{$city_id}' and `cat_id`='{$area_id}' and `id` != '{$id}' ",
                'fields' =>'name'
            );
            $res =$this->storeGoodsBrandMod->getOne($query);
            if($res['name']){
                $this->setData($info=array(),$status='0',$message='品牌相对应得分类已经存在！');
            }
        }
        if(empty($image_id)){
            $this->setData($info=array(),$status='0',$message='图片名称不能为空！');
        }
        if(empty($sort)){
            $this->setData($info=array(),$status='0',$message='排序不能为空！');
        }
        $query=array(
            'cond' =>"`id`='{$area_id}'",
            'fields'=>'name'
        );
        $res = $this->goodsClassMod->getOne($query);
        $insert_data =array(
            'name'=>$name,
            'logo'=>$image_id,
            'descrption' =>$desc,
            'sort' =>$sort,
            'cat_name'=>$res['name'],
            'max_cat_id'=>$pro_id,
            'parent_cat_id'=>$city_id,
            'cat_id' =>$area_id,
            'modify_time'=>time()
        );
        $insert_id =$this->storeGoodsBrandMod->doEdit($id,$insert_data);
        if($insert_id){
            $info['url'] = "store.php?app=storeGoodsBrand&act=storeBrand";
            $this->setData($info,$status='1',$message='更新成功！');
        }else{
            $this->setData($info=array(),$status='0',$message='更新失败！');
        }
    }
    /**
     * 处理品牌编辑功能
     * @author wanyan
     * @date 2017-08-03
     */
    public function dele(){
        $id =!empty($_REQUEST['id']) ? intval($_REQUEST['id']) :'0';
        $query =array(
            'cond' =>"`id` ='{$id}'",
        );
        $aff_id = $this->storeGoodsBrandMod->doDelete($query);
        if($aff_id){
            $this->setData($info=array(),$status='1',$message='删除成功！');
        }else{
            $this->setData($info=array(),$status='0',$message='删除失败！');
        }
    }


}