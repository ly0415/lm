<?php
/**
 * Created by PhpStorm.
 * User: gao
 * Date: 2018/11/1 0001
 * Time: 上午 9:56
 */

if (!defined('IN_ECM')) { die('Forbidden'); }
class HomefixPictureMod extends BaseMod
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct("homefix_picture");
    }

    public function getList($pictureName,$onsale,$area,$style){
        $where=' where 1=1';
        if(!empty($pictureName)){
            $where .= '  and  name  like  "%' . $pictureName . '%"';
        }
        if(!empty($area)){
            $where .= '  and  area_id = ' . $area;
        }
        if (!empty($style)) {
            $where .= '  and  style_id = ' . $style;
        }
        if (!empty($onsale)) {
            $where .= '  and   mark= ' . $onsale;
        }
        $sql="SELECT * FROM bs_homefix_picture ".$where;

        $data=$this->querySql($sql);
        return  $data;
    }
}