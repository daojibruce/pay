<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_stl_member(&$setting){
    //获取最新店铺
    $shopInfo = app::get('sysshop')->model('shop')->getList('shop_id,shop_name',['status' => 'active'],0,5,'open_time desc');
    $setting['shops'] = $shopInfo;

    //获取选择的文章信息
    $articles = [];
    foreach ($setting['article_select'] as $article_id) {
        $articleInfo = app::get('syscontent')->model('article')->getRow('article_id, title', ['article_id' => $article_id]);
        $articles[] = $articleInfo;
    }
    $setting['articles'] = $articles;

    //获取选择的其他文章信息
    $others = [];
    foreach ($setting['others_select'] as $article_id) {
        $articleInfo = app::get('syscontent')->model('others')->getRow('article_id, title', ['article_id' => $article_id]);
        $others[] = $articleInfo;
    }
    /*$count = 12 - count($setting['others_select']);
    if ($count > 0) {
        $list = app::get('syscontent')->model('others')->getList('article_id, title', ['article_id|notin' => $setting['others_select']],0,$count,'modified desc');
        $others = array_merge($list,$others);
    }*/
    $setting['others'] = $others;


    //获取选择的商家信息
    /*$shops = [];
    foreach ($setting['shop_select'] as $shop_id) {
        $shopInfo = app::get('sysshop')->model('shop')->getRow('shop_id, shop_name', ['shop_id' => $shop_id]);
        $shops[] = $shopInfo;
    }
    $setting['shops'] = $shops;*/

    //echo "<pre>";print_r($setting);exit;
    return $setting;
}
?>
