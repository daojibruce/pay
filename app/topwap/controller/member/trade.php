<?php

/**
 * trade.php 会员订单中心
 *
 * @author     Xiaodc
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topwap_ctl_member_trade extends topwap_ctl_member {
    public $tradeStatus = array(
            '1' => 'WAIT_BUYER_PAY',
            '2' => 'WAIT_SELLER_SEND_GOODS',
            '3' => 'WAIT_BUYER_CONFIRM_GOODS',
            '4' => 'TRADE_FINISHED',
            '5' => 'WAIT_SENDCONTRACT',
            '6' => 'WAIT_CONFIRM',
            '7' => 'REJECT_CONTRACT',
    );
    public $limit = 6;

    // 会员中心全部订单
    public function tradeList()
    {
        $postdata = input::get();
        $pagedata = $this->__getTrade($postdata);
        return $this->page('topwap/member/trade/index.html', $pagedata);
    }


    // 订单详情
    public function detail()
    {

        $this->setLayoutFlag('order_detail');
        $params['tid'] = input::get('tid');
        $params['user_id'] = userAuth::id();
        $params['fields'] = "tid,position,dlytmpl_id,status,shop_id,user_id,hongbao_fee,shipping_type,payment,delivery_goods_time,contract_status,payed_fee,need_invoice,invoice_name,invoice_type,invoice_main,invoice_vat_main,points_fee,post_fee,cancel_status,receiver_state,receiver_city,receiver_district,ziti_addr,ziti_memo,receiver_address,trade_memo,receiver_name,receiver_mobile,receiver_phone,created_time,orders.oid,orders.price,orders.num,orders.title,orders.aftersales_status,orders.complaints_status,orders.item_id,orders.pic_path,orders.sku_id,total_fee,discount_fee,buyer_rate,adjust_fee,activity,pay_type,cancel_reason,invoice_tfn,invoice_bank_name,invoice_bank_num,invoice_addr,invoice_mobile,orders.spec_nature_info,orders.gift_data";
        $trade = app::get('topwap')->rpcCall('trade.get', $params, 'buyer');
        $trade['unfeePayment'] = $trade['payment'] - $trade['post_fee'];
        if ($trade ['shipping_type'] == 'ziti')
        {
            $pagedata ['ziti'] = "true";
        }

        $trade ['is_buyer_rate'] = false;
        foreach ($trade ['orders'] as $orderListData)
        {
            if( !$orderListData['aftersales_status'] && $trade['buyer_rate'] == '0' && $trade['status'] == 'TRADE_FINISHED' )
            {
                $trade ['is_buyer_rate'] = true;
                break;
            }
        }

        if ($trade ['shipping_type'] == 'ziti')
        {
            $pagedata ['ziti'] = "true";
        }
        // 订单配送方式
        $shippingName = array(
                'express' => '快递配送',
                'ziti' => '自提',
                'post' => '平邮',
                'ems' => 'EMS',
                'virtual' => '虚拟发货'
        );
        $trade ['shipping_type_name'] = $shippingName [$trade ['shipping_type']];

        // 获取发货信息
        $pagedata ['logi'] = app::get('topwap')->rpcCall('delivery.get', array(
                'tid' => $params ['tid']
        ));
        $pagedata ['title'] = "订单详情"; // 标题
        $pagedata ['point_rate'] = app::get('topwap')->rpcCall('point.setting.get', [
                'field' => 'point.deduction.rate'
        ]);
        $pagedata ['tracking'] = app::get('syslogistics')->getConf('syslogistics.order.tracking');
        $pagedata ['defaultImageId'] = kernel::single('image_data_image')->getImageSetting('item');

        //sku信息 及 品牌信息
        $objMdlitem = app::get('sysitem')->model('item');
        $objMdlbrand = app::get('syscategory')->model('brand');
        $objMdlsku = app::get('sysitem')->model('sku');
        foreach($trade['orders'] as $key =>$value){
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
            $trade['orders'][$key]['price'] = $sku_price*$value['num'];
            if( isset($value['aftersales_status']) && $value['aftersales_status'] )
            {
                $afterSelf = app::get('topm')->rpcCall('aftersales.get.bn',['oid'=>$value['oid'],'fields'=>'aftersales_type']);
                $trade['orders'][$key]['aftersales_type'] = $afterSelf['aftersales_type'];
            }
        }

        /* 电子合同信息 */
        $trade['delivery_goods_time'] = date("Y-m-d ", $trade['delivery_goods_time']);
        $trade['cny_payment'] = $this->num_to_rmb($trade['payment']);
        $shop_id = $trade['shop_id'];
        $objMdlshopinfo = app::get('sysshop')->model('shop_info');
        $shopinfo = $objMdlshopinfo->getRow('company_name',array('shop_id' =>$shop_id));
        $pagedata['company_name'] = $shopinfo['company_name'];

        $objMdlelecontract = app::get('systrade')->model('elecontract');

        $elecontract = $objMdlelecontract->getRow('*',array('tid' =>$params['tid']));

        if($elecontract){
            $payment = $trade['payment'];
            // $elecontract['pay_type'] = unserialize($elecontract['pay_type']);
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
        /* 电子合同信息 */

        $pagedata ['trade'] = $trade;
        if($trade['dlytmpl_id'] == 0 && $trade['ziti_addr']){
            $pagedata['ziti'] = "true";
        }

        return $this->page('topwap/member/trade/detail.html', $pagedata);
    }

    public function confirmReceipt()
    {
        $params['tid'] = input::get('tid');
        $params['user_id'] = userAuth::id();
        try
        {
            app::get('topwap')->rpcCall('trade.confirm',$params,'buyer');
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg,true);
        }
        $url = url::action('topwap_ctl_member_trade@tradeList');
        $msg = app::get('topm')->_('订单确认收货完成');
        return $this->splash('success',$url,$msg,true);
    }

    public function cancel()
    {

        $validator = validator::make([
                input::get('tid')
        ], [
                'numeric'
        ]);
        if ($validator->fails())
        {return $this->splash('error', null, '订单格式错误！', true);}

        $pagedata ['tid'] = $params ['tid'] = input::get('tid');
        $params ['user_id'] = userAuth::id();
        $params ['fields'] = "status,post_fee,payment,points_fee,pay_type";
        $pagedata ['trade'] = app::get('topm')->rpcCall('trade.get', $params);
        $pagedata ['reason'] = config::get('tradeCancelReason');
        $pagedata ['title'] = "取消订单"; // 标题
        return $this->page('topwap/member/trade/cancel/cancel.html', $pagedata);
    }

    public function canceledTradeList()
    {
        $postdata = input::get();
        $pagedata=$this->__getCancledTradeList($postdata);
        $pagedata ['title'] = "取消订单列表"; // 标题

        return $this->page('topwap/member/trade/cancel/canceled.html', $pagedata);
    }

    // 订单取消详情
    public function canceledTradeDetail()
    {
        $pagedata['title'] = "取消订单详情";  //标题
        $cancelId = input::get('cancel_id');
        $data = app::get('topwap')->rpcCall('trade.cancel.get',['user_id'=>userAuth::id(),'cancel_id'=>$cancelId]);
        $pagedata['data'] = $data;
        return $this->page('topwap/member/trade/cancel/detail.html',$pagedata);
    }

    // 订单详情中查看取消详情
    public function gotoCanceledTradeDetail()
    {
        $tid = input::get('tid');
        $params['tid'] = $tid;
        $params['user_id'] = userAuth::id();
        $params['fields'] = "tid,cancel.cancel_id";
        $trade = app::get('topwap')->rpcCall('trade.get', $params, 'buyer');
        if($trade['cancelInfo']['cancel_id'])
        {
            redirect::action('topwap_ctl_member_trade@canceledTradeDetail',array('cancel_id'=>$trade['cancelInfo']['cancel_id']))->send();exit;
        }
        redirect::action('topwap_ctl_member_trade@canceledTradeList')->send();exit;
    }

    // 处理取消订单
    public function cancelBuyer()
    {
        $this->setLayoutFlag('cart');
        $reasonSetting = config::get('tradeCancelReason');
        $reasonPost = input::get('cancel_reason');

        if(!$reasonPost)
        {
            $msg = app::get('topwap')->_('取消原因必填');
            return $this->splash('error',null,$msg);
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
            app::get('topwap')->rpcCall('trade.cancel.create',$params);
        }
        catch(Exception $e)
        {
            //$pagedata['msg'] = $e->getMessage();
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg,true);
            //$pagedata['cancelerror'] = true;
        }
        $url = url::action('topwap_ctl_member_trade@tradeList');
        $msg = app::get('topwap')->_('订单取消成功');
        //return $this->page('topm/member/trade/status/result.html', $pagedata);
        return $this->splash('success', $url, $msg, true);
    }

    // ajax方式获取订单列表
    public function ajaxTradeList()
    {
        try
        {
            $postdata = input::get();
            $pagedata = $this->__getTrade($postdata);

            if($pagedata ['trades'])
            {
                $data ['html'] = view::make('topwap/member/trade/list.html', $pagedata)->render();
            }
            else
            {
                $data ['html'] = view::make('topwap/empty/trade.html', $pagedata)->render();
            }

            $data ['pages'] = $pagedata ['pagers'];
            $data ['s'] = $pagedata ['status'];
            $data ['success'] = true;
        } catch ( Exception $e )
        {
            $msg = $e->getMessage();
            return $this->splash('error', null, $msg, true);
        }
        //var_dump($data['pages']['total']);
        return response::json($data);
        //return view::make('topwap/member/trade/list.html',$pagedata);
    }


    public function ajaxCanceledTradeList()
    {
        try {
            $postdata = input::get();
            $pagedata=$this->__getCancledTradeList($postdata);
            $data ['html'] = view::make('topwap/member/trade/cancel/list.html', $pagedata)->render();
            $data ['pages'] = $pagedata ['pagers'];
            $data ['s'] = $pagedata ['status'];
            $data ['success'] = true;
        } catch (Exception $e) {
            $msg = $e->getMessage();
            return $this->splash('error', null, $msg, true);
        }

        return response::json($data);
        exit();
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
        $current = ($current && $current <= 100 ) ? $current : 1;

        if( $count > 0 ) $totalPage = ceil($count/$this->limit);
        $pagers = array(
            'link'=>'',
            'current'=>intval($current),
            'total'=>intval($totalPage),
        );
        return $pagers;
    }

    // 订单物流
    public function logistics()
    {
        // 订单id
        $params ['tid'] = input::get('tid');
        $params ['user_id'] = userAuth::id();
        $params ['fields'] = "tid,status,shop_id,user_id,shipping_type,receiver_state,receiver_city,receiver_district,ziti_addr,ziti_memo,receiver_address,receiver_name,receiver_mobile,created_time";
        $trade = app::get('topwap')->rpcCall('trade.get', $params, 'buyer');
        $pagedata ['trade'] = $trade;
        // 获取发货信息
        $pagedata ['logi'] = app::get('topwap')->rpcCall('delivery.get', array(
                'tid' => $params ['tid']
        ));

        $tracking = app::get('syslogistics')->getConf('syslogistics.order.tracking');
        $pagedata['trackFlag'] = $tracking;
        // 获取物流详情
        if($pagedata['logi'] && $pagedata['logi']['corp_code']!='other')
        {
            if($tracking && $tracking =='true')
            {
                $logic_params['logi_no'] = $pagedata['logi']['logi_no'];
                $logic_params['corp_code'] = $pagedata['logi']['corp_code'];
                $log_info = app::get('topwap')->rpcCall('logistics.tracking.get.hqepay',$logic_params);
                krsort($log_info['tracker']);

                $pagedata['track'] = $log_info;
            }
        }

        $pagedata['title'] = app::get('topwap')->_('物流详情');
        return $this->page('topwap/member/trade/logistics.html', $pagedata);
    }

    // 获取取消的订单
    private function __getCancledTradeList($postdata)
    {

        if (isset($postdata['s']) && $postdata['s'])
        {
            if ($postdata['s'] == 4)
            {
                $status = 'TRADE_FINISHED';
                $params['buyer_rate'] = 0;
            }
            else
            {
                $status = $this->tradeStatus[$postdata ['s']];
            }
        }
        else
        {
            $postdata['s'] = 0;
        }

        if( $postdata['tid'] )
        {
            $params['tid'] = $postdata['tid'];
        }

        $params['status'] = $status;
        $params['user_id'] = userAuth::id();
        $params['page_no'] = intval($postdata ['pages']) ? intval($postdata ['pages']) : 1;
        $params['page_size'] =intval($this->limit);
        $params['fields'] = "cancel_id,shop_id,payed_fee,refunds_status,tid";
        $params['order_by'] = 'created_time desc';

        $data = app::get('topwap')->rpcCall('trade.cancel.list.get', $params);
        $count = $data ['total'];
        $pagedata ['list'] = $data ['list'];

        foreach($pagedata['list'] as $key=>$value)
        {
            foreach($value['order'] as $val)
            {
                if($val['gift_data'])
                {

                    $pagedata['list'][$key]['gift_count'] += array_sum(array_column($val['gift_data'],'gift_num'));
                }
                $pagedata['list'][$key]['itemnum'] += $val['num'];
            }
        }

        $pagedata ['status'] = $postdata ['s']; // 状态
        $pagedata ['count'] = $count;
        $pagedata ['pages'] = $params ['page_no'];
        $pagedata['pagers'] = $this->__pages($postdata['pages'],$postdata,$count);

        $pagedata ['defaultImageId'] = kernel::single('image_data_image')->getImageSetting('item');

        return $pagedata;
    }

    // 获取订单列表
    private function __getTrade($postdata)
    {

        if (isset($this->tradeStatus[$postdata ['s']]))
        {
            if ($postdata ['s'] == 4)
            {
                $status = 'TRADE_FINISHED';
                $params ['buyer_rate'] = 0;
            }
            else
            {
                $status = $this->tradeStatus [$postdata ['s']];
            }
        }
        else
        {
            $postdata ['s'] = 0;
        }

        $params ['status'] = $status;
        $params ['user_id'] = userAuth::id();
        $params ['page_no'] = intval($postdata ['pages']) ? intval($postdata ['pages']) : 1;
        $params ['page_size'] = intval($this->limit);
        $params ['order_by'] = 'created_time desc';
        $params ['fields'] = 'tid,shop_id,user_id,status,payment,points_fee,total_fee,cancel_status,itemnum,post_fee,payed_fee,receiver_name,created_time,receiver_mobile,discount_fee,need_invoice,adjust_fee,order.title,order.price,order.num,order.pic_path,order.tid,order.oid,order.aftersales_status,buyer_rate,order.complaints_status,order.item_id,order.shop_id,order.status,activity,pay_type,order.spec_nature_info,order.gift_data';

        $tradelist = app::get('topwap')->rpcCall('trade.get.list', $params);

        $count = $tradelist ['count'];
        $tradelist = $tradelist ['list'];

        foreach ($tradelist as $key => $row)
        {
            $tradelist [$key] ['is_buyer_rate'] = false;

            foreach ($row ['order'] as $orderListData)
            {
                if(isset($orderListData['gift_data']) && $orderListData['gift_data'])
                {
                    $tradelist[$key]['gift_count'] += array_sum(array_column($orderListData['gift_data'],'gift_num'));
                }

                if ($row ['buyer_rate'] == '0' && $row ['status'] == 'TRADE_FINISHED')
                {
                    $tradelist [$key] ['is_buyer_rate'] = true;
                    break;
                }
            }

            unset($tradelist [$key] ['order']);
            $tradelist [$key] ['order'] [0] = $row ['order'];

            if (! $tradelist [$key] ['is_buyer_rate'] && $postdata ['s'] == 4)
            {
                unset($tradelist [$key]);
            }
        }

        $pagedata ['trades'] = $tradelist;
        $pagedata ['count'] = $count;
        $pagedata ['status'] = $postdata ['s']; // 状态
        $pagedata['pagers'] = $this->__pages($postdata['pages'],$postdata,$count);
        $pagedata ['defaultImageId'] = kernel::single('image_data_image')->getImageSetting('item');
        return $pagedata;
    }

    /*
     * custom s szj
     * 拒绝电子合同
    */
    public function ajaxCancelEleContract(){
        $pagedata = input::get();
        $pagedata['signed_time'] = time();
        $objMdltrade = app::get('systrade')->model('trade');
        $objMdlorder = app::get('systrade')->model('order');
        $objMdlelecontract = app::get('systrade')->model('elecontract');
        try{
            $objMdltrade->update(array('status' =>'REJECT_CONTRACT'),array('tid' =>$pagedata['tid']));
            $objMdlorder->update(array('status' =>'REJECT_CONTRACT'),array('tid' =>$pagedata['tid']));
            $objMdlelecontract->update($pagedata,array('tid' =>$pagedata['tid']));
        }catch(Exception $e){
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg,true);
        }
        $url = url::action('topwap_ctl_member_trade@detail',array('tid'=>$pagedata['tid']));
