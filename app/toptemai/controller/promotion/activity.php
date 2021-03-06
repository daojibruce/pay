<?php
class toptemai_ctl_promotion_activity extends toptemai_controller {

    // 我的活动报名列表
    public function registered_activity()
    {
        $this->contentHeaderTitle = app::get('toptemai')->_('活动报名');
        $filter = input::get();
        if(!$filter['pages'])
        {
            $filter['pages'] = 1;
        }
        $pageSize = 10;
        $params = array(
            'page_no' => intval($filter['pages']),
            'page_size' => intval($pageSize),
            'fields' =>'*',
            'shop_id' => $this->shopId,
            'activity_status' => 'starting',
        );
        $activityRegisterListData = app::get('toptemai')->rpcCall('promotion.activity.register.list', $params, 'seller');
        $count = $activityRegisterListData['count'];
        foreach ($activityRegisterListData['data'] as &$v)
        {
            $acparams = array(
                'activity_id' => $v['activity_id'],
                'fields' => '*',
            );
            $activityDetail = app::get('toptemai')->rpcCall('promotion.activity.info', $acparams, 'seller');
            $v['activity_name'] = $activityDetail['activity_name'];
            $v['start_time'] = $activityDetail['start_time'];
            $v['end_time'] = $activityDetail['end_time'];
            $v['activity_tag'] = $activityDetail['activity_tag'];
        }

        $pagedata['activityList'] = $activityRegisterListData['data'];

        //处理翻页数据
        $current = $filter['pages'] ? $filter['pages'] : 1;
        $filter['pages'] = time();
        if($count>0) $total = ceil($count/$pageSize);
        $pagedata['pagers'] = array(
            'link'=>url::action('toptemai_ctl_promotion_activity@registered_activity', $filter),
            'current'=>$current,
            'use_app' => 'toptemai',
            'total'=>$total,
            'token'=>$filter['pages'],
        );

        $pagedata['now'] = time();
        $pagedata['total'] = $count;

        return $this->page('toptemai/promotion/activity/registered.html', $pagedata);
    }

    //活动列表
    public function activity_list()
    {
        $this->contentHeaderTitle = app::get('toptemai')->_('活动列表');
        $filter = input::get();
        if(!$filter['pages'])
        {
            $filter['pages'] = 1;
        }
        $pageSize = 10;
        $params = array(
            'page_no' => intval($filter['pages']),
            'page_size' => $pageSize,
            'order_by' => 'apply_end_time desc',
            'fields' =>'*',
        );
        $activityListData = app::get('toptemai')->rpcCall('promotion.activity.list', $params, 'seller');

        foreach ($activityListData['data'] as $key => $value)
        {
            $data['activity_id'] = $value['activity_id'];
            $data['shop_id'] = $this->shopId;
            $registered_activity = app::get('toptemai')->rpcCall('promotion.activity.register.list', $data, 'seller');
            if($registered_activity['data'])
            {
                $activityListData['data'][$key]['verify_status'] = $registered_activity['data'][0]['verify_status'];
            }
        }
        //echo '<pre>';print_r($activityListData);exit();
        //获取商家店铺信息(shop、shop_info、brand)
        $shopId = $this->shopId;
        $params = array(
            'shop_id' => $shopId,
            'fields' =>'cat.cat_name,cat.cat_id,brand.brand_name,brand.brand_id,info',
        );
        $shopdata = app::get('toptemai')->rpcCall('shop.get.detail',$params);
        foreach ($shopdata['cat'] as $key => $value)
        {
            $catId[$key] = $value['cat_id'];
        }
        $shoptype = $shopdata['shop']['shop_type'];
        foreach ($activityListData['data'] as $key => $value)
        {
            if(array_intersect($catId,$value['limit_cat']) && in_array($shoptype,explode(',',$value['shoptype'])))
            {
                $activityListData['data'][$key]['isactivity']=1;
            }
            else
            {
                $activityListData['data'][$key]['isactivity']=0;
            }
        }
        //echo '<pre>';print_r($activityListData);exit();
        $count = $activityListData['count'];
        $pagedata['activityList'] = $activityListData['data'];

        //处理翻页数据
        $current = $filter['pages'] ? $filter['pages'] : 1;
        $filter['pages'] = time();
        if($count>0) $total = ceil($count/$pageSize);
        $pagedata['pagers'] = array(
            'link'=>url::action('toptemai_ctl_promotion_activity@activity_list', $filter),
            'current'=>$current,
            'use_app' => 'toptemai',
            'total'=>$total,
            'token'=>$filter['pages'],
        );

        $pagedata['now'] = time();
        $pagedata['total'] = $count;
        //echo '<pre>';print_r($pagedata);exit();
        return $this->page('toptemai/promotion/activity/list.html', $pagedata);
    }

