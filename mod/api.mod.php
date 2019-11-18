<?php
/**
 * 公共api模型
 * User: xt
 * Date: 2019/1/22
 * Time: 19:34
 */

class ApiMod extends BaseMod
{

    public $defaulLang;
    /**
     * ApiMod constructor.
     */
    public function __construct()
    {
       parent::__construct();
       $this->defaulLang = $_SESSION['admin']['lang_id'];
    }

    /**
     * 获取一级业务类型
     * @author tangp
     * @date 2019-01-21
     * @param $id
     * @return mixed
     */
    public function getTop($id)
    {
        $sql = "SELECT rtl.type_name as name,rtl.type_id as id  FROM bs_store_business AS sb 
        LEFT JOIN bs_room_type as rt ON sb.buss_id = rt.id
        LEFT JOIN bs_room_type_lang as rtl ON rt.id = rtl.type_id
        WHERE sb.store_id={$id} AND rt.superior_id=0 AND rtl.lang_id=".$this->defaulLang;
        $storeBusinessMod=&m('storebusiness');
        $res = $storeBusinessMod->querySql($sql);
        return $res;
    }

    /**
     * 获取二级业务
     * @author tangp
     * @date 2019-01-21
     * @param $id
     * @return mixed
     */
    public function getSecond($id)
    {
        $sql ="SELECT brtl.type_id as id,brtl.type_name as name FROM bs_room_type as brt LEFT JOIN bs_room_type_lang as brtl ON brt.id = brtl.type_id WHERE brt.superior_id = {$id} AND brtl.lang_id=".$this->defaulLang;
        $mod = &m('roomType');
        $res = $mod->querySql($sql);
        return $res;
    }

    /**
     * 转换数组结构
     * @author xt
     * @date 2019-01-22
     * @param $data
     * @return array
     */
    public function convertArrForm($data)
    {
        return array_map(function ($i, $m) {
            return array('id' => $i, 'name' => $m);
        }, array_keys($data), $data);
    }

}