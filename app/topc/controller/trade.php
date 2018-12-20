<?php
class topc_ctl_trade extends topc_controller{

    var $noCache = true;

    public function __construct(&$app)
    {
        parent::__construct();
        theme::setNoindex();
        theme::setNoarchive();
        theme::setNofolow();
        theme::prependHeaders('<meta name="robots" content="noindex,noarchive,nofollow" />\n');
        $this->title=app::get('topc')->_('订单中心');
        // 检测是否登录
        if( !userAuth::check() )
        {
            redirect::action('topc_ctl_passport@signin')->send();exit;
        }
    }

    public function create()
    {
        $postData                = input::get();
        $postData['mode']        = $postData['mode'] ? $postData['mode'] :'cart';
        $postData['source_from'] = 'pc';

        $postData['user_id']   = userAuth::id();
        $postData['user_name'] = userAuth::getLoginName();

        //配送方式
        $shopIdList = [];
        foreach( $postData['shipping'] as $shopId => $shipping )
        {
            array_push($shopIdList , $shopId);
            $postData['shipping_type'][] = [
                'shop_id' => $shopId,
                'type'    => $shipping['shipping_type'],
                'ziti_id' => ($shipping['shipping_type'] == 'ziti') ? $postData['ziti'][$shopId]['ziti_id'] : null,
            ];
        }
        unset($postData['shipping']);
        $postData['shipping_type'] = json_encode($postData['shipping_type']);

        //订单备注
        $markData = $postData['mark'];
        unset($postData['mark']);
        if( $markData )
        {
            foreach( $markData as $shopId=>$mark )
            {
                if( $mark )
                {
                    $postData['mark'][] = [
                        'shop_id' =>$shopId,
                        'memo' =>$mark,
                    ];
                }
            }
            $postData['mark'] = json_encode($postData['mark']);
        }

        //处理合同数据,合同数据入库
        $this->insertContractTb($shopIdList , $postData);

        //发票信息处理
        $postData['invoice_type']    = !$postData['invoice']['need_invoice'] ? 'notuse' : $postData['invoice']['invoice_type'];
        if( $postData['invoice_type'] == 'normal' )
        {
            $postData['invoice_content']['title'] = $postData['invoice']['invoice_title'];
            $postData['invoice_content']['content'] = $postData['invoice']['invoice_content'];
        }
        elseif( $postData['invoice_type'] == 'vat' )
        {
            $postData['invoice_content'] = $postData['invoice']['invoice_vat'];
        }
        $postData['invoice_content'] = json_encode($postData['invoice_content']);
        unset($postData['invoice']);

        try
        {
           $createFlag = app::get('topc')->rpcCall('trade.create',$postData, 'buyer');
           #等同于： $createFlag2 = kernel::single('systrade_api_trade_create')->createTrade($postData);
           if( $createFlag )
           {
               $countData = app::get('topc')->rpcCall('trade.cart.getCount', ['user_id' => userAuth::id()], 'buyer');
               userAuth::syncCookieWithCartNumber($countData['number']);
               userAuth::syncCookieWithCartVariety($countData['variety']);
           }
        }
        catch(Exception $e)
        {
            return $this->splash('error',null,$e->getMessage(),true);
        }

        $this->insertElectronicsContractTb($shopIdList , $createFlag , $postData);

        try
        {
            if($postData['payment_type'] == "online")
            {
                $params['tid'] = $createFlag;
                $params['user_id'] = userAuth::id();
                $params['user_name'] = userAuth::getLoginName();
                $paymentId = kernel::single('topc_payment')->getPaymentId($params);
                $redirect_url = url::action('topc_ctl_paycenter@index',array('payment_id'=>$paymentId,'merge'=>true));
            }
            else
            {
                $redirect_url = url::action('topc_ctl_paycenter@index',array('tid' => implode(',',$createFlag)));
            }
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            $url = url::action('topc_ctl_member_trade@tradeList');
            return $this->splash('success',$url,$msg,true);
        }
        return $this->splash('success',$redirect_url,'订单创建成功',true);
    }

    /**
     * 处理合同数据,合同数据入库
     * $postData : 为表单post过来的数据
     * */
    private function insertContractTb($shopIdList , & $postData)
    {
        //线下合同信息
        $need_contract = intval($postData['need_contract']);
        if(! $need_contract){
            $postData['contract_status'] = 0;
            return false;
        }

        //合同状态
        $postData['contract_status'] = 1;
        //将合同数据存入合同表
        $contractMdl = app::get('systrade')->model('contract');
        foreach ($shopIdList as $shopId) {
            $filter = Array(
                'user_id' => $postData['user_id'],//用户ID
                'contacts' => $postData['contract'],
                'phone' => $postData['phone'],
                'shop_id'=>$shopId,
            );
            try {
                //将合同数据存入合同表,返回合同表ID
                $postData['contract_id'][$shopId] = $contractMdl->insert($filter);

            } catch (Exception $e) {
                return $this->splash('error', null, $e->getMessage(), true);
            }
        }

        return true;
    }

