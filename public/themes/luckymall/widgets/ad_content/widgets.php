<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
$setting['author']='gongjiapeng@shopex.cn';
$setting['version']='v1.0';
$setting['name']='文章列表';
$setting['stime']='2015-08';
$setting['catalog']='辅助信息';
$setting['usual'] = '1';
$setting['description']='文章列表';
$setting['userinfo']='';
$setting['tag']    = 'auto';
$setting['template'] = array(
                            'default.html'=>app::get('topm')->_('默认')
                        );
$syscontentLibNode = kernel::single('syscontent_article_node');
$nodeList = $syscontentLibNode->nodeListWidget();
//echo '<pre>';print_r($nodeList);exit();
$setting['selectmaps'] = $nodeList;
?>
