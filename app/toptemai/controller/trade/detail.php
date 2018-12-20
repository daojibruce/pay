<?php
class toptemai_ctl_trade_detail extends toptemai_controller{
    private $__adjust_status = array(
        'WAIT_SENDCONTRACT',//待发起合同
        'WAIT_CONFIRM',	//等待买家确认合同
        'REJECT_CONTRACT',	//买家拒绝合同
        'WAIT_BUYER_PAY',	//等待买家付款
        'WAIT_SELLER_SEND_GOODS',//货到付款情况下在此订单状态才能付款
    );

    public function index()
    {
        $tids = input::get('tid');
        //面包屑
        $this->runtimePath = array(
            ['url'=> url::action('toptemai_ctl_index@index'),'title' => app::get('toptemai')->_('首页')],
            ['url'=> url::action('toptemai_ctl_trade_list@index'),'title' => app::get('toptemai')->_('订单列表')],
            ['title' => app::get('toptemai')->_('订单详情')],
        );

        $params['tid'] = $tids;
        // adjust_fee,
        $params['fields'] = "trade_from,isconfirm_post_fee,isconfirm_adjust_fee,modified_time,shipping_type,delivery_goods_time,dlytmpl_id,receiver_phone,orders.spec_nature_info,orders.sku_id,user_id,tid,status,payment,points_fee,ziti_addr,ziti_memo,post_fee,pay_type,payed_fee,receiver_state,receiver_city,receiver_district,receiver_address,receiver_zip,trade_memo,shop_memo,receiver_name,receiver_mobile,orders.price,orders.num,orders.title,orders.item_id,orders.pic_path,total_fee,discount_fee,buyer_rate,orders.total_fee,orders.adjust_fee,created_time,pay_time,consign_time,end_time,temai_user_id,need_invoice,invoice_name,invoice_type,invoice_main,invoice_vat_main,invoice_tfn,invoice_bank_name,invoice_bank_num,invoice_addr,invoice_mobile,orders.bn,cancel_reason,update_status,need_econtract,contract_status,contract_id,position,orders.refund_fee,orders.aftersales_status,orders.gift_data";
        $tradeInfo = app::get('toptemai')->rpcCall('trade.get',$params,'seller');
        if($tradeInfo['shipping_type'] == 'ziti')
        {
            $pagedata['ziti'] = "true";
        }

        if(!$tradeInfo)
        {
            redirect::action('toptemai_ctl_trade_list@index')->send();exit;
        }
        /*增加电子合同状态的显示 edit by nie 2016-1-17*/
		$tradeInfo['unfeePayment'] = $tradeInfo['payment'];
        $objMdlelecontract = app::get('systrade')->model('elecontract');
		$elecontract = $objMdlelecontract->getRow('*',array('tid' =>$tids));
		if($elecontract){
			$payment = $tradeInfo['payment'];
			$elecontract['signed_time'] =  date("Y-m-d",$elecontract['signed_time']);
			//$elecontract['pay_type'] = unserialize($elecontract['pay_type']);
			$time_arr = array(
                '0'=>'一个月',
                '1'=>'二个月',
                '2'=>'三个月',
                '3'=>'四个月',
                '4'=>'五个月',
                '5'=>'六个月',
                '6'=>'七个月',
                '7'=>'八个月',
                '8'=>'九个月',
                '9'=>'十个月',
                '10'=>'十一个月',
                '11'=>'十二个月',);
			$elecontract['pay_type']['detail_time1'] = $time_arr[$elecontract['pay_type']['detail_time1']];
			$elecontract['pay_type']['detail_time2'] = $time_arr[$elecontract['pay_type']['detail_time2']];
			$elecontract['pay_type']['detail_time3'] = $time_arr[$elecontract['pay_type']['detail_time3']];
			$tradeInfo['elecontract'] = $elecontract;
		}
		/*增加电子合同状态的显示 edit by nie 2016-1-17*/
        $contentObj = app::get('systrade')->model('contract');
		$tradeInfo['contact_img'] = $contentObj->getRow('contract_img',array('contract_id'=>$tradeInfo['contract_id']))['contract_img'];

        if(!empty($tradeInfo['contact_img'])) {
            $pagedata['contact_img'] = 1;
        }else{
            $pagedata['contact_img'] = 0;
        }
        //商品发送时间
        if($tradeInfo['delivery_goods_time']){
			$tradeInfo['delivery_goods_time'] = date("Y-m-d ", $tradeInfo['delivery_goods_time']);
		}
        $tradeInfo['cny_payment'] = $this->num_to_rmb($tradeInfo['unfeePayment']);
        $temai_user_id = $tradeInfo['temai_user_id'];
		$objMdlshopinfo = app::get('sysuser')->model('user');
		$objMdlitem = app::get('sysitem')->model('item');
		$objMdlbrand = app::get('syscategory')->model('brand');
		$objMdlsku = app::get('sysitem')->model('sku');
		$shopinfo = $objMdlshopinfo->getRow('name',array('user_id' =>$temai_user_id));
        $order_adjust_fee = 0;
		foreach($tradeInfo['orders'] as $key =>$value){
            $order_adjust_fee += $value['adjust_fee']*$value['num'];
			$item_id = $value['item_id'];
			$sku_id = $value['sku_id'];
			$items = $objMdlitem->getRow('item_id,brand_id,unit',array('item_id' =>$item_id));
			$sku = $objMdlsku->getRow('price',array('sku_id' =>$sku_id));
			$sku_price = $sku['price'];
			$brand_id = $items['brand_id'];
			$unit = $items['unit'];
			$brands = $objMdlbrand->getRow('brand_name,brand_id',array('brand_id' =>$brand_id));
			$brand_name = $brands['brand_name'];
			$tradeInfo['orders'][$key]['unit'] = $unit;
			$tradeInfo['orders'][$key]['brand_name'] = $brand_name;
			$tradeInfo['orders'][$key]['sku_price'] = $sku_price;
			$tradeInfo['orders'][$key]['price'] = $sku_price;
			$tradeInfo['orders'][$key]['adjust_fee'] = $value['adjust_fee'];
			$tradeInfo['orders'][$key]['payment'] = $value['payment'];
		}

        if($tradeInfo['dlytmpl_id'] == 0 && $tradeInfo['ziti_addr'])
        {
            $pagedata['ziti'] = "true";
        }


        $userInfo = app::get('toptemai')->rpcCall('user.get.account.name', ['user_id' => $tradeInfo['user_id']], 'seller');
        $tradeInfo['login_account'] = $userInfo[$tradeInfo['user_id']];
        $pagedata['company_name'] = $shopinfo['name'];
        $tradeInfo['total_adjust_fee'] = $order_adjust_fee;

        //获取默认图片信息
        $pagedata['defaultImageId']= kernel::single('image_data_image')->getImageSetting('item');
        $pagedata['trade']= $tradeInfo;
        $pagedata['editable'] = in_array($tradeInfo['status'], array('WAIT_SENDCONTRACT', 'REJECT_CONTRACT', 'WAIT_CONFIRM'));

        /*
        ** 判断运费是否可调
        ** postfee_adjustable 表示邮费是否可调
        ** price_adjustable 表示单价是否可调
        */
        $postfee_adjustable = in_array($tradeInfo['status'], $this->__adjust_status);
        //货到付款在卖家发货状态下也能修改订单
        if( 'WAIT_SELLER_SEND_GOODS' == $tradeInfo['status']  && 'offline' != $tradeInfo['pay_type'] ){
            $postfee_adjustable = false;
        }
        //货到付款在买家付款状态下不能修改订单
        if ('WAIT_BUYER_PAY' == $tradeInfo['status'] && 'offline' == $tradeInfo['pay_type']) {
            $postfee_adjustable = false;
        }
        $price_adjustable = $postfee_adjustable;
        
        $pagedata['postfee_adjustable'] = $postfee_adjustable;
        $pagedata['price_adjustable'] = $price_adjustable;

        //物流信息处理
        $pagedata['logi'] = app::get('toptemai')->rpcCall('delivery.get',array('tid'=>$params['tid']));
        $pagedata['tracking'] = app::get('syslogistics')->getConf('syslogistics.order.tracking');
        $this->contentHeaderTitle = app::get('toptemai')->_('订单详情');

        //合同审核状态
        $Objtrade=app::get('systrade')->model('trade');
        $data=$Objtrade->getrow('*',array('tid'=>$pagedata['trade']['tid']));
        $contractMd = app::get('systrade')->model('contract');
        $filter = Array(
            'contacts',
            'phone',
            'contract_img',
            'contract_id',
            'temai_user_id'
        );
        $filte['user_id'] = $pagedata['trade']['user_id'];
        $pagedata['contract_status']=$data['contract_status'];
        $pagedata['checkup_status']=$data['checkup_status'];

        //获取支付信息
		$objMdlpaybill = app::get('ectools')->model('trade_paybill');
		$payBillList = $objMdlpaybill->getList('*', array('tid' => $params['tid'], 'status' => 'succ'), 0, -1, ' paybill_id asc');
		$payData = array();
		if ($elecontract['is_part_pay']) {
			//分批付款
			$ii = 0;
			foreach ($elecontract['pay_type'] as $k => $v) {
				if ($v == 0 || strpos($k, 'pay_') === false) {
						continue;
				}
				$tmp = array();
				if (isset($payBillList[$ii])) {
						$tmp['pay_status'] = '已支付';
						$tmp['pay_time'] = $payBillList[$ii]['payed_time'];
						$tmp['wait_pay_time'] ='--';
				} else {
						$tmp['pay_status'] = '待支付';
						$tmp['wait_pay_time'] = $ii < 2 ? '--' : '收货后' . $elecontract['pay_type']['detail_time' . ($ii - 1)] . '内';
						$tmp['pay_time'] ='--';
				}
				$tmp['times'] = ++$ii;
				$tmp['amount'] = round($elecontract['pay_type']['num' . $ii],2);
				$payData[] = $tmp;
			}
		} else {
			$payinfo  = array(
				'times' => 1,
				'amount' => $tradeInfo['payment'],
				'pay_status' => isset($payBillList[0]) ? '已支付' : '待支付',
				'pay_time' => isset($payBillList[0]) ? $payBillList[$ii]['payed_time'] : '',
			);
			if(isset($payBillList[0])){
					$payinfo['pay_status'] = '已支付';
					$payinfo['pay_time'] = $payBillList[0]['payed_time'];
					$payinfo['wait_pay_time'] = '--';
			}else{
					$payinfo['pay_status'] = '待支付';
					$payinfo['pay_time'] = '--';
					$payinfo['wait_pay_time'] = '';
			}
			$payData[] = $payinfo;
		}
		$pagedata['payData'] = $payData;

        //获取该订单的发货信息
        $objMdlDelivery = app::get('syslogistics')->model('delivery');
        $objMdlDeliveryDetail = app::get('syslogistics')->model('delivery_detail');
        $deliveryRes=$objMdlDelivery->getList('*',array('tid'=>$params['tid']));
        foreach ($deliveryRes as $dk=>$dv){
            $deliveryRes[$dk]['delivery_detail']=$objMdlDeliveryDetail->getList('*',array('delivery_id'=>$dv['delivery_id']));
            if ($dv['department']=='ecommerce') {
            	$deliveryRes[$dk]['department']='电商运营部';
            }elseif ($dv['department']=='equipment') {
            	$deliveryRes[$dk]['department']='制造业装备经营部';
            }else{
            	$deliveryRes[$dk]['department']='';
            }
        }
        $pagedata['delivery']=$deliveryRes;
		$pagedata['time'] = time();
        $pagedata['contract']=$contractMd->getrow($filter,array('contract_id'=>$data['contract_id'],'temai_user_id'=>$temai_user_id));

        //电子合同
        $defaultEcontract = config::get('econtract');
        $pagedata['defaultEcontract'] = $defaultEcontract;

        $contract_list = unserialize($pagedata['contract']['contract_img']);
        unset($pagedata['contract']['contract_img']);
        foreach($contract_list as $row){
            $file_name =  explode('/',urldecode($row));
            $pagedata['contract']['contract_img'][] = array('title'=>end($file_name),'url'=>$row);
        }

        //获取物流公司列表
        $dlycorp = app::get('toptemai')->rpcCall('temai.dlycorp.getlist',['user_id'=>$temai_user_id]);
        $pagedata['dlycorp'] = $dlycorp['list'];

        return $this->page('toptemai/trade/detail.html', $pagedata);
    }

