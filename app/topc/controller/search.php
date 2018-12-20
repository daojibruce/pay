<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topc_ctl_search extends topc_controller {

    public function index()
    {
        $searchTypeList = ['goods','shops','brands','temai'];
        $keyword = input::get('keyword');
        $curType = input::get('curType');

        if(! in_array($curType , $searchTypeList)){
            $curType = "goods";
        }
        
        if( !empty($keyword) )
        {
            return redirect::action('topc_ctl_list@index',array('n'=>$keyword, 'k'=> $curType));
            /*if( $curType == 'shops' )
            {
                return redirect::action('topc_ctl_shopcenter@search',array('n'=>$keyword));
            }else{
                return redirect::action('topc_ctl_list@index',array('n'=>$keyword, 'k'=> $curType));
            }*/
        }
        else
        {
            return redirect::back();
        }
    }
}

