<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topc_ctl_subaccount extends topc_ctl_member {

    public function __construct(&$app) {
        parent::__construct();
    }

    //子账号列表
    public function subAccountList() {
        $userId = userAuth::id();
        $userInfo = userAuth::getUserInfo();
        if (!userAuth::isCompany()) {
            //只有企业会员才可以管理子账号
            $url = url::action('topc_ctl_member@index');
            $msg = app::get('topc')->_('无权访问');
            return $this->splash('error', $url, $msg);
        }
        //取得当前用户的子账号
        $subIds = app::get('sysuser')->model('user')->getList('user_id', array('master_user_id' => $userId));
        $subInfo = [];
        foreach ($subIds as $v) {
            $subInfo[$v['user_id']] = kernel::single('sysuser_passport')->memInfo($v['user_id']);
            $roles = userAuth::getUserRoles($v['user_id']);
            $subInfo[$v['user_id']]['roles'] = implode(',', array_keys($roles['roles']));
        }
        $pagedata['subInfo'] = $subInfo;
        //var_dump( $subInfo);

        $this->action_view = "subaccount/list.html";
        $pagedata['action'] = 'topc_ctl_subaccount@subAccountList';
        return $this->output($pagedata);
    }

    //子账号, 添加/编辑 主页
    public function subAccountEdit() {
        //获取主帐号权限
        $mainUserId = userAuth::id();
        $mainuserRoles = userAuth::getUserRoles($mainUserId);
        $mainuserRole = $mainuserRoles['roles'];

        //子帐号
        $userId = input::get('user_id');
        $userRole = userAuth::getUserRoles($userId);
        unset($userRole[SYS_USER_ROLE_SELLER], $userRole[SYS_USER_ROLE_COMPANY], $mainuserRole[SYS_USER_ROLE_SELLER], $mainuserRole[SYS_USER_ROLE_COMPANY]);
        $pagedata['mainUserRole'] = $mainuserRole;
        $pagedata['userRole'] = array_keys($userRole['roles']);
        if ($userId) {
            $userInfo = kernel::single('sysuser_passport')->memInfo($userId);
            $pagedata['userInfo'] = $userInfo;
            $this->action_view = "subaccount/edit.html";
        } else {
            $this->action_view = "subaccount/add.html";
        }
        $pagedata['action'] = 'topc_ctl_subaccount@subAccountList';//dump($pagedata);exit;
        return $this->output($pagedata);
    }

    //保存子账号
    public function subAccountSave() {
        $masterUserId = userAuth::id();
        $data = input::get();
        $userInfo = $data['pam_user'];

        try {
            $accountType = kernel::single('pam_tools')->checkLoginNameType($userInfo['account']);
            $row = app::get('sysuser')->Model('account')->getRow('user_id', array('login_account' => $userInfo['account']));
            if ($row) {
                    throw new \LogicException(app::get('topc')->_('用户名已被使用'));
            }
            $userModel = app::get('sysuser')->Model('user');

            if ($accountType == 'mobile') {
                $userInfo['mobile'] = $userInfo['account'];
                $userId = userAuth::signUp($userInfo['account'], $userInfo['password'], $userInfo['pwd_confirm'], $userInfo['mobile']);
            }

            if ($accountType == 'email') {
                $userInfo['email'] = $userInfo['account'];
                $userId = userAuth::signUp($userInfo['account'], $userInfo['password'], $userInfo['pwd_confirm'], $userInfo['email']);
            }

            if($accountType =='login_account'){
                $userId = userAuth::signUp($userInfo['account'], $userInfo['password'], $userInfo['pwd_confirm']);
            }

            //根据$userId修改对应的user_type账号类型
            $updateData = array(
                'usable' => '1',    //默认可用
                'user_type' => '1', //类型，企业
                'verify_status' => '1', //审核通过
                'is_master' => '0', //子帐号类型
                'master_user_id' => $masterUserId,  //主帐号id
                'name' => $data['name'] //姓名
            );
            $userModel->update($updateData, array('user_id' => $userId));

            //设置用户权限
            $this->__setUserRole($userId, $data['role']);

            $url = url::action('topc_ctl_subaccount@subAccountList');
            $msg = '保存成功';
            return $this->splash('success', $url, $msg, true);
        } catch (Exception $e) {
            $msg = $e->getMessage();
            return $this->splash('error', $url, $msg, true);
        }
    }

    //执行更新子账号信息
    public function subAccountUpdate() {
            $masterUserId = userAuth::id();
            $accountMdel = app::get('sysuser')->Model('account');
            $data = input::get();
            $url = url::action('topc_ctl_subaccount@subAccountList');
            if (isset($data['disabled'])) {
                //禁用/启用
                $flag = $accountMdel->update(array('disabled' => $data['disabled'] ? '0' : '1','disabled_time'=>time()), array('user_id' => $data['user_id']));
                $msg = $flag ? '更新成功' : '更新失败';
                return $this->splash($flag ? 'success' : 'error', $url, $msg, true);
            } else {
                try {
                    //更新普通信息
                    $userMdel = app::get('sysuser')->Model('user');
                    $flag = $userMdel->update(array('name' => $data['name']), array('user_id' => $data['user_id']));

                    //设置用户权限
                    $this->__setUserRole($data['user_id'], $data['role']);

                    $msg = $flag ? '更新成功' : '更新失败';
                    return $this->splash($flag ? 'success' : 'error', $url, $msg, true);
                } catch (Exception $e) {
                    $msg = $e->getMessage();
                    return $this->splash('error', $url, $msg, true);
                }
            }
    }

    //修改子账号密码主页
    public function subAccountPassword() {
        $slaveUserId = input::get('user_id');
        $userId = userAuth::id();

        //取得当前用户的子账号
        $row = app::get('sysuser')->model('user')->getList('user_id', array('user_id' => $slaveUserId, 'master_user_id' => $userId));
        if (!userAuth::isCompany()) {
            //只有企业会员才可以修改子账号密码
            $url = url::action('topc_ctl_member@index');
            $msg = app::get('topc')->_('无权访问');
            return $this->splash('error', $url, $msg);
        }
        $this->action_view = "subaccount/modifypwd.html";
        $pagedata['user_id'] = $slaveUserId;
        return $this->output($pagedata);
    }

    //执行子帐号密码保存
    public function subAccountSavePwd() {

        try {
            //$userId = userAuth::id();
            $postData = utils::_filter_input(input::get());
            //var_dump($postData);exit;
            $validator = validator::make(
                ['password' => $postData['new_password'], 'password_confirmation' => $postData['confirm_password']], [ 'password' => 'min:6|max:20|confirmed', 'password_confirmation' => 'required'], [ 'password' => '密码长度不能小于6位!|密码长度不能大于20位!|输入的密码不一致!', 'password_confirmation' => '确认密码不能为空!']
            );
            if ($validator->fails()) {
                $messages = $validator->messagesInfo();
                foreach ($messages as $error) {
                    return $this->splash('error', null, $error[0]);
                }
            }
            $data = array(
                'new_pwd' => $postData['new_password'],
                'confirm_pwd' => $postData['confirm_password'],
                'user_id' => $postData['user_id'],
                'type' => "update",
            );
            kernel::single('sysuser_passport')->modifySubPwd($data);
        } catch (Exception $e) {
            $msg = $e->getMessage();
            return $this->splash('error', null, $msg);
        }

        $url = url::action("topc_ctl_subaccount@subAccountList");
        $msg = app::get('topc')->_('修改成功');

        return $this->splash('success', $url, $msg);
    }

    /**
     * 操作日志列表
     * @return type
     */
    public function showActionLog() {
        $userId = userAuth::id();
        $pageSize = $this->limit;
        $data = input::get();
        $currentPage = empty($data['pages']) ? 1 : (int) $data['pages'];

        $params = array(
            'page_no' => $pageSize * ($currentPage - 1),
            'page_size' => $pageSize,
            'fields' => '*',
        );
        $filter = array(
            'master_user_id' => $userId
        );

        if (!empty($data['user_id'])) {
            $filter['user_id'] = $data['user_id'];
        }
        if (!empty($data['memo'])) {
            $filter['memo|has'] = $data['memo'];
        }
        $params['page_no'] = $params['page_no'] ? $params['page_no'] : '0';
        $params['page_size'] = $params['page_size'] ? $params['page_size'] : '-1';
        $orderBy = $params['orderBy'] ? $params['orderBy'] : 'created_time DESC';
        $actionLogMdll = app::get('sysuser')->model('actionlog');
        $list = $actionLogMdll->getList($params['fields'], $filter, $params['page_no'], $params['page_size'], $orderBy);
        //$list = app::get('sysuser')->model('actionlog')->getList('*', array('master_user_id' => $userId));
        $count = $actionLogMdll->count($filter);
        //处理翻页数据
        unset($filter['master_user_id']);
        $pageFilter = $filter;
        $pageFilter['pages'] = time();
        if ($count > 0) {
            $total = ceil($count / $params['page_size']);
        }
        $pagedata['pagers'] = array(
            'link' => url::action('topc_ctl_subaccount@showActionLog', $pageFilter),
            'current' => $currentPage,
            'total' => $total,
            'token' => $pageFilter['pages'],
        );

        //取得所有子账号
        $subUserList = app::get('sysuser')->model('user')->getList('user_id', array('master_user_id' => $userId));
        $subUserIds = array();
        foreach ($subUserList as $u) {
            $subUserIds[] = $u['user_id'];
        }
        if (!empty($subUserIds)) {
            $subAccountList = app::get('sysuser')->model('account')->getList('user_id,login_account', array('user_id' => $subUserIds));
            $subAccount = array();
            foreach ($subAccountList as $u) {
                $subAccount[$u['user_id']] = $u['login_account'];
            }
        }
        //var_dump($subAccount);
        $pagedata['loglist'] = $list;
        $pagedata['count'] = $count;
        $pagedata['subAccount'] = $subAccount;
        $pagedata['selectData'] = $data;
        $pagedata['action'] = 'topc_ctl_subaccount@showActionLog';
        $this->action_view = "subaccount/loglist.html";
        return $this->output($pagedata);
    }

    //设置子帐号权限
    private function __setUserRole($userId, $roles) {
        $role_config = config::get('userAuth.user_role');
        $role_config_list = array_keys($role_config);
        $role_code = kernel::single('ectools_code')->init($role_config_list);
        $_role = $role_code->setIsxx(0, SYS_USER_ROLE_CUSTOM);
        if($roles) {
            foreach ($roles AS $r) {
                $_role = $role_code->setIsxx($_role, $r);
            }
        }
        app::get('sysuser')->model('account')->update(['user_role' => $_role], ['user_id' => $userId]);
    }
}
