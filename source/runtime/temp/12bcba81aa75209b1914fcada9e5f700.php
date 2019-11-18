<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:77:"D:\phpStudy\WWW\lmeriPro\web/../source/application/store\view\order\index.php";i:1571624105;s:73:"D:\phpStudy\WWW\lmeriPro\source\application\store\view\layouts\layout.php";i:1571624106;}*/ ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <title>艾美睿零售</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="renderer" content="webkit"/>
    <meta http-equiv="Cache-Control" content="no-siteapp"/>
    <link rel="icon" type="image/png" href="assets/common/i/favicon.ico"/>
    <meta name="apple-mobile-web-app-title" content="艾美睿零售"/>
    <link rel="stylesheet" href="assets/common/css/amazeui.min.css"/>
    <link rel="stylesheet" href="assets/store/css/app.css?v=<?= $version ?>"/>
    <link rel="stylesheet" href="//at.alicdn.com/t/font_783249_fc0v7ysdt1k.css">
    <link rel="stylesheet" href="//at.alicdn.com/t/font_1282474_9pkaszims0j.css">
    <link rel="stylesheet" href="assets/store/css/tips.css">
    <script src="assets/common/js/jquery.min.js"></script>
    <script src="//at.alicdn.com/t/font_783249_e5yrsf08rap.js"></script>
    <script>
        BASE_URL = '<?= isset($base_url) ? $base_url : '' ?>';
        STORE_URL = '<?= isset($store_url) ? $store_url : '' ?>';
        MOUDEL = '<?=$request->controller()?>';
    </script>
</head>

<body data-type="">
<!--自动打印部分-->
<div id="automatic-print" style="display: none"></div>

<div class="am-g tpl-g">
    <!-- 头部 -->
    <header class="tpl-header">
        <!-- 右侧内容 -->
        <div class="tpl-header-fluid">
            <!-- 侧边切换 -->
            <div class="am-fl tpl-header-button switch-button">
                <i class="iconfont icon-menufold"></i>
            </div>
            <!-- 刷新页面 -->
            <div class="am-fl tpl-header-button refresh-button">
                <i class="iconfont icon-refresh"></i>
            </div>
            <!-- 其它功能-->
            <div class="am-fr tpl-header-navbar">
                <ul>
                    <!-- 欢迎语 -->
                    <li class="am-text-sm tpl-header-navbar-welcome">
                        <a href="<?= url('store.user/renew') ?>">欢迎你，<span><?= $store['user']['user_name'] ?></span>
                        </a>
                    </li>
                    <!-- 消息提示 -->
                    <li class="am-text-sm newsBox">
                        <a href="javascript:void(0)" class="lingdan">
                            <i class="iconfont icon-lingdang"></i>
                            <span class="tipsnum"><?= $tips_data['2'] ?></span>
                        </a>
                        <ul class="newsLists">
                            <li class="newsitem">
                                <a class="" href="<?= url('order/index',['tips'=>1]) ?>">
                                    <div style="position:relative;">
                                        <i class="iconfont icon-daifahuo"></i>&nbsp;&nbsp;待接单&nbsp;&nbsp;
                                        <span class="itemnum"><?= $tips_data['0'] ?>个</span>
                                    </div>
                                </a>
                            </li>
                            <li class="newsitem">
                                <a class="" href="<?= url('order/index',['tips'=>2]) ?>">
                                    <div style="position:relative;">
                                        <i class="iconfont icon-kucun"></i>&nbsp;&nbsp;待退款&nbsp;&nbsp;
                                        <span class="itemnum"><?= $tips_data['1'] ?>个</span>
                                    </div>
                                </a>
                            </li>