    public function ajaxGetTrack()
    {
        $postData = input::get();
        $pagedata['track'] = app::get('toptemai')->rpcCall('logistics.tracking.get.hqepay',$postData);
        return view::make('toptemai/trade/trade_logistics.html',$pagedata);
    }

    public function setTradeMemo()
    {
        $params['tid'] = input::get('tid');
        $params['temai_user_id'] = $this->userId;

        if( !is_numeric($params['tid']) )
        {
            $msg = app::get('toptemai')->_('参数错误');
            return $this->splash('error','',$msg,true);
        }

        try
        {
            $params['shop_memo'] = input::get('shop_memo');
            $result = app::get('toptemai')->rpcCall('temai.trade.add.memo',$params);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error','',$msg,true);
        }
        $this->sellerlog('编辑订单备注。订单号是'.$params['tid']);
        $msg = app::get('toptemai')->_('备注添加成功');
        $url = url::action('toptemai_ctl_trade_detail@index',array('tid'=>$params['tid']));
        return $this->splash('success',$url,$msg,true);
    }

    /*
    *维护生产进度  custom zmq 2016-07-05
    */
    public function progress()
    {
        $oids = input::get('oid');
        $orderModel=app::get('systrade')->model('order');
        $orderRow=$orderModel->getRow('oid,title,num,progress',array('oid'=>$oids));
        $pagedata['progress']=$orderRow;
        $pagedata['progress']['progress']=unserialize($orderRow['progress']);
        // echo '<pre>';print_r($pagedata);die();
        return view::make('toptemai/trade/progress.html',$pagedata);
    }

    public function addprogress()
    {
    	$data=input::get();
    	// $progress=serialize($data['progress']);
    	$objMdlOrder = app::get('systrade')->model('order');
    	$oid=$data['oid'];
    	$oldProgress=$objMdlOrder->getRow('progress',array('oid'=>$oid))['progress'];
    	$oldProgress=unserialize($oldProgress)['progress'];
    	$time=date('Y-m-d H:i:s',time());
    	$progress='['.$time.']   '.$data['progress'];
    	$proData=array();
    	$proData['progress']=$oldProgress.'</br>'.$progress.';';
    	$isSave = $objMdlOrder->update(['progress'=>$proData],['oid'=>$oid]);
        return $this->splash('success',null, '生产进度维护成功', true);
    }

