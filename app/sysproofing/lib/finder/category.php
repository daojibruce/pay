<?php
class sysproofing_finder_category{

    public $column_edit = '操作';
    public $column_edit_order = 2;



    public function column_edit(&$colList, $list)
    {
        foreach($list as $k=>$row)
        {
            $url = '?app=sysproofing&ctl=admin_proofing&act=categoryEdit&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$row['cat_id'];
            $target = 'dialog::  {title:\''.app::get('sysproofing')->_('编辑').'\', width:500, height:400}';
            $title = app::get('sysproofing')->_('编辑');

            $url2 = '?app=sysproofing&ctl=admin_proofing&act=categoryDelete&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$row['cat_id'];
            $title2 = app::get('sysproofing')->_('删除');

            $colList[$k] = '<a href="' . $url . '" target="' . $target . '">' . $title . '</a>|<a href="'. $url2  . '">' . $title2 . '</a>';
        }
    }

}
