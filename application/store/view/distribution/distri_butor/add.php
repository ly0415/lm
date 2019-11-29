<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">添加分销规则</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-3 am-form-label form-require">所属店铺 </label>
                                <div class="am-u-sm-8 am-u-end">
                                    <select name="disbut[store_ids][]" multiple data-am-selected="{btnSize: 'sm',btnWidth:400,}" required>
                                        <?php if (isset($stores)): foreach ($stores as $role): ?>
                                            <option value="<?= $role['id'] ?>"> <?= $role['store_name'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                    <div class="help-block">
                                        <small>注：支持多选</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-3 am-form-label form-require">规则名称 </label>
                                <div class="am-u-sm-8 am-u-end">
                                    <input type="text" class="tpl-form-input" name="disbut[rule_name]"
                                           value="" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-3 am-form-label form-require">一级分润规则 </label>
                                <div class="am-u-sm-8 am-u-end">
                                    <input type="text" class="bonus_rules tpl-form-input" name="disbut[lev1_prop]"
                                           value="" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-3 am-form-label form-require">二级分润规则 </label>
                                <div class="am-u-sm-8 am-u-end">
                                    <input type="text" class="bonus_rules tpl-form-input" name="disbut[lev2_prop]"
                                           value="" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-3 am-form-label form-require">三级分润规则 </label>
                                <div class="am-u-sm-8 am-u-end">
                                    <input type="text" class="bonus_rules tpl-form-input" name="disbut[lev3_prop]"
                                           value="" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">提交
                                    </button>
                                </div>
                            </div>
<!--                        </fieldset>-->
<!--                    </div>-->
<!--                </form>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
<!--</div>-->

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

<script>
    $(function () {
        $(document).on('keyup','.bonus_rules',function(){
            var reg = new RegExp("^(\\d|[1-9]\\d|100)$");
            var num = $(this).val();
            if(num==""){
                layer.msg("请填写0-100的正整数");
                $(this).val('')
                return false;
            }else{
                if(isNaN(num)){
                    alert('请填写0-100的正整数');
                    $(this).val('')
                }else{
                    var r = /^([1]?\d{1,2})$/;　　//0-100的正整数  		 
                    if(!reg.test(num)){
                        layer.msg('请填写0-100的正整数');
                        $(this).val('')
                        return false;
                    } 
                }
                return true;
            }
        })

        // 选择图片
        $('.upload-file').selectImages({
            name: 'source_list[img]'
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
