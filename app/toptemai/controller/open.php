<?php

/**
 * @brief 商家商品管理
 */
class toptemai_ctl_open extends toptemai_controller {

    public function index()
    {
        $shopId = $this->shopId;
        $this->contentHeaderTitle = app::get('toptemai')->_('开发者中心');

        $requestParams = ['shop_id'=>$shopId];
        $openInfo = app::get('toptemai')->rpcCall('open.shop.develop.info', $requestParams);
        $shopConf = app::get('toptemai')->rpcCall('open.shop.develop.conf', $requestParams);
        $pagedata['openInfo'] = $openInfo;
        $pagedata['shopConf'] = $shopConf;

        return $this->page('toptemai/open/index.html', $pagedata);
    }

    public function applyForOpen()
    {
        $url = url::action('toptemai_ctl_open@index');
        $shopId = $this->shopId;
        $requestParams = [
            'shop_id'=>$shopId,
            'key' => input::get('key'),
            'secret' => input::get('secret'),
        ];
        try
        {
        $res = app::get('toptemai')->rpcCall('open.shop.develop.apply', $requestParams);
        }
        catch( Exception $e )
        {
            return $this->splash('error',$url, $e->getMessage(),true);
        }
        $this->sellerlog('申请绑定开发者');
        return $this->splash('success',$url,'申请成功，等待审核',true);
    }

    public function setConf()
    {
        $shopId = $this->shopId;
        $confs = input::get();

        try
        {
            $requestParams = [
                'shop_id' => $shopId,
                'developMode' => $confs['developer'] ? $confs['developer'] : 'PRODUCT',
                ];
            app::get('toptemai')->rpcCall('open.shop.develop.setConf', $requestParams);
        }
        catch(Exception $e)
        {
            return $this->splash('error',$url,$e->getMessage(),true);
        }
        $this->sellerlog('开发者中心商家参数配置保存');
        return $this->splash('success',$url,'修改成功',true);
    }

}


