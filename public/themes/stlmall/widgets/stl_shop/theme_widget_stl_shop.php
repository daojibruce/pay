<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_stl_shop(&$setting){
    $rows = 'shop_id,shop_name,shop_logo';

    $shops = [];
    $shopMdl = app::get('sysshop')->model('shop');
    foreach($setting['shop_select'] as $key => $shop_id) {
        $shops[] = $shopMdl->getRow($rows,['shop_id' => $shop_id]);
    }
    $setting['shops'] = $shops;
    //echo "<Pre>";print_r($setting);exit;
    return $setting;
}
?>
