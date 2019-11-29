<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/hospitalityOrders.css">
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <input type="hidden" name="order_id" value="<?=$order['order_id']?>">
                    <input type="hidden" name="order_sn" value="<?=$order['order_sn']?>">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">微信支付</div>
                            </div>
                            <div>
                                <div class="wxpayBox am-text-center">
                                    <div class="wxpay">
                                        <div class="wxPayCont">
                                            <img src="<?=url('wxpay/qr_code',array('url'=>$order['payment']))?>" alt="">
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
<script>

        // 检测是否支付成功
        var pro = false;
        $(function(){
            var timeStatus = setInterval(function(){
                if(pro)return false;
                pro = true;
                var order_sn = $("input[name='order_sn']").val();
                var order_id = $("input[name='order_id']").val();
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
        })


</script>