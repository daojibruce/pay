<?php
return  array(
    'columns' => array(
        'tid' => array(
            //'type' => 'bigint unsigned',
            'type' => 'bigint',
            'unsigned' => true,
            'required' => true,
            //'pkey' => true,
            'in_list' => true,
            'is_title' => true,
            'searchtype' => 'has',
            'filtertype' => 'custom',
            'filterdefault' => true,
            'default_in_list' => true,
            'label' => app::get('systrade')->_('订单号'),
            'width' => '100',
            'order' => 10,
        ),
        'shop_id' => array(
            //'type' => 'table:shop@sysshop',
            'type' => 'bigint',
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'default' => '0',
            'label' => app::get('systrade')->_('所属商家'),
            'comment' => app::get('systrade')->_('订单所属的店铺id'),
            'width' => 100,
            'order' => 11,
        ),
        'temai_user_id' => array(
            'type' => 'table:account@sysuser',
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'default' => '0',
            'label' => app::get('systrade')->_('平台展销会员用户ID'),
            'comment' => app::get('systrade')->_('平台展销ID'),
            'width' => 100,
            'order' => 12,
        ),
        'user_id' => array(
            'type' => 'table:account@sysuser',
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('systrade')->_('会员用户名'),
            'comment' => app::get('systrade')->_('会员id'),
            'width' => 100,
            'order' => 12,
        ),
        'sub_user_id' => array(
            'type' => 'table:account@sysuser',
            'required' => false,
            'label' => app::get('systrade')->_('子账号'),
            'comment' => app::get('systrade')->_('子账号id'),
        ),
        'dlytmpl_id' => array(
            'type' => 'table:dlytmpl@syslogistics',
            'label' => app::get('systrade')->_('配送模板id'),
            'comment' => app::get('systrade')->_('配送模板id'),
        ),
        'dlytmpl_ids' => array(
            'type' => 'string',
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('systrade')->_('配送模板ids'),
            'comment' => app::get('systrade')->_('配送模板ids(1,2,3)'),
            'width' => 100,
            'order' => 12,
        ),
        'ziti_addr' => array(
            'type' => 'string',
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('systrade')->_('自提地址'),
            'comment' => app::get('systrade')->_('自提地址'),
        ),
        'status' => array(
            'type' => array(
                'WAIT_CHECK' => '等待审核',
                'UNPASS' => '不通过',
                'WAIT_SENDCONTRACT' => '等待卖家发起电子合同',
                'REJECT_CONTRACT' => '买家拒绝合同，卖家可编辑',
                'WAIT_CONFIRM' => '等待买家确认电子合同并付款',
                'WAIT_BUYER_PAY' => '已下单等待付款',
                'WAIT_SELLER_SEND_GOODS' => '已付款等待发货',
                'WAIT_BUYER_CONFIRM_GOODS' => '已发货等待确认收货',
                'WAIT_SELLER_CONFIRM_GOODS' => '等待卖家确认收货',
                'TRADE_FINISHED' => '已完成',
                'TRADE_CLOSED' => '已关闭(退款关闭订单)',
                'TRADE_CLOSED_BY_SYSTEM' => '已关闭(卖家或买家主动关闭)',
                'TRADE_DEL_BY_USER' => '买家删除',
                'TRADE_CLOSED_BY_SYSTEM' => '已关闭(卖家或买家主动关闭)',
                'WAIT_PLAT_CHECK_OFFLINE' => '待平台审核',
                'WAIT_EVALUATE'=>'等待评价',
            ),
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'width' => '100',
            'order' => 13,
            'label' => app::get('systrade')->_('订单状态'),
            'comment' => app::get('systrade')->_('订单状态'),
        ),
        'cancel_status' => array(
            'type' => array(
                'NO_APPLY_CANCEL' => '未申请',
                'WAIT_PROCESS' => '等待审核',
                'REFUND_PROCESS' => '退款处理',
                'SUCCESS' => '取消成功',
                'FAILS' => '取消失败',
            ),
            'default' => 'NO_APPLY_CANCEL',
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('systrade')->_('取消订单状态'),
        ),
        'settlement_status' => array(
            'type' => array(
                '0' => '未结算',
                '1' => '普通结算已结算',
                '2' => '运费结算已结算',
                '3' => '售后结算已结算',
                '4' => '拒收结算已结算',
            ),
            'default' => '0',
            'required' => true,
            'default' => '0',
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'custom',
            'filterdefault' => true,
            'label' => app::get('systrade')->_('结算状态'),
            'comment' => app::get('systrade')->_('结算状态'),
        ),
        //以后存储到订单取消记录表中
        'cancel_reason' => array(
            'type' => 'text',
            'in_list' => true,
            'default_in_list' => true,
            'width' => '100',
            'order' => 13,
            'label' => app::get('systrade')->_('取消订单原因'),
        ),
        'pay_type' => array(
            'type' => array(
                'online' => '线上付款',
                'offline' => '货到付款',
                'caroffline' => '线下付款',
            ),
            'default'=>'online',
            'required' => true,
            'width' => '100',
            'label' => app::get('systrade')->_('支付类型'),
            'comment' => app::get('systrade')->_('支付类型'),
        ),
        'payment' => array(
            'type' => 'money',
            'default' => '0',
            'in_list' => true,
            'default_in_list' => true,
            'width' => '50',
            'order' => 14,
            'label' => app::get('systrade')->_('实付金额'),
            'comment' => app::get('systrade')->_('实付金额,订单最终总额'),
        ),
        'points_fee' => array(
            'type' => 'money',
            'default' => '0',
            'in_list' => true,
            'default_in_list' => true,
            'width' => '50',
            'order' => 14,
            'label' => app::get('systrade')->_('积分抵扣金额'),
            'comment' => app::get('systrade')->_('积分抵扣金额'),
        ),
        'total_fee' => array(
            'type' => 'money',
            'default' => '0',
            'label' => '商品总额',
            'comment' => app::get('systrade')->_('各子订单中商品price * num的和，不包括任何优惠信息'),
        ),
        'post_fee' => array(
            'type' => 'money',
            'comment' => app::get('systrade')->_('邮费'),
        ),
        'isconfirm_post_fee' => array(
            'type' => array(
                '0' => '未修改',
                '1' => '已修改',
            ),
            'default' => 0,
            'required' => true,
            'comment' => app::get('systrade')->_('是否已经修改好运费'),
        ),
		'isconfirm_adjust_fee' => array(
            'type' => array(
                '0' => '未修改',
                '1' => '已修改',
            ),
            'default' => 0,
            'required' => true,
            'comment' => app::get('systrade')->_('是否已经修改好调价'),
        ),
        'payed_fee' => array (
            'type' => 'money',
            'default' => '0',
            'editable' => false,
            'comment' => app::get('systrade')->_('已支付金额(包含红包支付的金额)'),
        ),
        'hongbao_fee' => array(
            'type' => 'money',
            'default' => '0',
            'editable' => false,
            'comment' => app::get('systrade')->_('红包支付金额'),
        ),
        'user_hongbao_id' => array(
            'type' => 'string',
            'comment' => app::get('systrade')->_('使用红包支付的ID集合'),
        ),
        'seller_rate' => array(
            'type' => 'bool',
            /*
            'type' => array(
                'true' => '已评价',
                'false' => '未评价',
            ),
            */
            'default' => 0,
            'in_list' => true,
            'default_in_list' => false,
            'comment' => app::get('systrade')->_('卖家是否评价'),
            'label' => app::get('systrade')->_('卖家是否评价'),
        ),
        'receiver_name' => array(
            //'type' => 'varchar(20)',
            'type' => 'string',
            'length' => 50,

            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'has',
            'filtertype' => 'custom',
            'filterdefault' => true,
            'width' => '100',
            'order' => 15,
            'label' => app::get('systrade')->_('收货人姓名'),
            'comment' => app::get('systrade')->_('收货人姓名'),
        ),
        'created_time' => array(
            'type' => 'time',
            'in_list' => true,
            'default_in_list' => true,
            'width' => '100',
            'order' => 17,
            'label' => app::get('systrade')->_('订单创建时间'),
            'comment' => app::get('systrade')->_('订单创建时间'),
        ),
        'pay_time' => array(
            'type' => 'time',
            'comment' => app::get('systrade')->_('付款时间'),
        ),
        'consign_time' => array(
            'type' => 'time',
            'in_list' => true,
            'default_in_list' => false,
            'comment' => app::get('systrade')->_('卖家发货时间'),
            'label' => app::get('systrade')->_('卖家发货时间'),
        ),
        'end_time' => array(
            'type' => 'time',
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('systrade')->_('结束时间'),
            'comment' => app::get('systrade')->_('结束时间'),
        ),
        'modified_time' => array(
            'type' => 'last_modify',
            'in_list' => true,
            'default_in_list' => true,
            'width' => '100',
            'order' => 18,
            'label' => app::get('systrade')->_('修改时间'),
            'comment' => app::get('systrade')->_('修改时间'),
        ),
        'timeout_action_time' => array(
            'type' => 'time',
            'in_list' => true,
            'default_in_list' => false,
            'label' => app::get('systrade')->_('超时到期时间'),
            'comment' => app::get('systrade')->_('超时到期时间'),
        ),
        'delivery_goods_time' => array(
			'type' => 'time',
			'in_list' => true,
			'default_in_list' => true,
			'label' => app::get('systrade')->_('交货时间'),
			'comment' => app::get('systrade')->_('交货时间'),
        ),
        'send_time' => array(
            'type' => 'time',
            'comment' => app::get('systrade')->_('订单将在此时间前发出，主要用于预售订单'),
        ),
        'receiver_state' => array(
            //'type' => 'varchar(20)',
            'type' => 'string',
            'length' => 20,
            'comment' => app::get('systrade')->_('收货人所在省份'),
        ),
        'receiver_city' => array(
            //'type' => 'varchar(20)',
            'type' => 'string',
            'length' => 20,
            'comment' => app::get('systrade')->_('收货人所在城市'),
        ),
        'receiver_district' => array(
            //'type' => 'varchar(20)',
            'type' => 'string',
            'length' => 20,
            'comment' => app::get('systrade')->_('收货人所在地区'),
        ),
        'receiver_address' => array(
            //'type' => 'varchar(200)',
            'type' => 'string',
            'length' => 200,
            'in_list' => true,
            'default_in_list' => true,
            'width' => '150',
            'order' => 16,
            'label' => app::get('systrade')->_('收货人详细地址'),
            'comment' => app::get('systrade')->_('收货人详细地址'),
        ),
        'receiver_zip' => array(
            //'type' => 'varchar(20)',
            'type' => 'string',
            'length' => 20,
            'comment' => app::get('systrade')->_('收货人邮编'),
        ),
        'receiver_mobile' => array(
            //'type' => 'varchar(20)',
            'type' => 'string',
            'length' => 20,
            'comment' => app::get('systrade')->_('收货人手机号'),
        ),
        'receiver_phone' => array(
            //'type' => 'varchar(20)',
            'type' => 'string',
            'length' => 20,
            'comment' => app::get('systrade')->_('收货人电话'),
        ),
        'title' => array(
            //'type' => 'varchar(50)',
            'type' => 'string',
            'length' => 90,
            'in_list' => true,
            'default_in_list' => false,
            'label' => app::get('systrade')->_('交易标题'),
            'comment' => app::get('systrade')->_('交易标题'),
        ),
        'discount_fee' => array(
            'type' => 'money',
            'default' => '0',
            'comment' => app::get('systrade')->_('促销优惠金额'),
        ),
        'consume_point_fee' => array(
            'type' => 'number',
            'default' => '0',
            'comment' => app::get('systrade')->_('买家使用积分'),
        ),
        'buyer_message' => array(
            //'type' => 'varchar(255)',
            'type' => 'string',
            'comment' => app::get('systrade')->_('买家留言'),
        ),
        'need_invoice' => array(
            'type' => 'bool',
            'default' => 0,
            'comment' => app::get('systrade')->_('是否需要开票'),
        ),
        'invoice_name' => array(
            //'type' => 'varchar(20)',
            'type' => 'string',
            'length' => 100,
            'comment' => app::get('systrade')->_('发票抬头'),
        ),
        'invoice_type' => array(
            //'type' => 'varchar(20)',
            'type' => 'string',
            'length' => 20,
            'comment' => app::get('systrade')->_('发票类型'),
        ),
        'invoice_main' => array(
            //'type' => 'varchar(20)',
            'type' => 'string',
            'length' => 100,
            'comment' => app::get('systrade')->_('发票内容'),
        ),
        'invoice_vat_main' => array(
            'type' => 'serialize',
            'comment' => app::get('systrade')->_('增值税发票内容'),
        ),
	    'invoice_tfn' => array(
            //'type' => 'varchar(20)',
            'type' => 'string',
            'length' => 100,
            'comment' => app::get('systrade')->_('税号'),
        ),
        'invoice_bank_name' => array(
            //'type' => 'varchar(20)',
            'type' => 'string',
            'length' => 100,
            'comment' => app::get('systrade')->_('银行'),
        ),
        'invoice_bank_num' => array(
            //'type' => 'varchar(20)',
            'type' => 'string',
            'length' => 100,
            'comment' => app::get('systrade')->_('银行账号'),
        ),
        'invoice_addr' => array(
            //'type' => 'varchar(20)',
            'type' => 'string',
            'length' => 100,
            'comment' => app::get('systrade')->_('地址'),
        ),
        'invoice_mobile' => array(
            //'type' => 'varchar(20)',
            'type' => 'string',
            'length' => 100,
            'comment' => app::get('systrade')->_('手机号码'),
        ),
        'adjust_fee' => array(
            'type' => 'money',
            'default' => '0',
            'comment' => app::get('systrade')->_('卖家手工调整金额,子订单调整金额之和'),
        ),
        'trade_from' => array(
            'type' => array(
                'pc' => app::get('systrade')->_('标准平台'),
                'wap' => app::get('systrade')->_('手机触屏'),
                'weixin' => app::get('systrade')->_('微信商城'),
                'purchase' => app::get('systrade')->_('采购'),
                'app' => app::get('systrade')->_('手机APP'),
            ),
            'default' => 'pc',
            'in_list' => true,
            'default_in_list' => false,
            'label' => app::get('systrade')->_('订单来源'),
            'comment' => app::get('systrade')->_('订单来源'),
        ),
        'itemnum' => array(
            'type' => 'number',
            'default' => '0',
            'comment' => app::get('systrade')->_('子订单商品购买数量总数'),
        ),
        'shop_flag' => array(
            //'type' => 'varchar(20)',
            'type' => 'string',
            'length' => 20,
            'comment' => app::get('systrade')->_('卖家备注旗帜'),
        ),
        'shop_memo' => array(
            'type' => 'text',
            'comment' => app::get('systrade')->_('卖家备注'),
        ),
        'buyer_area' => array(
            //'type' => 'varchar(30)',
            'type' => 'string',
            'deny_export' => true,
            'length' => 30,
            'comment' => app::get('systrade')->_('买家下单的地区'),
        ),
        'area_id' => array(
            'type' => 'number',
            'deny_export' => true,
            'comment' => app::get('systrade')->_('区域id，代表订单下单的区位码'),
        ),
        'step_trade_status' => array(
            'type' => array(
                '0'=>'定金和尾款都付',
                '1' => '定金已付尾款未付',
                '2' => '定金未付尾款未付',
            ),
            'comment' => app::get('systrade')->_('分阶段付款的订单状态'),
        ),
        'total_weight' => array(
            'type' => 'decimal',
            'precision' => 20,
            'scale' => 3,
            'required' => true,
            'default' => 0,
            'label' => app::get('systrade')->_('商品重量'),
            'comment' => app::get('systrade')->_('商品重量'),
        ),
        'step_paid_fee' => array(
            'type' => 'money',
            'comment' => app::get('systrade')->_('分阶段付款的已付金额'),
        ),
        'shipping_type' => array(
            'type' => array(
                'free' => '卖家包邮',
                'ziti' => '自提',
                'post' => '平邮',
                'express' => '快递',
                'ems' => 'EMS',
                'virtual' => '虚拟发货',
            ),
            'comment' => app::get('systrade')->_('创建交易时的配送类型'),
            'label' => app::get('systrade')->_('创建交易时的配送类型'),
        ),
        'obtain_point_fee' => array(
            'type' => 'bigint',
            'comment' => app::get('systrade')->_('买家获得积分,返点的积分'),
        ),
        'trade_memo' => array(
            'type' => 'text',
            'comment' => app::get('systrade')->_('交易备注'),
        ),
        'buyer_rate' => array(
            'type' => 'bool',
            'default' => 0,
            'comment' => app::get('systrade')->_('买家是否已评价'),
        ),
        'sell_rate' => array(
            'type' => 'bool',
            'default' => 0,
            'comment' => app::get('systrade')->_('卖家是否已评价'),
        ),
        'is_part_consign' => array(
            'type' => 'bool',
            'default' => 0,
            'comment' => app::get('systrade')->_('是否是多次发货的订单'),
        ),
        'real_point_fee' => array(
            'type' => 'number',
            'comment' => app::get('systrade')->_('买家实际使用积分'),
        ),
        'ip' => array(
            'type' => 'string',
            'length' => 15,
            'editable' => false,
            'comment' => app::get('systrade')->_('IP地址'),
            'label' => app::get('systrade')->_('IP地址'),
            'in_list' => true,
        ),
        'anony' => array(
            'type' => 'bool',//1 匿名， 0 默认实名
            'default' => 0,
            'comment' => app::get('systrade')->_('下单选择的是否匿名，子订单将匿名修改该字段不修改，只表示下单的选择'),
        ),
        'is_clearing' => array(
            'type' => 'bool',
            /*
            'type' => array(
                'true' => '已生成结算单',
                'false' => '未生成结算单',
            ),
            */
            'default' => 0,
            'in_list' => true,
            'default_in_list' => false,
            'comment' => app::get('systrade')->_('是否生成结算单'),
            'label' => app::get('systrade')->_('是否生成结算单'),
        ),
        'disabled' => array(
            'type' => 'bool',
            'default' => 0,
            'required' => true,
            'editable' => false,
            'comment' => app::get('systrade')->_('是否有效'),
        ),
        'ziti_memo' => array(
            'type' => 'string',
            'length' => 300,
            'comment' => app::get('systrade')->_('自提备注'),

        ),
        'need_econtract' => array(
            'type' => 'bool',
            'default' => 1,	//默认需要
            'required' => false,
            'editable' => false,
            'comment' => app::get('systrade')->_('是否需要电子合同'),
        ),
        'contract_status' => array(
            'type' => array(
                '0' => '不需要合同',
                '1' => '需要合同',//未签
                '2' => '需要合同',//已签
            ),
            'required' => true,
            'in_list' => true,
            'width' => '100',
            'order' => 13,
            'label' => app::get('systrade')->_('是否需要合同'),
            'comment' => app::get('systrade')->_('是否需要合同'),
        ),
        'contract_id' => array(
            'type' => 'number',
            'default' => 0,
            'required' => true,
            'length' => 11,
            'label' => app::get('systrade')->_('合同ID'),
            'comment' => app::get('systrade')->_('合同ID'),
        ),
        'checkup_status' => array(
            'type' => array(
                '0' => '未审核',
                '1' => '已审核',
                '2' => '审核未通过',
            ),
            'default' => 0,
            'required' => true,
            'in_list' => true,
            'width' => '100',
            'label' => app::get('systrade')->_('合同审核状态'),
            'comment' => app::get('systrade')->_('合同审核状态'),
        ),
    	'send_time' => array(
                'type' => 'time',
                'in_list' => true,
                'default_in_list' => true,
                'width' => '100',
                'order' => 17,
                'label' => app::get('systrade')->_('合同审核时间'),
                'comment' => app::get('systrade')->_('合同审核时间'),
        ),
        'update_status' => array(
            'type' => array(
                '0' => '未上传',
                '1' => '已上传',
                '2' => '重新上传',
            ),
            'default' => 0,
            'required' => true,
            'in_list' => true,
            'width' => '100',
            'label' => app::get('systrade')->_('合同是否上传'),
            'comment' => app::get('systrade')->_('合同是否上传'),
        ),
        'position' => array(
            'type' => 'number',
            'default' => 0,
            'required' => true,
            'comment' => app::get('systrade')->_('已经完成几次支付'),
        ),
        'issend' => array(
            'type' => 'bool',
            'default' => 0,
            'comment' => app::get('systrade')->_('是否发送'),
            'editable' => false,
            'label' => app::get('systrade')->_('是否发送'),
            'in_list' => false,
        ),
        'order_type' => array(
            'type' => 'string',
            'length' => 16,
            'default' => 'normal',
            'comment' => app::get('systrade')->_('订单类型'),
        ),
    ),
    'primary' => 'tid',
    'index' => array(
        'ind_status' => ['columns' => ['status']],
        'ind_temai_user_id' => ['columns' => ['temai_user_id']],
        'ind_user_id' => ['columns' => ['user_id']],
        'ind_shop_id' => ['columns' => ['shop_id']],
        'ind_created_time' => ['columns' => ['created_time']],
    ),
    'comment' => app::get('systrade')->_('订单主表'),
);
