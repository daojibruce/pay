<?php

return array(
    'columns' =>
    array(
        'log_no' =>
        array(
            'type' => 'string',
            'length' => 50,
            'required' => true,
            'pkey' => true,
            'editable' => false,
            'searchtype' => 'has',
            'label' => '日志编号',
			'comment' => '日志编号，请求见证宝唯一日志号',
            'order' => '1',
        ),
        'tran_func' =>
        array(
            'type' =>
            array(
                //注册与绑定
                '6000' => '会员子账户开立【6000】',
                '6055' => '会员绑定提现账户-小额鉴权【6055】',
                '6064' => '验证鉴权金额【6064】',
                '6066' => '会员绑定提现账户-银联验证【6066】',
                '6067' => '验证短信验证码【6067】',
                '6065' => '会员解绑提现账户【6065】',
                //充值与提现
                '6056' => '会员清分(充值)【6056】',
                '6005' => '会员提现【6005】（验密）',
                '6033' => '会员提现【6033】',
                '6085' => '会员提现（支持手续费）【6085】',
                '6008' => '登记挂账【6008】',
                '6053' => '会员批量清分【6053】',
                //交易
                '6006' => '会员交易【6006】（验密）',
                '6034' => '会员交易【6034】',
                '6052' => '会员批量交易【6052】',
                '6031' => '平台订单管理【6031】',
                '6007' => '会员资金冻结【6007】',
                '6070' => '会员资金支付【6070】',
                '6077' => '交易撤销【6077】',
                //短信验证
                '6082' => '申请短信动态码【6082】',
                '6083' => '申请修改手机号码【6083】',
                '6084' => '回填动态码-修改手机【6084】',
                //查询
                '6010' => '查询银行子账户余额【6010】',
                '6014' => '查询银行单笔交易明细【6014】',
                '6048' => '查询银行提现退单信息【6048】',
                '6050' => '查询普通转账充值明细【6050】',
                '6072' => '查询银行时间段内交易明细【6072】',
                '6073' => '查询银行时间段内清分提现明细【6073】',
                '6011' => '查询资金汇总账户余额【6011】',
                '6037' => '查询会员子账号【6037】',
                //对账
                '6079' => '提现与清分对账接口【6079】',
                '6080' => '会员交易明细对账接口【6080】',
                '6068' => '理财购买【6068】',
                '6069' => '理财赎回【6069】',
                '6040' => '7日年化收益率查询【6040】',
                '6043' => '会员理财余额查询【6043】',
                '6044' => '会员每日收益查询【6044】',
                '6041' => '购买/赎回交易查询【6041】',
                '6042' => '单笔理财交易查询【6042】',
                '6061' =>'查询小额鉴权转账交易结果【6061】',
            ),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => true,
            'searchtype' => 'has',
            'label' => '交易码',
			'comment' => '交易码',
            'width' => '200',
            'order' => '3',
        ),
        'api_type' =>
        array(
            'type' =>
            array(
                'response' => '响应',
                'request' => '请求',
            ),
            'editable' => false,
            'default' => 'request',
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'yes',
            'filterdefault' => true,
            'label' => '同步类型',
			'comment' => '同步类型',
            'width' => '70',
        ),
        'log_type' =>
        array(
            'type' => 'string',
            'length' => 20,
			'required' => true,
			'default' => 'order',
            'editable' => false,
            'label' => '日志类型',
			'comment' => '日志类型',
        ),
		'trade_no' =>
        array(
            'type' => 'string',
            'length' => 50,
			'default' => '-',
            'editable' => false,
            'label' => '日志唯一性',
			'comment' => '交易号，支付、充值、提现、退款等',
        ),
        'status' =>
        array(
            'type' =>
            array(
                'running' => '运行中',
                'success' => '成功',
                'fail' => '失败',
                'sending' => '发起中',
            ),
            'required' => true,
            'default' => 'sending',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'editable' => false,
            'filtertype' => 'yes',
            'filterdefault' => true,
            'label' => '状态',
			'comment' => '状态',
            'width' => '60',
            'order' => '4',
        ),
        'params' =>
        array(
            'type' => 'text',
            'editable' => false,
            'label' => '日志参数',
			'comment' => '日志参数',
            'filtertype' => 'yes',
        ),
        'msg' =>
        array(
            'type' => 'text',
            'editable' => false,
            'label' => '同步消息',
			'comment' => '同步消息',
        ),
        'addon' =>
        array(
            'type' => 'text',
            'editable' => false,
            'label' => '附加参数',
			'comment' => '附加参数',
        ),
        'createtime' =>
        array(
            'type' => 'time',
            'label' => '发起同步时间',
			'comment' => '发起同步时间',
            'width' => '130',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'time',
            'filterdefault' => true,
            'order' => '7',
        ),
        'last_modified' =>
        array(
            'label' => '最后重试时间',
			'comment' => '最后重试时间',
            'type' => 'last_modify',
            'width' => '130',
            'editable' => false,
            // 'in_list' => true,
            // 'default_in_list' => true,
            'order' => '8',
        ),
    ),
    'index' => [
		'ind_log_type' => ['columns' => ['log_type']],
        'ind_createtime' => ['columns' => ['createtime']],
        'ind_last_modified' => ['columns' => ['last_modified']],
    ],
    'primary' => 'log_no',
    'comment' => '见证宝交易日志表',
    'engine' => 'innodb',
    'version' => '$Rev: 44513 $',
);
