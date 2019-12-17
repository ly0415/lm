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
                                <?php endif;?>
                                <?php if( checkPrivilege('comment.user_comment/quality')):?>
                                    <a href="javascript:;" class="am-btn am-round am-btn-secondary am-btn-xs j-isgood"  data-isgood="<?= $item['is_good'] ?>" data-commentid="<?= $item['comment_id'] ?>">
                                        <?php if($item['is_good']==1): ?>
                                            优质评论
                                        <?php else: ?>
                                            普通评论
                                        <?php endif; ?>
                                    </a>
                                <?php endif;?>
                                <?php if( checkPrivilege('comment.user_comment/add')):?>
                                    <button type='button' class="huifu am-btn am-btn-primary am-btn-xs">回复</button>
                                    <a href="javascript:;" class="fabiao am-btn am-btn-primary am-btn-xs j-state" data-id="<?= $item['comment_id'] ?>" data-state="">
                                        <i class="am-icon-pencil"></i>发表
                                    </a>
                                <?php endif;?>
                            </div>
                            <?php endforeach; else: ?>
                            <?php endif; ?>
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