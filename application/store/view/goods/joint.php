<link rel="stylesheet" href="assets/store/css/goods.css?v=<?= $version ?>">
<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">添加商品组合</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">商品名称：</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="goods[goods_name]"
                                           value="" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label ">商品编号(ZH_)：</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input  maxlength="7" type="text" class="tpl-form-input" name="goods[goods_sn]"
                                           value="" >
                                </div>
                            </div>

                            <div class="am-form-group am-padding-top">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">商品分类：</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="x-region-select">
                                        <select name="goods[province_id]" data-am-selected="{searchBox: 1, btnSize: 'sm',
                                             placeholder:'请选择商品分类', maxHeight: 400}" required>
                                            <option value=""></option>
                                            <?php if(isset($category)):foreach ($category as $item):?>
                                            <option value="<?=$item['id']?>"><?=$item['name']?></option>
                                            <?php endforeach;endif;?>
                                        </select>
                                        <select name="goods[city_id]" data-am-selected="{searchBox: 1, btnSize: 'sm',
                                             placeholder:'请选择商品分类', maxHeight: 400}"   required>
                                            <option value=""></option>
                                        </select>
                                        <select name="goods[cat_id]" data-am-selected="{searchBox: 1, btnSize: 'sm',
                                             placeholder:'请选择商品分类', maxHeight: 400}"  required>
                                            <option value=""></option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="am-form-group yewu_kinds" style="display:none">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label ">业务类型：</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="goods[room_id]" data-am-selected="{searchBox: 1, btnSize: 'sm',
                                             placeholder:'请选择业务分类', maxHeight: 400}"   >
                                        <option value=""></option>

                                    </select>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-2 am-u-lg-2 am-form-label form-require">是否上架 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="goods[is_on_sale]" value="1" data-am-ucheck checked>
                                        <span class="am-link-muted">是</span>
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="goods[is_on_sale]" value="2" data-am-ucheck>
                                        <span class="am-link-muted">否</span>
                                    </label>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-2 am-u-lg-2 am-form-label form-require">是否包邮 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="goods[is_free_shipping]" value="1" data-am-ucheck checked>
                                        <span class="am-link-muted">是</span>
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="goods[is_free_shipping]" value="2" data-am-ucheck>
                                        <span class="am-link-muted">否</span>
                                    </label>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">库存：</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" min="0" class="tpl-form-input" name="goods[goods_storage]"
                                           value="" required>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">本店售价：</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" min="0" class="tpl-form-input" name="goods[shop_price]"
                                           value="" required>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">市场售价：</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" min="0" class="tpl-form-input" name="goods[market_price]"
                                           value="" required>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label ">成本价：</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" min="0" class="tpl-form-input" name="goods[cost_price]"
                                           value="" >
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label ">配送费：</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" min="0" class="tpl-form-input" name="goods[delivery_fee]"
                                           value="" >
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">排序：</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" class="tpl-form-input" name="goods[sort]"
                                           value="100" required>
                                </div>
                            </div>


                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">商品图片：</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <button type="button"
                                                class="upload-file am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择图片
                                        </button>
                                        <div class="uploader-list am-cf">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="am-form-group">
                                 <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">选择商品：</label>
                                 <div class="am-u-sm-9 am-u-end">
                                     <div class="am-form-group" style="margin-bottom:0;">
                                         <div class="widget-become-goods am-form-file">
                                             <button type="button"
                                                     class="j-selectUser  am-btn am-btn-secondary am-radius" style="    font-size: 1.22rem;
    padding: .5rem .9rem;">
                                                 <i class="am-icon-cloud-upload"></i> 选择商品
                                             </button>
                                         </div>
                                         <div class="user-list uploader-list am-cf">
                                         </div>
                                     </div>
                                </div>
                            </div>

                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">商品信息</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">商品简介：</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <textarea name="goods[goods_remark]" id="" cols="30" rows="0"></textarea>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label for="marketValue" class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 配送属性： </label>
                                <div class="am-u-sm-8 am-u-end">
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" checked name="goods[attributes][]"  value="1" class="am-ucheck-checkbox am-field-valid">
                                        <span class="am-ucheck-icons">
                                        <i class="am-icon-unchecked"></i>
                                        <i class="am-icon-checked"></i>
                                        </span>
                                        到店自提
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="goods[attributes][]"  value="2" class="am-ucheck-checkbox am-field-valid">
                                        <span class="am-ucheck-icons">
                                        <i class="am-icon-unchecked"></i>
                                        <i class="am-icon-checked"></i>
                                        </span>
                                        送货上门
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="goods[attributes][]"  value="3" class="am-ucheck-checkbox am-field-valid">
                                        <span class="am-ucheck-icons">
                                        <i class="am-icon-unchecked"></i>
                                        <i class="am-icon-checked"></i>
                                        </span>
                                        邮寄托运
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="goods[attributes][]"  value="4" class="am-ucheck-checkbox am-field-valid">
                                        <span class="am-ucheck-icons">
                                        <i class="am-icon-unchecked"></i>
                                        <i class="am-icon-checked"></i>
                                        </span>
                                        海外代购
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">商品描述：</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <!-- 加载编辑器的容器 -->
                                    <textarea id="container" name="goods[goods_content]"  type="text/plain"></textarea>
                                </div>
                            </div>
                            <div>
