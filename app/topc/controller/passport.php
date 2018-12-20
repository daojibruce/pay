<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 */
class topc_ctl_passport extends topc_controller
{

    public function __construct()
    {
        parent::__construct();
        $this->setLayoutFlag('passport');
        kernel::single('base_session')->start();

        $this->passport = kernel::single('topc_passport');
    }

    //会员登录
    public function signin()
    {
        $pagedata['next_page'] = $this->__getFromUrl();
        $data = app::get('sysuser')->getConf('trustlogin_rule');

        // 获取信任登陆
        if (kernel::single('pam_trust_user')->enabled())
        {
            $trustInfoList = kernel::single('pam_trust_user')->getTrustInfoList('web', 'topc_ctl_trustlogin@callBack');
            $pagedata['trustInfoList'] = $trustInfoList;
        }

        $pagedata['isShowVcode'] = userAuth::isShowVcode('login');
        //echo '<pre>';print_r($pagedata);exit();
        return $this->page('topc/passport/signin/signin.html',$pagedata);
    }

    //会员注册主页
    public function signup()
    {
        $pagedata['next_page'] = $this->__getFromUrl();
        $pagedata['license'] = app::get('site')->getConf('site.usage_agreement');
        $pagedata['referees'] = app::get('sysuser')->model('referee')->getList('referee_id,referee_name',['status' => 1]);
        //dd($pagedata);
        return $this->page('topc/passport/signup/signup.html', $pagedata);
    }

    //执行会员登陆
    public function login()
    {
        $validator = validator::make(
            [input::get('account') , input::get('password')],
            ['required', 'required'],
            ['请输入账号!', '请输入密码!']
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                return $this->splash('error',null,$error[0]);
            }
        }
        $verifycode = input::get('verifycode');
        if( userAuth::isShowVcode('login') )
        {
            if( !input::get('key') || empty($verifycode) || !base_vcode::verify(input::get('key'), $verifycode))
            {
                $msg = app::get('topc')->_('验证码填写错误') ;
                return $this->splash('error', '', $msg, true);
            }
        }

