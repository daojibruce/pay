<?php
class topc_ctl_member_trade extends topc_ctl_member {

    public function tradeList()
    {
        $user_id = userAuth::id();
        $postdata = input::get();
        if(input::get('status'))
        {
            $status =input::get('status');
        }
        $params = array(
            'user_id' => userAuth::id(),
            'order_type' => 'normal',
            'status' => $status,
            'page_no' =>intval($postdata['pages']) ? intval($postdata['pages']) : 1,
            'page_size' =>intval($this->limit),
            'order_by' =>'created_time desc',
            'fields' =>'order.spec_nature_info,tid,shop_id,user_id,temai_user_id,status,cancel_status,payment,points_fee,total_fee,post_fee,payed_fee,receiver_name,created_time,receiver_mobile,discount_fee,need_invoice,adjust_fee,order.title,order.price,order.num,order.pic_path,order.tid,order.oid,order.aftersales_status,buyer_rate,order.complaints_status,order.sku_id,order.item_id,order.shop_id,order.status,order.spec_nature_info,activity,pay_type,order.sendnum,order.gift_data',
        );

        //如果执行了搜索
        if($postdata['keyword'])
        {
            $params['keyword'] = $postdata['keyword'];
            $tradelist = app::get('topc')->rpcCall('trade.order.list.item',$params);
        }
        else
        {
            $tradelist = app::get('topc')->rpcCall('trade.get.list',$params,'buyer');
        }

        $count = $tradelist['count'];
        $tradelist = $tradelist['list'];
        /*foreach ($tradelist as $k => $v) {
            foreach ($v['order'] as $key => $val) {
                $info = app::get('sysitem')->model('sku')->getRow('spec_code',['sku_id' => $val['sku_id']]);
                $tradelist[$k]['order'][$key]['spec_code'] = $info['spec_code'];
            }
        }*/
        foreach( $tradelist as $key=>&$row)
        {
            $tradelist[$key]['is_buyer_rate'] = false;

            foreach( $row['order'] as $k=>$orderListData )
            {
                if( $row['buyer_rate'] == '0' && $row['status'] == 'TRADE_FINISHED' )
                {
                    $tradelist[$key]['is_buyer_rate'] = true;
                }

                if( isset($orderListData['aftersales_status']) && $orderListData['aftersales_status'] )
                {
                    $afterSelf = app::get('topc')->rpcCall('aftersales.get.bn',['oid'=>$orderListData['oid'],'fields'=>'aftersales_type']);
                    $tradelist[$key]['order'][$k]['aftersales_type'] = $afterSelf['aftersales_type'];
                }
                if($orderListData['gift_data'])
                {

                    $tradelist[$key]['gift_count'] += count($orderListData['gift_data']);
                }
            }
            // 获取店铺子域名
            $row['subdomain'] = app::get('topc')->rpcCall('shop.subdomain.get',array('shop_id'=>$row['shop_id']))['subdomain'];
        }

        //获取默认图片信息
        $pagedata['defaultImageId']= kernel::single('image_data_image')->getImageSetting('item');

        $pagedata['trades'] = $tradelist;
        $pagedata['pagers'] = $this->__pages($postdata['pages'],$postdata,$count);
        $pagedata['count'] = $count;
        $pagedata['action'] = 'topc_ctl_member_trade@tradeList';
        $pagedata['imurl'] = app::get('topc')->res_full_url . '/im.png';
        $this->action_view = "trade/list.html";
        return $this->buycenterOutput($pagedata);
    }

