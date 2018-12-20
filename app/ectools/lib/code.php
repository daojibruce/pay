<?php
/**
 * 位计算 生成、读写权限
 *
 * @desc    请先设置 $this->init($config) ！！！！！！
 *
 * @author	Yuan
 * @updater	Lee
 *
 * @date	2017-04-24 18:27
 *
 * @since	@1.0
 */

class ectools_code
{
	public $_typeIsxx = [];


	/**
	 * 设置默认读取配置信息，必须设置！！！
     *
     * @param   array   $config 要设置读取的配置信息
     *
     * @return  $this
	 */
	public function init($config) {
		$this->_typeIsxx = $config;
		return $this;
	}

	/**
	 * 设置XX位置状态
	 *
	 * @param	string	$isxxVal	上一次的code
	 * @param	enum	$itemName	要添加的内容，详情如配置文件内容
	 * @param	enum	$op			+追加，-去掉内容
	 *
	 * @return	intger	生成的十进制码
	 */
	public function setIsxx($isxxVal , $itemName, $op = '+'){
		if(!in_array($itemName , $this->_typeIsxx)){
			return 0;
		}
		$isxxVal = intval($isxxVal);

		$totalLen = strlen(strval(decbin($isxxVal)));
		$indexOfItem = 1 + array_search($itemName , $this->_typeIsxx);
		if($op == '+'){
			$maskCode = str_pad('1', $indexOfItem, '0');
			$maskCode = bindec($maskCode);
			$isxxVal = $isxxVal | $maskCode ;
		}else{
			$maskCode = str_pad('0', $indexOfItem, '1');
			$diffLen = $totalLen - $indexOfItem;
			if($diffLen){
				$maskCode = str_pad($maskCode,$totalLen, '1', STR_PAD_LEFT);
			}
			$maskCode = bindec($maskCode);
			$isxxVal = $isxxVal & $maskCode ;
		}

		return $isxxVal;
	}

	/**
	 * 读取XX位置状态 是否有某种角色
	 *
	 * @param	string	$isxxVal	已生成的code
	 * @param	enum	$itemName	要添加的内容，详情如配置文件内容
	 *
	 * @return	boolean	是否存在
	 */
	public function getIsxx($isxxVal , $itemName = '普通会员'){
		if(!in_array($itemName , $this->_typeIsxx)){
			return 0;
		}

		$isxxVal = intval($isxxVal);
		$indexOfItem = 1 + array_search($itemName , $this->_typeIsxx);
		$maskCode = str_pad('1', $indexOfItem, '0');
		$maskCode = bindec($maskCode);

		$itemBool = $isxxVal & $maskCode ;

		return $itemBool;
	}

}
