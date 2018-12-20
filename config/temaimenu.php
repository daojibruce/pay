<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 * 商家管理中心菜单定义
 */

return array(
    /*
    |--------------------------------------------------------------------------
    | 商家管理中心之首页
    |--------------------------------------------------------------------------
     */
    'index' => array(
        'label' => '首页',
        'display' => true,
        'shopIndex' => true,
        'action' => 'toptemai_ctl_index@index',
        'icon' => 'glyphicon glyphicon-home',
        'menu' => array(
            array(
                'label'=>'首页',
                'display'=>false,
                'as'=>'toptemai.index',
                'action'=>'toptemai_ctl_index@index',
                'url'=>'/',
                'method'=>'get'
            ),
            array(
                'label'=>'浏览器检测',
                'display'=>false,
                'as'=>'toptemai.browserTip',
                'action'=>'toptemai_ctl_index@browserTip',
                'url'=>'browserTip.html',
                'method'=>'get'
            ),
        )
    ),

    /*
    |--------------------------------------------------------------------------
    | 商家管理中心之交易管理
    |--------------------------------------------------------------------------
     */
    'trade' => array(
        'label' => '交易',
        'display' => true,
        'action' => 'toptemai_ctl_trade_list@index',
        'icon' => 'glyphicon glyphicon-stats',
        'menu' => array(
            array('label'=>'订单管理','display'=>true,'as'=>'toptemai.trade.index','action'=>'toptemai_ctl_trade_list@index','url'=>'list.html','method'=>'get'),
            array('label'=>'订单搜索','display'=>false,'as'=>'toptemai.trade.search','action'=>'toptemai_ctl_trade_list@search','url'=>'trade/search.html','method'=>['get','post']),
            array('label'=>'订单详情','display'=>false,'as'=>'toptemai.trade.detail','action'=>'toptemai_ctl_trade_detail@index','url'=>'detail.html','method'=>'get'),
            array('label'=>'订单物流','display'=>false,'as'=>'toptemai.trade.detail.logi','action'=>'toptemai_ctl_trade_detail@ajaxGetTrack','url'=>'detail.html','method'=>'post'),
            array('label'=>'添加订单备注','display'=>false,'as'=>'toptemai.trade.detail.memo','action'=>'toptemai_ctl_trade_detail@setTradeMemo','url'=>'setMemo.html','method'=>'post','middleware'=>['toptemai_middleware_developerMode']),
            array('label'=>'修改订单价格页面','display'=>false,'as'=>'toptemai.trade.modifyPrice','action'=>'toptemai_ctl_trade_list@modifyPrice','url'=>'modifyprice.html','method'=>'get'),
            array('label'=>'保存修改订单价格','display'=>false,'as'=>'toptemai.trade.modifyPrice.post','action'=>'toptemai_ctl_trade_list@updatePrice','url'=>'updateprice.html','method'=>'post'),
            array('label'=>'订单发货','display'=>false,'as'=>'toptemai.trade.delivery','action'=>'toptemai_ctl_trade_flow@goDelivery','url'=>'delivery.html','method'=>'get'),
            //(电子)合同路由
            array('label'=>'调整订单详情订单信息','display'=>false,'as'=>'toptemai.trade.do_adjust_order','action'=>'toptemai_ctl_trade_detail@do_adjust_order','url'=>'do_adjust_order.html','method'=>'post'),
            array('label'=>'调整订单详情订单信息','display'=>false,'as'=>'toptemai.trade.ajaxPayList','action'=>'toptemai_ctl_trade_detail@ajaxPayList','url'=>'ajaxPayList.html','method'=>'post'),
            array('label'=>'获取订单详情订单合同金额','display'=>false,'as'=>'toptemai.trade.ajaxMoneyInfo','action'=>'toptemai_ctl_trade_detail@ajaxMoneyInfo','url'=>'ajaxMoneyInfo.html','method'=>'post'),
            array('label'=>'提交电子合同','display'=>false,'as'=>'toptemai.trade.send_contract','action'=>'toptemai_ctl_trade_detail@send_contract','url'=>'send_contract.html','method'=>'post'),
            array('label'=>'提交电子合同模板','display'=>false,'as'=>'toptemai.trade.send_template','action'=>'toptemai_ctl_trade_detail@send_template','url'=>'send_template.html','method'=>'post'),
            array('label'=>'发起电子合同跳转','display'=>false,'as'=>'toptemai.trade.addContract','action'=>'toptemai_ctl_trade_detail@addContract','url'=>'addContract.html','method'=>'get'),
            array('label'=>'发起电子合同预览','display'=>false,'as'=>'toptemai.trade.previewEcontract','action'=>'toptemai_ctl_trade_detail@previewEcontract','url'=>'previewEcontract.html','method'=>'get'),

            //订单货到付款时订单完成操作
            array('label'=>'ajax请求订单完成页面','display'=>false,'as'=>'toptemai.trade.finish','action'=>'toptemai_ctl_trade_list@ajaxFinishTrade','url'=>'ajaxfinish.html','method'=>'get'),
            array('label'=>'订单收钱并收货','display'=>false,'as'=>'toptemai.trade.postfinish','action'=>'toptemai_ctl_trade_list@finishTrade','url'=>'finish.html','method'=>'post'),

            //订单取消列表
            //ajax 请求订单信息以取消
            array('label'=>'ajax请求订单取消页面','display'=>false,'as'=>'toptemai.trade.close','action'=>'toptemai_ctl_trade_list@ajaxCloseTrade','url'=>'ajaxclose.html','method'=>'get','middleware'=>['toptemai_middleware_developerMode']),
            array('label'=>'ajax请求订单拒收页面','display'=>false,'as'=>'toptemai.trade.rejection','action'=>'toptemai_ctl_trade_list@ajaxCloseRejection','url'=>'ajaxrejection.html','method'=>'get'),
            array('label'=>'ajax请求发送自提提货码页面','display'=>false,'as'=>'toptemai.trade.ajaxSendDeliverySms','action'=>'toptemai_ctl_trade_list@ajaxSendDeliverySms','url'=>'ajaxSendDeliverySms.html','method'=>'get'),
            array('label'=>'ajax请求验证自提提货码页面','display'=>false,'as'=>'toptemai.trade.ajaxCheckDeliveryVcode','action'=>'toptemai_ctl_trade_list@ajaxCheckDeliveryVcode','url'=>'ajaxCheckDeliveryVcode.html','method'=>'get'),
            array('label'=>'发送自提提货码页面','display'=>false,'as'=>'toptemai.trade.sendDeliverySms','action'=>'toptemai_ctl_trade_list@sendDeliverySms','url'=>'sendDeliverySms.html','method'=>'post'),
            array('label'=>'验证自提提货码页面','display'=>false,'as'=>'toptemai.trade.checkDeliveryVcode','action'=>'toptemai_ctl_trade_list@checkDeliveryVcode','url'=>'checkDeliveryVcode.html','method'=>'post'),
            array('label'=>'订单取消','display'=>false,'as'=>'toptemai.trade.postclose','action'=>'toptemai_ctl_trade_list@closeTrade','url'=>'close.html','method'=>'post'),
            array('label'=>'订单取消管理','display'=>true,'as'=>'toptemai.trade.cancel.index','action'=>'toptemai_ctl_trade_cancel@index','url'=>'cancel/list.html','method'=>'get'),
            array('label'=>'订单取消详情','display'=>false,'as'=>'toptemai.trade.cancel.detail','action'=>'toptemai_ctl_trade_cancel@detail','url'=>'cancel/detail.html','method'=>'get'),
            array('label'=>'订单取消搜索','display'=>false,'as'=>'toptemai.trade.cancel.search','action'=>'toptemai_ctl_trade_cancel@ajaxSearch','url'=>'trade/cancel/search.html','method'=>['get','post']),
            array('label'=>'审核取消订单','display'=>false,'as'=>'toptemai.trade.cancel.check','action'=>'toptemai_ctl_trade_cancel@shopCheckCancel','url'=>'trade/cancel/check.html','method'=>'post','middleware'=>['toptemai_middleware_developerMode']),

            //店铺模板配置
            array('label'=>'快递模板配置','display'=>true,'as'=>'toptemai.dlytmpl.index','action'=>'toptemai_ctl_shop_dlytmpl@index','url'=>'wuliu/logis/templates.html','method'=>'get'),
            array('label'=>'快递模板配置编辑','display'=>false,'as'=>'toptemai.dlytmpl.edit','action'=>'toptemai_ctl_shop_dlytmpl@editView','url'=>'wuliu/logis/templates/create.html','method'=>'get'),
            array('label'=>'快递运费模板保存','display'=>false,'as'=>'toptemai.dlytmpl.save','action'=>'toptemai_ctl_shop_dlytmpl@savetmpl','url'=>'wuliu/logis/templates.html','method'=>'post'),
            array('label'=>'快递运费模板删除','display'=>false,'as'=>'toptemai.dlytmpl.delete','action'=>'toptemai_ctl_shop_dlytmpl@remove','url'=>'wuliu/logis/remove.html','method'=>'post'),
            array('label'=>'判断快递运费模板名称是否存在','display'=>false,'as'=>'toptemai.dlytmpl.isExists','action'=>'toptemai_ctl_shop_dlytmpl@isExists','url'=>'wuliu/logis/isExists.html','method'=>'post'),
            array('label'=>'物流公司','display'=>true,'as'=>'toptemai.dlycorp.index','action'=>'toptemai_ctl_shop_dlycorp@index','url'=>'wuliu/logis/dlycorp.html','method'=>'get'),
            array('label'=>'物流公司签约','display'=>false,'as'=>'toptemai.dlycorp.save','action'=>'toptemai_ctl_shop_dlycorp@signDlycorp','url'=>'wuliu/logis/savecorp.html','method'=>'post'),
            array('label'=>'物流公司解约','display'=>false,'as'=>'toptemai.dlycorp.cancel','action'=>'toptemai_ctl_shop_dlycorp@cancelDlycorp','url'=>'wuliu/logis/cancelcorp.html','method'=>'post'),
        ),
    ),

    /*
    |--------------------------------------------------------------------------
    | 商家管理中心之商家商品管理
    |--------------------------------------------------------------------------
     */
    'item' => array(
        'label' => '商品',
        'display' => true,
        'action'=> 'toptemai_ctl_item@itemList',
        'icon' => 'glyphicon glyphicon-edit',
        'menu' => array(
            array('label'=>'商品列表','display'=>true,'as'=>'toptemai.item.list','action'=>'toptemai_ctl_item@itemList','url'=>'item/itemList.html','method'=>'get'),
            array('label'=>'商品搜索','display'=>false,'as'=>'toptemai.item.search','action'=>'toptemai_ctl_item@searchItem','url'=>'item/search.html','method'=>['get','post']),
            array('label'=>'发布商品','display'=>true,'as'=>'toptemai.item.add','action'=>'toptemai_ctl_item@add','url'=>'item/add.html','method'=>'get'),
            array('label'=>'平台展销列表','display'=>true,'as'=>'toptemai.temai.list','action'=>'toptemai_ctl_temai@itemList','url'=>'temai/itemList.html','method'=>'get'),
            array('label'=>'申请平台展销','display'=>true,'as'=>'toptemai.temai.add','action'=>'toptemai_ctl_temai@add','url'=>'temai/add.html','method'=>'get'),
            array('label'=>'保存平台展销信息','display'=>false,'as'=>'toptemai.temai.storeItem','action'=>'toptemai_ctl_temai@storeItem','url'=>'temai/storeItem.html','method'=>'post'),
            array('label'=>'编辑商品','display'=>false,'as'=>'toptemai.item.edit','action'=>'toptemai_ctl_item@edit','url'=>'item/edit.html','method'=>'get'),
            array('label'=>'设置阶梯价格','display'=>false,'as'=>'toptemai.item.stepPrice','action'=>'toptemai_ctl_item@stepPrice','url'=>'item/stepPrice.html','method'=>'get'),
            array('label'=>'编辑阶梯价格','display'=>false,'as'=>'toptemai.item.editStepPrice','action'=>'toptemai_ctl_item@editStepPrice','url'=>'item/editStepPrice.html','method'=>'get'),
            array('label'=>'保存阶梯价格','display'=>false,'as'=>'toptemai.item.saveStepPrice','action'=>'toptemai_ctl_item@saveStepPrice','url'=>'item/saveStepPrice.html','method'=>'post'),
            array('label'=>'清除阶梯价格','display'=>false,'as'=>'toptemai.item.deleteStepPrice','action'=>'toptemai_ctl_item@deleteStepPrice','url'=>'item/deleteStepPrice.html','method'=>'get'),
            array('label'=>'商品库存报警','display'=>true,'as'=>'toptemai.storepolice.add','action'=>'toptemai_ctl_item@storePolice','url'=>'item/storepolice.html','method'=>'get'),
            array('label'=>'保存商品库存报警','display'=>false,'as'=>'toptemai.storepolice.save','action'=>'toptemai_ctl_item@saveStorePolice','url'=>'item/savestorepolice.html','method'=>'post'),
            array('label'=>'设置商品状态','display'=>false,'as'=>'toptemai.item.setStatus','action'=>'toptemai_ctl_item@setItemStatus','url'=>'item/setItemStatus.html','method'=>'post'),
            array('label'=>'删除商品','display'=>false,'as'=>'toptemai.item.delete','action'=>'toptemai_ctl_item@deleteItem','url'=>'item/deleteItem.html','method'=>'post'),
            array('label'=>'创建商品','display'=>false,'as'=>'toptemai.item.create','action'=>'toptemai_ctl_item@storeItem','url'=>'item/storeItem.html','method'=>'post'),

            array('label'=>'获取店铺支持品牌','display'=>false,'as'=>'toptemai.item.brand','action'=>'toptemai_ctl_item@ajaxGetBrand','url'=>'categories/getbrand.html','method'=>'post'),
            array('label'=>'获取店铺的运费模板','display'=>false,'as'=>'toptemai.item.dlytmpls','action'=>'toptemai_ctl_item@ajaxGetDlytmpls','url'=>'getdlytmpls.html','method'=>'get'),
            array('label'=>'更新商品的运费模板','display'=>false,'as'=>'toptemai.item.update.dlytmpls','action'=>'toptemai_ctl_item@updateItemDlytmpl','url'=>'updatedlytmpls.html','method'=>'post'),
            array('label'=>'商品导出','display'=>false,'as'=>'toptemai.item.export','action'=>'toptemai_ctl_item_importexport@export','url'=>'exportitem.html','method'=>'get'),
            array('label'=>'商品导入','display'=>false,'as'=>'toptemai.item.import','action'=>'toptemai_ctl_item_importexport@import','url'=>'importitem.html','method'=>'post'),
            array('label'=>'导出商品上传模板','display'=>false,'as'=>'toptemai.item.export.tmpl','action'=>'toptemai_ctl_item_importexport@downLoadImportTmpl','url'=>'exportitemtmpl.html','method'=>'get'),
            array('label'=>'商品上传模板','display'=>false, 'as'=>'toptemai.item.import.tmpl', 'action'=>'toptemai_ctl_item_importexport@importView','url'=>'importview.html','method'=>'get'),

            //图片管理
            array('label'=>'图片管理','display'=>true,'as'=>'toptemai.image.index','action'=>'toptemai_ctl_shop_image@index','url'=>'image.html','method'=>'get'),
            array('label'=>'根据条件搜索图片,tab切换','as'=>'toptemai.image.search','display'=>false,'action'=>'toptemai_ctl_shop_image@search','url'=>'image/search.html','method'=>'post'),
            array('label'=>'删除图片','display'=>false,'as'=>'toptemai.image.delete','action'=>'toptemai_ctl_shop_image@delImgLink','url'=>'image/delimglink.html','method'=>'post'),
            array('label'=>'修改图片名称','display'=>false,'as'=>'toptemai.image.upname','action'=>'toptemai_ctl_shop_image@upImgName','url'=>'image/upimgname.html','method'=>'post'),
            array('label'=>'商家使用图片加载modal','display'=>false,'as'=>'toptemai.image.loadModal','action'=>'toptemai_ctl_shop_image@loadImageModal','url'=>'image/loadimagemodal.html','method'=>'get'),
            array('label'=>'加载图片移动文件夹弹出框','display'=>false,'as'=>'toptemai.image.move.cat.loadModal','action'=>'toptemai_ctl_shop_image@loadImageMoveCatModal','url'=>'image/loadImageMoveCatModal.html','method'=>'post'),
            array('label'=>'图片移动文件夹','display'=>false,'as'=>'toptemai.image.move.cat','action'=>'toptemai_ctl_shop_image@moveImageCat','url'=>'image/loadImageMoveCat.html','method'=>'post'),

            array('label'=>'加载文件夹管理弹出框','display'=>false,'as'=>'toptemai.image.cat.loadImgCatModal','action'=>'toptemai_ctl_shop_image@loadImgCatModal','url'=>'image/loadImgCatModal.html','method'=>'post'),
            array('label'=>'创建图片文件夹','display'=>false,'as'=>'toptemai.image.add.cat','action'=>'toptemai_ctl_shop_image@addImgCat','url'=>'image/loadImageCreateCat.html','method'=>'post'),
            array('label'=>'删除图片文件夹','display'=>false,'as'=>'toptemai.image.del.cat','action'=>'toptemai_ctl_shop_image@delImgCat','url'=>'image/loadImageDelCat.html','method'=>'post'),
            array('label'=>'编辑图片文件夹','display'=>false,'as'=>'toptemai.image.update.cat','action'=>'toptemai_ctl_shop_image@editImgCat','url'=>'image/loadImageEditCat.html','method'=>'post'),
        ),
    ),

    /*
    |--------------------------------------------------------------------------
    | 商家管理中心之客户服务
    |--------------------------------------------------------------------------
     */
    /*
    'aftersales' => array(
        'label' => '客服',
        'display' => true,
        'action' => 'toptemai_ctl_aftersales@index',
        'icon' => 'icon-chatbubbles',
        'menu' => array(
            array('label'=>'退换货管理','display'=>true,'as'=>'toptemai.aftersales.list','action'=>'toptemai_ctl_aftersales@index','url'=>'aftersales-list.html','method'=>'get'),
            array('label'=>'退换货详情','display'=>false,'as'=>'toptemai.aftersales.detail','action'=>'toptemai_ctl_aftersales@detail','url'=>'aftersales-detail.html','method'=>'get'),
            array('label'=>'退换货搜索','display'=>false,'as'=>'toptemai.aftersales.search','action'=>'toptemai_ctl_aftersales@search','url'=>'aftersales-search.html','method'=>'post'),
            array('label'=>'退换货搜索','display'=>false,'as'=>'toptemai.aftersales.search','action'=>'toptemai_ctl_aftersales@search','url'=>'aftersales-search.html','method'=>'get'),
            array('label'=>'审核退换货申请','display'=>false,'as'=>'toptemai.aftersales.verification','action'=>'toptemai_ctl_aftersales@verification','url'=>'aftersales-verification.html','method'=>'post','middleware'=>['toptemai_middleware_developerMode'] ),
            array('label'=>'换货重新发货','display'=>false,'as'=>'toptemai.aftersales.sendConfirm','action'=>'toptemai_ctl_aftersales@sendConfirm','url'=>'aftersales-send.html','method'=>'post'),

            //评价管理&DSR管理
            array('label'=>'评价列表','display'=>true,'as'=>'toptemai.rate.list','action'=>'toptemai_ctl_rate@index','url'=>'rate-list.html','method'=>'get'),
            array('label'=>'评价搜索','display'=>false,'as'=>'toptemai.rate.search','action'=>'toptemai_ctl_rate@search','url'=>'rate-search.html','method'=>'get'),
            array('label'=>'评价详情','display'=>false,'as'=>'toptemai.rate.detail','action'=>'toptemai_ctl_rate@detail','url'=>'rate-detail.html','method'=>'get'),
            array('label'=>'评价回复','display'=>false,'as'=>'toptemai.rate.reply','action'=>'toptemai_ctl_rate@reply','url'=>'rate-reply.html','method'=>'post'),

            array('label'=>'申诉列表','display'=>true,'as'=>'toptemai.rate.appeal.list','action'=>'toptemai_ctl_rate_appeal@appealList','url'=>'rate-appeal-list.html','method'=>'get'),
            array('label'=>'申诉搜索','display'=>false,'as'=>'toptemai.rate.appeal.search','action'=>'toptemai_ctl_rate_appeal@search','url'=>'rate-appeal-search.html','method'=>'get'),
            array('label'=>'申诉详情','display'=>false,'as'=>'toptemai.rate.appeal.detail','action'=>'toptemai_ctl_rate_appeal@appeaInfo','url'=>'rate-appeal-info.html','method'=>'get'),
            array('label'=>'评价申诉','display'=>false,'as'=>'toptemai.rate.appeal','action'=>'toptemai_ctl_rate_appeal@appeal','url'=>'rate-appeal.html','method'=>'post'),

            array('label'=>'评价概况','display'=>true,'as'=>'toptemai.rate.count','action'=>'toptemai_ctl_rate_count@index','url'=>'rate-count.html','method'=>'get'),

            //咨询管理
            array('label'=>'咨询列表','display'=>true,'as'=>'toptemai.gask.list','action'=>'toptemai_ctl_consultation@index','url'=>'gask-list.html','method'=>'get'),
            array('label'=>'咨询回复','display'=>false,'as'=>'toptemai.gask.reply','action'=>'toptemai_ctl_consultation@doReply','url'=>'gask-reply.html','method'=>'post'),
            array('label'=>'咨询筛选','display'=>false,'as'=>'toptemai.gask.screening','action'=>'toptemai_ctl_consultation@screening','url'=>'gask-screening.html','method'=>'get'),
            array('label'=>'回复删除','display'=>false,'as'=>'toptemai.gask.delete','action'=>'toptemai_ctl_consultation@doDelete','url'=>'gask-del.html','method'=>'post'),
            array('label'=>'显示或关闭咨询与回复','display'=>false,'as'=>'toptemai.gask.display','action'=>'toptemai_ctl_consultation@doDisplay','url'=>'gask-display.html','method'=>'post'),

            //im相关管理-365webcall配置
            array('label'=>'365webcall配置','display'=>true,'as'=>'toptemai.im.webcall.index','action'=>'toptemai_ctl_im_webcall@index','url'=>'im-webcall.html','method'=>'get'),
            array('label'=>'365webcall配置','display'=>false,'as'=>'toptemai.im.webcall.applyPage','action'=>'toptemai_ctl_im_webcall@applyPage','url'=>'im-webcall-apply.html','method'=>'get'),
            array('label'=>'365webcall配置','display'=>false,'as'=>'toptemai.im.webcall.save','action'=>'toptemai_ctl_im_webcall@save','url'=>'im-webcall-save.html','method'=>'post'),
            array('label'=>'365webcall申请','display'=>false,'as'=>'toptemai.im.webcall.apply','action'=>'toptemai_ctl_im_webcall@apply','url'=>'im-webcall-apply.html','method'=>'post'),

        ),
    ),
    */

    'shopinfo' => array(
        'label' => '结算',
        'display' => true,
        'action' => 'toptemai_ctl_shop_shopinfo@index',
        'icon' => 'glyphicon glyphicon-cloud',
        'menu' => array(
            array('label'=>'商家结算汇总','display'=>true,'as'=>'toptemai.settlement','action'=>'toptemai_ctl_clearing_settlement@index','url'=>'shop/settlement.html','method'=>'get'),
            array('label'=>'商家结算明细','display'=>true,'as'=>'toptemai.settlement.detail','action'=>'toptemai_ctl_clearing_settlement@detail','url'=>'shop/settlement_detail.html','method'=>'get'),
        )
    ),

    'sysstat' => array(
        'label' => '报表',
        'display' => true,
        'action' => 'toptemai_ctl_sysstat_sysstat@index',
        'icon' => 'glyphicon glyphicon-list-alt',
        'menu' => array(
            array('label'=>'商家运营概况','display'=>true,'as'=>'toptemai.sysstat','action'=>'toptemai_ctl_sysstat_sysstat@index','url'=>'sysstat/sysstat.html','method'=>'get'),
            array('label'=>'交易数据分析','display'=>true,'as'=>'toptemai.stattrade','action'=>'toptemai_ctl_sysstat_stattrade@index','url'=>'sysstat/stattrade.html','method'=>'get'),
            array('label'=>'业务数据分析','display'=>true,'as'=>'toptemai.sysbusiness','action'=>'toptemai_ctl_sysstat_sysbusiness@index','url'=>'sysstat/sysbusiness.html','method'=>'get'),
            array('label'=>'商品销售分析','display'=>true,'as'=>'toptemai.sysstat.itemtrade.index','action'=>'toptemai_ctl_sysstat_itemtrade@index','url'=>'sysstat/itemtrade.html','method'=>'get'),
            array('label'=>'问题订单分析','display'=>true,'as'=>'toptemai.sysstat.aftersales.index','action'=>'toptemai_ctl_sysstat_stataftersales@index','url'=>'sysstat/stataftersales.html','method'=>'get'),
            array('label'=>'流量数据分析','display'=>true,'as'=>'toptemai.sysstat.systraffic.index','action'=>'toptemai_ctl_sysstat_systraffic@index','url'=>'sysstat/systraffic.html','method'=>'get'),
        )
    ),
);

