<style>
    .laydate-time-list>li{width:50%!important;}
    .laydate-time-list>li:last-child { display: none;}
</style>
<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/orderPay.css">
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form  id="my-form" class="am-form tpl-form-line-form">
                    <input type="hidden" id="uniquecode" name="order[uniquecode]" value="<?=$request->param('uniquecode');?>">
                    <input type="hidden" id="order_amount" name="order[order_amount]" value="<?=$cartList['order_total_price']?>">
<!--                    分销用户-->
                    <input id="fx_user_id" type="hidden" name="order[fx_user_id]" value="">
<!--                    分销折扣-->
                    <input id="fx_discount" type="hidden" name="order[fx_discount]" value="">
<!--                    自提-->
                    <input type="hidden" name="order[sendout]" value="1">
<!--                    代客下单-->
                    <input type="hidden" name="order[source]" value="3">
<!--                    优惠券id-->
                    <input type="hidden" id="coupon_id" name="order[coupon_id]" value="">
<!--                    用户优惠券id-->
                    <input type="hidden" id="user_coupon_id" name="order[user_coupon_id]" value="">
<!--                    优惠券金额-->
<!--                    <input type="hidden" id="coupon_amount" name="order[coupon_amount]" value="0">-->
<!--                    打折优惠/金额优惠-->
                    <input type="hidden" id="discount_type" name="order[discount_type]" value="1">