<!--                            <li class="newsitem">-->
<!--                                <a class="" href="">-->
<!--                                    <div style="position:relative;">-->
<!--                                        <i class="iconfont icon-pinglun"></i>&nbsp;&nbsp;新评论&nbsp;&nbsp;-->
<!--                                        <span class="itemnum">0个</span>-->
<!--                                    </div>-->
<!--                                </a>-->
<!--                            </li>-->
<!--                            <li class="newsitem">-->
<!--                                <a class="" href="">-->
<!--                                    <div style="position:relative;">-->
<!--                                        <i class="iconfont icon-money"></i>&nbsp;&nbsp;申请提现&nbsp;&nbsp;-->
<!--                                        <span class="itemnum">0个</span>-->
<!--                                    </div>-->
<!--                                </a>-->
<!--                            </li>-->
                        </ul>
                    </li>
                    <!-- 退出 -->
                    <li class="am-text-sm">
                        <a href="<?= url('passport/logout') ?>">
                            <i class="iconfont icon-tuichu"></i> 退出
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </header>
    <!-- 侧边导航栏 -->
    <div class="left-sidebar dis-flex">
        <?php $menus = $menus ?: []; $group = $group ?: 0; ?>
        <!-- 一级菜单 -->
        <ul class="sidebar-nav">
            <li class="sidebar-nav-heading">艾美睿零售</li>
            <?php foreach ($menus as $key => $item): ?>
                <li class="sidebar-nav-link">
                    <a href="<?= isset($item['index']) ? url($item['index']) : 'javascript:void(0);' ?>"
                       class="<?= $item['active'] ? 'active' : '' ?>">
                        <?php if (isset($item['is_svg']) && $item['is_svg'] == true): ?>
                            <svg class="icon sidebar-nav-link-logo" aria-hidden="true">
                                <use xlink:href="#<?= $item['icon'] ?>"></use>
                            </svg>
                        <?php else: ?>
                            <i class="iconfont sidebar-nav-link-logo <?= $item['icon'] ?>"
                               style="<?= isset($item['color']) ? "color:{$item['color']};" : '' ?>"></i>
                        <?php endif; ?>
                        <?= $item['name'] ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <!-- 子级菜单-->
        <?php $second = isset($menus[$group]['submenu']) ? $menus[$group]['submenu'] : []; if (!empty($second)) : ?>
            <ul class="left-sidebar-second">
                <li class="sidebar-second-title"><?= $menus[$group]['name'] ?></li>
                <li class="sidebar-second-item">
                    <?php foreach ($second as $item) : if (!isset($item['submenu'])): ?>
                            <!-- 二级菜单-->
                            <a href="<?= url($item['index']) ?>"
                               class="<?= (isset($item['active']) && $item['active']) ? 'active' : '' ?>">
                                <?= $item['name']; ?>
                            </a>
                        <?php else: ?>
                            <!-- 三级菜单-->
                            <div class="sidebar-third-item">
                                <a href="javascript:void(0);"
                                   class="sidebar-nav-sub-title <?= $item['active'] ? 'active' : '' ?>">
                                    <i class="iconfont icon-caret"></i>
                                    <?= $item['name']; ?>
                                </a>
                                <ul class="sidebar-third-nav-sub">
                                    <?php foreach ($item['submenu'] as $third) : ?>
                                        <li>
                                            <a class="<?= $third['active'] ? 'active' : '' ?>"
                                               href="<?= url($third['index']) ?>">
                                                <?= $third['name']; ?></a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; endforeach; ?>
                </li>
            </ul>
        <?php endif; ?>
    </div>

    <!-- 内容区域 start -->
    <div class="tpl-content-wrapper <?= empty($second) ? 'no-sidebar-second' : '' ?>">
        <div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">全部订单列表</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form id="form-search" class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-3">
                                <div class="am-form-group">
                                    <div class="am-btn-toolbar">
                                        <div class="am-btn-group am-btn-group-xs">
