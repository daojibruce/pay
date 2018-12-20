<?php
class syscontent_finder_others{

    public $column_look = "预览";
    public $column_look_order = 2;
    public $column_look_width = 50;
    public function column_look(&$colList, $list)
    {
        foreach($list as $k=>$row)
        {
            $url = url::action('topc_ctl_content@getOtherContentInfo',array('article_id'=>$row['article_id']));
            $target = '_blank';
            $title = app::get('syscontent')->_('预览');
            $button = '<a href="' . $url . '" target="' . $target . '">' . $title . '</a>';
            $colList[$k] = $button;
        }
    }

}