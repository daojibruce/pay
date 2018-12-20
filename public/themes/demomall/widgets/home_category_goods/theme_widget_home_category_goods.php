<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_home_category_goods(& $setting)
{
    if( !$setting['home_groupname'] ) return $setting;
    $id_list = home_category_goods_list($setting);

    $params['item_id'] = $id_list;
    $params['item_nolimit'] = '1';
    $params['item_disabled'] = '0';
    $params['fields']['rows'] = "item_id,title,sub_title,bn,price,image_default_id";
    $data = app::get('topc')->rpcCall('item.list.get',$params);
    $data = handle_category_list($setting , $data);
    $setting['data'] = $data;

    return $setting;
}

function home_category_goods_list(& $setting)
{
    foreach($setting['home_groupname'] as $index => $val){
        if(empty($val)){
            unset($setting['home_groupname'][$index]);
            unset($setting['home_goods'][$index]);
        }
    }

    $goodsList = array();
    foreach($setting['home_goods'] as $groupIndex => $list){
        $liststr = implode("," , $list);
        $setting['home_goods_idlist'][$groupIndex] = $liststr;
        $goodsList[] = $liststr;
    }

    return implode("," , $goodsList);
}

function handle_category_list($setting , $data){
    $newSetting = array();
    foreach($setting['home_groupname'] as $gindex => $gname){
        foreach($setting['home_goods'][$gindex] as $itemId){
            $newSetting[$gname][$itemId] = $data[$itemId];
        }
    }

    return $newSetting;
}