<!--                                            --><?php //if (checkPrivilege('order.operate/export')): ?>
<!--                                                <a class="j-export am-btn am-btn-success am-radius"-->
<!--                                                   href="javascript:void(0);">-->
<!--                                                    <i class="iconfont icon-daochu am-margin-right-xs"></i>订单导出-->
<!--                                                </a>-->
<!--                                            --><?php //endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-u-sm-12 am-u-md-9">
                                <div class="am fr">
                                    <?php if(!T_GENERAL): ?>
                                        <div class="am-form-group am-fl">
                                            <?php
                                                $searchStoreId = $request->get('search_store_id');
                                                $searchStoreId = empty($searchStoreId) ? SELECT_STORE_ID : $searchStoreId;
                                            ?>
                                            <select name="search_store_id" data-am-selected="{btnSize: 'sm',btnWidth:150, placeholder: '所属门店'}">
                                                <option value=""></option>
                                                <?php if (isset($storeList)): foreach ($storeList as $item): ?>
                                                    <option value="<?= $item['id'] ?>"
                                                        <?= $item['id'] == $searchStoreId ? 'selected' : '' ?>><?= $item['store_name'] ?>
                                                    </option>
                                                <?php endforeach; endif; ?>
                                            </select>
                                        </div>
                                    <?php endif; ?>
                                    <div class="am-form-group am-fl">
                                        <?php $order_state = $request->param('order_state'); ?>
                                        <select name="order_state" data-am-selected="{btnSize: 'sm',btnWidth:100, placeholder: '订单状态'}">
                                            <option value="-1"
                                                <?= $order_state === '-1' ? 'selected' : '' ?>>全部
                                            </option>
                                            <?php foreach ($orderState as $key => $item): ?>
                                                <option value="<?= $key ?>"
                                                    <?= isset($order_state) && $key == $order_state ? 'selected' : '' ?>><?= $item ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <?php $searchDeliveryType = $request->get('delivery_type'); ?>
                                        <select name="delivery_type" data-am-selected="{btnSize: 'sm',btnWidth:100, placeholder: '配送方式'}">
                                            <option value=""></option>
                                            <option value="-1"
                                                <?= $searchDeliveryType === '-1' ? 'selected' : '' ?>>全部
                                            </option>
                                            <?php foreach ($deliveryType as $key => $item): ?>
                                                <option value="<?= $key ?>"
                                                    <?= $key == $searchDeliveryType ? 'selected' : '' ?>><?= $item ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input type="text" name="start_time" class="am-form-field" style="width: 150px" value="<?= $request->get('start_time') ?>" placeholder="请选择起始日期" data-am-datepicker autocomplete="off">
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input type="text" name="end_time" class="am-form-field" style="width: 150px" value="<?= $request->get('end_time') ?>" placeholder="请选择截止日期" data-am-datepicker autocomplete="off">
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl" style="width: 150px">
                                        <input type="text" class="am-form-field" name="phone" placeholder="请输入手机号" value="<?= $request->get('phone') ?>">
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form" style="width: 200px">
                                            <input type="text" class="am-form-field" name="order_sn" placeholder="请输入订单号" value="<?= $request->get('order_sn') ?>">
                                            <div class="am-input-group-btn">
                                                <button class="am-btn am-btn-default am-icon-search" type="submit"></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="order-list am-scrollable-horizontal am-u-sm-12 am-margin-top-xs">
                        <table width="100%" class="am-table am-table-centered
                        am-text-nowrap am-margin-bottom-xs">
                            <thead>
                            <tr>
                                <th width="25%" class="goods-detail">商品信息</th>
                                <th width="8%">单价/数量</th>
                                <th width="8%">实付款</th>
                                <th width="15%">买家</th>
                                <th>支付方式</th>
                                <th>配送方式</th>
                                <th>订单状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $colspan = 8; if (!$orderList->isEmpty()): foreach ($orderList as $order): ?>
                                <tr class="order-empty">
                                    <td colspan="<?= $colspan ?>"></td>
                                </tr>
                                <tr>
                                    <td class="am-text-middle am-text-left" colspan="<?= $colspan ?>">
                                        <span class="am-margin-right-lg"> <?= $order['format_add_time'] ?></span>
                                        <span class="am-margin-right-lg">订单号：<?= $order['order_sn'] ?></span>
                                        <span class="am-margin-right-lg"><b><?= $order['number_order'] ?></b></span>
                                        <div class="am-fr tpl-table-black-operation" style="display:flex;">
                                            <?php if (checkPrivilege('order/appoint') && $order['format_fx_user'] == TRUE): ?>
                                                <a href="javascript:void(0);" data-store="<?=$order['store_id']?>" class="j-appoint" data-buyer="<?=$order['buyer_id']?>">指定分销</a>
                                            <?php endif;if (checkPrivilege('order/state') && $order['order_state'] == 20): ?>
                                                <a href="javascript:void(0);" data-sn="<?=$order['order_sn']?>" data-store="<?=$order['store_id']?>" class="j-receive">接单</a>
                                            <?php endif;if (checkPrivilege('order/order_print') && $order['order_state'] >= 20): ?>
                                                <a href="<?= url('order/order_print', ['order_sn' => $order['order_sn']]) ?>">票据打印</a>
                                            <?php endif;if (checkPrivilege('order.tag/index')): ?>
                                                <a href="<?= url('order.tag/add', ['order_sn' => $order['order_sn']]) ?>">顾客画像</a>
                                            <?php endif;?>
                                        </div>
                                    </td>
                                </tr>
                                <?php $i = 0;
                                foreach ($order['goods'] as $goods): $i++; ?>
                                    <tr>
                                        <td class="goods-detail am-text-middle">
                                            <div class="goods-image">
                                                <img src="<?= DOMAIN_NAME.$goods['goods_image'] ?>" alt="">
                                            </div>
                                            <div class="goods-info">
                                                <p class="goods-title"><?= $goods['goods_name'] ?></p>
                                                <p class="goods-spec am-link-muted"><?= $goods['spec_key_name'] ?></p>
                                            </div>
                                        </td>
                                        <td class="am-text-middle">
                                            <p>￥<?= $goods['goods_price'] ?></p>
                                            <p>×<?= $goods['goods_num'] ?></p>
                                        </td>
                                        <?php if ($i === 1) : $goodsCount = count($order['goods']); ?>
                                            <td class="am-text-middle" rowspan="<?= $goodsCount ?>">
                                                <p>￥<?= $order['order_amount'] ?></p>
