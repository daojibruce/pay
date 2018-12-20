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
            'provider_id'=>
                array(
                    'type'=>'number',
                    //'pkey'=>true,
                    'autoincrement' => true,
                    'comment' => app::get('sysproofing')->_('服务商ID'),
                ),
            'user_id' =>
                array (
                    'type' => 'table:account@sysuser',
                    'in_list'=>true,
                    'default_in_list'=>true,
                    //'pkey' => true,
                    'label' => app::get('sysproofing')->_('会员ID'),
                ),
            'provider_name'=>
                array(
                    //'type'=>'varchar(100)',
                    'type' => 'string',
                    'length' => 100,
                    'is_title'=>true,
                    'in_list'=>true,
                    'default_in_list'=>true,
                    'label' => app::get('sysproofing')->_('服务商名称'),
                    'comment' => app::get('sysproofing')->_('服务商名称'),
                ),
            'provider_cert'=>
                array(
                    //'type'=>'varchar(100)',
                    'type' => 'string',
                    'length' => 100,
                    'is_title'=>true,
                    'in_list'=>true,
                    'default_in_list'=>true,
                    'label' => app::get('sysproofing')->_('企业证件'),
                    'comment' => app::get('sysproofing')->_('企业证件'),
                ),
            'sb_img'=>
                array(
                    //'type'=>'varchar(100)',
                    'type' => 'text',
                    'is_title'=>true,
                    'comment' => app::get('sysproofing')->_('设备图片'),
                ),
            'yp_img'=>
                array(
                    //'type'=>'varchar(100)',
                    'type' => 'text',
                    'is_title'=>true,
                    'comment' => app::get('sysproofing')->_('样品图片'),
                ),
            'provider_mobile'=>
                array(
                    //'type'=>'varchar(32)',
                    'type' => 'string',
                    'length' => 32,
                    'in_list'=>true,
                    'default_in_list'=>true,
                    'label' => app::get('sysproofing')->_('手机号'),
                    'comment' => app::get('sysproofing')->_('手机号'),
                ),
            'provider_desc'=>array(
                'type'=>'text',
                'in_list'=>true,
                'default_in_list'=>true,
                'is_title' => true,
                'searchtype' => 'has',
                'filtertype' => false,
                'filterdefault' => 'true',
                'label' => app::get('sysproofing')->_('服务商描述'),
                'comment' => app::get('sysproofing')->_('服务商描述'),
                'order' => 14,
            ),
            'status' =>
                array (
                    'type' => 'bool',
                    'default' => 0,
                    'editable' => false,
                    'in_list'=>true,
                    'label' => app::get('sysproofing')->_('审核状态'),
                ),
            'enabled' =>
                array (
                    'type' => 'bool',
                    'default' => 0,
                    'editable' => false,
                    'in_list'=>true,
                    'label' => app::get('sysproofing')->_('是否开启权限'),
                ),
            'createtime'=>
                array(
                    'type'=>'time',
                    'in_list'=>true,
                    'default_in_list'=>false,
                    'label' => app::get('sysproofing')->_('创建时间'),
                    'comment' => app::get('sysproofing')->_('创建时间'),
                ),
            'modified_time' =>
                array (
                    'type' => 'last_modify',
                    'in_list'=>true,
                    'default_in_list'=>false,
                    'label' => app::get('sysproofing')->_('最后修改时间'),
                ),
            'reason' =>
                array (
                'type' => 'string',
                'in_list'=>false,
                'default' => '',
                'label' => app::get('sysproofing')->_('审核不通过理由'),
            ),
        ),
    'primary' => 'provider_id',
    'index' => array(
        'ind_provider_name' => ['columns' => ['provider_name']],
        'ind_createtime' => ['columns' => ['createtime']],
    ),
    'comment' => app::get('sysshop')->_('商家会员表'),
);