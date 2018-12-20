<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_stl_recommend(&$setting){
    $rows = 'item_id,shop_id,title,sub_title,price,image_default_id';
    $objItem = kernel::single('sysitem_item_info');
    $setting['item'] = $objItem->getItemList($setting['item_select'], $rows);
    $setting['defaultImg'] = app::get('image')->getConf('image.set');
    //读取店铺信息
    $shopMdl = app::get('sysshop')->model('shop');
    foreach($setting['item'] as $key => $item) {
        $shopInfo = $shopMdl->getRow('shop_name',['shop_id' => $item['shop_id']]);
        $setting['item'][$key]['shop_name'] = $shopInfo['shop_name'];
    }
    //echo "<Pre>";print_r($setting);exit;
    return $setting;
}
?>
