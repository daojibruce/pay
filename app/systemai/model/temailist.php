<?php
class systemai_mdl_temailist
{
    public function temaiListPage($params)
    {
        $dayKeyEnd = date('Ymd' , strtotime("+ 1 days"));
        $dayKey = $params['day_key'];
        if($dayKey > $dayKeyEnd){
            $dayKey = date("Ymd");
        }
        $countOfTemai = $this->__countTemai($dayKey);

        $pageSize = $params['page_size'];
        $pageNo = $params['page_no'];
        $offset = ($pageNo-1) * $pageSize;

        $temaiDayList = $this->__getTemaiIdList($offset , $pageSize , $dayKey);
        $temaiList = $this->__getTemaiList($temaiDayList);
        if(empty($temaiList)){
            return false;
        }

        return ['count' => $countOfTemai , 'temailist' => $temaiList];
    }
    
    public function getTemaiList($numOfRecords=20)
    {
        $dayKeyStart = date('Ymd' , strtotime("- 6 days"));
        $dayKeyEnd = date("Ymd" , strtotime("+1 day"));

        $temaiDayList = $this->__getTemaiIdList(0 , $numOfRecords , $dayKeyStart , $dayKeyEnd);
        $temaiList = $this->__getTemaiList($temaiDayList);
        if(empty($temaiList)){
            return false;
        }

        $temaiList = $this->__dayTemaiFormat($temaiDayList , $temaiList);

        return $temaiList;
    }

    /*
     * 获取1 - 7天平台展销数量
     * */
    private function __countTemai($dayKey)
    {
        $filter = array('day' => $dayKey);
        $temaiDayMdlObj = app::get('systemai')->model('temaiday');
        $countTotal = $temaiDayMdlObj->count($filter);

        return $countTotal;
    }

    /*
     * 获取1 - 7天平台展销IDs
     * */
    private function __getTemaiIdList($offset , $numOfRecords , $dayKeyStart , $dayKeyEnd = 0)
    {
        $filter = array('day' => $dayKeyStart);
        if($dayKeyEnd > 0){
            $filter = array('day|between' => [$dayKeyStart , $dayKeyEnd]);
        }

        $temaiDayMdlObj = app::get('systemai')->model('temaiday');
        $temaiIdList = $temaiDayMdlObj->getList("temai_id,day" , $filter , $offset , $numOfRecords , 'day desc');

        return $temaiIdList;
    }

    private function __getTemaiList($temaiDayList)
    {
        if(empty($temaiDayList)){
            return false;
        }
        $temaiIdArr = [];
        foreach ($temaiDayList as $temaiItem){
            array_push($temaiIdArr, $temaiItem['temai_id']);
        }

        if(is_array($temaiIdArr)){
            $temaiIdStr = implode(',', $temaiIdArr);
        }
        if(empty($temaiIdStr)){
            return false;
        }

        $sql = "select
                temai.temai_id,
                temai.item_id,
                temai.title,
                temai.sub_title,
                temai.price,
                temai.mkt_price,
                temai.store,
                temai.state,
                temai.sale_num,
                temai.store - temai.sale_num as store_remain,
                item.cat_id,
                item.image_default_id,
                cat.cat_name
            from
                systemai_temai temai
            left join
                sysitem_item item
            on
                temai.item_id = item.item_id
            left join
                syscategory_cat cat
            on
                item.cat_id = cat.cat_id
            where
                temai.temai_id in ({$temaiIdStr})
            order by
              temai.state asc
        ";

        $db = app::get('systemai')->database();
        $temaiList = $db->executeQuery($sql)->fetchAll();
        return $temaiList;
    }

    /*
     * array:1 [
          0 => array:6 [
            "temai_id" => 1
            "title" => "平台展销商品标题"
            "price" => "123.000"
            "store" => 23
            "cat_id" => 33
            "cat_name" => "连衣裙"
          ]
        ]
     * */
    private function __dayTemaiFormat($temaiDayList , $temaiList)
    {
        $newTemaiList = [];
        $temaiList = array_bind_key($temaiList, 'temai_id');

        foreach ($temaiDayList as $dayItem){
            $temaiId = $dayItem['temai_id'];
            $keyDay = $dayItem['day'];
            if(!isset($newTemaiList[$keyDay])){
                $newTemaiList[$keyDay] = [];
            }

            array_push($newTemaiList[$keyDay], $temaiList[$temaiId]);
        }
        return $newTemaiList;
    }

