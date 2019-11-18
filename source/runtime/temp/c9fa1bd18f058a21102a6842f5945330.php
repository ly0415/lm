<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:85:"D:\wamp\www\lmeriPro\newLmeri\web/../source/application/store\view\shop\user\edit.php";i:1564143685;s:78:"D:\wamp\www\lmeriPro\newLmeri\source\application\store\view\layouts\layout.php";i:1564143685;}*/ ?>
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
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑管理员</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">用户名 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="user[user_name]"
                                           value="<?= $model['user_name'] ?>" placeholder="请输入用户名" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 所属门店 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="user[store_id]"
                                            data-am-selected="{ btnSize: 'sm', placeholder:'请选择', maxHeight: 400}"
                                            id="store_id"    required>
                                        <option value=""></option>
                                        <?php if (isset($shopList) && !$shopList->isEmpty()):
                                            foreach ($shopList as $item): ?>
                                                <option <?php if($model['store_id'] == $item['id']) echo 'selected';?> value="<?= $item['id'] ?>"><?= $item['store_name'] ?></option>
                                            <?php endforeach; endif; ?>
                                    </select>
                                    <div class="help-block">
                                        <small>请选择店员所属的门店，用于核销订单</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">所属角色 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="user[role_id][]" multiple data-am-selected="{btnSize: 'sm'}" required>
                                        <?php if (isset($roleList)): foreach ($roleList as $role): ?>
                                            <option value="<?= $role['role_id'] ?>"
                                                <?= in_array($role['role_id'], $model['roleIds']) ? 'selected' : '' ?>>
                                                <?= $role['role_name_h1'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                    <div class="help-block">
                                        <small>注：支持多选</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">登录密码 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="password" class="tpl-form-input" name="user[password]"
                                           value="" placeholder="请输入登录密码">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">确认密码 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="password" class="tpl-form-input" name="user[password_confirm]"
                                           value="" placeholder="请输入确认密码">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">姓名 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="user[real_name]"
                                           value="<?= $model['real_name'] ?>">
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
<script>
    $(function () {

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();
        $('#store_id').change(function () {
            var _this = $(this);
            var store_id = _this.val();
            if(!store_id){
                return false
            }
            getRole(store_id);
        })
    });
    function getRole(store_id) {
        //获取选定的一级分类名称
        //根据一级分类查二级数据
        $.ajax({
            //取消异步，也就是必须完成上面才能走下面
            async:false,
            url:"<?php echo url('shop.user/getRoleList');?>",
            data:{store_id:store_id},
            type:"get",
            success: function(data){
                console.log(data);
                $("#role_id").html(data);
            }
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