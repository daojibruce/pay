<?php
class sysproofing_finder_provider{

    public $column_edit = '操作';
    public $column_edit_order = 2;



    public function column_edit(&$colList, $list)
    {

        foreach($list as $k=>$row)
        {
            if ($row['enabled'] != '1') {
                $url = '?app=sysproofing&ctl=admin_proofing&act=startReview&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$row['provider_id'];
                $target = 'dialog::  {title:\''.app::get('sysproofing')->_('审核').'\', width:800, height:600}';
                $title = app::get('sysproofing')->_('审核');

                $colList[$k] = '<a href="' . $url . '" target="' . $target . '">' . $title . '</a>';
            } else {
                $url = '?app=sysproofing&ctl=admin_proofing&act=cancelProvider&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$row['provider_id'];
                $title = app::get('sysproofing')->_('取消资格');
                $url2 = '?app=sysproofing&ctl=admin_proofing&act=providerDetail&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$row['provider_id'];
                $target2 = 'dialog::  {title:\''.app::get('sysproofing')->_('查看').'\', width:800, height:600}';
                $title2 = app::get('sysproofing')->_('查看');
                $colList[$k] = '<a href="' . $url . '">' . $title . '</a>|<a href="' . $url2 . '" target="'.$target2.'">' . $title2 . '</a>';
            }
        }
    }

}