    public function tradeDetail()
    {
        $params['tid'] = input::get('tid');
        $params['user_id'] = userAuth::id();
        /* custom s  zhm */
        //增加 合同状态字段contract_status
        $params['fields'] = "tid,dlytmpl_id,contract_id,trade_from,update_status,status,payment,delivery_goods_time,contract_status,post_fee,pay_type,payed_fee,receiver_state,receiver_city,receiver_district,receiver_address,trade_memo,receiver_name,receiver_mobile,receiver_phone,ziti_addr,ziti_memo,orders.price,orders.num,orders.title,orders.item_id,orders.pic_path,total_fee,discount_fee,buyer_rate,adjust_fee,orders.total_fee,orders.adjust_fee,orders.spec_nature_info,orders.sku_id,created_time,shop_id,need_invoice,invoice_name,invoice_type,invoice_main,activity,cancel_reason,invoice_tfn,invoice_bank_name,invoice_bank_num,invoice_addr,invoice_mobile,position,need_econtract,contract_status,update_status,order_type,invoice_vat_main";
        /* custom e  zhm */
        $trade = app::get('topc')->rpcCall('trade.get',$params,'buyer');
        /*线下合同  edit by nie 2016-1-18*/
        $trade['unfeePayment'] = $trade['payment'];
        $contentObj = app::get('systrade')->model('contract');
        $trade['contact_img'] = $contentObj->getRow('contract_img',array('contract_id'=>$trade['contract_id']))['contract_img'];
        $pagedata['contract']=$contentObj->getrow('contacts,phone,contract_img,contract_id,shop_id',array('contract_id'=>$trade['contract_id'],'shop_id'=>$trade['shop_id']));
        $contract_list = unserialize($pagedata['contract']['contract_img']);
        unset($pagedata['contract']['contract_img']);
        foreach($contract_list as $row){
            $file_name =  explode('/',urldecode($row));
            $pagedata['contract']['contract_img'][] = array('title'=>end($file_name),'url'=>$row);
        }

        /*电子合同信息edit by nie 2016-1-9*/
        $trade['delivery_goods_time'] = date("Y-m-d ", $trade['delivery_goods_time']);
        $pagedata['time'] = date("Y-m-d ",time());
        $trade['cny_payment'] = $this->num_to_rmb($trade['unfeePayment']);
        $shop_id = $trade['shop_id'];
        $objMdlshopinfo = app::get('sysshop')->model('shop_info');
        $objMdlelecontract = app::get('systrade')->model('elecontract');
        $objMdlitem = app::get('sysitem')->model('item');
        $objMdlbrand = app::get('syscategory')->model('brand');
        $objMdlsku = app::get('sysitem')->model('sku');
        $shopinfo = $objMdlshopinfo->getRow('company_name',array('shop_id' =>$shop_id));
        $order_adjust_fee=0;
        foreach($trade['orders'] as $key =>$value){
            $order_adjust_fee += $value['adjust_fee'];
            $item_id = $value['item_id'];
            $sku_id = $value['sku_id'];
            $items = $objMdlitem->getRow('item_id,brand_id,unit',array('item_id' =>$item_id));
            $sku = $objMdlsku->getRow('price',array('sku_id' =>$sku_id));
            $sku_price = $sku['price'];
            $brand_id = $items['brand_id'];
            $unit = $items['unit'];
            $brands = $objMdlbrand->getRow('brand_name,brand_id',array('brand_id' =>$brand_id));
            $brand_name = $brands['brand_name'];
            $trade['orders'][$key]['unit'] = $unit;
            $trade['orders'][$key]['brand_name'] = $brand_name;
            $trade['orders'][$key]['sku_price'] = $sku_price;
            $trade['orders'][$key]['price'] = $sku_price;
        }

        //获取电子合同信息
        $elecontract = $objMdlelecontract->getRow('*',array('tid' =>$params['tid']));
        if($elecontract){
            $payment = $trade['payment'];
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
            $trade['elecontract'] = $elecontract;
        }
        /*电子合同信息edit by nie 2016-1-9*/
        if($trade['dlytmpl_id'] == 0 && $trade['ziti_addr']){
            $pagedata['ziti'] = "true";
        }

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
                $tmp['amount'] = $elecontract['pay_type']['num' . $ii];
                $payData[] = $tmp;
            }
        } else {
            $payinfo  = array(
                'times' => 1,
                'amount' => $trade['payment'],
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

        // echo '<pre>';print_r($payData);die();
        $pagedata['payData'] = $payData;
        //获取该订单的发货信息
        $objMdlDelivery = app::get('syslogistics')->model('delivery');
        $objMdlDeliveryDetail = app::get('syslogistics')->model('delivery_detail');
        $deliveryRes=$objMdlDelivery->getList('*',array('tid'=>$params['tid']));
        foreach ($deliveryRes as $dk=>$dv){
            $deliveryRes[$dk]['delivery_detail']=$objMdlDeliveryDetail->getList('*',array('delivery_id'=>$dv['delivery_id']));
        }

        //格式化实付金额
        $elecontractRes = $objMdlelecontract->getRow('*',array('tid' =>$params['tid']));
        $objMdlDelivery = app::get('syslogistics')->model('delivery');
        $payNode=empty($elecontractRes['pay_type']) ? array(): $elecontractRes['pay_type'];
        $payNum=$this->parseNum($payNode);
        $payfee=array($payNode['pay_1'],$payNode['pay_2'],$payNode['pay_3'],$payNode['pay_4'],$payNode['pay_5']);
        $numfee=array($payNode['num1'],$payNode['num2'],$payNode['num3'],$payNode['num4'],$payNode['num5']);
        switch ($elecontractRes['is_part_pay']){
            case '0':
                $payment = $trade['payment'];
                break;
            case '1':
                $payment = $numfee[$trade['position']];

                //$paymentrate = $payfee[$trade['position']];
                //$payment=($paymentrate/100)*$trade['payment'];
                break;
        }
        $trade['paymentrate']=$payment;
        $pagedata['company_name'] = $shopinfo['company_name'];
        $trade['total_adjust_fee'] = $trade['payment']-$trade['total_fee']-$trade['post_fee'];

        $pagedata['trade'] = $trade;
        $longpayModel=app::get('systrade')->model('longpay');
        $longpay_info=$longpayModel->getRow('*',array('tid'=>$params['tid']));
        $pagedata['trade']['longpay_id']=$longpay_info['longpay_id'];
        $pagedata['logi'] = app::get('topc')->rpcCall('delivery.get',array('tid'=>$params['tid']));
        $pagedata['action'] = 'topc_ctl_member_trade@tradeList';
        $pagedata['delivery']=$deliveryRes;
        if ($pagedata['trade']['need_invoice'] == '1' && $pagedata['trade']['invoice_type'] == 'vat') {
            $pagedata['trade']['invoice_vat_main'] = unserialize($pagedata['trade']['invoice_vat_main']);
        }
        if ($pagedata['trade']['order_type'] == 'proofing') {
            $providerMdl = app::get('sysproofing')->model('provider');
            $provider_id = substr($shop_id,6);
            $providerInfo = $providerMdl->getRow('provider_name',array('provider_id' =>$provider_id));
            $pagedata['company_name'] = $providerInfo['provider_name'];
        }
        // echo '<pre>';print_r($pagedata);die();
        $this->action_view = "trade/detail.html";
        return $this->output($pagedata);
    }

    public function saveInvoice()
    {
        $postData = input::get();
        $tradeMdl = app::get('systrade')->model('trade');
        $data = ['tid' => $postData['tid']];
        if ($postData['need_invoice'] == '1') {
            $data['need_invoice'] = 1;
            //普通发票
            if ($postData['invoice_type'] == 'normal') {
                $data['invoice_name'] = $postData['invoice_name'];
                $data['invoice_type'] = $postData['invoice_type'];
                if (!trim($postData['invoice_main'])) return $this->splash('error','','请填写发票抬头');
                $data['invoice_main'] = trim($postData['invoice_main']);
                $data['invoice_vat_main'] = '';
                if ($tradeMdl->save($data)){
                    return $this->splash('success','','发票信息保存成功！');
                } else {
                    return $this->splash('error','','保存失败！');
                }
            } elseif ($postData['invoice_type'] == 'vat') {
                $data['invoice_name'] = '';
                $data['invoice_type'] = $postData['invoice_type'];
                $data['invoice_main'] = '';
                if (!trim($postData['invoice_vat_main']['company_name'])) return $this->splash('error','','请填写公司名');
                if (!trim($postData['invoice_vat_main']['registration_number'])) return $this->splash('error','','请填写公司登记号');
                if (!trim($postData['invoice_vat_main']['company_address'])) return $this->splash('error','','请填写公司地址');
                if (!trim($postData['invoice_vat_main']['company_phone'])) return $this->splash('error','','请填写公司电话');
                if (!trim($postData['invoice_vat_main']['bankname'])) return $this->splash('error','','请填写银行开户名');
                if (!trim($postData['invoice_vat_main']['bankaccount'])) return $this->splash('error','','请填写银行账号');
                $data['invoice_vat_main'] = serialize($postData['invoice_vat_main']);
                if ($tradeMdl->save($data)){
                    return $this->splash('success','','发票信息保存成功！');
                } else {
                    return $this->splash('error','','保存失败！');
                }
            }
        } else {
            $data['need_invoice'] = 0;
            if ($tradeMdl->save($data)){
                return $this->splash('success','','发票信息保存成功！');
            } else {
                return $this->splash('error','','保存失败！');
            }
        }
    }

    public function ajaxGetTrack()
    {
        $postData = input::get();
        $pagedata['track'] = app::get('topc')->rpcCall('logistics.tracking.get.hqepay',$postData);
        $pagedata['tracking'] = app::get('syslogistics')->getConf('syslogistics.order.tracking');
        return view::make('topc/member/trade/logistics.html', $pagedata);
    }


    public function ajaxCancelTrade()
    {
        $validator = validator::make([input::get('tid')],['numeric']);
        if ($validator->fails())
        {
            return $this->splash('error',null,'订单格式错误！');
        }
        $pagedata['tid'] = input::get('tid');
        $pagedata['reason'] = config::get('tradeCancelReason');
        return view::make('topc/member/gather/cancel.html', $pagedata);
    }

    public function ajaxConfirmTrade()
    {
        $validator = validator::make([input::get('tid')],['numeric']);
        if ($validator->fails())
        {
            return $this->splash('error',null,'订单格式错误！');
        }
        $pagedata['tid'] = input::get('tid');
        return view::make('topc/member/gather/confirm.html', $pagedata);
    }

    public function cancelOrderBuyer()
    {
        $reasonSetting = config::get('tradeCancelReason');
        $reasonPost = input::get('cancel_reason');
        $validator = validator::make([$reasonPost],['required'],['取消原因必选!']);
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                return $this->splash('error',null,$error[0]);
            }
        }
        if($reasonPost == "other")
        {
            $cancelReason = input::get('other_reason');
            $validator = validator::make([trim($cancelReason)],['required|max:50'],['取消原因必须填写!|取消原因最多填写50个字']);
            if ($validator->fails())
            {
                $messages = $validator->messagesInfo();
                foreach( $messages as $error )
                {
                    return $this->splash('error',null,$error[0]);
                }
            }
        }
        else
        {
            $cancelReason = $reasonSetting['user'][$reasonPost];
        }

        $params['tid'] = input::get('tid');
        $params['user_id'] = userAuth::id();
        $params['cancel_reason'] = $cancelReason;
        try
        {
            app::get('topc')->rpcCall('trade.cancel.create',$params);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg,true);
        }
        $url = url::action('topc_ctl_member_trade@tradeList');
        $msg = app::get('topc')->_('订单取消成功');

        // 获取订单状态
        $tparams['tid'] = input::get('tid');
        $tparams['user_id'] = userAuth::id();
        $tparams['fields'] = "tid,status";
        $trade = app::get('topc')->rpcCall('trade.get',$tparams,'buyer');
        if($trade['status'] !='WAIT_BUYER_PAY')
        {
            $msg = app::get('topc')->_('申请取消成功');
        }

        return $this->splash('success',$url,$msg,true);
    }

    public function confirmReceipt()
    {
        $params['tid'] = input::get('tid');
        $params['user_id'] = userAuth::id();
        try
        {
            app::get('topc')->rpcCall('trade.confirm',$params,'buyer');
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg,true);
        }
        if($oid = input::get('oid',0))
        {
            $url = url::action('topc_ctl_member_aftersales@aftersalesApply',['tid' => $params['tid'],'oid' => $oid]);
        }
        else
        {
            $url = url::action('topc_ctl_member_trade@tradeList');
        }
        $msg = app::get('topc')->_('订单确认收货完成');
        return $this->splash('success',$url,$msg,true);
    }

    /**
     * 分页处理
     * @param int $current 当前页
     *
     * @return $pagers
     */
    private function __pages($current,$filter,$count)
    {
        //处理翻页数据
        $current = $current ? $current : 1;
        $filter['pages'] = time();
        $limit = $this->limit;

        if( $count > 0 ) $totalPage = ceil($count/$limit);
        $pagers = array(
            'link'=>url::action('topc_ctl_member_trade@tradeList',$filter),
            'current'=>$current,
            'total'=>$totalPage,
            'token'=>time(),
        );
        return $pagers;
    }

    public function canceledTradeList()
    {
        $apiParams['page_no']  = intval(input::get('pages',1));
        if( input::get('tid') )
        {
            $apiParams['tid'] = input::get('tid');
        }
        $apiParams['page_size'] = intval($this->limit);
        $apiParams['fields'] = '*';
        $apiParams['user_id'] = userAuth::id();
        $data = app::get(topc)->rpcCall('trade.cancel.list.get', $apiParams);

        if( $data['total'] )
        {
            $filter = input::get();
            $pagedata['list'] = $data['list'];
            foreach($pagedata['list'] as $key=>$value)
            {
                foreach($value['order'] as $val)
                {
                    if($val['gift_data'])
                    {

                        $pagedata['list'][$key]['gift_count'] += count($val['gift_data']);
                    }
                }
            }


            //处理翻页数据
            $current = input::get('pages',1);
            $filter['pages'] = time();
            $limit = $this->limit;

            if( $data['total'] > 0 ) $totalPage = ceil($data['total']/$limit);
            $pagers = array(
                'link'=>url::action('topc_ctl_member_trade@canceledTradeList',$filter),
                'current'=>$current,
                'total'=>$totalPage,
                'token'=>time(),
            );
        }
        $pagedata['pagers'] = $pagers;
        $pagedata['action'] = 'topc_ctl_member_trade@canceledTradeList';
        $this->action_view = "trade/canceled.html";
        //获取默认图片信息
        $pagedata['defaultImageId']= kernel::single('image_data_image')->getImageSetting('item');
        return $this->buycenterOutput($pagedata);
    }

    public function canceledTradeDetail()
    {
        $cancelId = input::get('cancel_id');
        $data = app::get('topc')->rpcCall('trade.cancel.get',['user_id'=>userAuth::id(),'cancel_id'=>$cancelId]);
        $pagedata['data'] = $data;
        $pagedata['action'] = 'topc_ctl_member_trade@canceledTradeList';
        $this->action_view = "trade/canceled_detail.html";
        return $this->output($pagedata);
    }
    public function ajaxHint()
    {
        $validator = validator::make([input::get('tid'),input::get('oid')],['numeric','numeric']);
        if ($validator->fails())
        {
            return $this->splash('error',null,'订单格式错误！');
        }
        $pagedata = input::get();
        return view::make('topc/member/gather/hint.html', $pagedata);

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
    /*人民币小写转化为大写函数 edit by zmq 2016-3-2*/

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

    /*
    * 延长付款申请
    * zmq 2016-8-16
    */
    public function longPay()
    {
        $params['tid']=input::get('tid');
        $params['user_id'] = userAuth::id();
        /* custom s  zhm */
        //增加 合同状态字段contract_status
        $params['fields'] = "tid,dlytmpl_id,contract_id,trade_from,update_status,status,payment,delivery_goods_time,contract_status,post_fee,pay_type,payed_fee,receiver_state,receiver_city,receiver_district,receiver_address,trade_memo,receiver_name,receiver_mobile,receiver_phone,ziti_addr,ziti_memo,orders.price,orders.num,orders.title,orders.item_id,orders.pic_path,total_fee,discount_fee,buyer_rate,adjust_fee,orders.total_fee,orders.adjust_fee,orders.spec_nature_info,orders.sku_id,created_time,shop_id,temai_user_id,need_invoice,invoice_name,invoice_type,invoice_main,activity,cancel_reason,invoice_tfn,invoice_bank_name,invoice_bank_num,invoice_addr,invoice_mobile,position,need_econtract,contract_status,update_status";
        /* custom e  zhm */
        $trade = app::get('topc')->rpcCall('trade.get',$params,'buyer');
        $objMdlelecontract = app::get('systrade')->model('elecontract');
        //获取电子合同信息
        $elecontract = $objMdlelecontract->getRow('*',array('tid' =>$params['tid']));
        $pay_info=$elecontract['pay_type'];

        if($elecontract){
            $payment = $trade['payment'];
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
            $trade['elecontract'] = $elecontract['pay_type'];
        }
        $pagedata['trade'] = $trade['elecontract'];
        $pagedata['trade']['payment']=$trade['payment'];
        $pagedata['trade']['tid']=$trade['tid'];
        $pagedata['trade']['temai_user_id']=$trade['temai_user_id'];
        $pagedata['pay_info']=$pay_info;
        $pagedata['trade']['times']=input::get('times');


        $longpayModel=app::get('systrade')->model('longpay');
        $longpay_info=$longpayModel->getRow('*',array('tid'=>$params['tid']));
        $pagedata['longpay_info']=$longpay_info;

        // echo '<pre>';print_r($pagedata);die();
        return view::make('topc/member/trade/longpay.html',$pagedata);
    }

    /**
     * 保存延迟付款申请
     * author:zmq
     * Date:2016-8-17
     * @return string
     */
    public function saveLongpay()
    {
        $postData = utils::_filter_input(input::get());
        $pay_info=array();
        $payment=$postData['payment'];
        $tid = $postData['tid'];
        $temai_user_id=$postData['temai_user_id'];
        $pay_1=$postData['pay_1'];
        $num1=$payment*$pay_1/100;
        $pay_2=$postData['pay_2'];
        $num2=$payment*$pay_2/100;
        if ($postData['pay_3']) {
            $pay_3=$postData['pay_3'];
            $num3=$payment*$pay_3/100;
            $detail_time1=$postData['detail_time1'];
        }
        else{
            $pay_3=0;
            $num3=0;
            $detail_time1=0;
        }
        if ($postData['pay_4']) {
            $pay_4=$postData['pay_4'];
            $num4=$payment*$pay_4/100;
            $detail_time2=$postData['detail_time2'];
        }
        else{
            $pay_4=0;
            $num4=0;
            $detail_time2=1;
        }
        if ($postData['pay_5']) {
            $pay_5=$postData['pay_5'];
            $num5=$payment*$pay_5/100;
            $detail_time3=$postData['detail_time3'];
        }
        else{
            $pay_5=0;
            $num5=0;
            $detail_time3=2;
        }

        $memo=$postData['memo'];

        $pay_info=array(
            'pay_1'=>$pay_1,
            'num1'=>$num1,
            'pay_2'=>$pay_2,
            'num2'=>$num2,
            'pay_3'=>$pay_3,
            'num3'=>$num3,
            'detail_time1'=>$detail_time1,
            'pay_4'=>$pay_4,
            'num4'=>$num4,
            'detail_time2'=>$detail_time2,
            'pay_5'=>$pay_5,
            'num5'=>$num5,
            'detail_time3'=>$detail_time3,
        );
        $pay_type=serialize($pay_info);
        $time=time();


        $longpayModel=app::get('systrade')->model('longpay');
        $longpay_info=$longpayModel->getRow('*',array('tid'=>$tid));

        $db = app::get('systrade')->database();
        $db->beginTransaction();

        if ($longpay_info['longpay_id']) {
            try{
                $longpayModel->update(array('pay_type'=> $pay_type,'status'=>'WAIT_AGREE','memo'=>$memo,'modified_time'=>$time),array('longpay_id'=>$longpay_info['longpay_id']));
                $db->commit();
                $url = url::action('topc_ctl_member_trade@tradeDetail',array('tid' => $tid));
                $msg = app::get('topc')->_('延迟付款提交申请成功');
                return $this->splash('success', $url, $msg, true);
            }catch(\Exception $e){
                $db->rollback();
                throw $e;
            }
        }
        else{
            try{
                $data=array(
                    'temai_user_id'=>$temai_user_id,
                    'user_id'=>userAuth::id(),
                    'tid'=>$tid,
                    'status'=>'WAIT_AGREE',
                    'pay_type'=>$pay_type,
                    'memo'=>$memo,
                    'created_time'=>time(),
                );
                $longpayModel->insert($data);
                $db->commit();
                $url = url::action('topc_ctl_member_trade@tradeDetail',array('tid' => $tid));
                $msg = app::get('topc')->_('延迟付款提交申请成功');
                return $this->splash('success', $url, $msg, true);
            }catch(\Exception $e){
                $db->rollback();
                throw $e;
            }
        }
    }

    /**
     * 用户拒绝订单合同
     *
     * @author	daojibruce
     * @date	2016/3/7 17:20
     */
    public function rejectEleContract() {
        $pagedata = input::get();
        $tid = $pagedata['tid'];
        $objMdltrade = app::get('systrade')->model('trade');
        $objMdlorder = app::get('systrade')->model('order');
        try{
            $objMdltrade->update(array('status' =>'REJECT_CONTRACT'),array('tid' =>$pagedata['tid']));
            $objMdlorder->update(array('status' =>'REJECT_CONTRACT'),array('tid' =>$pagedata['tid']));
        }catch(Exception $e){
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg,true);
        }
        $url = url::action('topc_ctl_member_trade@tradeList');
        kernel::single('sysuser_actionlog')->actionlog(array('memo'=>'拒绝签订电子合同['.$tid.']','action_id'=>$tid));
        $msg = app::get('topc')->_('已拒绝签订，等待卖家重新编辑电子合同！');
        return $this->splash('success','',$msg,true);
    }

    /*
     *  确认电子订单合同并付款
     *  2015.1.10
     *  custom s nie
     */
    public function confirmEleContract(){
        $pagedata = input::get();

        $pagedata['signed_time'] = time();
        $objMdltrade = app::get('systrade')->model('trade');
        $objMdlorder = app::get('systrade')->model('order');
        $objMdlelecontract = app::get('systrade')->model('elecontract');

        //获取订单支付类型
        $pay_type = $objMdltrade->getRow('pay_type',array('tid'=>$pagedata['tid']))['pay_type'];
        try{
            if($pay_type == "offline"){
                $objMdltrade->update(array('status' =>'WAIT_SELLER_SEND_GOODS'),array('tid' =>$pagedata['tid']));
                $objMdlorder->update(array('status' =>'WAIT_SELLER_SEND_GOODS'),array('tid' =>$pagedata['tid']));

                $msg = app::get('topc')->_('合同已确认，等待卖家发货！');
                $url = url::action('topc_ctl_member_trade@tradeList');
            }else{
                $objMdltrade->update(array('status' =>'WAIT_BUYER_PAY'),array('tid' =>$pagedata['tid']));
                $objMdlorder->update(array('status' =>'WAIT_BUYER_PAY'),array('tid' =>$pagedata['tid']));

                $msg = app::get('topc')->_('合同已确认，请付款！');
                $url = url::action('topc_ctl_paycenter@createPay', array('tid' => $pagedata['tid'], 'merge' => false));
            }
            $objMdlelecontract->update($pagedata,array('tid' =>$pagedata['tid']));
        }catch(Exception $e){
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg,true);
        }
        kernel::single('sysuser_actionlog')->actionlog(array('memo'=>'确认电子合同['.$pagedata['tid'].']','action_id'=>$pagedata['tid']));
        return $this->splash('success',$url,$msg,true);
    }

    /*
    *查看生产进度
    *zmq 2016-7-8
    */
    public function getProgress()
    {
        $oids = input::get('oid');
        $orderModel=app::get('systrade')->model('order');
        $orderRow=$orderModel->getRow('oid,progress',array('oid'=>$oids));
        $pagedata['progress']=$orderRow;
        $pagedata['progress']['progress']=unserialize($orderRow['progress']);
        // echo '<pre>';print_r($pagedata);die();
        return view::make('topc/member/trade/status/progress.html',$pagedata);
    }

    public function myRequirementOrder()
    {
        $user_id = userAuth::id();
        $postdata = input::get();
        if(input::get('status'))
        {
            $status =input::get('status');
        }
        $params = array(
            'user_id' => userAuth::id(),
            'order_type' => 'proofing',
            'status' => $status,
            'page_no' =>intval($postdata['pages']) ? intval($postdata['pages']) : 1,
            'page_size' =>intval($this->limit),
            'order_by' =>'created_time desc',
            'fields' =>'order.spec_nature_info,tid,temai_user_id,user_id,status,cancel_status,payment,points_fee,total_fee,post_fee,payed_fee,receiver_name,created_time,receiver_mobile,discount_fee,need_invoice,adjust_fee,order.title,order.price,order.num,order.pic_path,order.tid,order.oid,order.aftersales_status,buyer_rate,order.complaints_status,order.item_id,order.temai_user_id,order.status,order.spec_nature_info,activity,pay_type,order.sendnum,order.gift_data',
        );

        //如果执行了搜索
        if($postdata['keyword'])
        {
            $params['keyword'] = $postdata['keyword'];
            $tradelist = app::get('topc')->rpcCall('trade.order.list.item',$params);
        }
        else
        {
            $tradelist = app::get('topc')->rpcCall('trade.get.list',$params,'buyer');
        }

        $count = $tradelist['count'];
        $tradelist = $tradelist['list'];

        foreach( $tradelist as $key=>&$row)
        {
            $tradelist[$key]['is_buyer_rate'] = false;

            foreach( $row['order'] as $k=>$orderListData )
            {
                if( $row['buyer_rate'] == '0' && $row['status'] == 'TRADE_FINISHED' )
                {
                    $tradelist[$key]['is_buyer_rate'] = true;
                }

                if( isset($orderListData['aftersales_status']) && $orderListData['aftersales_status'] )
                {
                    $afterSelf = app::get('topc')->rpcCall('aftersales.get.bn',['oid'=>$orderListData['oid'],'fields'=>'aftersales_type']);
                    $tradelist[$key]['order'][$k]['aftersales_type'] = $afterSelf['aftersales_type'];
                }
                if($orderListData['gift_data'])
                {

                    $tradelist[$key]['gift_count'] += count($orderListData['gift_data']);
                }
            }
            // 获取店铺子域名
            $row['subdomain'] = '';
        }

        //获取默认图片信息
        $pagedata['defaultImageId']= kernel::single('image_data_image')->getImageSetting('item');

        $pagedata['trades'] = $tradelist;
        $pagedata['pagers'] = $this->__pages($postdata['pages'],$postdata,$count);
        $pagedata['count'] = $count;
        $this->action_view = "myRequirementOrder.html";
        $pagedata['action'] = 'topc_ctl_member_trade@myRequirementOrder';
        return $this->proofingOutput($pagedata);
    }
}
