<?php
/**
 * ShopEx licence
 * - image.shop.cat.imagetype.list
 * - 获取店铺图片子分类列表
 *
 * @copyright Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license   http://ecos.shopex.cn/ ShopEx License
 * @link      http://www.shopex.cn/
 * @author    shopex 2016-05-17
 */
class image_api_temai_cat_imagetypelist {

    public $apiDescription = "获取店铺图片类型子分类列表(根据图片类型)";

    public function getParams()
    {
        $return['params'] = [
            'user_id' => ['type'=>'int', 'valid'=>'required|numeric', 'title'=>'平台展销会员ID', 'example'=>'24','desc'=>'会员ID'],
            'img_type'=> ['type'=>'string', 'valid'=>'', 'title'=>'店铺图片类型', 'example'=>'item','desc'=>'店铺图片类型，产品图片item;店铺图片shop'],
            'fields'  => ['type'=>'field_list', 'valid'=>'required', 'title'=>'查询字段', 'example'=>'*', 'desc'=>'需要查询返回的字段'],
        ];
        return $return;
    }

    /**
     * @desc 获取店铺图片子分类列表
     *
     * @return int image_cat_id 图片类型子分类ID
     * @return int shop_id      店铺ID
     * @return string img_type  图片类型
     * @return string image_cat_name 图片类型子分类名称
     * @return time last_modified  最后修改时间
     */
    public function get($params)
    {
        $objMdlImageCat = app::get('image')->model('image_cat');
        $filter['user_id'] = $params['user_id'];
        if( $params['img_type'] )
        {
            $filter['img_type'] = $params['img_type'];
        }
        return $objMdlImageCat->getlist($params['fields'], $filter);
    }
}

