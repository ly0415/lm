<?php if(isset($list) && !empty($list)):?>
<form id="my-form1" action="<?=url('goods/batch')?>" class="am-form tpl-form-line-form" method="post">

    <?php foreach ($list as $key => $item):?>
    <div class="am-form-group">
        <label class="am-u-sm-2" style="padding:0;font-weight:500;margin:9px 0 0 0;"> <?=$item['_key']?>： </label>
        <div class="am-u-sm-10 am-u-end am-text-left">
            <?php if(isset($item['_value'])):foreach ($item['_value'] as $items):?>
            <label class="am-checkbox-inline">
                <input type="checkbox" name="batch[_batch][<?=$key?>][]" value="<?=$items[0]?>" class="am-ucheck-checkbox am-field-valid">
                <span class="am-ucheck-icons">
                <i class="am-icon-unchecked"></i>
                <i class="am-icon-checked"></i>
                </span>
                <?=$items[1]?>
            </label>
        <?php endforeach;endif;?>
        </div>
    </div>
    <?php endforeach;?>

    <div class="am-form-group">
        <label class="am-u-sm-2 form-require" style="padding:0;font-weight:500;margin:9px 0 0 0;"> 价格： </label>
        <div class="am-u-sm-10 am-u-end">
            <input type="number" value="" min="0" required name="batch[price]" class="tpl-form-input" style="border:0;border-bottom: 1px solid #d6d6d6;padding: 6px 5px;outline:none;">
        </div>
    </div>
    <div class="am-form-group">
        <label class="am-u-sm-2 form-require" style="padding:0;font-weight:500;margin:9px 0 0 0;" > 库存： </label>
        <div class="am-u-sm-10 am-u-end">
            <input type="number" value="" min="0" required name="batch[stock]" class="tpl-form-input" style="border:0;border-bottom: 1px solid #d6d6d6;padding: 6px 5px;outline:none;">
        </div>
    </div>
    <div style="border-top:1px solid #eee;padding-top:10px;text-align:right">
        <input type="hidden" name="batch[store_goods_id]" value="<?=$request->param('goods_id')?>">
        <button type="submit" class="am-btn j-submit am-btn-secondary am-btn-xs">保存</button>
        <button type="button" class="am-btn am-btn-secondary am-btn-xs" data-am-modal-close>取消</button>
    </div>
</form>
<?php endif;?>
<script>
    $(function () {
        $('#my-form1').superForm();
    })
    </script>
