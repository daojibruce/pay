<?php

class topc_ctl_thirdparty extends topc_controller{

    public $limit = 10;

    function index()
    {
        $cat_id = input::get('cat_id');
        $page = input::get('pages') ? input::get('pages') : 1;
        if (!$cat_id) {
            return redirect::action('topc_ctl_default@index');
        }
        $catInfo =  app::get('thirdparty')->model('category')->getRow('cat_name',['cat_id' => $cat_id]);
        if (!$catInfo) {
            return redirect::action('topc_ctl_default@index');
        }
        $count = app::get('thirdparty')->model('cate_rel')->count(['cat_id' => $cat_id]);
        $providerInfo = app::get('thirdparty')->model('cate_rel')->getList('provider_id',['cat_id' => $cat_id],($page-1)*$this->limit,$this->limit,'createtime desc');

        if (!$providerInfo) {
            $pagedata['invalid'] = 1;
        } else {
            $providers = app::get('thirdparty')->model('provider')->getList('*',['provider_id|in' => array_column($providerInfo,'provider_id')]);
            $pagedata['providers'] = $providers;
        }
        $pagedata['cat_name'] = $catInfo['cat_name'];
        $pagedata['pagers'] = $this->__pager($page,$count,$cat_id);

        //echo "<pre>";print_r($pagedata);exit;
        return $this->page('topc/thirdparty/index.html', $pagedata);
    }

    private function __pager($page,$count,$cat_id)
    {
        $postFilter['pages'] = time();
        $postFilter['cat_id'] = $cat_id;
        $total = ceil($count/$this->limit);
        $pagers = array(
            'link'=>url::action('topc_ctl_thirdparty@index',$postFilter),
            'current'=>$page,
            'total'=>$total,
            'token'=>time(),
        );
        return $pagers;

    }
}
