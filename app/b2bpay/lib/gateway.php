<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.com/license/gpl GPL License
 */
//require_once(SCRIPT_DIR . "/b2bpay/javabridge/java/Java.inc");
//java_require(SCRIPT_DIR . "/b2bpay/ZJJZ_API_GW.jar");

class b2bpay_gateway {

	private $parmaKeyDict = null;
	private $retKeyDict = null;
	private $msg = null;
	private $log_type;
	public $params = [];

	/**
	 * 公共接口参数信息
	 */
	private $__stl_code;	//仕泰隆企业代码
	private $__stl_account;	//仕泰隆资金汇总账号
	private $__stl_log_no;	//请求流水号

	private $__error_code;

	function __construct() {
		//用于存放生成向银行请求报文的参数
		//$this->parmaKeyDict = new Java('java.util.HashMap');
		//用于存放银行发送报文的参数
		//$this->retKeyDict = new Java('java.util.HashMap');
		//实例化java API类
		//$this->msg = new Java('zjjz_api_gw.ZJJZ_API_GW');

		$this->__stl_code = B2B_BANK_ENTERPRISE_CODE;
		$this->__stl_account = B2B_BANK_SUPER_ACCOUNT_ID;
		$this->__stl_log_no = $this->__get_log_no();

		$this->__error_code = config::get('jzberror');
	}

	/**
	 * 向银行服务端发起请求
	 * @param	array	$params	接口请求参数
						默认初始化
							array(
								'Qydm',		//企业代码
								'SupAcctId',//资金汇总账号
								'ThirdLogNo'//请求流水号
							)
	 *
	 * @return	bool
	 * @throws InvalidArgumentException
	 */
	public function request($params, $log_type = 'order') {

		return array();

		//检验参数是否合法
		$this->__check_data($params);

		//初始化请求参数
		$this->__init_data($params);

		//报文参数
		foreach ($params as $k => $v) {
			$this->parmaKeyDict->put($k, $v);
		}

		$this->log_type = (string)$log_type;

		//生成报文
		$tranMessage = $this->msg->getTranMessage($this->parmaKeyDict);

		//发送请求报文
		try {
			$this->msg->SendTranMessage($tranMessage, B2B_BANK_SERVER_HOST, B2B_BANK_SERVER_PORT, $this->retKeyDict);
			return true;
		} catch (Exception $ex) {
			throw new RuntimeException('JAVA Socket error.');
		}
	}

	/**
	 *  获取银行返回报文
	 * @throws RuntimeException
	 * @return array
	 */
	public function response() {
		return array();
		if (empty($this->retKeyDict)) {
			throw new RuntimeException('Response Message Error.');
		}
		//银行返回的报文
		$recvMessage = $this->retKeyDict->get("RecvMessage");
		//解析返回的报文
		$retKeyDict = $this->msg->parsingTranMessageString($recvMessage);
		//java字符串转成php数组
		$a = java_values($retKeyDict);
		$b = var_export($a, 1);
		eval("\$res = " . $b . '; ');
		//write api log
		kernel::single('b2bpay_log')->api_log($this->params, $res, $this->log_type);
		$res['log_no'] = $this->params['ThirdLogNo'];
		return $res;
	}

	/**
	 * 生成银行请求签名
	 *
	 * @param	array	$params	组织的参数，接口数据
						默认初始化
							array(
								'Qydm',		//企业代码
								'SupAcctId',//资金汇总账号
								'ThirdLogNo'//请求流水号
							)
	 *
	 * @return	string
	 */
	public function getSign($params) {

		//检验参数是否合法
		$this->__check_data($params);

		//初始化请求参数
		$this->__init_data($params);

		//报文参数
		foreach ($params as $k => $v) {
			$this->parmaKeyDict->put($k, $v);
		}

		//发送请求报文
		try {
			return $this->msg->getSignMessage($this->parmaKeyDict);
		} catch (Exception $ex) {
			throw new RuntimeException('Create sign error.');
		}
	}

	/**
	 * 生成银行请求自动提交表单
	 *
	 * 简单的form的自动提交的代码。
	 *
	 * @param	array	$params	组织的参数，表单数据
						默认初始化
							array(
								'P2PCode',	//企业代码，即Qydm 企业代码
								'P2PType'	//平台代扣类型，2
							)
	 * @param	array	$method	表单提交方式，默认POST
	 *
	 * @return	html
	 */
	public function getHtml($params = array(), $method = 'POST') {

		//表单初始化参数
		$this->__init_form_data($params);

		header('Content-Type: text/html;charset=utf8');

		$strHtml ='<!DOCTYPE html>
		<head><meta charset="UTF-8"></head>
		<body>
		<div>Redirecting...</div>';

		$strHtml .= '<form action="' . JZB_GATEWAY_URL . '" method="' . $method . '" name="pay_form" id="pay_form">';

		// Generate all the hidden field.
		foreach ($params as $key => $value)
		{
			$strHtml .= "
			<input type=\"hidden\" name=\"{$key}\" value=\"{$value}\" />";
		}

		$strHtml .= '
			</form>
			<script type="text/javascript">
				window.onload = function(){
					document.getElementById("pay_form").submit();
				}
			</script>
		</body>
		</html>';

		return $strHtml;
	}

	/**
	 * 初始化共有数据
	 *
	 * @param	array	$params	组织的参数，接口数据
	 *
	 * @return	string
	 */
	private function __init_data(&$params) {
		//企业代码
		$params['Qydm'] = $this->__stl_code;

		//资金汇总账号
		$params['SupAcctId'] = $this->__stl_account;

		//请求流水号
		//if (!isset($params['ThirdLogNo'])) {
		$params['ThirdLogNo'] = substr($this->__stl_log_no, 0, 20);
		//}
		//查询小额鉴权流水号

		$this->params = $params;
	}

	/**
	 * 初始化表单共有数据
	 *
	 * @param	array	$params	组织的参数，表单数据
	 *
	 * @return	string
	 */
	private function __init_form_data(&$params) {
		//企业代码
		if (!isset($params['P2PCode'])) {
			$params['P2PCode'] = $this->__stl_code;
		}
		//平台类型，2非代扣
		if (!isset($params['P2PType'])) {
			$params['P2PType'] = 2;
		}

		$this->params = $params;
	}

	/**
	 * 初始化共有数据
	 *
	 * @param	array	$params	组织的参数，接口数据
	 *
	 * @return	string
	 */
	private function __check_data($params) {
		if (empty($params)) {
			throw new \InvalidArgumentException('To request bank\'s params are required.');
		}

		//接口交易码必填验证
		$tranFunc = isset($params['TranFunc']) ? trim($params['TranFunc']) : '';
		if(!$tranFunc) throw new \InvalidArgumentException('TranFunc code empty.');
	}

	//生成接口请求流水号
	private function __get_log_no() {
		$randStr = strtoupper(md5(uniqid(md5(microtime(true)),true)));
		return $randStr;
	}

	//生成请求的 xml报文
	private function __get_xml($arr = []) {
		$common_head = [];
		$body = [];
		$data['BEDC'] = ['Message' => [['commHead' => $common_head], ['Body' => $body]]];
		$xml = kernel::single('base_xml2')->toXml($arr);
		return $xml;
	}
}
