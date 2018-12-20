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
        'rel_id'=>
            array(
                'type'=>'number',
                //'pkey'=>true,
                'autoincrement' => true,
                'comment' => app::get('thirdparty')->_('关联ID'),
            ),
        'cat_id' => array(
            'type'=>'number',
            'label' => 'id',
            'comment' => app::get('thirdparty')->_('服务类型id'),
            'required' => true,
            'order' => 1,
        ),
        'provider_id' => array(
            //'type' => 'varchar(100)',
            'type' => 'number',
            'length' => 100,
            'required' => true,
            'label' => app::get('thirdparty')->_('服务商id'),
            'width' => 110,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'createtime' => array(
            'type' => 'time',
            'label' => app::get('thirdparty')->_('更新时间'),
            'width' => 110,
            'in_list' => true,
            'orderby' => true,
        ),
    ),
    'primary' => 'rel_id',
    'index' => array(
        'ind_provider_id' => ['columns' => ['provider_id']],
        'ind_cat_id' => ['columns' => ['cat_id']],
    ),
    'comment' => app::get('thirdparty')->_('服务商和服务类型关联表'),
);

