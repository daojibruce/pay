<?php

return  array(
    'columns' => array(
        'longpay_id' => array(
            'type' => 'bigint',
            'unsigned' => true,
            'required' => true,
            'autoincrement' => true,
            'comment' => app::get('systrade')->_(' 延长付款申请ID'),
        ),
        'temai_user_id' => array(
            'type' => 'table:account@sysuser',
            'required' => true,
            'default' => '0',
            'comment' => app::get('systrade')->_('平台展销会员用户ID'),
        ),
        'shop_id' => array(
            'type' => 'bigint',
            'default' => '0',
            'required' => true,
            'comment' => app::get('systrade')->_('被申请店铺ID'),
        ),
        'user_id' => array(
            'type' => 'bigint',
            'unsigned' => true,
            'default' => '0',
            'required' => true,
            'comment' => app::get('systrade')->_('发起申请用户ID'),
        ),
        'tid'=>array(
            'type' => 'bigint',
            'in_list' => true,
            'width' => '300',
            'default_in_list' => true,
            'searchtype' => 'has',
            'filtertype' => false,
            'filterdefault' => 'true',
            'required' => true,
            'label' => app::get('systrade')->_('订单号'),
            'comment' => app::get('systrade')->_('订单号'),
        ),
        'status' => array(
            'type' => array(
                'WAIT_AGREE' => '等待处理',
                'FINISHED' => '商家同意',
                'CLOSED' => '商家拒绝',
            ),
            'default' => 'WAIT_AGREE',
            'required' => true,
            'in_list' => true,
            'default_in_list' => false,
            'label' => app::get('systrade')->_('状态'),
        ),
        'pay_type' => array(
            'type' => 'serialize',
            'comment' => app::get('systrade')->_('付款方式'),
        ),
        'memo' => array(
            'type' => 'string',
            'default' => '',
            'in_list' => true,
            'width' => '300',
            'label' => app::get('systrade')->_('申请备注'),
            'order' => 50,
        ),
        'close_reasons' => array(
            'type' => 'string',
            'default' => '',
            'in_list' => true,
            'width' => '300',
            'label' => app::get('systrade')->_('商家拒绝原因'),
            'order' => 50,
        ),
        'created_time' => array(
            'type' => 'time',
            'label' => app::get('systrade')->_('申请时间'),
            'in_list' => true,
            'default_in_list' => true,
            'width' => '100',
            'order' => 60,
        ),
        'modified_time' => array(
            'type' => 'last_modify',
            'label' => app::get('systrade')->_('最后修改时间'),
            'in_list' => true,
            'default_in_list' => true,
            'width' => '100',
            'order' => 60,
        ),
    ),
    'primary' => 'longpay_id',
    'index' => array(   //索引名称
//         'ind_oid' => [
//             'columns' => ['oid'], // 需要建立索引的字段名
//             'prefix' => 'unique' // 目前只支持unqiue, 或者不填写
//         ],
        'ind_tid' => ['columns' => ['tid']],
        'ind_status' => ['columns' => ['status']],
    ),
    'comment' => app::get('systrade')->_('订单延长付款申请表'),
);

