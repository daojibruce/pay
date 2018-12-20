<?php /**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


/*
|--------------------------------------------------------------------------
| 店铺子域名路由
|--------------------------------------------------------------------------
*/
try{
    if(config::get('app.subdomain_enabled')){
        $domain_prefix = ['domain' => '{subdomain:(?!www)[\w\d-]+}.'.config::get('app.subdomain_basic')];
    }else{
        $domain_prefix = ['prefix' => 'shopcenter'];
    }
}catch (Exception $e){
    $domain_prefix = ['prefix' => 'shopcenter'];
}
route::group($domain_prefix, function() {
    # 店铺首页
    route::get('', [ 'as'=>'topc.shopcenter', 'uses' => 'topc_ctl_shopcenter@index' ]);
    # 店铺搜索
    route::post('search.html', [ 'uses' => 'topc_ctl_shopcenter@search' ]);
    route::get('search.html', [ 'uses' => 'topc_ctl_shopcenter@search' ]);
});

/*
|--------------------------------------------------------------------------
| api
|--------------------------------------------------------------------------
 */
route::group(array('prefix' => 'api'), function()
{
    route::match(array('GET','POST'),'/api.json',['as'=>'api/api.json','uses'=>'system_ctl_getApiJson@index']);
    route::match(array('GET','POST'),'/', ['uses'=>'base_rpc_server@process', 'middleware' => ['system_middleware_checkApiSystemParams']]);
});

#OMS API
route::group(array('prefix' => 'oms'), function()
{
    route::match(array('GET','POST'),'/', ['uses'=>'oms_server@process']);
});


route::group(array('prefix' => 'topapi'), function()
{
    route::match(array('GET','POST'),'/', ['uses'=>'topapi_server@process']);
});

/*
route::get('/kkk/{id}', ['as' => 'iiii', function($id) {
        echo $id;
    }]);
*/
/*
|--------------------------------------------------------------------------
| PC端消费者平台
|--------------------------------------------------------------------------
*/

