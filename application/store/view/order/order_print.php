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
                                    <span style="display: block;margin-bottom: 5px;position: relative;"><font face="楷体" size="3">优惠金额: <span style="position: absolute;right: 0"><?= $order['fx_money'] ?></span></font>
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
                                    <span style="display: block;margin-bottom: 5px;position: relative;"><font face="楷体" size="3">自提时间: <span style="position: absolute;right: 0"><?= date('H:i', $order['sendout_time']);  ?></span></font></span>
                                    <span style="display: block;margin-bottom: 5px;position: relative;"><font face="楷体" size="3">堂食桌号: <span style="position: absolute;right: 0">
                                        <?php if (!empty($order['format_table_desc']['table_num'])): ?>
                                            <?= $order['format_table_desc']['table_num'];  ?>
                                        <?php else:?>
                                            无
                                        <?php endif;?>
                                    </span></font></span>
                                    <span style="display: block;margin-bottom: 5px;position: relative;"><font face="楷体" size="3">堂食人数: <span style="position: absolute;right: 0">
                                         <?php if (!empty($order['format_table_desc']['table_number'])): ?>
                                             <?= $order['format_table_desc']['table_number'];  ?>
                                         <?php else:?>
                                             无
                                         <?php endif;?>
                                    </span></font></span>
                                <?php endif;?>
                                <?php if($order['sendout'] == 2): ?>
                                    <span style="display: block;margin-bottom: 25px;position: relative;"><font face="楷体" size="3">配送信息: <span style="position: absolute;right: 0"><b><?= $order['format_delivery']['phone'] ?></b></span></font></span>
                                    <span style="display: block;margin-bottom: 55px;position: relative;" ><font face="楷体" size="3"><span style="position: absolute;right: 0"><b><?= $order['format_delivery']['address'] ?></b></span></font></span>
                                <?php endif;?>
                                 <span style="display: block;margin: 30px 0;position: relative;">
                                     <img style="position:absolute;width: 80%;left: 10%;right: 10%" src="<?=url('api/QRcode/getOrderCode','',false)?>/order_sn/<?= $order['order_sn'] ?>">
                                 </span>
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
                                <div id="js-content2" style="width:100%;height:90px;overflow: hidden;position:relative;font-size:10px">
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
<?php
    $print_data = $print_data[0];
