<?php
/**
 * @brief 商城账号
 */
class sysuser_ctl_admin_user extends desktop_controller {


    function __construct($app){
        parent::__construct($app);
        $this->pamUserModel = app::get('sysuser')->model('account');
        $this->sysUserModel = app::get('sysuser')->model('user');
    }
    /**
     * @brief  商家账号列表
     *
     * @return
     */
    public function index()
    {
        return $this->finder('sysuser_mdl_user',array(
            'title' => app::get('sysuser')->_('个人会员列表'),
            'base_filter' => array('user_type' => '0'),//商城会员
            'use_buildin_filter' => true,
            'use_buildin_delete' => true,
            'actions'=>array(
                array(
                    'label'=>app::get('syscategory')->_('为选中的会员标记标签'),
                    'submit'=>'?app=sysuser&ctl=admin_tag&act=bindTag','target'=>'dialog::{title:\''.app::get('syscategory')->_('添加标签').'\',width:680,height:350}',
                    'href'=>'javascript:void(0)',
                ),
		array(
	               'label' => app::get('sysuser')->_('导出'),
	               'submit' => '?app=sysuser&ctl=admin_export&act=view',
	               'target'=>'dialog::{title:\''.app::get('sysuser')->_('导出').'\',width:400,height:170}',
	           ),
            )
        ));
    }

    public function license()
    {
        if( $_POST['license'] )
        {
            $this->begin();
            app::get('sysuser')->setConf('sysuser.register.setting_user_license',$_POST['license']);
            $this->end(true, app::get('sysuser')->_('当前配置修改成功！'));
        }
        $pagedata['license'] = app::get('sysuser')->getConf('sysuser.register.setting_user_license');
        return $this->page('sysuser/license.html', $pagedata);
    }

    /**
     * @brief  前台会员信息修改
     *
     * @return
     */
    public function editUserInfo($userId)
    {

        $sysInfo = kernel::single('sysuser_passport')->memInfo($userId);

        if($sysInfo['sex']==1)
        {
            $sysInfo['sex']='male';
        }
        else
        {
            $sysInfo['sex']='female';
        }
        $data = array(
            'user_id'=>$sysInfo['userId'],
            'name'=>$sysInfo['name'],
            'sex'=>$sysInfo['sex'],
            'birthday'=>$sysInfo['birthday'],
            'reg_ip'=>$sysInfo['reg_ip'],
            'regtime'=>$sysInfo['regtime'],
            'login_account'=>$sysInfo['login_account'],
            'email'=>$sysInfo['email'],
            'mobile'=>$sysInfo['mobile'],
        );

        $pagedata['data'] = $data;
        return $this->page('sysuser/admin/editinfo.html', $pagedata);
    }

