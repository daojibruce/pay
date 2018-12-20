<?php
/**
 * Created by PhpStorm.
 * User: dengLy
 * Date: 2016/1/4
 * Time: 14:21
 */
class sysuser_ctl_company_list extends desktop_controller {

    public function index()
    {
        $params = array(
            'title' => app::get('sysuser')->_('企业会员列表'),
            'base_filter' => array('user_type' => '1', 'verify_status' => '1'),//企业会员
            'actions' => array(
                   array(
                       'label' => app::get('sysuser')->_('导出'),
                       'submit' => '?app=sysuser&ctl=admin_export&act=view',
                       'target'=>'dialog::{title:\''.app::get('sysuser')->_('导出').'\',width:400,height:170}',
                   ),
                ),
        );
        return $this->finder('sysuser_mdl_user',$params);
    }

    public function refused()
    {
        $params = array(
            'title' => app::get('sysuser')->_('企业会员列表'),
            'base_filter' => array('user_type' => '1', 'verify_status' => '2'),//企业会员
            'actions' => array(
                array(
                    'label' => app::get('sysuser')->_('导出'),
                    'submit' => '?app=sysuser&ctl=admin_export&act=view',
                    'target'=>'dialog::{title:\''.app::get('sysuser')->_('导出').'\',width:400,height:170}',
                ),
            ),
        );
        return $this->finder('sysuser_mdl_user',$params);
    }

    public function review()
    {
        $params = array(
            'title' => app::get('sysuser')->_('企业会员列表'),
            'base_filter' => array('user_type' => '1', 'verify_status' => '0'),//企业会员
            'actions' => array(
                array(
                    'label' => app::get('sysuser')->_('导出'),
                    'submit' => '?app=sysuser&ctl=admin_export&act=view',
                    'target'=>'dialog::{title:\''.app::get('sysuser')->_('导出').'\',width:400,height:170}',
                ),
            ),
        );
        return $this->finder('sysuser_mdl_user',$params);
    }

    public function banner(){
        $pagedata['company_banner'] = app::get('sysconf')->getConf('sysconf_setting.company_banner');
        return $this->page('sysuser/company/bannersetting.html',$pagedata);
    }
}