route::group(array('middleware' => ['theme_middleware_preview']), function() {
    /*
    |--------------------------------------------------------------------------
    | 会员登录注册相关
    |--------------------------------------------------------------------------
    */
    route::group(array(), function() {
        #bank测试
        route::get('bank.html' , ['as' => 'bank_index' , 'uses' => 'topc_ctl_bank@index']);
        # 登陆页
        route::get('passport-signin.html', [ 'middleware' => 'topc_middleware_redirectIfAuthenticated','as' => 'what',
                                             'uses' => 'topc_ctl_passport@signin' ]);
        # 登陆
        route::post('passport-signin.html', [
                                                'middleware' => ['topc_middleware_redirectIfAuthenticated'],
                                              'uses' => 'topc_ctl_passport@login' ]);
        # 注册页
        route::get('passport-signup.html', ['middleware' => 'topc_middleware_redirectIfAuthenticated',
                                            'uses' => 'topc_ctl_passport@signup' ]);
        //route::get('passport-selfsignup.html', ['middleware' => 'topc_middleware_redirectIfAuthenticated', 'uses' => 'topc_ctl_passport@selfsignup' ]);
        # 注册成功页
        route::get('passport-signup-success.html', ['middleware' => 'topc_middleware_authenticate',
                                                    'uses' => 'topc_ctl_passport@signupSuccess']);
        # 注册
        route::post('passport-signup.html', ['uses' => 'topc_ctl_passport@create' ]);
        route::post('passport-sendVcode.html', [ 'uses' => 'topc_ctl_passport@sendVcode' ]);
        route::post('passport-getVcode.html', [ 'uses' => 'topc_ctl_passport@getVcode' ]);
        # 注册验证
        route::post('passport-signupcheck.html', [ 'uses' => 'topc_ctl_passport@checkLoginAccount' ]);
        # 找回密码1
        route::get('passport-findpwd.html', [ 'uses' => 'topc_ctl_passport@findPwd' ]);
        # 找回密码2
        route::post('passport-findpwdtwo.html', [ 'uses' => 'topc_ctl_passport@findPwdTwo' ]);
        route::get('passport-findpwdtwo.html', [ 'uses' => 'topc_ctl_passport@findPwdTwo' ]);
        # 找回密码3
        route::match(array('GET', 'POST'), 'passport-findpwdthree.html', ['uses' => 'topc_ctl_passport@findPwdThree']);
        # #找回密码短信验证码发送
        route::post('passport-findpwdfour.html', [ 'uses' => 'topc_ctl_passport@findPwdFour' ]);
        # 找回密码短信验证码发送
        //route::post('passport-sendVcode.html', [ 'uses' => 'topc_ctl_passport@sendVcode' ]);
        # 信任登陆
        # callback
        /*
        route::get('trustlogin-bind.html', [ 'uses' => 'topc_ctl_trustlogin@callBack' ]);
        # 设置新的账号
        route::post('trustlogin.html', [ 'uses' => 'topc_ctl_trustlogin@setLogin' ]);
        # 绑定已有账户
        route::post('trustlogin-binds.html', [ 'uses' => 'topc_ctl_trustlogin@checkLogin' ]);
        # 模拟登陆
        route::get('trustlogin-postlogin.html', [ 'uses' => 'topc_ctl_trustlogin@postLogin' ]);
        */
        route::get('trustlogin-bind.html', [ 'middleware' => 'topc_middleware_redirectIfAuthenticated',
                                             'uses' => 'topc_ctl_trustlogin@callBack' ]);
        route::post('bindDefaultCreateUser.html', [ 'middleware' => 'topc_middleware_redirectIfAuthenticated',
                                                    'uses' => 'topc_ctl_trustlogin@bindDefaultCreateUser' ]);
        route::post('bindExistsUser.html', [  'middleware' => 'topc_middleware_redirectIfAuthenticated',
                                              'uses' => 'topc_ctl_trustlogin@bindExistsUser' ]);
        route::post('bindSignupUser.html', [ 'middleware' => 'topc_middleware_redirectIfAuthenticated',
                                             'uses' => 'topc_ctl_trustlogin@bindSignupUser' ]);

        # 退出
        route::get('passport-logout.html', [ 'middleware' => 'topc_middleware_authenticate',
                                             'uses' => 'topc_ctl_passport@logout' ]);

        # 企业账号注册开始
        //企业申请主页面
        route::get('passport-companysignup.html', [ 'uses' => 'topc_ctl_passport@companysignup']);
        //企业申请创建
        route::post('passport-companysignup.html', [ 'uses' => 'topcompany_ctl_passport@companycreate']);
        # 企业修改页
        route::get('passport-company-signin.html', [ 'uses' => 'topc_ctl_passport@updateCompany']);
        //企业申请更新
        route::post('passport-company-signup.html', [ 'uses' => 'topcompany_ctl_passport@companyupdate']);

        # 会员中心公司资料
        route::get('member-seInfoCompanydisplay.html', [ 'uses' => 'topc_ctl_member@seInfoCompanydisplay']);
        route::get('member-seInfoCompany.html', [ 'uses' => 'topc_ctl_member@seInfoCompany']);
        route::post('member-saveInfoCompany.html', [ 'uses' => 'topc_ctl_member@saveInfoCompany']);

        # 企业注册验证
        route::post('passport-cpysignupcheck.html', [ 'uses' => 'topcompany_ctl_passport@checkLoginPassword']);
        route::post('passport-cpypswcheck.html', [ 'uses' => 'topcompany_ctl_passport@checkLoginConfirmpwd']);
        route::post('passport-verifyPassword.html', [ 'uses' => 'topcompany_ctl_passport@verifyPassword']);
        //route::get('passport-signoption.html', ['middleware' => 'topc_middleware_redirectIfAuthenticated',
        //   'uses' => 'topc_ctl_passport@signoption']);
        # 企业账号注册结束

        #申请平台展销身份
        route::get('member-temaiapply.html', [ 'uses' => 'topc_ctl_member@temaiapply']);
        route::post('member-temaisavelicense.html', [ 'uses' => 'topc_ctl_member@temaisavelicense']);

		#申请开通oms界面
		route::get('member-oms.html', [ 'uses' => 'topc_ctl_member@oms']);
        route::post('oms-open.html', [ 'uses' => 'oms_ctl_index@open']);
    });

    /*
    |--------------------------------------------------------------------------
    | 文章相关
    |--------------------------------------------------------------------------
    */
    route::group(array(), function() {
        route::get('content-index.html', [ 'uses' => 'topc_ctl_content@index' ]);
        route::get('content-info.html', [ 'uses' => 'topc_ctl_content@getContentInfo', 'as' => 'topc.content.detail' ]);
        route::get('other-content-info.html', [ 'uses' => 'topc_ctl_content@getOtherContentInfo']);
    });

    /*
    |--------------------------------------------------------------------------
    | 商品首页详细页相关
    |--------------------------------------------------------------------------
    */

    route::group(array(), function() {
		/* 新频道首页 */
        # 首页
        route::get('/', [ 'as' => 'topc', 'uses' => 'topc_ctl_default@index']);
        # 品牌页
         route::get('/brands.html', [ 'as' => 'topc', 'uses' => 'topc_ctl_default@brands']);
        # 平台展销页
        route::get('/platforms.html', [ 'as' => 'topc', 'uses' => 'topc_ctl_proofing@index']);
        # 服务撮合页
        route::get('/matchmaking.html', [ 'as' => 'topc', 'uses' => 'topc_ctl_temai_default@index']);

        #企业云
        route::get('ecloud.html', [ 'as' => 'topc', 'uses' => 'topc_ctl_default@ecloud']);
        #金融中心
        route::get('witness.html', [ 'as' => 'topc', 'uses' => 'topc_ctl_default@witness']);
        #一带一路
        route::get('obor.html', [ 'as' => 'topc', 'uses' => 'topc_ctl_default@obor']);
        #物流中心
        route::get('logistics.html', [ 'as' => 'topc', 'uses' => 'topc_ctl_default@logistics']);
        //这里做了一个跳转口，用于登陆等需要跳转的地方，可以跳转post等方式
        route::get('/redirect.html', [ 'as' => 'topc.redirect', 'uses' => 'topc_ctl_default@redirect']);
        //----------
        # 商品收藏
        route::post('member_fav.html', [ 'middleware' => 'topc_middleware_authenticate',
                                         'uses' => 'topc_ctl_collect@ajaxFav' ]);
        route::post('member-collectdel.html', [ 'middleware' => 'topc_middleware_authenticate',
                                                'uses' => 'topc_ctl_collect@ajaxFavDel' ]);
        # 店铺收藏
        route::post('member_favshop.html', [ 'middleware' => 'topc_middleware_authenticate',
                                             'uses' => 'topc_ctl_collect@ajaxFavshop' ]);
        route::post('member-collectshopdel.html', [ 'middleware' => 'topc_middleware_authenticate',
                                                    'uses' => 'topc_ctl_collect@ajaxFavshopDel' ]);
        # 商品列表
        route::get('list.html', [ 'uses' => 'topc_ctl_list@index' ]);

        # 商品列表页加入购物车
        route::get('mini_spec.html', [ 'uses' => 'topc_ctl_item@miniSpec' ]);
        # 商城一级类目页
        route::get('topics.html', [ 'as' => 'topc.topics', 'uses' => 'topc_ctl_topics@index' ]);
        # 品牌列表
        route::get('brand.html', [ 'uses' => 'topc_ctl_brand@index' ]);
        # 第三方服务商列表
        route::get('thirdparty.html', [ 'uses' => 'topc_ctl_thirdparty@index' ]);
        # 首字母插入
        //route::get('do.html', [ 'uses' => 'topc_ctl_brand@insertLetter' ]);

        # 商品详情
        route::get('item.html', ['as' =>'topc.item.detail', 'uses' => 'topc_ctl_item@index' ]);
        // 获取商品关联的组合促销
        route::get('item-package.html', ['as' =>'topc.item.package', 'uses' => 'topc_ctl_item@getPackage' ]);
        // 异步获取商品的规格信息
        route::get('item-getSpecSku.html', ['as' =>'topc.item.getSpecSku', 'uses' => 'topc_ctl_item@getSpecSku' ]);
        # 根据组合促销id获取对应组合商品的规格信息
        route::get('item-packageItemSpec.html', ['as' =>'topc.item.packageItemSpec', 'uses' => 'topc_ctl_item@getPackageItemSpec' ]);
        #商品详情页，评价列表
        route::get('item-rate.html', [ 'uses' => 'topc_ctl_item@getItemRate' ]);
        route::get('item-rate-list.html', [ 'uses' => 'topc_ctl_item@getItemRateList' ]);

        //商品详情页存储浏览商品纪录
        route::get('item/browserHistory.html', [ 'uses' => 'topc_ctl_item@setBrowserHistory' ]);

        //查询阶梯价格
        route::post('item/getStepPrice.html', [ 'uses' => 'topc_ctl_item@getStepPrice' ]);

        #商品详情页，促销
        route::get('promotion-item.html', [ 'uses' => 'topc_ctl_promotion@getPromotionItem' ]);
        // 促销专题页
        route::get('promotion-page/{page_id}.html', [ 'uses' => 'topc_ctl_promotion@ProjectPage' ]);
        route::get('lottery.html', ['uses' => 'topc_ctl_lottery@index' ]);
        route::post('ajax/lottery-prize.html',['middleware' => 'topc_middleware_authenticate', 'uses' => 'topc_ctl_lottery@getPrize' ]);
        route::post('lottery-exchangenum.html',['middleware' => 'topc_middleware_authenticate', 'uses' => 'topc_ctl_lottery@getExchangeNum' ]);
        route::post('lottery-infoDialog.html', ['middleware' => 'topc_middleware_authenticate', 'uses' => 'topc_ctl_lottery@lottery_info_dialog']); #收货地址弹框
        # 优惠券关联商品列表页
        route::get('promotion-coupon-item.html', [ 'uses' => 'topc_ctl_promotion@getCouponItem' ]);
        #商品详情页,到货通知
        route::post('user-item.html', [ 'uses' => 'topc_ctl_memberItem@userNotifyItem' ]);

        #商品详情页，咨询
        route::get('item-gack.html', [ 'uses' => 'topc_ctl_item@getItemConsultation' ]);
        route::get('item-gack-list.html', [ 'uses' => 'topc_ctl_item@getItemConsultationList' ]);
        #提交资讯信息
        route::post('item-gack-add.html', [ 'uses' => 'topc_ctl_item@commitConsultation' ]);
        #所有活动列表页
        route::get('activity/index.html', [ 'uses' => 'topc_ctl_activity@index' ]);
        route::get('activity/item_list.html', [ 'uses' => 'topc_ctl_activity@activity_item_list' ]);
        route::post('activity/remind.html', [ 'uses' => 'topc_ctl_activity@saleRemind' ]);
        route::get('activity/itemlist.html', [ 'uses' => 'topc_ctl_activity@itemlist' ]);
        route::get('activity/detail.html', [ 'uses' => 'topc_ctl_activity@detail' ]);
        route::post('activity/toremind.html',['uses' => 'topc_ctl_activity@toSaleRemind']);
        #微信扫码
        route::post('checkpayment.html', [ 'uses' => 'topc_ctl_paycenter@checkPayments' ]);
        route::match(array('GET', 'POST','PUT','DELETE'),'wxqrpay.html',[ 'uses' => 'topc_ctl_wechat@wxqrpay']);
    });

    // 平台展销中心
    route::group(array(), function() {
        route::get('temai.html', [ 'uses' => 'topc_ctl_temai_default@index' ]);
        route::get('temai-item.html', [ 'uses' => 'topc_ctl_temai_item@index' ]);
        route::get('temai-list.html', [ 'uses' => 'topc_ctl_temai_list@index' ]);
        route::get('temai-list-num.html', [ 'uses' => 'topc_ctl_temai_list@num_list' ]);
        route::get('temai-trade.html', [ 'uses' => 'topc_ctl_temai_trade@tradeList' ]);
    });

    /*
    |--------------------------------------------------------------------------
    | 店铺相关
    |--------------------------------------------------------------------------
    */
    route::group(array(), function() {
        # 店铺首页
        // route::get('shopcenter.html', [ 'as'=>'topc.shopcenter', 'uses' => 'topc_ctl_shopcenter@index' ]);
        # 店铺前台优惠券列表页
        route::get('shopCouponList.html', [ 'uses' => 'topc_ctl_shopcenter@shopCouponList' ]);
        # 店铺列表
        route::get('shopList.html', ['uses' => 'topc_ctl_list@shopList' ]);
        # 领取优惠券
        route::post('getCoupon.html', [ 'middleware' => 'topc_middleware_authenticate',
                                        'uses' => 'topc_ctl_shopcenter@getCouponCode' ]);
        # 领取优惠券成功页
        route::get('getCouponResult.html', [ 'uses' => 'topc_ctl_shopcenter@getCouponResult' ]);
        // route::get('search.html', [ 'uses' => 'topc_ctl_shopcenter@search' ]);

        #搜索
        route::post('search.html', [ 'uses' => 'topc_ctl_search@index' ]);

        // article
        route::get('shop-article.html', [ 'uses' => 'topc_ctl_shopcenter@shopArticle' ]);
        route::post('ajaxGetCats.html',     [ 'as' => 'topshop.register.ajaxGetCats',              'uses' => 'topc_ctl_passport@ajaxGetCats' ]);

        #sitemap
        # 列表
        //route::get('sitemaps.html', [ 'uses' => 'site_ctl_sitemaps@catalog' ]);
        # 目录明细
        //route::get('sitemaps/{id}.html', [ 'uses' => 'site_ctl_sitemaps@index' ]);

    });

    /*
    |--------------------------------------------------------------------------
    | 会员中心
    |--------------------------------------------------------------------------
    */
    route::group(array('middleware' => 'topc_middleware_authenticate'), function() {
        # 会员中心首页
        route::get('member-index.html', [ 'as' => 'topc.member.index', 'uses' => 'topc_ctl_member@index' ]);
        route::get('control.html', [ 'as' => 'topc.member.control', 'uses' => 'topc_ctl_member@control']);
        route::post('member-tradelist.html', [ 'as' => 'topc.member.tradelist', 'uses' => 'topc_ctl_member@tradeStatusList' ]);
        # 会员中心个人资料
        route::get('member-seinfoset.html', [ 'uses' => 'topc_ctl_member@seInfoSet' ]);
        # 会员中心个人资料
        route::post('member-saveinfoset.html', [ 'uses' => 'topc_ctl_member@saveInfoSet' ]);
        # 会员中心信任登陆密码修改
        route::get('member-pwdset.html', [ 'uses' => 'topc_ctl_member@pwdSet' ]);
        # 会员中心信任登陆密码修改
        route::post('member-savepwdset.html', [ 'uses' => 'topc_ctl_member@savePwdSet' ]);
        # 会员中心安全中心
        route::get('member-security.html', [ 'uses' => 'topc_ctl_member@security' ]);
        # 会员中心安全中心密码修改
        route::get('member-modifypwd.html', [ 'uses' => 'topc_ctl_member@modifyPwd' ]);
        # 会员中心安全中心密码修改
        route::post('member-savemodifypwd.html', [ 'uses' => 'topc_ctl_member@saveModifyPwd' ]);
        # 会员中心手机/邮箱绑定
        route::get('member-setuserinfo.html', [ 'uses' => 'topc_ctl_member@verify' ]);
        # 会员中心登录检测
        route::post('member-checkUserLogin.html', [ 'uses' => 'topc_ctl_member@CheckSetInfo' ]);
        route::get('member-setinfoone.html', [ 'uses' => 'topc_ctl_member@setUserInfoOne' ]);# 会员中心手机
        # 会员中心短信验证码发送
        route::post('member-sendVcode.html', [ 'uses' => 'topc_ctl_member@sendVcode' ]);
        # 会员中心个人信息最后保存
        route::post('member-bindMobile.html', [ 'uses' => 'topc_ctl_member@bindMobile' ]);
        route::get('member-bindEmail.html', [ 'uses' => 'topc_ctl_member@bindEmail' ]);
        # 会员中心个人信息最后保存后的跳转
        route::get('member-setinfolast.html', [ 'uses' => 'topc_ctl_member@setUserInfoLast' ]);
        # 会员中心解绑手机邮箱
        route::get('member-unverify.html', [ 'uses' => 'topc_ctl_member@unVerifyOne' ]);
        route::post('member-checkvcode.html', [ 'uses' => 'topc_ctl_member@checkVcode' ]);
        route::get('member-unverifytwo.html', [ 'uses' => 'topc_ctl_member@unVerifyTwo' ]);
        route::post('member-unverifymobile.html', [ 'uses' => 'topc_ctl_member@unVerifyMobile' ]);
        route::get('member-unverifyemail.html', [ 'uses' => 'topc_ctl_member@unVerifyEmail' ]);
        route::get('member-unverifylast.html', [ 'uses' => 'topc_ctl_member@unVerifyLast' ]);
        # 会员中心收货地址管理
        route::get('member-address.html', [ 'uses' => 'topc_ctl_member@address' ]);
        # 会员中心收货地址修改
        route::post('member-updateaddr.html', [ 'uses' => 'topc_ctl_member@ajaxAddrUpdate' ]);
        # 会员中心默认收货地址
        route::post('member-addrdef.html', [ 'uses' => 'topc_ctl_member@ajaxAddrDef' ]);
        # 删除会员中心收货地址
        route::post('member-deladdr.html', [ 'uses' => 'topc_ctl_member@ajaxDelAddr' ]);
        #会员中心收货地址添加
        route::post('member-address.html', [ 'uses' => 'topc_ctl_member@saveAddress','middleware'=>'topc_middleware_formDuplication' ]);
        # 会员中心店铺收藏
        route::get('member-collectshops.html', [ 'uses' => 'topc_ctl_member@shopsCollect' ]);
        # 会员中心商品收藏
        route::get('member-collectitems.html', [ 'uses' => 'topc_ctl_member@itemsCollect' ]);
        # 会员中心优惠券列表
        route::get('member-coupon.html', [ 'uses' => 'topc_ctl_member_coupon@couponList' ]);
        # 会员中心奖品列表
        route::get('member-lottery.html', [ 'uses' => 'topc_ctl_member_lottery@prizeList' ]);
        # 会员中心奖品收货地址添加
        route::post('ajax/prizeAddr.html', [ 'uses' => 'topc_ctl_member_lottery@saveAddr' ]);
        #会员签到
        route::post('member-checkin.html', [ 'uses' => 'topc_ctl_member@checkin' ]);
        #会员服务撮合需求
        route::get('member-myrequirement.html', [ 'uses' => 'topc_ctl_member@myRequirement' ]);
        route::post('member-cancelRequirement.html', [ 'uses' => 'topc_ctl_member@cancelRequirement' ]);
        route::post('member-delRequirement.html', [ 'uses' => 'topc_ctl_member@delRequirement' ]);
        #会员服务撮合报价
        route::get('member-myrequirementprice.html', [ 'uses' => 'topc_ctl_member@myRequirementPrice' ]);
        route::post('member-bidoffer.html', [ 'uses' => 'topc_ctl_member@bidOffer' ]);
        #会员服务撮合订单
        route::get('member-myrequirementOrder.html', [ 'uses' => 'topc_ctl_member_trade@myRequirementOrder' ]);
        #服务撮合服务商中心
        route::get('member-providerCenter.html', [ 'uses' => 'topc_ctl_member_proofing@index' ]);
        route::get('member-providerOffer.html', [ 'uses' => 'topc_ctl_member_proofing@offer' ]);
        route::get('member-providerList.html', [ 'uses' => 'topc_ctl_member_proofing@tradeList' ]);
        route::match(array('POST', 'GET'), 'member-orderSearch.html.html', [ 'uses' => 'topc_ctl_member_proofing@search']);
        route::get('member-providerAftersales.html', [ 'uses' => 'topc_ctl_member_proofing@aftersales' ]);
        route::get('member-providerSettlement.html', [ 'uses' => 'topc_ctl_member_proofing@settlement' ]);
        route::get('member-sampleDrawing.html', [ 'uses' => 'topc_ctl_member_proofing@downloadDrawing' ]);
        route::post('member-addOffer.html', [ 'uses' => 'topc_ctl_member_proofing@addOffer' ]);
        route::get('member-myOffer.html', [ 'uses' => 'topc_ctl_member_proofing@myOffer' ]);
        route::get('member-startEContract.html', [ 'uses' => 'topc_ctl_member_proofing@startEContract' ]);
        route::post('member-ajaxMoneyInfo.html', [ 'uses' => 'topc_ctl_member_proofing@ajaxMoneyInfo' ]);
        route::post('member-send_template.html', [ 'uses' => 'topc_ctl_member_proofing@send_template' ]);
        route::get('member-orderDetail.html', [ 'uses' => 'topc_ctl_member_proofing@orderDetail' ]);
        route::get('member-tradeCancel.html', [ 'uses' => 'topc_ctl_member_proofing@ajaxCloseTrade' ]);
        route::post('member-tradeClose.html', [ 'uses' => 'topc_ctl_member_proofing@closeTrade' ]);
        route::get('member-proofingGoDelivery.html', [ 'uses' => 'topc_ctl_member_proofing@goDelivery' ]);
        route::post('member-proofingDoDelivery.html', [ 'uses' => 'topc_ctl_member_proofing@doDelivery' ]);
        route::post('member-proofingSendContract.html', [ 'uses' => 'topc_ctl_member_proofing@send_contract' ]);
        route::get('member-sampleDetail.html', [ 'uses' => 'topc_ctl_proofing@detail' ]);
        route::get('member-addCategory.html', [ 'uses' => 'topc_ctl_member_proofing@addCategory' ]);
        route::post('member-updateCategory.html', [ 'uses' => 'topc_ctl_member_proofing@updateCategory' ]);

        #会员中心售后申请
        route::match(array('GET', 'POST'), 'member-aftersales-apply.html', [ 'uses' => 'topc_ctl_member_aftersales@aftersalesApply' ]);
        route::post('member-aftersales-commit.html', ['uses' => 'topc_ctl_member_aftersales@commitAftersalesApply' ]);
        route::get('member-aftersales-list.html', [ 'uses' => 'topc_ctl_member_aftersales@aftersalesList' ]);
        route::get('member-aftersales-detail.html', [ 'uses' => 'topc_ctl_member_aftersales@aftersalesDetail' ]);
        route::get('member-aftersales-godetail.html', [ 'uses' => 'topc_ctl_member_aftersales@goAftersalesDetail' ]);
        route::post('member-aftersales-sendback.html', [ 'uses' => 'topc_ctl_member_aftersales@sendback' ]);
        route::get('trade-aftersales-logistics.html', [ 'uses' => 'topc_ctl_member_aftersales@ajaxLogistics' ]);

        /*custom zmq 2016-7-8  查看订单中每个商品的生产进度*/
        route::match(array('POST', 'GET'), 'progress.html', [ 'uses' => 'topc_ctl_member_trade@getProgress']);
        /*custom zmq 2016-8-16  订单延长付款申请*/
        route::match(array('POST', 'GET'), 'longpay.html', [ 'uses' => 'topc_ctl_member_trade@longPay']);
        route::match(array('POST', 'GET'), 'savelongpay.html', [ 'uses' => 'topc_ctl_member_trade@saveLongpay']);

        #订单投诉
        route::get('complaints-view.html', [ 'uses' => 'topc_ctl_member_complaints@complaintsView']);
        route::post('complaints-ci.html', [ 'uses' => 'topc_ctl_member_complaints@complaintsCi']);
        route::get('complaints-detail.html', [ 'uses' => 'topc_ctl_member_complaints@detail']);
        route::get('complaints-list.html', [ 'uses' => 'topc_ctl_member_complaints@complaintsList']);
        route::post('complaints-close.html', [ 'uses' => 'topc_ctl_member_complaints@closeComplaints']);

        #会员中心评价
        route::get('member-rate-add.html',  [ 'uses' => 'topc_ctl_member_rate@createRate' ]);
        route::post('member-rate-add.html', [ 'uses' => 'topc_ctl_member_rate@doCreateRate' ]);
        route::get('member-rate-index.html', [ 'uses' => 'topc_ctl_member_rate@index' ]);
        route::get('member-rate-list.html', [ 'uses' => 'topc_ctl_member_rate@ratelist' ]);
        route::get('member-rate-setAnony.html', [ 'uses' => 'topc_ctl_member_rate@setAnony' ]);
        route::get('member-rate-del.html',  [ 'uses' => 'topc_ctl_member_rate@doDelete' ]);
        route::post('member-rate-update.html', [ 'uses' => 'topc_ctl_member_rate@update' ]);
        route::get('member-rate-edit.html', [ 'uses' => 'topc_ctl_member_rate@edit' ]);
        route::get('member-rate-append.html', [ 'uses' => 'topc_ctl_member_rate@showAppendRateView' ]);
        route::post('member-rate-append.html', [ 'uses' => 'topc_ctl_member_rate@appendRate' ]);

        #会员中心咨询
        route::get('member-gack-index.html', [ 'uses' => 'topc_ctl_member_consultation@index' ]);
        route::post('member-gack-del.html', [ 'uses' => 'topc_ctl_member_consultation@doDelete' ]);

        # 会员中心成长值及极分
        route::get('member-myexp.html', [ 'uses' => 'topc_ctl_member_experience@experience' ]);
        route::get('member-mypoint.html', [ 'uses' => 'topc_ctl_member_point@point' ]);
        route::post('member-getpoint.html', [ 'uses' => 'topc_ctl_member_point@ajaxGetUserPoint' ]);
        route::get('member-mygrade.html', [ 'uses' => 'topc_ctl_member@grade' ]);

        //会员中心红包使用
        route::get('member-hongbao.html', [ 'uses' => 'topc_ctl_member_hongbao@index' ]);
        route::get('member-get.html', [ 'uses' => 'topc_ctl_member_hongbao@getHongbao' ]);

        # 会员中心我的订单
        route::get('trade-list.html', [ 'uses' => 'topc_ctl_member_trade@tradeList' ]);
        route::post('logi.html', [ 'uses' => 'topc_ctl_member_trade@ajaxGetTrack' ]);
        route::post('hint.html', [ 'uses' => 'topc_ctl_member_trade@ajaxHint' ]);
        route::get('canceled-trade-list.html', [ 'uses' => 'topc_ctl_member_trade@canceledTradeList' ]);

        # 会员中心订单详情
        route::post('trade-detail.html', [ 'uses' => 'topc_ctl_member_trade@confirmEleContract' ]);
        route::get('trade-detail.html', [ 'uses' => 'topc_ctl_member_trade@tradeDetail' ]);
        route::get('trade-cancel.html', [ 'uses' => 'topc_ctl_member_trade@ajaxCancelTrade' ]);
        route::get('trade-confirm.html', [ 'uses' => 'topc_ctl_member_trade@ajaxConfirmTrade' ]);
        route::post('trade-reject.html', [ 'uses' => 'topc_ctl_member_trade@rejectEleContract' ]);
        route::match(array('GET', 'POST'), 'confirm-buyer.html', ['uses' => 'topc_ctl_member_trade@confirmReceipt']);
        route::match(array('GET', 'POST'), 'cancel-buyer.html', ['uses' => 'topc_ctl_member_trade@cancelOrderBuyer']);
        route::get('choose-pay.html', [ 'uses' => 'topc_ctl_member_trade@choosePay' ]);

        route::match(array('GET', 'POST'), 'member-deposit-modifyPassword.html', ['uses' => 'topc_ctl_member_deposit@modifyPassword']);
        route::match(array('GET', 'POST'), 'member-deposit-modifyPasswordCheckLoginPassword.html', ['uses' => 'topc_ctl_member_deposit@modifyPasswordCheckLoginPassword']);
        route::match(array('GET', 'POST'), 'member-deposit-doModifyPassword.html', ['uses' => 'topc_ctl_member_deposit@doModifyPassword']);
        route::match(array('GET', 'POST'), 'member-deposit-doModifyPasswordCheckLoginPassword.html', ['uses' => 'topc_ctl_member_deposit@doModifyPasswordCheckLoginPassword']);
        route::match(array('GET', 'POST'), 'member-deposit-forgetPassword.html', ['uses' => 'topc_ctl_member_deposit@forgetPassword']);
        route::match(array('GET', 'POST'), 'member-deposit-forgetPasswordSetPassword.html', ['uses' => 'topc_ctl_member_deposit@forgetPasswordSetPassword']);
        route::match(array('GET', 'POST'), 'member-deposit-forgetPasswordFinished.html', ['uses' => 'topc_ctl_member_deposit@forgetPasswordFinished']);
        route::match(array('GET', 'POST'), 'member-deposit-forgetPasswordSendVcode.html', ['uses' => 'topc_ctl_member_deposit@forgetPasswordSendVcode']);
        route::get('canceled-trade-detail.html', [ 'uses' => 'topc_ctl_member_trade@canceledTradeDetail' ]);
        route::post('trade-saveInvoice.html', [ 'uses' => 'topc_ctl_member_trade@saveInvoice' ]);

		//子账号管理
        route::get('member-subaccount.html', [ 'uses' => 'topc_ctl_subaccount@subAccountList']);
        route::match(array('GET', 'POST'), 'subaccount-edit.html', ['uses' => 'topc_ctl_subaccount@subAccountEdit']);
        route::match(array('GET', 'POST'), 'subaccount-save.html', ['uses' => 'topc_ctl_subaccount@subAccountSave']);
        route::match(array('GET', 'POST'), 'subaccount-update.html', ['uses' => 'topc_ctl_subaccount@subAccountUpdate']);
        route::match(array('GET', 'POST'), 'subaccount-modpassword.html', ['uses' => 'topc_ctl_subaccount@subAccountPassword']);
        route::match(array('GET', 'POST'), 'subaccount-savepassword.html', ['uses' => 'topc_ctl_subaccount@subAccountSavePwd']);
        route::get('member-subaccount-actionlog.html', [ 'uses' => 'topc_ctl_subaccount@showActionLog']);

		#会员中心资金账户管理、充值、提现
		/* 见证宝 start */
        route::get('pay-vault.html', [ 'uses' => 'topc_ctl_pay_vault@index' ]);
        route::post('pay-recharge.html', [ 'uses' => 'topc_ctl_pay_vault@recharge' ]);
        route::get('pay-rechargedetail.html', [ 'uses' => 'topc_ctl_pay_vault@rechargedetail' ]);
        route::post('pay-withdrawals.html', [ 'uses' => 'topc_ctl_pay_vault@withdrawals' ]);
        route::get('pay-withdrawals.html', [ 'uses' => 'topc_ctl_pay_vault@withdrawals' ]);
        route::match(array('GET', 'POST'),'pay-withdrawalsResult.html', [ 'uses' => 'topc_ctl_pay_vault@withdrawalsResult' ]);
        route::get('pay-withdrawalsdetail.html', [ 'uses' => 'topc_ctl_pay_vault@withdrawalsdetail' ]);
        # 会员中心绑定提现账户
		route::get('member-bindbankcardlist.html', [ 'uses' => 'topc_ctl_pay_bank@bank_list']);
        route::get('member-bindbankcard.html', [ 'uses' => 'topc_ctl_pay_bank@bindBankCard']);
		#会员中心支付密码管理
        route::get('pay-password.html', [ 'uses' => 'topc_ctl_pay_password@index' ]);
        route::get('pay-iset.html', [ 'uses' => 'topc_ctl_pay_password@iset' ]);
        # 会员中心保存账户信息
        route::post('member-savebankinfo.html', [ 'uses' => 'topc_ctl_pay_bank@saveBankInfo']);
        # 会员中心发送鉴权金额
        route::get('member-sendranamo.html', [ 'uses' => 'topc_ctl_pay_bank@sendRamount']);
        # 会员中心保存鉴权金额
        route::post('member-saveranamo.html', [ 'uses' => 'topc_ctl_pay_bank@saveRamount']);
        # 会员中心发送短信验证码
        route::get('member-sendcode.html', [ 'uses' => 'topc_ctl_pay_bank@sendcode']);
        # 会员中心绑定银行卡地区
        route::get('member-showC.html', [ 'uses' => 'topc_ctl_pay_bank@showC']);
        route::get('member-getbank.html', [ 'uses' => 'topc_ctl_pay_bank@getBankList']);
        route::post('member-getbank.html', [ 'uses' => 'topc_ctl_pay_bank@getBankList']);

        # 会员中心保存短信验证码
        route::post('member-savecode.html', [ 'uses' => 'topc_ctl_pay_bank@savecode']);
		/* 见证宝 end */
    });

    /*
    |--------------------------------------------------------------------------
    | 服务撮合
    |--------------------------------------------------------------------------
    */
    #服务撮合
    route::get('proofing-index.html', [ 'uses' => 'topc_ctl_proofing@index' ]);
    #更多服务撮合需求
    route::get('proofing-moreSample.html', [ 'uses' => 'topc_ctl_proofing@sampleList' ]);
    #更多服务商
    route::get('proofing-more.html', [ 'uses' => 'topc_ctl_proofing@providerList' ]);
    #升级为服务商
    route::get('proofing-update.html', [ 'uses' => 'topc_ctl_proofing@update' ]);
    route::post('proofing-doUpdate.html', [ 'uses' => 'topc_ctl_proofing@doUpdate' ]);
    route::post('proofing-imageUpload.html', [ 'uses' => 'topc_ctl_proofing@imageUpload' ]);
    route::post('proofing-saveDrawing.html', [ 'uses' => 'topc_ctl_proofing@saveDrawing' ]);
    #发布需求
    route::get('proofing-release.html', [ 'uses' => 'topc_ctl_proofing@release' ]);
    route::post('proofing-getSampleHtml.html', [ 'uses' => 'topc_ctl_proofing@getSampleHtml' ]);
    route::post('proofing-doRelease.html', [ 'uses' => 'topc_ctl_proofing@doRelease' ]);
    /*
    |--------------------------------------------------------------------------
    | 交易
    |--------------------------------------------------------------------------
    */

    // 购物车
    route::get('cart.html', [ 'uses' => 'topc_ctl_cart@index' ]);
    route::post('cart.html', [ 'uses' => 'topc_ctl_cart@ajaxBasicCart' ]); #购物车页
    route::post('cart-add.html', [ 'uses' => 'topc_ctl_cart@add' ]); #加入购物车
    route::post('cart-update.html', [ 'uses' => 'topc_ctl_cart@updateCart' ]); #更新购物车
    route::post('cart-remove.html', [ 'uses' => 'topc_ctl_cart@removeCart' ]); #删除
    route::group(array('middleware' => 'topc_middleware_authenticate'), function() {
        #显示购物车
        route::post('trade-create.html', [ 'uses' => 'topc_ctl_trade@create' ]); #生成订单
        route::post('cart-total.html', [ 'uses' => 'topc_ctl_cart@total' ]); #统计总金额

        route::get('trade-proofingCreate.html', [ 'uses' => 'topc_ctl_trade@proofingCreate' ]); #生成服务撮合订单

        // 订单确认页
        route::get('cart-checkout.html', [ 'uses' => 'topc_ctl_cart@checkout' ]); #立即购买
        route::post('cart-checkout.html', [ 'uses' => 'topc_ctl_cart@checkout' ]); #购物信息结算页
        route::post('cart-saveAddress.html', [ 'uses' => 'topc_ctl_cart@saveAddress' ]); #购物信息结算页
        route::post('cart-addrDialog.html', [ 'uses' => 'topc_ctl_cart@addr_dialog' ]); #收货地址弹框
        route::post('cart-getCoupons.html', [ 'uses' => 'topc_ctl_cart@getCoupons' ]); #获取用户的优惠券
        route::post('cart-useCoupon.html', [ 'uses' => 'topc_ctl_cart@useCoupon' ]); #使用优惠券
        route::post('cart-cancelCoupon.html', [ 'uses' => 'topc_ctl_cart@cancelCoupon' ]); #取消优惠券
        route::post('cart-getDtyList.html', [ 'uses' => 'topc_ctl_cart@getDtyList' ]); #获取指定店铺的配送方式列表

        //获取自提列表
        route::post('trade-ziti.html', [ 'uses' => 'topc_ctl_cart@getZitiList' ]); #生成订单
    });

    /*
    |--------------------------------------------------------------------------
    | 支付中心
    |--------------------------------------------------------------------------
    */
    route::group(array('middleware' => 'topc_middleware_authenticate'), function() {
        #支付中心
        route::get('payment.html', [ 'uses' => 'topc_ctl_paycenter@index' ]);
        route::get('errorpay.html', [ 'uses' => 'topc_ctl_paycenter@errorPay' ]);
        route::match(array('GET', 'POST'), 'create.html', ['uses' => 'topc_ctl_paycenter@createPay']);
        route::post('dopayment.html', ['uses' => 'topc_ctl_paycenter@dopayment' ]);

        route::match(array('GET', 'POST'),'finish.html', [ 'uses' => 'topc_ctl_paycenter@finish' ]);
    });
 });


