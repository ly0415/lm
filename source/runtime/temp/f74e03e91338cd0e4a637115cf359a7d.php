<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:91:"D:\wamp\www\lmeriPro\newLmeri\web/../source/application/store\view\shop\order\orderList.php";i:1564143685;s:78:"D:\wamp\www\lmeriPro\newLmeri\source\application\store\view\layouts\layout.php";i:1564143685;}*/ ?>
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
                <div class="widget-head am-cf">
                    <div class="widget-title a m-cf">交班报表11</div>
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
                                            <?php if (checkPrivilege('shop.order/excelOut')): ?>
                                                <a class="j-export am-btn am-btn-success am-radius"
                                                   href="javascript:void(0);">
                                                    <i class="iconfont icon-daochu am-margin-right-xs"></i>报表导出
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-u-sm-12 am-u-md-9">
                                <div class="am" style="display:flex;justify-content:flex-end;">
                                    <?php if(IS_ADMIN):?>
                                    <div class="am-form-group am-fl">
                                        <?php $storeId = $request->get('store_id') ? $request->get('store_id') : 58; ?>
                                        <select name="store_id"
                                                data-am-selected="{btnSize: 'sm', placeholder: '选择店铺'}">
                                            <option value=""></option>
                                            <?php if($stores):foreach ($stores as $item):?>
                                                <option value="<?=$item['id']?>" <?=$storeId == $item['id'] ? 'selected' : ''?>>
                     <?=$item['store_name']?>                           </option>
                                            <?php endforeach;endif;?>
                                        </select>
                                    </div>
                                    <?php endif;?>
                                    <div class="am-form-group am-fl">
                                        <?php $sendout = $request->get('sendout'); ?>
                                        <select name="sendout"
                                                data-am-selected="{btnSize: 'sm', placeholder: '配送属性'}">
                                            <option value=""></option>
                                            <option value="-1"
                                                <?= $sendout === '-1' ? 'selected' : '' ?>>全部
                                            </option>
                                            <?php if (isset($send)): foreach ($send as $k=> $items): ?>
                                                <option value="<?= $k ?>"
                                                    <?= $k == $sendout ? 'selected' : '' ?>><?= $items ?>
                                                </option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>

                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input type="text" name="start_time"
                                               class="am-form-field"
                                               value="<?= $request->get('start_time') ? : date('Y-').'01-01' ?>" placeholder="请选择起始日期"
                                               data-am-datepicker>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input type="text" name="end_time"
                                               class="am-form-field"
                                               value="<?= $request->get('end_time') ? : date('Y-').'12-31' ?>" placeholder="请选择截止日期"
                                               data-am-datepicker>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <div class="am-input-group-btn">
                                                <button class="am-btn am-btn-default am-icon-search"
                                                        type="submit"></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>订单编号</th>
                                <th>所属店铺</th>
                                <th>配送属性</th>
                                <th>买家姓名</th>
                                <th>买家手机</th>
                                <th>所属平台</th>
                                <th>支付方式</th>
                                <th>商品市场价</th>
                                <th>付款时间</th>
                                <th>实付金额</th>
                                <th>订单状态</th>
                                <th>订单运费</th>
                                <th>优惠额抵扣</th>
                                <th>分销码抵扣</th>
                                <th>睿积分抵扣</th>
                                <th>优惠劵抵扣</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['order_sn'] ?></td>

                                    <td class="am-text-middle">
                                        <span class="am-badge am-badge-secondary">
                                           <?= $item['store_name'] ?>
                                       </span>
                                    </td>


                                    <td class="am-text-middle"><?= $item['sendoutName'] ?></td>
                                    <td class="am-text-middle"><?= $item['username'] ?></td>
                                    <td class="am-text-middle"><?= $item['phone'] ?></td>
                                    <td class="am-text-middle">
                                           <?= $item['sourceName'] ?>
                                    </td>
                                    <td class="am-text-middle"><?= $item['paymentName'] ?></td>
                                        <td class="am-text-middle"><?= $item['market_price'] ?></td>
                                            <td class="am-text-middle"><?= $item['payment_time'] ?></td>
                                                <td class="am-text-middle">￥<?= $item['order_amount'] ?></td>
                                                    <td class="am-text-middle"><?= $item['statusName'] ?></td>
                                                        <td class="am-text-middle"><?= $item['shipping_fee'] ?></td>
                                                        <td class="am-text-middle"><?= $item['discount'] ?></td>
                                                        <td class="am-text-middle"><?= $item['fx_money'] ?></td>
                                                        <td class="am-text-middle"><?= $item['point_discount'] ?></td>
                                                        <td class="am-text-middle"><?= $item['coupon_discount'] ?>
                                                        </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="9" class="am-text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="am-u-lg-12 am-cf">
                        <div class="am-fr"><?= $list->render() ?> </div>
                        <div class="am-fr pagination-total am-margin-right">
                            <div class="am-vertical-align-middle">总记录：<?= $list->total() ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {

        // 删除元素
        var url = "<?= url('shop.clerk/delete') ?>";
        $('.item-delete').delete('clerk_id', url, '删除后不可恢复，确定要删除吗？');

        /**
         * 订单导出
         */
        $('.j-export').click(function () {
            var data = {};
            var formData = $('#form-search').serializeArray();
            $.each(formData, function () {
                this.name !== 's' && (data[this.name] = this.value);
            });
            // console.log(data);return false;
            window.location = "<?= url('shop.order/excelOut') ?>" + '&' + $.urlEncode(data);
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
