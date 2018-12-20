<?php

class tmimage_api_deleteImage {

    /**
     * 接口作用说明
     */
    public $apiDescription = '数据库中删除图片链接，但是不删除真实图片文件';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        $return['params'] = array(
            'image_id' => ['type'=>'string','valid'=>'required','description'=>'图片ID, 多个图片则用逗号隔开','example'=>'1,2,3','default'=>''],
        );

        return $return;
    }

    public function delete($params)
    {
		$filter['target_id'] = $params['user_id'];
		$filter['target_type'] = 'temai';
		$filter['id'] = explode(',',$params['image_id']);

		$resultData = app::get('image')->model('images')->update(['disabled'=>1], $filter);

		return $resultData;
	}
}
