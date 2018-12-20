<?php
class systemai_finder_temaiapply{
    public $column_op = '操作';
    public $column_op_order = 12;
    public $column_op_width = 10;

    public function column_op(&$col,$list)
    {
        foreach ($list as $k => $row) {
            if ($row['status'] == 0) {
                $onclick1 = "new Dialog('?app=systemai&ctl=admin_temaiapply&act=dopass&temai_server_id[]={$row['temai_server_id']}&finder_id={$_GET['_finder']['finder_id']}', {title:'审核通过', height:220})";
                $onclick2 = "new Dialog('?app=systemai&ctl=admin_temaiapply&act=refuse&temai_server_id[]={$row['temai_server_id']}&finder_id={$_GET['_finder']['finder_id']}', {title:'审核不通过', height:220})";

                //拼装按钮
                $col[$k] = '<a href="javascript:void(0)" title="审核通过" onclick="'.$onclick1.'">审核通过</a>';
                $col[$k] .= ' | <a href="javascript:void(0)" title="审核不通过" onclick="'.$onclick2.'">审核不通过</a>';
            }else if($row['status'] == 1){
                $col[$k] = '审核通过';
            }else{
                $col[$k] = '审核未通过';
            }
        }
    }

    public $column_status = '审核状态';
    public $column_status_order = 12;
    public $column_status_width = 10;

    public function column_status(&$colList,$list)
    {
        foreach ($list as $k => $row) {
            /*
            $params = array(
                'temai_server_id' => $row['temai_server_id'],
                'fields' => 'server_name,server_cert,server_mobile,status',
            );
            $data = app::get('sysitem')->rpcCall('item.get',$params);
            */

            $this->status = array(
                '0' => app::get('sysitem')->_('待审核'),
                '1' => app::get('sysitem')->_('已通过'),
                '2' => app::get('sysitem')->_('已拒绝'),
            );
            $colList[$k] = $this->status[$row['status']];
        }
    }

    public $detail_basic = '基本信息';
    public function detail_basic($id)
    {
        $user_id = input::get('user_id');
        $params['fields'] = "user_id,server_name,server_cert,server_mobile,server_desc,reson_refuse,createtime,modified_time";
        $params['filter'] = array('temai_server_id' => $id);
        $temaiapplyMdlObj = app::get("systemai")->model('temaiapply');
        $pagedata['temaiapply'] = $temaiapplyMdlObj->getRow($params['fields'] , $params['filter']);

        return view::make('systemai/temaiapply/detail.html',$pagedata)->render();
    }
}
