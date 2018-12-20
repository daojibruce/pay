<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return array(
    'trustLogins' => [
        'weibo',
        'qq',
        'renren',
        'weixin',
        'wapweixin',
    ],

	//用户角色
	'user_role' => [
        SYS_USER_ROLE_CUSTOM => [  //普通会员
            'name'  => SYS_USER_ROLE_CUSTOM,
            'use'   => 'topc_ctl_member@index',
            'url'   => url::action('topc_ctl_member@index'),
            'ico'   => '1',
        ],
        SYS_USER_ROLE_COMPANY => [  //企业会员
            'name'  => SYS_USER_ROLE_COMPANY,
            'use'   => 'topc_ctl_member@seInfoCompanydisplay',
            'url'   => url::action('topc_ctl_member@seInfoCompanydisplay'),
            'ico'   => '7',
        ], //企业会员
        SYS_USER_ROLE_SELLER => [  //商家
            'name'  => SYS_USER_ROLE_SELLER,
            'use'   => 'topshop_ctl_index@index',
            'url'   => url::action('topshop_ctl_index@index'),
            'ico'   => '8',
        ],  //商家
        SYS_USER_ROLE_SPECICAL => [  //平台展销
            'name'  => SYS_USER_ROLE_SPECICAL,
            'use'   => '',
            'url'   => '/temai',
            'ico'   => '2',
        ],
        SYS_USER_ROLE_SERVICE => [  //服务撮合
            'name'  => SYS_USER_ROLE_SERVICE,
            'use'   => 'topc_ctl_member_proofing@index',
            'url'   => url::action('topc_ctl_member_proofing@index'),
            'ico'   => '3',
        ],
        SYS_USER_ROLE_OMS => [  //服务撮合
            'name'  => SYS_USER_ROLE_OMS,
            'use'   => 'topc_ctl_member@oms',
            'url'   => url::action('topc_ctl_member@oms'),
            'ico'   => '9',
        ],
        /*
        SYS_USER_ROLE_JZB => [  //工业见证宝
            'name'  => SYS_USER_ROLE_JZB,
            'use'   => '',
            'url'   => 'javascript:alert(\'敬请期待！\');',
            'ico'   => '4',
        ],*/
        SYS_USER_ROLE_DELIVER => [  //物流
            'name'  => SYS_USER_ROLE_DELIVER,
            'use'   => '',
            'url'   => 'javascript:alert(\'敬请期待！\');',
            'ico'   => '5',
        ],
        SYS_USER_ROLE_STATE => [  //三方服务站
            'name'  => SYS_USER_ROLE_STATE,
            'use'   => '',
            'url'   => 'javascript:alert(\'敬请期待！\');',
            'ico'   => '6',
        ],
	],


    'credit_type' => [
        'base'	=> '基础积分',
        'seller'	=> '商城积分',
        'temai'	=> '平台展销积分',
        'proofing'	=> '服务撮合积分',
    ],
);
