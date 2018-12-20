<?php
class syscategory_api_virtualcat_virtualcatList{
    public $apiDescription = "获取类目树形结构";
    public function getParams()
    {
        $return['params'] = array(
        );
        return $return;
    }
    public function getList($params)
    {
        return kernel::single('syscategory_data_virtualcat')->getTree();
    }


}
