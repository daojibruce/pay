<?php
class toptemai_ctl_shop_applycat extends toptemai_controller{

    public $limit = 10;
    public function index()
    {
        $shopId = $this->shopId;
        $postFilter = input::get();
        $page = $postFilter['pages'] ? $postFilter['pages'] : 1;

        $shopdata = app::get('toptemai')->rpcCall('shop.get',array('shop_id'=>$shopId,'fields'=>'shop_type'));
        $pagedata['shop_type'] = $shopdata['shop_type'];

        $params = array(
            'shop_id'=>$shopId,
            'page_no' => intval($page),
            'page_size' => intval($this->limit),
        );

        $applycat = app::get('toptemai')->rpcCall('shop.apply.cat.getlist',$params);

        $pagedata['applycat'] = $applycat['list'] ;
        $pagedata['count'] = $applycat['count'] ;
        $pagedata['pagers'] = $this->__pager($postFilter,$page,$applycat['count']);
        $this->contentHeaderTitle = app::get('toptemai')->_('类目权限申请');
        return $this->page('toptemai/shop/applycat/list.html', $pagedata);
    }

    public function goApplyCat()
    {
        //获取所有类目列表
        $catList = app::get('toptemai')->rpcCall('category.cat.get.info',['parent_id'=>0,'level'=>1]);
        //获取店铺签约的类目
        $shopRelCat = app::get('toptemai')->rpcCall('shop.authorize.catbrandids.get',['shop_id'=>$this->shopId]);
        $relCat = $shopRelCat[$this->shopId]['cat'];
        foreach($relCat as $id)
        {
            unset($catList[$id]);
        }
        $pagedata['catList'] = $catList;
        return view::make('toptemai/shop/applycat/goapplycat.html', $pagedata);
    }

    // 申请类目权限
    public function doApplyCat()
    {
        $postdata = input::get();
        $postdata['cat_id'] = intval($postdata['cat_id']);
        $postdata['shop_id'] = $this->shopId;
        try{
            app::get('toptemai')->rpcCall('shop.apply.cat.save',$postdata);
        }catch(Exception $e){
            $msg = $e->getMessage();
            return $this->splash('error','',$msg,true);
        }
        $this->sellerlog('申请类目权限。申请的类目ID是 '.$postdata['cat_id']);
        $msg = app::get('toptemai')->_('保存成功');
        $url = url::action('toptemai_ctl_shop_applycat@index');
        return $this->splash('success',$url,$msg,true);
    }

    public function removeApplyCat()
    {
        $postdata['apply_id'] = intval(input::get('apply_id'));
        $postdata['shop_id'] = intval($this->shopId);
        try{
            app::get('toptemai')->rpcCall('shop.apply.cat.remove',$postdata);
        }catch(Exception $e){
            $msg = $e->getMessage();
            return $this->splash('error','',$msg,true);
        }
        $this->sellerlog('删除审核未通过的申请类目权限。申请ID是 '.$postdata['apply_id']);
        $msg = app::get('toptemai')->_('保存成功');
        $url = url::action('toptemai_ctl_shop_applycat@index');
        return $this->splash('success',$url,$msg,true);
    }

    public function getApplyCat()
    {
        $catId = intval(input::get('cat_id'));
        if($catId)
        {
            $applycat = app::get('toptemai')->rpcCall('shop.apply.cat.get',['shop_id'=>$this->shopId,'cat_id'=>$catId]);
            if($applycat)
            {
                $msg = app::get('toptemai')->_("当前类目已经被申请");
                return $this->splash('error',"",$msg,true);
            }
        }
        return $this->splash('success',"",$msg,true);
    }

    private function __pager($postFilter,$page,$count)
    {
        $postFilter['pages'] = time();
        $total = ceil($count/$this->limit);
        $pagers = array(
            'link'=>url::action('toptemai_ctl_shop_applycat@index',$postFilter),
            'current'=>$page,
            'use_app' => 'toptemai',
            'total'=>$total,
            'token'=>time(),
        );
        return $pagers;
    }
}
