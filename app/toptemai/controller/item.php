<?php

/**
 * @brief 商家商品管理
 */
class toptemai_ctl_item extends toptemai_controller {

    public $limit = 10;
    public $exportLimit = 100;

    public function itemList()
    {
        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');

        $status = input::get('status',false);
        $pages =  input::get('pages',1);
        $pagedata['status'] = $status;
        $filter = array(
            'user_id' => $this->userId,
            'approve_status' => $status,
            'page_no' =>intval($pages),
            'page_size' => intval($this->limit),
        );
        $filter['fields'] = 'item_id,list_time,modified_time,title,image_default_id,price,approve_status,dlytmpl_id,nospec';
        $filter['orderBy'] = 'modified_time desc';
        //库存报警判断
        if($status=='oversku')
        {
            $params['user_id'] = $this->userId;
            $params['fields'] = 'policevalue';
            $storeWarning = kernel::single('systemai_item_store_warning' , $this->userId);
            $storePoliceVal = $storeWarning->getStorePolice($params);
            $filter['store'] = $storePoliceVal['policevalue'] ? $storePoliceVal['policevalue'] : 0;

            $itemsList = $storeWarning->storePolice($filter);
        }
        else
        {
            $searchItems = kernel::single('systemai_item_searchItems');
            $itemsList = $searchItems->getList($filter);
        }
        $pagedata['item_list'] = $itemsList['list'];
        $pagedata['total'] = $itemsList['total_found'];

        $totalPage = ceil($itemsList['total_found']/$this->limit);
        $pagersFilter['pages'] = time();
        $pagersFilter['status'] = $status;
        $pagers = array(
            'link'=>url::action('toptemai_ctl_item@itemList',$pagersFilter),
            'current'=>$pages,
            'use_app' => 'toptemai',
            'total'=>$totalPage,
            'token'=>time(),
        );
        $pagedata['pagers'] = $pagers;

        //获取当前店铺商品分类
        $catparams['user_id'] = $this->userId;
        //$catparams['fields'] = 'cat_id,cat_name';

        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');
        //获取运费模板
        $tmpParams = array(
            'user_id' => $this->userId,
            'status' => 'on',
            'fields' => 'user_id,name,template_id',
        );
        $pagedata['dlytmpl'] = app::get('toptemai')->rpcCall('logistics.temaitmpl.get.list',$tmpParams);
        $this->contentHeaderTitle = app::get('toptemai')->_('商品列表');
        $pagedata['setting'] = true;
        $pagedata['exportLimit'] = $this->exportLimit;
        $pagedata['temai_cate'] = $this->temaiCategory;

        return $this->page('toptemai/item/list.html', $pagedata);
    }

    public function add()
    {
        $pagedata['userId'] = $this->userId;
        // 获取物流模板列表
        $tmpParams = array(
            'user_id' => $this->userId,
            'status' => 'on',
            'fields' => 'user_id,name,template_id',
        );
        $pagedata['dlytmpls'] = app::get('toptemai')->rpcCall('logistics.temaitmpl.get.list',$tmpParams);
        $pagedata['itemUnitList'] = config::get("temaiconf");
        $pagedata['itemUnitList'] = $pagedata['itemUnitList']['item_unit'];
        $pagedata['taxRateList'] = config::get("temaiconf.item_tax_rate");

        $this->contentHeaderTitle = app::get('toptemai')->_('添加商品');
        return $this->page('toptemai/item/edit.html', $pagedata);
    }
    //商品库存报警设置
    public function storePolice()
    {
        $userId = $this->userId;
        $params['user_id'] = $userId;
        $params['fields'] = 'police_id,policevalue';

        $storePolice = app::get('toptemai')->rpcCall('item.store.info',$params);
        //echo '<pre>';print_r($storePolice);exit();
        $pagedata['storePolice'] = $storePolice;
        $this->contentHeaderTitle = app::get('toptemai')->_('设置商品库存报警数');
        return $this->page('toptemai/item/storepolice.html', $pagedata);
    }
    //保存库存报警
    public function saveStorePolice()
    {
        $storePolice = intval(input::get('storepolice'));
        $policeId = intval(input::get('police_id'));
        $url = url::action('toptemai_ctl_item@storePolice');
        try
        {
            $validator = validator::make(
                [$storePolice],
                ['required|integer|min:1|max:99999'],
                ['库存预警值必填!|库存预警值必须为整数!|库存预警值最小为1!|库存预警值最大为99999!']
            );
            $validator->newFails();
        }
        catch( \LogicException $e )
        {
            return $this->splash('error', $url, $e->getMessage(), true);
        }
        $userId = $this->userId;
        $params['user_id'] = $userId;
        $params['policevalue'] = $storePolice;
        if(!is_null($policeId))
        {
            $params['police_id'] = $policeId;
        }
        try
        {
            app::get('toptemai')->rpcCall('item.store.police.add',$params);
        }
        catch( \LogicException $e )
        {
            return $this->splash('error', null, $e->getMessage(), true);
        }
        $this->sellerlog('库存预警设置。');
        return $this->splash('success', $url, '保存成功', true);
    }

