<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.com/license/gpl GPL License
 */

class base_unique{

	public $prefix = 'demo';
	public $max = 10000000;	//默认6位数，可设定限制
	private $__prefix = '0';//前缀

	//生成唯一码类型配置, 新增唯一码 要在此处新增！！！
	private $__type = [
		'user_no',	//用户编号
		'limit_temai',	//平台展销推送数量限制
	];

    function __construct() {
    }

	/**
	 * 获取唯一编码
	 *
	 * @param	enum	$type	生成唯一码类型
	 */
	public function get_unique_no($type) {
		$type = $this->__get_type($type);

		//获取是否存在唯一码
		$date = date('Ymd');
		$where['type'] = $type;
		$where['date'] = $date;

		$uniqueModel = app::get('base')->model('unique');

		//已生成过，生成新unique并更新原唯一码
		$current = $uniqueModel->getRow('number', $where);
		if($current) {
			//新唯一码
			$number = $this->__make_str((int)$current['number'] + 1);
			$data['number'] = $number;
			$uniqueModel->update($data, $where);
			return $this->prefix . $date . $number;
		}

		//生成新记录
		$where['number'] = $this->__make_str(1);
		$uniqueModel->insert($where);
		return $this->prefix . $date . $where['number'];
	}

	/**
	 * 获取唯一编码
	 *
	 * @param	enum	$type	生成唯一码类型
	 */
	public function get_number_day($type) {
		//不在配置项中，默认生成user唯一标识
		$type = $this->__get_type($type);
		$date = date('Ymd');

		//获取是否存在唯一码
		$where['type'] = $type;
		$where['date'] = $date;

		$uniqueModel = app::get('base')->model('unique');
		$current = $uniqueModel->getRow('id,number', $where);
		if($current['id'] > 0){
			return $current['number'];
		}

		return false;
	}

	public function set_number_day($type , $number , $minis = false) {
		$type = $this->__get_type($type);
		$date = date('Ymd');

		//获取是否存在唯一码
		$where['type'] = $type;
		$where['date'] = $date;

		$uniqueModel = app::get('base')->model('unique');
		$currentNum = $this->get_number_day($type);
		if(false === $currentNum) {
			$where['number'] = $number;
			$uniqueModel->insert($where);

			return $number;
		}

		if($minis){
			$number = $currentNum - $number;
			$number = $number>0 ? : 0;
		}else{
			$number = $currentNum + $number;
		}

		$data['number'] = $number;
		$uniqueModel->update($data, $where);

		return $number;
	}

	private function __get_type($type)
	{
		//不在配置项中，默认生成user唯一标识
		if(! in_array($type, $this->__type)){
			$type = 'user_no';
		}

		return $type;
	}

	/**
	 * 根据数据库内容，获取固定长度字符串
	 *
	 * @param	integer	$num	数值
	 *
	 * @return	固定长度字符串
	 */
	private function __make_str($num = 1) {
		$num = (int)$num;

		//最大长度后自行重新生成
		if($num >= $this->max) {
			return $num;
		}
		$length = strlen($this->max) - 1;
		$str = sprintf("%{$this->__prefix}{$length}d", $num);
		return $str;
	}
}
