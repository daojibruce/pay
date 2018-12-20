<?php
class toptemai_ctl_shop_dlycorp extends toptemai_controller{
    public $limit = 10;
    public function index()
    {
        $postFilter = input::get();
        $page = $postFilter['pages'] ? intval($postFilter['pages']) : 1;
        //获取我签约的物流
        $dlycorp = app::get('toptemai')->rpcCall('temai.dlycorp.getlist',['user_id'=>$this->userId]);
        $dlycorp = array_bind_key($dlycorp['list'],'corp_id');

        //获取全部物流
        $params = array(
            'fields'=>'corp_id,corp_code,corp_name',
            'page_no' => intval($page),
            'page_size' => intval($this->limit),
        );
        $corpData = app::get('toptemai')->rpcCall('logistics.dlycorp.get.list',$params);
        $data = $corpData['data'];
        foreach( $data as $key=>$value)
        {
            if($dlycorp[$value['corp_id']])
            {
                $data[$key]['is_open'] = true;
            }
        }
        $pagedata['page'] = $page;
        $pagedata['corpData'] = $data;
        $pagedata['count'] = $corpData['total_found'];
        $pagedata['pagers'] = $this->__pager($postFilter,$page,$corpData['total_found']);

        $this->contentHeaderTitle = app::get('toptemai')->_('物流公司');
        return $this->page('toptemai/shop/dlycorp.html', $pagedata);
    }

    /**
     * @brief 签约物流公司，最多5个
     *
     * @return
     */
    public function signDlycorp()
    {
        $postdata['corp_id'] = input::get('corp_id');
        $postdata['user_id'] = $this->userId;

        try{
            app::get('toptemai')->rpcCall('temai.dlycorp.save',$postdata);
        }catch(Exception $e){
            $msg = $e->getMessage();
            return $this->splash('error','',$msg,true);
        }
        $this->sellerlog('开启物流公司');
        $msg = app::get('toptemai')->_('保存成功');
        $url = url::action('toptemai_ctl_shop_dlycorp@index',['pages'=>input::get('pages')]);
        return $this->splash('success',$url,$msg,true);
    }

    public function cancelDlycorp()
    {
        $postdata = input::get();
        $postdata['user_id'] = $this->userId;
        try{
            app::get('toptemai')->rpcCall('temai.dlycorp.remove',$postdata);
        }catch(Exception $e){
            $msg = $e->getMessage();
            return $this->splash('error','',$msg,true);
        }
        $this->sellerlog('取消物流公司');
        $msg = app::get('toptemai')->_('保存成功');
        $url = url::action('toptemai_ctl_shop_dlycorp@index');
        return $this->splash('success',$url,$msg,true);
    }

    private function __pager($postFilter,$page,$count)
    {
        $postFilter['pages'] = time();
        $total = ceil($count/$this->limit);
        $pagers = array(
            'link'=>url::action('toptemai_ctl_shop_dlycorp@index',$postFilter),
            'current'=>$page,
            'use_app' => 'toptemai',
            'total'=>$total,
            'token'=>time(),
        );
        return $pagers;

    }

}
