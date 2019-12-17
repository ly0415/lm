<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">设置文章领劵</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">按钮名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input"  placeholder="按钮名称" name="coupon[name]"
                                           value="<?=$list['relation_1']['name']?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">抵扣卷 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="coupon[coupon_id]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm'}">
                                        <option value="0">选择抵扣卷</option>
                                        <?php if (isset($coupon)): foreach ($coupon as $first): ?>
                                            <option value="<?= $first['id'] ?>" <?=$list['relation_1']['coupon_id'] == $first['id'] ? 'selected':''?>>
                                                <?= $first['coupon_name'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">按钮颜色 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="color" class="tpl-form-input" placeholder="按钮颜色" name="coupon[color]"
                                           value="<?=$list['relation_1']['color']?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">是否开启 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="coupon[status]" <?=$list['status'] == 1 ? 'checked' : ''?> value="1" data-am-ucheck checked>
                                        是
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="coupon[status]" <?=$list['status'] == 2 ? 'checked' : ''?> value="2" data-am-ucheck>
                                        <span class="am-link-muted">否</span>
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">背景图片 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <button type="button"
                                                class="upload-file am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择图片
                                        </button>
                                        <div class="uploader-list am-cf">
                                            <?php if($list && isset($list['image'])):?>

                                          <div class="file-item">
                                                    <a href="<?=$list['image']['big_file_path']?>" title="点击查看大图" target="_blank">
                                                        <img src="<?=$list['image']['file_path']?>">
                                                    </a>
                                                    <input type="hidden" name="coupon[relation_2]" value="<?=$list['relation_2']?>">
                                                    <i class="iconfont icon-shanchu file-item-delete"></i>
                                                </div>
                                        <?php endif;?></div>
                                    </div>
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
            name: 'coupon[relation_2]'
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
