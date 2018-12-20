<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


return array (
    'columns' =>
        array (
            'article_id' =>array (
                'type' => 'number',
                'required' => true,
                'comment'=> app::get('syscontent')->_('文章ID'),
                'autoincrement' => true,
                'width' => 50,
                'order'=>1,
            ),
            'title' =>array (
                'type' => 'string',
                'searchtype' => 'has',
                'filtertype' => 'normal',
                'filterdefault' => 'true',
                'required' => true,
                'in_list' => true,
                'default_in_list' => true,
                'label' => app::get('syscontent')->_('文章标题'),
                'order'=>2,
            ),
            'author' =>array (
                'type' => 'string',
                'filtertype' => 'normal',
                'filterdefault' => 'true',
                'required' => true,
                'in_list' => true,
                'default_in_list' => true,
                'label' => app::get('syscontent')->_('文章作者'),
                'order'=>33,
            ),

            'modified' =>array (
                'type' => 'time',
                'label' => app::get('syscontent')->_('更新时间'),
                'editable' => false,
                'width' => 130,
                'in_list' => true,
                'default_in_list' => true,
                'order'=>7,
            ),
            'ifpub' => array(
                'type' => 'bool',
                'default' => 0,
                'comment' => app::get('syscontent')->_('发布'),
                'editable' => true,
                'width' => 40,
                'order'=>8,
            ),
            'content' =>array (
                'type' => 'text',
                'comment'=> app::get('syscontent')->_('文章内容'),
                'editable' => true,
            ),
            'url' =>array (
                'type' => 'string',
                'comment'=> app::get('syscontent')->_('来源地址'),
                'editable' => false,
            ),
        ),
    'primary' => 'article_id',
    'index' => array(
        'ind_title' => ['columns' => ['title']],
    ),
    'comment' => app::get('syscontent')->_('爬取文章表'),
);
