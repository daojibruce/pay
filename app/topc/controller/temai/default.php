<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topc_ctl_temai_default extends topc_controller {

    public function __construct() {
        parent::__construct();
        $this->setLayoutFlag('temai');
        $GLOBALS['runtime']['path'][] = array ('title' => app::get('topc')->_('平台展销首页'), 'link' => kernel::base_url(1));
    }

    public function index()
    {
        //一周平台展销
        $temaiDayMdlObj = app::get('systemai')->model('temailist');
        $temaiList = $temaiDayMdlObj->getTemaiList(null);
        if(! empty($temaiList)){
            $pagedata = ['temaiList' => $temaiList];

            //最新平台展销
            $top50Week = $temaiDayMdlObj->top50ByDay();
            $pagedata['top50Week'] = $top50Week;

            //最新订单
            $topOrderList = $temaiDayMdlObj->top90OrderList();
            $pagedata['topOrderList'] = $topOrderList;
        }

        return $this->page('topc/temai/main.html', $pagedata);
    }

    protected function _getCats() {
        $result = array();
        $data = config::get('temaiconf');
        foreach ($data['cat'] as $key=>$val) {
            $result[$key] = $val;
        }
        return $result;
    }
}


