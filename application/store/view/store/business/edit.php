<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑业务类型</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">业务类型 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="business[name]"
                                           value="<?=$model['name']?>" required>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">上级分类 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="business[pid]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm'}">
                                        <option value="0">顶级分类</option>
                                        <?php if (isset($business)): foreach ($business as $b): ?>
                                            <option value="<?= $b['id'] ?>"  <?php if($model['pid'] == $b['id'])echo 'selected';?> <?php if($model['id'] == $b['id'])echo 'disabled';?>>
                                                <?= $b['name'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">业务图片 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <button type="button"
                                                class="upload-file am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择图片
                                        </button>
                                        <div class="uploader-list am-cf">
                                            <?php if ($model['image']): ?>
                                                <div class="file-item">
                                                    <a href="uploads/big/<?= $model['image'] ?>" title="点击查看大图" target="_blank">
                                                        <img src="uploads/small/<?= $model['image'] ?>">
                                                    </a>
                                                    <input type="hidden" name="business[image]"
                                                           value="<?= $model['image'] ?>">
                                                    <i class="iconfont icon-shanchu file-item-delete"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">分类排序 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" class="tpl-form-input" name="business[sort]"
                                           value="<?= $model['sort'] ?>" required>
                                    <small>数字越小越靠前</small>
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

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<script>

    /**
     * 三级联动
     * @param id
     * @param tag
     */
    function getCateList(id, tag) {
        var d = $('#' + id).find('option:selected').val();
        if (parseInt(d) > 0) {
            var url = "<?=url('store.goods_category/get_category');?>";
            $.post(url,{parent_id:d} ,function (res) {
                var html = '';
                html += ' <option value="0">请选择商品分类</option>';
                $.each(res.data, function (i, n) {
                    html += '<option  value=' + n.id + ' >' + n.name + '</option>';
                });
                $('#' + tag).empty().html(html);

            }, 'json');

        } else {
            $('#' + tag).empty().html('<option value="0">请选择商品分类</option>');
            $('#b_pid_3').empty().html('<option value="0">请选择商品分类</option>');
        }
    }
    $(function () {

        $('#pid').change(function () {
            var pid = $(this).find('option:selected').val();
            if(parseInt(pid) > 0){
                $("#category").show();
            }else{
                $("#category").hide();
            }
        });
        // 选择图片
        $('.upload-file').selectImages({
            name: 'business[image]'
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
