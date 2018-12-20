<?php
/**
 * @brief 商家商品管理
 */
class toptemai_ctl_temai extends toptemai_controller {
    public $limit = 10;

    public function searchItem()
    {
        $t = "searchItem";
        return $t;
    }

    public function itemList()
    {
        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');
        $itemTitle = input::get('item_title', '');
        $status = input::get('status', '');
        $pages =  input::get('pages',1);
        $pagedata['search_keywords'] = $itemTitle;
        $pagedata['status'] = $status;
        $filter = array(
            'user_id' => $this->userId,
            'state' => $status,
            'page_no' =>intval($pages),
            'page_size' => intval($this->limit),
        );
        $filter['fields'] = 'temai_id,user_id,item_id,title,sub_title,price,temai_price,mkt_price,store,starttime,endtime,state,addtime,passtime,lasttime,cat,sale_num,click_num,batch,reason';
        $filter['orderBy'] = 'lasttime desc';
        $filter['temai_title'] = $itemTitle;
        $filter['min_price'] = input::get('min_price', '');
        $filter['max_price'] = input::get('max_price', '');

        $pagedata['min_price'] = $filter['min_price'];
        $pagedata['max_price'] = $filter['max_price'];
        $filter['containDetail'] = false;
        $searchItems = kernel::single('systemai_temai_searchItems');
        $temaiList = $searchItems->getTemaiList($filter);

        $pagedata['temai_list'] = $temaiList['list'];
        $pagedata['total'] = $temaiList['total_found'];

        $totalPage = ceil($temaiList['total_found']/$this->limit);
        $pagersFilter['pages'] = time();
        $pagersFilter['status'] = $status;
        $pagers = array(
            'link'=>url::action('toptemai_ctl_temai@itemList',$pagersFilter),
            'current'=>$pages,
            'use_app' => 'toptemai',
            'total'=>$totalPage,
            'token'=>time(),
        );
        $pagedata['pagers'] = $pagers;

        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');
        $this->contentHeaderTitle = app::get('toptemai')->_('平台展销列表');
        $pagedata['setting'] = true;

        return $this->page('toptemai/temai/list.html', $pagedata);
    }

    public function add()
    {
        $pagedata['temai_edit'] = 0;
        $temaiId = input::get('temaiid', '0');
        if($temaiId > 0){
            $pagedata['temai_edit'] = 1;
            $pagedata['temai_id'] = $temaiId;
            $filter = array('temai_id' => $temaiId , 'user_id' => $this->userId);
            $temaiItemRow = app::get('systemai')->model('temai')->getRow('item_id' , $filter);
            $pagedata['temai_item_id'] = intval($temaiItemRow['item_id']);

            if($pagedata['temai_item_id'] < 1){
                redirect::action('toptemai_ctl_temai@itemList')->send();exit;
            }
        }

        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');
        $pagedata['userId'] = $this->userId;

        $this->getItems($pagedata);
        // 获取物流模板列表
        $tmpParams = array(
            'user_id' => $this->userId,
            'status' => 'on',
            'fields' => 'user_id,name,template_id',
        );
        $pagedata['itemlist'] = app::get('toptemai')->rpcCall('logistics.temaitmpl.get.list',$tmpParams);

        $this->contentHeaderTitle = app::get('toptemai')->_('申请平台展销');
        return $this->page('toptemai/temai/edit.html', $pagedata);
    }

    public function getItems(&$pagedata)
    {
        $status = input::get('status',false);
        $pages =  input::get('pages',1);
        $pagedata['status'] = $status;
        $filter = array(
            'user_id' => $this->userId,
            'approve_status' => $status,
            'page_no' =>intval($pages),
            'page_size' => intval($this->limit),
        );

        $filter['fields'] = 'item_id,list_time,modified_time,title,image_default_id,price,approve_status';
        $filter['orderBy'] = 'modified_time desc';
        $searchItems = kernel::single('systemai_item_searchItems');
        $itemsList = $searchItems->getList($filter);
        $pagedata['item_list'] = $itemsList['list'];
        $pagedata['total'] = $itemsList['total_found'];

        $totalPage = ceil($itemsList['total_found']/$this->limit);
        $pagersFilter['pages'] = time();
        $pagersFilter['status'] = $status;
        $pagers = array(
            'link'=>url::action('toptemai_ctl_temai@itemList',$pagersFilter),
            'current'=>$pages,
            'use_app' => 'toptemai',
            'total'=>$totalPage,
            'token'=>time(),
        );
        $pagedata['pagers'] = $pagers;
    }