    /**
     * 修改订单 调价等信息
     */
    public function do_adjust_order() {
        //表单信息
        $postData = input::get();
        $filter['tid'] = $postData['tid'];

        $objMdltrade = app::get('systrade')->model('trade');
        $objMdlPayBill = app::get('ectools')->model('trade_paybill');
        $objMdlorder = app::get('systrade')->model('order');
        $trade = $objMdltrade->getRow('discount_fee,status,need_econtract',array('tid'=>$postData['tid']));
        if( ( $trade['need_econtract'] && ( $trade['status'] == 'WAIT_SENDCONTRACT' || $trade['status'] == 'REJECT_CONTRACT'))
            || !$trade['need_econtract']){

            $trade_time = strtotime($postData['delivery_goods_time']) ? strtotime($postData['delivery_goods_time']) : 0;
            if($trade_time){
                $trade_data['delivery_goods_time'] = $trade_time;
                if($trade_time < $postData['created_time']){
                    $tips = array('rs' => 'error', 'msg' => '交货时间选择应大于下单时间！');
                    return response::json($tips);
                }
            }

            foreach ($postData['order'] as $key => $value) {
                $order[$key]=$objMdlorder->getRow('num',array('oid'=>$value));

            }
            foreach ($postData['adjust'] as $key => $v) {
                $order[$key]['adjust_fee']=$v;
                $order[$key]['adjust']=$order[$key]['adjust_fee']*$order[$key]['num'];
            }
            foreach ($order as $key => $vv) {
                $sum_adjust +=$vv['adjust'];
            }

            //初始化 交易信息
            $trade_data['post_fee'] = $postData['post_fee'] ? $postData['post_fee'] : 0;
            $trade_data['adjust_fee'] = $postData['adjust_fee'] ? $postData['adjust_fee'] : 0;

            // $trade_data['total_adjust_fee'] = bcadd($trade_data['adjust_fee'], array_sum($postData['adjust']), 3);
            $trade_data['total_adjust_fee'] = bcadd($trade_data['adjust_fee'], $sum_adjust, 3);
            if(-$postData['total_fee'] > $trade_data['total_adjust_fee']){
                $tips = array('rs' => 'error', 'msg' => '手工调价总和不能小于商品总和！');
                return response::json($tips);
            }
            //手工调价+运费总和
            $total_fee = bcadd($trade_data['post_fee'], $trade_data['total_adjust_fee'], 3);
            //$total_fee = bcadd($total_fee, array_sum($postData['adjust']), 3);

            //应付金额
            $trade_data['payment'] = bcadd($postData['total_fee'], $total_fee, 3);


            //手工调价金额不能小于商品总额

            if($trade['discount_fee'] > 0) {
                // $trade_data['payment'] = bcsub($trade_data['payment'],$trade['discount_fee'],3);
            }

            if($trade_data['payment'] < 0){
                $tips = array('rs' => 'error', 'msg' => '金额输入有误,请核查！');
                return response::json($tips);
            }

            $db = app::get('toptemai')->database();
            $db->beginTransaction();

            try{
                $objMdlPayBill->update(array('payment'=>$trade_data['payment']),array('tid'=>$filter['tid']));
                $objMdltrade->update($trade_data, $filter);

                $new_order = array_combine ($postData['order'], $postData['adjust']);
                //获取该订单的子订单
                $objOrderMdl = app::get('systrade')->model('order');
                foreach($new_order as $key=>$value){
                    $order = $objOrderMdl->getRow('total_fee,payment,price,num',array('oid'=>$key));
                    if($order){
                        $payment=($order['price']+$value)*$order['num'];
                        // $objOrderMdl->update(array('adjust_fee'=>$value,'payment'=>$order['total_fee']+$value),array('oid'=>$key));
                        $objOrderMdl->update(array('adjust_fee'=>$value,'payment'=>$payment),array('oid'=>$key));
                    }
                }
                $tips = array('rs' => 'success', 'msg' => '修改订单信息成功！');
            }catch (Exception $e)
            {
                $db->rollback();
                throw $e;
            }
            $db->commit();
            return response::json($tips);
        }else{
            $tips = array('rs' => 'error', 'msg' => '合同已经提交给买家,无法修改!!');
            return response::json($tips);
        }
    }

    /**
     * 提交电子合同付款方式
     * edit by nie 2016-1-8
     */
    public function send_contract(){
        //表单信息
        $postData = input::get();
        if (!$postData['pay_1']) {
            $elecontract = app::get('systrade')->model('elecontract')->getRow('pay_type', array('tid'=>$postData['tid']));
            $postData['pay_1'] = $elecontract['pay_type']['pay_1'];
            $postData['pay_2'] = $elecontract['pay_type']['pay_2'];
            $postData['pay_3'] = $elecontract['pay_type']['pay_3'];
            $postData['pay_4'] = $elecontract['pay_type']['pay_4'];
            $postData['pay_5'] = $elecontract['pay_type']['pay_5'];
        }

        $filter['tid'] = $postData['tid'];
        $objMdltrade = app::get('systrade')->model('trade');
        $trade = $objMdltrade->getRow('discount_fee,payment,status,need_econtract',array('tid'=>$postData['tid']));
        if($trade['need_econtract'] && $trade['status'] != 'WAIT_SENDCONTRACT' && $trade['status'] != 'REJECT_CONTRACT'){
            return;
        }
        $data = array();
        /*0一次发货；1分次发货*/
        if($postData['zhongb2'] == '0'){
            $data['is_part_delivery'] = '0';
        }else{
            $data['is_part_delivery'] = '1';
        }

        if($postData['zhongb3'] == '0'){
            $data['is_delivery_type'] = 'DF';
        }else{
            $data['is_delivery_type'] = 'ZF';
        }

        if($postData['zhongb1'] == '0'){
            $data['is_part_pay'] = '0';
        }else{
            $data['is_part_pay'] = '1';
            $num = $postData['pay_1']+$postData['pay_2']+$postData['pay_3']+$postData['pay_4']+$postData['pay_5'];
            if($num != 100){
                $msg = "请重新填写付款方式！";
                $url = url::action('toptemai_ctl_trade_list@index');
                return $this->splash('error','',$msg,true);
            }

            //初始化 交易信息
            $trade_data['post_fee'] = $postData['post_fee'] ? $postData['post_fee'] : 0;
            $trade_data['adjust_fee'] = $postData['adjust_fee'] ? $postData['adjust_fee'] : 0;
            //订单金额

            $total_fee = bcadd($trade_data['post_fee'], $trade_data['adjust_fee'], 3);
            $total_fee = bcadd($total_fee, array_sum(explode(',',$postData['adjust'])), 3);

            $trade_data['adjust_fee'] = bcadd($trade_data['adjust_fee'], array_sum(explode(',',$postData['adjust'])), 3);
            $trade_data['payment'] = bcadd($postData['total_fee'], $total_fee, 3);


            if($trade['discount_fee'] > 0) {
                $trade_data['payment'] = bcsub($trade_data['payment'],$trade['discount_fee'],3);
            }

            if($trade_data['payment'] < 0){
                $tips = array('rs' => 'error', 'msg' => '金额输入有误,请核查！');
                return response::json($tips);
            }
            // $payment = $trade_data['payment'];
            $payment=$postData['SumTotal'];

            unset($postData['post_fee']);
            unset($postData['adjust_fee']);
            unset($postData['total_fee']);
            unset($postData['tid']);
            unset($postData['user_id']);
            unset($postData['zhongb1']);
            unset($postData['zhongb2']);
            unset($postData['zhongb3']);
            unset($postData['delivery_goods_time']);
            unset($postData['SumTotal']);

            $elecontract['pay_1'] = $postData['pay_1'];
            $elecontract['num1'] = strval(round($payment*$postData['pay_1']/100,2));
            $elecontract['pay_2'] = $postData['pay_2'];
            $elecontract['num2'] = strval(round($payment*$postData['pay_2']/100,2));

            if($postData['pay_3']) {
                if(!$postData['pay_4']){
                    $elecontract['num3'] = strval(round ($payment - $elecontract['num1'] - $elecontract['num2'], 2));
                }else{
                    $elecontract['num3'] = strval(round ($payment*$postData['pay_3']/100,2));
                }
                $elecontract['pay_3'] = $postData['pay_3'];
                $elecontract['detail_time1'] = $postData['detail_time1'];
            }else{
                $elecontract['pay_3'] = 0;
                $elecontract['num3'] = 0;
                $elecontract['detail_time1'] =  $postData['detail_time1'];
            }

            if($postData['pay_4']) {
                if(!$postData['pay_5']) {
                    $elecontract['num4'] = strval(round ($payment - $elecontract['num1'] - $elecontract['num2'] - $elecontract['num3'], 2));
                }else{
                    $elecontract['num4'] = strval(round ($payment*$postData['pay_4']/100,2));
                }
                $elecontract['pay_4'] = $postData['pay_4'];
                $elecontract['detail_time2'] = $postData['detail_time2'];
            }else {
                $elecontract['pay_4'] = 0;
                $elecontract['num4'] = 0;
                $elecontract['detail_time2'] = $postData['detail_time2'];
            }

            if($postData['pay_5']) {
                $elecontract['pay_5'] = $postData['pay_5'];
                $elecontract['num5'] = strval(round ($payment - $elecontract['num1'] - $elecontract['num2'] - $elecontract['num3'] - $elecontract['num4'], 2));
                $elecontract['detail_time3'] =  $postData['detail_time3'];
            }else{
                $elecontract['pay_5'] = 0;
                $elecontract['num5'] = 0;
                $elecontract['detail_time3'] =  $postData['detail_time3'];
            }
            $data['pay_type'] = $elecontract;
        }
        $objMdlelecontract = app::get('systrade')->model('elecontract');
        //$objMdltrade = app::get('systrade')->model('trade');
        $result = $objMdlelecontract->update($data, $filter);
        if($result){
            $msg = "提交成功!请填写合同模板！";
            //$objMdltrade->update($trade_data, $filter);
            return $this->splash('success','',$msg,true);
        }
    }