    /**
     * @brief 活动报名未审核和审核通过后显示的报名详情
     *
     * @return
     */
    public function canregistered_detail()
    {
        $activityId = intval(input::get('activity_id'));
        // 获取活动规则信息
        $activityParams = array(
            'activity_id' => $activityId,
            'shop_id'=>$this->shopId,
            'fields' => '*',
        );

        $registered_activity = app::get('toptemai')->rpcCall('promotion.activity.register.list', $activityParams, 'seller');
        $pagedata = app::get('toptemai')->rpcCall('promotion.activity.info', $activityParams, 'seller');
        if($registered_activity)
        {
            $pagedata['registered_activity'] = $registered_activity['data'];
        }
        $pagedata['limit_cat'] = implode(',', $pagedata['limit_cat']);
        $pagedata['shoptype'] = implode(',', $pagedata['shoptype']);
        $pages = input::get('pages', 1);
        // 获取商家活动报名的商品信息
        $size = 10;
        $itemParams = array(
            'fields' => '*',
            'shop_id' => $this->shopId,
            'activity_id' => $activityId,
            'page_size' => $size,
            'page_no' => intval($pages),
        );
        
        $registerItemList = app::get('toptemai')->rpcCall('promotion.activity.item.list', $itemParams, 'seller');
        $pagedata['itemsList'] = $registerItemList['list'];
        $filter['pages'] = time();
        $filter['activity_id'] = $activityId;

        if($registerItemList['count']>0) $total = ceil($registerItemList['count']/$size);
        $pagedata['pagers'] = array(
                'link'=>url::action('toptemai_ctl_promotion_activity@canregistered_detail', $filter),
                'current'=>$pages,
                'use_app' => 'toptemai',
                'total'=>$total,
                'token'=>$filter['pages'],
        );
        $pagedata['total'] = $registerItemList['count'];
        
        return $this->page('toptemai/promotion/activity/canregistered_detail.html', $pagedata);
    }

    // 活动表名提交页面
    public function canregistered_apply()
    {
        $activityId = intval(input::get('activity_id'));

        // 获取活动规则信息
        $activityParams = array(
            'activity_id' => $activityId,
            'shop_id'=>$this->shopId,
            'fields' => '*',
        );

        $registered_activity = app::get('toptemai')->rpcCall('promotion.activity.register.list', $activityParams, 'seller');

        $pagedata = app::get('toptemai')->rpcCall('promotion.activity.info', $activityParams, 'seller');
        if($registered_activity)
        {
            $pagedata['registered_activity'] = $registered_activity['data'];
        }

        if($pagedata['activity_id']=='')
        {
            throw new \LogicException(app::get('toptemai')->_('异常操作！'));
        }
        $pagedata['limit_cat'] = implode(',', $pagedata['limit_cat']);
        $pagedata['shoptype'] = implode(',', $pagedata['shoptype']);

        // 获取商家活动报名的商品信息
        $itemParams = array(
            'fields' => 'activity_price,item_id',
            'shop_id' => $this->shopId,
            'activity_id' => $activityId,
        );

        $registerItemList = app::get('toptemai')->rpcCall('promotion.activity.item.list', $itemParams, 'seller');

        // 去重已经参加的活动商品
        if($registerItemList['list'])
        {
            $notItems = array_column($registerItemList['list'], 'item_id');
            $pagedata['notEndItem'] =  json_encode($notItems,true);
            $pagedata['datavalue'] =  json_encode($registerItemList['list'],true);
        }

        return $this->page('toptemai/promotion/activity/canregistered_apply.html', $pagedata);
    }

    public function canregistered_apply_save()
    {
        $params = input::get();

        $apiData['shop_id'] = $this->shopId;
        $apiData['activity_id'] = (int) $params['activity_id'];
        //判断重复提交
        $data['activity_id'] = (int) $params['activity_id'];
        $data['shop_id'] = $this->shopId;
        $registered_activity = app::get('toptemai')->rpcCall('promotion.activity.register.list', $data, 'seller');
        if($registered_activity['data'] && $registered_activity['data'][0]['verify_status'] != "refuse")
        {
            $msg = '该活动已经报过名了，不可以重复报名！';
            return $this->splash('error',null,$msg);
        }

        $itemWithPrice = array();
        if(!$params['item_activity_price'])
        {
            $msg = '您还没有选择商品，请重新选择！';
            return $this->splash('error',null,$msg);
        }

        foreach ($params['item_activity_price'] as $itemId => $activityPrice)
        {
            $validator = validator::make(
                    [$activityPrice,$itemId],
                    ['required|numeric','required|numeric'],
                    ['请设置商品价格!|商品价格格式有误!','请设置活动商品!|请勿使用非法手段更改商品数据!']
            );
            if ($validator->fails())
            {
                $messages = $validator->messagesInfo();
                foreach( $messages as $error )
                {
                    return $this->splash('error',null,$error[0]);
                }
            }
            $itemWithPrice[] = $itemId.':'.$activityPrice;
        }
        $apiData['item_info'] = implode(';', $itemWithPrice);
        //echo '<pre>';print_r($apiData);exit();
        try
        {
            // 活动报名保存
            $result = app::get('toptemai')->rpcCall('promotion.activity.register', $apiData, 'seller');
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            $url = url::action('toptemai_ctl_promotion_activity@canregistered_apply', array('activity_id'=>$params['activity_id']));
            return $this->splash('error',$url,$msg,true);
        }
        $this->sellerlog('申请活动。活动ID是 '.$apiData['activity_id']);
        $url = url::action('toptemai_ctl_promotion_activity@activity_list');
        $msg = app::get('toptemai')->_('申请活动保存成功');
        return $this->splash('success',$url,$msg,true);

    }

