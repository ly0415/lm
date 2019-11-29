<div class="am-tabs" data-am-tabs>
    <ul class="am-tabs-nav am-nav am-nav-tabs">
        <li class="am-active"><a href="javascript: void(0)">折扣券</a></li>
        <li><a href="javascript: void(0)">兑换券</a></li>
    </ul>
    <div class="am-tabs-bd">
        <div class="am-tab-panel am-active" style="padding:0;">
            <?php if(isset($coupon['a']) && !empty($coupon['a'])):foreach ($coupon['a'] as $a):?>
            <!--  am-margin-bottom-xs am-margin-right-xs -->
            <div class="tabInnerContent doubleRow">
                <div class="tabContentLeft">
                    <div class="sameFontSize"><?=$a['discount']?>元折扣券</div>
                    <div class="sameFontSize2">
                        <span>满</span>
                        <span class="sameSpan"><?=$a['money']?></span>
                        <span>元使用</span>
                    </div>
                    <?php if(isset($a['no_use_tips'])):?>
                    <div class="sameFontSize2 useDesc">
                        使用须知<img src="upload/images/goods/cus_order/downRow.png" sureShow='1' alt="">
                    </div>
                    <?php endif;?>
                </div>
                <div class="tabContentRight">
                    <div class="sameFontSize1">副券</div>
                    <div class="sameFontSize1 am-margin-top-x"><?=$a['start_time']['text']?></div>
                    <div class="sameFontSize1 am-margin-top-x"><?=$a['end_time']['text']?></div>
                    <?php if(!isset($a['no_use_tips'])):?>
                    <div class="RightBtn" data-coupon_id="<?=$a['coupon_id']?>" data-user_coupon_id="<?=$a['user_coupon_id']?>" data-coupon_amount="<?=$a['discount']?>"><div>立即使用</div></div>
                    <?php else:?>
                    <div class="noRightBtn"><div>不可用</div></div>
                    <?php endif;?>
                </div>
                <div class="usetips">
                    <?php if(isset($a['no_use_tips']))echo $a['no_use_tips'];?>
                </div>
            </div>
            <?php endforeach;else:?>
            
            <?php endif;?>
        </div>
        <div class="am-tab-panel" style="padding:0;">
            <?php if(isset($coupon['b']) && !empty($coupon['b'])):foreach ($coupon['b'] as $b):?>
            <div class="tabInnerContent am-margin-bottom am-margin-right singleRow">
                <div class="tabContentLeft">
                    <div class="sameFontSize">兑换券</div>
                    <div class="sameFontSize2">
                        <span>限</span>
                        <span class="sameSpan">5.00</span>
                        <span>元使用</span>
                    </div>
                    <div class="sameFontSize2 useDesc">
                        <span>使用须知</span>
                        <img src="upload/images/goods/cus_order/downRow.png" alt="">
                    </div>
                </div>
                <div class="tabContentRight">
                    <div class="sameFontSize1">副券</div>
                    <div class="sameFontSize1 am-margin-top-x">2019-09-02</div>
                    <div class="sameFontSize1 am-margin-top-x">2019-09-03</div>
                    <div class="RightBtn"><div>立即使用</div></div>
                </div>
                <div class="quanDesc" style="display:none;">
                    <div>适用范围：限<span>艾美新零售所有门店</span>使用；使用次数：1天<span>一</span>次</div>
                </div>
            </div>
            <?php endforeach;else:?>

            <?php endif;?>
        </div>
    </div>
</div>
<script>
    $(function(){
        $('.am-tabs').tabs({noSwipe:1})
    })
</script>