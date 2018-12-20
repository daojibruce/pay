<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class toptemai_controller extends base_routing_controller
{

    /**
     * 页面不需要menu
     */
    public $userId ;
    public $shopId ;
    public $nomenu = false;
    public $temaiCategory;

    public function __construct(&$app)
    {
        kernel::single('base_session')->start();
        if(!$this->action) $this->action = 'index';
        $this->action_view = $this->action.".html";
        // 检测是否登录
        if( !userAuth::check() )
        {
            redirect::action('topc_ctl_passport@signin')->send();exit;
        }
        $this->limit = 20;

        $this->passport = kernel::single('topc_passport');
        #$this->setLayoutFlag('temai');
        $this->userId = userAuth::id();
        $rs = userAuth::getUserRoles($this->userId );
        if(! isset($rs['roles'][SYS_USER_ROLE_SPECICAL]['name'])){
            redirect::action("topc_ctl_member@temaiapply")->send();exit;
        }

        //尝试获取某平台展销商对应商家
        $this->getShopId();

        $this->temaiCategory = ['time_limit' => '限时','amount_limit' => '限量','new_product' => '新品'];
    }

    public function getShopId()
    {
        if(empty($this->shopId) && $this->userId){
            $shopSellerMdl = app::get('sysshop')->model("shop_rel_seller");
            $filter = array('seller_id' => $this->userId);
            $shopRow = $shopSellerMdl->getRow("shop_id" , $filter);
            $this->shopId = isset($shopRow['shop_id']) ? $shopRow['shop_id'] : 0;
        }

        return $this->shopId;
    }

    /**
     * @brief 检查是否登录
     *
     * @return bool
     */
    public function checklogin()
    {
        if($this->userId) return true;

        return false;
    }

    /**
     * @brief 错误或者成功输出
     *
     * @param string $status
     * @param stirng $url
     * @param string $msg
     * @param string $method
     * @param array $params
     *
     * @return string
     */
    public function splash($status='success',$url=null,$msg=null,$ajax=true){
        $status = ($status == 'failed') ? 'error' : $status;
        //如果需要返回则ajax
        if($ajax==true||request::ajax()){
            return response::json(array(
                $status => true,
                'message'=>$msg,
                'redirect' => $url,
            ));
        }

        if($url && !$msg){//如果有url地址但是没有信息输出则直接跳转
            return redirect::to($url);
        }
    }

    public function isValidMsg($status)
    {
        $status = ($status == 'true') ? 'true' : 'false';
        $res['valid'] = $status;
        return response::json($res);
    }

    /**
     * @brief 商家中心页面加载，默认包含商家中心头、尾、导航、和左边栏
     *
     * @param string $view  html路径
     * @param stirng $app   html路径所在app
     *
     * @return html
     */
    public function page($view, $pagedata = array())
    {
        $userData = userAuth::getLoginName();
        $this->shopId = $this->getShopId();

        $toptemaiPageParams['userinfo'] = $userData;
        $pagedata['shopId'] = $this->shopId;
        $toptemaiPageParams['path'] = $this->runtimePath;//设置面包屑

        if( $this->contentHeaderTitle )
        {
            $toptemaiPageParams['contentTitle'] = $this->contentHeaderTitle;
        }

        //当前页面调用的action
        $toptemaiPageParams['currentActionName']= route::currentActionName();

        $menuArr = $this->__getMenu();
        if( $menuArr && !$this->nomenu )
        {
            $toptemaiPageParams['navbar'] = $menuArr['navbar'];
            $toptemaiPageParams['sidebar'] = $menuArr['sidebar'];
            $toptemaiPageParams['allMenu'] = $menuArr['all'];
        }
        //获取logo
        $logo = app::get('site')->getConf('site.logo');
        $pagedata['logo'] = $logo;
        //echo '<pre>';print_r($logo);exit();
        $toptemaiPageParams['view'] = $view;

        $pagedata['toptemai'] = $toptemaiPageParams;

        //$pagedata['icon'] =  kernel::base_url(1).'/statics/shop/favicon.ico';
        $pagedata['icon'] =  app::get('toptemai')->res_url.'/favicon.ico';

        if( !$this->tmplName )
        {
            $this->tmplName = 'toptemai/tmpl/page.html';
        }
        return view::make($this->tmplName, $pagedata);
    }

    public function set_tmpl($tmpl)
    {
        $tmplName = 'toptemai/tmpl/'.$tmpl.'.html';
        $this->tmplName = $tmplName;
    }

    /**
     * @brief 获取到商家中心的导航菜单和左边栏菜单
     *
     * @return array $res
     */
    private function __getMenu()
    {
        $defaultActionName = route::currentActionName();
        $shopMenu = config::get('temaimenu');

        $shortcutMenuAction = $this->getShortcutMenu();
        $sidebar['commonUser']['label'] = '常用菜单';
        $sidebar['commonUser']['shortcutMenu'] = true;
        $sidebar['commonUser']['active'] = true; //是否展开
        $sidebar['commonUser']['icon'] = 'glyphicon glyphicon-heart';
        //$sidebar['commonUser']['menu'] = $commonUserMenu;
        $subdomainSetting = config::get('app.subdomain_enabled');
        $trafficsetting = config::get('stat.disabled');

        foreach( (array)$shopMenu as $menu => $row )
        {
            if( $row['display'] === false ) continue;

            foreach( (array)$row['menu'] as $k=>$params )
            {
                //编辑常用菜单使用
                if( $params['display'] !== false )
                {
                    $allMenu[$menu]['label'] = $row['label'];
                    if( in_array($params['action'], $shortcutMenuAction) )
                    {
                        $sidebar['commonUser']['menu'][] =  $params;
                        $params['isShortcutMenu'] = true;
                    }

                    $allMenu[$menu]['menu'][] =  $params;
                }

                if($row['shopIndex'] || $params['display'])
                {
                    if( !$navbar[$menu] )
                    {
                        $navbar[$menu]['label'] = $row['label'];
                        $navbar[$menu]['icon'] = $row['icon'];
                        $navbar[$menu]['action'] = $navbar[$menu]['action'] ?  $navbar[$menu]['action'] : $params['action'];
                        $navbar[$menu]['default'] = false;
                    }
                }

                //如果为当前的路由则高亮
                if( !$navbar[$menu]['default'] && $params['action'] == $defaultActionName && $navbar[$menu] )
                {
                    $navbar[$menu]['default'] = true;
                    $selectMenu = $menu;
                }
            }

            if( !$row['shopIndex'] && $selectMenu ==  $menu)
            {
                foreach( (array)$row['menu'] as $k=>$params )
                {
                    //判断是否开启二级域名
                    if(!$subdomainSetting && $params['url'] =="subdomain.html"){
                        $params['display'] = false;
                    }

                    //判断是否开启流量统计
                    if($trafficsetting && $params['url'] =="sysstat/systraffic.html"){
                        $params['display'] = false;
                    }

                    //标记一下，临时解决im的菜单问题
                    if(!app::get('sysim')->is_installed() && strstr($params['action'], 'im_webcall'))
                    {
                        $shopMenu[$menu][20]['display'] = false;
                        $params['display'] = false;
                    }
                    //标记一下，临时解决im的菜单问题--这里结束

                    $sidebar[$menu]['active'] = true;
                    $sidebar[$menu]['label'] = $row['label'];
                    $sidebar[$menu]['icon'] = $row['icon'];
                    $params['default'] = ($params['action'] == $defaultActionName) ? true : false;
                    $sidebar[$menu]['menu'][] =  $params;
                }
            }
        }

        $res['all'] = $allMenu;
        $res['navbar'] = $navbar;
        $res['sidebar'] = $sidebar;
        return $res;
    }

    public function setShortcutMenu($data)
    {
        return app::get('toptemai')->setConf('shortcutMenuAction.'.$this->sellerId, $data);
    }

    public function getShortcutMenu()
    {
        return app::get('toptemai')->getConf('shortcutMenuAction.'.$this->sellerId);
    }

    /**
     * 用于指示商家操作者的标志
     * @return array 商家登录用户信息
     */
    public function operator()
    {
        return array(
            'user_type' => 'seller',
            'op_id' => pamAccount::getAccountId(),
            'op_account' => pamAccount::getLoginName(),
        );
    }

    /**
     * 记录商家操作日志
     *
     * @param $lang 日志内容
     * @param $status 成功失败状态
     */
    protected final function sellerlog($memo)
    {
        // 开启了才记录操作日志
        if ( SELLER_OPERATOR_LOG !== true ) return;

        if(!$this->shopId)
        {
            $shopId = app::get('toptemai')->rpcCall('shop.get.loginId',array('seller_id'=>pamAccount::getAccountId()),'seller');
        }
        else
        {
            $shopId = $this->shopId;
        }
        $queue_params = array(
            'seller_userid'   => pamAccount::getAccountId(),
            'seller_username' => pamAccount::getLoginName(),
            'shop_id'         => $shopId,
            'created_time'    => time(),
            'memo'            => $memo,
            'router'          => request::fullurl(),
            'ip'              => request::getClientIp(),
        );
        return system_queue::instance()->publish('system_tasks_sellerlog', 'system_tasks_sellerlog', $queue_params);
    }

}
