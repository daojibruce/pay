<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
|--------------------------------------------------------------------------
| 定义paths
|--------------------------------------------------------------------------
|
| 常用的目录定义, 都在此处定义
|
*/
require __DIR__.'/paths.php';

/**
|--------------------------------------------------------------------------
| 兼容老系统的写法. 
|--------------------------------------------------------------------------
|
| 
|
*/

//todo production 会换成环境变量
if (file_exists(CONFIG_DIR.'/production/compatible.php'))
{
    require CONFIG_DIR.'/production/compatible.php';
}
else
{
    require CONFIG_DIR.'/compatible.php';
}


//todo: xhprof
if (defined('XHPROF_DEBUG') && constant('XHPROF_DEBUG') === true)
{
    include(ROOT_DIR."/app/serveradm/xhprof.php");
}


/**
|--------------------------------------------------------------------------
| 加载kernel
|--------------------------------------------------------------------------
|
| 加载kernel
|
*/

require(ROOT_DIR.'/app/base/kernel.php');

/**
|--------------------------------------------------------------------------
| 系统启动加载
|--------------------------------------------------------------------------
|
| 系统启动加载
|
*/
require APP_DIR.'/base/start.php';