<!--                    支付方式-->
                    <input type="hidden" id="payType" name="order[pay_type]" value="">
                    <input type="hidden" id="pay_price" name="order[pay_price]" value="<?=$cartList['order_total_price']?>"><!-- 应付金额 -->

                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">订单支付</div>
                            </div>
                            <div class="payCont">
                                <!-- 购买的详细信息 -->
                                <div class="mainBox">
                                    <div class="contentBox" >
                                        <!-- 商品信息列表 -->
                                        <div class="goodsNews">
                                            <table class="am-table am-text-justify">
                                                <thead>
                                                    <tr>
                                                        <th class="am-text-center">商品信息</th>
                                                        <th class="am-text-center">数量</th>
                                                        <th class="am-text-center">价格</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php if(!empty($cartList) && isset($cartList['goods_list'])):foreach ($cartList['goods_list'] as $cart):?>
                                                    <input type="hidden" name="order[cart_ids][]" value="<?=$cart['cart_id']?>">
                                                    <input type="hidden" name="order[goods_id][]" value="<?=$cart['goods_id']?>">
                                                    <tr class="goodsRow">
                                                        <td>
                                                            <div class="picSpecBox">
                                                                <img src="../<?=$cart['original_img']?>" alt="">
                                                                <div class="am-margin-left">
                                                                    <div class="am-text-center goodsName"><?=$cart['goods_name']?></div>
                                                                    <div class="am-text-center">
                                                                        <span><?=$cart['spec_key_name']?></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="am-text-center am-text-middle">
                                                            <div>
                                                                X<span><?=$cart['total_num']?></span>
                                                            </div>
                                                        </td>
                                                        <td class="am-text-center am-text-middle">
                                                            <div>
                                                                ￥<span class="goodsprice"><?=$cart['total_price']?></span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach;endif;?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="orderSuccess">
                                            <!-- 购买的商品的总价 -->
                                            <div class="am-cf orderRow">
                                                <div class="am-fl allPrice">订单提交成功,请尽快付款!</div>
                                                <div class="am-fr allPrice">
                                                    <span class="title">总价：</span>
                                                    <span class="yuan">￥<?=$cartList['order_total_price']?></span>
                                                </div>
                                            </div>
                                            <!-- 配送时间和客户 -->
                                            <div class="customerTime">
                                                <div class="lines am-padding-top">
                                                    <span>自提时间：</span>
                                                    <input type="text" style="width:260px;" id="store_start_time" value="<?=date('H:i')?>" class="tpl-form-input" name="order[sendout_time]" placeholder="请选择自提时间">
                                                </div>
                                                <div class="lines am-padding-top">
                                                    <span>选择用户：</span>
                                                    <div class="am-u-sm-5 am-fl" style="padding-left:0;">
                                                        <div class="am-u-sm-12 am-u-end" style="padding:0;display:flex;">
                                                            <div class="select-content am-form-group" style="margin-bottom:0;">
                                                                <input  type="text" name="order[phone]" id="select_input" class="select-input" value="" autocomplete="off" placeholder="请输入手机号" />
                                                                <div id="search_select" class="search-select">
                                                                    <ul id="select_ul" class="select-ul">

                                                                    </ul>
                                                                </div>
                                                            </div>
                                                            <small class="am-margin-left-xs">
                                                                <button type="button" class="j-selectUser am-btn am-btn-secondary am-btn-xs am-margin-left">选择用户</button>
                                                            </small>
                                                        </div>
                                                        <small>注：可使用扫码枪</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- 折扣券和兑换券选项卡 -->
                                            <div class="tabDiscount" style="display:none;">

                                            </div>
                                            <!-- 优惠打折和优惠金额选项卡 -->
                                            <div class="DiscountAmont">
                                                <div class="am-tabs" data-am-tabs>
                                                    <ul class="am-tabs-nav am-nav am-nav-tabs">
                                                        <li class="am-active"><a href="javascript: void(0)" idx='1'>优惠打折</a></li>
                                                        <li><a href="javascript: void(0)" idx='2'>优惠金额</a></li>
                                                    </ul>
                                                    <div class="am-tabs-bd">
                                                        <div class="am-tab-panel am-active">
                                                            <div class="UpRow descRow">
                                                                <div class="discName am-text-middle am-text-right">优惠打折：</div>
                                                                <input type="text" name="order[discount_num]" class="dazhe am-margin-right" autocomplete="off">
                                                                <p>
                                                                    <span>减价</span>
                                                                    <span class="reducePrice dazhePrice">￥0.00</span>
                                                                    <input type="hidden" id="discount_amount" name="order[discount_amount]" value="" class="am-margin-right">
                                                                </p>
                                                            </div>
                                                            <small style="margin-left:100px;">折扣必须大于0,小于10的数字</small>
                                                        </div>
                                                        <div class="am-tab-panel">
                                                            <div class="UpRow descRow">
                                                                <div class="discName am-text-middle am-text-right">优惠金额：</div>
                                                                <input type="text" name="order[reduced_price]" class="jine am-margin-right" autocomplete="off">
                                                                <p>
                                                                    <span>减价</span>
                                                                    <span class="reducePrice jineReduce">￥0.00</span>
                                                                </p>
                                                            </div>
                                                            <small style="margin-left:100px;">优惠金额必须小于应付金额的数字</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- 分销码 -->
                                            <div class="DownRow am-margin-bottom-xl descRow">
                                                <span class="am-fl">分销码：</span>
                                                <input type="text" name="order[fx_code]" id="fx_code" class="fenxiao am-margin-right" autocomplete="off" readonly>
                                                <p>
                                                    <span>减价</span>
                                                    <span class="reducePrice fx_reprice">￥0.00</span>
                                                </p>
                                            </div>
                                            <!-- 应付金额 -->
                                            <div class="payMoney am-margin-bottom-xl">
                                                <div class="payDetail">
                                                    <span class="am-fl">应付金额：</span>
                                                    <div class="payBtnBox">
                                                        <span class="payprice"><?=$cartList['order_pay_price']?></span>
                                                    </div>
                                                </div>
                                                <small style="margin-left:100px;">应付金额不足0元时需付0.01元</small>
                                            </div>
                                            <!-- 付款方式 -->
                                            <div class="selPaymenthod am-margin-bottom-xl">
                                                <span class="am-fl">支付方式：</span>
                                                <div class="payBtnBox">
                                                    <?php if(in_array(STORE_ID,[98,82,72,78,94,93,76,80,92,100,99,188])): ?>
                                                        <button data-type="11" type="button" class="yinlian" data-am-modal="{target: '#doc-modal-11'}">聚合支付</button>
                                                    <?php else: ?>
                                                        <button data-type="1" type="submit" class="j-submit am-btn" >支付宝支付</button>
                                                        <button data-type="2" type="submit" class="j-submit am-btn" >微信支付</button>
                                                    <?php endif;?>
                                                    <button data-type="4" type="button" class="onlineOrder" data-am-modal="{target: '#doc-modal-3'}">线下支付</button>
                                                    <button data-type="3" type="button" data-am-modal="{target: '#doc-modal-2'}">余额付款</button>
                                                    <!--<button type="button">免费兑换</button>-->
                                                    <!-- <button type="button">加入预购</button> -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- 扫码获取用户信息 -->
                                <div class="am-modal am-modal-no-btn" tabindex="-1" id="doc-modal-11">
                                    <div class="am-modal-dialog" style="width:400px;height:250px;overflow:auto;border-top:3px solid #ffa627;background:#fff;">
                                        <div class="widget-head am-cf">
                                            <div class="widget-title am-fl">扫码支付</div>
                                        </div>
                                        <div class="am-modal-body " style="position: relative;" id="yinlian_img">

                                        </div>
                                    </div>
                                </div>
                                <div class="am-modal am-modal-no-btn" tabindex="-1" id="doc-modal-2">
                                    <div class="am-modal-dialog" style="width:400px;height:220px;overflow:auto;border-top:3px solid #ffa627;background:#fff;">
                                        <div class="widget-head am-cf">
                                            <div class="widget-title am-fl">余额支付</div>
                                        </div>
                                        <div class="am-modal-body">
                                                <div class="am-form-group am-margin-bottom-lg am-margin-top">
                                                    <label class="am-u-sm-5 am-u-lg-4 am-form-label">请输入验证码：</label>
                                                    <div class="am-u-sm-7 am-u-end" style="display:flex;">
                                                        <input type="text" class="tpl-form-input" name="order[code]" value="">
                                                        <button class="am-margin-left am-btn am-btn-default am-btn-xs" id="phoneCode" type="button">获取验证码</button>
                                                        <button class="am-margin-left am-btn am-btn-default am-btn-xs" id="J_resetCode" type="button" style="display:none;"><span id="J_second">60</span>秒后重发</button>
                                                    </div>
                                                </div>
                                            <button type="button" class="am-modal-btn cancelPay" data-am-modal-cancel>取消付款</button>
                                            <button type="submit" class="j-submit am-btn" >确认付款
                                            </button>

                                        </div>
                                    </div>
                                </div>

                                <div class="am-modal" id="doc-modal-3">
                                    <div class="am-modal-dialog" style="width:580px;">
                                        <div class="am-modal-hd am-text-left am-text-xs" style="padding:0;">
                                            <div class="widget-head am-cf" style="margin:0;padding:0;height:45px;line-height:45px;">
                                                <span style="font-size:16px;margin-left:20px;">线下支付</span>
                                                <!-- <div class="widget-title am-fl">线下支付</div> -->
                                                <a href="javascript: void(0)" class="am-close am-close-spin" data-am-modal-close>&times;</a>
                                            </div>
                                        </div>
                                        <div class="am-modal-bd" style="background-color:white;border-bottom:1px solid #eaeaea;">
                                            <div class="specBox">
                                                <div class="row">
                                                    <div class="specName">所属平台：</div>
                                                    <div class="specVal pingtai">
                                                        <?php if(!empty($sourceList)): foreach ($sourceList as $key => $value): ?>
                                                        <div class="per_pingtai">
                                                            <input type="radio" name="order[store_source_id]" value="<?= $key ?>"   <?php if($key == 1): ?>checked<?php endif; ?>>
                                                            <img src="<?= BIG_IMG.$value ?>" alt="">
                                                        </div>
                                                        <?php endforeach; endif; ?>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="specName">当前金额：</div>
                                                    <div class="specVal">
                                                        <span class="currentPrice"></span>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="specName">支付金额：</div>
                                                    <div class="specVal">
                                                        <input type="text" class="am-fl payBox" id="payMoney" name="order[payMoney]" style="padding-left:10px;width:300px;border:none;border-bottom:1px solid #d1d1d1;">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="specName">配&nbsp&nbsp送&nbsp&nbsp费：</div>
                                                    <div class="specVal">
                                                        <input type="text" class="am-fl" name="order[source_delivery_fee]" placeholder="非必填" style="padding-left:10px;width:300px;border:none;border-bottom:1px solid #d1d1d1;">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="specName">配送地址：</div>
                                                    <div class="specVal">
                                                        <input type="text" class="am-fl"  name="order[source_address]" placeholder="非必填" style="padding-left:10px;width:300px;border:none;border-bottom:1px solid #d1d1d1;">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="specName">找&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp零：</div>
                                                    <div class="specVal">
                                                        <span class="changemoney">￥0.00</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="am-modal-footer" style="height:55px;background-color:white;display:flex;justify-content:flex-end;align-items:center;">
                                            <button type="submit" style="width:80px;height:30px;line-height:30px;text-align:center;color:white;background-color:#1e9fff;font-size:14px;padding:0;margin-right:10px;" class="j-submit am-btn" >确认付款
                                            </button>
                                            <button class="am-modal-btn" style="color:black;width:80px;height:30px;line-height:28px;text-align:center;font-size:14px;border:1px solid #dedede;margin-right:15px;" data-am-modal-cancel>取消付款</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}
