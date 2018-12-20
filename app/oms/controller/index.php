<?php
/**
 * 内部OMS相关页面、操作
 *
 * @author	daojibruce
 * @data	2017-07-14 10:00
 */

class oms_ctl_index extends oms_controller {
	public function __construct() {}

	//开通OMS服务 | 前端异步调用
	public function open() {
		$license = input::get('license', 0);

		if(!$license) {
			return $this->splash('error', '', '请先同意平台协议！', 1);
		}

		//获取公司信息
		$userId = userAuth::id();
		$companyInfo = app::get('sysuser')->mode('user_company')->getRow('user_id,company_name,company_phone,def_addr', ['user_id' => $userId]);

		//组织接口数据|公司信息
		$data['user']['name'] = $companyInfo['company_name'];
		$data['user']['status'] = 10;
		if($companyInfo['def_addr']) $data['user']['person'] = $companyInfo['def_addr'];
		if($companyInfo['company_phone']) $data['user']['phone'] = $companyInfo['company_phone'];

		//oms相关信息
		$data['oms']['domain'] = COMPANY_OMS_DOMAIN;

		//请求oms，开通服务
		$servObj = kernel::single('oms_api');
		$op = $servObj->openOMS($data);

		//开通成功返回
		if($op['status'] == 0) {
			return $this->splash('success', '', '开通成功！', 1);
		}

		//开通失败
		return $this->splash('error', '', $op['msg'], 1);
	}
}
