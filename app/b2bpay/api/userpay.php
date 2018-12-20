<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 */
class b2bpay_api_userpay {

    /**
     * 接口作用说明
     */
    public $apiDescription = '用户支付接口';

    public function getParams()
    {
        $return['params'] = array(
			'TranFunc' => array(	//业务号
				'type'=>'string', 'valid'=>'required', 'example'=>'6006', 
				'description'=>'业务号'),
			'Qydm' => array(	//平台代码
				'type'=>'string', 'valid'=>'required', 'example'=>'3006', 
				'description'=>'平台代码'),
			'FuncFlag' => array(	//功能标志
				'type'=>'string', 'valid'=>'required', 'example'=>'1', 
				'description'=>'功能标志'),
            'SupAcctId' => array(	//付款主帐号
				'type'=>'string', 'valid'=>'required', 'example'=>'11014166568005', 
				'description'=>'资金汇总账号'),

            'OutCustAcctId' => array(//付款子帐号
				'type'=>'string', 'valid'=>'required', 'example'=>'888100000291344', 
				'description'=>'转出子账户'),
            'OutThirdCustId' => array(	//转出会员代码
				'type'=>'string', 'valid'=>'required', 'example'=>'G0001', 
				'description'=>'转出会员代码'),
            'OutCustName' => array(	//转出子账户名称
				'type'=>'string', 'valid'=>'required', 'example'=>'资金监管七', 
				'description'=>'转出子账户名称'),
            'InCustAcctId' => array(//转入子账户
				'type'=>'string', 'valid'=>'required', 'example'=>'888100000291424', 
				'description'=>'转入子账户'),
            'InThirdCustId' => array(//转入会员代码
				'type'=>'string', 'valid'=>'required', 'example'=>'G0004', 
				'description'=>'转入会员代码'),
            'InCustName' => array(//转入子账户名称
				'type'=>'string', 'valid'=>'required', 'example'=>'资金测试七', 
				'description'=>'转入子账户名称'),
            'TranAmount' => array(//交易金额
				'type'=>'string', 'valid'=>'required', 'example'=>'0.01', 
				'description'=>'交易金额'),
            'TranFee' => array(//交易费用，平台收取交易费用
				'type'=>'string', 'valid'=>'required', 'example'=>'0.00', 
				'description'=>'交易费用，平台收取交易费用'),
            'TranType' => array(//交易类型，01普通交易
				'type'=>'string', 'valid'=>'required', 'example'=>'01', 
				'description'=>'交易类型'),
            'CcyCode' => array(//币种
				'type'=>'string', 'valid'=>'required', 'example'=>'RMB', 
				'description'=>'币种'),
            'ThirdHtId' => array(//订单号
				'type'=>'string', 'valid'=>'required', 'example'=>'2016042653052673', 
				'description'=>'订单号'),

            'ThirdHtMsg' => array(//订单内容
				'type'=>'string', 'valid'=>'', 'example'=>'', 
				'description'=>'订单内容'),
            'Note' => array(//备注
				'type'=>'string', 'valid'=>'', 'example'=>'', 
				'description'=>'备注'),
            'Reserve' => array(//保留域
				'type'=>'string', 'valid'=>'', 'example'=>'', 
				'description'=>'保留域'),
            'WebSign' => array(//网银签名
				'type'=>'string', 'valid'=>'', 'example'=>'', 
				'description'=>'网银签名'),
		);

        return $return;
    }

    public function pay($params)
    {
        if($params['oauth'])
        {
            $params['user_id'] = $params['oauth']['account_id'];
            unset($params['oauth']);
        }
        if(!$params['user_id'])
        {
            //throw new \LogicException(app::get('sysrate')->_('您已退出登录！'));
        }

		unset($params['user_id']);

        $payObj = kernel::single('b2bpay_gateway');
		$payObj->request($params);
		$rs = $payObj->response();
		if($rs['RspCode'] === '000000') return true;

        return false;
    }
}

