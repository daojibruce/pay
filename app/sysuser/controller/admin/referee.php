<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class sysuser_ctl_admin_referee extends desktop_controller{

    public function index()
    {

        return $this->finder('sysuser_mdl_referee',array(
            'title' => app::get('sysuser')->_('推荐来源列表'),
            'use_buildin_delete' => false,
            'base_filter' => array(
                'status' => '1',
            ),
            'actions'=>array(
                array(
                    'label'=>app::get('syscategory')->_('添加来源'),
                    'href'=>'?app=sysuser&ctl=admin_referee&act=edit','target'=>'dialog::{title:\''.app::get('syscategory')->_('添加来源').'\',width:500,height:350}'
                ),
            )
        ));
    }

    public function edit($referee_id = 0)
    {
        if($referee_id > 0)
        {
            $referee = app::get('sysuser')->model('referee')->getRow('*', ['referee_id'=>$referee_id]);
            $pagedata['referee'] = $referee;
        }

        return $this->page('sysuser/admin/referee/edit.html', $pagedata);
    }

    public function save()
    {
        $this->begin();
        try{
            if (input::get('referee_id')) $referee['referee_id'] = input::get('referee_id');
            if (!trim(input::get('referee_name'))) {
                return $this->end(false, '推荐来源名称不能为空');
            } else {
                $referee['referee_name']  = trim(input::get('referee_name'));
            }
            $refereeMdl = app::get('sysuser')->model('referee');
            $refereeInfo = $refereeMdl->getRow('referee_id',['referee_name' =>$referee['referee_name']]);
            if ($refereeInfo && $refereeInfo['referee_id'] != $referee['referee_id']) {
                return $this->end(false, '该名称已存在！');
            }
            if (!$referee['referee_id']) $referee['createtime'] = time();
            $referee['modified_time'] = time();
            $referee['account_id'] = $_SESSION['account']['shopadmin']['id'];
            $refereeMdl->save($referee);
        }catch(Exception $e){
            return $this->end(false, $e->getMessage());
        }
        return $this->end(true, app::get('sysuser')->_('保存成功'));
    }

    public function doDelete($referee_id)
    {
        $refereeMdl = app::get('sysuser')->model('referee');
        $userMdl = app::get('sysuser')->model('user');
        if ($userMdl->getRow('user_id',['referee_id' => $referee_id])) {
            return $this->end(false, '该来源存在推荐的会员,无法删除');
        } else {
            try{
                $refereeMdl->delete(['referee_id' => $referee_id]);
                return $this->end(true, app::get('sysuser')->_('删除成功'));
            } catch (Exception $e) {
                return $this->end(false, $e->getMessage());
            }
        }
    }

}

