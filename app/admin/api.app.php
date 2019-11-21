<?php
/**
 * 公共api方法
 * User: xt
 * Date: 2019/1/22
 * Time: 19:21
 */

class ApiApp extends BackendApp
{
    /**
     * ApiApp constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 数据封装
     * @author luffy
     * @date 2018-09-10
     */
    public function chang_lang() {
        $shorthand  = isset($_REQUEST['shorthand']) ? trim($_REQUEST['shorthand']) : '';
        $langMod    = &m('language');
        $langInfo   = $langMod->getOne(array('cond' => " shorthand = '{$shorthand}'"));
        switch($shorthand){
            case 'ZH':
                $_SESSION['admin']['lang_num']  = 0;
                break;
            case 'EN':
                $_SESSION['admin']['lang_num']  = 1;
                break;
        }
        $_SESSION['admin']['lang_id']   = $langInfo['id'];
        $_SESSION['admin']['shorthand'] = $langInfo['shorthand'];
        $this->setData(array(), $status= 1);
    }

    /**
     * 通过店铺区域联动
     * @author xt
     * @date 2018/01/22
     */
    public function getServiceSelect()
    {
        $type = $_REQUEST['type'] ? (int)$_REQUEST['type'] : 0;
        $id = $_REQUEST['id'] ? (int)$_REQUEST['id'] : 0;
        switch ($type) {
            // 获取店铺
            case 1:
                $data = &m('store')->getStoreArr($id, 1);
                $data = &m('api')->convertArrForm($data);
                break;
            // 获取一级业务类型
            case 2:
                $data = &m('api')->getTop($id);
                break;
            // 获取二级业务类型
            default :
                $data = &m('api')->getSecond($id);
        }
        $this->setData($data, 1);
    }

    /**
     * 通过店铺区域联动获取店员
     * @author xt
     * @date 2019/03/20
     */
    public function storeUsersSelect()
    {
        $type = $_REQUEST['type'] ? (int)$_REQUEST['type'] : 0;
        $id = $_REQUEST['id'] ? (int)$_REQUEST['id'] : 0;
        switch ($type) {
            // 获取店铺
            case 1:
                $data = &m('store')->getStoreArr($id, 1);
                $data = &m('api')->convertArrForm($data);
                break;
            // 获取店员
            case 2:
                $data = &m('storeUser')->storeUsers($id);
                break;
        }
        $this->setData($data, 1);
    }

