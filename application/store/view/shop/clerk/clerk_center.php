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
                                <div class="widget-title am-fl">店员中心</div>
                            </div>
                            <div id='tabNav'>
                                <ul class="tabTitle">
                                    <li :class="show_basic?'active_title':'noactive_title'" @click="basics_Info">基本信息</li>
                                    <li :class="!show_basic?'active_title':'noactive_title'" @click="order_youhui">下单优惠</li>
                                </ul>
                                <div class="contBox am-margin-top">
                                    <div v-if="show_basic">
                                        <div class="am-form-group">
                                            <label class="am-u-sm-4 am-u-lg-3 am-form-label personal_title">用户名：</label>
                                            <div class="am-u-sm-7 am-u-end am-padding-top-xs">
                                                <span class="personal_Info">阿萨德侯</span>
                                                <input type="hidden" name="" value="">
                                            </div>
                                        </div>
                                        <div class="am-form-group">
                                            <label class="am-u-sm-4 am-u-lg-3 am-form-label personal_title">真实姓名：</label>
                                            <div class="am-u-sm-7 am-u-end am-padding-top-xs">
                                                <span class="personal_Info">阿萨德侯</span>
                                                <input type="hidden" name="" value="">
                                            </div>
                                        </div>
                                        <div class="am-form-group">
                                            <label class="am-u-sm-4 am-u-lg-3 am-form-label personal_title">手机号：</label>
                                            <div class="am-u-sm-7 am-u-end am-padding-top-xs">
                                                <span class="personal_Info">15263254852</span>
                                                <input type="hidden" name="" value="">
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="!show_basic">
                                        <div class="am-form-group">
                                            <label class="am-u-sm-4 am-u-lg-3 am-form-label">下单优惠比例：</label>
                                            <div class="am-u-sm-7 am-u-end">
                                                <input type="text" class="tpl-form-input" name="" value="">
                                                <small>选填，商品卖点简述，例如：此款商品美观大方 性价比较高 不容错过</small>
                                            </div>
                                        </div>
                                        <div class="am-form-group">
                                            <div class="am-u-sm-9 am-u-sm-push-5 am-margin-top-lg">
                                                <input type="hidden" name="user[is_admin]" value="1" id="">
                                                <button type="button" class="am-btn am-btn-primary am-btn-xs">申请</button>
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
<script src="assets/common/js/vue.min.js"></script>
<script>
    new Vue({
        el:'#tabNav',
        data:function(){
            return {
                show_basic:true,
            }
        },
        created:function(){
            
        },
        methods:{
            basics_Info(){
                this.show_basic=true;
            },
            order_youhui(){
                this.show_basic=false;
            }
        },
    })
</script>
