<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class toptemai_ctl_shop_dlytmpl extends toptemai_controller {

    public $limit = 10;
    /**
     * @brief 运费模板列表显示
     */
    public function index()
    {

        $postFilter = input::get();
        $page = $postFilter['pages'] ? $postFilter['pages'] : 1;
        $params = array(
            'fields'=>'template_id,name,modifie_time,status,fee_conf,valuation,is_free',
            'page_no' => intval($page),
            'page_size' => intval($this->limit),
            'user_id' => $this->userId,
        );

        $pagedata = app::get('toptemai')->rpcCall('logistics.temaitmpl.get.list',$params,'buyer');

        $pagedata['pagers'] = $this->__pager($postFilter,$page,$pagedata['total_found']);
        $this->contentHeaderTitle = app::get('toptemai')->_('快递运费模板列表');
        return $this->page('toptemai/shop/dlytmpl.html', $pagedata);
    }

    /**
     * @brief 新增模板和编辑模板页面显示
     *
     */
    public function editView()
    {
        //面包屑
        $this->runtimePath = array(
            ['url'=> url::action('toptemai_ctl_index@index'),'title' => app::get('toptemai')->_('首页')],
            ['url'=> url::action('toptemai_ctl_shop_dlytmpl@index'),'title' => app::get('toptemai')->_('快递运费模板列表')],
            ['title' => app::get('toptemai')->_('新增运费模板')],
        );
        $this->contentHeaderTitle = app::get('toptemai')->_('新增运费模板');

        $template_id = input::get('template_id');
        if( $template_id )
        {
            $params['template_id'] = $template_id;
            $params['user_id'] = $this->userId;
            $params['fields'] = "template_id,name,modifie_time,status,fee_conf,free_conf,is_free,protect,protect_rate,minprice,valuation";
            $data = app::get('toptemai')->rpcCall('logistics.temaitmpl.get',$params);
            if($data['valuation']=='1'){
                $data['byweight']['fee_conf'] = $data['fee_conf'];
                $data['byweight']['free_conf'] = $data['free_conf'];
            }
            if($data['valuation']=='2'){
                $data['bynum']['fee_conf'] = $data['fee_conf'];
                $data['bynum']['free_conf'] = $data['free_conf'];
            }
            if($data['valuation']=='3'){
                $data['bymoney']['fee_conf'] = $data['fee_conf'];
            }
            unset($data['fee_conf']);
            unset($data['free_conf']);
            $pagedata['tmplData'] = $data;
            $this->contentHeaderTitle = app::get('toptemai')->_('编辑运费模板');
        }

        return $this->page('toptemai/shop/editdlytmpl.html', $pagedata);
    }

    // 保存运费模板
    public function savetmpl()
    {
        $params = input::get();
        $params['user_id'] = $this->userId;

        if($this->isExists())
        {
            $msg = app::get('toptemai')->_("此模板名称已存在，请换一个重试");
            return $this->splash("error","",$msg,true);
        }

        try
        {
            // 运费配置参数
            if(!$params['is_free'])
            {

                if($params['valuation']=='1' && $params['fee_conf']){
                    $params['fee_conf'] = json_encode($params['fee_conf']);
                    // 免邮配置参数
                    if($params['open_freerule'] && $params['free_conf'])
                    {
                        $params['free_conf'] = json_encode($params['free_conf']);
                    }else{
                        $params['free_conf'] = '';
                    }
                }
                if($params['valuation']=='2' && $params['fee_number_conf']){
                    if(!$params['fee_number_conf'][0]['start_standard'] || !$params['fee_number_conf'][0]['add_fee'] || !$params['fee_number_conf'][0]['add_standard'] || !$params['fee_number_conf'][0]['start_fee'])
                    {
                        $msg = app::get('toptemai')->_('默认运费必填');
                        return $this->splash('error','',$msg,true);
                    }

                    $params['fee_conf'] = json_encode($params['fee_number_conf']);
                    // 免邮配置参数
                    if($params['open_freerule'] && $params['free_number_conf'])
                    {
                        $params['free_conf'] = json_encode($params['free_number_conf']);
                    }else{
                        $params['free_conf'] = '';
                    }
               }
                if($params['valuation']=='3' && $params['fee_conf']){
                    if(count($params['fee_money_conf'])<1 || $params['fee_money_conf'][0]['rules'][0]['basefee']<0 )
                    {
                        $msg = app::get('toptemai')->_('至少有一条规则必填');
                        return $this->splash('error','',$msg,true);
                    }
                    $params['fee_conf'] = json_encode($params['fee_money_conf']);
                    $params['free_conf'] = '';
                }
            }

            if( $params['template_id'] )
            {
                app::get('toptemai')->rpcCall('logistics.temaitmpl.update', $params, 'buyer'); //修改运费模板
            }
            else
            {
                app::get('toptemai')->rpcCall('logistics.temaitmpl.add', $params, 'buyer'); //添加运费模板
            }
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error','',$msg,true);
        }
        $this->sellerlog('添加/编辑快递模板。模板名称 '.$params['name']);
        $msg = app::get('toptemai')->_('保存运费模板成功');
        $url = url::action('toptemai_ctl_shop_dlytmpl@index');
        return $this->splash('success',$url,$msg,true);
    }

    /**
     * @brief ajax请求判断运费模板名称是否存在
     */
    public function isExists()
    {
        try
        {
            $template = app::get('toptemai')->rpcCall('logistics.temaitmpl.get.list',array('name'=>input::get('name'),'user_id'=>$this->userId,'fields'=>'template_id'));
            $template_id = $template['template_id'];
            if( $template_id && (!input::get('template_id') || input::get('template_id') != $template_id) )
            {
                $status = true;//已存在
            }
            else
            {
                $status = false;//不存在
            }
        }
        catch(Exception $e)
        {
            $status = false;
        }
        return $status;
    }

    // 删除运费模板
    public function remove()
    {
        $filter['template_id'] = input::get('template_id');
        $filter['user_id'] = $this->userId;
        try
        {
            app::get('toptemai')->rpcCall('logistics.temaitmpl.delete',$filter,'buyer');
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error','',$msg,true);
        }
        $this->sellerlog('删除快递模板。模板ID是 '.$filter['template_id']);
        $url = url::action('toptemai_ctl_shop_dlytmpl@index');
        return $this->splash('success',$url,$msg,true);
    }

    private function __pager($postFilter,$page,$count)
    {
        $postFilter['pages'] = time();
        $total = ceil($count/$this->limit);
        $pagers = array(
            'link'=>url::action('toptemai_ctl_shop_dlytmpl@index',$postFilter),
            'current'=>$page,
            'use_app' => 'toptemai',
            'total'=>$total,
            'token'=>time(),
        );
        return $pagers;

    }


}