    public function ajaxGetItem()
    {
        $params['user_id'] = $this->userId;
        $params['cat_id'] = input::get('cat_id');
        try
        {
            $brand = app::get('toptemai')->rpcCall('category.tmget.cat.rel.brand',$params);
        }
        catch(Exception $e)
        {
            return $this->splash('error',null, $e->getMessage(), true);
        }
        return response::json($brand);exit;
    }

    public function storeItem()
    {
        $postData = input::get();
        $postData['user_id'] = $this->userId;
        try
        {
            // 格式化参数
            $result = app::get('toptemai')->rpcCall('temai.temai.create',$postData);
            if($result < 0){
                $msg = app::get('toptemai')->_('今日平台展销推送数量已超标，超出数量为：' . abs($result) . ' 个');
                return $this->splash('error', '', $msg, false);
            }else if($result)
            {
                $url = url::action('toptemai_ctl_temai@itemList');
                $msg = app::get('toptemai')->_('保存成功');
                return $this->splash('success', $url, $msg, true);
            }
        }
        catch (Exception $e)
        {
            return $this->splash('error', '', $e->getMessage(), true);
        }
    }

    public function deleteItem()
    {
        $temaiId = input::get('item_id');
        $filter = array("temai_id" => $temaiId);
        $temaiMdlObj = app::get("systemai")->model("temai");
        $rs = $temaiMdlObj->delete($filter);
        if($rs){
            //已经推送的数量
            kernel::single('base_unique')->set_number_day('limit_temai', count($temaiId) , true);
            kernel::single('systemai_mdl_temailist')->temaidayDelete($filter);
            $url = url::action('toptemai_ctl_temai@itemList');
            $msg = app::get('toptemai')->_('删除成功');
            $splash = $this->splash('success', $url, $msg, true);
        }else{
            $msg = app::get('toptemai')->_('删除失败...');
            $splash = $this->splash('error', '', $msg, true);
        }

        return $splash;
    }

    public function setItemStatus()
    {
        $actionType = input::get('type');
        $temaiId = input::get('item_id');

        $temaiMdlObj = app::get("systemai")->model("temai");

        //查询出 item_id
        $filter = array("temai_id" => $temaiId);
        $itemRow = $temaiMdlObj->getRow("item_id", $filter);
        $itemStatusMdlObj = app::get("sysitem")->model("item_status");

        $rs = 0;
        if('tostock' == $actionType){
            $filter = array("temai_id" => $temaiId , "user_id" => $this->userId);
            $data = array("state" => 2);
            $rs = $temaiMdlObj->update($data , $filter);

            if($rs){
                $data = array('approve_status' => "instock");
                $filter = array("item_id" => $itemRow['item_id']);
                $itemStatusMdlObj->update($data , $filter);

                //平台展销排期 删除
                $temaiFilter = array("temai_id" => $temaiId);
                kernel::single('systemai_mdl_temailist')->temaidayDelete($temaiFilter);

                $url = url::action('toptemai_ctl_temai@itemList');
                $msg = app::get('toptemai')->_('下架成功');
                $splash = $this->splash('success', $url, $msg, true);
            }else{
                $msg = app::get('toptemai')->_('下架失败');
                $splash = $this->splash('error', '', $msg, true);
            }
        }else if('tosell' == $actionType){
            $filter = array("temai_id" => $temaiId , "user_id" => $this->userId);
            $data = array("state" => 0);
            $rs = $temaiMdlObj->update($data , $filter);

            if($rs){
                $data = array('approve_status' => "pending");
                $filter = array("item_id" => $itemRow['item_id']);
                $itemStatusMdlObj->update($data , $filter);

                $url = url::action('toptemai_ctl_temai@itemList');
                $msg = app::get('toptemai')->_('上架成功');
                $splash = $this->splash('success', $url, $msg, true);
            }else{
                $msg = app::get('toptemai')->_('上架失败');
                $splash = $this->splash('error', '', $msg, true);
            }
        }

        return $splash;
    }

}