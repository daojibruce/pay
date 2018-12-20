<?php

/**
 * paycenter.php 支付
 *
 * @author     Xiaodc
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topwap_ctl_paycenter extends topwap_controller {

    protected $payment_icon = array(
            'wapupacp' => 'bbc-icon-unipay pay-style-unipay',
            'deposit' => 'bbc-icon-qianbao pay-style-qianbao',
            'malipay' => 'bbc-icon-zhifubao pay-style-zhifubao',
            'wxpayjsapi' => 'bbc-icon-weixin pay-style-weixin'
    );

    public function __construct($app)
    {
        parent::__construct($app);
        // 检测是否登录
        if( !userAuth::check() )
        {
            if( request::ajax() )
            {
                $url = url::action('topwap_ctl_passport@goLogin');
                return $this->splash('error', $url, app::get('topwap')->_('请登录'), true);
            }
            redirect::action('topwap_ctl_passport@goLogin')->send();exit;
        }
    }

    public function selectHongbao()
    {
        $total = input::get('total');
        $apiParams = [
            'user_id' => userAuth::id(),
            'is_valid'=> 'active',
            'fields'=>'hongbao_id,name,money,id,end_time',
            'page_size'=>'100',
            'used_platform' => "wap",
        ];
        $hongbaoData = app::get('topwap')->rpcCall('user.hongbao.list.get', $apiParams);
        $pagedata['hongbao_list'] = $hongbaoData['list'];

        // 获取当前平台设置的货币符号和精度
        $cur_symbol = app::get('topapi')->rpcCall('currency.get.symbol',array());
        $pagedata['cur_symbol'] = $cur_symbol;
        $pagedata['total'] = $total;
        $pagedata['rediret'] = request::server('HTTP_REFERER');
        $pagedata['active_hongbao_id'] = $_SESSION['pay_user_hongbao_id'];

        return $this->page('topwap/payment/redpacketlist.html', $pagedata);
    }

    public function saveHongbao()
    {
        if( input::get('hongbao_id') )
        {
            $_SESSION['pay_user_hongbao_id'] = explode(',',input::get('hongbao_id'));
        }
        else
        {
            unset($_SESSION['pay_user_hongbao_id']);
        }

        return $this->splash('success', null, null, true);
    }

    public function index($filter=null)
    {
        $this->setLayoutFlag('order_detail');
        if(!$filter)
        {
            $filter = input::get();
        }
        //$paymentsMdl = app::get('ectools')->model('payments');
        //$payInfo=$paymentsMdl->getRow('*',array('payment_id'=>$filter['payment_id']));
        //判断订单是否已支付
        
        $filter['fields'] = "*";
        $paymentBill = app::get('topc')->rpcCall('payment.bill.get',$filter,'buyer');
        if($paymentBill['status'] == "succ"){
            return $this->finish(['payment_id'=>$paymentBill['payment_id']]);
        }

        // 线下支付
        if(isset($filter['tid']) && $filter['tid'])
        {
            $pagedata['payment_type'] = "offline";
            $ordersMoney = app::get('topwap')->rpcCall('trade.money.get',array('tid'=>$filter['tid']),'buyer');

            if($ordersMoney)
            {
                foreach($ordersMoney as $key=>$val)
                {
                    $newOrders[$val['tid']] = $val['payment'];
                    $newMoney += $val['payment'];
                }
                $paymentBill['money'] = $newMoney;
                $paymentBill['cur_money'] = $newMoney;
            }
            $pagedata['trades'] = $paymentBill;
            $pagedata['title'] = app::get('topwap')->_('订单状态');
            return $this->page('topwap/payment/offline.html', $pagedata);
        }

        if($filter['newtrade'])
        {
            $newtrade = $filter['newtrade'];
            unset($filter['newtrade']);
        }

        //获取可用的支付方式列表
        $payType['platform'] = 'iswap';
        $payments = app::get('topwap')->rpcCall('payment.get.list',$payType,'buyer');
        $payments = $this->paymentsSort($payments,'app_order_by');

        // 微信支付  $payment  为某一项支付方式
        foreach($payments as $paymentKey => $payment)
        {

            if(in_array($payment['app_id'], ['wxpayjsapi']))
            {
                if(!kernel::single('topwap_wechat_wechat')->from_weixin())
                {
                    unset($payments[$paymentKey]);
                    continue;
                }

                $payInfo = app::get('topwap')->rpcCall('payment.get.conf', ['app_id' => 'wxpayjsapi']);
                $wxAppId = $payInfo['setting']['appId'];
                $wxAppsecret = $payInfo['setting']['Appsecret'];
                if(!input::get('code'))
                {
                    $url = url::action('topwap_ctl_paycenter@index',$filter);
                    kernel::single('topwap_wechat_wechat')->get_code($wxAppId, $url);
                }
                else
                {
                    $code = input::get('code');
                    $openid = kernel::single('topwap_wechat_wechat')->get_openid_by_code($wxAppId, $wxAppsecret, $code);
                    if($openid == null)
                        $this->splash('failed', 'back',  app::get('topwap')->_('获取openid失败'));
                    $pagedata['openid'] = $openid;
                }
            }
        }

        //检测订单中的金额是否和支付金额一致 及更新支付金额
        $trade = $paymentBill['trade'];
        $tidsListArr = array_keys($trade);
        $tids['tid'] = implode(',',$tidsListArr);
        $ordersMoney = app::get('topwap')->rpcCall('trade.money.get',$tids,'buyer');

        /* start of 处理电子合同版 */
        //格式化实付金额
        $params['fields'] = "tid,dlytmpl_id,status,payment,delivery_goods_time,contract_status,post_fee,pay_type,payed_fee,receiver_state,receiver_city,receiver_district,receiver_address,trade_memo,receiver_name,receiver_mobile,receiver_phone,ziti_addr,ziti_memo,orders.price,orders.num,orders.title,orders.item_id,orders.pic_path,total_fee,discount_fee,buyer_rate,adjust_fee,orders.total_fee,orders.adjust_fee,orders.spec_nature_info,orders.sku_id,created_time,shop_id,need_invoice,invoice_name,invoice_type,invoice_main,activity,cancel_reason,invoice_tfn,invoice_bank_name,invoice_bank_num,invoice_addr,invoice_mobile,position";
        
        $tid = $tidsListArr[0];
        $params['tid'] = $tid;
        $params['user_id'] = userAuth::id();
        $trade = app::get('topwap')->rpcCall('trade.get',$params,'buyer');

        $objMdlelecontract = app::get('systrade')->model('elecontract');
        $elecontractRes = $objMdlelecontract->getRow('*',array('tid' => $tid));
        $payNode=empty($elecontractRes['pay_type']) ? array(): $elecontractRes['pay_type'];

        $payfee = array($payNode['pay_1'],$payNode['pay_2'],$payNode['pay_3'],$payNode['pay_4'],$payNode['pay_5']);
        $numfee = array($payNode['num1'],$payNode['num2'],$payNode['num3'],$payNode['num4'],$payNode['num5']);
        switch ($elecontractRes['is_part_pay']){
            case '0':
                $newMoney = 0;
                foreach($ordersMoney as $key=>$val)
                    $newMoney = ecmath::number_plus(array($newMoney, ecmath::number_minus(array($val['payment'], $val['hongbao_fee']))));
                break;
            case '1':
                $newMoney = $numfee[$trade['position']];
                break;
        }
        /* end of 处理电子合同版 */

        if($ordersMoney)
        {
            foreach($ordersMoney as $key=>$val)
                $newOrders[$val['tid']] = $val['payment'];

            $result = array(
                    'trade_own_money' => json_encode($newOrders),
                    'money' => $newMoney,
                    'cur_money' => $newMoney,
                    'payment_id' => $filter['payment_id'],
            );

            if($newMoney != $paymentBill['cur_money'])
            {
                try{
                    app::get('topwap')->rpcCall('payment.money.update',$result);
                }
                catch(Exception $e)
                {
                    $msg = $e->getMessage();
                    $url = url::action('topwap_ctl_member_trade@tradeList');
                    return $this->splash('error',$url,$msg,true);
                }
                $paymentBill['money'] = $newMoney;
                $paymentBill['cur_money'] = $newMoney;
            }
        }

        $apiParams = [
            'user_id' => userAuth::id(),
            'is_valid'=> 'active',
            'fields'=>'hongbao_id,name,money,id,end_time',
            'page_size'=>'100',
            'used_platform' => "wap",
            ];
        $hongbaoData = app::get('topwap')->rpcCall('user.hongbao.list.get', $apiParams);
        if( !$hongbaoData )
        {
            $pagedata['is_empty_hongbao'] = true;
        }
        else
        {
            if( $_SESSION['pay_user_hongbao_id'] )
            {
                $userHongbaoIds = $_SESSION['pay_user_hongbao_id'];
                $selectHongbaoMoney = 0;
                foreach($hongbaoData['list'] as $row)
                {
                    if(in_array($row['id'], $userHongbaoIds))
                    {
                        $selectHongbao[] = $row;
                        $selectHongbaoMoney = ecmath::number_plus(array($selectHongbaoMoney, $row['money']));
                    }
                }

                if( $selectHongbaoMoney > $newMoney )
                {
                    unset($_SESSION['pay_user_hongbao_id']);
                }
                else
                {
                    $pagedata['select_hongbao_list'] = $selectHongbao;
                    $pagedata['select_hongbao_money'] = $selectHongbaoMoney;
                }
            }
        }

        $pagedata['tids'] = $tids['tid'];
        $pagedata['trades'] = $paymentBill;
        $pagedata['payments'] = $payments;
        $pagedata['newtrade'] = $newtrade;
        $pagedata['title'] = app::get('topwap')->_('订单支付');
        $pagedata['payment_icon'] = $this->payment_icon;
        $pagedata['hasDepositPassword'] = app::get('topwap')->rpcCall('user.deposit.password.has', ['user_id'=>userAuth::id()]);
        return $this->page('topwap/payment/index.html', $pagedata);
    }

    // 创建支付
    public function createPay()
    {
        $filter = input::get();
        $filter ['user_id'] = userAuth::id();
        $filter ['user_name'] = userAuth::getLoginName();
        $ifmerge = $filter['merge'];
        /* custom s sjz */
        //查询是否是线下支付
        $tradeMdl = app::get('systrade')->Model('trade');
        $tradeInfo = $tradeMdl->getRow('pay_type',array('tid'=>$filter['tid']));
        $filter['payment_type'] = $tradeInfo['pay_type'];
        //查询订单支付列表
        $trade_paybillMdl = app::get('ectools')->Model('trade_paybill');

        /* custom e sjz */
        if($filter['merge'])
        {
            $ifmerge = $filter['merge'];
            unset($filter['merge']);
        }

        try
        {
            $paymentId = kernel::single('topwap_payment')->getPaymentId($filter);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            $url = url::action('topwap_ctl_member_trade@tradeList');

            return $this->splash('error', $url, $msg);
        }
        /* custom s sjz */
        $url = url::action('topwap_ctl_paycenter@index', array(
            'payment_id' => $paymentId,
            'merge' => $ifmerge
        ));

        /* custom e sjz */
        return $this->splash('success',$url,'',true);
    }

    // 开始支付
    public function dopayment()
    {
        $postdata = input::get();
        $payment = $postdata['payment'];
        $payment['deposit_password'] = $postdata['deposit_password'];
        $payment['user_id'] = userAuth::id();
        $payment['platform'] = "wap";
        $payment['hongbao_ids'] = implode(',',$_SESSION['pay_user_hongbao_id']);
        unset($_SESSION['pay_user_hongbao_id']);
        try
        {
           app::get('topwap')->rpcCall('payment.trade.pay',$payment);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            $url = url::action('topwap_ctl_paycenter@index',array('payment_id'=>$payment['payment_id']));
            return $this->splash('error', $url, $msg, true);
        }
        $url = url::action('topwap_ctl_paycenter@finish',array('payment_id'=>$payment['payment_id']));
        return $this->splash('success', $url, $msg, true);
    }

    // 支付完成
    public function finish()
    {
        $this->setLayoutFlag('order_detail');
        $postdata = input::get();
        try
        {
            $params['payment_id'] = $postdata['payment_id'];
            $params['fields'] = 'payment_id,status,pay_app_id,pay_name,money,cur_money,payed_time,created_time,return_url';
            $result = app::get('topwap')->rpcCall('payment.bill.get',$params);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
        }

        $apiParams['user_id'] = userAuth::id();;
        $apiParams['tid'] = implode(",",array_column($result['trade'], 'tid'));
        $apiParams['fields'] = "tid,payment,payed_fee,hongbao_fee,status,pay_type";
        $trades = app::get('topc')->rpcCall('trade.get.list',$apiParams);

        $hongbaoMoney = 0;
        $tradeTotalPayment = 0;
        foreach( $trades['list'] as $row )
        {
            $hongbaoMoney = ecmath::number_plus(array($hongbaoMoney, $row['hongbao_fee']));
            $tradeTotalPayment = ecmath::number_plus(array($tradeTotalPayment, $row['payment']));
        }
        $pagedata['hongbao_fee'] = $hongbaoMoney;
        if( $tradeTotalPayment ==  $hongbaoMoney )
        {
            $result['status'] = 'succ';
        }

        $trades = $result['trade_own_money'];
        $result['num'] = count($trades);
        $pagedata['msg'] = $msg;
        $pagedata['payment'] = $result;

        return $this->page('topwap/payment/finish.html', $pagedata);
    }

    //支付方式排序
    public function paymentsSort($payments,$orderBy,$sort_order=SORT_ASC)
    {
        if(is_array($payments)){
            foreach ($payments as $value) {
                if(is_array($value)){
                    $paymentList[] = $value[$orderBy];
                }
            }
        }
        array_multisort($paymentList,$sort_order,$payments);
        return $payments;
    }

}

