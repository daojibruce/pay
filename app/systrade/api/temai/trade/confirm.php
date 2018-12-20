<?php

class systrade_api_temai_trade_confirm {

    public $apiDescription = "交易完成";

    public function getParams()
    {
        $return['params'] = array(
            'tid' => ['type'=>'int', 'valid'=>'required|numeric', 'default'=>'', 'example'=>'','description'=>'订单id'],
            'user_id' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'订单所属用户id'],
            'temai_user_id' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'订单所属平台展销会员id'],
        );

        return $return;
    }

    public function confirmTrade($params)
    {
        try
        {
            kernel::single('systrade_data_trade_confirm')
                ->setOperator($params['oauth'])
                ->generate($params['tid'], $params['user_id'] , null, $params['temai_user_id'] );
        }
        catch(Exception $e)
        {
           throw $e;
        }
        return true;
    }
}
