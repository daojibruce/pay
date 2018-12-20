<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topc_temai_check {

    public function reloadData($item) {
        $result = $item;
        unset($item);
        $itemId = $result['item_id'];

        $temaiinfo = app::get('topc')->rpcCall('temai.item.get', array(
            'item_id' => $itemId
        ));
        if ($temaiinfo && !empty($temaiinfo)) {
            $result['temai_info'] = $temaiinfo;
            // 主商品数据处理
            $result = array_merge($result, array(
                'price' => $temaiinfo['price'],
                'mkt_price' => $temaiinfo['mkt_price'],
                'title' => $temaiinfo['title'],
                'sub_title' => $temaiinfo['sub_title'],
                'realStore' => $temaiinfo['store'],
                'store' => $temaiinfo['store'],
                'saleStore' => $temaiinfo['sale_num'],
                'is_temai' => true,
            ));

            // 处理sku商品数据
            foreach ($temaiinfo['sku'] as $temaiitem) {
                if(isset($result['sku'][$temaiitem['sku_id']])) {
                    // 处理库存问题
                    $result['realStore'] = $result['realStore'] - $result['sku'][$temaiitem['sku_id']]['realStore'] + $temaiitem['store'];

                    $result['sku'][$temaiitem['sku_id']] = array_merge($result['sku'][$temaiitem['sku_id']], array(
                        'price' => $temaiitem['price'],
                        'mkt_price' => $temaiitem['mkt_price'],
                        'realStore' => $temaiitem['store'],
                        'store' => $temaiitem['store'],
                        'saleStore' => $temaiitem['sale_num'],
                    ));
                }
            }
        }
        return $result;
    }

    public function reloadSkuData($sku) {

    }

    /**
     * 检查是否平台展销商品
     *
     * @param   intger  $item_id    item_id
     * @param   intger  $sku_id     sku_id
     *
     * @return  boolean
     */
    public function checkIsTemai($item_id = 0, $sku_id = 0) {
        if($item_id) {
            return $this->getTemaiItem($item_id);
        }
        if($sku_id) {
            $skuInfo = app::get('systemai')->model('temai_detail')->getRow('item_id', ['sku_id' => $sku_id]);
            if($skuInfo) {
                return $this->getTemaiItem($skuInfo['item_id']);
            }
            return false;
        }
    }

    public function getTemaiItem($item_id = 0, $field = 'temai_id') {
        if($item_id) {
            $itemWhere = [
                'item_id' => $item_id,
                'state'   => 1,
                'store|than' => 0,
            ];
            $item = app::get('systemai')->model('temai')->getRow($field, $itemWhere);
            if($item) {
                return $item;
            }
            return false;
        }
    }
}