route::group(array('prefix' => 'pckkwap', 'middleware' => 'theme_middleware_preview'), function() {

    route::get('/', [ 'as' => 'topwap', 'uses' => 'topwap_ctl_default@index']);

    route::get('app.html', [ 'as' => 'topwap.app.index', 'uses' => 'topwap_ctl_app@index']);
    route::get('wx/app.html', [ 'as' => 'topwap.app.wx.boot', 'uses' => 'topwap_ctl_app@wxDownloadBoot']);

    route::get('configContent.html', ['as'=>'topwap.configContent', 'uses'=>'topwap_ctl_util@configContent']);

    //店铺模块相关
    route::group(array('middleware' => 'topwap_middleware_checkShop'), function() {
        route::get('shop-index.html', [ 'as' => 'shop.index', 'uses' => 'topwap_ctl_shop@index' ]);
        route::get('shop-info.html', [ 'as' => 'shop.info', 'uses' => 'topwap_ctl_shop@shopInfo' ]);
        route::get('shop-coupon.html', [ 'as' => 'shop.coupon', 'uses' => 'topwap_ctl_shop_coupon@index' ]);
        route::post('receive-coupon.html', [ 'as' => 'receive.coupon', 'uses' => 'topwap_ctl_shop_coupon@receiveConpon' ]);
        route::match(['GET','POST'], 'shop-list-index.html', [ 'as' => 'shop.list.index', 'uses' => 'topwap_ctl_shop_list@index' ]);
        route::match(['GET','POST'], 'shop-list-ajax.html', [ 'as' => 'shop.list.ajax', 'uses' => 'topwap_ctl_shop_list@ajaxGetItemList' ]);
    });

    //会员模块相关
    route::group(array('middleware' => 'topwap_middleware_authenticate'), function() {
        route::get('member.html', ['uses' => 'topwap_ctl_member@index' ]);
        route::get('member-setting.html', [ 'uses' => 'topwap_ctl_member@setting' ]);
        route::get('member-detail.html', [ 'uses' => 'topwap_ctl_member@detail' ]);

        #会员签到
        route::post('member-checkin.html', [ 'uses' => 'topwap_ctl_member@checkin' ]);

        //会员基本信息设置
        route::get('set-name.html', [ 'uses' => 'topwap_ctl_member@goSetName' ]);
        route::get('set-username.html', [ 'uses' => 'topwap_ctl_member@goSetUsername' ]);
        route::get('set-sex.html', [  'uses' => 'topwap_ctl_member@goSetSex' ]);
        route::get('set-birthday.html', ['uses' => 'topwap_ctl_member@goSetBirthday' ]);
        route::get('set-loginaccount.html', [ 'uses' => 'topwap_ctl_member@goSetLoginAccount']);

        //会员基本信息保存
        route::post('save-userinfo.html', ['uses' => 'topwap_ctl_member@saveUserInfo' ]);
        route::post('save-loginaccount.html', ['uses' => 'topwap_ctl_member@saveLoginAccount' ]);

        //会员收货地址
        route::get('addr-list.html', [ 'as' => 'member.addr.list', 'uses' => 'topwap_ctl_member_address@addrList' ]);
        route::get('addr-add.html', [ 'as' => 'member.addr.add', 'uses' => 'topwap_ctl_member_address@newAddress' ]);
        route::post('addr-save.html', [ 'as' => 'member.addr.save', 'uses' => 'topwap_ctl_member_address@saveAddress' ]);
        route::get('addr-update.html', [ 'as' => 'member.addr.update', 'uses' => 'topwap_ctl_member_address@updateAddr' ]);
        route::post('addr-setDefault.html', [ 'as' => 'member.addr.set.default', 'uses' => 'topwap_ctl_member_address@setDefault' ]);
        route::post('addr-remove.html', [ 'as' => 'member.addr.remove', 'uses' => 'topwap_ctl_member_address@removeAddr' ]);

        route::get('trade-list.html', [ 'uses' => 'topwap_ctl_member_trade@tradeList' ]);
        route::get('trade-ajaxlist.html', [ 'uses' => 'topwap_ctl_member_trade@ajaxTradeList' ]);
        route::get('trade-detail.html', [ 'uses' => 'topwap_ctl_member_trade@detail' ]);
        route::get('trade-logistics.html', [ 'uses' => 'topwap_ctl_member_trade@logistics' ]);
        route::match(array('GET', 'POST'), 'confirm-buyer.html', ['uses' => 'topwap_ctl_member_trade@confirmReceipt']);

        // cancel
        route::get('cancel.html', [ 'uses' => 'topwap_ctl_member_trade@cancel' ]);
        route::get('canceled.html', [ 'uses' => 'topwap_ctl_member_trade@canceledTradeList' ]);
        route::post('ajaxcancelelecontract.html', [ 'uses' => 'topwap_ctl_member_trade@ajaxCancelEleContract' ]);
        route::post('ajaxconfirmelecontract.html', [ 'uses' => 'topwap_ctl_member_trade@ajaxConfirmEleContract' ]);
        route::get('ajaxcanceled.html', [ 'uses' => 'topwap_ctl_member_trade@ajaxCanceledTradeList' ]);
        route::get('canceled_detail.html', [ 'uses' => 'topwap_ctl_member_trade@canceledTradeDetail' ]);
        route::get('goto_canceled_detail.html', [ 'uses' => 'topwap_ctl_member_trade@gotoCanceledTradeDetail' ]);
        route::match(array('GET', 'POST'), 'cancel-buyer.html', ['uses' => 'topwap_ctl_member_trade@cancelBuyer']);
        // 会员中心评价
        route::get('member-rate-add.html',  [ 'uses' => 'topwap_ctl_member_rate@createRate' ]);
        route::post('member-rate-add.html',  [ 'uses' => 'topwap_ctl_member_rate@doCreateRate' ]);
        route::get('member-rate-index.html',  [ 'uses' => 'topwap_ctl_member_rate@index' ]);
        route::get('member-rate-list.html',  [ 'uses' => 'topwap_ctl_member_rate@ratelist' ]);

        // 会员收藏
        route::get('member-collectitems.html', [ 'uses' => 'topwap_ctl_member_favorite@index' ]);
        route::get('member-ajaxcollectitems.html', [ 'uses' => 'topwap_ctl_member_favorite@ajaxitems' ]);
        route::get('member-ajaxcollectshops.html', [ 'uses' => 'topwap_ctl_member_favorite@ajaxshops' ]);

        route::post('collect-item.html', [ 'uses' => 'topwap_ctl_member_favorite@ajaxAddItemCollect' ]);#收藏商品
        route::post('collect-shop.html', [ 'uses' => 'topwap_ctl_member_favorite@ajaxAddShopCollect' ]);#收藏店铺
        # 店铺收藏删除,商品收藏删除
        route::post('collect-item-del.html', [ 'uses' => 'topwap_ctl_member_favorite@ajaxDelItemCollect' ]);
        route::post('collect-shop-del.html', [ 'uses' => 'topwap_ctl_member_favorite@ajaxDelShopCollect' ]);

        // 会员优惠券
        route::get('member-couponList.html', [ 'uses' => 'topwap_ctl_member_coupon@index' ]);
        route::get('member-ajaxcouponList.html', [ 'uses' => 'topwap_ctl_member_coupon@ajaxCouponList' ]);

        // 会员红包
        route::get('memeber-hongbaoList.html', [ 'uses' => 'topwap_ctl_member_hongbao@index' ]);
        route::post('memeber-ajaxhongbaoList.html', [ 'uses' => 'topwap_ctl_member_hongbao@ajaxHongbaoList' ]);

        // 会员中心售后申请
        route::get('member-aftersales-list.html', [ 'uses' => 'topwap_ctl_member_aftersales@aftersalesList' ]);
        route::get('ajax-member-aftersales-list.html', [ 'uses' => 'topwap_ctl_member_aftersales@ajaxAftersalesList' ]);
        route::get('member-aftersales-detail.html', [ 'uses' => 'topwap_ctl_member_aftersales@aftersalesDetail' ]);
        route::match(['POST', 'GET'], 'member-create-aftersales-logistics.html',[ 'uses' => 'topwap_ctl_member_aftersales@createAfterlogistics' ]);
        route::post('member-add-aftersales-logistics.html',[ 'uses' => 'topwap_ctl_member_aftersales@ajaxcreateAfterlogistics' ]);

        route::post('member-aftersales-sendback.html',[ 'uses' => 'topwap_ctl_member_aftersales@sendback' ]);
        route::post('member-aftersales-commit-apply.html',[ 'uses' => 'topwap_ctl_member_aftersales@commitAftersalesApply' ]);
        route::get('member-see-aftersales-logistics.html', [ 'uses' => 'topwap_ctl_member_aftersales@seeAfterlogistics' ]);
        route::get('member-aftersales-apply.html', [ 'uses' => 'topwap_ctl_member_aftersales@aftersalesApply' ]);
        route::get('member-aftersales-godetail.html', [ 'uses' => 'topwap_ctl_member_aftersales@goAftersalesDetail' ]);
        // 会员积分成长值
        route::get('mypoint.html', [ 'uses' => 'topwap_ctl_member_point@point' ]);
        route::get('ajax-mypoint.html', [ 'uses' => 'topwap_ctl_member_point@ajaxPonint' ]);
        // 会员中心安全中心
        route::get('member-security.html', [ 'uses' => 'topwap_ctl_member@security' ]);
        route::get('member-modifypwd.html', [ 'uses' => 'topwap_ctl_member_safe@setUserPwd' ]);# 会员中心安全中心密码修改
        route::post('member-savemodifypwd.html', [ 'uses' => 'topwap_ctl_member_safe@saveModifyPwd' ]);# 会员中心安全中心密码修改

        route::get('member-setuserinfo.html', [ 'uses' => 'topwap_ctl_member_safe@verify' ]); # 会员中心手机/邮箱绑定
        route::post('member-checkUserLogin.html', [ 'uses' => 'topwap_ctl_member_safe@CheckSetInfo' ]);# 会员中心登录检测
        route::get('member-setinfomobile.html', [ 'uses' => 'topwap_ctl_member_safe@setUserMobile' ]);# 会员中心手机
        route::match(['POST', 'GET'], 'member-sendVcode-mobile.html',[ 'uses' => 'topwap_ctl_member_safe@dosetmobile' ]);
        route::post('member-safe-sendVcode.html', [ 'uses' => 'topwap_ctl_member_safe@sendVcode' ]);# 会员中心短信验证码发送
        route::get('member-safe-viewmobile.html', [ 'uses' => 'topwap_ctl_member_safe@viewSetmobile' ]);# 会员中心短信验证码发送
        route::post('member-save-mobile.html', [ 'uses' => 'topwap_ctl_member_safe@saveMobile' ]); # 会员中心个人信息最后保存
        // 解绑手机
        route::get('member-safe-update-mobile.html', [ 'uses' => 'topwap_ctl_member_safe@viewUserMobile' ]);
        route::get('member-safe-unbind-mobile.html', [ 'uses' => 'topwap_ctl_member_safe@unbindMobile' ]);
        route::post('member-safe-unbind-mobile.html', [ 'uses' => 'topwap_ctl_member_safe@doUnbindMobile' ]);

        //会员中心投诉相关内容
        route::get('complaints-list.html', [ 'uses' => 'topwap_ctl_member_complaints@complaintsList']);
        route::get('complaints-view.html', [ 'uses' => 'topwap_ctl_member_complaints@complaintsView']);
        route::get('complaints-form.html', [ 'uses' => 'topwap_ctl_member_complaints@complaintsShopFormView']);
        route::post('complaints-post.html', [ 'uses' => 'topwap_ctl_member_complaints@complaintsPostData']);
        route::post('complaints-close.html', [ 'uses' => 'topwap_ctl_member_complaints@closeComplaints']);
        route::get('complaints-close.html', [ 'uses' => 'topwap_ctl_member_complaints@complaintsCloseFormView']);

        // 预存款密码相关
        route::get('member-deposit-passwd.html',[ 'uses' => 'topwap_ctl_member_deposit@depositPwd' ]);
        route::get('member-deposit-check-login-passwd.html',[ 'uses' => 'topwap_ctl_member_deposit@checkLoginpwd' ]);
        route::post('member-deposit-docheck-login-passwd.html',[ 'uses' => 'topwap_ctl_member_deposit@doCheckLoginPwd' ]);
        route::get('member-deposit-check-oldpay-passwd.html',[ 'uses' => 'topwap_ctl_member_deposit@checkOldpayPwd' ]);
        route::post('member-deposit-docheck-oldpay-passwd.html',[ 'uses' => 'topwap_ctl_member_deposit@doCheckOldpayPwd' ]);
        route::match(array('GET', 'POST'), 'member-deposit-modifyPassword.html', ['uses' => 'topwap_ctl_member_deposit@modifyPassword']);
        route::match(array('GET', 'POST'), 'member-deposit-doModifyPassword.html', ['uses' => 'topwap_ctl_member_deposit@doModifyPassword']);
        route::match(array('GET', 'POST'), 'member-deposit-doModifyPasswordCheckLoginPassword.html', ['uses' => 'topwap_ctl_member_deposit@doModifyPasswordCheckLoginPassword']);
        // find password
        route::match(array('GET', 'POST'), 'member-deposit-forgetPassword.html', ['uses' => 'topwap_ctl_member_deposit@forgetPassword']);
        route::match(array('GET', 'POST'), 'member-deposit-forgetPasswordSetPassword.html', ['uses' => 'topwap_ctl_member_deposit@forgetPasswordSetPassword']);
        route::match(array('GET', 'POST'), 'member-deposit-forgetPasswordSendVcode.html', ['uses' => 'topwap_ctl_member_deposit@forgetPasswordSendVcode']);

    });

    // 促销商品列表页
    route::get('promotion-item.html', [ 'as' => 'promotion.item', 'uses' => 'topwap_ctl_promotion@getPromotionItem' ]);
    // 促销专题页
    route::get('promotion-page/{page_id}.html', [ 'uses' => 'topwap_ctl_promotion@ProjectPage' ]);
    // ajax获取促销关联的商品列表
    route::get('ajax-promotion-item.html', [ 'as' => 'ajax-promotion.item', 'uses' => 'topwap_ctl_promotion@ajaxGetPromotionItem' ]);
    // 优惠券关联商品列表页
    route::get('promotion-coupon-item.html', [ 'uses' => 'topwap_ctl_promotion@getCouponItem' ]);

    route::get('item-detail.html', [ 'as' => 'item.detail', 'uses' => 'topwap_ctl_item_detail@index' ]);
    route::get('item-itempic.html', [ 'uses' => 'topwap_ctl_item_detail@itemPic' ]);
    route::match(['GET','POST'],'item-list.html', [ 'as' => 'item.list', 'uses' => 'topwap_ctl_item_list@index' ]);
    route::match(['GET','POST'],'item-list-ajax.html', [ 'as' => 'item.ajax.list', 'uses' => 'topwap_ctl_item_list@ajaxGetItemList' ]);
    //微信自定义分享
    route::get('wxshare.html', [ 'uses' => 'topwap_ctl_jssdk@index' ]);

    // 商品详情页，评价列表
    route::get('item-rate.html', [ 'uses' => 'topwap_ctl_item_detail@getItemRate' ]);
    route::get('item-rate-list.html', [ 'uses' => 'topwap_ctl_item_detail@getItemRateList' ]);
    // 商品详情页,到货通知
    route::get('notify-item.html', [ 'uses' => 'topwap_ctl_item_detail@viewNotifyItem' ]);
    route::post('user-item.html', [ 'uses' => 'topwap_ctl_item_detail@userNotifyItem' ]);

    //登录相关
    route::get('passport-gologin.html', [ 'as' => 'go.login', 'uses' => 'topwap_ctl_passport@goLogin' ]);
    route::post('passport-dologin.html', [ 'as' => 'do.login', 'uses' => 'topwap_ctl_passport@doLogin' ]);

    //注册相关
    route::match(array('GET', 'POST'),'passport-goregister.html', [ 'as' => 'go.register', 'uses' => 'topwap_ctl_passport@goRegister' ]);
    route::post('passport-checkuname.html', [ 'as' => 'checkuname', 'uses' => 'topwap_ctl_passport@checkUname' ]);
    route::post('passport-doregister.html', [ 'as' => 'do.register', 'uses' => 'topwap_ctl_passport@doRegister' ]);
    route::get('passport-register-succ.html', [ 'as' => 'register.succ', 'uses' => 'topwap_ctl_passport@registerSucc' ]);
    route::get('passport-register-license.html', [ 'as' => 'register.license', 'uses' => 'topwap_ctl_passport@registerLicense' ]);

    //会员退出
    route::get('passport-logout.html', [ 'as' => 'logout', 'uses' => 'topwap_ctl_passport@logout' ]);

    //找回密码相关
    //去找密码
    route::get('passport-gofindpwd.html', [ 'as' => 'gofindpwd', 'uses' => 'topwap_ctl_passport@goFindPwd' ]);
    //验证用户名
    route::post('passport-verify-uname.html', [ 'as' => 'verify.uname', 'uses' => 'topwap_ctl_passport@verifyUsername']);
    route::post('passport-send_vcode.html', [ 'as' => 'send.vcode', 'uses' => 'topwap_ctl_passport@sendVcode']);
    //验证手机验证码
    route::post('passport-verify-vcode.html', [ 'as' => 'verify.uname', 'uses' => 'topwap_ctl_passport@verifyVcode']);
    //设置新密码
    route::post('passport-setting-pwd.html', [ 'as' => 'setting-pwd', 'uses' => 'topwap_ctl_passport@settingPwd']);

    route::get('trustlogin-bind.html', [ 'uses' => 'topwap_ctl_trustlogin@callback' ]);
    route::post('trustlogin-exists.html', [ 'uses' => 'topwap_ctl_trustlogin@bindExistsUser' ]);
    route::post('trustlogin-signup.html', [ 'uses' => 'topwap_ctl_trustlogin@bindSignupUser' ]);
    // 会员订单

    // 支付中心
    route::get('select-hongbao.html', [ 'uses' => 'topwap_ctl_paycenter@selectHongbao' ]);
    route::post('save-hongbao.html', [ 'uses' => 'topwap_ctl_paycenter@saveHongbao' ]);
    route::get('payment.html', [ 'uses' => 'topwap_ctl_paycenter@index' ]);
    route::match(array('GET', 'POST'), 'create.html', ['uses' => 'topwap_ctl_paycenter@createPay']);
    route::post('do-payment.html', [ 'uses' => 'topwap_ctl_paycenter@dopayment' ]);
    route::get('finish.html', [ 'uses' => 'topwap_ctl_paycenter@finish' ]);


    // 微信的数据做转发
    route::match(array('GET', 'POST', 'PUT', 'DELETE'), 'wxpayjsapi.html', ['uses' => 'topwap_ctl_wechat@wxpayjsapi']);

    //平台类目列表
    route::get('category.html',['uses'=>'topwap_ctl_category@index']);

    //购物车首页
    route::get('cart.html',['uses'=>'topwap_ctl_cart@index']);
    route::post('cart-add.html', [ 'uses' => 'topwap_ctl_cart@addCart' ]); #加入购物车



    route::post('cart-update.html', [ 'uses' => 'topwap_ctl_cart@updateCart' ]); #更新购物车
    route::post('cart-remove.html', [ 'uses' => 'topwap_ctl_cart@removeCart' ]); #更新购物车
    route::post('cart-ajaxbasecart.html', [ 'uses' => 'topwap_ctl_cart@ajaxBasicCart' ]); #更新购物车

    route::post('cart-ajaxItemgetpromotion.html', [ 'uses' => 'topwap_ctl_cart@ajaxGetItemPromotion' ]); #购物车请求商品促销

    //会员模块相关
    route::group(array('middleware' => 'topwap_middleware_authenticate'), function() {

        route::match(array('GET','POST'),'cart-checkout.html',['uses' => 'topwap_ctl_cart_checkout@index']);  #立即购买
        route::get('cart-addrlist.html', [ 'uses' => 'topwap_ctl_cart_checkout@addrList' ]); #结算页获取收货地址列表
        route::post('cart-delivery.html', [ 'uses' => 'topwap_ctl_cart_checkout@deliveryList' ]); #结算页支付方式和配送方式列表
        route::get('trade-ziti.html', [ 'uses' => 'topwap_ctl_cart_checkout@getZitiList' ]); #生成订单

        route::get('cart-get-coupon.html', [ 'uses' => 'topwap_ctl_cart_checkout@getCouponList' ]); #结算页获取收货地址列表
        route::post('cart-total.html', [ 'uses' => 'topwap_ctl_cart_checkout@total' ]); #结算页获取收货地址列表
        route::post('cart-user-coupon.html', [ 'uses' => 'topwap_ctl_cart_checkout@useCoupon' ]); #结算页获取收货地址列表
        route::post('cart-cancel-coupon.html', [ 'uses' => 'topwap_ctl_cart_checkout@cancelCoupon' ]); #结算页获取收货地址列表
        route::post('cart-get-userpoint.html', [ 'uses' => 'topwap_ctl_cart_checkout@ajaxGetUserPoint' ]); #购物车获取会员积分

        route::post('trade-create.html', [ 'uses' => 'topwap_ctl_trade@create' ]); #结算页创建订单
    });

    /*
    |--------------------------------------------------------------------------
    | 文章相关
    |--------------------------------------------------------------------------
    */
    route::group(array(), function() {

        route::get('content-node-index.html', [ 'uses' => 'topwap_ctl_content@index' ]);
        route::get('content-node-child-list.html', [ 'uses' => 'topwap_ctl_content@childNodeList' ]);
        route::get('content-list.html', [ 'uses' => 'topwap_ctl_content@contentList' ]);
        route::get('content-info.html', [ 'uses' => 'topwap_ctl_content@getContentInfo']);
        route::post('ajax-content-list.html', [ 'uses' => 'topwap_ctl_content@ajaxContentList']);
        route::get('shop-article.html', [ 'uses' => 'topwap_ctl_content@shopArticle' ]);

    });

    # 进行中的活动首页
    route::get('activity-index.html', [ 'as' => 'activity.list.index', 'uses' => 'topwap_ctl_activity@active_list' ]);
    # 即将开始的活动
    route::get('activity-comming.html', [ 'as' => 'activity.list.comming', 'uses' => 'topwap_ctl_activity@comming_list' ]);
    # 活动详情
    route::get('activity-detail.html', [ 'as' => 'activity.list.detail', 'uses' => 'topwap_ctl_activity@detail' ]);
    # 活动的商品列表
    route::get('activity-itemlist.html', [ 'as' => 'activity.list.itemlist', 'uses' => 'topwap_ctl_activity@itemlist' ]);
    # 活动的商品详情
    route::get('activity-itemdetail.html', [ 'as' => 'activity.list.itemdetail', 'uses' => 'topwap_ctl_activity@itemdetail' ]);
    # 活动开售提醒
    route::get('activity-remind.html', [ 'as' => 'activity.list.remind', 'uses' => 'topwap_ctl_activity@remind' ]);
    # 保存订阅活动开售提醒信息
    route::post('activity-saveRemind.html', [ 'as' => 'activity.list.saveRemind', 'uses' => 'topwap_ctl_activity@saveRemind' ]);


});
/*
|--------------------------------------------------------------------------
| 商家管理中心
|--------------------------------------------------------------------------
*/

