<?php

class sysshop_api_shop_getStorePolice {

    public $apiDescription = "根据店铺ID获取店铺设置的库存报警值";

    public function getParams()
    {
        $return['params'] = array(
            'user_id' => ['type'=>'int','valid'=>'','description'=>'平台展销会员ID','default'=>'','example'=>''],
            'shop_id' => ['type'=>'int','valid'=>'','description'=>'店铺ID','default'=>'','example'=>''],
            'fields' => ['type'=>'field_list','valid'=>'','description'=>'店铺数据字段','default'=>'','example'=>''],
        );
        return $return;
    }

    public function getStorePolice($params)
    {
        $storePolice = app::get('sysshop')->model('store_police');
        if(!$params['fields'])
        {
            $params['fields'] = '*';
        }

        //当传递user_id值过来时，意味着从平台展销商后台取库存数值
        $filter = array('user_id'=>$params['user_id']);
        if($params['shop_id'] > 0){
            $filter = array('shop_id'=>$params['shop_id']);
        }
        $storePolice = $storePolice->getRow($params['fields'] , $filter);
        return  $storePolice;
    }
}

