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
    array(
        'label' => '服务撮合',
        'items' => array(
            array('label' => '撮合需求列表','action'=>'topc_ctl_member_proofing@index'),
            array('label' => '我的报价','action'=>'topc_ctl_member_proofing@offer'),
            array('label' => '我的服务撮合订单','action'=>'topc_ctl_member_proofing@tradeList'),
            array('label' => '新增服务类型','action'=>'topc_ctl_member_proofing@addCategory'),
            //array('label' => '服务撮合结算','action'=>'topc_ctl_member_proofing@settlement'),
        ),
    ),
);
