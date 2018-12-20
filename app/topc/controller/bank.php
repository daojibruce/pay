<?php
use Bpbpay\Gfbank\transaction as bankDeal;
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topc_ctl_bank extends topc_controller
{
    public function index()
    {
        with(bankDeal::init())->subaccountList();
    }
}
