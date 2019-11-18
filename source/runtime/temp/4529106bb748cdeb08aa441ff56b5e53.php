<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:96:"D:\wamp\www\lmeriPro\newLmeri\web/../source/application/store\view\store\store_console\index.php";i:1564143684;s:78:"D:\wamp\www\lmeriPro\newLmeri\source\application\store\view\layouts\layout.php";i:1564143685;s:95:"D:\wamp\www\lmeriPro\newLmeri\source\application\store\view\layouts\_template\tpl_file_item.php";i:1557114576;s:94:"D:\wamp\www\lmeriPro\newLmeri\source\application\store\view\layouts\_template\file_library.php";i:1564143685;}*/ ?>
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
        <link rel="stylesheet" href="assets/admin/css/set_attribute.css">
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div  data-am-tabs class="am-tabs widget am-cf">
<!--                <div class="widget-head am-cf">-->
<!--                    <div class="widget-title a m-cf">控制管理</div>-->
<!--                </div>-->
                <ul class="am-tabs-nav am-nav am-nav-tabs">
                    <li class="am-active"><a href="javascript: void(0)">控制管理</a></li>
                    <li><a href="javascript: void(0)">文章领劵</a></li>
                </ul>
                <div class="am-tabs-bd">
                <div class="am-tab-panel am-active am-in">
                    <div class="widget am-cf">
                    <form action="" class="am-form tpl-form-line-form" method="post">
                        <div class="am-form-group">
                            <div style="font-size: 1.5rem;">领券中心</div>
                            <div class="am-margin-top">
                                <div class="am-u-sm-3 am-text-right form-require am-form-label">注册新用户</div>
                                    <div class="am-u-sm-9 am-form-label am-text-left" id="recharge">
                                            <input type="text" name="relation_1" id="day" onblur="setConsole(this.name)" value="<?php if(isset($list[1]) && $list[1]['relation_1'])echo $list[1]['relation_1'];?>">天内可领取&nbsp;&nbsp;满
                                        <input type="text" name="money" onblur="setCoupon(this.name)" value="<?php if(isset($list[1]['coupon']) && $list[1]['coupon']['money'])echo $list[1]['coupon']['money'];?>"/>元减
                                            <input type="text" name="discount"  onblur="setCoupon(this.name)" value="<?php if(isset($list[1]['coupon']) && $list[1]['coupon']['discount'])echo $list[1]['coupon']['discount'];?>"/>元抵扣券（&nbsp;券名称：
                                            <input type="text" name="coupon_name" onblur="setCoupon(this.name)" value="<?php if(isset($list[1]['coupon']) && $list[1]['coupon']['coupon_name'])echo $list[1]['coupon']['coupon_name']?>"/>&nbsp;）&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            <label class="am-radio-inline">
                                                <input type="radio" onclick="setConsole(this.name)" name="status" <?php if(isset($list[1]) && $list[1]['status'] == 1)echo 'checked';?> value="1" data-am-ucheck checked required>开启
                                            </label>
                                            <label class="am-radio-inline">
                                                <input type="radio" onclick="setConsole(this.name)" name="status" <?php if(isset($list[1]) && $list[1]['status'] == 2)echo 'checked';?> value="2" data-am-ucheck >关闭
                                            </label>
                                    </div>
                            </div>
                        </div>
                        <hr/>


