<?php
/**
 * Created by Sublime.
 * User: ZCY
 * Date: 16/8/1
 * Time: 14:30
 */

class sysuser_ctl_admin_companyNotice extends desktop_controller{


    public function index()
    {
        return $this->finder('sysuser_mdl_user',array(
            'title' => app::get('sysuser')->_('企业会员列表'),
            'base_filter' => array('user_type' => '1'),//商城会员
            'use_buildin_filter' => true,
            'use_buildin_delete' => false,
            'actions'=>array(
                    array(
                        'label'=>app::get('sysuser')->_('添加全站通知'),
                        'target'=>'dialog::{ title:\''.app::get('sysuser')->_('添加全站通知').'\', width:1000, height:600}',
                        'href'=>'?app=sysuser&ctl=admin_companyNotice&act=addNotice',
                    ),
                    array(
                        'label'=>app::get('sysuser')->_('会员通知类型'),
                        'target'=>'dialog::{ title:\''.app::get('sysuser')->_('会员通知类型').'\', width:400, height:200}',
                        'href'=>'?app=sysuser&ctl=admin_companyNotice&act=addNoticeType',
                    ),
                ),
        ));
    }

    //添加会员通知类型
    public function addNoticeType(){

    	// $notice = app::get('sysshop')->getConf('shopnoticetype');
        $notice = app::get('sysuser')->getConf('usernoticetype');
    	$this->contentHeaderTitle = '添加通知类型';
    	$pagedata['notice'] = $notice;
    	$pagedata['count'] = count($notice);
    	return view::make('sysuser/admin/user/addNoticeType.html',$pagedata);
    }

    //保存会员通知类型
    public function saveNoticeType(){

    	$postdata = input::get('notice');
    	foreach ($postdata as $key => $value) {

    		if($value == ''){
    			$msg = app::get('sysuser')->_('通知类型不能为空！');
    			return $this->splash('error',null,$msg);
    		}
    	}
    	// $noticetype = app::get('sysshop')->getConf('shopnoticetype');
        $noticetype = app::get('sysuser')->getConf('usernoticetype');
    	if( count($noticetype) > count($postdata)){
    		$minustype = array_diff($noticetype, $postdata);
    		$userNoticeMdl = app::get('sysuser')->model('user_notice');
    		$minusTypeList = $userNoticeMdl->getList('notice_id',array('notice_type'=>$minustype));

    		if($minusTypeList){
    			$msg = app::get('sysuser')->_('该类型下面含有通知，请先删除通知再进行操作！');
    			return $this->splash('error',null,$msg);
    		}
    	}
    	// $result = app::get('sysshop')->setConf('shopnoticetype',$postdata);
        $result = app::get('sysuser')->setConf('usernoticetype',$postdata);
    	if($result){
    		$msg = app::get('sysuser')->_('会员通知类型添加成功！');
    		return $this->splash('success',null,$msg);
    	} else{
    		$msg = app::get('sysuser')->_('会员通知类型添加失败！');
    		return $this->splash('error',null,$msg);
    	}
    }

    public function addNotice(){
    	$userId = input::get('user_id');
    	if($userId != ''){
    		$notice['user_id'] = $userId;
    		$pagedata['notice'] = $notice;
    	}
    	// $notice = app::get('sysshop')->getConf('shopnoticetype');
        $notice = app::get('sysuser')->getConf('usernoticetype');
    	$this->contentHeaderTitle = '添加全站通知';
    	$pagedata['noticetype'] = $notice;
    	$pagedata['count'] = count($notice);
        $gradeMdl=app::get('sysuser')->model('user_grade');
        $pagedata['gradeInfo']=$gradeMdl->getList('*');
    	return view::make('sysuser/admin/user/addNotice.html',$pagedata);
    }

    //企业会员通知保存
    public function saveNotice(){
        $userMdl=app::get('sysuser')->model('user');
        $gradeMdl=app::get('sysuser')->model('user_grade');

    	$params = input::get('notice');
        if ($params['notice_grade']!='所有') {

            $gradeID=$gradeMdl->getRow('grade_id',array('grade_name'=>$params['notice_grade']));
            $userInfo=$userMdl->getList('user_id',array('grade_id'=>$gradeID['grade_id'],'user_type'=>'1'));
            if ($userInfo) {
                foreach ($userInfo as $key => $value) {
                    $params['user_id'] .=$value['user_id'].',';
                } 
            }
            else{
                $params['user_id']='-1';
            }
        }
    	try{
    		app::get('sysuser')->rpcCall('user.saveNotice',$params);
    	}
    	catch(\LogicExpection $e){
    		$msg = $e->getMessage();
    		return $this->splash('error',null,$msg);
    	}
    	$url="?app=sysuser&ctl=admin_companyNotice&act=index";
    	return $this->splash('success',$url,'添加通知成功');
    }
}