<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_stl_proofing_recommend(&$setting){
    //print_r($setting);exit;
    $providers = [];
    if (is_array($setting['provider_select'])) {
        $setting['provider_select'] = array_slice($setting['provider_select'], 0 ,9);
        foreach ($setting['provider_select'] as $provider_id) {
            $info =app::get('sysproofing')->model('provider')->getRow('provider_name, provider_id',['enabled' => '1','provider_id' => $provider_id]);
            $relInfo = app::get('sysproofing')->model('provider_cat')->getRow('cat_id ',['provider_id' => $provider_id]);
            $catInfo = app::get('sysproofing')->model('category')->getRow('cat_name ',['cat_id' => $relInfo['cat_id']]);
            $info['cat_name'] = $catInfo['cat_name'];
            $providers[] = $info;
        }
    } else {
        $provider_id = $setting['provider_select'];
        $info =app::get('sysproofing')->model('provider')->getRow('provider_name, provider_id',['enabled' => '1','provider_id' => $provider_id]);
        $relInfo = app::get('sysproofing')->model('provider_cat')->getRow('cat_id ',['provider_id' => $provider_id]);
        $catInfo = app::get('sysproofing')->model('category')->getRow('cat_name ',['cat_id' => $relInfo['cat_id']]);
        $info['cat_name'] = $catInfo['cat_name'];
        $providers[] = $info;
    }
    $setting['providers'] = $providers;
    return $setting;
}
?>
