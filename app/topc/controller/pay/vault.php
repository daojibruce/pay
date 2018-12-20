<?php
/**
 * Created by PhpStorm.
 * User: 公子彰
 * Date: 16/4/24
 * Time: 上午9:09
 */

class topc_ctl_pay_vault extends topc_ctl_member{

    /**
	 * 资金管理首页 充值、提现
	 */
	public function index()
    {
        $this->action_view = "vault.html";

        $payObj = kernel::single('b2bpay_gateway');
        $userId = userAuth::id();

        //获取可用余额
		$objMdlUser = app::get('sysuser')->model('user');
        $userInfo = $objMdlUser->getRow('third_cust_id',array('user_id'=>$userId));
        $third_cust_id = $userInfo['third_cust_id'];

        $params = [
            "TranFunc" => "6037",
            "ThirdCustId" => $third_cust_id,
        ];
        $payObj->request($params);
        $res = $payObj->response();
        $pagedata['TotalBalance'] = $res['TotalBalance'];
        $pagedata['TotalFreezeAmount'] = $res['TotalFreezeAmount'];//担保金额
		$pagedata['action'] = 'topc_ctl_pay_vault@index';
        return $this->output($pagedata);
    }

    /**
	 * 充值页面
	 */
	public function rechargedetail(){
        $this->action_view = "rechargedetail.html";
        $payObj = kernel::single('b2bpay_gateway');
        $userId = userAuth::id();

        $objMdlSeller = app::get('sysuser')->model('account');
        $shopdata = $objMdlSeller->getRow('login_account',array('user_id'=>$userId));
        $login_account = $shopdata[login_account];

        $params = [
            "TranFunc" => "6037",
            "ThirdCustId" => $login_account,
        ];
        $payObj->request($params);
        $res = $payObj->response();
        $pagedata['TotalBalance'] = $res['TotalBalance'];
        return $this->output($pagedata);
    }
    /**
     * 充值操作
     */
    public function recharge(){
        $userId = userAuth::id();
        $recharge_money = input::get('recharge_money', 0);

		if($recharge_money < 0) {
			return $this->splash('error', '', '充值金额异常！',true);
		}

		try {
			$data_r = array(
				'money'	=> $recharge_money,
				'user_id' => $userId,
			);
			$paymentId = kernel::single('topc_payment')->createRPayment($data_r);
		} catch(Exception $e) {
			return $this->splash('error', '', '充值异常！',true);
		}
		//return $this->splash('error', '', $paymentId,true);

		//增加见证宝充值记录
		$log['trade_no']= $paymentId;
		$log['money']	= $recharge_money;
		$log['user_id']	= $userId;
		$log['status']	= 'paying';//支付中
		kernel::single('b2bpay_log')->pay_log($log, 'recharge');

		$url = url::action('topc_ctl_paycenter@index', ['payment_id' => $paymentId, 'r' => 1]);
		return $this->splash('success', $url, '',true);



        //获取用户帐号信息
		$objMdlUser = app::get('sysuser')->model('user');
        $userdata = $objMdlUser->getRow('cust_account_id,third_cust_id', array('user_id' => $userId));

        $cust_account_id = $userdata['cust_account_id'];
        $third_cust_id = $userdata['third_cust_id'];

        $payObj = kernel::single('b2bpay_gateway');
        $params = [
            "TranFunc" => "6056",//交易码
            "CustAcctId" => $cust_account_id,//会员子账号
            "ThirdCustId" => $third_cust_id,//会员代码
            "TranAmount" => $recharge_money,//清分金额
            "CcyCode" => "RMB",//币种
            "Note" => "fuyun",//备注 可选
            "Reserve" => "anything",//保留域 可选
        ];
        $payObj->request($params);
        $res = $payObj->response();

        if($res['RspCode'] == '000000') {
            return $this->splash('success', '', '充值成功',true);
        }
        return $this->splash('error', '', '充值失败',true);

    }

	/**
	 * 提现页面
	 */
    public function withdrawalsdetail(){
        $this->action_view = "withdrawalsdetail.html";
        $payObj = kernel::single('b2bpay_gateway');
        $userId = userAuth::id();

        $objMdlSeller = app::get('sysuser')->model('account');
        $shopdata = $objMdlSeller->getRow('login_account',array('user_id'=>$userId));
        $login_account = $shopdata[login_account];

        $params = [
            "TranFunc" => "6037",
            "ThirdCustId" => $login_account,
        ];
        $payObj->request($params);
        $res = $payObj->response();
        $pagedata['TotalBalance'] = $res['TotalBalance'];

        /*custom s 获取下拉框银行卡信息*/
        $user_info = [];//app::get('sysuser')->model('user_info')->getList('id_type,id_code,acct_id,bank_code,no_acct_id', array('user_id'=>$userId));
        //$bankCodeMdl = app::get('b2bpay')->model('bankcode');
        foreach ($user_info as $info) {
			$bank_name = '';
            //$bank_name = $bankCodeMdl->getRow('bank_name', array('bank_code' => $info['bank_code']))['bank_name'];
            $data[$info['no_acct_id']]    = array(
                'bank_name'  => $bank_name, //银行名称
                'acct_id'    => $info['acct_id'], //银行卡号
                'no_acct_id' => $info['no_acct_id'], //银行卡后四位
            );
           $pagedata['bankList'] = $data;
        }
        /*custom e 获取下拉框银行卡信息*/

        return $this->output($pagedata);
    }