    /**
     * @brief 企业会员信息审核
     * @param $userId
     */
    public function checkInfo($userId){
        $sysInfo = kernel::single('sysuser_passport')->memInfo($userId);

        if($sysInfo['sex']==1)
        {
            $sysInfo['sex']='male';
        }
        else
        {
            $sysInfo['sex']='female';
        }
        //根据$userId查询公司信息
        $user_company_mdel=app::get('sysuser')->Model('user_company');
        $user_company=$user_company_mdel->getRow('*',array('user_id'=>$userId));
        $imageModel= app::get('image')->model('images');


        $user_company['business_license'] = $imageModel->getRow("url", array('id' => $user_company['business_license']));

        $data = array(
            'user_id'=>$sysInfo['userId'],
            'name'=>$sysInfo['name'],
            'sex'=>$sysInfo['sex'],
            'birthday'=>$sysInfo['birthday'],
            'reg_ip'=>$sysInfo['reg_ip'],
            'regtime'=>$sysInfo['regtime'],
            'login_account'=>$sysInfo['login_account'],
            'mobile'=>$sysInfo['mobile'],
            'company_name'=>$user_company['company_name'],
            'business_license'=>$user_company['business_license']['url'],
            'special_aptitude'=>$user_company['special_aptitude'],
            'tax_id'=>$user_company['tax_id'],
            'company_addr'=>$user_company['company_addr'],
            'company_num'=>$user_company['company_num'],
            'operating_range'=>$user_company['operating_range'],
            'company_contact'=>$user_company['company_contact'],
            'company_contactPhone'=>$user_company['company_contactPhone'],
            'registered_capital'=>$user_company['registered_capital'],
            'fax'=>$user_company['fax'],
            'website'=>$user_company['website'],
            'email'=>$user_company['email'],
            'valid_time'=>$user_company['valid_time'] ? $user_company['valid_time'] : '长期有效',

        );

        $pagedata['data'] = $data;
//        echo "<pre>";
//        var_dump($data['business_license']);
        $pagedata['areaData'] = area::areaKvdata();
        $pagedata['areaPath'] = area::getAreaIdPath();
        $company_area=$user_company['company_area'] ;
        if( $company_area )
        {
            $re=explode(':',$company_area);
            $data['area_id']  = str_replace('/', ',', $re[1]);
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

        return $this->page('sysuser/admin/check_info.html', $pagedata);
    }


    /**
     * @brief  企业会员信息修改
     * custom s dengLy
     * 2016.1.11
     */
    public function editCompanyInfo($userId)
    {

        $sysInfo = kernel::single('sysuser_passport')->memInfo($userId);

        /*if($sysInfo['sex']==1)
        {
            $sysInfo['sex']='male';
        }
        else
        {
            $sysInfo['sex']='female';
        }*/
        //根据$userId查询是否是企业用户
        $userInfoMdl=app::get('sysuser')->model('user');
        $users=$userInfoMdl->getRow('*',array('user_id'=>$userId));
        if ($users['is_master']==0 && $users['master_user_id'] != null && $users['user_type']=='1') {
            $userId=$users['master_user_id'];
        }
        //根据$userId查询公司信息
        $user_company_mdel=app::get('sysuser')->Model('user_company');
        $user_company=$user_company_mdel->getRow('*',array('user_id'=>$userId));
        $imageModel= app::get('image')->model('images');
//echo "<pre>";print_r($users);exit;

        $user_company['business_license'] = $imageModel->getRow("url", array('id' => $user_company['business_license']));

        $data = array(
            'user_id'=>$sysInfo['userId'],
            'name'=>$sysInfo['name'],
            'sex'=>$sysInfo['sex'],
            'birthday'=>$sysInfo['birthday'],
            'reg_ip'=>$sysInfo['reg_ip'],
            'regtime'=>$sysInfo['regtime'],
            'login_account'=>$sysInfo['login_account'],
            'mobile'=>$sysInfo['mobile'],
            'company_name'=>$user_company['company_name'],
            'business_license'=>$user_company['business_license']['url'],
            'special_aptitude'=>$user_company['special_aptitude'],
            'tax_id'=>$user_company['tax_id'],
            'company_addr'=>$user_company['company_addr'],
            'company_num'=>$user_company['company_num'],
            'operating_range'=>$user_company['operating_range'],
            'company_contact'=>$user_company['company_contact'],
            'company_contactPhone'=>$user_company['company_contactPhone'],
            'registered_capital'=>$user_company['registered_capital'],
            'fax'=>$user_company['fax'],
            'website'=>$user_company['website'],
            'email'=>$user_company['email'],
            'valid_time'=>$user_company['valid_time'] ? $user_company['valid_time'] : '长期有效',

        );

        $pagedata['data'] = $data;
//        echo "<pre>";
//        var_dump($data['business_license']);
        $pagedata['areaData'] = area::areaKvdata();
        $pagedata['areaPath'] = area::getAreaIdPath();
        $company_area=$user_company['company_area'] ;
        if( $company_area )
        {
            $re=explode(':',$company_area);
            $data['area_id']  = str_replace('/', ',', $re[1]);
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
        $pagedata['company_type'] = config::get('company');

        return $this->page('sysuser/admin/editcompanyinfo.html', $pagedata);
    }


    /*
     * 企业账户审核,拒绝
     */
    public function checkCompanyInfo()
    {
        $pagedata['user_id']=$_GET['user_id'];
        $pagedata['finder_id']=$_GET['finder_id'];
        return $this->page('sysuser/company/checkcompanyinfo.html', $pagedata);
    }

    public function refuseCompanyInfo()
    {
        $pagedata['user_id']=$_GET['user_id'];
        return $this->page('sysuser/company/refusecompanyinfo.html', $pagedata);
    }



    public function approve()
    {
        $this->begin("?app=sysuser&ctl=company_list&act=index");
        $postdata = input::get();

        try {
            if($postdata['status']=="agree"){//修改sysuser_user 表中verify_status状态变为1
                //根据$userId修改对应的user_type账号类型
                $userMdels = app::get('sysuser')->model('user');
                $role = userAuth::setUserRoles($postdata['user_id'], SYS_USER_ROLE_COMPANY);
                $userMdels->update(array('verify_status'=>'1'), array('user_id' => $postdata['user_id']));
            }
            $this->adminlog("[企业账户:{$postdata['user_id']}]", 1);

        } catch (Exception $e) {
            $this->adminlog("[企业账户:{$postdata['user_id']}]", 0);
            $msg = $e->getMessage();
            $this->end(false, $msg);
        }
        $this->end(true);
    }

    public function refuse()
    {
        $this->begin("?app=sysuser&ctl=company_list&act=index");
        $postdata = input::get();

        try {
            if($postdata['status']=="refuse"){//修改sysuser_user 表中verify_status状态变为1
                //根据$userId修改对应的user_type账号类型
                $userMdels = app::get('sysuser')->model('user');
                $userMdels->update(array('refuse_reason'=>$postdata['data']['cancel_reason'],'verify_status'=>'2'), array('user_id' => $postdata['user_id']));
            }
            $this->adminlog("[企业账户:{$postdata['user_id']}]", 1);

            //给会员发送审核通知
            $userList=app::get('sysuser')->model('account')->getRow('*',array('user_id'=>$postdata['user_id']));
            logger::info('Send user usercheck start:');
            try {
                if($userList['mobile']){
                    $content['login_account']=$userList['login_account'];
                    $content['status']=$postdata['status'];
                    $content['refuse_reason']=$postdata['data']['cancel_reason'];
                    messenger::sendSms($userList['mobile'],'user-check',$content);
                }
            }catch(Exception $e) {
                logger::info('sendsms error:' . var_export($e->getMessage(), 1));
            }
            logger::info('Send user usercheck end:');

        } catch (Exception $e) {
            $this->adminlog("[企业账户:{$postdata['user_id']}]", 0);
            $msg = $e->getMessage();
            $this->end(false, $msg);
        }
        $this->end(true);
    }
     /*
     * 企业账户审核
     *  custom e dengLy
     *  2016.1.5
     */
    public function saveCheckInfo(){
        try
        {

            $data = $_POST;
            kernel::single('sysuser_passport')->saveInfo($data);
            /*$company_profiles=input::get()['company']['company_profiles'];
            if (!$company_profiles) {
                $company_profiles=null;
            }*/
            //同步修改公司表中的公司信息
            $companyMdel = app::get('sysuser')->Model('user_company');
            $postdata['company_name']=$data['company']['company_name'];
            // $postdata['company_profiles']=$data['company']['company_profiles'];
            //$postdata['company_profiles']=$company_profiles;
            $postdata['business_license']=$data['company']['business_license'];
            //$postdata['organization_code']=$data['company']['organization_code'];
            //$postdata['taxpayer_license']=$data['company']['taxpayer_license'];
            //$postdata['special_aptitude']=$data['company']['special_aptitude'];
            //$postdata['company_proxy']=$data['company']['company_proxy'];
            $postdata['tax_id']=$data['company']['tax_id'];
            $postdata['company_addr']=$data['company']['company_addr'];
            $postdata['company_num']=$data['company']['company_num'];
            $postdata['operating_range']=$data['company']['operating_range'];
            $postdata['company_phone']=$data['company']['company_phone'];
            $postdata['company_contact']=$data['company']['company_contact'];
            $postdata['company_contactPhone']=$data['company']['company_contactPhone'];
            $postdata['company_area']= str_replace(",", "/", $data['area_id']);

            // 发票信息
            //$postdata['invoice_bank_name']=$data['company']['invoice_bank_name'];
            //$postdata['invoice_bank_num']=$data['company']['invoice_bank_num'];
            //$postdata['invoice_addr']=$data['company']['invoice_addr'];
            //$postdata['invoice_mobile']=$data['company']['invoice_mobile'];

            $companyMdel->update($postdata, array('user_id' => $data['user']['user_id']));

            $userMdel = app::get('sysuser')->Model('user');
            $userMdel->update(array('verify_status'=>'1'), array('user_id' => $data['user']['user_id']));
            $this->adminlog("审核会员信息[USER_ID:{$data['user']['user_id']}]", 1);

            //增加用户企业权限
            $role = userAuth::setUserRoles($data['user']['user_id'], SYS_USER_ROLE_COMPANY);

            //注册生成信用基础积分base
            $companyInfo = $companyMdel->getRow('*', ['user_id' => $data['user']['user_id']]);
            kernel::single('sysuser_data_user_credit')->setCreditPoint($companyInfo, 'sysuser_user_company');

            //给会员发送审核通知
            $userList=app::get('sysuser')->model('account')->getRow('*',array('user_id'=>$data['user']['user_id']));
            logger::info('Send user usercheck start:');
            try {
                if($userList['mobile']){
                    $content['login_account']=$userList['login_account'];
                    $content['status']="agree";
                    messenger::sendSms($userList['mobile'],'user-check',$content);
                }
            }catch(Exception $e) {
                logger::info('sendsms error:' . var_export($e->getMessage(), 1));
            }
            logger::info('Send user usercheck end:');

        }
        catch(Exception $e)
        {
            $this->adminlog("审核会员信息[USER_ID:{$data['user']['user_id']}]", 0);
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        $msg = app::get('sysuser')->_('审核成功');

        return $this->splash('success',null,$msg);

    }


    /**
     * @brief  前台企业会员信息保存
     *
     * @return
     */
    public function saveCompanyInfo()
    {
        try
        {
            $data = $_POST;

            $validator = validator::make(
                [
                    'business_license' => $data['company']['business_license'],
                    'company_name' => $data['company']['company_name'],
                    'tax_id' => $data['company']['tax_id'],
                    'registered_capital' => $data['company']['registered_capital'],
                    'company_address' => $data['company']['company_addr'],
                    'company_peopleNum' => $data['company']['company_num'],
                    'company_nature' => $data['company']['operating_range'],
                ],
                [
                    'business_license' => 'required',
                    'company_name' => 'required',
                    'tax_id' => 'required',
                    'registered_capital' => 'required|numeric',
                    'company_address' => 'required',
                    'company_peopleNum' => 'required',
                    'company_nature' => 'required',
                ],
                [
                    'business_license' => '请上传营业执照',
                    'company_name' => '公司名称不能为空',
                    'tax_id' => '营业执照登记号不能为空',
                    'registered_capital' => '注册资本不能为空|注册资本必须为正整数',
                    'company_address' => '经营地址不能为空',
                    'company_peopleNum' => '请选择企业人数',
                    'company_nature' => '请选择企业规模',
                ]
            );

            if ($validator->fails())
            {
                $messages = $validator->messagesInfo();
                foreach( $messages as $error )
                {
                    return $this->splash('error',null,$error[0]);
                }
            }

            kernel::single('sysuser_passport')->saveInfo($data);

            //查询图片ID
            $imageModel= app::get('image')->model('images');
            $business_license = $imageModel->getRow('id',array('url' => $data['company']['business_license']));

            //同步修改公司表中的公司信息
            $companyMdel = app::get('sysuser')->Model('user_company');
            $postdata['company_name']=$data['company']['company_name'];
            $postdata['business_license']=$business_license['id'];
            $postdata['tax_id']=$data['company']['tax_id'];
            $postdata['fax']=$data['company']['fax'];
            $postdata['website']=$data['company']['website'];
            $postdata['email']=$data['company']['email'];
            $postdata['valid_time']=$data['company']['valid_time'];
            $postdata['registered_capital']=$data['company']['registered_capital'];
            $postdata['company_addr']=$data['company']['company_addr'];
            $postdata['company_num']=$data['company']['company_num'];
            $postdata['operating_range']=$data['company']['operating_range'];
            $postdata['company_contact']=$data['company']['company_contact'];
            $postdata['company_contactPhone']=$data['company']['company_contactPhone'];
            $postdata['company_area']= str_replace(",", "/", $data['area_id']);

            $companyMdel->update($postdata, array('user_id' => $data['user']['user_id']));

            $userMdel = app::get('sysuser')->Model('user');
            $userMdel->update(array('verify_status'=>'0'), array('user_id' => $data['user']['user_id']));
            $this->adminlog("修改会员信息[USER_ID:{$data['user']['user_id']}]", 1);
        }
        catch(Exception $e)
        {
            $this->adminlog("修改会员信息[USER_ID:{$data['user']['user_id']}]", 0);
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        $msg = app::get('sysuser')->_('修改成功');

        return $this->splash('success',null,$msg);
    }

    /*
     * @brief  前台会员信息保存
     *
     * @return
     */
    public function saveUserInfo()
    {
        try
        {
            $data = $_POST;
            kernel::single('sysuser_passport')->saveInfo($data);
            $this->adminlog("修改会员信息[USER_ID:{$data['user']['user_id']}]", 1);
        }
        catch(Exception $e)
        {
            $this->adminlog("修改会员信息[USER_ID:{$data['user']['user_id']}]", 0);
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        $msg = app::get('sysuser')->_('修改成功');

        return $this->splash('success',null,$msg);
    }

    /**
     * @brief  前台会员密码修改
     *
     * @return
     */
    public function updatePwd()
    {
        try
        {
            $data = $_POST;
            $params = array(
                'type' =>'reset',
                'new_pwd' =>$data['login_password'],
                'confirm_pwd' =>$data['psw_confirm'],
                'user_id' =>$data['user_id'],
            );

            kernel::single('sysuser_passport')->modifyPwd($params);
            $this->adminlog("修改会员密码[USER_ID:{$data['user_id']}]", 1);
        }
        catch(Exception $e)
        {
            $this->adminlog("修改会员密码[USER_ID:{$data['user_id']}]", 0);
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        $msg = app::get('sysuser')->_('修改成功');

        return $this->splash('success',null,$msg);
    }

    public function changePoint()
    {
        $data = input::get('point');

        if(!$data['user_id'])
        {
            $msg = app::get('sysuser')->_('会员参数错误');
            return $this->splash('error',null,$msg);
        }
        $objMdlUserPoints = app::get('sysuser')->model('user_points');
        $points = $objMdlUserPoints->getRow('point_count',array('user_id'=>$data['user_id']));
        if(iconv_strlen($data['modify_point']) > 10)
        {
            $msg = app::get('sysuser')->_('积分值长度过长');
            return $this->splash('error',null,$msg);
        }

        if($data['modify_point'] < 0 && abs($data['modify_point']) > $points['point_count'])
        {
            $msg = app::get('sysuser')->_('会员积分不足');
            return $this->splash('error',null,$msg);
        }
        //平台修改积分
        $objPoints = kernel::single('sysuser_data_user_points');
        try
        {
            //平台操作会员积分时，先处理会员的积分过期
            $result = $objPoints->pointExpiredCount($data['user_id']);
            if(!$result)
            {
                throw new Exception('会员积分过期处理失败');
            }

            $result = $objPoints->changePoint($data);
            if(!$result)
            {
                throw new Exception('会员积分更改失败');
            }
            $this->adminlog("更改会员积分[USER_ID:{$data['user_id']}]", 1);
        }
        catch(Exception $e)
        {
            $this->adminlog("更改会员积分[USER_ID:{$data['user_id']}]", 0);
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }
        $msg = app::get('sysuser')->_('修改成功');
        return $this->splash('success',null,$msg);
    }

    /**
     * @brief  前台会员密码修改
     *
     * @return
     */
    public function resetDepositPassword()
    {
        try{
            $userId = $_GET['user_id'];
            if(!$userId > 0 )
                throw new LogicException('用户Id格式错误');

            $deposit = kernel::single('sysuser_data_deposit_password')->resetPassword($userId);
            $this->adminlog("重置会员支付密码[USER_ID:{$data['user_id']}]", 1);
        }
        catch(Exception $e)
        {
            $this->adminlog("重置会员支付密码[USER_ID:{$data['user_id']}]", 0);
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        $msg = app::get('sysuser')->_('重置成功');
        return $this->splash('success',null,$msg);
    }
}