    /**
     * 处理合同数据,合同数据入库
     * $postData : 为表单post过来的数据
     * */
    private function insertElectronicsContractTb($shopIdList , $createFlag , & $postData)
    {
        $postData['need_econtract'] = intval($postData['need_econtract']);
        $need_econtract = $postData['need_econtract'];

        $objMdlelecontract = app::get('systrade')->model('elecontract');
        foreach ($shopIdList as $shopId) {
            $elecontract = [];
            try {
                $elecontractItem = $postData['elecontract'];
                $elecontract['user_id'] = $postData['user_id'];
                $elecontract['shop_id'] = $shopId;
                $elecontract['user_name'] = $need_econtract ? trim($elecontractItem['name']) : '';
                $elecontract['phone'] = $need_econtract ? trim($elecontractItem['tel']) : '';
                $elecontract['createtime'] = time();

                foreach($createFlag as $value){
                    $elecontract['tid'] = $value;
                    $objMdlelecontract->insert($elecontract);
                }
            } catch (Exception $e) {
                return $this->splash('error', null, $e->getMessage(), true);
            }
        }

        return true;
    }

    /*服务撮合订单创建*/
    public function proofingCreate()
    {
        $postData                = input::get();
        $postData['source_from'] = 'pc';

        $postData['user_id']   = userAuth::id();
        $postData['user_name'] = userAuth::getLoginName();
        $postData['payment_type'] = 'online';

        $postData['md5_cart_info'] = '1234567890';
        $postData['mode']        = 'fastbuy';
        $postData['addr_id']        = '1234567890';
        $postData['shipping_type']        = ['shop_id' => '666666','type' => 'express'];
        $postData['invoice_type']        = 'notuse';

        //检查该样品是否已经下单
        $offerInfo = app::get('sysproofing')->model('offer')->getRow('*', ['offer_id' => $postData['offer_id']]);
        $info = app::get('systrade')->model('order')->getRow('oid',['item_id' => '99999999'.$offerInfo['sample_id']]);
        if ($info) {
            return redirect::action('topc_ctl_member_trade@myRequirementOrder');
        }

        try
        {
            $createFlag = app::get('topc')->rpcCall('trade.create',$postData);
            #等同于： $createFlag2 = kernel::single('systrade_api_trade_create')->createTrade($postData);
            if( $createFlag )
            {
                //插入电子合同数据
                $objMdlelecontract = app::get('systrade')->model('elecontract');
                $providerInfo = app::get('sysproofing')->model('provider')->getRow('*', ['provider_id' => $offerInfo['provider_id']]);
                $elecontract['user_id'] = $postData['user_id'];
                $elecontract['shop_id'] = '999999'.$offerInfo['provider_id'];
                $elecontract['user_name'] = $providerInfo['provider_name'];
                $elecontract['phone'] = $providerInfo['mobile'];
                $elecontract['createtime'] = time();
                $elecontract['tid'] = $createFlag[0];
                $objMdlelecontract->insert($elecontract);
                /*$countData = app::get('topc')->rpcCall('trade.cart.getCount', ['user_id' => userAuth::id()], 'buyer');
                userAuth::syncCookieWithCartNumber($countData['number']);
                userAuth::syncCookieWithCartVariety($countData['variety']);*/
            }
        }
        catch(Exception $e)
        {
            //throw new LogicException('发货失败，发货单有误');
            return redirect::action('topc_ctl_member_trade@myRequirementOrder');
        }

        try
        {
            if($postData['payment_type'] == "online")
            {
                $params['tid'] = $createFlag;
                $params['user_id'] = userAuth::id();
                $params['user_name'] = userAuth::getLoginName();
                $paymentId = kernel::single('topc_payment')->getPaymentId($params);
                return  redirect::action('topc_ctl_paycenter@index',array('payment_id'=>$paymentId,'merge'=>true));
            }
            else
            {
                return  redirect::action('topc_ctl_paycenter@index',array('tid' => implode(',',$createFlag)));
            }
        }
        catch(Exception $e)
        {
            //$msg = $e->getMessage();
            //$url = url::action('topc_ctl_member_trade@tradeList');
            return redirect::action('topc_ctl_member_trade@myRequirementOrder');
        }

    }
}


