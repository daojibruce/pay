<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_stl_brand(&$setting){

    $brands = app::get('syscategory')->model('brand')->getList('*', ['brand_id|in' => $setting['brand_select'],'disabled' => '0']);
    //echo "<Pre>";print_r($brands);exit;
    $setting['brands'] = $brands;

    return $setting;
}
?>
