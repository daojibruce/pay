<?php

class systrade_api_trade_create {

    public $apiDescription = "订单创建";

    public $use_strict_filter = true; // 是否严格过滤参数

    public function getParams()
    {
        $return['params'] = array(
            'user_id'   => ['type'=>'int', 'valid'=>'required', 'example'=>'','desc'=>'会员id'],
            'user_name' => ['type'=>'int', 'valid'=>'required', 'example'=>'','desc'=>'会员用户名'],
            'mode'          => ['type'=>'string', 'valid'=>'required|in:cart,fastbuy', 'desc'=>'购物车类型', 'example'=>'cart'],
            'md5_cart_info' => ['type'=>'string', 'valid'=>'required', 'desc'=>'购物车数据校验'],
            'addr_id'       => ['type'=>'string', 'valid'=>'required|integer|min:1', 'desc'=>'收货地址', 'msg'=>'请选择收货地址'],
            'payment_type'  => ['type'=>'string', 'valid'=>'required|in:offline,online', 'desc'=>'支付方式', 'msg'=>'请选择支付方式'],
            'source_from'   => ['type'=>'string', 'valid'=>'in:pc,wap,app', 'desc'=>'使用平台 pc电脑端 wap手机端 app手机App端'],
            'shipping_type' => ['type'=>'jsonArray', 'valid'=>'required', 'example'=>'', 'desc'=>'每个店铺的配送方式 [{"shop_id":"3","type":"express"}]', 'params' => [
                'shop_id' => ['type'=>'string', 'valid'=>'', 'example'=>'', 'desc'=>'店铺ID'],
                'type'    => ['type'=>'string', 'valid'=>'required|in:express,ziti', 'example'=>'', 'desc'=>'配送方式 express快递配送，ziti自提配送'],
                'ziti_id' => ['type'=>'string', 'valid'=>'required_if:type,ziti|integer|min:1',    'example'=>'', 'desc'=>'自提ID', 'msg'=>'请选择自提地址'],
            ]],
            'mark' => ['type'=>'jsonArray', 'valid'=>'', 'desc'=>'买家留言', 'params'=>[
                'shop_id' => ['type'=>'string',  'valid'=>'', 'desc'=>'店铺ID'],
                'memo'    => ['type'=>'string',  'valid'=>'required', 'desc'=>'对应店铺买家留言'],
            ]],
            'invoice_type'    => ['type'=>'string', 'valid'=>'required:in,normal,vat,notuse','desc'=>'发票类型 normal普通发票，vat 增值税发票, notuse 不需要发票'],
            'invoice_content' => ['type'=>'jsonObject', 'valid'=>'required_if:invoice_type,normal,vat', 'desc'=>'发票数据内容', 'params'=>array(
                'title'                 => ['type'=>'string', 'valid'=>'required_if:invoice_type,normal|in:individual,unit', 'desc'=>'发票抬头类型 individual 个人,unit 企业'],
                'content'               => ['type'=>'string', 'valid'=>'required_if:invoice_type,normal', 'desc'=>'发票抬头'],
                'company_name'          => ['type'=>'string', 'valid'=>'required_if:invoice_type,vat', 'desc'=>'公司名称',     'msg'=>'请输入公司名称'],
                'company_address'       => ['type'=>'string', 'valid'=>'required_if:invoice_type,vat', 'desc'=>'公司地址',     'msg'=>'请输入公司地址'],
                'registration_number'   => ['type'=>'string', 'valid'=>'required_if:invoice_type,vat', 'desc'=>'纳税人识别号', 'msg'=>'请输入纳税人识别号'],
                'bankname'              => ['type'=>'string', 'valid'=>'required_if:invoice_type,vat', 'desc'=>'开户银行',     'msg'=>'请输入开户银行'],
                'bankaccount'           => ['type'=>'string', 'valid'=>'required_if:invoice_type,vat', 'desc'=>'开户银行帐号', 'msg'=>'请输入开户银行帐号'],
                'company_phone'         => ['type'=>'string', 'valid'=>'required_if:invoice_type,vat', 'desc'=>'公司电话',     'msg'=>'请输入公司电话'],
            )],
            'use_points' => ['type'=>'int', 'valid'=>'integer|min:0', 'default'=>'', 'desc'=>'使用的积分值'],
            //是否需要线上合同
            'need_econtract' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'是否需要电子合同'],
            //线下合同
            'contract_status' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'合同状态'],
            'contract_id' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'合同ID'],

            'offer_id' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'报价ID'],
        );
        return $return;
    }

    public function createTrade($params)
    {
        if(!defined('STRESS_TESTING') && $params['md5_cart_info'] != '1234567890')
        {
            $this->__checkCartInfo($params);
        }

        //收货地址
        if ($params['addr_id'] == '1234567890' && $params['offer_id']) {
            $offerInfo =app::get('sysproofing')->model('offer')->getRow('*', ['offer_id' => $params['offer_id']]);
            $sampleInfo = app::get('sysproofing')->model('sample')->getRow('*', ['sample_id' => $offerInfo['sample_id']]);
            $requirementInfo = app::get('sysproofing')->model('requirement')->getRow('*', ['requirement_id' => $sampleInfo['requirement_id']]);
            $addrInfo = explode('-',$requirementInfo['addr']);
            $delivery['buyer_area'] = '9999';
            $delivery['receiver_state'] = $addrInfo[0];
            $delivery['receiver_city'] = $addrInfo[1];
            $delivery['receiver_district'] = $addrInfo[2];
            $delivery['receiver_address'] = $requirementInfo['detail_addr'];
            $delivery['receiver_name'] = $requirementInfo['user_name'];
            $delivery['receiver_mobile'] = $requirementInfo['mobile'];
            $data['delivery'] = $delivery;
            $data['region_id'] = '9999';
            $data['shipping'] = ['9999' => ['shipping_type' => 'express']];
        } else {
            $data = $this->__preAddr($params['user_id'], $params['addr_id']);
            //配送类型
            $data = $this->__preShipping($params, $data);
        }

        $data['user_id']      = $params['user_id'];
        $data['user_name']    = $params['user_name'];
        $data['use_points']   = $params['use_points'];
        $data['payment_type'] = $params['payment_type'];
        $data['mode']         = $params['mode'];
        $data['source_from']  = $params['source_from'];

        //是否需要电子合同
        $params['need_econtract'] = intval($params['need_econtract']);
        $data['need_econtract'] = $params['need_econtract'] ? 1 : 0;
        //线下合同ID & 合同状态
        $data['contract_id'] = $params['contract_id'];
        $data['contract_status'] = $params['contract_status'];

        foreach( $params['mark'] as $markRow )
        {
            $data['trade_memo'][$markRow['shop_id']] = strip_tags($markRow['memo']);
        }

        $data = $this->__preInvoice($params, $data);

        if ($params['offer_id']) {
            $providerInfo = app::get('sysproofing')->model('provider')->getRow('*', ['provider_id' => $offerInfo['provider_id']]);
            $data['need_econtract'] = 1;
            $data['order_type'] = 'proofing';
            $data['offer_id'] = $params['offer_id'];
            $data['post_fee'] = $offerInfo['post_fee'];
            $data['total_fee'] = $offerInfo['total_fee'];
            $data['sample_fee'] = $offerInfo['sample_fee'];
            $data['shop_id'] = '999999'.$offerInfo['provider_id'];
            $cartInfo['resultCartData']['999999'.$offerInfo['provider_id']] = [
                'shop_id' => '999999'.$offerInfo['provider_id'],
                'shop_name' => $providerInfo['provider_name'],
                'shop_type' => 'proofing',
                'cartCount' => [
                    'total_weight' => '1',
                    'itemnum' => $sampleInfo['quantity'],
                    'total_fee' => $offerInfo['sample_fee'],
                    'total_discount' => 0,
                    'total_coupon_discount' => 0,
                ],
                'usedCartPromotionWeight' => 0,
                'object' => [
                    0 => [
                        'obj_type' => 'proofing',
                        'user_id' => $params['user_id'],
                        'item_id' => '99999999'.$offerInfo['sample_id'],
                        'sku_id' => '99999999'.$offerInfo['sample_id'],
                        'cat_id' => '9999',
                        'bn' => microtime(true).$offerInfo['sample_id'],
                        'store' => $sampleInfo['quantity'],
                        'status' => 'onsale',
                        'price' => [
                            'discount_price' => 0,
                            'price' => round($offerInfo['sample_fee']/$sampleInfo['quantity'],2),
                            'total_price' => $offerInfo['sample_fee'],
                        ],
                        'quantity' => $sampleInfo['quantity'],
                        'title' => $sampleInfo['sample_name'],
                        'image_default_id' => '',
                        'weight' => '1',
                        'valid' => 1,
                        'is_checked' => 1,
                    ],
                ],
            ];
            $cartInfo['totalCart'] = [
                'totalWeight' => '1',
                'number' => $sampleInfo['quantity'],
                'totalPrice' => $offerInfo['sample_fee'],
                'totalAfterDiscount' => $offerInfo['sample_fee'],
                'totalDiscount' => 0,
            ];
        } else {
            //获取购物车数据
            $cartFilter = array(
                'mode' => $params['mode'],
                'needInvalid' => false,
                'platform' => $params['source_from'],
                'user_id' => $params['user_id'],
            );
            /**
             * 重构获取购物车下单逻辑
             *
             * @author  Bruce Lee
             */
            //$cartInfo = app::get('systrade')->rpcCall('trade.cart.getCartInfo', $cartFilter);
            $cartInfo = kernel::single('systrade_data_cart', $params['user_id'])->getCartInfo2($cartFilter);
        }
        //echo "<pre>";print_r($data);print_r($cartInfo);exit;
        $objOrderCreate = kernel::single("systrade_data_trade_create", $params['user_id']);
        $tids = $objOrderCreate->generate($data, $cartInfo);

        return $tids;
    }

    /**
     * 处理发票信息数据结构 function
     *
     * @return void
     */
    private function __preInvoice($params, $data)
    {
        if( $params['invoice_type'] == 'notuse')
        {
            $data['invoice']['need_invoice'] = 0;
        }
        else
        {
            $data['invoice']['need_invoice'] = 1;
            $data['invoice']['invoice_type'] = $params['invoice_type'];
            if( $params['invoice_type'] == 'normal')
            {
                $data['invoice']['invoice_title']   = $params['invoice_content']['title'];
                $data['invoice']['invoice_content'] = strip_tags($params['invoice_content']['content']);
            }
            else
            {
                $data['invoice']['invoice_vat'] = $params['invoice_content'];
            }
        }

        return $data;
    }


    /**
     * 检查收货地址是否合法
     */
    private function __preAddr($userId, $addrId)
    {
        $addr = app::get('systrade')->rpcCall('user.address.info',array('addr_id'=>$addrId,'user_id'=>$userId));
        if( !$addr )
        {
            throw new Exception('收货地址信息有误');
        }

        list($regions,$regionId) = explode(':',$addr['area']);
        list($state,$city,$district) = explode('/',$regions);
        $data['delivery'] = array(
            'buyer_area'        => $regionId,
            'addr_id'           => $addrId,
            'receiver_state'    => $state,
            'receiver_city'     => $city,
            'receiver_district' => $district,
            'receiver_address'  => $addr['addr'],
            'receiver_zip'      => $addr['zip'],
            'receiver_name'     => $addr['name'],
            'receiver_mobile'   => $addr['mobile'],
            'receiver_phone'    => $addr['tel'],
        );
        $data['region_id'] = str_replace('/', ',', $regionId);

        return $data;
    }

    private function __preShipping($params, $data)
    {
        $shopIds = array_column($params['shipping_type'],'shop_id');
        $shopIds = array_unique($shopIds);
        $shopData = app::get('topapi')->rpcCall('shop.get.list', ['shop_id'=>implode(',',$shopIds),'fields'=>'shop_id,shop_type']);
        $shopData = array_bind_key($shopData,'shop_id');

        $zitiIds = array_column($params['shipping_type'],'ziti_id');
        if( $zitiIds )
        {
            $zitiIds = array_unique($zitiIds);
            $zitiData = app::get('topapi')->rpcCall('logistics.ziti.list.get', ['id'=>implode(',',$zitiIds)]);
            $zitiData = array_bind_key($zitiData,'id');
        }

        $ifOpenZiti = app::get('syslogistics')->getConf('syslogistics.ziti.open');
        $ifOpenOffline = app::get('ectools')->getConf('ectools.payment.offline.open');

        foreach( $params['shipping_type'] as $row)
        {
            $shopId = $row['shop_id'];

            //判断是否支持货到付款支付方式
            if( $params['payment_type'] == 'offline' )
            {
                if(($shopData[$shopId]['shop_type'] != "self") || ($shopData[$shopId]['shop_type'] == "self" && $ifOpenOffline == "false"))
                {
                    $msg = app::get('topapi')->_($shopData[$shopId]['shopname'].'不支持货到付款');
                    throw new LogicException($msg);
                }
            }

            if($row['type'] == 'ziti')
            {
                //验证是否有自提资格
                if($shopData[$shopId]['shop_type'] != 'self' || $ifOpenZiti == 'false' )
                {
                    $msg = app::get('topapi')->_($shopData[$shopId]['shopname'].'不支持自提');
                    throw new LogicException($msg);
                }

                if( !$zitiData[$row['ziti_id']] )
                {
                    throw new LogicException('配送自提参数错误');
                }

                $areaIds = explode(',',$data['region_id']);
                $checkAreaIds =  count($areaIds) == 2 ? $zitiData[$row['ziti_id']]['area_city_id'] : $zitiData[$row['ziti_id']]['area_state_id'];
                if( $checkAreaIds != $areaIds[0] )
                {
                    $msg = app::get('systrade')->_("请重新选择自提地址");
                    throw new LogicException($msg);
                }

                $data['ziti'][$shopId]['ziti_addr'] = $zitiData[$row['ziti_id']]['area'].$zitiData[$row['ziti_id']]['addr'];
            }

            $data['shipping'][$shopId]['shipping_type'] = $row['type'];
        }

        return $data;
    }

    private function __checkCartInfo($params)
    {
        $md5CartFilter = array(
            'user_id'=>$params['user_id'],
            'platform'=>$params['source_from'],
            'mode'=>$params['mode'],
            'checked'=>1
        );

        $cartInfo = app::get('topapi')->rpcCall('trade.cart.getBasicCartInfo', $md5CartFilter);

        // 校验购物车是否为空
        if( !$cartInfo )
        {
            $msg = app::get('topapi')->_("购物车信息为空或者未选择商品");
            throw new \LogicException($msg);
        }

        // 校验购物车是否发生变化
        $md5CartInfo = md5(serialize(utils::array_ksort_recursive($cartInfo, SORT_STRING)));
        if( $params['md5_cart_info'] != $md5CartInfo )
        {
            $msg = app::get('topapi')->_("购物车数据发生变化，请刷新后确认提交");
            throw new \LogicException($msg);
        }

        unset($params['md5_cart_info']);

        return true;
    }
}

