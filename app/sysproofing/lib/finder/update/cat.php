<?php
class sysproofing_finder_update_cat{

    public $column_edit = '操作';
    public $column_edit_order = 2;



    public function column_edit(&$colList, $list)
    {
        foreach($list as $k=>$row)
        {
            $url = '?app=sysproofing&ctl=admin_proofing&act=newCategoryReview&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$row['provider_id'];
            $target = 'dialog::  {title:\''.app::get('sysproofing')->_('审核').'\', width:500, height:400}';
            $title = app::get('sysproofing')->_('审核');


            $colList[$k] = '<a href="' . $url . '" target="' . $target . '">' . $title . '</a>';
        }
    }

}
