<?php 
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 */
class sysclearing_data_clean
{
    
    /*
     * 清除初始化数据
     */
    public function clean()
    {
        app::get('sysclearing')->model('settlement')->delete( array('*') );
        app::get('sysclearing')->model('settlement_detail')->delete( array('*') );
    }
    #End Func
}