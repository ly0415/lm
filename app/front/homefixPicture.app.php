<?php

/**
 * 家装美图
 * @author zhangkx
 * @date 2018/11/2
 */

class homefixPictureApp extends BaseFrontApp {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 家装美图列表
     *
     * @author zhangkx
     * @date 2018/11/2
     */
    public function index() {
          //语言包
        $this->load($this->shorthand, 'Promotion/Promotion');
        $this->assign('langdata', $this->langData);
        $this->display("homefixPicture/index.html");
    }

    /**
     * 申请设计
     *
     * @author zhangkx
     * @date 2018/11/5
     */
    public function addDesign()
    {
        $this->load($_REQUEST['lang_id'], 'user_login/user_login');
        $a = $this->langData;
        $name = $_POST['name'] ? $_POST['name'] : '';
        $phone = $_POST['phone '] ? $_POST['phone '] : '';
        $planId = $_POST['plan_id '] ? $_POST['plan_id '] : 0;
        $provinceId = $_POST['province_id '] ? $_POST['province_id '] : 0;
        $cityId = $_POST['city_id '] ? $_POST['city_id '] : 0;
        $roomType = $_POST['room_type '] ? $_POST['room_type '] : 0;
        $roomArea = $_POST['room_area '] ? $_POST['room_area '] : '';
        $roomStatus = $_POST['room_status '] ? $_POST['room_status '] : 0;
        $roomStyle = $_POST['room_style '] ? $_POST['room_style '] : 0;
        $budgetMoney = $_POST['budget_money '] ? $_POST['budget_money '] : '';
        if (empty($name)) {
            $this->setData(array(), $status = '0', '请填写姓名');
        }
        if (empty($phone)) {
            $this->setData(array(), $status = '0', '请填写联系方式');
        }
        if (empty($provinceId)) {
            $this->setData(array(), $status = '0', '请选择省');
        }
        if (empty($cityId)) {
            $this->setData(array(), $status = '0', '请填写市');
        }
        if (empty($roomType)) {
            $this->setData(array(), $status = '0', '请选择房型');
        }
        if (empty($roomArea)) {
            $this->setData(array(), $status = '0', '请填写房屋面积');
        }
        if (empty($roomStatus)) {
            $this->setData(array(), $status = '0', '请选择房屋状态');
        }
        if (empty($roomStyle)) {
            $this->setData(array(), $status = '0', '请选择爱好风格');
        }
        if (empty($budgetMoney)) {
            $this->setData(array(), $status = '0', '请选择预算');
        }
        $data = array(
            'name' => $name,
            'phone' => $phone,
            'plan_id' => $planId,
            'province_id' => $provinceId,
            'city_id' => $cityId,
//            'addr' => $addr,
            'room_type' => $roomType,
            'room_area' => $roomArea,
            'room_status' => $roomStatus,
            'room_style' => $roomStyle,
            'budget_money' => $budgetMoney,
            'add_user' => $this->userId,
            'add_time' => time(),
            'mark' => 1,
        );
//        echo '<pre>';print_r($name);die;
        $result = $this->designRequireMod->doInsert($data);
        if (!$result) {
            $this->setData(array(), $status = '0', '添加失败');
        }
        $this->setData(array(), $status = '1', '添加成功');
    }

}
