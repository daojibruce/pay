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
            'sample_id'=>
                array(
                    'type'=>'number',
                    //'pkey'=>true,
                    'autoincrement' => true,
                    'comment' => app::get('sysproofing')->_('样品ID'),
                ),
            'requirement_id' =>
                array (
                    'type' => 'table:requirement@sysproofing',
                    'in_list'=>true,
                    'default_in_list'=>true,
                    //'pkey' => true,
                    'label' => app::get('sysproofing')->_('需求ID'),
                ),
            'sample_name'=>
                array(
                    //'type'=>'varchar(100)',
                    'type' => 'string',
                    'length' => 100,
                    'is_title'=>true,
                    'in_list'=>true,
                    'default_in_list'=>true,
                    'label' => app::get('sysproofing')->_('样品名称'),
                    'comment' => app::get('sysproofing')->_('样品名称'),
                ),
            'quantity'=>
                array(
                    //'type'=>'varchar(100)',
                    'type' => 'number',
                    'length' => 100,
                    'is_title'=>true,
                    'in_list'=>true,
                    'default_in_list'=>false,
                    'label' => app::get('sysproofing')->_('样品数量'),
                    'comment' => app::get('sysproofing')->_('样品数量'),
                ),
            'unit'=>
                array(
                    //'type'=>'varchar(100)',
                    'type' => 'string',
                    'length' => 100,
                    'is_title'=>true,
                    'in_list'=>true,
                    'label' => app::get('sysproofing')->_('服务撮合单位'),
                ),
            'material'=>
                array(
                    //'type'=>'varchar(100)',
                    'type' => 'string',
                    'length' => 100,
                    'is_title'=>true,
                    'in_list'=>true,
                    'label' => app::get('sysproofing')->_('服务撮合材质'),
                ),
            'cat_id'=>
                array(
                    //'type'=>'varchar(100)',
                    'type' => 'table:category@sysproofing',
                    'length' => 100,
                    'is_title'=>true,
                    'in_list'=>true,
                    'label' => app::get('sysproofing')->_('服务撮合类型'),
                ),
            'desc'=>array(
                'type'=>'text',
                'in_list'=>true,
                'default_in_list'=>false,
                'is_title' => true,
                'searchtype' => 'has',
                'filtertype' => false,
                'filterdefault' => 'true',
                'label' => app::get('sysproofing')->_('样品补充描述'),
                'order' => 14,
            ),
            'drawing'=>
                array(
                    //'type'=>'varchar(100)',
                    'type' => 'text',
                    'is_title'=>true,
                    'comment' => app::get('sysproofing')->_('样品图纸'),
                ),
            'delivery' =>
                array (
                    'type' => 'time',
                    'in_list'=>true,
                    'label' => app::get('sysproofing')->_('交货日期'),
                ),
            'pay_type' =>
                array (
                    'type' => 'string',
                    'length' => 32,
                    'in_list'=>false,
                    'default' => '',
                    'label' => app::get('sysproofing')->_('交易方式'),
                ),
            'status' =>
                array (
                    'type' => 'string',
                    'length' => 32,
                    'in_list'=>true,
                    'default_in_list'=>false,
                    'label' => app::get('sysproofing')->_('样品状态'),
                    'comment' => app::get('sysproofing')->_('样品状态'),
                ),
            'createtime'=>
                array(
                    'type'=>'time',
                    'in_list'=>true,
                    'label' => app::get('sysproofing')->_('创建时间'),
                    'comment' => app::get('sysproofing')->_('创建时间'),
                ),
        ),
    'primary' => 'sample_id',
    'index' => array(
        'ind_requirement_id' => ['columns' => ['requirement_id']],
        'ind_sample_name' => ['columns' => ['sample_name']],
        'ind_createtime' => ['columns' => ['createtime']],
    ),
    'comment' => app::get('sysproofing')->_('服务撮合样品表'),
);