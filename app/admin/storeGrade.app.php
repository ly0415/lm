<?php
/**
 * 业务类型控制器
 * @author  wanyan
 * @date 2017-07-31
 */
if (!defined('IN_ECM')) {die('Forbidden');}
class  storeGradeApp  extends  BackendApp
{
    private $storeGradeMod;

    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct();
        $this->storeGradeMod = &m('storeGrade');
       // $this->goodsBrandMod      = &m('goodsBrand');
    }
    /**
     * 商家等级
     * @author wanyan
     * @date 2017/08/17
     */
    public function storeGradeIndex(){
        $where = "order by sg_id desc";
        $sql = "select `sg_id`,`sg_name`,`sg_price`,`sg_sort`,`sg_description`,`add_time` from ".DB_PREFIX."store_grade ".$where;
        $rs = $this->storeGradeMod->querySqlPageData($sql);
        foreach ($rs['list'] as $key=>$val){
            $rs['list'][$key]['add_time'] = date('Y-m-d H:i:s',$val['add_time']);
        }
        $this->assign('list',$rs['list']);
        $this->assign('page',$rs['ph']);
        $this->display('storeGrade/storeGrade.html');
    }
    /**
     * 商家等级
     * @author wanyan
     * @date 2017/08/17
     */
    public function gradeAdd(){
        $this->assign('act','storeGradeIndex');
        $this->display('storeGrade/gradeAdd.html');
    }
    /**
     * 商家添加
     * @author wanyan
     * @date 2017/08/17
     */
    public function doAdd(){
      $sg_name = !empty($_REQUEST['sg_name']) ? htmlspecialchars(trim($_REQUEST['sg_name'])):'';
      $sg_price = !empty($_REQUEST['sg_price']) ? $_REQUEST['sg_price']:'';
      $sg_sort = !empty($_REQUEST['sg_sort']) ? intval($_REQUEST['sg_sort']):'';
      $sg_description= !empty($_REQUEST['sg_description']) ? htmlspecialchars(trim($_REQUEST['sg_name'])):'';
      if(empty($sg_name)){
          $this->setData($info=array(),$status='2',$message='等级名称不能为空！');
      }else{
          $query=array(
              'cond' =>"`sg_name` ='{$sg_name}'"
          );
          $rs = $this->storeGradeMod->getOne($query);
          if($rs['sg_name']){
              $this->setData($info=array(),$status='2',$message='等级名称已经存在！');
          }
      }
      if(empty($sg_price)){
            $this->setData($info=array(),$status='2',$message='入驻金额不能为空！');
      }
      if(!preg_match('/^\+?[1-9][0-9]*$/',$sg_price)){
          $this->setData($info=array(),$status='2',$message='金额格式不存在！');
      }
      $data =array(
          'sg_name'=>$sg_name,
          'sg_price'=>$sg_price,
          'sg_sort'=>$sg_sort,
          'sg_description'=>$sg_description,
          'add_time'=>time()
      );
      $insert_id =$this->storeGradeMod->doInsert($data);
      if($insert_id){
          $info['url']="?app=storeGrade&act=storeGradeIndex";
          $this->setData($info,$status='1',$message='添加成功！');
      }else{
          $this->setData($info=array(),$status='2',$message='添加失败！');
      }
    }
    /**
     * 商家编辑页面
     * @author wanyan
     * @date 2017/08/17
     */
    public function gradeEdit(){
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) :0;
        $query=array(
            'cond'=>"`sg_id`='{$id}'"
        );
        $rs = $this->storeGradeMod->getOne($query);
        $this->assign('rs',$rs);
        $this->display('storeGrade/gradeEdit.html');
    }
    /**
     * 商家编辑
     * @author wanyan
     * @date 2017/08/17
     */
    public function doEdit(){
        $sg_id = !empty($_REQUEST['sg_id']) ? intval($_REQUEST['sg_id']) :0;
        $sg_name = !empty($_REQUEST['sg_name']) ? htmlspecialchars(trim($_REQUEST['sg_name'])):'';
        $sg_price = !empty($_REQUEST['sg_price']) ? intval($_REQUEST['sg_price']):'0';
        $sg_sort = !empty($_REQUEST['sg_sort']) ? intval($_REQUEST['sg_sort']):'';
        $sg_description= !empty($_REQUEST['sg_description']) ? htmlspecialchars(trim($_REQUEST['sg_name'])):'';
        if(empty($sg_name)){
            $this->setData($info=array(),$status='2',$message='等级名称不能为空！');
        }else{
            $query=array(
                'cond' =>"`sg_name` ='{$sg_name}' and `sg_id` !='{$sg_id}'"
            );
            $rs = $this->storeGradeMod->getOne($query);
            if($rs['sg_name']){
                $this->setData($info=array(),$status='2',$message='等级名称已经存在！');
            }
        }
        if(empty($sg_price)){
            $this->setData($info=array(),$status='2',$message='入驻金额不能为空！');
        }
        if(!preg_match('/^\+?[1-9][0-9]*$/',$sg_price)){
            $this->setData($info=array(),$status='2',$message='金额格式不存在！');
        }
        $data =array(
            'sg_name'=>$sg_name,
            'sg_price'=>$sg_price,
            'sg_sort'=>$sg_sort,
            'sg_description'=>$sg_description,
           // 'modify_time' =>time()
        );
        $insert_id =$this->storeGradeMod->doEditSpec(array('sg_id'=>$sg_id),$data);
        if($insert_id){
            $info['url']="?app=storeGrade&act=storeGradeIndex";
            $this->setData($info,$status='1',$message='编辑成功！');
        }else{
            $this->setData($info=array(),$status='2',$message='编辑失败！');
        }
    }
    /**
     * 商家等级删除功能
     * @author wanyan
     * @date 2017/08/17
     */
    public function dele(){
        $id =!empty($_REQUEST['id']) ?intval($_REQUEST['id']) :0;
        $query =array(
            'cond' =>"`sg_id` ='{$id}'"
        );
        $dele_id = $this->storeGradeMod->doDelete($query);
        if($dele_id){
            $this->setData($info,$status='1',$message='删除成功！');
        }else{
            $this->setData($info=array(),$status='2',$message='删除失败！');
        }
    }

}
?>