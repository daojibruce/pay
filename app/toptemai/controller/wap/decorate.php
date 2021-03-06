<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class toptemai_ctl_wap_decorate extends toptemai_controller {

    public $headerTitle = [
        'wapslider' => ['default'=>'轮播广告页面配置'],
        'waptags' => ['default'=>'标签配置'],
        'wapshowitems' => ['default'=>'商品展示配置'],
        'wapimageslider' => ['default'=>'图片广告页配置(不支持APP)'],
        'wapcustom' => ['default'=>'自定义配置'],
        'waplogo' => ['default'=>'店铺招牌配置'],
    ];

    //显示装修店铺主页面
    public function index()
    {
        $confgData = shopWidgets::config();
        $sort = app::get('toptemai')->getConf('wap_decorate.sort');
        $data = unserialize($sort);
        if($data&&(count($data)==count($confgData['wap']['params'])))
        {
            foreach ($data as $key => $value)
            {
                $data[$key]['title'] = $confgData['wap']['params'][$key]['title'];
                $data[$key]['dialog'] = $confgData['wap']['params'][$key]['dialog'];
            }
        }
        else
        {
            $data = $confgData['wap']['params'];
        }
        $pagedata['config'] = $data;

        $this->contentHeaderTitle = app::get('toptemai')->_('店铺装修');
        return $this->page('toptemai/shop/wap/decorate.html', $pagedata);
    }
    //保存排序
    public function saveSort()
    {
        $sort = $_POST;

        if($sort['tag'])
        {
            app::get('toptemai')->setConf('wap_decorate.tagSort',serialize($sort['tag']));
        }
        elseif($sort['showItem'])
        {
            app::get('toptemai')->setConf('wap_decorate.showItemSort',serialize($sort['showItem']));
        }
        else
        {
            app::get('toptemai')->setConf('wap_decorate.sort',serialize($sort));
        }
        $msg = app::get('toptemai')->_('保存成功');
        return $this->splash('success',null,$msg,true);
    }
    //排序
    public function array_sort($arr,$keys,$type='asc')
    {
        $keysvalue = $new_array = array();
        foreach ($arr as $k=>$v)
        {
            $keysvalue[$k] = $v[$keys];
        }
        if($type == 'asc')
        {
            asort($keysvalue);
        }
        else
        {
            arsort($keysvalue);
        }
        reset($keysvalue);
        foreach ($keysvalue as $k=>$v)
        {
            $new_array[$k] = $arr[$k];
        }
        return $new_array;
    }

    //页面显示
    public function dialog()
    {
        $confgData = shopWidgets::config();

        $widgetsName = input::get('widgets');
        $dialogName = input::get('dialog');
        $title = $confgData['wap']['params'][$widgetsName]['title'];

        try {
            $dialogData =  shopWidgets::getWapInfo($widgetsName, $this->shopId);
            //特殊处理 广告配置 标签配置
            if($widgetsName=='waptags')
            {
                $sort = unserialize(app::get('toptemai')->getConf('wap_decorate.tagSort'));
                $tagData = $this->__getData($dialogData,$sort);
                $pagedata['data'] = $tagData;
            }
            elseif($widgetsName=='wapshowitems')
            {
                $sort = unserialize(app::get('toptemai')->getConf('wap_decorate.showItemSort'));
                $showItemData = $this->__getData($dialogData,$sort);
                $pagedata['data'] = $showItemData;
            }
            elseif($widgetsName=='wapimageslider')
            {
                $pagedata['data'] = $dialogData;
                $pagedata['countimageslider'] = count($dialogData[0]['params']);
            }
            else
            {
                $pagedata['data'] = $dialogData;
            }
        } catch (Exception $e) {
            #echo $e->getMessage();
        }
        $pagedata['widgetsName'] = $widgetsName;
        $pagedata['dialogName'] = $dialogName;
        //echo '<pre>';print_r($pagedata);exit();
        $this->contentHeaderTitle = app::get('toptemai')->_('店铺装修>'.$title);
        return $this->page("toptemai/shop/wap/widgets/{$widgetsName}/{$dialogName}.html", $pagedata);
    }
    //特殊处理 广告配置 标签配置
    private function __getData($pageData,$sort)
    {
        if($sort)
        {
            foreach ($pageData as $key => $value)
            {
                $pageData[$key]['order_sort'] = $sort[$value['widgets_id']]['order_sort'];
            }
            $dataInfo = $this->array_sort($pageData,'order_sort');
            return $dataInfo;
        }
        else
        {
            return $pageData;
        }
    }

    //标签添加
    public function addTags()
    {
        $confgData = shopWidgets::config();
        $widgetsName = input::get('widgets');
        $dialogName = input::get('dialog');
        $widgetsId = intval(input::get('widgetsId'));
        $title = $confgData['wap']['params'][$widgetsName]['title'];
        $pagedata['widgetsName'] = $widgetsName;
        $pagedata['widgetsId'] = $widgetsId;
        $pagedata['dialogName'] = $dialogName;
        $pagedata['shopCatList'] = json_decode($this->getCatList(),true);
        try
        {   if($widgetsId)
            {
                $dialogData =  shopWidgets::getWapInfo($widgetsName,$this->shopId,$widgetsId);
                foreach ($dialogData[0]['params']['item_id'] as $key => $value)
                {
                    $item_id .= $value.',';
                }
                $searchParams['item_id'] = rtrim($item_id, ",");
                $searchParams['fields'] = 'item_id,title,image_default_id,price';
                $itemsList = app::get('toptemai')->rpcCall('item.search',$searchParams);
                $data['params'] = $dialogData[0]['params'];
                $data['params']['itemlist'] = $itemsList;
                $pagedata['data'] = $data;

                $notEndItem = $dialogData[0]['params']['item_id'];

                $pagedata['notEndItem'] = json_encode($notEndItem,true);

                if($dialogData[0]['widgets_type'] == 'wapshowitems'){
                    $pagedata['itemsList'] = $itemsList['list'];
                    $selectedItems = array_column($pagedata['itemsList'], 'item_id');
                    $pagedata['notEndItem'] =  json_encode($selectedItems,true);
                }

            }
        }
        catch (Exception $e)
        {
            #echo $e->getMessage();
        }

        $this->contentHeaderTitle = app::get('toptemai')->_('店铺装修>'.$title);
        if (view::exists("toptemai/shop/wap/widgets/{$widgetsName}/add.html"))
        {
            return $this->page("toptemai/shop/wap/widgets/{$widgetsName}/add.html", $pagedata);
        }
        else
        {
            return redirect::action('toptemai_ctl_wap_decorate@index');
        }
    }


    //保存配置
    public function save()
    {
        try
        {
            $postData = $this->__checkData(input::get());

        }
        catch (Exception $e)
        {
            return $this->splash('error', null, $e->getMessage(), true);
        }

        try
        {
            shopWidgets::saveWap($postData['widgetsId'],$postData['widgets'],$postData['params']);
        }
        catch (Exception $e)
        {
            return $this->splash('error', null, $e->getMessage(), true);
        }

        $url = url::action('toptemai_ctl_wap_decorate@dialog',array('widgets'=>$postData['widgets'],'dialog'=>$postData['dialog']));

        $this->sellerlog('wap店铺装修。编辑 '.$this->headerTitle[input::get('widgets')]['default']);
        $msg = app::get('toptemai')->_('保存成功');
        return $this->splash('success',$url,$msg,true);
    }
    private function __checkData($data)
    {
        if($data['widgets']=='waptags'||$data['widgets']=='wapshowitems')
        {
            if($data['widgets']=='wapshowitems'){
                $data['params']['item_id'] = $data['item_id'];
            }
            if(empty($data['params']['tagsname']))
            {
                throw new \LogicException(app::get('toptemai')->_('模块名称不能为空!'));
            }
            if(!$data['params']['item_id'])
            {
                throw new \LogicException(app::get('toptemai')->_('添加商品不能为空!'));
            }


            if($data['widgets']=='wapshowitems'&&empty($data['widgetsId']))
            {
                $dialogData =  shopWidgets::getWapInfo($data['widgets'],$this->shopId,null);
                if(count($dialogData)>=5)
                {
                    $msg = app::get('toptemai')->_('前台商品配置项不能超过5个!');
                    throw new \LogicException($msg);
                }

            }
            if($data['widgets']=='waptags'&&empty($data['widgetsId']))
            {
                $dialogData =  shopWidgets::getWapInfo($data['widgets'],$this->shopId,null);
                if(count($dialogData)>=10)
                {
                    $msg = app::get('toptemai')->_('前台标签配置项不能超过10个!');
                    throw new \LogicException($msg);
                }
                $data['params']['itemlimit'] = $data['itemlimit'] <= 20 ? $data['itemlimit']:20;
            }
            $data['params']['itemlimit'] = $data['itemlimit'] <= 20 ? $data['itemlimit']:20;
            //echo '<pre>';print_r($data);exit();
            $data['params']['isstart'] = $data['isstart'];
            $data['params']['ordersort'] = $data['ordersort'];
            $data['params']['item_id'] = $data['params']['item_id'];

            if($data['isstart'])
            {
                try
                {
                    $this->__checkOpenTagsNum($data);
                }
                catch (Exception $e)
                {
                    throw new \LogicException($e->getMessage());
                }
            }
        }
        elseif ($data['widgets']=='wapslider')
        {
            if(count($data['params'])>10)
            {
                $msg = app::get('toptemai')->_('最多为10个');
                throw new \LogicException($msg);
                //return $this->splash('error',"",$msg,true);
            }
        }
        elseif($data['widgets']=='wapimageslider')
        {
            if($data['params']&&count($data['params'])>4)
            {
                $msg = app::get('toptemai')->_('最多为4个');
                throw new \LogicException($msg);
            }
            if(!$data['params'])
            {
                $data['params'] = '';
            }
        }
        return $data;
    }
    //挂件删除
    public function ajaxWidgetsDel()
    {
        $widgetsId = input::get('widgetsId');
        $widgetsName = input::get('widgetsName');
        $dialogName = input::get('dialogName');
        try
        {
            shopWidgets::delWapInfo($widgetsId,$this->shopId,$widgetsName);
        }
        catch (Exception $e)
        {
            return $this->splash('error', null, $e->getMessage(), true);
        }

        $url = url::action('toptemai_ctl_wap_decorate@dialog',array('widgets'=>$widgetsName,'dialog'=>$dialogName));
        $msg = app::get('toptemai')->_('删除成功');
        return $this->splash('success',$url,$msg,true);

    }
    //开启标签
    public function openTags()
    {
        $widgetsInfo = input::get();
        $dialogData =  shopWidgets::getWapInfo($widgetsInfo['widgets'],$this->shopId,$widgetsInfo['widgetsId']);
        try
        {
            $this->__checkOpenTagsNum($widgetsInfo);
        }
        catch (Exception $e)
        {
            return $this->splash('error', null, $e->getMessage(), true);
        }
        foreach ($dialogData as $key => $value)
        {
            $value['params']['isstart'] = 1;
            try
            {
                shopWidgets::saveWap($value['widgets_id'],$value['widgets_type'],$value['params']);
            }
            catch (Exception $e)
            {
                return $this->splash('error', null, $e->getMessage(), true);
            }
        }
        $url = url::action('toptemai_ctl_wap_decorate@dialog',array('widgets'=>$widgetsInfo['widgets'],'dialog'=>$widgetsInfo['dialog']));
        $msg = app::get('toptemai')->_('修改成功');
        return $this->splash('success',$url,$msg,true);
    }
    //检查标签开启的数量
    private function __checkOpenTagsNum($widgetsInfo)
    {
        if($widgetsInfo['widgets'])
        {
            $dialogData = shopWidgets::getWapInfo($widgetsInfo['widgets'],$this->shopId,null);
            $count = 0;
            foreach ($dialogData as $key => $value)
            {
                if($value['params']['isstart'] == 1)
                {
                    $count++;
                    $widgetsId[] = $value['widgets_id'];
                }
            }

            if(is_array($widgetsInfo['widgetsId']))
            {
                foreach ($widgetsInfo['widgetsId'] as $key => $value)
                {
                    if(in_array($value,$widgetsId))
                    {
                        unset($widgetsInfo['widgetsId'][$key]);
                    }
                }
            }
            else
            {
                foreach ($widgetsId as $key => $value)
                {
                    if($value == $widgetsInfo['widgetsId'])
                    {
                        unset($widgetsId[$key]);
                    }
                }
            }

            $allCount = count($widgetsId)+count($widgetsInfo['widgetsId']);
            if($allCount>5)
            {
                throw new \LogicException(app::get('toptemai')->_('最多开启5个标签!'));
            }
        }
        else
        {
            throw new \LogicException(app::get('toptemai')->_('挂件名称不能为空!'));
        }
    }
    //商品展示配置检查
    public function ajaxCheckShowItems()
    {
        $widgetsInfo = input::get();
        $dialogData =  shopWidgets::getWapInfo($widgetsInfo['widgets'],$this->shopId,null);
        if(count($dialogData)>=5)
        {
            $msg = app::get('toptemai')->_('前台商品配置项不能超过5个!');
            return $this->splash('error',null,$msg,true);
        }
        $url = url::action('toptemai_ctl_wap_decorate@addTags',array('widgets'=>$widgetsInfo['widgets'],'dialog'=>$widgetsInfo['dialog']));
        return $this->splash('success',$url,null,true);
    }
    public function checkImageSlider()
    {
        $widgetsInfo = input::get();
        $dialogData =  shopWidgets::getWapInfo($widgetsInfo['widgets'],$this->shopId,null);
        if(count($dialogData[0]['params'])>=4)
        {
            //$url = url::action('toptemai_ctl_wap_decorate@addTags',array('widgets'=>$widgetsInfo['widgets'],'dialog'=>$widgetsInfo['dialog']));
            $msg = app::get('toptemai')->_('前台商品配置项不能超过4个!');
            return $this->splash('error',null,$msg,true);
        }
    }
    //根据商家id的获取商家所经营的所有类目
    public function getCatList()
    {
        $shopId = shopAuth::getShopId();
        $catInfo = app::get('toptemai')->rpcCall('shop.authorize.cat',array('shop_id'=>$shopId));
        $catList = json_encode($catInfo);
        return $catList;
    }

    //根据商家id和3级分类id获取商家所经营的所有品牌
    public function getBrandList()
    {
        $shopId = shopAuth::getShopId();
        $catId = input::get('catId');
        $params = array(
            'shop_id'=>$shopId,
            'cat_id'=>$catId,
            'fields'=>'brand_id,brand_name,brand_url'
        );
        $brands = app::get('toptemai')->rpcCall('category.get.cat.rel.brand',$params);
        return response::json($brands);
    }

    //根据商家类目id的获取商家所经营类目下的所有商品
    public function searchItem()
    {
        $shopId = shopAuth::getShopId();
        $catId = input::get('catId');
        $brandId = input::get('brandId');
        $keywords = input::get('searchname');
        $widgetsId = input::get('widgetsId');
        if($brandId)
        {
            $searchParams = array(
                'shop_id' => $shopId,
                'cat_id' => $catId,
                'brand_id' => $brandId,
                'search_keywords' =>$keywords
            );
        }
        else
        {
            $searchParams = array(
                'shop_id' => $shopId,
                'cat_id' => $catId,
                'search_keywords' =>$keywords
            );
        }
        if($widgetsId)
        {
            $objMdlCouponItem = app::get('sysdecorate')->model('widgets_instance');
            $notEndItem = $objMdlCouponItem->getRow('params', array('widgets_id'=>$widgetsId) );
            $pagedata['notEndItem'] = $notEndItem['params']['item_id'];
        }
        else
        {
            $pagedata['notEndItem'] = array();
        }

        $searchParams['fields'] = 'item_id,title,image_default_id,price';
        $itemsList = app::get('toptemai')->rpcCall('item.search',$searchParams);
        $pagedata['itemsList'] = $itemsList['list'];
        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');
        return json_encode($pagedata,true);
    }
}