<!--                        门店余额支付-->
                        <div class="am-form-group">
                            <div style="font-size: 1.5rem;">门店支付开关设置</div>
                            <div class="am-margin-top">
                                <div class="am-u-sm-3 am-text-right form-require am-form-label">选择关闭余额支付的店铺</div>
                                <div class="am-u-sm-6 am-u-end">
                                    <div class="select-content am-form-group">
                                        <input type="hidden" value="" name="newMachineId" id="newMachineId1">
                                        <input type="text" name="select_input1"  id="select_input1" class="select-input" value="" autocomplete="off" placeholder="请输入要关闭的店铺" />
                                        <div id="search_select1" class="search-select">
                                            <ul id="select_ul1" data-type="3" class="select-ul">
                                            </ul>
                                        </div>
                                    </div>
                                    <ul class='shopsContent' data-type="3" id="shopsContent1">
                                        <?php if($list && isset($list[3]['relation_1'])):foreach ($list[3]['relation_1'] as $store_3):?>
                                        <li data-id="<?=$store_3['id']?>"><?=$store_3['store_name']?><i class="iconfont icon-shanchu1 insideI"></i></li>
                                <?php endforeach;endif;?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <hr/>
                        <div class="am-form-group">
                            <div style="font-size: 1.5rem;">客服功能设置</div>
                            <div class="am-margin-top">
                                <div class="am-u-sm-3 am-text-right form-require am-form-label">客服开关</div>
                                <div class="am-u-sm-6 am-u-end">
                                    <div class="select-content am-form-group">
                                            <label class="am-radio-inline">
                                                <input type="radio" data-status="<?php if(isset($list[4])){echo $list[4]['status'];}?>" onclick="setKuFu(this.name)" name="kufu" <?php if(isset($list[4]) && $list[4]['status'] == 1)echo 'checked';?> value="1" data-am-ucheck checked required>开启
                                            </label>
                                            <label class="am-radio-inline">
                                                <input type="radio" data-status="<?php if(isset($list[4])){echo $list[4]['status'];}?>" onclick="setKuFu(this.name)" name="kufu" <?php if(isset($list[4]) && $list[4]['status'] == 2)echo 'checked';?> value="2" data-am-ucheck >关闭
                                            </label>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>

                    </form>
                    </div>
                </div>

<!--            文章领取优惠券        -->
                    <div class="am-tab-panel">
                        <div class="widget am-cf">
                            <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">按钮名称 </label>
                                            <div class="am-u-sm-8 am-u-end">
                                                <input type="text" class="tpl-form-input"  placeholder="按钮名称" name="coupon[name]"
                                                       value="<?=$list1['relation_1']['name']?>" required>    
                                            </div>
                                        </div>
                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">抵扣卷 </label>
                                            <div class="am-u-sm-9 am-u-end">
                                                <select name="coupon[coupon_id]"
                                                        data-am-selected="{searchBox: 1, btnSize: 'sm'}">
                                                    <option value="0">选择抵扣卷</option>
                                                    <?php if (isset($coupon)): foreach ($coupon as $first): ?>
                                                        <option value="<?= $first['id'] ?>" <?=$list1['relation_1']['coupon_id'] == $first['id'] ? 'selected':''?>>
                                                            <?= $first['coupon_name'] ?></option>
                                                    <?php endforeach; endif; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">按钮颜色 </label>
                                            <div class="am-u-sm-9 am-u-end">
                                                <input type="color" class="tpl-form-input" placeholder="按钮颜色" name="coupon[color]"
                                                       value="<?=$list1['relation_1']['color']?>" required>
                                            </div>
                                        </div>
                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">是否开启 </label>
                                            <div class="am-u-sm-9 am-u-end">
                                                <label class="am-radio-inline">
                                                    <input type="radio" name="coupon[status]" <?=$list1['status'] == 1 ? 'checked' : ''?> value="1" data-am-ucheck checked>
                                                    是
                                                </label>
                                                <label class="am-radio-inline">
                                                    <input type="radio" name="coupon[status]" <?=$list1['status'] == 2 ? 'checked' : ''?> value="2" data-am-ucheck>
                                                    <span class="am-link-muted">否</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">背景图片 </label>
                                            <div class="am-u-sm-9 am-u-end">
                                                <div class="am-form-file">
                                                    <button type="button"
                                                            class="upload-file am-btn am-btn-secondary am-radius">
                                                        <i class="am-icon-cloud-upload"></i> 选择图片
                                                    </button>
                                                    <div class="uploader-list am-cf">
                                                        <?php if($list && isset($list1['image'])):?>

                                                            <div class="file-item">
                                                                <a href="<?=$list1['big_file_path']?>" title="点击查看大图" target="_blank">
                                                                    <img src="<?=$list1['small_file_path']?>">
                                                                </a>
                                                                <input type="hidden" name="coupon[relation_2]" value="<?=$list1['relation_2']?>">
                                                                <i class="iconfont icon-shanchu file-item-delete"></i>
                                                            </div>
                                                        <?php endif;?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">领券链接</label>
                                            <input type="hidden" value="<?php if(isset($list1['relation_1']['url']) && !empty($list1['relation_1']['url']))echo $list1['relation_1']['url'];else{echo 'wx.php?app=article&act=couponList';}?>" name="coupon[url]">
                                            <label class="am-u-sm-2 am-form-label am-text-left"><?php if(isset($list1['relation_1']['url']) && !empty($list1['relation_1']['url']))echo $list1['relation_1']['url'];else{echo 'wx.php?app=article&act=couponList';}?></label>
                                            <div class="am-u-sm-3 am-u-end">
                                                <button type="button" class="am-btn-xs am-btn am-btn-secondary">复制</button>
                                            </div> 
                                        </div>
                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">小程序码</label>
                                             <div class="am-u-sm-9 am-u-end">
                                                 <a href="<?php if(isset($list1['relation_1']['qrcode']) && !empty($list1['relation_1']['qrcode']))echo $list1['relation_1']['qrcode'];else{echo 'upload/1563530596.png';}?>"  target="_blank">
                                                  <img class="qrCode" src="<?php if(isset($list1['relation_1']['qrcode']) && !empty($list1['relation_1']['qrcode']))echo $list1['relation_1']['qrcode'];else{echo 'upload/1563530596.png';}?>" alt="" style="width:100px;height:100px;">
                                                 </a>
                                                 <input type="hidden" value="<?php if(isset($list1['relation_1']['qrcode']) && !empty($list1['relation_1']['qrcode']))echo $list1['relation_1']['qrcode'];else{echo 'upload/1563530596.png';}?>" name="coupon[qrcode]">
                                             </div>
                                        </div>
                                        <div class="am-form-group">
                                            <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                                <button type="submit" class="j-submit am-btn am-btn-secondary">提交
                                                </button>
                                            </div>
                                        </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<!-- 图片文件列表模板 -->
