<?php
class syslogistics_api_temaitmpl_delete{

    public $apiDescription = "运费模板更新";
    public function getParams()
    {
        $return['params'] = array(
            'template_id' =>['type'=>'string','valid'=>'required', 'description'=>'模板id','default'=>'','example'=>'1'],
            'user_id' =>['type'=>'string','valid'=>'required', 'description'=>'所属会员id','default'=>'','example'=>'1'],
        );
        return $return;
    }

    public function delete($params)
    {
        $filter['template_id'] = $params['template_id'];
        $filter['user_id']     = $params['user_id'];

        $searchParams['user_id']    = $params['user_id'];
        $searchParams['dlytmpl_id'] = $params['template_id'];
        $searchParams['fields'] = 'item_id';
        $searchParams['page_no'] = 0;
        $searchParams['page_size'] = 1;

        $itemList = app::get('topshop')->rpcCall('temai.item.search', $searchParams);
        if($itemList['total_found'])
        {
             throw new \LogicException('该快递模板还有商品绑定，不能删除！');
        }

        $objDataDlyTmpl = kernel::single('syslogistics_data_temaitmpl');
        $result = $objDataDlyTmpl->remove($filter);
        return $result;
    }
}
