<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2014-2014 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysuser_data_user_credit
{
	//积分Model
	private $_credit_model;
	private $_credit_config;

	public function __construct() {
		$this->_credit_model = app::get('sysuser')->model('user_credit');
	}

	/**
	 * 设置信用积分 | 基础积分保存，立即返积分
     *
     * @param   array   $arr    保存需增加积分内容项
     * @param   enum    $module 模块名称
	 *
	 * @return	void
	 */
	public function setCreditPoint($arr = array(), $module = 'sysuser_user_company') {
	    $user_id = $arr['user_id'];
	    unset($arr['user_id']);

		//获取基础积分配置
	    $config = $this->_get_credit_config($module);
        $this->_credit_config = $config;
		$_config = array_keys($config);

		$db = app::get('sysuser')->database();
		$db->beginTransaction();
		try {
            //此次变更积分
            $points = 0;
            foreach ($arr AS $key => $val) {
                //属于配置项，有值
                if (in_array($key, $_config)) {
                    //是否设置过
                    $isSet = $this->getIssetConfig($user_id, $key);

                    //已设置过
                    if ($isSet) {
                        //已设置，删除项时才操作
                        if (!$val) {
                            $points += -$config[$key]['point'];
                            $mark = $config[$key]['name'];
                            $log = [
                                'user_id' => $user_id,
                                'points' => $points,
                                'type' => 'base',
                                'marks' => $mark,
                            ];
                            $this->save_credit_log($log, '修改');

                            //设为未设置
                            if ($val) $this->setIssetConfig($user_id, $key, 0);
                        }
                        continue;
                    }

                    //没设置过，且有值 才操作 新加积分项
                    if ($val) {
                        $points += $config[$key]['point'];
                        $mark = $config[$key]['name'];
                        $log = [
                            'user_id' => $user_id,
                            'points' => $points,
                            'type' => 'base',
                            'marks' => $mark,
                        ];
                        $this->save_credit_log($log, '保存');

                        //设为未设置
                        $this->setIssetConfig($user_id, $key);
                    }

                }
            }

            //更新用户积分表，基础积分 && 总积分
            $exists = $this->_credit_model->getRow('user_id', ['user_id' => $user_id]);
            if ($exists) {
                $sql_update = "UPDATE sysuser_user_credit SET base=base+{$points},
                total=seller_ex+seller_trade+seller_rate+temai_ex+temai_trade+temai_rate+proofing_ex+proofing_trade+proofing_rate+total+$points 
                WHERE user_id={$user_id}";
                $rs = $db->executeUpdate($sql_update);
            } else {
                $insert_data = [
                    'user_id' => $user_id,
                    'base' => $points,
                    'total' => $points
                ];
                $rs = $this->_credit_model->save($insert_data);
            }

            if($rs) {
                $db->commit();
            }
        }
        catch(\Exception $e)
        {
            $db->rollback();
            return;
        }
	}

    /**
	 * 生成会员各频道积分
	 *
	 * @desc	crontab
     *
     * @return  void
	 */
	public function createCreditPoint()
    {
        //查询企业会员列表
        $accountObj = app::get('sysuser')->database();
        $sql = "SELECT user_id,login_account,user_role FROM sysuser_account WHERE user_role & 2";
        $companyUsers = $accountObj->executeUpdate($sql);

        foreach($companyUsers AS $user) {
            //基础积分
            $companyInfo = app::get('sysuser')->model('user_company')->getRow('*', ['user_id' => $user['user_id']]);
            $this->setCreditPoint($companyInfo);

            /* ↓ 商家积分 ↓ */
            //激励积分 | 交易积分 | 评价积分
            $data['seller_ex'] = 0;
            $data['seller_trade'] = 0;
            $data['seller_rate'] = 0;

            /* ↓ 平台展销积分 ↓ */
            //激励积分 | 交易积分 | 评价积分
            $data['temai_ex'] = 0;
            $data['temai_trade'] = 0;
            $data['temai_rate'] = 0;

            /* ↓ 服务撮合积分 ↓ */
            //激励积分 | 交易积分 | 评价积分
            $data['proofing_ex'] = 0;
            $data['proofing_trade'] = 0;
            $data['proofing_rate'] = 0;

            $user_id = $user['user_id'];

            //平台展销
            if ($user['user_role'] & 6) {
                //平台展销交易获取
                $seller_trade_where = [
                    'order_type' => 'normal',
                    'status|in' => ['WAIT_EVALUATE', 'TRADE_FINISHED'],   //订单状态：待评价、已完成
                    'temai_user_id' => $user_id
                ];
                $tradeMoney = app::get('systrade')->model('trade')->getRow('sum(payment) AS total', $seller_trade_where);
                $data['temai_trade'] = (float)$tradeMoney['total'] * (1 / 10000);
                $data['temai_ex'] = $data['temai_trade'];
                $data['temai_rate'] = 0;
            }
            //服务撮合
            if ($user['user_role'] & 8) {
                $seller_trade_where = [
                    'order_type' => 'proofing',
                    'status|in' => ['WAIT_EVALUATE', 'TRADE_FINISHED'],   //订单状态：待评价、已完成
                    'shop_id' => $user_id
                ];
                $tradeMoney = app::get('systrade')->model('trade')->getRow('sum(payment) AS total', $seller_trade_where);
                $data['proofing_trade'] = (float)$tradeMoney['total'] * (1 / 10000);
                $data['proofing_ex'] = $data['proofing_trade'];
                $data['proofing_rate'] = 0;
            }

            //商家
            $shop_id = $this->ShopIdBindUserId($user_id);
            if($shop_id) {
                //商城交易获取
                $seller_trade_where = [
                    'shop_id' => $shop_id,
                    'order_type' => 'normal',
                    'status|in' => ['WAIT_EVALUATE', 'TRADE_FINISHED'],   //订单状态：待评价、已完成
                ];
                $tradeMoney = app::get('systrade')->model('trade')->getRow('sum(payment) AS total', $seller_trade_where);
                $data['seller_trade'] = (float)$tradeMoney['total'] * (1 / 10000);

                //激励积分等同交易积分
                $data['seller_ex'] = $data['seller_trade'];

                //评价积分
                $shopRate = $this->__getShopDsr($shop_id);
                $rate = $shopRate['countDsr'];
                $data['seller_rate'] = 2 * ($rate['tally_dsr'] + $rate['attitude_dsr'] + $rate['delivery_speed_dsr']);
            }

            //更新积分
            $_update = [];
            foreach ($data AS $key => $val) {
                $_update[] = "`{$key}`={$val}";
            }
            $update = implode(',', $_update);

            $sum = array_sum($data);
            $sql_credit = "UPDATE sysuser_user_credit SET total=base+{$sum},{$update} WHERE user_id={$user_id}";
            $accountObj->executeUpdate($sql_credit);
        }
    }

	/**
	 * 获取积分配置
	 *
	 * @return	array
	 */
	protected function _get_credit_config($module = '') {
	    $where = [];
	    if($module) $where = ['module' => $module];
		$credit_config_model = app::get('sysuser')->model('user_credit_config');
		$config = $credit_config_model->getList('*', $where);

		$keys = array_column($config, 'key');
		$new_config = array_combine($keys, $config);

		return $new_config;
	}

	/**
	 * 保存积分日志
     *
     * @param   array   $log    积分日志内容
     * [
        'user_id' => '1',
        'points' => 5,  //-5
        'type'  => 'base',
        'marks' => '授权书',
       ]
     *
     * @return  void
	 */
	public function save_credit_log($log, $label = '') {
	    $credit_type = config::get('userAuth.credit_type');
	    $type = $credit_type[$log['type']];
        $op = $log['points'] > 0 ? '增加' : '扣除';
        //日志
        $data_log['user_id'] = $log['user_id'];
        $data_log['points'] = $log['points'];
        $data_log['type'] = $log['type'];
        $data_log['content'] = "{$label}{$type} {$log['marks']} {$op}积分";
        $data_log['addtime'] = time();
        app::get('sysuser')->model('user_credit_log')->save($data_log);
    }

    private function __getShopDsr($shopId)
    {
        $params['shop_id'] = $shopId;
        $params['catDsrDiff'] = true;
        $dsrData = app::get('topc')->rpcCall('rate.dsr.get', $params);
        if( !$dsrData )
        {
            $countDsr['tally_dsr'] = sprintf('%.1f',5.0);
            $countDsr['attitude_dsr'] = sprintf('%.1f',5.0);
            $countDsr['delivery_speed_dsr'] = sprintf('%.1f',5.0);
        }
        else
        {
            $countDsr['tally_dsr'] = sprintf('%.1f',$dsrData['tally_dsr']);
            $countDsr['attitude_dsr'] = sprintf('%.1f',$dsrData['attitude_dsr']);
            $countDsr['delivery_speed_dsr'] = sprintf('%.1f',$dsrData['delivery_speed_dsr']);
        }
        $shopDsrData['countDsr'] = $countDsr;
        $shopDsrData['catDsrDiff'] = $dsrData['catDsrDiff'];
        return $shopDsrData;
    }

    /**
     * ①根据user_id 获取shop_id
     * ②没有绑定 user_id 与 shop_id 绑定关系
     *
     * @param   integer $user_id    会员id
     * @param   integer $shop_id    店铺id
     *
     * @return  integer 店铺id
     */
    public function ShopIdBindUserId($user_id, $shop_id = 0) {
	    $key = 'user_id_shop_id_' . $user_id;
        $redis = redis::scene('sysuser');

        $cache_shop_id = 0;
        if($shop_id){
            $redis->set($key, $shop_id);
            $cache_shop_id = $shop_id;
        }

        if($cache_shop_id < 1){
            $cache_shop_id = $redis->get($key);
            $cache_shop_id = intval($cache_shop_id);
        }

        return $cache_shop_id;
    }

    /**
     * 根据用户user_id, 配置相中的Key从redis中获取是否已设置过基础积分状态
     *
     * @param   integer $user_id    用户id
     * @param   string  $item       配置项key
     *
     * @return  mixed null | integer
     */
    public function getIssetConfig($user_id, $item = '') {
        //没有要获取的配置
        if(!$item) return;

        if(!$this->_credit_config) $this->_credit_config = $this->_get_credit_config();

        //不在配置项中
        if(!in_array($item, array_column($this->_credit_config, 'key'))) return;

        //获取redis中key状态
        $key = 'user_id_base_credit_' . $user_id . '_' . $item;
        $redis = redis::scene('sysuser');
        return (int)$redis->get($key);
    }

    /**
     * 根据用户user_id, 配置相中的Key从redis中 设置 是否已设置过基础积分状态
     *
     * @param   integer $user_id    用户id
     * @param   string  $item       配置项key
     * @param   enum    $val        是否设置，0未设置, 默认 1已设置
     *
     * @return  void
     */
    public function setIssetConfig($user_id, $item = '', $val = 1) {
        //没有要获取的配置
        if(!$item) return;

        if(!$this->_credit_config) $this->_credit_config = $this->_get_credit_config();

        //不在配置项中
        if(!in_array($item, array_keys($this->_credit_config, 'key'))) return;

        //设置当前key状态为已设置，并保存到redis中
        $key = 'user_id_base_credit_' . $user_id . '_' . $item;
        $redis = redis::scene('sysuser');
        return $redis->set($key, $val);
    }
}