?>
<div class="row-content am-cf">
    <div class="row">
        <div style="margin-bottom:30px;">
            <span class="am-u-md-2">票据打印</span>
            <span class="am-u-md-8 am-md-text-right ">
                <button class="am-btn am-round am-btn-xs am-btn-success" style="outline:none" onclick="PrintByPrinterIndex();">打印小票</button>
                <button class="am-btn am-round am-btn-xs am-btn-success" style="outline:none" onclick="PrintByPrinterIndex_biaoqian();javascript :history.back(-1);">打印标签</button>
                <button class="am-btn am-round am-btn-xs am-btn-primary" onclick="PrintByPrinterIndex();PrintByPrinterIndex_biaoqian();javascript :history.back(-1);">一并打印</button>
            </span>
        </div>
        <table class="am-table">
            <thead style="margin-top:60px;" class="am-u-md-12">
                <tr class="am-u-md-12">
                    <th class="am-u-md-4 am-text-center" style="border:none;">小票预览</th>
                    <th class="am-u-md-4 am-u-end am-text-center" style="border:none;">标签预览</th>
                </tr>
            </thead>
            <tbody class="am-u-md-12">
                <tr class="am-u-md-12">
                    <td class="am-margin-top am-u-md-4" style="background-color:white;" rowspan="8">
                        <ul>
                            <li class="am-margin-top am-u-md-12" style="display:flex">
                                <span class="am-fl am-u-md-4">订 单 号：</span>
                                <div class="am-fr am-u-md-8 am-md-text-right" style="font-weight: bold"><?= $print_data['order_sn'] ?></div>
                            </li>
                            <li class="am-margin-top am-u-md-12" style="display:flex">
                                <span class="am-fl am-u-md-4">取 货 码：</span>
                                <div class="am-fr am-u-md-8 am-md-text-right" style="font-weight: bold"><?= $print_data['number_order'] ?></div>
                            </li>
                            <li class="am-margin-top am-u-md-12" style="display:flex">
                                <span class="am-fl am-u-md-4">订单商品：</span>
                                <div class="am-fr am-u-md-8 am-md-text-right"></div>
                            </li>
                            <?php foreach ($print_data['goods_list'] as $goods): ?>
                                <li class="am-margin-top am-u-md-12">
                                    <div class="am-margin-top am-u-md-12 am-u-end" style="font-weight: bold"><?= $goods['goods_name'] ?>&nbsp;&nbsp;(<?= $goods['spec_key_name'] ?>)</div>
                                    <div class="am-margin-top am-u-md-12 am-md-text-right" style="font-weight: bold">X <?= $goods['goods_num'] ?></div>
                                    <div class="am-margin-top am-u-md-12 am-u-end am-md-text-right" style="color:red;font-weight: bold"><?= $goods['goods_pay_price'] ?></div>
                                </li>
                            <?php endforeach; ?>
                            <li class="am-margin-top am-u-md-12" style="display:flex">
                                <span class="am-fl am-u-md-4">订单状态：</span>
                                <div class="am-fr am-u-md-8 am-md-text-right"><?= $print_data['format_order_state'] ?></div>
                            </li>
                            <li class="am-margin-top am-u-md-12" style="display:flex">
                                <span class="am-fl am-u-md-4">支付方式：</span>
                                <div class="am-fr am-u-md-8 am-md-text-right" style="font-weight: bold"><?= $print_data['format_payment_type'] ?></div>
                            </li>
                            <li class="am-margin-top am-u-md-12" style="display:flex">
                                <span class="am-fl am-u-md-4">用 户 名：</span>
                                <div class="am-fr am-u-md-8 am-md-text-right" style="font-weight: bold"><?= $print_data['username'] ?></div>
                            </li>
                            <li class="am-margin-top am-u-md-12" style="display:flex">
                                <span class="am-fl am-u-md-4">用户电话：</span>
                                <div class="am-fr am-u-md-8 am-md-text-right" style="font-weight: bold"><?= $print_data['format_phone'] ?></div>
                            </li>
                            <li class="am-margin-top am-u-md-12" style="display:flex">
                                <span class="am-fl am-u-md-4">商品总额：</span>
                                <div class="am-fr am-u-md-8 am-md-text-right" style="color:red;font-weight: bold">￥<?= $print_data['goods_amount'] ?></div>
                            </li>
                            <?php if (!empty($print_data['fx_user_id'])): ?>
                                <li class="am-margin-top am-u-md-12" style="display:flex">
                                    <span class="am-fl am-u-md-4">优惠金额：</span>
                                    <div class="am-fr am-u-md-8 am-md-text-right" style="color:red;font-weight: bold">￥<?= $print_data['fx_money'] ?></div>
                                </li>
                            <?php endif;?>
                            <?php if($order['sendout'] == 2): ?>
                                <li class="am-margin-top am-u-md-12" style="display:flex">
                                    <span class="am-fl am-u-md-4">配送费：</span>
                                    <div class="am-fr am-u-md-8 am-md-text-right"><?= $print_data['shipping_fee'] ?></div>
                                </li>
                            <?php endif;?>
                            <?php if($order['discount'] != 0): ?>
                                <li class="am-margin-top am-u-md-12" style="display:flex">
                                    <span class="am-fl am-u-md-4">到店优惠：</span>
                                    <div class="am-fr am-u-md-8 am-md-text-right"><?= $order['discount'] ?></div>
                                </li>
                            <?php endif;?>
                            <li class="am-margin-top am-u-md-12" style="display:flex">
                                <span class="am-fl am-u-md-4">支付总额：</span>
                                <div class="am-fr am-u-md-8 am-md-text-right" style="color:red;font-weight: bold">￥<?= $print_data['order_amount'] ?></div>
                            </li>
                            <?php if (!empty($print_data['seller_msg'])): ?>
                                <li class="am-margin-top am-u-md-12" style="display:flex">
                                    <span class="am-fl am-u-md-4">用户留言：</span>
                                    <div class="am-fr am-u-md-8 am-md-text-right" style="color:red;font-weight: bold">￥<?= $print_data['seller_msg'] ?></div>
                                </li>
                            <?php endif;?>
                            <li class="am-margin-top am-u-md-12" style="display:flex">
                                <span class="am-fl am-u-md-4">客户签字：</span>
                                <div class="am-fr am-u-md-8 am-md-text-right"></div>
                            </li>
                            <li class="am-margin-top am-u-md-12" style="display:flex">
                                <span class="am-fl am-u-md-4">配送属性：</span>
                                <div class="am-fr am-u-md-8 am-md-text-right"><?= $print_data['format_delivery_type'] ?></div>
                            </li>
                            <?php if($order['sendout'] == 1): ?>
                                <li class="am-margin-top am-u-md-12" style="display:flex">
                                    <span class="am-fl am-u-md-4">自提时间：</span>
                                    <div class="am-fr am-u-md-8 am-md-text-right"><?= date('H:i', $order['sendout_time']);  ?></div>
                                </li>
                                <li class="am-margin-top am-u-md-12" style="display:flex">
                                    <span class="am-fl am-u-md-4">堂食桌号：</span>
                                    <div class="am-fr am-u-md-8 am-md-text-right">
                                        <?php if (!empty($order['format_table_desc']['table_num'])): ?>
                                            <?= $order['format_table_desc']['table_num'];  ?>
                                        <?php else:?>
                                            无
                                        <?php endif;?>
                                    </div>
                                </li>
                                <li class="am-margin-top am-u-md-12" style="display:flex">
                                    <span class="am-fl am-u-md-4">堂食人数：</span>
                                    <div class="am-fr am-u-md-8 am-md-text-right">
                                        <?php if (!empty($order['format_table_desc']['table_number'])): ?>
                                            <?= $order['format_table_desc']['table_number'];  ?>
                                        <?php else:?>
                                            无
                                        <?php endif;?>
                                    </div>
                                </li>
                            <?php endif;?>
                            <?php if($order['sendout'] == 2): ?>
                                <li class="am-margin-top am-u-md-12" style="display:flex">
                                    <span class="am-fl am-u-md-4">配送信息: </span>
                                    <div class="am-fr am-u-md-8 am-md-text-right"><?= $order['format_delivery']['phone'] ?></div>
                                </li>
                                <li class="am-margin-top am-u-md-12" style="display:flex">
                                    <span class="am-fl am-u-md-4"></span>
                                    <div class="am-fr am-u-md-8 am-md-text-right"><?= $order['format_delivery']['address'] ?></div>
                                </li>
                            <?php endif;?>
                            <li class="am-margin-top am-u-md-12" style="display:flex;justify-content: center">
                                <img src="<?=url('api/QRcode/getOrderCode','',false)?>/order_sn/<?= $order['order_sn'] ?>">
                            </li>
                        </ul>
                    </td>
                    <td rowspan="8" class="am-margin-top am-u-md-4 am-u-end" style="background-color:white;margin-left:10px;">
                        <ul>
                            <?php foreach ($print_data['goods_list'] as $goods): ?>
                                <li class="am-u-md-12">
                                    <div class="am-margin-top am-u-md-12"><?= $print_data['format_delivery_type'] ?> <?= $print_data['format_phone'] ?></div>
                                    <div class="am-margin-top am-u-md-12"><?= $print_data['store_name'] ?> <?= $print_data['store_mobile'] ?></div>
                                    <div class="am-margin-top am-u-md-12" style="font-weight: bold"><?= $goods['goods_name'] ?></div>
                                    <div class="am-margin-top am-u-md-12" style="font-weight: bold">  (<?= $goods['spec_key_name'] ?>)</div>
                                    <div class="am-fr am-margin-top am-u-md-12 am-md-text-right" style="font-weight: bold"><?= $goods['goods_pay_price'] ?></div>
                                    <div class="am-margin-top am-u-md-12">
                                        <span><?= $goods['current_num'] ?>/<?= $print_data['total_num'] ?></span>
                                        <span class="am-fr am-md-text-right"><?= date('Y-m-d H:i',$print_data['add_time']) ?></span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<script src="http://www.lmeri.com/assets/plugin/lodop/LodopFuncs.js"></script>
<script language="javascript" type="text/javascript">
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

    $(window).load(function(){
        printerShow();
    })

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
        // $("body").mLoading({
        //     text:'票据打印中，请稍等...'
        // });
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
        // $("body").mLoading({
        //     text:'票据打印中，请稍等...'
        // });
    };
</script>

