<?php
/**
 * Created by PhpStorm.
 * User: dengLy
 * Date: 2016/1/4
 * Time: 10:12
 */
class topcompany_ctl_passport extends topc_controller{

    public function checkLoginPassword()
    {
        $signAccount = utils::_filter_input(input::get());
        $loginPassword = $signAccount['pam_user']['password'];

        $validator = validator::make(
            [$loginPassword],['required'],['请填写密码!']
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
            $loginPassword_length=strlen($loginPassword);
            if($loginPassword_length<6){
                throw new \LogicException('请填写密码，6-20个字符');
            }
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',$url,$msg,true);
        }
        return response::json($json);
    }


    public function checkLoginConfirmpwd()
    {

        $signAccount = utils::_filter_input(input::get());
        $loginPwd_confirm = $signAccount['pam_user']['pwd_confirm'];

        $validator = validator::make(
            [$loginPwd_confirm],['required'],['请填写确认密码!']
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

            $loginPwd_length=strlen($loginPwd_confirm);
            if($loginPwd_length<6){
                throw new \LogicException('请填写确认密码，6-20个字符');
            }

        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',$url,$msg,true);
        }
        return response::json($json);
    }


    public function verifyPassword()
    {

        $loginPwd_confirm=$_POST['login_pwd_confirm'];
        $loginPassword=$_POST['loginPassword'];
        try
        {

            if($loginPwd_confirm!=$loginPassword){
                throw new \LogicException('密码输入不一致');
            }

        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',$url,$msg,true);
        }
        return response::json($json);
    }


