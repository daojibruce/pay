<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
return [
    'columns' => [
        'image_cat_id' => [
            'type' => 'number',
            'required' => true,
            'autoincrement' => true,
            'comment' => app::get('image')->_('ID'),
        ],
        'user_id' => array(
            'type' => 'table:account@sysuser',
            'default' => 0,
            'required' => false,
            'label' => app::get('sysuser')->_('所属平台展销会员'),
            'comment' => app::get('sysuser')->_('平台展销会员id'),
            'in_list' => true,
            'default_in_list' => true,
            'order' => 11,
        ),
        'shop_id'=> [
            'type'=>'number',
            'default' => 0,
            'required' => false,
            'comment' => app::get('image')->_('店铺编号id'),
        ],
        'img_type' => [
            'type' => 'string',
            'length' => 20,
            'comment' => app::get('image')->_('图片类型'),
        ],
        'image_cat_name' => [
            'type' => 'string',
            'length' => '100',
            'comment' => app::get('image')->_('图片分类名称'),
        ],
        'last_modified' => [
            'comment'=>app::get('image')->_('更新时间'),
            'type' => 'last_modify',
            'required' => true,
        ]
    ],
    'primary' => 'image_cat_id',
    'comment' => app::get('image')->_('图片类型子分类表')
];

