<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title a m-cf">商品发布</div>
                </div>
                <div class="widget-body am-fr">
                    <form id="my-form" class="am-form tpl-form-line-form">
                        <div class="widget-body">
                            <div class="am-form-group">
                                <label for="goodsName" class="am-u-sm-2 am-form-label"> 商品名称: </label>
                                <div class="am-u-sm-8 am-u-end">
                                    <input id="goodsName" autocomplete="off" type="text" class="tpl-form-input" name=""
                                        placeholder="饮料" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label for="goodsCode" class="am-u-sm-2 am-form-label"> 商品编码: </label>
                                <div class="am-u-sm-8 am-u-end">
                                    <input id="goodsCode" autocomplete="off" type="text" class="tpl-form-input" name=""
                                        placeholder="请输入商品编码" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label for="reduceStock" class="am-u-sm-2 am-form-label form-require"> 库存扣除方式: </label>
                                <div class="am-u-sm-8 am-u-end">
                                    <div class="x-region-select" data-region-selected>
                                        <select id="reduceStock" class="am-u-sm-6" name="shop[province_id]" required>
                                            <option value="option1">请选择扣除方式</option>
                                            <option value="option2">同步扣除</option>
                                            <option value="option3">分开扣除</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-2 am-form-label form-require"> 商品分类: </label>
                                <div class="x-region-select am-u-sm-8 am-u-end">
                                    <select class="am-u-sm-5" name="shop[province_id]" required>
                                        <option value="">请选择分类</option>
                                    </select>
                                    <select name="shop[city_id]" required>
                                        <option value="">请选择分类</option>
                                    </select>
                                    <select name="shop[region_id]" required>
                                        <option value="">请选择分类</option>
                                    </select>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label for="businessKinds" class="am-u-sm-2 am-form-label"> 业务类型: </label>
                                <div class="am-u-sm-8 am-u-end">
                                    <input id="businessKinds" autocomplete="off" type="text" class="tpl-form-input" name="" placeholder="请输入业务类型" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label for="originalStock" class="am-u-sm-2 am-form-label"> 原始库存: </label>
                                <div class="am-u-sm-8 am-u-end">
                                    <input id="originalStock" autocomplete="off" type="text" class="tpl-form-input" name="" placeholder="请输入原始库存" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label for="mySopPrice" class="am-u-sm-2 am-form-label form-require"> 本店售价: </label>
                                <div class="am-u-sm-8 am-u-end">
                                    <input id="mySopPrice" autocomplete="off" type="text" class="tpl-form-input" name="" placeholder="本店售价" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label for="marketValue" class="am-u-sm-2 am-form-label form-require"> 市场价: </label>
                                <div class="am-u-sm-8 am-u-end">
                                    <input id="marketValue" autocomplete="off" type="text" class="tpl-form-input" name="" placeholder="市场价" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label for="costPrice" class="am-u-sm-2 am-form-label"> 成本价: </label>
                                <div class="am-u-sm-8 am-u-end">
                                    <input id="costPrice" autocomplete="off" type="text" class="tpl-form-input" name="" placeholder="成本" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label for="reduceStock" class="am-u-sm-2 am-form-label"> 商品模型: </label>
                                <div class="am-u-sm-8 am-u-end">
                                    <div class="x-region-select" data-region-selected>
                                        <select id="reduceStock" class="am-u-sm-6" name="" data-province required>
                                            <option value="option1">请选择商品模型</option>
                                            <option value="option2">同步扣除</option>
                                            <option value="option3">分开扣除</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label for="doc-select-1" class="am-u-sm-2 am-form-label">商品规格: </label>
                                <div class="am-u-sm-8 am-u-end" id="doc-select-1">
                                    <div>
                                        <label style="padding-left:5px;" class="am-u-sm-1 am-checkbox-inline am-text-left SKU_TYPE" is_required="1" propid="1" sku-type-name="茶味选择">茶味选择:</label>
                                        <div class="am-u-sm-11">
                                            <label class="am-checkbox-inline">
                                                <input type="checkbox" checked class="sku_value" propvalid="11" value="红茶">&nbsp;&nbsp;红茶
                                            </label>
                                            <label class="am-checkbox-inline">
                                                <input type="checkbox" class="sku_value" propvalid="12" value="绿茶">&nbsp;&nbsp;绿茶
                                            </label>
                                            <label class="am-checkbox-inline">
                                                <input type="checkbox" class="sku_value" propvalid="13" value="乌龙茶">&nbsp;&nbsp;乌龙茶
                                            </label>
                                        </div>
                                    </div>
                                    <div>
                                        <label style="padding-left:5px;" class="am-u-sm-1 am-checkbox-inline am-text-left SKU_TYPE" is_required="1" propid="2" sku-type-name="糖度选择">糖度选择:</label>
                                        <div class="am-u-sm-11">
                                            <label class="am-checkbox-inline">
                                                <input type="checkbox" checked class="sku_value" propvalid="21" value="标准甜">&nbsp;&nbsp;标准甜
                                            </label>
                                            <label class="am-checkbox-inline">
                                                <input type="checkbox" class="sku_value" propvalid="22" value="加甜">&nbsp;&nbsp;加甜
                                            </label>
                                            <label class="am-checkbox-inline">
                                                <input type="checkbox" class="sku_value" propvalid="23" value="少甜">&nbsp;&nbsp;少甜
                                            </label>
                                            <label class="am-checkbox-inline">
                                                <input type="checkbox" class="sku_value" propvalid="24" value="微甜">&nbsp;&nbsp;微甜
                                            </label>
                                            <label class="am-checkbox-inline">
                                                <input type="checkbox" class="sku_value" propvalid="25" value="无糖">&nbsp;&nbsp;无糖
                                            </label>
                                        </div>
                                    </div>
                                    <div>
                                        <label style="padding-left:5px;" class="am-u-sm-1 am-checkbox-inline am-text-left SKU_TYPE" is_required="0" propid="3" sku-type-name="温度选择">温度选择:</label>
                                        <div class="am-u-sm-11">
                                            <label class="am-checkbox-inline">
                                                <input type="checkbox" checked class="sku_value" propvalid="31" value="标准冰">&nbsp;&nbsp;标准冰
                                            </label>
                                            <label class="am-checkbox-inline">
                                                <input type="checkbox" class="sku_value" propvalid="32" value="热">&nbsp;&nbsp;热
                                            </label>
                                            <label class="am-checkbox-inline">
                                                <input type="checkbox" class="sku_value" propvalid="33" value="常温">&nbsp;&nbsp;常温
                                            </label>
                                            <label class="am-checkbox-inline">
                                                <input type="checkbox" class="sku_value" propvalid="34" value="少冰">&nbsp;&nbsp;少冰
                                            </label>
                                            <label class="am-checkbox-inline">
                                                <input type="checkbox" class="sku_value" propvalid="35" value="去冰">&nbsp;&nbsp;去冰
                                            </label>
                                            <label class="am-checkbox-inline">
                                                <input type="checkbox" class="sku_value" propvalid="36" value="多冰">&nbsp;&nbsp;多冰
                                            </label>
                                        </div>
                                    </div>
                                    <div>
                                        <label style="padding-left:5px;" class="am-u-sm-1 am-checkbox-inline am-text-left SKU_TYPE" is_required="1" propid="4" sku-type-name="产品规格">产品规格:</label>
                                        <div class="am-u-sm-11">
                                            <label class="am-checkbox-inline">
                                                <input type="checkbox" checked class="sku_value" propvalid="41" value="R(500ml)">&nbsp;&nbsp;R(500ml)
                                            </label>
                                            <label class="am-checkbox-inline">
                                                <input type="checkbox" class="sku_value" propvalid="42" value="L(700ml)">&nbsp;&nbsp;L(700ml)
                                            </label>
                                            <label class="am-checkbox-inline">
                                                <input type="checkbox" class="sku_value" propvalid="43" value="布丁杯">&nbsp;&nbsp;布丁杯
                                            </label>
                                            <label class="am-checkbox-inline">
                                                <input type="checkbox" class="sku_value" propvalid="44" value="S(400ml)">&nbsp;&nbsp;S(400ml)
                                            </label>
                                             
                                        </div>
                                    </div>
                                    <div>
                                        <label style="padding-left:5px;" class="am-u-sm-1 am-checkbox-inline am-text-left SKU_TYPE" is_required="1" propid="1" sku-type-name="加料选择">加料选择:</label>
                                        <div class="am-u-sm-11">
                                            <label class="am-checkbox-inline">
                                                <input type="checkbox" checked class="sku_value" propvalid="51" value="默认不加料">&nbsp;&nbsp;默认不加料
                                            </label>
                                            <label class="am-checkbox-inline">
                                                <input type="checkbox" checked class="sku_value" propvalid="52" value="红豆">&nbsp;&nbsp;红豆
                                            </label>
                                            <label class="am-checkbox-inline">
                                                <input type="checkbox" class="sku_value" propvalid="53" value="椰果">&nbsp;&nbsp;椰果
                                            </label>
                                            <label class="am-checkbox-inline">
                                                <input type="checkbox" class="sku_value" propvalid="54" value="布丁">&nbsp;&nbsp;布丁
                                            </label>
                                            <label class="am-checkbox-inline">
                                                <input type="checkbox" class="sku_value" propvalid="55" value="晶球">&nbsp;&nbsp;晶球
                                            </label>
                                            <label class="am-checkbox-inline">
                                                <input type="checkbox" class="sku_value" propvalid="56" value="龙珠">&nbsp;&nbsp;龙珠
                                            </label> 
                                        </div>
                                    </div>
                                    <div class="am-form-group"></div>
                                    <div id="skuTable">
                                        <table class="skuTable am-table am-table-bordered">
                                            <thead>
                                                <tr>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                    <th>价格</th>
                                                    <th>库存</th>
                                                    <th>SKU</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr propvalids="11,21,31,41" propids="" propvalnames="红茶;标准甜;标准冰;R(500ml)" propnames="" class="sku_table_tr">
                                                    <td rowspan="2">红茶</td>
                                                    <td rowspan="2">标准甜</td>
                                                    <td rowspan="2">标准冰</td>
                                                    <td>R(500ml)</td>
                                                    <td>默认不加料</td>
                                                    <td>
                                                        <input type="text" class="setting_sku_price" value="1.00">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="setting_sku_stock" value="2">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="setting_sku_sku" value="3">
                                                    </td>
                                                </tr>
                                                <tr propvalids="11,21,32,42" propids="" propvalnames="红茶;标准甜;热;L(700ml)" propnames="" class="sku_table_tr">
                                                    <td rowspan="1">L(700ml)</td>
                                                    <td rowspan="1">红豆</td>
                                                    <td>
                                                        <input type="text" class="setting_sku_price" value="2.00">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="setting_sku_stock" value="3">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="setting_sku_sku" value="4">
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 选择图片 </label>
                                <div class="am-u-sm-8 am-u-end">
                                    <div class="am-form-file">
                                        <div class="am-form-file">
                                            <button type="button"
                                                    class="upload-file am-btn am-btn-secondary am-radius">
                                                <i class="am-icon-cloud-upload"></i> 选择图片
                                            </button>
                                            <div class="uploader-list am-cf">
                                                <!-- 点击按钮，选择图片，图片存放在此处 -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label for="doc-ipt-3" class="am-u-sm-2 am-form-label form-require">文章内容: </label>
                                <div class="am-u-sm-8 am-u-end">
                                    <!-- 加载编辑器的容器 -->
                                    <textarea id="container" name="" type="text/plain"></textarea>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <div class="am-u-sm-12 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">提交
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
    $(function () {
        var url = "<?= url('store.data/delete') ?>";
        $('.item-delete').delete('user_id', url, '删除后不可恢复，确定要删除吗？');

        $('.j-export').click(function () {
            var data = {};
            var formData = $('#form-search').serializeArray();
            $.each(formData, function () {
                this.name !== 's' && (data[this.name] = this.value);
            });
            window.location = "<?= url('store.data/export') ?>" + '&' + $.urlEncode(data);
        });

        $('.upload-file').selectImages({
            name: 'shop[logo_image_id]'
        });

    });
    $(function(){
        // 点击事件
        $(document).on("click",'.sku_value',function(){
            getAlreadySetSkuVals();
            // console.log(alreadySetSkuVals);

            // 定义b为true
            var b=true;

            // 定义下个空数组
            var skuTypeArr=[];

            // 定义一个变量totalRow,值为1
            var totalRow=1;

            // 获取所有class=SKU_TYPE的标签,返回一个jquery数组对象  遍历这个数组对象
            $(".SKU_TYPE").each(function(){
                // 定义一个空对象
                var skuTypeObj={};

                // 对象的名称是skuTypeTitle，值为点击标签的sku-type-name所对应的值
                skuTypeObj.skuTypeTitle=$(this).attr("sku-type-name");
                // 定义一个变量propid，值为点击的标签的哪一类标签的propid
                var propid=$(this).attr("propid");
                // skuTypeObj对象的键名skuTypeKey，键值propid
                skuTypeObj.skuTypeKey=propid;
                // 定义is_required ，值为点击标签所属类别的li标签的is_required
                var is_required=$(this).attr("is_required");
                // 定义一个空数组skuValueArr
                skuValueArr=[];
                // 
                var skuValNode=$(this).next();
                console.log(skuValNode)
                var skuValCheckBoxs=$(skuValNode).find("input[type='checkbox'][class*='sku_value']");
                var checkedNodeLen=0;$(skuValCheckBoxs).each(function(){
                    if($(this).is(":checked")){
                        var skuValObj={};
                        skuValObj.skuValueTitle=$(this).val();
                        skuValObj.skuValueId=$(this).attr("propvalid");
                        skuValObj.skuPropId=$(this).attr("propid");
                        skuValueArr.push(skuValObj);
                        checkedNodeLen++;
                    }
                }
            );
        if(is_required&&"1"==is_required){
            if(checkedNodeLen<=0){
                b=false;
                return false;
            }
        }
        if(skuValueArr&&skuValueArr.length>0){
            totalRow=totalRow*skuValueArr.length;
            skuTypeObj.skuValues=skuValueArr;
            skuTypeObj.skuValueLen=skuValueArr.length;
            skuTypeArr.push(skuTypeObj);
        }
    });
    var SKUTableDom="";
    if(b){
        SKUTableDom+="<table class='skuTable am-table am-table-bordered'><tr>";
        for(var t=0;t<skuTypeArr.length;t++){
            SKUTableDom+='<th>'+skuTypeArr[t].skuTypeTitle+'</th>';
        }
        SKUTableDom+='<th>价格</th><th>库存</th><th>SKU</th>';
        SKUTableDom+="</tr>";
        for(var i=0;i<totalRow;i++){
            var currRowDoms="";
            var rowCount=1;
            var propvalidArr=[];
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
                    propIdArr.push(skuValues[point].skuPropId);
                    propvalnameArr.push(skuValues[point].skuValueTitle);
                }else{
                    propvalidArr.push(skuValues[parseInt(point)].skuValueId);
                    propIdArr.push(skuValues[parseInt(point)].skuPropId);
                    propvalnameArr.push(skuValues[parseInt(point)].skuValueTitle);
                }
            }
            var propvalids=propvalidArr.toString()
            var alreadySetSkuPrice="";
            var alreadySetSkuStock="";
            if(alreadySetSkuVals){
                var currGroupSkuVal=alreadySetSkuVals[propvalids];
                if(currGroupSkuVal){
                    alreadySetSkuPrice=currGroupSkuVal.skuPrice;
                    alreadySetSkuStock=currGroupSkuVal.skuStock
                }
            }
            SKUTableDom+='<tr propvalids=\''+propvalids+'\' propids=\''+propIdArr.toString()+'\' propvalnames=\''+propvalnameArr.join(";")+'\'  propnames=\''+propNameArr.join(";")+'\' class="sku_table_tr">'+currRowDoms+'<td><input type="text" class="setting_sku_price" value="'+alreadySetSkuPrice+'"/></td><td><input type="text" class="setting_sku_stock" value="'+alreadySetSkuStock+'"/></td><td><input type="text" class="setting_sku_stock" value="'+alreadySetSkuStock+'"/></td></tr>';}
            SKUTableDom+="</table>";
        }
        $("#skuTable").html(SKUTableDom);
    });
});
    function getAlreadySetSkuVals(){
        // 定义一个空对象
        alreadySetSkuVals={};
        // 获取面中所有有sku_table_tr类名的tr标签，遍历标签
        $("tr[class*='sku_table_tr']").each(function(){
            // 定义变量skuPrice 是的值是下面表格中价格中的数据
            var skuPrice=$(this).find("input[type='text'][class*='setting_sku_price']").val();
        });
    }
</script>

