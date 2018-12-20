<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_0_index_main_floor(&$setting)
{

	//商品信息分类
	$objMdlCat = app::get('syscategory')->model('cat');
	$first_cat_list = $objMdlCat->getList('cat_id,cat_name', ['level' => 1], 0, 9);
	$setting['first_cat_list'] = $first_cat_list;

	return $setting;
}
   /*
	$first_cat_id= 1;//$setting['class_id'][0];//第一级分类id

    //根据一级分类id,查询第二级分类名称
    //$ameArr = app::get('syscategory')->rpcCall('category.cat.get',array('cat_id'=>$first_cat_id));
    foreach($ameArr as $kk=>$aa){
        if(is_array($aa)){
            foreach($aa[lv2] as $a){
                $ap = array(
                    's_cat_id'=>$a['cat_id'],
                    's_cat_name'=>$a['cat_name'],
                );
                $s_cat_id[]=$ap['s_cat_id'];//第二级分类id

                foreach($a[lv3] as $b){
                    $ap = array(
                        'three_cat_id'=>$b['cat_id'],
                        'three_cat_name'=>$b['cat_name'],
                    );
                    $three_cat_id[]=$ap['three_cat_id'];//第二级分类id
                }
            }

        }
    }

    //根据第三级分类id，查询前10个三级分类名称
    $objMdlCat = app::get('syscategory')->model('cat');
    $three_classify_name = $objMdlCat->getList('cat_id,cat_name,level,cat_path',array('level'=>3,'cat_id|in'=>$three_cat_id),'0','9');
    $setting['item']=$three_classify_name;
    $setting['class_id']=$first_cat_id;

	//new
	if( !$setting['slider_type'] )
    {
        if( $setting['brand'] )
        {
            $data = app::get('topc')->rpcCall('category.brand.get.list',['brand_id'=>implode(',',$setting['brand']),['fields'=>'brand_id,brand_logo']]);
            $i = 0;
            $k = 1;
            foreach( $data as $n=>$row )
            {
                $picData[$n]['link'] = $row['brand_logo'];//图片地址
                $picData[$n]['linkinfo'] = $row['brand_name'];//图片描述
                $picData[$n]['linktarget'] = url::action('topc_ctl_list@index',['search_keywords'=>$row['brand_name']]);//链接地址


            }
            $setting['picData'] = $picData;
        }
    }
    else
    {
        foreach( (array)$setting['pic'] as $n=>$row )
        {
            $picData[][$n] = $row;
        }
        $setting['picData'] = $picData;
    }
    return $setting;
*/
?>
