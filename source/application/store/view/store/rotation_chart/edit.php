<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑轮播图片
                                    <?php if($roionList['type'] == 1): ?>
                                        <span class="">-->首页 banner 轮播图</span>
                                    <?php elseif(($roionList['type'] == 2)): ?>
                                        <span class="">-->活动页面 banner 轮播图</span>
                                    <?php elseif(($roionList['type'] == 3)): ?>
                                        <span class="">-->秒杀页面 banner 轮播图</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- <div class="am-form-group am-margin-left-lg">
                                <div>
                                    <div>
                                        <small style="width:40px;">注：</small>
                                        <small>1、小程序活动页面路径为 pages/exercise/exercise</small>
                                    </div>
                                    <small style="margin-left:29px;">2、小程序秒杀页面路径为 pages/miaosha/miaosha</small>
                                </div>
                            </div> -->

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">轮播图片</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <div class="am-form-file">
                                            <button type="button"
                                                    class="background_img upload-file am-btn am-btn-secondary am-radius am-btn-xs">
                                                <i class="am-icon-cloud-upload"></i> 选择图片
                                            </button>
                                            <div class="uploader-list am-cf">
                                                <?php if (!empty($roionList['imgs'])): foreach ($roionList['imgs'] as $role):  ?>
                                                    <div class="file-item" style="width:460px;display:flex;justify-content: space-between;align-items:center;">
                                                        <img src="uploads/big/<?= $role['img'] ?>" width="100" height="30" alt="">
                                                        <input type="hidden" name="rotionc[url][]" style="width:330px;height:30px;border:none;border-bottom:1px solid #999;outline:none;padding:0;margin-right:10px;" value="">
                                                        <input type="hidden" name="rotionc[img][]" value="<?= $role['img'] ?>">
                                                        <i class="iconfont icon-shanchu file-item-delete"></i>
                                                    </div>
                                                <?php endforeach;endif;  ?>
                                            </div>
                                        </div>
                                        <div class="help-block am-margin-top-sm">
                                            <?php if($roionList['type'] == 1): ?>
                                                <small>注：请选择 375×150大小的图片</small>
                                            <?php elseif(($roionList['type'] == 2)): ?>
                                                <small>注：请选择 345×150大小的图片</small>
                                            <?php elseif(($roionList['type'] == 3)): ?>
                                                <small>注：请选择 375×120大小的图片</small>
                                            <?php endif; ?>
                                        </div>
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
                            <!-- 图片文件列表模板 -->
                            <script id="tpl-file-item" type="text/template">
                                {{ each list }}
                                <div class="file-item" style="width:460px;display:flex;justify-content: space-between;align-items:center;">
                                    <img src="{{ $value.file_path }}" width="100" height="30" alt="">
                                    <input type="hidden" name="rotionc[url][]" style="width:330px;height:30px;border:none;border-bottom:1px solid #999;outline:none;padding:0;margin-right:10px;" value="">
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
// 选择图片
                                    $('.background_img').selectImages({
                                        name: 'rotionc[img][]'
                                        , multiple: true
                                    });
                                    /**
                                     * 表单验证提交
                                     * @type {*}
                                     */
                                    $('#my-form').superForm();

                                });
                            </script>
