<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class site_ctl_admin_temai_setting extends site_admin_controller {

    /*
     * workground
     * @var string
     */
    var $workground = 'site_ctl_admin_temai_setting';

    public function index() {
        $all_settings = array(
            app::get('site')->_('基本信息') => array (
                'temai.limit_apply',
                'temai.usage_agreement',
            ),
        );
        $html = kernel::single ( 'site_base_setting', $this->app )->process ( $all_settings );
        $pagedata ['_PAGE_CONTENT'] = $html;
        return $this->page('desktop/common/default.html', $pagedata);
    } //End Function

}//End Class
