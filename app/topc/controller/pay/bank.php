<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topc_ctl_pay_bank extends topc_controller {

	public function __construct(&$app) {
		parent::__construct();
		kernel::single('base_session')->start();

		if (!$this->action) $this->action = 'index';

		$this->action_view = $this->action . ".html";

		// 检测是否登录
		if (!userAuth::check()) {
			redirect::action('topc_ctl_passport@signin')->send();
			exit;
		}
		$this->limit = 10;

		$this->passport = kernel::single('topc_passport');

		$this->gw = kernel::single('b2bpay_gateway');

		//$this->userinfoMdl = app::get('sysuser')->model('user_info');
	}

	//银行卡列表
	public function bank_list() {
		$userId = userAuth::id();
		$bank_list = [];//$this->userinfoMdl->getList('*', array('user_id' => $userId, 'is_pass' => '1'));
		$pagedata['bank_list'] = $bank_list;
		$pagedata['b2bpay_bind'] = config::get('b2bpay.bind');
		$pagedata['action'] = 'topc_ctl_pay_bank@bank_list';
		$this->action_view = "list.html";
		return $this->output($pagedata);
	}

	//绑定银行卡
	public function bindBankCard(){

		//银行配置信息
		$pagedata['b2bpay_bind'] = config::get('b2bpay.bind');

		//接收值
		$input = input::get();
		$id = $input['id'];
		$bank_info = array();
		if($id) {
			//$bank_info = $this->userinfoMdl->getRow('*', array('user_id' => $userId, 'is_pass' => '1'));
		}

		$userId = userAuth::id();

		//会员信息
		$userInfo = userAuth::getUserInfo();
		if (isset($_SESSION['account']['member']['slave'])) {
			//如果子账号登陆，则使用子账号信息
			$userId = $_SESSION['account']['member']['slave']['id'];
			$userInfo = kernel::single('sysuser_passport')->memInfo($userId);
			$pagedata['is_slave'] = true;
		}
		$pagedata['userinfo'] = $userInfo;
		$pagedata['action'] = 'topc_ctl_pay_bank@bindBankCard';
		$pagedata['bank_info'] = $bank_info;
		$this->action_view = "bindbankcard.html";
		return $this->output($pagedata);
	}

	//保存提现银行卡
	public function saveBankInfo(){
		// $postData = utils::_filter_input(input::get());
		$postdata = input::get();

		$userId = userAuth::id();
		$userInfo = userAuth::getUserInfo();

		//支行信息
		$bankinfo['bankname'] = '';
		//$bankinfo = app::get('b2bpay')->model('cnaps_bankinfo')->getRow('bankname', ['bankno' => $postdata['bankName']]);

		//判断是平安银行
		$bankType = "2";
		if($postdata['bankCode'] == '307') {
			$bankType = "1";
		}
		$bankCode = $postdata['bankName'];

		if($postdata['bindtype'] == '0'){
			//小额鉴权【6055】
			$params = [
				"TranFunc" => "6055", //交易码
				"CustAcctId" => $userInfo['cust_account_id'], //子账户账号
				"ThirdCustId" => $userInfo['login_account'], //交易网会员代码
				"CustName" => $postdata['CustName'], //会员名称
				"IdType" => $postdata['IdType'],//会员证件号码
				"IdCode" => $postdata['IdCode'], //会员证件号码
				"AcctId" => $postdata['AcctId'], //会员账号
				"BankType" => $bankType, //银行类型  1：本行 2：他行
				"BankName" => $bankinfo['bankname'], //开户行名称
				"BankCode" => $bankCode, //大小额行号
				"MobilePhone" => $postdata['MobilePhone'], //手机号
				"Reserve" => "little", //保留域
			];
			$this->gw->request($params);

			//发送账户信息
			$res = $this->gw->response();
			logger::info("bind:debug:saveRamount： \n" . var_export($this->gw->params, 1) . "\n" . var_export($res, 1));
			if($res['RspCode'] == '000000'){
				$data = [
					'user_id' => $userId,
					'cust_name' => $params['CustName'],
					'id_type' => $params['IdType'],
					'id_code' => $params['IdCode'],
					'acct_id' => $params['AcctId'],
					'bank_code' => $postdata['bankCode'],
					'no_acct_id' => substr($params['AcctId'],-4),
					'bank_type' => $params['BankType'],
					'bank_name' => $params['BankName'],
					'mobile_phone' => $params['MobilePhone'],
					'bank_no' => $params['BankCode'],
				];
				//$res = $this->userinfoMdl->save($data);

				$url = url::action('topc_ctl_pay_bank@sendRamount', ['AcctId' => $params['AcctId']]);
				$msg = app::get('topc')->_('请输入鉴权金额');
				return $this->splash('success', $url, $msg);
			}else{

				//$url = url::action('topc_ctl_pay_bank@bindBankCard');
				$rspCode=config::get('rspCodes');
				if($rspCode[$res['RspCode']]){
					$msg= $rspCode[$res['RspCode']];
				}
				else
					$msg = app::get('topc')->_('账户无效，请重新绑定!');
				return $this->splash('error', '', $msg,true);
			}
		}
		else{
			//银联验证【6066】
			$params = [
				"TranFunc" => "6066", //交易码
				"CustAcctId" => $userInfo['cust_account_id'], //子账户账号
				"ThirdCustId" => $userInfo['login_account'], //交易网会员代码
				"CustName" => $postdata['CustName'],//名称
				"IdType" => $postdata['IdType'],	//证件类型
				"IdCode" => $postdata['IdCode'],	//证件号码
				"AcctId" => $postdata['AcctId'], //会员账号
				"BankType" => $bankType, //银行类型  1：本行 2：他行
				"BankName" => $bankinfo['bankname'], //开户行名称
				"BankCode" => $bankCode, //大小额行号
				"MobilePhone" => $postdata['MobilePhone'], //手机号
				"Reserve" => "lot", //保留域
			];
			$this->gw->request($params);
			//发送账户信息
			$res = $this->gw->response();
			// $res['RspCode'] = '000000';
			if($res['RspCode'] == '000000'){
				$data = [
					'user_id'	=> $userId,
					'cust_name' => $params['CustName'],
					'id_type'	=> $params['IdType'],
					'id_code'	=> $params['IdCode'],
					'acct_id'	=> $params['AcctId'],
					'bank_code' => $postdata['bankCode'],
					'no_acct_id'=> substr($params['AcctId'],-4),
					'bank_type'	=> $params['BankType'],
					'bank_no'	=> $params['BankCode'],
					'bank_name'	=> $bankinfo['bankname'],
					'mobile_phone'	=> $params['MobilePhone'],
				];
				//$res = $this->userinfoMdl->save($data);

				$url = url::action('topc_ctl_pay_bank@sendcode', ['AcctId' => $params['AcctId']]);
				$msg = app::get('topc')->_('请输入短信验证码');
				return $this->splash('success', $url, $msg);
			}else{

				$rspCode=config::get('rspCodes');
				if($rspCode[$res['RspCode']]){
					$msg= $rspCode[$res['RspCode']];
				}
				else
					$msg = app::get('topc')->_('账户无效，请重新绑定!');
				return $this->splash('error', '', $msg,true);
			}
		}

	}

	//小额鉴权 金额验证主页
	public function sendRamount() {
		$input	= input::get();
		$userId	= userAuth::id();
		//会员信息
		$userInfo = userAuth::getUserInfo();
		$this->action_view = "sendranamo.html";
		$params['userinfo'] = $userInfo;
		$params['AccId'] = $input['AcctId'];
		return $this->output($params);
	}

	//验证小额鉴权 金额
	public function saveRamount(){
		$postData = input::get();
		$userId = userAuth::id();
		$userInfo = userAuth::getUserInfo();
		//$user = app::get('sysuser')->model('user_info')->dump(array('user_id'=>$userId),'*');
		//验证鉴权金额
		$acc_id = trim($postData['AccId']);
		$params = [
			"TranFunc" => "6064",
			"CustAcctId" => $userInfo['cust_account_id'],
			"ThirdCustId" => $userInfo['login_account'],
			"AcctId" => $acc_id,
			"TranAmount" => $postData['randomamo'],
			"CcyCode" => "RMB",
			"Reserve" => "little",
		];
		$this->gw->request($params);
		//发送账户信息
		$res = $this->gw->response();
		//logger::info("\nbind:debug:saveRamount： \n" . var_export($this->gw->params, 1) . "\n" . var_export($res, 1));
		// $res['RspCode'] = '000000';
		if($res['RspCode'] == '000000'){
			//修改绑定体现账户状态
			$where = ['user_id' => $userId, 'acct_id' => $acc_id];
			app::get('sysuser')->model('user_info')->update(array('is_pass' => 1), $where);

			//成功跳转
			$url = url::action('topc_ctl_pay_bank@bank_list');
			$msg = app::get('topc')->_('绑定银行账户成功');
			return $this->splash('success', $url, $msg, true);
		}

		//绑定失败
		//$url = url::action('topc_ctl_pay_bank@sendRamount');
		$rspCode=config::get('rspCodes');
		if($rspCode[$res['RspCode']]){
			$msg= $rspCode[$res['RspCode']];
		}
		else
			$msg = app::get('topc')->_('金额无效，请重新输入!');
		return $this->splash('error', '', $msg, true);
	}

	//银联验证 验证码主页
	public function sendcode(){
		$input	= input::get();
		$userId = userAuth::id();
		//会员信息
		$userInfo = userAuth::getUserInfo();
		$this->action_view = "sendcode.html";
		$params['userinfo'] = $userInfo;
		$params['AccId'] = $input['AcctId'];
		return $this->output($params);
	}

	//验证银联验证 验证码
	public function savecode(){
		$postData = input::get();
		$userId = userAuth::id();
		$userInfo = userAuth::getUserInfo();

		//绑定卡号
		$acc_id = trim($postData['AccId']);

		$params = [
			"TranFunc"		=> "6067",
			"CustAcctId"	=> $userInfo['cust_account_id'],
			"ThirdCustId"	=> $userInfo['login_account'],
			"AcctId"		=> $acc_id,
			"MessageCode"	=> $postData['MessageCode'],
			"Reserve"		=> "anything",
		];
		$this->gw->request($params);
		$res = $this->gw->response();

		//绑定银行卡成功
		if($res['RspCode'] == '000000'){
			//修改绑定体现账户状态
			$where = ['user_id' => $userId, 'acct_id' => $acc_id];
			app::get('sysuser')->model('user_info')->update(array('is_pass' => 1), $where);

			//成功跳转
			$url = url::action('topc_ctl_pay_bank@bank_list');
			$msg = app::get('topc')->_('绑定银行账户成功');
			return $this->splash('success', $url, $msg, true);
		}
		$rspCode=config::get('rspCodes');
		if($rspCode[$res['RspCode']]){
			$msg= $rspCode[$res['RspCode']];
		}
		else
			$msg = app::get('topc')->_('请重新短信验证码');
		return $this->splash('error', '', $msg, true);
	}

	/**
	 * 获取省级区域信息
	 */
	public function getProList(){
		return [];//app::get('b2bpay')->model('pay_node')->getList('node_nodecode,node_nodename');
	}

	/**
	 *  根据省级编号查询该省下的市级信息
	 */
	public function getCityList($city_nodecode){
		return [];// app::get('b2bpay')->model('pay_city')->getList('*',array('city_nodecode'=>$city_nodecode,'city_areatype'=>2));
	}

	/**
	 *  获取区级信息
	 * select * from pub_pay_city where CITY_TOPAREACODE2='598000' and city_areatype='3'
	 */
	public function getDisList($city_topareacode2){
		return [];//app::get('b2bpay')->model('pay_city')->getList('*',array('city_topareacode2'=>$city_topareacode2,'city_areatype'=>3));
	}

	/**
	 * @return mixed
	 */
	public function showC(){
		$proList = $this->getProList();
		$regionArea = [];
		foreach($proList as $pro){
			$info['id'] = $pro['node_nodecode'];
			$info['value'] = $pro['node_nodename'];
			$info['parentId'] = 0;//顶级区域没有父级
			$cityList = $this->getCityList($pro['node_nodecode']);
			if(count($cityList)>0){
				$info['children'] = []; //获取该省级的市级区域
				foreach($cityList as $city){
					$cityInfo = array(
						'id' => $city['city_areacode'],
						'value' => $city['city_areaname'],
						'parentId' => $pro['node_nodecode']
					);
					$disList = $this->getDisList($city['city_topareacode2']);//获取区级信息
					if(count($disList)>0){
						$cityInfo['children'] = [];
						foreach($disList as $dis){
							$disInfo = array(
								'id' => $dis['city_areacode'],
								'value' => $dis['city_areaname'],
								'parentId' => $city['city_topareacode2']
							);
							array_push($cityInfo['children'],$disInfo);
						}
					}
					array_push($info['children'],$cityInfo);
				}
			}
			array_push($regionArea,$info);
		}
		return response::json($regionArea);
	}




	/**
	 * 根据开户银行和所选地区获取开户行列表
	 * select bankno,bankname from zjjz_cnaps_bankinfo where bankclscode='102' and citycode='5982
	 */
	public function getBankList(){
		return response::json([]);
		$filter = input::get();
		$citycode[] = split(',', $filter['citycode']);
		// echo '<pre>';print_r($filter);die;
		// echo '<pre>';print_r($citycode);
		foreach ($citycode as $key => $value) {
			if($value[2]){
				$city = app::get('b2bpay')->model('pay_city')->dump(array('city_areacode'=>$value[2]),'*');
				$data['citycode'] = $city['city_oraareacode'];
			}elseif ($value[1]) {
				$city = app::get('b2bpay')->model('pay_city')->dump(array('city_topareacode2'=>$value[1]),'*');
				$data['citycode'] = $city['city_oraareacode'];
			}else{
				$data['citycode'] = $value[0];
			}
		}
		if (!isset($filter['bankclscode'])) return $this->splash('error', '', '请选择开户银行', true);
		if (!isset($filter['citycode'])) return $this->splash('error', '', '请选择开户地区', true);

		$bankMdl = app::get('b2bpay')->model('cnaps_bankinfo');
		$bankList = $bankMdl->getList('bankno,bankname',array('bankclscode'=>$filter['bankclscode'],'citycode'=>$data['citycode']));
		return response::json($bankList);
	}

	/**
     * @brief 页面输出的统一页面
     *
     * @return html
     */
    private function output($pagedata) {
        $pagedata['cpmenu'] = config::get('usermenu');
        if ($pagedata['_PAGE_']) {
            $pagedata['_PAGE_'] = 'topc/pay/bank/' . $pagedata['_PAGE_'];
        } else {
            $pagedata['_PAGE_'] = 'topc/pay/bank/' . $this->action_view;
        }
        if (!isset($pagedata['userInfo'])) {
            $pagedata['userInfo'] = userAuth::getUserInfo();
        }
        $pagedata['slaveInfo'] = isset($_SESSION['account']['member']['slave']) ? $_SESSION['account']['member']['slave'] : false;
        //var_dump($pagedata['userInfo']);
        return $this->page('topc/member/main.html', $pagedata);
    }
}
