<link rel="stylesheet" href="assets/admin/css/set_attribute.css">
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title">编辑商品</div>
                </div>
                <div id='doc-my-tabs' data-am-tabs class="am-tabs widget am-cf">
                    <ul class="am-tabs-nav am-nav am-nav-tabs">
                        <li class="am-active"><a href="javascript: void(0)">基础信息</a></li>
                        <?php if($details['isExistSpec']): ?>
                            <li><a href="javascript: void(0)">规格信息</a></li>
                        <?php endif;?>
                    </ul>
                    <div class="am-tabs-bd">
                        <div class="am-tab-panel am-active am-in">
                            <div class="widget am-cf">
                                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                                    <input type="hidden" name="type" value="1">
                                    <input type="hidden" name="store_goods_id" value="<?= $details['id'] ?>">
                                    <div class="am-form-group">
                                        <label for="originalStock" class="am-u-sm-2 am-form-label"> 商品名称：</label>
                                        <div class="am-u-sm-8 am-u-end">
                                            <input id="originalStock" autocomplete="off" type="text" class="tpl-form-input" value="<?= $details['goods_name'] ?>" disabled>
                                        </div>
                                    </div>
                                    <div class="am-form-group">
                                        <label for="mySopPrice" class="am-u-sm-2 am-form-label"> 商品分类： </label>
                                        <div class="am-u-sm-8 am-u-end">
                                            <input id="mySopPrice" autocomplete="off" type="text" class="tpl-form-input" value="<?= $details['format_category'][0] ?>" disabled>
                                        </div>
                                    </div>
                                    <div class="am-form-group">
                                        <label for="marketValue" class="am-u-sm-2 am-form-label"> 业务类型： </label>
                                        <div class="am-u-sm-8 am-u-end">
                                            <input id="marketValue" autocomplete="off" type="text" class="tpl-form-input" value="<?= $details['format_business_name'] ?>" disabled>
                                            <?php if($details['format_auxiliarys']): ?>
                                                <small>辅助业务类型：<span class="am-badge am-badge-success am-radius"><?= $details['format_auxiliarys'] ?></span></small>
                                            <?php endif;?>
                                        </div>
                                    </div>
                                    <div class="am-form-group">
                                        <label for="marketValue" class="am-u-sm-2 am-form-label"> 市 场 价：</label>
                                        <div class="am-u-sm-8 am-u-end">
                                            <input id="marketValue" autocomplete="off" type="text" class="tpl-form-input" name="market_price" value="<?= $details['market_price'] ?>" disabled>
                                        </div>
                                    </div>
                                    <div class="am-form-group">
                                        <label for="marketValue" class="am-u-sm-2 am-form-label form-require"> 本店售价： </label>
                                        <div class="am-u-sm-8 am-u-end">
                                            <input id="marketValue" autocomplete="off" type="text" class="tpl-form-input" name="shop_price" value="<?= $details['shop_price'] ?>" placeholder="本店售价" required <?php if($details['deduction'] == 1): ?>disabled<?php endif; ?>>
                                        </div>
                                    </div>
                                    <div class="am-form-group">
                                        <label for="marketValue" class="am-u-sm-2 am-form-label form-require"> 本店库存： </label>
                                        <div class="am-u-sm-8 am-u-end">
                                            <input id="marketValue" autocomplete="off" type="text" class="tpl-form-input" name="goods_storage" value="<?= $details['goods_storage'] ?>" <?php if((T_GENERAL && $details['deduction'] == 1) || $details['isExistSpec']): ?>disabled<?php endif;?> required>
                                            <small>库存扣除方式：<span class="am-badge am-badge-<?= $details['deduction'] == 2 ? 'success' : 'warning' ?> am-radius"><?= $details['format_deduction'] ?></span></small>
                                        </div>
                                    </div>
                                    <?php if(!$details['isExistSpec']): ?>
                                        <div class="am-form-group">
                                            <label for="" class="am-u-sm-2 am-form-label"> 条形码： </label>
                                            <div class="am-u-sm-8 am-u-end">
                                                <input id="" autocomplete="off" type="text" class="tpl-form-input codeBox" name="bar_code" value="<?= $details['bar_code'] ?>">
                                                <small>条形码最多由20个数字组成</small>
                                            </div>
                                        </div>
                                    <?php endif;?>
                                    <div class="am-form-group">
                                        <label for="marketValue" class="am-u-sm-2 am-form-label form-require"> 配送属性： </label>
                                        <div class="am-u-sm-8 am-u-end">
                                            <?php if($deliveryType):foreach ($deliveryType as $key => $items):?>
                                                <label class="am-checkbox-inline">
                                                    <input type="checkbox" name="attributes[]" <?php if(in_array($key, $details['format_attributes_arr'])):?>checked<?php endif;?> value="<?= $key ?>" class="am-ucheck-checkbox am-field-valid"><span class="am-ucheck-icons"><i class="am-icon-unchecked"></i><i class="am-icon-checked"></i></span>
                                                    <?= $items ?>
                                                </label>
                                            <?php endforeach;?>
                                            <?php endif;?>
                                        </div>
                                    </div>
                                    <div class="am-form-group">
                                        <label for="marketValue" class="am-u-sm-2 am-form-label form-require"> 排序： </label>
                                        <div class="am-u-sm-8 am-u-end">
                                            <input id="marketValue" autocomplete="off" type="text" class="tpl-form-input" name="sort" value="<?= $details['sort'] ?>" placeholder="本店售价" <?php if(T_GENERAL): ?>disabled<?php endif;?>>
                                        </div>
                                    </div>
                                    <div class="am-form-group">
                                        <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                            <button type="submit" class="j-submit am-btn am-btn-secondary">提交</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="am-tab-panel">
                            <div class="widget am-cf">
                                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                                    <div class="am-form-group"  id="j-spec-table"></div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="am-modal" tabindex="-1" id="doc-modal-1">
                    <div class="am-modal-dialog">
                        <div class="am-modal-hd am-text-left">设置
                            <a href="javascript: void(0)" class="am-close am-close-spin" data-am-modal-close>&times;</a>
                        </div>
                        <div class="am-modal-bd">
                            <div class="specBox">
                                <div class="row">
                                    <div class="specName">配选：</div>
                                    <div class="specVal">
                                        <span data-item="2065" class="activeAttr">不选</span>
                                        <span data-item="2193" class="">加太妃榛果3元</span>
                                        <span data-item="2194" class="">加榛果2元</span>
                                        <span data-item="2195" class="">加香草2元</span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="specName">甜度选择：</div>
                                    <div class="specVal">
                                        <span data-item="2074" class="activeAttr">标准甜度1.0</span>
                                        <span data-item="2075" class="">加甜1.2</span>
                                        <span data-item="2141" class="">少甜0.8</span>
                                        <span data-item="2142" class="">微甜0.6</span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="specName">温度选择：</div>
                                    <div class="specVal">
                                        <span data-item="2144" class="activeAttr">去冰</span>
                                        <span data-item="2146" class="">冰沙</span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="specName">杯选择：</div>
                                    <div class="specVal">
                                        <span data-item="2149" class="activeAttr">常规杯R</span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="specName">价格：</div>
                                    <input type="text" class="am-fl">
                                </div>
                                <div class="row">
                                    <div class="specName">库存：</div>
                                    <input type="text" class="am-fl">
                                </div>
                                <div class="row">
                                    <div class="specName">SKU：</div>
                                    <input type="text" class="am-fl">
                                </div>
                            </div>
                        </div>
                        <div class="am-modal-footer">
                            <span class="am-modal-btn" data-am-modal-cancel>取消</span>
                            <span class="am-modal-btn" data-am-modal-confirm>确定</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- 图片文件列表模板 -->
{{include file="layouts/_template/tpl_file_item" /}}

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<script>
    $(function () {

        $('#doc-my-tabs').tabs({noSwipe: 1});

        // 选择图片
        $('.upload-file').selectImages({
            name: 'coupon[relation_2]'
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

        $.post("<?= url('Goods/getSpecInput') ?>", {goods_id: <?= $details['id'] ?>}, function (result) {
            $("#j-spec-table").append(result.data);
            hbdyg();  // 合并单元格
        });

        // 合并单元格
        function hbdyg() {
            var tab = document.getElementById("spec_input_tab"); //要合并的tableID
            var maxCol = 2, val, count, start;  //maxCol：合并单元格作用到多少列
            if (tab != null) {
                for (var col = maxCol - 1; col >= 0; col--) {
                    count = 1;
                    val = "";
                    for (var i = 0; i < tab.rows.length; i++) {
                        if (val == tab.rows[i].cells[col].innerHTML) {
                            count++;
                        } else {
                            if (count > 1) { //合并
                                start = i - count;
                                tab.rows[start].cells[col].rowSpan = count;
                                for (var j = start + 1; j < i; j++) {
                                    tab.rows[j].cells[col].style.display = "none";
                                }
                                count = 1;
                            }
                            val = tab.rows[i].cells[col].innerHTML;
                        }
                    }
                    if (count > 1) { //合并，最后几行相同的情况下
                        start = i - count;
                        tab.rows[start].cells[col].rowSpan = count;
                        for (var j = start + 1; j < i; j++) {
                            tab.rows[j].cells[col].style.display = "none";
                        }
                    }
                }
            }
        }

        $(document).on('keyup','.codeBox',function(){
            this.value=this.value.replace(/[^\d]/g,'')
            var inputdata=$(this).val()
            if(inputdata.length>=20){
                layer.msg('条形码最多由20位数字组成')
                inputdata=inputdata.slice(0,20)
                $(this).val(inputdata)
            }
        })

        //更新数据
        $('body').on('change','.j-edit-data1',function(){
            $.post("<?= url('Goods/edit') ?>", {goods_id: <?= $details['id'] ?>, value_data: $(this).val(), spec_key: $(this).attr('j-item-key'), type: 2, tp: 1}, function (result) {

            });
        });
        $('body').on('change','.j-edit-data2',function(){
            $.post("<?= url('Goods/edit') ?>", {goods_id: <?= $details['id'] ?>, value_data: $(this).val(), spec_key: $(this).attr('j-item-key'), type: 2, tp: 2}, function (result) {

            });
        });
        $('body').on('change','.j-edit-data3',function(){
            $.post("<?= url('Goods/edit') ?>", {goods_id: <?= $details['id'] ?>, value_data: $(this).val(), spec_key: $(this).attr('j-item-key'), type: 2, tp: 3}, function (result) {

            });
        });

        $('body').on('change','.j-edit-data4',function(){
            $.post("<?= url('Goods/edit') ?>", {goods_id: <?= $details['id'] ?>, value_data: $(this).val(), spec_key: $(this).attr('j-item-key'), type: 2, tp: 4}, function (result) {

            });
        });

        // 选择弹框中的规格
        $(document).on('click','.specVal>span',function(){
            $(this).addClass('activeAttr').siblings().removeClass('activeAttr');
        });
    });
</script>