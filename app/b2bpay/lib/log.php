<?php

/**
 * 日志类
 * @copyright shopex.cn
 */
class b2bpay_log {

	/**
	 * 接口 调用日志
	 *
	 * @param	array	$params	请求参数
	 * @param	array	$msg	请求回应信息
	 * @param	string	$log_type	日志类型类型
	 * @param	array	$addon	其他信息
	 *
	 * @return boolean
	 */
	public function api_log($params = [], $msg = [], $log_type = 'order', $addon = []) {
		$time = time();
		$log_id = $params['ThirdLogNo'];
		$log_sdf = [
			'log_no'	=> $log_id,
			'tran_func' => $params['TranFunc'],
			'params'	=> serialize($params),
			'api_type'	=> 'request', //发起
			'log_type'	=> $log_type,
			'status'	=> $msg['RspCode'] == '000000' ? 'success' : 'fail', // 默认值:失败
			'msg'		=> serialize($msg),
			'createtime'	=> $time,
			'last_modified'	=> $time,
		];
		if (!empty($addon) && is_array($addon)) {
			$log_sdf = array_merge($log_sdf, $addon);
		}

		if (app::get('b2bpay')->model('api_log')->save($log_sdf)) {
			return $log_id;
		}


		return false;
	}

	/**
	 * 交易流水 日志记录
	 * （包括 支付、退款、充值、提现）
	 *
	 * @param	array	$params		请求参数
	 * @param	string	$log_type	日志类型类型
						= array(
							'order',	//订单
							'recharge',	//充值
							'withdraw',	//提现
							'refund',	//退款
						)
	 *
	 * @return	boolean
	 */
	public function pay_log($params = [], $log_type = 'order') {
		$time = time();

		$log_sdf = [
			'trade_no'	=> $params['trade_no'] ? $params['trade_no'] : $this->number(),	//交易单号
			'log_no'	=> $params['log_no'],	//接口请求流水号
			'type'		=> $log_type,	//交易类型
			'money'		=> $params['money'],	//交易金额
			'user_id'	=> !empty($params['user_id'])?$params['user_id']:'0',	//操作用户
			'status'	=> $params['status'] ? $params['status'] : 'ready', //状态 默认值:ready
			'addtime'	=> $params['created_time'],
			'updatetime'=> $params['modified_time'],
			'finishtime'=>  $time,
			'shop_id'   =>  !empty($params['shop_id'])?$params['shop_id']:'0',
			'shop_name' =>  !empty($params['shop_name'])?$params['shop_name']:'',
		];

		$id	= app::get('b2bpay')->model('jzb_paylog')->save($log_sdf);
		if ($id) {
			return $id;
		}

		return false;
	}


	/**
	 * 生成交易流水号
	 *
	 * @access	public
	 *
	 * @return	string	number
	 */
	public function number() {
		$md5 = md5(uniqid(md5(microtime(true)),true));
		$number = date('YmdHis') . substr(0, 18, strtoupper($md5));

		return $number;
	}

}
