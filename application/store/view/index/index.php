<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/SearchDropDown.css">
<div class="page-home row-content am-cf">
    <!-- 商城统计 -->
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12 am-margin-bottom">
            <div class="widget am-cf">
                <div class="widget-head">
                    <div class="widget-title">
                        店铺统计
                        <?php if(!T_GENERAL): ?>
                            <div class="am-fr searchstore">
                                <?php
                                    $store_id = $request->param('store_id');
                                    $store_id = empty($store_id) ? SELECT_STORE_ID : $store_id;
                                ?>
                                <select name="search_store_id" data-am-selected="{btnWidth:'300px', btnSize: 'sm', placeholder: '所属门店'}">
                                    <option value=""></option>
                                    <?php if (isset($storeList)): foreach ($storeList as $item): ?>
                                        <option value="<?= $item['id'] ?>"
                                            <?= $item['id'] == $store_id ? 'selected' : '' ?>><?= $item['store_name'] ?>
                                        </option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="widget-body am-cf">
                    <div class="am-u-sm-12 am-u-md-6 am-u-lg-3">
                        <div class="widget-card card__blue am-cf" style="position: relative;">
                            <div class="card-header">商品总量</div>
                            <div class="card-body">
                                <div class="card-value"><?= $data['widget-card']['goods_total'][0]?></div>
                                <div class="card-description">当前上架商品总数量</div>
                            </div>
                            <div style="font-size: 1.2rem;position: absolute;top:12px;right:17px;">
                                <div>上架总量：<?= $data['widget-card']['goods_total'][1]?></div>
                                <div>下架总量：<?= $data['widget-card']['goods_total'][2]?></div>
                            </div>
                        </div>
                    </div>
                    <div class="am-u-sm-12 am-u-md-6 am-u-lg-3">
                        <div class="widget-card card__red am-cf">
                            <div class="card-header">订单总量</div>
                            <div class="card-body">
                                <div class="card-value"><?= $data['widget-card']['order_total'][0] ?></div>
                                <div class="card-description">当前门店订单总数量</div>
                            </div>
                            <div style="font-size: 1.2rem;position: absolute;top:12px;right:17px;">
                                <div>付款总量：<?= $data['widget-card']['order_total'][1]?></div>
                                <div>退款总量：<?= $data['widget-card']['order_total'][2]?></div>
                                <div>取消总量：<?= $data['widget-card']['order_total'][3]?></div>
                                <div>删除总量：<?= $data['widget-card']['order_total'][4]?></div>
                            </div>
                        </div>
                    </div>
                    <div class="am-u-sm-12 am-u-md-6 am-u-lg-3">
                        <div class="widget-card card__violet am-cf">
                            <div class="card-header">充值总额</div>
                            <div class="card-body">
                                <div class="card-value"><?= $data['widget-card']['recharge_total'] ?></div>
                                <div class="card-description">当前用户余额充值总额</div>
                            </div>
                        </div>
                    </div>
                    <div class="am-u-sm-12 am-u-md-6 am-u-lg-3">
                        <div class="widget-card card__primary am-cf">
                            <div class="card-header">总营业额</div>
                            <div class="card-body">
                                <div class="card-value"><?= $data['widget-card']['income_total'] ?></div>
                                <div class="card-description">当前总营业额</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- 实时概况 -->
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12 am-margin-bottom">
            <div class="widget am-cf">
                <div class="widget-head">
                    <div class="widget-title">实时概况</div>
                </div>
                <div class="widget-body am-cf">
                    <div class="am-u-sm-6 am-u-md-6 am-u-lg-3">
                        <div class="widget-outline dis-flex flex-y-center">
                            <div class="outline-left">
                                <img src="data:image/png;base64,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" alt="">
                            </div>
                            <div class="outline-right dis-flex flex-dir-column flex-x-between">
                                <div style="color: rgb(102, 102, 102); font-size: 1.3rem;">营业额(元)</div>
                                <div style="color: rgb(51, 51, 51); font-size: 2.4rem;"><?= $data['widget-outline']['income']['cday'] ?></div>
                                <div style="color: rgb(153, 153, 153); font-size: 1.2rem;">
                                    昨日：<?= $data['widget-outline']['income']['yday'] ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="am-u-sm-6 am-u-md-6 am-u-lg-3">
                        <div class="widget-outline dis-flex flex-dir-column flex-x-between">
                            <div style="color: rgb(102, 102, 102); font-size: 1.2rem;">支付订单数</div>
                            <div style="color: rgb(51, 51, 51); font-size: 2.4rem;"> <?= $data['widget-outline']['order_total']['cday'] ?></div>
                            <div style="color: rgb(153, 153, 153); font-size: 1.2rem;">
                                昨日：<?= $data['widget-outline']['order_total']['yday'] ?></div>
                        </div>
                    </div>
                    <div class="am-u-sm-6 am-u-md-6 am-u-lg-3">
                        <div class="widget-outline dis-flex flex-y-center">
                            <div class="outline-left">
                                <img src="assets/store/img/user.png" alt="">
                            </div>
                            <div class="outline-right dis-flex flex-dir-column flex-x-between">
                                <div style="color: rgb(102, 102, 102); font-size: 1.3rem;">下单用户数</div>
                                <div style="color: rgb(51, 51, 51); font-size: 2.4rem;"><?= $data['widget-outline']['new_user_total']['cday'] ?></div>
                                <div style="color: rgb(153, 153, 153); font-size: 1.2rem;">
                                    昨日：<?= $data['widget-outline']['new_user_total']['yday'] ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="am-u-sm-6 am-u-md-6 am-u-lg-3">
                        <div class="widget-outline dis-flex flex-dir-column flex-x-between">
                            <div style="color: rgb(102, 102, 102); font-size: 1.2rem;">退款用户数</div>
                            <div style="color: rgb(51, 51, 51); font-size: 2.4rem;"><?= $data['widget-outline']['refund_user_num']['cday'] ?></div>
                            <div style="color: rgb(153, 153, 153); font-size: 1.2rem;">
                                    昨日：<?= $data['widget-outline']['refund_user_num']['yday'] ?></div>
                        </div>
                    </div>
                    <div class="am-u-sm-6 am-u-md-6 am-u-lg-3">
                        <div class="widget-outline dis-flex flex-y-center">
                            <div class="outline-left">
                                 <img src="assets/store/img/return.png" alt="">
                            </div>
                            <div class="outline-right dis-flex flex-dir-column flex-x-between">
                                <div style="color: rgb(102, 102, 102); font-size: 1.3rem;">退款总额</div>
                                <div style="color: rgb(51, 51, 51); font-size: 2.4rem;"><?= $data['widget-outline']['refund_order_money']['cday'] ?></div>
                                <div style="color: rgb(153, 153, 153); font-size: 1.2rem;">
                                    昨日：<?= $data['widget-outline']['refund_order_money']['yday'] ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="am-u-sm-6 am-u-md-6 am-u-lg-3 am-u-end">
                        <div class="widget-outline dis-flex flex-dir-column flex-x-between">
                            <div style="color: rgb(102, 102, 102); font-size: 1.2rem;">退款订单数</div>
                            <div style="color: rgb(51, 51, 51); font-size: 2.4rem;"><?= $data['widget-outline']['refund_order_num']['cday'] ?></div>
                            <div style="color: rgb(153, 153, 153); font-size: 1.2rem;">
                                    昨日：<?= $data['widget-outline']['refund_order_num']['yday'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 近七日交易走势 -->
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12 am-margin-bottom">
            <div class="widget am-cf">
                <div class="widget-head">
                    <div class="widget-title">近七日交易走势</div>
                </div>
                <div class="widget-body am-cf">
                    <div id="echarts-trade" class="widget-echarts"></div>
                </div>
            </div>
        </div>
    </div>

</div>
<script src="assets/common/js/echarts.min.js"></script>
<script src="assets/common/js/echarts-walden.js"></script>
<script type="text/javascript">
    //选择切换事件
    $('select[name="search_store_id"]').on('change',function(){
        var store_id    = $(this).find('option:selected').val();
        window.location = "<?= url('index/index','', false) ?>" + '/store_id/' + store_id;
    });

    //点击按钮弹出搜索框和列表
    $('.searchstoreBox').click(function(event){
        $('.DropDownbox').show()
        event.stopPropagation()
    })
    $('.searchdownList>li').hover(function(){
        $(this).css({"background-color":"#e0e0e0"}).siblings().css({"background-color":"white"})
    })
    $('.searchdownList>li').click(function(){
        var searchstoreName=$(this).text()
        $('.storeDesc').text(searchstoreName)
        $('.DropDownbox').hide()
        event.stopPropagation()
    })
    // 输入框键盘keyup事件
    $('.inputBox').keyup(function(){
        
    })
    $('.page-home').click(function(){
        $('.DropDownbox').hide()
    })

    /**
     * 近七日交易走势
     * @type {HTMLElement}
     */
    var dom = document.getElementById('echarts-trade');
    echarts.init(dom, 'walden').setOption({
        tooltip: {
            trigger: 'axis'
        },
        legend: {
            data: ['成交量', '成交额']
        },
        toolbox: {
            show: true,
            showTitle: false,
            feature: {
                mark: {show: true},
                magicType: {show: true, type: ['line', 'bar']}
            }
        },
        calculable: true,
        xAxis: {
            type: 'category',
            boundaryGap: false,
            data: <?= $data['widget-echarts']['date'] ?>
        },
        yAxis: {
            type: 'value'
        },
        series: [
            {
                name: '成交额',
                type: 'line',
                data: <?= $data['widget-echarts']['order_total_price'] ?>
            },
            {
                name: '成交量',
                type: 'line',
                data: <?= $data['widget-echarts']['order_total'] ?>
            }
        ]
    }, true);
</script>