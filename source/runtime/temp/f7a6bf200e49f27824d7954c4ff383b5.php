<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:76:"D:\phpStudy\WWW\lmeriPro\web/../source/application/store\view\goods\edit.php";i:1573097703;s:73:"D:\phpStudy\WWW\lmeriPro\source\application\store\view\layouts\layout.php";i:1572400948;s:90:"D:\phpStudy\WWW\lmeriPro\source\application\store\view\layouts\_template\tpl_file_item.php";i:1571624106;s:89:"D:\phpStudy\WWW\lmeriPro\source\application\store\view\layouts\_template\file_library.php";i:1571624106;}*/ ?>
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
        <link rel="stylesheet" href="assets/admin/css/set_attribute.css">
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title">编辑商品</div>
                </div>
                <div id='doc-my-tabs' data-am-tabs class="am-tabs widget am-cf">
                    <ul class="am-tabs-nav am-nav am-nav-tabs">
                        <li class="am-active"><a href="javascript: void(0)">基础信息</a></li>
                        <?php if($details['isExistSpec']): ?>
                            <li><a href="javascript: void(0)">规格信息</a></li>
                        <?php endif;?>
                    </ul>
                    <div class="am-tabs-bd">
                        <div class="am-tab-panel am-active am-in">
                            <div class="widget am-cf">
                                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                                    <input type="hidden" name="type" value="1">
                                    <input type="hidden" name="store_goods_id" value="<?= $details['id'] ?>">
                                    <div class="am-form-group">
                                        <label for="originalStock" class="am-u-sm-2 am-form-label"> 商品名称：</label>
                                        <div class="am-u-sm-8 am-u-end">
                                            <input id="originalStock" autocomplete="off" type="text" class="tpl-form-input" value="<?= $details['goods_name'] ?>" disabled>
                                        </div>
                                    </div>
                                    <div class="am-form-group">
                                        <label for="mySopPrice" class="am-u-sm-2 am-form-label"> 商品分类： </label>
                                        <div class="am-u-sm-8 am-u-end">
                                            <input id="mySopPrice" autocomplete="off" type="text" class="tpl-form-input" value="<?= $details['format_category'][0] ?>" disabled>
                                        </div>
                                    </div>
                                    <div class="am-form-group">
                                        <label for="marketValue" class="am-u-sm-2 am-form-label"> 业务类型： </label>
                                        <div class="am-u-sm-8 am-u-end">
                                            <input id="marketValue" autocomplete="off" type="text" class="tpl-form-input" value="<?= $details['format_business_name'] ?>" disabled>
                                        </div>
                                    </div>
                                    <div class="am-form-group">
                                        <label for="marketValue" class="am-u-sm-2 am-form-label"> 市 场 价：</label>
                                        <div class="am-u-sm-8 am-u-end">
                                            <input id="marketValue" autocomplete="off" type="text" class="tpl-form-input" name="market_price" value="<?= $details['market_price'] ?>" disabled>
                                        </div>
                                    </div>
                                    <div class="am-form-group">
                                        <label for="marketValue" class="am-u-sm-2 am-form-label form-require"> 本店售价： </label>
                                        <div class="am-u-sm-8 am-u-end">
                                            <input id="marketValue" autocomplete="off" type="text" class="tpl-form-input" name="shop_price" value="<?= $details['shop_price'] ?>" placeholder="本店售价" required <?php if($details['deduction'] == 1): ?>disabled<?php endif; ?>>
                                        </div>
                                    </div>
                                    <div class="am-form-group">
                                        <label for="marketValue" class="am-u-sm-2 am-form-label form-require"> 本店库存： </label>
                                        <div class="am-u-sm-8 am-u-end">
                                            <input id="marketValue" autocomplete="off" type="text" class="tpl-form-input" name="goods_storage" value="<?= $details['goods_storage'] ?>" <?php if((T_GENERAL && $details['deduction'] == 1) || $details['isExistSpec']): ?>disabled<?php endif;?> required>
                                            <small>当前商品 库存扣除方式：<a class="am-badge am-badge-<?= $details['deduction'] == 2 ? 'success' : 'warning' ?> am-radius"><?= $details['format_deduction'] ?></a></small>
                                        </div>
                                    </div>
                                    <?php if(!$details['isExistSpec']): ?>
                                        <div class="am-form-group">
                                            <label for="" class="am-u-sm-2 am-form-label"> 条形码： </label>
                                            <div class="am-u-sm-8 am-u-end">
                                                <input id="" autocomplete="off" type="text" class="tpl-form-input codeBox" name="bar_code" value="<?= $details['bar_code'] ?>">
                                                <small>条形码最多由20个数字组成</small>
                                            </div>
                                        </div>
                                    <?php endif;?>
                                    <div class="am-form-group">
                                        <label for="marketValue" class="am-u-sm-2 am-form-label form-require"> 配送属性： </label>
                                        <div class="am-u-sm-8 am-u-end">
                                            <?php if($deliveryType):foreach ($deliveryType as $key => $items):?>
                                                <label class="am-checkbox-inline">
                                                    <input type="checkbox" name="attributes[]" <?php if(in_array($key, $details['format_attributes_arr'])):?>checked<?php endif;?> value="<?= $key ?>" class="am-ucheck-checkbox am-field-valid"><span class="am-ucheck-icons"><i class="am-icon-unchecked"></i><i class="am-icon-checked"></i></span>
                                                    <?= $items ?>
                                                </label>
                                            <?php endforeach;endif;?>
                                        </div>
                                    </div>
                                    <div class="am-form-group">
                                        <label for="marketValue" class="am-u-sm-2 am-form-label form-require"> 排序： </label>
                                        <div class="am-u-sm-8 am-u-end">
                                            <input id="marketValue" autocomplete="off" type="text" class="tpl-form-input" name="sort" value="<?= $details['sort'] ?>" placeholder="本店售价" disabled>
                                        </div>
                                    </div>
                                    <div class="am-form-group">
                                        <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                            <button type="submit" class="j-submit am-btn am-btn-secondary">提交</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="am-tab-panel">
                            <div class="widget am-cf">
                                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                                    <div class="am-form-group"  id="j-spec-table"></div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="am-modal" tabindex="-1" id="doc-modal-1">
                    <div class="am-modal-dialog">
                        <div class="am-modal-hd am-text-left">设置
                            <a href="javascript: void(0)" class="am-close am-close-spin" data-am-modal-close>&times;</a>
                        </div>
                        <div class="am-modal-bd">
                            <div class="specBox">
                                <div class="row">
                                    <div class="specName">配选：</div>
                                    <div class="specVal">
                                        <span data-item="2065" class="activeAttr">不选</span>
                                        <span data-item="2193" class="">加太妃榛果3元</span>
                                        <span data-item="2194" class="">加榛果2元</span>
                                        <span data-item="2195" class="">加香草2元</span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="specName">甜度选择：</div>
                                    <div class="specVal">
                                        <span data-item="2074" class="activeAttr">标准甜度1.0</span>
                                        <span data-item="2075" class="">加甜1.2</span>
                                        <span data-item="2141" class="">少甜0.8</span>
                                        <span data-item="2142" class="">微甜0.6</span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="specName">温度选择：</div>
                                    <div class="specVal">
                                        <span data-item="2144" class="activeAttr">去冰</span>
                                        <span data-item="2146" class="">冰沙</span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="specName">杯选择：</div>
                                    <div class="specVal">
                                        <span data-item="2149" class="activeAttr">常规杯R</span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="specName">价格：</div>
                                    <input type="text" class="am-fl">
                                </div>
                                <div class="row">
                                    <div class="specName">库存：</div>
                                    <input type="text" class="am-fl">
                                </div>
                                <div class="row">
                                    <div class="specName">SKU：</div>
                                    <input type="text" class="am-fl">
                                </div>
                            </div>
                        </div>
                        <div class="am-modal-footer">
                            <span class="am-modal-btn" data-am-modal-cancel>取消</span>
                            <span class="am-modal-btn" data-am-modal-confirm>确定</span>
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
    <li class="ng-scope" title="{{ $value.file_name }}" data-file-id="{{ $value.file_id }}" data-file-name="{{ $value.file_name }}" data-file-big-path="{{ $value.big_file_path }}"
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

        $('#doc-my-tabs').tabs({noSwipe: 1});

        // 选择图片
        $('.upload-file').selectImages({
            name: 'coupon[relation_2]'
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

        $.post("<?= url('Goods/getSpecInput') ?>", {goods_id: <?= $details['id'] ?>}, function (result) {
            $("#j-spec-table").append(result.data);
            hbdyg();  // 合并单元格
        });

        // 合并单元格
        function hbdyg() {
            var tab = document.getElementById("spec_input_tab"); //要合并的tableID
            var maxCol = 2, val, count, start;  //maxCol：合并单元格作用到多少列
            if (tab != null) {
                for (var col = maxCol - 1; col >= 0; col--) {
                    count = 1;
                    val = "";
                    for (var i = 0; i < tab.rows.length; i++) {
                        if (val == tab.rows[i].cells[col].innerHTML) {
                            count++;
                        } else {
                            if (count > 1) { //合并
                                start = i - count;
                                tab.rows[start].cells[col].rowSpan = count;
                                for (var j = start + 1; j < i; j++) {
                                    tab.rows[j].cells[col].style.display = "none";
                                }
                                count = 1;
                            }
                            val = tab.rows[i].cells[col].innerHTML;
                        }
                    }
                    if (count > 1) { //合并，最后几行相同的情况下
                        start = i - count;
                        tab.rows[start].cells[col].rowSpan = count;
                        for (var j = start + 1; j < i; j++) {
                            tab.rows[j].cells[col].style.display = "none";
                        }
                    }
                }
            }
        }

        $(document).on('keyup','.codeBox',function(){
            this.value=this.value.replace(/[^\d]/g,'')
            var inputdata=$(this).val()
            if(inputdata.length>=20){
                layer.msg('条形码最多由20位数字组成')
                inputdata=inputdata.slice(0,20)
                $(this).val(inputdata)
            }
        })

        //更新数据
        $('body').on('change','.j-edit-data1',function(){
            $.post("<?= url('Goods/edit') ?>", {goods_id: <?= $details['id'] ?>, value_data: $(this).val(), spec_key: $(this).attr('j-item-key'), type: 2, tp: 1}, function (result) {

            });
        });
        $('body').on('change','.j-edit-data2',function(){
            $.post("<?= url('Goods/edit') ?>", {goods_id: <?= $details['id'] ?>, value_data: $(this).val(), spec_key: $(this).attr('j-item-key'), type: 2, tp: 2}, function (result) {

            });
        });
        $('body').on('change','.j-edit-data3',function(){
            $.post("<?= url('Goods/edit') ?>", {goods_id: <?= $details['id'] ?>, value_data: $(this).val(), spec_key: $(this).attr('j-item-key'), type: 2, tp: 3}, function (result) {

            });
        });

        $('body').on('change','.j-edit-data4',function(){
            $.post("<?= url('Goods/edit') ?>", {goods_id: <?= $details['id'] ?>, value_data: $(this).val(), spec_key: $(this).attr('j-item-key'), type: 2, tp: 4}, function (result) {

            });
        });

        // 选择弹框中的规格
        $(document).on('click','.specVal>span',function(){
            $(this).addClass('activeAttr').siblings().removeClass('activeAttr');
        });
    });
</script>
    </div>
    <!-- 内容区域 end -->

</div>

<!--供音频提示音使用-->
<audio src="http://www.lmeri.com/assets/admin/dist/remind.mp3" id="myaudio"></audio>

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
                       var myAuto = document.getElementById('myaudio');
                       myAuto.play();
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