<script id="tpl-file-item" type="text/template">
    {{ each list }}
    <div class="file-item">
        <a href="{{ $value.file_path }}" title="点击查看大图" target="_blank">
            <img src="{{ $value.file_path }}">
        </a>
        <input type="hidden" name="{{ name }}" value="{{ $value.file_id }}">
        <i class="iconfont icon-shanchu file-item-delete"></i>
    </div>
    {{ /each }}
</script>




<!-- 文件库弹窗 -->
<!-- 文件库模板 -->
<script id="tpl-file-library" type="text/template">
    <div class="row">
        <div class="file-group">
            <ul class="nav-new">
                <li class="ng-scope {{ is_default ? 'active' : '' }}" data-group-id="-1">
                    <a class="group-name am-text-truncate" href="javascript:void(0);" title="全部">全部</a>
                </li>
                <li class="ng-scope" data-group-id="0">
                    <a class="group-name am-text-truncate" href="javascript:void(0);" title="未分组">未分组</a>
                </li>
                {{ each group_list }}
                <li class="ng-scope"
                    data-group-id="{{ $value.group_id }}" title="{{ $value.group_name }}">
                    <a class="group-edit" href="javascript:void(0);" title="编辑分组">
                        <i class="iconfont icon-bianji"></i>
                    </a>
                    <a class="group-name am-text-truncate" href="javascript:void(0);">
                        {{ $value.group_name }}
                    </a>
                    <a class="group-delete" href="javascript:void(0);" title="删除分组">
                        <i class="iconfont icon-shanchu1"></i>
                    </a>
                </li>
                {{ /each }}
            </ul>
            <a class="group-add" href="javascript:void(0);">新增分组</a>
        </div>
        <div class="file-list">
            <div class="v-box-header am-cf">
                <div class="h-left am-fl am-cf">
                    <div class="am-fl">
                        <div class="group-select am-dropdown">
                            <button type="button" class="am-btn am-btn-sm am-btn-secondary am-dropdown-toggle">
                                移动至 <span class="am-icon-caret-down"></span>
                            </button>
                            <ul class="group-list am-dropdown-content">
                                <li class="am-dropdown-header">请选择分组</li>
                                {{ each group_list }}
                                <li>
                                    <a class="move-file-group" data-group-id="{{ $value.group_id }}"
                                       href="javascript:void(0);">{{ $value.group_name }}</a>
                                </li>
                                {{ /each }}
                            </ul>
                        </div>
                    </div>
                    <div class="am-fl tpl-table-black-operation">
                        <a href="javascript:void(0);" class="file-delete tpl-table-black-operation-del"
                           data-group-id="2">
                            <i class="am-icon-trash"></i> 删除
                        </a>
                    </div>
                </div>
                <div class="h-rigth am-fr">
                    <div class="j-upload upload-image">
                        <i class="iconfont icon-add1"></i>
                        上传图片
                    </div>
                </div>
            </div>
            <div id="file-list-body" class="v-box-body">
                {{ include 'tpl-file-list' file_list }}
            </div>
            <div class="v-box-footer am-cf"></div>
        </div>
    </div>

