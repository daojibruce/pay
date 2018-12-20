<?php

/**
 * @brief 商品数据处理
 */
class systemai_data_temai {
    protected $itemMdlObj;
    protected $skuMdlObj;
    protected $temaiMdlObj;
    protected $temaiDetailMdlObj;

    public function __construct()
    {
        $this->itemMdlObj = app::get("sysitem")->model("item");
        $this->skuMdlObj = app::get("sysitem")->model("sku");
        $this->temaiMdlObj = app::get("systemai")->model("temai");
        $this->temaiDetailMdlObj = app::get("systemai")->model("temai_detail");
    }

    /**
     * @brief 平台展销信息更新
     * @author ajx
     * @param $params   array   要更新内容
     * @param $temai_id string
     * @param $msg string 处理结果
     *
     * @return bool
     */
    public function batchUpdateTemai($params, $temai_ids)
    {
        if(! is_array($temai_ids)){
            $temai_ids = explode(",", $temai_ids);
        }
        $filer = array("temai_id" => $temai_ids);
        $temaiMdlObj = app::get('systemai')->model("temai");
        $rs = $temaiMdlObj->update($params , $filer);

        return $rs;
    }

    public function batchPassUpdate($params, $temai_ids, &$msg)
    {
        $temaiItemIds = $this->__temai_data_set($params , $temai_ids);
        if(empty($temaiItemIds)){
            $msg = '平台展销排期有误，与所选平台展销商设定时间冲突';
            return false;
        }
        $this->__sku_price_set($params , $temaiItemIds);
        //$this->__setItemStatus($temaiItemIds);

        $msg = '平台展销审核通过';
        return true;
    }

    public function batchDisableUpdate($params, $temai_ids, &$msg)
    {
        if(! is_array($temai_ids)){
            $temai_ids = explode(",", $temai_ids);
        }
        $filer = array("temai_id" => $temai_ids , 'state' => 1);
        $temaiMdlObj = app::get('systemai')->model("temai");
        $itemIdsList = $temaiMdlObj->getList("item_id" , $filer);
        $itemIdsList = array_column($itemIdsList, 'item_id');
        $temaiMdlObj->update($params , $filer);
        //商品下架
        $this->__setItemStatus($itemIdsList , 'instock');
        $this->__temaiday_delete($temai_ids);

        return true;
    }

    public function batchRefuseUpdate($params, $temai_ids, &$msg)
    {
        if(! is_array($temai_ids)){
            $temai_ids = explode(",", $temai_ids);
        }
        $filer = array("temai_id" => $temai_ids , 'state' => 0);
        $temaiMdlObj = app::get('systemai')->model("temai");
        $itemIdsList = $temaiMdlObj->getList("item_id" , $filer);
        $itemIdsList = array_column($itemIdsList, 'item_id');
        $temaiMdlObj->update($params , $filer);
        //商品下架
        $this->__setItemStatus($itemIdsList , 'instock');
        $this->__temaiday_delete($temai_ids);

        return true;
    }

    /**
     *  判断平台设置的开始时间是否合法
     * */
    private function __temai_data_set($params , $tmids)
    {
        if(! is_array($tmids)){
            $tmids = explode(",", $tmids);
        }

        //处理平台展销上线排期
        $timeStart = strtotime($params['add_time_start']);
        $timeEnd = $timeStart + (86400 * $params['add_days']);
        unset($params['add_time_start']);unset($params['add_days']);

        //查询平台展销数据列表
        $temaiMdlObj = app::get("systemai")->model("temai");
        $tmpList = $this->_get_temai_list($tmids);
        $temaiItemidsArr = array();
        foreach ($tmpList as $row){
            $temaiId = $row['temai_id'];
            //处理排期
            $row['tm_starttime'] = $timeStart;
            $row['tm_endtime'] = $timeEnd;
            if($timeStart < $row['starttime']){
                $row['tm_starttime'] = $row['starttime'];
            }
            if($timeEnd > $row['endtime']){
                $row['tm_endtime'] = $row['endtime'];
            }
            if($row['tm_starttime'] > $row['tm_endtime']){
                continue;
            }
            if(! in_array($row['item_id'] , $temaiItemidsArr)){
                array_push($temaiItemidsArr , $row['item_id']);
            }
            //处理设定价格
            if($params['price_type'] == 1){
                //1：百分比 ， 2:定额增加
                $row['price'] = $row['temai_price'] * ($params['add_price']/100 + 1);
            }else{
                $row['price'] = $row['temai_price'] + $params['add_price'];
            }
            $row['store'] = $params['store'];
            if($params['store'] > $row['store_total']){
                $row['store'] = $row['store_total'];
            }
            $row['cat'] = $params['cat'];
            $row['state'] = $params['state'];

            $filer = array('temai_id' => $temaiId);
            $temaiMdlObj->update($row , $filer);
            $this->__temaiday_set($temaiId , $row['tm_starttime'] , $row['tm_endtime']);
        }

        return $temaiItemidsArr;
    }