    public function stepPrice()
    {
        $itemId = intval(input::get('item_id'));
        // 商品详细信息
        $params['item_id'] = $itemId;
        $params['user_id'] = $this->userId;
        $params['fields'] = "*,sku,item_status,spec_index";
        $pagedata['item'] = app::get('toptemai')->rpcCall('item.get',$params);
        //echo "<pre>";print_r($pagedata);exit;
        return $this->page('toptemai/item/stepPrice.html', $pagedata);
    }

    public function editStepPrice()
    {
        $skuId = intval(input::get('sku_id'));
        // 商品信息
        $params = app::get('sysitem')->model('sku')->getRow('step_price', ['sku_id' => $skuId]);
        if ($params) {
            $pagedata['data'] = unserialize($params['step_price']);
        }
        $pagedata['sku_id'] = $skuId;
        //echo "<pre>";print_r($pagedata);exit;
        echo view::make('toptemai/item/editStepPrice.html', $pagedata);exit;
    }

    public function deleteStepPrice()
    {
        $skuId = intval(input::get('sku_id'));
        // 商品信息
        $skuMdl = app::get('sysitem')->model('sku');
        $itemMdl = app::get('sysitem')->model('item');
        $itemInfo = $skuMdl->getRow('item_id', ['sku_id' => $skuId]);

        $res = $skuMdl->update(['step_price' => ''], ['sku_id' => $skuId]);
        $res2 = $itemMdl->update(['step_price' => ''], ['item_id' => $itemInfo['item_id']]);

        if ($res && $res2) {
            $data['message'] = '清除成功！';
        } else {
            $data['error_flag'] = 1;
            $data['message'] = '保存失败！';
        }
        //echo "<pre>";print_r($pagedata);exit;
        return response::json($data);exit;
    }

    public function saveStepPrice()
    {
        $skuId = intval(input::get('sku_id'));
        if (!$skuId) {
            $data['error_flag'] = 1;
        } else {
            $params = input::get('data');
            //检查
            $tmp = [];
            foreach ($params as $key => $val) {
                if (!trim($val['quantity']) || !trim($val['price'])) {
                    $data['error_flag'] = 1;
                    $data['message'] = '表单存在空值！';
                    return response::json($data);exit;
                }
                if (in_array( $val['quantity'], $tmp)) {
                    $data['error_flag'] = 1;
                    $data['message'] = '数量不能重复！';
                    return response::json($data);exit;
                }
                $tmp[] = $val['quantity'];
            }
            $skuMdl = app::get('sysitem')->model('sku');
            $res = $skuMdl->update(['step_price' => serialize($params)], ['sku_id' => $skuId]);
            $itemInfo = $skuMdl->getRow('item_id', ['sku_id' => $skuId]);
            app::get('sysitem')->model('item')->update(['step_price' => serialize($params)], ['item_id' => $itemInfo['item_id']]);
            if (!$res) {
                $data['error_flag'] = 1;
                $data['message'] = '保存失败！';
            }
        }
        //echo "<pre>";print_r($input);exit;
        return response::json($data);exit;
    }