</script>

<!-- 文件列表模板 -->
<script id="tpl-file-list" type="text/template">
    <ul class="file-list-item">
        {{ include 'tpl-file-list-item' data }}
    </ul>
    {{ if last_page > 1 }}
    <div class="file-page-box am-fr">
        <ul class="pagination">
            {{ if current_page > 1 }}
            <li>
                <a class="switch-page" href="javascript:void(0);" title="上一页" data-page="{{ current_page - 1 }}">«</a>
            </li>
            {{ /if }}
            {{ if current_page < last_page }}
            <li>
                <a class="switch-page" href="javascript:void(0);" title="下一页" data-page="{{ current_page + 1 }}">»</a>
            </li>
            {{ /if }}
        </ul>
    </div>
    {{ /if }}
</script>

<!-- 文件列表模板 -->
<script id="tpl-file-list-item" type="text/template">
    {{ each $data }}
    <li class="ng-scope" title="{{ $value.file_name }}" data-file-id="{{ $value.file_name }}"
        data-file-path="{{ $value.file_path }}">
        <div class="img-cover"
             style="background-image: url('{{ $value.file_path }}')">
        </div>
        <p class="file-name am-text-center am-text-truncate">{{ $value.file_name }}</p>
        <div class="select-mask">
            <img src="assets/store/img/chose.png">
        </div>
    </li>
    {{ /each }}
</script>

<!-- 分组元素-->
<script id="tpl-group-item" type="text/template">
    <li class="ng-scope" data-group-id="{{ group_id }}" title="{{ group_name }}">
        <a class="group-edit" href="javascript:void(0);" title="编辑分组">
            <i class="iconfont icon-bianji"></i>
        </a>
        <a class="group-name am-text-truncate" href="javascript:void(0);">
            {{ group_name }}
        </a>
        <a class="group-delete" href="javascript:void(0);" title="删除分组">
            <i class="iconfont icon-shanchu1"></i>
        </a>
    </li>
</script>


