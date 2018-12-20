<?php
return  array(
    'columns'=> array(
		'temai_day_id'=> array(
			'type' => 'number',
			'autoincrement' => true,
			'required' => true,
			'comment' => app::get('systemai')->_('平台展销排期表主键'),
		),
		'temai_id' => array(
			'type' => 'number',
			'required' => true,
			'comment' => app::get('systemai')->_('平台展销ID'),
		),
        'day' => array(
            'type' => 'number',
            'required' => true,
            'comment' => app::get('systemai')->_('平台展销日期'),
        ),
    ),
    'primary' => 'temai_day_id',
    'index' => array (
        'ind_temai_id' => ['columns' => ['temai_id']],
        'ind_day' => ['columns' => ['day']],
    ),
    'comment' => app::get('systemai')->_('平台展销排期表'),
);