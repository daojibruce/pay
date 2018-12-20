<?php

class topc_ctl_brand extends topc_controller{

	public function __construct() {
		parent::__construct();
        $this->setLayoutFlag('brands');
		$GLOBALS['runtime']['path'][] = array ('title' => app::get('topc')->_('品牌列表'), 'link' => kernel::base_url(1));
	}

    function index()
    {
        $categoryMdl = app::get('syscategory')->model('brand');

        $filter = array(
            'disabled' => 0,
        );
        $brandData = $categoryMdl->getList('brand_name,brand_logo,firstLetter', $filter);

        if(!$brandData) {
            $pagedata['valid'] = 1;
        }

        $pagedata['brands'] = $this->sortByName($brandData);
        foreach ($pagedata['brands'] as $key=>$val) {
            $pagedata['tips'][] = $key;
        }
        //echo "<pre>";print_r($pagedata);exit;
        return $this->page('topc/brand/index.html', $pagedata);
    }

    //品牌按首字母排序
    public function sortByName($brandData)
    {
        $letterList = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','0-9');
        $brands = [];
        foreach ($letterList as $v) {
            $brands[$v] = array();
            foreach ($brandData as $brand) {
                if ($v == $brand['firstLetter']) {
                    $brands[$v][] = $brand;
                }
            }
        }

        return $brands;
    }

    //获取首字母并插入
    public function insertLetter()
    {
        $pinyin = new Overtrue\Pinyin\Pinyin();

        $brandMdl = app::get('syscategory')->model('brand');
        $brands = $brandMdl->getList('brand_id,brand_name');
        foreach ($brands as $brand) {
            $firstLetter = mb_substr($pinyin->abbr($brand['brand_name']),0,1);
            $firstLetter = strtoupper($firstLetter);
            if (in_array($firstLetter,['0','1','2','3','4','5','6','7','8','9'])) {
                $firstLetter = "0-9";
            }
            $data = [
                'brand_id' => $brand['brand_id'],
                'firstLetter' => $firstLetter
            ];
            $brandMdl->save($data);
        }

        $shopMdl = app::get('sysshop')->model('shop');
        $brands = $shopMdl->getList('shop_id,shop_name');
        foreach ($brands as $brand) {
            $firstLetter = mb_substr($pinyin->abbr($brand['shop_name']),0,1);
            $firstLetter = strtoupper($firstLetter);
            if (in_array($firstLetter,['0','1','2','3','4','5','6','7','8','9'])) {
                $firstLetter = "0-9";
            }
            $data = [
                'shop_id' => $brand['shop_id'],
                'firstLetter' => $firstLetter
            ];
            $shopMdl->save($data);
        }
        return true;
    }
}
