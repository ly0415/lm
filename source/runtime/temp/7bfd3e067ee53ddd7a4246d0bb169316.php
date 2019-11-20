<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:81:"D:\phpStudy\WWW\lmeriPro\web/../source/application/store\view\store\shop\edit.php";i:1574216224;s:73:"D:\phpStudy\WWW\lmeriPro\source\application\store\view\layouts\layout.php";i:1573699389;s:89:"D:\phpStudy\WWW\lmeriPro\source\application\store\view\layouts\_template\file_library.php";i:1571624106;}*/ ?>
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
        <style>
    .laydate-time-list>li{width:50%!important;}
    .laydate-time-list>li:last-child { display: none;}
</style>
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑门店</div>
                            </div>
                            <input type="hidden" name="shop[id]" value="<?= $model['id'] ?>">
                            <div class="am-form-group">
                                <label class="am-u-sm-2 am-form-label form-require"> 站点参照类型: </label>
                                <div class="x-region-select am-u-sm-8 am-u-end">
                                    <select class="am-u-sm-5" name="shop[store_type]" id="store_type" required>
                                        <option value="">请选择...</option>
                                        <?php if (isset($storeType)): foreach ($storeType as $k =>$store_type): ?>
                                            <option <?php if($k == $model['store_type']){echo 'selected';}?> value="<?= $k ?>" <?php if($k === 1):?>disabled<?php endif;?>>
                                                <?= $store_type ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 门店名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="shop[store_name]" value="<?=$model['store_name']?>" placeholder="请输入门店名称" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 门店logo </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <div class="am-form-file">
                                            <button type="button" class="logo upload-file am-btn am-btn-secondary am-radius">
                                                <i class="am-icon-cloud-upload"></i> 选择图片
                                            </button>
                                            <div class="uploader-list am-cf">
                                                <?php if(isset($model['logo'])):?>
                                                <div class="file-item">
                                                    <a href="<?= BIG_IMG.$model['logo']?>" title="点击查看大图" target="_blank">
                                                        <img src="<?= SIM_IMG.$model['logo']?>">
                                                    </a>
                                                    <input type="hidden" name="shop[logo]" value="<?=$model['logo']?>">
                                                    <i class="iconfont icon-shanchu file-item-delete"></i>
                                                </div>
                                                <?php endif;?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 门店轮播图 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <div class="am-form-file">
                                            <button type="button" class="background_img upload-file am-btn am-btn-secondary am-radius">
                                                <i class="am-icon-cloud-upload"></i> 选择图片
                                            </button>
                                            <div class="uploader-list am-cf">
                                                <?php if(isset($model['format_background_img'])): foreach ($model['format_background_img'] as $v):?>
                                                    <div class="file-item am-text-center" style="padding-top:30px;">
                                                        <a href="<?= BIG_IMG.$v['background']?>" title="点击查看大图" target="_blank">
                                                            <img src="<?= SIM_IMG.$v['background']?>">
                                                        </a>
                                                        <input type="text" data-isShow="true" value="<?= !empty($v['url'])?$v['url']:''?>" name="shop[url][]" style="width:170px;text-align:center;margin:10px 10px 20px 10px;">
                                                        <input type="hidden" name="shop[background_img][]" value="<?=$v['background']?>">
                                                        <i class="iconfont icon-shanchu file-item-delete"></i>
                                                    </div>
                                                <?php endforeach;endif;?>
                                            </div>
                                            <div>
                                                <small>注：活动页面路径：/exercise/exercise</small>
                                            </div>
                                            <div>
                                                <small>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;秒杀页面路径：/miaosha/miaosha</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 联系电话 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="shop[store_mobile]" value="<?=$model['store_mobile']?>"
                                           placeholder="请输入门店联系电话" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 营业开始时间 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" id="store_start_time" class="tpl-form-input" name="shop[store_start_time]" value="<?=$model['store_start_time']?>" placeholder="请选择门店营业开始时间" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 营业结束时间 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" id="store_end_time" class="tpl-form-input" name="shop[store_end_time]" value="<?=$model['store_end_time']?>" placeholder="请选择门店营业结束时间" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-2 am-form-label form-require"> 业务类型: </label>
                                <div class="x-region-select am-u-sm-8 am-u-end">
                                    <select class="am-u-sm-5" name="shop[business_id]" id="cate_id" required>
                                        <option value="">请选择...</option>
                                        <?php if (isset($business)): foreach ($business as $value): ?>
                                            <option <?php if($model['business_id'] == $value['id']) {echo 'selected';}?> value="<?= $value['id'] ?>">
                                                <?= $value['name'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="am-form-group am-padding-top">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 门店地址 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="x-region-select" >
                                        <select name="shop[province_id]" id="province" required>
                                            <option value="">请选择省份</option>
                                            <?php if(isset($model['format_province'])):foreach ($model['format_province'] as $v ):?>
                                            <option <?php if(isset($model['format_store_address'][0]) && $model['format_store_address'][0] == $v['id']){echo 'selected';}?> value="<?=$v['id']?>"><?=$v['name']?></option>
                                            <?php endforeach;endif;?>
                                        </select>
                                        <select name="shop[city_id]" id="city" required>
                                            <option value="">请选择城市</option>
                                            <?php if(isset($model['format_city'])):foreach ($model['format_city'] as $vv ):?>
                                                <option <?php if(isset($model['format_store_address'][1]) && $model['format_store_address'][1] == $vv['id']){echo 'selected';}?> value="<?=$vv['id']?>"><?=$vv['name']?></option>
                                            <?php endforeach;endif;?>
                                        </select>
                                        <select name="shop[region_id]" id="region" required>
                                            <option value="">请选择地区</option>
                                            <?php if(isset($model['format_region'])):foreach ($model['format_region'] as $vvv ):?>
                                                <option <?php if(isset($model['format_store_address'][2]) && $model['format_store_address'][2] == $vvv['id']){echo 'selected';}?> value="<?=$vvv['id']?>"><?=$vvv['name']?></option>
                                            <?php endforeach;endif;?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 详细地址 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="shop[addr_detail]" value="<?=$model['addr_detail']?>"
                                           placeholder="请输入详细地址" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 门店坐标 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-block">
                                        <input type="text" style="background: none !important;" id="coordinate"
                                               class="tpl-form-input" name="shop[coordinate]" value="<?=$model['latitude'].','.$model['longitude']?>"
                                               placeholder="请选择门店坐标" readonly="" required>
                                    </div>
                                    <div class="am-block am-padding-top-xs">
                                        <iframe id="map" src="<?= url('store.shop/index',['getpoint'=>1]) ?>" width="915" height="610"></iframe>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group am-padding-top">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 门店公告 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <textarea class="am-field-valid" rows="5" placeholder="请输入门店公告"
                                              name="shop[store_notice]"><?=$model['store_notice']?></textarea>
                                </div>
                            </div>
<!--                            <div class="am-form-group">-->
<!--                                <label class="am-u-sm-3 am-u-lg-2 am-form-label ">配送距离 </label>-->
<!--                                <div class="am-u-sm-9 am-u-end">-->
<!--                                    <input type="number" class="tpl-form-input" name="shop[distance]"-->
<!--                                           value="" >-->
<!--                                    <small>单位：km</small>-->
<!--                                </div>-->
<!--                            </div>-->
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label ">配送费 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" class="tpl-form-input" name="shop[fee]"
                                           value="<?=$model['fee']?>" >
                                    <small>单位：元</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">门店排序 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" class="tpl-form-input" name="shop[sort]"
                                           value="<?=$model['sort']?>" required>
                                    <small>数字越小越靠前</small>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 门店状态 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="shop[is_open]" value="1" data-am-ucheck <?= $model['is_open'] === 1 ? 'checked':''?>
                                               >
                                        启用
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="shop[is_open]" value="2" <?= $model['is_open'] === 2 ? 'checked':''?> data-am-ucheck>
                                        禁用
                                    </label>
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
    <div class="file-item" style="padding-top:30px;">
        <a href="{{ $value.file_big_path }}" title="点击查看大图" target="_blank">
            <img src="{{ $value.file_path }}">
        </a>

        {{ if isShow }}
         <input type="text" data-isShow="{{ isShow }}" name="shop[url][]" style="width:170px;text-align:center;margin:10px 10px 20px 10px;">
        {{/if}}

        <input type="hidden" name="{{ name }}" value="{{ $value.file_name }}">
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


<script src="assets/common/plugins/laydate/laydate.js"></script>
<script>
    /**
     * 设置坐标
     */
    function setCoordinate(value) {
        var $coordinate = $('#coordinate');
        $coordinate.val(value);
        // 触发验证
        $coordinate.trigger('change');
    }
</script>
<script>

    $(function () {
        $("#province").on('change',function () {
            var province_id = $(this).val();
            var city = $("#city");
            var region = $("#region");
            var _html = "<option value='0'>请选择城市</option>";
            city.html(_html);
            region.html(_html);
            if(province_id > 0){
                $.post("<?=url('api/city/getCityProvince')?>",{parent_id:province_id},function (res) {
                    addItem(city,res);
                },'JSON')
            }
        });

        $("#city").on('change',function () {
            var city_id = $(this).val();
            var region = $("#region");
            var _html = "<option value='0'>请选择城市</option>";
            region.html(_html);
            if(city_id > 0){
                $.post("<?=url('api/city/getCityProvince')?>",{parent_id:city_id},function (res) {
                    addItem(region,res);
                },'JSON')
            }
        });

        function addItem(obj,item){
            var _html = '';
            $.each(item,function (k,v) {
                _html += "<option value='"+v.id+"'>"+v.name+"</option>";
            })
            obj.append(_html);
            obj.change();
        }

        // 选择图片
        $('.logo').selectImages({
            name: 'shop[logo]'
        });

        // 选择图片
        $('.background_img').selectImages({
            name: 'shop[background_img][]'
            , multiple: true
            , isShow:true
        });

        //时间选择器
        laydate.render({
            elem: '#store_start_time'
            ,type: 'time'
            ,format: 'HH:mm'
        });

        //时间选择器
        laydate.render({
            elem: '#store_end_time'
            ,type: 'time'
            ,format: 'HH:mm'
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

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
               var url = "<?=url('order/get_notips_order','',false);?>";
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
