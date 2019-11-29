
<div class="am-modal-dialog" style="width: 600px;">
    <div class="am-modal-hd" style="padding-top: 0;">
        <div class="widget-head am-cf" style="margin:0;">
            <div class="widget-title am-fl">商品规格</div>
        </div>
        <a href="javascript: void(0)" class="am-close am-close-spin" data-am-modal-close>&times;</a>
    </div>
    <div class="am-modal-bd">
        <div class="specdialog" style="height: 250px;padding:20px;overflow-y:hidden;overflow:auto">
            <input type="hidden" name="store_goods_id" value="<?=$request->post('store_goods_id')?>">
            <input type="hidden" name="stock" value="">
            <input type="hidden" name="price" value="">
            <?php if(isset($list) && !empty($list)):foreach ($list as $kk => $spec):?>
            <div style="display:flex">
                <span style="padding-left:5px;display:inline-block;width:80px;color: #333;" class="am-u-sm-1 am-checkbox-inline am-text-left" is_required="1" propid="1"><?=$spec['spec_name']?></span>
                <div class="am-u-sm-11 am-text-left">
                    <?php if(isset($spec['itemInfo']) && !empty($spec['itemInfo'])):foreach ($spec['itemInfo'] as $k => $item):?>
                    <label  class="am-checkbox-inline" style="margin: 0 3px 0 0">
                        <input type="radio" <?=$k == 0 ? 'checked':''?> class="sku_value" name="key_<?=$kk?>" value="<?=$item['item_id']?>" data-specname="<?=$item['item_name']?>">&nbsp;&nbsp;<?=$item['item_name']?>
                    </label>
                    <?php endforeach;else:?>
                        <span>暂无规格值</span>
                    <?php endif;?>
                </div>
            </div>
            <?php endforeach;endif;?>
        </div>
    </div>

    <div class="am-modal-footer">
        <span class="am-modal-btn">确定</span>
    </div>
</div>