//商家入驻
route::group(array('prefix' => 'supplier/register'), function() {
    //注册检查手机号
    route::get('signCheckPhone.html',  [ 'as' => 'topshop.register.signCheckPhonePage',              'uses' => 'topshop_ctl_register@signCheckPhonePage' ]);
    route::post('signCheckPhone.html', [ 'as' => 'topshop.register.signCheckPhoneActioin',           'uses' => 'topshop_ctl_register@signCheckPhoneAction' ]);
    //发送注册的短信验证码
    route::post('sendSms.html',        [ 'as' => 'topshop.register.sendSms',                         'uses' => 'topshop_ctl_register@sendSms' ]);
    //注册页面
    route::get('sign.html',            [ 'as' => 'topshop.register.signPage',                        'uses' => 'topshop_ctl_register@signPage' ]);
    route::post('sign.html',           [ 'as' => 'topshop.register.signAction',                      'uses' => 'topshop_ctl_register@signAction' ]);
    route::group(['middleware'=>['topshop_middleware_permission', 'topshop_middleware_register']], function(){

        //入驻协议
        route::get('agrement.html',        [ 'as' => 'topshop.register.enterAgreementPage',              'uses' => 'topshop_ctl_register@enterAgreementPage' ]);
        //入主流程-公司基本信息
        route::get('companyInfo.html',     [ 'as' => 'topshop.register.enterProcessCompanyInfoPage',     'uses' => 'topshop_ctl_register@enterProcessCompanyInfo' ]);
        route::post('companyInfo.html',    [ 'as' => 'topshop.register.enterProcessCompanyInfoAction',   'uses' => 'topshop_ctl_register@enterProcessCompanyInfoAction' ]);
        //入主流程-银行及税务信息
        route::get('economicInfo.html',    [ 'as' => 'topshop.register.enterProcessEconomicInfoPage',    'uses' => 'topshop_ctl_register@enterProcessEconomicInfo' ]);
        route::post('economicInfo.html',   [ 'as' => 'topshop.register.enterProcessEconomicInfoAction',  'uses' => 'topshop_ctl_register@enterProcessEconomicInfoAction' ]);
        //入驻流程-店铺信息
        route::get('shopInfo.html',        [ 'as' => 'topshop.register.enterProcessShopInfoPage',        'uses' => 'topshop_ctl_register@enterProcessShopInfo' ]);
        route::post('shopInfo.html',       [ 'as' => 'topshop.register.enterProcessShopInfoAction',      'uses' => 'topshop_ctl_register@enterProcessShopInfoAction' ]);
        //入驻流程-提交完成，等待审核
        route::get('waiteExamine.html',    [ 'as' => 'topshop.register.enterProcessWaiteExaminePage',    'uses' => 'topshop_ctl_register@enterProcessWaiteExamine' ]);
        //入驻流程-审核通过，等待签约
        route::get('waiteAward.html',      [ 'as' => 'topshop.register.enterProcessWaiteAwardPage',      'uses' => 'topshop_ctl_register@enterProcessWaiteAward' ]);
        //入驻流程-审核未通过的情况下，重新修改内容
        route::get('updateApply.html',     [ 'as' => 'topshop.register.enterProcessUpdate',              'uses' => 'topshop_ctl_register@enterProcessUpdateApply' ]);

    });
});

