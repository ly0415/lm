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
                            <input type="hidden" name="business[pid]" value="<?= $model['pid'] ?>">
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">业务类型 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="business[name]"
                                           value="<?= $model['name'] ?>" required>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">上级分类 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="goods_category[parent_id]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm'}">
                                        <option value="0">顶级分类</option>
                                        <?php if (isset($list)): foreach ($list as $first): ?>
                                            <option value="<?= $first['id'] ?>"  <?php if($model['pid'] == $first['id'])echo 'selected';?> <?php if($model['id'] == $first['id'])echo 'disabled';?>>
                                                <?= $first['name'] ?></option>
                                            <?php if (isset($first['child'])): foreach ($first['child'] as $first1): ?>
                                                <option value="<?= $first1['id'] ?>" <?php if($model['pid'] == $first1['id'])echo 'selected';?> <?php if($model['id'] == $first1['id'])echo 'disabled';?>>
                                                    &nbsp&nbsp&nbsp&nbsp--<?= $first1['name'] ?></option>

                                            <?php endforeach; endif; ?>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                            </div>


                            <div class="am-form-group" id="category" style="display: <?=$model['pid'] > 0 ? 'block' : 'none'?>" >
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">所属分类 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="business[b_pid_1]" id="b_pid_1" onchange="getCateList(this.id,'b_pid_2')"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm'}">
                                        <option value="0">请选择商品分类</option>

                                        <?php if(isset($category)):foreach ($category as $first):?>
                                            <option value="<?=$first['id']?>" <?php if(isset($_category['first']) && $first['id'] == $_category['first']['id']){echo 'selected';}?>><?=$first['name']?></option>

                                        <?php endforeach;?>
                                        <?php endif;?>


                                    </select>
                                    <select name="business[b_pid_2]" id="b_pid_2" onchange="getCateList(this.id,'b_pid_3')"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm'}">
                                        <option value="0">请选择商品分类</option>
                                        <?php if(isset($_category['category']) && isset($_category['category']['child'])):foreach ($_category['category']['child'] as $two):?>
                                            <option value="<?=$two['id']?>" <?php if(isset($_category['two']) && $two['id'] == $_category['two']['id']){echo 'selected';}?>><?=$two['name']?></option>

                                        <?php endforeach;?>
                                        <?php endif;?>

                                    </select>
                                    <select name="business[b_pid_3]" id="b_pid_3"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm'}">
                                        <option value="0">请选择商品分类</option>
                                        <?php if(isset($_category['category']) && isset($_category['category']['child'])):foreach ($_category['category']['child'] as $two):if(isset($two['child'])):foreach ($two['child'] as $three):?>
                                            <option value="<?=$three['id']?>" <?php if(isset($_category['three']) && $three['id'] == $_category['three']['id']){echo 'selected';}?>><?=$three['name']?></option>
                                            <?php endforeach;?>
                                        <?php endif;?>
                                        <?php endforeach;?>
                                        <?php endif;?>
                                    </select>
                                </div>
                            </div>
<!--                            <div class="am-form-group">-->
<!--                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">分类图片 </label>-->
<!--                                <div class="am-u-sm-9 am-u-end">-->
<!--                                    <div class="am-form-file">-->
<!--                                        <button type="button"-->
<!--                                                class="upload-file am-btn am-btn-secondary am-radius">-->
<!--                                            <i class="am-icon-cloud-upload"></i> 选择图片-->
<!--                                        </button>-->
<!--                                        <div class="uploader-list am-cf">-->
<!--                                            --><?php //if ($model['image']): ?>
<!--                                                <div class="file-item">-->
<!--                                                    <img src="--><?//= $model['image']?><!--">-->
<!--                                                    <input type="hidden" name="goods_category[image]"-->
<!--                                                           value="--><?//= $model['image'] ?><!--">-->
<!--                                                    <i class="iconfont icon-shanchu file-item-delete"></i>-->
<!--                                                </div>-->
<!--                                            --><?php //endif; ?>
<!--                                        </div>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                            </div>-->
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
{{include file="layouts/_template/tpl_file_item" /}}

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<script>

    /**
     * 三级联动
     * @param id
     * @param tag
     */
    function getCateList(id, tag) {
        // var language1_select_classification=$('input[name=language1_select_classification]').val();//请选择分类
        var d = $('#' + id).find('option:selected').val();
        if (parseInt(d) > 0) {
            var url = "<?=url('store.goods_category/get_json_cate');?>";
            $.post(url,{id:d} ,function (res) {
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