<!--                                                <p class="am-link-muted">(含运费：￥--><?php echo '<?'; ?>
//= $order['express_price'] ?><!--)</p>-->
                                            </td>
                                            <td class="am-text-middle" rowspan="<?= $goodsCount ?>">
                                                <p><?= $order['username'] ?></p>
                                                <p class="am-link-muted">( 手机号：<?= $order['format_phone'] ?> )</p>
                                            </td>
                                            <td class="am-text-middle" rowspan="<?= $goodsCount ?>">
                                                <span class="am-badge am-badge-<?php if($order['order_state'] == 0 || $order['order_state'] == 10): ?>default
                                                    <?php else: ?>secondary
                                                    <?php endif; ?>
                                                ">
                                                    <?= $order['format_payment_type'] ?>
                                                </span>
                                            </td>
                                            <td class="am-text-middle" rowspan="<?= $goodsCount ?>">
                                                <p>
                                                    <span class="am-badge am-badge-<?php if($order['sendout'] == 1): ?>secondary
                                                    <?php else: ?>warning
                                                    <?php endif; ?>"><?= $order['format_delivery_type'] ?></span>
                                                </p>
                                                <p class="am-link-muted">
                                                    <?php if($order['sendout'] == 1): ?>
                                                        ( 自取时间：<?= $order['format_sendout_time']; ?> )
                                                    <?php endif; ?>
                                                </p>
                                            </td>
                                            <td class="am-text-middle" rowspan="<?= $goodsCount ?>">
                                                <p>
                                                    <span class="am-badge am-badge-<?php if($order['order_state'] == 0): ?>default
                                                    <?php elseif($order['order_state'] == 10): ?>warning
                                                    <?php elseif($order['order_state'] == 25): ?>primary
                                                    <?php elseif($order['order_state'] == 60): ?>danger
                                                    <?php else: ?>success
                                                    <?php endif; ?>">
                                                        <?= $order['format_order_state'] ?>
                                                    </span>
                                                </p>
                                            </td>
                                            <td class="am-text-middle" rowspan="<?= $goodsCount ?>">
                                                <div class="tpl-table-black-operation">
                                                    <?php if (checkPrivilege('order/detail')): ?>
                                                        <a class="tpl-table-black-operation-green" href="<?= url('order/detail', ['order_sn' => $order['order_sn']]) ?>">订单详情</a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; endforeach; else: ?>
                                <tr>
                                    <td colspan="<?= $colspan ?>" class="am-text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="am-u-lg-12 am-cf">
                        <div class="am-fr"><?= $orderList->render() ?> </div>
                        <div class="am-fr pagination-total am-margin-right">
                            <div class="am-vertical-align-middle">总记录：<?= $orderList->total() ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 退款审核模板 -->
<script id="tpl-appoint" type="text/template">
    <div class="am-padding-xs am-padding-top-sm">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="j-tabs am-tabs">
                <div class="am-tabs-bd am-padding-xs">
                    <div class="am-tab-panel am-padding-0 am-active" id="tab1">
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                状态
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <label class="am-radio-inline">
                                    <input type="radio" name="refund[status]" value="1" data-am-ucheck checked> 同意
                                </label>
                                <label class="am-radio-inline">
                                    <input type="radio" name="refund[status]" value="2" data-am-ucheck> 驳回
                                </label>
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                备注
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <textarea rows="2" name="refund[remark]" placeholder="请输入备注（驳回必填）" class="am-field-valid"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</script>
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script>
    $(function () {
        /**
         * 订单导出
         */
        $('.j-export').click(function () {
            var data = {};
            var formData = $('#form-search').serializeArray();
            $.each(formData, function () {
                this.name !== 's' && (data[this.name] = this.value);
            });
            window.location = "<?= url('order.operate/export') ?>" + '&' + $.urlEncode(data);
        });

        //指定分销
        $('.j-appoint').click(function () {
            var buyer_id = $(this).attr('data-buyer');
            $.selectData({
                title: '分销人员',
                uri: "<?= url('data.distribution/lists') ?>",
                done: function (data) {
                    $.post("<?= url('order/appoint') ?>", {id:data,buyer_id:buyer_id}, function (result) {
                        result.code === 1 ? $.show_success(result.msg, result.url) : $.show_error(result.msg);
                    });
                }
            });
        });

        //接单
        $('.j-receive').click(function () {
            var data = $(this).data();
            layer.confirm('您确定要接单吗？'
                , {title: '友情提示'}
                , function (index) {
                    $.post("<?= url('order/state') ?>"
                        , {
                            order_sn: data.sn,
                            store_id: data.store,
                        }
                        , function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        });
                    layer.close(index);
                });

        });
    });

