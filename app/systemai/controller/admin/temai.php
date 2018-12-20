<?php
/**
 * @brief 平台展销商品列表
 */
class systemai_ctl_admin_temai extends desktop_controller{

    public $workground = 'systemai.workground.temai';

    /**
     * @brief	列表
     *
     * @return
     */
    public function index()
    {
        $actions = array(
            array(
                'label'=>app::get('systemai')->_('批量审核上架'),
                'icon' => 'download.gif',
                'submit' => '?app=systemai&ctl=admin_temai&act=onsale',
				'target' => "dialog::{title:'批量上架',width:.4,height:.8}",
            ),
            array(
                'label'=>app::get('systemai')->_('批量下架'),
                'icon' => 'download.gif',
                'submit' => '?app=systemai&ctl=admin_temai&act=disable',
                'confirm' => app::get('systemai')->_('确定要下架选中商品？'),
            ),
            array(
                'label'=>app::get('systemai')->_('批量审核不通过'),
                'icon' => 'download.gif',
                'submit' => '?app=systemai&ctl=admin_temai&act=refuse',
                'target' => "dialog::{title:'批量审核不通过',width:.4,height:.3}",
            ),
        );
        return $this->finder('systemai_mdl_temai',array(
            'use_buildin_set_tag' => false,
            'use_buildin_tagedit' => true,
            'use_buildin_filter'=> true,
            'use_buildin_refresh' => true,
            'use_buildin_delete' => false,
            //'allow_detail_popup' => true,
            'title' => app::get('systemai')->_('平台展销商品列表'),
            'actions' => $actions,
        ));
    }

	/**
	 * @brief 上架平台展销商品
	 *
	 * @return
     */
    public function onsale()
    {
        $pagedata = input::get();
        $temaiIdList = $pagedata['temai_id'];
		$pagedata['cat_conf'] = config::get('temaiconf');

        $pagedata['temai_ids'] = implode(',', $pagedata['temai_id']);
        return view::make('systemai/temai/onsale.html', $pagedata);
    }

	/**
	 * @brief 批量上架平台展销商品
	 *
	 * @return
     */
    public function saveonsale()
    {
        $this->begin('?app=systemai&ctl=admin_temai&cat=index');
        $temai_ids = $_POST['temai_ids'];
        $postdata['cat'] = $_POST['cat'];
        $postdata['store'] = $_POST['add_store'];

        $postdata['add_time_start'] = $_POST['add_time_start'];
        $postdata['add_days'] = $_POST['add_days'];
        $postdata['price_type'] = $_POST['price_type'];
        $postdata['add_price'] = $_POST['add_price'];
        $postdata['state'] = 1;

        $ojbItem = kernel::single('systemai_data_temai');

        $msg= '';
        $result = $ojbItem->batchPassUpdate($postdata, $temai_ids, $msg);
        $this->adminlog("平台展销商品上架", $result ? 1 : 0);
        $this->end($result, $msg , '');
    }

    /**
	 * @brief 批量下架平台展销商品
	 *
	 * @return
     */
    public function disable()
    {
        $this->begin('?app=systemai&ctl=admin_temai&cat=index');
        $pagedata = input::get();
        $temai_ids = $pagedata['temai_id'];
        $ojbItem = kernel::single('systemai_data_temai');

        $result = $ojbItem->batchDisableUpdate(['state' => 2], $temai_ids,$msg);
        $this->adminlog("平台展销商品下架", $result ? 1 : 0);
        $this->end($result, $result ? '下架平台展销商品成功' : '下架平台展销商品失败');
    }

    /**
     * @brief 批量不通过平台展销商品主页
     *
     * @return
     */
    public function refuse()
    {
        $pagedata = input::get();
        $temai_ids = implode(',', $pagedata['temai_id']);
        $pagedata['temai_ids'] = $temai_ids;
        return view::make('systemai/temai/refuse.html', $pagedata);
    }
    /**
     * @brief 批量不通过平台展销商品
     *
     * @return
     */
    public function dorefuse()
    {
        $this->begin('?app=systemai&ctl=admin_temai&cat=index');
        $pagedata = input::get();
        $pagedata['reason'] = trim($pagedata['reason']);

        if(!$pagedata['reason']) return $this->splash('error',null,'请填写驳回原因',true);

        $temai_ids = trim($pagedata['temai_id']);
        $ojbItem = kernel::single('systemai_data_temai');
        $result = $ojbItem->batchRefuseUpdate(['state' => 3, 'reason' => $pagedata['reason']], $temai_ids, $msg);
        $this->adminlog("平台展销商品审核不通过", $result ? 1 : 0);
        $this->end($result, $result ? '平台展销商品审核不通过成功' : '平台展销商品审核不通过失败');
    }

    public function _views()
    {
		$subMenu = array(
			0=>array(
				'label'=>app::get('sysitem')->_('全部'),
				'optional'=>false,
			),
			1=>array(
				'label'=>app::get('sysitem')->_('待上架(待审核)'),
				'optional'=>false,
				'filter'=>array(
					'state'=>'0',
				),
			),
			2=>array(
				'label'=>app::get('sysitem')->_('已上架'),
				'optional'=>false,
				'filter'=>array(
                    'state'=>'1',
				),
			),
			3=>array(
				'label'=>app::get('sysitem')->_('已下架'),
				'optional'=>false,
				'filter'=>array(
                    'state'=>'2',
				),
			),
			4=>array(
				'label'=>app::get('sysitem')->_('审核未通过'),
				'optional'=>false,
				'filter'=>array(
                    'state'=>3,
				),
			),
		);

        return $subMenu;
    }
}


