<?php
/**
 * qianjiang licence
 *
 * @copyright  Copyright (c) 2005-2015 qianjiang Technologies Inc. (http://www.qianjiang.cn)
 * @license  www.ec-os.net qianjiang License
 *
 */
class oms_api_login implements topapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '同步登录接口';

    public function getParams()
    {
        $return['params'] = [
			'username' => [	//登录用户名
				'type'=>'string', 'valid'=>'required', 'example'=>'18202100000',
				'description'=>'用户名'
			],
			'password' => [	//登录密码
				'type'=>'string', 'valid'=>'required', 'example'=>'xxxxxx',
				'description'=>'密码'
			],
			'domain' => [	//发起请求域名
				'type'=>'string', 'valid'=>'required', 'example'=>'oms.com',
				'description'=>'oms请求域名'
			]
		];

        return $return;
    }

    //单点登录接口
    public function handle($params) {
        try {
            $data = $params;

            $verify['username'] = $data['username'];
            $verify['password'] = $data['password'];
            $domain = $data['domain'];

            //if..

            $rs = app::get('sysuser')->rpcCall('user.login', $verify);
        }
        catch(\LogicException $e)
        {
            throw new \LogicException($e->getMessage());
        }
        return $rs;
    }

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        //接口传入的参数
        return [];
    }

    //返回格式
	public function returnJson()
    {
        $ret = [];
        return json_encode($ret);
    }
}

