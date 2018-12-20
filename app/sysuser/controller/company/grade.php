<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2016/1/4
 * Time: 14:32
 */
class sysuser_ctl_company_grade extends desktop_controller {

    public function index()
    {
        $mdl = app::get('sysuser')->model('user_grade');
        $count = $mdl->count();
        if($count < 8)
        {
            $actions[] = array(
                'label' => '添加等级',
                'href' => '?app=sysuser&ctl=admin_grade&act=create',
                'target'=>'dialog::{title:\''.app::get('sysuser')->_('会员等级添加').'\',width:500,height:400}'
            );
        }
        return $this->finder('sysuser_mdl_user_grade',array(
            'title' => app::get('sysuser')->_('会员等级列表'),
            'actions'=>$actions,
        ));
    }
}