    public function ajaxPayList(){

        $tids = input::get('tid');

        $params['tid'] = $tids;
        $params['fields'] = "trade_from,isconfirm_post_fee,isconfirm_adjust_fee,modified_time,contract_id,update_status,user_id,tid,status,contract_status,delivery_goods_time,payment,ziti_addr,ziti_memo,dlytmpl_id,post_fee,pay_type,payed_fee,receiver_state,receiver_city,receiver_district,receiver_address,receiver_zip,trade_memo,shop_memo,receiver_name,receiver_mobile,receiver_phone,orders.price,orders.num,orders.title,orders.item_id,orders.sku_id,orders.pic_path,total_fee,discount_fee,buyer_rate,adjust_fee,orders.total_fee,orders.adjust_fee,created_time,pay_time,consign_time,end_time,temai_user_id,need_invoice,invoice_name,invoice_type,invoice_main,invoice_tfn,invoice_bank_name,invoice_bank_num,invoice_addr,invoice_mobile,orders.spec_nature_info,orders.bn,cancel_reason,orders.refund_fee,position,need_econtract";

        $objMdlelecontract = app::get('systrade')->model('elecontract');
        /*增加电子合同状态的显示 edit by nie 2016-1-17*/
        $elecontract = $objMdlelecontract->getRow('*',array('tid' =>$tids));

        $tradeInfo = app::get('toptemai')->rpcCall('trade.get',$params,'seller');
        //获取支付信息
        $objMdlpaybill = app::get('ectools')->model('trade_paybill');
        $payBillList = $objMdlpaybill->getList('*', array('tid' => $params['tid'], 'status' => 'succ'), 0, -1, ' paybill_id asc');

        $time_arr = array(
            '0'=>'一个月',
            '1'=>'二个月',
            '2'=>'三个月',
            '3'=>'四个月',
            '4'=>'五个月',
            '5'=>'六个月',
            '6'=>'七个月',
            '7'=>'八个月',
            '8'=>'九个月',
            '9'=>'十个月',
            '10'=>'十一个月',
            '11'=>'十二个月',);
        $elecontract['pay_type']['detail_time1'] = $time_arr[$elecontract['pay_type']['detail_time1']];
        $elecontract['pay_type']['detail_time2'] = $time_arr[$elecontract['pay_type']['detail_time2']];
        $elecontract['pay_type']['detail_time3'] = $time_arr[$elecontract['pay_type']['detail_time3']];
        $payData = array();
        if ($elecontract['is_part_pay']) {
            //分批付款
            $ii = 0;
            foreach ($elecontract['pay_type'] as $k => $v) {
                if ($v == 0 || strpos($k, 'pay_') === false) {
                    continue;
                }
                $tmp = array();
                if (isset($payBillList[$ii])) {
                    $tmp['pay_status'] = '已支付';
                    $tmp['pay_time'] = $payBillList[$ii]['payed_time'];
                    $tmp['wait_pay_time'] ='--';
                } else {
                    $tmp['pay_status'] = '待支付';
                    $tmp['wait_pay_time'] = $ii < 2 ? '--' : '收货后' . $elecontract['pay_type']['detail_time' . ($ii - 1)] . '内';
                    $tmp['pay_time'] ='--';
                }
                $tmp['times'] = ++$ii;
                $tmp['amount'] = number_format($elecontract['pay_type']['num' . $ii],2);
                $tmp['type'] = $tradeInfo['pay_type'];
                $payData[] = $tmp;
            }
        } else {
            $payinfo  = array(
                'times' => 1,
                'amount' => number_format($tradeInfo['payment'],2),
                'pay_status' => isset($payBillList[0]) ? '已支付' : '待支付',
                'pay_time' => isset($payBillList[0]) ? $payBillList[$ii]['payed_time'] : '',
            );
            if(isset($payBillList[0])){
                $payinfo['pay_status'] = '已支付';
                $payinfo['pay_time'] = $payBillList[0]['payed_time'];
                $payinfo['wait_pay_time'] = '--';
            }else{
                $payinfo['pay_status'] = '待支付';
                $payinfo['pay_time'] = '--';
                $payinfo['wait_pay_time'] = '';
            }
            $payinfo['type'] = $tradeInfo['pay_type'];
            $payData[] = $payinfo;
        }
        return response::json($payData);
    }

