<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class toptemai_ctl_sysstat_itemtrade extends toptemai_controller
{
	/**
	 * 商品销售分析
	 * @param null
	 * @return null
	 */
	public function index()
	{
		$postSend = input::get();
		$type = $postSend['sendtype'];

		$objFilter = kernel::single('sysstat_data_filter');
		$params = $objFilter->filter($postSend);

		$itemtime = array('starttime'=>$postSend['itemtime']);
		if(!$postSend || !in_array($postSend['sendtype'],array('yesterday','beforday','week','month','selecttime')))
		{
			$type='yesterday';
		}
		$postSend['sendtype'] = $type;
		//api参数
		$all = $this->__getParams('all',$postSend,'item');
		$notAll = $this->__getParams('notall',$postSend,'item',$params);

		$itemInfo = app::get('toptemai')->rpcCall('sysstat.data.get',$notAll,'seller');

		$topParams = array('inforType'=>'item','timeType'=>$type,'starttime'=>$postSend['itemtime'],'limit'=>5);
		$topFiveItem = app::get('toptemai')->rpcCall('sysstat.data.get',$topParams,'seller');

		//获取页面显示的时间
		$pagetimes = app::get('toptemai')->rpcCall('sysstat.datatime.get',$all,'seller');
		//api的参数
		$countAll = $this->__getParams('all',$postSend,'itemcount');
		//处理翻页数据
		$countData = app::get('toptemai')->rpcCall('sysstat.data.get',$countAll,'seller');
		$count = $countData['count'];
		if($type == 'selecttime')
		{
			$pagetime = $pagetimes['before'];
		}
		else
		{
			$pagetime = $pagetimes;
		}

		if($count>0) $total = ceil($count/$params['limit']);
		$current = $postSend['pages'] ? $postSend['pages'] : 1;
		$pagedata['limits'] = $params['limit'];
		$pagedata['pages'] = $current;
		$postSend['pages'] = time();
		$pagedata['pagers'] = array(
			'link'=>url::action('toptemai_ctl_sysstat_itemtrade@index',$postSend),
			'current'=>$current,
			'total'=>$total,
			'use_app' => 'toptemai',
			'token'=>$postSend['pages']
		);
		$pagedata['sendtype'] = $type;
		$pagedata['itemInfo'] = $itemInfo['sysTrade']?$itemInfo['sysTrade']:$itemInfo['sysTradeData'];

		$pagedata['traffic_disabled'] = config::get('stat.disabled');
		if(!$pagedata['traffic_disabled']){
			$itemids = implode(',',array_column($pagedata['itemInfo'], 'item_id'));
			if($itemids){
				$apiData = $notAll;
				$apiData['itemids'] = $itemids;
				$pagedata['uvData'] = app::get('toptemai')->rpcCall('sysstat.traffic.data.get',$apiData);
			}
		}

		$pagedata['pagetime'] = $pagetime;
		$pagedata['topFiveItem'] = $topFiveItem['sysTrade'];

		$this->contentHeaderTitle = app::get('toptemai')->_('运营报表-商品销售分析');
		return $this->page('toptemai/sysstat/itemtrade.html', $pagedata);
	}

	/**
	 * 异步获取数据  图表用
	 * @param null
	 * @return array
	 */

	public function ajaxTrade()
	{
		$postData = input::get();

		$orderBy = $postData['trade'].' '.'DESC';
		$postData['orderBy'] = $orderBy;
		$postData['limit'] = 10;

		$grapParams = $this->__getParams('itemgraphall',$postData,'item');
		$datas =  app::get('toptemai')->rpcCall('sysstat.data.get',$grapParams,'seller');

		$ajaxdata = array('dataInfo'=>$data,'datas'=>$datas);

		return response::json($ajaxdata);
	}

	//api参数组织
	private function __getParams($type,$postSend,$objType,$data=null)
	{
		if($type=='all')
		{
			$params = array(
				'inforType'=>$objType,
				'timeType'=>$postSend['sendtype'],
				'starttime'=>$postSend['itemtime'],
			);
		}
		elseif($type=='notall')
		{
			$params = array(
				'inforType'=>$objType,
				'timeType'=>$postSend['sendtype'],
				'starttime'=>$postSend['itemtime'],
				'limit'=>intval($data['limit']),
				'start'=>intval($data['start'])
			);
		}
		elseif($type=='itemgraphall')
		{
			$params = array(
				'inforType'=>$objType,
				'tradeType'=>$postSend['trade'],
				'timeType'=>$postSend['sendtype'],
				'starttime'=>$postSend['itemtime'],
				'dataType'=>$type,
				'limit'=>intval($postSend['limit']),
				'orderBy'=>$postSend['orderBy'],
			);
		}
		return $params;
	}
}