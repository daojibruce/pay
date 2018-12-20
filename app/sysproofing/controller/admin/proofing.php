<?php

class sysproofing_ctl_admin_proofing extends desktop_controller {

    public $workground = 'sysuser.wrokground.shoptype';

    public function index()
    {
        return $this->finder(
            'sysproofing_mdl_provider',
            array(
                'title'=>app::get('sysproofing')->_('服务商列表'),
                'base_filter' => array(
                    'enabled' => '1',
                    'status' => '0'
                ),
                'use_buildin_delete' => false,
            )
        );
    }

    public function review()
    {
        return $this->finder(
            'sysproofing_mdl_provider',
            array(
                'title'=>app::get('sysproofing')->_('待审核服务商列表'),
                'base_filter' => array(
                    'enabled' => '0',
                    'status' => '0'
                ),
                'use_buildin_delete' => false,
            )
        );
    }

    public function startReview($provider_id)
    {
        $proMdl = app::get('sysproofing')->model('provider');
        $proInfo = $proMdl->getRow('*', ['provider_id' => $provider_id, 'enabled' => '0']);
        $proInfo['sb_img'] = unserialize($proInfo['sb_img']);
        $proInfo['yp_img'] = unserialize($proInfo['yp_img']);
        $db = app::get('sysproofing')->database();
        $cats = $db->executeQuery('SELECT c.cat_name,c.cat_id FROM sysproofing_category as c LEFT JOIN sysproofing_provider_cat as p ON c.cat_id=p.cat_id WHERE p.provider_id='.$provider_id)->fetchAll();
        $proInfo['cats'] = $cats;
        $userInfo = app::get('sysuser')->model('account')->getRow('login_account',['user_id' => $proInfo['user_id']]);
        $proInfo['user_name'] = $userInfo['login_account'];
        $pagedata['provider'] = $proInfo;
        //echo "<pre>";print_r($pagedata);exit;
        return $this->page('sysproofing/admin/review.html', $pagedata);
    }

    public function providerDetail($provider_id)
    {
        $proMdl = app::get('sysproofing')->model('provider');
        $proInfo = $proMdl->getRow('*', ['provider_id' => $provider_id, 'enabled' => '1']);
        $proInfo['sb_img'] = unserialize($proInfo['sb_img']);
        $proInfo['yp_img'] = unserialize($proInfo['yp_img']);
        $db = app::get('sysproofing')->database();
        $cats = $db->executeQuery('SELECT c.cat_name,c.cat_id FROM sysproofing_category as c LEFT JOIN sysproofing_provider_cat as p ON c.cat_id=p.cat_id WHERE p.provider_id='.$provider_id)->fetchAll();
        $proInfo['cats'] = $cats;
        $userInfo = app::get('sysuser')->model('account')->getRow('login_account',['user_id' => $proInfo['user_id']]);
        $proInfo['user_name'] = $userInfo['login_account'];
        $pagedata['provider'] = $proInfo;
        //echo "<pre>";print_r($pagedata);exit;
        return $this->page('sysproofing/admin/detail.html', $pagedata);
    }

    public function doReview()
    {
        $this->begin('?app=sysproofing&ctl=admin_proofing&act=startReview');
        $provider_id = input::get('provider_id');
        $proMdl = app::get('sysproofing')->model('provider');
        $params = array(
            'provider_id' => $provider_id,
            'enabled' => '1',
            'modified_time' => time()
        );
        $res = $proMdl->save($params);
        if ($res) {
			$proInfo = $proMdl->getRow('user_id', ['provider_id,provider_mobile' => $provider_id]);
			userAuth::setUserRoles($proInfo['user_id'], SYS_USER_ROLE_SERVICE);
			//发送短信通知
            logger::info('服务撮合审核信息发送开始：');
            try {
                if($proInfo['provider_mobile']){
                    $content['status']="agree";
                    messenger::sendSms($proInfo['provider_mobile'],'proofing-apply',$content);
                }
            }catch(Exception $e) {
                logger::info('sendsms error:' . var_export($e->getMessage(), 1));
            }
            logger::info('服务撮合审核信息发送结束');
            $this->end(true);
        }
        $this->end(false, app::get('sysproofing')->_('审核失败！'));
    }

    public function toReview()
    {
        $this->begin('?app=sysproofing&ctl=admin_proofing&act=startReview');
        $provider_id = input::get('provider_id');
        $reason = input::get('reject_reason');
        if (!trim($reason)) {
            $this->end(false,app::get('sysproofing')->_('理由不能为空！'));
        }
        $proMdl = app::get('sysproofing')->model('provider');
        $params = array(
            'provider_id' => $provider_id,
            'status' => '1',
            'reason' => $reason,
            'modified_time' => time()
        );
        $res = $proMdl->save($params);
        if ($res) {
            $this->end(true);
        }
        $this->end(false, app::get('sysproofing')->_('保存失败！'));
    }

    public function cancelProvider($provider_id)
    {
        $this->begin('?app=sysproofing&ctl=admin_proofing&act=review');
        $proMdl = app::get('sysproofing')->model('provider');
        $params = array(
            'provider_id' => $provider_id,
            'enabled' => '0',
        );
        $res = $proMdl->save($params);
        if ($res) {
            $this->end();
        } else {
            $this->end(false, app::get('sysproofing')->_('取消失败！'));
        }
    }

    public function requirement()
    {
        return $this->finder(
            'sysproofing_mdl_requirement',
            array(
                'title'=>app::get('sysproofing')->_('撮合需求列表'),
                'use_buildin_delete' => false,
            )
        );
    }

