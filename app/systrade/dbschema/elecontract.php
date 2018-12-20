<?php

/**
 * Created by Nie.
 * User: All User
 * Date: 2016/1/5
 * Time: 11:19
 * custom create nie
 * 电子合同表
 */
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
return array(
    'columns' => array(
        'elecontract_id' => array(
            'type' => 'number',
            //'pkey' => true,
            'autoincrement' => true,
            'required' => true,
            'comment' => app::get('systrade')->_('电子合同ID'),
        ),
        'tid' => array(
            'type' => 'bigint',
            'unsigned' => true,
            'required' => true,
            'comment' => app::get('systrade')->_('订单编号'),
        ),
        'temai_user_id' => array(
            'type' => 'table:account@sysuser',
            'required' => true,
            'default' => '0',
            'comment' => app::get('systrade')->_('平台展销会员用户ID'),
        ),
        'shop_id' => array(
            'type' => 'table:shop@sysshop',
            'required' => true,
            'default' => '0',
            'comment' => app::get('systrade')->_('所属商家'),
        ),
        'user_id' => array(
            'type' => 'table:account@sysuser',
            'required' => true,
            'comment' => app::get('systrade')->_('买家id'),
        ),
        'is_part_pay' => array(
            'type' => 'bool',
            'default' => 0,
            'comment' => app::get('systrade')->_('是否多次付款'),
        ),
        'pay_type' => array(
            'type' => 'serialize',
            'comment' => app::get('systrade')->_('付款方式'),
        ),
        'is_part_delivery' => array(
            'type' => 'bool',
            'default' => 0,
            'comment' => app::get('systrade')->_('是否多次发货'),
        ),
        'is_delivery_type' => array(
            'type' => 'string',
            'comment' => app::get('systrade')->_('是否代发'),
        ),
        'user_name' => array(
            'type' => 'string',
            'length' => '50',
            'comment' => app::get('systrade')->_('买家联系人'),
        ),
        'phone' => array(
            'type' => 'string',
            'length' => '13',
            'comment' => app::get('systrade')->_('买家联系人电话'),
        ),
        'user_place' => array(
            'type' => 'string',
            'length' => '50',
            'comment' => app::get('systrade')->_('买家住所'),
        ),
        'user_legal_agent' => array(
            'type' => 'string',
            'length' => '50',
            'comment' => app::get('systrade')->_('买家法定代表人'),
        ),
        'user_entrusted_agent' => array(
            'type' => 'string',
            'length' => '50',
            'comment' => app::get('systrade')->_('买家委托代理人'),
        ),
        'user_fax' => array(
            'type' => 'string',
            'length' => '13',
            'comment' => app::get('systrade')->_('买家传真'),
        ),
        'seller_name' => array(
            'type' => 'string',
            'length' => '50',
            'comment' => app::get('systrade')->_('卖家联系人'),
        ),
        'seller_phone' => array(
            'type' => 'string',
            'length' => '13',
            'comment' => app::get('systrade')->_('卖家联系人电话'),
        ),
        'seller_place' => array(
            'type' => 'string',
            'length' => '50',
            'comment' => app::get('systrade')->_('卖家住所'),
        ),
        'seller_legal_agent' => array(
            'type' => 'string',
            'length' => '50',
            'comment' => app::get('systrade')->_('卖家法定代表人'),
        ),
        'seller_entrusted_agent' => array(
            'type' => 'string',
            'length' => '50',
            'comment' => app::get('systrade')->_('卖家委托代理人'),
        ),
        'seller_fax' => array(
            'type' => 'string',
            'length' => '13',
            'comment' => app::get('systrade')->_('卖家传真'),
        ),
        'signed_place' => array(
            'type' => 'string',
            'length' => '50',
            'comment' => app::get('systrade')->_('签订地点'),
        ),
        'signed_pattern' => array(
            'type' => 'string',
            'length' => '50',
            'comment' => app::get('systrade')->_('签订方式'),
        ),
        'signed_time' => array(
            'type' => 'time',
            'comment' => app::get('systrade')->_('签订时间'),
        ),
        'standard' => array(
            'type' => 'text',
            'default' => '',
            'comment' => app::get('systrade')->_('质量标准'),
        ),
        'terms' => array(
            'type' => 'text',
            'default' => '',
            'comment' => app::get('systrade')->_('出卖人对质量负责的条件及期限'),
        ),
        'packing_pattern' => array(
            'type' => 'text',
            'default' => '',
            'comment' => app::get('systrade')->_('包装方式'),
        ),
        'appendix' => array(
            'type' => 'text',
            'default' => '',
            'comment' => app::get('systrade')->_('随货必备'),
        ),
        'loss_standard' => array(
            'type' => 'text',
            'default' => '',
            'comment' => app::get('systrade')->_('合理损耗标准及计算方法'),
        ),
        'content' => array(
            'type' => 'text',
            'default' => '',
            'comment' => app::get('systrade')->_('交货时间、方式、地点、运输方式、费用负担'),
        ),
        'inspection_standard' => array(
            'type' => 'text',
            'default' => '',
            'comment' => app::get('systrade')->_('检验标准、方法及期限'),
        ),
        'breach_responsibility' => array(
            'type' => 'text',
            'default' => '',
            'comment' => app::get('systrade')->_('违约责任'),
        ),
        'disputed' => array(
            'type' => 'text',
            'default' => '',
            'comment' => app::get('systrade')->_('合同争议的解决方式'),
        ),
        'Change_release' => array(
            'type' => 'text',
            'default' => '',
            'comment' => app::get('systrade')->_('合同变更和解除'),
        ),
        'ipr' => array(
            'type' => 'text',
            'default' => '',
            'comment' => app::get('systrade')->_('知识产权'),
        ),
        'customer_service' => array(
            'type' => 'text',
            'default' => '',
            'comment' => app::get('systrade')->_('售后条款'),
        ),
        'effective_condition' => array(
            'type' => 'text',
            'default' => '',
            'comment' => app::get('systrade')->_('生效条件'),
        ),
        'createtime' => array(
            'type' => 'time',
            'comment' => app::get('desktop')->_('创建时间'),
        ),
        'submittime' => array(
            'type' => 'time',
            'comment' => app::get('desktop')->_('发起电子合同时间'),
        ),
        'confirmtime' => array(
            'type' => 'time',
            'comment' => app::get('desktop')->_('确认电子合同时间'),
        ),
    ),
    'primary' => 'elecontract_id',
    'comment' => app::get('systrade')->_('电子合同数据列表'),
);
