<?php
/**
 * 专业分类控制器
 * @author: zhangr
 * @date: 2017/6/22
 */
class SystemTypeApp extends BackendApp{

    private $model,$schoolMod;

    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct();
        $this->model=&m('systemType');
    }

    /**
     * 析构函数
     */
    public function __destruct(){
    }
    /**
     * @author zhangr
     * @date 2017/12/07
     */
    public function index(){
        $title       = !empty($_REQUEST['title']) ? htmlspecialchars(trim($_REQUEST['title'])) : '';
        $type        = !empty($_REQUEST['type']) ? htmlspecialchars(trim($_REQUEST['type'])) : '';
        $relation_id = !empty($_REQUEST['relation_id']) ? htmlspecialchars(trim($_REQUEST['relation_id'])) : '';
        $this->assign('title',$title);
        $this->assign('type',$type);
        $this->assign('relation_id',$relation_id);
        $whe = ' where 1=1 and mark = 1';
        if($title){
            $whe .=' and title like "%' . $title . '%"';
        }
        if($type){
            $whe .=' and type = '. $type ;
        }
        if($relation_id){
            $whe .=' and relation_id = '. $relation_id ;
        }
        $sql = "select * from ".DB_PREFIX."system_type ".$whe." order by sort asc, id desc";
        $data = $this->model->querySqlPageData($sql);
        $p = $_REQUEST['p'] ? $_REQUEST['p'] : 1;
        foreach($data['list'] as $key => $value){
            $data['list'][$key]['type_name'] = $this->model->type[$value['type']];
            $data['list'][$key]['add_time'] = date("Y-m-d H:i",$value['add_time']);
            $data['list'][$key]['key'] = $data['total']-$key-20*($p-1);
        }
//        echo"<pre>";print_r($data['list']);die;
        $this->assign('ph',$data['ph']);
        $this->assign('list',$data['list']);
        //分类
        $this->assign('type_list',$this->model->type);
        //映射页面
        $this->display('systemType/index.html');
    }
    /**
     *添加、编辑分类操作
     * @author zhangr
     * @date 2017/12/07
     */
    public function edit(){
        $id = !empty($_REQUEST['id'])?intval($_REQUEST['id']):'';
        $this->assign('id',$id);
        if($id){
            $info = $this->model->getRow($id);
            $this->assign('info',$info);
        }
        if (IS_POST) {
            $data = $_POST;
            $data['sort'] = $data['sort'] ? (int)$data['sort'] : 1;//排序
            $data['title'] = $data['title'] ? htmlspecialchars(trim($data['title'])) : '';
            if(empty($data['title'])){
                $this->setData(array(), '0', '配置名称必填！');
            }
            if(empty($data['type'])){
                $this->setData(array(), '0', '请选择分类！');
            }
            if (empty($data['sort'])) {
                $this->setData(array(), '0', '请填写排序！');
            }
            if($id){
                $vo = $this->model->isExist('title',$data['title'],$data['type'],$id);
            }else{
                $vo = $this->model->isExist('title',$data['title'] ,$data['type']);
            }
            if($vo){
                $this->setData(array(), '0', '不得重复添加！');
            }
            if (!is_numeric($data['sort'])) {
                $this->setData(array(), '0', '请填写排序！');
            }
            if (!preg_match('/^[1-9][0-9]{0,2}$/', $data['sort'])) {
                $this->setData(array(), '0', '排序请填写正整数！');
            }
            if($id){
//                //校验数据
//                if (method_exists($this->model,  'checkData')) {
//                    $this->model->checkData($data,$id);
//                }
                //组装数据
                if (method_exists($this->model,  'buildData')) {
                    $data = $this->model->buildData($data,$id);
                }
                $data['upd_user'] = $this->accountId;
                $result = $this->model->doEdit($id,$data);
                if ($result) {
                    $this->setData(array(), '1', '编辑成功！');
                } else {
                    $this->setData(array(), '0', '编辑失败！');
                }
            }else{
                //校验数据
//                if (method_exists($this->model,  'checkData')) {
//                    $this->model->checkData($data);
//                }
                //组装数据
                if (method_exists($this->model,  'buildData')) {
                    $data = $this->model->buildData($data);
                }
                $data['add_user'] = $this->accountId;
                $result = $this->model->doInsert($data);
                if ($result) {
                    $this->setData(array(), '1', '添加成功！');
                } else {
                    $this->setData(array(), '0', '添加失败！');
                }
            }
        }
        //院校
//        $this->assign('school',$this->schoolMod->getAllArr());
        //分类
        $this->assign('type_list',$this->model->type);
        //映射页面
        $this->display('systemType/add.html');
    }
    /**
     * 删除分类
     * @author zhangr
     * @date 2017-12-11
     */
    public function drop(){
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        if (empty($id)) {
            $this->jsonError('系统错误！');
        }
        // 删除数据
        $res = $this->model->doMark($id);
        if ($res) {
            $this->setData(array(), '1', '删除成功！');
        } else {
            $this->setData(array(), '0', '删除失败！');
        }
    }
}