<?php
    /**
     * 买赠活动
     * @author wanyan
     * @date 2017-11-3
     */

class  GiftActivityApp extends BaseFrontApp{

    private $storeGoodsMod;
    private $cartMod;
    private $storeMod;
    private $userAddressMod;
    private $orderMod;
    private $orderDetailMod;

    public function __construct()
    {
        parent::__construct();
        $this->storeGoodsMod =  &m('areaGood');
        $this->cartMod = &m('cart');
        $this->storeMod = &m('store');
        $this->userAddressMod = &m('userAddress');
        $this->orderMod = &m('order');
        $this->orderDetailMod =& m('orderDetail');
    }

}