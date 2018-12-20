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
        'apply_id'=>
            array(
                'type'=>'number',
                //'pkey'=>true,
                'autoincrement' => true,
                'comment' => app::get('sysproofing')->_('申请ID'),
            ),
        'cat_id' => array(
            'type'=>'string',
            'label' => 'id',
            'comment' => app::get('sysproofing')->_('服务类型id'),
            'required' => true,
            'order' => 1,
        ),
        'provider_id' => array(
            //'type' => 'varchar(100)',
            'type' => 'table:provider@sysproofing',
            'length' => 100,
            'required' => true,
            'label' => app::get('sysproofing')->_('服务撮合服务商ID'),
            'width' => 110,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'reason' => array(
            'type'=>'string',
            'label' => app::get('sysproofing')->_('申请原因'),
            'required' => true,
            'order' => 2,
        ),
        'desc' => array(
            'type'=>'text',
            'label' => app::get('sysproofing')->_('服务能力描述'),
            'required' => true,
            'order' => 33,
        ),
        'status' => array(
            'type'=>'bool',
            'label' => app::get('sysproofing')->_('状态'),
            'default' => 0,
            'order' => 1,
            'in_list' => false,
        ),
        'reject_reason' => array(
            'type'=>'string',
            'label' => app::get('sysproofing')->_('拒绝原因'),
            'default' => '',
            'order' => 2,
        ),
        'modified_time' => array(
            'type' => 'last_modify',
            'label' => app::get('sysproofing')->_('更新时间'),
            'width' => 110,
            'in_list' => true,
            'orderby' => true,
        ),
    ),
    'primary' => 'apply_id',
    'index' => array(
        'ind_provider_id' => ['columns' => ['provider_id']],
        'ind_status' => ['columns' => ['status']],
    ),
    'comment' => app::get('sysproofing')->_('服务类型新增申请表'),
);