    private function _get_temai_list($tmids)
    {
        $temaiField = "temai_id , item_id , title , price , temai_price , starttime , endtime , store_total";
        $temaiFilter = array("temai_id" => $tmids);
        $temaiMdlObj = app::get("systemai")->model("temai");
        $temaiData = $temaiMdlObj->getList($temaiField , $temaiFilter);
        if(empty($temaiData)){
            return false;
        }

        return $temaiData;
    }

    private function __sku_price_set($params , $itemids)
    {
        $skuMdlObj = app::get("systemai")->model("temai_detail");

        $field = 'id,item_id,sku_id,price,temai_price';
        $filter = array("item_id" => $itemids);
        $skuList = $skuMdlObj->getList($field , $filter);
        foreach ($skuList as $sku){
            $filter = array("id" => $sku['id']);
            //处理设定价格
            if($params['price_type'] == 1){
                //1：百分比 ， 2:定额增加
                $sku['price'] = $sku['temai_price'] * ($params['add_price']/100 + 1);
            }else{
                $sku['price'] = $sku['temai_price'] + $params['add_price'];
            }

            $data = array('price' => $sku['price']);
            $skuMdlObj->update($data , $filter);
        }
    }

    private function __temaiday_set($temaiId , $startTime , $endTime)
    {
        $temaidayMdlObj = app::get("systemai")->model('temaiday');
        $this->__temaiday_delete($temaiId);

        for($time=$startTime;$time<=$endTime;$time+=86400){
            $timeKey = date('Ymd',$time);
            $data = array("temai_id" => $temaiId , "day" => $timeKey);
            $temaidayMdlObj->insert($data);
        }

        return true;
    }

    private function __temaiday_delete($temaiId)
    {
        $temaidayMdlObj = app::get("systemai")->model('temaiday');

        $filter = array("temai_id" => $temaiId);
        $temaidayMdlObj->delete($filter);

        return true;
    }

    /**
     * @brief 商品上下架
     * @author Lujy
     * @param $params int itemId
     * @param $status string onsale(上架、出售中) instock(下架、库中)
     * @param $msg bool 处理结果
     *
     * @return bool
     */
    public function setSaleStatus($params)
    {
        $itemId = $params['item_id'];
        $status = $params['approve_status'];
        $ojbMdlItem = app::get('sysitem')->model('item_status');

        $itemRealStore = kernel::single('sysitem_item_redisStore')->getStoreByItemId($itemId, 'realstore');

        if($status=='onsale')
        {
            if( $itemRealStore <= 0 )
            {
                throw new \LogicException('库存为0不能上架');
            }
            $data = array('approve_status' => 'onsale','list_time'=>time());
        }
        if($status=='instock')
        {
            $data = array('approve_status' => 'instock','delist_time'=>time());

            if ($itemId) {
                //判断团购商品是否可以修改
                $activityStatus = app::get('sysitem')->rpcCall('promotion.activity.item.info', ['item_id'=>$itemId, 'valid'=>1]);

                if($activityStatus['status'])
                {
                    $msg = app::get('sysitem')->_('该商品正在活动中不可修改！');
                    throw new \LogicException($msg);
                }
            }
        }

        if ($status=='pending')
        {
            if( $itemRealStore <= 0 )
            {
                throw new \LogicException('库存为0不能提交审核');
            }
            $data = array('approve_status' => 'pending','delist_time'=>time());
        }

        if ($status=='refuse') {
            $data = array('approve_status' => 'refuse','reason'=>$params['reason'],'delist_time'=>time());
        }

        if($params['item_id']){
            $result = $ojbMdlItem->update($data, array('item_id' => intval($itemId) ) );
        }else{
            $result = $ojbMdlItem->update($data, array('approve_status|nohas' => 'onsale' ) );
        }


        if($result)
        {
            return true;
        }
        else
        {
            $status == 'onsale' ? $msg = app::get('sysitem')->_('商品上架失败') : $msg = app::get('sysitem')->_('商品下架失败');
            throw new \LogicException($msg);
        }
    }

