<?php
      header("Content-type:text/html;charset=utf-8");
?>
<div class="wrapper">
    <textarea id="textarea1" style="display: none" js-print-id1="">
         <table style="width: 92%;margin-left:-40px;">
                <?php foreach ($print_data as $order): ?>
                    <tr>
                        <td height="10">
                            <div id="js-content">
                                <span style="display: block;margin: 10px;text-align: center;font-weight: bold"><font face="楷体" size="3"><?= $order['store_name'] ?></font></span>
                                <span style="display: block;margin-bottom: 5px;position: relative;"><font face="楷体" size="3">订 单 号:<span style="position: absolute;right: 0"><?= $order['order_sn'] ?></span></font></span>
                                <span style="display: block;margin-bottom: 5px;position: relative;"><font face="楷体" size="3">取 货 码:<span style="position: absolute;right: 0"><?= $order['number_order'] ?></span></font></span>
                                <span style="display: block;margin-bottom: 5px;"><font face="楷体" size="3">订单商品: </font></span>
                                <?php foreach ($order['goods_list'] as $goods): ?>
                                    <div style="width:100%;height: 60px;">
                                        <span style="display: block;margin: 0 0 5px 15px"><font face="楷体" size="2"><b><?= $goods['goods_name'] ?></b></font><font face="楷体" size="1">&nbsp;&nbsp;&nbsp;规格:（<?= $goods['spec_key_name'] ?>）</font></span>
                                        <span style="display: block;margin-bottom: 5px;position: relative"><font face="楷体" size="3"><span style="position: absolute;right: 0"> X <?= $goods['goods_num'] ?>&nbsp&nbsp&nbsp&nbsp<?= $goods['goods_pay_price'] ?></span></font></span>
                                    </div>
                                <?php endforeach; ?>
                                <span style="display: block;margin-bottom: 5px;position: relative;"><font face="楷体" size="3">订单状态: <span style="position: absolute;right: 0"><?= $order['format_order_state'] ?></span></font></span>
                                <span style="display: block;margin-bottom: 5px;position: relative;"><font face="楷体" size="3">支付方式: <span style="position: absolute;right: 0"><?= $order['format_payment_type'] ?></span></font></span>
                                <span style="display: block;margin-bottom: 5px;position: relative;"><font face="楷体" size="3">用 户 名: <span style="position: absolute;right: 0"><?= $order['username'] ?></span></font></span>
                                <span style="display: block;margin-bottom: 5px;position: relative;"><font face="楷体" size="3">用户电话: <span style="position: absolute;right: 0"><?= $order['format_phone'] ?></span></font></span>
                                <span style="display: block;margin: 5px 0;position: relative;"><font face="楷体" size="3">商品总额: <span style="position: absolute;right: 0"><?= $order['goods_amount'] ?></span></font></span>
                                <?php if (!empty($order['fx_user_id'])): ?>
                                    <span style="display: block;margin-bottom: 5px;position: relative;"><font face="楷体" size="3">优惠金额: <span style="position: absolute;right: 0"><?= $order['fx_money'] ?></span></font></span>
                                <?php endif;?>
                                <?php if (!empty($order['discount'])): ?>
                                    <span style="display: block;margin: 5px 0;position: relative;"><font face="楷体" size="3">到店优惠: <span style="position: absolute;right: 0"><?= $order['discount'] ?></span></font></span>
                                <?php endif;?>
                                <span style="display: block;margin-bottom: 5px;position: relative;"><font face="楷体" size="3">支付总额: <span style="position: absolute;right: 0"><?= $order['order_amount'] ?></span></font></span>
                                <?php if (!empty($order['seller_msg'])): ?>
                                    <span style="display: block;margin-bottom: 5px;position: relative;"><font face="楷体" size="3">用户留言: <span style="position: absolute;right: 0"><?= $order['seller_msg'] ?></span></font></span>
                                <?php endif;?>
                                <span style="display: block;margin-bottom: 5px"><font face="楷体" size="3">客户签字: </font></span>
                                 <span style="display: block;overflow: hidden;border: 1px dashed #000000;height: 1px;margin: 6px 0"></span>
                                <span style="display: block;margin-bottom: 5px;position: relative;"><font face="楷体" size="3">配送属性: <span style="position: absolute;right: 0"><?= $order['format_delivery_type'] ?></span></font></span>
                                <?php if($order['sendout'] == 1): ?>
                                    <span style="display: block;margin-bottom: 5px;position: relative;"><font face="楷体" size="3">自提时间: <span style="position: absolute;right: 0"><?= date('H:i', $order['sendout_time']) ?></span></font></span>
                                <?php endif;?>
                                <?php if($order['sendout'] == 2): ?>
                                    <span style="display: block;margin-bottom: 25px;position: relative;"><font face="楷体" size="3">配送信息: <span style="position: absolute;right: 0"><b><?= $order['format_delivery']['phone'] ?></b></span></font></span>
                                    <span style="display: block;margin-bottom: 55px;position: relative;" ><font face="楷体" size="3"><span style="position: absolute;right: 0"><b><?= $order['format_delivery']['address'] ?>1栋3441室</b></span></font></span>
                                <?php endif;?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
         </table>
    </textarea>
    <textarea id="textarea2" style="display: none" js-print-id2="">
        <table style="margin-left: 5px;margin-top: -25px;">
            <?= $i = 1; ?>
             <?php foreach ($print_data as $order): ?>
                <?php foreach ($order['goods_list'] as $goods): ?>
                    <?php for($k=0;$k<$goods['goods_num'];$k++){ ?>
                        <tr>
                            <td style="width:100%">
                                <div id="js-content2" style="width:100%;height:90px;overflow: hidden;position:relative;font-size:10px;">
                                    <span style="display: block;width:100%;margin-bottom: 2px;position: relative;">
                                        <font face="黑体"><?= $order['format_delivery_type']; ?> <?= $order['format_phone']; ?><span style="position: absolute;right: 3"></span>
                                        </font>
                                    </span>
                                    <span class="storeN" style="display: block;width:100%;position: relative;height: 25px"><span face="黑体" style=""><?= $order['store_name']; ?></span><span class="storeP" style="position: absolute;right:0;top: 10px;"><?= $order['store_mobile']; ?></span></span>
                                    <div style="clear:both;width:130px;display:block">
                                        <span style="overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2"><font face="黑体" style="overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2"><?= $goods['goods_name']; ?></font></span>
                                    </div>
                                    <div style="width:100%">
                                        <span style="width:100%;overflow: hidden;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2"><font face="黑体" style="overflow:hidden;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2"><?= $goods['spec_key_name']; ?></font></span>
                                    </div>
                                    <div style="position:absolute;top:70px;width:100%">
                                        <div style="width:100%;display: block;text-align:right;width:100%;><font face="黑体" ><span style="background-color:#FFF;"> ￥ <?= $goods['goods_pay_price']; ?></span></font></div>
                                        <div style="width:100%;display: block;margin-top: 0px;"><span style="width:30%"><?= $goods['current_num']; ?>/<?= $order['total_num']; ?></span><span style="width:70%;text-align:right"><?= date('Y-m-d H:i',$order['add_time']) ?></span></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                  <?php } ?>
               <?php endforeach; ?>
            <?php endforeach; ?>
        </table>
    </textarea>
