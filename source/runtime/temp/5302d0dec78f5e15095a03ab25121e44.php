<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:83:"D:\wamp\www\lmeriPro\newLmeri\web/../source/application/store\view\order\detail.php";i:1564319037;s:78:"D:\wamp\www\lmeriPro\newLmeri\source\application\store\view\layouts\layout.php";i:1564143685;}*/ ?>
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
    <script src="assets/common/js/jquery.min.js"></script>
    <script src="//at.alicdn.com/t/font_783249_e5yrsf08rap.js"></script>
    <script>
        BASE_URL = '<?= isset($base_url) ? $base_url : '' ?>';
        STORE_URL = '<?= isset($store_url) ? $store_url : '' ?>';
    </script>
</head>

<body data-type="">
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
                    <li class="am-text-sm newsBox" style="position:relative;">
                        <a href="javascript:void(0)" class="">
                            <i class="iconfont icon-lingdang"></i>
                            <span style="width:16px;height:16px;text-align:center;line-height:16px;border-radius:8px;background-color:#ff8585;color:#fff;font-size:12px;position:absolute;top:8px;right:8px;">60</span> 
                        </a>
                        <ul class="newsLists" style="position: absolute;top:38px;left:-40px;width:150px;border:1px solid #ccc;background-color:#fff;display:none;">
                            <li class="newsitem" style="width:100%;">
                                <a class="" href="">
                                    <div style="position:relative;">
                                        <i class="iconfont icon-daifahuo"></i>&nbsp;&nbsp;待发货&nbsp;&nbsp;
                                        <span class="" id=" " style="position:absolute;right:0;top:0;">0个</span>
                                    </div>
                                </a>
                            </li>
                            <li class="newsitem" style="width:100%;">
                                <a class="" href="">
                                    <div style="position:relative;">
                                        <i class="iconfont icon-kucun"></i>&nbsp;&nbsp;库存预警&nbsp;&nbsp;
                                        <span class=" " id=" " style="position:absolute;right:0;top:0;">99个</span>
                                    </div>
                                </a>
                            </li>
                            <li class="newsitem" style="width:100%;">
                                <a class="" href="">
                                    <div style="position:relative;">
                                        <i class="iconfont icon-pinglun"></i>&nbsp;&nbsp;新评论&nbsp;&nbsp;
                                        <span class=" " id=" " style="position:absolute;right:0;top:0;">0个</span>
                                    </div>
                                </a>
                            </li>
                            <li class="newsitem" style="width:100%;">
                                <a class="" href="">
                                    <div style="position:relative;">
                                        <i class="iconfont icon-money"></i>&nbsp;&nbsp;申请提现&nbsp;&nbsp;
                                        <span class=" " id=" " style="position:absolute;right:0;top:0;">0个</span>
                                    </div>
                                </a>
                            </li>
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
                <div class="widget__order-detail widget-body am-margin-bottom-lg">
                    <!-- 订单进度步骤条 -->
                    <div class="am-u-sm-12">
                        <?php
                            // 计算当前步骤位置
                            $progress = 2;
                            $orderDetail['order_state'] >= 20 && $progress += 1;
                            $orderDetail['order_state'] >= 25 && $progress += 1;
                            $orderDetail['order_state'] >= 50 && $progress += 1;    //自提流程
                            $orderDetail['evaluation_state'] == 1 && $progress += 1;
                        ?>
                        <ul class="order-detail-progress progress-<?= $progress ?>">
                            <li>
                                <span>下单时间</span>
                                <div class="tip"><?= $orderDetail['format_add_time'] ?></div>
                            </li>
                            <li>
                                <span>付款</span>
                                <?php if ($orderDetail['order_state'] >= 20): ?>
                                    <div class="tip">
                                        付款于 <?= $orderDetail['format_payment_time'] ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                            <li>
                                <span>接单</span>
                                <?php if ($orderDetail['order_state'] >= 25): ?>
                                    <div class="tip">
                                        接单于 <?= $orderDetail['format_receive_time'] ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                            <li>
                                <span>收货</span>
                                <?php if ($orderDetail['order_state'] >= 50): ?>
                                    <div class="tip">
                                        收货于 <?= $orderDetail['format_receive_time'] ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                            <li>
                                <span>评价</span>
                                <?php if ($orderDetail['evaluation_state'] == 1): ?>
                                    <div class="tip">
                                        评价于 <?= $orderDetail['format_comment_time'] ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                        </ul>
                    </div>

                    <!-- 基本信息 -->
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">基本信息</div>
                    </div>
                    <div class="am-scrollable-horizontal">
                        <table class="regional-table am-table am-table-bordered am-table-centered
                            am-text-nowrap am-margin-bottom-xs">
                            <tbody>
                            <tr>
                                <th>订单号</th>
                                <th>买家</th>
                                <th>订单金额</th>
                                <th>支付方式</th>
                                <th>配送方式</th>
                                <th>交易状态</th>
                            </tr>
                            <tr>
                                <td><?= $orderDetail['order_sn'] ?></td>
                                <td>
                                    <p><?= $orderDetail['username'] ?></p>
                                    <p class="am-link-muted">(用户id：<?= $orderDetail['buyer_id'] ?>)</p>
                                </td>
                                <td class="">
                                    <div class="td__order-price am-text-left">
                                        <ul class="am-avg-sm-2">
                                            <li class="am-text-right">订单总额：</li>
                                            <li class="am-text-right">￥<?= $orderDetail['goods_amount'] ?> </li>
                                        </ul>
