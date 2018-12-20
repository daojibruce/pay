<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_wap_top_slider(&$setting){
    $setting['allimg']="";
    $setting['allurl']="";
    $theme_dir = kernel::get_themes_host_url().'/'.theme::getThemeName();
    if(!$setting['pic']){
        foreach($setting['img'] as $value){
            $setting['allimg'].=$rvalue."|";
            $setting['allurl'].=urlencode($value["url"])."|";
        }
    }else{
        foreach($setting['pic'] as $key=>$value){
            if($value['link']){
                if($value["url"]){
                    $value["linktarget"]=$value["url"];
                }
                $setting['allimg'].=$rvalue."|";
                $setting['allurl'].=urlencode($value["linktarget"])."|";
                $setting['pic'][$key]['link'] = str_replace('%THEME%',$theme_dir,$value['link']);
                if(is_null($firstPic)) $firstPic = $setting['pic'][$key];
                $lastPic = $setting['pic'][$key];
            }
        }
    }
    $setting['firstPic'] = $firstPic;
    $setting['lastPic'] = $lastPic;
    return $setting;
}
?>