    private function __setItemStatus($itemIds  , $status = 'onsale')
    {
        $result = false;
        if(in_array($status,['onsale' , 'instock'])){
            $data = ['approve_status' => $status];
            $ojbMdlItem = app::get('sysitem')->model('item_status');
            $result = $ojbMdlItem->update($data, array('item_id' => $itemIds ) );
        }

        return $result;
    }

    // 添加商品（包括单品和多规格）,必须通过添加、修改商品接口调用此方法，因为许多验证是在接口做的，这里省略了
    function add($tmItemId , $tmItemSku , $userId , $imgFile = null)
    {
        $currentNum = 0;
        $overflowNum = $this->__temai_day_limit($tmItemId , $currentNum);
        if($overflowNum < 0){
            return $overflowNum;
        }

        $itemList = $this->__getItemListByIds($tmItemId);
        $skuList = $this->__getSkuListByIds($tmItemSku);

        $temaiId = $this->__addTemaiEach($itemList , $skuList , $userId , $imgFile);
        if($temaiId){
            $this->__temai_day_set_limit($totalNum);
        }

        return $temaiId;
    }

    private function __temai_day_limit($itemId, &$currentTotalNum)
    {
        //读取限制的数量
        $limitApply = app::get("site")->getConf("temai.limit_apply");

        //已经推送的数量
        $baseUnique = kernel::single('base_unique');
        $currentNum = $baseUnique->get_number_day('limit_temai');

        //本次推送的商品之中，与之前重复的数量
        $itemIdArr = array_keys($itemId);
        $currentTotalNum = count($itemIdArr) + $currentNum;
        $filter = array('item_id' => $itemIdArr);
        $itemNum = app::get('systemai')->model('temai')->count($filter);
        $currentTotalNum -= $itemNum;

        return ($limitApply - $currentTotalNum);
    }

    private function __temai_day_set_limit($totalNum)
    {
        //已经推送的数量
        $baseUnique = kernel::single('base_unique');
        $currentTotalNum = $baseUnique->set_number_day('limit_temai', $totalNum);

        return $currentTotalNum;
    }

    /*
     * 从数据库取出商品列表
     * */
    private function __getItemListByIds($tmItemId)
    {
        $tmItemId = array_keys($tmItemId);
        $filter = array("item_id" => $tmItemId);
        $itemList = $this->itemMdlObj->getList("*" , $filter);

        return $itemList;
    }

    private function __getSkuListByIds($tmItemSku)
    {
        $tmItemSkuList = $this->__getSkuList($tmItemSku);
        $tmItemSkuId = array_keys($tmItemSkuList);
        $filter = array("sku_id" => $tmItemSkuId);
        $tmpSkuList = $this->skuMdlObj->getList("*" , $filter);
        $itemSkuList = array();
        foreach($tmpSkuList as $index => $skuInfo){
            $itemId = $skuInfo['item_id'];
            $skuId = $skuInfo['sku_id'];
            $tmSkuInfo = $tmItemSkuList[$skuId];

            $itemSkuList[$itemId][$skuId] = $skuInfo;
            $itemSkuList[$itemId][$skuId]['tm_price'] = $tmSkuInfo['price'];
            $itemSkuList[$itemId][$skuId]['tm_mkt_price'] = $tmSkuInfo['mkt_price'];
            $itemSkuList[$itemId][$skuId]['tm_store'] = $tmSkuInfo['store'];
            $itemSkuList[$itemId][$skuId]['tm_expiry'] = $tmSkuInfo['expiry'];
        }

        return $itemSkuList;
    }

