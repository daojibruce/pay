<?php

/**
 * error.php 
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class toptemai_ctl_error extends toptemai_controller {

    public function index()
    {

        return $this->page('toptemai/error/error.html', []);
    }

}
 