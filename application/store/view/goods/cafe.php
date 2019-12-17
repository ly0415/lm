<link rel="stylesheet" href="assets/store/css/goods.css?v=<?= $version ?>">
<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">信息简介</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-3 am-form-label form-require">商品名称：</label>
                                <div class="am-u-sm-8 am-u-end">
                                    <input autocomplete="off" type="text" class="tpl-form-input" name="cafe[goods_name]" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-3 am-form-label">商品简介：</label>
                                <div class="am-u-sm-8 am-u-end">
                                    <textarea name="cafe[goods_remark]" id="" cols="30" rows=""></textarea>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-3 am-form-label">商品关键字：</label>
                                <div class="am-u-sm-8 am-u-end">
                                    <input autocomplete="off" type="text" class="tpl-form-input" name="cafe[keywords]" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-3 am-form-label form-require">商品描述：</label>
                                <div class="am-u-sm-8 am-u-end">
                                    <textarea id="container" name="cafe[goods_content]" type="text/plain"></textarea>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-3 am-form-label">商品相册：</label>
                                <div class="am-u-sm-8 am-u-end">
                                    <div class="am-form-file">
                                        <div class="am-form-file">
                                            <button type="button"
                                                    class="background_img upload-file am-btn am-btn-secondary am-radius">
                                                <i class="am-icon-cloud-upload"></i> 选择图片
                                            </button>
                                            <div class="uploader-list am-cf">
                                            </div>
                                        </div>
                                    </div>
                                    <small>注：图片大小不能超过2M 尺寸540*540</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-3 am-form-label">商品编码：</label>
                                <div class="am-u-sm-8 am-u-end">
                                    <input type="text" class="tpl-form-input" name="cafe[goods_sn]" value="">
                                    <small>注：如果不填会自动生成</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-3 am-form-label">SPU：</label>
                                <div class="am-u-sm-8 am-u-end">
                                    <input type="password" class="tpl-form-input" name="cafe[spu]" value="" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-3 am-form-label">SKU：</label>
                                <div class="am-u-sm-8 am-u-end">
                                    <input type="password" class="tpl-form-input" name="cafe[sku]" value="" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-3 am-form-label form-require">库存扣除方式：</label>
                                <div class="am-u-sm-8 am-u-end">
                                    <div class="x-region-select" data-region-selected>
                                        <select required name="cafe[deduction]" data-am-selected="{searchBox: 1, btnSize: 'sm',
                                             placeholder:'请选择扣除方式', maxHeight: 400}"   >
                                            <option value=""></option>
                                            <option value="1">同步扣除</option>
                                            <option value="2">分开扣除</option>
                                        </select>

                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-3 am-form-label form-require">商品分类：</label>
                                <div class="x-region-select am-u-sm-8 am-u-end">
                                    <select name="cafe[province_id]" data-am-selected="{searchBox: 1, btnSize: 'sm',
                                             placeholder:'请选择商品分类', maxHeight: 400}" required>
                                        <option value=""></option>
                                        <?php if(isset($category)):foreach ($category as $item):?>
                                            <option value="<?=$item['id']?>"><?=$item['name']?></option>
                                        <?php endforeach;endif;?>
                                    </select>
                                    <select name="cafe[city_id]" data-am-selected="{searchBox: 1, btnSize: 'sm',
                                             placeholder:'请选择商品分类', maxHeight: 400}"   required>
                                        <option value=""></option>
                                    </select>
                                    <select name="cafe[cat_id]" data-am-selected="{searchBox: 1, btnSize: 'sm',
                                             placeholder:'请选择商品分类', maxHeight: 400}"  required>
                                        <option value=""></option>
                                    </select>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-3 am-form-label ">业务类型：</label>
                                <div class="am-u-sm-8 am-u-end">
                                    <select name="cafe[room_id]" data-am-selected="{searchBox: 1, btnSize: 'sm',
                                             placeholder:'请选择业务分类', maxHeight: 400}"   >
                                        <option value=""></option>

                                    </select>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-3 am-form-label">辅助分类：</label>
                                <div class="am-u-sm-8 am-u-end">
                                    <button type="button" class="am-btn am-btn-secondary am-radius am-btn-xs">分类添加</button>
                                    <button type="button" class="am-btn am-btn-secondary am-radius am-btn-xs">收起</button>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-3 am-form-label form-require">区域限制：</label>
                                <div class="am-u-sm-8 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="cafe[is_limit]" value="1" data-am-ucheck checked>
                                        <span class="am-link-muted">不限制</span>
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="cafe[is_limit]" value="2" data-am-ucheck>
                                        <span class="am-link-muted">限制</span>
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-3 am-form-label">商品品牌：</label>
                                <div class="am-u-sm-8 am-u-end">
                                    <div class="x-region-select" data-region-selected>
                                        <select name="cafe[brand_id]" data-am-selected="{searchBox: 1, btnSize: 'sm',
                                             placeholder:'请选择品牌', maxHeight: 400}"   >
                                            <option value=""></option>
                                            <?php if(isset($brand)):foreach ($brand as $b):?>
                                            <option value="<?=$b['id']?>"><?=$b['name']?></option>
                                            <?php endforeach;endif;?>

                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-3 am-form-label">商品风格：</label>
                                <div class="am-u-sm-8 am-u-end">
                                    <div class="x-region-select" data-region-selected>
                                        <select name="cafe[style_id]" data-am-selected="{searchBox: 1, btnSize: 'sm',
                                             placeholder:'请选择品牌', maxHeight: 400}"   >
                                            <option value=""></option>
                                            <?php if(isset($style)):foreach ($style as $s):?>
                                                <option value="<?=$s['id']?>"><?=$s['style_name']?></option>
                                            <?php endforeach;endif;?>

                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-3 am-form-label">原始库存：</label>
                                <div class="am-u-sm-8 am-u-end">
                                    <input id="originalStock" autocomplete="off" type="number" class="tpl-form-input" name="cafe[goods_storage]" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-3 am-form-label form-require">本店售价：</label>
                                <div class="am-u-sm-8 am-u-end">
                                    <input id="mySopPrice" autocomplete="off" type="number" class="tpl-form-input" name="cafe[shop_price]" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-3 am-form-label form-require">市场价：</label>
                                <div class="am-u-sm-8 am-u-end">
                                    <input autocomplete="off" type="text" class="tpl-form-input" name="cafe[market_price]" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-3 am-form-label">成本价：</label>
                                <div class="am-u-sm-8 am-u-end">
                                    <input autocomplete="off" type="number" class="tpl-form-input" name="cafe[cost_price]" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-3 am-form-label form-require">封面图片：</label>
                                <div class="am-u-sm-8 am-u-end">
                                    <div class="am-form-file">
                                        <div class="am-form-file">
                                            <button type="button"
                                                    class="logo upload-file am-btn am-btn-secondary am-radius">
                                                <i class="am-icon-cloud-upload"></i> 选择图片
                                            </button>
                                            <div class="uploader-list am-cf">
                                            </div>
                                        </div>
                                    </div>
                                    <small>注：图片大小不能超过2M 尺寸540*540</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-3 am-form-label form-require"> 配送属性： </label>
                                <div class="am-u-sm-8 am-u-end">
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="cafe[attributes][]" checked value="1" class="am-ucheck-checkbox am-field-valid"><span class="am-ucheck-icons"><i class="am-icon-unchecked"></i><i class="am-icon-checked"></i></span>门店自取
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="cafe[attributes][]" checked value="2" class="am-ucheck-checkbox am-field-valid"><span class="am-ucheck-icons"><i class="am-icon-unchecked"></i><i class="am-icon-checked"></i></span>门店直配
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="cafe[attributes][]" checked value="3" class="am-ucheck-checkbox am-field-valid"><span class="am-ucheck-icons"><i class="am-icon-unchecked"></i><i class="am-icon-checked"></i></span>总仓直邮
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="cafe[attributes][]" checked value="4" class="am-ucheck-checkbox am-field-valid"><span class="am-ucheck-icons"><i class="am-icon-unchecked"></i><i class="am-icon-checked"></i></span>海外保税购
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-3 am-form-label">配送费用：</label>
                                <div class="am-u-sm-8 am-u-end">
                                    <input autocomplete="off" type="number" class="tpl-form-input" name="cafe[delivery_fee]" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-3 am-form-label">是否包邮：</label>
                                <div class="am-u-sm-8 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="cafe[is_free_shipping]" value="1" data-am-ucheck checked>是
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="cafe[is_free_shipping]" value="2" data-am-ucheck>否
                                    </label>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">提交</button>
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

    $(function(){


        $('select[name="cafe[province_id]"]').on('change',function () {
            var province_id = $(this).val();
            var city = $('select[name="cafe[city_id]"]');
            var region = $('select[name="cafe[cat_id]"]');
            var _html = "<option value=''></option>";
            city.html(_html);
            region.html(_html);
            if(province_id > 0){
                $.post("<?=url('store.goods_category/get_category')?>",{parent_id:province_id},function (res) {
                    addItem(city,res.data);
                },'JSON')
            }
        });

        $('select[name="cafe[city_id]"]').on('change',function () {
            var city_id = $(this).val();
            var region = $('select[name="cafe[cat_id]"]');
            var _html = "<option value=''></option>";
            region.html(_html);
            if(city_id > 0){
                $.post("<?=url('store.goods_category/get_category')?>",{parent_id:city_id},function (res) {
                    addItem(region,res.data);
                },'JSON')
            }
        });

        $('select[name="cafe[cat_id]"]').on('change',function () {
            var showNum=$(this).val()
            if(showNum!=0){
                $('.yewu_kinds').show()
            }else{
                $('.yewu_kinds').hide()
            }
            var cat_id = $(this).val();
            var goods_type = $('select[name="cafe[room_id]"]');
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

        $('.logo').selectImages({
            name: 'cafe[original_img][]'
        });

        // 商品相册
        $('.background_img').selectImages({
            name: 'cafe[images][]'
            , multiple: true
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();
    })
</script>