    private function __addTemaiEach( $itemList , $skuList , $userId , $imgFile = null)
    {
        if(empty($itemList) || empty($skuList) || empty($userId)){
            return false;
        }

        $temaiItemList = array();
        foreach($skuList as $itemId => $subSkuList){
            foreach ($subSkuList as $skuInfo){
                $itemInfo = $itemList[$itemId];

                //处理 开始时间  -  结束时间
                $expiryArr = explode("-",$skuInfo['tm_expiry']);
                $starttime = strtotime($expiryArr[0]);
                $endtime = strtotime($expiryArr[1]);

                $temaiItemList[$itemId]['starttime'] = $starttime;
                $temaiItemList[$itemId]['endtime'] = $endtime;
                if(!isset($temaiItemList[$itemId]['temai_price'])){
                    $temaiItemList[$itemId]['temai_price'] = $skuInfo['tm_price'];
                }else if($temaiItemList[$itemId]['temai_price'] > $skuInfo['tm_price']){
                    $temaiItemList[$itemId]['temai_price'] = $skuInfo['tm_price'];
                }
                if(!isset($temaiItemList[$itemId]['mkt_price'])){
                    $temaiItemList[$itemId]['mkt_price'] = $skuInfo['tm_mkt_price'];
                }else if($temaiItemList[$itemId]['mkt_price'] > $skuInfo['tm_mkt_price']){
                    $temaiItemList[$itemId]['mkt_price'] = $skuInfo['tm_mkt_price'];
                }
                $temaiItemList[$itemId]['store'] += $skuInfo['tm_store'];

                $temaiId = 0;
                $temaiInfo = array();
                $temaiInfo['item_id'] = $itemId;
                $temaiInfo['sku_id'] = $skuInfo['sku_id'];
                $temaiInfo['temai_price'] = $skuInfo['tm_price'];
                $temaiInfo['mkt_price'] = $skuInfo['tm_mkt_price'];
                $temaiInfo['store'] = $skuInfo['tm_store'];
                $temaiInfo['store_total'] = $skuInfo['tm_store'];

                $filter = array('item_id'=>$itemId , 'sku_id'=> $skuInfo['sku_id']);
                $temaiDetailItem = $this->__temaiDetailExists($filter);
                if($temaiDetailItem['id']){
                    $temaiId = $temaiDetailItem['id'];
                    $this->temaiDetailMdlObj->update($temaiInfo , $filter);
                }else{
                    $temaiId = $this->temaiDetailMdlObj->insert($temaiInfo);
                }
            }
        }

        $itemList = array_bind_key($itemList, 'item_id');
        foreach($itemList as $itemId => $itemInfo){
            $itemId = $itemInfo['item_id'];
            $temaiItemInfo = $temaiItemList[$itemId];

            $temaiArr = array();
            $temaiArr['user_id'] = $userId;
            $temaiArr['item_id'] = $itemInfo['item_id'];
            $temaiArr['title'] = $itemInfo['title'];
            $temaiArr['sub_title'] = $itemInfo['sub_title'];
            $temaiArr['temai_price'] = $temaiItemInfo['temai_price'];
            $temaiArr['mkt_price'] = $temaiItemInfo['mkt_price'];
            $temaiArr['store_total'] = $temaiItemInfo['store'];
            $temaiArr['starttime'] = $temaiItemInfo['starttime'];
            $temaiArr['endtime'] = $temaiItemInfo['endtime'];
            $temaiArr['state'] = 0;
            $temaiArr['addtime'] = time();
            $temaiArr['lasttime'] = $temaiArr['addtime'];
            $temaiArr['batch'] = $this->__countBatch($userId);
            $temaiArr['agreement'] = implode(',',$imgFile['agreement']);
            $temaiArr['certificate'] = implode(',',$imgFile['certificate']);
            $temaiArr['pricecerti'] = implode(',',$imgFile['pricecerti']);

            $filter = array('user_id'=>$userId , 'item_id'=>$itemId);
            $temaiItem = $this->__temaiExists($filter);
            if($temaiItem['temai_id']){
                $temaiId = $temaiItem['temai_id'];
                if($temaiItem['state']){
                    continue;
                }else{
                    unset($temaiArr['batch']);
                    $this->temaiMdlObj->update($temaiArr , $filter);
                }
            }else{
                $temaiId = $this->temaiMdlObj->insert($temaiArr);
            }
        }

        // 返回商品ID
        return $temaiId;
    }

    private function __countBatch($userId)
    {
        $filter = array("user_id" => $userId);
        $batch = $this->temaiMdlObj->count($filter);
        $batch++;

        return $batch;
    }

    private function __getSkuList($tmItemSku)
    {
        if(empty($tmItemSku)){
            return false;
        }

        $skuList = array();
        foreach ($tmItemSku as $itemList){
            foreach($itemList as $skuId => $skuInfo){
                $skuList[$skuId] = $skuInfo;
            }
        }

        return $skuList;
    }

    private function __temaiExists($where)
    {
        $temaiItem = $this->temaiMdlObj->getRow("temai_id,state" , $where);

        return $temaiItem;
    }

    private function __temaiDetailExists($where)
    {
        $temaiItem = $this->temaiDetailMdlObj->getRow("id" , $where);

        return $temaiItem;
    }

    public function getList($userId)
    {
        $filter = array("user_id" => $userId);
        $temaiList = $this->temaiMdlObj->getList("*" , $filter);

        return $temaiList;
    }
}