    public function withdrawals(){
        return;
        $userId = userAuth::id(); //记录会员ID
        $userModel = app::get('sysuser')->model('user');

        //买家信息
        $buyer = $userModel->getRow('cust_account_id,third_cust_id', ['user_id' => $userId]);
        $gw = kernel::single('b2bpay_gateway');
        $params = [
            "TranFunc" => "6037",
            "ThirdCustId" => $buyer['third_cust_id'],
        ];
        $gw->request($params);
        $res = $gw->response();
        $TotalBalance = $res['TotalBalance'];

        if($_POST['withdrawals_money'] > $TotalBalance){

            return $this->splash('error', '', '提现金额不能大于账户余额！',true);
        }

        $message = kernel::single('b2bpay_data_6005');
        $params = $message ->__sync_pay($userId,$_REQUEST['bank_car'],$_REQUEST['withdrawals_money']);

        $result = kernel::single('ectools_jzbwithdrawuse');
        $result->doRecharge($params);

        $paymentId = $result['paymentId'];

        return redirect::action('topc_ctl_member_deposit@rechargeResult', ['payment_id'=>$paymentId]);

    }



    /**
     * 提现操作
     */
    public function withdrawals1(){
        $payObj = kernel::single('b2bpay_gateway');
        $userId = userAuth::id();
        $withdrawals_money = input::get('withdrawals_money');
        $detailData = app::get('topc')->rpcCall('user.get.info',array('user_id'=>$userId));
        $cust_account_id = $detailData[cust_account_id];

        $bank_car=$_POST['bank_car'];

        $objMdlSeller = app::get('sysuser')->model('account');
        $userdata = $objMdlSeller->getRow('login_account',array('user_id'=>$userId));
        $login_account = $userdata[login_account];

        $bankdata=app::get('sysuser')->model('user_info')->getRow('cust_name,acct_id,id_type,id_code',array('user_id'=>$userId,'acct_id'=>$bank_car));

        $bankdata['id_type'] = (string)$bankdata['id_type'];

        /*验证支付密码的接口参数*/
        $data = array(
            'idType'    => $bankdata['id_type'], //证件类型
            'idNo'    => $bankdata['id_code'], //证件号码
            'name'  => $bankdata['cust_name'], //真实姓名
            'P2PCode'=>B2B_BANK_ENTERPRISE_CODE,
            'P2PType'=>2,
            'type'=>'V',
            'orderid'=>$this->_getOrderId(),
            'custAccId'=>$cust_account_id,
            'mobile'=>$userdata['mobile'],
            'thirdCustId'=>$login_account,
        );

        /*获取签名的接口参数*/
        $params = [
            "TranFunc" => "6005",//交易码
            "TranWebName" => B2B_BANK_TRAN_WEBNAME,//交易网名称————仕泰隆物流
            "CustAcctId" => $cust_account_id,//会员子账号
            "ThirdCustId" => $login_account,//交易网会员代码
            "CcyCode" => "RMB",//币种
            "Note" => "fuyun",//备注 可选
            "Reserve" => "anything",//保留域 可选
        ];

        $data['orig'] = $payObj->getSign($params);//获取的签名

        $this->pagedata['html'] =  $payObj->getHtml($data);

        /*特别注意：跳转平安银行验证支付密码成功后才可以往下继续，此处没有判断是否成功*/

        $params['IdType']=$bankdata['id_type'];
        $params['IdCode']=$bankdata['id_code'];
        $params['CustName']=$bankdata['cust_name'];
        $params['OutAcctId']=$bankdata['acct_id'];
        $params['OutAcctIdName']=$bankdata['cust_name'];
        $params['TranAmount']=$withdrawals_money;
        // vdump($params, 1);

        $pingan = kernel::single('ectools_pingan');
        $sign = $_POST['sign'];
        $params['WebSign'] = $pingan->_base64_url_decode($sign);

        $payObj->request($params);
        $res = $payObj->response();
        if($res['RspCode'] == '000000') {
            return $this->splash('success', '', '提现成功',true);
        }
        return $this->splash('error', '', '提现失败',true);
	}

    /**
     * @brief 页面输出的统一页面
     *
     * @return html
     */
    public function output($pagedata) {
        $pagedata['cpmenu'] = config::get('usermenu');
        if ($pagedata['_PAGE_']) {
            $pagedata['_PAGE_'] = 'topc/pay/vault/' . $pagedata['_PAGE_'];
        } else {
            $pagedata['_PAGE_'] = 'topc/pay/vault/' . $this->action_view;
        }
        if (!isset($pagedata['userInfo'])) {
            $pagedata['userInfo'] = userAuth::getUserInfo();
        }
        return $this->page('topc/member/main.html', $pagedata);
    }

    private function _getOrderId() {
        $sign      = '1'.date("Ymd");
        $microtime = microtime(true);
        mt_srand($microtime);
        $randval = substr(mt_rand(), 0, -3).rand(100, 999);
        return $sign.$randval;
    }

}