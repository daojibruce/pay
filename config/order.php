<?php

return [
    'status' => [
        'WAIT_BUYER_PAY' => '等待买家付款',
        'REJECT_CONTRACT' => '买家拒绝合同',
        'WAIT_SELLER_SEND_GOODS' => '等待卖家发货',
        'WAIT_BUYER_CONFIRM_GOODS' => '等待买家确认收货',
        'WAIT_SELLER_CONFIRM_GOODS' => '等待卖家确认收货',
        'TRADE_BUYER_SIGNED' => '买家已签收',
        'TRADE_FINISHED' => '交易成功',
        'TRADE_CLOSED_AFTER_PAY' => '用户退款成功',
        'TRADE_CLOSED_BEFORE_PAY' => '关闭交易',
        'WAIT_EVALUATE'=>'等待评价',
    ],
    'payStatus' => [
        'succ' => '支付成功',
        'failed' => '支付失败',
        'cancel' => '未支付',
        'error' => '处理异常',
        'invalid' => '非法参数',
        'progress' => '已付款至担保方',
        'timeout' => '超时',
        'ready' => '准备中',
        'paying' => '支付中'
    ],
    'tmOrderStatus' => [//此项，只用来显示在平台展销页面的最新订单
        'WAIT_BUYER_PAY' => '等待买家付款',
        'REJECT_CONTRACT' => '买家拒绝合同',
        'WAIT_SELLER_SEND_GOODS' => '等待卖家发货',
        'WAIT_BUYER_CONFIRM_GOODS' => '等待买家确认收货',
        'WAIT_SELLER_CONFIRM_GOODS' => '等待卖家确认收货',
        'TRADE_BUYER_SIGNED' => '买家已签收',
        'TRADE_FINISHED' => '交易成功',
        'TRADE_CLOSED_AFTER_PAY' => '用户退款成功',
        'TRADE_CLOSED_BEFORE_PAY' => '已售完',
        'WAIT_EVALUATE'=>'等待评价',
    ],

];