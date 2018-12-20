<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return  array(
    'columns' => array(
        'delivery_id' => array(
            //'type' => 'bigint unsigned',
            'type' => 'bigint',
            'unsigned' => true,
            'required' => true,
            //'pkey' => true,
            'autoincrement' => true,
            'comment' => app::get('syslogistics')->_('配送流水号'),

            'label' => app::get('syslogistics')->_('发货单号'),
            'editable' => false,
            'searchtype' => 'has',
            'filtertype' => 'yes',
            'in_list' => true,
            'default_in_list' => true,
            'order' => 1,
        ),
        'tid' => array(
            //'type' => 'varchar(100)',
            'type' => 'string',
            'length' => 100,

            'label' => app::get('syslogistics')->_('订单号'),
            'comment' => app::get('syslogistics')->_('订单号'),
            'editable' => false,
            'searchtype' => 'has',
            'filtertype' => 'normal',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
            'order' => 2,
        ),
        'user_id' => array(
            'type' => 'table:user@sysuser',
            'label' => app::get('syslogistics')->_('会员用户名'),
            'comment' => app::get('syslogistics')->_('订货会员ID'),
            'editable' => false,
            'filtertype' => 'yes',
            'filterdefault' => true,
        ),
        'seller_id' => array(
            'type' => 'table:seller@sysshop',
            'label' => app::get('syslogistics')->_('商家账号'),
            'comment' => app::get('syslogistics')->_('商家账号'),
            'editable' => false,
            'filtertype' => 'yes',
            'filterdefault' => true,
        ),
        'shop_id' => array(
            'type' => 'table:shop@sysshop',
            'label' => app::get('syslogistics')->_('店铺'),
            'comment' => app::get('syslogistics')->_('店铺ID'),
            'editable' => false,
            'filtertype' => 'yes',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
            'order' => 3,
        ),
        'post_fee' => array(
            'type' => 'money',
            'required' => true,
            'default' => 0,
            'label' => app::get('syslogistics')->_('物流费用'),
            'comment' => app::get('syslogistics')->_('配送费用'),
            'editable' => false,
            'filtertype' => 'number',
            'in_list' => true,
            'default_in_list' => true,
            'order' => 4,
            'width' =>'20',
        ),
        'is_protect' => array(
            'type' => 'bool',
            'default' => 0,
            'required' => true,
            'label' => app::get('syslogistics')->_('是否保价'),
            'comment' => app::get('syslogistics')->_('是否保价'),
            'editable' => false,
            'filtertype' => 'yes',
            'in_list' => true,
            'default_in_list' => false,
            'order' => 20,
        ),
        'dlytmpl_id' => array(
            'type' => 'string',
            'label' => app::get('syslogistics')->_('配送方式'),
            'comment' => app::get('syslogistics')->_('配送方式(货到付款、EMS...)'),
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => true,
        ),
        'logi_id' => array(
            //'type' => 'varchar(50)',
            'type' => 'string',
            'length' => 50,
            'comment' => app::get('syslogistics')->_('物流公司ID'),
            'editable' => false,
            'label' => app::get('syslogistics')->_('物流公司ID'),
        ),
        'logi_name' => array(
            //'type' => 'varchar(100)',
            'type' => 'string',
            'length' => 100,
            'label' => app::get('syslogistics')->_('物流公司'),
            'comment' => app::get('syslogistics')->_('物流公司名称'),
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
            'order' => 5,
        ),
        'corp_code' => array(
            //'type'=>'varchar(200)',
            'type' => 'string',
            'length' => 200,
            'label' => app::get('syslogistics')->_('物流公司代码'),
            'comment' => app::get('syslogistics')->_('物流公司代码'),
            'required' => false,
            'is_title' => true,
            'order' => 5,
        ),
        'logi_no' => array(
            //'type' => 'varchar(50)',
            'type' => 'string',
            'length' => 50,
            'label' => app::get('syslogistics')->_('物流单号'),
            'comment' => app::get('syslogistics')->_('物流单号'),
            'editable' => false,
            'searchtype' => 'tequal',
            'filtertype' => 'normal',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
            'order' => 6,
        ),
        'receiver_name' => array(
            //'type' => 'varchar(50)',
            'type' => 'string',
            'length' => 50,
            'label' => app::get('syslogistics')->_('收货人'),
            'comment' => app::get('syslogistics')->_('收货人姓名'),
        ),
        'receiver_state' => array(
            //'type' => 'varchar(20)',
            'type' => 'string',
            'length' => 20,
            'comment' => app::get('syslogistics')->_('收货人所在省'),
        ),
        'receiver_city' => array(
            //'type' => 'varchar(20)',
            'type' => 'string',
            'length' => 20,
            'comment' => app::get('syslogistics')->_('收货人所在市'),
        ),
        'receiver_district' => array(
            //'type' => 'varchar(20)',
            'type' => 'string',
            'length' => 20,
            'comment' => app::get('syslogistics')->_('收货人所在地区'),
        ),
        'receiver_address' => array(
            //'type' => 'varchar(200)',
            'type' => 'string',
            'length' => 200,
            'label' => app::get('syslogistics')->_('收货人详细地址'),
            'comment' => app::get('syslogistics')->_('收货人详细地址'),
        ),
        'receiver_zip' => array(
            //'type' => 'varchar(20)',
            'type' => 'string',
            'length' => 20,
            'comment' => app::get('syslogistics')->_('收货人邮编'),
        ),
        'receiver_mobile' => array(
            //'type' => 'varchar(20)',
            'type' => 'string',
            'length' => 20,
            'comment' => app::get('syslogistics')->_('收货人手机号'),
        ),
        'receiver_phone' => array(
            //'type' => 'varchar(20)',
            'type' => 'string',
            'length' => 20,
            'comment' => app::get('syslogistics')->_('收货人电话'),
        ),
        't_begin' => array(
            'type' => 'time',
            'label' => app::get('syslogistics')->_('单据创建时间'),
            'comment' => app::get('syslogistics')->_('单据生成时间'),
            'editable' => false,
            'filtertype' => 'time',
            'in_list' => true,
            'default_in_list' => true,
            'order' => 7,
        ),
        't_send' => array(
            'type' => 'time',
            'comment' => app::get('syslogistics')->_('单据结束时间'),
            'editable' => false,
            'label' => app::get('syslogistics')->_('单据结束时间'),
            'in_list' => true,
            'default_in_list' => true,
            'order' => 8,
        ),
        't_confirm' => array(
            'type' => 'time',
            'comment' => app::get('syslogistics')->_('单据确认时间'),
            'editable' => false,
            'label' => app::get('syslogistics')->_('单据确认时间'),
            'in_list' => true,
            'default_in_list' => true,
            'order' => 9,
        ),
        'op_name' => array(
            //'type' => 'varchar(50)',
            'type' => 'string',
            'length' => 50,
            'label' => app::get('syslogistics')->_('操作员'),
            'comment' => app::get('syslogistics')->_('操作者'),
            'editable' => false,
            'searchtype' => 'tequal',
            'filtertype' => 'normal',
            'in_list' => true,
            'default_in_list' => true,
            'order' => 19,
        ),
        'status' => array(
            'type' => array(
                'succ' => app::get('syslogistics')->_('成功到达'),
                'failed' => app::get('syslogistics')->_('发货失败'),
                'cancel' => app::get('syslogistics')->_('已取消'),
                'lost' => app::get('syslogistics')->_('货物丢失'),
                'progress' => app::get('syslogistics')->_('运送中'),
                'timeout' => app::get('syslogistics')->_('超时'),
                'ready' => app::get('syslogistics')->_('准备发货'),
            ),
            'default' => 'ready',
            'required' => true,
            'comment' => app::get('syslogistics')->_('状态'),
            'editable' => false,
            'label' => app::get('syslogistics')->_('状态'),
            'in_list' => true,
            'default_in_list' => true,
            'order' => 10,
        ),
        'memo' => array(
            //'type' => 'longtext',
            'type' => 'text',
            'label' => app::get('syslogistics')->_('备注'),
            'comment' => app::get('syslogistics')->_('备注'),
            'editable' => false,
            'filtertype' => 'normal',
            'in_list' => true,
            'default_in_list' => false,
            'order' => 21,
        ),
        'disabled' => array(
            'type' => 'bool',
            'default' => 0,
            'comment' => app::get('syslogistics')->_('无效'),
            'editable' => false,
            'label' => app::get('syslogistics')->_('无效'),
        ),
    ),
    'primary' => 'delivery_id',
    'index' => array(
        'ind_disabled' => ['columns' => ['disabled']],
        'ind_logi_no' => ['columns' => ['logi_no']],
    ),

    'comment' => app::get('syslogistics')->_('发货单表'),
);
