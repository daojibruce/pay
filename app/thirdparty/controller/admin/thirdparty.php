<?php

class thirdparty_ctl_admin_thirdparty extends desktop_controller {

    public $workground = 'thirdparty.wrokground.thirdparty';

    public function index()
    {
        return $this->finder(
            'thirdparty_mdl_provider',
            array(
                'title'=>app::get('thirdparty')->_('第三方服务商列表'),
                'use_buildin_delete' => false,
                'actions' => array(
                    array(
                        'label'=>app::get('thirdparty')->_('添加服务商'),
                        'href'=>'?app=thirdparty&ctl=admin_thirdparty&act=providerAdd',
                        'target'=>'dialog::{title:\''.app::get('thirdparty')->_('添加服务商').'\',  width:500,height:320}',
                    ),
                ),
            )
        );
    }

    public function providerAdd()
    {
        $cates = app::get('thirdparty')->model('category')->getList('*');
        $pagedata['cates'] = $cates;
        return $this->page('thirdparty/providerAdd.html', $pagedata);
    }

    public function providerEdit($provider_id)
    {
        $cates = app::get('thirdparty')->model('category')->getList('*');
        $providerMdl = app::get('thirdparty')->model('provider');
        $provider = $providerMdl->getRow('*', ['provider_id' => $provider_id]);

        $db = app::get('sysproofing')->database();
        $catInfo = $db->executeQuery('SELECT c.cat_id FROM thirdparty_category as c LEFT JOIN thirdparty_cate_rel as p ON c.cat_id=p.cat_id WHERE p.provider_id='.$provider_id)->fetchAll();
        $catList = array_column($catInfo,'cat_id');
        foreach ($cates as $key => $cate) {
            if (in_array($cate['cat_id'], $catList)) {
                $cates[$key]['is_checked'] = 1;
            }
        }

        $pagedata['cates'] = $cates;
        $pagedata['provider'] = $provider;
        return $this->page('thirdparty/providerAdd.html', $pagedata);
    }

    public function providerDelete($provider_id)
    {
        $this->begin('?app=thirdparty&ctl=admin_thirdparty&act=index');
        app::get('thirdparty')->model('cate_rel')->delete(['provider_id' => $provider_id]);
        app::get('thirdparty')->model('provider')->delete(['provider_id' => $provider_id]);
        $this->end();

    }

    public function providerSave()
    {
        $this->begin();
        $postData = utils::_filter_input(input::get());
        $validator = validator::make(
            ['name' => $postData['provider_name'] , 'desc' => $postData['desc'], 'addr' => $postData['addr'], 'contact' => $postData['contact'], 'mobile' => $postData['mobile']],
            ['name' => 'required' , 'desc' => 'required', 'addr' => 'required', 'contact' => 'required', 'mobile' => 'required|mobile'],
            ['loginName' => '请输入服务商名称!' , 'desc' => '请输入服务商描述!', 'addr' => '请输入服务商地址!', 'contact' => '请输入联系人!', 'mobile' => '请输入手机号!|请输入正确的手机号']
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                $this->end(false, $error[0]);
            }
        }
        if (!$postData['cate']) {
            $this->end(false, app::get('thirdparty')->_('请选择服务类型！'));
        }
        $providerMdl = app::get('thirdparty')->model('provider');

        $result = $providerMdl->getRow('provider_id', ['provider_name' => $postData['provider_name']]);
        if ($postData['provider_id']) {
            if ($result && $result['provider_id'] != $postData['provider_id']) {
                $this->end(false, app::get('thirdparty')->_('该服务商名称已存在！'));
            }
        } else {
            if ($result) {
                $this->end(false, app::get('thirdparty')->_('该服务商名称已存在！'));
            }
        }

        $res = $providerMdl->saveOne($postData);
        if ($res) {
            $this->end(true);
        } else {
            $this->end(false, app::get('thirdparty')->_('保存失败！'));
        }
    }

    public function category()
    {
        return $this->finder(
            'thirdparty_mdl_category',
            array(
                'title'=>app::get('thirdparty')->_('服务类型列表'),
                'use_buildin_delete' => false,
                'actions' => array(
                    array(
                        'label'=>app::get('thirdparty')->_('添加服务类型'),
                        'href'=>'?app=thirdparty&ctl=admin_thirdparty&act=categoryAdd',
                        'target'=>'dialog::{title:\''.app::get('thirdparty')->_('添加服务类型').'\',  width:500,height:320}',
                    ),
                ),
            )
        );
    }

    public function categoryAdd()
    {
        return $this->page('thirdparty/categoryAdd.html', $pagedata);
    }

    public function categoryEdit($cat_id)
    {
        $catMdl = app::get('thirdparty')->model('category');
        $result = $catMdl->getRow('cat_id, cat_name,sort', ['cat_id' => $cat_id]);
        $pagedata['cat_id'] = $result['cat_id'];
        $pagedata['cat_name'] = $result['cat_name'];
        $pagedata['sort'] = $result['sort'];
        return $this->page('thirdparty/categoryAdd.html', $pagedata);
    }

    public function categoryDelete($cat_id)
    {
        $this->begin('?app=thirdparty&ctl=admin_thirdparty&act=category');
        $catMdl = app::get('thirdparty')->model('category');
        $providers = app::get('thirdparty')->model('cate_rel')->getList('provider_id',['cat_id' => $cat_id]);
        if ($providers) {
            $this->end(false, app::get('thirdparty')->_('该服务类型下存在服务商，无法删除！'));
        }
        $result = $catMdl->delete(['cat_id' => $cat_id]);
        if ($result) {
            $this->end();
        } else {
            $this->end(false, app::get('thirdparty')->_('删除失败！'));
        }
    }

    public function categorySave()
    {
        $this->begin();
        $catMdl = app::get('thirdparty')->model('category');
        $cat_name = $_POST['cat_name'];
        if(!trim($cat_name)){
            $this->end(false, app::get('thirdparty')->_('服务类型不能为空'));
        }

        $data = array(
            'cat_name' => trim($cat_name),
        );

        $result = $catMdl->getRow('cat_id', ['cat_name' => $cat_name]);
        if ($_POST['cat_id']) {
            if ($result && $result['cat_id'] != $_POST['cat_id']) {
                $this->end(false, app::get('thirdparty')->_('该服务类型已存在！'));
            }
            $data['cat_id'] = $_POST['cat_id'];
        } else {
            if ($result) {
                $this->end(false, app::get('thirdparty')->_('该服务类型已存在！'));
            }
        }

        if (trim($_POST['sort'])) {
            if (is_numeric(trim($_POST['sort'])) && trim($_POST['sort']) >= 0) {
                $data['sort'] = trim($_POST['sort']);
            } else {
                $this->end(false, app::get('thirdparty')->_('请输入不小于0的整数！'));
            }
        } else {
            $this->end(false, app::get('thirdparty')->_('请填写排序值'));
        }

        $res = $catMdl->save($data);
        if ($res) {
            $this->adminlog("添加服务类型", $cat_name ? 1 : 0);
            $this->end($cat_name);
        } else {
            $this->end(false, app::get('thirdparty')->_('保存失败！'));
        }
    }
}