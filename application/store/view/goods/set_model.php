<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-body am-fr">
                    <form id="my-form" class="am-form tpl-form-line-form">
                        <div class="widget-body">
                            <div class="widget-head am-cf">
                                <div class="widget-title a m-cf">商品模型</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-2 am-form-label form-require">商品模型：</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="model[goods_type]" required
                                            data-am-selected="{searchBox: 1, btnSize: 'sm',
                                             placeholder:'请选择商品模型', maxHeight: 400}">
                                            <option value=""></option>
                                        <?php if(isset($category)):foreach ($category as $item):?>
                                        <option value="<?=$item['id']?>"><?=$item['name']?></option>
                                        <?php endforeach;endif;?>
                                    </select>
                                </div>
                            </div>
                            <div class="widget-head am-cf">
                                <div class="widget-title a m-cf">商品属性：</div>
                            </div>
                            <div id="attribute">
                                <div class="am-form-group">
                                    <label class="am-u-sm-2 am-u-lg-2 am-form-label">型号：</label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <input type="text" class="tpl-form-input" name="user[user_name]" value="" placeholder="请输入用户名" required>
                                    </div>
                                </div>
                            </div>


                            <div class="widget-head am-cf">
                                <div class="widget-title a m-cf">商品规格：</div>
                            </div>

                            <div id="spec">
                                <div class="am-form-group" style="margin-bottom:5px;">
                                    <label class="am-u-sm-2 am-form-label SKU_TYPE"  propid="1" sku-type-name="茶味选择">茶味选择:</label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <label class="am-checkbox-inline" style="margin:0 15px 0 0;padding:9.6px 0 0 5px;">
                                            <input type="checkbox" checked class="sku_value" style="margin:4px 0 0 5px;" propvalid="11" value="红茶">&nbsp;&nbsp;红茶
                                        </label>
                                        <label class="am-checkbox-inline" style="margin:0 15px 0 0;padding:9.6px 0 0 5px;">
                                            <input type="checkbox" class="sku_value" style="margin:4px 0 0 5px;" propvalid="12" value="绿茶">&nbsp;&nbsp;绿茶
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group"></div>
                            <div id="skuTable">
                                <table class="skuTable am-table am-table-bordered">
                                    <thead>
                                        <tr>
                                            <th>价格</th>
                                            <th>库存</th>
                                            <th>SKU</th>
                                            <th>条形码</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr propvalids="11,21,31,41" propids="" propvalnames="红茶;标准甜;标准冰;R(500ml)" propnames="" class="sku_table_tr">
                                            <td><input type="text"></td>
                                            <td><input type="text"></td>
                                            <td><input type="text"></td>
                                            <td><input type="text"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="am-form-group">
                                <div class="am-u-sm-12 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">保存
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function(){

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

        //规格&& 属性
        $('select[name="model[goods_type]"]').on('change',function () {
            var model_id = $(this).val();
            if(model_id > 0){
                $.post("<?=url('goods.spec/get_spec')?>",{model_id:model_id},function (res) {
                    $('#spec').empty().append(res);
                },'JSON');
                $.post("<?=url('goods.spec/get_attribute')?>",{model_id:model_id},function (res) {
                    $('#attribute').empty().append(res);
                },'JSON')
            }
        });




        // 点击事件
        $(document).on("click",'.sku_value',function(){
            var abc=true;
            var skuTypeArr=[];
            var totalRow=1;
            $(".SKU_TYPE").each(function(){
                var skuTypeObj={};
                skuTypeObj.skuTypeTitle=$(this).attr("sku-type-name");
                var propid=$(this).attr("propid");
                skuTypeObj.skuTypeKey=propid;
                var is_required=$(this).attr("is_required");
                skuValueArr=[];
                var skuValNode=$(this).next();
                var skuValCheckBoxs=$(skuValNode).find("input[type='checkbox'][class*='sku_value']");
                var checkedNodeLen=0;
                $(skuValCheckBoxs).each(function(){
                    if($(this).is(":checked")){
                        var skuValObj={};
                        skuValObj.skuValueTitle=$(this).val();
                        skuValObj.skuValueId=$(this).attr("propvalid");
                        skuValueArr.push(skuValObj);
                        checkedNodeLen++;
                    }
                });
                if(skuValueArr&&skuValueArr.length>0){
                    totalRow=totalRow*skuValueArr.length;
                    skuTypeObj.skuValues=skuValueArr;
                    skuTypeObj.skuValueLen=skuValueArr.length;
                    skuTypeArr.push(skuTypeObj);
                }
            });
            var SKUTableDom="";
            if(abc){
                SKUTableDom+="<table class='skuTable am-table am-table-bordered'><tr>";
                for(var t=0;t<skuTypeArr.length;t++){
                    SKUTableDom+='<th>'+skuTypeArr[t].skuTypeTitle+'</th>';
                }
                SKUTableDom+='<th>价格</th><th>库存</th><th>SKU</th><th>条形码</th>';
                SKUTableDom+="</tr>";
                for(var i=0;i<totalRow;i++){
                    var currRowDoms="";
                    var rowCount=1;
                    var propvalidArr=[];
                    var spec_keyvalue=''
                    var spec_keyname=''
                    var propIdArr=[];
                    var propvalnameArr=[];
                    var propNameArr=[];
                    for(var j=0;j<skuTypeArr.length;j++){
                        var skuValues=skuTypeArr[j].skuValues;
                        var skuValueLen=skuValues.length;
                        rowCount=(rowCount*skuValueLen);
                        var anInterBankNum=(totalRow/rowCount);
                        var point=((i/anInterBankNum)%skuValueLen);
                        propNameArr.push(skuTypeArr[j].skuTypeTitle);
                        if(0==(i%anInterBankNum)){
                            currRowDoms+='<td rowspan='+anInterBankNum+'>'+skuValues[point].skuValueTitle+'</td>';
                            propvalidArr.push(skuValues[point].skuValueId);
                            propvalnameArr.push(skuValues[point].skuValueTitle);
                            spec_keyvalue=propvalidArr.join('_')
                            spec_keyname=propvalnameArr.join('_')
                        }else{
                            propvalidArr.push(skuValues[parseInt(point)].skuValueId);
                            propvalnameArr.push(skuValues[parseInt(point)].skuValueTitle);
                            spec_keyvalue=propvalidArr.join('_')
                            spec_keyname=propvalnameArr.join('_')
                        }
                    }
                    var propvalids=propvalidArr.toString()
                    SKUTableDom+='<tr propvalids=\''+propvalids+'\' propids=\''+propIdArr.toString()+'\' propvalnames=\''+propvalnameArr.join(";")+'\'  propnames=\''+propNameArr.join(";")+'\' class="sku_table_tr">'+currRowDoms+'<td><input type="text" name="model[price][]" class="setting_sku_price" value=""/></td><td><input type="text" name="model[goods_storage][]" class="setting_sku_stock" value=""/></td><td><input type="text" class="setting_sku_stock" name="model[sku][]" value=""/></td><td><input type="text" class="setting_sku_qrcode"  name="model[bar_code][]" value=""/><input type="hidden" class="spec_keyvalue"  name="model[key][]" value="'+spec_keyvalue+'"/><input name="model[key_name][]" type="hidden" class="spec_keyname" value="'+propvalnameArr+'"/></td></tr>';
                }
                SKUTableDom+="</table>";
            }
            $("#skuTable").html(SKUTableDom);
        });
    });
</script>C