    /*
	 * 获取合同内金额
	 */
    public function ajaxMoneyInfo(){
        $tids = input::get('tid');
        $params['tid'] = $tids;
        $params['fields'] = "trade_from,isconfirm_post_fee,isconfirm_adjust_fee,modified_time,contract_id,update_status,user_id,tid,status,contract_status,delivery_goods_time,payment,ziti_addr,ziti_memo,dlytmpl_id,post_fee,pay_type,payed_fee,receiver_state,receiver_city,receiver_district,receiver_address,receiver_zip,trade_memo,shop_memo,receiver_name,receiver_mobile,receiver_phone,orders.price,orders.num,orders.title,orders.item_id,orders.sku_id,orders.pic_path,total_fee,discount_fee,buyer_rate,adjust_fee,orders.total_fee,orders.adjust_fee,created_time,pay_time,consign_time,end_time,temai_user_id,need_invoice,invoice_name,invoice_type,invoice_main,invoice_tfn,invoice_bank_name,invoice_bank_num,invoice_addr,invoice_mobile,orders.spec_nature_info,orders.bn,cancel_reason,orders.refund_fee,position,need_econtract";
        $tradeInfo = app::get('toptemai')->rpcCall('trade.get',$params,'buyer');
        // $tradeInfo['unfeePayment'] = number_format($tradeInfo['payment'],2);
        $tradeInfo['unfeePayment'] = $tradeInfo['payment'];
        $tradeInfo['cny_payment'] = $this->num_to_rmb($tradeInfo['unfeePayment']);
        // $tradeInfo['post_fee'] =  number_format($tradeInfo['post_fee'],2);
        $tradeInfo['post_fee'] =  $tradeInfo['post_fee'];

        // $tradeInfo['total_fee'] =  number_format($tradeInfo['total_fee'],2);
        $tradeInfo['total_fee'] =  $tradeInfo['total_fee'];
        $tradeInfo['total_adjust_fee'] =  $tradeInfo['unfeePayment']-$tradeInfo['total_fee']-$tradeInfo['post_fee'];
        $tradeInfo['total_adjust_fee']=number_format($tradeInfo['total_adjust_fee'],2);
        $tradeInfo['unfeePayment'] = number_format($tradeInfo['payment'],2);
        $tradeInfo['post_fee'] =  number_format($tradeInfo['post_fee'],2);
        $tradeInfo['total_fee'] =  number_format($tradeInfo['total_fee'],2);
        $tradeInfo['adjust_fee'] =  number_format($tradeInfo['total_adjust_fee'],2);
        $tradeInfo['discount_fee']=0;

        $objMdlitem = app::get('sysitem')->model('item');
        $objMdlbrand = app::get('syscategory')->model('brand');
        $objMdlsku = app::get('sysitem')->model('sku');

        foreach($tradeInfo['orders'] as $key =>$value){
            $item_id = $value['item_id'];
            $sku_id = $value['sku_id'];
            $items = $objMdlitem->getRow('item_id,brand_id,unit',array('item_id' =>$item_id));
            $sku = $objMdlsku->getRow('price',array('sku_id' =>$sku_id));
            $sku_price = number_format($sku['price'],2);
            $brand_id = $items['brand_id'];
            $unit = $items['unit'];
            $brands = $objMdlbrand->getRow('brand_name,brand_id',array('brand_id' =>$brand_id));
            $brand_name = $brands['brand_name'];
            $payment=($value['adjust_fee']+$value['price'])*$value['num'];
            $tradeInfo['orders'][$key]['unit'] = $unit;
            $tradeInfo['orders'][$key]['brand_name'] = $brand_name;
            // $tradeInfo['orders'][$key]['sku_price'] = number_format($sku_price,2);
            $tradeInfo['orders'][$key]['sku_price'] = number_format($value['price'],2);
            $tradeInfo['orders'][$key]['price'] = number_format($value['price'],2);
            $tradeInfo['orders'][$key]['adjust_fee'] =number_format($value['adjust_fee'],2);
            // $tradeInfo['orders'][$key]['payment'] = number_format($value['payment'],2);
            $tradeInfo['orders'][$key]['payment'] = number_format($payment,2);
        }

        return response::json($tradeInfo);
    }

    /*
	检测是否有相同的数据
	edit by nie 2016-1-8
	*/
	public function _chenkelecontract($tid){
		$objMdlelecontract = app::get('systrade')->model('elecontract');
		$result = $objMdlelecontract->getRow('*',array('tid' =>$tid));
		if($result){
			return true;
		}else{
			return false;
		}
	}
	/*
	提交电子合同模板
	edit by nie 2016-1-14
	*/
	public function send_template($tid){
		$postData = input::get();
		$tid = $postData['tid'];

        $objMdltrade = app::get('systrade')->model('trade');
        $objMdlorder = app::get('systrade')->model('order');

		$trade = $objMdltrade->getRow('need_econtract,status',array('tid'=>$postData['tid']));
        if( ($trade['need_econtract'] && ( $trade['status'] == 'WAIT_SENDCONTRACT' || $trade['status'] == 'REJECT_CONTRACT'))
		|| !$trade['need_econtract'] ){

            $objMdlelecontract = app::get('systrade')->model('elecontract');

            try{
                $objMdlelecontract->update($postData,array('tid' =>$tid));
                $objMdltrade ->update(array('status' =>'WAIT_CONFIRM'),array('tid' =>$tid));
                $objMdlorder ->update(array('status' =>'WAIT_CONFIRM'),array('tid' =>$tid));
            }catch(Exception $e){
                $msg = $e->getMessage();
                return $this->splash('error','',$msg,true);
            }
            // $msg = app::get('toptemai')->_('合同模板提交成功！');
            // $url = url::action('toptemai_ctl_trade_detail@index',array('tid'=>$tid));
            // return $this->splash('success','',$msg,true);
        }else{
            $tips = array('rs' => 'error', 'msg' => '合同已经提交给买家,无法修改！');
            return response::json($tips);
        }
		$msg = app::get('toptemai')->_('合同模板提交成功！');
		//2016.5.14 zcy 页面跳转
        $url = url::action('toptemai_ctl_trade_list@index');
        return $this->splash('success',$url,$msg,true);
	}

