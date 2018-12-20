<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/*基础配置项*/
$setting['author'] = 'lijidao@qjclouds.com';
$setting['version'] = 'v1.0';
$setting['name'] = 'index_main_floor';
$setting['order'] = 0;
$setting['stime'] = '2017-06-20';
$setting['catalog'] = '首页';
$setting['description'] = '主楼层';
$setting['userinfo'] = '';
$setting['usual']    = '1';
$setting['tag']    = 'auto';
$setting['template'] = array(
	'default.html'=>app::get('b2c')->_('默认')
);

//一级分类arr
$objMdlCat = app::get('syscategory')->model('cat');
$first_cat_list = $objMdlCat->getList('cat_id,cat_name', ['level' => 1], 0, 9);
$setting['first_cat_list'] = $first_cat_list;

//二级分类json
$cat_arr = [];
if($first_cat_list) {
	foreach($first_cat_list AS $cat) {
		$cat_arr[$cat['cat_id']] = $objMdlCat->getList('cat_id,cat_name', ['parent_id' => $cat['cat_id']], 0, 50);
	}
}
$setting['second_cat_list'] = $cat_arr;
//$setting['cat_json'] = json_encode($cat_arr);


?>