    public function category()
    {
        return $this->finder(
            'sysproofing_mdl_category',
            array(
                'title'=>app::get('sysproofing')->_('服务类型列表'),
                'use_buildin_delete' => false,
                'actions' => array(
                    array(
                        'label'=>app::get('sysproofing')->_('添加服务类型'),
                        'href'=>'?app=sysproofing&ctl=admin_proofing&act=categoryAdd',
                        'target'=>'dialog::{title:\''.app::get('sysproofing')->_('添加服务类型').'\',  width:500,height:320}',
                    ),
                ),
            )
        );
    }

    public function categoryAdd()
    {
        return $this->page('sysproofing/admin/categoryAdd.html', $pagedata);
    }

    public function categoryEdit($cat_id)
    {
        $catMdl = app::get('sysproofing')->model('category');
        $result = $catMdl->getRow('cat_id, cat_name,sort', ['cat_id' => $cat_id]);
        $pagedata['cat_id'] = $result['cat_id'];
        $pagedata['cat_name'] = $result['cat_name'];
        $pagedata['sort'] = $result['sort'];
        return $this->page('sysproofing/admin/categoryAdd.html', $pagedata);
    }

    public function categoryDelete($cat_id)
    {
        $this->begin('?app=sysproofing&ctl=admin_proofing&act=category');
        $catMdl = app::get('sysproofing')->model('category');
        $providers = app::get('sysproofing')->model('provider_cat')->getList('provider_id',['cat_id' => $cat_id]);
        if ($providers) {
            $this->end(false, app::get('sysproofing')->_('该服务类型下存在服务商，无法删除！'));
        }
        $result = $catMdl->delete(['cat_id' => $cat_id]);
        if ($result) {
            $this->end();
        } else {
            $this->end(false, app::get('sysproofing')->_('删除失败！'));
        }
    }

    public function categorySave()
    {
        $this->begin();
        $catMdl = app::get('sysproofing')->model('category');
        $cat_name = $_POST['cat_name'];
        if(!trim($cat_name)){
            $this->end(false, app::get('sysproofing')->_('服务类型不能为空'));
        }

        $data = array(
            'cat_name' => trim($cat_name),
        );

        $result = $catMdl->getRow('cat_id', ['cat_name' => $cat_name]);
        if ($_POST['cat_id']) {
            if ($result && $result['cat_id'] != $_POST['cat_id']) {
                $this->end(false, app::get('sysproofing')->_('该服务类型已存在！'));
            }
            $data['cat_id'] = $_POST['cat_id'];

        } else {
            if ($result) {
                $this->end(false, app::get('sysproofing')->_('该服务类型已存在！'));
            }
        }

        if (trim($_POST['sort'])) {
            if (is_numeric(trim($_POST['sort'])) && trim($_POST['sort']) >= 0) {
                $data['sort'] = trim($_POST['sort']);
            } else {
                $this->end(false, app::get('sysproofing')->_('请输入不小于0的整数！'));
            }
        } else {
            $this->end(false, app::get('sysproofing')->_('请填写排序值'));
        }

        $res = $catMdl->save($data);
        if ($res) {
            $this->adminlog("添加服务类型", $cat_name ? 1 : 0);
            $this->end($cat_name);
        } else {
            $this->end(false, app::get('sysproofing')->_('保存失败！'));
        }
    }

    //新增服务类型
    public function newCategoryApply()
    {
        return $this->finder(
            'sysproofing_mdl_update_cat',
            array(
                'title'=>app::get('sysproofing')->_('待审核服务商列表'),
                'base_filter' => array(
                    'status' => '0'
                ),
                'use_buildin_delete' => false,
            )
        );
    }

    public function newCategoryReview($provider_id)
    {
        $providerInfo = app::get('sysproofing')->model('provider')->getRow('provider_id,provider_name',['provider_id' => $provider_id]);
        $applyInfo = app::get('sysproofing')->model('update_cat')->getRow('*',['provider_id' => $provider_id]);
        $catInfo = app::get('sysproofing')->model('category')->getList('cat_id,cat_name',['cat_id|in' => explode(',',$applyInfo['cat_id'])]);
        $pagedata['provider'] = $providerInfo;
        $applyInfo['cat_id'] = $catInfo;
        $pagedata['apply'] = $applyInfo;
        return $this->page('sysproofing/admin/newCategoryAdd.html', $pagedata);
    }

    public function newCategorySave()
    {
        $this->begin();
        $post = input::get();
        $applyMdl = app::get('sysproofing')->model('update_cat');
        $relMdl = app::get('sysproofing')->model('provider_cat');
        $apply_id = $post['apply_id'];
        $applyInfo = $applyMdl->getRow('*',['apply_id' => $apply_id]);

        $db = app::get('sysproofing')->database();
        try{
            if ($post['is_reject'] == '1') {
                $data = ['status' => 1,'apply_id' => $apply_id];
                if ($applyMdl->save($data)) {
                    $cats = explode(',',$applyInfo['cat_id']);
                    foreach ($cats as $cat) {
                        $cdata = ['cat_id' => $cat,'provider_id' => $applyInfo['provider_id']];
                        if ($relMdl->save($cdata)) {
                            $this->end(true, app::get('sysproofing')->_('保存成功！'));
                        }
                    }
                }
            } else {
                if (!trim($post['reject_reason'])) {
                    $this->end(false, app::get('sysproofing')->_('拒绝原因不能为空！'));
                }
                $data = ['status' => 2,'apply_id' => $apply_id,'reject_reason' => $post['reject_reason']];
                $applyMdl->save($data);
                $this->end(true, app::get('sysproofing')->_('保存成功！'));
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
}