route::group(array('prefix' => 'supplier','middleware' => 'topshop_middleware_permission'), function() {
    # 首页
    route::get('/', [ 'as' => 'topshop.home', 'uses' => 'topshop_ctl_index@index' ]);
    #上传合同文件
    route::post('trade-upload.html', ['uses' => 'topshop_ctl_upload_uploadDoc@index']);

    route::get('nopermission.html', [ 'as' => 'topshop.nopermission', 'uses' => 'topshop_ctl_index@nopermission' ]);
    route::get('onlySelfManagement.html', [ 'as' => 'topshop.onlySelfManagement', 'uses' => 'topshop_ctl_index@onlySelfManagement' ]);

    # 登录
    route::get('passport/signin-s.html', [ 'as' => 'topshop.simpleSignin', 'uses' => 'topshop_ctl_passport@simpleSignin' ]);
    route::get('passport/signin.html', [ 'as' => 'topshop.signin', 'uses' => 'topshop_ctl_passport@signin', 'middleware' => 'topshop_middleware_redirectIfAuthenticated' ]);
    route::post('passport/signin.html', [ 'as' => 'topshop.postsignin', 'uses' => 'topshop_ctl_passport@login' ]);
    # 注册 |暂时关闭 商家注册
    route::get('passport/signup.html', [ 'as' => 'topshop.signup', 'uses' => 'topshop_ctl_passport@signup', 'middleware' => 'topshop_middleware_redirectIfAuthenticated'  ]);
    route::post('passport/signup.html', [ 'as' => 'topshop.postsignup', 'uses' => 'topshop_ctl_passport@create' ]);
    # 退出
    route::get('passport/logout.html', [ 'as' => 'topshop.logout', 'uses' => 'topshop_ctl_passport@logout' ]);
    # 账户是否存在
    route::get('passport/isexists.html', [ 'as' => 'topshop.userexists', 'uses' => 'topshop_ctl_passport@isExists' ]);
    # 商家修改密码
    route::get('passport/update.html', [ 'as' => 'topshop.update', 'uses' => 'topshop_ctl_passport@update' ]);
    route::post('passport/update.html', [ 'as' => 'topshop.postupdatepwd', 'uses' => 'topshop_ctl_passport@updatepwd' ]);
    #促销管理
    #满减
    route::post('promotion/fullminusbrand.html', [ 'as' => 'topshop.promotion.fullminus', 'uses' => 'topshop_ctl_promotion_fullminus@getBrandList' ]);
    #组合促销
    route::post('promotion/packagebrand.html', [ 'as' => 'topshop.promotion.package', 'uses' => 'topshop_ctl_promotion_package@getBrandList' ]);
    #满折
    route::post('promotion/fulldiscountbrand.html', [ 'as' => 'topshop.promotion.fulldiscount', 'uses' => 'topshop_ctl_promotion_fulldiscount@getBrandList' ]);
    #优惠券
    route::post('promotion/couponbrand.html', [ 'as' => 'topshop.promotion.coupon', 'uses' => 'topshop_ctl_promotion_coupon@getBrandList' ]);
    #x件y折
    route::post('promotion/xydiscountbrand.html', [ 'as' => 'topshop.promotion.xydiscount', 'uses' => 'topshop_ctl_promotion_xydiscount@getBrandList' ]);
    # 不可报名活动详情
    route::get('activity/noregistered_detail.html', [ 'as' => 'topshop.activity.noregistered_detail', 'uses' => 'topshop_ctl_promotion_activity@noregistered_detail' ]);
    # 活动报名表单
    route::get('activity/canregistered_apply.html', [ 'as' => 'topshop.activity.canregistered_apply', 'uses' => 'topshop_ctl_promotion_activity@canregistered_apply' ]);
    route::get('activity/canregistered_detail.html', [ 'as' => 'topshop.activity.canregistered_detail', 'uses' => 'topshop_ctl_promotion_activity@canregistered_detail' ]);
    # 保存活动报名表单
    route::post('activity/canregistered_apply_save.html', [ 'as' => 'topshop.activity.canregistered_apply_save', 'uses' => 'topshop_ctl_promotion_activity@canregistered_apply_save' ]);

    route::group(array('middleware' => 'topshop_middleware_enterapply'), function() {
        # 入驻申请-ajax请求类目下的品牌
        route::match(array('GET', 'POST'),'ajax/cat/brand.html', [ 'as' => 'topshop.ajax.cat.brand', 'uses' => 'topshop_ctl_enterapply@ajaxCatBrand' ]);
    });

    # 获取自然属性页面
    route::post('natureprops.html', [ 'as' => 'toputil.syscat.nature', 'uses' => 'topshop_ctl_sku@getNatureProps' ]);
    # 获取详细参数页面
    route::post('params.html', [ 'as' => 'toputil.syscat.params', 'uses' => 'topshop_ctl_sku@getParams' ]);
    # 获取销售属性页面
    route::post('spec/props.html', [ 'as' => 'toputil.syscat.spec.props', 'uses' => 'topshop_ctl_sku@getSpecProps' ]);
    # 获取销售属性选择信息
    route::post('spec/selectprops.html', [ 'as' => 'toputil.syscat.spec.selectprops', 'uses' => 'topshop_ctl_sku@set_spec_index' ]);
    # 商家后台报表
    route::post('sysstat/sysstat.html', [ 'as' => 'topshop.sysstat.sysstat', 'uses' => 'topshop_ctl_sysstat_sysstat@ajaxTrade' ]);
    route::post('sysstat/stattrade.html', [ 'as' => 'topshop.sysstat.stattrade', 'uses' => 'topshop_ctl_sysstat_stattrade@ajaxTrade' ]);
    route::post('sysstat/sysbusiness.html', [ 'as' => 'topshop.sysstat.sysbusiness', 'uses' => 'topshop_ctl_sysstat_sysbusiness@ajaxTrade' ]);
    route::post('sysstat/itemtrade.html', [ 'as' => 'topshop.sysstat.itemtrade', 'uses' => 'topshop_ctl_sysstat_itemtrade@ajaxTrade' ]);
    route::post('sysstat/stataftersales.html', [ 'as' => 'topshop.sysstat.stataftersales', 'uses' => 'topshop_ctl_sysstat_stataftersales@ajaxTrade' ]);
    route::post('sysstat/stattraffic.html', [ 'as' => 'topshop.sysstat.stattraffic', 'uses' => 'topshop_ctl_sysstat_systraffic@ajaxTrade' ]);
    # 商家发货
    route::group(array('middleware'=>'topshop_middleware_developerMode'), function() {
        //route::get('trade/godelivery.html', [ 'as' => 'topshop.trade.godelivery', 'uses' => 'topshop_ctl_trade_flow@godelivery', 'middleware'=>['topshop_middleware_developerMode']]);
        route::post('trade/dodelivery.html', [ 'as' => 'topshop.trade.dodelivery', 'uses' => 'topshop_ctl_trade_flow@dodelivery', 'middleware'=>['topshop_middleware_developerMode']]);
        route::post('trade/updateLogistic.html', [ 'as' => 'topshop.trade.updateLogistic', 'uses' => 'topshop_ctl_trade_flow@updateLogistic', 'middleware'=>['topshop_middleware_developerMode']]);
    });

    //wap配置
    route::post('wap/searchItem.html', [ 'as' => 'topshop.wap.decorate.searchItem', 'uses' => 'topshop_ctl_wap_decorate@searchItem' ]);
    route::post('wap/getBrandList.html', [ 'as' => 'topshop.wap.decorate.getBrandList', 'uses' => 'topshop_ctl_wap_decorate@getBrandList' ]);
    #意见反馈
    route::post('feedback.html', [ 'as' => 'topshop.feedback', 'uses' => 'topshop_ctl_index@feedback' ]);

    #编辑常用菜单
    route::post('common/user/menu.html', [ 'as' => 'topshop.commonUserMenu', 'uses' => 'topshop_ctl_menu@index' ]);

    route::get('export.html', [ 'as' => 'toputil.export.view', 'uses' => 'topshop_ctl_export@view' ]);
    route::post('export.html', [ 'as' => 'toputil.export.do', 'uses' => 'topshop_ctl_export@export' ]);

    # 选择商品组件
    route::get('select-goods.html', [ 'as' => 'topshop.goods.select', 'uses' => 'topshop_ctl_selector_item@loadSelectGoodsModal' ]);
    route::post('format-selected-goods.html', [ 'as' => 'topshop.goods.selected.format', 'uses' => 'topshop_ctl_selector_item@formatSelectedGoodsRow' ]);
    route::post('select-brandList.html', [ 'as' => 'topshop.goods.brandList', 'uses' => 'topshop_ctl_selector_item@getBrandList' ]);
    route::post('select-getItem.html', [ 'as' => 'topshop.goods.getItem', 'uses' => 'topshop_ctl_selector_item@searchItem' ]);
    route::get('select-item.getsku.html', [ 'as' => 'topshop.item.goods.getsku', 'uses' => 'topshop_ctl_selector_item@getSkuByItemId' ]);
    route::get('select-showsku.html', [ 'as' => 'topshop.goods.showsku', 'uses' => 'topshop_ctl_selector_item@showSkuByitemId' ]);

    // 商家错误页
    route::get('error_404.html', ['uses' => 'topshop_ctl_error@index' ]);
    route::get('select-sku.html', [ 'as' => 'topshop.sku.select', 'uses' => 'topshop_ctl_selector_sku@loadSelectSkuModal' ]);
    route::post('format-selected-sku.html', [ 'as' => 'topshop.sku.selected.format', 'uses' => 'topshop_ctl_selector_sku@formatSelectedSkusRow' ]);
    route::post('select-getSku.html', [ 'as' => 'topshop.sku.getSku', 'uses' => 'topshop_ctl_selector_sku@searchSku' ]);

    //商家后台路由器
    $menus = config::get('shop');
    foreach($menus as $subMenus)
    {
        foreach($subMenus['menu'] as $menu)
        {
            $parameters = array($menu['url'], ['as' => $menu['as'], 'uses' => $menu['action'], 'middleware'=>$menu['middleware']]);
            if(array_key_exists('method', $menu))
            {
                $method = $menu['method'];

                if(is_array($menu['method']))
                {
                    $method = 'match';
                    $parameters = array(['GET','POST'],$menu['url'], ['as' => $menu['as'], 'uses' => $menu['action'], 'middleware'=>$menu['middleware']]);
                }
            }
            forward_static_call_array(array('route', $method), $parameters);
        }
    }
});

