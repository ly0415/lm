<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">添加砍价活动</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">活动标题 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="activity[title]"
                                           value="" placeholder="请输入活动标题" required>
                                </div>
                            </div>
                            <div class="am-form-group am-padding-top">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 活动时间 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <!-- 会员等级列表-->
                                    <div class="am-input-group">
                                        <input id="my-start" type="text" class="j-laydate-start am-form-field" name="activity[start_time]" placeholder="开始时间" >
                                        <span class="am-input-group-label am-input-group-label__center">至</span>
                                        <input id="my-end" type="text" class="j-laydate-end am-form-field" name="activity[end_time]" placeholder="结束时间" >
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
                                        <input  type="text" class="am-form-field" name="activity[bargain_min_price]" placeholder="砍价最小金额" >
                                        <span class="am-input-group-label am-input-group-label__center">至</span>
                                        <input  type="text" class="am-form-field" name="activity[bargain_max_price]" placeholder="砍价最大金额" >
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
                                           value="24" placeholder="砍价有效期" required>
                                    <small>自用户发起砍价到砍价截止的时间，单位：小时</small>
                                </div>
                            </div>


                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">砍价底价 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="activity[min_price]"
                                           value="1" placeholder="最低价" required>
                                    <small>砍价商品的最低价格，单位：元</small>
                                </div>
                            </div>
<!--                            <div class="am-form-group">-->
<!--                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">砍价人数 </label>-->
<!--                                <div class="am-u-sm-9 am-u-end">-->
<!--                                    <input type="number" class="tpl-form-input" name="activity[peoples]"-->
<!--                                           value="10" required>-->
<!--                                    <small>每个砍价订单的帮砍人数，达到该人数才可砍至底价</small>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="am-form-group">-->
<!--                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 可自砍一刀 </label>-->
<!--                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">-->
<!--                                    <label class="am-radio-inline">-->
<!--                                        <input type="radio" name="activity[is_self_cut]" value="1" data-am-ucheck="" checked="" class="am-ucheck-radio"><span class="am-ucheck-icons"><i class="am-icon-unchecked"></i><i class="am-icon-checked"></i></span>-->
<!--                                        允许-->
<!--                                    </label>-->
<!--                                    <label class="am-radio-inline">-->
<!--                                        <input type="radio" name="activity[is_self_cut]" value="2" data-am-ucheck="" class="am-ucheck-radio"><span class="am-ucheck-icons"><i class="am-icon-unchecked"></i><i class="am-icon-checked"></i></span>-->
<!--                                        不允许-->
<!--                                    </label>-->
<!--                                    <div class="help-block">-->
<!--                                        <small>砍价发起人自己砍一刀</small>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="am-form-group">-->
<!--                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 必须底价购买 </label>-->
<!--                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">-->
<!--                                    <label class="am-radio-inline">-->
<!--                                        <input type="radio" name="activity[is_floor_buy]" value="1" data-am-ucheck="" class="am-ucheck-radio"><span class="am-ucheck-icons"><i class="am-icon-unchecked"></i><i class="am-icon-checked"></i></span>-->
<!--                                        是-->
<!--                                    </label>-->
<!--                                    <label class="am-radio-inline">-->
<!--                                        <input type="radio" name="activity[is_floor_buy]" value="2" data-am-ucheck="" checked="" class="am-ucheck-radio"><span class="am-ucheck-icons"><i class="am-icon-unchecked"></i><i class="am-icon-checked"></i></span>-->
<!--                                        否-->
<!--                                    </label>-->
<!--                                    <div class="help-block">-->
<!--                                        <small>只有砍到底价才可以购买</small>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="am-form-group">-->
<!--                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 初始虚拟销量 </label>-->
<!--                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">-->
<!--                                    <input type="number" min="0" class="tpl-form-input" name="activity[initial_sales]" required="" value="0" pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)?(?:\.\d+)?$">-->
<!--                                    <small>注：前台展示的销量 = 虚拟销量 + 实际销量</small>-->
<!--                                </div>-->
<!--                            </div>-->
                            <div class="am-form-group am-padding-top">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 分享标题 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="activity[share_title]" value="麻烦帮我砍一刀！我真的很想要了，爱你哟！(๑′ᴗ‵๑)" required="">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 砍价助力语 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="activity[prompt_words]" value="&quot;朋友一生一起走，帮砍一刀有没有&quot;">
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">排序 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" class="tpl-form-input" name="activity[sort]"
                                           value="100" required>
                                    <small>数字越小越靠前</small>
                                </div>
                            </div>

<!--                            <div class="am-form-group am-padding-top">-->
<!--                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">活动详情 </label>-->
<!--                                <div class="am-u-sm-9 am-u-end">-->
                                    <!-- 加载编辑器的容器 -->
