<?php if(isset($model) && isset($model['goods_attribute'])):foreach ($model['goods_attribute'] as $item ):?>
<div class="am-form-group">
    <label class="am-u-sm-2 am-u-lg-2 am-form-label"><?=$item['attr_name']?>：</label>
    <div class="am-u-sm-9 am-u-end">
        <input type="text" class="tpl-form-input" name="model[<?=$item['attr_id']?>][attr_value][]" value="" placeholder="请输入属性值" required>
    </div>
</div>
<?php endforeach;endif;?>