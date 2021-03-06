<?php

class toptemai_ctl_account_log extends toptemai_controller {

    public $pagesize = 10;

    // 商家操作日志列表
    public function index()
    {
        $this->contentHeaderTitle = app::get('toptemai')->_('日志管理');
        $params['shop_id'] = $this->shopId;
        if(!$nPage = input::get('pages'))
        {
            $nPage = 1;
        }
        $count = app::get('system')->model('seller_log')->count($params);
        $maxPage = ceil($count / $this->pagesize);
        if($nPage > $maxPage) $nPage = $maxPage;
        $start =  ($nPage-1) * $this->pagesize;
        $start = $start<0 ? 0 : $start;
        $pagedata['data'] = app::get('system')->model('seller_log')->getList('*', $params, $start, $this->pagesize, 'created_time DESC');
        $pagedata['count'] = $count;

        //处理翻页数据
        $filter['pages'] = time();
        $pagedata['pagers'] = array(
            'link'=>url::action('toptemai_ctl_account_log@index', $filter),
            'current'=>$nPage,
            'use_app'=>'toptemai',
            'total'=>$maxPage,
            'token'=>$filter['pages'],
        );

        return $this->page('toptemai/account/user/loglist.html', $pagedata);
    }

}