    public function edit()
    {
        //$pagedata['return_to_url'] = request::server('HTTP_REFERER');
        $itemId = intval(input::get('item_id'));
        $pagedata['userId'] = $this->userId;

        // 店铺关联的商品品牌列表
        // 商品详细信息
        $params['item_id'] = $itemId;
        $params['user_id'] = $this->userId;
        $params['fields'] = "*,sku,item_store,item_status,item_count,item_desc,item_nature,spec_index";
        $pagedata['item'] = app::get('toptemai')->rpcCall('item.get',$params);
        if ($pagedata['item']['nospec'] == '1') {
            $pagedata['item']['spec_code'] = array_column($pagedata['item']['sku'],'spec_code')[0];
        }

        // 获取运费模板列表
        $tmpParams = array(
            'user_id' => $this->userId,
            'status' => 'on',
            'fields' => 'user_id,name,template_id',
        );
        $pagedata['dlytmpls'] = app::get('toptemai')->rpcCall('logistics.temaitmpl.get.list',$tmpParams);
        $pagedata['itemUnitList'] = config::get("temaiconf");
        $pagedata['itemUnitList'] = $pagedata['itemUnitList']['item_unit'];
        $pagedata['taxRateList'] = config::get("temaiconf.item_tax_rate");

        $this->contentHeaderTitle = app::get('toptemai')->_('添加商品');
        $pagedata['item']['spec_code'] = array_column($pagedata['item']['sku'],'spec_code')[0];//dd($pagedata);
        return $this->page('toptemai/item/edit.html', $pagedata);
    }

