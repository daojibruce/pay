<?php
class syscategory_api_virtualcat_get{
    public $apiDescription = "获取父类下的所有子分类";
    public function getParams()
    {
        $return['params'] = array(
            'virtual_parent_id' => ['type'=>'string','valid'=>'required', 'description'=>'类目id','default'=>'','example'=>'1'],  
        );
        return $return;
    }
    public function get($params)
    {
        $catMdl= app::get('syscategory')->model('virtualcat');
        $fields = 'virtual_cat_name,virtual_cat_logo,virtual_cat_id,virtual_parent_id,level,child_count';
     
        return $catMdl->getList($fields, array('virtual_parent_id'=>$params['virtual_parent_id']), 0, -1, 'order_sort ASC, virtual_cat_id DESC');
    }


}
