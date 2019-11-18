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
                                <div class="widget-title am-fl">添加门店</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-2 am-form-label form-require"> 站点参照类型: </label>
                                <div class="x-region-select am-u-sm-8 am-u-end">
                                    <select class="am-u-sm-5" name="shop[store_type]" id="store_type" required>
                                        <option value="">请选择...</option>
                                        <?php if (isset($storeType)): foreach ($storeType as $k =>$store_type): ?>
                                            <option value="<?= $k ?>" <?php if($k === 1):?>disabled<?php endif;?>>
                                                <?= $store_type ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 门店名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="shop[store_name]"
                                           placeholder="请输入门店名称" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 门店logo </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <div class="am-form-file">
                                            <button type="button"
                                                    class="logo upload-file am-btn am-btn-secondary am-radius">
                                                <i class="am-icon-cloud-upload"></i> 选择图片
                                            </button>
                                            <div class="uploader-list am-cf">
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
                                            <button type="button"
                                                    class="background_img upload-file am-btn am-btn-secondary am-radius">
                                                <i class="am-icon-cloud-upload"></i> 选择图片
                                            </button>
                                            <div class="uploader-list am-cf">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 联系电话 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="shop[store_mobile]"
                                           placeholder="请输入门店联系电话" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 营业开始时间 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" id="store_start_time" class="tpl-form-input" name="shop[store_start_time]"
                                           placeholder="请选择门店营业开始时间" required>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 营业结束时间 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" id="store_end_time" class="tpl-form-input" name="shop[store_end_time]"
                                           placeholder="请选择门店营业结束时间" required>
                                </div>
                            </div>


                            <div class="am-form-group">
                                <label class="am-u-sm-2 am-form-label form-require"> 业务类型: </label>
                                <div class="x-region-select am-u-sm-8 am-u-end">
                                    <select class="am-u-sm-5" name="shop[business_id]" id="cate_id" required>
                                        <option value="">请选择...</option>
                                        <?php if (isset($roomType)): foreach ($roomType as $room_type): ?>
                                            <option value="<?= $room_type['id'] ?>">
                                                <?= $room_type['name'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                            </div>


                            <div class="am-form-group am-padding-top">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 门店地址 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="x-region-select" data-region-selected>
                                        <select name="shop[province_id]" data-province id="province" required>
                                            <option value="">请选择省份</option>
                                            <?php if(isset($province)):foreach ($province as $v ):?>
                                                <option  value="<?=$v['id']?>"><?=$v['name']?></option>
                                            <?php endforeach;endif;?>
                                        </select>
                                        <select name="shop[city_id]" data-city id="city" required>
                                            <option value="">请选择城市</option>
                                        </select>
                                        <select name="shop[region_id]" id="region" data-region required>
                                            <option value="">请选择地区</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 详细地址 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="shop[addr_detail]" placeholder="请输入详细地址" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 门店坐标 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-block">
                                        <input type="text" style="background: none !important;" id="coordinate" class="tpl-form-input" name="shop[coordinate]" placeholder="请选择门店坐标" readonly="" required>
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
                                              name="shop[store_notice]"></textarea>
                                </div>
                            </div>
<!--                            <div class="am-form-group">-->
<!--                                <label class="am-u-sm-3 am-u-lg-2 am-form-label ">配送距离 </label>-->
<!--                                <div class="am-u-sm-9 am-u-end">-->
<!--                                    <input type="number" class="tpl-form-input" name="shop[distance]"-->
<!--                                           value="3" >-->
<!--                                    <small>单位：km</small>-->
<!--                                </div>-->
<!--                            </div>-->
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label ">配送费 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" class="tpl-form-input" name="shop[fee]"
                                           value="0" >
                                    <small>单位：元</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">门店排序 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" class="tpl-form-input" name="shop[sort]"
                                           value="100" required>
                                    <small>数字越小越靠前</small>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 门店状态 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="shop[is_open]" value="1" data-am-ucheck
                                               checked>
                                        启用
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="shop[is_open]" value="2" data-am-ucheck>
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
    <div class="file-item">
        <a href="{{ $value.file_big_path }}" title="点击查看大图" target="_blank">
            <img src="{{ $value.file_path }}">
        </a>
        <input type="hidden" name="{{ name }}" value="{{ $value.file_name }}">
        <i class="iconfont icon-shanchu file-item-delete"></i>
    </div>
    {{ /each }}
</script>

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

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

     function addItem(obj,item){
         var _html = '';
         $.each(item,function (k,v) {
             _html += "<option value='"+v.id+"'>"+v.name+"</option>";
         })
         obj.append(_html);
         obj.change();
     }

    $(function () {
        $("#province").on('change',function () {
            var province_id = $(this).val();
            var city = $("#city");
            var region = $("#region");
            var _html = "<option value='0'>请选择城市</option>";
            city.html(_html);
            region.html(_html);
            if(province_id > 0){
                $.post("<?=url('City/getProvince')?>",{parent_id:province_id},function (res) {
                    addItem(city,res.data);
                },'JSON')
            }
        });


        $("#city").on('change',function () {
            var city_id = $(this).val();
            var region = $("#region");
            var _html = "<option value='0'>请选择城市</option>";
            region.html(_html);
            if(city_id > 0){
                $.post("<?=url('City/getProvince')?>",{parent_id:city_id},function (res) {
                    addItem(region,res.data);
                },'JSON')
            }
        });

        // 选择图片
        $('.logo').selectImages({
            name: 'shop[logo]'
        });

        // 选择图片
        $('.background_img').selectImages({
            name: 'shop[background_img][]'
            , multiple: true
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
