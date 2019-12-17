<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">添加商品规格</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">规格名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="goods_spec[name]"
                                           value="" required>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">商品模型 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select id="js-selected"  name="goods_spec[type_id]" required
                                            data-am-selected="{searchBox: 1, btnSize: 'sm',
                                             placeholder:'请选择商品模型', maxHeight: 400}">
                                        <option value=""></option>
                                        <?php if (isset($category)): foreach ($category as $first): ?>
                                            <option  value="<?= $first['id'] ?>" code="<?= $first['type'] ?>"><?= $first['name'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                    <small class="am-margin-left-xs">
                                        <a href="<?= url('store.goods_model/add') ?>">去添加</a>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">规格项 </label>
                                <div class="am-u-sm-10 specBox">
                                    <div class="am-form-group">
                                        <div class="am-u-sm-3"><input type="text" name="goods_spec[spec_item][item_specs][]"></div>
                                        <div class="am-u-sm-3 hideshow">
                                            <input type="text" placeholder="咖啡机标识" name="goods_spec[spec_item][values][]">
                                        </div>
                                        <div class="am-u-sm-3 am-u-end">
                                            <button type="button" class="add am-btn am-btn-default am-btn-xs am-btn-secondary">+</button>
                                            <button type="button" class="del am-btn am-btn-default am-btn-xs am-btn-secondary">-</button>
                                        </div>
                                    </div>
                                    <small>注：一行为一个规格项</small>
                                </div>
                            </div>


                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">排序 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="goods_spec[sort]"
                                           value="" required>
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
{{include file="layouts/_template/tpl_file_item" /}}

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<script>
    $(function () {
        $('.hideshow').hide()

        var is_coffe_model = '';
        $('#js-selected').on('change', function() {
            is_coffe_model = $(this).find("option:selected").attr("code");
            $('.specBox').empty()
            $('.specBox').html(`<div class="am-form-group">
                        <div class="am-u-sm-3"><input placeholder="规格项"name="goods_spec[spec_item][item_specs][]" required type="text"></div>
                        <div class="am-u-sm-3 hideshow"><input name="goods_spec[spec_item][values][]" placeholder="咖啡机标识" required type="text"></div>
                        <div class="am-u-sm-3 am-u-end"> 
                            <button type="button" class="add am-btn am-btn-default am-btn-xs am-btn-secondary">+</button>
                            <button type="button" class="del am-btn am-btn-default am-btn-xs am-btn-secondary">-</button>
                        </div>
                    </div>`)
            if(is_coffe_model==2){
                $('.hideshow').show()
            }else{
                $('.hideshow').hide()
            }
        });

        $('body').on('click','.add',function(){
            html = '';
            html += `<div class="am-form-group">
                        <div class="am-u-sm-3"><input name="goods_spec[spec_item][item_specs][]" required type="text"></div>
                        <div class="am-u-sm-3 hideshow"><input name="goods_spec[spec_item][values][]" required placeholder="咖啡机标识" type="text"></div>
                        <div class="am-u-sm-3 am-u-end"> 
                            <button type="button" class="add am-btn am-btn-default am-btn-xs am-btn-secondary">+</button>
                            <button type="button" class="del am-btn am-btn-default am-btn-xs am-btn-secondary">-</button>
                        </div>
                    </div>`;
            $(this).parent().parent().after(html);
            if(is_coffe_model==2){
                $('.hideshow').show()
            }else{
                $('.hideshow').hide()
            }
        });

        $('body').on('click','.del',function(){
            $(this).parent().parent().remove();
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
