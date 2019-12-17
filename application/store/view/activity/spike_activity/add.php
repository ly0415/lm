<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">秒杀活动</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">活动名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="coupon[name]"
                                           value="" placeholder="请输入优惠券名称" required>
                                </div>
                            </div>
                            <div class="am-form-group switch-expire_type expire_type__20">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">时间范围 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="j-startTime am-form-field am-margin-bottom-sm"
                                           name="coupon[start_time]" placeholder="请选择开始日期" required>
                                    <input type="text" class="j-endTime am-form-field" name="coupon[end_time]"
                                           placeholder="请选择结束日期" required>
                                    <small>&nbsp;&nbsp;如开始时间:2015-06-15，结束时间2015-06-16</small>
                                </div>
                            </div>
                            <div class="am-form-group am-padding-top">
                                 <!-- <div class="am-u-sm-2 am-text-right form-require">设置属性：</div> -->
                                 <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">时间段 </label>
                                 <div class="am-u-sm-10">
                                     <div class="am-form-group">
                                         <div class="am-u-sm-5"><input type="text" value="08:00" disabled></div>
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
                                     <div>
                                        
                                     </div>

                                     <div class="am-form-group">
                                         <div class="am-u-sm-5"><input type="text" value="10:00" disabled></div>
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
                                     <div>

                                     </div>

                                     <div class="am-form-group">
                                         <div class="am-u-sm-5"><input type="text" value="12:00" disabled></div>
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
                                     <div>
                                     
                                     </div>

                                     <div class="am-form-group">
                                         <div class="am-u-sm-5"><input type="text" value="14:00" disabled></div>
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
                                     <div>
                                     
                                     </div>

                                     <div class="am-form-group">
                                         <div class="am-u-sm-5"><input type="text" value="16:00" disabled></div>
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
                                     <div>
                                     
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
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script>
    /**
     * 时间选择
     */
    $(function () {
        var nowTemp = new Date();
        var nowDay = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0).valueOf();
        var nowMoth = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), 1, 0, 0, 0, 0).valueOf();
        var nowYear = new Date(nowTemp.getFullYear(), 0, 1, 0, 0, 0, 0).valueOf();
        var $startTime = $('.j-startTime');
        var $endTime = $('.j-endTime');

        var checkin = $startTime.datepicker({
            onRender: function (date, viewMode) {
                // 默认 days 视图，与当前日期比较
                var viewDate = nowDay;
                switch (viewMode) {
                    // moths 视图，与当前月份比较
                    case 1:
                        viewDate = nowMoth;
                        break;
                    // years 视图，与当前年份比较
                    case 2:
                        viewDate = nowYear;
                        break;
                }
                return date.valueOf() < viewDate ? 'am-disabled' : '';
            }
        }).on('changeDate.datepicker.amui', function (ev) {
            if (ev.date.valueOf() > checkout.date.valueOf()) {
                var newDate = new Date(ev.date)
                newDate.setDate(newDate.getDate() + 1);
                checkout.setValue(newDate);
            }
            checkin.close();
            $endTime[0].focus();
        }).data('amui.datepicker');

        var checkout = $endTime.datepicker({
            onRender: function (date, viewMode) {
                var inTime = checkin.date;
                var inDay = inTime.valueOf();
                var inMoth = new Date(inTime.getFullYear(), inTime.getMonth(), 1, 0, 0, 0, 0).valueOf();
                var inYear = new Date(inTime.getFullYear(), 0, 1, 0, 0, 0, 0).valueOf();
                // 默认 days 视图，与当前日期比较
                var viewDate = inDay;
                switch (viewMode) {
                    // moths 视图，与当前月份比较
                    case 1:
                        viewDate = inMoth;
                        break;
                    // years 视图，与当前年份比较
                    case 2:
                        viewDate = inYear;
                        break;
                }
                return date.valueOf() <= viewDate ? 'am-disabled' : '';
            }
        }).on('changeDate.datepicker.amui', function (ev) {
            checkout.close();
        }).data('amui.datepicker');
    });
</script>

<script>
    $(function () {
        //选择商品
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
                    console.log(data);return false;
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
                            var td3=$('<td></td>').html('').css({'width':'150px','text-align':'center'})
                            var td4=$('<td></td>').html('').css({'width':'250px','text-align':'center'})
                            var salePrice=$('<input name="activity[goods_price]['+user[k].id+'][]" />').attr('type','text').css({'border':'none','outline':'none','background-color':'#eee'})
                            var stock=$('<input name="activity[stock]['+user[k].id+'][]" />').attr('type','text').css({'border':'none','outline':'none','background-color':'#eee'})
                            var td5=$('<td></td>').html(salePrice).css('padding','0 20px')
                            var td6=$('<td></td>').html(stock).css('padding','0 20px')
                            tbody.append($('<tr data-goods='+user[k].id+'></tr>').append([td0,td1,td2,td3,td4,td5,td6]).css('border-top','1px solid #ccc'))
                        }
                    });
                    $userList.append(table.append([thead.append(ergodic(arrhead)),tbody])).get(0)
                }
            });
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
