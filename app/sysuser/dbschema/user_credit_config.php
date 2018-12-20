<?php
return array(
    'columns' => array(
        'id' => array(
            'type' => 'number',
			'autoincrement' => true,
            'in_list' => false,
            'required' => true,
            'default_in_list' => false,
            'label' => app::get('sysuser')->_('id'),
        ),
        'name' => array(
            'type' => 'string',
			'length' => 50,
            'in_list' => true,
            'default_in_list' => true,
            'default' => 0,
            'comment' => app::get('sysuser')->_('加分项'),
            'label' => app::get('sysuser')->_('加分项'),
        ),
        'point' => array(
            'type' => 'number',
            'in_list' => true,
            'default_in_list' => true,
            'default' => 0,
            'comment' => app::get('sysuser')->_('积分值'),
            'label' => app::get('sysuser')->_('积分值'),
        ),
		'module' => array(
            'type' => 'string',
			'length' => 20,
            'in_list' => false,
            'default_in_list' => false,
            'default' => '',
            'comment' => app::get('sysuser')->_('模块'),
            'label' => app::get('sysuser')->_('模块'),
        ),
        'key' => array(
            'type' => 'string',
			'length' => 20,
            'in_list' => true,
            'default_in_list' => true,
            'default' => '',
            'comment' => app::get('sysuser')->_('key'),
            'label' => app::get('sysuser')->_('key'),
        ),
		'url' => array(
            'type' => 'string',
			'length' => 200,
            'in_list' => true,
            'default_in_list' => true,
            'default' => '',
			'width' => 200,
            'comment' => app::get('sysuser')->_('地址'),
            'label' => app::get('sysuser')->_('地址'),
        ),
        'addtime' => array(
            'type' => 'time',
            'comment' => app::get('sysuser')->_('添加时间'),
            'default' => 0,
        ),
    ),
    'primary' => 'id',
    'index' => array(
    ),
    'comment' => app::get('sysuser')->_('会员基础积分配置表'),
);
