<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class site_task 
{
    function post_install() 
    {
        logger::info('Initial themes');
        kernel::single('site_theme_install')->initthemes();
        $themes = kernel::single('site_theme_install')->check_install($platform='pc');
    }//End Function

    function post_update($params){
        if($dbver['dbver'] < '1.0.7'){
            $pctheme = app::get('site')->getConf('current_theme');
            // 有才更新，否则可能会导致强制更新的时候设置了空的pc端默认模板
            if($pctheme){
                app::get('site')->setConf('pc_current_theme', $pctheme);
            }
        }
    }
}//End Class
