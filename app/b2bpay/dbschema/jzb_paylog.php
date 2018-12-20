<?php
/**
 * 见证宝 交易流水表
 *
 * @author	daojibruce
 * @date	2016/5/11 17:57
 */

return array(
    'columns' => array(

        'id' => array(
            'type' => 'number',
			'autoincrement' => true,
            'required' => true,
            'editable' => false,
            'searchtype' => 'has',
            'label' => app::get('b2bpay')->_('流水id'),
			'comment' => app::get('b2bpay')->_('主键'),
            'order' => '1',
        ),
		'trade_no' => array(
            'type' => 'string',
            'length' => 50,
			'required' => true,
			'default' => '',
            'editable' => false,
            'label' => app::get('b2bpay')->_('交易号'),
			'comment' => app::get('b2bpay')->_('交易号，支付、充值、提现、退款等'),
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
            'filtertype' => 'normal',
            'filterdefault' => 'true',
        ),
		'log_no' => array (
            'type' => 'string',
			'length' => 50,
			'required' => true,
			'default' => '',
			'editable' => false,
			'label' => app::get('b2bpay')->_('交易流水号'),
            'comment' => app::get('b2bpay')->_('第三方交易流水号'),
            'in_list' => true,
            'default_in_list' => true
        ),
        'type' => array(
            'type' => array(
				'order'	=> app::get('b2bpay')->_('订单打款担保'),
				'recharge'	=> app::get('b2bpay')->_('充值'),
				'withdraw'	=> app::get('b2bpay')->_('提现'),
				'refund'	=> app::get('b2bpay')->_('退款'),
                'delivery'  => app::get('b2bpay')->_('担保付款商家'),
			),
            'length' => 20,
			'default' => '',
            'editable' => false,
            'label' => app::get('b2bpay')->_('交易类型'),
			'comment' => app::get('b2bpay')->_('交易类型'),
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
        ),
		'pay_app_id' => array (
            'type' => array(
				'alipay'	=> app::get('b2bpay')->_('支付宝'),
				'pingan'	=> app::get('b2bpay')->_('平安网银'),
			),
			'required'	=> true,
			'default'	=> '',
			'length'	=> 50,
			'label'		=> app::get('b2bpay')->_('支付方式'),
            'comment' => app::get('b2bpay')->_('支付方式类型'),
        ),
        'money' => array (
            'type' => 'money',
            'required' => true,
            'default' => '0',
			'label' => app::get('b2bpay')->_('交易金额'),
            'comment' => app::get('b2bpay')->_('交易金额'),
            'in_list' => true,
            'default_in_list' => true
        ),
		'user_id' => array (
            //'type' => 'varchar(100)',
            'type' => 'table:account@sysuser',
            'length' => 100,
            'label' => app::get('b2bpay')->_('会员'),
            'comment' => app::get('b2bpay')->_('会员'),
            'in_list' => true,
            'default_in_list' => true
        ),

        'shop_id' => array (
            //'type' => 'varchar(100)',
            'type' => 'string',
            'length' => 20,
            'label' => app::get('b2bpay')->_('商家'),
            'comment' => app::get('b2bpay')->_('商家'),
            'default' => '0',
        ),
        'shop_name' => array (
            //'type' => 'varchar(100)',
            'type' => 'string',
            'length' => 50,
            'label' => app::get('b2bpay')->_('商家名称'),
            'comment' => app::get('b2bpay')->_('商家名称'),
            'in_list' => true,
            'default_in_list' => true,
        ),
		'status' => array (
            'type' => array (
				'ready' => app::get('b2bpay')->_('准备中'),
                'paying' => app::get('b2bpay')->_('支付中'),
                'succ' => app::get('b2bpay')->_('支付成功'),
                'failed' => app::get('b2bpay')->_('支付失败'),
            ),
            'required' => true,
            'default' => 'ready',
			'label' => app::get('b2bpay')->_('支付状态'),
            'comment' => app::get('b2bpay')->_('支付状态'),
            'is_title' => true,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
        ),
		'finishtime' => array (
            'type' => 'time',
			'label' => app::get('b2bpay')->_('支付完成时间'),
            'comment' => app::get('b2bpay')->_('支付完成时间'),
            'in_list' => true,
            'default_in_list' => true
        ),
		'addtime' => array (
            'type' => 'time',
			'label' => app::get('b2bpay')->_('添加时间'),
            'comment' => app::get('b2bpay')->_('添加时间'),
            'in_list' => true,
            'default_in_list' => true
        ),
		'updatetime' => array (
            'type' => 'time',
			'label' => app::get('b2bpay')->_('修改时间'),
            'comment' => app::get('b2bpay')->_('修改时间'),
            'in_list' => true,
            'default_in_list' => true
        ),
    ),
    'index' => array(
        'ind_trade_no' => ['columns' => ['trade_no']],
        'ind_log_no' => ['columns' => ['log_no']],
		'ind_type' => ['columns' => ['type']],
		'ind_user_id_log_type' => ['columns' => ['user_id', 'type']],
		'ind_user_id' => ['columns' => ['user_id']],
		'ind_addtime' => ['columns' => ['addtime']],
        'ind_updatetime' => ['columns' => ['updatetime']],
    ),
    'primary' => 'id',
    'comment' => '交易流水表',
    'engine' => 'innodb',
    'version' => '$1.0',
);
