<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return array(
    'columns' => array(
        'lottery_id' => array(
            'type' => 'number',
            'required' => true,
            'autoincrement' => true,
            'in_list' => false,
            'label' => app::get('syspromotion')->_('活动id'),
            'comment' => app::get('syspromotion')->_('活动id'),
        ),
        'lottery_name' => array(
            'type' => 'string',
            'length' => '50',
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'filterdefault'=>true,
            'is_title' => true,
            'label' => app::get('syspromotion')->_('活动名称'),
            'comment' => app::get('syspromotion')->_('活动名称1'),
        ),
        'lottery_desc' => array(
            'type' => 'text',
            'required' => false,
            'default' => '',
            'label' => app::get('syspromotion')->_('活动描述'),
            'comment' => app::get('syspromotion')->_('活动描述'),
        ),
        'status' => array(
            'type' => array(
                'stop' => app::get('syspromotion')->_('停用'),
                'active' => app::get('syspromotion')->_('启用'),
            ),
            'default' => 'stop',
            'filterdefault' => true,
            'default_in_list' => true,
            'in_list' => true,
            'label' => app::get('syspromotion')->_('是否启用'),
            'comment' => app::get('syspromotion')->_('是否启用'),
        ),
        'lottery_type' => array(
            'type' => array(
                '0' => '全部',
                '1' => '初始可抽奖',
                '2' => '积分兑换',
            ),
            'default' => '0',
            'in_list' => true,
            'default_in_list' => true,
            'required' => true,
            'label' => app::get('syspromotion')->_('抽奖次数获取方式'),
            'comment' => app::get('syspromotion')->_('抽奖次数获取方式'),
        ),
        'used_platform' => array(
            'type' => array(
                '0' => app::get('syspromotion')->_('商家全场可用'),
                '1' => app::get('syspromotion')->_('只能用于pc'),
                '2' => app::get('syspromotion')->_('只能用于wap'),
            ),
            'default' => 0,
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('syspromotion')->_('使用平台'),
            'comment' => app::get('syspromotion')->_('使用平台'),
        ),
        'lottery_joint_limit' => array(
            'type' =>'number',
            'default' => 0,
            'required' => true,
            'default_in_list' => false,
            'in_list' => true,
            'label' => app::get('syspromotion')->_('初始可抽奖次数'),
            'comment' => app::get('syspromotion')->_('初始可抽奖次数'),
        ),
        'lottery_point_num' => array(
            'type' =>'number',
            'default' => 0,
            'required' => true,
            'default_in_list' => false,
            'in_list' => true,
            'label' => app::get('syspromotion')->_('兑换抽奖所需积分'),
            'comment' => app::get('syspromotion')->_('兑换抽奖所需积分'),
        ),
        'lottery_rules' => array(
            'type' => 'text',
            'required' => true,
            'label' => app::get('syspromotion')->_('奖项设置规则'),
            'comment' => app::get('syspromotion')->_('奖项设置规则'),
        ),
        'created_time' => array(
            'type' => 'time',
            'in_list' => false,
            'default_in_list' => false,
            'filterdefault'=>true,
            'label' => app::get('syspromotion')->_('创建时间'),
            'comment' => app::get('syspromotion')->_('创建时间'),
        ),
        'modified_time' => array(
            'type' => 'time',
            'in_list' => true,
            'default_in_list' => false,
            'filterdefault'=>true,
            'label' => app::get('syspromotion')->_('修改时间'),
            'comment' => app::get('syspromotion')->_('修改时间'),
        ),
    ),
    'primary' => 'lottery_id',
    'comment' => app::get('syspromotion')->_('转盘抽奖规则表'),
);
