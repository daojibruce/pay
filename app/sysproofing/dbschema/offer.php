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
            'offer_id'=>
                array(
                    'type'=>'number',
                    //'pkey'=>true,
                    'autoincrement' => true,
                    'comment' => app::get('sysproofing')->_('报价ID'),
                ),
            'provider_id' =>
                array (
                    'type' => 'table:provider@sysproofing',
                    'in_list'=>true,
                    'default_in_list'=>true,
                    //'pkey' => true,
                    'label' => app::get('sysproofing')->_('服务商ID'),
                ),
            'sample_id' =>
                array (
                    'type' => 'table:sample@sysproofing',
                    'in_list'=>true,
                    'default_in_list'=>true,
                    //'pkey' => true,
                    'label' => app::get('sysproofing')->_('样品ID'),
                ),
            'sample_fee'=>
                array(
                    //'type'=>'varchar(100)',
                    'type' => 'string',
                    'length' => 100,
                    'is_title'=>true,
                    'in_list'=>true,
                    'default_in_list'=>true,
                    'label' => app::get('sysproofing')->_('样品总价'),
                ),
            'post_fee'=>
                array(
                    //'type'=>'varchar(32)',
                    'type' => 'string',
                    'length' => 100,
                    'is_title'=>true,
                    'in_list'=>true,
                    'default_in_list'=>true,
                    'label' => app::get('sysproofing')->_('运费'),
                ),
            'total_fee' =>
                array (
                    'type' => 'string',
                    'length' => 100,
                    'is_title'=>true,
                    'in_list'=>true,
                    'default_in_list'=>true,
                    'label' => app::get('sysproofing')->_('合计报价'),
                ),
            'post_type' =>
                array (
                    'type' => 'string',
                    'length' => 32,
                    'in_list'=>true,
                    'default_in_list'=>false,
                    'label' => app::get('sysproofing')->_('发货方式'),
                ),
            'pay_type' =>
                array (
                    'type' => 'string',
                    'length' => 32,
                    'in_list'=>true,
                    'default_in_list'=>false,
                    'label' => app::get('sysproofing')->_('付款方式'),
                ),
            'offer_delivery'=>
                array(
                    'type'=>'time',
                    'in_list'=>true,
                    'label' => app::get('sysproofing')->_('预计交货时间'),
                ),
            'createtime'=>
                array(
                    'type'=>'time',
                    'in_list'=>true,
                    'label' => app::get('sysproofing')->_('创建时间'),
                    'comment' => app::get('sysproofing')->_('创建时间'),
                ),
            'status' =>
                array (
                    'type' => 'number',
                    'in_list'=>true,
                    'default_in_list'=>false,
                    'label' => app::get('sysproofing')->_('状态'),
                ),
            'params' =>
                array (
                    'type' => 'text',
                    'in_list'=>false,
                    'label' => app::get('sysproofing')->_('分期付款参数'),
                ),
        ),
    'primary' => 'offer_id',
    'index' => array(
        'ind_provider_id' => ['columns' => ['provider_id']],
        'ind_sample_id' => ['columns' => ['sample_id']],
    ),
    'comment' => app::get('sysproofing')->_('商家报价表'),
);