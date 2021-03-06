<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topshop_ctl_shop_decorate extends topshop_controller {

    public $headerTitle = [
        'nav' => ['default'=>'导航菜单','add' => '导航菜单配置'],
        'shopsign' => ['default'=>'店铺招牌配置'],
        'custom' => ['default'=>'自定义区域配置'],
        'showitems' => ['default'=>'多商品展示列表','add' => '多商品展示列表配置'],
    ];

    //显示装修店铺主页面
    public function index()
    {
        $confg = shopWidgets::config();
        $pagedata['config'] = $confg['pc'];

        if( input::get('show',false) )
        {
            $pagedata['showdialog'] = input::get('show');
            $params = explode('-',input::get('show'));
            try {
                $dialogData =  shopWidgets::getDialogData($params[0], $params[1], $this->shopId);
                $pagedata['data'] = $dialogData['data'];
                $pagedata['widgets_id'] = $dialogData['widgets_id'];
                $html = "topshop/shop/decorate/widgets/{$params[0]}/{$params[1]}.html";
            } catch (Exception $e) {
                return redirect::action('topshop_ctl_shop_decorate@index');
            }
            $pagedata['show_widgetsName'] = $params[0];
            $pagedata['show_dialogName'] = $params[1];
            $pagedata['html_file'] = $html;
        }

        $this->contentHeaderTitle = app::get('topshop')->_('店铺装修');
        return $this->page('topshop/shop/decorate.html', $pagedata);
    }

    //弹出框页面显示
    public function dialog()
    {

        $widgetsName = input::get('widgets');
        $dialogName = input::get('dialog');
        try {
            $dialogData =  shopWidgets::getDialogData($widgetsName, $dialogName, $this->shopId);
            $pagedata['data'] = $dialogData['data'];
            $pagedata['widgets_id'] = $dialogData['widgets_id'];
        } catch (Exception $e) {
            #echo $e->getMessage();
        }

        //面包屑
        $this->runtimePath = array(
            ['url'=> url::action('topshop_ctl_shop_decorate@index'),'title' => app::get('topshop')->_('店铺装修')],
            ['url'=> url::action('topshop_ctl_shop_decorate@dialog',['widgets'=>$widgetsName,'dialog'=>'default']),'title' => $this->headerTitle[$widgetsName]['default']],
        );

        if( $dialogName != 'default')
        {
            $this->runtimePath[]['title'] = $this->headerTitle[$widgetsName][$dialogName];
        }

        $this->contentHeaderTitle = $this->headerTitle[$widgetsName]['default'];
        return $this->page("topshop/shop/decorate/widgets/{$widgetsName}/{$dialogName}.html", $pagedata);
    }

    //保存配置
    public function save()
    {

        $showdialog = input::get('showdialog',false);

        $params = input::get('params');

        try
        {
            // 判断表单数据的合法性
           $this->_checkParams($params);
            
            shopWidgets::setParams( input::get('widgets_id'), input::get('widgets'), input::get('dialog'), input::get('params'),$this->shopId);
        }
        catch (Exception $e)
        {
            return $this->splash('error', null, $e->getMessage(), true);
        }

        if( $showdialog )
        {
            $url = url::action('topshop_ctl_shop_decorate@dialog',array('widgets'=>input::get('widgets'),'dialog'=>$showdialog));
        }
        else
        {
            $url = url::action('topshop_ctl_shop_decorate@index');
        }
        $this->sellerlog('pc店铺装修。编辑 '.$this->headerTitle[input::get('widgets')]['default']);
        $msg = app::get('topshop')->_('保存成功');
        return $this->splash('success',$url,$msg,true);
    }
    
    // 验证挂件参数的合法性
    private function _checkParams($params)
    {
        if(isset($params['data']) && is_array($params['data']))
        {
            foreach ($params['data'] as $value)
            {
                if($value['order_sort'])
                {
                    if(!$this->_isInt($value['order_sort']))
                    {
                        throw new LogicException(app::get('topshop')->_('排序序号必须为正整数'));
                    }
                }
            }
        }
        
        if(isset($params['order_sort']) && $params['order_sort'])
        {
            if(!$this->_isInt($params['order_sort']))
            {
                throw new LogicException(app::get('topshop')->_('排序序号必须为正整数'));
            }
        }
        
        if(isset($params['num']))
        {
            if(!$this->_isInt($params['num']))
            {
                throw new LogicException(app::get('topshop')->_('数量必须为正整数'));
            }
        }
        
    }
    
    // 判断数字是否是正整数
    private function _isInt($num)
    {
        if(!is_numeric($num) || floor($num)!=$num)
        {
            return false;
        }
        
        return true;
    }
}