    //商品搜所
    public function searchItem()
    {
        $filter = input::get();

        if($filter['min_price']&&$filter['max_price'])
        {
            if($filter['min_price']>$filter['max_price'])
            {
                $msg = app::get('toptemai')->_('最大值不能小于最小值！');
                return $this->splash('error', null, $msg, true);
            }
        }
        $pages =  $filter['pages'] ? $filter['pages'] : 1;
        $params = array(
            'user_id' => $this->userId,
            'search_keywords' => $filter['item_title'],
            'min_price' => $filter['min_price'],
            'max_price' => $filter['max_price'],
            'page_no' =>intval($pages),
            'page_size' => intval($this->limit),
            'orderBy' =>'modified_time desc',
        );

        if($filter['use_platform'] >= 0)
        {
            $params['use_platform'] = $filter['use_platform'];
        }
        if($filter['item_cat'] && $filter['item_cat'] > 0)
        {
            $params['search_shop_cat_id'] = (int)$filter['item_cat'];
        }
        if($filter['item_no'])
        {
            $params['bn'] = $filter['item_no'];
        }
        if($filter['status'])
        {
            $params['approve_status'] = $filter['status'];
        }

        if($filter['dlytmpl_id']&&$filter['dlytmpl_id']>0)
        {
            $pagedata['dlytmpl_id'] = $params['dlytmpl_id'] = $filter['dlytmpl_id'];
        }

        $pagedata['use_platform'] = $filter['use_platform'];
        $pagedata['min_price'] = $filter['min_price'];
        $pagedata['max_price'] = $filter['max_price'];
        $pagedata['search_keywords'] = $filter['item_title'];
        $pagedata['item_cat_id'] = $filter['item_cat'];
        $pagedata['item_no'] = $filter['item_no'];
        $pagedata['status'] = isset($filter['status']) ? $filter['status'] : '';
        $params['fields'] = 'item_id,list_time,modified_time,title,image_default_id,price,approve_status,store,dlytmpl_id,nospec';
        //库存报警判断
        if($filter['status']=='oversku')
        {
            $pparams['user_id'] = $this->userId;
            $pparams['fields'] = 'policevalue';
            $storePolice = app::get('toptemai')->rpcCall('item.store.info',$pparams);
            $params['store'] = $storePolice['policevalue']?$storePolice['policevalue']:0;
            $itemsList = app::get('toptemai')->rpcCall('search.item.oversku',$params);
        }
        else
        {
            $itemsList = app::get('toptemai')->rpcCall('temai.item.search',$params);
        }
        //获取运费模板
        $tmpParams = array(
            'user_id' => $this->userId,
            'status' => 'on',
            'fields' => 'shop_id,name,template_id',
        );
        $pagedata['dlytmpl'] = app::get('toptemai')->rpcCall('logistics.temaitmpl.get.list',$tmpParams);


        $pagedata['item_list'] = $itemsList['list'];
        $pagedata['total'] = $itemsList['total_found'];

        $totalPage = ceil($itemsList['total_found']/$this->limit);
        $pagersFilter['pages'] = time();
        $pagersFilter['min_price'] = $filter['min_price'];
        $pagersFilter['max_price'] = $filter['max_price'];
        $pagersFilter['use_platform'] = $filter['use_platform'];
        $pagersFilter['item_title'] = $filter['item_title'];
        $pagersFilter['item_cat'] = $filter['item_cat'];
        $pagersFilter['item_no'] = $filter['item_no'];
        $pagersFilter['dlytmpl_id'] = $filter['dlytmpl_id'];
        if(isset($filter['status']))
        {
            $pagersFilter['status'] =  $filter['status'];
        }

        $pagers = array(
            'link'=>url::action('toptemai_ctl_item@searchItem',$pagersFilter),
            'current'=>$pages,
            'use_app' => 'toptemai',
            'total'=>$totalPage,
            'token'=>time(),
        );
        $pagedata['pagers'] = $pagers;

        //获取当前店铺商品分类
        $catparams['shop_id'] = $this->shopId;
        $itemCat = app::get('toptemai')->rpcCall('shop.cat.get', $catparams);
        $pagedata['item_cat'] = $itemCat;

        // 是否在搜索中
        $pagedata['is_search'] = true;
        // 表格切换条件
        $searchParams = $pagersFilter;
        $searchArr = array();
        $searchTmp = array();
        if($searchParams)
        {
            unset($searchParams['pages']);
            if(isset($searchParams['status']))
            {
                unset($searchParams['status']);
            }
            $status = array(
                'onsale' => app::get('toptemai')->_('上架中'),
                'instock' => app::get('toptemai')->_('仓库中'),
                'oversku' => app::get('toptemai')->_('库存报警'),
                'pending' => app::get('toptemai')->_('待审核'),
                'refuse' => app::get('toptemai')->_('审核失败'),
            );

            foreach ($status as $k=>$v)
            {
                $searchParams['status'] = $k;
                $searchTmp['status'] = $k;
                $searchTmp['url'] = url::action('toptemai_ctl_item@searchItem', $searchParams);
                $searchTmp['label'] = $v;
                $searchArr[] = $searchTmp;
            }
        }

        $pagedata['search_arr'] = $searchArr;
        $pagedata['setting'] = true;
        $this->contentHeaderTitle = app::get('toptemai')->_('商品列表');
        $pagedata['exportLimit'] = $this->exportLimit;

        return $this->page('toptemai/item/list.html', $pagedata);

    }
    public function storeItem()
    {
        $postData = input::get();
        if ($postData['item']['nospec'] == '0') {
            $postData['item']['store'] = 0;
            foreach ($postData['item']['sku'] as $key => $val) {
                $skuInfo = $val;
                $postData['item']['store'] += $val['store'];
            }
            $postData['item']['weight'] = $skuInfo['weight'];
            $postData['item']['length'] = $skuInfo['length'];
            $postData['item']['width'] = $skuInfo['width'];
            $postData['item']['height'] = $skuInfo['height'];
            $postData['item']['price'] = $skuInfo['price'];
            $postData['item']['mkt_price'] = $skuInfo['mkt_price'];
            $postData['item']['cost_price'] = $skuInfo['cost_price'];
            $postData['item']['bn'] = $skuInfo['bn'];
            $postData['item']['barcode'] = $skuInfo['barcode'];
            $postData['item']['spec_code'] = $skuInfo['spec_code'];

            $postData['item']['brand_name'] = trim($postData['item']['brand_name']);
            $postData['item']['brand_logo'] = trim($postData['item']['brand_logo']);
        }
        if(! empty($postData['item']['brand_name'])){
            $brandItem = array('brand_name' => $postData['item']['brand_name'] , 'brand_logo' => $postData['item']['brand_logo']);
            $barandMdl = app::get('syscategory')->model('brand');
            $brandId = $barandMdl->newBrand($brandItem);
            if($brandId){
                $shoprelMdl = app::get('systemai')->model('shop_rel_brand');
                $shoprelMdl->newBrandShopForItem($this->userId , $brandId);
            }
            $postData['item']['brand_id'] = $brandId;
            unset($postData['item']['brand_name']);unset($postData['item']['brand_logo']);
        }
        try
        {
            // 检查参数
            $this->_checkPost($postData);
            // 格式化参数
            $postData = $this->_formatItemData($postData);
            $postData['sku'] = json_encode($postData['sku']);
            $result = app::get('toptemai')->rpcCall('temai.item.create',$postData);
            if($result)
            {
                $this->sellerlog('保存商品，名称是'.$postData['title']);
                $url = url::action('toptemai_ctl_item@itemList');
                $msg = app::get('toptemai')->_('保存成功');
                return $this->splash('success', $url, $msg, true);
            }
        }
        catch (Exception $e)
        {
            return $this->splash('error', '', $e->getMessage(), true);
        }
    }

