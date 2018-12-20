<?php
class syscategory_api_virtualcat_virtualcatInfo{
    public $apiDescription = "获取虚拟分类信息";
    public function getParams()
    {
        $return['params'] = array(
            'virtual_cat_id' => ['type'=>'string','valid'=>'required', 'description'=>'类目id,以逗号相隔的多个id数据','default'=>'','example'=>'1,23'],
            'fields' => ['type'=>'field_list','valid'=>'', 'description'=>'获取类目的指定字段','default'=>'cat_name,level,cat_id','example'=>'cat_name,cat_id'],
        );
        return $return;
    }
    public function getInfo($params)
    {
        $virtual_cat_id = $params['virtual_cat_id'];
        $objMdlVirtualcat = app::get('syscategory')->model('virtualcat');
        $catInfo = $objMdlVirtualcat->getRow('cat_path, level, virtual_cat_name, virtual_cat_logo, virtual_cat_id, virtual_parent_id, order_sort, filter', array('virtual_cat_id'=>$virtual_cat_id));

        return $catInfo;
    }
}


