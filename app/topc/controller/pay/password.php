<?php
/**
 * Created by PhpStorm.
 * User: FHF
 * Date: 16/4/24
 * Time: 上午9:09
 */

class topc_ctl_pay_password extends topc_controller {

	//密码设置首页
	public function index() {
        $user_id = userAuth::id();
        //获取用户绑定的银行卡号
        $bank_list = [];//app::get('sysuser')->model('user_info')->getList('id_type,id_code,acct_id,bank_code,no_acct_id', array('user_id' => $user_id,'is_pass'=>1));
		$bank_config = config::get('b2bpay.bind.bank_list');
        foreach ($bank_list as $info) {
            $data[]    = array(
                'bank_name'  => $bank_config[$info['bank_code']], //银行名称
                'acct_id'    => $info['acct_id'], //银行卡号
                'no_acct_id' => $info['no_acct_id'], //银行卡后四位
            );
            $this->pagedata['bankList'] = $data;
        }
		$this->pagedata['action'] = 'topc_ctl_pay_password@index';
        $this->action_view = "index.html";
		return $this->output($this->pagedata);
	}

	public function iset() {
        $payObj = kernel::single('b2bpay_gateway');
        //银行卡信息
        $postData =utils::_filter_input(input::get());
        //获取用户绑定的银行卡号
        $seller_info = app::get('sysuser')->model('user_info')->getRow('id_type,cust_name,id_code,acct_id,bank_code,no_acct_id,mobile_phone', array('acct_id' => $postData['acct_id']));
        $data = array(
            'idType'    => $seller_info['id_type'], //证件类型
            'idNo'		=> $seller_info['id_code'], //证件号码
            'name'		=> $seller_info['cust_name'], //真实姓名
			'mobile'	=> $seller_info['mobile_phone'], //手机号码
			'accName'	=> $seller_info['cust_name'],
			'accNo'		=> $seller_info['acct_id'],
		);

        $data['P2PType']	= 1;
        $data['type']		= $postData['type'];

        //shop信息
		$user_id = userAuth::id();
        $data['orderid']     = $this->_getOrderId();
        $seller              = app::get('sysuser')->model('user')->getRow('user_id,cust_account_id,third_cust_id', array('user_id' => $user_id));
        $data['custAccId']   = $seller['cust_account_id'];
        $data['thirdCustId'] = $seller['third_cust_id'];

        $html =  $payObj->getHtml($data);
		//dump($payObj->params, 1);

        echo $html;
		//return view::make('topc/pay/password/iset.html',$this->pagedata);
	}

	private function _getOrderId() {
		$sign      = '1'.date("Ymd");
		$microtime = microtime(true);
		mt_srand($microtime);
		$randval = substr(mt_rand(), 0, -3).rand(100, 999);

		return $sign.$randval;
	}

    /**
     * @brief 页面输出的统一页面
     *
     * @return html
     */
    public function output($pagedata) {
        $pagedata['cpmenu'] = config::get('usermenu');
        if ($pagedata['_PAGE_']) {
            $pagedata['_PAGE_'] = 'topc/pay/password/' . $pagedata['_PAGE_'];
        } else {
            $pagedata['_PAGE_'] = 'topc/pay/password/' . $this->action_view;
        }
        if (!isset($pagedata['userInfo'])) {
            $pagedata['userInfo'] = userAuth::getUserInfo();
        }
        $pagedata['slaveInfo'] = isset($_SESSION['account']['member']['slave']) ? $_SESSION['account']['member']['slave'] : false;
        //var_dump($pagedata['userInfo']);
        return $this->page('topc/member/main.html', $pagedata);
    }
}