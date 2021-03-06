<?php

/**
 * ShopEx LuckyMall
 *
 * @author     ajx
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return  array(
    'columns' => array(
        'seller_id' => array(
            'type' => 'table:account@sysshop',
            //'pkey' => true,
            'label' => app::get('sysshop')->_('用户名'),
            'width' => 110,
            'order' => 10,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'shop_id'=>array(
            'type'=>'number',
            // 'required' => true,
            'label' => '店铺id',
            'comment' => app::get('sysshop')->_('店铺id'),
        ),
        'seller_type' => array(
            'type' => array(
                '0'=>'店主',
                '1'=>'店员',
            ),
            'required' => true,
            'default' => '0',
            'order' => 30,
            'width' => 100,
            'label' => app::get('sysshop')->_('商家账号类型'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'role_id' => array(
            'type' => 'number',
            'required' => true,
            'default'=>0,//0 店主
            'comment' => app::get('sysshop')->_('角色ID'),
        ),
        'name' => array(
            //'type'=>'varchar(50)',
            'type' => 'string',
            'length' => 50,
            'label' => app::get('sysshop')->_('姓名'),
            'require'   => true,
            'default' => '',
            'order' => 40,
            'in_list' => true,
            'default_in_list'=>true,
        ),
        'mobile' => array(
            //'type'=>'varchar(20)',
            'type' => 'string',
            'length' => 20,
            'label' => app::get('sysshop')->_('手机号'),
            'required' => true,
            
        ),
        'email' => array(
            //'type'=>'varchar(100)',
            'type' => 'string',
            'length' => 100,
            'label' => app::get('sysshop')->_('邮箱'),
            'required' => true,
            'default'   => '',
            'is_title' => true,
            'order' => 60,
            'in_list' => true,
            'default_in_list'=>true,
        ),
        'auth_type' => array(
                'type' => array(
                        'UNAUTH' => '邮箱手机均未认证',
                        'AUTH_MOBILE' => '手机已认证，邮箱未认证',
                        'AUTH_EMAIL' => '邮箱已认证，手机未认证',
                        'AUTH_ALL' => '手机邮箱都已认证',
                ),
                'required' => true,
                'default' => 'UNAUTH',
                'comment' => app::get('sysshop')->_('认证类型'),
        ),
        'modified_time' => array(
            'type' => 'last_modify',
            'label' => app::get('sysshop')->_('最后修改时间'),
        ),
    ),
    'primary' => 'seller_id',
    'index' => array(
        'ind_mobile' => ['columns' => ['mobile']],
        'ind_email' => ['columns' => ['email']],
        'ind_shop_id' => ['columns' => ['shop_id']],
    ),
    'comment' => app::get('sysshop')->_('商家账号信息'),
);

