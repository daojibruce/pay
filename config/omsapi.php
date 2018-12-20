<?php
/**
 * 对OMS开放对接接口
 *
 * @license	qj licence
 *
 * @author	daojibruce
 * @date	2017-07-20 17:09
 */
return [
	'routes' => [
		'v1' => [
			'oms.login' => ['uses' => 'oms_api_login@handle', 'version'=>['v1']],
		],
	]
];