    // 历史报名活动列表
    public function historyregistered_activity()
    {
        $this->contentHeaderTitle = app::get('toptemai')->_('活动管理');
        $filter = input::get();
        if(!$filter['pages'])
        {
            $filter['pages'] = 1;
        }
        $pageSize = 10;
        $params = array(
            'page_no' => intval($filter['pages']),
            'page_size' => $pageSize,
            'shop_id' => $this->shopId,
            'activity_status' => 'end',
            'fields' =>'*',
        );

        $activityRegisterListData = app::get('toptemai')->rpcCall('promotion.activity.register.list', $params, 'seller');
        $count = $activityRegisterListData['count'];
        foreach ($activityRegisterListData['data'] as &$v)
        {
            $acparams = array(
                'activity_id' => $v['activity_id'],
                'fields' => '*',
            );
            $activityDetail = app::get('toptemai')->rpcCall('promotion.activity.info', $acparams, 'seller');
            $v['activity_name'] = $activityDetail['activity_name'];
            $v['start_time'] = $activityDetail['start_time'];
            $v['end_time'] = $activityDetail['end_time'];
            $v['activity_tag'] = $activityDetail['activity_tag'];
        }

        $pagedata['activityList'] = $activityRegisterListData['data'];


        //处理翻页数据
        $current = $filter['pages'] ? $filter['pages'] : 1;
        $filter['pages'] = time();
        if($count>0) $total = ceil($count/$pageSize);
        $pagedata['pagers'] = array(
            'link'=>url::action('toptemai_ctl_promotion_activity@historyregistered_activity', $filter),
            'current'=>$current,
            'use_app' => 'toptemai',
            'total'=>$total,
            'token'=>$filter['pages'],
        );

        $pagedata['total'] = $count;

        return $this->page('toptemai/promotion/activity/historyregistered.html', $pagedata);
    }
    //历史报名活动详情
    public function historyregistered_detail()
    {
        $activityId = intval(input::get('activity_id'));

        // 获取活动规则信息
        $activityParams = array(
            'activity_id' => $activityId,
            'fields' => '*',
        );
        $pagedata = app::get('toptemai')->rpcCall('promotion.activity.info', $activityParams, 'seller');
        if($pagedata['activity_id']=='')
        {
            throw new \LogicException(app::get('toptemai')->_('异常操作！'));
        }
        $pagedata['limit_cat'] = implode(',', $pagedata['limit_cat']);
        $pagedata['shoptype'] = implode(',', $pagedata['shoptype']);

        // 获取商家活动报名的商品信息
        $itemParams = array(
            'fields' => '*',
            'shop_id' => $this->shopId,
            'activity_id' => $activityId,
        );

        $registerItemList = app::get('toptemai')->rpcCall('promotion.activity.item.list', $itemParams, 'seller');
        $pagedata['itemsList'] = $registerItemList['list'];
        //echo '<pre>';print_r($pagedata);exit();

        return $this->page('toptemai/promotion/activity/historyregistered_detail.html', $pagedata);
    }

    // 不可报名活动详情
    public function noregistered_detail()
    {
        $params = array(
            'activity_id' => intval(input::get('activity_id')),
            'fields' => '*',
        );
        $pagedata = app::get('toptemai')->rpcCall('promotion.activity.info', $params, 'seller');
        $pagedata['limit_cat'] = implode(',', $pagedata['limit_cat']);
        $pagedata['shoptype'] = implode(',', $pagedata['shoptype']);
        return $this->page('toptemai/promotion/activity/noregistered_detail.html', $pagedata);
    }
    //根据商家id和3级分类id获取商家所经营的所有品牌
    public function getBrandList()
    {
        $shopId = $this->shopId;
        $catId = input::get('catId');
        $params = array(
            'shop_id'=>$shopId,
            'cat_id'=>$catId,
            'fields'=>'brand_id,brand_name,brand_url'
        );
        $brands = app::get('toptemai')->rpcCall('category.get.cat.rel.brand',$params);
        return response::json($brands);
    }
}