    //暂时没有调用一周平台展销
    public function top50ByWeek()
    {
        $sevenDays = strtotime(date('Y-m-d',strtotime("-6 days")));
        $sql = "select
                temai.temai_id,
                temai.item_id,
                temai.title,
                temai.sub_title,
                temai.price,
                temai.mkt_price,
                temai.store,
                temai.state,
                temai.sale_num,
                temai.store - temai.sale_num as store_remain,
                temai.tm_starttime,
                item.cat_id,
                item.image_default_id,
                cat.cat_name,
                sku.spec_code
            from
                systemai_temai temai
            left join
                sysitem_item item
            on
                temai.item_id = item.item_id
            left join
                syscategory_cat cat
            on
                item.cat_id = cat.cat_id
            left join
                sysitem_sku sku
            on
                item.item_id = sku.item_id
            where
                temai.state = 1
            and
                store > 0
            and
                temai.tm_starttime <= $sevenDays
            and
                temai.tm_endtime >= $sevenDays
            order by
              temai.temai_id desc
            limit
              50
        ";

        $db = app::get('systemai')->database();
        $temaiList = $db->executeQuery($sql)->fetchAll();

        return $temaiList;
    }

    public function top50ByDay()
    {
        $todayTime = strtotime(date('Y-m-d'));
        $sql = "
            select
                temai.temai_id,
                temai.item_id,
                temai.title,
                temai.sub_title,
                temai.price,
                temai.mkt_price,
                temai.store,
                temai.state,
                temai.sale_num,
                temai.store - temai.sale_num as store_remain,
                temai.tm_starttime,
                item.cat_id,
                item.image_default_id,
                item.unit,
                cat.cat_name,
                sku.spec_code
            from
                systemai_temai temai
            left join
                sysitem_item item
            on
                temai.item_id = item.item_id
            left join
                syscategory_cat cat
            on
                item.cat_id = cat.cat_id
            left join
                sysitem_sku sku
            on
                item.item_id = sku.item_id
            where
                temai.state = 1
            and
                store > 0
            and
                temai.tm_starttime <= $todayTime
            and
                temai.tm_endtime >= $todayTime
            order by
              temai.temai_id desc
            limit
              50
        ";

        $db = app::get('systemai')->database();
        $temaiList = $db->executeQuery($sql)->fetchAll();

        return $temaiList;
    }

    public function top90OrderList()
    {
        $sql="
          select 
            o.oid,
            o.tid,
            o.cat_id,
            o.shop_id,
            o.user_id,
            o.item_id,
            o.sku_id,
            o.title,
            o.modified_time,
            o.status,
            a.user_no
          from 
            systrade_order o
          left join
            sysuser_user a
          on
            o.user_id = a.user_id
          where
            o.temai_user_id
          and
            o.status in ('WAIT_SELLER_SEND_GOODS' , 'WAIT_BUYER_CONFIRM_GOODS' , 'TRADE_BUYER_SIGNED' , 'TRADE_FINISHED','TRADE_CLOSED_BEFORE_PAY')
          order by
            o.oid desc
          limit
            90
        ";

        $db = app::get('systrade')->database();
        $tradeList = $db->executeQuery($sql)->fetchAll();

        return $tradeList;
    }

    //删除平台展销排期
    public function temaidayDelete($temaiIdFilter)
    {
        $temaiDayMdlObj = app::get('systemai')->model('temaiday');
        $count = $temaiDayMdlObj->delete($temaiIdFilter);

        return $count;
    }

    public function topsToday()
    {
        $todayTime = strtotime(date('Y-m-d'));
        $sql = "select
                temai.temai_id,
                temai.item_id,
                temai.title,
                temai.sub_title,
                temai.price,
                temai.mkt_price,
                temai.store,
                temai.state,
                temai.sale_num,
                temai.store - temai.sale_num as store_remain,
                item.cat_id,
                item.image_default_id,
                cat.cat_name
            from
                systemai_temai temai
            left join
                sysitem_item item
            on
                temai.item_id = item.item_id
            left join
                syscategory_cat cat
            on
                item.cat_id = cat.cat_id
            where
                temai.state = 1
            and
                store > 0
            and
                temai.tm_starttime >= $todayTime
            order by
              temai.tm_starttime desc
        ";

        $db = app::get('systemai')->database();
        $temaiList = $db->executeQuery($sql)->fetchAll();

        return $temaiList;
    }

}