<?php
class toptemai_ctl_shop_setting extends toptemai_controller{

    public function index()
    {
        $shopdata = app::get('toptemai')->rpcCall('shop.get',array('shop_id'=>shopAuth::getShopId()),'seller');
        $pagedata['shop'] = $shopdata;
        $pagedata['im_plugin'] = app::get('sysconf')->getConf('im.plugin');
        $this->contentHeaderTitle = app::get('toptemai')->_('店铺设置');
        return $this->page('toptemai/shop/setting.html', $pagedata);
    }

    public function saveSetting()
    {
        $postData = input::get();
        $validator = validator::make(
            [$postData['shop_descript']],['max:200'],['店铺描述不能超过200个字符!']
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                return $this->splash('error',null,$error[0]);
            }
        }
        try
        {
            $result = app::get('toptemai')->rpcCall('shop.update',$postData);
            if( $result )
            {
                $msg = app::get('toptemai')->_('设置成功');
                $result = 'success';
            }
            else
            {
                $msg = app::get('toptemai')->_('设置失败');
                $result = 'error';
            }
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            $result = 'error';
        }
        $this->sellerlog('编辑店铺配置。如店铺logo,描述等');
        $url = url::action('toptemai_ctl_shop_setting@index');
        return $this->splash($result,$url,$msg,true);

    }

}


