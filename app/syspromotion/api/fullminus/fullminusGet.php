<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 获取单条满减促销数据
 */
final class syspromotion_api_fullminus_fullminusGet {

    public $apiDescription = '获取单条满减促销数据';

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'shop_id'            => ['type' => 'int', 'valid'    => '', 'example' => '', 'desc' => '店铺ID,user_id和shop_id必填一个'],
            'fullminus_id'       => ['type' => 'int', 'valid'    => '', 'example' => '', 'desc' => '满减促销id'],
            'fullminus_itemList' => ['type' => 'string', 'valid' => '', 'example' => '', 'desc' => '满减促销的商品'],
        );

        return $return;
    }

    /**
     *  获取单条满减促销信息
     * @param  array $params 筛选条件数组 * @return array         返回一条满减促销信息
     */
    public function fullminusGet($params)
    {
        $fullminusInfo = kernel::single('syspromotion_data_object')->setPromotion('fullminus')->getPromoitonInfo($params['fullminus_id']);
        $fullminusInfo['valid'] = $this->__checkValid($fullminusInfo);
        if($params['fullminus_itemList'])
        {
            $fullminusItems = kernel::single('syspromotion_data_object')->setPromotion('fullminus')->getPromtionItems($params['fullminus_id']);
            $fullminusInfo['itemsList'] = $fullminusItems;
        }

        return $fullminusInfo;
    }

    // 检查满减是否可用
    private function __checkValid(&$fullminusInfo)
    {
        $now = time();
        if( ($fullminusInfo['fullminus_status']=='agree') && ($fullminusInfo['start_time']<$now) && ($fullminusInfo['end_time']>$now) )
        {
            return true;
        }
        return false;
    }

}

