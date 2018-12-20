<?php

/**
 * ShopEx LuckyMall
 *
 * @author     ajx
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return array(
    'columns' => array(
        'cat_id' => array(
            'type'=>'number',
            'label' => 'id',
            'comment' => app::get('thirdparty')->_('服务类型id'),
            'required' => true,
            //'pkey' => true,
            'autoincrement' => true,
            'order' => 1,
        ),
        'cat_name' => array(
            //'type' => 'varchar(100)',
            'type' => 'string',
            'length' => 100,
            'required' => true,
            'is_title' => true,
            'default' => '',
            'label' => app::get('thirdparty')->_('服务类型名称'),
            'width' => 110,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'createtime' => array(
            'type' => 'last_modify',
            'label' => app::get('thirdparty')->_('创建时间'),
            'width' => 110,
            'in_list' => true,
        ),
        'sort' => array(
            'type'=>'number',
            'label' => app::get('thirdparty')->_('排序'),
            'required' => true,
            'default' => '0',
            'orderby' => true,
            //'pkey' => true,
        ),
    ),
    'primary' => 'cat_id',
    'index' => array(
        'ind_cat_name' => ['columns' => ['cat_name']],
    ),
    'comment' => app::get('thirdparty')->_('服务类型表'),
);

