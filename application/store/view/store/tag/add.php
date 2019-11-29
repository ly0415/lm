<link rel="stylesheet" href="assets/store/css/goods.css?v=<?= $version ?>">
<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">顾客画像</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">标签属性：</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <!-- 商品多规格 -->
                                    <div id="many-app" v-cloak class="goods-spec-many am-form-group">
                                        <div class="goods-spec-box am-u-sm-9 am-u-end">
                                            <!-- 规格属性 -->
                                            <div class="spec-attr">
                                                <div v-for="(item, index) in spec_attr" class="spec-group-item">
                                                    <div class="spec-group-name">
                                                        <span>{{ item.group_name }}</span>
                                                        <i @click="onDeleteGroup(index)"
                                                        class="spec-group-delete iconfont icon-shanchu1" title="点击删除"></i>
                                                    </div>
                                                    <div class="spec-list am-cf">
                                                        <div v-for="(val, i) in item.spec_items" class="spec-item am-fl">
                                                            <span>{{ val.spec_value }}</span>
                                                            <i @click="onDeleteValue(index, i)"
                                                            class="spec-item-delete iconfont icon-shanchu1" title="点击删除"></i>
                                                        </div>
                                                        <div class="spec-item-add am-cf am-fl">
                                                            <input type="text" v-model="item.tempValue"
                                                                class="ipt-specItem am-fl am-field-valid">
                                                            <button @click="onSubmitAddValue(index)" type="button"
                                                                    class="am-btn am-fl">添加
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- 添加规格组：按钮 -->
                                            <div v-if="showAddGroupBtn" class="spec-group-button">
                                                <button @click="onToggleAddGroupForm" type="button"
                                                        class="am-btn">添加标签
                                                </button>
                                            </div>

                                            <!-- 添加规格：表单 -->
                                            <div v-if="showAddGroupForm" class="spec-group-add">
                                                <div class="spec-group-add-item am-form-group">
                                                    <label class="am-form-label form-require">标签名：</label>
                                                    <input type="text" class="input-specName tpl-form-input"
                                                        v-model="addGroupFrom.specName"
                                                        placeholder="请输入标签名称">
                                                </div>
                                                <div class="spec-group-add-item am-form-group">
                                                    <label class="am-form-label form-require">标签值：</label>
                                                    <input type="text" class="input-specValue tpl-form-input"
                                                        v-model="addGroupFrom.specValue"
                                                        placeholder="请输入标签值">
                                                </div>
                                                <div class="spec-group-add-item am-margin-top">
                                                    <button @click="onSubmitAddGroup" type="button"
                                                            class="am-btn am-btn-xs am-btn-secondary"> 确定
                                                    </button>
                                                    <button @click="onToggleAddGroupForm" type="button"
                                                            class="am-btn am-btn-xs am-btn-default"> 取消
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
{{include file="layouts/_template/tpl_file_item" /}}

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<script src="assets/common/js/vue.min.js"></script>
<script src="assets/common/js/ddsort.js"></script>
<script src="assets/common/plugins/umeditor/umeditor.config.js?v=<?= $version ?>"></script>
<script src="assets/common/plugins/umeditor/umeditor.min.js"></script>
<script src="assets/store/js/goods.spec.js?v=<?= $version ?>"></script>
<script>
    $(function () {

        // 富文本编辑器
        UM.getEditor('container', {
            initialFrameWidth: 375 + 15,
            initialFrameHeight: 600
        });

        $('.goods-spec-many').show()

        // 注册商品多规格组件
        var specMany = new GoodsSpec({
            el: '#many-app',
            baseData: {spec_attr:<?=json_encode($list)?>,spec_list:{}}
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm({
            // 获取多规格sku数据
            buildData: function () {
                var specData = specMany.appVue.getData();
                return {
                    goods: {
                        spec_many: {
                            spec_attr: specData.spec_attr,
                            spec_list: specData.spec_list
                        }
                    }
                };
            },
            // 自定义验证
            validation: function () {
                var specType = $('input:radio[name="goods[spec_type]"]:checked').val();
                if (specType === '20') {
                    var isEmpty = specMany.appVue.isEmptySkuList();
                    isEmpty === true && layer.msg('商品规格不能为空');
                    return !isEmpty;
                }
                return true;
            }
        });
    });
</script>
