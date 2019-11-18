<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑砍价活动</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">活动标题 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input"  name="activity[title]"
                                           value="<?= $model['title'] ?>" placeholder="请输入活动标题" required>
                                </div>
                            </div>
                            <div class="am-form-group am-padding-top">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 活动时间 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <!-- 会员等级列表-->
                                    <div class="am-input-group">
                                        <input id="my-start" type="text" class="j-laydate-start am-form-field" name="activity[start_time]" placeholder="开始时间" value="<?=date('Y-m-d H:i:s',$model['start_time'])?>">
                                        <span class="am-input-group-label am-input-group-label__center">至</span>
                                        <input id="my-end" type="text" class="j-laydate-end am-form-field" name="activity[end_time]" placeholder="结束时间" value="<?=date('Y-m-d H:i:s',$model['end_time'])?>">
                                    </div>
                                    <div class="help-block">
                                        <small>砍价活动的开始日期与截止日期</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group am-padding-top">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 砍价区间 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <div class="am-input-group">
                                        <input  type="text" class="am-form-field" value="<?=$model['bargain_min_price']?>" name="activity[bargain_min_price]" placeholder="砍价最小金额" >
                                        <span class="am-input-group-label am-input-group-label__center">至</span>
                                        <input  type="text" class="am-form-field" value="<?=$model['bargain_max_price']?>" name="activity[bargain_max_price]" placeholder="砍价最大金额" >
                                    </div>
                                    <div class="help-block">
                                        <small>砍价区间</small>
                                    </div>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">砍价有效期 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="activity[expiry_time]"
                                           value="<?=$model['expiry_time']?>" placeholder="砍价有效期" required>
                                    <small>自用户发起砍价到砍价截止的时间，单位：小时</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">砍价底价 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="activity[min_price]"
                                           value="<?=$model['min_price']?>" placeholder="最低价" required>
                                    <small>砍价商品的最低价格，单位：元</small>
                                </div>
                            </div>



<!--                            <div class="am-form-group">-->
<!--                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 虚拟销量 </label>-->
<!--                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">-->
<!--                                    <input type="number" min="0" class="tpl-form-input" name="activity[initial_sales]" value="--><?//=$model['initial_sales']?><!--" required="" pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)?(?:\.\d+)?$">-->
<!--                                    <small>注：前台展示的销量 = 虚拟销量 + 实际销量</small>-->
<!--                                </div>-->
<!--                            </div>-->
                            <div class="am-form-group am-padding-top">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 分享标题 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="activity[share_title]" value="<?=$model['share_title']?>" required="">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 砍价助力语 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="activity[prompt_words]" value="<?=$model['prompt_words']?>">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">排序 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" class="tpl-form-input" name="activity[sort]"
                                           value="<?= $model['sort'] ?>" required>
                                    <small>数字越小越靠前</small>
                                </div>
                            </div>
<!--                            <div class="am-form-group am-padding-top">-->
<!--                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">活动详情 </label>-->
<!--                                <div class="am-u-sm-9 am-u-end">-->
                                    <!-- 加载编辑器的容器 -->
<!--                                    <textarea id="container" name="activity[content]"-->
<!--                                              type="text/plain">--><?//= $model['content'] ?><!--</textarea>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="am-form-group am-padding-top">-->
<!--                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">砍价规则 </label>-->
<!--                                <div class="am-u-sm-9 am-u-end">-->
                                    <!-- 加载编辑器的容器 -->
