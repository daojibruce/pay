<?php

/**
 * @brief 商家商品管理
 */
class topc_ctl_temai_list extends topc_controller {

    public function __construct(&$app) {
        parent::__construct();
        $this->setLayoutFlag('temai');
    }

    public function index()
    {
        $temaiNullMsg = '暂无平台展销商品!';
        $filter = input::get();
        if(!$filter['pages'])
        {
            $filter['pages'] = 1;
        }
        $pagedata['retime'] = 0 ;
        $currentDayKey = date("Ymd");
        if($filter['day_key'] > $currentDayKey){
            $pagedata['time_msg'] = '尚未开始';
        }else if($filter['day_key'] < $currentDayKey){
            $pagedata['time_msg'] = '已经结束';
        }else{
            $pagedata['retime'] = date('Y/m/d 23:59:59');
        }

        $current = $filter['pages'] ? $filter['pages'] : 1;
        $pageSize = 20;
        $params = array(
            'page_no' => intval($current),
            'page_size' => intval($pageSize),
            'day_key' => $filter['day_key']
        );

        $temaiDayMdlObj = app::get('systemai')->model('temailist');
        $temaiList = $temaiDayMdlObj->temaiListPage($params);
        if(empty($temaiList['temailist'])){
            return $temaiNullMsg;
        }
        $count = $temaiList['count'];
        if($count>0) $total = ceil($count/$pageSize);
        $pagedata['pagers'] = array(
            'link'=>url::action('topc_ctl_temai_list@index',$filter),
            'current'=>$current,
            'total'=>$total,
            'token'=>$filter['pages'],
        );
        $pagedata['count'] = $count;
        $pagedata['temailist'] = $temaiList['temailist'];
        $userId = userAuth::id();
        if (!$userId) {
            return redirect::action('topc_ctl_passport@signin');
        }

        return $this->page('topc/temai/list.html', $pagedata);
    }

    public function index2() {
        $pagedata['screenlist'] = app::get('topc')->rpcCall('temai.search.searchItems.screen');
        // 默认限时抢购中的
        foreach ($pagedata['screenlist'] as $key=>$val) {
            $val['status'] == 'online' && $screen = $key;
        }

        $pagedata['screen'] = input::get('screen', $screen);
        // 限时平台展销数据
        $rpcParams = array (
            'type' => 'timeLimit',
            'page_size' => 20,
            'page_no' => 1,
            'screen' => $pagedata['screen']
        );
        $pagedata['itemlist'] = app::get('topc')->rpcCall('temai.search.searchItems', $rpcParams);
        $pagedata['retime'] = date('Y/m/d 23:59:59');
        return $this->page('topc/temai/list.html', $pagedata);
    }

    public function num_list() {
        // 限量平台展销数据
        $rpcParams = array (
            'type' => 'numLimit',
            'page_size' => 20,
            'page_no' => 1
        );
        $pagedata['itemlist'] = app::get('topc')->rpcCall('temai.search.searchItems', $rpcParams);

        return $this->page('topc/temai/num_list.html', $pagedata);
    }
}



