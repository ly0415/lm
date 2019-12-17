<?php if(isset($model) && isset($model['goods_spec'])):foreach ($model['goods_spec'] as $k => $item):?>
<div class="am-form-group" style="margin-bottom:5px;">
    <label class="am-u-sm-2 am-form-label SKU_TYPE" data-spec_id="<?=$item['id']?>"  propid="<?=$item['id']?>" sku-type-name="<?=$item['name']?>"><?=$item['name']?>:</label>
    <div class="am-u-sm-9 am-u-end">
        <?php if(isset($item['item'])): foreach ($item['item'] as $value):?>
        <label class="am-checkbox-inline" style="margin:0 15px 0 0;padding:9.6px 0 0 5px;">
            <input type="checkbox" data-item_id="<?=$value['id']?>" class="sku_value" style="margin:4px 0 0 5px;" propvalid="<?=$value['id']?>" value="<?=$value['item_names']?>">&nbsp;&nbsp;<?=$value['item_names']?>
        </label>
    <?php endforeach;endif;?>
    </div>
</div>
<?php endforeach; endif;?>