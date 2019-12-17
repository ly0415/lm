<?php
/**
 * 促销
 * @author gao
 * @date 2018-12-25
 */
class promotionActivityApp extends BaseWxApp
{
    public function __construct()
    {
        parent::__construct();
    }

    //促销详情页面
     public function index(){
         $this->load($this->shorthand, 'WeChat/goods');
         $this->assign('langdata', $this->langData);
         $storeGoodsId=!empty($_REQUEST['storeGoodsId']) ? $_REQUEST['storeGoodsId'] : 7094;//店铺商品id
         $activityId=!empty($_REQUEST['activityId']) ? $_REQUEST['activityId'] : 33;//活动Id
         $storeId=!empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : 84;//店铺Id
         $langId=!empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id']  : 29;//语言Id
         //判断是否登录
         if (!isset($_SESSION['userId'])) {
             header('Location: wx.php?app=user&act=loginOuth&back_app=' . APP . '&back_act=' . ACT . '&storeGoodsId=' . $storeGoodsId. '&activityId=' . $activityId .'&lang_id='.$langId.'&store_id='.$storeId);
         }
         //模型
         $goodsImgMod = &m('goodsImg');
         $storeGoodsMod = &m('areaGood');
         $promotionMod=&m('goodProm');
         $promotionGoodsMod=&m('goodPromDetail');
         $storeGoodsItemMod=&m('storeGoodItemPrice');
         $goodAttrMod = &m('goodsAttriInfo');
         //数据
         $storeGoodsData=$storeGoodsMod->getOne(array('cond'=>"`id`= '{$storeGoodsId}'",'fields'=>'goods_id,shop_price,goods_storage'));//店铺商品信息
         $prommotionData=$promotionMod->getOne(array('cond'=>"`id`= '{$activityId}' AND mark = 1 ",'fields'=>'end_time'));//促销活动信息
         $promotionGoodsData=$promotionGoodsMod->getOne(array('cond'=>"`goods_id`= '{$storeGoodsId}'  AND `prom_id`='{$activityId}' ",'fields'=>'discount_rate,goods_name,discount_price,goods_price,goods_img'));//促销活动商品信息
         $storeGoodsItemData=$storeGoodsItemMod->get_spec($storeGoodsData['goods_id'],$storeGoodsId,$langId);//促销商品规格信息
         $imgArrData = $goodsImgMod->getData(array('cond'=>"goods_id=".$storeGoodsData['goods_id']));//拿轮播图
         $storeGoodsInfo=$storeGoodsMod->getLangInfo($storeGoodsId,$langId);//店铺中英文商品信息
         $attrArr = $goodAttrMod->getLangData($storeGoodsData['goods_id'], $langId);//商品属性(暂时死数据展示，参数一 原始商品ID  参数二 语言ID)
         $discountRate=$promotionGoodsData['discount_rate'];

         if(!empty($storeGoodsItemData)){
             //有规格
             $storeGoodsItemJsonData= $storeGoodsItemMod->getSpecName($storeGoodsId,$storeId);//促销商品规格信息json格式
             foreach($storeGoodsItemData as $key=>$val){
                     $singleItemKey[]=$val[0]['item_id'];//选定规格
                     $singleItemKeyName[]=$val[0]['item'];//选定规格名称
                     $itemStr .=$val[0]['item_id'].'_';
             }
             $itemStr=rtrim($itemStr,'_');//选定规格组合
             $singleStoreGoodsItemData=$storeGoodsItemMod->getOne(array('cond'=>"`store_goods_id`= '{$storeGoodsId}'  AND `key`='{$itemStr}' ",'fields'=>'goods_storage,price,key_name'));
             $goodsKeyName=$singleStoreGoodsItemData['key_name'];
             $singleStoreGoodsItemData['price']=number_format($singleStoreGoodsItemData['price']*$discountRate/10,2);//规格价格
         }else{ //无规格
             $storeGoodsItemJsonData=array();
             $itemStr='';
             $goodsKeyName='';
             $singleStoreGoodsItemData['goods_storage']=$storeGoodsData['goods_storage'];//无规格库存
             $singleStoreGoodsItemData['price']=number_format($storeGoodsData['shop_price']*$discountRate/10,2);//无规格价格
         }
         $this->assign('attrArr',$attrArr);
         $this->assign('singleStoreGoodsItemData',$singleStoreGoodsItemData);
         $this->assign('singleItemKey',$singleItemKey);
         $this->assign('singleItemKeyName',$singleItemKeyName);
         $this->assign('goodsKeyName',$goodsKeyName);
         $this->assign('goodsKey',$itemStr);
         $this->assign('storeGoodsInfo',$storeGoodsInfo);
         $this->assign('imgArrData',$imgArrData);
         $this->assign('promotionData',$prommotionData);
         $this->assign('promotionGoodsData',$promotionGoodsData);
         $this->assign('storeGoodsItemData',$storeGoodsItemData);
         $this->assign('storeGoodsItemJsonData',$storeGoodsItemJsonData);
         $this->assign('langId',$langId);
         $this->assign('storeId',$storeId);
         $this->assign('storeGoodsId',$storeGoodsId);
         $this->assign('activityId',$activityId);
         $this->display('promotionActivity/index.html');
     }
    //库存查验
    public function checkoutNum(){
        $storeGoodsId=!empty($_REQUEST['storeGoodsId']) ? $_REQUEST['storeGoodsId'] : 0;//店铺商品id
        $activityId=!empty($_REQUEST['activityId']) ? $_REQUEST['activityId'] : 0;//活动Id
        $storeId=!empty($_REQUEST['storeId']) ? $_REQUEST['storeId'] : 0;//店铺Id
        $langId=!empty($_REQUEST['langId']) ? $_REQUEST['langId']  : 0;//语言Id
        $num=!empty($_REQUEST['num']) ? $_REQUEST['num']  : 0;//语言Id
        $goodsKey=!empty($_REQUEST['goodsKey']) ? $_REQUEST['goodsKey']  : ''; //规格
        $goodsKeyName=!empty($_REQUEST['goodsKeyName']) ? $_REQUEST['goodsKeyName']  : ''; //规格名称
        $type=!empty($_REQUEST['type'])?$_REQUEST['type'] : 0;  //js跳转判定
        $discountPrice=!empty($_REQUEST['discountPrice']) ? $_REQUEST['discountPrice'] : 0;  //促销价格
        //模型
        $storeGoodsMod = &m('areaGood');
        $promotionGoodsMod=&m('goodPromDetail');
        $storeGoodsItemMod=&m('storeGoodItemPrice');
        $orderGoodsMod=&m('orderGoods');
        //数据
        $prommotionGoodsData=$promotionGoodsMod->getOne(array('cond'=>"`goods_id`= '{$storeGoodsId}'  AND `prom_id`='{$activityId}' ",'fields'=>'limit_amount,id'));//促销活动商品信息
        $limitNum=$prommotionGoodsData['limit_amount'];//限购数量
        $buyerId=$this->userId;
        $userNum=$orderGoodsMod->getActivityOrderNum(2,$activityId,$storeGoodsId,$buyerId);//用户购买数量
        if(!empty($goodsKey)){
            $singleStoreGoodsItemData=$storeGoodsItemMod->getOne(array('cond'=>"`store_goods_id`= '{$storeGoodsId}'  AND `key`='{$goodsKey}' ",'fields'=>'goods_storage,price,key_name'));//库存信息
            $goodsStorage=$singleStoreGoodsItemData['goods_storage'];//有规格库存
        }else{
            $storeGoodsData=$storeGoodsMod->getOne(array('cond'=>"`id`= '{$storeGoodsId}'",'fields'=>'goods_id,shop_price,goods_storage'));//店铺商品信息
            $goodsStorage=$storeGoodsData['goods_storage'];//无规格库存
        }
        if($num>($limitNum-$userNum)){ //限购判断
            $this->setData(($limitNum),'0','限购'.$limitNum.'件商品');
        }
        if($num>$goodsStorage){ //库存判断
            $this->setData($goodsStorage,'0','库存不足,库存为'.$goodsStorage);
        }
        $rand = $this->buildNo(1);
        $orderNo = date('YmdHis') . $rand[0];
        $data=array(
            'langId'=>$langId,
            'storeId'=>$storeId,
            'activityId'=>$activityId,
            'activityGoodsId'=>$prommotionGoodsData['id'],
            'source'=>2,
            'goodsKey'=>$goodsKey,
            'goodsKeyName'=>$goodsKeyName,
            'goodsNum'=>$num,
            'discountPrice'=>$discountPrice,
            'orderSn'=>$orderNo
        );
        $info=base64_encode(json_encode($data));
        if($type==1){
            $arr=array('type'=>$type);
            $this->setData($arr,1,'');
        }else{
            $url="wx.php?app=orderList&act=activityIndex&info={$info}";
            $arr=array('type'=>$type,'url'=>$url);
            $this->setData($arr,1,'');
        }
    }
}