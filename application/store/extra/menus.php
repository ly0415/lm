<?php
/**
 * 后台菜单配置
 *    'home' => [
 *       'name' => '首页',                // 菜单名称
 *       'icon' => 'icon-home',          // 图标 (class)
 *       'index' => 'index/index',         // 链接
 *     ],
 */
return [
    'index' => [
        'name' => '首页',
        'icon' => 'icon-home',
        'index' => 'index/index',
    ],
    'store' => [
        'name' => '总站管理',
        'icon' => 'icon-guanliyuan',
        'index' => 'store.store_user/index',
        'submenu' => [
            [
                'name' => '基础信息',
                'submenu' => [
                    [
                        'name' => '业务类型',
                        'index' => 'store.business/index',
                        'uris' => [
                            'store.business/index',
                            'store.business/add',
                            'store.business/_add',
                            'store.business/_edit',
                            'store.business/_delete',
                            'store.business/edit',
                            'store.business/view',
                        ],
                    ],
                    [
                        'name' => '商品分类',
                        'index' => 'store.goods_category/index',
                        'uris' => [
                            'store.goods_category/index',
                            'store.goods_category/add',
                            'store.goods_category/edit',
                            'store.goods_category/delete',
                        ],
                    ],
                    [
                        'name' => '商品模型',
                        'index' => 'store.goods_model/index',
                        'uris' => [
                            'store.goods_model/index',
                            'store.goods_model/add',
                            'store.goods_model/edit',
                            'store.goods_model/delete',
                        ],
                    ],
                    [
                        'name' => '商品规格',
                        'index' => 'store.goods_spec/index',
                        'uris' => [
                            'store.goods_spec/index',
                            'store.goods_spec/add',
                            'store.goods_spec/edit',
                            'store.goods_spec/delete',
                        ],
                    ],
                    [
                        'name' => '商品属性',
                        'index' => 'store.goods_attribute/index',
                        'uris' => [
                            'store.goods_attribute/index',
                            'store.goods_attribute/add',
                            'store.goods_attribute/edit',
                            'store.goods_attribute/delete',
                        ],
                    ],
                    [
                        'name' => '来源列表',
                        'index' => 'store.source_list/index',
                        'uris' => [
                            'store.source_list/index',
                            'store.source_list/add',
                            'store.source_list/edit',
                            'store.source_list/delete',
                        ],
                    ],
                ]
            ],
            [
                'name' => '会员管理',
                'submenu' => [
                    [
                        'name' => '会员列表',
                        'index' => 'store.user/index',
                        'uris' => [
                            'store.user/index',
                            'store.user/edit',
                            'store.user/recomend',
                            'store.user/balanceHistory',
                            'store.user/export',
                        ],
                    ],
                ]
            ],
            [
                'name' => '门店管理',
                'submenu' => [
                    [
                        'name' => '门店列表',
                        'index' => 'store.shop/index',
                        'uris' => [
                            'store.shop/index',
                            'store.shop/add',
                            'store.shop/edit',
                            'store.shop/setting',
                            'store.shop/on',
                            'store.shop/electric_fence',
                            'store.shop/edit_ef',
                        ],
                    ],
                ]
            ],
            [
                'name' => '账号角色',
                'submenu' => [
                    [
                        'name' => '管理员列表',
                        'index' => 'store.store_user/index',
                        'uris' => [
                            'store.store_user/index',
                            'store.store_user/add',
                            'store.store_user/edit',
                            'store.store_user/delete',
                        ],
                    ],
                    [
                        'name' => '总站角色',
                        'index' => 'store.role/index',
                        'uris' => [
                            'store.role/index',
                            'store.role/add',
                            'store.role/edit',
                            'store.role/delete',
                        ],
                    ],
                ]
            ],
            [
                'name' => '数据管理',
                'submenu' => [
                    [
                        'name' => '用户电子卷',
                        'index' => 'store.data/index',
                        'uris' => [
                            'store.data/index',
                        ],
                    ],
                    [
                        'name' => '睿积分',
                        'index' => 'store.point/index',
                        'uris' => [
                            'store.point/index',
                        ],
                    ],
                    [
                        'name' => '微信收银',
                        'index' => 'store.wx_pay/index',
                        'uris' => [
                            'store.wx_pay/index',
                            'store.wx_pay/export',
                        ],
                    ],
                    [
                        'name' => '电子券管理',
                        'submenu' => [
                            [
                                'name' => '电子卷',
                                'index' => 'store.coupon/index',
                                'uris' => [
                                    'store.coupon/index',
                                    'store.coupon/add',
                                    'store.coupon/edit',
                                ],
                            ],

                        ]
                    ],
                ]
            ],
            [
                'name' => '控制台',
                'submenu' => [
                    [
                        'name' => '控制管理',
                        'index' => 'store.store_console/index',
                        'uris' => [
                            'store.store_console/index',
                        ],
                    ],
                    [
                        'name' => '画像标签',
                        'index' => 'store.tag/add',
                        'uris' => [
                            'store.tag/index',
                            'store.tag/add',
                            'store.tag/edit',
                        ]
                    ],
                ]
            ],
            [
                'name' => '门店活动',
                'submenu' => [
                    [
                        'name' => '活动列表',
                        'index' => 'store.activity/index',
                        'uris' => [
                            'store.activity/index',
                            'store.activity/add',
                            'store.activity/edit',
                            'store.activity/view',
                            'store.activity/delete',
                        ],
                    ],
                ]
            ],[
                'name' => '小程序管理',
                'submenu' => [
                    [
                        'name' => '设置轮播图',
                        'index' => 'store.rotation_chart/index',
                        'uris' => [
                            'store.rotation_chart/index',
                            'store.rotation_chart/edit',
                        ],
                    ],
                ]
            ],[
                'name' => '网站配置',
                'submenu' => [
                    [
                        'name' => '网站基本信息',
                        'index' => 'store.web_config/index',
                        'uris' => [
                            'store.web_config/index',
                        ],
                    ],
                ]
            ],
        ]
    ],
    'order' => [
        'name' => '订单管理',
        'icon' => 'icon-order',
        'index' => 'order/index',
        'submenu' => [
            [
                'name' => '订单列表',
                'index' => 'order/index',
                'uris' => [
                    'order/index',
                    'order.tag/index',
                    'order.tag/add',
                    'order.tag/edit',
                    'order/detail',
                    'order/extract',
     		        'order/appoint',
                    'order/order_print',
                    'order/get_notips_order',
                ],
            ],
            [
                'name' => '代客下单',
                'index' => 'order/goods_list',
                'uris' => [
                    'order/goods_list',
                    'order/ajax_get_spec',
                    'order/ajax_goods_price_stock',
                    'order/ajax_get_coupon',
                    'order/order_payment',
                    'order/ajax_get_fx_code',
                    'order/sms_code',
                    'order/add_cart',
                    'order/lists',
                    'wxpay/pay',
                    'alipay/pay',
                    'wxpay/qr_code',
                    'wxpay/query_order_status',
                    'user/search_user',
                ],
            ],
        ]
    ],
    'shop' => [
        'name' => '门店管理',
        'icon' => 'icon-shop',
        'index' => 'shop.store_user/index',
        'submenu' => [
            [
                'name' => '店员管理',
                'index' => 'shop.store_user/index',
                'uris' => [
                    'shop.store_user/index',
                    'shop.store_user/add',
                    'shop.store_user/edit',
                    'shop.store_user/delete',
                ],
            ],
            [
                'name' => '角色管理',
                'index' => 'shop.role/index',
                'uris' => [
                    'shop.role/index',
                    'shop.role/add',
                    'shop.role/edit',
                    'shop.role/delete',
                ],
            ],
            [
                'name' => '交班报表',
                'index' => 'shop.order/orderlist',
                'uris' => [
                    'shop.order/excelout',
                ],
            ],
            [
                'name' => '桌号设置',
                'index' => 'shop.table_number/index',
                'uris' => [
                    'shop.table_number/index',
                    'shop.table_number/add',
                    'shop.table_number/delete',
                ],
            ],
        ]
    ],
    'distribution' => [
        'name' => '分销管理',
        'icon' => 'icon-order',
        'index' => 'distribution/index',
        'submenu' => [
            [
                'name' => '分销人员',
                'index' => 'distribution/index',
                'uris' => [
                    'distribution/index',
                    'distribution/edit',
                    'distribution/own_user',
                    'distribution/exchange',
     		        'distribution/set_user_discount',
                ],
            ],[
                'name' => '优惠变更',
                'index' => 'distribution.discount_change/index',
                'uris' => [
                    'distribution.discount_change/index',
                    'distribution.discount_change/edit',
                    'distribution.discount_change/delete',
                ],
            ],[
                'name' => '分销规则',
                'index' => 'distribution.distri_butor/index',
                'uris' => [
                    'distribution.distri_butor/index',
                    'distribution.distri_butor/edit',
                    'distribution.distri_butor/delete',
                ],
            ],
        ]
    ],
    'goods' => [
        'name' => '商品管理',
        'icon' => 'icon-goods',
        'index' => 'goods/index',
        'submenu' => [
            [
                'name' => '商品列表',
                'index' => 'goods/index',
                'uris' => [
                    'goods/index',
                    'goods/bcode',
                    'goods/joint',
                    'goods/add',
                    'goods/edit',
   		            'goods/on',
                ],
            ],
        ],
    ],'statistics' => [
        'name' => '统计管理',
        'icon' => 'icon-setting',
        'index' => 'statistics.data_statistics/index',
        'submenu' => [
            [
                'name' => '销售统计',
                'submenu' => [
                    [
                        'name' => '销售商品',
                        'index' => 'statistics.data_statistics/index',
                        'uris' => [
                            'statistics.data_statistics/index',
                        ],
                    ],
                ]
            ],
            [
                'name' => '会员统计',
                'submenu' => [
                    [
                        'name' => '推荐会员',
                        'index' => 'statistics.user_statistics/index',
                        'uris' => [
                            'statistics.user_statistics/index',
                        ],
                    ],
                ]
            ],
            [
                'name' => '充值统计',
                'submenu' => [
                    [
                        'name' => '余额充值',
                        'index' => 'statistics.recharge_statistics/index',
                        'uris' => [
                            'statistics.recharge_statistics/index',
                        ],
                    ],
                ]
            ]
        ],
    ],'comment' => [
        'name' => '评价管理',
        'icon' => 'icon-setting',
        'index' => 'comment.user_comment/index',
        'submenu' => [
            [
                'name' => '用户评价',
                'index' => 'comment.user_comment/index',
                'uris' => [
                    'comment.user_comment/index',
                    'comment.user_comment/add',
                    'comment.user_comment/edit',
                    'comment.user_comment/quality',
                ],
            ],
        ],
    ],
    'market' => [
        'name' => '营销管理',
        'icon' => 'icon-setting',
        'index' => 'market.spike_activity/index',
        'submenu' => [
            [
                'name' => '秒杀列表',
                'index' => 'market.spike_activity/index',
                'uris' => [
                    'market.spike_activity/index',
                    'market.spike_activity/add',
                    'market.spike_activity/edit',
                    'market.spike_activity/delete',
                ],
            ],
        ],
    ],
    'setting' => [
        'name' => '控制中心',
        'icon' => 'icon-setting',
        'index' => 'setting/store',
        'submenu' => [
            [
                'name' => '控制列表',
                'index' => 'setting/store',
            ],
        ],
    ],
    'balance' => [
        'name' => '余额管理',
        'icon' => 'icon-setting',
        'index' => 'balance.balance_recharge/index',
        'submenu' => [
            [
                'name' => '余额充值日志',
                'index' => 'balance.balance_recharge/index',
                'uris' => [
                    'balance.balance_recharge/index',
                    'balance.balance_recharge/export',
                ],
            ],[
                'name' => '线下充值审核',
                'index' => 'balance.balance_audit/index',
                'uris' => [
                    'balance.balance_audit/index',
                    'balance.balance_audit/passOrNoAudit',
                ],
            ],[
                'name' => '余额充值券',
                'index' => 'balance.balance_coupon/index',
                'uris' => [
                    'balance.balance_coupon/index',
                    'balance.balance_coupon/add',
                    'balance.balance_coupon/designate',
                    'balance.balance_coupon/export',
                    'balance.balance_coupon/delete',
                ],
            ],
        ],
    ],
];
