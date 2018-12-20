<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysuser_finder_referee {

    public $column_edit = '操作';
    public $column_edit_order = 2;



    public function column_edit(&$colList, $list)
    {
        foreach($list as $k=>$row)
        {
            $url = '?app=sysuser&ctl=admin_referee&act=edit&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$row['referee_id'];
            $target = 'dialog::  {title:\''.app::get('sysuser')->_('编辑').'\', width:500, height:400}';
            $title = app::get('sysuser')->_('编辑');

            $url2 = '?app=sysuser&ctl=admin_referee&act=doDelete&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$row['referee_id'];
            $title2 = app::get('sysuser')->_('删除');

            $colList[$k] = '<a href="' . $url . '" target="' . $target . '">' . $title . '</a>|<a href="'. $url2  . '">' . $title2 . '</a>';
        }
    }

}

