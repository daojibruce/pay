<?php
class sysuser_ctl_admin_creditconfig extends desktop_controller{

    public function index()
    {
        return $this->finder('sysuser_mdl_user_credit_config',array(
            'title' => app::get('sysuser')->_('信用基础分配置项'),
            'use_buildin_delete'=>true,
			'actions' => [
				[
					'label' => '添加配置项',
					'href' => '?app=sysuser&ctl=admin_creditconfig&act=add',
					'target'=>'dialog::{title:\''.app::get('sysuser')->_('添加基础积分配置项').'\',width:500,height:249}'
				]
			],
        ));
    }

    public function add($id)
    {
		$pagedata = [];
		$objMdlCreditConfig = app::get('sysuser')->model('user_credit_config');
		if($id) $pagedata['credit'] = $objMdlCreditConfig->getRow('*',array('id'=>$id));
        return view::make('sysuser/admin/user/credit/addconfig.html',$pagedata);
    }

    public function save()
    {
        $postdata = input::get('credit');
        $db = app::get('sysuser')->database();
        $db->beginTransaction();
        try
        {
			if(!$postdata['id']) {
				$postdata['addtime'] = time();
			}
            app::get('sysuser')->model('user_credit_config')->save($postdata);
            $this->adminlog("添加、编辑会员基础积分项[{$postdata['name']}]", 1);
            $db->commit();
        }
        catch(Exception $e)
        {
            $this->adminlog("添加、编辑会员等级[{$postdata['name']}]", 0);
            $msg = $e->getMessage();
            $db->rollback();
            return $this->splash('error',null,$msg);
        }
        return $this->splash('success',null,"会员等级保存成功");
    }
}
