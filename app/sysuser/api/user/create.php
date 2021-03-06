<?php
class sysuser_api_user_create{

    public $apiDescription = "创建会员信息";
    public function getParams()
    {
        $return['params'] = array(
            'account' => ['type'=>'string','valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'用户名必填'],
            'password' => ['type'=>'int','valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'密码必填'],
            'pwd_confirm' => ['type'=>'int','valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'确认密码必填'],
            'reg_ip' => ['type'=>'string','valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'会员注册时的IP'],
            'account' => ['type'=>'string','valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'姓名'],
        );
        return $return;
    }

    public function add($params)
    {
        $userId = kernel::single('sysuser_passport')->signupUser($params);
        event::fire('user.create',[$userId]);
        return $userId;
    }
}
