<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2bpay_task{

    public function post_install($options)
    {
        kernel::single('base_initial', 'b2bpay')->init();
    }

    public function post_uninstall()
    {
        //@todo
    }

    public function post_update($dbver)
    {
        //@todo
    }

}

