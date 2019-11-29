<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div  class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">画像信息</div>
                                <?php if(checkPrivilege('order.tag/add') && !$tagList->isEmpty()):?>
                                <button type="button" class="am-btn am-btn-sm am-round am-btn-success am-fr" data-am-modal="{target: '#doc-modal-1'}">添加</button>
                                <?php endif;?>
                            </div>
                            <?php if (isset($list) && !$list->isEmpty()):foreach ($list as $k => $content):?>
                                <div class="am-cf am-text-middle" style="position: relative;width:100%">
                                    <div class="am-fl">
                                        <input class="huaxiang_name" name="title[]" type="text" value="<?=$content['title'] ? : '画像'.($k + 1)?>" style="border-bottom:0;text-align:center;margin:0;">
                                    </div>
                                    <i class="bianji_icon am-icon-pencil am-fl" style="height:31px;padding:3px 10px;"></i>
                                    <button id="confirm<?=$content['id']?>" onclick="confirms(this)" data-id="<?=$content['id']?>" type="button" class="saveBtn am-btn am-btn-xs am-round am-btn-success am-fl" style="display:none;margin:3px 0;padding:3px 6px;">保存</button>
                                </div>
                                <?php if(isset($content['content'])):foreach ($content['content'] as $tag):?>
                                    <div class="am-form-group">

                                        <?php if(isset($tag['tag_items'])):?>
                                            <label class="am-u-sm-3 am-form-label"><?=$tag['group_name']?>：</label>
                                        <?php endif;?>

                                        <div class="am-u-sm-8 am-text-left am-u-end">
                                            <?php if(isset($tag['tag_items'])):foreach ($tag['tag_items'] as $item):?>
                                                <label class="am-checkbox-inline" style="margin:0 10px 0 0;">
                                                    <input type="checkbox" name="tag[content][<?=$tag['group_id']?>][tag_items][]" value="<?=$item?>"  checked data-am-ucheck><?=$item?>
                                                </label>
                                            <?php  endforeach;endif;?>
                                        </div>
                                    </div>
                                <?php endforeach; endif;endforeach; endif;?>

                            <div class="am-modal" tabindex="-1" id="doc-modal-1">
                                <div class="am-modal-dialog" style="width:700px;max-height:400px;overflow:auto;border-top:3px solid #ffa627;background:#fff;">
                                    <form id="my-form" class="am-form tpl-form-line-form" method="post">
                                        <input type="hidden" name="tag[order_sn]" value="<?=$request->param('order_sn');?>">
                                        <div class="widget-head am-cf" style="margin-top:0;">
                                            <div class="widget-title am-fl">设置顾客画像</div>
                                        </div>
                                        <div class="am-modal-body" style="position: relative;" id="yinlian_img">
                                            <?php if (isset($tagList) && !$tagList->isEmpty()):foreach ($tagList as $tags):?>
                                                <div class="am-form-group">
                                                    <label class="am-u-sm-3 am-form-label"><?=$tags['group_name']?>：</label>
                                                    <input type="hidden" name="tag[content][<?=$tags['group_id']?>][group_id]" value="<?=$tags['group_id']?>" data-am-ucheck>
                                                    <input type="hidden" name="tag[content][<?=$tags['group_id']?>][group_name]" value="<?=$tags['group_name']?>" data-am-ucheck>

                                                    <div class="am-u-sm-8 am-text-left am-u-end">
                                                        <?php if(isset($tags['spec_items'])):foreach ($tags['spec_items'] as $item):?>
                                                            <label class="am-checkbox-inline" style="margin:0 10px 0 0;">
                                                                <input type="checkbox" name="tag[content][<?=$tags['group_id']?>][tag_items][]" value="<?=$item['spec_value']?>" data-am-ucheck><?=$item['spec_value']?>
                                                            </label>
                                                        <?php endforeach;endif;?>
                                                    </div>
                                                </div>
                                            <?php endforeach; endif;?>
                                        </div>
                                        <div class="am-modal-footer am-text-right">
                                            <button class="am-btn am-btn-xs am-btn-secondary am-margin-right-lg">保存</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg am-text-center">
                                    <button type="button" class="am-btn am-btn-xs am-btn-secondary" data-am-modal="{target: '#doc-modal-1'}">继续添加</button>
                                </div>
                            </div> -->
                        </fieldset>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>

    function confirms(obj){
        var id = $(obj).data('id');
        var title = $(obj).siblings('div').find("input[name='title[]']").val();
        $.post("<?=url('order.tag/edit')?>",{id:id,title:title},function (result) {
            result.code === 1 ? $.show_success(result.msg, result.url) : $.show_error(result.msg);
        },'JSON')


    }

    $(function () {

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();


        $('.bianji_icon').click(function(){
            var nameBox = $(this).siblings('.am-fl').children(".huaxiang_name");
            var nameLength = (nameBox.val()).length;
            console.log(nameLength)
            $(this).hide()
            $(this).siblings('.saveBtn').show()
            if((nameBox.get(0)).setSelectionRange){
                setTimeout(function(){
                    (nameBox.get(0)).focus();
                    (nameBox.get(0)).setSelectionRange(nameLength, nameLength);
                }, 0);
            }
        })

        $('.saveBtn').click(function(){
            $(this).hide()
            $(this).siblings('.bianji_icon').show()
        })
    });
</script>