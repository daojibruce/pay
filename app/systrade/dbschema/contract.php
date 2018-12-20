<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/10/28
 * Time: 16:52
 * custom s sjz
 * 合同表
 */

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return  array(
    'columns' => array(
        'shop_id' => array(
            //'type' => 'bigint unsigned',
            'type' => 'bigint',
            'unsigned' => true,
            'default' => '0',
//            'required' => true,
            //'pkey' => true,
            'in_list' => true,
            'is_title' => true,
            'searchtype' => 'has',
            'filtertype' => 'custom',
            'filterdefault' => true,
            'default_in_list' => true,
            'label' => app::get('systrade')->_('店铺id'),
            'width' => '100',
            'order' => 10,
        ),
        'contract_id' => array(
            'type' => 'number',
            //'pkey' => true,
            'autoincrement' => true,
            'required' => true,
            'comment' => app::get('systrade')->_('合同ID'),
        ),
        'temai_user_id' => array(
            'type' => 'table:account@sysuser',
            'required' => true,
            'default' => '0',
            'comment' => app::get('systrade')->_('平台展销会员用户ID'),
        ),
        'user_id' => array(
            'type' => 'table:account@sysuser',
            'default' => 0,
            'comment' => app::get('systrade')->_('会员ID'),
        ),
        'contacts' => array(
            //'type' => 'varchar(50)',
            'type' => 'string',
            'length'=>'50',

            'comment' => app::get('systrade')->_('联系人'),
        ),
        'phone' => array(
            //'type' => 'varchar(50)',
            'type' => 'string',
            'length'=>'13',
            'comment' => app::get('systrade')->_('联系人电话'),
        ),
        'contract_img' => array(
            //'type' => 'varchar(50)',
            'type' => 'text',
            'comment' => app::get('systrade')->_('合同图片'),
        ),

    ),
    'primary' => 'contract_id',
    'comment' => app::get('systrade')->_('合同数据列表'),
);
