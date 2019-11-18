<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:92:"D:\phpStudy\WWW\lmeriPro\web/../source/application/store\view\comment\user_comment\index.php";i:1573699538;s:73:"D:\phpStudy\WWW\lmeriPro\source\application\store\view\layouts\layout.php";i:1573699389;}*/ ?>
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
        <link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/cusTalk.css">
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <input type="hidden" name="/store/comment.user_comment/add" value="/<?= $request->pathinfo() ?>">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">评价列表</div>
                            </div>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                            <div class="perTalk">
                                <div class="userInfo">
                                    <!-- 用户头像 -->
                                    <div class="headImg">
                                        <img src="<?= $item['headimgurl']?>">
                                    </div>
                                    <!-- 用户姓名，评论时间，评论星级 -->
                                    <div class="detailInfo">
                                        <div class="username">
                                            <div class="cusName"><?= $item['username']?></div>
                                            <div class="talkTime">
                                                <span><?= date('Y-m-d h:s',$item['add_time'])?></span>
                                            </div>
                                        </div>
                                        <div class="timeStar">
                                            <?php if (!empty($item['sendtype'])): foreach ($item['sendtype'] as $key=>$val): ?>
                                                <div class="talkStar">
                                                    <div class="talkInfo"><?= $key?></div>
                                                    <div class="starBox">
                                                    <?php for($i=0;$i<=$val-1;$i++): ?>
                                                        <img src="upload/images/goods/cus_order/icon4.png" alt="">
                                                    <?php endfor; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach;endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <!-- 评论的内容 -->
                                <div class="talkCont am-margin-top-sm">
                                   <span><?= $item['content']?></span>
                                </div>
                                <!-- 评论的图片 -->
                                <?php if (!empty($item['image'])): ?>
                                    <div class="talkImg am-margin-top-sm">
                                        <?php foreach ($item['image'] as $val): ?>
                                            <img src="../<?= $val?>" alt="">
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif;?>
                                <!-- 回复的内容 -->
                                <div class="storeReply am-margin-top-sm">
                                    <?php if (!empty($item['storecomment'])): foreach ($item['storecomment'] as $val): ?>
                                    <div class="replyBox">
                                        <div><?= $val['content']?></div>
                                        <small><?= $val['real_name']?>&nbsp;&nbsp;<?= date('Y-m-d h:i',$val['creater_time'])?></small>
                                    </div>
                                    <?php endforeach;endif; ?>
                                </div>
                                <?php if( checkPrivilege('comment.user_comment/edit')):?>
                                    <a href="javascript:;" class="am-btn am-round am-btn-secondary am-btn-xs j-back"  data-isshow="<?= $item['is_show'] ?>" data-commentid="<?= $item['comment_id'] ?>">
                                        <?php if($item['is_show']==1): ?>
                                            显示
                                        <?php else: ?>
                                            屏蔽
                                        <?php endif; ?>
                                    </a>
                                <?php endif;if( checkPrivilege('comment.user_comment/quality')):?>
                                    <a href="javascript:;" class="am-btn am-round am-btn-secondary am-btn-xs j-isgood"  data-isgood="<?= $item['is_good'] ?>" data-commentid="<?= $item['comment_id'] ?>">
                                        <?php if($item['is_good']==1): ?>
                                            优质评论
                                        <?php else: ?>
                                            普通评论
                                        <?php endif; ?>
                                    </a>
                                <?php endif;if( checkPrivilege('comment.user_comment/add')):?>
                                    <button type='button' class="huifu am-btn am-btn-primary am-btn-xs">回复</button>
                                    <a href="javascript:;" class="fabiao am-btn am-btn-primary am-btn-xs j-state" data-id="<?= $item['comment_id'] ?>" data-state="">
                                        <i class="am-icon-pencil"></i>发表
                                    </a>
                                <?php endif;?>
                            </div>
                            <?php endforeach; else: endif; ?>
                            <div class="am-u-lg-12 am-cf">
                                <div class="am-fr"><?= $list->render() ?> </div>
                                <div class="am-fr pagination-total am-margin-right">
                                    <div class="am-vertical-align-middle">总记录：<?= $list->total() ?></div>
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
    $(function(){
        $('.huifu').click(function(){
            $(this).hide().siblings('.fabiao').show()
            console.log($(this).parent())
            var textBox = $('<textarea name="" class="textBox" style="width:100%;height:90px;margin:20px 0;border:1px solid #d3d3d3;"></textarea>')
            $(this).parent().append(textBox)
        })
        $('.fabiao').click(function(){
            $(this).hide().siblings('.huifu').show()
            $(this).siblings('.textBox').hide()
        })
     });

</script>

<script>
    $('.j-state').click(function () {
        var data = $(this).data();
        $(function () {
            $.ajax({
                type: 'post',
                url: "<?= url('comment.user_comment/add') ?>",
                data:{id:data.id,content:$('.textBox').val()},
                dataType: 'json',
                success: function (res) {
                    if (res.code) {
                        layer.msg(res.msg, {icon: 1, time: 1000});
                        setTimeout(function () {
                            window.location.reload();
                        }, 1000)
                    } else {
                        layer.msg(res.msg, {icon: 5})
                    }
                }
            })
        });
    });

    //更改是否屏蔽
    $('.j-back').click(function () {
        var data = $(this).data();
        if(data.isshow==1){
            var statu="屏蔽";
        }else{
            var statu="显示";
        }
        layer.confirm('确定要'+statu+'用户评价么？', {
            btn: ['确定', '取消'] //按钮
        }, function () {
            $.ajax({
                type: 'get',
                url: "<?= url('comment.user_comment/edit') ?>",
                data:{isshow:data.isshow,commentid:data.commentid},
                dataType: 'json',
                success: function (res) {
                    if (res.code) {
                        layer.msg(res.msg, {icon: 1, time: 2000});
                        setTimeout(function () {
                            window.location.reload();
                        }, 1000)
                    } else {
                        layer.msg(res.msg, {icon: 5})
                    }
                }
            })
        }, function () {
        });
    });

    //更改是否优质评论
    $('.j-isgood').click(function () {
        var data = $(this).data();
        if(data.isgood==0){
            var statu="评选为";
        }else{
            var statu="取消";
        }
        layer.confirm('确定'+statu+'优质评论？', {
            btn: ['确定', '取消'] //按钮
        }, function () {
            $.ajax({
                type: 'get',
                url: "<?= url('comment.user_comment/quality') ?>",
                data:{isgood:data.isgood,commentid:data.commentid},
                dataType: 'json',
                success: function (res) {
                    if (res.code) {
                        layer.msg(res.msg, {icon: 1, time: 2000});
                        setTimeout(function () {
                            window.location.reload();
                        }, 1000)
                    } else {
                        layer.msg(res.msg, {icon: 5})
                    }
                }
            })
        }, function () {
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
