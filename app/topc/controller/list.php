<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topc_ctl_list extends topc_controller {

    /**
     * 每页搜索多少个商品
     */
    public $limit = 20;

    /**
     * 最多搜索前100页的商品
     */
    public $maxPages = 100;

    /**
     * 最多搜索前4个店铺
     */
    public $shop_limit = 4;

    /**
     * 设置进入列表页的初始条件，用于查询渐进式筛选
     * 临时用于虚拟分类
     */
    private function __setInitFilter(&$params)
    {
        if( input::get('virtual_cat_id') )
        {
            $catinfo = app::get('topc')->rpcCall('category.virtualcat.info',array('virtual_cat_id'=>intval($params['virtual_cat_id'])));
            $initFilter = unserialize($catinfo['filter']);
            if( $initFilter['brand_id'] )
            {
                $initFilter['init_brand_id'] = implode(',',$initFilter['brand_id']);
                unset($initFilter['brand_id']);
            }

            if( $params['cat_id'] && !$initFilter['cat_id'] )
            {
                $initFilter['cat_id'] = $params['cat_id'];
            }

            if( $params['brand_id'] )
            {
                $initFilter['brand_id'] = is_array($params['brand_id']) ? implode(',',$params['brand_id']) : $params['brand_id'];
            }

            $params = array_merge($initFilter, $params);
        }
        elseif( $params['search_keywords'] )
        {
            $initFilter['search_keywords'] = $params['search_keywords'];
            $initFilter['cat_id']          = $params['cat_id'];
            $initFilter['brand_id']        = is_array($params['brand_id']) ? implode(',',$params['brand_id']) : $params['brand_id'];
        }
        elseif( $params['cat_id'] )
        {
            $initFilter['cat_id'] = $params['cat_id'];
        }

        return $initFilter;
    }

    /**
     * 获取查询商品条件
     */
    private function __getDecodeFilter()
    {
        $objLibFilter              = kernel::single('topc_item_filter');
        $postdata                  = input::get();
        $params                    = $objLibFilter->decode($postdata);
        $params['use_platform']    = '0,1';
        $params['search_keywords'] = parseSearchKeyWord(trim($params['search_keywords']));
        return $params;
    }

    public function index()
    {
        $this->setLayoutFlag('gallery');
        $objLibFilter = kernel::single('topc_item_filter');

        $params     = $this->__getDecodeFilter();
        $pagedata['curType'] = $params['k'];

        //判断自营  自营是1，非自营是0
        if($params['is_selfshop']=='1')
        {
            $pagedata['isself'] = '0';
        }
        else
        {
            $pagedata['isself'] = '1';
        }

        //已选择的搜索条件
        $pagedata['activeFilter'] = $params;

        $initFilter = $this->__setInitFilter($params);
        if( !$initFilter )
        {
            return redirect::back();
        }

        //默认图片
        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');

        //搜索或者筛选获取商品
        $searchParams = $this->__preFilter($params);

        //根据条件搜索出最多商品的分类，进行显示渐进式筛选项
        $filterItems = app::get('topc')->rpcCall('item.search.filterItems',$initFilter);
        //渐进式筛选的数据
        $pagedata['screen'] = $filterItems;
        if( $filterItems && !$filterItems['props'])
        {
            unset($pagedata['activeFilter']['prop_index']);
            unset($searchParams['prop_index']);
        }

        //已有的搜索条件
        $tmpFilter = $pagedata['activeFilter'];
        unset($tmpFilter['pages']);
        $pagedata['filter'] = $objLibFilter->encode($tmpFilter);

        //面包屑数据
        $breadcrumb = array();
        if($searchParams['cat_id'] )
        {
            $cat = app::get('topc')->rpcCall('category.cat.get.data',array('cat_id'=>intval($searchParams['cat_id'])));
            $breadcrumb = array(
                ['url'=>url::action('topc_ctl_topics@index',array('cat_id'=>$cat['lv1']['cat_id'])),'title'=>$cat['lv1']['cat_name']],
                ['url'=>url::action('topc_ctl_topics@index',array('cat_id'=>$cat['lv2']['cat_id'])),'title'=>$cat['lv2']['cat_name']],
                ['url'=>url::action('topc_ctl_list@index',array('cat_id'=>$cat['lv3']['cat_id'])),'title'=>$cat['lv3']['cat_name']],
            );
            if($searchParams['brand_id'])
            {
                $brands = app::get('topc')->rpcCall('category.brand.get.list',array('brand_id'=>$searchParams['brand_id'],'fields'=>'brand_id,brand_name'));
                $title = (count($brands) >1) ? "品牌：" : '';
                foreach($brands as $brand)
                {
                    $title .= $brand['brand_name']."、";
                }
                $title = rtrim($title,"、");
                $breadcrumb[] = ['url'=>'','title'=>$title];
            }
        }elseif($searchParams['virtual_cat_id']){
            $virtualcat = app::get('topc')->rpcCall('category.virtualcat.getData',array('virtual_cat_id'=>intval($searchParams['virtual_cat_id'])));
            $breadcrumb = array(
                ['url'=>url::action('topc_ctl_topics@index',array('virtual_cat_id'=>$virtualcat['lv1']['virtual_cat_id'])),'title'=>$virtualcat['lv1']['virtual_cat_name']],
                ['url'=>url::action('topc_ctl_topics@index',array('virtual_cat_id'=>$virtualcat['lv2']['virtual_cat_id'])),'title'=>$virtualcat['lv2']['virtual_cat_name']],
                ['url'=>url::action('topc_ctl_list@index',array('virtual_cat_id'=>$virtualcat['lv3']['virtual_cat_id'])),'title'=>$virtualcat['lv3']['virtual_cat_name']],
            );
        }

        if($searchParams['search_keywords'])
        {
            $breadcrumb = array(
                ['url'=>'','title'=>'全部商品'],
                ['url'=>'','title'=>$searchParams['search_keywords']],
            );
        }
        $pagedata['breadcrumb'] = $breadcrumb;


        $searchParams['fields'] = 'item_id,title,sub_title,image_default_id,price,promotion';
        $search_keywords = $searchParams['search_keywords'];
        //搜索品牌，关键字只用来过滤品牌
        if($params['k'] == 'brands'){
            $brandMdlObj = app::get('syscategory')->model('brand');
            $brandIdList = $brandMdlObj->getBrandIdsBySearch($params['search_keywords']);
            $searchParams['brand_id'] = implode(',', $brandIdList);
            unset($searchParams['search_keywords']);
        }else if($params['k'] == 'temai'){
            //搜索平台展销时，只需要平台展销会员ID大于零，即表示该商品为平台展销商品
            $searchParams['user_id|than']=0;
        }

        try
        {
            $itemsList = app::get('topc')->rpcCall('item.search',$searchParams);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }
        $searchParams['search_keywords'] = $search_keywords;

        //检测是否有参加团购活动
        if($itemsList['list'])
        {
            $itemsList['list'] = array_bind_key($itemsList['list'],'item_id');
            $itemIds = array_keys($itemsList['list']);
            $activityParams['item_id'] = implode(',',$itemIds);
            $activityParams['status'] = 'agree';
            $activityParams['end_time'] = 'bthan';
            $activityParams['start_time'] = 'sthan';
            $activityParams['fields'] = 'activity_id,item_id,activity_tag,price,activity_price';
            $activityItemList = app::get('topc')->rpcCall('promotion.activity.item.list',$activityParams);
            if($activityItemList['list'])
            {
                foreach($activityItemList['list'] as $key=>$value)
                {
                    $itemsList['list'][$value['item_id']]['activity'] = $value;
                    $itemsList['list'][$value['item_id']]['activity_price'] = $value['activity_price'];
                }
            }
        }

        if ($params['k'] == 'shops') {
            //搜索店铺信息
            $objDataShop = kernel::single('sysshop_data_shop');
            $row ="shop_id,shop_name,shop_descript,shop_type,status,open_time,shop_logo,shop_area,shop_addr,shop_name,shop_type";
            $filter = array(
                'shop_name|has' => $params['search_keywords'],
                'status'=>'active',//只查询活动中的店铺
            );
            $filterItems_news2 = $objDataShop->fetchListShopInfo($row,$filter);
            $pagedata['count'] = count($filterItems_news2); //根据条件搜索到的总数
            $filterItems_news = $objDataShop->fetchListShopInfo($row,$filter,($params['pages']-1)*$this->shop_limit,$this->shop_limit);
            if ($filterItems_news) {
                foreach ($filterItems_news as $key => $shop) {
                    //app::get('sysitem')->model('item')->getList();
                    $par['page_size'] = 4;
                    $par['shop_id'] = $shop['shop_id'];
                    $par['fields'] = $searchParams['fields'];
                    $par['approve_status'] = 'onsale';
                    $par['buildExcerpts'] = 1;
                    $items = app::get('topc')->rpcCall('item.search',$par);
                    $filterItems_news[$key]['items'] = $items;
                }
            }

            $pagedata['shops'] = $filterItems_news; //根据条件搜索到的总数
            $pagedata['pagers'] = $this->__page($params['pages'], $pagedata['count'], $pagedata['filter']);
            return $this->page('topc/list/shop_index.html', $pagedata);
        } else {
            $pagedata['items'] = $itemsList['list'];//根据条件搜索出的商品
            $pagedata['count'] = $itemsList['total_found']; //根据条件搜索到的总数
//echo "<pre>";print_r($itemsList);exit;
            //分页
            $pagedata['pagers'] = $this->__pages($params['pages'], $pagedata['count'], $pagedata['filter']);
            return $this->page('topc/list/index.html', $pagedata);
        }

    }

    /**
     * 将post过的数据转换为搜索需要的参数
     *
     * @param array $params
     */
    private function __preFilter($params)
    {
        $searchParams = $params;
        $searchParams['page_no'] = ($params['pages'] >=1 || $params['pages'] <= 100) ? $params['pages'] : 1;
        $searchParams['page_size'] = $this->limit;

        $searchParams['approve_status'] = 'onsale';
        $searchParams['buildExcerpts'] = true;

        if( $searchParams['brand_id'] && is_array($searchParams['brand_id']) )
        {
            $searchParams['brand_id'] = implode(',', $searchParams['brand_id']);
        }

        if( $searchParams['prop_index'] && is_array($searchParams['prop_index']) )
        {
            $searchParams['prop_index'] = implode(',', $searchParams['prop_index']);
        }

        //排序
        if( !$params['orderBy'] )
        {
            $params['orderBy'] = 'sold_quantity desc';
        }
        $searchparams['orderBy'] = $params['orderBy'];

        return $searchParams;
    }

    /**
     * 分页处理
     * @param int $current 当前页
     * @return int $total  总页数
     * @return array $filter 查询条件
     *
     * @return $pagers
     */
    private function __pages($current, $total, $filter)
    {
        //处理翻页数据
        $current = ($current || $current <= 100 ) ? $current : 1;
        $filter['pages'] = time();

        if( $total > 0 ) $totalPage = ceil($total/$this->limit);
        $pagers = array(
            'link'=>url::action('topc_ctl_list@index',$filter),
            'current'=>$current,
            'total'=>$totalPage,
            'token'=>time(),
        );
        return $pagers;
    }

    //处理店铺搜索翻页问题
    private function __page($current, $total, $filter)
    {
        //处理翻页数据
        $current = ($current || $current <= 100 ) ? $current : 1;
        $filter['pages'] = time();
        if( $total > 0 ) $totalPage = ceil($total/$this->shop_limit);
        $pagers = array(
            'link'=>url::action('topc_ctl_list@index',$filter),
            'current'=>$current,
            'total'=>$totalPage,
            'token'=>time(),
        );
        return $pagers;
    }

    //店铺列表
    public function shopList()
    {
        $shopMdl = app::get('sysshop')->model('shop');

        $filter = array(
            'status' => 'active',
        );
        $shopData = $shopMdl->getList('shop_id,shop_name,shop_logo,firstLetter', $filter);

        if(!$shopData) {
            $pagedata['valid'] = 1;
        }

        $pagedata['shops'] = $this->sortByName($shopData);
        foreach ($pagedata['shops'] as $key=>$val) {
            $pagedata['tips'][] = $key;
        }
        //echo "<pre>";print_r($pagedata);exit;
        return $this->page('topc/list/list.html', $pagedata);
    }

    //店铺按首字母排序
    public function sortByName($shopData)
    {
        $letterList = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','0-9');
        $brands = [];
        foreach ($letterList as $v) {
            $brands[$v] = array();
            foreach ($shopData as $brand) {
                if ($v == $brand['firstLetter']) {
                    $brands[$v][] = $brand;
                }
            }
        }

        return $brands;
    }

}