route::group(array('prefix' => 'temai','middleware' => 'topc_middleware_authenticate'), function() {
    # 平台展销中心
    #route::get('temai.html', [ 'uses' => 'topc_ctl_temai_trade@index' ]);
    #route::get('temai-item.html', [ 'uses' => 'topc_ctl_temai_item@index' ]);
    #route::get('temai-item-list.html', [ 'uses' => 'topc_ctl_temai_item@itemList' ]);
    #route::get('temai-trade.html', [ 'uses' => 'topc_ctl_temai_trade@tradeList' ]);
    #上传合同文件
    route::post('trade-upload.html', ['uses' => 'toptemai_ctl_upload_uploadDoc@index']);
    #编辑常用菜单
    route::post('feedback.html', [ 'as' => 'toptemai.feedback', 'uses' => 'toptemai_ctl_index@feedback' ]);
    route::post('common/user/menu.html', [ 'as' => 'toptemai.commonUserMenu', 'uses' => 'toptemai_ctl_menu@index' ]);

    # 获取自然属性选择信息
    route::post('natureprops.html', [ 'as' => 'temai_toputil.syscat.nature', 'uses' => 'toptemai_ctl_sku@getNatureProps' ]);
    route::post('params.html', [ 'as' => 'temai_toputil.syscat.params', 'uses' => 'toptemai_ctl_sku@getParams' ]);
    route::post('spec/props.html', [ 'as' => 'temai_toputil.syscat.spec.props', 'uses' => 'toptemai_ctl_sku@getSpecProps' ]);
    # 获取销售属性选择信息
    route::post('spec/selectprops.html', [ 'as' => 'temai_toputil.syscat.spec.selectprops', 'uses' => 'toptemai_ctl_sku@set_spec_index' ]);
    route::get('export.html', [ 'as' => 'toputil.export.view', 'uses' => 'toptemai_ctl_export@view' ]);
    route::post('export.html', [ 'as' => 'toputil.export.do', 'uses' => 'toptemai_ctl_export@export' ]);

    # 选择商品组件
    route::get('select-goods.html', [ 'as' => 'toptemai.goods.select', 'uses' => 'toptemai_ctl_selector_item@loadSelectGoodsModal' ]);
    route::post('format-selected-goods.html', [ 'as' => 'toptemai.goods.selected.format', 'uses' => 'toptemai_ctl_selector_item@formatSelectedGoodsRow' ]);
    route::post('select-brandList.html', [ 'as' => 'toptemai.goods.brandList', 'uses' => 'toptemai_ctl_selector_item@getBrandList' ]);
    route::post('select-getItem.html', [ 'as' => 'toptemai.goods.getItem', 'uses' => 'toptemai_ctl_selector_item@searchItem' ]);
    route::get('select-item.getsku.html', [ 'as' => 'toptemai.item.goods.getsku', 'uses' => 'toptemai_ctl_selector_item@getSkuByItemId' ]);
    route::get('select-showsku.html', [ 'as' => 'toptemai.goods.showsku', 'uses' => 'toptemai_ctl_selector_item@showSkuByitemId' ]);
    route::post('sysstat/sysstat.html', [ 'as' => 'toptemai.sysstat.sysstat', 'uses' => 'toptemai_ctl_sysstat_sysstat@ajaxTrade' ]);
    route::post('sysstat/stattrade.html', [ 'as' => 'toptemai.sysstat.stattrade', 'uses' => 'toptemai_ctl_sysstat_stattrade@ajaxTrade' ]);
    route::post('sysstat/sysbusiness.html', [ 'as' => 'toptemai.sysstat.sysbusiness', 'uses' => 'toptemai_ctl_sysstat_sysbusiness@ajaxTrade' ]);
    route::post('sysstat/itemtrade.html', [ 'as' => 'toptemai.sysstat.itemtrade', 'uses' => 'toptemai_ctl_sysstat_itemtrade@ajaxTrade' ]);
    route::post('sysstat/stataftersales.html', [ 'as' => 'toptemai.sysstat.stataftersales', 'uses' => 'toptemai_ctl_sysstat_stataftersales@ajaxTrade' ]);
    route::post('sysstat/stattraffic.html', [ 'as' => 'toptemai.sysstat.stattraffic', 'uses' => 'toptemai_ctl_sysstat_systraffic@ajaxTrade' ]);

    # 商家发货
    route::group(array('middleware'=>'toptemai_middleware_developerMode'), function() {
        //route::get('trade/godelivery.html', [ 'as' => 'topshop.trade.godelivery', 'uses' => 'topshop_ctl_trade_flow@godelivery', 'middleware'=>['topshop_middleware_developerMode']]);
        route::post('trade/dodelivery.html', [ 'as' => 'toptemai.trade.dodelivery', 'uses' => 'toptemai_ctl_trade_flow@dodelivery', 'middleware'=>['toptemai_middleware_developerMode']]);
        route::post('trade/updateLogistic.html', [ 'as' => 'toptemai.trade.updateLogistic', 'uses' => 'toptemai_ctl_trade_flow@updateLogistic', 'middleware'=>['toptemai_middleware_developerMode']]);
    });

    $menus = config::get('temai');
    foreach($menus as $subMenus)
    {
        foreach($subMenus['menu'] as $menu)
        {
            $parameters = array($menu['url'], ['as' => $menu['as'], 'uses' => $menu['action'], 'middleware'=>$menu['middleware']]);
            if(array_key_exists('method', $menu))
            {
                $method = $menu['method'];

                if(is_array($menu['method']))
                {
                    $method = 'match';
                    $parameters = array(['GET','POST'],$menu['url'], ['as' => $menu['as'], 'uses' => $menu['action'], 'middleware'=>$menu['middleware']]);
                }
            }
            forward_static_call_array(array('route', $method), $parameters);
        }
    }
});

