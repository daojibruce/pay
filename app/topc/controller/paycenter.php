<?php
class topc_ctl_paycenter extends topc_controller{

    public function __construct($app)
    {
        parent::__construct();
        $this->setLayoutFlag('paycenter');
        // 检测是否登录
    }

    /*
     * 订单创建成功页面
     * custom s dengLy
     * 2015.12.28
     */
    public function orderSuccess(){

        return $this->page('topc/payment/oderSuccess.html', $pagedata);

    }
    public function index()
    {
        $filter = input::get();
        $paymentsMdl = app::get('ectools')->model('payments');
        $payInfo=$paymentsMdl->getRow('*',array('payment_id'=>$filter['payment_id']));

        //充值入口
        $is_recharge = isset($filter['r']) ? intval($filter['r']) : 0;
        if($is_recharge) {
            $payType['platform'] = 'ispc';
            $payments = app::get('topc')->rpcCall('payment.get.list',$payType,'buyer');

            $trades = array(
                'payment_id'	=> $filter['payment_id'],
                'cur_money'		=> $payInfo['money'],
            );

            //支付方式
            $pagedata['payments'] = $payments;
            $pagedata['trades'] = $trades;

            //$pagedata['newtrade'] = $newtrade;
            $pagedata['mainfile'] = "topc/payment/payment.html";
            return $this->page('topc/payment/index.html', $pagedata);
        }



        //判断订单是否已支付
        $filter['fields'] = "*";
        $paymentBill = app::get('topc')->rpcCall('payment.bill.get',$filter,'buyer');
        if($paymentBill['status'] == "succ"){
            return $this->finish(['payment_id'=>$paymentBill['payment_id']]);
        }

        //获取交易订单号
        foreach ($paymentBill['trade'] as $val){
            $tid=$val['tid'];
        }

        if($filter['newtrade']){
            $newtrade = $filter['newtrade'];
            unset($filter['newtrade']);
        }

        if($filter['merge']){
            $ifmerge = $filter['merge'];
            unset($filter['merge']);
        }

        //格式化实付金额
        $params['fields'] = "tid,dlytmpl_id,status,payment,delivery_goods_time,contract_status,post_fee,pay_type,payed_fee,receiver_state,receiver_city,receiver_district,receiver_address,trade_memo,receiver_name,receiver_mobile,receiver_phone,ziti_addr,ziti_memo,orders.price,orders.num,orders.title,orders.item_id,orders.pic_path,total_fee,discount_fee,buyer_rate,adjust_fee,orders.total_fee,orders.adjust_fee,orders.spec_nature_info,orders.sku_id,created_time,shop_id,need_invoice,invoice_name,invoice_type,invoice_main,activity,cancel_reason,invoice_tfn,invoice_bank_name,invoice_bank_num,invoice_addr,invoice_mobile,position";
        $params['tid'] = $tid;
        $params['user_id'] = userAuth::id();
        $trade = app::get('topc')->rpcCall('trade.get',$params,'buyer');

        $objMdlelecontract = app::get('systrade')->model('elecontract');
        $elecontractRes = $objMdlelecontract->getRow('*',array('tid' =>$tid));
        $objMdlDelivery = app::get('syslogistics')->model('delivery');
        $payNode=empty($elecontractRes['pay_type']) ? array(): $elecontractRes['pay_type'];

        //是否分期付款
        $elecontractRes['is_part_pay'] = (int)$elecontractRes['is_part_pay'];

        $payNum=$this->parseNum($payNode);

        $payfee=array($payNode['pay_1'],$payNode['pay_2'],$payNode['pay_3'],$payNode['pay_4'],$payNode['pay_5']);
        $numfee=array($payNode['num1'],$payNode['num2'],$payNode['num3'],$payNode['num4'],$payNode['num5']);
        switch ($elecontractRes['is_part_pay']){
            case 0:
                $payment = $trade['payment'];
                break;
            case 1:
                $payment = $numfee[$trade['position']];
                break;
        }

        $update = array(
            'trade_own_money' => json_encode(array($tid=>$payment)),
            'money' => $payment,
            'cur_money' => $payment,
            'payment_id' => $filter['payment_id'],
        );

        //订单列表页地址
        $url_tradelist = url::action('topc_ctl_member_trade@tradeList');

        if($payment != $paymentBill['cur_money']){
            try{
                app::get('topc')->rpcCall('payment.money.update',$update);
            }catch(Exception $e){
                $msg = $e->getMessage();
                $this->__redirect($url_tradelist);
            }
        }
        $paymentBill['money'] = $payment;
        $paymentBill['cur_money'] = $payment;
        $pagedata['trades'] = $paymentBill;
        $pagedata['tids'] = $tid;
        $pagedata['pay_money']=$payment;
        $pagedata['payment']['user_id']=$paymentBill['user_id'];
        $dir = kernel::base_url(0);
        $pagedata['dir'] = $dir.kernel::url_prefix();
        //判断是否选择需要合同
        $tradeMdl = app::get('systrade')->model('trade');
        try{
            $trade_contract= $tradeMdl->getRow('contract_status');
            $pagedata['contract_status']=$trade_contract['contract_status'];
        }catch (Exception $e){
            $this->__redirect($url_tradelist);
        }
        //分流
        switch ($payInfo['pay_type']){
            case 'caroffline':
                $settingMdl = app::get('base')->model('setting');
                $carsettinginfo =  $settingMdl->getRow('value',array('app'=>'ectools','key'=>'ectools_payment_plugin_caroffline'));
                $info=unserialize(unserialize($carsettinginfo['value']));
                $pagedata['account']=$info['setting']['account'];      //  <!--收款帐号-->
                $pagedata['bank']=$info['setting']['bank'];         //<!--收款银行-->
                $pagedata['bank_key']=$info['setting']['bank_key'];// <!--收款卡号-->
                $pagedata['payment_type'] ="caroffline";
                $pagedata['which'] = 'caroffline';
                $pagedata['mainfile'] = "topc/payment/carpayment.html";
                break;
            case 'online':
                $payType['platform'] = 'ispc';
                $payments = app::get('topc')->rpcCall('payment.get.list',$payType,'buyer');
                $pagedata['payments'] = $payments;
                $pagedata['newtrade'] = $newtrade;
                $pagedata['mainfile'] = "topc/payment/payment.html";
                break;
            case 'offline':
                if(isset($filter['which']) && $filter['which'] == 'online'){
                    $payType['platform'] = 'ispc';
                    $payments = app::get('topc')->rpcCall('payment.get.list',$payType,'buyer');
                    $pagedata['payments'] = $payments;
                    $pagedata['newtrade'] = $newtrade;
                    $pagedata['which'] = 'online';
                    $pagedata['mainfile'] = "topc/payment/payment.html";
                }else if(isset($filter['which']) &&  $filter['which'] == 'caroffline'){
                    $settingMdl = app::get('base')->model('setting');
                    $carsettinginfo =  $settingMdl->getRow('value',array('app'=>'ectools','key'=>'ectools_payment_plugin_caroffline'));
                    $info=unserialize(unserialize($carsettinginfo['value']));
                    $pagedata['account']=$info['setting']['account'];      //  <!--收款帐号-->
                    $pagedata['bank']=$info['setting']['bank'];         //<!--收款银行-->
                    $pagedata['banpayment_typek_key']=$info['setting']['bank_key'];// <!--收款卡号-->
                    $pagedata[''] ="caroffline";
                    $pagedata['which'] = 'caroffline';
                    $pagedata['mainfile'] = "topc/payment/carpayment.html";
                }

                /* $settingMdl = app::get('base')->model('setting');
                 $carsettinginfo =  $settingMdl->getRow('value',array('app'=>'ectools','key'=>'ectools_payment_plugin_caroffline'));
                 $info=unserialize(unserialize($carsettinginfo['value']));
                 $pagedata['account']=$info['setting']['account'];      //  <!--收款帐号-->
                 $pagedata['bank']=$info['setting']['bank'];         //<!--收款银行-->
                 $pagedata['bank_key']=$info['setting']['bank_key'];// <!--收款卡号-->
                 $pagedata['payment_type'] = "offline";
                 $pagedata['mainfile'] = "topc/payment/payment.html";
                 return $this->page('topc/payment/index.html', $pagedata);*/
                break;
        }
        //获取购买商品的信息
        $pagedata['tradeInfo'] = $trade;
        //dd($pagedata);
        kernel::single('sysuser_actionlog')->actionlog(array('memo'=>'发起订单支付['.  $pagedata['tids'].']','action_id'=>$pagedata['tids']));

        $pagedata['tradeCancelIntervalTime'] = app::get('sysconf')->getConf('trade.cancel.spacing.time');

        return $this->page('topc/payment/index.html', $pagedata);
    }

