<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.com/license/gpl GPL License
 */
//require_once(SCRIPT_DIR . "/b2bpay/javabridge/java/Java.inc");
//java_require(SCRIPT_DIR . "/b2bpay/ZJJZ_API_GW.jar");

class b2bpay_api {

	private $__gateway;

	function __construct() {
		$this->__gateway = kernel::single('b2bpay_gateway');
	}

	/**
	 * 查询账户余额
	 *
	 * @param	string	$account	查询帐号
	 *
	 * @return	array
	 */
	public function get_cash($account = '') {
		if(!$account) return 0;

		$tran_code = '0001';
		$data['account'] = $account;
		$ret = $this->__gateway->request($tran_code, $data);
		return $ret;
	}

	/**
	 * 查询交易状态
	 *
	 * @param	array	$d	查询条件 [
							'tid' => '',	//订单号
							'date' => date('Ymd')	//查询日期
							]
	 *
	 * @return	array
	 */
	public function get_trade($d = []) {

		$config = [
			'2' => '待授权',
			'3' => '部分授权',
			'4' => '授权拒绝',
			'5' => '授权通过',
			'6' => '主机交易成功',
			'7' => '主机交易失败',
			'8' => '状态未知，没有收到后台系',
			'E' => '大额查证',
		];

		$tran_code = '0004';
		$data['origEntseqno']	= $d['tid'];
		$data['origEntdate']	= $d['date'];
		$ret = $this->__gateway->request($tran_code, $data);

		return $config[$ret['hostStatus']];
	}

	/**
	 * 创建虚拟子账户
	 *
	 * @param	string	$d	子帐号信息[
								'sub_account_name',	//虚拟子账号户名
								'name',		//联系人
								'phone',	//联系电话
								'address',	//联系地址
							]
	 *
	 * @return	array
	 */
	public function create_account($d = []) {
		$tran_code = '0073';
		$data['type'] = 1;
		$data['parentacctount']		= PARENT_ACCTOUNT;
		$data['subaccountname1']	= $d['sub_account_name'];
		isset($d['name']) && $data['contactname']	= $d['name'];
		isset($d['phone']) && $data['contactphone']	= $d['phone'];
		isset($d['address']) && $data['contactaddress']	= $d['address'];
		$ret = $this->__gateway->request2($tran_code, $data);

		return $ret['subaccount1'];
	}

	/**
	 * 注销虚拟子账户
	 *
	 * @param	string	$d	子帐号信息[
								'cost_account',	//结算帐号
								'sub_account',	//虚拟子账号
								'name',		//虚拟子账号户名
							]
	 *
	 * @return	boolean
	 */
	public function del_account($d = []) {
		$tran_code = '0073';
		$data['type'] = 2;
		$data['parentacctount']		= $d['cost_account'];
		$data['subaccount1']		= $d['sub_account_name'];
		$data['subaccountname1']	= $d['name'];
		$ret = $this->__gateway->request2($tran_code, $data);

		return $ret['errMsg'] == 'S';
	}

	/**
	 * 冻结、解冻 虚拟子账户
	 *
	 * @param	string	$d	子帐号信息[
								'sub_account',	//虚拟子账号
								'name',		//虚拟子账号户名
								'amount',	//要冻结的金额
							]
	 * @param	enum	$type	1冻结，0解冻  默认:冻结
	 * @param	enum	$type	1账户冻结，2金额冻结  默认:金额冻结
	 *
	 * @return	boolean
	 */
	public function cash_freez($d = [], $type = 1, $freetype = 2) {
		$tran_code = '0074';
		$data['type'] = $type;
		$data['subaccount1']		= $d['sub_account_name'];
		$data['subaccountname1']	= $d['name'];
		$data['freezetype']			= in_array($freetype, [1,2]) ? $freetype : 2;
		$freetype == 2 && $d['amount'];
		$ret = $this->__gateway->request2($tran_code, $data);

		return $ret['errMsg'] == 'Y';
	}

	/**
	 * 转账 虚拟子账户 → 虚拟子账户
	 *
	 * @param	string	$d	转账子帐号信息[
								'pay_vname',	//付款人户名
								'pay_vaccount',	//付款人账号
								'in_vname',		//收款人户名
								'in_vaccount',	//收款人账号
								'money',		//转账金额
							]
	 * @return	string	交易流水号
	 */
	public function cash_transfer($d = []) {
		$tran_code = '0076';
		$data['outAccName']	= $d['pay_vname'];
		$data['outAcc']		= $d['pay_vaccount'];
		$data['inAccName']	= $d['in_vname'];
		$data['outAcc']		= $d['in_vaccount'];
		$data['inAcc']		= $d['money'];
		$ret = $this->__gateway->request2($tran_code, $data);

		return $ret['traceNo'];
	}

	/**
	 * 充值 实体 → 虚拟子账户
	 *
	 * @param	string	$d	转账子帐号信息[
								'pay_name',	//付款人户名
								'pay_account',	//付款人账号
								'in_vname',		//收款人户名
								'in_vaccount',	//收款人账号
								'money',		//转账金额
							]
	 * @return	string	交易流水号
	 */
	public function recharge($d = []) {
		$tran_code = '0077';
		$data['outAccName']	= $d['pay_name'];
		$data['outAcc']		= $d['pay_account'];
		$data['inAccName']	= $d['in_vname'];
		$data['inAcc']		= $d['in_vaccount'];
		$data['amount']		= $d['money'];
		$ret = $this->__gateway->request2($tran_code, $data);

		return $ret['traceNo'];
	}

	/**
	 * 查询虚拟子账户列表
	 *
	 * @param	enum	$status		状态 N正常, C销户, T暂禁, F冻结
	 * @param	integer	$pagesize	每页大小 最大25
 	 * @param	integer	$page		每页大小 最大25
	 *
	 * @return	array	查询结果
	 */
	public function select_vaccount_list($status = 'N', $pagesize = 20, $page = 1) {
		$tran_code = '0078';
		$data['account']	= PARENT_ACCTOUNT;
		$data['state']		= $d['status'];
		$pagesize && $data['pagerow']	= $d['pagesize'];
		$page && $data['querypage']	= $d['page'];
		$ret = $this->__gateway->request2($tran_code, $data);

		return $ret;
	}

}
