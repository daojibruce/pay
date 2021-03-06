<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class toptemai_ctl_index extends toptemai_controller {

	public function index()
	{
        $userId = $this->userId;
        $shopId = $this->getShopId();
        return $this->page('toptemai/index.html' , [$shopId,$userId]);

		//获取店铺数据
        $params = array(
            'user_id' => $userId,
            'fields' =>'shop_id,shop_name,close_reason,close_time,shop_type,open_time,shop_logo,shop_descript,status,brand.brand_name,cat.cat_id,cat.cat_name',
        );

		$shopInfo = app::get('toptemai')->rpcCall('shop.get.detail',$params,'seller');
        //获取商家入驻信息以及入驻佣金  类目
        if($shopInfo['shop']['shop_type']!='self')
        {
        	$shopCatInfo = app::get('toptemai')->rpcCall('shop.get.cat.fee',array('shop_id'=>$shopId),'seller');
			$pagedata['shopCatInfo'] = $shopCatInfo;
        }

		//业务提想没有付款的订单数量
        $countUnTradeFee = app::get('toptemai')->rpcCall('trade.count',array('shop_id'=>$shopId,'status'=>'WAIT_BUYER_PAY'));
		//业务提想等代发货的订单数量
        $countReadysSend = app::get('toptemai')->rpcCall('trade.count',array('shop_id'=>$shopId,'status'=>'WAIT_SELLER_SEND_GOODS'));
		//业务提想等代收货的的订单数量
        $countReadyRec = app::get('toptemai')->rpcCall('trade.count',array('shop_id'=>$shopId,'status'=>'WAIT_BUYER_CONFIRM_GOODS'));

		//获取店铺上架商品数量
        $countShopOnsaleItem = app::get('toptemai')->rpcCall('item.count',array('shop_id'=>$shopId,'status'=>'onsale'));
		//获取店铺下架商品数量
        $countShopInstockItem = app::get('toptemai')->rpcCall('item.count',array('shop_id'=>$shopId,'status'=>'instock'));
        //获取店审核失败商品数量
        $countShopRefuseItem = app::get('toptemai')->rpcCall('item.count',array('shop_id'=>$shopId,'status'=>'refuse'));

		//昨日数据
		$yesterdayInfo = $this->getAverPrice('yesterday');
		//前日数据
		$beforInfo = $this->getAverPrice('beforday');
		//本周数据
		$weekInfo = $this->getAverPrice('week');
		//本月数据
		$monthInfo = $this->getAverPrice('month');

		//获取当前登录用户信息
		$sellData = shopAuth::getSellerData();
		//判断是否显示验证提醒
		$authNotice = false;
		if($sellData['seller_type'] == 0 && $sellData['auth_type'] == 'UNAUTH')
		{
		    $authNotice = true;
		    if(isset($_COOKIE['authNotice']) && $_COOKIE['authNotice'] === 'n')
		    {
		        $authNotice = false;
		    }
		}

        //库存报警
        $pagedata['storepolice'] = 0;
        $params['shop_id'] = $shopId;
        $params['fields'] = 'policevalue';
        $storePolice = app::get('toptemai')->rpcCall('item.store.info',$params);
        if($storePolice['policevalue'])
        {
            $filter['store'] = $storePolice['policevalue'];
            $filter['shop_id'] = $shopId;
            //echo '<pre>';print_r($filter);exit();
            $storepolice = app::get('toptemai')->rpcCall('item.store.police.count',$filter);
            $pagedata['storepolice'] = $storepolice;
        }

		$pagedata['countShopOnsaleItem'] = $countShopOnsaleItem;
		$pagedata['countShopInstockItem'] = $countShopInstockItem;
                $pagedata['countShopRefuseItem'] = $countShopRefuseItem;
		$pagedata['countUnTradeFee'] = $countUnTradeFee;
		$pagedata['countReadysSend'] = $countReadysSend;
		$pagedata['countReadyRec'] = $countReadyRec;
		$pagedata['shop'] = $shopInfo['shop'];
		$pagedata['shopBrandInfo'] = $shopInfo['brand'];
		$pagedata['yesterday'] = $yesterdayInfo;
		$pagedata['beforInfo'] = $beforInfo;
		$pagedata['weekInfo'] = $weekInfo;
		$pagedata['monthInfo'] = $monthInfo;
		$pagedata['authnotice'] = $authNotice;
		$url = url::action("toptemai_ctl_index@index",array('shop_id'=>$shopId));
		$pagedata['qrCodeData'] = getQrcodeUri($url, 80, 0);
                $pagedata['examineSetting'] = app::get('sysconf')->getConf('shop.goods.examine');
		$this->contentHeaderTitle = app::get('toptemai')->_('我的工作台');
		return $this->page('toptemai/index.html', $pagedata);
	}

	/**
	 * 获取平均客单价
	 * @param data
	 * @return data
	 */
    public function getAverPrice($data)
    {
        switch($data)
        {
        case "yesterday":
            $stattime = strtotime(date("Y-m-d", time()-86400) . ' 00:00:00');
            $filterType = "nequal";
            break;
        case "beforday":
            $stattime = strtotime(date("Y-m-d", time()-86400*2) . ' 00:00:00');
            $filterType = "nequal";
            break;
        case "week":
            $stattime = strtotime(date("Y-m-d", time()-86400*7) . ' 00:00:00');
            $filterType = "bthan";
            break;
        case "month":
            $stattime = strtotime(date("Y-m-d", time()-86400*30) . ' 00:00:00');
            $filterType = "bthan";
            break;
        }
        $filter = array(
            'shop_id' => $this->shopId,
            'type' => $filterType,
            'createtime' => $stattime,
        );
        $getData = app::get('toptemai')->rpcCall('stat.trade.data.count.get',$filter);

		$data = array();
        foreach ($getData as $key => $value)
        {
			$data['shop_id'] =$value['shop_id'];
			$data['new_trade'] +=$value['new_trade'];
			$data['new_fee'] +=$value['new_fee'];
			$data['ready_trade'] +=$value['ready_trade'];
			$data['ready_fee'] +=$value['ready_fee'];
			$data['ready_send_trade'] +=$value['ready_send_trade'];
			$data['ready_send_fee'] +=$value['ready_send_fee'];
			$data['already_send_trade'] +=$value['already_send_trade'];
			$data['already_send_fee'] +=$value['already_send_fee'];
			$data['cancle_trade'] +=$value['cancle_trade'];
			$data['complete_trade'] +=$value['complete_trade'];
			$data['complete_fee'] +=$value['complete_fee'];
			$data['createtime'] =$value['createtime'];
		}

		if($data['new_trade']==0)
		{
			$data['averPrice'] = 0;
		}
		else
		{
			$data['averPrice'] = number_format($data['new_fee'] / $data['new_trade'], 2, '.',' ');
		}
		return $data;
	}

	/**
	 * 判断浏览器
	 * @param null
	 * @return null
	 */
	public function browserTip()
	{
		return $this->page('toptemai/common/browser_tip.html');
	}

    public function feedback()
    {
        $status = 'success';
        $msg = '提交成功';
        $validator = validator::make(
            [input::get('question'),input::get('tel'),input::get('email')],
            ['min:10|max:300','mobile','email'],
            ['提交问题最少10个字!|提交问题不能超过300个字','手机号码格式错误!', '邮箱格式错误']
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                return $this->splash('error',$url,$error[0],true);
            }
        }

        try
        {
            app::get('toptemai')->rpcCall('temai.feedback.add', input::get(), 'buyer');
        }
        catch (LogicException $e)
        {
            $msg = $e->getMessage();
            $status = 'error';
        }
        $this->sellerlog('提交意见反馈');
        return $this->splash($status,$url,$msg,true);
    }

    public function nopermission()
    {
        $pagedata['url'] = specialutils::filterCrlf(input::get('next_page', request::server('HTTP_REFERER')));
        return view::make('toptemai/permission.html',$pagedata);
    }

    public function onlySelfManagement()
    {
        $pagedata['url'] = specialutils::filterCrlf(input::get('next_page', request::server('HTTP_REFERER')));
        return view::make('toptemai/onlySelfManagement.html',$pagedata);
    }

}
