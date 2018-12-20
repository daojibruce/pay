<?php
/**
 * 接口作用说明
 * item.search
 */
class systemai_temai_searchItems{

    public $apiDescription = '根据条件获取商品列表';
    
    private function __getFilter($params)
    {
        //平台展销状态
        $filter = array();

        if($params['state'] != ''){
            $filter['state'] = $params['state'];
        }
        if($params['user_id']){
            $filter['user_id'] = $params['user_id'];
        }
        if($params['temai_title'] != ''){
            $filter['title|has'] = $params['temai_title'];
        }
        if($params['min_price']){
            $filter['temai_price|bthan'] = $params['min_price'];
        }
        if($params['max_price'] > 0){
            $filter['temai_price|sthan'] = $params['max_price'];
        }

        return $filter;
    }

    public function getTemaiList($params)
    {
        $limit = $params['page_size'];
        $start = ($params['page_no']-1) * $params['page_size'];

        $filter = $this->__getFilter($params);
        $temaiMdlObj = app::get("systemai")->model("temai");

        $total_found = $this->__countTemai($filter);

        $tmpList = $temaiMdlObj->getList($params['fields'] , $filter , $start , $limit , $params['orderBy']);
        $temaiList = array();
        foreach($tmpList as $index => $temai){
            $itemId = $temai['item_id'];
            $temaiList[$itemId]['temai'] = $temai;
        }
        if($temaiList){
            $this->__getItemDefaultImages($temaiList);
        }

        if ($params['containDetail'] && $temaiList){
            $this->__getTemaiDetailList($temaiList);
        }

        return array('list' => $temaiList , 'total_found' => $total_found);
    }

    private function __getItemDefaultImages(&$temaiList)
    {
        $itemidsArr = array_keys($temaiList);
        $itemMdlObj = app::get("sysitem")->model("item");

        $filter = array("item_id" => $itemidsArr);
        $data = "item_id,image_default_id";
        $itemDefaultImages = $itemMdlObj->getList($data , $filter);
        $itemDefaultImages = array_bind_key($itemDefaultImages, "item_id");

        foreach ($temaiList as $itemId => $temaiRow){
            $temaiList[$itemId]['temai']['image_default_id'] = $itemDefaultImages[$itemId]['image_default_id'];
        }

        return true;
    }

    private function __getTemaiDetailList(&$temaiList)
    {
        $itemidsArr = array_keys($temaiList);

        $filter = array("item_id" => $itemidsArr);
        $detailMdlObj = app::get("systemai")->model("temai_detail");
        $tmpList = $detailMdlObj->getList("*" , $filter);

        foreach ($tmpList as $detailItem) {
            $itemId = $detailItem['item_id'];
            $skuId = $detailItem['sku_id'];

            $temaiList[$itemId]['detail'][$skuId] = $detailItem;
        }

        return true;
    }

    private function __countTemai($filer)
    {
        $temaiMdlObj = app::get("systemai")->model("temai");
        $total = $temaiMdlObj->count($filer);

        return $total;
    }
}