# 忘记密码
route::group(array('prefix' => 'supplier', 'middleware' => 'topshop_middleware_redirectIfAuthenticated'), function() {
    route::get('find/index.html', [ 'as' => 'topshop.find', 'uses' => 'topshop_ctl_find@index']);
    route::get('find/firststep.html', [ 'as' => 'topshop.find', 'uses' => 'topshop_ctl_find@firstStep']);
    route::get('find/isauth.html', [ 'as' => 'topshop.findisauth', 'uses' => 'topshop_ctl_find@isAuth' ]);
    //验证
    route::post('find/checkinfo.html', ['as'=>'topshop.find.check','uses'=>'topshop_ctl_find@checkFindInfo']);
    //找回密码第二步
    route::get('find/secondstep.html', [ 'as' => 'topshop.find.second', 'uses' => 'topshop_ctl_find@secondStep']);
    // 修改密码
    route::post('find/resetpwd.html', [ 'as' => 'topshop.find.resetpwd', 'uses' => 'topshop_ctl_find@resetPassword']);
    // 发送验证码
    route::post('find/sendcode.html', ['as'=>'topshop.auth.send.code','uses'=>'topshop_ctl_find@send']);
});
/*
|--------------------------------------------------------------------------
| 店铺通用显示数据处理
|--------------------------------------------------------------------------
 */