    public function companycreate()
    {

        //是否登录
        $userId = userAuth::id();
        $url = url::action('topc_ctl_passport@signin');
        if(!$userId) return $this->splash('error', $url, '请先登录！', true);

        $area_data = input::get();
        $data = utils::_filter_input(input::get());

        $userInfo = $data['pam_user'];

        $validator = validator::make(
            [
                'business_license' => $data['demand']['business_license'][0],
                'company_name' => $data['pam_user']['company_name'],
                'tax_id' => $data['pam_user']['tax_id'],
                'registered_capital' => $data['pam_user']['registered_capital'],
                'company_address' => $data['pam_user']['company_address'],
                'company_contact' => $data['pam_user']['company_contact'],
                'company_contactPhone' => $data['pam_user']['company_contactPhone'],
                'company_peopleNum' => $data['pam_user']['company_peopleNum'],
                'company_nature' => $data['pam_user']['company_nature'],
            ],
            [
                'business_license' => 'required',
                'company_name' => 'required',
                'tax_id' => 'required',
                'registered_capital' => 'required|numeric',
                'company_address' => 'required',
                'company_contact' => 'required',
                'company_contactPhone' => 'required|max:13',
                'company_peopleNum' => 'required',
                'company_nature' => 'required',
            ],
            [
                'business_license' => '请上传营业执照',
                'company_name' => '公司名称不能为空',
                'tax_id' => '营业执照登记号不能为空',
                'registered_capital' => '注册资本不能为空|注册资本必须为正整数',
                'company_address' => '经营地址不能为空',
                'company_contact' => '联系人不能为空',
                'company_contactPhone' => '公司电话不能为空|公司电话最大长度为13位',
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
        //判断tax_id唯一性
        $user_company_mdel=app::get('sysuser')->Model('user_company');
        $info = $user_company_mdel->getRow('company_id',['tax_id' => trim($data['pam_user']['tax_id'])]);
        if ($info) {
            return $this->splash('error',null,'该营业执照登记号已存在！');
        }
        $db = app::get('sysuser')->database();
        $db->beginTransaction();
        try {
            //根据$userId修改对应的user_type账号类型
            $userMdel = app::get('sysuser')->Model('user');
            $userMdel->update(array('usable'=>'0','user_type' => '1','verify_status'=>'1'), array('user_id' => $userId));

            //增加用户企业权限
            $role = userAuth::setUserRoles($userId, SYS_USER_ROLE_COMPANY);

            //根据$data['area'][0]查询地区名称，存入格式“天津市/河西区:120100/120103”--string(13) "110100,110101"
            $string_area=$area_data['area'][0];
            $area = app::get('topc')->rpcCall('logistics.area', array('area' => $string_area));
            if ($area) {
                $areaId = str_replace(",", "/", $string_area);
                $resultData['area'] = $area . ':' . $areaId;
            }else {
                $msg = '地区不存在!';
                return $this->splash('error', null, $msg);
            }

            //根据$userId保存公司资质信息，公司信息

            $parameter['business_license']=$data['demand']['business_license'][0];//营业执照
            $parameter['special_aptitude']=$data['demand']['special_aptitude'][0];//特种物资采购必备资质
            $parameter['fax']=$data['pam_user']['fax'];//传真
            $parameter['website']=$data['pam_user']['website'];//网址
            $parameter['email']=$data['pam_user']['email'];//邮箱
            $parameter['company_name']=$data['pam_user']['company_name'];//公司名称
            $parameter['valid_time']=$data['long_valid_time'] ? '长期有效' : $data['valid_time'];
            $parameter['tax_id']=trim($data['pam_user']['tax_id']);//税务登记号
            $parameter['registered_capital']=$data['pam_user']['registered_capital'];//注册资本
            $parameter['company_area']= $resultData['area'];//公司地区
            $parameter['company_addr']=$data['pam_user']['company_address'];//公司地址
            $parameter['company_num']=$data['pam_user']['company_peopleNum'];//企业人数
            $parameter['operating_range']=$data['pam_user']['company_nature'];//公司性质
            $parameter['company_contact']=$data['pam_user']['company_contact'];//公司联系人
            $parameter['company_contactPhone']=$data['pam_user']['company_contactPhone'];//联系人手机号
            $parameter['user_id']=$userId;//会员id
            $parameter['updatetime']=time();//入库时间

            $result = $user_company_mdel->save($parameter);

            //获取后台设置注册初始积分数据
            $initial_points=app::get('sysconf')->getConf('point.initial.points');

            //向会员积分表中更新初始积分数据
            $user_pointsMdel = app::get('sysuser')->Model('user_points');
            $params['user_id']=$userId;
            $params['point_count']=$initial_points;
            $params['modified_time']=time();
            $user_pointsMdel->save($params);

            // 向积分sysuser_user_pointlog记录表中存入注册积分记录
            $objMdlUserPointLog = app::get('sysuser')->model('user_pointlog');
            $params_pointlog['user_id']=$userId;
            $params_pointlog['modified_time']=time();
            $params_pointlog['behavior_type']='regist';
            $params_pointlog['behavior']='注册获得积分';
            $params_pointlog['point']=$initial_points;
            $objMdlUserPointLog->save($params_pointlog);
            //注册生成信用基础积分base
            $companyInfo = $user_company_mdel->getRow('*', ['user_id' => $userId]);
            kernel::single('sysuser_data_user_credit')->setCreditPoint($companyInfo, 'sysuser_user_company');
            $db->commit();
        } catch (Exception $e) {
            $msg = $e->getMessage();
            $db->rollback();
            return $this->splash('error', $url, $msg, true);
        }
        $url = url::action('topc_ctl_member@control');

        return $this->splash('success', $url, null, true);
    }

    //更新企业申请信息
    public function companyupdate()
    {
        $area_data = input::get();
        $data = utils::_filter_input(input::get());
        $user_id = userAuth::id();
        if (!$user_id) {
            $url = url::action('topc_ctl_passport@signin');
            return $this->splash('success', $url, null, true);
        }
        try {
            //根据$data['area'][0]查询地区名称，存入格式“天津市/河西区:120100/120103”--string(13) "110100,110101"
            $string_area=$area_data['area'][0];
            $area = app::get('topc')->rpcCall('logistics.area', array('area' => $string_area));
            if ($area) {
                $areaId = str_replace(",", "/", $string_area);
                $resultData['area'] = $area . ':' . $areaId;
            }else {
                $msg = '地区不存在!';
                return $this->splash('error', null, $msg);
            }


            if(empty($data['demand']['business_license'][0])){
                $data['demand']['business_license'][0]=$data['demand']['business_license_1'][0];
            }

            $validator = validator::make(
                [
                    'business_license' => $data['demand']['business_license'][0],
                    'company_name' => $data['pam_user']['company_name'],
                    'tax_id' => $data['pam_user']['tax_id'],
                    'registered_capital' => $data['pam_user']['registered_capital'],
                    'company_address' => $data['pam_user']['company_address'],
                    'company_contact' => $data['pam_user']['company_contact'],
                    'company_contactPhone' => $data['pam_user']['company_contactPhone'],
                    'company_peopleNum' => $data['pam_user']['company_peopleNum'],
                    'company_nature' => $data['pam_user']['company_nature'],
                ],
                [
                    'business_license' => 'required',
                    'company_name' => 'required',
                    'tax_id' => 'required',
                    'registered_capital' => 'required|numeric',
                    'company_address' => 'required',
                    'company_contact' => 'required',
                    'company_contactPhone' => 'required|max:13',
                    'company_peopleNum' => 'required',
                    'company_nature' => 'required',
                ],
                [
                    'business_license' => '请上传营业执照',
                    'company_name' => '公司名称不能为空',
                    'tax_id' => '营业执照登记号不能为空',
                    'registered_capital' => '注册资本不能为空|注册资本必须为正整数',
                    'company_address' => '经营地址不能为空',
                    'company_contact' => '联系人不能为空',
                    'company_contactPhone' => '公司电话不能为空|公司电话最大长度为13位',
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
            //判断tax_id唯一性
            $user_company_mdel=app::get('sysuser')->Model('user_company');
            $info = $user_company_mdel->getRow('company_id',['tax_id' => trim($data['pam_user']['tax_id'])]);
            if ($info['company_id'] != $data['company_id']) {
                return $this->splash('error',null,'该营业执照登记号已存在！');
            }

            //根据$userId保存公司资质信息，公司信息
            $parameter['company_id'] = $data['company_id'];
            $parameter['business_license']=$data['demand']['business_license'][0];//营业执照
            $parameter['special_aptitude']=$data['demand']['special_aptitude'][0];//特种物资采购必备资质
            $parameter['fax']=$data['pam_user']['fax'];//传真
            $parameter['website']=$data['pam_user']['website'];//网址
            $parameter['email']=$data['pam_user']['email'];//邮箱
            $parameter['company_name']=$data['pam_user']['company_name'];//公司名称
            $parameter['valid_time']=$data['long_valid_time'] ? '长期有效' : $data['valid_time'];
            $parameter['tax_id']=trim($data['pam_user']['tax_id']);//税务登记号
            $parameter['registered_capital']=$data['pam_user']['registered_capital'];//注册资本
            $parameter['company_area']= $resultData['area'];//公司地区
            $parameter['company_addr']=$data['pam_user']['company_address'];//公司地址
            $parameter['company_num']=$data['pam_user']['company_peopleNum'];//企业人数
            $parameter['operating_range']=$data['pam_user']['company_nature'];//公司性质
            $parameter['company_contact']=$data['pam_user']['company_contact'];//公司联系人
            $parameter['company_contactPhone']=$data['pam_user']['company_contactPhone'];//联系人手机号
            $parameter['user_id']=$user_id;//会员id
            $parameter['updatetime']=time();//入库时间

            $result=$user_company_mdel->save($parameter);
            //根据$userId修改对应的user_type账号类型
            $userMdel = app::get('sysuser')->Model('user');
            $userMdel->update(array('usable'=>'0','user_type' => '1','verify_status'=>'0'), array('user_id' => $user_id));
        } catch (Exception $e) {
            $msg = $e->getMessage();
            return $this->splash('error', $url, $msg, true);
        }

        $url = url::action('topc_ctl_passport@updateCompany');
        return $this->splash('success', $url, null, true);
    }

    private function __getFromUrl()
    {
        $url = input::get('next_page', request::server('HTTP_REFERER'));
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
}
