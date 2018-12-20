<?php
class systrade_api_trade_payFinish {

    public $apiDescription = "订单支付状态改变";

    public function getParams()
    {
        $return['params'] = array(
            'tid' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'订单id'],
            'payment' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'已支付金额'],
        );
        return $return;
    }

    public function tradePay($params)
    {
        $tid = $params['tid'];
        $objTrade = kernel::single('systrade_data_trade');
        $tradeInfo = $objTrade->getTradeInfo('*',['tid'=>$tid]);
        /*if( $tradeInfo['status'] != 'WAIT_BUYER_PAY'  || $tradeInfo['pay_type'] != 'offline'){
            logger::info("支付已成功的订单，不需要重复支付");
            return true;
        }*/
        //获取电子合同信息
        $elecontractMdel = app::get('systrade')->Model('elecontract');
        $elecontracArr=$elecontractMdel->getRow('tid,is_part_pay,pay_type',array('tid'=>$tid));
        $payNode=empty($elecontracArr['pay_type']) ? array(): $elecontracArr['pay_type'];
        $payNum=$this->parseNum($payNode);
        $numfee=array($payNode['num1'],$payNode['num2'],$payNode['num3'],$payNode['num4'],$payNode['num5']);
        $position=$tradeInfo['position']+1;
        //确认最终已付金额
        switch ($elecontracArr['is_part_pay']){
            case '0':
                $payment = $tradeInfo['payment'];
                break;
            case '1':
                $payment=0;
                for ($i=0;$i<=$tradeInfo['position'];$i++){
                    $payment += $numfee[$i];
                }
                break;
        }

        $objMdlOrder = app::get('systrade')->model('order');
        $objMdlTrade = app::get('systrade')->model('trade');
        $orderRes=$objMdlOrder->getList('oid,sendnum',array('tid'=>$tid));
        $numArr=array();
        foreach ($orderRes as $k=>$v){
            $numArr[$k]=$v['sendnum'];
        }
        $sendnum = array_sum($numArr);
        $tradeRes=$objMdlTrade->getRow('status,pay_type,itemnum',array('tid'=>$tid));


        //主订单状态、子订单状态
        if(empty($elecontracArr)){
            $elecontracArr['is_part_pay'] = 0;
        }
        switch ($elecontracArr['is_part_pay']){
            case '0':
                try{
                    //修改主订单状态
                    switch($tradeInfo['pay_type']){
                        case 'offline':
                            if ($sendnum < $tradeRes['itemnum']){
                                $tradeData['data']['status']='WAIT_SELLER_SEND_GOODS';
                                $orders = array(
                                    'status'=>'WAIT_SELLER_SEND_GOODS',
                                    'pay_time'=> time(),
                                );
                            }else{
                                $tradeData['data']['status']='WAIT_SELLER_CONFIRM_GOODS';
                                $orders = array(
                                    'status'=>'WAIT_SELLER_CONFIRM_GOODS',
                                    'pay_time'=> time(),
                                );
                            }
                            break;
                        default:
                            $tradeData['data']['status']='WAIT_SELLER_SEND_GOODS';
                            $orders = array(
                                'status'=>'WAIT_SELLER_SEND_GOODS',
                                'pay_time'=> time(),
                            );
                            break;
                    }

                    $tradeData['data']['modified_time']=time();
                    $tradeData['data']['pay_time']=time();
                    $tradeData['data']['payed_fee'] = $payment;
                    $tradeData['data']['position']=$position;
                    $tradeData['filter']['tid'] = $tid;
                
                    logger::info("支付成功，更新主订单".var_export($tradeData,1));
                    $result = $objTrade->updateTrade($tradeData);
                    if(!$result){
                        throw new \LogicException("主订单支付状态更新失败");
                    }
                    error_log("=-=-=-=-1111111=-=-=-=-\n".print_r($tradeData,1),3,DATA_DIR.'/fail.log');
                    
                    //修改子订单状态
                    $objMdlOrder = app::get('systrade')->model('order');

                    logger::info("支付成功，更新子订单".var_export($orders,1));
                    if(!$objMdlOrder->update($orders, array('tid'=>$tid) ) ){
                        $msg = "子订单支付状态修改失败";
                        throw new \LogicException($msg);
                    }
                    $this->addLog($tid,$params);
                }catch(\Exception $e){
                    throw $e;
                }
                break;
            case '1':
                if ($tradeInfo['position']==0){//第一次付款
                    try{
                        //修改主订单状态
                        if($tradeInfo['pay_type'] == "offline"){
                            $tradeData['data']['status']='WAIT_BUYER_PAY';
                            $orders = array(
                                'status'=>'WAIT_BUYER_PAY',
                                'pay_time'=> time(),
                            );
                        }else{
                            $tradeData['data']['status']='WAIT_BUYER_PAY';
                            $orders = array(
                                'status'=>'WAIT_BUYER_PAY',
                                'pay_time'=> time(),
                            );
                        }
                        $tradeData['data']['modified_time']=time();
                        $tradeData['data']['pay_time']=time();
                        $tradeData['data']['payed_fee'] = $payment;
                        $tradeData['data']['position']=$position;
                        $tradeData['filter']['tid'] = $tid;
                    
                        logger::info("支付成功，更新主订单".var_export($tradeData,1));
                        $result = $objTrade->updateTrade($tradeData);
                        if(!$result){
                            throw new \LogicException("主订单支付状态更新失败");
                        }
                        
                        //修改子订单状态
                        $objMdlOrder = app::get('systrade')->model('order');

                        logger::info("支付成功，更新子订单".var_export($orders,1));
                        if(!$objMdlOrder->update($orders, array('tid'=>$tid) ) ){
                            $msg = "子订单支付状态修改失败";
                            throw new \LogicException($msg);
                        }
                        $this->addLog($tid,$params);
                    }catch(\Exception $e){
                        throw $e;
                    }
                }else if (($tradeInfo['position']+1) == $payNum){//最后一次付款，结算
                    if($tradeInfo['pay_type'] == "offline"){
                        //看卖家是否将货物发完，发完=WAIT_SELLER_CONFIRM_GOODS  没发完 == WAIT_SELLER_SEND_GOODS
                        if ($sendnum < $tradeRes['itemnum']){
                            $paramArr = array(
                                'filter' => array(
                                    'tid' => $tid,
                                ),
                                'data' => array(
                                    'status' => 'WAIT_SELLER_SEND_GOODS',
                                    'modified_time' => time(),
                                    'end_time' => time(),
                                    'position'=>$position,
                                    'payed_fee'=>$payment,
                                    'pay_time'=>time()
                                ),
                            );
                            $order_status = "WAIT_SELLER_SEND_GOODS";
                        }else{
                            $paramArr = array(
                                'filter' => array(
                                    'tid' => $tid,
                                ),
                                'data' => array(
                                    'status' => 'WAIT_SELLER_CONFIRM_GOODS',
                                    'modified_time' => time(),
                                    'end_time' => time(),
                                    'position'=>$position,
                                    'payed_fee'=>$payment,
                                    'pay_time'=>time()
                                ),
                            );
                            $order_status = "WAIT_SELLER_CONFIRM_GOODS";
                        }
                    }else{
                        if ($sendnum < $tradeRes['itemnum']){
                            $paramArr = array(
                                'filter' => array(
                                    'tid' => $tid,
                                ),
                                'data' => array(
                                    'status' => 'WAIT_SELLER_SEND_GOODS',
                                    'modified_time' => time(),
                                    'end_time' => time(),
                                    'position'=>$position,
                                    'payed_fee'=>$payment,
                                    'pay_time'=>time()
                                ),
                            );
                            $order_status = "WAIT_SELLER_SEND_GOODS";
                        }else{
                            $paramArr = array(
                                'filter' => array(
                                    'tid' => $tid,
                                ),
                                'data' => array(
                                    'status' => 'WAIT_BUYER_CONFIRM_GOODS',
                                    'modified_time' => time(),
                                    'end_time' => time(),
                                    'position'=>$position,
                                    'payed_fee'=>$payment,
                                    'pay_time'=>time()
                                ),
                            );
                            $order_status = "WAIT_BUYER_CONFIRM_GOODS";
                        }
                    }


                    //生成结算点明细
                    $db = app::get('systrade')->database();
                    $db->beginTransaction();
                    try {
                        //生成结算点明细
                        $isClearing = app::get('systrade')->rpcCall('clearing.detail.add',['tid'=>$tradeInfo['tid']]);
                        if($isClearing){
                            $params['data']['is_clearing'] = 1;
                        }
                        //更新主订单状态
                        if(!$objTrade->updateTrade($paramArr)){
                            throw new \LogicException("订单完成失败，更新数据库失败");
                        }
                        //更新子订单状态
                        $objMdlOrder = app::get('systrade')->model('order');
                        if(!$objMdlOrder->update( array('status'=>$order_status,'pay_time'=> time(),'end_time'=>time()), array('tid'=>$tradeInfo['tid']))){
                            throw new \LogicException("订单的子订单完成失败，更新数据库失败");
                        }
                        
                        // 修改商品销量
                        if($this->updateSoldQuantity($tradeInfo)){
                            $msg = "确认订单失败[销量统计失败]";
                            throw new \LogicException($msg = "确认订单失败[销量统计失败]");
                        }
                        //积分确认
                        if(!$this->confirmPoint($tradeInfo)){
                            throw new \LogicException("确认订单失败[会员积分结算失败]");
                        }
                        //经验值确认
                        if(!$this->confirmExperience($tradeInfo)){
                            throw new \LogicException("确认订单失败[会员经验值结算失败]");
                        }
                        $this->addLog($tid,$params);
                        $db->commit();
                    }catch (\Exception $e){
                        $db->rollback();
                        throw $e;
                    }
                }else{
                    try{
                        //修改主订单状态
                        $tradeData['data']['status']='WAIT_BUYER_PAY';
                        $tradeData['data']['modified_time']=time();
                        $tradeData['data']['pay_time']=time();
                        $tradeData['data']['payed_fee'] = $payment;
                        $tradeData['data']['position']=$position;
                        $tradeData['filter']['tid'] = $tid;
                    
                        logger::info("支付成功，更新主订单".var_export($tradeData,1));
                        $result = $objTrade->updateTrade($tradeData);
                        if(!$result){
                            throw new \LogicException("主订单支付状态更新失败");
                        }
                    
                        //修改子订单状态
                        $objMdlOrder = app::get('systrade')->model('order');
                        if($tradeInfo['pay_type'] == "offline"){
                            $orders = array(
                                'status'=>'WAIT_BUYER_CONFIRM_GOODS',
                                'pay_time'=> time(),
                            );
                        }else{
                            $orders = array(
                                'status'=>'WAIT_BUYER_PAY',
                                'pay_time'=> time(),
                            );
                        }

                        logger::info("支付成功，更新子订单".var_export($orders,1));
                        if(!$objMdlOrder->update($orders, array('tid'=>$tid) ) ){
                            $msg = "子订单支付状态修改失败";
                            throw new \LogicException($msg);
                        }
                        $this->addLog($tid,$params);
                    }catch(\Exception $e){
                        throw $e;
                    }
                }
                break;
        }
        return true;
    }

    private function __minusStore($orderData)
    {
        // 处理sku订单冻结
        $params = array(
            'item_id' => $orderData['item_id'],
            'sku_id' => $orderData['sku_id'],
            'quantity' => $orderData['num'],
            'sub_stock' => $orderData['sub_stock'],
            'status' => 'afterpay',
        );
        $isMinus = app::get('systrade')->rpcCall('item.store.minus',$params);
        if( ! $isMinus )
        {
            throw new \LogicException(app::get('systrade')->_('冻结库存失败'));
        }

        if(isset($orderData['gift_data']) && $orderData['gift_data'])
        {
            foreach($orderData['gift_data'] as $key=>$value)
            {
                $params = array(
                    'item_id' => $value['item_id'],
                    'sku_id' => $value['sku_id'],
                    'quantity' => $value['gift_num'],
                    'sub_stock' => $value['sub_stock'],
                    'status' => 'afterpay',
                );
                $isMinus = app::get('systrade')->rpcCall('item.store.minus',$params);
                if( ! $isMinus )
                {
                    throw new \LogicException(app::get('systrade')->_('冻结赠品库存失败'));
                }
            }
        }
        return true;

    }

    /**
     * 调用修改商品销量接口
     * @param  array &$tradeInfo 订单信息
     * @return
     */
    private function updateSoldQuantity(&$tradeInfo){
        if($tradeInfo && is_array($tradeInfo['order'])){
            foreach($tradeInfo['order'] as $key => $val){
                $apiData = array('item_id'=>$val['item_id'], 'num'=>$val['num']);
                if(!app::get('systrade')->rpcCall('item.updateSoldQuantity', $apiData)){
                    return false;
                }
            }
        }else{
            return false;
        }
    }

    /**
     * @brief 确认会员积分
     * @param $tradeInfo
     * @return
     */
    private function confirmPoint(&$tradeInfo){
        if(!$tradeInfo) return false;
        $params['user_id'] = $tradeInfo['user_id'];
        $params['type'] = "obtain";
        $params['behavior'] = "购物获得积分";
        $params['remark'] = "当前积分来自订单：".$tradeInfo['tid'];
        if($tradeInfo['consume_point_fee']){
            $params['type'] = "consume";
            $params['behavior'] = "购物消费积分";
            $params['remark'] = "当前积分由订单：".$tradeInfo['tid']."消费";
        }
        $params['num'] = $tradeInfo['obtain_point_fee'];
        $result = app::get('systrade')->rpcCall('user.updateUserPoint',$params);
        if(!$result) return false;
        return true;
    }

    /**
     * @brief 确认会员经验值
     * @param $tradeInfo
     * @return
     */
    private function confirmExperience(&$tradeInfo){
        if(!$tradeInfo) return false;
        $params['user_id'] = $tradeInfo['user_id'];
        $params['type'] = "obtain";
        $params['num'] = $tradeInfo['payment']-$tradeInfo['post_fee'];
        $params['behavior'] = "购物获得经验值";
        $params['remark'] = "当前经验值来自订单：".$tradeInfo['tid'];
        $result = app::get('systrade')->rpcCall('user.updateUserExp',$params);
        if(!$result) return false;
        return true;
    }

    /**
     * 记录订单取消日志
     * @param int &$canCancelTid 订单数据[操作者信息]
     * @param array &$params       成功标识
     */
    private function addLog($tid ){
        $objLibLog = kernel::single('systrade_data_trade_log');
        $logText = '订单付款成功！';
        $sdfTradeLog = array(
            'rel_id'   => $tid,
            'op_id'    => 0,
            'op_name'  => '系统',
            'op_role'  => 'system',
            'behavior' => 'payment',
            'log_text' => $logText,
        );
        if(!$objLibLog->addLog($sdfTradeLog)){
            $msg = "log记录失败";
            throw new \LogicException($msg);
            return false;
        }
        return true;
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
}