</script>
    </div>
    <!-- 内容区域 end -->

</div>
<script src="assets/common/plugins/layer/layer.js"></script>
<script src="assets/common/js/jquery.form.min.js"></script>
<script src="assets/common/js/amazeui.min.js"></script>
<script src="assets/common/js/webuploader.html5only.js"></script>
<script src="assets/common/js/art-template.js"></script>
<script src="assets/store/js/app.js?v=<?= $version ?>"></script>
<script src="assets/store/js/file.library.js?v=<?= $version ?>"></script>
<?php if($tipsAuth): ?>
    <script src="http://www.lmeri.com/assets/plugin/lodop/LodopFuncs.js"></script>
<?php endif; ?>
<script>
   $('.newsBox').hover(
       function(){
           $(this).css('background','#eaeaea');
       },
       function(){
        $(this).css('background','#fff'); 
       }
   )

   var tipsAuth     = "<?= $tipsAuth[0]; ?>";
   var tipsAuthUser = "<?= $tipsAuth[1]; ?>";
   if( tipsAuth && tipsAuthUser){
       setInterval(function(){
           if( LODOP.VERSION ){
               //页面轮询---查询订单
               var url = "<?=url('order/get_notips_order');?>";
               $.post(url,{} ,function (res) {
                   if(res.code == 1){
                       var _window = window.open('http://www.lmeri.com/web/assets/orderTips/orderTips.php','_blank','width=230,height=100,toolbar=no,menubar=no,scrollbars=no, resizable=no,location=no, status=no');
                       _window.moveTo(10000,10000);
                       $('#automatic-print').html(res.data);
                       setTimeout(function(){
                           printerShow();
                           PrintByPrinterIndex();
                           PrintByPrinterIndex_biaoqian();
                       }, 1000);
                   }
               }, 'json');
           }
       }, 10000);
   }

   $('.newsitem').hover(
       function(){
          $(this).css('background','#ececec'); 
       },
       function(){
        $(this).css('background','#fff');  
       }
   )

    $('.newsBox').on('click',function(e){
        e.stopPropagation();
        $('.newsLists').toggle();
        var tag = $('.newsLists');
        var flag = true;
        $(document).bind('click',function(e){
            var target = $(e.target);
            if(target.closest(tag).length==0&&flag==true){
                tag.hide();
                flag=false;
            }
        })
    })
</script>
</body>

</html>
