<?php
return array(
    'columns'=>array(
        'shop_id'=>array(
            'type'=>'table:account@sysuser',
            //'pkey'=>true,
            'required' => true,
            'comment' => app::get('systemai')->_(' 关联平台展销商id'),
        ),
        'brand_id'=>array(
            'type'=>'table:brand@syscategory',
            //'pkey'=>true,
            'required' => true,
            'comment' => app::get('systemai')->_(' 关联品牌id'),
        ),
        'brand_warranty'=>array(
            //'type'=>'varchar(50)',
            'type' => 'string',
            'comment' => app::get('systemai')->_('品牌授权书'),
        ),
    ),

    'primary' => ['shop_id', 'brand_id'],
    'comment' => app::get('systemai')->_('店铺品牌关联表'),
);
