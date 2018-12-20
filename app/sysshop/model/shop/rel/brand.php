<?php

class sysshop_mdl_shop_rel_brand extends dbeav_model{
    public function newBrandShopForItem($shopId , $brandId)
    {
        $data = array('shop_id' => $shopId, 'brand_id' => $brandId);
        $brandItem = $this->getRow('brand_id' , $data);
        if($brandItem['brand_id'] > 0 ){
            return true;
        }

        try{
            $this->insert($data);
        }catch(Exception $e){
            $msg = $e->getMessage();
            throw new Exception("商铺签约品牌: ".$msg);
        }
    }
}

