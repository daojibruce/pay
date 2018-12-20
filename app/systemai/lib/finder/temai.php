<?php
class systemai_finder_temai{
    public $column_edit = '商品平台展销标题';
    public $column_edit_order = 3;
    public $column_edit_width = 100;


    public function column_edit(&$colList, $list)
    {
        foreach($list as $k=>$row)
        {
            $url = url::action('topc_ctl_item@index',array('item_id'=>$row['item_id']));
            $colList[$k] = "<a href='".$url."'>".$row['title']."</a>";
        }
    }

    public $column_status = '是否上架';
    public $column_status_order = 12;
    public $column_status_width = 10;

    public function column_status(&$colList,$list)
    {
        foreach ($list as $k => $row) {
            $this->stateList = array(
                '0' => app::get('sysitem')->_('待审核'),
                '1' => app::get('sysitem')->_('已上架'),
                '2' => app::get('sysitem')->_('已下架'),
                '3' => app::get('sysitem')->_('审核未通过'),
            );
            $colList[$k] = $this->stateList[$row['state']];
        }
    }

    public $column_op = "操作";
    public $column_op_order = 1;
    public $column_op_width = 10;

    public function column_op(&$col ,$list)
    {
        foreach($list as $k=>$row)
        {
			if ($row['state'] == 0) {
				$title = app::get('systemai')->_('审核');
				$onclick1 = "new Dialog('?app=systemai&ctl=admin_temai&act=onsale&temai_id[]={$row['temai_id']}&finder_id={$_GET['_finder']['finder_id']}', {title:'审核上架', height:330})";
				$onclick2 = "new Dialog('?app=systemai&ctl=admin_temai&act=refuse&temai_id[]={$row['temai_id']}&finder_id={$_GET['_finder']['finder_id']}', {title:'审核不通过', height:220})";

				//拼装按钮
				$col[$k] = '<a href="javascript:void(0)" title="审核并上架" onclick="'.$onclick1.'">审核并上架</a>';
				$col[$k] .= ' | <a href="javascript:void(0)" title="审核不通过" onclick="'.$onclick2.'">审核不通过</a>';
			}
        }
    }

    public $column_tmstore = '商品平台展销标题';
    public $column_tmstore_order = 3;
    public $column_tmstore_width = 100;


    public function column_tmstore(&$colList, $list)
    {
        foreach($list as $k=>$row)
        {
            $url = url::action('topc_ctl_item@index',array('item_id'=>$row['item_id']));
            $colList[$k] = "<a href='".$url."'>".$row['title']."</a>";
        }
    }

    public $detail_basic = '基本信息';
    public function detail_basic($temaiId)
    {
        $pagedata = [];
        $filter = array('temai_id' => $temaiId);
        $temaiMdlObj = app::get('systemai')->model('temai');
        $temaiInfo = $temaiMdlObj->getRow("*" , $filter);
        if(!empty($temaiInfo['agreement'])){
            $temaiInfo['agreement'] = explode(',', $temaiInfo['agreement']);
        }
        if(!empty($temaiInfo['certificate'])){
            $temaiInfo['certificate'] = explode(',', $temaiInfo['certificate']);
        }
        if(!empty($temaiInfo['pricecerti'])){
            $temaiInfo['pricecerti'] = explode(',', $temaiInfo['pricecerti']);
        }

        if(empty($temaiInfo['item_id'])){
            return '';
        }

        $params['item_id'] = $temaiInfo['item_id'];
        $params['fields'] = "*,sku,item_store,item_status,item_count,item_desc,item_nature,spec_index";
        $pagedata = app::get('sysitem')->rpcCall('item.get',$params);

        $catParams = array(
            'user_id' => $temaiInfo['user_id'],
            'cat_id' =>$pagedata['cat_id'],
            'fields' => 'cat_id,cat_name,is_leaf,parent_id,level');
        $pagedata['catInfo'] = app::get('sysitem')->rpcCall('category.cat.get.data',$catParams);

        $pagedata['temaiInfo'] = $temaiInfo;
        return view::make('systemai/temai/detail.html',$pagedata)->render();
    }
}
