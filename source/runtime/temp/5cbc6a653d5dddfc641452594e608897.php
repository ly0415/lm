<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:77:"D:\phpStudy\WWW\lmeriPro\web/../source/application/store\view\goods\joint.php";i:1571624104;s:73:"D:\phpStudy\WWW\lmeriPro\source\application\store\view\layouts\layout.php";i:1572400948;s:90:"D:\phpStudy\WWW\lmeriPro\source\application\store\view\layouts\_template\tpl_file_item.php";i:1571624106;s:89:"D:\phpStudy\WWW\lmeriPro\source\application\store\view\layouts\_template\file_library.php";i:1571624106;}*/ ?>
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
        <link rel="stylesheet" href="assets/store/css/goods.css?v=<?= $version ?>">
<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">添加商品组合</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-2 am-u-lg-2 am-form-label form-require">商品名称：</label>
                                <div class="am-u-sm-10 am-u-end">
                                    <input type="text" class="tpl-form-input" name="goods[goods_name]"
                                           value="" required>
                                </div>
                            </div>

                            <div class="am-form-group am-padding-top">
                                <label class="am-u-sm-2 am-u-lg-2 am-form-label form-require">商品分类：</label>
                                <div class="am-u-sm-10 am-u-end">
                                    <div class="x-region-select">
                                        <select name="goods[province_id]" required>
                                            <option value="0">请选择分类</option>
                                            <?php if(isset($category)):foreach ($category as $item):?>
                                            <option value="<?=$item['id']?>"><?=$item['name']?></option>
                                            <?php endforeach;endif;?>
                                        </select>
                                        <select name="goods[city_id]"  required>
                                            <option value="0">请选择分类</option>
                                        </select>
                                        <select name="goods[cat_id]"  required>
                                            <option value="0">请选择分类</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="am-form-group yewu_kinds" style="display:none">
                                <label class="am-u-sm-2 am-u-lg-2 am-form-label ">业务类型：</label>
                                <div class="am-u-sm-10 am-u-end">
                                    <select name="goods[goods_type]" data-am-selected="{btnSize: 'sm'}"  >
                                        <option value="0">请选择业务分类</option>

                                    </select>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-2 am-u-lg-2 am-form-label">库存：</label>
                                <div class="am-u-sm-10 am-u-end">
                                    <input type="text" class="tpl-form-input" name="goods[goods_storage]"
                                           value="" required>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-2 am-u-lg-2 am-form-label form-require">本店售价：</label>
                                <div class="am-u-sm-10 am-u-end">
                                    <input type="number" class="tpl-form-input" name="goods[shop_price]"
                                           value="" required>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-2 am-u-lg-2 am-form-label form-require">市场售价：</label>
                                <div class="am-u-sm-10 am-u-end">
                                    <input type="number" class="tpl-form-input" name="goods[market_price]"
                                           value="" required>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-2 am-u-lg-2 am-form-label form-require">商品图片：</label>
                                <div class="am-u-sm-10 am-u-end">
                                    <div class="am-form-file">
                                        <button type="button"
                                                class="upload-file am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择图片
                                        </button>
                                        <div class="uploader-list am-cf">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="am-form-group">
                                 <label class="am-u-sm-2 am-u-lg-2 am-form-label form-require">选择商品：</label>
                                 <div class="am-u-sm-10 am-u-end">
                                     <div class="am-form-group" style="margin-bottom:0;">
                                         <div class="widget-become-goods am-form-file">
                                             <button type="button"
                                                     class="j-selectUser  am-btn am-btn-secondary am-radius" style="    font-size: 1.22rem;
    padding: .5rem .9rem;">
                                                 <i class="am-icon-cloud-upload"></i> 选择商品
                                             </button>
                                         </div>
                                         <div class="user-list uploader-list am-cf">
                                         </div>
                                     </div>
                                </div>
                            </div>

                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">商品信息</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-2 am-u-lg-2 am-form-label">商品简介：</label>
                                <div class="am-u-sm-10 am-u-end">
                                    <textarea name="goods[goods_remark]" id="" cols="30" rows="0"></textarea>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label for="marketValue" class="am-u-sm-2 am-u-lg-2 am-form-label form-require"> 配送属性： </label>
                                <div class="am-u-sm-10 am-u-end">
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" checked name="goods[attributes][]"  value="1" class="am-ucheck-checkbox am-field-valid">
                                        <span class="am-ucheck-icons">
                                        <i class="am-icon-unchecked"></i>
                                        <i class="am-icon-checked"></i>
                                        </span>
                                        到店自提
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="goods[attributes][]"  value="2" class="am-ucheck-checkbox am-field-valid">
                                        <span class="am-ucheck-icons">
                                        <i class="am-icon-unchecked"></i>
                                        <i class="am-icon-checked"></i>
                                        </span>
                                        送货上门
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="goods[attributes][]"  value="3" class="am-ucheck-checkbox am-field-valid">
                                        <span class="am-ucheck-icons">
                                        <i class="am-icon-unchecked"></i>
                                        <i class="am-icon-checked"></i>
                                        </span>
                                        邮寄托运
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="goods[attributes][]"  value="4" class="am-ucheck-checkbox am-field-valid">
                                        <span class="am-ucheck-icons">
                                        <i class="am-icon-unchecked"></i>
                                        <i class="am-icon-checked"></i>
                                        </span>
                                        海外代购
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-2 am-u-lg-2 am-form-label">商品描述：</label>
                                <div class="am-u-sm-10 am-u-end">
                                    <!-- 加载编辑器的容器 -->
                                    <textarea id="container" name="goods[goods_content]"  type="text/plain"></textarea>
                                </div>
                            </div>
                            <div>
