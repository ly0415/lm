<div class="am-modal-dialog" style="width:450px;max-height:450px;overflow:auto;border-top:3px solid #ffa627;">
    <div class="am-modal-hd am-text-left"></div>
    <div class="am-modal-bd" style="position: relative;padding-bottom:0;">
        <div class="specBox">
            <?php if(isset($list) && !empty($list)):foreach ($list as $spec):?>
            <div class="row">
                <div class="specName"><?=$spec['spec_name']?>：</div>
                <div class="specVal" >
                    <?php if(isset($spec['itemInfo']) && !empty($spec['itemInfo'])):foreach ($spec['itemInfo'] as $k => $item):?>
                    <span data-item="<?=$item['item_id']?>" class="<?=$k === 0 ? 'activeAttr':''?>"><?=$item['item_name']?></span>
                    <?php endforeach;else:?>
                    <span>暂无规格值</span>
                    <?php endif;?>
                </div>
            </div>
            <?php endforeach;else:?>
            <div class="row">
                <div class="specName">暂无规格</div>
            </div>
            <?php endif;?>
            <div class="row">
                <div class="specName">数量：</div>
                <div class="specVal">
                    <input class="numBox" type="text" style="border: 1px solid rgb(196, 196, 196);width:150px;" value="">
                </div>
            </div>
            <div class="row">
                <div class="specName">单价：</div>
                <div class="specVal">
                    <input class="numBox" type="text" style="border: 1px solid rgb(196, 196, 196);width:150px;" value="">
                </div>
            </div>
        </div>
        <input type="hidden" name="store_goods_id" value="<?=$request->post('store_goods_id')?>">
        <div class="btnBox">
            <button type="button" class="addCart" data-am-modal="{target: '#doc-modal-1'}">确定</button>
        </div>
    </div>
</div>