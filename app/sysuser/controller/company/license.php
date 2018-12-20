<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2016/1/4
 * Time: 14:32
 */
class sysuser_ctl_company_license extends desktop_controller {

    public function license()
    {
        if( $_POST['license'] )
        {
            $this->begin();
            app::get('sysuser')->setConf('sysuser.register.setting_company_license',$_POST['license']);
            $this->end(true, app::get('sysuser')->_('当前配置修改成功！'));
        }
        $pagedata['license'] = app::get('sysuser')->getConf('sysuser.register.setting_company_license');
        return $this->page('sysuser/company/license.html', $pagedata);
    }
}