    public function addContract(){
        $tids = input::get('tid');
        //面包屑
        $this->runtimePath = array(
            ['url'=> url::action('toptemai_ctl_index@index'),'title' => app::get('toptemai')->_('首页')],
            ['url'=> url::action('toptemai_ctl_trade_list@index'),'title' => app::get('toptemai')->_('订单列表')],
            ['title' => app::get('toptemai')->_('订单详情')],
        );

        $params['tid'] = $tids;
        $params['fields'] = "trade_from,contract_id,isconfirm_post_fee,isconfirm_adjust_fee,update_status,user_id,tid,status,contract_status,delivery_goods_time,payment,ziti_addr,ziti_memo,dlytmpl_id,post_fee,pay_type,payed_fee,receiver_state,receiver_city,receiver_district,receiver_address,receiver_zip,trade_memo,shop_memo,receiver_name,receiver_mobile,receiver_phone,orders.price,orders.num,orders.title,orders.item_id,orders.sku_id,orders.pic_path,total_fee,discount_fee,buyer_rate,adjust_fee,orders.total_fee,orders.adjust_fee,created_time,pay_time,consign_time,end_time,temai_user_id,need_invoice,invoice_name,invoice_type,invoice_main,invoice_tfn,invoice_bank_name,invoice_bank_num,invoice_addr,invoice_mobile,orders.spec_nature_info,orders.bn,cancel_reason,orders.refund_fee,position";
        $tradeInfo = app::get('toptemai')->rpcCall('trade.get',$params,'buyer');
		$objMdlelecontract = app::get('systrade')->model('elecontract');

        $contentObj = app::get('systrade')->model('contract');
        $pagedata['contract']=$contentObj->getrow('contacts,phone,contract_img,contract_id,temai_user_id',array('contract_id'=>$tradeInfo['contract_id'],'temai_user_id'=>$tradeInfo['temai_user_id']));
        $contract_list = unserialize($pagedata['contract']['contract_img']);
        unset($pagedata['contract']['contract_img']);
        foreach($contract_list as $row){
            $file_name =  explode('/',urldecode($row));
            $pagedata['contract']['contract_img'][] = array('title'=>end($file_name),'url'=>$row);
        }


        /*增加电子合同状态的显示 edit by nie 2016-1-17*/
		$tradeInfo['unfeePayment'] = $tradeInfo['payment'];
		$elecontract = $objMdlelecontract->getRow('*',array('tid' =>$tids));
		if($elecontract){
			$payment = $tradeInfo['payment'];
			$elecontract['signed_time'] =  date("Y-m-d",$elecontract['signed_time']);
			//$elecontract['pay_type'] = unserialize($elecontract['pay_type']);
			$elecontract['pay_type']['pay_1'] = round($payment*$elecontract['pay_type']['pay_1']/100,2);
			$elecontract['pay_type']['pay_2'] = $payment*$elecontract['pay_type']['pay_2']/100;
			if($elecontract['pay_type']['pay_3'])
				$elecontract['pay_type']['pay_3'] = $payment*$elecontract['pay_type']['pay_3']/100;
			else{
				$elecontract['pay_type']['pay_3'] = 0;
			}
			if($elecontract['pay_type']['pay_4'])
				$elecontract['pay_type']['pay_4'] = $payment*$elecontract['pay_type']['pay_4']/100;
			else{
				$elecontract['pay_type']['pay_4'] = 0;
			}
			if($elecontract['pay_type']['pay_5'])
				$elecontract['pay_type']['pay_5'] = $payment*$elecontract['pay_type']['pay_5']/100;
			else{
				$elecontract['pay_type']['pay_5'] = 0;
			}
			$time_arr = array(
							'0'=>'一个月',
							'1'=>'二个月',
							'2'=>'三个月',
							'3'=>'四个月',
							'4'=>'五个月',
							'5'=>'六个月',
							'6'=>'七个月',
							'7'=>'八个月',
							'8'=>'九个月',
							'9'=>'十个月',
							'10'=>'十一个月',
							'11'=>'十二个月',);
			$elecontract['pay_type']['detail_time1'] = $time_arr[$elecontract['pay_type']['detail_time1']];
			$elecontract['pay_type']['detail_time2'] = $time_arr[$elecontract['pay_type']['detail_time2']];
			$elecontract['pay_type']['detail_time3'] = $time_arr[$elecontract['pay_type']['detail_time3']];
			$tradeInfo['elecontract'] = $elecontract;
		}
                   //电子合同内容默认值
	   $defaultEcontract = config::get('econtract');
	   $pagedata['defaultEcontract'] = $defaultEcontract;
		/*增加电子合同状态的显示 edit by nie 2016-1-17*/
		if($tradeInfo['update_status']=='1'){
			$contentObj = app::get('systrade')->model('contract');
			$tradeInfo['contact_img'] = $contentObj->getRow('contract_img',array('contract_id'=>$tradeInfo['contract_id']))['contract_img'];
		}
		if($tradeInfo['delivery_goods_time']){
			$tradeInfo['delivery_goods_time'] = date("Y-m-d ", $tradeInfo['delivery_goods_time']);
		}
		$tradeInfo['cny_payment'] = $this->num_to_rmb($tradeInfo['unfeePayment']);
        $temai_user_id = $tradeInfo['temai_user_id'];
        $objMdlshopinfo = app::get('sysuser')->model('user');
		$objMdlitem = app::get('sysitem')->model('item');
		$objMdlbrand = app::get('syscategory')->model('brand');
		$objMdlsku = app::get('sysitem')->model('sku');
        $shopinfo = $objMdlshopinfo->getRow('name',array('user_id' =>$temai_user_id));
		foreach($tradeInfo['orders'] as $key =>$value){
			$item_id = $value['item_id'];
			$sku_id = $value['sku_id'];
			$items = $objMdlitem->getRow('item_id,brand_id,unit',array('item_id' =>$item_id));
			$sku = $objMdlsku->getRow('price',array('sku_id' =>$sku_id));
			$sku_price = $sku['price'];
			$brand_id = $items['brand_id'];
			$unit = $items['unit'];
			$brands = $objMdlbrand->getRow('brand_name,brand_id',array('brand_id' =>$brand_id));
			$brand_name = $brands['brand_name'];
			$tradeInfo['orders'][$key]['unit'] = $unit;
			$tradeInfo['orders'][$key]['brand_name'] = $brand_name;
			$tradeInfo['orders'][$key]['sku_price'] = $sku_price;
			$tradeInfo['orders'][$key]['price'] = $sku_price;
		}
        if($tradeInfo['dlytmpl_id'] == 0 && $tradeInfo['ziti_addr'])
        {
            $pagedata['ziti'] = "true";
        }
        if(!$tradeInfo)
        {
            redirect::action('toptemai_ctl_trade_list@index')->send();exit;
        }
        $userInfo = app::get('toptemai')->rpcCall('user.get.account.name', ['user_id' => $tradeInfo['user_id']], 'buyer');
        $tradeInfo['login_account'] = $userInfo[$tradeInfo['user_id']];
		$pagedata['company_name'] = $shopinfo['name'];
		$pagedata['editable'] = in_array($tradeInfo['status'], array('WAIT_SENDCONTRACT', 'REJECT_CONTRACT'));
        $pagedata['trade']= $tradeInfo;
        $pagedata['logi'] = app::get('topc')->rpcCall('delivery.get',array('tid'=>$params['tid']));
        $this->contentHeaderTitle = app::get('toptemai')->_('订单详情');
        $Objtrade=app::get('systrade')->model('trade');
        $data=$Objtrade->getrow('*',array('tid'=>$pagedata['trade']['tid']));
        $contractMd = app::get('systrade')->model('contract');
        $filter = Array(
            'contacts',
            'phone',
            'contract_img',
            'contract_id',
            'temai_user_id'
        );

        $filte['user_id'] = $pagedata['trade']['user_id'];
        $pagedata['contract_status']=$data['contract_status'];
        $pagedata['checkup_status']=$data['checkup_status'];//合同审核状态

        //获取该订单的发货信息
        $objMdlDelivery = app::get('syslogistics')->model('delivery');
        $objMdlDeliveryDetail = app::get('syslogistics')->model('delivery_detail');
        $deliveryRes=$objMdlDelivery->getList('*',array('tid'=>$params['tid']));
        foreach ($deliveryRes as $dk=>$dv){
            $deliveryRes[$dk]['delivery_detail']=$objMdlDeliveryDetail->getRow('*',array('delivery_id'=>$dv['delivery_id']));
        }
        $pagedata['delivery']=$deliveryRes;
		$pagedata['time'] = time();
        $pagedata['contract']=$contractMd->getrow($filter,array('contract_id'=>$data['contract_id'],'temai_user_id'=>$temai_user_id));
        
		return $this->page('toptemai/trade/detail.html', $pagedata);
	}

