<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * 电商支付控制类
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package ectools.lib
 */
class ectools_pay extends ectools_operation
{
    /**
     * 最终的克隆方法，禁止克隆本类实例，克隆是抛出异常。
     * @params null
     * @return null
     */
    final public function __clone()
    {
        throw new \Exception(app::get('ectools')->_('此类对象不能被克隆！'));
    }

    /**
     * 请求第三方支付网关
     * @params array - 订单数据
     * @params obj - 应用对象
     * @params string - 支付单生成的记录
     * @return boolean - 创建成功与否
     */
    public function generate(&$sdf)
    {
        // 异常处理
        if (!isset($sdf) || !$sdf || !is_array($sdf))
        {
            throw new \Exception(app::get('ectools')->_('支付单信息不能为空！'));
        }
        if (!$sdf['type'])
        {
            throw new \Exception(app::get('ectools')->_('支付类型是付款还是退款不能为空'));
        }

        // 支付方式的处理
        $str_app = "";
        $pay_app_id = ($sdf['pay_app_id']) ? $sdf['pay_app_id'] : $sdf['pay_type'];
        $obj_app_plugins = kernel::servicelist("ectools_payment.ectools_mdl_payment_cfgs");
        foreach ($obj_app_plugins as $obj_app)
        {
            $app_class_name = get_class($obj_app);
            $arr_class_name = explode('_', $app_class_name);
            if (isset($arr_class_name[count($arr_class_name)-1]) && $arr_class_name[count($arr_class_name)-1])
            {
                if ($arr_class_name[count($arr_class_name)-1] == $pay_app_id)
                {
                    $pay_app_ins = $obj_app;
                    $str_app = $app_class_name;
                }
            }
            else
            {
                if ($app_class_name == $pay_app_id)
                {
                    $pay_app_ins = $obj_app;
                    $str_app = $app_class_name;
                }
            }
        }

        $pay_app_ins = new $str_app();

        $is_payed = true;

        switch($sdf['pay_type'])
        {
            case "recharge":

            case "online":
                if($sdf['type']=='refund')
                {
                    if(!method_exists( $pay_app_ins, 'dorefund' ) )
                    {
                        throw new \Exception(app::get('ectools')->_('原支付方式不支持原路返回！请换线下退款方式！'));
                    }
                    logger::info("第三方退款请求信息：".var_export($sdf,1));
                    $is_payed = $pay_app_ins->dorefund($sdf);
                }
                else
                {
                    logger::info("支付请求信息：".var_export($sdf,1));
                    $is_payed = $pay_app_ins->dopay($sdf);
                }
                break;
            default:
                $is_payed = false;
                throw new \LogicException(app::get('ectools')->_('请求支付网关失败！'));
                break;
        }

        return $is_payed;
    }
}
