<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:82:"D:\wamp\www\lmeriPro\newLmeri\web/../source/application/store\view\index\index.php";i:1564143685;s:78:"D:\wamp\www\lmeriPro\newLmeri\source\application\store\view\layouts\layout.php";i:1564143685;}*/ ?>
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
        <link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/SearchDropDown.css">
<div class="page-home row-content am-cf">
    <!-- 商城统计 -->
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12 am-margin-bottom">
            <div class="widget am-cf">
                <div class="widget-head">
                    <div class="widget-title">
                        店铺统计

                        <div class="am-fr searchstoreBox am-selected-status" id="storeName">
                            <div class="searchstoreBtn">
                                <span class="storeDesc" style="padding:0;margin:0 0 0 10px;height:18px;line-height:18px;">请输入店铺名称</span>
                                <img class="searchstoreImg" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwEAYAAAAHkiXEAAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAAAgY0hSTQAAeiYAAICEAAD6AAAAgOgAAHUwAADqYAAAOpgAABdwnLpRPAAAAAZiS0dEAAAAAAAA+UO7fwAAAAlwSFlzAAAASAAAAEgARslrPgAAAbpJREFUeNrt1a9Pw0AUB/BvCQmqyZpOTy3Zplc3RXZTOMQkAgj/AgZTxCZQCNzMBMkMWLLs6ibZsr8AhVqam0KV7BDlDMlC+NU74PtxPXGv717eewARERERERERERERERHRX+bZ/gExFmMxvrrCAAMMTk6wwgqr7e2fiqf7uq/76zXqqKN+fZ34iZ/4Bwe28rdfgK7oim6aQkFBhWFhgRUU1NOTnMu5nPt+fqh10flvFR3wLV3TNV07Py887lAP9TCOX78Kf3jDegcYIhCBCG5u0EQTzf39Hws0wgijuztZlmVZ3tvLD+0VwHoHGJnKVKaOjhAiRPjw8O0Beuih9/iYhVmYhWbm23t4w5kOMEQqUpFGESJEiKZTVFFFdWfn0xcGCBA8P+czf3dXJjKRyXRqO0/DmQ4w8tFwf++VvJJXOj398oUxYsRnZ649vOFcAYzJYrKYLC4vMcMMs9vbD1/wOutbjVaj1bi4sJ3PJs4WwPjwbngz62Mv9mJvvbadxybO7YBN3t0Njs/6TZzvAOPd3eD4rP9z2sv2sr08POxUOpVO5fg4P/V+TUcTERERERERERERERER/Q8vuom47pvraxEAAAAldEVYdGRhdGU6Y3JlYXRlADIwMTktMDctMjVUMDk6MzA6NTkrMDg6MDA28tQkAAAAJXRFWHRkYXRlOm1vZGlmeQAyMDE5LTA3LTI1VDA5OjMwOjU5KzA4OjAwR69smAAAAEt0RVh0c3ZnOmJhc2UtdXJpAGZpbGU6Ly8vaG9tZS9hZG1pbi9pY29uLWZvbnQvdG1wL2ljb25fY3lwOWg3dXlkbG4vZjExLWNvcHkuc3ZnwPqaUwAAAABJRU5ErkJggg==" alt="">
                            </div>
                            <div class="DropDownbox">
                                <input class="inputBox" style="width:280px;height:35px;padding:7px;margin:10px;border:none;outline:none;border-bottom:1px solid #c2cad8;" type="text">
                                <ul class="searchdownList" style="">
                                    <li>111adwa45</li>
                                    <li>2225154asdad5442</li>
                                    <li>333241424</li>
                                    <li>44451544</li>
                                </ul>
                            </div>
                        </div>

                        <!-- <div class="am-fr searchstore">
                            <div class="am-u-sm-9 am-u-end">
                                <select name="search_store_id" required="" data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'', maxHeight: 400}" style="display: none;"></select>
                                <div style="position: absolute;top:0;" class="am-selected am-dropdown" data-am-dropdown="">
                                    <button type="button" class="am-selected-btn am-btn am-dropdown-toggle am-btn-sm am-btn-default">
                                        <span class="am-selected-status am-fl" id="storeName">请输入店铺名称</span>
                                    </button>
                                    <div class="am-selected-content am-dropdown-content" style="min-width: 200px;">
                                        <h2 class="am-selected-header">
                                            <span class="am-icon-chevron-left">返回</span>
                                        </h2>
                                        <div class="am-selected-search">
                                            <input autocomplete="off" class="am-form-field am-input-sm">
                                        </div>
                                        <ul class="am-selected-list" id="storeNameList" style="max-height: 400px; overflow-y: scroll;">
                                            <?php $searchStoreId = $request->get('search_store_id'); if (isset($storeList)): foreach ($storeList as $value): ?>
                                                <li><?= $value['store_name'] ?></li>
                                            <?php endforeach; endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div> -->

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
<!--                        <div class="widget-outline dis-flex flex-dir-column flex-x-between">-->
<!--                            <div style="color: rgb(102, 102, 102); font-size: 1.2rem;">退款用户数</div>-->
<!--                            <div style="color: rgb(51, 51, 51); font-size: 2.4rem;">   </div>-->
<!--                            <div style="color: rgb(153, 153, 153); font-size: 1.2rem;">-->
<!--                                昨日：  </div>-->
<!--                        </div>-->
                    </div>
<!--                    <div class="am-u-sm-6 am-u-md-6 am-u-lg-3">-->
<!--                        <div class="widget-outline dis-flex flex-y-center">-->
<!--                            <div class="outline-left">-->
<!--                                 <img src="assets/store/img/return.png" alt="">-->
<!--                            </div>-->
<!--                            <div class="outline-right dis-flex flex-dir-column flex-x-between">-->
<!--                                <div style="color: rgb(102, 102, 102); font-size: 1.3rem;">退款订单数</div>-->
<!--                                <div style="color: rgb(51, 51, 51); font-size: 2.4rem;"> </div>-->
<!--                                <div style="color: rgb(153, 153, 153); font-size: 1.2rem;">-->
<!--                                    昨日：</div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                    <div class="am-u-sm-6 am-u-md-6 am-u-lg-3 am-u-end">-->
<!--                        <div class="widget-outline dis-flex flex-dir-column flex-x-between">-->
<!--                            <div style="color: rgb(102, 102, 102); font-size: 1.2rem;">退款总额</div>-->
<!--                            <div style="color: rgb(51, 51, 51); font-size: 2.4rem;">  </div>-->
<!--                            <div style="color: rgb(153, 153, 153); font-size: 1.2rem;">-->
<!--                                昨日：    </div>-->
<!--                        </div>-->
<!--                    </div>-->
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