route::group(array('prefix' => 'utils'), function() {
    # 系统分类
    route::post('syscat.html', [ 'as' => 'toputil.syscat', 'uses' => 'toputil_ctl_syscat@getChildrenCatList' ]);
    route::get('vcode.html', [ 'as' => 'toputil.vcode', 'uses' => 'toputil_ctl_vcode@gen_vcode' ]);
    route::post('util/upload_images.html', [ 'as' => 'toputil.uploadImages', 'uses' => 'toputil_ctl_image@uploadImages' ]);
    route::get('util/item_pic.html', [ 'as' => 'toputil.getDefaultItemPic', 'uses' => 'toputil_ctl_image@getItemPic' ]);
    route::post('ajax/articleList.html', [ 'as' => 'toputil.getContentNodeArticleList', 'uses' => 'toputil_ctl_themesAjax@getContentNodeArticleList' ]);
    route::post('ajax/catList.html', [ 'as' => 'toputil.catList', 'uses' => 'toputil_ctl_themesAjax@getChildrenCatList' ]);
    route::post('ajax/virtualCatList.html', [ 'as' => 'toputil.virtualCatList', 'uses' => 'toputil_ctl_themesAjax@getVirtualCatChildrenList' ]);
    route::post('trafficstat.html', [ 'as' => 'toputil.trafficStatic', 'uses' => 'toputil_ctl_trafficStatic@stat' ]);
    route::post('util/company_upload_images.html', [ 'as' => 'toputil.uploadImages', 'uses' => 'toputil_ctl_image@companyUploadImages']);
    route::post('util/toputil_file.html', [ 'as' => 'toputil.uploadFile', 'uses' => 'toputil_ctl_file@uploadFile']);
});

route::match(array('GET', 'POST', 'PUT', 'DELETE'), 'wxpayApp.html', ['uses' => 'topapi_ctl_wechat@wxpayApp']);

/*
|--------------------------------------------------------------------------
| 后台通用route
|--------------------------------------------------------------------------
 */
route::group(array('prefix' => 'platform'), function() {

    # 系统分类
    route::match(array('GET', 'POST'), '/', array('as' => 'shopadmin', 'uses' => 'desktop_router@dispatch'));
});


route::group(array('prefix' => 'dev'), function() {
    route::get('/', [ 'as' => 'topdev.index', 'uses' => 'topdev_ctl_index@index' ]);
    route::get('apis/list.html', [ 'as' => 'topdev.apis.list', 'uses' => 'topdev_ctl_apis@group']);
    route::get('apis/info.html', [ 'as' => 'topdev.apis.info', 'uses' => 'topdev_ctl_apis@info']);
    route::get('apis/test.html', [ 'as' => 'topdev.apis.test', 'uses' => 'topdev_ctl_apis@testView']);
    route::post('apis/test.html', [ 'as' => 'topdev.apis.use', 'uses' => 'topdev_ctl_apis@testApi']);
    route::get('apis/search.html', [ 'as' => 'topdev.apis.search', 'uses' => 'topdev_ctl_apis@search']);
    route::get('apis/topapi/export.html', [ 'as' => 'topdev.apis.topapi.export', 'uses' => 'topdev_ctl_apis@topapiExport']);
});



/*
|--------------------------------------------------------------------------
| setup
|--------------------------------------------------------------------------
 */
route::group(array('prefix' => 'setup'), function() {
    # 安装首页
    route::match(array('GET', 'POST'), '/', ['as' => 'setup', 'uses' => 'setup_ctl_default@index']);
    # 安装页
    route::match(array('GET', 'POST'), '/default/process', ['uses' => 'setup_ctl_default@process']);
    # 命令行安装
    route::match(array('GET', 'POST'), '/default/install_app', ['uses' => 'setup_ctl_default@install_app']);
    # console
    route::match(array('GET', 'POST'), '/default/console', ['uses' => 'setup_ctl_default@console']);
    # 激活
    route::match(array('GET', 'POST'), '/default/active', ['uses' => 'setup_ctl_default@active']);
    # 激活成功
    route::match(array('GET', 'POST'), '/default/success', ['uses' => 'setup_ctl_default@success']);
    # 环境初始化
    route::match(array('GET', 'POST'), '/default/initenv', ['uses' => 'setup_ctl_default@initenv']);
    # 女装初始化数据
    route::match(array('GET', 'POST'), '/default/install_demodata', ['uses' => 'setup_ctl_default@install_demodata']);
    route::match(array('GET', 'POST'), '/default/setuptools', ['uses' => 'setup_ctl_default@setuptools']);

});