    /**
     * 检查分销人员余额是否正确
     * @author zhangkx
     * @date 2019/3/5
     */
    public function checkMoney()
    {
        $orderMod = &m('order');
        $fxUserMod = &m('fxuser');
        $cashMod = &m('fxOutmoneyApply');
        $total = array();
        $userSql = 'select * from bs_fx_user where id not in (4,5,40,92,103)';
        $user = $fxUserMod->querySql($userSql);
        foreach ($user as $key => $value) {
            //计算已入账佣金
            if ($value['level'] == 1) {
                $lev2 =$fxUserMod->getData(array('cond'=>'parent_id='.$value['id'].' and level = 2'));
                if ($lev2) {
                    foreach ($lev2 as $k => $v){
                        $secondFxUserIdData[]=$v['id'];
                    }
                    $secondFxUserIds=implode(',',$secondFxUserIdData);
                    unset($secondFxUserIdData);
                    $lev3 =$fxUserMod->getData(array('cond'=>'parent_id in ('.$secondFxUserIds.') and level = 3'));
                    if ($lev3) {
                        foreach($lev3 as $k=>$v){
                            $thirdFxUserIdData[]=$v['id'];
                        }
                        $thirdFxUserIds=implode(',',$thirdFxUserIdData);
                        unset($thirdFxUserIdData);
                        $sql = 'SELECT
                        a.pay_money,
                            SUM(
                                ROUND((a.pay_money * d.lev1_prop * 0.01),2)
                            ) as total,
                         a.fx_user_id,
                         c.parent_id,
                         c. LEVEL,
                         d.lev1_prop,
                         d.lev2_prop,
                         d.lev3_prop,
                         c.discount,
                         c.monery
                        FROM
                            bs_fx_order AS a
                        LEFT JOIN bs_order AS b ON a.order_id = b.order_id
                        LEFT JOIN bs_fx_user AS c ON a.fx_user_id = c.id
                        LEFT JOIN bs_fx_rule AS d ON a.rule_id = d.id
                        WHERE
                            a.is_on = 1
                      AND a.fx_user_id IN ('.$thirdFxUserIds.')';
                        $money = $orderMod->querySql($sql);
                    }
                }
                $total[$value['id']]['total'] = $money[0]['total'] ? $money[0]['total'] : 0;
                unset($money);
            } elseif ($value['level'] == 2) {
                $lev3 =$fxUserMod->getData(array('cond'=>'parent_id='.$value['id'].' and level = 3'));
                if ($lev3) {
                    foreach ($lev3 as $v){
                        $idList[] = $v['id'];
                    }
                    $ids = implode(',',$idList);
                    unset($idList);
                    $sql = 'SELECT
                        a.pay_money,
                            SUM(
                                ROUND((a.pay_money * d.lev2_prop * 0.01),2)
                            ) as total,
                         a.fx_user_id,
                         c.parent_id,
                         c. LEVEL,
                         d.lev1_prop,
                         d.lev2_prop,
                         d.lev3_prop,
                         c.discount,
                         c.monery
                        FROM
                            bs_fx_order AS a
                        LEFT JOIN bs_order AS b ON a.order_id = b.order_id
                        LEFT JOIN bs_fx_user AS c ON a.fx_user_id = c.id
                        LEFT JOIN bs_fx_rule AS d ON a.rule_id = d.id
                        WHERE
                            a.is_on = 1
                      AND a.fx_user_id IN ('.$ids.')';
                    $money = $orderMod->querySql($sql);
                }
                $total[$value['id']]['total'] = $money[0]['total'] ? $money[0]['total'] : 0;
                unset($money);
            } else {
                $sql = 'SELECT
                        a.pay_money,
                            SUM(
                                ROUND((a.pay_money * (d.lev3_prop - a.fx_discount) * 0.01),2)
                            ) as total,
                         a.fx_user_id,
                         c.parent_id,
                         c. LEVEL,
                         d.lev1_prop,
                         d.lev2_prop,
                         d.lev3_prop,
                         c.discount,
                         c.monery
                        FROM
                            bs_fx_order AS a
                        LEFT JOIN bs_order AS b ON a.order_id = b.order_id
                        LEFT JOIN bs_fx_user AS c ON a.fx_user_id = c.id
                        LEFT JOIN bs_fx_rule AS d ON a.rule_id = d.id
                        WHERE
                            a.is_on = 1
                      AND a.fx_user_id IN ('.$value['id'].')';
                $money = $orderMod->querySql($sql);
                $total[$value['id']]['total'] = $money[0]['total'] ? $money[0]['total'] : 0;
            }
            //计算已提现成功的佣金
            $applySql = 'select fx_user_id,sum(apply_money) as total from bs_fx_outmoney_apply where fx_user_id = '.$value['id'].' and is_check = 2';
            $apply = $cashMod->querySql($applySql);
            $total[$value['id']]['apply'] = $apply[0]['total'] ? $apply[0]['total'] : 0;
            $total[$value['id']]['now'] = $value['monery'] ? $value['monery'] : 0;
            $total[$value['id']]['id'] = $value['id'];
            $total[$value['id']]['real_name'] = $value['real_name'];
            $total[$value['id']]['level'] = $value['level'];
            $total[$value['id']]['money'] = $total[$value['id']]['total'] - $total[$value['id']]['apply'];
            $delta = 0.00001;
            if (abs($total[$value['id']]['money'] - $total[$value['id']]['now']) < $delta) {
                $total[$value['id']]['color'] = 'green';
                $total[$value['id']]['status_name'] = '正常';

            } else {
                $total[$value['id']]['color'] = 'red';
                $total[$value['id']]['status_name'] = '异常';
            }
        }
        echo '<table border="1">';
        echo '<tr>'.
            '<td>id</td>'.
            '<td>姓名</td>'.
            '<td>等级</td>'.
            '<td>已入账总佣金</td>'.
            '<td>已提现总额</td>'.
            '<td>理论账户余额</td>'.
            '<td>实际账户余额</td>'.
            '<td >状态</td>'.
            '</tr>';
        foreach ($total as $key => &$value) {
            echo '<tr>'.
                '<td>'.$value['id'].'</td>'.
                '<td>'.$value['real_name'].'</td>'.
                '<td>'.$value['level'].'</td>'.
                '<td>'.$value['total'].'</td>'.
                '<td>'.$value['apply'].'</td>'.
                '<td>'.$value['money'].'</td>'.
                '<td>'.$value['now'].'</td>'.
                '<td style="color: '.$value['color'].'">'.$value['status_name'].'</td>'.
                '</tr>';
        }
        echo '</table>';
    }

    /**
     * 获取子市区分类
     * @author zhangkx
     * @date 2019-03-22
     */
    public function getChild(){
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

        if( empty($id) ){
            $this->jsonError('系统错误');
        }
        $systemCityMod = &m('systemCity');
        $cityDatas = $systemCityMod->getData(array(
            'cond' =>' parent_id = '.$id,
            'fields' => '*'
        ));
        echo json_encode(array_values($cityDatas));
        exit;
    }

    /**
     * 获取活动
     * @author zhangkx
     * @date 2019/4/1
     */
    public function getActivity()
    {
        $type = !empty($_REQUEST['type']) ? intval($_REQUEST['type']) : 1;
        $storeId = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
        $activityMod = &m('storeActivity');
        $cond = 'mark = 1 and store_id = '.$storeId.' and type = ' . $type . ' and begin_time <= ' . time() .' and end_time >= ' . time();
        $data = $activityMod->getOne(array('cond'=>$cond));
        if (!$data) {
            $this->setData(array(), 0);
        }
        $this->setData($data, 1);
    }

    /**
     * 获取活动规则
     * @author zhangkx
     * @date 2019/4/2
     */
    public function getFission()
    {
        $storeId = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
        $fissionMod = &m('fission');
        $data = $fissionMod->getData(array('cond'=>'mark = 1 and store_id = '.$storeId));
        if (!$data) {
            $this->setData(array(), 0);
        }
        $this->setData($data, 1);
    }

}