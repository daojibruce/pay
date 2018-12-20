<?php
/**
 * 商品添加、编辑
 * item.create
 */
class systemai_api_temai_create {

    public $apiDescription = "商品添加、编辑";

    public $use_strict_filter = true; // 是否严格过滤参数

    public function getParams()
    {
        $return['params'] = array(
            'user_id' => ['type'=>'string','valid'=>'required','description'=>'平台展销会员id','example'=>'18'],
            'agreement' => ['type'=>'string','valid'=>'required','description'=>'平台展销协议加盖公章附件','msg' => '平台展销协议加盖公章附件为必填项','example'=>''],
            'certificate' => ['type'=>'string','valid'=>'required','description'=>'授权证明文件','example'=>'','msg' => '授权证明文件为必填项'],
            'pricecerti' => ['type'=>'string','valid'=>'required','description'=>'销售证明附件','example'=>'','msg' => '销售证明附件为必填项'],
            'tmitem_id' => ['type'=>'field_list','valid'=>'required','description'=>'要获取的商品字段集 tmitem_id必填','msg' => '您必须选择需要平台展销的商品',
                'example'=>'item_id,title,item_store.store,item_status.approve_status','default'=>''],
            'tmitem_sku' => ['type'=>'field_list','valid'=>'required','description'=>'要获取的商品字段集 tmitem_sku必填',
                'example'=>'item_id,title,item_store.store,item_status.approve_status','default'=>''],
        );

        return $return;
    }

    public function itemCreate($params)
    {
        $tmItemId = $params['tmitem_id'];
        $tmItemSku = $params['tmitem_sku'];

        $imgFile = ['agreement' => $params['agreement'] , 'certificate' => $params['certificate'], 'pricecerti' => $params['pricecerti']];
        $result = kernel::single('systemai_data_temai')->add($tmItemId , $tmItemSku , $params['user_id'] , $imgFile);

        return $result;
    }
}
