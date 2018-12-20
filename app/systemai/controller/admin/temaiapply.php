<?php
/**
 * @brief 平台展销商品列表
 */
class systemai_ctl_admin_temaiapply extends desktop_controller{

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
                'label'=>app::get('systemai')->_('批量审核通过'),
                'icon' => 'download.gif',
                'submit' => '?app=systemai&ctl=admin_temaiapply&act=dopass',
				'target' => "dialog::{title:'审核通过',width:.4,height:.3}",
            ),
            array(
                'label'=>app::get('systemai')->_('批量审核不通过'),
                'icon' => 'download.gif',
                'submit' => '?app=systemai&ctl=admin_temaiapply&act=refuse',
                'target' => "dialog::{title:'批量审核不通过',width:.4,height:.3}",
            ),
        );
        return $this->finder('systemai_mdl_temaiapply',array(
            'use_buildin_set_tag' => false,
            'use_buildin_tagedit' => true,
            'use_buildin_filter'=> true,
            'use_buildin_refresh' => true,
            'use_buildin_delete' => false,
            //'allow_detail_popup' => true,
            'title' => app::get('systemai')->_('申请平台展销商列表'),
            'actions' => $actions,
        ));
    }

	/**
	 * @brief 上架平台展销商品
	 *
	 * @return
     */
    public function dopass()
    {
        $pagedata = input::get();

        $filter = array("temai_server_id" => $pagedata['temai_server_id']);
        $temaiapplyMdlObj = app::get("systemai")->model('temaiapply');
        $pagedata['temaiapply'] = $temaiapplyMdlObj->getRow("*" , $filter);

        $pagedata['temai_server_ids'] = implode(',', $pagedata['temai_server_id']);
        return view::make('systemai/temaiapply/onsale.html', $pagedata);
    }

	/**
	 * @brief 批量上架平台展销商品
	 *
	 * @return
     */
    public function saveonsale()
    {
        $this->begin('?app=systemai&ctl=admin_temaiapply&cat=index');
        $temai_ids = $_POST['temai_server_ids'];

        $temai_ids = explode(',', trim($temai_ids));
        $temaiapplyMdlObj = app::get("systemai")->model('temaiapply');
        $filter = array("temai_server_id" => $temai_ids);
        $result = 0 ;
        $temaiapplyUser = $temaiapplyMdlObj->getRow("user_id,server_mobile", $filter);
        if ($temaiapplyUser['user_id'] > 0) {
            $result = userAuth::setUserRoles($temaiapplyUser['user_id'], SYS_USER_ROLE_SPECICAL, '+');
            if($result){
                $applyData = array("status" => 1);
                $result = $temaiapplyMdlObj->update($applyData, $filter);
            }
        }
        if ($result) {
            logger::info('平台展销审核信息发送开始：');
            try {
                if($temaiapplyUser['server_mobile']){
                    $content['status']="agree";
                    messenger::sendSms($temaiapplyUser['server_mobile'],'temai-apply',$content);
                }
            }catch(Exception $e) {
                logger::info('sendsms error:' . var_export($e->getMessage(), 1));
            }
            logger::info('平台展销审核信息发送结束');
        }
        $this->adminlog("平台展销商家通过审核", $result ? 1 : 0);
        $this->end($result, $result ? '平台展销商家通过审核': '平台展销商家审核失败');
    }

    /**
     * @brief 批量不通过平台展销商品主页
     *
     * @return
     */
    public function refuse()
    {
        $pagedata = input::get();
        $temai_server_id = implode(',', $pagedata['temai_server_id']);
        $pagedata['temai_server_id'] = $temai_server_id;
        return view::make('systemai/temaiapply/refuse.html', $pagedata);}

    /**
     * @brief 批量不通过平台展销商品
     *
     * @return
     */
    public function dorefuse()
    {
        $this->begin('?app=systemai&ctl=admin_temaiapply&cat=index');
        $pagedata = input::get();
        $pagedata['reson_refuse'] = trim($pagedata['reson_refuse']);

        if(!$pagedata['reson_refuse']) return $this->splash('error',null,'请填写驳回原因',true);

        $temai_ids = explode(',', trim($pagedata['temai_server_id']));
        $temaiapplyMdlObj = app::get("systemai")->model('temaiapply');
        $filter = array("temai_server_id" => $temai_ids);
        $applyData = array("status" => 2 , 'reson_refuse' => $pagedata['reson_refuse']);
        $result = $temaiapplyMdlObj->update($applyData , $filter);

        $this->adminlog("拒绝平台展销商家成功", $result ? 1 : 0);
        $this->end($result, $result ? '拒绝平台展销商家成功' : '拒绝平台展销商家失败');
    }

    public function _views()
    {
		$subMenu = array(
			0=>array(
				'label'=>app::get('systemai')->_('全部'),
				'optional'=>false,
			),
			1=>array(
				'label'=>app::get('systemai')->_('待审核'),
				'optional'=>false,
				'filter'=>array(
					'status'=>'0',
				),
			),
			2=>array(
				'label'=>app::get('systemai')->_('已通过'),
				'optional'=>false,
				'filter'=>array(
                    'status'=>'1',
				),
			),
			3=>array(
				'label'=>app::get('systemai')->_('已拒绝'),
				'optional'=>false,
				'filter'=>array(
                    'status'=>'2',
				),
			),
		);

        return $subMenu;
    }
}


