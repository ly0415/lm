<?php
/**
 * 业务类型控制器
 * @author  wanyan
 * @date 2017-07-31
 */
if (!defined('IN_ECM')) {die('Forbidden');}
class  storeBrandApp  extends  BackendApp
{
    private $storeBrandMod;

    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct();
        $this->storeBrandMod = &m('storeGoodsBrand');
        $this->goodsBrandMod      = &m('goodsBrand');
    }
    /**
     * 商家品牌页面
     * @author  wanyan
     * @date 2017-08-09
     */
    public function storeBrand(){
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) :'';
        $status = !empty($_REQUEST['status']) ? htmlspecialchars(trim($_REQUEST['status'])) :'0';
        $where = " where 1=1" ;
        if(!empty($name)){
            $where .= " and `name` like '%".$name."%'";
        }
        if(!empty($status)){
            $where .= " and  `status` = '{$status}'";
        }
        $where .= " order by sort desc ";
        $sql = "select * from ".DB_PREFIX."store_goods_brand ".$where;
        $res =$this->storeBrandMod->querySqlPageData($sql);
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
        $this->assign('status',$status);
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
        $res =$this->storeBrandMod->querySql($sql);
        return $res[0]['name'];
    }
    /**
     * 根据level 获取相对应的分类
     * @author wanyan
     * @date 2017-08-03
     */
    public function getCateByLevel($level,$parent_id){
        $sql = "select `id`,`name`,`is_hot`,`parent_id`,`is_show`,`sort_order`,`add_time` from ".DB_PREFIX."goods_category where `level` = '{$level}' and `parent_id` ='{$parent_id}'";
        $res =$this->storeBrandMod->querySql($sql);
        return $res;
    }
    /**
     * 商品品牌查看页面
     * @author wanyan
     * @date 2017-08-03
     */
    public function brandEdit(){
        $brand_id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) :'0';
        $query =array(
            'cond' =>"`id` ='{$brand_id}'",
            'fields' =>'*'
        );
        $brandInfo = $this->storeBrandMod->getOne($query);
        $res = $this->goodsBrandMod->getParent();
        $this->assign('secCate',$this->getCateByLevel(2,$brandInfo['max_cat_id']));
        $this->assign('thirdCate',$this->getCateByLevel(3,$brandInfo['parent_cat_id']));
        $this->assign('brandInfo',$brandInfo);
        $this->assign('cates',$res);
        $this->assign('act','brandIndex');
        $this->display('storeBrand/brandEdit.html');
    }
    /**
     * 商品品牌审核
     * @author wanyan
     * @date 2017-08-09
     */
    public function  check(){
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) :'0';
        $query =array(
            'cond' =>"`id` ='{$id}'",
            'fields' =>'*'
        );
        $brandInfo = $this->storeBrandMod->getOne($query);
        $this->assign('info',$brandInfo);
        $this->display('storeBrand/check.html');
    }
    /**
     * 商品品牌审核
     * @author wanyan
     * @date 2017-08-09
     */
    public function doCheck(){
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) :'0';
        $review_status = !empty($_REQUEST['review_status']) ? htmlspecialchars($_REQUEST['review_status']) :'';
        $review_note= !empty($_REQUEST['review_note']) ? htmlspecialchars($_REQUEST['review_note']) :'';
        $data =array(
            'status' =>$review_status,
            'note'   =>$review_note,
            'modify_time'=>time()
        );
        $affact_id = $this->storeBrandMod->doEdit($id,$data);
        if($affact_id){
            $this->setData($info=array(),$status='1',$message='操作成功!');
        }else{
            $this->setData($info=array(),$status='0',$message='操作失败!');
        }
    }

}
