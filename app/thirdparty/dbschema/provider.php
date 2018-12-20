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
                    'comment' => app::get('thirdparty')->_('第三方服务商ID'),
                ),

            'provider_name'=>
                array(
                    //'type'=>'varchar(100)',
                    'type' => 'string',
                    'length' => 100,
                    'is_title'=>true,
                    'in_list'=>true,
                    'default_in_list'=>true,
                    'label' => app::get('thirdparty')->_('第三方服务商名称'),
                ),
            'contact'=>
                array(
                    //'type'=>'varchar(32)',
                    'type' => 'string',
                    'length' => 32,
                    'in_list'=>true,
                    'default_in_list'=>true,
                    'label' => app::get('thirdparty')->_('联系人'),
                ),
            'mobile'=>
                array(
                    //'type'=>'varchar(32)',
                    'type' => 'string',
                    'length' => 32,
                    'in_list'=>true,
                    'default_in_list'=>true,
                    'label' => app::get('thirdparty')->_('手机号'),
                ),
            'addr'=>
                array(
                    //'type'=>'varchar(32)',
                    'type' => 'string',
                    'length' => 32,
                    'in_list'=>true,
                    'default_in_list'=>true,
                    'label' => app::get('thirdparty')->_('地址'),
                ),
            'desc'=>array(
                'type'=>'text',
                'in_list'=>true,
                'default_in_list'=>true,
                'label' => app::get('thirdparty')->_('第三方服务商描述'),
                'order' => 14,
            ),
            'status' =>
                array (
                    'type' => 'number',
                    'default' => 0,
                    'editable' => false,
                    'in_list'=>true,
                    'label' => app::get('thirdparty')->_('第三方服务商状态'),
                ),
            'createtime'=>
                array(
                    'type'=>'time',
                    'in_list'=>true,
                    'default_in_list'=>false,
                    'label' => app::get('thirdparty')->_('创建时间'),
                ),
        ),
    'primary' => 'provider_id',
    'index' => array(
        'ind_provider_name' => ['columns' => ['provider_name']],
        'ind_createtime' => ['columns' => ['createtime']],
    ),
    'comment' => app::get('thirdparty')->_('第三方服务商表'),
);