    public function createPay()
    {
        $filter = input::get();
        $filter['user_id'] = userAuth::id();
        $filter['user_name'] = userAuth::getLoginName();
        /* custom s sjz */
        //查询是否是线下支付
        $tradeMdl = app::get('systrade')->Model('trade');
        $tradeInfo = $tradeMdl->getRow('*',array('tid'=>$filter['tid']));
        $payment = $tradeInfo['payment'];
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
            $paymentId = kernel::single('topc_payment')->getPaymentId($filter);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            $url = url::action('topc_ctl_member_trade@tradeList');
            echo '<meta charset="utf-8"><script>alert("'.$msg.'");location.href="'.$url.'";</script>';
            exit;
        }
        /* custom s sjz */
        $url = url::action('topc_ctl_paycenter@index',array('oid'=>$filter['tid'],'payment_id'=>$paymentId,'merge'=>$ifmerge,'which' => $filter['which']));
        /* custom e sjz */
        return $this->splash('success',$url,'',true);
    }

    public function dopayment()
    {
        $postdata = input::get();
        $payment = $postdata['payment'];
        $payment['user_id'] = userAuth::id();
        $payment['platform'] = "pc";//return $this->splash('error','',json_encode($payment),true);
        try{
            app::get('topc')->rpcCall('payment.trade.pay',$payment);
        }
        catch(Exception $e){
            $msg = $e->getMessage();
            echo '<meta charset="utf-8"><script>alert("'.$msg.'"); window.close();</script>';
            exit;
        }
        kernel::single('sysuser_actionlog')->actionlog(array('memo'=>'成功支付订单['.  $payment['tids'].'][paymentid='.$payment['payment_id'].']','action_id'=>$payment['tids']));
        $url = url::action('topc_ctl_paycenter@finish',array('payment_id'=>$payment['payment_id']));
        return $this->splash('success',$url,$msg,true);
    }

    //用来确认支付单是否支付成功
    public function checkPayments()
    {
        $postdata = input::get();
        if(!is_numeric($postdata['payment_id']))
        {
            $this->splash('failed',null,"payment_id格式错误",true);exit;
        }
        $params['payment_id'] = $postdata['payment_id'];
        $result = app::get('topc')->rpcCall('payment.checkpayment.statu',$params);
        return $result;
    }

    /* custom s sjz */
    //线下支付
    function docarpayment(){
        $postdata = input::get();
        $payment = $postdata['payment'];
        $payment['user_id'] = userAuth::id();
        $payment['platform'] = "pc";
        $payment_id=$payment['payment_id'];
        // echo '<pre>';print_r($payment_id);die();
        //如果是线下支付
        if($payment['pay_type']=='caroffline'){
            $payment['pay_app_id']='caroffline';
            $payment['pay_name']='线下支付';
            $payment['status']='paying';
            //更新
            unset($payment['payment_id']);
            $ectoolsMdl = app::get('ectools')->Model('payments');
            $objMdlTrade = app::get('systrade')->Model('trade');
            $objMdlOrder = app::get('systrade')->Model('order');

            try{
                //将订单状态变成等待平台审核
                if(isset($payment['which']) && $payment['which'] == "caroffline"){
                    $objMdlTrade->update(array('status'=>'WAIT_PLAT_CHECK_OFFLINE'),array('tid'=>$payment['tids']));
                    $objMdlOrder->update(array('status'=>'WAIT_PLAT_CHECK_OFFLINE'),array('tid'=>$payment['tids']));
                }
                $ectoolsMdl->update($payment,array('payment_id'=>$payment_id));
            }catch(Exception $e){
                $msg = $e->getMessage();
                var_dump($msg);
                exit;
            }
        }else{
            $msg ='线下支付有错误,请联系管理员!';
            echo '<meta charset="utf-8"><script>alert("'.$msg.'"); window.close();</script>';
        }
        return $this->carfinish($payment_id);
    }

    /* custom e sjz */
    //支付审核页面
    function carfinish($payment_id){

        $ectoolsMdl = app::get('ectools')->Model('payments');
        $payinfo=  $ectoolsMdl->getRow('*',array('payment_id'=>$payment_id));
        $pagedata['payment'] = $payinfo;

        //应付金额 dengsf
        //格式化实付金额\

        $params['fields'] = "tid,dlytmpl_id,status,payment,delivery_goods_time,contract_status,post_fee,pay_type,payed_fee,receiver_state,receiver_city,receiver_district,receiver_address,trade_memo,receiver_name,receiver_mobile,receiver_phone,ziti_addr,ziti_memo,orders.price,orders.num,orders.title,orders.item_id,orders.pic_path,total_fee,discount_fee,buyer_rate,adjust_fee,orders.total_fee,orders.adjust_fee,orders.spec_nature_info,orders.sku_id,created_time,shop_id,need_invoice,invoice_name,invoice_type,invoice_main,activity,cancel_reason,invoice_tfn,invoice_bank_name,invoice_bank_num,invoice_addr,invoice_mobile,position";
        /* custom e  zhm */
        $params['tid'] = $payinfo['tids'];
        $params['user_id'] = userAuth::id();
        $trade1 = app::get('topc')->rpcCall('trade.get',$params,'buyer');

        $objMdlelecontract = app::get('systrade')->model('elecontract');
        $elecontractRes = $objMdlelecontract->getRow('*',array('tid' =>$payinfo['tids']));
        $objMdlDelivery = app::get('syslogistics')->model('delivery');
        $payNode=empty($elecontractRes['pay_type']) ? array(): $elecontractRes['pay_type'];
        $payNum=$this->parseNum($payNode);

        $payfee=array($payNode['pay_1'],$payNode['pay_2'],$payNode['pay_3'],$payNode['pay_4'],$payNode['pay_5']);
        $numfee=array($payNode['num1'],$payNode['num2'],$payNode['num3'],$payNode['num4'],$payNode['num5']);
        switch ($elecontractRes['is_part_pay']){
            case '0':
                $payment = $trade1['payment'];
                break;
            case '1':
                $payment=$numfee[$trade1['position']];
                break;
        }
        $pagedata['paymoney'] = $payment;
        $pagedata['mainfile'] = "topc/payment/carfinish.html";
        return $this->page('topc/payment/index.html', $pagedata);
    }

    public function finish($postdata = array())
    {
        //支付成功页面
        if(!$postdata)
        {
            $postdata = input::get();
        }
        
        try
        {
            $params['payment_id'] = $postdata['payment_id'];
            $params['fields'] = 'payment_id,status,pay_app_id,pay_name,money,cur_money';
            $result = app::get('topc')->rpcCall('payment.bill.get',$params);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
        }

        $result['num'] = count($result['trade']);
        $pagedata['msg'] = $msg;
        $pagedata['payment'] = $result;
        $pagedata['mainfile'] = "topc/payment/finish.html";
        return $this->page('topc/payment/index.html', $pagedata);
    }


    /**
     *  订单错误页面提示
     *  @param int $paymentId
     *  @param string $msg
     *  @param string $result
     *  @return void
     * */
    public function errorPay($paymentId, $msg = '', $result='', $code=0)
    {
        $postdata = input::get();
        if($postdata['payment_id'])
        {
            $paymentId = $postdata['payment_id'];
        }
        if(!$paymentId)
        {
            kernel::abort('404');
        }
        $params['payment_id'] = $paymentId;

        $notice = '订单支付失败，请重试';
        $msg = $msg ? $msg : $notice;
        $pagedata = array();

        //status表示订单是否存在
        $pagedata['status'] = true;
        $pagedata['msg'] = $msg;

        //判断订单状态
        if(!$result)
        {
            $result = app::get('topc')->rpcCall('payment.checkpayment.statu',$params);
            if(!$result)
            {
                $pagedata['msg'] = '订单不存在';
                $pagedata['status'] = false;
                return $this->page('topc/payment/error.html', $pagedata);
            }
        }

        if( $result !='succ')
        {
            //获取订单详情
            $params['fields'] = 'cur_money';
            $paymentBill = app::get('topc')->rpcCall('payment.bill.get',$params);
            $trade = $paymentBill['trade'];
            $tids = array_keys($trade);
            $iparams['tid'] = $tids;
            $iparams['user_id'] = userAuth::id();
            $iparams['fields'] = "tid,orders.title";
            $itrade = app::get('topc')->rpcCall('trade.get',$iparams);
            $orders = $itrade['orders'];
            $pagedata['cur_money'] = $paymentBill['cur_money'];
            $pagedata['orders'] = $orders;
            $pagedata['payment_id'] = $paymentId;

            return $this->page('topc/payment/error.html', $pagedata);
        }else
        {
            return redirect::action('topc_ctl_paycenter@finish', array('payment_id' => $params['payment_id']));
        }

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

    public function parseNum($arr){
        if (isset($arr['pay_1'])  && $arr['pay_1']!=''){
            $numArr[]=$arr['pay_1'];
        }
        if (isset($arr['pay_2'])  && $arr['pay_2']!=''){
            $numArr[]=$arr['pay_2'];
        }
        if (isset($arr['pay_3'])  && $arr['pay_3']!=''){
            $numArr[]=$arr['pay_3'];
        }
        if (isset($arr['pay_4'])  && $arr['pay_4']!=''){
            $numArr[]=$arr['pay_4'];
        }
        if (isset($arr['pay_5'])  && $arr['pay_5']!=''){
            $numArr[]=$arr['pay_5'];
        }
        return count($numArr);
    }
	//跳转页面
	private function __redirect($url)
	{
		header('Location:' . $url);
		exit;
	}
}


