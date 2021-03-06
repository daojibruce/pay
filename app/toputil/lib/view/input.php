<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class toputil_view_input{

    function input_category($params)
    {
        $selectedCatId = $params['value'];
        $shopId = $params['shop_id'];
        $userId = $params['user_id'];
        $catId = $params['cat_id'];

        if($selectedCatId && $shopId)
        {
            $selectedCatInfo = $this->__getCatList($selectedCatId,$shopId);
        }
        elseif(!$selectedCatId && $shopId)
        {
            $selectedCatInfo = $this->__getCatInfo($shopId);
        }
        elseif($selectedCatId && $userId) //平台展销会员ID
        {
            $selectedCatInfo = $this->__getCatListTemai($selectedCatId , $userId);
        }elseif(!$selectedCatId && $userId) //平台展销会员ID
        {
            $selectedCatInfo = $this->__getCatInfoTemai($userId);
        }elseif($shopId == '0' && $catId && !$selectedCatId)
        {
            $selectedCatInfo = $this->__getCatInfoPass($catId);
        }
        elseif($shopId == '0' && $catId && $selectedCatId)
        {
            $selectedCatInfo = $this->__getCatListPass($catId, $selectedCatId);
        }
        $pagedata['value'] = json_encode($selectedCatInfo);
        $pagedata['callback'] = $params['callback'] ? $params['callback'] : false;
        return view::make('toputil/smarty/cat-select.html', $pagedata)->render();
    }

    private function __getCatInfo($shopId)
    {
        $shopAuthorize = app::get('toputil')->rpcCall('shop.authorize.catbrandids.get',array('shop_id'=>$shopId));
        $catId = $shopAuthorize[$shopId]['cat'];
        $shopType = $shopAuthorize[$shopId]['shop_type'];
        if(!$catId && $shopType == "self")
        {
            $catList = app::get('toputil')->rpcCall('category.cat.get.info',array('parent_id'=>'0','fields'=>'cat_id,cat_name,child_count'));
        }
        elseif($catId)
        {
            $catList = app::get('toputil')->rpcCall('category.cat.get.info',array('cat_id'=>implode(',',$catId),'fields'=>'cat_id,cat_name,child_count'));
        }
        else{
            $catList = array();
        }

        if($catList)
        {
            foreach($catList as $key=>$value) {
                $newList[0]['options'][$key] = array(
                    'value' => $value['cat_id'],
                    'text' => $value['cat_name'],
                    'hasChild' => ($value['child_count'] >0) ? true : false,
                );
            }
        }
        return $newList;
    }

    private function __getCatInfoPass($catId)
    {
        $catList = app::get('toputil')->rpcCall('category.cat.get.info',array('cat_id'=>$catId,'parent_id'=>'0','fields'=>'cat_id,cat_name,child_count'));

        if($catList)
        {
            foreach($catList as $key=>$value) {
                $newList[0]['options'][$key] = array(
                    'value' => $value['cat_id'],
                    'text' => $value['cat_name'],
                    'hasChild' => ($value['child_count'] >0) ? true : false,
                );
            }
        }
        return $newList;
    }

    private function __getCatListPass($catId, $sCatId)
    {
        $catList = app::get('toputil')->rpcCall('category.cat.get.list');

        foreach($catList as $key=>$value)
        {
            $lv1Data[$value['parent_id']]['options'][$value['cat_id']] = array(
                'value' => $value['cat_id'],
                'text' => $value['cat_name'],
                'hasChild' => ($value['child_count'] >0) ? true : false,
            );
            foreach($value['lv2'] as $key2=>$value2)
            {
                $lv2Data[$value2['parent_id']]['options'][$value2['cat_id']] = array(
                    'value' => $value2['cat_id'],
                    'text' => $value2['cat_name'],
                    'hasChild' => ($value2['child_count'] >0) ? true : false,
                );

                foreach($value2['lv3'] as $key3=>$value3)
                {
                    $lv3Data[$value3['parent_id']]['options'][$value3['cat_id']] = array(
                        'value' => $value3['cat_id'],
                        'text' => $value3['cat_name'],
                        'hasChild' => ($value3['child_count'] >0) ? true : false,
                    );
                    if($value3['cat_id'] == $sCatId)
                    {
                        $lv3Data[$value3['parent_id']]['selectedIndex'] = $sCatId;
                        $lv3ParentId = $value3['parent_id'];
                    }
                }
                if($lv3ParentId == $value2['cat_id'])
                {
                    $lv2Data[$value2['parent_id']]['selectedIndex'] = $value2['cat_id'];
                    $lv2ParentId = $value2['parent_id'];
                }
            }

            if($value['cat_id'] == $lv2ParentId)
            {
                $lv1Data[$value['parent_id']]['selectedIndex'] = $value['cat_id'];
                $lv1ParentId = $value['parent_id'];
            }
        }

        $newList[$lv1ParentId] = $lv1Data[$lv1ParentId];
        $newList[$lv2ParentId] = $lv2Data[$lv2ParentId];
        $newList[$lv3ParentId] = $lv3Data[$lv3ParentId];
        return $newList;
    }

    private function __getCatInfoTemai($userId)
    {
        $catList = app::get('toputil')->rpcCall('category.cat.get.info',array('parent_id'=>'0','fields'=>'cat_id,cat_name,child_count'));

        if($catList)
        {
            foreach($catList as $key=>$value) {
                $newList[0]['options'][$key] = array(
                    'value' => $value['cat_id'],
                    'text' => $value['cat_name'],
                    'hasChild' => ($value['child_count'] >0) ? true : false,
                );
            }
        }
        return $newList;
    }

    private function __getCatList($sCatId,$shopId)
    {
        $shopAuthorize = app::get('toputil')->rpcCall('shop.authorize.catbrandids.get',array('shop_id'=>$shopId));
        $catId = $shopAuthorize[$shopId]['cat'];
        $shopType = $shopAuthorize[$shopId]['shop_type'];
        if(!$catId && $shopType == "self")
        {
            $catList = app::get('toputil')->rpcCall('category.cat.get.list');
        }
        elseif($catId)
        {
            $catList = app::get('toputil')->rpcCall('category.cat.get',array('cat_id'=>implode(',',$catId)));
        }

        foreach($catList as $key=>$value)
        {
            $lv1Data[$value['parent_id']]['options'][$value['cat_id']] = array(
                'value' => $value['cat_id'],
                'text' => $value['cat_name'],
                'hasChild' => ($value['child_count'] >0) ? true : false,
            );
            foreach($value['lv2'] as $key2=>$value2)
            {
                $lv2Data[$value2['parent_id']]['options'][$value2['cat_id']] = array(
                    'value' => $value2['cat_id'],
                    'text' => $value2['cat_name'],
                    'hasChild' => ($value2['child_count'] >0) ? true : false,
                );

                foreach($value2['lv3'] as $key3=>$value3)
                {
                    $lv3Data[$value3['parent_id']]['options'][$value3['cat_id']] = array(
                        'value' => $value3['cat_id'],
                        'text' => $value3['cat_name'],
                        'hasChild' => ($value3['child_count'] >0) ? true : false,
                    );
                    if($value3['cat_id'] == $sCatId)
                    {
                        $lv3Data[$value3['parent_id']]['selectedIndex'] = $sCatId;
                        $lv3ParentId = $value3['parent_id'];
                    }
                }
                if($lv3ParentId == $value2['cat_id'])
                {
                    $lv2Data[$value2['parent_id']]['selectedIndex'] = $value2['cat_id'];
                    $lv2ParentId = $value2['parent_id'];
                }
            }

            if($value['cat_id'] == $lv2ParentId)
            {
                $lv1Data[$value['parent_id']]['selectedIndex'] = $value['cat_id'];
                $lv1ParentId = $value['parent_id'];
            }
        }

        $newList[$lv1ParentId] = $lv1Data[$lv1ParentId];
        $newList[$lv2ParentId] = $lv2Data[$lv2ParentId];
        $newList[$lv3ParentId] = $lv3Data[$lv3ParentId];
        return $newList;
    }

    private function __getCatListTemai($sCatId,$userId)
    {
        $catList = app::get('toputil')->rpcCall('category.cat.get.list');
        foreach($catList as $key=>$value)
        {
            $lv1Data[$value['parent_id']]['options'][$value['cat_id']] = array(
                'value' => $value['cat_id'],
                'text' => $value['cat_name'],
                'hasChild' => ($value['child_count'] >0) ? true : false,
            );
            foreach($value['lv2'] as $key2=>$value2)
            {
                $lv2Data[$value2['parent_id']]['options'][$value2['cat_id']] = array(
                    'value' => $value2['cat_id'],
                    'text' => $value2['cat_name'],
                    'hasChild' => ($value2['child_count'] >0) ? true : false,
                );

                foreach($value2['lv3'] as $key3=>$value3)
                {
                    $lv3Data[$value3['parent_id']]['options'][$value3['cat_id']] = array(
                        'value' => $value3['cat_id'],
                        'text' => $value3['cat_name'],
                        'hasChild' => ($value3['child_count'] >0) ? true : false,
                    );
                    if($value3['cat_id'] == $sCatId)
                    {
                        $lv3Data[$value3['parent_id']]['selectedIndex'] = $sCatId;
                        $lv3ParentId = $value3['parent_id'];
                    }
                }
                if($lv3ParentId == $value2['cat_id'])
                {
                    $lv2Data[$value2['parent_id']]['selectedIndex'] = $value2['cat_id'];
                    $lv2ParentId = $value2['parent_id'];
                }
            }

            if($value['cat_id'] == $lv2ParentId)
            {
                $lv1Data[$value['parent_id']]['selectedIndex'] = $value['cat_id'];
                $lv1ParentId = $value['parent_id'];
            }
        }

        $newList[$lv1ParentId] = $lv1Data[$lv1ParentId];
        $newList[$lv2ParentId] = $lv2Data[$lv2ParentId];
        $newList[$lv3ParentId] = $lv3Data[$lv3ParentId];
        return $newList;
    }

    function input_item_select($params)
    {
    }
}