    public function  previewEcontract(){
            $tids = input::get('tid');
            $params['tid'] = $tids;
        $params['fields'] = "trade_from,contract_id,isconfirm_post_fee,isconfirm_adjust_fee,update_status,user_id,tid,status,contract_status,delivery_goods_time,payment,ziti_addr,ziti_memo,dlytmpl_id,post_fee,pay_type,payed_fee,receiver_state,receiver_city,receiver_district,receiver_address,receiver_zip,trade_memo,shop_memo,receiver_name,receiver_mobile,receiver_phone,orders.price,orders.num,orders.title,orders.item_id,orders.sku_id,orders.pic_path,total_fee,discount_fee,buyer_rate,adjust_fee,orders.total_fee,orders.adjust_fee,created_time,pay_time,consign_time,end_time,temai_user_id,need_invoice,invoice_name,invoice_type,invoice_main,invoice_tfn,invoice_bank_name,invoice_bank_num,invoice_addr,invoice_mobile,orders.spec_nature_info,orders.bn,cancel_reason,orders.refund_fee,position";
        $tradeInfo = app::get('toptemai')->rpcCall('trade.get',$params,'buyer');
        $objMdlelecontract = app::get('systrade')->model('elecontract');
             /*增加电子合同状态的显示 edit by nie 2016-1-17*/
		$temai_user_id = $tradeInfo['temai_user_id'];
        $tradeInfo['unfeePayment'] = $tradeInfo['payment'];
		$elecontract = $objMdlelecontract->getRow('*',array('tid' =>$tids));
		if($elecontract){
			$payment = $tradeInfo['payment'];
			$elecontract['signed_time'] =  date("Y-m-d",$elecontract['signed_time']);
			//$elecontract['pay_type'] = unserialize($elecontract['pay_type']);
			$elecontract['pay_type']['pay_1'] = round($payment*$elecontract['pay_type']['pay_1']/100,2);
			$elecontract['pay_type']['pay_2'] = $payment*$elecontract['pay_type']['pay_2']/100;
			if($elecontract['pay_type']['pay_3'])
				$elecontract['pay_type']['pay_3'] = $payment*$elecontract['pay_type']['pay_3']/100;
			else{
				$elecontract['pay_type']['pay_3'] = 0;
			}
			if($elecontract['pay_type']['pay_4'])
				$elecontract['pay_type']['pay_4'] = $payment*$elecontract['pay_type']['pay_4']/100;
			else{
				$elecontract['pay_type']['pay_4'] = 0;
			}
			if($elecontract['pay_type']['pay_5'])
				$elecontract['pay_type']['pay_5'] = $payment*$elecontract['pay_type']['pay_5']/100;
			else{
				$elecontract['pay_type']['pay_5'] = 0;
			}
			$time_arr = array(
							'0'=>'一个月',
							'1'=>'二个月',
							'2'=>'三个月',
							'3'=>'四个月',
							'4'=>'五个月',
							'5'=>'六个月',
							'6'=>'七个月',
							'7'=>'八个月',
							'8'=>'九个月',
							'9'=>'十个月',
							'10'=>'十一个月',
							'11'=>'十二个月',);
			$elecontract['pay_type']['detail_time1'] = $time_arr[$elecontract['pay_type']['detail_time1']];
			$elecontract['pay_type']['detail_time2'] = $time_arr[$elecontract['pay_type']['detail_time2']];
			$elecontract['pay_type']['detail_time3'] = $time_arr[$elecontract['pay_type']['detail_time3']];
			$tradeInfo['elecontract'] = $elecontract;
		}
        $objMdlshopinfo = app::get('sysuser')->model('user');
		$objMdlitem = app::get('sysitem')->model('item');
		$objMdlbrand = app::get('syscategory')->model('brand');
		$objMdlsku = app::get('sysitem')->model('sku');
        $shopinfo = $objMdlshopinfo->getRow('name',array('user_id' =>$temai_user_id));
		foreach($tradeInfo['orders'] as $key =>$value){
			$item_id = $value['item_id'];
			$sku_id = $value['sku_id'];
			$items = $objMdlitem->getRow('item_id,brand_id,unit',array('item_id' =>$item_id));
			$sku = $objMdlsku->getRow('price',array('sku_id' =>$sku_id));
			$sku_price = $sku['price'];
			$brand_id = $items['brand_id'];
			$unit = $items['unit'];
			$brands = $objMdlbrand->getRow('brand_name,brand_id',array('brand_id' =>$brand_id));
			$brand_name = $brands['brand_name'];
			$tradeInfo['orders'][$key]['unit'] = $unit;
			$tradeInfo['orders'][$key]['brand_name'] = $brand_name;
			$tradeInfo['orders'][$key]['sku_price'] = $sku_price;
			$tradeInfo['orders'][$key]['price'] = $sku_price;
		}
        $pagedata['company_name'] = $shopinfo['name'];
        $pagedata['trade']= $tradeInfo;
        return view::make('toptemai/trade/econtract.html', $pagedata);
        // return $this->page('toptemai/trade/econtract.html', $pagedata);
    }

    /*
     * 商家对买家评论
     *
     */
    public function comment()
    {
        $tids = input::get('tid');
        //根据tids查询订单信息
        $params['tid'] = $tids;
        $params['fields'] = "user_id,tid,status,payment,ziti_addr,ziti_memo,dlytmpl_id,post_fee,pay_type,payed_fee,receiver_state,receiver_city,receiver_district,receiver_address,receiver_zip,trade_memo,shop_memo,receiver_name,receiver_mobile,receiver_phone,orders.price,orders.num,orders.title,orders.item_id,orders.pic_path,total_fee,discount_fee,buyer_rate,adjust_fee,orders.total_fee,orders.adjust_fee,created_time,pay_time,consign_time,end_time,temai_user_id,need_invoice,invoice_name,invoice_type,invoice_main,invoice_tfn,invoice_bank_name,invoice_bank_num,invoice_addr,invoice_mobile,orders.bn,cancel_reason,orders.refund_fee";
        $tradeInfo = app::get('toptemai')->rpcCall('trade.get',$params,'buyer');
        $pagedata['tids']=$tids;
        $pagedata['title']=$tradeInfo['orders'][0]['title'];
        $pagedata['num']=$tradeInfo['orders'][0]['num'];
        $pagedata['price']=$tradeInfo['orders'][0]['price'];
        $pagedata['pic_path']=$tradeInfo['orders'][0]['pic_path'];
        $pagedata['payment']=$tradeInfo['payment'];
        $pagedata['post_fee']=$tradeInfo['post_fee'];
        $pagedata['adjust_fee']=$tradeInfo['adjust_fee'];
        $pagedata['created_time']=$tradeInfo['created_time'];
        return $this->page('toptemai/trade/comment.html', $pagedata);
    }

    public function comments()
    {
        $data = input::get();
        $tids = $data['tids'];
        //根据tids查询订单信息
        $params['tid'] = $tids;
        $params['fields'] = "user_id,tid,status,payment,ziti_addr,ziti_memo,dlytmpl_id,post_fee,pay_type,payed_fee,receiver_state,receiver_city,receiver_district,receiver_address,receiver_zip,trade_memo,shop_memo,receiver_name,receiver_mobile,receiver_phone,orders.price,orders.num,orders.title,orders.item_id,orders.pic_path,total_fee,discount_fee,buyer_rate,adjust_fee,orders.total_fee,orders.adjust_fee,created_time,pay_time,consign_time,end_time,temai_user_id,need_invoice,invoice_name,invoice_type,invoice_main,invoice_tfn,invoice_bank_name,invoice_bank_num,invoice_addr,invoice_mobile,orders.bn,cancel_reason,orders.refund_fee";
        $tradeInfo = app::get('toptemai')->rpcCall('trade.get',$params,'buyer');
        $pagedata['tids']=$tids;
        $pagedata['title']=$tradeInfo['orders'][0]['title'];
        $pagedata['num']=$tradeInfo['orders'][0]['num'];
        $pagedata['price']=$tradeInfo['orders'][0]['price'];
        $pagedata['pic_path']=$tradeInfo['orders'][0]['pic_path'];
        $pagedata['payment']=$tradeInfo['payment'];
        $pagedata['post_fee']=$tradeInfo['post_fee'];
        $pagedata['adjust_fee']=$tradeInfo['adjust_fee'];
        $pagedata['created_time']=$tradeInfo['created_time'];

        $total_score=$data['score_1']+$data['score_2']+$data['score_3'];

        $user_id=$tradeInfo['user_id'];
        if($total_score)
        {

            //统计对店铺评论星级个数，蒋统计个数*设置兑换积分比率=获得积分数量，更新到积分表中custom s dengLy 2015.1.8
            $user_pointsMdel = app::get('sysuser')->Model('user_points');
            $result_points=$user_pointsMdel->getRow('point_count',array('user_id'=>$user_id));
            $star_points=app::get('sysconf')->getConf('point.star.points');//获取兑换率
            $total_points=$result_points['point_count']+$total_score*$star_points;
            $points_params['point_count']=$total_points;//积分总数
            $points_params['modified_time']=time();
            $user_pointsMdel->update($points_params, array('user_id' =>$user_id));//更新积分数据

            // 向积分sysuser_user_pointlog记录表中存入注册积分记录
            $objMdlUserPointLog = app::get('sysuser')->model('user_pointlog');
            $params_pointlog['user_id']=$user_id;
            $params_pointlog['modified_time']=time();
            $params_pointlog['behavior_type']='scmt';
            $params_pointlog['remark']='当前积分来自订单：'.$params['tid'];
            $params_pointlog['behavior']='卖家评论获得积分';
            $params_pointlog['point']=$total_points-$result_points['point_count'];
            $objMdlUserPointLog->save($params_pointlog);//custom e dengLy 2015.1.8

            // 向积分user_experience记录表中存入评论增值
            $objMdlUserExp  = app::get('sysuser')->model('user_experience');
            $params_UserExp['user_id']=userAuth::id();
            $params_UserExp['modified_time']=time();
            $params_UserExp['behavior_type']='scmt';
            $params_UserExp['remark']='当前积分来自订单：'.$params['tid'];
            $params_UserExp['behavior']='卖家评论获得经验值';
            $params_UserExp['experience']=$total_points-$result_points['point_count'];
            $objMdlUserExp->save($params_UserExp);//custom e dengLy 2015.1.8

            //sysuser_user中experience更新
            $experience= $total_points;
            $sysuserMdl=app::get('sysuser')->model('user');
            $sysuserMdl->update(array('experience'=>$experience),array('user_id'=>userAuth::id()));

            //评论完，更改
            $trade_Mdl=app::get('systrade')->model('trade');
            $trade_Mdl->update(array('sell_rate'=>'1'), array('tid' =>$tids));//更新订单卖家评论状态

            redirect::action('toptemai_ctl_trade_list@index')->send();exit;
        }
        return $this->page('toptemai/trade/comment.html', $pagedata);
    }

