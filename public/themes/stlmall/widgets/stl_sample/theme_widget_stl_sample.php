<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_stl_sample(&$setting){
    $samples = app::get('sysproofing')->model('sample')->getList('sample_name,sample_id', ['status' => '0'],0,5,'createtime desc');
    //查看用户是否是服务商
    $userId = userAuth::id();
    $proInfo = app::get('sysproofing')->model('provider')->getRow('provider_id',['user_id' => $userId, 'enabled' => '1']);
    if ($proInfo) {
        $setting['is_provider'] = 1;
    }
    $setting['samples'] = $samples;

    //今日平台展销
    $todayTime = date('Ymd');
    $todayFilter = array('day' => $todayTime);
    $temaiIdList = app::get('systemai')->model('temaiday')->getlist("temai_id" , $todayFilter , 0 , 10);
    $setting['temaiToday'] = false;
    if(!empty($temaiIdList)){
        $temaiIdList = array_column($temaiIdList, 'temai_id');
        $todayFilter = array('temai_id' => $temaiIdList);
        $temaiList = app::get('systemai')->model('temai')->getlist('temai_id,item_id,title' , $todayFilter);
        $setting['temaiToday'] = $temaiList;
    }

    return $setting;
}
?>
