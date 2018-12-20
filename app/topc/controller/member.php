<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topc_ctl_member extends topc_controller {

    public $verifyType = ['mobile','email'];
    public $sendType = ['update','delete'];
    protected $cachePaymentIdKey = 'pc:pay.wait.payid';
    public function __construct(&$app)
    {
        parent::__construct();
        kernel::single('base_session')->start();
        if(!$this->action) $this->action = 'index';
        $this->action_view = $this->action.".html";
        // 检测是否登录
        if( !userAuth::check() )
        {
            redirect::action('topc_ctl_passport@signin')->send();exit;
        }
        $this->limit = 10;

        $this->passport = kernel::single('topc_passport');
        $this->setLayoutFlag('member');
    }

    public function control() {
        //用户信息
        $userInfo = userAuth::getUserInfo();
        $pagedata['userInfo'] = $userInfo;

        //已开通应用
        $userId = userAuth::id();
        $roleList = config::get('userAuth.user_role');
        $userRoles = userAuth::getUserRoles($userId);
        $userRole = $userRoles['roles'];
        /*unset(
            $userRole[SYS_USER_ROLE_SELLER],
            $userRole[SYS_USER_ROLE_COMPANY],
            $roleList[SYS_USER_ROLE_SELLER],
            $roleList[SYS_USER_ROLE_COMPANY]
        );*/

        //未开通应用
        $notUserRole = [];
        foreach($roleList AS $key => $role) {
            if(!in_array($key, array_keys($userRole))) {
                if ($key == '企业会员') {
                    $role['use'] = 'topc_ctl_passport@companysignup';
                    $role['url'] = url::action('topc_ctl_passport@companysignup');
                }
                $notUserRole[$key] = $role;
            }
        }
        $pagedata['userRole'] = $userRole;
        $pagedata['notUserRole'] = $notUserRole;

        //我的积分
        $credit = app::get('sysuser')->model('user_credit')->getRow('*', ['user_id' => $userId]);
        if(!$credit) {
            $credit['total'] = 0;
            $credit['base'] = 0;
            $credit['seller_ex'] =
            $credit['seller_trade'] =
            $credit['seller_credit'] =
            $credit['temai_ex'] =
            $credit['temai_trade'] =
            $credit['temai_credit'] =
            $credit['proofing_ex'] =
            $credit['proofing_trade'] =
            $credit['proofing_credit'] = 0;
        }
        $pagedata['credit'] = $credit;

        $params = array(
            'filter'=>array('user_id'=>$userId),
            'limit' =>5,
        );
        $countParams = array(
            'filter' => array(
                'status' => array('WAIT_BUYER_PAY','WAIT_SELLER_SEND_GOODS','WAIT_BUYER_CONFIRM_GOODS'),
                'user_id'=>$userId,
            ),
            'rows' => "tid,status",
        );
        //获取订单各种状态的数量
        $pagedata['nupay'] = app::get('topc')->rpcCall('trade.count',array('user_id'=>$userId,'status'=>'WAIT_BUYER_PAY'));
        $pagedata['nudelivery'] = app::get('topc')->rpcCall('trade.count',array('user_id'=>$userId,'status'=>'WAIT_SELLER_SEND_GOODS'));
        $pagedata['nuconfirm'] = app::get('topc')->rpcCall('trade.count',array('user_id'=>$userId,'status'=>'WAIT_BUYER_CONFIRM_GOODS'));

        //获取最新订单5条
        $tradeParams['page_no'] = 1;
        $tradeParams['user_id'] =$userId;
        $tradeParams['page_size'] = 5;
        $tradeParams['order_by'] = " created_time DESC";
        $tradeParams['fields'] = 'tid,shop_id,user_id,status,payment,points_fee,total_fee,post_fee,payed_fee,pay_type,receiver_name,created_time,receiver_mobile,discount_fee,need_invoice,adjust_fee,order.title,order.price,order.num,order.pic_path,order.tid,order.oid,order.item_id,activity,order.gift_data';
        $tradelist = app::get('topc')->rpcCall('trade.get.list',$tradeParams);
        $pagedata['trades'] = $tradelist['list'];
        foreach ($pagedata['trades'] as &$v)
        {
            // 获取店铺子域名
            $v['subdomain'] = app::get('topc')->rpcCall('shop.subdomain.get',array('shop_id'=>$v['shop_id']))['subdomain'];
        }
        // 批量获取店铺名称，减少SQL查询
        $shopIds = array_column($pagedata['trades'], 'shop_id');
        if( $shopIds )
        {
            $shopIds = implode(',', $shopIds);
            $pagedata['shopName'] = app::get('systrade')->rpcCall('shop.get.shopname',array('shop_id'=>$shopIds));
        }
        //会员收藏
        $collectParams['page_no'] = 1;
        $collectParams['page_size'] = 10;
        $collectParams['order_by'] = "gnotify_id DESC";
        $collectParams['fields'] = "gnotify_id,image_default_id,goods_name,goods_price,item_id,user_id,cat_id,object_type";
        $collectParams['user_id'] = $userId ;

        $favList = app::get('topc')->rpcCall('user.itemcollect.list',$collectParams,'buyer');
        $pagedata['favList'] = $favList['itemcollect'];

        //会员店铺收藏
        $collectParams['page_no'] = 1;
        $collectParams['page_size'] = 10;
        $collectParams['order_by'] = "snotify_id DESC";
        $collectParams['fields'] = "snotify_id,shop_id,user_id,shop_name,shop_logo";
        $collectParams['user_id'] = $userId ;

        $pagedata['hongbao_total'] = app::get('topc')->rpcCall('user.hongbao.count',['user_id'=>$userId])['hongbao_total'];

        $pagedata['isCompany'] = userAuth::isCompany();
        $pagedata['action'] = 'topc_ctl_member@control';
        $this->action_view = "newindex.html";
        return $this->output($pagedata);

        //return $this->page('topc/member/newindex.html', $pagedata);
    }

    public function index()
    {
        $userId = userAuth::id();
        //会员信息
        $userInfo = userAuth::getUserInfo();
        $pagedata['userInfo'] = $userInfo;

        $params = array(
            'filter'=>array('user_id'=>$userId),
            'limit' =>5,
        );
        $countParams = array(
            'filter' => array(
                'status' => array('WAIT_BUYER_PAY','WAIT_SELLER_SEND_GOODS','WAIT_BUYER_CONFIRM_GOODS'),
                'user_id'=>$userId,
            ),
            'rows' => "tid,status",
        );
        //获取订单各种状态的数量
        $pagedata['nupay'] = app::get('topc')->rpcCall('trade.count',array('user_id'=>$userId,'status'=>'WAIT_BUYER_PAY'));
        $pagedata['nudelivery'] = app::get('topc')->rpcCall('trade.count',array('user_id'=>$userId,'status'=>'WAIT_SELLER_SEND_GOODS'));
        $pagedata['nuconfirm'] = app::get('topc')->rpcCall('trade.count',array('user_id'=>$userId,'status'=>'WAIT_BUYER_CONFIRM_GOODS'));

        //获取最新订单5条
        $tradeParams['page_no'] = 1;
        $tradeParams['user_id'] =$userId;
        $tradeParams['page_size'] = 5;
        $tradeParams['order_by'] = " created_time DESC";
        $tradeParams['fields'] = 'tid,shop_id,user_id,status,payment,points_fee,total_fee,post_fee,payed_fee,pay_type,receiver_name,created_time,receiver_mobile,discount_fee,need_invoice,adjust_fee,order.title,order.price,order.num,order.pic_path,order.tid,order.oid,order.item_id,activity,order.gift_data';
        $tradelist = app::get('topc')->rpcCall('trade.get.list',$tradeParams);
        $pagedata['trades'] = $tradelist['list'];
        foreach ($pagedata['trades'] as &$v)
        {
            // 获取店铺子域名
            $v['subdomain'] = app::get('topc')->rpcCall('shop.subdomain.get',array('shop_id'=>$v['shop_id']))['subdomain'];
        }
        // 批量获取店铺名称，减少SQL查询
        $shopIds = array_column($pagedata['trades'], 'shop_id');
        if( $shopIds )
        {
            $shopIds = implode(',', $shopIds);
            $pagedata['shopName'] = app::get('systrade')->rpcCall('shop.get.shopname',array('shop_id'=>$shopIds));
        }
        //会员收藏
        $collectParams['page_no'] = 1;
        $collectParams['page_size'] = 10;
        $collectParams['order_by'] = "gnotify_id DESC";
        $collectParams['fields'] = "gnotify_id,image_default_id,goods_name,goods_price,item_id,user_id,cat_id,object_type";
        $collectParams['user_id'] = $userId ;

        $favList = app::get('topc')->rpcCall('user.itemcollect.list',$collectParams,'buyer');
        $pagedata['favList'] = $favList['itemcollect'];

        //会员店铺收藏
        $collectParams['page_no'] = 1;
        $collectParams['page_size'] = 10;
        $collectParams['order_by'] = "snotify_id DESC";
        $collectParams['fields'] = "snotify_id,shop_id,user_id,shop_name,shop_logo";
        $collectParams['user_id'] = $userId ;

        $pagedata['hongbao_total'] = app::get('topc')->rpcCall('user.hongbao.count',['user_id'=>$userId])['hongbao_total'];

        //会员优惠券
        $pagedata['coupon'] = app::get('topm')->rpcCall('user.coupon.list',['user_id'=>$userId, 'is_valid'=>'1', 'page_size'=>1]);
        $favShopList = app::get('topc')->rpcCall('user.shopcollect.list',$collectParams,'buyer');
        $pagedata['favShopList'] = $favShopList['shopcollect'];
        foreach ($pagedata['favShopList'] as &$v)
        {
            // 获取店铺子域名
            $v['subdomain'] = app::get('topc')->rpcCall('shop.subdomain.get',array('shop_id'=>$v['shop_id']))['subdomain'];
        }
        $pagedata['action']= 'topc_ctl_cart@index';

        //获取默认图片信息
        $pagedata['defaultImageId']= kernel::single('image_data_image')->getImageSetting('item');

        //浏览历史
        $pagedata['itemBrowserHistory']= $this->itemBrowserHistoryGet();

        //会员签到
        $pagedata['isCheckin'] = app::get('sysconf')->getConf('open.checkin');
        $pagedata['isPoint'] = app::get('sysconf')->getConf('open.point');
        $pagedata['checkinPointNum'] = app::get('sysconf')->getConf('checkinPoint.num');
        $params =array(
            'user_id' => $userId,
            'checkin_date' => date('Y-m-d'),
        );
        $pagedata['checkin_status'] = app::get('topc')->rpcCall('user.get.checkin.info',$params);

        $pagedata['member_role_list'] = config::get('userAuth.user_role');
        unset($pagedata['member_role_list'][SYS_USER_ROLE_CUSTOM]);
        //查看用户是否是服务商
        $proInfo = app::get('sysproofing')->model('provider')->getRow('provider_id',['user_id' => $userId, 'enabled' => '1']);
        if ($proInfo) {
            $pagedata['is_provider'] = 1;
        }
        //unset($pagedata['member_role_list'][SYS_USER_ROLE_COMPANY]);
        $pagedata['isCompany'] = userAuth::isCompany();

        return $this->output($pagedata);
    }

    public function itemBrowserHistoryGet()
    {
        $browserHistoryItemIds = app::get('topc')->rpcCall('user.browserHistory.get',array('user_id'=>userAuth::id()));
        $itemIds = $browserHistoryItemIds['itemIds'];

        if( !$itemIds ) return array();

        $itemIds = implode(',',$itemIds);
        $fields = 'item_id,title,price,image_default_id';
        $data = app::get('topc')->rpcCall('item.list.get',['item_id'=>$itemIds, 'fields'=>$fields]);
        foreach( explode(',',$itemIds) as $id  )
        {
            $return[$id] = $data[$id];
        }
        return $return;
    }

    /**
     * @brief 会员中心首页订单tb
     *
     *
     * @return html
     */
    public function tradeStatusList()
    {
        $userId = userAuth::id();
        $postData = input::get();
        //获取最新订单5条
        $tradeParams['page_no'] = 1;
        $tradeParams['user_id'] =$userId;
        $tradeParams['page_size'] = 5;
        $tradeParams['order_by'] = " created_time DESC";
        if($postData['status']!=0 && $postData['status']!='sellerRate')
        {
            $tradeParams['status'] = $this->__getTradeStatus($postData['status']);
        }
        if($postData['status']=='sellerRate')
        {
            $tradeParams['buyer_rate'] = '0';
            $tradeParams['status'] = 'TRADE_FINISHED';
        }
        $tradeParams['fields'] = 'tid,shop_id,user_id,status,payment,points_fee,total_fee,post_fee,payed_fee,receiver_name,created_time,receiver_mobile,discount_fee,need_invoice,adjust_fee,order.title,order.price,order.num,order.pic_path,order.tid,order.oid,order.item_id,order.seller_rate,activity,pay_type';
        $tradelist = app::get('topc')->rpcCall('trade.get.list',$tradeParams);
        foreach ($tradelist['list'] as &$v)
        {
            // 获取店铺子域名
            $v['subdomain'] = app::get('topc')->rpcCall('shop.subdomain.get',array('shop_id'=>$v['shop_id']))['subdomain'];
        }
        $pagedata['trades'] = $tradelist['list'];
        return view::make('topc/member/index/tradestatuslist.html', $pagedata);
    }

    private function __getTradeStatus($data)
    {
        $statusLUT = array(
            '0' => 'all',
            '1' => 'WAIT_BUYER_PAY',
            '2' => 'WAIT_BUYER_CONFIRM_GOODS',
            //'3' => 'TRADE_FINISHED',
        );
        if(!$statusLUT[$data])
        {
            return fasle;
        }
        return $statusLUT[$data];
    }

    /**
     * @brief 页面输出的统一页面
     *
     * @return html
     */
    public function output($pagedata = [])
    {
        $pagedata['cpmenu'] = config::get('newusermenu');
        if( $pagedata['_PAGE_'] ){
            $pagedata['_PAGE_'] = 'topc/member/'.$pagedata['_PAGE_'];
        }else{
            $pagedata['_PAGE_'] = 'topc/member/'.$this->action_view;
        }

        /* 角色划分是否显示某些菜单 */
        //非企业用户，移除企业功能
        if(!userAuth::isCompany()) {
            unset($pagedata['cpmenu'][7], $pagedata['cpmenu'][5][2]);
        }

        //服务撮合服务
        if(!userAuth::isProofing()) {
            //unset($pagedata['cpmenu'][2]);
        }

        return $this->page('topc/member/main.html', $pagedata);
    }

    public function proofingOutput($pagedata = [])
    {
        $pagedata['cpmenu'] = config::get('deepmenu.proofing_front');
        if( $pagedata['_PAGE_'] ){
            $pagedata['_PAGE_'] = 'topc/member/'.$pagedata['_PAGE_'];
        }else{
            $pagedata['_PAGE_'] = 'topc/member/'.$this->action_view;
        }

        /* 角色划分是否显示某些菜单 */
        //非企业用户，移除企业功能
        if(!userAuth::isCompany()) {
            unset($pagedata['cpmenu'][7], $pagedata['cpmenu'][5][2]);
        }

        //服务撮合服务
        if(!userAuth::isProofing()) {
            //unset($pagedata['cpmenu'][2]);
        }

        return $this->page('topc/member/proofing_main.html', $pagedata);
    }

    public function buycenterOutput($pagedata = [])
    {
        $pagedata['cpmenu'] = config::get('deepmenu.buycenter');
        if( $pagedata['_PAGE_'] ){
            $pagedata['_PAGE_'] = 'topc/member/'.$pagedata['_PAGE_'];
        }else{
            $pagedata['_PAGE_'] = 'topc/member/'.$this->action_view;
        }

        /* 角色划分是否显示某些菜单 */
        //非企业用户，移除企业功能
        if(!userAuth::isCompany()) {
            unset($pagedata['cpmenu'][7], $pagedata['cpmenu'][5][2]);
        }

        //服务撮合服务
        if(!userAuth::isProofing()) {
            //unset($pagedata['cpmenu'][2]);
        }

        return $this->page('topc/member/buycenter_main.html', $pagedata);
    }

    public function temaiOutput($pagedata = [])
    {
        $pagedata['cpmenu'] = config::get('deepmenu.buycenter');
        if( $pagedata['_PAGE_'] ){
            $pagedata['_PAGE_'] = 'topc/member/'.$pagedata['_PAGE_'];
        }else{
            $pagedata['_PAGE_'] = 'topc/member/'.$this->action_view;
        }

        /* 角色划分是否显示某些菜单 */
        //非企业用户，移除企业功能
        if(!userAuth::isCompany()) {
            unset($pagedata['cpmenu'][7], $pagedata['cpmenu'][5][2]);
        }

        //服务撮合服务
        if(!userAuth::isProofing()) {
            //unset($pagedata['cpmenu'][2]);
        }

        return $this->page('topc/member/temai_main.html', $pagedata);
    }

    /**
     * @brief 会员地址输出
     *
     * @return html
     */
    public function address()
    {
        $userId = userAuth::id();
        $params['user_id'] = $userId;
        //会员收货地址
        $userAddrList = app::get('topc')->rpcCall('user.address.list',$params,'buyer');
        $count = $userAddrList['count'];
        $userAddrList = $userAddrList['list'];
        foreach ($userAddrList as $key => $value) {
            $userAddrList[$key]['area'] = explode(":",$value['area'])[0];
        }

        $pagedata['userAddrList'] = $userAddrList;
        $pagedata['userAddrCount'] = $count;
        $pagedata['action'] = 'topc_ctl_member@address';
        $this->action_view = "address.html";
        return $this->output($pagedata);
    }
    /**
     * @brief 会员地址保存
     *
     * @return html
     */
    public function saveAddress()
    {
        $userId = userAuth::id();
        $postData =input::get();
        $postData['area'] = input::get()['area'][0];
        $postData['user_id'] = $userId;
        $area = app::get('topc')->rpcCall('logistics.area',array('area'=>$postData['area']));
        //echo '<pre>';var_dump(intval($postData));
        $validator = validator::make(
            [
             'area' => $area,
             'addr' => $postData['addr'] ,
             'name' => $postData['name'],
             'mobile' => $postData['mobile'],
             'user_id' =>$postData['user_id'],
             'zip' =>intval($postData['zip']),
            ],
            [
            'area' => 'required|max:20',
            'addr' => 'required',
            'name' => 'required',
            'mobile' => 'required|mobile',
            'user_id' => 'required',
             'zip' =>'numeric|max:999999',
            ],
            [
             'area' => '地区不存在!',
             'addr' => '会员街道地址必填!',
             'name' => '收货人姓名未填写!',
             'mobile' => '手机号码必填!|手机号码格式不正确!',
             'user_id' => '缺少参数!',
             'zip' =>'邮编必须为6位数的整数|邮编最大为999999',
            ]
        );
        //echo '<pre>';var_dump(intval($postData));
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();

            foreach( $messages as $error )
            {
                return $this->splash('error',null,$error[0]);
            }
        }
        $areaId =  str_replace(",","/", $postData['area']);
        $postData['area'] = $area . ':' . $areaId;
        if( $postData['addr_id'] )
        {
            $msg = app::get('topc')->_('修改收货地址成功');
        }
        else
        {
            $msg = app::get('topc')->_('添加成功');
        }

        try
        {
            app::get('topc')->rpcCall('user.address.add',$postData,'buyer');
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        $url = url::action('topc_ctl_member@address');
        return $this->splash('success',$url,$msg);

    }
    /**
     * @brief 会员地址编辑
     *
     * @return html
     */
    public function ajaxAddrUpdate()
    {
        $params['addr_id'] = input::get('addr_id');
        $params['user_id'] = userAuth::id();
        $addrInfo = app::get('topc')->rpcCall('user.address.info',$params);
        list($regions,$region_id) = explode(':', $addrInfo['area']);
        $addrInfo['area'] = $regions;
        $addrInfo['region_id'] = str_replace('/', ',', $region_id);
        return response::json($addrInfo);
    }

    public function myRequirement()
    {
        /*status 0:需求提出;1:中标;2:完成;3:关闭需求;
         * */
        $userId = userAuth::id();
        $page = input::get('pages') ? input::get('pages') : 1;
        $db = app::get('sysproofing')->database();
        $sql = "SELECT s.*,r.requirement_id,r.user_id,r.start_time,r.end_time FROM sysproofing_sample as s LEFT JOIN sysproofing_requirement as r ON s.requirement_id=r.requirement_id WHERE r.user_id=".$userId;
        if (input::get('status') && input::get('status') != '0') {
            if (input::get('status') == '2') {
                $sql .= " AND s.status=2";
                $pagedata['req_status'] = 2;
            } else {
                $sql .= " AND s.status<>2";
                $pagedata['req_status'] = 1;
            }
        } else {
            $pagedata['req_status'] = 0;
        }
        $sampleInfo = $db->executeQuery($sql)->fetchAll();
        $count = count($sampleInfo);
        $sql .= " LIMIT ".$this->limit*($page-1).",".$this->limit;
        $sampleInfo = $db->executeQuery($sql)->fetchAll();
        foreach ($sampleInfo as $key => $sample) {
            if (app::get('sysproofing')->model('offer')->getRow('offer_id',['sample_id' => $sample['sample_id']])) {
                $sampleInfo[$key]['has_offer'] = 1;
            }
            $catInfo = app::get('sysproofing')->model('category')->getRow('cat_name',['cat_id' => $sample['cat_id']]);
            $sampleInfo[$key]['cat_name'] = $catInfo['cat_name'];
        }
        $pagedata['samples'] = $sampleInfo;
        $pagedata['pagers'] = $this->__pager([],$page, $count);
        //echo "<pre>";print_r($sampleInfo);exit;

        $this->action_view = "myRequirement.html";
        $pagedata['action'] = 'topc_ctl_member@myRequirement';
        return $this->proofingOutput($pagedata);
    }

    private function __pager($postFilter,$page,$count)
    {
        $postFilter['pages'] = time();
        $total = ceil($count/$this->limit);
        $pagers = array(
            'link'=>url::action('topc_ctl_member@myRequirement',$postFilter),
            'current'=>$page,
            'use_app' => 'topc',
            'total'=>$total,
            'token'=>time(),
        );
        return $pagers;

    }

    //
    public function cancelRequirement()
    {
        $data = input::get('data');
        $samMdl = app::get('sysproofing')->model('sample');
        $reqId = [];
        foreach ($data as $sample_id) {
            $res = $samMdl->getRow('status, requirement_id',['sample_id' => $sample_id]);
            if ($res['status'] != 0) {
                echo json_encode(['status' => 'error', 'message' => '已中标需求无法关闭！']);exit;
            }
            $reqId[] = $res['requirement_id'];
        }
        foreach ($data as $sample_id) {
            $params = ['status' => 3, 'sample_id' => $sample_id];
            $re = $samMdl->save($params);
        }
        //检查需求中的样品是否全部关闭
        $db = app::get('sysproofing')->database();
        foreach ($reqId as $requirement_id) {
            $res = $db->executeQuery('SELECT sample_id FROM sysproofing_sample WHERE requirement_id='.$requirement_id.' AND (status=0 OR status=1)')->fetchAll();
            if (!$res) {
                $params = ['requirement_id' => $requirement_id, 'status' => '1'];
                app::get('sysproofing')->model('requirement')->save($params);
            }
        }
        echo json_encode(['status' => 'success']);exit;
    }

    public function delRequirement()
    {
        $data = input::get('data');
        $samMdl = app::get('sysproofing')->model('sample');
        $reqId = [];
        foreach ($data as $sample_id) {
            $res = $samMdl->getRow('status, requirement_id',['sample_id' => $sample_id]);
            if ($res['status'] == 1 && $res['status'] == 2) {
                echo json_encode(['status' => 'error', 'message' => '已中标需求无法删除！']);exit;
            } elseif ($res['status'] == 3) {
                echo json_encode(['status' => 'error', 'message' => '该需求已关闭!']);exit;
            }
            $reqId[] = $res['requirement_id'];
        }
        foreach ($data as $sample_id) {
            $params = ['sample_id' => $sample_id];
            $re = $samMdl->delete($params);
        }
        //检查需求中的样品是否全部关闭
        $db = app::get('sysproofing')->database();
        foreach ($reqId as $requirement_id) {
            $res = $db->executeQuery('SELECT sample_id FROM sysproofing_sample WHERE requirement_id='.$requirement_id.' AND (status=0 OR status=1)')->fetchAll();
            if (!$res) {
                $params = ['requirement_id' => $requirement_id, 'status' => '1'];
                app::get('sysproofing')->model('requirement')->save($params);
            }
        }
        echo json_encode(['status' => 'success']);exit;
    }

    public function myRequirementPrice()
    {
        $sample_id =input::get('sample_id');
        if (!$sample_id) {
            return redirect::action('topc_ctl_member@myRequirement');
        }

        $db = app::get('sysproofing')->database();
        $sql = "SELECT o.*,s.sample_name,s.quantity,s.unit FROM sysproofing_offer AS o LEFT JOIN sysproofing_sample AS s ON o.sample_id=s.sample_id WHERE o.sample_id=".$sample_id;
        $offers = $db->executeQuery($sql)->fetchAll();
        if ($offers) {
            foreach ($offers as $key => $offer) {
                $providerInfo = app::get('sysproofing')->model('provider')->getRow('provider_name',['provider_id' => $offer['provider_id']]);
                $offers[$key]['provider_name'] = $providerInfo['provider_name'];
                $offers[$key]['per_fee'] = round($offer['sample_fee']/$offer['quantity'], 2);
                if ($offer['post_type'] == '1') {
                    $offers[$key]['post_type'] = '分批';
                } else {
                    $offers[$key]['post_type'] = '一次性';
                }
                if ($offer['pay_type'] == '1') {
                    $params = unserialize($offer['params']);//echo "<pre>";print_r($params);exit;
                    $str = '订单生成'.$params['fee_create'].'%&nbsp;&nbsp;&nbsp;&nbsp;确认收货'.$params['fee_confirm'].'%';
                    if ($params['time1']) $str .= '&nbsp;&nbsp;&nbsp;&nbsp;'.$params['time1'].'个月后运行款'.$params['fee_1'].'%';
                    if ($params['time2']) $str .= '&nbsp;&nbsp;&nbsp;&nbsp;'.$params['time2'].'个月后质保金'.$params['fee_2'].'%';
                    if ($params['time3']) $str .= '&nbsp;&nbsp;&nbsp;&nbsp;'.$params['time3'].'个月后'.$params['fee_3'].'%';
                    $offers[$key]['pay_type'] = $str;
                } else {
                    $offers[$key]['post_type'] = '一次性';
                }
            }
            $pagedata['offers'] = $offers;
            //echo "<pre>";print_r($offers);exit;
        }

        $this->action_view = "myRequirementPrice.html";
        $pagedata['action'] = 'topc_ctl_member@myRequirementPrice';
        return $this->proofingOutput($pagedata);
    }

    public function bidOffer()
    {
        $offer_id = input::get('offer_id');
        $sample_id = input::get('sample_id');
        $sampleMdl = app::get('sysproofing')->model('sample');
        $offerMdl = app::get('sysproofing')->model('offer');
        //检查sample状态
        $sampleInfo = $sampleMdl->getRow('status, requirement_id',['sample_id' => $sample_id]);
        if ($sampleInfo['status'] != 0) {
            return response::json(['status' => 'error', 'message' => '该需求状态已发生变化', 'url'=> url::action('topc_ctl_member@myRequirement')]);exit;
        } else {
            try{
                $db = app::get('sysproofing')->database();
                $db->beginTransaction();
                $params = ['offer_id' => $offer_id, 'status' => 1];
                $offerMdl->save($params);
                $params2 = ['sample_id' => $sample_id, 'status' => 1];
                $sampleMdl->save($params2);
                $db->commit();
                return response::json(['status' => 'success', 'message' => '确认中标成功！', 'url'=> url::action('topc_ctl_trade@proofingCreate',['offer_id' => $offer_id])]);exit;
            } catch(Exception $e) {
                $db->rollback();
                return response::json(['status' => 'error', 'message' => '确认失败！']);exit;
            }
        }
    }


    /**
     * @brief 设置默认会员地址
     *
     * @return html
     */
    public function ajaxAddrDef()
    {
        $userId = userAuth::id();

        $params['addr_id'] = input::get('addr_id');
        $params['user_id'] = $userId;

        try
        {
            app::get('topc')->rpcCall('user.address.setDef',$params,'buyer');
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }
        $msg = app::get('topc')->_('设置成功');
        return $this->splash('success',null,$msg);

    }
    /**
     * @brief 删除会员地址
     *
     * @return html
     */
    public function ajaxDelAddr()
    {
        $userId = userAuth::id();
        $params['addr_id'] = input::get('addr_id');
        $params['user_id'] = $userId;

        try
        {
            app::get('topc')->rpcCall('user.address.del',$params,'buyer');
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }
        $url = url::action('topc_ctl_member@address');
        $msg = app::get('topc')->_('删除成功');
        return $this->splash('success',$url,$msg);
    }

    /**
     * @brief 安全中心
     *
     * @return html
     */
    public function security()
    {
        $input = input::get();
        $userId = userAuth::id();
        //会员信息
        $userInfo = userAuth::getUserInfo();
        // 判断来源
        if(isset($input['payment_id']))
        {
            cache::store('session')->put($this->cachePaymentIdKey.'-'.$userId, $input['payment_id'], 30);
        }
        $pagedata['userInfo'] = $userInfo;
        $pagedata['hasDepositPassword'] = app::get('topc')->rpcCall('user.deposit.password.has', ['user_id'=>$userId]);
        $pagedata['action']= 'topc_ctl_member@security';
        $this->action_view = "security.html";
        return $this->output($pagedata);
    }
    /**
     * @brief 会员中心安全中心密码修改
     *
     * @return html
     */
    public function modifyPwd()
    {
        $pagedata['action']= 'topc_ctl_member@modifyPwd';
        $this->action_view = "modifypwd.html";
        return $this->output($pagedata);
    }
    /**
     * @brief 会员中心安全中心密码修改保存
     *
     * @return html
     */
    public function saveModifyPwd()
    {
        try{
            $userId = userAuth::id();
            $postData = input::get();
            $validator = validator::make(
                ['oldpassword' => $postData['old_password'] ,'password' => $postData['new_password'] , 'password_confirmation' =>$postData['confirm_password']],
                ['oldpassword' => 'required' ,'password' => 'min:6|max:20|confirmed','password_confirmation' =>'required'],
                ['oldpassword' => '老密码不能为空！' ,'password' => '密码长度不能小于6位!|密码长度不能大于20位!|输入的密码不一致!','password_confirmation' =>'确认密码不能为空!']
            );
            if ($validator->fails())
            {
                $messages = $validator->messagesInfo();
                foreach( $messages as $error )
                {
                    return $this->splash('error',null,$error[0]);
                }
            }
            $data = array(
                'new_pwd' => $postData['new_password'],
                'confirm_pwd' => $postData['confirm_password'],
                'old_pwd' => $postData['old_password'],
                'user_id' => $userId,
                'type' => "update",
            );
            app::get('topc')->rpcCall('user.pwd.update',$data,'buyer');
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        //同时修改商家帐号密码
        $userMdlObj = app::get('sysuser')->model("account");
        $filter = ['user_id' => $userId];
        $tmpRow = $userMdlObj->getRow("mobile" , $filter);
        $accountMobile = $tmpRow['mobile'];
        if(!empty($accountMobile)){
            $filter = ['login_account' => $accountMobile];
            $data = ['login_password' => hash::make($postData['new_password'])];
            app::get('sysshop')->model("account")->update($data , $filter);
        }//end of 修改商家密码

        $url = url::action("topc_ctl_member@security");
        $msg = app::get('topc')->_('修改成功');

        return $this->splash('success',$url,$msg);
    }
    /**
     * @brief 会员信息设置
     *
     * @return html
     */
    public function seInfoSet()
    {
        $userId = userAuth::id();
        //会员信息
        $userInfo = userAuth::getUserInfo();
        list($regions,$region_id) = explode(':', $userInfo['area']);
        $userInfo['area'] = $regions;
        $userInfo['region_id'] = str_replace('/', ',', $region_id);
        $pagedata['userInfo'] = $userInfo;
        $pagedata['action']= 'topc_ctl_member@seInfoSet';

        $this->action_view = "infoset.html";
        return $this->output($pagedata);
    }
    /**
     * @brief 会员信息设置保存
     *
     * @return html
     */
    public function saveInfoSet()
    {
        $userId = userAuth::id();
        $postData = input::get('user');
        $postData['user_id'] = $userId;
        $areas = input::get('area');
        $area = app::get('topc')->rpcCall('logistics.area',array('area'=>$areas[0]));

        $validator = validator::make(
            ['name' => $postData['name'] ,
             'username' => $postData['username'],
             'user_id' => $postData['user_id'],
             'area' => $area
            ],
            ['name' => 'required|min:4|max:20|regex:/^[\w\-\x{4E00}-\x{9FA5}]*$/ui' ,
            'username' => 'required|max:20',
            'user_id' => 'required',
            'area' => 'required'
            ],
            ['name' => '用户昵称不能为空!|用户昵称最少4个字符!|用户昵最多20个字符!|用户昵称不能包含特殊符号！' ,
             'username' => '用户姓名不能为空!|用户姓名过长,请输入20个英文或10个汉字!',
             'user_id' => '您还没有登陆，请先登陆!',
             'area' => '地区不存在!'
            ]
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();

            foreach( $messages as $error )
            {
                return $this->splash('error',null,$error[0]);
            }
        }

        $areaId =  str_replace(",","/", $areas[0]);
        $postData['area'] = $area . ':' . $areaId;
        try
        {
            $data = array('user_id'=>$userId,'data'=>json_encode($postData));
            $result = app::get('topc')->rpcCall('user.basics.update',$data,'buyer');
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        $url = url::action('topc_ctl_member@seInfoSet');
        $msg = app::get('topc')->_('修改成功');
        return $this->splash('success',$url,$msg);

    }

    /**
     * @brief 解绑第一步
     *
     * @return html
     */
    /*public function unVerifyOne()
    {
        $postData = utils::_filter_input(input::get());

        $userId = userAuth::id();
        //会员信息
        $userInfo = userAuth::getUserInfo();

        $pagedata['userInfo']= $userInfo;
        $pagedata['verifyType']= $postData['verifyType'];
        $pagedata['type']= $postData['type'];
        $this->action_view = "unverify.html";
        return $this->output($pagedata);
    }*/
    //解绑的验证码检测
    /*public function checkVcode()
    {
        $postData =utils::_filter_input(input::get());
        if(empty($postData['verifycode']) || !base_vcode::verify('topc_unverify', $postData['verifycode']))
        {
            $msg = app::get('topc')->_('验证码填写错误') ;
            return $this->splash('error',null,$msg,true);
        }
        $verifyType = $postData['verifyType'];
        $url = url::action("topc_ctl_member@unVerifyTwo",array('verifyType'=>$verifyType,'op'=>$postData['type']));
        return $this->splash('success',$url,null);

    }*/
    /**
     * @brief 解绑第二步
     *
     * @return html
     */
    public function unVerifyTwo()
    {
        //会员信息
        $userInfo = userAuth::getUserInfo();
        $postdata = input::get();

        if($postdata['op'] == "delete" && !$userInfo['login_account'] && $postdata['verifyType']=='mobile')
        {
            return redirect::action('topc_ctl_member@pwdSet');
        }
        $pagedata['userInfo'] = $userInfo;
        $pagedata['op'] = $postdata['op'];
        $pagedata['verifyType']= $postdata['verifyType'];
        if($postdata['verifyType']=='email'&&$userInfo['email_verify'])
        {
            $this->action_view = "unemail.html";
        }
        elseif($postdata['verifyType']=='mobile'&&$userInfo['mobile'])
        {
            $this->action_view = "unmobile.html";
        }
        else
        {
            $msg = app::get('topc')->_('参数错误');
            return $this->splash('error',$url,$msg);
        }
        return $this->output($pagedata);
    }

    //解绑mobile
    public function unVerifyMobile()
    {
        $postData = input::get();
        $sendType = $postData['verifyType'];
        $userId = userAuth::id();
        try
        {
            if(!userVcode::verify($postData['vcode'],$postData['uname'],$postData['type']))
            {
                throw new \LogicException(app::get('topc')->_('验证码错误'));
            }

            $data['user_id'] = $userId;
            $data['user_name'] = $postData['uname'];
            $data['type'] = $postData['op'];
            app::get('topc')->rpcCall('user.account.update',$data,'buyer');
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        $url = url::action("topc_ctl_member@unVerifyLast",array('sendType'=>$sendType));
        return $this->splash('success',$url,null);
    }

    //绑定邮箱
    public function unVerifyEmail()
    {
        $postData = input::get();
        $userId = userAuth::id();
        try
        {
            if(md5($userId) != $postData['verify'])
            {
                throw new \LogicException(app::get('topc')->_('用户不一致！'));
            }
            if(!userVcode::verify($postData['vcode'],$postData['uname'],$postData['type']))
            {
                throw new \LogicException(app::get('topc')->_('验证码错误'));
            }

            $data['user_id'] = $userId;
            $data['user_name'] = $postData['uname'];
            $data['type'] = 'delete';
            app::get('topc')->rpcCall('user.account.update',$data,'buyer');
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        $pagedata['sendType']= 'email';
        $pagedata['action']= 'topc_ctl_member@unVerifyEmail';
        $this->action_view = "unverifylast.html";
        return $this->output($pagedata);

    }

    /**
     * @brief 解绑最后一步
     *
     * @return html
     */
    public function unVerifyLast()
    {
        $sendType = input::get();
        $pagedata['sendType']= $sendType;
        $pagedata['action']= 'topc_ctl_member@unVerifyLast';
        $this->action_view = "unverifylast.html";
        return $this->output($pagedata);
    }


    /**
     * @brief 绑定第一步
     *
     * @return html
     */
    public function verify()
    {

        $postData = input::get();

        $userId = userAuth::id();
        //会员信息
        $userInfo = userAuth::getUserInfo();

        $pagedata['userInfo']= $userInfo;
        $pagedata['verifyType']= in_array($postData['verifyType'],$this->verifyType)?$postData['verifyType']:$this->verifyType[0];
        $pagedata['type']= in_array($postData['type'],$this->sendType)?$postData['type']:'';
        $this->action_view = "verify.html";
        //echo '<pre>';print_r($pagedata);exit();
        return $this->output($pagedata);
    }
    /**
     * @brief 验证登陆密码
     *
     * @return html
     */
    public function CheckSetInfo()
    {
        $postData =input::get();
        $validator = validator::make(
            ['password' => $postData['password']],
            ['password' => 'required'],
            ['password' => '密码不能为空!']
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                return $this->splash('error',null,$error[0]);
            }
        }
        $data['password'] = $postData['password'];
        try
        {
            app::get('topc')->rpcCall('user.login.pwd.check',$data,'buyer');
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        $verifyType = $postData['verifyType'];
        $type = $postData['type'];
        $url = url::action("topc_ctl_member@setUserInfoOne",array('verifyType'=>$verifyType,'type'=>$type));

        return $this->splash('success',$url,null);
    }

    /**
     * @brief 验证第二步
     *
     * @return html
     */
    public function setUserInfoOne()
    {
        //会员信息
        $userInfo = userAuth::getUserInfo();
        $postdata = input::get();
        $pagedata['type'] = $postdata['type'];

        if($postdata['type'] && $postdata['type'] = "update" && !$userInfo['login_account'])
        {
            $msg = app::get('topc')->_('您还没有设置用户名，请前往设置用户名!');
            return $this->splash('error',$url,$msg);
        }

        $pagedata['userInfo']= $userInfo;
        $pagedata['verifyType']= $postdata['verifyType'];
        if($postdata['verifyType']=='email')
        {
            $this->action_view = "emailfirst.html";
        }
        elseif($postdata['verifyType']=='mobile')
        {
            $this->action_view = "mobilefirst.html";
        }
        else
        {
            $msg = app::get('topc')->_('参数错误');
            return $this->splash('error',$url,$msg);
        }

        return $this->output($pagedata);
    }

    //绑定mobile
    public function bindMobile()
    {
        $postData = input::get();
        $sendType = $postData['verifyType'];
        $postData['user_id'] = userAuth::id();
        try
        {
            if(!userVcode::verify($postData['vcode'],$postData['uname'],$postData['type']))
            {
                throw new \LogicException(app::get('topc')->_('验证码错误'));
            }

            $data['user_id'] = $postData['user_id'];
            $data['user_name'] = $postData['uname'];
            app::get('topc')->rpcCall('user.account.update',$data,'buyer');
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        $url = url::action("topc_ctl_member@setUserInfoLast",array('sendType'=>$sendType));
        return $this->splash('success',$url,null);
    }

    //绑定邮箱
    public function bindEmail()
    {
        $postData = input::get();
        $userId = userAuth::id();
        try
        {
            if(md5($userId) != $postData['verify'])
            {
                throw new \LogicException(app::get('topc')->_('用户不一致！'));
            }
            if(!userVcode::verify($postData['vcode'],$postData['uname'],$postData['type']))
            {
                throw new \LogicException(app::get('topc')->_('验证码错误'));
                return false;
            }

            $data['user_id'] = $userId;
            $data['email'] = $postData['uname'];
            app::get('topc')->rpcCall('user.email.verify',$data,'buyer');
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        $pagedata['sendType']= 'email';
        $pagedata['action']= 'topc_ctl_member@bindEmail';
        $this->action_view = "setinfolast.html";
        return $this->output($pagedata);
    }

    /**
     * @brief 发送短信验证码
     *
     * @return html
     */
    public function sendVcode()
    {
        $postData = input::get();
        if(isset($postData['imagevcode']))
        {
            $valid = validator::make(
                [$postData['imagevcode']],['required']
            );
            if($valid->fails())
            {
                return $this->splash('error',null,"图片验证码不能为空!");
            }
            if(!base_vcode::verify($postData['imagevcodekey'],$postData['imagevcode']))
            {
                return $this->splash('error',null,"图片验证码错误!");
            }

        }
        //echo '<pre>';print_r($postData);exit();
        if($postData['verifyType'] == "email")
        {
            $validator = validator::make(
                [$postData['uname']],['required|email'],['您的邮箱号不能为空!|邮箱号格式不对!']
            );
            if ($validator->fails())
            {
                $messages = $validator->messagesInfo();

                foreach( $messages as $error )
                {
                    return $this->splash('error',null,$error[0]);
                }
            }
        }
        if($postData['verifyType'] == "mobile")
        {
            $validator = validator::make(
                [$postData['uname']],['required|mobile'],['您的手机号不能为空!|手机号格式不对!']
            );
            if ($validator->fails())
            {
                $messages = $validator->messagesInfo();
                foreach( $messages as $error )
                {
                    return $this->splash('error',null,$error[0]);
                }
            }
        }
        try
        {
            $this->passport->sendVcode($postData['uname'],$postData['type']);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        //$accountType = kernel::single('pam_tools')->checkLoginNameType($postData['uname']);
        $accountType = app::get('topc')->rpcCall('user.get.account.type',array('user_name'=>$postData['uname']),'buyer');
        if($accountType == "email")
        {
            return $this->splash('success',null,"邮箱验证链接已经发送至邮箱，请登录邮箱验证");
        }
        else
        {
            return $this->splash('success',null,"验证码发送成功");
        }
    }
    /**
     * @brief 验证最后一步
     *
     * @return html
     */
    public function setUserInfoLast()
    {
        $sendType = input::get();
        $pagedata['sendType']= $sendType;
        $pagedata['action']= 'topc_ctl_member@setUserInfoLast';
        $this->action_view = "setinfolast.html";
        return $this->output($pagedata);
    }


    public function shopsCollect()
    {
        $filter = input::get();
        if(!$filter['pages'])
        {
            $filter['pages'] = 1;
        }
        $pageSize = 18;
        $params = array(
            'page_no' =>  $filter['pages'],
            'page_size' => $pageSize,
            'fields' =>'*',
            'user_id'=>userAuth::id(),
        );
        $favData = app::get('topc')->rpcCall('user.shopcollect.list',$params,'buyer');
        $count = $favData['shopcount'];
        $favList = $favData['shopcollect'];
        foreach ($favList as &$v)
        {
            // 获取店铺子域名
            $v['subdomain'] = app::get('topc')->rpcCall('shop.subdomain.get',array('shop_id'=>$v['shop_id']))['subdomain'];
        }

        //处理翻页数据
        $current = $filter['pages'] ? $filter['pages'] : 1;
        $filter['pages'] = time();
        if($count>0) $total = ceil($count/$pageSize);
        $pagedata['pagers'] = array(
            'link'=>url::action('topc_ctl_member@shopsCollect',$filter),
            'current'=>$current,
            'total'=>$total,
            'token'=>$filter['pages'],
        );
        $pagedata['favshop_info']= $favList;
        $pagedata['count'] = $count;
        $pagedata['action']= 'topc_ctl_member@shopsCollect';


        $this->action_view = "shops.html";
        return $this->output($pagedata);
    }

    public function itemsCollect()
    {
        $filter = input::get();
        if(!$filter['pages'])
        {
            $filter['pages'] = 1;
        }
        $pageSize = 18;
        $params = array(
            'page_no' => $filter['pages'],
            'page_size' => $pageSize,
            'fields' =>'*',
            'user_id'=>userAuth::id(),
            'cat_id'=>$filter['cat_id'],
        );
        if( $filter['cat_id'] )
        {
            $pagedata['catId'] = $filter['cat_id'];
        }
        $favData = app::get('topc')->rpcCall('user.itemcollect.list',$params,'buyer');
        $count = $favData['itemcount'];
        $favList = $favData['itemcollect'];

        //获取类目
        $catInfo = $this->getCatInfo();
        //处理翻页数据

        if($count>0) $total = ceil($count/$pageSize);
        $filter['pages'] = $filter['pages'] > $total ?  $total : $filter['pages'];
        $current = $filter['pages'] ? $filter['pages'] : 1;
        $filter['pages'] = time();
        $pagedata['pagers'] = array(
            'link'=>url::action('topc_ctl_member@itemsCollect',$filter),
            'current'=>$current,
            'total'=>$total,
            'token'=>$filter['pages'],
        );
        $pagedata['fav_info']= $favList;
        $pagedata['catInfo']= $catInfo;
        $pagedata['count'] = $count;
        $pagedata['action']= 'topc_ctl_member@itemsCollect';
        $this->action_view = "items.html";
        return $this->output($pagedata);
    }

    public function getCatInfo()
    {
        $params = array(
            'user_id'=>userAuth::id(),
        );

        $favData = app::get('topc')->rpcCall('user.itemcollect.list',$params, 'buyer');
        $infoList = $favData['itemcollect'];

        if(!$infoList) return "";

        foreach ($infoList as $key => $value)
        {
            $catId[] = $value['cat_id'];
        }

        $catNum = array_count_values($catId);
        $catInfo = app::get('topc')->rpcCall('category.cat.get.info',array('cat_id'=>implode(',',$catId),'fields'=>'cat_id,cat_name'),'buyer');
        foreach($catInfo as $k=>$val)
        {
            $catName[$k]['num'] = $catNum[$k];
            $catName[$k]['cat_id'] = $val['cat_id'];
            $catName[$k]['cat_name'] = $val['cat_name'];
        }
        return $catName;
    }

    /**
     * 信任登陆用户名密码设置
     */
    public function pwdSet()
    {
        //会员信息
        $userInfo = userAuth::getUserInfo();
        $pagedata['userInfo'] = $userInfo;
        $pagedata['action'] = 'topc_ctl_member@pwdSet';
        $this->action_view = "pwdset.html";
        return $this->output($pagedata);
    }
    /**
     * 信任登陆用户名密码设置
     */
    public function savePwdSet()
    {
        $postData = input::get();

        $userId = userAuth::id();
        //会员信息
        $userInfo = userAuth::getUserInfo();
        $url = url::action("topc_ctl_member@pwdSet");
        if($userInfo['login_type']=='trustlogin')
        {
            try
            {
                $this->__checkAccount($postData['username']);
                $data = array(
                    'new_pwd' => $postData['new_password'],
                    'confirm_pwd' => $postData['confirm_password'],
                    'old_pwd' => $postData['old_password'],
                    'uname' => $postData['username'],
                    'user_id' => $userId,
                    'type' => ($userInfo['login_type']=='trustlogin') ? "reset" : "update",
                );
                app::get('topc')->rpcCall('user.pwd.update',$data,'buyer');
            }
            catch(\Exception $e)
            {
                $msg = $e->getMessage();
                return $this->splash('error',null,$msg,true);
            }
        }
        else
        {
            try
            {
                $this->__checkAccount($postData['username']);
                $data = array(
                    'user_name'   => $postData['username'],
                    'user_id' => $userId,
                );
                app::get('topc')->rpcCall('user.account.update',$data,'buyer');
            }
            catch(\Exception $e)
            {
                $msg = $e->getMessage();
                return $this->splash('error',null,$msg,true);
            }
        }

        return $this->splash('success',$url,app::get('topc')->_('修改成功'),true);
    }

    private function __checkAccount($username)
    {

        $validator = validator::make(
            ['username' => $username],
            ['username' => 'loginaccount|required|max:20'],
            ['username' => '用户名不能为纯数字或邮箱地址!|用户名不能为空!|用户名最长为20个字符!']
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                throw new LogicException( $error[0] );
            }
        }
        return true;
    }

    /**
     *  会员签到
     */
    public function checkin(){
        try
        {
            $params = array(
                'user_id' => userAuth::id(),
            );
            app::get('topc')->rpcCall('user.add.checkin.log',$params);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg,true);
        }

        return $this->splash('success','',app::get('topc')->_('签到成功'),true);
    }

    #----------------- 公司资料 -------------------------
    //会员中心公司资料展示页面
    public function seInfoCompanydisplay() {
        $userId = userAuth::id();
        if (!$userId) {
            return redirect::action('topc_ctl_passport@login');
        }
        $companyInfo = app::get('sysuser')->model('user')->getRow('user_type,verify_status',['user_id' => $userId]);
        if ($companyInfo['user_type'] != '1' || $companyInfo['verify_status'] != '1') {
            return redirect::action('topc_ctl_passport@companysignup');
        }
        $sysMdlcompany = app::get('sysuser')->model('user_company');
        $user_company_Info = $sysMdlcompany->getRow('*', array('user_id' => $userId));
        if ($user_company_Info) {
            list($regions, $region_id) = explode(':', $user_company_Info['company_area']);
            $user_company_Info['area'] = $regions;
            $user_company_Info['region_id'] = str_replace('/', ',', $region_id);

            $company_area=$user_company_Info['company_area'] ;
            if( $company_area )
            {
                $area = '';
                $re=explode(':',$company_area);
                $data['area_id']  = str_replace('/', ',', $re[1]);
                foreach( (array)explode(',',$data['area_id']) as $areaId)
                {
                    $res = area::areaKvdata($areaId);
                    $area .= $res['value'].' ';
                }
                $user_company_Info['company_area'] = $area;
            }

            $pagedata['userInfo'] = $user_company_Info;
            $pagedata['action'] = 'topc_ctl_member@seInfoCompanydisplay';
            $this->action_view = "seInfoCompanydisplay.html";
            return $this->output($pagedata);
        } else {
            $pagedata['userInfo'] = '';
            $pagedata['action'] = 'topc_ctl_member@seInfoCompanydisplay';
            $this->action_view = "seInfoCompanydisplay01.html";
            return $this->output($pagedata);
        }
    }

    //会员中心公司资料填写页面
    public function seInfoCompany() {
        $userId = userAuth::id();

        //查询会员公司信息
        $sysMdlcompany = app::get('sysuser')->model('user_company');
        $user_company_Info = $sysMdlcompany->getRow('*', array('user_id' => $userId));
        //地址的处理
        list($regions, $region_id) = explode(':', $user_company_Info['company_area']);
        $user_company_Info['area'] = $regions;
        $user_company_Info['region_id'] = str_replace('/', ',', $region_id);


        $pagedata['userInfo'] = $user_company_Info;
        $pagedata['action'] = 'topc_ctl_member@seInfoCompany';
        $this->action_view = "seInfoCompany.html";
        return $this->output($pagedata);
    }

    //会员中心公司资料保存
    public function saveInfoCompany() {
        $userId = userAuth::id();
        $postData = utils::_filter_input(input::get());
        $postData['user']['user_id'] = $userId;

        $postData['area'] = input::get()['area'][0];
        $area = app::get('topc')->rpcCall('logistics.area',array('area'=>$postData['area']));

        if($area)
        {
            $areaId =  str_replace(",","/", $postData['area']);
            $postData['area'] = $area . ':' . $areaId;
        }
        else
        {
            $msg = '地区不存在!';
            return $this->splash('error',null,$msg);
        }


        $data = array(
            'user_id' => $userId,
            'company_name' => $postData['user']['company_name'],
            'company_area' => $postData['area'],
            'company_addr' => $postData['user']['addr'],
            'operating_range' => $postData['user']['operating_range'],
            'company_num' => $postData['user']['company_num'],
            'company_registered' => $postData['user']['company_registered'],
            'company_turnover' => $postData['user']['company_turnover'],
            'def_addr' => $postData['user']['def_addr'],
            'company_phone' => $postData['user']['company_phone'],
            'company_contact'=>$postData['user']['company_contact'],
            'company_contactPhone'=>$postData['user']['company_contactPhone'],
            'updatetime' => time(),
            'invoice_bank_name'=>$postData['user']['invoice_bank_name'],
            'invoice_bank_num'=>$postData['user']['invoice_bank_num'],
            'invoice_addr'=>$postData['user']['invoice_addr'],
            'invoice_mobile'=>$postData['user']['invoice_mobile'],
        );

        try {

            $sysMdlcompany = app::get('sysuser')->model('user_company');

            $company = $sysMdlcompany->getRow('*', array('user_id' => $userId));

            //查询是否已经在表中添加过数据
            if ($company) {
                $result = $sysMdlcompany->update($data, array('user_id' => $userId));
            } else {
                $result = $sysMdlcompany->save($data);
            }
            kernel::single('sysuser_data_user_credit')->setCreditPoint($data, 'sysuser_user_company');
        } catch (Exception $e) {
            $msg = $e->getMessage();
            return $this->splash('error', null, $msg);
        }
        if ($result) {
            $url = url::action('topc_ctl_member@seInfoCompanydisplay');
            $msg = app::get('topc')->_('保存成功!');
        } else {
            $url = url::action('topc_ctl_member@seInfoCompany');
            $msg = app::get('topc')->_('保存失败!');
        }


        return $this->splash('success', $url, $msg);
    }

    //会员中心公司资料展示页面
    public function temaiapply() {
        $pagedata = array();
        $this->action_view = "tmsign.html";

        $pagedata['isCompanyRoles'] = true;
        //不是企业会员直接弹出错误提示
        $userId = userAuth::id();
        $roles = userAuth::getUserRoles($userId);
        if(! isset($roles['roles'][SYS_USER_ROLE_COMPANY]['name'])){
            $pagedata['isCompanyRoles'] = false;
        }

        $userId = userAuth::id();
        $temaiapplyMdlObj = app::get("systemai")->model("temaiapply");
        $filter = array("user_id" => $userId);
        $applyInfo = $temaiapplyMdlObj->getRow("*" , $filter);


        $pagedata['license'] = app::get("site")->getConf("temai.usage_agreement");
        $pagedata['applyInfo'] = $applyInfo;

        return $this->output($pagedata);
    }

    public function temaisavelicense()
    {
        $userId = userAuth::id();
        $timeNow = time();
        $post = input::get();
        $temaiapplyMdlObj = app::get("systemai")->model("temaiapply");
        $filter = array(
            'user_id' => $userId
        );

        if(empty($post['server_mobile'])){
            $msg = '请填写联系人手机号!';
            return $this->splash('error','', $msg);
        }else if(empty($post['server_desc'])){
            $msg = '请填写申请原因!';
            return $this->splash('error','', $msg);
        }

        $data  = array(
            'user_id' => $userId,
            'server_name' => '',
            'server_cert' => '',
            'server_mobile' => $post['server_mobile'],
            'server_desc' => $post['server_desc'],
            'status' => '0',
            'createtime' => $timeNow,
            'modified_time' => $timeNow,
        );
        $temaiInfo = $temaiapplyMdlObj->getRow("temai_server_id" , $filter);
        if($temaiInfo['temai_server_id'] > 0){
            unset($data['createtime']);
            $temaiapplyMdlObj->update($data , $filter);
            $msg = '申请已提交，预计需要1，请耐心等待。';
            $url = url::action("toptemai_ctl_index@index");
            return $this->splash('success',$url, $msg);
        }

        $temaiServerId = $temaiapplyMdlObj->insert($data);
        if($temaiServerId){
            $msg = '申请已提交，预计需要1个工作日，请耐心等待。';
            $url = url::action("toptemai_ctl_index@index");
            return $this->splash('success', $url, $msg);
        }

        $msg = '申请失败,请联系我们客服!';
        $url = '';
        return $this->splash('error', '', $msg);

        //企业会员，增加平台展销标识
        //$rs = userAuth::setUserRoles($this->userId ,  SYS_USER_ROLE_SPECICAL , '+');
    }

	//开通OMS | 及跳转Oms
    public function oms() {
        //是否已开通
		$username = userAuth::getLoginName(userAuth::id());
        $isOpen = kernel::single('oms_api')->isOpenOMS($username);

		//已开通，同步跳转、登陆oms
		if($isOpen) {
			$url = kernel::single('oms_api')->ssoLogin();
			header('Location:' . $url);
			exit;
		}

		//没开通进入开通页面
        $this->action_view = route::url('topc_ctl_member@oms');

       /*
		$pagedata['isCompanyRoles'] = true;
        $userId = userAuth::id();
        $roles = userAuth::getUserRoles($userId);
        if(! isset($roles['roles'][SYS_USER_ROLE_COMPANY]['name'])) {
            $pagedata['isCompanyRoles'] = false;
        }
	   */

		$pagedata['isCompanyRoles'] = userAuth::isCompany();

        return $this->output($pagedata);
    }

}


