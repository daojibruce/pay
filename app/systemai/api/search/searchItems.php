<?php

/**
 * 接口作用说明
 * item.search
 */
class systemai_api_search_searchItems {

    public $apiDescription = '根据条件获取平台展销商品列表';

    public $defaultFields = 'temai_id,user_id,item_id,title,sub_title,store,sale_num,price,starttime,endtime';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams() {
        $return['params'] = array (
            'item_id' => ['type' => 'string', 'valid' => '', 'description' => '商品id，多个id用，隔开', 'example' => '2,3,5,6', 'default' => ''],
            'shop_id' => ['type' => 'int', 'valid' => 'integer', 'description' => '店铺id', 'example' => '', 'default' => ''],
            'dlytmpl_id' => ['type' => 'int', 'valid' => 'integer', 'description' => '运费模板id', 'example' => '', 'default' => ''],
            'shop_cat_id' => ['type' => 'int', 'valid' => 'string', 'description' => '店铺自有类目id', 'example' => '', 'default' => ''],
            'search_shop_cat_id' => ['type' => 'int', 'valid' => '', 'description' => '店铺搜索自有一级类目id', 'example' => '', 'default' => ''],
            'cat_id' => ['type' => 'string', 'valid' => '', 'description' => '商城类目id', 'example' => '1,3', 'default' => ''],
            'brand_id' => ['type' => 'string', 'valid' => '', 'description' => '品牌ID', 'example' => '1,2,3', 'default' => ''],
            'prop_index' => ['type' => 'string', 'valid' => '', 'description' => '商品自然属性', 'example' => '', 'default' => ''],
            'search_keywords' => ['type' => 'string', 'valid' => '', 'description' => '搜索商品关键字', 'example' => '', 'default' => ''],
            'buildExcerpts' => ['type' => 'bool', 'valid' => '', 'description' => '是否关键字高亮', 'example' => '', 'default' => ''],
            'is_selfshop' => ['type' => 'bool', 'valid' => '', 'description' => '是否是自营', 'example' => '', 'default' => ''],
            'use_platform' => ['type' => 'string', 'valid' => '', 'description' => '商品使用平台(0=全部支持,1=仅支持pc端,2=仅支持移动端)如果查询不限制平台，则不需要传入该参数', 'example' => '1', 'default' => '0'],
            'min_price' => ['type' => 'int', 'valid' => 'numeric', 'description' => '搜索最小价格', 'example' => '', 'default' => ''],
            'max_price' => ['type' => 'int', 'valid' => 'numeric', 'description' => '搜索最大价格', 'example' => '', 'default' => ''],
            'bn' => ['type' => 'string', 'valid' => '', 'description' => '搜索商品货号', 'example' => '', 'default' => ''],

            'approve_status' => ['type' => 'string', 'valid' => '', 'description' => '商品上架状态', 'example' => '', 'default' => ''],
            'page_no' => ['type' => 'int', 'valid' => 'numeric', 'description' => '分页当前页码,1<=no<=499', 'example' => '', 'default' => '1'],
            'page_size' => ['type' => 'int', 'valid' => 'numeric', 'description' => '分页每页条数(1<=size<=200)', 'example' => '', 'default' => '40'],
            'orderBy' => ['type' => 'string', 'valid' => '', 'description' => '排序方式', 'example' => '', 'default' => 'modified_time desc,list_time desc'],
            'fields' => ['type' => 'field_list', 'valid' => '', 'description' => '要获取的商品字段集', 'example' => '', 'default' => ''],
        );
        $return['extendsFields'] = ['item'];
        return $return;
    }

    /**
     * 获取一天所有场次
     * @return mixed
     */
    public function getScreenStatus() {
        $screenData = $this->_getScreenData();
        $nowTime = date('H:i:s');
        $result = array();
        foreach ($screenData as $key=>$val) {
            $val['starttime'] = date('H:i', $this->_getTodayFullTime($val['starttime']));
            $result[$key] = $val;
            if($nowTime < $val['starttime']) {
                $result[$key]['status'] = 'off';
            } elseif($nowTime >= $val['starttime'] && $nowTime <= $val['endtime']) {
                $result[$key]['status'] = 'online';
            } else {
                $result[$key]['status'] = 'out';
            }
        }
        return $result;
    }

