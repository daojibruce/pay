<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
|--------------------------------------------------------------------------
| 二次开发目录设置
|--------------------------------------------------------------------------
|
| 二次开发目录设置,`custom`可以替换为自己的二次开发目录
|
*/
# define('CUSTOM_CORE_DIR', ROOT_DIR.'/custom');

/**
|--------------------------------------------------------------------------
| 压力测试模式开关
|--------------------------------------------------------------------------
|
| 默认注释掉, 压力测试的时候可以开启使用
|
*/
# define('STRESS_TESTING', true);


//平台角色
define('SYS_USER_ROLE_CUSTOM', '普通会员');
define('SYS_USER_ROLE_COMPANY', '企业会员');
define('SYS_USER_ROLE_SELLER', '店铺');
define('SYS_USER_ROLE_SERVICE', '服务撮合');
define('SYS_USER_ROLE_SPECICAL', '平台展销');
define('SYS_USER_ROLE_JZB', '金融');
define('SYS_USER_ROLE_DELIVER', '物流服务商');
define('SYS_USER_ROLE_STATE', '平台服务商');
define('SYS_USER_ROLE_OMS', 'SAAS ERP');

//OMS地址
define('COMPANY_OMS_DOMAIN', '');

/**
|--------------------------------------------------------------------------
| 见证宝 银行网管调试开关
|--------------------------------------------------------------------------
|
| 默认为:0 开发环境, 上线请转换成 线上环境:1
|
| 平安见证宝配置信息
*/
//环境 0开发, 1生产
define('ENVIRONMENT', 0);

/**
|--------------------------------------------------------------------------
| xhprof调试开关
|--------------------------------------------------------------------------
|
| 默认为false, 不需要调试时请不要开启
|
*/
define('XHPROF_DEBUG', false);

/**
|--------------------------------------------------------------------------
| 尚未完成改造的部分
|--------------------------------------------------------------------------
|
| 尚未完成改造的部分
|
*/
define('WITH_REWRITE', false); // URL REWRITE配置
define('EDITOR_ALL_SOUCECODE',false);//是否使后台可视化编辑器变为源码编辑模式
define('DONOTUSE_CSSFRAMEWORK',false);//是否禁用前台系统css框架n
define('WITHOUT_AUTOPADDINGIMAGE',false);//图片处理时不需要自动补白

define('WITHOUT_GZIP', false);
define('WITHOUT_STRIP_HTML', true);

define('ADMIN_OPERATOR_LOG', true); //是否开启平台操作日志
define('SELLER_OPERATOR_LOG', true); //是否开启商家操作日志

# define('GZIP_CSS',true);
# define('GZIP_JS',true);
define('DEV_CHECKDEMO', false);

/**
|--------------------------------------------------------------------------
| 暂时没地方放的常量定义
|--------------------------------------------------------------------------
|
| 暂时没地方放的常量定义
|
*/
define('SET_T_STR', 0);
define('SET_T_INT', 1);
define('SET_T_ENUM', 2);
define('SET_T_BOOL', 3);
define('SET_T_TXT', 4);
define('SET_T_FILE', 5);
define('SET_T_DIGITS', 6);
/**
|--------------------------------------------------------------------------
| windows安装兼容
|--------------------------------------------------------------------------
|
| windows安装兼容
|
*/
define('LOG_SYS_EMERG', 0);
define('LOG_SYS_ALERT', 1);
define('LOG_SYS_CRIT', 2);
define('LOG_SYS_ERR', 3);
define('LOG_SYS_WARNING', 4);
define('LOG_SYS_NOTICE', 5);
define('LOG_SYS_INFO', 6);
define('LOG_SYS_DEBUG', 7);