//        kernel::single('sysuser_actionlog')->actionlog(array('memo'=>'确认电子合同['.$pagedata['tid'].']','action_id'=>$pagedata['tid']));
        $msg = app::get('topwap')->_('合同已拒绝，请等待卖家重新发起！');

        return $this->splash('success',$url,$msg,true);
    }

    /*
     * custom s szj
     * 确认电子合同
    */
    public function ajaxConfirmEleContract(){
        $pagedata = input::get();
        $pagedata['signed_time'] = time();

        $objMdltrade = app::get('systrade')->model('trade');
        $objMdlorder = app::get('systrade')->model('order');
        $objMdlelecontract = app::get('systrade')->model('elecontract');

        //获取订单支付类型
        $pay_type = $objMdltrade->getRow('pay_type',array('tid'=>$pagedata['tid']))['pay_type'];
        try{
            $objMdltrade->update(array('status' =>'WAIT_BUYER_PAY'),array('tid' =>$pagedata['tid']));
            $objMdlorder->update(array('status' =>'WAIT_BUYER_PAY'),array('tid' =>$pagedata['tid']));
            $objMdlelecontract->update($pagedata , array('tid' =>$pagedata['tid']));

            $msg = app::get('topwap')->_('合同已确认，请付款！');
            $url = url::action('topwap_ctl_member_trade@detail', array('tid' => $pagedata['tid']));
        }catch(Exception $e){
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg,true);
        }
        kernel::single('sysuser_actionlog')->actionlog(array('memo'=>'确认电子合同['.$pagedata['tid'].']','action_id'=>$pagedata['tid']));

        return $this->splash('success',$url,$msg,true);
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
}

