<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topc_middleware_redirectIfAuthenticated
{
    public function __contruct()
    {

    }

    public function handle($request, Closure $next)
    {
        if (userAuth::check())
        {
            return redirect::route('topc');
        }
        return $next($request);
    }
}