<!--                                规格-->
                            <div class="am-modal am-modal-alert" tabindex="-1" id="my-alert">
                                    </div>
                            </div>
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">提交
                                    </button>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- 图片文件列表模板 -->
<script id="tpl-file-item" type="text/template">
    {{ each list }}
    <div class="file-item">
        <a href="{{ $value.file_big_path }}" title="点击查看大图" target="_blank">
            <img src="{{ $value.file_path }}">
        </a>
        <input type="hidden" name="{{ name }}" value="{{ $value.file_name }}">
        <i class="iconfont icon-shanchu file-item-delete"></i>
    </div>
    {{ /each }}
</script>

<!-- 图片文件列表模板 -->
{{include file="layouts/_template/tpl_file_item" /}}

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}
<script src="assets/common/js/vue.min.js"></script>
<script src="assets/common/js/ddsort.js"></script>
<script src="assets/common/plugins/umeditor/umeditor.config.js?v=<?= $version ?>"></script>
<script src="assets/common/plugins/umeditor/umeditor.min.js"></script>
<script src="assets/store/js/goods.spec.js?v=<?= $version ?>"></script>
<script src="assets/common/plugins/laydate/laydate.js"></script>
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script>

    function addItem(obj,item){
        var _html = '';
        $.each(item,function (k,v) {
            _html += "<option value='"+v.id+"'>"+v.name+"</option>";
        })
        obj.append(_html);
        obj.change();
    }


    $(function () {

        $('select[name="goods[province_id]"]').on('change',function () {
            var province_id = $(this).val();
            var city = $('select[name="goods[city_id]"]');
            var region = $('select[name="goods[cat_id]"]');
            var _html = "<option value=''></option>";
            city.html(_html);
            region.html(_html);
            if(province_id > 0){
                $.post("<?=url('store.goods_category/get_category')?>",{parent_id:province_id},function (res) {
                    addItem(city,res.data);
                },'JSON')
            }
        });

        $('select[name="goods[city_id]"]').on('change',function () {
            var city_id = $(this).val();
            var region = $('select[name="goods[cat_id]"]');
            var _html = "<option value=''></option>";
            region.html(_html);
            if(city_id > 0){
                $.post("<?=url('store.goods_category/get_category')?>",{parent_id:city_id},function (res) {
                    addItem(region,res.data);
                },'JSON')
            }
        });

        $('select[name="goods[cat_id]"]').on('change',function () {
            var showNum=$(this).val()
            if(showNum!=0){
                $('.yewu_kinds').show()
            }else{
                $('.yewu_kinds').hide()
            }
            var cat_id = $(this).val();
            var goods_type = $('select[name="goods[room_id]"]');
            var _html = "<option value=''></option>";
            if(cat_id > 0){
                $.post("<?=url('store.business/get_room_name')?>",{category_id:cat_id},function (res) {
                    $.each(res.data, function (i, item) {
                        if(item.business){
                            _html += "<option value='"+ item.business.id +"'>" + item.business.name + "</option>";
                        }
                    });
                    goods_type.html(_html);
                },'JSON')
            }
        });


        // 富文本编辑器
        UM.getEditor('container', {
            initialFrameWidth: 400 + 15,
            initialFrameHeight: 400
        });

        // 选择图片
        $('.upload-file').selectImages({
            name: 'goods[original_img]'
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

        $.activity = {};
        $.extend($.activity,{
            aIndex:0,//设置的index值
            chkArry:[],
        });

        function ergodic(arr){
            let tr=$('<tr></tr>')
            var th0=$('<th></th>').text('商品ID').css({'width':'100px','text-align':'center'})
            var th1=$('<th></th>').text('图片').css({'width':'100px','text-align':'center'})
            var th2=$('<th></th>').text('名称').css({'width':'100px','text-align':'center'})
            var th3=$('<th></th>').text('规格').css({'width':'100px','text-align':'center'})
            var th4=$('<th></th>').text('数量').css({'width':'100px','text-align':'center'})
            var th5=$('<th></th>').text('操作').css({'width':'100px','text-align':'center'})
            tr.append([th0,th1,th2,th3,th4,th5])
            return tr
        }

        // 选择商品
        var select_goods=[];
        $('.j-selectUser').click(function () {
            var goods_ids = [];
            $("input[name='activity[goods_id][]']").each(function(){
                goods_ids.push($(this).val());
            });
            $.selectData({
                title: '选择商品',
                uri: "<?=url('data.store_goods/lists')?>",
                dataIndex: 'goods_id',
                done: function (data) {
                    var selData=data
                    $.each(selData,function(k,v){
                        v.specName='';
                        v.specNames=[];
                        v.specId='';
                        v.specIds=[];
                    })
                    if(select_goods==''){
                        $.each(selData,function(k,v){
                            select_goods.push(v);
                        })
                        showData(select_goods)
                    }else{
                        let length1 = select_goods.length;
                        let length2 = data.length;
                        for (let i = 0; i < length1; i++) {
                            for (let j = 0; j < length2; j++) 
                            {
                                //判断添加的数组是否为空了
                                if (select_goods.length > 0) {
                                    if (select_goods[i]["id"] === data[j]["id"]) {
                                        select_goods.splice(i, 1); //利用splice函数删除元素，从第i个位置，截取长度为1的元素
                                        length1--;
                                    }
                                }
                            }
                        }
                        for (let n = 0; n < data.length; n++) {
                            select_goods.push(data[n]);
                        }
                        showData(select_goods)
                    }
                }
            });
        });

        function showData(arr){
            var user = [];
            $('.user-list').empty()
            var table=$('<table></table>').css('margin-top','20px')
            let thead=$('<thead></thead>')
            var tbody=$('<tbody></tbody>')
            $.each(arr,function (k,v) {
                user.push(v);;
                var input_id=$("<input readonly>").val(v.id).css({'width':'91px','height':'31px','text-align':'center','border':'none','outline':'none','background-color':'#eee'});
                var td0=$("<td></td>").append(input_id).css({'width':'100px','text-align':'center'});
                var td1='';
                var td2=$("<td></td>").html(v.goods_name).css({'width':'150px','text-align':'center'});
                var td3=''
                if(v.has_spec){
                    var inputBox=$('<input type="text" placeholder="请选择商品规格" required>').val(v.specName)
                    var guige_btn=$('<button type="button" data-id="'+v.id+'" class="shezhi am-btn am-btn-secondary am-btn-xs" data-am-modal="{target: \'#my-alert\'}"></button>').text('选择规格').attr({'set_id':v.id,'idn':k})
                    td1=$("<td></td>").html('<img style="padding: 20px" src="'+v.original_img+'"><input type="hidden" name="goods[joint]['+k+'][store_goods_ids]" value="'+v.id+'"'+'> <input type="hidden" name="goods[joint]['+k+'][key]" value="'+v.specId+'" form-require> <input type="hidden" name="goods[joint]['+k+'][key_name]" value="'+v.specName+'">')
                    td3=$("<td></td>").append([inputBox,guige_btn]).css({'width':'250px','text-align':'center'});
                }else{
                    var spanBox=$('<div>无规格</div>').css({'text-align':'center','font-size':'14px'});
                    td1=$("<td></td>").html('<img style="padding: 20px" src="'+v.original_img+'"> <input type="hidden" name="goods[joint]['+k+'][store_goods_ids]" value="'+v.id+'"'+'> <input type="hidden" name="goods[joint]['+k+'][key]" value="'+v.specId+'"> <input type="hidden" name="goods[joint]['+k+'][key_name]" value="'+v.specName+'">')
                    td3=$("<td></td>").append(spanBox).css({'width':'250px','text-align':'center'});
                }
                
                var salePrice=$("<input name='activity[goods_price]['+v.id+'][]'/>").val(v.goods_price).attr('type','text').css({'border':'none','outline':'none','background-color':'#eee','text-align':'center'})
                var stock=$('<input name="goods[joint]['+k+'][num]" />').attr('type','text').css({'border':'none','outline':'none','background-color':'#eee'}).val(1)
                var td4=$("<td></td>").html(stock).css('padding','0 20px')
                var delBtn=$("<button type='button' class='delBtn am-btn am-btn-danger am-btn-xs'>删除</button>").attr({'del_id':v.id,'idn':k})
                var td5=$("<td></td>").html(delBtn).css({'padding':'0 20px','text-align':'center'})
                tbody.append($("<tr></tr>").attr('data-goods',user[k].id).append([td0,td1,td2,td3,td4,td5]).css('border-top','1px solid #ccc'))
            });
            $('.user-list').append(table.append([thead.append(ergodic()),tbody])).get(0)
        }

        // 删除按钮
        $(document).on('click','.delBtn',function(){
            if($(this).parent().parent().parent().children().length==1){
                $('.user-list').empty()
                select_goods=[];
            }else{
                var idn=$(this).attr('idn');
                var inx=0;
                $.each(select_goods,function(id,row){
                    if(id==idn){
                        inx=id
                    }
                })
                select_goods.splice(inx,1)
                showData(select_goods)
            }
        })

        // 获取设置按钮的set_id属性，并保存到全局$中
        $(document).off('click.shezhi').on('click.shezhi','.shezhi',function(){
            // console.log($(this).parent().parent().find('input[name="goods[joint]['+dataIndex+'][key]"]').val())
			var dataIndex = $(this).attr('idn');
			var store_goods_id = $(this).data('id');
			$.activity.aIndex = dataIndex;
            var index = layer.load();
			$.post("<?=url('goods/ajax_get_specs')?>",{store_goods_id:store_goods_id},function (res) {
                layer.close(index);
                $("#my-alert").empty().append(res);
            })
        })
		
        // 点击确定后，选中的属性回填到对应的设置按钮的td中并把设置按钮隐藏
        $(document).off('click.am-modal-btn').on('click.am-modal-btn',' .am-modal-btn',function(){
            $.extend($.activity,{
                specName:'',
                specNames:[],
                specId:'',
                specIds:[]
            });
			var inputs = $('.specChk input[type="radio"]');
			$.activity.chkArry=[];
			$('.sku_value:checked').each(function(i,v){
				var chkVal = $(this).attr('data-specname');
				var chkId = $(this).val();
				var chkData = {};
                $.activity.specName=$.activity.specName+'_'+chkVal
                $.activity.specNames.push(chkVal)
                $.activity.specId=$.activity.specId+'_'+chkId
                $.activity.specIds.push(chkId)
				chkData.chkVal = chkVal;
				chkData.chkId = chkId;
				$.activity.chkArry.push(chkData);
			})
            $.activity.specName=$.activity.specName.substr(1,$.activity.specName.length)
            $.activity.specId=$.activity.specId.substr(1,$.activity.specId.length)
			$('.shezhi').each(function(j,k){
				var dataIndex = $(this).attr('idn');
				if($.activity.aIndex == dataIndex){
                    select_goods[dataIndex].specName=$.activity.specName
                    select_goods[dataIndex].specNames=$.activity.specNames
                    select_goods[dataIndex].specId=$.activity.specId
                    select_goods[dataIndex].specIds=$.activity.specIds
				}
                showData(select_goods)
			})
        })
    });
</script>
