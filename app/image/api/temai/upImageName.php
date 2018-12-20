<?php

class image_api_temai_upImageName {

    /**
     * 接口作用说明
     */
    public $apiDescription = '根据图片URL,修改图片名称';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        $return['params'] = array(
            'url' => ['type'=>'string','valid'=>'required','description'=>'图片URL','example'=>'','default'=>''],
            'image_name' => ['type'=>'string','valid'=>'required','description'=>'图片名称','example'=>'xxx','default'=>''],
        );

        return $return;
    }

    public function up($params)
    {
        $params = utils::_filter_input($params);

		$filter['disabled'] = 0;
		$filter['target_id'] = $params['user_id'];
		$filter['target_type'] = 'temai';
		$filter['url'] = $params['url'];

        $params['image_name'] = htmlspecialchars($params['image_name'],ENT_QUOTES);
		$resultData = app::get('image')->model('images')->update(['image_name'=>trim($params['image_name'])], $filter);
		return $resultData;
	}
}
