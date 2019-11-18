<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑业务分类</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">业务名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="business_category[name]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm'}">
                                        <option value="0">请选择业务</option>
                                        <?php if (isset($list)): foreach ($list as $business): ?>
                                            <option value="<?= $business['name'] ?>" <?php if($model['name'] == $business['id']) echo 'selected';?>>
                                                <?= $business['name'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">商品分类 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="business_category[b_pid_1]" id="b_pid_1" onchange="getctglist(this.id,'b_pid_2')"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm'}">
                                        <option value="0">请选择商品分类</option>
                                        <?php if (isset($category_1)): foreach ($category_1 as $first): ?>
                                            <option value="<?= $first['id'] ?>" <?php if(isset($model['cate_path_id'][2]) && $first['id'] == $model['cate_path_id'][2])echo 'selected';?>>
                                                <?= $first['name'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                    <select name="business_category[b_pid_2]" id="b_pid_2" onchange="getctglist(this.id,'b_pid_3')"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm'}">
                                        <option value="0">请选择商品分类</option>
                                        <?php if (isset($category_2)): foreach ($category_2 as $two): ?>
                                            <option value="<?= $two['id'] ?>" <?php if(isset($model['cate_path_id'][1]) &&$two['id'] == $model['cate_path_id'][1])echo 'selected';?>>
                                                <?= $two['name'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                    <select name="business_category[b_pid_3]" id="b_pid_3"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm'}">
                                        <option value="0">请选择商品分类</option>
                                        <?php if (isset($category_3)): foreach ($category_3 as $three): ?>
                                            <option value="<?= $three['id'] ?>" <?php if($three['id'] == $model['cate_path_id'][0])echo 'selected';?>>
                                                <?= $three['name'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>

                            </div>


                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">分类排序 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" class="tpl-form-input" name="business_category[sort]"
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
{{include file="layouts/_template/tpl_file_item" /}}

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<script>
    $(function () {

        // 选择图片
        $('.upload-file').selectImages({
            name: 'goods_category[image]'
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });

    /**
     * 三级联动
     * @param id
     * @param tag
     */
    function getctglist(id, tag) {
        // var language1_select_classification=$('input[name=language1_select_classification]').val();//请选择分类
        var d = $('#' + id).find('option:selected').val();
        if (parseInt(d) > 0) {
            var url = "<?=url('goods.goods_category/getJsonCate');?>";
            $.post(url,{id:d} ,function (res) {
                var html = '';
                html += ' <option value="0">请选择分类</option>';
                $.each(res.data, function (i, n) {
                    html += '<option  value=' + n.id + ' >' + n.name + '</option>';
                });
                $('#' + tag).html(html);

            }, 'json');

        } else {
            $('#' + tag).html('<option value="0"> 请选择商品分类 </option>');
            $('#b_pid_3').html('<option value="0"> 请选择商品分类 </option>');
        }

    }
</script>
