<?php
/**
 * 分销人员对应会员表模型
 * @author: luffy
 * @date  : 2018-10-16
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class FxUserAccountMod extends BaseMod {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct("fx_user_account");
    }

    /**
     * 传入的分销人员ID为三级分销人员
     * is_count 为0返回三级分销人员下所有会员 ----为1校验分销人员下是否有会员
     * @author: luffy
     * @date  : 2018-10-16
     */
    public function checkUserAccount($fx_user_id, $is_count = 0, $fields = '*') {
        $fxuserMod  = &m('fxuser');
        $fxInfo     = $fxuserMod  ->getRow($fx_user_id);

        if( $fxInfo['level'] != 3 ){
            return false;
        }

        if( $is_count == 1 ){
            $count = $this->getCount(array('cond'=>' fx_user_id = '.$fx_user_id));
            return $count;
        } else {
            $sql = 'SELECT '.$fields.' FROM '
                .DB_PREFIX.'fx_user_account AS a LEFT JOIN '
                .DB_PREFIX.'user AS b ON a.user_id = b.id WHERE a.fx_user_id = '.$fx_user_id;
            $data = $this->querySql($sql);
        }
        return $data;
    }

    /**
     * 三级分销人员替换的会员转移（一级二级无需会员转移）
     * 传入的分销人员ID为三级分销人员
     * @params  $fx_user_id1      替换人
     * @params  $fx_user_id2      被替换人
     * @author: luffy
     * @date  : 2018-10-16
     */
    public function fxAccountChange($fx_user_id1, $fx_user_id2) {
        $fxuserMod  = &m('fxuser');
        $fxInfo1    = $fxuserMod  ->getRow($fx_user_id1);
        $fxInfo2    = $fxuserMod  ->getRow($fx_user_id2);

        if( $fxInfo1['level'] != 3 || $fxInfo2['level'] != 3 ){
            return false;
        }

        //转移会员
        $relation_ids = $this->getIds(array(
           'cond' => ' fx_user_id = '.$fx_user_id2
        ));
        if( $relation_ids && is_array($relation_ids) ){   //替换
            $relation_ids = implode(',', $relation_ids);
            $sql          = 'UPDATE '.DB_PREFIX.'fx_user_account SET fx_user_id = '.$fx_user_id1.' WHERE id IN ('.$relation_ids.')';
            $res  = $this->doEditSql($sql);
            return $res;
        }
    }

    /**
     * 会员绑定分销人员
     * @params  $fx_user_id   分销人员ID
     * @params  $user_id      会员ID
     * @author: luffy
     * @date  : 2018-10-17
     */
    public function addFxUser($fx_user_id, $user_id, $type = 9) {
        //查询当前会员是否绑定分销人员
        $fxUserAccountMod = &m('fxUserAccount');
        $info = $fxUserAccountMod->getOne(array(
            'cond'   => ' user_id = '.$user_id
        ));
        //获取分销人员code
        $fxuserMod  = &m('fxuser');
        $oldFxUser  = $fxuserMod->getRow($fx_user_id);
        if( empty($info) ){
            $fxUserAccountMod ->doInsert(array(
                'fx_user_id' => $fx_user_id,
                'user_id'    => $user_id,
            ));
        } elseif( $fx_user_id != $info['fx_user_id'] ) {
            $fxUserAccountMod ->doEdit($info['id'], array(
                'fx_user_id' => $fx_user_id,
                'user_id'    => $user_id,
            ));
        }
        $old_code = 0;
        if(in_array($type, array(6,8))){
            $old_code = $info['fx_code'];
        }
        if(in_array($type, array(5,6))){
            $create_user = $_SESSION['store']['userId'];
        } else {
            $create_user = $user_id;
        }
        //生成变更记录
        $sql = 'INSERT INTO '.DB_PREFIX.'fx_user_change_log (type, user_arr, old_fx_code, new_fx_code, create_user, create_time) VALUES ('.$type.','.$user_id.','.$old_code.','.$oldFxUser['fx_code'].','.$create_user.','.time().')';
        $this->querySql($sql);
        return true;
    }
}