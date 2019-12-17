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
                                        <?php if($list && isset($list[3]['closed_store'])):foreach ($list[3]['closed_store'] as $store_3):?>
                                        <li data-id="<?=$store_3['id']?>"><?=$store_3['store_name']?><i class="iconfont icon-shanchu1 insideI"></i></li>
                                <?php endforeach;?>
                                        <?php endif;?>
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
{{include file="layouts/_template/tpl_file_item" /}}

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

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
                    if(res.code === 1){
                        layer.msg(res.msg);
                        _this.parent().remove();
                    }else{
                        layer.msg(res.msg);
                    }
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
                layer.msg(res.msg);
            }else{
                layer.msg(res.msg);
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

