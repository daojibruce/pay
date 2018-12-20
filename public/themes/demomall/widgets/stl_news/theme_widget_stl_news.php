<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_stl_news(&$setting){
    //获取选择的文章信息
    $articles = [];
    foreach ($setting['article_select'] as $article_id) {
        $articleInfo = app::get('syscontent')->model('article')->getRow('article_id, title, modified', ['article_id' => $article_id]);
        $articleInfo['modified'] = date('Y年m月d日',$articleInfo['modified']);
        $articles[] = $articleInfo;
    }
    $setting['articles'] = $articles;
    $articleInfo = app::get('syscontent')->model('article')->getRow('article_id, title, content', ['article_id' => $setting['single_article_select']]);
    $setting['single_article'] = $articleInfo;
    //echo "<pre>";print_r($setting);exit;
    return $setting;
}
?>
