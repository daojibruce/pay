<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class toptemai_ctl_sysstat_sysstat extends toptemai_controller
{
	/**
	 * 根据时间shopid获取商家运营情况
	 * @param null
	 * @return array
	 */
	public function index()
	{
		$sendtype = input::get();
		$type = $sendtype['sendtype'];
		if(!$type || !in_array($type,array('yesterday','beforday','week','month')))
		{
			$type='yesterday';
		}
		$postSend['sendtype'] = $type;
		//api参数
		$all = $this->__getParams('all',$postSend,'trade');

		//获取交易数据
		$data = app::get('toptemai')->rpcCall('temai.sysstat.data.get',$all,'buyer');
		//业务数据
		$sysDataInfo = $this->getDataInfo();
		//商品排行
		$sysItemInfo = app::get('toptemai')->rpcCall('temai.sysstat.data.get',array('temai_user_id'=>$this->userId,'inforType'=>'item','timeType'=>$type,'limit' =>5,'start'=>0),'buyer');
		$pagedata['sysItemInfo'] = $sysItemInfo['sysTrade']?$sysItemInfo['sysTrade']:$sysItemInfo['sysTradeData'];
		//店铺流量数据
		$pagedata['traffic_disabled'] = config::get('stat.disabled');
		if(!$pagedata['traffic_disabled']){
			$all['temai_user_id'] = $this->userId;
			$pagedata['trafficData'] = app::get('toptemai')->rpcCall('sysstat.traffic.data.get',$all);
		}

		$pagedata['sysstat'] = $data['sysstat'];
		$pagedata['sendtype'] = $type;
		$pagedata['sysDataInfo'] = $sysDataInfo;
		$this->contentHeaderTitle = app::get('toptemai')->_('运营报表-商家运营概况');
		return $this->page('toptemai/sysstat/sysstat.html', $pagedata);
	}

	//获取页面下面数据
	private function getDataInfo()
	{
		//昨日数据
		$yesterday = app::get('toptemai')->rpcCall('sysstat.data.get',array('inforType'=>'trade','timeType'=>'yesterday'),'buyer');
		//前日数据
		$before = app::get('toptemai')->rpcCall('sysstat.data.get',array('inforType'=>'trade','timeType'=>'beforday'),'buyer');
		//本周数据
		$week = app::get('toptemai')->rpcCall('sysstat.data.get',array('inforType'=>'trade','timeType'=>'week'),'buyer');
		//本月数据
		$month = app::get('toptemai')->rpcCall('sysstat.data.get',array('inforType'=>'trade','timeType'=>'month'),'buyer');
		$data = array(
			'yesterday'=>$yesterday['sysstat'],
			'beforInfo'=>$before['sysstat'],
			'week'=>$week['sysstat'],
			'month'=>$month['sysstat'],
		);

		return $data;
	}

	/**
	 * 异步获取数据  图表用
	 * @param null
	 * @return array
	 */

	public function ajaxTrade()
	{
		$postData = input::get();
		//api的参数
		$all = $this->__getParams('graphall',$postData,'trade');
		$datas =  app::get('toptemai')->rpcCall('sysstat.data.get',$all,'buyer');

		return response::json($datas);
	}

	//api参数组织
	private function __getParams($type,$postSend,$objType,$data=null)
	{
		if($type=='all')
		{
			$params = array(
				'inforType'=>$objType,
				'timeType'=>$postSend['sendtype'],
				'starttime'=>$postSend['starttime'],
				'endtime'=>$postSend['endtime'],
				'dataType'=>$type
			);
		}
		elseif($type=='graphall')
		{
			$params = array(
				'inforType'=>$objType,
				'tradeType'=>$postSend['trade'],
				'timeType'=>$postSend['sendtype'],
				'starttime'=>$postSend['starttime'],
				'endtime'=>$postSend['endtime'],
				'dataType'=>$type
			);
		}
		return $params;
	}
}