<!--                                    <textarea id="container" name="activity[content]"-->
<!--                                              type="text/plain"></textarea>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="am-form-group am-padding-top">-->
<!--                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">砍价规则 </label>-->
<!--                                <div class="am-u-sm-9 am-u-end">-->
                                    <!-- 加载编辑器的容器 -->
<!--                                    <textarea id="containers" name="activity[rule]"-->
<!--                                              type="text/plain"></textarea>-->
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
<script id="tpl-file-item" type="text/template">
    {{ each list }}
    <div class="file-item">
        <a href="{{ $value.file_path }}" title="点击查看大图" target="_blank">
            <img src="{{ $value.file_path }}">
        </a>
        <input type="hidden" name="{{ name }}" value="{{ $value.file_id }}">
        <i class="iconfont icon-shanchu file-item-delete"></i>
    </div>
    {{ /each }}
</script>

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}
<script src="assets/common/js/ddsort.js"></script>
<script src="assets/common/plugins/umeditor/umeditor.config.js?v=<?= $version ?>"></script>
<script src="assets/common/plugins/umeditor/umeditor.min.js"></script>
<script src="assets/common/plugins/laydate/laydate.js"></script>
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>

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
    // 循环数据，添加table的thead 数组arr表示table中thead的名称
    function ergodic(arr){
        let tr=$('<tr></tr>')
        for (var i = 0; i < arr.length; i++) {
            if(i>=arr.length){
                return
            }else{
                tr.append($('<th></th>').text(arr[i]).css('text-align','center').css('padding','20px'))
            }
        }
        return tr
    }
    // 选择会员
    $('.j-selectUser').click(function () {
        var $userList = $('.user-list');
        var goods_ids = [];
        $("input[name='activity[goods_id][]']").each(function(){
            goods_ids.push($(this).val());
        });
        // console.log(goods_ids);
        $.selectData({
            title: '选择商品',
            uri: 'store_goods/lists/goods_ids/'+goods_ids.join(','),
            dataIndex: 'goods_id',
            done: function (data) {
                var user = [];
                $userList.empty()
                var table=$('<table></table>').css('margin-top','20px')
                let thead=$('<thead></thead>')
                var arrhead=['商品ID','图片','名称','分类','规格','售价','库存']
                var tbody=$('<tbody></tbody>')
                $.each(data,function (k,v) {
                    if(goods_ids.indexOf(v.goods_id) == -1){
                        user.push(v);
                        var goodsImg=$('<img/>').attr('src',user[k].goods_image).css('padding','20px')
                        var td0=$('<td><input type="text" name="activity[goods_id][]" readonly value="'+user[k].id+'"></td>')
                        var td1=$('<td></td>').html(goodsImg)
                        var td2=$('<td></td>').html(user[k].goods_name).css({'width':'150px','text-align':'center'})
                        var td3=$('<td></td>').html(user[k].goods_category).css({'width':'150px','text-align':'center'})
                        var td4=$('<td></td>').html('').css({'width':'250px','text-align':'center'})
                        var salePrice=$('<input name="activity[goods_price]['+user[k].id+'][]" />').attr('type','text').css({'border':'none','outline':'none','background-color':'#eee'})
                        var stock=$('<input name="activity[stock]['+user[k].id+'][]" />').attr('type','text').css({'border':'none','outline':'none','background-color':'#eee'})
                        var td5=$('<td></td>').html(salePrice).css('padding','0 20px')
                        var td6=$('<td></td>').html(stock).css('padding','0 20px')
                        tbody.append($('<tr data-goods='+user[k].id+'></tr>').append([td0,td1,td2,td3,td4,td5,td6]).css('border-top','1px solid #ccc'))
                    }
                });
                $userList.append(table.append([thead.append(ergodic(arrhead)),tbody])).get(0)


                // 原始的代码
                // $.each(data,function (k,v) {
                //     if(goods_ids.indexOf(v.goods_id) == -1){
                //         user.push(v);
                //     }
                // });
                // $userList.html($userList.html()+template('tpl-user-item', user));
                // $('.file-item-delete').on('click',function () {
                //     var $this = $(this)
                //         , noClick = $this.data('noClick')
                //         , name = $this.data('name');

                //     if (noClick) {
                //         return false;
                //     }
                //     layer.confirm('您确定要删除该' + (name ? name : '图片') + '吗？', {
                //         title: '友情提示'
                //     }, function (index) {
                //         $this.parent().remove();
                //         layer.close(index);
                //     });
                // })
            }
        });
    });

    laydate.render({
        elem: '.j-laydate-start'
        , type: 'datetime'
    });
    laydate.render({
        elem: '.j-laydate-end'
        , type: 'datetime'
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

    /**
     * 表单验证提交
     * @type {*}
     */
    $('#my-form').superForm();

});
</script>
