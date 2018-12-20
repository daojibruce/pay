<?php
class systemai_api_shop_saveDlycorp{
    public $apiDescription = "店铺保存签约的物流公司";
    public function getParams()
    {
        $return['params'] = array(
            'user_id' =>['type'=>'int','valid'=>'required', 'description'=>'平台展销会员编号id','default'=>'','example'=>'1'],
            'corp_id' =>['type'=>'int','valid'=>'required', 'description'=>'物流公司编号id','default'=>'','example'=>'1'],
        );
        return $return;
    }
    public function savedata($params)
    {
        $dlycorp = app::get('sysshop')->rpcCall('logistics.dlycorp.get',['corp_id'=>$params['corp_id'],'fields'=>'corp_id,corp_code,corp_name']);
        $dlycorp['user_id'] = $params['user_id'];

        $objMdlDlycorpShop = app::get('systemai')->model('user_rel_dlycorp');
        try{
            $pagedata = $objMdlDlycorpShop->save($dlycorp);
        }
        catch( \LogicException $e )
        {
            $msg = $e->getMessage();
            throw new \LogicException($msg);
        }
        return $pagedata;
    }

}