    // 初步判断数据合法性
    private function _checkPost($postData)
    {
        if(mb_strlen($postData['item']['title'],'UTF-8') > 50)
        {
            throw new Exception('商品名称至多50个字符');
        }

        if($postData['spec_value'])
        {
            foreach($postData['spec_value'] as $val)
            {
                if(mb_strlen($val,'UTF-8') > 20)
                {
                    throw new Exception('销售属性名称至多20个字符');
                }
            }
        }
    }

    // 格式化添加商品接口需要的数据
    private function _formatItemData($postData)
    {
        $data = [];
        $data['user_id'] = $this->userId;
        $data['cat_id'] = $postData['cat_id'];
        $data['brand_id'] = $postData['item']['brand_id'];
        $data['shop_cat_id'] = '';
        $data['title'] = htmlspecialchars($postData['item']['title']);
        $data['sub_title'] = htmlspecialchars($postData['item']['sub_title']);

        $data['bn'] = $postData['item']['bn'];
        $data['price'] = $postData['item']['price'];
        $data['cost_price'] = $postData['item']['cost_price'] ? : 0;
        $data['mkt_price'] = $postData['item']['mkt_price'] ? : 0;
        $data['show_mkt_price'] = $postData['item']['show_mkt_price'] ? 1 : 0;

        $data['weight'] = $postData['item']['weight'] ? : 0;
        $data['length'] = $postData['item']['length'] ? : 0;
        $data['width'] = $postData['item']['width'] ? : 0;
        $data['height'] = $postData['item']['height'] ? : 0;
        $data['unit'] = $postData['item']['unit'];
        $data['tax_rate'] = $postData['item']['tax_rate'];
        $data['list_image'] = $postData['listimages'] ? implode(',', $postData['listimages']) : '';
        $data['images'] = $postData['images']; //颜色属性的关联图片
        $data['order_sort'] = 0; // 目前未用到

        $data['has_discount'] = 0; // 目前未用到
        $data['is_virtual'] = 0; // 目前未用到
        $data['is_timing'] = 0; // 目前未用到
        $data['nospec'] = $postData['item']['nospec'] ? 1: 0;

        $data['spec'] = $postData['item']['spec'];
        $data['spec_value'] = $postData['spec_value'];
        $data['nature_props'] = $postData['item']['nature_props'];
        $data['params'] = $postData['item']['params'];
        $data['itemParams'] = $postData['itemParams'];
        $data['sub_stock'] = $postData['item']['sub_stock'];

        $data['outer_id'] = 0;$postData['item']['outer_id']; //目前未用到
        $data['is_offline'] = 0; // 目前未用到
        $data['barcode'] = $postData['item']['barcode'];
        $data['spec_code'] = $postData['item']['spec_code'];
        $data['use_platform'] = $postData['item']['use_platform'];
        $data['dlytmpl_id'] = $postData['item']['dlytmpl_id'];

        $data['approve_status'] = 'instock'; // 编辑后默认下架
        $data['list_time'] = $postData['item']['list_time'];
        $data['item_id'] = $postData['item']['item_id'];
        $data['protection_info'] = $postData['item']['protection_info'];
        $data['sku'] = $postData['item']['sku'];

        //编辑单品时，将商品库存赋值给货品库存
        $sku = json_decode($data['sku'],1);
        if( $sku && $data['nospec'])
        {
            foreach($sku as $key=>&$val)
            {
                $val['store'] = $postData['item']['store'];
            }
            $data['sku'] = json_encode($sku);
        }

        $data['desc'] = $postData['item']['desc'];

        $data['wap_desc'] = $postData['item']['wap_desc'];
        $data['store'] = $postData['item']['store'];//单品时候需要

        return $data;
    }

