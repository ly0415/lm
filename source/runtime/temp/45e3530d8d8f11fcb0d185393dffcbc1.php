<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:92:"D:\phpStudy\WWW\lmeriPro\web/../source/application/store\view\store\goods_category\index.php";i:1571624106;s:73:"D:\phpStudy\WWW\lmeriPro\source\application\store\view\layouts\layout.php";i:1572400948;}*/ ?>
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
                    <div class="widget-title am-cf">商品分类</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="tips am-margin-bottom-sm am-u-sm-12">
                        <div class="pre">
                            <p> 注：商品分类最多添加3级
                        </div>
                    </div>
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-3">
                                <div class="am-form-group">
                                    <?php if (checkPrivilege('store.goods_category/add')): ?>
                                        <div class="am-btn-group am-btn-group-xs">
                                            <a class="am-btn am-btn-default am-btn-success am-radius"
                                               href="<?= url('store.goods_category/add') ?>">
                                                <span class="am-icon-plus"></span> 新增
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="am-u-sm-12 am-u-md-9">
                                <div class="am fr">
                                    <div class="am-form-group am-fl">
                                        <?php $category_id = $request->get('category_id') ?: null; ?>
                                        <select name="category_id" data-am-selected="{searchBox: 1, btnSize: 'sm',  placeholder: '商品分类', maxHeight: 400}">
                                            <option value="0">请选择商品分类</option>
                                            <?php if (isset($category)): foreach ($category as $first): ?>
                                                <option value="<?= $first['id'] ?>"
                                                    <?= $category_id == $first['id'] ? 'selected' : '' ?>>
                                                    <?= $first['name'] ?></option>
                                                <?php if (isset($first['child'])): foreach ($first['child'] as $two): ?>
                                                    <option value="<?= $two['id'] ?>"
                                                        <?= $category_id == $two['id'] ? 'selected' : '' ?>>
                                                        　　<?= $two['name'] ?></option>
                                                    <?php if (isset($two['child'])): foreach ($two['child'] as $three): ?>
                                                        <option value="<?= $three['id'] ?>"
                                                            <?= $category_id == $three['id'] ? 'selected' : '' ?>>
                                                            　　　<?= $three['name'] ?></option>
                                                    <?php endforeach; endif; endforeach; endif; endforeach; endif; ?>
                                        </select>
                                    </div>

                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <div class="am-input-group-btn">
                                                <button class="am-btn am-btn-default am-icon-search" type="submit"></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black ">
                            <thead>
                            <tr>
                                <th>分类ID</th>
                                <th>分类名称</th>
                                <th>分类排序</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
<!--                            一级分类-->
                            <?php if (!empty($list)): foreach ($list as $first): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $first['id'] ?></td>
                                    <td class="am-text-middle"><?= $first['name'] ?></td>
                                    <td class="am-text-middle"><?= $first['sort'] ?></td>
                                    <td class="am-text-middle"><?= $first['create_time'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('store.goods_category/edit')): ?>
                                                <a href="<?= url('store.goods_category/edit',
                                                    ['id' => $first['id']]) ?>">
                                                    <i class="am-icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; if (checkPrivilege('store.goods_category/delete')): ?>
                                                <a href="javascript:;" class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $first['id'] ?>">
                                                    <i class="am-icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
<!--                            二级分类-->
                                <?php if (isset($first['child'])): foreach ($first['child'] as $two): ?>
                                    <tr>
                                        <td class="am-text-middle"><?= $two['id'] ?></td>
                                        <td class="am-text-middle">------------<?= $two['name'] ?></td>
                                        <td class="am-text-middle"><?= $two['sort'] ?></td>
                                        <td class="am-text-middle"><?= $two['create_time'] ?></td>
                                        <td class="am-text-middle">
                                            <div class="tpl-table-black-operation">
                                                <?php if (checkPrivilege('store.goods_category/edit')): ?>
                                                    <a href="<?= url('store.goods_category/edit',
                                                        ['id' => $two['id']]) ?>">
                                                        <i class="am-icon-pencil"></i> 编辑
                                                    </a>
                                                <?php endif; if (checkPrivilege('store.goods_category/delete')): ?>
                                                    <a href="javascript:;"
                                                       class="item-delete tpl-table-black-operation-del"
                                                       data-id="<?= $two['id'] ?>">
                                                        <i class="am-icon-trash"></i> 删除
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
<!--                                三级分类-->
                                    <?php if (isset($two['child'])): foreach ($two['child'] as $three): ?>
                                        <tr>
                                            <td class="am-text-middle"><?= $three['id'] ?></td>
                                            <td class="am-text-middle">------------------------------<?= $three['name'] ?></td>
                                            <td class="am-text-middle"><?= $two['sort'] ?></td>
                                            <td class="am-text-middle"><?= $three['create_time'] ?></td>
                                            <td class="am-text-middle">
                                                <div class="tpl-table-black-operation">
                                                    <?php if (checkPrivilege('store.goods_category/view')): ?>
                                                        <a href="<?= url('store.goods_category/view',
                                                            ['id' => $three['id']]) ?>">
                                                            <i class="am-icon-pencil"></i> 所属业务
                                                        </a>
                                                    <?php endif; if (checkPrivilege('store.goods_category/edit')): ?>
                                                        <a href="<?= url('store.goods_category/edit',
                                                            ['id' => $three['id']]) ?>">
                                                            <i class="am-icon-pencil"></i> 编辑
                                                        </a>
                                                    <?php endif; if (checkPrivilege('store.goods_category/delete')): ?>
                                                        <a href="javascript:;"
                                                           class="item-delete tpl-table-black-operation-del"
                                                           data-id="<?= $three['id'] ?>">
                                                            <i class="am-icon-trash"></i> 删除
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; endforeach; endif; endforeach; else: ?>
                                <tr>
                                    <td colspan="5" class="am-text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {
        // 删除元素
        var url = "<?= url('store.goods_category/delete') ?>";
        $('.item-delete').delete('id', url);
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
