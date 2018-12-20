<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return array (
  'columns' =>
  array (
    'id' => array(
        'type' => 'number',
		'autoincrement' => true,
    ),
    'type' => array(
        'type' => 'string',
		'length' => 50,
        'comment' => app::get('base')->_('唯一生成类型 user:用户编号,xx:xx'),
    ),
	'number' => array(
        'type' => 'string',
		'length' => 20,
        'comment' => app::get('base')->_('生成唯一标识'),
    ),
	'date' => array(
        'type' => 'number',
        'comment' => app::get('base')->_('当天日期，如20170520'),
    ),
	//其他
  ),
  'primary' => ['id'],
  'index'	=> [
	'ind_type'	=> ['columns' => ['type']],
	'ind_date'	=> ['columns' => ['date']],
	'ind_number_date'	=> ['columns' => ['number', 'date']],
  ],
  'comment' => app::get('base')->_('唯一标识生成表'),
);
