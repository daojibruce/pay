<?php
/**
 * ShopEx
 *
 * @author     ajx
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return  array(
    'columns' => array(
        'id' => array(
            'type' => 'number',
            'autoincrement' => true,
            'required' => true,
        ),
        'corp_id' => array(
            'type' => 'smallint',
            'label' => app::get('syslogistics')->_('物流公司ID'),
            'comment' => app::get('syslogistics')->_('物流公司ID'),
            'required' => true,
            'order' => 1,
        ),
        'user_id' => array(
            'type' => 'number',
            'label' => app::get('syslogistics')->_('会员ID'),
            'comment' => app::get('syslogistics')->_('会员ID'),
            'required' => true,
            'order' => 2,
        ),
        'corp_code' => array(
            'type' => 'string',
            'length' => 200,
            'label' => app::get('syslogistics')->_('物流公司代码'),
            'comment' => app::get('syslogistics')->_('物流公司代码'),
            'required' => true,
            'is_title' => true,
            'in_list' => true,
            'default_in_list'=>true,
            'order' => 5,
        ),
        'corp_name' => array(
            //'type'=>'varchar(200)',
            'type' => 'string',
            'length' => 200,
            'label' => app::get('syslogistics')->_('物流公司简称'),
            'comment' => app::get('syslogistics')->_('物流公司简称'),
            'required' => true,
            'is_title' => true,
            'in_list' => true,
            'default_in_list'=>true,
            'order' => 6,
        ),
    ),
    'primary' => 'id',
    'index' => array(
        'ind_corp_id' => ['columns' => ['corp_id','user_id'],],
    ),
    'comment' => app::get('syslogistics')->_('平台展销签约物流公司表'),
);