    /**
     * 获取场次剩余时间
     * @param $screen
     */
    public function getRemtime($screen = null) {
        $result = $this->getScreen($screen);
        //$result = time() >= $this->_getTodayFullTime($result['endtime'])?0:($this->_getTodayFullTime($result['endtime'])-time());
        return $result['endtime'];
    }

    public function getList($params) {
        $objMdlItem = app::get('systemai')->model('temai');

        $row = $params['fields']['rows'] ? $params['fields']['rows'] : $this->defaultFields;

        //分页使用
        $pageSize = $params['page_size'] ? $params['page_size'] : 40;
        $pageNo = $params['page_no'] ? $params['page_no'] : 1;
        $max = 1000000;
        if ($pageSize >= 1 && $pageSize < 500 && $pageNo >= 1 && $pageSize * $pageNo < $max) {
            $limit = $pageSize;
            $page = ($pageNo - 1) * $limit;
        }

        $orderBy = $params['orderBy'];
        if (!$params['orderBy']) {
            $orderBy = "endtime asc, temai_id desc";
        }
        $data['list'] = $objMdlItem->getList($row, $this->__getFilter($params), $page, $limit, $orderBy);
        $itemIds = array_column($data['list'], 'item_id');
        if ($itemIds) {
            $itemList = app::get('sysitem')->rpcCall('item.list.get', array ('item_id' => implode(',', $itemIds), 'fields' => 'item_id,title,price,image_default_id'));
            $itemList = array_bind_key($itemList, 'item_id');
            $data['itemList'] = $itemList;
        }
        return $data;
    }

    /**
     * 根据场次id获取场次时间段，默认根据当前时间获取
     * @param $screen
     * @return mixed|string
     */
    public function getScreen($screen) {
        $screenData = $this->_getScreenData();
        if (isset($screen) && $screen && isset($screenData[$screen])) {
            return $screenData[$screen];
        }
        $nowTime = date('H:i:s');
        return $this->_getScreen($nowTime);
    }

    /**
     * 限时还是限量
     * @return array
     */
    private function _getType() {
        return array (
            'timeLimit', 'numLimit'
        );
    }

    /**
     * 默认一天场次时间段配置
     * @return array
     */
    private function _getScreenData() {
        return array (
            1 => array ('starttime' => '08:00:00', 'endtime' => '08:59:59'),
            2 => array ('starttime' => '09:00:00', 'endtime' => '14:59:59'),
            3 => array ('starttime' => '15:00:00', 'endtime' => '16:59:59'),
            4 => array ('starttime' => '17:00:00', 'endtime' => '20:59:59'),
            5 => array ('starttime' => '21:00:00', 'endtime' => '23:59:59'),
        );
    }

    /**
     * 根据当前时间获取当前场次
     */
    private function _getScreen($time) {
        $result = '';
        $i = 0;
        $screen = $this->_getScreenData();
        foreach ($screen as $key => $val) {
            if ($i == 0) {
                if ($time < $val['starttime']) {
                    $result = $screen[$key];
                    break;
                } elseif ($time >= $val['starttime'] && $time <= $val['endtime']) {
                    $result = $screen[$key];
                    break;
                }
            } else {
                if ($time >= $val['starttime'] && $time <= $val['endtime']) {
                    $result = $screen[$key];
                    break;
                }
            }
            $i++;
        }
        return $result;
    }

    private function __getFilter($params) {
        if (isset($params['type']) && in_array($params['type'], $this->_getType()) && $params['type'] == 'timeLimit') {
            $screen = $this->getScreen($params['screen']);
            $filter['starttime|bthan'] = $this->_getTodayFullTime($screen['starttime']);
            $filter['endtime|sthan'] = $this->_getTodayFullTime($screen['endtime']);
        }

        if (isset($params['type']) && in_array($params['type'], $this->_getType()) && $params['type'] == 'numLimit') {

        }

        if (isset($params['catids']) && $params['catids']) {
            foreach ($params['catids'] as $v) {
                $catids[] = intval($v);
            }
            $filter['cat'] = $catids;
        }

        $filter['state'] = !isset($params['status'])?1:$params['status'];
        return $filter;
    }

    /**
     * 根据当前几点几分获取完整的unix时间戳
     * @param $time
     * @return false|int|string
     */
    private function _getTodayFullTime($time) {
        if (!$time) {
            return $time;
        }
        $date = date('Y-m-d');
        $time = $date.' '.$time;
        return strtotime($time);
    }
}
