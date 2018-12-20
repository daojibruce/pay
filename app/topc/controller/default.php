<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topc_ctl_default extends topc_controller
{
    public function index()
    {
        $GLOBALS['runtime']['path'][] = array('title'=>app::get('topc')->_('首页'),'link'=>kernel::base_url(1));
        $this->setLayoutFlag('index');

        return $this->page();
    }

    public function ecloud()
    {
        $GLOBALS['runtime']['path'][] = array('title'=>app::get('topc')->_('企业云'),'link'=>kernel::base_url(1));
        $this->setLayoutFlag('ecloud');

        return $this->page();
    }

    public function obor()
    {
        $GLOBALS['runtime']['path'][] = array('title'=>app::get('topc')->_('一带一路'),'link'=>kernel::base_url(1));
        $this->setLayoutFlag('obor');

        return $this->page();
    }

    public function logistics()
        {
            $GLOBALS['runtime']['path'][] = array('title'=>app::get('topc')->_('物流中心'),'link'=>kernel::base_url(1));
            $this->setLayoutFlag('logistics');

            return $this->page();
        }

    public function witness()
    {
        $GLOBALS['runtime']['path'][] = array('title'=>app::get('topc')->_('金融中心'),'link'=>kernel::base_url(1));
        $this->setLayoutFlag('witness');

        return $this->page();
    }

    public function brands()
        {
            $GLOBALS['runtime']['path'][] = array('title'=>app::get('topc')->_('品牌页'),'link'=>kernel::base_url(1));
            $this->setLayoutFlag('brands');

            return $this->page();
        }

    public function platforms()
        {
            $GLOBALS['runtime']['path'][] = array('title'=>app::get('topc')->_('平台展销页'),'link'=>kernel::base_url(1));
            $this->setLayoutFlag('platforms');

            return $this->page();
        }

    public function matchmaking()
        {
            $GLOBALS['runtime']['path'][] = array('title'=>app::get('topc')->_('服务撮合页'),'link'=>kernel::base_url(1));
            $this->setLayoutFlag('matchmaking');

            return $this->page();
        }

    public function redirect()
    {
        return view::make('topc/redirect.html', $pagedata);
    }
}
