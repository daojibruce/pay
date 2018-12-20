<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/10/26
 * Time: 17:15
 * custom sjz
 */
return array(
    'columns' => array(
        'company_id' => array(
            //'type' => 'int(10)',
            'type' => 'number',
            'required' => true,
            //'pkey' => true,
            'autoincrement' => true,
            'editable' => false,
            'comment' => app::get('sysuser')->_('会员公司ID'),
        ),
        'user_id' => array(
            'type' => 'table:user',
            'default' => 0,
            'required' => true,
            'editable' => false,
            'comment' => app::get('sysuser')->_('会员ID'),
        ),
        'company_name' => array(
            'is_title' => true,
            'type' => 'string',
            'length' => 50,
            'editable' => false,
            'comment' => app::get('sysuser')->_('会员公司名称'),
        ),
        'tax_id' => array(
            'is_title' => true,
            'type' => 'string',
            'length' => 50,
            'editable' => false,
            'comment' => app::get('sysuser')->_('税务登记号'),
        ),
        'company_area' => array(
            'type' => 'string',
            'editable' => false,
            'comment' => app::get('sysuser')->_('公司地区'),
        ),
        'company_addr' => array(
            'type' => 'string',
            'length' => 100,
            'editable' => false,
            'comment' => app::get('sysuser')->_('公司详细地址'),
        ),
        'operating_range' => array(
            'type' => 'string',
            'length' => 150,
            'editable' => false,
            'comment' => app::get('sysuser')->_('公司经营范围'),
        ),
        'company_num' => array(
            //'type' => 'int(11)',
            'type' => 'string',
            'length' => 11,
            'comment' => app::get('sysuser')->_('公司人数'),
        ),
        'company_registered' => array(
            //'type' => 'int(11)',
            'type' => 'string',
            'length' => 11,
            'comment' => app::get('sysuser')->_('公司注册资金'),
        ),
        'company_turnover' => array(
            'type' => 'string',
            'length' => 11,
            'comment' => app::get('sysuser')->_('年营业额'),
        ),
        'def_addr' => array(
            'type' => 'string',
            'length' => 50,
            'editable' => false,
            'comment' => app::get('sysuser')->_('法人代表姓名'),
        ),
        'company_phone' => array(
            'type' => 'string',
            'length' => 16,
            'default' => 0,
            'editable' => false,
            'comment' => app::get('sysuser')->_('公司电话'),
        ),
        'registered_capital' => array(
            'type' => 'number',
            'length' => 14,
            'default' => 0,
            'editable' => false,
            'comment' => app::get('sysuser')->_('注册资本'),
        ),
        'website' => array(
            'type' => 'string',
            'editable' => false,
            'comment' => app::get('sysuser')->_('网址'),
        ),
        'email' => array(
            'type' => 'string',
            'editable' => false,
            'comment' => app::get('sysuser')->_('邮箱'),
        ),
        'valid_time' => array(
            'type' => 'string',
            'editable' => false,
            'comment' => app::get('sysuser')->_('有效期至'),
        ),
        'company_contact' => array(
            'type' => 'string',
            'editable' => false,
            'comment' => app::get('sysuser')->_('公司联系人'),
        ),
        'company_contactPhone' => array(
            'type' => 'string',
            'length' => 16,
            'editable' => false,
            'comment' => app::get('sysuser')->_('联系人手机号'),
        ),
        'updatetime' => array(
            'type' => 'number',
            'length' => 11,
            'editable' => false,
            'comment' => app::get('sysuser')->_('更新时间'),
        ),
        //custom s dengLy 2016.1.5 企业注册资料
        'fax'=>array(
            'type' => 'string',
            'label' => app::get('sysuser')->_('传真'),
        ),
        'business_license'=>array(
            'type' => 'string',
            'label' => app::get('sysuser')->_('营业执照'),
            'comment' => app::get('sysuser')->_('营业执照电子版'),
        ),
        'special_aptitude'=>array(
            'type' => 'string',
            'label' => app::get('sysuser')->_('特种物资采购必备资质'),
            'comment' => app::get('sysuser')->_('特种物资采购必备资质电子版'),
        ),

        //custom e zmq 2016.10.10 企业发票信息
    ),
    'primary' => 'company_id',
    'index' => array(
        'ind_user_id' => ['columns' => ['user_id']],
    ),
    'comment' => app::get('sysuser')->_('会员公司列表'),
);