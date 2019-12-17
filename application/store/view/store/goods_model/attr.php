<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
<!--                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">-->
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">添加商品规格</div>
                            </div>
<!--                            <div class="am-form-group">-->
<!--                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">规格名称 </label>-->
<!--                                <div class="am-u-sm-9 am-u-end">-->
<!--                                    <input type="text" class="tpl-form-input" name="goods_spec[attr_name]"-->
<!--                                           value="--><?//=$list['attr_name']?><!--" required>-->
<!--                                </div>-->
<!--                            </div>-->
                            <!--  指派弹框 -->
                            <div class="am-modal am-modal-no-btn" tabindex="-1" id="doc-modal-2">
                                <div class="am-modal-dialog" style="background-color: #fff;width:500px">
                                    <div class="am-modal-hd" style="padding:0 10px;">
                                        <div class="widget-head am-cf" style="margin:0;">
                                            <div class="widget-title am-text-left">编辑</div>
                                        </div>
                                        <a href="javascript: void(0)" class="am-close am-close-spin" data-am-modal-close>&times;</a>
                                    </div>
                                    <div class="am-modal-bd" style="min-height:100px;">
                                        <form id="my-form2" class="am-form tpl-form-line-form" method="post" action="<?=url('store.goods_model/attr')?>">
                                            <div class="am-form-group am-margin-top-lg am-margin-bottom">
                                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 店员 </label>
                                                <div class="am-u-sm-9 am-u-end">
                                                    <div class="x-region-select am-text-left" >
                                                        <select name="type_id" id="province1"  data-province data-am-selected="{btnSize: 'sm',btnWidth:150, placeholder: '所属门店'}">
                                                            <option value="-1">商品模型</option>
                                                            <?php if ($category): foreach ($category as $val):  ?>
                                                                <option value="<?= $val['id']?>"  ><?= $val['name']?></option>
                                                            <?php endforeach;endif;?>
                                                        </select>
                                                    </div>
                                                    <div class="am-form-group am-margin-top am-margin-bottom-xl">
                                                        <label class="am-u-sm-4 am-text-right form-require" style="padding:0;font-weight:500;margin:9px 0 0 0;"> 属性名称 </label>
                                                        <div class="am-u-sm-6 am-u-end">
                                                            <input type="text" name="attr_name" value="" placeholder="" class="tpl-form-input" style="border:0;border-bottom: 1px solid #d6d6d6;padding: 6px 5px;outline:none;" required>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div style="border-top:1px solid #eee;padding-top:10px;text-align:right">
                                                <input type="hidden" name="attr_id" value="">
<!--                                                <input type="hidden" name="_type" value="">-->
                                                <button type="submit" class="am-btn am-btn-secondary am-btn-xs">保存</button>
                                                <button type="button" class="am-btn am-btn-secondary am-btn-xs" data-am-modal-close>取消</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">商品模型 </label>

                                <div class="am-u-sm-9 am-u-end">
                                    <select name="goods_spec[type_id]" required
                                            data-am-selected="{searchBox: 1, btnSize: 'sm',
                                             placeholder:'请选择商品模型', maxHeight: 400}">
                                        <option value=""></option>
                                        <?php if (isset($category)): foreach ($category as $first): ?>
                                            <option <?=$first['id'] == $model['id'] ? 'selected' : ''?> value="<?=$first['id']?>"><?= $first['name'] ?></option>
                                        <?php endforeach; endif; ?>

                                    </select>
                                    <div class="am-input-group-btn">
                                        <button class="am-btn am-btn-default am-icon-search"
                                                type="submit"></button>
                                    </div>
                                </div>
                            </div>
<!--                            <div class="am-form-group">-->
<!--                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">-->
<!--                                    <button type="submit" class="j-submit am-btn am-btn-secondary">提交-->
<!--                                    </button>-->
<!--                                </div>-->
<!--                            </div>-->
                        </fieldset>
                    </div>

                    <div class="am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black ">
                            <thead>
                            <tr>
                                <th>序号</th>
                                <th>属性名称</th>
                                <th>所属模型</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($list)): foreach ($list as $k=>$first): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $k+1 ?></td>
                                    <td class="am-text-middle"><?= $first['attr_name'] ?></td>
                                    <td class="am-text-middle"><?= $first['name'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('store.goods_model/edit')): ?>
                                                <a class="am-btn am-btn-default am-btn-xs adesignate"  data-id="<?= $first['attr_id'] ?>" type="type"
                                                   data-am-modal="{target: '#doc-modal-2'}"><i class="am-icon-eye"></i> 编辑</a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('store.goods_model/delete')): ?>
                                                <a href="javascript:;" class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $first['attr_id'] ?>">
                                                    <i class="am-icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="5" class="am-text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
<!--                </form>-->
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
            name: 'goods_category[image]'
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();


        $('#my-form2').superForm();

    });
</script>
