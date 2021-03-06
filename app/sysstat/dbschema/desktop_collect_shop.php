<?php
return  array(
    'columns'=>array(
        'collect_shop_id'=>array(
            'type' => 'bigint',
            'unsigned' => true,
            'autoincrement' => true,
            'required' => true,
            'label' => 'id',
            'comment' => app::get('sysstat')->_('店铺收藏排行id 自赠'),
            'order' => 1,
        ),
        'shop_id' => array(
            'type' => 'bigint',
            'required' => true,
            'comment' => app::get('sysstat')->_('店铺id'),
            'order' => 2,
        ),
        'shopname'=>array(
            'type' => 'string',
            'length' => 90,
            'label' => app::get('sysstat')->_('店铺名称'),
            'comment' => app::get('sysstat')->_('店铺名称'),
            'in_list'=>true,
            'default_in_list'=>true,
            'is_title' => true,
            'order' => 3,
        ),
        'collectnum'=>array(
            'type' => 'number',
            'default' => 0,
            'label' => app::get('sysstat')->_('收藏量'),
            'comment' => app::get('sysstat')->_('收藏量'),
            'in_list'=>true,
            'default_in_list'=>true,
            'is_title' => true,
            'order' => 5,
        ),
        'shopurl' => array(
            'type' => 'string',
            'comment' => app::get('sysstat')->_('店铺链接'),
            'label' => app::get('sysstat')->_('店铺链接'),
            'order' => 7,
        ),
        'createtime'=>array(
            'type'=>'time',
            'comment' => app::get('sysstat')->_('统计时间'),
            'label' => app::get('sysstat')->_('统计时间'),
            'filtertype' => true,
            'filterdefault' => true,
            'order' => 6,
        ),
    ),
    'primary' => 'collect_shop_id',
    'index' => array(
        'ind_createtime' => ['columns' => ['createtime']],
        'ind_shop_id' => ['columns' => ['shop_id']],
    ),
    'comment' => app::get('sysstat')->_('店铺收藏排行统计表'),
);