<!--                                    <textarea id="containers" name="activity[rule]"-->
<!--                                              type="text/plain">--><?//= $model['rule'] ?><!--</textarea>-->
<!--                                </div>-->
<!--                            </div>-->
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">添加商品</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3  am-u-lg-2 am-form-label form-require"> 选择商品 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="widget-become-goods am-form-file am-margin-top-xs">
                                        <button type="button"
                                                class="j-selectUser  am-btn am-btn-secondary am-radius" style="    font-size: 1.22rem;
    padding: .5rem .9rem;">
                                            <i class="am-icon-cloud-upload"></i> 选择商品
                                        </button>
                                        <div class="user-list uploader-list am-cf">
                                            <div class="am-scrollable-horizontal am-u-sm-12">
                                                <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                                                    <thead>
                                                    <tr>
                                                        <th>商品ID</th>
                                                        <th>图片</th>
                                                        <th>商品名称</th>
                                                        <th>分类</th>
                                                        <th>规格</th>
                                                        <th>售价</th>
                                                        <th>库存</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php if ($model['activity_goods']): foreach ($model['activity_goods'] as $item): ?>
                                                        <tr>
                                                            <td class="am-text-middle" ><input type="hidden" name="activity[goods_id][]"  value="<?=$item['goods_id']?>"><?=$item['goods_id']?></td>
                                                            <td class="am-text-middle">
                                                                <img src="<?=$item['original_img']?>" alt="">                                </td>
                                                            <td class="am-text-middle"><?=$item['goods_name']?></td>
                                                            <td class="am-text-middle"></td>
                                                            <td class="am-text-middle"></td>
                                                            <td class="am-text-middle"><input type="text" name="activity[goods_price][<?=$item['goods_id']?>]" value="<?=$item['goods_price']?>"></td>
                                                            <td class="am-text-middle"><input type="text" value="<?=$item['stock']?>" name="activity[stock][<?=$item['goods_id']?>]"></td>
                                                        </tr>
                                                    <?php endforeach; else: ?>
                                                        <tr>
                                                            <td colspan="10" class="am-text-center">暂无商品</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>

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


<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<script src="assets/common/plugins/umeditor/umeditor.config.js?v=<?= $version ?>"></script>
<script src="assets/common/plugins/umeditor/umeditor.min.js"></script>
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script src="assets/common/plugins/laydate/laydate.js"></script>
<!--<script id="tpl-user-item" type="text/template">-->
<!--    {{ each $data }}-->
<!--    <div class="file-item">-->
<!--        <a href="{{ $value.goods_image }}" title="{{ $value.goods_name }} (ID:{{ $value.goods_id }})" target="_blank">-->
<!--            <img src="{{ $value.goods_image }}">-->
<!--        </a>-->
<!--        <input type="hidden" name="activity[goods_id][]" value="{{ $value.goods_id }}">-->
<!--        <i class="iconfont icon-shanchu file-item-delete"></i>-->
<!--    </div>-->
<!--    {{ /each }}-->
<!--</script>-->

<script>
    $(function () {
        // 选择会员
        $('.j-selectUser').on('click',function () {
            var $userList = $('.user-list');
            var goods_ids = [];
            $("input[name='activity[goods_id][]']").each(function(){
                goods_ids.push($(this).val());
            });
            console.log(goods_ids);
            $.selectData({
                title: '选择商品',
                uri: 'store_goods/lists/goods_ids/'+goods_ids.join(','),
                dataIndex: 'goods_id',
                done: function (data) {
                    var user = [];
                    $.each(data,function (k,v) {
                        if(goods_ids.indexOf(v.goods_id) == -1){
                            user.push(v);
                        }
                    });
                    $userList.html($userList.html()+template('tpl-user-item', user));
                    // $delete = $userList.find('.file-item-delete');
                    // 删除文件
                    $('.file-item-delete').on('click',function () {
                    var $this = $(this)
                        , noClick = $this.data('noClick')
                        , name = $this.data('name');

                    if (noClick) {
                        return false;
                    }
                    layer.confirm('您确定要删除该' + (name ? name : '图片') + '吗？', {
                        title: '友情提示'
                    }, function (index) {
                        $this.parent().remove();
                        layer.close(index);
                    });
                }
                )}
            });
        });
        // 选择图片
        $('.upload-file').selectImages({
            name: 'activity[image]'
        });

        // 富文本编辑器
        // UM.getEditor('container', {
        //     initialFrameWidth: 375 + 15,
        //     initialFrameHeight: 300
        // });
        // UM.getEditor('containers', {
        //     initialFrameWidth: 375 + 15,
        //     initialFrameHeight: 300
        // });

        laydate.render({
            elem: '.j-laydate-start'
            , type: 'datetime'
        });
        laydate.render({
            elem: '.j-laydate-end'
            , type: 'datetime'
        });
        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();
    });
</script>
