<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topc_ctl_member_proofing extends topc_ctl_member {

    public $provider_id;
    public $shopId;
    public $sellerId;
    public $user_id;
    private $__adjust_status = array(
        'WAIT_SENDCONTRACT',//待发起合同
        'WAIT_CONFIRM',	//等待买家确认合同
        'REJECT_CONTRACT',	//买家拒绝合同
        'WAIT_BUYER_PAY',	//等待买家付款
        'WAIT_SELLER_SEND_GOODS',//货到付款情况下在此订单状态才能付款
    );

    public function __construct(&$app)
    {
        parent::__construct();
        kernel::single('base_session')->start();
        $this->userId = userAuth::id();

        $proInfo = app::get('sysproofing')->model('provider')->getRow('provider_id',['user_id' => $this->userId, 'enabled' => '1']);
        if ($proInfo) {
            $this->provider_id = $proInfo['provider_id'];
            $this->shopId = '999999'.$this->provider_id;
            $this->sellerId = '999999'.$this->provider_id;
        } else {
            return redirect::action('topc_ctl_proofing@update')->send();exit;
        }
    }

    //订单详情
    public function orderDetail()
    {
        $tids = input::get('tid');
        //面包屑
        $this->runtimePath = array(
            ['url'=> url::action('topshop_ctl_index@index'),'title' => app::get('topshop')->_('首页')],
            ['url'=> url::action('topshop_ctl_trade_list@index'),'title' => app::get('topshop')->_('订单列表')],
            ['title' => app::get('topshop')->_('订单详情')],
        );

        $params['tid'] = $tids;
        // adjust_fee,
        $params['fields'] = "trade_from,isconfirm_post_fee,isconfirm_adjust_fee,modified_time,shipping_type,delivery_goods_time,dlytmpl_id,receiver_phone,orders.spec_nature_info,orders.sku_id,user_id,tid,status,payment,points_fee,ziti_addr,ziti_memo,post_fee,pay_type,payed_fee,receiver_state,receiver_city,receiver_district,receiver_address,receiver_zip,trade_memo,shop_memo,receiver_name,receiver_mobile,orders.price,orders.num,orders.title,orders.item_id,orders.pic_path,total_fee,discount_fee,buyer_rate,orders.total_fee,orders.adjust_fee,created_time,pay_time,consign_time,end_time,shop_id,need_invoice,invoice_name,invoice_type,invoice_main,invoice_vat_main,invoice_tfn,invoice_bank_name,invoice_bank_num,invoice_addr,invoice_mobile,orders.bn,cancel_reason,update_status,need_econtract,contract_status,contract_id,position,orders.refund_fee,orders.aftersales_status,orders.gift_data";
        $tradeInfo = app::get('topshop')->rpcCall('trade.get',$params,'seller');
        if($tradeInfo['shipping_type'] == 'ziti')
        {
            $pagedata['ziti'] = "true";
        }//echo "<Pre>";print_r($tradeInfo);exit;

        if(!$tradeInfo)
        {
            redirect::action('topshop_ctl_trade_list@index')->send();exit;
        }
        /*增加电子合同状态的显示 edit by nie 2016-1-17*/
        $tradeInfo['unfeePayment'] = $tradeInfo['payment'];
        $objMdlelecontract = app::get('systrade')->model('elecontract');
        $elecontract = $objMdlelecontract->getRow('*',array('tid' =>$tids));
        if($elecontract){
            $payment = $tradeInfo['payment'];
            $elecontract['signed_time'] =  date("Y-m-d",$elecontract['signed_time']);
            //$elecontract['pay_type'] = unserialize($elecontract['pay_type']);
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
            $tradeInfo['elecontract'] = $elecontract;
        }
        /*增加电子合同状态的显示 edit by nie 2016-1-17*/
        $contentObj = app::get('systrade')->model('contract');
        $tradeInfo['contact_img'] = $contentObj->getRow('contract_img',array('contract_id'=>$tradeInfo['contract_id']))['contract_img'];

        if(!empty($tradeInfo['contact_img'])) {
            $pagedata['contact_img'] = 1;
        }else{
            $pagedata['contact_img'] = 0;
        }
        //商品发送时间
        if($tradeInfo['delivery_goods_time']){
            $tradeInfo['delivery_goods_time'] = date("Y-m-d ", $tradeInfo['delivery_goods_time']);
        }
        $tradeInfo['cny_payment'] = $this->num_to_rmb($tradeInfo['unfeePayment']);
        $shop_id = $tradeInfo['shop_id'];
//        $objMdlshopinfo = app::get('sysshop')->model('shop_info');
//        $objMdlitem = app::get('sysitem')->model('item');
//        $objMdlbrand = app::get('syscategory')->model('brand');
//        $objMdlsku = app::get('sysitem')->model('sku');
        $providerMdl = app::get('sysproofing')->model('provider');
        $sampleMdl = app::get('sysproofing')->model('sample');
        $offerMdl = app::get('sysproofing')->model('offer');
        $provider_id = substr($shop_id,6);
        $providerInfo = $providerMdl->getRow('provider_name',array('provider_id' =>$provider_id));
        $order_adjust_fee = 0;
        foreach($tradeInfo['orders'] as $key =>$value){
            $order_adjust_fee += $value['adjust_fee']*$value['num'];
            $item_id = $value['item_id'];
            $sample_id = substr($item_id,6);
            $sample = $sampleMdl->getRow('sample_id,sample_name,unit,quantity',array('sample_id' =>$sample_id));
            $offer = $offerMdl->getRow('sample_fee,post_fee,offer_id',array('sample_id' =>$sample_id,'status' => '1'));
            $price = round($offer['sample_fee']/$sample['quantity'],2);
            $unit = $sample['unit'];
            $tradeInfo['orders'][$key]['unit'] = $unit;
            $tradeInfo['orders'][$key]['price'] = $price;
            $tradeInfo['orders'][$key]['adjust_fee'] = $value['adjust_fee'];
            $tradeInfo['orders'][$key]['payment'] = $value['payment'];
        }

        $userInfo = app::get('topshop')->rpcCall('user.get.account.name', ['user_id' => $tradeInfo['user_id']], 'seller');
        $tradeInfo['login_account'] = $userInfo[$tradeInfo['user_id']];
        $pagedata['company_name'] = $providerInfo['provider_name'];
        $tradeInfo['total_adjust_fee'] = $order_adjust_fee;

        //获取默认图片信息
        $pagedata['defaultImageId']= kernel::single('image_data_image')->getImageSetting('item');
        $pagedata['trade']= $tradeInfo;
        $pagedata['editable'] = in_array($tradeInfo['status'], array('WAIT_SENDCONTRACT', 'REJECT_CONTRACT', 'WAIT_CONFIRM'));

        /*
        ** 判断运费是否可调
        ** postfee_adjustable 表示邮费是否可调
        ** price_adjustable 表示单价是否可调
        */
        $postfee_adjustable = in_array($tradeInfo['status'], $this->__adjust_status);
        //货到付款在卖家发货状态下也能修改订单
        if( 'WAIT_SELLER_SEND_GOODS' == $tradeInfo['status']  && 'offline' != $tradeInfo['pay_type'] ){
            $postfee_adjustable = false;
        }
        //货到付款在买家付款状态下不能修改订单
        if ('WAIT_BUYER_PAY' == $tradeInfo['status'] && 'offline' == $tradeInfo['pay_type']) {
            $postfee_adjustable = false;
        }
        $price_adjustable = $postfee_adjustable;

        $pagedata['postfee_adjustable'] = $postfee_adjustable;
        $pagedata['price_adjustable'] = $price_adjustable;

        //物流信息处理
        $pagedata['logi'] = app::get('topshop')->rpcCall('delivery.get',array('tid'=>$params['tid']));
        $pagedata['tracking'] = app::get('syslogistics')->getConf('syslogistics.order.tracking');
        $this->contentHeaderTitle = app::get('topshop')->_('订单详情');

        //合同审核状态
        $Objtrade=app::get('systrade')->model('trade');
        $data=$Objtrade->getrow('*',array('tid'=>$pagedata['trade']['tid']));
        $contractMd = app::get('systrade')->model('contract');
        $filter = Array(
            'contacts',
            'phone',
            'contract_img',
            'contract_id',
            'shop_id'
        );
        $filte['user_id'] = $pagedata['trade']['user_id'];
        $pagedata['contract_status']=$data['contract_status'];
        $pagedata['checkup_status']=$data['checkup_status'];

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
                $tmp['amount'] = round($elecontract['pay_type']['num' . $ii],2);
                $payData[] = $tmp;
            }
        } else {
            $payinfo  = array(
                'times' => 1,
                'amount' => $tradeInfo['payment'],
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
        $pagedata['payData'] = $payData;

        //获取该订单的发货信息
        $objMdlDelivery = app::get('syslogistics')->model('delivery');
        $objMdlDeliveryDetail = app::get('syslogistics')->model('delivery_detail');
        $deliveryRes=$objMdlDelivery->getList('*',array('tid'=>$params['tid']));
        foreach ($deliveryRes as $dk=>$dv){
            $deliveryRes[$dk]['delivery_detail']=$objMdlDeliveryDetail->getList('*',array('delivery_id'=>$dv['delivery_id']));
            if ($dv['department']=='ecommerce') {
                $deliveryRes[$dk]['department']='电商运营部';
            }elseif ($dv['department']=='equipment') {
                $deliveryRes[$dk]['department']='制造业装备经营部';
            }else{
                $deliveryRes[$dk]['department']='';
            }
        }
        $pagedata['delivery']=$deliveryRes;
        $pagedata['time'] = time();
        $pagedata['contract']=$contractMd->getrow($filter,array('contract_id'=>$data['contract_id'],'shop_id'=>$shop_id));

        //电子合同
        $defaultEcontract = config::get('econtract');
        $pagedata['defaultEcontract'] = $defaultEcontract;

        $contract_list = unserialize($pagedata['contract']['contract_img']);
        unset($pagedata['contract']['contract_img']);
        foreach($contract_list as $row){
            $file_name =  explode('/',urldecode($row));
            $pagedata['contract']['contract_img'][] = array('title'=>end($file_name),'url'=>$row);
        }

        //获取物流公司列表
        $dlycorp = app::get('topshop')->rpcCall('shop.dlycorp.getlist',['shop_id'=>$this->shopId]);
        $pagedata['dlycorp'] = $dlycorp['list'];

        $this->action_view = "trade/detail.html";
        $pagedata['action'] = 'topc_ctl_member_proofing@orderDetail';
        return $this->output($pagedata);
    }

    //取消订单
    public function ajaxCloseTrade()
    {
        $pagedata['tid'] = input::get('tid');
        $pagedata['reason'] = config::get('tradeCancelReason');
        return view::make('topc/member/proofing/trade/cancel.html', $pagedata);
    }

    public function closeTrade()
    {
        $reasonSetting = config::get('tradeCancelReason');
        $reasonPost = input::get('cancel_reason');
        if($reasonPost == "other")
        {
            $cancelReason = input::get('other_reason');
            if(!$cancelReason)
            {
                return $this->splash('error',"",'其他取消原因必填',true);
            }
        }
        elseif(is_numeric($reasonPost))
        {
            $cancelReason = $reasonSetting['shopuser'][$reasonPost];
        }
        else
        {
            $cancelReason = $reasonPost;
        }

        $params['tid'] = input::get('tid');
        $params['shop_id'] = $this->shopId;
        $params['cancel_reason'] = $cancelReason;
        if(input::get('if_refund_post_fee'))
        {
            $params['return_freight'] =input::get('if_refund_post_fee');
        }
        $url = url::action('topc_ctl_member_proofing@tradeList');
        try
        {
            app::get('topshop')->rpcCall('trade.cancel',$params);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',"",$msg,true);
        }
        $msg = '取消成功';
        return $this->splash('success',$url,$msg,true);
    }

    //需求列表
    public function index()
    {
        $db = app::get('sysproofing')->database();
        $userId = userAuth::id();

        $hours = app::get('sysconf')->getConf('proofing.period');//推送间隔时间
        $num = app::get('sysconf')->getConf('proofing.num');//推送商家数量
        $pointInfo = app::get('sysuser')->model('user_credit')->getRow('proofing_trade',['user_id' => $userId]);
        if(!$pointInfo['proofing_trade']) $pointInfo['proofing_trade'] = 0;
        //查询服务商的分类排名
        $catInfo = app::get('sysproofing')->model('provider_cat')->getList('cat_id',['provider_id' => $this->provider_id]);
        foreach ($catInfo as $key => $cat) {
            $sql = 'SELECT count(*) as num FROM sysuser_user_credit AS c LEFT JOIN sysproofing_provider AS p ON c.user_id=p.user_id LEFT JOIN sysproofing_provider_cat AS r ON r.provider_id=p.provider_id WHERE c.proofing_trade>'.$pointInfo['proofing_trade'].' AND p.enabled=1 AND r.cat_id='.$cat['cat_id'];
            $res = $db->executeQuery($sql)->fetchAll();
            $catInfo[$key]['ranking'] = $res[0]['num'];
        }

        $page = input::get('pages') ? input::get('pages') : 1;

        $sql = 'SELECT s.*,r.user_name,r.start_time,r.end_time FROM sysproofing_sample AS s  LEFT JOIN sysproofing_requirement AS r ON r.requirement_id=s.requirement_id WHERE s.status=0 AND (';
        $catNum = count($catInfo);
        foreach ($catInfo as $key => $cat) {
            if ($cat['ranking'] < $num) {
                $time = time();
            } else {
                $time = time() - 3600 * $hours;
            }
            $sql .= '(r.start_time<'.$time.' AND s.cat_id='.$cat['cat_id'].')';
            if ($key != ($catNum - 1)) $sql .= ' OR ';
        }
        $sql .= ')';

        $samples = $db->executeQuery($sql)->fetchAll();
        if ($samples) {
            $count = count($samples);
            $sql .= " LIMIT ".$this->limit*($page-1).",".$this->limit;
            $samples = $db->executeQuery($sql)->fetchAll();
            //查询该服务商的所有报价
            $offers = app::get('sysproofing')->model('offer')->getList('offer_id, sample_id',['provider_id' => $this->provider_id]);
            foreach ($samples as $key => $sample) {
                foreach ($offers as $offer) {
                    if ($offer['sample_id'] == $sample['sample_id']) {
                        $samples[$key]['offer_id'] = $offer['offer_id'];
                    }
                }
            }
            $pagedata['samples'] = $samples;
            $pagedata['pagers'] = $this->__indexPager([],$page, $count);
        }

        $this->action_view = "index.html";
        $pagedata['action'] = 'topc_ctl_member_proofing@index';
        return $this->output($pagedata);
    }

    private function __indexPager($postFilter,$page,$count)
    {
        $postFilter['pages'] = time();
        $total = ceil($count/$this->limit);
        $pagers = array(
            'link'=>url::action('topc_ctl_member_proofing@index',$postFilter),
            'current'=>$page,
            'use_app' => 'topc',
            'total'=>$total,
            'token'=>time(),
        );
        return $pagers;

    }


    //报价
    public function offer()
    {
        $sample_id = input::get('sample_id');
        if (!$sample_id) return redirect::action('topc_ctl_member_proofing@index');
        $db = app::get('sysproofing')->database();
        $sql = 'SELECT s.*,r.user_name,r.start_time,r.end_time,r.addr FROM sysproofing_sample AS s LEFT JOIN sysproofing_requirement AS r ON s.requirement_id=r.requirement_id WHERE s.sample_id='.$sample_id.' AND s.status=0 AND r.start_time<'.time().' AND r.end_time>'.time();
        $samples = $db->executeQuery($sql)->fetchAll();
        if (!$samples) return redirect::action('topc_ctl_member_proofing@index');

        $sample = $samples[0];
        $cat = app::get('sysproofing')->model('category')->getRow('cat_name',['cat_id' => $sample['cat_id']]);
        $sample['cat_name'] = $cat['cat_name'];
        $pagedata['sample'] = $sample;
        $pagedata['sample']['drawing'] = $_SERVER['DOCUMENT_ROOT'] . '/images/drawing/' . $this->userId.'/'.$sample_id.'drawing.zip';
        //echo "<pre>";print_r($_SERVER);exit;
        $this->action_view = "offer.html";
        $pagedata['action'] = 'topc_ctl_member_proofing@offer';
        return $this->output($pagedata);
    }

    public function myOffer()
    {
        $offer_id = input::get('offer_id');
        if (!$offer_id) {
            return redirect::action('topc_ctl_member_proofing@index');
        }

        $db = app::get('sysproofing')->database();
        $sql = 'SELECT o.offer_id,o.sample_fee,o.post_fee,o.total_fee,o.post_type,o.pay_type,o.offer_delivery,o.params,o.status,'
            .'s.sample_id,s.cat_id,s.sample_name,s.quantity,s.unit,s.material,s.desc,s.drawing,s.delivery,s.pay_type as ptype,s.createtime,'
            .'r.user_name,r.start_time,r.end_time,r.addr'
            .' FROM sysproofing_sample AS s LEFT JOIN sysproofing_requirement AS r ON s.requirement_id=r.requirement_id LEFT JOIN sysproofing_offer AS o ON o.sample_id=s.sample_id WHERE o.offer_id='.$offer_id.' AND s.status=0 AND r.start_time<'.time().' AND r.end_time>'.time();
        $offers = $db->executeQuery($sql)->fetchAll();
        if (!$offers) {
            return redirect::action('topc_ctl_member_proofing@index');
        }
        $cat = app::get('sysproofing')->model('category')->getRow('cat_name',['cat_id' => $offers[0]['cat_id']]);
        $offers[0]['cat_name'] = $cat['cat_name'];
        $offers[0]['params'] = unserialize($offers[0]['params']);
        $pagedata['offer'] = $offers[0];
        $this->action_view = "myOffer.html";
        $pagedata['action'] = 'topc_ctl_member_proofing@offer';
        return $this->output($pagedata);
    }

    public function addOffer()
    {
        $params = utils::_filter_input(input::get());
        $flag = $this->_checkOffer($params);
        if ($flag) {
            return response::json($flag);exit;
        }
        if ($params['pay_type'] == 1) {
            if (!$params['params']['fee_3'] || $params['params']['fee_3'] == 0) {
                unset($params['params']['fee_3'] );
                unset($params['params']['time3'] );
            }
            if (!$params['params']['fee_2'] || $params['params']['fee_2'] == 0) {
                unset($params['params']['fee_2'] );
                unset($params['params']['time2'] );
            }
            if (!$params['params']['fee_1'] || $params['params']['fee_1'] == 0) {
                unset($params['params']['fee_1'] );
                unset($params['params']['time1'] );
            }
        }
        $offerMdl = app::get('sysproofing')->model('offer');
        $res = $offerMdl->saveOne($params, $this->provider_id);
        if ($res) {
            return response::json(['status' => 'success', 'url' => url::action('topc_ctl_member_proofing@index')]);exit;
        } else {
            return response::json(['status' => 'error', 'message' => '提交失败，请刷新页面重试！']);exit;
        }


    }

    private function _checkOffer($data)
    {
        $return['status'] = 'error';
        $validator = validator::make(
            [
                'sample_fee' => $data['sample_fee'],
                'post_fee' => $data['post_fee'],
            ],
            [
                'sample_fee' => 'required|Numeric',
                'post_fee' => 'required|Numeric',
            ],
            [
                'sample_fee' => '样品总价不能为空|样品总价必须为数字',
                'post_fee' => '运费不能为空|运费必须为数字',
            ]
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                $return['message'] = $error[0];
                return $return;
            }
        }

        if ($data['day'] == '0') {
            $return['message'] = '预计交货日期不能为空';
            return $return;
        }
        $time = strtotime($data['year'].'-'.$data['month'].'-'.$data['day']);
        if (time() > $time) {
            $return['message'] = '预计交货时间不能小于当前时间';
            return $return;
        }

        //分期付款检查
        if ($data['pay_type'] == '1') {
            if ($data['params']['fee_create'] + $data['params']['fee_confirm'] + $data['params']['fee_1'] + $data['params']['fee_2'] + $data['params']['fee_3'] != 100) {
                $return['message'] = '分期付款金额总和必须为100%';
                return $return;
            }
        }

        //检查是否已经报价过
        if (!$data['offer_id']) {
            $offer = app::get('sysproofing')->model('offer')->getRow('offer_id',['provider_id' => $this->provider_id, 'sample_id' => $data['sample_id']]);
            if ($offer) {
                $return['message'] = '该样品你已经报价过了';
                $return['url'] = url::action('topc_ctl_member_proofing@index');
                return $return;
            }
        }

        //检查是否在时间内
        $sql = "SELECT s.sample_id FROM sysproofing_sample AS s LEFT JOIN sysproofing_requirement AS r ON s.requirement_id = r.requirement_id WHERE s.sample_id=".$data['sample_id']. " AND r.start_time<".time()." AND r.end_time >".time();
        if (!app::get('sysproofing')->database()->executeQuery($sql)->fetchAll()) {
            $return['message'] = '该样品不在报价时间内!';
            return $return;
        }

        return false;
    }

    //下载图纸
    public function downloadDrawing()
    {
        $sample_id = input::get('sample_id');
        $sample = app::get('sysproofing')->model('sample')->getRow('drawing',['sample_id' => $sample_id]);
        $sample['drawing'] = unserialize($sample['drawing']);
        $file_name=$_SERVER['DOCUMENT_ROOT'] . '/images/drawing/' . $this->userId.'/'.$sample_id.'drawing.zip';
        //图纸打包
        if (!file_exists($file_name)) {
            $zip = new ZipArchive();
            if($zip->open($file_name,ZipArchive::CREATE)===TRUE){
                foreach ($sample['drawing'] as $loc) {
                    $zip->addFile($loc);//假设加入的文件名是image.txt，在当前路径下
                    unlink($loc);
                }
                $zip->close();
            }
        }

        header("Content-type:text/html;charset=utf-8");
        $file_name=iconv("utf-8","gb2312",$file_name);
        $fp=fopen($file_name,"r");
        $file_size=filesize($file_name);
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length:".$file_size);
        Header("Content-Disposition: attachment; filename=".$sample_id.'drawing.zip');
        $buffer=1024;
        $file_count=0;
        while(!feof($fp) && $file_count<$file_size){
            $file_con=fread($fp,$buffer);
            $file_count+=$buffer;
            echo $file_con;
        }
        fclose($fp);
    }

    //服务撮合订单
    public function tradeList()
    {
        $orderStatusList = array(
            '0' => '全部',
            '1' => '待支付',
            '2' => '待发货',
            '3' => '待收货',
            '4' => '已收货',
            '5' => '已取消',
        );

        $status = (int)input::get('status');
        $status = in_array($status, array_keys($orderStatusList)) ? $status : 0;

        $pagedata['status'] = $orderStatusList;
        $pagedata['filter']['status'] = $status;
        //$pagedata['shop_type'] = $this->shopInfo['shop_type'];
        $pagedata['useSessionFilter'] = input::get('useSessionFilter');
        $pagedata['settlement_status'] = array(
            '-1'=>'全部',
            '1'=>'普通结算',
            '2'=>'运费结算',
            '3'=>'售后结算',
            '4'=>'拒收结算',
        );
        //$this->contentHeaderTitle = app::get('topshop')->_('订单列表');
        $this->action_view = "trade/list.html";
        $pagedata['action'] = 'topc_ctl_member_proofing@tradeList';
        return $this->output($pagedata);
    }

    public function search()
    {
        $pagedata['progress'] = array(
            '0' => app::get('topshop')->_('待处理'),
            '1' => app::get('topshop')->_('待回寄'),
            '2' => app::get('topshop')->_('待确认收货'),
            '4' => app::get('topshop')->_('商家已处理'),//换货的时候可以直接在商家处理结束
            '3' => app::get('topshop')->_('商家已驳回'),
            '5' => app::get('topshop')->_('商家已收货'),
            '7' => app::get('topshop')->_('平台已退款'),//退款，退货则需要平台确实退款
            '6' => app::get('topshop')->_('平台已驳回'),
            '8' => app::get('topshop')->_('待平台退款'),
        );

        $tradeStatus = array(
            'WAIT_BUYER_PAY' => '未付款',
            'WAIT_SELLER_SEND_GOODS' => '已付款，请发货',
            'WAIT_BUYER_CONFIRM_GOODS' => '已发货，等待确认',
            'TRADE_FINISHED' => '已完成',
            'TRADE_CLOSED' => '已关闭',
            'TRADE_CLOSED_BY_SYSTEM' => '已关闭',
        );
        //$this->contentHeaderTitle = app::get('topshop')->_('订单查询');
        $postFilter = input::get();

        if($postFilter['useSessionFilter'])
        {
            $filter = $this->__getTradeSearchFilter();
        } else {
            $filter = $this->_checkParams($postFilter);
            $this->__saveTradeSearchFilter($filter);
        }

        $limit = $this->limit;
        $status = $filter['status'];
        if(is_array($filter['status']))
        {
            $status = implode(',',$filter['status']);
        }

        $page = $filter['pages'] ? $filter['pages'] : 1;
        $params = array(
            'status' => $status,
            'tid' => $filter['tid'],
            'create_time_start' =>$filter['created_time_start'],
            'create_time_end' =>$filter['created_time_end'],
            'receiver_mobile' =>$filter['receiver_mobile'],
            'receiver_phone' =>$filter['receiver_phone'],
            'receiver_name' =>$filter['receiver_name'],
            'user_name' =>$filter['user_name'],
            'pay_type' =>$filter['pay_type'],
            'shipping_type' =>$filter['shipping_type'],
            'settlement_status' =>$filter['settlement_status'],
            'page_no' => intval($page),
            'page_size' =>intval($limit),
            'order_by' =>'created_time desc',
            'fields' =>'order.spec_nature_info,shipping_type,tid,shop_id,user_id,status,settlement_status,payment,points_fee,total_fee,post_fee,payed_fee,receiver_name,trade_memo,created_time,receiver_mobile,discount_fee,adjust_fee,order.title,order.price,order.num,order.pic_path,order.tid,order.oid,order.item_id,need_invoice,invoice_name,invoice_type,invoice_main,pay_type,cancel_status,need_econtract,contract_status,contract_id,order.gift_data',
        );
        $params['order_type'] = 'proofing';

        //显示订单售后状态
        $params['is_aftersale'] = true;
        $params['shop_id'] = $this->shopId;
        $tradeList = app::get('topshop')->rpcCall('trade.get.list',$params,'proofing');
        $count = $tradeList['count'];
        $tradeList = $tradeList['list'];//echo "<pre>";print_r($params);exit;

        $usersId = array_column($tradeList, 'user_id');
        if( $usersId )
        {
            $username = app::get('topshop')->rpcCall('user.get.account.name', ['user_id' => implode(',', $usersId)], 'seller');
        }

        foreach((array)$tradeList as $key=>$value)
        {
            $tid[] = $value['tid'];
            $tradeList[$key]['status_depict'] = $tradeStatus[$value['status']];
            $tradeList[$key]['user_login_name'] = $username[$value['user_id']];
            /*if (app::get('ectools')->model('trade_paybill')->getRow('paybill_id',['tid' => $value['tid'], 'status' => 'succ']) && $value['status'] == 'WAIT_BUYER_PAY') {
                $tradeList[$key]['part_payed'] = 1;
            }*/
        }
        $pagedata['orderlist'] =$tradeList;
        $pagedata['count'] =$count;
        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');

        if( $tid )
        {
            $zitiDeliveryVcode = app::get('topshop')->rpcCall('trade.shop.delivery.vcode.get', ['tid' => implode(',', $tid),'shop_id'=>$this->shopId]);
        }

        $pagedata['deliveryVcode'] = $zitiDeliveryVcode;
        $pagedata['pagers'] = $this->__pager($postFilter,$page,$count);
        return view::make('topc/member/proofing/trade/item.html', $pagedata);
    }

    private function _checkParams($filter)
    {
        if($filter['settlement_status'] == '-1'){
            unset($filter['settlement_status']);
        }
        $statusLUT = array(
            '1' => 'WAIT_BUYER_PAY',
            '2' => 'WAIT_SELLER_SEND_GOODS',
            '3' => 'WAIT_BUYER_CONFIRM_GOODS',
            '4' => 'TRADE_FINISHED',
            '5' => array('TRADE_CLOSED','TRADE_CLOSED_BY_SYSTEM'),
        );
        foreach($filter as $key=>$value)
        {
            if(!$value) unset($filter[$key]);
            if($key == 'create_time')
            {
                $times = array_filter(explode('-',$value));
                if($times)
                {
                    $filter['created_time_start'] = strtotime($times['0']);
                    $filter['created_time_end'] = strtotime($times['1'])+86400;
                    unset($filter['create_time']);
                }
            }

            if($key=='status' && $value)
            {
                if($value <= 5)
                {
                    $filter['status'] = $statusLUT[$value];
                }
                else
                {
                    if($value == 6)
                    {
                        $filter['pay_type'] = 'offline';
                    }
                    if($value == 7)
                    {
                        $filter['shipping_type'] = 'ziti';
                    }
                    unset($filter['status']);
                }
            }
        }
        return $filter;
    }

    //售后
    public function aftersales()
    {
        $this->action_view = "aftersales.html";
        $pagedata['action'] = 'topc_ctl_member_proofing@aftersales';
        return $this->output($pagedata);
    }
    //结算
    public function settlement()
    {
        $this->action_view = "settlement.html";
        $pagedata['action'] = 'topc_ctl_member_proofing@settlement';
        return $this->output($pagedata);
    }


    /**
     * @brief 页面输出的统一页面
     *
     * @return html
     */
    public function output($pagedata)
    {
        $pagedata['cpmenu'] = config::get('deepmenu');
        if( $pagedata['_PAGE_'] ){
            $pagedata['_PAGE_'] = 'topc/member/proofing/'.$pagedata['_PAGE_'];
        }else{
            $pagedata['_PAGE_'] = 'topc/member/proofing/'.$this->action_view;
        }
        // echo "<pre>";print_r($pagedata);exit;
        return $this->page('topc/member/proofing/main.html', $pagedata);
    }

    private function __pager($postFilter,$page,$count)
    {
        $postFilter['pages'] = time();
        $total = ceil($count/$this->limit);
        $pagers = array(
            'link'=>url::action('topc_ctl_member_proofing@search',$postFilter),
            'current'=>$page,
            'use_app' => 'topshop',
            'total'=>$total,
            'token'=>time(),
        );
        return $pagers;

    }

    private function __saveTradeSearchFilter($filter)
    {
        $_SESSION['topc_member_proofing_filter'] = $filter;
    }

    private function __getTradeSearchFilter()
    {
        return $_SESSION['topc_member_proofing_filter'];
    }

    public function startEContract()
    {
        $tids = input::get('tid');
        //面包屑
        $this->runtimePath = array(
            ['url'=> url::action('topc_ctl_member_proofing@index'),'title' => app::get('topc')->_('服务撮合中心')],
            ['url'=> url::action('topc_ctl_member_proofing@tradeList'),'title' => app::get('topc')->_('订单列表')],
            ['title' => app::get('topc')->_('订单详情')],
        );

        $params['tid'] = $tids;
        // adjust_fee,
        $params['fields'] = "trade_from,isconfirm_post_fee,isconfirm_adjust_fee,modified_time,shipping_type,delivery_goods_time,dlytmpl_id,receiver_phone,orders.spec_nature_info,orders.sku_id,user_id,tid,status,payment,points_fee,ziti_addr,ziti_memo,post_fee,pay_type,payed_fee,receiver_state,receiver_city,receiver_district,receiver_address,receiver_zip,trade_memo,shop_memo,receiver_name,receiver_mobile,orders.price,orders.num,orders.title,orders.item_id,orders.pic_path,total_fee,discount_fee,buyer_rate,orders.total_fee,orders.adjust_fee,created_time,pay_time,consign_time,end_time,shop_id,need_invoice,invoice_name,invoice_type,invoice_main,invoice_vat_main,invoice_tfn,invoice_bank_name,invoice_bank_num,invoice_addr,invoice_mobile,orders.bn,cancel_reason,update_status,need_econtract,contract_status,contract_id,position,orders.refund_fee,orders.aftersales_status,orders.gift_data";
        $tradeInfo = app::get('topshop')->rpcCall('trade.get',$params,'seller');
        if(!$tradeInfo)
        {
            redirect::action('topc_ctl_member_proofing@index')->send();exit;
        }
        /*增加电子合同状态的显示 */
        $tradeInfo['unfeePayment'] = $tradeInfo['payment'];
        $objMdlelecontract = app::get('systrade')->model('elecontract');
        $elecontract = $objMdlelecontract->getRow('*',array('tid' =>$tids));
        if($elecontract){
            $payment = $tradeInfo['payment'];
            $elecontract['signed_time'] =  date("Y-m-d",$elecontract['signed_time']);
            //$elecontract['pay_type'] = unserialize($elecontract['pay_type']);
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
            $tradeInfo['elecontract'] = $elecontract;
        }
        /*增加电子合同状态的显示 edit by nie 2016-1-17*/
        $contentObj = app::get('systrade')->model('contract');
        $tradeInfo['contact_img'] = $contentObj->getRow('contract_img',array('contract_id'=>$tradeInfo['contract_id']))['contract_img'];

        if(!empty($tradeInfo['contact_img'])) {
            $pagedata['contact_img'] = 1;
        }else{
            $pagedata['contact_img'] = 0;
        }
        //商品发送时间
        if($tradeInfo['delivery_goods_time']){
            $tradeInfo['delivery_goods_time'] = date("Y-m-d ", $tradeInfo['delivery_goods_time']);
        }
        $tradeInfo['cny_payment'] = $this->num_to_rmb($tradeInfo['unfeePayment']);
        $shop_id = $tradeInfo['shop_id'];
        //$objMdlshopinfo = app::get('sysshop')->model('shop_info');
        //$objMdlitem = app::get('sysitem')->model('item');
        //$objMdlbrand = app::get('syscategory')->model('brand');
        //$objMdlsku = app::get('sysitem')->model('sku');
        $providerMdl = app::get('sysproofing')->model('provider');
        $sampleMdl = app::get('sysproofing')->model('sample');
        $offerMdl = app::get('sysproofing')->model('offer');
        $provider_id = substr($shop_id,6);
        $providerInfo = $providerMdl->getRow('provider_name',array('provider_id' =>$provider_id));
        $order_adjust_fee = 0;
        foreach($tradeInfo['orders'] as $key =>$value){
            $order_adjust_fee += $value['adjust_fee']*$value['num'];
            $item_id = $value['item_id'];
            $sample_id = substr($item_id,6);
            $sample = $sampleMdl->getRow('sample_id,sample_name,unit,quantity',array('sample_id' =>$sample_id));
            $offer = $offerMdl->getRow('sample_fee,post_fee,offer_id',array('sample_id' =>$sample_id,'status' => '1'));
            $price = round($offer['sample_fee']/$sample['quantity'],2);
            $unit = $sample['unit'];
            $tradeInfo['orders'][$key]['unit'] = $unit;
            $tradeInfo['orders'][$key]['price'] = $price;
            $tradeInfo['orders'][$key]['adjust_fee'] = $value['adjust_fee'];
            $tradeInfo['orders'][$key]['payment'] = $value['payment'];
        }

        $userInfo = app::get('topshop')->rpcCall('user.get.account.name', ['user_id' => $tradeInfo['user_id']], 'seller');
        $tradeInfo['login_account'] = $userInfo[$tradeInfo['user_id']];
        $pagedata['company_name'] = $providerInfo['provider_name'];
        $tradeInfo['total_adjust_fee'] = $order_adjust_fee;

        //获取默认图片信息
        $pagedata['defaultImageId']= kernel::single('image_data_image')->getImageSetting('item');
        $pagedata['trade']= $tradeInfo;
        $pagedata['editable'] = in_array($tradeInfo['status'], array('WAIT_SENDCONTRACT', 'REJECT_CONTRACT', 'WAIT_CONFIRM'));
        /*
        ** 判断运费是否可调
        ** postfee_adjustable 表示邮费是否可调
        ** price_adjustable 表示单价是否可调
        */
        $postfee_adjustable = in_array($tradeInfo['status'], $this->__adjust_status);
        //货到付款在卖家发货状态下也能修改订单
        if( 'WAIT_SELLER_SEND_GOODS' == $tradeInfo['status']  && 'offline' != $tradeInfo['pay_type'] ){
            $postfee_adjustable = false;
        }
        //货到付款在买家付款状态下不能修改订单
        if ('WAIT_BUYER_PAY' == $tradeInfo['status'] && 'offline' == $tradeInfo['pay_type']) {
            $postfee_adjustable = false;
        }
        $price_adjustable = $postfee_adjustable;

        $pagedata['postfee_adjustable'] = $postfee_adjustable;
        $pagedata['price_adjustable'] = $price_adjustable;

        //物流信息处理
        $pagedata['logi'] = app::get('topshop')->rpcCall('delivery.get',array('tid'=>$params['tid']));
        $pagedata['tracking'] = app::get('syslogistics')->getConf('syslogistics.order.tracking');
        $this->contentHeaderTitle = app::get('topshop')->_('订单详情');

        //合同审核状态
        $Objtrade=app::get('systrade')->model('trade');
        $data=$Objtrade->getRow('*',array('tid'=>$pagedata['trade']['tid']));
        $contractMd = app::get('systrade')->model('contract');
        $filter = Array(
            'contacts',
            'phone',
            'contract_img',
            'contract_id',
            'shop_id'
        );
        $filte['user_id'] = $pagedata['trade']['user_id'];
        $pagedata['contract_status']=$data['contract_status'];
        $pagedata['checkup_status']=$data['checkup_status'];

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
                $tmp['amount'] = round($elecontract['pay_type']['num' . $ii],2);
                $payData[] = $tmp;
            }
        } else {
            $payinfo  = array(
                'times' => 1,
                'amount' => $tradeInfo['payment'],
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
        $pagedata['payData'] = $payData;
        //获取该订单的发货信息
        $objMdlDelivery = app::get('syslogistics')->model('delivery');
        $objMdlDeliveryDetail = app::get('syslogistics')->model('delivery_detail');
        $deliveryRes=$objMdlDelivery->getList('*',array('tid'=>$params['tid']));
        foreach ($deliveryRes as $dk=>$dv){
            $deliveryRes[$dk]['delivery_detail']=$objMdlDeliveryDetail->getList('*',array('delivery_id'=>$dv['delivery_id']));
            if ($dv['department']=='ecommerce') {
                $deliveryRes[$dk]['department']='电商运营部';
            }elseif ($dv['department']=='equipment') {
                $deliveryRes[$dk]['department']='制造业装备经营部';
            }else{
                $deliveryRes[$dk]['department']='';
            }
        }
        $pagedata['delivery']=$deliveryRes;
        $pagedata['time'] = time();
        $pagedata['contract']=$contractMd->getrow($filter,array('contract_id'=>$data['contract_id'],'shop_id'=>$shop_id));

        //电子合同
        $defaultEcontract = config::get('econtract');
        $pagedata['defaultEcontract'] = $defaultEcontract;

        $contract_list = unserialize($pagedata['contract']['contract_img']);
        unset($pagedata['contract']['contract_img']);
        foreach($contract_list as $row){
            $file_name =  explode('/',urldecode($row));
            $pagedata['contract']['contract_img'][] = array('title'=>end($file_name),'url'=>$row);
        }

        //获取物流公司列表
        $dlycorp = app::get('topshop')->rpcCall('shop.dlycorp.getlist',['shop_id'=>$this->shopId]);
        $pagedata['dlycorp'] = $dlycorp['list'];
        //echo "<Pre>";print_r($pagedata);exit;
        $this->action_view = "trade/detail.html";
        $pagedata['action'] = 'topc_ctl_member_proofing@startEContract';
        return $this->output($pagedata);
    }

    /*人民币小写转化为大写函数 edit by zmq 2016-3-2*/
    private function num_to_rmb($num){
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

    /*
	 * 获取合同内金额
	 */
    public function ajaxMoneyInfo(){
        $tids = input::get('tid');
        $params['tid'] = $tids;
        $params['fields'] = "trade_from,isconfirm_post_fee,isconfirm_adjust_fee,modified_time,contract_id,update_status,user_id,tid,status,contract_status,delivery_goods_time,payment,ziti_addr,ziti_memo,dlytmpl_id,post_fee,pay_type,payed_fee,receiver_state,receiver_city,receiver_district,receiver_address,receiver_zip,trade_memo,shop_memo,receiver_name,receiver_mobile,receiver_phone,orders.price,orders.num,orders.title,orders.item_id,orders.sku_id,orders.pic_path,total_fee,discount_fee,buyer_rate,adjust_fee,orders.total_fee,orders.adjust_fee,created_time,pay_time,consign_time,end_time,shop_id,need_invoice,invoice_name,invoice_type,invoice_main,invoice_tfn,invoice_bank_name,invoice_bank_num,invoice_addr,invoice_mobile,orders.spec_nature_info,orders.bn,cancel_reason,orders.refund_fee,position,need_econtract";
        $tradeInfo = app::get('topshop')->rpcCall('trade.get',$params,'seller');
        // $tradeInfo['unfeePayment'] = number_format($tradeInfo['payment'],2);
        $tradeInfo['unfeePayment'] = $tradeInfo['payment'];
        $tradeInfo['cny_payment'] = $this->num_to_rmb($tradeInfo['unfeePayment']);
        // $tradeInfo['post_fee'] =  number_format($tradeInfo['post_fee'],2);
        $tradeInfo['post_fee'] =  $tradeInfo['post_fee'];

        // $tradeInfo['total_fee'] =  number_format($tradeInfo['total_fee'],2);
        $tradeInfo['total_fee'] =  $tradeInfo['total_fee'];
        $tradeInfo['total_adjust_fee'] =  $tradeInfo['unfeePayment']-$tradeInfo['total_fee']-$tradeInfo['post_fee'];
        $tradeInfo['total_adjust_fee']=number_format($tradeInfo['total_adjust_fee'],2);
        $tradeInfo['unfeePayment'] = number_format($tradeInfo['payment'],2);
        $tradeInfo['post_fee'] =  number_format($tradeInfo['post_fee'],2);
        $tradeInfo['total_fee'] =  number_format($tradeInfo['total_fee'],2);
        $tradeInfo['adjust_fee'] =  number_format($tradeInfo['total_adjust_fee'],2);
        $tradeInfo['discount_fee']=0;

        //$objMdlitem = app::get('sysitem')->model('item');
        //$objMdlbrand = app::get('syscategory')->model('brand');
        //$objMdlsku = app::get('sysitem')->model('sku');
        $providerMdl = app::get('sysproofing')->model('provider');
        $sampleMdl = app::get('sysproofing')->model('sample');
        $offerMdl = app::get('sysproofing')->model('offer');
        //$provider_id = substr($this->shop_id,4);
        //$providerInfo = $providerMdl->getRow('provider_name',array('provider_id' =>$provider_id));
        foreach($tradeInfo['orders'] as $key =>$value){
            $item_id = $value['item_id'];
            $sample_id = substr($item_id,6);
            $sample = $sampleMdl->getRow('sample_id,sample_name,unit,quantity',array('sample_id' =>$sample_id));
            $offer = $offerMdl->getRow('sample_fee,post_fee,offer_id',array('sample_id' =>$sample_id,'status' => '1'));
            $sku_price = $price = round($offer['sample_fee']/$sample['quantity'],2);
            $unit = $sample['unit'];
            $payment=($value['adjust_fee']+$value['price'])*$value['num'];
            $tradeInfo['orders'][$key]['unit'] = $unit;
             $tradeInfo['orders'][$key]['sku_price'] = number_format($sku_price,2);
            $tradeInfo['orders'][$key]['sku_price'] = number_format($value['price'],2);
            $tradeInfo['orders'][$key]['price'] = number_format($value['price'],2);
            $tradeInfo['orders'][$key]['adjust_fee'] =number_format($value['adjust_fee'],2);
            // $tradeInfo['orders'][$key]['payment'] = number_format($value['payment'],2);
            $tradeInfo['orders'][$key]['payment'] = number_format($payment,2);
        }

        return response::json($tradeInfo);
    }

    /*
	提交电子合同模板
	edit by nie 2016-1-14
	*/
    public function send_template($tid){
        $postData = input::get();
        $tid = $postData['tid'];

        $objMdltrade = app::get('systrade')->model('trade');
        $objMdlorder = app::get('systrade')->model('order');

        $trade = $objMdltrade->getRow('need_econtract,status',array('tid'=>$postData['tid']));
        if( ($trade['need_econtract'] && ( $trade['status'] == 'WAIT_SENDCONTRACT' || $trade['status'] == 'REJECT_CONTRACT'))
            || !$trade['need_econtract'] ){

            $objMdlelecontract = app::get('systrade')->model('elecontract');

            try{
                $objMdlelecontract->update($postData,array('tid' =>$tid));
                $objMdltrade ->update(array('status' =>'WAIT_CONFIRM'),array('tid' =>$tid));
                $objMdlorder ->update(array('status' =>'WAIT_CONFIRM'),array('tid' =>$tid));
            }catch(Exception $e){
                $msg = $e->getMessage();
                return $this->splash('error','',$msg,true);
            }
            // $msg = app::get('topshop')->_('合同模板提交成功！');
            // $url = url::action('topshop_ctl_trade_detail@index',array('tid'=>$tid));
            // return $this->splash('success','',$msg,true);
        }else{
            $tips = array('rs' => 'error', 'msg' => '合同已经提交给买家,无法修改！');
            return response::json($tips);
        }
        $msg = app::get('topc')->_('合同模板提交成功！');
        //2016.5.14 zcy 页面跳转
        $url = url::action('topc_ctl_member_proofing@tradeList');
        return $this->splash('success',$url,$msg,true);
    }

    /**
     * 产生订单发货页面
     * @params string order id
     * @return string html
     */

    public function goDelivery()
    {
        //面包屑
        /*$this->runtimePath = array(
            ['url'=> url::action('topshop_ctl_index@index'),'title' => app::get('topshop')->_('首页')],
            ['url'=> url::action('topshop_ctl_trade_list@index'),'title' => app::get('topshop')->_('订单列表')],
            ['title' => app::get('topshop')->_('订单发货')],
        );
        $this->contentHeaderTitle = app::get('topshop')->_('订单发货');*/

        $tid = input::get('tid');
        if(!$tid)
        {
            header('Content-Type:application/json; charset=utf-8');
            echo '{error:"'.app::get('topshop')->_("订单号传递出错.").'",_:null}';exit;
        }
        $params['tid'] = $tid;
        $params['fields'] = "orders.spec_nature_info,tid,receiver_name,receiver_mobile,receiver_state,receiver_district,receiver_address,need_invoice,ziti_addr,invoice_type,invoice_name,invoice_main,orders.price,orders.num,orders.title,orders.item_id,orders.pic_path,total_fee,discount_fee,buyer_rate,adjust_fee,orders.total_fee,orders.adjust_fee,created_time,pay_time,consign_time,end_time,shop_id,need_invoice,invoice_name,invoice_type,invoice_main,orders.bn,cancel_reason,orders.refund_fee,orders.aftersales_status,orders.dlytmpl_id,shipping_type,orders.gift_data";
        $tradeInfo = app::get('topshop')->rpcCall('trade.get',$params,'seller');
        //获取默认图片信息
        $pagedata['defaultImageId']= kernel::single('image_data_image')->getImageSetting('item');

        $pagedata['tradeInfo'] = $tradeInfo;

        //获取用户的物流模板
        if($tradeInfo['shipping_type'] == 'ziti')
        {
            $pagedata['ziti'] = 'true';
        }

        //默认查询字段
        $row = "corp_id,corp_code,corp_name";

        $objMdlDlycorp = app::get('syslogistics')->model('dlycorp');
        /*
        //分页使用
        $count = $objMdlDlycorpShop->count($filter);
        $pageTotal = ceil($count/$params['page_size']);
        $page =  $params['page_no'] ? $params['page_no'] : 1;
        $limit = $params['page_size'] ? $params['page_size'] : -1;
        $currentPage = ($pageTotal < $page) ? $pageTotal : $page; //防止传入错误页面，返回最后一页信息
        $offset = ($currentPage-1) * $limit;
         */

        //$pagedata['list'] = $objMdlDlycorpShop->getList($row,$filter,$offset, $limit);
        //$pagedata['count'] = $count;
        $dlycorp = $objMdlDlycorp->getList($row,[]);

        //$dlycorp = app::get('topshop')->rpcCall('shop.dlycorp.getlist',['shop_id'=>$this->shopId]);
        $pagedata['dlycorp'] = $dlycorp;

        $this->action_view = "trade/godelivery.html";
        $pagedata['action'] = 'topc_ctl_member_proofing@goDelivery';
        return $this->output($pagedata);
    }

    /**
     * 发货订单处理
     * @params null
     * @return null
     */
    public function doDelivery()
    {
        $sdf = input::get();

        //当订单为自提订单并且没有物流配送，可以填写字体备注
        if( isset($sdf['isZiti']) && $sdf['isZiti'] == "true" )
        {
            if(!trim($sdf['logi_no']) && !trim($sdf['ziti_memo']))
            {
                return $this->splash('error',null, '订单为自提订单，运单号和备注至少选择一项必填', true);
            }
            if( mb_strlen(trim($sdf['ziti_memo']),'utf8') > 200)
            {
                return $this->splash('error',null, '自提备注过长', true);
            }
            $sdf['ziti_memo'] = trim($sdf['ziti_memo']) ? trim($sdf['ziti_memo']) : "";
        }
        else
        {
            unset($sdf['isZiti'],$sdf['ziti_memo']);
            if(empty($sdf['logi_no']))
            {
                return $this->splash('error',null, '发货单号不能为空', true);
            }
        }

        if(isset($sdf['logi_no']) && trim($sdf['logi_no']) && strlen(trim($sdf['logi_no'])) < 6)
        {
            return $this->splash('error',null, '运单号过短，请认真核对后填写(大于6)正确的编号', true);
        }

        if(strlen(trim($sdf['logi_no'])) > 20 )
        {
            return $this->splash('error',null, '运单号过长，请认真核对后填写(小于20)正确的编号', true);
        }
        $sdf['logi_no'] = trim($sdf['logi_no']) ? trim($sdf['logi_no']) : "0";
        $sdf['seller_id'] = $this->sellerId;
        $sdf['shop_id'] = $this->shopId;

        try
        {
            app::get('topshop')->rpcCall('trade.delivery',$sdf);
        }
        catch (Exception $e)
        {
            return $this->splash('error',null, $e->getMessage(), true);
        }
        $this->sellerlog('订单发货。订单号是:'.$sdf['tid']);
        $url = url::action('topc_ctl_member_proofing@tradeList', ['useSessionFilter'=>true]);
        return $this->splash('success',$url, '发货成功', true);
    }

    /**
     * 提交电子合同付款方式
     * edit by nie 2016-1-8
     */
    public function send_contract(){
        //表单信息
        $postData = input::get();
        if (!$postData['pay_1']) {
            $elecontract = app::get('systrade')->model('elecontract')->getRow('pay_type', array('tid'=>$postData['tid']));
            $postData['pay_1'] = $elecontract['pay_type']['pay_1'];
            $postData['pay_2'] = $elecontract['pay_type']['pay_2'];
            $postData['pay_3'] = $elecontract['pay_type']['pay_3'];
            $postData['pay_4'] = $elecontract['pay_type']['pay_4'];
            $postData['pay_5'] = $elecontract['pay_type']['pay_5'];
        }

        $filter['tid'] = $postData['tid'];
        $objMdltrade = app::get('systrade')->model('trade');
        $trade = $objMdltrade->getRow('discount_fee,payment,status,need_econtract',array('tid'=>$postData['tid']));
        if($trade['need_econtract'] && $trade['status'] != 'WAIT_SENDCONTRACT' && $trade['status'] != 'REJECT_CONTRACT'){
            return;
        }
        $data = array();
        /*0一次发货；1分次发货*/
        if($postData['zhongb2'] == '0'){
            $data['is_part_delivery'] = '0';
        }else{
            $data['is_part_delivery'] = '1';
        }

        if($postData['zhongb3'] == '0'){
            $data['is_delivery_type'] = 'DF';
        }else{
            $data['is_delivery_type'] = 'ZF';
        }

        if($postData['zhongb1'] == '0'){
            $data['is_part_pay'] = '0';
        }else{
            $data['is_part_pay'] = '1';
            $num = $postData['pay_1']+$postData['pay_2']+$postData['pay_3']+$postData['pay_4']+$postData['pay_5'];
            if($num != 100){
                $msg = "请重新填写付款方式！";
                //$url = url::action('topshop_ctl_trade_list@index');
                return $this->splash('error','',$msg,true);
            }

            //初始化 交易信息
            $trade_data['post_fee'] = $postData['post_fee'] ? $postData['post_fee'] : 0;
            $trade_data['adjust_fee'] = $postData['adjust_fee'] ? $postData['adjust_fee'] : 0;
            //订单金额

            $total_fee = bcadd($trade_data['post_fee'], $trade_data['adjust_fee'], 3);
            $total_fee = bcadd($total_fee, array_sum(explode(',',$postData['adjust'])), 3);

            $trade_data['adjust_fee'] = bcadd($trade_data['adjust_fee'], array_sum(explode(',',$postData['adjust'])), 3);
            $trade_data['payment'] = bcadd($postData['total_fee'], $total_fee, 3);


            if($trade['discount_fee'] > 0) {
                $trade_data['payment'] = bcsub($trade_data['payment'],$trade['discount_fee'],3);
            }

            if($trade_data['payment'] < 0){
                $tips = array('rs' => 'error', 'msg' => '金额输入有误,请核查！');
                return response::json($tips);
            }
            // $payment = $trade_data['payment'];
            $payment=$postData['SumTotal'];

            unset($postData['post_fee']);
            unset($postData['adjust_fee']);
            unset($postData['total_fee']);
            unset($postData['tid']);
            unset($postData['user_id']);
            unset($postData['zhongb1']);
            unset($postData['zhongb2']);
            unset($postData['zhongb3']);
            unset($postData['delivery_goods_time']);
            unset($postData['SumTotal']);

            $elecontract['pay_1'] = $postData['pay_1'];
            $elecontract['num1'] = strval(round($payment*$postData['pay_1']/100,2));
            $elecontract['pay_2'] = $postData['pay_2'];
            $elecontract['num2'] = strval(round($payment*$postData['pay_2']/100,2));

            if($postData['pay_3']) {
                if(!$postData['pay_4']){
                    $elecontract['num3'] = strval(round ($payment - $elecontract['num1'] - $elecontract['num2'], 2));
                }else{
                    $elecontract['num3'] = strval(round ($payment*$postData['pay_3']/100,2));
                }
                $elecontract['pay_3'] = $postData['pay_3'];
                $elecontract['detail_time1'] = $postData['detail_time1'];
            }else{
                $elecontract['pay_3'] = 0;
                $elecontract['num3'] = 0;
                $elecontract['detail_time1'] =  $postData['detail_time1'];
            }

            if($postData['pay_4']) {
                if(!$postData['pay_5']) {
                    $elecontract['num4'] = strval(round ($payment - $elecontract['num1'] - $elecontract['num2'] - $elecontract['num3'], 2));
                }else{
                    $elecontract['num4'] = strval(round ($payment*$postData['pay_4']/100,2));
                }
                $elecontract['pay_4'] = $postData['pay_4'];
                $elecontract['detail_time2'] = $postData['detail_time2'];
            }else {
                $elecontract['pay_4'] = 0;
                $elecontract['num4'] = 0;
                $elecontract['detail_time2'] = $postData['detail_time2'];
            }

            if($postData['pay_5']) {
                $elecontract['pay_5'] = $postData['pay_5'];
                $elecontract['num5'] = strval(round ($payment - $elecontract['num1'] - $elecontract['num2'] - $elecontract['num3'] - $elecontract['num4'], 2));
                $elecontract['detail_time3'] =  $postData['detail_time3'];
            }else{
                $elecontract['pay_5'] = 0;
                $elecontract['num5'] = 0;
                $elecontract['detail_time3'] =  $postData['detail_time3'];
            }
            $data['pay_type'] = $elecontract;
        }
        $objMdlelecontract = app::get('systrade')->model('elecontract');dd($data);
        //$objMdltrade = app::get('systrade')->model('trade');
        $result = $objMdlelecontract->update($data, $filter);
        if($result){
            $msg = "提交成功!请填写合同模板！";
            //$objMdltrade->update($trade_data, $filter);
            return $this->splash('success','',$msg,true);
        }
    }

    //新增服务分类
    public function addCategory()
    {
        $updateMdl = app::get('sysproofing')->model('update_cat');
        if ($updateMdl->getRow('*',['provider_id' => $this->provider_id, 'status' => 0]))
        {
            $pagedata['not_approve'] = 1;
        } else {
            if ($applyInfo = $updateMdl->getRow('*',['provider_id' => $this->provider_id, 'status' => 2])) {
                $applyInfo['cat_id'] = explode(',',$applyInfo['cat_id']);
                $pagedata['apply'] = $applyInfo;
            }
            $relMdl = app::get('sysproofing')->model('provider_cat');
            $cats = $relMdl->getList('cat_id',['provider_id' => $this->provider_id]);
            $categories = app::get('sysproofing')->model('category')->getList('*');

            foreach ($categories as $cat) {
                if (in_array($cat['cat_id'],array_column($cats,'cat_id'))) {
                    $pagedata['has'][] = $cat;
                } else {
                    $pagedata['nothas'][] = $cat;
                }
            }
            $pagedata['provider_id'] = $this->provider_id;
        }

        $this->action_view = "addCategory.html";
        $pagedata['action'] = 'topc_ctl_member_proofing@addCategory';
        return $this->output($pagedata);
    }

    public function updateCategory()
    {

        $post = utils::_filter_input(input::get());
        if ($post['provider_id'] != $this->provider_id) {
            return $this->splash('error','','登录账号变更，请刷新页面重试！');
        }
        $validator = validator::make(
            [
                'cat' => $post['cats'],
                'reason' => $post['reason'],
                'desc' => $post['desc'],
            ],
            [
                'cat' => 'required',
                'reason' => 'required',
                'desc' => 'required',
            ],
            [
                'cat' => '请选择至少一种服务类型',
                'reason' => '申请原因不能为空',
                'desc' => '服务能力描述不能为空',
            ]
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                return $this->splash('error','',$error[0]);
            }
        }
        $url = url::action('topc_ctl_member_proofing@addCategory');

        $updateMdl = app::get('sysproofing')->model('update_cat');
        if ($updateMdl->getRow('*',['provider_id' => $post['provider_id'], 'status' => 0]))
        {
            return $this->splash('error',$url,'您的上一个申请还未通过，无法继续申请！');
        }
        $data['provider_id'] = $post['provider_id'];
        $data['cat_id'] = implode(',',$post['cats']);
        $data['reason'] = $post['reason'];
        $data['desc'] = $post['desc'];
        $data['status'] = 0;
        $data['modified_time'] = time();
        if ($post['apply_id']) $data['apply_id'] = $post['apply_id'];
        if ($updateMdl->save($data)) {
            return $this->splash('success',$url,'申请成功！');
        } else {
            return $this->splash('error','','申请失败！');
        }
    }
}
