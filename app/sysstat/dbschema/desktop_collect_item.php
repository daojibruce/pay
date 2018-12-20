<?php
return  array(
    'columns'=>array(
        'collect_item_id'=>array(
            'type' => 'bigint',
            'unsigned' => true,
            'autoincrement' => true,
            'required' => true,
            'label' => 'id',
            'comment' => app::get('sysstat')->_('商品收藏排行id 自赠'),
            'order' => 1,
        ),
        'shop_id' => array(
            'type' => 'table:shop@sysshop',
            'required' => true,
            'comment' => app::get('sysstat')->_('商家id'),
            'order' => 2,
        ),
        'shop_name' => array(
            'type' => 'string',
            'length' => 90,
            'comment' => app::get('sysstat')->_('所属商家'),
            'label' => app::get('sysstat')->_('所属店铺'),
            'in_list'=>true,
            'default_in_list'=>true,
            'is_title' => true,
            'order' => 3,
        ),
        'cat_id' => array(
            'type' => 'table:cat@syscategory',
            'required' => true,
            'comment' => app::get('sysstat')->_('一级分类id'),
            'order' => 4,
        ),
        'cat_name' => array(
            'type' => 'string',
            'required' => true,
            'comment' => app::get('sysstat')->_('所属一级分类'),
            'label' => app::get('sysstat')->_('所属一级分类'),
            'in_list'=>true,
            'default_in_list'=>true,
            'is_title' => true,
            'order' => 5,
        ),
        'item_id' => array(
            'type' => 'table:item@sysitem',
            'required' => true,
            'comment' => app::get('sysstat')->_('商品id'),
            'order' => 6,
        ),
        'title' => array(
            'type' => 'string',
            'required' => true,
            'length' => 90,
            'default' => '',
            'comment' => app::get('sysstat')->_('商品标题'),
            'label' => app::get('sysstat')->_('商品名称'),
            'in_list'=>true,
            'default_in_list'=>true,
            'is_title' => true,
            'order' => 7,
        ),
        'pic_path' => array(
            'type' => 'string',
            'comment' => app::get('sysstat')->_('商品图片绝对路径'),
            'label' => app::get('sysstat')->_('商品图片'),
            'order' => 8,
        ),
        'itemurl' => array(
            'type' => 'string',
            'comment' => app::get('sysstat')->_('商品链接'),
            'label' => app::get('sysstat')->_('商品链接'),
            'order' => 12,
        ),
        'collectnum' => array(
            'type' => 'number',
            'comment' => app::get('sysstat')->_('收藏数量'),
            'label' => app::get('sysstat')->_('收藏数量'),
            'in_list'=>true,
            'default_in_list'=>true,
            'is_title' => true,
            'order' => 9,
        ),
        'createtime'=>array(
            'type' => 'time',
            'comment' => app::get('sysstat')->_('统计时间'),
            'label' => app::get('sysstat')->_('统计时间'),
            'filtertype' => true,
            'filterdefault' => 'true',
            'order' => 11,
        ),
    ),
    'primary' => 'collect_item_id',
    'index' => array(
        'ind_createtime' => ['columns' => ['createtime']],
        'ind_shop_id' => ['columns' => ['shop_id']],
        'ind_item_id' => ['columns' => ['item_id']],
    ),
    'comment' => app::get('sysstat')->_('店铺收藏排行统计表'),
);
