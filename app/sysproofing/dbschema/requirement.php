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
            'requirement_id'=>
                array(
                    'type'=>'number',
                    //'pkey'=>true,
                    'autoincrement' => true,
                    'comment' => app::get('sysproofing')->_('需求ID'),
                ),
            'user_id' =>
                array (
                    'type' => 'table:account@sysuser',
                    'in_list'=>true,
                    'default_in_list'=>true,
                    //'pkey' => true,
                    'label' => app::get('sysproofing')->_('会员ID'),
                ),
            'user_name'=>
                array(
                    //'type'=>'varchar(100)',
                    'type' => 'string',
                    'length' => 100,
                    'is_title'=>true,
                    'in_list'=>true,
                    'default_in_list'=>true,
                    'label' => app::get('sysproofing')->_('联系人'),
                    'comment' => app::get('sysproofing')->_('联系人'),
                ),
            'status' =>
                array (
                    'type' => 'string',
                    'length' => 32,
                    'in_list'=>true,
                    'default_in_list'=>false,
                    'label' => app::get('sysproofing')->_('需求状态'),
                    'comment' => app::get('sysproofing')->_('需求状态'),
                ),
            'addr' =>
                array (
                    'type' => 'string',
                    'length' => 32,
                    'in_list'=>true,
                    'default_in_list'=>false,
                    'label' => app::get('sysproofing')->_('交货地点'),
                ),
            'detail_addr' =>
                array (
                    'type' => 'string',
                    'length' => 32,
                    'in_list'=>true,
                    'default_in_list'=>false,
                    'label' => app::get('sysproofing')->_('详细地址'),
                ),
            'createtime'=>
                array(
                    'type'=>'time',
                    'in_list'=>true,
                    'label' => app::get('sysproofing')->_('创建时间'),
                    'comment' => app::get('sysproofing')->_('创建时间'),
                ),
            'start_time' =>
                array (
                    'type' => 'time',
                    'in_list'=>true,
                    'label' => app::get('sysproofing')->_('报价开始时间'),
                ),
            'end_time' =>
                array (
                    'type' => 'time',
                    'in_list'=>true,
                    'label' => app::get('sysproofing')->_('报价截止时间'),
                ),
        ),
    'primary' => 'requirement_id',
    'index' => array(
        'ind_user_id' => ['columns' => ['user_id']],
        'ind_start_time' => ['columns' => ['start_time']],
        'ind_end_time' => ['columns' => ['end_time']],
    ),
    'comment' => app::get('sysproofing')->_('撮合需求表'),
);