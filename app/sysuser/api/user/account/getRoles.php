<?php
class sysuser_api_user_account_getRoles{
    public $apiDescription = "根据会员ID获取对应的用户名";
    public function getParams()
    {
        $return['params'] = array(
            'user_id' => ['type'=>'string','valid'=>'required', 'description'=>'用户ID必填','default'=>'','example'=>''],
        );
        return $return;
    }
    public function getRoles($params)
    {
        if(!$params['user_id'])
        {
            throw new \LogicException('参数user_id不能为空！');
        }
        $userId = $params['user_id'];

        $userRoles = kernel::single('sysuser_passport')->getUserRoles($userId);
        return $userRoles;
    }
}
