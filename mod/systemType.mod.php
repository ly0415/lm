<?php
/**
 * 基础配置模块模型
 * @author luffy
 * @date 2018-07-23
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class systemTypeMod extends BaseMod {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct("system_type");
    }
    public $type = array(
        1 => '空间',
        2 => '风格',
        3 => '房型',
        4 => '房屋状态',
        5 => '预算',
    );
    /**
     * 检测配置名称是否存在
     * @author zhangr
     * @date 2017/12/07
     */
    public function isExist($name, $value,$type, $id = 0){
        $cond = "{$name}='{$value}'";
        if($type){
            $cond.=" and  type = {$type}";
        }
        $cond .= '  and mark =1  ';
        if ($id) {
            $cond .= " AND id != {$id}";
        }
        $query = array('fields' => 'id', 'cond' => $cond);
        $info = $this->getOne($query);
        $id = (int)$info['id'];
        return $id;
    }

    /**
     * 基础配置数据初始化
     * @author luffy
     * @date 2018-07-23
     */
    function initBasicConfig($school_id){
        $initData = $this->initBasicData;
        $initSql = 'INSERT INTO '.DB_PREFIX.'system_type (title, relation_id, type, sort, add_user, add_time) VALUES ';
        foreach($initData as $key => $value){
            if( $value ){
                foreach($value as $k => $v){
                    $initSql .= '(\''.$v.'\','.$school_id.','.$key.',1,'.$this->adminId.','.time().'),';
                }
            }
        }
        $initSql = substr($initSql, 0, -1);
        $rows = $this->exeSql($initSql);
        if( $rows ){
            //缓存配置表数据
            $this->redisData(array(
                'cond' => ' relation_id = '.$school_id
            ));
        }
    }

    /**
     * 获取基础数据列表
     * @author luffy
     * @date 2018-07-23
     */
    public function getTypeList($school_id, $type, $fieldString = 'id,title'){
        $sql = "SELECT ".$fieldString." FROM ".DB_PREFIX."system_type where relation_id = {$school_id} AND type = {$type} AND mark = 1 group by sort asc,id desc";
        return $this->querySql($sql);
    }
    /**
     * 根据id获取数据
     * @author luffy
     * @date 2018-07-23
     */
    public function getOneRow($id,$obj = 0){
        $sql = "SELECT * FROM ".DB_PREFIX."system_type where  id=".$id;
        $row = $this->querySql($sql);
        if($obj == 1){
            $info =   $row;
        }else{
            $info =   $row[0]['title'];
        }
        return $info;
    }

    /**
     * 校验数据
     * @author Run
     * @date 2018-07-18
     * @param $data
     * @param $id
     * @return bool
     */
    public function checkData($data, $id)
    {
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
            $vo = $this->isExist('title',$data['title'],$data['type'],$id);
        }else{
            $vo = $this->isExist('title',$data['title'] ,$data['type']);
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
        return true;
    }

    /**
     * 组装数据
     * @author Run
     * @date 2018-07-18
     * @param $data
     * @param $id
     * @return array
     */
    public function buildData($data, $id)
    {
        $result = array(
            'title' => $data['title'],
            'type' => $data['type'],
            'sort' => $data['sort'],
        );
        if ($id) {
            if($result['id']) unset($result['id']);
            $result['upd_time'] = time();
        } else {
            $result['add_time'] = time();
        }
        return $result;
    }

    /**
     * 根据配置名称获取id
     *
     * @param $title
     * @param $type
     * @param $schoolId
     * @return int
     * @author  zhangkx
     * @date 2018-08-14
     */
    public function getIdByTitle($title, $type, $schoolId)
    {
        $cond = array(
            'cond' => "relation_id = {$schoolId} AND title = '{$title}' AND type = {$type} AND mark = 1"
        );
        $result = $this->getOne($cond);
        return $result['id'];
    }

    /**
     * 删除基础配置
     * @author  luffy
     * @date 2018-08-31
     */
    public function dropSystemType($school_id){
        $ids = $this->getIds(array(
            'cond' =>' mark = 1 AND relation_id in ('.$school_id.') ',
        ));
        $res = $this -> doMark($ids);
        return $res;
    }

    /**
     * 空间和风格数据
     * @author  luffy
     * @date 2018-08-31
     */
    public function typeData($type,$id){
        $where=' where 1=1 and mark=1 ';
        if(empty($id)){
            $where.=' and type='.$type;
        }else{
            $where.=' and type='.$type.' and id='.$id;
        }
        $sql="SELECT * FROM bs_system_type ".$where;
        $data=$this->querySql($sql);
        return $data;
    }
}