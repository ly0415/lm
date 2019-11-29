<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">添加来源</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">来源名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="source_list[name]"
                                           value="<?= $model['name'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">来源图片 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <button type="button"
                                                class="upload-file am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择图片
                                        </button>
                                        <div class="uploader-list am-cf">
                                            <?php if ($model['img']): ?>

                                                <div class="file-item">
<!--                                                    <a href="--><?//=$model['img']['big_file_path']?><!--" title="点击查看大图" target="_blank">-->
                                                        <img src="/web/uploads/small/<?=$model['img']?>">
<!--                                                    </a>-->
                                                    <input type="hidden" name="source_list[img]" value="<?=$model['img']?>">
                                                    <i class="iconfont icon-shanchu file-item-delete"></i>
                                                </div>

                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">来源排序 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" class="tpl-form-input" name="source_list[sort]"
                                           value="<?= $model['sort'] ?>" required>
                                    <!--                                    <small>数字越小越靠前</small>-->
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
