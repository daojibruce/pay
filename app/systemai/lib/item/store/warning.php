<?php

/**
 * import.php 
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class systemai_item_store_warning {

    protected $userId;
    
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    //查看库存报警阀值
    public function getStorePolice($params)
    {
        $storePolice = app::get('sysshop')->model('store_police');
        if(!$params['fields'])
        {
            $params['fields'] = '*';
        }
        $storePolice = $storePolice->getRow($params['fields'],array('user_id'=>$params['user_id']));
        return  $storePolice;
    }

    public function storePolice($params)
    {
        if($params['fields'])
        {
            $row = $params['fields'];
        }
        else
        {
            $row = '*';
        }

        $filter['store'] = $params['store'];
        $filter['user_id'] = $params['user_id'];

        //分页使用
        $itemCount = kernel::single('systemai_item_store')->getItemCountByStore($filter);
        $pageTotal = ceil($itemCount/$params['page_size']);
        $page =  $params['page_no'] ? $params['page_no'] : 1;
        $limit = $params['page_size'] ? $params['page_size'] : 40;
        $currentPage = ($pageTotal && $pageTotal < $page) ? $pageTotal : $page; //防止传入错误页面，返回最后一页信息
        $offset = ($currentPage-1) * $limit;

        //排序
        $orderBy = $params['orderBy'];
        if(!$params['orderBy'])
        {
            $orderBy = "modified_time desc,list_time desc";
        }

        $itemList = kernel::single('systemai_item_store')->getItemListByStore($row,$filter,$offset, $limit,$orderBy);
        $data['list'] = $itemList;
        $data['total_found'] = $itemCount;
        return $data;
        //echo '<pre>';print_r($itemList);exit();
    }

}
 