<!--                                        --><?php //if ($detail['coupon_id'] > 0) : ?>
<!--                                            <ul class="am-avg-sm-2">-->
<!--                                                <li class="am-text-right">优惠券抵扣：</li>-->
<!--                                                <li class="am-text-right">- ￥--><?php echo '<?'; ?>
//= $detail['coupon_price'] ?><!--</li>-->
<!--                                            </ul>-->
<!--                                        --><?php //endif; ?>
<!--                                        <ul class="am-avg-sm-2">-->
<!--                                            <li class="am-text-right">运费金额：</li>-->
<!--                                            <li class="am-text-right">+￥--><?php echo '<?'; ?>
//= $detail['express_price'] ?><!--</li>-->
<!--                                        </ul>-->
                                        <ul class="am-avg-sm-2">
                                            <li class="am-text-right">实付款金额：</li>
                                            <li class="x-color-red am-text-right">
                                                ￥<?= $orderDetail['order_amount'] ?></li>
                                        </ul>
                                    </div>
                                </td>
                                <td>
                                    <span class="am-badge am-badge-secondary"><?= $orderDetail['format_payment_type'] ?></span>
                                </td>
                                <td>
                                    <span class="am-badge am-badge-secondary"><?= $orderDetail['format_delivery_type'] ?></span>
                                </td>
                                <td>
                                    <p>订单状态：<span class="am-badge am-badge-warning"><?= $orderDetail['format_order_state'] ?></span></p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- 商品信息 -->
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">商品信息</div>
                    </div>
                    <div class="am-scrollable-horizontal">
                        <table class="regional-table am-table am-table-bordered am-table-centered
                            am-text-nowrap am-margin-bottom-xs">
                            <tbody>
                            <tr>
                                <th>商品名称</th>
                                <th>商品编码</th>
                                <th>单价</th>
                                <th>购买数量</th>
                                <th>商品总价</th>
                            </tr>
                            <?php foreach ($orderDetail['goods'] as $goods): ?>
                                <tr>
                                    <td class="goods-detail am-text-middle">
                                        <div class="goods-image">
                                            <img src="<?= DOMAIN_NAME.$goods['goods_image'] ?>" alt="">
                                        </div>
                                        <div class="goods-info">
                                            <p class="goods-title"><?= $goods['goods_name'] ?></p>
                                            <p class="goods-spec am-link-muted">
                                                <?= $goods['spec_key_name'] ?>
                                            </p>
                                        </div>
                                    </td>
                                    <td>待开发</td>
                                    <td>￥<?= $goods['goods_price'] ?></td>
                                    <td>×<?= $goods['goods_num'] ?></td>
                                    <td>￥<?= ($goods['goods_price']*$goods['goods_num']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="5" class="am-text-right am-cf">
                                    <span class="am-fl">买家留言：待开发</span>
                                    <span class="am-fr">总计金额：￥<?= $orderDetail['goods_amount'] ?></span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- 收货信息 -->
<!--                    --><?php //if ($detail['delivery_type']['value'] == DeliveryTypeEnum::EXPRESS): ?>
<!--                        <div class="widget-head am-cf">-->
<!--                            <div class="widget-title am-fl">收货信息</div>-->
<!--                        </div>-->
<!--                        <div class="am-scrollable-horizontal">-->
<!--                            <table class="regional-table am-table am-table-bordered am-table-centered-->
<!--                            am-text-nowrap am-margin-bottom-xs">-->
<!--                                <tbody>-->
<!--                                <tr>-->
<!--                                    <th>收货人</th>-->
<!--                                    <th>收货电话</th>-->
<!--                                    <th>收货地址</th>-->
<!--                                </tr>-->
<!--                                <tr>-->
<!--                                    <td>--><?php echo '<?'; ?>
//= $detail['address']['name'] ?><!--</td>-->
<!--                                    <td>--><?php echo '<?'; ?>
//= $detail['address']['phone'] ?><!--</td>-->
<!--                                    <td>-->
<!--                                        --><?php echo '<?'; ?>
//= $detail['address']['region']['province'] ?>
<!--                                        --><?php echo '<?'; ?>
//= $detail['address']['region']['city'] ?>
<!--                                        --><?php echo '<?'; ?>
//= $detail['address']['region']['region'] ?>
<!--                                        --><?php echo '<?'; ?>
//= $detail['address']['detail'] ?>
<!--                                    </td>-->
<!--                                </tr>-->
<!--                                </tbody>-->
<!--                            </table>-->
<!--                        </div>-->
<!--                    --><?php //endif; ?>

                    <!-- 自提门店信息 -->
<!--                    --><?php //if ($detail['delivery_type']['value'] == DeliveryTypeEnum::EXTRACT): ?>
<!--                        --><?php //if (!empty($detail['extract'])): ?>
<!--                            <div class="widget-head am-cf">-->
<!--                                <div class="widget-title am-fl">自提信息</div>-->
<!--                            </div>-->
<!--                            <div class="help-block x-f-14 am-padding-left">-->
<!--                                <p class="am-margin-bottom-xs">联系人：--><?php echo '<?'; ?>
//= $detail['extract']['linkman'] ?><!--</p>-->
<!--                                <p>联系电话：--><?php echo '<?'; ?>
//= $detail['extract']['phone'] ?><!--</p>-->
<!--                            </div>-->
<!--                        --><?php //endif; ?>
<!--                        <div class="widget-head am-cf">-->
<!--                            <div class="widget-title am-fl">自提门店信息</div>-->
<!--                        </div>-->
<!--                        <div class="am-scrollable-horizontal">-->
<!--                            <table class="regional-table am-table am-table-bordered am-table-centered-->
<!--                            am-text-nowrap am-margin-bottom-xs">-->
<!--                                <tbody>-->
<!--                                <tr>-->
<!--                                    <th>门店ID</th>-->
<!--                                    <th>门店logo</th>-->
<!--                                    <th>门店名称</th>-->
<!--                                    <th>联系人</th>-->
<!--                                    <th>联系电话</th>-->
<!--                                    <th>门店地址</th>-->
<!--                                </tr>-->
<!--                                <tr>-->
<!--                                    <td>--><?php echo '<?'; ?>
//= $detail['extract_shop']['shop_id'] ?><!--</td>-->
<!--                                    <td>-->
<!--                                        <a href="--><?php echo '<?'; ?>
//= $detail['extract_shop']['logo']['file_path'] ?><!--" title="点击查看大图"-->
<!--                                           target="_blank">-->
<!--                                            <img src="--><?php echo '<?'; ?>
//= $detail['extract_shop']['logo']['file_path'] ?><!--" height="72"-->
<!--                                                 alt="">-->
<!--                                        </a>-->
<!--                                    </td>-->
<!--                                    <td>--><?php echo '<?'; ?>
//= $detail['extract_shop']['shop_name'] ?><!--</td>-->
<!--                                    <td>--><?php echo '<?'; ?>
//= $detail['extract_shop']['linkman'] ?><!--</td>-->
<!--                                    <td>--><?php echo '<?'; ?>
//= $detail['extract_shop']['phone'] ?><!--</td>-->
<!--                                    <td>-->
<!--                                        --><?php echo '<?'; ?>
//= $detail['extract_shop']['region']['province'] ?>
<!--                                        --><?php echo '<?'; ?>
//= $detail['extract_shop']['region']['city'] ?>
<!--                                        --><?php echo '<?'; ?>
//= $detail['extract_shop']['region']['region'] ?>
<!--                                        --><?php echo '<?'; ?>
//= $detail['extract_shop']['address'] ?>
<!--                                    </td>-->
<!--                                </tr>-->
<!--                                </tbody>-->
<!--                            </table>-->
<!--                        </div>-->
<!--                    --><?php //endif; ?>

                    <!-- 付款信息 -->
<!--                    --><?php //if ($detail['pay_status']['value'] == 20): ?>
<!--                        <div class="widget-head am-cf">-->
<!--                            <div class="widget-title am-fl">付款信息</div>-->
<!--                        </div>-->
<!--                        <div class="am-scrollable-horizontal">-->
<!--                            <table class="regional-table am-table am-table-bordered am-table-centered-->
<!--                                am-text-nowrap am-margin-bottom-xs">-->
<!--                                <tbody>-->
<!--                                <tr>-->
<!--                                    <th>应付款金额</th>-->
<!--                                    <th>支付方式</th>-->
<!--                                    <th>支付流水号</th>-->
<!--                                    <th>付款状态</th>-->
<!--                                    <th>付款时间</th>-->
<!--                                </tr>-->
<!--                                <tr>-->
<!--                                    <td>￥--><?php echo '<?'; ?>
//= $detail['pay_price'] ?><!--</td>-->
<!--                                    <td>--><?php echo '<?'; ?>
//= $detail['pay_type']['text'] ?><!--</td>-->
<!--                                    <td>--><?php echo '<?'; ?>
//= $detail['transaction_id'] ?: '--' ?><!--</td>-->
<!--                                    <td>-->
<!--                                        <span class="am-badge-->
<!--                                        --><?php echo '<?'; ?>
//= $detail['pay_status']['value'] == 20 ? 'am-badge-success' : '' ?><!--">-->
<!--                                                --><?php echo '<?'; ?>
//= $detail['pay_status']['text'] ?><!--</span>-->
<!--                                    </td>-->
<!--                                    <td>-->
<!--                                        --><?php echo '<?'; ?>
//= $detail['pay_time'] ? date('Y-m-d H:i:s', $detail['pay_time']) : '--' ?>
<!--                                    </td>-->
<!--                                </tr>-->
<!--                                </tbody>-->
<!--                            </table>-->
<!--                        </div>-->
<!--                    --><?php //endif; ?>

                    <!--  用户取消订单 -->
                    <?php if ($orderDetail['order_state'] == 200000): if (checkPrivilege('order.operate/confirmcancel')): ?>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl"><strong>用户取消订单</strong></div>
                            </div>
                            <div class="tips am-margin-bottom-sm am-u-sm-12">
                                <div class="pre">
                                    <p>当前买家已付款并申请取消订单，请审核是否同意，如同意则自动退回付款金额（微信支付原路退款）并关闭订单。</p>
                                </div>
                            </div>
                            <!-- 去审核 -->
                            <form id="cancel" class="my-form am-form tpl-form-line-form" method="post" action="<?= url('order.operate/confirmcancel', ['order_id' => $detail['order_id']]) ?>">
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">审核状态 </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <div class="am-u-sm-9">
                                            <label class="am-radio-inline">
                                                <input type="radio" name="order[is_cancel]"
                                                       value="1"
                                                       data-am-ucheck
                                                       required>
                                                同意
                                            </label>
                                            <label class="am-radio-inline">
                                                <input type="radio" name="order[is_cancel]"
                                                       value="0"
                                                       data-am-ucheck
                                                       checked>
                                                拒绝
                                            </label>
                                        </div>

                                    </div>
                                </div>
                                <div class="am-form-group">
                                    <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                        <button type="submit" class="j-submit am-btn am-btn-sm am-btn-secondary">
                                            确认审核
                                        </button>

                                    </div>
                                </div>
                            </form>
                        <?php endif; endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

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
<script>
   $('.newsBox').hover(
       function(){
           $(this).css('background','#eaeaea');
       },
       function(){
        $(this).css('background','#fff'); 
       }
       )
//    $('.newsBox').click(function(){
//         if( $('.newsLists').is(':visible')) {
//             $('.newsLists').hide();
//         }else{
//             $('.newsLists').show();
//         }
//    });

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
