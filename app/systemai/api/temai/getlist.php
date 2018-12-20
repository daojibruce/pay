<?php
/**
 * 商品添加、编辑
 * item.create
 */
class systemai_api_temai_getlist {

    public $apiDescription = "商品添加、编辑";

    public $use_strict_filter = true; // 是否严格过滤参数

    public function getParams()
    {
        $return['params'] = array(
            'user_id' => ['type'=>'string','valid'=>'required','description'=>'平台展销会员id','example'=>'18'],
        );
        return $return;
    }

    public function getList($params)
    {
        $result = kernel::single('systemai_data_temai')->getList($params['user_id']);

        return $result;
    }
}
