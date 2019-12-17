<?php if(isset($list)):foreach ($list as $v):?>
<li>
    <div class="imgBox">
        <img src="../<?=$v['original_img']?>" />
    </div>
    <div class="bottomPart">
        <div class="goodsName"><?=$v['goods_name']?></div>
        <div class="priceBox">
            <span>￥<?=$v['shop_price']?></span>
            <button class="selSpec" data-id="<?=$v['id']?>" type='button' data-am-modal="{target: '#doc-modal-1'}">选规格</button>
        </div>
    </div>
</li>
<?php endforeach;endif;?>