<!--                                规格-->
                            <div class="am-modal am-modal-alert" tabindex="-1" id="my-alert">
                                    </div>
                            </div>
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">提交
                                    </button>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- 图片文件列表模板 -->
<script id="tpl-file-item" type="text/template">
    {{ each list }}
    <div class="file-item">
        <a href="{{ $value.file_big_path }}" title="点击查看大图" target="_blank">
            <img src="{{ $value.file_path }}">
        </a>
        <input type="hidden" name="{{ name }}" value="{{ $value.file_name }}">
        <i class="iconfont icon-shanchu file-item-delete"></i>
    </div>
    {{ /each }}
</script>

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

<script src="assets/common/js/vue.min.js"></script>
<script src="assets/common/js/ddsort.js"></script>
<script src="assets/common/plugins/umeditor/umeditor.config.js?v=<?= $version ?>"></script>
<script src="assets/common/plugins/umeditor/umeditor.min.js"></script>
<script src="assets/store/js/goods.spec.js?v=<?= $version ?>"></script>
<script src="assets/common/plugins/laydate/laydate.js"></script>
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script>

    function addItem(obj,item){
        var _html = '';
        $.each(item,function (k,v) {
            _html += "<option value='"+v.id+"'>"+v.name+"</option>";
        })
        obj.append(_html);
        obj.change();
    }


    $(function () {

        $('select[name="goods[province_id]"]').on('change',function () {
            var province_id = $(this).val();
            var city = $('select[name="goods[city_id]"]');
            var region = $('select[name="goods[cat_id]"]');
            var _html = "<option value='0'>请选择分类</option>";
            city.html(_html);
            region.html(_html);
            if(province_id > 0){
                $.post("<?=url('store.goods_category/get_category')?>",{parent_id:province_id},function (res) {
                    addItem(city,res.data);
                },'JSON')
            }
        });

        $('select[name="goods[city_id]"]').on('change',function () {
            var city_id = $(this).val();
            var region = $('select[name="goods[cat_id]"]');
            var _html = "<option value='0'>请选择分类</option>";
            region.html(_html);
            if(city_id > 0){
                $.post("<?=url('store.goods_category/get_category')?>",{parent_id:city_id},function (res) {
                    addItem(region,res.data);
                },'JSON')
            }
        });

        $('select[name="goods[cat_id]"]').on('change',function () {
            var showNum=$(this).val()
            if(showNum!=0){
                $('.yewu_kinds').show()
            }else{
                $('.yewu_kinds').hide()
            }
            var cat_id = $(this).val();
            var goods_type = $('select[name="goods[goods_type]"]');
            var _html = "<option value='0'>请选择业务类型</option>";
            if(cat_id > 0){
                $.post("<?=url('store.business/get_room_name')?>",{category_id:cat_id},function (res) {
                    $.each(res.data, function (i, e) {
                        _html += '<option value="' + e.room_type.id + '">' + e.room_type.room_name + '</option>';
                    });
                    goods_type.html(_html);
                },'JSON')
            }
        });


        // 富文本编辑器
        UM.getEditor('container', {
            initialFrameWidth: 400 + 15,
            initialFrameHeight: 400
        });

        // 选择图片
        $('.upload-file').selectImages({
            name: 'goods[original_img]'
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

        $.activity = {};
        $.extend($.activity,{
            aIndex:0,//设置的index值
            chkArry:[],
        });

        function ergodic(arr){
            let tr=$('<tr></tr>')
            var th0=$('<th class="am-text-center"></th>').text('商品ID')
            var th1=$('<th class="am-text-center"></th>').text('图片')
            var th2=$('<th class="am-text-center"></th>').text('名称')
            var th3=$('<th class="am-text-center"></th>').text('规格')
            var th4=$('<th class="am-text-center"></th>').text('数量')
            var th5=$('<th class="am-text-center"></th>').text('操作')
            tr.append([th0,th1,th2,th3,th4,th5])
            return tr
        }

        // 选择商品
        var select_goods=[];
        $('.j-selectUser').click(function () {
            var goods_ids = [];
            $("input[name='activity[goods_id][]']").each(function(){
                goods_ids.push($(this).val());
            });
            $.selectData({
                title: '选择商品',
                uri: 'store_goods/lists',
                dataIndex: 'goods_id',
                done: function (data) {
                    var selData=data
                    $.each(selData,function(k,v){
                        v.specName='';
                        v.specNames=[];
                        v.specId='';
                        v.specIds=[];
                    })
                    if(select_goods==''){
                        $.each(selData,function(k,v){
                            select_goods.push(v);
                        })
                        showData(select_goods)
                    }else{
                        let length1 = select_goods.length;
                        let length2 = data.length;
                        for (let i = 0; i < length1; i++) {
                            for (let j = 0; j < length2; j++) 
                            {
                                //判断添加的数组是否为空了
                                if (select_goods.length > 0) {
                                    if (select_goods[i]["id"] === data[j]["id"]) {
                                        select_goods.splice(i, 1); //利用splice函数删除元素，从第i个位置，截取长度为1的元素
                                        length1--;
                                    }
                                }
                            }
                        }
                        for (let n = 0; n < data.length; n++) {
                            select_goods.push(data[n]);
                        }
                        showData(select_goods)
                    }
                }
            });
        });

        function showData(arr){
            var user = [];
            $('.user-list').empty()
            var table=$('<table></table>').css('margin-top','20px')
            let thead=$('<thead></thead>')
            var tbody=$('<tbody></tbody>')
            $.each(arr,function (k,v) {
                user.push(v);;
                var input_id=$("<input readonly>").val(v.id).css({'width':'91px','height':'31px','text-align':'center','border':'none','outline':'none','background-color':'#eee'});
                var td0=$("<td></td>").append(input_id).css({'width':'100px','text-align':'center'});
                var td1='';
                var td2=$("<td></td>").html(v.goods_name).css({'width':'150px','text-align':'center','font-size':'13px'});
                var td3=''
                if(v.has_spec){
                    var inputBox=$('<input type="text" style="width:170px;border:none;outline:none;background-color:#eee;margin:22px 0;" placeholder="请选择商品规格" required>').val(v.specName)
                    var guige_btn=$('<button type="button" style="width:80px;height:31px;padding:6px 5px;margin:22px 0;" data-id="'+v.id+'" class="shezhi am-btn am-btn-secondary am-btn-xs" data-am-modal="{target: \'#my-alert\'}"></button>').text('选择规格').attr({'idn':k})
                    td1=$("<td style='text-align:center'></td>").html('<img style="width:70px;height:70px;padding: 10px" src="../'+v.original_img+'"><input type="hidden" name="goods[joint]['+k+'][store_goods_ids]" value="'+v.id+'"'+'> <input type="hidden" name="goods[joint]['+k+'][key]" value="'+v.specId+'" form-require> <input type="hidden" name="goods[joint]['+k+'][key_name]" value="'+v.specName+'">')
                    td3=$("<td></td>").append([inputBox,guige_btn]).css({'display':'flex','justify-content':'center','align-items': 'center'});
                }else{
                    var spanBox=$('<div>无规格</div>').css({'text-align':'center','font-size':'14px'});
                    td1=$("<td></td>").html('<img style="width:70px;height:70px;padding: 10px" src="'+v.original_img+'"> <input type="hidden" name="goods[joint]['+k+'][store_goods_ids]" value="'+v.id+'"'+'> <input type="hidden" name="goods[joint]['+k+'][key]" value="'+v.specId+'"> <input type="hidden" name="goods[joint]['+k+'][key_name]" value="'+v.specName+'">')
                    td3=$("<td></td>").append(spanBox).css({'width':'300px','text-align':'center'});
                }
                
                var stock=$('<input style="width:60px;border:none;outline:none;background-color:#eee" class="am-text-center" name="goods[joint]['+k+'][num]" />').attr('type','text').val(1)
                var td4=$("<td></td>").html(stock).css('padding','0 20px')
                var delBtn=$("<button type='button' class='delBtn am-btn am-btn-danger am-btn-xs'>删除</button>").attr({'del_id':v.id,'idn':k})
                var td5=$("<td></td>").html(delBtn).css({'padding':'0 20px','text-align':'center'})
                tbody.append($("<tr></tr>").attr('data-goods',user[k].id).append([td0,td1,td2,td3,td4,td5]).css('border-top','1px solid #ccc'))
            });
            $('.user-list').append(table.append([thead.append(ergodic()),tbody])).get(0)
        }

        // 删除按钮
        $(document).on('click','.delBtn',function(){
            if($(this).parent().parent().parent().children().length==1){
                $('.user-list').empty()
                select_goods=[];
            }else{
                var idn=$(this).attr('idn');
                var inx=0;
                $.each(select_goods,function(id,row){
                    if(id==idn){
                        inx=id
                    }
                })
                select_goods.splice(inx,1)
                showData(select_goods)
            }
        })

        // 获取设置按钮的idn属性，并保存到全局$中
        $(document).off('click.shezhi').on('click.shezhi','.shezhi',function(){
            // console.log($(this).parent().parent().find('input[name="goods[joint]['+dataIndex+'][key]"]').val())
			var dataIndex = $(this).attr('idn');
			var store_goods_id = $(this).data('id');
			$.activity.aIndex = dataIndex;
            var index = layer.load();
			$.post("<?=url('goods/ajax_get_specs')?>",{store_goods_id:store_goods_id},function (res) {
                layer.close(index);
                $("#my-alert").empty().append(res);
            })
        })
		
        // 点击确定后，选中的属性回填到对应的设置按钮的td中并把设置按钮隐藏
        $(document).off('click.am-modal-btn').on('click.am-modal-btn',' .am-modal-btn',function(){
            $.extend($.activity,{
                specName:'',
                specNames:[],
                specId:'',
                specIds:[]
            });
			var inputs = $('.specChk input[type="radio"]');
			$.activity.chkArry=[];
			$('.sku_value:checked').each(function(i,v){
				var chkVal = $(this).attr('data-specname');
				var chkId = $(this).val();
				var chkData = {};
                $.activity.specName=$.activity.specName+'_'+chkVal
                $.activity.specNames.push(chkVal)
                $.activity.specId=$.activity.specId+'_'+chkId
                $.activity.specIds.push(chkId)
				chkData.chkVal = chkVal;
				chkData.chkId = chkId;
				$.activity.chkArry.push(chkData);
			})
            $.activity.specName=$.activity.specName.substr(1,$.activity.specName.length)
            $.activity.specId=$.activity.specId.substr(1,$.activity.specId.length)
			$('.shezhi').each(function(j,k){
				var dataIndex = $(this).attr('idn');
				if($.activity.aIndex == dataIndex){
                    select_goods[dataIndex].specName=$.activity.specName
                    select_goods[dataIndex].specNames=$.activity.specNames
                    select_goods[dataIndex].specId=$.activity.specId
                    select_goods[dataIndex].specIds=$.activity.specIds
				}
                showData(select_goods)
			})
        })
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
