<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
<!--                            --><?php //if(isset($list)): foreach($list as $value):?>
                            <input type="hidden" name="old[id]" value="<?= !empty($value['id'])?$value['id']:''?>">
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">网站设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">网站备案号 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="config[record_no]" value="<?= !empty($value['record_no'])?$value['record_no']:''?>" >
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">网站名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="config[store_name]" value="<?= !empty($value['store_name'])?$value['store_name']:''?>">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">网站logo </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <div class="am-form-file">
                                            <button type="button" class="logo upload-file am-btn am-btn-secondary am-radius">
                                                <i class="am-icon-cloud-upload"></i> 选择图片
                                            </button>
                                            <div class="uploader-list am-cf">
                                                <?php if(!empty($value['store_logo'])):?>
                                                <div class="file-item">
                                                    <a href="<?= BIG_IMG.$value['store_logo']?>" title="点击查看大图" target="_blank">
                                                        <img src="<?= SIM_IMG.$value['store_logo']?>">
                                                    </a>
                                                    <input type="hidden" name="config[store_logo]" value="<?=$value['store_logo']?>">
                                                    <i class="iconfont icon-shanchu file-item-delete"></i>
                                                </div>
                                                <?php endif;?>
                                            </div>
                                            <div>
                                                <small>注：默认网站Logo，最佳显示尺寸为240×75像素.png</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">登录背景图 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <div class="am-form-file">
                                            <button type="button" class="background_img upload-file am-btn am-btn-secondary am-radius">
                                                <i class="am-icon-cloud-upload"></i> 选择图片
                                            </button>
                                            <div class="uploader-list am-cf">
                                                <?php if(!empty($value['background_img'])):?>
                                                    <div class="file-item am-text-center" style="padding-top:30px;">
                                                        <a href="<?= BIG_IMG.$value['background_img']?>" title="点击查看大图" target="_blank">
                                                            <img src="<?= SIM_IMG.$value['background_img']?>">
                                                        </a>
<!--                                                        <input type="text" data-isShow="true" value="--><?//= !empty($v['url'])?$v['url']:''?><!--" name="shop[url][]" -->
<!--                                                               style="width:170px;text-align:center;margin:10px 10px 20px 10px;">-->
                                                        <input type="hidden" name="config[background_img]" value="<?=$value['background_img']?>">
                                                        <i class="iconfont icon-shanchu file-item-delete"></i>
                                                    </div>
                                                <?php endif;?>
                                            </div>
                                            <div>
                                                <small>注：请选择登录页背景</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">网站标题 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="config[store_title]" value="<?= !empty($value['store_title'])?$value['store_title']:''?>">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">网站描述 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="config[store_desc]" value="<?= !empty($value['store_desc'])?$value['store_desc']:''?>">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">网站关键字 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="config[store_keyword]" value="<?= !empty($value['store_keyword'])?$value['store_keyword']:''?>">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">联系人 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="config[link_name]" value="<?= !empty($value['link_name'])?$value['link_name']:''?>">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">联系人电话 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="config[link_phone]" value="<?= !empty($value['link_phone'])?$value['link_phone']:''?>">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">客服电话 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="config[kefu_phone]" value="<?= !empty($value['kefu_phone'])?$value['kefu_phone']:''?>">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">优惠商品 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="config[active_name]" value="<?= !empty($value['active_name'])?$value['active_name']:''?>">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">热销商品 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="config[hot_name]" value="<?= !empty($value['hot_name'])?$value['hot_name']:''?>">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-sm am-btn-secondary">保存
                                    </button>
                                </div>
                            </div>
<!--                            --><?php //endforeach; endif; ?>
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
    <div class="file-item" style="padding-top:30px;">
        <a href="{{ $value.file_big_path }}" title="点击查看大图" target="_blank">
            <img src="{{ $value.file_path }}">
        </a>

        {{ if isShow }}
         <input type="text" data-isShow="{{ isShow }}" name="shop[url][]" style="width:170px;text-align:center;margin:10px 10px 20px 10px;">
        {{/if}}

        <input type="hidden" name="{{ name }}" value="{{ $value.file_name }}">
        <i class="iconfont icon-shanchu file-item-delete"></i>
    </div>
    {{ /each }}
</script>

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<script src="assets/common/plugins/laydate/laydate.js"></script>

<script>
    $(function () {

        $('#my-form').superForm();
        // 选择Logo
        $('.logo').selectImages({
            name: 'config[store_logo]'
        });

        // 选择背景图
        $('.background_img').selectImages({
            name: 'config[background_img]'
        });
    });
</script>
