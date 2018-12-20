<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 * 商家前台会员中心左侧菜单列表
 */

return array(
    //卖家服务撮合菜单
    'proofing_back' =>
        array(
            'label' => '我的服务撮合',
            'items' => array(
                array('label' => '服务撮合需求列表','action'=>'topc_ctl_member_proofing@index'),
                array('label' => '我的报价','action'=>'topc_ctl_member_proofing@offer'),
                array('label' => '我的服务撮合订单','action'=>'topc_ctl_member_proofing@tradeList'),
                array('label' => '新增服务类型','action'=>'topc_ctl_member_proofing@addCategory'),
                //array('label' => '服务撮合结算','action'=>'topc_ctl_member_proofing@settlement'),
            ),
        ),

    /*//买家服务撮合菜单
    'proofing_front' =>
        array(
            'label' => '我的服务撮合',
            'items' => array(
                array('label' => '我的服务撮合需求','action'=>'topc_ctl_member@myRequirement'),
                array('label' => '服务撮合报价','action'=>'topc_ctl_member@myRequirementPrice'),
                array('label' => '我的服务撮合订单','action'=>'topc_ctl_member_trade@myRequirementOrder'),
            ),
        ),

    //买家商城中心
    'buycenter' =>
        array(
            'label' => '我的商城',
            'items' => array(
                array('label' => '我的订单','action'=>'topc_ctl_member_trade@tradeList'),
                array('label' => '取消订单记录','action'=>'topc_ctl_member_trade@canceledTradeList'),
            ),
        ),

    //买家服务撮合中心
    'temai' =>
        array(
            'label' => '我的平台展销',
            'items' => array(
                array('label' => '我的订单','action'=>'topc_ctl_temai_trade@tradeList'),
                array('label' => '取消订单记录','action'=>'topc_ctl_member_trade@canceledTradeList'),
            ),
        ),*/
);
