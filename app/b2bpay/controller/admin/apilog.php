<?php
/**
 * @brief 平安见证宝交易日志
 */
class b2bpay_ctl_admin_apilog extends desktop_controller {

    /**
     * @brief  平台操作日志
     *
     * @return
     */
    public function index()
    {
        return $this->finder('b2bpay_mdl_api_log',array(
            'use_buildin_delete' => false,
            'title' => app::get('system')->_('见证宝API请求日志'),
            'actions'=>array(),
        ));
    }

    public function log(){
        return $this->finder('b2bpay_mdl_jzb_paylog',array(
            'use_buildin_delete' => false,

            'use_buildin_filter'=> true,
            'title' => app::get('system')->_('见证宝交易日志'),
            'actions'=>array(),
        ));

    }
}


