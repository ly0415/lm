<?php
/**
 * 系统缓存管理
 * @author: luffy
 * @date  : 2018-12-10
 */
if (!defined('IN_ECM')) {die('Forbidden');}
class SystemRedisApp extends BackendApp{

    public $lang_num    = 0;
    public $redis_arr   = array();

    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct();

        //引入redis扩展类
        import('redis.lib');
        $this->redis    = new RedisCacheServer();
        $this->lang_num = $_SESSION['admin']['lang_num'];
        $this->redis->select($this->lang_num);

        //定义英文状态下需要删除和重置缓存的表
        $this->redis_arr = array('bs_goods_category');
    }

    /**
     * 析构函数
     */
    public function __destruct(){
    }

    /**
     * 数据缓存页面
     * @author zhangkx
     * @date 2018/12/7
     */
    public function index(){
        $commit     = $_REQUEST['commit'] ? htmlspecialchars(trim($_REQUEST['commit'])):'';
        $name       = $_REQUEST['name'] ? htmlspecialchars(trim($_REQUEST['name'])):'';

        //只缓存分类表
        $where = ' WHERE table_type=\'base table\'and table_schema = \'bspm711\' and table_name in (\'bs_store_cate\',\'bs_goods_category\',\'bs_store_goods\')';
        if($name){
            $where .=' and table_name like "%' . $name . '%"';
            $this->assign('name', $name);
        }
        if($commit){
            $where .=' and TABLE_COMMENT like "%' . $commit . '%"';
            $this->assign('commit', $commit);
        }

        //获取表结构
        $accountMod = &m('account');
        $sql = 'SELECT table_name name,TABLE_COMMENT commit FROM INFORMATION_SCHEMA.TABLES '.$where.' order by table_name asc';
        $listTables = $accountMod->querySql($sql);

        //表数据查询
        foreach($listTables as $key => $value){
            $markSql = '';
            //判断是否存在mark字段
            $res = $accountMod -> querySql('SELECT * FROM information_schema.columns WHERE table_schema = \'bspm711\' AND table_name = \''.$value['name'].'\' AND column_name = \'mark\'');
            if( count($res) > 0 ){
                $markSql = ' where mark = 1 ';
            }
            //总记录数
            $count = $accountMod -> querySql("SELECT COUNT(*) AS rcnt FROM {$value['name']} " . $markSql);
            $listTables[$key]['num'] = $count[0]['rcnt'] ? $count[0]['rcnt'] : '---';
            //总缓存数
            $modName = buildModName($value['name']);
            $redisDatas = $this->redis->all("{$modName}Mod_info_*");
            $countRedisDatas = count($redisDatas);
            $listTables[$key]['redisNum'] = $countRedisDatas ? $countRedisDatas : '---';
        }
        $this->assign('lang_num',   $_SESSION['admin']['lang_num']);
        $this->assign('listTables', $listTables);
        $this->display('systemRedis/index.html');
    }

    /**
     * 重置缓存
     * @author zhangkx
     * @date 2018/12/7
     */
    public function reset(){
        $modName = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) : '';
        if ($this->lang_num == 1 && !in_array($modName, $this->redis_arr)) {
            $this->setData(array(), $status = '0', $message = 'no need to cache!');
        }
        if (empty($modName)) {
            $this->setData(array(), $status = '0', $message = 'parameter error!');
        }
        $table = $modName;
        $markSql = '';
        //判断是否存在mark字段
        $accountMod = &m('account');
        $res = $accountMod -> querySql('SELECT * FROM information_schema.columns WHERE table_schema = \'bspm711\' AND table_name = \''.$modName.'\' AND column_name = \'mark\'');
        if( count($res) > 0 ){
            $markSql = ' where mark = 1 ';
        }
        $modName = buildModName($modName);
        $modMod = &m($modName);
        $sql = "select id from ".$table.$markSql;
        $datas = $modMod->querySql($sql);
        if(empty($datas)){
            $this->setData(array(), $status = '0', $this->langDataBank->project->table_no_data);
        } else {
            //删除该表缓存
            $redisDatas = $this->redis->all("{$modName}Mod_info_*");
            $this->redis->dropArr($redisDatas);

            //缓存该表所有数据
            foreach($datas as $value){
                $info = $modMod->grInfo($value['id']);
            }

            if(method_exists($modMod , 'relationRedis')){
                $modMod->relationRedis();
            }
        }
        if ($info) {
            $this->setData(array(), $status = '1', $this->langDataBank->project->reset_success);
        } else {
            $this->setData(array(), $status = '0', $this->langDataBank->project->reset_fail);
        }
    }

    /**
     * 重置全表缓存
     * @author zhangkx
     * @date 2018/12/7
     */
    public function resetAll(){
        //清楚当前分支所有缓存
        $this->redis->dropAll();

        //日志表不缓存
        $where = ' WHERE table_type=\'base table\'and table_schema = \'bspm711\' and table_name != \'bs_system_log\'';
        //获取表结构
        $accountMod = &m('account');
        $sql = 'SELECT table_name name,TABLE_COMMENT commit FROM INFORMATION_SCHEMA.TABLES '.$where.' order by table_name asc';
        $listTables = $accountMod->querySql($sql);
        //表数据查询
        foreach($listTables as $key => $value){
            $markSql = '';
            //判断是否存在mark字段
            $res = $accountMod -> querySql('SELECT * FROM information_schema.columns WHERE table_schema = \'bspm711\' AND table_name = \''.$value['name'].'\' AND column_name = \'mark\'');
            if( count($res) > 0 ){
                $markSql = ' where mark = 1 ';
            }
            $modName = buildModName($value['name']);
            $modMod = &m($modName);
            $sql = "select id from ".$modMod->table.$markSql;
            $datas = $modMod->querySql($sql);
            //查询缓存数据
            foreach($datas as $val){
                $info = $modMod -> grInfo($val['id']);
            }
        }
        if( $info ){
            $this->setData(array(), $status = '1', $this->langDataBank->project->cache_reset_fail);
        } else {
            $this->setData(array(), $status = '1', $this->langDataBank->project->reset_fail);
        }
    }

    /**
     * 缓存查询
     * @author zhangkx
     * @date 2018/12/7
     */
    public function search(){
        //获取表结构
        $accountMod = &m('account');
        $sql = 'SELECT table_name name,TABLE_COMMENT commit FROM INFORMATION_SCHEMA.TABLES WHERE table_type=\'base table\'and table_schema = \'bspm711\' order by table_name asc';
        $listTables = $accountMod->querySql($sql);

        $this->assign('listTables', $listTables);
        $this->display('systemRedis/search.html');
    }

    /**
     * 删除缓存
     * @author zhangkx
     * @date 2018/12/7
     */
    public function drop(){
        $modName = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) : '';
        if ($this->lang_num == 1 && !in_array($modName, $this->redis_arr)) {
            $this->setData(array(), $status = '0', $message = 'no need to cache!');
        }
        if (empty($modName)) {
            $this->setData(array(), $status = '0', $message = 'parameter error!');
        }
        $modName = buildModName($modName);
        //删除该表缓存
        $redisDatas = $this->redis->all("{$modName}Mod_info_*");
        if( $redisDatas ){
            $res = $this->redis->dropArr($redisDatas);
            if ( $res ) {
                $this->setData(array(), $status = '1', $this->langDataBank->public->drop_success);
            } else {
                $this->setData(array(), $status = '0', $this->langDataBank->public->drop_fail);
            }
        } else {
            $this->setData(array(), $status = '0', $this->langDataBank->project->not_clear);
        }
    }
}