<script>
    $(function () {

        // 选择图片
        $('.upload-file').selectImages({
            name: 'coupon[relation_2]'
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
<script>


    tempArr = <?=json_encode($storeList)?>;
    // var tempArr = [
    //     {shopName: '艾美睿®小站--浙江衢州柯城道前街店',shopId: '001'},
    //     {shopName: '艾美睿小站-衢州柯城下街店',shopId: '002'},
    //     {shopName: '艾美睿®生活--浙江衢州工厂店',shopId: '003'},
    //     {shopName: '艾美睿®生活--浙江衢州柯城上街店',shopId: '010'},
    //     {shopName: '艾美睿®书吧--浙江衢州柯城万达三楼',shopId: '011'},
    //     {shopName: '艾美睿®小站--浙江衢州东港海力大道店',shopId: '010'}
    // ];

 function setConsole(tag) {
     var val = $("input[name='" + tag +"']").val();
     if(tag == 'status'){
         var val = $("input[name='" + tag +"']:checked").val();
     }
     if(!val)return false;
     var data = {console:{val:val,key:tag}};
     var url = "<?=url('store.store_console/setConsole')?>";
     $.post(url,data,function (res) {
         layer.msg(res.msg)
     },'JSON')
 }

    function setKuFu(tag) {
        var val = $("input[name='" + tag +"']:checked").val();
        if(!val)return false;
        var data = {kufu:{status:val}};
        var url = "<?=url('store.store_console/setCustomer ')?>";
        $.post(url,data,function (res) {
            layer.msg(res.msg)
        },'JSON')
    }

 function setCoupon(tag) {
     var val = $("input[name='" + tag + "']").val();
     if(!val)return false;
     var data = {'console':{val:val,key:tag}};
     var url = "<?=url('store.store_console/setCoupon')?>";
     $.post(url,data,function (res) {
         layer.msg(res.msg)
     },'JSON')
 }


for(var i=0;i<2;i++){
    searchInput(tempArr,i);
    $('#shopsContent'+i).on('click','.insideI',function(){
        var _this = $(this);
        var url = "<?=url('store.store_console/delCloseStore')?>";
        var param = {store_id:_this.parent().attr('data-id'),type:_this.parent().parent().attr('data-type')};
        // console.log(param);return false;
        layer.confirm('确定要删除吗？', {title: '友情提示'}
            , function (index) {
                $.post(url, param, function (res) {
                    res.code === 1 ? _this.parent().remove():layer.msg(res.msg);
                });
                layer.close(index);
            }
        );
})
}


function newOptions(tempArr,index){
    //遍历数据，判断输入框的文本内容在数组中是否存在，存在则将该数组元素push到新数组中
    var listArr = [];
    tempArr.forEach(function(v,i){
        if(v.store_name.indexOf($('#select_input'+index).val())>-1){
            listArr.push(v);
        }
    })
    //遍历新数组，将数组中的元素以dom的形式插入ul标签里
    var options = '';
    listArr.forEach(function(val,idx){
        var opt = '<li class="li-select" data-newMachineId="' + val.id + '">' + val.store_name + '</li>';
        options +=opt;
    })

    //判断列表中有无数据，没有则隐藏ui列表，有就显示列表，并且将options加入到列表
    // console.log(options);return false;
    if(options == ''){
        $('#search_select'+index).hide();
    }else{
        $('#search_select'+index).show();
        $('#select_ul'+index).html(options);
    }
}

function searchInput(tempArr,index){
    //鼠标按下触发方法
    $('#select_input'+index).on('keyup',function(){
        newOptions(tempArr,index);
    });
    //input框获取焦点触发（鼠标点击）：下拉框显示，调用newOptions方法
    $('#select_input'+index).on('focus',function(){
        $('#search_select'+index).show();
        newOptions(tempArr,index);
    });


    $('#select_ul'+index).delegate('.li-select', 'click',function(){
        var _this = $(this);
        var id = $($(this)[0]).attr("data-newMachineId");
        var type = $(this).parent().attr('data-type');
        $.post("<?=url('store.store_console/addCloseStore')?>",{store_id:id,type:type},function (res){
            if(res.code == 1){
                // console.log(id);
                $('#select_ul'+index+' .li-select').removeClass('li-hover');
                var selectText = _this.html();
                $('#select_input'+index).val(selectText);
                var html = '<li data-id="' + id + '">'+ $('#select_input'+index).val()+' <i class="iconfont icon-shanchu1 insideI"></i></li>';
                $('#shopsContent'+index).append(html);
                $('#search_select'+index).hide();
                $("#newMachineId"+index).val(id);
            }
        },'JSON');

    });

    //鼠标移入移出事件（选择框）
    $('#search_select'+index).on('mouseover',function(){
        $(this).addClass('ul-hover');
    });
    $('#search_select'+index).on('mouseout',function(){
        $(this).removeClass('ul-hover');
    });
    //input框失去焦点
    $('#select_input'+index).on('blur',function(){
        if($('#search_select'+index).hasClass('ul-hover')){
            $('#search_select'+index).show();
        }else{
            $('#search_select'+index).hide();
        }
    });

    $('#select_ul'+index).delegate('.li-select', 'mouseover',function(){
        $('#select_ul'+index+' .li-select').removeClass('li-hover');
        $(this).addClass('li-hover');
    });

}





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
