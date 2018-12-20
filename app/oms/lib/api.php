<?php
/**
 * oms互联，接口api
 *
 * @author	daojibruce
 * @data	2017-07-13 10:42
 */

class oms_api {

	private $__errorMsg = '';

	function __construct() {
		$baseObj = kernel::single('oms_gateway');
	}

	/**
	 * 开通OMS
	 *
	 * @param	array	$params	请求参数
	 *
	 * @return	mixed
	 */
	public function openOMS($params = []) {
		$rs = $baseObj->call('company/openoms', $params);

		return $rs;
	}

	/**
	 * 是否开通OMS
	 *
	 * @param	array	$params	请求参数
	 *
	 * @return	mixed
	 */
	public function isOpenOMS($params = []) {
		$rs = $baseObj->call('company/isopenoms', $params);

		return $rs;
	}
	/**
	 * 信任登录
	 *
	 * @param	array	$params	请求参数
	 *
	 * @return	mixed
	 */
	public function trustLogin($params = []) {
		$rs = $baseObj->call('host/user/info', $params);

		return $rs;
	}

	/**
	 * 单点登录
	 *
	 * @param	array	$params	请求参数
	 *
	 * @return	mixed
	 */
	public function ssoLogin($params = []) {
		//$rs = $baseObj->call('host/user/info', $params);
		$url = '';

		return $url;
	}
}
