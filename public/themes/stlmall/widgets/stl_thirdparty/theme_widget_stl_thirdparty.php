<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_stl_thirdparty(&$setting){
    //取出第三方服务商分类信息
    $setting['categories'] = app::get('thirdparty')->model('category')->getList('*');
    return $setting;
}
?>