        try
        {
            userAuth::setAttemptRemember(input::get('remember',null));

            if (userAuth::attempt(input::get('account'), input::get('password')))
            {
                $url = specialutils::filterCrlf(input::get('next_page'));
                kernel::single('topc_cart')->mergeCart();

                //商家同步登录..
                shopAuth::login(input::get('account'), input::get('password'));

                //绑定user_id 跟 shop_id
                $shop_id = shopAuth::getShopId();
                if($shop_id) kernel::single('sysuser_data_user_credit')->ShopIdBindUserId(userAuth::id(), $shop_id);

                return $this->splash('success',$url, '');
            }
        }
        catch(Exception $e)
        {
            userAuth::setAttemptNumber();
            if( userAuth::isShowVcode('login') )
            {
                $url = url::action('topc_ctl_passport@signin');
            }
            $msg = $e->getMessage();
            return $this->splash('error',$url,$msg,true);
        }
    }

    //会员注册
    public function create()
    {
        $data = utils::_filter_input(input::get());

        $codyKey = $data['key'];
        $verifycode = $data['verifycode'];
        $userInfo = $data['pam_user'];
        $vcode = $data['vcode'];
        //数据检测
        $validator = validator::make(
            ['loginAccount'=>$userInfo['account'],'license'=>input::get('license'),'password' => $userInfo['password'], 'password_confirmation' => $userInfo['pwd_confirm']],
            ['loginAccount'=>'required','license'=>'required','password' => 'min:6|max:20|confirmed','password_confirmation'=>'required'],
            ['loginAccount'=>'请输入用户名!','license'=>'请阅读并接受会员注册协议!','password' => '密码长度不能小于6位!|密码长度不能大于20位!|输入的密码不一致!','password_confirmation'=>'确认密码不能为空!']
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                return $this->splash('error',$url,$error[0],true);
            }
        }

        try
        {
			/*
            $accountType = app::get('topc')->rpcCall('user.get.account.type',array('user_name'=>$userInfo['account']),'buyer');
            kernel::single('sysuser_passport')->checkSignupAccount($userInfo['account'],$accountType);
            if($accountType == "mobile")
            {
                $vcodeData=userVcode::verify($vcode,$userInfo['account'],'signup');

                if(!$vcodeData)
                {
                    throw new \LogicException(app::get('topc')->_('手机验证码错误'));
                }
            }
            else
            {
                if( empty($verifycode) || !base_vcode::verify($codyKey,$verifycode) )
                {
                    throw new \LogicException(app::get('topc')->_('验证码填写错误'));
                }
            }
			*/

            $userId = userAuth::signUp($userInfo['account'], $userInfo['password'], $userInfo['pwd_confirm']);
            //保存姓名
            //$params = ['user_id' => $userId,'username' => $userInfo['username']];
            //app::get('sysuser')->model('user')->save($params);
            userAuth::login($userId, $userInfo['account']);

            //登陆合并离线购物车
            kernel::single('topc_cart')->mergeCart();

            //同时注册商家
            $shopData = [
                'login_account'     => $userInfo['account'],
                'login_password'    => $userInfo['password'],
                'psw_confirm'       => $userInfo['pwd_confirm'],
                'mobile'            => $userInfo['account'],
            ];
            shopAuth::signupSeller($shopData);
            //保存来源
            if ($data['referee_id'] != '0') {
                $reInfo = app::get('sysuser')->model('referee')->getRow('referee_name',['referee_id' => $data['referee_id']]);
                $refereeData = ['referee_id' => $data['referee_id'], 'user_id' => $userId, 'referee_name' => $reInfo['referee_name']];
                app::get('sysuser')->model('user')->save($refereeData);
            }
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error','',$msg,true);
        }

        // 跳成功页
        $url = url::action('topc_ctl_passport@signupSuccess', ['next_page' => $this->__getFromUrl()]);

        return $this->splash('success', $url, null, true);
    }

    //注册成功
    public function signupSuccess()
    {
        $loginName = userAuth::getLoginName();
        $userInfo = userAuth::getUserInfo();
        $pagedata['userInfo'] = $userInfo;
        $pagedata['loginname'] = $loginName;
        $pagedata['next_page'] = specialutils::filterCrlf(input::get('next_page'));
        $pagedata['sendPointNum'] = app::get('sysconf')->getConf('sendPoint.num');
        $pagedata['setting'] = app::get('sysconf')->getConf('open.sendPoint');

        return $this->page('topc/passport/signin/success.html', $pagedata);
     }

    //退出登录
    public function logout()
    {
        userAuth::logout();
        return redirect::action('topc_ctl_passport@signin')->send();
    }

    //检查是否已经注册
    public function checkLoginAccount()
    {
        $signAccount = utils::_filter_input(input::get());
        $loginName = $signAccount['pam_user']['account'];
        $validator = validator::make(
            ['account' => $loginName],['account' => 'required|mobile'],['account' => '请输入手机号!|手机号有误！']
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                return $this->splash('error',null,$error[0]);
            }
        }
        try
        {
            $data = userAuth::getAccountInfo($loginName);
            if($data)
            {
                throw new \LogicException('该用户名已被使用');
            }


            $json['needVerify'] = app::get('topc')->rpcCall('user.get.account.type',array('user_name'=>$loginName),'buyer');

            kernel::single('sysuser_passport')->checkSignupAccount($loginName, $json['needVerify']);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',$url,$msg,true);
        }
        return response::json($json);
    }

    //前端注册验证码的发送
    public function sendVcode()
    {
        $postData = utils::_filter_input(input::get());

        $validator = validator::make(
            [$postData['uname']],['required'],['您的邮箱或手机号不能为空!']
        );


        //验证码发送之前的判断
        //这里之前是判断用户post数据是否包含verifycode字段，如果不包含就跳过验证码了。这里改为判断用户使用手机注册（by Elrond at 2015.1.27）
        $accountType = app::get('topc')->rpcCall('user.get.account.type',array('user_name'=>$postData['uname']),'buyer');
        if( $accountType == 'mobile' )
        {
            $valid = validator::make(
                [$postData['verifycode']],['required']
            );
            if($valid->fails())
            {
                return $this->splash('error',null,"图片验证码不能为空!");
            }
            if(!base_vcode::verify($postData['verifycodekey'],$postData['verifycode']))
            {
                return $this->splash('error',null,"图片验证码错误!");
            }

        }

       if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                return $this->splash('error',null,$error[0]);
            }
        }
        //$accountType = kernel::single('pam_tools')->checkLoginNameType($postData['uname']);
        try
        {
            $this->passport->sendVcode($postData['uname'],$postData['type']);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        if($accountType == "email")
        {
            return $this->splash('success',null,"邮箱验证链接已经发送至邮箱，请登录邮箱验证");
        }
        else
        {
            return $this->splash('success',null,"验证码发送成功");
        }
    }

    //找回密码第一步
    public function findPwd()
    {
        return $this->page('topc/passport/forgot/forgot.html');
    }

    //找回密码第二步
    public function findPwdTwo()
    {
        $postData = utils::_filter_input(input::get());
        if($postData)
        {
            $loginName = $postData['username'];
            $data = userAuth::getAccountInfo($loginName);

            if($data)
            {
                if( (!empty($data['email']) && $data['email_verify']) || !empty($data['mobile']))
                {
                    $send_status = 'true';
                }
                else
                {
                    $send_status = 'false';
                }
                $pagedata['send_status'] = $send_status;
                $pagedata['data'] = $data;
                return view::make('topc/passport/forgot/two.html', $pagedata);
            }
        }

        $url = url::action('topc_ctl_passport@findPwd');
        $msg = app::get('topc')->_('账户不存在');
        return $this->splash('error',$url,$msg);
    }

    //找回密码第三步
    public function findPwdThree()
    {
        $postData = utils::_filter_input(input::get());

        $vcode = $postData['vcode'];
        $loginName = $postData['uname'];
        $sendType = $postData['type'];
        $validator = validator::make(
            [$loginName],['required'],['用户不能为空!']
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                return $this->splash('error',null,$error[0]);
            }
        }
        //$accountType = kernel::single('pam_tools')->checkLoginNameType($loginName);
        $accountType = app::get('topc')->rpcCall('user.get.account.type',array('user_name'=>$loginName),'buyer');
        /**
		 * 暂时屏蔽验证码
		try
        {
            $vcodeData=userVcode::verify($vcode,$loginName,$sendType);
            if(!$vcodeData)
            {
                throw new \LogicException('验证码输入错误');
            }
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }
		*/

        $userInfo = userAuth::getAccountInfo($loginName);

        $key = userVcode::getVcodeKey($loginName ,$sendType);
        $userInfo['key'] = md5($vcodeData['vcode'].$key.$userInfo['user_id']);

        $pagedata['data'] = $userInfo;
        $pagedata['account'] = $loginName;
        if($accountType == "email")
        {
            return $this->page('topc/passport/forgot/email_three.html', $pagedata);
        }
        else
        {
            return view::make('topc/passport/forgot/three.html', $pagedata);
        }
    }

    //找回密码第四步
    public function findPwdFour()
    {
        $postData = utils::_filter_input(input::get());
        $userId = $postData['userid'];
        $account = $postData['account'];

        $vcodeData = userVcode::getVcode($account,'forgot');
        $key = userVcode::getVcodeKey($account,'forgot');

        if($account !=$vcodeData['account']  || $postData['key'] != md5($vcodeData['vcode'].$key.$userId) )
        {
            $msg = app::get('topc')->_('页面已过期,请重新找回密码');
            return $this->splash('failed',null,$msg,true);
        }

        $validator = validator::make(
            ['password' => $postData['password'] , 'password_confirmation' => $postData['confirmpwd']],
            ['password' => 'min:6|max:20|confirmed'],
            ['password' => '密码长度不能小于6位!|密码长度不能大于20位!|输入的密码不一致!']
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                return $this->splash('error',$url,$error[0],true);
            }
        }

        $data['type'] = 'reset';
        $data['new_pwd'] = $postData['password'];
        $data['user_id'] = $postData['userid'];
        $data['confirm_pwd'] = $postData['confirmpwd'];
        try
        {
            app::get('topc')->rpcCall('user.pwd.update',$data,'buyer');
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg,true);
        }
        return view::make('topc/passport/forgot/four.html');
    }

    //获取来源地址
    private function __getFromUrl()
    {
        $url = specialutils::filterCrlf(input::get('next_page', request::server('HTTP_REFERER')));
        $validator = validator::make([$url],['url'],['数据格式错误！']);
        if ($validator->fails())
        {
            return url::action('topc_ctl_default@index');
        }
        if( !is_null($url) )
        {
            if( strpos($url, 'passport') )
            {
                return url::action('topc_ctl_default@index');
            }
            return $url;
        }else{
            return url::action('topc_ctl_default@index');
        }
    }

    /**
     * author:wuzhipeng
     * time:17/4/17
     * selfsignup
     */
    public function selfsignup()
    {
        return;
        //如果已登录则跳转到退出页
        if( userAuth::check() ) $this->logout();
        $pagedata['next_page'] = $this->__getFromUrl();
        $pagedata['license'] = app::get('site')->getConf('site.usage_agreement');
        return $this->page('topc/passport/signup/selfsignup.html', $pagedata);


    }

    //企业会员申请主页
    public function companysignup()
    {
//        //未登录，跳转登录页
//        if( !userAuth::check() ) $this->logout();
//
//        $userId = userAuth::id();
//        $userMdel = app::get('sysuser')->Model('user');
//        $userInfo = $userMdel->getRow('usable,verify_status', ['user_id' => $userId, 'user_type' => '1']);
//        if($userInfo) {
//            //go 企业地方
//            if($userInfo['verify_status'] == 1) {
//                return redirect::action('topc_ctl_member@seInfoCompanydisplay')->send();
//            }
//            //go update
//            return redirect::action('topc_ctl_passport@updateCompany')->send();
//        }
//
//        $pagedata['next_page'] = $this->__getFromUrl();
//
//        //企业注册协议调用接口
//        $pagedata['license'] = app::get('sysuser')->getConf('sysuser.register.setting_company_license');
//
//        //地区相关
//        $pagedata['areaData'] = area::areaKvdata();
//        $pagedata['areaPath'] = area::getAreaIdPath();
//        $pagedata['areaLv1'] = area::getAreaDataLv1();
//
//        //注册广告位banner
//        //$pagedata['company_banner'] = app::get('sysconf')->getConf('sysconf_setting.company_banner');
//
//        return $this->page('topc/passport/signup/companysignup.html', $pagedata);
        //如果已登录则跳转到退出页
        $userId = userAuth::id();
        if (!$userId) {
            return redirect::action('topc_ctl_passport@login');
        }
        $companyInfo = app::get('sysuser')->model('user')->getRow('user_type,verify_status',['user_id' => $userId]);
        if ($companyInfo['user_type'] == '1' && $companyInfo['verify_status'] == '1') {
            return redirect::action('topc_ctl_member@seInfoCompanydisplay');
        } elseif ($companyInfo['user_type'] == '1' && $companyInfo['verify_status'] == '0') {
            return redirect::action('topc_ctl_passport@updateCompany');
        } elseif ($companyInfo['user_type'] == '1' && $companyInfo['verify_status'] == '2') {
            return redirect::action('topc_ctl_passport@updateCompany');
        }
        if( !userAuth::check() ) $this->logout();
        $pagedata['next_page'] = $this->__getFromUrl();
        $pagedata['license'] = app::get('sysuser')->getConf('sysuser.register.setting_company_license');//企业注册协议调用接口
        $pagedata['areaData'] = area::areaKvdata();
        $pagedata['areaPath'] = area::getAreaIdPath();

        if( input::get('id') )
        {
            $data = app::get('syslogistics')->rpcCall('logistics.ziti.get',['id'=>input::get('id')]);
            foreach( (array)explode(',',$data['area_id']) as $areaId)
            {
                if( $parentId )
                {
                    $areaData[$areaId] = $pagedata['areaPath'][$parentId];
                    $parentId = $areaId;
                }
                else
                {
                    $areaData[$areaId] = area::getAreaDataLv1();
                    $parentId = $areaId;
                }
            }
            $pagedata['selectArea'] = $areaData;

            $data['area'] = $data['area'].":".$data['area_id'];
            $pagedata['data'] = $data;
        }
        else
        {
            $pagedata['areaLv1'] = area::getAreaDataLv1();
        }
        //获取企业信息配置
        $pagedata['company_type'] = config::get('company');
        $pagedata['company_banner'] = app::get('sysconf')->getConf('sysconf_setting.company_banner');
        return $this->page('topc/passport/signup/companysignup.html', $pagedata);

    }

    /*
     *  账号注册选择页面 | 暂且不用
     */
    public function signoption(){
        return;
        //如果已登录则跳转到退出页
        if( !userAuth::check() ) $this->logout();

        $pagedata['next_page'] = $this->__getFromUrl();
        $pagedata['license'] = app::get('sysuser')->getConf('sysuser.register.setting_user_license');
        return $this->page('topcompany/passport/signup/signoption.html', $pagedata);

    }

    /**
     * wuzhipeng
     *
     *
     */
    public function updateCompany()
    {
        //登录判断
        if( !userAuth::check() ) $this->logout();

        $user_id = userAuth::id();

        $userMdel = app::get('sysuser')->Model('user');
        $user_info= $userMdel->getRow('*',array('user_id'=>$user_id));

        $companyMdel = app::get('sysuser')->Model('user_company');
        $company_info= $companyMdel->getRow('*',array('user_id'=>$user_id));

        $re=explode(':',$company_info['company_area']);
        $pagedata['area_id']  = str_replace('/', ',', $re[1]);

        $imageModel= app::get('image')->model('images');

        //读取图片
        $img_id_list = [
            $company_info['business_license'] => 'business_license',
        ];
        $img_ids = array_keys($img_id_list);
        $img_arr = $imageModel->getList("id,url", array('id|in' => $img_ids));
        foreach($img_arr AS $im) {
            $company_info[$img_id_list[$im['id']] . '1'] = $im['url'];
        }

        $pagedata['company_info']=$company_info;
        $pagedata['user_info']=$user_info;
        $pagedata['login_account']=userAuth::getLoginName($user_id);
        $pagedata['verify_status']=$user_info['verify_status'];
        $pagedata['refuse_reason']=$user_info['refuse_reason'];
        $pagedata['company_banner'] = app::get('sysconf')->getConf('sysconf_setting.company_banner');
        //获取企业信息配置
        $pagedata['company_type'] = config::get('company');
        //echo "<pre>";print_r($pagedata);exit;
        return $this->page('topc/passport/signin/company-signin.html',$pagedata);
    }

    public function ajaxGetCats()
    {
        $cat_id = input::get('cat_id');
        $new_cat_id = input::get('new_cat_id');
        $pagedata['cat_id'] = $cat_id;
        if ($new_cat_id) $pagedata['new_cat_id'] = $new_cat_id;
        return view::make('topshop/register/ajaxCats.html',$pagedata)->render();
    }
}
