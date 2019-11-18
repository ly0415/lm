<?php
/**
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class  LanguageMod  extends  BaseMod{

    public function __construct() {
        parent::__construct("language");
    }
    /**
     * 获取语言方法
     * @author: wanyan
     * @date: 2017/10/09
     */
    public function getLanguage(){
        $query=array(
            'fields' =>" `id`,`name`,`name_en`,`shorthand`"
        );
        $rs = $this->getData($query);
        return $rs;
    }

    /**
     * 获取语言数组
     * @author: luffy
     * @date  : 2017-11-07
     */
    public function getlangArr($type = 0) {
        $query = array(
            'fields' => 'id, name'
        );
        $langArr = $this->getData($query);
        if( $type ){
            $result = array();
            foreach($langArr as $k => $v){
                $result[$v['id']] = $v['name'];
            }
            $langArr = $result;
        }
        return $langArr;
    }
}