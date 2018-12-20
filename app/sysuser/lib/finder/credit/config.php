<?php
class sysuser_finder_credit_config{

    public $column_edit = "编辑";
    public $column_edit_order = 1;
    public function column_edit(&$colList, $list)
    {
        foreach($list as $k=>$row)
        {
            $url = '?app=sysuser&ctl=admin_creditconfig&act=add&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$row['id'];
            $target = 'dialog::{title:\''.app::get('sysuser')->_('积分基础项编辑').'\', width:500, height:249}';
            $title = app::get('sysuser')->_('编辑');
            $button = '<a href="' . $url . '" target="' . $target . '">' . $title . '</a>';

            $colList[$k] = $button;
        }
    }
}
