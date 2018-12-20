<?php
return array(
    'columns' => array(
		'id' => array(
            'type' => 'number',
			'autoincrement' => true,
            'required' => true,
            'in_list' => false,
            'default_in_list' => false,
            'comment' => app::get('sysuser')->_('id'),
        ),
        'user_id' => array(
            'type' => 'table:user',
            'required' => true,
            'in_list' => false,
            'default_in_list' => false,
            'label' => app::get('sysuser')->_('会员'),
        ),
        'type' => array(
            'type' => [
				'base'	=> '基础积分',
                'seller'	=> '商城积分',
                'temai'	=> '平台展销积分',
                'proofing'	=> '服务撮合积分',
			],
            'length' => 20,
            'in_list' => true,
            'default_in_list' => true,
            'default' => 0,
            'comment' => app::get('sysuser')->_('积分获取类型'),
            'label' => app::get('sysuser')->_('积分获取类型'),
        ),
        'points' => array(
            'type' => 'number',
            'in_list' => true,
            'default_in_list' => true,
            'default' => 0,
            'comment' => app::get('sysuser')->_('获取积分'),
            'label' => app::get('sysuser')->_('获取积分'),
        ),
		'content' => array(
            'type' => 'string',
            'in_list' => true,
            'default_in_list' => true,
            'default' => 0,
            'comment' => app::get('sysuser')->_('说明'),
            'label' => app::get('sysuser')->_('说明'),
        ),
		'addtime' => array(
            'type' => 'time',
            'in_list' => true,
            'default_in_list' => true,
            'default' => 0,
            'comment' => app::get('sysuser')->_('生成时间'),
            'label' => app::get('sysuser')->_('生成时间'),
        ),
    ),
    'primary' => 'id',
    'index' => array(
		'ind_user_id' => ['columns' => ['user_id']],
		'ind_addtime' => ['columns' => ['addtime']],
    ),
    'comment' => app::get('sysuser')->_('会员信用积分日志'),
);
