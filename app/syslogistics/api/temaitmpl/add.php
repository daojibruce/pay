<?php
// logistics.dlytmpl.add
class syslogistics_api_temaitmpl_add{

    public $apiDescription = "运费模板添加";
    public function getParams()
    {
        $return['params'] = array(
            'name' =>['type'=>'string','valid'=>'required', 'description'=>'模板名称','default'=>'','example'=>'1'],
            'user_id' =>['type'=>'int','valid'=>'required', 'description'=>'所属平台展销会员id','default'=>'','example'=>'1'],
            'order_sort' =>['type'=>'int','valid'=>'', 'description'=>'排序','default'=>'','example'=>'1'],
            'status' =>['type'=>'string','valid'=>'', 'description'=>'模板状态','default'=>'','example'=>'1'],
            'valuation' =>['type'=>'string','valid'=>'', 'description'=>'运费计算参数来源','default'=>'','example'=>'1'],
            'protect' =>['type'=>'string','valid'=>'', 'description'=>'物流保价','default'=>'','example'=>'1'],
            'protect_rate' =>['type'=>'string','valid'=>'', 'description'=>'保价费率','default'=>'','example'=>'1'],
            'minprice' =>['type'=>'string','valid'=>'', 'description'=>'保价费最低值','default'=>'','example'=>'1'],
            'fee_conf' =>['type'=>'json','valid'=>'', 'description'=>'配送地区配置','default'=>'','example'=>'1'],
            'free_conf' =>['type'=>'json','valid'=>'', 'description'=>'包邮规则配置','default'=>'','example'=>'1'],
            'is_free' =>['type'=>'boolean','valid'=>'', 'description'=>'是否包邮','default'=>'','example'=>'1'],
        );
        return $return;
    }
    public function create($params)
    {
        if($params['fee_conf'])
        {
            $params['fee_conf'] = json_decode($params['fee_conf'],true);
        }
        if($params['free_conf'])
        {
            $params['free_conf'] = json_decode($params['free_conf'],true);
        }

        $userId = $params['user_id'];

        $objDataDlyTmpl = kernel::single('syslogistics_data_temaitmpl');
        $result = $objDataDlyTmpl->addDlyTmpl($params,$userId);
        return $result;
    }
}
