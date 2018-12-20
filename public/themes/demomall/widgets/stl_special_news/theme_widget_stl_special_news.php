<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_stl_special_news(&$setting){

    //获取选择的文章信息
    $articles = [];
    foreach ($setting['article_select'] as $article_id) {
        $articleInfo = app::get('syscontent')->model('article')->getRow('article_id, title', ['article_id' => $article_id]);
        $articles[] = $articleInfo;
    }
    $articles = array_slice($articles, 0, 4);
    $setting['articles'] = $articles;

    return $setting;
}
?>