<script src="assets/common/plugins/laydate/laydate.js"></script>
<script src="assets/store/js/select.data.js"></script>
<script>
    var quan_price=0;
    var reduce_price=0;
    var fenxiao=0;
    var fx_discount=$("#fx_discount").val();
    var total_Price=($('.yuan').text()).substring(1)
    var goodsAllPrice=Number(total_Price)

    //获取分销信息
    function getFxInfo() {
        var phone = $.trim($('#select_input').val());
        $.post("<?=url('order.setting/ajax_get_fx_code')?>",{phone:phone},function (f) {
            if(f.code == 1){
                if(f.data.discounts>0){
                    $("#fx_discount").val(f.data.discounts);
                }else{
                    $("#fx_discount").val(f.data.discount);
                }
                $("#fx_user_id").val(f.data.id);
                $("#fx_code").val(f.data.fx_code);
            }else{
                $("#fx_discount").val(0);
                $("#fx_user_id").val(0);
                $("#fx_code").val('');
            }
            fenxiao=$('#fx_discount').val()
            var fx_youhui=(parseInt(fenxiao)*parseFloat(goodsAllPrice)*parseFloat(0.01)).toFixed(2)
            $('.fx_reprice').text('￥'+fx_youhui)

            var fx_price=($('.fx_reprice').text()).substring(1) 
            var pay_money=goodsAllPrice-Number(quan_price)-Number(reduce_price)-Number(fx_price)
            if(pay_money<=0){
                pay_money=0.01
            }
            $('.payprice').text(pay_money.toFixed(2))
            $('#pay_price').val(pay_money.toFixed(2))

        },'JSON');
    }

    //获取优惠券
    function getCoupon() {
        var phone = $.trim($('#select_input').val());
        if (phone.length == 11) {
            var amount = $('#order_amount').val();
            var uniquecode = $('#uniquecode').val();
            $.post("<?=url('order.setting/ajax_get_coupon')?>",{amount:amount,phone:phone,uniquecode:uniquecode},function (re) {
                $('.tabDiscount').show().empty().append(re);

            });
        }
    }
    //查询订单支付状态
    var pro = false;
    function query_order_status(order_id,order_sn){
        var timeStatus = setInterval(function(){
            if(pro)return false;
            pro = true;
            if(!order_id || !order_sn){
                $.show_error(result.msg);return false;
            }
            $.post("<?=url('wxpay/query_order_status')?>",{order_id:order_id,order_sn:order_sn},function (result) {
                console.log(result);
                if(result.code === 1){
                    clearInterval(timeStatus);
                    $.show_success(result.msg, result.url);
                }else if(result.code == 3){
                    pro = false;
                }else{
                    $.show_error(result.msg);
                }
            },'JSON')
        }, 3000);
    }

    $(function(){

        document.onkeydown = function keyDown(e){
            if(e.keyCode==13){
                window.event.returnValue = false;  //设置条形码扫描后不进行自动提交
                //获取详细信息操作
            }
       }

        // 获取验证码的倒计时
        function resetCode(){
            $('#phoneCode').hide();
            $('#J_second').html('60');
            $('#J_resetCode').show();
            var second = 60;
            var timer = null;
            timer = setInterval(function(){
                second -= 1;
                if(second >0 ){
                    $('#J_second').html(second);
                }else{
                    clearInterval(timer);
                    $('#phoneCode').show();
                    $('#J_resetCode').hide();
                }
            },1000);
        }

        //获取短信验证码
        $("#phoneCode").on('click',function () {
            var phone = $.trim($('#select_input').val());
            var reg = /^1\d{10}$/;    //正则表达式
            if(!reg.test(phone)){
                layer.msg('请正确填写手机号');
                return false;
            }
            layer.confirm('确定要发送短信到'+phone+'吗', function (index) {
                var load = layer.load();
                resetCode();
                var url = "<?= url('order.setting/sms_code') ?>";
                $.post(url, {accept_phone:phone}, function (result) {
                    layer.msg(result.msg);
                    layer.close(load);
                },'JSON');
                layer.close(index);
            });
        });

        $('.am-tabs').tabs({noSwipe:1});

        $('#my-form').superForm();

        // 选择用户
        $('.j-selectUser').click(function () {
            var $userList = $('#select_input');
            $.selectData({
                title: '选择用户',
                uri: 'user/lists',
                dataIndex: 'id',
                done: function (data) {
                    var user = data[0];
                    $userList.val(user.phone);
                    getCoupon();
                    getFxInfo()
                }
            });
        });
        
        $(document).on('click','.sel_user',function(){
            $('.selUserCode').focus()
        })

        $('.payBtnBox button').on('click',function () {
            $("#payType").val($(this).data('type'));
        });

        //时间选择器
        laydate.render({
            elem: '#store_start_time'
            ,type: 'time'
            ,format: 'HH:mm'
        });

        // 优惠券兑换券显示问题
        $(document).on('keyup','#select_input',function(){
            var _this = $(this);
            var phone = _this.val();
            if(phone.length >= 3){
                $('.select-ul').show().css({"border":"1px solid #ececec"});
                var _html = '';
                $.post("<?=url('order.setting/search_user')?>",{phone:phone},function (res) {
                    $.each(res.data.data,function (k,v) {
                        _html += '<li>'+v.phone+'</li>';
                    });
                    $('.select-ul').empty().append(_html);
                },'JSON');
            }
        });

        // 是否展示券的描述
        $(document).on('click','.useDesc img',function(){
            var orshow=$(this).attr('sureShow')
            $(this).closest('.tabInnerContent').siblings().find('.useDesc>img').attr('sureShow',1)
            if(orshow==1){
                $(this).attr('sureShow',2)
                $(this).closest('.tabInnerContent').children('.usetips').show()
                $(this).closest('.tabInnerContent').siblings().children('.usetips').hide()
            }else{
                $(this).attr('sureShow',1)
                $(this).closest('.tabInnerContent').children('.usetips').hide()
            }
        })

        $(document).on('click','.select-ul li',function(){
            var txt = $(this).text();
            $('#select_input').val(txt);
            $('.select-ul').hide();
            getCoupon();
            getFxInfo();
        })

        $(document).on('click','.RightBtn',function(){
            if($(this).closest('.tabInnerContent').attr('orChoose')){
                $(this).closest('.tabInnerContent').removeAttr('orChoose').find('.RightBtn').children().text('立即使用')
                $(this).closest('.tabInnerContent').removeClass('doubleRow_active').addClass('doubleRow')
                $( "input[name='order[coupon_id]']" ).val(0);
                $( "input[name='order[user_coupon_id]']" ).val(0);
                $( "input[name='order[coupon_amount]']" ).val(0);
                quan_price=0
            }else{
                var coupon_id=$(this).attr('data-coupon_id')
                var user_coupon_id=$(this).attr('data-user_coupon_id')
                var coupon_amount=$(this).attr('data-coupon_amount')
                $( "input[name='order[coupon_id]']" ).val(coupon_id);
                $( "input[name='order[user_coupon_id]']" ).val(user_coupon_id);
                $( "input[name='order[coupon_amount]']" ).val(coupon_amount);
                $(this).children('div').text('已选择')
                $(this).closest('.tabInnerContent').removeClass('doubleRow').addClass('doubleRow_active')
                $(this).closest('.tabInnerContent').siblings().removeClass('doubleRow_active').addClass('doubleRow')
                $(this).closest('.tabInnerContent').attr('orChoose',1).siblings().removeAttr('orChoose').find('.RightBtn').children().text('立即使用')
                $(this).closest('.am-tab-panel').siblings().children().removeAttr('orChoose').find('.RightBtn').children().text('立即使用')
                quan_price=Number($(this).attr('data-coupon_amount'))
            }
            var fx_youhui=(parseInt(fenxiao)*parseFloat(Number(goodsAllPrice)-Number(quan_price))*parseFloat(0.01)).toFixed(2)
            $('.fx_reprice').text('￥'+fx_youhui)
            var fx_price=($('.fx_reprice').text()).substring(1)
            var pay_money=goodsAllPrice-Number(quan_price)-Number(reduce_price)-Number(fx_price)
            if(pay_money<=0){
                pay_money=0.01
            }
            $('.payprice').text(pay_money.toFixed(2))
            $('#pay_price').val(pay_money.toFixed(2))
        })

        $(document).on('click','.DiscountAmont a',function(){
            var discount_type=$(this).attr('idx')
            $("input[name='order[discount_type]']").val(discount_type)
            $('.dazhe').val('')
            $('.jine').val('')

            $('.dazhePrice').text('￥'+(0).toFixed(2))
            $('.jineReduce').text("￥"+(0).toFixed(2))
        })
        
        

        $('.dazhe').keyup(function(){
            var discount_num=Number($(this).val())//获取折扣数字
            var discount_num_reduce=0;
            if(discount_num>=10){
                discount_num=10
                $(this).val(discount_num)
                discount_num_reduce=(0).toFixed(2)
            }
            if(discount_num&&discount_num<10){
                discount_num_reduce=((1-parseInt(Number(discount_num))*parseFloat(0.1))*parseFloat(goodsAllPrice)).toFixed(2)
            }
            if(discount_num==''||discount_num<=0){
                discount_num=0
                $(this).val('')
                discount_num_reduce=(0).toFixed(2)
            }
            $('.dazhePrice').text('￥'+discount_num_reduce)
            reduce_price=($('.dazhePrice').text()).substring(1)
            fx_price=($('.fx_reprice').text()).substring(1)

            var pay_money=goodsAllPrice-Number(quan_price)-Number(reduce_price)-Number(fx_price)
            if(pay_money<=0){
                pay_money=0.01
            }
            $('.payprice').text(pay_money.toFixed(2))
            $('#pay_price').val(pay_money.toFixed(2))
        })

        $('.jine').keyup(function(){
            var discount_price=Number($(this).val())//获取优惠金额
            var discount_price_reduce=0;
            if(discount_price>=goodsAllPrice){
                discount_price_reduce=(goodsAllPrice).toFixed(2)
            }
            if(discount_price<goodsAllPrice&&discount_price>0){
                discount_price_reduce=(discount_price).toFixed(2)
            }
            if(discount_price<=0||discount_price==''){
                $(this).val('')
                discount_price_reduce=(0).toFixed(2)
            }
            $('.jineReduce').text("￥"+discount_price_reduce)
            reduce_price=($('.jineReduce').text()).substring(1)
            fx_price=($('.fx_reprice').text()).substring(1)
            var pay_money=goodsAllPrice-Number(quan_price)-Number(reduce_price)-Number(fx_price)
            if(pay_money<=0){
                pay_money=0.01
            }
            $('.payprice').text(pay_money.toFixed(2))
            $('#pay_price').val(pay_money.toFixed(2))
        })

        //获取银联支付二维码
        $(document).on('click','.yinlian',function(){

            var phone = $.trim($('#select_input').val());
            if(!phone){
                layer.msg('请填写手机号');
                return false;
            }
            var reg = /^1\d{10}$/;    //正则表达式
            if(!reg.test(phone)){
                layer.msg('请正确填写手机号');
                return false;
            }
            var cart = [];
            $.each($('input[name="order[cart_ids][]"]'),function () {
                cart.push($(this).val());
            });
            var pay_money   = $.trim($('#pay_price').val());
            var order_sn    = $.trim($('#uniquecode').val());
            var data = {order: {
                    phone : phone,
                    uniquecode : order_sn,
                    cart_ids : cart,
                    pay_type : $('#payType').val(),
                    sendout : $('input[name="order[sendout]"]').val(),
                    source : $('input[name="order[source]"]').val(),
                    sendout_time : $('input[name="order[sendout_time]"]').val(),
                    fx_user_id : $("#fx_user_id").val(),
                    fx_discount : $("#fx_discount").val(),
                    fx_code : $('input[name="order[fx_code]"]').val(),
                    discount_num : $('input[name="order[discount_num]"]').val(),
                    coupon_id : $('input[name="order[coupon_id]"]').val(),
                    user_coupon_id : $('input[name="order[user_coupon_id]"]').val(),
                    discount_type : $('input[name="order[discount_type]"]').val(),
                    reduced_price : $('input[name="order[reduced_price]"]').val(),
                }
            };
            $.post("<?=url('order/order_payment')?>",data,function (r) {
                if(r.code == 1){
                    var imgUrl = "<?=url('api/wxapp/getYinlianCode','',false)?>"+"/pay_money/"+pay_money+"/order_sn/"+order_sn+"/store_id/<?= STORE_ID;?>";
                    $("#yinlian_img").html('<img src="'+imgUrl+'" alt=""/>');
                    query_order_status(r.data.order_id,r.data.order_sn);
                }else if(r.code == 2){
                    $.show_success(r.msg,r.url);
                }else{
                    $.show_error(r.msg);
                }
            },'JSON');
        });

        $(document).on('click','.onlineOrder',function(){
            var pays=$('.payprice').text();
            $('.currentPrice').text(pays);
        })
        $(document).on('keyup','.specVal>.payBox',function(){
            var payment_price = $(this).val();
            var pay_price = $('.currentPrice').text();

            if(payment_price!=0){
                if(Number(payment_price)-Number(pay_price)<0){
                    $('.changemoney').text(0);
                }else{
                    $('.changemoney').text((parseFloat(payment_price) - parseFloat(pay_price)).toFixed(2));
                }
            }else{
                $('.changemoney').text(0);
            }
        })

    })
</script>