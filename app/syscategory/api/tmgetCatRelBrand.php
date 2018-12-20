<?php
class syscategory_api_tmgetCatRelBrand{
    public $apiDescription = "获取类目关联的品牌";
    public function getParams()
    {
        $return['params'] = array(
            'user_id' => ['type'=>'string','valid'=>'', 'description'=>'签约店铺id','example'=>'3','default'=>''],
            'cat_id' => ['type'=>'int','valid'=>'required|numeric','description'=>'类目id|类目id必须是整型','default'=>'','example'=>'1'],
            'fields' => ['type'=>'fields_list','valid'=>'','description'=>'品牌字段集','default'=>'','example'=>'brand_id,brand_name'],
        );
        return $return;
    }

    public function getData($params)
    {
        $brands = array();
        $catId = $params['cat_id'];
        $userId = $params['user_id'];
        $row = $params['fields'];

        if(!is_numeric($catId) )
        {
            throw new \LogicException(app::get('syscategory')->_('参数cat_id有误'));
        }

        //优先读取签约的品牌列表
        $relBrandMdl = app::get('systemai')->model('shop_rel_brand');
        $relBrand = $relBrandMdl->getBrandIdByShopId($userId);
        if(!$relBrand)
        {
            throw new \LogicException(app::get('syscategory')->_('该类目下无关联品牌'));
        }
        $brandIdList = array_column($relBrand, 'brand_id');
        $brands = $this->__getBrand($brandIdList,$row);
        if($brands){
            return $brands;
        }

        //读取与分类相关联的品牌列表
        $objMdlCat = app::get('syscategory')->model('cat');
        $cat = $objMdlCat->getRow('cat_id,level,is_leaf,cat_path',array('cat_id'=>$catId));
        if(!$cat || !$cat['level'])
        {
            throw new \LogicException(app::get('syscategory')->_('参数类目cat_id='.$catId.'不存在'));
        }

        $lv3CatIds = $this->__getLv3CatId($catId,$cat['level']);
        if($lv3CatIds)
        {
            $relBrand = $this->__getRelBrand($lv3CatIds);
            if($relBrand)
            {
                $brandIds = array_column($relBrand,'brand_id');
                $brands = $this->__getBrand($brandIds,$row);
            }else{
                throw new \LogicException(app::get('syscategory')->_('该类目下无关联品牌'));
            }
        }
        //throw new \LogicException(app::get('syscategory')->_('参数cat_id='.$catId.'有误'));

       return $brands;
    }

    //获取三级类目的ids
    private function __getLv3CatId($catId,$lv=3)
    {
        $objMdlCat = app::get('syscategory')->model('cat');
        switch($lv)
        {
            case "1":
                $lv2Ids = $objMdlCat->getList('cat_id',array('parent_id'=>$catId));
                if(!$lv2Ids) return false;
                $lv2cids = array_column($lv2Ids,'cat_id');

                $lv3Ids = $objMdlCat->getList('cat_id',array('parent_id'=>$lv2cids));
                $lv3Ids = array_column($lv3Ids,'cat_id');
                break;

            case "2":
                $lv3Ids = $objMdlCat->getList('cat_id',array('parent_id'=>$catId));
                $lv3Ids = array_column($lv3Ids,'cat_id');
                break;

            case "3":
                $lv3Ids = $catId;
                break;
        }
        return $lv3Ids;
    }

    //获取三级类目关联的品牌
    private function __getRelBrand($catIds)
    {
        $filter['cat_id'] = $catIds;
        $objMdlRelBrand = app::get('syscategory')->model('cat_rel_brand');
        $datas = $objMdlRelBrand->getList('brand_id,cat_id',$filter);
        return $datas;
    }

    //获取指定品牌信息
    private function __getbrand($brandIds,$row="")
    {
        if(!$row)
        {
            $row = "brand_id,brand_name";
        }
        $filter['brand_id'] = $brandIds;
        $objMdlBrand = app::get('syscategory')->model('brand');
        $brands = $objMdlBrand->getList($row,$filter);
        return $brands;
    }
}