</div>
<script src="https://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
<script src="http://www.lmeri.com/assets/plugin/lodop/LodopFuncs.js"></script><script language="javascript" type="text/javascript">
    var LODOP; //声明为全局变量

    //计算打印控件个数
    function printerShow() {
        LODOP=getLodop();
        var printNum = LODOP.GET_PRINTER_COUNT();
        if (!printNum){
            return false;
        }
        for (var i=0;i < printNum;i++){
            if( LODOP.GET_PRINTER_NAME(i) == 'FK-80x Printer'){
                $('#textarea1').attr('js-print-id1', i);
            } else if(LODOP.GET_PRINTER_NAME(i) == 'Fujitsu LPK140'){
                $('#textarea2').attr('js-print-id2', i);
            }
        }
    };

    function PrintByPrinterIndex() {
        var intPrinterIndex = $('#textarea1').attr('js-print-id1');
        CreatePrintPage();
        if (LODOP.SET_PRINTER_INDEX(intPrinterIndex))
            LODOP.PRINT();
    };

    function CreatePrintPage() {
        LODOP = getLodop();
        LODOP.PRINT_INIT("复坤打印机");
        LODOP.ADD_PRINT_HTM(10,55,"100%","100%",document.getElementById("textarea1").value);
    };

    function PrintByPrinterIndex_biaoqian() {
        var intPrinterIndex = $('#textarea2').attr('js-print-id2');
        CreatePrintPage_biaoqian();
        if (LODOP.SET_PRINTER_INDEX(intPrinterIndex))
            LODOP.PRINT();
    };

    function CreatePrintPage_biaoqian() {
        LODOP = getLodop();
        LODOP.PRINT_INIT("富士通打印机");
        LODOP.ADD_PRINT_HTM(0,0,"100%","100%",document.getElementById("textarea2").value);
    };
</script>

