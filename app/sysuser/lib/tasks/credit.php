<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class sysuser_tasks_credit extends base_task_abstract implements base_interface_task{

    public function exec($params=null)
    {
		try {
			kernel::single('sysuser_data_user_credit')->createCreditPoint();
		} catch(Exception $e) {
			return $e->getMessage();
		}
    }
}