    /*人民币小写转化为大写函数 edit by nie 2016-1-15*/
	private function rmb_format($ns){
		static $cnums=array("零","壹","贰","叁","肆","伍","陆","柒","捌","玖"),
		$cnyunits=array("圆","角","分"),
		$grees=array("拾","佰","仟","万","拾","佰","仟","亿");
		list($ns1,$ns2)=explode(".",$ns,2);
		$ns2=array_filter(array($ns2[1],$ns2[0]));
		$ret=array_merge($ns2,array(implode("",$this->_cny_map_unit(str_split($ns1),$grees)),""));
		$ret=implode("",array_reverse($this->_cny_map_unit($ret,$cnyunits)));
		$money = str_replace(array_keys($cnums),$cnums,$ret);
		if(mb_substr($money,0,1,'utf-8') == '圆'){
			$money='零'.$money;
		}
		return $money;
	}

    private function _cny_map_unit($list,$units) {
        $ul=count($units);
        $xs=array();
        foreach (array_reverse($list) as $x) {
            $l=count($xs);
            if ($x!="0" || !($l%4)) $n=($x=='0'?'':$x).($units[($l-1)%$ul]);
            else $n=is_numeric($xs[0][0])?$x:'';
            array_unshift($xs,$n);
        }
        return $xs;
	}

    /**
	 * 人民币小写转大写
	 *
	 * @param string $number 数值
	 * @param string $int_unit 币种单位，默认"元"，有的需求可能为"圆"
	 * @param bool $is_round 是否对小数进行四舍五入
	 * @param bool $is_extra_zero 是否对整数部分以0结尾，小数存在的数字附加0,比如1960.30
	 * @return string
	 */
	private function cny($money = 0, $int_unit = '元', $is_round = true, $is_extra_zero = false) {
		// 将数字切分成两段
		$parts = explode ( '.', $money, 2 );
		$int = isset ( $parts [0] ) ? strval ( $parts [0] ) : '0';
		$dec = isset ( $parts [1] ) ? strval ( $parts [1] ) : '';

		// 如果小数点后多于2位，不四舍五入就直接截，否则就处理
		$dec_len = strlen ( $dec );
		if (isset ( $parts [1] ) && $dec_len > 2) {
			$dec = $is_round ? substr ( strrchr ( strval ( round ( floatval ( "0." . $dec ), 2 ) ), '.' ), 1 ) : substr ( $parts [1], 0, 2 );
		}

		// 当number为0.001时，小数点后的金额为0元
		if (empty ( $int ) && empty ( $dec )) {
			return '零';
		}

		// 定义
		$chs = array ('0', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖' );
		$uni = array ('', '拾', '佰', '仟' );
		$dec_uni = array ('角', '分' );
		$exp = array ('', '万' );
		$res = '';

		// 整数部分从右向左找
		for($i = strlen ( $int ) - 1, $k = 0; $i >= 0; $k ++) {
			$str = '';
			// 按照中文读写习惯，每4个字为一段进行转化，i一直在减
			for($j = 0; $j < 4 && $i >= 0; $j ++, $i --) {
				$u = $int {$i} > 0 ? $uni [$j] : ''; // 非0的数字后面添加单位
				$str = $chs [$int {$i}] . $u . $str;
			}
			$str = rtrim ( $str, '0' ); // 去掉末尾的0
			$str = preg_replace ( "/0+/", "零", $str ); // 替换多个连续的0
			if (! isset ( $exp [$k] )) {
				$exp [$k] = $exp [$k - 2] . '亿'; // 构建单位
			}
			$u2 = $str != '' ? $exp [$k] : '';
			$res = $str . $u2 . $res;
		}

		// 如果小数部分处理完之后是00，需要处理下
		$dec = rtrim ( $dec, '0' );
		// 小数部分从左向右找
		if (! empty ( $dec )) {
			$res .= $int_unit;

			// 是否要在整数部分以0结尾的数字后附加0，有的系统有这要求
			if ($is_extra_zero) {
				if (substr ( $int, - 1 ) === '0') {
					$res .= '零';
				}
			}

			for($i = 0, $cnt = strlen ( $dec ); $i < $cnt; $i ++) {
				$u = $dec {$i} > 0 ? $dec_uni [$i] : ''; // 非0的数字后面添加单位
				$res .= $chs [$dec {$i}] . $u;
				if ($cnt == 1)
					$res .= '整';
			}

			$res = rtrim ( $res, '0' ); // 去掉末尾的0
			$res = preg_replace ( "/0+/", "零", $res ); // 替换多个连续的0
		} else {
			$res .= $int_unit . '整';
		}
		return $res;
	}

    /*人民币小写转化为大写函数 edit by zmq 2016-3-2*/
    function num_to_rmb($num){
        $c1 = "零壹贰叁肆伍陆柒捌玖";
        $c2 = "分角元拾佰仟万拾佰仟亿";
        //精确到分后面就不要了，所以只留两个小数位
        $num = round($num, 2); 
        //将数字转化为整数
        $num = $num * 100;
        if (strlen($num) > 10) {
                return "金额太大，请检查";
        } 
        $i = 0;
        $c = "";
        while (1) {
                if ($i == 0) {
                        //获取最后一位数字
                        $n = substr($num, strlen($num)-1, 1);
                } else {
                        $n = $num % 10;
                }
                //每次将最后一位数字转化为中文
                $p1 = substr($c1, 3 * $n, 3);
                $p2 = substr($c2, 3 * $i, 3);
                if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
                        $c = $p1 . $p2 . $c;
                } else {
                        $c = $p1 . $c;
                }
                $i = $i + 1;
                //去掉数字最后一位了
                $num = $num / 10;
                $num = (int)$num;
                //结束循环
                if ($num == 0) {
                        break;
                } 
        }
        $j = 0;
        $slen = strlen($c);
        while ($j < $slen) {
                //utf8一个汉字相当3个字符
                $m = substr($c, $j, 6);
                //处理数字中很多0的情况,每次循环去掉一个汉字“零”
                if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
                        $left = substr($c, 0, $j);
                        $right = substr($c, $j + 3);
                        $c = $left . $right;
                        $j = $j-3;
                        $slen = $slen-3;
                } 
                $j = $j + 3;
        } 
        //这个是为了去掉类似23.0中最后一个“零”字
        if (substr($c, strlen($c)-3, 3) == '零') {
                $c = substr($c, 0, strlen($c)-3);
        }
        //将处理的汉字加上“整”
        if (empty($c)) {
                return "零元整";
        }else{
                return $c . "整";
        }
    }
}
