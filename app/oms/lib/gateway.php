<?php
/**
 * 千匠 直连 oms请求基类
 *
 * @author	daojibruce
 * @data	2017-07-13 10:42
 */

class oms_gateway {

	//请求地址
	public $url = 'http://saasapi.cloud.qianjiangcloud.com/';

	//加密字符串
	private $__signStr = 123456;//'#@$GFIK%*&)123ss';
	private $__errorMsg = '';

	function __construct() {

		//$this->url = '';
	}

	/**
	 * 公共接口请求入口
	 *
	 * @param	array	$params	请求参数
	 *
	 * @return	mixed
	 */
	public function call($api, $params = []) {
		$url .= $this->url . $api;
		$sign = $this->_getSign($params);
		$params['sign'] = $sign;

		$rs = $this->_request($url, $params);
		$ret = $this->_parse($rs);
		return $ret;
	}

	/**
	 * 根据参数获取sign
	 *
	 * @param	array	$params	参数
	 *
	 * @return	md5
	 */
	protected function _getSign($params = []) {
		ksort($params);
		$str = '';
		foreach($params AS $k => $v) {
			$str .= $k . $v;
		}
		$str .= $this->__signStr;
		$sign = md5($str);

		return $sign;
	}

	/**
	 * 公共发起请求
	 *
	 * @param	string	$url	请求地址
	 * @param	array	$params	请求参数
	 *
	 * @return	mixed
	 */
	protected function _request($url, $params = []) {
	}

	/**
	 * 公共解析接口返回
	 *
	 * @param	string	$url	请求地址
	 * @param	array	$params	请求参数
	 *
	 * @return	mixed
	 */
	protected function _parse() {
	}

}
