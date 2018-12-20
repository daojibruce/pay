<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return array (
    'columns' => array (
        'referee_id' => array (
            'type'=>'number',
            //'pkey'=>true,
            'autoincrement' => true,
            'label' => app::get('sysuser')->_('推荐来源ID'),
            'comment' => app::get('sysuser')->_('推荐来源ID'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'referee_name' => array(
            'type' => 'string',
            'length' => 150,
            'label' => app::get('sysuser')->_('推荐来源名称'),
            'comment' => app::get('sysuser')->_('推荐来源名称'),
            'in_list' => true,
            'required' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
            'filtertype' => 'yes',
            'filterdefault' => true,
        ),
        'account_id' => array(
            'type' => 'table:account@desktop',
            'label' => app::get('sysuser')->_('操作员'),
            'comment' => app::get('sysuser')->_('操作员'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'createtime'=>
            array(
                'type'=>'time',
                'comment' => app::get('sysuser')->_('创建时间'),
            ),
        'modified_time' =>
            array (
                'type' => 'last_modify',
                'label' => app::get('sysuser')->_('最后修改时间'),
            ),
        'status' => array(
            'type' => 'bool',
            'default' => 1,
            'comment' => app::get('sysuser')->_('状态'),
            'label' => app::get('sysuser')->_('状态'),
            'width' => 50,
            'in_list' => true,
        ),
    ),
    'primary' => 'referee_id',
    'comment' => app::get('sysuser')->_('推荐来源'),
);
