<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
return  array(
    'columns'=>
        array(
            'temai_server_id'=>
                array(
                    'type'=>'number',
                    'autoincrement' => true,
                    'comment' => app::get('systemai')->_('平台展销商ID'),
                ),
            'user_id' =>
                array (
                    'type' => 'table:account@sysuser',
                    'in_list'=>true,
                    'default_in_list'=>true,
                    //'pkey' => true,
                    'label' => app::get('systemai')->_('平台展销会员ID'),
                ),
            'server_name'=>
                array(
                    //'type'=>'varchar(100)',
                    'type' => 'string',
                    'length' => 100,
                    'is_title'=>true,
                    'in_list'=>false,
                    'default_in_list'=>false,
                    'label' => app::get('systemai')->_('平台展销商名称'),
                    'comment' => app::get('systemai')->_('平台展销商名称'),
                ),
            'server_cert'=>
                array(
                    //'type'=>'varchar(100)',
                    'type' => 'string',
                    'length' => 100,
                    'is_title'=>true,
                    'in_list'=>false,
                    'default_in_list'=>false,
                    'label' => app::get('systemai')->_('企业证件'),
                    'comment' => app::get('systemai')->_('企业证件'),
                ),
            'server_mobile'=>
                array(
                    //'type'=>'varchar(32)',
                    'type' => 'string',
                    'length' => 32,
                    'in_list'=>true,
                    'default_in_list'=>true,
                    'label' => app::get('systemai')->_('手机号'),
                    'comment' => app::get('systemai')->_('手机号'),
                ),
            'server_desc'=>array(
                'type'=>'text',
                'in_list'=>true,
                'default_in_list'=>false,
                'is_title' => false,
                'searchtype' => 'has',
                'filtertype' => false,
                'filterdefault' => 'true',
                'label' => app::get('systemai')->_('平台展销商描述'),
                'comment' => app::get('systemai')->_('平台展销商描述'),
                'order' => 14,
            ),
            'reson_refuse'=>array(
                'type'=>'text',
                'in_list'=>false,
                'default_in_list'=>false,
                'is_title' => true,
                'searchtype' => 'has',
                'filtertype' => false,
                'filterdefault' => 'true',
                'label' => app::get('systemai')->_('拒绝平台展销商描述'),
                'comment' => app::get('systemai')->_('拒绝平台展销商描述'),
                'order' => 14,
            ),
            'status' =>
                array (
                    'type' => array(
                        'pending' => app::get('systemai')->_('等待审核'),
                        'reject' => app::get('systemai')->_('审核失败'),
                        'succ' => app::get('systemai')->_('审核通过'),
                    ),
                    'default' => 0,
                    'editable' => false,
                    'in_list'=>true,
                    'label' => app::get('systemai')->_('状态'),
                ),
            'createtime'=>
                array(
                    'type'=>'time',
                    'in_list'=>true,
                    'label' => app::get('systemai')->_('创建时间'),
                    'comment' => app::get('systemai')->_('创建时间'),
                ),
            'modified_time' =>
                array (
                    'type' => 'last_modify',
                    'in_list'=>true,
                    'label' => app::get('systemai')->_('最后修改时间'),
                ),
        ),
    'primary' => 'temai_server_id',
    'index' => array(
        'ind_server_name' => ['columns' => ['server_name']],
        'ind_createtime' => ['columns' => ['createtime']],
    ),
    'comment' => app::get('sysshop')->_('平台展销商申请记录表'),
);