    public function setItemStatus(){

        $postData = input::get();
        try
        {
            if(!$itemId = $postData['item_id'])
            {
                $msg = app::get('toptemai')->_('商品id不能为空');
                return $this->splash('error',null,$msg,true);
            }

            if($postData['type'] == 'tosale')
            {
                if(true){//app::get('sysconf')->getConf('shop.goods.examine')
                    $status = 'pending';
                    $msg = app::get('toptemai')->_('提交审核成功');
                }else{
                    $status = 'onsale';
                    $msg = app::get('toptemai')->_('上架成功');
                }
            }
            elseif($postData['type'] == 'tostock')
            {
                $status = 'instock';
                $msg = app::get('toptemai')->_('下架成功');
            }
            else
            {
                return $this->splash('error',null,'非法操作!', true);
            }

            $itemstatus = app::get('topc')->rpcCall('item.get',array('item_id'=>$itemId,'fields'=>'item_id,approve_status'));

            if($status =='instock' || $itemstatus['approve_status'] != 'onsale' ){
                $params['item_id'] = intval($itemId);
                $params['user_id'] = intval($this->userId);
                $params['approve_status'] = $status;
                app::get('toptemai')->rpcCall('item.sale.status',$params);
            }

            $queue_params['item_id'] = intval($itemId);
            $queue_params['shop_id'] = intval($this->shopId);
            $queue_params['status'] = $status;
            event::fire('item.notify', array($queue_params));

            $this->sellerlog('操作商品状态。改为 '.$status);
            $url = url::action('toptemai_ctl_item@itemList');
            return $this->splash('success', $url, $msg, true);
        }
        catch(Exception $e)
        {
            return $this->splash('error',null,$e->getMessage(), true);
        }
    }

    public function deleteItem()
    {
        $postData = input::get();
        //订单状态
        $orderStatus = array('WAIT_BUYER_PAY', 'WAIT_SELLER_SEND_GOODS', 'WAIT_BUYER_CONFIRM_GOODS');

        try
        {
            if(!$itemId = $postData['item_id'])
            {
                $msg = app::get('toptemai')->_('商品id不能为空');
                return $this->splash('error',null,$msg, true);
            }

            //判断商品所在订单的状态
            $orderParams = array();
            $orderParams['item_id'] = (int)$itemId;
            $orderParams['fields'] = 'status';
            $orderList = app::get('toptemai')->rpcCall('trade.order.list.get', $orderParams);
            if($orderList)
            {
                $orderArrStatus = array_column($orderList, 'status');
                foreach ($orderStatus as $status)
                {
                    if(in_array($status, $orderArrStatus))
                    {
                        $msg = app::get('toptemai')->_('商品存在未完成的订单，不能删除');
                        return $this->splash('error',null,$msg, true);
                    }
                }
            }

            app::get('toptemai')->rpcCall('temai.item.delete',array('item_id'=>intval($itemId),'user_id'=>intval($this->userId)));
        }
        catch(Exception $e)
        {
            return $this->splash('error',null, $e->getMessage(), true);
        }
        $this->sellerlog('删除商品。 商品ID是'.$itemId);
        return $this->splash('success',null,'删除成功', true);
    }

    public function ajaxGetBrand($cat_id)
    {
        $params['user_id'] = $this->userId;
        $params['cat_id'] = input::get('cat_id');
        try
        {
            $brand = app::get('toptemai')->rpcCall('category.tmget.cat.rel.brand',$params);
        }
        catch(Exception $e)
        {
            return $this->splash('error',null, $e->getMessage(), true);
        }
        return response::json($brand);exit;
    }

    public function ajaxGetDlytmpls()
    {
        // 获取物流模板列表
        $tmpParams = array(
            'user_id' => $this->userId,
            'status' => 'on',
            'fields' => 'user_id,name,template_id',
        );
        $pagedata = app::get('toptemai')->rpcCall('logistics.temaitmpl.get.list',$tmpParams);
        return response::json($pagedata);exit;
    }

    // 批量更新商品的运费模板
    public function updateItemDlytmpl()
    {
        $postData = input::get();

        try
        {
            if(!$itemIds = $postData['itemids'])
            {
                $msg = app::get('toptemai')->_('请至少选择一个商品！');
                return $this->splash('error',null,$msg, true);
            }
            if(!$dlytmpId = $postData['dlytmpl_id'])
            {
                $msg = app::get('toptemai')->_('没有选择运费模板！');
                return $this->splash('error',null,$msg, true);
            }
            foreach($itemIds as $itemId)
            {
                if($itemId)
                {
                    app::get('toptemai')->rpcCall('temai.item.update.dlytmpl', array('item_id'=>intval($itemId), 'dlytmpl_id'=>intval($dlytmpId),'user_id'=>intval($this->userId)));
                }
            }
        }
        catch(Exception $e)
        {
            return $this->splash('error',null, $e->getMessage(), true);
        }
        $this->sellerlog('批量更新商品关联的运费模板。');
        $url = url::action('toptemai_ctl_item@itemList');
        return $this->splash('success',$url,'更新运费模板成功', true);
    }

}


